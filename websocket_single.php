#!/usr/bin/env php
<?php

$port = isset($argv[1]) && intval($argv[1]) ? $argv[1] : 8888;

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_bind($sock, '127.0.0.1', $port);
socket_listen($sock);

$handshaked = false;

while($c = socket_accept($sock)){
  while($rec = socket_read($c, 4096)){
    if(!$handshaked){
      $heads = read_handshake($rec);
      socket_write($c, res_handshake($heads));
      $handshaked = true;
    }else{
      $str = decode_bin($rec);
      echo $str . "\n";
      socket_write($c, encode("received [". $str . "]"));
    }
  }
}
socket_close($sock);


function read_handshake($header){
  foreach(explode("\n", $header) as $line){
    $head = explode(':', $line); 
    if(count($head) > 1){
      $ret[trim($head[0])] = trim($head[1]);
    }
  }
  return $ret;
}

function res_handshake($heads){
  $accept = base64_encode(sha1($heads['Sec-WebSocket-Key'].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));;
  $header  = "HTTP/1.1 101 Switching Protocols\r\n";
  $header .= "Upgrade: websocket\r\n";
  $header .= "Connection: Upgrade\r\n";
  $header .= "Sec-WebSocket-Accept: $accept\r\n";
  $header .= "\r\n";
  return $header;
}

function decode_bin($data){
  $res = '';
  $offset = 6;
  $mask = substr($data, 2, 4);
  $payloadlength = ord($data[1]) &127;
  $length = $payloadlength + $offset;

  for($i=$offset; $i<$length; $i++){
    $j = $i - $offset;
    $res .= $data[$i] ^ $mask[$j % 4];
  }
  return $res;
}


function encode($text){
  $head = [];
  $head[0] = chr(129);
  $head[1] = chr(strlen($text) + 128);
  for($i=0; $i<4; $i++){
    $mask[$i] = chr(rand(0, 255));
  }
  $head = array_merge($head, $mask);
  $frame = implode('', $head);

  for($i=0; $i<strlen($text); $i++){
    $frame .= $text[$i] ^ $mask[$i % 4];
  }
  return $frame;
}
