<?php
/**
 * This is not public API.
 * There is the experimental code in this file! Don't use it!!!
 */

class CTaskFilterBuilder
{
	protected $arEffectiveFilterEntities = array();
	protected $sqlCode = '';
	protected $arFilter = array();

	/**
	 * 
	 */
	public function __construct ($arMatrixFilterEntities, $arStackFromJs)
	{
		$arManifests = array();

		foreach ($arMatrixFilterEntities as $o)
			$arManifests[] = $o->GetManifest();

		$this->arFilter = array(
			'::LOGIC' => 'AND'
			);
		$this->sqlCode = '(';
		
		$bMustBeOperator = false;
		$arCurSubStack = array(
			'::LOGIC' => 'OR'
			);
		$curStackLogic = 'OR';
		$i = 0;
		foreach ($arStackFromJs as $key => $arStackElement)
		{
			if ($bMustBeOperator)
			{
				if ( ! isset($arStackElement['operator']) )
					throw new Exception('Operator expected!');

				$this->sqlCode .= ' ';

				if ($arStackElement['operator'] === 'AND')
					$this->sqlCode .= "\n) AND (\n";
				elseif ($arStackElement['operator'] === 'OR')
					$this->sqlCode .= "\nOR\n";
				else
					throw new Exception('Unknown operator: ' . $arStackElement['operator']);

				$this->sqlCode .= ' ';

				if ($arStackElement['operator'] !== $curStackLogic)
				{
					$curStackLogic = $arStackElement['operator'];

					// If logic changed from OR to AND => we must push substack to filter
					if ($arStackElement['operator'] === 'AND')
					{
						$this->arFilter['::SUBFILTER-' . $i++] = $arCurSubStack;

						// Reinit substack for next usage
						$arCurSubStack = array(
							'::LOGIC' => 'OR'
						);
					}
				}

				$bMustBeOperator = false;
			}
			else
			{
				if ( isset($arStackElement['operator']) )
					throw new Exception('Operator not expected!');

				$bFoundEntity = false;
				foreach ($arManifests as $arManifest)
				{
					if (
						($arManifest['type'] === $arStackElement['type'])
						&& ($arManifest['hash'] === $arStackElement['hash'])
					)
					{
						$bFoundEntity = true;

						$oEntity = new $arManifest['className'](
							$arManifest['name'],
							$arManifest['dbField'],
							$arManifest['values'],
							$arStackElement['value']
						);

						$this->arEffectiveFilterEntities[] = $oEntity;

						$this->sqlCode .= $oEntity->GetSqlCode();

						$field = $arManifest['dbField'];

						if ( ! isset($arCurSubStack[$field]) )
							$arCurSubStack[$field] = $oEntity->GetSelectedValue();
						else
						{
							$arCurSubStack[$field] = array_unique(
								array_merge(
									(array) $arCurSubStack[$field],
									(array) $oEntity->GetSelectedValue()
								)
							);
						}

						break;
					}
				}

				if ( ! $bFoundEntity )
				{
					throw new Exception(
						'Entity not found, type: ' . $arStackElement['type'] 
						. ', hash: ' . $arStackElement['hash']
					);
				}

				$bMustBeOperator = true;
			}
		}

		// Save last substack
		$this->arFilter['::SUBFILTER-' . $i++] = $arCurSubStack;

		$this->sqlCode .= ')';
	}


	public function GetFilter()
	{
		return ($this->arFilter);
	}


	public function GetEffectiveFilterEntities()
	{
		return ($this->arEffectiveFilterEntities);
	}


	public function GetSqlCode()
	{
		return ($this->sqlCode);
	}


