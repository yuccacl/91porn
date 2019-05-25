<?php
error_reporting(0);
#引入模块
require_once 'lib/phpQuery.php';
require_once 'lib/QueryList.php';
require_once 'lib/aes.php';
include_once "lib/Snoopy.class.php";
//require_once 'vendor/autoload.php';
include_once 'config.php';

$aes=new aes(isset($key)?$key:'hello_world');
ini_set('memory_limit', '1024M');
function getList($domain="http://www.91porn.com",$page = 1){
    //video.php?category=hot&viewtype=basic 当前最热
    //video.php?category=rp&viewtype=basic 最近得分
    //video.php?category=long&viewtype=basic  十分钟以上
    //video.php?category=md&viewtype=basic 本月讨论
    //video.php?category=tf&viewtype=basic 本月收藏
    //video.php?category=mf&viewtype=basic 收藏最多
    //video.php?category=rf&viewtype=basic  最近加精
    //video.php?category=top&viewtype=basic 本月最热
    //video.php?category=top&m=-1&viewtype=basic 上月最热
    //video.php?category=hd&viewtype=basic 高清

    $category = $_COOKIE["category"];

	$url = $domain."/video.php?". ($category == '' ? "" : "category={$category}") ."&page=".$page;
    //echo $url;
	$html = getHtml($url);
	//echo $html;

	$html = preg_replace('/<span class="title">(.*)/', '', $html);
    $html=substr($html,4);
    $dom = new \DOMDocument();
    //从一个字符串加载HTML
    @$dom->loadHTML($html);
    //使该HTML规范化
    $dom->normalize();
    //用DOMXpath加载DOM，用于查询
    $xpath = new \DOMXPath($dom);
    $picList=$xpath->query('//*[@id="videobox"]/table/tr/td/div/div[1]/a/img/@src');
    $titleList=$xpath->query('//*[@id="videobox"]/table/tr/td/div/div[1]/a/img/@title');//txt
    $linkList=$xpath->query('//*[@id="videobox"]/table/tr/td/div/div[1]/a/@href');
    $info2=$xpath->query('//*[@id="videobox"]/table/tr/td/div');
    for($i=0;$i<=$picList->length-1;$i++){
        $data[$i]['pic']=$picList->item($i)->nodeValue;
        $data[$i]['title']=$titleList->item($i)->nodeValue;
        $data[$i]['link']=$linkList->item($i)->nodeValue;
        $info3=$info2->item($i);
        $info=$info3->ownerDocument->saveHTML($info3);
        $info=preg_replace("/<\/?div[^>]*>/",'',$info);//去掉class=imagechannel的外围标签
        $info=preg_replace("/<\/?a[^>]*>/",'',$info);//去掉a外围标签
        $info=preg_replace("/<\/?img[^>]*>/",'',$info);//去掉img外围标签
        $info=preg_replace("/<\/?br[^>]*>/",'',$info);//去掉img外围标签
//        $info=str_replace(array("\r\n","\r","\n","\t"),'<br/>',$info);
        $info=(trim($info));
        $info=mb_substr($info,0,mb_strlen($info)-33);
//        $info=trim($info,'<br>');
        $data[$i]['info']=nl2br($info);//去掉img外围标签
//        $data[$i]['info']=str_replace(array("\r\n","\r","\n","\t"),'<br/>',$info);
        unset($info);
    }
//	$data = \QL\QueryList::Query($html,$rules,'','','',true)->data;
	//print_r($data);
	return $data;
}

function randIp(){
    return rand(50,250).".".rand(50,250).".".rand(50,250).".".rand(50,250);
}


//根据地址，获取视频地址
function getVideo($url){

	$html = getHtml($url);
    $html=substr($html,4);
    $dom = new \DOMDocument();
    //从一个字符串加载HTML
    @$dom->loadHTML($html);
    //使该HTML规范化
    $dom->normalize();
    //用DOMXpath加载DOM，用于查询
    $xpath = new \DOMXPath($dom);
    $title=$xpath->query('//*[@id="viewvideo-title"]')->item(0)->textContent;
    $video=$xpath->query('//*[@id="vid"]/script[1]')->item(0);//video
//    $info=$xpath->query('//*[@id="useraction"]/div[1]')->item(0);
    $video=$video->ownerDocument->saveHTML($video);
//    $info=$info->ownerDocument->saveHTML($info);
    $data=[
        'video'=>$video,
        'title'=>$title,
//        'info'=>$info,
    ];
	//print_r($data);
	return $data;
}


