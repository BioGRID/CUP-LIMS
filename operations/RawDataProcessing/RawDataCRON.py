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

from classes import Lookups, ParserHandler, TwoColumnParser

# Setup a PID file to prevent this CRON job from
# executing multiple times if currently in progress
# already

pid = str( os.getpid( ) )
pidfile = "/tmp/ORCA_RawDataCRON.pid"

if os.path.isfile(pidfile) :
	print "CRON ALREADY RUNNING %s" % pidfile
	sys.exit( )
	
with open( pidfile, 'w' ) as outFile :
	outFile.write( pid )
	
def killPID( ) :
	os.unlink( pidfile )
	
atexit.register( killPID )

# Begin File Processing

with Database.db as cursor :

	lookups = Lookups.Lookups( Database.db )

	cursor.execute( "SELECT file_id, file_name, file_state, experiment_id FROM " + Config.DB_MAIN + ".files WHERE file_state IN ('new','redo') AND file_status='active' ORDER BY experiment_id ASC" )
	
	if cursor.rowcount > 0 :
	
		# Establish parser handler
		parserHandler = ParserHandler.ParserHandler( Database.db )
	
		# Get Experiment Code Hash
		expCodes = lookups.buildExperimentCodeHash( )
	
		# Get sgRNA Hash
		sgRNAs = lookups.buildSGRNAHash( )
	
		for row in cursor.fetchall( ) :
		
			expCode = expCodes[str(row['experiment_id'])]
		
			with open( Config.PROCESSED_DIR + "/" + expCode + "/" + row['file_name'] ) as inFile :
				# PROCESS FILE
				if row['file_state'] == "redo" :
					parserHandler.delExistingRecords( row['file_id'] )
					
				parserHandler.setFileState( row['file_id'], "inprogress", [] )
			
				lines = inFile.readlines( )
				
				firstLine = lines[0].split( "\t" )
				if len(firstLine) == 2 :
					# TWO COL FORMAT
					twoColParser = TwoColumnParser.TwoColumnParser( row['file_id'], lines, Database.db, sgRNAs )
					errors = twoColParser.parse( )
					
					if len(errors) > 0 :
						parserHandler.setFileState( row['file_id'], "error", errors )
					else :
						parserHandler.setFileState( row['file_id'], "parsed", [] )
						
			break
					
sys.exit(0)