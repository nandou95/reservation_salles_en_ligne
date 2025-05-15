<script>
$(document).ready(function() {
    get_mont()
    var transfert_id1 = $('#transfert_id1').val();
    var sum_montant_transfert = $('#sum_montant_transfert').val();
    var transfert_informa = $('#transfert_id23').val();
    $('#transfert_id1').bind('paste', function(e) {
        e.preventDefault();
    });

    document.getElementById('mont').readOnly = true;
    document.getElementById('apres_transfert_id').readOnly = true;
    document.getElementById('mtapres_transfert').readOnly = true;
    document.getElementById('montant_ligne_transfert').readOnly = true;
    document.getElementById('montant_vote_active_transfert').readOnly = true;
    document.getElementById('mont_precis_apres_transfert').readOnly = true;

    var countDataTable = "<?=$countDataTable?>";
    if (countDataTable > 0) {
        $('#transfert_info').attr('hidden', false)
        $('#PTBA_ID1').prop('disabled', false)
    } else {
        $('#transfert_info').attr('hidden', true)
        $('#activite1').attr('hidden', true)
    }
    var summ_transfert_act = $('#summ_transfert_act').val();
    var transfert_credit = $('#transfert_credit').val();


    var montant_trouve_ttransfer = parseInt(transfert_credit) - parseInt(summ_transfert_act)
    $('#transfert_id23').val(montant_trouve_ttransfer);

    if ((parseInt(sum_montant_transfert) == parseInt(transfert_credit))) {

        var montant_trouve_ttransfer = parseInt(transfert_credit) - parseInt(summ_transfert_act)
        $('#transfert_id23').val(montant_trouve_ttransfer);
    }

    if (montant_trouve_ttransfer == 0) {
        $('#btn_save').attr('disabled', true)
    }

    if ((parseInt(sum_montant_transfert) == parseInt(transfert_credit)) && (parseInt(summ_transfert_act) ==
            parseInt(transfert_credit))) {
        $('#savetempo_transfer').hide();
    }

});
</script>

<script>
function savetemp() {
    statut = true;

    var montant_vote_id = $('#mont').val();
    var transfert = $('#transfert_id1').val();
    var ministere = $('#ministere_id').val();
    var mont_precis_transfert = $('#mont_precis_transfert').val();
    var transfert_id1 = $('#transfert_id1').val();
    var ptba = $('#PTBA_ID').val();
    var trimestre = $('#TRANCHE_ID').val();
    var linebugdetaire = $('#LIGNE_BUDG_ID').val();
    var observation_id = $('#observation_id').val();
    var aactiv_transfert_id = $('#aactiv_transfert_id').val();
    // var error_act_transfert= $('#error_act_transfert').val();
    if (aactiv_transfert_id == '') {
        $('#error_act_transfert').html('Le champ est obligatoire');
        statut = false;
    } else {
        $('#error_act_transfert').html('');
    }

    if (observation_id == '') {
        $('#observation_error').html('Le champ est obligatoire');
        statut = false;
    } else {
        $('#observation_error').html('');
    }

    if (linebugdetaire == '') {
        $('#error_line').html('Le champ est obligatoire');
        statut = false;
    } else {
        $('#error_line').html('');
    }

    // if (linebgd == '') 
    // {
    // 	$('#error_line').html('Le champ est obligatoire');
    // 	statut = false;
    // } else 
    // {
    // 	$('#error_line').html('');
    // }

    if (ptba == '') {
        $('#error_PTBA_ID').html('Le champ est obligatoire');
        statut = false;
    } else {
        $('#error_PTBA_ID').html('');
    }

    if (trimestre == '') {
        $('#trimestre_error').html('Le champ est obligatoire');
        statut = false;
    } else {
        $('#trimestre_error').html('');
    }

    if (transfert == '') {
        $('#error_montant_traansfer_valide').html('Le champ est obligatoire');
        statut = false;
    } else {
        $('#error_montant_traansfer_valide').html('');
    }

    if (ministere == '') {
        $('#error_ministere').html('Le champ est obligatoire');
        statut = false;
    } else {
        $('#error_ministere').html('');
    }

    if (parseInt(transfert) > parseInt(montant_vote_id)) {
        statut = false;
        $('#error_montant_traansfer').html('Vous ne pouvez pas transferer le montant supérieur au montant voté')
    } else {
        $('#error_montant_traansfer').html('')

    }


    if (parseInt(mont_precis_transfert) > parseInt(transfert_id1)) {
        $('#montant_activite_precis').html(
            'vous ne pouvez pas saisir le montant superieur à celle du montant à transfert');
        statut = false;
    } else {
        $('#montant_activite_precis').html('');
    }

    if (statut == true) {
        $('#Myform').submit();
    }
}
</script>

