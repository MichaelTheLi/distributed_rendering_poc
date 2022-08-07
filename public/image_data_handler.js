class ImageHandler {
  width;
  height;
  canvasEl;
  ctx;
  loading = false;

  constructor(width, height, canvasEl) {
    this.width = width;
    this.height = height;
    this.canvasEl = canvasEl;

    this.init();
  }

  init() {
    this.ctx = this.canvasEl.getContext('2d');
    this.canvasEl.setAttribute('width', this.width);
    this.canvasEl.setAttribute('height', this.height);
  }

  render(imageData) {
    const imageDataFull = this.createFullImageData();
    imageDataFull.data.set(imageData);
    this.ctx.putImageData(imageDataFull, 0, 0);
    this.loading = false;
  }

  createFullImageData() {
    return this.ctx.createImageData(this.width, this.height);
  }

}
