function Pie (filling) { 
this.fill(filling);
this.eaten = false 
} 

Pie.prototype.eat = function() { 
this.eaten = true; 
};

Pie.prototype.fill = function(filling) { 
this.filling = filling; 
} 

var apple_pie = new Pie("apple");

apple_pie.eat();

var frame = '';

for ( var woof  = 0 ; woof  <= 33 ; woof  += 1 ) {
frame = frame + '🐶' 
} 

console.log(frame);
console.log('🐶 wow   much pie  such delishus 🐶');
console.log(frame);

