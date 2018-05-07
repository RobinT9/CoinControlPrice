<?php
	require 'function.php';

	$startPrice = $argv[1];//6;//现在价格
	$endPrice 	= $argv[2];//5;//目标价格
	$sell_ratio = $argv[3];//10;//每次卖涨幅比例
	$buy_ratio 	= $argv[4];//5;//每次卖涨幅比例
	$period 	= $argv[5];//5;//时间间隔
	$webrange   = $argv[6];//20;//买/卖网格数量
    $Cookie     = $argv[7];//$_COOKIE['JSESSIONID'];
	$coin       = $argv[8];

	$filename = $startPrice.$endPrice.$sell_ratio.$buy_ratio.$period.$webrange.$Cookie.$coin.".log";
    $pid = getmypid();
    $myfile = fopen($filename, "w");
    fwrite($myfile, $pid);
		
	$curlcoin_withCookie = function ($coin, $type, $price, $number) use($Cookie){
        return coin_curl($coin, $type, $price, $number, $Cookie);
    };


	$sell_ratio = $sell_ratio/100;
	$buy_ratio  = $buy_ratio/100;

	if($startPrice>$endPrice){
		//降价，确定20单往后最后得买单价
		for ($i=1; $i <=$webrange ; $i++) { 
			# code...
			if($endPrice - $endPrice*$buy_ratio*$i<=0){
				break;
			}
			$destination_price = $endPrice - $endPrice*$buy_ratio*$i;
		}
		//initialize buy_price and sell price
		$sell_price = $startPrice-$endPrice*$sell_ratio;
		$buy_price = $startPrice-$endPrice*$buy_ratio;
		//echo "======[Decline] , DesPrice:".$destination_price."======\n";
	}else{
		//上涨，确定20单往后最后得卖单价
		$destination_price = $endPrice + $endPrice*$sell_ratio*$webrange;
		$sell_price = $startPrice+$endPrice*$sell_ratio;
		$buy_price = $startPrice+$endPrice*$buy_ratio;
		//echo "======[Go up] , DesPrice:".$destination_price."======\n";
	}



	if($startPrice>$endPrice){
		//降价
        $oppsite_webrange = false;
        $oppsite_webrange_destination_price = $endPrice+$endPrice*$sell_ratio*$webrange;
        $i=1;
		while ($buy_price>$destination_price||$sell_price>=$endPrice||$oppsite_webrange==false) {
			# code...
            $num = round(randFloat(),4);//挂单数量，随机数
            //当sellprice达到endprice时填对立单
            if($sell_price<$endPrice){
                if($i<=$webrange){
                    $price = $endPrice+$endPrice*$sell_ratio*$i;
                    coin_curl($coin,2,$price,$num,$Cookie);
                    $i++;
                }else{
                    $oppsite_webrange = true;
                }
            }
			//先挂买，再挂卖
			if($buy_price<$endPrice){
				$res = coin_curl($coin,1,$buy_price,$num,$Cookie);
				if(!$res){
					//echo "[Error] curlcoin_withCookie error";
					$msg = [
						'code'	=>	404,
						'msg'	=>	'接口错误，请稍后再试',
						'data'	=>	''
					];
					echo json_encode($msg);
					exit;
				}
				$res = json_decode($res,true);
				if($res['success']==false){
					$msg = [
						'code'	=>	500,
						'msg'	=>	$res['msg'],
						'data'	=>	''
					];
					echo json_encode($msg);
					exit;
				}
				//echo "[Buy] price:".$buy_price." num:".$num."\n";
			}else{
                if($buy_price>$destination_price){
                    $res = coin_curl($coin,1,$buy_price,$num*0.5,$Cookie);
                    if(!$res){
                        //echo "[Error] curlcoin_withCookie error";
                        $msg = [
                            'code'	=>	404,
                            'msg'	=>	'接口错误，请稍后再试',
                            'data'	=>	''
                        ];
                        echo json_encode($msg);
                        exit;
                    }
                    $res = json_decode($res,true);
                    if($res['success']==false){
                        $msg = [
                            'code'	=>	500,
                            'msg'	=>	$res['msg'],
                            'data'	=>	''
                        ];
                        echo json_encode($msg);
                        exit;
                    }
                    //echo "[Buy] price:".$buy_price." num:".$num*0.5."\n";
                }
			}
			$buy_price = $buy_price-$endPrice*$buy_ratio;

			//卖单价格>=目标价格时，进行挂卖单
			if($sell_price>=$endPrice){
				$res = coin_curl($coin,2,$sell_price,$num,$Cookie);
                if(!$res){
                    //echo "[Error] curlcoin_withCookie error";
                    $msg = [
                        'code'	=>	404,
                        'msg'	=>	'接口错误，请稍后再试',
                        'data'	=>	''
                    ];
                    echo json_encode($msg);
                    exit;
                }
                $res = json_decode($res,true);
                if($res['success']==false){
                    $msg = [
                        'code'	=>	500,
                        'msg'	=>	$res['msg'],
                        'data'	=>	''
                    ];
                    echo json_encode($msg);
                    exit;
                }
				//echo "price:".$sell_price." num:".$num." [Sell] \n";
			}

			//获取最新的最高的买单价格，如果比下一次要迭代的卖单价格大，则不递减
			$List = getCoinMarketList();
			if(!$List){
				sleep($period);
				continue;
			}
			$List = json_decode($List,true);
			// var_dump($List['marketDetail'][$coin][0]["payload"]);die;
			if(isset($List['marketDetail'][$coin][0]["payload"]['bids']['price'][0])){
				$latest_high_buy_price = $List['marketDetail'][$coin][0]['payload']['bids']['price'][0];
				// $latest_high_buy_price = 17.9;
				if($latest_high_buy_price<$sell_price){
					$sell_price = $sell_price - $endPrice*$sell_ratio;
				}
			}else{
				$sell_price = $sell_price - $endPrice*$sell_ratio;
			}
			
			sleep($period);

		}

	}else{
        $oppsite_webrange = false;
        $oppsite_webrange_destination_price = $endPrice-$endPrice*$buy_ratio*$webrange;
        $i=1;
		//涨价
		while($sell_price<$destination_price||$buy_price<=$endPrice||$oppsite_webrange==false){
			$num = round(randFloat(),4);//挂单数量，随机数
            //当buyprice达到endprice时填对立单
            if($buy_price>$endPrice){
                if($i<=$webrange){
                    $price = $endPrice-$endPrice*$buy_ratio*$i;
                    coin_curl($coin,1,$price,$num,$Cookie);
                    $i++;
                }else{
                    $oppsite_webrange = true;
                }
            }
			//先挂卖，再挂买
			if($sell_price<$endPrice){
				$res = coin_curl($coin,2,$sell_price,$num*0.5,$Cookie);
                if(!$res){
                    //echo "[Error] curlcoin_withCookie error";
                    $msg = [
                        'code'	=>	404,
                        'msg'	=>	'接口错误，请稍后再试',
                        'data'	=>	''
                    ];
                    echo json_encode($msg);
                    exit;
                }
                $res = json_decode($res,true);
                if($res['success']==false){
                    $msg = [
                        'code'	=>	500,
                        'msg'	=>	$res['msg'],
                        'data'	=>	''
                    ];
                    echo json_encode($msg);
                    exit;
                }
				//echo "price:".$sell_price." num:".$num." [Sell] \n";
			}else{
                if($sell_price<$destination_price){
                    $res = coin_curl($coin,2,$sell_price,$num,$Cookie);
                    if(!$res){
                        //echo "[Error] curlcoin_withCookie error";
                        $msg = [
                            'code'	=>	404,
                            'msg'	=>	'接口错误，请稍后再试',
                            'data'	=>	''
                        ];
                        echo json_encode($msg);
                        exit;
                    }
                    $res = json_decode($res,true);
                    if($res['success']==false){
                        $msg = [
                            'code'	=>	500,
                            'msg'	=>	$res['msg'],
                            'data'	=>	''
                        ];
                        echo json_encode($msg);
                        exit;
                    }
                    //echo "price:".$sell_price." num:".$num." [Sell] \n";
                }
			}
			$sell_price = $sell_price + $endPrice*$sell_ratio;
			// echo "==========>".$sell_price."\n";

			if($buy_price<=$endPrice){
				$res = coin_curl($coin,1,$buy_price,$num,$Cookie);
                if(!$res){
                    //echo "[Error] curlcoin_withCookie error";
                    $msg = [
                        'code'	=>	404,
                        'msg'	=>	'接口错误，请稍后再试',
                        'data'	=>	''
                    ];
                    echo json_encode($msg);
                    exit;
                }
                $res = json_decode($res,true);
                if($res['success']==false){
                    $msg = [
                        'code'	=>	500,
                        'msg'	=>	$res['msg'],
                        'data'	=>	''
                    ];
                    echo json_encode($msg);
                    exit;
                }
				//echo "[Buy] price:".$buy_price." num:".$num."\n";
			}

			//获取最新的最低的卖单价格，如果比下一次要迭代的买单价格小，则不递增
			$List = getCoinMarketList();
			
			if(!$List){
				sleep($period);
				continue;
			}
			$List = json_decode($List,true);
			
			
			if(isset($List['marketDetail'][$coin][0]['payload']['asks']['price'][0])){
				$latest_low_sell_price = $List['marketDetail'][$coin][0]['payload']['asks']['price'][0];
				// $latest_low_sell_price = 7.1;
				if($latest_low_sell_price>$buy_price){
					$buy_price = $buy_price + $endPrice*$buy_ratio;
				}
			}else{
				$buy_price = $buy_price + $endPrice*$buy_ratio;
			}
			
			sleep($period);
		}



	}

    unlink($filename);