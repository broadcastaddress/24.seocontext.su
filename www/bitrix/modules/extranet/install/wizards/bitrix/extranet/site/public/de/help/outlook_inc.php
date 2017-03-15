<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule("intranet");
CModule::IncludeModule("iblock");
?>
<table style="margin-top:7px;">
<tr><td><li><a href="javascript:<?echo CIntranetUtils::GetStsSyncURL(array('LINK_URL' => SITE_DIR.'contacts/'), 'contacts')?>">Verbindung zu Kontakten herstellen</a></td></tr>
<tr><td><li><a href="javascript:<?echo CIntranetUtils::GetStsSyncURL(array('LINK_URL' => SITE_DIR.'contacts/'), 'contacts', true)?>">Verbindung zu Mitarbeitern herstellen</a></td></tr>
<tr><td><li><a href="javascript:<?echo CIntranetUtils::GetStsSyncURL(array('LINK_URL' => '/'.$USER->GetID().'/'), 'tasks')?>">Verbindung zu Meine(n) Aufgaben herstellen</a></td></tr>
<?

if(COption::GetOptionInt("intranet", 'iblock_calendar', 0)>0):
	$dbRs = CIBlockSection::GetList(Array(), Array("IBLOCK_ID"=>COption::GetOptionInt("intranet", 'iblock_calendar', 0), 'CREATED_BY'=>$USER->GetID()));
	if($arRs = $dbRs->Fetch()):
		$dbRs2 = CIBlockSection::GetList(Array(), Array('SECTION_ID'=>$arRs["ID"]));
		while($arRs2 = $dbRs2->GetNext()):
		?>
		<tr><td><li><a href="javascript:<?echo CIntranetUtils::GetStsSyncURL(array('ID' => $arRs2["ID"], 'LINK_URL' => SITE_DIR.'personal/user/'.$USER->GetID().'/calendar/'), 'calendar')?>">Verbindung zu <?=$arRs2["NAME"]?></a></td></tr>
		<?endwhile?>
	<?endif?>
<?endif?>
</table>
