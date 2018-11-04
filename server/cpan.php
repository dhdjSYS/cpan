<?php
//苟利国家生死以 岂因祸福避趋之 Protocol Ver.1
//创建files和cache文件夹
@mkdir('files');
@mkdir('cache');
//新建swoole对象
$cpan = new swoole_server('0.0.0.0', 2333, SWOOLE_BASE, SWOOLE_SOCK_TCP);
//swoole设置
$cpan->set(array(
	//4个worker线程
    'worker_num' => 8,
    //4个reactor线程
    'reactor_num' => 4,
    //不daemonize
    'daemonize' => false,
    //设置最大连接数为100
    'max_connection' => 100,
    //随机分配worker
    'dispatch_mode' => 3,
    //每个链接提供8m buffer
    'socket_buffer_size' => 16 * 1024 *1024,
    //我不知道这是啥设置
    'backlog' => 128,
));
//设置回调函数
$cpan->on('Connect', 'onConnect');
$cpan->on('Receive', 'onReceive');
$cpan->on('Close', 'onClose');
//开启swoole服务器
$cpan->start();

function onConnect($server, $fd, $reactorId){
	//获取Cache文件夹名称
	$fdCache=md5(md5($server->connection_info($fd)['reactor_id']+$server->connection_info($fd)['server_fd']+$server->connection_info($fd)['server_port']+$server->connection_info($fd)['remote_port']).md5($server->connection_info($fd)['remote_ip']));
	//获取Cache文件夹目录
	$fdDir=dirname(__FILE__).'/cache/'.$fdCache.'/';
	//如果Cache文件夹存咋
	while(file_exists($fdDir.'info.json')){
		//那就等待
		sleep(1);
	}
	//这时候说明正常了 那就创建Cache文件夹
	@mkdir($fdDir);
	//放置信息文件
	file_put_contents($fdDir.'info.json',json_encode(array('status'=>'starting','name'=>null,'length'=>null,'password'=>'0')));
	echo '['.date("Y-m-d H:i:s").']'."与".$server->connection_info($fd)['remote_ip'].':'.$server->connection_info($fd)['remote_port'].'创建链接成功. fd='.$fd.' reactorId='.$reactorId.' fdCache='.$fdCache.PHP_EOL;
	//发送允许开始传输头数据的数据
	$server->send($fd,'0000');
}
function onReceive($server, $fd, $reactorId, $data){
	//获取Cache文件夹名称
	$fdCache=md5(md5($server->connection_info($fd)['reactor_id']+$server->connection_info($fd)['server_fd']+$server->connection_info($fd)['server_port']+$server->connection_info($fd)['remote_port']).md5($server->connection_info($fd)['remote_ip']));
	//获取Cache文件夹目录
	$fdDir=dirname(__FILE__).'/cache/'.$fdCache.'/';
	//获取information
	$info=json_decode(file_get_contents($fdDir.'info.json'),true);
	//判断状态
	switch($info['status']){
		case 'transfering':
		file_put_contents($fdDir.'file', $data, FILE_APPEND);
		if(rand(0,10000)==5000){
			echo '['.date("Y-m-d H:i:s").']'."从".$server->connection_info($fd)['remote_ip'].':'.$server->connection_info($fd)['remote_port'].'获取文件数据中. fd='.$fd.' reactorId='.$reactorId.PHP_EOL;
		}
		clearstatcache();
		if(filesize($fdDir.'file')>=$info['length']){
			$server->send($fd,'0002');
			sleep(1);
			$server->close($fd,true);
		}
		break;
		case 'starting':
		if(!is_array($information=json_decode($data,true))){
			echo '['.date("Y-m-d H:i:s").']'."从".$server->connection_info($fd)['remote_ip'].':'.$server->connection_info($fd)['remote_port'].'获取文件信息失败. fd='.$fd.' reactorId='.$reactorId.PHP_EOL;
			$server->close($fd,true);
		}else{
			if(!isset($information['name']) or !isset($information['length']) or !isset($information['password'])){
				$server->close($fd,true);
			}
			file_put_contents($fdDir.'info.json',json_encode(array('status'=>'transfering','name'=>$information['name'],'length'=>$information['length'],'password'=>$information['password'])));
			file_put_contents($fdDir.'file', '');
			echo '['.date("Y-m-d H:i:s").']'."从".$server->connection_info($fd)['remote_ip'].':'.$server->connection_info($fd)['remote_port'].'获取文件信息成功. fd='.$fd.' reactorId='.$reactorId.PHP_EOL;
			if($information['name']=='.' or $information['name']=='..' or strstr($information['name'],'/')){
				$server->close($fd,true);
			}else{
				$server->send($fd,'0001');
			}
		}
		break;
		default:
			echo '['.date("Y-m-d H:i:s").']'."与".$server->connection_info($fd)['remote_ip'].':'.$server->connection_info($fd)['remote_port'].'的链接不正常,即将断开连接. fd='.$fd.' reactorId='.$reactorId.PHP_EOL;
			$server->close($fd,true);
		break;
	}
}
function onClose($server, $fd, $reactorId){
	//获取Cache文件夹名称
	$fdCache=md5(md5($server->connection_info($fd)['reactor_id']+$server->connection_info($fd)['server_fd']+$server->connection_info($fd)['server_port']+$server->connection_info($fd)['remote_port']).md5($server->connection_info($fd)['remote_ip']));
	//获取Cache文件夹目录
	$fdDir=dirname(__FILE__).'/cache/'.$fdCache.'/';
	//获取information
	$info=json_decode(file_get_contents($fdDir.'info.json'),true);
	if(file_exists($fdDir.'file')){
		clearstatcache();
		if(filesize($fdDir.'file')==$info['length']){
			echo '['.date("Y-m-d H:i:s").']'."从".$server->connection_info($fd)['remote_ip'].':'.$server->connection_info($fd)['remote_port'].'传输的文件即将被处理. fd='.$fd.' reactorId='.$reactorId.PHP_EOL;
				$pid = Swoole\Async::exec("/usr/bin/env php worker.php ".$fdDir.' '.md5($info['password']), function ($result, $status) {});
		}else{
			system('rm -rf '.$fdDir);
		}
	}else{
		system('rm -rf '.$fdDir);
	}
	echo '['.date("Y-m-d H:i:s").']'."与".$server->connection_info($fd)['remote_ip'].':'.$server->connection_info($fd)['remote_port'].'的连接断开. fd='.$fd.' reactorId='.$reactorId.PHP_EOL;
}