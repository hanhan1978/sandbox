#!/usr/bin/env php
<?php


$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_bind($sock, '127.0.0.1', 8889);
socket_listen($sock);


for ($i=0 ; $i<3; $i++) {
    $pid = pcntl_fork();
    if ($pid) {
        continue;
    } else {
        while ($c = socket_accept($sock)) {
            while (true) {
                $rec = socket_read($c, 4096);
                echo $rec;
                socket_write($c, ">> you wrote : $rec");
                if (trim($rec) == 'quit') {
                    socket_write($c, "bye\n");
                    break;
                }
            }
            socket_close($c);
        }
        socket_close($sock);
    }
}

pcntl_wait($status);
