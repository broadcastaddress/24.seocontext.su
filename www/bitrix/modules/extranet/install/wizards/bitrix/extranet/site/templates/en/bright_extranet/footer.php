<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

		<?if($APPLICATION->GetCurPage() == "#SITE_DIR#index.php" || $APPLICATION->GetCurPage() == "#SITE_DIR#"):?>

				<div align="center"><br /><?$APPLICATION->IncludeComponent(
					"bitrix:advertising.banner",
					"",
					Array(
						"TYPE" => "468x60_BOTTOM", 
						"CACHE_TYPE" => "A", 
						"CACHE_TIME" => "0" 
					)
				);?></div>

				</div>
		<?else:?>

				<div align="center"><br /><?$APPLICATION->IncludeComponent(
					"bitrix:advertising.banner",
					"",
					Array(
						"TYPE" => "468x60_BOTTOM", 
						"CACHE_TYPE" => "A", 
						"CACHE_TIME" => "0" 
					)
				);?></div>

				</td>
			</tr>
		</table>
		<?endif?>

	</div>

	<div id="space-for-footer"></div>
</div>

<div id="footer">

	<div id="copyright">Copyright &copy; 2001-2011 Bitrix, Inc., Powered by Bitrix Intranet</div>

	<div id="footer-links">
		<a href="#SITE_DIR#about/contacts.php">Contacts</a>&nbsp;&nbsp;
		<a href="#SITE_DIR#search/">Search</a>&nbsp;&nbsp;
		<a href="#SITE_DIR#search/map.php">Site map</a>
	</div>

</div>

</body>
</html>