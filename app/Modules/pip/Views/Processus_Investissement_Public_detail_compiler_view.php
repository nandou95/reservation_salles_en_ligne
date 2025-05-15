<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>

</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">

          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">

                <!-- <br> -->
                <div class="card-body">
                  <!-- <form id="my_form" action="<?= base_url() ?>" method="POST"> -->
                    <div class="card-body">
                   
                      <!-- Bouton des Actions et liste -->
                      <div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
                        <div class="row" style="margin :  5px">
                          <div class="col-3">
                            <input type="hidden" name="ID_DOC_COMPILATION" id="ID_DOC_COMPILATION" value="<?= $ID_DOC_COMPILATION ?>">
                            <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown"><?= lang('messages_lang.table_Action') ?>
                                <span class="caret"></span></button>
                              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <?php
                                if (!empty($etape) and !empty(session()->get('SESSION_SUIVIE_PTBA_PROFIL_ID'))) {
                                  foreach ($etape as $keyEtape) {
                                ?>
                                    <li class="px-3 dropdown-item"> 
                                      <?php if($keyEtape->ETAPE_ID ): ?>
                                        <form action="<?= base_url()?>/pip/Processus_Investissement_Public/avancement" method="POST" id="form_<?= $keyEtape->ACTION_ID ?>" class="position-absolute">
                                          <input type="hidden" name="ID_DEMANDE" id="ID_DEMANDE" value="<?= $principal['ID_DOC_COMPILATION'] ?>">
                                          <input type="hidden" name="CURRENT_STEP" id="CURRENT_STEP" value="<?= $keyEtape->ETAPE_ID ?>">
                                          <input type="hidden" name="ACTION_ID" id="ACTION_ID" value="<?= $keyEtape->ACTION_ID ?>">
                                          <input type="hidden" name="IS_CORRECTION_PIP" id="IS_CORRECTION_PIP" value="<?= $keyEtape->IS_CORRECTION_PIP ?>">
                                          <textarea class="d-none" name="FORM_COMMENTAIRE" id="FORM_COMMENTAIRE"></textarea>
                                        </form>
                                        <div style="cursor: pointer;" id="openModal" data-toggle="modal" data-target="#addCommentaire"><?= $keyEtape->DESCR_ACTION ?></div>
                                      <?php elseif($keyEtape->ETAPE_ID == $last[0]->ETAPE_ID): ?>
                                        <div style="cursor: pointer;"><?= lang('messages_lang.action_valider_PIP') ?></div>
                                
                                      <?php endif; ?>
                                  <?php
                                  }
                                } else {
                                  ?>
                                  <a href="#" onclick="history.go(-1)" class="btn btn-primary"><i class="fa fa-reply-all"></i> <?= lang('messages_lang.action_retour') ?> </a>
                                  </li>
                                <?php
                                }
                                ?>
                              </ul>
                            </div>
                          </div>
                          <div class="col-6"></div>
                          <div class="col-3">
                            <a href="<?= base_url('pip/Fiche_Pip_Proposer/liste_pip_proposer') ?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;<?= lang('messages_lang.link_list') ?></a>
                          </div>
                        </div>
                      </div>
                      <br>

                      <!-- Info de base de la demande -->
                      <div >
                        <div class="row">
                          <div class="container">
                            <div  style="width: 100%;">
                              <table class=" table table-striped ">
                                <tr>
                                 
                                  <th>
                                    <center><?= lang('messages_lang.code') ?></center>
                                  </th>
                                  <th>
                                    <center><?= lang('messages_lang.col_etape') ?> </center>
                                  </th>
                                  <th>
                                    <center><?= lang('messages_lang.labelle_date_compilation') ?> </center>
                                  </th>
                                  
                                </tr>
                                <tr>
                                  
                                  <td style="background:#ddd"><?= !empty($principal['CODE_PIP']) ? $principal['CODE_PIP'] : "N/A" ?></td>
                                  <td style="background:#ddd"><?= !empty($principal['DESCR_ETAPE']) ? $principal['DESCR_ETAPE'] : "N/A" ?></td>
                                  <td style="background:#ddd"><?= !empty($principal['DATE_COMPILATION']) ? date('d-m-Y', strtotime($principal['DATE_COMPILATION'])) : "N/A" ?></td>
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
                                <button class="nav-link  active ml-1 mt-1" id="pills-tab2-tab" data-toggle="pill" data-target="#pills-tab2" type="button" role="tab" aria-controls="pills-tab2"  aria-selected="true"><i class="fa fa-history" aria-hidden="true"></i> <?= lang('messages_lang.btn_historique') ?></button>
                                 <button class="nav-link  ml-1 mt-1" id="pills-tab5-tab" data-toggle="pill" data-target="#pills-tab5" type="button" role="tab" aria-controls="pills-tab5" aria-selected="false"><i class="fa fa-house"></i> <?= lang('messages_lang.labelle_projet_ministere') ?> </button> 
                                 <button class="nav-link  ml-1 mt-1" id="pills-tab6-tab" data-toggle="pill" data-target="#pills-tab6" type="button" role="tab" aria-controls="pills-tab6" aria-selected="false"><i class="fa fa-credit-card"></i> <?= lang('messages_lang.labelle_investissement') ?> </button> 
                                 <button class="nav-link  ml-1 mt-1" id="pills-tab7-tab" data-toggle="pill" data-target="#pills-tab7" type="button" role="tab" aria-controls="pills-tab7" aria-selected="false"><i class="fa fa-cubes"></i> <?= lang('messages_lang.labelle_objectif_strategique_PND') ?></button> 
                                 <button class="nav-link  ml-1 mt-1" id="pills-tab8-tab" data-toggle="pill" data-target="#pills-tab8" type="button" role="tab" aria-controls="pills-tab8" aria-selected="false"><i class="fa fa-cogs"></i> <?= lang('messages_lang.labelle_axe_intervention_PND') ?> </button> 
                              </div>
                            </nav>
                            <div class="tab-content" id="nav-tabContent">
                              <div style="background-color: white" class="tab-pane mt-5 show active" id="pills-tab1" aria-labelledby="pills-tab1-tab">
                              
                              </div>

                              <div class="tab-pane fade active show" id="pills-tab2" aria-labelledby="pills-tab2-tab">
                                <div class="table-responsive" style="width: 100%;">
                                  <table id="mytablehisto" class=" table table-striped table-bordered">
                                    <thead>
                                      <tr>
                                        <th>#</th>
                                        <th><?= lang('messages_lang.Lab_jur_etape') ?></th>
                                        <th><?= lang('messages_lang.table_Action') ?></th>
                                        <th><?= lang('messages_lang.labelle_commentaire') ?></th>
                                        <th><?= lang('messages_lang.labelle_UTILISATEUR') ?></th>
                                        <th><?= lang('messages_lang.labelle_date_traitement') ?></th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php 
                                        if(count($historics)):
                                        $i=1;
                                        foreach($historics as $hist):
                                      ?>
                                          <tr>
                                            <td><?= $i ?></td>
                                            <td><?= $hist->DESCR_ETAPE ?></td>
                                            <td><?= $hist->DESCR_ACTION ?></td>
                                            <td><?= $hist->COMMENTAIRE ?? '-' ?></td>
                                            <td><?= $hist->USER_NAME ?></td>
                                            <td><?= date_format(new \DateTime($hist->DATE_INSERTION),"d/m/Y") ?></td>
                                          </tr>
                                      <?php
                                          $i++;
                                          endforeach;
                                        else:
                                      ?>
                                        <td colspan="6" class="text-center"><?= lang('messages_lang.td_aucune_donnee') ?></td>
                                      <?php
                                        endif;
                                      ?>
                                    </tbody>
                                  </table>
                                </div>
                              </div>

                              <div class="tab-pane fade" id="pills-tab6" aria-labelledby="pills-tab6-tab">
                                <div class="table-responsive" >
                                   <table id="mytable6" class="  table table-striped table-bordered" style="width: 100%;">
                                    <thead>
                                      <tr>
                                        <th>#</th>
                                        <th><?= lang('messages_lang.labelle_objectif_strategique') ?></th>
                                        <th>2023-2024</th>
                                        <th>2024-2025 </th>
                                        <th>2025-2026</th>
                                        <th><?= lang('messages_lang.labelle_total') ?></th>
                                        <th>%</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                  </table>
                                </div>
                              </div>

                              <div class="tab-pane fade" id="pills-tab7" aria-labelledby="pills-tab7-tab">
                                <div class="table-responsive">
                                <table id="mytable7" class=" table table-striped table-bordered"  style="width: 100%;">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th><?= lang('messages_lang.labelle_objectif_strategique_PND') ?></th>
                                      <th>2023-2024</th>
                                      <th>2024-2025 </th>
                                      <th>2025-2026</th>
                                      <th><?= lang('messages_lang.labelle_total') ?></th>
                                      <th>%</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                  </tbody>
                                </table>
                                </div>
                              </div>

                              <div class="tab-pane fade" id="pills-tab8" aria-labelledby="pills-tab8-tab">
                                <div class="table-responsive" >
                                <table id="mytable8" class=" table table-striped table-bordered" style="width: 100%;">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th><?= lang('messages_lang.labelle_axe_intervention_PND') ?></th>
                                      <th>2023-2024</th>
                                      <th>2024-2025 </th>
                                      <th>2025-2026</th>
                                      <th><?= lang('messages_lang.labelle_total') ?></th>
                                      <th>%</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                  </tbody>
                                </table>
                                </div>
                              </div>

                              <div class="tab-pane fade" id="pills-tab9" aria-labelledby="pills-tab9-tab">
                                <div class="table-responsive" >
                                <table id="mytable8" class=" table table-striped table-bordered">
                                <thead>
                                  <tr>
                                    <th>#</th>
                                    <th><?= lang('messages_lang.label_droit_pilier') ?></th>
                                    <th>2023-2024</th>
                                    <th>2024-2025 </th>
                                    <th>2025-2026</th>
                                    <th><?= lang('messages_lang.labelle_total') ?></th>
                                    <th>%</th>
                                  </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                </table>
                                </div>
                              </div>

                              <div class="tab-pane fade" id="pills-tab10" aria-labelledby="pills-tab10-tab">
                                <div class="table-responsive" >
                               <table id="mytable10" class=" table table-striped table-bordered">
                                    <thead>
                                      <tr>
                                        <th>N0</th>
                                        <th><?= lang('messages_lang.label_droit_pilier') ?></th>
                                        <th><?= lang('messages_lang.labelle_total_financement') ?></th>
                                        <th><?= lang('messages_lang.labelle_cout_projet') ?></th>
                                        <th><?= lang('messages_lang.labelle_gap_financement') ?></th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                  </table>
                                </div>
                              </div>

                              <div style="background-color: white" class="tab-pane fade mt-3" id="pills-tab5" aria-labelledby="pills-tab5-tab">
                                <div class="table-responsive" style="width: 100%;">
                                <table id="mytable5" class=" table table-striped table-bordered">
                                    <thead>
                                      <tr>
                                        <th>#</th>
                                        <th><?= lang('messages_lang.th_instit') ?></th>
                                        <th><?= lang('messages_lang.th_nom_projet') ?></th>
                                        <th><?= lang('messages_lang.th_en_cours') ?> </th>
                                        <th><?= lang('messages_lang.th_en_preparation') ?></th>
                                        <th><?= lang('messages_lang.th_nouveau') ?></th>
                                        <th><?= lang('messages_lang.th_idee') ?></th>
                                        <th><?= lang('messages_lang.th_termine') ?></th>
                                        <th><?= lang('messages_lang.th_total_projet') ?></th>
                                        <th>%</th>
                                      </tr>
                                    </thead>
                                    
                                    <tbody>
                                    </tbody>
                                  </table>
                                  </div>
                              </div>
                              <div style="background-color: white" class="tab-pane fade" id="pills-tab4" aria-labelledby="pills-tab4-tab">
                                <div class="table-responsive" style="width: 100%;">
                                    <table id="mytable2" class=" table table-striped table-bordered">
                                      <thead>
                                        <tr>
                                          <th>#</th>
                                          <th><?= lang('messages_lang.th_type_document') ?></th>
                                          <th><?= lang('messages_lang.document_action') ?> </th>
                                         
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php if(isset($details[0]->PATH_DOC_COMPILER)): ?>
                                        <tr>
                                          <td>1</td>
                                          <td><?= lang('messages_lang.td_fiche_compilation') ?></td>
                                          <td><button style="border:none;" type="button" onclick="get_doc(1)"><span class="fa fa-file-pdf" style="color:#b30f0f;font-size: 200%;"></span></button></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if(isset($reference[0]->DOC_REFERENCE)): ?>
                                        <tr>
                                          <td>2</td>
                                          <td><?= lang('messages_lang.td_document_reference') ?></td>
                                          <td><button style="border:none;" type="button" onclick="get_doc(2)"><span class="fa fa-file-pdf" style="color:#b30f0f;font-size: 200%;"></span></button></td>     
                                        </tr>
                                        <?php endif; ?>
                                      </tbody>
                                    </table>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                    </div>
                  <!-- </form> -->
                </div>

              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php'); ?>