<script>
$(document).ready(function() {
    $('#identifiant').html("Bon d'engagement");
    $('#num_bon_eng').hide();
    $('#date_bon_eng').hide();
    $('#titre_decaiss').hide();
    $('#date_titre').hide();

    var MOUVEMENT_DEPENSE_ID = $('#Mouvement_id').val();
    if (MOUVEMENT_DEPENSE_ID == '') {
        $('#montant_realise_id').attr('disabled', true)
    }

});
</script>
<script>
/**fonction pour  recuperer les code budgetaires */
function get_code() {
    statut = true
    var ministere = $('#ministere_id').val();

    $.ajax({
        url: "<?=base_url()?>/transfert/Transfert/get_code/" + ministere,
        type: "POST",
        dataType: "JSON",
        success: function(data) {
            $('#LIGNE_BUDG_ID').html(data.code);

        }
    });

}
</script>
<script>
/**
 * fonction pour  recuperer les activites selon les lignes  budgetaires */
function get_activite() {
    statut = true
    var LIGNE_BUDG_ID = $('#LIGNE_BUDG_ID').val();

    $.ajax({
        url: "<?=base_url()?>/transfert/Transfert/get_activite/" + LIGNE_BUDG_ID,
        type: "POST",
        dataType: "JSON",
        success: function(data) {

            $('#aactiv_transfert_id').html(data.activites);


        }
    });

}
</script>
<script>
/**
 * fonction pour recuper les montqnt selon id ptba
 * 
 *  */
function get_mont() {
    statut = true
    var PTBA_ID = $('#PTBA_ID').val();
    $.ajax({
        url: "<?=base_url()?>/transfert/Transfert/get_montantss/" + PTBA_ID,
        type: "GET",
        dataType: "JSON",
        success: function(data) {
            $('#mont').val(data.mont);
            $('#montant_vote_label').text("Montant voté annuel");
        }
    });

}
</script>

<script>
/**fonction pour recuper les montqnt selon id ptba */
function get_mont2() {
    statut = true
    var PTBA_ID = $('#aactiv_transfert_id').val();
    var mont_precis_transfert = $('#mont_precis_transfert').val();
    $.ajax({
        url: "<?=base_url()?>/transfert/Transfert/get_montant_act_transfert/" + PTBA_ID,
        type: "GET",
        dataType: "JSON",
        success: function(data) {
            $('#montant_vote_active_transfert').val(data.montant);
            $('#mont_precis_apres_transfert').val(parseInt(data.montant) + parseInt(mont_precis_transfert));

        }
    });

}
</script>
<script>
/***permet de verifier le montant precis sur l activite qu il est superieur au montant à tranferer */
function valide_montant3() {
    var mont_precis_transfert = $('#mont_precis_transfert').val();
    var transfert_id1 = $('#transfert_id1').val();

    if (parseInt(mont_precis_transfert) > parseInt(transfert_id1)) {
        $('#montant_activite_precis').html(
            'vous ne pouvez pas saisir le montant superieur à celle du montant à transfert');
        statut = false;
    } else {
        $('#montant_activite_precis').html('');
    }
}
</script>

<script>
/**
 * fonction pour recuperer la sommation du montant selon  le code noomanclature slectionner
 */
function summation_activite() {
    statut = true
    var LIGNE_BUDG_ID = $('#LIGNE_BUDG_ID').val();
    $.ajax({
        url: "<?=base_url()?>/transfert/Transfert/get_summ_activite/" + LIGNE_BUDG_ID,
        type: "POST",
        dataType: "JSON",
        success: function(data) {
            $('#montant_ligne_transfert').val(data.activite_sum);
            var transfert = $('#transfert_id1').val();
            var montant_ligne_transfert = $('#montant_ligne_transfert').val();

            var total_apres_transfer_lignebudgt = (parseInt(transfert) + parseInt(montant_ligne_transfert))
            $('#mtapres_transfert').val(total_apres_transfer_lignebudgt);

        }
    });
}
</script>
<script>
$('#message').delay('slow').fadeOut(7000);
</script>

