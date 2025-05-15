<!DOCTYPE html>
<html lang="en">
<head>
<?php echo view('includesbackend/header.php');?>
<?php $validation = \Config\Services::validation(); ?>
<link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
<script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
<style type="text/css">
.modal-signature 
  {
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-end;
  border-bottom-right-radius: .3rem;
  border-bottom-left-radius: .3rem
   }
</style>
</head>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script type="text/javascript" src="monfichier.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"></script> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<body>
<div class="wrapper">
<?php echo view('includesbackend/navybar_menu.php');?>

<div class="main">
  <?php echo view('includesbackend/navybar_topbar.php');?>
  <main class="content">
    <div class="container-fluid">
     
     <div class="row" style="margin-top: -5px">
      <div class="col-12">
        <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
          <div class="card-header">

          </div>
          <div class="card-body" style="overflow-x:auto;">


           <div class="row">
               <div class="form-group col-md-6">
                  <label><b><?=lang('messages_lang.pip_rapport_institutio_filtre')?></b></label>
                  <input type="hidden" name="NIVEAU_VISION" id="NIVEAU_VISION" value="">
                  <input type="hidden" name="INSTITUTION_ID1" id="INSTITUTION_ID1" value="">
                  <select class="form-control" onchange="get_rapport();liste()" name="INSTITUTION_ID" id="INSTITUTION_ID">

                    <option value=""><?=lang('messages_lang.label_selecte')?></option>

                    <?php
                    foreach ($institutions as $value)
                    {
                      if ($value->INSTITUTION_ID==$INSTITUTION_ID)
                      {
                        ?>
                        <option value="<?= $value->INSTITUTION_ID ?>" selected><?=$value->DESCRIPTION_INSTITUTION ?></option>
                        <?php
                      } 
                      else if($value->INSTITUTION_ID== $INSTITUTION_ID1){
                        ?>
                        <option value="<?= $value->INSTITUTION_ID?>" selected><?=$value->DESCRIPTION_INSTITUTION ?></option>
                        <?php

                      }else
                      { 
                        ?>
                        <option value="<?=$value->INSTITUTION_ID?>"><?= $value->DESCRIPTION_INSTITUTION ?></option>
                        <?php 
                      } 
                    } 
                    ?>
                  </select>        
                </div>
                <div class="form-group col-md-6">
                  <label><b><?=lang('messages_lang.pip_rapport_institutio_filtre_axe')?></b></label>
                  <select class="form-control" onchange="get_rapport();liste()" name="ID_AXE_INTERVENTION_PND" id="ID_AXE_INTERVENTION_PND">
                    <option value=""><?=lang('messages_lang.label_selecte')?></option>
                    <?php
                    foreach ($axe_intervations as $value)
                    {
                      if ($value->ID_AXE_INTERVENTION_PND== set_value('ID_AXE_INTERVENTION_PND')) {?>
                       <option value="<?=$value->ID_AXE_INTERVENTION_PND?>" selected><?=$value->DESCR_AXE_INTERVATION_PND?></option>
                       <?php

                         }else{?>

                       <option value="<?=$value->ID_AXE_INTERVENTION_PND?>"><?=$value->DESCR_AXE_INTERVATION_PND?></option>

                       <?php 
                     }

                   }
                   ?>
                 </select>        
               </div>
          </div>


        </div>


      </div>

      <div class="row">
        <div class="col-md-12" style="margin-bottom: 20px"></div>
        <div id="container"  class="col-md-12" ></div> 
      </div>
      <div class="row"> 
                 <div class="col-md-12" style="margin-bottom: 10px"></div>
                 <!-- Début liste -->
                 <div class="col-12">
                  <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                    <div class="car-body">
                      <div class="row col-md-12">
                        <div class="col-md-12">
                          <h3 class="header-title text-black"><?=lang('messages_lang.pip_rapport_source_fina')?> </h3>

                          <div style ="max-width: 15%;">
                        <a href="#" id="btnexport" onclick="exporter()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-center"></span><?=lang('messages_lang.pip_rapport_institutio_telecharge')?></a>
                          
                          </div> 
                        </div>
    
                      </div>
                      <div class="table-responsive container " style="margin-top:-0px">
                        <div class="table-responsive" style="width: 100%;">
                          <table id="list_table" class=" table table-striped table-bordered">
                            <thead>
                              <tr>
                                <th><center>#</center></th>
                                  <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_filtre')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                              
                                <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_projet')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>

                                  <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_dashboar_statut')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>

                                 
                                 <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_bailleur')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                 <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_monta')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>

                                <th><?=lang('messages_lang.pip_rapport_institutio_annee1')?>&nbsp;2024&nbsp;&nbsp;-&nbsp;2025&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                 <th><?=lang('messages_lang.pip_rapport_institutio_annee1')?>&nbsp;2025&nbsp;&nbsp;-&nbsp;2026&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                               <th><?=lang('messages_lang.pip_rapport_institutio_annee1')?>&nbsp;2026&nbsp;&nbsp;-&nbsp;2027&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
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
         <p>
    </div>
  </div>
