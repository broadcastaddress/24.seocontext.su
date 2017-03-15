<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION */
/** @var CBitrixComponent $component */
$APPLICATION->IncludeComponent(
	'bitrix:crm.activity.list',
	"grid",
	array(
		'PERMISSION_TYPE' => 'WRITE',
		'ENABLE_TOOLBAR' => true,
		'ENABLE_NAVIGATION' => true,
		'DISPLAY_REFERENCE' => true,
		'DISPLAY_CLIENT' => true,
		'AJAX_MODE' => 'Y',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_HISTORY' => 'N',
		'PREFIX' => 'MY_ACTIVITIES',
		'PATH_TO_ACTIVITY_LIST' => $arResult['PATH_TO_ACTIVITY_LIST'],
		'PATH_TO_ACTIVITY_WIDGET' => $arResult['PATH_TO_ACTIVITY_WIDGET'],
		'NAVIGATION_CONTEXT_ID' => $arResult['NAVIGATION_CONTEXT_ID']
	),
	$component
);