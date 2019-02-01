<?php

require_once "lang.conf.php";

/**
 * Youtube Proxy
 * Simple Youtube PHP Proxy Server
 * @author ZXQ
 * @version V1.2
 * @description 核心操作函数集合
 */

require_once(dirname(__FILE__).'/config.php');

//加载第三方ytb解析库
require_once(dirname(__FILE__).'/YouTubeDownloader.php');
//获取远程数据函数
 function get_data($url){
    if (!function_exists("curl_init")) {
		$f = file_get_contents($url);
	} else {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_REFERER, 'http://www.youtube.com/');
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.91 Safari/534.30");
		$f = curl_exec($ch);
		curl_close($ch);
	}
   return $f;
}
//获取类别热门视频
function get_trending($apikey,$max,$pageToken='',$regionCode=GJ_CODE){
    $apilink='https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&chart=mostPopular&regionCode='.$regionCode.'&maxResults='.$max.'&key='.$apikey.'&pageToken='.$pageToken;
     return json_decode(get_data($apilink),true);
}

//获取视频数据函数
 function get_video_info($id,$apikey){
    $apilink='https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics&id='.$id.'&key='.$apikey;
     return json_decode(get_data($apilink),true);
}

//获取用户频道数据
function get_channel_info($cid,$apikey){
   $apilink='https://www.googleapis.com/youtube/v3/channels?part=snippet,contentDetails,statistics&hl=zh&id='.$cid.'&key='.$apikey;
   return json_decode(get_data($apilink),true);
}

//获取相关视频
function get_related_video($vid,$apikey){
   $apilink='https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&maxResults=24&relatedToVideoId='.$vid.'&key='.$apikey;
   return json_decode(get_data($apilink),true);
}


//获取用户频道视频
function get_channel_video($cid,$pageToken='',$apikey,$regionCode=GJ_CODE){
   $apilink='https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&maxResults=50&type=video&regionCode='.$regionCode.'&hl='.$lang['YT_LANG'].'&channelId='.$cid.'&key='.$apikey.'&pageToken='.$pageToken;
   return json_decode(get_data($apilink),true);
}

//获取视频类别内容
function videoCategories($apikey=APIKEY,$regionCode=GJ_CODE){
   $apicache = '/tmp/ytb_videoCategories_'.$regionCode;
   $json = file_get_contents($apicache);
   if (empty($json)) {
       $apilink='https://www.googleapis.com/youtube/v3/videoCategories?part=snippet&regionCode='.$regionCode.'&hl='.$lang['YT_LANG'].'&key='.$apikey;
       $json = get_data($apilink);
      file_put_contents($apicache,$json);
      file_put_contents($apicache.".ts","REQUEST_TIME: " . $_SERVER['REQUEST_TIME']);
   }
   $ret = json_decode($json,true);
   $items = $ret['items'];
   if (strtolower($regionCode) == 'tw') {
      return array_filter($items, function($v){
         return array_search($v['id'], ['18','21','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44']) === FALSE;
      });
   }
   return $items;
}

function categorieslist($id){
    $categories = videoCategories();
    $data = array();
    foreach ($categories as $k => $v) {
        $data[$v['id']] = $v['snippet']['title'];
    }

    if($id=='all'){
        return $data;
    }else{
        return $data[$id];
    }
}

//获取视频类别内容
function Categories($id,$apikey,$pageToken='',$order='relevance',$regionCode=GJ_CODE){
   $apilink='https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&&regionCode='.$regionCode.'&hl=zh-ZH&maxResults=48&videoCategoryId='.$id.'&key='.$apikey.'&order='.$order.'&pageToken='.$pageToken;
   return json_decode(get_data($apilink),true);
}


//获取搜索数据
function get_search_video($query,$apikey,$pageToken='',$type='video',$order='relevance',$regionCode=GJ_CODE){
   $apilink='https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults=48&order='.$order.'&type='.$type.'&q='.$query.'&key='.$apikey.'&pageToken='.$pageToken;
   return json_decode(get_data($apilink),true);
}

//api返回值时间转换函数1
function covtime($youtube_time){
    $start = new DateTime('@0');
    $start->add(new DateInterval($youtube_time));
    if(strlen($youtube_time)<=7){
      return $start->format('i:s');
    }else{
     return $start->format('H:i:s');
    }

}

