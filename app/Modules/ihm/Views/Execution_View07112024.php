
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

                <div class="card-header" >
                  <div class="row col-md-12">
                    <div class="col-md-8">
                      <h1 class="header-title text-dark">Liste des Executions</h1>
                    </div>
                    
                  </div>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_institution')?></label>
                        <select onchange="liste(this.value); get_programme()" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
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
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_prog')?></label>
                        <select onchange="liste(this.value); get_action()" class="form-control" name="PROGRAMME_ID" id="PROGRAMME_ID">
                          <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_action')?></label>
                        <select onchange="liste(this.value); get_pap_activite()" class="form-control" name="ACTION_ID" id="ACTION_ID">
                          <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-label">Activite</label>
                        <select onchange="liste(this.value)" class="form-control" name="PAP_ACTIVITE_ID" id="PAP_ACTIVITE_ID">
                          <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive" >
                    <table id="mytable" class=" table table-striped table-bordered" style="width: 100%;">
                      <thead>
                        <tr> 
                          <th><center>#</center></th>
                          <th><center>TITRE&nbsp;DECAISSEMENT</center></th>
                          <th><center>NUMERO&nbsp;BON&nbsp;ENGAGEMENT</center></th>
                          <th><center>ENG&nbsp;BUDGETAIRE</center></th>
                          <th><center>ENG&nbsp;JURIDIQUE</center></th>
                          <th><center>LIQUIDATION</center></th>
                          <th><center>ORDONNANCEMENT</center></th>
                          <th><center>PAIEMENT</center></th>
                          <th><center>DECAISSEMENT</center></th>
                          <th>DETAILS</th>
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
      var PROGRAMME_ID=$('#PROGRAMME_ID').val();
      var ACTION_ID=$('#ACTION_ID').val();
      var PAP_ACTIVITE_ID=$('#PAP_ACTIVITE_ID').val();
      var row_count ="1000000";
      $("#mytable").DataTable({
        "processing":true,
        "destroy" : true,
        "serverSide":true,
        "targets":[],
        "order":[],
        "ajax":{
          url:"<?= base_url('ihm/Execution/listing')?>",
          type:"POST", 
          data:
          {
           INSTITUTION_ID:INSTITUTION_ID,
           PROGRAMME_ID:PROGRAMME_ID,
           ACTION_ID:ACTION_ID,
           PAP_ACTIVITE_ID:PAP_ACTIVITE_ID

         }
       },

       lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
       pageLength: 10,
       "columnDefs":[{
        "targets":[2,4],
        "orderable":false
      }],

       dom: 'Bfrtlip',
       order: [],
       buttons: [],
       language: {
        "sProcessing":     "<?=lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?=lang('messages_lang.search_button')?>&nbsp;:",
        "sLengthMenu":     "<?=lang('messages_lang.labelle_et_afficher')?> _MENU_ <?=lang('messages_lang.labelle_et_affichage_element')?>",
        "sInfo":  "<?=lang('messages_lang.labelle_et_affichage_element')?> _START_ <?=lang('messages_lang.labelle_et_a')?> _END_ <?=lang('messages_lang.labelle_et_a')?> sur _TOTAL_ <?=lang('messages_lang.labelle_et_element')?>",
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
  /***********   Script pour la sélection des programmes   ***********/
    function get_programme(){
      let INSTITUTION_ID = $('#INSTITUTION_ID').val();

      $.post('<?=base_url('ihm/Execution/get_programme')?>',
      {
        INSTITUTION_ID:INSTITUTION_ID
      },
      function(data)
      {
        $('#PROGRAMME_ID').html(data.programme);
      })
}

  /**************   Script pour la sélection des actions   ****************/
    function get_action(){
      let PROGRAMME_ID=$('#PROGRAMME_ID').val();

      $.post('<?=base_url('ihm/Execution/get_action')?>',
      {
        PROGRAMME_ID:PROGRAMME_ID
      },
      function(data)
      {
        $('#ACTION_ID').html(data.action);
      })
}

  /**************   Script pour la sélection des actions   ****************/
    function get_pap_activite(){
      let ACTION_ID=$('#ACTION_ID').val();

      $.post('<?=base_url('ihm/Execution/get_pap_activite')?>',
      {
        ACTION_ID:ACTION_ID
      },
      function(data)
      {
        $('#PAP_ACTIVITE_ID').html(data.pap_activite);
      })
}

  </script>
