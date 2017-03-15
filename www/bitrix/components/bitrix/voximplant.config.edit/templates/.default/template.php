<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/voximplant.main/templates/.default/telephony.css");
/**
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */
$i = 0;
?>
<form action="<?=POST_FORM_ACTION_URI?>" method="POST" id="voximplantform">
<?=bitrix_sessid_post()?>
<input type="hidden" name="action" value="save" />
<div class="tel-set-main-wrap">
	<div class="tel-set-top-title"><?=htmlspecialcharsbx($arResult["ITEM"]["PHONE_NAME"])?></div>
	<div class="tel-set-inner-wrap">
		<div class="tel-set-cont-block">
			<?if(strlen($arResult["ERROR"])>0):?>
				<div class="tel-set-cont-error"><?=$arResult['ERROR']?></div>
			<?endif;?>
			<?if(!empty($arResult["SIP_CONFIG"])):?>
				<div class="tel-set-cont-title"><?=GetMessage("VI_CONFIG_SIP_TITLE")?></div>
				<div class="tel-set-sip-blocks">
					<div class="tel-set-sip-block">
						<div class="tel-set-sip-block-title">
							<b><?=GetMessage('VI_CONFIG_SIP_OUT_TITLE')?></b><br>
							<?=GetMessage('VI_CONFIG_SIP_OUT')?>
						</div>
						<table class="tel-set-sip-table" cellpadding="0" cellspacing="0">
							<tr>
								<td class="tel-set-sip-td-l"><?=GetMessage('VI_CONFIG_SIP_T_NUMBER')?></td>
								<td class="tel-set-sip-td-r"><input type="text" name="SIP[NUMBER]" value="<?=htmlspecialcharsbx($arResult['SIP_CONFIG']['NUMBER'])?>" class="tel-set-inp tel-set-inp-sip" /></td>
							</tr>
							<tr>
								<td class="tel-set-sip-td-l"><?=GetMessage('VI_CONFIG_SIP_T_SERVER')?></td>
								<td class="tel-set-sip-td-r"><input type="text" name="SIP[SERVER]" value="<?=htmlspecialcharsbx($arResult['SIP_CONFIG']['SERVER'])?>" class="tel-set-inp tel-set-inp-sip" /></td>
							</tr>
							<tr>
								<td class="tel-set-sip-td-l"><?=GetMessage('VI_CONFIG_SIP_T_LOGIN')?></td>
								<td class="tel-set-sip-td-r"><input type="text" name="SIP[LOGIN]" value="<?=htmlspecialcharsbx($arResult['SIP_CONFIG']['LOGIN'])?>" class="tel-set-inp tel-set-inp-sip" /></td>
							</tr>
							<tr>
								<td class="tel-set-sip-td-l"><?=GetMessage('VI_CONFIG_SIP_T_PASS')?></td>
								<td class="tel-set-sip-td-r"><input type="text" name="SIP[PASSWORD]" value="<?=htmlspecialcharsbx($arResult['SIP_CONFIG']['PASSWORD'])?>" class="tel-set-inp tel-set-inp-sip"/></td>
							</tr>
						</table>
					</div>
					<div class="tel-set-sip-block">
						<div class="tel-set-sip-block-title">
							<b><?=GetMessage('VI_CONFIG_SIP_IN_TITLE')?></b><br>
							<?=GetMessage('VI_CONFIG_SIP_IN')?>
						</div>
						<table class="tel-set-sip-table" cellpadding="0" cellspacing="0">
							<tr>
								<td class="tel-set-sip-td-l"><?=GetMessage('VI_CONFIG_SIP_T_INC_SERVER')?></td>
								<td class="tel-set-sip-td-r">
									<input type="text" class="tel-set-inp tel-set-inp-sip-inc" readonly value="<?=htmlspecialcharsbx($arResult['SIP_CONFIG']['INCOMING_SERVER'])?>"/>
								</td>
							</tr>
							<tr>
								<td class="tel-set-sip-td-l"><?=GetMessage('VI_CONFIG_SIP_T_INC_LOGIN')?></td>
								<td class="tel-set-sip-td-r">
									<input type="text" class="tel-set-inp tel-set-inp-sip-inc" readonly value="<?=htmlspecialcharsbx($arResult['SIP_CONFIG']['INCOMING_LOGIN'])?>"/>
								</td>
							</tr>
							<tr>
								<td class="tel-set-sip-td-l"><?=GetMessage('VI_CONFIG_SIP_T_INC_PASS')?></td>
								<td class="tel-set-sip-td-r">
									<input type="text" class="tel-set-inp tel-set-inp-sip-inc" readonly value="<?=htmlspecialcharsbx($arResult['SIP_CONFIG']['INCOMING_PASSWORD'])?>"/>
								</td>
							</tr>
						</table>
						<div class="tel-set-sip-block-notice">
							<?=GetMessage('VI_CONFIG_SIP_CONFIG_INFO', Array('#LINK_START#' => '<a href="'.(in_array(LANGUAGE_ID, Array("ru", "kz", "ua", "by"))? 'https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=48&LESSON_ID=3236': 'http://www.bitrixsoft.com/support/training/course/index.php?COURSE_ID=55&LESSON_ID=6635').'" target="_blank">', '#LINK_END#' => '</a>'));?>
						</div>
					</div>
				</div>
			<?endif;?>
			<div class="tel-set-cont-title"><?=GetMessage("VI_CONFIG_EDIT_CALLS_ROUTING")?></div>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					<input name="DIRECT_CODE" type="hidden" value="N" />
					<input id="id<?=(++$i)?>" name="DIRECT_CODE" <? if ($arResult["ITEM"]["DIRECT_CODE"] == "Y") { ?>checked<? } ?> type="checkbox" value="Y" class="tel-set-checkbox"/>
					<span class="tel-set-item-num-text"><?=$i?>.</span>
				</div>
				<div class="tel-set-item-cont-block">
					<label for="id<?=$i?>" class="tel-set-cont-item-title"><?=GetMessage("VI_CONFIG_EDIT_EXT_NUM_PROCESSING")?></label>
					<div class="tel-set-item-cont">
						<div class="tel-set-item-text"><?=GetMessage("VI_CONFIG_EDIT_EXT_NUM_PROCESSING_TIP")?></div>
						<div class="tel-set-item-select-block">
							<span class="tel-set-item-select-text"><?=GetMessage("VI_CONFIG_EDIT_EXT_NUM_PROCESSING_OMITTED_CALL")?></span>
							<select class="tel-set-item-select" name="DIRECT_CODE_RULE">
								<?foreach (array("1" => CVoxImplantIncoming::RULE_QUEUE, "2" => CVoxImplantIncoming::RULE_VOICEMAIL, "3" => CVoxImplantIncoming::RULE_PSTN) as $ii => $k):?>
									<option value="<?=$k?>"<?=($k == $arResult["ITEM"]["DIRECT_CODE_RULE"] ? " selected" : "")?>><?=GetMessage("VI_CONFIG_EDIT_DEALING_WITH_OMITTED_CALL_".$ii)?></option>
								<?endforeach;?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					<input name="CRM" type="hidden" value="N" />
					<input type="checkbox" id="id<?=(++$i)?>" name="CRM" <? if ($arResult["ITEM"]["CRM"] == "Y") { ?>checked<? } ?> value="Y" class="tel-set-checkbox"/>
					<span class="tel-set-item-num-text"><?=$i?>.</span>
				</div>
				<div class="tel-set-item-cont-block">
					<label for="id<?=$i?>" class="tel-set-cont-item-title">
						<?=GetMessage("VI_CONFIG_EDIT_CRM_CHECKING")?>
						<span class="tel-set-cont-item-title-description">
							<?=GetMessage("VI_CONFIG_EDIT_CRM_CHECKING_TIP")?>
						</span>
					</label>
					<div class="tel-set-item-cont">
						<div class="tel-set-item-select-block">
							<div class="tel-set-item-select-text"><?=GetMessage("VI_CONFIG_EDIT_CRM_CHECKING_OMITTED_CALL")?></div>
							<select class="tel-set-item-select" name="CRM_RULE">
								<?foreach (array("1" => CVoxImplantIncoming::RULE_QUEUE, "2" => CVoxImplantIncoming::RULE_VOICEMAIL, "3" => CVoxImplantIncoming::RULE_PSTN) as $ii => $k):?>
									<option value="<?=$k?>"<?=($k == $arResult["ITEM"]["CRM_RULE"] ? " selected" : "")?>><?=GetMessage("VI_CONFIG_EDIT_DEALING_WITH_OMITTED_CALL_".$ii)?></option>
								<?endforeach;?>
							</select>
						</div>
						<div class="tel-set-item-select-block">
							<div class="tel-set-item-select-text"><?=GetMessage("VI_CONFIG_EDIT_CRM_CREATE")?></div>
							<select class="tel-set-item-select" name="CRM_CREATE">
								<?foreach (array("1" => CVoxImplantConfig::CRM_CREATE_NONE, "2" => CVoxImplantConfig::CRM_CREATE_LEAD) as $ii => $k):?>
									<option value="<?=$k?>"<?=($k == $arResult["ITEM"]["CRM_CREATE"] ? " selected" : "")?>><?=GetMessage("VI_CONFIG_EDIT_CRM_CREATE_".$ii)?></option>
								<?endforeach;?>
							</select>
						</div>
					</div>
				</div>
			</div>
