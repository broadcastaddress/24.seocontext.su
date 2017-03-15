<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main;
use Bitrix\Voximplant\Tts;

class CBPVoximplantCallActivity extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	private $callId;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			'Title'         => '',
			'OutputNumber'  => '',
			'Number'        => '',
			'Text'          => '',
			'VoiceLanguage' => '',
			'VoiceSpeed'    => '',
			'VoiceVolume'   => '',

			'UseAudioFile'  => 'N',
			'AudioFile'     => null,

			"WaitForResult" => 'N',

			//return
			'Result' => null,
			'ResultText' => '',
		);

		$this->SetPropertiesTypes(array(
			'ResultText' => array(
				'Type' => 'text',
			),
		));
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		$this->callId = null;
		$this->Result = null;
		$this->ResultText = '';
	}

	public function Cancel()
	{
		if ($this->WaitForResult == 'Y')
			$this->Unsubscribe($this);
		return CBPActivityExecutionStatus::Closed;
	}

	private function prepareNumbers($numbers)
	{
		if(is_string($numbers))
			$numbers = explode(',', $numbers);
		else
			$numbers = (array) $numbers;


		$result = array();

		foreach ($numbers as $number)
		{
			if (!$number || !is_scalar($number))
				continue;

			$normalized = \NormalizePhone($number, 1);
			if ($normalized)
				$result[] = $normalized;
		}

		return sizeof($result) > 0 ? $result[0] : '';
	}

	private function prepareTexts($texts)
	{
		$texts = (array) $texts;
		$result = array();

		foreach ($texts as $text)
		{
			if (!$text || !is_scalar($text))
				continue;

			$result[] = strip_tags($text);
		}

		return sizeof($result) == 1 ? $result[0] : $result;
	}

	private function prepareFiles($files)
	{
		$files = (array) $files;
		$result = array();

		foreach ($files as $file)
		{
			if (!$file || !is_scalar($file))
				continue;

			if (preg_match("#^(http[s]?)://#", $file))
			{
				$result[] = $file;
			}
			elseif (intval($file) > 0)
			{
				$fileArray = \CFile::GetFileArray($file);
				if (!is_array($fileArray))
					continue;

				$result[] = $fileArray['SRC']; // append protocol & domain?
			}
		}

		return sizeof($result) == 1 ? $result[0] : $result;
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("voximplant"))
			return CBPActivityExecutionStatus::Closed;

		if ($this->UseAudioFile != 'Y')
		{
			$this->callId = CVoxImplantOutgoing::StartInfoCallWithText(
				$this->OutputNumber,
				$this->prepareNumbers($this->ParseValue($this->getRawProperty('Number'), 'string')),
				$this->prepareTexts($this->ParseValue($this->getRawProperty('Text'), 'text')),
				$this->VoiceLanguage,
				$this->VoiceSpeed,
				$this->VoiceVolume
			);
		}
		else
		{
			$this->callId = CVoxImplantOutgoing::StartInfoCallWithSound(
				$this->OutputNumber,
				$this->prepareNumbers($this->Number),
				$this->prepareFiles($this->ParseValue($this->getRawProperty('AudioFile'), 'file'))
			);
		}

		if($this->callId === false)
		{
			$this->Result = false;
			return CBPActivityExecutionStatus::Closed;
		}

		if ($this->WaitForResult != 'Y')
			return CBPActivityExecutionStatus::Closed;

		$this->Subscribe($this);
		$this->WriteToTrackingService(GetMessage("BPVICA_TRACK_SUBSCR"));

		return CBPActivityExecutionStatus::Executing;
	}


	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$schedulerService->SubscribeOnEvent(
			$this->workflow->GetInstanceId(),
			$this->name,
			"voximplant",
			"OnInfoCallResult",
			$this->callId
		);

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}


	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$schedulerService->UnSubscribeOnEvent(
			$this->workflow->GetInstanceId(),
			$this->name,
			"voximplant",
			"OnInfoCallResult",
			$this->callId
		);

		$this->workflow->RemoveEventHandler($this->name, $eventHandler);
	}


	public function OnExternalEvent($arEventParameters = array())
	{
		$parameters = $arEventParameters[1];

		if(!is_array($parameters))
			return;
		
		if ($this->callId != $arEventParameters[0])
			return;

		$this->Result = ($parameters['RESULT'] ? 'Y' : 'N');

		$this->ResultText = sprintf('%s (%s)',
			GetMessage($this->Result ? 'BPVICA_RESULT_TRUE' : 'BPVICA_RESULT_FALSE'),
			$parameters['CODE']
		);

		$this->Unsubscribe($this);
		$this->workflow->CloseActivity($this);
	}

	public function HandleFault(Exception $exception)
	{
		if ($exception == null)
			throw new Exception("exception");

		$status = $this->Cancel();
		if ($status == CBPActivityExecutionStatus::Canceling)
			return CBPActivityExecutionStatus::Faulting;

		return $status;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $currentValues = null, $formName = "", $popupWindow = null, $currentSiteId = null)
	{
		if (!CModule::IncludeModule("voximplant"))
			return '<tr><td colspan="2" style="color: red">'.GetMessage('BPVICA_INCLUDE_MODULE').'</td></tr>';

		$runtime = CBPRuntime::GetRuntime();

		$propertiesMap = array(
			"OutputNumber" => "output_number",
			"Number" => "number",
			"UseAudioFile" => "use_audio_file",
			"Text" => "text",
			"VoiceLanguage" => "voice_language",
			"VoiceSpeed" => "voice_speed",
			"VoiceVolume" => "voice_volume",
			"AudioFile" => "audio_file",
			"WaitForResult" => "wait_for_result",
		);

		if (!is_array($currentValues))
		{
			$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			$currentValues = array();
			if (is_array($currentActivity["Properties"]))
			{
				foreach ($propertiesMap as $k => $v)
				{
					if (array_key_exists($k, $currentActivity["Properties"]))
					{
						$currentValues[$propertiesMap[$k]] = $currentActivity["Properties"][$k];
					}
					else
					{
						$currentValues[$propertiesMap[$k]] = "";
					}
				}
			}
			else
			{
				foreach ($propertiesMap as $k => $v)
					$currentValues[$propertiesMap[$k]] = "";
			}
		}

		if (empty($currentValues['output_number']))
			$currentValues['output_number'] = CVoxImplantConfig::GetPortalNumber();
		if (empty($currentValues['voice_language']))
			$currentValues['voice_language'] = Tts\Language::getDefaultVoice(Main\Context::getCurrent()->getLanguage());
		if (empty($currentValues['voice_speed']))
			$currentValues['voice_speed'] = Tts\Speed::getDefault();
		if (empty($currentValues['voice_volume']))
			$currentValues['voice_volume'] = Tts\Volume::getDefault();

		return $runtime->ExecuteResourceFile(
			__FILE__, "properties_dialog.php", array(
				"currentValues" => $currentValues,
				"outputNumber" => CVoxImplantConfig::GetPortalNumbers(false),
				"voiceLanguage" => Tts\Language::getList(),
				"voiceSpeed" => Tts\Speed::getList(),
				"voiceVolume" => Tts\Volume::getList(),
				"formName" => $formName,
				"documentType" => $documentType,
				"popupWindow" => &$popupWindow,
				'currentSiteId' => $currentSiteId,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$workflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$propertiesMap = array(
			"output_number" => "OutputNumber",
			"number" => "Number",
			"use_audio_file" => "UseAudioFile",
			"text" => "Text",
			"voice_language" => "VoiceLanguage",
			"voice_speed" => "VoiceSpeed",
			"voice_volume" => "VoiceVolume",
			"audio_file" => "AudioFile",
			"wait_for_result" => "WaitForResult",
		);

		$properties = array();
		foreach ($propertiesMap as $key => $value)
		{
			$properties[$value] = $arCurrentValues[$key];
		}

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
			return false;

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($workflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}

	public static function ValidateProperties($testProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = array();

		if (empty($testProperties['OutputNumber']))
		{
			$errors[] = array("code" => "NotExist", "parameter" => "OutputNumber", "message" => GetMessage("BPVICA_ERROR_OUTPUT_NUMBER"));
		}

		if (empty($testProperties['Number']))
		{
			$errors[] = array("code" => "NotExist", "parameter" => "Number", "message" => GetMessage("BPVICA_ERROR_NUMBER"));
		}

		if ($testProperties['UseAudioFile'] != 'Y' && empty($testProperties['Text']))
		{
			$errors[] = array("code" => "NotExist", "parameter" => "Text", "message" => GetMessage("BPVICA_ERROR_TEXT"));
		}

		if ($testProperties['UseAudioFile'] == 'Y' && empty($testProperties['AudioFile']))
		{
			$errors[] = array("code" => "NotExist", "parameter" => "AudioFile", "message" => GetMessage("BPVICA_ERROR_AUDIO_FILE"));
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

}