# @formatter:off
#
# _______ _____ __   _ _______  ______     _______  _____   ______
# |  |  |   |   | \  | |______ |_____/ ___    |    |     | |_____/
# |  |  | __|__ |  \_| |______ |    \_        |    |_____| |    \_
#
#  _____  __   __ _______ _     _  _____  __   _      _______ _____ __   _ _______  ______
# |_____]   \_/      |    |_____| |     | | \  |      |  |  |   |   | \  | |______ |_____/
# |          |       |    |     | |_____| |  \_|      |  |  | __|__ |  \_| |______ |    \_
#
# (c) Nigel Johnson 2020
# https://github.com/nigeljohnson73/minertor
#
# @formatter:on
import json
import requests
from datetime import datetime
import hashlib
import time
import getopt
import sys
VERSION = "0.1a"


def jsonApi(url, payload):
    headers = {"Content-type": "application/x-www-form-urlencoded"}
    proxies = {}
    if use_tor:
        proxies = {"http": "socks5h://{}".format(tor_proxy)}

    for n in range(5):
        try:
            response = requests.post(
                url, data=payload, headers=headers, proxies=proxies, timeout=15)

        except Exception as err:
            print("--- Api call failed: {}".format(repr(err)))

            class DuffReponse:
                pass

            response = DuffReponse()
            response.status_code = 0
            response.reason = repr(err)

        if response.status_code == 200:
            return json.loads(response.text)

    print("Api call failed ({code}): {reason}".format(
        code=response.status_code, reason=response.reason))
    return False


def usage():
    print("")
    print(
        "Usage:- python3 {} [-c 'chip-id'] [-d] [-h] [-p 'tor-proxy'] [-q] [-r 'rig-id'] -w 'wallet-id' [-y]".format(sys.argv[0]))
    print("")
    print("    -c 'id'  : Set the chip id for this miner (defaults to 'Python Script')")
    print("    -d       : Use the development server (mnrtor.local)")
    print("    -h       : This help message")
    print("    -p 'url' : Set the TOR proxy (defaults to '127.0.0.1:9050')")
    print("    -r 'id'  : Set the rig name for this miner (defaults to 'Python-Miner')")
    print("    -w 'id'  : Set 130 character wallet ID for miner rewards")
    print("    -y       : Yes!! I got everything correct, just get on with it")
    print("")
    sys.exit()


api_host = "http://ckwtzols3ukgmnam5w2bixq3iyw6d5oedp7a5cli6totg6ektlyknsqd.onion"
rig_id = "Python-Miner"
chip_id = "Python Script"
wallet_id = ""
tor_proxy = "127.0.0.1:9050"
pause = True
use_tor = True

try:
    opts, args = getopt.getopt(sys.argv[1:], "c:dhp:r:w:y", [
                               "chipid=", "dev", "help", "proxy=", "rigid=", "walletid=", "yes"])
except getopt.GetoptError as err:
    # print help information and exit:
    print(err)  # will print something like "option -a not recognized"
    usage()

for o, a in opts:
    if o in ("-c", "--chipid"):
        # print ("CHIPID")
        chip_id = a
    elif o in ("-d", "--dev"):
        # print ("DEV")
        api_host = "http://mnrtor.local"
        use_tor = False
    elif o in ("-h", "--help"):
        # print ("HELP")
        usage()
    elif o in ("-p", "--proxy"):
        # print ("PROXY")
        tor_proxy = a
    elif o in ("-r", "--rigid"):
        # print ("RIGID")
        rig_id = a
    elif o in ("-w", "--walletid"):
        # print ("WALLETID")
        wallet_id = a
    elif o in ("-y", "--yes"):
        # print ("YES")
        pause = False
    else:
        assert False, "unknown option"

if len(rig_id) == 0:
    print("No Rig ID supplied")
    usage()

if len(wallet_id) == 0:
    print("No Wallet ID supplied")
    usage()

if len(wallet_id) != 130:
    print("Wallet ID doesn't look correct. It should look like this (but obviously, don't use this one):")
    print("")
    print("    '04d329153bacfc18f8400b53904729fecbe44637e0b7902254f1a55d1f47b109b1e6d045d45b826234c04e35902eb5423f4b6d6104fde6a05ef3621a86a19f8171'")
    usage()

if pause:
    print("#####################################################################################################################################################")
    print("#")
    print("# Python Miner v{}".format(VERSION))
    print("#")
    print("#    Rig ID    : '{}'".format(rig_id))
    print("#    Wallet ID : '{}'".format(wallet_id))
    print("#    API host  : '{}'".format(api_host))
    if use_tor:
        print("#    TOR proxy : '{}'".format(tor_proxy))
    print("#")
    print("#####################################################################################################################################################")
    input("Press return to continue")


# Output messages with timestamp
def output(str):
    now = datetime.now()
    ts = now.strftime("%Y/%m/%d %H:%M:%S")
    print(ts + " | " + str)


request_api = "/api/job/request/json"
submit_api = "/api/job/submit/json"

# Prepare the data for the API calls (hashrate will be calulated and overwritten)
request_payload = {"wallet_id": wallet_id, "rig_id": rig_id}
submit_payload = {"hashrate": 0, "chiptype": chip_id}

# Keep track of how many jobs we received and how many were successful
job_c = 0
shares = 0

for loop in range(sys.maxsize):
    job = jsonApi(api_host + request_api, request_payload)
    if job:
        if job["success"] == False:
            print("0x00 | Request failed: {reason}".format(
                reason=job["reason"]))
        else:
            job_c += 1
            data = job["data"]

            job_id = data["job_id"]
            hash = data["hash"]
            diff = data["difficulty"]
            submit_delay = data["target_seconds"]

            output("0x01 | Recieved job: Y {jobid} {hash} {diff:02d} {sec:02d}".format(
                jobid=job_id, hash=hash, diff=diff, sec=submit_delay))

            nonce = -1
            valid = ""
            for x in range(diff):
                valid = valid + str("0")

            start = time.perf_counter()
            for x in range(sys.maxsize):
                lhash = hash + str(x)
                hasho = hashlib.sha1(lhash.encode())
                if hasho.hexdigest().startswith(valid):
                    nonce = x
                    break
                if (time.perf_counter() - start) > (submit_delay * 2):
                    break

            duration = time.perf_counter() - start
            submit_payload["hashrate"] = (nonce + 1) / duration

            if nonce >= 0:
                output("0x02 | Nonce: {nonce} | duration: {duration:0.4f} | hashrate: {hashrate:,.2f} | hash: {hash}".format(
                    nonce=nonce, duration=duration, hashrate=submit_payload["hashrate"], hash=hasho.hexdigest()))
            else:
                output("0x02 | Error: Failed to calculate hash in time")

            while (time.perf_counter() - start) < submit_delay:
                time.sleep(0.1)

            job = jsonApi(api_host + submit_api + "/" +
                          job_id + "/" + str(nonce), submit_payload)
            if job == False:
                print("0x04 | REJECTED | Unknown reason")
            elif job["success"] == False:
                print("0x04 | REJECTED | {reason}".format(
                    reason=job["reason"]))
            else:
                shares += 1
                output("0x03 | ACCEPTED | {accepted:,}/{total:,} | {pcnt:0.2f}%".format(
                    accepted=shares, total=job_c, pcnt=(100 * (shares / job_c))))
    else:
        time.sleep(5)
