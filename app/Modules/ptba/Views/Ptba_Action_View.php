
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
                    <div class="col-md-6">
                      <h1 class="header-title text-black"><?=lang('messages_lang.title_list_action')?>                      
                     </h1>
                   </div>
                   <div class="col-md-6" style="float: right;">

                    
                  </div>
                </div>
              </div>

              <div class="card-body">
                <div class="table-responsive" style="width: 100%;">
                  <form action="<?= base_url('ptba/Ptba_Action/indexdeux');?>" id="Myform" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6">
                          <label for="INSTITUTION_ID" class="form-label"><?=lang('messages_lang.label_institution')?></label>
                          <select onchange="submit();" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            foreach($institution as $key)
                            {
                              if($key->INSTITUTION_ID==$INSTITUTION_ID)
                              {
                                ?>
                                <option value="<?= $key->INSTITUTION_ID; ?>" selected><?= $key->DESCRIPTION_INSTITUTION; ?></option>
                                <?php
                              }
                              else
                              {
                                ?>
                                <option value="<?= $key->INSTITUTION_ID; ?>"><?= $key->DESCRIPTION_INSTITUTION; ?></option>
                                <?php
                              }
                            }
                            ?>
                          </select>
                        </div>

                        <div class="col-md-6">
                          <label for="PROGRAMME_ID" class="form-label"><?=lang('messages_lang.label_prog')?></label>
                          <select onchange="submit();" class="form-control" name="PROGRAMME_ID" id="PROGRAMME_ID">
                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            <?php
                            if(!empty($program))
                            {
                              foreach($program as $key)
                              {
                                if($key->PROGRAMME_ID == $PROGRAMME_ID)
                                {
                                  ?>
                                  <option value="<?= $key->PROGRAMME_ID; ?>" selected><?= $key->INTITULE_PROGRAMME; ?></option>
                                  <?php
                                }
                                else
                                {
                                  ?>
                                  <option value="<?= $key->PROGRAMME_ID; ?>"><?= $key->INTITULE_PROGRAMME; ?></option>
                                  <?php
                                }
                              }
                            }
                            ?>
                          </select>
                        </div>
                      </div>
                    </div>
                  </form>
                  <!-- </div>
                    <div class="row"> -->
                      <?php
                      if(session()->getFlashKeys('alert'))
                      {
                        ?>
                        <div class="w-100 bg-success text-white text-center" id="message">
                          <?php echo session()->getFlashdata('alert')['message']; ?>
                        </div>
                        <?php
                      }
                      ?>
                    <div class="table-responsive">
                      <table id="mytable" class=" table table-striped table-bordered" style="width: 100%;">
                        <thead>
                          <tr>
                            <th><center>#</center></th>
                            <th><center>CODE</center></th>
                            <th><center><?=lang('messages_lang.th_first_name')?></center></th>
                            <th><center>T1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                            <th><center>T2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                            <th><center>T3&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                            <th><center>T4&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</center></th>
                            <th><center><?=lang('messages_lang.th_total')?></center></th>
                            <th><center><?=lang('messages_lang.th_detail')?></center></th>
                          </tr>
                        </thead>
                      </table>
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
  $('#message').delay('slow').fadeOut(3000);
  $(document).ready(function ()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var row_count ="1000000";
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[],
      "order":[],
      "ajax":
      {
        url:"<?=base_url('ptba/Ptba_Action/listing')?>",
        type:"POST",
        data: {
          INSTITUTION_ID: INSTITUTION_ID,
          PROGRAMME_ID: PROGRAMME_ID
        },
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
          "targets":[0,8],
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
  });
  
  function getinstution()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    $.post('<?=base_url('ptba/Ptba_Action/indexdeux
      ')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID
    },
    function(data)
    {
      $('#PROGRAMME_ID').html(data.cart);
      PROGRAMME_ID.InnerHtml=data;
    })
  }
</script>
