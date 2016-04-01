#!/bin/bash

echo "Starting with Ruby:"
printf "number of lines: "
wc -l < ruby/pie.rb
ruby ruby/pie.rb
echo
echo "Ruby --> Javascript ☕:"
printf "number of lines: "
wc -l < javascript/ruby-pie.js
rake -f ./ruby/rakefile.rb build
node javascript/ruby-pie.js
echo
