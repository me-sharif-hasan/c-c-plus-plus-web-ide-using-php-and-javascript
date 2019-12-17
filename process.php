<?php
include("time.php");
ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);


$p = $_POST;
$t=time();
$pre = file_get_contents(__DIR__."/sources/save");
$pre++;
$f = fopen(__DIR__."/sources/save", "w+");
fwrite($f, $pre);
fclose($f);

$source = "sources/$pre._upload_sources_".date('m_d_Y__H_i_s',$t).".c";
$inputs = "sources/$pre._upload_inputs_".date('m_d_Y__H_i_s',$t).".txt";
$out = "sources/$pre._upload_output_".date('m_d_Y__H_i_s',$t).".txt";
$name = "sources/$pre._upload_exe_".date('m_d_Y__H_i_s',$t).".exe";
$obj = "sources/$pre._upload_exe_".date('m_d_Y__H_i_s',$t).".o";

file_put_contents($inputs,$p["input"]);
file_put_contents($source,$p["source"]);


$f = fopen(__DIR__."/sources/last", "w+");
fwrite($f, $p["source"]);
fclose($f);

$f = fopen(__DIR__."/sources/lastTask", "w+");
fwrite($f, str_replace("sources/", "", $name));
fclose($f);


function my_shell_exec($cmd, &$stdout=null, &$stderr=null) {
    $proc = proc_open($cmd,array(
        1 => array('pipe','w'),
        2 => array('pipe','w'),
    ),$pipes);
    $stdout = stream_get_contents($pipes[1]);    
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $rt = proc_close($proc);
    return array($stderr,$stdout,$rt);
}

$e = my_shell_exec('g++  -Wl,--stack,16777216 -std=c++11 -g -o '.$name.' '.$source.'>error.log');
$flag = 0;
$error="";
if($e[0])
{
	$error =str_replace($source.":", "================================\n", $e[0]);
	$f=1;
}
$ts = microtime(true);
$te=0;
$responseData = "";
$rt = 0;
if(!$flag){
$exe = __DIR__."/".$name;
$in = __DIR__."/".$inputs;
$out = __DIR__."/".$out;
$o = my_shell_exec("$exe < $in");
$rt = $o[2];
$te= microtime(true);
$responseData = $o[1];
}
$timeEnd = $te;

if($error!=NULL){
    $responseData = $error;
    $timeEnd = microtime(true);
}
$execution = "\n================================\nExecution time: ".number_format((float)($timeEnd-$ts)+.01, 2, '.', '')." Seconds\nProcess return ".$rt.($rt<0?" [Runtime error].":" ");
$output = json_encode(array("result"=>$responseData,"time"=>$execution));
echo $output;
?>
