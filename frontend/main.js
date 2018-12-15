// Создает WebSocket - подключение.
const socket = new WebSocket('ws://pof.test:8080/index.php');
socket.binaryType = 'arraybuffer';

const width = 120;
const height = 80;

const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');
canvas.setAttribute('width', width);
canvas.setAttribute('height', height);

const START_FULL_RENDER_MESSAGE = 1;
const FULL_RENDER_STARTED_MESSAGE = 2;
const FULL_RENDER_DONE_MESSAGE = 3;

const createFullImageData = function () {
  return ctx.createImageData(width, height);
};

let loading = false;

const getStartMessage = function () {
  const byteArray = new Uint8Array(1);
  byteArray[0] = START_FULL_RENDER_MESSAGE;

  return byteArray.buffer;
};

let count = 0;

const initializeApp = function () {
  socket.addEventListener('message', function (event) {
    let data = event.data;

    if (data instanceof ArrayBuffer) {
      data = new Uint8Array(data);
    }

    if (data[0] === FULL_RENDER_DONE_MESSAGE) {
      const imageData = data.slice(1);
      const imageDataFull = createFullImageData();
      imageDataFull.data.set(imageData);
      ctx.putImageData(imageDataFull, 0, 0);
      loading = false;
      count++;
    }
  });

  setInterval(function () {
    if (loading === false) {
      loading = true;
      socket.send(getStartMessage());
    }
  },0);
};


setInterval(function () {
  const fpsValue = count;
  count = 0;
  const fpsEl = document.getElementById('fps');
  fpsEl.innerText = fpsValue.toFixed(1);
}, 1000);

// Соединение открыто
socket.addEventListener('open', function (event) {
 console.log('Opened');

 initializeApp();
});

