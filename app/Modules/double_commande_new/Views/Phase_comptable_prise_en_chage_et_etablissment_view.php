<!DOCTYPE html>
<html lang="en">

<head>
 <?php echo view('includesbackend/header.php');?>
 <?php $validation = \Config\Services::validation(); 
 $session  = \Config\Services::session();
 $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

 if(empty($user_id))
 {
   return redirect('Login_Ptba');
 }
 ?>
 <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
 <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
 <style type="text/css">
   .modal-signature {
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

               </div>
               <div class="card-body">
                 <div style="margin-top: -25px;" class="card">
                 </div>
                 <div class="card-body" style="margin-top: -20px">

                   <div style="float: right;">
                     <a href="<?php echo base_url('double_commande_new/Liste_Paiement')?>"
                       style="float: right;margin-right: 20px;margin-top:5px"
                       class="btn btn-primary"><i class="fa fa-list"
                       aria-hidden="true"></i> <?= lang('messages_lang.link_list')?></a>
                     </div>
                     <div>
                      <font style="font-size:18px,color:#333">
                       <h4> <?= lang('messages_lang.title_global')?> : <?php if(!empty($etapes)){?>
                        <?= $etapes['DESC_ETAPE_DOUBLE_COMMANDE']?>
                        <?php    }?> </h4>
                      </font>
                    </div>
                    <hr>

                    <!-- debut -->
                    <div style="width:100%">
                      <div id="accordion">
                        <div class="card-header" id="headingThree" style="padding: 0; display: flex; justify-content: space-between">
                          <h5 class="mb-0">
                            <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><?= lang('messages_lang.lab_hist')?>
                          </button>
                        </h5>
                      </div>
                    </div>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                      <?php include  'includes/Detail_View.php'; ?>
                    </div>
                  </div>
                  <!-- fin -->

                  <form
                  action="<?=base_url('double_commande_new/Phase_comptable/save_prise_en_charge_etablissement')?>"
                  id="MyFormData" method="post">

                  <input type="hidden" name="id_detail" value="<?= $id['EXECUTION_BUDGETAIRE_DETAIL_ID'] ?>">
                  <input type="hidden" name="id_titr_dec" value="<?= $id['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'] ?>">
                  <input type="hidden" name="id" value="<?= $id['EXECUTION_BUDGETAIRE_ID'] ?>">
                  <input type="hidden" name="ETAPE_ID" class="form-control" value="<?= $id['ETAPE_DOUBLE_COMMANDE_ID']?>">
                  <input type="hidden"  id="paiment_id" name="ordonancement" value="<?= $etapes['MONTANT_PAIEMENT']?>">

                  <div class="col-md-12 container " style="border-radius:10px">
                    <div class="row mt-3">
                      <div class="col-md-6"><br>
                        <label> <?= lang('messages_lang.lab_dec')?><span style="color: red;">*</span></label>
                        <select class="form-control" name="ID_OPERATION" id="ID_OPERATION" onchange="get_rejet(this)">
                          <option value=""> <?= lang('messages_lang.labelle_select') ?></option>
                          <?php
                          foreach ($operation as $key) {
                            if ($key->ID_OPERATION == set_value('ID_OPERATION')) {
                              echo "<option value='" . $key->ID_OPERATION . "' >" . $key->DESCRIPTION . "</option>";
                            } else {
                              echo "<option value='" . $key->ID_OPERATION . "' >" . $key->DESCRIPTION . "</option>";
                            }
                          }
                          ?>
                        </select>
                        <font color="red" id="error_ID_OPERATION"><?= $validation->getError('ID_OPERATION'); ?></font>
                      </div>

                      <div class="col-md-6" id="show_motif" style="display:none;"><br>
                        <label for=""><?= lang('messages_lang.labelle_mot') ?><font color="red">*</font><span id="loading_motif"></span></label>
                        <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple onchange='getAutreMotif(this.value)' >
                          <option value="-1"><?=lang('messages_lang.selection_autre')?></option>
                          <?php
                          foreach ($motif as $value) {
                            if ($value->TYPE_ANALYSE_MOTIF_ID == set_value('TYPE_ANALYSE_MOTIF_ID')) { ?>
                              <option value="<?= $value->TYPE_ANALYSE_ID ?>" selected><?= $value->DESC_TYPE_ANALYSE_MOTIF ?></option>
                            <?php } else {
                              ?>
                              <option value="<?= $value->TYPE_ANALYSE_MOTIF_ID ?>"><?= $value->DESC_TYPE_ANALYSE_MOTIF ?></option>
                              <?php
                            }
                          }
                          ?>
                        </select>
                        <br>
                        <span id="autre_motif" class="col-md-12 row" style="display: none">
                          <div class="col-md-9">
                            <input type="text" class="form-control" id="DESCRIPTION_MOTIF" placeholder="Autre motif" name="DESCRIPTION_MOTIF">
                          </div>
                          <div class="col-md-2" style="margin-left: 5px;">
                            <button type="button" class="btn btn-success" onclick="save_newMotif()"><i class="fa fa-plus"></i></button>
                          </div>
                        </span>
                        <?php if (isset($validation)) : ?>
                          <font color="red" id="error_TYPE_ANALYSE_MOTIF_ID"><?= $validation->getError('TYPE_ANALYSE_MOTIF_ID'); ?></font>
                        <?php endif ?>
                      </div>
                      <div class="col-md-6" id="sho_lab_num_tit" style="display:none;"><br>
                        <label for=""><?= lang('messages_lang.label_numero_titre_decaissement')?><font color="red">*</font>
                        </label>
                        <input type="" onkeyDown="check_caractere1()" name="numero_decaissement"
                        id="numero_decaissement_id" class="form-control" >
                        <font color="red" id="num_decaissement_error"></font>
                        <font color="green" id="charCount1"></font>
                      </div>
                      <div class="col-md-6" id="sho_lab_mont_tit" style="display:none;"><br>
                        <label for=""><?= lang('messages_lang.label_montant_titre_decaissement')?> <font color="red">*</font>
                        </label>
                        <input type="text" name="montant_decaissement"
                        onKeyDown="get_montant()"  id="number_decaissement_id" class="form-control" value="<?=number_format($etapes['MONTANT_PAIEMENT'],'2',',',' ')?>">
                        <font color="red" id="number_decaissement_error"></font>
                        <font color="red" id="charCount1"></font>
                      </div>

                      <div class="col-md-6" id="sho_lab_elab_tit" style="display:none;" ><br>
                        <label for=""><?= lang('messages_lang.label_elaboration_titre')?><font color="red">*</font>
                        </label>
                        <input type="date" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" value="<?=date('Y-m-d')?>" name="date_elaboration"
                        max="<?= date('Y-m-d')?>"  id="date_elaboration_id" class="form-control">
                        <font color="red" id="date_elaboration_error"></font>
                      </div>

                      <div class="col-md-6" id="sho_lab_observe" style="display:none;"><br>
                        <label for=""><?= lang('messages_lang.labelle_observartion')?>
                      </label>
                      <input type="text" name="NOM_PERSONNE_RETRAIT" id="NOM_PERSONNE_RETRAIT" class="form-control" value="">
                      <font color="red" id="NOM_PERSONNE_RETRAIT_error"></font>
                    </div>

                    <div class="col-md-6" id="sho_lab_devise" style="display:none;"><br>
                      <label class="form-label"><?= lang('messages_lang.labelle_devise_dec') ?><font color="red">*</font></label>
                      <select onchange="" name="DEVISE_TYPE_ID_RETRAIT" id="DEVISE_TYPE_ID_RETRAIT" class="form-control">
                        <option value=""><?=lang('messages_lang.labelle_select')?></option>
                        <?php 
                        foreach($get_device as $key) { 
                          if ($key->DEVISE_TYPE_ID==set_value('TYPE_MONTANT_ID')) { 
                            echo "<option value='".$key->DEVISE_TYPE_ID."' selected>".$key->DESC_DEVISE_TYPE."</option>";
                          }else{
                            echo "<option value='".$key->DEVISE_TYPE_ID."' >".$key->DESC_DEVISE_TYPE."</option>"; 
                          } 
                        }?>

                      </select>
                      <?php if (isset($validation)) : ?>
                        <font color="red" id="DEVISE_TYPE_ID_RETRAIT_error"><?= $validation->getError('DEVISE_TYPE_ID_RETRAIT'); ?></font>
                      <?php endif ?>
                    </div>                                                   

                    <div class="col-md-6"><br>
                      <label for=""><?= lang('messages_lang.label_date_transmission_')?><font color="red">*</font>
                      </label>
                      <input type="date" name="date_transmission"
                      onkeypress="return false" value="<?=date('Y-m-d')?>" max="<?= date('Y-m-d')?>"  min="<?= date('Y-m-d')?>" id="date_transmission_id" class="form-control">
                      <font color="red" id="date_transmission_error"></font>
                    </div>
                  </div>
                </div>

              </form>
              <div style="float:right" class="mt-4">
                <a class="btn btn-primary" onclick="save_dossier()"
                id="btns" class="form-control"><?= lang('messages_lang.bouton_enregistrer')?></a>
              </div>
            </div>
          </div>
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

<?php echo view('includesbackend/scripts_js.php');?>

<div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_confirmation')?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive overflow-auto mt-2" >
          <table class=" table  m-b-0 m-t-20">
            <tbody>
              <tr>
                <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_operatio') ?></td>
                <td id="operation_validation_modal"></td>
              </tr>
              <tr id="motif_ret">
                <td><i class="fa fa-list"></i> <?= lang('messages_lang.label_motif_dec')?></td>
                <td id="motif_retour_id_modal"></td>
              </tr>
              <tr id="show_tit_dec">
                <td><i class="fa fa-cogs"></i> <?= lang('messages_lang.label_numero_titre_decaissement')?></td>
                <td id="titre_decaissement_id_modal"></td>
              </tr>

              <tr id="show_mont_dec" >
                <td> <i class="fa fa-credit-card"></i> <?= lang('messages_lang.label_montant_titre_decaissement')?></td>
                <td id="montant_id_modal"></td>
              </tr>

              <tr id="show_observ">
                <td> <i class="fa fa-credit-card"></i> <?= lang('messages_lang.labelle_observartion')?></td>
                <td id="NOM_modal"></td>
              </tr>

              <tr id="show_devise_dec">
                <td> <i class="fa fa-credit-card"></i> <?= lang('messages_lang.labelle_devise_dec')?></td>
                <td id="DEVISE_modal"></td>
              </tr>

              <tr id="show_date_elab">
                <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.label_elaboration_titre')?></td>
                <td id="date_elaboration_id_modal"></td>
              </tr>

              <tr >
                <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.label_date_transmission_')?></td>
                <td id="date_transmission_id_modal"></td>
              </tr>

            </tbody>
          </table>     
        </div> 
      </div>
      <div class="modal-footer">
        <button id="mod" type="button" class="btn btn-secondary" data-dismiss="modal"> <i class=" fa fa-edit"></i> <?= lang('messages_lang.bouton_modifier')?></button>
        <button id="myElement" onclick="save_info();hideButton()"  type="button" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.bouton_confirmer')?></button>
      </div>
    </div>
  </div>
</div>
</body>
</html>

<script>
  function hideButton()
  {
    var element = document.getElementById("myElement");
    element.style.display = "none";

    var elementmod = document.getElementById("mod");
    elementmod.style.display = "none";
  }
</script>
<!-- Formatter le montants -->
<script type="text/javascript">
  $('#number_decaissement_id').on('input', function() {
    var value = $(this).val();
    value = value.replace(/[^0-9.]/g, '');
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    $(this).val(value);
    if (/^0\d/.test(value)) {
      value = value.replace(/^0\d/, '');
      $(this).val(value);
    }
  })

  $(document).ready(function()
  {
    $('#number_decaissement_id').prop('readOnly', true);
  });
</script>

<script>
 function DoPrevent(e)
 {
  e.preventDefault();
  e.stopPropagation();
}

function get_montant()
{
  var paiment_id= $('#paiment_id').val();
  var number_decaissement_id= $('#number_decaissement_id').val();

  var montant_paiement = parseFloat(paiment_id.replace(/\s/g, ''))
  var montant_decaissement = parseFloat(number_decaissement_id.replace(/\s/g, ''));
  if(parseInt(montant_decaissement) > parseInt(montant_paiement))
  {
    $('#number_decaissement_id').on('keypress',DoPrevent);
    $('#number_decaissement_error').html('<?= lang('messages_lang.label_limite_montant_titre')?>');
    $('#btns').hide();
  }
  else
  {
    $('#number_decaissement_error').html('');
    $('#btns').show();
    $('#number_decaissement_id').off('keypress',DoPrevent);
  }
}
</script>



<script>
  function get_min_trans()
  {
    $("#date_transmission_id").prop('min',$("#date_reception_id").val());
  }
</script>

<script>
  function check_caractere1()
  {
    var input = document.getElementById("numero_decaissement_id");
    var charCount = document.getElementById("charCount1");
    input.addEventListener("input", function()
    {
      var text = input.value;
      var count = text.length;
      charCount.textContent = "<?= lang('messages_lang.label_number_character')?> : " + count;
      var maxLength = 20;
      if (input.value.length > maxLength) 
      {
        input.value = input.value.slice(0, maxLength);
      }

    });

    $('#numero_decaissement_id').on('input', function()
    {
      if(this.id === "numero_decaissement_id")
      {
        $(this).val($(this).val().toUpperCase());
        $(this).val(this.value.substring(0,20));
      }
    })
    
  }
</script>

<script>
  function save_dossier()
  {
    var statut = true;

    var ID_OPERATION = $('#ID_OPERATION').val();
    var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();

    var numero_decaissement_id = $('#numero_decaissement_id').val();
    var date_reception_id = $('#date_reception_id').val();
    var date_transmission_id= $('#date_transmission_id').val();
    var number_decaissement_id = $('#number_decaissement_id').val();
    var date_prise_en_charge_id = $('#date_prise_en_charge_id').val();
    var date_elaboration = $('#date_elaboration_id').val();
    var NOM_PERSONNE_RETRAIT=$('#NOM_PERSONNE_RETRAIT').val();
    var DEVISE_TYPE_ID_RETRAIT=$('#DEVISE_TYPE_ID_RETRAIT').val();

    var paiment_id= $('#paiment_id').val();
    var number_decaissement_id= $('#number_decaissement_id').val();
    
    $("#error_ID_OPERATION").html('');
    $("#error_TYPE_ANALYSE_MOTIF_ID").html('');


    if (ID_OPERATION == '') {
      statut = false;
      $('#error_ID_OPERATION').html('<?= lang('messages_lang.error_sms')?>');

    } 

    if (ID_OPERATION == 1 || ID_OPERATION == 3)
    {
      if (TYPE_ANALYSE_MOTIF_ID == '')
      {
        statut = false;
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('<?= lang('messages_lang.error_sms')?>');

      }
    }
    else if(ID_OPERATION == 2){

      if (DEVISE_TYPE_ID_RETRAIT == "")
      {
        statut = false;
        $("#DEVISE_TYPE_ID_RETRAIT_error").html("<?= lang('messages_lang.validation_message')?>");
      }

      if(parseInt(number_decaissement_id) > parseInt(paiment_id))
      {
        $('#number_decaissement_error').html('<?= lang('messages_lang.label_limite_montant_titre')?>');       
      }
      else
      {
        $('#number_decaissement_error').html('');
      }

      if (numero_decaissement_id == "")
      {
        statut = false;
        $("#num_decaissement_error").html("<?= lang('messages_lang.validation_message')?>");
      }
      else
      {
        $("#num_decaissement_error").html("");
      }

      if (date_elaboration == "") 
      {
        statut = false;
        $("#date_elaboration_error").html("<?= lang('messages_lang.validation_message')?>");
      }
      else 
      {
        $("#date_elaboration_error").html("");
      }

      if (date_reception_id == "") {
        statut = false;
        $("#date_reception_error").html("<?= lang('messages_lang.validation_message')?>");
      } else {
        $("#date_reception_error").html("");
      }

      if (number_decaissement_id == "")
      {
        statut = false;
        $("#number_decaissement_error").html("<?= lang('messages_lang.validation_message')?>");
      }
      else
      {
        $("#number_decaissement_error").html("");
      }

      if (date_prise_en_charge_id == "")
      {
        statut = false;
        $("#date_prise_en_charge_error").html("<?= lang('messages_lang.validation_message')?>");
      }
      else
      {
        $("#date_prise_en_charge_error").html("");
      }

    }   

    if (date_transmission_id == "")
    {
      statut = false;
      $("#date_transmission_error").html("<?= lang('messages_lang.validation_message')?>");
    }
    else
    {
      $("#date_transmission_error").html("");
    }
    
    if (statut == true) 
    {
      var date=moment(date_reception_id, "YYYY/mm/DD")
      var reception_date= date.format('DD/mm/YYYY')
      var date1=moment(date_transmission_id, "YYYY/mm/DD")
      var transmission_date= date1.format('DD/mm/YYYY')
      var date2=moment(date_prise_en_charge_id, "YYYY/mm/DD")
      var pris_en_charge= date2.format('DD/mm/YYYY')
      var date3=moment(date_elaboration, "YYYY/mm/DD")
      var elaboration_date= date3.format('DD/mm/YYYY')

      var operation_validation = $('#ID_OPERATION option:selected').toArray().map(item => item.text).join();
      $('#operation_validation_modal').html(operation_validation);
      $("#date_transmission_id_modal").html(transmission_date);

      if(ID_OPERATION == 1 || ID_OPERATION == 3) {
        var motif = $('#TYPE_ANALYSE_MOTIF_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');
        var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${motif}</ol>`;
        $('#motif_retour_id_modal').html(orderedList);

        $('#show_tit_dec').hide();
        $('#show_mont_dec').hide();
        $('#show_observ').hide();
        $('#show_devise_dec').hide();
        $('#show_date_elab').hide();
      
      }else{
        $('#motif_ret').hide();
        $('#motif_retour_id_modal').hide();

        $("#date_reception_id_modal").html(reception_date);
        
        $("#titre_decaissement_id_modal").html(numero_decaissement_id);
        $("#montant_id_modal").html(number_decaissement_id);
        $("#date_prise_id_modal").html(pris_en_charge);
        $("#date_elaboration_id_modal").html(elaboration_date);

        $('#show_tit_dec').show();
        $('#show_mont_dec').show();
        $('#show_observ').show();
        $('#show_devise_dec').show();
        $('#show_date_elab').show();

        $("#NOM_modal").html("-");
        if (NOM_PERSONNE_RETRAIT!='') 
        {
          $("#NOM_modal").html(NOM_PERSONNE_RETRAIT);
        }else{
          $("#NOM_modal").html("-");
        }

        $("#DEVISE_modal").html($('#DEVISE_TYPE_ID_RETRAIT option:selected').text());

      }

      $('#detail').modal('show');
    }
  }
  </script>

    <script>
      function save_info()
      {
        $('#MyFormData').submit()

      }
    </script>

    <script>
      function get_rejet() {
        var OPERATION = $('#ID_OPERATION').val();

        if (OPERATION == '') {
          $('#show_motif').hide();
          $('#error_TYPE_ANALYSE_MOTIF_ID').html('');

          $('#sho_lab_num_tit').hide();
          $('#sho_lab_mont_tit').hide();
          $('#sho_lab_elab_tit').hide();
          $('#sho_lab_observe').hide();
          $('#sho_lab_devise').hide();

        } else {

          if (OPERATION == 1 || OPERATION == 3) {
            $('#show_motif').show();
            $('#error_TYPE_ANALYSE_MOTIF_ID').html('');

            $('#sho_lab_num_tit').hide();
            $('#sho_lab_mont_tit').hide();
            $('#sho_lab_elab_tit').hide();
            $('#sho_lab_observe').hide();
            $('#sho_lab_devise').hide();
          } else {

            $('#show_motif').hide();
            $('#error_TYPE_ANALYSE_MOTIF_ID').html('');

            $('#sho_lab_num_tit').show();
            $('#sho_lab_mont_tit').show();
            $('#sho_lab_elab_tit').show();
            $('#sho_lab_observe').show();
            $('#sho_lab_devise').show();
          }
        }
      }
    </script>

    <script type="text/javascript">
      function getAutreMotif(id = 0)
      {
        var selectElement = document.getElementById("TYPE_ANALYSE_MOTIF_ID");
        if (id.includes("-1")) {
          $('#autre_motif').delay(100).show('hide');
          $('#DESCRIPTION_MOTIF').val('');
          $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
          disableOptions(selectElement);

        }else{
          $('#autre_motif').delay(100).hide('show');
          $('#DESCRIPTION_MOTIF').val('');
          $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
          enableOptions(selectElement);
        }

      }

      function disableOptions(selectElement) {
        for (var i = 0; i < selectElement.options.length; i++) {
          if (selectElement.options[i].value !== "-1") {
            selectElement.options[i].disabled = true;
          }
        }
      }

      function enableOptions(selectElement) {
        for (var i = 0; i < selectElement.options.length; i++) {
          selectElement.options[i].disabled = false;
        }
      }

      function save_newMotif()
      {
        var DESCRIPTION_MOTIF = $('#DESCRIPTION_MOTIF').val();
        var statut = 2;

        if (DESCRIPTION_MOTIF == "") {

          $('#DESCRIPTION_MOTIF').css('border-color','red');
          statut = 1;
        }

        if(statut == 2)
        {
          $.ajax({
            url: "<?=base_url('')?>/double_commande_new/Phase_comptable/save_newMotif",
            type: "POST",
            dataType: "JSON",
            data: {
              DESCRIPTION_MOTIF:DESCRIPTION_MOTIF
            },
            beforeSend: function() {
              $('#loading_motif').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data) {
              $('#TYPE_ANALYSE_MOTIF_ID').html(data.motifs);
              TYPE_ANALYSE_MOTIF_ID.InnerHtml=data.motifs;
              $('#loading_motif').html("");
              $('#TYPE_ANALYSE_MOTIF_ID').val([]).trigger('change');
              $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
              $('#autre_motif').delay(100).hide('show');
            }
          });
        }


      }
    </script>