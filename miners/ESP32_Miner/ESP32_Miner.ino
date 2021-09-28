/*
 _______        _______ _    _ _______ __   _ _______ __   __      _______ _____ _    _ _______
 |______ |      |______  \  /  |______ | \  |    |      \_/        |______   |    \  /  |______
 |______ |_____ |______   \/   |______ |  \_|    |       |         |       __|__   \/   |______
                                                                                               
 _______ _______  _____ 32    _______ _____ __   _ _______  ______
 |______ |______ |_____]      |  |  |   |   | \  | |______ |_____/
 |______ ______| |            |  |  | __|__ |  \_| |______ |    \_                 Version 0.1a

 (c) Nigel Johnson 2020
 https://github.com/nigeljohnson73/coin73
 https://coin73.appspot.com/
*/
const String VERSION = "v0.1a";

#include <ESPmDNS.h>
#include <HTTPClient.h>
#include <WiFi.h>
#include <WiFiUdp.h>
#include <esp_task_wdt.h>
#include <ArduinoOTA.h>

// Add the latest board details to your board manger and chooose at least version 2.0.0 of the ESP32 package
// https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_dev_index.json
#include "sha/sha_parallel_engine.h"

const char* wifi_ssid = "WIFISSID";  // You WiFi SSID on your router
const char* wifi_pass = "PASSWORD";  // The password/phrase you access the router with
const char* wallet_id = "WALLETID";  // The really long string found on the account
const char* rig_id  = "ESP32-Miner"; // This must be unique on your account

/************************************************************************************************
 _______                          __            _______         __               
|     __|.-----.-----.-----.----.|__|.----.    |     __|.-----.|  |_.--.--.-----.
|    |  ||  -__|     |  -__|   _||  ||  __|    |__     ||  -__||   _|  |  |  _  |
|_______||_____|__|__|_____|__|  |__||____|    |_______||_____||____|_____|   __|
                                                                          |__|   
You shouldn't need to tinker below here
*/
// The watchdog timer is used to reboot the board if it gets stuck after this many seconds of silence
#define WDT_TIMEOUT 60

// Used for debugging on the server
const char* chip_id = "ESP32";

// In some ESP32 implementations, it seems min() and max() functions are not defined.
// Check and define them if they are missing
#ifndef min
#define min(a,b) (((a) < (b)) ? (a) : (b))
#endif

#ifndef max
#define max(a,b) (((a) > (b)) ? (a) : (b))
#endif

// If you don't want to use the Serial interface comment this line out
#define ENABLE_SERIAL

// A bigger number here will have more Serial traffic LED flashing, but if you're wanting to see what's 
// in the Serial output on the monitor, that will be bad.
#define BLINK_SIZE 10

#ifndef ENABLE_SERIAL
// If you have an onboard LED and you're not using the Serial interface to make it blink, define it here
#define LED_BUILTIN 2
#define Serial DummySerial
class MySerial {
	public:
		void begin(...) {
			pinMode(LED_BUILTIN, OUTPUT);
			}
		void print(...) {
			pinMode(LED_BUILTIN, HIGH);
			pinMode(LED_BUILTIN, LOW);
		}
		void println(...) {
			pinMode(LED_BUILTIN, HIGH);
			pinMode(LED_BUILTIN, LOW);
		}
		void printf(...) {
			pinMode(LED_BUILTIN, HIGH);
			pinMode(LED_BUILTIN, LOW);
		}
};
MySerial Serial;
#endif

// Here we will store some data that will be accessible across threads. Generally it is 
// one way, so we will not need a mutex to handle clashes

// Only do anything if the WiFi is connected. Managed in the wifi management thread
// Set it to connected here so the maanger will handle a disconnect to start with
volatile int wifi_state = WL_CONNECTED;

// Once we have connected, pause a bit to let the management complete and wifi stabilise
volatile unsigned long wifi_ready_millis = 0;

// The OTA stuff will need to halt the main processing so it can finish
volatile bool ota_state = false;

// Store the threaded task handles.
TaskHandle_t wifi_handle;
TaskHandle_t miner1_handle;

