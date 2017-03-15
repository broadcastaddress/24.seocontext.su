<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

class CBPCrmCreateCallActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Subject" => null,
			"StartTime" => null,
			"EndTime" => null,
			"IsImportant" => null,
			"Description" => null,
			"NotifyType" => null,
			"NotifyValue" => null,
			"Responsible" => null,
			"AutoComplete" => null,
			//return
			"Id" => null
		);

		$this->SetPropertiesTypes(array(
			'Id' => array(
				'Type' => 'int'
			)
		));
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("crm"))
			return CBPActivityExecutionStatus::Closed;

		$start = (string)$this->StartTime;
		$end = (string)$this->EndTime;

		if ($start === '')
			$start = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL');

		if ($end === '')
			$end = $start;

		$activityFields = array(
			'START_TIME' => $start,
			'END_TIME' => $end,
			'TYPE_ID' =>  \CCrmActivityType::Call,
			'SUBJECT' => (string)$this->Subject,
			'PRIORITY' => ($this->IsImportant == 'Y') ? \CCrmActivityPriority::High : CCrmActivityPriority::Medium,
			'DESCRIPTION' => (string)$this->Description,
			'DESCRIPTION_TYPE' => CCrmContentType::PlainText,
			'NOTIFY_TYPE' => (int)$this->NotifyType,
			'NOTIFY_VALUE' => (int)$this->NotifyValue,
			'PROVIDER_ID' => \Bitrix\Crm\Activity\Provider\Call::getId(),
			'PROVIDER_TYPE_ID' => \Bitrix\Crm\Activity\Provider\Call::getTypeId(array()),
			'DIRECTION' => CCrmActivityDirection::Outgoing,
			'RESPONSIBLE_ID' => $this->getResponsibleId()
		);

		$activityFields['BINDINGS'] = $this->getBindings();
		$communications = $this->getCommunications();

		if (empty($activityFields['SUBJECT']) && !empty($communications))
		{
			$arCommInfo = array(
				'ENTITY_ID' => $communications[0]['ENTITY_ID'],
				'ENTITY_TYPE_ID' => $communications[0]['ENTITY_TYPE_ID']
			);
			CCrmActivity::PrepareCommunicationInfo($arCommInfo);

			$activityFields['SUBJECT'] = \Bitrix\Crm\Activity\Provider\Call::generateSubject(
				$activityFields['PROVIDER_TYPE_ID'],
				CCrmActivityDirection::Outgoing,
				array(
					'#DATE#'=> $activityFields['START_TIME'],
					'#TITLE#' => isset($arCommInfo['TITLE']) ? $arCommInfo['TITLE'] : '',
					'#COMMUNICATION#' => ''
				)
			);
		}

		if(!($id = CCrmActivity::Add($activityFields, false, true, array('REGISTER_SONET_EVENT' => true))))
		{
			$this->WriteToTrackingService(CCrmActivity::GetLastErrorMessage());
			return CBPActivityExecutionStatus::Closed;
		}

		if ($id > 0)
		{
			$this->Id = $id;
			CCrmActivity::SaveCommunications($id, $communications, $activityFields, false, false);
			$this->WriteToTrackingService($id, 0, CBPTrackingType::AttachedEntity);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function getResponsibleId()
	{
		$id = $this->Responsible;
		if (!$id)
		{
			$runtime = CBPRuntime::GetRuntime();
			$runtime->StartRuntime();
			/** @var CBPDocumentService $documentService */
			$documentService = $runtime->GetService('DocumentService');
			$document = $documentService->GetDocument($this->GetDocumentId());

			$id = isset($document['ASSIGNED_BY_ID']) ? $document['ASSIGNED_BY_ID'] : null;

		}

		return CBPHelper::ExtractUsers($id, $this->GetDocumentId(), true);
	}

	private function getBindings()
	{
		$documentId = $this->GetDocumentId();
		$ownerTypeId = CCrmOwnerType::Undefined;
		$ownerId = 0;

		switch ($documentId[1])
		{
			case 'CCrmDocumentDeal':
				$ownerTypeId = CCrmOwnerType::Deal;
				$ownerId = (int)str_replace('DEAL_', '', $documentId[2]);
				break;

			case 'CCrmDocumentLead':
				$ownerTypeId = CCrmOwnerType::Lead;
				$ownerId = (int)str_replace('LEAD_', '', $documentId[2]);
				break;
		}

		return array(
			array('OWNER_TYPE_ID' => $ownerTypeId, 'OWNER_ID' => $ownerId)
		);
	}

	private function getCommunications()
	{
		$documentId = $this->GetDocumentId();
		$communications = array();

		switch ($documentId[1])
		{
			case 'CCrmDocumentDeal':
				$communications = $this->getDealCommunications((int)str_replace('DEAL_', '', $documentId[2]));
				break;
			case 'CCrmDocumentLead':
				$communications = $this->getCommunicationsFromFM(CCrmOwnerType::Lead, (int)str_replace('LEAD_', '', $documentId[2]));
				break;
		}

		$communications = array_slice($communications, 0, 1);
		return $communications;
	}

	private function getDealCommunications($id)
	{
		$communications = array();

		$entity = CCrmDeal::GetByID($id);
		if(!$entity)
		{
			return array();
		}

		$entityContactID = isset($entity['CONTACT_ID']) ? intval($entity['CONTACT_ID']) : 0;
		$entityCompanyID = isset($entity['COMPANY_ID']) ? intval($entity['COMPANY_ID']) : 0;

		if ($entityContactID > 0)
		{
			$communications = $this->getCommunicationsFromFM(CCrmOwnerType::Contact, $entityContactID);
		}

		if (empty($communications) && $entityCompanyID > 0)
		{
			$communications = CCrmActivity::GetCompanyCommunications($entityCompanyID, 'PHONE');
		}

		if (empty($communications))
		{
			$communications = CCrmActivity::GetCommunicationsByOwner('DEAL', $id, 'PHONE');
		}

		return $communications;
	}

	private function getCommunicationsFromFM($entityTypeId, $entityId)
	{
		$entityTypeName = CCrmOwnerType::ResolveName($entityTypeId);
		$communications = array();

		$iterator = CCrmFieldMulti::GetList(
			array('ID' => 'asc'),
			array('ENTITY_ID' => $entityTypeName,
				'ELEMENT_ID' => $entityId,
				'TYPE_ID' => 'PHONE'
			)
		);

		while ($row = $iterator->fetch())
		{
			if (empty($row['VALUE']))
				continue;

			$communications[] = array(
				'ENTITY_ID' => $entityId,
				'ENTITY_TYPE_ID' => $entityTypeId,
				'ENTITY_TYPE' => $entityTypeName,
				'TYPE' => 'PHONE',
				'VALUE' => $row['VALUE'],
				'VALUE_TYPE' => $row['VALUE_TYPE']
			);
		}

		return $communications;
	}

	public static function ValidateProperties($testProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = array();
		$fieldsMap = static::getPropertiesDialogMap();

		foreach ($fieldsMap as $propertyKey => $fieldProperties)
		{
			if (
				CBPHelper::getBool($fieldProperties['Required'])
				&& CBPHelper::isEmptyValue($testProperties[$propertyKey])
			)
				$errors[] = array(
					"code" => "NotExist",
					"parameter" => $propertyKey,
					"message" => GetMessage("CRM_CREATE_CALL_EMPTY_PROP", array('#PROPERTY#' => $fieldProperties['Name']))
				);
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null, $siteId = '')
	{
		if (!CModule::IncludeModule("crm"))
			return '';

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
			'siteId' => $siteId
		));

		$dialog->setMap(static::getPropertiesDialogMap());

		return $dialog;
	}

	private static function getPropertiesDialogMap()
	{
		$notifyTypes = \CCrmActivityNotifyType::GetAllDescriptions();
		unset($notifyTypes[\CCrmActivityNotifyType::None]);

		return array(
			'Subject' => array(
				'Name' => GetMessage('CRM_CREATE_CALL_SUBJECT'),
				'FieldName' => 'subject',
				'Type' => 'string'
			),
			'StartTime' => array(
				'Name' => GetMessage('CRM_CREATE_CALL_START_TIME'),
				'FieldName' => 'start_time',
				'Type' => 'datetime'
			),
			'EndTime' => array(
				'Name' => GetMessage('CRM_CREATE_CALL_END_TIME'),
				'FieldName' => 'end_time',
				'Type' => 'datetime'
			),
			'Description' => array(
				'Name' => GetMessage('CRM_CREATE_CALL_DESCRIPTION'),
				'FieldName' => 'description',
				'Type' => 'text'
			),
			'NotifyValue' => array(
				'Name' => GetMessage('CRM_CREATE_CALL_NOTIFY_VALUE'),
				'FieldName' => 'notify_value',
				'Type' => 'int'
			),
			'NotifyType' => array(
				'Name' => GetMessage('CRM_CREATE_CALL_NOTIFY_TYPE'),
				'FieldName' => 'notify_type',
				'Type' => 'select',
				'Options' => $notifyTypes
			),
			'Responsible' => array(
				'Name' => GetMessage('CRM_CREATE_CALL_RESPONSIBLE_ID'),
				'FieldName' => 'responsible',
				'Type' => 'user',
				'Default' => 'author'
			),
			'IsImportant' => array(
				'Name' => GetMessage('CRM_CREATE_CALL_IS_IMPORTANT'),
				'FieldName' => 'is_important',
				'Type' => 'bool'
			),
			/*'AutoComplete' => array(
				'Name' => GetMessage('CRM_CREATE_CALL_AUTO_COMPLETE'),
				'FieldName' => 'auto_completed',
				'Type' => 'bool'
			)*/
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $currentValues, &$errors)
	{
		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();

		$errors = $properties = array();
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService('DocumentService');

		$fieldsMap = static::getPropertiesDialogMap();
		foreach ($fieldsMap as $propertyKey => $fieldProperties)
		{
			$field = $documentService->getFieldTypeObject($documentType, $fieldProperties);
			if (!$field)
				continue;

			$properties[$propertyKey] = $field->extractValue(
				array('Field' => $fieldProperties['FieldName']),
				$currentValues,
				$errors
			);
		}

		//convert special robot datetime interval
		$startTimeFieldsPrefix = $fieldsMap['StartTime']['FieldName'];
		if (isset($currentValues[$startTimeFieldsPrefix.'_interval_d']) && isset($currentValues[$startTimeFieldsPrefix.'_interval_t']))
		{
			$interval = array('d' => $currentValues[$startTimeFieldsPrefix.'_interval_d']);
			$time = \Bitrix\Crm\Automation\Helper::parseTimeString($currentValues[$startTimeFieldsPrefix.'_interval_t']);
			$interval['h'] = $time['h'];
			$interval['i'] = $time['i'];
			$properties['StartTime'] = \Bitrix\Crm\Automation\Helper::getDateTimeIntervalString($interval);
			++$interval['h'];
			$properties['EndTime'] = \Bitrix\Crm\Automation\Helper::getDateTimeIntervalString($interval);
		}

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
			return false;

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity['Properties'] = $properties;

		return true;
	}
}