import sys, string
import Config
import datetime

class TwoColumnParser( ) :

	"""Function for parsing two column raw data files into the database"""

	def __init__( self, fileID, data, db, sgRNAs ) :
		self.fileID = fileID
		self.data = data
		self.db = db
		self.cursor = self.db.cursor( )
		self.sgRNAs = sgRNAs
		self.errors = []
		self.readTotal = 0
	
	def parse( self ) :
		
		"""Read in the data and dump it to the database"""
		
		self.errors = { }
		self.readTotal = 0
		reads = { }
		
		try :
		
			lineNumber = 0
			for line in self.data :
				lineNumber = lineNumber + 1
				
				# Skip Duplicates or Invalid Lines
				splitLine = line.strip( ).split( "\t" )
				if len(splitLine) <= 0 :
					continue
				elif len(splitLine) != 2 :
					self.errors[lineNumber] = "TOO MANY ENTRIES: Found " + str(len(splitLine)) + " entries on row " + str(lineNumber)
					continue
				
				# Ensure first value is an integer
				readCount = 0
				try :
					readCount = int(splitLine[0])
				except ValueError :
					self.errors[lineNumber] = "INVALID READ FORMAT: Found non-integer read value " + str(splitLine[0]) + " on row " + str(lineNumber)
					continue
					
				# See if sgRNA exists, if not add it
				sgRNA = str(splitLine[1]).upper( )
				sgRNAID = 0
				if sgRNA in self.sgRNAs :
					sgRNAID = self.sgRNAs[sgRNA]
				else :
					self.errors[lineNumber] = "FOUND SGRNA NOT LOADED IN DATABASE: Found an sgRNA that has not yet been loaded via annotation " + str(splitLine[1]) + " on row " + str(lineNumber)
					continue
					
				self.readTotal = self.readTotal + readCount
				self.cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".raw_reads VALUES( '0', %s, %s, %s )", [sgRNAID, readCount, self.fileID] )
				
				if str(sgRNAID) not in reads :
					reads[str(sgRNAID)] = 0
					
				reads[str(sgRNAID)] = reads[str(sgRNAID)] + int(readCount)
				
			if len(self.errors) > 0 :
				self.db.rollback( )
			else :
				self.db.commit( )
			
		except :
			self.db.rollback( )
			exctype, value = sys.exc_info( )[:2]
			self.errors[lineNumber] = "UNEXPECTED ERROR: " + str(exctype);
			
		return reads, self.readTotal, self.errors