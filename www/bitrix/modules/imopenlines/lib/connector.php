<?php

namespace Bitrix\ImOpenLines;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Connector
{
	const TYPE_LIVECHAT = 'livechat';
	const TYPE_NETWORK = 'network';
	const TYPE_CONNECTOR = 'connector';

	private $error = null;
	private $moduleLoad = false;

	public function __construct()
	{
		
		$imLoad = \Bitrix\Main\Loader::includeModule('im');
		$pullLoad = \Bitrix\Main\Loader::includeModule('pull');
		$connectorLoad = \Bitrix\Main\Loader::includeModule('imconnector');
		if ($imLoad && $pullLoad && $connectorLoad)
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
			elseif (!$connectorLoad)
			{
				$this->error = new Error(__METHOD__, 'CONNECTOR_LOAD_ERROR', Loc::getMessage('IMOL_CHAT_ERROR_CONNECTOR_LOAD'));
			}
		}
	}

	private function isModuleLoad()
	{
		return $this->moduleLoad;
	}

	public function addMessage($params)
	{
		if (empty($params))
		{
			return false;
		}

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => self::getUserCode($params['connector']),
			'CONNECTOR' => $params,
			'DEFERRED_JOIN' => 'Y'
		));
		if (!$result)
		{
			return false;
		}

		if (!empty($params['message']['files']))
		{
			$params['message']['files'] = \CIMDisk::UploadFileFromMain(
				$session->getData('CHAT_ID'),
				$params['message']['files']
			);
		}
		$addMessage = Array(
			"TO_CHAT_ID" => $session->getData('CHAT_ID'),
			"FROM_USER_ID" => $params['message']['user_id'],
			//"SKIP_COMMAND" => "Y"
		);
		if (is_object($params['message']['date']))
		{
			$addMessage["MESSAGE_DATE"] = $params['message']['date']->toString();
		}
		if (!empty($params['message']['text']) || $params['message']['text'] === '0')
		{
			$addMessage["MESSAGE"] = $params['message']['text'];
		}
		if (!empty($params['message']['files']))
		{
			$addMessage["PARAMS"] = Array('FILE_ID' => $params['message']['files']);
		}
		if (!empty($params['message']['attach']))
		{
			if ($params['connector']['connector_id'] == self::TYPE_LIVECHAT)
			{
				$addMessage["ATTACH"] = $params['message']['attach'];
			}
			else
			{
				$addMessage["ATTACH"] = \CIMMessageParamAttach::GetAttachByJson($params['message']['attach']);
			}
		}
		if (!empty($params['message']['keyboard']))
		{
			if ($params['connector']['connector_id'] == self::TYPE_LIVECHAT)
			{
				$addMessage["KEYBOARD"] = $params['message']['keyboard'];
			}
			else
			{
				$keyboard = Array();
				if (!isset($params['message']['keyboard']['BUTTONS']))
				{
					$keyboard['BUTTONS'] = $params['message']['keyboard'];
				}
				else
				{
					$keyboard = $params['message']['keyboard'];
				}
				$addMessage["KEYBOARD"] = \Bitrix\Im\Bot\Keyboard::getKeyboardByJson($keyboard);
			}
		}
		if (!empty($params['message']['fileLinks']))
		{
			if (!$addMessage["ATTACH"])
			{
				$addMessage["ATTACH"] = new \CIMMessageParamAttach(null, \CIMMessageParamAttach::CHAT);
			}
			foreach ($params['message']['fileLinks'] as $key => $value)
			{
				$addMessage["ATTACH"]->AddFiles(array(
					array(
						"NAME" => $value['name'],
						"LINK" => $value['link'],
						"SIZE" => $value['size'],
					)
				));
			}
		}
		if (strlen($addMessage["MESSAGE"]) <= 0 && empty($params['message']['files']) && empty($addMessage["ATTACH"]))
		{
			$session->finish();
			return false;
		}
		if ($session->getChat()->isNowCreated() || $session->getAction() == \Bitrix\ImOpenLines\Session::ACTION_WELCOME)
		{
			if ($params['connector']['connector_id'] == self::TYPE_LIVECHAT)
			{
				$orm = \Bitrix\Im\Model\ChatTable::getById($params['chat']['id']);
				$guestChatData = $orm->fetch();
			}
			else
			{
				$guestChatData = $session->getChat()->getData();
			}
			if ($guestChatData && strlen($guestChatData['DESCRIPTION']))
			{
				\CIMChat::AddMessage(Array(
					"TO_CHAT_ID" => $session->getData('CHAT_ID'),
					'MESSAGE' => '[B]'.Loc::getMessage('IMOL_CONNECTOR_RECEIVED_DATA').'[/B][BR] '.$guestChatData['DESCRIPTION'],
					'SYSTEM' => 'Y',
					'SKIP_COMMAND' => 'Y'
				));
			}
		}
		
		$session->joinUser();

		$messageId = \CIMChat::AddMessage($addMessage);
		if (!$messageId)
		{
			return false;
		}
		
		$tracker = new \Bitrix\ImOpenLines\Tracker();
		$tracker->message(Array(
			'SESSION' => $session,
			'MESSAGE' => Array(
				'ID' => $messageId,
				'TEXT' => $addMessage["MESSAGE"]
			)
		));

		if ($params['connector']['connector_id'] == self::TYPE_LIVECHAT)
		{
			\CIMMessageParam::Set($params['im']['message_id'], Array('CONNECTOR_MID' => $messageId));
		}

		if ($params['message']['id'])
		{
			\CIMMessageParam::Set($messageId, Array('CONNECTOR_MID' => $params['message']['id']));
	
			if (!in_array($params['connector']['connector_id'], Array(self::TYPE_LIVECHAT, self::TYPE_NETWORK)))
			{
				\CIMMessageParam::SendPull($messageId);
			}
		}
		$session->execAutoAction(Array(
			'MESSAGE_ID' => $messageId
		));

		$updateSession = array(
			'MESSAGE_COUNT' => true,
			'DATE_LAST_MESSAGE' => new \Bitrix\Main\Type\DateTime()
		);
		$session->update($updateSession);

		return true;
	}

	public function updateMessage($params)
	{
		if (empty($params))
			return false;
		
		$messageIds = \CIMMessageParam::GetMessageIdByParam('CONNECTOR_MID', $params['message']['id']);
		if (empty($messageIds))
			return false;
		
		\CIMMessenger::DisableMessageCheck();
		foreach($messageIds as $messageId)
		{
			\CIMMessenger::Update($messageId, $params['message']['text'], true, true, null, true);
		}
		\CIMMessenger::EnableMessageCheck();
		
		return true;
	}
	
	public function deleteMessage($params)
	{
		if (empty($params))
			return false;
		
		$messageIds = \CIMMessageParam::GetMessageIdByParam('CONNECTOR_MID', $params['message']['id']);
		if (empty($messageIds))
			return false;
		
		\CIMMessenger::DisableMessageCheck();
		foreach($messageIds as $messageId)
		{
			\CIMMessenger::Delete($messageId, null, false, true);
		}
		\CIMMessenger::EnableMessageCheck();
		
		return true;
	}

	public function sendMessage($params)
	{
		if (!$this->isModuleLoad())
			return false;

		Log::write($params, 'SEND MESSAGE');

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => self::getUserCode($params['connector']),
			'MODE' => Session::MODE_OUTPUT,
			'OPERATOR_ID' => $params['message']['user_id']
		));
		if (!$result)
		{
			return false;
		}
		
		$session->execAutoAction(Array(
			'MESSAGE_ID' => $params['message']['id']
		));

		if ($session->getConfig('ACTIVE') == 'Y')
		{
			$fields = array(
				'im' => array(
					'chat_id' => $params['message']['chat_id'],
					'message_id' => $params['message']['id']
				),
				'message' => array(
					'user_id' => $params['message']['user_id'],
					'text' => $params['message']['text'],
					'files' => $params['message']['files'],
					'attachments' => $params['message']['attachments'],
				),
				'chat' => array(
					'id' => $params['connector']['chat_id']
				),
			);

			if ($params['message']['system'] != 'Y')
			{
				$updateSession = array(
					'MESSAGE_COUNT' => true,
					'DATE_LAST_MESSAGE' => new \Bitrix\Main\Type\DateTime()
				);

				if (!$session->getData('DATE_FIRST_ANSWER') && !\Bitrix\Im\User::getInstance($session->getData('OPERATOR_ID'))->isBot())
				{
					$currentTime = new \Bitrix\Main\Type\DateTime();
					$updateSession['DATE_FIRST_ANSWER'] = $currentTime;
					$updateSession['TIME_FIRST_ANSWER'] = $currentTime->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp();
				}

				$session->update($updateSession);
			}

			if ($params['connector']['connector_id'] == self::TYPE_NETWORK)
			{
				$network = new \Bitrix\ImOpenLines\Network();
				$network->sendMessage($params['connector']['line_id'], $fields);
			}
			else
			{
				$connector = new \Bitrix\ImConnector\Output($params['connector']['connector_id'], $params['connector']['line_id']);
				$result = $connector->sendMessage(Array($fields));
				if (!$result->isSuccess())
				{
					$this->error = new Error(__METHOD__, 'CONNECTOR_SEND_ERROR', $result->getErrorMessages());

					return false;
				}
			}
		}

		return true;
	}

	public function sendStatusWriting($fields, $user)
	{
		Log::write(array($fields, $user), 'STATUS WRITING');
		if ($fields['connector']['connector_id'] == 'network')
		{
			$network = new \Bitrix\ImOpenLines\Network();
			$network->sendStatusWriting($fields['connector']['line_id'], $fields);
		}
		else if ($fields['connector']['connector_id'] == 'livechat')
		{
			\CIMMessenger::StartWriting('chat'.$fields['connector']['chat_id'], $user['id'], "", true);
		}

		return false;
	}
	
	public function sendStatusRead($connector, $messages, $event)
	{
		if (empty($messages))
			return false;

		if ($connector['connector_id'] == 'network')
		{

		}
		else if ($connector['connector_id'] == 'lines')
		{
			Log::write(array($connector, $messages, $event), 'STATUS READ');
			
			$maxId = 0;
			foreach ($messages as $messageId)
			{
				$maxId = $maxId < $messageId? $messageId: $maxId;
			}
			
			$chat = new \CIMChat();
			$chat->SetReadMessage($connector['chat_id'], $maxId, true);
		}
		else if ($connector['connector_id'] == 'livechat')
		{
			Log::write(array($connector, $messages, $event), 'STATUS READ');
			
			$maxId = 0;
			foreach ($messages as $messageId)
			{
				$maxId = $maxId < $messageId? $messageId: $maxId;
			}
			
			$chat = new \Bitrix\ImOpenLines\Chat($connector['chat_id']);
			$chat->updateFieldData(\Bitrix\ImOpenLines\Chat::FIELD_LIVECHAT, Array(
				'READED' => 'Y',
				'READED_ID' => $maxId,
				'READED_TIME' => new \Bitrix\Main\Type\DateTime()
			));
		}
		else
		{
			$sendMessages = Array();
			foreach ($messages as $messageId)
			{
				$sendMessages[] = Array(
					'chat' => Array(
						'id' => $connector['chat_id']
					),
					'message' => Array(
						'id' => $messageId
					)
				);
			}

			$connector = new \Bitrix\ImConnector\Output($connector['connector_id'], $connector['line_id']);
			$connector->setStatusReading($sendMessages);
		}

		return false;
	}

	public static function getUserCode($params)
	{
		return $params['connector_id'].'|'.$params['line_id'].'|'.$params['chat_id'].'|'.$params['user_id'];
	}
	
	public static function onBeforeMessageSend($fields, $chat)
	{
		if ($chat['CHAT_ENTITY_TYPE'] != 'LINES')
			return true;
		
		if ($fields['FROM_USER_ID'] <= 0)
			return true;
			
		if (\Bitrix\Im\User::getInstance($fields['FROM_USER_ID'])->isConnector())
			return true;
		
		if (!\Bitrix\Main\Loader::includeModule('imconnector'))
			return false;
		
		$result = true;
		list($connectorId, $lineId) = explode('|', $chat['CHAT_ENTITY_ID']);
		
		if ($connectorId == self::TYPE_NETWORK)
		{}
		else if ($connectorId == self::TYPE_NETWORK)
		{}
		else if (\Bitrix\Main\Loader::includeModule('imconnector'))
		{
			$status = \Bitrix\ImConnector\Status::getInstance($connectorId, $lineId);
			if (!$status->isStatus())
			{
				$result = Array(
					'result' => false,
					'reason' => Loc::getMessage('IMOL_CONNECTOR_STATUS_ERROR')
				);
			}
		}
		
		return $result;
	}
	
	public static function onMessageUpdate($messageId, $messageFields, $flags)
	{
		if ($flags['BY_EVENT'] || !isset($messageFields['PARAMS']['CONNECTOR_MID']))
		{
			return false;
		}
		
		if ($messageFields['CHAT_ENTITY_TYPE'] == 'LINES')
		{
			list($connectorType, $lineId, $connectorChatId) = explode("|", $messageFields['CHAT_ENTITY_ID']);
			
		}
		else if ($messageFields['CHAT_ENTITY_TYPE'] == 'LIVECHAT')
		{
			$connectorType = self::TYPE_LIVECHAT;
		}
		else
		{
			return false;
		}
		
		if ($connectorType == self::TYPE_LIVECHAT)
		{
			\CIMMessenger::DisableMessageCheck();
			foreach($messageFields['PARAMS']['CONNECTOR_MID'] as $mid)
			{
				\CIMMessenger::Update($mid, $flags['TEXT'], $flags['URL_PREVIEW'], $flags['EDIT_FLAG'], $flags['USER_ID'], true);
			}
			\CIMMessenger::EnableMessageCheck();
		}
		else if (
			isset($lineId) && isset($connectorChatId) 
			&& !empty($messageFields['PARAMS']['CONNECTOR_MID']) && is_array($messageFields['PARAMS']['CONNECTOR_MID'])
			&& \Bitrix\Main\Loader::includeModule('imconnector')
		)
		{
			$connector = new \Bitrix\ImConnector\Output($connectorType, $lineId);

			$fields = array();

			$fields[] = array(
				'im' => array(
					'chat_id' => $messageFields['CHAT_ID'],
					'message_id' => $messageFields['ID']
				),
				'message' => array(
					'id' => $messageFields['PARAMS']['CONNECTOR_MID'],
					'text' => $flags['TEXT'],
				),
				'chat' => array(
					'id' => $connectorChatId
				),
			);
			
			$connector->updateMessage($fields);
		}
		
		return true;
	}
	
	public static function onMessageDelete($messageId, $messageFields, $flags)
	{
		if ($flags['BY_EVENT'] || !isset($messageFields['PARAMS']['CONNECTOR_MID']))
		{
			return false;
		}
		
		if ($messageFields['CHAT_ENTITY_TYPE'] == 'LINES')
		{
			list($connectorType, $lineId, $connectorChatId) = explode("|", $messageFields['CHAT_ENTITY_ID']);
		}
		else if ($messageFields['CHAT_ENTITY_TYPE'] == 'LIVECHAT')
		{
			$connectorType = self::TYPE_LIVECHAT;
		}
		else
		{
			return false;
		}

		if ($connectorType == self::TYPE_LIVECHAT)
		{
			\CIMMessenger::DisableMessageCheck();
			foreach($messageFields['PARAMS']['CONNECTOR_MID'] as $mid)
			{
				\CIMMessenger::Delete($mid, $flags['USER_ID'], $flags['COMPLETE_DELETE'], true);
			}
			\CIMMessenger::EnableMessageCheck();
		}
		else if(isset($lineId) && isset($connectorChatId))
		{
			

			$fields = array();
			foreach($messageFields['PARAMS']['CONNECTOR_MID'] as $mid)
			{
				$fields[] = array(
					'im' => array(
						'chat_id' => $messageFields['CHAT_ID'],
						'message_id' => $messageFields['ID']
					),
					'message' => array(
						'id' => $mid
					),
					'chat' => array(
						'id' => $connectorChatId
					),
				);
			}
			if(!empty($fields) && \Bitrix\Main\Loader::includeModule('imconnector'))
			{
				$connector = new \Bitrix\ImConnector\Output($connectorType, $lineId);
				$connector->deleteMessage($fields);
			}
		}

		return true;
	}
	
	public static function onMessageSend($messageId, $messageFields)
	{
		if ($messageFields['CHAT_ENTITY_TYPE'] != 'LINES')
			return false;

		if ($messageFields['AUTHOR_ID'] > 0)
		{
			$user = \Bitrix\Im\User::getInstance($messageFields['AUTHOR_ID']);
			if ($user->isConnector())
				return false;
		}

		if (
			($messageFields['SILENT_CONNECTOR'] == 'Y' || $messageFields['CHAT_'.Chat::getFieldName(Chat::FIELD_SILENT_MODE)] == 'Y') 
			&& $messageFields['IMPORTANT_CONNECTOR'] != 'Y'
		)
		{
			\CIMMessageParam::Set($messageId, Array('CLASS' => 'bx-messenger-content-item-system'));
			\CIMMessageParam::SendPull($messageId);
			return false;
		}

		if ($messageFields['SKIP_CONNECTOR'] == 'Y')
			return false;

		if ($messageFields['IMPORTANT_CONNECTOR'] != 'Y' && $messageFields['SYSTEM'] == 'Y')
			return false;

		list($connectorId, $lineId, $connectorChatId, $connectorUserId) = explode('|', $messageFields['CHAT_ENTITY_ID']);
		
		if ($connectorId == self::TYPE_LIVECHAT)
		{
			$message = Array(
				"TO_CHAT_ID" => $connectorChatId,
				"FROM_USER_ID" => $messageFields['AUTHOR_ID'],
				"SYSTEM" => $messageFields['SYSTEM'],
				"URL_PREVIEW" => $messageFields['URL_PREVIEW'],
				"ATTACH" => $messageFields['ATTACH'],
				"SKIP_USER_CHECK" => "Y",
				"SKIP_COMMAND" => "Y",
				"SKIP_CONNECTOR" => "Y",
			);
			if (array_key_exists('MESSAGE', $messageFields))
			{
				$message["MESSAGE"] = $messageFields['MESSAGE'];
			}

			$session = new Session();
			$session->load(Array(
				'MODE' => Session::MODE_OUTPUT,
				'USER_CODE' => $messageFields['CHAT_ENTITY_ID'],
				'OPERATOR_ID' => $messageFields['AUTHOR_ID']
			));
			$updateSession = array(
				'MESSAGE_COUNT' => true,
				'DATE_LAST_MESSAGE' => new \Bitrix\Main\Type\DateTime(),
				'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime(),
				'USER_ID' => $messageFields['AUTHOR_ID'],
			);
			if (!$session->getData('DATE_FIRST_ANSWER') && !\Bitrix\Im\User::getInstance($session->getData('OPERATOR_ID'))->isBot())
			{
				$currentTime = new \Bitrix\Main\Type\DateTime();
				$updateSession['DATE_FIRST_ANSWER'] = $currentTime;
				$updateSession['TIME_FIRST_ANSWER'] = $currentTime->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp();
			}
			
			$session->update($updateSession);

			$mid = \CIMChat::AddMessage($message);

			if ($messageId && $mid)
			{
				\CIMMessageParam::Set($messageId, Array('CONNECTOR_MID' => $mid));
				\CIMMessageParam::Set($mid, Array('CONNECTOR_MID' => $messageId));
			}
		}
		else
		{
			if (!self::isEnableSendSystemMessage($connectorId) && $messageFields['SYSTEM'] == 'Y')
			{
				return false;
			}
			
			$attaches = Array();
			if (isset($messageFields['PARAMS']['ATTACH']))
			{
				foreach ($messageFields['PARAMS']['ATTACH'] as $attach)
				{
					if ($attach instanceof \CIMMessageParamAttach)
					{
						$attaches[] = $attach->GetJSON();
					}
				}
			}

			$files = Array();
			if (isset($messageFields['FILES']) && \Bitrix\Main\Loader::includeModule('disk'))
			{
				foreach ($messageFields['FILES'] as $file)
				{
					$fileModel = \Bitrix\Disk\File::loadById($file['id']);
					if (!$fileModel)
						continue;

					$extModel = $fileModel->addExternalLink(array(
						'CREATED_BY' => $messageFields['FROM_USER_ID'],
						'TYPE' => \Bitrix\Disk\Internals\ExternalLinkTable::TYPE_MANUAL,
					));
					if (!$extModel)
						continue;

					$file['link'] = \Bitrix\Disk\Driver::getInstance()->getUrlManager()->getShortUrlExternalLink(array(
						'hash' => $extModel->getHash(),
						'action' => 'default',
					), true);

					if (!$file['link'])
						continue;

					$files[] = array(
						'name' => $file['name'],
						'type' => $file['type'],
						'link' => $file['link'],
						'size' => $file['size']
					);
				}
			}
			if (empty($attaches) && empty($files) && empty($messageFields['MESSAGE']) && $messageFields['MESSAGE'] !== "0")
				return false;

			if ($messageFields['SYSTEM'] != 'Y' && self::isEnableSendMessageWithSignature($connectorId) && $messageFields['AUTHOR_ID'] > 0)
			{
				$messageFields['MESSAGE'] = '[b]'.$user->getFullName().':[/b]'.(strlen($messageFields['MESSAGE'])>0? '[br] '.$messageFields['MESSAGE']: '');
			}

			$fields = array(
				'connector' => Array(
					'connector_id' => $connectorId,
					'line_id' => $lineId,
					'user_id' => $connectorUserId,
					'chat_id' => $connectorChatId,
				),
				'message' => Array(
					'id' => $messageId,
					'chat_id' => $messageFields['TO_CHAT_ID'],
					'user_id' => $messageFields['FROM_USER_ID'],
					'text' => $messageFields['MESSAGE'],
					'files' => $files,
					'attachments' => $attaches,
					'system' => $messageFields['SYSTEM']
				)
			);

			$manager = new self();
			if (!$manager->sendMessage($fields))
			{
				\CIMChat::AddMessage(Array(
					"TO_CHAT_ID" => $messageFields['TO_CHAT_ID'],
					"MESSAGE" => Loc::getMessage('IMOL_CHAT_ERROR_CONNECTOR_SEND'),
					"SYSTEM" => 'Y',
				));
				return false;
			}
		}

		return true;
	}

	public static function onStartWriting($params)
	{
		if (empty($params['CHAT']) || !in_array($params['CHAT']['ENTITY_TYPE'], Array('LINES', 'LIVECHAT')) || $params['BY_EVENT'])
			return true;
		
		if ($params['CHAT']['ENTITY_TYPE'] == 'LINES')
		{
			list($connectorId, $lineId, $connectorChatId, $connectorUserId) = explode('|', $params['CHAT']['ENTITY_ID']);
		}
		else // LIVECHAT
		{
			$connectorChatId = 0;
			$connectorId = self::TYPE_LIVECHAT;
			list($lineId, $connectorUserId) = explode('|', $params['CHAT']['ENTITY_ID']);
			
			$orm = Model\SessionTable::getList(array(
				'filter' => array(
					'=USER_CODE' => $connectorId.'|'.$lineId.'|'.$params['CHAT']['ID'].'|'.$params['USER_ID'],
					'=CLOSED' => 'N'
				)
			));
			if ($session = $orm->fetch())
			{
				$connectorChatId = $session['CHAT_ID'];
			}
		}
		
		if ($connectorChatId <= 0)
			return true;
		
		$chat = new Chat($params['CHAT']['ID']);
		if ($chat->isSilentModeEnabled() || $params['LINES_SILENT_MODE'])
			return true;
		
		$fields = array(
			'connector' => Array(
				'connector_id' => $connectorId,
				'line_id' => $lineId,
				'user_id' => $connectorUserId,
				'chat_id' => $connectorChatId,
			),
			'chat' => Array('id' => $connectorChatId),
			'user' => $params['USER_ID']
		);
		
		$userData = \Bitrix\Im\User::getInstance($params['USER_ID']);
		
		$manager = new self();
		return $manager->sendStatusWriting($fields, $userData->getFields());
	}
	
	public static function onChatRead($params)
	{
		if (!in_array($params['CHAT_ENTITY_TYPE'], Array('LINES', 'LIVECHAT')) || $params['BY_EVENT'])
			return true;
		
		if ($params['CHAT_ENTITY_TYPE'] == 'LINES')
		{
			list($connectorId, $lineId, $connectorChatId, $connectorUserId) = explode('|', $params['CHAT_ENTITY_ID']);
		}
		else // LIVECHAT
		{
			$chatId = $params['CHAT_ID'];
			$connectorChatId = 0;
			$connectorId = self::TYPE_LIVECHAT;
			list($lineId, $connectorUserId) = explode('|', $params['CHAT_ENTITY_ID']);
			
			$orm = Model\SessionTable::getList(array(
				'filter' => array(
					'=USER_CODE' => $connectorId.'|'.$lineId.'|'.$chatId.'|'.$connectorUserId,
					'=CLOSED' => 'N'
				)
			));
			if ($session = $orm->fetch())
			{
				$connectorChatId = $session['CHAT_ID'];
			}
			$connectorId = 'lines';
		}
		
		$event = $params;
		
		$connector = Array(
			'connector_id' => $connectorId,
			'line_id' => $lineId,
			'chat_id' => $connectorChatId,
		);

		$application = \Bitrix\Main\Application::getInstance();
		$connection = $application->getConnection();

		$params['END_ID'] = intval($params['END_ID']);

		$messages = Array();
		$query = $connection->query("
			SELECT M.ID, MP.PARAM_VALUE
			FROM b_im_message M
			LEFT JOIN b_im_message_param MP ON MP.MESSAGE_ID = M.ID AND MP.PARAM_NAME = 'CONNECTOR_MID'
			WHERE
			M.CHAT_ID = ".intval($params['CHAT_ID'])." AND
			M.ID > ".intval($params['START_ID']).($params['END_ID']? " AND M.ID < ".(intval($params['END_ID'])+1): "")."
		");
		while($row = $query->fetch())
		{
			$messages[] = $row['PARAM_VALUE'];
		}

		$manager = new self();
		return $manager->sendStatusRead($connector, $messages, $event);
	}
	
	public static function onReceivedEntity($params)
	{
		$userId = intval($params['user']);

		global $USER;
		if($userId > 0 && !$USER->IsAuthorized() && $USER->Authorize($userId, false, false))
		{
			setSessionExpired(true);
		}
		
		if (!isset($params['message']['user_id']))
		{
			$params['message']['user_id'] = $params['user'];
		}
		
		$fields = array(
			'connector' => Array(
				'connector_id' => $params['connector'],
				'line_id' => $params['line'],
				'chat_id' => $params['chat']['id'],
				'user_id' => $params['user'],
			),
			'chat' => $params['chat'],
			'message' => $params['message']
		);

		Log::write($fields, 'CONNECTOR - ENTITY ADD');
		$manager = new self();
		return $manager->addMessage($fields);
	}
	
	public static function onReceivedMessage(\Bitrix\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		return static::onReceivedEntity($params);
	}

	public static function onReceivedPost(\Bitrix\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		$params['message']['id'] = ''; 
		
		return static::onReceivedEntity($params);
	}
	
	public static function onReceivedMessageUpdate(\Bitrix\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		$userId = intval($params['user']);

		global $USER;
		if($userId > 0 && !$USER->IsAuthorized() && $USER->Authorize($userId, false, false))
		{
			setSessionExpired(true);
		}

		if (!isset($params['message']['user_id']))
		{
			$params['message']['user_id'] = $params['user'];
		}
		
		$fields = array(
			'connector' => Array(
				'connector_id' => $params['connector'],
				'line_id' => $params['line'],
				'chat_id' => $params['chat']['id'],
				'user_id' => $params['user'],
			),
			'chat' => $params['chat'],
			'message' => $params['message']
		);

		Log::write($fields, 'CONNECTOR - ENTITY UPDATE');
		$manager = new self();
		return $manager->updateMessage($fields);
	}

	public static function onReceivedPostUpdate(\Bitrix\Main\Event $event)
	{
		return self::onReceivedMessageUpdate($event);
	}

	public static function onReceivedMessageDelete(\Bitrix\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		$userId = intval($params['user']);

		global $USER;
		if($userId > 0 && !$USER->IsAuthorized() && $USER->Authorize($userId, false, false))
		{
			setSessionExpired(true);
		}

		if (!isset($params['message']['user_id']))
		{
			$params['message']['user_id'] = $params['user'];
		}
		
		$fields = array(
			'connector' => Array(
				'connector_id' => $params['connector'],
				'line_id' => $params['line'],
				'chat_id' => $params['chat']['id'],
				'user_id' => $params['user'],
			),
			'chat' => $params['chat'],
			'message' => $params['message']
		);

		Log::write($fields, 'CONNECTOR - ENTITY DELETE');
		$manager = new self();
		return $manager->deleteMessage($fields);
	}
	
	public static function onReceivedStatusError(\Bitrix\Main\Event $event)
	{
		return true;
	}

	public static function onReceivedStatusDelivery(\Bitrix\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;
		
		if (empty($params['im']['message_id']) || empty($params['message']['id']))
			return false;
		
		Log::write($params, 'CONNECTOR - STATUS DELIVERY');
		
		if (\Bitrix\Main\Loader::includeModule('im'))
		{
			\CIMMessageParam::Set($params['im']['message_id'], Array('CONNECTOR_MID' => $params['message']['id']));
			\CIMMessageParam::SendPull($params['im']['message_id']);
		}
		
		return true;
	}

	public static function onReceivedStatusReading(\Bitrix\Main\Event $event)
	{
		return true;
	}
	
	public static function onReceivedStatusWrites(\Bitrix\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;
		
		if (!isset($params['message']['user_id']))
		{
			$params['message']['user_id'] = $params['user'];
		}
		
		$fields = array(
			'connector' => Array(
				'connector_id' => $params['connector'],
				'line_id' => $params['line'],
				'chat_id' => $params['chat']['id'],
				'user_id' => $params['user'],
			),
			'chat' => $params['chat'],
			'message' => $params['message']
		);
		
		$skipCreate = true;
		$configManager = new Config();
		$config = $configManager->get($params['line']);
		if (
			$config && 
			$config['WELCOME_BOT_ENABLE'] == 'Y' && 
			$config['WELCOME_BOT_ID'] > 0 &&
			$config['WELCOME_BOT_JOIN'] == \Bitrix\ImOpenLines\Config::BOT_JOIN_ALWAYS
		)
		{
			$skipCreate = false;
		}
		
		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => self::getUserCode($fields['connector']),
			'CONNECTOR' => $fields,
			'SKIP_CREATE' => $skipCreate? 'Y': 'N'
		));
		if (!$result)
		{
			return false;
		}
		
		$chat = $session->getChat();
		$chatId = $chat->getData('ID');

		if (\CModule::IncludeModule('im'))
		{
			\CIMMessenger::StartWriting('chat'.$chatId, $params['user'], "", true);
		}
		
		return true;
	}
	
	public static function getListCanDeleteMessage()
	{
		$connectorList = array();
		if (\Bitrix\Main\Loader::includeModule('imconnector'))
		{
			$connectorList = \Bitrix\ImConnector\Connector::getListConnectorDelExternalMessages();
		}
		
		return $connectorList;
	}
	
	public static function getListCanUpdateOwnMessage()
	{
		$connectorList = array();
		if (\Bitrix\Main\Loader::includeModule('imconnector'))
		{
			$connectorList = \Bitrix\ImConnector\Connector::getListConnectorEditInternalMessages();
		}
		$connectorList[] = self::TYPE_LIVECHAT;
		//$connectorList[] = self::TYPE_NETWORK;
		
		return $connectorList;
	}
	
	public static function isEnableSendSystemMessage($connectorId)
	{
		if (in_array($connectorId, array(self::TYPE_LIVECHAT, self::TYPE_NETWORK)))
		{
			$result = true;
		}
		else if (\Bitrix\Main\Loader::includeModule('imconnector'))
		{
			$result = \Bitrix\ImConnector\Connector::isNeedSystemMessages($connectorId);
		}
		else
		{
			$result = true;
		}
		
		return $result;
	}
	
	public static function isEnableSendMessageWithSignature($connectorId)
	{
		if (in_array($connectorId, array(self::TYPE_LIVECHAT, self::TYPE_NETWORK)))
		{
			$result = false;
		}
		else if (\Bitrix\Main\Loader::includeModule('imconnector'))
		{
			$result = \Bitrix\ImConnector\Connector::isNeedSignature($connectorId);
		}
		else
		{
			$result = true;
		}
		
		return $result;
	}
	
	public static function isEnableGroupByChat($connectorId)
	{
		if (\Bitrix\Main\Loader::includeModule('imconnector'))
		{
			$result = \Bitrix\ImConnector\Connector::isChatGroup($connectorId);
		}
		else
		{
			$result = false;
		}
		return $result;
	}

	public function getError()
	{
		return $this->error;
	}
}