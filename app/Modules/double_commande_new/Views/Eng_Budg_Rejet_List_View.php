<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
 
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    
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
                    $eng_budg_sans_be="btn";
                    $eng_budg_deja_fait="btn";
                    $eng_budg_a_valider="btn";
                    $eng_budg_a_corriger="btn";
                    $eng_budg_deja_valide="btn";
                    $eng_budg_rej ="btn active";
                    ?>
                    <?php include  'includes/Menu_Engag_Budgetaire.php'; ?> 
                  </div>
                </div>
                <div class="card-body">
                  <div class="col-12" style="float: left;">
                    <h1 class="header-title text-dark"><?=lang('messages_lang.list_eng_budg_reject')?></h1>
                  </div>
                  <div class="row">
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="Nom" class="form-label"><?=lang('messages_lang.labelle_institution')?></label>
                        <select onchange="get_soutut()" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                          <option value="">-<?=lang('messages_lang.labelle_selecte')?>-</option>
                          <?php
                          foreach($institutions as $key)
                          {
                            if($key->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                            {
                              echo "<option value='".$key->INSTITUTION_ID."'  selected>".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                            }
                            else
                            {
                              echo "<option value='".$key->INSTITUTION_ID."' >".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                            }
                          }
                          ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="Mouvement" class="form-label"><?=lang('messages_lang.table_st')?></label>
                        <select onchange="liste()" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                          <option value="">-<?=lang('messages_lang.labelle_selecte')?>-</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div style="margin-left: 15px" class="row">
                    <?php if (session()->getFlashKeys('alert'))
                    {
                      ?>
                      <div class="w-100 bg-success text-white text-center" id="message" >
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                      <?php
                    }
                    ?>
                  </div>

                  <div class="table-responsive container ">
                    <table id="mytable" class=" table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th class="text-uppercase"><?=lang('messages_lang.col_bon_eng')?></th>
                          <th class="text-uppercase"><?=lang('messages_lang.col_imputation')?></th>
                          <th class="text-uppercase"><?=lang('messages_lang.th_tache')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                          <th class="text-uppercase"><?=lang('messages_lang.labelle_devise')?></th>
                          <th class="text-uppercase"><?=lang('messages_lang.col_eng_budg')?></th>
                          <th class="text-uppercase"><?=lang('messages_lang.col_option')?></th>
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
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(30000);
  $(document).ready(function()
  {
    liste();
  });
</script>

<script type="text/javascript">
  function liste() 
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val(); 
    var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Menu_Engagement_Budgetaire/listing_eng_rejette')?>",
        type: "POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
          SOUS_TUTEL_ID:SOUS_TUTEL_ID
        }
      },

      lengthMenu: [[10, 50, 100, row_count], [10, 50, 100, "All"]],
      pageLength: 10,
      "columnDefs": [{
        "targets": [],
        "orderable": false
      }],

      dom: 'Bfrtlip',
      order: [],
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
<script type="text/javascript">
  function get_soutut()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    $.post('<?=base_url('double_commande_new/Menu_Engagement_Budgetaire/get_soutut')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID
    },
    function(data)
    {
      $('#SOUS_TUTEL_ID').html(data.html);
      SOUS_TUTEL_ID.InnerHtml=data.html;
      liste();
    })
  }
</script>

<!--MODAL POUR LES TACHES ------>

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
   $('#task_id').val(id);
   var task_id = $('#task_id').val();

   $("#multi_tache").modal("show");

   var row_count ="1000000";
   table=$("#mytable3").DataTable({
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "oreder":[],
    "ajax":{
      url:"<?= base_url('/double_commande_new/Menu_Engagement_Budgetaire/detail_task')?>",
      type:"POST",
      data: {
        task_id:task_id
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