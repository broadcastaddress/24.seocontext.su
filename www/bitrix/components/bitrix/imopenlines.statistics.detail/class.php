<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader,
	\Bitrix\Main\LoaderException,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\HttpApplication;

class ImOpenLinesComponentStatisticsDetail extends CBitrixComponent
{
	private $gridId = "imopenlines_statistic_v2";
	/** @var CGridOptions */
	private $gridOptions;
	private $excelMode = false;
	/** @var \Bitrix\ImOpenlines\Security\Permissions */
	protected $userPermissions;
	protected $showHistory;

	private function init()
	{
		$this->userPermissions = \Bitrix\ImOpenlines\Security\Permissions::createWithCurrentUser();
		
		$this->gridOptions = new CGridOptions($this->gridId);
		if(isset($_REQUEST['excel']) && $_REQUEST['excel'] === 'Y')
			$this->excelMode = 'Y';

		$request = HttpApplication::getInstance()->getContext()->getRequest();
		if ($request->get('CONFIG_ID'))
		{
			$config = \Bitrix\ImOpenLines\Config::getInstance()->get($request->get('CONFIG_ID'));
			$this->arResult['LINE_NAME'] = $config['LINE_NAME'];
		}
	}

	protected function checkModules()
	{
		if (!Loader::includeModule('imopenlines'))
		{
			\ShowError(Loc::getMessage('OL_COMPONENT_MODULE_NOT_INSTALLED'));
			return false;
		}

		if (!Loader::includeModule('imconnector'))
		{
			\ShowError(Loc::getMessage('OL_COMPONENT_MODULE_NOT_INSTALLED'));
			return false;
		}
		return true;
	}
	
	protected function checkAccess()
	{
		if(!$this->userPermissions->canPerform(\Bitrix\ImOpenlines\Security\Permissions::ENTITY_SESSION, \Bitrix\ImOpenlines\Security\Permissions::ACTION_VIEW))
		{
			\ShowError(Loc::getMessage('OL_COMPONENT_ACCESS_DENIED'));
			return false;
		}
		
		return true;
	}

	private static function getCrmLink($row)
	{
		$link = '';

		if ($row['CRM'] == 'Y' && \CModule::IncludeModule('crm'))
		{
			if (in_array($row['CRM_ENTITY_TYPE'], Array('LEAD','CONTACT','COMPANY','DEAL')))
			{
				$link = \Bitrix\ImOpenLines\Common::getServerAddress().\CComponentEngine::MakePathFromTemplate(
					\COption::GetOptionString('crm', 'path_to_'.strtolower($row['CRM_ENTITY_TYPE']).'_show'),
					array(strtolower($row['CRM_ENTITY_TYPE']).'_id' => $row['CRM_ENTITY_ID'])
				);
			}
		}

		return $link;
	}

	private static function formatDuration($duration)
	{
		$duration = intval($duration);
		if ($duration <= 0)
			return "-";

		$currentTime = new \Bitrix\Main\Type\DateTime();
		$formatTime = $currentTime->getTimestamp()-$duration;
		if ($duration < 3600)
		{
			$result = \FormatDate(Array(
				"s" => "sdiff",
				"i" => "idiff",
			), $formatTime);
		}
		elseif ($duration >= 3600 && $duration < 86400)
		{

			$formatTime = $currentTime->getTimestamp()-$duration;
			$result = \FormatDate('Hdiff', $formatTime);

			if ($duration % 3600 != 0)
			{
				$formatTime = $currentTime->getTimestamp()-($duration % 3600);
				$result = $result .' '. \FormatDate(Array(
				"s" => "sdiff",
				"i" => "idiff",
				), $formatTime);
			}
		}
		elseif ($duration >= 86400)
		{

			$formatTime = $currentTime->getTimestamp()-$duration;
			$result = \FormatDate('ddiff', $formatTime);

			if ($duration % 86400 != 0 && ceil($duration % 86400) > 3600)
			{
				$formatTime = $currentTime->getTimestamp()-ceil($duration % 86400);
				$result = $result .' '. \FormatDate(Array(
					"i" => "idiff",
					"H" => "Hdiff",
				), $formatTime);
			}
		}

		return $result;
	}

