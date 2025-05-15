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
       <?=lang('messages_lang.dashboard_dynamiq')?>
        </div>
       </h1>
       <?=$inst_connexion?>
        <div class="col-md-2" style="margin-top:35px;"> 
        <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE1" value="1" <?=$ch?>>
          <label><?=lang('messages_lang.trimestre1')?> </label>
        </div>
        <div class="col-md-3" style="margin-top:35px;">
          <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE2" value="2" <?=$ch1?>>
          <label><?=lang('messages_lang.trimestre2')?> </label>
        </div>
        <div class="col-md-2" style="margin-top:35px;">
          <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE3" value="3" <?=$ch2?>>
          <label><?=lang('messages_lang.trimestre3')?></label>
        </div>
        <div class="col-md-3" style="margin-top:35px;">
          <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE4" value="4" <?=$ch3?>>
          <label><?=lang('messages_lang.trimestre4')?> </label>
        </div>
        <div class="col-md-2" style="margin-top:35px;">
          <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE5" value="5" >

          <label><?=lang('messages_lang.label_annuel')?> </label>
        </div>
        <div class="form-group col-md-2">
          <label><b>Catégorie</b></b></label>
          <select class="form-control" onchange="get_i();liste_programme()" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">

            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>

            <?php
            foreach ($type_ministre as $value)
            {
              if ($value->TYPE_INSTITUTION_ID==$type_connect)
              {
                ?>
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
          <select class="form-control" onchange="get_m();liste_programme()" name="INSTITUTION_ID" id="INSTITUTION_ID">
            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
          </select>        
        </div>

        <div class="form-group col-md-3">
          <!-- <label><b>Entités responsable</b></label> -->
          <label><b><?=lang('messages_lang.entit_respo')?></b></label>
          <select class="form-control" onchange="get_add();liste_programme()" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
            <option value=""><label><?=lang('messages_lang.labelle_selecte')?></label></option>
          </select>        
        </div>
        <div class="form-group col-md-3">
          <label><b><a id="program"></a></b></label>
          <select class="form-control" onchange="get_s()" name="PROGRAMME_ID" id="PROGRAMME_ID">
            <option value=""><?=lang('messages_lang.selection_message')?></option>
          </select>        
        </div>
        <div class="form-group col-md-4">
          <label><b><?=lang('messages_lang.label_droit_action')?></b></label>
          <select class="form-control" onchange="get_rapport();liste_programme()" onchange="get_act()" name="ACTION_ID" id="ACTION_ID">
            <option value=""><?=lang('messages_lang.selection_message')?></option>
          </select>
        </div>
         <div class="form-group col-md-4">
          <label><b>Nomenclature</b></label>
          <select class="form-control" onchange="get_rapport();liste_programme()" onchange="get_activite()" name="LIGNE_BUDGETAIRE" id="LIGNE_BUDGETAIRE">
            <option value=""><?=lang('messages_lang.selection_message')?></option>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label><b>Activité</b></label>
          <select class="form-control" onchange="get_rapport();liste_programme()"  name="PAP_ACTIVITE_ID" id="PAP_ACTIVITE_ID">
            <option value=""><?=lang('messages_lang.selection_message')?></option>
          </select>
        </div>

      <!--   <div class="form-group col-md-3"> 
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
                   <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center>
                     <th style='width:100px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspINSTITUTIONS&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                     <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_programme')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                     <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_resultant_attendus')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                     <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                     <th style='width:120px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_activites')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                     <th style='width:110px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a id="idpro"></a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                     <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.table_date_engag')?>&nbsp&nbsp&nbsp</label></font></center></th>
                     <th style='width:70px'><center><font color="white" size="3"><label><a id="trim"></a></label></font></center></th>
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
    <div id="container2"  class="col-md-12"></div>
    <div class="col-md-12" style="margin-bottom: 20px"></div>
  </div>
  <div class="row">
    <div class="table-responsive container " style="margin-bottom: 20px">
      <div  class="container" style= "width:95%">
        <h2 class="text-black"><?=lang('messages_lang.liste_entit_respo')?></h2><br>
        <div style ="max-width: 15%;">
          <a href="#" id="btnexport" onclick="exporter()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-center"></span><?=lang('messages_lang.pip_rapport_institutio_telecharge')?>
        </a>
      </div>
      <table id="mytable1" class="table table-bordered" style="width:100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Institutions&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th> 
            <th><?=lang('messages_lang.labelle_programme')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
            <th><?=lang('messages_lang.labelle_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
             <th>Ligne&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspBudgétaire</th>
               <th><?=lang('messages_lang.labelle_activites')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
               <th>Tâches&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
            <th><?=lang('messages_lang.labelle_eng_budget')?></th>
            <th><?=lang('messages_lang.labelle_eng_jud')?></th>
            <th><?=lang('messages_lang.labelle_liquidation')?></th>
            <th><?=lang('messages_lang.labelle_ordonan')?></th>
            <th><?=lang('messages_lang.labelle_paiement')?></th>
            <th><?=lang('messages_lang.labelle_decaisse')?></th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>
</div>
<div id="nouveau"></div>
<div id="nouveau1"></div>
<div id="nouveau2"></div>
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
$('#PAP_ACTIVITE_ID').html('');
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
  $('#PAP_ACTIVITE_ID').html('');
    get_rapport();
    liste_programme();
}

function get_add()
{
$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#PAP_ACTIVITE_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
   liste_programme();
}

function get_s()
{
$('#ACTION_ID').html('');
$('#PAP_ACTIVITE_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
    liste_programme();
   
}
function get_act()
{
$('#PAP_ACTIVITE_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
   liste_programme();
}
function get_activite() 
  {
    $('#PAP_ACTIVITE_ID').html('');
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
}else if (document.getElementById('IS_PRIVATE5').checked) {
 var IS_PRIVATE = document.getElementById('IS_PRIVATE5').value;
}
// alert(IS_PRIVATE);
    var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();
    var LIGNE_BUDGETAIRE=$('#LIGNE_BUDGETAIRE').val();
    var inst_conn=$('#inst_conn').val();
    var IS_DOUBLE_COMMANDE=$('#IS_DOUBLE_COMMANDE').val();
    $.ajax({
      url : "<?=base_url('dashboard/Dashboard_TCD_Valeur_Engagement_Vote/get_Excution_engage_vote')?>",
      type : "GET",
      dataType: "JSON",
      cache:false,
      data:{
       TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
       INSTITUTION_ID:INSTITUTION_ID,
       SOUS_TUTEL_ID:SOUS_TUTEL_ID,
       PROGRAMME_ID:PROGRAMME_ID,
       ACTION_ID:ACTION_ID,
       IS_PRIVATE:IS_PRIVATE,
       LIGNE_BUDGETAIRE:LIGNE_BUDGETAIRE,
       PAP_ACTIVITE_ID:PAP_ACTIVITE_ID,
       inst_conn:inst_conn,
       IS_DOUBLE_COMMANDE:IS_DOUBLE_COMMANDE,

     },

     success:function(data){
      $('#container').html("");             
      $('#container1').html("");
      $('#nouveau').html(data.rapp2);
      $('#INSTITUTION_ID').html(data.inst);
      $('#PROGRAMME_ID').html(data.program);
      $('#SOUS_TUTEL_ID').html(data.soustutel);
      $('#ACTION_ID').html(data.actions);
      $('#LIGNE_BUDGETAIRE').html(data.ligne_budgetaires);
      $('#PAP_ACTIVITE_ID').html(data.ligne_activite);

      if (TYPE_INSTITUTION_ID==1) {
        $("#idmin").html("<?=lang('messages_lang.admin_perso')?>");
        $("#program").html("Dotations");
        // id_action.style.display='block';
         }
      else if (TYPE_INSTITUTION_ID==2) {
        $("#idmin").html("<?=lang('messages_lang.minister')?>");
        $("#program").html("Programmes");
        // id_action.style.display='block';
      }else{
        $("#idmin").html("<?=lang('messages_lang.admin_perso')?>/<?=lang('messages_lang.minister')?>");
        // id_action.style.display='block';
        $("#program").html("Programmes");
      } 
      
    },            
  });  
  }
  function saveData()

  {

   $('#myModal').modal('hide');
 } 
    
</script>

<script>

  function liste_programme()
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
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var LIGNE_BUDGETAIRE=$('#LIGNE_BUDGETAIRE').val();
    var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();

    $("#mytable1").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url:"<?= base_url('dashboard/Dashboard_TCD_Valeur_Engagement_Vote/liste_institution_engage_vote')?>",
        type: "POST",
        data: {IS_PRIVATE:IS_PRIVATE,
              TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
              INSTITUTION_ID:INSTITUTION_ID,
              SOUS_TUTEL_ID:SOUS_TUTEL_ID,
              LIGNE_BUDGETAIRE:LIGNE_BUDGETAIRE,
              PAP_ACTIVITE_ID:PAP_ACTIVITE_ID,
              ACTION_ID:ACTION_ID,
              PROGRAMME_ID:PROGRAMME_ID,
            },
        beforeSend: function() {}
      },
      lengthMenu: [
      [5,10, 50, 100, -1],
      [5,10, 50, 100, "All"]
      ],
      pageLength: 5,
      "columnDefs": [{
        "targets": [],
        "orderable": false
      }],
      dom: 'Bfrtlip',
      order:[1,'desc'],
      buttons: ['excel', 'pdf'],
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
    });
  };
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
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var LIGNE_BUDGETAIRE=$('#LIGNE_BUDGETAIRE').val();
    var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();
    var ACTION_ID=$('ACTION_ID').val();
    var PROGRAMME_ID=$('PROGRAMME_ID').val();
    var inst_conn=$('#inst_conn').val();

    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (TYPE_INSTITUTION_ID == '' || TYPE_INSTITUTION_ID == null) {TYPE_INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (LIGNE_BUDGETAIRE == '' || LIGNE_BUDGETAIRE == null) {LIGNE_BUDGETAIRE = 0}
    if (PAP_ACTIVITE_ID == '' || PAP_ACTIVITE_ID == null) {PAP_ACTIVITE_ID = 0}
    if (IS_PRIVATE == '' || IS_PRIVATE == null) {IS_PRIVATE = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
   
    

    document.getElementById("btnexport").href = "<?=base_url('dashboard/Dashboard_TCD_Valeur_Engagement_Vote/exporter/')?>"+'/'+TYPE_INSTITUTION_ID+'/'+INSTITUTION_ID+'/'+SOUS_TUTEL_ID+'/'+IS_PRIVATE+'/'+PAP_ACTIVITE_ID+'/'+LIGNE_BUDGETAIRE+'/'+PROGRAMME_ID+'/'+ACTION_ID;
     }
</script>