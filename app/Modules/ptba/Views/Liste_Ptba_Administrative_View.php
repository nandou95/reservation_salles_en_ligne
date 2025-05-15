
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
                                        <div class="col-md-12">
                                            <h1 class="header-title text-black"><?=lang('messages_lang.classification_administrative')?>
                                            </h1>
                                        </div>
                                    </div>
                                </div>
                               
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_institution')?></label>
                                        <select onchange="liste(this.value); get_soutut(this.value)" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
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
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_sous_titelle')?></label>
                                        <select onchange="liste(this.value); get_prog(this.value)" class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID">
                                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                            
                                        </select>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_prog')?></label>
                                        <select onchange="liste(this.value); get_action(this.value)" class="form-control" name="CODE_PROGRAMME" id="CODE_PROGRAMME">
                                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>

                                        </select>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_action')?></label>
                                        <select onchange="liste(this.value)" class="form-control" name="CODE_ACTION" id="CODE_ACTION">
                                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                            
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="row">
                                    <br>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_trimestre')?></label>
                                        <select onchange="liste(this.value)" class="form-control" name="CODE_TRANCHE" id="CODE_TRANCHE">
                                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                            <?php
                                            foreach($tranche as $key)
                                            {
                                              if($key->CODE_TRANCHE==set_value('CODE_TRANCHE'))
                                              {
                                                echo "<option value='".$key->CODE_TRANCHE."'  selected>".$key->DESCRIPTION_TRANCHE."</option>";
                                              }
                                              else
                                              {
                                                echo "<option value='".$key->CODE_TRANCHE."' >".$key->DESCRIPTION_TRANCHE."</option>";
                                              }
                                            }
                                            ?>
                                        </select>
                                      </div>
                                    </div>
                                </div>

                                <div class="table-responsive" >
                                        
                                    <table id="mytable" class=" table table-striped table-bordered" style="width: 100%;">
                                        <thead>
                                            <tr>                                               
                                              <th><?=lang('messages_lang.th_institution')?></th>
                                              <th><?=lang('messages_lang.th_programme')?></th>
                                              <th><?=lang('messages_lang.th_action')?></th>
                                              <th><?=lang('messages_lang.th_activite')?></th>
                                              <th><?=lang('messages_lang.th_ligne_budg')?></th>
                                              <th><?=lang('messages_lang.th_resultat_attendu')?></th>
                                              <th><?=lang('messages_lang.th_quantite')?></th>
                                              <th><?=lang('messages_lang.th_montant')?></th>
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
  });
</script>

<script>
  
    function liste()
    {
        var INSTITUTION_ID=$('#INSTITUTION_ID').val();
        var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
        var CODE_PROGRAMME=$('#CODE_PROGRAMME').val();
        var CODE_ACTION=$('#CODE_ACTION').val();
        var CODE_TRANCHE=$('#CODE_TRANCHE').val();
        var row_count ="1000000";
        $("#mytable").DataTable({
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "targets":[],
          "order":[],
          "ajax":{
            url:"<?= base_url('ptba/Liste_Ptba_Administrative/listing')?>",
            type:"POST", 
            data:
            {
              INSTITUTION_ID:INSTITUTION_ID,
              SOUS_TUTEL_ID:SOUS_TUTEL_ID,
              CODE_PROGRAMME:CODE_PROGRAMME,
              CODE_ACTION:CODE_ACTION,
              CODE_TRANCHE:CODE_TRANCHE
            } 
          },

          lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
          pageLength: 10,
          "columnDefs":[{
            "targets":[6,7],
            "orderable":false
          }],

          dom: 'Bfrtlip',
          order: [],
          buttons: [
            'excel', 'pdf'
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
  
  /***********   Script pour la sélection des sous tutelles   ***********/
  function get_soutut()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    $('#SOUS_TUTEL_ID').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');
    $('#CODE_PROGRAMME').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');
    $('#CODE_ACTION').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');

    $.post('<?=base_url('ptba/Liste_Ptba_Administrative/get_soutut')?>',
    {
      INSTITUTION_ID : INSTITUTION_ID,
    },
    function(data)
    {
      $('#SOUS_TUTEL_ID').html(data.html);
      SOUS_TUTEL_ID.InnerHtml=data.html;
      liste();
      
    })
  }

  
  /***********   Script pour la sélection des programmes   ***********/
  function get_prog()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    $('#CODE_PROGRAMME').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');
    $('#CODE_ACTION').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');


    $.post('<?=base_url('ptba/Liste_Ptba_Administrative/get_prog')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID,
      SOUS_TUTEL_ID:SOUS_TUTEL_ID
    },
    function(data)
    {
      //alert(data);
      $('#CODE_PROGRAMME').html(data.html);
      CODE_PROGRAMME.InnerHtml=data.html;
      liste();
      
    })
  }

  
  /**************   Script pour la sélection des actions   ****************/
  function get_action()
  {
    var CODE_PROGRAMME=$('#CODE_PROGRAMME').val();
    $('#CODE_ACTION').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');

    $.post('<?=base_url('ptba/Liste_Ptba_Administrative/get_action')?>',
    {
      CODE_PROGRAMME:CODE_PROGRAMME
    },
    function(data)
    {
      //alert(data);
      $('#CODE_ACTION').html(data.html);
      CODE_ACTION.InnerHtml=data.html;
      liste();
      
    })
  }

</script>