//转换时间函数，计算发布时间几天前几年前
function format_date($time){
    $t=strtotime($time);
    $t=time()-$t;
    $f=array(
    '31536000'=>$lang['LIB_DCONV'][0],
    '2592000'=>$lang['LIB_DCONV'][1],
    '604800'=>$lang['LIB_DCONV'][2],
    '86400'=>$lang['LIB_DCONV'][3],
    '3600'=>$lang['LIB_DCONV'][4],
    '60'=>$lang['LIB_DCONV'][5],
    '1'=>$lang['LIB_DCONV'][6]
    );
    foreach ($f as $k=>$v)    {
        if (0 !=$c=floor($t/(int)$k)) {
            return $c.$v.$lang['LIB_AGO'];
        }
    }
}

//api返回值时间转换函数2
function str2time($ts) {
 return date("Y-m-d H:i", strtotime($ts));
}

 //播放量转换
function convertviewCount($value){
    if($value <= 10000){
    $number = $value;
    }else{
      $number = $value / 1000 ;
      $number = round($number,1).'K';

    }

    return $number;
}
//获取banner背景
function get_banner($a,$apikey){
   $apilink='https://www.googleapis.com/youtube/v3/channels?part=brandingSettings&id='.$a.'&key='.$apikey;
   $json=json_decode(get_data($apilink),true);
  if (array_key_exists('bannerTabletImageUrl',$json['items'][0]['brandingSettings']['image'])){
  return $json['items'][0]['brandingSettings']['image']['bannerTabletImageUrl'];
 }else{
  return 'https://c1.staticflickr.com/5/4546/24706755178_66c375d5ba_h.jpg';
 }
}
$videotype=array(
    '3GP144P' => array('3GP','144P','3gpp'),
    '360P' => array('MP4','360P','mp4'),
    '720P' => array('MP4','720P','mp4'),
    'WebM360P' => array('webM','360P','webm'),
    'Unknown' => array('N/A','N/A','3gpp'),
    );

//获取相关频道 api不支持，靠采集完成
require_once(dirname(__FILE__).'/inc/phpQuery.php');
require_once(dirname(__FILE__).'/inc/QueryList.php');
use QL\QueryList;
function get_related_channel($id){
    $channel='https://www.youtube.com/channel/'.$id;
    $rules = array(
    'id' => array('.branded-page-related-channels .branded-page-related-channels-list li','data-external-id'),
    'name' => array('.branded-page-related-channels .branded-page-related-channels-list li .yt-lockup .yt-lockup-content .yt-lockup-title a','text'),
);

return $data = QueryList::Query(get_data($channel),$rules)->data;
}

//采集抓取随机推荐内容
function random_recommend(){
   $dat=get_data('https://www.youtube.com/?gl='.constant("GJ_CODE").'&hl='.$lang['YT_LANG']);
   $rules = array(
    't' => array('#feed .individual-feed .section-list li .item-section li .feed-item-container .feed-item-dismissable .shelf-title-table .shelf-title-row h2 .branded-page-module-title-text','text'),
    'html' => array('#feed .individual-feed .section-list li .item-section li .feed-item-container .feed-item-dismissable .compact-shelf .yt-viewport .yt-uix-shelfslider-list','html'),
        );

    $rules1 = array(
    'id' => array('li .yt-lockup ','data-context-item-id'),
    'title' => array('li .yt-lockup .yt-lockup-dismissable .yt-lockup-content .yt-lockup-title a','text'),
        );

    $data = QueryList::Query($dat,$rules)->data;

    $ldata=array();
    foreach ($data as $v) {
       $d = QueryList::Query($v['html'],$rules1)->data;
       $ldata[]=array(
           't'=> $v['t'],
           'dat' => $d
           );
    }
    array_shift($ldata);
    return $ldata;
}
//视频下载
function video_down($v,$name){
$yt = new YouTubeDownloader();
$links = $yt->getDownloadLinks("https://www.youtube.com/watch?v=$v");
echo '<table class="table table-hover"><thead><tr>
      <th>'.$lang['LIB_D_FORMAT'].'</th>
      <th>'.$lang['LIB_D_TYPE'].'</th>
      <th>'.$lang['LIB_D_DOWN'].'</th>
    </tr>
  </thead>';
foreach ($links as $value) {
    global $videotype;
echo ' <tbody>
    <tr>

      <td>'.$videotype[$value['format']][0].'</td>
      <td>'.$videotype[$value['format']][1].'</td>
      <td><a href="./downvideo.php?v='.$v.'&quality='.$value['format'].'&name='.$name.'&format='.$videotype[$value['format']][2].'" class="btn btn-outline-success btn-sm">'.$lang['LIB_D_DOWN'].'</a></td>
    </tr></tbody>';
    }
    echo '</table>';
}

