<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Integration mit Microsoft Outlook (in beide Richtungen)");
?>
<script type="text/javascript" src="/bitrix/templates/learning/js/imgshw.js"></script>
<p>Das Intranet Portal ermöglicht eine <b>Integration mit Microsoft Outlook in beide Richtungen. </b>
Neben Datenimport vom Portal in ein E-Mail-Programm ist nun auch ein beidseitiger Datenaustausch zwischen Portal und MS Outlook möglich: alle Änderungen, die Sie in persönlichen Kalendern, Mitarbeiterkontakten und Ihren eigenen Aufgaben in einem Programm vorgenommen haben, werden automatisch auch in das jeweils andere Programm übertragen und angezeigt.</p>

<table border="0" cellspacing="1" cellpadding="1" width="100%">
  <tbody>
    <tr><td valign="top"><img hspace="10" src="#SITE_DIR#images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#my_kalendar" >Synchronisierung persönlicher Kalender;</a>
        <br />
      <img hspace="10" src="#SITE_DIR#images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#company_calendar" >Synchronisierung der Unternehmenskalender;</a>
        <br />
      <img hspace="10" src="#SITE_DIR#images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#useful" >Synchronisierung persönlicher Kontakte</a>
        <br />
      <img hspace="10" src="#SITE_DIR#images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#kalendars" >Export mehrerer Kalender;</a>
        <br />
      <img hspace="10" src="#SITE_DIR#images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#kalendars" >Überlagerte Anzeige der Kalender mit MS Outlook;;</a>
        <br />
      <img hspace="10" src="#SITE_DIR#images/docs/cp/bullet-n.gif" width="12" height="15" /><a href="#useful1" >Synchronisierung persönlicher Aufgaben;</a>
      </td><td>
        <br />
      </td><td valign="top">
        <b>Die Verbindung zu Outlook jetzt herstellen!</b>
<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default", array(
	"AREA_FILE_SHOW" => "file",
	"PATH" => "/help/outlook_inc.php",
	"EDIT_TEMPLATE" => ""
	),
	false
);?>

        <br />
      </td></tr>
  </tbody>
</table>

<h2>Was ist so toll an einer beidseitigen Synchronisierung</h2>
<p>Bei einer solchen Synchronisierung werden Daten (beispielsweise persönliche Kontakte) auf dem Portal und in MS Outlook gleichzeitig aktualisiert. Probieren Sie das einfach mal selbst aus, und Sie werden sofort sehen, wieviel bequemer und einfacher Ihre Arbeit nun ist. Ein einfaches Beispiel: Angenommen Sie befinden sich in einem Flugzeug, und sind dabei verschiedene Treffen, Aufgaben und Aktivitäten für Ihr Unternehmen zu planen. Wenn Sie nun die Möglichkeit einer beidseitigen Synchronisierung der Outlook-Kalender mit den Portalkalendern benutzen, werden Ihre Pläne und Vorhaben sofort auch bspw. im Unternehmenskalender Ihres Intranets angezeigt. Sie machen die gewohnte Arbeit nur einmal und Ihnen bleibt ein erneutes Eingeben der Daten erspart. Deshalb ist die Synchronisierung eine tolle Funktion: Schnell, praktisch und effektiv.
<a name="useful"></a></p>

<h2>Nutzersynchronisierung</h2>

<p>Um jemandem Aufgaben stellen zu können oder auch von jemandem Aufgaben gestellt zu bekommen, sollte dieser Jemand Ihrem MS Outlook hinzugefügt werden.
 Das ist nicht schwierig: Im Portal rufen Sie die Seite <b>Mitarbeiter suchen</b> auf und klicken auf die Schaltfläche <b>Outlook</b> - der Rest erfolgt dann automatisch.
 Alles was Sie machen müssen, sind lediglich ein paar Klicks auf die Zustimmungen in den erscheinenden Popup-Fenstern. Falls das Unternehmen viele Mitarbeiter hat,
 müssen Sie vielleicht noch etwas warten, bis alle Daten hochgeladen werden, ansonsten war es das. Kinderleicht.</p>
<p>Die Mitarbeiterliste erscheint sofort in Ihren Outlook-Kontakten:</p>

<div align="center">
  <table style="BORDER-COLLAPSE: collapse" border="0" cellspacing="1" cellpadding="1">
    <tbody>
      <tr><td>
          <div align="center"><a href="javascript:ShowImg('#SITE_DIR#images/docs/big/outlook.png', 600, 413, 'Hinzugefügte Mitarbeiter')"><img title="Hinzugefügte Mitarbeiter" border="0" alt="Hinzugefügte Mitarbeiter" src="#SITE_DIR#images/docs/outlook_sm.png" width="300" height="207" /> </a>
            <br />
          </div>

          <div align="center"><i>Hinzugefügte Mitarbeiter</i>
            <br />
          </div>
        </td></tr>
    </tbody>
  </table>
