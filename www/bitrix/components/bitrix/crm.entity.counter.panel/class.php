<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\Counter\EntityCounterType;
use \Bitrix\Crm\Counter\EntityCounterFactory;

Loc::loadMessages(__FILE__);

class CCrmEntityCounterPanelComponent extends CBitrixComponent
{
	/** @var int */
	protected $userID = 0;
	/** @var string */
	protected $entityTypeName = '';
	/** @var int */
	protected $entityTypeID = \CCrmOwnerType::Undefined;
	/** @var array */
	protected $extras = array();
	/** @var string  */
	protected $entityListUrl = '';
	/** @var array */
	protected $errors = array();
	/** @var bool */
	protected $isVisible = true;
	/** @var bool  */
	protected $recalculate = false;

	public function executeComponent()
	{
		$this->initialize();
		if($this->isVisible)
		{
			foreach($this->errors as $message)
			{
				ShowError($message);
			}
			$this->includeComponentTemplate();
		}
	}
	protected function initialize()
	{
		if (!Bitrix\Main\Loader::includeModule('crm'))
		{
			$this->errors[] = GetMessage('CRM_MODULE_NOT_INSTALLED');
			return;
		}

		$this->userID = CCrmSecurityHelper::GetCurrentUserID();

		if(isset($this->arParams['ENTITY_TYPE_NAME']))
		{
			$this->entityTypeName = $this->arParams['ENTITY_TYPE_NAME'];
		}
		$this->entityTypeID = CCrmOwnerType::ResolveID($this->entityTypeName);
		if(!CCrmOwnerType::IsDefined($this->entityTypeID))
		{
			$this->errors[] = GetMessage('CRM_COUNTER_ENTITY_TYPE_NOT_DEFINED');
			return;
		}

		if(isset($this->arParams['EXTRAS']) && is_array($this->arParams['EXTRAS']))
		{
			$this->extras = $this->arParams['EXTRAS'];
		}

		if(isset($this->arParams['PATH_TO_ENTITY_LIST']))
		{
			$this->entityListUrl = $this->arParams['PATH_TO_ENTITY_LIST'];
		}

		$this->recalculate = isset($_REQUEST['recalc']) && strtoupper($_REQUEST['recalc']) === 'Y';

		/*
		 * Messages are used:
		 * CRM_COUNTER_DEAL_CAPTION
		 * CRM_COUNTER_LEAD_CAPTION
		 * CRM_COUNTER_CONTACT_CAPTION
		 * CRM_COUNTER_COMPANY_CAPTION
		 */

		$this->arResult['ENTITY_CAPTION'] = GetMessage("CRM_COUNTER_{$this->entityTypeName}_CAPTION");

		$data = array();
		$total = 0;
		foreach(EntityCounterType::getAll() as $typeID)
		{
			$counter = EntityCounterFactory::create($this->entityTypeID, $typeID, $this->userID, $this->extras);
			$value = $counter->getValue($this->recalculate);
			if($value > 0)
			{
				$typeName = EntityCounterType::resolveName($typeID);
				$data[] = array(
					'TYPE_NAME' => $typeName,
					'TEXT' => GetMessage("CRM_COUNTER_TYPE_{$typeName}", array('#VALUE#' => $value)),
					'IS_ALERT' => $typeID === EntityCounterType::OVERDUE,
					'URL' => $counter->prepareDetailsPageUrl($this->entityListUrl)
				);
				$total += $value;
			}
		}

		$this->arResult['TOTAL'] = $total;
		$this->arResult['DATA'] = $data;
		$this->isVisible = ($total > 0);
	}
}