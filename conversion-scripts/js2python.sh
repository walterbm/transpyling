#!/bin/bash

echo "Starting with Javascript ☕:"
wc -l < javascript/pie.js
node javascript/pie.js
echo

echo "Javascript --> Python:"
npm run convert2php --prefix ./javascript >& conversion-log.s
python python/js2python.py
echo
