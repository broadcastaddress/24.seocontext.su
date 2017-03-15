<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$dialog = $arResult['dialog'];

$data = $dialog->getRuntimeData();
$map = $dialog->getMap();
$activityData = $data['ACTIVITY_DATA'];

$properties = isset($activityData['PROPERTIES']) && is_array($activityData['PROPERTIES']) ? $activityData['PROPERTIES'] : array();
$currentValues = $dialog->getCurrentValues();

foreach ($properties as $name => $property):
	$name = $map[$name]['FieldName'];
	$required = CBPHelper::getBool($property['REQUIRED']);
	$value = !CBPHelper::isEmptyValue($currentValues[$name]) ? $currentValues[$name] : $property['DEFAULT'];

	$title = \Bitrix\Bizproc\RestActivityTable::getLocalization($property['NAME'], LANGUAGE_ID)

	?>
	<div class="crm-automation-popup-settings">
		<span class="crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete"><?=htmlspecialcharsbx($title)?>: </span>
		<?
		switch ($property['TYPE'])
		{
			case 'bool':
				?>
				<select class="crm-automation-popup-settings-dropdown" name="<?=htmlspecialcharsbx($name)?>">
					<option value=""><?=GetMessage('CRM_AUTOMATION_NOT_SELECTED')?></option>
					<option value="Y" <?=($value == 'Y') ? 'selected':''?>><?=GetMessage('MAIN_YES')?></option>
					<option value="N" <?=($value == 'N') ? 'selected':''?>><?=GetMessage('MAIN_NO')?></option>
				</select>
				<?
				break;
			case 'date':
				?>
				<input name="<?=htmlspecialcharsbx($name)?>" type="text" class="crm-automation-popup-input"
					value="<?=htmlspecialcharsbx($value)?>"
					placeholder="<?=htmlspecialcharsbx(!empty($property['DESCRIPTION']) ? $property['DESCRIPTION'] : '')?>"
					data-role="inline-selector-target"
					onclick="BX.calendar({node:this, field:this, bTime: false})"
				>
				<?
				break;
			case 'datetime':
				?>
				<input name="<?=htmlspecialcharsbx($name)?>" type="text" class="crm-automation-popup-input"
					   value="<?=htmlspecialcharsbx($value)?>"
					   placeholder="<?=htmlspecialcharsbx(!empty($property['DESCRIPTION']) ? $property['DESCRIPTION'] : '')?>"
					   data-role="inline-selector-target"
					   onclick="BX.calendar({node:this, field:this, bTime: true})"
				>
				<?
				break;
			case 'double':
			case 'int':
				?>
				<input name="<?=htmlspecialcharsbx($name)?>" type="text" class="crm-automation-popup-input"
					value="<?=htmlspecialcharsbx($value)?>"
					placeholder="<?=htmlspecialcharsbx(!empty($property['DESCRIPTION']) ? $property['DESCRIPTION'] : '')?>"
					data-role="inline-selector-target"
				>
				<?
				break;
			case 'select':
				$options = isset($property['OPTIONS']) && is_array($property['OPTIONS'])
					? $property['OPTIONS'] : array();
				?>
				<select class="crm-automation-popup-settings-dropdown" name="<?=htmlspecialcharsbx($name)?>">
					<option value=""><?=GetMessage('CRM_AUTOMATION_NOT_SELECTED')?></option>
					<?
					foreach ($options as $k => $v)
					{
						echo '<option value="'.htmlspecialcharsbx($k).'"'.($k == $value ? ' selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
					}
					?>
				</select>
				<?
				break;
			case 'string':
				?>
				<input name="<?=htmlspecialcharsbx($name)?>" type="text" class="crm-automation-popup-input"
					value="<?=htmlspecialcharsbx($value)?>"
					placeholder="<?=htmlspecialcharsbx(!empty($property['DESCRIPTION']) ? $property['DESCRIPTION'] : '')?>"
					data-role="inline-selector-target"
				>
				<?
				break;
			case 'text':
				?>
				<textarea name="<?=htmlspecialcharsbx($name)?>"
					class="crm-automation-popup-textarea"
					placeholder="<?=htmlspecialcharsbx(!empty($property['DESCRIPTION']) ? $property['DESCRIPTION'] : '')?>"
					data-role="inline-selector-target"
				><?=htmlspecialcharsbx($value)?></textarea>
				<?
				break;
			case 'user':
				?>
				<div data-role="user-selector" data-config="<?=htmlspecialcharsbx(
						\Bitrix\Main\Web\Json::encode(array(
							'valueInputName' => $name,
							'selected' => \Bitrix\Crm\Automation\Helper::prepareUserSelectorEntities(
								$dialog->getDocumentType(),
								$currentValues[$name]
							),
							'multiple' => isset($property['MULTIPLE']) && $property['MULTIPLE'] === 'Y',
							'required' => $required,
						))
				)?>"></div>
				<?
				break;
		}
		?>
	</div>
	<?
endforeach;
