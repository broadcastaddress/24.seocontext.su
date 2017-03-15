<?php

namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Session
{
	private $error = null;
	private $config = Array();
	private $session = Array();
	private $connectorId = '';

	/* @var \Bitrix\ImOpenLines\Chat */
	public $chat = null;

	private $action = 'none';
	public $joinUserId = 0;
	public $joinUserList = Array();
	private $isCreated = false;

	const RULE_TEXT = 'text';
	const RULE_FORM = 'form';
	const RULE_QUEUE = 'queue';
	const RULE_NONE = 'none';

	const CRM_CREATE_LEAD = 'lead';
	const CRM_CREATE_NONE = 'none';

	const ACTION_WELCOME = 'welcome';
	const ACTION_WORKTIME = 'worktime';
	const ACTION_NO_ANSWER = 'no_answer';
	const ACTION_CLOSED = 'closed';
	const ACTION_NONE = 'none';

	const MODE_INPUT = 'input';
	const MODE_OUTPUT = 'output';

	const CACHE_QUEUE = 'queue';
	const CACHE_CLOSE = 'close';
	const CACHE_INIT = 'init';

	const ORM_SAVE = 'save';
	const ORM_GET = 'get';

	public function __construct($config = array())
	{
		$this->error = new Error(null, '', '');
		$this->config = $config;

		\Bitrix\Main\Loader::includeModule('im');
	}

	public function loadByArray($session, $config, $chat)
	{
		$this->session = $session;
		$this->config = $config;
		$this->chat = $chat;
		$this->connectorId = $session['SOURCE'];
	}

	public function load($params)
	{
		list($connectorId, $lineId, $chatId, $userId) = explode('|', $params['USER_CODE']);

		$params['CONFIG_ID'] = $lineId;
		$params['USER_ID'] = $userId;
		$params['SOURCE'] = $connectorId;
		$params['CHAT_ID'] = $chatId;

		if (\Bitrix\ImOpenLines\Connector::isEnableGroupByChat($connectorId))
		{
			$params['USER_CODE'] = $connectorId.'|'.$lineId.'|'.$chatId.'|0';
		}

		return $this->start($params);
	}

	public function start($params)
	{
		$this->connectorId = $params['SOURCE'];

		$fields['USER_CODE'] = $params['USER_CODE'];
		$fields['CONFIG_ID'] = intval($params['CONFIG_ID']);
		$fields['USER_ID'] = intval($params['USER_ID']);
		$fields['OPERATOR_ID'] = intval($params['OPERATOR_ID']);
		$fields['SOURCE'] = $params['SOURCE'];
		$fields['MODE'] =  $params['MODE'] == self::MODE_OUTPUT? self::MODE_OUTPUT: self::MODE_INPUT;
		$params['DEFERRED_JOIN'] =  $params['DEFERRED_JOIN'] == 'Y'? 'Y': 'N';
		$params['SKIP_CREATE'] =  $params['SKIP_CREATE'] == 'Y'? 'Y': 'N';

		//if ($fields['SOURCE'] != 'network')
		//{
			//if (self::getQueueFlagCache(self::CACHE_INIT))
			//{
			//	return false;
			//}
			//self::setQueueFlagCache(self::CACHE_INIT);
		//}

		$orm = Model\SessionTable::getList(array(
			'select' => array('*', 'CHECK_DATE_CLOSE' => 'CHECK.DATE_CLOSE', 'CHECK_DATE_QUEUE' => 'CHECK.DATE_QUEUE'),
			'filter' => array(
				'=USER_CODE' => $fields['USER_CODE'],
				'=CLOSED' => 'N'
			)
		));
		$result = $orm->fetch();
		if ($result)
		{
			//self::deleteQueueFlagCache(self::CACHE_INIT);

			$this->session = $result;

			if (!$this->config)
			{
				$configManager = new Config();
				$this->config = $configManager->get($this->session['CONFIG_ID']);
				if (!$this->config)
				{
					return false;
				}
			}

			$fields['CONFIG_ID'] = $this->session['CONFIG_ID'];

			$this->chat = new Chat($this->session['CHAT_ID']);
			if (!$this->chat->isNowCreated())
			{
				$this->chat->join($fields['USER_ID']);
			}

			return true;
		}

		if (!$this->config)
		{
			$configManager = new Config();
			$this->config = $configManager->get($fields['CONFIG_ID']);
			if (!$this->config)
			{
				return false;
			}
		}

		if ($params['SKIP_CREATE'] == 'Y')
		{
			return false;
		}

		$this->chat = new Chat();
		$result = $this->chat->load(Array(
			'USER_CODE' => $fields['USER_CODE'],
			'USER_ID' => $fields['USER_ID'],
			'LINE_NAME' => $this->config['LINE_NAME'],
			'CONNECTOR' => $params['CONNECTOR'],
		));
		if (!$result)
		{
			return false;
		}

		$fields['CHAT_ID'] = $this->chat->getData('ID');
		if (!$this->chat->isNowCreated())
		{
			$fields['START_ID'] = $this->chat->getData('LAST_MESSAGE_ID')+1;
			$this->chat->join($fields['USER_ID']);
		}

		$orm = Model\SessionTable::add($fields);
		if (!$orm->isSuccess())
		{
			return false;
		}
		$this->isCreated = true;
		$fields['SESSION_ID'] = $orm->getId();

		$this->chat->updateFieldData(Chat::FIELD_SESSION, Array(
			'ID' => $fields['SESSION_ID'],
		));

		if ($params['SOURCE'] == 'livechat')
		{
			$liveChat = new Chat($params['CHAT_ID']);
			$liveChat->updateFieldData(Chat::FIELD_LIVECHAT, Array(
				'SESSION_ID' => $fields['SESSION_ID']
			));
		}

		if ($fields['MODE'] == self::MODE_INPUT)
		{
			$this->session['JOIN_BOT'] = false;
			if ($this->config['WELCOME_BOT_ENABLE'] == 'Y' && $this->config['WELCOME_BOT_ID'] > 0)
			{
				if ($this->config['WELCOME_BOT_JOIN'] == \Bitrix\ImOpenLines\Config::BOT_JOIN_ALWAYS)
				{
					$this->chat->setUserIdForJoin($fields['USER_ID']);
					$this->session['JOIN_BOT'] = true;
				}
				else if ($this->chat->isNowCreated())
				{
					$this->chat->setUserIdForJoin($fields['USER_ID']);
					$this->session['JOIN_BOT'] = true;
				}
			}
			else if ($this->chat->isNowCreated())
			{
				$this->action = self::ACTION_WELCOME;
			}

			/* QUEUE BLOCK */

			if ($this->config['QUEUE_TYPE'] == Config::QUEUE_TYPE_ALL)
			{
				$queue = $this->getQueue();
				$fields['OPERATOR_ID'] = 0;
				$fields['QUEUE_HISTORY'] = Array();
				$params['USER_LIST'] = $queue['USER_LIST'];
			}
			else
			{
				$queue = $this->getNextInQueue();
				$fields['OPERATOR_ID'] = $queue['RESULT'] ? $queue['USER_ID'] : $queue['FIRST_IN_QUEUE'];
				if (isset($this->session['QUEUE_HISTORY']))
				{
					$fields['QUEUE_HISTORY'] = $this->session['QUEUE_HISTORY'];
				}
			}

			/* CRM BLOCK */
			if (!Connector::isEnableGroupByChat($fields['SOURCE']) && $this->config['CRM'] == 'Y' && \IsModuleInstalled('crm'))
			{
				$crmFields = array_merge($fields, Array('CRM_TITLE' => $this->chat->getData('TITLE')));

				$result = $this->updateCrm($crmFields);
				$fields = array_merge($fields, $result);

				$this->chat->setCrmFlag(Array(
					'ACTIVE' => $fields['CRM'],
					'ENTITY_TYPE' => $fields['CRM_ENTITY_TYPE'],
					'ENTITY_ID' => $fields['CRM_ENTITY_ID'],
				));
			}
			else
			{
				$this->chat->setCrmFlag(Array(
					'ACTIVE' => 'N',
					'ENTITY_TYPE' => 'NONE',
					'ENTITY_ID' => 0,
				));
			}

			/* CLOSED LINE */
			if ($this->config['ACTIVE'] == 'N')
			{
				$this->session['JOIN_BOT'] = false;
				$this->action = self::ACTION_CLOSED;
				$fields['WORKTIME'] = 'N';
				$fields['WAIT_ACTION'] = 'Y';
				$this->chat->update(Array('AUTHOR_ID' => $fields['OPERATOR_ID']));
			}
			/* WORKTIME BLOCK */
			else if ($this->checkWorkTime())
			{
				/* NO ANSWER BLOCK */
				if (!$queue['RESULT'] && !$this->session['JOIN_BOT'])
				{
					if ($this->startNoAnswerRule())
					{
						$fields['WAIT_ACTION'] = 'Y';
					}
				}
			}
			else
			{
				$fields['WORKTIME'] = 'N';
				if ($this->session['JOIN_BOT'])
				{
					$this->action = self::ACTION_NONE;
				}
				else
				{
					$fields['WAIT_ACTION'] = 'Y';
				}
			}

			if ($this->session['JOIN_BOT'])
			{
				$queue['RESULT'] = true;
				$fields['OPERATOR_ID'] = $this->config['WELCOME_BOT_ID'];
			}
			else if ($fields['OPERATOR_ID'])
			{
				$fields['DATE_OPERATOR'] = new \Bitrix\Main\Type\DateTime();
				$fields['QUEUE_HISTORY'][$fields['OPERATOR_ID']] = true;
			}

			if ($fields['OPERATOR_ID'])
			{
				$this->joinUserList = Array($fields['OPERATOR_ID']);
			}
			else if ($params['USER_LIST'])
			{
				$this->joinUserList = $params['USER_LIST'];
			}
		}
		elseif ($fields['MODE'] == self::MODE_OUTPUT)
		{
			if ($this->config['CRM'] == 'Y' && \IsModuleInstalled('crm'))
			{
				$crmFields = array_merge($fields, Array('CRM_TITLE' => $this->chat->getData('TITLE')));
				$crmFields['ANSWERED'] = 'Y';

				$result = $this->updateCrm($crmFields);

				$fields = array_merge($fields, $result);
				$this->chat->setCrmFlag(Array(
					'ACTIVE' => 'Y',
					'ENTITY_TYPE' => $fields['CRM_ENTITY_TYPE'],
					'ENTITY_ID' => $fields['CRM_ENTITY_ID'],
				));
			}
			if ($this->config['ACTIVE'] == 'N')
			{
				$this->action = self::ACTION_CLOSED;
				$fields['WORKTIME'] = 'N';
				$fields['WAIT_ACTION'] = 'Y';
			}
			if ($fields['OPERATOR_ID'])
			{
				if ($this->chat->getData('AUTHOR_ID') == 0)
				{
					$this->chat->answer($fields['OPERATOR_ID'], true);
					$this->chat->join($fields['OPERATOR_ID']);
				}
			}
			$fields['WAIT_ANSWER'] = 'N';
		}

		$sessionId = $fields['SESSION_ID'];
		unset($fields['SESSION_ID']);
		Model\SessionTable::update($sessionId, $fields);
		$fields['SESSION_ID'] = $sessionId;

		if (
			$fields['MODE'] == self::MODE_INPUT &&
			$fields['OPERATOR_ID'] &&
			$params['DEFERRED_JOIN'] == 'N'
		)
		{
			$this->chat->join($this->joinUserList, $this->session['JOIN_BOT']);
			$this->joinUserList = Array();
		}

		$updateStats = Array(
			'IN_WORK' => new \Bitrix\Main\DB\SqlExpression("?# + 1", "IN_WORK"),
			'SESSION' => new \Bitrix\Main\DB\SqlExpression("?# + 1", "SESSION"),
		);
		if ($fields['CRM_CREATE'] == 'Y')
		{
			$updateStats['LEAD'] = new \Bitrix\Main\DB\SqlExpression("?# + 1", "LEAD");
		}
		Model\ConfigStatisticTable::update($fields['CONFIG_ID'], $updateStats);

		$dateClose = new \Bitrix\Main\Type\DateTime();
		$dateClose->add($this->config['AUTO_CLOSE_TIME'].' SECONDS');
		$dateQueue = null;

		if ($fields['MODE'] == self::MODE_INPUT)
		{
			$dateQueue = new \Bitrix\Main\Type\DateTime();
			if ($this->session['JOIN_BOT'])
			{
				if ($this->config['WELCOME_BOT_TIME'] > 0)
				{
					$dateQueue->add($this->config['WELCOME_BOT_TIME'].' SECONDS');
				}
				else
				{
					$dateQueue = null;
				}
			}
			else if ($fields['WAIT_ACTION'] != 'Y')
			{
				$dateQueue->add($this->config['QUEUE_TIME'].' SECONDS');
			}
		}

		Model\SessionCheckTable::add(Array(
			'SESSION_ID' => $fields['SESSION_ID'],
			'DATE_CLOSE' => $fields['MODE'] == self::MODE_OUTPUT? $dateClose: null,
			'DATE_QUEUE' => $dateQueue,
		));

		$orm = Model\SessionTable::getById($fields['SESSION_ID']);
		$this->session = $orm->fetch();

		$this->session['CHECK_DATE_CLOSE'] = $fields['MODE'] == self::MODE_OUTPUT? $dateClose: null;
		$this->session['CHECK_DATE_QUEUE'] = $dateQueue;

		self::deleteQueueFlagCache();

		$eventData['SESSION'] = $this->session;
		$eventData['CONFIG'] = $this->config;
		$event = new \Bitrix\Main\Event("imopenlines", "OnSessionStart", $eventData);
		$event->send();

		return true;
	}

	public function pause($active = true)
	{
		$this->update(Array(
			'PAUSE' => $active? 'Y': 'N',
			'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime()
		));
		return true;
	}

	public function finish($auto = false)
	{
		if (empty($this->session))
		{
			return false;
		}

		$update = Array();
		$currentDate = new \Bitrix\Main\Type\DateTime();

		if ($this->session['CLOSED'] == 'Y' || $this->session['WAIT_ACTION'] == 'Y')
		{
			$update['CLOSED'] = 'Y';
		}
		else
		{
			$enableSystemMessage = Connector::isEnableSendSystemMessage($this->connectorId);
			if ($this->config['ACTIVE'] == 'N')
			{
				$update['WAIT_ACTION'] = 'N';
				$update['CLOSED'] = 'Y';
			}
			else if ($auto)
			{
				if ($this->config['AUTO_CLOSE_RULE'] == self::RULE_TEXT && $enableSystemMessage)
				{
					$this->chat->update(Array(
						Chat::getFieldName(Chat::FIELD_SILENT_MODE) => 'N'
					));

					\CIMChat::AddMessage(Array(
						"TO_CHAT_ID" => $this->session['CHAT_ID'],
						"MESSAGE" => $this->config['AUTO_CLOSE_TEXT'],
						"SYSTEM" => 'Y',
						"IMPORTANT_CONNECTOR" => 'Y',
						"PARAMS" => Array(
							"CLASS" => "bx-messenger-content-item-ol-output"
						)
					));
					$update['WAIT_ACTION'] = 'Y';
				}
				else
				{
					\CIMChat::AddMessage(Array(
						"TO_CHAT_ID" => $this->session['CHAT_ID'],
						"FROM_USER_ID" => $this->session['OPERATOR_ID'],
						"MESSAGE" => Loc::getMessage('IMOL_SESSION_CLOSE_AUTO'),
						"SYSTEM" => 'Y',
					));
					$update['WAIT_ACTION'] = 'N';
					$update['CLOSED'] = 'Y';
				}
			}
			else
			{
				if ($this->config['CLOSE_RULE'] == self::RULE_TEXT && $enableSystemMessage)
				{
					\CIMChat::AddMessage(Array(
						"TO_CHAT_ID" => $this->session['CHAT_ID'],
						"MESSAGE" => $this->config['CLOSE_TEXT'],
						"SYSTEM" => 'Y',
						"IMPORTANT_CONNECTOR" => 'Y',
						"PARAMS" => Array(
							"CLASS" => "bx-messenger-content-item-ol-output"
						)
					));
					$update['WAIT_ACTION'] = 'Y';
				}
				else
				{
					$userSkip = \Bitrix\Im\User::getInstance($this->chat->getData('OPERATOR_ID'));
					\CIMChat::AddMessage(Array(
						"TO_CHAT_ID" => $this->session['CHAT_ID'],
						"FROM_USER_ID" => $this->session['OPERATOR_ID'],
						"MESSAGE" => Loc::getMessage('IMOL_SESSION_CLOSE_'.$userSkip->getGender(), Array('#USER#' => '[USER='.$userSkip->getId().']'.$userSkip->getFullName().'[/USER]')),
						"SYSTEM" => 'Y',
					));
					$update['WAIT_ACTION'] = 'N';
					$update['CLOSED'] = 'Y';
				}

				if ($this->session['CRM_ACTIVITY_ID'] > 0)
				{
					$crmManager = new Crm();
					$crmManager->updateActivity(Array(
						'ID' => $this->session['CRM_ACTIVITY_ID'],
						'UPDATE' => Array(
							'COMPLETED' => 'Y',
							'END_TIME' => new \Bitrix\Main\Type\DateTime()
						)
					));
				}

				if (!\Bitrix\Im\User::getInstance($this->session['OPERATOR_ID'])->isBot())
				{
					$update['DATE_OPERATOR_CLOSE'] = $currentDate;
				}
				if ($this->session['DATE_CREATE'])
				{
					$update['TIME_CLOSE'] = $currentDate->getTimestamp()-$this->session['DATE_CREATE']->getTimestamp();
				}
			}

			if (\CModule::IncludeModule("pull"))
			{
				$arRel = \CIMChat::GetRelationById($this->session['CHAT_ID']);
				\CPullStack::AddByUsers(array_keys($arRel), Array(
					'module_id' => 'im',
					'command' => 'chatHide',
					'expiry' => 3600,
					'params' => Array(
						'chatId' => $this->session['CHAT_ID'],
					),
				));
			}

			$update['DATE_MODIFY'] = new \Bitrix\Main\Type\DateTime();
		}

		if ($update['CLOSED'] == 'Y')
		{
			$update['DATE_CLOSE'] = new \Bitrix\Main\Type\DateTime();
			if ($this->session['TIME_CLOSE'] <= 0 && $this->session['DATE_CREATE'])
			{
				$update['TIME_CLOSE'] = $currentDate->getTimestamp()-$this->session['DATE_CREATE']->getTimestamp();
			}
			if (\Bitrix\Im\User::getInstance($this->session['OPERATOR_ID'])->isBot() && $this->session['TIME_BOT'] <= 0 && $this->session['DATE_CREATE'])
			{
				$update['TIME_BOT'] = $currentDate->getTimestamp()-$this->session['DATE_CREATE']->getTimestamp();
			}
		}

		if ($this->session['CHAT_ID'])
		{
			$orm = \Bitrix\Im\Model\ChatTable::getById($this->session['CHAT_ID']);
			$chatData = $orm->fetch();
			$update['END_ID'] = $chatData['LAST_MESSAGE_ID'];
		}

		$this->update($update);

		return true;
	}

	public function getData($field = '')
	{
		if ($field)
		{
			return isset($this->session[$field])? $this->session[$field]: null;
		}
		else
		{
			return $this->session;
		}
	}

	public function getConfig($field = '')
	{
		if ($field)
		{
			return isset($this->config[$field])? $this->config[$field]: null;
		}
		else
		{
			return $this->config;
		}
	}

	public function createLead($params)
	{
		$limitRemainder = Limit::getTrackerLimitRemainder();
		if ($limitRemainder <= 0)
		{
			$tracker = new Tracker;
			$tracker->sendLimitMessage(Array(
				'CHAT_ID' => $params['CHAT_ID'],
				'MESSAGE_TYPE' => Tracker::MESSAGE_ERROR_CREATE
			));

			return false;
		}

		$crmData = false;

		$crmManager = new Crm();
		$id = $crmManager->addLead(array(
			'CONFIG_ID' => $params['CONFIG_ID'],
			'USER_CODE' => $params['USER_CODE'],
			'USER_ID' => $params['USER_ID'],
			'TITLE' => $params['CRM_TITLE'],
			'OPERATOR_ID' => $params['OPERATOR_ID'],
		));
		if ($id)
		{
			\Bitrix\Imopenlines\Limit::increaseTracker();

			$crmData['ENTITY_TYPE'] = \CCrmOwnerType::LeadName;
			$crmData['ENTITY_ID'] = $id;
			$crmData['BINDINGS'][] = array(
				'OWNER_ID' => $id,
				'OWNER_TYPE_ID' => \CCrmOwnerType::Lead
			);

			\CIMChat::AddMessage(Array(
				"TO_CHAT_ID" => $params['CHAT_ID'],
				"MESSAGE" => Loc::getMessage('IMOL_SESSION_LEAD_ADD_NEW'),
				"SYSTEM" => 'Y',
				"ATTACH" => $crmManager->getEntityCard($crmData['ENTITY_TYPE'], $crmData['ENTITY_ID'])
			));

			if (!empty($crmData['BINDINGS']))
			{
				$crmData['ACTIVITY_ID'] = $crmManager->addActivity(Array(
					'TITLE' => $params['CRM_TITLE'],
					'MODE' => $params['MODE'],
					'USER_CODE' => $params['USER_CODE'],
					'SESSION_ID' => intval($params['SESSION_ID']),
					'COMPLETED' => 'N',
					'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
					'AUTHOR_ID' => $params['OPERATOR_ID'],
					'RESPONSIBLE_ID' => $params['OPERATOR_ID'],
					'CRM_ENTITY_TYPE' => $crmData['ENTITY_TYPE'],
					'CRM_ENTITY_ID' => $crmData['ENTITY_ID'],
					'CRM_BINDINGS' => $crmData['BINDINGS'],
					'ANSWERED' => $params['ANSWERED'] == 'Y'? 'Y': 'N',
				));
			}
		}

		return $crmData;
	}

	public function setOperatorId($id, $waitAnswer = false)
	{
		$this->update(Array(
			'WAIT_ANSWER' => $waitAnswer? 'Y': 'N',
			'OPERATOR_ID' => $id,
			'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime(),
			'SKIP_DATE_CLOSE' => 'Y'
		));
		if ($this->config['CRM'] == 'Y' && $this->config['CRM_TRANSFER_CHANGE'] == 'Y' && \IsModuleInstalled('crm'))
		{
			$crmManager = new Crm();
			if ($this->session['CRM_CREATE'] == 'Y' && $this->session['CRM_ENTITY_TYPE'] == Crm::ENTITY_LEAD)
			{
				$crmManager->update($this->session['CRM_ENTITY_TYPE'], $this->session['CRM_ENTITY_ID'], Array(
					'ASSIGNED_BY_ID' => $id
				));
			}
			$crmManager->updateActivity(Array(
				'ID' => $this->session['CRM_ACTIVITY_ID'],
				'UPDATE' => Array(
					'RESPONSIBLE_ID' => $id
				)
			));
		}

		return true;
	}

	public function update($fields)
	{
		$updateCheckTable = Array();
		if (isset($fields['CONFIG_ID']))
		{
			$configManager = new Config();
			$config = $configManager->get($fields['CONFIG_ID']);
			if ($config)
			{
				$this->config = $config;
			}
			else
			{
				unset($fields['CONFIG_ID']);
			}
		}
		if (isset($fields['DATE_MODIFY']) && ($this->session['WAIT_ANSWER'] == 'N') && $this->session['PAUSE'] == 'N')
		{
			$dateClose = clone $fields['DATE_MODIFY'];
			if (isset($fields['USER_ID']) && \Bitrix\Im\User::getInstance($fields['USER_ID'])->isConnector())
			{
				$dateClose = new \Bitrix\Main\Type\DateTime();
				$dateClose->add('1 MONTH');

				$updateCheckTable['DATE_CLOSE'] = $dateClose;
			}
			else
			{
				if ($this->session['WAIT_ACTION'] == 'Y' && $fields['WAIT_ACTION'] != 'N' || $fields['WAIT_ACTION'] == 'Y')
				{
					if (isset($fields['SKIP_DATE_CLOSE']))
					{
						$dateClose = null;
					}
					else
					{
						$dateClose->add('10 MINUTES');
					}
				}
				else
				{
					$dateClose->add($this->config['AUTO_CLOSE_TIME'].' SECONDS');
				}

				if ($dateClose)
				{
					$updateCheckTable['DATE_CLOSE'] = $dateClose;
				}
			}
		}

		if (isset($fields['DATE_LAST_MESSAGE']) && $this->session['DATE_CREATE'])
		{
			$fields['TIME_DIALOG'] = $fields['DATE_LAST_MESSAGE']->getTimestamp()-$this->session['DATE_CREATE']->getTimestamp();
		}

		if (isset($fields['CLOSED']) && $fields['CLOSED'] == 'Y')
		{
			if ($this->session['WAIT_ANSWER'] == 'N')
			{
				$fields['PAUSE'] = 'N';
				$updateCheckTable = Array();
				Model\SessionCheckTable::delete($this->session['ID']);

				$updateStatisticTable['CLOSED'] = new \Bitrix\Main\DB\SqlExpression("?# + 1", "CLOSED");
				$updateStatisticTable['IN_WORK'] = new \Bitrix\Main\DB\SqlExpression("?# - 1", "IN_WORK");

				$this->chat->close();
			}
			else
			{
				unset($fields['CLOSED']);
			}
		}
		else if (isset($fields['PAUSE']))
		{
			if ($fields['PAUSE'] == 'Y')
			{
				$datePause = new \Bitrix\Main\Type\DateTime();
				$datePause->add('1 WEEK');

				$updateCheckTable['DATE_CLOSE'] = $datePause;
			}
		}
		else if (isset($fields['WAIT_ANSWER']))
		{
			if ($fields['WAIT_ANSWER'] == 'Y')
			{
				$fields['PAUSE'] = 'N';

				$dateQueue = new \Bitrix\Main\Type\DateTime();
				$dateQueue->add($this->config['QUEUE_TIME'].' SECONDS');
				$updateCheckTable['DATE_QUEUE'] = $dateQueue;
			}
			else
			{
				$fields['WAIT_ACTION'] = 'N';
				$fields['PAUSE'] = 'N';
				$updateCheckTable['DATE_QUEUE'] = null;
			}
		}

		if (isset($fields['WAIT_ACTION']))
		{
			$this->chat->updateFieldData(Chat::FIELD_SESSION, Array(
				'WAIT_ACTION' => $fields['WAIT_ACTION'],
			));
		}

		if (isset($fields['MESSAGE_COUNT']))
		{
			$fields["MESSAGE_COUNT"] = new \Bitrix\Main\DB\SqlExpression("?# + 1", "MESSAGE_COUNT");
			$updateStatisticTable['MESSAGE'] = new \Bitrix\Main\DB\SqlExpression("?# + 1", "MESSAGE");
		}

		if (isset($fields['CRM_CREATE']) && $fields['CRM_CREATE'] == 'Y')
		{
			$updateStatisticTable['LEAD'] = new \Bitrix\Main\DB\SqlExpression("?# + 1", "LEAD");
		}

		if (!empty($updateCheckTable))
		{
			Model\SessionCheckTable::update($this->session['ID'], $updateCheckTable);
		}

		if (!empty($updateStatisticTable))
		{
			Model\ConfigStatisticTable::update($this->session['CONFIG_ID'], $updateStatisticTable);
		}

		unset($fields['USER_ID']);
		unset($fields['SKIP_DATE_CLOSE']);

		foreach ($fields as $key => $value)
		{
			$this->session[$key] = $value;
		}
		foreach ($updateCheckTable as $key => $value)
		{
			$this->session['CHECK_'.$key] = $value;
		}
		if ($this->session['ID'] && !empty($fields))
		{
			Model\SessionTable::update($this->session['ID'], $fields);
		}

		return true;
	}

	private function updateCrm($params)
	{
		$result = array(
			'CRM' => 'N',
			'CRM_CREATE' => 'N',
			'CRM_ENTITY_TYPE' => 'NONE',
			'CRM_ENTITY_ID' => 0,
			'CRM_ACTIVITY_ID' => 0
		);
		if (!\IsModuleInstalled('crm'))
		{
			return $result;
		}

		$crmManager = new Crm();
		$crmData = $crmManager->find(Crm::FIND_BY_CODE, Array('CODE' => $params['USER_CODE']));
		if (!$crmData && $params['USER_ID'] > 0)
		{
			$limitRemainder = Limit::getTrackerLimitRemainder();
			if ($limitRemainder > 0)
			{
				$tracker = new \Bitrix\ImOpenLines\Tracker();
				$crmData = $tracker->user(Array(
					'CHAT_ID' => $params['CHAT_ID'],
					'USER_ID' => $params['USER_ID'],
					'USER_CODE' => $params['USER_CODE'],
					'SESSION_ID' => $params['SESSION_ID'],
				));
			}
		}

		if ($crmData)
		{
			$result['CRM'] = 'Y';
			$result['CRM_ENTITY_TYPE'] = $crmData['ENTITY_TYPE'];
			$result['CRM_ENTITY_ID'] = $crmData['ENTITY_ID'];

			if ($this->config['CRM_FORWARD'] == 'Y' && $result['CRM'] == 'Y' && $params['MODE'] == self::MODE_INPUT)
			{
				$entityData = $crmManager->getEntityData($result['CRM_ENTITY_TYPE'], $result['CRM_ENTITY_ID']);
				if ($entityData)
				{
					$params['OPERATOR_ID'] = $entityData['ASSIGNED_BY_ID'];
					$result['OPERATOR_ID'] = $params['OPERATOR_ID'];
				}
			}

			if (!empty($crmData['BINDINGS']))
			{
				$result['CRM_ACTIVITY_ID'] = $crmManager->addActivity(Array(
					'TITLE' => $params['CRM_TITLE'],
					'MODE' => $params['MODE'],
					'USER_CODE' => $params['USER_CODE'],
					'SESSION_ID' => intval($params['SESSION_ID']),
					'COMPLETED' => 'N',
					'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
					'AUTHOR_ID' => $params['OPERATOR_ID'],
					'RESPONSIBLE_ID' => $params['OPERATOR_ID'],
					'CRM_ENTITY_TYPE' => $crmData['ENTITY_TYPE'],
					'CRM_ENTITY_ID' => $crmData['ENTITY_ID'],
					'CRM_BINDINGS' => $crmData['BINDINGS'],
					'ANSWERED' => $params['ANSWERED'] == 'Y'? 'Y': 'N',
				));
			}
		}
		else if ($this->config['CRM_CREATE'] == self::CRM_CREATE_LEAD && $params['OPERATOR_ID'] && $params['SOURCE'] != 'livechat' && $params['USER_ID'] > 0)
		{
			$crmData = $this->createLead(array(
				'CONFIG_ID' => $params['CONFIG_ID'],
				'MODE' => $params['MODE'],
				'USER_CODE' => $params['USER_CODE'],
				'SESSION_ID' => intval($params['SESSION_ID']),
				'USER_ID' => $params['USER_ID'],
				'CRM_TITLE' => $params['CRM_TITLE'],
				'OPERATOR_ID' => $params['OPERATOR_ID'],
				'CHAT_ID' => $params['CHAT_ID'],
				'ANSWERED' => $params['ANSWERED'] == 'Y'? 'Y': 'N',
			));
			if ($crmData)
			{
				$result['CRM'] = 'Y';
				$result['CRM_CREATE'] = 'Y';
				$result['CRM_ENTITY_TYPE'] = \CCrmOwnerType::LeadName;
				$result['CRM_ENTITY_ID'] = $crmData['ENTITY_ID'];
				$result['CRM_ACTIVITY_ID'] = $crmData['ACTIVITY_ID'];
			}
		}

		return $result;
	}

	public function checkWorkTime()
	{
		$skipSession = false;
		if ($this->config['WORKTIME_ENABLE'] == 'N')
		{
			return true;
		}

		$timezone = !empty($this->config["WORKTIME_TIMEZONE"])? new \DateTimeZone($this->config["WORKTIME_TIMEZONE"]) : null;
		$numberDate = new \Bitrix\Main\Type\DateTime(null, null, $timezone);

		if (!empty($this->config['WORKTIME_DAYOFF']))
		{
			$allWeekDays = array('MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6, 'SU' => 7);
			$currentWeekDay = $numberDate->format('N');
			foreach($this->config['WORKTIME_DAYOFF'] as $day)
			{
				if ($currentWeekDay == $allWeekDays[$day])
				{
					$skipSession = true;
					break;
				}
			}
		}

		if (!$skipSession && !empty($this->config['WORKTIME_HOLIDAYS']))
		{
			$currentDay = $numberDate->format('d.m');
			foreach($this->config['WORKTIME_HOLIDAYS'] as $holiday)
			{
				if ($currentDay == $holiday)
				{
					$skipSession = true;
					break;
				}
			}
		}

		if (!$skipSession && !empty($this->config['WORKTIME_FROM']) && !empty($this->config['WORKTIME_TO']))
		{
			$currentTime = $numberDate->format('G.i');

			if (!($currentTime >= $this->config['WORKTIME_FROM'] && $currentTime <= $this->config['WORKTIME_TO']))
			{
				$skipSession = true;
			}
		}

		if ($skipSession)
		{
			$this->action = self::ACTION_WORKTIME;
		}

		return $skipSession? false: true;
	}

	public function execAutoAction($params)
	{
		$update = Array();

		$enableSystemMessage = Connector::isEnableSendSystemMessage($this->connectorId);

		if ($this->chat->isNowCreated() || $this->action == \Bitrix\ImOpenLines\Session::ACTION_WELCOME)
		{
			if ($this->config['WELCOME_MESSAGE'] == 'Y' && $this->session['SOURCE'] != 'network' && $enableSystemMessage)
			{
				\CIMChat::AddMessage(Array(
					"TO_CHAT_ID" => $this->session['CHAT_ID'],
					"MESSAGE" => $this->config['WELCOME_MESSAGE_TEXT'],
					"SYSTEM" => 'Y',
					"IMPORTANT_CONNECTOR" => 'Y',
					"PARAMS" => Array(
						"CLASS" => "bx-messenger-content-item-ol-output"
					)
				));
			}
		}

		if ($this->action == self::ACTION_CLOSED && $this->config['ACTIVE'] == 'N')
		{
			\CIMChat::AddMessage(Array(
				"TO_CHAT_ID" => $this->session['CHAT_ID'],
				"MESSAGE" => Loc::getMessage('IMOL_SESSION_LINE_IS_CLOSED'),
				"SYSTEM" => 'Y',
			));
		}
		else if ($enableSystemMessage)
		{
			if ($this->action == self::ACTION_WORKTIME)
			{
				if ($this->config['WORKTIME_DAYOFF_RULE'] == self::RULE_TEXT)
				{
					\CIMChat::AddMessage(Array(
						"TO_CHAT_ID" => $this->session['CHAT_ID'],
						"MESSAGE" => $this->config['WORKTIME_DAYOFF_TEXT'],
						"SYSTEM" => 'Y',
						"IMPORTANT_CONNECTOR" => 'Y',
						"PARAMS" => Array(
							"CLASS" => "bx-messenger-content-item-ol-output"
						)
					));
				}
			}
			else if ($this->action == self::ACTION_NO_ANSWER)
			{
				if ($this->config['NO_ANSWER_RULE'] == self::RULE_TEXT)
				{
					\CIMChat::AddMessage(Array(
						"TO_CHAT_ID" => $this->session['CHAT_ID'],
						"MESSAGE" => $this->config['NO_ANSWER_TEXT'],
						"SYSTEM" => 'Y',
						"IMPORTANT_CONNECTOR" => 'Y',
						"PARAMS" => Array(
							"CLASS" => "bx-messenger-content-item-ol-output"
						)
					));
				}
			}
		}

		$update['DATE_MODIFY'] = new \Bitrix\Main\Type\DateTime();
		$update['USER_ID'] = $GLOBALS['USER']? $GLOBALS['USER']->GetId(): 0;

		$this->update($update);
	}

	public function getQueue()
	{
		$queue = new Queue($this->session, $this->config, $this->chat);
		$result = $queue->getQueue();

		return $result;
	}

	public function getNextInQueue($manual = false)
	{
		$queue = new Queue($this->session, $this->config, $this->chat);
		$result = $queue->getNextUser($manual);

		return $result;
	}

	public function transferToNextInQueue($manual = true)
	{
		$queue = $this->getNextInQueue($manual);
		if (!$manual && $this->config['QUEUE_TYPE'] == Config::QUEUE_TYPE_ALL)
		{
			$queue['RESULT'] = false;
		}
		else
		{
			$transferToQueue = false;
			$dateQueue = new \Bitrix\Main\Type\DateTime();
			$dateQueue->add($this->config['QUEUE_TIME'].' SECONDS');
		}

		if ($queue['RESULT'])
		{
			if ($queue['USER_ID'] && $this->session['OPERATOR_ID'] != $queue['USER_ID'])
			{
				$this->chat->transfer(Array(
					'FROM' => $this->session['OPERATOR_ID'],
					'TO' => $queue['USER_ID'],
					'MODE' => Chat::TRANSFER_MODE_AUTO,
					'LEAVE' => $this->config['WELCOME_BOT_LEFT'] == Config::BOT_LEFT_CLOSE && \Bitrix\Im\User::getInstance($this->session['OPERATOR_ID'])->isBot()? 'N':'Y'
				));
			}
			$this->session['QUEUE_HISTORY'][$queue['USER_ID']] = true;
			$update['QUEUE_HISTORY'] = $this->session['QUEUE_HISTORY'];
		}
		else if ($this->session['WAIT_ACTION'] != 'Y' && $this->config['ACTIVE'] == 'Y')
		{
			if ($this->session['OPERATOR_ID'] <= 0 || \Bitrix\Im\User::getInstance($this->session['OPERATOR_ID'])->isBot())
			{
				$this->chat->transfer(Array(
					'FROM' => $this->session['OPERATOR_ID'],
					'TO' => $queue['FIRST_IN_QUEUE'],
					'MODE' => Chat::TRANSFER_MODE_AUTO,
					'LEAVE' => $this->config['WELCOME_BOT_LEFT'] == Config::BOT_LEFT_CLOSE && \Bitrix\Im\User::getInstance($this->session['OPERATOR_ID'])->isBot()? 'N':'Y'
				));
			}
			if ($this->startNoAnswerRule())
			{
				if ($this->config['NO_ANSWER_RULE'] != self::RULE_QUEUE)
				{
					$update['WAIT_ACTION'] = 'Y';
					$dateQueue = null;
				}
				if ($this->config['NO_ANSWER_RULE'] == self::RULE_TEXT && Connector::isEnableSendSystemMessage($this->connectorId))
				{
					\CIMChat::AddMessage(Array(
						"TO_CHAT_ID" => $this->session['CHAT_ID'],
						"MESSAGE" => $this->config['NO_ANSWER_TEXT'],
						"SYSTEM" => 'Y',
						"IMPORTANT_CONNECTOR" => 'Y',
						"PARAMS" => Array(
							"CLASS" => "bx-messenger-content-item-ol-output"
						)
					));
				}
			}
			else if ($this->config['NO_ANSWER_RULE'] == self::RULE_QUEUE && $manual)
			{
				\CIMChat::AddMessage(Array(
					"TO_CHAT_ID" => $this->session['CHAT_ID'],
					'MESSAGE' => Loc::getMessage('IMOL_SESSION_SKIP_ALONE'),
					'SYSTEM' => 'Y',
					'SKIP_COMMAND' => 'Y'
				));
			}
		}
		else
		{
			if ($this->session['OPERATOR_ID'] <= 0 && $queue['FIRST_IN_QUEUE'] > 0)
			{
				$this->chat->transfer(Array(
					'FROM' => $this->session['OPERATOR_ID'],
					'TO' => $queue['FIRST_IN_QUEUE'],
					'MODE' => Chat::TRANSFER_MODE_AUTO,
					'LEAVE' => $this->config['WELCOME_BOT_LEFT'] == Config::BOT_LEFT_CLOSE && \Bitrix\Im\User::getInstance($this->session['OPERATOR_ID'])->isBot()? 'N':'Y'
				));
			}
			else if ($manual)
			{
				if ($queue['FIRST_IN_QUEUE'] > 0 && $queue['FIRST_IN_QUEUE'] != $this->session['OPERATOR_ID'])
				{
					$transferToQueue = true;
				}
				else
				{
					\CIMChat::AddMessage(Array(
						"TO_CHAT_ID" => $this->session['CHAT_ID'],
						'MESSAGE' => Loc::getMessage('IMOL_SESSION_SKIP_ALONE'),
						'SYSTEM' => 'Y',
						'SKIP_COMMAND' => 'Y'
					));
				}
			}
			$dateQueue = null;
			$update['QUEUE_HISTORY'] = Array();
		}

		Model\SessionCheckTable::update($this->session['ID'], Array(
			'DATE_QUEUE' => $dateQueue
		));

		$update['DATE_MODIFY'] = new \Bitrix\Main\Type\DateTime();
		$update['SKIP_DATE_CLOSE'] = 'Y';

		$this->update($update);

		if ($transferToQueue)
		{
			self::transferToNextInQueue(true);
		}
	}

	public function startNoAnswerRule()
	{
		$finalize = false;
		if ($this->config['NO_ANSWER_RULE'] != self::RULE_QUEUE)
		{
			$this->action = self::ACTION_NO_ANSWER;
			$finalize = true;
		}
		return $finalize;
	}

	public static function transferToNextInQueueAgent($nextExec)
	{
		if (self::getQueueFlagCache(self::CACHE_QUEUE) || self::getQueueFlagCache(self::CACHE_CLOSE))
			return '\Bitrix\ImOpenLines\Session::transferToNextInQueueAgent(0);';

		$configCount = Model\SessionCheckTable::getList(array(
			'select' => array('CNT'),
			'filter' => Array(
				'!=DATE_QUEUE' => null
			),
			'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
		))->fetch();
		if ($configCount['CNT'] <= 0)
		{
			self::setQueueFlagCache(self::CACHE_QUEUE);
			return '\Bitrix\ImOpenLines\Session::transferToNextInQueueAgent(0);';
		}

		$configs = Array();
		$chats = Array();
		$configManager = new Config();

		$res = Model\SessionCheckTable::getList(Array(
			'select' => Array('SESSION.*'),
			'filter' => Array(
				'<=DATE_QUEUE' => new \Bitrix\Main\Type\DateTime()
			),
			'limit' => 100
		));
		while ($row = $res->fetch())
		{
			$fields = Array();
			foreach($row as $key=>$value)
			{
				$key = str_replace('IMOPENLINES_MODEL_SESSION_CHECK_SESSION_', '', $key);
				$fields[$key] = $value;
			}

			if (!isset($configs[$fields['CONFIG_ID']]))
			{
				$configs[$fields['CONFIG_ID']] = $configManager->get($fields['CONFIG_ID']);
			}
			if (!isset($chats[$fields['CHAT_ID']]))
			{
				$chats[$fields['CHAT_ID']] = new Chat($fields['CHAT_ID']);
			}

			$session = new Session();
			$session->loadByArray($fields, $configs[$fields['CONFIG_ID']], $chats[$fields['CHAT_ID']]);
			$session->transferToNextInQueue(false);
		}

		return '\Bitrix\ImOpenLines\Session::transferToNextInQueueAgent(1);';
	}

	public static function closeByTimeAgent($nextExec)
	{
		if (self::getQueueFlagCache(self::CACHE_CLOSE))
			return '\Bitrix\ImOpenLines\Session::closeByTimeAgent(0);';

		$configCount = Model\SessionCheckTable::getList(array(
			'select' => array('CNT'),
			'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
		))->fetch();
		if ($configCount['CNT'] <= 0)
		{
			self::setQueueFlagCache(self::CACHE_CLOSE);
			return '\Bitrix\ImOpenLines\Session::closeByTimeAgent(0);';
		}

		$configs = Array();
		$chats = Array();
		$configManager = new Config();

		$res = Model\SessionCheckTable::getList(Array(
			'select' => Array('SESSION.*'),
			'filter' => Array(
				'<=DATE_CLOSE' => new \Bitrix\Main\Type\DateTime()
			),
			'limit' => 100
		));
		while ($row = $res->fetch())
		{
			$fields = Array();
			foreach($row as $key=>$value)
			{
				$key = str_replace('IMOPENLINES_MODEL_SESSION_CHECK_SESSION_', '', $key);
				$fields[$key] = $value;
			}

			if (!isset($configs[$fields['CONFIG_ID']]))
			{
				$configs[$fields['CONFIG_ID']] = $configManager->get($fields['CONFIG_ID']);
			}

			if (!isset($chats[$fields['CHAT_ID']]))
			{
				$chats[$fields['CHAT_ID']] = new Chat($fields['CHAT_ID']);
			}

			$session = new Session();
			$session->loadByArray($fields, $configs[$fields['CONFIG_ID']], $chats[$fields['CHAT_ID']]);
			$session->finish(true);
		}

		return '\Bitrix\ImOpenLines\Session::closeByTimeAgent(1);';
	}

	private static function prolongDueChatActivity($chatId)
	{
		$orm = Model\SessionTable::getList(array(
			'select' => Array(
				'*',
				'CHECK_DATE_CLOSE' => 'CHECK.DATE_CLOSE'
			),
			'filter' => array(
				'=CHAT_ID' => $chatId,
				'=CLOSED' => 'N'
			)
		));

		if ($result = $orm->fetch())
		{
			$currentDate = new \Bitrix\Main\Type\DateTime();
			if ($result['CHECK_DATE_CLOSE'] && $currentDate->getTimestamp()+600 > $result['CHECK_DATE_CLOSE']->getTimestamp())
			{
				$dateClose = $result['CHECK_DATE_CLOSE']->add('10 MINUTES');
				Model\SessionCheckTable::update($result['ID'], Array(
					'DATE_CLOSE' => $dateClose
				));
			}
		}
	}

	public static function onSessionProlongLastMessage($chatId, $dialogId, $entityType = '', $entityId = '', $userId = '')
	{
		if ($entityType != 'LINES')
			return true;

		self::prolongDueChatActivity($chatId);

		return true;
	}

	public static function onSessionProlongWriting($params)
	{
		if ($params['CHAT']['ENTITY_TYPE'] != 'LINES')
			return true;

		self::prolongDueChatActivity($params['CHAT']['ID']);

		return true;
	}

	public static function onSessionProlongChatRename($chatId, $title, $entityType = '', $entityId = '', $userId = '')
	{
		if ($entityType != 'LINES')
			return true;

		self::prolongDueChatActivity($chatId);

		return true;
	}

	public static function setQueueFlagCache($type = "")
	{
		if (!$type)
			return false;

		$app = \Bitrix\Main\Application::getInstance();
		$managedCache = $app->getManagedCache();
		$managedCache->clean("imol_queue_flag_".$type);
		$managedCache->read(86400*30, "imol_queue_flag_".$type);
		$managedCache->set("imol_queue_flag_".$type, true);

		return true;
	}

	public static function deleteQueueFlagCache($type = "")
	{
		$app = \Bitrix\Main\Application::getInstance();
		$managedCache = $app->getManagedCache();
		if ($type)
		{
			$managedCache->clean("imol_queue_flag_".$type);
		}
		else
		{
			$managedCache->clean("imol_queue_flag_".self::CACHE_CLOSE);
			$managedCache->clean("imol_queue_flag_".self::CACHE_QUEUE);
			$managedCache->clean("imol_queue_flag_".self::CACHE_INIT);
		}

		return true;
	}

	public static function getQueueFlagCache($type = "")
	{
		if (!$type)
			return false;

		$app = \Bitrix\Main\Application::getInstance();
		$managedCache = $app->getManagedCache();
		if ($result = $managedCache->read(86400*30, "imol_queue_flag_".$type))
		{
			$result = $managedCache->get("imol_queue_flag_".$type) === false? false: true;
		}
		return $result;
	}

	public function getChat()
	{
		return $this->chat;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function joinUser()
	{
		if (!empty($this->joinUserList))
		{
			Log::write($this->joinUserList, 'DEFFERED JOIN');
			$this->chat->join($this->joinUserList);
		}

		return true;
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
