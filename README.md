# Trans*py*ling

A tour of transpiling and back again with six different web development languages.

Creating modern web applications requires developers that are proficient in an increasing variety of languages. Python, Ruby, and PHP are among the most popular server-side languages while JavaScript monopolizes client-side development. But there's a dream for the future of web development. A future where applications can be written once, in your favorite language, and then run everywhere.

This demo explores the reality and sacrifices of trying to chase this dream through source-to-source compiling for client-side applications.

Prepared for the annual :snake: Fuzzy.py :snake: extravaganza at [Fuzz Productions](https://fuzzproductions.com).

## Starting Point

A small python command-line program is used as the control to be transpiled into a variety of languages.

```python
class Pie:

    def __init__(self, filling):
        self.fill_with(filling)
        self.eaten = False

    def eat(self):
        self.eaten = True

    def fill_with(self, filling):
        self.filling = filling


apple_pie = Pie('apple')
apple_pie.eat

frame = ''.join(['*' for s in xrange(28)])

print frame
print "* Who ate the %s pie?!? *" % apple_pie.filling
print frame
```

## Conversions

- `Python` (through [PythonJS](https://github.com/PythonJS/PythonJS)) ➢ `JS`
- `Ruby` (through [Opal](http://opalrb.org/)) ➢ `JS`
- `JS` (through [js2php](https://github.com/sstur/js2php)) ➢ `PHP`
- `JS` (through [js2py](https://github.com/PiotrDabkowski/Js2Py)) ➢ `Python`

##### BONUS!
- `JS` (through [js2hd](https://github.com/hummingbirdtech/hodor)) ➢ `Hodor`
- `Dogescript` (through [dogescript](https://dogescript.com/)) ➢ `JS`

## Execute

Install the dependencies and start the demo by running `magic.sh` bash script in the root directory. The script will run through all six conversions and test the resulting code. 
