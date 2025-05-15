<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>

</head>
<body>
  <div id="modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <?php foreach($details as $d): ?>
            <?php if($d->PATH_PROJET_LOI_FINANCE!=null): ?>
              <embed id="pdf1" style="display:none;" src="<?=base_url('uploads/programmation_budgetaire/'.$d->PATH_PROJET_LOI_FINANCE)?>" type="application/pdf" width="100%" height="600px">
              <?php endif;?>

              <?php if($d->LETTRE_CADRAGE!=null): ?>
                <embed id="pdf2" style="display:none;" src="<?=base_url('uploads/programmation_budgetaire/'.$d->LETTRE_CADRAGE)?>" type="application/pdf" width="100%" height="600px">
                <?php endif;?>

              <?php endforeach;?>    
            </div>
          </div>
        </div>
      </div>

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

                      <div class="card-body">

                        <?php
                        if(session()->getFlashKeys('alert_fail'))
                        {
                          ?>
                          <div class="col-md-12">
                            <div class="w-100 bg-success text-white text-center" id="message">
                              <?php echo session()->getFlashdata('alert_fail')['message']; ?>
                            </div>
                          </div>
                          <?php
                        }elseif(session()->getFlashKeys('alert')){                  
                          ?>
                          <div class="col-md-12">
                            <div class="w-100 bg-danger text-white text-center" id="message">
                              <?php echo session()->getFlashdata('alert')['message']; ?>
                            </div>
                          </div>
                          <?php
                        }
                        ?>

                      <div class="row" style="margin :  5px">
                        <div class="col-9">
                          <div class="dropdown">
                            <?php if ($infoAffiche['IS_END']!=1): ?>
                              <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><?= lang('messages_lang.choisir_act')?>
                              <span class="caret"></span></button>
                            <?php endif ?>
                            
                            <ul class="dropdown-menu" style="top:1rem !important">
                              <?php
                              if (!empty($actions)) {
                                foreach ($actions as $keyAction) 
                                {
                                  if ($keyAction->GET_FORM == 1)
                                    { ?>

                                      <li>&nbsp;&nbsp; >> <a href="<?=base_url(''.$keyAction->LINK_FORM.$ID_DEMANDE)?>" style="color:#006666;"><?=$keyAction->DESCR_ACTION?></a></li>

                                    <?php  }else{
                                      ?>

                                      <li><a href="#" class="traitement_class" onclick="traitement(<?=$keyAction->ACTION_ID?>,$(this).data('req'),$(this).data('moveto'));get_infos_docs(<?=$keyAction->ACTION_ID?>,$(this).data('id_demande'))" class="dropdown-item" style="color:#006666;" 
                                        data-req="<?= ($keyAction->IS_REQUIRED!=1) ? 0 : $keyAction->IS_REQUIRED; ?>" data-action_id="<?= $keyAction->ACTION_ID ?>" data-moveto="<?= $keyAction->MOVETO ?>" data-id_demande="<?=$ID_DEMANDE?>">
                                        >> <?=$keyAction->DESCR_ACTION?></a></li>

                                        <?php
                                      }
                                    }
                                  }else{
                                    ?>
                                    <a href="#" onclick="history.go(-1)" class="btn btn-primary"><i class="fa fa-reply-all"></i><?= lang('messages_lang.btnretour')?> </a>
                                    <?php
                                  }
                                  ?>
                                </ul>
                              </div>
                            </div>
                            <div class="col-3">
                              <a href="<?=base_url('process/Demandes_Program_Budget')?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;<?= lang('messages_lang.link_list')?></a> 
                            </div>
                            
                            
                          </div>
                          <!-- </div> -->
                          <br>

                          <!-- Info de base de la demande -->
                          <!-- <div style="border:1px solid #ddd;border-radius:5px;margin: 5px"> -->
                            <div class="row" style="margin :  5px">
                              <div class="col-12">
                                <div style="width: 100%;">
                                  <table class=" table table-striped table-bordered">
                                    <tr>
                                      <th><center><?= lang('messages_lang.code_demande')?></center></th>
                                      <th><center><?= lang('messages_lang.proc')?> </center></th>
                                      <th><center><?= lang('messages_lang.step')?></center></th>
                                      <th><center><?= lang('messages_lang.date')?></center></th>
                                      <th><center><?= lang('messages_lang.utilisateur')?></center></th>
                                    </tr>
                                    <tr>
                                      <td><?=!empty($infoAffiche['CODE_DEMANDE']) ? $infoAffiche['CODE_DEMANDE'] : "N/A" ?></td>
                                      <td><?=!empty($infoAffiche['NOM_PROCESS']) ? $infoAffiche['NOM_PROCESS'] : "N/A" ?></td>
                                      <td><?=!empty($infoAffiche['DESCR_ETAPE']) ? $infoAffiche['DESCR_ETAPE'].' :: '.$infoAffiche['PROFIL_DESCR'] : "N/A" ?></td>
                                      <td><?=!empty($infoAffiche['DATE_INSERTION']) ? date('d-m-Y',strtotime($infoAffiche['DATE_INSERTION'])) : "N/A" ?></td>
                                      <td><?=!empty($infoAffiche['NOM']) ? $infoAffiche['NOM'].' '.$infoAffiche['PRENOM'] : "N/A" ?></td>
                                    </tr>
                                  </table>
                                </div>
                              </div>
                            </div>
                            <!-- </div> -->

                            <!-- informations detaillées de la demande -->
                            <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                              <div class="row" style="margin :  5px">
                                <div class="col-12">
                                  <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                      <button class="nav-link active" id="pills-tab1-tab" data-toggle="pill" data-target="#pills-tab1" type="button" role="tab" aria-controls="pills-tab1" aria-selected="true"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.details_prog_budg')?></button>

                                      <button class="nav-link" id="pills-tab2-tab" data-toggle="pill" data-target="#pills-tab2" type="button" role="tab" aria-controls="pills-tab2" aria-selected="false"><i class="fa fa-history" aria-hidden="true"></i> <?= lang('messages_lang.treatment_history')?></button>

                                      <button class="nav-link" id="pills-tab4-tab" data-toggle="pill" data-target="#pills-tab4" type="button" role="tab" aria-controls="pills-tab4" aria-selected="false"><i class="fa fa-folder" aria-hidden="true"></i> <?= lang('messages_lang.document_download')?></button>


                                      <button class="nav-link" id="pills-tab6-tab" data-toggle="pill" data-target="#pills-tab6" type="button" role="tab" aria-controls="pills-tab6" aria-selected="false"><i class="fa fa-eye" aria-hidden="true"></i> <?= lang('messages_lang.label_CDMT')?> </button>
                                      <button class="nav-link" id="pills-tab7-tab" data-toggle="pill" data-target="#pills-tab7" type="button" role="tab" aria-controls="pills-tab7" aria-selected="true"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.label_COSTAB')?> </button>
                          
                        </div>
                      </nav>

                      <div class="tab-content" id="nav-tabContent">


                        <div class="tab-pane show active" id="pills-tab1" aria-labelledby="pills-tab1-tab">
                          <br>
                          <div class="table-responsive" style="width: 100%;">
                           <?php 
                           if (!empty($details))
                           {
                            foreach($details as $key)
                            {
                              if(!empty($key->DATE_PROGRAMMATION))
                              {
                                if (!empty($key->DATE_PROGRAMMATION))
                                { ?>
                                  <div class="d-inline-block">
                                  <div class="card border border-dark rounded">
                                    <div class="card-header bg-info text-white">
                                      <?=lang('messages_lang.date_de_programmation')?>

                                    </div>
                                    <div class="card-body text-center">
                                      <p class="card-text"><?= $key->DATE_PROGRAMMATION ?></p>
                                    </div>
                                  </div>
                                </div>
                              <?php
                                }
                                
                              }
                              if (!empty($key->DATE_PRORAMMATION_2))
                                { ?>
                                  <div class="d-inline-block">
                                    <div class="card border border-dark rounded">
                                      <div class="card-header bg-info text-white">
                                        <?=lang('messages_lang.deuxième_date_de_programmation')?>
                                        </div>
                                        <div class="card-body text-center">
                                          <p class="card-text"><?= $key->DATE_PRORAMMATION_2 ?></p>
                                        </div>
                                      </div>
                                    </div>
                                  <?php  }

                                }
                              } 
                              ?>

                              <?php foreach ($get_note_cbmt as  $value) {
                             ?>
                              <div class="d-inline-block">
                                <div class="card border border-dark rounded">
                                 
                                   <div class="card-header bg-info text-white">

                                     <b><?= $value->DESCR_ETAPE?></b>
                                   </div>
                                   <div class="card-body text-center">

                                     <p><a   href="<?= base_url('uploads/process/'.$value->PATH_NOTE_CADRAGE)?>" target="_blank"><i class="fa fa-file-pdf fa-3x text-danger"></i></a></p>
                                  </div>
                                  
                                </div>
                              </div>
                               <?php 
                          } ?>
                           
                          </div>
                        </div>

                        <div class="tab-pane fade" id="pills-tab2" aria-labelledby="pills-tab2-tab">
                          <br>
                          <div>
                            <?php
                            if(session()->getFlashKeys('alert'))
                            {
                              ?>
                              <div class="col-md-12">
                                <div class="w-100 bg-success text-white text-center" id="message_btn2">
                                  <?php echo session()->getFlashdata('alert')['message']; ?>
                                </div>
                              </div>
                              <?php
                            }elseif(session()->getFlashKeys('alert_fail')){                  
                              ?>
                              <div class="col-md-12">
                                <div class="w-100 bg-danger text-white text-center" id="message_btn2">
                                  <?php echo session()->getFlashdata('alert_fail')['message']; ?>
                                </div>
                              </div>
                              <?php
                            }
                            ?>
                          </div>
                          <div class="table-responsive" style="width: 100%;">
                            <table id="mytable_historique" class=" table table-striped table-bordered" style="width:100%">
                              <thead>
                                <tr>
                                  <th>#</th>
                                  <th><?= lang('messages_lang.demande')?></th>
                                  <th><?= lang('messages_lang.step')?></th>
                                  <th><?=lang('messages_lang.labelle_et_action')?></th>
                                  <th><?= lang('messages_lang.utilisateur')?></th> 
                                  <th><?= lang('messages_lang.commentaire')?></th> 
                                  <th><?= lang('messages_lang.date_trait')?></th> 
                                </tr>
                              </thead>
                              <tbody>
                                <?php if(!empty($demandes_histos)): ?>
                                  <?php $counter=1; ?>

                                  <?php foreach($demandes_histos as $d): ?>
                                    <tr>
                                      <td><?=$counter++?></td>
                                      <td><?= $d->CODE_DEMANDE ?></td>
                                      <td><?= $d->DESCR_ETAPE ?></td>
                                      <td><?= $d->DESCR_ACTION ?></td>
                                      <td><?= $d->NOM.' '.$d->PRENOM ?></td>
                                      <td><?= $d->COMMENTAIRE ?></td>
                                      <td><?= $d->DATE_INSERTION ?></td>
                                    </tr>
                                  <?php endforeach; ?>

                                <?php endif; ?>
                              </tbody>
                            </table>                          
                          </div>
                        </div>

                            <div style="background-color: white" class="tab-pane fade" id="pills-tab4" aria-labelledby="pills-tab4-tab">

                              <div class="table-responsive" style="width: 100%;">

                                <table id="mytable" class=" table table-striped table-bordered">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th><?= lang('messages_lang.document_download')?></th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php if(!empty($details)): ?>
                                      <?php foreach($details as $d): ?>

                                        <?php if($d->PATH_PROJET_LOI_FINANCE!=null): ?>
                                          <tr>
                                            <td>Projet de loi des finances</td>
                                            <td>
                                              <button style="border:none;" type="button" onclick="get_doc(1)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>

                                            </td>
                                          </tr>
                                          <?php 
                                        endif;
                                        if($d->LETTRE_CADRAGE != null):
                                          ?> 
                                          <tr>
                                            <td><?=lang('messages_lang.lettre_de_cadrage') ?></td>
                                            <td>
                                              <button style="border:none;" type="button" onclick="get_doc(2)"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></button>
                                            </td>
                                          </tr> 
                                          <?php
                                        endif;?>
                                      </tr>
                                      <?php $counter++ ?>
                                    <?php endforeach; ?>
                                  <?php endif; ?>

                                </tbody>
                              </table>                          
                            </div>
                          </div>



                          <div style="background-color: white" class="tab-pane fade" id="pills-tab5" aria-labelledby="pills-tab5-tab">
                           <br>
                           <table class="table m-b-0 m-t-20">
                            <tbody>
                              <tr>
                                <td><i class="fa fa-file-pdf"></i> &nbsp;<strong>Note de cadrage</strong></td>
                                <td class="text-dark"><?php if(!empty($get_note_cbmt['PATH_NOTE_CADRAGE'])){ ?><a href="#" data-toggle="modal" data-target="#note_corrige"><span style="font-size: 30px;color:red;" class="fa fa-file-pdf"></span></a><?php }else{?><?php } ?></td>
                              </tr>
                            </tbody>
                          </table>

                     </div>                          

                            <!-- Debut COSTAB -->
                            <div style="background-color: white" class="tab-pane fade" id="pills-tab7" aria-labelledby="pills-tab7-tab">
                             <br>
                             <div class="table table-bordered">
                              <table id="mycostab" class=" table table-responsive table-bordered" style="width: 100%;">
                                <thead>
                                  <tr>
                                    <th>#</th>
                                    <th><?= lang('messages_lang.th_enjeu')?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                    <th><?= lang('messages_lang.label_pilier')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                    <th><?= lang('messages_lang.pip_rapport_institutio_ax')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th> 
                                    <th><?= lang('messages_lang.pip_rapport_objectifs')?>&nbsp;&nbsp;&nbsp;&nbsp;</th> 
                                    <th><?= lang('messages_lang.th_programme')?>&nbsp;&nbsp;&nbsp;&nbsp;</th> 
                                
                                    <th><?= lang('messages_lang.th_budget_annee')?>2023</th> 
                                    <th><?= lang('messages_lang.th_budget_annee')?>2024</th> 
                                    <th><?= lang('messages_lang.th_budget_annee')?>2025</th> 
                                    <th><?= lang('messages_lang.th_budget_annee')?>2026</th> 
                                    <th><?= lang('messages_lang.th_budget_annee')?>2027</th>
                                    <th><?= lang('messages_lang.th_budget_quinquenal')?></th> 
                                  </tr>
                                </thead>
                                <tbody>
                                </tbody>
                              </table>                          
                            </div>
                          </div>
                          <!-- Fin COSTAB -->

                          <!--  CL & CMR  -->
                          <div style="background-color: white" class="tab-pane fade" id="pills-tab6" aria-labelledby="pills-tab6-tab">
                           <br>
                           <div class="table table-bordered">
                            <table id="mycl" class=" table table-striped table-bordered" style="width: 100%;">
                              <thead>
                                <tr>
                                  <th>#</th>
                                  <th><?= lang('messages_lang.label_pilier')?>&nbsp;&nbsp;&nbsp;</th>
                                  <th><?= lang('messages_lang.pip_rapport_objectifs')?>&nbsp;&nbsp;&nbsp;</th>
                                  <th><?= lang('messages_lang.pip_rapport_institutio_indi')?>&nbsp;&nbsp;&nbsp;</th> 
                                  <th><?= lang('messages_lang.th_refer')?>&nbsp;&nbsp;&nbsp;</th> 
                                  <th><?= lang('messages_lang.pip_rapport_institutio_cible_annee1')?>&nbsp;&nbsp;&nbsp;</th> 
                                  <th><?= lang('messages_lang.th_precise')?>&nbsp;&nbsp;&nbsp;</th> 
                                </tr>
                              </thead>
                              <tbody>
                              </tbody>
                            </table>                          
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>

                <!-- Fin informations détaillées de la demande -->
              </div>


            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <?php echo view('includesbackend/scripts_js.php');?>

  <div class='modal fade' id='addCommentaire' data-backdrop="static" >
    <div class='modal-dialog  modal-lg' style="max-width: 60%">
      <div class='modal-content'>
        <div class="modal-header">
          <h5 class="modal-title"><?= lang('messages_lang.labelle_Etapes')?> : <?=!empty($infoAffiche['DESCR_ETAPE']) ? $infoAffiche['DESCR_ETAPE'] : "N/A" ?></h5>
        </div>
        <form id="my_form" action="<?= base_url('process/Demandes_Programmation_Budgetaire/send_data') ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="ID_DEMANDE" id="ID_DEMANDE" value="<?=$infoAffiche['ID_DEMANDE']?>">
          <input type="hidden" name="ACTION_ID" id="ACTION_ID">
          <input type="hidden" name="ETAPE_ID" id="ETAPE_ID" value="<?= $infoAffiche['ETAPE_ID'] ?>">
          <input type="hidden" name="MOVETO_INPUT" id="MOVETO_INPUT">
          <input type="hidden" name="IS_REQUIRED" id="IS_REQUIRED">

          <div class='modal-body'>         
            <div class="row">
              <div class="col-12">

                <!-- debut affichage infos supp et docs -->
                <div style="display:block;margin-bottom:2rem">

                  <div id="infosSupp">

                  </div>

                  <!-- début input pour tester si div#infosSupp a une valeur ou pas -->
                  <input type="hidden" name="infosSupp" id="infosSuppTest">
                  <!-- fin input pour tester si div#infosSupp a une valeur ou pas -->

                 
                </div>
                <!-- fin affichage infos supp et docs -->
                <?php 
                if(!empty($document) && $document['DOCUMENT_ID'] == 3):                       
                  ?>
                 <div class="row">
                    <div class="col-md-4">
                    <label for="file_upload"><?= lang('messages_lang.lettre_de_cadrage')?><font color="red">*</font></label>
                    <input type="file" name="LETTRE_CADRAGE" class="form-control" id="LETTRE_CADRAGE" onchange="valid_doc2()" required> 
                    <span class="text-danger" id="error_lettre_cadrage"></span>
                  </div>
                  
                 </div>
                 <br>
                <?php endif; ?>
                <label id="commentaire_label"><?= lang('messages_lang.labelle_observartion')?> <font color="red"></font></label>
                <textarea rows="5" name="COMMENTAIRE" id="COMMENTAIRE" class="form-control" form="my_form"></textarea>
                <?php
                if(isset($validation)) : ?>
                  <?= $validation->getError('COMMENTAIRE'); ?>
                <?php endif ?>
                <font id="errorCOMMENTAIRE" color="red"></font>
              </div>
            </div>
          </div>
          <div class='modal-footer'>
            <button class='btn btn-danger btn-md' data-dismiss='modal'><i class="fa fa-close"></i><?=lang('messages_lang.annuler_modal')?></button>
            <button onclick="send_data()" type="button" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?=lang('messages_lang.transmettre_modal')?> <span id="loading_btn"></span> <span id="message_btn"></span></button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <script>

    $(document).ready(function()
    {
      listing_demandes_historique();
      liste_cl();
      liste_costab();

    });


    function liste_costab()
    {
      var ID_DEMANDE=$('#ID_DEMANDE').val();
      
      var row_count ="1000000";
      $("#mycostab").DataTable({
        "processing":true,
        "destroy" : true,
        "serverSide":true,
        "targets":[],
        "order":[],
        "ajax":{
          url:"<?= base_url('process/Listing_demandes_costab_programmation')?>",
          type:"POST", 
          data:
          {
            ID_DEMANDE:ID_DEMANDE,
          } 
        },

        lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
          "targets":[2,4],
          "orderable":false
        }],

        dom: 'Bfrtlip',
        order: [],
        buttons: [
          'excel', 'pdf'
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


    function listing_demandes_historique() {

      var ID_DEMANDE = $('#ID_DEMANDE').val();
      var row_count ="1000000";
      $('#message_btn2').delay(9000).fadeOut('slow');
      $("#mytable_historique").DataTable(
      {
        "processing":true,
        "destroy" : true,
        "serverSide":true,
        "ajax":
        {
          url:"<?= base_url('process/Demandes_Programmation_Budgetaire/listing_demandes_historique')?>",
          type:"POST",
          dataType: "JSON",
          data:{
            ID_DEMANDE:ID_DEMANDE
          },
        },
        lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
          "targets":[0,4],
          "orderable":true
        }],
        dom: 'Bfrtlip',
        order:[1,'asc'],
        buttons: [
          'excel', 'pdf'
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

    //variable pour récupérer l'id du champ fileUpload
    var idUpload = '';
    //variable qui contient l'INFOS_NAME
    var infos_name = '';
    // fonction qui appelle le modal si GET_FORM==0 
    //et appelle le formulaire GET_FORM==1
    function traitement(ACTION_ID,REQ,MOVETO){

      var is_required = REQ;
      
      $('#ACTION_ID').val(ACTION_ID);
      $('#IS_REQUIRED').val(is_required);
      $('#MOVETO_INPUT').val(MOVETO);
      var font = $('#commentaire_label font').text('')
      if(is_required==1){
        $('#commentaire_label font').text('*');
      }else{
        $('#commentaire_label font').text('');
      }
      
      $('#addCommentaire').modal('show');

      return ACTION_ID;
    }

    function liste_cl()
    {
      var ID_DEMANDE=$('#ID_DEMANDE').val();      
      var row_count ="1000000";
      $("#mycl").DataTable({
        "processing":true,
        "destroy" : true,
        "serverSide":true,
        "targets":[],
        "order":[],
        "ajax":{
          url:"<?= base_url('process/Demandes_Programmation_Budgetaire/listing_demandes_cl_cmr')?>",
          type:"POST", 
          data:
          {
            ID_DEMANDE:ID_DEMANDE,
          } 
        },

        lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
          "targets":[2,4],
          "orderable":false
        }],

        dom: 'Bfrtlip',
        order: [],
        buttons: [
          'excel', 'pdf'
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
    //function to get infos supp and documents
    function get_infos_docs(ID_ACTION,ID_DEMANDE){

      var ACTION_ID = ID_ACTION;
      var ETAPE_ID = $('#ETAPE_ID').val();
      var ID_DEMANDE = ID_DEMANDE;

      $.ajax({
        url:"<?=base_url('process/Demandes_Programmation_Budgetaire/get_infos_docs')?>"+'/'+ACTION_ID,
        type:"POST",
        dataType:"json",
        data: {
          ACTION_ID:ACTION_ID,
          ETAPE_ID:ETAPE_ID
        },
        success: function(data)
        { 

          $('#fileUpload').html('');
          $('#infosSupp').html('');
          if(Object.keys(data[0]).length>0){
            
            if(data[0]['DESCR_INFOS_SUPP']!==null)
            {
              // console.log(data[0]);return;
              if(data[0]['TYPE_INFOS_NAME']==1){//input type date
                $('#infosSupp').html('<label for="'+data[0]['INFOS_NAME']+'">'+data[0]['DESCR_INFOS_SUPP']+'<font color="red">*</font></label><input type="date" class="form-control" style="cursor:pointer" name="'+data[0]['INFOS_NAME']+'" min="<?=date('Y-m-d')?>" id="'+data[0]['INFOS_NAME']+'"><input type="hidden" name="INFOS_NAME" value="'+data[0]['INFOS_NAME']+'">'+'<font class="errorInfosSupp" color="red"></font>');
              }else if(data[0]['TYPE_INFOS_NAME']==3)
              {//input type text
                $('#infosSupp').html('<label for="'+data[0]['INFOS_NAME']+'"><'+data[0]['DESCR_INFOS_SUPP']+'<font color="red">*</font></label><input type="text" class="form-control" style="cursor:pointer" name="'+data[0]['INFOS_NAME']+'" id="'+data[0]['INFOS_NAME']+'"><input type="hidden" name="INFOS_NAME" value="'+data[0]['INFOS_NAME']+'">'+'<font class="errorInfosSupp" color="red"></font>');
              }

              $('#infosSuppTest').val(data[0]['INFOS_NAME']); 
              infos_name = data[0]['INFOS_NAME'];
            } 
          }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) { 
          alert("Status: " + textStatus); alert("Error: " + errorThrown); 
        }       
      });
    }

    function send_data(){
      $('.errorUpload').text('');      
      var day = new Date().getDate();
      var month= new Date().getMonth()+1;
      var year = new Date().getFullYear();
      var date = year+'-'+month+'-'+day;
      let date1 = new Date(date);
      
      var statut = 1;
      if (infos_name!='')
      {
        let date2 = new Date($('input[name='+infos_name+']').val());
        if(date1>date2)
        {
          $('.errorInfosSupp').text('');
          $('.errorInfosSupp').text('Veuillez sélectionner une date non dépassée.');
          statut = 2;
        }
      }
      
      var LETTRE_CADRAGE=$('#LETTRE_CADRAGE').val();
      var COMMENTAIRE=$('#COMMENTAIRE').val();
      var IS_REQUIRED=$('#IS_REQUIRED').val();
      $('#error_lettre_cadrage').val(''); 
      
      if(LETTRE_CADRAGE == ''){
        $('#error_lettre_cadrage').html('<?= lang('messages_lang.message_champs_obligatoire')?>');
        statut = 2;
      }
      else{
        $('#error_lettre_cadrage').text('');
      }

      if(IS_REQUIRED==1)
      {
        if(COMMENTAIRE=='')
        {
          $('#errorCOMMENTAIRE').text('<?= lang('messages_lang.message_champs_obligatoire')?>');
          statut = 2;
        }
        else
        {
          $('#errorCOMMENTAIRE').text('');
        }
      }
      
      if($('#infosSupp').children().length>0)
      {
        if($('#infosSupp input').val()==null || $('#infosSupp input').val()=='')
        {
          $('.errorInfosSupp').text('');
          $('.errorInfosSupp').text('<?= lang('messages_lang.message_champs_obligatoire')?>');
          statut = 2;
        }else{
          $('.errorInfosSupp').text('');
        }
      }
      

      if (statut == 1) {
        $('#my_form').submit();
      }      
    }
    function valid_doc2()
    {
      var fileInput = document.getElementById('LETTRE_CADRAGE');
      var filePath = fileInput.value;
      var allowedExtensions = /(\.pdf)$/i;
      if (!allowedExtensions.exec(filePath))
      {
        $('#error_lettre_cadrage').text("<?= lang('messages_lang.error_message_pdf')?>"); 
        fileInput.value = '';
        return false;
      }
      else
      {
        // Check if any file is selected. 
        if (fileInput.files.length > 0)
        { 
          for (const i = 0; i <= fileInput.files.length - 1; i++)
          { 
            const fsize = fileInput.files.item(i).size; 
            const file = Math.round((fsize / 1024)); 
            // The size of the file. 
            if (file > 200)
            { 
              $('#error_lettre_cadrage').text('<?= lang('messages_lang.error_message_taille_pdf')?>');
              fileInput.value = '';
            }else
            {
             $('#error_lettre_cadrage').text(''); 
           }
         } 
       }
     }
   }

   function valid_doc()
   {
    var fileInput = document.getElementById(idUpload);
    var filePath = fileInput.value;
      // Allowing file type
    var allowedExtensions = /(\.pdf)$/i;

    $('.errorUpload').text('');

    if (!allowedExtensions.exec(filePath)){
      $('.errorUpload').text("<?= lang('messages_lang.error_message_pdf')?>");
      fileInput.value = '';
      return false;
    }
    else
    {
        // Check if any file is selected. 
      if (fileInput.files.length > 0)
      { 
        for (const i = 0; i <= fileInput.files.length - 1; i++)
        { 
          const fsize = fileInput.files.item(i).size; 
          const file = Math.round((fsize / 1024)); 
            // The size of the file. 
          if (file > 200)
          { 
            $('.errorUpload').text('<?= lang('messages_lang.error_message_taille_pdf')?>');
            fileInput.value = '';
          }else
          {
            $('.errorUpload').text(''); 
          }
        } 
      }
    }
  }

  function get_doc($id){
    switch($id){
    case 1:
      $('#pdf1').css('display', 'block');
      break;
    case 2:
      $('#pdf2').css('display', 'block');
      break;
    }

    $('#modal').modal('show');          
  }
</script>

</div>

</body>

</html>