<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

AddEventHandler("main", "OnBeforeProlog", "__AddEXButton");
function __AddEXButton()
{
	if($GLOBALS["USER"]->IsAdmin())
		$GLOBALS["APPLICATION"]->AddPanelButton(array(
			"HREF"=>"/bitrix/admin/wizard_install.php?lang=en&wizardName=bitrix:extranet&".bitrix_sessid_get(),
			"ID"=>"extranet_wizard",
			"ICON"=>"icon-edit",
			"ALT"=>"Run extranet site design wizard",
			"TEXT"=>'Extranet Configuration Wizard',
			"MAIN_SORT"=>"326",
			"SORT"=>20,
			//"MENU"=> $arMenu,
		));
}
?>
