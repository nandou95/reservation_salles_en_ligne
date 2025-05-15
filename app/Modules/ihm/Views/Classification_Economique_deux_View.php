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
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="car-body">
                  <h1 class="header-title text-black"><?=lang('messages_lang.title_classification_economique')?></h1><br>
                  <?= session()->getFlashdata('message') ?>
                  <div class="table-responsive container " style="margin-top:">
                    <form action="<?= base_url('ihm/Classification_Economique_deux/filterData');?>" id="Myform" method="POST" enctype="multipart/form-data">
                      <div class="card-body">
                        <div class="row">
                          <!-- filtre niveau 2 -->
                          <div class="col-md-4">
                            <label class="form-label"><?=lang('messages_lang.label_institution')?></label>
                            <select onchange="get_dep();listing();getinstution()" class="form-control" id="INSTITUTION_ID" name="INSTITUTION_ID">
                              <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                              <?php
                              foreach($institution as $keyinstitution)
                              {
                                ?>
                                <option value="<?=$keyinstitution->INSTITUTION_ID?>"><?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                <?php
                              }
                              ?>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label id="label_programme" class="form-label"><?=lang('messages_lang.labelle_programme')?></label>
                            <select onchange="get_dep();listing()" class="form-control" id="PROGRAMME_ID" name="PROGRAMME_ID">
                              <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label class="form-label"><?=lang('messages_lang.labelle_grandes_masses')?> </label>
                            <select onchange="listing()" class="form-control" id="GRANDE_MASSE_ID" name="GRANDE_MASSE_ID">
                              <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                              
                            </select>
                          </div>
                          <div class="col-md-4" id="">
                            <label for="Nom" class="form-label"><?=lang('messages_lang.select_anne_budget')?></label>
                            <select class="form-control" onchange="listing();get_date_limit();" class="form-control" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID">
                              <?php
                              foreach($annee_budgetaire as $key)
                              {
                                if($key->ANNEE_BUDGETAIRE_ID==set_value('ANNEE_BUDGETAIRE_ID'))
                                {
                                  echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."'  selected>".$key->ANNEE_DESCRIPTION."</option>";
                                }
                                if($key->ANNEE_BUDGETAIRE_ID==$annee_budgetaire_en_cours)
                                {
                                  echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."'  selected>".$key->ANNEE_DESCRIPTION."</option>";
                                }
                                else
                                {
                                  echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."' >".$key->ANNEE_DESCRIPTION."</option>";
                                }
                              }
                              ?>
                            </select>
                          </div>
                            <div class="col-md-4">
                              <label class="form-label"><?=lang('messages_lang.date_debut_du')?>:</label>
                              <input type="date" name="DATE_DEBUT" id="DATE_DEBUT" onchange="listing();get_date()" max="<?=date('Y-m-d')?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                              <label class="form-label"><?=lang('messages_lang.date_fin_au')?>:</label>
                              <input type="date" name="DATE_FIN" id="DATE_FIN" onchange="listing()" max="<?=date('Y-m-d')?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                              <label for="form-label"><?=lang('messages_lang.include_action')?>:</label>
                                <div>
                                  <input type="radio" name="activ" id="activ" value="0" onclick="test();exporter_word();exporter_pdf();" checked> <?=lang('messages_lang.label_non')?>
                                  <input type="radio" name="activ" id="activ" value="1" onclick="test();exporter_word();exporter_pdf();"><?=lang('messages_lang.label_oui')?>
                              </div> 
                            </div>

                          
                            <div class="col-md-12">
                              <div class="col-md-6" style="float: right;">
                                <br>

                                <a href="#" id="btnexport" onclick="exporter()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel pull-right"></span>&nbsp; Excel<?//=lang('messages_lang.bouton_exporter')?></a>

                                <a href="#" id="exportword" onclick="exporter_word()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-word pull-right"></span>&nbsp;Word <?//=lang('messages_lang.bouton_exporter')?></a>

                                <a href="#" id="exportpdf" onclick="exporter_pdf()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-pdf pull-right"></span>&nbsp;PDF <?//=lang('messages_lang.bouton_exporter')?></a>
                              </div>
                            </div>
                          </div>
                      </div>
                    </form>
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <!-- <th><center><?=lang('messages_lang.th_code_action_new')?></center></th> 
                            <th><center><?=lang('messages_lang.th_action')?></center></th>
                            <th><center><?=lang('messages_lang.th_code_article_new')?></center></th>
                            <th><center><?=lang('messages_lang.th_code_nature_economique1')?></center></th>
                            <th><center><?=lang('messages_lang.th_intitule_nature_economique')?></center></th>-->
                            <th><center>&emsp;&emsp;<?=lang('messages_lang.label_taches')?>&emsp;&emsp;</center></th> 
                            <th><center><?=lang('messages_lang.th_budget_vote')?></center></th>
                            <th><center><?=lang('messages_lang.th_transfert_credit')?></center></th>
                            <th><center><?=lang('messages_lang.th_credit_transfert')?></center></th>
                            <th><center><?=lang('messages_lang.th_engag_budg')?></center></th>
                            <th><center><?=lang('messages_lang.th_engag_jurd')?></center></th>
                            <th><center><?=lang('messages_lang.th_liquidation')?></center></th>
                            <th><center><?=lang('messages_lang.th_ordonancement')?></center></th>
                            <th><center><?=lang('messages_lang.th_paiement')?></center></th>
                            <th><center><?=lang('messages_lang.th_decaissement')?></center></th>
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
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  function get_date(){ 
      $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
    }
 </script>
