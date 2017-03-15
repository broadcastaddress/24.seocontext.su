<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($_POST["mode"] == "upload") {
	$arResult = array();
	if (check_bitrix_sessid())
	{
		$count = sizeof($_FILES["task-attachments"]["name"]);
		for($i = 0; $i < $count; $i++)
		{
			$arFile = array(
				"name" => $_FILES["task-attachments"]["name"][$i],
				"size" => $_FILES["task-attachments"]["size"][$i],
				"tmp_name" => $_FILES["task-attachments"]["tmp_name"][$i],
				"type" => $_FILES["task-attachments"]["type"][$i],
				"MODULE_ID" => "tasks"
			);
			$fileID = CFile::SaveFile($arFile, "tasks");
			$tmp = array(
				"name" => $_FILES["task-attachments"]["name"][$i],
				"fileID" => $fileID
			);
			if ($fileID)
			{
				if (!isset($_SESSION["TASKS_UPLOADED_FILES"]))
				{
					$_SESSION["TASKS_UPLOADED_FILES"] = array($fileID);
				}
				else
				{
					$_SESSION["TASKS_UPLOADED_FILES"][] = $fileID;
				}
				$file = CFile::GetFileArray($fileID);
				if ($file)
				{
					$tmp["fileULR"] = $file["SRC"];
				}
			}
			$arResult[] = $tmp;
		}
	}
	$APPLICATION->RestartBuffer();
	Header('Content-Type: text/html; charset='.LANG_CHARSET);
?>
	<script type="text/javascript">
		window.parent.window.taskManagerForm._filesUploaded(<?php echo CUtil::PhpToJsObject($arResult);?>, <?php echo intval($_POST["uniqueID"])?>);
	</script>
<?php
}
elseif ($_POST["mode"] == "delete")
{
	if (check_bitrix_sessid())
	{
		if (isset($_SESSION["TASKS_UPLOADED_FILES"]) && in_array(intval($_POST["fileID"]), $_SESSION["TASKS_UPLOADED_FILES"]))
		{
			CFile::Delete(intval($_POST["fileID"]));
			$key = array_search(intval($_POST["fileID"]), $_SESSION["TASKS_UPLOADED_FILES"]);
			unset($_SESSION["TASKS_UPLOADED_FILES"][$key]);
		}
	}
}
?>
<?php die();?>