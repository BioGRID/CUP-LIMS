import sys, string
import Config
import datetime
import copy
import json
import math

class MatrixView( ) :

	"""Generate a matrix view table based on passed in parameters"""

	def __init__( self, db, sgRNAToGroup, sgRNAGroups, sgRNAGroupToBioGRID ) :
		self.db = db
		self.cursor = self.db.cursor( )
		self.conditionReference = { }
		self.conditionBlock = []
		self.sgRNAToGroup = sgRNAToGroup
		self.sgRNAGroups = sgRNAGroups
		self.sgRNAGroupToBioGRID = sgRNAGroupToBioGRID
		self.matrix = { }
		self.logPad = 1
		self.colCount = 13
		self.max = 0
		self.min = 0
		
	def build( self, view, fileMap, rawData, fileHash ) :
		"""Create a matrix view table based on the view information passed in"""
		self.conditionReference = { }
		self.conditionBlock = []
		self.matrix = { }
		
		# Build a list of all conditions for the X axis
		fileList = { }
		for fileID, backgroundIDs in fileMap.iteritems( ) :
			bgSet = backgroundIDs.split( "|" )
			for bg in sorted(bgSet) :
				fileList[str(fileID) + "|" + str(bg)] = 0
				
		# Create the database table for storing the view 
		self.createView( view, fileList )
		
		# Process each file one by one
		for fileInfo in fileList :
			fileInfo = fileInfo.split( "|" )
			fileID = fileInfo[0]
			backgroundID = fileInfo[1]
			self.generateGroupSummary( view, fileID, backgroundID, rawData, fileHash )
			
		# Collapse down conditions into mean values
		self.processView( view )
		
		# Return reference details
		referenceHash = { }
		referenceHash['FILES'] = self.buildReferenceHash( fileHash )
		referenceHash['MAX'] = self.max
		referenceHash['MIN'] = self.min
		return referenceHash
		
	def buildReferenceHash( self, fileHash ) :
		"""Create a lookup has that maps condition columns to the set of files that comprised it"""
		referenceHash = { }
		for fileRef, condName in self.conditionReference.items( ) :
			fileInfo = fileRef.split( "|" )
			file = fileHash[fileInfo[0]]
			bgFile = fileHash[fileInfo[1]]
			referenceHash[condName] = { "FILE" : { "ID" : file['file_id'], "NAME" : file['file_name'], "EXP_ID" : file['experiment_id'] }, "BG" : { "ID" : bgFile['file_id'], "NAME" : bgFile['file_name'], "EXP_ID" : bgFile['experiment_id'] } }
		
		return referenceHash
	
	def generateGroupSummary( self, view, fileID, backgroundID, rawData, fileHash ) :
	
			fileRef = fileID + "|" + backgroundID
			
			reads = rawData.fetchReads( fileID )
			readSGRNAIDs = reads.keys( )
			readFile = fileHash[fileID]
			
			backgroundReads = rawData.fetchReads( backgroundID )
			backgroundSGRNAIDs = backgroundReads.keys( )
			backgroundFile = fileHash[backgroundID]
			
			# Get universal set of all unique sgRNA ids represented
			allSGRNA = set(readSGRNAIDs + backgroundSGRNAIDs)
			
			# Step through each one and perform required calculation
			count = 0
			for sgRNAID in allSGRNA :
				
				# Only use it if we can find a mapping to a group
				if str(sgRNAID) in self.sgRNAToGroup :
					groupID = self.sgRNAToGroup[str(sgRNAID)]
					
					readScore = 0
					if str(sgRNAID) in reads :
						readScore = float(reads[str(sgRNAID)])
						
					backgroundScore = 0
					if str(sgRNAID) in backgroundReads :
						backgroundScore = float(backgroundReads[str(sgRNAID)])

					if groupID not in self.matrix :
						self.matrix[groupID] = self.initializeConditionSet( )
					
					# 1 is Log2FoldChange
					if str(view['view_value_id']) == "1" :
						logChange = self.calculateLog2FoldChange( readScore, backgroundScore, readFile['file_readtotal'], backgroundFile['file_readtotal'] )
						self.matrix[groupID][self.conditionReference[fileRef]].append(logChange)
						
	def initializeConditionSet( self ) :
		"""Initialize a new group with a dict containing all possible conditions"""
		conditionSet = { }
		for condition in self.conditionBlock :
			conditionSet[condition] = []
			
		return conditionSet
							
	def calculateLog2FoldChange( self, readCount, bgReadCount, readTotal, bgReadTotal ) :
		"""Generate a log2foldchange value"""
		readScore = (float(readCount) + self.logPad) / (float(bgReadCount) + self.logPad)
		totalScore = (float(readTotal) + self.logPad) / (float(bgReadTotal) + self.logPad)
		return math.log(readScore, 2) - math.log(totalScore, 2)
		
	def processView( self, view ) :
		"""Process the view to the database"""
		
		for groupID, conditions in self.matrix.items( ) :
			conditionSet = { }
			groupInfo = self.sgRNAGroups[groupID]
			groupName = groupInfo['sgrna_group_reference']
			
			# Initialize with basic annotation data
			if groupID in self.sgRNAGroupToBioGRID :
				biogridAnn = self.sgRNAGroupToBioGRID[groupID]
				
				if biogridAnn['official_symbol'] != "-" :
					groupName = biogridAnn['official_symbol']
				
				row = [groupInfo['sgrna_group_id'], groupInfo['sgrna_group_reference'], groupInfo['sgrna_group_reference_type'], groupName, biogridAnn['official_symbol'], biogridAnn['systematic_name'], biogridAnn['aliases'], biogridAnn['definition'], biogridAnn['organism_id'], biogridAnn['organism_common_name'], biogridAnn['organism_official_name'], biogridAnn['organism_abbreviation'], biogridAnn['organism_strain']]
			else :
				row = [groupInfo['sgrna_group_id'], groupInfo['sgrna_group_reference'], groupInfo['sgrna_group_reference_type'], groupName, "-", "-", "-", "-", "0", "-", "-", "-", "-"]
			
			# Perform any remaining calculations on results
			for conditionRef, conditionScores in conditions.items( ) :
				# 1 is Log2FoldChange
				if str(view['view_value_id']) == "1" :
					collapsedValue = self.calculateMean( conditionScores )
					
					# Find the Max and Min for the entire view
					if collapsedValue < self.min :
						self.min = collapsedValue
					elif collapsedValue > self.max :
						self.max = collapsedValue
						
					conditionSet[conditionRef] = collapsedValue
			
			# Insert condition values in the correct ordering
			for condition in self.conditionBlock :
				row.append( conditionSet[condition] )
			
			# Insert formatted data to database
			formatCols = ','.join( ['%s'] * self.colCount)
			query = "INSERT INTO " + Config.DB_VIEWS + ".view_" + view['view_code'] + " VALUES ( %s )"
			query = query % formatCols
			self.cursor.execute( query, tuple(row) )
					
		self.db.commit( )
		
	def calculateMean( self, values ) :
		"""Calculate the mean of a set of values"""
		if len(values) <= 0 :
			return 0
			
		return float(sum(values)) / len(values)
	
	def createView( self, view, fileList ) :
		"""Build a MySQL Table that supports this view"""
		
		# CREATE BASIC STRUCTURE FOR THE TABLE
		query = "CREATE TABLE " + Config.DB_VIEWS + ".view_" + view['view_code'] + "("
		
		tableFields = []
		tableFields.append( "sgrna_group_id BIGINT(10) NOT NULL AUTO_INCREMENT" )
		tableFields.append( "sgrna_group_reference VARCHAR(255) NOT NULL" )
		tableFields.append( "sgrna_group_reference_type VARCHAR(255) NOT NULL" )
		tableFields.append( "group_name VARCHAR(255) NOT NULL" )
		tableFields.append( "official_symbol VARCHAR(255) NOT NULL" )
		tableFields.append( "systematic_name VARCHAR(255) NOT NULL" )
		tableFields.append( "aliases LONGTEXT NOT NULL" )
		tableFields.append( "definition TEXT NOT NULL" )
		tableFields.append( "organism_id BIGINT(10) NOT NULL" )
		tableFields.append( "organism_common_name VARCHAR(255) NOT NULL" )
		tableFields.append( "organism_official_name VARCHAR(255) NOT NULL" )
		tableFields.append( "organism_abbreviation VARCHAR(255) NOT NULL" )
		tableFields.append( "organism_strain VARCHAR(255) NOT NULL" )
		
		conditionCount = 1
		for fileRef, fileReads in fileList.iteritems( ) :
			tableFields.append( "condition" + str(conditionCount) + " DOUBLE NOT NULL" )
			self.conditionReference[fileRef] = "condition" + str(conditionCount)
			self.conditionBlock.append( "condition" + str(conditionCount) )
			conditionCount = conditionCount + 1
			
		self.colCount = self.colCount + len(self.conditionBlock)
		
		query = query + ",".join( tableFields )
		query = query + ",PRIMARY KEY (sgrna_group_id)"
		query = query + ") ENGINE=INNODB DEFAULT CHARSET=latin1;"
		
		self.cursor.execute( query )
		
		# ADD INDEXES
		query = "ALTER TABLE " + Config.DB_VIEWS + ".view_" + view['view_code']
		
		tableIndexes = []
		tableIndexes.append( "ADD KEY (sgrna_group_reference)" )
		tableIndexes.append( "ADD KEY (sgrna_group_reference_type)" )
		tableIndexes.append( "ADD KEY (group_name)" )
		tableIndexes.append( "ADD KEY (official_symbol)" )
		tableIndexes.append( "ADD KEY (systematic_name)" )
		tableIndexes.append( "ADD KEY (organism_id)" )
		tableIndexes.append( "ADD KEY (organism_common_name)" )
		tableIndexes.append( "ADD KEY (organism_official_name)" )
		tableIndexes.append( "ADD KEY (organism_abbreviation)" )
		tableIndexes.append( "ADD KEY (organism_strain)" )
		
		conditionCount = 1
		for fileRef, fileReads in fileList.iteritems( ) :
			tableIndexes.append( "ADD KEY (condition" + str(conditionCount) + ")" )
			conditionCount = conditionCount + 1
		
		query = query + " " + ",".join( tableIndexes ) + ";"
		
		self.cursor.execute( query )