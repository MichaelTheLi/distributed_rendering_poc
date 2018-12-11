// Создает WebSocket - подключение.
const socket = new WebSocket('ws://pof.test:8085/api/index.php');
// const socket = new WebSocket('ws://localhost:8085');

// Соединение открыто
socket.addEventListener('open', function (event) {
 socket.send('Hello Server!');
 console.log('Sent message');
});

// Наблюдает за сообщениями
socket.addEventListener('message', function (event) {
  console.log('Message from server ', event.data);
});
