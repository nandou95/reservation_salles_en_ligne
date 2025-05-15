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

        <!--  -->
        <div class="card-body">

          <div class="card-body">
            <!-- Titre Etape actuelle -->
            <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
              <div class="row" style="margin :  5px">
                <div class="col-12">
                  <h1 style="font-size: 20px" class="header-title text-black"><?=!empty($infoAffiche['DESCR_ETAPE']) ? $infoAffiche['DESCR_ETAPE'] : "N/A" ?></h1>
                </div>
              </div>
            </div>

            <!-- Bouton des Actions et liste -->
            <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
              <div class="row" style="margin :  5px">
                <div class="col-9">
                  <?php
                  if (!empty($getAction)) {
                    ?>
                    <div class="dropdown">
                      <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><?= lang('messages_lang.choisir_act')?>
                        <span class="caret"></span> <span id="loading_popup"></span></button>
                        <ul class="dropdown-menu">
                          <?php
                          foreach ($getAction as $keyEtape) {
                            ?>
                            <li>&nbsp;&nbsp; >> <?php if ($keyEtape->GET_FORM == 1){?>
                            <a href="<?=base_url(''.$keyEtape->LINK_FORM.''.$keyEtape->ACTION_ID.'/'.md5($infoAffiche['ID_DEMANDE']).'')?>" style="color:#006666;"><?=$keyEtape->DESCR_ACTION?></a>
                            <?php }else if ($keyEtape->GET_FORM == 2){?>
                            <a href="#" onclick="note_cbmt(<?=$keyEtape->ACTION_ID?>,<?=$keyEtape->MOVETO?>,<?=$keyEtape->ETAPE_ID?>,<?=$keyEtape->IS_REQUIRED?>,<?=$keyEtape->GET_FORM?>)" style="color:#006666;"><?=$keyEtape->DESCR_ACTION?></a>
                            <?php } else { ?>
                              <a href="#" onclick="traitement(<?=$keyEtape->ACTION_ID?>,<?=$keyEtape->MOVETO?>,<?=$keyEtape->ETAPE_ID?>,<?=$keyEtape->IS_REQUIRED?>)" style="color:#006666;"><?=$keyEtape->DESCR_ACTION?></a>
                            <?php } ?></li>
                            <?php
                          }
                          ?>
                        </ul>
                      </div>
                      <?php
                    }else{
                      ?>
                      <a href="#" onclick="history.go(-1)" class="btn btn-primary"><i class="fa fa-reply-all"></i><?= lang('messages_lang.action_retour')?> </a>
                      <?php
                    }
                    ?>
                  </div>
                  <div class="col-3">
                    <a href="<?=base_url('process/Demandes')?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;<?= lang('messages_lang.link_list')?></a> 
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
                          <th><center><?= lang('messages_lang.th_code_demande')?></center></th>
                          <th><center><?= lang('messages_lang.proc')?> </center></th>
                          <th><center><?= lang('messages_lang.step')?> </center></th>
                          <th><center><?= lang('messages_lang.th_date_demande')?> </center></th>
                          <th><center><?= lang('messages_lang.table_utilisateur')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
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
                        <button class="nav-link active" id="pills-tab1-tab" data-toggle="pill" data-target="#pills-tab1" type="button" role="tab" aria-controls="pills-tab1" aria-selected="false"><i class="fa fa-history" aria-hidden="true"></i><?= lang('messages_lang.btn_historique')?> </button>
                        <button class="nav-link" id="pills-tab2-tab" data-toggle="pill" data-target="#pills-tab2" type="button" role="tab" aria-controls="pills-tab2" aria-selected="false"><i class="fa fa-eye" aria-hidden="true"></i><?= lang('messages_lang.label_CDMT')?>  </button>
                         <button class="nav-link" id="pills-tab3-tab" data-toggle="pill" data-target="#pills-tab3" type="button" role="tab" aria-controls="pills-tab3" aria-selected="true"><i class="fa fa-list" aria-hidden="true"></i><?= lang('messages_lang.label_COSTAB')?>  </button>
                          <button class="nav-link" id="pills-tab4-tab" data-toggle="pill" data-target="#pills-tab4" type="button" role="tab" aria-controls="pills-tab4" aria-selected="true"><i class="fa fa-list" aria-hidden="true"></i><?= lang('messages_lang.label_note_cadrage')?> </button>
                      </div>
                    </nav>

                    <div class="tab-content" id="nav-tabContent">

                      <div style="background-color: white" class="tab-pane show active" id="pills-tab1" aria-labelledby="pills-tab1-tab">
                        <br>
                        <div class="table table-bordered overflow-auto">
                          <table id="myhisto" class=" table table-striped table-bordered" style="width: 100%;">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th><?= lang('messages_lang.labelle_et_etape')?>&nbsp;&nbsp;&nbsp;</th>
                                <th><?= lang('messages_lang.labelle_et_action')?>&nbsp;&nbsp;&nbsp;</th>
                                <th><?= lang('messages_lang.commentaire')?>&nbsp;&nbsp;</th> 
                                <th><?= lang('messages_lang.table_profil')?>&nbsp;&nbsp;&nbsp;</th> 
                                <th><?= lang('messages_lang.utilisateur')?>&nbsp;&nbsp;</th> 
                                <th><?= lang('messages_lang.date_trait')?></th> 
                              </tr>
                            </thead>
                            <tbody>
                            </tbody>
                          </table>                          
                        </div>
                      </div>

                      <!--  CL & CMR  -->
                      <div style="background-color: white" class="tab-pane fade" id="pills-tab2" aria-labelledby="pills-tab2-tab">
                         <br>
                        <div class="table table-bordered overflow-auto">
                          <table id="mycl" class=" table table-striped table-bordered" style="width: 100%;">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th><?= lang('messages_lang.label_pilier')?>&nbsp;&nbsp;&nbsp;</th>
                                <th><?= lang('messages_lang.th_objectif')?>&nbsp;&nbsp;&nbsp;</th>
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
                      
                      <!-- Debut COSTAB -->
                       <div style="background-color: white" class="tab-pane fade" id="pills-tab3" aria-labelledby="pills-tab3-tab">
                         <br>
                        <div class="table table-bordered overflow-auto">
                          <table id="mycostab" class=" table table-responsive table-bordered" style="width: 100%;">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th><?= lang('messages_lang.th_enjeu')?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                <th><?= lang('messages_lang.label_pilier')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                <th>AXE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th> 
                                <th><?= lang('messages_lang.th_objectif')?>&nbsp;&nbsp;&nbsp;&nbsp;</th> 
                                <th><?= lang('messages_lang.th_programme')?>&nbsp;&nbsp;&nbsp;&nbsp;</th> 
                                <th><?= lang('messages_lang.th_projet')?>&nbsp;&nbsp;&nbsp;&nbsp;</th> 
                                <th><?= lang('messages_lang.th_budget_annee')?>&nbsp;2023</th> 
                                <th><?= lang('messages_lang.th_budget_annee')?>&nbsp;2024</th> 
                                <th><?= lang('messages_lang.th_budget_annee')?>&nbsp;2025</th> 
                                <th><?= lang('messages_lang.th_budget_annee')?>&nbsp;2026</th> 
                                <th><?= lang('messages_lang.th_budget_annee')?>&nbsp;2027</th>
                                <th><?= lang('messages_lang.th_budget_quinquenal')?></th> 
                              </tr>
                            </thead>
                            <tbody>
                            </tbody>
                          </table>                          
                        </div>
                     </div>
                      <!-- Fin COSTAB -->
                       <div style="background-color: white" class="tab-pane fade" id="pills-tab4" aria-labelledby="pills-tab4-tab">
                         <br>

                         <?php if (!empty($get_note_cbmt)) {
                             ?>
                         <table class="table m-b-0 m-t-20">
                          <tbody>

                            <?php foreach ($get_note_cbmt as  $value) {
                             ?>
                             <tr>
                               <td>
                                <b><?= $value->DESCR_ETAPE?></b>
                              </td>
                              <td><a   href="<?= base_url('uploads/process/'.$value->PATH_NOTE_CADRAGE)?>" target="_blank"><i class="fa fa-file-pdf fa-3x text-danger"></i></a></td>
                            </tr>
                            <?php 
                          } ?>

                          </tbody>
                        </table>
                           <?php 
                          } ?>
                     </div>
                    </div>
                  </div>
                </div>
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


