<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includes/header.php');?>
</head>
<body>
  <div class="container-xxl  subpage_bg">
    <div class="row">
      <div class="col-md-12 center-side">
        <div class="login_form">
          <div class="form-group">
            <form  method="POST" id="Myform">
              <div  class="form-group ">
                <img src="/assets_new/images/minifinance-01.png" alt="#" />
              </div>
              <div class="form-group ">
                <h3 class="text-center"><?=lang('messages_lang.label_seconnect')?></h3>
              </div>
              <div class="form-group " id="error_message">
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
              </div>
              <div class="form-group" id="message_login"></div>
              <div class="form-group">
                <label><strong><?=lang('messages_lang.label_nom_utilisateur')?></strong></label><span id="loading_cart"></span></button>
                <input type="text" class="form-control" name="inputUsername"
                id="inputUsername">
                <div class="form-group">
                  <font id="errorinputUsername" color="red"></font>
                </div>
              </div>

              <div class="form-group">
                <label><strong><?=lang('messages_lang.label_mot_de_passe')?></strong></label>
                <input type="password" id="inputPassword" name="inputPassword" class="form-control">
                <div class="form-group col-lg-12">
                  <font id="errorinputPassword" color="red"></font>
                </div>
              </div>

              <div class="form-row d-flex justify-content-between mt-4 mb-2">
                <div class="form-group">
                  <div class="form-check ml-2">
                    <input class="form-check-input" type="checkbox" onclick="show_password()" id="basic_checkbox_1">
                    <label class="form-check-label" for="basic_checkbox_1"><p class="form_pass"><?=lang('messages_lang.label_affich_mot_passe')?></p></label>
                  </div>
                </div>
                <div class="form-group">
                  <a  href="/Recover_pwd"><?=lang('messages_lang.label_oublie_passwrd')?></a>
                </div>
              </div>

              <div class="text-center">
                <button type="button " onclick="login()" id="sign" class="btn"><?=lang('messages_lang.btn_connexion')?></button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
</body>
</html>
<script type="text/javascript">
  $(document).ready(function()
  {
    $('#message').delay(5000).hide('slow');
  });

  function show_password()
  {
    var x = document.getElementById("inputPassword");
    if (x.type === "password")
    {
      x.type = "text";
    }
    else
    {
      x.type = "password";
    }
  }
</script>
<script>
  function login()
  {
    $('#error_message').html("");
    var inputUsername= $('#inputUsername').val();
    var inputPassword= $('#inputPassword').val();
    $('#sign').attr('disabled', true);
    $('#message_login').html('');
    $('#errorinputUsername').html('');
    $('#errorinputPassword').html('');
    var statutvalidation=1;
    if ($('#inputUsername').val() == '')
    {
      statutvalidation = 0;
      $('#errorinputUsername').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
    }

    if ($('#inputPassword').val() == '')
    {
      statutvalidation = 0;
      $('#errorinputPassword').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
    }

    if(statutvalidation == 1)
    {
      $.ajax(
      {
        url : "<?=base_url('/Login_Ptba/login/')?>",
        type: "POST",
        dataType: "JSON",
        data: {inputUsername:inputUsername,inputPassword:inputPassword},
        beforeSend: function()
        {
          // $('#loading_cart').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
          $('#sign').attr('disabled',true);
        },
        success: function(data)
        {
          if(data.usersExiste==0 || data.usersExiste==3)
          {
            $('#error_message').html("<div class='alert alert-danger text-center'><?=lang('messages_lang.mesg_connection_echoue')?></div>");
            $('#loading_cart').html("");
            $('#sign').attr('disabled',false);
          }
          else if(data.usersExiste==1)
          {
            window.location.href="<?= base_url('Login_Ptba/homepage')?>";
          }
          else if (data.usersExiste==2)
          {
            $('#error_message').html("<div class='alert alert-danger text-center'><?=lang('messages_lang.mesg_consulte_admin')?></div>");
            $('#loading_cart').html("");
            $('#sign').attr('disabled',false);
          }
        }
      });
    }
    else
    {
      $('#error_message').html("");
      $('#sign').attr('disabled', false);
    }
  }
</script>