//判断高清微缩图是否存在
function get_thumbnail_code($vid){
$thumblink='https://img.youtube.com/vi/'.$vid.'/maxresdefault.jpg';
$oCurl = curl_init();
$header[] = "Content-type: application/x-www-form-urlencoded";
$user_agent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36";
curl_setopt($oCurl, CURLOPT_URL, $thumblink);
curl_setopt($oCurl, CURLOPT_HTTPHEADER,$header);
curl_setopt($oCurl, CURLOPT_HEADER, true);
curl_setopt($oCurl, CURLOPT_NOBODY, true);
curl_setopt($oCurl, CURLOPT_USERAGENT,$user_agent);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt($oCurl, CURLOPT_POST, false);
$sContent = curl_exec($oCurl);
$headerSize = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
curl_close($oCurl);
if($headerSize == '404'){
  return 'https://img.youtube.com/vi/'.$vid.'/hqdefault.jpg';
}else{
  return 'https://img.youtube.com/vi/'.$vid.'/maxresdefault.jpg';
}
}


//解析历史记录
function Hislist($str,$apikey){
    $str=str_replace('@',',',$str);
    $apilink='https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id='.$str.'&key='.$apikey;
   return json_decode(get_data($apilink),true);
}

//获取频道所属国家
$CountryID=$lang['LIB_COUNTRY_ID'];
function get_country($c){
    global $CountryID;
    return  $CountryID[$c];
}

//url字符串加解密
function strencode($string,$key='09KxDsIIe|+]8Fo{YP<l+3!y#>a$;^PzFpsxS9&d;!l;~M>2?N7G}`@?UJ@{FDI') {
    $key = sha1($key);
    $strLen = strlen($string);
    $keyLen = strlen($key);
    for ($i = 0; $i < $strLen; $i++) {
        $ordStr = ord(substr($string,$i,1));
        if (@$j == $keyLen) { $j = 0; }
        $ordKey = ord(substr($key,$j,1));
        @$j++;
    @$hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
    }
    return 'Urls://'.$hash;
}
function strdecode($string,$key='09KxDsIIe|+]8Fo{YP<l+3!y#>a$;^PzFpsxS9&d;!l;~M>2?N7G}`@?UJ@{FDI') {
    $string= ltrim($string, 'Urls://');
    $key = sha1($key);
    $strLen = strlen($string);
    $keyLen = strlen($key);
    for ($i = 0; $i < $strLen; $i+=2) {
        $ordStr = hexdec(base_convert(strrev(substr($string,$i,2)),36,16));
        if (@$j == $keyLen) { @$j = 0; }
        $ordKey = ord(substr($key,@$j,1));
        @$j++;
        @$hash .= chr($ordStr - $ordKey);
    }
    return $hash;
}

