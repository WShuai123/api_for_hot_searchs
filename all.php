<?php
header('Content-Type: application/json; charset=utf-8');

function Curl($url, $header = [
    "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
    "Accept-Encoding: gzip, deflate, br",
    "Accept-Language: zh-CN,zh;q=0.9",
    "Connection: keep-alive",
    "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1"
  ], $cookie = null, $refer = 'https://www.baidu.com')
  {
    $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
    $header[] = "CLIENT-IP:" . $ip;
    $header[] = "X-FORWARDED-FOR:" . $ip;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); //设置传输的 url
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //发送 http 报头
    curl_setopt($ch, CURLOPT_COOKIE, $cookie); //设置Cookie
    curl_setopt($ch, CURLOPT_REFERER,  $refer); //设置Referer
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); // 解码压缩文件
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 设置超时限制防止死循环
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
  }

// 百度
function baiduredian()
  {
    $_resHtml = str_replace(["\n", "\r", " "], '', Curl('https://top.baidu.com/board?tab=realtime', null));
    preg_match('/<!--s-data:(.*?)-->/', $_resHtml, $_resHtmlArr);
    $jsonRes = json_decode($_resHtmlArr[1], true);
    //return $jsonRes;
    $tempArr = [];
    foreach ($jsonRes['data']['cards'] as $v) {
      foreach ($v['content'] as $k => $_v) {
        array_push($tempArr, [
          'index' => $k + 1,
          'title' => $_v['word'],
          'desc' => $_v['desc'],
          'pic' => $_v['img'],
          'url' => $_v['url'],
          'hot' => round($_v['hotScore']/10000,1).'万',
          'mobilUrl' => $_v['appUrl']
        ]);
      }
    }
    return [
      'success' => true,
      'title' => '百度热点',
      'subtitle' => '指数',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $tempArr
    ];
  }

// 微博热搜
function weibo() {
    // 执行 cURL 请求
    $jsonRes = json_decode(Curl('https://weibo.com/ajax/side/hotSearch', null, null), true);

    $tempArr = [];
    foreach ($jsonRes['data']['realtime'] as $k => $v) {
        // 因为index=1时，不是热搜的标题，是介绍语，所以从第二个开始索引。
            array_push($tempArr, [
                'index' => $k+1,
                'title' => $v['word'],
                'url' => "https://s.weibo.com/weibo?q=".$v['word'],
            ]);
    }

    return [
        'success' => true,
        'title' => '腾讯新闻',
        'subtitle' => '热点榜',
        'update_time' => date('Y-m-d H:i:s', time()),
        'data' => $tempArr
    ];
}

// 贴吧
function tieba() {
    // 执行 cURL 请求
    $jsonRes = json_decode(Curl('https://tieba.baidu.com/hottopic/browse/topicList', null, null), true);
    
    $tempArr = [];
    foreach ($jsonRes['data']['bang_topic']['topic_list'] as $k => $v) {
            array_push($tempArr, [
                'index' => $k+1,
                'title' => $v['topic_name'],
                'url' => $v['topic_url'],
                'abstract' => $v['topic_desc']
            ]);
    }

        return [
            'success' => true,
            'title' => '百度贴吧',
            'subtitle' => '热议话题',
            'update_time' => date('Y-m-d H:i:s', time()),
            'data' => $tempArr
        ];
    }

// 腾讯新闻
  function qqnews() {
    // 执行 cURL 请求
    $jsonRes = json_decode(Curl('https://r.inews.qq.com/gw/event/hot_ranking_list?page_size=51', null, null), true);

    $tempArr = [];
    foreach ($jsonRes['idlist'][0]['newslist'] as $k => $v) {
        // 因为index=1时，不是热搜的标题，是介绍语，所以从第二个开始索引。
        if ($k >= 1) {
            array_push($tempArr, [
                'index' => $k,
                'title' => $v['title'],
                'url' => $v['url'],
            ]);
        }
    }

    return [
        'success' => true,
        'title' => '腾讯新闻',
        'subtitle' => '热点榜',
        'update_time' => date('Y-m-d H:i:s', time()),
        'data' => $tempArr
    ];
}

