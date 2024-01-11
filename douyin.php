<?php
header('Content-Type: application/json; charset=utf-8');

function curl_douyin($url, $header = [
    "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
    "Accept-Encoding: gzip, deflate, br",
    "Accept-Language: zh-CN,zh;q=0.9",
    "Connection: keep-alive",
    "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1"
], $cookie = null, $refer = 'https://www.baidu.com') {
    $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
    $header[] = "CLIENT-IP:" . $ip;
    $header[] = "X-FORWARDED-FOR:" . $ip;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_REFERER,  $refer);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function douyin()
  {
    $jsonRes = json_decode(curl_douyin('https://www.iesdouyin.com/web/api/v2/hotsearch/billboard/word/', null, null, "https://www.douyin.com"), true);
    $tempArr = [];
    foreach ($jsonRes['word_list'] as $k => $v) {
      array_push($tempArr, [
        'index' => $k +1,
        'title' => $v['word'],
        'hot' => round($v['hot_value']/10000,1).'万',
        'url' => 'https://www.douyin.com/search/'.urlencode($v['word']),
        'mobilUrl' => 'https://www.douyin.com/search/'.urlencode($v['word'])
      ]);
    }
    return [
      'success' => true,
      'title' => '抖音',
      'subtitle' => '热搜榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $tempArr
    ];
  }

if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
	$_res = douyin();
	$json = json_encode($_res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	$json = str_replace('\/', '/', $json);
	echo $json;
}

  ?>