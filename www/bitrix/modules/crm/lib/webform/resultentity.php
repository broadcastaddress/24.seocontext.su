<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Crm\WebForm;

use Bitrix\Crm\Automation;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\WebForm\Internals\ResultEntityTable;
use Bitrix\Crm\Activity\Provider;
use Bitrix\Crm\Integration\Channel\WebFormTracker;
use Bitrix\Crm\Integration\Channel\SiteButtonTracker as CallBackWebFormTracker;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class ResultEntity
{
	CONST DUPLICATE_CONTROL_MODE_REPLACE = 'REPLACE';
	CONST DUPLICATE_CONTROL_MODE_MERGE = 'MERGE';
	CONST DUPLICATE_CONTROL_MODE_NONE = '';
	CONST INVOICE_PAYER_COMPANY = 'COMPANY';
	CONST INVOICE_PAYER_CONTACT = 'CONTACT';

	protected $formId = null;
	protected $formData = array();
	protected $duplicateMode = null;
	protected $resultId = null;

	protected $entityMap = null;
	protected $scheme = null;
	protected $fields = array();
	protected $productRows = array();
	protected $currencyId = null;
	protected $presetFields = array();
	protected $placeholders = array();
	protected $commonFields = array();
	protected $assignedById = null;
	protected $activityFields = array();
	protected $invoiceSettings = array();

	protected $activityId = null;
	protected $contactId = null;
	protected $companyId = null;
	protected $dealId = null;
	protected $leadId = null;
	protected $quoteId = null;
	protected $invoiceId = null;

	protected $resultEntityPack = array();

	public static function getDuplicateModes()
	{
		return array_keys(self::getDuplicateModeList());
	}

	public static function getDuplicateModeList()
	{
		return array(
			self::DUPLICATE_CONTROL_MODE_NONE => Loc::getMessage('CRM_WEBFORM_RESULT_ENTITY_DC_NONE'),
			self::DUPLICATE_CONTROL_MODE_REPLACE => Loc::getMessage('CRM_WEBFORM_RESULT_ENTITY_DC_REPLACE'),
			self::DUPLICATE_CONTROL_MODE_MERGE => Loc::getMessage('CRM_WEBFORM_RESULT_ENTITY_DC_MERGE'),
		);
	}

	protected function processDuplicateControl($entityTypeName, $fields)
	{
		if(!$this->duplicateMode || $this->duplicateMode == self::DUPLICATE_CONTROL_MODE_NONE)
		{
			return null;
		}

		$entity = Entity::getMap($entityTypeName);
		$entityType = \CCrmOwnerType::ResolveID($entityTypeName);
		if(!$entity || empty($entity['DUPLICATE_CHECK']))
		{
			return null;
		}

		$availableCheckFieldNames = array(
			'FM' => array(
				'PHONE',
				'EMAIL'
			),
			'' => array(
				'TITLE',
				'NAME',
				'SECOND_NAME',
				'LAST_NAME'
			)
		);

		$fieldValues = array();
		$fieldNames = array();
		foreach($availableCheckFieldNames as $availablePrefix => $availableFieldList)
		{
			foreach($availableFieldList as $availableField)
			{
				if(!$availablePrefix)
				{
					if(empty($fields[$availableField]))
					{
						continue;
					}

					if($this->getTemplateFieldValue($entityTypeName, $availableField) === $fields[$availableField])
					{
						unset($fields[$availableField]);
						continue;
					}

					$fieldNames[] = $availableField;
					$fieldValues[$availableField] = $fields[$availableField];
				}
				else
				{
					if(empty($fields[$availablePrefix][$availableField]))
					{
						continue;
					}

					$fieldNames[] = $availablePrefix . '.' . $availableField;
					if(!is_array($fieldValues[$availablePrefix]))
					{
						$fieldValues[$availablePrefix] = array();
					}

					$fieldValue = $fields[$availablePrefix][$availableField];
					$fieldValues[$availablePrefix][$availableField] = $fieldValue;
				}
			}
		}

		if(count($fieldNames) == 0 || count($fieldValues) == 0)
		{
			return null;
		}

		/** @var  $duplicateChecker \Bitrix\Crm\Integrity\DuplicateChecker */
		$duplicateChecker = new $entity['DUPLICATE_CHECK']['CHECKER_CLASS_NAME'];
		$adapter = \Bitrix\Crm\EntityAdapterFactory::create($fieldValues, $entityType);
		$duplicateList = $duplicateChecker->findDuplicates(
			$adapter,
			new \Bitrix\Crm\Integrity\DuplicateSearchParams($fieldNames)
		);

		$duplicateRow = null;
		foreach($duplicateList as $duplicate)
		{
			/** @var  $duplicate \Bitrix\Crm\Integrity\Duplicate */
			$foundedEntityList = $duplicate->getEntityArray();
			foreach($foundedEntityList as $foundedEntity)
			{
				if($foundedEntity['ENTITY_TYPE_ID'] != $entityType || !$foundedEntity['ENTITY_ID'])
				{
					continue;
				}

				$duplicateRow = $foundedEntity;
				break;
			}
		}

		if(!$duplicateRow)
		{
			return null;
		}

		$rowId = $duplicateRow['ENTITY_ID'];

		/** @var  $entityObject \CCrmContact */
		$entityObject = new $entity['CLASS_NAME'](false);

		$entityMultiFields = array();
		$multiFields = \CCrmFieldMulti::GetEntityFields($entityTypeName, $rowId, null);
		foreach($multiFields as $multiField)
		{
			$entityMultiFields[$multiField['TYPE_ID']] = array(
				$multiField['ID'] => array(
					'VALUE' => $multiField['VALUE'],
					'VALUE_TYPE' => $multiField['VALUE_TYPE'],
				)
			);
		}
		unset($multiFields);

		/** @var  $merger \Bitrix\Crm\Merger\EntityMerger */
		$mergerClass = $entity['DUPLICATE_CHECK']['MERGER_CLASS_NAME'];
		switch($this->duplicateMode)
		{
			case self::DUPLICATE_CONTROL_MODE_MERGE:
				$entityFieldsDb = $entityObject->GetListEx(
					array(),
					array('ID' => $rowId, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('*', 'UF_*')
				);
				$entityFields = $entityFieldsDb->Fetch();
				$entityFields['FM'] = $entityMultiFields;
				$merger = new $mergerClass(0, false);
				$merger->mergeFields($fields, $entityFields);

				$entityObject->Update($rowId, $entityFields);
				break;

			case self::DUPLICATE_CONTROL_MODE_REPLACE:
				$entityFields = array('FM' => $entityMultiFields);
				$merger = new $mergerClass(0, false);
				$merger->mergeFields($fields, $entityFields);
				$entityObject->Update($rowId, $entityFields);

				if ($entityType === \CCrmOwnerType::Lead && isset($entityFields['STATUS_ID']))
				{
					Automation\Factory::runOnStatusChanged($entityType, $rowId);
				}
				break;
		}

		return $rowId;
	}

	public function getInvoiceId()
	{
		return $this->invoiceId;
	}

	protected function replaceDefaultFieldValue($template)
	{
		static $replace = null;
		if(!$replace)
		{
			$replaceList = array(
				'RESULT_ID' => $this->resultId
			);

			$replace['from'] = array();
			$replace['to'] = array();
			foreach($replaceList as $replaceFrom => $replaceTo)
			{
				$replace['from'][] = '#' . $replaceFrom . '#';
				$replace['to'][] = $replaceTo;
			}
		}

		return str_replace($replace['from'], $replace['to'], $template);
	}

	protected function getTemplateFieldValue($entityName, $fieldName)
	{
		if(!$this->entityMap)
		{
			$this->entityMap = Entity::getMap();
		}

		if(!isset($this->entityMap[$entityName]))
		{
			return null;
		}

		if(!isset($this->entityMap[$entityName]['FIELD_AUTO_FILL_TEMPLATE']))
		{
			return null;
		}

		if(!isset($this->entityMap[$entityName]['FIELD_AUTO_FILL_TEMPLATE'][$fieldName]))
		{
			return null;
		}

		$fieldProperties = $this->entityMap[$entityName]['FIELD_AUTO_FILL_TEMPLATE'][$fieldName];
		return $this->replaceDefaultFieldValue($fieldProperties['TEMPLATE']);
	}

	protected function getEntityFields($entityName)
	{
		if(isset($this->fields[$entityName]))
		{
			$fields = $this->fields[$entityName];
		}
		else
		{
			$fields =  array();
		}

		if(!$this->entityMap)
		{
			$this->entityMap = Entity::getMap();
		}

		if(!isset($this->entityMap[$entityName]['FIELD_AUTO_FILL_TEMPLATE']))
		{
			return $fields;
		}


		foreach($this->entityMap[$entityName]['FIELD_AUTO_FILL_TEMPLATE'] as $fieldName => $fieldProperties)
		{
			if(isset($fields[$fieldName]) && $fields[$fieldName])
			{
				continue;
			}

			$fields[$fieldName] =  $this->replaceDefaultFieldValue($fieldProperties['TEMPLATE']);
		}

		return $fields;
	}

	public function setProductRows($productList)
	{
		foreach($productList as $product)
		{
			$this->productRows[] = array(
				'PRODUCT_ID' => (int) $product['ID'],
				'PRODUCT_NAME' => $product['NAME'],
				'PRICE' => $product['PRICE'],
				'QUANTITY' => 1
			);
		}
	}

	public function setPresetFields($fields = array())
	{
		$this->presetFields = $fields;
	}

	public function setActivityFields($activityFields = array())
	{
		$this->activityFields = $activityFields;
	}

	public function setInvoiceSettings($invoiceSettings = array())
	{
		$this->invoiceSettings = $invoiceSettings;
	}

	protected function getProductRowsSum()
	{
		$result = 0;
		foreach($this->productRows as $productRow)
		{
			$result += $productRow['PRICE'];
		}

		return $result;
	}

	protected function getProductRows($withProductId = true)
	{
		if($withProductId)
		{
			return $this->productRows;
		}
		else
		{
			$result = array();
			foreach($this->productRows as $productRow)
			{
				$productRow['PRODUCT_ID'] = 0;
				$result[] = $productRow;
			}

			return $result;
		}
	}

	protected function fillFieldsByPresetFields($entityName, $entityFields)
	{
		if(!$this->presetFields)
		{
			return $entityFields;
		}

		foreach($this->presetFields as $presetField)
		{
			if($presetField['ENTITY_NAME'] != $entityName)
			{
				continue;
			}

			$value = $presetField['VALUE'];
			$placeholders = $this->placeholders;
			$placeholders['crm_form_id'] = $this->formId;
			$placeholders['crm_form_name'] = $this->formData['NAME'];
			$placeholders['crm_result_id'] = $this->resultId;
			$fromList = $toList = array();
			foreach ($placeholders as $key => $val)
			{
				$fromList[] = '%' . $key . '%';
				$toList[] = $val;
			}
			$value = str_replace($fromList,	$toList, $value);
			$entityFields[$presetField['FIELD_NAME']] = $value;
		}

		return $entityFields;
	}

	protected function addByEntityName($entityName, $params = array())
	{
		$id = null;

		$entityFields = $this->getEntityFields($entityName);
		if($params['FIELDS'])
		{
			$entityFields = $params['FIELDS'] + $entityFields;
		}

		if(count($entityFields) == 0)
		{
			return null;
		}

		if(!$this->entityMap[$entityName])
		{
			return null;
		}

		if($entityName !== \CCrmOwnerType::InvoiceName)
		{
			$entityFields = $entityFields + $this->commonFields;
		}
		$entityFields = $this->fillFieldsByPresetFields($entityName, $entityFields);

		if($this->assignedById)
		{
			$entityFields['ASSIGNED_BY_ID'] = $this->assignedById;
		}

		$productRows = $this->getProductRows();
		$isNeedAddProducts = ($params['SET_PRODUCTS'] && count($productRows) > 0);
		$entity = $this->entityMap[$entityName];
		/** @var \CCrmLead $entityClassName */
		$entityClassName = $entity['CLASS_NAME'];

		$isEntityAdded = false;
		$id = $this->processDuplicateControl($entityName, $entityFields);
		if(!$id)
		{
			if($isNeedAddProducts && $entityName == \CCrmOwnerType::InvoiceName)
			{
				$entityFields["PRODUCT_ROWS"] = $this->getProductRows(false);
			}

			/** @var \CCrmLead $entityInstance */
			$entityInstance = new $entityClassName(false);
			$addOptions = array('DISABLE_USER_FIELD_CHECK' => true);
			if($entityName == \CCrmOwnerType::InvoiceName)
			{
				/** @var \CCrmInvoice $entityInstance */
				$recalculateOptions = false;
				$id = $entityInstance->Add($entityFields, $recalculateOptions, SITE_ID, $addOptions);
			}
			else
			{
				$entityFields['WEBFORM_ID'] = $this->formId;
				$id = $entityInstance->Add($entityFields, true, $addOptions);
			}
			$isEntityAdded = true;
		}

		if($id)
		{
			if($isNeedAddProducts && in_array($entityName, array(\CCrmOwnerType::LeadName, \CCrmOwnerType::DealName, \CCrmOwnerType::QuoteName)) )
			{
				$entityClassName::SaveProductRows($id, $productRows, false);
			}

			$this->resultEntityPack[] = array(
				'RESULT_ID' => $this->resultId,
				'ENTITY_NAME' => $entityName,
				'ITEM_ID' => $id,
			);

			$errors = array();
			if($isEntityAdded)
			{
				\CCrmBizProcHelper::AutoStartWorkflows(
					\CCrmOwnerType::ResolveID($entityName),
					$id,
					\CCrmBizProcEventType::Create,
					$errors
				);

				Automation\Factory::runOnAdd(\CCrmOwnerType::ResolveID($entityName), $id);
			}
		}

		return $id;
	}

	protected function addDeal($dealParams = array())
	{
		$this->addClient();

		$params = array();
		$params['FIELDS'] = array();
		if($this->companyId || $this->contactId)
		{
			if($this->companyId)
			{
				$params['FIELDS']['COMPANY_ID'] = $this->companyId;
			}

			if($this->contactId)
			{
				$params['FIELDS']['CONTACT_ID'] = $this->contactId;
			}
		}

		if(is_array($this->formData['FORM_SETTINGS']) && $this->formData['FORM_SETTINGS']['DEAL_CATEGORY'])
		{
			$params['FIELDS']['CATEGORY_ID'] = $this->formData['FORM_SETTINGS']['DEAL_CATEGORY'];
		}

		$params['SET_PRODUCTS'] = true;
		$this->dealId = $this->addByEntityName(\CCrmOwnerType::DealName, $params);

		if ($this->dealId)
		{
			if ($this->formData['IS_CALLBACK_FORM'] == 'Y')
			{
				CallBackWebFormTracker::getInstance()->registerDeal($this->dealId);
			}
			else
			{
				WebFormTracker::getInstance()->registerDeal($this->dealId, array('ORIGIN_ID' => $this->formId));
			}
		}

		if($dealParams['ADD_INVOICE'])
		{
			$this->addInvoice();
		}
	}

	protected function addLead($leadParams = array())
	{
		$params = array();
		$params['SET_PRODUCTS'] = true;
		$this->leadId = $this->addByEntityName(\CCrmOwnerType::LeadName, $params);

		if ($this->leadId)
		{
			if ($this->formData['IS_CALLBACK_FORM'] == 'Y')
			{
				CallBackWebFormTracker::getInstance()->registerLead($this->leadId);
			}
			else
			{
				WebFormTracker::getInstance()->registerLead($this->leadId, array('ORIGIN_ID' => $this->formId));
			}
		}

		if($leadParams['ADD_INVOICE'])
		{
			$this->addClient();
			$this->addInvoice();
		}
	}

	protected function addCompany()
	{
		$this->companyId = $this->addByEntityName(\CCrmOwnerType::CompanyName);
	}

	protected function addContact()
	{
		$params = array();
		if($this->companyId)
		{
			$params['FIELDS'] = array('COMPANY_ID' => $this->companyId);
		}

		$this->contactId = $this->addByEntityName(\CCrmOwnerType::ContactName, $params);
	}

	protected function addClient($clientParams = array())
	{
		$isAddCompany = isset($this->fields[\CCrmOwnerType::CompanyName]) || $this->isInvoiceSettingsPayerCompany();
		$isAddContact = isset($this->fields[\CCrmOwnerType::ContactName]) || $this->isInvoiceSettingsPayerContact();

		if($isAddCompany)
		{
			$this->addCompany();
		}

		if($isAddContact)
		{
			$this->addContact();
		}

		if($clientParams['ADD_INVOICE'])
		{
			$this->addInvoice();
		}
	}

	protected function addQuote($quoteParams = array())
	{
		$this->addClient();

		$params = array();
		if($this->companyId || $this->contactId)
		{
			$params['FIELDS'] = array();
			if($this->companyId)
			{
				$params['FIELDS']['COMPANY_ID'] = $this->companyId;
			}

			if($this->contactId)
			{
				$params['FIELDS']['CONTACT_ID'] = $this->contactId;
			}
		}

		$params['SET_PRODUCTS'] = true;
		$this->quoteId = $this->addByEntityName(\CCrmOwnerType::QuoteName, $params);

		if($quoteParams['ADD_INVOICE'])
		{
			$this->addInvoice();
		}
	}

	protected function getInvoiceSettingsPayer()
	{
		$payer = null;
		if($this->invoiceSettings && $this->invoiceSettings['PAYER'])
		{
			$payer = $this->invoiceSettings['PAYER'];
		}

		return $payer;
	}

	protected function isInvoiceSettingsPayerCompany()
	{
		return $this->getInvoiceSettingsPayer() == self::INVOICE_PAYER_COMPANY;
	}

	protected function isInvoiceSettingsPayerContact()
	{
		return $this->getInvoiceSettingsPayer() == self::INVOICE_PAYER_CONTACT;
	}

	protected function addInvoice($params = array())
	{
		if(!isset($params['FIELDS']))
		{
			$params['FIELDS'] = array();
		}

		$personTypes = \CCrmPaySystem::getPersonTypeIDs();
		$currentPersonTypeId = (int)$personTypes['CONTACT'];

		$isPersonTypeSet = false;
		if($this->companyId && $this->isInvoiceSettingsPayerCompany())
		{
			$isPersonTypeSet = true;
			$currentPersonTypeId = (int)$personTypes['COMPANY'];
			$params['FIELDS']['PERSON_TYPE_ID'] = $currentPersonTypeId;
			$params['FIELDS']['UF_COMPANY_ID'] = $this->companyId;
			$params['FIELDS']['INVOICE_PROPERTIES'] = array('COMPANY' => '-');
		}

		if($this->contactId && $this->isInvoiceSettingsPayerContact())
		{
			$isPersonTypeSet = true;
			$params['FIELDS']['UF_CONTACT_ID'] = $this->contactId;
			if(!$params['FIELDS']['PERSON_TYPE_ID'])
			{
				$currentPersonTypeId = (int)$personTypes['CONTACT'];
				$params['FIELDS']['PERSON_TYPE_ID'] = $currentPersonTypeId;
			}
			$params['FIELDS']['INVOICE_PROPERTIES'] = array('CONTACT' => '-');
		}

		if($this->dealId)
		{
			$params['FIELDS']['UF_DEAL_ID'] = $this->dealId;
		}
		if($this->quoteId)
		{
			$params['FIELDS']['UF_QUOTE_ID'] = $this->quoteId;
		}

		if(!$isPersonTypeSet)
		{
			return;
		}


		$billList = \CCrmPaySystem::GetPaySystemsListItems($currentPersonTypeId);
		foreach($billList as $billId => $billName)
		{
			$params['FIELDS']['PAY_SYSTEM_ID'] = $billId;
			break;
		}

		$params['SET_PRODUCTS'] = true;
		$this->invoiceId = $this->addByEntityName(\CCrmOwnerType::InvoiceName, $params);
	}

	protected function addActivity()
	{
		$bindings = array();
		foreach($this->resultEntityPack as $entity)
		{
			$bindings[] = array(
				'OWNER_ID' => $entity['ITEM_ID'],
				'OWNER_TYPE_ID' => \CCrmOwnerType::ResolveID($entity['ENTITY_NAME'])
			);
		}

		$activityFields = array(
			'TYPE_ID' =>  \CCrmActivityType::Provider,
			'PROVIDER_ID' => Provider\WebForm::PROVIDER_ID,
			'PROVIDER_TYPE_ID' => $this->formId,
			'SUBJECT' => Loc::getMessage('CRM_WEBFORM_RESULT_ENTITY_ACTIVITY_SUBJECT') . $this->resultId,
			'DIRECTION' => \CCrmActivityDirection::Incoming,
			'ASSOCIATED_ENTITY_ID' => $this->resultId,
			'START_TIME' => new DateTime(),
			'COMPLETED' => 'N',
			'PRIORITY' => \CCrmActivityPriority::Medium,
			'DESCRIPTION' => '',
			'DESCRIPTION_TYPE' => \CCrmContentType::PlainText,
			'LOCATION' => '',
			'NOTIFY_TYPE' => \CCrmActivityNotifyType::None,
			'SETTINGS' => array(),
			'AUTHOR_ID' => $this->assignedById,
			'RESPONSIBLE_ID' => $this->assignedById,
			'ORIGIN_ID' => '',
			'BINDINGS' => $bindings,
			'PROVIDER_PARAMS' => array(
				'FIELDS' => $this->activityFields,
				'FORM' => array(
					'LINK' => Script::getUrlContext($this->formData)
				)
			)
		);

		$activityFields = $this->fillFieldsByPresetFields(\CCrmOwnerType::ActivityName, $activityFields);

		$productRowsSum = $this->getProductRowsSum();
		if($productRowsSum > 0)
		{
			$activityFields['RESULT_SUM'] = $productRowsSum;
			$form = new Form(); //TODO: set id when price will be changeable
			$activityFields['RESULT_CURRENCY_ID'] = $form->getCurrencyId();
		}

		$communications = array();
		if($this->contactId)
		{
			$communicationFields = $this->getEntityFields(\CCrmOwnerType::ContactName);
			$communications[] = array(
				'TYPE' => '',
				'VALUE' => $communicationFields['NAME'],
				'ENTITY_ID' => $this->contactId,
				'ENTITY_TYPE_ID' => \CCrmOwnerType::Contact
			);
		}
		if($this->companyId)
		{
			$communicationFields = $this->getEntityFields(\CCrmOwnerType::CompanyName);
			$communications[] = array(
				'TYPE' => '',
				'VALUE' => $communicationFields['TITLE'],
				'ENTITY_ID' => $this->companyId,
				'ENTITY_TYPE_ID' => \CCrmOwnerType::Company
			);
		}
		if($this->leadId)
		{
			$communicationFields = $this->getEntityFields(\CCrmOwnerType::LeadName);
			$communications[] = array(
				'TYPE' => '',
				'VALUE' => $communicationFields['TITLE'],
				'ENTITY_ID' => $this->leadId,
				'ENTITY_TYPE_ID' => \CCrmOwnerType::Lead
			);
		}

		$id = \CCrmActivity::Add(
			$activityFields, false, true,
			array('REGISTER_SONET_EVENT' => true)
		);
		if($id > 0)
		{
			$this->activityId = $id;
			if(count($communications) > 0)
			{
				\CCrmActivity::SaveCommunications($this->activityId, $communications, $activityFields, true, false);
			}

			if ($this->formData['IS_CALLBACK_FORM'] == 'Y')
			{
				CallBackWebFormTracker::getInstance()->registerActivity($this->activityId);
			}
			else
			{
				WebFormTracker::getInstance()->registerActivity($this->activityId, array('ORIGIN_ID' => $this->formId));
			}

			if(Loader::includeModule('im'))
			{
				$url = "/crm/activity/?open_view=#log_id#";
				$url = str_replace(array("#log_id#"), array($id), $url);
				$serverName = (Context::getCurrent()->getRequest()->isHttps() ? "https" : "http") . "://";
				if(defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0)
				{
					$serverName .= SITE_SERVER_NAME;
				}
				else
				{
					$serverName .= Option::get("main", "server_name", "");
				}
				$imNotifyMessage = Loc::getMessage(
					'CRM_WEBFORM_RESULT_ENTITY_ACTIVITY_RESPONSIBLE_IM_NOTIFY',
					Array("#title#" => '<a href="' . $url . '">' . htmlspecialcharsbx($activityFields['SUBJECT']).'</a>')
				);
				$imNotifyMessageOut = Loc::getMessage(
						'CRM_WEBFORM_RESULT_ENTITY_ACTIVITY_RESPONSIBLE_IM_NOTIFY',
						Array("#title#" => htmlspecialcharsbx($activityFields['SUBJECT']))
					) . " (". $serverName . $url . ")";

				$imNotifyFields = array(
					"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
					"TO_USER_ID" => $activityFields['RESPONSIBLE_ID'],
					"FROM_USER_ID" => $activityFields['AUTHOR_ID'],
					"NOTIFY_TYPE" => IM_NOTIFY_FROM,
					"NOTIFY_MODULE" => "crm",
					"LOG_ID" => null,
					"NOTIFY_EVENT" => "activity_add",
					"NOTIFY_TAG" => "CRM|ACTIVITY|" . $id,
					"NOTIFY_MESSAGE" => $imNotifyMessage,
					"NOTIFY_MESSAGE_OUT" => $imNotifyMessageOut
				);
				\CIMNotify::Add($imNotifyFields);
			}
		}
	}

	protected function executeTrigger()
	{
		$bindings = array();
		foreach($this->resultEntityPack as $entity)
		{
			$bindings[] = array(
				'OWNER_ID' => $entity['ITEM_ID'],
				'OWNER_TYPE_ID' => \CCrmOwnerType::ResolveID($entity['ENTITY_NAME'])
			);
		}

		Automation\Trigger\WebFormTrigger::execute($bindings, array(
			'WEBFORM_ID' => $this->formId
		));
	}

	protected function prepareFields($fields)
	{
		$entityFields = array();
		foreach($fields as $field)
		{
			$values = $field['values'];
			switch($field['type_original'])
			{
				case 'typed_string':
					$valuesTmp = array();
					$valueIndex = 0;
					foreach($values as $value)
					{
						$valuesTmp['n' . $valueIndex] = array(
							'VALUE' => $value,
							'VALUE_TYPE' => $field['value_type'] ? $field['value_type'] : 'OTHER'
						);
						$valueIndex++;
					}
					$entityFields[$field['entity_name']]['FM'][$field['entity_field_name']] = $valuesTmp;
					continue 2;
					break;
			}

			$values = $field['multiple_original'] ? $values : $values[0];
			$entityFields[$field['entity_name']][$field['entity_field_name']] = $values;
		}

		return $entityFields;
	}

	public function add($scheme, $fields)
	{
		$this->entityMap = Entity::getMap();
		$this->scheme = $scheme;
		$this->fields = $this->prepareFields($fields);

		try
		{
			switch($scheme)
			{
				case Entity::ENUM_ENTITY_SCHEME_CONTACT:
					$this->addClient();
					break;

				case Entity::ENUM_ENTITY_SCHEME_LEAD:
					$this->addLead();
					break;

				case Entity::ENUM_ENTITY_SCHEME_LEAD_INVOICE:
					$this->addLead(array('ADD_INVOICE' => true));
					break;

				case Entity::ENUM_ENTITY_SCHEME_DEAL:
					$this->addDeal();
					break;

				case Entity::ENUM_ENTITY_SCHEME_QUOTE:
					$this->addQuote();
					break;

				case Entity::ENUM_ENTITY_SCHEME_DEAL_INVOICE:
					$this->addDeal(array('ADD_INVOICE' => true));
					break;

				case Entity::ENUM_ENTITY_SCHEME_QUOTE_INVOICE:
					$this->addQuote(array('ADD_INVOICE' => true));
					break;

				case Entity::ENUM_ENTITY_SCHEME_CONTACT_INVOICE:
					$this->addClient(array('ADD_INVOICE' => true));
					break;
			}

			$this->addActivity();
			$this->executeTrigger();

			if(count($this->resultEntityPack) > 0)
			{
				ResultEntityTable::addBatch($this->formId, $this->resultEntityPack);
			}
		}
		catch(\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * @param int $resultId Id of result
	 */
	public function setResultId($resultId)
	{
		$this->resultId = $resultId;
	}

	/**
	 * @param int $formId Id of form
	 */
	public function setFormId($formId)
	{
		$this->formId = $formId;
	}

	/**
	 * @param array $formData Form data
	 */
	public function setFormData($formData)
	{
		$this->formData = $formData;
	}

	/**
	 * @param string $duplicateMode Duplicate mode code
	 */
	public function setDuplicateMode($duplicateMode)
	{
		$this->duplicateMode = $duplicateMode;
	}

	/**
	 * @param integer $assignedById Assigned by id
	 */
	public function setAssignedById($assignedById)
	{
		$this->assignedById = $assignedById;
	}

	/**
	 * @param integer $currencyId Currency Id
	 */
	public function setCurrencyId($currencyId)
	{
		$this->currencyId = $currencyId;
	}

	/**
	 * @param array $placeholders Placeholders
	 */
	public function setPlaceholders($placeholders = array())
	{
		$this->placeholders = $placeholders;
	}

	/**
	 * @param array $placeholders Placeholders
	 */
	public function setCommonFields($commonFields = array())
	{
		$this->commonFields = $commonFields;
	}


	/**
	 * @param string $entityTypeName Entity Type Name
	 * @return null|int
	 */
	public function getEntityIdByTypeName($entityTypeName)
	{
		foreach ($this->resultEntityPack as $packItem)
		{
			if ($entityTypeName == $packItem['ENTITY_NAME'])
			{
				return $packItem['ITEM_ID'];
			}
		}

		return null;
	}
}
