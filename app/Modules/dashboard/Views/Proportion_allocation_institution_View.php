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
                  <?=$inst_connexion?>
                  <div class="col-md-12">
                    <h3 class="text-black"><?=strtoupper(lang('messages_lang.tbd_proportion_allocation'))?></label></h3>
                  </div><br><br>
                  <div class="form-check form-check-inline col-md-2">
                    <input class="form-check-input" type="radio" name="TRIMESTRE" id="T1" selected onchange="liste();get_rapport()" value="1">
                    <label class="form-check-label" for="T1"><?=lang('messages_lang.trimestre1')?></label>
                  </div>
                  <div class="form-check form-check-inline col-md-2">
                    <input class="form-check-input" type="radio" name="TRIMESTRE" id="T2" onchange="liste();get_rapport()" value="2">
                    <label class="form-check-label" for="T2"><?=lang('messages_lang.trimestre2')?></label>
                  </div>
                  <div class="form-check form-check-inline col-md-2">
                    <input class="form-check-input" type="radio" name="TRIMESTRE" id="T3" onchange="liste();get_rapport()" value="3">
                    <label class="form-check-label" for="T3"><?=lang('messages_lang.trimestre3')?></label>
                  </div>
                  <div class="form-check form-check-inline col-md-2">
                    <input class="form-check-input" type="radio" name="TRIMESTRE" id="T4" onchange="liste();get_rapport()" value="4">
                    <label class="form-check-label" for="T4"><?=lang('messages_lang.trimestre4')?></label>
                  </div>
                 <div class="form-check form-check-inline col-md-2">
                    <input class="form-check-input" type="radio" name="TRIMESTRE" id="TS" onchange="liste();get_rapport()" value="5" checked>
                    <label class="form-check-label" for="TS"><?=lang('messages_lang.label_annuel')?></label>
                  </div>


                </div>
                <br>
                <div class="form-group col-md-4"> 
                      <label><b><?=lang('messages_lang.select_anne_budget')?></b></label>
                      <select class="form-control" onchange="get_rapport();liste()" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID">
                        <?php foreach($anne_budget as $key){ ?>
                          <?php if($key->ANNEE_BUDGETAIRE_ID == $ann_actuel_id){ ?>
                            <option value="<?=$key->ANNEE_BUDGETAIRE_ID?>" selected><?=$key->ANNEE_DESCRIPTION?></option>
                          <?php }else{ ?>
                            <option value="<?=$key->ANNEE_BUDGETAIRE_ID?>"><?=$key->ANNEE_DESCRIPTION?></option>
                        <?php } } ?>
                      </select>        
                    </div>
              </div>
              <div class="row">
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                <div id="container"  class="col-md-12"></div>  
                <div id="nouveau"></div>
              </div>

              <div class="row"> 
               <div class="col-md-12" style="margin-bottom: 10px"></div>
               <!-- Début liste -->
               <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                  <div class="car-body">
                    <div class="row col-md-12">
                      <div class="col-md-12">
                        <h5 class="header-title text-black"><?=lang('messages_lang.tbd_proportion_allocation')?> </h5>

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
                              
                              <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.th_programme')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>

                              <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.th_action')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>

                               <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NUMENCLATURE&nbsp;&nbsp;&nbsp;&nbsp;BUDGETAIRE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>

                              <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.th_activite')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>

                            <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TACHE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>

                              <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.th_budget_vote')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
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

<!-- footer end -->
<!-- script start -->
<?php echo view('includesbackend/scripts_js.php');?>


</body>
</html>


<div class="modal fade" id="myModal" role="dialog">
  <div class="modal-dialog" style ="max-width: 70%;">
    <div class="modal-content  ">
      <div class="modal-header">
        <h4 class="modal-title"><span id="titre" style="color: black"></span></h4>
      </div>
      <div class="modal-body">
        <table style="width: 100%;" id='mytable' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
          <thead>
           <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center></th>
           <th style='width:90px'><center><font color="white" size="3"><label> &nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_prog')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
           <th style='width:100px'><center><font color="white" size="3"><label> <?=lang('messages_lang.label_activite')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

           <th style='width:60px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbspMONTANT &nbsp&nbspVOTE&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
           

         </thead>
       </table>  
     </div>
     <div class="modal-footer">
      <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
    </div>
  </div>
</div>
</div>  

<script type="text/javascript">
 $(document).ready(function() {
    // alert();
    liste();
    get_rapport();

    
  });   

</script>


<script type="text/javascript">
  function get_rapport(){

   if (document.getElementById('T1').checked)
   {
     var TRIMESTRE = document.getElementById('T1').value;
   }
   else if (document.getElementById('T2').checked)
   {
     var TRIMESTRE = document.getElementById('T2').value;
   }
   else if (document.getElementById('T3').checked)
   {
     var TRIMESTRE = document.getElementById('T3').value;
   }
   else if (document.getElementById('T4').checked)
   {
     var TRIMESTRE = document.getElementById('T4').value;
   }

   var inst_conn=$('#inst_conn').val();
   var INSTITUTION_ID=$('#INSTITUTION_ID').val();
   var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();

   $.ajax({
    url : "<?=base_url('dashboard/Proportion_allocation_institution/get_rapport')?>",
    type : "POST",
    dataType: "JSON",
    cache:false,
    data:{TRIMESTRE:TRIMESTRE,
     inst_conn:inst_conn,
     INSTITUTION_ID:INSTITUTION_ID,
     ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID,
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

 
    if (document.getElementById('T1').checked)
    {
      var TRIMESTRE = document.getElementById('T1').value;
    }
    else if (document.getElementById('T2').checked)
    {
      var TRIMESTRE = document.getElementById('T2').value;
    }
    else if (document.getElementById('T3').checked)
    {
      var TRIMESTRE = document.getElementById('T3').value;
    }
    else if (document.getElementById('T4').checked)
    {
      var TRIMESTRE = document.getElementById('T4').value;
    }

    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    
    
    var row_count ="1000000";
    $("#list_table").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":{
        url:"<?= base_url('dashboard/Proportion_allocation_institution/listing')?>",
        type:"POST",
        data:{
          TRIMESTRE:TRIMESTRE,
          ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID,
        },
      },

        lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, "All"]],
        pageLength:5,
        "columnDefs":[{
          "orderable":false
        }],

        dom: 'Bfrtlip',
        buttons: [
        'pdf'
        ],
        language: {
          "sProcessing": "Traitement en cours...",
          "sSearch": "Rechercher&nbsp;:",
          "sLengthMenu": "Afficher _MENU_ &eacute;l&eacute;ments",
          "sInfo": "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
          "sInfoEmpty": "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
          "sInfoFiltered": "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
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
  // });

}
</script>

<script type="text/javascript">
  function exporter()
    {
    if (document.getElementById('T1').checked)
    {
      var TRIMESTRE = document.getElementById('T1').value;
    }
    else if (document.getElementById('T2').checked)
    {
      var TRIMESTRE = document.getElementById('T2').value;
    }
    else if (document.getElementById('T3').checked)
    {
      var TRIMESTRE = document.getElementById('T3').value;
    }
    else if (document.getElementById('T4').checked)
    {
      var TRIMESTRE = document.getElementById('T4').value;
    }

    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
      
    if (TRIMESTRE == '' || TRIMESTRE == null) {TRIMESTRE = 5}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}

      document.getElementById("btnexport").href = "<?=base_url('dashboard/Proportion_allocation_institution/exporter/')?>"+'/'+TRIMESTRE+'/'+ANNEE_BUDGETAIRE_ID;
  }
</script>