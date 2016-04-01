echo "🐶 Dogescript --> Javascript ☕:"
printf "number of lines: "
npm run convert2js --prefix ./javascript >& conversion-log.s
wc -l < javascript/dogescript-pie.js
node javascript/dogescript-pie.js
echo
