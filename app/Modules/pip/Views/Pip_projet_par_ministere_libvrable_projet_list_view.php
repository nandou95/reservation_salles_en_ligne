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
                    <div class="col-md-9" style="float: left;">
                      <h5 class="header-title text-black">
                        <?= lang('messages_lang.titre_ministere_institution') ?>
                      </h5>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?=base_url()?>/pip/Pip_projet_par_ministere_libvrable_projet/exporter"  type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span><?= lang('messages_lang.bouton_telecharger') ?></a>
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

                    <div class="table-responsive" >
                      <table id="mytable" class=" table table-striped table-bordered" style="width: 100%;">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th><?= lang('messages_lang.labelle_institution') ?></th>
                            <th><?= lang('messages_lang.th_projets') ?></th>
                            <th style="width:200px"><?= lang('messages_lang.labelle_livrable_extrants') ?></th>
                            <th style="width:200px"><?= lang('messages_lang.labelle_indicateur_mesure') ?></th>
                            <th style="width:200px"><?= lang('messages_lang.labelle_unite_mesure') ?></th>
                            <th style="width:200px"><?= $annees[0]->ANNEE_DESCRIPTION ?></th>
                            <th style="width:200px"><?= $annees[1]->ANNEE_DESCRIPTION ?></th>
                            <th style="width:200px"><?= $annees[2]->ANNEE_DESCRIPTION ?></th>
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
<div class="modal fade" id="mydelete">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <input type="hidden" id="deleterowId" name="RowidOne">
      </div>
      <div class="modal-footer">
        <center>
          <h5><strong><?= lang('messages_lang.question_supprimer_sfp') ?> </strong><br> <b style="background-color:prink;"><a class="btn btn-danger btn-md" onclick="remove(this)"><?= lang('messages_lang.label_oui') ?></a></b></h5>
        </center>
        <button class="btn mb-1 btn-dark" class="close" data-dismiss="modal"><?= lang('messages_lang.label_non')?></button>
      </div>
    </div>
  </div>
</div>
<script>
  function deleteData(Rowid)
  {
    $("#deleterowId").val(Rowid);
    $("#mydelete").modal("show");
  }

  function remove()
  {
    var id=$("#deleterowId").val();
    $.post('<?=base_url('pip/supprimer')?>',
    {
      id:id,
    },
    function(data)
    {
      if(data)
      {
        window.location.href="<?= base_url('pip/Source_finance_bailleur')?>";
      }
    });
  }

  $(document).ready(function()
  {
    var row_count ="1000000";
    $('#message').delay('slow').fadeOut(3000);
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":
      {
        url:"<?= base_url('pip/Pip_projet_par_ministere_libvrable_projet/listing_project_livrable')?>",
        type:"POST", 
      },
      lengthMenu: [[10,50,100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],
      dom: 'Bfrtlip',
      order:[1,'asc'],
      buttons: ['excel', 'pdf'],
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
        "oPaginate":
        {
          "sFirst": "<?= lang('messages_lang.labelle_et_premier') ?>",
          "sPrevious": "<?= lang('messages_lang.labelle_et_precedent') ?>",
          "sNext": "<?= lang('messages_lang.labelle_et_suivant') ?>",
          "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria":
        {
          "sSortAscending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
        }
      }
    });
  });
</script>