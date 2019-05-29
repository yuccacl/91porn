## 91视频的PHP项目

91Porn手机版，完全无广告，无多余信息，突破游客每天只能看10次的限制。


## 原理简介

项目基于PHP,自动获取91视频，并解析真实地址,通过伪装客户端IP，绕过游客10次观看限制

样例地址 ：<a href="http://scjtqs.vastserve.com/" target="_blank" >http://scjtqs.vastserve.com</a>国外的免费php空间，速度很渣


1.直接进入设置可设置访问域名和页码，如果部署至国内服务器，需设置免番地址，国外服务器推荐用原始地址 http://ip/set.php
默认了一个国内可用源。国外服务完全推荐改成原始地址。

<img src="frozenui/img/007452UMly1foya1v9unwj30a40aojri.png"/>

2.点击确认进入列表页，可直接打开http://ip/index.php  

<img src="frozenui/img/007452UMly1foya2fnqbzj30b70hfq6o.png"/>

3.点击进入视频详情页

<img src="frozenui/img/007452UMly1foya3q7nn8j30b60bf763.png"/>

如无法正常播放，直接刷新页面即可。

4.下载视频，视频详情页提供了解析出的真实地址，

## 配置说明

<b>环境要求</b>
<ol>
<li>PHP 5.6 以上</li>
<li>安装了openssl扩展（闲得蛋疼加密了url）</li>
<li>服务器要有访问外网权限</li>
</ol>

如果你的服务器没有翻墙能力，需要用能翻墙的http代理 或者ss的socks5代理服务共享

请在网站根目录下创建 config.php文件
内容如下
```php
<?php
$proxy="http://192.168.0.1:1282";//代理服务器地址 支持http,socks4,socks5 如果没有请留空
// eg:// http://192.168.0.1:80;socket5://192.168.0.5:4455;这样的，记得要带端口号。代理地址只填一个，没做多个检测随机抽取功能。
$key='hello_world';//aes密码
```


推荐使用nginx的fastcgi_cache-purge缓存加速
nginx使用 fastcgi_cache-purge的样例，请勿直接抄袭，需要根据自己的实际情况更改
```
####################################################################################################
#     Nginx开启fastcgi_cache-purge缓存加速，支持html伪静态页面 By 张戈博客
#     文章地址：http://zhangge.net/5042.html ‎
#     参 考 ①：http://jybb.me/nginx-wordpress-fastcgi_cache-purge
#     参 考 ②：https://rtcamp.com/wordpress-nginx/tutorials/single-site/fastcgi-cache-with-purging/
#     转载本文请务必保留以上申明，谢谢合作！
####################################################################################################
 
#下面各个参数的含义请自行百度，我就不赘述了
#下面2行的中的wpcache路径请自行提前创建，否则可能会路径不存在而无法启动nginx，max_size请根据分区大小自行设置
fastcgi_cache_path /tmp/wpcache levels=1:2 keys_zone=WORDPRESS:250m inactive=1d max_size=1G;
fastcgi_temp_path /tmp/wpcache/temp;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
fastcgi_cache_use_stale error timeout invalid_header http_500;
#忽略一切nocache申明，避免不缓存伪静态等
fastcgi_ignore_headers Cache-Control Expires Set-Cookie;
#Ps：如果是多个站点，以上内容不要重复添加，否则会冲突，可以考虑将以上内容添加到nginx.conf里面，避免加了多次。
server
    {
        listen 80;
        #请修改为自己的域名
        server_name zhangge.net;
        index index.html index.htm index.php default.html default.htm default.php;
        #请修改为自己网站的存放路径
        root  /home/wwwroot/zhangge.net;
       
        set $skip_cache 0;
        #post访问不缓存
        if ($request_method = POST) {
            set $skip_cache 1;
        }   
        #动态查询不缓存
        if ($query_string != "") {
            set $skip_cache 1;
        }   
        #后台等特定页面不缓存（其他需求请自行添加即可）
        if ($request_uri ~* "/wp-admin/|/xmlrpc.php|wp-.*.php|/feed/|index.php|sitemap(_index)?.xml") {
            set $skip_cache 1;
        }   
        #对登录用户、评论过的用户不展示缓存（这个规则张戈博客并没有使用，所有人看到的都是缓存）
        if ($http_cookie ~* "comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_no_cache|wordpress_logged_in") {
            set $skip_cache 1;
        }
        #这里请参考你网站之前的配置，特别是sock的路径，弄错了就502了！
        location ~ [^/]\.php(/|$)
            {
                try_files $uri =404;
                fastcgi_pass  unix:/tmp/php-cgi.sock;
                fastcgi_index index.php;
                include fastcgi.conf;  
                #新增的缓存规则
                fastcgi_cache_bypass $skip_cache;
                fastcgi_no_cache $skip_cache;
                add_header X-Cache "$upstream_cache_status From $host";
                fastcgi_cache WORDPRESS;
                fastcgi_cache_valid 200 301 302 1d;
        }
        location / {
                #此处可以添加自定义的伪静态规则（之前你新增的伪静态规则可以添加到这，没有就不用了）
                try_files $uri $uri/ /index.php?$args;
                rewrite /wp-admin$ $scheme://$host$uri/ permanent;
         }
        #缓存清理配置（可选模块，请细看下文说明）
        location ~ /purge(/.*) {
            allow 127.0.0.1;
            allow "此处填写你服务器的真实外网IP";
            deny all;
            fastcgi_cache_purge WORDPRESS "$scheme$request_method$host$1";
        }
    
        location ~* ^.+\.(ogg|ogv|svg|svgz|eot|otf|woff|mp4|ttf|rss|atom|jpg|jpeg|gif|png|ico|zip|tgz|gz|rar|bz2|doc|xls|exe|ppt|tar|mid|midi|wav|bmp|rtf)$ {
                access_log off; log_not_found off; expires max;
        }
 
        location = /robots.txt { access_log off; log_not_found off; }
        location ~ /\. { deny  all; access_log off; log_not_found off; }
        #请注意修改日志路径
        access_log /home/wwwlogs/zhangge.net.log access;
}
```
