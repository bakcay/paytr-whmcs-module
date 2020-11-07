<?php
/**
 * Created by PhpStorm.
 * User: bunyaminakcay
 * Project name inetmar
 * 29.10.2020 02:08
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */


if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

define('payprocc_version','1.5.Stable');
define('payprocc_name','PayTR Pro');

function payprocc_MetaData() {
    return [
        'DisplayName' => payprocc_name,
        'APIVersion'  => payprocc_version,
        // Use API Version 1.1
    ];
}

function payprocc_config() {

    $sysurl = Capsule::table('tblconfiguration')->where('setting','SystemURL')->value('value');

    $version = payprocc_version;
    $imagedesc=payprocc_name .' '.payprocc_version.'<br>Destek : <a href="mailto:bunyamin@bunyam.in">bunyamin@bunyam.in</a><br><a href="javascript:void(0)" target="_blank">Dökümantasyon linki</a>';
    $imagedesc2='<a href="https://www.paytr.com/magaza/ayarlar" target="_blank">PayTR Panelinde Ayarlar sekmesinde</a> "BİLDİRİM URL" aşağıdakini ayarlayın<br><br><code>'.$sysurl . 'modules/gateways/callback/payproccfallback.php</code>';
    return [
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => [
            'Type'  => 'System',
            'Value' => payprocc_name,
        ],


        'merchantid'  => [
            'FriendlyName' => 'Mağaza Kodu',
            'Type'         => 'text',
            'Size'         => '25',
            'Default'      => '',
            'Description'  => 'Mağaza No (merchant_id) ',
        ],

        'merchantkey'  => [
            'FriendlyName' => 'Mağza Anahtarı',
            'Type'         => 'password',
            'Size'         => '25',
            'Default'      => '',
            'Description'  => 'Mağaza Parola (merchant_key). Gizli tutun.',
        ],

        'merchantsalt'  => [
            'FriendlyName' => 'Mağza Gizli Anahtarı',
            'Type'         => 'password',
            'Size'         => '25',
            'Default'      => '',
            'Description'  => 'Mağaza Gizli Anahtar (merchant_salt). Gizli tutun',
        ],


        'paymentbtntext'  => [
            'FriendlyName' => 'Ödeme Butonu Yazısı',
            'Type'         => 'text',
            'Size'         => '25',
            'Default'      => 'Ödeme Yap',
            'Description'  => 'Müşterinin göreceği Ödeme yapma butonu yazısı.',
        ],

        'installament' => [
            'FriendlyName' => 'Taksitlendirme',
            "Type"         => 'dropdown',
            "Options"      => [
                '0' => 'Taksitlendirme Yok',
                2   => '2 Taksit',
                3   => '3 Taksit',
                4   => '4 Taksit',
                5   => '5 Taksit',
                6   => '6 Taksit',
                7   => '7 Taksit',
                8   => '8 Taksit',
                9   => '9 Taksit',
                10  => '10 Taksit',
                11  => '11 Taksit',
                12  => '12 Taksit'
            ],
            //'Taksitlendirme Yok,2 Taksit,3 Taksit,4 Taksit,5 Taksit,6 Taksit,7 Taksit,8 Taksit,9 Taksit,10 Taksit,11 Taksit,12 Taksit',
            'Description'  => 'En Fazla seçilebilecek taksit sayısı',
        ],
        'orderprefix'  => [
            'FriendlyName' => 'Sipariş Ön Eki',
            'Type'         => 'text',
            'Size'         => '25',
            'Default'      => 'FATURA',
            'Description'  => 'Sipariş numaranız örneğin "FATURA12345" olacak. Türkçe karakter kullanmayın, Yanlızca A-Z <script type="text/javascript"> var imagedesc=\''.$imagedesc.'\';  var imagedesc2=\''.$imagedesc2.'\'; </script> <link href="https://cdn.bunyam.in/paytr/main.css?v='.$version.'" rel="stylesheet" /> <script type="text/javascript" src="https://cdn.bunyam.in/paytr/main.js?v='.$version.'"></script>',
        ],
        'testingmode' => [
            'FriendlyName' => 'Test Modu',
            'Type' => 'yesno',
            'Description' => 'Yanlızca Test Modu için kullanın. Canlı için kullanmayın.',
        ]

    ];
}


function payprocc_nolocalcc() {
}


function payprocc_capture($params) {

}

