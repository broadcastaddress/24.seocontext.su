<?php
namespace Bitrix\ImBot\Service;

use Bitrix\ImBot\Log;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Openlines
{
	const BOT_CODE = "network";
	const SERVICE_CODE = "openlines";

	public static function onMessageSend($params)
	{
		$params['MESSAGE_TEXT'] = $params['MESSAGE_TEXT'] === '0'? '#ZERO#': $params['MESSAGE_TEXT'];
		$params['MESSAGE_TEXT'] = preg_replace("/\\[CHAT=[0-9]+\\](.*?)\\[\\/CHAT\\]/", "\\1",  $params['MESSAGE_TEXT']);
		$params['MESSAGE_TEXT'] = preg_replace("/\\[USER=[0-9]+\\](.*?)\\[\\/USER\\]/", "\\1",  $params['MESSAGE_TEXT']);

		$http = new \Bitrix\ImBot\Http(self::BOT_CODE);
		$query = $http->query(
			'ReceiveMessage',
			$params
		);
		if (isset($query->error))
		{
			return false;
		}

		return true;
	}
	
	public static function onStartWritingSend($params)
	{
		$http = new \Bitrix\ImBot\Http(self::BOT_CODE);
		$query = $http->query(
			'ReceiveStartWriting',
			$params
		);
		if (isset($query->error))
		{
			return false;
		}

		return true;
	}

	public static function onMessageReceive($command, $params)
	{
		unset($params['BX_BOT_NAME']);
		unset($params['BX_SERVICE_NAME']);
		unset($params['BX_COMMAND']);
		unset($params['BX_TYPE']);

		if (!\Bitrix\Main\Loader::includeModule('imopenlines'))
			return false;

		\Bitrix\ImBot\Log::write(Array($command,$params), 'NETWORK SERVICE');

		$network = new \Bitrix\ImOpenLines\Network();
		if($result = $network->onCommandReceive($command, $params))
		{
			$result = Array('RESULT' => 'OK');
		}
		else
		{
			$result = new \Bitrix\ImBot\Error(__METHOD__, 'UNKNOWN_COMMAND', 'Command isnt found');
		}

		return $result;
	}
}