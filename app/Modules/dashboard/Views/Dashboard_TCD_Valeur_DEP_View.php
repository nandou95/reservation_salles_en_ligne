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
       Tableau croisé dynamique en valeur avec taux sur budget voté
        </div>
                </h1>
       <?=$inst_connexion?>
        <div class="col-md-2" style="margin-top:35px;"> 
        <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE1" value="1" <?=$ch?>>

          <label>1er trimestre </label>
        </div>
        <div class="col-md-3" style="margin-top:35px;">
          <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE2" value="2" <?=$ch1?>>

          <label>2ème trimestre </label>
        </div>
        <div class="col-md-2" style="margin-top:35px;">
          <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE3" value="3" <?=$ch2?>>

          <label>3ème trimestre </label>
        </div>
        <div class="col-md-3" style="margin-top:35px;">
          <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE4" value="4" <?=$ch3?>>

          <label>4ème trimestre </label>
        </div>
        <div class="col-md-2" style="margin-top:35px;">
          <input type="radio" onchange="get_rapport();liste_programme()" name="IS_PRIVATE" id="IS_PRIVATE5" value="5" >

          <label>Annuel</label>
        </div>
                    <div class="form-group col-md-4">
                      <label><b>Catégorie</b></label>
                      <select class="form-control" onchange="get_i();liste_programme()" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                        
                        <option value="">sélectionner</option>

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
                        <option value="">Sélectionner</option>
                      </select>        
                    </div>

                    <div class="form-group col-md-4">
                      <label><b>Entités responsable</b></label>
                      <select class="form-control" onchange="get_add();liste_programme()" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
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
                               <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center></th>
                              <th style='width:90px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspActivités&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                              <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspRésultats&nbspattendus&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspActions&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                                <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspProgrammes&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:50px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspInstitution&nbspou&nbspMinistère&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label><a id="trim"></a></label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<a id="idpro"></a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspDate&nbsp&nbsp&nbsp&nbsp&nbspengagement&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspTaux&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                               <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspObservation&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                        
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
              </div></div>
            
                <div class="row">
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                <div id="container2"  class="col-md-12"></div>
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                </div>
                <div class="row">
                <div class="table-responsive container " style="margin-bottom: 20px">
                 <div  class="container" style= "width:95%">
                  <h3 class="text-black">Liste des entités responsables</h3><br>
                     
                      <table id="mytable1" class="table table-bordered" style="width:100%">
                        <thead>
                          <tr>
                          <th>#</th>
                          <th>MINISTERES/ADMINISTRATIONS&nbsp;PERSONNALISEES</th>
                          <th>ENTITES&nbsp;RESPONSABLE</th>
                          <th>MONTANT&nbsp;VOTE</th>       
                          <th>ENGAGEMENT&nbsp;BUDGETAIRE</th>
                          <th>ENGAGEMENT&nbsp;JURIDIQUE</th>
                          <th>LIQUIDATION&nbsp;</th>
                          <th>ORDONNANCEMENT</th>
                          <th>PAIEMENT</th>
                          <th>DECAISSEMENT</th>
                          <th>TAUX&nbsp;ENGAGEMENT</th>
                          <th>OBSERVATION&nbsp;ENGAGEMENT</th>
                          <th>TAUX&nbsp;JURIDIQUE</th>
                          <th>OBSERVATION&nbsp;JURIDIQUE</th>
                          <th>TAUX&nbsp;LIQUIDATION</th>
                          <th>OBSERVATION&nbsp;LIQUIDATION</th>
                          <th>TAUX&nbsp;ORDONNANCEMENT</th>
                          <th>OBSERVATION&nbsp;ORDONNANCEMENT</th>
                          <th>TAUX&nbsp;PAIEMENT</th>
                          <th>OBSERVATION&nbsp;PAIEMENT</th>
                          <th>TAUX&nbsp;DECAISSEMENT</th>
                          <th>OBSERVATION&nbsp;DECAISSEMENT</th>
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
$('#ACTIVITE').html('');

 get_rapport();
 liste_programme();
   
}
</script>
<script type="text/javascript">
function get_m() {
  $('#SOUS_TUTEL_ID').html('');
  $('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#ACTIVITE').html('');
    get_rapport();
    5
   
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
$('#ACTIVITE').html('');
    get_rapport();
    liste_programme();
   
}
function get_act() {

$('#ACTIVITE').html('');
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
    var ACTIVITE=$('#ACTIVITE').val();
    var inst_conn=$('#inst_conn').val();
    $.ajax({
      url : "<?=base_url('dashboard/Dashboard_TCD_Valeur_DEP/get_Performence_Excution_Vote')?>",
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
       ACTIVITE:ACTIVITE,
       inst_conn:inst_conn,

     },

     success:function(data){
      $('#container').html("");             
      $('#container1').html("");
      $('#nouveau').html(data.rapp2);
      $('#INSTITUTION_ID').html(data.inst);
      $('#PROGRAMME_ID').html(data.program);
      $('#SOUS_TUTEL_ID').html(data.soustutel);
      $('#ACTION_ID').html(data.actions);
      $('#ACTIVITE').html(data.activite);

      
      
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

    $("#mytable1").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url:"<?= base_url('dashboard/Dashboard_TCD_Valeur_DEP/get_Performence_Liste_Execution')?>",
        type: "POST",
        data: {IS_PRIVATE:IS_PRIVATE,
              TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
              INSTITUTION_ID:INSTITUTION_ID,
              SOUS_TUTEL_ID:SOUS_TUTEL_ID
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
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
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
  };
</script>
  