<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation();
  $session  = \Config\Services::session();
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

  if (empty($user_id)) {
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
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">

                </div>
                <div class="card-body" style="margin-top: -20px">
                  <div style="float: right;">
                    <a href="<?php echo base_url('double_commande_new/Liste_Paiement/vue_obr') ?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang("messages_lang.list_transmission_du_bordereau") ?></a>
                  </div>
                  <div>
                    <font style="font-size:18px;color:#333">
                      <h4>
                        <?= lang('messages_lang.phase_comptable_phase_comptable_prise_en_charge') ?> : <?php if (!empty($etapes)) { ?>
                        <?= $etapes['DESC_ETAPE_DOUBLE_COMMANDE'] ?>
                        <?php    } ?>        
                      </h4>
                    </font>
                  </div>
                  <hr>
                  <!-- debut -->
                  <div class="container" style="width:100%">
                    <div style="width:100%">
                      <div id="accordion">
                        <div class="card-header" id="headingThree" style="padding: 0; display: flex; justify-content: space-between">
                          <h5 class="mb-0">
                            <button style="background:#061e69; color:#fff; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> <?= lang('messages_lang.lab_hist') ?>
                            </button>
                          </h5>
                        </div>
                      </div>
                      <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <?php include  'includes/Detail_View.php';?>
                      </div>
                    </div><br><br>
                  </div>
                  <!--fin-->
                  <form action="<?= base_url('double_commande_new/Phase_comptable/save_reception_obr') ?>" id="MyFormData" method="post">
                    <input type="hidden" name="id" value="<?= $id['EXECUTION_BUDGETAIRE_DETAIL_ID'] ?>">
                    <input type="hidden" name="id_exec_titr_dec" value="<?= $id['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'] ?>">
                    <input type="hidden" name="ETAPE_ID" class="form-control" value="<?= $id['ETAPE_DOUBLE_COMMANDE_ID'] ?>">
                    <input type="hidden" id="paiement_id" name="ordonancement" value="<?= $etapes['MONTANT_ORDONNANCEMENT'] ?>">
                    <div class="col-md-12 container " style="border-radius:10px">
                      <div class="row mt-3">
                        <div class="col-md-6">
                          <div class=""><br>
                            <label for=""> <?= lang('messages_lang.date_reception_phase_comptable_prise_en_charge_comptable') ?> <font color="red">*</font>
                            </label>
                            <input type="date" name="date_reception" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)" id="date_reception_id" class="form-control">
                            <font color="red" id="date_reception_error"></font>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class=""><br>
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
                        </div>

                        <div class="col-md-6" id="show_motif" style="display:none;">
                          <div class=""><br>     
                            <label for=""><?= lang('messages_lang.labelle_mot') ?><font color="red">*</font><span id="loading_motif"></span></label>
                            <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple onchange='getAutreMotif(this.value)'>
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
                            <?php if (isset($validation)) : ?>
                                <font color="red" id="error_TYPE_ANALYSE_MOTIF_ID"><?= $validation->getError('TYPE_ANALYSE_MOTIF_ID'); ?></font>
                            <?php endif ?>
                            <br>
                            <span id="autre_motif" class="col-md-12 row" style="display: none">
                              <div class="col-md-9">
                                <input type="text" class="form-control" id="DESCRIPTION_MOTIF" placeholder="Autre motif" name="DESCRIPTION_MOTIF">
                              </div>
                              <div class="col-md-2" style="margin-left: 5px;">
                                  <button type="button" class="btn btn-success" onclick="save_newMotif()"><i class="fa fa-plus"></i></button>
                              </div>
                            </span>
                          </div>
                        </div>

                        <div class="col-md-6" id="show_result" style="display:none;">
                          <div class=""><br>
                            <label for=""> <?= lang('messages_lang.resultat_analyse_obr') ?> <font color="red">*</font>
                              </label>
                            <select name="resultat" id="resultat_id" class="form-control" onchange="get_retenu()">
                              <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                              <?php foreach ($resultat_data as $info) : ?>
                              <option value="<?= $info->ID_ANALYSE ?>"><?= $info->DESCRIPTION ?></option>
                              <?php endforeach ?>
                            </select>
                            <font color="red" id="resultat_error"></font>
                          </div>
                        </div>

                        <div class="col-md-6" id="type_retenu">
                          <div class=""><br>
                            <label for=""> <?= lang('messages_lang.label_type_retenu') ?> <font color="red">*</font>
                            </label>
                            <select name="TYPE_RETENU_ID" id="TYPE_RETENU_ID" class="form-control" onchange="get_montant()">
                              <option value=""> <?= lang('messages_lang.labelle_selecte') ?> </option>
                              <?php foreach ($retenu_data as $info) : ?>
                              <option value="<?= $info->TYPE_RETENU_ID ?>"><?= $info->DESC_TYPE_RETENU ?>&nbsp;&nbsp;&nbsp;(<?=$info->TYPE_RETENU_POURCENTAGE?>%)</option>
                              <?php endforeach ?>
                            </select>
                            <font color="red" id="TYPE_RETENU_ID_error"></font>
                          </div>
                        </div>

                        <div class="col-md-6" id="montant_prelev">
                          <div class=""><br>
                            <label for=""> <?= lang('messages_lang.label_mont_retenu') ?> <font color="red">*</font>
                            </label>
                            <input  class="form-control" type="text" name="montant_fiscale" id="montant_fiscale_id"> 
                            <font color="red" id="charCount1"></font>
                            <font color="red" id="prelevement_error"></font>
                          </div>    
                        </div>
                        <div class="col-md-6">
                          <div class=""><br>
                            <label for=""><?= lang('messages_lang.date_trasmission_phase_comptable_prise_en_charge_comptable') ?> <font color="red">*</font></label>
                            <input type="date" name="date_transmission"  value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" id="date_transmission_id" class="form-control">
                            <font color="red" id="date_transmission_error"></font>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                  <div style="float:right" class="mt-4">
                    <a class="btn btn-primary" onclick="save_dossier()" id="btns" class="form-control">
                      <?= lang("messages_lang.enregistre_transmission_du_bordereau") ?>
                    </a>
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

  <div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_confirmation') ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
          <div class="table-responsive overflow-auto mt-2">
            <table class=" table  m-b-0 m-t-20">
              <tbody>
                <tr>
                  <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.date_reception_phase_comptable_prise_en_charge_comptable') ?> </td>
                  <td id="date_reception_id_modal"></td>
                </tr>
                <tr>
                  <td><i class="fa fa-cogs"></i><?= lang('messages_lang.label_decision')?> </td>
                  <td id="decision"></td>
                </tr>
                
                <tr id="motif_ret">
                  <td><i class="fa fa-list"></i> <?= lang('messages_lang.label_motif_dec')?></td>
                  <td id="motif_retour_id_modal"></td>
                </tr>
                
                <tr id="res_analy">
                  <td><i class="fa fa-cubes"></i> <?= lang('messages_lang.lab_res_analy') ?></td>
                  <td id="resultat_id_modal"></td>
                </tr>
                <tr id="deretenu">
                  <td><i class="fa fa-tag"></i> <?= lang('messages_lang.label_type_retenu') ?></td>
                  <td id="retenu_id_modal"></td>
                </tr>
                <tr class="d-none" id="mont_hide">
                  <td><i class="fa fa-credit-card"></i> <?= lang('messages_lang.label_mont_retenu') ?></td>
                  <td id="montant_id_modal"></td>
                </tr>
                <tr>
                  <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.date_trasmission_phase_comptable_prise_en_charge_comptable') ?></td>
                  <td id="date_transmission_id_modal"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
            <button id="mod" type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: .2rem !important;"> <i class=" fa fa-edit"></i> <?= lang('messages_lang.modifier_transmission_du_bordereau') ?> </button>
            <button id="myElement" onclick="save_info();hideButton()" type="button" class="btn btn-info"><i class="fa fa-check"></i> <?= lang("messages_lang.confirmer_transmission_du_bordereau") ?> </button>
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
<script>
  function get_min_trans() {
    $("#date_transmission_id").prop('min', $("#date_reception_id").val());
  }
