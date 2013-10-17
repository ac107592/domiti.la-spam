<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>

<body>

<pre>
<?php
//Return-Path (RFC 822)deve ser o mesmo e-mail especificado em From
// $headers .= "Return-Path: Um Nome <umemail@remetente.com>\n";
//$headers .= "Bcc: outroemail@destinatario.com\n"
$to      = 'regis.puc@gmail.com';
$subject = 'the subject2';
$message = 'hello2';
$headers = 'From: contato@litera.mus.br' . "\r\n" .
    'Reply-To: contato@litera.mus.br' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers,
   '-fcontato@litera.mus.br');

//	mail('"regis.puc@gmail.com', 'teste', 'oi');


?>

</pre>


</body>
</html>