<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Crm\Kanban\Helper;

$filter = Helper::getFilter($arParams['ENTITY_TYPE']);
$grid = Helper::getGrid($arParams['ENTITY_TYPE']);
$gridId = Helper::getGridId($arParams['ENTITY_TYPE']);
$gridFilter = (array)$grid->GetFilter($filter);
$options = (array)$grid->GetOptions();
$rows = isset($options['filter_rows'])
		? array_fill_keys(explode(',', $options['filter_rows']), true)
		: Helper::getDefaultFilterKey($arParams['ENTITY_TYPE']);

$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.filter',
	'flat',
	array(
		'GRID_ID' => $gridId,
		'FILTER' => $filter,
		'FILTER_ROWS' => $rows,
		'FILTER_FIELDS' => $gridFilter,
		'FILTER_PRESETS' => array(),
		'RENDER_FILTER_INTO_VIEW' => false,
		'HIDE_FILTER' => false,
		'OPTIONS' => $options,
		'ENABLE_PROVIDER' => true,
		'NAVIGATION_BAR' => $arParams['NAVIGATION_BAR']
	),
	$component,
	array('HIDE_ICONS' => true)
);

//bug fix
if (!isset($gridFilter['GRID_FILTER_APPLIED']) || !isset($gridFilter['ASSIGNED_BY_ID']))
{
	$gridFilter['ASSIGNED_BY_ID'] = 0;//\CCrmSecurityHelper::GetCurrentUserID();
}
if ($gridFilter['ASSIGNED_BY_ID'] != $filter['ASSIGNED_BY_ID']['valuedb'])
{
	Helper::setSelectorUserId('ASSIGNED_BY_ID', $gridFilter['ASSIGNED_BY_ID']);
	LocalRedirect($APPLICATION->getCurPageParam(), true);
}