<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<title>Importador</title>

<body>
<h1>Importador</h1>


<form action="index.php" method="post">
	<input type="submit" value="Importar">
	<input type="hidden" name="nha" value="=D">
</form>

<pre>
<?php

if (empty($_POST))
	die('</pre></body></html>');



function destrincha ($str, &$usr, &$dom)
{
	$str = explode('@', $str);
	$usr = $str[0];
	$dom = $str[1];
}


	////////////////
	// BEANCHMARK //
	////////////////
	list($usec, $sec) = explode(' ', microtime()); 
	$beanch = ((float)$usec + (float)$sec);

// desmarque para verificar existencia de dominios
//	$DNS = 1;

// desmarque para limitar o numero de contas total por dominio
	$LIMIT = 3;

	$delimitador = ',';
	$cerca = '"';
	$countdominios = array();
	$vetdominios = array();
	$blacklist = array
	(
		'sindec.org.br', 'claudiojanta.com.br', 'fsindical-rs.org.br', 'ibest.com.br', 'yahoo.com', 'pop.com.br',
		'brturbo.com.br', 'hotmail.com.br', 'msn.com', 'ymail.com', 'bol.com', 'oi.com.br', 'portoweb.com.br', 'live.com',
		'zipmail.com.br', 'ig.com.br', 'rocketmail.com', 'click21.com.br', 'superig.com.br', 'pop.com', 'mksnet.com.br',
		'brturbo.com', 'mksnet.com.br', 'aol.com', 'uol.com', 'senalba-rs.com.br', 'ibest.com', 'fsindical.org.br', 'hot.com',
		'smc.prefpoa.com.br', 'googlemail.com', 'hotmail.it', 'ig.com', 'mail.orkut.com', 'veraz.com.br', 'dieese.org.br',
		'windowslive.com', 'hotmail.co.uk', 'mail.com', 'zaz.com.br', 'hotmail.fr', 'secguaiba.org.br', 'globomail.com',
		'yahoo.es', 'live.it', 'hp.com', 'org.br', 'oknet.com.br', 'live.com.pt', 'zipmail.com', 'email.com', 'solicomm.net',
		'2009.com', 'thithas.com.br', 'yahoo.fr', 'fetracos.org.br', 'sindec-rs.org.br'
	);


	$fp = fopen('contatos2.csv', 'r');
	if (!$fp)
		die('nao abriu o arquivo CSV</pre></body></html>');


	$dominio = '';
	$conta = '';
	$iTotal = 0;
	while (!feof($fp))
	{
		$linha = fgetcsv($fp, 0, $delimitador, $cerca);
		if (!$linha)
			continue;

		$mail = trim($linha[1]);
		destrincha($mail, $conta, $dominio);

		if (in_array($dominio, $blacklist))
			continue;

		// Agrupa e conta domínios
		if ( !array_key_exists($dominio, $countdominios) )
		{
			$countdominios[$dominio] = 1;
			$vetdominios[$dominio] = array( $conta );
		}
			else
		{
			$countdominios[$dominio]++;
			array_push($vetdominios[$dominio], $conta );
		}

		$iTotal++;

	}

	fclose($fp);
	arsort($countdominios); // ordena por dominios


	// purifica
	if (isset($LIMIT))
		foreach ($countdominios as $key => $val)
			if ( $val < $LIMIT )
			{
				$iTotal -= $countdominios[$key];
				unset($countdominios[$key]);
				unset($vetdominios[$key]);
			}

	if (isset($DNS))
		foreach ($countdominios as $key => $val)
			if ( !checkdnsrr($key) )
			{
				$iTotal -= $countdominios[$key];
				unset($countdominios[$key]);
				unset($vetdominios[$key]);
			}


	// persiste
	$GLOBALS['BD_SQLite'] = new PDO('sqlite:'. dirname($_SERVER['SCRIPT_FILENAME']) .'/contatos.sqlite',
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

	$GLOBALS['BD_SQLite']->exec('DELETE FROM Contatos;');
	$GLOBALS['BD_SQLite']->exec('DELETE FROM Dominios;');
	$sth = $GLOBALS['BD_SQLite']->prepare('INSERT INTO Contatos ( usuario, fkDominios ) VALUES ( ? , ? ) ;');
	$sth2 = $GLOBALS['BD_SQLite']->prepare('INSERT INTO Dominios ( nome ) VALUES ( ? ) ;');


	$GLOBALS['BD_SQLite']->beginTransaction();
	foreach ($countdominios as $key => $val)
	{
		$sth2->execute(array( $key ));
		$fk = $GLOBALS['BD_SQLite']->lastInsertId();
		foreach ($vetdominios[$key] as $dom => $contas)
		{
			$sth->execute(array(
				$contas , $fk
			));
		}
	}
	$GLOBALS['BD_SQLite']->commit();
	$sth->closeCursor();


	////////////////
	// BEANCHMARK //
	////////////////
	list($usec, $sec) = explode(' ', microtime()); 
	$beanch2 = ((float)$usec + (float)$sec);
	$beanch = ($beanch2 - $beanch);

?>

------------------------------------------------------------------
~<?=$iTotal?> contatos migrados em <?=$beanch?> segundos!


<?php
	print_r($countdominios);
?>
</pre>


</body>
</html>