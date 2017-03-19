import sys, string
import Config
import datetime

class Lookups( ) :

	"""Functions for building quick lookup data-structures to save on Database query times"""

	def __init__( self, db ) :
		self.db = db
		self.cursor = self.db.cursor( )
		
	def buildExistingEntrezGeneHash( self, taxID ) :
	
		"""Build a set of entrez genes that are already entered"""
		
		mapping = set( )
		self.cursor.execute( "SELECT entrez_gene_id FROM " + Config.DB_MAIN + ".genes WHERE organism_id=%s", [taxID] )
		
		for row in self.cursor.fetchall( ) :
			mapping.add( str(row['entrez_gene_id']) )
			
		return mapping
		
	def buildEntrezGeneToBioGRIDHash( self, taxID ) :
	
		"""Build a set of biogrid gene ids that map to an organism"""
		
		mapping = { }
		self.cursor.execute( "SELECT gene_id, quick_identifier_value FROM " + Config.DB_QUICK + ".quick_identifiers WHERE quick_identifier_type='ENTREZ_GENE' AND organism_id=%s", [taxID] )
		
		for row in self.cursor.fetchall( ) :
			mapping[str(row['quick_identifier_value'])] = str(row['gene_id'])
			
		return mapping