
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
      <!-- <div class="header">
       <h1 class="header-title text-black"> Répartition de l’investissement public selon les Objectifs Stratégiques
       </h1>
     </div> -->
     <div class="row" style="margin-top: -5px">
      <div class="col-12">
        <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
          <div class="card-header">

          </div>
           <h1 class="header-title text-black">Rapport des projets par leur lieux d'intervention
       </h1>
          <div class="card-body" style="overflow-x:auto;">
           <div class="row">
           

            <div class="form-group col-md-6">
              <label><b>Institutions</b></label>
              <input type="hidden" name="INSTITUTION_ID1" id="INSTITUTION_ID1" value="">
              <select class="form-control" onchange="get_rapport();listing()" name="INSTITUTION_ID" id="INSTITUTION_ID">
                <option value="">sélectionner</option>
                <?php
                foreach ($type_ministre as $value)
                {
                  if ($value->INSTITUTION_ID==$INSTITUTION_ID1)
                    { ?>
                      <option value="<?= $value->INSTITUTION_ID ?>" selected><?=$value->Name ?></option>
                      <?php
                    } 
                    else
                    { 
                      ?>
                      <option value="<?=$value->INSTITUTION_ID?>"><?= $value->Name ?></option>
                      <?php 
                    } 
                  } 
                  ?>
                </select>        
              </div>


              <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog" style ="max-width: 65%;">
                  <div class="modal-content  ">
                    <div class="modal-header">
                      <h4 class="modal-title"><span id="titre" style="color: black"></span></h4>
                    </div>
                    <div class="modal-body">
                      <table style="width: 100%;" id='mytable' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                        <thead>
                          <th style='width:30px'><center><label>#</label></center></th>
                           <th ><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INSTITUTION&nbsp;&nbsp;&nbsp;&nbsp;</label></center></th>
                          <th style='width:70px'><center><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OBJECTIF&nbsp;STRATEGIQUE&nbsp;&nbsp;</label></center></th>
                          <th style='width:70px'><center><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PROJET&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></center></th>
                        
                          <th ><center><label>&nbsp;&nbsp;&nbsp;&nbsp;ANNEE&nbsp;&nbsp;BUDGETAIRE&nbsp;&nbsp;&nbsp;</label></center></th>

                          <th ><center><label>&nbsp;&nbsp;&nbsp;&nbsp;MONTANT&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></center></th>

                        </thead>
                      </table>
                      <!-- </div> -->
                    </div>
                    <div class="modal-footer">
                      <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary">Fermer</button>
                    </div>
                  </div>
                </div>
              </div>

          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12" style="margin-bottom: 20px"></div>
        <div id="container"  class="col-md-12" ></div> 

        <div id="nouveau"></div>
      </div>
    </div>

    <p>
    </div><br><br>

        <div class="row">
          <div class="col-12">
            <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">

                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-12">
                      <h1 class="header-title text-black">
                          Liste des projets 
                      </h1>
                    </div>
                  </div>
                </div>
                <div style ="max-width: 15%;">
                  <a href="#" id="btnexport" onclick="exporter()" type="button" style="float: center;margin-top: 10px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-center"></span> Télécharger</a>
                    </div>
               
            <div class="car-body">
              <div class="table-responsive" style="width: 100%;">  
                <table id="mytable1" class=" table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th><center>#</center></th>
                      <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INSTITUTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                  
                      <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PROJET&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                      <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PROVINCE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                      <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;COMMUNE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>

                      <th>PILIER&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                      <th>AXE&nbsp;&nbsp;D'&nbsp;INTERVENTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                      <!-- <th>2026&nbsp;&nbsp;-&nbsp;2027&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th> -->
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
</div>
</div>
</main>
</div>
</div>


<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>


<script type="text/javascript">
$( document ).ready(function() {
  get_rapport();
  listing();
// alert();
});   
</script>

<script type="text/javascript">
function get_rapport(){

var INSTITUTION_ID=$('#INSTITUTION_ID').val();
var inst_conn=$('#inst_conn').val();

$.ajax({
  url : "<?=base_url('dashboard/Rapport_Projet_localite/Get_localite')?>",
  type : "GET",
  dataType: "JSON",
  cache:false,
  data:{
   INSTITUTION_ID:INSTITUTION_ID,
   inst_conn:inst_conn,

 },

 success:function(data){
  $('#container').html("");             
  $('#nouveau').html(data.rapp);

  // $('#INSTITUTION_ID').html(data.inst);
},            
});  
}

function saveData()
{
  $('#myModal').modal('hide');
  $('#myModal_phase').modal('hide');
 }
</script>

<script type="text/javascript">

  function listing()
    {
      var INSTITUTION_ID=$('#INSTITUTION_ID').val();
      var inst_conn=$('#inst_conn').val();

        var row_count ="1000000";
        $("#mytable1").DataTable({
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "targets":[0,4],
          "oreder":[[ 0, 'desc' ]],
          "ajax":{
            url:"<?= base_url('dashboard/Rapport_Projet_localite/liste')?>",
            type:"POST", 
             data:{
               INSTITUTION_ID:INSTITUTION_ID,
               inst_conn:inst_conn,

             } 
          },

          lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
          pageLength: 5,
          "columnDefs":[{
            "targets":[],
            "orderable":false
          }],

          dom: 'Bfrtlip',
          order: [4,'desc'],
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
    }
</script>

<script type="text/javascript">
function exporter()
    {
  var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    
  if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}

  document.getElementById("btnexport").href = "<?=base_url('dashboard/Rapport_Projet_localite/exporter/')?>"+'/'+INSTITUTION_ID;
}
</script>




    