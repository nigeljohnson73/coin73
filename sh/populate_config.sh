#!/bin/bash

cd /webroot/minertor

echo "Paste your bundle here"
read bundle

echo $bundle > bundle.json
php sh/populate_config.php
rm -f bundle.json
