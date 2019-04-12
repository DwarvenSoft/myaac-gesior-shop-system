<?php
/**
 * This is shop system taken from Gesior, modified for MyAAC.
 *
 * @name      myaac-gesior-shop-system
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @website   github.com/slawkens/myaac-gesior-shop-system
 */
defined('MYAAC') or die('Direct access not allowed!');

class GesiorShop {
	public static function getDonationType() {
		global $config;
		$field = 'premium_points';
		if(strtolower($config['donation_type']) == 'coins') {
			$field = 'coins';
		}

		return $field;
	}
}
function add_points(OTS_Account $account, $amount)
{
	if(!$account->isLoaded())
		return false;

	$field = GesiorShop::getDonationType();
	$account->setCustomField($field, $account->getCustomField($field) + $amount);
	return true;
}