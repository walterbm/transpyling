#!/bin/bash

echo "Starting with Javascript ☕:"
printf "number of lines: "
wc -l < javascript/pie.js
node javascript/pie.js
echo

echo "Javascript --> PHP:"
printf "number of lines: "
npm run convert2php --prefix ./javascript >& conversion-log.s
wc -l < php/javascript-pie.php
php php/javascript-pie.php
echo
