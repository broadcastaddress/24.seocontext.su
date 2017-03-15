<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>

<input type="hidden" value="<?=htmlspecialcharsbx($arResult['ENTITY']['VK_PROFILE'])?>" data-role="crm-card-vk-profile">

<?if ($arResult['SIMPLE']):?>
	<div class="crm-activity-visit-main">
		<div class="crm-activity-visit-user">
			<? if(isset($arResult['ENTITY']['PHOTO_URL'])): ?>
				<div class="crm-activity-visit-user-item" style="background-image: url(<?=$arResult['ENTITY']['PHOTO_URL']?>)"></div>
			<?else: ?>
				<div class="crm-activity-visit-user-item"></div>
			<?endif?>
		</div><!--crm-activity-visit-user-->
		<div class="crm-activity-visit-user-name">
			<div class="crm-activity-visit-user-name-item"><?=htmlspecialcharsbx($arResult['ENTITY']['FORMATTED_NAME'])?></div>
			<?if($arResult['ENTITY']['POST']):?>
				<div class="crm-activity-visit-user-name-desc"><?=htmlspecialcharsbx($arResult['ENTITY']['POST'])?></div>
			<?endif?>
			<?if($arResult['ENTITY']['COMPANY_TITLE']):?>
				<div class="crm-activity-visit-user-name-desc"><?=htmlspecialcharsbx($arResult['ENTITY']['COMPANY_TITLE'])?></div>
			<?endif?>
		</div><!--crm-activity-visit-user-name-->
		<div class="crm-activity-visit-user-settings">
			<div class="crm-activity-visit-user-settings-item"></div>
		</div><!--crm-activity-visit-user-settings-->
	</div><!--crm-activity-visit-main-->
