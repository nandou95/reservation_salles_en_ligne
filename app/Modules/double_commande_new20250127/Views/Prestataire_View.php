<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>

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
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
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

                  <h1 class="header-title text-black"><?= lang('messages_lang.label_prest_list') ?></h1><br>

                  <div class="col-md-12">
                    <div class="col-md-3" style="float:right;">
                      <a href="<?php echo base_url();?>/double_commande_new/Prestataire/add" style="float: right;" class="btn btn-primary float-right mb-3"><i class="fa fa-plus"></i> <?= lang('messages_lang.labelle_et_nouveau') ?> </a>
                    </div>
                  </div>

                  <div class="table-responsive container ">
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <th><center>#</center></th>
                            <th><center><?= lang('messages_lang.soumen_prestat') ?></center></th>
                            <th><center><?= lang('messages_lang.labelle_NIF') ?></center></th>
                            <th><center><?= lang('messages_lang.table_banque') ?></center></th>
                            <th><center><?= lang('messages_lang.label_cpte_bak') ?></center></th>
                            <th><center><?= lang('messages_lang.label_type_benef') ?></center></th>
                            <th><center><?= lang('messages_lang.ajout_ind') ?></center></th>
                            
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

<script type="text/javascript">
  $(document).ready(function ()
  {
    listing()
    $("#message").delay(5000).hide('slow');
  });

  function listing()
  {
    var row_count ="1000000";
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,4],
      "ajax":
      {
        url:"<?= base_url('double_commande_new/Prestataire/listing')?>",
        type:"POST",
        data: {},
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],
      dom: 'Bfrtlip',
      order:[2,'asc'],
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

