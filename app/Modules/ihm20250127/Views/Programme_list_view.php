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
                                                <?= lang('messages_lang.liste_des_programmes') ?>
                                            </h1>
                                        </div>
                                        <div class="col-md-6" style="float: right;">
                                            <a href="<?=base_url('ihm/Programme/ajout') ?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-plus pull-right"></span> <?= lang('messages_lang.labelle_et_nouveau') ?> </a>
                                        </div>
                                    </div>
                                </div>
                            <div class="car-body">
                                <div class="table-responsive" style="width: 100%;">
                                    <table id="mytable" class=" table table-striped table-bordered">
                                        <thead>
                                            <tr class="text-uppercase">
                                                <th>#</th>
                                                <th><?= lang('messages_lang.th_programme') ?></th>
                                                <th><?= lang('messages_lang.code') ?></th>
                                                <th><?= lang('messages_lang.labelle_institution') ?></th>
                                                <th><?= lang('messages_lang.th_objectif_programme') ?></th>
                                                <th><?= lang('messages_lang.th_statut') ?></th>
                                                <th><?= lang('messages_lang.dropdown_link_options') ?></th>
                                            </tr>
                                        </thead>
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
<div class="modal fade" id="mydelete" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div id="mess" class="modal-body"></div>
      <div id="foot" class="modal-footer"></div>
    </div>
  </div>
</div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        table_institutions_program();
    });

    function show_modal(id)
    {
      var message = $('#message' + id).html();
      $('#mess').html(message);
      var footer = $('#footer' + id).html();
      $('#foot').html(footer);
      $('#mydelete').modal('show');
    }
  
    function table_institutions_program()
    {
        var row_count ="1000000";
        $("#mytable").DataTable({
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "targets":[0,4],
          "oreder":[[ 0, 'desc' ]],
          "ajax":{
            url:"<?= base_url('ihm/Programme/listing')?>",
            type:"POST", 
          },

          lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
          pageLength: 10,
          "columnDefs":[{
            "targets":[],
            "orderable":false
          }],

          dom: 'Bfrtlip',
          order: [4,'desc'],
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
    }
</script>