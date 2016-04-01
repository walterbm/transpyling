#!/bin/bash

echo "Starting with Python:"
printf "number of lines: "
wc -l < python/pie.py
python python/pie.py
echo

echo "Python --> Javascript ☕:"
printf "number of lines: "
./python/PythonJS/pythonjs/translator.py ./python/pie.py > ./javascript/python-pie.js --no-wrapper
wc -l < javascript/python-pie.js
node javascript/python-pie.js
echo
