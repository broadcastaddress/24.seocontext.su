<?php

namespace Bitrix\Voximplant\Integration;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class Bitrix24
{
	/**
	 * Returns array of user ids of portal admins
	 * @return array
	 */
	public static function getAdmins()
	{
		if(!Loader::includeModule('bitrix24'))
			return array();

		return \CBitrix24::getAllAdminId();
	}

	/**
	 * Returns true if Bitrix24 is installed
	 * @return bool
	 */
	public static function isInstalled()
	{
		return ModuleManager::isModuleInstalled('bitrix24');
	}
}