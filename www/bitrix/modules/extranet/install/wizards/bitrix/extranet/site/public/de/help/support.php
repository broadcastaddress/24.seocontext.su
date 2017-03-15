<?
define("NEED_AUTH",true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Technischer Support");
?><p>Klicken Sie auf Meine Tickets, wenn Sie sich Ihre Anfragen ansehen wollen. Oder verwenden Sie das folgende Formular, wenn Sie eine neue Anfrage stellen wollen.</p>


<p><?$APPLICATION->IncludeComponent("bitrix:support.wizard", ".default", array(
	"IBLOCK_TYPE" => "services",
	"IBLOCK_ID" => "#MASTER_IBLOCK_ID#",
	"SITE_ID" => SITE_ID,
	"PROPERTY_FIELD_TYPE" => "type",
	"PROPERTY_FIELD_VALUES" => "values",
	"AJAX_MODE" => "Y",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
	"TICKETS_PER_PAGE" => "50",
	"MESSAGES_PER_PAGE" => "20",
	"SET_PAGE_TITLE" => "N",
	"TEMPLATE_TYPE" => "standard",
	"SHOW_RESULT" => "Y",
	"SHOW_COUPON_FIELD" => "N",
	"SECTIONS_TO_CATEGORIES" => "N",
	"AJAX_OPTION_ADDITIONAL" => "",
	"VARIABLE_ALIASES" => array(
		"ID" => "ID",
	)
	),
	false
);?></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>