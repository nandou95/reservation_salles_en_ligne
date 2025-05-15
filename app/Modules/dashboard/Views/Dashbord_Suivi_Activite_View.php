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
                  <div class="col-md-12">
                    <h1 class=" header-title text-black">Tableau de bord de suivie des activités</h1>

                  </div>

                  <div class="form-group col-md-4">
                    <label><b>Institutions</b></label>
                    <select class="form-control" onchange="get_rapport();liste();liste2()" name="INSTITUTION_ID" id="INSTITUTION_ID">
                      <option value="">sélectionner</option>
                      <?php
                      foreach ($institutions as $value)
                      {
                        if ($value->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                          { ?>
                            <option value="<?= $value->INSTITUTION_ID?>" selected><?=$value->DESCRIPTION_INSTITUTION ?></option>
                            <?php
                          } 
                          else
                          { 
                            ?>
                            <option value="<?=$value->INSTITUTION_ID?>"><?= $value->DESCRIPTION_INSTITUTION ?></option>
                            <?php 
                          } 
                        } 
                        ?>
                      </select>        
                    </div>

                    <div class="form-group col-md-4">
                      <label><b>Programmes</b></label>
                      <select class="form-control " onchange="get_rapport();liste();liste2()" name="PROGRAMME_ID" id="PROGRAMME_ID">
                        
                        <option value="">Sélectionner</option>
                      </select>        
                    </div>

                  </div>      
                  <div></div>  

                  <!-- Rapport graphique -->
                  <div class="row">
                   
                   
                   <div class="col-md-12" style="margin-bottom: 20px"></div>
                   <!-- Début liste -->
                   <div class="col-12">
                    <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                  <div class="car-body">
                    <div id="container"  class="col-md-12"></div> 
                  </div>
                    <div class="car-body">
                     <div id="container1"  class="col-md-12"></div> 
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row"> 
                 <div class="col-md-12" style="margin-bottom: 20px"></div>
                 <!-- Début liste -->
                 <div class="col-12">

                  <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                    <div class="car-body">
                      <div class="row col-md-12">


                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                          <li class="nav-item" role="presentation">
                            <a onclick="show_nav(1)" class="nav-link active" id="faire-tab" data-bs-toggle="tab" data-bs-target="#detailleformation"  role="tab" aria-controls="detailleformation" aria-selected="true"> <i class="fa fa-th-list" aria-hidden="true"></i> <span id="total_faire"></span>  tâches(s) à faire </a>
                          </li>

                          <li class="nav-item" role="presentation">
                            <a onclick="show_nav(2)" class="nav-link" id="fait-tab" data-bs-toggle="tab" data-bs-target="#histotraitement"  role="tab" aria-controls="histotraitement" aria-selected="false"> <i class="fa fa-th-list" aria-hidden="true"></i> <span id="total_fait"></span>  tâches(s) déjà faite(s) </a>
                          </li>

                        </ul>
                        <div id="faire">
                          <div class="col-md-12">
                            <h1 class="header-title text-black">Liste des activités à faire </h1>

                            <a href="#" id="btnexport1" onclick="exporter_activite_faire()" type="button" style="float: center;margin-top: ;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-center"></span><?=lang('messages_lang.pip_rapport_institutio_telecharge')?></a>
                          </div>


                          <div class="table-responsive container " style="margin-top:">
                            <div class="table-responsive" style="width: 100%;">
                              <table id="list_table" class=" table table-striped table-bordered">
                                <thead>
                                  <tr>
                                    <th><center>#</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INSTITUTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PROGAMME&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ACTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ACTIVITE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;MONTANT&nbsp;VOTE&nbsp;T1&nbsp;</center></th>
                                    <th><center>&nbsp;MONTANT&nbsp;VOTE&nbsp;T2&nbsp;</center></th>
                                    <th><center>&nbsp;MONTANT&nbsp;VOTE&nbsp;T3&nbsp;</center></th>
                                    <th><center>&nbsp;MONTANT&nbsp;VOTE&nbsp;T4&nbsp;</center></th>
                                    
                                  </tr>
                                </thead>
                                <tbody>
                                </tbody>
                              </table>
                            </div>
                          </div>

                        </div>



                        <div id="fait">
                          <div class="col-md-12">
                            <h3 class="header-title text-black">Liste des activités déjà faites </h3>

                            <a href="#" id="btnexport2" onclick="exporter_activite_deja_fait()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-center"></span><?=lang('messages_lang.pip_rapport_institutio_telecharge')?></a>
                          </div>

                          
                          <div class="table-responsive container " style="margin-top:">
                            <div class="table-responsive" style="width: 100%;">
                              <table id="mytable-list" class=" table table-striped table-bordered">
                                <thead>
                                  <tr>
                                    <th><center>#</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INSTITUTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PROGAMME&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ACTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ACTIVITE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;MONTANT&nbsp;VOTE&nbsp;T1&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;MONTANT&nbsp;VOTE&nbsp;T2&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;MONTANT&nbsp;VOTE&nbsp;T3&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    <th><center>&nbsp;&nbsp;&nbsp;&nbsp;MONTANT&nbsp;VOTE&nbsp;T4&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                                    

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
        </div>
        <!-- body page end -->
      </div>

    </div>
  </main>
</div>

<!-- footer end -->

<!-- script start -->
<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>


<!-- details du rapport graphique -->
<div class="modal fade" id="myModal" role="dialog">
  <div class="modal-dialog modal-xl" style ="width:100%">
    <div class="modal-content  modal-xl">
      <div class="modal-header">
        <h4 class="modal-title"><span id="titre"></span></h4>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table id='mytable' class='table table-bordered table-striped table-hover table-condensed' style="width:100%">
            <thead>
             <th>&nbsp;#&nbsp;</th>
             <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INSTITUTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
             <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PROGRAMME&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
             
             <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ACTION&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
             <th>&nbsp;&nbsp;&nbsp;ACTIVITE&nbsp;1&nbsp;&nbsp;&nbsp;</th>

             
           </thead>
         </table>
       </div>
     </div>
     <div class="modal-footer">
      <button type="button btn-btn danger" class="btn btn-danger" data-dismiss="modal" onclick="saveData()">Close</button>
    </div>
  </div>
</div>
</div>
<div id="nouveau"></div>
<div id="nouveau1"></div>

<script type="text/javascript">
 $(document).ready(function() { 
  get_rapport();
  liste2();
  liste();

  $('#fait').prop('hidden',true);


});   

</script>
<script type="text/javascript">
  function get_rapport()
      { 
   var INSTITUTION_ID=$('#INSTITUTION_ID').val();
   var PROGRAMME_ID=$('#PROGRAMME_ID').val();
   $.ajax({
    url : "<?=base_url('dashboard/Dashbord_Suivi_Activite/get_rapport')?>",
    type : "POST",
    dataType: "JSON",
    cache:false,
    data:{INSTITUTION_ID:INSTITUTION_ID,
         PROGRAMME_ID:PROGRAMME_ID,
         },
    success:function(data)
       {
      $('#container').html("");             
      $('#nouveau').html(data.rapp);
      $('#container1').html("");             
      $('#nouveau1').html(data.rapp1);
      $('#total_fait').html(data.total_activite_faite); 
      $('#total_faire').html(data.total_activite_faire); 
      $('#PROGRAMME_ID').html(data.program);
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
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var row_count ="1000000";
    $("#list_table").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":{
        url:"<?= base_url('dashboard/Dashbord_Suivi_Activite/listing')?>",
        type:"POST",
        data:{INSTITUTION_ID:INSTITUTION_ID,
          PROGRAMME_ID:PROGRAMME_ID
        },
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

  function liste2(){
    
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var row_count ="1000000";
    $("#mytable-list").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":{
        url:"<?= base_url('dashboard/Dashbord_Suivi_Activite/listing_deux')?>",
        type:"POST",
        data:{PROGRAMME_ID:PROGRAMME_ID,
          INSTITUTION_ID:INSTITUTION_ID
          
        },
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


<script>
  function show_nav(tab) { 

    if (tab==1)
      {
      $('#fait').prop('hidden',true);
      $('#faire').prop('hidden',false);
      $('#fait-tab').prop('class',"nav-link ");
      $('#faire-tab').prop('class',"nav-link active");
      }
    else if (tab==2)
       {
      $('#fait').prop('hidden',false);
      $('#faire').prop('hidden',true);
      $('#fait-tab').prop('class',"nav-link active");
      $('#faire-tab').prop('class',"nav-link ");


    }

  }
</script>

<script type="text/javascript">
  function get_lieux(id) {

    var id =id;

    $("#modal_des_pads").modal('show');

    var row_count = "1000000";
    table = $("#table_pad").DataTable({
      "processing": true,
      "destroy": true,
      "serverSide": true,
      "oreder": [
      [2, 'desc']
      ],
      "ajax": {
        
       url:"<?= base_url('dashboard/Dashbord_Suivi_Activite/get_lieux')?>",
       type: "POST",
       data: {id:id}
     },
     lengthMenu: [
     [10, 50, 100, row_count],
     [10, 50, 100, "All"]
     ],
     pageLength: 10,
     "columnDefs": [{
      "targets": [],
      "orderable": false
    }],

    dom: 'Bfrtlip',
    buttons: [
    'copy', 'csv', 'excel', 'pdf', 'print'
    ],
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
  }

</script>


<script type="text/javascript">
  function exporter_activite_faire()
  {
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    if (PROGRAMME_ID == ''  || PROGRAMME_ID == null)
    {
      PROGRAMME_ID = 0
    }

    if (INSTITUTION_ID == ''  || INSTITUTION_ID == null)
    {
      INSTITUTION_ID = 0
    }
   

    document.getElementById("btnexport1").href = "<?=base_url('dashboard/Dashbord_Suivi_Activite/exporter_activite_faire/')?>"+'/'+PROGRAMME_ID+'/'+INSTITUTION_ID;
  }

  function exporter_activite_deja_fait()
  {
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    if (PROGRAMME_ID == ''  || PROGRAMME_ID == null)
    {
      PROGRAMME_ID = 0
    }

    if (INSTITUTION_ID == ''  || INSTITUTION_ID == null)
    {
      INSTITUTION_ID = 0
    }

    document.getElementById("btnexport2").href = "<?=base_url('dashboard/Dashbord_Suivi_Activite/exporter_activite_deja_fait/')?>"+'/'+PROGRAMME_ID+'/'+INSTITUTION_ID;
  }
</script>