<?php
	use WHMCS\Database\Capsule;
	function confirmadeposito_config() {
		$configarray = array(
			"name" => "Confirmação de deposito",
			"description" => 'Módulo de confirmação de depósito/transferência para WHMCS. Esse módulo é gratuito, portanto a venda do mesmo é totalmente proibida.<br /><a href="https://compulabs.com.br/blog/modulo-whmcs-de-confirmacao-de-deposito/" class="btn btn-xs btn-primary" target="_blank"><span class="fa fa-file"></span> Post do módulo</a> <a href="https://compulabs.com.br/blog" class="btn btn-xs btn-primary" target="_blank"><span class="fa fa-cubes"></span> Mais módulos WHMCS</a>',
			"version" => "2.0",
			"author" => '<a href="https://compulabs.com.br/" target="_blank"><img src="http://i.imgur.com/IOvpH3W.png" width="115"/></a>',
		);
		return $configarray;
	}
	function confirmadeposito_activate() {
		$pdo = Capsule::connection()->getPdo();
		$pdo->beginTransaction();
		try {
		    $statement = $pdo->prepare("CREATE TABLE IF NOT EXISTS `mod_confirmadeposito` (
			    `id_confirmadeposito` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			    `fatura_confirmadeposito` varchar(20) NOT NULL,
			    `banco_confirmadeposito` varchar(150) NOT NULL,
			    `modo_confirmadeposito` varchar(150) NOT NULL,
			    `hora_confirmadeposito` varchar(150) NOT NULL,
			    `comprovante_confirmadeposito` varchar(150) NOT NULL,
			    `dataregistro_confirmadeposito` varchar(150) NOT NULL,
			    `confirmado_confirmadeposito` int(1) NOT NULL,
			    UNIQUE KEY `id_confirmadeposito` (`id_confirmadeposito`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
		    $statement->execute();
		    $statement = $pdo->prepare("CREATE TABLE IF NOT EXISTS `mod_confirmadeposito_config` (
			    `id_config` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			    `automatico_config` int(1) NOT NULL,
			    `alerta_config` text NOT NULL,
			    `bancos_config` text NOT NULL,
			    UNIQUE KEY `id_config` (`id_config`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
		    $statement->execute();
		    $statement = $pdo->prepare("INSERT INTO mod_confirmadeposito_config(automatico_config,alerta_config,bancos_config) VALUES('0','ATENÇÃO: Comprovantes falsos resultarão na suspensão da conta e de todos os serviços da mesma.','Itaú,Bradesco,Caixa Econômica');");
		    $statement->execute();
		    $pdo->commit();
		    return array('status'=>'success','description'=>'O Módulo Confirma Depósito foi instalado com sucesso.');
		} catch (\Exception $e) {
			return array('status'=>'error','description'=>'Erro ao instalar o Módulo Confirma Depósito: '.$e->getMessage());
		    $pdo->rollBack();
		}
	}
	function confirmadeposito_deactivate() {
		return array('status'=>'success','description'=>'O Módulo Confirma Depósito foi desinstalado com sucesso.');
	}
	function confirmadeposito_output(){
		$pdo = Capsule::connection()->getPdo();
		$pdo->beginTransaction();
		if(isset($_POST['modo']))
		{
			if($_POST['modo'] == "confirma")
			{
				$sql = $pdo->prepare("UPDATE mod_confirmadeposito SET confirmado_confirmadeposito = '1 WHERE fatura_confirmadeposito = '".$_POST['fatura']."';");
				$sql->execute();
				$pdo->commit();
				$qr2 = $pdo->query("SELECT mod_confirmadeposito.*, tblinvoices.*, tblclients.*, tblclients.id AS usuid FROM mod_confirmadeposito INNER JOIN tblinvoices ON tblinvoices.id = fatura_confirmadeposito INNER JOIN tblclients ON tblclients.id = tblinvoices.userid WHERE id_confirmadeposito = '".$_POST['fatura']."';");
				$row2 = $qr2->fetch();
				require "../../../includes/invoicefunctions.php";
				addInvoicePayment($_POST['fatura'], "#CONFIRMADEPO-".$_POST['fatura'], $row2['total'], "0", 'contadeposito');
				header("LOCATION: addonmodules.php?module=confirmadeposito".(isset($_GET['cfm'])?"&cfm=1":null));
			}
			else {
				$sql = $pdo->prepare("UPDATE mod_confirmadeposito SET confirmado_confirmadeposito = 2 WHERE fatura_confirmadeposito = '".$_POST['fatura']."';");
				$sql->execute();
				$pdo->commit();
				header("LOCATION: addonmodules.php?module=confirmadeposito".(isset($_GET['cfm'])?"&cfm=1":null));
			}
		}
	?>
        <style>.navbar-red{background-color:#007FFF;color:#fff;box-shadow:0 0 5px rgba(0,0,0,.2);border:none;width:100%;border-radius: 10px 10px 0px 10px;}.navbar-brand{color:#fff;padding:8px}.navbar-nav li a{color:#fff;font-size:16px}.navbar-nav .active{background-color:#FF8000;color:#fff;}.navbar-nav li a:focus,.navbar-nav li a:hover{background: #0065CC;color:#fff;}.navbar-brand h1{color:#fff;font-size:30px !important;}.panel-primary .panel-heading{background:#FF8000;color:#fff;}.sobre{background:#007FFF;color:#fff;border-radius: 10px 10px 0px 10px;padding:5px;}</style>
		<nav class="navbar navbar-red">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="addonmodules.php?module=contadeposito"><h1><img src="http://i.imgur.com/6IQ1JOI.png" class="img-responsive"/></h1></a>
			</div>
			<div id="navbar" class="collapse navbar-collapse navbar-right">
				<ul class="nav navbar-nav">
					<li <?=!isset($_GET['p']) && !isset($_GET['cfm'])?'class="active"':null?>><a href="addonmodules.php?module=confirmadeposito"><span class="fa fa-dollar"></span> Depósitos</a></li>
					<li <?=!isset($_GET['cfm'])?null:'class="active"'?>><a href="addonmodules.php?module=confirmadeposito&cfm=1"><span class="fa fa-check"></span> Depósitos processados</a></li>
					<li <?=!isset($_GET['p'])?null:'class="active"'?>><a href="addonmodules.php?module=confirmadeposito&p=config"><span class="fa fa-cog"></span> Configurações</a></li>
				</ul>
			</div>
		</nav>
		<?php
			if(!isset($_GET['p']))
			{
		?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">Depósitos pendentes</div>
                    <div class="panel-body">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<th>Data envio</th>
								<th>Fatura</th>
								<th>Valor</th>
								<th>Cliente</th>
								<th>Data comprovante</th>
								<th>Banco</th>
								<th>Modo</th>
								<th>Comprovante</th>
							</thead>
							<tbody>
								<?php
									$qr = $pdo->query("SELECT mod_confirmadeposito.*, tblinvoices.*, tblclients.*, tblclients.id AS usuid FROM mod_confirmadeposito INNER JOIN tblinvoices ON tblinvoices.id = fatura_confirmadeposito INNER JOIN tblclients ON tblclients.id = tblinvoices.userid WHERE confirmado_confirmadeposito = '".(isset($_GET['cfm'])?"1' OR confirmado_confirmadeposito = '2":"0")."';");
									while($row = $qr->fetch())
									{
								?>
								<tr>
									<td><?=$row['dataregistro_confirmadeposito']?></td>
									<td><a href="invoices.php?action=edit&id=<?=$row['fatura_confirmadeposito']?>" target="_blank" class="btn btn-block btn-primary btn-xs"><span class="fa fa-external-link"></span> <?=$row['fatura_confirmadeposito']?></a></td>
									<td>R$<?=$row['total']?></td>
									<td><a href="clientssummary.php?userid=<?=$row['usuid']?>" target="_blank" class="btn btn-block btn-primary btn-xs"><span class="fa fa-external-link"></span> <?=$row['firstname']?></td>
									<td><?=$row['hora_confirmadeposito']?></td>
									<td><?=$row['banco_confirmadeposito']?></td>
									<td><?=$row['modo_confirmadeposito']?></td>
									<td class="text-center">
										<a class="btn btn-default" style="padding:3px;" href="../modules/addons/confirmadeposito/comprovantes/<?=$row['comprovante_confirmadeposito']?>" target="_blank"><img src="../modules/addons/confirmadeposito/comprovantes/<?=$row['comprovante_confirmadeposito']?>" height="45" /></a>
									</td>
									<td>
										<form method="POST" action="addonmodules.php?module=confirmadeposito<?=isset($_GET['cfm'])?"&cfm=1":null?>">
											<input type="hidden" name="fatura" value="<?=$row['fatura_confirmadeposito']?>" />
											<input type="hidden" name="modo" value="confirma" />
											<button type="submit" class="btn btn-success btn-block btn-xs"><span class="fa fa-check"></span> Confirmar</button>
										</form>
										<form method="POST" action="addonmodules.php?module=confirmadeposito<?=isset($_GET['cfm'])?"&cfm=1":null?>">
											<input type="hidden" name="fatura" value="<?=$row['fatura_confirmadeposito']?>" />
											<input type="hidden" name="modo" value="cancelar" />
											<button type="submit" class="btn btn-danger btn-block btn-xs"><span class="fa fa-times"></span> Cancelar</button>
										</form>
									</td>
								</tr>
								<?php
									}
								?>
							</tbody>
						</table>
                    </div>
                </div>
            </div>
        </div>
		<?php
		}
		else {
			if(isset($_POST['txt-automatico']))
			{
				$sql = $pdo->prepare("UPDATE mod_confirmadeposito_config SET automatico_config = '".$_POST['txt-automatico']."', alerta_config = '".$_POST['txt-alerta']."', bancos_config = '".$_POST['txt-bancos']."';");
				$sql->execute();
				$pdo->commit();
			}
			$qr = $pdo->query("SELECT * FROM mod_confirmadeposito_config WHERE id_config = '1';");
			$row = $qr->fetch();
			?>
	        <div class="row">
	            <div class="col-md-4">
	                <div class="panel panel-primary">
	                    <div class="panel-heading">Configurações</div>
	                    <div class="panel-body">
	                    	<form method="POST" action="addonmodules.php?module=confirmadeposito&p=config">
	                    		<div class="form-group">
	                    			<label><span class="fa fa-check"></span> Confirmação automática</label>
	                    			<select name="txt-automatico" required class="form-control">
	                    				<option value="1" <?=$row['automatico_config']=="1"?"selected":null?>>Sim</option>
	                    				<option value="0" <?=$row['automatico_config']=="0"?"selected":null?>>Não</option>
	                    			</select>
	                    			<small class="pull-right">Marcar como pago após o envio do comprovante</small>
	                    		</div><br />
	                    		<div class="form-group">
	                    			<label><span class="fa fa-list"></span> Texto de alerta</label>
	                    			<textarea name="txt-alerta" required class="form-control"><?=$row['alerta_config']?></textarea>
	                    			<small class="pull-right">Deixe em branco para ocultar</small>
	                    		</div><br />
	                    		<div class="form-group">
	                    			<label><span class="fa fa-money"></span> Lista de bancos</label>
	                    			<textarea name="txt-bancos" required class="form-control"><?=$row['bancos_config']?></textarea>
	                    			<small class="pull-right">Separe com vírgula (,)</small>
	                    		</div><br />
	                    		<div class="form-group">
	                    			<button type="submit" class="btn btn-success pull-right"><span class="fa fa-check"></span> Salvar</button>
	                    		</div>
	                    	</form>
	                    </div>
	                </div>
	            </div>
	        </div>
			<?php
		}
		?>
        <div class="row">
            <div class="col-xs-12">
            	<div class="col-xs-12 sobre">
			        <div class="row">
			            <div class="col-sm-9">
			            	<?=date("Y")?> ConfirmaDepó - Todos os direitos reservados. Esse módulo é gratuito, portanto sua venda é totalmente proibida.
			            </div>
			            <div class="col-sm-3 text-right">
			            	<a href="http://compulabs.com.br" target="_blank"><img src="http://i.imgur.com/wkdi6L5.png" /></a>
			            </div>
			        </div>
            	</div>
            </div>
        </div>
		<?php
	}
?>