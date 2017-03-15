<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

class CBPCrmChangeResponsibleActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Responsible" => null,
		);
	}

	public function Execute()
	{
		if ($this->Responsible == null || !CModule::IncludeModule("crm"))
			return CBPActivityExecutionStatus::Closed;

		$documentId = $this->GetDocumentId();
		$fields = array('ASSIGNED_BY_ID' => $this->Responsible);

		$runtime = CBPRuntime::GetRuntime();
		/** @var CBPDocumentService $ds */
		$ds = $runtime->GetService('DocumentService');

		$ds->UpdateDocument($documentId, $fields);

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (empty($arTestProperties["Responsible"]))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "Responsible", "message" => GetMessage("CRM_CHANGE_RESPONSIBLE_EMPTY_PROP"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
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

		$dialog->setMap(array(
			'Responsible' => array(
				'Name' => GetMessage('CRM_CHANGE_RESPONSIBLE_NEW'),
				'FieldName' => 'responsible',
				'Type' => 'user',
				'Required' => true
			)
		));

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = Array();

		$arProperties = array(
			'Responsible' => CBPHelper::UsersStringToArray($arCurrentValues["responsible"], $documentType, $arErrors)
		);

		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}