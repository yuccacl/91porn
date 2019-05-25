<?php
require_once 'functions.php';
$url=$_GET['url'];
//$url="encyT0d0Zm5OcnRqNEE5ZnNTM0NUMTJHdktXRXliWTBDbTNQU25ycVl3TE9HdEtEOjqkJq2sY6f0XavEaEhh07Ym";
if(!$url){
    httpStatus(404);
}
$url=$aes->decrypt(urldecode($url));
if(!$url){
    httpStatus(403);
}
header('Content-Type: application/javascript');
echo getHtml($url);