# BioGRID ORCA Web Application
This directory contains the entirety of the BioGRID ORCA Web Application. 

## System Requirements
To use all of the tools contained within, you require at least the following:

+ MySQL 5.5+ (https://www.mysql.com/)
+ PHP 7.0+ (http://www.php.net/)
+ Additional PHP Libraries: PHP-Mycrpt, PHP-CURL, PHP-XML, PHP-MySQL, PHP-BCMath
+ NPM (https://www.npmjs.com/)
+ Composer (https://getcomposer.org/)
+ A web server such as Nginx (https://www.nginx.com/) or Apache (https://httpd.apache.org/)

## Directories
+ **app** - The non-public facing tools, classes, and application components for the site. Should be inaccessible via your website URL.
+ **www** - The public facing resources for the site. This is the directory you will want available on the web.

## Website Setup Instructions
+ Download the Repository from GitHub
+ Set your web root for the site to point to <INSTALL LOCATION>/site/www
    + Apache: Setup a virtual host with the variable: **DocumentRoot <INSTALL LOCATION>/site/www**
	+ Nginx: Setup a new server declaration with a root of: **root   <INSTALL LOCATION>/site/www;**
+ Navigate to the <INSTALL LOCATION>/config directory
    + Using the config.json.example file as a template, create a new file named config.json
    + Modify the variables to match your installation, specifically, these are of most importance:
	    + Database Variables: DB_IP, DB_PORT, DB_USER, DB_PASS, DB_ORCA, DB_QUICK
		+ BASE_PATH: change this to your <INSTALL LOCATION>/site/www path
		+ WEB_URL: change this to your url (ex: http://www.example.com) or use an ip (http://192.16.222.32)
+ Navigate to the <INSTALL LOCATION>/site directory
	+ Run: **composer install** or **php composer.phar install** depening on your setup to install composer requirements
	+ Run: **npm install** to install NPM requirements
	+ Run: **npm run build** to copy and build the components from app to www

## Nginx/Apache Tweaks
+ If you intend to work with large inputs via the user interface or via the file uploader, you will need to make modifications to your web server configuration to increase memory availability for body size.
    + NGINX: Add the following to your Server configuration for IMS 4: **client_max_body_size 128M;** (adjust this value as required)

## PHP Configuration Tweaks
+ If you intend to work with large inputs via the user interface or via the file uploader, you will need to make modifications to your php configuration to increase memory availability.
    + Modify the following variables as required in your php7.0-fpm php.ini file (example settings): 
		+ max_execution_time = 600
		+ max_input_time = 60
		+ memory_limit = 2000M
		+ post_max_size = 128M
		+ upload_max_filesize = 128M
		
## CRON JOBS
The processing of uploaded raw data and annotation files requires a regularly run CRON task. Also, the creation of views requires a regularly run CRON task. We recommend running these tasks as regularly as possible (every minute) but can ultimately be set to run at whatever time frame you prefer. The easiest way to get this going is to go into the <INSTALL LOCATION>/site/app/bin directory and modify "**orcaCron.sh**" to change the "operationsPath" and "python" variables to match that of your installation. Then simply run the shell script at whatever frequency you prefer...
+ Using FLOCK (preferred method): * * * * * /usr/bin/flock -n /tmp/orcaCRON.lockfile <INSTALL LOCATION>/site/app/bin/orcaCron.sh >> <LOG FILE LOCATION>/orca.log
+ Example Straight Cron: * * * * * <INSTALL LOCATION>/site/app/bin/orcaCron.sh >> <LOG FILE LOCATION>/orca.log
