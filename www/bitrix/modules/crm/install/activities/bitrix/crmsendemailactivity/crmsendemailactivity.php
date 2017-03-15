<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

class CBPCrmSendEmailActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Subject" => "",
			"MessageText" => '',
		);
	}

	public function Execute()
	{
		if (!$this->MessageText || !CModule::IncludeModule("crm") || !CModule::IncludeModule('subscribe'))
			return CBPActivityExecutionStatus::Closed;

		$ownerTypeID = $this->getEntityTypeId();
		$ownerID = $this->getEntityId();

		$userID = CCrmOwnerType::GetResponsibleID($ownerTypeID, $ownerID, false);
		if($userID <= 0)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		list($from, $userMailbox, $crmEmail) = $this->getFromEmail($userID);
		$to = $this->getToEmail($ownerTypeID, $ownerID);

		$errors = array();

		// Bindings & Communications -->
		$arBindings = array(
			array(
				'OWNER_TYPE_ID' => $ownerTypeID,
				'OWNER_ID' => $ownerID
			)
		);
		$arComms = array(array(
			'TYPE' => 'EMAIL',
			'VALUE' => $to,
			'ENTITY_ID' => $ownerID,
			'ENTITY_TYPE_ID' => $ownerTypeID
		));
		// <-- Bindings & Communications

		if(empty($to) || !empty($errors))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$subject = (string)$this->Subject;
		$message = $this->MessageText;

		if($message !== '')
		{
			CCrmActivity::AddEmailSignature($message, CCrmContentType::BBCode);
		}

		if($message === '')
		{
			$messageHtml = '';
		}
		else
		{
			//Convert BBCODE to HTML
			$parser = new CTextParser();
			$parser->allow['SMILES'] = 'N';
			$messageHtml = $parser->convertText($message);
		}

		$now = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL');
		if($subject === '')
		{
			$subject = GetMessage(
				'CRM_SEMA_DEFAULT_SUBJECT',
				array('#DATE#'=> $now)
			);
		}

		$description = $message;

		$arFields = array(
			'OWNER_ID' => $ownerID,
			'OWNER_TYPE_ID' => $ownerTypeID,
			'TYPE_ID' =>  CCrmActivityType::Email,
			'SUBJECT' => $subject,
			'START_TIME' => $now,
			'END_TIME' => $now,
			'COMPLETED' => 'Y',
			'RESPONSIBLE_ID' => $userID,
			'PRIORITY' => CCrmActivityPriority::Medium,
			'DESCRIPTION' => $description,
			'DESCRIPTION_TYPE' => CCrmContentType::BBCode,
			'DIRECTION' => CCrmActivityDirection::Outgoing,
			'BINDINGS' => array_values($arBindings),
		);

		if(!($ID = CCrmActivity::Add($arFields, false, false, array('REGISTER_SONET_EVENT' => true))))
		{
			$this->WriteToTrackingService(CCrmActivity::GetLastErrorMessage(), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$urn = CCrmActivity::PrepareUrn($arFields);
		if($urn !== '')
		{
			CCrmActivity::Update($ID, array('URN'=> $urn), false, false, array('REGISTER_SONET_EVENT' => true));
		}

		$messageId = sprintf(
			'<crm.activity.%s@%s>', $urn,
			defined('BX24_HOST_NAME') ? BX24_HOST_NAME : (
			defined('SITE_SERVER_NAME') && SITE_SERVER_NAME
				? SITE_SERVER_NAME : \COption::getOptionString('main', 'server_name', '')
			)
		);

		CCrmActivity::SaveCommunications($ID, $arComms, $arFields, false, false);

		// Creating Email -->

		if(!empty($errors))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		// Try to resolve posting charset -->
		$postingCharset = '';
		$siteCharset = defined('LANG_CHARSET') ? LANG_CHARSET : (defined('SITE_CHARSET') ? SITE_CHARSET : 'windows-1251');
		$arSupportedCharset = explode(',', COption::GetOptionString('subscribe', 'posting_charset'));
		if(count($arSupportedCharset) === 0)
		{
			$postingCharset = $siteCharset;
		}
		else
		{
			foreach($arSupportedCharset as $curCharset)
			{
				if(strcasecmp($curCharset, $siteCharset) === 0)
				{
					$postingCharset = $curCharset;
					break;
				}
			}

			if($postingCharset === '')
			{
				$postingCharset = $arSupportedCharset[0];
			}
		}
		//<-- Try to resolve posting charset

		$postingData = array(
			'STATUS' => 'D',
			'FROM_FIELD' => $from,
			'TO_FIELD' => implode(',', empty($userMailbox) ? array_merge(array($from), (array)$to) : (array)$to),
			//'BCC_FIELD' => implode(',', $to),
			'SUBJECT' => $subject,
			'BODY_TYPE' => 'html',
			'BODY' => $messageHtml !== '' ? $messageHtml : GetMessage('CRM_EMAIL_ACTION_DEFAULT_DESCRIPTION'),
			'DIRECT_SEND' => 'Y',
			'SUBSCR_FORMAT' => 'html',
			'CHARSET' => $postingCharset
		);

		CCrmActivity::InjectUrnInMessage(
			$postingData,
			$urn,
			CCrmEMailCodeAllocation::GetCurrent()
		);

		$posting = new CPosting();
		$postingID = $posting->Add($postingData);
		if($postingID === false)
		{
			$errors[] = $posting->LAST_ERROR;
		}
		else
		{
			if(empty($errors))
			{
				$arUpdateFields = array(
					'ASSOCIATED_ENTITY_ID' => $postingID,
					'SETTINGS' => array('MESSAGE_HEADERS' => array('Message-Id' => $messageId))
				);

				$fromEmail = strtolower(trim(CCrmMailHelper::extractEmail($from)));
				if ($crmEmail !== '' && $fromEmail !== $crmEmail)
				{
					$arUpdateFields['SETTINGS']['MESSAGE_HEADERS']['Reply-To'] = sprintf('<%s>, <%s>', $fromEmail, $crmEmail);
				}
				CCrmActivity::Update($ID, $arUpdateFields, false, false);
			}
		}
		// <-- Creating Email

		if(!empty($errors))
		{
			CCrmActivity::Delete($ID);
			return CBPActivityExecutionStatus::Closed;
		}

		if (!empty($userMailbox))
		{
			class_exists('Bitrix\Mail\Helper');

			$rcpt = '';
			foreach ((array)$to as $item)
				$rcpt[] = \Bitrix\Mail\DummyMail::encodeHeaderFrom($item, SITE_CHARSET);
			$rcpt = join(', ', $rcpt);

			$outgoing = new \Bitrix\Mail\DummyMail(array(
				'CONTENT_TYPE' => 'html',
				'CHARSET'      => SITE_CHARSET,
				'HEADER'       => array(
					'From'       => $from,
					'To'         => $rcpt,
					'Subject'    => $subject,
					'Message-Id' => $messageId,
				),
				'BODY'         => $messageHtml ?: getMessage('CRM_EMAIL_ACTION_DEFAULT_DESCRIPTION'),
			));

			\Bitrix\Mail\Helper::addImapMessage($userMailbox, (string) $outgoing, $error);
		}

		// Sending Email -->
		if($posting->ChangeStatus($postingID, 'P'))
		{
			$rsAgents = CAgent::GetList(
				array('ID'=>'DESC'),
				array(
					'MODULE_ID' => 'subscribe',
					'NAME' => 'CPosting::AutoSend('.$postingID.',%',
				)
			);

			if(!$rsAgents->Fetch())
			{
				CAgent::AddAgent('CPosting::AutoSend('.$postingID.',true);', 'subscribe', 'N', 0);
			}
		}

		// Try add event to entity
		$CCrmEvent = new CCrmEvent();

		$eventText  = '';
		$eventText .= GetMessage('CRM_SEMA_EMAIL_SUBJECT').': '.$subject."\n\r";
		$eventText .= GetMessage('CRM_SEMA_EMAIL_FROM').': '.$from."\n\r";
		$eventText .= GetMessage('CRM_SEMA_EMAIL_TO').': '.implode(',', (array)$to)."\n\r\n\r";
		$eventText .= $messageHtml;
		// Register event only for owner
		$CCrmEvent->Add(
			array(
				'ENTITY' => array(
					$ownerID => array(
						'ENTITY_TYPE' => \CCrmOwnerType::ResolveName($ownerTypeID),
						'ENTITY_ID' => $ownerID
					)
				),
				'EVENT_ID' => 'MESSAGE',
				'EVENT_TEXT_1' => $eventText
			)
		);
		// <-- Sending Email

		return CBPActivityExecutionStatus::Closed;
	}

	private function getFromEmail($userId)
	{
		$from = CUserOptions::GetOption('crm', 'activity_email_addresser', '', $userId);
		$userMailbox = $crmEmail = null;

		if (CModule::includeModule('mail'))
		{
			$res = \Bitrix\Mail\MailboxTable::getList(array(
				'select' => array('*', 'LANG_CHARSET' => 'SITE.CULTURE.CHARSET'),
				'filter' => array('LID' => SITE_ID, 'ACTIVE' => 'Y', 'USER_ID' => array($userId, 0)),
				'order'  => array('USER_ID' => 'DESC', 'TIMESTAMP_X' => 'DESC'),
			));

			while ($mailbox = $res->fetch())
			{
				if (!$mailbox['USER_ID'] && $mailbox['SERVER_TYPE'] != 'imap')
					continue;

				if (!empty($mailbox['OPTIONS']['flags']) && in_array('crm_connect', $mailbox['OPTIONS']['flags']))
				{
					$userMailbox = $mailbox;

					$email = $mailbox['USER_ID'] > 0 ? $mailbox['LOGIN'] : $mailbox['NAME'];
					if (strpos($email, '@') > 0)
						$crmEmail = $email;

					break;
				}
			}
		}

		if (empty($crmEmail))
			$crmEmail = \CCrmMailHelper::extractEmail(\COption::getOptionString('crm', 'mail', ''));

		if (!$from)
			$from = $crmEmail;

		if (!$from)
		{
			$users = CUser::GetList(
				($by='ID'),
				($order='ASC'),
				array('=ID' => $userId),
				array(
					'FIELDS' => array('EMAIL'),
					'NAV_PARAMS' => array('nTopCount' => 1)
				)
			);
			$arUser = $users ? $users->fetch() : null;
			if ($arUser)
			{
				$from = $arUser['EMAIL'];
			}
		}

		return array($from, $userMailbox, $crmEmail);
	}

	private function getToEmail($entityTypeId, $entityId)
	{
		$to = '';
		if ($entityTypeId == \CCrmOwnerType::Lead)
		{
			$to = $this->getEntityEmail($entityTypeId, $entityId);
		}
		elseif ($entityTypeId == \CCrmOwnerType::Deal)
		{
			$entity = \CCrmDeal::GetByID($entityId, false);
			$entityContactID = isset($entity['CONTACT_ID']) ? intval($entity['CONTACT_ID']) : 0;
			$entityCompanyID = isset($entity['COMPANY_ID']) ? intval($entity['COMPANY_ID']) : 0;

			if($entityContactID > 0)
			{
				$to = $this->getEntityEmail(\CCrmOwnerType::Contact, $entityContactID);
			}
			if (empty($to) && $entityCompanyID > 0)
			{
				$to = $this->getEntityEmail(\CCrmOwnerType::Company, $entityCompanyID);
			}
		}

		return $to;
	}

	private function getEntityEmail($entityTypeId, $entityId)
	{
		$result = '';
		$dbResFields = CCrmFieldMulti::GetList(
			array('ID' => 'asc'),
			array(
				'ENTITY_ID' => \CCrmOwnerType::ResolveName($entityTypeId),
				'ELEMENT_ID' => $entityId,
				'TYPE_ID' => \CCrmFieldMulti::EMAIL
			)
		);

		while($arField = $dbResFields->Fetch())
		{
			if(empty($arField['VALUE']))
			{
				continue;
			}

			$result = $arField['VALUE'];
			break;
		}

		return $result;
	}

	private function getEntityTypeId()
	{
		$id = $this->GetDocumentId();
		$typeId = \CCrmOwnerType::Undefined;
		if ($id[1] == 'CCrmDocumentDeal')
			$typeId = \CCrmOwnerType::Deal;
		elseif ($id[1] == 'CCrmDocumentLead')
			$typeId = \CCrmOwnerType::Lead;
		return $typeId;
	}

	private function getEntityId()
	{
		//extract real entity id from string like LEAD_123 or DEAL_345
		$id = $this->GetDocumentId();
		$pairs = explode('_', $id[2]);

		return count($pairs) > 1 ? $pairs[1] : $pairs[0];
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (empty($arTestProperties["MessageText"]))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageText", "message" => GetMessage("CRM_SEMA_EMPTY_PROP"));
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
			'Subject' => array(
				'Name' => GetMessage('CRM_SEMA_EMAIL_SUBJECT'),
				'FieldName' => 'subject',
				'Type' => 'string',
				'Required' => true
			),
			'MessageText' => array(
				'Name' => GetMessage('CRM_SEMA_MESSAGE_TEXT'),
				'FieldName' => 'message_text',
				'Type' => 'text',
				'Required' => true
			)
		));

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = Array();

		$arProperties = array(
			'Subject' => (string)$arCurrentValues["subject"],
			'MessageText' => (string)$arCurrentValues["message_text"]
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