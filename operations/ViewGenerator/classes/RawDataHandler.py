import sys, string
import Config
import datetime
import json

class RawDataHandler( ) :

	"""Methods for handing of raw data from the database"""

	def __init__( self, db ) :
		self.db = db
		self.cursor = self.db.cursor( )
		self.files = { }
		
	def loadRawData( self, fileIDs ) :
		"""Load the raw data for a file into a dict for faster lookup"""
		filesToLoad = set( )
		for fileID in fileIDs :
			if str(fileID) not in self.files :
				filesToLoad.add( str(fileID) )
		
		if len(filesToLoad) > 0 :
			formatFileIDs = ','.join( ['%s'] * len( filesToLoad ))
			query = "SELECT * FROM " + Config.DB_MAIN + ".raw_reads WHERE file_id IN (%s)"
			query = query % formatFileIDs
			self.cursor.execute( query, tuple( filesToLoad ))
			
		for row in self.cursor.fetchall( ) :
			if str(row['file_id']) not in self.files :
				self.files[str(row['file_id'])] = { }
				
			self.files[str(row['file_id'])][str(row['sgrna_id'])] = row['raw_read_count']
			
	def fetchReads( self, fileID ) :
		"""Lookup the raw data for a single file"""
		if str(fileID) in self.files :
			return self.files[str(fileID)]
			
		return False