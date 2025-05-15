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
                  <div class="col-md-9" style="float: left;">
                    <h1 class="header-title text-black">
                      <?=lang('messages_lang.list_affect_prof')?>
                    </h1>
                  </div>
                  <div class="col-md-3" style="float: right;">
                    <a href="Etape_Double_Commande_Profil/add" style="float: right;" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> <?=lang('messages_lang.labelle_et_nouveau')?> </a>
                  </div>
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
                          <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.label_etape')?></th>
                          <th class="text-uppercase" style="white-space: nowrap;" ><?= lang('messages_lang.labelle_et_prof')?></th>
                          <th>OPTIONS</th>
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
    var row_count ="1000000";
    $('#message').delay('slow').fadeOut(3000);
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":
      {
        url:"Etape_Double_Commande_Profil/listing",
        type:"POST", 
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":true
      }],
      dom: 'Bfrtlip',
      order:[],
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
          "sSortAscending":  ": <?=lang('messages_lang.labelle_et_trier_colone')?>",
          "sSortDescending": ": <?=lang('messages_lang.labelle_et_trier_activer_trier')?>"
        }
      }

    });
  });
</script>

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
<div class="modal fade" id="mydelete" tabindex="-1" aria-hidden="true">
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


<!-------------------------MODAL POUR LES PROFILS ------------------------------>

        <div class="modal" id="profil" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                      <h3 class="modal-title text-center" id="exampleModalLabel">
                        <?=lang('messages_lang.titre_liste_detail')?>
                    </h3>
                    <button type="button"  class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="affect_etape_id" id="affect_etape_id">
                <div class="table-responsive">
                    <table id='mytable3' class="table table-bordered table-striped table-hover table-condensed " style="width: 100%;">
                      <thead>
                       <tr>
                          <th>#</th>
                          <th class="text-uppercase" style="white-space: nowrap;"><?=lang('messages_lang.labelle_et_prof')?>&nbsp;&nbsp;</th>  
                       </tr>
                   </thead>
                   <tbody id="table3">

                   </tbody>
               </table>

           </div>
           <div class="modal-footer">

            <button class="btn mb-1 btn-secondary" class="close" data-dismiss="modal"><?=lang('messages_lang.quiter_action')?></button>
        </div>

    </div>
</div>
</div>
</div>



<!-------------------------SCRIPT POUR LES PROFILS------------------------------>

<script type="text/javascript">
   function get_profil(id)
   {

       $('#affect_etape_id').val(id);
       var affect_etape_id = $('#affect_etape_id').val();
       var row_count ="1000000";
       table=$("#mytable3").DataTable({
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "oreder":[],
          "ajax":{
            url:"<?= base_url('/double_commande_new/Etape_Double_Commande_Profil/detail_profil')?>",
            type:"POST",
            data: {
              affect_etape_id:affect_etape_id
            
            }
        },
        lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
            "targets":[],
            "orderable":false
        }],

        language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?>_MAX_<?= lang('messages_lang.labelle_et_elementtotal')?>)",
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

  $("#profil").modal("show");

} 
</script>
