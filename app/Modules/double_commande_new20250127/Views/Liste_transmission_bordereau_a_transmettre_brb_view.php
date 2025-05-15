<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
  <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
            <h1 class="header-title"></h1>
          </div>

          <div class="row">
            <div class="col-12">
            <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                    <?php
                      $paiement_a_faire = "btn";
                      $paiement_deja_fait = "btn";
                      $bordereau_dc ="btn";
                      $bordereau_deja_dc ="btn";
                      $bordereau_brb ="btn active";
                      $bordereau_deja_brb ="btn";
                      $recep_prise_en_charge ="btn";
                      $deja_recep_prise_en_charge ="btn";
                      $recep_dir ="btn";
                      $deja_recep_dir ="btn";
                      $recepion_brb ="btn";
                      $deja_reception_brb="btn";
                      $valid_faire = "btn";
                      $valid_termnine = "btn";
                    ?>
                    <?php include  'includes/Menu_Paiement.php';?>
                  </div>
                </div>
                <div class="card-body" style="margin-top: -20px"></div>
              </div>
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;" class="card">
                 <div class="ml-2">
                  <div class="card-header">
                    <h3><?= lang('messages_lang.titre_decaissement_a_transmettre_BRB')?></h3>
                  </div>
                    <?php
                    

                    ?>

                    <div style="float: right;">
                        <a href="<?php echo base_url('double_commande_new/Transmission_borderau_brb') ?>" style="float: right; width: 150px; margin-right: 20px;margin-top:25px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?= lang("messages_lang.Transmission_TD") ?></a>
                    </div>
                </div>
                <div class="card-body">
                <div class="table-responsive " style="width: 100%;">
                  <table id="mytable" class=" table table-striped table-bordered ">
                    <thead>
                      <tr>
                        <th><center><?=lang('messages_lang.th_num_titre')?></center></th>
                        <th><center><?=lang('messages_lang.th_devise')?></center></th>
                        <th><center><?=lang('messages_lang.th_montant_decais')?></center></th>
                        <th><center><?=lang('messages_lang.pip_rapport_institutio_filtre')?></center></th>
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
  $(document).ready(function ()
  {
    var row_count ="1000000";
    $('#message').delay('slow').fadeOut(3000);
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,2],
      "oreder":[[ 0, 'desc' ]],
      "ajax":{
        url:"<?= base_url('double_commande_new/Liste_transmission_bordereau_a_transmettre_brb/listing')?>",
        type:"POST"
      },

      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[0,3],
        "orderable":false
      }],

      dom: 'Bfrtlip',
      order: [2,'desc'],
      buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
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
  });
</script>