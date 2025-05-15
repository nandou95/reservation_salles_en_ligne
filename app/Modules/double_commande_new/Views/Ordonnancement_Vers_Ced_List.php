<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <?php 
  $session  = \Config\Services::session();
  $profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
  $userfiancier = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER');
  ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
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
                <div class="card-header">
                  <div class="col-12 d-flex">
                    <?php 
                    $get_liquid_Afaire1="btn";
                    $get_liquid_deja_fait1="btn"; 
                    $get_liquid_Avalider1="btn"; 
                    $get_liquid_Acorriger1="btn";
                    $get_liquid_valider1="btn";
                    $get_liquid_rejeter1="btn";
                    $get_liquid_partielle1="btn";
                    $nbr_from_ord1="btn active";
                    ?>
                    <?php include  'includes/Menu_Liquidation.php'; ?>
                  </div>
                </div>
                <div class="card-body" style="margin-top: -20px">
                </div>                       
              </div>
              <div style="box-shadow:rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="car-body">
                  <?php
                  if(session()->getFlashKeys('alert'))
                  {
                    ?>
                    <div class="alert alert-success" id="message">
                      <?php echo session()->getFlashdata('alert')['message']; ?>
                    </div>
                    <?php
                  }
                  ?>
                  
                  <b style="font-size:20px" class="header-title text-black"><?=lang('messages_lang.liqu_faire_from_ordo') ?></b><br><br>

                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-md-4">
                        <label><?=lang('messages_lang.Lab_jur_instit')?></label>
                        <select autofocus onchange="getSousTutel();listing_liquid_Afaire()" class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                          <option value=""><?=lang('messages_lang.selection_message') ?></option>
                          <?php foreach ($institutions_user as $keyinstitution) { ?>
                            <option value="<?=$keyinstitution->INSTITUTION_ID?>">
                              <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                            <?php }?>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label><?=lang('messages_lang.Lab_jur_tutel')?> <span id="loading_sous_tutel"></span></label>
                          <select onchange="listing_liquid_Afaire()" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                            <option value="">--<?=lang('messages_lang.selection_message') ?>--</option>
                          </select>
                        </div>
                      </div>
                    </div><br><br>

                    <div class="table-responsive container ">
                      <div class="table-responsive" style="width: 100%;">
                        <table id="mytable" class=" table table-striped table-bordered">
                          <thead>
                            <tr>
                              <th class="text-uppercase"><?=lang('messages_lang.col_bon_eng')?></th>
                              <th class="text-uppercase"><?=lang('messages_lang.col_imputation')?></th>
                              <th class="text-uppercase"><?=lang('messages_lang.th_tache')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                              <th class="text-uppercase"><?=lang('messages_lang.labelle_devise')?></th>
                              <th class="text-uppercase"><?=lang('messages_lang.col_eng_budg')?></th>
                              <th class="text-uppercase"><?=lang('messages_lang.col_eng_jur')?></th>
                              <th><?=lang('messages_lang.col_liquid')?></th>
                              <th class="text-uppercase"><?=lang('messages_lang.col_option')?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
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
          </div>
        </main>
      </div>
    </div>



    <?php echo view('includesbackend/scripts_js.php');?>
  </body>
  </html>
  <script type="text/javascript">
    $(document).ready(function ()
    {
      listing();
    });
  </script>

  <script>
    function listing()
    {
      var INSTITUTION_ID=$('#INSTITUTION_ID').val();
      var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();

      var row_count ="1000000";
      $("#mytable").DataTable(
      {
        "processing":true,
        "destroy" : true,
        "serverSide":true,
        "targets":[0,4],
        "oreder":[[ 0, 'desc' ]],
        "ajax":
        {
          url:"<?= base_url('double_commande_new/Ordonnancement_Vers_Ced/listing')?>",
          type:"POST", 
          data:
          {
            INSTITUTION_ID:INSTITUTION_ID,
            SOUS_TUTEL_ID:SOUS_TUTEL_ID
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
        buttons: [],
      language: {
        "sProcessing": "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch": "<?= lang('messages_lang.search_button') ?>&nbsp;:",
        "sLengthMenu": "<?= lang('messages_lang.sLengthMenu_enjeux') ?>",
        "sInfo": "<?= lang('messages_lang.sInfo_enjeux') ?>",
        "sInfoEmpty": "<?= lang('messages_lang.sInfo_enjeux_0') ?>",
        "sInfoFiltered": "(<?= lang('messages_lang.filtre_max_total_enjeux') ?>)",
        "sInfoPostFix": "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords": "<?= lang('messages_lang.aucun_element_afficher_enjeux') ?>",
        "sEmptyTable": "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst": "<?= lang('messages_lang.labelle_1') ?>",
          "sPrevious": "<?= lang('messages_lang.btn_precedent') ?>",
          "sNext": "<?= lang('messages_lang.btn_suivant') ?>",
          "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending": "<?= lang('messages_lang.sSortAscending_enjeux') ?>",
          "sSortDescending": "<?= lang('messages_lang.sSortDescending_enjeux') ?>"
        }
      }
      });
    }
  </script>

<div class="modal" id="multi_tache" role="dialog">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title text-center" id="exampleModalLabel">
          <?=lang('messages_lang.list_task')?>
        </h3>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="task_id" id="task_id">
        <div class="table-responsive">
          <table id='mytable3' class="table table-bordered table-striped table-hover table-condensed " style="width: 100%;">
            <thead>
              <tr class="text-uppercase" >
                <th class="text-uppercase"><?=lang('messages_lang.th_tache')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <th class="text-uppercase"><?=lang('messages_lang.list_quantite')?></th>
                <th class="text-uppercase"><?=lang('messages_lang.labelle_devise')?></th>
                <th class="text-uppercase"><?=lang('messages_lang.col_eng_budg')?></th>
                <th class="text-uppercase"><?=lang('messages_lang.col_eng_jur')?></th>
                <th><?=lang('messages_lang.col_liquid')?></th>
              </tr>
            </thead>
            <tbody id="table3">
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button class="btn mb-1 btn-secondary" class="close" data-dismiss="modal"><?=lang('messages_lang.quiter_action')?></button>
        </div>
      </div>
    </div>
  </div>
</div>
  <!--SCRIPT POUR LES TACHES------>
<script type="text/javascript">
  function get_task(id)
  {
   $("#multi_tache").modal("show");

   var row_count ="1000000";
   table=$("#mytable3").DataTable({
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "oreder":[],
    "ajax":{
      url:"<?= base_url('/double_commande_new/Liquidation_Double_Commande/detail_task')?>",
      type:"POST",
      data: {
        task_id:id
      }
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength: 10,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    language: {
      "sProcessing":     "Traitement en cours...",
      "sSearch":         "Rechercher&nbsp;:",
      "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
      "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
      "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
      "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
      "sInfoPostFix":    "",
      "sLoadingRecords": "Chargement en cours...",
      "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
      "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
      "oPaginate": {
        "sFirst":      "Premier",
        "sPrevious":   "Pr&eacute;c&eacute;dent",
        "sNext":       "Suivant",
        "sLast":       "Dernier"
      },
      "oAria": {
        "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
        "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
      }
    }

  });
 } 
</script>
