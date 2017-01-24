#!/usr/bin/env python
import Config
import Database
import atexit, os, time

from flask import Flask
from concurrent.futures import ThreadPoolExecutor
from classes import CRONTask

# Generate a thread pool
executor = ThreadPoolExecutor(5)
app = Flask( __name__ )

@app.route( "/" )
def index( ) :
	return "View Generator Service is Active!"
	
@app.route( "/View" )
def view( ) :
	executor.submit(runTask)
	return ""
	
def runTask( ) :
	cron = CRONTask.CRONTask( )
	cron.run( )
	cron.killPID( )
	sys.exit(0)

if __name__ == '__main__' :
	app.run( debug=True, port=Config.PORT, host=Config.HOST )