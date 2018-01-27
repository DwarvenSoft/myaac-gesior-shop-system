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
defined('MYAAC') or die('Direct access not allowed!');

if(!$logged) {
	$was_before = $config['friendly_urls'];
	$config['friendly_urls'] = true;
	
	echo 'To buy points you need to be logged. ' . generateLink(getLink('?subtopic=accountmanagement') . '&redirect=' . urlencode(BASE_URL . '?subtopic=points&system=paypal'), 'Login') . ' first to make a donate.';
	
	$config['friendly_urls'] = $was_before;
	return;
}

$was_before = $config['friendly_urls'];
$config['friendly_urls'] = false;
if($config['paypal']['terms'] && !isset($_REQUEST['agree']))
{
	echo $twig->render('gesior-shop-system/paypal-terms.html.twig');
	return;
}

if(empty($config['paypal']['contact_email'])) {
	$config['paypal']['contact_email'] = $config['paypal']['email'];
	$twig->addGlobal('config', $config);
}

if(!in_array($config['paypal']['payment_type'], array('_xclick', '_donations'))) {
	error('Unsupported $config paypal payment_type: ' . $config['paypal']['payment_type'] . '. Please go to your config.php and fix it.');
	return;
}

$is_localhost = strpos(BASE_URL, 'localhost') !== false || strpos(BASE_URL, '127.0.0.1') !== false;
if($is_localhost) {
	warning("Paypal is not supported on localhost (" . BASE_URL . "). Please change your domain to public one and visit this site again later.<br/>
	This site is visible, but you can't donate.");
}

echo $twig->render('gesior-shop-system/paypal.html.twig', array('is_localhost' => $is_localhost));
$config['friendly_urls'] = $was_before;
?>