<script type="text/javascript">
  function get_dep()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    $.ajax(
    {
      url : "<?=base_url('/ihm/Classification_Economique_deux/get_dep')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:
      {
        INSTITUTION_ID:INSTITUTION_ID,
        PROGRAMME_ID:PROGRAMME_ID
      },
      success:function(data)
      {
        $('#PROGRAMME_ID').html(data.prog);
        $('#GRANDE_MASSE_ID').html(data.grande_masse);
      }
    });  
  }
</script>
<script type="text/javascript">
  function getinstution()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    $.ajax(
    {
      url : "<?=base_url('ihm/Classification_Economique_deux/getinstution')?>",
      type: "POST",
      dataType: "JSON",
      data: {INSTITUTION_ID:INSTITUTION_ID},
      success: function(data)
      {
        if(data.TYPE_INSTITUTION_ID==1)
        {
          $('#label_programme').text('Dotation');
        }
        else if (data.TYPE_INSTITUTION_ID==2)
        {
          $('#label_programme').text('Programme');
        }
      }
    });
  }
</script>
<script type="text/javascript">
  $(document).ready(function ()
  {
    listing();
    get_date_limit();
    exporter();
    exporter_word();
    exporter_pdf();

  });

  function listing(argument)
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var GRANDE_MASSE_ID=$('#GRANDE_MASSE_ID').val();

    var row_count ="1000000";
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[],
      "ajax":
      {
        url:"<?=base_url('ihm/Classification_Economique_deux/listing')?>",
        type:"POST",
        data:
        {
          INSTITUTION_ID: INSTITUTION_ID,
          PROGRAMME_ID: PROGRAMME_ID,
          DATE_DEBUT:DATE_DEBUT,
          DATE_FIN:DATE_FIN,
          ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID,
          GRANDE_MASSE_ID:GRANDE_MASSE_ID
        },
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],
      dom: 'Bfrtlip',
      order:[],
      buttons: [

        ],
      language: {
        "sProcessing":     "<?=lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?=lang('messages_lang.search_button')?>&nbsp;:",
        "sLengthMenu":     "<?=lang('messages_lang.labelle_et_afficher')?> _MENU_ <?=lang('messages_lang.labelle_et_affichage_element')?>",
        "sInfo":  "<?=lang('messages_lang.labelle_et_affichage_element')?> _START_ <?=lang('messages_lang.labelle_et_a')?> _END_ <?=lang('messages_lang.labelle_et_a')?> _END_ sur _TOTAL_ <?=lang('messages_lang.labelle_et_affichage_filtre')?> _TOTAL_ <?=lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?=lang('messages_lang.labelle_et_affichage_element')?> 0  <?=lang('messages_lang.labelle_et_a')?> 0 <?=lang('messages_lang.labelle_et_affichage_filtre')?> 0 <?=lang('messages_lang.labelle_et_element')?>",      
        "sInfoFiltered":   "(<?=lang('messages_lang.labelle_et_elementtotal')?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?=lang('messages_lang.labelle_et_chargement')?>...",
        "sZeroRecords":    "<?=lang('messages_lang.labelle_et_aucun_element')?>",
        "sEmptyTable":     "<?=lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
        "oPaginate": {
          "sFirst":      "<?=lang('messages_lang.labelle_et_premier')?>",
          "sPrevious":   "<?=lang('messages_lang.labelle_et_precedent')?>",
          "sNext":       "<?=lang('messages_lang.labelle_et_suivant')?>",
          "sLast":       "<?=lang('messages_lang.labelle_et_dernier')?>"
        },
        "oAria": {
          "sSortAscending":  ": <?=lang('messages_lang.labelle_et_trier_colone')?>",
          "sSortDescending": ": <?=lang('messages_lang.labelle_et_trier_activer_trier')?>"
        }
      }
    });
  }
