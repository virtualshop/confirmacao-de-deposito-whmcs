<?php
use WHMCS\Database\Capsule;
add_hook('ClientAreaPageInvoices', 1, function($vars) {
	$pdo = Capsule::connection()->getPdo();
	$pdo->beginTransaction();
	$qr = $pdo->query("SELECT * FROM mod_confirmadeposito_config WHERE id_config = '1';");
	$row = $qr->fetch();
	$bancos = explode(",",$row['bancos_config']);
	$bancos_opt = "<option disabled selected>Escolha o banco</option>";
	for($i=0;$i<count($bancos);$i++)
	{
		$bancos_opt .= "<option value='".$bancos[$i]."'>".$bancos[$i]."</option>";
	}
    echo '
    <style>
	    .inputfile {
			width: 0.1px;
			height: 0.1px;
			opacity: 0;
			overflow: hidden;
			position: absolute;
			z-index: -1;
		}
		.inputfile + label {
			font-size: 16px;
			font-weight: 700;
			color: white;
			padding: 15px;
			background-color: #FF8000;
			border-radius: 10px 10px 0 10px !important;
			display: inline-block;
			width: 100%;
		}

		.inputfile:focus + label,
		.inputfile + label:hover {
		    background-color: #C66300;
		}
		.inputfile + label {
			cursor: pointer; /* "hand" cursor */
		}
		.inputfile:focus + label {
			outline: 1px dotted #000;
			outline: -webkit-focus-ring-color auto 5px;
		}
		.inputfile + label * {
			pointer-events: none;
		}
		@-webkit-keyframes spinnerRotate
		{
		    from{-webkit-transform:rotate(0deg);}
		    to{-webkit-transform:rotate(360deg);}
		}
		@-moz-keyframes spinnerRotate
		{
		    from{-moz-transform:rotate(0deg);}
		    to{-moz-transform:rotate(360deg);}
		}
		@-ms-keyframes spinnerRotate
		{
		    from{-ms-transform:rotate(0deg);}
		    to{-ms-transform:rotate(360deg);}
		}
		.spin{
		    -webkit-animation-name: spinnerRotate;
		    -webkit-animation-duration: 5s;
		    -webkit-animation-iteration-count: infinite;
		    -webkit-animation-timing-function: linear;
		    -moz-animation-name: spinnerRotate;
		    -moz-animation-duration: 5s;
		    -moz-animation-iteration-count: infinite;
		    -moz-animation-timing-function: linear;
		    -ms-animation-name: spinnerRotate;
		    -ms-animation-duration: 5s;
		    -ms-animation-iteration-count: infinite;
		    -ms-animation-timing-function: linear;
		}

    </style>
	<div class="modal fade" id="comprovantemodal" tabindex="-1" role="dialog" aria-labelledby="comprovantemodalLabel">
	  <div class="modal-dialog" role="document">
	    <form enctype="multipart/form-data" class="modal-content" method="post" action="modules/addons/confirmadeposito/form.php" id="frmcomprovante">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="myModalLabel">Enviar comprovante de pagamento #<span class="idfaturacomprovante"></span></h4>
	      </div>
	      <div class="modal-body">
	      	<div class="alert alert-warning">'.$row['alerta_config'].'</div>
	      	<div class="alertas"></div>
	      	<div class="row">
	      		<div class="col-sm-6">
	      			<div class="form-group">
	      				<select class="form-control" required name="txt-banco">'.$bancos_opt.'</select>
	      			</div>
	      		</div>
	      		<div class="col-sm-6">
	      			<div class="form-group">
	      				<select class="form-control" required name="txt-modo">
	      					<option disabled selected>Escolha o modo</option>
	      					<option>Depósito no caixa</option>
	      					<option>Depósito na lotérica</option>
	      					<option>Transferência DOC</option>
	      					<option>Transferência TED</option>
	      					<option>Transferência TEV</option>
	      					<option>Outro</option>
	      				</select>
	      			</div>
	      		</div>
	      		<div class="col-sm-6">
	      			<div class="form-group">
	      				<input type="text" name="txt-data" required class="form-control" placeholder="Data e hora da operação" />
	      			</div>
	      		</div>
	      		<div class="col-sm-6">
	      			<div class="form-group">
	      				<input type="hidden" name="txt-fatura" value="" id="comprovante_id"/>
						<input type="file" name="txt-arquivo" id="file" class="inputfile" data-multiple-caption="{count} files selected" multiple />
						<label for="file"><i class="fa fa-upload"></i> <span>Selecionar comprovante</span></label>
	      			</div>
	      		</div>
	      	</div>
	      </div>
	      <div class="modal-footer">
	        <span class="btn btn-default" data-dismiss="modal">Cancelar</span>
	        <button type="submit" class="btn btn-primary" id="frmcomprovante-btn"><span class="fa fa-check"></span> Enviar comprovante</button>
	      </div>
	    </form>
	  </div>
	</div>
	<script>
		var idfatura = "";
		function enviar_comprovante(id)
		{
			$("#comprovantemodal").modal("show");
			$("#comprovante_id").val(id);
			$(".idfaturacomprovante").text(id);
			$(".alertas").html("");
			idfatura = id;
		}
		'.file_get_contents('https://raw.githubusercontent.com/compulabsbr/utilidades/master/fileselect.js').'
	</script>
	';
});
add_hook('ClientAreaFooterOutput', 1, function($vars) {
    return '<script>
    		$(document).ready(function() {
			$("#frmcomprovante").submit(function(event) {
				$("#frmcomprovante-btn").html(\'<span class="fa fa-spinner spin"></span> Enviando...\');
				var dados = $("#frmcomprovante").serialize();
				$.ajax({
					url: "modules/addons/confirmadeposito/form.php",
					type: "POST",
					data: new FormData(this),
					contentType: false,
					cache: false,
					processData:false,
					success: function(data)
					{
						if(data == "1")
						{
							$(".alertas").html(\'<div class="alert alert-success">Comprovante de pagamento enviado com sucesso.</div>\');
						}
						else
						{
							$(".alertas").html(\'<div class="alert alert-danger">\'+data+\'</div>\');
						}
						$("#frmcomprovante-btn").html(\'<span class="fa fa-check"></span> Enviar comprovante\');
					}
				});
				event.preventDefault();
			});
		});</script>';
});
?>