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
                <div class="card-body">
                  <div style="margin-top: -25px;" class="card">
                  </div>
                  <div class="card-body" style="margin-top: -20px">
                    <br>
                    <div style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Receptio_Border_Dir_compt') ?>" style="float: right;margin-right: 20px;margin-top:px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.bouton_liste') ?> </a>
                    </div>

                    <div>
                      <font style="font-size:18px;color:#333">
                        <h4><?= lang('messages_lang.labelle_phase_comptable') ?>:
                          <?php if (!empty($titre_etape)) { ?>
                             <?= $titre_etape['DESC_ETAPE_DOUBLE_COMMANDE'] ?>
                          <?php } ?> 
                        </h4>
                      </font>
                    </div>
                    <br>
                    <div style="margin: 15px;">
                      <hr>
                    </div>
                    <div style="width:100%">
                      
                    </div>
                    <div class="card-body">
                      <div style="margin-left: 15px" class="row">
                        <?php if (session()->getFlashKeys('alert')) : ?>
                         
                        <?php endif; ?>
                      </div>

                      <div class="table-responsive container ">
                        <?php if (isset($pas_donne)) : ?>
                          <h3> <?= $pas_donne ?> </h3>
                        <?php else : ?>
                          <form action="<?= base_url("double_commande_new/reception_titre_decaissement/store") ?>" method="post" id="push_bon">
                            <input type="hidden" id="etap_borderaux_transmission_id" name="etap_borderaux_transmission_id" value="<?=$etap_borderaux_transmission_id?>">
                            <input type="hidden" id="BORDEREAU_TRANSMISSION_ID" name="BORDEREAU_TRANSMISSION_ID" value="<?=$BORDEREAU_TRANSMISSION_ID?>">
                            <input type="hidden" id="DATE_TRANSMISSION" name="date_insertion_check" value="<?= $hist['DATE_TRANSMISSION'] ?>">

                            <div class="row">
                              <div class="col-md-6">
                                  <label for=""> <?= lang('messages_lang.labelle_date_recherche') ?> </label>
                                  <input type="date" class="form-control" name="DATE_RECEPTION" id="DATE_RECEPTION" value="<?=set_value('DATE_RECEPTION')?>" min="<?=date('Y-m-d', strtotime($hist['DATE_TRANSMISSION']))?>" max="<?=Date('Y-m-d')?>" onchange="get_date_min_trans()" onkeypress="return false" onblur="this.type='date'">
                                  <span class="error" style="color: red;  font-size: 13px"></span>
                                  <font color="red" id="error_DATE_RECEPTION"></font>
                                  
                                  <span class="date-error" id="error_DATE_RECEPTION" style="color: red; font-size: 13px"></span> 
                                </div>
                                <div class="col-md-6">
                                  <label for=""><?= lang('messages_lang.labelle_numero_bordereau') ?></label>
                                  <input type="text" class="form-control" name="NUMERO_BORDEREAU_TRANSMISSION" id="bordereau_transmission" readonly="on" value="<?=$NUMERO_BORDEREAU_TRANSMISSION?>">
                              </div>
                            </div>
                            <br>
                            <div class="row">
                              <div class="col-6" style="display: none;">
                                <label><?= lang('messages_lang.label_numero_titre_decaissement')?><span style="color: red;">*</span></label>
                                <select class="form-control select2" multiple name="titre_decaissement[]" id="titre_decaissement">
                                  <option value=""><?= lang('messages_lang.label_selecte')?></option>
                                  <?php
                                  foreach ($titre_decaissement as $row) { ?>
                                    <option value="<?= $row->EXECUTION_BUDGETAIRE_DETAIL_ID ?>" selected><?= $row->NUMERO_DOCUMENT ?></option>
                                  <?php
                                  }
                                  ?>
                                </select>
                                <font color="red" id="error_TITRE_DECAISSEMENT"></font>
                              </div>

                              <div class="col-6">
                                <label><?= lang('messages_lang.label_numero_titre_decaissement')?><span style="color: red;">*</span></label>
                                <select class="form-control select2" multiple name="titre_decaissement1[]" id="titre_decaissement1" disabled>
                                  <option value=""><?= lang('messages_lang.label_selecte')?></option>
                                  <?php
                                  foreach ($titre_decaissement as $row) { ?>
                                    <option value="<?= $row->EXECUTION_BUDGETAIRE_DETAIL_ID ?>" selected><?= $row->NUMERO_DOCUMENT ?></option>
                                  <?php
                                  }
                                  ?>
                                </select>
                                <font color="red" id="error_TITRE_DECAISSEMENT"></font>
                              </div>
                        
                              <div class="col-md-6">
                                  <label for=""> <?= lang('messages_lang.labelle_date_trans')?><font color="red">*</font></label>
                                  <input type="date" class="form-control" name="DATE_TRANSMISSION" id="DATE_TRANSMISSION" min="<?= date('Y-m-d') ?>" value="<?=set_value('DATE_TRANSMISSION')?>"  max="<?=Date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">

                                  <span class="error" style="color: red; font-size: 13px"></span>
                                  <font color="red"  id="error_DATE_TRANSMISSION"></font>
                                  <?php if (isset($validation)) : ?>
                                      <?= $validation->getError('DATE_TRANSMISSION'); ?>
                                  <?php endif ?>
                              </div>
                            </div>
                            <br>
                           
                            
                          </form>
                        <?php endif ?>
                        <div style="float:right" class="mt-4">
                          <div class="form-group ">
                            <a onclick="save_titre()" id="btn_save" class="btn" style="float:right;margin-right: 20px;background:#061e69;color:white"> <?= lang('messages_lang.bouton_enregistrer')?></a>
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
  </div>
 
  <?php echo view('includesbackend/scripts_js.php'); ?>

