import sys, string
import Config
import datetime
import copy
import json
import math

class MatrixView( ) :

	"""Generate a matrix view table based on passed in parameters"""

	def __init__( self, db, sgRNAToGroup, sgRNAGroups, sgRNAGroupToGene, organismHash ) :
		self.db = db
		self.cursor = self.db.cursor( )
		self.conditionReference = { }
		self.conditionBlock = []
		self.sgRNAToGroup = sgRNAToGroup
		self.sgRNAGroups = sgRNAGroups
		self.sgRNAGroupToGene = sgRNAGroupToGene
		self.organismHash = organismHash
		self.matrix = { }
		self.logPad = 1
		self.colCount = 14
		self.max = 0
		self.min = 0
		
	def build( self, view, fileMap, rawData, fileHash ) :
		"""Create a matrix view table based on the view information passed in"""
		self.conditionReference = { }
		self.conditionBlock = []
		self.matrix = { }
		
		# Build a list of all conditions for the X axis
		fileList = { }
		for fileID, fileInfo in fileMap.iteritems( ) :
			ctrlSet = fileInfo['BG'].split( "|" )
			for ctrl in sorted(ctrlSet) :
				fileList[str(fileID) + "|" + str(ctrl) + "|" + str(fileInfo['MAP'])] = 0
				
		# Create the database table for storing the view 
		self.createView( view, fileList )
		
		# Process each file one by one
		for fileInfo in fileList :
			fileInfo = fileInfo.split( "|" )
			fileID = fileInfo[0]
			ctrlID = fileInfo[1]
			mapID = fileInfo[2]
			self.generateGroupSummary( view, fileID, ctrlID, mapID, rawData, fileHash )
			
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
			ctrlFile = fileHash[fileInfo[1]]
			referenceHash[condName] = { "FILE" : { "ID" : file['file_id'], "NAME" : file['file_name'] }, "BG" : { "ID" : ctrlFile['file_id'], "NAME" : ctrlFile['file_name'] } }
		
		return referenceHash
	
	def generateGroupSummary( self, view, fileID, ctrlID, mapID, rawData, fileHash ) :
	
			fileRef = fileID + "|" + ctrlID + "|" + mapID
			
			reads = rawData.fetchReads( fileID )
			readSGRNAIDs = reads.keys( )
			readFile = fileHash[fileID]
			
			ctrlReads = rawData.fetchReads( ctrlID )
			ctrlSGRNAIDs = ctrlReads.keys( )
			ctrlFile = fileHash[ctrlID]
			
			# Get universal set of all unique sgRNA ids represented
			allSGRNA = set(readSGRNAIDs + ctrlSGRNAIDs)
			
			# Step through each one and perform required calculation
			count = 0
			for sgRNAID in allSGRNA :
				
				# Only use it if we can find a mapping to a group
				if str(sgRNAID) in self.sgRNAToGroup[str(mapID)] :
					groupIDs = self.sgRNAToGroup[str(mapID)][str(sgRNAID)]
					
					# Could be multiple groups this sgRNA is a 
					# member of
					for groupID in groupIDs :
					
						readScore = 0
						if str(sgRNAID) in reads :
							readScore = float(reads[str(sgRNAID)])
							
						ctrlScore = 0
						if str(sgRNAID) in ctrlReads :
							ctrlScore = float(ctrlReads[str(sgRNAID)])

						if groupID not in self.matrix :
							self.matrix[groupID] = self.initializeConditionSet( )
						
						# 1 is Log2FoldChange
						if str(view['view_value_id']) == "1" :
							logChange = self.calculateLog2FoldChange( readScore, ctrlScore, readFile['file_readtotal'], ctrlFile['file_readtotal'] )
							self.matrix[groupID][self.conditionReference[fileRef]].append(logChange)
						
	def initializeConditionSet( self ) :
		"""Initialize a new group with a dict containing all possible conditions"""
		conditionSet = { }
		for condition in self.conditionBlock :
			conditionSet[condition] = []
			
		return conditionSet
							
	def calculateLog2FoldChange( self, readCount, ctrlReadCount, readTotal, ctrlReadTotal ) :
		"""Generate a log2foldchange value"""
		
		# Pad numbers to prevent division by zero
		readScore = (float(readCount) + self.logPad) / (float(ctrlReadCount) + self.logPad)
		totalScore = (float(readTotal) + self.logPad) / (float(ctrlReadTotal) + self.logPad)
		return math.log(readScore, 2) - math.log(totalScore, 2)
		
	def processView( self, view ) :
		"""Process the view to the database"""
		
		for groupID, conditions in self.matrix.items( ) :
			conditionSet = { }
			groupInfo = self.sgRNAGroups[groupID]
			groupName = groupInfo['sgrna_group_reference']
			
			# Initialize with basic annotation data
			if groupID in self.sgRNAGroupToGene :
				geneAnn = self.sgRNAGroupToGene[groupID]
				
				if geneAnn['official_symbol'] != "-" :
					groupName = geneAnn['official_symbol']
					
				orgAnn = self.organismHash[str(geneAnn['organism_id'])]
				
				row = [groupInfo['sgrna_group_id'], groupInfo['sgrna_group_reference'], groupInfo['sgrna_group_reference_type'], groupName, geneAnn['official_symbol'], geneAnn['systematic_name'], geneAnn['aliases'], geneAnn['definition'], geneAnn['biogrid_id'], geneAnn['organism_id'], orgAnn['organism_common_name'], orgAnn['organism_official_name'], orgAnn['organism_abbreviation'], orgAnn['organism_strain']]
			else :
				row = [groupInfo['sgrna_group_id'], groupInfo['sgrna_group_reference'], groupInfo['sgrna_group_reference_type'], groupName, "-", "-", "-", "-", "0", "0", "-", "-", "-", "-"]
			
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
			formatCols = ','.join( ['%s'] * len(row))
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
		tableFields.append( "biogrid_id BIGINT(10) NOT NULL" )
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
		tableIndexes.append( "ADD KEY (biogrid_id)" )
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