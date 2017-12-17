<?php
if(!defined('INITIALIZED'))
	exit;

require_once(SYSTEM . 'pay/paypal/config.php');

$ip = $_SERVER['REMOTE_ADDR'];

require(SYSTEM . 'pay/paypal/PaypalIPN.php');
$ipn = new PaypalIPN();
// Use the sandbox endpoint during testing.
//$ipn->useSandbox();
$ipn->usePHPCerts();
$verified = $ipn->verifyIPN();
if (!$verified) {
	log_append('paypal_scammer.log', $ip);
	die('Access denied.');
}

$receiverMail = $_REQUEST['receiver_email']; // ots admin mail
$status = $_REQUEST['payment_status']; // payment status, we add only when is 'Completed'
$currency = $_REQUEST['mc_currency']; // money currency, like USD or EUR
$gross = $_REQUEST['mc_gross']; // amount of money, like: 10.00
$payerMail = $_REQUEST['payer_email']; // player mail
$accountID = $_REQUEST['custom']; // user account ID
$transactionID = $_REQUEST['txn_id']; // transaction ID

$logFile = SYSTEM . 'pay/paypal/reported_ids/' . $transactionID . '.log';
if(!file_exists($logFile) && $status == 'Completed')
{
	foreach($paypals as $pay)
	{
		if($receiverMail == $pay['mail'] && $currency == $pay['money_currency'] && $gross == $pay['money_amount'])
		{
			$account = new OTS_Account();
			$account->load($accountID);
			if($account->isLoaded())
			{
				if(file_put_contents($logFile, 'accountID:' . $accountID . ',mail:' . $payerMail . ',amount:' . $gross . ' ' . $currency . ',points:' . $pay['premium_points']) !== false)
				{
					add_points($account, $pay['premium_points']);
				}
			}
			break;
		}
	}
}

function add_points(OTS_Account $account, $amount)
{
	if(!$account->isLoaded())
		return false;

	$account->setCustomField('premium_points', $account->getCustomField('premium_points') + $amount);
	return true;
}

header("HTTP/1.1 200 OK");