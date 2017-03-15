<?php

namespace Bitrix\Voximplant\Security;

use Bitrix\Bitrix24\Feature;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Voximplant\PhoneTable;

class Helper
{
	/**
	 * @param int $userId
	 * @param string $permission
	 * @return array|null Returns array of owner user ids if there is limit and null if query should not be limited.
	 */
	public static function getAllowedUserIds($userId, $permission)
	{
		$result = array();
		switch ($permission)
		{
			case Permissions::PERMISSION_NONE:
				$result = array();
				break;
			case Permissions::PERMISSION_SELF:
				$result = array($userId);
				break;
			case Permissions::PERMISSION_DEPARTMENT:
				$result = self::getUserColleagues($userId);
				break;
			case Permissions::PERMISSION_ANY:
				$result = null;
				break;
		}

		return $result;
	}

	/**
	 * @return int
	 */
	public static function getCurrentUserId()
	{
		return (int)$GLOBALS['USER']->GetID();
	}

	/**
	 * @param $userId
	 * @return array
	 */
	public static function getUserColleagues($userId)
	{
		if(!Loader::includeModule('intranet'))
			return array();

		$result = array();
		$cursor = \CIntranetUtils::getDepartmentColleagues($userId, true);

		while ($row = $cursor->Fetch())
		{
			$result[] = (int)$row['ID'];
		}
		return $result;
	}

	public static function isMainMenuEnabled()
	{
		return (
			self::isBalanceMenuEnabled() ||
			self::isSettingsMenuEnabled() ||
			self::isLinesMenuEnabled() ||
			self::isUsersMenuEnabled()
		);
	}

	public static function isBalanceMenuEnabled()
	{
		$permissions = Permissions::createWithCurrentUser();
		return (
			$permissions->canPerform(Permissions::ENTITY_CALL_DETAIL, Permissions::ACTION_VIEW) ||
			$permissions->canPerform(Permissions::ENTITY_LINE, Permissions::ACTION_MODIFY)
		);
	}

	public static function isSettingsMenuEnabled()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canModifySettings();
	}

	public static function isLinesMenuEnabled()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canModifyLines();
	}

	public static function isUsersMenuEnabled()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canPerform(Permissions::ENTITY_USER, Permissions::ACTION_MODIFY);
	}

	public static function clearMenuCache()
	{
		\Bitrix\Main\Application::getInstance()->getTaggedCache()->clearByTag('bitrix:menu');
	}

	public static function canCurrentUserCallFromCrm()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canPerform(Permissions::ENTITY_CALL, Permissions::ACTION_PERFORM, Permissions::PERMISSION_CALL_CRM);
	}

	public static function canCurrentUserPerformAnyCall()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canPerform(Permissions::ENTITY_CALL, Permissions::ACTION_PERFORM, Permissions::PERMISSION_ANY);
	}

	public static function canUserCallNumber($userId, $number)
	{
		$result = false;
		$userPermissions = Permissions::createWithUserId($userId);
		$callPermission = $userPermissions->getPermission(Permissions::ENTITY_CALL, Permissions::ACTION_PERFORM);

		switch ($callPermission)
		{
			case Permissions::PERMISSION_NONE:
				$result = false;
				break;
			case Permissions::PERMISSION_ANY:
				$result = true;
				break;
			case Permissions::PERMISSION_CALL_CRM:
				$result = (\CVoxImplantCrmHelper::GetCrmEntity($number, $userId) !== false);
				break;
			case Permissions::PERMISSION_CALL_USERS:
				$result = (\CVoxImplantCrmHelper::GetCrmEntity($number, $userId) !== false);
				if(!$result)
				{
					$cursor = PhoneTable::getList(array(
						'filter' => array(
							'PHONE_NUMBER' => $number,
						)
					));
					$result = ($cursor->fetch() !== false);					
				}
				break;
		}
		return $result;
	}

	public static function canUse()
	{
		if(!Loader::includeModule('bitrix24'))
			return true;

		return Feature::isFeatureEnabled('voximplant_security');
	}
}