</script>
<script type="text/javascript">
  function exporter()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var GRANDE_MASSE_ID=$('#GRANDE_MASSE_ID').val();
    var is_action=$("input[name='activ']:checked").val();


    if(INSTITUTION_ID == '' || INSTITUTION_ID == null)
    {
      INSTITUTION_ID = 0;
    }

    if(PROGRAMME_ID == '' || PROGRAMME_ID == null)
    {
      PROGRAMME_ID = 0;
    }
    
    if(DATE_DEBUT == '' || DATE_DEBUT == null)
    {
      DATE_DEBUT = 0;
    }
    if(DATE_FIN == '' || DATE_FIN == null)
    {
      DATE_FIN = 0;
    }
    if(ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null)
    {
      ANNEE_BUDGETAIRE_ID = 0;
    }
    if(GRANDE_MASSE_ID == '' || GRANDE_MASSE_ID == null)
    {
      GRANDE_MASSE_ID = 0;
    }
    document.getElementById("btnexport").href = "<?=base_url('ihm/Classification_Economique_deux/exporter/')?>"+'/'+INSTITUTION_ID+'/'+PROGRAMME_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+ANNEE_BUDGETAIRE_ID+'/'+GRANDE_MASSE_ID+'/'+is_action;
  }
</script>
<script type="text/javascript">
  function exporter_word()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var GRANDE_MASSE_ID=$('#GRANDE_MASSE_ID').val();
    var is_action=$("input[name='activ']:checked").val();

    if(INSTITUTION_ID == '' || INSTITUTION_ID == null)
    {
      INSTITUTION_ID = 0;
    }

    if(PROGRAMME_ID == '' || PROGRAMME_ID == null)
    {
      PROGRAMME_ID = 0;
    }

    if(GRANDE_MASSE_ID == '' || GRANDE_MASSE_ID == null)
    {
      GRANDE_MASSE_ID = 0;
    }
    if(DATE_DEBUT == '' || DATE_DEBUT == null)
    {
      DATE_DEBUT = 0;
    }
    if(DATE_FIN == '' || DATE_FIN == null)
    {
      DATE_FIN = 0;
    }
    if(ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null)
    {
      ANNEE_BUDGETAIRE_ID = 0;
    }
    document.getElementById("exportword").href = "<?=base_url('ihm/Classification_Economique_deux/export_word/')?>"+'/'+INSTITUTION_ID+'/'+PROGRAMME_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+ANNEE_BUDGETAIRE_ID+'/'+GRANDE_MASSE_ID+'/'+is_action;
  }
</script>
<script type="text/javascript">
  function exporter_pdf()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var GRANDE_MASSE_ID=$('#GRANDE_MASSE_ID').val();
    var is_action=$("input[name='activ']:checked").val();

    if(INSTITUTION_ID == '' || INSTITUTION_ID == null)
    {
      INSTITUTION_ID = 0;
    }

    if(PROGRAMME_ID == '' || PROGRAMME_ID == null)
    {
      PROGRAMME_ID = 0;
    }

    if(GRANDE_MASSE_ID == '' || GRANDE_MASSE_ID == null)
    {
      GRANDE_MASSE_ID = 0;
    }
    if(DATE_DEBUT == '' || DATE_DEBUT == null)
    {
      DATE_DEBUT = 0;
    }
    if(DATE_FIN == '' || DATE_FIN == null)
    {
      DATE_FIN = 0;
    }
    if(ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null)
    {
      ANNEE_BUDGETAIRE_ID = 0;
    }
    document.getElementById("exportpdf").href = "<?=base_url('ihm/Classification_Economique_deux/export_pdf/')?>"+'/'+INSTITUTION_ID+'/'+PROGRAMME_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+ANNEE_BUDGETAIRE_ID+'/'+GRANDE_MASSE_ID+'/'+is_action;
  }
</script>

<script type="text/javascript">
  function get_date_limit()
  {
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    
    $.ajax({
      url: "<?=base_url('')?>/ihm/Classification_Economique_deux/get_date_limit",
      type: "POST",
      dataType: "JSON",
      data: {
        ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID
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