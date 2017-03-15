<?
##############################################
# Bitrix Site Manager WebDav				 #
# Copyright (c) 2002-2010 Bitrix			 #
# http://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################
IncludeModuleLangFile(__FILE__);

class CWebDavIblockStorage extends CWebDavStorage
{
	const OBJ_TYPE_IBLOCK = 'IBLOCK';
	const OBJ_TYPE_SECTION = 'SECTION';
	const OBJ_TYPE_ELEMENT = 'ELEMENT';
	
	const TYPE = 'IBLOCK';
	
	function __construct($arParams)
	{
	}
				
	/********** IBlock Rights **********/
	/* $this->workflow != "workflow" && $this->workflow != "bizproc" */
	
	static protected function GetIBlockRightsObject($type, $iBlockID, $id = null)
	{
		if ($type !== self::OBJ_TYPE_IBLOCK && $type !== self::OBJ_TYPE_SECTION && $type !== self::OBJ_TYPE_ELEMENT)
		{
			throw new Exception("_get_ib_rights_object invalid type \"".htmlspecialcharsbx($type)."\"");
		}
		
		$ibRights = null;
		if ($iBlockID === null)
		{
			throw new Exception("_get_ib_rights_object called, but no iBlockID is set");
		}
		
		if ($type !== self::OBJ_TYPE_IBLOCK && $id === null)
		{
			throw new Exception("_get_ib_rights_object called, but no ID is set");
		}

		if ($type == self::OBJ_TYPE_SECTION)
		{
			$ibRights = new CIBlockSectionRights($iBlockID, $id);
		}
		elseif ($type == self::OBJ_TYPE_ELEMENT)
		{
			$ibRights = new CIBlockElementRights($iBlockID, $id);
		}
		else
		{
			$ibRights = new CIBlockRights($iBlockID);
		}

		return $ibRights;
	}
	
	static function CheckUserIBlockPermission($permission, $type, $iBlockID, $id = null)
	{
		$obj = self::GetIBlockRightsObject($type, $iBlockID, $id);
		if(!is_object($obj))
		{
			return false;
		}
		return $obj::UserHasRightTo($iBlockID, $id, $permission);
	}
	
	/********** IBlock Rights End **********/
		
}