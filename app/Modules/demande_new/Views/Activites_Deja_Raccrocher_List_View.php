
<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
 <?php $validation = \Config\Services::validation(); ?>
 <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
 <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
 <style type="text/css">
   .modal-signature {
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    border-bottom-right-radius: .3rem;
    border-bottom-left-radius: .3rem
  }
</style>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid"> 
         <br>
         <div class="row" style="margin-top: -5px">
          <div class="col-12">
            <div style="margin-top: -25px;" class="card">
              <div class="card-header">
               <b style="font-size:20px" class="header-title text-black"><?= lang('messages_lang.act_dej_racc')?></b><br><br>            
             </div>
             <div class="container-xxl py-2 subpage_bg">
              <div class="col-md-12 right-side table table-responsive">
                <table id="mytable" class="table table-bordered">
                  <thead>
                    <tr> 
                      <th><?=lang('messages_lang.code_prog')?></th>
                      <th><?=lang('messages_lang.col_activite')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                      <th><?=lang('messages_lang.col_imputation')?></th> 
                      <th><?=lang('messages_lang.etat_avancement')?></th>
                      <th><?=lang('messages_lang.mont_real_bugd')?></th>
                      <th><?=lang('messages_lang.mont_real_jur')?></th>
                      <th><?=lang('messages_lang.mont_real_liq')?></th>
                      <th><?=lang('messages_lang.mont_real_ord')?></th>
                      <th><?=lang('messages_lang.mont_real_paie')?></th>
                      <th><?=lang('messages_lang.mont_real_dec')?></th>
                      <th><?=lang('messages_lang.option_action')?></th>
                      
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
</div>
</div>
</main>
</div>
</div>

<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-body">
        <div style="font-size:13px" id="act1">   
        </div>
      </div>
      <div class="modal-footer">
        <button class='btn btn-primary btn-md' data-dismiss='modal'>
         Quitter
       </button>
     </div>
   </div>
 </div>
</div>

<script type="text/javascript">
  function show_activities(id){
    $.ajax(
    {
      url:"<?=base_url()?>/demande_new/Activites_Deja_Raccroche/activities/"+id,
      type:"POST",
      dataType:"JSON",
      success: function(data)
      {
        $('#act1').html(data.activity);
        $('#EXECUTION_BUDGETAIRE_RACCROCHAGE_ID').html(data.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID)
      }
    });

    $('#exampleModal').modal('show')

  }
</script>
<script type="text/javascript">
  $(document).ready(function () {
    list_activities();
  });
</script>

<script>

  function list_activities()
  {
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url:"<?= base_url()?>/demande_new/Activites_Deja_Raccroche/list_activities",
        type: "POST",
        data: {},
        beforeSend: function() {}
      },
      lengthMenu: [
      [5,10, 50, 100, -1],
      [5,10, 50, 100, "All"]
      ],
      pageLength: 10,
      "columnDefs": [{
        "targets": [],
        "orderable": false
      }],
      dom: 'Bfrtlip',
      order:[1,'desc'],
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
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
        },        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
        }
      }
    });
  };
</script>