<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage intranet
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Crm\SiteButton;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Manager
 * @package Bitrix\Crm\SiteButton
 */
class Manager
{
	const ENUM_TYPE_OPEN_LINE = 'openline';
	const ENUM_TYPE_CRM_FORM = 'crmform';
	const ENUM_TYPE_CALLBACK = 'callback';

	public static function canUseOpenLine()
	{
		return Loader::includeModule('imopenlines') && Loader::includeModule('imconnector');
	}

	public static function canUseCrmForm()
	{
		return (bool) Loader::includeModule('crm');
	}

	public static function canUseCallback()
	{
		return (bool) Loader::includeModule('voximplant') && (bool) Loader::includeModule('crm');
	}

	public static function getTypeList()
	{
		$result = array();
		$widgetList = self::getWidgetList();
		foreach ($widgetList as $widget)
		{
			$result[$widget['TYPE']] = $widget['NAME'];
		}

		return $result;
	}

	public static function getWidgetList()
	{
		static $list = null;

		if ($list !== null)
		{
			return $list;
		}

		$list = array();

		if(self::canUseOpenLine())
		{
			$imConfigList = \Bitrix\Imopenlines\Model\ConfigTable::getList(array(
				'select' => array('ID', 'NAME' => 'LINE_NAME'),
				'filter' => array(
					'=ACTIVE' => 'Y'
				),
			))->fetchAll();
			$imResultConfigList = array();
			foreach ($imConfigList as $imConfig)
			{
				$connectors = \Bitrix\ImConnector\Connector::infoConnectorsLine($imConfig['ID']);
				$connectors = array_keys($connectors);
				if (count($connectors) > 0)
				{
					$imResultConfigList[] = $imConfig;
				}
			}

			$type = self::ENUM_TYPE_OPEN_LINE;
			$list[] = array(
				'TYPE' => $type,
				'NAME' => Loc::getMessage('CRM_BUTTON_MANAGER_TYPE_NAME_' . strtoupper($type)),
				'PATH_LIST' => \Bitrix\ImOpenLines\Common::getPublicFolder()."list/edit.php?ID=0",
				'PATH_ADD' => \Bitrix\ImOpenLines\Common::getPublicFolder()."list/edit.php?ID=0",
				'PATH_EDIT' => array(
					'path' => \Bitrix\ImOpenLines\Common::getPublicFolder()."list/edit.php?ID=#ID#",
					'id' => '#ID#'
				),
				'RESOURCES' => array(
					array(
						'name' => 'ol_imcon_icon_style.css',
						'type' => 'text/css',
						'path' => '/bitrix/components/bitrix/crm.button.button/templates/.default/imconnector_icon.css', //TODO: use from imconnector
						//'path' => '/bitrix/js/imconnector/icon.css',
					)
				),
				'LIST' => $imResultConfigList,
				'FUNCTION_GET_PRESETS' => function ()
				{
					return \Bitrix\Imopenlines\Model\LivechatTable::getList(array(
						'select' => array('ID' => 'CONFIG_ID', 'NAME' => 'CONFIG.LINE_NAME'),
						'filter' => array(
							'=CONFIG.ACTIVE' => 'Y'
						),
					))->fetchAll();
				},
				'FUNCTION_GET_CONNECTORS' => function ($lineId)
				{
					return \Bitrix\ImConnector\Connector::getListConnectedConnector($lineId);
				},
				'FUNCTION_GET_WIDGETS' => function ($externalId, $removeCopyright = false)
				{
					$widgets = array();

					if (!\Bitrix\Main\Loader::includeModule('imopenlines'))
					{
						return $widgets;
					}

					if (!\Bitrix\Main\Loader::includeModule('imconnector'))
					{
						return $widgets;
					}

					$sort = 400;
					$type = \Bitrix\Crm\SiteButton\Manager::ENUM_TYPE_OPEN_LINE;
					$connectorNameList = \Bitrix\ImConnector\Connector::getListConnectorReal(20);
					$connectors = \Bitrix\ImConnector\Connector::infoConnectorsLine($externalId);
					foreach ($connectors as $connectorId => $connector)
					{
						$widget = array(
							'id' => $type . '_' . $connectorId,
							'title' => $connector['name'],
							'script' => '',
							'show' => null,
							'hide' => null,
						);
						if ($connectorId == 'facebook')
						{
							$widget['title'] = 'Facebook';
						}
						else if (isset($connectorNameList[$connectorId]))
						{
							$widget['title'] = $connectorNameList[$connectorId];
						}

						if ($connectorId == 'livechat')
						{
							$liveChatManager = new \Bitrix\ImOpenLines\LiveChatManager($externalId);
							$widget['script'] = $liveChatManager->getWidget(
								\Bitrix\ImOpenLines\LiveChatManager::TYPE_BUTTON
							);

							$widget['show'] = 'window.BX.LiveChat.openLiveChat();';
							$widget['hide'] = 'window.BX.LiveChat.closeLiveChat();';
							$widget['sort'] = 100;
							$widget['useColors'] = true;
							$widget['classList'] = array('b24-widget-button-' . $widget['id']);
						}
						else
						{
							if (empty($connector['url']) && empty($connector['url_im']))
							{
								continue;
							}

							$widget['classList'] = array(
								'connector-icon',
								'connector-icon-45',
								'connector-icon-' . str_replace('.', '-', $connectorId)
							);
							$widget['sort'] = $sort;
							$sort += 100;
							$widget['show'] = array(
								'url' => $connector['url_im']
							);
						}

						$widgets[] = $widget;
					}

					return $widgets;
				}
			);
		}

		if(self::canUseCrmForm())
		{
			$type = self::ENUM_TYPE_CRM_FORM;
			$list[] = array(
				'TYPE' => $type,
				'NAME' => Loc::getMessage('CRM_BUTTON_MANAGER_TYPE_NAME_' . strtoupper($type)),
				'PATH_LIST' => Option::get('crm', 'path_to_webform_list', ''),
				'PATH_ADD' => str_replace('#form_id#', '0', Option::get('crm', 'path_to_webform_edit', '')),
				'PATH_EDIT' => array(
					'path' => Option::get('crm', 'path_to_webform_edit', ''),
					'id' => '#form_id#'
				),
				'LIST' => \Bitrix\Crm\WebForm\Internals\FormTable::getList(array(
					'select' => array('ID', 'NAME'),
					'filter' => array(
						'=ACTIVE' => 'Y',
						'=IS_CALLBACK_FORM' => 'N'
					),
				))->fetchAll(),
				'FUNCTION_GET_PRESETS' => function ()
				{
					return \Bitrix\Crm\WebForm\Internals\FormTable::getList(array(
						'select' => array('ID', 'NAME'),
						'filter' => array(
							'=ACTIVE' => 'Y',
							'=IS_CALLBACK_FORM' => 'N',
							'=IS_SYSTEM' => 'Y',
							'=XML_ID' => 'crm_preset_fb',
						),
					))->fetchAll();
				},
				'FUNCTION_GET_WIDGETS' => function ($externalId, $removeCopyright = false)
				{
					$widgets = array();

					$type = \Bitrix\Crm\SiteButton\Manager::ENUM_TYPE_CRM_FORM;
					$formData = \Bitrix\Crm\WebForm\Internals\FormTable::getRowById($externalId);
					$title = strlen($formData['CAPTION']) > 0 ? $formData['CAPTION'] : Loc::getMessage('CRM_BUTTON_MANAGER_TYPE_NAME_CRMFORM_TITLE');
					$widget = array(
						'id' => $type,
						'title' => $title,
						'script' => \Bitrix\Crm\WebForm\Script::getCrmButtonWidget(
							$externalId,
							array(
								'IS_CALLBACK' => false,
								'REMOVE_COPYRIGHT' => $removeCopyright
							)
						),
						'sort' => 300,
						'useColors' => true,
						'classList' => array('b24-widget-button-' . $type),
						'show' => \Bitrix\Crm\WebForm\Script::getCrmButtonWidgetShower($externalId),
						'hide' => 'BX.SiteButton.removeClass(document.getElementById(\'bx24_form_container_' . $externalId . '\'), \'open-sidebar\'); BX.SiteButton.onWidgetClose();',
					);
					$widgets[] = $widget;

					return $widgets;
				}
			);
		}

		if(self::canUseCallback())
		{
			$type = self::ENUM_TYPE_CALLBACK;
			$list[] = array(
				'TYPE' => $type,
				'NAME' => Loc::getMessage('CRM_BUTTON_MANAGER_TYPE_NAME_' . strtoupper($type)),
				'PATH_LIST' => Option::get('crm', 'path_to_webform_list', ''),
				'PATH_ADD' => str_replace('#form_id#', '0', Option::get('crm', 'path_to_webform_edit', '')),
				'PATH_EDIT' => array(
					'path' => Option::get('crm', 'path_to_webform_edit', ''),
					'id' => '#form_id#'
				),
				'LIST' => \Bitrix\Crm\WebForm\Internals\FormTable::getList(array(
					'select' => array('ID', 'NAME', 'CALL_FROM'),
					'filter' => array(
						'=ACTIVE' => 'Y',
						'=IS_CALLBACK_FORM' => 'Y'
					),
				))->fetchAll(),
				'FUNCTION_GET_PRESETS' => function ()
				{
					return \Bitrix\Crm\WebForm\Internals\FormTable::getList(array(
						'select' => array('ID', 'NAME'),
						'filter' => array(
							'=ACTIVE' => 'Y',
							'=IS_CALLBACK_FORM' => 'Y',
							'=IS_SYSTEM' => 'Y'
						),
					))->fetchAll();
				},
				'FUNCTION_GET_WIDGETS' => function ($externalId, $removeCopyright = false)
				{
					$widgets = array();

					$type = \Bitrix\Crm\SiteButton\Manager::ENUM_TYPE_CALLBACK;
					$widget = array(
						'id' => $type,
						'title' => Loc::getMessage('CRM_BUTTON_MANAGER_TYPE_NAME_' . strtoupper($type)),
						'script' => \Bitrix\Crm\WebForm\Script::getCrmButtonWidget(
							$externalId,
							array(
								'IS_CALLBACK' => true,
								'REMOVE_COPYRIGHT' => $removeCopyright
							)
						),
						'sort' => 200,
						'useColors' => true,
						'classList' => array('b24-widget-button-' . $type),
						'show' => \Bitrix\Crm\WebForm\Script::getCrmButtonWidgetShower($externalId),
						'hide' => 'BX.SiteButton.removeClass(document.getElementById(\'bx24_form_container_' . $externalId . '\'), \'open-sidebar\'); BX.SiteButton.onWidgetClose();',
					);
					$widgets[] = $widget;

					return $widgets;
				}
			);
		}

		return $list;
	}

