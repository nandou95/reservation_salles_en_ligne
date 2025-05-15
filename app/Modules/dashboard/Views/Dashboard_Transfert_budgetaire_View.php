
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
       
          <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                </div>
                <div class="card-body" style="overflow-x:auto;">
                 <div class="row">

                   <div class="col-md-12">
                    <h2 class="header-title text-black">
                      Tableau de bord des transferts
                    </h2>
                  </div>
                   <!--  <div class="form-group col-md-6"> 
                      <label><b><?=lang('messages_lang.select_anne_budget')?></b></label>
                      <select class="form-control" onchange="get_rapport();liste()" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID">
                        <?php foreach($anne_budget as $key){ ?>
                          <?php if($key->ANNEE_BUDGETAIRE_ID == $ann_actuel_id){ ?>
                            <option value="<?=$key->ANNEE_BUDGETAIRE_ID?>" selected><?=$key->ANNEE_DESCRIPTION?></option>
                          <?php }else{ ?>
                            <option value="<?=$key->ANNEE_BUDGETAIRE_ID?>"><?=$key->ANNEE_DESCRIPTION?></option>
                        <?php } } ?>
                      </select>        
                    </div> -->
                    
                    
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
                               <th style='width:50px'><center><font color="white" size="3"><label>&nbsp&nbspInstitution&nbspou&nbspMinistère&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                              <th style='width:90px'><center><font color="white" size="3"><label>&nbsp&nbspActivités&nbsp&nbspd'origine&nbsp&nbsp&nbsp</label></font></center></th>
                              <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbspMontant&nbsp&nbsptransféré&nbsp&nbsp</label></font></center></th>
                            
                             <th style='width:90px'><center><font color="white" size="3"><label>Activités&nbspde&nbspdestination&nbsp&nbsp&nbsp</label></font></center></th>
                             <th style='width:90px'><center><font color="white" size="3"><label>&nbspTrimestre&nbsp&nbspd'origine&nbsp&nbsp&nbsp</label></font></center></th>
                             <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspActions&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                            <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbspRésultats&nbsp&nbspattendus&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                            <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbspProgrammes&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                            <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbspDate&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                            </thead>
                           </table>  
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
                <div id="container"  class="col-md-12"></div>
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                <div id="container1"  class="col-md-12"></div>

                <div id="nouveau"></div>
                <div id="nouveau1"></div>
               </div>

               <br>

               <div class="row">
                  <div  class="container" class="col-md-12">
                    

                    <div class="table-responsive">
                      <h3 class="text-black">Liste des transferts budgetaire</h3>

                    <div style ="max-width: 15%;">
                        <a href="#" id="btnexport" onclick="exporter()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-center"></span><?=lang('messages_lang.pip_rapport_institutio_telecharge')?></a>
                          </div>

                          <!-- <br><br> -->
                      <table id="mytable1" class="table table-bordered" style="width:100%">
                        <thead>
                         <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center></th>
                         <th style='width:50px'><center><font color="white" size="3"><label>&nbsp&nbspInstitution&nbspou&nbspMinistère&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:90px'><center><font color="white" size="3"><label>&nbsp&nbspActivités&nbsp&nbspd'origine&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbspMontant&nbsp&nbsptransféré&nbsp&nbsp</label></font></center></th>
                      
                       <th style='width:90px'><center><font color="white" size="3"><label>Activités&nbspde&nbspdestination&nbsp&nbsp&nbsp</label></font></center></th>
                       <th style='width:90px'><center><font color="white" size="3"><label>&nbspTrimestre&nbsp&nbspd'origine&nbsp&nbsp&nbsp</label></font></center></th>
                       <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspActions&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                      <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbspRésultats&nbsp&nbspattendus&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                      <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbspProgrammes&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                      <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbspDate&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                      </thead>

                      </table>
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



    <?php echo view('includesbackend/scripts_js.php');?>
  </body>
  </html>

<!-- <link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript">
    let table = new DataTable('#myTable');
  </script> -->
<script type="text/javascript">
 $( document ).ready(function() {
    get_rapport();
    // alert();
});   
function get_i() {

 $('#INSTITUTION_ID').html('');
$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#SOUS_TUTEL_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');

 get_rapport();
   
}
</script>
<script type="text/javascript">
function get_m() {
  $('#SOUS_TUTEL_ID').html('');
  $('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
   
}

function get_add()
{
	$('#PROGRAMME_ID').html('');
	$('#ACTION_ID').html('');
	$('#LIGNE_BUDGETAIRE').html('');
	get_rapport();

}
function get_s()
{
 $('#ACTION_ID').html('');
 $('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
}
function get_act()
 {
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();  
}
 
</script>
<script type="text/javascript">
  function get_rapport(){
  
    var inst_conn=$('#inst_conn').val();
    $.ajax({
      url : "<?=base_url('dashboard/Dashboard_Transfert_budgetaire/get_transfert_budgetaire')?>",
      type : "GET",
      dataType: "JSON",
      cache:false,
      data:{
       inst_conn:inst_conn,
      },
     success:function(data){
      $('#container').html("");             
      $('#nouveau').html(data.rapp);

      $('#container1').html("");             
      $('#nouveau1').html(data.rapp1);
     
    },            
  });  
  }
  function saveData()
  {
  $('#myModal').modal('hide');
 }
</script>

<script>

  $( document ).ready(function() {
     listing();
});

function listing(){

    var row_count ="1000000";
    $("#mytable1").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":{
        url:"<?= base_url('dashboard/Dashboard_Transfert_budgetaire/listing')?>",
        type:"POST",
    },

    lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, "All"]],
    pageLength: 5,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    dom: 'Bfrtlip',
    order: [],
    buttons: [],
    language: {
      "sProcessing":     "Traitement en cours...",
      "sSearch":         "Rechercher&nbsp;:",
      "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
      "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
      "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
      "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
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

  function exporter()
  {

  document.getElementById("btnexport").href = "<?=base_url('dashboard/Dashboard_Transfert_budgetaire/exporter/')?>";
}
</script>