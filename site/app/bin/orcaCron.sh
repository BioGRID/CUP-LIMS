#!/bin/sh -e
operationsPath=/home/stark/public_html/ORCA/operations
python=/usr/bin/python

nice -n 5 $python $operationsPath/DataProcessing/DataProcessingCRON.py
nice -n 5 $python $operationsPath/ViewGenerator/ViewGeneratorCRON.py