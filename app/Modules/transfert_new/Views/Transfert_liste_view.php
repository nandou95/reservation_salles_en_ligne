<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $session  = \Config\Services::session();
  $userfiancier = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER');
  echo view('includesbackend/header.php');
  ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
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
                  $historique="btn active"; 
                  $incrementation="btn"; 
                  $imputation="btn"; 
                  $deux_activite="btn";
                  ?>
                  <?php include  'includes/Include_menu_transfert.php'; ?> 
                </div>

               </div>

               <div class="card-body" style="margin-top: -20px">


               </div>
             </div>
             <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
              <div class="car-body">

                <?php
                if(session()->getFlashKeys('alert'))
                {
                  ?>
                  <div class="alert alert-success" id="message">
                    <?php echo session()->getFlashdata('alert')['message']; ?>
                  </div>
                  <?php
                }
                ?>

                <div class="header-title text-black"><h6 style="margin-bottom:12px" class="ml-2"> <?=lang('messages_lang.liste_des_transferts')?>   </h6></div>
                <div class="table-responsive container ">
                  <div class="table-responsive" style="width: 100%;">
                    <table id="mytable" class=" table table-striped table-bordered">
                      <thead>
                        <tr>
                          <!-- <th><center>#</center></th> -->
                          <th><center><?=lang('messages_lang.act_orig')?></center></th>
                          <th><center><?=lang('messages_lang.mont_transf')?></center></th>
                          <th><center><?=lang('messages_lang.table_act_dest')?></center></th>
                          <th><center><?=lang('messages_lang.mont_rec')?></center></th>
                          <th><center><?=lang('messages_lang.inst_origi')?></center></th>
                          <th><center><?=lang('messages_lang.inst_dest')?></center></th>
                          <th><center><?=lang('messages_lang.trim')?></center></th>
                          <th><center><?=lang('messages_lang.label_opera')?></center></th>
                          <th><center><?=lang('messages_lang.labelle_observartion')?></center></th>
                          <th><center><?=lang('messages_lang.preuv')?></center></th>
                          <th><center><?=lang('messages_lang.labelle_option')?></center></th>
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

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?=lang('messages_lang.preuv_just')?></h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
       
        <div class="table-responsive">
          <table id="preuves" class='table table-bordered mb-0' id="table1">
            <thead>
              <tr>
                <th>document</th>
                
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=lang('messages_lang.quiter_action')?></button>
      </div>
    </div>
  </div>
</div>

<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  $(document).ready(function ()
  {
    var row_count ="1000000";
    $('#message').delay('slow').fadeOut(3000);
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,2],
      "oreder":[[ 0, 'desc' ]],
      "ajax":{
        url:"<?= base_url('transfert_new/Transfert_list/liste')?>",
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
        'copy', 'csv', 'excel', 'pdf', 'print'
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
  });
</script>

<script>
  /**
   * fonction pour afficher modal qui afficher la liste des documents
   */
  function modal_odd(id) {
    

   
    $("#exampleModal").modal("show");
        // function get_odd() {
    
    var row_count = "1000000";
    $("#preuves").DataTable({
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,4],
      "oreder":[[ 0, 'desc' ]],
      "ajax":{
        
        
       url: "<?= base_url() ?>/transfert_new/Transfert_list/liste_preuve/"+id,

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
        "sSearch": "<?= lang('messages_lang.search_button') ?>&nbsp;:",
        "sLengthMenu": "<?= lang('messages_lang.sLengthMenu_enjeux') ?>",
        "sInfo": "<?= lang('messages_lang.sInfo_enjeux') ?>",
        "sInfoEmpty": "<?= lang('messages_lang.sInfo_enjeux_0') ?>",
        "sInfoFiltered": "(<?= lang('messages_lang.filtre_max_total_enjeux') ?>)",
        "sInfoPostFix": "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords": "<?= lang('messages_lang.aucun_element_afficher_enjeux') ?>",
        "sEmptyTable": "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst": "<?= lang('messages_lang.labelle_1') ?>",
          "sPrevious": "<?= lang('messages_lang.btn_precedent') ?>",
          "sNext": "<?= lang('messages_lang.btn_suivant') ?>",
          "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending": "<?= lang('messages_lang.sSortAscending_enjeux') ?>",
          "sSortDescending": "<?= lang('messages_lang.sSortDescending_enjeux') ?>"
        }
      }

  })
       
  }
</script>