	private function getFilterDefinition()
	{
		$result = array(
			"DATE_CREATE" => array(
				"id" => "DATE_CREATE",
				"name" => Loc::getMessage("OL_STATS_HEADER_DATE_CREATE"),
				"type" => "date",
				"default" => true
			),
			"DATE_CLOSE" => array(
				"id" => "DATE_CLOSE",
				"name" => Loc::getMessage("OL_STATS_HEADER_DATE_CLOSE"),
				"type" => "date",
				"default" => false
			),
			"OPERATOR_ID" => array(
				"id" => "OPERATOR_ID",
				"name" => Loc::getMessage("OL_STATS_HEADER_OPERATOR_NAME"),
				"type" => "custom",
				"default" => true,
			),
			'SOURCE' => array(
				"id" => "SOURCE",
				"name" => Loc::getMessage("OL_STATS_HEADER_SOURCE_TEXT"),
				"type" => "list",
				"items" => \Bitrix\ImConnector\Connector::getListConnector(),
				"default" => false,
				"params" => array(
					"multiple" => true
				)
			),
			"STATUS" => array(
				"id" => "STATUS",
				"name" => Loc::getMessage("OL_STATS_HEADER_STATUS"),
				"type" => "list",
				"items" => array(
					"" => Loc::getMessage("OL_STATS_FILTER_UNSET"),
					"WAIT_ACTION" => Loc::getMessage("OL_COMPONENT_TABLE_STATUS_WAIT_ACTION"),
					"CLOSED" => Loc::getMessage("OL_COMPONENT_TABLE_STATUS_CLOSED"),
					"WAIT_ANSWER" => Loc::getMessage("OL_COMPONENT_TABLE_STATUS_WAIT_ANSWER"),
					"IN_PROCESS" => Loc::getMessage("OL_COMPONENT_TABLE_STATUS_IN_PROCESS"),
				),
				"default" => false,
			),
			"CRM" => array(
				"id" => "CRM",
				"name" => Loc::getMessage("OL_STATS_HEADER_CRM_TEXT"),
				"default" => false,
				"type" => "list",
				"items" => array(
					"" => Loc::getMessage("OL_STATS_FILTER_UNSET"),
					"Y" => Loc::getMessage("OL_STATS_FILTER_Y"),
					"N" => Loc::getMessage("OL_STATS_FILTER_N"),
				)
			),
			"WORKTIME" => array(
				"id" => "WORKTIME",
				"name" => Loc::getMessage("OL_STATS_HEADER_WORKTIME_TEXT"),
				"default" => false,
				"type" => "list",
				"items" => array(
					"" => Loc::getMessage("OL_STATS_FILTER_UNSET"),
					"Y" => Loc::getMessage("OL_STATS_FILTER_Y"),
					"N" => Loc::getMessage("OL_STATS_FILTER_N"),
				)
			),
		);

		$filterValues = $this->gridOptions->GetFilter($result);
		$operatorId = isset($filterValues['OPERATOR_ID']) ? (int)$filterValues['OPERATOR_ID'] : 0;

		$result["OPERATOR_ID"]["value"] = \Bitrix\ImOpenLines\Ui\Helper::renderUserSelector(
			"OPERATOR_SELECT",
			"OPERATOR_ID_name",
			"OPERATOR_ID",
			"OPERATOR_SELECT_COMPONENT",
			$operatorId
		);

		return $result;
	}

