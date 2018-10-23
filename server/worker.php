<?php
require_once('security.php');
$Security= new Security();
if(!isset($argv[1]) or !isset($argv[2])){
	echo '['.date("Y-m-d H:i:s").']'."致命错误: 参数不存在".PHP_EOL;
	system('rm -rf '.$argv[1]);
	exit();
}
if(file_exists($argv[1].'info.json')){
	$info=json_decode(file_get_contents($argv[1].'info.json'),true);
}else{
	echo '['.date("Y-m-d H:i:s").']'.$argv[1]." 致命错误: 文件夹不存在".PHP_EOL;
	system('rm -rf '.$argv[1]);
	exit();
}
$base=dirname(__FILE__).'/';
@mkdir($base.'files/'.$info['name'].'/');
$Security->SetMP('aes-128-cbc',$info['password']);
$size=filesize($argv[1].'file');
echo '['.date("Y-m-d H:i:s").']'.$argv[1]." 文件大小: ".$size.PHP_EOL;
if($size>10*1024*1024*1024){
	echo '['.date("Y-m-d H:i:s").']'.$argv[1]." 致命错误: 文件太他妈大了".PHP_EOL;
	system('rm -rf '.$argv[1]);
	exit();
}
if($size>8*1024*1024){
	echo '['.date("Y-m-d H:i:s").']'.$argv[1]." 文件大于8MB".PHP_EOL;
	 $offset=$size%(8*1024*1024);
	 $msize=$size-$offset;
	 $times=$msize/(8*1024*1024);
	 echo '['.date("Y-m-d H:i:s").']'.$argv[1]." 8MB区块数量: ".$times.PHP_EOL;
	 echo '['.date("Y-m-d H:i:s").']'.$argv[1]." 残留区块大小: ".$offset.PHP_EOL;
	 $handle=fopen($argv[1].'file','r');
	 $list=array();
	 for($x=1;$x<=$times;$x++){
	 	file_put_contents($base.'files/'.$info['name'].'/part_'.$x,$Security->Encrypt(fread($handle,8*1024*1024)));
	 	echo '['.date("Y-m-d H:i:s").']'.$argv[1]." 8MB区块完成度: ".$x.'/'.$times.PHP_EOL;
	 	$list[]='part_'.$x;
	 }
	 if($offset!=0){
		file_put_contents($base.'files/'.$info['name'].'/part_x',$Security->Encrypt(fread($handle,$offset)));
		echo '['.date("Y-m-d H:i:s").']'.$argv[1]." 残留区块完成".PHP_EOL;
	 	$list[]='part_x';
	 }
	 file_put_contents($base.'files/'.$info['name'].'/list.json',json_encode($list));
}else{
	file_put_contents($base.'files/'.$info['name'].'/part_1',file_get_contents($argv[1].'file'));
	file_put_contents($base.'files/'.$info['name'].'/list.json',json_encode(array('part_1')));
}
file_put_contents($base.'files/'.$info['name'].'/password',$argv[2]);
system('rm -rf '.$argv[1]);
echo '['.date("Y-m-d H:i:s").']'.$argv[1]." 执行完毕".PHP_EOL;