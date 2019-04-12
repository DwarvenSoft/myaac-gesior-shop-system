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
$title = 'Homepay';

if(!function_exists('curlit'))
{
	function curlit($link, $post=null)
	{
		$h = curl_init();
		curl_setopt($h, CURLOPT_URL, $link);
		curl_setopt($h, CURLOPT_HEADER, 0);
		if(!is_null($post))
			curl_setopt($h, CURLOPT_POSTFIELDS, $post);

		curl_setopt($h, CURLOPT_RETURNTRANSFER, 1);
		$r = curl_exec($h);
		curl_close($h);
		return $r;
	}
}

echo '<h1 align="center">Pay with Homepay.pl</h1><br />';
if(isset($_POST['code']) && isset($_POST['price']) && isset($_POST['type']) && preg_match("/^[A-Za-z0-9]{8}$/", $_POST['code']) && in_array($_POST['type'], array('sms', 'ivr')))
{
	$tmp = $config['homepay'][$_POST['type']][$_POST['price']];
	if(sizeof($tmp) == 3)
	{
		$post = array(
			'id' => $config['homepay']['uid'],
			'account' => $tmp[1],
			'command' => ($_POST['type'] == 'ivr' ? 'CheckIvr' : 'CheckSms'),
			'key' => $config['homepay']['key'],
			'code' => $_POST['code']
		);
		$status = json_decode(curlit("https://homepay.pl/api", json_encode($post)), true);

		if($status['code'] == 1)
		{
			add_points($account_logged, $tmp[0]);
			echo '<center>Dodano <b>' . $tmp[0] . ' punktów</b> do Twojego konta.</center><br/>';
		}
	}
}

echo '<script type="text/javascript">
var sources = {';
$i = 0;
foreach($config['homepay']['prs'] as $offer) {
	$post = array(
		'id' => $config['homepay']['uid'],
		'account' => $offer[1],
		'command' => 'Prs',
		'key' => $config['homepay']['key'],
		'control' => $account_logged->getId()
	);
	$link = json_decode(curlit("https://homepay.pl/api", json_encode($post)), true);
	$i++;
	echo $offer[1] . ': "' . $link . '"' . ($i == sizeof($config['homepay']['prs']) ? '' : ',');
}

echo '}';
echo 'var homepay = {
	sms: function(selector)
	{
		var tmp = (selector.value * 1.23);
		document.getElementById("homepay_sms").innerHTML = tmp.toFixed(2);
		var noweNumery = ' . ($config['homepay']['noweNumery'] ? 'true' : 'false') . ';

		tmp = (selector.value * 1);
		if(selector.value == 25 && noweNumery)
			document.getElementById("homepay_target").innerHTML = "92520";
		elseif(selector.value == 6 && noweNumery)
			document.getElementById("homepay_target").innerHTML = "76660";
		elseif(selector.value > 9)
			document.getElementById("homepay_target").innerHTML = "9" + tmp.toFixed(0) + "55";
		else
			document.getElementById("homepay_target").innerHTML = "7" + tmp.toFixed(0) + "55";
	},
	ivr: function(selector)
	{
		var tmp = (selector.value * 1.23);
		document.getElementById("homepay_ivr").innerHTML = tmp.toFixed(2);
		document.getElementById("homepay_number").innerHTML = selector.options[selector.selectedIndex].getAttribute("rel");
	},
	prs: function(selector)
	{
		var e = document.getElementById("homepay_prs");
		var p = e.parentNode;
		p.removeChild(e);

		var n = document.createElement("script");
		n.src = sources[selector];
		n.type = "text/javascript";

		n.id = "homepay_prs";
		p.appendChild(n);
	},
	transfer: function(selector)
	{
		document.getElementById("homepay_link").action = selector.value;
	}
};
</script>';

if(isset($config['homepay']['message']) && strlen($config['homepay']['message']) != 0)
{
	$first = key($config['homepay']['sms']);
	echo '<h2 align="center">Through SMS premium</h2>
<center>Wyślij wiadomość o treści: <b>HPAY.' . $config['homepay']['message'] . '.' . $account_logged->getId() . '</b> na numer <span id="homepay_target" style="font-weight: bold;">' . (($first == 6 && $config['homepay']['noweNumery']) ? '76660' : (($first == 25 && $config['homepay']['noweNumery']) ? '92520' : '7' . $first . '55')) . '</span>.<br/>
<br/>';
	if($config['homepay']['codes'])
		echo '<form action="" method="post">';

	echo '<b>Oferta</b>:<br/>
<b>Oferta</b>:<br/>
<select onchange="homepay.sms(this);">';
	foreach($config['homepay']['sms'] as $cost => $offer)
		echo '<option value="' . $cost . '">' . (isset($offer[2]) ? $offer[2] : $offer[0] . ' punkty premium') . ' (' . number_format($cost * 1.23, 2, ',', '') . ' PLN)</option>';

	echo '</select><br/>';
	if($config['homepay']['codes'])
		echo '<b>Kod</b>:<br/>
<input type="text" name="code" value="" placeholder="Tu wpisz kod..."/><br/>
<input type="hidden" name="type" value="sms"/><input type="submit" value="Prześlij"/>
</form><br/>';

	echo '<br/>
Cena brutto (z VAT) za SMS wynosi <b id="homepay_sms">' . number_format($first * 1.23, 2) . '</b> PLN,<br />
pobierana tylko po wysłaniu wiadomości, a nigdy nie przed jej wysłaniem.<br />
Usługa doładowania punktów premium dostarczona przez ' . $config['homepay']['provider'] . ',<br />
dostępna we wszystkich polskich sieciach komórkowych.<br />
Serwis obsługuje <a href="http://homepay.pl">Homepay.pl</a>, <a href="http://homepay.pl/regulamin/regulamin-uslug-premium/">regulamin usług premium</a>, <a href="https://homepay.pl/reklamacje/">reklamacje</a>.<br />
Infolinia dostępna pod numerem telefonu: +48 222668525 (koszt za połączenie zgodnie z cennikiem Operatora).</center>';
}