	private function getFilter(array $filterDefinition)
	{
		$result = array();
		$filter = $this->gridOptions->GetFilter($filterDefinition);
		
		$allowedUserIds = \Bitrix\ImOpenlines\Security\Helper::getAllowedUserIds(
			\Bitrix\ImOpenlines\Security\Helper::getCurrentUserId(),
			$this->userPermissions->getPermission(\Bitrix\ImOpenlines\Security\Permissions::ENTITY_SESSION, \Bitrix\ImOpenlines\Security\Permissions::ACTION_VIEW)
		);
		if(isset($filter["OPERATOR_ID"]))
		{
			$filter["OPERATOR_ID"] = (int)$filter["OPERATOR_ID"];
			if(is_array($allowedUserIds))
			{
				$result["=OPERATOR_ID"] = array_intersect($allowedUserIds, array($filter["OPERATOR_ID"]));
			}
			else
			{
				$result["=OPERATOR_ID"] = $filter["OPERATOR_ID"];
			}
		}
		else
		{
			if(is_array($allowedUserIds))
			{
				$result["=OPERATOR_ID"] = $allowedUserIds;
			}
		}

		if (strlen($filter["DATE_CREATE_from"]) > 0)
		{
			try
			{
				$result[">=DATE_CREATE"] = new \Bitrix\Main\Type\Date($filter["DATE_CREATE_from"]);
			} catch (Exception $e){}
		}
		if (strlen($filter["DATE_CREATE_to"]) > 0)
		{
			try
			{
				$result["<=DATE_CREATE"] = new \Bitrix\Main\Type\Date($filter["DATE_CREATE_to"]);
				$result["<=DATE_CREATE"]->add("1 day");
			} catch (Exception $e){}
		}
		if (strlen($filter["DATE_CLOSE_from"]) > 0)
		{
			try
			{
				$result[">=DATE_CLOSE"] = new \Bitrix\Main\Type\Date($filter["DATE_CLOSE_from"]);
			} catch (Exception $e){}
		}
		if (strlen($filter["DATE_CLOSE_to"]) > 0)
		{
			try
			{
				$result["<=DATE_CLOSE"] = new \Bitrix\Main\Type\Date($filter["DATE_CLOSE_to"]);
				$result["<=DATE_CLOSE"]->add("1 day");
			} catch (Exception $e){}
		}

		if(is_array($filter["SOURCE"]))
			$result["=SOURCE"] = $filter["SOURCE"];

		if(isset($filter["STATUS"]))
		{
			switch ($filter["STATUS"])
			{
				case "WAIT_ACTION":
					$result["=WAIT_ACTION"] = "Y";
					$result["=CLOSED"] = "N";
				break;

				case "CLOSED":
					$result["=CLOSED"] = "Y";
				break;

				case "WAIT_ANSWER":
					$result["=WAIT_ANSWER"] = "Y";
					$result["=CLOSED"] = "N";
				break;

				case "IN_PROCESS":
					$result["=WAIT_ACTION"] = "N";
					$result["=CLOSED"] = "N";
					$result["=WAIT_ANSWER"] = "N";
				break;
			}
		}

		if(isset($filter["CRM"]))
			$result["=CRM"] = $filter["CRM"];

		if(isset($filter["WORKTIME"]))
			$result["=WORKTIME"] = $filter["WORKTIME"];
		
		$request = HttpApplication::getInstance()->getContext()->getRequest();
		if ($request->get('CONFIG_ID'))
		{
			$result['=CONFIG_ID'] = $request->get('CONFIG_ID');
		}

		return $result;
	}

	private function getUserHtml($userId, $userData)
	{
		$result = '';
		if ($this->excelMode)
		{
			if ($userId > 0)
			{
				$result = $userData[$userId]["FULL_NAME"];
			}
			else
			{
				$result = '-';
			}
		}
		else
		{
			if ($userId > 0)
			{
				$photoStyle = '';
				if ($userData[$userId]["PHOTO"])
				{
					$photoStyle = "background: url('".$userData[$userId]["PHOTO"]."') no-repeat center;";
				}
				$userHtml = '<span class="ol-stat-user-img user-avatar" style="'.$photoStyle.'"></span>';
				$userHtml .= $userData[$userId]["FULL_NAME"];
			}
			else
			{
				$userHtml = '<span class="ol-stat-user-img user-avatar"></span> &mdash;';
			}
			$result = '<nobr>'.$userHtml.'</nobr>';
		}
		return $result;
	}
	
