<?php

class CTasksMemberEntity extends CBaseEntity
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
			'TASK_FOLLOWED' => array(
				'data_type' => 'Tasks',
				'reference' => array(
					'=this.TASK_ID' => 'ref.ID',
					'=this.TYPE' => array('?', 'U')
				)
			),
			'TASK_COWORKED' => array(
				'data_type' => 'Tasks',
				'reference' => array(
					'=this.TASK_ID' => 'ref.ID',
					'=this.TYPE' => array('?', 'A')
				)
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'USER' => array(
				'data_type' => 'User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
			'TYPE' => array(
				'data_type' => 'string',
				'primary' => true
			)
		);
	}

}