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
                <div class="car-body">
                  <h1 class="header-title text-black"><?=$titre;?></h1><br>
                  <div class="table-responsive container " style="margin-top:50px">
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr class="text-uppercase">
                            <th><center>#</center></th>
                            <th><center> <?= lang('messages_lang.code') ?> </center></th>
                            <th><center> <?= lang('messages_lang.th_institution') ?> </center></th>
                            <th><center> <?= lang('messages_lang.link_detail') ?> </center></th>
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
    var row_count ="1000000";
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,2],
      "oreder":[[ 0, 'desc' ]],
      "ajax":{
        url:"<?= base_url('ihm/Institution/get_info')?>",
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
        'excel', 'pdf'
        ],
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.sLengthMenu_enjeux') ?>",
        "sInfo":           "<?= lang('messages_lang.sInfo_enjeux') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.sInfo_enjeux_0') ?>",
        "sInfoFiltered":   "<?= lang('messages_lang.filtre_max_total_enjeux') ?>",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords":    "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier') ?>"
        }
      }
    });
  });
</script>
