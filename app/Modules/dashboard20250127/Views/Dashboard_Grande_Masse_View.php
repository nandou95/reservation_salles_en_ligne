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
             <div class="col-md-12">
               <h3 class="header-title text-black"><?=lang('messages_lang.tb_grande_masse')?>
               </h3>
             </div>

             <?=$inst_connexion?>

             
            <div class="col-md-3" style="margin-top:3px;">
              <input type="radio" onchange="liste();get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE1" value="1" <?=$ch?>>

              <label><?=lang('messages_lang.trimestre1')?></label>
            </div>
            <div class="col-md-2" style="margin-top:3px;">
              <input type="radio" onchange="liste();get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE2" value="2" <?=$ch1?>>

              <label><?=lang('messages_lang.trimestre2')?></label>
            </div>
            <div class="col-md-2" style="margin-top:3px;">
              <input type="radio" onchange="liste();get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE3" value="3" <?=$ch2?>>

              <label><?=lang('messages_lang.trimestre3')?> </label>
            </div>
            <div class="col-md-2" style="margin-top:3px;">
              <input type="radio" onchange="liste();get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE4" value="4" <?=$ch3?>>

              <label><?=lang('messages_lang.trimestre4')?> </label>
            </div>

            <div class="col-md-3" style="margin-top:3px;"> 
              <input type="radio" onchange="get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE5" value="5" <?=$ch4?>>

              <label><?=lang('messages_lang.label_annuel')?>  </label>
            </div>

            <div class="form-group col-md-2">
              <label><b>Catégorie</b></label>
              <select class="form-control" onchange="liste();get_i()" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
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
                <select class="form-control " onchange="liste();get_m()" name="INSTITUTION_ID" id="INSTITUTION_ID">
                  <option value=""><?=lang('messages_lang.selection_message')?></option>
                </select>        
              </div>
              <div class="form-group col-md-3">
                <label><b><?=lang('messages_lang.titre_entite_pragmatique')?></b></label>
                <select class="form-control" onchange="liste();get_add()" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                  <option value=""><?=lang('messages_lang.selection_message')?></option>
                </select>        
              </div>

              <div class="form-group col-md-3">
                <label><b><a id="program"></a></b></label>
                <select class="form-control" onchange="liste();get_s()" name="PROGRAMME_ID" id="PROGRAMME_ID">
                <option value=""><?=lang('messages_lang.selection_message')?></option>
                </select>        
              </div>

              <div class="form-group col-md-4"id="id_action" > 
                <label><b><?=lang('messages_lang.label_droit_action')?></b></label>
                <select class="form-control" onchange="liste();get_rapport()" onchange="get_act()" name="ACTION_ID" id="ACTION_ID">
                  <option value=""><?=lang('messages_lang.selection_message')?></option> 
                </select>
              </div>

                  <div class="form-group col-md-4">
                    <label><b>Nomenclature</b></label>
                      <select class="form-control" onchange="get_rapport();liste()" onchange="get_activite()" name="LIGNE_BUDGETAIRE" id="LIGNE_BUDGETAIRE">
                      <option value=""><?=lang('messages_lang.selection_message')?></option>
                        </select>
                      </div>

                      <div class="form-group col-md-4">
                        <label><b>Activité</b></label>
                        <select class="form-control" onchange="get_rapport();liste()"  name="PAP_ACTIVITE_ID" id="PAP_ACTIVITE_ID">
                          <option value=""><?=lang('messages_lang.selection_message')?></option>
                        </select>
                      </div>

              <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog" style ="max-width: 65%;">
                  <div class="modal-content  ">
                    <div class="modal-header">
                      <h4 class="modal-title">
                        <span id="titre" style="color: black"></span></h4>
                    </div>
                    <div class="modal-body">
                      <table style="width: 100%;" id='mytable' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                        <thead>
                          <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center></th>
                           <th ><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=strtoupper(lang('messages_lang.labelle_institution'))?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                          <th style='width:70px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_grandes_masses')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                          <th style='width:70px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.detail_montant_vote')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                          <th ><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;<?=strtoupper(lang('messages_lang.th_programme'))?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                          <th ><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;<?=strtoupper(lang('messages_lang.th_activite'))?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                          <th ><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DATE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>

                        </thead>
                      </table>
                      <!-- </div> -->
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal" onclick="saveData()"><?=lang('messages_lang.label_ferm')?></button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal fade" id="myModal1" role="dialog">
                <div class="modal-dialog" style ="max-width: 65%;">
                  <div class="modal-content  ">
                    <div class="modal-header">
                      <h4 class="modal-title"><span id="titre1" style="color: black"></span></h4>
                    </div>
                    <div class="modal-body">
                      <table style="width: 100%;" id='mytable1' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                       <thead>
                        <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center></th>
                        <th style='width:70px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;<?=strtoupper(lang('messages_lang.th_instit'))?>&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                        <th style='width:70px'><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_grandes_masses')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                        <th ><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.montant_execute')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                        <th ><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;<?=strtoupper(lang('messages_lang.label_prog'))?>&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                        <th ><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=strtoupper(lang('messages_lang.th_activite'))?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                        <th ><center><font color="white" size="3"><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DATE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label></font></center></th>
                      </thead>
                    </table>
                    <!-- </div> -->

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="saveData()"><?=lang('messages_lang.label_ferm')?></button>
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
        <div class="col-md-12" style="margin-bottom: 20px"></div>
        <div id="container1"  class="col-md-12" ></div>
        <div class="col-md-12" style="margin-bottom: 20px"></div>
        <div id="container2"  class="col-md-12" ></div>
 
        <div id="nouveau1"></div>
        <div id="nouveau2" ></div>
      </div>
      <div class="row"> 
           <div class="col-md-12" style="margin-bottom: 10px"></div>
           <!-- Début liste -->
           <div class="col-12">
            <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
              <div class="car-body">
                <div class="row col-md-12">
                  <div class="col-md-12">
                    <h5 class="header-title text-black"><?=lang('messages_lang.tb_grande_masse')?> </h5>

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

                          <th>LIGNE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BUDGETAIRE&nbsp;&nbsp;&nbsp;&nbsp;</th>

                          <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.th_activite')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>

                          <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TACHES&nbsp;&nbsp;&nbsp;&nbsp;</th>

                          <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.mont_eng_budg')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>

                          <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.mont_eng_jur')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>

                          <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.mont_liq')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>

                          <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.mont_ord')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>

                          <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.mont_pai')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>

                          <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.mont_dec')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>

                          <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.list_rap_gd_mass')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
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



