<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Crm\SiteButton;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class Preset
{
	protected $errors = array();
	protected static $version = 1;
	protected static $versionOptionName = 'site_button_preset_version';

	protected static function getVersion()
	{
		return self::$version;
	}

	protected static function getInstalledVersion()
	{
		return (int) Option::get('crm', self::$versionOptionName, 0);
	}

	public static function updateInstalledVersion($version = null)
	{
		if($version === null)
		{
			$version = self::getVersion();
		}

		Option::set('crm', self::$versionOptionName, $version);
	}

	public static function checkVersion()
	{
		return self::getVersion() > self::getInstalledVersion();
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function hasErrors()
	{
		return count($this->errors) > 0;
	}

	public function isInstalled($xmlId)
	{
		$dataDb = Internals\ButtonTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=IS_SYSTEM' => 'Y', '=XML_ID' => $xmlId),
		));
		if($dataDb->fetch())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function installDependencies()
	{
		if (\Bitrix\Crm\WebForm\Preset::checkVersion())
		{
			$formPreset = new \Bitrix\Crm\WebForm\Preset();
			$formPreset->install();
		}

		if (Loader::includeModule('imopenlines') && \Bitrix\ImOpenlines\Preset::checkVersion())
		{
			$formPreset = new \Bitrix\ImOpenlines\Preset();
			$formPreset->install();
		}
	}

	public function install()
	{
		self::installDependencies();

		if(!self::checkVersion())
		{
			return true;
		}

		$presets = static::getList();
		foreach($presets as $preset)
		{
			if($this->isInstalled($preset['XML_ID']))
			{
				continue;
			}

			$this->addButton($preset);
		}

		if(!$this->hasErrors())
		{
			self::updateInstalledVersion();
		}

		$callback = array(__CLASS__, 'installVersion' . self::getVersion());
		if (is_callable($callback))
		{
			call_user_func_array($callback, array());
		}

		return $this->hasErrors();
	}

	public function uninstall($xmlId = null)
	{
		$filter = array('=IS_SYSTEM' => 'Y');
		if($xmlId)
		{
			$filter['=XML_ID'] = $xmlId;
		}
		$formDb = Internals\ButtonTable::getList(array(
			'select' => array('ID'),
			'filter' => $filter,
		));
		while($form = $formDb->fetch())
		{
			$deleteDb = Internals\ButtonTable::delete($form['ID']);
			if(!$deleteDb->isSuccess())
			{
				$this->errors = array_merge($this->errors, $deleteDb->getErrorMessages());
			}
		}

		if(!$xmlId)
		{
			self::updateInstalledVersion(0);
		}
	}

	protected function addButton($formData)
	{
		$formData['IS_SYSTEM'] = 'Y';
		$formData['ACTIVE_CHANGE_BY'] = self::getCurrentUserId();
		$formData['CREATED_BY'] = self::getCurrentUserId();
		$formData['ACTIVE'] = 'Y';

		$button = new Button();
		$button->mergeData($formData);
		$button->save();
		$this->errors = array_merge($this->errors, $button->getErrors());

		return $button->hasErrors();
	}

	protected static function getCurrentUserId()
	{
		static $userId = null;
		if($userId === null)
		{
			global $USER;
			$userId = (is_object($USER) && $USER->GetID()) ? $USER->GetID() : 1;
		}

		return $userId;
	}

	public static function getById($xmlId)
	{
		$presets = static::getList();
		foreach($presets as $preset)
		{
			if($preset['ID'] == $xmlId)
			{
				return $preset;
			}
		}

		return null;
	}

	public static function getList()
	{
		$list = array();
		$items = array();
		$typeNames = array();
		$widgetList = Manager::getWidgetList();
		foreach ($widgetList as $widget)
		{
			$externals = $widget['FUNCTION_GET_PRESETS']();
			if (count($externals) == 0)
			{
				$externals = $widget['LIST'];
			}
			foreach ($externals as $external)
			{
				$typeNames[] = $widget['NAME'];
				$items[$widget['TYPE']] = array(
					'EXTERNAL_ID' => $external['ID'],
					'EXTERNAL_NAME' => $external['NAME'],
					'ACTIVE' => 'Y',
					'PAGES' => array(
						'MODE' => 'EXCLUDE',
						'LIST' => array()
					)
				);

				break;
			}
		}

		if(count($items) > 0)
		{
			$preset = array(
				'XML_ID' => 'crm_preset_all', //cd - All channels
				'NAME' => implode(', ', $typeNames),
				'BACKGROUND_COLOR' => '#00AEEF',
				'ICON_COLOR' => '#FFFFFF',
				'ITEMS' => $items
			);
			$list[] = $preset;
		}

		return $list;
	}
}
