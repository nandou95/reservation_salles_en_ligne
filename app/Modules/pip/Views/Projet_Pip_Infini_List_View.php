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
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                   <?php
                   $incomplet="btn active";
                   $Complet="btn"; 
                   $corriger="btn"; 
                   $valide="btn";
                   ?>
                   <?php include  'includes/Menu_Liste_pip.php'; ?> 
                 </div>

               </div>

               <div class="card-body" style="margin-top: -20px">
               </div>                       
             </div>
             <br>
             <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
              <div class="col-md-12 d-flex">
                <div class="col-md-6" style="float: left;">
                  <h4 style="margin-left: 1%;margin-top:10px"><?= lang('messages_lang.titre_projet_incomplet') ?></h4>
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
                        <option value=""><?= lang('messages_lang.label_selecte') ?></option>
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
                  <div class="col-md-6">
                  </div>
                  <div class="col-md-2">
                    <a href="<?=base_url('pip/Processus_Investissement_Public/demande') ?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-plus pull-right"></span> <?= lang('messages_lang.labelle_et_nouveau') ?></a>
                  </div>
                </div>
                <div class="row">
                  <div class="table-responsive" style="width: 100%;">
                    <table id="mytable" class=" table table-striped table-bordered">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th><?= lang('messages_lang.th_numero_projet') ?></th>
                          <th><?= lang('messages_lang.th_nom_projet') ?></th>
                          <th><?= lang('messages_lang.col_etape') ?></th>
                          <th><?= lang('messages_lang.th_statut_projet') ?></th>
                          <th><?= lang('messages_lang.date') ?></th>
                          <th><?= lang('messages_lang.labelle_et_action') ?></th>
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

<div class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('messages_lang.titre_confirmation') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="">
        <input type="hidden" id="projet">
        <p><?= lang('messages_lang.question_supprimer') ?></p>
      </div>
      <div class="modal-footer">
        <a class="btn btn-info" onclick="confirmer()"><?= lang('messages_lang.label_oui') ?></a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('messages_lang.label_non') ?></button>
      </div>
      </form>
    </div>
  </div>
</div>

<?php echo view('includesbackend/scripts_js.php');?>
</body>

</html>
<script>
 $(document).ready(function () {

  liste()
});
</script>

<script>
  function confirmer()
  {
    var id= $('#projet').val()
    $.ajax(
					{
						type: 'POST',
						url: '<?= base_url()?>/pip/Projet_Pip_Infini/annuler_projet_incomplet/'+id,
						
						success: function(response)
						{
              console.log(response)
              if(response == 1)
              {

                window.location.href='<?= base_url('pip/Projet_Pip_Infini/liste_pip_infini') ?>'
              }
							
						}
				}
	
  )}
</script>

<script>
  function confirmation(id)
  {
    $('#projet').val(id)
     $('.modal').modal('show')
  }
</script>

<script>


  function liste() 
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val(); 
   $('#message').delay('slow').fadeOut(3000);
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('pip/Projet_Pip_Infini/liste_projet_infini')?>",
        type: "POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
        }
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
