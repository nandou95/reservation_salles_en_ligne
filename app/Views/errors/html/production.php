<!doctype html>
<html>
<head>
  <?php echo view('includes/header.php');?>
  <style>
    <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
  </style>
</head>
<body>
  <div class="container-xxl py-4 subpage_bg">
    <div class="container text-dark">
      <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8 center-side">
          <div class="center verticle_center">
            <div class="login_section">
              <div class="login_form">
                <fieldset>
                  <div class="form-group col-lg-12" id="message_login"></div>
                  <div class="form-group col-lg-12">
                    <form  method="POST" id="Myform">
                      <div  class="form-group col-lg-12">
                        <img width="600" src="/assets_new/images/minifinance-01.jpg" alt="#" />
                      </div>
                      <div class="form-group col-lg-12">
                        <h4 class="text-center">Un problème s'est produit</h4>
                        <h4 class="text-center">Veuillez réessayer</h4>
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
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
</body>
</html>
