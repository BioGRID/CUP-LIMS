# BioGRID ORCA Data Processing
This directory contains the entirety of the BioGRID ORCA Data Processing CRON App. This is a tool designed for processing uploaded files at regular intervals via a CRON task.

## System Requirements
To use all of the tools contained within, you require at least the following:

+ MySQL 5.5+ (https://www.mysql.com/)
+ Python 2.7
+ Additional Python Libraries: argparse, math

## Settings
+ Main settings are in the global config in the "config" directory of the root ORCA file structure

## How to Operate
+ Execute **python DataProcessingCRON.py** - This will start a process (if it's not already operating) that will begin processing of uploaded raw data files into the database. It is specifically built to be run at regular intervals via CRON automation.