#!/usr/bin/env python
import Config
import Database
from time import sleep
from flask import Flask
from concurrent.futures import ThreadPoolExecutor

# Generate a thread pool
executor = ThreadPoolExecutor(2)

app = Flask( __name__ )

@app.route( "/" )
def index( ) :
	executor.submit(runViewGenerator)
	return "View Generator Service is Active!"
	
def runViewGenerator( ) :
	print "STARTING TASK"
	sleep(10)
	print "ENDING TASK"
	
if __name__ == '__main__' :
	app.run( debug=True, port=Config.PORT, host=Config.HOST )