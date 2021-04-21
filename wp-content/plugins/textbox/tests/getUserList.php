<?php
/* Config */
$token = '123123';

/* Do not touch */
preg_match( '/(.*)\/wp-content/', $_SERVER['REQUEST_URI'], $wordpressDir );
$wordpressDir = $wordpressDir[1] ? $wordpressDir[1] : '';
$url = 'http://' . $_SERVER['SERVER_NAME'] . $wordpressDir . '/wp-admin/admin-ajax.php';

$fields = array(
	'action' => urlencode('textbox_get_user_list'),
    'token' => urlencode($token)
);

//url-ify the data for the POST
$arStringFields = array();
foreach( $fields as $key => $value ) {
	$arStringFields[] = "{$key}={$value}";
}
$stringFields = implode('&', $arStringFields);


//open connection
$curl = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POST, count($fields));
curl_setopt($curl, CURLOPT_POSTFIELDS, $stringFields);

//execute post
$result = curl_exec($curl);
print $result;