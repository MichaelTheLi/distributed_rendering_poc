class FPS {
  el;
  framesCount = 0;
  constructor(el) {
    this.el = el;

    setInterval(this.render.bind(this), 1000)
  }

  bump() {
    this.framesCount++;
  }

  render() {
    this.el.innerText = this.framesCount.toFixed(1);
    this.framesCount = 0;
  }
}