	public static function updateScriptCache($fromButtonId = null)
	{
		$filter = array();
		if ($fromButtonId)
		{
			$filter['>=ID'] = $fromButtonId;
		}
		$buttonDb = Internals\ButtonTable::getList(array(
			'filter' => $filter,
			'order' => array('ID' => 'ASC')
		));
		while($buttonData = $buttonDb->fetch())
		{
			$button = new Button();
			$button->loadByData($buttonData);
			if (!Script::saveCache($button))
			{
				return $buttonData['ID'];
			}
		}

		return null;
	}

	public static function updateScriptCacheAgent($fromButtonId = null)
	{
		/*@var $USER CUser*/
		global $USER;
		if (!is_object($USER))
		{
			$USER = new \CUser();
		}

		$resultButtonId = self::updateScriptCache($fromButtonId);
		if ($resultButtonId)
		{
			return '\\Bitrix\\Crm\\SiteButton\\Manager::updateScriptCacheAgent(' . $resultButtonId . ');';
		}
		else
		{
			return '';
		}
	}

	public static function onImConnectorChange()
	{
		self::updateScriptCache();
	}

	public static function getList($params = array('order' => array('ID' => 'DESC'), 'cache' => array('ttl' => 36000)))
	{
		$result = array();
		$typeList = self::getTypeList();
		$locationList = Internals\ButtonTable::getLocationList();
		$buttonDb = Internals\ButtonTable::getList($params);
		$domain = \Bitrix\Crm\WebForm\Script::getDomain();
		while($buttonData = $buttonDb->fetch())
		{
			$button = new Button();
			$button->loadByData($buttonData);

			$buttonData['IS_PAGES_USED'] = false;
			$items = array();
			foreach ($typeList as $typeId => $typeName)
			{
				if ($button->hasActiveItem($typeId))
				{
					$item = $button->getItemByType($typeId);
					$items[$typeId] = array(
						'ID' => $item['EXTERNAL_ID'],
						'NAME' => $item['EXTERNAL_NAME'],
						'TYPE_NAME' => $typeName,
					);
				}

				$buttonData['IS_PAGES_USED'] = $buttonData['IS_PAGES_USED'] || $button->hasItemPages($typeId);
			}
			$buttonData['ITEMS'] = $items;

			if ($buttonData['IS_PAGES_USED'])
			{
				$buttonData['PAGES_USE_DISPLAY'] = Loc::getMessage('CRM_BUTTON_MANAGER_PAGES_USE_DISPLAY_USER');
			}
			else
			{
				$buttonData['PAGES_USE_DISPLAY'] = Loc::getMessage('CRM_BUTTON_MANAGER_PAGES_USE_DISPLAY_ALL');
			}


			$buttonData['LOCATION_DISPLAY'] = $locationList[$buttonData['LOCATION']];
			$buttonData['PATH_EDIT'] = $domain . str_replace(
				'#id#',
				$buttonData['ID'],
				Option::get('crm', 'path_to_button_edit', '/crm/button/edit/#id#/')
			);
			$buttonData['SCRIPT'] = Script::getScript($button);
			$result[] = $buttonData;
		}

		return $result;
	}