</body>

</html>


<div class='modal fade' id='addCommentaire' data-backdrop="static" >
  <div class='modal-dialog  modal-lg' style="max-width: 60%">
    <form enctype='multipart/form-data' name="saveEtape" id="saveEtape" action="<?=base_url('process/Process_planification_Cdmt_Cbmt/envoyer/')?>" method="post" >
     <input type="hidden" name="ID_DEMANDE" id="ID_DEMANDE" value="<?=$infoAffiche['ID_DEMANDE']?>">

     <input type="hidden" name="ACTION_ID" id="ACTION_ID">
     <input type="hidden" name="MOVETO" id="MOVETO">
     <input type="hidden" name="ETAPE_ID" id="ETAPE_ID">
     <input type="hidden" name="IS_REQUIRED" id="IS_REQUIRED">
     <div class='modal-content'>
      <div class="modal-header">
        <h5 class="modal-title"><?=!empty($infoAffiche['DESCR_ETAPE']) ? $infoAffiche['DESCR_ETAPE'] : "N/A" ?></h5>
      </div>
      <div class='modal-body'>
        <div class="row">
          <div class="col-12">
            <label><?= lang('messages_lang.modal_commentaire')?> <font color="red" id="required"></font> </label>
            <textarea rows="5" name="COMMENTAIRE" id="COMMENTAIRE" class="form-control"></textarea>
            <font id="errorCOMMENTAIRE" color="red"></font>
          </div>
        </div>
      </div>
      <div class='modal-footer'>
        <button class='btn btn-danger btn-md' data-dismiss='modal'><i class="fa fa-close"></i> <?= lang('messages_lang.annuler_modal')?></button>
        <button id="text_btn" onclick="envoyer()" type="button" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?= lang('messages_lang.enregistre_action')?></button>
      </div>
    </div>
  </form>
