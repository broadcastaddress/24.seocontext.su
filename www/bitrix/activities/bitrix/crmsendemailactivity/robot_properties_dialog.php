<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
$messageText = $map['MessageText'];
$subject = $map['Subject'];
?>
<div class="crm-automation-popup-settings">
	<input name="<?=htmlspecialcharsbx($subject['FieldName'])?>" type="text" class="crm-automation-popup-input"
		value="<?=htmlspecialcharsbx($dialog->getCurrentValue($subject['FieldName']))?>"
		placeholder="<?=htmlspecialcharsbx($subject['Name'])?>"
		data-role="inline-selector-target"
	>
</div>
<div class="crm-automation-popup-settings">
	<textarea name="<?=htmlspecialcharsbx($messageText['FieldName'])?>"
			class="crm-automation-popup-textarea"
			placeholder="<?=htmlspecialcharsbx($messageText['Name'])?>"
			data-role="inline-selector-target"
	><?=htmlspecialcharsbx($dialog->getCurrentValue($messageText['FieldName'], ''))?></textarea>
</div>