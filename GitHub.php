<?php
header('Content-Type: application/json; charset=utf-8');

 function github(){
$urls = "https://github.com/trending";
	$context = stream_context_create([
	    "http" => [
	        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
	    ],
	]);
	
	$html = file_get_contents($urls, false, $context);
	
	// 获取响应头中的Content-Type信息
	$encoding = mb_detect_encoding($html, 'UTF-8, GBK');
	$html = mb_convert_encoding($html, 'UTF-8', $encoding);
	// 正则表达式
		$pattern = '/<span\s+data-view-component="true"\s+class="text-normal">\s*([^<]+)\s*<\/span>\s*([^<]+)<\/a>\s*<\/h2>\s*<p\sclass="col-9 color-fg-muted my-1 pr-4">\s*([^<]+)\s*<\/p>/';
	preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

	$results = [];

	foreach ($matches as $index => $match) {
		// 删除空格
	    $title = trim(preg_replace('/\s+/', '', $match[1] . $match[2]));
		$des = $match[3];
	    // 将标题和链接添加到结果数组
	    $results[] = [
	    	   'index' => $index+1,
	        'title' => $title,
	        'url' => "https://github.com/" . $title,
	        'desc' => trim($des),
	    ];
}
 	return [
      'success' => true,
      'title' => 'GitHub',
      'subtitle' => '热榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $results
    ];
}

// 检查是否是直接运行的脚本
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
	$_res = github();
	$json = json_encode($_res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	$json = str_replace('\/', '/', $json);
	echo $json;
	
}
?>