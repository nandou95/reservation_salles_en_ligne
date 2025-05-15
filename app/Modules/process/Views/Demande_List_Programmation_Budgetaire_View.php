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
                   <div class="col-md-4">
                    <div class="form-group">
                      <label for="" class="form-label"><?=lang('messages_lang.labelle_institution')?> <font color="red">*</font></label>
                      <select name="INSTITUTION_ID" onchange="get_etapes();listing()" id="INSTITUTION_ID" class="form-control">
                        <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>

                        <?php foreach($institution as $value):
                          if($value->INSTITUTION_ID==set_value('INSTITUTION_ID')){?>
                            <option value="<?= $value->INSTITUTION_ID?>" selected><?= $value->DESCRIPTION_INSTITUTION ?></option>
                          <?php }else{?>
                            <option value="<?=$value->INSTITUTION_ID?>"><?= $value->DESCRIPTION_INSTITUTION ?></option>
                          <?php }endforeach ?>

                        </select>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="" class="form-label"> <?=lang('messages_lang.labelle_process')?> <font color="red">*</font></label>
                        <select name="PROCESS_ID" onchange="get_etapes();listing()" id="PROCESS_ID" class="form-control">
                          <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>
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
                        <div class="col-md-4">
                          <div class="form-group">
                            <label for="" class="form-label"> <?=lang('messages_lang.labelle_Etapes')?><font color="red">*</font></label>
                            <select name="ID_ETAPE" onchange="listing()" id="ID_ETAPE" class="form-control">
                              <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>
                            </select>
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
                      <div class="col-md-6" style="float: left;">
                        <h4 style="margin-left: 1%;margin-top:10px"> <?=lang('messages_lang.liste_demande')?></h4>
                      </div>

                      <?php
                      if (!empty($getAction)) {
                      ?>
                      <div class="col-6">
                        <a href="<?=base_url(''.$getAction['LINK_FORM'].''.$getAction['ACTION_ID'].'')?>" style="float: right;margin-right: 90px;margin: 5px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;<?=$getAction['DESCR_ACTION']?></a>
                      </div>
                      <?php
                      }
                      ?>
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
                        }
                        ?>
                      </div>
                      <div class="row">
                        <div class="table-responsive" style="width: 100%;">
                          <table id="mytable" class=" table table-striped table-bordered">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th><?=lang('messages_lang.th_code_demande')?></th>
                                <th><?=lang('messages_lang.labelle_process')?></th>
                                <th> <?=lang('messages_lang.labelle_Etapes')?></th>
                                <th><?=lang('messages_lang.labelle_institution')?></th>
                                <th><?=lang('messages_lang.th_date_demande')?></th>
                                <th><?=lang('messages_lang.labelle_et_action')?></th>
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
    <script>
      $(document).ready(function()
      {
       listing();
     });

      function listing() {

        var PROCESS_ID=$('#PROCESS_ID').val();
        var ID_ETAPE=$('#ID_ETAPE').val();
        var INSTITUTION_ID=$('#INSTITUTION_ID').val();

        var row_count ="1000000";
        $('#message').delay('slow').fadeOut(3000);
        $("#mytable").DataTable(
        {
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "ajax":
          {
            url:"<?= base_url('process/Demandes_Program_Budget/listing')?>",
            type:"POST",
            dataType: "JSON",
            data:{PROCESS_ID:PROCESS_ID,
              ID_ETAPE:ID_ETAPE,
              INSTITUTION_ID:INSTITUTION_ID
            },
          },
          lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
          pageLength: 10,
          "columnDefs":[{
            "targets":[0,4],
            "orderable":true
          }],
          dom: 'Bfrtlip',
          order:[1,'asc'],
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

     function get_etapes() {

       var PROCESS_ID = $('#PROCESS_ID').val();
       $.ajax({
        url : "<?=base_url('process/Demandes_Program_Budget/get_etapes')?>",
        type : "POST",
        dataType : 'JSON',
        data: {
         PROCESS_ID:PROCESS_ID
       },
       success:function(data) {
         $('#ID_ETAPE').html(data.DATA_ETAPE);
       },
       error:function(jsXHR,textStatus,errorThrown) {
        $('#wait_classe').html("");
        console.log('Impossible de récupérer les etapes : '+textStatus);
      }
    }); //end ajouter
     } 
   </script>