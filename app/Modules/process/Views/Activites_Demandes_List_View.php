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
          <div style="box-shadow: rgba(100,100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-body">
                  <div class="row container">
                   <div class="col-md-6">
                    <div class="form-group">
                      <label for="" class="form-label"><?=lang('messages_lang.label_inst')?> <font color="red">*</font></label>
                      <select name="INSTITUTION_ID" onchange="create_get_action();listing()" id="INSTITUTION_ID" class="form-control">
                        <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>

                        <?php foreach($instit as $value):
                          if($value->INSTITUTION_ID==set_value('INSTITUTION_ID')){?>
                            <option value="<?= $value->INSTITUTION_ID?>" selected><?= $value->DESCRIPTION_INSTITUTION ?></option>
                          <?php }else{?>
                            <option value="<?=$value->INSTITUTION_ID?>"><?= $value->DESCRIPTION_INSTITUTION ?></option>
                          <?php }endforeach ?>

                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="" class="form-label"> <?=lang('messages_lang.label_sousTitre')?> <font color="red">*</font></label>
                        <select name="SOUS_TUTEL_ID" onchange="create_get_data();listing()" id="TUTEL_ID" class="form-control">
                        <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>


                        </select>
                          </div>
                        
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="card-header">
                  <div class="col-md-12 d-flex">
                    <div class="col-md-9" style="float: left;">
                      <h1 class="header-title text-black">
                        <?=lang('messages_lang.titre_dem_activ')?> 
                      </h1>
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

                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                          <th>#</th>                              
                            <th><?=lang('messages_lang.th_instit')?></th>
                            <th><?=lang('messages_lang.th_programme')?></th>
                            <th><?=lang('messages_lang.th_action')?></th>
                            <th><?=lang('messages_lang.th_code_budgetaire')?></th>
                            <th><?=lang('messages_lang.code_prog')?></th>
                            <th><?=lang('messages_lang.th_activite')?></th>
                            <th><?=lang('messages_lang.result_attend')?></th>
                            <th><?=lang('messages_lang.th_articl_eco')?></th>
                            <th><?=lang('messages_lang.th_natur_eco')?></th>
                            <th><?=lang('messages_lang.th_intit_art_eco')?></th>
                            <th><?=lang('messages_lang.th_intit_nat_eco')?></th>
                            <th><?=lang('messages_lang.th_code_division')?></th>
                            <th><?=lang('messages_lang.th_intit_division')?></th>
                            <th><?=lang('messages_lang.th_code_group')?></th>
                            <th><?=lang('messages_lang.th_intit_group')?></th>
                            <th><?=lang('messages_lang.th_code_class')?></th>
                            <th><?=lang('messages_lang.th_intit_class')?></th>
                            <th><?=lang('messages_lang.th_cout_unit')?></th>
                            <th><?=lang('messages_lang.table_unite')?></th>
                            <th><?=lang('messages_lang.th_quantite')?>&nbspT1</th>
                            <th><?=lang('messages_lang.th_quantite')?>&nbspT2</th>
                            <th><?=lang('messages_lang.th_quantite')?>&nbspT3</th>
                            <th><?=lang('messages_lang.th_quantite')?>&nbspT4</th>
                            <th><?=lang('messages_lang.th_montant')?>&nbspT1</th>
                            <th><?=lang('messages_lang.th_montant')?>&nbspT2</th>
                            <th><?=lang('messages_lang.th_montant')?>&nbspT3</th>
                            <th><?=lang('messages_lang.th_montant')?>&nbspT4</th>
                            <th><?=lang('messages_lang.th_intit_gm')?></th>
                            <th><?=lang('messages_lang.th_budg_prog')?></th>
                            <th><?=lang('messages_lang.th_respo')?></th>
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

function create_get_data(){

var SOUS_TUTEL_ID=$('#TUTEL_ID').val();
$.ajax({
  
  url : "<?=base_url('process/Dem_Liste_Activites/create_get_tutel')?>",
  type : "POST",
  dataType: "JSON",
  cache:false,
  data:{
    PROGRAMME_ID:SOUS_TUTEL_ID
  },

  success:function(data){   
    $('#TUTEL_ID').html(data.tutel);
    console.log(data);
  },            
});  
}

function create_get_action(){

var PROGRAMME_ID=$('#INSTITUTION_ID').val();
$.ajax({
  
  url : "<?=base_url('process/Dem_Liste_Activites/create_get_tutel')?>",
  type : "POST",
  dataType: "JSON",
  cache:false,
  data:{
    PROGRAMME_ID:PROGRAMME_ID
  },

  success:function(data){   
    $('#TUTEL_ID').html(data.tutel);
  },            

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
      url:"<?= base_url('process/Dem_Liste_Activites/activites')?>",
      type:"POST", 
    },
    lengthMenu: [[5,50,100, row_count], [5,50, 100, "All"]],
    pageLength: 5,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],
    dom: 'Bfrtlip',
    order:[1,'asc'],
    buttons: [
    'excel', 'pdf'
    ],
   language: {
      "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
      "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
      "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
      "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
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

});
</script>