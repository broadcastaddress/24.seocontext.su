<?php
namespace Bitrix\Crm\Kanban;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Helper
{
	const FILTER_PREFIX = 'KANBAN_V10_';

	/**
	 * Get instance of grid.
	 * @param string $type Type of entity.
	 * @return \CGridOptions
	 */
	public static function getGrid($type)
	{
		static $grid = array();

		if (!array_key_exists($type, $grid))
		{
			$grid[$type] = new \CGridOptions(self::FILTER_PREFIX . $type);
		}
		return $grid[$type];
	}

	/**
	 * Get id of grid.
	 * @param string $type Type of entity.
	 * @return string
	 */
	public static function getGridId($type)
	{
		return self::FILTER_PREFIX . $type;
	}

	/**
	 * Get lead sources, deal types, etc.
	 * @param string $code Type ot status.
	 * @return array
	 */
	private function getStatuses($code)
	{
		static $statuses = array();

		if (empty($statuses))
		{
			$statuses[$code] = array();
			foreach (\CCrmStatus::GetStatus($code) as $row)
			{
				$statuses[$code][$row['STATUS_ID']] = $row['NAME'];
			}
		}

		return $statuses[$code];
	}

	/**
	 * Get filter for Kanban.
	 * @param string $entity Type of entity.
	 * @return array
	 */
	public static function getFilter($entity)
	{
		static $filter = array();
		static $types = null;

		if ($types === null)
		{
			$types = array(
				'lead' => \CCrmOwnerType::LeadName,
				'deal' => \CCrmOwnerType::DealName,
				'quote' => \CCrmOwnerType::QuoteName,
				'invoice' => \CCrmOwnerType::InvoiceName
			);
		}

		if (!array_key_exists($entity, $filter))
		{
			$filter[$entity] = array();
			//common
			if ($entity != $types['invoice'])
			{
				$filter[$entity]['TITLE'] = array(
					'id' => 'TITLE',
					'flt_key' => '?TITLE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_TITLE'),
					'default' => true
				);
				$filter[$entity]['DATE_CREATE'] = array(
					'id' => 'DATE_CREATE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_DATE_CREATE'),
					'default' => true,
					'type' => 'date'
				);
			}
			//invoice
			else
			{
				$filter[$entity]['ORDER_TOPIC'] = array(
					'id' => 'ORDER_TOPIC',
					'flt_key' => '%ORDER_TOPIC',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_TITLE'),
					'default' => true
				);
				$filter[$entity]['DATE_INSERT'] = array(
					'id' => 'DATE_INSERT',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_DATE_CREATE'),
					'default' => true,
					'type' => 'date'
				);
			}
			//lead
			if ($entity == $types['lead'])
			{
				$filter[$entity]['SOURCE_ID'] = array(
					'id' => 'SOURCE_ID',
					'flt_key' => '=SOURCE_ID',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_SOURCE'),
					'default' => true,
					'type' => 'list',
					'items' => self::getStatuses('SOURCE'),
					'params' => array(
						'multiple' => 'Y'
					)
				);
				$filter[$entity]['NAME'] = array(
					'id' => 'NAME',
					'flt_key' => '?NAME',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_NAME'),
					'default' => false
				);
				$filter[$entity]['LAST_NAME'] = array(
					'id' => 'LAST_NAME',
					'flt_key' => '?LAST_NAME',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_LAST_NAME'),
					'default' => false
				);
				$filter[$entity]['SECOND_NAME'] = array(
					'id' => 'SECOND_NAME',
					'flt_key' => '?SECOND_NAME',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_SECOND_NAME'),
					'default' => false
				);
				$filter[$entity]['HAS_PHONE'] = array(
					'id' => 'HAS_PHONE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_HAS_PHONE'),
					'default' => false,
					'type' => 'checkbox'
				);
				$filter[$entity]['PHONE'] = array(
					'id' => 'PHONE',
					'flt_key' => 'FM#PHONE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_PHONE'),
					'default' => false
				);
				$filter[$entity]['HAS_EMAIL'] = array(
					'id' => 'HAS_EMAIL',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_HAS_EMAIL'),
					'default' => false,
					'type' => 'checkbox'
				);
				$filter[$entity]['EMAIL'] = array(
					'id' => 'EMAIL',
					'flt_key' => 'FM#EMAIL',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_EMAIL'),
					'default' => false
				);
			}
			//deal or quote
			if ($entity == $types['deal'] || $entity == $types['quote'])
			{
				$filter[$entity]['BEGINDATE'] = array(
					'id' => 'BEGINDATE',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_BEGINDATE_' . $entity),
					'default' => false,
					'type' => 'date'
				);
				$filter[$entity]['TYPE_ID'] = array(
					'id' => 'TYPE_ID',
					'flt_key' => '=TYPE_ID',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_DEAL_TYPE'),
					'default' => true,
					'type' => 'list',
					'items' => self::getStatuses('DEAL_TYPE'),
					'params' => array(
						'multiple' => 'Y'
					)
				);
				$filter[$entity]['OPPORTUNITY'] = array(
					'id' => 'OPPORTUNITY',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_OPPORTUNITY'),
					'default' => false,
					'type' => 'number'
				);
			}
			//invoice
			if ($entity == $types['invoice'])
			{
				$filter[$entity]['DATE_BILL'] = array(
					'id' => 'DATE_BILL',
					'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_DATE_BILL'),
					'default' => false,
					'type' => 'date'
				);
			}
			//common
			ob_start();
			$userID = self::getSelectorUserId('ASSIGNED_BY_ID', 0);//\CCrmSecurityHelper::GetCurrentUserID()
			$userName = $userID > 0 ? \CCrmViewHelper::GetFormattedUserName($userID) : '';
			\CCrmViewHelper::RenderUserCustomSearch(
				array(
					'ID' => 'ASSIGNED_BY_ID',
					'SEARCH_INPUT_ID' => 'ASSIGNED_BY_ID_ID',
					'SEARCH_INPUT_NAME' => 'ASSIGNED_BY_ID_NAME',
					'DATA_INPUT_ID' => 'ASSIGNED_BY_ID',
					'DATA_INPUT_NAME' => 'ASSIGNED_BY_ID',
					'COMPONENT_NAME' => 'ASSIGNED_BY_ID_SEARCH',
					'SITE_ID' => SITE_ID,
					'USER' => array('ID' => $userID, 'NAME' => $userName),
					'DELAY' => 100
				)
			);
			$val = ob_get_clean();
			$filter[$entity]['ASSIGNED_BY_ID'] = array(
				'id' => 'ASSIGNED_BY_ID',
				'name' => Loc::getMessage('CRM_KANBAN_HELPER_FLT_ASSIGNED_BY_ID'),
				'default' => true,
				'type' => 'custom',
				'value' => $val,
				'valuedb' => $userID,
				'settingsHtml' => $val,
			);
		}

		return $filter[$entity];
	}

	/**
	 * Get default key of filter for Kanban.
	 * @param string $entity Type of entity.
	 * @return array
	 */
	public static function getDefaultFilterKey($entity)
	{
		$keys = array();
		foreach (self::getFilter($entity) as $key => $item)
		{
			$keys[$key] = $item['default']===true;
		}
		return $keys;
	}

	/**
	 * Get UID for user selector.
	 * @param string $code Field code.
	 * @param int $default Default value.
	 * @return int
	 */
	public static function getSelectorUserId($code, $default)
	{
		return isset($_SESSION[self::FILTER_PREFIX . 'UID_' . $code])
				? $_SESSION[self::FILTER_PREFIX . 'UID_' . $code]
				: $default;
	}

	/**
	 * Set UID for user selector.
	 * @param string $code Field code.
	 * @param int $value Value.
	 * @return void
	 */
	public static function setSelectorUserId($code, $value)
	{
		$_SESSION[self::FILTER_PREFIX . 'UID_' . $code] = $value;
	}
}