<div  id=" modal_id" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Modal body text goes here.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary">Save changes</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</body>

</html>

<?php if(preg_match("/élaborarion du PIP/i",$principal['DESCR_ETAPE'])): ?>
<div class="modal fade" id="modal_form" tabindex="-1" role="dialog" aria-labelledby="example2ModalLabel" aria-hidden="true" data-backdrop="static">
  <div class='modal-dialog  modal-lg' role="document" style="max-width: 60%">
      <div class='modal-content'>
        <div class="modal-header">
          <h5 class="modal-title"><?= !empty($principal['DESCR_ETAPE']) ? $principal['DESCR_ETAPE'] : "N/A" ?></h5>
        </div>
        <div class='modal-body'>
          <div class="row">
            <form action="" method="post">
              <div class="form-group">
                <input type="text" class="form-control">
              </div>
            </form>
          </div>
        </div>
      </div>
  </div>
</div>
<?php else: ?>
<div class='modal fade' id='addCommentaire' tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class='modal-dialog  modal-lg' role="document" style="max-width: 60%">
    <div class='modal-content'>
      <div class="modal-header">
        <h5 class="modal-title"><?= !empty($principal['DESCR_ETAPE']) ? $principal['DESCR_ETAPE'] : "N/A" ?></h5>
      </div>
      <div class='modal-body'>
        <div class="row">
          <input type="hidden" name="" id="form_id">
          <div class="col-12">
            <label><?= lang('messages_lang.labelle_commentaire') ?> </label>
            <textarea rows="5" name="COMMENTAIRE" id="COMMENTAIRE" class="form-control"></textarea>
          </div>
        </div>
      </div>
      <div class='modal-footer'>
        <button class='btn btn-danger btn-md' data-dismiss='modal'><i class="fa fa-close"></i> <?= lang('messages_lang.annuler_modal') ?></button>
        <button id="submit_form" type="button" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?= lang('messages_lang.transmettre_modal') ?></button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <?php if (!empty($details[0]->PATH_DOC_COMPILER)) { ?>
        <embed id="pdf1" style="display:none;" src="<?=base_url($details[0]->PATH_DOC_COMPILER)?>" type="application/pdf" width="100%" height="600px">
        <?php } ?>
        <?php if (!empty($reference[0]->DOC_REFERENCE)) { ?>
        <embed id="pdf2" style="display:none;" src="<?=base_url($reference[0]->DOC_REFERENCE)?>" type="application/pdf" width="100%" height="600px">
        <?php } ?>
        
      </div>
    </div>
  </div>
