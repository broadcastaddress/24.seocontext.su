<?
##############################################
# Bitrix Site Manager WebDav				 #
# Copyright (c) 2002-2010 Bitrix			 #
# http://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################
IncludeModuleLangFile(__FILE__);

class CWebDavStorage
{
	const MIME_GROUP_OFFICE = 'OFFICE';
	const MIME_GROUP_OPEN_OFFICE = 'OPEN_OFFICE';
	const MIME_GROUP_ARCHIVE = 'ARCHIVE';
	const MIME_GROUP_ONLY_LOADING = 'ONLY_LOADING';
	const MIME_GROUP_IMAGE = 'IMAGE';
	
	protected $multiPartSeparator = null;

	static $io = null;
	
	//Получить объект нужного типа (CWebDavIblockStorage, ...)
	static function GetWebDavStorageObj($type, $arParams)
	{
		$typeS = "";
		if(is_string($type))
		{
			$typeS = $type;
		}
		
		if($typeS == CWebDavIblockStorage::TYPE)
		{
			return new CWebDavIblockStorage($arParams);
		}
		return null;
	}

	static function GetIo()
	{
		if(self::$io == null)
		{
			self::$io = CBXVirtualIo::GetInstance();
		}
		return self::$io;
	}

	//Добавить/заменить заголовок $str, $replace = true - заменить если уже существует / false - добавить даже если уже существует
	static function SetHeader($str, $replace=true) // safe from response splitting
	{
		header(str_replace(array("\r", "\n"), "", $str), $replace);
	}

	static function CleanRelativalPathString($filePath, $fullPath = null)
	{
		$io = self::GetIo();
		$filePath = $io->CombinePath("/", $filePath);
		if($fullPath !== null)
		{
			$filePath = $io->CombinePath($fullPath, $filePath);
		}
		if(!$io->ValidatePathString($filePath))
		{
			return false;
		}
		return $filePath;
	}

	static function FromFullToRelatival($filePath, $fullPath)
	{
		$res = $filePath;
		if(strpos($res, $fullPath) === 0)
		{
			$res = substr($res, strlen($fullPath));
		}
		return $res;
	}
	
	
	/********** Mime **********/
	
