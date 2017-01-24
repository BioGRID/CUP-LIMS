import sys, string
import Config
import datetime

class Lookups( ) :

	"""Functions for building quick lookup data-structures to save on Database query times"""

	def __init__( self, db ) :
		self.db = db
		self.cursor = self.db.cursor( )
		
	def buildExperimentCodeHash( self ) :
		
		"""Build a set of experiment codes mapped to experiment ids"""
		
		mapping = { }
		self.cursor.execute( "SELECT experiment_id, experiment_code FROM " + Config.DB_MAIN + ".experiments" )
		
		for row in self.cursor.fetchall( ) :
			mapping[str(row['experiment_id'])] = str(row['experiment_code'])
			
		return mapping
		
	def buildSGRNAHash( self ) :
		
		"""Build a set of sgRNAs mapped to sgRNA IDs"""
		
		mapping = { }
		self.cursor.execute( "SELECT sgrna_id, sgrna_sequence FROM " + Config.DB_MAIN + ".sgRNAs" )
		
		for row in self.cursor.fetchall( ) :
			mapping[str(row['sgrna_sequence'])] = str(row['sgrna_id'])
			
		return mapping
		
	def buildSGRNAToGeneHash( self ) :
	
		"""Build a set of sgRNAs mapped to gene IDs"""
		
		mapping = { }
		self.cursor.execute( "SELECT sgrna_id, sgrna_identifier_value FROM " + Config.DB_MAIN + ".sgRNA_identifiers WHERE sgrna_identifier_type='BioGRID Gene ID'" )
		
		for row in self.cursor.fetchall( ) :
			mapping[str(row['sgrna_id'])] = str(row['sgrna_identifier_value'])
			
		return mapping