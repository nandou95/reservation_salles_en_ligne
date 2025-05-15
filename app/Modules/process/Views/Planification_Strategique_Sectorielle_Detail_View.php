<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>

</head>
<body>
 <div class="wrapper">
  <?php echo view('includesbackend/navybar_menu.php');?>

  <div class="main">
   <?php echo view('includesbackend/navybar_topbar.php');?>
   <main class="content">
    <div class="container-fluid">

     <div class="row">
      <div class="col-12">
       <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
         
        <!-- <br> -->
        <div class="card-body">

         <form id="my_form" action="" method="POST">
          <div class="card-body">

            <input type="hidden" name="ID_DEMANDE" id="ID_DEMANDE" value="<?=$infoAffiche['ID_DEMANDE']?>">

            <input type="hidden" name="ACTION_ID" id="ACTION_ID">
            <input type="hidden" name="MOVETO" id="MOVETO">
            <input type="hidden" name="ETAPE_ID" id="ETAPE_ID">
            <input type="hidden" name="IS_REQUIRED" id="IS_REQUIRED">

            <!-- Bouton des Actions et liste -->
            <div style="border:0px solid #ddd;border-radius:5px;margin: 5px">
              <div class="row" style="margin :  5px">
                <?php
                if ($infoAffiche['IS_END']!=1)
                {
                ?>
                <div class="col-3">
                  <?php
                  if (!empty($getAction)) {
                  ?>
                  <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><?= lang('messages_lang.choix_action')?>
                    <span class="caret"></span> <span id="loading_popup"></span></button>
                    <ul class="dropdown-menu">
                      <?php
                      foreach ($getAction as $keyEtape)
                      {

                        if ($keyEtape->GET_FORM==1)
                        {
                          ?>
                          <li>&nbsp;&nbsp; >> <a href="<?=base_url(''.$keyEtape->LINK_FORM.''.$keyEtape->ACTION_ID.'/'.md5($infoAffiche['ID_DEMANDE']).'')?>" style="color:#006666;"><?=$keyEtape->DESCR_ACTION?></a></li>
                          <?php
                        }
                        else
                        {
                          ?>
                          <li>&nbsp;&nbsp; >> <a href="#" onclick="traitement(<?=$keyEtape->ACTION_ID?>,<?=$keyEtape->MOVETO?>,<?=$keyEtape->ETAPE_ID?>,<?=$keyEtape->IS_REQUIRED?>)" style="color:#006666;"><?=$keyEtape->DESCR_ACTION?></a></li>
                          <?php
                        }
                      }
                      ?>
                    </ul>
                  </div>
                  <?php
                  }else{
                  ?>
                  <a href="#" onclick="history.go(-1)" class="btn btn-primary"><i class="fa fa-reply-all"></i> Retour </a>
                  <?php
                  }
                  ?>
                </div>
                <?php
                }else{
                ?>
                <div class="col-3"></div>
                <?php
                }
                ?>

                <div class="col-6"></div>
                <div class="col-3">
                <a href="<?=base_url('process/Demandes')?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;Liste</a> 
                </div>
              </div>
            </div>
            <br>

            <!-- Info de base de la demande -->
            <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
              <div class="row" style="margin :  5px">
                <div class="col-12">
                  <div class="table-responsive" style="width: 100%;">
                    <table class=" table table-striped table-bordered">
                        <tr>
                          <th><center><?= lang('messages_lang.code_demande')?></center></th>
                          <th><center><?= lang('messages_lang.th_proc')?> </center></th>
                          <th><center><?= lang('messages_lang.labelle_et_etape')?> </center></th>
                          <th><center><?= lang('messages_lang.th_date_demande')?></center></th>
                          <th><center><?= lang('messages_lang.utilisateur')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                        </tr>
                        <tr>
                          <td><?=!empty($infoAffiche['CODE_DEMANDE']) ? $infoAffiche['CODE_DEMANDE'] : "N/A" ?></td>
                          <td><?=!empty($infoAffiche['NOM_PROCESS']) ? $infoAffiche['NOM_PROCESS'] : "N/A" ?></td>
                          <td><?=!empty($infoAffiche['DESCR_ETAPE']) ? $infoAffiche['DESCR_ETAPE'] : "N/A" ?></td>
                          <td><?=!empty($infoAffiche['DATE_INSERTION']) ? date('d-m-Y',strtotime($infoAffiche['DATE_INSERTION'])) : "N/A" ?></td>
                          <td><?=!empty($infoAffiche['NOM']) ? $infoAffiche['NOM'].' '.$infoAffiche['PRENOM'] : "N/A" ?></td>
                        </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- information detaillé de la demande -->
            <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
              <div class="row" style="margin :  5px">
                <div class="col-12">
                  <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                      <button class="nav-link active" id="pills-tab1-tab" data-toggle="pill" data-target="#pills-tab1" type="button" role="tab" aria-controls="pills-tab1" aria-selected="false"><i class="fa fa-history" aria-hidden="true"></i> <?= lang('messages_lang.treatment_history')?></button>
                      <button class="nav-link" id="pills-tab2-tab" data-toggle="pill" data-target="#pills-tab2" type="button" role="tab" aria-controls="pills-tab2" aria-selected="false"><i class="fa fa-eye" aria-hidden="true"></i> <?= lang('messages_lang.cl_vision')?></button>
                       <button class="nav-link" id="pills-tab3-tab" data-toggle="pill" data-target="#pills-tab3" type="button" role="tab" aria-controls="pills-tab3" aria-selected="true"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.cl_pap_vision')?></button>
                       <button class="nav-link" id="pills-tab4-tab" data-toggle="pill" data-target="#pills-tab4" type="button" role="tab" aria-controls="pills-tab4" aria-selected="false"><i class="fa fa-cubes"></i> <?= lang('messages_lang.cl_cmr_politique')?></button>
                      <button class="nav-link" id="pills-tab5-tab" data-toggle="pill" data-target="#pills-tab5" type="button" role="tab" aria-controls="pills-tab5" aria-selected="false"><i class="fa fa-eye" aria-hidden="true"></i> <?= lang('messages_lang.costab_vision')?></button>
                      <button class="nav-link" id="pills-tab6-tab" data-toggle="pill" data-target="#pills-tab6" type="button" role="tab" aria-controls="pills-tab6" aria-selected="false"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.costab_pap_vision')?></button>
                      <button class="nav-link" id="pills-tab7-tab" data-toggle="pill" data-target="#pills-tab7" type="button" role="tab" aria-controls="pills-tab7" aria-selected="false"><i class="fa fa-cubes" aria-hidden="true"></i> <?= lang('messages_lang.costab_politique')?></button>
                    </div>
                  </nav>

                  <div class="tab-content" id="nav-tabContent">

                    <div style="background-color: white" class="tab-pane show active" id="pills-tab1" aria-labelledby="pills-tab1-tab">
                      <br>
                      <div class="table-responsive" style="width: 100%;">
                        <table id="mytable_historique" class=" table table-striped table-bordered">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th><?= lang('messages_lang.code_demande')?></th>
                              <th><?= lang('messages_lang.labelle_et_etape')?></th>
                              <th><?= lang('messages_lang.labelle_et_action')?></th>
                              <th><?= lang('messages_lang.commentaire')?> </th>
                              <th><?= lang('messages_lang.table_profil')?></th>
                              <th><?= lang('messages_lang.table_utilisateur')?></th> 
                              <th><?= lang('messages_lang.date_trait')?></th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>                          
                      </div>
                    </div>

                    <!-- Fin CL & CMR de la vision -->
                    <div style="background-color: white" class="tab-pane fade" id="pills-tab2" aria-labelledby="pills-tab2-tab">
                      <br>
                      <div class="table-responsive" style="width: 100%;">
                        <table id="mytable_cl_cmr_vision" class=" table table-striped table-bordered">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th><?= lang('messages_lang.th_categorie')?></th>
                              <th><?= lang('messages_lang.label_pilier')?></th>
                              <th><?= lang('messages_lang.th_objectif')?></th>
                              <th><?= lang('messages_lang.th_indiq')?></th>
                              <th><?= lang('messages_lang.th_precise')?></th>
                              <th><?= lang('messages_lang.th_refer')?></th> 
                              <th><?= lang('messages_lang.pip_rapport_institutio_cible_annee1')?></th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>                          
                      </div>
                    </div>
                    <!-- Fin CL & CMR de la vision -->

                    <!-- Debut CL & CMR du PAP du PND aligné à la vision -->
                    <div style="background-color: white" class="tab-pane fade" id="pills-tab3" aria-labelledby="pills-tab3-tab">
                      <br>
                      <div class="table-responsive" style="width: 100%;">
                        <table id="mytable_cl_cmr_pap" class=" table table-striped table-bordered">
                          <thead>
                            <tr>
                              <th>#</th>
                             <th><?= lang('messages_lang.th_categorie')?></th>
                              <th><?= lang('messages_lang.label_pilier')?></th>
                              <th><?= lang('messages_lang.th_objectif')?></th>
                              <th><?= lang('messages_lang.th_indiq')?></th>
                              <th><?= lang('messages_lang.th_precise')?></th>
                              <th><?= lang('messages_lang.th_refer')?></th> 
                              <th><?= lang('messages_lang.pip_rapport_institutio_cible_annee1')?></th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>                          
                      </div>
                    </div>
                    <!-- Fin CL & CMR du PAP du PND aligné à la vision -->

                    <!-- Debut CL & CMR des politiques sectorielles alignées au PND -->
                    <div style="background-color: white" class="tab-pane fade" id="pills-tab4" aria-labelledby="pills-tab4-tab">
                      <br>
                      <div class="table-responsive" style="width: 100%;">
                        <table id="mytable_cl_cmr_politique_sectorielle" class=" table table-striped table-bordered">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th><?= lang('messages_lang.th_categorie')?></th>
                              <th><?= lang('messages_lang.label_pilier')?></th>
                              <th><?= lang('messages_lang.th_objectif')?></th>
                              <th><?= lang('messages_lang.th_indiq')?></th>
                              <th><?= lang('messages_lang.th_precise')?></th>
                              <th><?= lang('messages_lang.th_refer')?></th> 
                              <th><?= lang('messages_lang.pip_rapport_institutio_cible_annee1')?></th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>                          
                      </div>
                    </div>
                    <!-- Fin CL & CMR des politiques sectorielles alignées au PND -->

                    <!-- Debut COSTAB de la vision -->
                    <div style="background-color: white" class="tab-pane fade" id="pills-tab5" aria-labelledby="pills-tab5-tab">
                      <br>
                      <div class="table-responsive" style="width: 100%;">
                        <table id="mytable_costab_vision" class=" table table-striped table-bordered">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th><?= lang('messages_lang.th_categorie')?></th>
                              <th><?= lang('messages_lang.th_enjeu')?></th>
                              <th><?= lang('messages_lang.label_pilier')?></th>
                              <th><?= lang('messages_lang.pip_rapport_institutio_filtre_axe')?></th> 
                              <th><?= lang('messages_lang.th_objectif')?></th>
                              <th><?= lang('messages_lang.th_programme')?></th> 
                              <th><?= lang('messages_lang.th_nom_projet')?></th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>1</th>
                              <th><?= lang('messages_lang.th_budget_annee')?>2</th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>3</th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>4</th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>5</th> 
                              <th><?= lang('messages_lang.th_budget_tot')?></th> 
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>                          
                      </div>
                    </div>
                    <!-- Fin COSTAB de la vision -->

                    <!-- Debut COSTAB du PAP du PND aligné à la vision -->
                    <div style="background-color: white" class="tab-pane fade" id="pills-tab6" aria-labelledby="pills-tab6-tab">
                      <br>
                      <div class="table-responsive" style="width: 100%;">
                        <table id="mytable_costab_pap" class=" table table-striped table-bordered">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th><?= lang('messages_lang.th_categorie')?></th>
                              <th><?= lang('messages_lang.th_enjeu')?></th>
                              <th><?= lang('messages_lang.label_pilier')?></th>
                              <th><?= lang('messages_lang.pip_rapport_institutio_filtre_axe')?></th> 
                              <th><?= lang('messages_lang.th_objectif')?></th>
                              <th><?= lang('messages_lang.th_programme')?></th> 
                              <th><?= lang('messages_lang.th_nom_projet')?></th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>1</th>
                              <th><?= lang('messages_lang.th_budget_annee')?>2</th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>3</th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>4</th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>5</th> 
                              <th><?= lang('messages_lang.th_budget_tot')?></th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>                          
                      </div>
                    </div>
                    <!-- Fin COSTAB du PAP du PND aligné à la vision -->

                    <!-- Debut COSTAB des politiques sectorielles alignées au PND -->
                    <div style="background-color: white" class="tab-pane fade" id="pills-tab7" aria-labelledby="pills-tab7-tab">
                      <br>
                      <div class="table-responsive" style="width: 100%;">
                        <table id="mytable_costab_politique_sectorielle" class=" table table-striped table-bordered">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th><?= lang('messages_lang.th_categorie')?></th>
                              <th><?= lang('messages_lang.th_enjeu')?></th>
                              <th><?= lang('messages_lang.label_pilier')?></th>
                              <th><?= lang('messages_lang.pip_rapport_institutio_filtre_axe')?></th> 
                              <th><?= lang('messages_lang.th_objectif')?></th>
                              <th><?= lang('messages_lang.th_programme')?></th> 
                              <th><?= lang('messages_lang.th_nom_projet')?></th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>1</th>
                              <th><?= lang('messages_lang.th_budget_annee')?>2</th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>3</th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>4</th> 
                              <th><?= lang('messages_lang.th_budget_annee')?>5</th> 
                              <th><?= lang('messages_lang.th_budget_tot')?></th> 
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                        </table>                          
                      </div>
                    </div>
                    <!-- Fin COSTAB des politiques sectorielles alignées au PND -->
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
</main>
</div>
</div>
<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<div class='modal fade' id='addCommentaire' data-backdrop="static" >
  <div class='modal-dialog  modal-lg' style="max-width: 60%">
    <div class='modal-content'>
      <div class="modal-header">
        <h5 class="modal-title"><?=!empty($infoAffiche['DESCR_ETAPE']) ? $infoAffiche['DESCR_ETAPE'] : "N/A" ?></h5>
      </div>
      <div class='modal-body'>
        <div class="row">
          <div class="col-12">
            <label><?= lang('messages_lang.labelle_commentaire')?> <font color="red" id="required"></font> </label>
            <textarea rows="5" name="COMMENTAIRE" id="COMMENTAIRE" class="form-control"></textarea>
            <font id="errorCOMMENTAIRE" color="red"></font>
          </div>
        </div>
      </div>
      <div class='modal-footer'>
        <button class='btn btn-danger btn-md' data-dismiss='modal'><i class="fa fa-close"></i> <?= lang('messages_lang.annuler_modal')?></button>
        <button id="text_btn" onclick="send_data()" type="button" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?= lang('messages_lang.bouton_enregistrer')?></button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function()
  {
    liste_historique(<?=$infoAffiche['ID_DEMANDE'];?>);

    liste_cl_cmr_vision(<?=$infoAffiche['ID_DEMANDE'];?>);
    liste_cl_cmr_pap(<?=$infoAffiche['ID_DEMANDE'];?>);
    liste_cl_cmr_politique_sectorielle(<?=$infoAffiche['ID_DEMANDE'];?>);

    liste_costab_vision(<?=$infoAffiche['ID_DEMANDE'];?>);
    liste_costab_pap(<?=$infoAffiche['ID_DEMANDE'];?>);
    liste_costab_politique_sectorielle(<?=$infoAffiche['ID_DEMANDE'];?>);
  });
