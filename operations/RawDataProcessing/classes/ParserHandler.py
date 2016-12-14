import sys, string
import Config
import datetime
import json

class ParserHandler( ) :

	"""Functions for handling various generic parser operations"""

	def __init__( self, db ) :
		self.db = db
		self.cursor = self.db.cursor( )
	
	def delExistingReads( self, fileID ) :
		self.cursor.execute( "DELETE FROM " + Config.DB_MAIN + ".raw_reads WHERE file_id=%s", [fileID] )
		self.db.commit( );
			
	def setFileState( self, fileID, state, messages ) :
		self.cursor.execute( "UPDATE " + Config.DB_MAIN + ".files SET file_state=%s,file_state_msg=%s WHERE file_id=%s", [state, json.dumps(messages), fileID] )
		self.db.commit( )