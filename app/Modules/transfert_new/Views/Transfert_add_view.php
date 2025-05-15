<!DOCTYPE html>
<html lang="en">
<head>
	<?php echo view('includesbackend/header.php'); ?>
	<style>
		hr.vertical
		{
			border: none;
			border-left: 1px solid hsla(200, 2%, 12%, 100);
			height: 55vh;
			width: 1px;
			color: #ddd
		}
	</style>
</head>
<body>
	<div class="wrapper">
		<?php echo view('includesbackend/navybar_menu.php'); ?>
		<link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
		<script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js">
		</script>
		<script src="/DataTables/datatables.js"></script>
		<script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
		<div class="main">
			<?php echo view('includesbackend/navybar_topbar.php'); ?>
			<?php $validation = \Config\Services::validation(); ?>
			<main class="content">
				<div class="container-fluid">
					<div class="header">
						<h1 class="header-title text-white"></h1>
					</div>
					<div class="row">
						<div class="col-12">
							<div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
								<div style="float: right;">
									<a href="<?php echo base_url('transfert_new/Transfert/')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?=lang('messages_lang.link_list')?></a>
								</div>
								<div class="car-body">
									<?php
									if(session()->getFlashKeys('alert'))
									{
										?>
										<center class="ml-5" style="height=100px;width:90%">
											<div class="alert alert-success" id="message">
												<?php echo session()->getFlashdata('alert')['message']; ?>
											</div>
										</center>
										<?php
									}
									?>
									
									<h4 style="margin-left:4%;margin-top:10px"><?=lang('messages_lang.titre_transf_add')?></h4>
									<br>

									<div class=" container " style="width:90%">
										<form id="Myform" action="<?=base_url('transfert_new/Transfert/enregistre_tempo')?>" method="post">
											<input type="hidden" value="<?= $info['EXECUTION_BUDGETAIRE_ID']?>" name="id" class="form-control">
											<div>
												<div><i class="fa fa-cubes"></i> <?=lang('messages_lang.ligne_budgetaire')?> : <?= $info['IMPUTATION']?></div>
											</div>

											<div class="mt-3">
												<div><i class="fa fa-credit-card"></i> <?=lang('messages_lang.plafond')?>  : <?= number_format($info['TRANSFERTS_CREDITS'],'0',' ',' ') ?> FBU </div>
											</div>

											<input class="form-control" type="hidden" id="sum_montant_transfert" value="<?=$summ_transfert['montant_transfert']?>">
											<input class="form-control" type="hidden" id="summ_transfert_act" value="<?= $summ_transfert_act['montant_transfert_activte']?>">
											<input class="form-control" name="institustion_transfert" type="hidden"  value="<?= $instution['INSTITUTION_ID']?> ">
											<input type="hidden" class="form-control" min="0"value="<?= $info['TRANSFERTS_CREDITS'] ?>" id="transfert_credit" name="transfert_credit" readonly>
											<input type="hidden" name="ligne"value="<?= $info['IMPUTATION']?>" class="form-control" readonly>
											<hr>
											<div style="border:1px solid #ddd;margin-bottom:20px">
												<center>Origine</center>
												<hr class="w-25">
												<div class="row container mt-3">
													<div id="activite2" class="col-md-4">
														<div class='form-froup'>
															<label class="form-label"> <?=lang('messages_lang.labelle_activie_origine')?> <font color="red">*</font></label>
															<select name="PTBA_ID" id="PTBA_ID" class=" select2 form-control" onchange="get_mont()">
																<option value=""><?=lang('messages_lang.labelle_selecte')?></option>
																<?php  
																foreach($activite as $act)
																{
																	?>
																	<option value="<?= $act->PTBA_ID ?>"><?= $act->ACTIVITES ?></option>
																	<?php
																}
																?>
															</select>
															<?php if (isset($validation)) : ?>
																<?= $validation->getError('PTBA_ID'); ?>
															<?php endif ?>
															<font color="red" id="error_PTBA_ID"></font>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for=""><?=lang('messages_lang.trim_origine')?> <font color="red">*</font></label>
															<select onchange="getMontantAnnuel()" name="TRANCHE_ID" id="TRANCHE_ID" class="form-control">
																<option value=""><?=lang('messages_lang.labelle_selecte')?></option>
																<?php foreach($tranches as $tr):?>
																	<option value="<?= $tr->TRANCHE_ID ?>"> <?= $tr->DESCRIPTION_TRANCHE ?></</option>
																<?php endforeach ?>
															</select>
														</div>
														<font color="red" id="trimestre_error"></font>
														<?php if (isset($validation)) : ?>
															<?= $validation->getError('TRANCHE_ID'); ?>
														<?php endif ?>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for=""> <?=lang('messages_lang.mont_a_transf')?><font color="red">*</font></label>
															<input type="number" class="form-control" id="transfert_id1" onkeyup="valide_montant();" name="transfert_transferer" min=0 oninput="validity.valid||(value='');">
															<font color="red" id="error_montant_traansfer"></font>
															<font color="red" id="error_montant_traansfer_valide">
															</font>
														</div>
													</div>
												</div> 

												<div class="row container">
													<div class="col-md-4">
														<div class="form-group">
															<label id="montant_vote_label" for=""><?=lang('messages_lang.labelle_montant_vote')?></label>
															<input type="" class="form-control" id="mont" name="montant_vote" readonly>
															<font color="red" id="montant_error"></font>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label for=""><?=lang('messages_lang.labelle_montant_apres_transfert')?> <font color="red">*</font></label>
															<input readonly type="number" class="form-control" min="0" id="apres_transfert_id" name="apres_transfert" >
															<font color="red" id="montant_error"></font>
															<font color="red" id="montant_error_mont"></font>
														</div>
													</div>

													<div hidden class="col-md-4" id="transfert_info">
														<div class="form-group">
															<label for=""><?=lang('messages_lang.mont_rest_mont_trans')?></label>
															<input readonly type="" class="form-control" value=""  name="transfert_id2" id="transfert_id23">
														</div>
													</div>
												</div>
												<hr>

												<center><?=lang('messages_lang.label_destin')?></center>
												<hr class="w-25">
												<div class="row container mt-3">
													<div class="col-md-4">
														<div class="form-group">
															<label class="form-label"><?=lang('messages_lang.min_dest')?> <font color="red">*</font></label>
															<select class="select2 form-control" id="ministere_id" name="ministere" onchange="get_code()">
																<option value=""><?=lang('messages_lang.labelle_selecte')?></option>
																<?php foreach($ministere as $min):?>
																	<option value="<?= $min->INSTITUTION_ID ?>"><?= $min->DESCRIPTION_INSTITUTION ?></option>
																<?php endforeach ?>
															</select>
															<font color="red" id="error_ministere"></font>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="form-label"> <?=lang('messages_lang.ligne_budg_dest')?> <font color="red">*</font></label>
															<select class="select2 form-control" id="LIGNE_BUDG_ID" name="LIGNE_BUDGETAIRE_TRANSFERT" onchange="get_activite(),summation_activite()">
															</select>
															<font color="red" id="error_line"></font>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="form-label"> <?=lang('messages_lang.act_dest')?> <font color="red">*</font></label>
															<select class="select2 form-control" id="aactiv_transfert_id" name="aactiv_transfert" onchange="get_mont2()">
															</select>
															<font color="red" id="error_act_transfert"></font>
														</div>
													</div>

													<div class="col-md-4 ">
														<div class="form-group">
															<label for=""><?=lang('messages_lang.labelle_observartion')?></label>
															<select name="observation" id="observation_id" class="select2 form-control">
																<option value=""><?=lang('messages_lang.labelle_selecte')?></option>
																<?php foreach($observation as $observa):?>
																	<option value="<?=  $observa->OBSERVATION_FINANCIER_ID ?>"><?= $observa->DESC_OBSERVATION_FINANCIER ?></option>
																<?php endforeach ?>
															</select>
															<font color="red" id="observation_error"></font>
														</div>
													</div>
												</div>

												<div class="row container">
													<div class="col-md-4 mt-4">
														<div class="form-group">
															<label for=""><?=lang('messages_lang.mont_a_rec')?> <font color="red">*</font></label>
															<input type="number" class="form-control" min="0" onkeyup='get_montant_precis();valide_montant3()' id="mont_precis_transfert" name="mont_precise_transfert">
															<font color="red" id="montant_error"></font>
															<font color="red" id="montant_error_mont"></font>
															<font color="red" id="montant_activite_precis"></font>
														</div>
													</div>

													<div class="col-md-4 mt-4">
														<div class="form-group">
															<label for=""> <?=lang('messages_lang.mont_vote_lign_dest')?> <font color="red">*
															</font></label>
															<input readonly type="number" class="form-control" min="0" id="montant_ligne_transfert"
															name="montant_vote_lbtransfert">
														</div>
													</div>

													<div class="col-md-4 mt-4">
														<div class="form-group">
															<label for=""> <?=lang('messages_lang.montant_vot_act_dest')?> <font color="red">*</font>
															</label>
															<input type="text" class="form-control" min="0" name="montant_vote_active_transfert" id="montant_vote_active_transfert" readonly>
														</div>
													</div>

													<div class="col-md-4" >
														<div class="form-group">
															<label for=""><?=lang('messages_lang.mont_apre_trans_act_dest')?><font color="red">*</font></label> 
															<input readonly type="number" class="form-control" min="0" id="mont_precis_apres_transfert" name="mont_precise_apres_transfert">
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label><?=lang('messages_lang.mont_tot_apr_transf')?> <font color="red">*</font></label>
															<input readonly type="number" class="form-control" min="0" id="mtapres_transfert"
															name="montant_apres_transfert">
															<font color="red" id="montant_error"></font>
															<font color="red" id="montant_error_mont"></font>
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12  " id="savetempo_transfer" style="margin-bottom:100px">
												<div class="form-group ">
													<a onclick="savetemp()" id="btn_save" class="btn" style="float:right;background:#061e69;color:white"><?=lang('messages_lang.bouton_ajouter')?></a>
												</div>
											</div>
										</form>

										<div class="container" style="width:98%">
											<?php if(!empty($transfert)):?>
												<form id="Myform2" action="<?= base_url('transfert_new/Transfert/save_transfert')?>" method="post">
													<input type="hidden" name="sum_montant_transfert" id="sum_montant_transfert" value="<?=$montant_restants['transfert']?>">
													<input type="hidden" name="montant_credit"value="<?=$info['TRANSFERTS_CREDITS'] ?>">
													<input type="hidden" value="<?= $info['EXECUTION_BUDGETAIRE_ID']?>" name="EXECUTION_BUDGETAIRE_ID" class="form-control">
													<div class="table-responsive mt-5">
														<table class="table table-bordered">
															<thead style="background-color:#061e69;color:white">
																<th><?lang('messages_lang.act_donatr')?></th>
																<th><?lang('messages_lang.inst_donatr')?></th>
																<th><?lang('messages_lang.mont_trensf')?></th>
																<th><?lang('messages_lang.act_recept')?></th>
																<th><?lang('messages_lang.montant_rec')?></th>
																<th><?lang('messages_lang.inst_recept')?></th>
																<th><?=lang('messages_lang.labelle_UTILISATEUR')?></th>
																<th>Option</th>
															</thead>
															<tbody>
																<?php foreach($transfert as $infos):?>
																	<tr>
																		<td><?= $infos->activite_transfert ?></td>
																		<td><?= $infos->transfert_inst ?></td>
																		<td><?= number_format($infos->MONTANT_TRANSFERT,'0',',',' ') ?></td>
																		<td><?= $infos->activite_reception ?></td>
																		<td><?= number_format($infos->MONTANT_RECEPTION,'0',',',' ')  ?></td>
																		<td><?= $infos->transfert_rec ?></td>
																		<td><?= $infos->NOM.'&nbsp;&nbsp;'.$infos->PRENOM ?></td>
																		<td><a href="<?php echo base_url('transfert_new/Transfert/deleteData/'.$infos->TRANSFERT_ID.'/'.$info['EXECUTION_BUDGETAIRE_ID'])?>" class="btn btn-danger"><i class="fa fa-close "></i></a></td>
																	</tr>
																<?php endforeach ?>
															</tbody>
															<tfoot>
															</tfoot>
														</table>
													</div>

													<div style="float:right;margin-bottom:70px" >
														<button class="btn mt-3" style="background:#061e69;color:white">Enregistrer</button>
													</div>
												</form>
											<?php endif ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</main>
		</div>
	</div>
	<?php echo view('includesbackend/scripts_js.php');?>
	<?php echo view('includesbackend/Transfert_script.php');?>
