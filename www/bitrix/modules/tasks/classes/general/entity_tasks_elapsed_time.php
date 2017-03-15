<?php

class CTasksElapsedTimeEntity extends CBaseEntity
{
	protected function __construct() {}

	public function initialize()
	{
		$this->className = __CLASS__;
		$this->filePath = __FILE__;

		$this->fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'CREATED_DATE' => array(
				'data_type' => 'date'
			),
			'USER_ID' => array(
				'data_type' => 'integer'
			),
			'USER' => array(
				'data_type' => 'User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
			'TASK_ID' => array(
				'data_type' => 'integer'
			),
			'TASK' => array(
				'data_type' => 'Tasks',
				'reference' => array('=this.TASK_ID' => 'ref.ID')
			),
			'MINUTES' => array(
				'data_type' => 'integer'
			)
		);
	}

}