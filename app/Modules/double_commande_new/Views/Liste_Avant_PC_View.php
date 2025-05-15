<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url("template/css") ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                      <div class="col-12 d-flex">
                        <?php
                        $reception_obr = "btn";
                        $prise_charge_compt = "btn";
                        $etab_titre = "btn";
                        $sign_dir_compt = "btn";
                        $sign_dir_dgfp = "btn";
                        $sign_dir_min = "btn";

                        $recep_prise_en_charge ="btn";
                        $deja_recep_prise_en_charge ="btn";
                        $recep_dir ="btn";
                        $deja_recep_dir ="btn";
                        $recepion_brb ="btn";
                        $deja_reception_brb ="btn";

                        $bordereau_dc ="btn";
                        $bordereau_deja_dc ="btn";
                        $bordereau_brb ="btn";
                        $bordereau_deja_brb="btn";

                        $valid_faire = "btn";
                        $valid_termnine = "btn";

                        $get_nbr_av_obr1="btn";
                        $get_nbr_av_pc1="btn active";
                        ?>
                        <?php include  'includes/Menu_Paiement.php';?>
                      </div>
                    </div>
              </div>
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <br>
                <div>
                  <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                    <div class="card-body" style="margin-top: -20px"></div>
                  </div>
                  <h1 class="header-title text-dark"><?=lang("messages_lang.Transmission_vers_le_service_prise_en_charge")?></h1>
                </div>

                <div style="margin-right: 20px; float: right;">
                    <a href="<?= base_url("double_commande_new/Avant_PC/add") ?>" class='btn btn-primary' style="float: right;"> <?= lang("messages_lang.Transmission_TD") ?></h1> </a>
                </div>
                <div class="row ml-3">
                  <div class="col-md-3">
                      <div class="form-group">
                        <label for="Nom" class="form-label"><?=lang('messages_lang.Lab_jur_instit')?></label>
                        <select autofocus onchange="getSousTutel();listing_ordon_deja_fait()" class="form-control select2" id="INSTITUTION_ID" name="INSTITUTION_ID">
                          <option value=""><?=lang('messages_lang.selection_message')?></option>
                          <?php foreach ($institutions as $keyinstitution) { ?>
                            <option value="<?=$keyinstitution->INSTITUTION_ID?>" <?=$INST_ID == $keyinstitution->INSTITUTION_ID ? "selected" : ""?>><?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                          <?php }?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="Mouvement" class="form-label"><?=lang('messages_lang.Lab_jur_tutel')?></label>
                        <select onchange="liste()" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                          <option value="">-<?=lang('messages_lang.selection_message')?>-</option>
                        </select>
                      </div>
                    </div>
                </div>
                <div class="card-body">
                  <div style="margin-left: 15px" class="row">
                    <?php if (session()->getFlashKeys('alert')) : ?>
                      <div class="w-100 bg-success text-white text-center" id="message">
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="table-responsive container ">
                    <table id="mytable" class=" table table-bordered table-striped">
                      <thead>
                        <tr class="text-uppercase text-nowrap">
                          <th> <?= lang("messages_lang.bon_engagement_transmission_du_bordereau") ?> </th>
                          <th> <?= lang("messages_lang.labelle_devise") ?> </th>
                          <th> <?= lang("messages_lang.table_mont_ord") ?> </th>
                          <th> <?= lang("messages_lang.label_inst") ?> </th>
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
  <?php echo view('includesbackend/scripts_js.php'); ?>
</body>
</html>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(3000);
  $(document).ready(function() {
    liste();
  });

  function liste() {
    change_count()
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Liste_Avant_PC/listing') ?>",
        type: "POST",
        
      },

      lengthMenu: [
        [10, 50, 100, row_count],
        [10, 50, 100, "All"]
      ],
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
<script>
  function change_count()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    INSTITUTION_ID=(INSTITUTION_ID!='')?INSTITUTION_ID:0
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    SOUS_TUTEL_ID=(SOUS_TUTEL_ID!='')?SOUS_TUTEL_ID:0

    $.post('<?=base_url('double_commande_new/Liste_Paiement/change_count')?>',
    {
      ANNEE_BUDGETAIRE_ID:0,
      INSTITUTION_ID,
      SOUS_TUTEL_ID,
      DATE_DEBUT:0,
      DATE_FIN:0
    },
    function(data)
    {
      $('#recep_prise_charge').html(data.recep_prise_charge);
      $('#deja_recep_prise_charge').html(data.deja_recep_prise_charge);
      $('#get_nbr_av_obr').html(data.get_nbr_av_obr);      
      $('#get_recep_obr').html(data.get_recep_obr);      
      $('#get_nbr_av_pc').html(data.get_nbr_av_pc);      
      $('#get_prise_charge').html(data.get_prise_charge);      
      $('#get_prise_charge_corr').html(data.get_prise_charge_corr);     
      $('#get_etape_reject_pc').html(data.get_etape_reject_pc);      
      $('#get_etape_corr').html(data.get_etape_corr);      
      $('#get_etab_titre').html(data.get_etab_titre);      
      $('#get_recep_td_corriger').html(data.get_recep_td_corriger);     
      $('#get_etab_titre_corr').html(data.get_etab_titre_corr);      
      $('#get_bord_dc').html(data.get_bord_dc);      
      $('#get_bord_deja_dc').html(data.get_bord_deja_dc);      
      $('#recep_dir_comptable').html(data.recep_dir_comptable);      
      $('#deja_recep_dir_comptable').html(data.deja_recep_dir_comptable);      
      $('#get_sign_dir_compt').html(data.get_sign_dir_compt);      
      $('#get_sign_dir_dgfp').html(data.get_sign_dir_dgfp);      
      $('#get_sign_ministre').html(data.get_sign_ministre);      
      $('#get_titre_valide').html(data.get_titre_valide);      
      $('#get_titre_termine').html(data.get_titre_termine);      
      $('#get_bord_brb').html(data.get_bord_brb);      
      $('#get_bord_deja_trans_brb').html(data.get_bord_deja_trans_brb); 
    })
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