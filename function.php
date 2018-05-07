<?php
function randFloat($min=0, $max=5){
  return $min + mt_rand()/mt_getrandmax() * ($max-$min);
}
function getCoinMarketList(){
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_SSL_VERIFYPEER => false,
	  CURLOPT_SSL_VERIFYHOST => false,
	  CURLOPT_CUSTOMREQUEST => "POST",
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  return $err;
	} else {
	  return $response;
	}
}
//type=1买
function coin_curl($coin, $type, $price, $number, $Cookie){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "coinCode=".$coin."&tokenId=&status=0&source=2&type=".$type."&entrustWay=1&entrustSum=&entrustPrice=".$price."&entrustCount=".$number,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded",
            "Cookie: JSESSIONID=$Cookie"
        ),
    ));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return $err;
    } else {
        return $response;
    }
}


