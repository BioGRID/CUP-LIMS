import sys, string
import Config
import Database
import MySQLdb
import datetime
import math
import atexit, os, time

from classes import Lookups, MatrixView

class ViewGenerator( ) :

	"""Class for managing the building of views"""

	def __init__( self ) :
		self.db = Database.db
		self.lookups = Lookups.Lookups( self.db )
		self.sgRNAToGene = self.lookups.buildSGRNAToGeneHash( )
		self.matrixView = None
	
	def run( self ) :
		"""Default class to start the generator process executing"""
		self.buildViews( )
	
	def buildViews( self ) :
		"""Entire process for building views that are queued"""
		queuedViews = self.fetchQueuedViews( )
		
		for view in queuedViews :
			if not self.viewExists( view['view_code'] ) :
		
				# View Type 1 is a Matrix View
				if str(view['view_type_id']) == "1" :
					self.buildMatrixView( view )
					print "MATRIX VIEW"
				else :
					# Unknown View Type, Do Nothing, Leave it Queued
					continue
					
			#self.updateViewState( view['view_id'], 'complete' )
			
	def buildMatrixView( self, view ) :
	
		"""Create a matrix view using the appropriate classes"""
		if self.matrixView == None :
			self.matrixView = MatrixView.MatrixView( self.db )
			
		self.matrixView.build( view )
			
	def viewExists( self, viewCode ) :
		"""Test to see if a view already exists as a table"""
		try :
			with self.db as cursor :
				cursor.execute( "SELECT * FROM " + Config.DB_VIEWS + ".view_" + viewCode + " LIMIT 1" )
				return True
		except MySQLdb.Error :
			return False
				
	def updateViewState( self, viewID, viewState ) :
		"""Change the state of a given view"""
		with self.db as cursor :
			cursor.execute( "UPDATE " + Config.DB_MAIN + ".views SET view_state=%s WHERE view_id=%s", [viewState, viewID] )
			self.db.commit( )
		
	def fetchQueuedViews( self ) :
		"""Fetch views from the database that are in need of being built"""
		with self.db as cursor :
			cursor.execute( "SELECT * FROM " + Config.DB_MAIN + ".views WHERE view_state='building' AND view_status='active' ORDER BY view_addeddate ASC" )
			
			queuedViews = []
			for row in cursor.fetchall( ) :
				queuedViews.append( row )
				
			return queuedViews
		