</div>

<script>
  $('#submit_form').click(function(){
    let COMMENTAIRE = $('#COMMENTAIRE').val()
    let ID_DEMANDE = $('#ID_DEMANDE').val()
    let CURRENT_STEP = $('#CURRENT_STEP').val()
    let ACTION_ID = $('#ACTION_ID').val()
    let FORM_ID = $('#addCommentaire input#form_id').val();

    if(ID_DEMANDE == '' || CURRENT_STEP == '' || ACTION_ID == '' || FORM_ID==''){
      $('#addCommentaire .modal-body').append(`
        <div class="text-danger my-3"><?= lang('messages_lang.message_erreur_commentaire') ?></div>
      `)

      return false;
    }

    
    $('#FORM_COMMENTAIRE').val(COMMENTAIRE);
    
    $(this).attr('disabled','disabled')

    $('#'+FORM_ID).submit()
  })
  
  $('div #openModal').click(function(){
    let ID = $(this).prev().attr('id')
    console.log(ID)
    $('#addCommentaire input#form_id').val(ID);
  })

  $("#mytable2").DataTable(
    {
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    }
  );

  /**
   * fonction pour recuperer le fichier en 
   */
  function get_document()
  {
    $('#modal_id').modal('show');  
  }

  function get_doc(doc){
    if (doc==1) {
      $('#pdf1').css('display', 'block');
      $('#modal').modal('show');      
    }else{
      $('#modal').modal('hide');
      $('#pdf1').css('display', 'none');
    }
    if (doc==2) {
      $('#pdf2').css('display', 'block');
      $('#modal').modal('show');      
    }else{
      $('#modal').modal('hide');
      $('#pdf2').css('display', 'none');
    }
  }
