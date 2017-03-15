<?php

class CTasksTagEntity extends CBaseEntity
{
	protected function __construct() {}

	public function initialize()
	{
		$this->className = __CLASS__;
		$this->filePath = __FILE__;

		$this->fieldsMap = array(
			'TASK_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'TASK' => array(
				'data_type' => 'Tasks',
				'reference' => array('=this.TASK_ID' => 'ref.ID')
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'USER' => array(
				'data_type' => 'User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
			'NAME' => array(
				'data_type' => 'string',
				'primary' => true
			)
		);
	}
}