</script>

<script>
function liste_historique(ID_DEMANDE)
{
  var row_count ="1000000";
  $("#mytable_historique").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('process/Planification_Strategique_Sectorielle/liste_historique')?>",
      type:"POST", 
      data:
      {
        ID_DEMANDE:ID_DEMANDE,
      } 
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength: 10,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    order: [0,'desc'],
    dom: 'Bfrtlip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
      ],
    language: {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
      "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
      "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
      "sInfoPostFix":    "",
      "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
      "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
      "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
      "oPaginate": {
        "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
        "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
        "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
        "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
      },        "oAria": {
        "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
        "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
      }
    }
  });
}
</script>

<script>
function liste_cl_cmr_vision(ID_DEMANDE)
{
  var row_count ="1000000";
  $("#mytable_cl_cmr_vision").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('process/Planification_Strategique_Sectorielle/liste_cl_cmr_vision')?>",
      type:"POST", 
      data:
      {
        ID_DEMANDE:ID_DEMANDE,
      } 
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength: 10,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    order: [0,'desc'],
    dom: 'Bfrtlip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
      ],
    language: {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
      "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
      "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
      "sInfoPostFix":    "",
      "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
      "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
      "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
      "oPaginate": {
        "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
        "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
        "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
        "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
      },        "oAria": {
        "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
        "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
      }
    }
  });
}
</script>

