<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Abonnement");
?>

<p>Sie können alle News per E-Mail bekommen. Wählen Sie bitte die Nachrichtenkategorien aus, und klicken Sie auf <i>Abonnieren</i>.</p>

<?$APPLICATION->IncludeComponent(
	"bitrix:subscribe.simple",
	"",
	Array(
		"AJAX_MODE" => "N", 
		"SHOW_HIDDEN" => "N", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
		"SET_TITLE" => "N", 
		"AJAX_OPTION_SHADOW" => "Y", 
		"AJAX_OPTION_JUMP" => "N", 
		"AJAX_OPTION_STYLE" => "Y", 
		"AJAX_OPTION_HISTORY" => "N" 
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>