</script>

<script>
  $(document).ready(function() {
    var res = $('#montant_prelev').hide();
    var retenu = $('#type_retenu').hide();
  });
</script>
<script>
  function get_retenu() {
    var res = $('#resultat_id').val();
    if (res == 2) {
      $('#type_retenu').hide();
      $('#TYPE_RETENU_ID').val('');
      $('#montant_prelev').hide();
      $('#montant_fiscale_id').val('');
    } else {
      $('#type_retenu').show();
      $('#TYPE_RETENU_ID').val('');
      $('#montant_prelev').hide();
      $('#montant_fiscale_id').val('');
    }
    }

  function get_montant() {
    var res = $('#TYPE_RETENU_ID').val();
    if (res == '') {
      $('#montant_prelev').hide();
    } else {
      $('#montant_prelev').show();
    }
  }
</script>
<script>
  function verif_montant() {
    var liquidation = $('#liquidation_id').val();
    var paiement = $('#montant_paiment_id').val();

    if (parseInt(paiement) > parseInt(liquidation)) {
      $('#paiment_error').attr('disabled', true);
    }
  }
</script>

<script>
  const numberInput = document.getElementById('montant_paiment_id');
  if (numberInput !== null) {
    numberInput.addEventListener('input', function() {
      const value = numberInput.value;
      if (isNaN(value)) {
          numberInput.value = '';
      }
    });
    numberInput.addEventListener('keydown', function(event) {
      if (
          !/[0-9.]/.test(event.key) &&
          !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(event.key)
      ) {
          event.preventDefault();
      }
    });
  }
