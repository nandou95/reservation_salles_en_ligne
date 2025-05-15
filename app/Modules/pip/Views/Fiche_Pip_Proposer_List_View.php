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
               <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                   <?php
                   $compiler="btn";
                   $proposer="btn active"; 
                   $corriger="btn"; 
                   $valider="btn";
                   ?>
                   <?php include  'includes/Menu_Liste_compilation.php'; ?> 
                 </div>

               </div>

               <div class="card-body" style="margin-top: -20px">
               </div>                       
             </div>
             <br>
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="col-md-12 d-flex">
                  <div class="col-md-6" style="float: left;">
                    <h4 style="margin-left: 1%;margin-top:10px"><?= lang('messages_lang.titre_pip_corriger_list') ?></h4>
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


                  </div>
                  <div class="row">
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>CODE&nbsp;PIP</th>
                            <th>PIP</th>
                            <th><?= lang('messages_lang.col_etape') ?></th>
                            <th><?= lang('messages_lang.th_date_elaboration') ?></th>
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
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
 <div class="modal-dialog  modal-lg">
   <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.titre_projet_propose') ?>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table id="document" class='table table-bordered mb-0' id="table1">
            <thead>
              <tr>

                <th>#</th>
                <th><?= lang('messages_lang.labelle_numero_projet') ?></th>
                <th><?= lang('messages_lang.labelle_nom_du_projet') ?></th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('messages_lang.quiter_action') ?></button>
      </div>
    </div>
  </div>
</div>
<script>
  /**
   * fonction pour afficher modal qui afficher la liste des documents
   */
   function get_pip_proposer(id) {
    $("#exampleModal").modal("show");
    var row_count = "1000000";
    $("#document").DataTable({
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,4],
      "oreder":[[ 0, 'desc' ]],
      "ajax":{
        url: "<?= base_url() ?>/pip/Fiche_Pip_Proposer/projet_propose/" + id,
       type:"POST", 
      
     },
     lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],

     pageLength: 10,
     "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    dom: 'Bfrtlip',
    buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print'  ],
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

  })
        // }
      }
    </script>
    <script>
     $(document).ready(function () {
      liste()
    });
  </script>
  <script>


    function liste() 
    { 
      var row_count = "1000000";
      $("#mytable").DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
          url: "<?= base_url('pip/Fiche_Pip_Proposer/liste_projet_proposer')?>",
          type: "POST",
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
  
  <div class="modal fade" id="doc_pip">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <center><?= lang('messages_lang.titre_pip_propose') ?></center>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- <center> -->
            <div id="documentPIP">
              
            </div>
            <!-- </center> -->
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary btn-md" data-dismiss="modal">
              <?= lang('messages_lang.quiter_action') ?>
            </button>
          </div>
        </div>
      </div>
    </div>

  <script type="text/javascript">
  function get_doc(id)
  {

    $.ajax(
    {
      url:"<?=base_url()?>/pip/Fiche_Pip_Proposer/get_path_pip/"+id,
      type:"POST",
      dataType:"JSON",
      success: function(data)
      {
        var embed = data.documentPIP;
        document.getElementById("documentPIP").innerHTML=embed
      }
    });

    $('#doc_pip').modal('show')

  }
</script>