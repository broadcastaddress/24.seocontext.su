<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

function __MB_TASKS_LIST_GetFilterEntities()
{
	// Define available filter entities
	$oFieldOriginator = new CTaskFilterEntityUser(
		'TASKS_FILTER_BUILDER_PRESET_ORIGINATOR_NAME',
		'CREATED_BY'
	);

	$oFieldResponsible = new CTaskFilterEntityUser(
		'TASKS_FILTER_BUILDER_PRESET_RESPONSIBLE_NAME',
		'RESPONSIBLE_ID'
	);

	$oFieldCloser = new CTaskFilterEntityUser(
		'TASKS_FILTER_BUILDER_PRESET_CLOSER_NAME',
		'CLOSED_BY'
	);

	$oFieldPriority = new CTaskFilterEntity(
		'TASKS_FILTER_BUILDER_PRESET_PRIORITY_NAME',
		'PRIORITY',
		array(
			array(
				'name'  => 'TASKS_FILTER_BUILDER_PRESET_PRIORITY_LOW',
				'value' => 0
			),
			array(
				'name'  => 'TASKS_FILTER_BUILDER_PRESET_PRIORITY_MIDDLE',
				'value' => 1
			),
			array(
				'name'  => 'TASKS_FILTER_BUILDER_PRESET_PRIORITY_HIGH',
				'value' => 2
			)
		)
	);

	$oFieldStatus = new CTaskFilterEntity(
		'TASKS_FILTER_BUILDER_PRESET_STATUS_NAME',
		'STATUS',
		array(
			array(
				'name'  => 'TASKS_FILTER_BUILDER_PRESET_STATUS_ACTIVE',
				'value' => array(
					CTasks::METASTATE_VIRGIN_NEW,
					CTasks::METASTATE_EXPIRED,
					CTasks::STATE_NEW,
					CTasks::STATE_PENDING,
					CTasks::STATE_IN_PROGRESS,
					CTasks::STATE_DECLINED
				)
			),
			array(
				'name'  => 'TASKS_FILTER_BUILDER_PRESET_STATUS_DEFERRED',
				'value' => CTasks::STATE_DEFERRED
			),
			array(
				'name'  => 'TASKS_FILTER_BUILDER_PRESET_STATUS_DECLINED',
				'value' => CTasks::STATE_DECLINED
			),
			array(
				'name'  => 'TASKS_FILTER_BUILDER_PRESET_STATUS_EXPIRED',
				'value' => CTasks::METASTATE_EXPIRED
			)
		)
	);

	$arFilterEntities = array(
		$oFieldOriginator,
		$oFieldResponsible,
		$oFieldCloser,
		$oFieldPriority,
		$oFieldStatus
	);

	return ($arFilterEntities);
}