<?
$MESS ['BPT_TTITLE'] = "Обработка обращений (запросов) граждан";
$MESS ['BPT_SPT_PARAM_OP_READ'] = "Сотрудники, имеющие право на просмотр всех процессов";
$MESS ['BPT_SPT_PARAM_OP_CREATE'] = "Сотрудники, имеющие право создавать новые процессы";
$MESS ['BPT_SPT_PARAM_OP_ADMIN'] = "Сотрудники, имеющие право управлять процессами";
$MESS ['BPT_SPT_PARAM_OPERATOR'] = "Сотрудник(и), обрабатывающие обращения (запросы)";
$MESS ['BPT_SPT_PARAM_CONTROLLER'] = "Сотрудник(и), контролирующие обработку обращений (запросов)";
$MESS ['BPT_SPT_PARAM_STATUS'] = "Статус обращения (запроса)";                                       
$MESS ['BPT_SPT_PARAM_STATUS_VALUE_1'] = "В обработке";          
$MESS ['BPT_SPT_PARAM_STATUS_VALUE_2'] = "Отказано";         
$MESS ['BPT_SPT_PARAM_STATUS_VALUE_3'] = "Подготовлен ответ";    
$MESS ['BPT_SPT_PARAM_STATUS_VALUE_4'] = "Предоставлен ответ";  
$MESS ['BPT_SPT_PARAM_WANSWER'] = "Ответственный за подготовку ответа";  
$MESS ['BPT_SPT_PARAM_ANSWER'] = "Ответ на обращение (запрос)";        
$MESS ['BPT_SPT_PARAM_OFFEMAIL'] = "Email официального сайта";   

$MESS ['BPT_SPT_P_NAME'] = "ФИО";  
$MESS ['BPT_SPT_P_TEXT'] = "Текст обращения (запроса)";  
$MESS ['BPT_SPT_P_TYPE'] = "Вид запроса";   
$MESS ['BPT_SPT_P_TYPE_VALUE_1'] = "Запрос информации (ФЗ № 8 от 13.02.2009)";   
$MESS ['BPT_SPT_P_TYPE_VALUE_2'] = "Обращение граждан (ФЗ № 59 от 02.05.2006)";  
$MESS ['BPT_SPT_P_ADDRESS'] = "Адрес";                               
$MESS ['BPT_SPT_P_PHONE'] = "Телефон";   
$MESS ['BPT_SPT_P_EMAIL'] = "Электронная почта";  
$MESS ['BPT_SPT_P_DATE_FROM'] = "Дата обращения (запроса)";  
$MESS ['BPT_SPT_P_ID'] = "Номер обращения с сайта";         
$MESS ['BPT_SPT_P_ID_DESC'] = "Не заполнять вручную!";


$MESS ['BPT_BT_SWA'] = "Последовательный бизнес-процесс";

$MESS ['BPT_SPT_STA1_STATE_TITLE'] = "В обработке";
$MESS ['BPT_SPT_STA1_TITLE'] = "Установка статуса: В обработке";

$MESS ['BPT_BT_SFA1_TITLE'] = "Сохранение параметров";

$MESS ['BPT_SPT_PA1_TITLE'] = "Параллельное выполнение";

$MESS ['BPT_SPT_SA1_TITLE'] = "Последовательность действий";

$MESS ['BPT_SPT_WA1_TITLE'] = "Цикл обработки обращения (запроса)";

$MESS ['BPT_SPT_SA2_TITLE'] = "Последовательность действий";

$MESS ['BPT_SPT_AA1_TITLE'] = "Соответствие требованиям ФЗ?";
$MESS ['BPT_SPT_AA1_NAME'] = "Проверить соответствие обращения (запроса) требованиям ФЗ";
$MESS ['BPT_SPT_AA1_DESC'] = "ФИО: {=Document:NAME}
Адрес: {=Document:PROPERTY_ADDRESS}
Телефон: {=Document:PROPERTY_PHONE}
E-mail: {=Document:PROPERTY_EMAIL}
Тип: {=Document:PROPERTY_TYPE}
Рег.номер: {=Document:ID}

Текст запроса (обращения):
{=Document:PREVIEW_TEXT}

---
В случае отказа, в поле \"Комментарий\" необходимо обосновать причину отказа с указанием соответствующих статей ФЗ.";
$MESS ['BPT_SPT_AA1_STATUS'] = "Проголосовало #PERC#% (#REV# из #TOT#)";

$MESS ['BPT_SPT_SA3_TITLE'] = "Последовательность действий";

