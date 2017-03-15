<?
$MESS["SALE_QH_TITLE"] = "Qiwi Wallet";
$MESS["SALE_QH_DESCRIPTION"] = "<div class='adm-info-message'>
Der Zahlungsservice <a href='https://ishop.qiwi.com' target='_blank'>Visa QIWI Wallet</a><br/>
<ol>
<li>Geben Sie erforderliche Parameter an.</li>
<li>Erstellen Sie eine Seite zum Empfangen der Meldungen vom Zahlungssystem und platziere Sie dort die Komponente <strong>bitrix:sale.order.payment.receive</strong>.</li>
<li>Konfigurieren Sie <strong>bitrix:sale.order.payment.receive</strong> für dieses Zahlungssystem.</li>
<li>Geben Sie im <a href = 'https://ishop.qiwi.com/options/merchants.action'>persönlichen Account</a> auf Qiwi Wallet die URL der erstellten Seite an.</li>
</ol>
</div>
";
$MESS["SALE_QH_SHOP_ID"] = "ID des Onlineshops";
$MESS["SALE_QH_SHOP_ID_DESCR"] = "Diese ID können Sie auf der Seite der Einstellungen im Bereich <a target='_blank' href='https://ishop.qiwi.com/options/merchants.action'>HTTP-Einstellungen</a> angezeigt bekommen.";
$MESS["SALE_QH_API_LOGIN"] = "API-ID";
$MESS["SALE_QH_API_LOGIN_DESCR"] = "ID für den Zugriff auf API. Wird in <a href='https://ishop.qiwi.com/options/merchants.action' target='_blank'>Onlineshop-Einstellungen</a> im Bereich 'Authentifizierungsdaten für alle Protokolle' definiert.";
$MESS["SALE_QH_API_PASS"] = "API-Passwort";
$MESS["SALE_QH_API_PASS_DESCR"] = "Passwort für den Zugriff auf API. Wird in <a href='https://ishop.qiwi.com/options/merchants.action' target='_blank'>Onlineshop-Einstellungen</a> im Bereich 'Authentifizierungsdaten für alle Protokolle' definiert.";
$MESS["SALE_QH_CLIENT_PHONE"] = "Telefon des Kunden";
$MESS["SALE_QH_ORDER_ID"] = "Transaktionsnummer in Ihrem System";
$MESS["SALE_QH_ORDER_ID_DESCR"] = "(z.B. Bestellnummer im Onlineshop)";
$MESS["SALE_QH_SHOULD_PAY"] = "Betrag";
$MESS["SALE_QH_SHOULD_PAY_DESCR"] = "Betrag in der Rechnung";
$MESS["SALE_QH_CURRENCY"] = "Rechnungswährung";
$MESS["SALE_QH_CURRENCY_DESCR"] = "(muss entsprechend ISO 4217 in Buchstaben oder Zahlen angegeben werden)";
$MESS["SALE_QH_BILL_LIFETIME"] = "Gültigkeitsdauer der Rechnung";
$MESS["SALE_QH_BILL_LIFETIME_DESCR"] = "(in Minuten)";
$MESS["SALE_QH_FAIL_URL"] = "URL der Seite, auf welche der Nutzer weitergeleitet wird, falls die Bezahlung der Rechnung <strong>fehlgeschlagen</strong> ist.";
$MESS["SALE_QH_SUCCESS_URL"] = "URL der Seite, auf welche der Nutzer weitergeleitet wird, falls die Bezahlung der Rechnung <strong>erfolgreich</strong> war.";
$MESS["SALE_QH_CHANGE_STATUS_PAY"] = "Bestellung automatisch bezahlen, wenn der Status einer erfolgreichen Bezahlung angezeigt wird.";
$MESS["SALE_QH_CHANGE_STATUS_PAY_DESC"] = "(<strong>Y</strong> - ja, <strong>N</strong> - nein)";
$MESS["SALE_QH_YES"] = "Ja";
$MESS["SALE_QH_NO"] = "Nein";
$MESS["SALE_QH_AUTHORIZATION"] = "Autorisierungsmethode";
$MESS["SALE_QH_AUTHORIZATION_DESCR"] = "Wird zur Autorisierung bei den Benachrichtigungen benutzt. Kann im persönlichen Account im Bereich <a href='https://ishop.qiwi.com/options/merchants.action' target='_blank'>Einstellungen von Pull (REST)</a> (Option 'Signatur'). <br/> (<strong>OPEN</strong> - Offene Passwortübermittlung, <strong>SIMPLE</strong> - Verwendung von einfacher Signatur)";
$MESS["SALE_QH_AUTH_OPEN"] = "Offene Passwortübermittlung";
$MESS["SALE_QH_AUTH_SIMPLE"] = "Verwendung von einfacher Signatur";
$MESS["SALE_QH_NOTICE_PASSWORD"] = "Passwort der Benachrichtigung";
$MESS["SALE_QH_NOTICE_PASSWORD_DESCR"] = "Passwort kann im Feld <a target='_blank' href='https://ishop.qiwi.com/options/merchants.action'>Passwort der Benachrichtigung ändern</a> im Bereich Einstellungen von Pull (REST) geändert werden. <strong>Geben Sie die URL für Benachrichtigung unbedingt an.</strong>";
$MESS["MU_IBLOCK_DESCRIPTION_PAYING"] = "Sie brauchen nicht mehr im Büro herumzulaufen, um sich irgendwelche Unterschriften der Vorgesetzten zu holen. Senden Sie einen Antrag auf Bezahlung einer Rechnung, indem Sie einige Felder ausfüllen oder eine eingescannte Kopie der Rechnung anhängen. Ihr Antrag wird an Ihren Vorgesetzten gesendet, und nach dessen Bestätigung an weitere Verantwortliche. Sie werden benachrichtigt, sobald Ihre Rechnung bestätigt und bezahlt wird.



