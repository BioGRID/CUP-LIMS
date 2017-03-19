#!/bin/env python

# Take a set of sgRNA_Groups and attempt to map them
# to better references

import sys, string, argparse
import MySQLdb
import Config
import Database

from classes import Lookups

with Database.db as cursor :

	cursor.execute( "SELECT sgrna_group_id, sgrna_group_reference_original, sgrna_group_reference_original_type FROM " + Config.DB_MAIN + ".sgRNA_groups WHERE sgrna_group_reference='-'" )
	
	for row in cursor.fetchall( ) :
		
		# See if we can find a match with an Official Symbol
		cursor.execute( "SELECT gene_id FROM " + Config.DB_QUICK + ".quick_identifiers WHERE quick_identifier_value=%s AND quick_identifier_type IN ('OFFICIAL SYMBOL') AND organism_id='9606' GROUP BY gene_id", [row['sgrna_group_reference_original']] )
		
		idInfo = ""
		if cursor.rowcount == 1 :
			idInfo = cursor.fetchone( ) 
		else :
				
			cursor.execute( "SELECT gene_id FROM " + Config.DB_QUICK + ".quick_identifiers WHERE quick_identifier_value=%s AND quick_identifier_type IN ('SYNONYM', 'ORDERED LOCUS') AND organism_id='9606' GROUP BY gene_id", [row['sgrna_group_reference_original']] )
				
			if cursor.rowcount == 1 :
				idInfo = cursor.fetchone( )
					
		if idInfo != "" :
			cursor.execute( "UPDATE " + Config.DB_MAIN + ".sgRNA_groups SET sgrna_group_reference=%s, sgrna_group_reference_type=%s WHERE sgrna_group_id=%s", [idInfo['gene_id'], 'BIOGRID', row['sgrna_group_id']] )
		else :
			cursor.execute( "UPDATE " + Config.DB_MAIN + ".sgRNA_groups SET sgrna_group_reference=%s, sgrna_group_reference_type=%s WHERE sgrna_group_id=%s", [row['sgrna_group_reference_original'], row['sgrna_group_reference_original_type'], row['sgrna_group_id']] )
				
		Database.db.commit( )
	Database.db.commit( )
			
sys.exit( )