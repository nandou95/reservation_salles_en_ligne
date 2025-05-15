<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <?php $validation = \Config\Services::validation(); ?>
  <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
  <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
  <style type="text/css">
    .modal-signature
    {
      flex-wrap: wrap;
      align-items: center;
      justify-content: flex-end;
      border-bottom-right-radius: .3rem;
      border-bottom-left-radius: .3rem
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                    <div class="col-9" style="float: left;">
                      <h1 class="header-title text-dark">Importation des données du PTBA Revise</h1>
                    </div>
                  </div>
                </div>
                <div class="card-body" style="margin-top: -10px">
                  <form method="post" name="myform" id="myform" action="<?= base_url('donnees/Data_Ptba_Revise/importfile') ?>" class="form-group row needs-validation p-5" enctype="multipart/form-data">
                    <div class="row">
                      <div class="form-group col-md-8">
                        <label for="Nom" class="form-label">Fichier Excel des PTBA Revise<font color="red">*</font></label>
                        <div class="input-group has-validation">
                          <input accept="" type="file"  class="form-control " name="UPLOAD_DOCUMENT" id="UPLOAD_DOCUMENT" placeholder="Mettez le document " value="">
                        </div>
                        <span class="text-danger" id="error_docu"></span>
                      </div>
                      <div class="col-md-4" style="float: right;margin-top: 30px;">
                        <a onclick="importfile()" style="float: right;margin: 2px" class="btn btn-primary"><i class="fa fa-file-import" aria-hidden="true"></i> Importer</a>
                      </div>
                    </div>
                  </form>
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
  function importfile()
  {
    var UPLOAD_DOCUMENT = $('#UPLOAD_DOCUMENT').val();
    $('#error_docu').html('');

    var statut = 2;
    if (UPLOAD_DOCUMENT == '') 
    {
      $('#error_docu').html('Le champ est obligatoire');
      statut = 1;
    }

    var url;
    if(statut == 2)
    {
      $('#myform').submit();
    }
  }
</script>