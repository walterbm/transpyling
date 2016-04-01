#!/bin/bash

echo "Starting with Ruby:"
ruby ruby/pie.rb
echo
echo "Ruby --> Javascript ☕:"
rake -f ./ruby/rakefile.rb build
node javascript/pie.js
echo