// 哔哩哔哩热搜
function bili() {
    // 执行 cURL 请求
    $jsonRes = json_decode(Curl('https://app.bilibili.com/x/v2/search/trending/ranking', null, null, "https://www.bilibili.com"), true);

    $tempArr = [];
    foreach ($jsonRes['data']['list'] as $k => $v) {
      array_push($tempArr, [
        'index' => $v['position'],
        'title' => $v['keyword'],
        'url' => 'https://search.bilibili.com/all?keyword='.$v['keyword'].'&order=click',
        'mobilUrl' => 'https://search.bilibili.com/all?keyword='.$v['keyword'].'&order=click'
      ]);
    }

    return [
        'success' => true,
        'title' => '哔哩哔哩热搜榜',
        'subtitle' => '热搜',
        'update_time' => date('Y-m-d H:i:s', time()),
        'data' => $tempArr
    ];
}

// 哔哩哔哩日榜
function bili_all() {
    // 执行 cURL 请求
    $jsonRes = json_decode(Curl('https://api.bilibili.com/x/web-interface/ranking/v2?rid=0&type=all', null, null, "https://www.bilibili.com"), true);

    $tempArr = [];
    foreach ($jsonRes['data']['list'] as $k => $v) {
      array_push($tempArr, [
        'index' => $k +1,
        'title' => $v['title'],
        'pic' => $v['pic'],
        'desc' => $v['desc'],
        'hot' => round($v['stat']['view']/10000,1).'万',
        'url' => "https://www.bilibili.com/video/". $v['bvid']
      ]);
    }

    return [
        'success' => true,
        'title' => '哔哩哔哩全站日榜',
        'subtitle' => '日榜',
        'update_time' => date('Y-m-d H:i:s', time()),
        'data' => $tempArr
    ];
}

// GitHub
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

// V2EX
function v2ex() {
	$urls = "https://www.v2ex.com/";
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
		$pattern = '/<span class="item_hot_topic_title">\s<a href="(.*?)">(.*?)<\/a>\s<\/span>/';
	preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

	$results = [];

	foreach ($matches as $index => $match) {
		// 删除空格
	    $title = $match[2];
	    $url = $match[1];
	    // 将标题和链接添加到结果数组
	    $results[] = [
	    'index' => $index+1,
	        'title' => $title,
	        'url' => "https://www.v2ex.com" . $url,
	    ];
}
 	return [
      'success' => true,
      'title' => 'V2EX',
      'subtitle' => '今日热议主题',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $results
    ];
}

// 吾爱破解
function wuaipojie() {
$urls = "https://www.52pojie.cn/misc.php?mod=ranklist&type=thread&view=heats&orderby=today";
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
	$pattern = '/<th><a href="(.*?)" target="_blank">(.*?)<\/a><\/th>/';
	preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
	
	$results = [];
	
	foreach ($matches as $index => $match) {
	    $title = trim($match[2]);
	    $url = $match[1];
	
	    // 将标题和链接添加到结果数组
	    $results[] = [
	    'index' => $index+1,
	        'title' => $title,
	        'url' => stripslashes("https://www.52pojie.cn/".$url),
	    ];
}
 	return [
      'success' => true,
      'title' => '吾爱破解',
      'subtitle' => '热榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $results
    ];
}

//知乎
function zhihu() {
    // 执行 cURL 请求
    $jsonRes = json_decode(Curl('https://www.zhihu.com/api/v3/feed/topstory/hot-lists/total?limit=50&desktop=true', null, null), true);

    $tempArr = [];
    foreach ($jsonRes['data'] as $k => $v) {
      preg_match('/\d+/',  $v['detail_text'], $hot);
      array_push($tempArr, [
        'index' => $k + 1,
        'title' => $v['target']['title'],
        'hot' => $hot[0].'万',
        'url' => 'https://www.zhihu.com/question/'.urlencode($v['target']['id']),
        'mobilUrl' => 'https://www.zhihu.com/question/'.urlencode($v['target']['id'])
      ]);
    }

    return [
        'success' => true,
        'title' => '知乎热榜',
        'subtitle' => '热点榜',
        'update_time' => date('Y-m-d H:i:s', time()),
        'data' => $tempArr
    ];
}