<?
if (IsModuleInstalled("socialnetwork"))
{
	CUtil::InitJSCore(array("socnetlogdest"));
?>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					<span class="tel-set-item-num-text"><?=(++$i)?>.</span>
				</div>
				<div class="tel-set-item-cont-block">
					<label class="tel-set-cont-item-title"><?=GetMessage("VI_CONFIG_EDIT_QUEUE")?></label>
					<div class="tel-set-item-cont">
						<div class="tel-set-item-text">
							<?=GetMessage("VI_CONFIG_EDIT_QUEUE_TIP")?>
						</div>
						<div class="tel-set-destination-container" id="users_for_queue"></div>
						<div class="tel-set-item-select-block">
							<div class="tel-set-item-select-text"><?=GetMessage("VI_CONFIG_EDIT_QUEUE_AMOUNT_OF_BEEPS_BEFORE_REDIRECT")?></div>
							<select class="tel-set-item-select" name="QUEUE_TIME">
								<?foreach (array("3", "4", "5") as $k):?>
									<option value="<?=$k?>"<?=($k == $arResult["ITEM"]["QUEUE_TIME"] ? " selected" : "")?>><?=GetMessage("VI_CONFIG_EDIT_QUEUE_AMOUNT_OF_BEEPS_BEFORE_REDIRECT_".$k)?></option>
								<?endforeach;?>
							</select>
						</div>
					</div>
				</div>
			</div>
<script type="text/javascript">
	BX.ready(function(){
		BX.message({LM_ADD1 : '<?=GetMessageJS("LM_ADD1")?>', LM_ADD2 : '<?=GetMessageJS("LM_ADD2")?>'});
		BX.VoxImplantConfigEdit.initDestination(BX('users_for_queue'), 'QUEUE', <?=CUtil::PhpToJSObject($arParams["DESTINATION"])?>);
	});
</script>
<?
}
?>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					<span class="tel-set-item-num-text"><?=++$i?>.</span>
				</div>
				<div class="tel-set-item-cont-block">
					<label class="tel-set-cont-item-title" for="id<?=$i?>"><?=GetMessage("VI_CONFIG_EDIT_NO_ANSWER")?></label>
					<div class="tel-set-item-cont">
						<div class="tel-set-item-text">
							<?=GetMessage("VI_CONFIG_EDIT_NO_ANSWER_TIP")?>
						</div>
						<div class="tel-set-item-select-block">
							<div class="tel-set-item-select-text"><?=GetMessage("VI_CONFIG_EDIT_NO_ANSWER_ACTION")?></div>
							<select class="tel-set-item-select" name="NO_ANSWER_RULE">
								<?foreach (array("2" => CVoxImplantIncoming::RULE_VOICEMAIL, "3" => CVoxImplantIncoming::RULE_PSTN, "4" => CVoxImplantIncoming::RULE_HUNGUP) as $ii => $k):?>
									<option value="<?=$k?>"<?=($k == $arResult["ITEM"]["NO_ANSWER_RULE"] ? " selected" : "")?>><?=GetMessage("VI_CONFIG_EDIT_NO_ANSWER_ACTION_".$ii)?></option>
								<?endforeach;?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					<input name="RECORDING" type="hidden" value="N" />
					<input type="checkbox" id="id<?=(++$i)?>" name="RECORDING" <?if($arResult["ITEM"]["RECORDING"] == "Y") { ?>checked<? }?> value="Y" class="tel-set-checkbox"/>
					<span class="tel-set-item-num-text"><?=$i?>.</span>
				</div>
				<div class="tel-set-item-cont-block">
					<label for="id<?=$i?>" class="tel-set-cont-item-title">
						<?=GetMessage("VI_CONFIG_EDIT_RECORD")?>
						<span class="tel-set-cont-item-title-description"><?=GetMessage("VI_CONFIG_EDIT_RECORD_TIP")?></span>
					</label>
					<div class="tel-set-item-cont">
						<div class="tel-set-item-alert">
							<?=GetMessage("VI_CONFIG_EDIT_RECORD_TIP2")?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tel-set-cont-block">
			<div class="tel-set-cont-title"><?=GetMessage("VI_CONFIG_EDIT_TUNES")?></div>
			<div class="tel-set-item">
				<div class="tel-set-item-num"></div>
				<div class="tel-set-item-cont-block">
					<div class="tel-set-item-cont">
						<div class="tel-set-item-select-block">
							<div class="tel-set-item-select-text"><?=GetMessage("VI_CONFIG_EDIT_TUNES_LANGUAGE")?></div>
							<select class="tel-set-item-select" name="MELODY_LANG">
								<?foreach (array("RU", "EN", "DE") as $k):?>
									<option value="<?=$k?>"<?=($k == $arResult["ITEM"]["MELODY_LANG"] ? " selected" : "")?>><?=GetMessage("VI_CONFIG_EDIT_TUNES_LANGUAGE_".$k)?></option>
								<?endforeach;?>
							</select>
						</div>
					</div>
				</div>
			</div>