</div>
</div>


<div class='modal fade' id='addCommentairenote' data-backdrop="static" >
  <div class='modal-dialog  modal-lg' style="max-width: 60%">
    <form enctype='multipart/form-data' name="saveEtapenote" id="saveEtapenote" action="<?=base_url('process/Process_planification_Cdmt_Cbmt/envoyernote/')?>" method="post" >
     <input type="hidden" name="ID_DEMANDEnote" id="ID_DEMANDEnote" value="<?=$infoAffiche['ID_DEMANDE']?>">

     <input type="hidden" name="ACTION_IDnote" id="ACTION_IDnote">
     <input type="hidden" name="MOVETOnote" id="MOVETOnote">
     <input type="hidden" name="ETAPE_IDnote" id="ETAPE_IDnote">
     <input type="hidden" name="IS_REQUIREDnote" id="IS_REQUIREDnote">
     <input type="hidden" name="GET_FORMnote" id="GET_FORMnote">

     <div class='modal-content'>
      <div class="modal-header">
        <h5 class="modal-title"><?=!empty($infoAffiche['DESCR_ETAPE']) ? $infoAffiche['DESCR_ETAPE'] : "N/A" ?></h5>
      </div>
      <div class='modal-body'>
        <div class="row">
          <div class="col-md-12">
            
            <label for=""> <?=lang('messages_lang.label_note_cadre')?> <font color="red" id="initialnote"></font></label>  <?php if(!empty($get_note_cbmt['PATH_NOTE_CADRAGE'])){ ?><a href="#" data-toggle="modal" data-target="#note_corrige"><span class="fa fa-file-pdf" style="color:red;"></span></a><input type="hidden" name="PATH_NOTE_CADRAGE_old" id="PATH_NOTE_CADRAGE_old" value="<?=$get_note_cbmt['PATH_NOTE_CADRAGE']?>"><?php } ?>
            <input type="file" accept=".pdf" class="form-control" name="PATH_NOTE_CADRAGE" id="PATH_NOTE_CADRAGE" value="<?=set_value("PATH_NOTE_CADRAGE")?>">
            <font color="red" id="error_PATH_NOTE_CADRAGE"></font>
            <?php if (isset($validation)) : ?>
              <?= $validation->getError('PATH_NOTE_CADRAGE'); ?>
              <?php endif ?>
          </div>
          </div>
          <br>
          <div class="row">
          <div class="col-12">
            <label><?= lang('messages_lang.labelle_commentaire')?> <font color="red" id="requirednote"></font> </label>
            <textarea rows="5" name="COMMENTAIREnote" id="COMMENTAIREnote" class="form-control"></textarea>
            <font id="errorCOMMENTAIREnote" color="red"></font>
          </div>
        </div>
      </div>
      <div class='modal-footer'>
        <button class='btn btn-danger btn-md' data-dismiss='modal'><i class="fa fa-close"></i> Annuler</button>
        <button id="text_btnnote" onclick="envoyernote()" type="button" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?= lang('messages_lang.enregistre_action')?></button>
      </div>
    </div>
  </form>