	private function getUserData($id = array())
	{
		$users = array();
		if (empty($id))
			return $users;

		$orm = \Bitrix\Main\UserTable::getList(Array(
			'filter' => Array('ID' => $id)
		));
		while($user = $orm->fetch())
		{
			$users[$user["ID"]]["FULL_NAME"] =  CUser::FormatName("#NAME# #LAST_NAME#", array(
				"NAME" => $user["NAME"],
				"LAST_NAME" => $user["LAST_NAME"],
				"SECOND_NAME" => $user["SECOND_NAME"],
				"LOGIN" => $user["LOGIN"]
			));
			if (intval($user["PERSONAL_PHOTO"]) > 0)
			{
				$imageFile = \CFile::GetFileArray($user["PERSONAL_PHOTO"]);
				if ($imageFile !== false)
				{
					$file = CFile::ResizeImageGet(
						$imageFile,
						array("width" => "30", "height" => "30"),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
					$users[$user["ID"]]["PHOTO"] = $file["src"];
				}
			}
		}

		return $users;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		$this->includeComponentLang('class.php');

		if (!$this->checkModules())
			return false;
		
		$this->init();
		
		if (!$this->checkAccess())
			return false;

		$this->arResult["GRID_ID"] = $this->gridId;
		$this->arResult["FILTER"] = $this->getFilterDefinition();

		$sorting = $this->gridOptions->GetSorting(array("sort" => array("ID" => "DESC")));
		$navParams = $this->gridOptions->GetNavParams();
		$pageSize = $navParams['nPageSize'];

		$nav = new \Bitrix\Main\UI\PageNavigation("page");
		$nav->allowAllRecords(false)
			->setPageSize($pageSize)
			->initFromUri();

		$cursor = \Bitrix\ImOpenLines\Model\SessionTable::getList(array(
			'order' => array('ID'=>'DESC'),
			'filter' => $this->getFilter($this->arResult["FILTER"]),
			'select' => array('*'),
			"count_total" => true,
			'limit' => ($this->excelMode ? 0 : $nav->getLimit()),
			'offset' => ($this->excelMode ? 0 : $nav->getOffset())
		));

		$this->arResult["ROWS_COUNT"] = $cursor->getCount();
		$nav->setRecordCount($cursor->getCount());

		$this->arResult["SORT"] = $sorting["sort"];
		$this->arResult["SORT_VARS"] = $sorting["vars"];
		$this->arResult["NAV_OBJECT"] = $nav;

		$userId = array();
		$this->arResult["ELEMENTS_ROWS"] = array();
		while($data = $cursor->fetch())
		{
			if ($data["USER_ID"] > 0)
			{
				$userId[$data["USER_ID"]] = $data["USER_ID"];
			}
			if ($data["OPERATOR_ID"] > 0)
			{
				$userId[$data["OPERATOR_ID"]] = $data["OPERATOR_ID"];
			}
			$this->arResult["ELEMENTS_ROWS"][] = array("data" => $data, "columns" => array());
		}
		
		
		$this->showHistory = \Bitrix\ImOpenlines\Security\Helper::getAllowedUserIds(
			\Bitrix\ImOpenlines\Security\Helper::getCurrentUserId(),
			$this->userPermissions->getPermission(\Bitrix\ImOpenlines\Security\Permissions::ENTITY_HISTORY, \Bitrix\ImOpenlines\Security\Permissions::ACTION_VIEW)
		);
		$configManager = new \Bitrix\ImOpenLines\Config();

		$arUsers = $this->getUserData($userId);
		$arSources = \Bitrix\ImConnector\Connector::getListConnector();
		foreach($this->arResult["ELEMENTS_ROWS"] as $key => $row)
		{
			$newRow = $this->arResult["ELEMENTS_ROWS"][$key]["columns"];

			$newRow["USER_NAME"] = $this->getUserHtml($row["data"]["USER_ID"], $arUsers);
			$newRow["OPERATOR_NAME"] = $this->getUserHtml($row["data"]["OPERATOR_ID"], $arUsers);
			$newRow["MODE_NAME"] = $row["data"]["MODE"] == 'input'? Loc::getMessage('OL_COMPONENT_TABLE_INPUT'): Loc::getMessage('OL_COMPONENT_TABLE_OUTPUT');

			$newRow["SOURCE_TEXT"] = $arSources[$row["data"]["SOURCE"]];
			if ($row["data"]["CLOSED"] == 'Y')
			{
				$newRow["STATUS"] = Loc::getMessage('OL_COMPONENT_TABLE_STATUS_CLOSED');
			}
			else if ($row["data"]["WAIT_ACTION"] == 'Y')
			{
				$newRow["STATUS"] = Loc::getMessage('OL_COMPONENT_TABLE_STATUS_WAIT_ACTION');
			}
			else if ($row["data"]["WAIT_ANSWER"] == 'Y')
			{
				$newRow["STATUS"] = Loc::getMessage('OL_COMPONENT_TABLE_STATUS_WAIT_ANSWER');
			}
			else
			{
				$newRow["STATUS"] = Loc::getMessage('OL_COMPONENT_TABLE_STATUS_IN_PROCESS');
			}

			$newRow["PAUSE_TEXT"] = $row["data"]["PAUSE"] == 'Y'? Loc::getMessage('OL_COMPONENT_TABLE_YES'): Loc::getMessage('OL_COMPONENT_TABLE_NO');


			$crmLink = self::getCrmLink($row["data"]);
			$newRow["CRM_TEXT"] = $crmLink? '<a href="'.$crmLink.'" target="_blank">'.Loc::getMessage('OL_COMPONENT_TABLE_YES').'</a>': Loc::getMessage('OL_COMPONENT_TABLE_NO');

			if ($this->excelMode)
			{
				$newRow["CRM_TEXT"] = $row["data"]["CRM"] == 'Y'? Loc::getMessage('OL_COMPONENT_TABLE_YES'): Loc::getMessage('OL_COMPONENT_TABLE_NO');
				$newRow["CRM_LINK"] = $crmLink;
			}

			$newRow["WORKTIME_TEXT"] = $row["data"]["WORKTIME"] == 'Y'? Loc::getMessage('OL_COMPONENT_TABLE_YES'): Loc::getMessage('OL_COMPONENT_TABLE_NO');

			if (!$this->excelMode)
			{
				if (!is_array($this->showHistory) || in_array($row["data"]["OPERATOR_ID"], $this->showHistory))
				{
					$newRow["ACTION"] = '<nobr><a href="#history" onclick="BXIM.openHistory(\'imol|'.$row["data"]["ID"].'\'); return false;">'.Loc::getMessage('OL_COMPONENT_TABLE_ACTION_HISTORY').'</a></nobr> ';
				}
				if ($configManager->canJoin($row["data"]["CONFIG_ID"]))
				{
					$newRow["ACTION"] .= '<nobr><a href="#startSession" onclick="BXIM.openMessenger(\'imol|'.$row["data"]["USER_CODE"].'\'); return false;">'.Loc::getMessage('OL_COMPONENT_TABLE_ACTION_START').'</a></nobr>';
				}
			}

			$newRow["TIME_ANSWER_WO_BOT"] = $row["data"]["TIME_ANSWER"]? $row["data"]["TIME_ANSWER"]-$row["data"]["TIME_BOT"]: 0;
			$newRow["TIME_CLOSE_WO_BOT"] = $row["data"]["TIME_CLOSE"]? $row["data"]["TIME_CLOSE"]-$row["data"]["TIME_BOT"]: 0;
			$newRow["TIME_CLOSE"] = $row["data"]["TIME_CLOSE"] != $row["data"]["TIME_BOT"]? $row["data"]["TIME_CLOSE"]: 0;
			$newRow["TIME_DIALOG_WO_BOT"] = $row["data"]["TIME_DIALOG"]? $row["data"]["TIME_DIALOG"]-$row["data"]["TIME_BOT"]: 0;
			$newRow["TIME_FIRST_ANSWER"] = $row["data"]["TIME_FIRST_ANSWER"]? $row["data"]["TIME_FIRST_ANSWER"]-$row["data"]["TIME_BOT"]: 0;

			if (!$this->excelMode)
			{
				$newRow["DATE_OPERATOR"] = $row["data"]["DATE_OPERATOR"]? $row["data"]["DATE_OPERATOR"]: '-';
				$newRow["DATE_OPERATOR_ANSWER"] = $row["data"]["DATE_OPERATOR_ANSWER"]? $row["data"]["DATE_OPERATOR_ANSWER"]: '-';
				$newRow["DATE_OPERATOR_CLOSE"] = $row["data"]["DATE_OPERATOR_CLOSE"]? $row["data"]["DATE_OPERATOR_CLOSE"]: '-';
				$newRow["DATE_CLOSE"] = $row["data"]["DATE_CLOSE"]? $row["data"]["DATE_CLOSE"]: '-';
				$newRow["DATE_LAST_MESSAGE"] = $row["data"]["DATE_LAST_MESSAGE"]? $row["data"]["DATE_LAST_MESSAGE"]: '-';
				$newRow["DATE_FIRST_ANSWER"] = $row["data"]["DATE_FIRST_ANSWER"]? $row["data"]["DATE_FIRST_ANSWER"]: '-';
				$newRow["TIME_ANSWER_WO_BOT"] = self::formatDuration($newRow["TIME_ANSWER_WO_BOT"]);
				$newRow["TIME_CLOSE_WO_BOT"] = self::formatDuration($newRow["TIME_CLOSE_WO_BOT"]);
				$newRow["TIME_ANSWER"] = self::formatDuration($row["data"]["TIME_ANSWER"]);
				$newRow["TIME_CLOSE"] = self::formatDuration($newRow["TIME_CLOSE"]);
				$newRow["TIME_BOT"] = self::formatDuration($row["data"]["TIME_BOT"]);
				$newRow["TIME_DIALOG_WO_BOT"] = self::formatDuration($newRow["TIME_DIALOG_WO_BOT"]);
				$newRow["TIME_FIRST_ANSWER"] = self::formatDuration($newRow["TIME_FIRST_ANSWER"]);
				$newRow["TIME_DIALOG"] = self::formatDuration($row["data"]["TIME_DIALOG"]);
			}

			$this->arResult["ELEMENTS_ROWS"][$key]["columns"] = $newRow;
		}

		$this->arResult["HEADERS"] = array(
			array("id"=>"ID", "name"=>'ID', "default"=>false, "editable"=>false),
			array("id"=>"MODE_NAME", "name"=>GetMessage("OL_STATS_HEADER_MODE_NAME"), "default"=>true, "editable"=>false),
			array("id"=>"STATUS", "name"=>GetMessage("OL_STATS_HEADER_STATUS"), "default"=>true, "editable"=>false),
			array("id"=>"SOURCE_TEXT", "name"=>GetMessage("OL_STATS_HEADER_SOURCE_TEXT"), "default"=>true, "editable"=>false),
			array("id"=>"USER_NAME", "name"=>GetMessage("OL_STATS_HEADER_USER_NAME"), "default"=>true, "editable"=>false),
			array("id"=>"CRM_TEXT", "name"=>GetMessage("OL_STATS_HEADER_CRM_TEXT"), "default"=>true, "editable"=>false),
		);
		if ($this->excelMode)
		{
			$this->arResult["HEADERS"] = array_merge($this->arResult["HEADERS"], Array(
				array("id"=>"CRM_LINK", "name"=>GetMessage("OL_STATS_HEADER_CRM_LINK"), "default"=>true, "editable"=>false)
			));
		}
		$this->arResult["HEADERS"] = array_merge($this->arResult["HEADERS"], Array(
			array("id"=>"PAUSE_TEXT", "name"=>GetMessage("OL_STATS_HEADER_PAUSE_TEXT"), "default"=>false, "editable"=>false),
			array("id"=>"WORKTIME_TEXT", "name"=>GetMessage("OL_STATS_HEADER_WORKTIME_TEXT"), "default"=>false, "editable"=>false),
			array("id"=>"MESSAGE_COUNT", "name"=>GetMessage("OL_STATS_HEADER_MESSAGE_COUNT"), "default"=>true, "editable"=>false),
			array("id"=>"OPERATOR_NAME", "name"=>GetMessage("OL_STATS_HEADER_OPERATOR_NAME"), "default"=>true, "editable"=>false),
			array("id"=>"DATE_CREATE", "name"=>GetMessage("OL_STATS_HEADER_DATE_CREATE"), "default"=>true, "editable"=>false),
			array("id"=>"DATE_OPERATOR", "name"=>GetMessage("OL_STATS_HEADER_DATE_OPERATOR"), "default"=>false, "editable"=>false),
			array("id"=>"DATE_FIRST_ANSWER", "name"=>GetMessage("OL_STATS_HEADER_DATE_FIRST_ANSWER"), "default"=>true, "editable"=>false),
			array("id"=>"DATE_OPERATOR_ANSWER", "name"=>GetMessage("OL_STATS_HEADER_DATE_OPERATOR_ANSWER"), "default"=>false, "editable"=>false),
			array("id"=>"DATE_LAST_MESSAGE", "name"=>GetMessage("OL_STATS_HEADER_DATE_LAST_MESSAGE"), "default"=>true, "editable"=>false),
			array("id"=>"DATE_OPERATOR_CLOSE", "name"=>GetMessage("OL_STATS_HEADER_DATE_OPERATOR_CLOSE"), "default"=>true, "editable"=>false),
			array("id"=>"DATE_CLOSE", "name"=>GetMessage("OL_STATS_HEADER_DATE_CLOSE"), "default"=>false, "editable"=>false),
			array("id"=>"DATE_MODIFY", "name"=>GetMessage("OL_STATS_HEADER_DATE_MODIFY"), "default"=>false, "editable"=>false),
			array("id"=>"TIME_FIRST_ANSWER", "name"=>GetMessage("OL_STATS_HEADER_TIME_FIRST_ANSWER"), "default"=>true, "editable"=>false),
			array("id"=>"TIME_ANSWER_WO_BOT", "name"=>GetMessage("OL_STATS_HEADER_TIME_ANSWER_WO_BOT"), "default"=>false, "editable"=>false),
			array("id"=>"TIME_CLOSE_WO_BOT", "name"=>GetMessage("OL_STATS_HEADER_TIME_CLOSE_WO_BOT"), "default"=>false, "editable"=>false),
		//	array("id"=>"TIME_ANSWER", "name"=>GetMessage("OL_STATS_HEADER_TIME_ANSWER"), "default"=>false, "editable"=>false),
		//	array("id"=>"TIME_CLOSE", "name"=>GetMessage("OL_STATS_HEADER_TIME_CLOSE"), "default"=>false, "editable"=>false),
			array("id"=>"TIME_DIALOG_WO_BOT", "name"=>GetMessage("OL_STATS_HEADER_TIME_DIALOG_WO_BOT"), "default"=>true, "editable"=>false),
		//	array("id"=>"TIME_DIALOG", "name"=>GetMessage("OL_STATS_HEADER_TIME_DIALOG"), "default"=>false, "editable"=>false),
			array("id"=>"TIME_BOT", "name"=>GetMessage("OL_STATS_HEADER_TIME_BOT"), "default"=>true, "editable"=>false),
			array("id"=>"ACTION", "name"=>GetMessage("OL_STATS_HEADER_ACTION"), "default"=>true, "editable"=>false),
		));

		if($this->excelMode)
		{
			$now = new \Bitrix\Main\Type\Date();
			$filename = 'openlines_details_'.$now->format('Y_m_d').'.xls';
			$APPLICATION->RestartBuffer();
			header("Content-Type: application/vnd.ms-excel");
			header("Content-Disposition: filename=".$filename);
			$this->includeComponentTemplate('excel');
			CMain::FinalActions();
			die();
		}
		else
		{
			$this->includeComponentTemplate();
			return $this->arResult;
		}
	}
};