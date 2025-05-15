<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation(); ?>
</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-9">
                      <h3> Modification d'un signataires sur la note</h3>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Signataire_Note/liste') ?>" style="float: right;margin-right: 20px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.list_transmission_du_bordereau') ?> </a>
                    </div>

                  </div>
                </div>
                <hr>
                <div class="card-body">

                  <form id="my_form" action="<?= base_url('double_commande_new/Signataire_Note/update_sign') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="INSTITUTION_SIGNATAIRE_ID" value="<?=$donnees['INSTITUTION_SIGNATAIRE_ID']?>">
                    <div class="row col-md-12">
                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.table_institution') ?> <span style="color: red;">*</span></label>
                        <select class="form-control select2" name="INSTITUTION_ID" id="INSTITUTION_ID" onchange="getSousTutel()">
                          <option value=""><?= lang('messages_lang.selectionner_transmission_du_bordereau') ?></option>
                          <?php
                            foreach ($institution as $value)
                            {
                              if($value->INSTITUTION_ID==$donnees['INSTITUTION_ID'])
                              {
                                echo '<option value="'.$value->INSTITUTION_ID.'" selected>'.$value->CODE_INSTITUTION.' '.$value->DESCRIPTION_INSTITUTION.'</option>';                                  
                              }
                              else
                              {
                                echo '<option value="'.$value->INSTITUTION_ID.'">'.$value->CODE_INSTITUTION.' '.$value->DESCRIPTION_INSTITUTION.'</option>';                                
                              }                          
                            }
                          ?>
                        </select>
                        <span class="text-danger" id="error_INSTITUTION_ID"></span>
                          <?= $validation->getError('INSTITUTION_ID'); ?>
                      </div>

                      <div class="col-md-6">
                        <br>
                        <div class="">
                          <label for=""> <?= lang('messages_lang.table_st') ?><span id="loading_sous_tutel"></span></label>
                          <select class="form-control select2" id="SOUS_TUTEL_ID" name="SOUS_TUTEL_ID">
                            <option></option>
                            <?php
                            if (!empty($donnees['SOUS_TUTEL_ID']))
                            {
                              echo '<option value="'.$donnees['SOUS_TUTEL_ID'].'" selected>'.$donnees['DESCRIPTION_SOUS_TUTEL'].'</option>';
                            }
                            ?>
                          </select>
                          <span class="text-danger" id="error_SOUS_TUTEL_ID"></span>
                          <?= $validation->getError('SOUS_TUTEL_ID'); ?>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.poste') ?><span style="color: red;">*</span><span id="loading_poste"></span></label>
                        <select class="form-control select2" name="POSTE_SIGNATAIRE_ID" id="POSTE_SIGNATAIRE_ID" onchange='getAutrePoste(this.value)'>
                          <option value=""><?= lang('messages_lang.selectionner_transmission_du_bordereau') ?></option>
                          <?php
                          foreach ($poste as $key)
                          {
                            if ($key->POSTE_SIGNATAIRE_ID==$donnees['POSTE_SIGNATAIRE_ID'])
                            {
                              echo '<option value="'.$key->POSTE_SIGNATAIRE_ID.'" selected>'.$key->DESC_POSTE_SIGNATAIRE.'</option>';
                            }
                            else
                            {
                              echo '<option value="'.$key->POSTE_SIGNATAIRE_ID.'">'.$key->DESC_POSTE_SIGNATAIRE.'</option>';
                            }
                          }
                          ?>
                          <option value="-1"><?= lang('messages_lang.selection_autre') ?></option>
                        </select>
                        <?php if (isset($validation)) : ?>
                          <?= $validation->getError('POSTE_SIGNATAIRE_ID'); ?>
                        <?php endif ?>
                        <span id="error_POSTE_SIGNATAIRE_ID" class="text-danger"></span>

                        <br>
                        <span id="autre_poste" class="col-md-12 row" style="display: none">
                          <div class="col-md-9">
                            <textarea class="form-control" id="DESC_POSTE_SIGNATAIRE" placeholder="Autre poste" name="DESC_POSTE_SIGNATAIRE"></textarea>
                          </div>
                          <div class="col-md-3">
                            <button type="button" class="btn btn-success" onclick="save_newPoste()"><i class="fa fa-plus"></i></button>
                            <button type="button" style="background:red;" class="btn" onclick="hide_newPoste()"><i class="fa fa-close"></i></button>
                          </div>
                        </span>
                        <font color="red" id="error_poste"></font>
                      </div>

                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.labelle_nom') ?><span style="color: red;">*</span></label>
                        <input type="text" class="form-control" name="NOM_PRENOM" id="NOM_PRENOM" value="<?=$donnees['NOM_PRENOM']?>">
                        <?php if (isset($validation)) : ?>
                          <?= $validation->getError('NOM_PRENOM'); ?>
                        <?php endif ?>
                        <span id="error_NOM_PRENOM" class="text-danger"></span>
                      </div>
                  </div>
                    </form>
                    <div class="card-footer" id="btn_save">
                      <div style="float:right;">
                        <a onclick="save();" class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.bouton_enregistrer') ?></a>
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

    <div class="modal fade" id="prep_projet" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.confirmation_modal_transmission_du_bordereau') ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="table-responsive  mt-3">
              <table class="table m-b-0 m-t-20">
                <tbody>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.table_institution') ?></strong></td>
                    <td id="INSTITUTION_ID_VERIFY" class="text-dark"></td>
                  </tr>

                  <tr id='st' hidden="true">
                    <td style="width:350px;"><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.table_st') ?></strong></td>
                    <td id="SOUS_TUTEL_ID_VERIFY" class="text-dark"></td>
                  </tr>

                  <tr>
                    <td style="width:350px;"><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.poste') ?></strong></td>
                    <td id="POSTE_SIGNATAIRE_ID_VERIFY" class="text-dark"></td>
                  </tr>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.labelle_nom') ?></strong></td>
                    <td id="NOM_PRENOM_VERIFY" class="text-dark"></td>
                  </tr>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <div class="row">
            <button id="mod" type="button" class="btn btn-primary" style="margin-top:10px" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.modifier_transmission_du_bordereau') ?></button>
            <a id="myElement" onclick="save_etap2();hideButton()" style="float: right; margin-top:10px" class="btn btn-info"><i class="fa fa-check" aria-hidden="true"></i> <?= lang('messages_lang.confirmer_transmission_du_bordereau') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>

