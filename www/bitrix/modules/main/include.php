<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen("/include.php"))."/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/start.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_io.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_file.php");


$application = \Bitrix\Main\Application::getInstance();
$application->initializeExtendedKernel(array(
	"get" => $_GET,
	"post" => $_POST,
	"files" => $_FILES,
	"cookie" => $_COOKIE,
	"server" => $_SERVER,
	"env" => $_ENV
));

//define global application object
$GLOBALS["APPLICATION"] = new CMain;

if(defined("SITE_ID"))
	define("LANG", SITE_ID);

if(defined("LANG"))
{
	if(defined("ADMIN_SECTION") && ADMIN_SECTION===true)
		$db_lang = CLangAdmin::GetByID(LANG);
	else
		$db_lang = CLang::GetByID(LANG);

	$arLang = $db_lang->Fetch();

	if(!$arLang)
	{
		throw new \Bitrix\Main\SystemException("Incorrect site: ".LANG.".");
	}
}
else
{
	$arLang = $GLOBALS["APPLICATION"]->GetLang();
	define("LANG", $arLang["LID"]);
}

$lang = $arLang["LID"];
if (!defined("SITE_ID"))
	define("SITE_ID", $arLang["LID"]);
define("SITE_DIR", $arLang["DIR"]);
define("SITE_SERVER_NAME", $arLang["SERVER_NAME"]);
define("SITE_CHARSET", $arLang["CHARSET"]);
define("FORMAT_DATE", $arLang["FORMAT_DATE"]);
define("FORMAT_DATETIME", $arLang["FORMAT_DATETIME"]);
define("LANG_DIR", $arLang["DIR"]);
define("LANG_CHARSET", $arLang["CHARSET"]);
define("LANG_ADMIN_LID", $arLang["LANGUAGE_ID"]);
define("LANGUAGE_ID", $arLang["LANGUAGE_ID"]);

$context = $application->getContext();
$context->setLanguage(LANGUAGE_ID);
$context->setCulture(new \Bitrix\Main\Context\Culture($arLang));

$request = $context->getRequest();
if (!$request->isAdminSection())
{
	$context->setSite(SITE_ID);
}

$application->start();

$GLOBALS["APPLICATION"]->reinitPath();

if (!defined("POST_FORM_ACTION_URI"))
{
	define("POST_FORM_ACTION_URI", htmlspecialcharsbx(GetRequestUri()));
}

$GLOBALS["MESS"] = array();
$GLOBALS["ALL_LANG_FILES"] = array();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/tools.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/database.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/main.php");
IncludeModuleLangFile(__FILE__);

error_reporting(COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE) & ~E_STRICT & ~E_DEPRECATED);

