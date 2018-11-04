<?php
mkdir($argv[1]);
$list=json_decode(file_get_contents('http://down.cpan.info/'.$argv[1].'/list.json'),true);
if(!is_array($list)){
	die('file does not exist');
}
/*
	$cli = new swoole_http_client('down.cpan.info', 80);
	$cli->setHeaders([
    'Host' => "localhost",
    "User-Agent" => 'Chrome/49.0.2587.3',
    'Accept' => '*',
    'Accept-Encoding' => 'gzip',
	]);*/
foreach($list as $file){
	echo $file.PHP_EOL;
	//$cli->download('/'.$argv[1].'/'.$file, __DIR__.'/'.$argv[1].'/'.$file, function ($cli) {});
	file_put_contents($argv[1].'/'.$file,file_get_contents('http://down.cpan.info/'.$argv[1].'/'.$file));
}