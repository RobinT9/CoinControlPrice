<?php
	$tokenId = $_COOKIE['tokenId'];
	
	if(empty($tokenId)){
	    $msg = [
                'code'	=>	50,
                'msg'	=>	'data must be numeric',
                'data'	=>	''
            ];
            echo json_encode($msg);exit;
	}
	$data['startPrice'] 	= $_POST['startPrice'];
	$data['endPrice'] 		= $_POST['endPrice'];
	$data['sell_ratio'] 	= $_POST['sell_ratio'];
	$data['buy_ratio'] 		= $_POST['buy_ratio'];
	$data['period'] 		= $_POST['period'];
	$data['webrange'] 		= $_POST['webrange'];
	$coin 		= $_POST['coin'];
	$cookie = $_COOKIE['JSESSIONID'];
	

    //	verify and filter
    foreach ($data as $d){
        strip_tags($d); //get rid of the html tag
        if(!is_numeric($d)){
            $msg = [
                'code'	=>	110,
                'msg'	=>	'data must be numeric',
                'data'	=>	''
            ];
            echo json_encode($msg);exit;
        }
    }


	//final exec
    ignore_user_abort(true); // 后台运行
    set_time_limit(0); // 取消脚本运行时间的超时上限

		
	$res = exec('php recontrol.php '.$data['startPrice']." ".$data['endPrice']." ".$data['sell_ratio']." ".$data['buy_ratio']." ".$data['period']." ".$data['webrange']." ".$cookie." ".$coin);
    $res = json_decode($res,true);

	$msg = [
        'code'	=>	$res['code'],
        'msg'	=>	$res['msg'],
        'data'	=>	''
    ];

    echo json_encode($msg);exit;
//	$msg = [
//		'code'	=>	0,
//		'msg'	=>	'success',
//		'data'	=>	''
//	];
//
//	echo json_encode($msg);