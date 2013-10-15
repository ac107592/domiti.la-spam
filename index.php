<?php
	# Carrega as definicoes globais
	foreach(parse_ini_file('configuracoes.ini', TRUE) as $key => $param)
		foreach($param as $chave => $valor)
			$GLOBALS[$key][$chave] = $valor;

	$GLOBALS['BD_SQLite'] = new PDO('sqlite:'. dirname($_SERVER['SCRIPT_FILENAME']) .'/bd/contatos.sqlite',
		NULL, NULL,
			array(
				PDO::ATTR_PERSISTENT => TRUE,
				PDO::ERRMODE_WARNING => TRUE,#
				PDO::ATTR_STRINGIFY_FETCHES => TRUE,
				PDO::NULL_EMPTY_STRING => TRUE,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
			)
	);
	$GLOBALS['BD_SQLite']->exec('PRAGMA encoding="UTF-8"; PRAGMA cache_size=4000; PRAGMA writable_schema=ON; PRAGMA synchronous=OFF; PRAGMA case_sensitive_like=OFF; PRAGMA journal_mode=OFF; PRAGMA count_changes=OFF;');

	$ret = $GLOBALS['BD_SQLite']->query('SELECT COUNT(1) AS total FROM Contatos;');
	$ret = $ret->fetch();
	$itotal = $ret['total'];
?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<title>SPAM da Lítera</title>

<body>
<script type="text/javascript">
function m(pk){
	document.getElementById('pk').value=pk
	document.frm.submit();
}
</script>
<h1>Envio de ~<?=$itotal?> emails [<?=$GLOBALS['EMAIL']['ROUND']?>-<?=$GLOBALS['EMAIL']['PASSOS']?> <?=$GLOBALS['EMAIL']['ROUND']*$GLOBALS['EMAIL']['PASSOS']?>]</h1>

<pre>
<? if(empty($_GET['pk'])):


	// captura os anexos
	$anexos = array();
	foreach(scandir('mail') as $x)
		if($x[0] != '.')
		{
			$mime = mime_content_type('mail/'. $x);
			if(in_array($mime, array('image/gif', 'image/png', 'image/jpg', 'image/jpeg')))
			{
				$arquivo = fopen("mail/$x", 'r');
				$anexos[$x] = array
				(
					'mime' => $mime,
					'cid' => md5($x),
					'base64' => chunk_split(base64_encode(  fread($arquivo, filesize("mail/$x"))  ))
				);
				fclose($arquivo);
			}
		}


	// monta o cabecalho
	date_default_timezone_set('America/Sao_Paulo');
	$limitador = "_=======". date('YmdHms'). time() . "=======_";
	$headers = <<<HTML
MIME-version: 1.1\r
Content-type: multipart/related; boundary="{$limitador}"\r
From: "{$GLOBALS['REMETENTE']['NOME']}" <{$GLOBALS['REMETENTE']['EMAIL']}>\r
HTML;

	// monta o corpo da mensagem
	$msg_body = file_get_contents('mail/index.html.inc');

	foreach ($anexos as $key => $val)
		$msg_body = str_replace($key, 'cid:'. $val['cid'], $msg_body);

	$msg_body = <<<HTML
--$limitador\r
Content-type: text/html; charset=utf-8\r
$msg_body\r
--$limitador\r
HTML;

	// anexa os anexos
	foreach ($anexos as $key => $val)
	{
		$msg_body .= 'Content-type: '. $val['mime'] .'; name="'. $key ."\"\r\n";
		$msg_body .= "Content-Transfer-Encoding: base64\r\n";
		$msg_body .= 'Content-ID: <'. $val['cid'] .">\r\n";
		$msg_body .= $val['base64'] . "\r\n";
		$msg_body .= "--$limitador\r\n";
	}

	// versao texto
	$msg_body .= 'Content-type: text/plain; charset="utf-8"' . "\r\n";
	$msg_body .= "Content-Transfer-Encoding: 7bit\r\n";
	$msg_body .= file_get_contents('mail/index.html.txt');
	$msg_body .= "--$limitador\r\n";


print_r($msg_body);
die;
	// processa a requisicao
	$sth = $GLOBALS['BD_SQLite']->prepare('SELECT pkContatos AS id, usuario, nome AS dominio FROM Contatos, Dominios WHERE pkDominios = fkDominios AND fkDominios = ? LIMIT ? ;');
	$sth->execute(array( $_GET['pk'] , $GLOBALS['EMAIL']['ROUND']*$GLOBALS['EMAIL']['PASSOS'] ));
	$ret = $sth->fetchAll();

	$ids = array();
	foreach ($ret as $val)
	{
		array_push($ids, $val['id']);
	}

	$sth = $GLOBALS['BD_SQLite']->prepare('DELETE FROM Contatos WHERE pkContatos IN ( ? ) ;');
	if (sizeof($ids )==1)
		$sth->execute(array(  $ids[0]  ));
	else
		$sth->execute(array( explode(', ', $ids)  ));


//	mail("\"{$GLOBALS['DESTINATARIO']['NOME']}\" <{$GLOBALS['DESTINATARIO']['EMAIL']}>",
//		$GLOBALS['DESTINATARIO']['TITULO'],
//		$msg_body, $headers,
//		'-r '. $GLOBALS['REMETENTE']['REPLAY']) or die;


endif?>
</pre>

<form action="index.php" method="get" name='frm'>
<input type="hidden" name="cache" value="<?=uniqid()?>">
<input type="hidden" name="pk" id="pk" value="">
<?
	$sth = $GLOBALS['BD_SQLite']->prepare('SELECT id, nome, total FROM LstDominios;');
	$sth->execute();
	$ret = $sth->fetchAll();
?>
<? foreach ($ret as $item):?>
	<button type="button" onclick="m(<?=$item['id']?>)"><?=$item['nome']?><br><?=$item['total']?></button>
<? endforeach?>
</form>


</body>
</html>