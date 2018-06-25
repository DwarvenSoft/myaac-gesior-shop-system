<?php
/**
 * This is shop system taken from Gesior, modified for MyAAC.
 *
 * @name      myaac-gesior-shop-system
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @website   github.com/slawkens/myaac-gesior-shop-system
 * @version   2.1
 */
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Gifts';

require_once(LIBS . 'shop-system.php');
require_once(PLUGINS . 'gesior-shop-system/config.php');

if(!$config['gifts_system'])
{
	if(!admin())
	{
		echo 'The gifts system is disabled there, sorry.';
		return;
	}
	else
		warning("You're able to access this page but it is disabled for normal users.<br/>
		Its enabled for you so you can view/edit shop offers before displaying them to use.<br/>
		You can enable it by editing this line in myaac config.local.php file:<br/>
		<p style=\"margin-left: 3em;\"><b>\$config['gifts_system'] = true;</b></p>");
}

if(!isset($config['shop_outfit_colors'])) {
	$config['shop_outfit_colors'] = array(
		'head' => 4,
		'body' => 38,
		'legs' => 87,
		'feet' => 114,
	);
}

if(GesiorShop::getDonationType() == 'coins' && !fieldExist('coins', 'accounts')) {
	error("Your server doesn't support accounts.coins. Please change back config.donation_type to points.");
	return;
}

$canEdit = superAdmin();

if($logged) {
	$user_premium_points = $account_logged->getCustomField(GesiorShop::getDonationType());
}
else {
	$was_before = $config['friendly_urls'];
	$config['friendly_urls'] = true;
	$user_premium_points = generateLink(getLink('?subtopic=accountmanagement') . '&redirect=' . urlencode(BASE_URL . '?subtopic=gifts'), 'Login first');
	$config['friendly_urls'] = $was_before;
}

function getItemByID($id)
{
	global $db;
	$id = (int) $id;
	$data = $db->query('SELECT * FROM '.$db->tableName('z_shop_offer').' WHERE '.$db->fieldName('id').' = '.$db->quote($id).';')->fetch();
	$offer['hidden'] = $data['hidden'];
	if ($data['offer_type'] == 'pacc')
	{
		$offer['id'] = $data['id'];
		$offer['type'] = $data['offer_type'];
		$offer['days'] = $data['count1'];
		$offer['points'] = $data['points'];
		$offer['description'] = $data['offer_description'];
		$offer['name'] = $data['offer_name'];
	}
	elseif ($data['offer_type'] == 'item')
	{
		$offer['id'] = $data['id'];
		$offer['type'] = $data['offer_type'];
		$offer['item_id'] = $data['itemid1'];
		$offer['item_count'] = $data['count1'];
		$offer['points'] = $data['points'];
		$offer['description'] = $data['offer_description'];
		$offer['name'] = $data['offer_name'];
	}
	elseif ($data['offer_type'] == 'container')
	{
		$offer['id'] = $data['id'];
		$offer['type'] = $data['offer_type'];
		$offer['container_id'] = $data['itemid2'];
		$offer['container_count'] = $data['count2'];
		$offer['item_id'] = $data['itemid1'];
		$offer['item_count'] = $data['count1'];
		$offer['points'] = $data['points'];
		$offer['description'] = $data['offer_description'];
		$offer['name'] = $data['offer_name'];
	}
	elseif ($data['offer_type'] == 'unban')
	{
		$offer['id'] = $data['id'];
		$offer['type'] = $data['offer_type'];
		$offer['points'] = $data['points'];
		$offer['description'] = $data['offer_description'];
		$offer['name'] = $data['offer_name'];
	}
	elseif ($data['offer_type'] == 'redskull')
	{
		$offer['id'] = $data['id'];
		$offer['type'] = $data['offer_type'];
		$offer['points'] = $data['points'];
		$offer['description'] = $data['offer_description'];
		$offer['name'] = $data['offer_name'];
	}
	elseif ($data['offer_type'] == 'addon')
	{
		$offer['id'] = $data['id'];
		$offer['type'] = $data['offer_type'];
		$offer['look_female'] = $data['itemid1'];
		$offer['look_male'] = $data['itemid2'];
		$offer['addons_female'] = $data['count1'];
		$offer['addons_male'] = $data['count2'];
		$offer['points'] = $data['points'];
		$offer['description'] = $data['offer_description'];
		$offer['name'] = $data['offer_name'];
	}
	elseif ($data['offer_type'] == 'mount')
	{
		$offer['id'] = $data['id'];
		$offer['type'] = $data['offer_type'];
		$offer['mount_id'] = $data['itemid1'];
		$offer['points'] = $data['points'];
		$offer['description'] = $data['offer_description'];
		$offer['name'] = $data['offer_name'];
	}
	return $offer;
}

