<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
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
                </div>
                <div class="card-body">
                  <fieldset>
                    <div class="form-group col-lg-12"></div>
                    <div class="form-group col-lg-12">
                      <?php $validation = \Config\Services::validation(); ?>
                      <form action="<?=base_url('Change_Password/new_password')?>" method="POST" id="Myform">
                        <div class="form-group col-lg-12">
                          <h4 class="text-center">Modification du mot de passe</h4>
                        </div>
                        <div class="w-100 bg-danger text-white text-center" id="show_message" ><?=$message;?></div>
                        <div class="form-group">
                          <label><strong>Ancien mot de passe</strong></label>
                          <input type="password" id="OLD_PASSWORD" class="form-control" name="OLD_PASSWORD" autofocus>
                          <div class="form-group col-lg-12">
                            <font id="error_OLD_PASSWORD" color="red"></font>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('OLD_PASSWORD'); ?>
                            <?php endif ?>
                          </div>
                        </div>

                        <div class="form-group">
                          <label><strong>Nouveau mot de passe</strong></label>
                          <input type="password" id="NEW_PASSWORD" class="form-control" name="NEW_PASSWORD">
                          <div class="form-group col-lg-12">
                            <font id="error_NEW_PASSWORD" color="red"></font>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('NEW_PASSWORD'); ?>
                            <?php endif ?>
                          </div>
                        </div>

                        <div class="form-group">
                          <label><strong>Confirmer le nouveau mot de passe</strong></label>
                          <input type="password" id="NEW_PASSWORD_CONF" class="form-control" name="NEW_PASSWORD_CONF">
                          <div class="form-group col-lg-12">
                            <font id="error_NEW_PASSWORD_CONF" color="red"></font>
                            <?php if (isset($validation)) :?>
                              <?= $validation->getError('NEW_PASSWORD_CONF'); ?>
                            <?php endif ?>
                          </div>
                        </div>

                        <div class="form-row d-flex justify-content-between mt-4 mb-2">
                          <div class="form-group">
                            <div class="form-check ml-2">
                              <input class="form-check-input" type="checkbox" onclick="show_password()" id="basic_checkbox_1">
                              <label style="padding-top: 15px;" class="form-check-label" for="basic_checkbox_1"><strong>Afficher le mot de passe</strong></label>
                            </div>
                          </div>
                        </div>
                        <div class="text-center">
                          <button type="button" onclick="change()" id="sign" class="btn btn-primary btn-block">Modifier</button>
                        </div>
                      </form>
                    </div>
                  </fieldset>
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
<script>
  $(document).ready(function() {
    $('#message').delay(5000).hide('slow');
  });
</script>
<script>
  $('#NEW_PASSWORD_CONF').bind("cut copy paste",function(e) {
    e.preventDefault();
  });

  function show_password()
  {
    var x = document.getElementById("OLD_PASSWORD");
    var y = document.getElementById("NEW_PASSWORD");
    var z = document.getElementById("NEW_PASSWORD_CONF");
    if(x.type === "password")
    {
      x.type = "text";
    }
    else
    {
      x.type = "password";
    }

    if(y.type === "password")
    {
      y.type = "text";
    }
    else
    {
      y.type = "password";
    }

    if(z.type === "password")
    {
      z.type = "text";
    }
    else
    {
      z.type = "password";
    }
  }
</script>
<script>
  function change()
  {
    var OLD_PASSWORD = $('#OLD_PASSWORD').val();
    var NEW_PASSWORD = $('#NEW_PASSWORD').val();
    var NEW_PASSWORD_CONF = $('#NEW_PASSWORD_CONF').val();
    var statut = 2;

    $('#error_OLD_PASSWORD').html('');
    $('#error_NEW_PASSWORD').html('');
    $('#error_NEW_PASSWORD_CONF').html('');

    if(OLD_PASSWORD == '')
    {
      $('#error_OLD_PASSWORD').html('Le champs est obligatoire!');
      statut = 1
    }

    if(NEW_PASSWORD == '')
    {
      $('#error_NEW_PASSWORD').html('Le champs est obligatoire!');
      statut = 1
    }

    if(NEW_PASSWORD_CONF == '')
    {
      $('#error_NEW_PASSWORD_CONF').html('Le champs est obligatoire!');
      statut = 1
    }

    if(NEW_PASSWORD != NEW_PASSWORD_CONF)
    {
      $('#error_NEW_PASSWORD_CONF').html('Les deux mots de passe ne sont pas identiques!');
      statut = 1
    }

    if(statut == 2)
    {
      $('#Myform').submit();
    }
  }
</script>