</body>
</html>
<script>
	$(document).ready(function() 
	{
		get_mont()
		var transfert_id1 = $('#transfert_id1').val();
		var sum_montant_transfert = $('#sum_montant_transfert').val();
		var transfert_informa=$('#transfert_id23').val();
		if(transfert_informa=='')
		{
			$('#transfert_info').hide();
		}
		else
		{
			$('#transfert_info').show();
		}

		var summ_transfert_act = $('#summ_transfert_act').val();
		var transfert_credit = $('#transfert_credit').val();
		if((parseInt(sum_montant_transfert) == parseInt(transfert_credit))) 
		{
			var montant_trouve_ttransfer = parseInt(sum_montant_transfert) - parseInt(summ_transfert_act)
			$('#transfert_id1').attr('disabled', true)
			$('#PTBA_ID').prop('disabled', true)
			$('#transfert_id23').val(montant_trouve_ttransfer);
		}

		if((parseInt(sum_montant_transfert) == parseInt(transfert_credit)) && (parseInt(summ_transfert_act) ==
			parseInt(transfert_credit)))
		{
			$('#savetempo_transfer').hide();
		}

		if(parseInt(summ_transfert_act) >= parseInt(transfert_credit))
		{
			$('#transfert_id1').attr('disabled',true);
		}
	});
