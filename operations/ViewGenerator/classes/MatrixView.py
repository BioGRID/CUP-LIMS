import sys, string
import Config
import datetime
import json

class MatrixView( ) :

	"""Generate a matrix view table based on passed in parameters"""

	def __init__( self, db ) :
		self.db = db
		self.cursor = self.db.cursor( )
		
	def build( self, view ) :
		"""Create a matrix view table based on the view information passed in"""
		
		files = json.loads( view['view_files'] )
		fileSet = set( )
		bgSet = set( )
		fileLookup = { }
		for fileID, bgID in files.iteritems( ) :
			fileSet.add( str(fileID) )
			bgSet.add( str(bgSet) )
			fileLookup[str(fileID)] = str(bgID)