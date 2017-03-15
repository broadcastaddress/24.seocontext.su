<?
$MESS["SEC_OTP_ACCESS_DENIED"] = "Você não pode editar os parâmetros de senha temporárias";
$MESS["SEC_OTP_SWITCH_ON"] = "Habilitar senhas compostas";
$MESS["SEC_OTP_SECRET_KEY"] = "Secret Key (fornecida com o aparelho)";
$MESS["SEC_OTP_INIT"] = "Inicialização";
$MESS["SEC_OTP_PASS1"] = "A primeira senha do dispositivo (Clique e anote)";
$MESS["SEC_OTP_PASS2"] = "A segunda senha do dispositivo (clique novamente e anote)";
$MESS["SEC_OTP_NOTE"] = "<h3 style=\"clear:both\"> <br> Password Temporários </h3>
<img src=\"/bitrix/images/security/etoken_pass.png\" align=\"left\" style=\"margin-right:10px;\">
A <a href=\"http://en.wikipedia.org/wiki/One-time_password\"> Senha temporária </a> (<b> OTP </b>) o conceito autoriza o regime de autorização padrão e reforça significativamente a segurança projeto web. O sistema de senha única requer um token de hardware físico (dispositivo) (por exemplo, <a eToken PASS </a>) ou software de ST especial. O administrador do site é fortemente recomendado o uso de ST para garantir a melhor segurança.
<h3 style=\"clear:both\"> Uso <br> </h3>
<img src=\"/bitrix/images/security/en_pass_form.png\" align=\"left\" style=\"margin-right:10px;\">
Se o sistema OTP for ativado, o usuário pode autorizar com um login e uma senha composta que consiste de uma senha padrão e a senha do dispositivo de uma só vez (6 dígitos). A senha temporária (ver <font style=\"color:red\"> 2 </font> na figura) é inserida no campo \"Password\", juntamente com a senha padrão (veja <font style=\"color:red\">1</font> na figura), sem o espaço, na forma de autorização. <br>
A autenticação OTP tem efeito após a chave secreta e <b> consecutivamente gerada uma senha temporária </b> obtida a partir do dispositivo são inseridas.
<h3 style=\"clear:both\"> Inicialização Jogos </h3>
Ao inicializar ou sincronizar repetidamente o dispositivo, você terá que fornecer as duas <b> senhas geradas consecutivamentes </b> obtidas a partir do dispositivo.
<h3 style=\"clear:both\"> Descrição </h3>
O sistema de autorização OTP foi desenvolvido pela Iniciativa para Open Authentication (OATH <a href=\"http://www.openauthentication.org/\"> </a>). <br>
A implementação baseia-se no algoritmo HMAC e na função hash SHA-1. Para calcular o valor de OTP, o sistema toma os dois parâmetros de entrada: a chave secreta (valor inicial para o gerador) e o valor do contador de corrente (os ciclos necessários de geração). Após a inicialização do dispositivo, o valor inicial é armazenado no dispositivo, bem como no local. Os contador do dispositivo incrementa cada vez que uma OTP nova é gerado, o contador do servidor - sobre cada autenticação OTP sucesso <br>.
Cada lote de dispositivos OTP é fornecido com um arquivo criptografado que contém os valores iniciais (chaves secretas) para todos os dispositivos de um lote. Os valores são ligados aos números de série dos dispositivos impressos no corpo do dispositivo. <br>
Se o dispositivo e os contadores de geradores do servidor se tornarem dessincronizados, você poderá facilmente voltar a sincroniza-los, redefinindo o valor do servidor para o valor armazenado no dispositivo. Este procedimento requer que um administrador de sistema (ou um usuário que possua permissões suficientes) gere dois valores OTP consequentes e informa-los no formulário OTP.
";
?>