<?
$aMenuLinks = Array(
	Array(
		"Переговорные", 
		"/services/index.php", 
		Array("/services/res_c.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('MeetingRoomBookingSystem')" 
	),
	Array(
		"Есть Идея?", 
		"/services/idea/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Idea')" 
	),
	Array(
		"Списки", 
		"/services/lists/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Lists')" 
	),
	Array(
		"Электронные заявки", 
		"/services/requests/", 
		Array(), 
		Array(), 
		"false" 
	),
	Array(
		"Обучение", 
		"/services/learning/", 
		Array("/services/course.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Learning')" 
	),
	Array(
		"База знаний (wiki)", 
		"/services/wiki/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Wiki')" 
	),
	Array(
		"Вопросы и ответы", 
		"/services/faq/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Опросы", 
		"/services/votes.php", 
		Array("/services/vote_new.php", "/services/vote_result.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Vote')" 
	),
	Array(
		"Техническая поддержка", 
		"/services/support.php?show_wizard=Y", 
		Array("/services/support.php"), 
		Array(), 
		"false" 
	),
	Array(
		"Каталог ссылок", 
		"/services/links.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('WebLink')" 
	),
	Array(
		"Подписка", 
		"/services/subscr_edit.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Subscribe')" 
	),
	Array(
		"Журнал изменений", 
		"/services/event_list.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('EventList')" 
	),
	Array(
		"Зарплата и отпуск", 
		"/services/salary/", 
		Array(), 
		Array(), 
		"LANGUAGE_ID == 'ru' && CBXFeatures::IsFeatureEnabled('Salary')" 
	),
	Array(
		"Доска объявлений", 
		"/services/board/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Board')" 
	),
	Array(
		"Телефония", 
		"/services/telephony/", 
		Array(), 
		Array(), 
		"CModule::IncludeModule(\"voximplant\") && SITE_TEMPLATE_ID !== \"bitrix24\" && Bitrix\\Voximplant\\Security\\Helper::isMainMenuEnabled()" 
	),
	Array(
		"Открытые линии", 
		"/services/openlines/", 
		Array(), 
		Array(), 
		"CModule::IncludeModule(\"imopenlines\") && SITE_TEMPLATE_ID !== \"bitrix24\" && Bitrix\\ImOpenlines\\Security\\Helper::isMainMenuEnabled()" 
	),
	Array(
		"Видеоконференции", 
		"/services/video/", 
		Array(), 
		Array(), 
		"" 
	)
);
?>