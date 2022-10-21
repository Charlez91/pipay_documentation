<?php
  $curl = curl_init();
  
  curl_setopt_array($curl, array(
    CURLOPT_URL => "http://127.0.0.1:5000//api/v1/payment/pant007",//change it from local host to pipay.pihub.biz. change the pant007 to your memo
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	//CURLOPT_POST => false,//you can use this instead if this to your get request
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
      "Key: 049f693ac217518d34dd342782871f65",
      "Cache-Control: no-cache",
	  "Content-Type: application/json"
    ),
  ));
  
  $pipayresponse = curl_exec($curl);
  $err = curl_error($curl);
  $res = json_decode($pipayresponse, true);
  var_dump($res);
  curl_close($curl);
  
  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    echo $res;
  }
?>