<?else:?>
	<div class="crm-activity-visit-detail">
		<div class="crm-activity-visit-detail-header">
			<div class="crm-activity-visit-detail-header-user">
				<div class="crm-activity-visit-detail-header-user-image">
					<? if(isset($arResult['ENTITY']['PHOTO_URL'])): ?>
						<div class="crm-activity-visit-detail-header-user-image-item" style="background-image: url(<?=$arResult['ENTITY']['PHOTO_URL']?>)"></div>
					<? else: ?>
						<div class="crm-activity-visit-detail-header-user-image-item"></div>
					<? endif ?>
				</div>
				<div class="crm-activity-visit-detail-header-user-info">
					<div class="crm-activity-visit-detail-header-user-name"><?=htmlspecialcharsbx($arResult['ENTITY']['FORMATTED_NAME'])?></div>
					<?if($arResult['ENTITY']['POST']):?>
						<div class="crm-activity-visit-detail-header-user-item"><?=htmlspecialcharsbx($arResult['ENTITY']['POST'])?></div>
					<?endif?>
					<?if($arResult['ENTITY']['COMPANY_TITLE']):?>
						<div class="crm-activity-visit-detail-header-user-item"><?=htmlspecialcharsbx($arResult['ENTITY']['COMPANY_TITLE'])?></div>
					<?endif?>
				</div>
			</div><!--crm-activity-visit-detail-header-user-->
			<div class="crm-activity-visit-detail-header-user-status">
				<div class="crm-activity-visit-detail-header-user-status-item"><?/*=GetMessage('CRM_CARD_CONSTANT_CLIENT')*/?></div>
			</div><!--crm-activity-visit-detail-header-user-status-->
		</div><!--crm-activity-visit-detail-header-->
		<div class="crm-activity-visit-detail-info">
			<div class="crm-activity-visit-detail-info-inner">
				<div class="crm-activity-visit-detail-info-content">
					<? if(count($arResult['ENTITY']['ACTIVITIES']) > 0): ?>
						<div class="crm-activity-visit-detail-info-title crm-activity-visit-title-main">
							<div class="crm-activity-visit-detail-info-title-item">
								<a href="<?=htmlspecialcharsbx($arResult['ENTITY']['ACTIVITY_LIST_URL'])?>" target="_blank">
									<?=GetMessage('CRM_CARD_ACTIVITIES')?>
								</a>
							</div>
						</div>
						<? foreach ($arResult['ENTITY']['ACTIVITIES'] as $activity): ?>
							<div class="crm-activity-visit-detail-info-block">
								<div class="crm-activity-visit-detail-info-name">
									<div class="crm-activity-visit-detail-info-name-item">
										<a href="<?=htmlspecialcharsbx($activity['SHOW_URL'])?>" target="_blank">
											<?=htmlspecialcharsbx($activity['SUBJECT'])?>
										</a>
									</div>
								</div>
								<div class="crm-activity-visit-detail-info-desc">
									<div class="crm-activity-visit-detail-info-desc-item"><?=htmlspecialcharsbx($activity['DEADLINE'])?></div>
								</div>
							</div><!--crm-activity-visit-detail-info-block-->
						<? endforeach ?>
					<? endif ?>

					<? if(count($arResult['ENTITY']['DEALS']) > 0): ?>
						<div class="crm-activity-visit-detail-info-title crm-activity-visit-title-main">
							<div class="crm-activity-visit-detail-info-title-item">
								<a href="<?=htmlspecialcharsbx($arResult['ENTITY']['DEAL_LIST_URL'])?>" target="_blank"><?=GetMessage('CRM_CARD_DEALS')?></a>
							</div>
						</div>
						<? foreach ($arResult['ENTITY']['DEALS'] as $deal): ?>
							<div class="crm-activity-visit-detail-info-main-inner">
								<div class="crm-activity-visit-detail-info-main-content">
									<div class="crm-activity-visit-detail-info-block">
										<div class="crm-activity-visit-detail-info-name">
											<div class="crm-activity-visit-detail-info-name-item">
												<a href="<?=htmlspecialcharsbx($deal['SHOW_URL'])?>" target="_blank">
													<?=htmlspecialcharsbx($deal['TITLE'])?>
												</a>
											</div>
										</div>
										<div class="crm-activity-visit-detail-info-desc">
											<div class="crm-activity-visit-detail-info-desc-item"><?=htmlspecialcharsbx($deal['FORMATTED_OPPORTUNITY'])?></div>
										</div>
									</div>
								</div><!--crm-activity-visit-detail-info-main-content-->
								<div class="crm-activity-visit-detail-info-main-status">
									<?= CCrmViewHelper::RenderDealStageControl(
										array(
											'ENTITY_ID' => $deal['ID'],
											'CURRENT_ID' => $deal['STAGE_ID'],
											'CATEGORY_ID' => $deal['CATEGORY_ID'],
											'READ_ONLY' => true
										)) ?>
								</div><!--crm-activity-visit-detail-info-main-status-->
							</div><!--crm-activity-visit-detail-info-main-inner-->
						<? endforeach ?>
					<? endif ?>

					<? if(count($arResult['ENTITY']['INVOICES']) > 0): ?>
						<div class="crm-activity-visit-detail-info-title crm-activity-visit-title-main">
							<div class="crm-activity-visit-detail-info-title-item">
								<a href="<?=htmlspecialcharsbx($arResult['ENTITY']['INVOICE_LIST_URL'])?>" target="_blank">
									<?=GetMessage('CRM_CARD_INVOICES')?>
								</a>
							</div>
						</div>
						<? foreach ($arResult['ENTITY']['INVOICES'] as $invoice): ?>
							<div class="crm-activity-visit-detail-info-main-inner">
								<div class="crm-activity-visit-detail-info-main-content">
									<div class="crm-activity-visit-detail-info-block">
										<div class="crm-activity-visit-detail-info-name">
											<div class="crm-activity-visit-detail-info-name-item">
												<a href="<?=htmlspecialcharsbx($invoice['SHOW_URL'])?>" target="_blank">
													<?=htmlspecialcharsbx($invoice['ORDER_TOPIC']).' '.GetMessage('CRM_CARD_INVOICE_DATE_FROM').' '.htmlspecialcharsbx($invoice['DATE_BILL'])?>
												</a>
											</div>
										</div>
										<div class="crm-activity-visit-detail-info-desc">
											<div class="crm-activity-visit-detail-info-desc-item"><?=htmlspecialcharsbx($invoice['PRICE_FORMATTED'])?></div>
										</div>
									</div>
								</div><!--crm-activity-visit-detail-info-main-content-->
								<div class="crm-activity-visit-detail-info-main-status">
									<?= CCrmViewHelper::RenderInvoiceStatusControl(
										array(
											'ENTITY_ID' => $invoice['ID'],
											'CURRENT_ID' => $invoice['STATUS_ID'],
											'READ_ONLY' => true
										)) ?>
								</div><!--crm-activity-visit-detail-info-main-status-->
							</div><!--crm-activity-visit-detail-info-main-inner-->
						<? endforeach ?>
					<? endif ?>
				</div><!--crm-activity-visit-detail-info-content-->
			</div><!--crm-activity-visit-detail-info-inner-->
		</div><!--crm-activity-visit-detail-info-->
	</div><!--crm-activity-visit-detail-->
<?endif?>
