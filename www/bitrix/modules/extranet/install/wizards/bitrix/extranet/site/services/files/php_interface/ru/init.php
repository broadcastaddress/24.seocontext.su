<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

AddEventHandler("main", "OnBeforeProlog", "__AddEXButton");
function __AddEXButton()
{
	if($GLOBALS["USER"]->IsAdmin())
		$GLOBALS["APPLICATION"]->AddPanelButton(array(
			"HREF"=>"/bitrix/admin/wizard_install.php?lang=ru&wizardName=bitrix:extranet&".bitrix_sessid_get(),
			"ID"=>"extranet_wizard",
			"ICON"=>"icon-edit",
			"ALT"=>"Запустить мастер смены дизайна и настройки сайта экстранета",
			"TEXT"=>'Мастер настройки экстранета',
			"MAIN_SORT"=>"326",
			"SORT"=>20,
			//"MENU"=> $arMenu,
		));
}
?>