// This function will pull out the tokens within a string. It is designed to be readable,
// not fast or efficient. This is ok, because we have lots of power to spare on an ESP32.
String getStringPart(const String& data, const char separator, const int index) {
	String chunk = ""; // the return chunk should we find it
	int chunk_n = 0;   // indicates the number of the chunk we are dealing with
	bool done = false; // we're done cuz we've found the end of the chunk we need

	// Walk through the data, one character at a time
	for(int i = 0; (i < (data.length() - 1)) && !done; i++) {
		const char ch = data.charAt(i);
		if(ch == separator) {
			// If we've hit a separator, then we are in a new chunk of the data
			chunk_n++;
		} else if(chunk_n == index) {
			// If we're in the chunk we are wanting, add it to the return chunk
			chunk.concat(ch);
		} else if(chunk_n > index) {
			// We have gone past the bit we need, so we're done
			done = true;
		}
	}

	// return any chunk we have
	return chunk;
}

/************************************************************************************************
 ________ __ _______ __      _______                                                      __   
|  |  |  |__|    ___|__|    |   |   |.---.-.-----.---.-.-----.-----.--------.-----.-----.|  |_ 
|  |  |  |  |    ___|  |    |       ||  _  |     |  _  |  _  |  -__|        |  -__|     ||   _|
|________|__|___|   |__|    |__|_|__||___._|__|__|___._|___  |_____|__|__|__|_____|__|__||____|
                                                       |_____|                                 
*/
// Because of the static counter, we need to have separate functions to handle the WDT checkin.
void wifiWdtCheckin() {
	static unsigned long last_wdt = 0;
	const long wdt_interval = 5000;
	unsigned long current_millis = millis();
	if (current_millis - last_wdt >= wdt_interval) {
		last_wdt = current_millis;
		esp_task_wdt_reset();
	}

	// Be nice to the processor
	yield();
	delay(50);
}

// Handle all of the wifi management processes including signalling to mining threads
void wifiManager(void *pvParameters) {
	// Initialise it as connected so the first time I check we force a disconnection loop
	//int wifi_state_prev = WL_CONNECTED;

	// A little Serial visual styling while connecting to wifi
	unsigned long previous_millis = 0; // Last time I printed a connecting dot
	const unsigned long interval = 500; // How often to print a connecting dot

	// Configure this task as one to be managed by the WatchDogTimer
	esp_task_wdt_add(NULL);

	// Loop forever (hopefully)
	for (;;) {
		// Set the current state
		int wifi_state_now = WiFi.status();

		// If we are doing any OTA stuff. Handle that tick here.
		ArduinoOTA.handle();
		
		if (ota_state)  {
			// Not sure how long it will have been, so just have a little pause pause
			wifiWdtCheckin();
		}
		
		// check if WiFi status has changed.
		if ((wifi_state_now == WL_CONNECTED) && (wifi_state != WL_CONNECTED)) {
			// We have just connected to the WiFi
			wifiWdtCheckin();

			// Signal steady state in 500ms
			wifi_ready_millis = millis() + 500;
			Serial.println(F("\nConnected to WiFi!"));
			Serial.println("    IP address: " + WiFi.localIP().toString());
			Serial.println("      Rig name: " + String(rig_id));
			Serial.println();

			// Be nice to the scheduler
			yield();
			delay(50);
		} else if ((wifi_state_now != WL_CONNECTED) && (wifi_state == WL_CONNECTED)) {
			// We have just disconnected to the WiFi
			wifiWdtCheckin();

			// Signal any other process that we won't be ready for a while
			wifi_ready_millis = millis() + 100000;
			Serial.println(F("\nWiFi disconnected!"));
			// Force the disconnect handling
			WiFi.disconnect();

			// Scan the network for available SSIDs
			Serial.println(F("Scanning for WiFi networks"));
			int n = WiFi.scanNetworks(false, true);
			Serial.println(F("Scan done"));
			
			if (n == 0) {
				Serial.println(F("No networks found. Resetting ESP32."));
				esp_restart();
			} else {
				Serial.print(n);
				Serial.println(F(" networks found"));
				for (int i = 0; i < n; ++i) {
					// Print wifi_ssid and RSSI for each network found
					Serial.print(i + 1);
					Serial.print(F(": "));
					String ssid = WiFi.SSID(i);
					if(ssid == "") {
						ssid = "<HIDDEN>";
					}
					Serial.print(ssid);
					Serial.print(F(" ("));
					Serial.print(WiFi.RSSI(i));
					Serial.print(F(")"));
					Serial.println((WiFi.encryptionType(i) == WIFI_AUTH_OPEN) ? " (OPEN)" : "");
					delay(10);
				}
			}
			// reset the watchdog timer
			esp_task_wdt_reset();
	
			Serial.println(F("\nPlease, check if your WiFi network is on the list and check if it's strong enough (greater than -90)."));
			Serial.println("ESP32 will reset itself after " + String(WDT_TIMEOUT) + " seconds if can't connect to the network");

			// Start the reconnection process. It's handled by the onboard chip, so will come back immediately.
			Serial.print("Connecting to: " + String(wifi_ssid));
			WiFi.reconnect();
		} else if ((wifi_state_now == WL_CONNECTED) && (wifi_state == WL_CONNECTED)) {
			// This is the steady state. Just keep checking the watchdog timer
			wifiWdtCheckin();
		} else {
			// We are probably reconnecting, so print some dots to show progress.
			// Don't reset watchdog timer so the board will reset after the timeout occurs
			unsigned long current_millis = millis();
			wifi_ready_millis = current_millis + 100000;
			if (current_millis - previous_millis >= interval) {
				previous_millis = current_millis;
				Serial.print(F("."));
			}
		}
		// Update the threads on the current wifi status
		wifi_state = wifi_state_now;
	}
}

