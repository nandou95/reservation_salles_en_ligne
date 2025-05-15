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

                <div class="card-body">
                  <div class="col-12" style="float: left;">
                   <br>
                   <h1 class="header-title text-dark">
                    <?=$titre;?><br><br>
                  </h1>
                </div>


                <div class="table-responsive container ">

                  <div></div>
                  <table id="mytable" class=" table table-bordered table-striped">
                    <thead>
                      <tr>
                       <th>#</th>                              
                       <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.institution_origine') ?></th>
                       <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.titre_ligne_budg_origine') ?></th>
                       <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.tache_origin') ?></th>
                       <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.labelle_tranche') ?></th>
                       <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.label_mont_orig') ?></th>
                       <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.inst_dest') ?></th>
                       <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.ligne_budg_dest') ?></th>
                       <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.tache_dest') ?></th>
                       <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.mont_rec') ?></th>
                      </tr>
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
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Transfert_Double_Commande/listing_Transfert')?>",
        type: "POST",
        data: {}
      },

      lengthMenu: [[10, 50, 100, row_count], [10, 50, 100, "All"]],
      pageLength: 10,
      "columnDefs": [{
        "targets": [],
        "orderable": false
      }],

      dom: 'Bfrtlip',
      order: [],
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
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
