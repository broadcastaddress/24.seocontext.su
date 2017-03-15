<?php

namespace Bitrix\Voximplant;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Voximplant\Integration\Bitrix24;

Loc::loadMessages(__FILE__);

class Migrations
{
	/**
	 * Creates default access roles.
	 * @return string
	 */
	public static function migrateTo_16_5_1()
	{
		if(!Loader::includeModule('intranet'))
			return '';

		if(!class_exists('\Bitrix\Voximplant\Model\RoleTable')
			|| !class_exists('\Bitrix\Voximplant\Model\RoleAccessTable')
			|| !class_exists('\Bitrix\Voximplant\Security\RoleManager')
		)
		{
			return '\Bitrix\Voximplant\Migrations::migrateTo_16_5_1();';
		}


		$checkCursor = \Bitrix\Voximplant\Model\RoleTable::getList(array(
			'limit' => 1
		));

		if($checkCursor->fetch())
			return '';

		$defaultRoles = array(
			'admin' => array(
				'NAME' => Loc::getMessage('VOXIMPLANT_ROLE_ADMIN'),
				'PERMISSIONS' => array(
					'CALL_DETAIL' => array(
						'VIEW' => 'X',
					),
					'CALL' => array(
						'PERFORM' => 'X'
					),
					'CALL_RECORD' => array(
						'LISTEN' => 'X'
					),
					'USER' => array(
						'MODIFY' => 'X'
					),
					'SETTINGS' => array(
						'MODIFY' => 'X'
					),
					'LINE' => array(
						'MODIFY' => 'X'
					)
				)
			),
			'chief' => array(
				'NAME' => Loc::getMessage('VOXIMPLANT_ROLE_CHIEF'),
				'PERMISSIONS' => array(
					'CALL_DETAIL' => array(
						'VIEW' => 'X',
					),
					'CALL' => array(
						'PERFORM' => 'X'
					),
					'CALL_RECORD' => array(
						'LISTEN' => 'X'
					),
				)
			),
			'department_head' => array(
				'NAME' => Loc::getMessage('VOXIMPLANT_ROLE_DEPARTMENT_HEAD'),
				'PERMISSIONS' => array(
					'CALL_DETAIL' => array(
						'VIEW' => 'D',
					),
					'CALL' => array(
						'PERFORM' => 'X'
					),
					'CALL_RECORD' => array(
						'LISTEN' => 'D'
					),
				)
			),
			'manager' => array(
				'NAME' => Loc::getMessage('VOXIMPLANT_ROLE_MANAGER'),
				'PERMISSIONS' => array(
					'CALL_DETAIL' => array(
						'VIEW' => 'A',
					),
					'CALL' => array(
						'PERFORM' => 'X'
					),
					'CALL_RECORD' => array(
						'LISTEN' => 'A'
					),
				)
			)
		);

		$roleIds = array();
		foreach ($defaultRoles as $roleCode => $role)
		{
			$addResult = \Bitrix\Voximplant\Model\RoleTable::add(array(
				'NAME' => $role['NAME'],
			));

			$roleId = $addResult->getId();
			if($roleId)
			{
				$roleIds[$roleCode] = $roleId;
				\Bitrix\Voximplant\Security\RoleManager::setRolePermissions($roleId, $role['PERMISSIONS']);
			}
		}

		if(isset($roleIds['admin']))
		{
			\Bitrix\Voximplant\Model\RoleAccessTable::add(array(
				'ROLE_ID' => $roleIds['admin'],
				'ACCESS_CODE' => 'G1'
			));
		}

		if(isset($roleIds['manager']) && \Bitrix\Main\Loader::includeModule('intranet'))
		{
			$departmentTree = \CIntranetUtils::GetDeparmentsTree();
			$rootDepartment = (int)$departmentTree[0][0];

			if($rootDepartment > 0)
			{
				\Bitrix\Voximplant\Model\RoleAccessTable::add(array(
					'ROLE_ID' => $roleIds['manager'],
					'ACCESS_CODE' => 'DR'.$rootDepartment
				));
			}
		}

		return '';
	}

	/**
	 * Creates default config for
	 * Return string Returns agent name or empty string;
	 */
	public static function migrateTo_16_5_4()
	{
		$checkCursor = \Bitrix\Voximplant\ConfigTable::getList(array(
			'filter' => array('=PORTAL_MODE' => \CVoxImplantConfig::MODE_LINK),
			'limit' => 1
		));

		if($checkCursor->fetch())
			return '';

		$newConfig = array(
			'PORTAL_MODE' => \CVoxImplantConfig::MODE_LINK,
			'RECORDING' => \CVoxImplantConfig::GetLinkCallRecord()? 'Y': 'N',
			'CRM' => \CVoxImplantConfig::GetLinkCheckCrm()? 'Y': 'N',
			'MELODY_HOLD' => \CVoxImplantConfig::GetMelody('MELODY_HOLD'),
		);

		$callerId = \CVoxImplantPhone::GetCallerId();

		$newConfig['SEARCH_ID'] = \CVoxImplantConfig::LINK_BASE_NUMBER;
		$newConfig['PHONE_VERIFIED'] = ($callerId['VERIFIED'] ? 'Y' : 'N');
		$insertResult = ConfigTable::add($newConfig);
		if(!$insertResult->isSuccess())
			return '\Bitrix\Voximplant\Migrations::migrateTo_16_5_4();';

		$configId = $insertResult->getId();
		$portalAdmins = Bitrix24::getAdmins();
		if(count($portalAdmins) === 0)
		{
			$cursor = \CAllGroup::GetGroupUserEx(1);
			while($admin = $cursor->fetch())
			{
				$portalAdmins[] = $admin["USER_ID"];
			}
		}
		foreach ($portalAdmins as $portalAdmin)
		{
			QueueTable::add(array(
				'CONFIG_ID' => $configId,
				'USER_ID' => $portalAdmin
			));
		}

		return '';
	}
}