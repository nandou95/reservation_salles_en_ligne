<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
 
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
               <br>
               <div class="col-12 d-flex">

                <div class="col-9" style="float: left;">
                  <h1 class="header-title text-dark">
                    <?=lang('messages_lang.liste_activités')?><br>
                  </h1>
                  <?php if($profil != 1){?>
                  <h5><srtong><?=$instit['DESCRIPTION_INSTITUTION']?></srtong><h5>
                  <?php }?>
                </div>
                <div class="col-3" style="float: right;">
                  <a href="<?=base_url('ihm/Liste_Activites/create')?>" style="float: right;margin-right: 90px;margin: 10px" class="btn btn-primary"><span class="fa fa-plus pull-right"></span><?=lang('messages_lang.labelle_et_nouveau')?></a>   
                </div>
              </div>
              <br>
              
              <div class="card-body">
                <div class="row">
                  <?php if($profil == 1){?>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="Nom" class="form-label"><?=lang('messages_lang.label_institution')?></label>
                        <select onchange="liste(this.value); get_prog(this.value)" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                          <option value="">-<?=lang('messages_lang.labelle_selecte')?>-</option>
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
                  <?php }else{?>
                    <input type="hidden" name="INSTITUTION_ID" id="INSTITUTION_ID" class="form-control" onchange="liste(this.value); get_prog(this.value)" value="<?=$instit['INSTITUTION_ID']?>" readonly>
                  <?php } ?>
                      
                   

                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="Nom" class="form-label"><?=lang('messages_lang.label_prog')?></label>
                      <select onchange="liste(this.value); get_action(this.value)" class="form-control" name="CODE_PROGRAMME" id="CODE_PROGRAMME">
                        <option value="">-<?=lang('messages_lang.labelle_selecte')?>-</option>

                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="Nom" class="form-label"><?=lang('messages_lang.label_action')?></label>
                      <select onchange="liste(this.value)" class="form-control" name="CODE_ACTION" id="CODE_ACTION">
                        <option value="">-<?=lang('messages_lang.labelle_selecte')?>-</option>

                      </select>
                    </div>
                  </div>
                </div>

                 <div style="margin-left: 15px" class="row">
                  <?php if (session()->getFlashKeys('alert')) : ?>
                    <div class="w-100 bg-success text-white text-center" id="message" >
                      <?php echo session()->getFlashdata('alert')['message']; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="table-responsive container ">

                  <div></div>
                  <table id="mytable" class=" table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th><?=lang('messages_lang.th_code_budgetaire')?></th>
                        <th><?=lang('messages_lang.code_prog')?></th> 
                        <th><?=lang('messages_lang.th_activite')?>&emsp;&emsp;</th>
                        <th><?=lang('messages_lang.result_attend')?></th>
                        <th>T1&emsp;&emsp;&emsp;&emsp;</th>
                        <th>T2&emsp;&emsp;&emsp;&emsp;</th>
                        <th>T3&emsp;&emsp;&emsp;&emsp;</th>
                        <th>T4&emsp;&emsp;&emsp;&emsp;</th>
                        <th><?=lang('messages_lang.labelle_et_statut')?></th>
                        <th><?=lang('messages_lang.thead_detail')?></th>
                      </tr>
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
    </main>
  </div>
</div>
<?php echo view('includesbackend/scripts_js.php');?>


</body>

</html>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(3000);
  $(document).ready(function()
  {
    if(<?=$profil?> !=1 ){
      get_prog();
    }
    liste();
  });
</script >

<script type="text/javascript">
  function liste() 
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var CODE_PROGRAMME = $('#CODE_PROGRAMME').val();
    var CODE_ACTION = $('#CODE_ACTION').val();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('ihm/Liste_Activites/listing')?>",
        type: "POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
          CODE_PROGRAMME: CODE_PROGRAMME,
          CODE_ACTION: CODE_ACTION
        }
      },

      lengthMenu: [[10, 50, 100, row_count], [10, 50, 100, "All"]],
      pageLength: 10,
      "columnDefs": [{
        "targets": [0,10],
        "orderable": false
      }],

      dom: 'Bfrtlip',
      order: [],
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
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
    $('#CODE_PROGRAMME').html('<option value="">-<?= lang('messages_lang.labelle_selecte')?>-</option>');
    $('#CODE_ACTION').html('<option value="">-<?= lang('messages_lang.labelle_selecte')?>-</option>');


    $.post('<?=base_url('ihm/Liste_Activites/get_prog')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID
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
    $('#CODE_ACTION').html('<option value="">-<?= lang('messages_lang.labelle_selecte')?>-</option>');

    $.post('<?=base_url('ihm/Liste_Activites/get_action')?>',
    {
      CODE_PROGRAMME:CODE_PROGRAMME
    },
    function(data)
    {
      $('#CODE_ACTION').html(data.html);
      CODE_ACTION.InnerHtml=data.html;
      liste(); 
    })
  }
</script>