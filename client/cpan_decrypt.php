<?php
//php cpan_decrypt.php {} fromdir tofile password
$argv[1]=base64_decode($argv[1]);
require_once('security.php');
$Security= new Security();
$Security->SetMP('aes-128-cbc',$argv[4]);
if($Security->Decrypt(file_get_contents($argv[2].'test'))==false){
	die('wrong password');
}
$list=json_decode($argv[1],true);
file_put_contents($argv[3],'');
foreach($list as $file){
	while(!file_exists($argv[2].$file)){
		sleep(1);
	}
	file_put_contents($argv[3],$Security->Decrypt(file_get_contents($argv[2].$file)),FILE_APPEND);
}
echo "success".PHP_EOL;