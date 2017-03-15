<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadString('<script type="text/javascript" src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/im_mobile.js").'"></script>');

$arMessage = Array();
foreach ($arResult['message'] as $data)
{
	if ($arMessage[$data['senderId']]['date'] < $data['date'])
	{
		$data['counter'] = isset($data['counter'])? $data['counter']++: 1;
		$arMessage[$data['senderId']] = $data;
	}
	else
	{
		$arMessage[$data['senderId']]['counter']++;
	}
}
?>
<div class="notif-block-wrap">
<?foreach ($arMessage as $data):?>
<?
	$arFormat = Array(
		"tommorow" => "tommorow, H:i",
		"today" => "today, H:i",
		"yesterday" => "yesterday, H:i",
		"" => 'H:i, d F Y'
	);
	$data['date'] = FormatDate($arFormat, $data['date']);	
?>	
	<div class="notif-block" onclick="app.openNewPage('/mobile/im/dialog.php?id=<?=$data['senderId']?>')">
		<div class="notif-counter"><?=$data['counter']?></div>
		<!-- notif-avatar-<?=$arResult['users'][$data['senderId']]['status']?>-->
		<div class="avatar notif-avatar"><div class="im-avatar" style="background-image:url('<?=$arResult['users'][$data['senderId']]['avatar']?>'); background-size:cover;"></div></div>
		<div class="notif-cont">
			<div class="notif-title"><?=$arResult['users'][$data['senderId']]['name']?></div>
			<div class="notif-text"><?=(strlen($data['text'])>100? substr($data['text'], 0, 100).'...': $data['text'])?></div>
			<div class="notif-time"><?=$data['date']?></div>
		</div>
	</div>
<?endforeach;?>	
</div>