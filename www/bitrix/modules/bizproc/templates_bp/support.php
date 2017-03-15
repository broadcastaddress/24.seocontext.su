<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

class CBPTemplates_Support
{
	function GetName()
	{
		return GetMessage("BPT_TTITLE");
	}

	function GetVariables()
	{
		$arBPTemplateVariables = array(
			'ParameterOpRead' => array(
				'Name' => GetMessage("BPT_SPT_PARAM_OP_READ"),
				'Description' => '',
				'Type' => 'S:UserID',
				'Required' => true,
				'Multiple' => true,
				'Default' => 'Author'
			),
			'ParameterOpCreate' => array(
				'Name' => GetMessage("BPT_SPT_PARAM_OP_CREATE"),
				'Description' => '',
				'Type' => 'S:UserID',
				'Required' => true,
				'Multiple' => true,
				'Default' => 'Author'
			),
			'ParameterOpAdmin' => array(
				'Name' => GetMessage("BPT_SPT_PARAM_OP_ADMIN"),
				'Description' => '',
				'Type' => 'S:UserID',
				'Required' => true,
				'Multiple' => true,
				'Default' => ''
			),
			'ParameterOperator' => array(
				'Name' => GetMessage("BPT_SPT_PARAM_OPERATOR"),
				'Description' => '',
				'Type' => 'S:UserID',
				'Required' => true,
				'Multiple' => true,
				'Default' => ''
			),
			'ParameterController' => array(
				'Name' => GetMessage("BPT_SPT_PARAM_CONTROLLER"),
				'Description' => '',
				'Type' => 'S:UserID',
				'Required' => true,
				'Multiple' => true,
				'Default' => ''
			),
			'VariableStatus' => array(
				'Name' => GetMessage("BPT_SPT_PARAM_STATUS"),
				'Description' => '',
				'Type' => 'L',
				'Required' => true,
				'Multiple' => false,
                'Options' => array(
                    'first'  => GetMessage("BPT_SPT_PARAM_STATUS_VALUE_1"),
                    'second' => GetMessage("BPT_SPT_PARAM_STATUS_VALUE_2"),
                    'thrird' => GetMessage("BPT_SPT_PARAM_STATUS_VALUE_3"),
                    'fourth' => GetMessage("BPT_SPT_PARAM_STATUS_VALUE_4"),
                ),
				'Default' => GetMessage("BPT_SPT_PARAM_STATUS_VALUE_1")
			),
			'who_answer' => array(
				'Name' => GetMessage("BPT_SPT_PARAM_WANSWER"),
				'Description' => '',
				'Type' => 'S:UserID',
				'Required' => true,
				'Multiple' => false,
				'Default' => ''
			),
      'text_answer' => array(
          'Name' => GetMessage("BPT_SPT_PARAM_ANSWER"),
          'Description' => '',
          'Type' => 'T',
          'Required' => true,
          'Multiple' => false,
          'Default' => ''
      ),
      'VariableSiteEmail' => array(
          'Name' => GetMessage("BPT_SPT_PARAM_OFFEMAIL"),
          'Description' => '',
          'Type' => 'S',
          'Required' => true,
          'Multiple' => false,
          'Default' => ''
      ),
		);

		return $arBPTemplateVariables;
	}

