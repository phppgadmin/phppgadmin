#!/usr/bin/python -u
'''
    ADOdb release upload script
'''

from distutils.version import LooseVersion
import getopt
import getpass
import glob
import json
import os
from os import path
import re
import requests
import subprocess
import sys
import yaml


# Directories and files to exclude from release tarballs
# for debugging, set to a local dir e.g. "localhost:/tmp/sf-adodb/"
sf_files = "frs.sourceforge.net:/home/frs/project/adodb/"

# SourceForge Release API base URL
# https://sourceforge.net/p/forge/documentation/Using%20the%20Release%20API/
sf_api_url = 'https://sourceforge.net/projects/adodb/files/{}/'

# rsync command template
rsync_cmd = "rsync -vP --rsh ssh {opt} {src} {usr}@{dst}"

# Command-line options
options = "hu:ns"
long_options = ["help", "user=", "dry-run", "skip-upload"]


def usage():
    print '''Usage: %s [options] username [release_path]

    This script will upload the files in the given directory (or the
    current one if unspecified) to SourceForge.

    Parameters:
        release_path            Location of the release files to upload
                                (see buildrelease.py)

    Options:
        -h | --help             Show this usage message
        -u | --user <name>      SourceForge account (defaults to current user)
        -n | --dry-run          Do not upload the files
''' % (
        path.basename(__file__)
    )
# end usage()


def call_rsync(usr, opt, src, dst):
    ''' Calls rsync to upload files with given parameters
        usr = ssh username
        opt = options
        src = source directory
        dst = target directory
    '''
    global dry_run

    command = rsync_cmd.format(usr=usr, opt=opt, src=src, dst=dst)

    # Create directory if it does not exist
    dst_split = dst.rsplit(':')
    host = dst_split[0]
    dst = dst_split[1]
    mkdir = 'ssh {usr}@{host} mkdir -p {dst}'.format(
        usr=usr,
        host=host,
        dst=dst
    )

    if dry_run:
        print mkdir
        print command
    else:
        subprocess.call(mkdir, shell=True)
        subprocess.call(command, shell=True)


def get_release_version():
    ''' Returns the version number (X.Y.Z) from the zip file to upload,
        excluding the SemVer suffix
    '''
    try:
        zipfile = glob.glob('adodb-*.zip')[0]
    except IndexError:
        print "ERROR: release zip file not found in '%s'" % release_path
        sys.exit(1)

    try:
        version = re.search(
            "^adodb-([\d]+\.[\d]+\.[\d]+)(-(alpha|beta|rc)\.[\d]+)?\.zip$",
            zipfile
            ).group(1)
    except AttributeError:
        print "ERROR: unable to extract version number from '%s'" % zipfile
        print "       Only 3 groups of digits separated by periods are allowed"
        sys.exit(1)

    return version


def sourceforge_target_dir(version):
    ''' Returns the SourceForge target directory, relative to the root
        directory (defined in sf_files global variable): basedir/subdir, with
        basedir:
        - for ADOdb version 5: adodb-php5-only
        - for newer versions:  adodbX (where X is the major version number)
        subdir:
        - if version >= 5.21: adodb-X.Y
        - for older versions: adodb-XYZ-for-php5
    '''
    major_version = int(version.rsplit('.')[0])

    # Base directory
    if major_version == 5:
        directory = 'adodb-php5-only/'
    else:
        directory = 'adodb{}/'.format(major_version)

    # Keep only X.Y (discard patch number and pre-release suffix)
    short_version = version.split('-')[0].rsplit('.', 1)[0]

    if LooseVersion(version) >= LooseVersion('5.21'):
        directory += "adodb-" + short_version
    else:
        directory += "adodb-{}-for-php5".format(short_version.replace('.', ''))

    return directory


def load_env():
    global api_key

    # Load the config file
    env_file = path.join(path.dirname(path.abspath(__file__)), 'env.yml')
    try:
        stream = file(env_file, 'r')
        y = yaml.safe_load(stream)
    except IOError:
        print("ERROR: Environment file {} not found".format(env_file))
        sys.exit(3)
    except yaml.parser.ParserError as e:
        print("ERROR: Invalid Environment file")
        print(e)
        sys.exit(3)

    api_key = y['api_key']


def process_command_line():
    ''' Retrieve command-line options and set global variables accordingly
    '''
    global dry_run, username, release_path, skip_upload

    # Get command-line options
    try:
        opts, args = getopt.gnu_getopt(sys.argv[1:], options, long_options)
    except getopt.GetoptError, err:
        print str(err)
        usage()
        sys.exit(2)

    # Default values for flags
    username = getpass.getuser()
    skip_upload = False
    dry_run = False

    for opt, val in opts:
        if opt in ("-h", "--help"):
            usage()
            sys.exit(0)

        elif opt in ("-u", "--user"):
            username = val

        elif opt in ("-s", "--skip-upload"):
            skip_upload = True

        elif opt in ("-n", "--dry-run"):
            print("Dry-run mode - files will not be uploaded or modified")
            dry_run = True

    # Mandatory parameters
    # (none)

    # Change to release directory, current if not specified
    try:
        release_path = args[0]
        os.chdir(release_path)
    except IndexError:
        release_path = os.getcwd()


def upload_release_files():
    ''' Upload release files from source directory to SourceForge
    '''
    version = get_release_version()
    target = sf_files + sourceforge_target_dir(version)

    print
    print "Uploading release files..."
    print "  Source:", release_path
    print "  Target: " + target
    print "  Files:  " + ', '.join(glob.glob('*'))
    print
    call_rsync(
        username,
        "",
        path.join(release_path, "*"),
        target
    )
    print


def set_sourceforge_file_info():
    global api_key, dry_run

    print("Updating uploaded files information")

    base_url = sf_api_url.format(
        sourceforge_target_dir(get_release_version())
        )
    headers = {'Accept': 'application/json"'}

    # Loop through uploaded files
    for file in glob.glob('adodb-*'):
        print("  " + file)

        # Determine defaults based on file extension
        ext = file.split('.', 3)[3]
        if ext == 'zip':
            defaults = ['windows']
        elif ext == 'tar.gz':
            defaults = ['linux', 'mac', 'bsd', 'solaris', 'others']
        else:
            print("WARNING: Unknown extension for file " + file)
            continue

        # SourceForge API request
        url = path.join(base_url, file)
        payload = {
            'default': defaults,
            'api_key': api_key
            }
        if dry_run:
            req = requests.Request('PUT', url, headers=headers, params=payload)
            r = req.prepare()
            print("    Calling SourceForge Release API: " + r.url)
        else:
            req = requests.put(url, headers=headers, params=payload)

            # Print results
            if req.status_code == requests.codes.ok:
                result = json.loads(req.text)['result']
                print("    Download default for: " + result['x_sf']['default'])
            else:
                if req.status_code == requests.codes.unauthorized:
                    err = "access denied"
                else:
                    err = "SourceForge API call failed"
                print("ERROR: {} - check API key".format(err))
                break


def main():
    # Start upload process
    print "ADOdb release upload script"

    load_env()
    process_command_line()

    global skip_upload
    if skip_upload:
        print("Skipping upload of release files")
    else:
        upload_release_files()

    set_sourceforge_file_info()

# end main()

if __name__ == "__main__":
    main()