</div>

<p>Wenn Sie jedoch Mitarbeiter aus den Outlook-Kontakten löschen, führt dies nicht zwangsläufig zu deren Löschung im Portal - Dies bleibt das Privileg des Portaladministrators.</p>

<h2><a name="my_kalendar"></a>Kalendersynchronisierung mit MS Outlook</h2>
<p>Sie können beliebige Portalkalender mit Kalendern in MS Outlook synchronisieren. Es können persönliche Kalender, Mitarbeiterkalender, Arbeitsgruppenkalender 
oder auch allgemeine Unternehmenskalender sein. Versuchen Sie jetzt eine solche Synchronisierung durchzuführen: Wechseln Sie dafür auf eine Seite mit einem Kalender, 
wählen Sie den Menüpunkt <b>Mit Outlook synchronisieren</b> aus und schon wird der Synchronisierungsprozess initiiert!</p>

<div align="center"><img src="#SITE_DIR#images/docs/outlook_1.png"  />
<br><i>Mit Outlook verbinden</i></div>

<p>Sie brauchen die Popup-Mitteilungen von Outlook nicht sonderlich zu beachten, da sie meistens rein informativ sind. Stimmen Sie diesen Mitteilungen einfach zu. 
Erscheint beispielsweise die warnende Frage <b>&quot;Soll dieser SharePoint (Kalender) mit Outlook verbunden werden?&quot;</b> 
auf dem Bildschirm, können Sie ruhig auf <b>Ja</b> klicken, denn die Integration entspricht den spezifischen Forderungen von Microsoft im vollen Umfang, 
so dass es keine Probleme entstehen werden!</p>

<div align="center"><img src="#SITE_DIR#images/docs/outlook_2_sm.png" />
  <br />
</div>

<p>Im Prinzip könnten Sie sich aber auch ein wenig Zeit lassen und statt <b>Ja</b> auf <b>Erweitert</b> klicken, und so Ihrem Kalender eine etwas ausführlichere Beschreibung hinzufügen.</p>

<div align="center">
  <table style="BORDER-COLLAPSE: collapse" border="0" cellspacing="1" cellpadding="1">
    <tbody>
      <tr><td>
          <div align="center"><a href="javascript:ShowImg('#SITE_DIR#images/docs/big/outlook_3.png', 509, 449, 'Kalenderbeschreibung')"><img title="Kalenderbeschreibung" border="0" alt="Kalenderbeschreibung" src="#SITE_DIR#images/docs/outlook_3_sm.png" /> </a>
            <br />
          </div>

          <div align="center"><i>Kalenderbeschreibung</i>
            <br />
          </div>
        </td></tr>
    </tbody>
  </table>
</div>

<p><a name="company_calendar"></a>Als Ergebnis erscheint in Ihrem Outlook ein neuer Kalender, in welchem <b>bereits alle Daten und Termine eingetragen sind</b>. 
Was bringt mir das? Ganz einfach: Angenommen Sie waren für eine längere Zeit auf Dienstreise und in der Zwischenzeit wurde eine Vielzahl verschiedener 
Veranstaltungen geplant. Wenn Sie jetzt die benötigten Unternehmens- oder Arbeitsgruppenkalender mit Ihrem Outlook synchronisieren, halten Sie mit dem 
Unternehmensleben Schritt und bleiben immer bestens informiert!</p>

<div align="center">
  <table style="BORDER-COLLAPSE: collapse" border="0" cellspacing="1" cellpadding="1">
    <tbody>
      <tr><td>
          <div align="center"><a href="javascript:ShowImg('#SITE_DIR#images/docs/big/outlook_4.png', 709, 575, 'Ein neuer Kalender wurde zu Outlook hinzugefügt')"><img title="Ein neuer Kalender wurde zu Outlook hinzugefügt" border="0" alt="Ein neuer Kalender wurde zu Outlook hinzugefügt" src="#SITE_DIR#images/docs/outlook_4_sm.png"  /> </a>
            <br />
          </div>

          <div align="center"><i>Ein neuer Kalender wurde zu Outlook hinzugefügt</i>
            <br />
          </div>
        </td></tr>
    </tbody>
  </table>
</div>

<p><a name="kalendars"></a>Jetzt können Sie alle weiteren benötigten Kalender vom Portal Schritt für Schritt mit Outlook verbinden. Lassen Sie sich diese Kalender
 überlagert anzeigen, und schon sieht Ihr Outlook -Kalender genauso wie der Portal-Kalender aus.</p>

<div align="center">
  <table style="BORDER-COLLAPSE: collapse" border="0" cellspacing="1" cellpadding="1">
    <tbody>
      <tr><td>
          <div align="center"><a href="javascript:ShowImg('#SITE_DIR#images/docs/big/outlook_5.png', 709, 575, 'Kalender im Outlook in einem Netz"><img title="Kalender im Outlook in einem Netz" border="0" alt="Kalender im Outlook in einem Netz" src="#SITE_DIR#images/docs/outlook_5_sm.png"  /> </a>
            <br />
          </div>

          <div align="center"><i>Kalender im Outlook in einem Netz</i>
            <br />
          </div>
        </td></tr>
    </tbody>
  </table>
