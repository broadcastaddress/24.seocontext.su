<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?

if (array_key_exists("CATEGORY", $arParams) && strlen($arParams["CATEGORY"]) > 0)
	echo GetMessage("BLOG_BLOG_BLOG_CATEGORY_FILTER", array("#CATEGORY#" => htmlspecialchars($arParams["CATEGORY"])));

if(count($arResult["POSTS"])>0)
{
	foreach($arResult["POSTS"] as $CurPost)
	{
		$className = "blog-post";
		?>
		<div class="<?=$className?>">
			<h2 class="blog-post-title"><a href="<?=$CurPost["urlToPost"]?>" title="<?=$CurPost["TITLE"]?>"><?=$CurPost["TITLE"]?></a></h2>
			<div class="blog-post-info-back blog-post-info-top">
				<div class="blog-post-info">
					<?if ($arParams["SHOW_RATING"] == "Y"):?>
						<div class="blog-post-rating">
						<?
						$APPLICATION->IncludeComponent(
							"bitrix:rating.vote", "",
							Array(
								"ENTITY_TYPE_ID" => "BLOG_POST",
								"ENTITY_ID" => $CurPost["ID"],
								"OWNER_ID" => $CurPost["AUTHOR_ID"],
								"USER_HAS_VOTED" => $arResult["RATING"][$CurPost["ID"]]["USER_HAS_VOTED"],
								"TOTAL_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VOTES"],
								"TOTAL_POSITIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_POSITIVE_VOTES"],
								"TOTAL_NEGATIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_NEGATIVE_VOTES"],
								"TOTAL_VALUE" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VALUE"]
							),
							null,
							array("HIDE_ICONS" => "Y")
						);?>
						</div>
					<?endif;?>

					<div class="blog-author">
						<?if($arParams["SEO_USER"] == "Y"):?>
							<noindex>
								<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>" rel="nofollow"></a>
							</noindex>
						<?else:?>
							<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>"></a>
						<?endif;?>
						<?
						if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && (strlen($CurPost["urlToBlog"]) > 0 || strlen($CurPost["urlToAuthor"]) > 0) && array_key_exists("BLOG_USER_ALIAS", $CurPost) && strlen($CurPost["BLOG_USER_ALIAS"]) > 0)
							$arTmpUser = array(
								"NAME" => "",
								"LAST_NAME" => "",
								"SECOND_NAME" => "",
								"LOGIN" => "",
								"NAME_LIST_FORMATTED" => $CurPost["~BLOG_USER_ALIAS"],
							);
						elseif (strlen($CurPost["urlToBlog"]) > 0 || strlen($CurPost["urlToAuthor"]) > 0)
							$arTmpUser = array(
								"NAME" => $CurPost["~AUTHOR_NAME"],
								"LAST_NAME" => $CurPost["~AUTHOR_LAST_NAME"],
								"SECOND_NAME" => $CurPost["~AUTHOR_SECOND_NAME"],
								"LOGIN" => $CurPost["~AUTHOR_LOGIN"],
								"NAME_LIST_FORMATTED" => "",
							);	
						?>
						<?if($arParams["SEO_USER"] == "Y"):?>
							<noindex>
						<?endif;?>
						<?
						$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $CurPost["AUTHOR_ID"],
								"NAME" => $arTmpUser["NAME"],
								"LAST_NAME" => $arTmpUser["LAST_NAME"],
								"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
								"LOGIN" => $arTmpUser["LOGIN"],
								"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
								"USE_THUMBNAIL_LIST" => "N",
								"PROFILE_URL" => $CurPost["urlToAuthor"],
								"PROFILE_URL_LIST" => $CurPost["urlToBlog"],
								"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
								"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
								"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
								"SHOW_YEAR" => $arParams["SHOW_YEAR"],
								"CACHE_TYPE" => $arParams["CACHE_TYPE"],
								"CACHE_TIME" => $arParams["CACHE_TIME"],
								"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
								"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
								"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
								"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_SONET_USER_PROFILE"],
								"INLINE" => "Y",
								"SEO_USER" => $arParams["SEO_USER"],
							),
							false,
							array("HIDE_ICONS" => "Y")
						);
						?>
						<?if($arParams["SEO_USER"] == "Y"):?>
							</noindex>
						<?endif;?>
					</div>
					<div class="blog-post-date"><span class="blog-post-day"><?=$CurPost["DATE_PUBLISH_DATE"]?></span><span class="blog-post-time"><?=$CurPost["DATE_PUBLISH_TIME"]?></span><span class="blog-post-date-formated"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></span></div>
				</div>
			</div>
			
			<div class="blog-post-content">
				<div class="blog-post-avatar"><?=$CurPost["BlogUser"]["AVATAR_img"]?></div>
				<?=$CurPost["TEXT_FORMATED"]?>
				<?
				if ($CurPost["CUT"] == "Y")
				{
					?><p><a class="blog-postmore-link" href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_MORE")?></a></p><?
				}
				?>
				<?if($CurPost["POST_PROPERTIES"]["SHOW"] == "Y"):?>
					<p>
					<?foreach ($CurPost["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
						<?if(strlen($arPostField["VALUE"])>0):?>
							<b><?=$arPostField["EDIT_FORM_LABEL"]?>:</b>&nbsp;<?$APPLICATION->IncludeComponent(
										"bitrix:system.field.view", 
										$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
										array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?><br />
						<?endif;?>
					<?endforeach;?>
					</p>
				<?endif;?>
			</div>

			<div class="blog-post-meta">
				<div class="blog-post-meta-util">
					<span class="blog-post-comments-link"><a href="<?=$CurPost["urlToPost"]?>#comments"><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?> <?=IntVal($CurPost["NUM_COMMENTS"]);?></a></span>
					<span class="blog-post-views-link"><a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?> <?=IntVal($CurPost["VIEWS"]);?></a></span>
				</div>
				<div class="blog-post-tag">
					<noindex>
					<?
					if(!empty($CurPost["CATEGORY"]))
					{
						echo GetMessage("BLOG_BLOG_BLOG_CATEGORY");
						$i=0;
						foreach($CurPost["CATEGORY"] as $v)
						{
							if($i!=0)
								echo ",";
							?> <a href="<?=$v["urlToCategory"]?>" rel="nofollow"><?=$v["NAME"]?></a><?
							$i++;
						}
					}
					?>
					</noindex>
				</div>
			</div>

		</div>
			
<!--				
		<table class="blog-table-post">
		<tr>
			<th width="100%">
				<table class="blog-table-post-table">
				<tr>
					<td width="100%" align="left">
						<span class="blog-post-date"><b><?=$CurPost["DATE_PUBLISH_FORMATED"]?></b></span><br />
						<span class="blog-author"><b><a href="<?=$CurPost["urlToAuthor"]?>" class="blog-user"></a>&nbsp;<?
						
						if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && (strlen($CurPost["urlToBlog"]) > 0 || strlen($CurPost["urlToAuthor"]) > 0) && array_key_exists("BLOG_USER_ALIAS", $CurPost) && strlen($CurPost["BLOG_USER_ALIAS"]) > 0)
						{
							$arTmpUser = array(
								"NAME" => "",
								"LAST_NAME" => "",
								"SECOND_NAME" => "",
								"LOGIN" => "",
								"NAME_LIST_FORMATTED" => $CurPost["~BLOG_USER_ALIAS"],
							);
						}
						elseif (strlen($CurPost["urlToBlog"]) > 0 || strlen($CurPost["urlToAuthor"]) > 0)
							$arTmpUser = array(
								"NAME" => $CurPost["~AUTHOR_NAME"],
								"LAST_NAME" => $CurPost["~AUTHOR_LAST_NAME"],
								"SECOND_NAME" => $CurPost["~AUTHOR_SECOND_NAME"],
								"LOGIN" => $CurPost["~AUTHOR_LOGIN"],
								"NAME_LIST_FORMATTED" => "",
							);	

						$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $CurPost["AUTHOR_ID"],
								"HTML_ID" => "blog_new_posts_list_".$CurPost["AUTHOR_ID"],
								"NAME" => $arTmpUser["NAME"],
								"LAST_NAME" => $arTmpUser["LAST_NAME"],
								"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
								"LOGIN" => $arTmpUser["LOGIN"],
								"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
								"USE_THUMBNAIL_LIST" => "N",
								"PROFILE_URL" => $CurPost["urlToAuthor"],
								"PROFILE_URL_LIST" => $CurPost["urlToBlog"],							
								"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
								"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
								"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
								"SHOW_YEAR" => $arParams["SHOW_YEAR"],
								"CACHE_TYPE" => $arParams["CACHE_TYPE"],
								"CACHE_TIME" => $arParams["CACHE_TIME"],
								"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
								"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
								"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
								"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_SONET_USER_PROFILE"],
								"INLINE" => "Y",
								"SEO_USER" => $arParams["SEO_USER"],
							),
							false,
							array("HIDE_ICONS" => "Y")
						);
						
						?>:&nbsp;<a href="<?=$CurPost["urlToPost"]?>"><?=$CurPost["TITLE"]?></a></b></span>
					</td>
				</tr>
				</table>
			</th>
		</tr>
		<tr>
			<td>
				<span class="blog-text"><?=$CurPost["TEXT_FORMATED"]?></span><?
				if ($CurPost["CUT"] == "Y")
				{
					?><br /><br /><div align="left" class="blog-post-date"><a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_MORE")?></a></div><?
				}
				?>
				<table width="100%" cellspacing="0" cellpadding="0" border="0" class="blog-table-post-table">
				<tr>
					<td colspan="2"><div class="blog-line"></div></td>
				</tr>
				<tr>
					<td align="left">						
						<?
						if(!empty($CurPost["CATEGORY"]))
						{
							echo GetMessage("BLOG_BLOG_BLOG_CATEGORY");
							$i=0;
							foreach($CurPost["CATEGORY"] as $v)
							{
								if($i!=0)
									echo ",";
								?> <a href="<?=$v["urlToCategory"]?>"><?=$v["NAME"]?></a><?
								$i++;
							}
						}
						?></td>
					<td align="right" nowrap><a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_PERMALINK")?></a>&nbsp;|&nbsp;
					<?if($arResult["enable_trackback"] == "Y" && $CurPost["ENABLE_TRACKBACK"]=="Y"):?>
						<a href="<?=$CurPost["urlToPost"]?>#trackback">Trackbacks: <?=$CurPost["NUM_TRACKBACKS"];?></a>&nbsp;|&nbsp;
					<?endif;?>
					<a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?> <?=IntVal($CurPost["VIEWS"]);?></a>&nbsp;|&nbsp;
					<a href="<?=$CurPost["urlToPost"]?>#comment"><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?> <?=$CurPost["NUM_COMMENTS"];?></a></td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
		<br />
-->		
		<?
	}
	if(strlen($arResult["NAV_STRING"])>0)
		echo $arResult["NAV_STRING"];
}
?>	
