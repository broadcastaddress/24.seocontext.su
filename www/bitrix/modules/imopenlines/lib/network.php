<?php

namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Network
{
	const MODULE_ID = 'imopenlines';
	const EXTERNAL_AUTH_ID = 'imconnector';

	private $error = null;

	public function __construct()
	{
		$this->error = new Error(null, '', '');
	}

	public function sendMessage($lineId, $fields)
	{
		if (!\Bitrix\Main\Loader::includeModule('imbot'))
		{
			$this->error = new Error(__METHOD__, 'IMBOT_ERROR', Loc::getMessage('IMOL_NETWORK_IMBOT_LOAD_ERROR'));
		}

		\Bitrix\ImOpenLines\Log::write($fields, 'NETWORK ANSWER');

		$userArray = Array();
		if ($fields['message']['user_id'] > 0)
		{
			$user = \Bitrix\Im\User::getInstance($fields['message']['user_id']);

			$avatarUrl = '';
			if ($user->getAvatarId())
			{
				$arFileTmp = \CFile::ResizeImageGet(
					$user->getAvatarId(),
					array('width' => 300, 'height' => 300),
					BX_RESIZE_IMAGE_EXACT,
					false,
					false,
					true
				);
				$avatarUrl = substr($arFileTmp['src'], 0, 4) == 'http'? $arFileTmp['src']: \Bitrix\ImOpenLines\Common::getServerAddress().$arFileTmp['src'];
			}

			$userArray = Array(
				'ID' => $user->getId(),
				'NAME' => $user->getName(),
				'LAST_NAME' => $user->getLastName(),
				'PERSONAL_GENDER' => $user->getGender(),
				'PERSONAL_PHOTO' => $avatarUrl
			);
		}

		$message = Array(
			"LINE_ID" => $lineId,
			"GUID" => $fields['chat']['id'],
			"MESSAGE_ID" => $fields['im']['message_id'],
			"MESSAGE_TEXT" => $fields['message']['text'],
			"FILES" => $fields['message']['files'],
			"ATTACH" => $fields['message']['attachments'],
			"USER" => $userArray
		);
		\Bitrix\ImBot\Service\Openlines::onMessageSend($message);

		return true;
	}

	public function sendStatusWriting($lineId, $fields)
	{
		if (!\Bitrix\Main\Loader::includeModule('imbot'))
		{
			$this->error = new Error(__METHOD__, 'IMBOT_ERROR', Loc::getMessage('IMOL_NETWORK_IMBOT_LOAD_ERROR'));
		}

		\Bitrix\ImOpenLines\Log::write($fields, 'NETWORK START WRITING (SEND)');

		$userArray = Array();
		if ($fields['user'] > 0)
		{
			$user = \Bitrix\Im\User::getInstance($fields['message']['user_id']);

			$avatarUrl = '';
			if ($user->getAvatarId())
			{
				$arFileTmp = \CFile::ResizeImageGet(
					$user->getAvatarId(),
					array('width' => 300, 'height' => 300),
					BX_RESIZE_IMAGE_EXACT,
					false,
					false,
					true
				);
				$avatarUrl = substr($arFileTmp['src'], 0, 4) == 'http'? $arFileTmp['src']: \Bitrix\ImOpenLines\Common::getServerAddress().$arFileTmp['src'];
			}

			$userArray = Array(
				'ID' => $user->getId(),
				'NAME' => $user->getName(),
				'LAST_NAME' => $user->getLastName(),
				'PERSONAL_GENDER' => $user->getGender(),
				'PERSONAL_PHOTO' => $avatarUrl
			);
		}

		$message = Array(
			"LINE_ID" => $lineId,
			"GUID" => $fields['chat']['id'],
			"USER" => $userArray
		);
		\Bitrix\ImBot\Service\Openlines::onStartWritingSend($message);

		return true;
	}

	public function search($text)
	{
		if (!\Bitrix\Main\Loader::includeModule('imbot'))
		{
			$this->error = new Error(__METHOD__, 'IMBOT_ERROR', Loc::getMessage('IMOL_NETWORK_IMBOT_LOAD_ERROR'));
		}

		$result = \Bitrix\ImBot\Bot\Network::search($text);
		if (!$result)
		{
			$this->error = \Bitrix\ImBot\Bot\Network::getError();
		}
		return $result;
	}

	public function join($code)
	{
		if (!\Bitrix\Main\Loader::includeModule('imbot'))
		{
			$this->error = new Error(__METHOD__, 'IMBOT_ERROR', Loc::getMessage('IMOL_NETWORK_IMBOT_LOAD_ERROR'));
		}

		$result = \Bitrix\ImBot\Bot\Network::join($code);
		if (!$result)
		{
			$this->error = \Bitrix\ImBot\Bot\Network::getError();
		}
		return $result;
	}

	public function registerConnector($lineId, $fields = array())
	{
		if (!\Bitrix\Main\Loader::includeModule('imbot'))
		{
			$this->error = new Error(__METHOD__, 'IMBOT_ERROR', Loc::getMessage('IMOL_NETWORK_IMBOT_LOAD_ERROR'));
		}

		$result = \Bitrix\ImBot\Bot\Network::registerConnector($lineId, $fields);
		if (!$result)
		{
			$this->error = \Bitrix\ImBot\Bot\Network::getError();
		}
		return $result;
	}

	public function updateConnector($lineId, $fields)
	{
		if (!\Bitrix\Main\Loader::includeModule('imbot'))
		{
			$this->error = new Error(__METHOD__, 'IMBOT_ERROR', Loc::getMessage('IMOL_NETWORK_IMBOT_LOAD_ERROR'));
		}

		$result = \Bitrix\ImBot\Bot\Network::updateConnector($lineId, $fields);
		if (!$result)
		{
			$this->error = \Bitrix\ImBot\Bot\Network::getError();
		}
		return $result;
	}

	public function unRegisterConnector($lineId)
	{
		if (!\Bitrix\Main\Loader::includeModule('imbot'))
		{
			$this->error = new Error(__METHOD__, 'IMBOT_ERROR', Loc::getMessage('IMOL_NETWORK_IMBOT_LOAD_ERROR'));
		}

		$result = \Bitrix\ImBot\Bot\Network::unRegisterConnector($lineId);
		if (!$result)
		{
			$this->error = \Bitrix\ImBot\Bot\Network::getError();
		}
		return $result;
	}

	public function onCommandReceive($command, $params)
	{
		$result = false;

		if ($command == 'message')
		{
			$result = $this->processMessage($params);
		}
		else if ($command == 'startWriting')
		{
			$result = $this->processStartWriting($params);
		}

		return $result;
	}

	private function processStartWriting($params)
	{
		if (!isset($params['USER']))
			return false;
		
		$userId = $this->getUserId($params['USER'], false);
		if (!$userId)
			return false;
		
		\Bitrix\ImOpenLines\Log::write($params, 'NETWORK START WRITING');
		
		$event = new \Bitrix\Main\Event('imconnector', 'OnReceivedStatusWrites', Array(
			'user' => $userId,
			'connector' => 'network',
			'line' => $params['LINE_ID'],
			'chat' => Array('id' => $params['GUID'])
		));
		$event->send();

		return true;
	}

	private function processMessage($params)
	{
		if (!isset($params['USER']))
			return false;

		if ($params['MESSAGE_TYPE'] != 'P')
			return false;

		\Bitrix\ImOpenLines\Log::write($params, 'NETWORK GET');

		$userId = $this->getUserId($params['USER']);

		$message = Array(
			'id' => $params['MESSAGE_ID'],
			'date' => "",
			'text' => $params['MESSAGE_TEXT'],
			'fileLinks' => $params['FILES'],
			'attach' => $params['ATTACH'],
		);

		$params['USER']['FULL_NAME'] = \CUser::FormatName(\CSite::GetNameFormat(false), $params['USER'], true, false);

		$description = '[B]'.Loc::getMessage('IMOL_NETWORK_NAME').'[/B]: '.$params['USER']['FULL_NAME'].'[BR]';
		if (isset($params['USER']['WORK_POSITION']) && !empty($params['USER']['WORK_POSITION']))
		{
			$description .= '[B]'.Loc::getMessage('IMOL_NETWORK_POST').'[/B]: '.$params['USER']['WORK_POSITION'].'[BR]';
		}
		if (isset($params['USER']['EMAIL']) && !empty($params['USER']['EMAIL']))
		{
			$description .= '[B]'.Loc::getMessage('IMOL_NETWORK_EMAIL').'[/B]: '.$params['USER']['EMAIL'].'[BR]';
		}
		$description .= '[B]'.Loc::getMessage('IMOL_NETWORK_WWW').'[/B]: '.$params['USER']['PERSONAL_WWW'];

		$event = new \Bitrix\Main\Event('imconnector', 'OnReceivedMessage', Array(
			'user' => $userId,
			'connector' => 'network',
			'line' => $params['LINE_ID'],
			'chat' => Array('id' => $params['GUID'], 'description' => $description),
			'message' => $message
		));
		$event->send();

		return true;
	}

	private function getUserId($params, $createUser = true)
	{
		$orm = \Bitrix\Main\UserTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=EXTERNAL_AUTH_ID' => self::EXTERNAL_AUTH_ID,
				'=XML_ID' => 'network|'.$params['UUID']
			),
			'limit' => 1
		));

		$userId = 0;
		if($userFields = $orm->fetch())
		{
			$userId = $userFields['ID'];
		}
		else if ($createUser)
		{
			$userName = $params['NAME']? $params['NAME']: Loc::getMessage('IMOL_NETWORK_GUEST_NAME');
			$userLastName = $params['LAST_NAME'];
			$userGender = $params['PERSONAL_GENDER'];
			$userAvatar = $params['PERSONAL_PHOTO'];
			$userWww = $params['PERSONAL_WWW'];
			$userEmail = $params['EMAIL'];

			if ($userAvatar)
			{
				$userAvatar = \CFile::MakeFileArray($userAvatar);
			}

			$cUser = new \CUser;
			$fields['LOGIN'] = self::MODULE_ID . '_' . rand(1000,9999) . randString(5);
			$fields['NAME'] = $userName;
			$fields['LAST_NAME'] = $userLastName;
			if ($userAvatar)
			{
				$fields['PERSONAL_PHOTO'] = $userAvatar;
			}
			if ($userEmail)
			{
				$fields['EMAIL'] = $userEmail;
			}
			$fields['PERSONAL_GENDER'] = $userGender;
			$fields['PERSONAL_WWW'] = $userWww;
			$fields['PASSWORD'] = md5($fields['LOGIN'].'|'.rand(1000,9999).'|'.time());
			$fields['CONFIRM_PASSWORD'] = $fields['PASSWORD'];
			$fields['EXTERNAL_AUTH_ID'] = self::EXTERNAL_AUTH_ID;
			$fields['XML_ID'] =  'network|'.$params['UUID'];
			$fields['ACTIVE'] = 'Y';

			$userId = $cUser->Add($fields);
		}

		return $userId;
	}

	public function getError()
	{
		return $this->error;
	}
}