</script>

<script>

function list_histo()
{
  var id=$('#ID_DOC_COMPILATION').val()
  var row_count ="1000000";
  $("#mytablehisto").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url: "<?= base_url() ?>/pip/Processus_Investissement_Public/listing_histo_compilation//" + id,
      type:"POST", 
      data:
      {
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
    language:
    {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
    }
  });
}

function list_projet()
{
  var row_count ="1000000";
  $("#mytable5").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('pip/Repartition_projet/list_projet')?>",
      type:"POST", 
      data:
      {
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
    language:
    {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
    }
  });
}

function list_objectif()
{
  var row_count ="1000000";
  $("#mytable6").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('pip/Repartition_objectif/list_objectif')?>",
      type:"POST", 
      data:
      {
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
    language:
    {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
    }
  });
}

function list_objectif_pnd()
{
  var row_count ="1000000";
  $("#mytable7").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('pip/Repartition_obj_strat_pnd/list_objectif_pnd')?>",
      type:"POST", 
      data:
      {
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
    language:
    {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
    }
  });
}

function list_intervention()
{
  var row_count ="1000000";
  $("#mytable8").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('pip/Repartition_intervention_pnd/list_intervention')?>",
      type:"POST", 
      data:
      {
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
    language:
    {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
    }
  });
}

function list_gap_pilier()
{
  var row_count ="1000000";
  $("#mytable10").DataTable(
  {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "targets":[0,4],
    "oreder":[[ 0, 'desc' ]],
    "ajax":
    {
      url:"<?= base_url('pip/Gap_financement_pilier/list_gap_pilier')?>",
      type:"POST", 
      data:
      {
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
    language:
    {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
    }
  });
}
</script>

<script>
  $(document).ready(function () {
    // list_projet()
    // list_objectif()
    // list_objectif_pnd()
    // list_intervention()
    // list_gap_pilier()
    list_histo()
  });
</script>