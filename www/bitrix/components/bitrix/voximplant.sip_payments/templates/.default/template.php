<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/voximplant.main/templates/.default/telephony.css");
?>
<div class="tel-history-block tel-regular-block">
	<div class="tel-history-title"><?=GetMessage('VI_SIP_TITLE')?></div>

	<?if($arResult['DATE_END']):?>
		<div class="tel-sip-paid-before">
			<?=GetMessage('VI_SIP_PAID_BEFORE', Array('#DATE#' => '<b>'.$arResult['DATE_END'].'</b>'))?>
			<?if (!empty($arResult['LINK_TO_BUY'])):?>
			<a href="<?=$arResult['LINK_TO_BUY']?>" class="tel-balance-blue-btn tel-sip-button">
				<span class="tel-balance-update-btn-text"><?=GetMessage('VI_SIP_BUTTON')?></span>
			</a>
			<?endif;?>
		</div>
		<div class="tel-sip-notice"><?=GetMessage('VI_SIP_PAID_NOTICE')?></div>
		<div class="tel-history-more">
			<a href="<?=CVoxImplantMain::GetPublicFolder()?>settings.php?MODE=SIP" class="tel-history-more-link"><?=GetMessage('VI_SIP_CONFIG')?></a>
		</div>
	<?else:?>
		<div class="tel-sip-paid-before">
			<?=GetMessage('VI_SIP_PAID_FREE', Array('#COUNT#' => '<b>'.$arResult['FREE'].'</b>'))?>
			<a href="<?=CVoxImplantMain::GetPublicFolder()?>settings.php?MODE=SIP" class="tel-balance-blue-btn tel-sip-button">
				<span class="tel-balance-update-btn-text"><?=GetMessage('VI_SIP_BUTTON_BUY')?></span>
			</a>
		</div>
		<div class="tel-sip-notice"><?=GetMessage('VI_SIP_PAID_NOTICE_2')?></div>
	<?endif;?>
</div>