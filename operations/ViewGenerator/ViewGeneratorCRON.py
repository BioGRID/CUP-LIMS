#!/bin/env python

# This script will need to be setup as a cron job
# it will watch periodically for new files to appear 
# in the file table, and begin to process them
# recording their results in the table for status 
# updates

import sys, string
import Config
import Database
import argparse
import atexit, os, time
import math

from classes import CRONTask

cron = CRONTask.CRONTask( )
cron.run( )
cron.killPID( )
					
sys.exit(0)