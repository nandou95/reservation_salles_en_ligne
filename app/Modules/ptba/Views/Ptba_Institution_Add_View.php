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
                <div class="car-body">
                  <br>
                  <div class="row col-md-12">
                    <div class="col-md-6">
                      <h1 class="header-title text-black">
                        <?=$titre;?>
                      </h1>
                    </div>
                    <div class="col-md-6">
                      <div style="float: right;">
                        <a href="<?=base_url('ptba/Ptba_Institution/index') ?>" style="float: right;margin: 4px" class="btn btn-primary"><i class="fa fa-reply-all" aria-hidden="true"></i> Liste </a>
                      </div>
                    </div>
                  </div>

                  <form id='MyFormData' method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label><?=lang('messages_lang.label_institution')?><font color="red">*</font></label>
                            <select id="INSTITUTION_ID" name="INSTITUTION_ID" class="form-control">
                              <option value=""><?=lang('messages_lang.selection_message')?></option>
                              <?php
                              foreach($institutions as  $value)
                              {
                                ?>
                                <option value="<?=$value->INSTITUTION_ID?>"><?=$value->DESCRIPTION_INSTITUTION?></option>
                                <?php
                              }
                              ?>
                            </select>
                            <span class="text-danger" id="error_institution"></span>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label><?=lang('messages_lang.label_ann_exerc')?> <font color="red">*</font></label>
                            <select id="EXERCICE_ID" name="EXERCICE_ID" class="form-control">
                              <option value="">--- <?=lang('messages_lang.selection_message')?> ---</option>
                              <?php
                              foreach($annees as $key)
                              {
                                ?>
                                <option value="<?= $key->EXERCICE_ID?>"><?= $key->ANNEE ?></option>
                                <?php
                              }
                              ?>
                            </select>
                            <span class="text-danger" id="error_annee"></span>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label><?=lang('messages_lang.tranche_institution_detail')?> 1<font color="red">*</font></label>
                            <input type="number" class="form-control" name="TRANCHE_UN" id="TRANCHE_UN" min="0">
                            <span class="text-danger" id="error_un"></span>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label><?=lang('messages_lang.tranche_institution_detail')?> 2<font color="red">*</font></label>
                            <input type="number" class="form-control" name="TRANCHE_DEUX" id="TRANCHE_DEUX" min="0">
                            <span class="text-danger" id="error_deux"></span>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label><?=lang('messages_lang.tranche_institution_detail')?> 3<font color="red">*</font></label>
                            <input type="number" class="form-control" name="TRANCHE_TROIX" id="TRANCHE_TROIX" min="0">
                            <span class="text-danger" id="error_trois"></span>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label><?=lang('messages_lang.tranche_institution_detail')?> 4<font color="red">*</font></label>
                            <input type="number" class="form-control" name="TRANCHE_QUATRE" id="TRANCHE_QUATRE" min="0">
                            <span class="text-danger" id="error_quatre"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                  <div style="float:right" class="mt-4">
                    <a class="btn btn-primary" onclick="insert()" type="button"><?=lang('messages_lang.bouton_enregistrer')?></a>
                  </div>
                </div><br><br>
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
  function insert()
  {
    var statut = true;
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var EXERCICE_ID = $("#EXERCICE_ID").val();
    var TRANCHE_UN = $("#TRANCHE_UN").val();
    var TRANCHE_DEUX = $("#TRANCHE_DEUX").val();
    var TRANCHE_TROIX = $("#TRANCHE_TROIX").val();
    var TRANCHE_QUATRE = $("#TRANCHE_QUATRE").val();

    if(INSTITUTION_ID == "")
    {
      statut = false;
      $("#error_institution").html("<?=lang('messages_lang.error_sms')?>");
    }
    else
    {
      $("#error_institution").html("");
      statut = true;
    }

    if(EXERCICE_ID == "")
    {
      statut = false;
      $("#error_annee").html("<?=lang('messages_lang.error_sms')?>");
    }
    else
    {
      $("#error_annee").html("");
      statut = true;
    }

    if(TRANCHE_UN == "")
    {
      statut = false;
      $("#error_un").html("<?=lang('messages_lang.error_sms')?>");
    }
    else
    {
      $("#error_un").html("");
      statut = true;
    }

    if(TRANCHE_DEUX == "")
    {
      statut = false;
      $("#error_deux").html("<?=lang('messages_lang.error_sms')?>");
    }
    else
    {
      $("#error_deux").html("");
      statut = true;
    }

    if(TRANCHE_TROIX == "")
    {
      statut = false;
      $("#error_trois").html("<?=lang('messages_lang.error_sms')?>");
    }
    else
    {
      $("#error_trois").html("");
      statut = true;
    }

    if(TRANCHE_QUATRE == 0)
    {
      statut = false;
      $("#error_quatre").html("<?=lang('messages_lang.error_sms')?>");
    }
    else
    {
      $("#error_quatre").html("");
      statut = true;
    }

    if(statut == true)
    {
      var form_data = new FormData($("#MyFormData")[0]);
      url = "<?=base_url('ptba/Ptba_Institution/create')?>";
      $.ajax(
      {
        url: url,
        type: 'POST',
        dataType: 'JSON',
        data: form_data,
        contentType: false,
        cache: false,
        processData: false,
        success: function(data)
        {
          if (data.statut == true)
          {
            window.location.href = '<?=base_url('ptba/Ptba_Institution')?>';
          }
        }
      });
    }
  }
</script>