function payprocc_remoteinput($params) {


    $merchant_id       = $params['merchantid'];
    $merchant_key      = $params['merchantkey'];
    $merchant_salt     = $params['merchantsalt'];
    $installament      = $params['installament'];
    $testmode          = $params['testingmode'];
    $prefix            = trim($params['orderprefix']);
    $email             = $params['clientdetails']['email'];
    $clientid          = $params['clientdetails']['id'];
    $invoiceid         = $params['invoiceid'];
    $currency          = $params['currency'];
    $payment_amount    = ($params['amount'] * 100);
    $user_name         = $params['clientdetails']['firstname'] . ' ' . $params['clientdetails']['lastname'];
    $user_address      = $params['clientdetails']['address1'] . ' ' . $params['clientdetails']['address2'] . ' ' . $params['clientdetails']['city'] . ' ' . $params['clientdetails']['state'];
    $phonenumber       = $params['clientdetails']['phonenumber'];
    $phonenumber       = str_replace('+90.', '', $phonenumber);
    $merchant_ok_url   = $params['systemurl'] . 'modules/gateways/callback/payprocc.php?&invoiceid='.$invoiceid.'&result=success';
    $merchant_fail_url = $params['systemurl'] . 'modules/gateways/callback/payprocc.php?&invoiceid='.$invoiceid.'&result=failed';
    $timeout_limit     = "30";
    $debug_on          = $testmode == 'on';
    $test_mode         = $testmode == 'on';


	$no_installment	= 0;
	$max_installment = 0;

	if(strlen($params['clientdetails']['firstname'])>0){
	    $user_name.= ' - '.$params['clientdetails']['firstname'];
    }
    if($phonenumber>5000000000 && $phonenumber<5999999999){

    }else{
        $phonenumber='559000'.rand(1000,9999);
    }

	$user_phone = $phonenumber;


    if(strlen($prefix)<1){
        $prefix='ORDER';
    }
    $merchant_oid = "{$prefix}{$invoiceid}";

	## Müşterinin sepet/sipariş içeriği
	$user_basket = "";

	$_u1 = Capsule::table('tblinvoiceitems')
        ->where('invoiceid',$invoiceid)
        ->implode('description',' ');

	$user_basket = base64_encode(json_encode([[$_u1, $params['amount'] , 1]]));


	if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	} elseif( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else {
		$ip = $_SERVER["REMOTE_ADDR"];
	}

	$user_ip=$ip;


	if($installament==0){
	    $no_installment=1;
	    $max_installment=1;
    }else{
	    $no_installment=0;
	    $max_installment=$installament;
    }


    $hash_str    = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;
    $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));
    $post_vals   = [
        'merchant_id'       => $merchant_id,
        'user_ip'           => $user_ip,
        'merchant_oid'      => $merchant_oid,
        'email'             => $email,
        'payment_amount'    => $payment_amount,
        'paytr_token'       => $paytr_token,
        'user_basket'       => $user_basket,
        'debug_on'          => $debug_on,
        'no_installment'    => $no_installment,
        'max_installment'   => $max_installment,
        'user_name'         => $user_name,
        'user_address'      => $user_address,
        'user_phone'        => $user_phone,
        'merchant_ok_url'   => $merchant_ok_url,
        'merchant_fail_url' => $merchant_fail_url,
        'timeout_limit'     => $timeout_limit,
        'currency'          => $currency,
        'test_mode'         => $test_mode
    ];

	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1) ;
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);

	$result = @curl_exec($ch);
	$err_no = curl_errno($ch);
	curl_close($ch);

	logTransaction($params["name"], ['form_data'=>$post_vals,'response'=>$result], "Request");

	$response_code='';


    if ($err_no) {
        $response_code = 'PayTR bir hatayla karşılaştı, Curl ' . $err_no;
    } else {
        $result = json_decode($result, true);

        if ($result['status'] == 'success') {
            $token         = $result['token'];
           // $response_code = '<iframe src="https://www.paytr.com/odeme/guvenli/' . $token . '" style="width: 100%;min-height: 600px"></iframe>';
            $response_code='<form method="get" action="https://www.paytr.com/odeme/guvenli/' . $token . '"><noscript><input type="submit" value="Click here to continue"></noscript></form>';
        } else {
            $response_code = 'PayTR bir hatayla karşılaştı, Token :' . $result['reason'];
        }
    }

	return $response_code;

}
