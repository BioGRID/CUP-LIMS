
# Parse all genes from Entrez Gene that
# are relevant to the organisms we want loaded
# via the organisms table.

import Config
import sys, string, argparse
import MySQLdb
import Database
import gzip, json

from classes import Lookups

# Process Command Line Input
argParser = argparse.ArgumentParser( description = 'Add/Update Annotation Records' )
argParser.add_argument( '--input', '-i', action='store', type = str, nargs = 1, help = 'Input file to parse', required='true' )
argParser.add_argument( '--organism', '-o', type = int, nargs = 1, help = 'An ncbi tax id to process annotation for', action='store', required='true' )
inputArgs = vars( argParser.parse_args( ) )

with Database.db as cursor :

	lookups = Lookups.Lookups( Database.db )
	existingEntrezGenes = lookups.buildExistingEntrezGeneHash( inputArgs['organism'][0] )
	biogridIDHash = lookups.buildEntrezGeneToBioGRIDHash( inputArgs['organism'][0] )

	cursor.execute( "UPDATE " + Config.DB_MAIN + ".genes SET gene_status='inactive' WHERE source='ENTREZ' AND organism_id=%s", [inputArgs['organism']] )
	cursor.execute( "DELETE FROM " + Config.DB_MAIN + ".gene_identifiers WHERE organism_id=%s", [inputArgs['organism']] )
	Database.db.commit( )
	
	insertCount = 0
	with gzip.open( inputArgs['input'][0], 'r' ) as file :
		for line in file.readlines( ) :
			
			line = line.strip( )
			
			# Ignore Header Line
			if "#" == line[0] :
				continue
				
			splitLine = line.split( "\t" )
			entrezGeneTaxID = int(splitLine[0].strip( ))
			entrezGeneID = splitLine[1].strip( )
			officialSymbol = splitLine[2].strip( )
			systematicName = splitLine[3].strip( )
			synonyms = (splitLine[4].strip( )).split( "|" )
			definition = splitLine[8].strip( )
			fullName = splitLine[11].strip( )
			dbxrefs = (splitLine[5].strip( )).split( "|" )
			geneType = splitLine[9].strip( )
			
			# Identifiers
			identifiers = []
			
			# Skip NEWENTRY records
			if "NEWENTRY" == officialSymbol :
				continue

			# Only load it if it's the organism we are working with
			if str(entrezGeneTaxID) == str(inputArgs['organism'][0]) :

				entry = [entrezGeneTaxID]
				identifiers.append( entrezGeneID + "|ENTREZ_GENE" )
				
				# Process Official Symbol
				if "-" != officialSymbol :
					entry.append( officialSymbol )
					identifiers.append( officialSymbol + "|OFFICIAL_SYMBOL" )
				else :
					entry.append( "-" )
					
				# Process Systematic Name
				if "-" != systematicName :
					entry.append( systematicName )
					identifiers.append( systematicName + "|SYSTEMATIC_NAME" )
				else :
					entry.append( "-" )
					
				# Process Synonyms
				aliases = []
				if "-" != splitLine[4].strip( ) :
					for alias in synonyms :
						alias = alias.strip( )
						if "-" != alias and len( alias ) > 0 :
							aliases.append( alias )
							identifiers.append( alias + "|SYNONYM" )
							
				entry.append( json.dumps( aliases ) )
				
				# Process Full Name
				if "-" != fullName :
					entry.append( fullName )
				else :
					entry.append( "-" )
					
				# Process Definition
				if "-" != definition :
					entry.append( definition )
				else :
					entry.append( "-" )
				
				# Process External IDs
				externalIDs = []
				externalTypes = []
				if "-" != splitLine[5].strip( ) :
					for dbxref in dbxrefs :
						dbxrefInfo = dbxref.split( ":", 1 )
						dbxrefInfo[1] = str(dbxrefInfo[1]).upper( ).replace( "HGNC:", "" ).replace( "MGI:", "" ).replace( "RGD:", "" )
						externalIDs.append( dbxrefInfo[1].strip( ) )
						externalTypes.append( dbxrefInfo[0].strip( ).upper( ) )
						identifiers.append( dbxrefInfo[1].strip( ) + "|" + dbxrefInfo[0].strip( ).upper( ) )
						
				entry.append( json.dumps( externalIDs ) )
				entry.append( json.dumps( externalTypes ) )
				
				# Process Source
				entry.append( "ENTREZ" )
				
				# Process Type
				if "-" != geneType :
					entry.append( geneType )
				else :
					entry.append( "-" )
					
				# Process BioGRID ID
				biogridID = 0 
				if str(entrezGeneID) in biogridIDHash :
					biogridID = biogridIDHash[str(entrezGeneID)]
					identifiers.append( str(biogridID) + "|BIOGRID" )
					
				entry.append( biogridID )
				
				# Process Status
				entry.append( "active" )
				
				# Check to see if we are updating an existing entry
				# or loading a new one
				if str(entrezGeneID) in existingEntrezGenes :
					entry.append( str(entrezGeneID) )
					cursor.execute( "UPDATE " + Config.DB_MAIN + ".genes SET organism_id=%s, official_symbol=%s, systematic_name=%s, aliases=%s, full_name=%s, definition=%s, external_ids=%s, external_id_types=%s, source=%s, gene_type=%s, biogrid_id=%s, gene_status=%s, gene_lastupdated=NOW( ) WHERE entrez_gene_id=%s", entry )
				else :
					entry.insert( 0, str(entrezGeneID) )
					cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".genes VALUES ( %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW( ), NOW( ) )", entry )
					
				for identifier in identifiers :
					splitIdentifier = identifier.split( "|" )
					cursor.execute( "INSERT INTO " + Config.DB_MAIN + ".gene_identifiers VALUES ( %s, %s, %s, %s )", [str(entrezGeneID), splitIdentifier[0], splitIdentifier[1], entrezGeneTaxID] )
					
				insertCount = insertCount + 1
				if 0 == (insertCount % 5000 ) :
					Database.db.commit( )
				
		Database.db.commit( )
	Database.db.commit( )
	
sys.exit( )	