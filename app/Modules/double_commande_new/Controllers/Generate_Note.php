<?php
/**Alain Charbel Nderagakura
*Titre: Generer la note
*Numero de telephone: +257 62003511/76887837
*Email pro: charbel@mediabox.bi
*Date: 19/7/2024
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use Config\Services;
use Dompdf\Dompdf;
use Dompdf\Exception;
ini_set('max_execution_time', 20000);
ini_set('memory_limit','4048M');
class Generate_Note extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		// define("DOMPDF_ENABLE_REMOTE", true);
	}

	//function pour generer la note
	public function generate_note($value=0,$provenance=0)
	{
		$data = $this->urichk();
		$dompdf = new Dompdf();
  	// Charger la vue dans Dompdf  
  	$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$value;

  	$callpsreq = "CALL getRequete(?,?,?,?);";
  	$bindparams = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID','execution_budgetaire_titre_decaissement','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
  	$bindparams = str_replace('\\','',$bindparams);
  	$res= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
  	$EXECUTION_BUDGETAIRE_DETAIL_ID = $res['EXECUTION_BUDGETAIRE_DETAIL_ID'];

		$detail=$this->detail_new(MD5($res['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
		$get_info = $detail['get_info'];
		$get_infoEBET=$detail['get_infoEBET'];
		$EXECUTION_BUDGETAIRE_ID=$get_info['EXECUTION_BUDGETAIRE_ID'];
		$montantvote = $detail['montantvote'];		
		$intro_note=$get_info['INTRODUCTION_NOTE'];

		foreach ($get_infoEBET as $key)
		{
			$crd_an_rest_tach=floatval($key->BUDGET_RESTANT_T1)+floatval($key->BUDGET_RESTANT_T2)+floatval($key->BUDGET_RESTANT_T3)+floatval($key->BUDGET_RESTANT_T4);

			//credit vote sur activite pap
			$vote_activite = "";
			if (!empty($key->PAP_ACTIVITE_ID))
			{
				$req_activ='SELECT SUM(ptba.BUDGET_ANNUEL) as budg_vote FROM ptba_tache ptba WHERE ptba.PAP_ACTIVITE_ID='.$key->PAP_ACTIVITE_ID.'';
				$req_activ = "CALL getTable('".$req_activ."');";
				$vote_activite = $this->ModelPs->getRequeteOne($req_activ);
			}

			//credit annuel transfere pour la tache
			$req_tach='SELECT SUM(trans.MONTANT_TRANSFERT) as trans_tache FROM transfert_historique_transfert trans WHERE trans.PTBA_TACHE_ID_TRANSFERT='.$key->PTBA_TACHE_ID;
			$req_tach = "CALL getTable('".$req_tach."');";
			$trans_tache = $this->ModelPs->getRequeteOne($req_tach);

			//credit trimestriel transfere pour la tache
			$req_tach_trim='SELECT SUM(trans.MONTANT_TRANSFERT) as trans_tache FROM transfert_historique_transfert trans WHERE trans.PTBA_TACHE_ID_TRANSFERT='.$key->PTBA_TACHE_ID.' AND TRIMESTRE_ID='.$get_info['TRIMESTRE_ID'];
			$req_tach_trim = "CALL getTable('".$req_tach_trim."');";
			$trans_tache_trim = $this->ModelPs->getRequeteOne($req_tach_trim);

			//montant deja liquide sur la tache
			$liqu_tach='SELECT SUM(MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION FROM execution_budgetaire_execution_tache exec_tach JOIN execution_budgetaire exec ON exec_tach.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE PTBA_TACHE_ID='.$key->PTBA_TACHE_ID.' AND TRIMESTRE_ID='.$get_info['TRIMESTRE_ID'];
			$liqu_tach = "CALL getTable('".$liqu_tach."');";
			$liqu_tach = $this->ModelPs->getRequeteOne($liqu_tach);

			$creditVote=0;
			$crd_rest_tache_trim=0;
			$reserve=0;
			$CODE_TRANCHE=$get_info['TRIMESTRE_ID'];
			if ($CODE_TRANCHE == 1)
			{
				$creditVote=floatval($key->T1);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T1);
				$reserve=floatval($key->BUDGET_UTILISE_T1);
			}
			else if ($CODE_TRANCHE == 2)
			{
				$creditVote=floatval($key->T2);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T2);
				$reserve=floatval($key->BUDGET_UTILISE_T2);

			}
			else if ($CODE_TRANCHE == 3)
			{
				$creditVote=floatval($key->T3);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T3);
				$reserve=floatval($key->BUDGET_UTILISE_T3);

			}
			else if ($CODE_TRANCHE == 4)
			{
				$creditVote=floatval($key->T4);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T4); 
				$reserve=floatval($key->BUDGET_RESTANT_T4);
			}
		}
		
    //utilisateur
		$req_users="";
		if($provenance==1)
		{
			$req_users='SELECT user.USER_ID,NOM,PRENOM,PROFIL_DESCR FROM execution_budgetaire_tache_detail_histo histo JOIN user_users user ON user.USER_ID=histo.USER_ID JOIN user_profil prof ON prof.PROFIL_ID=user.PROFIL_ID WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'" AND ETAPE_DOUBLE_COMMANDE_ID=3';
		}
		else
		{
			$req_users='SELECT user.USER_ID,NOM,PRENOM,PROFIL_DESCR FROM execution_budgetaire_tache_detail_histo histo JOIN user_users user ON user.USER_ID=histo.USER_ID JOIN user_profil prof ON prof.PROFIL_ID=user.PROFIL_ID WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'" AND ETAPE_DOUBLE_COMMANDE_ID=11';
		}
		$req_users = "CALL getTable('".$req_users."');";
		$users = $this->ModelPs->getRequeteOne($req_users);

		//credit vote/transfere sur la ligne budgetaire
		$req='SELECT SUM(ptba.BUDGET_ANNUEL) as budg_vote,SUM(trans.MONTANT_TRANSFERT) as budg_trans,SUM(ptba.BUDGET_RESTANT_T1) AS restant_t1,SUM(ptba.BUDGET_RESTANT_T2) AS restant_t2,SUM(ptba.BUDGET_RESTANT_T3) AS restant_t3,SUM(ptba.BUDGET_RESTANT_T4) AS restant_t4 FROM ptba_tache ptba LEFT JOIN transfert_historique_transfert trans ON trans.PTBA_TACHE_ID_TRANSFERT=ptba.PTBA_TACHE_ID WHERE ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$get_infoEBET[0]->CODE_NOMENCLATURE_BUDGETAIRE_ID.'';
		$req = "CALL getTable('".$req."');";
		$vote_ligne = $this->ModelPs->getRequeteOne($req);

		//get signataires sur la note
		$signataire='SELECT sign.POSTE_SIGNATAIRE_ID,poste.DESC_POSTE_SIGNATAIRE,NOM_PRENOM FROM inst_institutions_signataires_notes sign JOIN poste_signataire poste ON poste.POSTE_SIGNATAIRE_ID=sign.POSTE_SIGNATAIRE_ID WHERE 1 AND INSTITUTION_ID='.$get_infoEBET[0]->INSTITUTION_ID.' AND SOUS_TUTEL_ID='.$get_infoEBET[0]->SOUS_TUTEL_ID.' ORDER BY INSTITUTION_SIGNATAIRE_ID DESC';
		$signataire = "CALL getTable('".$signataire."');";
		$signataire = $this->ModelPs->getRequete($signataire);

		$html="<html><div style='font-size:13px;'>";
		$html.="
		<div style='float:left;height: 0vh;'>
		<p><b>REPUBLIQUE DU BURUNDI</b></p>
		</div>&nbsp;
		<div style='float:right;;height: 0vh'>
		<p><b>...........,le...../....../202..</b></p>
		</div>
		";
		$dompdf->set_option('chroot', ROOTPATH);
		$dompdf->set_option('base_path', base_url());

		$html.= '<div><br><br>
		<div style="">
		<img style="width: 75px;height: 5vh;" src="assets_frontend/img/logo_burundi.png"><br>
		</div>';

		$html.="<p>".$get_infoEBET[0]->DESCRIPTION_INSTITUTION."</p>";
		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<b>CABINET DU MINISTERE</b>";
		}else{
			$html.="<b>CABINET .......</b>";
		}
		$html.="<p><b>Réf: </b>...................</p></p>";
		
		$html.="<center><b><u>NOTE AU DIRECTEUR DE LA COMPTABILITE PUBLIQUE</u></b></center>";
		$html.="<div style='word-wrap: break-word;'>".$intro_note."</div><br>";
		$html.="<table>";
		$html.="<tr>
		<td>Exercice : </td>
		<td><strong>".$get_info['ANNEE_DESCRIPTION']."</strong></td>
		</tr>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<tr>
			<td>Code programme : </td>
			<td><strong>".$get_infoEBET[0]->CODE_PROGRAMME."</strong></td>
			</tr>";

			$html.="<tr>
			<td>Code programmatique : </td>
			<td><b>".$get_infoEBET[0]->CODES_PROGRAMMATIQUE."</b></td>
			</tr>";
		}
		else
		{
			$html.="<tr>
			<td>Code Dotation : </td>
			<td><b>".$get_infoEBET[0]->CODES_PROGRAMMATIQUE."</b></td>
			</tr>";
		}

		$html.="<tr>
			<td>Imputation : </td>
			<td><strong>".$get_infoEBET[0]->CODE_NOMENCLATURE_BUDGETAIRE."</strong></td>
		</tr>";

		$html.="<tr>
				<td>Intitulé de la ligne budgétaire : </td>
				<td><strong>".$get_infoEBET[0]->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE."</strong></td>
			</tr>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<tr>
				<td>Intitulé de l’activité PAP  : </td>
				<td><strong>".$get_infoEBET[0]->DESC_PAP_ACTIVITE."</strong></td>
			</tr>";
		}

		$html.="<tr>
			<td>Intitulé de la tâche	 : </td>
			<td style='word-break: break-word;'><strong>".addslashes($get_infoEBET[0]->DESC_TACHE)."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Crédit voté sur la ligne budgétaire   : </td>
			<td><strong>".$vote_ligne['budg_vote']."</strong></td>
		</tr>";

		$credi_trans=!empty($vote_ligne['budg_trans'])?$vote_ligne['budg_trans']:0;
		$html.="<tr>
			<td>Crédit transféré sur la ligne budgétaire : </td>
			<td><strong>".$credi_trans."</strong></td>
		</tr>";
		$crd_lgn_apre_trans=floatval($vote_ligne['budg_vote'])-floatval($credi_trans);
		$html.="<tr>
			<td>Crédit de la ligne budgétaire après transfert : </td>
			<td><strong>".$crd_lgn_apre_trans."</strong></td>
		</tr>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<tr>
				<td>Crédit voté pour l’activité PAP  : </td>
				<td><strong>".$vote_activite['budg_vote']."</strong></td>
			</tr>";
		}

		$html.="<tr>
			<td>Crédit annuel voté pour la tâche: </td>
			<td><strong>".$get_infoEBET[0]->BUDGET_ANNUEL."</strong></td>
		</tr>";		

		$trans_tach=!empty($trans_tache['trans_tache'])?$trans_tache['trans_tache']:0;
		$crd_tach_ap_trans=$get_infoEBET[0]->BUDGET_ANNUEL-$trans_tach;
		$html.="<tr>
			<td>Crédit annuel de la tâche après transfert : </td>
			<td><strong>".$crd_tach_ap_trans."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Crédit trimestriel voté pour la tâche	: </td>
			<td><strong>".$creditVote."</strong></td>
		</tr>";

		$trans_trim_tach=!empty($trans_tache_trim['trans_tache'])?$trans_tache_trim['trans_tache']:0;
		$tach_rest_trim=floatval($creditVote)-floatval($trans_trim_tach);
		$html.="<tr>
			<td>Crédit&nbsp;trimestriel&nbsp;de&nbsp;la&nbsp;tâche&nbsp;après&nbsp;transfert&nbsp;: </td>
			<td><strong>".$tach_rest_trim."</strong></td>
		</tr>";

		if(!empty($get_info['ENG_BUDGETAIRE_DEVISE']))
		{
			$html.="<tr>
				<td>Montant engagé en devise      : </td>
				<td><strong>".$get_info['ENG_BUDGETAIRE_DEVISE']." ".$get_info['DESC_DEVISE_TYPE']." au taux de :".$get_info['COUR_DEVISE']."</strong></td>
			</tr>";
		}
		$html.="<tr>
			<td>Montant engagé en BIF : </td>
			<td><strong>".$get_info['ENG_BUDGETAIRE']."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Bon d’Engagement n° : </td>
			<td><strong>".$get_info['NUMERO_BON_ENGAGEMENT']."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Date d’engagement : </td>
			<td><strong>".date('Y-m-d',strtotime($get_info['DATE_DEMANDE']))."</strong></td>
		</tr>";

		if($provenance==2)
		{
			$html.="<tr>
				<td>Montant liquidé : </td>
				<td><strong>".$get_info['MONTANT_LIQUIDATION']."</strong></td>
			</tr>";
		}

		$crd_rest_ann_lign=floatval($vote_ligne['restant_t1'])+floatval($vote_ligne['restant_t2'])+floatval($vote_ligne['restant_t3'])+floatval($vote_ligne['restant_t4']);
		$html.="<tr>
			<td>Crédit restant sur la ligne budgétaire : </td>
			<td><strong>".$crd_rest_ann_lign."</strong></td>
		</tr>";
		$html.="<tr>
			<td>Crédit annuel restant sur la tâche	: </td>
			<td><strong>".$crd_an_rest_tach."</strong></td>
			</tr>";
		
		$reste=floatval($creditVote)-floatval($liqu_tach['MONTANT_LIQUIDATION']);

		if($provenance==1)
		{
			$html.="<tr>
			<td>Crédit trimestriel restant sur la tâche: </td>
			<td><strong>".$crd_rest_tache_trim."</strong></td>
			</tr>";
		}
		else
		{
			$html.="<tr>
			<td>Crédit trimestriel restant sur la tâche: </td>
			<td><strong>".$reste."</strong></td>
			</tr>";
		}

		$html.="</table>";

		if(!empty($signataire))
		{
			$html .= "<div style='width:100%; display: flex; flex-wrap: wrap;'>";

			$index = 0;

			foreach ($signataire as $key)
			{
			  $alignement = ($index % 2 == 0) ? 'left' : 'right';
			  $marginStyle = ($alignement == 'left') ? 'margin: 10px;' : '';
			  $margin_top=($index>=2) ? '30px':'0px';

			  $html .= "
			  <div style='width: 40%; box-sizing: border-box; padding: 0 10px;{$marginStyle};float:{$alignement};margin-top:{$margin_top}'>
			      <div style=''>
			        <p><b>LE ".$key->DESC_POSTE_SIGNATAIRE."</b></p>
			        <p><b>".$key->NOM_PRENOM."</b></p>
			      </div>
			  </div>";

			  if ($index % 2 == 1) {
			    $html .= "<div style='width: 100%; clear: both;'></div>";
			  }

			  $index++;
			}
			$html .= "</div>";
		}
		else
		{
			if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
			{
				$html.="
					<div>
					<div>
						<div style='float:left'>
							<p><b><u>POUR ETABLISSEMENT:</u></b></p>
							<p><b>Responsable d’activités:</b></p>
							<p><b>.........................</b><br></p>
							<p><b>LE CONTROLEUR DES ENGAGEMENTS DE DEPENSES</b></p>
							<p><b>".$users['NOM'].' '.$users['PRENOM']."</b></p>
						</div>
						<div style='float:right'>
							<p><b><u>POUR APPROBATION :</u></b></p>
							<p><b>Responsable de programme</b></p>
							<p><b>.........................</b><br></p>
							<p><b>LE MINISTRE</b></p>
							<p><b>.........................</b></p>
						</div>
					</div>
				</div>";
			}
			else
			{
				$html.="
					<div>
					<div>
						<div style='float:left'>
							<p><b><u>POUR ETABLISSEMENT:</u></b></p>
							<p><b>.........................</b><br></p>
							<p><b>LE CONTROLEUR DES ENGAGEMENTS DE DEPENSES</b></p>
							<p><b>".$users['NOM'].' '.$users['PRENOM']."</b></p>
						</div>
						<div style='float:right'>
							<p><b><u>POUR APPROBATION :</u></b></p>
							<p><b>.........................</b><br></p>
							<p><b>LE RESPONSABLE </b></p>
							<p><b>.........................</b></p>
						</div>
					</div>
				</div>";
			}
		}
			        
		$html.="</div></html>";

    	// Charger le contenu HTML
		$dompdf->loadHtml($html);

    	// Définir la taille et l'orientation du papier
		$dompdf->setPaper('A4', 'portrait');

    	// Rendre le HTML en PDF
		$dompdf->render();

		$output = $dompdf->output();

		$name_file = 'LETTRE_OTB'.uniqid().'.pdf';
		$PATH_NOTE = 'uploads/double_commande_new/'.$name_file;
		file_put_contents($PATH_NOTE, $output);

		$where ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;          
		$insertIntoSupp='execution_budgetaire_tache_detail';
		$columsup="PATH_NOTE_A_LA_DCP = '".$name_file."'";
		$this->update_all_table($insertIntoSupp,$columsup,$where); 

		$pdf_base64 = base64_encode($output);
		$fichier = "<embed src=".base_url(''.$PATH_NOTE.'')." type='application/pdf' width='100%' height='600px' />";
		// $fichier = "<embed src='data:application/pdf;base64," . $pdf_base64 . "' type='application/pdf' width='100%' height='600px' />";
		$data['returns']=$fichier;
		$data['provenance']=$provenance;
		$data['message']=lang('messages_lang.eng_succ');
		return view("App\Modules\double_commande_new\Views\Visualise_Note_View",$data);
	}

	//generate note pour plusieures taches
	public function generate_note_plusieur($value=0,$provenance=0)
	{
		$data = $this->urichk();
		$dompdf = new Dompdf();
  	// Charger la vue dans Dompdf  
  	$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$value;

  	$callpsreq = "CALL getRequete(?,?,?,?);";
  	$bindparams = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID','execution_budgetaire_titre_decaissement','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
  	$bindparams = str_replace('\\','',$bindparams);
  	$res= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
  	$EXECUTION_BUDGETAIRE_DETAIL_ID = $res['EXECUTION_BUDGETAIRE_DETAIL_ID'];

		$detail=$this->detail_new(MD5($res['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
		$get_info = $detail['get_info'];
		$get_infoEBET=$detail['get_infoEBET'];
		$EXECUTION_BUDGETAIRE_ID=$get_info['EXECUTION_BUDGETAIRE_ID'];
		$montantvote = $detail['montantvote'];		
		$intro_note=$get_info['INTRODUCTION_NOTE'];

		$info_tache='
				<tr class="tr">
					<th class="th">Tâche</th>
					<th class="th">Crédit&nbsp;annuel voté&nbsp;pour&nbsp;la tâche</th>
					<th class="th">Crédit annuel de la tâche après transfert</th>
					<th class="th">Crédit trimestriel voté pour la tâche</th>
					<th class="th">Crédit trimestriel de la tâche après transfert</th>
					<th class="th">Crédit annuel restant sur la tâche</th>
					<th class="th">Crédit trimestriel restant sur la tâche</th>
				</tr>';
		$data_tache='';

		foreach ($get_infoEBET as $key)
		{
			$crd_an_rest_tach=floatval($key->BUDGET_RESTANT_T1)+floatval($key->BUDGET_RESTANT_T2)+floatval($key->BUDGET_RESTANT_T3)+floatval($key->BUDGET_RESTANT_T4);

			//credit vote sur activite pap
			$vote_activite = "";
			if (!empty($key->PAP_ACTIVITE_ID))
			{
				$req_activ='SELECT SUM(ptba.BUDGET_ANNUEL) as budg_vote FROM ptba_tache ptba WHERE ptba.PAP_ACTIVITE_ID='.$key->PAP_ACTIVITE_ID.'';
				$req_activ = "CALL getTable('".$req_activ."');";
				$vote_activite = $this->ModelPs->getRequeteOne($req_activ);
			}

			//credit annuel transfere pour la tache
			$req_tach='SELECT SUM(trans.MONTANT_TRANSFERT) as trans_tache FROM transfert_historique_transfert trans WHERE trans.PTBA_TACHE_ID_TRANSFERT='.$key->PTBA_TACHE_ID;
			$req_tach = "CALL getTable('".$req_tach."');";
			$trans_tache = $this->ModelPs->getRequeteOne($req_tach);

			//credit trimestriel transfere pour la tache
			$req_tach_trim='SELECT SUM(trans.MONTANT_TRANSFERT) as trans_tache FROM transfert_historique_transfert trans WHERE trans.PTBA_TACHE_ID_TRANSFERT='.$key->PTBA_TACHE_ID.' AND TRIMESTRE_ID='.$get_info['TRIMESTRE_ID'];
			$req_tach_trim = "CALL getTable('".$req_tach_trim."');";
			$trans_tache_trim = $this->ModelPs->getRequeteOne($req_tach_trim);

			//montant deja liquide sur la tache
			$liqu_tach='SELECT SUM(MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION FROM execution_budgetaire_execution_tache exec_tach JOIN execution_budgetaire exec ON exec_tach.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE PTBA_TACHE_ID='.$key->PTBA_TACHE_ID.' AND TRIMESTRE_ID='.$get_info['TRIMESTRE_ID'];
			$liqu_tach = "CALL getTable('".$liqu_tach."');";
			$liqu_tach = $this->ModelPs->getRequeteOne($liqu_tach);

			$creditVote=0;
			$crd_rest_tache_trim=0;
			$reserve=0;
			$CODE_TRANCHE=$get_info['TRIMESTRE_ID'];
			if ($CODE_TRANCHE == 1)
			{
				$creditVote=floatval($key->T1);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T1);
				$reserve=floatval($key->BUDGET_UTILISE_T1);
			}
			else if ($CODE_TRANCHE == 2)
			{
				$creditVote=floatval($key->T2);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T2);
				$reserve=floatval($key->BUDGET_UTILISE_T2);

			}
			else if ($CODE_TRANCHE == 3)
			{
				$creditVote=floatval($key->T3);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T3);
				$reserve=floatval($key->BUDGET_UTILISE_T3);

			}
			else if ($CODE_TRANCHE == 4)
			{
				$creditVote=floatval($key->T4);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T4); 
				$reserve=floatval($key->BUDGET_RESTANT_T4);
			}

			$trans_tach=!empty($trans_tache['trans_tache'])?$trans_tache['trans_tache']:0;
			$crd_tach_ap_trans=$key->BUDGET_ANNUEL-$trans_tach;

			$trans_trim_tach=!empty($trans_tache_trim['trans_tache'])?$trans_tache_trim['trans_tache']:0;
			$tach_rest_trim=floatval($creditVote)-floatval($trans_trim_tach);

			$reste=floatval($creditVote)-floatval($liqu_tach['MONTANT_LIQUIDATION']);
			$reste_tache='';
			if($provenance==1)
			{
				$reste_tache=$crd_rest_tache_trim;
			}
			else
			{
				$reste_tache=$reste;
			}

			$data_tache.='
				<tr class="tr">
					<td class="td">'.$key->DESC_TACHE.'</td>
					<td class="td">'.number_format($key->BUDGET_ANNUEL,$this->get_precision($key->BUDGET_ANNUEL),'',' ').'</td>
					<td class="td">'.number_format($crd_tach_ap_trans,$this->get_precision($crd_tach_ap_trans),'',' ').'</td>
					<td class="td">'.number_format($creditVote,$this->get_precision($creditVote),'',' ').'</td>
					<td class="td">'.number_format($tach_rest_trim,$this->get_precision($tach_rest_trim),'',' ').'</td>
					<td class="td">'.number_format($crd_an_rest_tach,$this->get_precision($crd_an_rest_tach),'',' ').'</td>
					<td class="td">'.number_format($reste_tache,$this->get_precision($reste_tache),'',' ').'</td>
				</tr>';
		}
		
    //utilisateur
		$req_users="";
		if($provenance==1)
		{
			$req_users='SELECT user.USER_ID,NOM,PRENOM,PROFIL_DESCR FROM execution_budgetaire_tache_detail_histo histo JOIN user_users user ON user.USER_ID=histo.USER_ID JOIN user_profil prof ON prof.PROFIL_ID=user.PROFIL_ID WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'" AND ETAPE_DOUBLE_COMMANDE_ID=3';
		}
		else
		{
			$req_users='SELECT user.USER_ID,NOM,PRENOM,PROFIL_DESCR FROM execution_budgetaire_tache_detail_histo histo JOIN user_users user ON user.USER_ID=histo.USER_ID JOIN user_profil prof ON prof.PROFIL_ID=user.PROFIL_ID WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'" AND ETAPE_DOUBLE_COMMANDE_ID=11';
		}
		$req_users = "CALL getTable('".$req_users."');";
		$users = $this->ModelPs->getRequeteOne($req_users);

		//credit vote/transfere sur la ligne budgetaire
		$req='SELECT SUM(ptba.BUDGET_ANNUEL) as budg_vote,SUM(trans.MONTANT_TRANSFERT) as budg_trans,SUM(ptba.BUDGET_RESTANT_T1) AS restant_t1,SUM(ptba.BUDGET_RESTANT_T2) AS restant_t2,SUM(ptba.BUDGET_RESTANT_T3) AS restant_t3,SUM(ptba.BUDGET_RESTANT_T4) AS restant_t4 FROM ptba_tache ptba LEFT JOIN transfert_historique_transfert trans ON trans.PTBA_TACHE_ID_TRANSFERT=ptba.PTBA_TACHE_ID WHERE ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$get_infoEBET[0]->CODE_NOMENCLATURE_BUDGETAIRE_ID.'';
		$req = "CALL getTable('".$req."');";
		$vote_ligne = $this->ModelPs->getRequeteOne($req);

		//get signataires sur la note
		$signataire='SELECT sign.POSTE_SIGNATAIRE_ID,poste.DESC_POSTE_SIGNATAIRE,NOM_PRENOM FROM inst_institutions_signataires_notes sign JOIN poste_signataire poste ON poste.POSTE_SIGNATAIRE_ID=sign.POSTE_SIGNATAIRE_ID WHERE 1 AND INSTITUTION_ID='.$get_infoEBET[0]->INSTITUTION_ID.' AND SOUS_TUTEL_ID='.$get_infoEBET[0]->SOUS_TUTEL_ID.'';
		$signataire = "CALL getTable('".$signataire."');";
		$signataire = $this->ModelPs->getRequete($signataire);

		$html="<html><div style='font-size:13px'>";
		$html.="
		<div style='float:left;height: 0vh;'>
		<p><b>REPUBLIQUE DU BURUNDI</b></p>
		</div>&nbsp;
		<div style='float:right;;height: 0vh'>
		<p><b>...........,le...../....../202..</b></p>
		</div>
		";
		$dompdf->set_option('chroot', ROOTPATH);
		$dompdf->set_option('base_path', base_url());

		$html.= '<div><br><br>
		<div style="">
		<img style="width: 75px;height: 5vh;" src="assets_frontend/img/logo_burundi.png"><br>
		</div>';

		$html.="<p>".$get_infoEBET[0]->DESCRIPTION_INSTITUTION."</p>";
		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<b>CABINET DU MINISTERE</b>";
		}else{
			$html.="<b>CABINET .......</b>";
		}
		$html.="<p><b>Réf: </b>...................</p></p>";
		
		$html.="<center><b><u>NOTE AU DIRECTEUR DE LA COMPTABILITE PUBLIQUE</u></b></center>";

		$html.="<div>".$intro_note."</div><br>";

		$html.="<table>";
		
		$html.="<tr>
		<td>Exercice : </td>
		<td><strong>".$get_info['ANNEE_DESCRIPTION']."</strong></td>
		</tr>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<tr>
			<td>Code programme : </td>
			<td><strong>".$get_infoEBET[0]->CODE_PROGRAMME."</strong></td>
			</tr>";

			$html.="<tr>
			<td>Code programmatique : </td>
			<td><b>".$get_infoEBET[0]->CODES_PROGRAMMATIQUE."</b></td>
			</tr>";
		}
		else
		{
			$html.="<tr>
			<td>Code Dotation : </td>
			<td><b>".$get_infoEBET[0]->CODES_PROGRAMMATIQUE."</b></td>
			</tr>";
		}

		$html.="<tr>
			<td>Imputation : </td>
			<td><strong>".$get_infoEBET[0]->CODE_NOMENCLATURE_BUDGETAIRE."</strong></td>
		</tr><br>";

		$html.="<tr>
			<td>Intitulé de la ligne budgétaire : </td>
			<td><strong>".$get_infoEBET[0]->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE."</strong></td>
		</tr><br>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<tr>
				<td>Intitulé de l’activité PAP  : </td>
				<td><strong>".$get_infoEBET[0]->DESC_PAP_ACTIVITE."</strong></td>
			</tr><br>";
		}

		$html.="<tr>
			<td>Intitulé de la tâche	 : </td>
			<td style='word-break: break-word;'><strong>".addslashes($get_infoEBET[0]->DESC_TACHE)."</strong></td>
		</tr><br>";

		$html.="<tr>
			<td>Crédit voté sur la ligne budgétaire   : </td>
			<td><strong>".$vote_ligne['budg_vote']."</strong></td>
		</tr>";

		$credi_trans=!empty($vote_ligne['budg_trans'])?$vote_ligne['budg_trans']:0;
		$html.="<tr>
			<td>Crédit transféré sur la ligne budgétaire : </td>
			<td><strong>".$credi_trans."</strong></td>
		</tr>";
		$crd_lgn_apre_trans=floatval($vote_ligne['budg_vote'])-floatval($credi_trans);
		$html.="<tr>
			<td>Crédit de la ligne budgétaire après transfert : </td>
			<td><strong>".$crd_lgn_apre_trans."</strong></td>
		</tr>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<tr>
				<td>Crédit voté pour l’activité PAP  : </td>
				<td><strong>".$vote_activite['budg_vote']."</strong></td>
			</tr>";
		}

		$html.="<tr>
			<td>Crédit annuel voté pour la tâche: </td>
			<td><strong>".$get_infoEBET[0]->BUDGET_ANNUEL."</strong></td>
		</tr>";		

		$trans_tach=!empty($trans_tache['trans_tache'])?$trans_tache['trans_tache']:0;
		$crd_tach_ap_trans=$get_infoEBET[0]->BUDGET_ANNUEL-$trans_tach;
		$html.="<tr>
			<td>Crédit annuel de la tâche après transfert : </td>
			<td><strong>".$crd_tach_ap_trans."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Crédit trimestriel voté pour la tâche	: </td>
			<td><strong>".$creditVote."</strong></td>
		</tr>";

		$trans_trim_tach=!empty($trans_tache_trim['trans_tache'])?$trans_tache_trim['trans_tache']:0;
		$tach_rest_trim=floatval($creditVote)-floatval($trans_trim_tach);
		$html.="<tr>
			<td>Crédit&nbsp;trimestriel&nbsp;de&nbsp;la&nbsp;tâche&nbsp;après&nbsp;transfert&nbsp;: </td>
			<td><strong>".$tach_rest_trim."</strong></td>
		</tr>";

		if(!empty($get_info['ENG_BUDGETAIRE_DEVISE']))
		{
			$html.="<tr>
				<td>Montant engagé en devise      : </td>
				<td><strong>".$get_info['ENG_BUDGETAIRE_DEVISE']." ".$get_info['DESC_DEVISE_TYPE']." au taux de :".$get_info['COUR_DEVISE']."</strong></td>
			</tr>";
		}
		$html.="<tr>
			<td>Montant engagé en BIF : </td>
			<td><strong>".$get_info['ENG_BUDGETAIRE']."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Bon d’Engagement n° : </td>
			<td><strong>".$get_info['NUMERO_BON_ENGAGEMENT']."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Date d’engagement : </td>
			<td><strong>".date('Y-m-d',strtotime($get_info['DATE_DEMANDE']))."</strong></td>
		</tr>";

		if($provenance==2)
		{
			$html.="<tr>
				<td>Montant liquidé : </td>
				<td><strong>".$get_info['MONTANT_LIQUIDATION']."</strong></td>
			</tr>";
		}

		$crd_rest_ann_lign=floatval($vote_ligne['restant_t1'])+floatval($vote_ligne['restant_t2'])+floatval($vote_ligne['restant_t3'])+floatval($vote_ligne['restant_t4']);
		$html.="<tr>
			<td>Crédit restant sur la ligne budgétaire : </td>
			<td><strong>".$crd_rest_ann_lign."</strong></td>
		</tr>";
		$html.="<tr>
			<td>Crédit annuel restant sur la tâche	: </td>
			<td><strong>".$crd_an_rest_tach."</strong></td>
			</tr>";
		
		$reste=floatval($creditVote)-floatval($liqu_tach['MONTANT_LIQUIDATION']);

		if($provenance==1)
		{
			$html.="<tr>
			<td>Crédit trimestriel restant sur la tâche: </td>
			<td><strong>".$crd_rest_tache_trim."</strong></td>
			</tr>";
		}
		else
		{
			$html.="<tr>
			<td>Crédit trimestriel restant sur la tâche: </td>
			<td><strong>".$reste."</strong></td>
			</tr>";
		}

		$html.="</table>";

		$html.='<style>
			.table {
		  width: 100%;
		  border-collapse: collapse;
		}

		.th, .td {
		  border: 1px solid #dddddd;
		  padding: 8px;
		}

		.tr:nth-child(even) {
		  background-color: #f2f2f2;
		}</style><table>'.$info_tache.$data_tache.'<br></table>';

		if(!empty($signataire))
		{
			$html .= "<div style='width:100%; display: flex; flex-wrap: wrap;'>";

			$index = 0;

			foreach ($signataire as $key)
			{
			  $alignement = ($index % 2 == 0) ? 'left' : 'right';
			  $marginStyle = ($alignement == 'left') ? 'margin: 10px;' : '';
			  $margin_top=($index>2) ? '200px':'0px';

			  $html .= "
			  <div style='width: 40%; box-sizing: border-box; padding: 0 10px;{$marginStyle};float:{$alignement};margin-top:{$margin_top}'>
			      <div style=''>
			        <p><b>LE ".$key->DESC_POSTE_SIGNATAIRE."</b></p>
			        <p><b>".$key->NOM_PRENOM."</b></p>
			      </div>
			  </div>";

			  if ($index % 2 == 1) {
			    $html .= "<div style='width: 100%; clear: both;'></div>";
			  }

			  $index++;
			}
			$html .= "</div>";
		}
		else
		{
			if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
			{
				$html.="
					<div>
					<div>
						<div style='float:left'>
							<p><b><u>POUR ETABLISSEMENT:</u></b></p>
							<p><b>Responsable d’activités:</b></p>
							<p><b>.........................</b><br></p>
							<p><b>LE CONTROLEUR DES ENGAGEMENTS DE DEPENSES</b></p>
							<p><b>".$users['NOM'].' '.$users['PRENOM']."</b></p>
						</div>
						<div style='float:right'>
							<p><b><u>POUR APPROBATION :</u></b></p>
							<p><b>Responsable de programme</b></p>
							<p><b>.........................</b><br></p>
							<p><b>LE MINISTRE</b></p>
							<p><b>.........................</b></p>
						</div>
					</div>
				</div>";
			}
			else
			{
				$html.="
					<div>
					<div>
						<div style='float:left'>
							<p><b><u>POUR ETABLISSEMENT:</u></b></p>
							<p><b>.........................</b><br></p>
							<p><b>LE CONTROLEUR DES ENGAGEMENTS DE DEPENSES</b></p>
							<p><b>".$users['NOM'].' '.$users['PRENOM']."</b></p>
						</div>
						<div style='float:right'>
							<p><b><u>POUR APPROBATION :</u></b></p>
							<p><b>.........................</b><br></p>
							<p><b>LE RESPONSABLE </b></p>
							<p><b>.........................</b></p>
						</div>
					</div>
				</div>";
			}
		}
			        
		$html.="</div></html>";

    	// Charger le contenu HTML
		$dompdf->loadHtml($html);

    	// Définir la taille et l'orientation du papier
		$dompdf->setPaper('A4', 'portrait');

    	// Rendre le HTML en PDF
		$dompdf->render();

		$output = $dompdf->output();

		$name_file = 'LETTRE_OTB'.uniqid().'.pdf';
		$PATH_NOTE = 'uploads/double_commande_new/'.$name_file;
		file_put_contents($PATH_NOTE, $output);

		$where ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;          
		$insertIntoSupp='execution_budgetaire_tache_detail';
		$columsup="PATH_NOTE_A_LA_DCP = '".$name_file."'";
		$this->update_all_table($insertIntoSupp,$columsup,$where); 

		$pdf_base64 = base64_encode($output);
		$fichier = "<embed src=".base_url(''.$PATH_NOTE.'')." type='application/pdf' width='100%' height='600px' />";
		// $fichier = "<embed src='data:application/pdf;base64," . $pdf_base64 . "' type='application/pdf' width='100%' height='600px' />";
		$data['returns']=$fichier;
		$data['provenance']=$provenance;
		$data['message']=lang('messages_lang.eng_succ');
		return view("App\Modules\double_commande_new\Views\Visualise_Note_View",$data);
	}

	public function generate_noteOLD($value=0,$provenance=0)
	{
		$data = $this->urichk();
		$dompdf = new Dompdf();
    	// Charger la vue dans Dompdf  
    	$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$value;

    	$callpsreq = "CALL getRequete(?,?,?,?);";
    	$bindparams = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID','execution_budgetaire_titre_decaissement','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
    	$bindparams = str_replace('\\','',$bindparams);
      	$res= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
      	$EXECUTION_BUDGETAIRE_DETAIL_ID = $res['EXECUTION_BUDGETAIRE_DETAIL_ID'];

		$detail=$this->detail_new(MD5($res['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
		$get_info = $detail['get_info'];
		$get_infoEBET=$detail['get_infoEBET'];
		$EXECUTION_BUDGETAIRE_ID=$get_info['EXECUTION_BUDGETAIRE_ID'];
		$montantvote = $detail['montantvote'];
		// $creditVote = $detail['creditVote'];
		// $montant_reserve = $detail['montant_reserve'];
		// $crd_rest_tache_trim=$detail['cred_act'];
		$intro_note=$get_info['INTRODUCTION_NOTE'];

		foreach ($get_infoEBET as $key)
		{
			$crd_an_rest_tach=floatval($key->BUDGET_RESTANT_T1)+floatval($key->BUDGET_RESTANT_T2)+floatval($key->BUDGET_RESTANT_T3)+floatval($key->BUDGET_RESTANT_T4);

			//credit vote sur activite pap
			$vote_activite = "";
			if (!empty($key->PAP_ACTIVITE_ID))
			{
				$req_activ='SELECT SUM(ptba.BUDGET_ANNUEL) as budg_vote FROM ptba_tache ptba WHERE ptba.PAP_ACTIVITE_ID='.$key->PAP_ACTIVITE_ID.'';
				$req_activ = "CALL getTable('".$req_activ."');";
				$vote_activite = $this->ModelPs->getRequeteOne($req_activ);
			}

			//credit annuel transfere pour la tache
			$req_tach='SELECT SUM(trans.MONTANT_TRANSFERT) as trans_tache FROM transfert_historique_transfert trans WHERE trans.PTBA_TACHE_ID_TRANSFERT='.$key->PTBA_TACHE_ID;
			$req_tach = "CALL getTable('".$req_tach."');";
			$trans_tache = $this->ModelPs->getRequeteOne($req_tach);

			//credit trimestriel transfere pour la tache
			$req_tach_trim='SELECT SUM(trans.MONTANT_TRANSFERT) as trans_tache FROM transfert_historique_transfert trans WHERE trans.PTBA_TACHE_ID_TRANSFERT='.$key->PTBA_TACHE_ID.' AND TRIMESTRE_ID='.$get_info['TRIMESTRE_ID'];
			$req_tach_trim = "CALL getTable('".$req_tach_trim."');";
			$trans_tache_trim = $this->ModelPs->getRequeteOne($req_tach_trim);

			//montant deja liquide sur la tache
			$liqu_tach='SELECT SUM(MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION FROM execution_budgetaire_execution_tache exec_tach JOIN execution_budgetaire exec ON exec_tach.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE PTBA_TACHE_ID='.$key->PTBA_TACHE_ID.' AND TRIMESTRE_ID='.$get_info['TRIMESTRE_ID'];
			$liqu_tach = "CALL getTable('".$liqu_tach."');";
			$liqu_tach = $this->ModelPs->getRequeteOne($liqu_tach);

			$creditVote=0;
			$crd_rest_tache_trim=0;
			$reserve=0;
			$CODE_TRANCHE=$get_info['TRIMESTRE_ID'];
			if ($CODE_TRANCHE == 1)
			{
				$creditVote=floatval($key->T1);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T1);
				$reserve=floatval($key->BUDGET_UTILISE_T1);
			}
			else if ($CODE_TRANCHE == 2)
			{
				$creditVote=floatval($key->T2);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T2);
				$reserve=floatval($key->BUDGET_UTILISE_T2);

			}
			else if ($CODE_TRANCHE == 3)
			{
				$creditVote=floatval($key->T3);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T3);
				$reserve=floatval($key->BUDGET_UTILISE_T3);

			}
			else if ($CODE_TRANCHE == 4)
			{
				$creditVote=floatval($key->T4);
				$crd_rest_tache_trim=floatval($key->BUDGET_RESTANT_T4); 
				$reserve=floatval($key->BUDGET_RESTANT_T4);
			}
		}
		
    	//utilisateur
		$req_users="";
		if($provenance==1)
		{
			$req_users='SELECT user.USER_ID,NOM,PRENOM,PROFIL_DESCR FROM execution_budgetaire_tache_detail_histo histo JOIN user_users user ON user.USER_ID=histo.USER_ID JOIN user_profil prof ON prof.PROFIL_ID=user.PROFIL_ID WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'" AND ETAPE_DOUBLE_COMMANDE_ID=3';
		}
		else
		{
			$req_users='SELECT user.USER_ID,NOM,PRENOM,PROFIL_DESCR FROM execution_budgetaire_tache_detail_histo histo JOIN user_users user ON user.USER_ID=histo.USER_ID JOIN user_profil prof ON prof.PROFIL_ID=user.PROFIL_ID WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'" AND ETAPE_DOUBLE_COMMANDE_ID=11';
		}
		$req_users = "CALL getTable('".$req_users."');";
		$users = $this->ModelPs->getRequeteOne($req_users);

		//credit vote/transfere sur la ligne budgetaire
		$req='SELECT SUM(ptba.BUDGET_ANNUEL) as budg_vote,SUM(trans.MONTANT_TRANSFERT) as budg_trans,SUM(ptba.BUDGET_RESTANT_T1) AS restant_t1,SUM(ptba.BUDGET_RESTANT_T2) AS restant_t2,SUM(ptba.BUDGET_RESTANT_T3) AS restant_t3,SUM(ptba.BUDGET_RESTANT_T4) AS restant_t4 FROM ptba_tache ptba LEFT JOIN transfert_historique_transfert trans ON trans.PTBA_TACHE_ID_TRANSFERT=ptba.PTBA_TACHE_ID WHERE ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$get_infoEBET[0]->CODE_NOMENCLATURE_BUDGETAIRE_ID.'';
		$req = "CALL getTable('".$req."');";
		$vote_ligne = $this->ModelPs->getRequeteOne($req);

		$html="<html><div style='font-size:13px'>";
		$html.="
		<div style='float:left;height: 0vh;'>
		<p><b>REPUBLIQUE DU BURUNDI</b></p>
		</div>&nbsp;
		<div style='float:right;;height: 0vh'>
		<p><b>...........,le...../....../202..</b></p>
		</div>
		";
		$dompdf->set_option('chroot', ROOTPATH);
		$dompdf->set_option('base_path', base_url());

		$html.= '<div><br><br>
		<div style="">
		<img style="width: 75px;height: 5vh;" src="assets_frontend/img/logo_burundi.png"><br>
		</div>';

		$html.="<p>".$get_infoEBET[0]->DESCRIPTION_INSTITUTION."</p>";
		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<b>CABINET DU MINISTERE</b>";
		}else{
			$html.="<b>CABINET .......</b>";
		}
		$html.="<p><b>Réf: </b>".$get_info['EXECUTION_BUDGETAIRE_ID']."/".$get_info['EXECUTION_BUDGETAIRE_ID']."/".$get_info['ANNEE_DESCRIPTION']."</p>";
		
		$html.="<center><b><u>NOTE AU DIRECTEUR DE LA COMPTABILITE PUBLIQUE</u></b></center>";

		$html.="<div>".$intro_note."</div><br>";

		$html.="<table>";		
		$html.="<tr>
		<td>Exercice : </td>
		<td><strong>".$get_info['ANNEE_DESCRIPTION']."</strong></td>
		</tr>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<tr>
			<td>Code programme : </td>
			<td><strong>".$get_infoEBET[0]->CODE_PROGRAMME."</strong></td>
			</tr>";

			$html.="<tr>
			<td>Code programmatique : </td>
			<td><b>".$get_infoEBET[0]->CODES_PROGRAMMATIQUE."</b></td>
			</tr>";
		}
		else
		{
			$html.="<tr>
			<td>Code Dotation : </td>
			<td><b>".$get_infoEBET[0]->CODES_PROGRAMMATIQUE."</b></td>
			</tr>";
		}

		$html.="<tr>
			<td>Imputation : </td>
			<td><strong>".$get_infoEBET[0]->CODE_NOMENCLATURE_BUDGETAIRE."</strong></td>
		</tr><br>";

		$html.="<tr>
			<td>Intitulé de la ligne budgétaire : </td>
			<td><strong>".$get_infoEBET[0]->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE."</strong></td>
		</tr><br>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<tr>
				<td>Intitulé de l’activité PAP  : </td>
				<td><strong>".$get_infoEBET[0]->DESC_PAP_ACTIVITE."</strong></td>
			</tr><br>";
		}

		$html.="<tr>
			<td>Intitulé de la tâche	 : </td>
			<td style='word-break: break-word;'><strong>".addslashes($get_infoEBET[0]->DESC_TACHE)."</strong></td>
		</tr><br>";

		$html.="<tr>
			<td>Crédit voté sur la ligne budgétaire   : </td>
			<td><strong>".$vote_ligne['budg_vote']."</strong></td>
		</tr>";

		$credi_trans=!empty($vote_ligne['budg_trans'])?$vote_ligne['budg_trans']:0;
		$html.="<tr>
			<td>Crédit transféré sur la ligne budgétaire : </td>
			<td><strong>".$credi_trans."</strong></td>
		</tr>";
		$crd_lgn_apre_trans=floatval($vote_ligne['budg_vote'])-floatval($credi_trans);
		$html.="<tr>
			<td>Crédit de la ligne budgétaire après transfert : </td>
			<td><strong>".$crd_lgn_apre_trans."</strong></td>
		</tr>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="<tr>
				<td>Crédit voté pour l’activité PAP  : </td>
				<td><strong>".$vote_activite['budg_vote']."</strong></td>
			</tr>";
		}

		$html.="<tr>
			<td>Crédit annuel voté pour la tâche: </td>
			<td><strong>".$get_infoEBET[0]->BUDGET_ANNUEL."</strong></td>
		</tr>";		

		$trans_tach=!empty($trans_tache['trans_tache'])?$trans_tache['trans_tache']:0;
		$crd_tach_ap_trans=$get_infoEBET[0]->BUDGET_ANNUEL-$trans_tach;
		$html.="<tr>
			<td>Crédit annuel de la tâche après transfert : </td>
			<td><strong>".$crd_tach_ap_trans."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Crédit trimestriel voté pour la tâche	: </td>
			<td><strong>".$creditVote."</strong></td>
		</tr>";

		$trans_trim_tach=!empty($trans_tache_trim['trans_tache'])?$trans_tache_trim['trans_tache']:0;
		$tach_rest_trim=floatval($creditVote)-floatval($trans_trim_tach);
		$html.="<tr>
			<td>Crédit&nbsp;trimestriel&nbsp;de&nbsp;la&nbsp;tâche&nbsp;après&nbsp;transfert&nbsp;: </td>
			<td><strong>".$tach_rest_trim."</strong></td>
		</tr>";

		if(!empty($get_info['ENG_BUDGETAIRE_DEVISE']))
		{
			$html.="<tr>
				<td>Montant engagé en devise      : </td>
				<td><strong>".$get_info['ENG_BUDGETAIRE_DEVISE']." ".$get_info['DESC_DEVISE_TYPE']." au taux de :".$get_info['COUR_DEVISE']."</strong></td>
			</tr>";
		}
		$html.="<tr>
			<td>Montant engagé en BIF : </td>
			<td><strong>".$get_info['ENG_BUDGETAIRE']."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Bon d’Engagement n° : </td>
			<td><strong>".$get_info['NUMERO_BON_ENGAGEMENT']."</strong></td>
		</tr>";

		$html.="<tr>
			<td>Date d’engagement : </td>
			<td><strong>".date('Y-m-d',strtotime($get_info['DATE_DEMANDE']))."</strong></td>
		</tr>";

		if($provenance==2)
		{
			$html.="<tr>
				<td>Montant liquidé : </td>
				<td><strong>".$get_info['MONTANT_LIQUIDATION']."</strong></td>
			</tr>";
		}

		$crd_rest_ann_lign=floatval($vote_ligne['restant_t1'])+floatval($vote_ligne['restant_t2'])+floatval($vote_ligne['restant_t3'])+floatval($vote_ligne['restant_t4']);
		$html.="<tr>
			<td>Crédit restant sur la ligne budgétaire : </td>
			<td><strong>".$crd_rest_ann_lign."</strong></td>
		</tr>";
		$html.="<tr>
			<td>Crédit annuel restant sur la tâche	: </td>
			<td><strong>".$crd_an_rest_tach."</strong></td>
			</tr>";
		
		$reste=floatval($creditVote)-floatval($liqu_tach['MONTANT_LIQUIDATION']);

		if($provenance==1)
		{
			$html.="<tr>
			<td>Crédit trimestriel restant sur la tâche: </td>
			<td><strong>".$crd_rest_tache_trim."</strong></td>
			</tr>";
		}
		else
		{
			$html.="<tr>
			<td>Crédit trimestriel restant sur la tâche: </td>
			<td><strong>".$reste."</strong></td>
			</tr>";
		}

		$html.="</table>";

		if($get_infoEBET[0]->TYPE_INSTITUTION_ID==2)
		{
			$html.="
				<div>
				<div>
					<div style='float:left'>
						<p><b><u>POUR ETABLISSEMENT:</u></b></p>
						<p><b>Responsable d’activités:</b></p>
						<p><b>.........................</b><br></p>
						<p><b>LE CONTROLEUR DES ENGAGEMENTS DE DEPENSES</b></p>
						<p><b>".$users['NOM'].' '.$users['PRENOM']."</b></p>
					</div>
					<div style='float:right'>
						<p><b><u>POUR APPROBATION :</u></b></p>
						<p><b>Responsable de programme</b></p>
						<p><b>.........................</b><br></p>
						<p><b>LE MINISTRE</b></p>
						<p><b>.........................</b></p>
					</div>
				</div>
			</div>";
		}
		else
		{
			$html.="
				<div>
				<div>
					<div style='float:left'>
						<p><b><u>POUR ETABLISSEMENT:</u></b></p>
						<p><b>.........................</b><br></p>
						<p><b>LE CONTROLEUR DES ENGAGEMENTS DE DEPENSES</b></p>
						<p><b>".$users['NOM'].' '.$users['PRENOM']."</b></p>
					</div>
					<div style='float:right'>
						<p><b><u>POUR APPROBATION :</u></b></p>
						<p><b>.........................</b><br></p>
						<p><b>LE RESPONSABLE </b></p>
						<p><b>.........................</b></p>
					</div>
				</div>
			</div>";
		}
			        
		$html.="</div></html>";

    	// Charger le contenu HTML
		$dompdf->loadHtml($html);

    	// Définir la taille et l'orientation du papier
		$dompdf->setPaper('A4', 'portrait');

    	// Rendre le HTML en PDF
		$dompdf->render();

		$output = $dompdf->output();

		$name_file = 'LETTRE_OTB'.uniqid().'.pdf';
		$PATH_NOTE = 'uploads/double_commande_new/'.$name_file;
		file_put_contents($PATH_NOTE, $output);

		$where ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;          
		$insertIntoSupp='execution_budgetaire_tache_detail';
		$columsup="PATH_NOTE_A_LA_DCP = '".$name_file."'";
		$this->update_all_table($insertIntoSupp,$columsup,$where); 

		$pdf_base64 = base64_encode($output);
		$fichier = "<embed src=".base_url(''.$PATH_NOTE.'')." type='application/pdf' width='100%' height='600px' />";
		$data['returns']=$fichier;
		$data['provenance']=$provenance;
		$data['message']=lang('messages_lang.eng_succ');
		return view("App\Modules\double_commande_new\Views\Visualise_Note_View",$data);
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	/* update table */
	function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}

	private function get_precision($value=0)
	{
		$string = strval($value);
		$number=explode('.',$string)[1] ?? '';
		$precision='';
		for($i=1;$i<=strlen($number);$i++)
		{
			$precision=$i;
		}
		if(!empty($precision)) 
		{
			return $precision;
		}
		else
		{
			return 0;
		}    
	}
}
