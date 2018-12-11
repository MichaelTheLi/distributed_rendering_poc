// Создает WebSocket - подключение.
const socket = new WebSocket('ws://pof.test:8080/index.php');
// const socket = new WebSocket('ws://localhost:8085');

const ctx = document.getElementById('canvas').getContext('2d');

const createImageData = function () {
  return ctx.createImageData(100, 100);
};

const createFullImageData = function () {
  return ctx.createImageData(200, 100);
};

// let imageData = createImageData();

let loading = false;

const initializeApp = function () {
  socket.addEventListener('message', function (event) {
    const fpsEl = document.getElementById('fps');
    const data = JSON.parse(event.data);

    if (data.message === 'done_full') {
      const imageDataFull = createFullImageData();
      imageDataFull.data.set(data.imageData);
      ctx.putImageData(imageDataFull, 0, 0);
      let time = Date.now() - data.startTime;
      setTimeout(function () {
        fpsEl.innerText = (1000 / time).toFixed(1);
      }, 0);
      // console.log((Date.now() - data.startTime) + 'ms');
      loading = false;
    } else if (data.message === 'done') {
      ctx.putImageData(imageData, 0, 0);
      console.log((Date.now() - data.startTime) + 'ms');
    } else if (data.message === 'pixel') {
      imageData.data[data.index + 0] = data.R;  // R value
      imageData.data[data.index + 1] = data.G;  // G value
      imageData.data[data.index + 2] = data.B;  // B value
      imageData.data[data.index + 3] = data.A;  // A value
    }
  });

  // imageData = createImageData();
  // socket.send('render_full_image');

  setInterval(function () {
    if (loading === false) {
      loading = true;
      // imageData = ctx.createImageData(100, 100);
      socket.send(JSON.stringify({
        message: 'render_full_image',
        startTime: Date.now()
      }));
    }
  }, 50);
  // setInterval(function () {
  //   imageData = ctx.createImageData(100, 100);
  //   socket.send('render_image');
  //   startTime = Date.now();
  // }, 2000);
};

// Соединение открыто
socket.addEventListener('open', function (event) {
 console.log('Opened');

 initializeApp();
});