<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>


<script type="text/javascript">
let table = new DataTable('#myTable');
</script>

<script type="text/javascript">
$( document ).ready(function() {
  liste();
  get_rapport();

});   
function get_i() {

$('#INSTITUTION_ID').html('');
$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#SOUS_TUTEL_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
$('#PAP_ACTIVITE_ID').html('');
get_rapport();

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

}


function get_add() {

$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
$('#PAP_ACTIVITE_ID').html('');

get_rapport();

}
function get_s() {

$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
$('#PAP_ACTIVITE_ID').html('');
get_rapport();
}
function get_act() {

$('#LIGNE_BUDGETAIRE').html('');
$('#PAP_ACTIVITE_ID').html('');
get_rapport();
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
var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();
var inst_conn=$('#inst_conn').val();



$.ajax({
  url : "<?=base_url('dashboard/Get_Dashboard_Grande_Masse')?>",
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
   PAP_ACTIVITE_ID:PAP_ACTIVITE_ID,
   inst_conn:inst_conn,


 },

 success:function(data){
 
  $('#container1').html("");             
  $('#nouveau1').html(data.rapp1);
  $('#container2').html("");             
  $('#nouveau2').html(data.rapp2);

  $('#SOUS_TUTEL_ID').html(data.soustutel);

  $('#INSTITUTION_ID').html(data.inst);
  $('#PROGRAMME_ID').html(data.program);
  $('#ACTION_ID').html(data.actions);
  $('#LIGNE_BUDGETAIRE').html(data.ligne_budgetaires);
  $('#PAP_ACTIVITE_ID').html(data.ligne_activite);

  if (TYPE_INSTITUTION_ID==1)
  {
    $("#idmin").html("<?=lang('messages_lang.admin_perso')?>");
    $("#program").html("Dotations");

  }

  else if (TYPE_INSTITUTION_ID==2)
  {
    $("#idmin").html("<?=lang('messages_lang.minister')?>");
    $("#program").html("Programmes");
  }
  else
  {
    $("#idmin").html("<?=lang('messages_lang.admin_perso')?>/<?=lang('messages_lang.minister')?>");
    $("#program").html("<?=lang('messages_lang.label_prog')?>");
  } 
},            
});  
}
function saveData()
{
$('#myModal1').modal('hide');
$('#myModal_phase').modal('hide');
$('#myModal_masse').modal('hide');
$('#myModal_transfert').modal('hide');
$('#myModal_recu').modal('hide');
}
</script>

<script type="text/javascript">

  function liste(){

 
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
    var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();
   
    
    
    var row_count ="1000000";
    $("#list_table").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":{
        url:"<?= base_url('dashboard/Dashboard_Grande_Masse/listing')?>",
        type:"POST",
        data:{
          TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
          INSTITUTION_ID:INSTITUTION_ID,
          PROGRAMME_ID:PROGRAMME_ID,
          ACTION_ID:ACTION_ID,
          IS_PRIVATE:IS_PRIVATE,
          SOUS_TUTEL_ID:SOUS_TUTEL_ID,
          LIGNE_BUDGETAIRE:LIGNE_BUDGETAIRE,
          PAP_ACTIVITE_ID:PAP_ACTIVITE_ID,
          
          
        },
      },

        lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, "All"]],
        pageLength:5,
        "columnDefs":[{
          "orderable":false
        }],

        dom: 'Bfrtlip',
        order: [],
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
    var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();
  

    if (TYPE_INSTITUTION_ID == '' || TYPE_INSTITUTION_ID == null ) {TYPE_INSTITUTION_ID = 0}
    if (INSTITUTION_ID == '' || INSTITUTION_ID == null ) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null ) {SOUS_TUTEL_ID = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null ) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null ) {ACTION_ID = 0}
    if (LIGNE_BUDGETAIRE == '' || LIGNE_BUDGETAIRE == null ) {LIGNE_BUDGETAIRE = 0}
    if (IS_PRIVATE == '' || IS_PRIVATE == null || IS_PRIVATE == 5) {IS_PRIVATE = 0}
    if (PAP_ACTIVITE_ID == '' || PAP_ACTIVITE_ID == null) {PAP_ACTIVITE_ID = 0}
  
    document.getElementById("btnexport").href = "<?=base_url('dashboard/Dashboard_Grande_Masse/exporter/')?>"+'/'+TYPE_INSTITUTION_ID+'/'+INSTITUTION_ID+'/'+SOUS_TUTEL_ID+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+LIGNE_BUDGETAIRE+'/'+IS_PRIVATE+'/'+PAP_ACTIVITE_ID;
  }
</script>
