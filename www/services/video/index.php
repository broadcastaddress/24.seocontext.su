<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Видеоконференции");
?>
<?
$APPLICATION->IncludeComponent("bitrix:video", ".default", array(
	"IBLOCK_TYPE" => "events",
	"IBLOCK_ID" => "0",
	"PATH_TO_VIDEO_CONF" => "/services/video/detail.php?ID=#ID#",
	"SET_TITLE" => "Y",
	),
	false
);
?>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
