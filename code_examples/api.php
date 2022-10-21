<?php
  $url = 'https://pipay.pihub.biz/api/v1/payment';// i used dis to test set it to pipay.pihub.biz/api/v1/payment
  // Collection object

  $data = array(
    'amount' => 3.14,
    'memo' => 'shoe321',
    'useremail' => 'test2@demo.com',
  );
  $headers = array(
                   "Content-Type: application/json",
                   'key'=> 'PUT-IN-YOUR-OWN-API-KEY'
                );

  // Initializes a new cURL session
  $curl = curl_init($url);
  // Set the CURLOPT_RETURNTRANSFER option to true
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  // Set the CURLOPT_POST option to true for POST request
  curl_setopt($curl, CURLOPT_POST, true);
  // Set the request data as JSON using json_encode function
  curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));//d code no work cos u no encode am
  // Set custom headers for RapidAPI Auth and Content-Type header
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  // Execute cURL request with all previous settings
  $response = curl_exec($curl);
  // Close cURL session // you can close the session here or you use the if conditional
  //curl_close($curl); //you fit use dis if you no want put d error code conditional of 200 or so
  //echo $response . PHP_EOL;//this displays the response to browser
   $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);// dis gets the status code weda 200, 400 or 500

  // This if statement tries to make sure the status code of the request is 200 if not im go return: failed to connect
  if ($code == 200 && !(curl_errno($curl))) {
      curl_close($curl);
      $pipayresponse = $response;
  } else {
      curl_close($curl);
      echo "FAILED TO CONNECT WITH PIPAY API";
      exit;
  }

  # PARSE THE JSON RESPONSE and redirects to the access url. you can stop line 29 to just display output of the api
  $res = json_decode($pipayresponse, true);
  var_dump($res);
  if (isset($res['accessurl']) && $res['accessurl'] != "") {
      # THERE ARE MANY WAYS TO REDIRECT - Javascript, Meta Tag or Php Header Redirect or Other
      #echo "<script>window.location.href = '". $res['accessurl'] ."';</script>";
      #echo "<meta http-equiv='refresh' content='0;url=" . $res['accessurl'] . "'>";
       header("Location: ". $res['accessurl']);
      exit;
  } else {
      echo "JSON Data parsing error!";
  }

?>