$MESS ['BPT_SPT_RI1_TITLE'] = "Определение отвечающего";
$MESS ['BPT_SPT_RI1_NAME'] = "Определите ответственного за подготовку ответа";
$MESS ['BPT_SPT_RI1_DESC'] = "ФИО: {=Document:NAME}
Адрес: {=Document:PROPERTY_ADDRESS}
Телефон: {=Document:PROPERTY_PHONE}
E-mail: {=Document:PROPERTY_EMAIL}
Тип: {=Document:PROPERTY_TYPE}
Рег.номер: {=Document:ID}

Текст запроса (обращения):
{=Document:PREVIEW_TEXT}";

$MESS ['BPT_SPT_RI2_TITLE'] = "Подготовка ответа";
$MESS ['BPT_SPT_RI2_NAME'] = "Подготовьте ответ на запрос (обращение) N {=Document:ID}, от {=Document:ACTIVE_FROM}";
$MESS ['BPT_SPT_RI2_DESC'] = "ФИО: {=Document:NAME}
Адрес: {=Document:PROPERTY_ADDRESS}
Телефон: {=Document:PROPERTY_PHONE}
E-mail: {=Document:PROPERTY_EMAIL}
Тип: {=Document:PROPERTY_TYPE}
Рег.номер: {=Document:ID}
Дата обращения (запроса): {=Document:ACTIVE_FROM}

Текст запроса (обращения):
{=Document:PREVIEW_TEXT}";

$MESS ['BPT_SPT_SV1_TITLE'] = "Подготовлен ответ (изменение статуса)";

$MESS ['BPT_SPT_SF2_TITLE'] = "Записать ответ";

$MESS ['BPT_SPT_SA4_TITLE'] = "Последовательность действий";

$MESS ['BPT_SPT_SV2_TITLE'] = "Отказать (изменение статуса)";

$MESS ['BPT_SPT_SF2_TITLE'] = "Отказать (запись ответа)";

$MESS ['BPT_SPT_STA2_TITLE'] = "Установить статус обработки";

$MESS ['BPT_SPT_SA5_TITLE'] = "Последовательность действий";

$MESS ['BPT_BT_SNMA1_TITLE'] = "Сообщение контролеру по соц. сети";
$MESS ['BPT_BT_SNMA1_MESSAGE'] = "Обращение (запрос) #{=Document:ID}  {=Document:ACTIVE_FROM} в обработке.";

$MESS ['BPT_SPT_IE1_TITLE'] = "Источник запроса?";   

$MESS ['BPT_SPT_IEB1_TITLE'] = "Заполнена форма";      

$MESS ['BPT_SPT_R1_TITLE'] = "Задачи по отправке ответа";
$MESS ['BPT_SPT_R1_NAME'] = "Отправьте ответ на запрос (обращение)";
$MESS ['BPT_SPT_R1_DESC'] = "Ответ на \"{=Document:PROPERTY_TYPE}\" N{=Document:PROPERTY_ID} от {=Document:ACTIVE_FROM}

{=Document:DETAIL_TEXT}

С уважением,
{=Variable:who_answer_printable}

Ваше обращение (запрос):
{=Document:PREVIEW_TEXT}";
$MESS ['BPT_SPT_R1_STATUS'] = "Ознакомлено #PERC#% (#REV# из #TOT#)";
$MESS ['BPT_SPT_R1_BUTTON'] = "Ответ отправлен";

$MESS ['BPT_SPT_IEB2_TITLE'] = "Обращение с сайта";
$MESS ['BPT_SPT_M2_TITLE'] = "Уведомление на официальный сайт о принятии";
$MESS ['BPT_SPT_M1_TITLE'] = "Ответ на официальный сайт";
$MESS ['BPT_SPT_M1_TEXT'] = "<ANSWER>
    <ID>{=Template:ID}</ID>
    <STATUS>{=Document:PROPERTY_STATUS}</STATUS>
    <TEXT>
    Ответ на \"{=Document:PROPERTY_TYPE}\" N{=Document:PROPERTY_ID} от {=Document:ACTIVE_FROM}

    {=Document:DETAIL_TEXT}

    С уважением,
    {=Variable:who_answer_printable}
    </TEXT>
</ANSWER>";

$MESS ['BPT_SPT_SF3_TITLE'] = "Изменение документа"; 

$MESS ['BPT_SPT_STA3_TITLE'] = "Cтатус: Ответ (отказ) оправлен"; 
$MESS ['BPT_SPT_STA3_STATE_TITLE'] = "Ответ (отказ) отправлен";  
 
$MESS ['BPT_SPT_DF_TYPE'] = "Вид обращения";
$MESS ['BPT_SPT_DF_ADDRESS'] = "Адрес";
$MESS ['BPT_SPT_DF_PHONE'] = "Телефон";
$MESS ['BPT_SPT_DF_EMAIL'] = "Электронная почта";
$MESS ['BPT_SPT_DF_ID'] = "Номер обращения";
$MESS ['BPT_SPT_DF_STATUS'] = "Состояние обращения (запроса)";
?>