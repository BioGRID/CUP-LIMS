import sys, string
import Config
import datetime

from classes import Lookups

class RawAnnotatedView( ) :

	"""Generate a raw annotated view table based on passed in parameters"""

	def __init__( self, db, sgRNAToGroup, sgRNAGroups, sgRNAGroupToGene ) :
		self.db = db
		self.cursor = self.db.cursor( )
		self.sgRNAToGroup = sgRNAToGroup
		self.sgRNAGroups = sgRNAGroups
		self.sgRNAGroupToGene = sgRNAGroupToGene
		
		self.lookups = Lookups.Lookups( self.db )
		self.sgRNAHash = self.lookups.buildSGRNAIDHash( )
		
	def build( self, view, fileMap, rawData ) :
		"""Create a raw annotated view table based on the view information passed in"""
		
		# Get a list of files
		fileList = fileMap.keys( )
		mapID = str(fileMap[fileList[0]]["MAP"])
				
		# Create the database table for storing the view 
		self.createView( view )
		
		# Process each file one by one
		self.generateRawAnnotatedView( view, fileList, mapID, rawData )
			
		return { }
	
	def generateRawAnnotatedView( self, view, fileList, mapID, rawData ) :
	
		readCount = 0
		for fileID in fileList :
			reads = rawData.fetchReads( fileID )
			
			for sgRNAID, readScore in reads.items( ) :
				sgRNASeq = self.sgRNAHash[sgRNAID]
				
				groupIDs = []
				if sgRNAID in self.sgRNAToGroup[mapID] :
					groupIDs = self.sgRNAToGroup[mapID][sgRNAID]
				
				groupNames = []
				for groupID in groupIDs :
					groupInfo = self.sgRNAGroups[groupID]
					groupName = groupInfo['sgrna_group_reference']
					
					# Initialize with basic annotation data
					if groupID in self.sgRNAGroupToGene :
						biogridAnn = self.sgRNAGroupToGene[groupID]
						
						if biogridAnn['official_symbol'] != "-" :
							groupName = biogridAnn['official_symbol']
							
					groupNames.append( groupName )
					
				self.cursor.execute( "INSERT INTO " + Config.DB_VIEWS + ".view_" + view['view_code'] + " VALUES( '0', %s, %s, %s, %s, %s )", [sgRNAID, sgRNASeq, "|".join(groupIDs), "|".join(groupNames), readScore] )
				
				readCount = readCount + 1
				if (readCount % 20000) == 0 :
					self.db.commit( )
					
			self.db.commit( )
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