</script>

<script>
	function savetemp()
	{
		statut = true;
		var montant_vote_id = $('#mont').val();
		var transfert = $('#transfert_id1').val();
		var ministere = $('#ministere_id').val();

		if(transfert == '')
		{
			$('#error_montant_traansfer_valide').html('<?=lang('messages_lang.message_champs_obligatoire')?>');
			statut = false;
		}
		else
		{
			$('#error_montant_traansfer_valide').html('');
		}

		if(ministere == '')
		{
			$('#error_ministere').html('<?=lang('messages_lang.message_champs_obligatoire')?>');
			statut = false;
		}
		else
		{
			$('#error_ministere').html('');
		}

		if(parseInt(transfert) > parseInt(montant_vote_id))
		{
			statut = false;
			$('#error_montant_traansfer').html('<?=lang('messages_lang.message_trans_sup_mont_vot')?>');
		}
		else
		{
			$('#error_montant_traansfer').html('');
		}

		if(statut == true)
		{
			$('#Myform').submit();
		}
	}
</script>

<script>
	/*fonction pour  recuperer les code budgetaires */
	function get_code()
	{
		statut = true
		var ministere = $('#ministere_id').val();

		$.ajax(
		{
			url: "<?=base_url()?>/transfert_new/Transfert/get_code/" + ministere,
			type: "POST",
			dataType: "JSON",
			success: function(data)
			{
				$('#LIGNE_BUDG_ID').html(data.code);
			}
		});
	}
