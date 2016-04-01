#!/bin/bash

echo "Starting with Javascript ☕:"
printf "number of lines: "
wc -l < javascript/pie.js
node javascript/pie.js
echo

echo "Javascript --> Hodor:"
printf "number of lines: "
npm run convert2hodor --prefix ./javascript >& conversion-log.s
wc -l < javascript/pie.hd
npm run hodor --prefix ./javascript
mkdir -p hodor
mv javascript/pie.hd hodor
echo
