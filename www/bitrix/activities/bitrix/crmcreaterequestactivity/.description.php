<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	'NAME' => GetMessage('CRM_CREATE_REQUEST_NAME'),
	'DESCRIPTION' => GetMessage('CRM_CREATE_REQUEST_DESC'),
	'TYPE' => array('activity', 'robot_activity'),
	'CLASS' => 'CrmCreateRequestActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => array(
		'ID' => 'interaction',
		"OWN_ID" => 'crm',
		"OWN_NAME" => 'CRM',
	),
	'FILTER' => array(
		'INCLUDE' => array(
			array('crm', 'CCrmDocumentDeal'),
			array('crm', 'CCrmDocumentLead')
		),
	),
	'ROBOT_SETTINGS' => array(
		'RESPONSIBLE_PROPERTY' => 'Responsible'
	),
);