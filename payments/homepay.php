<?php
/**
 * This is shop system taken from Gesior, modified for MyAAC.
 *
 * @name      myaac-gesior-shop-system
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @website   github.com/slawkens/myaac-gesior-shop-system
 */

require_once('../common.php');
require_once(SYSTEM . 'functions.php');
require_once(SYSTEM . 'init.php');
require_once(LIBS . 'shop-system.php');
require_once(PLUGINS . 'gesior-shop-system/config.php');

if(!isset($config['homepay']) || !count($config['homepay']) || $config['homepay']['uid'] == 0)
	die('HomePay disabled.');

function check_ip()
{
	if(empty($_SERVER['REMOTE_ADDR']))
		return false;

	if(ini_get('allow_url_fopen') != 1)
		return gethostbyname("get.homepay.pl") == $_SERVER['REMOTE_ADDR'];

	$handle = fopen('http://get.homepay.pl/index.htm', 'r');
	$data = trim(stream_get_contents($handle));
	fclose($handle);
	return in_array($_SERVER['REMOTE_ADDR'], explode(',', $data));
}

if(empty($_POST['json']) || !check_ip())
	die('Access denied');

$time = date('d.m.Y, g:i A');
$json = json_decode($_POST['json'], true);
foreach($json as $data)
{
	if(isset($data['account']))
	{
		echo $data['account'] . ',' . $data['id'] . ",1\n";
		if($data['status'] != 2)
		{
			$account = new OTS_Account();
			$account->load($data['control']);
			if($account->isLoaded()) {
				$exp = explode(' ', str_replace('\"', '', $data['title']));
				$points = (int)$exp[0];
				if(add_points($account, $points)) {
					$account_id = $account->getId();
					$data_account = $data['account'];
					$data_id = $data['id'];
					$data_status = $data['status'];
					log_append('homepay.log', "$time;$account_id;$points;$data_account;$data_id;$data_status");
				}
			}
		}
	}
	elseif(isset($data['prs_account']) && isset($data['prs_ppp']))
	{
		foreach($config['homepay']['prs'] as $offer)
		{
			if($offer[1] != $data['prs_account'])
				continue;

			$account = $ots->createObject('Account');
			$account_id = (int)str_replace('\"', '', $data['prs_control']);
			$account->load($account_id);
			if($account->isLoaded()) {
				$points = round($data['prs_ppp']);
				if(add_points($account, $points)) {
					$account_id = $account->getId();
					$data_account = $data['prs_account'];
					$data_id = $data['prs_id'];
					log_append('homepay.log', "$time;$account_id;$points;$data_account;$data_id");
				}
			}

			echo $data['prs_account'] . ',' . $data['prs_id'] . ",1\n";
			break;
		}
	}
	elseif(isset($data['prs_account']) && !isset($data['prs_ppp']))
	{
		foreach($config['homepay']['prs'] as $offer)
		{
			if($offer[1] != $data['prs_account'])
				continue;

			$account = $ots->createObject('Account');
			$account_id = (int)str_replace('\"', '', $data['prs_control']);
			$account->load($account_id);
			if($account->isLoaded()) {
				$points = (int)$offer[0];
				if(add_points($account, $points)) {
					$account_id = $account->getId();
					$data_account = $data['prs_account'];
					$data_id = $data['prs_id'];
					log_append('homepay.log', "$time;$account_id;$points;$data_account;$data_id");
				}
			}

			echo $data['prs_account'] . ',' . $data['prs_id'] . ",1\n";
			break;
		}
	}
	elseif(isset($data['sms_account']))
	{
		$sms = explode('.', str_replace('\"', '', $data['sms_text']));
		foreach($config['homepay']['sms'] as $cost => $offer)
		{
			if($offer[1] != $data['sms_account'])
				continue;

			$account = $ots->createObject('Account');
			$account_id = (int)$sms[2];
			$account->load($account_id);
			if($account->isLoaded()) {
				$points = (int)$offer[0];
				if(add_points($account, $points)) {
					$account_id = $account->getId();
					$data_account = $data['sms_account'];
					$sms_text = $data['sms_text'];
					$sms_id = $data['sms_id'];
					log_append('homepay.log', "$time;$account_id;$points;$data_account;$sms_text;$sms_id");
				}
			}

			echo $data['sms_account'] . ',' . $data['sms_id'] . ",1\n";
			break;
		}
	}
}