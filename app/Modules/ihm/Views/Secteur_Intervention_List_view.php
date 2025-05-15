<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
 <?php $validation = \Config\Services::validation(); ?>
 <?php 
 $session  = \Config\Services::session();
 $gdc = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
 ?>
 <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
 <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
 <style type="text/css">
   .modal-signature {
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    border-bottom-right-radius: .3rem;
    border-bottom-left-radius: .3rem
  }
</style>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
         <div class="row" style="margin-top: -5px">
          <div class="col-12">
            <div style="margin-top: -25px;" class="card">
              <div class="card-header">
                <a href="<?php echo base_url('ihm/Secteur_Intervention/add_secteur')?>" id="btn_save"  class="btn btn-primary" style="float:right;color:white"><i class="fa fa-plus"></i>Nouveau</a>
                <b style="font-size:20px" class="header-title text-black"><?= $titre;?></b><br><br>
              </div>

              <div class="col-12">

                <?php
                if(session()->getFlashKeys('alert'))
                {
                  ?>
                  <center class="ml-5" style="height=100px;width:90%" >
                    <div class="w-100 bg-success text-white text-center"  id="message">
                      <?php echo session()->getFlashdata('alert')['message']; ?>
                    </div>
                  </center>
                  <?php
                } ?>
                <div class="container-xxl py-2 subpage_bg">
                  <div class="col-md-12 right-side table table-responsive">
                    <table id="mytable" class="table table-bordered">
                      <thead>
                        <tr> 
                          <th>#</th>
                          <th><?= lang('messages_lang.ajout_secteur_intervention_lib')?></th>
                          <th>ACTION</th>                      
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
  $(document).ready(function () {
    liste_bon();
  });
</script>
<script>

  function liste_bon()
  {
    var row_count ="1000000";
    $('#message').delay('slow').fadeOut(30000);

    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url:"<?= base_url()?>/ihm/Secteur_Intervention/liste_secteur",
        type: "POST",
        data: 
        {},
        beforeSend: function() {}
      },
      lengthMenu: [[10, 50, 100, row_count], [10, 50, 100, "All"]],
      pageLength: 10,
      "columnDefs": [{
        "targets": [],
        "orderable": false
      }],
      dom: 'Bfrtlip',
      order:[],
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
  };
</script>