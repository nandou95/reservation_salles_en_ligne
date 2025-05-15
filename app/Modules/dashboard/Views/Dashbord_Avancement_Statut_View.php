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
        Avancement des engagements
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
                   <div class="form-group col-md-6"> 
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
                    

         <div class="row">
        <div class="col-md-12" style="margin-bottom: 20px"></div>
        <div id="container"  class="col-md-12" style="height: 35em"></div>
        <div id="container2"  class="col-md-12" style="height: 35em"></div> 
        <div class="col-md-12" style="margin-bottom: 20px"></div>
        </div>
               

              <div class="row"> 
               <div class="col-md-12" style="margin-bottom: 10px"></div>
               <!-- Début liste -->
               <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                  <div class="car-body">
                    <div class="row col-md-12">
                      <div class="col-md-12">
                        <h5 class="header-title text-black">Liste des engagements </h5>

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
                        <th>#</th>
                        <th><?=lang('messages_lang.labelle_institutio')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th>AXE&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th><?=lang('messages_lang.th_programme')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th><?=lang('messages_lang.th_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>

                        <th>LIGNE&nbsp&nbsp&nbsp&nbsp&nbsp&nbspBUDGETAIRE&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>

                        <th><?=lang('messages_lang.th_activite')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th>TACHES&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th>BUDGET&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspVOTE&nbsp&nbsp&nbsp&nbsp</th>
                        <th>ENGAGEMENT&nbsp&nbsp&nbsp&nbsp&nbspBUDGETAIRE&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th>ENGAGEMENT&nbsp&nbsp&nbsp&nbsp&nbspJURIDIQUE&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th>LIQUIDATION&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th>ORDONNANCEMENT&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th>PAIEMENT&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
                        <th>DECAISSEMENT&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
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
 <div id="nouveau"></div>
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
 $(document).ready(function() {
    // alert();
    liste();
    get_rapport();

    
  });   

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
   var inst_conn=$('#inst_conn').val();
   var INSTITUTION_ID=$('#INSTITUTION_ID').val();
   var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();

   $.ajax({
    url : "<?=base_url('dashboard/Dashbord_Avancement_Statut/get_rapport')?>",
    type : "POST",
    dataType: "JSON",
    cache:false,
    data:{
     inst_conn:inst_conn,
     INSTITUTION_ID:INSTITUTION_ID,
     IS_PRIVATE:IS_PRIVATE,
     ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID,
    },
    success:function(data){
      $('#container').html("");             
      $('#nouveau').html(data.rapp);

      $('#container2').html("");             
      $('#nouveau2').html(data.rapp2);
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

    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    
    
    var row_count ="1000000";
    $("#list_table").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":{
        url:"<?= base_url('dashboard/Dashbord_Avancement_Statut/listing')?>",
        type:"POST",
        data:{
          IS_PRIVATE:IS_PRIVATE,
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
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
      
    if (IS_PRIVATE == '' || IS_PRIVATE == null) {IS_PRIVATE = 5}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}
      document.getElementById("btnexport").href = "<?=base_url('dashboard/Dashbord_Avancement_Statut/exporter/')?>"+'/'+IS_PRIVATE+'/'+ANNEE_BUDGETAIRE_ID;
  }
</script>