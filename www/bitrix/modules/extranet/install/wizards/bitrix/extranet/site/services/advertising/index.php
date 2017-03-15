<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('advertising'))
	return;

$rsAdvContract = CAdvContract::GetList($by = "s_id", $order = "asc", array(), $is_filtered, "N");	
if ($arContract = $rsAdvContract->Fetch())
	$ContractID = $arContract["ID"];
else
	return;

$arrSites = CAdvContract::GetSiteArray($ContractID);
$arrSites[] = WIZARD_SITE_ID;
$arContract = array("arrSITE" => $arrSites);

CAdvContract::Set($arContract, $ContractID, $CHECK_RIGHTS="N");



//Types
$arTypes = Array(
	Array(
		"SID"=>"INFO",
		"ACTIVE"=>"Y",
		"NAME"=>GetMessage("EXTRANET_ADV_INFO"),
		"SORT"=>"10",
		"DESCRIPTION"=>""
	)
);

foreach ($arTypes as $arFields)
{
	$dbResult = CAdvType::GetByID($arTypes["SID"], $CHECK_RIGHTS="N");
	if ($dbResult && $dbResult->Fetch())
		continue;

	CAdvType::Set($arFields, "", $CHECK_RIGHTS="N");
}
	

//Matrix
$arWeekday = Array(
	"SUNDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"MONDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"TUESDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"WEDNESDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"THURSDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"FRIDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"SATURDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23)
);

$pathToBanner = WIZARD_SERVICE_ABSOLUTE_PATH."/banners/".LANGUAGE_ID;

$arBanners = Array(
	Array(
		"CONTRACT_ID" => $ContractID,
		"TYPE_SID" => "INFO",
		"STATUS_SID"		=> "PUBLISHED",
		"NAME" => GetMessage("EXTRANET_ADV_100_100_ONE_NAME"),
		"ACTIVE" => "Y",
		"FIX_SHOW" => "Y",
		"FIX_CLICK" => "Y",
		"arrSITE" => Array(WIZARD_SITE_ID),
		"WEIGHT"=> 100,
		"AD_TYPE" => "image",
		"arrIMAGE_ID" => Array(
			"name" => "dashboard.jpg",
			"type" => "image/jpeg",
			"tmp_name" => $pathToBanner."/dashboard.jpg",
			"error" => "0",
			"size" => @filesize($pathToBanner."/dashboard.jpg"),
			"MODULE_ID" => "advertising"
		),
		"IMAGE_ALT" => GetMessage("EXTRANET_ADV_100_100_ONE_NAME"),
		"URL" => WIZARD_SITE_DIR."help/index.php",
		"URL_TARGET" => "_blank",
		"STAT_EVENT_1" => "banner",
		"STAT_EVENT_2" => "click",
		"arrWEEKDAY" => $arWeekday,
		"COMMENTS" => "dashboard_external.jpg",
		"SHOWS_FOR_VISITOR" => 10
	),

);

foreach ($arBanners as $arFields)
{
	$dbResult = CAdvBanner::GetList($by, $order, Array("COMMENTS" => $arFields["COMMENTS"], "COMMENTS_EXACT_MATCH" => "Y"), $is_filtered, "N");
	if ($dbResult && $dbResult->Fetch())
		continue;

	CAdvBanner::Set($arFields, "", $CHECK_RIGHTS="N");
}

if (WIZARD_IS_RERUN !== true)
{
	$APPLICATION->SetGroupRight("advertising", WIZARD_EXTRANET_ADMIN_GROUP, "W");
}
?>
