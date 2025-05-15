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
                  <a href="<?php echo base_url('double_commande_new/Menu_Engagement_Budgetaire/get_sans_bon_engagement')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                </div>
                <div class="car-body">

                  <h4 style="margin-left:4%;margin-top:10px"> <?=$etape1_prime?></h4>
                  <br>
                  <!-- debut -->
                  
                  <div class="container" style="width:90%;">
                    <div id="accordion">
                      <div  class="card-header" id="headingThree" style="float: left;">
                        <h5 class="mb-0">
                          <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><?= lang('messages_lang.histo_btn') ?></button>
                        </h5>
                      </div>  
                    </div><br><br>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                      <?php include  'includes/Detail_View.php'; ?>
                    </div>
                  </div>
                  <!-- fin -->
                  <br><br>
                  <div class=" container " style="width:90%">
                    <form enctype='multipart/form-data' name="myEtape1_Prime" id="myEtape1_Prime" action="<?=base_url('double_commande_new/Phase_Administrative_Budget/save_etape1_prime/')?>" method="post" >
                      <div class="container">

                       <?php
                       if(session()->getFlashKeys('alert'))
                       {
                        ?>
                        <center class="ml-5" style="height=100px;width:90%" >
                          <div class="w-100 bg-danger text-white text-center"  id="message">
                            <?php echo session()->getFlashdata('alert')['message']; ?>
                          </div>
                        </center>
                        <?php
                      } ?>

                      <div class="row" style="border:1px solid #ddd;border-radius:5px">
                        <div class="col-md-12 mt-2" style="margin-bottom:50px">

                          <div class="row">
                            
                            <input type="hidden" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$info['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">

                            <input type="hidden" id="EXECUTION_BUDGETAIRE_ID" name="EXECUTION_BUDGETAIRE_ID" value="<?=$info['EXECUTION_BUDGETAIRE_ID']?>">

                            <input type="hidden" name="EXECUTION_BUDGETAIRE_DETAIL_ID" name="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?= $info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">

                            <input type="hidden" name="ETAPE_DOUBLE_COMMANDE_ID" id="ETAPE_DOUBLE_COMMANDE_ID" value="<?=$info['ETAPE_DOUBLE_COMMANDE_ID']?>">

                            <input type="hidden" name="NUMERO_BON_ENGAGEMENT" id="NUMERO_BON_ENGAGEMENT"/>

                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_date_eng') ?> <font color="red">*</font></label>
                              <input type="date" value="<?= date('Y-m-d')?>" max="<?= date('Y-m-d')?>" min="<?=date('Y-m-d')?>" class="form-control" onkeypress="return false" name="DATE_ENG_BUDGETAIRE" id="DATE_ENG_BUDGETAIRE" value="<?= set_value('DATE_ENG_BUDGETAIRE')?>">
                              <font color="red" id="error_DATE_ENG_BUDGETAIRE"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DATE_ENG_BUDGETAIRE'); ?>
                              <?php endif ?>
                              <br>
                            </div>
                            <div class="col-md-6">
                              <div class="form-froup">
                                <label for=""><?= lang('messages_lang.label_num') ?> <font color="red">*</font></label>
                                <div class="row px-2">
                                  <span style=""> BE<?= $info['CODE_INSTITUTION']?> - </span>

                                  <input type="text" pattern="[0-9]*" class="mx-1" style="width: 170px" oninput="" onkeyup="number(); checkInput('<?= $info['CODE_INSTITUTION']?>', '<?= $info['ANNEE_DESCRIPTION']?>')" onpaste="return false;" onkeydown="number(); checkInput('<?= $info['CODE_INSTITUTION']?>', '<?= $info['ANNEE_DESCRIPTION']?>')" maxlength="30" type="text" class="form-control" name="NUMERO_BON_ENGAGEMENT_PARTIE" id="NUMERO_BON_ENGAGEMENT_PARTIE" />

                                  <span style=""> / <?= $info['ANNEE_DESCRIPTION']?></span>
                                </div>
                                <input disabled type="text" id="be" style="width:270px">
                                <br>
                                <font color="red" id="error_NUMERO_BON_ENGAGEMENT"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('NUMERO_BON_ENGAGEMENT'); ?>
                                <?php endif ?>
                              </div>
                              <br>
                            </div>

                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_date_tra') ?> (CED)<font color="red">*</font></label>
                              <input type="date" class="form-control" onkeypress="return false" name="DATE_TRANSMISSION" value="<?= date('Y-m-d') ?>" id="DATE_TRANSMISSION" max="<?= date('Y-m-d')?>" min="<?=date('Y-m-d')?>">
                              <font color="red" id="error_DATE_TRANSMISSION"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DATE_TRANSMISSION'); ?>
                              <?php endif ?>
                            </div>

                          </div>
                        </div> 
                      </div> 
                      <div style="float: right;" class="col-md-2 mt-5 " >
                      <div class="form-group " >
                        <a onclick="saveEtape1_Prime()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.label_enre') ?></a>
                      </div>
                    </div>         
                    </div> 
                    <br>
                  </div>
                </form><br><br>
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
  function checkInput(codeInst, anneeBudg) {
    var NUMERO_BON_ENGAGEMENT = $('#NUMERO_BON_ENGAGEMENT');
    var NUMERO_BON_ENGAGEMENT_PARTIE = $('#NUMERO_BON_ENGAGEMENT_PARTIE');
    if(NUMERO_BON_ENGAGEMENT_PARTIE.val()){
      NUMERO_BON_ENGAGEMENT_PARTIE.val(NUMERO_BON_ENGAGEMENT_PARTIE.val().replace(/\s+/g,''));
      if(!isNaN(NUMERO_BON_ENGAGEMENT_PARTIE.val())){
        let num = NUMERO_BON_ENGAGEMENT_PARTIE.val().trim();
        num = num.replace(/^0+/,"");
        num = num.replace('.', '');
        NUMERO_BON_ENGAGEMENT_PARTIE.val(num);

        if(!/[a-zA-Z]/.test(num)){
          let bon = 'BE'+codeInst+'-'+num+'/'+anneeBudg;
          NUMERO_BON_ENGAGEMENT.val(bon);

          $('#be').val(bon);
          $('#be').show();
        }
        else{
          
          NUMERO_BON_ENGAGEMENT_PARTIE.val(NUMERO_BON_ENGAGEMENT_PARTIE.val().replace(/[^\d]/g,''));
          statut=1;
        }

        if(/\./.test(num)){
          NUMERO_BON_ENGAGEMENT.val("");
          $('#be').val('');
          $('#be').hide();
        }
      }
      if(isNaN(NUMERO_BON_ENGAGEMENT_PARTIE.val()) || NUMERO_BON_ENGAGEMENT_PARTIE.val() == 0){
        NUMERO_BON_ENGAGEMENT_PARTIE.val(NUMERO_BON_ENGAGEMENT_PARTIE.val().replace(/[^\d]/g,''));
        
        statut=1;
      }
    }
    else{
      NUMERO_BON_ENGAGEMENT.val("");
      $('#be').val('');
      $('#be').hide();
      statut=1;
    }
  }
  function number()
  {
    var NUMERO_BON_ENGAGEMENT = $('#NUMERO_BON_ENGAGEMENT');
    var NUMERO_BON_ENGAGEMENT_PARTIE = $('#NUMERO_BON_ENGAGEMENT_PARTIE').val();
    $('#error_NUMERO_BON_ENGAGEMENT').html('');

    //condition pour que le bon NUMERO_BON_ENGAGEMENT.val() n'ait pas plus de 20 char -> NUMERO_BON_ENGAGEMENT_PARTIE doit avoir 5 char ou moins
    if (NUMERO_BON_ENGAGEMENT_PARTIE.trim().length > 5 &&
        !isNaN(NUMERO_BON_ENGAGEMENT_PARTIE) && 
        !NUMERO_BON_ENGAGEMENT_PARTIE.startsWith('0') &&
        !/[a-zA-Z]/.test(NUMERO_BON_ENGAGEMENT_PARTIE) &&
        !/\./.test(NUMERO_BON_ENGAGEMENT_PARTIE))
    {
      // NUMERO_BON_ENGAGEMENT.val("");
      $('#be').val('');
      $('#be').hide();
      statut=1;
      $('#error_NUMERO_BON_ENGAGEMENT').html("<?=lang('messages_lang.numer_eng')?>");
   }
 }
</script>

<script type="text/javascript">
  $('#message').delay('slow').fadeOut(3000);
  $('#be').hide();

</script >

<script type="text/javascript">
  function saveEtape1_Prime()
  {
    var NUMERO_BON_ENGAGEMENT_PARTIE = $('#NUMERO_BON_ENGAGEMENT_PARTIE').val();
    var NUMERO_BON_ENGAGEMENT = $('#NUMERO_BON_ENGAGEMENT').val();
    $('#error_NUMERO_BON_ENGAGEMENT').html('');
    var DATE_ENG_BUDGETAIRE = $('#DATE_ENG_BUDGETAIRE').val();
    $('#error_DATE_ENG_BUDGETAIRE').html('');
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    $('#error_DATE_TRANSMISSION').html('');

    // var PATH_BON_ENGAGEMENT = document.getElementById('PATH_BON_ENGAGEMENT');
    // $('#error_PATH_BON_ENGAGEMENT').html('');
    // var maxSize = 20000*1024;
    
    var statut=2;

    // if (PATH_BON_ENGAGEMENT.files.length === 0)
    // {
    //   $('#error_PATH_BON_ENGAGEMENT').html("<?//=lang('messages_lang.input_oblige')?>");
    //   statut = 1;
    // }else if (PATH_BON_ENGAGEMENT.files[0].size > maxSize)
    // {
    //   $('#error_PATH_BON_ENGAGEMENT').html("<?//=lang('messages_lang.pdf_max')?>");
    //   statut = 1;
    // }

    if (DATE_ENG_BUDGETAIRE=='') 
    {
      $('#error_DATE_ENG_BUDGETAIRE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }
    if (DATE_TRANSMISSION=='')
    {
      $('#error_DATE_TRANSMISSION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (NUMERO_BON_ENGAGEMENT_PARTIE=='' || !NUMERO_BON_ENGAGEMENT)
    {
     $('#error_NUMERO_BON_ENGAGEMENT').html("<?=lang('messages_lang.input_oblige')?>");
     statut=1;
   }

   if (NUMERO_BON_ENGAGEMENT_PARTIE.length > 5)
   {
     $('#error_NUMERO_BON_ENGAGEMENT').html("<?=lang('messages_lang.numer_eng')?>");
     statut=1;
   }

   var url;
   if(statut == 2)
   {
      // var PATH_BON_ENGAGEMENT = $('#PATH_BON_ENGAGEMENT').val();
      // var path = PATH_BON_ENGAGEMENT;
      // var doc = path.split("\\");
      // var bon= doc[doc.length-1];
      var DATE_ENG_BUDGETAIRE = moment(DATE_ENG_BUDGETAIRE, "YYYY/mm/DD");
      var DATE_ENG_BUDGETAIRE = DATE_ENG_BUDGETAIRE.format("DD/mm/YYYY");
      var DATE_TRANSMISSION = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
      var DATE_TRANSMISSION = DATE_TRANSMISSION.format("DD/mm/YYYY");

     $('#NUMERO_BON_ENGAGEMENT_verifie').html(NUMERO_BON_ENGAGEMENT);
     $('#DATE_ENG_BUDGETAIRE_verifie').html(DATE_ENG_BUDGETAIRE);
     $('#DATE_TRANSMISSION_verifie').html(DATE_TRANSMISSION);
     // $('#PATH_BON_ENGAGEMENT_verifie').html(bon);

     $("#etape1_prime_modal").modal("show");
   }

 }
</script>


<div class="modal fade" id="etape1_prime_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
     <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_titre') ?></h5>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body">
      <div class="table-responsive  mt-3">
        <table class="table m-b-0 m-t-20">
          <tbody>
           
          <tr>
            <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_eng') ?></strong></td>
            <td id="DATE_ENG_BUDGETAIRE_verifie" class="text-dark"></td>
          </tr>
            <tr>
             <td style="width:250px ;"><i class="fa fa-certificate"></i>&nbsp;<strong><?= lang('messages_lang.label_num') ?></strong></td>
             <td id="NUMERO_BON_ENGAGEMENT_verifie" class="text-dark"></td>
           </tr>
           <!-- <tr>
            <td><i class="fa fa-calendar"></i> &nbsp;<strong><?//= lang('messages_lang.label_bon') ?></strong></td>
            <td id="PATH_BON_ENGAGEMENT_verifie" class="text-dark"></td>
          </tr> -->
          <tr>
            <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_tra') ?> (CED)</strong></td>
            <td id="DATE_TRANSMISSION_verifie" class="text-dark"></td>
          </tr>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<div class="modal-footer">
  <button type="button" id="modif" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.label_modify') ?></button>
  <a onclick="save_etap2();hideButton()" id="sav" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.label_confir') ?></a>
</div>
</div>
</div>
</div>


<script type="text/javascript">
  function save_etap2()
  {
    document.getElementById("myEtape1_Prime").submit();
  }
</script>

<script>
  function hideButton()
  {
    var element = document.getElementById("sav");
    element.style.display = "none";

    var elementmod = document.getElementById("modif");
    elementmod.style.display = "none";
  }
</script>

