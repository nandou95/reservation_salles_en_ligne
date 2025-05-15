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
                <h3 class="text-center">Mot de passe oublie</h3>
              </div>
              <div class="form-group " id="error_message"></div>
              <div class="form-group">
                <label><strong>Nom d'utilisateur ou Email</strong></label>
                <input type="text" autofocus="" class="form-control" name="inputUsername" id="inputUsername">
                <div class="form-group"><font id="errorinputUsername" color="red"></font></div>
              </div>

              <div class="form-group">
                <label><strong>Confirmer le nom d'utilisateur ou Email</strong></label>
                <input type="text" autofocus="" class="form-control" name="inputConfirmeUsername" id="inputConfirmeUsername">
                <div class="form-group"><font id="errorinputConfirmeUsername" color="red"></font></div>
              </div>

              <div class="form-row d-flex justify-content-between mt-4 mb-2">

                <div class="form-group">
                  <a  href="<?=base_url()?>">Se connecter</a>
                </div>
              </div>
              
            </form>
            <div class="text-center">
              <button onclick="recover()" id="sign" class="btn">Récupérer</button>
            </div>
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
<script>
  function recover()
  {
    var status=1;
    var inputUsername=$('#inputUsername').val();
    $('#errorinputUsername').html('');
    $('#errorinputConfirmeUsername').html('');
    if($('#inputUsername').val()=='')
    {
      status=2;
      $('#errorinputUsername').html('Ce champ est obligatoire');
    }
    else if($('#inputConfirmeUsername').val()=='')
    {
      status=2;
      $('#errorinputConfirmeUsername').html('Ce champ est obligatoire');
    }
    else if ($('#inputUsername').val()!=$('#inputConfirmeUsername').val())
    {
      status=2;
      $('#errorinputConfirmeUsername').html('Les noms d’utilisateur ou email ne correspondent pas');
    }
    
    if(status==1)
    {
      $.ajax(
      {
        url : "<?=base_url('/Recover_pwd/set_pwd/')?>",
        type: "POST",
        dataType: "JSON",
        data: {inputUsername:inputUsername},
        beforeSend: function()
        {
          $('#sign').attr('disabled',true);
        },
        success: function(data)
        {
          if(data.usersExiste==0)
          {
            $('#error_message').html("<div class='alert alert-danger text-center'>Votre nom d’utilisateur ou email n’existe pas</div>");
            $('#sign').attr('disabled',false);
          }
          else if(data.usersExiste==1)
          {
            window.location.href="<?=base_url('Login_Ptba')?>";
          }
          else if (data.usersExiste==2)
          {
            $('#error_message').html("<div class='alert alert-danger text-center'>Votre nom d’utilisateur ou email est inactif</div>");
            $('#sign').attr('disabled',false);
          }
        }
      });
    }
  }
</script>