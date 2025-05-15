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
                    $get_jurid_Afaire1="btn";
                    $get_jurid_deja_fait1="btn"; 
                    $get_jurid_Avalider1="btn";
                    $get_jurid_valider1="btn";
                    $get_jurid_Acorriger1="btn";
                    $get_jurid_rejeter="btn active";
                    ?>
                    <?php include 'includes/Menu_Juridique.php'; ?>
                  </div>
                </div>
                <div class="card-body">
                  <div style="margin-left: 15px;" class="row">
                    <h1 class="header-title text-dark">
                      <?=lang('messages_lang.titre_jur_rejet')?>
                    </h1>
                  </div>
                  <div style="margin-left: 15px;" class="row">
                    <div class="col-md-6">
                      <label for="Nom" class="form-label"><?=lang('messages_lang.Lab_jur_instit')?></label>
                      <select onchange="liste(); get_sousTutel(this.value)" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                        <option value="">-<?=lang('messages_lang.selection_message')?>-</option>
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

                    <div class="col-md-6">
                      <label for="Mouvement" class="form-label"><?=lang('messages_lang.Lab_jur_tutel')?></label>
                      <select class="select2 form-control" id="SOUS_TUTEL_ID" name="SOUS_TUTEL_ID" onchange="liste()">
                        <option value=""><?=lang('messages_lang.selection_message')?></option>
                      </select>
                    </div>

                  </div>
                  <div style="margin-left: 15px" class="row">
                    <?php if (session()->getFlashKeys('alert')) : ?>
                    <div class="w-100 bg-success text-white text-center" id="message" >
                      <?php echo session()->getFlashdata('alert')['message']; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="col-md-12 table-responsive container ">
                  <table id="mytable" class=" table table-responsive table-bordered table-striped" style="width:100%">
                    <thead>
                      <tr>
                        <th><?=lang('messages_lang.col_bon_eng')?></th>
                        <th><?=lang('messages_lang.col_imputation')?></th>
                        <th><?=lang('messages_lang.libelle_imputation')?></th>
                        <th><?=lang('messages_lang.col_activite')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <th><?=lang('messages_lang.th_tache')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                        <th><?=lang('messages_lang.col_obj_eng')?></th>
                        <th><?=lang('messages_lang.col_eng_budg')?></th>
                        <th><?=lang('messages_lang.col_eng_jur')?></th>
                        <th><?=lang('messages_lang.col_option')?></th>
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
</script >

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
        url: "<?= base_url('double_commande_new/Menu_Engagement_Juridique/listing_jur_rejeter')?>",
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
  function get_sousTutel()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();

    if(INSTITUTION_ID=='')
    {
      $('#SOUS_TUTEL_ID').html('<option value="">Sélectionner</option>');
    }
    else
    {
      $('#SOUS_TUTEL_ID').html('<option value="">Sélectionner</option>');
      var url = "<?=base_url()?>/double_commande_new/Menu_Engagement_Juridique/get_sousTutel/"+INSTITUTION_ID;

      $.ajax(
      {

        url:url,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {
          $('#SOUS_TUTEL_ID').html(data.SousTutel);
        }
      });

    }
  }
</script>
