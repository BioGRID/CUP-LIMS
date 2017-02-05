import sys, string
import Config
import datetime
import copy
import json
import math

class RawAnnotatedView( ) :

	"""Generate a raw annotated view table based on passed in parameters"""

	def __init__( self, db, sgRNAToGroup, sgRNAGroups, sgRNAGroupToBioGRID ) :
		self.db = db
		self.cursor = self.db.cursor( )
		self.sgRNAToGroup = sgRNAToGroup
		self.sgRNAGroups = sgRNAGroups
		self.sgRNAGroupToBioGRID = sgRNAGroupToBioGRID
		
	def build( self, view, fileMap ) :
		"""Create a raw annotated view table based on the view information passed in"""
		
		# Get a list of files
		fileList = fileMap.keys( )
				
		# Create the database table for storing the view 
		self.createView( view )
		
		# Process each file one by one
		self.generateRawAnnotatedView( view, fileList )
			
		return { }
	
	def generateRawAnnotatedView( self, view, fileList ) :
	
		# Insert formatted data to database
		formatCols = ','.join( ['%s'] * len(fileList) )
		query = "INSERT INTO " + Config.DB_VIEWS + ".view_" + view['view_code'] + " ( SELECT r.raw_read_id, r.sgrna_id, s.sgrna_sequence, '-', '-', r.raw_read_count FROM " + Config.DB_MAIN + ".raw_reads r LEFT JOIN " + Config.DB_MAIN + ".sgRNAs s ON (r.sgrna_id=s.sgrna_id) WHERE r.file_id IN ( %s ) )"
		query = query % formatCols
		self.cursor.execute( query, tuple(fileList) )
		
		self.db.commit( )
	
	def createView( self, view ) :
		"""Build a MySQL Table that supports this view"""
		
		# CREATE BASIC STRUCTURE FOR THE TABLE
		query = "CREATE TABLE " + Config.DB_VIEWS + ".view_" + view['view_code'] + "("
		
		tableFields = []
		tableFields.append( "raw_read_id BIGINT(10) NOT NULL AUTO_INCREMENT" )
		tableFields.append( "sgrna_id BIGINT(10) NOT NULL" )
		tableFields.append( "sgrna_sequence VARCHAR(20) NOT NULL" )
		tableFields.append( "sgrna_group_ids VARCHAR(255) NOT NULL" )
		tableFields.append( "group_names VARCHAR(255) NOT NULL" )
		tableFields.append( "raw_read_count MEDIUMINT(10) NOT NULL" )
		
		query = query + ",".join( tableFields )
		query = query + ",PRIMARY KEY (raw_read_id)"
		query = query + ") ENGINE=INNODB DEFAULT CHARSET=latin1;"
		
		self.cursor.execute( query )
		
		# ADD INDEXES
		query = "ALTER TABLE " + Config.DB_VIEWS + ".view_" + view['view_code']
		
		tableIndexes = []
		tableIndexes.append( "ADD KEY (sgrna_id)" )
		tableIndexes.append( "ADD KEY (sgrna_sequence)" )
		tableIndexes.append( "ADD KEY (sgrna_group_ids)" )
		tableIndexes.append( "ADD KEY (group_names)" )
		tableIndexes.append( "ADD KEY (raw_read_count)" )
		
		query = query + " " + ",".join( tableIndexes ) + ";"
		
		self.cursor.execute( query )