</div>
</div>

<script>
  // function qui appelle le modal 
  function note_cbmt(ACTION_ID,MOVETO,ETAPE_ID,IS_REQUIRED,GET_FORM)
  {
    $('#ACTION_IDnote').val(ACTION_ID);
    $('#MOVETOnote').val(MOVETO);
    $('#ETAPE_IDnote').val(ETAPE_ID);
    $('#IS_REQUIREDnote').val(IS_REQUIRED);
    $('#GET_FORMnote').val(GET_FORM);

    $.ajax(
    {
      url:"<?=base_url('/process/Process_planification_Cdmt_Cbmt/getAction')?>",
      type:"POST",
      dataType:"JSON",
      data: {
        ACTION_ID:ACTION_ID
      },
      beforeSend:function() {
        $('#loading_popupnote').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      { 
        $('#loading_popupnote').html("");
        $('#addCommentairenote').modal('show');
        if (IS_REQUIRED==1)
        {
          $('#requirednote').text("*");
        }else{
          $('#requirednote').text("");
        }

        if (IS_REQUIRED==1)
        {
          $('#initialnote').text("*");
          $('#text_btnnote').html(""+data.DESCR_ACTION+" <span id='loadingnote'></span> <span id='messagenote'></span>");
        }else{
          $('#initialnote').text("");
          $('#text_btnnote').html(""+data.DESCR_ACTION+" <span id='loadingnote'></span> <span id='messagenote'></span>");
        }

      }
    });
  }

  function envoyernote(argument)
  {
    var statut = true;
    var COMMENTAIRE = $('#COMMENTAIREnote').val();
    var ID_DEMANDE = $('#ID_DEMANDEnote').val();
    var ACTION_ID = $('#ACTION_IDnote').val();
    var MOVETO = $('#MOVETOnote').val();
    var ETAPE_ID = $('#ETAPE_IDnote').val();
    var IS_REQUIRED = $('#IS_REQUIREDnote').val();
    var GET_FORM = $('#GET_FORMnote').val();

    var PATH_NOTE_CADRAGE = document.getElementById('PATH_NOTE_CADRAGE');
    $('#error_PATH_NOTE_CADRAGE').html('');
    var maxSize = 200*1024;

    if (IS_REQUIRED==1)
    {
      if (PATH_NOTE_CADRAGE.files.length === 0)
      {

        $('#error_PATH_NOTE_CADRAGE').html('<?= lang('messages_lang.validation_message')?>');
        statut = false;
      }else if (PATH_NOTE_CADRAGE.files[0].size > maxSize)
      {
        $('#error_PATH_NOTE_CADRAGE').html('<?= lang('messages_lang.pdf_max')?>');
        statut = false;
      }

      if(COMMENTAIRE=='') 
      {
        $('#errorCOMMENTAIREnote').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }
      else
      {
        $('#errorCOMMENTAIREnote').text('');
      }
    }

    if (statut == true)
    {
      document.getElementById("saveEtapenote").submit();
    }
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
      url:"<?=base_url('/process/Process_planification_Cdmt_Cbmt/getAction')?>",
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

  function envoyer(argument)
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
        $('#errorCOMMENTAIRE').text('<?= lang('messages_lang.validation_message')?>');
        return false;
      }
      else
      {
        $('#errorCOMMENTAIRE').text('');
      }
    }

    if (statut == true)
    {
      document.getElementById("saveEtape").submit();
    }
  }
