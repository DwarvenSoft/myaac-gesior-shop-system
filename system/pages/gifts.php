<?php
if(!defined('INITIALIZED'))
	exit;

$config['site']['item_images_url'] = 'http://item-images.ots.me/960/';
$config['site']['item_images_extension'] = '.gif';

$errormessage = '';
if($config['site']['shop_system'])
{
	if($logged)
	{
		$user_premium_points = $account_logged->getCustomField('premium_points');
	}
	else
	{
		$user_premium_points = 'Login first';
	}
	function getItemByID($id)
	{
		global $db;
		$id = (int) $id;
		$data = $db->query('SELECT * FROM '.$db->tableName('z_shop_offer').' WHERE '.$db->fieldName('id').' = '.$db->quote($id).';')->fetch();
		if($data['offer_type'] == 'item')
		{
			$offer['id'] = $data['id'];
			$offer['type'] = $data['offer_type'];
			$offer['item_id'] = $data['itemid1'];
			$offer['item_count'] = $data['count1'];
			$offer['points'] = $data['points'];
			$offer['description'] = $data['offer_description'];
			$offer['name'] = $data['offer_name'];
		}
		elseif($data['offer_type'] == 'container')
		{
			$offer['id'] = $data['id'];
			$offer['type'] = $data['offer_type'];
			$offer['container_id'] = $data['itemid1'];
			$offer['container_count'] = $data['count1'];
			$offer['item_id'] = $data['itemid2'];
			$offer['item_count'] = $data['count2'];
			$offer['points'] = $data['points'];
			$offer['description'] = $data['offer_description'];
			$offer['name'] = $data['offer_name'];
		}
		return $offer;
	}

	function getOfferArray()
	{
		global $db;

		$offer_list = $db->query('SELECT * FROM '.$db->tableName('z_shop_offer').';');
		$i_item = 0;
		$i_container = 0;
		$offer_array = array();
		$offer_array['item'] = array();
		$offer_array['container'] = array();
		while($data = $offer_list->fetch())
		{
			if($data['offer_type'] == 'item')
			{
				$offer_array['item'][$i_item]['id'] = $data['id'];
				$offer_array['item'][$i_item]['item_id'] = $data['itemid1'];
				$offer_array['item'][$i_item]['item_count'] = $data['count1'];
				$offer_array['item'][$i_item]['points'] = $data['points'];
				$offer_array['item'][$i_item]['description'] = $data['offer_description'];
				$offer_array['item'][$i_item]['name'] = $data['offer_name'];
				$i_item++;
			}
			elseif($data['offer_type'] == 'container')
			{
				$offer_array['container'][$i_container]['id'] = $data['id'];
				$offer_array['container'][$i_container]['container_id'] = $data['itemid1'];
				$offer_array['container'][$i_container]['container_count'] = $data['count1'];
				$offer_array['container'][$i_container]['item_id'] = $data['itemid2'];
				$offer_array['container'][$i_container]['item_count'] = $data['count2'];
				$offer_array['container'][$i_container]['points'] = $data['points'];
				$offer_array['container'][$i_container]['description'] = $data['offer_description'];
				$offer_array['container'][$i_container]['name'] = $data['offer_name'];
				$i_container++;
			}
		}
		return $offer_array;
	}
	if(($action == '') or ($action == 'item') or ($action == 'container'))
	{
		unset($_SESSION['viewed_confirmation_page']);
		$offer_list = getOfferArray();

		if(empty($action))
		{
			if(isset($offer_list['item']) && count($offer_list['item']) > 0)
				$action = 'item';
			elseif(isset($offer_list['container']) && count($offer_list['container']) > 0)
				$action = 'container';
		}

		function selectcolor($value)
		{
			if($GLOBALS['action'] == $value)
				return '#505050; color: #FFFFFF';
			else
				return '#303030; color: #aaaaaa';
		}

		if((count($offer_list['item']) > 0) or (count($offer_list['container']) > 0))
		{
			echo '<TABLE WIDTH=100% BORDER=0 CELLSPACING=0 CELLPADDING=4><TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=left CLASS=white colspan="2"><B>Choose a categorie: </B>';
			if(isset($offer_list['item']) && count($offer_list['item']) > 0) echo '<a href="?subtopic=gifts&action=item" style="padding: 5px 5px 7px 5px; margin: 5px 1px 0px 1px; background-color: '.selectcolor('item').';">ITEMS</a>';
			if(isset($offer_list['container']) && count($offer_list['container']) > 0) echo '<a href="?subtopic=gifts&action=container" style="padding: 5px 5px 7px 5px; margin: 5px 1px 0px 1px; background-color: '.selectcolor('container').';">CONTAINERS</a>';
			echo '</TD></TR></TD></TR></table><table BORDER=0 CELLPaDDING="4" CELLSPaCING="1" style="width:100%;font-weight:bold;text-align:center;"><tr style="background:#505050;"><td colspan="3" style="height:px;"></td></tr></table>';
		}

		//show list of items offers
		if((count($offer_list['item']) > 0) and ($action == 'item'))
		{
			echo '<table border="0" cellpadding="4" cellspacing="1" width="100%"><tr bgcolor="'.$config['site']['vdarkborder'].'"><td width="8%" align="center" class="white"><b>Points</b></td><td width="9%" align="center" class="white"><b>Picture</b></td><td width="350" align="left" class="white"><b>Description</b></td><td width="250" align="center" class="white"><b>Select product</b></td></tr>';
			$number_of_rows = 0;
			foreach($offer_list['item'] as $item)
			{
				if(!is_int($number_of_rows / 2)) { $bgcolor = $config['site']['darkborder']; } else { $bgcolor = $config['site']['lightborder']; } $number_of_rows++;
				echo '<tr bgcolor="'.$bgcolor.'"><td align="center"><b>'.$item['points'].'</b></td><td align="center"><img src="' . $config['site']['item_images_url'] . $item['item_id'] . $config['site']['item_images_extension'] . '"></td><td><b>'.htmlspecialchars($item['name']).'</b> ('.$item['points'].' points)<br />'.htmlspecialchars($item['description']).'</td><td align="center">';
				if(!$logged)
				{
					echo '<b>Login to buy</b>';
				}
				else
				{
					echo '<form action="?subtopic=gifts&action=select_player" method="POST" name="itemform_'.$item['id'].'"><input type="hidden" name="buy_id" value="'.$item['id'].'"><div class="navibutton"><a href="" onClick="itemform_'.$item['id'].'.submit();return false;">BUY</a></div></form>';
				}
				echo '</td></tr>';
			}
			echo '</table>';
		}
		//show list of containers offers
		if(isset($offer_list['container']) && (count($offer_list['container']) > 0) and ($action == 'container'))
		{
			if(!is_int($number_of_rows / 2)) { $bgcolor = $config['site']['darkborder']; } else { $bgcolor = $config['site']['lightborder']; } $number_of_rows++;
			echo '<table border="0" cellpadding="4" cellspacing="1" width="100%"><tr bgcolor="'.$config['site']['vdarkborder'].'"><td width="8%" align="center" class="white"><b>Points</b></td><td width="9%" align="center" class="white"><b>Picture</b></td><td width="350" align="left" class="white"><b>Description</b></td><td width="250" align="center" class="white"><b>Select product</b></td></tr>';
			foreach($offer_list['container'] as $container)
			{
				echo '<tr bgcolor="'.$bgcolor.'"><td align="center"><b>'.$container['points'].'</b></td><td align="center"><img src="' . $config['site']['item_images_url'] . $container['item_id'] . $config['site']['item_images_extension'] . '"></td><td><b>'.htmlspecialchars($container['name']).'</b> ('.$container['points'].' points)<br />'.htmlspecialchars($container['description']).'</td><td align="center">';
				if(!$logged)
				{
					echo '<b>Login to buy</b>';
				}
				else
				{
					echo '<form action="?subtopic=gifts&action=select_player" method="POST" name="contform_'.$container['id'].'"><input type="hidden" name="buy_id" value="'.$container['id'].'"><div class="navibutton"><a href="" onClick="contform_'.$container['id'].'.submit();return false;">BUY</a></div></form>';
				}
				echo '</td></tr>';
			}
			echo '</table>';
		}

		if((count($offer_list['item']) > 0) or (count($offer_list['container']) > 0))
		{
			echo '<table BORDER=0 CELLPaDDING="4" CELLSPaCING="1" style="width:100%;font-weight:bold;text-align:center;">
			<tr style="background:#505050;">
					<td colspan="3" style="height:px;"></td>
			</tr>
			</table>';
		}
	}
	if($action == 'select_player')
	{
		unset($_SESSION['viewed_confirmation_page']);
		if(!$logged) {
			$errormessage .= 'Please login first.';
		}
		else
		{
			$buy_id = (int) $_REQUEST['buy_id'];
			if(empty($buy_id))
			{
				$errormessage .= 'Please <a href="?subtopic=gifts">select item</a> first.';
			}
			else
			{
				$buy_offer = getItemByID($buy_id);
				if(isset($buy_offer['id'])) //item exist in database
				{
					if($user_premium_points >= $buy_offer['points'])
					{
						echo '<table border="0" cellpadding="4" cellspacing="1" width="100%">
						<tr bgcolor="'.$config['site']['vdarkborder'].'"><td colspan="2" class="white"><b>Selected Offer</b></td></tr>
						<tr bgcolor="'.$config['site']['lightborder'].'"><td width="100"><b>Name:</b></td><td width="550">'.htmlspecialchars($buy_offer['name']).'</td></tr>
						<tr bgcolor="'.$config['site']['darkborder'].'"><td width="100"><b>Description:</b></td><td width="550">'.htmlspecialchars($buy_offer['description']).'</td></tr>
						</table><br />
						<form action="?subtopic=gifts&action=confirm_transaction" method="POST"><input type="hidden" name="buy_id" value="'.$buy_id.'">
						<table border="0" cellpadding="4" cellspacing="1" width="100%">
						<tr bgcolor="'.$config['site']['vdarkborder'].'"><td colspan="2" class="white"><b>Give item to player from your account</b></td></tr>
						<tr bgcolor="'.$config['site']['lightborder'].'"><td width="110"><b>Name:</b></td><td width="550"><select name="buy_name">';
						$players_from_logged_acc = $account_logged->getPlayersList();
						if(count($players_from_logged_acc) > 0)
						{
							foreach($players_from_logged_acc as $player)
							{
								echo '<option>'.htmlspecialchars($player->getName()).'</option>';
							}
						}
						else
						{
							echo 'You don\'t have any character on your account.';
						}
						echo '</select>&nbsp;<input type="submit" value="Give"></td></tr>
						</table>
						</form><br /><form action="?subtopic=gifts&action=confirm_transaction" method="POST"><input type="hidden" name="buy_id" value="'.$buy_id.'">
							<table border="0" cellpadding="4" cellspacing="1" width="100%">
							<tr bgcolor="'.$config['site']['vdarkborder'].'"><td colspan="2" class="white"><b>Give item to other player</b></td></tr>
							<tr bgcolor="'.$config['site']['lightborder'].'"><td width="110"><b>To player:</b></td><td width="550"><input type="text" name="buy_name"> - name of player</td></tr>
							<tr bgcolor="'.$config['site']['darkborder'].'"><td width="110"><b>From:</b></td><td width="550"><input type="text" name="buy_from">&nbsp;<input type="submit" value="Give"> - your nick, \'empty\' = Anonymous</td></tr>
							</table><br />
							</form>';

					}
					else
					{
						$errormessage .= 'For this item you need <b>'.$buy_offer['points'].'</b> points. You have only <b>'.$user_premium_points.'</b> premium points. Please <a href="?subtopic=gifts">select other item</a> or buy premium points.';
					}
				}
				else
				{
					$errormessage .= 'Offer with ID <b>'.$buy_id.'</b> doesn\'t exist. Please <a href="?subtopic=gifts">select item</a> again.';
				}
			}
		}
		if(!empty($errormessage))
		{
			echo '<TABLE WIDTH=100% BORDER=0 CELLSPACING=1 CELLPADDING=4>
				<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=left CLASS=white><B>Informations</B></TD></TR>
				<TR><TD BGCOLOR="'.$config['site']['lightborder'].'" ALIGN=left><b>'.$errormessage.'</b></TD></TR>
				</table>';
		}
	}
	elseif($action == 'confirm_transaction')
	{
		$set_session = false;
		if(!$logged)
		{
			$errormessage .= 'Please login first.';
		}
		else
		{
			$buy_id = isset($_POST['buy_id']) ? (int) $_POST['buy_id'] : NULL;
			$buy_name = isset($_POST['buy_name']) ? $_POST['buy_name'] : NULL;
			$buy_from = isset($_POST['buy_from']) ? $_POST['buy_from'] : NULL;
			if(empty($buy_from))
			{
				$buy_from = 'Anonymous';
			}
			if(empty($buy_id))
			{
				$errormessage .= 'Please <a href="?subtopic=gifts">select item</a> first.';
			}
			else
			{
				if(!check_name($buy_from))
				{
					$errormessage .= 'Invalid nick ("from player") format. Please <a href="?subtopic=gifts&action=select_player&buy_id='.$buy_id.'">select other name</a> or contact with administrator.';
				}
				else
				{
					$buy_offer = getItemByID($buy_id);
					if(isset($buy_offer['id'])) //item exist in database
					{
						if($user_premium_points >= $buy_offer['points'])
						{
							if(check_name($buy_name))
							{
								$buy_player = $ots->createObject('Player');
								$buy_player->find($buy_name);
								if($buy_player->isLoaded())
								{
									$buy_player_account = $buy_player->getAccount();
									if(isset($_SESSION['viewed_confirmation_page']) && $_SESSION['viewed_confirmation_page'] == 'yes' && isset($_POST['buy_confirmed']) && $_POST['buy_confirmed'] == 'yes')
									{
										if($buy_offer['type'] == 'item')
										{
											$sql = 'INSERT INTO '.$db->tableName('z_ots_comunication').' ('.$db->fieldName('id').','.$db->fieldName('name').','.$db->fieldName('type').','.$db->fieldName('action').','.$db->fieldName('param1').','.$db->fieldName('param2').','.$db->fieldName('param3').','.$db->fieldName('param4').','.$db->fieldName('param5').','.$db->fieldName('param6').','.$db->fieldName('param7').','.$db->fieldName('delete_it').') VALUES (NULL, '.$db->quote($buy_player->getName()).', '.$db->quote('login').', '.$db->quote('give_item').', '.$db->quote($buy_offer['item_id']).', '.$db->quote($buy_offer['item_count']).', '.$db->quote('').', '.$db->quote('').', '.$db->quote('item').', '.$db->quote($buy_offer['name']).', '.$db->quote('').', '.$db->quote(1).');';
											$db->query($sql);
											$save_transaction = 'INSERT INTO '.$db->tableName('z_shop_history_item').' ('.$db->fieldName('id').','.$db->fieldName('to_name').','.$db->fieldName('to_account').','.$db->fieldName('from_nick').','.$db->fieldName('from_account').','.$db->fieldName('price').','.$db->fieldName('offer_id').','.$db->fieldName('trans_state').','.$db->fieldName('trans_start').','.$db->fieldName('trans_real').') VALUES ('.$db->lastInsertId().', '.$db->quote($buy_player->getName()).', '.$db->quote($buy_player_account->getId()).', '.$db->quote($buy_from).',  '.$db->quote($account_logged->getId()).', '.$db->quote($buy_offer['points']).', '.$db->quote($buy_offer['name']).', '.$db->quote('wait').', '.$db->quote(time()).', '.$db->quote(0).');';
											$db->query($save_transaction);
											$account_logged->setCustomField('premium_points', $user_premium_points-$buy_offer['points']);
											$user_premium_points = $user_premium_points - $buy_offer['points'];
											echo '<TABLE WIDTH=100% BORDER=0 CELLSPACING=1 CELLPADDING=4>
												<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=left CLASS=white><B>Item added!</B></TD></TR>
												<TR><TD BGCOLOR="'.$config['site']['lightborder'].'" ALIGN=left><b>'.htmlspecialchars($buy_offer['name']).'</b> added to player <b>'.htmlspecialchars($buy_player->getName()).'</b> items (he will get this items after relog) for <b>'.$buy_offer['points'].' premium points</b> from your account.<br />Now you have <b>'.$user_premium_points.' premium points</b>.<br /><a href="?subtopic=gifts">GO TO MAIN SHOP SITE</a></TD></TR>
												</table>';
										}
										elseif($buy_offer['type'] == 'container')
										{
											$sql = 'INSERT INTO '.$db->tableName('z_ots_comunication').' ('.$db->fieldName('id').','.$db->fieldName('name').','.$db->fieldName('type').','.$db->fieldName('action').','.$db->fieldName('param1').','.$db->fieldName('param2').','.$db->fieldName('param3').','.$db->fieldName('param4').','.$db->fieldName('param5').','.$db->fieldName('param6').','.$db->fieldName('param7').','.$db->fieldName('delete_it').') VALUES (NULL, '.$db->quote($buy_player->getName()).', '.$db->quote('login').', '.$db->quote('give_item').', '.$db->quote($buy_offer['item_id']).', '.$db->quote($buy_offer['item_count']).', '.$db->quote($buy_offer['container_id']).', '.$db->quote($buy_offer['container_count']).', '.$db->quote('container').', '.$db->quote($buy_offer['name']).', '.$db->quote('').', '.$db->quote(1).');';
											$db->query($sql);
											$save_transaction = 'INSERT INTO '.$db->tableName('z_shop_history_item').' ('.$db->fieldName('id').','.$db->fieldName('to_name').','.$db->fieldName('to_account').','.$db->fieldName('from_nick').','.$db->fieldName('from_account').','.$db->fieldName('price').','.$db->fieldName('offer_id').','.$db->fieldName('trans_state').','.$db->fieldName('trans_start').','.$db->fieldName('trans_real').') VALUES ('.$db->lastInsertId().', '.$db->quote($buy_player->getName()).', '.$db->quote($buy_player_account->getId()).', '.$db->quote($buy_from).',  '.$db->quote($account_logged->getId()).', '.$db->quote($buy_offer['points']).', '.$db->quote($buy_offer['name']).', '.$db->quote('wait').', '.$db->quote(time()).', '.$db->quote(0).');';
											$db->query($save_transaction);
											$account_logged->setCustomField('premium_points', $user_premium_points-$buy_offer['points']);
											$user_premium_points = $user_premium_points - $buy_offer['points'];
											echo '<TABLE WIDTH=100% BORDER=0 CELLSPACING=1 CELLPADDING=4>
												<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=left CLASS=white><B>Container of items added!</B></TD></TR>
												<TR><TD BGCOLOR="'.$config['site']['lightborder'].'" ALIGN=left><b>'.htmlspecialchars($buy_offer['name']).'</b> added to player <b>'.htmlspecialchars($buy_player->getName()).'</b> items (he will get this container with items after relog) for <b>'.$buy_offer['points'].' premium points</b> from your account.<br />Now you have <b>'.$user_premium_points.' premium points</b>.<br /><a href="?subtopic=gifts">GO TO MAIN SHOP SITE</a></TD></TR>
												</table>';
										}
									}
									else
									{
										$set_session = TRUE;
										$_SESSION['viewed_confirmation_page'] = 'yes';
										echo '<table border="0" cellpadding="4" cellspacing="1" width="100%">
										<tr bgcolor="'.$config['site']['vdarkborder'].'"><td colspan="3" class="white"><b>Confirm Transaction</b></td></tr>
										<tr bgcolor="'.$config['site']['lightborder'].'"><td width="100"><b>Name:</b></td><td width="550" colspan="2">'. htmlspecialchars($buy_offer['name']).'</td></tr>
										<tr bgcolor="'.$config['site']['darkborder'].'"><td width="100"><b>Description:</b></td><td width="550" colspan="2">'. htmlspecialchars($buy_offer['description']).'</td></tr>
										<tr bgcolor="'.$config['site']['lightborder'].'"><td width="100"><b>Cost:</b></td><td width="550" colspan="2"><b>'. htmlspecialchars($buy_offer['points']).' premium points</b> from your account</td></tr>
										<tr bgcolor="'.$config['site']['darkborder'].'"><td width="100"><b>For Player:</b></td><td width="550" colspan="2"><font color="red">'.htmlspecialchars($buy_player->getName()).'</font></td></tr>
										<tr bgcolor="'.$config['site']['lightborder'].'"><td width="100"><b>From:</b></td><td width="550" colspan="2"><font color="red">'.htmlspecialchars($buy_from).'</font></td></tr>
										<tr bgcolor="'.$config['site']['darkborder'].'"><td colspan="3"></td></tr>
										<tr bgcolor="'.$config['site']['darkborder'].'"><td width="100"><b>Transaction?</b></td><td width="275" align="left">
										<form action="?subtopic=gifts&action=confirm_transaction" method="POST"><input type="hidden" name="buy_confirmed" value="yes"><input type="hidden" name="buy_id" value="'.$buy_id.'"><input type="hidden" name="buy_from" value="'.htmlspecialchars($buy_from).'"><input type="hidden" name="buy_name" value="'.htmlspecialchars($buy_name).'"><input type="submit" value="Accept"></form></td>
										<td align="right"><form action="?subtopic=gifts" method="POST"><input type="submit" value="Cancel"></form></td></tr>
										<tr bgcolor="'.$config['site']['darkborder'].'"><td colspan="3"></td></tr>
										</table>
										';
									}
								}
								else
								{
									$errormessage .= 'Player with name <b>'.htmlspecialchars($buy_name).'</b> doesn\'t exist. Please <a href="?subtopic=gifts&action=select_player&buy_id='.$buy_id.'">select other name</a>.';
								}
							}
							else
							{
								$errormessage .= 'Invalid name format. Please <a href="?subtopic=gifts&action=select_player&buy_id='.$buy_id.'">select other name</a> or contact with administrator.';
							}
						}
						else
						{
							$errormessage .= 'For this item you need <b>'.$buy_offer['points'].'</b> points. You have only <b>'.$user_premium_points.'</b> premium points. Please <a href="?subtopic=gifts">select other item</a> or buy premium points.';
						}
					}
					else
					{
						$errormessage .= 'Offer with ID <b>'.$buy_id.'</b> doesn\'t exist. Please <a href="?subtopic=gifts">select item</a> again.';
					}
				}
			}
		}
		if(!empty($errormessage))
		{
			echo '<TABLE WIDTH=100% BORDER=0 CELLSPACING=1 CELLPADDING=4>
				<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=left CLASS=white><B>Informations</B></TD></TR>
				<TR><TD BGCOLOR="'.$config['site']['lightborder'].'" ALIGN=left><b>'.$errormessage.'</b></TD></TR>
				</table>';
		}
		if(!$set_session)
		{
			unset($_SESSION['viewed_confirmation_page']);
		}
	}
	elseif($action == 'show_history')
	{
		if(!$logged)
		{
			$errormessage .= 'Please login first.';
		}
		else
		{
			$items_received_text = '';
			$items_history_received = $db->query('SELECT * FROM '.$db->tableName('z_shop_history_item').' WHERE '.$db->fieldName('to_account').' = '.$db->quote($account_logged->getId()).' OR '.$db->fieldName('from_account').' = '.$db->quote($account_logged->getId()).';');
			if(is_object($items_history_received))
			{
				foreach($items_history_received as $item_received)
				{
					if($account_logged->getId() == $item_received['to_account'])
						$char_color = 'green';
					else
						$char_color = 'red';
					$items_received_text .= '<tr bgcolor="'.$config['site']['lightborder'].'"><td><font color="'.$char_color.'">'.htmlspecialchars($item_received['to_name']).'</font></td><td>';
					if($account_logged->getId() == $item_received['from_account'])
						$items_received_text .= '<i>Your account</i>';
					else
						$items_received_text .= htmlspecialchars($item_received['from_nick']);
					$items_received_text .= '</td><td>'.htmlspecialchars($item_received['offer_id']).'</td><td>'.date("j F Y, H:i:s", $item_received['trans_start']).'</td>';
					if($item_received['trans_real'] > 0)
						$items_received_text .= '<td>'.date("j F Y, H:i:s", $item_received['trans_real']).'</td>';
					else
						$items_received_text .= '<td><b><font color="red">Not realized yet.</font></b></td>';
					$items_received_text .= '</tr>';
				}
			}
			echo '<TABLE WIDTH=100% BORDER=0 CELLSPACING=1 CELLPADDING=4>
				<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'"></TD></TR>
				<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=left CLASS=white><center><B>Transactions History</B></center></TD></TR>
				<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'"></TD></TR>
				</table><br>';

			if(!empty($items_received_text))
			{
				echo '<TABLE WIDTH=100% BORDER=0 CELLSPACING=1 CELLPADDING=4>
					<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=left CLASS=white colspan="5"><B>Item Transactions</B></TD></TR>
					<tr bgcolor="'.$config['site']['darkborder'].'"><td><b>To:</b></td><td><b>From:</b></td><td><b>Offer name</b></td><td><b>Bought on page</b></td><td><b>Received on OTS</b></td></tr>
					'.$items_received_text.'
					</table><br />';
			}
			if(empty($items_received_text))
				$errormessage .= 'You did not buy/receive any item.';
		}
		if(!empty($errormessage))
		{
			echo '<TABLE WIDTH=100% BORDER=0 CELLSPACING=1 CELLPADDING=4>
				<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=left CLASS=white><B>Informations</B></TD></TR>
				<TR><TD BGCOLOR="'.$config['site']['lightborder'].'" ALIGN=left><b>'.$errormessage.'</b></TD></TR>
				</table>';
		}
	}
	echo '<br><TABLE WIDTH=100% BORDER=0 CELLSPACING=1 CELLPADDING=4>
		<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=left CLASS=white><B>Premium Points</B></TD></TR>
		<TR><TD BGCOLOR="'.$config['site']['lightborder'].'" ALIGN=left><b><font color="green">You have premium points: </font></b>'.$user_premium_points.'</TD></TR>
		</table>';
}
else
	echo '<TABLE WIDTH=100% BORDER=0 CELLSPACING=1 CELLPADDING=4>
	<TR><TD BGCOLOR="'.$config['site']['vdarkborder'].'" ALIGN=center CLASS=white ><B>Shop Information</B></TD></TR>
	<TR><TD BGCOLOR="'.$config['site']['darkborder'].'"><center>Shop is currently closed. [to admin: edit it in \'config/config.php\']</TD></TR>
	</table>';