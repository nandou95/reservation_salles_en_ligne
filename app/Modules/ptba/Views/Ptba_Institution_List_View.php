
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
                               
                            <div class="car-body">
                                <h1 class="header-title text-black">
                                    <?=$titre;?>
                                </h1><br>
                                <div class="table-responsive" style="width: 100%;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="Nom" class="form-label"><?=lang('messages_lang.label_annee')?></label>
                                            <select onchange="table_institutions_program(this.value)" class="form-control" name="ANNEE_ID" id="ANNEE_ID">
                                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                                <?php
                                                foreach($annees as $key)
                                                {
                                                  if($key->EXERCICE_ID==set_value('ANNEE_ID'))
                                                  {
                                                    echo "<option value='".$key->EXERCICE_ID."'  selected>".$key->ANNEE."</option>";
                                                  }
                                                  else
                                                  {
                                                    echo "<option value='".$key->EXERCICE_ID."' >".$key->ANNEE."</option>";
                                                  }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <div style="float: right;">
                                            </div>
                                        </div>
                                    </div><br>
                                    <table id="mytable" class=" table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th><center><?=lang('messages_lang.code')?></center></th>
                                                <th><center><?= lang('messages_lang.th_institution') ?></center></th>
                                                <th><center>T1&emsp;&emsp;&emsp;&emsp;</center></th>
                                                <th><center>T2&emsp;&emsp;&emsp;&emsp;</center></th>
                                                <th><center>T3&emsp;&emsp;&emsp;&emsp;</center></th>
                                                <th><center>T4&emsp;&emsp;&emsp;&emsp;</center></th>
                                                <th><center><?=lang('messages_lang.th_total_annuel')?></center></th>
                                                <th><center><?=lang('messages_lang.th_detail')?></center></th>
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
    table_institutions_program();
  });
</script>




<script>
  
    function table_institutions_program()
    {
        var ANNEE_ID = $('#ANNEE_ID').val();
        var row_count ="1000000";
        $("#mytable").DataTable({
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "targets":[0,4],
          "oreder":[[ 0, 'desc' ]],
          "ajax":{
            url:"<?= base_url('ptba/Ptba_Institution/get_info')?>",
            type:"POST",
            data:
            {
              ANNEE_ID:ANNEE_ID
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