</div>
</div>
</div>
</main>
</div>
</div>
<div id="nouveau"></div>
<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
$( document ).ready(function() {
  liste();
  get_rapport();
});   
</script>

<script type="text/javascript">
function get_rapport()
  {
  	
var INSTITUTION_ID=$('#INSTITUTION_ID').val();
var ID_AXE_INTERVENTION_PND=$('#ID_AXE_INTERVENTION_PND').val();

$.ajax({
  url : "<?=base_url('dashboard/Dashboard_Pip_Source_Financement/Get_financement')?>",
  type : "GET",
  dataType: "JSON",
  cache:false,
  data:{INSTITUTION_ID:INSTITUTION_ID,
       ID_AXE_INTERVENTION_PND:ID_AXE_INTERVENTION_PND
       },

 success:function(data){
  $('#container').html("");             
  $('#nouveau').html(data.rapp); 
},            
});  
}
function saveData()
 {
  $('#myModal').modal('hide');
 } 
</script>
<script type="text/javascript">

  function liste(){

    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var ID_AXE_INTERVENTION_PND=$('#ID_AXE_INTERVENTION_PND').val();
    var row_count ="1000000";
    $("#list_table").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,2],
      "oreder":[[ 0, 'desc' ]],
      "ajax":{
        url:"<?= base_url('dashboard/Dashboard_Pip_Source_Financement/listing')?>",
        type:"POST",
        data:{
        INSTITUTION_ID:INSTITUTION_ID,
        ID_AXE_INTERVENTION_PND:ID_AXE_INTERVENTION_PND
      },
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength:5,
    "columnDefs":[{
      "targets":[0,3],
      "orderable":false
    }],

    dom: 'Bfrtlip',
    order: [2,'desc'],
  buttons: [
     'pdf'
      ],
    language: {
      "sProcessing":     "Traitement en cours...",
      "sSearch":         "Rechercher&nbsp;:",
      "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
      "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
      "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
      "sInfoFiltered":   "(filtr&eacute; de MAX &eacute;l&eacute;ments au total)",
      "sInfoPostFix":    "",
      "sLoadingRecords": "Chargement en cours...",
      "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
      "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
      "oPaginate": {
        "sFirst":      "Premier",
        "sPrevious":   "Pr&eacute;c&eacute;dent",
        "sNext":       "Suivant",
        "sLast":       "Dernier"
      },
      "oAria": {
        "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
        "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
      }
    }
  });
  // });

  }
</script>

<script type="text/javascript">
function exporter()
    {
  var INSTITUTION_ID = $('#INSTITUTION_ID').val();
  var ID_AXE_INTERVENTION_PND = $('#ID_AXE_INTERVENTION_PND').val();
    
    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}

     if (ID_AXE_INTERVENTION_PND == '' || ID_AXE_INTERVENTION_PND == null) {ID_AXE_INTERVENTION_PND = 0}

    document.getElementById("btnexport").href = "<?=base_url('dashboard/Dashboard_Pip_Source_Financement/exporter/')?>"+'/'+INSTITUTION_ID+'/'+ID_AXE_INTERVENTION_PND;
}
</script>