if(sizeof($config['homepay']['ivr']) > 0)
{
	$first = key($config['homepay']['ivr']);
	echo '<h2 align="center">Through IVR premium</h2>
<center>Zadzwoń na numer <span id="homepay_number" style="font-weight: bold;">' . $config['homepay']['ivr'][$first][2] . '</span>.<br/>
<br/>
<form action="" method="post">
<b>Oferta</b>:<br/>
<select name="price" onchange="homepay.ivr(this);">';
	foreach($config['homepay']['ivr'] as $cost => $offer)
		echo '<option value="' . $cost . '" rel="' . $offer[2] . '">' . (isset($offer[3]) ? $offer[3] : $offer[0] . ' punkty premium') . ' (' . number_format($cost * 1.23, 2, ',', '') . ' PLN)</option>';

	echo '</select><br/>
<b>Kod</b>:<br/>
<input type="text" name="code" value="" placeholder="Tu wpisz kod..."/><br/>
<input type="hidden" name="type" value="ivr"/><input type="submit" value="Prześlij"/>
</form><br/>
<br/>
Cena brutto (z VAT) za połączenie wynosi <b id="homepay_ivr">' . number_format($first * 1.23, 2) . '</b> PLN,<br />
pobierana w momencie nawiązania połączenia.<br />
Usługa doładowania punktów premium dostarczona przez ' . $config['homepay']['provider'] . '.<br />
Serwis obsługuje <a href="http://homepay.pl">Homepay.pl</a>, <a href="http://homepay.pl/regulamin/regulamin-uslug-premium/">regulamin usług premium</a>, <a href="https://homepay.pl/reklamacje/">reklamacje</a>.<br />
Infolinia dostępna pod numerem telefonu: +48 222668525 (koszt za połączenie zgodnie z cennikiem Operatora).<br/>
Dostawcą usługi jest firma "Numer IVR Mobiltek"</center>';
}

if(sizeof($config['homepay']['prs']) > 0)
{
	echo '<h2 align="center">Through PRS</h2>
<center><b>Oferta</b>:<br/>
<select name="price" onchange="homepay.prs(this);">';
	foreach($config['homepay']['prs'] as $offer)
		echo '<option value="' . $offer[1] . '">' . (isset($offer[2]) ? $offer[2] : $offer[0] . ' punkty premium') . '</option>';

	reset($config['homepay']['prs']);
	$tmp = current($config['homepay']['prs']);
	echo '</select><br/>
<button id="homepay">Zapłać</button>';
$post = array(
'id' => $config['homepay']['uid'],
'account' => $tmp[1],
'command' => 'Prs',
'key' => $config['homepay']['key'],
'control' => $account_logged->getId()
);
$link = json_decode(curlit("https://homepay.pl/api", json_encode($post)), true);
echo '<script id="homepay_prs" type="text/javascript" src="' . $link . '"></script></center>';
}

if(isset($config['homepay']['aid']) && $config['homepay']['aid'] != 0
	&& isset($config['homepay']['key']) && strlen($config['homepay']['message']) != 0)
{
	$transfer = array();
	foreach($config['homepay']['transfers'] as $cost => $offer)
	{
		$post = array(
			'id' => $config['homepay']['uid'],
			'command' => 'InitTransfer',
			'key' => $config['homepay']['key'],
			'account' => $config['homepay']['aid'],
			"for" => $offer . ' premium points',
			"return" => $config['website'],
			"control" => $_SESSION['account_id'],
			"amount" => $cost
		);
		$data = json_decode(curlit('https://homepay.pl/api', json_encode($post)), true);

		if($data['code'] == 1)
			array_push($transfer, array($data['return']['url'], $cost, $offer));
	}

	echo '<h2 align="center">Through wire transfer</h2>
<center><b>Oferta</b>:<br/>
<form id="homepay_link" action="' . $transfer[0][0] . '" method="post">
<select name="price" onchange="homepay.transfer(this);">';
	foreach($transfer as $_)
		echo '<option value="' . $_[0] . '">' . (is_array($_[2]) ? $_[2][1] : $_[2] . ' punkty premium') . ' (' . number_format($_[1], 2, ',', '') . ' PLN)</option>';

	echo '</select><br/>
<br/>
<input type="submit" name="transfer" value="Dalej..." />
</form><br/>
Usługa dostępna we wszystkich polskich bankach.<br />
Serwis obsługuje <a href="http://homepay.pl">Homepay.pl</a>, <a href="https://homepay.pl/reklamacje/">reklamacje</a>.<br />
Infolinia dostępna pod numerem telefonu: +48 222668525 (koszt za połączenie zgodnie z cennikiem Operatora).</center>';
}