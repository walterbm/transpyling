#!/bin/bash

echo "Starting with Ruby:"
printf "number of lines: "
wc -l < ruby/pie.rb
ruby ruby/pie.rb
echo

echo "Ruby --> Javascript ☕:"
printf "number of lines: "
rake -f ./ruby/rakefile.rb build
wc -l < javascript/ruby-pie.js
node javascript/ruby-pie.js
echo

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

echo "Starting with Javascript ☕:"
printf "number of lines: "
wc -l < javascript/pie.js
node javascript/pie.js
echo

echo "Javascript --> PHP:"
printf "number of lines: "
npm run convert2php --prefix ./javascript >& js2php-log.s
wc -l < php/javascript-pie.php
php php/javascript-pie.php
echo
