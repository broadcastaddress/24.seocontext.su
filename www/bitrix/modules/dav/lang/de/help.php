<?
$MESS["DAV_HELP_NAME"] = "Modul DAV";
$MESS["DAV_HELP_TEXT"] = "Das Modul DAV ermöglicht eine Synchronisierung der Kalender und Kontakte zwischen dem Portal und einer beliebigen Software oder Hardware, die die Protokolle CalDAV und/oder CardDAV unterstützen, beispielsweise iPhone und iPad. Software-Unterstützung bieten Mozilla Sunbird, eM Client und einige andere Software-Anwendungen.<br><br>
<ul>
<li><b><a href=\"#caldav\">Verbindung via CalDav</a></b>
<ul>
<li><a href=\"#caldavipad\">Mit iPhone verbinden</a></li>
<li><a href=\"#carddavsunbird\">Mit Mozilla Sunbird verbinden</a></li>
</ul>
</li>
<li><b><a href=\"#carddav\">Verbindung via CardDav</a></b></li>
</ul>

<br><br>

<h3><a name=\"caldav\"></a>Verbindung via CalDav</h3>

<h4><a name=\"caldavipad\"></a>Mit iPhone verbinden</h4>

Um die Unterstützung für CalDAV in Ihrem Apple-Gerät einzustellen:
<ol>
<li>Tippen Sie auf <b>Einstellungen</b> und wählen Sie <b>Mail, Kontakte, Kalender</b>.</li>
<li>Tippen Sie auf <b>Account hinzufügen</b>.</li>
<li>Wählen Sie <b>Andere Accounts</b> &gt; <b>Fügen Sie einen CalDAV-Account hinzu</b>.</li>
<li>Geben Sie diese Website-Adresse als Server (#SERVER#) an. Benutzen Sie Ihren Login und Ihr Passwort.</li>
<li>Benutzen Sie die Basisautorisierung.</li>
<li>Um die Portnummer anzugeben, speichern Sie den Account und öffnen Sie ihn für die Bearbeitung wieder.</li>
</ol>

Ihre Kalender werden in der App \"Kalender\" erscheinen.<br>
Um die Verbindung mit Kalendern anderer Nutzer herzustellen, benutzen Sie Links:<br>
<i>#SERVER#/bitrix/groupdav.php/Website_ID/Nutzer_Name/calendar/</i><br>
und<br>
<i>#SERVER#/bitrix/groupdav.php/Website_ID/Gruppe_ID/calendar/</i><br>

<br><br>

<h4><a name=\"carddavsunbird\"></a>Mit Mozilla Sunbird verbinden</h4>

Um Mozilla Sunbird für die Benutzung mit CalDAV zu konfigurieren:
<ol>
<li>Starten Sie Sunbird und wählen Sie <b>Datei &gt; Neuer Kalender</b>.</li>
<li>Wählen Sie <b>Im Netzwerk</b> und tippen Sie auf <b>Weiter</b>.</li>
<li>Wählen Sie das <b>CalDAV</b> Format.</li>
<li>Im Feld <b>Adresse</b> geben Sie:<br>
<i>#SERVER#/bitrix/groupdav.php/Website_ID/Nutzer_Name/calendar/calendar_ID/</i><br>
oder<br>
<i>#SERVER#/bitrix/groupdav.php/Website_ID/Gruppe_ID/calendar/calendar_ID/</i> ein <br>
und tippen Sie auf <b>Weiter</b>.</li>
<li>Geben Sie Ihrem Kalender einen Namen und wählen Sie dafür eine Farbe aus.</li>
<li>Geben Sie Ihren Nutzernamen und Ihr Passwort ein.</li>
</ol>

<br><br>

<h3><a name=\"carddav\"></a>Verbindung via CardDav</h3>

Um die Unterstützung für CardDAV in Ihrem Apple-Gerät einzustellen:
<ol>
<li>Tippen Sie auf <b>Einstellungen</b> und wählen Sie <b>Mail, Kontakte, Kalender</b>.</li>
<li>Tippen Sie auf <b>Account hinzufügen</b>.</li>
<li>Wählen Sie <b>Andere Accounts</b> &gt; <b>Fügen Sie einen CardDAV-Account hinzu</b>.</li>
<li>Geben Sie diese Website-Adresse als Server (#SERVER#) an. Benutzen Sie Ihren Login und Ihr Passwort.</li>
<li>Benutzen Sie die Basisautorisierung.</li>
<li> Um die Portnummer anzugeben, speichern Sie den Account und öffnen Sie ihn für die Bearbeitung wieder.</li>
</ol>

Ihre Kalender werden in der App \"Kontakte\" erscheinen.<br>
";
?>