</body>

</html>
<script type="text/javascript">
    function finale_save() {
      document.getElementById("push_bon").submit();
    }
</script>

<script>
  function save_titre()
  {
    var statut = true;
    var date_reception = $('#DATE_RECEPTION').val();
    var numero_bordereau= $('#bordereau_transmission').val();
    var DATE_TRANSMISSION= $('#DATE_TRANSMISSION').val();
    var titre_decaissement = $('#titre_decaissement').val();

    if (date_reception == "") {
        statut = false;
        $("#error_DATE_RECEPTION").html("<?= lang('messages_lang.error_reception')?>");
    } else {
        $("#error_DATE_RECEPTION").html("");
    }
    if(DATE_TRANSMISSION == ""){
      statut = false;
      $("#error_DATE_TRANSMISSION").html("<?= lang('messages_lang.error_reception')?>");
    } else {
         $('#error_DATE_TRANSMISSION').html('');
    }
    if(titre_decaissement == ""){
      statut = false;
      $("#error_TITRE_DECAISSEMENT").html("<?= lang('messages_lang.error_reception')?>");
    } else {
         $('#error_TITRE_DECAISSEMENT').html('');
    }

            
           

    if (statut == true) 
    {
      var date=moment(date_reception, "YYYY/mm/DD")
      var reception_date= date.format('DD/mm/YYYY')
      var date=moment(DATE_TRANSMISSION, "YYYY/mm/DD")
      var DATE_TRANSMISSION= date.format('DD/mm/YYYY')
      var titre_decaissement = $('#titre_decaissement').val();

      $("#DATE_RECEPTION_MODAL").html(reception_date);
      $("#BORDERO_TRANSMISSION_MODAL").html(numero_bordereau);
      $("#DATE_TRANSMISSION_MODAL").html(DATE_TRANSMISSION);
      var titre_decaissement = $('#titre_decaissement option:selected').toArray().map(item => item.text).join();
      $('#titre_decaissement_MODAL').html(titre_decaissement);

     // Pour mettre le checked item sur le modal
      var checkboxes = document.querySelectorAll('input[type="checkbox"]');
      var valeurs = [];

      checkboxes.forEach(function(checkbox) {
        if (checkbox.checked) {
          var label = checkbox.nextElementSibling.textContent;
          valeurs.push(label);
        }
      });

      var listeOrd = '<ul>';  // Début de la balise <ol>
      valeurs.forEach(function(valeur) {
        listeOrd += '<li>' + valeur + '</li>';  // Ajoute chaque valeur dans un élément <li>
      });
      listeOrd += '</ul>';  // Fin de la balise </ol>

      $('#EXECUTION_BUDGETAIRE_RACCROCHAGE_ID_MODAL').html(listeOrd);
      $('#enjeux_modal').modal('show');
    }
  }
    
