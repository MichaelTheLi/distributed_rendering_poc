class SocketTransport {
  _path;
  _socket;
  _listeners = [];

  constructor(path) {
    this._path = path;

    this.init();
  }

  init() {
    this._socket = new WebSocket(this._path);
    this._socket.binaryType = 'arraybuffer';

    this._socket.addEventListener('open', this.onOpen.bind(this));
    this._socket.addEventListener('message', this.onMessage.bind(this));

  }

  addListener(listener) {
    this._listeners.push(listener)
  }
  onOpen(event) {
    console.log('Transport: opened');

    this.notifyListeners('open', event)
  }

  onMessage(event) {
    let data = event.data;

    if (data instanceof ArrayBuffer) {
      data = new Uint8Array(data);
    }

    this.notifyListeners(data[0], event, data.slice(1))
  }

  notifyListeners(eventName, event, data) {
    this._listeners.forEach(function (listener) {
      if (listener.event === eventName) {
        listener.onEvent(event, data);
      }
    })
  }

  send(data) {
    this._socket.send(data)
  }
}