Probieren Sie jetzt!
";
$MESS["MU_IBLOCK_DESCRIPTION_HOLIDAY"] = "Sie brauchen nicht mehr im Büro herumzulaufen, um sich irgendwelche Unterschriften der Vorgesetzten zu holen. Senden Sie einen Antrag auf Jahresurlaub, indem Sie einige Felder ausfüllen. Ihr Antrag wird an Ihren Vorgesetzten gesendet, und nach dessen Bestätigung an weitere Verantwortliche. Sie werden benachrichtigt, sobald Ihr Antrag bestätigt wird.



Probieren Sie jetzt!
";
$MESS["MU_IBLOCK_NAME_INBOX"] = "Eingehende Dokumente";
$MESS["MU_IBLOCK_NAME_PAYING"] = "Eingehende Rechnung";
$MESS["MU_IBLOCK_NAME_CASH"] = "Antrag auf Bargeldauszahlung";
$MESS["MU_IBLOCK_FIELD_LIST_YES"] = "Ja";
$MESS["MU_IBLOCK_SECTION_ADD"] = "Bereich hinzufügen";
$MESS["MU_IBLOCK_ELEMENT_ADD"] = "Element hinzufügen";
$MESS["MU_IBLOCK_DESCRIPTION_OUTBOX"] = "Fügen Sie ein ausgehendes Dokument zum System hinzu, geben Sie die Versandart, Anschrift sowie eventuelle Kommentare an. Ihr Dokument wird an den Verantwortlichen gesendet, der es registrieren und versenden wird.



Probieren Sie jetzt!
";
$MESS["MU_IBLOCK_NAME_MISSION"] = "Dienstreise beantragen";
$MESS["MU_IBLOCK_NAME_HOLIDAY"] = "Urlaub beantragen";
$MESS["MU_IBLOCK_SECTION_EDIT"] = "Bereich bearbeiten";
$MESS["MU_IBLOCK_ELEMENT_EDIT"] = "Element bearbeiten";
$MESS["MU_IBLOCK_NAME_OUTBOX"] = "Ausgehende Dokumente";
$MESS["MU_MENU_TITLE_MY_PROCESSES"] = "Meine Prozesse";
$MESS["MU_IBLOCK_NAME_FIELD"] = "Name";
$MESS["MU_IBLOCK_FIELD_NAME"] = "Name";
$MESS["MU_IBLOCK_FIELD_LIST_NO"] = "Nein";
$MESS["MU_IBLOCK_DESCRIPTION_MISSION"] = "Beantragen Sie eine Dienstreise, indem Sie Reisedaten, Zielort und Zweck der Reise angeben. Ihr Antrag wird an Ihren Vorgesetzten gesendet, und nach dessen Bestätigung an weitere Verantwortliche. Sie werden benachrichtigt, sobald Ihr Antrag bestätigt wird.



Probieren Sie jetzt!
";
$MESS["NAME"] = "Workflows";
$MESS["MU_MENU_TITLE_PROCESS"] = "Workflows";
$MESS["MU_IBLOCK_SECTION_NAME"] = "Bereich";
$MESS["SECTION_NAME"] = "Bereiche";
$MESS["MU_IBLOCK_SECTIONS_NAME"] = "Bereiche";
$MESS["MU_IBLOCK_SECTION_DELETE"] = "Bereich löschen";
$MESS["MU_IBLOCK_ELEMENT_DELETE"] = "Element löschen";
$MESS["MU_IBLOCK_DESCRIPTION_CASH"] = "Beantragen Sie eine Bargeldauszahlung, indem Sie alle erforderlichen Informationen angeben. Ihr Antrag wird an Ihren Vorgesetzten gesendet, und nach dessen Bestätigung an weitere Verantwortliche. Sie werden benachrichtigt, sobald Ihr Antrag bestätigt wird.



Probieren Sie jetzt!
";
$MESS["MU_IBLOCK_DESCRIPTION_INBOX"] = "Registrieren Sie eingehende Dokumente mithilfe von einem Workflow im Activity Stream. Laden Sie ein empfangenes Dokument hoch, geben Sie alle erforderlichen Informationen an und senden Sie es an Verantwortliche zur Kenntnisnahme.



Probieren Sie jetzt!
";
$MESS["MU_IBLOCK_ELEMENT_NAME"] = "Element";
$MESS["ELEMENT_NAME"] = "Elemente";
$MESS["MU_IBLOCK_ELEMENTS_NAME"] = "Elemente";
?>