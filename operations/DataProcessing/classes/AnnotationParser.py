import sys, string
import Config
import datetime

class AnnotationParser( ) :

	"""Function for parsing annotation files into the database"""

	def __init__( self, annotationFileID, data, db, sgRNAs, officialHash, synonymHash, groupHash ) :
		self.fileID = annotationFileID
		self.data = data
		self.db = db
		self.cursor = self.db.cursor( )
		self.sgRNAs = sgRNAs
		self.errors = []
		self.officialHash = officialHash
		self.synonymHash = synonymHash
		self.groupHash = groupHash
	
	def parse( self ) :
		
		"""Read in the data and dump it to the database"""
		
		self.errors = { }
		
		try :
		
			lineNumber = 0
			for line in self.data :
				lineNumber = lineNumber + 1
				
				# Skip Duplicates or Invalid Lines
				splitLine = line.strip( ).split( "\t" )
				if len(splitLine) <= 0 :
					continue
				elif len(splitLine) < 2 :
					self.errors[lineNumber] = "NOT ENOUGH VALUES: Found " + str(len(splitLine)) + " entries on row " + str(lineNumber)
					continue
					
				# See if sgRNA exists, if not add it
				sgRNA = str(splitLine[0].strip( )).upper( )
				sgRNAID = 0
				if sgRNA in self.sgRNAs :
					sgRNAID = self.sgRNAs[sgRNA]
				else :
					if len(sgRNA) != 20 :
						self.errors[lineNumber] = "INVALID SGRNA (NOT 20 characters): Found " + str(sgRNA) + " on row " + str(lineNumber)
						continue
					else :
						sgRNAID = self.insertSGRNA( sgRNA )
						
				# Establish Group Mappings
				for x in xrange( 1, len(splitLine) ) :
					identifier = str(splitLine[x].strip( ))
					groupID = None
					
					# Test name against official symbols & systematic names
					if identifier.upper( ) in self.officialHash :
						geneSet = self.officialHash[identifier.upper( )]
						if len(geneSet) == 1 :
							if str(geneSet[0]).upper( ) + "|ENTREZ" in self.groupHash :
								groupID = self.groupHash[str(geneSet[0]).upper( ) + "|ENTREZ"]
							else :
								groupID = self.addGroup( geneSet[0], "ENTREZ" )
					
					# Test name against synonyms
					if groupID == None and identifier.upper( ) in self.synonymHash :
						geneSet = self.synonymHash[identifier.upper( )]
						if len(geneSet) == 1 :
							if str(geneSet[0]).upper( ) + "|ENTREZ" in self.groupHash :
								groupID = self.groupHash[str(geneSet[0]).upper( ) + "|ENTREZ"]
							else :
								groupID = self.addGroup( geneSet[0], "ENTREZ" )
					
					# Test to see if an existing custom group already exists
					if groupID == None and identifier.upper( ) + "|CUSTOM" in self.groupHash :
						groupID = self.groupHash[identifier.upper( ) + "|CUSTOM"]
						
					# Add a new group if all else fails
					elif groupID == None :
						groupID = self.addGroup( identifier, "CUSTOM" )
						
					# Add a mapping for this group to the sgRNA if one does not exist
					self.addGroupMapping( groupID, sgRNAID, identifier ) 
				
			if len(self.errors) > 0 :
				self.db.rollback( )
			else :
				self.db.commit( )
			
		except :
			self.db.rollback( )
			exctype, value = sys.exc_info( )[:2]
			self.errors[lineNumber] = "UNEXPECTED ERROR: " + str(exctype);
			
		return self.errors
		
	def addGroup( self, identifier, identifierType ) :
	
		"""Add a new group to the database based on the passed in details"""
		
		self.cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".sgRNA_groups VALUES( '0', %s, %s, NOW( ), 'active' )", [identifier, identifierType.upper( )] )
		groupID = self.cursor.lastrowid
		self.groupHash[identifier.upper( ) + "|" + identifierType.upper( )] = groupID
		
		return groupID
		
	def addGroupMapping( self, groupID, sgRNAID, identifier ) :
	
		"""Add a new group mapping or re-activate an existing one if invalid"""
		
		# See if it exists already in some form
		self.cursor.execute( "SELECT sgrna_group_mapping_id FROM " + Config.DB_MAIN + ".sgRNA_group_mappings WHERE sgrna_group_id=%s AND sgrna_id=%s AND annotation_file_id=%s LIMIT 1", [groupID, sgRNAID, self.fileID] )
		row = self.cursor.fetchone( )
		
		# If it doesn't exist, insert new
		if row == None :
			self.cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".sgRNA_group_mappings VALUES ( '0', %s, %s, %s, %s, NOW( ), 'active' )", [groupID, sgRNAID, self.fileID, identifier] )
		
		# else, update existing and set to active
		else :
			self.cursor.execute( "UPDATE " + Config.DB_MAIN + ".sgRNA_group_mappings SET sgrna_group_mapping_status='active' WHERE sgrna_group_mapping_id=%s", [row['sgrna_group_mapping_id']] )
			
		
	def insertSGRNA( self, sgRNA ) :
	
		"""See if sgRNA exists and if not add it"""
		
		sgRNA = sgRNA.strip( ).upper( )
		
		self.cursor.execute( "SELECT sgrna_id FROM " + Config.DB_MAIN + ".sgRNAs WHERE sgrna_sequence=%s LIMIT 1", [sgRNA] )
		row = self.cursor.fetchone( )
		
		if row == None :
			self.cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".sgRNAs VALUES( '0', %s, NOW( ), 'active' )", [sgRNA] )
			sgRNAID = self.cursor.lastrowid
			self.sgRNAs[sgRNA] = str(sgRNAID)
			
		return sgRNAID