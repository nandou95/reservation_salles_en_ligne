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
                  <div class="col-md-12 d-flex">
                    <!-- <div class="col-md-12" style="float: left;"> -->
                    <div class="col-md-12" style="text-align: center;">
                      <h1 class="header-title text-black">
                      <?= lang('messages_lang.titre_sfp_list') ?> <?= $annees[0]->ANNEE_DESCRIPTION ?> <?= lang('messages_lang.labelle_et_a') ?> <?= $annees[2]->ANNEE_DESCRIPTION ?> (BIF)
                      </h1>
                    </div>
                    <div class="col-md-3" style="float: right;">
                    </div>
                  </div>
                </div>
                
                <div class="card-body">
                  <div class="row">
                    
                  </div>
                  <div class="row">
                    <?php
                    if(session()->getFlashKeys('alert'))
                    {
                      ?>
                      <div class="col-md-12">
                        <div class="w-100 bg-success text-white text-center" id="message">
                          <?php echo session()->getFlashdata('alert')['message']; ?>
                        </div>
                      </div>
                      <?php
                    }
                    ?>
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <td rowspan="2"><?= lang('messages_lang.code') ?></td>
                            <td rowspan="2"><?= lang('messages_lang.th_bailleur') ?></td>
                            <td colspan="5" style="text-align:center;"><?= lang('messages_lang.th_total_financement') ?></td>
                          </tr>
                          <tr>
                            <?php foreach($annees as $annee): ?>
                            <th><?= $annee->ANNEE_DESCRIPTION ?></th>
                            <?php endforeach; ?>
                            <th><?= lang('messages_lang.th_total') ?></th>
                            <th>%</th>
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
    list_investissement();
    $('#message').delay('slow').fadeOut(3000);
  });
</script>

<script>
function list_investissement()
{
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
      url:"<?= base_url('pip/Source_investissement_public/list_investissement')?>",
      type:"POST", 
      data:
      {
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
    language:
    {
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
}
</script>