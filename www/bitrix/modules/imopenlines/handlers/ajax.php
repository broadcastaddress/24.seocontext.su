<?
if($_SERVER["REQUEST_METHOD"] == "POST" && array_key_exists("IM_AJAX_CALL", $_REQUEST) && $_REQUEST["IM_AJAX_CALL"] === "Y" && $_POST['IM_OPEN_LINES'] == 'Y')
{
	if (intval($USER->GetID()) <= 0 || !(IsModuleInstalled('imopenlines') && (!IsModuleInstalled('extranet') || CModule::IncludeModule('extranet') && CExtranet::IsIntranetUser())))
	{
		echo CUtil::PhpToJsObject(Array('ERROR' => 'AUTHORIZE_ERROR'));
		CMain::FinalActions();
		die();
	}

	if (!check_bitrix_sessid())
	{
		echo CUtil::PhpToJsObject(Array(
			'BITRIX_SESSID' => bitrix_sessid(),
			'ERROR' => 'SESSION_ERROR'
		));
		CMain::FinalActions();
		die();
	}

	$chatId = intval($_POST['CHAT_ID']);
	$userId = intval($USER->GetId());

	if ($_POST['COMMAND'] == 'answer')
	{
		$chat = new \CIMChat();
		$chat->SetReadMessage($_POST['CHAT_ID']);
		
		$control = new \Bitrix\ImOpenLines\Operator($_POST['CHAT_ID'], $userId);
		$result = $control->answer();
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'skip')
	{
		$control = new \Bitrix\ImOpenLines\Operator($_POST['CHAT_ID'], $userId);
		$result = $control->skip();
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'transfer')
	{
		$control = new \Bitrix\ImOpenLines\Operator($_POST['CHAT_ID'], $userId);
		$result = $control->transfer(Array(
			'TRANSFER_ID' => $_POST['TRANSFER_ID'],
		));
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'silentMode')
	{
		$control = new \Bitrix\ImOpenLines\Operator($_POST['CHAT_ID'], $userId);
		$result = $control->setSilentMode($_POST['ACTIVATE'] == 'Y');
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'pinMode')
	{
		$control = new \Bitrix\ImOpenLines\Operator($_POST['CHAT_ID'], $userId);
		$result = $control->setPinMode($_POST['ACTIVATE'] == 'Y');
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'closeDialog')
	{
		$control = new \Bitrix\ImOpenLines\Operator($_POST['CHAT_ID'], $userId);
		$result = $control->closeDialog();
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'createLead')
	{
		$control = new \Bitrix\ImOpenLines\Operator($_POST['CHAT_ID'], $userId);
		$result = $control->createLead();
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'cancelCrmExtend')
	{
		$control = new \Bitrix\ImOpenLines\Operator($_POST['CHAT_ID'], $userId);
		$result = $control->cancelCrmExtend($_POST['MESSAGE_ID']);
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'changeCrmEntity')
	{
		$control = new \Bitrix\ImOpenLines\Operator($_POST['CHAT_ID'], $userId);
		$result = $control->changeCrmEntity($_POST['MESSAGE_ID'], strtoupper($_POST['ENTITY_TYPE']), $_POST['ENTITY_ID']);
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'sessionStart')
	{
		$control = new \Bitrix\ImOpenLines\Operator(0, $userId);
		$result = $control->joinChat($_POST['USER_CODE']);
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'CHAT_ID' => $result['ID'],
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
	else if ($_POST['COMMAND'] == 'sessionGetHistory')
	{
		$control = new \Bitrix\ImOpenLines\Operator(0, $userId);
		$result = $control->getSessionHistory($_POST['SESSION_ID']);
		if ($result)
		{
			echo CUtil::PhpToJsObject(Array(
				'CHAT_ID' => $result['chatId'],
				'CAN_JOIN' => $result['canJoin'],
				'USER_ID' => 'chat'.$result['chatId'],
				'MESSAGE' => isset($result['message'])? $result['message']: Array(),
				'USERS_MESSAGE' => isset($result['message'])? $result['usersMessage']: Array(),
				'USERS' => isset($result['users'])? $result['users']: Array(),
				'USER_IN_GROUP' => isset($result['userInGroup'])? $result['userInGroup']: Array(),
				'WO_USER_IN_GROUP' => isset($result['woUserInGroup'])? $result['woUserInGroup']: Array(),
				'CHAT' => isset($result['chat'])? $result['chat']: Array(),
				'USER_BLOCK_CHAT' => isset($result['userChatBlockStatus'])? $result['userChatBlockStatus']: Array(),
				'USER_IN_CHAT' => isset($result['userInChat'])? $result['userInChat']: Array(),
				'FILES' => isset($result['files'])? $result['files']: Array(),
				'ERROR' => ''
			));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array(
				'CODE' => $control->getError()->code,
				'ERROR' => $control->getError()->msg
			));
		}
	}
}
?>