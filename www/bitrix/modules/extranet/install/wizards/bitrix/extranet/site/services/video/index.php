<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock"))
	return;
if(!CModule::IncludeModule("video"))
	return;

$iblockCode = "video-meeting";
$iblockType = "events";
$iblockID = 0;
	
$dbIblockType = CIBlockType::GetList(Array(), Array("=ID" => $iblockType));
if(!$dbIblockType -> Fetch())
{
	$obBlocktype = new CIBlockType;
	$arFields = Array(
			"ID" => $iblockType,
			"SORT" => 500,
			"IN_RSS" => "N",
			"SECTIONS" => "Y"
		);
		
	$arFields["LANG"][LANGUAGE_ID] = Array("NAME" => GetMessage("VI_IBLOCK_NAME"));

	$res = $obBlocktype->Add($arFields);
}

$rsIBlock = CIBlock::GetList(array(), array("CODE" => $iblockCode, "TYPE" => $iblockType));
if ($arIBlock = $rsIBlock->Fetch())
	$iblockID = $arIBlock["ID"];

if($iblockID <= 0)
{
	$iblockID = WizardServices::ImportIBlockFromXML(
		WIZARD_SERVICE_RELATIVE_PATH."/xml/lang_".LANGUAGE_ID."/res_video.xml",
		$iblockCode,
		$iblockType,
		WIZARD_SITE_ID,
		$permissions = Array(
			"1" => "X",
			"2" => "R",
			WIZARD_PORTAL_ADMINISTRATION_GROUP => "X",
		)
	);
}

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/index.php", Array("CALENDAR_RES_VIDEO_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/index_b24.php", Array("CALENDAR_RES_VIDEO_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/contacts/video/detail.php", Array("CALENDAR_RES_VIDEO_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/contacts/personal.php", Array("CALENDAR_RES_VIDEO_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/workgroups/index.php", Array("CALENDAR_RES_VIDEO_IBLOCK_ID" => $iblockID));
?>