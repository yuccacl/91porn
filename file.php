<?php
define('ROOT',__DIR__);
require_once 'functions.php';
$url=$_GET['url'];
//$url="RGNLNXQwUEJTa3h0YmhFbnZEc2RyVUZwOGFIVHhNaW5NRlVraGkvdFprZlZiWU5tclhxdGMxREovSGFzZmdtN2V0VUh1U3RSNGNiV0ZZN2dBRGkwQ3Z4clVYbHphODgrQVhKUXJKMUxQbkdWS1lQa3orWDJ5T3lVMGFPcStTM0luZENFZUZQYUgzNXFpRW9COjoiKkr8ODKBbPHsKp2CIksW";
if(!$url){
    httpStatus(404);
}
$file=new \lib\cache\driver\File();
$data=$file->get($url);
if(!$data){
    $url=$aes->decrypt(urldecode($url));
    if(!$url){
        httpStatus(403);
    }
    $data=getHtml($url);
    $file->set($_GET['url'],$data,7200);
}
header('Content-Type: application/javascript');
echo $data;