</script>
<script>
  function check_caractere() {
    var input = document.getElementById("motif_paie_id");
    var charCount = document.getElementById("charCount");

    input.addEventListener("input", function() {
      var text = input.value;
      var count = text.length;
      charCount.textContent = "Nombre de caractères : " + count;

      var maxLength = 100;
      if (input.value.length > maxLength) {
          input.value = input.value.slice(0, maxLength);
      }

    });
  }

  function check_caractere1() {
    var input = document.getElementById("num_compte_id");
    var charCount = document.getElementById("charCount1");

    input.addEventListener("input", function() {
      var text = input.value;
      var count = text.length;
      charCount.textContent = "Nombre de caractères : " + count;

      var maxLength = 20;
      if (input.value.length > maxLength) {
          input.value = input.value.slice(0, maxLength);
      }

    });

    $('#num_compte_id').on('input', function() {
      if (this.id === "num_compte_id") {
          $(this).val($(this).val().toUpperCase());
          $(this).val(this.value.substring(0, 20));
      }
    })
  }

  function check_caractere2() {
    var input = document.getElementById("num_titre_id");
    var charCount = document.getElementById("charCount2");

    input.addEventListener("input", function() {
      var text = input.value;
      var count = text.length;
      charCount.textContent = "Nombre de caractères : " + count;

      var maxLength = 20;
      if (input.value.length > maxLength) {
          input.value = input.value.slice(0, maxLength);
      }

    });
  }
</script>

<!-- Formatter Cour décaissement en devise* -->
<script type="text/javascript">
  $('#montant_fiscale_id').on('input', function() {
    var value = $(this).val();
    value = value.replace(/[^0-9.]/g, '');
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    $(this).val(value);
    if (/^0\d/.test(value)) {
      value = value.replace(/^0\d/, '');
      $(this).val(value);
    }
    get_montants()
  })
</script> 