	function GetParameters()
	{
		$arBPTemplateParameters = array(
			'NAME' => array(
				'Name' => GetMessage("BPT_SPT_P_NAME"),
				'Description' => '',
				'Type' => 'S',
				'Required' => true,
				'Multiple' => false,
				'Default' => ''
			),
			'TEXT' => array(
				'Name' => GetMessage("BPT_SPT_P_TEXT"),
				'Description' => '',
				'Type' => 'T',
				'Required' => true,
				'Multiple' => false,
				'Default' => ''
			),   
			'TYPE' => array(
				'Name' => GetMessage("BPT_SPT_P_TYPE"),
				'Description' => '',
				'Type' => 'L',
				'Required' => true,
				'Multiple' => false,
                'Options' => array(
                    'first'  => GetMessage("BPT_SPT_P_TYPE_VALUE_1"),
                    'second' => GetMessage("BPT_SPT_P_TYPE_VALUE_2"), 
                ),
				'Default' => GetMessage("BPT_SPT_P_TYPE_VALUE_1")
			),
			'ADDRESS' => array(
				'Name' => GetMessage("BPT_SPT_P_ADDRESS"),
				'Description' => '',
				'Type' => 'S',
				'Required' => true,
				'Multiple' => false,
				'Default' => ''
			),
			'PHONE' => array(
				'Name' => GetMessage("BPT_SPT_P_PHONE"),
				'Description' => '',
				'Type' => 'S',
				'Required' => true,
				'Multiple' => false,
				'Default' => ''
			),
			'EMAIL' => array(
				'Name' => GetMessage("BPT_SPT_P_EMAIL"),
				'Description' => '',
				'Type' => 'S',
				'Required' => false,
				'Multiple' => false,
				'Default' => ''
			),
			'DATE_FROM' => array(
				'Name' => GetMessage("BPT_SPT_P_DATE_FROM"),
				'Description' => '',
				'Type' => 'S:DateTime',
				'Required' => true,
				'Multiple' => false,
				'Default' => ''
			),
            'ID' => array(
                'Name' => GetMessage("BPT_SPT_P_ID"),
                'Description' => GetMessage("BPT_SPT_P_ID_DESC"),
                'Type' => 'S',
                'Required' => false,
                'Multiple' => false,
                'Default' => ''
            ),
		);                                                                  

		return $arBPTemplateParameters;
	}

