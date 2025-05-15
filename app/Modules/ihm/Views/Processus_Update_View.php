  <!DOCTYPE html>
  <html lang="en">
  <head>
    <?php echo view('includesbackend/header.php'); ?>
  </head>
  <style>
    hr.vertical {
      border:         none;
      border-left:    1px solid hsla(200, 2%, 12%,100);
      height:         55vh;
      width: 1px;
      color: #ddd
    }
  </style>
  <body>
    <div class="wrapper">
      <?php echo view('includesbackend/navybar_menu.php'); ?>
      <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
      <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
      <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
      <script src="/DataTables/datatables.js"></script>
      <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
      <div class="main">
        <?php echo view('includesbackend/navybar_topbar.php'); ?>
        
        <main class="content">
          <div class="container-fluid">
            <div class="header">
              <h1 class="header-title text-white"></h1>
            </div>
            <div class="row">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                  <div class="col-md-12 d-flex">
                    <div class="col-md-9" style="float: left;">
                      <h4 style="margin-left: 1%;margin-top:10px"><?= lang('messages_lang.labelle_et_modifier_proc')?></h4>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?php echo base_url('ihm/Processus')?>" style="float: right;margin: 40px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.labelle_et_list')?> </a>
                    </div>
                  </div>
                  <div class=" container " style="width:100%">
                    <?php $validation = \Config\Services::validation(); ?>
                    <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('ihm/Processus/update')?>" method="post" >
                      <div class="container">

                        <div class="row" style="border:1px solid #ddd;border-radius:5px">
                          <div class="col-md-12 mt-3 ml-2"style="margin-bottom:50px" >
                            <div class="row">
                              <input type="hidden" name="PROCESS_ID" id="PROCESS_ID" value="<?=$process['PROCESS_ID']?>">
                              <div class="col-md-6" >
                                  <label for=""><?= lang('messages_lang.labelle_nom_proc')?><font color="red">*</font></label>
                                  <textarea class="form-control" id="PROCESS_NOM" name="PROCESS_NOM"  ><?php if (isset($validation)) : ?><?=set_value('PROCESS_NOM')?><?php endif ?><?=$process['NOM_PROCESS']?></textarea>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_PROCESS_NOM"><?= $validation->getError('PROCESS_NOM'); ?></font>
                                  <?php endif ?>    
                                </div>
                              <div class="col-md-6">
                                  <label for=""><?= lang('messages_lang.labelle_nom_table')?><font color="red">*</font></label>
                                  <input type="text" class="form-control" id="TABLE" name="TABLE" value="<?=$process['TABLE_NAME'] ?>" <?php if (isset($validation)) : ?> value="<?=set_value('TABLE')?>" <?php endif ?> >
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_TABLE"><?= $validation->getError('TABLE'); ?></font>
                                  <?php endif ?>    
                                </div>
                                <br>
                              <div class="col-md-6">
                                  <label for=""><?= lang('messages_lang.lien_action')?><font color="red">*</font></label>
                                  <input type="text" class="form-control" id="LINK" name="LINK"  value="<?=$process['LINK']?>" <?php if (isset($validation)) : ?> value="<?=set_value('LINK')?>" <?php endif ?>>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_LINK"><?= $validation->getError('LINK'); ?></font>
                                  <?php endif ?>    
                                </div>
                            </div>
                            <br>
                            <div class="col-md-12 mt-5 " >
                                <a onclick="save()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.enregistre_action')?></a>
                              </div>
                          </div>       
                        </div>
                      </div>
                    </form><br><br>

                    <!--******************* Modal pour confirmer les infos saisies ***********************-->
                    <div class="modal fade" id="confirmer_process" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.enregistre_action_confirmer')?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <div class="table-responsive  mt-3">
                              <table class="table m-b-0 m-t-20">
                                <tbody>
                                  <tr>
                                    <td><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.labelle_nom_proc')?></strong></td>
                                    <td id="process_verifie" class="text-dark"></td>
                                  </tr>
                                  <tr>
                                    <td><i class="fa fa-database"></i> &nbsp;<strong><?= lang('messages_lang.labelle_nom_table')?></strong></td>
                                    <td id="table_verifie" class="text-dark"></td>
                                  </tr>
                                  <tr>
                                    <td><i class="fa fa-right-from-bracket"></i> &nbsp;<strong><?= lang('messages_lang.lien_action')?></strong></td>
                                    <td id="link_verifie" class="text-dark"></td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.bouton_modifier')?></button>
                            <a onclick="confirm()" style="float: right;margin: 2px" class="btn btn-info"><i class="fa fa-save" aria-hidden="true"></i> <?= lang('messages_lang.bouton_confirmer')?></a>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!--******************* Modal pour confirmer les infos saisies ***********************-->
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php'); ?>
</body>
</html>
<script type="text/javascript">
  $(document).ready(function(){

    $('#PROCESS_NOM,#TABLE').on('input', function(){

      if(this.id === "PROCESS_NOM")
      {
        $(this).val($(this).val().toUpperCase());
        $(this).val(this.value.substring(0,200));
      }

      if(this.id === "TABLE")
      {
      
        $(this).val(this.value.substring(0,50));
      }
    })
  })
</script>
<script type="text/javascript">
  $('#LINK').on('input paste change',function()
  {
    // $('#error_LINK').hide();
    $(this).val($(this).val().replace(/[\"'\\\.\,\{\}]/g, ''));
      
  });

</script>

<script type="text/javascript">
  function save()
  {
    var PROCESS_NOM = $('#PROCESS_NOM').val();
    var TABLE = $('#TABLE').val();
    var LINK = $('#LINK').val();
    var status = 2;

    $('#error_PROCESS_NOM, #error_TABLE').html('');
    if(PROCESS_NOM == '')
    {
      $('#error_PROCESS_NOM').html('<?= lang('messages_lang.labelle_et_error')?>');
      status = 1;
    }

    if(TABLE == '')
    {
      $('#error_TABLE').html('<?= lang('messages_lang.labelle_et_error')?>');
      status = 1;
    }
    if(LINK == '')
    {
      $('#error_LINK').show();
      $('#error_LINK').html('<?= lang('messages_lang.labelle_et_error')?>');
      status = 1;
    }


    if(status == 2){
      $('#process_verifie').html(PROCESS_NOM);
      $('#table_verifie').html(TABLE);
      $('#link_verifie').html(LINK);
      $('#confirmer_process').modal('show');
    }
  }
</script>
<script type="text/javascript">
  function confirm()
  {
    $("#MyFormData").submit();
  }
</script>











