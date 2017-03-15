<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("subscribe"))
	return;

if (WIZARD_IS_RERUN !== true)
{
	$APPLICATION->SetGroupRight("subscribe", WIZARD_EXTRANET_ADMIN_GROUP, "W");
}

$rsRubric = CRubric::GetList(Array(), Array("NAME" => GetMessage("SUBSCRIBE_EXTRANET"), "LID" => WIZARD_SITE_ID));
if(!$rsRubric->Fetch())
{
	$arFields = Array(
		"ACTIVE"	=> "Y",
		"NAME"		=> GetMessage("SUBSCRIBE_EXTRANET"),
		"SORT"		=> 100,
		"DESCRIPTION"	=> "",
		"LID"		=> WIZARD_SITE_ID,
		"AUTO"		=> "N",
	);

	$obRubric = new CRubric;
	$ID = $obRubric->Add($arFields);
}
?>