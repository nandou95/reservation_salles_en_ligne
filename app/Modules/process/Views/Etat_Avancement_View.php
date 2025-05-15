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
              <div style="box-shadow: rgba(100,100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-body">
                    <div class="row container">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="" class="form-label"><?=lang('messages_lang.labelle_process')?> <font color="red">*</font></label>
                          <select name="PROCESS_ID" onchange="etat_avancement_listing();get_profil_etape();" id="PROCESS_ID" class="form-control">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                              <?php foreach ($process as $value) {
                                if ($value->PROCESS_ID== set_value('PROCESS_ID')) { ?>
                                  <option value="<?= $value->PROCESS_ID?>" selected>
                                    <?= $value->NOM_PROCESS ?></option>
                                  <?php } else { ?>
                                  <option value="<?= $value->PROCESS_ID?>">
                                    <?= $value->NOM_PROCESS ?></option>
                                  <?php }
                                } ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="" class="form-label"><?=lang('messages_lang.label_etape')?> <font color="red">*</font></label>
                          <select name="ID_ETAPE" onchange="etat_avancement_listing()" id="ID_ETAPE" class="form-control select2">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>  
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="col-md-12 d-flex">
                  <div class="col-md-9" style="float: left;">
                    <h4 style="margin-left: 1%;margin-top:10px"><?=lang('messages_lang.etat_avancement')?></h4>
                  </div>
                </div>
              
              <div class="card-body">
                <div class="row">
                  <?php
                  if(session()->getFlashKeys('alert'))
                  {
                    ?>
                    <div class="col-md-12">
                      <div class="w-100 bg-success text-white text-center" id="message">
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                    </div>
                    <?php
                  }elseif(session()->getFlashKeys('alert_fail')){                  
                  ?>
                    <div class="col-md-12">
                      <div class="w-100 bg-danger text-white text-center" id="message">
                        <?php echo session()->getFlashdata('alert_fail')['message']; ?>
                      </div>
                    </div>
                  <?php
                  }
                  ?>
                </div>
                <div class="row">
                  <div class="table-responsive" style="width: 100%;">
                    <table id="mytable" class=" table table-striped table-bordered">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th><?=lang('messages_lang.code_demande')?></th>
                          <th><?=lang('messages_lang.proc')?></th>
                          <th><?=lang('messages_lang.step')?></th>
                          <th><?=lang('messages_lang.init_demande')?></th>
                          <th><?=lang('messages_lang.prof')?></th>
                          <th><?=lang('messages_lang.th_date_demande')?></th>
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
<div class='modal fade' id='affiche_profil'>
    <div class='modal-dialog'>
      <div class='modal-content'>

        <div class='modal-body'>
          <center>
          <h5>
            <strong><?=lang('messages_lang.labelle_et_prof')?> </strong><br>
          </h5>
          </center>
          <div class="row">
            <ul id='profil_list' ><span id="loading_popup"></span>
            </ul>
          </div>
        </div>
        <div class='modal-footer'>
          
          <button class='btn btn-primary btn-md' data-dismiss='modal'><?=lang('messages_lang.quiter_action')?>          
          </button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<script>
  $(document).ready(function()
  {
    etat_avancement_listing();
   });

  function etat_avancement_listing()
  {

    var PROCESS_ID=$('#PROCESS_ID').val();

   
    var ID_ETAPE=$('#ID_ETAPE').val();
    // alert(PROCESS_ID+ID_ETAPE);
    var row_count ="1000000";
    $('#message').delay('slow').fadeOut(3000);
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":
      {
        url:"<?= base_url('process/Etat_avancement/etat_avancement_listing')?>",
        type:"POST",
        dataType: "JSON",
        data:{PROCESS_ID:PROCESS_ID,
              ID_ETAPE:ID_ETAPE
             },
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "orderable":true
      }],
      dom: 'Bfrtlip',
      buttons: [
      'excel', 'pdf'
      ],
      language: {
            "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
            "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
            "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
            "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
            "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
            "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
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

<script>
 
    function get_profil_etape()
    {
       var PROCESS_ID=$('#PROCESS_ID').val();
      $.post('<?= base_url('process/Etat_avancement/get_profil_etape') ?>', 
      {
        PROCESS_ID: PROCESS_ID
      },
      function(data) {
        $('#ID_ETAPE').html(data.html);
        ID_ETAPE.InnerHtml = data;
      });
    }
</script>


<script>
  function get_profils(id)
  {
    $('#affiche_profil').modal('show');
    $.ajax(
    {
      url:"<?=base_url()?>/process/Etat_avancement/get_profils/"+id,
      type:"GET",
      dataType:"JSON",
      beforeSend:function() {
        $('#loading_popup').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success: function(data)
      {
        $('#loading_popup').html("");
        $('#profil_list').html(data.liste);
        
      }
    });
  }
</script>