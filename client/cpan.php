<?php
//苟!!!!

$cpan = new swoole_client(SWOOLE_SOCK_TCP);
if (!$cpan->connect('35.237.187.165', 2333, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
if($cpan->recv()=='0000'){
$cpan->send(json_encode(array('name'=>'test','length'=>filesize('test')))."\n");
}
if($cpan->recv()=='0001'){
$cpan->sendfile('test');
}