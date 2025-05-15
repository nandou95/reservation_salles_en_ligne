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
          <div class="header">
             <h1 class="header-title text-white">
             Tableau de bord de taux d'exécution
             </h1>
         </div>
          <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
             
                </div>
                <div class="card-body" style="overflow-x:auto;">
                 <div class="row">

                   <?=$inst_connexion?>

                   <div class="col-md-3" style="margin-top:35px;"> 
                    <input type="radio" onchange="get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE5" value="5" <?=$ch4?>>

                    <label>Annuel </label>
                  </div>
                  <div class="col-md-3" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE1" value="1" <?=$ch?>>

                    <label>1er trimestre </label>
                  </div>
                  <div class="col-md-2" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE2" value="2" <?=$ch1?>>

                    <label>2ème trimestre </label>
                  </div>
                  <div class="col-md-2" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE3" value="3" <?=$ch2?>>

                    <label>3ème trimestre </label>
                  </div>
                  <div class="col-md-2" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE4" value="4" <?=$ch3?>>

                    <label>4ème trimestre </label>
                  </div>
                    <div class="form-group col-md-3">
                      <label><b>Type institutions</b></label>
                      <select class="form-control" onchange="get_i()" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                        <option value="">sélectionner</option>
                        <?php
                        foreach ($type_ministre as $value)
                        {
                          if ($value->TYPE_INSTITUTION_ID==$type_connect)
                          { ?>
                          <option value="<?= $value->TYPE_INSTITUTION_ID ?>" selected><?=$value->Name ?></option>
                            <?php
                          } 
                          else
                          { 
                            ?>
                            <option value="<?=$value->TYPE_INSTITUTION_ID?>"><?= $value->Name ?></option>
                            <?php 
                           } 
                        } 
                        ?>
                      </select>        
                    </div>
                    
                    <div class="form-group col-md-3">
                      <label><b><a id="idmin"></a></b></label>
                      <select class="form-control " onchange="get_m()" name="INSTITUTION_ID" id="INSTITUTION_ID">
                        
                        <option value="">Sélectionner</option>

                        
                      </select>        
                    </div>
                    <div class="form-group col-md-3">
                      <label><b>Entités responsable</b></label>
                      <select class="form-control" onchange="get_add()" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                        <option value="">Sélectionner</option>
                      </select>        
                    </div>
                    
                    <div class="form-group col-md-3">
                      <label><b><a id="program"></a></b></label>
                      <select class="form-control" onchange="get_s()" name="PROGRAMME_ID" id="PROGRAMME_ID">
                        
                        <option value="">Sélectionner</option>

                        
                      </select>        
                    </div>
                     <div class="form-group col-md-3">
                      <label><b>Actions</b></label>
                      <select class="form-control" onchange="get_rapport()" onchange="get_act()" name="ACTION_ID" id="ACTION_ID">
                        <option value="">Sélectionner</option>
                        
                      </select>
                       </div>
                   <div class="form-group col-md-2"> 
                      <label><b>Ligne&nbspbudgétaire</b></label>
                      <select class="form-control" onchange="get_rapport()" name="LIGNE_BUDGETAIRE" id="LIGNE_BUDGETAIRE">
                        <option value="">Sélectionner</option>
                      </select>        
                    </div>
                    <div class="modal fade" id="myModal" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                            <h4 class="modal-title"><span id="titre" style="color: black"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width: 100%;" id='mytable' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                               <th style='width:30px'><center><font color="black" size="3"><label>#</label></font></center></th>
                              <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspActivités&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                              <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspQuantités&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                              <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspattendus&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspActions&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                                <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspProgrammes&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                                
                               
                               <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspInstitution&nbspou&nbspMinistère&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               
                               <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbspMontant&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary">Fermer</button>
                        </div>
                      </div>
                    </div>
                  </div>  
                  <div class="modal fade" id="myModal_phase" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                            <h4 class="modal-title"><span id="titre_phase" style="color: black"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width: 100%;" id='mytable_phase' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>

                               
                               <th style='width:30px'><center><font color="black" size="3"><label>#</label></font></center>
                               <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspType&nbspinstitution&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                               </th>   
                               <th style='width:50px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspInstitution&nbspou&nbspMinistère&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:90px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspProgramme&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspAction&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspActivité&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                               <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspRésultat&nbspAttendus&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                               <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspMontant&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary">Fermer</button>
                        </div>
                      </div>
                    </div>
                  </div> 
               
                <div class="modal fade" id="myModal_masse" role="dialog">
              <div class="modal-dialog" style ="max-width: 70%;">
                <div class="modal-content  ">
                  <div class="modal-header">
                    <h4 class="modal-title"><span id="titre_masse" style="color: black"></span></h4>
                  </div>
                  <div class="modal-body">
                    <table style="width: 100%;" id='mytable_masse' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                      <thead>
                      <th style='width:30px'><center><font color="black" size="3"><label>#</label></font></center></th>
                      <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbspMinistère&nbsp&nbsp</label></font></center></th>
                       <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbspProgramme&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbspObjectif&nbspdu&nbspprogramme&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbspAction&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbspActivités&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbspBudget&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                       <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspGrande&nbspmasse&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                     </thead>
                   </table>  
                 </div>
                 <div class="modal-footer">
                  <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary">Fermer</button>
                </div>
              </div>
            </div>
          </div> 
                 
          <div class="modal fade" id="myModal_transfert" role="dialog">
              <div class="modal-dialog" style ="max-width: 70%;">
                <div class="modal-content  ">
                  <div class="modal-header">
                    <h4 class="modal-title"><span id="titre_tranfert" style="color: black"></span></h4>
                  </div>
                  <div class="modal-body">
                    <table style="width: 100%;" id='mytable_transfert' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                      <thead>
                      <th style='width:30px'><center><font color="black" size="3"><label>#</label></font></center></th>
                      <th style='width:90px'><center><font color="black" size="3"><label>&nbsp&nbspMinistère&nbsp&nbsp<a id='idpro'><a>&nbsp&nbsp</label></font></center></th>
                       <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbspProgramme&nbsp&nbsp&nbsp<a id='idcod'><a>&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbspObjectif&nbspdu&nbspprogramme&nbsp<a id='idcod'><a>&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbspAction&nbsp&nbsp<a id='idobj'><a>&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                         <th style='width:70px'><center><font color="black" size="3"><label>&nbspRésultat&nbsp&nbspattendus&nbsp&nbs<a id='idobj'><a>&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbspActivités&nbsp&nbsp&nbsp&nbsp<a id='idobj'><a>&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbspBudget&nbsp&nbspvoté&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                       <th style='width:50px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspBudget&nbsptransferé&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                     </thead>
                   </table>  
                 </div>
                 <div class="modal-footer">
                  <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary">Fermer</button>
                </div>
              </div>
            </div>
          </div>



          <div class="modal fade" id="myModal_recu" role="dialog">
              <div class="modal-dialog" style ="max-width: 70%;">
                <div class="modal-content  ">
                  <div class="modal-header">
                    <h4 class="modal-title"><span id="titre_recu" style="color: black"></span></h4>
                  </div>
                  <div class="modal-body">
                    <table style="width: 100%;" id='mytable_recu' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                      <thead>
                      <th style='width:30px'><center><font color="black" size="3"><label>#</label></font></center></th>
                      <th style='width:90px'><center><font color="black" size="3"><label>&nbsp&nbspMinistère&nbsp&nbsp</label></font></center></th>
                       <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbspProgramme&nbsp&nbsp&nbsp<a></label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbspObjectif&nbspdu&nbspprogramme&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbspAction&nbsp&nbsp</label></font></center></th>

                         <th style='width:70px'><center><font color="black" size="3"><label>&nbspRésultat&nbsp&nbspattendus&nbsp&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbspActivités&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbspBudget&nbsp&nbspvoté&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                       <th style='width:50px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspBudget&nbspreçu&nbsp&nbspdu&nbsp&nbsptransfert&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
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
                <div id="container1"  class="col-md-12" style="height: 55em"></div>
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                <div id="container"  class="col-md-12" style="height: 55em"></div>
                <div id="nouveau"></div>
                <div id="nouveau1"></div>
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