if(!defined("BX_COMP_MANAGED_CACHE") && COption::GetOptionString("main", "component_managed_cache_on", "Y") <> "N")
{
	define("BX_COMP_MANAGED_CACHE", true);
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/filter_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/ajax_tools.php");

/*ZDUyZmZNjY0ZDFmZjE5NDllMWM3MzM2MjkyZWJiNWZjMjM0Njc=*/$GLOBALS['_____45831982']= array(base64_decode('R2V0T'.'W9kdW'.'xlR'.'XZl'.'bnRz'),base64_decode('RXhlY'.'3V0Z'.'U1v'.'ZH'.'Vs'.'Z'.'UV2Z'.'W50RXg='));; $GLOBALS['____611136115']= array(base64_decode('ZGV'.'maW'.'5l'),base64_decode('c'.'3RybG'.'Vu'),base64_decode(''.'YmF'.'zZTY0'.'X'.'2RlY29kZ'.'Q=='),base64_decode(''.'dW5zZX'.'JpYWxpem'.'U'.'='),base64_decode('a'.'X'.'NfYX'.'JyYXk='),base64_decode('Y29'.'1bn'.'Q='),base64_decode('aW5fYXJyYXk='),base64_decode('c2VyaWFsaXpl'),base64_decode('YmFzZ'.'TY0X2VuY'.'29kZQ=='),base64_decode('c3RybGVu'),base64_decode('YXJy'.'YXlfa2V5X2V4'.'aXN0'.'cw='.'='),base64_decode('Y'.'XJy'.'YXlfa'.'2V5X'.'2V4aX'.'N0cw=='),base64_decode(''.'bWt'.'0aW1l'),base64_decode(''.'ZGF0'.'ZQ=='),base64_decode('ZGF0ZQ=='),base64_decode('Y'.'XJy'.'YXlfa2V5'.'X2V4aXN0cw'.'='.'='),base64_decode('c3RybGVu'),base64_decode(''.'YX'.'JyYXlfa2V5X2V4'.'aX'.'N0c'.'w'.'=='),base64_decode('c3RybGVu'),base64_decode('YX'.'J'.'yYXlfa'.'2V5X2V'.'4a'.'XN0cw'.'=='),base64_decode('YXJyYXlfa'.'2V5X2V4aXN0'.'c'.'w='.'='),base64_decode('bWt'.'0'.'a'.'W1l'),base64_decode('ZG'.'F0ZQ=='),base64_decode('ZGF0'.'ZQ=='),base64_decode('bW'.'V0a'.'G9'.'kX2'.'V4'.'aXN0cw=='),base64_decode(''.'Y'.'2FsbF9'.'1c2VyX2'.'Z1'.'bmN'.'f'.'YXJy'.'YX'.'k='),base64_decode('c'.'3RybGVu'),base64_decode('YXJ'.'y'.'YX'.'l'.'fa2V5X2V4'.'aXN0cw=='),base64_decode(''.'YXJyY'.'Xlfa2V5X2V'.'4aXN0cw=='),base64_decode(''.'c'.'2VyaWFsa'.'Xp'.'l'),base64_decode('Y'.'m'.'FzZTY0X'.'2VuY29'.'k'.'ZQ=='),base64_decode('c3R'.'ybGVu'),base64_decode(''.'Y'.'XJyYX'.'lfa2V5X2V'.'4aXN0cw='.'='),base64_decode('Y'.'XJyY'.'Xlfa2V5X2V4aX'.'N'.'0'.'cw=='),base64_decode(''.'Y'.'XJyYX'.'lfa2'.'V5X2V'.'4aXN0cw=='),base64_decode(''.'aX'.'NfYX'.'JyY'.'Xk'.'='),base64_decode('YXJyYXlfa2V'.'5X2V4a'.'XN0cw=='),base64_decode('c'.'2Vy'.'aW'.'Fs'.'a'.'Xpl'),base64_decode('YmFz'.'ZTY0X2V'.'uY29kZQ=='),base64_decode('YXJy'.'YXlfa2V'.'5X2V4aX'.'N0c'.'w'.'='.'='),base64_decode('YXJyYX'.'lf'.'a2'.'V5'.'X'.'2V4'.'aXN0cw='.'='),base64_decode(''.'c'.'2VyaWFsaXpl'),base64_decode('YmFzZT'.'Y0X2'.'VuY29kZQ'.'=='),base64_decode(''.'aXN'.'fY'.'XJ'.'y'.'YXk='),base64_decode('a'.'XN'.'fY'.'XJyYX'.'k='),base64_decode('a'.'W5'.'fYX'.'JyYXk'.'='),base64_decode(''.'YXJyYXlfa'.'2'.'V5X'.'2V4aXN0cw=='),base64_decode('aW'.'5fY'.'X'.'JyYXk='),base64_decode('bWt0aW'.'1l'),base64_decode('ZGF0'.'ZQ=='),base64_decode('ZGF0ZQ=='),base64_decode('ZGF0ZQ'.'=='),base64_decode(''.'bWt0aW1l'),base64_decode(''.'ZGF0ZQ=='),base64_decode('ZG'.'F'.'0'.'Z'.'Q='.'='),base64_decode(''.'aW5'.'fYXJy'.'YXk'.'='),base64_decode('Y'.'XJyYXlfa2V5'.'X2V'.'4aXN0c'.'w=='),base64_decode('YXJ'.'y'.'YX'.'l'.'fa2'.'V5X'.'2V4'.'a'.'XN0cw=='),base64_decode('c'.'2Vya'.'WFs'.'aXpl'),base64_decode('YmFzZTY0X2VuY29kZ'.'Q=='),base64_decode(''.'YXJyY'.'X'.'l'.'fa2V5X'.'2'.'V4aX'.'N0c'.'w=='),base64_decode('aW5'.'0'.'dmFs'),base64_decode('dG'.'ltZ'.'Q=='),base64_decode('YXJyYX'.'lf'.'a'.'2V5'.'X'.'2V'.'4'.'aXN0c'.'w='.'='),base64_decode('ZmlsZV9leGl'.'z'.'d'.'H'.'M='),base64_decode('c'.'3'.'R'.'yX'.'3JlcGx'.'hY2U='),base64_decode('Y2x'.'h'.'c3NfZXhpc3Rz'),base64_decode(''.'ZGV'.'m'.'aW'.'5l'));; function ___1604330162($_1023058352){static $_1615816014= false; if($_1615816014 == false) $_1615816014=array('SU5UU'.'kFOR'.'VRfRURJV'.'ElP'.'T'.'g==','W'.'Q==',''.'bWFpbg==','fm'.'NwZl9tYXB'.'fdm'.'F'.'sdWU=','','ZQ==','Zg==',''.'ZQ==','Rg==','WA'.'='.'=','Z'.'g='.'=','bWF'.'pbg==',''.'fmNwZl9tYXBfdmFsdWU=','UG9ydGFs','Rg==','Z'.'Q==',''.'ZQ==','WA='.'=',''.'Rg==','RA==',''.'RA='.'=','bQ'.'==','ZA==','WQ==','Zg==',''.'Zg==','Zg'.'='.'=',''.'Zg==','U'.'G'.'9ydGFs','Rg'.'==','ZQ='.'=','Z'.'Q==','W'.'A==',''.'Rg==','RA'.'==','RA==','bQ==','ZA==','WQ==','bWF'.'pb'.'g='.'=','T24=','U2V0dGluZ3ND'.'aGFuZ2U=',''.'Zg='.'=','Zg'.'==','Zg'.'==','Z'.'g==','bWFpb'.'g==',''.'fmNw'.'Zl9tYXBf'.'dmFsd'.'WU=','ZQ==',''.'ZQ==','ZQ==','RA'.'==',''.'ZQ==','ZQ==','Zg'.'='.'=','Zg==','Zg='.'=','ZQ==','bWFp'.'bg='.'=','f'.'m'.'NwZl9t'.'YX'.'Bfdm'.'FsdWU=',''.'ZQ==','Zg==','Zg='.'=','Zg='.'=','Zg'.'==','bWFpbg==','fmNwZl'.'9tY'.'XBfdm'.'Fsd'.'WU=',''.'ZQ='.'=','Zg==','UG9ydGFs',''.'UG9ydG'.'Fs','Z'.'Q'.'='.'=','ZQ='.'=','UG9ydGFs',''.'R'.'g'.'==','WA==','Rg='.'=','R'.'A'.'='.'=','ZQ'.'==','ZQ==','RA'.'==','bQ'.'==','ZA==','WQ==','ZQ'.'==',''.'WA='.'=','ZQ==',''.'Rg'.'==','ZQ==','RA'.'==','Zg==','ZQ='.'=','RA==','ZQ='.'=',''.'b'.'Q'.'==','ZA==','W'.'Q='.'=','Zg==','Zg'.'==','Zg==','Zg==',''.'Zg==','Zg='.'=','Zg==','Zg==','b'.'WFpbg==','fmN'.'w'.'Z'.'l9tYXBfdm'.'FsdW'.'U'.'=',''.'ZQ==','ZQ==','UG9yd'.'GFs',''.'Rg'.'='.'=','WA='.'=','VFlQ'.'RQ==','RE'.'FURQ'.'==',''.'RkVBVFVSR'.'VM'.'=','RVhQSVJFRA==','V'.'FlQRQ==','RA==','VFJZX0RBWVNfQ09'.'VTlQ=','REFURQ==',''.'VFJZX0RB'.'WVN'.'fQ09VTlQ=',''.'R'.'VhQSVJFRA'.'==','R'.'k'.'VBVFVSRVM=','Z'.'g='.'=',''.'Zg='.'=',''.'RE9DVU1F'.'TlRfUk9PVA==','L2J'.'pdHJpeC9'.'t'.'b'.'2R'.'1bG'.'V'.'zLw'.'==','L'.'2'.'luc'.'3RhbG'.'wv'.'a'.'W5kZXgucGh'.'w','Lg==','Xw='.'=','c2'.'Vh'.'cmNo','Tg==','','',''.'QUNUSVZF','WQ='.'=','c'.'29j'.'aWFsb'.'mV0'.'d'.'29yaw==','Y'.'Wxsb3d'.'fZ'.'nJpZWxkcw==','WQ'.'==','S'.'U'.'Q'.'=','c2'.'9'.'jaW'.'FsbmV0d29yaw'.'==','YWxs'.'b3'.'dfZnJp'.'ZWxkcw==',''.'SUQ=','c29jaWF'.'sb'.'mV0d'.'29yaw'.'==','YW'.'xs'.'b3'.'d'.'f'.'Zn'.'J'.'pZWxkcw='.'=','Tg==','','','QUNUS'.'V'.'ZF','W'.'Q==','c2'.'9ja'.'WFsbm'.'V0d2'.'9ya'.'w==','YWxsb3df'.'b'.'Wljcm9ibG'.'9'.'nX3V'.'zZXI=','WQ='.'=','SUQ=','c29'.'jaWFsb'.'mV0d2'.'9yaw==','YWxsb3dfbWl'.'jcm9ibG9nX3VzZX'.'I=','SUQ=','c29jaW'.'FsbmV0d'.'29yaw==','YWx'.'sb'.'3dfb'.'Wljcm9i'.'bG9n'.'X3'.'VzZXI'.'=','c'.'29'.'j'.'aW'.'Fs'.'bmV'.'0d2'.'9y'.'aw='.'=','Y'.'W'.'xsb'.'3dfb'.'Wl'.'jcm9ib'.'G9nX2dyb3V'.'w','WQ==','SUQ=',''.'c29jaWFsbmV0'.'d'.'2'.'9'.'y'.'aw==','YWxs'.'b3df'.'b'.'Wljcm9'.'ibG9nX2dy'.'b3Vw','S'.'U'.'Q=','c29jaW'.'FsbmV'.'0d2'.'9'.'yaw==','YWxsb'.'3dfb'.'Wljcm'.'9ibG9nX2dyb'.'3'.'V'.'w',''.'Tg==','','','QUNU'.'SVZ'.'F','WQ==','c29ja'.'WFsbmV0'.'d29y'.'aw='.'=','YW'.'x'.'sb3dfZ'.'ml'.'sZXNf'.'dXNlcg==','WQ='.'=','SUQ'.'=','c2'.'9jaWFsb'.'mV0d29y'.'aw'.'==','Y'.'Wxs'.'b3d'.'fZmls'.'ZX'.'N'.'fdXNlc'.'g==','SUQ=','c29ja'.'W'.'FsbmV0d29yaw==','YWxsb'.'3dfZmlsZX'.'N'.'fdXNl'.'cg==','T'.'g==','','','QUNU'.'S'.'VZ'.'F',''.'WQ==','c29'.'jaWFsbmV0d29yaw==','YWxsb3d'.'f'.'YmxvZ191'.'c2Vy','WQ'.'==','SU'.'Q=','c29j'.'a'.'WFsb'.'mV0'.'d29'.'ya'.'w==','YW'.'xsb3dfYmxvZ191c'.'2Vy','SUQ=','c29jaWFsb'.'mV'.'0d2'.'9yaw'.'==','YWxsb3d'.'fYmxvZ19'.'1'.'c'.'2Vy','Tg==','','','QUNUSVZF','WQ==','c29ja'.'WFsb'.'mV0d29yaw='.'=','YWxsb3'.'dfcG'.'h'.'vdG9'.'fdXNlcg==','WQ==',''.'SUQ'.'=','c29j'.'aWFs'.'bmV0d29y'.'a'.'w==','YWxs'.'b3df'.'cGhvdG'.'9fdXNlcg==','S'.'UQ=','c'.'29j'.'aW'.'Fsbm'.'V0'.'d'.'29yaw==',''.'YWxsb3dfc'.'Ghv'.'dG9f'.'dX'.'Nlcg==','Tg='.'=','','','Q'.'UNUSVZF',''.'WQ==','c29jaWFsbmV0d29ya'.'w==','YWxsb3'.'dfZm9'.'ydW'.'1fdXNlcg==',''.'W'.'Q==','SU'.'Q=','c2'.'9j'.'aW'.'Fsb'.'mV'.'0d'.'2'.'9yaw='.'=',''.'YWxsb3df'.'Z'.'m9ydW1'.'fdXNlcg==',''.'SUQ=','c'.'29ja'.'WFs'.'bmV0d29yaw==','YWx'.'sb3dfZm9y'.'dW1fdXNlcg==','T'.'g='.'=','','','QUNUSVZF','WQ==','c29j'.'a'.'WFsb'.'mV0d29yaw==','Y'.'Wxs'.'b3d'.'f'.'dGFza3Nf'.'dXNlcg'.'==','WQ==',''.'S'.'U'.'Q=','c29jaW'.'FsbmV0d29'.'y'.'aw==','YWx'.'s'.'b3dfdGFza3Nf'.'dXN'.'l'.'c'.'g==','SU'.'Q=','c29jaWFs'.'bmV0'.'d29'.'yaw==','YW'.'xsb'.'3dfdGFz'.'a3NfdXNl'.'c'.'g==','c2'.'9'.'jaWF'.'sbmV'.'0d'.'2'.'9'.'y'.'a'.'w==','YWxsb3d'.'fdGFza3NfZ3Jv'.'dXA=',''.'WQ==','SUQ=','c29jaWF'.'sbmV0d2'.'9yaw==','YWxsb3d'.'fdG'.'Fza'.'3'.'NfZ3J'.'vdXA=','SUQ=','c'.'29jaWF'.'sb'.'m'.'V'.'0d'.'29yaw='.'=','YWxsb'.'3dfd'.'GFza3NfZ'.'3JvdX'.'A=',''.'dGFza3M=','Tg==','','','QUNUSVZF','WQ==',''.'c29jaWFsbmV0d2'.'9ya'.'w==','Y'.'Wxsb3dfY2FsZW5'.'kY'.'X'.'JfdX'.'Nlcg==',''.'WQ==','SUQ'.'=','c'.'29jaW'.'Fs'.'bm'.'V0d29yaw==','YWxsb'.'3d'.'fY2FsZ'.'W'.'5k'.'YX'.'JfdXNlcg'.'==','S'.'UQ=','c29ja'.'W'.'F'.'sbmV0d2'.'9yaw='.'=','Y'.'Wxs'.'b3dfY2FsZ'.'W5kYXJfdXNlcg==','c29jaWFs'.'b'.'mV0d29yaw'.'==','Y'.'Wxsb3'.'dfY'.'2FsZW5kYXJ'.'fZ3JvdXA'.'=','WQ='.'=','S'.'UQ=','c2'.'9'.'jaWFs'.'b'.'mV0d'.'29yaw==','YW'.'xsb3dfY2'.'F'.'sZW5kY'.'XJ'.'fZ3JvdXA=','SUQ=','c29jaW'.'F'.'sb'.'mV0d29y'.'aw==','YWxsb3'.'dfY'.'2F'.'sZ'.'W5kYXJf'.'Z3Jvd'.'XA=','Y2'.'FsZW5'.'kY'.'X'.'I'.'=','QUN'.'USVZF',''.'WQ'.'==','Tg==',''.'ZXh0c'.'mFu'.'ZXQ=','aWJ'.'sb2Nr','T2'.'5'.'BZnRlcklCb'.'G9ja0'.'VsZW1lb'.'nRVcGRh'.'dGU=','aW50'.'cmFuZXQ=','Q'.'0'.'ludH'.'Jhb'.'mV'.'0R'.'XZlbnRIYW'.'5kbGVyc'.'w==',''.'U1'.'BSZWdpc3'.'Rlcl'.'VwZ'.'GF0ZWRJ'.'dGV'.'t','Q'.'0'.'lud'.'HJ'.'hbmV0U2'.'h'.'hcmVwb2lu'.'dDo'.'6QWdlb'.'nRMaXN0c'.'y'.'gpOw='.'=',''.'a'.'W5'.'0cmFuZXQ=','Tg==','Q0'.'ludHJh'.'b'.'mV'.'0U2hhc'.'mVwb'.'2ludDo6QWdlbnRRdWV1ZSgpOw==',''.'aW50cmFuZX'.'Q=','Tg='.'=','Q0lud'.'HJhbmV'.'0U2'.'hh'.'cm'.'Vwb2l'.'ud'.'Do6QW'.'d'.'lb'.'n'.'RVcG'.'RhdGUoKTs=','aW50cmFuZXQ=','Tg==','aW'.'Jsb'.'2Nr','T2'.'5BZnR'.'lc'.'klCb'.'G'.'9'.'ja0'.'Vs'.'ZW1'.'l'.'bn'.'RBZ'.'GQ=','aW'.'50cmFuZXQ=','Q0'.'ludHJh'.'b'.'mV0'.'RX'.'Z'.'lbnRIY'.'W5k'.'bG'.'Vy'.'cw==','U1BSZWdpc3'.'Rlc'.'l'.'V'.'wZG'.'F'.'0ZWRJdGVt','a'.'WJsb2'.'Nr','T25BZnRlckl'.'C'.'b'.'G9j'.'a0VsZW1lbnRVcGRhdGU'.'=','a'.'W5'.'0cm'.'FuZXQ=','Q0ludH'.'JhbmV'.'0RX'.'Zlb'.'nRIYW5kbGVycw'.'==','U'.'1BSZWdpc3Rlc'.'lV'.'wZGF0ZWRJ'.'d'.'GVt','Q'.'0'.'ludHJhbmV'.'0'.'U2'.'hhcmVwb2ludDo6Q'.'W'.'dlbnRMaXN0'.'cygpOw==',''.'a'.'W50cmFuZ'.'XQ=','Q0ludHJhbmV0'.'U2hhcmVwb'.'2l'.'ud'.'Do6QWdlb'.'n'.'RRdW'.'V1ZS'.'gp'.'Ow='.'=','aW5'.'0cmFuZXQ=','Q0lud'.'H'.'Jh'.'bmV0U2hhcmV'.'w'.'b2lu'.'dDo'.'6Q'.'W'.'dlbn'.'RVcGRhdGUoK'.'Ts=','aW50cm'.'FuZX'.'Q=','Y3'.'Jt','bWFpbg'.'==',''.'T'.'2'.'5CZWZv'.'c'.'mVQcm9sb2'.'c=','bWFpbg'.'==','Q1d'.'pemFyZ'.'FNvbFB'.'h'.'bmVs'.'SW50cmFuZX'.'Q=','U2h'.'vd1Bhb'.'mVs','L21vZHVsZX'.'Mv'.'a'.'W50'.'cm'.'Fu'.'ZXQvcGFuZWxfYnV0dG9uLnBo'.'cA==','RU5DT0RF','WQ'.'==');return base64_decode($_1615816014[$_1023058352]);}; $GLOBALS['____611136115'][0](___1604330162(0), ___1604330162(1)); class CBXFeatures{ private static $_741916849= 30; private static $_195367000= array( "Portal" => array( "CompanyCalendar", "CompanyPhoto", "CompanyVideo", "CompanyCareer", "StaffChanges", "StaffAbsence", "CommonDocuments", "MeetingRoomBookingSystem", "Wiki", "Learning", "Vote", "WebLink", "Subscribe", "Friends", "PersonalFiles", "PersonalBlog", "PersonalPhoto", "PersonalForum", "Blog", "Forum", "Gallery", "Board", "MicroBlog", "WebMessenger",), "Communications" => array( "Tasks", "Calendar", "Workgroups", "Jabber", "VideoConference", "Extranet", "SMTP", "Requests", "DAV", "intranet_sharepoint", "timeman", "Idea", "Meeting", "EventList", "Salary", "XDImport",), "Enterprise" => array( "BizProc", "Lists", "Support", "Analytics", "crm", "Controller",), "Holding" => array( "Cluster", "MultiSites",),); private static $_165692405= false; private static $_890533893= false; private static function Initialize(){ if(self::$_165692405 == false){ self::$_165692405= array(); foreach(self::$_195367000 as $_2009671592 => $_1574823244){ foreach($_1574823244 as $_1473692294) self::$_165692405[$_1473692294]= $_2009671592;}} if(self::$_890533893 == false){ self::$_890533893= array(); $_106745102= COption::GetOptionString(___1604330162(2), ___1604330162(3), ___1604330162(4)); if($GLOBALS['____611136115'][1]($_106745102)> min(72,0,24)){ $_106745102= $GLOBALS['____611136115'][2]($_106745102); self::$_890533893= $GLOBALS['____611136115'][3]($_106745102); if(!$GLOBALS['____611136115'][4](self::$_890533893)) self::$_890533893= array();} if($GLOBALS['____611136115'][5](self::$_890533893) <=(1020/2-510)) self::$_890533893= array(___1604330162(5) => array(), ___1604330162(6) => array());}} public static function InitiateEditionsSettings($_1293021137){ self::Initialize(); $_1545920924= array(); foreach(self::$_195367000 as $_2009671592 => $_1574823244){ $_649620778= $GLOBALS['____611136115'][6]($_2009671592, $_1293021137); self::$_890533893[___1604330162(7)][$_2009671592]=($_649620778? array(___1604330162(8)): array(___1604330162(9))); foreach($_1574823244 as $_1473692294){ self::$_890533893[___1604330162(10)][$_1473692294]= $_649620778; if(!$_649620778) $_1545920924[]= array($_1473692294, false);}} $_1286488250= $GLOBALS['____611136115'][7](self::$_890533893); $_1286488250= $GLOBALS['____611136115'][8]($_1286488250); COption::SetOptionString(___1604330162(11), ___1604330162(12), $_1286488250); foreach($_1545920924 as $_1707760861) self::ExecuteEvent($_1707760861[min(114,0,38)], $_1707760861[round(0+0.25+0.25+0.25+0.25)]);} public static function IsFeatureEnabled($_1473692294){ if($GLOBALS['____611136115'][9]($_1473692294) <= 0) return true; self::Initialize(); if(!$GLOBALS['____611136115'][10]($_1473692294, self::$_165692405)) return true; if(self::$_165692405[$_1473692294] == ___1604330162(13)) $_1274877158= array(___1604330162(14)); elseif($GLOBALS['____611136115'][11](self::$_165692405[$_1473692294], self::$_890533893[___1604330162(15)])) $_1274877158= self::$_890533893[___1604330162(16)][self::$_165692405[$_1473692294]]; else $_1274877158= array(___1604330162(17)); if($_1274877158[(1452/2-726)] != ___1604330162(18) && $_1274877158[min(50,0,16.666666666667)] != ___1604330162(19)){ return false;} elseif($_1274877158[(1040/2-520)] == ___1604330162(20)){ if($_1274877158[round(0+1)]< $GLOBALS['____611136115'][12]((237*2-474), min(90,0,30), min(60,0,20), Date(___1604330162(21)), $GLOBALS['____611136115'][13](___1604330162(22))- self::$_741916849, $GLOBALS['____611136115'][14](___1604330162(23)))){ if(!isset($_1274877158[round(0+2)]) ||!$_1274877158[round(0+0.4+0.4+0.4+0.4+0.4)]) self::MarkTrialPeriodExpired(self::$_165692405[$_1473692294]); return false;}} return!$GLOBALS['____611136115'][15]($_1473692294, self::$_890533893[___1604330162(24)]) || self::$_890533893[___1604330162(25)][$_1473692294];} public static function IsFeatureInstalled($_1473692294){ if($GLOBALS['____611136115'][16]($_1473692294) <= 0) return true; self::Initialize(); return($GLOBALS['____611136115'][17]($_1473692294, self::$_890533893[___1604330162(26)]) && self::$_890533893[___1604330162(27)][$_1473692294]);} public static function IsFeatureEditable($_1473692294){ if($GLOBALS['____611136115'][18]($_1473692294) <= 0) return true; self::Initialize(); if(!$GLOBALS['____611136115'][19]($_1473692294, self::$_165692405)) return true; if(self::$_165692405[$_1473692294] == ___1604330162(28)) $_1274877158= array(___1604330162(29)); elseif($GLOBALS['____611136115'][20](self::$_165692405[$_1473692294], self::$_890533893[___1604330162(30)])) $_1274877158= self::$_890533893[___1604330162(31)][self::$_165692405[$_1473692294]]; else $_1274877158= array(___1604330162(32)); if($_1274877158[(130*2-260)] != ___1604330162(33) && $_1274877158[(1148/2-574)] != ___1604330162(34)){ return false;} elseif($_1274877158[min(42,0,14)] == ___1604330162(35)){ if($_1274877158[round(0+0.25+0.25+0.25+0.25)]< $GLOBALS['____611136115'][21](min(166,0,55.333333333333), min(232,0,77.333333333333),(792-2*396), Date(___1604330162(36)), $GLOBALS['____611136115'][22](___1604330162(37))- self::$_741916849, $GLOBALS['____611136115'][23](___1604330162(38)))){ if(!isset($_1274877158[round(0+0.5+0.5+0.5+0.5)]) ||!$_1274877158[round(0+0.4+0.4+0.4+0.4+0.4)]) self::MarkTrialPeriodExpired(self::$_165692405[$_1473692294]); return false;}} return true;} private static function ExecuteEvent($_1473692294, $_234567832){ if($GLOBALS['____611136115'][24]("CBXFeatures", "On".$_1473692294."SettingsChange")) $GLOBALS['____611136115'][25](array("CBXFeatures", "On".$_1473692294."SettingsChange"), array($_1473692294, $_234567832)); $_312132179= $GLOBALS['_____45831982'][0](___1604330162(39), ___1604330162(40).$_1473692294.___1604330162(41)); while($_1647124026= $_312132179->Fetch()) $GLOBALS['_____45831982'][1]($_1647124026, array($_1473692294, $_234567832));} public static function SetFeatureEnabled($_1473692294, $_234567832= true, $_624401977= true){ if($GLOBALS['____611136115'][26]($_1473692294) <= 0) return; if(!self::IsFeatureEditable($_1473692294)) $_234567832= false; $_234567832=($_234567832? true: false); self::Initialize(); $_43197061=(!$GLOBALS['____611136115'][27]($_1473692294, self::$_890533893[___1604330162(42)]) && $_234567832 || $GLOBALS['____611136115'][28]($_1473692294, self::$_890533893[___1604330162(43)]) && $_234567832 != self::$_890533893[___1604330162(44)][$_1473692294]); self::$_890533893[___1604330162(45)][$_1473692294]= $_234567832; $_1286488250= $GLOBALS['____611136115'][29](self::$_890533893); $_1286488250= $GLOBALS['____611136115'][30]($_1286488250); COption::SetOptionString(___1604330162(46), ___1604330162(47), $_1286488250); if($_43197061 && $_624401977) self::ExecuteEvent($_1473692294, $_234567832);} private static function MarkTrialPeriodExpired($_2009671592){ if($GLOBALS['____611136115'][31]($_2009671592) <= 0 || $_2009671592 == "Portal") return; self::Initialize(); if(!$GLOBALS['____611136115'][32]($_2009671592, self::$_890533893[___1604330162(48)]) || $GLOBALS['____611136115'][33]($_2009671592, self::$_890533893[___1604330162(49)]) && self::$_890533893[___1604330162(50)][$_2009671592][(924-2*462)] != ___1604330162(51)) return; if(isset(self::$_890533893[___1604330162(52)][$_2009671592][round(0+0.4+0.4+0.4+0.4+0.4)]) && self::$_890533893[___1604330162(53)][$_2009671592][round(0+0.5+0.5+0.5+0.5)]) return; $_1545920924= array(); if($GLOBALS['____611136115'][34]($_2009671592, self::$_195367000) && $GLOBALS['____611136115'][35](self::$_195367000[$_2009671592])){ foreach(self::$_195367000[$_2009671592] as $_1473692294){ if($GLOBALS['____611136115'][36]($_1473692294, self::$_890533893[___1604330162(54)]) && self::$_890533893[___1604330162(55)][$_1473692294]){ self::$_890533893[___1604330162(56)][$_1473692294]= false; $_1545920924[]= array($_1473692294, false);}} self::$_890533893[___1604330162(57)][$_2009671592][round(0+0.5+0.5+0.5+0.5)]= true;} $_1286488250= $GLOBALS['____611136115'][37](self::$_890533893); $_1286488250= $GLOBALS['____611136115'][38]($_1286488250); COption::SetOptionString(___1604330162(58), ___1604330162(59), $_1286488250); foreach($_1545920924 as $_1707760861) self::ExecuteEvent($_1707760861[(150*2-300)], $_1707760861[round(0+0.5+0.5)]);} public static function ModifyFeaturesSettings($_1293021137, $_1574823244){ self::Initialize(); foreach($_1293021137 as $_2009671592 => $_714363040) self::$_890533893[___1604330162(60)][$_2009671592]= $_714363040; $_1545920924= array(); foreach($_1574823244 as $_1473692294 => $_234567832){ if(!$GLOBALS['____611136115'][39]($_1473692294, self::$_890533893[___1604330162(61)]) && $_234567832 || $GLOBALS['____611136115'][40]($_1473692294, self::$_890533893[___1604330162(62)]) && $_234567832 != self::$_890533893[___1604330162(63)][$_1473692294]) $_1545920924[]= array($_1473692294, $_234567832); self::$_890533893[___1604330162(64)][$_1473692294]= $_234567832;} $_1286488250= $GLOBALS['____611136115'][41](self::$_890533893); $_1286488250= $GLOBALS['____611136115'][42]($_1286488250); COption::SetOptionString(___1604330162(65), ___1604330162(66), $_1286488250); self::$_890533893= false; foreach($_1545920924 as $_1707760861) self::ExecuteEvent($_1707760861[(1292/2-646)], $_1707760861[round(0+0.25+0.25+0.25+0.25)]);} public static function SaveFeaturesSettings($_312780903, $_702743948){ self::Initialize(); $_1853537045= array(___1604330162(67) => array(), ___1604330162(68) => array()); if(!$GLOBALS['____611136115'][43]($_312780903)) $_312780903= array(); if(!$GLOBALS['____611136115'][44]($_702743948)) $_702743948= array(); if(!$GLOBALS['____611136115'][45](___1604330162(69), $_312780903)) $_312780903[]= ___1604330162(70); foreach(self::$_195367000 as $_2009671592 => $_1574823244){ if($GLOBALS['____611136115'][46]($_2009671592, self::$_890533893[___1604330162(71)])) $_1266474311= self::$_890533893[___1604330162(72)][$_2009671592]; else $_1266474311=($_2009671592 == ___1604330162(73))? array(___1604330162(74)): array(___1604330162(75)); if($_1266474311[min(4,0,1.3333333333333)] == ___1604330162(76) || $_1266474311[(158*2-316)] == ___1604330162(77)){ $_1853537045[___1604330162(78)][$_2009671592]= $_1266474311;} else{ if($GLOBALS['____611136115'][47]($_2009671592, $_312780903)) $_1853537045[___1604330162(79)][$_2009671592]= array(___1604330162(80), $GLOBALS['____611136115'][48](min(160,0,53.333333333333),(190*2-380),(886-2*443), $GLOBALS['____611136115'][49](___1604330162(81)), $GLOBALS['____611136115'][50](___1604330162(82)), $GLOBALS['____611136115'][51](___1604330162(83)))); else $_1853537045[___1604330162(84)][$_2009671592]= array(___1604330162(85));}} $_1545920924= array(); foreach(self::$_165692405 as $_1473692294 => $_2009671592){ if($_1853537045[___1604330162(86)][$_2009671592][(974-2*487)] != ___1604330162(87) && $_1853537045[___1604330162(88)][$_2009671592][(1072/2-536)] != ___1604330162(89)){ $_1853537045[___1604330162(90)][$_1473692294]= false;} else{ if($_1853537045[___1604330162(91)][$_2009671592][(938-2*469)] == ___1604330162(92) && $_1853537045[___1604330162(93)][$_2009671592][round(0+1)]< $GLOBALS['____611136115'][52]((178*2-356),(228*2-456),(1248/2-624), Date(___1604330162(94)), $GLOBALS['____611136115'][53](___1604330162(95))- self::$_741916849, $GLOBALS['____611136115'][54](___1604330162(96)))) $_1853537045[___1604330162(97)][$_1473692294]= false; else $_1853537045[___1604330162(98)][$_1473692294]= $GLOBALS['____611136115'][55]($_1473692294, $_702743948); if(!$GLOBALS['____611136115'][56]($_1473692294, self::$_890533893[___1604330162(99)]) && $_1853537045[___1604330162(100)][$_1473692294] || $GLOBALS['____611136115'][57]($_1473692294, self::$_890533893[___1604330162(101)]) && $_1853537045[___1604330162(102)][$_1473692294] != self::$_890533893[___1604330162(103)][$_1473692294]) $_1545920924[]= array($_1473692294, $_1853537045[___1604330162(104)][$_1473692294]);}} $_1286488250= $GLOBALS['____611136115'][58]($_1853537045); $_1286488250= $GLOBALS['____611136115'][59]($_1286488250); COption::SetOptionString(___1604330162(105), ___1604330162(106), $_1286488250); self::$_890533893= false; foreach($_1545920924 as $_1707760861) self::ExecuteEvent($_1707760861[(1300/2-650)], $_1707760861[round(0+0.2+0.2+0.2+0.2+0.2)]);} public static function GetFeaturesList(){ self::Initialize(); $_680052440= array(); foreach(self::$_195367000 as $_2009671592 => $_1574823244){ if($GLOBALS['____611136115'][60]($_2009671592, self::$_890533893[___1604330162(107)])) $_1266474311= self::$_890533893[___1604330162(108)][$_2009671592]; else $_1266474311=($_2009671592 == ___1604330162(109))? array(___1604330162(110)): array(___1604330162(111)); $_680052440[$_2009671592]= array( ___1604330162(112) => $_1266474311[min(180,0,60)], ___1604330162(113) => $_1266474311[round(0+0.25+0.25+0.25+0.25)], ___1604330162(114) => array(),); $_680052440[$_2009671592][___1604330162(115)]= false; if($_680052440[$_2009671592][___1604330162(116)] == ___1604330162(117)){ $_680052440[$_2009671592][___1604330162(118)]= $GLOBALS['____611136115'][61](($GLOBALS['____611136115'][62]()- $_680052440[$_2009671592][___1604330162(119)])/ round(0+86400)); if($_680052440[$_2009671592][___1604330162(120)]> self::$_741916849) $_680052440[$_2009671592][___1604330162(121)]= true;} foreach($_1574823244 as $_1473692294) $_680052440[$_2009671592][___1604330162(122)][$_1473692294]=(!$GLOBALS['____611136115'][63]($_1473692294, self::$_890533893[___1604330162(123)]) || self::$_890533893[___1604330162(124)][$_1473692294]);} return $_680052440;} private static function InstallModule($_1473347412, $_259038012){ if(IsModuleInstalled($_1473347412) == $_259038012) return true; $_80900962= $_SERVER[___1604330162(125)].___1604330162(126).$_1473347412.___1604330162(127); if(!$GLOBALS['____611136115'][64]($_80900962)) return false; include_once($_80900962); $_1714886153= $GLOBALS['____611136115'][65](___1604330162(128), ___1604330162(129), $_1473347412); if(!$GLOBALS['____611136115'][66]($_1714886153)) return false; $_1044039558= new $_1714886153; if($_259038012){ if(!$_1044039558->InstallDB()) return false; $_1044039558->InstallEvents(); if(!$_1044039558->InstallFiles()) return false;} else{ if(CModule::IncludeModule(___1604330162(130))) CSearch::DeleteIndex($_1473347412); UnRegisterModule($_1473347412);     } return true;} private static function OnRequestsSettingsChange($_1473692294, $_234567832){ self::InstallModule("form", $_234567832);} private static function OnLearningSettingsChange($_1473692294, $_234567832){ self::InstallModule("learning", $_234567832);} private static function OnJabberSettingsChange($_1473692294, $_234567832){ self::InstallModule("xmpp", $_234567832);} private static function OnVideoConferenceSettingsChange($_1473692294, $_234567832){ self::InstallModule("video", $_234567832);} private static function OnBizProcSettingsChange($_1473692294, $_234567832){ self::InstallModule("bizprocdesigner", $_234567832);} private static function OnListsSettingsChange($_1473692294, $_234567832){ self::InstallModule("lists", $_234567832);} private static function OnWikiSettingsChange($_1473692294, $_234567832){ self::InstallModule("wiki", $_234567832);} private static function OnSupportSettingsChange($_1473692294, $_234567832){ self::InstallModule("support", $_234567832);} private static function OnControllerSettingsChange($_1473692294, $_234567832){ self::InstallModule("controller", $_234567832);} private static function OnAnalyticsSettingsChange($_1473692294, $_234567832){ self::InstallModule("statistic", $_234567832);} private static function OnVoteSettingsChange($_1473692294, $_234567832){ self::InstallModule("vote", $_234567832);} private static function OnFriendsSettingsChange($_1473692294, $_234567832){ if($_234567832) $_1814621795= "Y"; else $_1814621795= ___1604330162(131); $_57576039= CSite::GetList(($_649620778= ___1604330162(132)),($_638193390= ___1604330162(133)), array(___1604330162(134) => ___1604330162(135))); while($_1587657964= $_57576039->Fetch()){ if(COption::GetOptionString(___1604330162(136), ___1604330162(137), ___1604330162(138), $_1587657964[___1604330162(139)]) != $_1814621795){ COption::SetOptionString(___1604330162(140), ___1604330162(141), $_1814621795, false, $_1587657964[___1604330162(142)]); COption::SetOptionString(___1604330162(143), ___1604330162(144), $_1814621795);}}} private static function OnMicroBlogSettingsChange($_1473692294, $_234567832){ if($_234567832) $_1814621795= "Y"; else $_1814621795= ___1604330162(145); $_57576039= CSite::GetList(($_649620778= ___1604330162(146)),($_638193390= ___1604330162(147)), array(___1604330162(148) => ___1604330162(149))); while($_1587657964= $_57576039->Fetch()){ if(COption::GetOptionString(___1604330162(150), ___1604330162(151), ___1604330162(152), $_1587657964[___1604330162(153)]) != $_1814621795){ COption::SetOptionString(___1604330162(154), ___1604330162(155), $_1814621795, false, $_1587657964[___1604330162(156)]); COption::SetOptionString(___1604330162(157), ___1604330162(158), $_1814621795);} if(COption::GetOptionString(___1604330162(159), ___1604330162(160), ___1604330162(161), $_1587657964[___1604330162(162)]) != $_1814621795){ COption::SetOptionString(___1604330162(163), ___1604330162(164), $_1814621795, false, $_1587657964[___1604330162(165)]); COption::SetOptionString(___1604330162(166), ___1604330162(167), $_1814621795);}}} private static function OnPersonalFilesSettingsChange($_1473692294, $_234567832){ if($_234567832) $_1814621795= "Y"; else $_1814621795= ___1604330162(168); $_57576039= CSite::GetList(($_649620778= ___1604330162(169)),($_638193390= ___1604330162(170)), array(___1604330162(171) => ___1604330162(172))); while($_1587657964= $_57576039->Fetch()){ if(COption::GetOptionString(___1604330162(173), ___1604330162(174), ___1604330162(175), $_1587657964[___1604330162(176)]) != $_1814621795){ COption::SetOptionString(___1604330162(177), ___1604330162(178), $_1814621795, false, $_1587657964[___1604330162(179)]); COption::SetOptionString(___1604330162(180), ___1604330162(181), $_1814621795);}}} private static function OnPersonalBlogSettingsChange($_1473692294, $_234567832){ if($_234567832) $_1814621795= "Y"; else $_1814621795= ___1604330162(182); $_57576039= CSite::GetList(($_649620778= ___1604330162(183)),($_638193390= ___1604330162(184)), array(___1604330162(185) => ___1604330162(186))); while($_1587657964= $_57576039->Fetch()){ if(COption::GetOptionString(___1604330162(187), ___1604330162(188), ___1604330162(189), $_1587657964[___1604330162(190)]) != $_1814621795){ COption::SetOptionString(___1604330162(191), ___1604330162(192), $_1814621795, false, $_1587657964[___1604330162(193)]); COption::SetOptionString(___1604330162(194), ___1604330162(195), $_1814621795);}}} private static function OnPersonalPhotoSettingsChange($_1473692294, $_234567832){ if($_234567832) $_1814621795= "Y"; else $_1814621795= ___1604330162(196); $_57576039= CSite::GetList(($_649620778= ___1604330162(197)),($_638193390= ___1604330162(198)), array(___1604330162(199) => ___1604330162(200))); while($_1587657964= $_57576039->Fetch()){ if(COption::GetOptionString(___1604330162(201), ___1604330162(202), ___1604330162(203), $_1587657964[___1604330162(204)]) != $_1814621795){ COption::SetOptionString(___1604330162(205), ___1604330162(206), $_1814621795, false, $_1587657964[___1604330162(207)]); COption::SetOptionString(___1604330162(208), ___1604330162(209), $_1814621795);}}} private static function OnPersonalForumSettingsChange($_1473692294, $_234567832){ if($_234567832) $_1814621795= "Y"; else $_1814621795= ___1604330162(210); $_57576039= CSite::GetList(($_649620778= ___1604330162(211)),($_638193390= ___1604330162(212)), array(___1604330162(213) => ___1604330162(214))); while($_1587657964= $_57576039->Fetch()){ if(COption::GetOptionString(___1604330162(215), ___1604330162(216), ___1604330162(217), $_1587657964[___1604330162(218)]) != $_1814621795){ COption::SetOptionString(___1604330162(219), ___1604330162(220), $_1814621795, false, $_1587657964[___1604330162(221)]); COption::SetOptionString(___1604330162(222), ___1604330162(223), $_1814621795);}}} private static function OnTasksSettingsChange($_1473692294, $_234567832){ if($_234567832) $_1814621795= "Y"; else $_1814621795= ___1604330162(224); $_57576039= CSite::GetList(($_649620778= ___1604330162(225)),($_638193390= ___1604330162(226)), array(___1604330162(227) => ___1604330162(228))); while($_1587657964= $_57576039->Fetch()){ if(COption::GetOptionString(___1604330162(229), ___1604330162(230), ___1604330162(231), $_1587657964[___1604330162(232)]) != $_1814621795){ COption::SetOptionString(___1604330162(233), ___1604330162(234), $_1814621795, false, $_1587657964[___1604330162(235)]); COption::SetOptionString(___1604330162(236), ___1604330162(237), $_1814621795);} if(COption::GetOptionString(___1604330162(238), ___1604330162(239), ___1604330162(240), $_1587657964[___1604330162(241)]) != $_1814621795){ COption::SetOptionString(___1604330162(242), ___1604330162(243), $_1814621795, false, $_1587657964[___1604330162(244)]); COption::SetOptionString(___1604330162(245), ___1604330162(246), $_1814621795);}} self::InstallModule(___1604330162(247), $_234567832);} private static function OnCalendarSettingsChange($_1473692294, $_234567832){ if($_234567832) $_1814621795= "Y"; else $_1814621795= ___1604330162(248); $_57576039= CSite::GetList(($_649620778= ___1604330162(249)),($_638193390= ___1604330162(250)), array(___1604330162(251) => ___1604330162(252))); while($_1587657964= $_57576039->Fetch()){ if(COption::GetOptionString(___1604330162(253), ___1604330162(254), ___1604330162(255), $_1587657964[___1604330162(256)]) != $_1814621795){ COption::SetOptionString(___1604330162(257), ___1604330162(258), $_1814621795, false, $_1587657964[___1604330162(259)]); COption::SetOptionString(___1604330162(260), ___1604330162(261), $_1814621795);} if(COption::GetOptionString(___1604330162(262), ___1604330162(263), ___1604330162(264), $_1587657964[___1604330162(265)]) != $_1814621795){ COption::SetOptionString(___1604330162(266), ___1604330162(267), $_1814621795, false, $_1587657964[___1604330162(268)]); COption::SetOptionString(___1604330162(269), ___1604330162(270), $_1814621795);}} self::InstallModule(___1604330162(271), $_234567832);} private static function OnSMTPSettingsChange($_1473692294, $_234567832){ self::InstallModule("mail", $_234567832);} private static function OnExtranetSettingsChange($_1473692294, $_234567832){ $_1182698479= COption::GetOptionString("extranet", "extranet_site", ""); if($_1182698479){ $_1173165694= new CSite; $_1173165694->Update($_1182698479, array(___1604330162(272) =>($_234567832? ___1604330162(273): ___1604330162(274))));} self::InstallModule(___1604330162(275), $_234567832);} private static function OnDAVSettingsChange($_1473692294, $_234567832){ self::InstallModule("dav", $_234567832);} private static function OntimemanSettingsChange($_1473692294, $_234567832){ self::InstallModule("timeman", $_234567832);} private static function Onintranet_sharepointSettingsChange($_1473692294, $_234567832){ if($_234567832){ RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem"); RegisterModuleDependences(___1604330162(276), ___1604330162(277), ___1604330162(278), ___1604330162(279), ___1604330162(280)); CAgent::AddAgent(___1604330162(281), ___1604330162(282), ___1604330162(283), round(0+166.66666666667+166.66666666667+166.66666666667)); CAgent::AddAgent(___1604330162(284), ___1604330162(285), ___1604330162(286), round(0+150+150)); CAgent::AddAgent(___1604330162(287), ___1604330162(288), ___1604330162(289), round(0+720+720+720+720+720));} else{ UnRegisterModuleDependences(___1604330162(290), ___1604330162(291), ___1604330162(292), ___1604330162(293), ___1604330162(294)); UnRegisterModuleDependences(___1604330162(295), ___1604330162(296), ___1604330162(297), ___1604330162(298), ___1604330162(299)); CAgent::RemoveAgent(___1604330162(300), ___1604330162(301)); CAgent::RemoveAgent(___1604330162(302), ___1604330162(303)); CAgent::RemoveAgent(___1604330162(304), ___1604330162(305));}} private static function OncrmSettingsChange($_1473692294, $_234567832){ if($_234567832) COption::SetOptionString("crm", "form_features", "Y"); self::InstallModule(___1604330162(306), $_234567832);} private static function OnClusterSettingsChange($_1473692294, $_234567832){ self::InstallModule("cluster", $_234567832);} private static function OnMultiSitesSettingsChange($_1473692294, $_234567832){ if($_234567832) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php"); else UnRegisterModuleDependences(___1604330162(307), ___1604330162(308), ___1604330162(309), ___1604330162(310), ___1604330162(311), ___1604330162(312));} private static function OnIdeaSettingsChange($_1473692294, $_234567832){ self::InstallModule("idea", $_234567832);} private static function OnMeetingSettingsChange($_1473692294, $_234567832){ self::InstallModule("meeting", $_234567832);} private static function OnXDImportSettingsChange($_1473692294, $_234567832){ self::InstallModule("xdimport", $_234567832);}} $GLOBALS['____611136115'][67](___1604330162(313), ___1604330162(314));/**/			//Do not remove this

//component 2.0 template engines
$GLOBALS["arCustomTemplateEngines"] = array();

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/urlrewriter.php");

/**
 * Defined in dbconn.php
 * @param string $DBType
 */

\Bitrix\Main\Loader::registerAutoLoadClasses(
	"main",
	array(
		"CSiteTemplate" => "classes/general/site_template.php",
		"CBitrixComponent" => "classes/general/component.php",
		"CComponentEngine" => "classes/general/component_engine.php",
		"CComponentAjax" => "classes/general/component_ajax.php",
		"CBitrixComponentTemplate" => "classes/general/component_template.php",
		"CComponentUtil" => "classes/general/component_util.php",
		"CControllerClient" => "classes/general/controller_member.php",
		"PHPParser" => "classes/general/php_parser.php",
		"CDiskQuota" => "classes/".$DBType."/quota.php",
		"CEventLog" => "classes/general/event_log.php",
		"CEventMain" => "classes/general/event_log.php",
		"CAdminFileDialog" => "classes/general/file_dialog.php",
		"WLL_User" => "classes/general/liveid.php",
		"WLL_ConsentToken" => "classes/general/liveid.php",
		"WindowsLiveLogin" => "classes/general/liveid.php",
		"CAllFile" => "classes/general/file.php",
		"CFile" => "classes/".$DBType."/file.php",
		"CTempFile" => "classes/general/file_temp.php",
		"CFavorites" => "classes/".$DBType."/favorites.php",
		"CUserOptions" => "classes/general/user_options.php",
		"CGridOptions" => "classes/general/grids.php",
		"CUndo" => "/classes/general/undo.php",
		"CAutoSave" => "/classes/general/undo.php",
		"CRatings" => "classes/".$DBType."/ratings.php",
		"CRatingsComponentsMain" => "classes/".$DBType."/ratings_components.php",
		"CRatingRule" => "classes/general/rating_rule.php",
		"CRatingRulesMain" => "classes/".$DBType."/rating_rules.php",
		"CTopPanel" => "public/top_panel.php",
		"CEditArea" => "public/edit_area.php",
		"CComponentPanel" => "public/edit_area.php",
		"CTextParser" => "classes/general/textparser.php",
		"CPHPCacheFiles" => "classes/general/cache_files.php",
		"CDataXML" => "classes/general/xml.php",
		"CXMLFileStream" => "classes/general/xml.php",
		"CRsaProvider" => "classes/general/rsasecurity.php",
		"CRsaSecurity" => "classes/general/rsasecurity.php",
		"CRsaBcmathProvider" => "classes/general/rsabcmath.php",
		"CRsaOpensslProvider" => "classes/general/rsaopenssl.php",
		"CASNReader" => "classes/general/asn.php",
		"CBXShortUri" => "classes/".$DBType."/short_uri.php",
		"CFinder" => "classes/general/finder.php",
		"CAccess" => "classes/general/access.php",
		"CAuthProvider" => "classes/general/authproviders.php",
		"IProviderInterface" => "classes/general/authproviders.php",
		"CGroupAuthProvider" => "classes/general/authproviders.php",
		"CUserAuthProvider" => "classes/general/authproviders.php",
		"CTableSchema" => "classes/general/table_schema.php",
		"CCSVData" => "classes/general/csv_data.php",
		"CSmile" => "classes/general/smile.php",
		"CSmileGallery" => "classes/general/smile.php",
		"CSmileSet" => "classes/general/smile.php",
		"CGlobalCounter" => "classes/general/global_counter.php",
		"CUserCounter" => "classes/".$DBType."/user_counter.php",
		"CUserCounterPage" => "classes/".$DBType."/user_counter.php",
		"CHotKeys" => "classes/general/hot_keys.php",
		"CHotKeysCode" => "classes/general/hot_keys.php",
		"CBXSanitizer" => "classes/general/sanitizer.php",
		"CBXArchive" => "classes/general/archive.php",
		"CAdminNotify" => "classes/general/admin_notify.php",
		"CBXFavAdmMenu" => "classes/general/favorites.php",
		"CAdminInformer" => "classes/general/admin_informer.php",
		"CSiteCheckerTest" => "classes/general/site_checker.php",
		"CSqlUtil" => "classes/general/sql_util.php",
		"CHTMLPagesCache" => "classes/general/cache_html.php",
		"CFileUploader" => "classes/general/uploader.php",
		"LPA" => "classes/general/lpa.php",
		"CAdminFilter" => "interface/admin_filter.php",
		"CAdminList" => "interface/admin_list.php",
		"CAdminListRow" => "interface/admin_list.php",
		"CAdminTabControl" => "interface/admin_tabcontrol.php",
		"CAdminForm" => "interface/admin_form.php",
		"CAdminFormSettings" => "interface/admin_form.php",
		"CAdminTabControlDrag" => "interface/admin_tabcontrol_drag.php",
		"CAdminDraggableBlockEngine" => "interface/admin_tabcontrol_drag.php",
		"CJSPopup" => "interface/jspopup.php",
		"CJSPopupOnPage" => "interface/jspopup.php",
		"CAdminCalendar" => "interface/admin_calendar.php",
		"CAdminViewTabControl" => "interface/admin_viewtabcontrol.php",
		"CAdminTabEngine" => "interface/admin_tabengine.php",
	)
);

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/agent.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/user.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/event.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/menu.php");
AddEventHandler("main", "OnAfterEpilog", array("\\Bitrix\\Main\\Data\\ManagedCache", "finalize"));
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/usertype.php");

if(file_exists(($_fname = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_db_updater.php")))
{
	$US_HOST_PROCESS_MAIN = False;
	include($_fname);
}

$GLOBALS["APPLICATION"]->AddJSKernelInfo(
	'main',
	array(
		'/bitrix/js/main/core/core.js', '/bitrix/js/main/core/core_ajax.js', '/bitrix/js/main/json/json2.min.js',
		'/bitrix/js/main/core/core_ls.js', '/bitrix/js/main/core/core_popup.js', '/bitrix/js/main/core/core_tooltip.js',
		'/bitrix/js/main/core/core_date.js','/bitrix/js/main/core/core_timer.js', '/bitrix/js/main/core/core_fx.js',
		'/bitrix/js/main/core/core_window.js', '/bitrix/js/main/core/core_autosave.js', '/bitrix/js/main/rating_like.js',
		'/bitrix/js/main/session.js', '/bitrix/js/main/dd.js', '/bitrix/js/main/utils.js',
		'/bitrix/js/main/core/core_dd.js', '/bitrix/js/main/core/core_webrtc.js'
	)
);


$GLOBALS["APPLICATION"]->AddCSSKernelInfo(
	'main',
	array(
		'/bitrix/js/main/core/css/core.css', '/bitrix/js/main/core/css/core_popup.css',
		'/bitrix/js/main/core/css/core_tooltip.css', '/bitrix/js/main/core/css/core_date.css'
	)
);

//Park core uploader
$GLOBALS["APPLICATION"]->AddJSKernelInfo(
	'coreuploader',
	array(
		'/bitrix/js/main/core/core_uploader/common.js',
		'/bitrix/js/main/core/core_uploader/uploader.js',
		'/bitrix/js/main/core/core_uploader/file.js',
		'/bitrix/js/main/core/core_uploader/queue.js',
	)
);

if(file_exists(($_fname = $_SERVER["DOCUMENT_ROOT"]."/bitrix/init.php")))
	include_once($_fname);

if(($_fname = getLocalPath("php_interface/init.php", BX_PERSONAL_ROOT)) !== false)
	include_once($_SERVER["DOCUMENT_ROOT"].$_fname);

if(($_fname = getLocalPath("php_interface/".SITE_ID."/init.php", BX_PERSONAL_ROOT)) !== false)
	include_once($_SERVER["DOCUMENT_ROOT"].$_fname);

if(!defined("BX_FILE_PERMISSIONS"))
	define("BX_FILE_PERMISSIONS", 0644);
if(!defined("BX_DIR_PERMISSIONS"))
	define("BX_DIR_PERMISSIONS", 0755);

//global var, is used somewhere
$GLOBALS["sDocPath"] = $GLOBALS["APPLICATION"]->GetCurPage();

if((!(defined("STATISTIC_ONLY") && STATISTIC_ONLY && substr($GLOBALS["APPLICATION"]->GetCurPage(), 0, strlen(BX_ROOT."/admin/"))!=BX_ROOT."/admin/")) && COption::GetOptionString("main", "include_charset", "Y")=="Y" && strlen(LANG_CHARSET)>0)
	header("Content-Type: text/html; charset=".LANG_CHARSET);

if(COption::GetOptionString("main", "set_p3p_header", "Y")=="Y")
	header("P3P: policyref=\"/bitrix/p3p.xml\", CP=\"NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA\"");

//licence key
$LICENSE_KEY = "";
if(file_exists(($_fname = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/license_key.php")))
	include($_fname);
if($LICENSE_KEY == "" || strtoupper($LICENSE_KEY) == "DEMO")
	define("LICENSE_KEY", "DEMO");
else
	define("LICENSE_KEY", $LICENSE_KEY);

header("X-Powered-CMS: Bitrix Site Manager (".(LICENSE_KEY == "DEMO"? "DEMO" : md5("BITRIX".LICENSE_KEY."LICENCE")).")");
if (COption::GetOptionString("main", "update_devsrv", "") == "Y")
	header("X-DevSrv-CMS: Bitrix");

define("BX_CRONTAB_SUPPORT", defined("BX_CRONTAB"));

if(COption::GetOptionString("main", "check_agents", "Y")=="Y")
{
	define("START_EXEC_AGENTS_1", microtime());
	$GLOBALS["BX_STATE"] = "AG";
	$GLOBALS["DB"]->StartUsingMasterOnly();
	CAgent::CheckAgents();
	$GLOBALS["DB"]->StopUsingMasterOnly();
	define("START_EXEC_AGENTS_2", microtime());
	$GLOBALS["BX_STATE"] = "PB";
}

//session initialization
ini_set("session.cookie_httponly", "1");

if($domain = $GLOBALS["APPLICATION"]->GetCookieDomain())
	ini_set("session.cookie_domain", $domain);

if(COption::GetOptionString("security", "session", "N") === "Y"	&& CModule::IncludeModule("security"))
	CSecuritySession::Init();

session_start();

foreach (GetModuleEvents("main", "OnPageStart", true) as $arEvent)
	ExecuteModuleEventEx($arEvent);

//define global user object
$GLOBALS["USER"] = new CUser;

//session control from group policy
$arPolicy = $GLOBALS["USER"]->GetSecurityPolicy();
$currTime = time();
if(
	(
		//IP address changed
		$_SESSION['SESS_IP']
		&& strlen($arPolicy["SESSION_IP_MASK"])>0
		&& (
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($_SESSION['SESS_IP']))
			!=
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($_SERVER['REMOTE_ADDR']))
		)
	)
	||
	(
		//session timeout
		$arPolicy["SESSION_TIMEOUT"]>0
		&& $_SESSION['SESS_TIME']>0
		&& $currTime-$arPolicy["SESSION_TIMEOUT"]*60 > $_SESSION['SESS_TIME']
	)
	||
	(
		//session expander control
		isset($_SESSION["BX_SESSION_TERMINATE_TIME"])
		&& $_SESSION["BX_SESSION_TERMINATE_TIME"] > 0
		&& $currTime > $_SESSION["BX_SESSION_TERMINATE_TIME"]
	)
	||
	(
		//signed session
		isset($_SESSION["BX_SESSION_SIGN"])
		&& $_SESSION["BX_SESSION_SIGN"] <> bitrix_sess_sign()
	)
	||
	(
		//session manually expired, e.g. in $User->LoginHitByHash
	isSessionExpired()
	)
)
{
	$_SESSION = array();
	@session_destroy();

	//session_destroy cleans user sesssion handles in some PHP versions
	//see http://bugs.php.net/bug.php?id=32330 discussion
	if(COption::GetOptionString("security", "session", "N") === "Y"	&& CModule::IncludeModule("security"))
		CSecuritySession::Init();

	session_id(md5(uniqid(rand(), true)));
	session_start();
	$GLOBALS["USER"] = new CUser;
}
$_SESSION['SESS_IP'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['SESS_TIME'] = time();
if(!isset($_SESSION["BX_SESSION_SIGN"]))
	$_SESSION["BX_SESSION_SIGN"] = bitrix_sess_sign();

//session control from security module
if(
	(COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y")
	&& (COption::GetOptionInt("main", "session_id_ttl", 0) > 0)
	&& !defined("BX_SESSION_ID_CHANGE")
)
{
	if(!array_key_exists('SESS_ID_TIME', $_SESSION))
	{
		$_SESSION['SESS_ID_TIME'] = $_SESSION['SESS_TIME'];
	}
	elseif(($_SESSION['SESS_ID_TIME'] + COption::GetOptionInt("main", "session_id_ttl")) < $_SESSION['SESS_TIME'])
	{
		if(COption::GetOptionString("security", "session", "N") === "Y" && CModule::IncludeModule("security"))
		{
			CSecuritySession::UpdateSessID();
		}
		else
		{
			session_regenerate_id();
		}
		$_SESSION['SESS_ID_TIME'] = $_SESSION['SESS_TIME'];
	}
}

define("BX_STARTED", true);

if (isset($_SESSION['BX_ADMIN_LOAD_AUTH']))
{
	define('ADMIN_SECTION_LOAD_AUTH', 1);
	unset($_SESSION['BX_ADMIN_LOAD_AUTH']);
}

if(!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true)
{
	$bLogout = isset($_REQUEST["logout"]) && (strtolower($_REQUEST["logout"]) == "yes");

	if($bLogout && $GLOBALS["USER"]->IsAuthorized())
	{
		$GLOBALS["USER"]->Logout();
		LocalRedirect($GLOBALS["APPLICATION"]->GetCurPageParam('', array('logout')));
	}

	// authorize by cookies
	if(!$GLOBALS["USER"]->IsAuthorized())
	{
		$GLOBALS["USER"]->LoginByCookies();
	}

	$arAuthResult = false;

	//http basic and digest authorization
	if(($httpAuth = $GLOBALS["USER"]->LoginByHttpAuth()) !== null)
	{
		$arAuthResult = $httpAuth;
		$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
	}

	//Authorize user from authorization html form
	if(isset($_REQUEST["AUTH_FORM"]) && $_REQUEST["AUTH_FORM"] <> '')
	{
		$bRsaError = false;
		if(COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
		{
			//possible encrypted user password
			$sec = new CRsaSecurity();
			if(($arKeys = $sec->LoadKeys()))
			{
				$sec->SetKeys($arKeys);
				$errno = $sec->AcceptFromForm(array('USER_PASSWORD', 'USER_CONFIRM_PASSWORD'));
				if($errno == CRsaSecurity::ERROR_SESS_CHECK)
					$arAuthResult = array("MESSAGE"=>GetMessage("main_include_decode_pass_sess"), "TYPE"=>"ERROR");
				elseif($errno < 0)
					$arAuthResult = array("MESSAGE"=>GetMessage("main_include_decode_pass_err", array("#ERRCODE#"=>$errno)), "TYPE"=>"ERROR");

				if($errno < 0)
					$bRsaError = true;
			}
		}

		if($bRsaError == false)
		{
			if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
				$USER_LID = LANG;
			else
				$USER_LID = false;

			if($_REQUEST["TYPE"] == "AUTH")
			{
				$arAuthResult = $GLOBALS["USER"]->Login($_REQUEST["USER_LOGIN"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_REMEMBER"]);
			}
			elseif($_REQUEST["TYPE"] == "OTP")
			{
				$arAuthResult = $GLOBALS["USER"]->LoginByOtp($_REQUEST["USER_OTP"], $_REQUEST["OTP_REMEMBER"], $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			}
			elseif($_REQUEST["TYPE"] == "SEND_PWD")
			{
				$arAuthResult = CUser::SendPassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_EMAIL"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			}
			elseif($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST["TYPE"] == "CHANGE_PWD")
			{
				$arAuthResult = $GLOBALS["USER"]->ChangePassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_CHECKWORD"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			}
			elseif(COption::GetOptionString("main", "new_user_registration", "N") == "Y" && $_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST["TYPE"] == "REGISTRATION" && (!defined("ADMIN_SECTION") || ADMIN_SECTION!==true))
			{
				$arAuthResult = $GLOBALS["USER"]->Register($_REQUEST["USER_LOGIN"], $_REQUEST["USER_NAME"], $_REQUEST["USER_LAST_NAME"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $_REQUEST["USER_EMAIL"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			}

			if($_REQUEST["TYPE"] == "AUTH" || $_REQUEST["TYPE"] == "OTP")
			{
				//special login form in the control panel
				if($arAuthResult === true && defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				{
					//store cookies for next hit (see CMain::GetSpreadCookieHTML())
					$GLOBALS["APPLICATION"]->StoreCookies();
					$_SESSION['BX_ADMIN_LOAD_AUTH'] = true;
					echo '<script type="text/javascript">window.onload=function(){top.BX.AUTHAGENT.setAuthResult(false);};</script>';
					die();
				}
			}
		}
		$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
	}
	elseif(!$GLOBALS["USER"]->IsAuthorized())
	{
		//Authorize by unique URL
		$GLOBALS["USER"]->LoginHitByHash();
	}
}

//application password scope control
if(($applicationID = $GLOBALS["USER"]->GetParam("APPLICATION_ID")) !== null)
{
	$appManager = \Bitrix\Main\Authentication\ApplicationManager::getInstance();
	if($appManager->checkScope($applicationID) !== true)
	{
		$event = new \Bitrix\Main\Event("main", "onApplicationScopeError", Array('APPLICATION_ID' => $applicationID));
		$event->send();

		CHTTP::SetStatus("403 Forbidden");
		die();
	}
}

//define the site template
if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
{
	$siteTemplate = "";
	if(is_string($_REQUEST["bitrix_preview_site_template"]) && $_REQUEST["bitrix_preview_site_template"] <> "" && $GLOBALS["USER"]->CanDoOperation('view_other_settings'))
	{
		//preview of site template
		$signer = new Bitrix\Main\Security\Sign\Signer();
		try
		{
			//protected by a sign
			$requestTemplate = $signer->unsign($_REQUEST["bitrix_preview_site_template"], "template_preview".bitrix_sessid());

			$aTemplates = CSiteTemplate::GetByID($requestTemplate);
			if($template = $aTemplates->Fetch())
			{
				$siteTemplate = $template["ID"];

				//preview of unsaved template
				if(isset($_GET['bx_template_preview_mode']) && $_GET['bx_template_preview_mode'] == 'Y' && $GLOBALS["USER"]->CanDoOperation('edit_other_settings'))
				{
					define("SITE_TEMPLATE_PREVIEW_MODE", true);
				}
			}
		}
		catch(\Bitrix\Main\Security\Sign\BadSignatureException $e)
		{
		}
	}
	if($siteTemplate == "")
	{
		$siteTemplate = CSite::GetCurTemplate();
	}
	define("SITE_TEMPLATE_ID", $siteTemplate);
	define("SITE_TEMPLATE_PATH", getLocalPath('templates/'.SITE_TEMPLATE_ID, BX_PERSONAL_ROOT));
}

//magic parameters: show page creation time
if(isset($_GET["show_page_exec_time"]))
{
	if($_GET["show_page_exec_time"]=="Y" || $_GET["show_page_exec_time"]=="N")
		$_SESSION["SESS_SHOW_TIME_EXEC"] = $_GET["show_page_exec_time"];
}

//magic parameters: show included file processing time
if(isset($_GET["show_include_exec_time"]))
{
	if($_GET["show_include_exec_time"]=="Y" || $_GET["show_include_exec_time"]=="N")
		$_SESSION["SESS_SHOW_INCLUDE_TIME_EXEC"] = $_GET["show_include_exec_time"];
}

//magic parameters: show include areas
if(isset($_GET["bitrix_include_areas"]) && $_GET["bitrix_include_areas"] <> "")
	$GLOBALS["APPLICATION"]->SetShowIncludeAreas($_GET["bitrix_include_areas"]=="Y");

//magic sound
if($GLOBALS["USER"]->IsAuthorized())
{
	$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
	if(!isset($_COOKIE[$cookie_prefix.'_SOUND_LOGIN_PLAYED']))
		$GLOBALS["APPLICATION"]->set_cookie('SOUND_LOGIN_PLAYED', 'Y', 0);
}

//magic cache
\Bitrix\Main\Page\Frame::shouldBeEnabled();

//magic short URI
if(defined("BX_CHECK_SHORT_URI") && BX_CHECK_SHORT_URI && CBXShortUri::CheckUri())
{
	//local redirect inside
	die();
}

foreach(GetModuleEvents("main", "OnBeforeProlog", true) as $arEvent)
	ExecuteModuleEventEx($arEvent);

if((!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true) && (!defined("NOT_CHECK_FILE_PERMISSIONS") || NOT_CHECK_FILE_PERMISSIONS!==true))
{
	$real_path = $request->getScriptFile();

	if(!$GLOBALS["USER"]->CanDoFileOperation('fm_view_file', array(SITE_ID, $real_path)) || (defined("NEED_AUTH") && NEED_AUTH && !$GLOBALS["USER"]->IsAuthorized()))
	{
		/** @noinspection PhpUndefinedVariableInspection */
		if($GLOBALS["USER"]->IsAuthorized() && $arAuthResult["MESSAGE"] == '')
			$arAuthResult = array("MESSAGE"=>GetMessage("ACCESS_DENIED").' '.GetMessage("ACCESS_DENIED_FILE", array("#FILE#"=>$real_path)), "TYPE"=>"ERROR");

		if(defined("ADMIN_SECTION") && ADMIN_SECTION==true)
		{
			if ($_REQUEST["mode"]=="list" || $_REQUEST["mode"]=="settings")
			{
				echo "<script>top.location='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';</script>";
				die();
			}
			elseif ($_REQUEST["mode"]=="frame")
			{
				echo "<script type=\"text/javascript\">
					var w = (opener? opener.window:parent.window);
					w.location.href='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';
				</script>";
				die();
			}
			elseif(defined("MOBILE_APP_ADMIN") && MOBILE_APP_ADMIN==true)
			{
				echo json_encode(Array("status"=>"failed"));
				die();
			}
		}

		/** @noinspection PhpUndefinedVariableInspection */
		$GLOBALS["APPLICATION"]->AuthForm($arAuthResult);
	}
}

       //Do not remove this

if(isset($REDIRECT_STATUS) && $REDIRECT_STATUS==404)
{
	if(COption::GetOptionString("main", "header_200", "N")=="Y")
		CHTTP::SetStatus("200 OK");
}
