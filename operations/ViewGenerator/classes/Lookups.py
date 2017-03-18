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
		
	def buildSGRNAIDHash( self ) :
		
		"""Build a set of sgRNA IDs mapped to sgRNA Sequences"""
		
		mapping = { }
		self.cursor.execute( "SELECT sgrna_id, sgrna_sequence FROM " + Config.DB_MAIN + ".sgRNAs" )
		
		for row in self.cursor.fetchall( ) :
			mapping[str(row['sgrna_id'])] = str(row['sgrna_sequence'])
			
		return mapping
		
	def buildSGRNAGroupHash( self ) :
	
		mapping = { }
		self.cursor.execute( "SELECT sgrna_group_id, sgrna_group_reference, sgrna_group_reference_type FROM " + Config.DB_MAIN + ".sgRNA_groups" )
		
		for row in self.cursor.fetchall( ) :
			mapping[str(row['sgrna_group_id'])] = row
			
		return mapping
		
	def buildSGRNAIDtoSGRNAGroupHash( self ) :
	
		"""Build a set of sgRNAs mapped to gene IDs"""
		
		mapping = { }
		self.cursor.execute( "SELECT sgrna_id, sgrna_group_id FROM " + Config.DB_MAIN + ".sgRNA_group_mappings WHERE sgrna_group_mapping_status='active'" )
		
		for row in self.cursor.fetchall( ) :
		
			if str(row['sgrna_id']) not in mapping :
				mapping[str(row['sgrna_id'])] = []
		
			mapping[str(row['sgrna_id'])].append( str(row['sgrna_group_id']) )
			
		return mapping
		
	def buildGroupIDToBioGRIDAnnotation( self ) :
		"""Build a quick lookup of BioGRID annotation for groups with a BioGRID ID reference"""
		mapping = { }
		self.cursor.execute( "SELECT o.sgrna_group_id, o.sgrna_group_reference, p.systematic_name, p.official_symbol, p.aliases, p.definition, p.organism_id, p.organism_common_name, p.organism_official_name, p.organism_abbreviation, p.organism_strain FROM " + Config.DB_MAIN + ".sgRNA_groups o LEFT JOIN " + Config.DB_QUICK + ".quick_annotation p ON (o.sgrna_group_reference=p.gene_id) WHERE o.sgrna_group_reference_type='BIOGRID'" )
		
		for row in self.cursor.fetchall( ) :
			mapping[str(row['sgrna_group_id'])] = row
			
		return mapping
		
	def buildFileHash( self, fileIDs ) :
		"""Build a set of file details indexed by file ID"""
		formatFileIDs = ','.join( ['%s'] * len( fileIDs ))
		query = "SELECT * FROM " + Config.DB_MAIN + ".files WHERE file_id IN (%s)"
		query = query % formatFileIDs
		self.cursor.execute( query, tuple( fileIDs ))
		
		files = { }
		for row in self.cursor.fetchall( ) :
			files[str(row['file_id'])] = row
			
		return files
		