<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript">
    let table = new DataTable('#myTable');
  </script>
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

function get_add() {
  $('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');

$('#LIGNE_BUDGETAIRE').html('');

    get_rapport();
   
}
function get_s() {

$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
   
}
function get_act() {

$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
   
}
 
</script>
<script type="text/javascript">
  function get_rapport(){

  if (document.getElementById('IS_PRIVATE1').checked) {
 var IS_PRIVATE = document.getElementById('IS_PRIVATE1').value;
   }else if (document.getElementById('IS_PRIVATE2').checked) {
 var IS_PRIVATE = document.getElementById('IS_PRIVATE2').value;
   }else if (document.getElementById('IS_PRIVATE3').checked) {
 var IS_PRIVATE = document.getElementById('IS_PRIVATE3').value;
    }else if (document.getElementById('IS_PRIVATE4').checked) {
 var IS_PRIVATE = document.getElementById('IS_PRIVATE4').value;
}else if(document.getElementById('IS_PRIVATE5').checked) {
 var IS_PRIVATE = document.getElementById('IS_PRIVATE5').value;
}

    var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
    var LIGNE_BUDGETAIRE=$('#LIGNE_BUDGETAIRE').val();
    var inst_conn=$('#inst_conn').val();
    $.ajax({
      url : "<?=base_url('dashboard/Dashbord_Taux_Execution_Circuit_Demande/execution_Circuit_demande')?>",
      type : "GET",
      dataType: "JSON",
      cache:false,
      data:{
       TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
       INSTITUTION_ID:INSTITUTION_ID,
       PROGRAMME_ID:PROGRAMME_ID,
       ACTION_ID:ACTION_ID,
       IS_PRIVATE:IS_PRIVATE,
       SOUS_TUTEL_ID:SOUS_TUTEL_ID,
       LIGNE_BUDGETAIRE:LIGNE_BUDGETAIRE,
       inst_conn:inst_conn,
         },
     success:function(data){            
      $('#container1').html("");
      $('#nouveau').html(data.rapp1);
      $('#container').html("");
      $('#nouveau').html(data.rapp);
      $('#SOUS_TUTEL_ID').html(data.soustutel);
      $('#INSTITUTION_ID').html(data.inst);
      $('#PROGRAMME_ID').html(data.program);
      $('#ACTION_ID').html(data.actions);
      $('#LIGNE_BUDGETAIRE').html(data.ligne_budgetaires);
      if (TYPE_INSTITUTION_ID==1) {
        $("#idmin").html("Institution");
        $("#program").html("Dotations");
        id_action.style.display='none';
        }
      else if (TYPE_INSTITUTION_ID==2) {
        $("#idmin").html("Ministère");
        $("#program").html("Programmes");
        id_action.style.display='block';
         }else{
        $("#idmin").html("Institution/Ministère");
         $("#program").html("Dotations/Programmes");
        id_action.style.display='none';
        $("#program").html("Programmes");
      } 
    },            
  });  
  }
  function saveData()
  {
  $('#myModal').modal('hide');
  $('#myModal_phase').modal('hide');
  $('#myModal_masse').modal('hide');
  $('#myModal_transfert').modal('hide');
  $('#myModal_recu').modal('hide');
 }
</script>

