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
                      <h1 class="header-title text-black">
                      <?= lang('messages_lang.labelle_et_liste')?>
                        
                      </h1>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="Proc_Etape/ajout" style="float: right;margin: 40px" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> <?= lang('messages_lang.nouvelle_action')?> </a>
                    </div>
                  </div>
                </div>
                
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-4">
                      <label for="PROCESS_ID" class="form-label"> <?= lang('messages_lang.processus_action') ?> </label>
                      <select onchange="liste();" class="form-control" name="PROCESS_ID" id="PROCESS_ID">
                        <option value=""><?= lang('messages_lang.labelle_selecte') ?></option>
                        <?php
                        foreach ($process as $key) {
                          if ($key->PROCESS_ID == set_value('PROCESS_ID')) {
                        ?>
                            <option value="<?= $key->PROCESS_ID; ?>" selected><?= $key->NOM_PROCESS; ?></option>
                          <?php
                          } else {
                          ?>
                            <option value="<?= $key->PROCESS_ID; ?>"><?= $key->NOM_PROCESS; ?></option>
                        <?php
                          }
                        }
                        ?>
                      </select>
                    </div>
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
                            <th>#</th>
                            <th><?= lang('messages_lang.labelle_et_etape')?></th>
                            <th><?= lang('messages_lang.labelle_et_proce')?></th>
                            <th><?= lang('messages_lang.labelle_et_profil')?></th>
                            <th> <?= lang('messages_lang.labelle_et_statut')?></th>
                            <th><?= lang('messages_lang.labelle_et_action')?></th>
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
  <div class='modal fade' id='affiche_profil' data-backdrop="static">
					<div class='modal-dialog'>
						<div class='modal-content'>

							<div class='modal-body'>
								<center>
								<h5>
									<strong><?= lang('messages_lang.labelle_et_mod_prof')?></strong><br>
								</h5>
								</center>
								<div>
									<ul id='profil_list' data-etape_id=''>
									</ul>
								</div>
							</div>
							<div class='modal-footer'>
								
								<button class='btn btn-primary btn-md' data-dismiss='modal'>
								<?= lang('messages_lang.labelle_et_mod_quiter')?>
								</button>
							</div>
						</div>
					</div>
				</div>
    </body>
</html>


<script type="text/javascript">
  function show_modal(id)
  {
    var message=$('#message'+id).html();
    $('#mess').html(message);
    var footer=$('#footer'+id).html();
    $('#foot').html(footer);
    $('#mydelete').modal('show');
  }
</script>

<!--******************* Modal pour supprimer dans le cart ***********************-->
<div class="modal fade" id="mydelete" tabindex="-1" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">

      <div id="mess" class="modal-body">

      </div>
      <div id="foot" class="modal-footer">

      </div>
    </div>
  </div>
</div>
<!--******************* Modal pour confirmer les infos saisies ***********************-->

<script>
  $(document).ready(function() {
    liste();
  });
</script>

<script>

  function liste() 
  {
    var PROCESS_ID = $('#PROCESS_ID').val();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "processing": true,
      "destroy": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('ihm/Proc_Etape/listing') ?>",
        type: "POST",
        data: {
          PROCESS_ID: PROCESS_ID
        },
      },
      lengthMenu: [
        [10, 50, 100, row_count],
        [10, 50, 100, "All"]
      ],
      pageLength: 10,
      "columnDefs": [{
        "targets": [0, 4],
        "orderable": false
      }],
      dom: 'Bfrtlip',
      //order:[1,'desc'],
      buttons: [
        'excel', 'pdf'
      ],
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal')?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
        "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
        "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
        "oPaginate": {
          "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
          "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
          "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
          "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
        }
      }
    });
  }

</script>