<?
$MESS["SECURITY_SITE_CHECKER_EnvironmentTest_NAME"] = "Verificação de Meio Ambiente";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_TMP_DETAIL"] = "Em teoria, um invasor pode usar a pasta temp para ver os arquivos carregados.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP"] = "Scripts PHP são executados no diretório de arquivos carregados.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_RECOMMENDATION"] = "Configure seu servidor web corretamente.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DOUBLE"] = "Scripts PHP com a extensão dupla (por exemplo php.lala) são executados no diretório de arquivos carregados.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DOUBLE_RECOMMENDATION"] = "Configure seu servidor web corretamente.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PY"] = "Scripts em Python são executados no diretório de arquivos carregados.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PY_RECOMMENDATION"] = "Configure seu servidor web corretamente.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_HTACCESS"] = "O Apache não deve processar os arquivos. Htaccess no diretório de arquivos enviados";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_HTACCESS_RECOMMENDATION"] = "Configure seu servidor web corretamente.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_NEGOTIATION"] = "A negociação de conteúdo apache está ativada no diretório de upload de arquivos.  ";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_NEGOTIATION_DETAIL"] = "A negociação de conteúdo apache não é recomendada porque poderá incorrer em ataques XSS.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_NEGOTIATION_RECOMMENDATION"] = "Configure o servidor web corretamente. ";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_TMP"] = "O diretório temporário PHP está disponível para todos";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_TMP_RECOMMENDATION"] = "Configurar as permissões de acesso para o diretório temporário.";
$MESS["SECURITY_SITE_CHECKER_SESSION"] = "O diretório de armazenamento de sessão está disponível para todos";
$MESS["SECURITY_SITE_CHECKER_SESSION_DETAIL"] = "Isso pode comprometer o projeto por completo.";
$MESS["SECURITY_SITE_CHECKER_SESSION_RECOMMENDATION"] = "Configurar as permissões de acesso para este diretório.";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION"] = "O diretório de armazenamento da sessão contém sessões de projetos diferentes.";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_DETAIL"] = "A situação pode acontecer quando isto compromete completamente o seu projeto.";
$MESS["SECURITY_SITE_CHECKER_COLLECTIVE_SESSION_RECOMMENDATION"] = "Usar um armazenamento individual para cada projeto.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DETAIL"] = "s vezes, os desenvolvedores não prestam atenção suficiente em filtros de nome de arquivo adequados. Um invasor pode explorar essa vulnerabilidade e assumir total controle do seu projeto.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PHP_DOUBLE_DETAIL"] = "s vezes, os desenvolvedores não prestam atenção suficiente em filtros de nome de arquivo adequados. Um invasor pode explorar essa vulnerabilidade e assumir total controle do seu projeto.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_EXECUTABLE_PY_DETAIL"] = "s vezes, os desenvolvedores não prestam atenção suficiente em filtros de nome de arquivo adequados. Um invasor pode explorar essa vulnerabilidade e assumir total controle do seu projeto.";
$MESS["SECURITY_SITE_CHECKER_UPLOAD_HTACCESS_DETAIL"] = "s vezes, os desenvolvedores não prestam atenção suficiente em filtros de nome de arquivo adequados. Um invasor pode explorar essa vulnerabilidade e assumir total controle do seu projeto.";
?>