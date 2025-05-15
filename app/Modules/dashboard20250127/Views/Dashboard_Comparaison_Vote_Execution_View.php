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
                    <h1 class="header-title text-black">
                       <?=lang('messages_lang.tb_budget')?>
                     </h1>
                   </div>
                   <?=$inst_connexion?>
                  <div class="col-md-3" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport();liste();" name="IS_PRIVATE" id="IS_PRIVATE1" value="1" <?=$ch?>>

                    <label><?=lang('messages_lang.trimestre1')?> </label>
                  </div>
                  <div class="col-md-2" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport();liste();" name="IS_PRIVATE" id="IS_PRIVATE2" value="2" <?=$ch1?>>

                    <label><?=lang('messages_lang.trimestre2')?> </label>
                  </div>
                  <div class="col-md-2" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport();liste();" name="IS_PRIVATE" id="IS_PRIVATE3" value="3" <?=$ch2?>>

                    <label><?=lang('messages_lang.trimestre3')?> </label>
                  </div>
                  <div class="col-md-2" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport();liste();" name="IS_PRIVATE" id="IS_PRIVATE4" value="4" <?=$ch3?>>
                    <label><?=lang('messages_lang.trimestre4')?> </label>
                  </div>
                   <div class="col-md-3" style="margin-top:35px;"> 
                    <input type="radio" onchange="get_rapport();liste();" name="IS_PRIVATE" id="IS_PRIVATE5" value="5" <?=$ch4?>>
                    <label><?=lang('messages_lang.label_annuel')?> </label>
                  </div>
                    <div class="form-group col-md-2">
                      <label><b>Catégorie</b></label>
                      <select class="form-control" onchange="get_i()" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                        <option value=""><?=lang('messages_lang.selection_message')?></option>
                        <?php
                        foreach ($type_ministre as $value)
                        {
                          if ($value->TYPE_INSTITUTION_ID==$TYPE_INSTITUTION_ID)
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
                    
                    <div class="form-group col-md-4">
                      <label><b><a id="idmin"></a></b></label>
                      <select class="form-control " onchange="get_m()" name="INSTITUTION_ID" id="INSTITUTION_ID">
                        <option value=""><?=lang('messages_lang.selection_message')?></option>
                      </select>        
                    </div>
                    <div class="form-group col-md-3">
                      <label><b><a id="program"></a></b></label>
                      <select class="form-control" onchange="get_s()" name="PROGRAMME_ID" id="PROGRAMME_ID">
                        <option value=""><?=lang('messages_lang.selection_message')?></option>
                      </select>        
                    </div>
                     <div class="form-group col-md-3">
                      <label><b><?=lang('messages_lang.label_action')?></b></label>
                      <select class="form-control" onchange="get_rapport();liste()" onchange="get_act()" name="ACTION_ID" id="ACTION_ID">
                        <option value=""><?=lang('messages_lang.selection_message')?></option>
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
                               <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center></th>
                               <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_inst_min')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                             <th style='width:90px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_prog')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                              <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                              <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_activites')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                              <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspResultatss&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                             <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspTaches&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                            <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_montant')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                        <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
                        </div>
                      </div>
                    </div>
                  </div>  
                  <div class="modal fade" id="myModal_phase" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content ">
                          <div class="modal-header">
                            <h4 class="modal-title"><span id="titre_phase" style="color: black"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width: 100%;" id='mytable_phase' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>

                               
                             <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center>
                             <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.categorie_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                             </th>   
                             <th style='width:50px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_inst_min')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                             <th style='width:90px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_prog')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                             <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                             <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_activite')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                             <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_resultant_attendus')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                             <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_montant')?>&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
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
                      <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center></th>
                      <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.minister')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                       <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.labelle_programme')?>&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                       <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.objectif_programm')?>&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.label_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="white" size="3"><label>&nbsp<?=lang('messages_lang.label_activite')?>s&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="white" size="3"><label>&nbspBudget&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                       <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.menu_grande_masse')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                     </thead>
                   </table>  
                 </div>
                 <div class="modal-footer">
                  <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
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
                      <th style='width:90px'><center><font color="black" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.minister')?>&nbsp&nbsp<a id='idpro'><a>&nbsp&nbsp</label></font></center></th>
                       <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.labelle_programme')?>&nbsp&nbsp&nbsp<a id='idcod'><a>&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbsp<?=lang('messages_lang.objectif_programm')?>&nbsp<a id='idcod'><a>&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.label_action')?>&nbsp&nbsp<a id='idobj'><a>&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                         <th style='width:70px'><center><font color="black" size="3"><label>&nbsp;<?=lang('messages_lang.label_resultant_attendus')?>&nbsp;&nbsp;<a id='idobj'><a>&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbsp<?=lang('messages_lang.label_activite')?>s&nbsp&nbsp&nbsp&nbsp<a id='idobj'><a>&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbsp<?=lang('messages_lang.budget_vote')?>&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                       <th style='width:50px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_montant_transferer')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                     </thead>
                   </table>  
                 </div>
                 <div class="modal-footer">
                  <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
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
                      <th style='width:90px'><center><font color="black" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.minister')?>&nbsp&nbsp</label></font></center></th>
                       <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.label_prog')?>&nbsp&nbsp&nbsp<a></label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbsp<?=lang('messages_lang.objectif_programm')?>&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.label_action')?>&nbsp&nbsp</label></font></center></th>

                         <th style='width:70px'><center><font color="black" size="3"><label>&nbsp<?=lang('messages_lang.label_resultant_attendus')?>&nbsp&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbsp<?=lang('messages_lang.label_activite')?>s&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbspBudget&nbsp&nbspvoté<?=lang('messages_lang.budget_vote')?>&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                       <th style='width:50px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_trans2')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                     </thead>
                   </table>  
                 </div>
                 <div class="modal-footer">
                  <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
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
                <div id="container1"  class="col-md-12"></div>
                  <div class="col-md-12" style="margin-bottom: 20px"></div>
          
            
                <div id="nouveau"></div>
                <div id="nouveau1"></div>
                <div id="nouveau2"></div>
                <div id="nouveau3"></div>
                <div id="nouveau4"></div>
                <div id="nouveau5"></div>
                <div id="nouveau6"></div>
                <div id="nouveau7"></div>
               </div>
          </div>

            
          <p>
        </div>
        <!-- <div class="table-responsive" style="width: 100%;">
          <div style ="max-width: 15%;">
            <a id="btnexport" onclick="exporter()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-center"></span><?=lang('messages_lang.pip_rapport_institutio_telecharge')?></a>
          </div> 
          <table id="mytable2" class=" table table-striped table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th><?=lang('messages_lang.labelle_institutio')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                <th><?=lang('messages_lang.th_programme')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                <th><?=lang('messages_lang.th_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                <th><?=lang('messages_lang.th_activite')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                <th><?=lang('messages_lang.menu_grande_masse')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                <th><?=lang('messages_lang.trimestre1')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                <th><?=lang('messages_lang.trimestre2')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                <th><?=lang('messages_lang.trimestre3')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                <th><?=lang('messages_lang.trimestre4')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div> -->
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
    let table = new DataTable('#myTable');
  </script>
