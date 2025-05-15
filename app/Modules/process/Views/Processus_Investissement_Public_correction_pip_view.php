<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo view('includesbackend/header.php'); ?>
</head>

<body>
    <div class="wrapper">
        <?php echo view('includesbackend/navybar_menu.php'); ?>
        <div class="main">
            <?php echo view('includesbackend/navybar_topbar.php'); ?>
            <main class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                                <div class="card-header">


                                </div>

                                <div class="card-body" style="margin-top: -20px">
                                </div>
                            </div>
                            <br>
                            <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                                <div class="col-md-12 d-flex">
                                    <div class="col-md-6" style="float: left;">
                                        <h4 style="margin-left: 1%;margin-top:10px"><?= lang('messages_lang.titre_correction_PIP') ?> </h4>
                                    </div>

                                </div>
                                <div class="col-md-12">
                                    <div class="col-md-6">
                                        <?php if (session()->getFlashKeys('alert')) : ?>
                                            <div class="w-100 bg-success text-white text-center" id="message">
                                                <?php echo session()->getFlashdata('alert')['message']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    if (empty($id)) {
                                        return redirect('pip/Projet_Pip_A_Compiler/liste_pip_proposer');
                                    }
                                    ?>
                                    <form id="myform" action="<?= base_url('pip/Processus_Investissement_Public/save_correction_pip') ?>" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="id_doc_compilation" id="id_doc_compilation" value="<?= $id ?>">
                                        <input type="hidden" id="demande_id" value="<?= $getdemande_id['ID_DEMANDE'] ?>">
                                        <input type="hidden" name="action" id="carrent_action_id" value="<?= $etape['ACTION_ID'] ?>">
                                        <input type="hidden" name="etape" id="carrent_etape_id" value="<?= $etape['ETAPE_ID'] ?>">
                                        <input type="hidden" name="etape_suivante" id="MOVETO" value="<?= $etape['MOVETO'] ?>">
                                        <div class="form-group col-md-8 d-flex">
                                            <input type="file" name="file_compiler" accept='.pdf' class="form-control w-75 mt-4" required>
                                            <a onclick="get_modal()" class="btn btn-primary mt-4 ml-3 " style="width:100px;height:40px"><?= lang('messages_lang.action_corriger_pip') ?></a>
                                        </div>
                                    </form>

                                    <div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.titre_confirmation') ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <?= lang('messages_lang.titre_modal') ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('messages_lang.bouton_modifier') ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="save_correction()"><?= lang('messages_lang.bouton_confirmer') ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="card-body">
                                    <div class="row">
                                        <?php
                                        if (session()->getFlashKeys('alert')) {
                                        ?>
                                            <div class="col-md-12">
                                                <div class="w-100 bg-success text-white text-center" id="message">
                                                    <?php echo session()->getFlashdata('alert')['message']; ?>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>

                                        <div class="col-md-4">

                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="table-responsive" style="width: 100%;">
                                            <table id="mytable12" class=" table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th><?= lang('messages_lang.labelle_numero_projet') ?></th>
                                                        <th><?= lang('messages_lang.labelle_nom_du_projet') ?></th>
                                                        <th><?= lang('messages_lang.labelle_institution') ?></th>
                                                        <th><?= lang('messages_lang.actions_action') ?></th>
                                                    </tr>
                                                </thead>

                                            </table>
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
<script>
    function get_modal() {
        $('#modal1').modal('show')
    }

    function save_correction() {
        $("#myform").submit()
    }
</script>
<script>
    $(document).ready(function() {
        liste()
    });
</script>
<script>
    function liste() {
        var id = $('#id_doc_compilation').val();
        var row_count = "1000000";
        $("#mytable12").DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "<?= base_url('pip/Processus_Investissement_Public/liste_projet_pip_correction') ?>/" + id,
                type: "POST",

            },

            lengthMenu: [
                [10, 50, 100, row_count],
                [10, 50, 100, "All"]
            ],
            pageLength: 10,
            "columnDefs": [{
                "targets": [],
                "orderable": false
            }],

            dom: 'Bfrtlip',
            order: [],
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            language: {
                "sProcessing": "<?= lang('messages_lang.labelle_et_traitement') ?>",
                "sSearch": "<?= lang('messages_lang.labelle_et_rechercher') ?>&nbsp;:",
                "sLengthMenu": "<?= lang('messages_lang.labelle_et_afficher') ?> _MENU_ <?= lang('messages_lang.labelle_et_element') ?>",
                "sInfo": "<?= lang('messages_lang.labelle_et_affichage_element') ?> _START_ <?= lang('messages_lang.labelle_et_a') ?> _END_ <?= lang('messages_lang.labelle_et_sur') ?> _TOTAL_ <?= lang('messages_lang.labelle_et_element') ?>",
                "sInfoEmpty": "<?= lang('messages_lang.labelle_et_vide') ?>",
                "sInfoFiltered": "(<?= lang('messages_lang.labelle_et_affichage_filtre') ?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal') ?>)",
                "sInfoPostFix": "",
                "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
                "sZeroRecords": "<?= lang('messages_lang.labelle_et_aucun_element') ?>",
                "sEmptyTable": "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
                "oPaginate": {
                    "sFirst": "<?= lang('messages_lang.labelle_et_premier') ?>",
                    "sPrevious": "<?= lang('messages_lang.labelle_et_precedent') ?>",
                    "sNext": "<?= lang('messages_lang.labelle_et_suivant') ?>",
                    "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
                },
                "oAria": {
                    "sSortAscending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>",
                    "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_colone') ?>"
                }
            }
        });
    }
</script>