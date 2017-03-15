<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/about/media.php");
$APPLICATION->SetTitle(GetMessage("ABOUT_TITLE"));
?>

<?$APPLICATION->IncludeComponent("bitrix:iblock.tv", "round", Array(
	"IBLOCK_TYPE"	=>	"services",
	"IBLOCK_ID"	=>	"83",
	"PATH_TO_FILE"	=>	"401",
	"DURATION"	=>	"402",
	"SECTION_ID"	=>	"60",
	"ELEMENT_ID"	=>	"2685",
	"WIDTH"	=>	"400",
	"HEIGHT"	=>	"300",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"36000000"
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>