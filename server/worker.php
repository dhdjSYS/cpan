<?php
require_once('Security.php');
$Security= new Security();
if(!isset($argv[1]) or !isset($argv[2])){
	system('rm -rf '.$argv[1]);
	exit();
}
if(file_exists($argv[1].'info.json')){
	$info=json_decode(file_get_contents($argv[1].'info.json'),true);
}else{
	system('rm -rf '.$argv[1]);
	exit();
}
$base=dirname(__FILE__).'/';
@mkdir($base.'files/'.$info['name'].'/');
$Security->SetMP('aes-128-cbc',$info['password']);
$size=filesize($argv[1].'file');
if($size>8*1024*1024){
	 $offset=$size%(8*1024*1024);
	 $msize=$size-$offset;
	 $times=$msize/(8*1024*1024);
	 $handle=fopen($argv[1].'file','r');
	 $list=array();
	 for($x=1;$x<=$times;$x++){
	 	file_put_contents($base.'files/'.$info['name'].'/part_'.$x,$Security->Encrypt(fread($handle,8*1024*1024)));
	 	$list[]='part_'.$x;
	 }
	 if($offset!=0){
		file_put_contents($base.'files/'.$info['name'].'/part_x',$Security->Encrypt(fread($handle,$offset)));
	 	$list[]='part_x';
	 }
	 file_put_contents($base.'files/'.$info['name'].'/list.json',json_encode($list));
}else{
	file_put_contents($base.'files/'.$info['name'].'/part_1',file_get_contents($argv[1].'file'));
	file_put_contents($base.'files/'.$info['name'].'/list.json',json_encode(array('part_1')));
}
file_put_contents($base.'files/'.$info['name'].'/password',$argv[2]);