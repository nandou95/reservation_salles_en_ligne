<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo view('includes/header.php');?>
</head>

<body>
    <?php echo view('includes/menu.php');?>

    <!-- Contact End -->
    <!-- Contact Start -->
    <div class="container-xxl py-4 subpage_bg">
        <div class="container text-dark">

            <div class="row">
                <!-- <div class="col-md-6 form-left">

                    <form class="p-5 " >
                    <div class="login-header text-center">
                        <h3>Profils</h3>
                          <ul class="form-left-list">
                            <li> <i class="fa fa-user"></i> &nbsp; <a href="Sinscrire_ministere.html">Ministère</a></li>
                            <li> <i class="fa fa-user"></i> &nbsp; <a href="Sinscrire_partenaire.html">Partenaire</a></li>
                            <li> <i class="fa fa-user"></i> &nbsp;<a href="Sinscrire_gestion.html">Unité de gestion <br> &nbsp; &nbsp; &nbsp;&nbsp;de projets</a></li>
                            <li> <i class="fa fa-user"></i> &nbsp;<a href="Sinscrire_primature.html">Primature</a></li>
                            <li> <i class="fa fa-user"></i> &nbsp;<a href="Sinscrire_presidence.html">Présidence</a></li>
                          </ul>
                       </div> 

                       <div class="footer-text">
                        <p>Les termes et Conditions</p>
                        <small>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Autem temporibus perferendis deleniti exercitationem neque beatae odio consectetur amet dicta voluptate repellendus molestiae, veritatis corporis repudiandae tempore quae. Eligendi, harum accusamus?
                           
                        </small>
                        <br>
                        <br>
                        <br>
                        
                       </div>
                    </form>
                </div> -->
                <div class="col-md-2"></div>
                <div class="col-md-8 center-side">

                <div class="center verticle_center full_height">
                <div class="login_section">
                    <div class="logo_login">
                        <div class="center">
                            <img width="400" src="/assets/backend/images/minifinance-01.jpg" alt="#" />
                        </div>
                    </div>
                    <div class="login_form">
                        <div class="form-group col-lg-12">
                            <h4 class="text-center">S'authentifier</h4>
                        </div>
                        <fieldset>
                            <div class="form-group col-lg-12" id="message_login"></div>
                            <div class="form-group col-lg-12">
                                <form action="/Ptba_login/do_login" method="POST" id="Myform">
                                    <div class="form-group">
                                        <label><strong>Nom d'utilisateur</strong></label>
                                        <input type="text" autofocus="" class="form-control" name="inputUsername"
                                            id="inputUsername">
                                        <div class="form-group col-lg-12">
                                            <font id="errorinputUsername" color="red"></font>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Mot de passe</strong></label>
                                        <input type="password" id="inputPassword" class="form-control"
                                            name="inputPassword">
                                        <div class="form-group col-lg-12">
                                            <font id="errorinputPassword" color="red"></font>
                                        </div>
                                    </div>
                                    <div class="form-row d-flex justify-content-between mt-4 mb-2">
                                        <div class="form-group">
                                            <div class="form-check ml-2">
                                                <input class="form-check-input" type="checkbox"
                                                    onclick="show_password()" id="basic_checkbox_1">
                                                <label class="form-check-label" for="basic_checkbox_1"><strong>Afficher
                                                        le mot de passe</strong></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <a href="/Recover_pwd">Mot de passe oublié ?</a>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="button" onclick="login()" id="sign"
                                            class="btn btn-primary btn-block">CONNEXION</button>
                                    </div>
                                </form>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>

                </div>
            </div>

        </div>


        <!-- End of Form -->
    </div>

    </div>


    <?php echo view('includes/footer.php');?>



    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous">
    </script>

</body>

</html>


<script type="text/javascript">
$(document).ready(function() {
    $('#message').delay(5000).hide('slow');
});

function show_password() {
    var x = document.getElementById("inputPassword");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}
</script>
<script>
function login() {
    $('#sign').text('Connexion');
    $('#sign').attr('disabled', true);
    $('#message_login').html('');
    $('#errorinputUsername').html('');
    $('#errorinputPassword').html('');
    var statutvalidation = 1;
    if ($('#inputUsername').val() == '') {
        statutvalidation = 0;
        $('#errorinputUsername').html('Le champ est obligatoire');
    }

    if ($('#inputPassword').val() == '') {
        statutvalidation = 0;
        $('#errorinputPassword').html('Le champ est obligatoire');
    }

    if (statutvalidation == 1) {
        var formData = $('#Myform').serialize();
        $.ajax({
            url: "/Ptba_login/check_login",
            type: "POST",
            data: formData,
            dataType: "JSON",
            success: function(data) {
                if (data.status) {
                    $('#message_login').html("<center><span class='text text-success'>" + data.message +
                        "</span></center>");
                    $('#sign').attr('disabled', true);
                    setTimeout(function() {
                        $('#Myform').submit();
                    }, 2000);
                } else {
                    $('#message_login').html("<span class='text text-danger'>" + data.message + "</span>");
                }
                $('#sign').text('Connexion');
                $('#sign').attr('disabled', false);
            }
        });
    } else {
        $('#sign').text('Connexion');
        $('#sign').attr('disabled', false);
    }
}
</script>