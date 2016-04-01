require 'opal'

desc "Convert Ruby to JavaScript"
task :build do
  Opal.append_path "ruby"
  File.binwrite "./javascript/ruby-pie.js", Opal::Builder.build("pie").to_s
end