	/**
	 * Prepare array
	 */
	public static function ListEntitiesForJs ($arEntities)
	{
		$arManifests = array();

		foreach ($arEntities as $o)
			$arManifests[] = $o->GetManifest();

		$rc = "[\n" . implode(
			",\n", 
			array_map(
				function($arManifest)
				{
					$hash = CUtil::JSEscape($arManifest['hash']);
					$type = CUtil::JSEscape($arManifest['type']);
					$name = CUtil::JSEscape($arManifest['name']);

					$arValuesAsJsObjNotation = array();

					$values = '';

					if (is_array($arManifest['values']))
					{
						foreach ($arManifest['values'] as $id => $arValue)
						{
							$arValuesAsJsObjNotation[] = "\t\t{" 
								. CUtil::JSEscape($id) . ": '" 
								. CUtil::JSEscape($arValue['name']) 
								. "'}";
						}

						$values = implode(",\n", $arValuesAsJsObjNotation);
					}

					$rc = "{\n" 
						. "\thash: '" . $hash . "', \n" 
						. "\ttype: '" . $type . "', \n" 
						. "\tname: '" . $name . "', \n" 
						. "\tvalues: [\n" . $values . "\n\t]\n"
						. '}';

					return ($rc);
				},
				$arManifests
			)
		) . ']';

		return ($rc);
	}
}


class CTaskFilterEntity
{
	const T_PREDEFINED_VALUES = 0;		// Take value from given list
	const T_DATE_SELECTOR     = 1;		// Take value from date selector (one date, or two dates can be)
	const T_USER_SELECTOR     = 2;		// Take value from user selector
	const T_GROUP_SELECTOR    = 3;		// Take value from group selector

	protected $type, $name, $dbField, $values, $selectedValueId, $className;


	public function __construct($name, $dbField, $values = null, $selectedValueId = null)
	{
		$this->name      = $name;
		$this->values    = $values;
		$this->dbField   = $dbField;
		$this->type      = $this->GetType();
		$this->className = $this->GetClassName();

		if ($selectedValueId !== null)
			$this->SetValue($selectedValueId);
	}


	protected function Init()
	{
		$this->type = self::T_PREDEFINED_VALUES;
	}


	public function GetName()
	{
		return ($this->name);
	}


	public function GetType()
	{
		return (self::T_PREDEFINED_VALUES);
	}


	public function GetClassName()
	{
		return ('CTaskFilterEntity');
	}


	public function SetValue($value)
	{
		if ($this->type === self::T_USER_SELECTOR)
			$this->selectedValueId = $value;	// user_id
		elseif ($this->type === self::T_GROUP_SELECTOR)
			$this->selectedValueId = $value;	// group_id
		elseif ($this->type === self::T_DATE_SELECTOR)
			$dateSelected = $value;
		elseif ($this->type === self::T_PREDEFINED_VALUES)
		{
			$arAllowedValues = array();

			foreach ($this->values as $id => $arV)
				$arAllowedValues[] = $id;

			if ( ! in_array( (int) $value, $arAllowedValues, true) )
				throw new Exception('Value out of bounds');
			
			$this->selectedValueId = $value;
		}
		else
			throw new Exception('Error Processing Request', 1);			
	}


	public function GetManifest()
	{
		static $arManifestTypesMap = array(
			self::T_PREDEFINED_VALUES => 'T_PREDEFINED_VALUES',
			self::T_USER_SELECTOR     => 'T_USER_SELECTOR',
			self::T_GROUP_SELECTOR    => 'T_GROUP_SELECTOR',
			self::T_DATE_SELECTOR     => 'T_DATE_SELECTOR'
		);

		if ( ! isset($arManifestTypesMap[$this->type]) )
			throw new Exception('Check $arManifestTypesMap!');

		$type = $arManifestTypesMap[$this->type];

		$arManifest = array(
			'name'            => $this->name,
			'type'            => $type,
			'className'       => $this->className,
			'dbField'         => $this->dbField,
			'values'          => $this->values,
			'selectedValueId' => $this->selectedValueId
		);

		// To preserve synchronization of backend and frontend
		$arManifest['hash'] = sha1(serialize($arManifest));

		return($arManifest);
	}


