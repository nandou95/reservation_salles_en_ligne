<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?> 

 <script src="https://code.highcharts.com/highcharts.js"></script>
 <script src="https://code.highcharts.com/highcharts-3d.js"></script>
 <script src="https://code.highcharts.com/modules/exporting.js"></script>
 <script src="https://code.highcharts.com/modules/export-data.js"></script>
 <script src="https://code.highcharts.com/modules/accessibility.js"></script>
 <script type="text/javascript" src="monfichier.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"></script> 
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="dashboard dashboard_1">
 <div class="wrapper">
  <!-- Sidebar  -->
  <?php echo view('includesbackend/navybar_menu.php');?>
  <!-- end sidebar -->

  <!-- right content -->
  <div class="main">
    <!-- topbar -->
    <?php echo view('includesbackend/navybar_topbar.php');?>
    <!-- end topbar -->

    <!-- body page start -->
    <main class="content">
      <div class="container-fluid">

        <div class="col-md-12 right-side">
          <div class="row">
            <div class="col-md-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card" >
                <div class="row">
                  <div class="col-md-12">
                    <h1 class=" header-title text-black"><?=lang('messages_lang.pip_rapport_axes_intervention_titre')?></h1>
                  </div>
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
               <div></div>  
               <!-- Rapport graphique -->
                  <div class="row">
                 <div class="col-md-12" style="margin-bottom: 20px"></div>
                 <!-- Début liste -->
                 <div class="col-12">
                  <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                    <div class="car-body">
                      <div id="container1"  class="col-md-12"></div> 
                  </div>
                  </div>
                </div>
               </div>
               <div class="row"> 
                 <div class="col-md-12" style="margin-bottom: 20px"></div>
                 <!-- Début liste -->
                 <div class="col-12">
                  <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                    <div class="car-body">
                      <div class="row col-md-12">
                        <div class="col-md-12">
                          <h1 class="header-title text-black"><?=lang('messages_lang.pip_rapport_axes_intervention')?> </h1>
                        </div>

                      </div>
                      <div class="table-responsive container " style="margin-top: 20px">
                        <div class="table-responsive" style="width: 100%;">
                          <table id="list_table" class=" table table-striped table-bordered">
                            <thead>
                              <tr>
                                <th><center>#</center></th>
                                <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_ax')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_projet')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_filtre')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                 <th><center><?=lang('messages_lang.pip_rapport_pilier_annee_titre_ann')?>&nbsp;<?=lang('messages_lang.pip_rapport_institutio_program_budgetaire')?>&nbsp;&nbsp;</center></th>
                                <th><center><?=lang('messages_lang.pip_rapport_institutio_monta')?>&nbsp;&nbsp;&nbsp;</center></th>
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
      </div>
      <!-- body page end -->
    </div>

  </div>


  <!-- Modal pour afficher les lieux d'intervention -->

  <div class="modal fade" id="modal_des_pads" style="z-index: 1500 !important;">
    <div class="modal-dialog modal-lg"> 
      <div class="modal-content">
        <div class="modal-header">

         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

       </div>
       <div class="modal-body" style="width: 100%">
         <div class="table-responsive">
          <table id="table_pad" class='table mb-0' id="table1">
            <thead>
              <tr>
                <th>#</th>
                <th><font color="white">PROVINCE</font></th>
                <th><font 
                  color="white">COMMUNE</font></th>
                  
                </tr>
              </thead>
              <tbody> </tbody>

            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- footer end -->

  <!-- script start -->
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>


<!-- details du rapport graphique -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-xl" style ="width:100%">
      <div class="modal-content  modal-xl">
        <div class="modal-header">
          <h4 class="modal-title"><span id="titre"></span></h4>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table id='mytable' class='table table-bordered table-striped table-hover table-condensed' style="width:100%">
              <thead>
               <th>&nbsp;#&nbsp;</th>
               <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_ax')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
               <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_projet')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                
            <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.pip_rapport_institutio_filtre')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                <th><center><?=lang('messages_lang.pip_rapport_pilier_annee_titre_ann')?>&nbsp;<?=lang('messages_lang.pip_rapport_institutio_program_budgetaire')?>&nbsp;&nbsp;</center></th>
                <th><center><?=lang('messages_lang.pip_rapport_institutio_monta')?>&nbsp;&nbsp;&nbsp;</center></th>
                
                
              </thead>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button btn-btn danger" class="btn btn-danger" data-dismiss="modal" onclick="saveData()"><?=lang('messages_lang.label_ferm')?></button>
        </div>
      </div>
    </div>
  </div>
<div id="nouveau"></div>


<script type="text/javascript">
 $(document).ready(function() { 
  get_rapport();
  liste();


});   

</script>


<script type="text/javascript">

  function get_rapport(){ 

   var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var ID_AXE_INTERVENTION_PND=$('#ID_AXE_INTERVENTION_PND').val();
  

   $.ajax({
    url : "<?=base_url('dashboard/Rapport_Projet_Intervention/get_rapport')?>",
    type : "POST",
    dataType: "JSON",
    cache:false,
    data:{INSTITUTION_ID:INSTITUTION_ID,
       ID_AXE_INTERVENTION_PND:ID_AXE_INTERVENTION_PND
 
  },
  success:function(data){
    $('#container1').html("");             
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
        url:"<?= base_url('dashboard/Rapport_Projet_Intervention/listing')?>",
        type:"POST",
        data:{INSTITUTION_ID:INSTITUTION_ID,
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
      'copy', 'csv', 'excel', 'pdf', 'print'
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
  function get_lieux(id) {

    var id =id;

   $("#modal_des_pads").modal('show');

   var row_count = "1000000";
   table = $("#table_pad").DataTable({
    "processing": true,
    "destroy": true,
    "serverSide": true,
    "oreder": [
      [2, 'desc']
      ],
    "ajax": {
      
       url:"<?= base_url('dashboard/Rapport_Projet_Intervention/get_lieux')?>",
      type: "POST",
      data: {id:id}
    },
    lengthMenu: [
      [10, 50, 100, row_count],
      [10, 50, 100, "All"]
      ],
    pageLength: 5,
    "columnDefs": [{
      "targets": [],
      "orderable": false
    }],

    dom: 'Bfrtlip',
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
      ],
    language: {
      "sProcessing": "Traitement en cours...",
      "sSearch": "Rechercher&nbsp;:",
      "sLengthMenu": "Afficher MENU &eacute;l&eacute;ments",
      "sInfo": "Affichage de l'&eacute;l&eacute;ment START &agrave; END sur TOTAL &eacute;l&eacute;ments",
      "sInfoEmpty": "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
      "sInfoFiltered": "(filtr&eacute; de MAX &eacute;l&eacute;ments au total)",
      "sInfoPostFix": "",
      "sLoadingRecords": "Chargement en cours...",
      "sZeroRecords": "Aucun &eacute;l&eacute;ment &agrave; afficher",
      "sEmptyTable": "Aucune donn&eacute;e disponible dans le tableau",
      "oPaginate": {
        "sFirst": "Premier",
        "sPrevious": "Pr&eacute;c&eacute;dent",
        "sNext": "Suivant",
        "sLast": "Dernier"
      },
      "oAria": {
        "sSortAscending": ": activer pour trier la colonne par ordre croissant",
        "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
      }
    }
  });
 }

</script>


