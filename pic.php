<?php
define('ROOT',__DIR__);
require_once 'functions.php';
$url=$_GET['url'];
//$url="ZzdWU1Z2ck9ESHFvTW1oNVFSakcxbnVhU1lKelZhK085WHNhVWQvdjRma1dyMms0OjpJChS3wh0VXg%2Bfvty2PCb6";
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
    $file->set($_GET['url'],base64_encode($data));
}else{
    $data=base64_decode($data);
}
//$mime = image_type_to_mime_type(exif_imagetype($url)); //获取图片的 MIME 类型
header('Content-Type:'.'image/jpeg');
echo $data;