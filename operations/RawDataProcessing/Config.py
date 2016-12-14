
# Load the config file, and create variables
# that can be referenced within other files

import json
import os

BASE_DIR = os.path.dirname(os.path.realpath(__file__))

with open( BASE_DIR + "/../../config/config.json", "r" ) as configFile :
	data = configFile.read( )

data = json.loads( data )

# DATABASE VARS
DB_HOST = data['DATABASE']['DB_HOST']
DB_USER = data['DATABASE']['DB_USER']
DB_PASS = data['DATABASE']['DB_PASS']
DB_QUICK = data['DATABASE']['DB_QUICK']
DB_MAIN = data['DATABASE']['DB_ORCA']

# FILE PATH VARS
BASE_PATH = data['DIRECTORIES']['BASE_PATH']
WEB_DIR = BASE_PATH + "/" + data['DIRECTORIES']['WEB_DIR']
UPLOAD_DIR = WEB_DIR + "/" + data['DIRECTORIES']['UPLOAD_DIR']
PROCESSED_DIR = UPLOAD_DIR + "/" + data['DIRECTORIES']['UPLOAD_PROCESSED_DIR']