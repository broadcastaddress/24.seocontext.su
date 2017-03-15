<?php
namespace Bitrix\ImConnector\Connectors;

use \Bitrix\Main\Loader;
use \Bitrix\ImConnector\Status,
	\Bitrix\ImConnector\Output,
	\Bitrix\ImConnector\Library,
	\Bitrix\ImConnector\Connector;

class Instagram
{
	/**
	 * Agent
	 *
	 * @return string
	 */
	public static function initializeReceiveMessages()
	{
		if(Loader::includeModule('imconnector') && defined('\Bitrix\ImConnector\Library::ID_INSTAGRAM_CONNECTOR') && Connector::isConnector(Library::ID_INSTAGRAM_CONNECTOR, true))
		{
			$statuses = Status::getInstanceAllLine(Library::ID_INSTAGRAM_CONNECTOR);

			if(!empty($statuses))
			{
				foreach ($statuses as $line=>$status)
				{
					if($status->isStatus())
					{
						$connectorOutput = new Output(Library::ID_INSTAGRAM_CONNECTOR, $line);

						$connectorOutput->initializeReceiveMessages($status->getData());
					}
				}
			}
		}

		return '\Bitrix\ImConnector\Connectors\Instagram::initializeReceiveMessages();';
	}

	public static function newMediaProcessing($message, $connector, $line)
	{
		if($connector == Library::ID_INSTAGRAM_CONNECTOR)
		{
			$status = Status::getInstance($connector, $line);

			if(!($data = $status->getData()))
				$data = array();

			$dataOld = $data;


			if(!empty($message['chat']['id']))
			{
				if(is_object($message['message']['date']))
					$datetime = $message['message']['date']->getTimestamp();
				else
					$datetime = $message['message']['date'];

				if(empty($data[$message['chat']['id']]))
					$data[$message['chat']['id']] = array(
						'datetime' => $datetime,
						'comments' => array()
					);
				else
					$data[$message['chat']['id']]['datetime'] = $datetime;

				if(count($data)>Library::INSTAGRAM_MAX_COUNT)
				{
					uasort(
						$data,
						function ($a, $b)
						{
							if ($a['datetime'] == $b['datetime'])
								return 0;
							return ($a['datetime'] > $b['datetime']) ? -1 : 1;
						}
					);

					$data = array_slice($data, 0, Library::INSTAGRAM_MAX_COUNT, true);
				}
			}


			if(!empty($data) && $dataOld!==$data)
				$status->setData($data);
		}
	}

	public static function newCommentProcessing($message, $connector, $line)
	{
		if($connector == Library::ID_INSTAGRAM_CONNECTOR)
		{
			$status = Status::getInstance($connector, $line);

			if(!($data = $status->getData()))
				$data = array();

			$dataOld = $data;


			if(!empty($message['chat']['id']))
			{
				if(empty($data[$message['chat']['id']]['comments']) || !in_array($message['message']['id'], $data[$message['chat']['id']]['comments']))
				{
					$data[$message['chat']['id']]['comments'][] = $message['message']['id'];
				}
			}


			if(!empty($data) && $dataOld!==$data)
				$status->setData($data);
		}
	}

	public static function newCommentDeliveryProcessing($message, $connector, $line)
	{
		if($connector == Library::ID_INSTAGRAM_CONNECTOR)
		{
			$status = Status::getInstance($connector, $line);

			if(!($data = $status->getData()))
				$data = array();

			$dataOld = $data;


			if(!empty($message['chat']['id']))
			{
				if(empty($data[$message['chat']['id']]['comments']) || !in_array($message['message']['id'], $data[$message['chat']['id']]['comments']))
				{
					foreach ($message['message']['id'] as $messageId)
						$data[$message['chat']['id']]['comments'][] = $messageId;
				}
			}

			if(!empty($data) && $dataOld!==$data)
				$status->setData($data);
		}
	}
}