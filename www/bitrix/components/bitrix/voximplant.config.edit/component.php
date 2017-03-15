<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (!CModule::IncludeModule('voximplant'))
	return;

/**
 * @var $arParams array
 * @var $arResult array
 * @var $this CBitrixComponent
 * @var $APPLICATION CMain
 */
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["ID"] = intval($arParams["ID"] > 0 ? $arParams["ID"] : $_REQUEST["ID"]);
/********************************************************************
				/Input params
********************************************************************/
$arResult = array(
	"ITEM" => Bitrix\Voximplant\ConfigTable::getById($arParams["ID"])->fetch(),
	"QUEUE" => array(),
	"SIP_CONFIG" => array(),
	"~QUEUE" => array()
);
if (!!$arResult["ITEM"])
{
	$db_res = Bitrix\Voximplant\QueueTable::getList(Array(
		'filter' => Array('=CONFIG_ID' => $arResult["ITEM"]["ID"]),
	));
	while ($res = $db_res->fetch())
	{
		$arResult["QUEUE"][$res["USER_ID"]] = $res;
		$arResult["~QUEUE"][$res["ID"]] = $res["USER_ID"];
	}

	if ($arResult["ITEM"]["PORTAL_MODE"] == CVoxImplantConfig::MODE_SIP)
	{
		$viSip = new CVoxImplantSip();
		$arResult["SIP_CONFIG"] = $viSip->Get($arParams["ID"]);
		$arResult["SIP_CONFIG"]['NUMBER'] = $arResult['ITEM']['PHONE_NAME'];
	}
}

if (empty($arResult["ITEM"]))
	return;

if ($_REQUEST["action"] == "save" && check_bitrix_sessid())
{
	$post = \Bitrix\Main\Context::getCurrent()->getRequest()->getPostList()->toArray();

	$skipSaving = false;
	$arFieldsSip = Array();

	if (isset($post['SIP']))
	{
		$viSip = new CVoxImplantSip();
		$result = $viSip->Update($arParams["ID"], Array(
			'NUMBER' => $post['SIP']['NUMBER'],
			'SERVER' => $post['SIP']['SERVER'],
			'LOGIN' => $post['SIP']['LOGIN'],
			'PASSWORD' => $post['SIP']['PASSWORD'],
		));

		$skipSaving = !$result;

		$arFieldsSip = Array(
			'NUMBER' => $post['SIP']['NUMBER'],
			'SERVER' => $post['SIP']['SERVER'],
			'LOGIN' => $post['SIP']['LOGIN'],
			'PASSWORD' => $post['SIP']['PASSWORD'],
		);
	}

	$arFields = Array(
		"DIRECT_CODE" => $post["DIRECT_CODE"],
		"DIRECT_CODE_RULE" => $post["DIRECT_CODE_RULE"],
		"CRM" => $post["CRM"],
		"CRM_RULE" => $post["CRM_RULE"],
		"CRM_CREATE" => $post["CRM_CREATE"],
		"QUEUE_TIME" => $post["QUEUE_TIME"],
		"NO_ANSWER_RULE" => $post["NO_ANSWER_RULE"],
		"RECORDING" => $post["RECORDING"],
		"MELODY_LANG" => $post["MELODY_LANG"],
		"MELODY_WELCOME" => $post["MELODY_WELCOME"],
		"MELODY_WELCOME_ENABLE" => $post["MELODY_WELCOME_ENABLE"],
		"MELODY_WAIT" => $post["MELODY_WAIT"],
		"MELODY_HOLD" => $post["MELODY_HOLD"],
		"MELODY_VOICEMAIL" => $post["MELODY_VOICEMAIL"]
	);

	$post["QUEUE"] = (is_array($post["QUEUE"]) ? $post["QUEUE"] : array());
	$post["QUEUE"]["U"] = (is_array($post["QUEUE"]["U"]) ? $post["QUEUE"]["U"] : array());
	$queue = array();
	if (is_array($post["QUEUE"]) && is_array($post["QUEUE"]["U"]))
	{
		foreach($post["QUEUE"] as $type => $k)
		{
			$queue[$type] = str_replace($type, "", $k);
		}
	}

	if ($skipSaving)
	{
		$error = $viSip->GetError()->msg;
	}
	else
	{
		if (($res = Bitrix\Voximplant\ConfigTable::update($arParams["ID"], $arFields)) && $res->isSuccess())
		{
			// TODO We should work with other socialnetwork entities
			$toDrop = array_diff($arResult["~QUEUE"], $queue["U"]);
			$toAdd = array_diff($queue["U"], array_keys($arResult["QUEUE"]));
			foreach ($toDrop as $primary => $id)
				Bitrix\Voximplant\QueueTable::delete($primary);
			foreach ($toAdd as $k)
				Bitrix\Voximplant\QueueTable::add(array(
					"SEARCH_ID" => $arResult["ITEM"]["SEARCH_ID"],
					"CONFIG_ID" => $arParams["ID"],
					"USER_ID" => $k,
					"STATUS" => "OFFLINE"
				));
			LocalRedirect(CVoxImplantMain::GetPublicFolder().'settings.php?MODE='.$arResult["ITEM"]["PORTAL_MODE"]);
		}
		$error = $res->getErrorMessages();
	}

	$arResult = array(
		"ERROR" => $error,
		"ITEM" => array_merge($arResult["ITEM"], $arFields),
		"QUEUE" => array_flip($queue["U"]),
		"SIP_CONFIG" => array_merge($arResult["SIP_CONFIG"], $arFieldsSip)
	);
}
foreach (array("MELODY_WELCOME", "MELODY_WAIT", "MELODY_HOLD", "MELODY_VOICEMAIL") as $id)
{
	if ($arResult["ITEM"][$id] > 0)
	{
		$res = CFile::GetFileArray($arResult["ITEM"][$id]);
		if ($res)
		{
			$arResult["ITEM"]["~".$id] = $res;
		}
		else
		{
			$arResult["ITEM"][$id] = 0;
		}
	}
}
$arResult["ITEM"]["MELODY_LANG"] = (empty($arResult["ITEM"]["MELODY_LANG"]) ? strtoupper(LANGUAGE_ID) : $arResult["ITEM"]["MELODY_LANG"]);
$arResult["ITEM"]["MELODY_LANG"] = (in_array($arResult["ITEM"]["MELODY_LANG"], array("RU", "EN", "DE")) ? $arResult["ITEM"]["MELODY_LANG"] : "EN");
$arResult["DEFAULT_MELODIES"] = CVoxImplantConfig::GetDefaultMelodies(false);

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;
?>