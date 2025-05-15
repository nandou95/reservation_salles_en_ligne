
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

             

              <div class="col-12">

                <?php
                if(session()->getFlashKeys('alert'))
                {
                  ?>
                  <center class="ml-5" style="height:100px;width:90%" >
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
                          <th><?= lang('messages_lang.th_projets') ?></th>
                          <th><?= lang('messages_lang.table_Action') ?></th>
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
        url:"<?= base_url()?>/pip/presentation_fichier_investisement_public/liste_projet_detail",
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
        "sSearch": "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu": "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo": "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty": "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered": "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
        "sInfoPostFix": "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords": "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable": "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst": "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious": "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext": "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  };
</script>