import sys, string
import Config
import atexit, os, time

from classes import ViewGenerator
#from tendo import singleton

class CRONTask( ) :

	"""Common functions for managing running of view generator tasks in CRON capacity"""

	def __init__( self ) :
		self.pidFile = "/tmp/ORCA_ViewGeneratorCRON.pid";
		self.setPID( )
		self.wrotePID = False
		atexit.register( self.killPID )
		
	def setPID( self ) :
		"""Generate an OS PID value"""
		self.pid = str( os.getpid( ) )
		
	def existsPID( self ) :
		"""Test to see if the PID file already exists"""
		if os.path.isfile(self.pidFile) :
			return True
			
		return False
		
	def run( self ) :
		"""Run the cron task or skip if it's already running"""
		if self.existsPID( ) :
			print "CRON ALREADY RUNNING"
			return
			
		self.writePID( )
		viewGenerator = ViewGenerator.ViewGenerator( )
		viewGenerator.run( )
		return
		
	def writePID( self ) :
		"""Write out the PID value to the PID FILE"""
		with open( self.pidFile, 'w' ) as outFile :
			outFile.write( self.pid )
			self.wrotePID = True
			
	def killPID( self ) :
		"""Remove the PID file to allow for other instances to run later"""
		print "KILLING"
		if self.wrotePID :
			print "KILLING CAUSE I WROTE IT"
			try :
				os.unlink( self.pidFile )
			except OSError :
				pass
			
	def __del__( self ) :
		self.killPID( )