<script>
function liste_cl_cmr_pap(ID_DEMANDE)
{
  var row_count ="1000000";
  $("#mytable_cl_cmr_pap").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('process/Planification_Strategique_Sectorielle/liste_cl_cmr_pap')?>",
      type:"POST", 
      data:
      {
        ID_DEMANDE:ID_DEMANDE,
      } 
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength: 10,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    order: [0,'desc'],
    dom: 'Bfrtlip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
      ],
    language: {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
      "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
      "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
      "sInfoPostFix":    "",
      "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
      "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
      "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
      "oPaginate": {
        "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
        "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
        "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
        "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
      },        "oAria": {
        "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
        "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
      }
    }
  });
}
</script>

<script>
function liste_cl_cmr_politique_sectorielle(ID_DEMANDE)
{
  var row_count ="1000000";
  $("#mytable_cl_cmr_politique_sectorielle").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('process/Planification_Strategique_Sectorielle/liste_cl_cmr_politique_sectorielle')?>",
      type:"POST", 
      data:
      {
        ID_DEMANDE:ID_DEMANDE,
      } 
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength: 10,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    order: [0,'desc'],
    dom: 'Bfrtlip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
      ],
    language: {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
      "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
      "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
      "sInfoPostFix":    "",
      "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
      "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
      "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
      "oPaginate": {
        "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
        "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
        "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
        "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
      },        "oAria": {
        "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
        "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
      }
    }
  });
}
</script>

