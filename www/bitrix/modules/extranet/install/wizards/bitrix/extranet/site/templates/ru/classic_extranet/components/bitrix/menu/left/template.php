<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult))
	return;

?>

<div class="left-menu">
	<?foreach($arResult as $itemIndex => $arItem):?>
		<?if ($arItem["PERMISSION"] > "D" && $arItem["DEPTH_LEVEL"] == 1):?>
			<?if ($itemIndex > 0):?>
				<div class="separator"></div>
			<?endif?><div<?if($arItem["IS_PARENT"]):?> class="directory"<?endif?>>
			<a href="<?=$arItem["LINK"]?>"<?if($arItem["SELECTED"]):?> class="selected"<?endif?>><?=$arItem["TEXT"]?></a></div>
		<?endif?>
	<?endforeach?>
</div>