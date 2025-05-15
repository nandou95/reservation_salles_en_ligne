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
                <input type="hidden" id="doc" name="doc" value="<?=$returns?>">
                <input type="hidden" id="provenance" name="provenance" value="<?=$provenance?>">
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

  $(document).ready(function () 
  {
    $('#pdf').html($('#doc').val())
    $('#note').modal("show");
  });

  function redirection()
  {
    var provenance=$('#provenance').val();
    if(provenance==1)
    {
      window.location.href = "<?= base_url() ?>/double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Valide";
    }
    else
    {
      window.location.href = "<?=base_url()?>/double_commande_new/Liquidation_Double_Commande/get_liquid_valider";
    }
  }
</script>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(100000);
</script >

<div class='modal fade' id='note' data-backdrop="static">
  <div class='modal-dialog modal-lg'>
    <div class='modal-content'>
     <div class="modal-header">
      <center class="success" id="message"><?=$message?></center>
      <button type="button" class="close" onclick="redirection()" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class='modal-body'>
      <center id="pdf">

      </center>
      <!-- <div>
        Modifier la disposition des signataires
        <hr>
        <select>
          <option>Sélectionner</option>
        </select>
      </div> -->
    </div>
    <div class='modal-footer'>
      <button class='btn btn-primary btn-md' onclick="redirection()">
        Quitter
      </button>
    </div>
    </div>
  </div>
</div>


