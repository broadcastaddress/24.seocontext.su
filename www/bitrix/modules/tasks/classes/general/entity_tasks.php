<?php

class CTasksEntity extends CBaseEntity
{
	protected function __construct() {}

	public function initialize()
	{
		$this->className = __CLASS__;
		$this->filePath = __FILE__;

		global $DB;

		$this->fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'TITLE' => array(
				'data_type' => 'string'
			),
			'PRIORITY' => array(
				'data_type' => 'string'
			),
			'STATUS' => array(
				'data_type' => 'string'
			),
			'RESPONSIBLE_ID' => array(
				'data_type' => 'integer'
			),
			'RESPONSIBLE' => array(
				'data_type' => 'User',
				'reference' => array('=this.RESPONSIBLE_ID' => 'ref.ID')
			),
			'DATE_START' => array(
				'data_type' => 'datetime'
			),
			'START_DATE_PLAN' => array(
				'data_type' => 'datetime'
			),
			'END_DATE_PLAN' => array(
				'data_type' => 'datetime'
			),
			'DURATION_PLAN' => array(
				'data_type' => 'integer'
			),
			'DURATION_TYPE' => array(
				'data_type' => 'string'
			),
			'DEADLINE' => array(
				'data_type' => 'datetime'
			),
			'CREATED_BY' => array(
				'data_type' => 'integer'
			),
			'CREATED_BY_USER' => array(
				'data_type' => 'User',
				'reference' => array(
					'=this.CREATED_BY' => 'ref.ID'
				)
			),
			'CREATED_DATE' => array(
				'data_type' => 'datetime'
			),
			'CHANGED_BY' => array(
				'data_type' => 'integer'
			),
			'CHANGED_BY_USER' => array(
				'data_type' => 'User',
				'reference' => array('=this.CHANGED_BY' => 'ref.ID')
			),
			'CHANGED_DATE' => array(
				'data_type' => 'datetime'
			),
			'STATUS_CHANGED_BY' => array(
				'data_type' => 'integer'
			),
			'STATUS_CHANGED_BY_USER' => array(
				'data_type' => 'User',
				'reference' => array('=this.STATUS_CHANGED_BY' => 'ref.ID')
			),
			'STATUS_CHANGED_DATE' => array(
				'data_type' => 'datetime'
			),
			'CLOSED_BY' => array(
				'data_type' => 'integer'
			),
			'CLOSED_BY_USER' => array(
				'data_type' => 'User',
				'reference' => array('=this.CLOSED_BY' => 'ref.ID')
			),
			'CLOSED_DATE' => array(
				'data_type' => 'datetime'
			),
			'PARENT_ID' => array(
				'data_type' => 'integer'
			),
			'PARENT' => array(
				'data_type' => 'Tasks',
				'reference' => array('=this.PARENT_ID' => 'ref.ID')
			),
			'SITE_ID' => array(
				'data_type' => 'integer'
			),
			'SITE' => array(
				'data_type' => 'Site',
				'reference' => array('=this.SITE_ID' => 'ref.LID')
			),
			'ADD_IN_REPORT' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'GROUP_ID' => array(
				'data_type' => 'integer'
			),
			'GROUP' => array(
				'data_type' => 'Workgroup',
				'reference' => array('=this.GROUP_ID' => 'ref.ID')
			),
			'MARK' => array(
				'data_type' => 'string'
			),

			'DURATION' => array(
				'data_type' => 'integer',
				'expression' => array(
					'(SELECT  SUM(MINUTES) FROM b_tasks_elapsed_time WHERE TASK_ID = %s)',
					'ID'
				)
			),

			'DURATION_FOR_PERIOD' => array(
				'data_type' => 'integer',
				'expression' => array(
					'(SELECT  SUM(CASE WHEN CREATED_DATE %%RT_TIME_INTERVAL%% THEN MINUTES ELSE 0 END) FROM b_tasks_elapsed_time WHERE TASK_ID = %s)',
					'ID'
				)
			),

			'DURATION_PLAN_MINUTES' => array(
				'data_type' => 'integer',
				'expression' => array(
					'%s * (CASE WHEN %s = \'days\' THEN 8 ELSE 1 END) * 60',
					'DURATION_PLAN', 'DURATION_TYPE'
				)
			),

			'IS_NEW' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN %s %%RT_TIME_INTERVAL%% THEN 1 ELSE 0 END',
					'CREATED_DATE'
				),
				'values' => array(0, 1)
			),

			'IS_OPEN' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN %s %%RT_TIME_INTERVAL%% THEN 0 ELSE 1 END',
					'DATE_START'
				),
				'values' => array(0, 1)
			),

			'IS_OVERDUE' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN %s IS NOT NULL AND (%s < %s OR (%s IS NULL AND %s < '.$DB->CurrentTimeFunction().')) THEN 1 ELSE 0 END',
					'DEADLINE', 'DEADLINE', 'CLOSED_DATE', 'CLOSED_DATE', 'DEADLINE'
				),
				'values' => array(0, 1)
			),
			'IS_OVERDUE_PRCNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'SUM(%s)/COUNT(%s)*100',
					'IS_OVERDUE', 'ID'
				)
			),

			'IS_MARKED' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN %s IN(\'P\', \'N\') THEN 1 ELSE 0 END',
					'MARK'
				),
				'values' => array(0, 1)
			),
			'IS_MARKED_PRCNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'SUM(%s)/COUNT(%s)*100',
					'IS_MARKED', 'ID'
				)
			),

			'IS_EFFECTIVE' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN %s = \'P\' THEN 1 ELSE 0 END',
					'MARK'
				),
				'values' => array(0, 1)
			),
			'IS_EFFECTIVE_PRCNT' => array(
				'data_type' => 'integer',
				'expression' => array(
					'SUM(%s)/COUNT(%s)*100',
					'IS_EFFECTIVE', 'ID'
				)
			),

			'IS_FINISHED' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'CASE WHEN %s %%RT_TIME_INTERVAL%% AND %s IS NOT NULL THEN 1 ELSE 0 END',
					'CLOSED_DATE', 'CLOSED_DATE'
				),
				'values' => array(0, 1)
			),

			'IS_RUNNING' => array(
				'data_type' => 'boolean',
				'expression' => array(
					'1'
				),
				'values' => array(0, 1)
			)
		);
	}
}