<?
$defaultM = $arResult["DEFAULT_MELODIES"];
$melodies = array(
	array(
		"TITLE" => GetMessage("VI_CONFIG_EDIT_WELCOMING_TUNE"),
		"TIP" => GetMessage("VI_CONFIG_EDIT_WELCOMING_TUNE_TIP"),
		"MELODY" => (array_key_exists("~MELODY_WELCOME", $arResult["ITEM"]) ? $arResult["ITEM"]["~MELODY_WELCOME"]["SRC"] : str_replace("#LANG_ID#", $arResult["ITEM"]["MELODY_LANG"], $defaultM["MELODY_WELCOME"])),
		"MELODY_ID" => $arResult["ITEM"]["MELODY_WELCOME"],
		"DEFAULT_MELODY" => $defaultM["MELODY_WELCOME"],
		"CHECKBOX" => "MELODY_WELCOME_ENABLE",
		"INPUT_NAME" => "MELODY_WELCOME"
	),
	array(
		"TITLE" => GetMessage("VI_CONFIG_EDIT_WAITING_TUNE"),
		"TIP" => GetMessage("VI_CONFIG_EDIT_WAITING_TUNE_TIP"),
		"MELODY" => (array_key_exists("~MELODY_WAIT", $arResult["ITEM"]) ? $arResult["ITEM"]["~MELODY_WAIT"]["SRC"] : str_replace("#LANG_ID#", $arResult["ITEM"]["MELODY_LANG"], $defaultM["MELODY_WAIT"])),
		"MELODY_ID" => $arResult["ITEM"]["MELODY_WAIT"],
		"DEFAULT_MELODY" => $defaultM["MELODY_WAIT"],
		"INPUT_NAME" => "MELODY_WAIT"
	),
	array(
		"TITLE" => GetMessage("VI_CONFIG_EDIT_HOLDING_TUNE"),
		"TIP" => GetMessage("VI_CONFIG_EDIT_HOLDING_TUNE_TIP"),
		"MELODY" => (array_key_exists("~MELODY_HOLD", $arResult["ITEM"]) ? $arResult["ITEM"]["~MELODY_HOLD"]["SRC"] : str_replace("#LANG_ID#", $arResult["ITEM"]["MELODY_LANG"], $defaultM["MELODY_HOLD"])),
		"MELODY_ID" => $arResult["ITEM"]["MELODY_HOLD"],
		"DEFAULT_MELODY" => $defaultM["MELODY_HOLD"],
		"INPUT_NAME" => "MELODY_HOLD"
	),
	array(
		"TITLE" => GetMessage("VI_CONFIG_EDIT_AUTO_ANSWERING_TUNE"),
		"TIP" => GetMessage("VI_CONFIG_EDIT_AUTO_ANSWERING_TUNE_TIP"),
		"MELODY" => (array_key_exists("~MELODY_VOICEMAIL", $arResult["ITEM"]) ? $arResult["ITEM"]["~MELODY_VOICEMAIL"]["SRC"] : str_replace("#LANG_ID#", $arResult["ITEM"]["MELODY_LANG"], $defaultM["MELODY_VOICEMAIL"])),
		"MELODY_ID" => $arResult["ITEM"]["MELODY_VOICEMAIL"],
		"DEFAULT_MELODY" => $defaultM["MELODY_VOICEMAIL"],
		"INPUT_NAME" => "MELODY_VOICEMAIL"
	)
);

