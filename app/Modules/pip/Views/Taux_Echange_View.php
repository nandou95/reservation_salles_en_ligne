
<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>

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

                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-6">
                      <h1 class="header-title text-black">
                        <?= lang('messages_lang.label_droit_taux') ?>
                     </h1>
                   </div>
                   <div class="col-md-6" style="float: right;">

                    <a href="<?=base_url('pip/Taux_Echange/new')?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-plus pull-right"></span> <?= lang('messages_lang.labelle_et_nouveau') ?></a>
                  </div>
                </div>
              </div>

              <div class="card-body">
                <div class="table-responsive" style="width: 100%;">
                    <div class="card-body">
                     
                      <?php
                      if(session()->getFlashKeys('alert'))
                      {
                        ?>
                        <div class="w-100 bg-success text-white text-center" id="message">
                          <?php echo session()->getFlashdata('alert')['message']; ?>
                        </div>
                        <?php
                      }
                      ?>
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class="table table-striped table-bordered">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th><?= lang('messages_lang.labelle_devise') ?></th>
                            <th><?= lang('messages_lang.label_droit_taux') ?></th>
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
        url:"<?= base_url()?>/pip/Taux_Echange/listing",
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

<script>
  function modal(id)
  {
    $('#TAUX_ECHANGE_ID').val(id);
    $('#exampleModal').modal()
  }
</script>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-body">
        <center><h5><strong><?= lang('messages_lang.question_suppr_taux') ?> </strong></h5></center>
      </div>
      <div class="modal-footer">
        <form action="<?=base_url('pip/Taux_Echange/delete') ?>" method="POST"> 
          <input type="hidden" name="TAUX_ECHANGE_ID" id="TAUX_ECHANGE_ID">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('messages_lang.label_ferm') ?></button>
          <button type="submit" class='btn btn-danger btn-md' ><?= lang('messages_lang.supprimer_action') ?></button>
        </form>
      </div>
    </div>
  </div>
</div>