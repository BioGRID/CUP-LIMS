#!/bin/env python

# Take a directory of zipped or unzipped files
# and generate a set of sgRNAs

import sys, string, argparse
import MySQLdb
import Config
import Database

from classes import Lookups

# Process Command Line Input
argParser = argparse.ArgumentParser( description = 'Input sgRNA data from a formatted annotation file' )
argParser.add_argument( '--input', '-i', action='store', type = str, nargs = 1, help = 'Input file to parse' )
inputArgs = argParser.parse_args( )

if not inputArgs.input :
	print "You Must pass a file name: '--input <file>'"
	sys.exit( )

# Initialize Lookups, then make a list of existing sgRNAs
lookups = Lookups.Lookups( Database.db )
sgRNAs = lookups.buildSGRNAHash( )
sgRNAGroupReference = lookups.buildSGRNAGroupReferenceHash( )
sgRNAtoGroup = lookups.buildSGRNAToGroupMapping( )

# Parse all the lines into a List
with open( inputArgs.input[0], "r" ) as fp :
	lines = fp.readlines( )

# Step through each line one by one
with Database.db as cursor :

	for line in lines :
		line = line.strip( )
		if len(line) <= 0 :
			continue
		
		# Test to see if sgRNA Sequence already exists
		# If not, add it and add mapping to sgRNAs List
		splitLine = line.split( "\t" )
		sgRNA = str(splitLine[0].strip( ))
		if sgRNA not in sgRNAs :
			cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".sgRNAs VALUES ('0',%s,NOW( ),'active' )", [splitLine[0]] )
			sgRNAID = Database.db.insert_id( )
			sgRNAs[sgRNA] = str(sgRNAID)
		
		# Fetch the sgRNAID
		sgRNAID = sgRNAs[sgRNA]
		
		for index in range( 1, len(splitLine) ) :
		
			# Check to see if we already have a reference using this identifier
			currentID = str(splitLine[index]).strip( )
			if currentID not in sgRNAGroupReference :
				cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".sgRNA_groups VALUES( '0', '-', '-', %s, %s, NOW( ), 'active' )", [currentID, "EXTMAP"] )
				groupID = Database.db.insert_id( )
				sgRNAGroupReference[currentID] = str(groupID)
				
			groupID = sgRNAGroupReference[currentID]
			
			# Add Mapping if none exists
			mapping = groupID + "|" + sgRNAID
			if mapping not in sgRNAtoGroup :
				cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".sgRNA_group_mappings VALUES( '0', %s, %s, NOW( ), 'active' )", [groupID, sgRNAID] )
				
				# cursor.execute( "SELECT gene_id, quick_identifier_value FROM " + Config.DB_QUICK + ".quick_identifiers WHERE quick_identifier_value=%s AND quick_identifier_type IN ('OFFICIAL SYMBOL') AND organism_id='9606' GROUP BY gene_id", [splitLine[index]] )
				
				# idInfo = ""
				# if cursor.rowcount == 1 :
					# idInfo = cursor.fetchone( )
				# else :
				
					# cursor.execute( "SELECT gene_id, quick_identifier_value FROM " + Config.DB_QUICK + ".quick_identifiers WHERE quick_identifier_value=%s AND quick_identifier_type IN ('SYNONYM', 'ORDERED LOCUS') AND organism_id='9606' GROUP BY gene_id", [splitLine[index]] )
					
					# if cursor.rowcount == 1 :
						# idInfo = cursor.fetchone( )
						
				# if idInfo != "" :
					
					# cursor.execute( "INSERT INTO " + Config.DB_NAME + ".sgRNA_identifiers VALUES( %s, %s, %s, %s, NOW( ), 'active' )", [str(sgRNAID), idInfo[0], "BioGRID Gene ID", "BioGRID"] )
					
					# cursor.execute( "SELECT gene_id, quick_identifier_value FROM " + Config.DB_QUICK + ".quick_identifiers WHERE quick_identifier_type=%s AND gene_id=%s LIMIT 1", ["ENTREZ_GENE",idInfo[0]] )
					
					# egRow = cursor.fetchone( )
					# if egRow != None :
						# cursor.execute( "INSERT INTO " + Config.DB_NAME + ".sgRNA_identifiers VALUES( %s, %s, %s, %s, NOW( ), 'active' )", [str(sgRNAID), egRow[1], "Entrez Gene ID", "BioGRID"] )
				
			Database.db.commit( )
		Database.db.commit( )
	Database.db.commit( )
			
sys.exit( )