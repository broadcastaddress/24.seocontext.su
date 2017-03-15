<?php
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true); 
define('NO_KEEP_STATISTIC', true);
define('BX_STATISTIC_BUFFER_USED', false);
define('NO_LANG_FILES', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_PUBLIC_TOOLS', true);

$site_id = '';
if (isset($_REQUEST['site']) && is_string($_REQUEST['site']))
{
	$site_id = isset($_REQUEST['site'])? trim($_REQUEST['site']): '';
	$site_id = substr(preg_replace('/[^a-z0-9_]/i', '', $site_id), 0, 2);

	define('SITE_ID', $site_id);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/bx_root.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

CUtil::JSPostUnescape();

if ( ! (isset($GLOBALS['USER']) && is_object($GLOBALS['USER']) && $GLOBALS['USER']->IsAuthorized()) )
	exit(json_encode(array('status' => 'failed')));

$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

$lng = isset($_REQUEST['lang'])? trim($_REQUEST['lang']): '';
$lng = substr(preg_replace('/[^a-z0-9_]/i', '', $lng), 0, 2);

if ( ! defined('LANGUAGE_ID') )
{
	$rsSite = CSite::GetByID($site_id);
	if ($arSite = $rsSite->Fetch())
		define('LANGUAGE_ID', $arSite['LANGUAGE_ID']);
	else
		define('LANGUAGE_ID', 'en');
}

$langFilename = dirname(__FILE__) . '/lang/' . $lng . '/ajax.php';
if (file_exists($langFilename))
	__IncludeLang($langFilename);

if (CModule::IncludeModule('compression'))
	CCompress::Disable2048Spaces();

// write and close session to prevent lock;
session_write_close();

require_once(dirname(__FILE__) . '/functions.php');

$arResult = array(
	'bResultInJson' => false,
	'result'        => array()
);

$userId = (int) $GLOBALS['USER']->GetID();

if (
	( ! CModule::IncludeModule('tasks') )
	|| ( ! $GLOBALS["USER"]->IsAuthorized() )
	|| ( ! check_bitrix_sessid() )
)
{
	$arResult = array(
		'bResultInJson' => false,
		'result'        => array()
	);
}
elseif (
	($action === 'remove_filter')
	&& isset($_POST['PRESET_ID'])
)
{
	$presetId = (int) $_POST['PRESET_ID'];
	CTaskFilterCtrl::RemovePreset($userId, $presetId);
}
elseif (
	($action === 'create_filter')
	&& isset($_POST['FILTER_NAME'])
	&& isset($_POST['STACK'])
)
{
	$filterName = $_POST['FILTER_NAME'];
	if (strlen($filterName) == 0)
		$filterName = time();

	$arFilterEntities = __MB_TASKS_LIST_GetFilterEntities();

	$oTmp = new CTaskFilterBuilder($arFilterEntities, $_POST['STACK']);
	$arData = $oTmp->GetEffectiveFilterEntities();
	$sql = $oTmp->GetSqlCode();
	$arFilter = $oTmp->GetFilter();

	$arPresetData = array(
		'Name'      => $filterName,
		'Parent'    => CTaskFilterCtrl::ROOT_PRESET,
		'Condition' => $arFilter
	);

	CTaskFilterCtrl::CreatePreset($userId, $arPresetData);

	$arResult = array(
		'bResultInJson' => false,
		'result'        => array()
	);
}
else
{
	$arResult = array(
		'bResultInJson' => false,
		'result'        => array()
	);
}

$APPLICATION->RestartBuffer();

//header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
if ($arResult['bResultInJson'])
	echo $arResult['result'];
else
{
	echo json_encode($arResult['result']);
}

define('PUBLIC_AJAX_MODE', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
