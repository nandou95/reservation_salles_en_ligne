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
                    $ptba_original="btn";
                    $ptba_revise="btn "; 
                    $taches_trouve="btn active"; 
                    $taches_non_trouve="btn";
                   
                    ?>
                    <?php include  'includes/Menu_Croisement.php'; ?> 
                  </div>
                </div>
                <div class="card-body" style="margin-top: -20px">
                </div>                       
              </div>



              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
               
                
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
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Programme</th>
                            <th>Action </th>
                            <th>Ligne&nbsp;budgétaire</th>
                            <th>Activité&nbsp;avant&nbsp;révison</th>
                            <th>Activité&nbsp;après&nbsp;révison</th>
                            <th>Tache&nbsp;avant&nbsp;révison</th>
                            <th>Tache&nbsp;après&nbsp;révison</th>
                            <th>QT1&nbsp;avant&nbsp;révison</th>
                            <th>QT1&nbsp;après&nbsp;révison</th>
                            <th>QT2&nbsp;avant&nbsp;révison</th>
                            <th>QT2&nbsp;après&nbsp;révison</th>
                            <th>QT3&nbsp;avant&nbsp;révison</th>
                            <th>QT3&nbsp;après&nbsp;révison</th>
                            <th>QT4&nbsp;avant&nbsp;révison</th>
                            <th>QT4&nbsp;après&nbsp;révison</th>
                            <th>T1&nbsp;avant&nbsp;révison</th>
                            <th>T1&nbsp;après&nbsp;révison</th>
                            <th>T2&nbsp;avant&nbsp;révison</th>
                            <th>T2&nbsp;après&nbsp;révison</th>
                            <th>T3&nbsp;avant&nbsp;révison</th>
                            <th>T3&nbsp;après&nbsp;révison</th>
                            <th>T4&nbsp;avant&nbsp;révison</th>
                            <th>T4&nbsp;après&nbsp;révison</th>
                            
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
<script>
  $(document).ready(function()
  {
    liste();
  });
</script>

<script type="text/javascript">
  function liste() {
  
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Liste_tache_trouve/listing') ?>",
        type: "POST",
        data: {
      
        }
      },

      lengthMenu: [
        [10, 50, 100, row_count],
        [10, 50, 100, "All"]
      ],
      pageLength: 10,
      "columnDefs": [{
        "targets": [],
        "orderable": false
      }],

      dom: 'Bfrtlip',
      order: [],
      buttons: [],
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
    });
  }
</script>






