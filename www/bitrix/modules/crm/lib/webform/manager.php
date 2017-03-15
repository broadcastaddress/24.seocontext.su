<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Crm\WebForm;

use Bitrix\Main\Config\Option;

class Manager
{
	/**
	 * Is crm-forms in use
	 * @return bool
	 */
	public static function isInUse($isCallback = 'N')
	{
		$filter = array();
		if (in_array($isCallback, array('N', 'Y')))
		{
			$filter['=FORM.IS_CALLBACK_FORM'] = $isCallback;
		}
		$resultDb = Internals\ResultTable::getList(array('select' => array('ID'), 'filter' => $filter, 'limit' => 1));
		if ($resultDb->fetch())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check read permissions
	 * @param null|\CCrmAuthorizationHelper $userPermissions User permissions.
	 * @return bool
	 */
	public static function checkReadPermission($userPermissions = null)
	{
		return \CCrmAuthorizationHelper::CheckReadPermission('WEBFORM', 0, $userPermissions);
	}

	/**
	 * Get path to crm-form list page
	 * @return string
	 */
	public static function getUrl()
	{
		return Option::get('crm', 'path_to_webform_list', '/crm/webform/');
	}

	/**
	 * Get plain form list
	 * @return array
	 */
	public static function getListPlain()
	{
		$parameters = array();
		// TODO: uncomment when will support in main
		//$parameters["cache"] = array("ttl" => 3600);
		return Internals\FormTable::getList($parameters)->fetchAll();
	}

	/**
	 * Get list form names list
	 * @return string
	 */
	public static function getListNames()
	{
		static $result = null;
		if (!is_array($result))
		{
			$result = array();
			$formList = self::getListPlain();
			foreach ($formList as $form)
			{
				$result[$form['ID']] = $form['NAME'];
			}
		}

		return $result;
	}
}