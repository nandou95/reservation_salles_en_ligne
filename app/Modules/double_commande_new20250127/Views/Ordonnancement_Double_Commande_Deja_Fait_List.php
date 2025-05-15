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
                    $get_ordon_Afaire1="btn";
                    $get_ordon_deja_fait1="btn active";
                    $bordereau_spe ="btn";
                    $bordereau_deja_spe ="btn";
                    ?>
                    <?php include  'includes/Menu_Ordonnancement.php'; ?>
                  </div>
                </div>
                <div class="card-body" style="margin-top: -20px">
                </div>                       
              </div>
              <div style="box-shadow:rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="car-body">
                  <b style="font-size:20px" class="header-title text-black"><?=lang('messages_lang.titre_ordo_faits')?></b><br><br>
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-md-4">
                        <label><?=lang('messages_lang.Lab_jur_instit')?></label>
                        <select autofocus onchange="getSousTutel();listing_ordon_deja_fait()" class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                          <option value=""><?=lang('messages_lang.selection_message')?></option>
                          <?php foreach ($institutions_user as $keyinstitution) { ?>
                            <option value="<?=$keyinstitution->INSTITUTION_ID?>"><?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                          <?php }?>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label><?=lang('messages_lang.Lab_jur_tutel')?><span id="loading_sous_tutel"></span></label>
                        <select onchange="listing_ordon_deja_fait()" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                          <option value="">--<?=lang('messages_lang.selection_message')?>--</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <br>
                  <br>
                  <div style="margin-left: 15px" class="row">
                      <?php if (session()->getFlashKeys('alert')) : ?>
                      <div class="w-100 bg-success text-white text-center" id="message" >
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="table-responsive container ">
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <th><?=lang('messages_lang.col_bon_eng')?></th>
                            <th><?=lang('messages_lang.col_imputation')?></th>
                            <th><?=lang('messages_lang.col_activite')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                            <th><?=lang('messages_lang.col_obj_eng')?></th>
                            <th><?=lang('messages_lang.col_eng_budg')?></th>
                            <th><?=lang('messages_lang.col_eng_jur')?></th>
                            <th><?=lang('messages_lang.col_liquid')?></th>
                            <th><?=lang('messages_lang.titre_ordon')?></th>
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
    listing_ordon_deja_fait();
  });
</script>
<script>
  function listing_ordon_deja_fait()
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
        url:"<?= base_url('double_commande_new/Ordonnancement_Double_Commande/listing_ordon_deja_fait')?>",
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
      buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
        ],
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

<script>
  function getSousTutel()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();

    $.ajax(
    {
      url : "<?=base_url('/double_commande_new/Ordonnancement_Double_Commande/getSousTutel')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:
      {
        INSTITUTION_ID:INSTITUTION_ID
      },
      beforeSend:function() {
        $('#loading_sous_tutel').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success:function(data)
      {   
        $('#SOUS_TUTEL_ID').html(data.tutel);
        $('#loading_sous_tutel').html("");
      }
    });
  }
</script>
