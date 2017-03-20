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

from classes import Lookups, ParserHandler, TwoColumnParser, AnnotationParser

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
	sgRNAs = None
	officialHash = None
	synonymHash = None
	groupHash = None

	# ---------------------------------
	# Parse Annotation Files
	
	cursor.execute( "SELECT annotation_file_id, annotation_file_name, annotation_file_code, annotation_file_state, organism_id FROM " + Config.DB_MAIN + ".annotation_files WHERE annotation_file_state IN ('new','redo') AND annotation_file_status='active' ORDER BY organism_id ASC" )
	
	if cursor.rowcount > 0 :
	
		# Establish parser handler
		parserHandler = ParserHandler.ParserHandler( Database.db )
	
		# Get sgRNA Hash
		if sgRNAs == None :
			sgRNAs = lookups.buildSGRNAHash( )
		
		previousOrganism = None
		redoGroups = False
		for row in cursor.fetchall( ) :
		
			# Get official Hash
			if officialHash == None or previousOrganism != str(row['organism_id']) :
				officialHash = lookups.buildOfficialNameHash( str(row['organism_id']) )
				
			# Get synonym Hash
			if synonymHash == None or previousOrganism != str(row['organism_id']) :
				synonymHash = lookups.buildSynonymNameHash( str(row['organism_id']) )
				
			# Get group Hash
			if groupHash == None or redoGroups == True :
				groupHash = lookups.buildGroupHash( )
				redoGroups = False
		
			with open( Config.PROCESSED_DIR + "/" + row['annotation_file_code'] + "/" + row['annotation_file_name'] ) as inFile :
				# PROCESS FILE
				if row['annotation_file_state'] == "redo" :
					parserHandler.deprecateAnnotationMappings( row['annotation_file_id'] )
					
				parserHandler.setAnnotationFileState( row['annotation_file_id'], "inprogress", [] )
				
				# read entire file into list
				lines = inFile.readlines( )
				
				firstLine = lines[0].split( "\t" )
				if len(firstLine) >= 2 :
					annParser = AnnotationParser.AnnotationParser( row['annotation_file_id'], lines, Database.db, sgRNAs, officialHash, synonymHash, groupHash )
					errors = annParser.parse( )
					print "GROUP HASH SIZE: " + str(len(groupHash))
			
			if len(errors) > 0 :
				redoGroups = True
				parserHandler.setAnnotationFileState( row['annotation_file_id'], "error", errors )
			else :
				parserHandler.setAnnotationFileState( row['annotation_file_id'], "parsed", [] )
				
			# Set this so we only rebuild hashes if the organism changes
			previousOrganism = str(row['organism_id'])
				
		Database.db.commit( )
	
	# ---------------------------------
	# Parse Raw Data Files
	
	cursor.execute( "SELECT file_id, file_name, file_code, file_state, file_iscontrol FROM " + Config.DB_MAIN + ".files WHERE file_state IN ('new','redo') AND file_status='active' ORDER BY file_code ASC, file_iscontrol DESC" )
	
	if cursor.rowcount > 0 :
	
		# Establish parser handler
		parserHandler = ParserHandler.ParserHandler( Database.db )
	
		# Get sgRNA Hash
		if sgRNAs == None :
			sgRNAs = lookups.buildSGRNAHash( )
			
		for row in cursor.fetchall( ) :
		
			with open( Config.PROCESSED_DIR + "/" + row['file_code'] + "/" + row['file_name'] ) as inFile :
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
			
			if len(errors) > 0 :
				parserHandler.setFileState( row['file_id'], "error", errors )
			else :
				parserHandler.setFileState( row['file_id'], "parsed", [] )
				
		Database.db.commit( )
				
sys.exit(0)