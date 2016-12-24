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
		
		self.lookups = Lookups.Lookups( self.db )
		
		# Get gene Hash
		self.sgRNAToGene = self.lookups.buildSGRNAToGeneHash( )
		print len(self.sgRNAToGene)
		
	def buildGeneSummary( self, fileID, fileReads, fileReadTotal, bgData, bgTotals ) :
	
		"""Build a gene summary dict using the various calculations and background files we have available"""
	
		print "HERE"
		print len(self.sgRNAToGene)
		self.geneSummary = { }
		for sgRNAID,readCount in fileReads.items( ) :
			geneID = 0
			
			# Test to see if this sgRNA is mapped to a gene
			# we know of
			if str(sgRNAID) in self.sgRNAToGene :
				geneID = self.sgRNAToGene[str(sgRNAID)]
				
				# Step through each background type
				for bgFileID, bgReads in bgData.items( ) :
				
					if str(bgFileID) not in self.geneSummary :
						self.geneSummary[str(bgFileID)] = { }
				
					# Fetch total reads for background and the read for this
					# specific sgRNA
					bgTotal = bgTotals[str(bgFileID)]
					bgRead = bgReads[str(sgRNAID)]
					
					# If it's a gene ID we know of
					if geneID > 0 :
					
						if str(geneID) not in self.geneSummary[str(bgFileID)] :
							self.geneSummary[str(bgFileID)][str(geneID)] = {}
							
						if 'log2fold' not in self.geneSummary[str(bgFileID)][str(geneID)] :
							self.geneSummary[str(bgFileID)][str(geneID)]['log2fold'] = []
					
						# Calculate the various values we desire
						logChange = self.calculateLog2FoldChange( readCount, bgRead, fileReadTotal, bgTotal )
						
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
						collapsedValue = math.mean( values )
						
					self.cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".gene_summaries VALUES ( '0', %s, %s, %s, %s, %s )", [geneID, fileID, bgFileID, collapsedValue, calculationType] )
					
		self.db.commit( )
						
		
	def calculateLog2FoldChange( self, readCount, bgReadCount, readTotal, bgReadTotal ) :
	
		"""Generate a log2foldchange value"""
		if bgReadCount > 0 :
			print "log(" + str(readCount) + "/" + str(bgReadCount) + ") - log(" + str(readTotal) + "/" + str(bgReadTotal) + ")"
			
			readScore = float(readCount) / float(bgReadCount)
			totalScore = float(readTotal) / float(bgReadTotal)
			return math.log(readScore, 2) - math.log(totalScore, 2)
			
		return False
		
	def fetchGeneSummary( self ) :
		return self.geneSummary