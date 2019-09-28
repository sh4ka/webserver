<?php

$server = socket_create(AF_INET, SOCK_STREAM, 0);

if (!socket_bind($server, '127.0.0.1', 8000))
{
    throw new Exception('Error binding');
}

if (!socket_listen($server)) {
    throw new Exception('Error listening');
}

while(true) {
    echo 'Server running on port 8000' . PHP_EOL;
    $newSocket = @socket_accept($server);

    if ($newSocket === false) {
        usleep(100);
    } elseif ($newSocket > 0) {
        handleClient($server, $newSocket);
        socket_close($newSocket);
    } else {
        echo "error: ".socket_strerror($newSocket);
        die;
    }
}

function handleClient($ssocket, $csocket) {
    $data = socket_read($csocket, 1024);
    echo $data . PHP_EOL;
    if (substr($data, 0, 5) === 'GET /') {
        $fileSize = filesize('index.html');
        $header = 'HTTP/1.1 200 OK'.
            PHP_EOL .
            'Content-Type: text/html'.
            PHP_EOL .
            'Server: shaka'.
            PHP_EOL .
            'Content-Length: ' . $fileSize;
        // return data from file
        $file = fopen('index.html', 'r');
        $content = fread($file, $fileSize);
        $contentResponse = $header . PHP_EOL . PHP_EOL. $content;
        $responseSize = strlen($contentResponse);
        $written = 0;
        while ($written < $responseSize) {
            $written += socket_write($csocket, $contentResponse, $responseSize);
        }

        fclose($file);
    }
}