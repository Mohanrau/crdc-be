var app = require('express')();

var http = require('http').Server(app);

var io = require('socket.io')(http);

var Redis = require('ioredis');

var redis = new Redis();

redis.psubscribe('*', function(err, count) {});

redis.on('pmessage', function(subscribed, channel, message) {
    console.log('Channel: ' + channel);

    console.log('Message Recieved: ' + message);

    message = JSON.parse(message);

    io.emit(channel + ':' + message.event, message.data);
});


http.listen(3000, function(){
    console.log('Listening on Port 3000');
});
/*
TODO clean the bellow part
redis.monitor(function (err, monitor) {
    monitor.on('monitor', function (time, args, source, database) {
        console.log('time : ' + time);

        console.log('source : ' + source);

        console.log('database : ' + database);
    });
});
*/
// var room = io.sockets.in('some super awesome room');
// room.on('join', function() {
//     //console.log(channel);
//     console.log("Someone joined the room.");
// });
// room.on('leave', function() {
//     console.log("Someone left the room.");
// });