</script>

<script>
	/* fonction pour  recuperer les activites selon les lignes  budgetaires */
	function get_activite()
	{
		statut = true
		var LIGNE_BUDG_ID = $('#LIGNE_BUDG_ID').val();
		$.ajax(
		{
			url: "<?=base_url()?>/transfert_new/Transfert/get_activite/" + LIGNE_BUDG_ID,
			type: "POST",
			dataType: "JSON",
			success: function(data)
			{
				$('#aactiv_transfert_id').html(data.activites);
			}
		});
	}
</script>

<script>
	/* fonction pour recuper les montqnt selon id ptba */
	function get_mont()
	{
		statut = true
		var PTBA_ID = $('#PTBA_ID').val();
		$.ajax(
		{
			url: "<?=base_url()?>/transfert_new/Transfert/get_montantss/" + PTBA_ID,
			type: "GET",
			dataType: "JSON",
			success: function(data)
			{
				$('#mont').val(data.mont);
			}
		});
	}
</script>

<script>
	/* fonction pour recuper les montqnt selon id ptba */
	function get_mont2()
	{
		statut = true
		var PTBA_ID = $('#aactiv_transfert_id').val();
		$.ajax(
		{
			url: "<?=base_url()?>/transfert_new/Transfert/get_montant_act_transfert/" + PTBA_ID,
			type: "GET",
			dataType: "JSON",
			success: function(data)
			{
				$('#montant_vote_active_transfert').val(data.montant);
			}
		});
	}