<script>
  $(document).ready(function()
  {
  });

  function getAutrePoste(id = 0)
  {
    var selectElement = document.getElementById("POSTE_SIGNATAIRE_ID");
    if (id.includes("-1")) {
      $('#autre_poste').delay(100).show('hide');
      $('#DESC_POSTE_SIGNATAIRE').val('');
      $('#DESC_POSTE_SIGNATAIRE').attr('placeholder','Autre poste');
      disableOptions(selectElement);

    }else{
      $('#autre_poste').delay(100).hide('show');
      $('#DESC_POSTE_SIGNATAIRE').val('');
      $('#DESC_POSTE_SIGNATAIRE').attr('placeholder','Autre poste');
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

  function hide_newPoste()
  {
    $('#autre_poste').hide();
    var selectElement = document.getElementById("POSTE_SIGNATAIRE_ID");
    enableOptions(selectElement)
    selectElement.value='';
  }
  function save_newPoste()
  {
    var DESC_POSTE_SIGNATAIRE = $('#DESC_POSTE_SIGNATAIRE').val();
    var statut = 2;
    if (DESC_POSTE_SIGNATAIRE == "") {
      $('#DESC_POSTE_SIGNATAIRE').css('border-color','red');
      statut = 1;
    }

    if(statut == 2)
    {
      $.ajax({
        url: "<?=base_url('')?>/double_commande_new/Signataire_Note/save_newPoste",
        type: "POST",
        dataType: "JSON",
        data: {
          DESC_POSTE_SIGNATAIRE:DESC_POSTE_SIGNATAIRE
        },
        beforeSend: function() {
          $('#loading_poste').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data) {
          $('#POSTE_SIGNATAIRE_ID').html(data.poste);
          POSTE_SIGNATAIRE_ID.InnerHtml=data.motifs;
          $('#loading_poste').html("");
          $('#POSTE_SIGNATAIRE_ID').val([]).trigger('change');
          $('#DESC_POSTE_SIGNATAIRE').attr('placeholder','Autre poste');
          $('#autre_poste').delay(100).hide('show');
        }
      });
    }
  }

  function getSousTutel()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    
    $.ajax(
    {
      url : "<?=base_url('/double_commande_new/Signataire_Note/getSousTutel')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:
      {
        INSTITUTION_ID:INSTITUTION_ID
      },
      beforeSend:function() {
        $('#loading_sous_tutel').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success:function(data)
      {   
        $('#SOUS_TUTEL_ID').html(data.tutel);
        $('#loading_sous_tutel').html("");
      }
    });
  }

  // function hideButton()
  // {
  //   var element = document.getElementById("myElement");
  //   element.style.display = "none";

  //   var elementmod = document.getElementById("mod");
  //   elementmod.style.display = "none";
  // }
</script>

<script type="text/javascript">
  function save() 
  {
    var statut = 2;

    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var POSTE_SIGNATAIRE_ID = $('#POSTE_SIGNATAIRE_ID').val();
    var NOM_PRENOM = $('#NOM_PRENOM').val();

    $('#error_INSTITUTION_ID').html('');
    $('#error_POSTE_SIGNATAIRE_ID').html('');
    $('#error_NOM_PRENOM').html('');


    if (INSTITUTION_ID == '') {
      statut = 1;
      $('#error_INSTITUTION_ID').html("<?= lang('messages_lang.champ_obligatoire_transmission_du_bordereau') ?>");
    } else {
      $('#error_INSTITUTION_ID').html("");
    }


    if (POSTE_SIGNATAIRE_ID == '') {
      $('#error_POSTE_SIGNATAIRE_ID').html('<?= lang('messages_lang.champ_obligatoire_transmission_du_bordereau') ?>');
      statut = 1;
    }

    if (NOM_PRENOM == '') {
      $('#error_NOM_PRENOM').html('<?= lang('messages_lang.champ_obligatoire_transmission_du_bordereau') ?>');
      statut = 1;
    }

    if (statut == 2) 
    {
      var INSTITUTION_ID_VERIFY=$('#INSTITUTION_ID option:selected').text();
      $('#INSTITUTION_ID_VERIFY').html(INSTITUTION_ID_VERIFY)

      if ($('#SOUS_TUTEL_ID').val()!='')
      {
        $('#st').attr('hidden',false)
        var SOUS_TUTEL_ID_VERIFY=$('#SOUS_TUTEL_ID option:selected').text();
        $('#SOUS_TUTEL_ID_VERIFY').html(SOUS_TUTEL_ID_VERIFY)
      }
      $('#POSTE_SIGNATAIRE_ID_VERIFY').html($('#POSTE_SIGNATAIRE_ID option:selected').text())
      $('#NOM_PRENOM_VERIFY').html(NOM_PRENOM)
      
      $('#prep_projet').modal('show')
    }
  }
</script>

<script type="text/javascript">
  function save_etap2()
  {
    $('#my_form').submit()
  }
</script>
