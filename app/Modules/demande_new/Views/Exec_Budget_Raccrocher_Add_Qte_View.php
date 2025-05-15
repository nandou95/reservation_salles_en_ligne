<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation(); ?>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
    <script src="/DataTables/datatables.js"></script>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
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
                  <a href="<?php echo base_url('demande_new/Exec_Budget_Raccrocher_Trim2/')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?= lang('messages_lang.link_list')?></a>
                </div>
                <div class="car-body">

                  <h4 style="margin-left:4%;margin-top:10px"> <?=lang('messages_lang.prec_qte')?></h4>
                  <div class="table-responsive container " style="margin:15px">
                    <form method="post" name="myQte" id="myQte" action="<?= base_url('demande_new/Exec_Budget_Raccrocher_Trim2/save') ?>" class="form-group row needs-validation p-5" enctype="multipart/form-data">

                      <div class="container">
                        <div class="row">

                          <input type="hidden" name="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID"  id="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID" value="<?=$info['EXECUTION_BUDGETAIRE_RACCROCHAGE_ID']?>">
                          <input type="hidden" name="UNITE" id="UNITE" value="<?=$info['UNITE']?>">
                          <input type="hidden" name="MOUVEMENT_DEPENSE_ID" id="MOUVEMENT_DEPENSE_ID" value="<?=$info['MOUVEMENT_DEPENSE_ID']?>">

                          <div class=" table-responsive ">
                            <table class="table m-b-0 m-t-20">
                              <tbody>
                                <tr>
                                  <td style="width:200px ;"><font style="float:left;"><i class="fa fa-building"> </i>&nbsp;<?=lang('messages_lang.labelle_institution')?></font></td>
                                  <td><strong><font style="float:left;"><?= !empty($info['CODE_MINISTERE']) ? $info['CODE_MINISTERE'].'&nbsp;-&nbsp;' : 'N/A - ' ;?> <?= !empty($info['INTITULE_MINISTERE']) ? $info['INTITULE_MINISTERE'] : 'N/A' ;?></font></strong></td>
                                </tr>
                                <tr>
                                  <td style="width:200px ;"><font style="float:left;"><i class="fa fa-home"> </i><?=lang('messages_lang.table_st')?></font></td>
                                  <td><strong><font style="float:left;"><?= $sous_tutel['CODE_SOUS_TUTEL'].'&nbsp;-&nbsp;'.$sous_tutel['DESCRIPTION_SOUS_TUTEL']?></font></strong></td>
                                </tr>
                                <tr>
                                  <td style="width:200px ;"><font style="float:left;"><i class="fa fa-certificate"> </i>&nbsp;<?=lang('messages_lang.table_Programme')?> </font></td>
                                  <td><strong><font style="float:left;"><?= $info['CODE_PROGRAMME'].'&nbsp;-&nbsp;'.$info['INTITULE_PROGRAMME']?></font></strong></td>
                                </tr>
                                <tr>
                                  <td style="width:200px ;"><font style="float:left;"><i class="fa fa-certificate"> </i>&nbsp;<?=lang('messages_lang.table_Action')?> </font></td>
                                  <td><strong><font style="float:left;"><?= $info['CODE_ACTION'].'&nbsp;-&nbsp;'.$info['LIBELLE_ACTION']?></font></strong></td>
                                </tr>

                                <tr>
                                  <td style="width:200px ;"><font style="float:left;"><i class="fa fa-cubes"> </i><?=lang('messages_lang.label_ligne')?></font></td>
                                  <td><strong><font style="float:left;"><?= $info['IMPUTATION']?></font></strong></td>
                                </tr>
                                
                                <tr>
                                  <td style="width:200px ;"><font style="float:left;"><i class="fa fa-cogs"> </i>&nbsp;<?=lang('messages_lang.label_activite')?></font></td>
                                  <td><strong><font style="float:left;"><?=$info['ACTIVITES']?></font></strong></td>
                                </tr>

                                <tr>
                                  <td style="width:200px ;"><font style="float:left;"><i class="fa fa-file-text"></i><?=lang('messages_lang.etat_exec')?></font></td>
                                  <td><strong><font style="float:left;"><?= $info['DESC_MOUVEMENT_DEPENSE']?></font></strong></td>
                                </tr>
                              </tbody>
                            </table>        
                          </div>

                        </div>
                        <hr>
                        <div class="row">
                          <div class="col-md-12 card shadow">
                            <div class="table-responsive  mt-3">
                              <table class="table table-bordered">
                                <thead class="bg-dark text-white">
                                  <th><?=lang('messages_lang.label_qte_vot')?></th>
                                  <th><?=lang('messages_lang.label_unity')?></th>
                                  <th><?=lang('messages_lang.real_budg')?></th>
                                  <th><?=lang('messages_lang.real_juri')?></th>
                                  <th><?=lang('messages_lang.real_liqui')?></th>
                                  <th><?=lang('messages_lang.real_ord')?></th>
                                  <th><?=lang('messages_lang.real_paie')?></th>
                                  <th><?=lang('messages_lang.real_dec')?></th>
                                  

                                </thead>
                                <tbody>
                                  <tr>
                                    <td><?= $qte_vote?></td>
                                    <td><?= $info['UNITE']?></td>
                                    <td><?= number_format($info['MONTANT_RACCROCHE'],2,',',' ')?></td>  
                                    <td><?= number_format($info['MONTANT_RACCROCHE_JURIDIQUE'],2,',',' ')?></td>
                                    <td><?= number_format($info['MONTANT_RACCROCHE_LIQUIDATION'],2,',',' ')?></td>
                                    <td><?= number_format($info['MONTANT_RACCROCHE_ORDONNANCEMENT'],2,',',' ')?></td>
                                    <td><?= number_format($info['MONTANT_RACCROCHE_PAIEMENT'],2,',',' ')?></td>
                                    <td><?= number_format($info['MONTANT_RACCROCHE_DECAISSEMENT'],2,',',' ')?></td>  
                                    
                                    
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                        <hr >
                        <div class="row">


                          <div class="col-md-6">
                            <label for=""> <?=lang('messages_lang.qte_realise_minus')?> <font color="red" >*</font></label>
                            <input type="text" min="0" maxlength="255"class="form-control allownumericwithdecimal" id="QTE_RACCROCHE" name="QTE_RACCROCHE">
                            <span class="text-danger" id="error_qte"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('QTE_RACCROCHE'); ?>
                            <?php endif ?>

                          </div>
                          <div class="col-md-6">

                            <label for=""> <?=lang('messages_lang.label_unity')?></label>
                            <input type="text" class="form-control"  id="UNITE" name="UNITE" value="<?=$info['UNITE']?>" readonly>
                          </div>
                        </div><br>
                        <div class="row">
                          <div class="col-md-6">
                            <label for=""> <?=lang('messages_lang.label_observ')?> </label>
                            <textarea type="text" class="form-control" id="COMMENTAIRE" name="COMMENTAIRE"></textarea>
                          </div>
                        </div>
                        <br>
                        <div class="row">
                          <div class="col-12">
                           <div class="col-10" style="float: left;">
                            <h1 class="header-title text-dark">

                            </h1>
                          </div>
                          <div class="col-2" style="float: right;">
                            <a onclick="save_qte();" style="float: right;margin: 2px" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> <?=lang('messages_lang.bouton_ajouter')?></a>
                            <!--  -->  
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>

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
<script type="text/javascript">
 $(".allownumericwithdecimal").on("keypress keyup blur",function (event) {
  $(this).val($(this).val().replace(/[^0-9\.|\,]/g,''));
  debugger;
  if(event.which == 44)
  {
    return true;
  }
  if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57  )) {

    event.preventDefault();
  }
});
</script>
<script type="text/javascript">
  function save_qte()
  {
    var statut = 2;
    var QTE_RACCROCHE = $('#QTE_RACCROCHE').val();
    var UNITE = $('#UNITE').val();
    var COMMENTAIRE = $('#COMMENTAIRE').val();

    if (QTE_RACCROCHE == '') 
    {
      $('#error_qte').html('<?=lang('messages_lang.labelle_et_error')?>');
      statut = 1;
    }

    if (QTE_RACCROCHE < 0) 
    {
      $('#error_qte').html('<?=lang('messages_lang.qte_not_negati')?>');
      statut = 1;
    }

    var url;

    if(statut == 2)
    {
     $('#QTE_RACCROCHE_valide').html(QTE_RACCROCHE);
     $('#UNITE_valide').html(UNITE);

     if (COMMENTAIRE == '') 
     {
       $('#COMMENTAIRE_valide').html('-');
     }else{
      $('#COMMENTAIRE_valide').html(COMMENTAIRE);
    }

    $("#quantification").modal("show");

  }
}
</script>

<div class="modal fade" id="quantification" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-xl">
    <div class="modal-content">
      <div class="modal-body">


        <div class="table-responsive  mt-3">
          <table class="table table-bordered">
            <thead class="bg-dark text-white">
              <th><?=lang('messages_lang.qte_realise_minus')?></th>
              <th><?=lang('messages_lang.label_unity')?></th>
              <th><?=lang('messages_lang.label_observ')?></th>

            </thead>
            <tbody>
              <tr>
                <td id="QTE_RACCROCHE_valide"></td>
                <td id="UNITE_valide"></td>
                <td id="COMMENTAIRE_valide"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?=lang('messages_lang.bouton_modifier')?></button>
        <a onclick="save()" style="float: right;margin: 2px" class="btn btn-info"><i class="fa fa-save" aria-hidden="true"></i> <?=lang('messages_lang.bouton_enregistrer')?></a>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
  function save()
  {
   document.getElementById("myQte").submit();
 }
</script>
