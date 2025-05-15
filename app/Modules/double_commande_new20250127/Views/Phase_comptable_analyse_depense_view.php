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

                                        <div style="float: right;">
                                            <a href="<?php echo base_url('double_commande_new/Liste_Paiement') ?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i>
                                                <?= lang('messages_lang.labelle_list') ?></a>
                                                      
                                        </div>
                                        <div>
                                            <font style="font-size:18px,color: #333">
                                                <h4> <?= lang('messages_lang.labelle_phasec') ?>: <?php if (!empty($etapes)) { ?>
                                                        <?= $etapes['DESC_ETAPE_DOUBLE_COMMANDE'] ?>
                                                    <?php    } ?></h4>
                                            </font>
                                        </div>
                                        <hr>
                                        <!-- debut -->
                                        <div class="row col-md-12" style="width:90%">

                                        </div>
                                        <div style="width: 90%">
                                            <div id="accordion">
                                                <div class="card-header" id="headingThree" style="padding: 0; display: flex; justify-content: space-between">
                                                    <h5 class="mb-0">
                                                        <button style="background:#061e69; color:#fff; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> <?= lang('messages_lang.labelle_historique') ?>
                                                        </button>
                                                    </h5>
                                                </div>
                                            </div>
                                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                                                <?php include  'includes/Detail_View.php'; ?>  
                                            </div>

                                                 
                                            <!-- fin -->
                                            <form action="<?= base_url('double_commande_new/Phase_comptable/save_analyse_depense') ?>" id="MyFormData" method="post">

                                                <input type="hidden" name="id" value="<?= $id['EXECUTION_BUDGETAIRE_DETAIL_ID'] ?>">
                                                <input type="hidden" name="ETAPE_ID" class="form-control" value="<?= $id['ETAPE_DOUBLE_COMMANDE_ID'] ?>">
                                                <input type="hidden" name="paiement" id="paiment_id" value="<?= $etapes['MONTANT_PAIEMENT'] ?>">
                                                <div class="col-md-12 container " style="border-radius:10px">
                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
                                                            <label for=""><?= lang('messages_lang.labelle_d_recep') ?><font color="red">*
                                                                </font>
                                                            </label>
                                                            <input type="date" name="date_reception" value="<?= set_value('DATE_RECEPTION') ?>" min="<?= date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION'])) ?>" max="<?= date('Y-m-d') ?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)" id="date_reception_id" class="form-control">
                                                            <font color="red" id="date_reception_id_error"></font>
                                                            <font color="red" id="charCount1"></font>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label for=""> <?= lang('messages_lang.labelle_d_signature') ?><font color="red">*</font>
                                                            </label>
                                                            <input type="date" name="date_signature_titre" onkeypress="return false" min="<?= date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION'])) ?>" max="<?= date('Y-m-d') ?>" id="date_signature_id" class="form-control">
                                                            <font color="red" id="date_signature_error"></font>
                                                        </div>
                                                    </div>
                                                        <!-- Je le select -->
                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
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
                                                        <div class="col-md-6">
                                                            <label for=""> <?= lang('messages_lang.labelle_d_tran')?><font color="red">*
                                                                </font>
                                                            </label>
                                                            <input type="date" name="date_transmission" max="<?= date('Y-m-d') ?>" onkeypress="return false" min="<?= date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION'])) ?>" id="date_transmission_id" class="form-control">
                                                            <font color="red" id="date_transmission_id_error">
                                                            </font>
                                                        </div>
                                                    </div>    
                                                        <!-- Je le select -->
                                                        <div class="col-md-6" id="show_motif" style="display:none;">
                                                            
                                                                <label for=""><?= lang('messages_lang.labelle_mot') ?><font color="red">*</font></label>
                                                                <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple>
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
                                                            </div>
                                                        


                                                    </div>
                                                </div>



                                            </form>
                                            <div style="float:right" class="mt-4">
                                                <a class="btn btn-primary" onclick="save_dossier()" class="form-control"> <?= lang('messages_lang.btn_enrg') ?></a>
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
    <?php echo view('includesbackend/scripts_js.php'); ?>
    <div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"> <?= lang('messages_lang.titre_modal') ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive overflow-auto mt-2">
                        <table class=" table  m-b-0 m-t-20">
                            <tbody>
                                <tr>
                                    <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_d_recep') ?></td>
                                    <td id="date_reception_id_modal"></td>
                                </tr>
                                </tr>
                                <tr>
                                    <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_d_tran') ?></td>
                                    <td id="date_transmission_id_modal"></td>
                                </tr>
                                </tr>

                                <tr>
                                    <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_d_signature') ?></td>
                                    <td id="date_signature_id_modal"></td>
                                </tr>
                                </tr>

                                <tr>
                                    <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_operatio') ?></td>
                                    <td id="operation_validation_modal"></td>
                                </tr>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="mod" type="button" class="btn btn-secondary" data-dismiss="modal"> <i class=" fa fa-edit"></i>
                        <?= lang('messages_lang.labelle_mod') ?></button>
                    <button id="myElement" onclick="save_info();hideButton()" type="button" class="btn btn-info"><i class="fa fa-check"></i>
                        <?= lang('messages_lang.labelle_conf') ?></button>
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
    function get_min_trans()
    {
        $("#date_transmission_id").prop('min', $("#date_reception_id").val());
        $("#date_signature_id").prop('min', $("#date_reception_id").val());
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
<script>
    function save_dossier() {
        var statut = true;
        var date_transmission_id = $('#date_transmission_id').val();
        var date_reception_id = $('#date_reception_id').val();
        var date_signature_titre_id = $('#date_signature_id').val();

        var ID_OPERATION = $('#ID_OPERATION').val();
        var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();

        if (ID_OPERATION == "") {
            statut = false;
            $("#error_ID_OPERATION").html("<?= lang('messages_lang.error_sms') ?>");
        } else {
            $("#error_ID_OPERATION").html("");
        }

        if (date_transmission_id == "") {
            statut = false;
            $("#date_transmission_id_error").html("<?= lang('messages_lang.error_sms') ?>");
        } else {
            $("#date_transmission_id_error").html("");
        }

        if (date_reception_id == "") {
            statut = false;
            $("#date_reception_id_error").html("<?= lang('messages_lang.error_sms') ?>");
        } else {
            $("#date_reception_id_error").html("");
        }

        if (date_signature_titre_id == "") {
            statut = false;
            $("#date_signature_error").html("<?= lang('messages_lang.error_sms') ?>");
        } else {
            $("#date_signature_error").html("");
        }

        if (statut == true) {
            var date = moment(date_reception_id, "YYYY/mm/DD")
            var reception_date = date.format('DD/mm/YYYY')
            var date1 = moment(date_transmission_id, "YYYY/mm/DD")
            var transmission_date = date1.format('DD/mm/YYYY')
            var date2 = moment(date_signature_titre_id, "YYYY/mm/DD")
            var signature_date = date2.format('DD/mm/YYYY')
            var operation_validation = $('#ID_OPERATION option:selected').toArray().map(item => item.text).join();

            $('#date_reception_id_modal').html(reception_date);
            $('#date_transmission_id_modal').html(transmission_date);
            $('#date_signature_id_modal').html(signature_date);
            $('#operation_validation_modal').html(operation_validation);
            $('#detail').modal()
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

        } else {

            if (OPERATION == 1 || OPERATION == 3) {
                $('#show_motif').show();
            } else {

                $('#show_motif').hide();
            }
        }
    }
</script>