	public static function GetManifestTypesMap()
	{
		static $arManifestTypesMap = array(
			'T_PREDEFINED_VALUES' => self::T_PREDEFINED_VALUES,
			'T_USER_SELECTOR'     => self::T_USER_SELECTOR,
			'T_GROUP_SELECTOR'    => self::T_GROUP_SELECTOR,
			'T_DATE_SELECTOR'     => self::T_DATE_SELECTOR
		);

		return ($arManifestTypesMap);
	}


	public static function CreateFromManifest($arManifest)
	{
		if ( ! (
			isset($arManifest['name'])
			&& isset($arManifest['type'])
			&& isset($arManifest['className'])
			&& isset($arManifest['dbField'])
			&& isset($arManifest['values'])
			&& isset($arManifest['selectedValueId'])
		) )
		{
			throw new Exception('Wrong manifest');
		}

		$o = new $arManifest['className'](
			$arManifest['name'], 
			$arManifest['dbField'], 
			$arManifest['values'], 
			$arManifest['selectedValueId']
		);

		return ($o);
	}


	/*
		// Use it with new kernel
		public function GetSqlCode0()
		{
			$arFilter = array(
				'LOGIC'   => 'AND',
				'=STATUS' => 7,
				array(
					'LOGIC'        => 'OR',
					'=CREATED_BY'  => 4,
					'=RESPONSIBLE' => 21
				)
			);

			use Bitrix\Main\Entity;
			$q = new Entity\Query(Entity\Base::getInstance('Bitrix\Tasks\TaskTable'));
			$q->setSelect(array('ID'));
			$q->setFilter($arFilter);
			$result = $q->exec();
			soundex($result);
		}
	*/


	public function GetSelectedValue()
	{
		if (is_array($this->values))
		{
			if ( ! isset($this->values[$this->selectedValueId]['value']) )
				throw new Exception('Value not found!');

			$value = $this->values[$this->selectedValueId]['value'];
		}
		else
			$value = $this->selectedValueId;

		return ($value);
	}


	public function GetSqlCode()
	{
		$value = $this->GetSelectedValue();

		$rc = CTasks::GetFilter(array($this->dbField => $value));

		$sql = '(' . implode (' AND ', $rc) . ')';

		return $sql;
	}


	/*
		public function GetSqlCode1()
		{
			if (is_array($this->values))
			{
				if ( ! isset($this->values[$this->selectedValueId]['value']) )
					throw new Exception('Value not found!');

				$value = $this->values[$this->selectedValueId]['value'];
			}
			else
				$value = $this->selectedValueId;

			if (is_array($value))
			{
				$sql = '(';
				$arTmp = array();

				foreach ($value as $v)
					$arTmp[] = $this->dbField . ' = ' . $v;

				$sql .= implode(' OR ', $arTmp) . ')';
			}
			else
				$sql = $this->dbField . ' = ' . $this->selectedValueId;

			return ($sql);
		}
	*/
}


class CTaskFilterEntityUser extends CTaskFilterEntity
{
	final public function GetType()
	{
//throw new Exception('Code not ready yet!');		
		return (self::T_USER_SELECTOR);
	}


	final public function GetClassName()
	{
		return('CTaskFilterEntityUser');
	}
}


class CTaskFilterEntityGroup extends CTaskFilterEntity
{
	final public function GetType()
	{
//throw new Exception('Code not ready yet!');		
		return (self::T_GROUP_SELECTOR);
	}


	final public function GetClassName()
	{
		return('CTaskFilterEntityGroup');
	}
}


class CTaskFilterEntityDate extends CTaskFilterEntity
{
	final public function GetType()
	{
//throw new Exception('Code not ready yet!');		
		return (self::T_DATE_SELECTOR);
	}


	final public function GetClassName()
	{
		return('CTaskFilterEntityDate');
	}
}