<script>
function liste_costab_vision(ID_DEMANDE)
{
  var row_count ="1000000";
  $("#mytable_costab_vision").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('process/Planification_Strategique_Sectorielle/liste_costab_vision')?>",
      type:"POST", 
      data:
      {
        ID_DEMANDE:ID_DEMANDE,
      } 
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength: 10,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    order: [0,'desc'],
    dom: 'Bfrtlip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
      ],
    language: {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
      "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
      "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
      "sInfoPostFix":    "",
      "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
      "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
      "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
      "oPaginate": {
        "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
        "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
        "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
        "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
      },        "oAria": {
        "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
        "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
      }
    }
  });
}
</script>

<script>
function liste_costab_pap(ID_DEMANDE)
{
  var row_count ="1000000";
  $("#mytable_costab_pap").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('process/Planification_Strategique_Sectorielle/liste_costab_pap')?>",
      type:"POST", 
      data:
      {
        ID_DEMANDE:ID_DEMANDE,
      } 
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength: 10,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    order: [0,'desc'],
    dom: 'Bfrtlip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
      ],
    language: {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
      "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
      "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
      "sInfoPostFix":    "",
      "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
      "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
      "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
      "oPaginate": {
        "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
        "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
        "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
        "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
      },        "oAria": {
        "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
        "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
      }
    }
  });
}
</script>

