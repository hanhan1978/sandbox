#!/usr/bin/env php
<?php

$pid = pcntl_fork();

if($pid === 0){
  echo("child \n");
  sleep(3);
}else{
  echo("parent [before child wait] \n");
  pcntl_wait($status);
  echo("parent [after child wait] \n");
  
}