</script>
 <script type="text/javascript">
    $(document).ready(function ()
    {
      
      liste_costab();
      liste_histo();
      liste_cl();
    });
  </script>
<script type="text/javascript">
   function liste_histo()
    {
      var ID_DEMANDE=$('#ID_DEMANDE').val();
      
      var row_count ="1000000";
      $("#myhisto").DataTable({
        "processing":true,
        "destroy" : true,
        "serverSide":true,
        "targets":[],
        "order":[],
        "ajax":{
          url:"<?= base_url('process/Process_planification_Cdmt_Cbmt/historique')?>",
          type:"POST", 
          data:
          {
            ID_DEMANDE:ID_DEMANDE,
          } 
        },

        lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
          "orderable":false
        }],

        dom: 'Bfrtlip',
        order: [],
        buttons: [
        'excel', 'pdf'
        ],
        language: {
        "sProcessing":     "<?=lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?=lang('messages_lang.search_button')?>&nbsp;:",
        "sLengthMenu":     "<?=lang('messages_lang.labelle_et_afficher')?> _MENU_ <?=lang('messages_lang.labelle_et_affichage_element')?>",
        "sInfo":  "<?=lang('messages_lang.labelle_et_affichage_element')?> _START_ <?=lang('messages_lang.labelle_et_a')?> _END_ <?=lang('messages_lang.labelle_et_a')?> _END_ sur _TOTAL_ <?=lang('messages_lang.labelle_et_affichage_filtre')?> _TOTAL_ <?=lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?=lang('messages_lang.labelle_et_affichage_element')?> 0  <?=lang('messages_lang.labelle_et_a')?> 0 <?=lang('messages_lang.labelle_et_affichage_filtre')?> 0 <?=lang('messages_lang.labelle_et_element')?>",      
        "sInfoFiltered":   "(<?=lang('messages_lang.labelle_et_elementtotal')?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?=lang('messages_lang.labelle_et_chargement')?>...",
        "sZeroRecords":    "<?=lang('messages_lang.labelle_et_aucun_element')?>",
        "sEmptyTable":     "<?=lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
        "oPaginate": {
          "sFirst":      "<?=lang('messages_lang.labelle_et_premier')?>",
          "sPrevious":   "<?=lang('messages_lang.labelle_et_precedent')?>",
          "sNext":       "<?=lang('messages_lang.labelle_et_suivant')?>",
          "sLast":       "<?=lang('messages_lang.labelle_et_dernier')?>"
        },
          "oAria": {
            "sSortAscending":  ": <?=lang('messages_lang.labelle_et_trier_colone')?>",
            "sSortDescending": ": <?=lang('messages_lang.labelle_et_trier_activer_trier')?>"
          }
        }

      });
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
          url:"<?= base_url('process/Process_planification_Cdmt_Cbmt/cl_cmr')?>",
          type:"POST", 
          data:
          {
            ID_DEMANDE:ID_DEMANDE,
          } 
        },

        lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
          "orderable":false
        }],

        dom: 'Bfrtlip',
        order: [],
        buttons: [
        'excel', 'pdf'
        ],
        language: {
        "sProcessing":     "<?=lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?=lang('messages_lang.search_button')?>&nbsp;:",
        "sLengthMenu": "<?=lang('messages_lang.labelle_et_afficher')?> _MENU_ <?=lang('messages_lang.labelle_et_affichage_element')?>",
        "sInfo":  "<?=lang('messages_lang.labelle_et_affichage_element')?> _START_ <?=lang('messages_lang.labelle_et_a')?> _END_ <?=lang('messages_lang.labelle_et_a')?> _END_ sur _TOTAL_ <?=lang('messages_lang.labelle_et_affichage_filtre')?> _TOTAL_ <?=lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?=lang('messages_lang.labelle_et_affichage_element')?> 0  <?=lang('messages_lang.labelle_et_a')?> 0 <?=lang('messages_lang.labelle_et_affichage_filtre')?> 0 <?=lang('messages_lang.labelle_et_element')?>",      
        "sInfoFiltered":   "(<?=lang('messages_lang.labelle_et_elementtotal')?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?=lang('messages_lang.labelle_et_chargement')?>...",
        "sZeroRecords":    "<?=lang('messages_lang.labelle_et_aucun_element')?>",
        "sEmptyTable":     "<?=lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
        "oPaginate": {
          "sFirst":      "<?=lang('messages_lang.labelle_et_premier')?>",
          "sPrevious":   "<?=lang('messages_lang.labelle_et_precedent')?>",
          "sNext":       "<?=lang('messages_lang.labelle_et_suivant')?>",
          "sLast":       "<?=lang('messages_lang.labelle_et_dernier')?>"
        },
          "oAria": {
            "sSortAscending":  ":<?=lang('messages_lang.labelle_et_trier_colone')?>",
            "sSortDescending": ":<?=lang('messages_lang.labelle_et_trier_activer_trier')?>"
          }
        }

      });
    }

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
          url:"<?= base_url('process/Process_planification_Cdmt_Cbmt/costab')?>",
          type:"POST", 
          data:
          {
            ID_DEMANDE:ID_DEMANDE,
          } 
        },

        lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
          "orderable":false
        }],

        dom: 'Bfrtlip',
        order: [],
        buttons: [
        'excel', 'pdf'
        ],
        language: {
        "sProcessing":     "<?=lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?=lang('messages_lang.search_button')?>&nbsp;:",
        "sLengthMenu":     "<?=lang('messages_lang.labelle_et_afficher')?> _MENU_ <?=lang('messages_lang.labelle_et_affichage_element')?>",
        "sInfo":  "<?=lang('messages_lang.labelle_et_affichage_element')?> _START_ <?=lang('messages_lang.labelle_et_a')?> _END_ <?=lang('messages_lang.labelle_et_a')?> _END_ sur _TOTAL_ <?=lang('messages_lang.labelle_et_affichage_filtre')?> _TOTAL_ <?=lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?=lang('messages_lang.labelle_et_affichage_element')?> 0  <?=lang('messages_lang.labelle_et_a')?> 0 <?=lang('messages_lang.labelle_et_affichage_filtre')?> 0 <?=lang('messages_lang.labelle_et_element')?>",      
        "sInfoFiltered":   "(<?=lang('messages_lang.labelle_et_elementtotal')?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?=lang('messages_lang.labelle_et_chargement')?>...",
        "sZeroRecords":    "<?=lang('messages_lang.labelle_et_aucun_element')?>",
        "sEmptyTable":     "<?=lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
        "oPaginate": {
          "sFirst":      "<?=lang('messages_lang.labelle_et_premier')?>",
          "sPrevious":   "<?=lang('messages_lang.labelle_et_precedent')?>",
          "sNext":       "<?=lang('messages_lang.labelle_et_suivant')?>",
          "sLast":       "<?=lang('messages_lang.labelle_et_dernier')?>"
        },
          "oAria": {
            "sSortAscending":  ": <?=lang('messages_lang.labelle_et_trier_colone')?>",
            "sSortDescending": ": <?=lang('messages_lang.labelle_et_trier_activer_trier')?>"
          }
        }

      });
    }
</script>