/************************************************************************************************
 _______ __         __                                 __        
|   |   |__|.-----.|__|.-----.-----.    .----.-----.--|  |.-----.
|       |  ||     ||  ||     |  _  |    |  __|  _  |  _  ||  -__|
|__|_|__|__||__|__||__||__|__|___  |    |____|_____|_____||_____|
                             |_____|                             
*/
// Because of the static counter, we need to have separate functions to handle the WDT checkin.
// In the miner one though, make sure we flash some lights
void minerWdtCheckin() {
	static unsigned long last_wdt = 0;
	const long wdt_interval = 5000;

	unsigned long current_millis = millis();
	if (current_millis - last_wdt >= wdt_interval) {
		last_wdt = current_millis;
		esp_task_wdt_reset();
	}

	// Be nice to the processor
	yield();

	// This next bit is in no way needed, it just makes the LED blink a little more so the device
	// Looks busier than it is. It uses the Serial device because the EPS32 I have doesnt have
	// a specfic LED I can control, but it does have a TX/RX LED for the serial port.
	if((wifi_state == WL_CONNECTED) && (millis() > wifi_ready_millis)) {
		// If we are connected and in steady state
		// Randomise the delay between 20 and 100ms
		delay(20 + random(0, 80));

		// Make the lights flash a little bit
		int n = random(0, BLINK_SIZE) - (BLINK_SIZE/2);
		if(n > 0) {
			String output = "";
			for(int i = 0; i < n; i++) {
				output += " ";
			}
			Serial.print(output);
		}
	} else {
		// If we are nont connected and in steady state
		// fixed, short delay to aid looping
		delay(20);
	}

}

// If we need to pause for any reason, handle that here with Yeilding and checking the WatchDog
void minerWait(const unsigned long ms) {
	//Serial.println(String("minerWait(") + ms + ")");
	const unsigned long started = millis();
	while(millis() - started < ms) {
		minerWdtCheckin();
	}
}

