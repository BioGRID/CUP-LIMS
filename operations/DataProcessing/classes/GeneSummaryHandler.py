import sys, string
import Config
import datetime
import math

from classes import Lookups

class GeneSummaryHandler( ) :

	"""Class for managing raw data already parsed into the database"""

	def __init__( self, db ) :
		self.db = db
		self.cursor = self.db.cursor( )
		self.geneSummary = { }
		self.logPad = 1
		
		self.lookups = Lookups.Lookups( self.db )
		
		# Get gene Hash
		self.sgRNAToGene = self.lookups.buildSGRNAToGeneHash( )
		
	def buildGeneSummary( self, fileID, fileReads, fileReadTotal, bgData, bgTotals ) :
	
		"""Build a gene summary dict using the various calculations and background files we have available"""
	
		self.geneSummary = { }
		
		# Get list of sgRNAIDs
		fgIDs = fileReads.keys( )
		
		# Step through each background file separately
		for bgFileID, bgReads in bgData.items( ) :
		
			# Add bgFile as starting point in gene summary
			if str(bgFileID) not in self.geneSummary :
				self.geneSummary[str(bgFileID)] = { }
			
			# Fetch total reads for background
			bgTotal = bgTotals[str(bgFileID)]
		
			# Build universal set of sgRNAIDs
			bgIDs = bgReads.keys( )
			sgRNAIDs = set(fgIDs + bgIDs)
			
			# Step through the combined set
			for sgRNAID in sgRNAIDs :
			
				# Test to see if this sgRNA is mapped to a gene
				# we know of
				if str(sgRNAID) in self.sgRNAToGene :
					geneID = self.sgRNAToGene[str(sgRNAID)]
					
					# Fetch reads for this sgRNAID
					bgRead = 0
					if str(sgRNAID) in bgReads :
						bgRead = float(bgReads[str(sgRNAID)])
						
					reads = 0
					if str(sgRNAID) in fileReads :
						reads = float(fileReads[str(sgRNAID)])
					
					# If it's a gene ID we know of
					if geneID > 0 :
						
						if str(geneID) not in self.geneSummary[str(bgFileID)] :
							self.geneSummary[str(bgFileID)][str(geneID)] = {}
							
						if 'log2fold' not in self.geneSummary[str(bgFileID)][str(geneID)] :
							self.geneSummary[str(bgFileID)][str(geneID)]['log2fold'] = []
					
						# Calculate the various values we desire
						logChange = self.calculateLog2FoldChange( reads, bgRead, fileReadTotal, bgTotal )
						
						if logChange :
							self.geneSummary[str(bgFileID)][str(geneID)]['log2fold'].append( logChange )
						
		self.processSummary( fileID )
		
	def processSummary( self, fileID ) :
	
		for bgFileID,geneSet in self.geneSummary.items( ) :
			for geneID, calculationSet in geneSet.items( ) :
				for calculationType, values in calculationSet.items( ) :
					
					# For Log2Fold, we have to get the mean of
					# all the values that were derived previously
					collapsedValue = 0
					if calculationType == "log2fold" :
						collapsedValue = self.calculateMean( values )
						
					self.cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".gene_summaries VALUES ( '0', %s, %s, %s, %s, %s )", [geneID, fileID, bgFileID, collapsedValue, calculationType] )
					
		self.db.commit( )
		
		
	def calculateMean( self, values ) :

		if len(values) <= 0 :
			return 0
			
		return float(sum(values)) / len(values)
		
	def calculateLog2FoldChange( self, readCount, bgReadCount, readTotal, bgReadTotal ) :
	
		"""Generate a log2foldchange value"""
		# print "log(" + str(readCount) + "/" + str(bgReadCount) + ") - log(" + str(readTotal) + "/" + str(bgReadTotal) + ")"
		
		readScore = (float(readCount) + self.logPad) / (float(bgReadCount) + self.logPad)
		totalScore = (float(readTotal) + self.logPad) / (float(bgReadTotal) + self.logPad)
		return math.log(readScore, 2) - math.log(totalScore, 2)
			
		return False
		
	def fetchGeneSummary( self ) :
		return self.geneSummary