<script>
  function DoPrevent(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  function get_montants() {
    var montant_fiscale_id = $('#montant_fiscale_id').val();
    var paiement = $('#paiement_id').val();
    var montant_fiscale = parseFloat(montant_fiscale_id.replace(/\s/g, ''))
    
    if (parseInt(montant_fiscale) > parseInt(paiement)) {
      statut = false;
      $("#prelevement_error").html("<?= lang('messages_lang.label_limite_montant_titre') ?>");
      $('#btns').hide();
    } else {
      $("#prelevement_error").html(" ");
      $('#btns').show();
    }
  }
</script>

<script>
  function save_dossier() {
    var statut = true;
    var date_reception_id = $('#date_reception_id').val();
    var date_transmission_id = $('#date_transmission_id').val();
    var resultat_analyse = $('#resultat_id').val();
    var montant_fiscale_id = $('#montant_fiscale_id').val();
    var TYPE_RETENU_ID = $('#TYPE_RETENU_ID').val();
    var ID_OPERATION = $('#ID_OPERATION').val();
    var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();

    if (ID_OPERATION == '') {
      statut = false;
      $('#error_ID_OPERATION').html('<?= lang('messages_lang.error_sms')?>');
    } else {
      $('#error_ID_OPERATION').html('');
    }

    if (ID_OPERATION == '') {
      statut = false;
      $('#error_ID_OPERATION').html('<?= lang('messages_lang.error_sms')?>');
    } else {
      $('#error_ID_OPERATION').html('');
    }
    if (ID_OPERATION == 1) {

      if (TYPE_ANALYSE_MOTIF_ID == '') {
        statut = false;
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('<?= lang('messages_lang.error_sms')?>');

      } else {
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
      }

    } else {

      if (date_transmission_id == "") {
        statut = false;
        $("#date_transmission_error").html("<?= lang('messages_lang.validation_message') ?>");
      } else {
          $("#date_transmission_error").html("");
      }

      if (date_reception_id == "") {
        statut = false;
        $("#date_reception_error").html("<?= lang('messages_lang.validation_message') ?>");
      } else {
        $("#date_reception_error").html("");
      }

      if (resultat_analyse == "") {
        statut = false;
        $("#resultat_error").html("<?= lang('messages_lang.validation_message') ?>");
      } else if (resultat_analyse == 1){
        $("#resultat_error").html("");

          if(TYPE_RETENU_ID == "")
          { 
            
            statut = false;
            $("#TYPE_RETENU_ID_error").html("<?= lang('messages_lang.validation_message') ?>");
          } else {
            $("#TYPE_RETENU_ID_error").html("");

            if(montant_fiscale_id == "")
            {
              statut = false;
              $("#prelevement_error").html("<?= lang('messages_lang.validation_message') ?>");
            } else {
              $("#prelevement_error").html("");
            }
          }

      } else {
          
        $("#resultat_error").html("");
      }

    }


    if (statut == true) {
        var date = moment(date_reception_id, "YYYY/mm/DD")
        var reception_date = date.format('DD/mm/YYYY')
        var date1 = moment(date_transmission_id, "YYYY/mm/DD")
        var transmission_date = date1.format('DD/mm/YYYY')

        $('#date_reception_id_modal').html(reception_date);
        $('#date_transmission_id_modal').html(transmission_date);
        var operation = $('#ID_OPERATION option:selected').toArray().map(item => item.text).join();

        $('#decision').html(operation);

        if(ID_OPERATION == 1) {
          var motif = $('#TYPE_ANALYSE_MOTIF_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');
          var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${motif}</ol>`;
          $('#motif_retour_id_modal').html(orderedList);
          $('#res_analy').hide();
          $('#deretenu').hide();
          $('#mont_hide').hide();

        }else{
          $('#motif_ret').hide();
          $('#motif_retour_id_modal').hide();
          $('#res_analy').show();
          $('#resultat_id_modal').html($('#resultat_id option:selected').text());

          if (resultat_analyse == 1) {

            if(TYPE_RETENU_ID != '')
            {
              $('#deretenu').show();
              $('#retenu_id_modal').html($('#TYPE_RETENU_ID option:selected').text());

              if (montant_fiscale_id != '')
              {
                document.querySelector('#montant_id_modal').parentElement.classList.remove("d-none")
                $('#montant_id_modal').html(montant_fiscale_id);
                $('#mont_hide').show();
              }
            }else{
              $('#deretenu').hide();
              $('#mont_hide').hide();
            }

          }else{

            $('#deretenu').hide();
            $('#mont_hide').hide();
          }
        }


        $("#detail").modal()
    }
  }
</script>
<script>
  function save_info() {
    $('#MyFormData').submit()
  }
</script>

<script>
  function get_rejet() {
    var OPERATION = $('#ID_OPERATION').val();
    if (OPERATION == '') {
      $('#show_motif').hide();
      $('#show_result').hide();
      $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
      $("#resultat_error").html("");
      $('#resultat_id').val('');
      $('#type_retenu').hide();
      $('#TYPE_RETENU_ID').val('');
      $('#montant_prelev').hide();
      $('#montant_fiscale_id').val('');
        
    } else {

      if (OPERATION == 1 || OPERATION == 3) {
        $('#show_motif').show();
        $('#show_result').hide();
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
        $("#resultat_error").html("");
        $('#resultat_id').val('');
        $('#type_retenu').hide();
        $('#TYPE_RETENU_ID').val('');
        $('#montant_prelev').hide();
        $('#montant_fiscale_id').val('');

      } else {

        $('#show_motif').hide();
        $('#show_result').show();
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('');
        $("#resultat_error").html("");
        $('#resultat_id').val('');
        $('#type_retenu').hide();
        $('#TYPE_RETENU_ID').val('');
        $('#montant_prelev').hide();
        $('#montant_fiscale_id').val('');
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
