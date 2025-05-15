<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation(); ?>
</head>
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
                <div class="card-header">
                  <div class="col-md-12 d-flex">
                    <div class="col-md-9" style="float: left;">
                      <h1 class="header-title text-black"><?= lang('messages_lang.labelle_liste_sfp') ?></h1>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?=base_url('pip/Source_finance_bailleur')?>" style="float: right;margin-right: 80px;margin-top:15px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.link_list') ?></a>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive container " style="margin-top:50px">
                    <form action="<?=base_url('pip/Modify_data')?>" id="Myform" method="POST" enctype="multipart/form-data">
                      <div class="card-body">
                        <div class="row">
                          <?php
                          if(session()->getFlashKeys('alert'))
                          {
                            ?>
                            <div class="col-md-12">
                              <div class="w-100 bg-danger text-white text-center" id="messageerror">
                                <?php echo session()->getFlashdata('notification')['messageerror']; ?>
                              </div>
                            </div>
                            <?php
                          }
                          ?>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for=""><?= lang('messages_lang.th_code_bailleur') ?><span style="color: red;">*</span></label>
                              <input type="text" class="form-control" id="CODE" name="CODE" value="<?=$bailleur['CODE_BAILLEUR']?>">
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('CODE'); ?>
                              <?php endif ?>
                              <span class="text-danger" id="CODE"></span>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="form-group">
                              <label for=""><?= lang('messages_lang.th_nom_source_finance') ?><span style="color: red;">*</span></label>
                              <input type="text" class="form-control"  id="Names" name="Names" value="<?=$bailleur['NOM_SOURCE_FINANCE']?>">
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('Names'); ?>
                              <?php endif ?>
                              <span class="text-danger" id="Names"></span>
                            </div>
                          </div>
                          <input type="hidden" name="RowId" value="<?=$bailleur['ID_SOURCE_FINANCE_BAILLEUR']?>">
                          <div id="bouton_cart" class="col-md-12"><br>
                            <button id="bouton_envoyer" type="submit" class="btn btn-primary btn-block"><?= lang('messages_lang.bouton_modifier') ?> <span id="loading_cart"></span></button>
                          </div>
                        </div>
                      </form>
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
  <?php echo view('includesbackend/scripts_js.php'); ?>
</body>
</html>

<script type="text/javascript">
  $('#ENJEUX_ID').select2({
   width: '100%',
   placeholder: "<?= lang('messages_lang.labelle_selecte') ?>",
   allowClear: false });
 </script>