</script>


<script>
  $("#DATE_TRANSMISSION").prop('min', $("#DATE_RECEPTION").val());
  document.querySelector("#push_bon").addEventListener("submit", (ev) => {
    ev.preventDefault()
    let error = false

    if (document.querySelector("#DATE_RECEPTION").value == "") {
      document.querySelector(".error").innerText = "veiller complete ce champ <?= lang('messages_lang.bouton_enregistrer')?>"
      error = true
    } else {
      document.querySelector(".error").innerText = ""
      error = false
    }

  })
</script>

<script>
  const live_search_input = document.querySelector("#live_search_engagement")
  const form = document.querySelector("#push_bon")
  live_search_input.addEventListener("input", (ev) => {
    console.log(live_search_input.value);
    if (live_search_input.value != "") {
      let target = ev.currentTarget
      let ajax = $.ajax({
        method: "POST",
        url: "<?= base_url('double_commande_new/reception_titre_decaissement/search_engagement') ?>",
        dataType: 'JSON',
        data: {
          search_element: target.value,
          id_etap: "<?= $etap_borderaux_transmission_id ?>",
        },
      })

      ajax.done(function(data) {
        $(".list-group").html(data)
      })
      ajax.fail(function(data) {
        $(".list-group").html(`<h3 class="bon_error"> ${data.responseJSON.data} </h3>`)
      })
    }
    if (live_search_input.value == "") {
      let target = ev.currentTarget
      let ajax = $.ajax({
        method: "POST",
        url: "<?= base_url('double_commande_new/reception_titre_decaissement/search_engagement3') ?>",
        dataType: 'JSON',
        data: {
          search_element: target.value,
          id_etap: "<?= $etap_borderaux_transmission_id ?>",
        },
      })

      ajax.done(function(data) {
        $(".list-group").html(data)
      })
      ajax.fail(function(data) {
        $(".list-group").html(`<h3 class="bon_error"> ${data.responseJSON.data} </h3>`)
      })
    }
  })
</script>


<div class="modal fade" id="enjeux_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-body">
        <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.labell_titre_modale')?></h5>
        <div class="table-responsive  mt-3">
          <table class="table m-b-0 m-t-20">
            <tbody>
              <tr>
                <td><i class="fa fa-exchange"></i> &nbsp;<strong><?= lang('messages_lang.labelle_numero_bordereau') ?></strong></td>
                <td id="BORDERO_TRANSMISSION_MODAL" class="text-dark"></td>
              </tr>
              <tr>
                <td style="Width:60%"><i class="fa fa-archive"></i> &nbsp;<strong><?= lang('messages_lang.labelle_date_recherche') ?></strong></td>
                <td id="DATE_RECEPTION_MODAL" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-bars"></i> &nbsp;<strong><?= lang('messages_lang.labelle_titre_decaissement')?></strong></td>
                <td id="titre_decaissement_MODAL" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-bars"></i> &nbsp;<strong><?= lang('messages_lang.labelle_date_trans')?></strong></td>
                <td id="DATE_TRANSMISSION_MODAL" class="text-dark"></td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.bouton_modifier')?></button>
        <button id="myElement" type="button" onclick="finale_save();hideButton()" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.bouton_confirmer')?></button>
      </div>
      
    </div>
  </div>
</div>


<script>
  function hideButton()
  {
    var element = document.getElementById("myElement");
    element.style.display = "none";

    var elementmod = document.getElementById("mod");
    elementmod.style.display = "none";
  }
</script>