	function GetTemplate()
	{
		$arBPTemplate = array(
            array(
                'Type' => 'SequentialWorkflowActivity',
                'Name' => 'Template',
                'Properties' => array(
                    'Title' => GetMessage('BPT_BT_SWA'),
                    'Permission' => array(
                        'read' => array('Variable', 'ParameterOpRead'),
                        'create' => array('Variable', 'ParameterOpCreate'),
                        'admin' => array('Variable', 'ParameterOpAdmin')
                    )
                ),
                'Children' => array(
                    array(
                        'Type' => 'SetStateTitleActivity',
                        'Name' => 'A8820_23518_17015_17291',
                        'Properties' => array(
                            'TargetStateTitle' => GetMessage('BPT_SPT_STA1_STATE_TITLE'),
                            'Title' => GetMessage('BPT_SPT_STA1_TITLE')
                        )
                    ), 
                    array(
                        'Type' => 'SetFieldActivity',
                        'Name' => 'A8624_69347_73745_31643',
                        'Properties' => array(
                            'FieldValue' => array(
                                'ACTIVE_FROM' => '{=Template:DATE_FROM}',
                                'NAME' => '{=Template:NAME}',
                                'PREVIEW_TEXT' => '{=Template:TEXT}',
                                'PROPERTY_ADDRESS' => '{=Template:ADDRESS}',
                                'PROPERTY_TYPE' => '{=Template:TYPE}',
                                'PROPERTY_ID' => '{=Template:ID}',
                                'PROPERTY_STATUS' => GetMessage('BPT_SPT_PARAM_STATUS_VALUE_1'),
                                'PROPERTY_PHONE' => '{=Template:PHONE}',
                                'PROPERTY_EMAIL' => '{=Template:EMAIL}'
                            ),
                            'Title' => GetMessage('BPT_BT_SFA1_TITLE')
                        )
                    ), 
                    array(
                                        'Type' => 'MailActivity',
                                        'Name' => 'A42199_6386_52298_3480',
                                        'Properties' => array(
                                            'MailSubject' => 'SYS_ADDED',
                                            'MailText' => '<ADDED>
								    <ID>{=Template:ID}</ID>
								    <REGNOMER>{=Document:ID}</REGNOMER>
								</ADDED>',
                                            'MailMessageType' => 'plain',
                                            'MailCharset' => 'windows-1251',
                                            'MailUserFrom' => '',
                                            'MailUserFromArray' => array(array('Variable', 'who_answer')),
                                            'MailUserTo' => '',
                                            'MailUserToArray' => array(array('Variable', 'VariableSiteEmail')),
                                            'Title' => GetMessage('BPT_SPT_M2_TITLE')
                                        )
                                    ),
                    array(
                        'Type' => 'ParallelActivity',
                        'Name' => 'A68361_69339_35789_44122',
                        'Properties' => array(
                            'Title' => GetMessage('BPT_SPT_PA1_TITLE')
                        ),
                        'Children' => array(
                            array(
                                'Type' => 'SequenceActivity',
                                'Name' => 'A76314_57293_64736_84954',
                                'Properties' => array(
                                    'Title' => GetMessage('BPT_SPT_SA1_TITLE')
                                ),
                                'Children' => array(
                                    array(
                                        'Type' => 'WhileActivity',
                                        'Name' => 'A84706_38584_15219_58651',
                                        'Properties' => array(
                                            'Title' => GetMessage('BPT_SPT_WA1_TITLE'),
                                            'propertyvariablecondition' => array(
                                                array('VariableStatus', '=', GetMessage('BPT_SPT_PARAM_STATUS_VALUE_1'))
                                            )
                                        ),
                                        'Children' => array(
                                            array(
                                                'Type' => 'SequenceActivity',
                                                'Name' => 'A51875_39345_97903_50948',
                                                'Properties' => array(
                                                    'Title' => GetMessage('BPT_SPT_SA2_TITLE')
                                                ),
                                                'Children' => array(
                                                    array(
                                                        'Type' => 'ApproveActivity',
                                                        'Name' => 'A86137_11217_80171_31958',
                                                        'Properties' => array(
                                                            'ApproveType' => 'any',
                                                            'OverdueDate' => '',
                                                            'ApproveMinPercent' => '50',
                                                            'ApproveWaitForAll' => 'N',
                                                            'Name' => GetMessage('BPT_SPT_AA1_NAME'),
                                                            'Description' => GetMessage('BPT_SPT_AA1_DESC'),
                                                            'Parameters' => '',
                                                            'StatusMessage' => GetMessage('BPT_SPT_AA1_STATUS'),
                                                            'SetStatusMessage' => 'N',
                                                            'Users' => array('Variable', 'ParameterOperator'),
                                                            'TimeoutDuration' => '0',
                                                            'Title' => GetMessage('BPT_SPT_AA1_TITLE')
                                                        ),
                                                        'Children' => array(
                                                            array(
                                                                'Type' => 'SequenceActivity',
                                                                'Name' => 'A96217_58555_95872_72987',
                                                                'Properties' => array(
                                                                    'Title' => GetMessage('BPT_SPT_SA3_TITLE')
                                                                ),
                                                                'Children' => array(
                                                                    array(
                                                                        'Type' => 'RequestInformationActivity',
                                                                        'Name' => 'A46299_94851_27774_87787',
                                                                        'Properties' => array(
                                                                            'OverdueDate' => '',
                                                                            'Name' => GetMessage('BPT_SPT_RI1_NAME'),
                                                                            'Description' => GetMessage('BPT_SPT_RI1_DESC'),
                                                                            'Parameters' => '',
                                                                            'RequestedInformation' => array(
                                                                                array(
                                                                                    'Name' => 'who_answer',
                                                                                    'Title' => GetMessage('BPT_SPT_PARAM_WANSWER'),
                                                                                    'Type' => 'S:UserID',
                                                                                    'Default' => '',
                                                                                    'Required' => '1',
                                                                                    'Multiple' => '0'
                                                                                )
                                                                            ),
                                                                            'Users' => array('Variable', 'ParameterOperator'),
                                                                            'Title' => GetMessage('BPT_SPT_RI1_TITLE')
                                                                        )
                                                                    ), 
                                                                    array(
                                                                        'Type' => 'RequestInformationActivity',
                                                                        'Name' => 'A22534_79457_32779_56038',
                                                                        'Properties' => array(
                                                                            'OverdueDate' => '',
                                                                            'Name' => GetMessage('BPT_SPT_RI2_NAME'),
                                                                            'Description' => GetMessage('BPT_SPT_RI2_DESC'),
                                                                            'Parameters' => '',
                                                                            'RequestedInformation' => array(
                                                                                array(
                                                                                    'Name' => 'text_answer',
                                                                                    'Title' => GetMessage('BPT_SPT_PARAM_ANSWER'),
                                                                                    'Type' => 'T',
                                                                                    'Default' => '',
                                                                                    'Required' => '1',
                                                                                    'Multiple' => '0'
                                                                                )
                                                                            ),
                                                                            'Users' => array('Variable', 'who_answer'),
                                                                            'Title' => GetMessage('BPT_SPT_RI2_TITLE')
                                                                        )
                                                                    ), 
                                                                    array(
                                                                        'Type' => 'SetVariableActivity',
                                                                        'Name' => 'A89706_1520_18702_66176',
                                                                        'Properties' => array(
                                                                            'VariableValue' => array(
                                                                                'VariableStatus' => GetMessage('BPT_SPT_PARAM_STATUS_VALUE_3')
                                                                            ),
                                                                            'Title' => GetMessage('BPT_SPT_SV1_TITLE')
                                                                        )
                                                                    ), 
                                                                    array(
                                                                        'Type' => 'SetFieldActivity',
                                                                        'Name' => 'A94192_5523_51795_9440',
                                                                        'Properties' => array(
                                                                            'FieldValue' => array(
                                                                                'DETAIL_TEXT' => '{=Variable:text_answer}',
                                                                                'PROPERTY_STATUS' => GetMessage('BPT_SPT_PARAM_STATUS_VALUE_3')
                                                                            ),
                                                                            'Title' => GetMessage('BPT_SPT_SF2_TITLE')
                                                                        )
                                                                    )
                                                                )
                                                            ), 
                                                            array(
                                                                'Type' => 'SequenceActivity',
                                                                'Name' => 'A74032_25249_65473_81962',
                                                                'Properties' => array(
                                                                    'Title' => GetMessage('BPT_SPT_SA4_TITLE')
                                                                ),
                                                                'Children' => array(
                                                                    array(
                                                                        'Type' => 'SetVariableActivity',
                                                                        'Name' => 'A91026_4282_80572_87857',
                                                                        'Properties' => array(
                                                                            'VariableValue' => array(
                                                                                'VariableStatus' => GetMessage('BPT_SPT_PARAM_STATUS_VALUE_2')
                                                                            ),
                                                                            'Title' => GetMessage('BPT_SPT_SV2_TITLE')
                                                                        )
                                                                    ), 
                                                                    array(
                                                                        'Type' => 'SetFieldActivity',
                                                                        'Name' => 'A83846_83641_16559_33438',
                                                                        'Properties' => array(
                                                                            'FieldValue' => array(
                                                                                'DETAIL_TEXT' => '{=A86137_11217_80171_31958:Comments}',
                                                                                'PROPERTY_STATUS' => GetMessage('BPT_SPT_PARAM_STATUS_VALUE_2')
                                                                            ),
                                                                            'Title' => GetMessage('BPT_SPT_SF2_TITLE')
                                                                        )
                                                                    )
                                                                )
                                                            )
                                                        )
                                                    ), 
                                                    array(
                                                        'Type' => 'SetStateTitleActivity',
                                                        'Name' => 'A2258_19930_53382_47610',
                                                        'Properties' => array(
                                                            'TargetStateTitle' => '{=Variable:VariableStatus}',
                                                            'Title' => GetMessage('BPT_SPT_STA2_TITLE')
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            ), 
                            array(
                                'Type' => 'SequenceActivity',
                                'Name' => 'A13548_83214_49931_25273',
                                'Properties' => array(
                                    'Title' => GetMessage('BPT_SPT_SA5_TITLE')
                                ),
                                'Children' => array(
                                    array(
                                        'Type' => 'SocNetMessageActivity',
                                        'Name' => 'A77677_92773_75_72497',
                                        'Properties' => array(
                                            'MessageText' => GetMessage('BPT_BT_SNMA1_MESSAGE'),
                                            'MessageUserFrom' => array('Document', 'CREATED_BY'),
                                            'MessageUserTo' => array('Variable', 'ParameterController'),
                                            'Title' => GetMessage('BPT_BT_SNMA1_TITLE')
                                        )
                                    )
                                )
                            )
                        )
                    ), 
                    array(
                        'Type' => 'IfElseActivity',
                        'Name' => 'A52694_61021_11842_80985',
                        'Properties' => array(
                            'Title' => GetMessage('BPT_SPT_IE1_TITLE') 
                        ),
                        'Children' => array(
                            array(
                                'Type' => 'IfElseBranchActivity',
                                'Name' => 'A98427_15497_58074_66793',
                                'Properties' => array(
                                    'Title' => GetMessage('BPT_SPT_IEB1_TITLE'),
                                    'propertyvariablecondition' => array(array('ID', '=', '0'))
                                ),
                                'Children' => array(
                                    array(
                                        'Type' => 'ReviewActivity',
                                        'Name' => 'A71623_8445_3107_94572',
                                        'Properties' => array(
                                            'ApproveType' => 'any',
                                            'OverdueDate' => '',
                                            'Name' => GetMessage('BPT_SPT_R1_NAME'),
                                            'Description' => GetMessage('BPT_SPT_R1_DESC'),
                                            'Parameters' => '',
                                            'StatusMessage' => GetMessage('BPT_SPT_R1_STATUS'),
                                            'SetStatusMessage' => 'N',
                                            'TaskButtonMessage' => GetMessage('BPT_SPT_R1_BUTTON'),
                                            'Users' => array('Variable', 'ParameterOperator'),
                                            'TimeoutDuration' => '0',
                                            'Title' => GetMessage('BPT_SPT_R1_TITLE')
                                        )
                                    )
                                )
                            ), 
                            array(
                                'Type' => 'IfElseBranchActivity',
                                'Name' => 'A55119_18890_79683_95270',
                                'Properties' => array(
                                    'Title' => GetMessage('BPT_SPT_IEB2_TITLE'),
                                    'propertyvariablecondition' => array(array('ID', '>', '0'))
                                ),
                                'Children' => array(
                                    array(
                                        'Type' => 'MailActivity',
                                        'Name' => 'A11788_7875_74799_24501',
                                        'Properties' => array(
                                            'MailSubject' => 'SYS_ANSWER',
                                            'MailText' => GetMessage('BPT_SPT_M1_TEXT'),
                                            'MailMessageType' => 'plain',
                                            'MailCharset' => 'windows-1251',
                                            'MailUserFrom' => '',
                                            'MailUserFromArray' => array(array('Variable', 'who_answer')),
                                            'MailUserTo' => '',
                                            'MailUserToArray' => array(array('Variable', 'VariableSiteEmail')),
                                            'Title' => GetMessage('BPT_SPT_M1_TITLE')
                                        )
                                    )
                                )
                            )
                        )
                    ), 
                    array(
                        'Type' => 'SetFieldActivity',
                        'Name' => 'A79523_74466_91744_67761',
                        'Properties' => array(
                            'FieldValue' => array(
                                'PROPERTY_STATUS' => GetMessage('BPT_SPT_PARAM_STATUS_VALUE_4')
                            ),
                            'Title' => GetMessage('BPT_SPT_SF3_TITLE')
                        )
                    ), 
                    array(
                        'Type' => 'SetStateTitleActivity',
                        'Name' => 'A87313_33646_72756_56431',
                        'Properties' => array(
                            'TargetStateTitle' => GetMessage('BPT_SPT_STA3_STATE_TITLE'),
                            'Title' => GetMessage('BPT_SPT_STA3_TITLE')
                        )
                    )
                )
            )
        );

		return $arBPTemplate;
	}

	function GetDocumentFields()
	{
		$arDocumentFields = array(
			array(
				"name" => GetMessage("BPT_SPT_DF_TYPE"),
				"code" => "TYPE",
				"type" => "S",
				"multiple" => "N",
				"required" => "N",
				"options" => "",
			),
			array(
				"name" => GetMessage("BPT_SPT_DF_ADDRESS"),
				"code" => "ADDRESS",
				"type" => "S",
				"multiple" => "N",
				"required" => "N",
				"options" => "",
			),
			array(
				"name" => GetMessage("BPT_SPT_DF_PHONE"),
				"code" => "PHONE",
				"type" => "S",
				"multiple" => "Y",
				"required" => "N",
				"options" => "",
			),
			array(
				"name" => GetMessage("BPT_SPT_DF_EMAIL"),
				"code" => "EMAIL",
				"type" => "S",
				"multiple" => "N",
				"required" => "N",
				"options" => "",
			),
			array(
				"name" => GetMessage("BPT_SPT_DF_ID"),
				"code" => "ID",
				"type" => "S",
				"multiple" => "N",
				"required" => "N",
				"options" => "",
			),
            array(
                "name" => GetMessage("BPT_SPT_DF_STATUS"),
                "code" => "STATUS",
                "type" => "S",
                "multiple" => "N",
                "required" => "Y",
                "options" => "",
            ),
		);         

		return $arDocumentFields;
	}
}

$bpTemplateObject = new CBPTemplates_Support();
?>