<script>
function liste_costab_politique_sectorielle(ID_DEMANDE)
{
  var row_count ="1000000";
  $("#mytable_costab_politique_sectorielle").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('process/Planification_Strategique_Sectorielle/liste_costab_politique_sectorielle')?>",
      type:"POST", 
      data:
      {
        ID_DEMANDE:ID_DEMANDE,
      } 
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength: 10,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    order: [0,'desc'],
    dom: 'Bfrtlip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
      ],
    language: {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
      "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
      "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
      "sInfoPostFix":    "",
      "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
      "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
      "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
      "oPaginate": {
        "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
        "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
        "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
        "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
      },        "oAria": {
        "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
        "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
      }
    }
  });
}
</script>

<script>
  // function qui appelle le modal 
  function traitement(ACTION_ID,MOVETO,ETAPE_ID,IS_REQUIRED)
  {
    $('#ACTION_ID').val(ACTION_ID);
    $('#MOVETO').val(MOVETO);
    $('#ETAPE_ID').val(ETAPE_ID);
    $('#IS_REQUIRED').val(IS_REQUIRED);

    $.ajax(
    {
      url:"<?=base_url('/process/Planification_Strategique_Sectorielle/getDescriptionAction')?>",
      type:"POST",
      dataType:"JSON",
      data: {
        ACTION_ID:ACTION_ID
      },
      beforeSend:function() {
        $('#loading_popup').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      { 
        $('#loading_popup').html("");
        $('#addCommentaire').modal('show');
        if (IS_REQUIRED==1)
        {
          $('#required').text("*");
          $('#text_btn').html(""+data.DESCR_ACTION+" <span id='loading'></span> <span id='message'></span>");
        }else{
          $('#required').text("");
          $('#text_btn').html(""+data.DESCR_ACTION+" <span id='loading'></span> <span id='message'></span>");
        }
      }
    });

  }

  function send_data(argument)
  {
    var statut = true;

    var COMMENTAIRE = $('#COMMENTAIRE').val();
    var ID_DEMANDE = $('#ID_DEMANDE').val();
    var ACTION_ID = $('#ACTION_ID').val();
    var MOVETO = $('#MOVETO').val();
    var ETAPE_ID = $('#ETAPE_ID').val();
    var IS_REQUIRED = $('#IS_REQUIRED').val();

    if (IS_REQUIRED==1)
    {
      if(COMMENTAIRE=='') 
      {
        $('#errorCOMMENTAIRE').text('<?= lang('messages_lang.message_champs_obligatoire')?>');
        return false;
      }
      else
      {
        $('#errorCOMMENTAIRE').text('');
      }
    }

    if (statut == true)
    {
      $.ajax(
      {
        url:"<?=base_url('/process/Planification_Strategique_Sectorielle/send_data')?>",
        type:"POST",
        dataType:"JSON",
        data: {
          COMMENTAIRE:COMMENTAIRE,
          ID_DEMANDE:ID_DEMANDE,
          ACTION_ID:ACTION_ID,
          MOVETO:MOVETO,
          ETAPE_ID:ETAPE_ID,
          IS_REQUIRED:IS_REQUIRED,
        },
        beforeSend:function() {
          $('#loading').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#text_btn').attr('disabled',true);
        },
        success: function(data)
        { 
          $('#loading').html("");
          $('#text_btn').attr('disabled',false);
          $('#message').html('<i class="fa fa-check"></i>');
          
          setTimeout(()=>{
          window.location.href="<?= base_url('process/Demandes') ?>";

          $('#COMMENTAIRE').val('');
        },3000);
        }
      });
    }
  }
</script>
