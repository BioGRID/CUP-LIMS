# BioGRID ORCA sgRNA Processing
This directory contains tools to load up sgRNA sequence annotation into the database and create mappings to annotation and groups.

## System Requirements
To use all of the tools contained within, you require at least the following:

+ MySQL 5.5+ (https://www.mysql.com/)
+ Python 2.7
+ Additional Python Libraries: argparse

## Settings
+ Main settings are in the global config in the "config" directory of the root ORCA file structure

## How to Operate
+ Execute **python LoadSGRNAs.py -i <inputFile>** - This will load sgRNA and annotation data in an annotation file into the database.
+ Execute **python UpdateGroupReferences.py** - This will attempt to map sgRNA_Groups to BioGRID Identifiers if possible.