/*
 _______        _______ _    _ _______ __   _ _______ __   __      _______ _____ _    _ _______
 |______ |      |______  \  /  |______ | \  |    |      \_/        |______   |    \  /  |______
 |______ |_____ |______   \/   |______ |  \_|    |       |         |       __|__   \/   |______
                                                                                               
 _______ _______  _____ 32    _______ _____ __   _ _______  ______
 |______ |______ |_____]      |  |  |   |   | \  | |______ |_____/
 |______ ______| |            |  |  | __|__ |  \_| |______ |    \_                 Version 0.1a

 (c) Nigel Johnson 2020
 https://github.com/nigeljohnson73/minertor
 https://minertor.appspot.com/
*/
/***********************************************************************************************
 * The purpose of this file is to demontrate how the complicated match bit works in the ESP32 
 * miner. It uses the exact same process to generate a hash with a nonce that is validatable
 * and then prints out the complex algorithms zero character matching ability.
 */

// Add the latest board details to your board manger and chooose at least version 2.0.0 of the ESP32 package
// https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_dev_index.json
#include "sha/sha_parallel_engine.h"

// How many zeros make a valid signature. The more, the longer it will take to find a match
int diff = 3;

void setup() {
	Serial.begin(500000);

	// For the purposes of testing and using unsigned int for the full 32 bit data space, start at zero
	unsigned int nonce = 0;

	// A 'hashable' string
	String hash = "It doesn't matter what's in here";

	// The Hash we will generate will be 20 bytes long in RAW form
	uint8_t buff[20];

	// Create a string to do the adding of the nonce here, so we don't use too many CPU cycles
	String lhash = "";

	// Start the match with a true, so we can bitwise and with the result to ensure the match
	bool match = true;

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

	// We are done with the algorithm. Lets turn the output into a hex string for testing
	lhash = "";
	for (auto i:buff) {
		String hex = String(i, HEX);
		if (hex.length() < 2) {
			hex = "0" + hex;
		}
		lhash += hex;
	}

	// Output the nonce we found, and the string it generates
	Serial.println(String("\nNonce: ") + nonce + ", hash: " + lhash);

	// 20 bytes of raw hash is 40 bytes of hexidecimal. Iterate through each character and see if it's a zero
	for(unsigned int c=0; c < 40; c++) {
		Serial.print(String((c<10)?("0"):("")) + c);
		Serial.print(String(", char: ") + lhash[c]);
		Serial.print(String(", zero: ") + ((buff[(int)floor(c/2)] & (0xf << ((!(c%2))*4))) == 0));
		Serial.print(String(", buff: ") + buff[(int)floor(c/2)]);
		Serial.print(String(", mask: ") + (0xf << ((!(c%2))*4)));
		Serial.println("");
	}
}

void loop() {
}
