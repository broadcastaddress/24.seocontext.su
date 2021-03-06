<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CrmCardShowComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		global $USER;
		$this->arResult = array();
		$this->arResult['SIMPLE'] = true;
		$entity = $this->getEntityData(
			$this->arParams['ENTITY_TYPE'],
			$this->arParams['ENTITY_ID'],
			$USER->GetID()
		);

		if($entity)
		{
			$this->arResult['FOUND'] = true;
			$this->arResult['ENTITY'] = $entity;

			$photoInfo = CFile::ResizeImageGet($entity['PHOTO'], array('width' => 300, 'height' => 300), BX_RESIZE_IMAGE_EXACT);
			if(is_array($photoInfo) && isset($photoInfo['src']))
			{
				$this->arResult['ENTITY']['PHOTO_URL'] = $photoInfo['src'];
			}

			if(count($entity['ACTIVITIES']) > 0 || count($entity['DEALS']) > 0 || count($entity['INVOICES']) > 0)
			{
				$this->arResult['SIMPLE'] = false;
			}

			if($this->arResult['ENTITY']['FORMATTED_NAME'] == '' && $this->arResult['ENTITY']['TITLE'] != '')
				$this->arResult['ENTITY']['FORMATTED_NAME'] = $this->arResult['ENTITY']['TITLE'];

			$this->arResult['ENTITY']['VK_PROFILE'] = $this->getVkProfile(
				$this->arParams['ENTITY_TYPE'],
				$this->arParams['ENTITY_ID']
			);
		}
		else
		{
			$this->arResult['FOUND'] = false;
			$this->arResult['ENTITY'] = array(
				'FORMATTED_NAME' => Loc::getMessage('CRM_CARD_NAME_UNKNOWN'),
			);
		}

		$this->includeComponentTemplate();
		return $this->arResult;
	}

	protected function getEntityData($entityType, $entityId, $userId)
	{
		$findParams = array('USER_ID'=> $userId);

		$entityTypeId = CCrmOwnerType::ResolveID($entityType);
		$entityId = (int)$entityId;
		return CCrmSipHelper::getEntityFields($entityTypeId, $entityId, $findParams);
	}

	protected function getVkProfile($entityType, $entityId)
	{
		$cursor = CCrmFieldMulti::GetList(
			array('ID' => 'asc'),
			array('ENTITY_ID' => $entityType, 'ELEMENT_ID' => $entityId)
		);
		while ($row = $cursor->Fetch())
		{
			if($row['TYPE_ID'] === 'WEB' && $row['VALUE_TYPE'] === 'VK')
			{
				return $row['VALUE'];
			}
		}
		return '';
	}
}