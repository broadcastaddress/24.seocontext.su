<?php
namespace Bitrix\ImConnector\Connectors;

use \Bitrix\Im\User,
	\Bitrix\Main\Loader,
	\Bitrix\Main\Web\Uri,
	\Bitrix\Main\Config\Option;
use \Bitrix\ImConnector\Library;

class Viber
{
	public static function sendMessageProcessing($value, $connector)
	{
		if($connector == Library::ID_VIBER_CONNECTOR && !empty($value['chat']['id']) && !empty($value['message']['user_id']) && Loader::includeModule('im'))
		{
			$user = User::getInstance($value['message']['user_id'])->getFields();

			if(!empty($user) && !empty($user['avatarId']) && !empty($user['avatar']))
			{
				if(!Library::isEmpty($user['name']))
					$value['user']['name'] = $user['name'];

				$uri = new Uri($user['avatar']);
				if($uri->getHost())
					$value['user']['picture'] = array('url' => $user['avatar']);
				else
					$value['user']['picture'] = array('url' => Option::get(Library::MODULE_ID, "uri_client") . $user['avatar']);
			}
		}

		return $value;
	}
}