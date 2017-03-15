<?php

namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Chat
{
	const FIELD_SESSION = 'LINES_SESSION';
	const FIELD_CONFIG = 'LINES_CONFIG';
	const FIELD_SILENT_MODE = 'LINES_SILENT_MODE';
	const FIELD_LIVECHAT = 'LIVECHAT_SESSION';

	public static $fieldAssoc = Array(
		'LINES_SESSION' => 'ENTITY_DATA_1',
		'LINES_CONFIG' => 'ENTITY_DATA_2',
		'LINES_SILENT_MODE' => 'ENTITY_DATA_3',
		'LIVECHAT_SESSION' => 'ENTITY_DATA_1',
	);

	const TRANSFER_MODE_AUTO = 'AUTO';
	const TRANSFER_MODE_MANUAL = 'MANUAL';
	const TRANSFER_MODE_BOT = 'BOT';

	private $error = null;
	private $moduleLoad = false;
	private $isCreated = false;
	private $joinByUserId = 0;

	public function __construct($chatId = 0)
	{
		$imLoad = \Bitrix\Main\Loader::includeModule('im');
		$pullLoad = \Bitrix\Main\Loader::includeModule('pull');
		if ($imLoad && $pullLoad)
		{
			$this->error = new Error(null, '', '');
			$this->moduleLoad = true;
		}
		else
		{
			if (!$imLoad)
			{
				$this->error = new Error(__METHOD__, 'IM_LOAD_ERROR', Loc::getMessage('IMOL_CHAT_ERROR_IM_LOAD'));
			}
			elseif (!$pullLoad)
			{
				$this->error = new Error(__METHOD__, 'PULL_LOAD_ERROR', Loc::getMessage('IMOL_CHAT_ERROR_PULL_LOAD'));
			}
		}
		$chatId = intval($chatId);
		if ($chatId > 0)
		{
			$orm = \Bitrix\Im\Model\ChatTable::getById($chatId);
			if ($chat = $orm->fetch())
			{
				$this->chat = $chat;
				$this->isDataLoaded = true;
			}
		}
	}

	private function isModuleLoad()
	{
		return $this->moduleLoad;
	}

	public function load($params)
	{
		if (!$this->isModuleLoad())
		{
			return false;
		}
		$orm = \Bitrix\Im\Model\ChatTable::getList(array(
			'filter' => array(
				'=ENTITY_TYPE' => 'LINES',
				'=ENTITY_ID' => $params['USER_CODE']
			),
			'limit' => 1
		));
		if($chat = $orm->fetch())
		{
			$this->chat = $chat;
			$this->isDataLoaded = true;
			return true;
		}
		else if ($params['ONLY_LOAD'] == 'Y')
		{
			return false;
		}

		list($connectorId) = explode('|', $params['USER_CODE']);

		$avatarId = 0;
		$userName = '';
		$chatColorCode = '';
		$addChat['USERS'] = false;
		if ($params['USER_ID'])
		{
			$orm = \Bitrix\Main\UserTable::getById($params['USER_ID']);
			if ($user = $orm->fetch())
			{
				if ($user['PERSONAL_PHOTO'] > 0)
				{
					$avatarId = \CFile::CopyFile($user['PERSONAL_PHOTO']);
				}
				$addChat['USERS'] = Array($params['USER_ID']);

				if ($connectorId != 'livechat' || !empty($user['NAME']))
				{
					$userName = \Bitrix\Im\User::getInstance($params['USER_ID'])->getFullName();
				}
				$chatColorCode = \Bitrix\Im\Color::getCodeByNumber($params['USER_ID']);
				if (\Bitrix\Im\User::getInstance($params['USER_ID'])->getGender() == 'M')
				{
					$replaceColor = \Bitrix\Im\Color::getReplaceColors();
					if (isset($replaceColor[$chatColorCode]))
					{
						$chatColorCode = $replaceColor[$chatColorCode];
					}
				}
			}
		}

		$description = '';
		if (isset($params['CONNECTOR']['chat']['description']))
		{
			$description = trim($params['CONNECTOR']['chat']['description']);
		}

		if (!$userName)
		{
			$result = self::getGuestName($chatColorCode);
			$userName = $result['USER_NAME'];
			$chatColorCode = $result['USER_COLOR'];
		}

		$addChat['TITLE'] = Loc::getMessage('IMOL_CHAT_CHAT_NAME', Array("#USER_NAME#" => $userName, "#LINE_NAME#" => $params['LINE_NAME']));

		$addChat['TYPE'] = IM_MESSAGE_CHAT;
		$addChat['AVATAR_ID'] = $avatarId;
		$addChat['COLOR'] = $chatColorCode;
		$addChat['DESCRIPTION'] = $description;
		$addChat['ENTITY_TYPE'] = 'LINES';
		$addChat['ENTITY_ID'] = $params['USER_CODE'];
		$addChat['SKIP_ADD_MESSAGE'] = 'Y';

		$chat = new \CIMChat(0);
		$id = $chat->Add($addChat);
		if (!$id)
		{
			return false;
		}

		$orm = \Bitrix\Im\Model\ChatTable::getById($id);
		$this->chat = $orm->fetch();
		$this->isCreated = true;
		$this->isDataLoaded = true;

		return true;
	}

	public function answer($userId, $skipSession = false, $skipMessage = false)
	{
		if (!$skipSession)
		{
			$session = new Session();
			$result = $session->load(Array(
				'USER_CODE' => $this->chat['ENTITY_ID'],
				'MODE' => Session::MODE_OUTPUT,
				'OPERATOR_ID' => $userId
			));
			if (!$result)
			{
				return false;
			}
			else if($session->isNowCreated())
			{
				return true;
			}
		}

		if ($skipSession)
		{
			list(, $lineId) = explode('|', $this->chat['ENTITY_ID']);
			$configManager = new Config();
			$config = $configManager->get($lineId);
		}
		else
		{
			$session->setOperatorId($userId, false);
			if ($session->getData('CRM_ACTIVITY_ID') > 0)
			{
				$crmManager = new Crm();
				$crmManager->updateActivity(Array(
					'ID' => $session->getData('CRM_ACTIVITY_ID'),
					'UPDATE' => Array(
						'ANSWERED' => 'Y'
					)
				));
			}

			$sessionUpdate = Array(
				'OPERATOR_ID' => $userId,
				'WAIT_ACTION' => 'N',
				'WAIT_ANSWER' => 'N',
				'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime(),
				'SKIP_DATE_CLOSE' => 'Y'
			);
			if (!\Bitrix\Im\User::getInstance($userId)->isBot() && $session->getData('DATE_OPERATOR_ANSWER') <= 0)
			{
				$currentDate = new \Bitrix\Main\Type\DateTime();
				$sessionUpdate['DATE_OPERATOR_ANSWER'] = $currentDate;
				$sessionUpdate['TIME_ANSWER'] = $currentDate->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp();
			}

			$session->update($sessionUpdate);

			$config = $session->getConfig();
		}

		$chat = new \CIMChat(0);
		$relations = \CIMChat::GetRelationById($this->chat['ID']);
		
		foreach ($relations as $relation)
		{
			if ($userId == $relation['USER_ID'])
				continue;
			
			if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isConnector())
				continue;
			
			if ($config['WELCOME_BOT_LEFT'] == Config::BOT_LEFT_CLOSE && \Bitrix\Im\User::getInstance($relation['USER_ID'])->isBot())
				continue;
			
			$chat->DeleteUser($this->chat['ID'], $relation['USER_ID'], false, true);
		}
		
		$this->update(Array(
			'AUTHOR_ID' => $userId
		));

		\CPullStack::AddByUser($userId, Array(
			'module_id' => 'imopenlines',
			'command' => 'linesAnswer',
			'params' => Array(
				'chatId' => $this->chat['ID']
			)
		));
		
		if (!$skipMessage)
		{
			$userAnswer = \Bitrix\Im\User::getInstance($userId);

			\CIMChat::AddMessage(Array(
				"FROM_USER_ID" => $userId,
				"TO_CHAT_ID" => $this->chat['ID'],
				"MESSAGE" => Loc::getMessage('IMOL_CHAT_ANSWER_'.$userAnswer->getGender(), Array('#USER#' => '[USER='.$userAnswer->getId().']'.$userAnswer->getFullName().'[/USER]')),
				"SYSTEM" => 'Y',
			));
		}

		return true;
	}

	public function skip($userId = 0)
	{
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID'],
			'SKIP_CREATE' => 'Y'
		));
		if (!$result)
		{
			if ($userId > 0)
			{
				$chat = new \CIMChat();
				$chat->DeleteUser($this->chat['ID'], $userId, false);
			}
			
			return false;
		}

		if ($userId)
		{
			$userSkip = \Bitrix\Im\User::getInstance($userId);

			\CIMChat::AddMessage(Array(
				"FROM_USER_ID" => $userId,
				"TO_CHAT_ID" => $this->chat['ID'],
				"MESSAGE" => Loc::getMessage('IMOL_CHAT_SKIP_'.$userSkip->getGender(), Array('#USER#' => '[USER='.$userSkip->getId().']'.$userSkip->getFullName().'[/USER]')),
				"SYSTEM" => 'Y',
			));
		}

		if ($session->getConfig('QUEUE_TYPE') == Config::QUEUE_TYPE_ALL && $userId)
		{
			$count = 0;
			$relations = \CIMChat::GetRelationById($this->chat['ID']);
			foreach ($relations as $relation)
			{
				if ($userId == $relation['USER_ID'])
					continue;
				
				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isConnector())
					continue;
				
				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isBot())
					continue;
				
				$count++;
			}
			if ($count > 0)
			{
				$chat = new \CIMChat(0);
				$chat->DeleteUser($this->chat['ID'], $userId, false, true);
			}
			else
			{
				$session->transferToNextInQueue();
			}
		}
		else
		{
			$session->transferToNextInQueue();
		}

		return true;
	}

	public function endBotSession()
	{
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if ($result && \Bitrix\Im\User::getInstance($session->getData('OPERATOR_ID'))->isBot())
		{
			if ($session->getConfig('QUEUE_TYPE') == Config::QUEUE_TYPE_ALL)
			{
				$chat = new \CIMChat(0);
				if ($session->getConfig('WELCOME_BOT_LEFT') != Config::BOT_LEFT_CLOSE)
				{
					$chat->DeleteUser($this->chat['ID'], $session->getData('OPERATOR_ID'), false, true);
				}
				else
				{
					$chat->SetOwner($this->chat['ID'], 0);
				}
				
				$queue = $session->getQueue();
				$this->join($queue['USER_LIST'], true);
				
				\CIMChat::AddMessage(Array(
					"TO_CHAT_ID" => $this->chat['ID'],
					"MESSAGE" => Loc::getMessage('IMOL_CHAT_TO_QUEUE'),
					"SYSTEM" => 'Y',
				));
			}
			else
			{
				$session->transferToNextInQueue();
			}
			
			return true;
		}

		return false;
	}

	public function waitAnswer($userId)
	{
		$this->update(Array(
			'AUTHOR_ID' => $userId,
			self::getFieldName(self::FIELD_SILENT_MODE) => 'N'
		));
	}

	public function transfer($params)
	{
		$mode = in_array($params['MODE'], Array(self::TRANSFER_MODE_AUTO, self::TRANSFER_MODE_BOT))? $params['MODE']: self::TRANSFER_MODE_MANUAL;
		$selfExit = isset($params['LEAVE']) && $params['LEAVE'] == 'N'? false: true;

		$chat = new \CIMChat(0);
		if (substr($params['TO'], 0, 5) == 'queue')
		{
			$queueId = intval(substr($params['TO'], 5));

			$config = \Bitrix\ImOpenlines\Model\ConfigTable::getById($queueId)->fetch();
			if (!$config)
			{
				return false;
			}

			$session = new Session();
			$result = $session->load(Array(
				'USER_CODE' => $this->chat['ENTITY_ID']
			));
			if (!$result)
			{
				return false;
			}

			Log::write(Array($params['FROM'], $queueId), 'TRANSFER TO LINE');

			$session->setOperatorId(0, true);
			$this->update(Array('AUTHOR_ID' => 0));

			$userFrom = \Bitrix\Im\User::getInstance($params['FROM']);

			if ($mode != self::TRANSFER_MODE_AUTO)
			{
				\Bitrix\ImOpenLines\Model\OperatorTransferTable::Add(Array(
					'CONFIG_ID' => $session->getData('CONFIG_ID'),
					'SESSION_ID' => $session->getData('ID'),
					'USER_ID' => $params['FROM'],
					'TRANSFER_TYPE' => 'queue',
					'TRANSFER_USER_ID' => $queueId
				));
			}

			$lineFromId = $session->getConfig('ID');
			$lineFrom = $session->getConfig('LINE_NAME');

			if ($userFrom->isBot() && !$session->getData('DATE_OPERATOR'))
			{
				$currentDate = new \Bitrix\Main\Type\DateTime();
				$session->update(Array(
					'CONFIG_ID' => $queueId,
					'DATE_OPERATOR' => $currentDate,
					'QUEUE_HISTORY' => Array(),
					'TIME_BOT' => $currentDate->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp()
				));
			}
			else
			{
				$session->update(Array(
					'CONFIG_ID' => $queueId,
					'QUEUE_HISTORY' => Array(),
				));
			}
			$lineTo = $session->getConfig('LINE_NAME');

			if ($lineFromId == $queueId)
			{
				$message = Loc::getMessage('IMOL_CHAT_SKIP_'.$userFrom->getGender(), Array(
					'#USER#' => '[USER='.$userFrom->getId().']'.$userFrom->getFullName().'[/USER]',
				));
			}
			else
			{
				$message = Loc::getMessage('IMOL_CHAT_TRANSFER_LINE_'.$userFrom->getGender(), Array(
					'#USER_FROM#' => '[USER='.$userFrom->getId().']'.$userFrom->getFullName().'[/USER]',
					'#LINE_FROM#' => '[b]'.$lineFrom.'[/b]',
					'#LINE_TO#' => '[b]'.$lineTo.'[/b]',
				));
			}
			
			$queue = $session->getQueue();
			
			$chat = new \CIMChat(0);
			$relations = \CIMChat::GetRelationById($this->chat['ID']);
			foreach ($relations as $relation)
			{
				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isConnector())
					continue;
				
				if (\Bitrix\Im\User::getInstance($relation['USER_ID'])->isBot())
					continue;
				
				if (!in_array($relation['USER_ID'], $queue['USER_LIST']))
				{
					$chat->DeleteUser($this->chat['ID'], $relation['USER_ID'], false, true);
				}
			}
			
			if ($session->getConfig('QUEUE_TYPE') == Config::QUEUE_TYPE_ALL)
			{
				$chat = new \CIMChat(0);
				$chat->SetOwner($this->chat['ID'], 0);
				
				$this->join($queue['USER_LIST'], true);
				
				\CIMChat::AddMessage(Array(
					"TO_CHAT_ID" => $this->chat['ID'],
					"MESSAGE" => $message,
					"SYSTEM" => 'Y',
				));
			}
			else
			{
				\CIMChat::AddMessage(Array(
					"TO_CHAT_ID" => $this->chat['ID'],
					"MESSAGE" => $message,
					"SYSTEM" => 'Y',
				));
				
				$session->transferToNextInQueue(false);
			}
		}
		else
		{
			$transferUserId = intval($params['TO']);

			if (
				\Bitrix\Im\User::getInstance($transferUserId)->isBot() ||
				\Bitrix\Im\User::getInstance($transferUserId)->isExtranet() ||
				\Bitrix\Im\User::getInstance($transferUserId)->isConnector() ||
				$transferUserId <= 0
			)
			{
				return false;
			}

			$session = new Session();
			$result = $session->load(Array(
				'USER_CODE' => $this->chat['ENTITY_ID']
			));
			if (!$result)
			{
				return false;
			}
			
			if ($selfExit)
			{
				$chat->DeleteUser($this->chat['ID'], $params['FROM'], false, true);
			}
			
			if ($session->getConfig('ACTIVE') == 'Y')
			{
				$this->update(Array('AUTHOR_ID' => 0));
			}
			else
			{
				$this->update(Array('AUTHOR_ID' => $transferUserId));
			}
			$chat->AddUser($this->chat['ID'], $transferUserId, false, true);

			$userFrom = \Bitrix\Im\User::getInstance($params['FROM']);
			$userTo = \Bitrix\Im\User::getInstance($transferUserId);

			Log::write(Array($params['FROM'], $transferUserId), 'TRANSFER TO USER');

			if ($params['FROM'] > 0 && ($mode == self::TRANSFER_MODE_MANUAL || $mode == self::TRANSFER_MODE_BOT))
			{
				$message = Loc::getMessage('IMOL_CHAT_TRANSFER_'.$userFrom->getGender(), Array(
					'#USER_FROM#' => '[USER='.$userFrom->getId().']'.$userFrom->getFullName().'[/USER]',
					'#USER_TO#' => '[USER='.$userTo->getId().']'.$userTo->getFullName().'[/USER]')
				);
			}
			else
			{
				$message = Loc::getMessage('IMOL_CHAT_NEXT_IN_QUEUE', Array('#USER_TO#' => '[USER='.$userTo->getId().']'.$userTo->getFullName().'[/USER]'));
			}

			if ($mode != self::TRANSFER_MODE_AUTO)
			{
				\Bitrix\ImOpenLines\Model\OperatorTransferTable::Add(Array(
					'CONFIG_ID' => $session->getData('CONFIG_ID'),
					'SESSION_ID' => $session->getData('ID'),
					'USER_ID' => $params['FROM'],
					'TRANSFER_TYPE' => 'user',
					'TRANSFER_USER_ID' => $transferUserId
				));
			}

			\CIMChat::AddMessage(Array(
				"TO_CHAT_ID" => $this->chat['ID'],
				"MESSAGE" => $message,
				"SYSTEM" => 'Y',
			));

			if ($userFrom->isBot() && !$session->getData('DATE_OPERATOR'))
			{
				$currentDate = new \Bitrix\Main\Type\DateTime();
				$session->update(Array(
					'DATE_OPERATOR' => $currentDate,
					'TIME_BOT' => $currentDate->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp()
				));
			}

			if ($mode == self::TRANSFER_MODE_MANUAL)
			{
				$this->answer($transferUserId, false, true);
			}
			else
			{
				$session->setOperatorId($transferUserId, true);
			}
		}

		return true;
	}

	public function join($userId, $skipMessage = true)
	{
		if (!$this->isDataLoaded)
			return false;

		if (empty($userId))
			return false;

		$chat = new \CIMChat($this->joinByUserId);
		return $chat->AddUser($this->chat['ID'], $userId, false, $skipMessage);
	}

	public function leave($userId)
	{
		if (!$this->isDataLoaded)
			return false;

		$chat = new \CIMChat(0);
		return $chat->DeleteUser($this->chat['ID'], $userId, false, true);
	}

	public function close()
	{
		if (!$this->isDataLoaded)
			return false;

		$relationList = \Bitrix\Im\Model\RelationTable::getList(array(
			"select" => array("ID", "USER_ID", "EXTERNAL_AUTH_ID" => "USER.EXTERNAL_AUTH_ID"),
			"filter" => array(
				"=CHAT_ID" => $this->chat['ID']
			),
		));
		while ($relation = $relationList->fetch())
		{
			if ($relation['EXTERNAL_AUTH_ID'] == "imconnector")
				continue;

			$this->leave($relation['USER_ID']);
		}

		$this->updateFieldData(Chat::FIELD_SESSION, Array(
			'ID' => '0',
			'PAUSE' => 'N',
			'WAIT_ACTION' => 'N'
		));

		$this->update(Array(
			'AUTHOR_ID' => 0,
			self::getFieldName(self::FIELD_SILENT_MODE) => 'N'
		));

		return true;
	}

	public function setUserIdForJoin($userId)
	{
		$this->joinByUserId = intval($userId);
		return true;
	}

	public function setCrmFlag($params)
	{
		$active = $params['ACTIVE'] == 'Y'? 'Y': 'N';
		$entityType = $params['ENTITY_TYPE'];
		$entityId = intval($params['ENTITY_ID']);

		$sessionField = $this->getFieldData(self::FIELD_SESSION);
		if (
			$sessionField['CRM'] == $active &&
			$sessionField['CRM_ENTITY_TYPE'] == $entityType &&
			$sessionField['CRM_ENTITY_ID'] == $entityId
		)
		{
			return true;
		}

		$this->updateFieldData(self::FIELD_SESSION, Array(
			'CRM' => $active,
			'CRM_ENTITY_TYPE' => $entityType,
			'CRM_ENTITY_ID' => $entityId
		));

		return true;
	}

	public function finish()
	{
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$session->finish();

		return true;
	}

	public function setSilentMode($active = true)
	{
		$active = $active? 'Y': '';
		if ($this->chat[self::getFieldName(self::FIELD_SILENT_MODE)] == $active)
			return true;

		\Bitrix\Im\Model\ChatTable::update($this->chat['ID'], Array(
			self::getFieldName(self::FIELD_SILENT_MODE) => $active
		));

		\CIMChat::AddMessage(Array(
			"TO_CHAT_ID" => $this->chat['ID'],
			"MESSAGE" => Loc::getMessage($active? 'IMOL_CHAT_STEALTH_ON': 'IMOL_CHAT_STEALTH_OFF'),
			"SYSTEM" => 'Y',
		));

		return true;
	}

	public function isSilentModeEnabled()
	{
		return $this->chat[self::getFieldName(self::FIELD_SILENT_MODE)] == 'Y';
	}

	public function setPauseFlag($params)
	{
		$pause = $params['ACTIVE'] == 'Y'? 'Y': 'N';
		$sessionField = $this->getFieldData(self::FIELD_SESSION);
		if ($sessionField['PAUSE'] == $pause)
		{
			return true;
		}

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$session->pause($pause == 'Y');

		$this->updateFieldData(self::FIELD_SESSION, Array(
			'PAUSE' => $pause,
		));

		if ($pause == 'Y')
		{
			$datePause = new \Bitrix\Main\Type\DateTime();
			$datePause->add('1 WEEK');

			$formattedDate = \FormatDate('d F', $datePause->getTimestamp());
			\CIMChat::AddMessage(Array(
				"TO_CHAT_ID" => $this->chat['ID'],
				"MESSAGE" => Loc::getMessage('IMOL_CHAT_PIN_ON', array('#DATE#' => '[b]'.$formattedDate.'[/b]')),
				"SYSTEM" => 'Y',
			));
		}

		return true;
	}

	public function createLead()
	{
		if (!\Bitrix\Main\Loader::includeModule('crm'))
		{
			return false;
		}

		$sessionField = $this->getFieldData(self::FIELD_SESSION);
		if ($sessionField['CRM'] == 'Y')
		{
			return true;
		}

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => $this->chat['ENTITY_ID']
		));
		if (!$result)
		{
			return false;
		}

		$params = $session->getData();
		$crmData = $session->createLead(array(
			'CONFIG_ID' => $params['CONFIG_ID'],
			'SESSION_ID' => $params['ID'],
			'MODE' => $params['MODE'],
			'USER_CODE' => $params['USER_CODE'],
			'USER_ID' => $params['USER_ID'],
			'CRM_TITLE' => $this->getData('TITLE'),
			'OPERATOR_ID' => $params['OPERATOR_ID'],
			'CHAT_ID' => $params['CHAT_ID']
		));
		if (!$crmData)
			return false;

		$session->update(Array(
			'CRM' => 'Y',
			'CRM_CREATE' => 'Y',
			'CRM_ENTITY_TYPE' => $crmData['ENTITY_TYPE'],
			'CRM_ENTITY_ID' => $crmData['ENTITY_ID'],
			'CRM_ACTIVITY_ID' => $crmData['ACTIVITY_ID'],
		));

		$this->updateFieldData(self::FIELD_SESSION, Array(
			'CRM' => 'Y',
			'CRM_ENTITY_TYPE' => \CCrmOwnerType::LeadName,
			'CRM_ENTITY_ID' => $crmData['ENTITY_ID'],
		));

		return true;
	}

	public function getFieldData($field)
	{
		if (!in_array($field, Array(self::FIELD_CONFIG, self::FIELD_SESSION, self::FIELD_LIVECHAT)))
		{
			return false;
		}

		$data = Array();

		if ($field == self::FIELD_SESSION)
		{
			$data = Array(
				'ID' => time(),
				'CRM' => 'N',
				'CRM_ENTITY_TYPE' => 'NONE',
				'CRM_ENTITY_ID' => '0',
				'PAUSE' => 'N',
				'WAIT_ACTION' => 'N'
			);

			$fieldData = explode("|", $this->chat[self::getFieldName($field)]);
			if (isset($fieldData[0]) && $fieldData[0] == 'Y')
			{
				$data['CRM'] = $fieldData[0];
			}
			if (isset($fieldData[1]))
			{
				$data['CRM_ENTITY_TYPE'] = $fieldData[1];
			}
			if (isset($fieldData[2]))
			{
				$data['CRM_ENTITY_ID'] = $fieldData[2];
			}
			if (isset($fieldData[3]) && $fieldData[3] == 'Y')
			{
				$data['PAUSE'] = $fieldData[3];
			}
			if (isset($fieldData[4]) && $fieldData[4] == 'Y')
			{
				$data['WAIT_ACTION'] = $fieldData[4];
			}
			if (isset($fieldData[5]))
			{
				$data['ID'] = intval($fieldData[5]);
			}
		}
		else if ($field == self::FIELD_LIVECHAT)
		{
			$data = Array(
				'READED' => 'N',
				'READED_ID' => '0',
				'READED_TIME' => '0',
				'SESSION_ID' => '0',
			);
			$fieldData = explode("|", $this->chat[self::getFieldName($field)]);
			if (isset($fieldData[0]) && $fieldData[0] == 'Y')
			{
				$data['READED'] = $fieldData[0];
			}
			if (isset($fieldData[1]))
			{
				$data['READED_ID'] = intval($fieldData[1]);
			}
			if (isset($fieldData[2]))
			{
				$data['READED_TIME'] = intval($fieldData[2]);
			}
			if (isset($fieldData[3]))
			{
				$data['SESSION_ID'] = intval($fieldData[3]);
			}
		}

		return $data;
	}

	public function updateFieldData($field, $fieldData)
	{
		if (!in_array($field, Array(self::FIELD_CONFIG, self::FIELD_SESSION, self::FIELD_LIVECHAT)))
		{
			return false;
		}

		$data = Array();
		if ($field == self::FIELD_SESSION)
		{
			$data = self::getFieldData($field);
			if (isset($fieldData['CRM']))
			{
				$data['CRM'] = $fieldData['CRM'];
			}
			if (isset($fieldData['CRM_ENTITY_TYPE']))
			{
				$data['CRM_ENTITY_TYPE'] = $fieldData['CRM_ENTITY_TYPE'];
			}
			if (isset($fieldData['CRM_ENTITY_ID']))
			{
				$data['CRM_ENTITY_ID'] = $fieldData['CRM_ENTITY_ID'];
			}
			if (isset($fieldData['PAUSE']))
			{
				$data['PAUSE'] = $fieldData['PAUSE'];
			}
			if (isset($fieldData['WAIT_ACTION']))
			{
				$data['WAIT_ACTION'] = $fieldData['WAIT_ACTION'];
			}
			if (isset($fieldData['ID']))
			{
				$data['ID'] = $fieldData['ID'];
			}
			$this->chat[self::getFieldName($field)] = $data['CRM'].'|'.$data['CRM_ENTITY_TYPE'].'|'.$data['CRM_ENTITY_ID'].'|'.$data['PAUSE'].'|'.$data['WAIT_ACTION'].'|'.$data['ID'];
		}
		else if ($field == self::FIELD_LIVECHAT)
		{
			$data = self::getFieldData($field);
			if (isset($fieldData['READED']))
			{
				$data['READED'] = $fieldData['READED'];
			}
			if (isset($fieldData['READED_ID']))
			{
				$data['READED_ID'] = intval($fieldData['READED_ID']);
			}
			if (isset($fieldData['READED_TIME']))
			{
				$data['READED_TIME'] = is_object($fieldData['READED_TIME'])? $fieldData['READED_TIME']->getTimestamp(): intval($fieldData['READED_TIME']);
			}
			if (isset($fieldData['SESSION_ID']))
			{
				$data['SESSION_ID'] = intval($fieldData['SESSION_ID']);
			}
			$this->chat[self::getFieldName($field)] = $data['READED'].'|'.$data['READED_ID'].'|'.$data['READED_TIME'].'|'.$data['SESSION_ID'];
		}

		\Bitrix\Im\Model\ChatTable::update($this->chat['ID'], Array(
			self::getFieldName($field) => $this->chat[self::getFieldName($field)]
		));

		$users = Array();
		$relationList = \Bitrix\Im\Model\RelationTable::getList(array(
			"select" => array("ID", "USER_ID", "EXTERNAL_AUTH_ID" => "USER.EXTERNAL_AUTH_ID"),
			"filter" => array(
				"=CHAT_ID" => $this->chat['ID']
			),
		));
		while ($relation = $relationList->fetch())
		{
			if (
				\Bitrix\Im\User::getInstance($relation['USER_ID'])->isBot() ||
				\Bitrix\Im\User::getInstance($relation['USER_ID'])->isNetwork() ||
				$field != self::FIELD_LIVECHAT && \Bitrix\Im\User::getInstance($relation['USER_ID'])->isConnector()
			)
			{
				continue;
			}
			\CIMContactList::CleanChatCache($relation['USER_ID']);
			$users[] = $relation['USER_ID'];
		}
		if (!empty($users))
		{
			\CPullStack::AddByUsers($users, Array(
				'module_id' => 'imopenlines',
				'command' => 'updateChat',
				'params' => Array(
					'chatId' => $this->chat['ID'],
					'fieldName' => strtolower(self::getFieldName($field)),
					'fieldValue' => $this->chat[self::getFieldName($field)]
				)
			));
		}

		return $data;
	}

	public function update($fields)
	{
		\Bitrix\Im\Model\ChatTable::update($this->chat['ID'], $fields);

		$updateManager = isset($fields['AUTHOR_ID']) && IM_REVISION >= 86;

		$relations = \CIMChat::GetRelationById($this->chat['ID']);
		foreach ($relations as $rel)
		{
			\CIMContactList::CleanChatCache($rel['USER_ID']);

			if ($updateManager)
			{
				if ($rel['USER_ID'] == $this->chat['AUTHOR_ID'])
				{
					\Bitrix\Im\Model\RelationTable::update($rel['ID'], Array('MANAGER' => 'N'));
				}
				if ($rel['USER_ID'] == $fields['AUTHOR_ID'])
				{
					\Bitrix\Im\Model\RelationTable::update($rel['ID'], Array('MANAGER' => 'Y'));
				}
			}
		}

		return true;
	}

	public function getData($field = '')
	{
		if (!$this->isDataLoaded)
			return false;

		if ($field)
		{
			return isset($this->chat[$field])? $this->chat[$field]: null;
		}
		else
		{
			return $this->chat;
		}
	}

	public static function getGuestName($chatColorCode = '')
	{
		if (!\Bitrix\Main\Loader::includeModule('im'))
			return false;

		if (\Bitrix\Im\Color::isEnabled())
		{
			if (!$chatColorCode)
			{
				\CGlobalCounter::Increment('im_chat_color_id', \CGlobalCounter::ALL_SITES, false);
				$chatColorId = \CGlobalCounter::GetValue('im_chat_color_id', \CGlobalCounter::ALL_SITES);
				$chatColorCode = \Bitrix\Im\Color::getCodeByNumber($chatColorId);
			}
			\CGlobalCounter::Increment('im_chat_color_'.$chatColorCode, \CGlobalCounter::ALL_SITES, false);

			$chatColorCodeCount = \CGlobalCounter::GetValue('im_chat_color_'.$chatColorCode, \CGlobalCounter::ALL_SITES);
			if ($chatColorCodeCount == 99)
			{
				\CGlobalCounter::Set('im_chat_color_'.$chatColorCode, 1, \CGlobalCounter::ALL_SITES, '', false);
			}
			$userName = Loc::getMessage('IMOL_CHAT_CHAT_NAME_COLOR_GUEST', Array("#COLOR#" => \Bitrix\Im\Color::getName($chatColorCode), "#NUMBER#" => $chatColorCodeCount+1));
		}
		else
		{
			$guestId = \CGlobalCounter::GetValue('imol_guest_id', \CGlobalCounter::ALL_SITES);
			\CGlobalCounter::Increment('imol_guest_id', \CGlobalCounter::ALL_SITES, false);
			if ($guestId == 99)
			{
				\CGlobalCounter::Set('imol_guest_id', 1, \CGlobalCounter::ALL_SITES, '', false);
			}
			$userName = Loc::getMessage('IMOL_CHAT_CHAT_NAME_GUEST', Array("#NUMBER#" => $guestId+1));
		}

		return Array(
			'USER_NAME' => $userName,
			'USER_COLOR' => $chatColorCode,
		);
	}

	public static function getFieldName($field)
	{
		return self::$fieldAssoc[$field];
	}


	public function isNowCreated()
	{
		return $this->isCreated;
	}

	public function getError()
	{
		return $this->error;
	}
}