function getHtml($url){

    $ip = randIp();
    $snoopy = new Snoopy;
    //添加koolshare的v2ray的http代理地址
    if(isset($proxy)){
        //配置有代理
        $header = [
            'X-FORWARDED-FOR:' . $ip,
            'CLIENT-IP:' . $ip,
            'Accept-language: zh-cn',
            'HTTP_X-FORWARDED-FOR:' . $ip,
            'Content-Type: text/html:charset=utf-8',
        ];
        $result=superCurl($url,',',$header,$proxy);
        return $result['data'];
    }else{
        $snoopy->rawheaders["Accept-language"] = "zh-cn"; //cache 的http头信息
        $snoopy->rawheaders["Content-Type"] = "text/html; charset=utf-8"; //cache 的http头信息
        $snoopy->rawheaders["CLIENT-IP"] = $ip; //伪装ip
        $snoopy->rawheaders["HTTP_X_FORWARDED_FOR"] = $ip; //伪装ip

        $snoopy->fetch($url);
        return $snoopy->results;
    }

}
function fun_adm_each(&$array){
    $result = array();
    $key = key($array);
    if(!is_null($key)){
        $val = $array[$key];

        $result[1] = $val;
        $result['value'] = $val;
        $result[0] = $key;
        $result['key'] = $key;
        next($array);
    }
    return $result;
}
echo fun_adm_count('');

function fun_adm_count($array_or_countable,$mode = COUNT_NORMAL){
    if(is_array($array_or_countable) || is_object($array_or_countable)){
        return count($array_or_countable, $mode);
    }else{
        return false;
    }
}

function httpStatus($num){//网页返回码
    static $http = array (
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out"
    );
    header($http[$num]);
    exit();
}
/** 带COOKIE 获取数据
 * @param $url
 * @param $cookie
 * @param $proxy 代理地址 可以是字符串，也可以是数组
 * @return mixed
 */
function superCurl($url,$post=null,$cookie=null,$headers=[],$proxy=null){
    $ch =curl_init();
    if($proxy){
        //以下代码设置代理服务器
        //代理服务器地址http://www.cnproxy.com/proxy1.html !!Hong Kong, China的速度比较好
        if(!is_array($proxy)){
            //以下代码设置代理服务器
            //代理服务器地址http://www.cnproxy.com/proxy1.html !!Hong Kong, China的速度比较好
            $proxy_type = explode('://', $proxy)[0];			// http, https, socks4, socks5
            $proxy_ip_port = explode('://', $proxy)[1];			// ip:port
            curl_setopt ( $ch, CURLOPT_HTTPPROXYTUNNEL, false );
            curl_setopt ( $ch, CURLOPT_PROXY, $proxy_ip_port );

            if ($proxy_type == "http") {
                curl_setopt ( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );			// http
            }
            elseif ($proxy_type == "https") {
                curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
                curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );				// https
            }
            elseif ($proxy_type == "socks4") {
                curl_setopt ( $ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4 );		// socks4
            }
            elseif ($proxy_type == "socks5") {
                curl_setopt ( $ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5 );		// socks5
            }
        }else{
            $count=count($proxy);
            $proxyIp=$proxy[mt_rand(0,$count-1)];
            $proxy_type = explode('://', $proxyIp)[0];			// http, https, socks4, socks5
            $proxy_ip_port = explode('://', $proxyIp)[1];			// ip:port
            curl_setopt ( $ch, CURLOPT_HTTPPROXYTUNNEL, false );
            curl_setopt ( $ch, CURLOPT_PROXY, $proxy_ip_port );

            if ($proxy_type == "http") {
                curl_setopt ( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );			// http
            }
            elseif ($proxy_type == "https") {
                curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
                curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );				// https
            }
            elseif ($proxy_type == "socks4") {
                curl_setopt ( $ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4 );		// socks4
            }
            elseif ($proxy_type == "socks5") {
                curl_setopt ( $ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5 );		// socks5
            }
        }
    }
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 302 redirect
    curl_setopt($ch, CURLOPT_MAXREDIRS, 7); //HTTp定向级别
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);//6秒超时设置
    $SSL = substr($url, 0, 8) == "https://" ? true : false;
    if ($SSL) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
    }
    if($post){
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

    if($cookie){
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);
    }
    if($headers){
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_REFERER, $this->base_url);//模拟来路
    }else{
        curl_setopt($ch,CURLOPT_HEADER,false);
    }

    $result = curl_exec( $ch );
    $info = curl_getinfo($ch);
    curl_close( $ch );

    return array(
        'header'=>$info,
        'data'=>$result
    );
}
/*
 * php 页面直接输出图片
 */
function showImg($img){
    $info = getimagesize($img);
    $imgExt = image_type_to_extension($info[2], false); //获取文件后缀
    $fun = "imagecreatefrom{$imgExt}";
    $imgInfo = $fun($img); //1.由文件或 URL 创建一个新图象。如:imagecreatefrompng ( string $filename )
    //$mime = $info['mime'];
    $mime = image_type_to_mime_type(exif_imagetype($img)); //获取图片的 MIME 类型
    header('Content-Type:'.$mime);
    $quality = 100;
    if($imgExt == 'png') $quality = 9; //输出质量,JPEG格式(0-100),PNG格式(0-9)
    $getImgInfo = "image{$imgExt}";
    $getImgInfo($imgInfo, null, $quality); //2.将图像输出到浏览器或文件。如: imagepng ( resource $image )
    imagedestroy($imgInfo);
}