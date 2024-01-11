<?php

header("Content-type:application/json; charset=utf-8");

function ithome()
 {
 	$html = file_get_contents("https://m.ithome.com/rankm/");
	$pattern = '/<p class="plc-title">(.*?)<\/p>.*?<a href="(.*?)"/s';
	preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
	
	$results = [];
	
	foreach ($matches as $index => $match) {
	    $title = trim($match[1]);
	    $url = $match[2];
	
	    // 将标题和链接添加到结果数组
	    $results[] = [
	    'index' => $index+1,
	        'title' => $title,
	        'url' => $url,
	    ];
}
 	return [
      'success' => true,
      'title' => 'IT之家',
      'subtitle' => '热榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $results
    ];
 }

if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
	$_res = ithome();
	$json = json_encode($_res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	$json = str_replace('\/', '/', $json);
	echo $json;
}
?>