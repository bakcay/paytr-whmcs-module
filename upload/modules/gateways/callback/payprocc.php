<?php
/**
 * Created by PhpStorm.
 * User: bunyaminakcay
 * Project name inetmar
 * 30.10.2020 01:55
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */

require_once __DIR__ . '/../../../init.php';

App::load_function('gateway');
App::load_function('invoice');

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Verify the module is active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}
$invoiceId = $_GET['invoiceid'];

if ($_GET['result'] == 'success') {

    callback3DSecureRedirect($invoiceId, true);

} else {

    callback3DSecureRedirect($invoiceId, false);

}
