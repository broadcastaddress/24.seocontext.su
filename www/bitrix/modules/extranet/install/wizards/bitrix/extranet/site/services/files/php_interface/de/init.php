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
			"ALT"=>"Assistent für Extranet-Sitedesign ausführen",
			"TEXT"=>'Extranet-Konfigurationsassistent',
			"MAIN_SORT"=>"326",
			"SORT"=>20,
			//"MENU"=> $arMenu,
		));
}
?>