foreach ($melodies as $i => $melody)
{
	$id = 'voximplant'.$i;
	CHTTP::URN2URI($APPLICATION->GetCurPageParam("mfi_mode=down&fileID=".$fileID."&cid=".$cid."&".bitrix_sessid_get(), array("mfi_mode", "fileID", "cid")))
?>
			<div class="tel-set-item tel-set-item-border">
				<div class="tel-set-item-num">
					<?if (array_key_exists("CHECKBOX", $melody)):?>
					<input name="<?=$melody["CHECKBOX"]?>" type="hidden" value="N" />
					<input type="checkbox" id="checkbox<?=$melody["CHECKBOX"]?>" name="<?=$melody["CHECKBOX"]?>" class="tel-set-checkbox" value="Y" <? if ($arResult["ITEM"][$melody["CHECKBOX"]] == "Y"): ?> checked <? endif; ?> style="margin-top: 3px;" />
					<?endif;?>
				</div>
				<div class="tel-set-item-cont-block">
					<label class="tel-set-cont-item-title" for="checkbox<?=$melody["CHECKBOX"]?>"><?=$melody["TITLE"]?></label>
					<div class="tel-set-item-cont">
						<div class="tel-set-item-text"><?=$melody["TIP"]?></div>
						<div class="tel-set-melody-block">
							<span class="tel-set-player-wrap">
								<?

								$APPLICATION->IncludeComponent(
									"bitrix:player",
									"",
									Array(
										"PLAYER_ID" => $id."player",
										"PLAYER_TYPE" => "flv",
										"USE_PLAYLIST" => "N",
										"PATH" => $melody["MELODY"],
										"PROVIDER" => "sound",
										"STREAMER" => "",
										"WIDTH" => "217",
										"HEIGHT" => "24",
										"PREVIEW" => "",
										"FILE_TITLE" => "",
										"FILE_DURATION" => "",
										"FILE_AUTHOR" => "",
										"FILE_DATE" => "",
										"FILE_DESCRIPTION" => "",
										"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins",
										"SKIN" => "",
										"CONTROLBAR" => "bottom",
										"WMODE" => "opaque",
										"LOGO" => "",
										"LOGO_LINK" => "",
										"LOGO_POSITION" => "none",
										"PLUGINS" => array(),
										"ADDITIONAL_FLASHVARS" => "",
										"AUTOSTART" => "N",
										"REPEAT" => "none",
										"VOLUME" => "90",
										"MUTE" => "N",
										"ADVANCED_MODE_SETTINGS" => "Y",
										"BUFFER_LENGTH" => "2",
										"ALLOW_SWF" => "N"
									),
								null,
								Array(
									'HIDE_ICONS' => 'Y'
								)
								);?>
							</span>
							<span style="display: none;"><?
							$APPLICATION->IncludeComponent('bitrix:main.file.input', '.default',
								array(
									'INPUT_CAPTION' => GetMessage("VI_CONFIG_EDIT_DOWNLOAD_TUNE"),
									'INPUT_NAME' => $melody["INPUT_NAME"],
									'INPUT_NAME_UNSAVED' => $melody["INPUT_NAME"]."_TMP",
									'INPUT_VALUE' => array($melody["MELODY_ID"]),
									'MAX_FILE_SIZE' => 2097152,
									'MODULE_ID' => 'voximplant',
									'CONTROL_ID' => $id,
									'MULTIPLE' => 'N',
									'ALLOW_UPLOAD' => 'F',
									'ALLOW_UPLOAD_EXT' => 'mp3'
								),
								$this->component,
								array("HIDE_ICONS" => true)
							);?></span><?

							?>
							<span class="tel-set-melody-item">
								<span class="tel-set-item-melody-link tel-set-item-melody-link-active" id="<?=$id?>span"><?=GetMessage("VI_CONFIG_EDIT_DOWNLOAD_TUNE")?></span>
								<span class="tel-set-melody-description" id="<?=$id?>notice" ><?=GetMessage("VI_CONFIG_EDIT_DOWNLOAD_TUNE_TIP")?></span>
							</span>
							<span class="tel-set-melody-item" id="<?=$id?>default" <?if ($melody["MELODY_ID"] <= 0) { ?> style="display:none;" <? } ?>>
								<span class="tel-set-item-melody-link"><?=GetMessage("VI_CONFIG_EDIT_SET_DEFAULT_TUNE")?></span>
							</span>
						</div>
					</div>
				</div>
			</div>
<script type="application/javascript">
BX.ready(function(){
		BX.bind(BX("voximplantform").elements["MELODY_LANG"], "change", function() {
			if (!(!!BX("voximplantform").elements["<?=$melody["INPUT_NAME"]?>"] && !!BX("voximplantform").elements["<?=$melody["INPUT_NAME"]?>"]))
				window.jwplayer("<?=$id?>player_div").load( [ { file : "<?=CUtil::JSEscape($melody["DEFAULT_MELODY"])?>".replace("#LANG_ID#", this.value) } ] );
		});
		BX('<?=$id?>span').appendChild(BX('file_input_<?=$id?>'));
		BX.bind(BX('<?=$id?>default'), "click", function() {
			window["FILE_INPUT_<?=$id?>"]._deleteFile(BX('voximplantform').elements["<?=$melody["INPUT_NAME"]?>"]);
		});
		BX.addCustomEvent(window["FILE_INPUT_<?=$id?>"], 'onSubmit', function() {
			BX('<?=$id?>span').appendChild(
				BX.create('SPAN', {attrs: {id : '<?=$id?>waiter'}, props : {className : "webform-field-upload-list"}, html : '<i></i>'})
			);
		});
		BX.addCustomEvent(window["FILE_INPUT_<?=$id?>"], 'onFileUploaderChange', function() {
			window["FILE_INPUT_<?=$id?>"].INPUT.disabled = false;
		});
		BX.addCustomEvent(window["FILE_INPUT_<?=$id?>"], 'onDeleteFile', function(id) {
			BX.hide(BX('<?=$id?>default'));
			BX('<?=$id?>notice').innerHTML = BX.message("VI_CONFIG_EDIT_DOWNLOAD_TUNE_TIP");
			window.jwplayer("<?=$id?>player_div").load( [ { file : "<?=CUtil::JSEscape($melody["DEFAULT_MELODY"])?>".replace("#LANG_ID#", BX("voximplantform").elements["MELODY_LANG"].value) } ] );
			window["FILE_INPUT_<?=$id?>"].INPUT.disabled = false;
		});

		BX.addCustomEvent(window["FILE_INPUT_<?=$id?>"], 'onDone', function(files, id, err) {
			BX.remove(BX('<?=$id?>waiter'));
			if (!!files && files.length > 0)
			{
				var n = BX('<?=$id?>notice');
				if (err === false && !!files[0])
				{
					if (id != 'init')
					{
						n.innerHTML = BX.message('VI_CONFIG_EDIT_UPLOAD_SUCCESS');
						if (!!window["jwplayer"])
						{
							window.jwplayer("<?=$id?>player_div").load( [ { file : files[0]["fileURL"] } ] );
						}
						BX('<?=$id?>default').style.display = '';
					}
				}
				else if (!!files[0] && files[0]["error"])
				{
					n.innerHTML = files[0]["error"];
				}
			}
		});
});
</script><?
}
?>
<script type="application/javascript">
	BX.message({
		VI_CONFIG_EDIT_DOWNLOAD_TUNE_TIP : '<?=GetMessageJS('VI_CONFIG_EDIT_DOWNLOAD_TUNE_TIP')?>',
		VI_CONFIG_EDIT_UPLOAD_SUCCESS : '<?=GetMessageJS("VI_CONFIG_EDIT_UPLOAD_SUCCESS")?>'});
</script>
			<div class="tel-set-item tel-set-item-border">
				<div class="tel-set-item-cont-block">
					<div class="tel-set-item-alert">
						<?=GetMessage("VI_CONFIG_EDIT_TUNES_TIP")?>
					</div>
				</div>
			</div>
		</div>
		<div class="tel-set-footer-btn">
			<span class="webform-button webform-button-accept" onclick="BX.submit(BX('voximplantform'))">
				<span class="webform-button-left"></span><span class="webform-button-text"><?=GetMessage("VI_CONFIG_EDIT_SAVE")?></span><span class="webform-button-right"></span>
			</span>
			<a href="<?=CVoxImplantMain::GetPublicFolder()?>settings.php?MODE=<?=$arResult["ITEM"]["PORTAL_MODE"]?>" class="webform-small-button-link"><?=GetMessage("VI_CONFIG_EDIT_BACK")?></a>
		</div>
	</div>
</div>
</form>