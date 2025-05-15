<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
            <h1 class="header-title text-white">

            </h1>
          </div>
          <form id="myform" action="" method="POST">
            <div class="row">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                  <div class="car-body">
                    <h1 class="header-title text-black"><?=lang('messages_lang.title_classification_fonctionnelle')?></h1>                   

                    <div class="row">
                      <div class="col-3">
                        <label><?=lang('messages_lang.label_division')?></label>
                        <select class="form-control" name="DIVISION_ID" id="DIVISION_ID" value="<?=set_value('DIVISION_ID')?>" onchange="get_dep();liste()">
                          <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          <?php foreach ($division as $value) { ?>  
                            <option value="<?=$value->DIVISION_ID ?>"><?= $value->LIBELLE_DIVISION ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-3">
                        <label><?=lang('messages_lang.label_groupe')?></label>
                        <select class="form-control" name="GROUPE_ID" id="GROUPE_ID" onchange="get_dep();liste()">
                          <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                        </select>
                      </div>

                      <div class="col-3">
                        <label><?=lang('messages_lang.label_classe')?></label>
                        <select class="form-control" name="CLASSE_ID" id="CLASSE_ID" onchange="liste()">
                          <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                        </select>
                      </div>

                      <div class="col-md-3">
                        <label><?=lang('messages_lang.select_anne_budget')?></label>
                        <select class="form-control" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID" onchange="liste(this.value);get_date_limit(this.value);" >
                          <?php
                          foreach($anne_budgetaire as $key_anne)
                          {
                            if($key_anne->ANNEE_BUDGETAIRE_ID==$ANNEE_BUDGETAIRE_ID)
                            {
                              echo "<option value='".$key_anne->ANNEE_BUDGETAIRE_ID."'  selected>".$key_anne->ANNEE_DESCRIPTION."</option>";
                            }
                            else
                            {
                              echo "<option value='".$key_anne->ANNEE_BUDGETAIRE_ID."' >".$key_anne->ANNEE_DESCRIPTION."</option>";
                            }
                          }
                          ?>
                        </select>
                      </div>

                      <div class="col-md-3">
                        <label class="form-label"><?=lang('messages_lang.label_trimestre')?> </label>
                        <select class="form-control" name="TRIMESTRE_ID" id="TRIMESTRE_ID" onchange="liste(this.value);get_date_limit(this.value);">
                          <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          <?php foreach ($trimestre as $value){ ?>
                            <option value="<?=$value->TRIMESTRE_ID ?>"><?= $value->DESC_TRIMESTRE ?></option>
                          <?php } ?>
                        </select>
                      </div>

                      <div class="col-md-3">
                        <label class="form-label"><?=lang('messages_lang.date_debut_du')?>:</label>
                        <input type="date" name="DATE_DEBUT" id="DATE_DEBUT" onchange="liste();get_date()" max="<?=date('Y-m-d')?>" class="form-control">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label"><?=lang('messages_lang.date_fin_au')?>:</label>
                        <input type="date" name="DATE_FIN" id="DATE_FIN" onchange="liste()" max="<?=date('Y-m-d')?>" class="form-control">
                      </div>
                      <div class="col-md-12" style="float: right;">

                       <a href="#" id="btnexport" onclick="exporter()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span>Excel</a>
                       <a href="#" id="btnexport_pdf" onclick="exporter_pdf()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span>PDF</a>
                       <a href="#" id="btnexport_wrd" onclick="exporter_word()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span>Word</a>
                       </div>

                     </div>
                     <br>
                     <div class="table-responsive" style="width: 100%;">

                      <table id="mytable" class="table table-striped table-bordered">
                        <thead>
                          <tr>
                            <!-- <th><center>#</center></th> -->
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.labelle_intitule_division')?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.labelle_intitule_groupe')?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.labelle_intitule_classe')?>&nbsp;&nbsp;&nbsp;&nbsp;</th>
                            <th id="total" class="text-uppercase" style="white-space: nowrap;" ></th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_transfert_credit')?></th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_credit_transfert')?></th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_engag_budg')?></th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_engag_jurd')?></th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_liquidation')?></th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_ordonancement')?></th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_paiement')?></th>
                            <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_decaissement')?></th>
                          </tr>
                        </thead>
                      </table>
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

    <script>
      $(document).ready(function()
      {
        $('#total').html('<center><?=lang('messages_lang.th_programmation_financiere')?></center>');
        liste();
        get_date_limit();

      });
    </script>
    <script type="text/javascript">
      function get_date(){ 
        $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
      }
    </script>

    <script type="text/javascript">
      function liste()
      {
        var DIVISION_ID = $('#DIVISION_ID').val();
        var GROUPE_ID = $('#GROUPE_ID').val();
        var CLASSE_ID = $('#CLASSE_ID').val();
        var TRIMESTRE_ID = $('#TRIMESTRE_ID').val();
        var DATE_DEBUT = $('#DATE_DEBUT').val();
        var DATE_FIN = $('#DATE_FIN').val();
        var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();

        if (TRIMESTRE_ID==1)
        {
          $('#total').html('<center>&emsp;&emsp;&emsp;T1&emsp;&emsp;&emsp;</center>');
        }else if (TRIMESTRE_ID==2)
        {
          $('#total').html('<center>&emsp;&emsp;&emsp;T2&emsp;&emsp;&emsp;</center>');
        }else if (TRIMESTRE_ID==3)
        {
          $('#total').html('<center>&emsp;&emsp;&emsp;T3&emsp;&emsp;&emsp;</center>')
        }else if (TRIMESTRE_ID==4)
        {
          $('#total').html('<center>&emsp;&emsp;&emsp;T4&emsp;&emsp;&emsp;</center>')
        }else{
          $('#total').html('<center><?=lang('messages_lang.th_programmation_financiere')?></center>');
        }

        var row_count ="1000000";
        $("#mytable").DataTable({
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "targets":[0,12],
          "oreder":[[ 0, 'desc' ]],
          "ajax":{
            url:"<?= base_url('/ihm/Fonctionnel/listing')?>",
            type:"POST", 
            data:
            {
              DIVISION_ID:DIVISION_ID,
              GROUPE_ID:GROUPE_ID,
              CLASSE_ID:CLASSE_ID,
              TRIMESTRE_ID: TRIMESTRE_ID,
              DATE_DEBUT:DATE_DEBUT,
              DATE_FIN:DATE_FIN,
              ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID
            },
          },
          lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
          pageLength: 10,
          "columnDefs":[{
            "targets":[],
            "orderable":false
          }],

          dom: 'Bfrtlip',
          order: [2,'desc'],
          buttons: [  ],
          language: {
            "sProcessing": "<?= lang('messages_lang.labelle_et_traitement') ?>",
            "sSearch": "<?= lang('messages_lang.search_button') ?>&nbsp;:",
            "sLengthMenu": "<?= lang('messages_lang.sLengthMenu_enjeux') ?>",
            "sInfo": "<?= lang('messages_lang.sInfo_enjeux') ?>",
            "sInfoEmpty": "<?= lang('messages_lang.sInfo_enjeux_0') ?>",
            "sInfoFiltered": "(<?= lang('messages_lang.filtre_max_total_enjeux') ?>)",
            "sInfoPostFix": "",
            "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
            "sZeroRecords": "<?= lang('messages_lang.aucun_element_afficher_enjeux') ?>",
            "sEmptyTable": "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
            "oPaginate": {
              "sFirst": "<?= lang('messages_lang.labelle_1') ?>",
              "sPrevious": "<?= lang('messages_lang.btn_precedent') ?>",
              "sNext": "<?= lang('messages_lang.btn_suivant') ?>",
              "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
            },
            "oAria": {
              "sSortAscending": "<?= lang('messages_lang.sSortAscending_enjeux') ?>",
              "sSortDescending": "<?= lang('messages_lang.sSortDescending_enjeux') ?>"
            }
          }

        });
      }
    </script>

    <script type="text/javascript">
      function get_dep()
      {
        var DIVISION_ID = $('#DIVISION_ID').val();
        var CLASSE_ID = $('#CLASSE_ID').val();
        var GROUPE_ID = $('#GROUPE_ID').val();

        $.ajax({
          url : "<?=base_url('/ihm/Fonctionnel/get_dep')?>",
          type : "POST",
          dataType: "JSON",
          cache:false,
          data:{
            DIVISION_ID:DIVISION_ID,
            CLASSE_ID:CLASSE_ID,
            GROUPE_ID:GROUPE_ID

          },
          success:function(data){       

            $('#GROUPE_ID').html(data.groupe);
            $('#CLASSE_ID').html(data.classe);

          },            

        });  
      }
    </script>


    <script type="text/javascript">
      function exporter()
      {
        var DIVISION_ID = $('#DIVISION_ID').val();
        var GROUPE_ID = $('#GROUPE_ID').val();
        var CLASSE_ID = $('#CLASSE_ID').val();
        var TRIMESTRE_ID = $('#TRIMESTRE_ID').val();
        var DATE_DEBUT = $('#DATE_DEBUT').val();
        var DATE_FIN = $('#DATE_FIN').val();
        var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();

        if (DIVISION_ID == '' || DIVISION_ID == null) {DIVISION_ID = 0}
        if (GROUPE_ID == '' || GROUPE_ID == null) {GROUPE_ID = 0}
        if (CLASSE_ID == '' || CLASSE_ID == null) {CLASSE_ID = 0}
        if (TRIMESTRE_ID == '' || TRIMESTRE_ID == null) {TRIMESTRE_ID = 0}
        if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
        if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}
        if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}

          document.getElementById("btnexport").href = "<?=base_url('ihm/Fonctionnel/exporter/')?>"+'/'+DIVISION_ID+'/'+GROUPE_ID+'/'+CLASSE_ID+'/'+TRIMESTRE_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+ANNEE_BUDGETAIRE_ID;
      }
    </script>

    <script type="text/javascript">
     function getinstution()
     {
      var INSTITUTION_ID=$('#INSTITUTION_ID').val();

      $.ajax({
        url : "<?=base_url('ihm/Fonctionnel/getinstution')?>",
        type: "POST",
        dataType: "JSON",
        data: {INSTITUTION_ID:INSTITUTION_ID},
        success: function(data)
        {
          if (data.TYPE_INSTITUTION_ID==1) {
            $('#label_programme').text('Dotation');
            document.getElementById('div_action').style.display="none";
          }else if (data.TYPE_INSTITUTION_ID==2) {
            $('#label_programme').text('Programme');
            document.getElementById('div_action').style.display="block";
          }
        }
      });
    }
    </script>

    <script type="text/javascript">
      function exporter_word()
      {
        var DIVISION_ID = $('#DIVISION_ID').val();
        var GROUPE_ID = $('#GROUPE_ID').val();
        var CLASSE_ID = $('#CLASSE_ID').val();
        var TRIMESTRE_ID = $('#TRIMESTRE_ID').val();
        var DATE_DEBUT = $('#DATE_DEBUT').val();
        var DATE_FIN = $('#DATE_FIN').val();
        var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();

        if (DIVISION_ID == '' || DIVISION_ID == null) {DIVISION_ID = 0}
        if (GROUPE_ID == '' || GROUPE_ID == null) {GROUPE_ID = 0}
        if (CLASSE_ID == '' || CLASSE_ID == null) {CLASSE_ID = 0}
        if (TRIMESTRE_ID == '' || TRIMESTRE_ID == null) {TRIMESTRE_ID = 0}
        if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
        if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}
        if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}

        document.getElementById("btnexport_wrd").href = "<?=base_url('ihm/Fonctionnel/exporter_word/')?>"+'/'+DIVISION_ID+'/'+GROUPE_ID+'/'+CLASSE_ID+'/'+TRIMESTRE_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+ANNEE_BUDGETAIRE_ID;
      }
    </script>

    <script type="text/javascript">
      function exporter_pdf()
      {
        var DIVISION_ID = $('#DIVISION_ID').val();
        var GROUPE_ID = $('#GROUPE_ID').val();
        var CLASSE_ID = $('#CLASSE_ID').val();
        var TRIMESTRE_ID = $('#TRIMESTRE_ID').val();
        var DATE_DEBUT = $('#DATE_DEBUT').val();
        var DATE_FIN = $('#DATE_FIN').val();
        var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();

        if (DIVISION_ID == '' || DIVISION_ID == null) {DIVISION_ID = 0}
        if (GROUPE_ID == '' || GROUPE_ID == null) {GROUPE_ID = 0}
        if (CLASSE_ID == '' || CLASSE_ID == null) {CLASSE_ID = 0}
        if (TRIMESTRE_ID == '' || TRIMESTRE_ID == null) {TRIMESTRE_ID = 0}
        if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
        if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}
        if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}

        document.getElementById("btnexport_pdf").href = "<?=base_url('ihm/Fonctionnel/exporter_pdf/')?>"+'/'+DIVISION_ID+'/'+GROUPE_ID+'/'+CLASSE_ID+'/'+TRIMESTRE_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+ANNEE_BUDGETAIRE_ID;
      }
    </script>

    <script type="text/javascript">
      function get_date_limit()
      {
        var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
        var TRIMESTRE_ID = $('#TRIMESTRE_ID').val();

        $.ajax({
          url: "<?=base_url('')?>/ihm/Fonctionnel/get_date_limit",
          type: "POST",
          dataType: "JSON",
          data: {
            ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID,
            TRIMESTRE_ID:TRIMESTRE_ID
          },
          success: function(data) {

            $("#DATE_DEBUT").prop('min',data.datedebut);
            $("#DATE_DEBUT").prop('max',data.datefin);
            $("#DATE_FIN").prop('min',data.datedebut);
            $("#DATE_FIN").prop('max',data.datefin);
          }
        });

      }
    </script>