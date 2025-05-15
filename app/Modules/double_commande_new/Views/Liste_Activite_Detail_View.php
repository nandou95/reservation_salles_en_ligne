<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation(); ?>
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
                <div style="float: right;">

                  <a href="javascript:history.back()" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-none="true"></i> Liste</a>

                </div>
                <div class="car-body">
                  <br>                    
                  <div class=" container " style="width:90%">

                    <form enctype='multipart/form-data' name="myEtape2" id="myEtape2" action="<?=base_url('double_commande_new/Phase_Administrative_Budget/save_etape2/')?>" method="post" >

                      <div class="container">
                        <center class="ml-5" style="height=100px;width:90%" >
                          <div class="row" style="border:1px solid #ddd;border-radius:5px">
                            <div class="col-md-12 mt-2" style="margin-bottom:50px">
                              <?php include  'includes/Detail_View.php'; ?>
                              <hr>
                            </div>
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
