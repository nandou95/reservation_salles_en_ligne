
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
                                        <h1 class="header-title text-black">
                                               <?=$titre?>
                                            </h1>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-header">
                                   
                                    <div class="row col-md-12">
                                      <div class="form-group">
                                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_institution')?></label>
                                        <select onchange="liste_ptba_programme(this.value)" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                                          <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                          <?php
                                          foreach($get_institution as $key)
                                          {
                                            if($key->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                                            {
                                              echo "<option value='".$key->INSTITUTION_ID."'  selected>".$key->CODE_INSTITUTION." - ".$key->DESCRIPTION_INSTITUTION."</option>";
                                          }
                                          else
                                          {
                                              echo "<option value='".$key->INSTITUTION_ID."' >".$key->CODE_INSTITUTION." - ".$key->DESCRIPTION_INSTITUTION."</option>";
                                          }
                                      }
                                      ?>
                                  </select>
                              </div>
                          </div>
                      </div>

                      <div class="car-body">
                            <div class="table-responsive" style="width: 100%;">
                                    <table id="mytable" class=" table table-striped table-bordered">
                                      <thead>
                                        <tr>
                                          <th>#</th>
                                          <th>CODE</th>
                                          <th><?=lang('messages_lang.th_programme')?></th>
                                          <th><?=lang('messages_lang.th_objectif')?></th>
                                          <th><?=lang('messages_lang.th_institution')?></th>
                                          <th>T1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&emsp;&emsp;</th>
                                          <th>T2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&emsp;&emsp;</th>
                                          <th>T3&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&emsp;&emsp;</th>
                                          <th>T4&&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&emsp;&emsp;</th>
                                          <th><?=lang('messages_lang.th_total_annuel')?></th>
                                          <th>OPTION</th>
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
        liste_ptba_programme();
    });
</script>

<script>

    function liste_ptba_programme()
    {
      var INSTITUTION_ID=$('#INSTITUTION_ID').val();

      var row_count ="1000000";
      $("#mytable").DataTable({
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "targets":[0,4],
          "oreder":[[ 0, 'desc' ]],
          "ajax":{
            url:"<?= base_url('ptba/Ptba_Programme/liste_ptba_programme')?>",
            type:"POST", 
            data:
            {
                INSTITUTION_ID:INSTITUTION_ID

            }
        },

        lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
            "targets":[],
            "orderable":false
        }],

        dom: 'Bfrtlip',
        order: [4,'desc'],
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
