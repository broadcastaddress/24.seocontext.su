<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

function __GetDirTitle()
{
	CMain::InitPathVars($site, $path = false);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);

	$path = $GLOBALS["APPLICATION"]->GetCurDir();

	$arChain = Array();
	$i = -1;

	$sSectionName = "";
	while(true)
	{
		$path = rtrim($path, "/");

		$chain_file_name = $DOC_ROOT.$path."/.section.php";
		$section_template_init = false;
		if(file_exists($chain_file_name))
		{
			include($chain_file_name);
			if(strlen($sSectionName)>0)
				break;
		}

		if(strlen($path)<=0)
			break;

		$pos = bxstrrpos($path, "/");
		if($pos===false)
			break;

		$path = substr($path, 0, $pos+1);
	}

	return $sSectionName;
}
if (empty($arResult))
	return;

?>

<div class="left-menu">
	<div class="left-menu-header"><?=htmlspecialchars(__GetDirTitle());?></div>
	<?foreach($arResult as $itemIndex => $arItem):?>
		<?if ($arItem["PERMISSION"] > "D" && $arItem["DEPTH_LEVEL"] == 1):?>
			<div<?if($arItem["IS_PARENT"]):?> class="directory"<?endif?>>
			<a href="<?=$arItem["LINK"]?>"<?if($arItem["SELECTED"]):?> class="selected"<?endif?>><?=$arItem["TEXT"]?></a></div>
			<?if (count($arResult) != $itemIndex+1):?><div class="separator"></div><?endif?>
		<?endif?>
	<?endforeach?>
</div>