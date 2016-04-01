require 'opal'

class Pie

  attr_reader :filling

  def initialize (filling)
    fill_with(filling)
    @eaten = false
  end

  def eat
    @eaten = true
  end

  private

    def fill_with (filling)
      @filling = filling
    end

end

apple_pie = Pie.new(:apple)
apple_pie.eat

frame = (0..27).reduce('')  { |sum, n| sum + '♦' }

puts frame
puts "♦ Who ate the #{apple_pie.filling} pie?!? ♦"
puts frame