//分享功能
$title=$lang['LIB_SHAREIT_FWYI'];
function shareit($id,$title){
    $pic=ROOT_PART.'thumbnail.php%3Fvid%3D'.$id;
    $url=ROOT_PART.'watch.php%3Fv%3D'.$id;
    $title=str_replace('&','||',$title);
    $title=str_replace('"',' ',$title);
     $title=str_replace("'",' ',$title);
    $des='【'.$lang['LIB_FWYI'].'】'.$lang['LIB_SHAREIT_M1'].'《'.$title.'》'.$lang['LIB_SHAREIT_M2'];
    return "<div id='share'>
  <a class='icoqz' href='https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=".$url."&desc=".$des."&title=".$titlel."
&pics=".$pic."' target='blank' title='".$lang['LIB_SHAREIT_T']."'><i class='iconfont icon-qqkongjian icofontsize'></i></a>

  <a class='icotb' href='http://tieba.baidu.com/f/commit/share/openShareApi?title=".$title."&url=".$url."&to=tieba&type=text&relateUid=&pic=".$pic."&key=&sign=on&desc=&comment=".$title."' target='blank' title='".$lang['LIB_SHAREIT_S1']."'><i class='iconfont icon-40 icofontsize'></i></a>
  <a class='icowb' href='http://service.weibo.com/share/share.php?url=".$url."&title=".$des."&pic=".$pic."&sudaref=".$title."' target='blank' title='".$lang['LIB_SHAREIT_S2']."'><i class='iconfont icon-weibo icofontsize'></i></a>
  <a class='icobi' href='https://member.bilibili.com/v/#/text-edit' target='blank' title='".$lang['LIB_SHAREIT_S3']."'><i class='iconfont icon-bilibili icofontsize'></i></a>
  <a class='icowx' href='http://api.addthis.com/oexchange/0.8/forward/wechat/offer?url=".ROOT_PART."watch.php?v=".$id."' target='blank' title='".$lang['LIB_SHAREIT_S4']."' ><i class='iconfont icon-weixin icofontsize'></i></a>
</div>
 <div class='form-group'><div class='d-inline-block h6 pt-3 col-12'>
    ".$lang['LIB_SHAREIT_SC']."
 </div>
    <textarea style='resize:none;height: auto' class='form-control d-inline align-middle col-12 icoys icontext' id='inputs' type='text' rows='5' placeholder='Default input'><iframe height=498 width=510 src=&quot;".ROOT_PART."embed/?v=".$id."&quot; frameborder=0 &quot;allowfullscreen&quot;></iframe></textarea>

    <button type='submit' class='btn btn-primary align-middle col-12 mt-2' onclick='copytext1()'>".$lang['LIB_SHAREIT_SCOPY']."</button></div>";

}
//
function html5_player($id){
    $yt = new YouTubeDownloader();
    $links = $yt->getDownloadLinks('https://www.youtube.com/watch?v='.$id);
    if(count($links)!=1){
        echo'<video id="h5player"  class="video-js vjs-fluid mh-100 mw-100" loop="loop" width="100%" preload="auto"  webkit-playsinline="true" playsinline="true" x-webkit-airplay="true" controls="controls" controls preload="auto" width="100%" poster="./thumbnail.php?type=maxresdefault&vid='.$id.'" data-setup=\'\'>';

        //获取视频分辨率
        /*if(array_key_exists('22',$links)){
        echo '<source src="./ytdl/getvideo.mp4?videoid='.$id.'&quality=720" type=\'video/mp4\' res="720" label=\'720P\'/>';
            };*/
        echo '<source src="./ytdl/getvideo.mp4?videoid='.$id.'&format=ipad" type=\'video/mp4\' res="360" label=\'360P\'/>';


    //提取字幕
     $slink='https://www.youtube.com/api/timedtext?type=list&v='.$id;
     $vdata=get_data($slink);
     @$xml = simplexml_load_string($vdata);
     $array1=json_decode(json_encode($xml), true);
     $arr=array();
     //分离出几种常用字幕
     if(array_key_exists('track',$array1) && array_key_exists('0',$array1['track'])){
         if (array_key_exists('track', $array1) && array_key_exists('0', $array1['track'
    									   ])) {
    	foreach ($array1['track'] as $val) {if ($val['@attributes']['lang_code'] == 'en' || $val['@attributes']['lang_code'] == 'zh' || $val['@attributes']['lang_code'] ==$lang['YT_LANG'] || $val['@attributes']['lang_code'] =='zh-TW' || $val['@attributes']['lang_code'] =='zh-HK') {
    			$arr[$val['@attributes']['lang_code']] = "
    <track kind='captions' src='./tracks.php?vtt={$id}&lang=" . $val['@attributes']
    ['lang_code'] . "' srclang='" . $val['@attributes']['lang_code'] . "' label='" .
    				   $val['@attributes']['lang_original'] . "'/>";
    		}
    	}
    	foreach ($arr as $k => $v) {
    	    switch ($k) {
    		    case $lang['YT_LANG']:
    		        $arr[$k] = substr_replace($v, ' default ', -2,0);
    				break;
    			case 'zh':
    		        $arr[$k] = substr_replace($v, ' default ', -2,0);
    				break;
    			case 'zh-HK':
    		        $arr[$k] = substr_replace($v, ' default ', -2,0);
    				break;
    			case 'zh-TW':
    				$arr[$k] = substr_replace($v, ' default ', -2,0);
    				break;
    			case 'en':
    				$arr[$k] = substr_replace($v, ' default ', -2,0);
    				break;
    		}
    		break;
    	}
    	foreach($arr as $vl ){
          echo $vl.PHP_EOL;
      }
    }
     }elseif(array_key_exists('track',$array1)){
     echo "<track kind='captions' src='./tracks.php?vtt={$id}&lang=".$array1['track']['@attributes']['lang_code']."' srclang='".$array1['code']."' label='".$array1['track']['@attributes']['lang_original']."' default />";
     }

    echo '</video>';
    }else{
        echo '<img src="./inc/2.svg" class="w-100" onerror="this.onerror=null; this.src="./inc/2.gif"">';
        }
}
//获取安装目录
function Root_part(){
$http=isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$part=rtrim($_SERVER['SCRIPT_NAME'],basename($_SERVER['SCRIPT_NAME']));
$domain=$_SERVER['SERVER_NAME'];
 return "$http"."$domain"."$part";
}
?>
