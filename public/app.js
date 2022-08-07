class App {
  START_FULL_RENDER_MESSAGE = 1;
  FULL_RENDER_STARTED_MESSAGE = 2;
  FULL_RENDER_DONE_MESSAGE = 3;

  _loading = false;
  width = 120;
  height = 80;
  desiredFps = 24;

  init() {
    this.transport = new SocketTransport('ws://pof.test:8086/api/index.php');

    this.transport.addListener({
      event: 'open',
      onEvent: this.initRendering.bind(this)
    })

    document.getElementById('width').addEventListener('change', function () {
      this.width = event.target.value
      this.initRendering();
    }.bind(this))
    document.getElementById('height').addEventListener('change', function () {
      this.height = event.target.value
      this.initRendering();
    }.bind(this))
  }

  initRendering() {
    this.image = new ImageHandler(this.width, this.height, document.getElementById('canvas'));
    this.fps = new FPS(document.getElementById('fps'));

    this.startLoop()
    this.transport.addListener(this.messageListener())
  }

  startLoop() {
    const timeout = 1000 / this.desiredFps;
    setInterval(this.loop.bind(this), timeout);
  }

  loop() {
    if (this._loading === false) {
      this._loading = true;
      this.transport.send(this.buildStartMessage());
    }
  }

  buildStartMessage() {
    const byteArray = new Uint8Array(3);
    byteArray[0] = this.START_FULL_RENDER_MESSAGE;
    byteArray[1] = this.width;
    byteArray[2] = this.height;

    return byteArray.buffer;
  }

  messageListener() {
    return {
      event: this.FULL_RENDER_DONE_MESSAGE,
      onEvent: this.onFullRenderEvent.bind(this)
    }
  }

  onFullRenderEvent(event, data) {
    this._loading = false;

    this.image.render(data)
    this.fps.bump();
  }
}
