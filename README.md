# PIPAY API Documentation

The documentation for PiPayPro Api used for integration of Pi and other crypto payment options for websites and webapp various business. The Api is RESTful and can be used to initiate payments, verify payments by transaction hash(txid) and memo, check transactions that have occured on account and some extra features.



## Table of Contents

- [PiPay API](#pipaypro-api-documentation)
  - [Table of Contents](#table-of-contents)
  - [Initiate Payment](#initiate-payment)
    - [Using Forms](#using-forms)
    - [API](#api)
    - [From Dashboard](#dashboard)
      - [Payment Links/request](#payment-linksrequest)
      - [POS](#pospoint-of-sale)
  - [Verify Payments](#verify-payments)
    - [Using API](#using-api)
      - [memo](#memo)
      - [txid](#txid)
    - [Using Webhooks](#using-webhooks)
  - [Get Transactions](#get-transaction)
  - [Get Payouts](#get-payout)
  - [Summary](#summary)

## Initiate Payment

In Pipay payment transactions can be initated in several ways. This is to allow flexibility for the users to decide on the method they find most convenient. Forms can be used to initiate a payment transactions from the frontend directly of a website or API calls from the backend of your servers can be used to initate payments. Also for those who dont have websites payments can be initiated from your dashboard on [PiPay](https://pipay.pihub.biz/dashboard). Detailed explanations for the following mentioned methods are written below with corresponding examples.

- ### Using Forms

Normally forms are html elements embedded in the frontend of a website and can be used to submit details or values passed in the form fields to the backend. We can initate payment from frontend forms by having input fields that contain your **public-key** (can be found on your pipay dashboard->settings), **amount** in USD that the user is to pay(this can be fed from your backend or parsed in as a default value), **email** of the user, **memo**(same as your reference) which is a unique identified for the payment txn. This four things are required before a transaction can be processed. For a transaction to be sent it has to have its **action** value set to **'pipay.pihub.biz/payment/forms'** while **method** is set to **'post'**. Below is table of required and non-required elements which can be a parsed into your form.

| Option | Required | Type  | Description | Value |
| --- | --- | --- |--- | ---|
| **cmd** | Yes | String | set to **pay** to initiate payment txn| 'pay'
| **public-key** | Yes | String | Public-key from [PiPay](pipay.pihub.biz) dashboard->settings| 'ur public-key' |
| **amount** | Yes | float | amount in USD(if currency is not entered) you want the customer to pay the crypto equivalent | e.g: 100.50, 50.2 etc |
| **email** | Yes | String | email of your customer/user used to notify them on each txn | e.g'customermail@user.com' |
| **memo** | Yes | String | a unique ID/reference for the transaction parsed in by you | 'ur txn ref' |
| **callback-url** | No | String | a url your customer will be directed to after payment has been made. Default is what is in your accounts profile in the dashboard. | 'url.com/callback' |
| **currency** | No | String | it means the `amount` you specified is in the said currency(Pi) and not in default usd | 'Pi'|
| **pivalue** | No | float | it means the price you want set as your value for 1 Pi | 100, 314 etc|
| **action** | Yes | String | set to **pipay.pihub.biz/payment/forms** |inside the opening form tag.| 'pipay.pihub.biz/payment/forms'|
| **method** | Yes | String | set to **post** inside the opening form tag.| 'post'|

Below is an example of initiating payment request using html-forms from the frontend of your website:

```html
<!-- Dom Element -->
<form action="https://pipay.pihub.biz/payment/forms" method="post" target="_top">
	<input type="hidden" id="cmd" name="cmd" value="pay">
	<input type="hidden" id="public-key" name="public-key" value="your-public-key">
	<input type="hidden" id="amount" name="amount" value="10.00">
	<input type="email" id="email" name="email" value="ur-customer@demo.com">
	<input type="hidden" id="memo" name="memo" value="your txn ref">
	<input type="hidden" id="callback-url" name="callback-url" value="https://ur-url.com/callback">
  <input type="hidden" id="currency" name="currency" value="Pi">
	<div class="form-submit">
		<button type="submit"> Pay </button>
	</div>
</form>
```
**N/B:** 
* Due to some concerns like CSRF etc it is considered not really safe to use forms to initiate transactions of especially huge amounts or of very sensitive nature. The PiPay API(from a backend) is recommended instead as standard practice. We are developing our inline JS module that will make this method safer. 
* Also the email of the customer parsed in has to be a valid email to enable them receive notifications on the transactions.
* If currency is not entered/parsed in the body/input of `form` request, the default is "USD" (when `currency` is not specified the `amount` entered is assumed to be in USD and the customer will be required to pay the crypto(Pi) equivalent of that USD `amount`. But if `currency` is specified the `amount` you entered in API request is assumed to be in Crypto(Pi). The user is now required to pay the said `amount` in crypto(Pi)).
* `24hrs` after a transaction has been initiated, if payment has not been made it will be marked `Expired` and you the user will have to reinitiate another transaction via another `form` POST call.
* Please and please the value of Pi if set in the `pivalue` input field is assumed to be your own platform/websites consensus value for the transaction as the value for Pi has not been generally agreed on yet. If this field is not parsed we will use PiHub PiPay's consensus value to effect the transaction.
* make sure to [Verify-Payments](#verify-payments) after-on to confirm status.

## API

PiPay's Api can be used to initiate transactions. A Post request is made to the API from server/backend with the **memo**(the transaction refernce), **amount**(amount to be paid by the customer in USD for which you need a Pi equivalent of it), **useremail**(email of the customer) etc. parsed into the body of the API request while the **key**(the key is your unique which we use to know that its you that initiated the request) is passed in at the header of the API and all the information parsed in both in the header or body  **must** be in `JSON` format. The memo, amount, email of the customer can be gotten from a frontend form which can be filled by the customer or info can be from your database or session/cookie. 
When a `POST` request is made to the PiPay API url @ `https://pipay.pihub.biz/api/v1/payment` some data is returned which contains an accessurl for the PiPay Payment Gateway to which you will redirect your users to for payment. Bellow are the parameters which can be parsed in

| Option | Required | Type  | Description  | Value/Example |
| --- | --- | --- |--- | ---|
| Header Options | ---| ---| --- | --- |
| **key** | Yes | String | API key gotten from [PiPay](pipay.pihub.biz) dashboard->settings used to identify you| 'key':'ur public-key' |
| **content-type** | No | String | used to indicate the type of content being sent. Only JSON requests are accepted | 'Content-Type': application/json |
| Body Options | --- | --- | --- | --- |
| **amount** | Yes | float | amount in USD you want the customer to pay the crypto(Pi) equivalent if `currency` is not set to "Pi" | e.g: 'amount':100.50, 50.2 etc |
| **useremail** | Yes | String | email of your customer/user used to notify them on each txn. | e.g'email':'customermail@user.com' |
| **memo** | Yes | String | a unique ID/reference for the transaction parsed in by you | 'memo': 'ur txn ref' |
| **callback-url** | No | String | a url your customer will be directed to after payment has been made. Default is what is in your accounts profile settings in the dashboard. | 'callback-url': 'ur-url.com/callback' |
| **currency** | No | String | it means the value of `amount` you specified is in the said currency(Pi) and not in default usd | 'currency':'Pi'|
| **pivalue** | No | float | it means the price you want set as your value for 1 Pi | 'pivalue': 100, 314 etc|
| Other info | --- | --- | --- | --- |
| **method** | Yes | N/A | The request type must a **POST** request| `POST`|
| **api-url** | Yes | String | set to the PiPay API url | `https://pipay.pihub.biz/api/v1/payment` |

Below is a sample request using Bash curl: 

```Bash
curl https://pipay.pihub.biz/api/v1/payment
-H "key: YOUR_SECRET_KEY"
-H "Content-Type: application/json"
-d '{ "useremail": "demo@demo3.com", "amount": 100, "memo": "Dog007", "currency":"Pi" }'
-X POST
```

Below is a sample response:

```JSON
{
  "amount": 98, 
  "memo":"Dog007", 
  "acccess_code": "16ea13e4936baeaa",
  "amounttotal":100,
  "accessurl":"pipay.pihub.biz/gateway/16ea13e4936baeaa",
  "usdvalue": 10000,
  "price": 100,
  "callback-url":"https://mysite.com/payment",
  "message": "Details recieved"
}
```
**N/B**: Assuming $100 for one Pi. 

Also a sample implementations using PHP incoporating both the API POST call and the redirection is shown below:

```PHP
<?php

      $url = 'https://pipay.pihub.biz/api/v1/payment';// i used dis to test set it to pipay.pihub.biz/api/v1/payment
      

      $vendorEmail = 'tests2@demo.com';

      $amount = 100; 

      date_default_timezone_set("Africa/Lagos");

      $d = strtotime(date('Y-m-d H:i:s'));

      $memo = "PL".$d;

      $data = array(
        'amount' => $amount,
        'memo' => $memo,
        'useremail' => $vendorEmail,
      );
      $headers = array(
                       "content-type: application/json",
                       'key'=> 'YOUR-API-KEY'
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
           echo "<script>window.location.href = 'http://". $res['accessurl'] ."';</script>";//"<script>window.location.href = '". $res['accessurl'] ."';</script>";
          #echo "<meta http-equiv='refresh' content='0;url=" . $res['accessurl'] . "'>";
          # header("Location: ". $res['accessurl']);
          exit;
      } else {
          echo "JSON Data parsing error!";
      }
  
?>
```

`N/B`: 
* The request must be in `JSON` format.
* The request must be a `POST` request.
* The `memo` must be unique in the sense that it has to be different from all other memos. Your server can use random number sequence or timestamp with some prefixes to get a unique string/identifier
* API calls must be made from your server or backend of your app and **never** the frontend.
* You API `key` must be kept secret at all times and treated as such by treating/keeping it as your environment variables and reading it from there.
* `24hrs` after a transaction has been initiated, if payment has not been made it will be cancelled and marked as `Expired` and you the user will have to reinitiate another transaction via another API call.
* If `currency` is not entered/parsed in the body of `POST` request, the default is "USD" (i.e: when `currency` is not specified the `amount` entered is assumed to be in USD and the customer will be required to pay the crypto(Pi) equivalent of that USD `amount`. But if `currency` is specified the `amount` you entered in API request is assumed to be in Crypto(Pi). The user is now required to pay the said `amount` in crypto(Pi)).
* `fees` = `amounttotal` - `amount`(i.e PiPay charges a 2% service charge/fee on all transactions initiated and completed using the PiPay API. So, `amounttotal` is what the customer pays(in Pi) while `amount` is what you get(in Pi) after fees have been deducted)
* Please and please the value of Pi if set in the `pivalue` field is assumed to be your own platform/website's `Pi` consensus value for the transaction as the value for Pi has not been generally agreed on yet. If this field is not parsed we will use PiHub-PiPay's consensus value to effect the transaction.
* make sure to [Verify-Payments](#verify-payments) after-on.



## Dashboard

Payment requests can initialized from the dashboard of [PiPay](https://pipay.pihub.biz/dAashboard). Under POS or payment links/requests menu you can generate unique urls(payment links/requests) or generate a POS barcode which can be scanned by the customer and used to pay you. This is brought about for those who do not have a website or yet to integrate the API

- ### Payment Links/request

This menu(dashboard->payment-request/links) is used to generate a unique url which you can send to your customer via social media, emails etc with which they can pay you for your goods/service. 
The page contains any completed or pending payment while at the top right corner you will see generate payment link. A form will be displayed where you will be required to fill out the details of your user/customer. Details asked are the **memo**(memo is your own identifier or reference with which you can use to identify that particular transaction and it must be unique.if you cant put a unique refernce you can leave it blank the system will generate one for you), **amount**(amount in usd to be paid for by customer with the equivalent in crypto;in this case Pi), **email**(email of the customer) and any other information. On submission of the form, a unique url will be generated which you can share with your customers to pay you.

- ### POS(Point of sale)
 This can be used in the checkout parts of your shop(physical shop) where there is a customer facing monitor/display and you dont have the PiPay API integrated into the your website. Its quite similar to the payment link menu the difference is that it generates a barcode in a new tab or window which your customer scans with the customer facing monitor to pay for the product or service. It can aslo be also be done on your mobile phones/tablets for the customer to scan the barcode to pay you.
 The POS menu/page is found in [PiPay](https://pipay.pihub.biz/dashboard) Dashboard->POS. It shows a table which contains list of all recent POS transactions while the top right corner has a button generate POS barcode. When clicked a form is displayed where you required to fill in the details of the customer and the transactions.Fields in the form include: **memo**(a unique identifier for each transaction which you can leave blank if you cant put in a unique field), **amount**(usd value of the amount you want the customer to pay the crypto equivalent), **email**(the email of the customer for notification and records), **coin**(this the crypto you want transferred to you) and other optional information. On submission of the form a POS transaction will be generated with a corresponding redirection to the new tab where the barcode will be generated and the user scans and completes payment.

 ## Verify Payments

 After payment has been made by a customer, they will be redirected to the Callback-url set on the dashboard or the one parsed in when you initiated the transaction. The `memo` and `message` status of the transaction will be parsed in as query string parameters at the end of the callback-url after a payment has been concluded and the customer will be redirected to the said url. The said callback-url redirection is in this format for a successful payment as shown below:
 ```Bash
  ur-callback-url = 'https://your-callback.com/payed'

  query-string = `memo=${memo}&message=success`

  url-user-will-be-redirected-to = `https://your-callback.com/payed?memo=${memo}&message=success`
 ```
 **N/B**: When a callback-url is not provided both in the API/Form requests or in the PiPay dashboard->settings. The user will not be redirected after payment has been made.

 The ideal thing to do is to verify that the transactions was actually successful or failed. And one way to do it is by retrieving the **`memo`*** from the callback-URL parameters and calling the PiPay API GET endpoint with the **`txid`**,**`memo`** or **`accesscode`** as Path Parameters from the backend of your callback-url or by using webhooks and receive `POST` requests to your webhook-url. Depending on status you can decide whether to release the goods/item(s) or perform the service. Explanation for the various ways to verify payments are written below:
 
 - ### Using API
 Payment transaction status and details can be verified with the PiPay API by throwing a **`GET`** request to it. The **GET** request as known doesnt contain any data or body field instead some Parameters are parsed into the path. The Parameters parsed in could be the `memo`, `txid` or `accesscode`.
  The base url is `https://pipay.pihub.biz/api/v1/payment` while the path parameter can be parsed in at the end of the url like dis **`https://pipay.pihub.biz/api/v1/payment/:memo`** or **`https://pipay.pihub.biz/api/v1/payment/${txid}`**. 
  Each time a `GET` request is made to this Url, data containing paid status, accesscode, amount paid, txid, link to transaction on the blockchain, payment-id, time of completion etc is returned. Below is a breakdown of parameter expected from the API user and their various locations:

  | Option | Required | Type  | Description | Value |
| --- | --- | --- |--- | ---|
| Header Options | ---| ---| --- | --- |
| **key** | Yes | String | API key gotten from [PiPay](pipay.pihub.biz) dashboard->settings used to identify you| 'key':'ur public-key' |
| **content-type** | No | String | used to indicate the type of content being sent. Only JSON requests are accepted | 'Content-Type': application/json |
| Path Options | --- | --- | --- | --- |
| **memo** | Yes | String | a unique ID/reference for the transaction parsed in by you | 'ur txn ref' |
| **txid** | Yes | String | a unique ID/reference for the transaction after payment. Available on dashboard | 'txn id' |
| **accesscode** | Yes | String | a unique ID for the transaction generated when a payment request is initiated. Its appended at the end of the accessurl | 'accesscode' |
| Other info | --- | --- | --- | --- |
| **method** | Yes | N/A | The request type must a **POST** request| `POST`|
| **api-url** | Yes | String | set to the PiPay API url | `https://pipay.pihub.biz/api/v1/payment` |

**N/B**: Please if **`memo`** is used then **`txid`**  and `**accesscode**` should not be parsed in and vice-versa. Only one parameter is allowed as a path parameter to API verify endpoint per call.

  
Below is a code example of the stated get request in Bash Curl for **`memo`**:

```Bash
curl https://pipay.pihub.biz/api/v1/payment/${memo}
-H "key: YOUR_SECRET_KEY"
-H "Content-Type: application/json"
-X GET
```

Below is a code example of the stated get request in Bash Curl for **`txid`**:

```Bash
curl https://pipay.pihub.biz/api/v1/payment/txid:${txid}
-H "key: YOUR_SECRET_KEY"
-H "Content-Type: application/json"
-X GET
```

Below is a code example of the stated get request in Bash cURL for **`accesscode`**:

```Bash
curl https://pipay.pihub.biz/api/v1/payment/accesscode:${accesscode}
-H "key: YOUR_SECRET_KEY"
-H "Content-Type: application/json"
-X GET
```


A Sample response is shown below:

```JSON
{
"amount": 98, 
"memo":"Dog007", 
"amounttotal":100, 
"meta_data": {"memo":"Dog007"} , 
"txn_date": "2021-12-08 10:30:54", 
"user_email": "demo2@demo.com", 
"access_code": "5b5cdc21478d69bc", 
"paid": true,
"notified": false,
"price": 100.0,
"status": "Pending",
"init_date": "Thu, 20 Oct 2022 13:40:45 GMT",
"paymentid": "29kkF7glQINCJRAGIz2CgXyXG5uU", 
"txn_id":"85352c960c0bedb1aa0e25ddb3f5803e326922905b83fb2397aefba5e1d7c83d", 
"block_link": "https://api.testnet.minepi.com/transactions/d1a1debaf8a5d0ebec4f3c061e9f57509f6d38a022a77a267192b5b37d94e2ba",
"message": "Transaction details retrieved"
}
```
**N/B**: 
* The same response is sent not minding whether it was **`memo`**, **`txid`** or **`accesscode`** that was used as path parameter to throw a  `GET` request.
* This API `GET` request must be made from the backend of your server and **NEVER** the frontend.
* Sometimes responses from the PiPay-API on transaction status maynot be immediate due to network issues or delays in transactions processing etc. You have to keep **Polling** the PiPay API. Polling simply means making repeated calls to the PiPay-API at regular intervals to the API verify endpoint(as shown) until the transactions status data is returned and confirmed or to avoid the stress you can use [`webhooks`](#using-webhooks).

Below is a sample implementation in PHP

```PHP
<?php
  $curl = curl_init();
  
  $memo = "dog007";
  curl_setopt_array($curl, array(
    CURLOPT_URL => `http://pipay.pihub.biz/api/v1/payment/$memo`,//change it from local host to pipay.pihub.biz. change the pant007 to your memo
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
      "Key: YOUR-PRIVATE-KEY",
      "Cache-Control: no-cache",
      "content-type: application/json"
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
```


### Using Webhooks

Webhooks are endpoints on your Server which recieve RESTful **POST** request notification from the *PiPay API*. When a payment has been completed, a **POST** request notification is sent to your set **`webhook-url`** and the transaction status is parsed into the Body or Data field(as an *`event`*) of the **POST** request while your `secret-key` is parsed into the header of the `POST` request that will be sent to your on server/url.
The secret-key has to be set in [PiPay](https://pipay.pihub.biz) dashboard->setting to enable your server recognize that the transaction status notification was actually sent by PiHub PiPay to avoid phishing and malicious calls by untrusted servers. Additionally the secret-key you set on your dashboard is used to hash the body of the sent request using the HMAC Sha512 algorithm.
One advantage of webhooks is that the PiPay server(resource server)  sends updates on your transaction status to your webhook-url endpoints as `POST` requests. So when the status of your transaction changes you will get a notification and your server needs to return a http-status-code `**200`** in the http-header to confirm reception. If a `200` is not received when a `POST` request bearing the event and transaction details is sent from our server, we will continue sending the request every **`30mins`** interval for the next **`48hrs`** from when the first webhook was sent hoping for `200` status-code. If a response is not received within the stated `48hr` the webhook notification request will be marked as `failed`.
A webhook URL is simply a POST endpoint that a resource server sends updates to. The URL needs to the parse the JSON request(sent by PiPay as a `POST` request) and acknowledge receipt by returning a `200` OKn to the PiPay server(i.e the resource server).

Below are list of events which can be sent by PiPay on transaction status:

| EVENT | DESCRIPTION |
| --- | --- |
| payment.success | the payment was successfully completed by customer |
| Payment.failed | payment by customer failed |
| payment.cancelled | payment was cancelled by the customer |
| url-payment.successful | payment by customer sent a payment-link/ulr was successful |
| url-payment.failed | payment by customer sent a payment-link/url failed |
| payout.approved | a payout request was approved by PiPay |
| payout.completed | a payout request was completed and funds have been sent |
| payout.failed | a payout request was not completed or unsuccesful |

Below is a sample `POST` request which will be sent by us (PiPay) to your `webhook-url` endpoint on successful payment:
```BASH
 curl https:your-url.com/webhook-url
-H "secret-key: YOUR_SECRET_KEY"
-H "x-pipay-signature: 'HMAC-SHA512-HASH-OF-BODY'"
-H "Content-Type: application/json"
-d '{ "event": "payment.success",
"memo":"Dog007", 
"amounttotal":102, 
"meta_data": {"memo":"Dog007"} , 
"txn_date": "2021-12-08 10:30:54", 
"user_email": "demo2@demo.com", 
"access_code": "5b5cdc21478d69bc", 
"paid": true,
"paymentid": "29kkF7glQINCJRAGIz2CgXyXG5uU", 
"txn_id":"85352c960c0bedb1aa0e25ddb3f5803e326922905b83fb2397aefba5e1d7c83d", 
"block_link": "https://api.testnet.minepi.com/transactions/d1a1debaf8a5d0ebec4f3c061e9f57509f6d38a022a77a267192b5b37d94e2ba", }'
-X POST
```
Below is a way you can process the said request and return the required **`200` Ok** response code in php:
```PHP
<?php
public function webhook_url(){
// Retrieve the request's body and parse it as JSON
  if ((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST' ) || !array_key_exists('secret-key', $_SERVER) ) 
      exit();

  // Retrieve the request's body
  $input = @file_get_contents("php://input");
  define('PIPAY_SECRET_KEY','SECRET_KEY');

  // validate event do all at once to avoid timing attack
  if($_SERVER['HTTP_SECRET_KEY'] !==  PIPAY_SECRET_KEY)
    exit();
  $event = json_decode($input);
  // Do something with $event maybe update database, mark paid ec
  http_response_code(200); 
}
?>
```

**`N/B`**:
* Using webhooks is not compulsory but its recommended.
* webhooks can be used together with Polling(calling the PiPay `GET` payment endpoint) to verify transaction.
* `webhook-url` and `secret-key` can be set on the PiPay Dashboard->settings 
* its required you also set your secret-key to enable your server be able to verify and make sure its actually PiPay that is sending the webhook notification.
* Please your webhook-url should not be set to your development server or localhost.e.g(localhost, localhost:3000, localhost:5000, localhost:8000 etc). It should be a publicly available url.
* Test out your webhook and make sure it receives **JSON `POST`** notifications and returns http status`200`.
* Failed webhook notification `POST` request(i.e when your server doesnt return http status-code `200`) are tried for the next `48hrs` within a `30mins` interval.


## Getting Transactions

The PiPay API allows you get all the recent transactions that have been initiated by you(both pending and completed). This is done by throwing an API **`GET`** request to the PiPay-API get transactions endpoint(`"https://pipay.pihub.biz/api/v1/transactions"`). The endpoint returns JSON data which is paginated. This is done to reduce the volume/size of data which will be returned and could be very bulky to handle by the User. Therefore the API caller(you) can specify the number of transactions `per_page` and the particular `page` you are accessing by parsing these as query parameters to the PiPay-API GET transactions endpoint. 
You can also add a query parameter for completed transactions by specifying for your customers who have paid.
Below is the parameters that are allowed when sending the `GET` request to the PiPay API:

| Option | Required | Type  | Description | Value |
| --- | --- | --- |--- | ---|
| Header Options | ---| ---| --- | --- |
| **key** | Yes | String | API key gotten from [PiPay](pipay.pihub.biz) dashboard->settings used to identify you| 'key':'ur public-key' |
| **Content-Type** | No | String | used to indicate the type of content being sent. Only JSON requests are accepted | 'Content-Type': application/json |
| Query Options | --- | --- | --- | --- |
| **page** | No | Integer | an integer which indicates the page of your transactions details you want to access(default= 1) | e.g 2,5 etc |
| **per_page** | No | Integer | an Integer which indicates the number of transactions that can be shown per-page of the API request(default=5) | e.g 7,9,10 etc |
| Other info | --- | --- | --- | --- |
| **method** | Yes | N/A | The request type must a **GET** request| `GET`|
| **api-url** | Yes | String | set to the PiPay API url | `https://pipay.pihub.biz/api/v1/transactions` |

Below is an example of the an API call using bash cURL:
```Bash
curl https://pipay.pihub.biz/api/v1/transactions?page=2&per_page=10
-H "key: YOUR_SECRET_KEY"
-H "Content-Type: application/json"
-X GET
```


Below is a sample `JSON` response from the PiPay API:

```JSON
{
  "message": "Transaction details retrieved", 
  "count": 25,//total number of txns
  "per_page":10, 
  "page":2, 
  "txns":[
{"access_code": "78b01e00c34c0491", "accessurl": "pipay.pihub.biz/gateway/78b01e00c34c0491", "amount": 20.0, "amounttotal": 20.6, "memo": "PL1660740607", "meta_data": "'memo':PL1660740607, 'compname':Test Company 2", "paid": false, "paymentid": null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}, 
{"access_code": "635b6664236c257f", "accessurl": "pipay.pihub.biz/gateway/635b6664236c257f", "amount": 20.0, "amounttotal": 20.6, "memo": "PL1660740512", "meta_data": "'memo':PL1660740512, 'compname':Test Company 2", "paid": false, "paymentid": 
null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}, 
{"access_code": "fc240b5eece944d7", "accessurl": "pipay.pihub.biz/gateway/fc240b5eece944d7", "amount": 20.0, "amounttotal": 20.6, "memo": "PL1660740301", "meta_data": "'memo':PL1660740301, 'compname':Test Company 2", "paid": false, "paymentid": null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}, 
{"access_code": "e6ed078ead9d5ea3", "accessurl": "pipay.pihub.biz/gateway/e6ed078ead9d5ea3", "amount": 20.0, "amounttotal": 20.6, "memo": "PL1660740180", "meta_data": "'memo':PL1660740180, 'compname':Test Company 2", "paid": false, "paymentid": null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}, 
{"access_code": "18704ef5cfdb5094", "accessurl": "pipay.pihub.biz/gateway/18704ef5cfdb5094", "amount": 20.0, "amounttotal": 20.6, "memo": "PL1660740130", "meta_data": "'memo':PL1660740130, 'compname':Test Company 2", "paid": false, "paymentid": null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}, {"memo": "PL1660740113", "meta_data": "'memo':PL1660740113, 'compname':Test Company 2", "paid": false, "paymentid": null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}, 
{"access_code": "fffcd6cdf7b7d795", "accessurl": "pipay.pihub.biz/gateway/fffcd6cdf7b7d795", "amount": 20.0, "amounttotal": 20.6, "memo": "PL1660735194", "meta_data": "'memo':PL1660735194, 'compname':Test Company 2", "paid": false, "paymentid": null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}, 
{"access_code": "4531fad6bba7ba44", "accessurl": "pipay.pihub.biz/gateway/4531fad6bba7ba44", "amount": 20.0, "amounttotal": 20.6, "memo": "PL1660731702", "meta_data": "'memo':PL1660731702, 'compname':Test Company 2", "paid": false, "paymentid": null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}, 
{"access_code": "af385e0b2212cde2", "accessurl": "pipay.pihub.biz/gateway/af385e0b2212cde2", "amount": 20.0, "amounttotal": 20.6, "memo": "PL1660731694", "meta_data": "'memo':PL1660731694, 'compname':Test Company 2", "paid": false, "paymentid": null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}, 
{"access_code": "966f0d2064644d52", "accessurl": "pipay.pihub.biz/gateway/966f0d2064644d52", "amount": 20.0, "amounttotal": 20.6, "memo": "PL1660725335", "meta_data": "'memo':PL1660725335, 'compname':Test Company 2", "paid": false, "paymentid": null, "txn_date": null, "txn_id": null, "user_email": "tests2@demo.com"}]}
```

N/B:
* The page and per_page optional path parameters make page navigation possible.



## Getting Payouts

The PiPay-API has an endpoint to enable you to be able monitor your payouts. Payouts must be requested on the PiPay dashboard and can be tracked with the API by throwing `GET` requests to payouts url endpoint(`https://pipay.pihub.biz/api/v1/payouts`).The endpoint returns JSON data which is paginated. This is done to reduce the volume/size of data which will be returned and could be very bulky to handle by the User. Therefore the API caller(you) can specify the number of `payout` transactions `per_page` and the particular `page` you are accessing by parsing these as query parameters to the PiPay-API GET transactions endpoint. 
You can also add a query parameter for completed transactions by specifying for your customers who have `paid`.
Below is the parameters that are allowed when sending the `GET` request to the PiPay API:

| Option | Required | Type  | Description | Value |
| --- | --- | --- |--- | ---|
| Header Options | ---| ---| --- | --- |
| **key** | Yes | String | API key gotten from [PiPay](pipay.pihub.biz) dashboard->settings used to identify you| 'key':'ur public-key' |
| **Content-Type** | No | String | used to indicate the type of content being sent. Only JSON requests are accepted | 'Content-Type': application/json |
| Query Options | --- | --- | --- | --- |
| **page** | No | Integer | an integer which indicates the page of your payout transactions details you want to access(default= 1) | e.g 2,5 etc |
| **per_page** | No | Integer | an Integer which indicates the number of payout transactions that can be shown per-page of the API request(default=5) | e.g 7,9,10 etc |
| **paid** | No | Boolean | a boolean indicating whether you want details of the transactions which have been paidout or not(either true or false) | true or false |
| Other info | --- | --- | --- | --- |
| **method** | Yes | N/A | The request type must a **GET** request| `GET`|
| **api-url** | Yes | String | set to the PiPay API url | `https://pipay.pihub.biz/api/v1/payouts` |

Below is an example of the an API call using bash cURL:
```Bash
curl https://pipay.pihub.biz/api/v1/payouts
-H "key: YOUR_SECRET_KEY"
-H "Content-Type: application/json"
-X GET
```

A sample JSON response is shown below:
```JSON
{"count": 7,
  "message": "Payout details retrieved",
  "page": 1,
  "per_page": 5,
  "txns": [
      {
          "address": "aj2oojdh9adahdahd2hkhedh292dq",
          "amount": 10.0,
          "amountpaid": 10.0,
          "currency": "Pi",
          "description": "Charlez91",
          "paymentid": "9f471effaeeed783",
          "payout-time": null,
          "request-time": "Wed, 19 Oct 2022 03:36:35 GMT",
          "status": "Approved",
          "usd-value": 1000.0
      },
      {
          "address": "aj2oojdh9adahdahd2hkhedh292dq",
          "amount": 5.0,
          "amountpaid": 5.0,
          "currency": "Pi",
          "description": "charles12",
          "paymentid": "f3c83cba0b191fe9",
          "payout-time": null,
          "request-time": "Wed, 19 Oct 2022 03:32:35 GMT",
          "status": "Approved",
          "usd-value": 500.0
      },
      {
          "address": "aj2oojdh9adahdahd2hkhedh292dq",
          "amount": 0.5,
          "amountpaid": 0.5,
          "currency": "Pi",
          "description": "Charlez91",
          "paymentid": "e83b88509e34f6fe",
          "payout-time": null,
          "request-time": "Wed, 19 Oct 2022 03:24:24 GMT",
          "status": "Unverified",
          "usd-value": 50.0
      },
      {
          "address": "aj2oojdh9adahdahd2hkhedh292dq",
          "amount": 10.0,
          "amountpaid": 10.0,
          "currency": "Pi",
          "description": "doggestboy",
          "paymentid": "bb37f79c790b79bc",
          "payout-time": null,
          "request-time": "Tue, 18 Oct 2022 04:22:03 GMT",
          "status": "Unverified",
          "usd-value": 1000.0
      },
      {
          "address": "3n2kqqo2o3ialkjo23ji23ja231ww",
          "amount": 0.2,
          "amountpaid": 0.2,
          "currency": "Pi",
          "description": "charles3",
          "paymentid": "DaavkABFoR1mh/x/EH.WbMB8XlCxI4yyH.LuoOdN0qFvED0SRy",
          "payout-time": null,
          "request-time": "Sat, 15 Oct 2022 05:47:35 GMT",
          "status": "Unverified",
          "usd-value": 20.0
      }
  ]
}
```


## Summary
The above shown examples are not strictly how the PiPay API should be used there is alot of rooms for customisation and all. 

With time more routes or functionalities will be added to the API as PiHub sees fit or due to Ideas from the API users(you).

For your tests(with the Pi Testnet) you can replace the base url for `PiPay`(https://pipay.pihub.biz) with `PiPayPRO`(https://pipaypro.com) and send your API request to it.