// The main loop for mining
void processMining(void *pvParameters) {
	// Define the API end points we will need as per the specs:
	//     https://coin73.appspot.com/wiki/api/job/request
	//     https://coin73.appspot.com/wiki/api/job/submit
	const char* get_api = "http://coin73.appspot.com/api/job/request/text";
	const char* put_api = "http://coin73.appspot.com/api/job/submit/text";

	// If there is an error on the API, don't flood it with more requests, pause a bit
	const unsigned long error_wait = 5000;

	// Add some counters so we can work out the subission success rate
	unsigned long received_jobs = 0;
	unsigned long accepted_jobs = 0;

	// Set up the WiFiClient and HTTP Request
	WiFiClient client;
	HTTPClient http;
	String payload = "";

	// Configure this task as one to be managed by the WatchDogTimer
	esp_task_wdt_add(NULL);

	// Loop forever (hopefully)
	for(;;) {
		// Set up some job related goodness
		unsigned int nonce = 0;
		String job_id = "";
		String hash = "";
		int diff = 0;
		int sub_time = 0;

		// Only do anything if we are connected and the WIFI is ready
		if((wifi_state == WL_CONNECTED) && (millis() > wifi_ready_millis)) {
			if (http.begin(client, get_api)) {
				// HTTP client is ready to go, add the POST header and data
				http.addHeader("Content-Type", "application/x-www-form-urlencoded");
				String data = String("wallet_id=") + wallet_id +"&rig_id=" + rig_id;
				int resp = http.POST(data);

				if (resp == HTTP_CODE_OK) {
					// Response was good, so get the payload for processing
					payload = http.getString();

					// If the first 'chunk' is a 'Y' for yes
					if(getStringPart(payload, ' ', 0) == "Y") {
						// We got a job!!!
						received_jobs = received_jobs + 1;
						Serial.print("\nReceived job: " + payload);

						// Extract the parts of the job as per the spec
						job_id = getStringPart(payload, ' ', 1);
						hash = getStringPart(payload, ' ', 2);
						diff = getStringPart(payload, ' ', 3).toInt();
						sub_time = getStringPart(payload, ' ', 4).toInt();

						// The submission time is seconds into the future, so add now
						sub_time = millis() + (sub_time * 1000);
					} else if(getStringPart(payload, ' ', 0) == "N") {
						// An error occurred so wait for a bit.
						Serial.print(String("\nRequest error: '") + (payload.c_str() + 2) + "'");
						minerWait(error_wait);
					} else {
						// We really should not be here!!!!
						Serial.print(String("\nUnknown payload: '") + payload + "'");
						minerWait(error_wait);
					}
				} else {
					// Being here means the underlying HTTP subsystem reported a failure like a 404 etc, or timed out
					Serial.print(String("\n[HTTP] failed (") + resp + ") " + http.errorToString(resp));
					minerWait(error_wait);
				}
			} // http.begin()

			// Close off this HTTP call
			http.end();
			minerWdtCheckin();

			// If we processed job details from the request, then lets parse it.
			if(job_id.length()) {
				// The Hash we will generate will be 20 bytes long in RAW form
				uint8_t buff[20];

				// Create a string to do the adding of the nonce here, so we don't use too many CPU cycles
				String lhash = "";

				// Start the match with a true, so we can bitwise and with the result to ensure the match
				bool match = true;

				// Start a timer so we can calulate hashrate
				unsigned long started = millis();

				// Loop to the full 32 bits space, or until we fine a nonce
				for(unsigned int i = 1; i < 0xFFFFFFFF && nonce == 0; i++) {
					// Add the counter to the supplied job and get the SHA1 hash of that
					lhash = hash + i;
					esp_sha(esp_sha_type::SHA1, (const unsigned char *)lhash.c_str(), lhash.length(), buff);

					// Since we are looking for numbers of zeros in a hex string, zero in decimal for a character is represented 
					// by 0x00 in hexidecimal, so we have to check half a byte for the hex zero... for every zero we want.
					match = true;
					for (int c = 0; c < diff; c++) {
						// This is REALLLY difficult to explain, and I know I said things should be written for clairty, but 
						// I just wanted a good hashrate. This basically checks the high and low 4 bits and compares them to zero.
						match &= (buff[(int)floor(c/2)] & (0xf << ((!(c%2))*4))) == 0;
					}

					// If we got to the end of the checking and we are still matching, then set the nonce
					if(match) {
						nonce = i;
					}
				}

				// Calculate the hashrate
				double duration = (millis() - started)/1000.0;
				double hashrate = ((nonce > 0) && (duration > 0)) ? ((nonce + 1)/duration) : 0;

				lhash = "";
				for (auto i:buff) {
					String hex = String(i, HEX);
					if (hex.length() < 2) {
						hex = "0" + hex;
					}
					lhash += hex;
				}
				Serial.print(String("\nNonce: ") + nonce + ", duration: " + duration + ", hashrate: " + hashrate + ", hash: " + lhash);

				// Wait until we are allowed to submit the result
				while(millis() < sub_time) {
					minerWdtCheckin();
				}

				// The submission process is similar to the request. Create the HTTP request
				if (http.begin(client, String(put_api) + "/" + job_id + "/" + nonce)) {
					// Add the post data
					http.addHeader("Content-Type", "application/x-www-form-urlencoded");
					String data = String("chip_id=") + chip_id +"&hashrate=" + hashrate;
					int resp = http.POST(data);

					if (resp == HTTP_CODE_OK) {
						// If we got the OK message, Job done
						payload = http.getString();

						// If the first 'chunk' is a 'Y' for yes, it was accepted
						if(getStringPart(payload, ' ', 0) == "Y") {
							// Increment the counter and output some more stats
							accepted_jobs = accepted_jobs + 1;
							Serial.print(F("\nACCEPTED, "));
							Serial.print(accepted_jobs);
							Serial.print(F("/"));
							Serial.print(received_jobs);
							Serial.print(F(", "));
							Serial.print((((double)accepted_jobs)/((double)received_jobs)) * 100.0);
							Serial.print(F("%"));
						} else if(getStringPart(payload, ' ', 0) == "N") {
							// An error occurred, so output why and wait a bit
							Serial.print(String("\nREJECTED: '") + (payload.c_str() + 2) + "'");
							minerWait(error_wait);
						} else {
							// We really shouldn't be here, but wait
							Serial.print(String("\nUnknown response: '") + payload + "'");
							minerWait(error_wait);
						}
					} else {
						// Underlying HTTP failure, so putput it and wait a bit
						Serial.print(String("\n[HTTP] failed (") + resp + ") " + http.errorToString(resp));
						minerWait(error_wait);
					}
				} // http.begin();
				
				// Clear up that HTTP connection
				http.end();
			} // job_id.length()
		} // wifi connected

		// Just before the loop, check in with teh watchdog if we need to
		minerWdtCheckin();
	}
}