	static function GetMimeArray()
	{	
		static $arMimes = array(
				
			'html' => array( 'mime' => 'text/html', 'group' => self::MIME_GROUP_ONLY_LOADING ),
			'htm' => array( 'mime' => 'text/html', 'group' => self::MIME_GROUP_ONLY_LOADING ),
			'mht' => array( 'mime' => 'message/rfc822', 'group' => self::MIME_GROUP_ONLY_LOADING ),
			'xhtml' => array( 'mime' => 'application/xhtml+xml', 'group' => self::MIME_GROUP_ONLY_LOADING ),
			'xml' => array( 'mime' => 'application/xml', 'group' => self::MIME_GROUP_ONLY_LOADING ),
			'swf' => array( 'mime' => 'application/x-shockwave-flash', 'group' => self::MIME_GROUP_ONLY_LOADING ),
			'svg' => array( 'mime' => 'image/svg+xml', 'group' => self::MIME_GROUP_ONLY_LOADING ),
			'txt' => array( 'mime' => 'text/plain', 'group' => self::MIME_GROUP_ONLY_LOADING ),
			'pdf' => array( 'mime' => 'application/pdf', 'group' => self::MIME_GROUP_ONLY_LOADING ),
			
			'rar' => array( 'mime' => 'application/x-rar-compressed', 'group' => self::MIME_GROUP_ARCHIVE ),
			'zip' => array( 'mime' => 'application/zip', 'group' => self::MIME_GROUP_ARCHIVE ),
			
			'gif' => array( 'mime' => 'image/gif', 'group' => self::MIME_GROUP_IMAGE ),
			'jpg' => array( 'mime' => 'image/jpeg', 'group' => self::MIME_GROUP_IMAGE ),
			'jpeg' => array( 'mime' => 'image/jpeg', 'group' => self::MIME_GROUP_IMAGE ),
			'jpe' => array( 'mime' => 'image/jpeg', 'group' => self::MIME_GROUP_IMAGE ),
			'bmp' => array( 'mime' => 'image/bmp', 'group' => self::MIME_GROUP_IMAGE ),
			'png' => array( 'mime' => 'image/png', 'group' => self::MIME_GROUP_IMAGE ),
			
			'doc' => array( 'mime' => 'application/msword', 'group' => self::MIME_GROUP_OFFICE ),
			'docm' => array( 'mime' => 'application/vnd.ms-word.document.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ), 
			'docx' => array( 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'group' => self::MIME_GROUP_OFFICE ), 
			'dotm' => array( 'mime' => 'application/vnd.ms-word.template.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ),
			'dotx' => array( 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'group' => self::MIME_GROUP_OFFICE ),
			'ppt' => array( 'mime' => 'application/powerpoint', 'group' => self::MIME_GROUP_OFFICE ),
			'potm' => array( 'mime' => 'application/vnd.ms-powerpoint.template.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ),
			'potx' => array( 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.template', 'group' => self::MIME_GROUP_OFFICE ),
			'ppam' => array( 'mime' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ),
			'ppsm' => array( 'mime' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ),
			'ppsx' => array( 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'group' => self::MIME_GROUP_OFFICE ),
			'pptm' => array( 'mime' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ),
			'pptx' => array( 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'group' => self::MIME_GROUP_OFFICE ),
			'xls' => array( 'mime' => 'application/vnd.ms-excel', 'group' => self::MIME_GROUP_OFFICE ),
			'xlam' => array( 'mime' => 'application/vnd.ms-excel.addin.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ),
			'xlsb' => array( 'mime' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ),
			'xlsm' => array( 'mime' => 'application/vnd.ms-excel.sheet.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ),
			'xlsx' => array( 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'group' => self::MIME_GROUP_OFFICE ),
			'xltm' => array( 'mime' => 'application/vnd.ms-excel.template.macroEnabled.12', 'group' => self::MIME_GROUP_OFFICE ),
			'xltx' => array( 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template', 'group' => self::MIME_GROUP_OFFICE ),
			
			'csv' => array( 'mime' => 'application/vnd.ms-excel', 'group' => self::MIME_GROUP_OFFICE ),
			
			'odt' => array( 'mime' => 'application/vnd.oasis.opendocument.text', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'ott' => array( 'mime' => 'application/vnd.oasis.opendocument.text-template', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'odg' => array( 'mime' => 'application/vnd.oasis.opendocument.graphics', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'otg' => array( 'mime' => 'application/vnd.oasis.opendocument.graphics-template', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'odp' => array( 'mime' => 'application/vnd.oasis.opendocument.presentation', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'otp' => array( 'mime' => 'application/vnd.oasis.opendocument.presentation-template', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'ods' => array( 'mime' => 'application/vnd.oasis.opendocument.spreadsheet', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'ots' => array( 'mime' => 'application/vnd.oasis.opendocument.spreadsheet-template', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'odc' => array( 'mime' => 'application/vnd.oasis.opendocument.chart', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'otc' => array( 'mime' => 'application/vnd.oasis.opendocument.chart-template', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'odi' => array( 'mime' => 'application/vnd.oasis.opendocument.image', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'oti' => array( 'mime' => 'application/vnd.oasis.opendocument.image-template', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'odf' => array( 'mime' => 'application/vnd.oasis.opendocument.formula', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'otf' => array( 'mime' => 'application/vnd.oasis.opendocument.formula-template', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'odm' => array( 'mime' => 'application/vnd.oasis.opendocument.text-master', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			'oth' => array( 'mime' => 'application/vnd.oasis.opendocument.text-web', 'group' => self::MIME_GROUP_OPEN_OFFICE ),
			
		);
		
		return $arMimes;
	}
	
	static function GetMimeAndGroup($fullPath)
	{
		$arM = self::GetMimeArray();
		$fExtQ = strtolower(GetFileExtension($fullPath));

		if(array_key_exists($fExtQ, $arM))
		{
			if($arM[$fExtQ]["group"] == self::MIME_GROUP_IMAGE)
			{
				$arF = CFile::MakeFileArray($fullPath);
				$res = CFile::CheckImageFile($arF);
				if(strlen($res) <= 0)
				{
					return $arM[$fExtQ];
				}
			}
			else
			{
				return $arM[$fExtQ];
			}
		}
		return array( 'mime' => 'application/octet-stream', 'group' => self::MIME_GROUP_ONLY_LOADING );
	}
	
	//Возвращает true если файл надо(по сообржениям безопасности) скачать или fals если файл можно открыть
	static function CanViewFile($path, $pathIsShort = true)
	{
		if($pathIsShort)
		{
			$path = $_SERVER["DOCUMENT_ROOT"] . $path;
		}
		$arM = self::GetMimeAndGroup($path);
		$arView = array(self::MIME_GROUP_OFFICE, self::MIME_GROUP_IMAGE);
		if(in_array($arM["group"], $arView))
		{
			return true;
		}
		return false;
	}
	
	/********** Mime End **********/
	
	
	/********** MultiPart/ByteRanges **********/
	
	function SetMultiPartByteRangesHeader()
	{
		$this->multiPartSeparator = "SEPARATOR_".md5(microtime());
		self::set_header("Content-type: multipart/byteranges; boundary=" . $this->multiPartSeparator);
	}
	
	function GetMultiPartByteRangesPartBegin($mimetype, $from, $to, $total = false)
	{
		$t ="\n--{" . $this->multiPartSeparator . "}\n" .
			"Content-type: $mimetype\n" . 
			"Content-range: $from-$to/". ($total === false ? "*" : $total) . 
			"\n\n";
		return $t;
	}
	
	function GetMultiPartByteRangesEnd()
	{
		return "\n--{" . $this->multiPartSeparator . "}--";
	}
		
	/********** MultiPart/ByteRanges End **********/

	
}