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
                <div class="col-md-12 d-flex">
                  <div class="col-md-6" style="float: left;">
                    <h4 style="margin-left: 1%;margin-top:10px"><?= lang('messages_lang.titre_liste_projet') ?></h4>
                  </div>
                  <div class="col-md-6" style="float: right;">
                      
                      <a href="<?=base_url('pip/Processus_Investissement_Public/demande') ?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-plus pull-right"></span> Nouveau</a>
                  </div>
                </div>
              
              <div class="card-body">
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

                  <div class="col-md-4">
                  <div class="form-group">
                      <label for="Nom" class="form-label"><?= lang('messages_lang.labelle_institution') ?></label>
                      <select  onchange="liste()" class="form-control select2" name="INSTITUTION_ID" id="INSTITUTION_ID">
                        <option value="">-- <?= lang('messages_labelle_Séléctioner') ?> --</option>
                        <?php
                        foreach($institution as $key)
                        {
                          if($key->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                          {
                            echo "<option value='".$key->INSTITUTION_ID."'  selected>".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                          }
                          else
                          {
                            echo "<option value='".$key->INSTITUTION_ID."' >".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                          }
                        }
                        ?>
                      </select>
                    </div>
                  </div>

                </div>
                <div class="row">
                  <div class="table-responsive" style="width: 100%;">
                    <table id="mytable" class=" table table-striped table-bordered">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th class="text-uppercase"><?= lang('messages_lang.th_code_demande') ?></th>
                          <th class="text-uppercase"><?= lang('messages_lang.labelle_et_proce') ?></th>
                          <th class="text-uppercase"><?= lang('messages_lang.labelle_et_etape') ?></th>
                          <th class="text-uppercase"><?= lang('messages_lang.th_instit') ?></th>
                          <th class="text-uppercase"><?= lang('messages_lang.th_date_demande') ?></th>
                          <th class="text-uppercase"><?= lang('messages_lang.labelle_et_action') ?></th>
                        </tr>
                      </thead>
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
<script>
  $(document).ready(function()
  {
   liste()
  });
</script>

<script>
 function  liste(){
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var row_count ="1000000";
    $('#message').delay('slow').fadeOut(3000);
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":
      {
        url:"<?= base_url('pip/Processus_Investissement_Public/listing')?>",
        type:"POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
        }
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[0,4],
        "orderable":true
      }],
      dom: 'Bfrtlip',
      order:[0,'desc'],
      buttons: [
      'excel', 'pdf'
      ],
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_vide') ?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
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
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }

    });
  }
</script>