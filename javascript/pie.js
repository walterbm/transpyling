function Pie(filling) {
  this.fill_with(filling);
  this.eaten = false;
};

Pie.prototype.eat = function() {
  this.eaten = true;
};

Pie.prototype.fill_with = function(filling) {
    this.filling = filling;
};

var apple_pie = new Pie('apple');
apple_pie.eat

var frame = '';
for (i = 0; i < 28; i++) {
    frame = frame + '♦'
}

console.log(frame)
console.log("♦ Who ate the " + apple_pie.filling + " pie?!? ♦")
console.log(frame)