	/**
	 * Is button in use
	 * @return bool
	 */
	public static function isInUse()
	{
		$resultDb = Internals\ButtonTable::getList(array(
			'select' => array('ID'),
			'limit' => 1
		));
		if ($resultDb->fetch())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Is button with callback in use
	 * @return bool
	 */
	public static function isCallbackInUse()
	{
		return \Bitrix\Crm\WebForm\Manager::isInUse('Y');
	}

	/**
	 * Check read permissions
	 * @param null|\CCrmAuthorizationHelper $userPermissions User permissions.
	 * @return bool
	 */
	public static function checkReadPermission($userPermissions = null)
	{
		return \CCrmAuthorizationHelper::CheckReadPermission('BUTTON', 0, $userPermissions);
	}

	/**
	 * Get path to button list page
	 * @return string
	 */
	public static function getUrl()
	{
		return Option::get('crm', 'path_to_button_list', '/crm/button/');
	}

	/**
	 * Can remove copyright
	 * @return bool
	 */
	public static function canRemoveCopyright()
	{
		if(!Loader::includeModule('bitrix24'))
		{
			return true;
		}

		if(\CBitrix24::IsDemoLicense())
		{
			return true;
		}

		return \CBitrix24::IsLicensePaid();
	}

	/**
	 * Event handler of changing licence
	 * @return bool
	 */
	public static function onBitrix24LicenseChange(\Bitrix\Main\Event $event)
	{
		preg_match("/(project|tf|team)$/is", $event->getParameter(0), $matches);
		$licenseType = strtolower($matches[0]);
		if ($licenseType)
		{
			if(!self::canRemoveCopyright())
			{
				$buttonDb = Internals\ButtonTable::getList(array('select' => array('ID')));
				while($buttonData = $buttonDb->fetch())
				{
					$button = new Button($buttonData['ID']);
					$data = $button->getData();
					if ($data['SETTINGS']['COPYRIGHT_REMOVED'] == 'Y')
					{
						$data['SETTINGS']['COPYRIGHT_REMOVED'] = 'N';
						$updateResult = Internals\ButtonTable::update(
							$buttonData['ID'],
							array('SETTINGS' => $data['SETTINGS'])
						);
						$updateResult->isSuccess();
					}
				}
			}
		}
	}
}
