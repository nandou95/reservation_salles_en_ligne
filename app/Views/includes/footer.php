
<div class="dash_footer">
 <!-- Footer Start -->
    <div class="container-fluid footer text-white py-5 px-sm-3 px-md-5">
        <div class="row pt-5">
            <div class="col-lg-7 col-md-6">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <h3 class="mb-4">Nous contacter</h3>
                        <p><i class="fa fa-map-marker-alt mr-2"></i>Bujumbura, rue 26 Burundi</p>
                        <p><i class="fa fa-phone-alt mr-2"></i>+2577852546</p>
                        <p><i class="fa fa-envelope mr-2"></i>info@besd.bi</p>
                        <div class="d-flex justify-content-start mt-4">
                            <a class="btn btn-outline-light btn-social mr-2" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-social mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-social mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                      
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <h3 class=" mb-4">Nos Partenaires</h3>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-white mb-2" href="<?= base_url('Home'); ?>"><i class="fa fa-angle-right mr-2"></i>Accueil</a>
                            <a class="text-white mb-2" href="<?= base_url('Home'); ?>#apropos"><i class="fa fa-angle-right mr-2"></i>Apropos</a>
                            <a class="text-white mb-2" href="<?= base_url('Home'); ?>#FAQ"><i class="fa fa-angle-right mr-2"></i>FAQ</a>
                            <a class="text-white mb-2" href="<?= base_url('Contacts'); ?>"><i class="fa fa-angle-right mr-2"></i>Contact</a>
                            <!-- <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>FAQ</a> -->
                            <a class="text-white mb-2" href="<?=base_url('Login_Front')?>"><i class="fa fa-angle-right mr-2"></i>Se connecter</a>
                          
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-6 mb-5 Newsletter">
                <h3 class=" mb-4">Newsletter</h3>
                <p>Contactez nous par vos Newsletter</p>
                <div class="w-100">
                    <div class="input-group">
                        <input type="text" id="EMAIL" name="EMAIL" class="form-control border-light" style="padding: 30px;" placeholder="Votre Addresse Email ici">
                        <div class="input-group-append">
                            <button id="btn_btn" onclick="newsletter()" class="btn px-4">Enregistrer <span id="loading_btn"></span></button>
                        </div>
                    </div>
                    <div id="errorEMAIL" class="text-danger"></div>
                   <div id="ermessage_email_invalide" class="text-danger"></div>
                   <div id="ermessage_email_existe" class="text-danger"></div>
                   <div id="ermessage_success" class="text-success"></div>

                </div>
            </div>
        </div>
    </div>


<div class="container-fluid bg-dark  copyright text-white border-top py-4 px-sm-3 px-md-5" style="border-color: #3E3E4E !important;">
        <div class="row">
            <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0 text-center">
  
             <p>   &copy;<?=date('Y')?> <strong> Copyright</strong> &nbsp; &nbsp; <span>BESD </span>
                </p>
            </div>
            <div class="col-lg-6 text-center text-md-right">
            
         Développé par <a href="www.mediabox.bi">Mediabox  <img src="<?=base_url()?>/assets_frontend/img/Mediabox.png" alt="Mediabox"></a>
            </div>
        </div>
    </div>

 <!-- Footer End -->
</div>

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="<?=base_url()?>/assets_frontend/lib/easing/easing.min.js"></script>
    <script src="<?=base_url()?>/assets_frontend/lib/waypoints/waypoints.min.js"></script>
    <script src="<?=base_url()?>/assets_frontend/lib/counterup/counterup.min.js"></script>
    <script src="<?=base_url()?>/assets_frontend/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>



  <!-- Vendor JS Files -->
  <script src="<?=base_url()?>/assets_frontend/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?=base_url()?>/assets_frontend/assets/vendor/aos/aos.js"></script>
  <script src="<?=base_url()?>/assets_frontend/assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="<?=base_url()?>/assets_frontend/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="<?=base_url()?>/assets_frontend/assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="<?=base_url()?>/assets_frontend/assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="<?=base_url()?>/assets_frontend/assets/vendor/php-email-form/validate.js"></script>


    <!-- Contact Javascript File -->
    <script src="<?=base_url()?>/assets_frontend/mail/jqBootstrapValidation.min.js"></script>
    <script src="<?=base_url()?>/assets_frontend/mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="<?=base_url()?>/assets_frontend/js/main.js"></script>

     <!-- Tom select -->
         <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>


    
    <script src="<?=base_url()?>/assets_frontend/dataTables/js/jquery.dataTables.min.js"></script>

    <script>
      var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
    /* Toggle between adding and removing the "active" class,
    to highlight the button that controls the panel */
    this.classList.toggle("active");

    /* Toggle between hiding and showing the active panel */
    var panel = this.nextElementSibling;
    if (panel.style.display === "block") {
      panel.style.display = "none";
    } else {
      panel.style.display = "block";
    }
  });
}
    </script>

    <script type="text/javascript">
    //Fonction pour verifier si le Email est valider
  function checkEmail(email) {
       var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
       return re.test(email);
   }
</script>

<script type="text/javascript">
  function newsletter(argument) {

    var statut = true;

    if ($("#EMAIL").val()=='') 
    {
        $('#errorEMAIL').text('Ce champ est obligatoire');
        $('#ermessage_email_existe').text("");
        $('#ermessage_email_invalide').text('');
        return false;
    }else{
        $('#errorEMAIL').text('');
        var email = document.getElementById("EMAIL").value;
        if (checkEmail(email)) {
             $('#ermessage_email_invalide').text('');
        } else {
             $('#ermessage_email_invalide').text('Email invalide');
             $('#ermessage_email_existe').text("");
             return false;
        }
    }

    var EMAIL = $("#EMAIL").val();

    if (statut==true) {

      $.ajax({
          url : "<?=base_url('/newsletter/')?>",
          type: "POST",
          dataType: "JSON",
          data: {EMAIL:EMAIL},
          beforeSend: function() {
            $('#loading_btn').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            $('#btn_btn').attr('disabled',true);
          },
          success: function(data)
          {
            if (data.statut==1) {
              $('#ermessage_email_existe').text("L'Email Existe déjà dans notre système informatique !");
              $('#loading_btn').html("");
              $('#btn_btn').attr('disabled',false);
            }else if (data.statut==2) {
              
              setTimeout(()=>{
                      $('#ermessage_success').text("Enregistrement reussi avec succès");
                      $('#ermessage_email_existe').text('');

                      $('#loading_btn').html("");
                      $('#btn_btn').attr('disabled',false);

                      //vider les input
                      $('#EMAIL').val('');
                  },5000); 
            }
          }
      });
    }
    
  }
</script>