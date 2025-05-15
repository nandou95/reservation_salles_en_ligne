<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
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

                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-8">
                      <h1 class="header-title text-black"><?=lang('messages_lang.title_canevas_deux')?>
                    </h1>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="Nom" class="form-label"><?=lang('messages_lang.label_institution')?></label>
                      <select class="form-control" 
                      onchange="liste(this.value); get_prog(this.value); test()" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                      <?php
                      foreach($instit as $key)
                      {
                        if($key->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                        {
                          echo "<option value='".$key->INSTITUTION_ID."'  selected>".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                        }
                        else
                        {
                          echo "<option value='".$key->INSTITUTION_ID."' >".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                        }
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <!-- <div class="col-md-3">
                  <div class="form-group">
                    <label for="Nom" class="form-label"><?=lang('messages_lang.label_sous_titelle')?></label>
                    <select class="form-control" onchange="liste(this.value); get_prog(this.value);test()" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>

                    </select>
                  </div>
                </div> -->
                <div class="col-md-3">
                  <div class="form-group">
                    <label id="id_pgm" for="Nom" class="form-label"><?=lang('messages_lang.labelle_programme')?></label>
                    <select class="form-control" onchange="liste(this.value); get_action(this.value);test()" class="form-control" name="CODE_PROGRAMME" id="CODE_PROGRAMME">
                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>

                    </select>
                  </div>
                </div>
                <div class="col-md-3" id="div_action">
                  <div class="form-group">
                    <label for="Nom" class="form-label"><?=lang('messages_lang.labelle_action')?></label>
                    <select class="form-control" onchange="liste(this.value);test()" class="form-control" name="CODE_ACTION" id="CODE_ACTION">
                      <option value=""><?=lang('messages_lang.labelle_selecte')?></option>

                    </select>
                  </div>
                </div>
                <div class="col-md-3" id="">
                  <div class="form-group">
                    <label for="Nom" class="form-label"><?=lang('messages_lang.select_anne_budget')?></label>
                    <select class="form-control" onchange="liste(this.value);test()" class="form-control" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID">
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
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="Nom" class="form-label"><?=lang('messages_lang.label_trimestre')?></label>
                    <select class="form-control" name="TRIMESTRE_ID" id="TRIMESTRE_ID" onchange="liste(this.value);test()" >
                      <option value="5"><?=lang('messages_lang.labelle_selecte')?></option>
                      <?php
                      foreach($tranches as $key_tranche)
                      {
                        if($key_tranche->TRANCHE_ID==set_value('TRANCHE_ID'))
                        {
                          echo "<option value='".$key_tranche->TRANCHE_ID."'  selected>".$key_tranche->DESCRIPTION_TRANCHE."</option>";
                        }
                        else
                        {
                          echo "<option value='".$key_tranche->TRANCHE_ID."' >".$key_tranche->DESCRIPTION_TRANCHE."</option>";
                        }
                      }
                      ?>
                    </select>
                  </div>
                </div>
                
              </div>
              <div class="col-md-6" style="float: right;">
                <a href="" id="btnexport" onclick="test()" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel pull-right"></span></span>&nbsp;Excel</a>&nbsp;&nbsp;

              </div>
              <div class="table-responsive" >
                <table id="mytable" class=" table table-striped table-bordered" style="width: 100%;">
                  <thead>
                    <tr>
                      <th><?=lang('messages_lang.th_institution')?></th>
                      <th><?=lang('messages_lang.th_programme')?></th>
                      <th><?=lang('messages_lang.th_action')?>&emsp;&emsp;&emsp;&emsp;</th>
                      <th><?=lang('messages_lang.th_respo')?></th>
                      <th><?=lang('messages_lang.ligne_budgetaire_institution_detail')?></th>

                      <th><?=lang('messages_lang.th_activite')?>&emsp;&emsp;&emsp;&emsp;</th>
                      <th><?=lang('messages_lang.result_attend')?>&emsp;&emsp;&emsp;&emsp;</th>
                      <th><?=lang('messages_lang.table_unite')?></th>

                      <th><?=lang('messages_lang.th_montant_vote')?></th>
                      <th><?=lang('messages_lang.th_transfert_credit')?></th>
                      <th><?=lang('messages_lang.th_credit_transfert')?></th>
                      <th><?=lang('messages_lang.th_engag_budg')?></th>
                      <th><?=lang('messages_lang.th_engag_jurd')?></th>
                      <th><?=lang('messages_lang.th_liquidation')?></th>
                      <th><?=lang('messages_lang.th_ordonancement')?></th>
                      <th><?=lang('messages_lang.th_paiement')?></th>
                      <th><?=lang('messages_lang.th_decaissement')?></th>
                      <th><?=lang('messages_lang.th_ecart_budg')?></th>
                      <th><?=lang('messages_lang.th_ecart_jurid')?></th>
                      <th><?=lang('messages_lang.th_ecart_liquid')?></th>
                      <th><?=lang('messages_lang.th_ecart_ordona')?></th>
                      <th><?=lang('messages_lang.th_ecart_pay')?></th>
                      <th><?=lang('messages_lang.th_ecart_decaiss')?></th>
                      <th><?=lang('messages_lang.th_taux_budgetaire')?></th>
                      <th><?=lang('messages_lang.th_taux_jurdique')?></th>
                      <th><?=lang('messages_lang.th_taux_juridique')?></th>
                      <th><?=lang('messages_lang.th_taux_ordonnancement')?></th>
                      <th><?=lang('messages_lang.th_taux_paiement')?></th>
                      <th><?=lang('messages_lang.th_taux_decaissement')?></th>
                      <th><?=lang('messages_lang.th_resultat_realise')?></th>
                      <th><?=lang('messages_lang.th_ecart_physique')?></th>
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

<script type="text/javascript">
  $(document).ready(function ()
  {
    liste();
    $("#id_pgm").html('<?=lang('messages_lang.labelle_programme')?>');
    $("#div_action").show();
  });
</script>

<script>  
  function liste()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var CODE_PROGRAMME=$('#CODE_PROGRAMME').val();
    var CODE_ACTION=$('#CODE_ACTION').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var TRIMESTRE_ID=$('#TRIMESTRE_ID').val();

    
    var row_count ="1000000";
    $("#mytable").DataTable({
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[],
      "ajax":{
        url:"<?= base_url('ihm/Canevas_Suivi_Evaluation_Un/listing')?>",
        type:"POST", 
        data:
        {
          INSTITUTION_ID:INSTITUTION_ID,
          CODE_PROGRAMME:CODE_PROGRAMME,
          CODE_ACTION:CODE_ACTION,
          ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID,
          TRIMESTRE_ID:TRIMESTRE_ID
        } 
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],
      order: [],
      dom: 'Bfrtlip',
      buttons: [

      ],
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
  
  /***********   Script pour la sélection des programmes   ***********/
  function get_prog()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    //var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();

    $.post('<?=base_url('ihm/Canevas_Suivi_Evaluation_Un/get_prog')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID
    },
    function(data)
    {
      $('#CODE_PROGRAMME').html(data.html);
      CODE_PROGRAMME.InnerHtml=data.html;
      
    })
  }
  
  /**************   Script pour la sélection des actions   ****************/
  function get_action()
  {
    var CODE_PROGRAMME=$('#CODE_PROGRAMME').val();

    $.post('<?=base_url('ihm/Canevas_Suivi_Evaluation_Un/get_action')?>',
    {
      CODE_PROGRAMME:CODE_PROGRAMME
    },
    function(data)
    {
      $('#CODE_ACTION').html(data.html);
      CODE_ACTION.InnerHtml=data.html;
      
    })
  }
</script>
<script type="text/javascript">
  function test()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val()
    //var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val()
    var PROGRAMME_ID=$('#CODE_PROGRAMME').val()
    var ACTION_ID=$('#CODE_ACTION').val()
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var TRIMESTRE_ID=$('#TRIMESTRE_ID').val();

    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}
    if (TRIMESTRE_ID == '' || TRIMESTRE_ID == null) {TRIMESTRE_ID = 0}
          
          document.getElementById("btnexport").href =url="<?=base_url('ihm/Canevas_Suivi_Evaluation_Un/export/')?>"+'/'+INSTITUTION_ID+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+ANNEE_BUDGETAIRE_ID+'/'+TRIMESTRE_ID;
  }
</script>