# BioGRID ORCA View Generator
This directory contains the entirety of the BioGRID ORCA View Generator App

## System Requirements
To use all of the tools contained within, you require at least the following:

+ MySQL 5.5+ (https://www.mysql.com/)
+ Python 2.7
+ Additional Python Libraries: Flask

## Settings
+ Main settings are in the global config in the "config" directory of the root ORCA file structure
+ ViewGenerator specific settings are in the "VIEWGENERATOR" category of the config.json file
+ If hosting the ViewGenerator on an alternate system than the main web application, you will need to ensure it is accessible through your firewall from the web application host. 

## How to Operate
+ Execute **./ViewGeneratorService.py** - This will launch a flask based webservice designed to handle incoming requests for the building and uploading of various complex graphical views.
+ Execute **python ViewGeneratorCRON.py** - Use tis version if you'd rather run the view generating tool as a repetitive CRON job. The downside to this technique is you are limited to having it run only as frequently as your cron job configuration allows (usually no more than once every minute).