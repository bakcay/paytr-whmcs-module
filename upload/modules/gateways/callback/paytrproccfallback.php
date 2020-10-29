<?php
/**
 * Created by PhpStorm.
 * User: bunyaminakcay
 * Project name inetmar
 * 30.10.2020 02:04
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');
$gatewayModuleName=str_replace('fallback','',$gatewayModuleName);

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}
/**
 * Validate callback authenticity.
 *
 * Most payment gateways provide a method of verifying that a callback
 * originated from them. In the case of our example here, this is achieved by
 * way of a shared secret which is used to build and compare a hash.
 */

$invoice_id = explode( 'PAYTRWHMCS', $_POST['merchant_oid'] );
$trn = $invoice_id[1];

$st=$_POST['status'];
$amt=$_POST['total_amount'];
$hs=$_POST['hash'];

$hash=base64_encode(hash_hmac('sha256', $_POST['merchant_oid'].$gatewayParams['merchantSalt'].$st.$amt,$gatewayParams['merchantKey'],true));

if( $hash != $hs )
    die('PAYTR notification failed: bad hash');

echo "OK";

/**
 * Log Transaction.
 *
 * Add an entry to the Gateway Log for debugging purposes.
 *
 * The debug data can be a string or an array. In the case of an
 * array it will be
 *
 * @param string $gatewayName        Display label
 * @param string|array $debugData    Data to log
 * @param string $transactionStatus  Status
 */
logTransaction($gatewayParams['name'], $_POST, $transactionStatus);

if( $st != 'success' )
    exit;

/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 */
$invoiceId = checkCbInvoiceID($trn, $gatewayParams['name']);

/**
 * Check Callback Transaction ID.
 *
 * Performs a check for any existing transactions with the same given
 * transaction number.
 *
 * Performs a die upon encountering a duplicate.
 */
checkCbTransID($trn);

/**
 * Add Invoice Payment.
 *
 * Applies a payment transaction entry to the given invoice ID.
 *
 * @param int $invoiceId         Invoice ID
 * @param string $transactionId  Transaction ID
 * @param float $paymentAmount   Amount paid (defaults to full balance)
 * @param float $paymentFee      Payment fee (optional)
 * @param string $gatewayModule  Gateway module name
 */
addInvoicePayment(
    $trn,
    $trn,
    '',
    '',
    $gatewayParams['name']
);