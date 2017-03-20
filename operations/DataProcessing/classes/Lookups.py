import sys, string
import Config
import datetime

class Lookups( ) :

	"""Functions for building quick lookup data-structures to save on Database query times"""

	def __init__( self, db ) :
		self.db = db
		self.cursor = self.db.cursor( )
		
	def buildSGRNAHash( self ) :
		
		"""Build a set of sgRNAs mapped to sgRNA IDs"""
		
		mapping = { }
		self.cursor.execute( "SELECT sgrna_id, sgrna_sequence FROM " + Config.DB_MAIN + ".sgRNAs" )
		
		for row in self.cursor.fetchall( ) :
			mapping[str(row['sgrna_sequence'])] = str(row['sgrna_id'])
			
		return mapping
		
	def buildOfficialNameHash( self, organismID ) :
	
		"""Build a quick lookup hash of mapped official names"""
		
		mapping = { }
		self.cursor.execute( "SELECT entrez_gene_id, identifier_value FROM " + Config.DB_MAIN + ".gene_identifiers WHERE identifier_type='OFFICIAL_SYMBOL' OR identifier_type='SYSTEMATIC_NAME' AND organism_id=%s GROUP BY entrez_gene_id, identifier_type", [organismID] )
		
		for row in self.cursor.fetchall( ) :
			identifier = str(row['identifier_value']).upper( )
			if identifier not in mapping :
				mapping[identifier] = []
		
			mapping[identifier].append( str(row['entrez_gene_id']) )
			
		return mapping
		
	def buildSynonymNameHash( self, organismID ) :
	
		"""Build a quick lookup hash of mapped synonyms"""
		
		mapping = { }
		self.cursor.execute( "SELECT entrez_gene_id, identifier_value FROM " + Config.DB_MAIN + ".gene_identifiers WHERE identifier_type='SYNONYM' AND organism_id=%s", [organismID] )
		
		for row in self.cursor.fetchall( ) :
			identifier = str(row['identifier_value']).upper( )
			if identifier not in mapping :
				mapping[identifier] = []
		
			mapping[identifier].append( str(row['entrez_gene_id']) )
			
		return mapping
		
	def buildGroupHash( self ) :
	
		"""Build a quick lookup hash of existing sgrna groups"""
		
		mapping = { }
		self.cursor.execute( "SELECT sgrna_group_id, sgrna_group_reference, sgrna_group_reference_type FROM " + Config.DB_MAIN + ".sgRNA_groups WHERE sgrna_group_status='active'" )
		
		for row in self.cursor.fetchall( ) :
			identifier = str(row['sgrna_group_reference']).upper( ) + "|" + str(row['sgrna_group_reference_type'])
			mapping[identifier] = str(row['sgrna_group_id'])
			
		return mapping