<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Create Group");
?> <?$APPLICATION->IncludeComponent("bitrix:extranet.group_create", ".default", array(
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "#SITE_DIR#workgroups/create/",
	"PATH_TO_GROUP" => "#SITE_DIR#workgroups/group/#group_id#/",
	"PATH_TO_USER" => "#SITE_DIR#contacts/personal/user/#user_id#/",
	"SEF_URL_TEMPLATES" => array(
		"index" => "index.php",
		"invite" => "#group_id#/invite/",
	)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>