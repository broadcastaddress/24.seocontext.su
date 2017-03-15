<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?$APPLICATION->ShowHead();?>
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<link rel="stylesheet" type="text/css" media="print" href="<?=SITE_TEMPLATE_PATH?>/print.css" />
<link rel="alternate stylesheet" type="text/css" media="screen,projection" href="<?=SITE_TEMPLATE_PATH?>/print.css" title="print" />
<title><?$APPLICATION->ShowTitle()?></title>

<!--[if lt IE 6]><style type="text/css">#main-page{width:100%;} #page-content{width:100%;}</style><![endif]-->
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/script.js"></script>

</head>
<body>

<div id="panel"><?$APPLICATION->ShowPanel();?></div>

<div id="page-container">
	<a href="#" onclick="return BackToDesignMode();" id="print-link-back">Back to Normal View</a>
	<div id="header">
		<div id="header-logo"></div>

		<div id="company-info">
		<?$APPLICATION->IncludeFile(
			$APPLICATION->GetTemplatePath("include_areas/company_name.php"),
			Array(),
			Array("MODE"=>"html")
		);?>
		</div>

		<div id="auth">
			<?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "auth", Array(
				"REGISTER_URL"	=>	"#SITE_DIR#auth.php",
				"PROFILE_URL"	=>	"#SITE_DIR#contacts/personal/user/".$USER->GetID()."/edit/",
				"SHOW_ERRORS" => "Y",
			)
		);?></div>

	</div>

	<div id="header-separator"></div>

	<div id="top-menu-section">
		
		<div id="site-icons">
			<a href="#SITE_DIR#search/map.php" class="sitemap-icon" title="Site Map"></a>
			<a href="#print" onclick="SetPrintCSS(true)" rel="nofollow" class="print-icon" title="Printable Version"></a>
			<a href="" onclick="return AddToBookmark()" class="favourite-icon" title="Add to Favorites"></a>
		</div>

		<div id="top-menu-left-corner">
			<div id="top-menu">
			<?
			if (IsModuleInstalled('extranet') && CExtranet::IsIntranetUser())
				$_GET["employee"] = "Y";
			else
				$_GET["employee"] = "N";
			?>
			<?$APPLICATION->IncludeComponent("bitrix:menu", "horizontal_multilevel", Array(
				"ROOT_MENU_TYPE"	=>	"top",
				"MAX_LEVEL"	=>	"3",
				"CHILD_MENU_TYPE"	=>	"left",
				"USE_EXT"	=>	"Y",
				"MENU_CACHE_TYPE" => "A",
				"MENU_CACHE_TIME" => "3600",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => Array("employee")
			)
		);?></div>
		</div>

	</div>

	<?if($APPLICATION->GetCurPage() != "#SITE_DIR#index.php" && $APPLICATION->GetCurPage() != "#SITE_DIR#"):?>
	<div id="breadcumb-section">

		<div id="search">
			<form method="get" action="#SITE_DIR#search/">
				<span class="search-text">Search</span>
				<input class="search-field" type="text" name="q"/>
				<input class="search-submit" type="image" alt="" src="<?=SITE_TEMPLATE_PATH?>/images/search-button.gif"/>
			</form>
		</div>

		<div id="navigation">
			<?$APPLICATION->IncludeComponent(
				"bitrix:breadcrumb",
				".default",
				Array(
					"START_FROM" => "1",
					"PATH" => "",
					"SITE_ID" => ""
				)
			);?>
		</div>

	</div>
	<?endif?>

	<div id="page-content">

	<?if($APPLICATION->GetCurPage() == "#SITE_DIR#index.php" || $APPLICATION->GetCurPage() == "#SITE_DIR#"):?>

		<div id="main-page">

		<div align="center">
			<?$APPLICATION->IncludeComponent(
					"bitrix:advertising.banner",
					"",
					Array(
						"TYPE" => "468x60_TOP", 
						"CACHE_TYPE" => "A", 
						"CACHE_TIME" => "0" 
					)
				);?>
		</div>

		<h1><?$APPLICATION->ShowTitle(false);?>
			<div id="search-main-page">
				<form method="get" action="#SITE_DIR#search/">
					<span class="search-text">Search</span>
					<input class="search-field" type="text" name="q"/>
					<input class="search-submit" type="image" alt="" src="<?=SITE_TEMPLATE_PATH?>/images/search-button.gif"/>
				</form>
			</div>
		</h1>

	<?else:?>

		<table id="main-table" cellpadding="0" cellspacing="0">
			<tr>
				<td id="left-column">
					<?$APPLICATION->ShowViewContent("sidebar_tools_1")?>
					<div class="left-column-delimiter"></div>

					<?$APPLICATION->IncludeComponent("bitrix:menu", "left", Array(
							"ROOT_MENU_TYPE"	=>	"left",
							"MAX_LEVEL"	=>	"2",
							"CHILD_MENU_TYPE"	=>	"left",
							"USE_EXT"	=>	"Y",
							"MENU_CACHE_TYPE" => "A",
							"MENU_CACHE_TIME" => "3600",
							"MENU_CACHE_USE_GROUPS" => "Y",
							"MENU_CACHE_GET_VARS" => Array()
							)
						);?>
						<br />

						<?$APPLICATION->IncludeComponent("bitrix:socialnetwork.events_dyn", ".default", Array(
							"PATH_TO_USER"	=>	"#SITE_DIR#contacts/personal/user/#user_id#/",
							"PATH_TO_GROUP"	=>	"#SITE_DIR#workgroups/group/#group_id#/",
							"PATH_TO_MESSAGE_FORM"	=>	"#SITE_DIR#contacts/personal/messages/form/#user_id#/",
							"PATH_TO_MESSAGE_FORM_MESS"	=>	"#SITE_DIR#contacts/personal/messages/form/#user_id#/#message_id#/",
							"PATH_TO_MESSAGES_CHAT"	=>	"#SITE_DIR#contacts/personal/messages/chat/#user_id#/",
							"PATH_TO_CONPANY_DEPARTMENT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#", 
							"PATH_TO_VIDEO_CALL" => "#SITE_DIR#contacts/personal/video/#user_id#/",
							"PATH_TO_SMILE"	=>	"/bitrix/images/socialnetwork/smile/",
							"MESSAGE_VAR"	=>	"message_id",
							"PAGE_VAR"	=>	"page",
							"USER_VAR"	=>	"user_id",
							"NAME_TEMPLATE" => "#NOBR##NAME# #LAST_NAME##/NOBR#",
							)
						);
						?>
						<br />
						<div align="center"><?$APPLICATION->IncludeComponent(
							"bitrix:advertising.banner",
							"",
							Array(
								"TYPE" => "100x100_ONE", 
								"CACHE_TYPE" => "A", 
								"CACHE_TIME" => "0" 
							)
						);?></div>
						<br />

						<div align="center">
						<?$APPLICATION->IncludeComponent(
							"bitrix:advertising.banner",
							"",
							Array(
								"TYPE" => "100x100_TWO", 
								"CACHE_TYPE" => "A", 
								"CACHE_TIME" => "0" 
							)
						);?>
						</div>

				</td>


				<td id="main-column">

					<div align="center"><?$APPLICATION->IncludeComponent(
						"bitrix:advertising.banner",
						"",
						Array(
							"TYPE" => "468x60_TOP", 
							"CACHE_TYPE" => "A", 
							"CACHE_TIME" => "0" 
						)
						);?>
					</div>

					<h1 id="pagetitle"><?$APPLICATION->ShowTitle(false);?><?$APPLICATION->ShowViewContent("pagetitle")?></h1>
	<?endif?>
	<?if(CModule::IncludeModule('intranet')){$GLOBALS['INTRANET_TOOLBAR']->Show();}?>