<script type="text/javascript">
 $( document ).ready(function() {
    get_rapport();
    liste();
});   
function get_i() {

 $('#INSTITUTION_ID').html('');
$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#SOUS_TUTEL_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
$('#PAP_ACTIVITE_ID').html('');
 get_rapport();
 liste();
   
}
</script>
<script type="text/javascript">
function get_m() {
  $('#SOUS_TUTEL_ID').html('');
  $('#PROGRAMME_ID').html('');
  $('#ACTION_ID').html('');
  $('#LIGNE_BUDGETAIRE').html('');
  $('#PAP_ACTIVITE_ID').html('');
    get_rapport();
    liste();
   
}


function get_add() {

$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');

$('#LIGNE_BUDGETAIRE').html('');

    get_rapport();
    liste();
   
}
function get_s() {
$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
    liste();
}
function get_act() {

$('#LIGNE_BUDGETAIRE').html('');
$('#PAP_ACTIVITE_ID').html('');
    get_rapport();
    liste();
}

 function get_activite() 
  {
  $('#PAP_ACTIVITE_ID').html('');
    get_rapport();
    liste();
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
    var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();

    $.ajax({
      url : "<?=base_url('dashboard/Dashboard_Comparaison_Vote_Execution/get_Rapport_comparaison')?>",
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
       PAP_ACTIVITE_ID:PAP_ACTIVITE_ID,
      },
     success:function(data){
      $('#container').html("");             
      $('#container1').html("");
      $('#nouveau').html(data.rapp);
      $('#nouveau1').html(data.rapp1);
      $('#SOUS_TUTEL_ID').html(data.soustutel);
      $('#INSTITUTION_ID').html(data.inst);
      $('#PROGRAMME_ID').html(data.program);
      $('#ACTION_ID').html(data.actions);
      $('#LIGNE_BUDGETAIRE').html(data.ligne_budgetaires);
       $('#PAP_ACTIVITE_ID').html(data.ligne_activite);

      if (TYPE_INSTITUTION_ID==1) {
        $("#idmin").html("<?=lang('messages_lang.admin_perso')?>");
        $("#program").html("Dotations");
        
        }
      else if (TYPE_INSTITUTION_ID==2) {
        $("#idmin").html("<?=lang('messages_lang.minister')?>");
        $("#program").html("<?=lang('messages_lang.labelle_programme')?>");
        
         }else{
        $("#idmin").html("<?=lang('messages_lang.minister')?>/<?=lang('messages_lang.admin_perso')?>");
         $("#program").html("<?=lang('messages_lang.dotation_programe')?>");
        
        $("#program").html("<?=lang('messages_lang.labelle_programme')?>");
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
<script>
  function liste()
  {
    if (document.getElementById('IS_PRIVATE1').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE1').value;
    }else if (document.getElementById('IS_PRIVATE2').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE2').value;
    }else if (document.getElementById('IS_PRIVATE3').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE3').value;
    }else if (document.getElementById('IS_PRIVATE4').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE4').value;
    }else if (document.getElementById('IS_PRIVATE5').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE5').value;
    }
    var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
    var LIGNE_BUDGETAIRE=$('#LIGNE_BUDGETAIRE').val();
    var inst_conn=$('#inst_conn').val();
    var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();

    var row_count ="1000000";
    $("#mytable2").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":
      {
        url:"<?= base_url('dashboard/Dashboard_Comparaison_Vote_Execution/listing_budget')?>",
        type:"POST", 
        data:
        {
          TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
          INSTITUTION_ID:INSTITUTION_ID,
          SOUS_TUTEL_ID:SOUS_TUTEL_ID,
          PROGRAMME_ID:PROGRAMME_ID,
          ACTION_ID:ACTION_ID,
          IS_PRIVATE:IS_PRIVATE,
          LIGNE_BUDGETAIRE:LIGNE_BUDGETAIRE,
          inst_conn:inst_conn,
          PAP_ACTIVITE_ID:PAP_ACTIVITE_ID,
        } 
      },
      lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, "All"]],
      pageLength: 5,
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
  }
</script>
<script type="text/javascript">
function exporter()
{
  if (document.getElementById('IS_PRIVATE1').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE1').value;
    }else if (document.getElementById('IS_PRIVATE2').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE2').value;
    }else if (document.getElementById('IS_PRIVATE3').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE3').value;
    }else if (document.getElementById('IS_PRIVATE4').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE4').value;
    }else if (document.getElementById('IS_PRIVATE5').checked){
      var IS_PRIVATE = document.getElementById('IS_PRIVATE5').value;
    }
    var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
    var LIGNE_BUDGETAIRE=$('#LIGNE_BUDGETAIRE').val();
    var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();

    if (TYPE_INSTITUTION_ID == '' || TYPE_INSTITUTION_ID == null) {TYPE_INSTITUTION_ID = 0}
    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
    if (LIGNE_BUDGETAIRE == '' || LIGNE_BUDGETAIRE == null) {LIGNE_BUDGETAIRE = 0}
    if (IS_PRIVATE == '' || IS_PRIVATE == null) {IS_PRIVATE = 0}
    if (PAP_ACTIVITE_ID == '' || PAP_ACTIVITE_ID == null) {PAP_ACTIVITE_ID = 0}

    document.getElementById("btnexport").href = "<?=base_url('dashboard/Dashboard_Comparaison_Vote_Execution/exporter/')?>"+'/'+TYPE_INSTITUTION_ID+'/'+INSTITUTION_ID+'/'+SOUS_TUTEL_ID+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+LIGNE_BUDGETAIRE+'/'+IS_PRIVATE+'/'+PAP_ACTIVITE_ID;
}
</script>