/************************************************************************************************
 ______                        _______         __               
|      |.-----.----.-----.    |     __|.-----.|  |_.--.--.-----.
|   ---||  _  |   _|  -__|    |__     ||  -__||   _|  |  |  _  |
|______||_____|__| |_____|    |_______||_____||____|_____|   __|
                                                         |__|   
 */
void setup() {
	// The ESP has a fast Serial interface, lets use it
	Serial.begin(500000);  // Start serial connection
	Serial.println(String("\n\n") + rig_id + ": Coin73 ESP32 Miner " + VERSION);

	// Enable Wifi in client (Station) mode
	WiFi.mode(WIFI_STA);
	WiFi.begin(wifi_ssid, wifi_pass);

	// Disable Bluetooth for security and power management reasons.
	btStop();

	// When doin the OTA stuff,the WiFi thread doesn't cater for this in steady state becasue
	// there is no way for it to know specifically. Flag this so it's handled properly there.
	ota_state = false;

	// Define how the OTA stuff should work. This is default stuff so don't delve too deeply.
	ArduinoOTA.onStart([]() {
		String type;
		if (ArduinoOTA.getCommand() == U_FLASH) {
			type = "sketch";
		} else { // U_SPIFFS
			type = "filesystem";
		}
		Serial.println("Start updating " + type);
		ota_state = true;
	}).onEnd([]() { 
		Serial.println(F("\nEnd")); 
	}).onProgress([](unsigned int progress, unsigned int total) {
		Serial.printf("Progress: %u%%\r", (progress / (total / 100)));
		esp_task_wdt_reset();
		ota_state = true;
	}).onError([](ota_error_t error) {
		Serial.printf("Error[%u]: ", error);
		if (error == OTA_AUTH_ERROR) {
			Serial.println(F("Auth Failed"));
		} else if (error == OTA_BEGIN_ERROR) {
			Serial.println(F("Begin Failed"));
		} else if (error == OTA_CONNECT_ERROR) {
			Serial.println(F("Connect Failed"));
		} else if (error == OTA_RECEIVE_ERROR) {
			Serial.println(F("Receive Failed"));
		} else if (error == OTA_END_ERROR) {
			Serial.println(F("End Failed"));
		}
		ota_state = false;
		esp_restart();
	});

	// Set the OTA hostname so it's easier to see on the Arduino IDE port dropdown
	ArduinoOTA.setHostname(rig_id);
	ArduinoOTA.begin();

	// Initialise the WatchDog Timer
	esp_task_wdt_init(WDT_TIMEOUT, true);

	// create a WiFi management task with priority 3 and executed on core 0
	xTaskCreatePinnedToCore(wifiManager, "wifi", 10000, NULL, 3, &wifi_handle, 0);
	delay(250);

	// create a Mining task with priority 3 and executed on core 0
	xTaskCreatePinnedToCore(processMining, "miner1", 10000, NULL, 3, &miner1_handle, 1);
	delay(250);
}

void loop() {
	// becasue we have built the threads as core pinned tasks, there is nothing to do in here.

}