//抖音
function douyin()
  {
    $jsonRes = json_decode(Curl('https://www.iesdouyin.com/web/api/v2/hotsearch/billboard/word/', null, null, "https://www.douyin.com"), true);
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

// 历史上的今天
function history()
  {
    $month=date('m',time() );
    $day=date('d',time() );
    //当前年月日
    $today = date('Y年m月d日');
    //获取接口数据
    $jsonRes = json_decode(Curl('https://baike.baidu.com/cms/home/eventsOnHistory/'.$month.'.json', null, null, "https://baike.baidu.com"), true);
    $tempArr = [];
    $countnum = count($jsonRes[$month][$month.$day])-1;
    foreach ($jsonRes[$month][$month.$day] as $k => $v) {
      array_push($tempArr, [
        'index' => $k +1,
        'title' => $v['year'].'年-'.strip_tags($v['title']),
        'url' => 'https://www.douyin.com/search/'.urlencode($v['title']),
        'mobilUrl' => 'https://www.douyin.com/search/'.urlencode($v['title'])
      ]);
    }
    return [
      'success' => true,
      'title' => '历史上的今天',
      'subtitle' => '历史上的今天',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $tempArr
    ];
  }

// CSDN
function csdn() {
    // 执行 cURL 请求
    $jsonRes = json_decode(Curl('https://blog.csdn.net/phoenix/web/blog/hotRank?&pageSize=100', null, null), true);

    $tempArr = [];
    foreach ($jsonRes['data'] as $k => $v) {
      array_push($tempArr, [
        'index' => $k +1,
        'title' => $v['articleTitle'],
        'url' => $v['articleDetailUrl'],
      ]);
    }
    return [
        'success' => true,
        'title' => 'CSDN',
        'subtitle' => '热榜',
        'update_time' => date('Y-m-d H:i:s', time()),
        'data' => $tempArr
    ];
}

// AcFun
function acfun()
  {
    $jsonRes = json_decode(Curl('https://www.acfun.cn/rest/pc-direct/rank/channel?channelId=&subChannelId=&rankLimit=30&rankPeriod=DAY', null, null, "www.acfun.cn"), true);
    $tempArr = [];
    foreach ($jsonRes['rankList'] as $index => $v) {
      array_push($tempArr, [
        'index' => $index +1,
        'title' => $v['contentTitle'],
        'url' => $v['shareUrl'],
      ]);
    }
    return [
      'success' => true,
      'title' => 'ACFun',
      'subtitle' => '热榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $tempArr
    ];
  }

 // 少数派
 function sspai()
  {
    $jsonRes = json_decode(Curl('https://sspai.com/api/v1/article/tag/page/get?limit=100000&tag=%E7%83%AD%E9%97%A8%E6%96%87%E7%AB%A0', null, null, "https://sspai.com"), true);
    $tempArr = [];
    foreach ($jsonRes['data'] as $k => $v) {
      array_push($tempArr, [
        'index' => $k +1,
        'title' => $v['title'],
        'createdAt' => date('Y-m-d', $v['released_time']),
        'other' => $v['author']['nickname'],
        'like_count' => $v['like_count'],
        'comment_count' => $v['comment_count'],
        'url' => 'https://sspai.com/post/'.$v['id'],
        'mobilUrl' => 'https://sspai.com/post/'.$v['id']
      ]);
    }
    return [
      'success' => true,
      'title' => '少数派',
      'subtitle' => '热榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $tempArr
    ];
  }

 // 36氪
 function kr_36()
  {
    $jsonRes = json_decode(Curl('https://api.pearktrue.cn/api/dailyhot/?title=36%E6%B0%AA', null, null, "https://blog.csdn.net"), true);
    $tempArr = [];
    foreach ($jsonRes['data'] as $k => $v) {
      array_push($tempArr, [
        'index' => $k +1,
        'title' => $v['title'],
        'url' => $v['url'],
      ]);
    }
    return [
      'success' => true,
      'title' => '36氪',
      'subtitle' => '热榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $tempArr
    ];
  }

// IT之家
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

 // 懂球帝
 function dongqiudi() {
    // 执行 cURL 请求
    $jsonRes = json_decode(Curl('https://dongqiudi.com/api/v3/archive/pc/index/getIndex', null, null, "https://dongqiudi.com/"), true);
    // 检查 cURL 请求是否成功，并且数据结构是否符合预期
    $tempArr = [];
    foreach ($jsonRes['data']['new_list'] as $k => $v) {
            array_push($tempArr, [
                'index' => $k + 1,
                'title' => $v['title'],
                'url' => $v['url'],
            ]);
        }

        return [
            'success' => true,
            'title' => '懂球帝',
            'subtitle' => '热榜',
            'update_time' => date('Y-m-d H:i:s', time()),
            'data' => $tempArr
        ];
    }
    
 // 安全客
function anquanke() {
    // 执行 cURL 请求
    $jsonRes = json_decode(Curl('https://www.anquanke.com/webapi/api/index/top/list?page=1', null, null,"https://www.anquanke.com/"), true);
    // 检查 cURL 请求是否成功，并且数据结构是否符合预期
    $tempArr = [];
    foreach ($jsonRes['data']['list'] as $k => $v) {
            array_push($tempArr, [
                'index' => $k + 1,
                'title' => $v['title'],
                'url' => "https://www.anquanke.com".$v['url'],
            ]);
        }
    $jsonRes = json_decode(Curl('https://www.anquanke.com/webapi/api/index/top/list?page=2', null, null,"https://www.anquanke.com/"), true);
    foreach ($jsonRes['data']['list'] as $k => $v) {
            array_push($tempArr, [
                'index' => $k + 11,
                'title' => $v['title'],
                'url' => "https://www.anquanke.com".$v['url'],
            ]);
        }

        return [
            'success' => true,
            'title' => '安全客',
            'subtitle' => '热榜',
            'update_time' => date('Y-m-d H:i:s', time()),
            'data' => $tempArr
        ];
        
    }
    
 // 易车网
 function yichewang() {
 $urls = "https://news.yiche.com/";
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
	$pattern = '/<li\sclass="artical-item"><a\shref="([^"]+)".*?>.*?<\/span>(.*?)<\/a>/';
	preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
	
	$results = [];
	
	foreach ($matches as $index => $match) {
	    $title = trim($match[2]);
	    $url =  "https://news.yiche.com" . $match[1];
	
	    // 将标题和链接添加到结果数组
	    $results[] = [
	    'index' => $index+1,
	        'title' => $title,
	        'url' => $url,
	    ];
}
 	return [
      'success' => true,
      'title' => '易车网',
      'subtitle' => '热榜',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $results
    ];
 }

 // 虎扑
 function hupu() {
	 $urls = "https://www.hupu.com/";
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
		$pattern = '/<a\s+href="([^"]+)"[^>]+>\s*<div[^>]+>\s*<div[^>]+>\d+<\/div>\s*<div[^>]+>(.*?)<\/div>/i';
	preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

	$results = [];

	foreach ($matches as $index => $match) {
		// 删除空格
	    $title = $match[2];
	    $url = $match[1];
	    // 将标题和链接添加到结果数组
	    $results[] = [
	    		'index' => $index+1,
	        'title' => $title,
	        'url' => $url,
	    ];
}
 	return [
      'success' => true,
      'title' => '虎扑',
      'subtitle' => '虎扑热榜-篮球-足球-步行街',
      'update_time' => date('Y-m-d H:i:s', time()),
      'data' => $results
    ];
 }
$all = [
        'success' => true,
        'title' => '热搜',
        'author' => [ "GitHub" => "https://github.com/WShuai123",
        			  "Blog" => "https://www.iiecho.com"
        ],
        'update_time' => date('Y-m-d H:i:s', time()),
        'data' => [ "微博热搜" => weibo()['data'],
        			"百度" => baiduredian()['data'],
        			"百度贴吧" => tieba()['data'],
        			"腾讯新闻" => qqnews()['data'],
        			"哔哩哔哩热搜" => bili()['data'],
        			"哔哩哔哩全站日榜" => bili_all()['data'],
        			"GitHub" => github()['data'],
        			"V2EX" => v2ex()['data'],
        			"吾爱破解" => wuaipojie()['data'],
        			"知乎" => zhihu()['data'],
        			"抖音" => douyin()['data'],
        			"历史上的今天" => history()['data'],
        			"CSDN" => csdn()['data'],
        			"ACFun" => acfun()['data'],
        			"少数派" => sspai()['data'],
        			"36氪" => kr_36()['data'],
        			"IT之家" => ithome()['data'],
        			"懂球帝" => dongqiudi()['data'],
        			"安全客" => anquanke()['data'],
        			"易车网" => yichewang()['data'],
        			"虎扑" => hupu()['data'],
        ]
    ];

$mergedJson = json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo $mergedJson;

?>