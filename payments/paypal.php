<?php
/**
 * This is shop system taken from Gesior, modified for MyAAC.
 *
 * @name      myaac-gesior-shop-system
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @website   github.com/slawkens/myaac-gesior-shop-system
 * @version   2.0
 */

require_once('../common.php');
require_once(SYSTEM . 'functions.php');
require_once(SYSTEM . 'init.php');
require_once(LIBS . 'shop-system.php');
require_once(PLUGINS . 'gesior-shop-system/config.php');

if(!isset($config['paypal']) || !count($config['paypal']) || !count($config['paypal']['options']))
	die('PayPal disabled.');

$ip = $_SERVER['REMOTE_ADDR'];

require(LIBS . 'paypal.php');
$ipn = new PaypalIPN();
// Use the sandbox endpoint during testing.
//$ipn->useSandbox();
$ipn->usePHPCerts();
$verified = $ipn->verifyIPN();
if (!$verified) {
	log_append('paypal_scammer.log', $ip);
	die('Access denied.');
}

$paylist = $config['paypal']['options'];
$custom = stripslashes(trim($_REQUEST['custom']));
$payer_email = $_REQUEST['payer_email'];
$receiver_email = $_REQUEST['receiver_email'];
$business = $_REQUEST['business'];

$payment_status = $_REQUEST['payment_status'];
$payer_status = $_REQUEST['payer_status'];

$mc_gross = $_REQUEST['mc_gross'];
$mc_fee = $_REQUEST['mc_fee'];
$mc_currency = $_REQUEST['mc_currency'];
$address_status = $_REQUEST['address_status'];
$payer_status = $_REQUEST['payer_status'];

$time = date('d.m.Y, H:i');

if(strtolower($payment_status) == 'completed' && $business == $config['paypal']['email'] && isset($paylist[$mc_gross]) && $mc_currency == 'EUR')
{
	$account = new OTS_Account();
	$account->load($custom);
	if($account->isLoaded()) {
		if(add_points($account, $paylist[$mc_gross])) {
			log_append('paypal.log', "$time;$custom;$payer_email;$mc_gross:$mc_currency;$mc_fee;$receiver_email;$payment_status;$ip;$business;$address_status;$payer_status");
		}
	}
}
else
	echo('Error.');

header("HTTP/1.1 200 OK");
?>