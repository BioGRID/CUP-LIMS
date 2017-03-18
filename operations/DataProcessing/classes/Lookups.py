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