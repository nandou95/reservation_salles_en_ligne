<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
  <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>
</head>
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
                  <?=lang('messages_lang.rapp_activite_depassant_budget_vote')?></h1>
                  </div>
                
                   <?=$inst_connexion?>
                   
                  <div class="col-md-3" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE1" value="1" <?=$ch?>>

                    <label><?=lang('messages_lang.trimestre1')?></label>
                  </div>
                  <div class="col-md-2" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE2" value="2" <?=$ch1?>>

                    <label><?=lang('messages_lang.trimestre2')?></label>
                  </div>
                  <div class="col-md-2" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE3" value="3" <?=$ch2?>>

                    <label><?=lang('messages_lang.trimestre3')?> </label>
                  </div>
                  <div class="col-md-2" style="margin-top:35px;">
                    <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE4" value="4" <?=$ch3?>>

                    <label><?=lang('messages_lang.trimestre4')?> </label>
                  </div>

                  <div class="col-md-3" style="margin-top:35px;"> 
                    <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE5" value="5" <?=$ch4?>>
                    <label><?=lang('messages_lang.label_annuel')?> </label>
                  </div>
                    <div class="form-group col-md-6">
                      <label><b>Catégorie</b></label>
                      <select class="form-control" onchange="get_i();liste_programme()" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
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
                    
                    <div class="form-group col-md-6">
                      <label><b><a id="idmin"></a></b></label>
                      <select class="form-control " onchange="get_m();liste_programme()" name="INSTITUTION_ID" id="INSTITUTION_ID">
                      <option value="">Sélectionner</option>
                      </select>        
                    </div>
                   <!--  <div class="form-group col-md-4"> 
                      <label><b><?=lang('messages_lang.select_anne_budget')?></b></label>
                      <select class="form-control" onchange="get_rapport();liste_programme()" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID">
                        <?php foreach($anne_budget as $key){ ?>
                          <?php if($key->ANNEE_BUDGETAIRE_ID == $ann_actuel_id){ ?>
                            <option value="<?=$key->ANNEE_BUDGETAIRE_ID?>" selected><?=$key->ANNEE_DESCRIPTION?></option>
                          <?php }else{ ?>
                            <option value="<?=$key->ANNEE_BUDGETAIRE_ID?>"><?=$key->ANNEE_DESCRIPTION?></option>
                        <?php } } ?>
                      </select>        
                    </div> -->
                   <!--  <div class="form-group col-md-2"> 
                      <label><b><?=lang('messages_lang.label_droit_execution')?></b></label>
                      <select class="form-control" onchange="get_rapport();liste_programme()" name="IS_DOUBLE_COMMANDE" id="IS_DOUBLE_COMMANDE">
                        <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                        <option value="1"><?=lang('messages_lang.label_droit_raccrochage')?></option>
                        <option value="2"><?=lang('messages_lang.label_droit_double_com')?></option>
                      </select>        
                    </div> -->

              </div>
              </div>

              <!-- ------------------- -->
              </div>
              </div>
              </div>


             <!-- rapport graphique -->
             <div class="row" style="margin-top: -5px">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">

                  <div class="row">
                    <div class="col-md-12" style="margin-bottom: 20px"></div>
                    <div id="container1"  class="col-md-12"></div>
                  </div>

                </div>
              </div>
            </div>
           <!-- Liste -->
            <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">

                <div class="row">
                  <div  class="container" style= "width:95%">
                    <h3 class="text-black"><?=lang('messages_lang.activites_list')?></h3>

                    <div style ="max-width: 15%;">
                        <a href="#" id="btnexport" onclick="exporter()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-center"></span><?=lang('messages_lang.pip_rapport_institutio_telecharge')?></a>
                          </div>

                    <div class="table-responsive container">
                      <table id="mytable1" class="table table-bordered" style="width:100%">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th><?=lang('messages_lang.labelle_inst_min')?>&nbsp;</th>
                            <th><?=lang('messages_lang.label_prog')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;</th>
                            <th><?=lang('messages_lang.labelle_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;</th>
                            <th><?=lang('messages_lang.label_resultant_attendus')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;</th>
                            <th>Activités&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;</th>
                            <th><?=lang('messages_lang.budget_vote')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;</th>
                            <th><?=lang('messages_lang.labelle_eng_budget')?>&nbsp&nbsp&nbsp&nbsp;</th>
                            <th><?=lang('messages_lang.labelle_eng_budget')?>&nbspDate&nbsp&nbsp&nbsp&nbsp&nbsp;</th>
                            <th><?=lang('messages_lang.label_trans2')?>&nbsp&nbsp&nbsp&nbsp&nbsp;</th>
                          </tr>
                        </thead>

                      </table>
                    </div>
                  </div>
                </div>
              </div>
                    
                <div id="nouveau"></div>
                <div id="nouveau1"></div>
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
                      <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspGrande&nbspmasse&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                      <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbspMinistère&nbsp&nbsp</label></font></center></th>
                       <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbspProgramme&nbsp&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbspObjectif&nbspdu&nbspprogramme&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbsp&nbspAction&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbspActivités&nbsp&nbsp</label></font></center></th>
                        <th style='width:120px'><center><font color="black" size="3"><label>&nbspBudget&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th> 
                     </thead>
                   </table>  
                 </div>
                 <div class="modal-footer">
                  <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary">Fermer</button>
                </div>
              </div>
            </div>
          </div> 




                       <!-- Modal detail -->
                      <div class="modal fade" id="myModal" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                            <h3 class="modal-title text-black"><span id="titre" style="color: black;"></span></h3>
                          </div>
                          <div class="modal-body">
                            <table style="width: 100%;" id='mytable' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                                <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center>
                               <th style='width:100px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_inst_min')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                                <th style='width:70px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.label_prog')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                               <th style='width:120px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_action')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.label_resultant_attendus')?>&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                               <th style='width:120px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_activite')?>&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.budget_vote')?>&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_eng_budget')?>&nbsp;&nbsp;&nbsp;</label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspDate&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_eng_budget')?>&nbsp&nbsp&nbsp</label></font></center></th>
                               
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
 $( document ).ready(function() {
    get_rapport();
     liste_programme();
    // alert();
});   
function get_i() {

 $('#INSTITUTION_ID').html('');
$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#SOUS_TUTEL_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');

 get_rapport();
 liste_programme();
   
}
</script>
<script type="text/javascript">
function get_m() {
  $('#SOUS_TUTEL_ID').html('');
  $('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
    liste_programme();
   
}

function get_add() {
  $('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');

$('#LIGNE_BUDGETAIRE').html('');

    get_rapport();
    liste_programme();
   
}
function get_s() {

$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
   
}
function get_act() {

$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
    liste_programme();
   
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
      url : "<?=base_url('dashboard/Dashboard_Depassement_Budget_Vote/execution_deppasement_budget_vote')?>",
      type : "GET",
      dataType: "JSON",
      cache:false,
      data:{
       TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
       INSTITUTION_ID:INSTITUTION_ID,
       IS_PRIVATE:IS_PRIVATE,
       SOUS_TUTEL_ID:SOUS_TUTEL_ID,
       inst_conn:inst_conn,
     
      
      },
     success:function(data){            
      $('#container1').html("");
      $('#nouveau').html(data.rapp1);
      $('#SOUS_TUTEL_ID').html(data.soustutel);
      $('#INSTITUTION_ID').html(data.inst);
      $('#PROGRAMME_ID').html(data.program);
      $('#ACTION_ID').html(data.actions);
      $('#LIGNE_BUDGETAIRE').html(data.ligne_budgetaires);
      if (TYPE_INSTITUTION_ID==1) {
        $("#idmin").html("Administrations personnalisées");
        $("#program").html("Dotations");
        // id_action.style.display='block';
         }
      else if (TYPE_INSTITUTION_ID==2) {
        $("#idmin").html("Ministères");
        $("#program").html("Programmes");
        // id_action.style.display='block';
      }else{
        $("#idmin").html("Ministères/Administrations personnalisées");
        // id_action.style.display='block';
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

<script>

  function liste_programme(){

    if (document.getElementById('IS_PRIVATE1').checked) {
       var IS_PRIVATE = document.getElementById('IS_PRIVATE1').value;
       }else if (document.getElementById('IS_PRIVATE2').checked) {
      var IS_PRIVATE = document.getElementById('IS_PRIVATE2').value;
      }else if (document.getElementById('IS_PRIVATE3').checked) {
      var IS_PRIVATE = document.getElementById('IS_PRIVATE3').value;
      }else if (document.getElementById('IS_PRIVATE4').checked) {
     var IS_PRIVATE = document.getElementById('IS_PRIVATE4').value;
      }else if (document.getElementById('IS_PRIVATE5').checked) {
     var IS_PRIVATE = document.getElementById('IS_PRIVATE5').value;
     }

    var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var inst_conn=$('#inst_conn').val();
   


    var row_count ="1000000";
    $("#mytable1").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":{
        url:"<?= base_url('dashboard/Dashboard_Depassement_Budget_Vote/liste_depassement_budget_vote')?>",
        type:"POST",
        data:{IS_PRIVATE:IS_PRIVATE,
              TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
              INSTITUTION_ID:INSTITUTION_ID,
              SOUS_TUTEL_ID:SOUS_TUTEL_ID,
              inst_conn:inst_conn,
             
              
      },
    },

    lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, "All"]],
    pageLength: 5,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    dom: 'Bfrtlip',
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

</script>


<script>
function get_detail_activite(id)
    {
   $("#detail").modal("show");
   var row_count ="1000000";
   table2=$("#mytable2").DataTable(
     {
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "ajax":{
      url:"<?= base_url('')?>/dashboard/Dashboard_Depassement_Budget_Vote/liste_activites/"+id,
      type:"POST",
      },
    lengthMenu: [[5,50, 100, row_count], [5,50, 100, "All"]],
    pageLength: 5,
    "columnDefs":[{
      "targets":[],
      "orderable":false
        }],
    dom: 'Bfrtlip',
    order:[],
    buttons:[ 'pdf'],
       language: {
          "sProcessing":  "<?=lang('messages_lang.labelle_et_traitement')?>",
         "sSearch": "<?=lang('messages_lang.search_button')?>",
        "sLengthMenu": "<?=lang('messages_lang.labelle_et_afficher')?> _MENU_ <?=lang('messages_lang.labelle_et_affichage_element')?>",
        "sInfo": "<?=lang('messages_lang.labelle_et_affichage_element')?> _START_ <?=lang('messages_lang.labelle_et_a')?> _END_ <?=lang('messages_lang.labelle_et_a')?> _TOTAL_ <?=lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty": "<?=lang('messages_lang.labelle_et_affichage_element')?> 0 <?=lang('messages_lang.labelle_et_a')?> 0 <?=lang('messages_lang.labelle_et_affichage_filtre')?> 0 <?=lang('messages_lang.labelle_et_element')?>",
        "sInfoFiltered": "(<?=lang('messages_lang.labelle_et_elementtotal')?>)",
        "sInfoPostFix": "",
        "sLoadingRecords": "<?=lang('messages_lang.labelle_et_chargement')?>...",
        "sZeroRecords": "<?=lang('messages_lang.labelle_et_aucun_element')?>",
        "sEmptyTable": "<?=lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
        "oPaginate": {
          "sFirst": "<?=lang('messages_lang.labelle_et_premier')?>",
          "sPrevious": "<?=lang('messages_lang.labelle_et_precedent')?>",
          "sNext": "<?=lang('messages_lang.labelle_et_suivant')?>",
          "sLast": "<?=lang('messages_lang.labelle_et_dernier')?>"
        },
        "oAria": {
          "sSortAscending": ": <?=lang('messages_lang.labelle_et_trier_colone')?>",
          "sSortDescending": ": <?=lang('messages_lang.labelle_et_trier_activer_trier')?>"
        }
    }
  }
  );

 }
</script>
<script type="text/javascript">
  function show_modal(id){
    $.ajax(
      {
      url:"<?=base_url()?>/dashboard/Dashboard_Depassement_Budget_Vote/libelleCall/"+id,
      type:"POST",
      dataType:"JSON",
      success: function(data)
      {
      $('#libelle').html(data.data123)
      }
    });
    $('#exampleModal').modal('show')
  }
</script>
<script type="text/javascript">
function exporter()
    {
  if (document.getElementById('IS_PRIVATE1').checked) {
    var IS_PRIVATE = document.getElementById('IS_PRIVATE1').value;
  }else if (document.getElementById('IS_PRIVATE2').checked) {
    var IS_PRIVATE = document.getElementById('IS_PRIVATE2').value;
  }else if (document.getElementById('IS_PRIVATE3').checked) {
    var IS_PRIVATE = document.getElementById('IS_PRIVATE3').value;
  }else if (document.getElementById('IS_PRIVATE4').checked) {
    var IS_PRIVATE = document.getElementById('IS_PRIVATE4').value;
  }else if (document.getElementById('IS_PRIVATE5').checked) {
    var IS_PRIVATE = document.getElementById('IS_PRIVATE5').value;
  }
  var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val();
  var INSTITUTION_ID=$('#INSTITUTION_ID').val();

 
  
  if (TYPE_INSTITUTION_ID == ''  || TYPE_INSTITUTION_ID == null){TYPE_INSTITUTION_ID = 0}
  if (INSTITUTION_ID == ''  || INSTITUTION_ID == null){INSTITUTION_ID = 0}
  if (IS_PRIVATE == ''  || IS_PRIVATE == null){IS_PRIVATE = 0}
 
  
  document.getElementById("btnexport").href = "<?=base_url('dashboard/Dashboard_Depassement_Budget_Vote/exporter/')?>"+'/'+TYPE_INSTITUTION_ID+'/'+INSTITUTION_ID+'/'+IS_PRIVATE;
}
</script>
<div class="modal fade" id="detail">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <b style="font-size:20px" class="modal-title header-title text-black" id="exampleModalLabel"><?=lang('messages_lang.list_transfert_faits')?></b>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table id='mytable2' class="table table-bordered table-striped table-hover table-condensed " style="width: 100%;">
            <thead>
              <tr> 
               <th>&nbsp;<?=lang('messages_lang.institution_origine')?>&nbsp;&nbsp;</th>

                <th>&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.programme_origine')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <th>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.activite_origine')?>&nbsp;&nbsp;</th>
                <th>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_montant_transferer')?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
                <th>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.date_transferer')?>&nbsp;&nbsp;</th>
               <th>&nbsp;&nbsp;<?=lang('messages_lang.trimestre_origin')?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
              </tr>
            </thead>
            <tbody id="table2">
            </tbody>
          </table>
          <div class="modal-footer">
            <button class="btn mb-1 btn-primary" class="close" data-dismiss="modal"><?=lang('messages_lang.quiter_action')?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>