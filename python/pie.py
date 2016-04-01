# encoding=utf8

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
