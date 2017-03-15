<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (!CModule::IncludeModule('voximplant'))
	return;

$arResult = Array();

$arResult['SIP_ENABLE'] = CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_SIP);
$arResult['LIST_SIP_NUMBERS'] = Array();

$arResult['LINK_TO_BUY'] = '';
if (IsModuleInstalled('bitrix24'))
{
	$arResult['LINK_TO_BUY'] = '/settings/license_phone_sip.php';
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

$res = Bitrix\Voximplant\ConfigTable::getList(Array(
	'filter' => Array('=PORTAL_MODE' => CVoxImplantConfig::MODE_SIP)
));
while ($row = $res->fetch())
{
	$arResult['LIST_SIP_NUMBERS'][$row['ID']] = Array(
		'PHONE_NAME' => htmlspecialcharsbx($row['PHONE_NAME']),
	);
}

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;

?>