function getOfferArray()
{
	global $db;
	$offer_list = $db->query('SELECT * FROM '.$db->tableName('z_shop_offer').' WHERE hidden != 1;');
	$i_pacc = 0;
	$i_item = 0;
	$i_addonitem = 0;
	$i_container = 0;
	$i_decoration = 0;

	$i_unban = 0;
	$i_redskull = 0;
	$i_itemlogout = 0;
	$i_addon = 0;

	$offer_array = array();
	while($data = $offer_list->fetch())
	{
		if ($data['offer_type'] == 'pacc')
		{
			$offer_array['pacc'][$i_pacc]['id'] = $data['id'];
			$offer_array['pacc'][$i_pacc]['days'] = $data['count1'];
			$offer_array['pacc'][$i_pacc]['points'] = $data['points'];
			$offer_array['pacc'][$i_pacc]['description'] = $data['offer_description'];
			$offer_array['pacc'][$i_pacc]['name'] = $data['offer_name'];
			$i_pacc++;
		}
		elseif ($data['offer_type'] == 'item')
		{
			$offer_array['item'][$i_item]['id'] = $data['id'];
			$offer_array['item'][$i_item]['item_id'] = $data['itemid1'];
			$offer_array['item'][$i_item]['item_count'] = $data['count1'];
			$offer_array['item'][$i_item]['points'] = $data['points'];
			$offer_array['item'][$i_item]['description'] = $data['offer_description'];
			$offer_array['item'][$i_item]['name'] = $data['offer_name'];
			$i_item++;
		}
		elseif ($data['offer_type'] == 'container')
		{
			$offer_array['container'][$i_container]['id'] = $data['id'];
			$offer_array['container'][$i_container]['container_id'] = $data['itemid2'];
			$offer_array['container'][$i_container]['container_count'] = $data['count2'];
			$offer_array['container'][$i_container]['item_id'] = $data['itemid1'];
			$offer_array['container'][$i_container]['item_count'] = $data['count1'];
			$offer_array['container'][$i_container]['points'] = $data['points'];
			$offer_array['container'][$i_container]['description'] = $data['offer_description'];
			$offer_array['container'][$i_container]['name'] = $data['offer_name'];
			$i_container++;
		}
		elseif ($data['offer_type'] == 'unban')
		{
			$offer_array['unban'][$i_unban]['id'] = $data['id'];
			$offer_array['unban'][$i_unban]['points'] = $data['points'];
			$offer_array['unban'][$i_unban]['description'] = $data['offer_description'];
			$offer_array['unban'][$i_unban]['name'] = $data['offer_name'];
			$i_unban++;
		}
		elseif ($data['offer_type'] == 'redskull')
		{
			$offer_array['redskull'][$i_redskull]['id'] = $data['id'];
			$offer_array['redskull'][$i_redskull]['points'] = $data['points'];
			$offer_array['redskull'][$i_redskull]['description'] = $data['offer_description'];
			$offer_array['redskull'][$i_redskull]['name'] = $data['offer_name'];
			$i_redskull++;
		}
		elseif ($data['offer_type'] == 'itemlogout')
		{
			$offer_array['itemlogout'][$i_itemlogout]['id'] = $data['id'];
			$offer_array['itemlogout'][$i_itemlogout]['points'] = $data['points'];
			$offer_array['itemlogout'][$i_itemlogout]['description'] = $data['offer_description'];
			$offer_array['itemlogout'][$i_itemlogout]['name'] = $data['offer_name'];
			$offer_array['itemlogout'][$i_itemlogout]['count1'] = $data['count1'];
			$offer_array['itemlogout'][$i_itemlogout]['pid'] = $data['pid'];
			$offer_array['itemlogout'][$i_itemlogout]['item_id'] = $data['itemid1'];
			$offer_array['itemlogout'][$i_itemlogout]['free_cap'] = $data['free_cap'];
			$i_itemlogout++;
		}
		elseif ($data['offer_type'] == 'addon')
		{
			$offer_array['addon'][$i_addon]['id'] = $data['id'];
			$offer_array['addon'][$i_addon]['look_female'] = $data['itemid1'];
			$offer_array['addon'][$i_addon]['look_male'] = $data['itemid2'];
			$offer_array['addon'][$i_addon]['addons_female'] = $data['count1'];
			$offer_array['addon'][$i_addon]['addons_male'] = $data['count2'];
			$offer_array['addon'][$i_addon]['points'] = $data['points'];
			$offer_array['addon'][$i_addon]['description'] = $data['offer_description'];
			$offer_array['addon'][$i_addon]['name'] = $data['offer_name'];
			$i_addon++;
		}
		elseif ($data['offer_type'] == 'mount')
		{
			$offer_array['mount'][$i_addon]['id'] = $data['id'];
			$offer_array['mount'][$i_addon]['mount_id'] = $data['itemid1'];
			$offer_array['mount'][$i_addon]['points'] = $data['points'];
			$offer_array['mount'][$i_addon]['description'] = $data['offer_description'];
			$offer_array['mount'][$i_addon]['name'] = $data['offer_name'];
			$i_addon++;
		}
	}
	return $offer_array;
}

	if($action == 'admin_add') {
		if($canEdit) {
			$itemid1 = $count1 = $itemid2 = $count2 = 0;

			$points = isset($_REQUEST['points']) ? $_REQUEST['points'] : null;
			$offer_type = isset($_REQUEST['type']) ? strtolower($_REQUEST['type']) : null;
			$offer_name = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
			$offer_desc = isset($_REQUEST['description']) ? $_REQUEST['description'] : '';
			if(empty($points)) {
				error('Please fill all fields. Points is empty.');
			}
			else if(empty($offer_type)) {
				error('Please fill all fields. Type is empty.');
			}
			else if(empty($offer_name)) {
				error('Please fill all fields. Name is empty.');
			}
			else {
				$error = false;
				switch($offer_type) {
					case 'item':
						$itemid1 = isset($_REQUEST['item_id']) ? $_REQUEST['item_id'] : null;
						$count1 = isset($_REQUEST['item_count']) ? $_REQUEST['item_count'] : null;
						if(!$itemid1 || !is_numeric($itemid1)) {
							$error = 'Please fill all fields. Item ID is empty or its not a number.';
						}
						else if(!$count1 || !is_numeric($count1)) {
							$error = 'Please fill all fields. Item Count is empty or its not a number.';
						}

						break;

					case 'container':
						$itemid1 = isset($_REQUEST['item_id']) ? $_REQUEST['item_id'] : null;
						$count1 = isset($_REQUEST['item_count']) ? $_REQUEST['item_count'] : null;
						$itemid2 = isset($_REQUEST['container_id']) ? $_REQUEST['container_id'] : null;
						$count2 = isset($_REQUEST['container_count']) ? $_REQUEST['container_count'] : null;
						if(!$itemid1 || !is_numeric($itemid1)) {
							$error = 'Please fill all fields. Item ID is empty or its not a number.';
						}
						else if(!$count1 || !is_numeric($count1)) {
							$error = 'Please fill all fields. Item Count is empty or its not a number.';
						}
						else if(!$itemid2 || !is_numeric($itemid2)) {
							$error = 'Please fill all fields. Container ID is empty or its not a number.';
						}
						else if(!$count2 || !is_numeric($count2)) {
							$error = 'Please fill all fields. Container Count is empty or its not a number.';
						}

						break;
					case 'addon':
						$itemid1 = isset($_REQUEST['look_female']) ? $_REQUEST['look_female'] : null;
						$count1 = isset($_REQUEST['addons_female']) ? $_REQUEST['addons_female'] : null;
						$itemid2 = isset($_REQUEST['look_male']) ? $_REQUEST['look_male'] : null;
						$count2 = isset($_REQUEST['addons_male']) ? $_REQUEST['addons_male'] : null;

						if(!$itemid1 || !is_numeric($itemid1)) {
							$error = 'Please fill all fields. Look Female is empty or its not a number.';
						}
						else if(!$count1 || !is_numeric($count1)) {
							$error = 'Please fill all fields. Addons Female is empty or its not a number.';
						}
						else if(!$itemid2 || !is_numeric($itemid2)) {
							$error = 'Please fill all fields. Look Male is empty or its not a number.';
						}
						else if(!$count2 || !is_numeric($count2)) {
							$error = 'Please fill all fields. Addons Male is empty or its not a number.';
						}

						break;

					case 'mount':
						$itemid1 = isset($_REQUEST['mount_id']) ? $_REQUEST['mount_id'] : null;

						if(!$itemid1 || !is_numeric($itemid1)) {
							$error = 'Please fill all fields. Mount ID is empty or its not a number.';
						}

						break;

					case 'pacc':
						$count1 = isset($_REQUEST['days']) ? $_REQUEST['days'] : null;

						if(!$count1 || !is_numeric($count1)) {
							$error = 'Please fill all fields. Premium Days is empty or its not a number.';
						}

						break;

					default:
						$error = 'Unsupported offer type.';
				}

				if($error === false) {
					$db->insert('z_shop_offer', array(
						'points' => $points,
						'itemid1' => $itemid1, 'count1' => $count1,
						'itemid2' => $itemid2, 'count2' => $count2,
						'offer_type' => $offer_type,
						'offer_name' => $offer_name, 'offer_description' => $offer_desc,
					));

					$was_before = $config['friendly_urls'];
					$config['friendly_urls'] = true;
					success('Your offer has been saved! Go ' . generateLink(getLink('?subtopic=gifts'), 'BACK and add new one!'));
					$config['friendly_urls'] = $was_before;
				}
				else {
					error($error);
				}
			}
		}

		return;
	}
	else if($action == '') {
		if($canEdit) {
			success("You're logged as admin, so you can add new offers.<br/>
			Please fill all fields.<br/>
			Use phpmyadmin to edit or remove offers.");
			echo $twig->render('gesior-shop-system/admin-add.html.twig');
		}

		unset($_SESSION['viewed_confirmation_page']);
		echo '<div style="float:right;"><b><font color="green">Your premium points: </font></b>' . $user_premium_points . '</div><br/><br/>';
		echo '<center>';

		$offer_types = array();
		$tmp_query = $db->query('SELECT `name`, `description` FROM `' . 'z_shop_categories` WHERE `hidden` != 1')->fetchAll();
		foreach($tmp_query as $tmp_res)
			$offer_types[$tmp_res['name']] = $tmp_res['description'];

		$get_offerType = isset($_GET['offertype']) ? $_GET['offertype'] : 'item';
		foreach($offer_types as $offer_type => $offer_name)
		{
			echo '<a href="?subtopic=gifts&offertype='.$offer_type.'" style="padding: 5px 5px 1px 5px; margin: 5px 1px 0px 1px; t; background-color: '.($get_offerType == $offer_type ? '#505050' : '#303030').' ; color: #FFFFFF;">'.$offer_name.'</a>';
		}

		if(!isset($offer_types[$get_offerType])) {
			warning("This offer type doesn't exist. Please specify different name.");
			return;
		}

		$offer_list = getOfferArray();

		echo '<table border="0" cellpadding="1" cellspacing="1" width="550">
			<tr width="650" bgcolor="'.$config['vdarkborder'].'">
				<td colspan="4"></td>
			</tr>
			<tr bgcolor="'.$config['darkborder'].'">
				<td width="50" align="center"><b>Picture</b></td>
				<td width="350" align="left"><b>Description</b></td>
				<td width="50" align="center"><b>Points</b></td>
				<td width="100" align="center"><b>Select product</b></td>
			</tr>';
		//show list of pacc offers

		if($get_offerType == 'other')
		{
			//Remove Red Skull
			if(isset($offer_list['redskull']) && count($offer_list['redskull']) > 0)
			{
				foreach($offer_list['redskull'] as $redskull)
				{
					echo '<tr bgcolor="'.$config['lightborder'].'"><td align="center">--</td><td><b>'.$redskull['name'].'</b> ('.$redskull['points'].' points)<br />'.$redskull['description'].'</td><td align="center">';
					if(!$logged)
						echo '<b>Login to buy</b>';
					else
					echo '<form action="?subtopic=gifts&action=select_player" method=POST><input type="hidden" name="buy_id" value="'.$redskull['id'].'"><input type="submit" value="Buy"><br><b>for '.$redskull['points'].' points</b></form>';
					echo '</td></tr>';
				}
			}
			//Unban
			if(isset($offer_list['unban']) && count($offer_list['unban']) > 0)
			{
				foreach($offer_list['unban'] as $unban)
				{
					echo '
					<tr bgcolor="'.$config['lightborder'].'">
						<td align="center">--</td>
						<td><b>'.$unban['name'].'</b><br />'.$unban['description'].'</td>
						<td>' . $unban['points'] . '</td>
						<td align="center">';
					if(!$logged)
						echo '<b>Login to buy</b>';
					else
					echo '<form action="?subtopic=gifts&action=select_player" method=POST><input type="hidden" name="buy_id" value="'.$unban['id'].'"><input type="submit" value="Buy"><br></form>';
					echo '</td>
					</tr>';
				}
			}
			//Logout item
			if(isset($offer_list['itemlogout']) && count($offer_list['itemlogout']) > 0)
			{
				echo '<table border="0" cellpadding="1" cellspacing="1" width="650"><tr width="650" bgcolor="#505050"><td colspan="3"><font color="white" size="4"><b>&nbsp;Receive Item on Logout</b></font></td></tr><tr bgcolor="#D4C0A1"><td width="50" align="center"><b>Picture</b></font></td><td width="350" align="left"><b>Description</b></font></td><td width="250" align="center"><b>Select product</b></font></td></tr>';
				foreach($offer_list['itemlogout'] as $itemlogout) {
					echo '<tr bgcolor="'.$config['lightborder'].'"><td align="center"><img src="item_images/'.$itemlogout['id'].'.jpg"></td><td><b>'.$itemlogout['name'].'</b> ('.$itemlogout['points'].' points)<br />'.$itemlogout['description'].'</td><td align="center">';
					if(!$logged) echo '<b>Login to buy</b>'; else
					echo '<form action="?subtopic=gifts&action=select_player" method=POST><input type="hidden" name="buy_id" value="'.$itemlogout['id'].'"><input type="submit" value="Buy"><br><b>for '.$itemlogout['points'].' points</b></form>';
					echo '</td></tr>';
				}
				echo '</table><br />';
			}
		}

		if($get_offerType == 'pacc' && isset($offer_list['pacc']) && count($offer_list['pacc']) > 0)
		{
			foreach($offer_list['pacc'] as $pacc)
			{
				echo '<tr bgcolor="'.$config['lightborder'].'"><td align="center"><img src="images/shop-system/PremiumTime.png" alt="Premium Time"/></td><td><b>'.$pacc['name'].'</b><br />'.$pacc['description'].'</td><td align="center">'.$pacc['points'].'</td><td align="center">';
				if(!$logged)
					echo '<b>Login to buy</b>';
				else
				{
					echo '<form action="?subtopic=gifts&action=select_player" method=POST><input type="hidden" name="buy_id" value="'.$pacc['id'].'"><input type="submit" value="Buy"><br><b>for '.$pacc['points'].' points</b></form>';
				}
			}
		}
		//show list of items offers
		else if($get_offerType == 'item' && isset($offer_list['item']) && count($offer_list['item']) > 0)
		{
			foreach($offer_list['item'] as $item)
			{
				echo '<tr bgcolor="'.$config['lightborder'].'"><td align="center">'.getItemImage($item['item_id']).'</td><td><b>'.$item['name'].'</b><br/>'.$item['description'].'</td><td align="center">'.$item['points'].'</td><td align="center">';
				if(!$logged)
				{
					echo '<b>Login to buy</b>';
				}
				else
				{
					echo '<form action="?subtopic=gifts&action=select_player" method=POST><input type="hidden" name="buy_id" value="'.$item['id'].'"><input type="submit" value="Buy"><br><b>for '.$item['points'].' points</b></form>';
				}
				echo '</td></tr>';
			}
		}
		//show list of containers offers
		else if($get_offerType == 'container' && isset($offer_list['container']) && count($offer_list['container']) > 0)
		{
			foreach($offer_list['container'] as $container)
			{
				echo '<tr bgcolor="'.$config['lightborder'].'"><td align="center">'.getItemImage($container['item_id']).getItemImage($container['container_id']).'</td><td><b>'.$container['name'].'</b><br />'.$container['description'].'</td><td align="center">'.$container['points'].'</td><td align="center">';
				if(!$logged)
				{
					echo '<b>Login to buy</b>';
				}
				else
				{
					echo '<form action="?subtopic=gifts&action=select_player" method=POST><input type="hidden" name="buy_id" value="'.$container['id'].'"><input type="submit" value="Buy"><br><b>for '.$container['points'].' points</b></form>';
				}
				echo '</td></tr>';
			}
		}
		else if($get_offerType == 'addon' && isset($offer_list['addon']) && count($offer_list['addon']) > 0)
		{
			foreach($offer_list['addon'] as $addon)
			{
				echo '<tr bgcolor="'.$config['lightborder'].'">
					<td align="center">
						<table>
							<tr>
								<td>
									<img style="margin-left:-30px;margin-top:-30px;width:64px;height:64px;" src="'.$config['outfit_images_url'] . '?id=' . $addon['look_male'] . '&addons=' . $addon['addons_male'] . '&head=' . $config['shop_outfit_colors']['head'] . '&body=' . $config['shop_outfit_colors']['body'] . '&legs=' . $config['shop_outfit_colors']['legs'] . '&feet=' . $config['shop_outfit_colors']['feet'] . '"/>
								</td>
								<td>
									<img style="margin-left:-30px;margin-top:-30px;width:64px;height:64px;" src="'.$config['outfit_images_url'] . '?id=' . $addon['look_female'] . '&addons=' . $addon['addons_female'] . '&head=' . $config['shop_outfit_colors']['head'] . '&body=' . $config['shop_outfit_colors']['body'] . '&legs=' . $config['shop_outfit_colors']['legs'] . '&feet=' . $config['shop_outfit_colors']['feet'] . '"/>
								</td>
							</tr>
						</table>
					</td>
					<td><b>'.$addon['name'].'</b><br />'.$addon['description'].'</td>
					<td align="center">'.$addon['points'].'</td>
					<td align="center">';
				if(!$logged)
				{
					echo '<b>Login to buy</b>';
				}
				else
				{
					echo '<form action="?subtopic=gifts&action=select_player" method=POST><input type="hidden" name="buy_id" value="'.$addon['id'].'"><input type="submit" value="Buy"><br><b>for '.$addon['points'].' points</b></form>';
				}
				echo '</td></tr>';
			}
		}
		else if($get_offerType == 'mount' && isset($offer_list['mount']) && count($offer_list['mount']) > 0)
		{
			$query = $db->query("SELECT `id` FROM `z_shop_offer` WHERE `offer_type` = 'mount' LIMIT 1;");
			if($query->rowCount() > 0) {
				$tmp = '';
				if($cache->enabled() && $cache->fetch('mounts', $tmp)) {
					$config['mounts'] = unserialize($tmp);
				}
				else {
					$mounts = new DOMDocument();
					$file = $config['data_path'] . 'XML/mounts.xml';

					if(file_exists($file)) {
						$mounts->load($file);

						if($mounts) {
							$config['mounts'] = array();
							foreach($mounts->getElementsByTagName('mount') as $mount) {
								$id = $mount->getAttribute('id');
								$config['mounts'][$id] = $mount->getAttribute('clientid');
							}

							if($cache->enabled()) {
								$cache->set('mounts', serialize($config['mounts']), 120);
							}
						}
					}
				}
			}

			foreach($offer_list['mount'] as $mount)
			{
				$mount_image = 'images/shop-system/mounts_category.png';
				if(isset($config['mounts']) && $config['mounts'][$mount['mount_id']]) {
					$mount_image = $config['outfit_images_url'] . '?id=' . $config['mounts'][$mount['mount_id']] . '&addons=0&head=' . $config['shop_outfit_colors']['head'] . '&body=' . $config['shop_outfit_colors']['body'] . '&legs=' . $config['shop_outfit_colors']['legs'] . '&feet=' . $config['shop_outfit_colors']['feet'];
					//$mount_image = BASE_URL . 'images/mounts/' . $mount['mount_id'] '.png';
				}

				echo '<tr bgcolor="'.$config['lightborder'].'">
					<td align="center">
						<img style="margin-left:-30px;margin-top:-30px;width:64px;height:64px;" src="'. $mount_image . '"/>
					</td>
					<td><b>'.$mount['name'].'</b><br />'.$mount['description'].'</td>
					<td align="center">'.$mount['points'].'</td>
					<td align="center">';
				if(!$logged)
				{
					echo '<b>Login to buy</b>';
				}
				else
				{
					echo '<form action="?subtopic=gifts&action=select_player" method=POST><input type="hidden" name="buy_id" value="'.$mount['id'].'"><input type="submit" value="Buy"><br><b>for '.$mount['points'].' points</b></form>';
				}
				echo '</td></tr>';
			}
		}

		echo '</table><br />';
	}
	elseif($action == 'select_player')
	{
		unset($_SESSION['viewed_confirmation_page']);
		if(!$logged)
			echo 'Please login first.';
		else
		{
			$buy_id = (int) $_REQUEST['buy_id'];
			if(empty($buy_id))
			{
				echo 'Please <a href="?subtopic=gifts">select item</a> first.';
			}
			else
			{
				$buy_offer = getItemByID($buy_id);
				if(isset($buy_offer['id']) && $buy_offer['hidden'] != '1') //item exist in database
				{
					if($user_premium_points >= $buy_offer['points'])
					{
						echo '<h2>Select player</h2>
						<table border="0" cellpadding="1" cellspacing="1" width="650">
						<tr bgcolor="'.$config['vdarkborder'].'"><td colspan="2"><font color="white" size="4"><b>Selected offer</b></font></td></tr>
						<tr bgcolor="'.$config['darkborder'].'"><td width="100"><b>Name:</b></td><td width="550">'.$buy_offer['name'].'</td></tr>
						<tr bgcolor="'.$config['darkborder'].'"><td width="100"><b>Description:</b></td><td width="550">'.$buy_offer['description'].'</td></tr>
						</table><br />
						<form action="?subtopic=gifts&action=confirm_transaction" method=POST><input type="hidden" name="buy_id" value="'.$buy_id.'">
						<table border="0" cellpadding="1" cellspacing="1" width="650">
						<tr bgcolor="'.$config['vdarkborder'].'"><td colspan="2"><font color="white" size="4"><b>Give item to player from your account</b></font></td></tr>
						<tr bgcolor="'.$config['darkborder'].'"><td width="110"><b>Name:</b></td><td width="550"><select name="buy_name">';
						$players_from_logged_acc = $account_logged->getPlayersList();
						if(count($players_from_logged_acc) > 0)
						{
							$players_from_logged_acc->orderBy('name');
							foreach($players_from_logged_acc as $player)
							{
								echo '<option>'.$player->getName().'</option>';
							}
						}
						else
						{
							echo 'You don\'t have any character on your account.';
						}
						echo '</select><input type="submit" value="Give"></td></tr>
						</table>
						</form><br /><form action="?subtopic=gifts&action=confirm_transaction" method=POST><input type="hidden" name="buy_id" value="'.$buy_id.'">
							<table border="0" cellpadding="1" cellspacing="1" width="650">
							<tr bgcolor="'.$config['vdarkborder'].'"><td colspan="2"><font color="white" size="4"><b>Give item to other player</b></font></td></tr>
							<tr bgcolor="'.$config['darkborder'].'"><td width="110"><b>To player:</b></td><td width="550"><input type="text" name="buy_name"> - name of player</td></tr>
							<tr bgcolor="'.$config['darkborder'].'"><td width="110"><b>From:</b></td><td width="550"><input type="text" name="buy_from">Â <input type="submit" value="Give"> - your nick, \'empty\' = Anonymous</td></tr>
							</table><br />
							</form>';

						echo '*PACC is for all characters from account of selected player name';
					}
					else
					{
						echo 'For this item you need <b>'.$buy_offer['points'].'</b> points. You have only <b>'.$user_premium_points.'</b> premium points. Please <a href="?subtopic=gifts">select other item</a> or buy premium points.';
					}
				}
				else
				{
					echo 'Offer with ID <b>'.$buy_id.'</b> doesn\'t exist. Please <a href="?subtopic=gifts">select item</a> again.';
				}
			}
		}
	}
	elseif($action == 'confirm_transaction')
	{
		$set_session = false;

		if(!$logged) {
			echo 'Please login first.';
		}
		else
		{
			$buy_id = (int) $_POST['buy_id'];
			$buy_name = stripslashes(urldecode($_POST['buy_name']));
			$buy_from = isset($_POST['buy_from']) ? stripslashes(urldecode($_POST['buy_from'])) : '';
			if(empty($buy_from))
			{
				$buy_from = 'Anonymous';
			}
			if(empty($buy_id))
			{
				echo 'Please <a href="?subtopic=gifts">select item</a> first.';
			}
			else
			{
				if(!check_name($buy_from))
				{
					echo 'Invalid nick ("from player") format. Please <a href="?subtopic=gifts&action=select_player&buy_id='.$buy_id.'">select other name</a> or contact with administrator.';
				}
				else
				{
					$buy_offer = getItemByID($buy_id);
					if(isset($buy_offer['id']) && $buy_offer['hidden'] != '1') //item exist in database
					{
						if($user_premium_points >= $buy_offer['points'])
						{
							if(check_name($buy_name))
							{
								$buy_player = new OTS_Player();
								$buy_player->find($buy_name);
								if($buy_player->isLoaded())
								{
									$buy_player_account = $buy_player->getAccount();
									if(isset($_SESSION['viewed_confirmation_page']) && $_SESSION['viewed_confirmation_page'] == 'yes' && $_POST['buy_confirmed'] == 'yes')
									{
										if($buy_offer['type'] == 'pacc')
										{
											$is_othire = fieldExist('premend', 'accounts');
											if($is_othire && $buy_player->isOnline()) {
												$errors = array();
												$errors[] = 'Player with name <b>'.$buy_name.'</b> is online. Please logout. Then <a href="?subtopic=gifts&action=select_player&buy_id='.$buy_id.'">refresh this page</a>.';

												echo $twig->render('error_box.html.twig', array('errors' => $errors));
												return;
											}
											$save_transaction = 'INSERT INTO '.$db->tableName('z_shop_history').' (id, to_name, to_account, from_nick, from_account, price, offer_id, trans_state, trans_start, trans_real) VALUES (NULL, '.$db->quote($buy_player->getName()).', '.$db->quote($buy_player_account->getId()).', '.$db->quote($buy_from).',  '.$db->quote($account_logged->getId()).', '.$db->quote($buy_offer['points']).', '.$db->quote($buy_offer['id']).', \'realized\', '.$db->quote(time()).', '.$db->quote(time()).');';
											$db->query($save_transaction);

											if($is_othire) {
												$time = $buy_player_account->getCustomField('premend');
												if($time == 0) {
													$time = time();
												}

												$buy_player_account->setCustomField('premend', $time + $buy_offer['days'] * 86400);
											}
											else {// rest
												$buy_player_account->setCustomField('premdays', $buy_player_account->getCustomField('premdays') + $buy_offer['days']);

												if($buy_player_account->getCustomField('lastday') == 0) {
													$buy_player_account->setCustomField('lastday', time());
												}
											}

											$user_premium_points -= $buy_offer['points'];
											add_points($account_logged, -$buy_offer['points']);

											echo '<h2>PACC added!</h2><b>'.$buy_offer['days'].' days</b> of Premium Account added to account of player <b>'.$buy_player->getName().'</b> for <b>'.$buy_offer['points'].' premium points</b> from your account.<br />Now you have <b>'.$user_premium_points.' premium points</b>.<br /><a href="?subtopic=gifts">GO TO MAIN SHOP SITE</a>';
										}
										elseif($buy_offer['type'] == 'item')
										{
											$query = 'INSERT INTO '.$db->tableName('z_ots_comunication').' (id, name, type, action, param1, param2, param3, param4, param5, param6, param7, delete_it) VALUES (NULL, '.$db->quote($buy_player->getName()).', \'login\', \'give_item\', '.$db->quote($buy_offer['item_id']).', '.$db->quote($buy_offer['item_count']).', 0, 0, \'item\', '.$db->quote($buy_offer['name']).', \'\', \'1\');';
											$db->query($query);
											$save_transaction = 'INSERT INTO '.$db->tableName('z_shop_history').' (id, comunication_id, to_name, to_account, from_nick, from_account, price, offer_id, trans_state, trans_start, trans_real) VALUES (NULL, '.$db->lastInsertId().', '.$db->quote($buy_player->getName()).', '.$db->quote($buy_player_account->getId()).', '.$db->quote($buy_from).',  '.$db->quote($account_logged->getId()).', '.$db->quote($buy_offer['points']).', '.$db->quote($buy_offer['id']).', \'wait\', '.$db->quote(time()).', \'0\');';
											$db->query($save_transaction);
											$user_premium_points -= $buy_offer['points'];
											add_points($account_logged, -$buy_offer['points']);
											echo '<h2>You have bought this item!</h2><b>'.$buy_offer['name'].'</b> added to player <b>'.$buy_player->getName().'</b> for <b>'.$buy_offer['points'].' premium points</b> from your account.<br/>Now patiently wait in game MAX 3 minutes to receive this item!<br />Now you have <b>'.$user_premium_points.' premium points</b>.<br /><a href="?subtopic=gifts">GO TO MAIN SHOP SITE</a>';
										}
										elseif($buy_offer['type'] == 'addon')
										{
											$query = 'INSERT INTO '.$db->tableName('z_ots_comunication').' (id, name, type, action, param1, param2, param3, param4, param5, param6, param7, delete_it) VALUES (NULL, '.$db->quote($buy_player->getName()).', \'login\', \'give_item\', '.$db->quote($buy_offer['look_female']).', '.$db->quote($buy_offer['look_male']).', '.$db->quote($buy_offer['addons_female']).', '.$db->quote($buy_offer['addons_male']).', \'addon\', '.$db->quote($buy_offer['name']).', \'\', \'1\');';
											$db->query($query);
											$save_transaction = 'INSERT INTO '.$db->tableName('z_shop_history').' (id, comunication_id, to_name, to_account, from_nick, from_account, price, offer_id, trans_state, trans_start, trans_real) VALUES (NULL, '.$db->lastInsertId().', '.$db->quote($buy_player->getName()).', '.$db->quote($buy_player_account->getId()).', '.$db->quote($buy_from).',  '.$db->quote($account_logged->getId()).', '.$db->quote($buy_offer['points']).', '.$db->quote($buy_offer['id']).', \'wait\', '.$db->quote(time()).', \'0\');';
											$db->query($save_transaction);
											$user_premium_points -= $buy_offer['points'];
											add_points($account_logged, -$buy_offer['points']);
											echo '<h2>You have bought this addon!</h2><b>'.$buy_offer['name'].'</b> added to player <b>'.$buy_player->getName().'</b> for <b>'.$buy_offer['points'].' premium points</b> from your account.<br/>Now patiently wait in game MAX 3 minutes to receive this addon!<br />Now you have <b>'.$user_premium_points.' premium points</b>.<br /><a href="?subtopic=gifts">GO TO MAIN SHOP SITE</a>';
										}
										elseif($buy_offer['type'] == 'mount')
										{
											$query = 'INSERT INTO '.$db->tableName('z_ots_comunication').' (id, name, type, action, param1, param2, param3, param4, param5, param6, param7, delete_it) VALUES (NULL, '.$db->quote($buy_player->getName()).', \'login\', \'give_item\', '.$db->quote($buy_offer['mount_id']).', 0, 0, 0, \'mount\', '.$db->quote($buy_offer['name']).', \'\', \'1\');';
											$db->query($query);
											$save_transaction = 'INSERT INTO '.$db->tableName('z_shop_history').' (id, comunication_id, to_name, to_account, from_nick, from_account, price, offer_id, trans_state, trans_start, trans_real) VALUES (NULL, '.$db->lastInsertId().', '.$db->quote($buy_player->getName()).', '.$db->quote($buy_player_account->getId()).', '.$db->quote($buy_from).',  '.$db->quote($account_logged->getId()).', '.$db->quote($buy_offer['points']).', '.$db->quote($buy_offer['id']).', \'wait\', '.$db->quote(time()).', \'0\');';
											$db->query($save_transaction);
											$user_premium_points -= $buy_offer['points'];
											add_points($account_logged, -$buy_offer['points']);
											echo '<h2>You have bought this mount!</h2><b>'.$buy_offer['name'].'</b> added to player <b>'.$buy_player->getName().'</b> for <b>'.$buy_offer['points'].' premium points</b> from your account.<br/>Now patiently wait in game MAX 3 minutes to receive this addon!<br />Now you have <b>'.$user_premium_points.' premium points</b>.<br /><a href="?subtopic=gifts">GO TO MAIN SHOP SITE</a>';
										}
										elseif($buy_offer['type'] == 'container')
										{
											$query = 'INSERT INTO '.$db->tableName('z_ots_comunication').' (id, name, type, action, param1, param2, param3, param4, param5, param6, param7, delete_it) VALUES (NULL, '.$db->quote($buy_player->getName()).', \'login\', \'give_item\', '.$db->quote($buy_offer['item_id']).', '.$db->quote($buy_offer['item_count']).', '.$db->quote($buy_offer['container_id']).', '.$db->quote($buy_offer['container_count']).', \'container\', '.$db->quote($buy_offer['name']).', \'\', \'1\');';
											$db->query($query);
											$save_transaction = 'INSERT INTO '.$db->tableName('z_shop_history').' (id, comunication_id, to_name, to_account, from_nick, from_account, price, offer_id, trans_state, trans_start, trans_real) VALUES (NULL, '.$db->lastInsertId().', '.$db->quote($buy_player->getName()).', '.$db->quote($buy_player_account->getId()).', '.$db->quote($buy_from).',  '.$db->quote($account_logged->getId()).', '.$db->quote($buy_offer['points']).', '.$db->quote($buy_offer['id']).', \'wait\', '.$db->quote(time()).', \'0\');';
											$db->query($save_transaction);
											$user_premium_points -= $buy_offer['points'];
											add_points($account_logged, -$buy_offer['points']);
											echo '<h2>You have bought this container of items!</h2><b>'.$buy_offer['name'].'</b> added to player <b>'.$buy_player->getName().'</b> for <b>'.$buy_offer['points'].' premium points</b> from your account.<br/>Now patiently wait in game MAX 3 minutes to receive this item!<br />Now you have <b>'.$user_premium_points.' premium points</b>.<br /><a href="?subtopic=gifts">GO TO MAIN SHOP SITE</a>';
										}
									}
									else
									{
										$set_session = true;
										$_SESSION['viewed_confirmation_page'] = 'yes';
										echo '<h2>Confirm transaction</h2>
										<table border="0" cellpadding="1" cellspacing="1" width="650">
										<tr bgcolor="'.$config['vdarkborder'].'"><td colspan="3"><font color="white" size="4"><b>Confirm transaction</b></font></td></tr>
										<tr bgcolor="'.$config['darkborder'].'"><td width="100"><b>Name:</b></td><td width="550" colspan="2">'.$buy_offer['name'].'</td></tr>
										<tr bgcolor="'.$config['darkborder'].'"><td width="100"><b>Description:</b></td><td width="550" colspan="2">'.$buy_offer['description'].'</td></tr>
										<tr bgcolor="'.$config['darkborder'].'"><td width="100"><b>Cost:</b></td><td width="550" colspan="2"><b>'.$buy_offer['points'].' premium points</b> from your account</td></tr>
										<tr bgcolor="'.$config['darkborder'].'"><td width="100"><b>For Player:</b></td><td width="550" colspan="2"><font color="red">'.$buy_player->getName().'</font></td></tr>
										<tr bgcolor="'.$config['darkborder'].'"><td width="100"><b>From:</b></td><td width="550" colspan="2"><font color="red">'.$buy_from.'</font></td></tr>
										<tr bgcolor="red"><td width="100"><b>Transaction?</b></td><td width="275" align="left">
										<form action="?subtopic=gifts&action=confirm_transaction" method="POST"><input type="hidden" name="buy_confirmed" value="yes"><input type="hidden" name="buy_id" value="'.$buy_id.'"><input type="hidden" name="buy_from" value="'.urlencode($buy_from).'"><input type="hidden" name="buy_name" value="'.urlencode($buy_name).'"><input type="submit" value="Accept"></form></td>
										<td align="right"><form action="?subtopic=gifts" method="POST"><input type="submit" value="Cancel"></form></td></tr>
										</table>
										';
									}
								}
								else
								{
									echo 'Player with name <b>'.$buy_name.'</b> doesn\'t exist. Please <a href="?subtopic=gifts&action=select_player&buy_id='.$buy_id.'">select other name</a>.';
								}
							}
							else
							{
								echo 'Invalid name format. Please <a href="?subtopic=gifts&action=select_player&buy_id='.$buy_id.'">select other name</a> or contact with administrator.';
							}
						}
						else
						{
							echo 'For this item you need <b>'.$buy_offer['points'].'</b> points. You have only <b>'.$user_premium_points.'</b> premium points. Please <a href="?subtopic=gifts">select other item</a> or buy premium points.';
						}
					}
					else {
						echo 'Offer with ID <b>'.$buy_id.'</b> doesn\'t exist. Please <a href="?subtopic=gifts">select item</a> again.';
					}
				}
			}
		}
		if(!$set_session) {
			unset($_SESSION['viewed_confirmation_page']);
		}
	}
	elseif($action == 'show_history')
	{
		if(!$logged) {
			echo 'Please login first.';
		}
		else
		{
			$items_history_received = $db->query('SELECT * FROM '.$db->tableName('z_shop_history').' WHERE `is_pacc` = 0 AND ('.$db->fieldName('to_account').' = '.$db->quote($account_logged->getId()).' OR '.$db->fieldName('from_account').' = '.$db->quote($account_logged->getId()).');');
			if(is_object($items_history_received)) {
				foreach($items_history_received as $item_received)
				{
					if($account_logged->getId() == $item_received['to_account'])
						$char_color = 'green';
					else
						$char_color = 'red';

					$items_received_text = '<tr bgcolor="'.$config['lightborder'].'"><td><font color="'.$char_color.'">'.$item_received['to_name'].'</font></td><td>';
					if($account_logged->getId() == $item_received['from_account'])
						$items_received_text .= '<i>Your account</i>';
					else
						$items_received_text .= $item_received['from_nick'];

					$tmp_item_name = getItemByID($item_received['offer_id']);
					$items_received_text .= '</td><td>'.$tmp_item_name['name'].'</td><td>'.date("j F Y, H:i:s", $item_received['trans_start']).'</td>';
					if($item_received['trans_real'] > 0)
						$items_received_text .= '<td>'.date("j F Y, H:i:s", $item_received['trans_real']).'</td>';
					else
						$items_received_text .= '<td><b><font color="red">Not realized yet.</font></b></td>';
					$items_received_text .= '</tr>';
				}
			}
			$paccs_history_received = $db->query('SELECT * FROM '.$db->tableName('z_shop_history').' WHERE `is_pacc` = 1 AND ('.$db->fieldName('to_account').' = '.$db->quote($account_logged->getId()).' OR '.$db->fieldName('from_account').' = '.$db->quote($account_logged->getId()).');');
			if(is_object($paccs_history_received)) {
				foreach($paccs_history_received as $pacc_received)
				{
					if($account_logged->getId() == $pacc_received['to_account'])
						$char_color = 'green';
					else
						$char_color = 'red';
					$paccs_received_text = '<tr bgcolor="'.$config['lightborder'].'"><td><font color="'.$char_color.'">'.$pacc_received['to_name'].'</font></td><td>';
					if($account_logged->getId() == $pacc_received['from_account'])
						$paccs_received_text .= '<i>Your account</i>';
					else
						$paccs_received_text .= $pacc_received['from_nick'];

					$tmp_pacc = getItemByID($pacc_received['offer_id']);
					$paccs_received_text .= '</td><td>'.$tmp_pacc['days'].' days</td><td>'.$pacc_received['price'].' Points</td><td>'.date("j F Y, H:i:s", $pacc_received['trans_real']).'</td></tr>';
				}
			}
			echo '<center><h1>Transactions History</h1></center>';
			if(!empty($items_received_text))
			{
				echo '<h2>Item transactions</h2><table BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=100%><tr bgcolor="'.$config['darkborder'].'"><td><b>To:</b></td><td><b>From:</b></td><td><b>Offer name</b></td><td><b>Bought on page</b></td><td><b>Received on OTS</b></td></tr>'.$items_received_text.'</table><br />';
			}
			if(!empty($paccs_received_text))
			{
				echo '<h2>PACC transactions</h2><table BORDER=0 CELLPADDING=1 CELLSPACING=1 WIDTH=100%><tr bgcolor="'.$config['darkborder'].'"><td><b>To:</b></td><td><b>From:</b></td><td><b>Duration</b></td><td><b>Cost</b></td><td><b>Added:</b></td></tr>'.$paccs_received_text.'</table><br />';
			}
			if(empty($paccs_received_text) && empty($items_received_text))
				echo 'You did not buy/receive any item or PACC.';
		}
	}
	echo '<br><br><b><font color="green">Your premium points: </font></b>'.$user_premium_points.'</br>';
?>
