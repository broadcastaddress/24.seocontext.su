<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$caption = $arResult['ENTITY_CAPTION'];
$total = isset($arResult['TOTAL']) ? $arResult['TOTAL'] : '0';
$data = isset($arResult['DATA']) ? $arResult['DATA'] : array();
?>
<div class="crm-counter">
	<div class="crm-counter-title">
		<span class="crm-counter-total"><?=$total <= 99 ? $total : '99+'?></span>
		<span><?=htmlspecialcharsbx($caption)?> - </span>
		<?
		for($i = 0, $l = count($data); $i < $l; $i++)
		{
			$item = $data[$i];
			$url = isset($item['URL']) ? $item['URL'] : '#';
			$text = isset($item['TEXT']) ? $item['TEXT'] : '';
			$class = (isset($item['IS_ALERT']) && $item['IS_ALERT'] === true)
				? 'crm-counter-overdue' : 'crm-counter-link';
			if($i == 0)
			{
				?><a href="<?=htmlspecialcharsbx($url)?>" class="<?=$class?>"><?=htmlspecialcharsbx($text)?></a><?
			}
			else
			{
				?><span>, </span><a href="<?=htmlspecialcharsbx($url)?>" class="<?=$class?>"><?=htmlspecialcharsbx($text)?></a><?
			}
		}
	?></div>
</div>