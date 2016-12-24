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

from classes import Lookups, ParserHandler, TwoColumnParser, GeneSummaryHandler

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

	cursor.execute( "SELECT file_id, file_name, file_state, experiment_id, file_isbackground FROM " + Config.DB_MAIN + ".files WHERE file_state IN ('new','redo') AND file_status='active' ORDER BY experiment_id ASC, file_isbackground DESC LIMIT 2" )
	
	if cursor.rowcount > 0 :
	
		# Establish parser handler
		parserHandler = ParserHandler.ParserHandler( Database.db )
	
		# Get Experiment Code Hash
		expCodes = lookups.buildExperimentCodeHash( )
	
		# Get sgRNA Hash
		sgRNAs = lookups.buildSGRNAHash( )
		
		# Gene Summaries Handler
		geneSummaryHandler = GeneSummaryHandler.GeneSummaryHandler( Database.db )
		
		# Background Data 
		backgroundData = { }
		backgroundTotals = { }
	
		for row in cursor.fetchall( ) :
		
			expCode = expCodes[str(row['experiment_id'])]
		
			with open( Config.PROCESSED_DIR + "/" + expCode + "/" + row['file_name'] ) as inFile :
				# PROCESS FILE
				if row['file_state'] == "redo" :
					parserHandler.delExistingRecords( row['file_id'] )
					
				parserHandler.setFileState( row['file_id'], "inprogress", [] )
			
				# read entire file into list
				lines = inFile.readlines( )
				
				firstLine = lines[0].split( "\t" )
				reads = {}
				readTotal = 0
				errors = { }
				if len(firstLine) == 2 :
					# TWO COL FORMAT
					twoColParser = TwoColumnParser.TwoColumnParser( row['file_id'], lines, Database.db, sgRNAs )
					reads, readTotal, errors = twoColParser.parse( )
					
					parserHandler.setFileReadTotal( row['file_id'], readTotal )
					
			if row['file_isbackground'] == 1 :
				# Load Background Data
				backgroundData[str(row['file_id'])] = reads
				backgroundTotals[str(row['file_id'])] = readTotal
			else :
				# File data in reads
				# step through backgrounds, perform calculations
				geneSummaryHandler.buildGeneSummary( row['file_id'], reads, readTotal, backgroundData, backgroundTotals )
			
			if len(errors) > 0 :
				parserHandler.setFileState( row['file_id'], "error", errors )
			else :
				parserHandler.setFileState( row['file_id'], "parsed", [] )
					
sys.exit(0)