<!-- <script>
		function valide_montant2() {
			var transfert = $('#transfert_id1').val();
			var transfert_credit = $('#transfert_credit').val();
			if (parseInt(transfert) > parseInt(transfert_credit)) {
				statut = false;
				$('#error_montant_traansfer').html(
					'Vous ne pouvez pas transferer le montant supérieur au montant transfert credit')
			} else {
				$('#error_montant_traansfer').html('')
			}
		}
	</script> -->
<script>
/**la on va trouver tous les controlles concerant les montants */
function valide_montant() {
    statut = true;
    var montant_vote_id = $('#mont').val();
    var transfert = $('#transfert_id1').val();
    var transfert_credit = $('#transfert_credit').val();


    if (parseInt(transfert) > parseInt(montant_vote_id)) {
        statut = false;
        $('#error_montant_traansfer').html('Vous ne pouvez pas transferer le montant supérieur au montant voté')
    } else {
        $('#error_montant_traansfer').html('')

        var total = parseInt(montant_vote_id) - transfert;
        $('#mont_precis_transfert').val(transfert);
        $('#mont_precis_transfert').attr('disabled', true);

        $('#apres_transfert_id').val(total);
    }

    if (parseInt(transfert) > parseInt(transfert_credit)) {
        statut = false;
        $('#error_montant_traansfer').html(
            'Vous ne pouvez pas transferer le montant supérieur au montant transfert credit')
    } else {
        $('#error_montant_traansfer').html('')
    }

}
</script>

<script>
/**
 * fonction pour recuperer les montant precis sur l activite qu on veux effectuer le transfert
 */
function get_montant_precis() {
    var mont_precis_transfert = $('#mont_precis_transfert').val();
    var montant_vote_active_transfert = $('#montant_vote_active_transfert').val();
    var total = parseInt(mont_precis_transfert) + parseInt(montant_vote_active_transfert)
    $("#mont_precis_apres_transfert").val(total);
}
</script>


<script type="text/javascript">
function getMontantAnnuel(argument) {

    var PTBA_ID = $('#PTBA_ID').val();
    var TRANCHE_ID = $('#TRANCHE_ID').val();

    // if (TRANCHE_ID==5) {

    if (PTBA_ID == '') {
        $('#error_TRANCHE_ID2').text("Vous devriez dabord sélectionner une activité");
        $('#TRANCHE_ID').val('')
    } else {

        $('#error_TRANCHE_ID2').text("");
        $('#error_MONTANT_TRANSFERT_SUP').text("");
        $.ajax({
            url: "<?=base_url('/transfert/Transfert/getMontantAnnuel')?>",
            type: "POST",
            dataType: "JSON",
            data: {
                PTBA_ID: PTBA_ID,
                TRANCHE_ID: TRANCHE_ID
            },
            beforeSend: function() {
                $('#loading_montant_transfert').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
            },
            success: function(data) {
                if (TRANCHE_ID == 5) {
                    $('#transfert_id1').attr('disabled', true);
                    $('#mont_precis_transfert').attr('disabled', true);
                    $('#transfert_id1').val(data.MONTANT_TRANSFERT);
                    $('#mont_precis_transfert').val(data.MONTANT_TRANSFERT);
                    $('#mont').val(data.MONTANT_VOTE);
                    $('#apres_transfert_id').val(parseInt(data.MONTANT_VOTE) - parseInt(data
                        .MONTANT_TRANSFERT));
                    $('#montant_vote_label').text("Montant voté annuel");
                } else {
                    if (TRANCHE_ID == 1) {
                        var DESC_TRANCHE = 'première';
                    } else if (TRANCHE_ID == 2) {
                        var DESC_TRANCHE = 'deuxième';
                    } else if (TRANCHE_ID == 3) {
                        var DESC_TRANCHE = 'troisième';
                    } else if (TRANCHE_ID == 4) {
                        var DESC_TRANCHE = 'quatrième';
                    }
                    $('#transfert_id1').val('');
                    $('#transfert_id1').attr('disabled', false);
                    $('#mont').val(data.MONTANT_VOTE);
                    $('#montant_vote_label').text("Montant voté du " + DESC_TRANCHE + " trimestre");
                }
                $('#loading_montant_transfert').html("");
            }
        });
    }
}
</script>