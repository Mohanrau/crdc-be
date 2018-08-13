<!DOCTYPE html>
<head>
    <title>Pusher Test</title>
    <script src="/js/socket.io.js"></script>
    <script>


        const PRIVATE_CHANNEL = 'sales-created'

        //

        var io = io('http://project-masala.lan:3000')

        var host = window.location.host.split(':')[0]

        var socket = io.connect('http://' + host + ':3000', {secure: false, rejectUnauthorized: true})

        console.log(socket)

        socket.on('connect', function () {
            console.log('CONNECT')

            socket.on('.event', function (data) {
                console.log('EVENT', data)
            })

            socket.on('sales-created:sales.new', function (data) {
                console.log('NEW MESSAGE', data)
            })

            socket.on('disconnect', function () {
                console.log('disconnect')
            })

            // Kick it off
            // Can be any channel. For private channels, Laravel should pass it upon page load (or given by another user).
            // socket.emit('messages.new', {channel: PRIVATE_CHANNEL})
            console.log('SUBSCRIBED TO <' + PRIVATE_CHANNEL + '>');
        })

        /*

        Echo.channel('channel').listen('', (e) => {
            console.log(e.id);
        });

        Echo.channel('channel')
        .listen('.messages.new', (e) => {
            console.log(e.article);
        });

        Echo.channel('articles')
        .listen('.messages.new', (e) => {
            console.log(e.article);
        });
        */

    </script>
</head>
<body>
</body>
</html>