<?php
/**
 * Created by PhpStorm.
 * User: bunyaminakcay
 * Project name inetmar
 * 30.10.2020 02:04
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
require_once __DIR__ . '/../../../init.php';

App::load_function('gateway');
App::load_function('invoice');

$gatewayModuleName = basename(__FILE__, '.php');
$gatewayModuleName = str_replace('fallback', '', $gatewayModuleName);
$gwparams          = getGatewayVariables($gatewayModuleName);
$prefix            = trim($gwparams['orderprefix']);
$merchant_id       = $gwparams['merchantid'];
$merchant_oid      = $_POST['merchant_oid'];
$merchant_key      = $gwparams['merchantkey'];
$merchant_salt     = $gwparams['merchantsalt'];
$installment       = $_POST['installment_count'];
$invoice_id        = str_replace($prefix, '', $merchant_oid);
$status            = $_POST['status'];
$amount            = $_POST['total_amount'];
$_hash             = $_POST['hash'];
$hash              = base64_encode(hash_hmac('sha256', $merchant_oid . $merchant_salt . $status . $amount, $merchant_key, true));


// Die if module is not active.
if (!$gwparams['type']) {
    die("Modül Aktif Değil");
}

if ($hash != $_hash) {
    die('PAYTR Bildirimde hata var, Hash Uyumsuz');
}

echo "OK";

logTransaction($gwparams['name'], $_POST, $status);

if ($status != 'success') {
    exit;
}

$ii = checkCbInvoiceID($invoice_id, $gwparams['name']);

checkCbTransID($invoice_id);

$transaction_name = 'PAYTR_' . $merchant_oid;
if ($installment > 1) {
    $transaction_name .= '_' . $installment . 'Taksit';
}

addInvoicePayment($invoice_id, $transaction_name, '', '', $gwparams['name']);