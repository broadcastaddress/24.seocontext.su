<?php

namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Common
{
	const TYPE_BITRIX24 = 'B24';
	const TYPE_CP = 'CP';
	
	/**
	 * Unsupported old-fashioned permission check. 
	 * @return bool
	 * @deprecated Use Bitrix\ImOpenLines\Security\Permissions instead. 
	 */
	public static function hasAccessForAdminPages()
	{
		if (\IsModuleInstalled('bitrix24'))
		{
			return $GLOBALS['USER']->CanDoOperation('bitrix24_config');
		}
		else
		{
			return $GLOBALS["USER"]->IsAdmin();
		}
	}
	
	public static function getPortalType()
	{
		$type = '';
		if(defined('BX24_HOST_NAME'))
		{
			$type = self::TYPE_BITRIX24;
		}
		else
		{
			$type = self::TYPE_CP;
		}
		return $type;
	}

	public static function getPublicFolder()
	{
		return self::GetPortalType() == self::TYPE_BITRIX24? '/openlines/': SITE_DIR . 'services/openlines/';
	}

	public static function getServerAddress()
	{
		$publicUrl = \Bitrix\Main\Config\Option::get("imopenlines", "portal_url");

		if ($publicUrl != '')
			return $publicUrl;
		else
			return (\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".$_SERVER['SERVER_NAME'].(in_array($_SERVER['SERVER_PORT'], Array(80, 443))?'':':'.$_SERVER['SERVER_PORT']);
	}

	public static function deleteBrokenSession()
	{
		$orm = \Bitrix\ImOpenLines\Model\SessionCheckTable::getList(array(
			'filter' => Array('=SESSION.ID' => '')
		));
		while ($session = $orm->fetch())
		{
			\Bitrix\ImOpenLines\Model\SessionCheckTable::delete($session['SESSION_ID']);
		}

		$orm = \Bitrix\ImOpenLines\Model\SessionTable::getList(array(
			'filter' => Array('=CONFIG.ID' => '')
		));
		while ($session = $orm->fetch())
		{
			\Bitrix\ImOpenLines\Model\SessionTable::delete($session['ID']);
		}

		return "";
	}
}
