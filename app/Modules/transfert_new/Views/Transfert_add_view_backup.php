<!DOCTYPE html>
<html lang="en">
<head>
	<?php echo view('includesbackend/header.php'); ?>
</head>
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
			<main class="content">
				<div class="container-fluid">
					<div class="header">
						<h1 class="header-title text-white"></h1>
					</div>

					<div class="row">
						<div class="col-12">
							<div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
								<div style="float: right;">
									<a href="<?php echo base_url('transfert_new/Transfert/')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i>Liste</a>
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
									<h4 style="margin-left:4%;margin-top:10px">
										<?=lang('messages_lang.label_trans_money')?></h4>
									<br>

									<div class=" container " style="width:90%">
										<form id="Myform" action="<?=base_url('transfert_new/Transfert/enregistre_tempo')?>"
											method="post">
											<div class="row">
												<input type="hidden" value="<?= $info['EXECUTION_BUDGETAIRE_ID']?>"
												name="id" class="form-control">
												<div class="col-md-12 mt-3 ml-2" style="margin-bottom:50px">
													<div class="row">
														<div class="col-md-4">
															<div class='form-froup'>
																<label class="form-label"><?=lang('messages_lang.label_ligne')?><font color="red">*</font></label>
																<input type="text" name="ligne" value="<?= $info['IMPUTATION']?>" class="form-control" readonly>
																<font color="red" id="error_PTBA_ID"></font>
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label for=""> <?=lang('messages_lang.label_mont_credit')?> <font color="red">*</font></label>
																<input type="number" class="form-control" min="0" value="<?= $info['TRANSFERTS_CREDITS']?>" id="transfert_credit" name="transfert_apres_credit" readonly>
																<font color="red" id="montant_error"></font>
																<font color="red" id="montant_error_mont"></font>
															</div>
														</div>

														<div class="col-md-4">
															<div class='form-froup'>
																<label class="form-label"> <?=lang('messages_lang.labelle_activite')?> <font color="red">*</font></label>
																<select name="PTBA_ID" id="PTBA_ID" class=" select2 form-control" onchange="get_mont()">
																	<option value=""><?=lang('messages_lang.labelle_selecte')?></option>
																	<?php
																	foreach($activite as $act)
																	{
																		?>
																		<?php
																		if($act->PTBA_ID==set_value('PTBA_ID'))
																		{
																			?>
																			<option value="<?= $act->PTBA_ID ?>"selected><?= $act->ACTIVITES ?></option>
																			<?php
																		}
																		else
																		{
																			?>
																			<option value="<?= $act->PTBA_ID ?>"><?= $act->ACTIVITES ?></option>
																			<?php
																		}
																	}
																	?>
																</select>
																<font color="red" id="error_PTBA_ID"></font>
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label for=""><?=lang('messages_lang.labelle_montant_vote')?></label>
																<input type="" class="form-control" id="mont" name="montant_vote" readonly>
																<font color="red" id="montant_error"></font>
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label for=""> <?=lang('messages_lang.label_mont_trans')?> <font color="red">*</font></label>
																<input type="number" class="form-control" value="<?=set_value('transfert_transferer') ?>" min="0" id="transfert_id1" onKeyDown="valide_montant();valide_montant2();" name="transfert_transferer">
																<font color="red" id="error_montant_traansfer"></font>
																<font color="red" id="error_montant_traansfer_valide">
																</font>
															</div>
														</div>

														<div class="col-md-4" id="transfert_info">
															<div class="form-group">
																<label for=""><?=lang('messages_lang.mont_rest_mont_trans')?></label>
																<input type=""  class="form-control" value="<?=set_value('transfert_id2') ?>"  name="transfert_id2" id="transfert_id23">
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label for=""> <?=lang('messages_lang.labelle_montant_apres_transfert')?> <font color="red">*</font></label>
																<input readonly type="number" value="<?=set_value('apres_transfert') ?>" class="form-control" min="0" id="apres_transfert_id" name="apres_transfert">
																<font color="red" id="montant_error"></font>
																<font color="red" id="montant_error_mont"></font>
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label class="form-label"> <?=lang('messages_lang.minister')?> <font color="red">*</font></label>
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
																<label class="form-label"> <?=lang('messages_lang.ligne_trans')?><font color="red">*</font></label>
																<select class="select2 form-control" id="LIGNE_BUDG_ID" name="LIGNE_BUDGETAIRE_TRANSFERT" onchange="get_activite(),summation_activite()">
																</select>
																<font color="red" id="error_"></font>
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label for=""> <?=lang('messages_lang.mont_total')?> <font color="red">*</font></label>
																<input readonly type="number" class="form-control" min="0" id="montant_ligne_transfert"name="montant_vote_lbtransfert">
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label for=""> <?=lang('messages_lang.mont_tot_apr_transf')?> <font color="red">*</font></label>
																<input readonly type="number" class="form-control" min="0" id="mtapres_transfert" name="montant_apres_transfert">
																<font color="red" id="montant_error"></font>
																<font color="red" id="montant_error_mont"></font>
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label class="form-label"> <?=lang('messages_lang.activit_trans')?><font color="red">*</font></label>
																<select class="select2 form-control" id="aactiv_transfert_id" name="aactiv_transfert" onchange="get_mont2()">
																</select>
																<font color="red" id="error_marche"></font>
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label for=""> <?=lang('messages_lang.labelle_montant_vote')?> <font color="red">*</font></label>
																<input type="text" class="form-control" min="0" name="montant_vote_active_transfert" id="montant_vote_active_transfert" readonly>
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label for=""><?=lang('messages_lang.label_preciz_mont')?> <font color="red">*</font></label>
																<input type="number" class="form-control" min="0" onkeyDown='get_montant_precis()' id="mont_precis_transfert" name="mont_precise_transfert">
																<font color="red" id="montant_error"></font>
																<font color="red" id="montant_error_mont"></font>
															</div>
														</div>

														<div class="col-md-4">
															<div class="form-group">
																<label for=""> <?=lang('messages_lang.apres_preciz_mont')?><font color="red">*</font></label>
																<input readonly type="number" class="form-control" min="0" id="mont_precis_apres_transfert" name="mont_precise_apres_transfert">
															</div>
														</div>

														<input class="form-control" type="hidden" id="sum_montant_transfert" value="<?=$summ_transfert['montant_transfert']?>">
														<input class="form-control" type="hidden" id="summ_transfert_act" value="<?= $summ_transfert_act['montant_transfert_activte']?>">

														<div class="col-md-12 mt-5" id="savetempo_transfer">
															<div class="form-group">
																<a onclick="savetemp()" id="btn_save" class="btn"
																style="float:right;background:#061e69;color:white">Ajouter</a>
															</div>
														</div>
													</div>
												</div>
											</div>
										</form>
										<?php if(!empty($transfert)):?>
											<form id="Myform2" action="<?=base_url('transfert_new/Transfert/save_transfert')?>" method="post">
												<input type="hidden" name="sum_montant_transfert" id="sum_montant_transfert"
												value="<?=$summ_transfert['montant_transfert']?>">

												<input type="hidden" value="<?= $info['EXECUTION_BUDGETAIRE_ID']?>" name="EXECUTION_BUDGETAIRE_ID" class="form-control">
												<div class="table-responsive mt-5">
													<table class="table table-bordered">
														<thead>
															<th><?=lang('messages_lang.label_ligne')?></th>
															<th><?=lang('messages_lang.labelle_activites')?></th>
															<th><?=lang('messages_lang.label_mont_credit')?> </th>
															<th><?=lang('messages_lang.labelle_montant_vote')?></th>
															<th><?=lang('messages_lang.label_mont_trans')?></th>
															<th><?=lang('messages_lang.labelle_montant_apres_transfert')?></th>
															<th><?=lang('messages_lang.minister')?></th>
															<th><?=lang('messages_lang.ligne_trans')?> </th>
															<th><?=lang('messages_lang.mont_total')?></th>
															<th><?=lang('messages_lang.mont_tot_apr_transf')?></th>
															<th><?=lang('messages_lang.activit_trans')?></th>
															<th><?=lang('messages_lang.labelle_montant_vote')?></th>
															<th><?=lang('messages_lang.argent_trans')?></th>
															<th><?=lang('messages_lang.argent_apres_trans')?></th>
															<th><?=lang('messages_lang.labelle_option')?></th>
														</thead>
														<tbody>
															<?php foreach($transfert as $infos):?>
																<tr>
																	<td><?=$infos->LIGNE_BUDGETAIRE?></td>
																	<?php
																	if(strlen($infos->activite_line) > 12)
																	{ 
																		$activite_line =  substr($infos->activite_line, 0, 12) .'...<a class="btn-sm" title="Afficher" data-toggle="modal" data-target="#institution'.$infos->ID_TRANSFERT.'" data-toggle="tooltip" ><i class="fa fa-eye"></i></a>';
																	}
																	else
																	{
																		$activite_line =  $infos->activite_line;
																	}
																	?>
																	<td><?= $activite_line ?></td>
																	<td><?=number_format($infos->MONTANT_CREDIT_TRANSFERT ,'0',',',' ')?></td>
																	<td><?=number_format($infos->MONTANT_VOTE,'0',',',' ' )?></td>
																	<td><?=number_format($infos->MONTANT_A_TRANSFERE,'0',',',' ' )?></td>
																	<td><?=number_format($infos->MONTANT_APRES_TRANSFERT,'0',',',' ' )?></td>
																	<td><?=$infos->DESCRIPTION_INSTITUTION ?></td>
																	<td><?=$infos->code_transfert ?></td>
																	<td><?=number_format($infos->MONTANT_TOTAL_VOTE_PAR_LIGNE,'0',',',' ' )?></td>
																	<td><?=number_format($infos->MONTANT_TOTAL_APRES_TRANSFERT_PAR_LIGNE_BUDG,'0',',',' ')?></td>
																	<td><?=$infos->activite_trnasfert ?></td>
																	<td><?=number_format($infos->MONTANT_VOTE_ACTIVITE_TRANSFERT,'0',',',' ') ?></td>
																	<td><?=number_format($infos->MONTANT_PRECIS_ACTIVITE,'0',',',' ') ?></td>
																	<td><?=number_format($infos->MONTANT_PRECIS_APRES_TRANSFERT,'0',',',' ')?></td>
																	<td><a href="<?php echo base_url('transfert_new/Transfert/deleteData/'.$infos->ID_TRANSFERT .'/'.$info['EXECUTION_BUDGETAIRE_ID'] )?>" class="btn btn-danger"><i class="fa fa-close "></i></a></td>
																</tr>
															<?php endforeach ?>
														</tbody>
													</table>
												</div>
												<div style="float:right" class="mt-5">
													<button class="btn" style="background:#061e69;color:white"><?=lang('messages_lang.bouton_enregistrer')?></button>
												</div>
											</form>
										<?php endif ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</main>
		</div>
	</div>
	<?php echo view('includesbackend/scripts_js.php'); ?>
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

		if((parseInt(sum_montant_transfert) == parseInt(transfert_credit)) && (parseInt(summ_transfert_act)==
			parseInt(transfert_credit)))
		{
			$('#savetempo_transfer').hide();
		}

		if (parseInt(summ_transfert_act) >= parseInt(transfert_credit))
		{
			$('#transfert_id1').attr('disabled', true);
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
			$('#error_montant_traansfer_valide').html('<?= lang('messages_lang.error_message') ?>');
			statut = false;
		}
		else
		{
			$('#error_montant_traansfer_valide').html('');
		}

		if(ministere == '')
		{
			$('#error_ministere').html('<?= lang('messages_lang.error_message') ?>');
			statut = false;
		}
		else
		{
			$('#error_ministere').html('');
		}

		if(parseInt(transfert) > parseInt(montant_vote_id))
		{
			statut = false;
			$('#error_montant_traansfer').html('<?= lang('messages_lang.message_trans_sup_mont_vot') ?>')
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
	$(document).ready(function() 
	{
		$('#identifiant').html("<?= lang('messages_lang.Bon_engagement') ?>");
		$('#num_bon_eng').hide();
		$('#date_bon_eng').hide();
		$('#titre_decaiss').hide();
		$('#date_titre').hide();

		var MOUVEMENT_DEPENSE_ID = $('#Mouvement_id').val();
		if(MOUVEMENT_DEPENSE_ID == '')
		{
			$('#montant_realise_id').attr('disabled', true)
		}
	});
</script>

<script>
	/* fonction pour  recuperer les code budgetaires */
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
	/*fonction pour  recuperer les activites selon les lignes  budgetaires */
	function get_activite()
	{
		statut = true;
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
	/*  fonction pour recuperer la sommation du montant selon  le code noomanclature slectionner */
	function summation_activite()
	{
		statut = true;
		var LIGNE_BUDG_ID = $('#LIGNE_BUDG_ID').val();
		$.ajax(
		{
			url: "<?=base_url()?>/transfert_new/Transfert/get_summ_activite/" + LIGNE_BUDG_ID,
			type: "POST",
			dataType: "JSON",
			success: function(data)
			{
				$('#montant_ligne_transfert').val(data.activite_sum);
				var transfert = $('#transfert_id1').val();
				var montant_ligne_transfert = $('#montant_ligne_transfert').val();
				var total_apres_transfer_lignebudgt = (parseInt(transfert) + parseInt(montant_ligne_transfert));
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
		if(parseInt(transfert) > parseInt(transfert_credit))
		{
			statut = false;
			$('#error_montant_traansfer').html('<?= lang('messages_lang.messa_trans_sup_trans_credit') ?>');
		}
		else
		{
			$('#error_montant_traansfer').html('');
		}
	}
</script>

<script>
	/* la on va trouver tous les controlles concerant les montants */
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
			$('#error_montant_traansfer').html('<?= lang('messages_lang.message_trans_sup_mont_vot') ?>');
		}
		else
		{
			$('#error_montant_traansfer').html('');
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