</div>

<h2>Neues Ereignis hinzufügen</h2>
<p>Wie funktioniert die beidseitige Integration? <b>Wenn Sie Ihrem Outlook-Kalender einen neues Ereignis hinzufügen, erscheint dieses Ereignis automatisch in Ihrem 
Portal-Kalender</b>. Machen Sie dafür Folgendes:</p>

<ul>
  <li>Wählen Sie das Datum des Ereignisses im Kalender aus;</li>
  <li>Mit einem Doppelklick öffnen Sie das Fenster zum Erstellen eines neuen Ereignisses;</li>
  <li>Füllen Sie die Felder 'Thema', 'Anfang', 'Ende' und 'Beschreibung' aus;</li>
  <li>Klicken Sie auf 'Speichern und schließen'. </li>
</ul>

<p>Ein neues Ereignis wurde zum <b>Outlook-Kalender hinzugefügt</b>.</p>

<div align="center"><a href="javascript:ShowImg('#SITE_DIR#images/docs/big/outlook_6.png',650,500,'New Event')"> <img height="192" border="0" width="250" src="#SITE_DIR#images/docs/outlook_6_sm.png" title="Click to Enlarge" style="cursor: pointer;" /></a></p>
  </div>
<p class="a1"><a name="sinhr"></a>Sie brauchen sich um regelmäßige Synchronisierung von Outlook mit dem Portal-Kalender nicht zu kümmern. Sie erfolgt automatisch und entsprechend dem bei Outlook eingestellten Turnus zum Senden und Empfangen von E-Mails. <b>Der Synchronisierungsprozess</b> wird in der unteren rechten Ecke des Programms angezeigt.</p>

<p class="a1" align="center"><img src="#SITE_DIR#images/docs/outlook_8.png" /></p>
<p>Dasselbe Ereignis sieht im <b>Portal-Kalender</b> so aus:</p>

<div align="center"><a href="javascript:ShowImg('#SITE_DIR#images/docs/big/outlook_9.png',452,272,'New Event in the Portal Calendar')"> <img height="150" border="0" width="249" src="#SITE_DIR#images/docs/outlook_9_sm.png" title="Click to Enlarge" style="cursor: pointer;" /></a></div>

<p>Auf dieselbe Art und Weise können <b>Sie Ereignisse bearbeiten und/oder löschen</b>, und zwar sowohl von Outlook als auch von den Kalendern des Portals aus. 
Dabei erfolgen die vorgenommenen Änderungen wiederum <b>in beide Richtungen gleichzeitig</b>, also <b>synchron</b>. </p>
<p>Nun wissen Sie, was mit einer automatischen Synchronisierung gemeint ist: Ereignisse können hinzugefügt, bearbeitet oder gelöscht werden  - diese Änderungen, 
egal wo sie vorgenommen wurden (im Outlook- oder im Portal-Kalender), werden immer im jeweils anderen Kalender angezeigt.</p>

<a name="useful1"></a>
<h2>Synchronisierung persönlicher Aufgaben</h2>
<p>Jetzt werden wir die Aufgaben synchronisieren, ohne dabei weiter  ins Detail zu gehen. Eine wichtige Anmerkung: Auch diese Synchronisierung läuft ohne Ihre 
aktive Beteiligung ab.</p>

<div align="center">
  <div align="center"><a href="javascript:ShowImg('#SITE_DIR#images/docs/big/outlook_13.png', 600, 413, 'Synchronisierung persönlicher Aufgaben')"><img title="Synchronisierung persönlicher Aufgaben" border="0" alt="Synchronisierung persönlicher Aufgaben" src="#SITE_DIR#images/docs/outlook_13_sm.png" width="400" height="276" /> </a>
    <br />
  </div>
  <div align="center"><i>Synchronisierung persönlicher Aufgaben</i>
  </div>
</div>

<p>Machen Sie bitte Folgendes:</p>
<ul>
  <li>Synchronisieren Sie die Nutzer (Schaltfläche 'Outlook' auf der Seite 'Mitarbeiter suchen' klicken);</li>
  <li>Falls gefragt, geben Sie nun das AD- Passwort (oder Passwort für den Portalzugriff) ein;</li>
  <li>Die Nutzerliste wird dem MS Outlook automatisch hinzugefügt;</li>
  <li>Mit den Outlook-Werkzeug stellen Sie einem beliebigen Nutzer eine Aufgabe;</li>
  <li>MS Outlook stellt die Verbindung zum Portal her und synchronisiert die Aufgaben. </li>
</ul>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>