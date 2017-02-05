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
		
	def buildSGRNAGroupReferenceHash( self ) :
		
		"""Build a set of sgRNA Group References Mapped to Group IDs"""
		mapping = { }
		self.cursor.execute( "SELECT sgrna_group_reference_original, sgrna_group_id FROM " + Config.DB_MAIN + ".sgRNA_groups" )
		
		for row in self.cursor.fetchall( ) :
			mapping[str(row['sgrna_group_reference_original'])] = str(row['sgrna_group_id'])
			
		return mapping
		
	def buildSGRNAToGroupMapping( self ) :
		
		"""Build a set of sgRNA to Group Mappings"""
		mapping = set( )
		self.cursor.execute( "SELECT sgrna_group_id, sgrna_id FROM " + Config.DB_MAIN + ".sgRNA_group_mappings" )
		
		for row in self.cursor.fetchall( ) :
			mapping.add( str(sgrna_group_id) + "|" + str(sgrna_id) )
			
		return mapping