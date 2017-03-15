<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (!CModule::IncludeModule('voximplant'))
	return;

$arResult['LINK_TO_BUY'] = '';
if (IsModuleInstalled('bitrix24'))
{
	if (in_array(LANGUAGE_ID, array("ru", "en", "de", "la")))
	{
		$arResult['LINK_TO_BUY'] = '/settings/license_phone.php';
	}
}
else
{
	if (LANGUAGE_ID == 'ru')
	{
		$arResult['LINK_TO_BUY'] = 'http://www.1c-bitrix.ru/buy/intranet.php#tab-call-link';
	}
	else if (LANGUAGE_ID == 'ua')
	{
		$arResult['LINK_TO_BUY'] = 'http://www.1c-bitrix.ua/buy/intranet.php#tab-call-link';
	}
	else if (LANGUAGE_ID == 'kz')
	{
	}
	else if (LANGUAGE_ID == 'de')
	{
		$arResult['LINK_TO_BUY'] = 'http://www.bitrix.de/buy/intranet.php#tab-sip-link';
	}
	else
	{
		$arResult['LINK_TO_BUY'] = 'http://www.bitrixsoft.com/buy/intranet.php#tab-sip-link';
	}
}


if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;

?>