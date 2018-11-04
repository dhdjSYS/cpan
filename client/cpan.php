<?php
//苟!!!!

$cpan = new swoole_client(SWOOLE_SOCK_TCP);
if (!$cpan->connect('35.237.187.165', 2333, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
if($cpan->recv()=='0000'){
$cpan->send(json_encode(array('name'=>'崔永元傻逼.dmg','length'=>filesize('崔永元傻逼.dmg'),'password'=>0)));
}
if($cpan->recv()=='0001'){
$cpan->sendfile('崔永元傻逼.dmg');
}
if($cpan->recv()=='0002'){
	$cpan->close();
}