<?php

session_start();
// Cria variáveis ************
$formnome = $_POST['nome']; // dados vindos do contato.php
$formemail = $_POST['email']; // dados vindos do contato.php
$formdesc = $_POST['desc']; // dados vindos do contato.php
$assunto = $_POST['assunto']; // dados vindos do contato.php

require ("alterar_esse_arquivo.php"); // pega os dados personalizados desse arquivo

require("class.phpmailer.php"); // envio de e-mail com autenticacao do provedor

$mail = new PHPMailer(); // envodo de email com autenticacao do provedor
$mail->SetLanguage("br", "language/");
$mail->IsSMTP();

//Cria PHPmailer class
$mail->From = $formemail; //email do remetente
$mail->FromName = $formnome; //Nome de formatado do remetente
$mail->Host = "$smtp_endereco"; //Pegando dados do alterar_esse_arquivo.php
$mail->Mailer = "smtp"; //Usando protocolo SMTP
$mail->AddAddress("$seu_email"); //pegando dados do alterar_esse_arquivo.php
$mail->Subject = "$assunto";
$mail->Port = 587;
$mail->SMTPKeepAlive = true;
$mail->SMTPDebug = 2;

//Assunto do email
$mail->Body = $formdesc; //Body of the message assunto que veio do from.htm

//SMTP
$mail->SMTPAuth = true;
$mail->Username = "$usuario_smtp"; 
$mail->Password = "$senha_smtp"; 

//Verifica se email sera enviado
if(!$mail->Send())
{ //Checa erros no envo do email
echo "Ocorreram erros ao enviar email"; //Imprime mensagem de que email nào foi enviado
exit; 
}
else
{
echo "$mensagem_sucesso";
exit; 
}

?>