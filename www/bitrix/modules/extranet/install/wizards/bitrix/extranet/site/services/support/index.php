<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (WIZARD_IS_RERUN === true)
	return;

if(!CModule::IncludeModule("support"))
	return;


// categories
$arCategories = Array(

	Array(
		'NAME'		=> GetMessage('CATEGORY_EXTRANET'),
		'arrSITE'	=> Array(WIZARD_SITE_ID),
		'C_TYPE'  	=> 'C',
		'C_SORT'	=> 100,
		'EVENT1'	=> 'ticket',
	),

);

$newCategoryID = Array();
foreach ($arCategories as $arCategory)
{
	$categoryID = (int)CTicketDictionary::Add($arCategory);
	$newCategoryID[] = $categoryID;
}

// criticallies

$arFilter = Array("TYPE" => "K");

$rsTD = CTicketDictionary::GetList($by, $order, $arFilter, $is_filtered);
while ($arTD = $rsTD->Fetch())
{
	$arSites = CTicketDictionary::GetSiteArray($arTD["ID"]);
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID;
		CTicketDictionary::Update($arTD["ID"], array("SID"=>$arTD["SID"], "arrSITE"=>$arSites));
	}
}

// statuses

$arFilter = Array("TYPE" => "S");

$rsTD = CTicketDictionary::GetList($by, $order, $arFilter, $is_filtered);
while ($arTD = $rsTD->Fetch())
{
	$arSites = CTicketDictionary::GetSiteArray($arTD["ID"]);
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID;
		CTicketDictionary::Update($arTD["ID"], array("SID"=>$arTD["SID"], "arrSITE"=>$arSites));
	}
}

// marks

$arFilter = Array("TYPE" => "M");

$rsTD = CTicketDictionary::GetList($by, $order, $arFilter, $is_filtered);
while ($arTD = $rsTD->Fetch())
{
	$arSites = CTicketDictionary::GetSiteArray($arTD["ID"]);
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID;
		CTicketDictionary::Update($arTD["ID"], array("SID"=>$arTD["SID"], "arrSITE"=>$arSites));
	}
}

// sources

$arFilter = Array("TYPE" => "SR");

$rsTD = CTicketDictionary::GetList($by, $order, $arFilter, $is_filtered);
while ($arTD = $rsTD->Fetch())
{
	$arSites = CTicketDictionary::GetSiteArray($arTD["ID"]);
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID;
		CTicketDictionary::Update($arTD["ID"], array("SID"=>$arTD["SID"], "arrSITE"=>$arSites));
	}
}

// hardness

$arFilter = Array("TYPE" => "D");

$rsTD = CTicketDictionary::GetList($by, $order, $arFilter, $is_filtered);
while ($arTD = $rsTD->Fetch())
{
	$arSites = CTicketDictionary::GetSiteArray($arTD["ID"]);
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID;
		CTicketDictionary::Update($arTD["ID"], array("SID"=>$arTD["SID"], "arrSITE"=>$arSites));
	}
}


$APPLICATION->SetGroupRight("support", WIZARD_EXTRANET_GROUP, "R");
$APPLICATION->SetGroupRight("support", WIZARD_EXTRANET_SUPPORT_GROUP, "W");
$APPLICATION->SetGroupRight("support", WIZARD_EXTRANET_ADMIN_GROUP, "W");

// SLA

$arFields = array(
	"NAME" => GetMessage('SLA_EXTRANET'),
	"SORT" => 100,
	"arGROUPS" => array(WIZARD_EXTRANET_GROUP),
	"arSITES" => array(WIZARD_SITE_ID),
);

CTicketSLA::Set($arFields, 0, false);

?>