</script>

<script>
	/* fonction pour recuperer la sommation du montant selon  le code noomanclature slectionner */
	function summation_activite()
	{
		statut = true
		var LIGNE_BUDG_ID = $('#LIGNE_BUDG_ID').val();
		$.ajax(
		{
			url: "<?=base_url()?>/transfert_new/Transfert/get_summ_activite/" + LIGNE_BUDG_ID,
			type: "POST",
			dataType: "JSON",
			success: function(data)
			{
				$('#montant_ligne_transfert').val(data.activite_sum);
				var transfert=$('#transfert_id1').val();
				var montant_ligne_transfert = $('#montant_ligne_transfert').val();
				var total_apres_transfer_lignebudgt = (parseInt(transfert) + parseInt(montant_ligne_transfert))
				$('#mtapres_transfert').val(total_apres_transfer_lignebudgt);
			}
		});
	}
</script>

<script>
	$('#message').delay('slow').fadeOut(3000);
</script>

<script>
	function valide_montant2()
	{
		var transfert = $('#transfert_id1').val();
		var transfert_credit = $('#transfert_credit').val();
		if (parseInt(transfert) > parseInt(transfert_credit))
		{
			statut = false;
			$('#error_montant_traansfer').html('<?=lang('messages_lang.messa_trans_sup_trans_credit')?>');
		}
		else
		{
			$('#error_montant_traansfer').html('');
		}
	}
</script>

<script>
	/*la on va trouver tous les controlles concerant les montants */
	function valide_montant()
	{
		statut = true;
		var montant_vote_id = $('#mont').val();
		var transfert = $('#transfert_id1').val();
		var transfert_credit = $('#transfert_credit').val();

		var total = parseInt(montant_vote_id) - transfert;
		$('#apres_transfert_id').val(total);

		if(parseInt(transfert) > parseInt(montant_vote_id))
		{
			statut = false;
			$('#error_montant_traansfer').html('<?=lang('messages_lang.message_trans_sup_mont_vot')?>')
		}
		else
		{
			$('#error_montant_traansfer').html('')
		}
	}
</script>

<script>
	/* fonction pour recuperer les montant precis sur l activite qu on veux effectuer le transfert */
	function get_montant_precis()
	{
		var mont_precis_transfert = $('#mont_precis_transfert').val();
		var montant_vote_active_transfert = $('#montant_vote_active_transfert').val();
		var total = parseInt(mont_precis_transfert) + parseInt(montant_vote_active_transfert)
		$("#mont_precis_apres_transfert").val(total);
	}
</script>