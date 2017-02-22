<?php
	use WHMCS\ClientArea;
	use WHMCS\Database\Capsule;
	require "../../../init.php";
	session_start();

	if($_POST)
	foreach($_POST as $key => $value){
		$_POST[$key] = addslashes($value);
	}

	if(isset($_SESSION['tempocomprovante']))
	{
		$time_inicial = strtotime($_SESSION['tempocomprovante']);
		$time_final = strtotime(date("Y-m-d H:i:s"));
		$diferenca = $time_final - $time_inicial;
		$min = (int)floor( $diferenca / (60));
		if($min < 6)
		{
			echo "Aguarde ".(5-$min)." minuto(s) para enviar o próximo comprovante.";
			exit();
		}
	}

	if(isset($_POST['txt-banco']) && isset($_POST['txt-modo']) && isset($_POST['txt-fatura']) && isset($_POST['txt-data']) && $_SESSION['uid'])
	{
		$n = explode(".",$_FILES['txt-arquivo']['name']);
		$ext = strtolower(end($n));
		$tamanho = 1024 * 1024 * 5;
		$extencao = array('jpg', 'png', 'gif', 'bmp', 'pdf', 'jpeg');

		if (array_search($ext, $extencao) === false) {
			$exttxt = $extencao[0];
			for($i=1;$i<count($extencao);$i++)
			{
				$exttxt .= ", ".$extencao[$i];
			}
			echo "Por favor, envie comprovantes apenas com as extenções: ".$exttxt;
			exit();
		}

		if ($tamanho < $_FILES['txt-arquivo']['size']) {
		  echo "O arquivo enviado é muito grande, envie arquivos de até ".(($tamanho/1024)/1024)."Mb.";
		  exit();
		}

		$nome = md5(microtime().date("d-m-Y H:i:s").rand(0,999)).".".$ext;

		$sourcePath = $_FILES['txt-arquivo']['tmp_name'];
		$targetPath = "comprovantes/".$nome;
		move_uploaded_file($sourcePath,$targetPath);
		$_SESSION['tempocomprovante'] = date("Y-m-d H:i:s");

		$pdo = Capsule::connection()->getPdo();
		$pdo->beginTransaction();
		$qr = $pdo->query("SELECT * FROM mod_confirmadeposito_config WHERE id_config = '1';");
		$row = $qr->fetch();

		$confirmado = "0";
		if($row['automatico_config'] == "1")
		{
			$qr2 = $pdo->query("SELECT mod_confirmadeposito.*, tblinvoices.*, tblclients.*, tblclients.id AS usuid FROM mod_confirmadeposito INNER JOIN tblinvoices ON tblinvoices.id = fatura_confirmadeposito INNER JOIN tblclients ON tblclients.id = tblinvoices.userid WHERE id_confirmadeposito = '".$_POST['txt-fatura']."';");
			$row2 = $qr2->fetch();
			require ROOTDIR . "/includes/invoicefunctions.php";
			addInvoicePayment($_POST['txt-fatura'], "#CONFIRMADEPO-".$_POST['txt-fatura'], $row2['total'], "0", 'contadeposito');
			$confirmado = "1";
		}

		$sql = $pdo->prepare("INSERT INTO mod_confirmadeposito(fatura_confirmadeposito,banco_confirmadeposito,modo_confirmadeposito,hora_confirmadeposito,comprovante_confirmadeposito,dataregistro_confirmadeposito,confirmado_confirmadeposito) VALUES('".$_POST['txt-fatura']."','".$_POST['txt-banco']."','".$_POST['txt-modo']."','".$_POST['txt-data']."','".$nome."','".date("d/m/Y H:i:s")."','".$confirmado."');");
		$sql->execute();
		$pdo->commit();

		echo "1";
	}
	else {
		echo "Campos incorretos";
	}
?>