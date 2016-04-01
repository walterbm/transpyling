echo "Starting with Ruby 🎈:"
printf "number of lines: "
wc -l < ruby/pie.rb
ruby ruby/pie.rb
echo

echo "🎈 Ruby --> Javascript ☕:"
printf "number of lines: "
rake -f ./ruby/rakefile.rb build
wc -l < javascript/ruby-pie.js
node javascript/ruby-pie.js
echo
