<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Liquidation
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 25 oct 2023
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
use Dompdf\Dompdf;

class Liquidation extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		define("DOMPDF_ENABLE_REMOTE", true);
		$this->validation = \Config\Services::validation();
		$this->generate_note= new \App\Modules\double_commande_new\Controllers\Generate_Note();
	}
	//Les nouveaux motifs
	function save_newMotif()
	{
		$session  = \Config\Services::session();

		$DESCRIPTION_MOTIF = $this->request->getPost('DESCRIPTION_MOTIF');
		$MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
		$MOUVEMENT_DEPENSE_ID=3;

		$table="budgetaire_type_analyse_motif";
		$columsinsert = "MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE";
		$datacolumsinsert = "{$MOUVEMENT_DEPENSE_ID},'{$DESCRIPTION_MOTIF}',{$MARCHE_PUBLIQUE}";    
		$this->save_all_table($table,$columsinsert,$datacolumsinsert);

		$callpsreq = "CALL getRequete(?,?,?,?);";
      //récuperer les motifs
		$bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','MOUVEMENT_DEPENSE_ID=3 AND IS_MARCHE='.$MARCHE_PUBLIQUE,'DESC_TYPE_ANALYSE_MOTIF ASC');
		$motif = $this->ModelPs->getRequete($callpsreq, $bind_motif);

		$html='<option value="-1">'.lang('messages_lang.selection_autre').'</option>';

		if(!empty($motif))
		{
			foreach($motif as $key)
			{ 
				$html.= "<option value='".$key->TYPE_ANALYSE_MOTIF_ID."'>".$key->DESC_TYPE_ANALYSE_MOTIF."</option>";
			}
		}
		$output = array('status' => TRUE ,'motifs' => $html);
		return $this->response->setJSON($output);
	}

	public function index($value='')
	{
		$data=$this->urichk();
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		//get data institution
		$institution = $this->getBindParms('DESCRIPTION_INSTITUTION,INSTITUTION_ID', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
		$data['institution'] = $this->ModelPs->getRequete($psgetrequete, $institution);
		return view('App\Modules\double_commande_new\Views\Liquidation_List_Views',$data);   
	}

	public function uploadFile($fieldName=NULL, $folder=NULL, $prefix = NULL): string
	{
		$prefix = ($prefix === '') ? uniqid() : $prefix;
		$path = '';

		$file = $this->request->getFile($fieldName);

		if ($file->isValid() && !$file->hasMoved()) {
			$newName = uniqid(). '' . date('ymdhis') . '.' . $file->getExtension();
			$file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
			$path = 'uploads/' . $folder . '/' . $newName;
		}
		return $path;
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	//fonction get pour recuperer les données idmd5
	public function getOne($idmd5='',$dist='')
	{
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$inforacc = 'SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$idmd5.'"';
		$inforacc = "CALL `getTable`('" . $inforacc . "');";
		$resultatracc= $this->ModelPs->getRequeteOne($inforacc);

		if(empty($resultatracc))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$id=$resultatracc['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'];
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		if($id=='' || $dist=='')
		{
			return redirect('Login_Ptba/do_logout');
		}
		
		$infoAffiche  = 'SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.MARCHE_PUBLIQUE, exec.COMMENTAIRE,exec.ENG_JURIDIQUE_DEVISE AS MONTANT_RACCROCHE_JURIDIQUE_DEVISE,exec.ENG_JURIDIQUE AS MONTANT_RACCROCHE_JURIDIQUE,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,det.MONTANT_LIQUIDATION_DEVISE AS MONTANT_RACCROCHE_LIQUIDATION_DEVISE,det.MONTANT_LIQUIDATION AS MONTANT_RACCROCHE_LIQUIDATION,ebtd.ETAPE_DOUBLE_COMMANDE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID, det.COUR_DEVISE, exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE, det.TAUX_TVA_ID, supp.DATE_FIN_CONTRAT, supp.NBRE_JR_CONTRAT, supp.DATE_DEBUT_CONTRAT, supp.MONTANT_AMENDE_PAYER, supp.NBRE_JR_RETARD, det.DATE_LIVRAISON_CONTRAT,BUDGETAIRE_TYPE_DOCUMENT_ID,exec.TRIMESTRE_ID,dev.DESC_DEVISE_TYPE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_tache_info_suppl supp ON supp.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id;
		$infoAffiche = "CALL `getTable`('" . $infoAffiche . "');";
		$data['info'] = $this->ModelPs->getRequeteOne($infoAffiche);
		$data['cour_devise']=number_format($data['info']['COUR_DEVISE'],$this->get_precision($data['info']['COUR_DEVISE']),'.',' ');

		//get les montant par tache
		$requeteTaches="SELECT ebet.MONTANT_ENG_JURIDIQUE TACHE_JURIDIQUE, 
							   ebet.MONTANT_ENG_JURIDIQUE_DEVISE TACHE_JURIDIQUE_DEVISE,
							   ebet.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID EBET_ID,
							   tache.DESC_TACHE
						 FROM execution_budgetaire_execution_tache ebet 
						 JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID
						 WHERE ebet.EXECUTION_BUDGETAIRE_ID=".$data['info']['EXECUTION_BUDGETAIRE_ID'];
		$infoTaches = "CALL `getTable`('" . $requeteTaches . "');";
		$data['infoTaches'] = $this->ModelPs->getRequete($infoTaches);
		$data['infoTachesJson'] = json_encode($data['infoTaches']);

		if($data['info']['MARCHE_PUBLIQUE']==1)
		{
			$getMontantenleve = 'SELECT BUDGET_ENLEVE_T1,BUDGET_ENLEVE_T2,BUDGET_ENLEVE_T3,BUDGET_ENLEVE_T4 FROM execution_budgetaire_histo_budget_marche_public marche WHERE marche.EXECUTION_BUDGETAIRE_ID='.$data['info']['EXECUTION_BUDGETAIRE_ID'].' ORDER BY EXECUTION_BUDGETAIRE_HISTO_BUDGET_MARCHE_PUBLIC_ID DESC';
	    	$getMontantenleve = "CALL `getTable`('".$getMontantenleve."');";
	    	$MontantEnleve= $this->ModelPs->getRequeteOne($getMontantenleve);

	    	$mont_enleve='';
	    	if($data['info']['TRIMESTRE_ID']==1)
	    	{
	    		$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T1'];
	    	}
	    	elseif($data['info']['TRIMESTRE_ID']==2)
	    	{
	    		$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T2'];
	    	}
	    	elseif($data['info']['TRIMESTRE_ID']==3)
	    	{
	    		$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T3'];
	    	}
	    	elseif($data['info']['TRIMESTRE_ID']==4)
	    	{
	    		$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T4'];
	    	}
	    	$data['mont_enleve']=$mont_enleve;

	    	if($data['info']['TAUX_ECHANGE_ID']!=1)
	    	{
	    		$data['mont_enleve_devise']=$mont_enleve/$data['info']['COUR_DEVISE'];
	    	}
		}
		
		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
		$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

		if (!empty($getProfil))
		{
			foreach ($getProfil as $value)
			{
				if ($prof_id == $value->PROFIL_ID || $prof_id==1)
				{
					$verification  = 'SELECT BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,MOUVEMENT_DEPENSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID = 3 AND IS_MARCHE='.$data['info']['MARCHE_PUBLIQUE'].' ORDER BY DESC_TYPE_ANALYSE ASC';
					$verification = "CALL `getTable`('" . $verification . "');";
					$data['get_verification']= $this->ModelPs->getRequete($verification);
					$data['nbrverification'] = count($data['get_verification']);

					$data['TYPE_MONTANT_ID'] =$data['info']['TAUX_ECHANGE_ID'];
					$data['ETAPE_ID'] =$data['info']['ETAPE_DOUBLE_COMMANDE_ID'];

		            //get description etape
					$etape_descr  = 'SELECT DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande WHERE 1 AND ETAPE_DOUBLE_COMMANDE_ID='.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'].'';
					$etape_descr = "CALL `getTable`('" . $etape_descr . "');";
					$data['etape_descr']= $this->ModelPs->getRequeteOne($etape_descr);

		            //tva
					$taux_tva  = 'SELECT TAUX_TVA_ID ,DESCRIPTION_TAUX_TVA FROM taux_tva WHERE 1 ORDER BY DESCRIPTION_TAUX_TVA ASC';
					$taux_tvaRqt = "CALL `getTable`('" . $taux_tva . "');";
					$data['get_taux_tva']= $this->ModelPs->getRequete($taux_tvaRqt);

		      //type_liquidation
					$type_liquidation = 'SELECT LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,DESCRIPTION_LIQUIDATION FROM liquidation_type WHERE 1 ORDER BY DESCRIPTION_LIQUIDATION ASC';
					if($data['info']['MARCHE_PUBLIQUE']==1)
					{
						$type_liquidation = 'SELECT LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,DESCRIPTION_LIQUIDATION FROM liquidation_type WHERE LIQUIDATION_TYPE_ID <> 2 ORDER BY DESCRIPTION_LIQUIDATION ASC';
					}
					$type_liquidationRqt = "CALL `getTable`('" . $type_liquidation . "');";
					$data['get_type_liquidation']= $this->ModelPs->getRequete($type_liquidationRqt);
    	            ##  Fin envoie de donées pour tout le view de ce 3 action du process ###############

    	            //Le min de la date de réception
					$bind_date_histo  = 'SELECT DATE_TRANSMISSION,DATE_INSERTION FROM execution_budgetaire_tache_detail_histo  WHERE 1 AND EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].' ORDER BY DATE_INSERTION DESC';
					$bind_date_histoRqt = "CALL `getTable`('" . $bind_date_histo . "');";
					$data['date_trans']= $this->ModelPs->getRequeteOne($bind_date_histoRqt);
		            ######################################

    	            // etape initiale de la laquidation
					if($dist==1)
					{						
						$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
						if($gdc!=1)
						{
							return redirect('Login_Ptba/homepage'); 
						}

     		            //liquidation
						$detail=$this->detail_new(md5($data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
						$data['get_info']=$detail['get_info'];
			            $data['montantvote']=$detail['montantvote'];
			            $data['get_infoEBET']=$detail['get_infoEBET'];

						return view('App\Modules\double_commande_new\Views\Liquidation_Add_Views',$data);
					}
		            //si une fois le parametré manque on se redirige sur la liste
					else
					{
						return redirect('Login_Ptba/do_logout');
					}

				}  
			}
			return redirect('Login_Ptba/homepage'); 
		}
		else
		{
			return redirect('Login_Ptba/homepage');
		}
	}

	//fonction get pour confirmation des données idmd5 (id_detail) 
	public function getOne_conf($idmd5='',$dist='')
	{
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$info = 'SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$idmd5.'"';
		$infoDetail = "CALL `getTable`('" . $info . "');";
		$resultatdetail= $this->ModelPs->getRequeteOne($infoDetail);

		if(empty($resultatdetail))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$id=$resultatdetail['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'];
		
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		if($id=='' || $dist=='')
		{
			return redirect('Login_Ptba/do_logout');
		}
		
		$data['idmd5']=$idmd5;
		$infoAffiche  = 'SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.MARCHE_PUBLIQUE, exec.COMMENTAIRE,exec.ENG_JURIDIQUE_DEVISE AS MONTANT_RACCROCHE_JURIDIQUE_DEVISE,exec.ENG_JURIDIQUE AS MONTANT_RACCROCHE_JURIDIQUE,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,ebtd.ETAPE_DOUBLE_COMMANDE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID, det.COUR_DEVISE,det.MONTANT_LIQUIDATION_DEVISE AS MONTANT_RACCROCHE_LIQUIDATION_DEVISE,det.MONTANT_LIQUIDATION AS MONTANT_RACCROCHE_LIQUIDATION, exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE, det.TAUX_TVA_ID, supp.DATE_FIN_CONTRAT, supp.NBRE_JR_CONTRAT, supp.DATE_DEBUT_CONTRAT, supp.MONTANT_AMENDE_PAYER, supp.NBRE_JR_RETARD, det.DATE_LIVRAISON_CONTRAT,det.COMMENTAIRE,BUDGETAIRE_TYPE_DOCUMENT_ID,exec.TRIMESTRE_ID,exec.LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_tache_info_suppl supp ON supp.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id;
		
		$infoAffiche = 'CALL `getTable`("' . $infoAffiche . '");';
		$data['info'] = $this->ModelPs->getRequeteOne($infoAffiche);

		$id_racc = md5($data['info']['EXECUTION_BUDGETAIRE_ID']);
		###########################################

		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
		$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

		if (!empty($getProfil))
		{
			foreach ($getProfil as $value)
			{
				if ($prof_id == $value->PROFIL_ID || $prof_id==1)
				{
					$verification  = 'SELECT BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,MOUVEMENT_DEPENSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID=3 AND IS_MARCHE='.$data['info']['MARCHE_PUBLIQUE'].' ORDER BY DESC_TYPE_ANALYSE ASC';
					$verification = "CALL `getTable`('" . $verification . "');";
					$data['get_verification']= $this->ModelPs->getRequete($verification);
					$data['nbrverification'] = count($data['get_verification']);

					$data['TYPE_MONTANT_ID'] =$data['info']['TAUX_ECHANGE_ID'];
					$data['ETAPE_ID'] =$data['info']['ETAPE_DOUBLE_COMMANDE_ID'];

		            //get description etape
					$etape_descr  = 'SELECT DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande WHERE 1 AND ETAPE_DOUBLE_COMMANDE_ID='.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'].'';
					$etape_descr = "CALL `getTable`('" . $etape_descr . "');";
					$data['etape_descr']= $this->ModelPs->getRequeteOne($etape_descr);

		            //tva
					$taux_tva  = 'SELECT TAUX_TVA_ID ,DESCRIPTION_TAUX_TVA FROM taux_tva WHERE 1 ORDER BY DESCRIPTION_TAUX_TVA ASC';
					$taux_tvaRqt = "CALL `getTable`('" . $taux_tva . "');";
					$data['get_taux_tva']= $this->ModelPs->getRequete($taux_tvaRqt);

		            //type_liquidation
					$type_liquidation  = 'SELECT LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,DESCRIPTION_LIQUIDATION FROM liquidation_type WHERE  LIQUIDATION_TYPE_ID='.$data['info']['ID_TYPE_LIQUIDATION'].' ORDER BY DESCRIPTION_LIQUIDATION ASC';
					$type_liquidationRqt = "CALL `getTable`('" . $type_liquidation . "');";
					$data['get_type_liquidation']= $this->ModelPs->getRequeteOne($type_liquidationRqt);
    	            ###  Fin envoie de donées pour tout le view de ce 3 action du process ###############
                   	//Le min de la date de réception
					$bind_date_histo  = 'SELECT DATE_TRANSMISSION,DATE_INSERTION FROM execution_budgetaire_tache_detail_histo  WHERE 1 AND EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].' ORDER BY DATE_INSERTION DESC';
					$bind_date_histoRqt = "CALL `getTable`('" . $bind_date_histo . "');";
					$data['date_trans']= $this->ModelPs->getRequeteOne($bind_date_histoRqt);

		            // etape de confirmation
					if ($dist==2)
					{
						$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
						if($ced!=1)
						{
							return redirect('Login_Ptba/homepage'); 
						}
			        		##### Debut envoie de données pour faire la validation ou le rejet ####################
			            //opeartion validation
						$type_operation_validation  = 'SELECT ID_OPERATION,DESCRIPTION FROM budgetaire_type_operation_validation WHERE 1';
						$type_operation_validationRqt = "CALL `getTable`('" . $type_operation_validation . "');";
						$data['get_type_operation_validation']= $this->ModelPs->getRequete($type_operation_validationRqt);

			            //get type_analyse_motif
						$type_analyse_motif  = 'SELECT TYPE_ANALYSE_MOTIF_ID, DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif WHERE MOUVEMENT_DEPENSE_ID=3 AND IS_MARCHE ='.$data['info']['MARCHE_PUBLIQUE'].'';
						$type_analyse_motifRqt = "CALL `getTable`('" . $type_analyse_motif . "');";
						$data['get_type_analyse_motif']= $this->ModelPs->getRequete($type_analyse_motifRqt);

			      //get etape_retour_correction
			      $etape_retour ="";

			      $getliqui="SELECT COUNT(EXECUTION_BUDGETAIRE_DETAIL_ID) AS nbr FROM execution_budgetaire_tache_detail det WHERE det.EXECUTION_BUDGETAIRE_ID =".$data['info']['EXECUTION_BUDGETAIRE_ID'];
			      $getliqui = "CALL `getTable`('".$getliqui."');";
						$getliqui= $this->ModelPs->getRequeteOne($getliqui);
			      if($data['info']['ID_TYPE_LIQUIDATION']==1 && $getliqui['nbr']>1)
			      {
							$etape_retour = 'SELECT ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR FROM budgetaire_etape_retour_correction WHERE ETAPE_RETOUR_CORRECTION_ID=1';										      	
			      }
			      else
			      {
			      	$etape_retour = 'SELECT ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR FROM budgetaire_etape_retour_correction WHERE ETAPE_RETOUR_CORRECTION_ID!=4';
			      }      
						
						$etape_retourRqt = "CALL `getTable`('" . $etape_retour . "');";
						$data['get_etape_retour']= $this->ModelPs->getRequete($etape_retourRqt);

						$detail=$this->detail_new(md5($data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
						$data['get_info']=$detail['get_info'];
			            $data['montantvote']=$detail['montantvote'];
			            $data['get_infoEBET']=$detail['get_infoEBET'];

						return view('App\Modules\double_commande_new\Views\Confirmation_Liquidation_Add_Views',$data);	
					}
					else
					{
						return redirect('Login_Ptba/do_logout');
					}
				}  
			}
			return redirect('Login_Ptba/homepage'); 
		}
		else
		{
			return redirect('Login_Ptba/homepage');
		}
	}

	function generer_doc_liquidation($EXECUTION_BUDGETAIRE_DETAIL_ID)
	{
		$dompdf = new Dompdf();
      	// Charger la vue dans Dompdf
		$detail=$this->detail_new($EXECUTION_BUDGETAIRE_DETAIL_ID);
		$get_info = $detail['get_info'];
		$idmd5=md5($get_info['EXECUTION_BUDGETAIRE_ID']);
		$EXECUTION_BUDGETAIRE_ID=$get_info['EXECUTION_BUDGETAIRE_ID'];
		$montantvote = $detail['montantvote'];
		$creditVote = $detail['creditVote'];
		$montant_reserve = $detail['montant_reserve'];
    	//récuperer les fournisseurs/acquéreurs
		$prestataire  = 'SELECT NOM_PRESTATAIRE,PRENOM_PRESTATAIRE,DESC_TYPE_BENEFICIAIRE FROM prestataire JOIN execution_budgetaire_tache_info_suppl ON execution_budgetaire_tache_info_suppl.PRESTATAIRE_ID=prestataire.PRESTATAIRE_ID JOIN type_beneficiaire ON type_beneficiaire.TYPE_BENEFICIAIRE_ID=prestataire.TYPE_BENEFICIAIRE_ID WHERE md5(EXECUTION_BUDGETAIRE_ID)="'.$idmd5.'"';
		$prestataire = "CALL getTable('" . $prestataire . "');";
		$prestataire = $this->ModelPs->getRequeteOne($prestataire);
    	//type_liquidation
		$type_liquidation='SELECT LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,DESCRIPTION_LIQUIDATION FROM liquidation_type WHERE LIQUIDATION_TYPE_ID ='.$get_info['LIQUIDATION_TYPE_ID'].'';
		$type_liquidationRqt = "CALL getTable('" . $type_liquidation . "');";
		$get_type_liquidation = $this->ModelPs->getRequeteOne($type_liquidationRqt);

    	//utilisateur
		$req_users='SELECT user.USER_ID,NOM,PRENOM,PROFIL_DESCR FROM execution_budgetaire_tache_detail_histo histo JOIN user_users user ON user.USER_ID=histo.USER_ID JOIN user_profil prof ON prof.PROFIL_ID=user.PROFIL_ID WHERE md5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$EXECUTION_BUDGETAIRE_DETAIL_ID.'" AND ETAPE_DOUBLE_COMMANDE_ID=11';
		$req_users = "CALL getTable('".$req_users."');";
		$users = $this->ModelPs->getRequeteOne($req_users);

		$html="<html>";
		$html.="<center><b><u>".lang('messages_lang.label_piece_justificative')."</u></b></center><br><br>";

		if(!empty($get_info['CODE_NOMENCLATURE_BUDGETAIRE']))
		{
			$html.="<p>".lang('messages_lang.labelle_ligne_budgtaire')." : <strong>".$get_info['CODE_NOMENCLATURE_BUDGETAIRE']." - ".$get_info['LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE']."</strong></p>";
		}

		if(!empty($get_info['CODE_INSTITUTION']))
		{
			$html.="<p>".lang('messages_lang.labelle_institution')." : <strong>".$get_info['CODE_INSTITUTION'].'&nbsp;&nbsp;'.$get_info['DESCRIPTION_INSTITUTION']."</strong></p>";
		}

		if(!empty($get_info['DESCRIPTION_SOUS_TUTEL']))
		{
			$html.='<p>'.lang('messages_lang.table_st').' : <strong>'.$get_info['DESCRIPTION_SOUS_TUTEL'].'</strong></p>';
		}

		if(!empty($get_info['INTITULE_PROGRAMME']))
		{
			$html.='<p>'.lang('messages_lang.table_Programme').' : <strong>'.$get_info['INTITULE_PROGRAMME'].'</strong></p>';
		}

		if(!empty($get_info['LIBELLE_ACTION']))
		{
			$html.='<p>'.lang('messages_lang.table_Action').' : <strong>'.$get_info['LIBELLE_ACTION'].'</strong></p>';
		}

		if(!empty($get_info['DESC_TACHE']))
		{
			$html.='<p>'.lang('messages_lang.label_taches').' : <strong>'.$get_info['DESC_TACHE'].'</strong></p>';
		}

		if (!empty($get_info['DESC_PAP_ACTIVITE']))
		{
			$html.='<p>'.lang('messages_lang.labelle_activites').' : <strong>'.$get_info['DESC_PAP_ACTIVITE'].'</strong></p>';
		}

		if ($get_info['MARCHE_PUBLIQUE']==1)
		{
			$html.='<p>'.lang('messages_lang.labelle_marche_publique').' : <strong>'.lang('messages_lang.label_oui').'</strong></p>';
		}
		else
		{
			$html.='<p>'.lang('messages_lang.labelle_marche_publique').' : <strong>'.lang('messages_lang.label_non').'</strong></p>';
		}

		if(!empty($creditVote))
		{
			$html.='<p>'.lang('messages_lang.labelle_credit_vote_activite').' : <strong>'.number_format($creditVote,'0',',',' ').'</strong></p>';
		}

		if(!empty($get_info['ENG_BUDGETAIRE']))
		{
			$html.='<p>'.lang('messages_lang.labelle_montant_engagement_budgetaire').' : <strong>'.number_format($get_info['ENG_BUDGETAIRE'],'0',',',' ').'</strong></p>';
		}

		if(!empty($get_info['NUMERO_BON_ENGAGEMENT']))
		{
			$html.="<p>".lang('messages_lang.table_num_bon')." : <strong>".$get_info['NUMERO_BON_ENGAGEMENT']."</strong></p>";
		}

		if(!empty($get_info['COMMENTAIRE']))
		{
			$html.='<p>'.lang('messages_lang.table_objet').' : <strong>'.$get_info['COMMENTAIRE'].'</strong></p>';
		}

		if(!empty($montantvote))
		{
			$html.='<p>'.lang('messages_lang.labelle_credit_vote_ligne_budgetaire').' : <strong>'.number_format($montantvote,'0',',',' ').'</strong></p>';
		}

		if(!empty($montant_reserve))
		{
			$html.='<p>'.lang('messages_lang.labelle_credit_reserve').' : <strong>'.number_format($montant_reserve,'0',',',' ').'</strong></p>';
		}

		if(!empty($get_info['DATE_DEMANDE']))
		{
			$html.='<p>'.lang('messages_lang.table_date_engaget').' : <strong>'.$retVal = (!empty($get_info['DATE_DEMANDE'])) ? date('d/m/Y',strtotime($get_info['DATE_DEMANDE'])) : 'N/A'.'</strong></p>';
		}

		if(!empty($prestataire))
		{
			$html.="<p>".lang('messages_lang.label_type_benef')." : <strong>".$prestataire['DESC_TYPE_BENEFICIAIRE']."</strong></p>";
			$html.="<p>Prestataire : <strong>".$prestataire['NOM_PRESTATAIRE']." ".$prestataire['PRENOM_PRESTATAIRE']."</strong></p>";
		}

		if(!empty($get_info['ENG_JURIDIQUE']))
		{
			$html.='<p>'.lang('messages_lang.label_mont_juridique').' : <strong>'.number_format($get_info['ENG_JURIDIQUE'],'0',',',' ').'</strong></p>';
		}

		if(!empty($get_info['DATE_ENG_JURIDIQUE']))
		{
			$html.='<p>'.lang('messages_lang.label_date_juridique').' : <strong>'.date('d/m/Y',strtotime($get_info['DATE_ENG_JURIDIQUE'])).'</strong></p>';
		}

		if(!empty($get_info['DATE_DEBUT_CONTRAT']))
		{
			$html.='<p>'.lang('messages_lang.label_date_debut_contrat').' : <strong>'.date('d/m/Y',strtotime($get_info['DATE_DEBUT_CONTRAT'])).'</strong></p>';
		}

		if(!empty($get_info['DATE_FIN_CONTRAT']))
		{
			$html.='<p>'.lang('messages_lang.label_date_fin_contrat').' : <strong>'.date('d/m/Y',strtotime($get_info['DATE_FIN_CONTRAT'])).'</strong></p>';
		}

		if(!empty($get_type_liquidation))
		{
			$html.='<p>'.lang('messages_lang.label_type_liquidation').' : <strong>'.$get_type_liquidation['DESCRIPTION_LIQUIDATION'].'</strong></p>';
		}

		if(!empty($get_info['TITRE_CREANCE']))
		{
			$html.='<p>'.lang('messages_lang.table_titre_c').' : <strong>'.$get_info['TITRE_CREANCE'].'</strong></p>';
		}

		if(!empty($get_info['DATE_CREANCE']))
		{
			$html.='<p>'.lang('messages_lang.label_date_titre_creance').' : <strong>'.date('d-m-Y',strtotime($get_info['DATE_CREANCE'])).'</strong></p>';
		}

		if ($get_info['DEVISE_TYPE_ID']==1)
		{
			if(!empty($get_info['MONTANT_CREANCE']))
			{
				$html.='<p>'.lang('messages_lang.label_montant_titre_creance').' : <strong>'.number_format($get_info['MONTANT_CREANCE'],'0',',',' ').'</strong></p>';
			}
		}
		else
		{
			if(!empty($get_info['MONTANT_LIQUIDATION_DEVISE']))
			{
				$html.='<p>'.lang('messages_lang.label_montant_devise').' : <strong>'.number_format($get_info['MONTANT_LIQUIDATION_DEVISE'],'0',',',' ').'</strong></p>';
			}

			if(!empty($get_info['COUR_DEVISE']))
			{
				$html.='<p>'.lang('messages_lang.label_cours_devise').' : <strong>'.number_format($get_info['COUR_DEVISE'],'0',',',' ').'</strong></p>';
			}

			if(!empty($get_info['LIQUIDATION']))
			{
				$html.='<p>'.lang('messages_lang.label_montant_titre_creance').' : <strong>'.number_format($get_info['LIQUIDATION'],'0',',',' ').'</strong></p>';
			}

    		// if(!empty($get_info['DATE_DEVISE_LIQUIDATION']))
    		// {
    		// 	$html.='<p>'.lang('messages_lang.label_date_cours_devise').' : <strong>'.date('d-m-Y',strtotime($get_info['DATE_DEVISE_LIQUIDATION'])).'</strong></p>';
    		// }
		}

		if(!empty($get_info['EXONERATION']==1))
		{
			$html.='<p>'.lang('messages_lang.table_exo').' : <strong>Oui</strong></p>';
		}
		else
		{
			$html.='<p>'.lang('messages_lang.table_exo').' : <strong>Non</strong></p>';
		}

		if(!empty($get_info['DESCRIPTION_TAUX_TVA']))
		{
			$html.='<p>'.lang('messages_lang.label_taux_tva').' : <strong>'.$get_info['DESCRIPTION_TAUX_TVA'].'</strong></p>';
		}

		if(!empty($get_info['DATE_LIVRAISON_CONTRAT']))
		{
			$html.='<p>'.lang('messages_lang.label_date_livraison').' : <strong>'.date('d-m-Y',strtotime($get_info['DATE_LIVRAISON_CONTRAT'])).'</strong></p>';
		}

		$html.='<br><br><p></strong>'.$users['PROFIL_DESCR'].' : <strong>'.$users['NOM'].' '.$users['PRENOM'].'</strong></p>';
		$html.="</html>";

			//create the folder if it does not already exists
		$lien_sauvegarder =  FCPATH.'uploads/double_commande/';
		if(!is_dir($lien_sauvegarder))
		{
			mkdir($lien_sauvegarder ,0777 ,TRUE);
		}

      	// Charger le contenu HTML
		$dompdf->loadHtml($html);
    	// Définir la taille et l'orientation du papier
		$dompdf->setPaper('A4', 'portrait');

    	// Rendre le HTML en PDF
		$dompdf->render();
    	// unlink()
		$name_file = 'PIECEJUSTIFICATIVE'.uniqid().'.pdf';
			// $fichier='uploads/double_commande/PIECEJUSTIFICATIVE'.uniqid();
		$PATH_PIECE_JUSTIFICATIVE = 'uploads/double_commande/'.$name_file;

			// update dans la table info supp pour ajouter le doc generer
		$table = 'execution_budgetaire_tache_info_suppl';
		$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
		$data='PATH_PIECE_JUSTIFICATIVE="'.$PATH_PIECE_JUSTIFICATIVE.'"';
		$this->update_all_table($table,$data,$where);
		$where_detail='EXECUTION_BUDGETAIRE_DETAIL_ID='.$get_info['EXECUTION_BUDGETAIRE_DETAIL_ID'];
		$this->update_all_table('execution_budgetaire_tache_detail',$data,$where_detail);
		//envoie de données

		$output = $dompdf->output();
		file_put_contents($PATH_PIECE_JUSTIFICATIVE, $output);
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="liquidationpiecejustificatif.pdf"');
		echo $dompdf->output();
	}

	//fonction get pour recuperer les données idmd5
	public function getOne_partiel($idmd5='',$dist='')
	{
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		// $inforacc = 'SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$idmd5.'"';
		$inforacc = 'SELECT EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire WHERE md5(EXECUTION_BUDGETAIRE_ID)="'.$idmd5.'"';
		$inforacc = "CALL `getTable`('" . $inforacc . "');";
		$resultatracc= $this->ModelPs->getRequeteOne($inforacc);
		if(empty($resultatracc))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$id=$resultatracc['EXECUTION_BUDGETAIRE_ID'];
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		if($id=='' || $dist=='')
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}    
		
		$infoAffiche  = 'SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.MARCHE_PUBLIQUE, exec.COMMENTAIRE,exec.ENG_JURIDIQUE_DEVISE AS MONTANT_RACCROCHE_JURIDIQUE_DEVISE,exec.ENG_JURIDIQUE AS MONTANT_RACCROCHE_JURIDIQUE,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,exec.LIQUIDATION AS MONTANT_RACCROCHE_LIQUIDATION, exec.LIQUIDATION_DEVISE AS MONTANT_RACCROCHE_LIQUIDATION_DEVISE,ebtd.ETAPE_DOUBLE_COMMANDE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID, det.COUR_DEVISE, exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE, det.TAUX_TVA_ID, supp.DATE_FIN_CONTRAT, supp.NBRE_JR_CONTRAT, supp.DATE_DEBUT_CONTRAT, supp.MONTANT_AMENDE_PAYER, supp.NBRE_JR_RETARD, det.DATE_LIVRAISON_CONTRAT,BUDGETAIRE_TYPE_DOCUMENT_ID,exec.TRIMESTRE_ID,dev.DESC_DEVISE_TYPE,dev_histo.TAUX FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN devise_type_hist dev_histo ON dev_histo.DEVISE_TYPE_HISTO_ID = exec.DEVISE_TYPE_HISTO_ENG_ID JOIN execution_budgetaire_tache_info_suppl supp ON supp.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_ID='.$id;
		$infoAffiche = "CALL `getTable`('" . $infoAffiche . "');";
		$data['info'] = $this->ModelPs->getRequeteOne($infoAffiche);

		//get les montant par tache
		$requeteTaches="SELECT ebet.MONTANT_ENG_JURIDIQUE TACHE_JURIDIQUE, 
							   ebet.MONTANT_ENG_JURIDIQUE_DEVISE TACHE_JURIDIQUE_DEVISE,
							   ebet.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID EBET_ID,
							   ebet.MONTANT_LIQUIDATION TACHE_LIQUIDATION,
							   ebet.MONTANT_LIQUIDATION_DEVISE TACHE_LIQUIDATION_DEVISE,
							   tache.DESC_TACHE
						 FROM execution_budgetaire_execution_tache ebet 
						 JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID
						 WHERE ebet.EXECUTION_BUDGETAIRE_ID=".$data['info']['EXECUTION_BUDGETAIRE_ID'];
		$infoTaches = "CALL `getTable`('" . $requeteTaches . "');";
		$data['infoTaches'] = $this->ModelPs->getRequete($infoTaches);
		$data['infoTachesJson'] = json_encode($data['infoTaches']);
		##############################################################

		if($data['info']['MARCHE_PUBLIQUE']==1)
		{
			$getMontantenleve = 'SELECT BUDGET_ENLEVE_T1,BUDGET_ENLEVE_T2,BUDGET_ENLEVE_T3,BUDGET_ENLEVE_T4 FROM execution_budgetaire_histo_budget_marche_public marche WHERE marche.EXECUTION_BUDGETAIRE_ID='.$data['info']['EXECUTION_BUDGETAIRE_ID'].' ORDER BY EXECUTION_BUDGETAIRE_HISTO_BUDGET_MARCHE_PUBLIC_ID DESC';
			$getMontantenleve = "CALL `getTable`('".$getMontantenleve."');";
			$MontantEnleve= $this->ModelPs->getRequeteOne($getMontantenleve);

			$mont_enleve='';
			if($data['info']['TRIMESTRE_ID']==1)
			{
				$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T1'];
			}
			elseif($data['info']['TRIMESTRE_ID']==2)
			{
				$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T2'];
			}
			elseif($data['info']['TRIMESTRE_ID']==3)
			{
				$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T3'];
			}
			elseif($data['info']['TRIMESTRE_ID']==4)
			{
				$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T4'];
			}
			$data['mont_enleve']=$mont_enleve;
			if($data['info']['TAUX_ECHANGE_ID']!=1)
			{
				$data['mont_enleve_devise']=$mont_enleve/$data['info']['TAUX'];
			}
		}

		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=10','PROFIL_ID DESC');
		$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

		if (!empty($getProfil))
		{
			foreach ($getProfil as $value)
			{

				if ($prof_id == $value->PROFIL_ID || $prof_id==1)
				{
					$verification  = 'SELECT BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,MOUVEMENT_DEPENSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID = 3 AND IS_MARCHE='.$data['info']['MARCHE_PUBLIQUE'].' ORDER BY DESC_TYPE_ANALYSE ASC';
					$verification = "CALL `getTable`('" . $verification . "');";
					$data['get_verification']= $this->ModelPs->getRequete($verification);
					$data['nbrverification'] = count($data['get_verification']);

					$data['TYPE_MONTANT_ID'] =$data['info']['TAUX_ECHANGE_ID'];
					$data['ETAPE_ID'] =10;

		            //get description etape
					$etape_descr  = 'SELECT DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande WHERE 1 AND ETAPE_DOUBLE_COMMANDE_ID='.$data['ETAPE_ID'];
					$etape_descr = "CALL `getTable`('" . $etape_descr . "');";
					$data['etape_descr']= $this->ModelPs->getRequeteOne($etape_descr);
					
		            //tva
					$taux_tva  = 'SELECT TAUX_TVA_ID ,DESCRIPTION_TAUX_TVA FROM taux_tva WHERE 1 ORDER BY DESCRIPTION_TAUX_TVA ASC';
					$taux_tvaRqt = "CALL `getTable`('" . $taux_tva . "');";
					$data['get_taux_tva']= $this->ModelPs->getRequete($taux_tvaRqt);

                    //Le min de la date de réception
					$bind_date_histo  = 'SELECT DATE_TRANSMISSION,hist.DATE_INSERTION FROM execution_budgetaire_tache_detail_histo hist WHERE hist.ETAPE_DOUBLE_COMMANDE_ID='.$data['ETAPE_ID'].' AND hist.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].' ORDER BY hist.EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC LIMIT 1';
					$bind_date_histoRqt = "CALL `getTable`('" . $bind_date_histo . "');";
					$data['date_trans']= $this->ModelPs->getRequeteOne($bind_date_histoRqt);

					// print_r($bind_date_histo);die();

		            #####################################################
					$type_liquidation  = 'SELECT LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,DESCRIPTION_LIQUIDATION FROM liquidation_type WHERE LIQUIDATION_TYPE_ID=1 ORDER BY DESCRIPTION_LIQUIDATION ASC';
					$type_liquidationRqt = "CALL `getTable`('" . $type_liquidation . "');";
					$data['get_type_liquidation']= $this->ModelPs->getRequeteOne($type_liquidationRqt);

					return view('App\Modules\double_commande_new\Views\Liquidation_Partiel_Add_View',$data);
				}  
			}
			return redirect('Login_Ptba/homepage'); 
		}
		else
		{
			return redirect('Login_Ptba/homepage');
		}
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

	//fonction get pour corriger les données idmd5  (id_detail) 
	public function getOne_corriger($idmd5='',$dist='')
	{
		$psgetrequete = "CALL `getRequete`(?,?,?,?)";
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$info = 'SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement WHERE md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$idmd5.'"';
		$infoDetail = "CALL `getTable`('" . $info . "');";
		$resultatdetail= $this->ModelPs->getRequeteOne($infoDetail);
		if(empty($resultatdetail))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$id=$resultatdetail['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'];
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		if($id=='' || $dist=='')
		{
			return redirect('Login_Ptba/do_logout');
		}
		
		$infoAffiche  = 'SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.MARCHE_PUBLIQUE, exec.COMMENTAIRE,exec.ENG_JURIDIQUE_DEVISE AS MONTANT_RACCROCHE_JURIDIQUE_DEVISE,exec.ENG_JURIDIQUE AS MONTANT_RACCROCHE_JURIDIQUE,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,exec.LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,ebtd.ETAPE_DOUBLE_COMMANDE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID, det.COUR_DEVISE,det.TITRE_CREANCE, exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,exec.LIQUIDATION,exec.LIQUIDATION_DEVISE AS MONTANT_RACCROCHE_LIQUIDATION_DEVISE,det.MONTANT_LIQUIDATION AS MONTANT_RACCROCHE_LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE, det.TAUX_TVA_ID,det.DATE_CREANCE, supp.DATE_FIN_CONTRAT, supp.NBRE_JR_CONTRAT, supp.DATE_DEBUT_CONTRAT, supp.MONTANT_AMENDE_PAYER, supp.NBRE_JR_RETARD, det.DATE_LIVRAISON_CONTRAT,det.DATE_LIQUIDATION,det.MOTIF_LIQUIDATION,det.PATH_PV_RECEPTION_LIQUIDATION,BUDGETAIRE_TYPE_DOCUMENT_ID,exec.TRIMESTRE_ID,dev.DESC_DEVISE_TYPE,PATH_FACTURE_LIQUIDATION,INTRODUCTION_NOTE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_tache_info_suppl supp ON supp.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id;
		$infoAffiche = "CALL `getTable`('" . $infoAffiche . "');";
		$data['info'] = $this->ModelPs->getRequeteOne($infoAffiche);

		$data['cour_devise']=number_format($data['info']['COUR_DEVISE'],$this->get_precision($data['info']['COUR_DEVISE']),'.',' ');

		//get les montant par tache
		$requeteTaches="SELECT ebet.MONTANT_ENG_JURIDIQUE TACHE_JURIDIQUE, 
							   ebet.MONTANT_ENG_JURIDIQUE_DEVISE TACHE_JURIDIQUE_DEVISE,
							   ebet.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID EBET_ID,
							   ebet.MONTANT_LIQUIDATION TACHE_LIQUIDATION,
							   ebet.MONTANT_LIQUIDATION_DEVISE TACHE_LIQUIDATION_DEVISE,
							   tache.DESC_TACHE,
							   ebetd.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_DETAIL_ID EBETD_ID,
							   ebetd.MONTANT_LIQUIDATION SINGLE_TACHE_LIQUIDATION,
							   ebetd.MONTANT_LIQUIDATION_DEVISE SINGLE_TACHE_LIQUIDATION_DEVISE 
						 FROM execution_budgetaire_execution_tache ebet 
						 JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID
						 JOIN execution_budgetaire_execution_tache_detail ebetd ON ebetd.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID=ebet.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID
						 WHERE ebetd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$id;
		$infoTaches = "CALL `getTable`('" . $requeteTaches . "');";
		$data['infoTaches'] = $this->ModelPs->getRequete($infoTaches);
		$data['infoTachesJson'] = json_encode($data['infoTaches']);

		if($data['info']['MARCHE_PUBLIQUE']==1)
		{
			$getMontantenleve = 'SELECT BUDGET_ENLEVE_T1,BUDGET_ENLEVE_T2,BUDGET_ENLEVE_T3,BUDGET_ENLEVE_T4 FROM execution_budgetaire_histo_budget_marche_public marche WHERE marche.EXECUTION_BUDGETAIRE_ID='.$data['info']['EXECUTION_BUDGETAIRE_ID'].' ORDER BY EXECUTION_BUDGETAIRE_HISTO_BUDGET_MARCHE_PUBLIC_ID DESC';
	    	$getMontantenleve = "CALL `getTable`('".$getMontantenleve."');";
	    	$MontantEnleve= $this->ModelPs->getRequeteOne($getMontantenleve);

	    	$mont_enleve='';
	    	if($data['info']['TRIMESTRE_ID']==1)
	    	{
	    		$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T1'];
	    	}
	    	elseif($data['info']['TRIMESTRE_ID']==2)
	    	{
	    		$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T2'];
	    	}
	    	elseif($data['info']['TRIMESTRE_ID']==3)
	    	{
	    		$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T3'];
	    	}
	    	elseif($data['info']['TRIMESTRE_ID']==4)
	    	{
	    		$mont_enleve=$MontantEnleve['BUDGET_ENLEVE_T4'];
	    	}
	    	$data['mont_enleve']=$mont_enleve;

	    	if($data['info']['TAUX_ECHANGE_ID']!=1)
	    	{
	    		$data['mont_enleve_devise']=$mont_enleve/$data['info']['COUR_DEVISE'];
	    	}
		}

		$id_racc = md5($data['info']['EXECUTION_BUDGETAIRE_ID']);

		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
		$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

		if (!empty($getProfil))
		{
			foreach ($getProfil as $value)
			{
				if ($prof_id == $value->PROFIL_ID || $prof_id==1)
				{
					$verification  = 'SELECT BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,MOUVEMENT_DEPENSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID = 3 AND IS_MARCHE='.$data['info']['MARCHE_PUBLIQUE'].' ORDER BY DESC_TYPE_ANALYSE ASC';
					$verification = "CALL `getTable`('" . $verification . "');";
					$data['get_verification']= $this->ModelPs->getRequete($verification);
					$data['nbrverification'] = count($data['get_verification']);

					$data['TYPE_MONTANT_ID'] =$data['info']['TAUX_ECHANGE_ID'];
					$data['ETAPE_ID'] =$data['info']['ETAPE_DOUBLE_COMMANDE_ID'];

		            //get description etape
					$etape_descr  = 'SELECT DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande WHERE 1 AND ETAPE_DOUBLE_COMMANDE_ID='.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'].'';
					$etape_descr = "CALL `getTable`('" . $etape_descr . "');";
					$data['etape_descr']= $this->ModelPs->getRequeteOne($etape_descr);

		            //tva
					$taux_tva  = 'SELECT TAUX_TVA_ID ,DESCRIPTION_TAUX_TVA FROM taux_tva WHERE 1 ORDER BY DESCRIPTION_TAUX_TVA ASC';
					$taux_tvaRqt = "CALL `getTable`('" . $taux_tva . "');";
					$data['get_taux_tva']= $this->ModelPs->getRequete($taux_tvaRqt);

		            //type_liquidation
					$type_liquidation  = 'SELECT LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,DESCRIPTION_LIQUIDATION FROM liquidation_type WHERE 1 ORDER BY DESCRIPTION_LIQUIDATION ASC';
					if($data['info']['MARCHE_PUBLIQUE']==1)
					{
						$type_liquidation = 'SELECT LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,DESCRIPTION_LIQUIDATION FROM liquidation_type WHERE LIQUIDATION_TYPE_ID <> 2 ORDER BY DESCRIPTION_LIQUIDATION ASC';
					}
					$type_liquidationRqt = "CALL `getTable`('" . $type_liquidation . "');";
					$data['get_type_liquidation']= $this->ModelPs->getRequete($type_liquidationRqt);
                    // Fin envoie de donées pour tout le view de ce 3 action du process 

                    //Le min de la date de réception
					$bind_date_histo  = 'SELECT DATE_TRANSMISSION,DATE_INSERTION FROM execution_budgetaire_tache_detail_histo  WHERE 1 AND EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].' ORDER BY DATE_INSERTION DESC';
					$bind_date_histoRqt = "CALL `getTable`('" . $bind_date_histo . "');";
					$data['date_trans']= $this->ModelPs->getRequeteOne($bind_date_histoRqt);
		            #####################################################

		            //retour pour correction
					if($dist==3)
					{
			            //multi select de verification dans le view

						$sqlVerification='SELECT DISTINCT analyse.DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE,analyse.MOUVEMENT_DEPENSE_ID, analyse.BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,veri.EXECUTION_BUDGETAIRE_ID FROM budgetaire_type_analyse analyse JOIN execution_budgetaire_histo_operation_verification veri ON veri.TYPE_ANALYSE_ID=analyse.BUDGETAIRE_TYPE_ANALYSE_ID WHERE veri.EXECUTION_BUDGETAIRE_ID='.$data['info']['EXECUTION_BUDGETAIRE_ID'].' AND veri.TYPE_ANALYSE_ID IN(SELECT BUDGETAIRE_TYPE_ANALYSE_ID FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID=3)';
						$data['get_verification2'] = $this->ModelPs->getRequete("CALL `getTable`('" . $sqlVerification . "')");

			            //la correction (obseravtion,date transmission) motif rejet apres rejet dans l'input
						$bindparams=$this->getBindParms('OBSERVATION,DATE_TRANSMISSION,DATE_RECEPTION,MOTIF_REJET','execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].'','EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
						$data['data_correction'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
			            #################################    

	      	            //Récuperation de l'étape précedent
						$bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].'','EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
						$bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
						$etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

			      //get motif rejet de la table historique_raccrochage_operation_verification_motif 
						$motif_rejet  = 'SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].' AND ETAPE_DOUBLE_COMMANDE_ID='.$etap_prev['ETAPE_ID'].'';
						$motif_rejetRqt = "CALL getTable('" . $motif_rejet . "');";
						$data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);


      		  // correction 
						if (!empty($data['get_verification2']))
						{
							$TYPE_ANALYSE_ID  = '';
							foreach ($data['get_verification2'] as $key)
							{
								$TYPE_ANALYSE_ID .= $key->TYPE_ANALYSE_ID.',';
							}

							$TYPE_ANALYSE_ID.=',';
							$TYPE_ANALYSE_ID = str_replace(',,','',$TYPE_ANALYSE_ID);

							$bindparams=$this->getBindParms('BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID, DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE','budgetaire_type_analyse','1 AND BUDGETAIRE_TYPE_ANALYSE_ID NOT IN ('.$TYPE_ANALYSE_ID.') AND MOUVEMENT_DEPENSE_ID = 3','DESC_BUDGETAIRE_TYPE_ANALYSE ASC');
							$data['get_verification'] = $this->ModelPs->getRequete($callpsreq, $bindparams);
						}
						else
						{
							$verification  = 'SELECT BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,MOUVEMENT_DEPENSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID = 3 AND  IS_MARCHE='.$data['info']['MARCHE_PUBLIQUE'].' ORDER BY DESC_TYPE_ANALYSE ASC';
							$verification = "CALL `getTable`('" . $verification . "');";
							$data['get_verification']= $this->ModelPs->getRequete($verification);
						}

						$detail=$this->detail_new(md5($data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
						$data['get_info']=$detail['get_info'];
			            $data['montantvote']=$detail['montantvote'];
			            $data['get_infoEBET']=$detail['get_infoEBET'];

						$info_detail  = 'SELECT EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_tache_detail WHERE  EXECUTION_BUDGETAIRE_ID='.$data['info']['EXECUTION_BUDGETAIRE_ID'].'';
						$info_detail = "CALL `getTable`('" . $info_detail . "');";
						$info_detail = $this->ModelPs->getRequeteOne($info_detail);
						$count_detail = count($info_detail);

						if ($data['info']['ID_TYPE_LIQUIDATION'] == 1)
						{
							$type_liquidation  = 'SELECT LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,DESCRIPTION_LIQUIDATION FROM liquidation_type WHERE LIQUIDATION_TYPE_ID='.$data['info']['ID_TYPE_LIQUIDATION'].' ORDER BY DESCRIPTION_LIQUIDATION ASC';
							$type_liquidationRqt = "CALL `getTable`('" . $type_liquidation . "');";
							$data['get_type_liquidation']= $this->ModelPs->getRequeteOne($type_liquidationRqt);

							$getLiquid  = 'SELECT det.MONTANT_LIQUIDATION AS MONTANT_RACCROCHE_LIQUIDATION, det.MONTANT_LIQUIDATION_DEVISE AS MONTANT_RACCROCHE_LIQUIDATION_DEVISE FROM execution_budgetaire_tache_detail det WHERE det.EXECUTION_BUDGETAIRE_DETAIL_ID='.$data['info']['EXECUTION_BUDGETAIRE_DETAIL_ID'].'';
							$getLiquid = "CALL `getTable`('" . $getLiquid . "');";
							$get_monnaie= $this->ModelPs->getRequeteOne($getLiquid);

							$data['get_monnaie'] = ($data['TYPE_MONTANT_ID']==1) ? $get_monnaie['MONTANT_RACCROCHE_LIQUIDATION'] : $get_monnaie['MONTANT_RACCROCHE_LIQUIDATION_DEVISE'] ;

							return view('App\Modules\double_commande_new\Views\Correction_Liquidation_Partiel_View',$data);
						}
						else
						{
							return view('App\Modules\double_commande_new\Views\Correction_Liquidation_Views',$data);
						}	
					}
					else
					{
						return redirect('Login_Ptba/do_logout');
					}
				}  
			}
			return redirect('Login_Ptba/homepage'); 
		}
		else
		{
			return redirect('Login_Ptba/homepage');
		}	
	}

	public function liste_historique_liquidation()
	{
		$session  = \Config\Services::session();
		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
		if($gdc!=1 AND $ced!=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
		$TYPE_MONTANT_ID = $this->request->getPost('TYPE_MONTANT_ID');

		$money  = ' SELECT DEVISE_TYPE_HISTO_ID,TAUX,DEV.DESC_DEVISE_TYPE AS DEVISE FROM devise_type_hist hist JOIN devise_type dev ON dev.DEVISE_TYPE_ID=hist.DEVISE_TYPE_ID WHERE IS_ACTIVE=1 AND hist.DEVISE_TYPE_ID='.$TYPE_MONTANT_ID.'';
		$money = "CALL `getTable`('" . $money . "');";
		$histo_money= $this->ModelPs->getRequeteOne($money);

		$type_monnaie = ($TYPE_MONTANT_ID==1) ? $histo_money['DEVISE'] : $histo_money['DEVISE'].', taux = '.$histo_money['TAUX'] ;

		$histo_liquidation  = 'SELECT DISTINCT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,exec.ENG_JURIDIQUE AS MONTANT_RACCROCHE_JURIDIQUE,exec.ENG_BUDGETAIRE as MONTANT_RACCROCHE,exec.ENG_JURIDIQUE_DEVISE AS MONTANT_RACCROCHE_JURIDIQUE_DEVISE,det.MONTANT_LIQUIDATION AS MONTANT_RACCROCHE_LIQUIDATION,det.DATE_LIQUIDATION, det.TITRE_CREANCE, det.MOTIF_LIQUIDATION, det.MONTANT_CREANCE,det.MONTANT_LIQUIDATION_DEVISE AS MONTANT_RACCROCHE_LIQUIDATION_DEVISE,COUR_DEVISE FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE det.EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' AND ebtd.ETAPE_DOUBLE_COMMANDE_ID>10';
		$histo_liquidation = "CALL `getTable`('" . $histo_liquidation . "');";
		$histo_liquidation = $this->ModelPs->getRequete($histo_liquidation);

		if (!empty($histo_liquidation))
		{
			$colonne1 = '';
			$colonne2 = '';
			$budget = '';
			$juridiq = '';
			$liquide = '';

			$table = '<div class="table-responsive">
			<h4>Historique liquidation</h4>
			<table id="tables_histo_liquidation" class="table table-bordered table-hover table-striped table-condesed">
			<thead>
			<tr>
			<th>Devise</th>
			<th>MONTANT&nbsp;&nbsp;BUDGETAIRE</th>
			<th>MONTANT&nbsp;&nbsp;JURUDIQUE</th>
			<th>MONTANT&nbsp;&nbsp;LIQUIDATION</th>
			<th>TITRE&nbsp;&nbsp;CREANCE</th>			                
			<th>DATE&nbsp;&nbsp;LIQUIDATION</th>
			<th>MOTIF&nbsp;&nbsp;LIQUIDATION</th>
			</tr>
			<thead><tbody>';

			foreach ($histo_liquidation as $row) 
			{
				if ($TYPE_MONTANT_ID !=1)
				{
					$colonne1 = '<th>MONTANT&nbsp;&nbsp;DEVISE</th>';
					$budget=number_format($row->MONTANT_RACCROCHE_DEVISE,2,',',' ');
					$juridiq=number_format($row->MONTANT_RACCROCHE_JURIDIQUE_DEVISE,2,',',' ');
					$liquide=number_format($row->MONTANT_RACCROCHE_LIQUIDATION_DEVISE,2,',',' ');
				}
				else
				{
					$colonne1 = '<th>MONTANT&nbsp;&nbsp;CREANCE</th>';
					$budget=number_format($row->MONTANT_RACCROCHE,2,',',' ');
					$juridiq=number_format($row->MONTANT_RACCROCHE_JURIDIQUE,2,',',' ');
					$liquide=number_format($row->MONTANT_RACCROCHE_LIQUIDATION,2,',',' ');
				}

				$table.="<tr>
				<td>".number_format($row->COUR_DEVISE,2,',',' ')."</td>
				<td>".$budget."</td>
				<td>".$juridiq."</td>
				<td>".$liquide."</td>
				<td>".$row->TITRE_CREANCE."</td>
				<td>".$row->DATE_LIQUIDATION."</td> 
				<td>".$row->MOTIF_LIQUIDATION."</td> 
				<tr>";
			}
			$table.='</tbody><table/></div>';
			$table.='<script>
			$(document).ready(function(){
				
				$("#tables_histo_liquidation").DataTable({
					lengthMenu: [[2,10, 20,-1], [2,10, 20, "All"]],
					pageLength: 2,
					"columnDefs":[{
						"targets":[],
						"orderable":false
						}],
						
						language: {
							"sProcessing":     "Traitement en cours...",
							"sSearch":         "Rechercher&nbsp;:",
							"sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
							"sInfo":           "Affichage de l\'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
							"sInfoEmpty":      "Affichage de l\'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
							"sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
							"sInfoPostFix":    "",
							"sLoadingRecords": "Chargement en cours...",
							"sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
							"sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
							"oPaginate": {
								"sFirst":      "Premier",
								"sPrevious":   "Pr&eacute;c&eacute;dent",
								"sNext":       "Suivant",
								"sLast":       "Dernier"
								},
								"oAria": {
									"sSortAscending":  ": activer pour trier la colonne par ordre croissant",
									"sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
								}
							}
							
							});
							}); 
							</script>';

			$output = array('tabledata'=>$table);
			echo json_encode($output);
		}
	}

	/* Debut Gestion update table de la demande detail*/
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	/* Fin Gestion update table de la demande detail*/

	/* Debut Gestion insertion */
	public function save_all_table($table,$columsinsert,$datacolumsinsert)
	{
		// $columsinsert: Nom des colonnes separe par,
		// $datacolumsinsert : les donnees a inserer dans les colonnes
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams =[$table,$columsinsert,$datacolumsinsert];
		$result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
		return $id=$result['id'];
	}

	public function getInfoDetail($value='')
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

		if ($USER_ID=='') {
			return  redirect('Login_Ptba/do_logout');
		}

		$ID_TYPE_LIQUIDATION = $this->request->getPost('ID_TYPE_LIQUIDATION');
		$TITRE_CREANCE = $this->request->getPost('TITRE_CREANCE');
		$DATE_CREANCE = $this->request->getPost('DATE_CREANCE');
		$MONTANT_CREANCE = $this->request->getPost('MONTANT_CREANCE');
		$LIQUIDATION = $this->request->getPost('LIQUIDATION');
		$DATE_LIQUIDATION = $this->request->getPost('DATE_LIQUIDATION');
		$MOTIF_LIQUIDATION = $this->request->getPost('MOTIF_LIQUIDATION');
		$TAUX_TVA_ID = $this->request->getPost('TAUX_TVA_ID');
		$EXONERATION = $this->request->getPost('EXONERATION');
		$OBSERVATION = $this->request->getPost('OBSERVATION');
		$DESC_TYPE_ANALYSE = $this->request->getPost('DESC_TYPE_ANALYSE[]');
		$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
		$DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
		$TYPE_MONTANT_ID = $this->request->getPost('TYPE_MONTANT_ID');
		$MONTANT_DEVISE = $this->request->getPost('MONTANT_DEVISE');

		$MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
		$BUDGETAIRE_TYPE_DOCUMENT_ID=$this->request->getPost('BUDGETAIRE_TYPE_DOCUMENT_ID');
		$intro_note=$this->request->getPost('intro_note');
		$PATH_PV_RECEPTION_LIQUIDATION = '';
		$PATH_FACTURE_LIQUIDATION = '';
		$COUT_DEVISE=$this->request->getPost('COUT_DEVISE');
		$DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');
		######################## upload file ##############################
		if ($MARCHE_PUBLIQUE==1)
		{
			$DATE_LIVRAISON_CONTRAT = $this->request->getPost('DATE_LIVRAISON_CONTRAT');

			//Vérifier si l'input du fichier 1 est vide
			$file_1 = $this->request->getFile('PATH_PV_RECEPTION_LIQUIDATION');

			if (!$file_1 || !$file_1->isValid())
			{
				$PATH_PV_RECEPTION_LIQUIDATION=$this->request->getPost('PATH_PV_RECEPTION_LIQUIDATION_OUP');

			}else
			{
				$PATH_PV_RECEPTION_LIQUIDATION = $this->request->getPost('PATH_PV_RECEPTION_LIQUIDATION');
				$PATH_PV_RECEPTION_LIQUIDATION=$this->uploadFile('PATH_PV_RECEPTION_LIQUIDATION','file_liquidation_tempo',$PATH_PV_RECEPTION_LIQUIDATION);								
			}				
			
			$insertIntoTable='execution_budgetaire_tempo_file_liquidation';
			$columsinsert="USER_ID,PATH_PV_RECEPTION_LIQUIDATION";
			$datacolumsinsert=$USER_ID.",'".$PATH_PV_RECEPTION_LIQUIDATION."'";
			$TEMPO_FILE_LIQUIDATION_ID   = $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

			$sql_file='SELECT PATH_PV_RECEPTION_LIQUIDATION FROM execution_budgetaire_tempo_file_liquidation WHERE TEMPO_FILE_LIQUIDATION_ID='.$TEMPO_FILE_LIQUIDATION_ID.' AND USER_ID='.$USER_ID.'';
			$file = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_file . "')");

			$PATH_PV_RECEPTION_LIQUIDATION = '<a href="'.base_url($file['PATH_PV_RECEPTION_LIQUIDATION']).'" target="_blank"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></a>';			
		}

		if($BUDGETAIRE_TYPE_DOCUMENT_ID==2)
		{
			//Vérifier si l'input du fichier 2 est vide
			$file_2 = $this->request->getFile('PATH_FACTURE_LIQUIDATION');

			if (!$file_2 || !$file_2->isValid())
			{
				$PATH_FACTURE_LIQUIDATION=$this->request->getPost('PATH_FACTURE_LIQUIDATION_OUP');
			}
			else
			{
				$PATH_FACTURE_LIQUIDATION = $this->request->getPost('PATH_FACTURE_LIQUIDATION');
				$PATH_FACTURE_LIQUIDATION=$this->uploadFile('PATH_FACTURE_LIQUIDATION','file_liquidation_tempo',$PATH_FACTURE_LIQUIDATION);
			}
			$insertIntoTable='execution_budgetaire_tempo_file_liquidation';
			$columsinsert="USER_ID,PATH_FACTURE_LIQUIDATION";
			$datacolumsinsert=$USER_ID.",'".$PATH_FACTURE_LIQUIDATION."'";
			$TEMPO_FILE_LIQUIDATION_ID   = $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

			$sql_file='SELECT PATH_FACTURE_LIQUIDATION FROM execution_budgetaire_tempo_file_liquidation WHERE TEMPO_FILE_LIQUIDATION_ID='.$TEMPO_FILE_LIQUIDATION_ID.' AND USER_ID='.$USER_ID.'';
			$file = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_file . "')");

			$PATH_FACTURE_LIQUIDATION = '<a href="'.base_url($file['PATH_FACTURE_LIQUIDATION']).'" target="_blank"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></a>';
		}
		#####################################      ################################################

		//type_liquidation
		$type_liquidation  = 'SELECT LIQUIDATION_TYPE_ID AS ID_TYPE_LIQUIDATION,DESCRIPTION_LIQUIDATION FROM liquidation_type WHERE LIQUIDATION_TYPE_ID='.$ID_TYPE_LIQUIDATION.'';
		$type_liquidationRqt = "CALL `getTable`('" . $type_liquidation . "');";
		$get_type_liquidation = $this->ModelPs->getRequeteOne($type_liquidationRqt);
		
		//get taux
		$sql_taux='SELECT DESCRIPTION_TAUX_TVA FROM taux_tva WHERE 1 AND TAUX_TVA_ID ='.$TAUX_TVA_ID.' ';
		$get_taux_tva = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_taux . "')");
		################################################################     ################

		if ($EXONERATION==1) {
			$EXONERATION_DATA = ''.lang('messages_lang.label_oui').'';
		}else{
			$EXONERATION_DATA = ''.lang('messages_lang.label_non').'';
		}
		###############################################    ################################
		$infoTypeMontantUS = '';
		$infoTypeMontantBIF = '';
		$infoMarchePublique = '';

		if ($TYPE_MONTANT_ID!=1)
		{
			$infoTypeMontantUS = '<tr>
			<td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;'.lang('messages_lang.label_montant_devise').' </font></td>
			<td><strong><font style="float:left;">'.$MONTANT_DEVISE.'</font></strong></td>
			</tr>
			<tr>
			<td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;'.lang('messages_lang.label_montant_titre_creance').'</font></td>
			<td><strong><font style="float:left;">'.$LIQUIDATION.'</font></strong></td>
			</tr>
			<tr>
        <td><i class="fa fa-credit-card"></i> &nbsp;<font>'.lang('messages_lang.label_echange').'</font></td>
        <td>
          <strong>'.$COUT_DEVISE.'</strong>
        </td>
      </tr>
      <tr id="date_sha">
        <td><i class="fa fa-calendar"></i> &nbsp;<font>'.lang('messages_lang.label_date_cours').'</font></td>
        <td>
          <strong>'.$DATE_COUT_DEVISE.'</strong>
        </td>
      </tr>';
		}
		else if ($TYPE_MONTANT_ID==1)
		{
			$infoTypeMontantBIF = '<tr>
			<td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;'.lang('messages_lang.label_montant_titre_creance').' </font></td>
			<td><strong><font style="float:left;">'.$MONTANT_CREANCE.'</font></strong></td>
			</tr>';
		}

		if ($MARCHE_PUBLIQUE==1)
		{
			$infoMarchePublique = '
			<tr>
			<td style="width:250px ;"><font style="float:left;"><i class="fa fa-file"></i>&nbsp;'.lang('messages_lang.label_pv_reception').'</font></td>
			<td><strong><font style="float:left;">'.$PATH_PV_RECEPTION_LIQUIDATION.'</font></strong></td>
			</tr>
			<tr>
			<td style="width:250px ;"><font style="float:left;"><i class="fa fa-file"></i>&nbsp;'.lang('messages_lang.label_facture').'</font></td>
			<td><strong><font style="float:left;">'.$PATH_FACTURE_LIQUIDATION.'</font></strong></td>
			</tr>
			<tr>
			<td style="width:250px ;"><font style="float:left;"><i class="fa fa-calendar"> </i>&nbsp;'.lang('messages_lang.label_date_livraison').' </font></td>
			<td><strong><font style="float:left;">'.date('d-m-Y',strtotime($DATE_LIVRAISON_CONTRAT)).'</font></strong></td>
			</tr>';
		}

		//border:1px solid #ddd;border-radius:5px;margin: 5px
		$html = '<div class="col-12">
		<div class=" table-responsive ">
		<table class="table m-b-0 m-t-20">
		<tbody>
		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-list"> </i>&nbsp;'.lang('messages_lang.label_type_liquidation').'</font></td>
		<td><strong><font style="float:left;">'.$get_type_liquidation['DESCRIPTION_LIQUIDATION'].'</font></strong></td>
		</tr>
		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-home"> </i>&nbsp;'.lang('messages_lang.label_number_titre_creance').'</font></td>
		<td><strong><font style="float:left;">'.$TITRE_CREANCE.'</font></strong></td>
		</tr>
		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-calendar"> </i>&nbsp;'.lang('messages_lang.label_date_titre_creance').'</font></td>
		<td><strong><font style="float:left;">'.date('d-m-Y',strtotime($DATE_CREANCE)).'</font></strong></td>
		</tr>
		'.$infoTypeMontantBIF.'
		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-calendar"> </i>&nbsp;'.lang('messages_lang.table_ate_liq').' </font></td>
		<td><strong><font style="float:left;">'.date('d-m-Y',strtotime($DATE_LIQUIDATION)).'</font></strong></td>
		</tr>
		
		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-comment"> </i>&nbsp;'.lang('messages_lang.table_moif').'</font></td>
		<td><strong><font style="float:left;">'.$MOTIF_LIQUIDATION.'</font></strong></td>
		</tr>
		'.$infoTypeMontantUS.'

		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-list"> </i>&nbsp;'.lang('messages_lang.label_taux_tva').'</font></td>
		<td><strong><font style="float:left;">'.$get_taux_tva['DESCRIPTION_TAUX_TVA'].'</font></strong></td>
		</tr>

		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;'.lang('messages_lang.table_exo').'</font></td>
		<td><strong><font style="float:left;">'.$EXONERATION_DATA.'</font></strong></td>
		</tr>

		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-calendar"></i>&nbsp;'.lang('messages_lang.labelle_d_reception').'</font></td>
		<td><strong><font style="float:left;">'.date('d-m-Y',strtotime($DATE_RECEPTION)).'</font></strong></td>
		</tr>

		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-calendar"></i>&nbsp;'.lang('messages_lang.labelle_d_trans').'</font></td>
		<td><strong><font style="float:left;">'.date('d-m-Y',strtotime($DATE_TRANSMISSION)).'</font></strong></td>
		</tr>
		
		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-list"></i>&nbsp;'.lang('messages_lang.label_verification').'</font></td>
		<td><strong><font style="float:left;">'.$DESC_TYPE_ANALYSE.'</font></strong></td>
		</tr>

		<tr>
		<td style="width:250px ;"><font style="float:left;"><i class="fa fa-comment"></i>&nbsp;'.lang('messages_lang.labelle_observartion').'</font></td>
		<td><strong><font style="float:left;">'.$OBSERVATION.'</font></strong></td>
		</tr>

		'.$infoMarchePublique.'

		</tbody>
		</table>        
		</div>
		</div>';

		$output = array(
			"html" => $html
		);
		return $this->response->setJSON($output);
	}

	// insert data liquidation
	public function add($value='')
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$rules = [
			'ID_TYPE_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_RECEPTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'TITRE_CREANCE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_CREANCE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'MOTIF_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'TAUX_TVA_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'EXONERATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'DATE_TRANSMISSION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'TYPE_ANALYSE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			]      
		];

		$MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
		$BUDGETAIRE_TYPE_DOCUMENT_ID = $this->request->getPost('BUDGETAIRE_TYPE_DOCUMENT_ID');
		$TYPE_MONTANT_ID = $this->request->getPost('TYPE_MONTANT_ID');

		if ($TYPE_MONTANT_ID != 1)
		{
			$rules['MONTANT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['LIQUIDATION'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['COUT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['DATE_COUT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];   

			$infoTaches=$this->request->getPost("infoTaches");
			$infoTaches = json_decode($infoTaches, true);

			foreach($infoTaches as $key)
			{
				$liquidTache=$this->request->getPost("MONTANT_DEVISE_".$key['EBET_ID']);
				if($liquidTache > $key['TACHE_JURIDIQUE_DEVISE'])
				{
					$rules['MONTANT_DEVISE_'.$key["EBET_ID"]] = [
						'label' => '',
						'rules' => 'required',
						'errors' => [
							'required' => '<font style="color:red;size:2px;">Montant invalide</font>'
						]
					];
				}
			}    
		}
		else
		{
			$rules['MONTANT_CREANCE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$infoTaches=$this->request->getPost("infoTaches");
			$infoTaches = json_decode($infoTaches, true);

			foreach($infoTaches as $key)
			{
				$liquidTache=$this->request->getPost("MONTANT_".$key['EBET_ID']);
				if($liquidTache > $key['TACHE_JURIDIQUE'])
				{
					$rules['MONTANT_'.$key["EBET_ID"]] = [
						'label' => '',
						'rules' => 'required',
						'errors' => [
							'required' => '<font style="color:red;size:2px;">Montant invalide</font>'
						]
					];
				}
			}
		}

		if ($MARCHE_PUBLIQUE == 1)
		{
			$rules['DATE_LIVRAISON_CONTRAT'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
		}

		if ($BUDGETAIRE_TYPE_DOCUMENT_ID == 2)
		{
			$rules['intro_note'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire'
				]
			];
		}

		$this->validation->setRules($rules);

		$EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
		$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');

		if($this->validation->withRequest($this->request)->run())
		{
			$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
			$TITRE_CREANCE = $this->request->getPost('TITRE_CREANCE');
			$DATE_CREANCE = $this->request->getPost('DATE_CREANCE');
			$MONTANT_CREANCE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_CREANCE'));
			$DATE_LIQUIDATION = $this->request->getPost('DATE_LIQUIDATION');
			$MOTIF_LIQUIDATION = str_replace("'", "\'", $this->request->getPost('MOTIF_LIQUIDATION'));
			$MONTANT_DEVISE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_DEVISE'));
			$TAUX_TVA_ID = $this->request->getPost('TAUX_TVA_ID');
			$EXONERATION = $this->request->getPost('EXONERATION');
			$ID_TYPE_LIQUIDATION = $this->request->getPost('ID_TYPE_LIQUIDATION');
			$MONTANT_RACCROCHE_JURIDIQUE_VALUE = $this->request->getPost('MONTANT_RACCROCHE_JURIDIQUE_VALUE');
			$MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE = $this->request->getPost('MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE');
			$DATE_LIVRAISON_CONTRAT = $this->request->getPost('DATE_LIVRAISON_CONTRAT');
			$LIQUIDATION = preg_replace('/\s/', '', $this->request->getPost('LIQUIDATION'));
			$intro_note=$this->request->getPost('intro_note');
			$intro_note=addslashes($intro_note);
			$COUT_DEVISE=$this->request->getPost('COUT_DEVISE');
			$COUT_DEVISE=str_replace(' ','',$COUT_DEVISE);
			$DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');

			//on fait l'update dans infos sup si c'est marché publique
			if($MARCHE_PUBLIQUE==1)
			{
				######################## upload file ##############################
				$PATH_PV_RECEPTION_LIQUIDATION = $this->request->getPost('PATH_PV_RECEPTION_LIQUIDATION');
				$PATH_PV_RECEPTION=$this->uploadFile('PATH_PV_RECEPTION_LIQUIDATION','double_commande_new',$PATH_PV_RECEPTION_LIQUIDATION);
	  		######################## upload file ##############################

	  		//mise a jour dans la table execution_budgetaire_raccrochage_activite_info_suppl
				$table = 'execution_budgetaire_tache_detail';
				$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$data='TAUX_TVA_ID='.$TAUX_TVA_ID.',EXONERATION='.$EXONERATION.',DATE_LIVRAISON_CONTRAT="'.$DATE_LIVRAISON_CONTRAT.'"';
				$this->update_all_table($table,$data,$where);
	    	####################################################################

	  		//mise a jour dans la table   execution_budgetaire_raccrochage_activite_detail
				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1='PATH_PV_RECEPTION_LIQUIDATION="'.$PATH_PV_RECEPTION.'"';
				$this->update_all_table($table1,$data1,$where1);
			}

			if($BUDGETAIRE_TYPE_DOCUMENT_ID==2)
			{
				$PATH_FACTURE_LIQUIDATION = $this->request->getPost('PATH_FACTURE_LIQUIDATION');
				$PATH_FACTURE=$this->uploadFile('PATH_FACTURE_LIQUIDATION','double_commande_new',$PATH_FACTURE_LIQUIDATION);

	  		//mise a jour dans la table   execution_budgetaire_raccrochage_activite_detail
				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1='PATH_FACTURE_LIQUIDATION="'.$PATH_FACTURE.'",INTRODUCTION_NOTE="'.$intro_note.'"';
				$this->update_all_table($table1,$data1,$where1);
			}

			########################################################################
			$MONTANT_DEVISE=!empty($MONTANT_DEVISE)?$MONTANT_DEVISE:0;
			$EXONERATION=!empty($EXONERATION)?$EXONERATION:0;

			$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
			$ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

			//récuperer les etapes et mouvements
			$psgetrequete = "CALL `getRequete`(?,?,?,?);";
			$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
			$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
			$MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

			// MOUVEMENT_DEPENSE_ID quité
			$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.'','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
			$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
			$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
			//etape suivant

			$next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
			$get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);
			$MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
				// mouve qui va suivre
			##############################################

			//mise a jour dans la table execution_budgetaire
			$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
			####################################################################

			$tableLiquidTermin = 'execution_budgetaire';
			$whereLiquidTermin='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
			$dataLiquidTermin='IS_FINISHED=1';

			//cas ou type montant est dollar, ero....
			if ($TYPE_MONTANT_ID!=1)
			{
				$LIQUIDATION = $LIQUIDATION;
				$DEVISE_TYPE_HISTO_LIQUI_ID	="";

				$taux_exist = "SELECT DEVISE_TYPE_HISTO_ID,DEVISE_TYPE_ID,TAUX,DATE_INSERTION FROM devise_type_hist WHERE DEVISE_TYPE_ID=".$TYPE_MONTANT_ID." AND TAUX=".$COUT_DEVISE." AND DATE_INSERTION LIKE '$DATE_COUT_DEVISE%'";
				$taux_exist='CALL `getTable`("'.$taux_exist.'")';
				$taux_exist= $this->ModelPs->getRequeteOne($taux_exist);
				if(!empty($taux_exist))
				{
					$DEVISE_TYPE_HISTO_LIQUI_ID=$taux_exist["DEVISE_TYPE_HISTO_ID"];
				}
				else
				{
					$columns="DEVISE_TYPE_ID,TAUX,IS_ACTIVE,DATE_INSERTION";
					$data_col=$TYPE_MONTANT_ID.",".$COUT_DEVISE.",0,'".$DATE_COUT_DEVISE."'";
					$table_dev="devise_type_hist";
					$DEVISE_TYPE_HISTO_LIQUI_ID =$this->save_all_table($table_dev,$columns,$data_col);
				}

				//cas partiel
				if($ID_TYPE_LIQUIDATION==1)
				{
					$dataLiquidTermin='IS_FINISHED=0';
					$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
				}
				else
				{
					$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
				}
				######################################################

				$table = 'execution_budgetaire';
				$where = 'EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$data ='LIQUIDATION_DEVISE="'.$MONTANT_DEVISE.'", LIQUIDATION='.$LIQUIDATION.', LIQUIDATION_TYPE_ID='.$ID_TYPE_LIQUIDATION;
				$this->update_all_table($table,$data,$where);
				###############################################   ###########################

				//update execution_bugetaire_execution_tache
				//cas total
				if($ID_TYPE_LIQUIDATION==2)
				{
					//update execution_bugetaire_execution_tache
					$tableEBET='execution_budgetaire_execution_tache';
					$conditionsEBET='EXECUTION_BUDGETAIRE_ID ='.$EXECUTION_BUDGETAIRE_ID;
					$datatomodifieEBET= 'MONTANT_LIQUIDATION=MONTANT_ENG_BUDGETAIRE, MONTANT_LIQUIDATION_DEVISE=MONTANT_ENG_BUDGETAIRE_DEVISE';
					$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);

					foreach ($infoTaches as $infoTache) 
					{
						$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
						$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
						$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;

						$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
						$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
						$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

						$MONTANT_EBET = $MONTANT_DEVISE_EBET * $COUT_DEVISE;

						//save les montants de chaque tache
						$columnsEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE";
						$dataEBETD=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$infoTache['EBET_ID'].",".$MONTANT_EBET.",".$MONTANT_DEVISE_EBET."";
						$tableEBETD="execution_budgetaire_execution_tache_detail";
						$resEBETD =$this->save_all_table($tableEBETD,$columnsEBETD,$dataEBETD);
					}

				}
				//cas partiel
				else{
					foreach ($infoTaches as $infoTache) {
						$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
						$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
						$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;


						$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
						$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
						$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

						$MONTANT_EBET = $MONTANT_DEVISE_EBET * $COUT_DEVISE;

						$tableEBET='execution_budgetaire_execution_tache';
						$conditionsEBET='EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ='.$infoTache['EBET_ID'];
						$datatomodifieEBET= 'MONTANT_LIQUIDATION='.$MONTANT_EBET.', MONTANT_LIQUIDATION_DEVISE='.$MONTANT_DEVISE_EBET;
						$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);

						//save les montants de chaque tache
						$columnsEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE";
						$dataEBETD=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$infoTache['EBET_ID'].",".$MONTANT_EBET.",".$MONTANT_DEVISE_EBET."";
						$tableEBETD="execution_budgetaire_execution_tache_detail";
						$resEBETD =$this->save_all_table($tableEBETD,$columnsEBETD,$dataEBETD);
					}

				}

				$table1 = 'execution_budgetaire_tache_detail';
				$where1 ='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1 = "MONTANT_LIQUIDATION_DEVISE=".$MONTANT_DEVISE.",MONTANT_LIQUIDATION='".$LIQUIDATION."',TITRE_CREANCE='".$TITRE_CREANCE."',DATE_CREANCE='".$DATE_CREANCE."',DATE_LIQUIDATION='".$DATE_LIQUIDATION."',MOTIF_LIQUIDATION='".$MOTIF_LIQUIDATION."',COUR_DEVISE='".$COUT_DEVISE."',DATE_COUR_DEVISE='".$DATE_COUT_DEVISE."',DEVISE_TYPE_HISTO_LIQUI_ID=".$DEVISE_TYPE_HISTO_LIQUI_ID."";
				$this->update_all_table($table1,$data1,$where1);

				//Enregistrement dans execution_budgetaire_titre_decaissement
	            $tableEBTD='execution_budgetaire_titre_decaissement';
	            $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
	            $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
	            $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

				##########################################################################
			}
			else
			{
				$LIQUIDATION = $MONTANT_CREANCE;
				//cas partiel
				if ($ID_TYPE_LIQUIDATION==1)
				{
					$dataLiquidTermin='IS_FINISHED=0';
					$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
				}
				else
				{
					$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
				}

				$table = 'execution_budgetaire';
				$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$data='LIQUIDATION='.$MONTANT_CREANCE.', LIQUIDATION_TYPE_ID='.$ID_TYPE_LIQUIDATION;
				$this->update_all_table($table,$data,$where);
				##############################################

				//update execution_bugetaire_execution_tache
				if($ID_TYPE_LIQUIDATION==2)
				{
					//update execution_bugetaire_execution_tache
					$tableEBET='execution_budgetaire_execution_tache';
					$conditionsEBET='EXECUTION_BUDGETAIRE_ID ='.$EXECUTION_BUDGETAIRE_ID;
					$datatomodifieEBET= 'MONTANT_LIQUIDATION=MONTANT_ENG_BUDGETAIRE, MONTANT_LIQUIDATION_DEVISE=MONTANT_ENG_BUDGETAIRE_DEVISE';
					$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);

					foreach ($infoTaches as $infoTache) 
					{
						$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
						$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
						$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;

						$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
						$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
						$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

						$MONTANT_EBET = floatval($MONTANT_DEVISE_EBET) * floatval($COUT_DEVISE);

						//save les montants de chaque tache
						$columnsEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE";
						$dataEBETD=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$infoTache['EBET_ID'].",".$MONTANT_EBET.",".$MONTANT_DEVISE_EBET."";
						$tableEBETD="execution_budgetaire_execution_tache_detail";
						$resEBETD =$this->save_all_table($tableEBETD,$columnsEBETD,$dataEBETD);
					}

				}
				//cas partiel
				else{
					foreach ($infoTaches as $infoTache) {
						$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
						$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
						$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;

						$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
						$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
						$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

						$tableEBET='execution_budgetaire_execution_tache';
						$conditionsEBET='EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ='.$infoTache['EBET_ID'];
						$datatomodifieEBET= 'MONTANT_LIQUIDATION='.$MONTANT_EBET.', MONTANT_LIQUIDATION_DEVISE='.$MONTANT_DEVISE_EBET;
						$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);

						//save les montants de chaque tache
						$columnsEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE";
						$dataEBETD=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$infoTache['EBET_ID'].",".$MONTANT_EBET.",".$MONTANT_DEVISE_EBET."";
						$tableEBETD="execution_budgetaire_execution_tache_detail";
						$resEBETD =$this->save_all_table($tableEBETD,$columnsEBETD,$dataEBETD);
					}
				}

				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1="MONTANT_CREANCE=".$MONTANT_CREANCE.", MONTANT_LIQUIDATION=".$MONTANT_CREANCE.", TITRE_CREANCE='".$TITRE_CREANCE."', DATE_CREANCE='".$DATE_CREANCE."', DATE_LIQUIDATION='".$DATE_LIQUIDATION."', MOTIF_LIQUIDATION='".$MOTIF_LIQUIDATION."'";
				$this->update_all_table($table1,$data1,$where1);

				//Enregistrement dans execution_budgetaire_titre_decaissement
	            $tableEBTD='execution_budgetaire_titre_decaissement';
	            $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
	            $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
	            $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

				##########################################################################
			}    

			//insert dans la table historique_raccrochage
			$OBSERVATION = str_replace("'", "\'", $this->request->getPost('OBSERVATION'));
			$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
			$DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
			$TYPE_RACCROCHAGE_ID = 2;

			$table2 = 'execution_budgetaire_tache_detail_histo';
			$columsinsert2="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID, OBSERVATION, DATE_TRANSMISSION, DATE_RECEPTION";
			$datacolumsinsert2=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.", ".$USER_ID.", ".$ETAPE_ID.", '".$OBSERVATION."','".$DATE_TRANSMISSION."', '".$DATE_RECEPTION."'";
			$this->save_all_table($table2,$columsinsert2,$datacolumsinsert2);
			##################################################################

			$table = 'execution_budgetaire_tache_detail';
			$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
			$data='TAUX_TVA_ID='.$TAUX_TVA_ID.',EXONERATION='.$EXONERATION.'';
			$this->update_all_table($table,$data,$where);
			###############################################   ###########################

			// insert dans la table historique_raccrochage_operation_verification
			$TYPE_ANALYSE_ID = $this->request->getPost('TYPE_ANALYSE_ID[]');
			$table5 = 'execution_budgetaire_histo_operation_verification';
			foreach ($TYPE_ANALYSE_ID as $key)
			{
				$columsinsert="TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID, EXECUTION_BUDGETAIRE_ID";
				$datacolumsinsert=$key.",".$ETAPE_ID.",".$EXECUTION_BUDGETAIRE_ID;
				$this->save_all_table($table5,$columsinsert,$datacolumsinsert);
			}
			######################################################################
			
			$data=['message' => "".lang('messages_lang.message_success').""];
			session()->setFlashdata('alert', $data);
			return redirect('double_commande_new/Liquidation_Double_Commande/get_liquid_Afaire');
		}
		else
		{	
			$dist=1;
			return $this->getOne(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),$dist);
		}			
	}

	// insert data validation 
	public function insert($value='')
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
		if($ced!=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}
		$rules = [
			'DATE_RECEPTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'ID_OPERATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_TRANSMISSION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			]  
		];
		$TYPE_OPERATION_ID = $this->request->getPost('ID_OPERATION');
		if ($TYPE_OPERATION_ID == 1)
		{
			$rules['MOTIF_REJET'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['ETAPE_RETOUR_CORRECTION_ID'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
		}
		if ($TYPE_OPERATION_ID == 3)
		{
			$rules['OBSERVATION'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
		}

		$this->validation->setRules($rules);
		$EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
		if($this->validation->withRequest($this->request)->run())
		{
			$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
			$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
			$OBSERVATION = str_replace("'", "\'", $this->request->getPost('OBSERVATION'));
			$MOTIF_REJET = $this->request->getPost('MOTIF_REJET[]');
			$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
			$ETAPE_ID = $this->request->getPost('ETAPE_ID');
			$MONTANT_RACCROCHE_LIQUIDATION = $this->request->getPost('MONTANT_RACCROCHE_LIQUIDATION');
			$ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
			$DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
			$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
			$BUDGETAIRE_TYPE_DOCUMENT_ID=$this->request->getPost("BUDGETAIRE_TYPE_DOCUMENT_ID");

			//get exec
			$psgetrequete = "CALL `getRequete`(?,?,?,?);";
			$get_exec = $this->getBindParms('LIQUIDATION_TYPE_ID,LIQUIDATION,LIQUIDATION_DEVISE,DEVISE_TYPE_ID,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE,MARCHE_PUBLIQUE','execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID,'EXECUTION_BUDGETAIRE_DETAIL_ID ASC');
			$get_exec = $this->ModelPs->getRequeteOne($psgetrequete, $get_exec);
			$LIQUIDATION_TYPE_ID = $get_exec['LIQUIDATION_TYPE_ID'];

		  //get detail
			$get_det = $this->getBindParms('COUNT(det.EXECUTION_BUDGETAIRE_DETAIL_ID) AS nbr','execution_budgetaire_tache_detail det LEFT JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','det.EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' AND ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13)','det.EXECUTION_BUDGETAIRE_ID ASC');
			$get_det = $this->ModelPs->getRequeteOne($psgetrequete, $get_det);
			$nbr_liqui_partiel=$get_det['nbr'];

			//si c'est (retour à la correction)
			if($TYPE_OPERATION_ID == 1)
			{
				$tableLiquidTermin = 'execution_budgetaire';
				$whereLiquidTermin='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$dataLiquidTermin='IS_FINISHED=0 ';
				$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
					###################################

	 			//Etape initiale de la liquidation
				if ($ETAPE_RETOUR_CORRECTION_ID==1)
				{
	    			//récuperer les etapes
					$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=1  AND MOUVEMENT_DEPENSE_ID=3','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
					$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
					$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
	      			//etape suivant

					$MONT_DEVISE=$get_exec['LIQUIDATION_DEVISE']-$get_exec['MONTANT_LIQUIDATION_DEVISE'];
					$MONT=floatval($get_exec['LIQUIDATION'])-floatval($get_exec['MONTANT_LIQUIDATION']);
					$table = 'execution_budgetaire';
					$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
					$data='LIQUIDATION='.$MONT.',LIQUIDATION_DEVISE='.$MONT_DEVISE;
					$this->update_all_table($table,$data,$where);

					//query le montant des taches de la liquidation
					$requeteTachesMontant = "SELECT 
												ebet.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID EBET_ID,
											   	ebet.MONTANT_LIQUIDATION TACHE_LIQUIDATION,
											   	ebet.MONTANT_LIQUIDATION_DEVISE TACHE_LIQUIDATION_DEVISE,
												ebetd.MONTANT_LIQUIDATION SINGLE_TACHE_LIQUIDATION,
							   					ebetd.MONTANT_LIQUIDATION_DEVISE SINGLE_TACHE_LIQUIDATION_DEVISE 
							   					FROM execution_budgetaire_execution_tache_detail ebetd
							   					JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID=ebetd.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID
							   					WHERE ebetd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
					$infoTachesMontant = "CALL `getTable`('" . $requeteTachesMontant . "');";
					$tachesMontants = $this->ModelPs->getRequete($infoTachesMontant);

					foreach ($tachesMontants as $tachesMontant) 
					{
						$NEW_MONTANT_EBET = $tachesMontant->TACHE_LIQUIDATION - $tachesMontant->SINGLE_TACHE_LIQUIDATION;
						$NEW_MONTANT_DEVISE_EBET = $tachesMontant->TACHE_LIQUIDATION_DEVISE - $tachesMontant->SINGLE_TACHE_LIQUIDATION_DEVISE;

						//update le montant total de chaque tache
						$tableEBET='execution_budgetaire_execution_tache';
						$conditionsEBET='EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ='.$tachesMontant->EBET_ID;
						$datatomodifieEBET= 'MONTANT_LIQUIDATION='.$NEW_MONTANT_EBET.', MONTANT_LIQUIDATION_DEVISE='.$NEW_MONTANT_DEVISE_EBET;
						$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);
					}
				}
	  			// etape engagement budgetaire
				else if ($ETAPE_RETOUR_CORRECTION_ID==2)
				{
					//récuperer les etapes et mouvements
					$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
					$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
	    		$MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];// MOUVEMENT_DEPENSE_ID quité

	    		$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=1','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
	    		$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
	    		$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant
	    		$MOUVEMENT_NEXT_ID =  $get_next_step['MOUVEMENT_DEPENSE_ID'];// mouve qui va suivre
	    		$this->gestion_rejet_ptba($EXECUTION_BUDGETAIRE_ID);
	    		#######################################         ##########################################
	    	}
	      	//etape engagement juridique
	    	else if ($ETAPE_RETOUR_CORRECTION_ID==3)
	    	{
	    		//récuperer les etapes et mouvements
	    		$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
	    		$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
	        $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];// MOUVEMENT_DEPENSE_ID quité

	        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=2','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
	        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
	        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant
	        $MOUVEMENT_NEXT_ID =  $get_next_step['MOUVEMENT_DEPENSE_ID'];// mouve qui va suivre
	        #######################################         ##########################################
	    	}

		  	// insert dans la table historique_raccrochage_operation_verification_motif	      
		    $table5 = 'execution_budgetaire_histo_operation_verification_motif';

		    foreach ($MOTIF_REJET as $key)
		    {
		    	$columsinsert="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
		    	$datacolumsinsert=$key.",".$ETAPE_ID.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
		    	$this->save_all_table($table5,$columsinsert,$datacolumsinsert);
		    }

		    $table1 = 'execution_budgetaire_titre_decaissement';
		    $where1='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
		    $data1="ETAPE_DOUBLE_COMMANDE_ID=".$NEXT_ETAPE_ID;
		    $this->update_all_table($table1,$data1,$where1);
					############################################################
			}
			//si c'est visa
			else if($TYPE_OPERATION_ID == 2)
			{
				// $tableLiquidTermin = 'execution_budgetaire';
				// $whereLiquidTermin='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				// $dataLiquidTermin='IS_FINISHED=0 ';
				// $this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);	
			  		//on verifie si le montant liquidation est interieur à 500 millions
				if ($MONTANT_RACCROCHE_LIQUIDATION<500000000)
				{
					$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND EST_SUPERIEUR_CENT_MILLION=0 AND IS_CORRECTION=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
					$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
				    	$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant
				}
			    else
			    {
			  		//récuperer les etapes et mouvements
			    	$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND EST_SUPERIEUR_CENT_MILLION=1 AND IS_CORRECTION=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
			    	$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
		        	$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant
		    	}

			    $table1 = 'execution_budgetaire_titre_decaissement';
			    $where1='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
			    $data1="ETAPE_DOUBLE_COMMANDE_ID=".$NEXT_ETAPE_ID;
			    $this->update_all_table($table1,$data1,$where1);
						############################################################
			}
		  	//si c'est Rejet
			else if($TYPE_OPERATION_ID == 3)
			{
				$tableLiquidTermin = 'execution_budgetaire';
				$whereLiquidTermin='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$dataLiquidTermin='IS_FINISHED=2';
				$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
			  		//récuperer les etapes
				$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=2','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
				$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
			  $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant

			  $NEXT_ETAPE_ID = 9;
	      if($LIQUIDATION_TYPE_ID==1 )
	      {
	      	if($nbr_liqui_partiel>1)
	      	{
	      		$NEXT_ETAPE_ID = 42;
	      	}
	      	else
	      	{
	      		$NEXT_ETAPE_ID = 9;
	      	}
	      }

	      if($get_exec['DEVISE_TYPE_ID']!=1)
	      {
	      	$MONT_DEVISE=$get_exec['LIQUIDATION_DEVISE']-$get_exec['MONTANT_LIQUIDATION_DEVISE'];
	      	$MONT=floatval($get_exec['LIQUIDATION'])-floatval($get_exec['MONTANT_LIQUIDATION']);
	      	$table = 'execution_budgetaire';
	      	$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
	      	$data='LIQUIDATION='.$MONT.',LIQUIDATION_DEVISE='.$MONT_DEVISE;
	      	$this->update_all_table($table,$data,$where);
	      }
	      else
	      {
	      	$MONT=floatval($get_exec['LIQUIDATION'])-floatval($get_exec['MONTANT_LIQUIDATION']);
	      	$table = 'execution_budgetaire';
	      	$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
	      	$data='LIQUIDATION='.$MONT;
	      	$this->update_all_table($table,$data,$where);
	      }

	      $table1 = 'execution_budgetaire_tache_detail';
	      $where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
	      $data1='MONTANT_LIQUIDATION=0, MONTANT_LIQUIDATION_DEVISE=0';
	      $this->update_all_table($table1,$data1,$where1);

	      $tableEBTD = 'execution_budgetaire_titre_decaissement';
	      $whereEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
	      $dataEBTD="ETAPE_DOUBLE_COMMANDE_ID=".$NEXT_ETAPE_ID;
	      $this->update_all_table($tableEBTD,$dataEBTD,$whereEBTD);
				############################################################
		  }

		  $table2 = 'execution_budgetaire_tache_detail_histo';
		  $columsinsert2="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, USER_ID,ETAPE_DOUBLE_COMMANDE_ID, OBSERVATION, DATE_TRANSMISSION, DATE_RECEPTION";
		  $datacolumsinsert2=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.", ".$USER_ID.", ".$ETAPE_ID.", '".$OBSERVATION."','".$DATE_TRANSMISSION."', '".$DATE_RECEPTION."'";
		  $this->save_all_table($table2,$columsinsert2,$datacolumsinsert2);

		  if($BUDGETAIRE_TYPE_DOCUMENT_ID==2 && $TYPE_OPERATION_ID == 2)
		  {
		  	$typ_exec = $this->getBindParms('EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID', 'execution_budgetaire', 'EXECUTION_BUDGETAIRE_ID = '.$EXECUTION_BUDGETAIRE_ID.'' , ' EXECUTION_BUDGETAIRE_ID DESC');
	      $typ_exec = $this->ModelPs->getRequeteOne($psgetrequete, $typ_exec);
	      $provenance=2;
	      $url ='';
	      if($typ_exec['EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID']==1) 
	      {
	        $url = base_url('double_commande_new/Generate_Note/generate_note/'.md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID).'/'.$provenance);
	      }
	      else
	      {
	        $url = base_url('double_commande_new/Generate_Note/generate_note_plusieur/'.md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID).'/'.$provenance);
	      }        
	    
	      return redirect()->to($url);
		  }
		  elseif ($TYPE_OPERATION_ID == 2 && $get_exec['MARCHE_PUBLIQUE']==1)
		  {
		  	$typ_exec = $this->getBindParms('EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID', 'execution_budgetaire', 'EXECUTION_BUDGETAIRE_ID = '.$EXECUTION_BUDGETAIRE_ID.'' , ' EXECUTION_BUDGETAIRE_ID DESC');
	      $typ_exec = $this->ModelPs->getRequeteOne($psgetrequete, $typ_exec);
	      $provenance=2;
	      $url ='';
	      if($typ_exec['EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID']==1) 
	      {
	        $url = base_url('double_commande_new/Generate_Note/generate_note/'.md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID).'/'.$provenance);
	      }
	      else
	      {
	        $url = base_url('double_commande_new/Generate_Note/generate_note_plusieur/'.md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID).'/'.$provenance);
	      }        
	    
	      return redirect()->to($url);
		  }
		  else
		  {
		  	$data=['message' => "".lang('messages_lang.message_success').""];
		  	session()->setFlashdata('alert', $data);
		  	return redirect('double_commande_new/Liquidation_Double_Commande/get_liquid_valider');
		  }
		}
		else
		{
			$dist=2;
			return $this->getOne_conf(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),$dist);
		}		
	}
	
	// update data liquidation
	public function update($value='')
	{
		$db = db_connect();

		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($gdc!=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$rules = [
			'ID_TYPE_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_RECEPTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'TITRE_CREANCE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_CREANCE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'MOTIF_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'TAUX_TVA_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'EXONERATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'DATE_TRANSMISSION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'TYPE_ANALYSE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			]      
		];

		$TYPE_MONTANT_ID = $this->request->getPost('TYPE_MONTANT_ID');
		$MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
		$BUDGETAIRE_TYPE_DOCUMENT_ID = $this->request->getPost('BUDGETAIRE_TYPE_DOCUMENT_ID');

		if ($TYPE_MONTANT_ID != 1)
		{
			$rules['MONTANT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['LIQUIDATION'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['COUT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['DATE_COUT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];       
		}
		else
		{
			$rules['MONTANT_CREANCE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
		}

		if ($MARCHE_PUBLIQUE == 1)
		{
			$rules['DATE_LIVRAISON_CONTRAT'] = [
			'label' => '',
			'rules' => 'required',
			'errors' => [
				'required' => 'Le champ est obligatoire'
				]
			];
		}

		if ($BUDGETAIRE_TYPE_DOCUMENT_ID == 2)
		{
			$rules['intro_note'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire'
				]
			];
		}

		$this->validation->setRules($rules);
		$EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
		if($this->validation->withRequest($this->request)->run())
		{
			$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
			$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
			$TITRE_CREANCE = $this->request->getPost('TITRE_CREANCE');
			$DATE_CREANCE = $this->request->getPost('DATE_CREANCE');
			$MONTANT_CREANCE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_CREANCE'));
			$DATE_LIQUIDATION = $this->request->getPost('DATE_LIQUIDATION');
			$MOTIF_LIQUIDATION = str_replace("'", "\'", $this->request->getPost('MOTIF_LIQUIDATION'));
			$MONTANT_DEVISE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_DEVISE'));
			$TAUX_TVA_ID = $this->request->getPost('TAUX_TVA_ID');
			$EXONERATION = $this->request->getPost('EXONERATION');
			$ID_TYPE_LIQUIDATION = $this->request->getPost('ID_TYPE_LIQUIDATION');
			$MONTANT_RACCROCHE_JURIDIQUE_VALUE = $this->request->getPost('MONTANT_RACCROCHE_JURIDIQUE_VALUE');
			$MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE = $this->request->getPost('MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE');  	
			$DATE_LIVRAISON_CONTRAT = $this->request->getPost('DATE_LIVRAISON_CONTRAT');
			$LIQUIDATION = preg_replace('/\s/', '', $this->request->getPost('LIQUIDATION'));
			$intro_note=$this->request->getPost('intro_note');
			$intro_note=addslashes($intro_note);
			$COUT_DEVISE=$this->request->getPost('COUT_DEVISE');
			$COUT_DEVISE=str_replace(' ','',$COUT_DEVISE);
			$DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');

		  	//on fait l'update dans infos sup si marche c'est marché publique
			if ($MARCHE_PUBLIQUE==1)
			{
		  		######################## upload file ##############################
				if (!empty($_FILES['PATH_PV_RECEPTION_LIQUIDATION']['tmp_name']))
				{
					$PATH_PV_RECEPTION_LIQUIDATION = $this->request->getPost('PATH_PV_RECEPTION_LIQUIDATION');
					$PATH_PV_RECEPTION=$this->uploadFile('PATH_PV_RECEPTION_LIQUIDATION','double_commande_new',$PATH_PV_RECEPTION_LIQUIDATION);
				}
				else
				{
					$PATH_PV_RECEPTION = $this->request->getPost('PATH_PV_RECEPTION_LIQUIDATION_OUP');
				}  		
		    	######################## upload file ##############################

		  		//mise a jour dans la table execution_budgetaire_raccrochage_activite_info_suppl
				$table = 'execution_budgetaire_tache_info_suppl';
				$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$data='DATE_LIVRAISON_CONTRAT="'.$DATE_LIVRAISON_CONTRAT.'"';
				$this->update_all_table($table,$data,$where);
		      ##################################################################################

		      //mise a jour dans la table execution_budgetaire_raccrochage_activite_detail
				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1='PATH_PV_RECEPTION_LIQUIDATION="'.$PATH_PV_RECEPTION.'"';
				$this->update_all_table($table1,$data1,$where1);
			}

			if($BUDGETAIRE_TYPE_DOCUMENT_ID==2)
			{
				if (!empty($_FILES['PATH_FACTURE_LIQUIDATION']['tmp_name']))
				{
					$PATH_FACTURE_LIQUIDATION = $this->request->getPost('PATH_FACTURE_LIQUIDATION');
					$PATH_FACTURE_LIQUIDATION=$this->uploadFile('PATH_FACTURE_LIQUIDATION','double_commande_new',$PATH_FACTURE_LIQUIDATION);
				}
				else
				{
					$PATH_FACTURE_LIQUIDATION = $this->request->getPost('PATH_FACTURE_LIQUIDATION_OUP');
				}

		      //mise a jour dans la table execution_budgetaire_raccrochage_activite_detail
				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1='PATH_FACTURE_LIQUIDATION="'.$PATH_FACTURE_LIQUIDATION.'",INTRODUCTION_NOTE="'.$intro_note.'"';
				$this->update_all_table($table1,$data1,$where1);
			}

			$MONTANT_DEVISE=!empty($MONTANT_DEVISE)?$MONTANT_DEVISE:0;
			$EXONERATION=!empty($EXONERATION)?$EXONERATION:0;

			$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

				//mise a jour dans la table execution_budgetaire
			$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
			$ETAPE_ID = $this->request->getPost('ETAPE_ID');
				//récuperer les etapes et mouvements
			$psgetrequete = "CALL `getRequete`(?,?,?,?);";
			$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID, MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
			$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
			$MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];
		  	// MOUVEMENT_DEPENSE_ID quité

			$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.'','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
			$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
			$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
		  	//etape suivant

			$next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
			$get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);
			$MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
		  	// mouve qui va suivre
		  	#############################################################
			$tableLiquidTermin = 'execution_budgetaire';
			$whereLiquidTermin='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
			$dataLiquidTermin='IS_FINISHED=1 ';

		  	//cas ou type montant est dollar, ero....
			if ($TYPE_MONTANT_ID!=1)
			{
				$LIQUIDATION = $LIQUIDATION;
					//cas partiel
				if ($ID_TYPE_LIQUIDATION==1)
				{
						//paiement terminé
					if ($MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE==$MONTANT_DEVISE)
					{
						$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
					}
				}
				else
				{
					$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
				}
					###############################################################################################
		  		//get devise a jour
		  		// $devise_a_jour = $this->getBindParms('DEVISE_TYPE_HISTO_ID','devise_type_hist','IS_ACTIVE=1 AND DEVISE_TYPE_ID='.$TYPE_MONTANT_ID,'1');
		  		// $devise_a_jour = $this->ModelPs->getRequeteOne($psgetrequete, $devise_a_jour);
		  		// $DEVISE_TYPE_HISTO_LIQUI_ID =  $devise_a_jour['DEVISE_TYPE_HISTO_ID'];

				$DEVISE_TYPE_HISTO_LIQUI_ID	="";

				$taux_exist = "SELECT DEVISE_TYPE_HISTO_ID,DEVISE_TYPE_ID,TAUX,DATE_INSERTION FROM devise_type_hist WHERE DEVISE_TYPE_ID=".$TYPE_MONTANT_ID." AND TAUX=".$COUT_DEVISE." AND DATE_INSERTION LIKE '$DATE_COUT_DEVISE%'";
				$taux_exist='CALL `getTable`("'.$taux_exist.'")';
				$taux_exist= $this->ModelPs->getRequeteOne($taux_exist);
				if(!empty($taux_exist))
				{
					$DEVISE_TYPE_HISTO_LIQUI_ID=$taux_exist["DEVISE_TYPE_HISTO_ID"];
				}
				else
				{
					$columns="DEVISE_TYPE_ID,TAUX,IS_ACTIVE,DATE_INSERTION";
					$data_col=$TYPE_MONTANT_ID.",".$COUT_DEVISE.",0,'".$DATE_COUT_DEVISE."'";
					$table_dev="devise_type_hist";
					$DEVISE_TYPE_HISTO_LIQUI_ID =$this->save_all_table($table_dev,$columns,$data_col);
				}

				$table = 'execution_budgetaire';
				$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$data='LIQUIDATION_DEVISE='.$MONTANT_DEVISE.', LIQUIDATION='.$LIQUIDATION.', LIQUIDATION_TYPE_ID='.$ID_TYPE_LIQUIDATION;
				$this->update_all_table($table,$data,$where);
					###############################################   ###########################

				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1="MONTANT_LIQUIDATION_DEVISE=".$MONTANT_DEVISE.",MONTANT_LIQUIDATION='".$LIQUIDATION."', TITRE_CREANCE='".$TITRE_CREANCE."', DATE_CREANCE='".$DATE_CREANCE."', DATE_LIQUIDATION='".$DATE_LIQUIDATION."', MOTIF_LIQUIDATION='".$MOTIF_LIQUIDATION."',COUR_DEVISE='".$COUT_DEVISE."',DATE_COUR_DEVISE='".$DATE_COUT_DEVISE."',DEVISE_TYPE_HISTO_LIQUI_ID=".$DEVISE_TYPE_HISTO_LIQUI_ID."";
				$this->update_all_table($table1,$data1,$where1);

				//Enregistrement dans execution_budgetaire_titre_decaissement
				$tableEBTD='execution_budgetaire_titre_decaissement';
				$conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
				$datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
				$this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);
			}
			else
			{
				$LIQUIDATION = $MONTANT_CREANCE;
					//cas partiel
				if ($ID_TYPE_LIQUIDATION==1)
				{
						//paiement terminé
					if ($MONTANT_RACCROCHE_JURIDIQUE_VALUE==$MONTANT_CREANCE)
					{
						$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
					}
				}
				else
				{
					$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
				}

				$table = 'execution_budgetaire';
				$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$data='LIQUIDATION='.$MONTANT_CREANCE.', LIQUIDATION_TYPE_ID='.$ID_TYPE_LIQUIDATION;
				$this->update_all_table($table,$data,$where);
					#############################################

				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1="MONTANT_CREANCE=".$MONTANT_CREANCE.", MONTANT_LIQUIDATION=".$MONTANT_CREANCE.", TITRE_CREANCE='".$TITRE_CREANCE."', DATE_CREANCE='".$DATE_CREANCE."', DATE_LIQUIDATION='".$DATE_LIQUIDATION."', MOTIF_LIQUIDATION='".$MOTIF_LIQUIDATION."'";
				$this->update_all_table($table1,$data1,$where1);

		  		//Enregistrement dans execution_budgetaire_titre_decaissement
				$tableEBTD='execution_budgetaire_titre_decaissement';
				$conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
				$datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
	            // print_r($datatomodifieEBTD);die();
				$this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

					##########################################################################
			}

			//Mise à jours dans la table historique_raccrochage 
			$OBSERVATION = str_replace("'", "\'", $this->request->getPost('OBSERVATION'));
			$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
			$DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
			$TYPE_RACCROCHAGE_ID = 2;//double commande

			$table2 = 'execution_budgetaire_tache_detail_histo';
			$columsinsert2="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, USER_ID,ETAPE_DOUBLE_COMMANDE_ID, OBSERVATION, DATE_TRANSMISSION, DATE_RECEPTION";
			$datacolumsinsert2=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.", ".$USER_ID.", ".$ETAPE_ID.", '".$OBSERVATION."','".$DATE_TRANSMISSION."', '".$DATE_RECEPTION."'";
			$this->save_all_table($table2,$columsinsert2,$datacolumsinsert2);

		    ######################################################################################

			$table = 'execution_budgetaire_tache_detail';
			$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
			$data='TAUX_TVA_ID='.$TAUX_TVA_ID.', EXONERATION='.$EXONERATION.'';
			$this->update_all_table($table,$data,$where);

		    // mise à jour dans la table historique_raccrochage_operation_verification
		    ##################### delete ###########################################
			$table5 = 'execution_budgetaire_histo_operation_verification';
			$deleteRequete3='DELETE FROM execution_budgetaire_histo_operation_verification WHERE execution_budgetaire_histo_operation_verification.EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' AND TYPE_ANALYSE_ID IN(SELECT budgetaire_type_analyse.BUDGETAIRE_TYPE_ANALYSE_ID FROM budgetaire_type_analyse WHERE budgetaire_type_analyse.MOUVEMENT_DEPENSE_ID=3)';
			$this->ModelPs->getRequete("CALL `getTable`('" . $deleteRequete3 . "')");
	      	########################   insert  #####################################
			$TYPE_ANALYSE_ID = $this->request->getPost('TYPE_ANALYSE_ID[]');

			foreach ($TYPE_ANALYSE_ID as $key)
			{
				$columsinsert="TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
				$datacolumsinsert=$key.", ".$ETAPE_ID.", ".$EXECUTION_BUDGETAIRE_ID." ";
				$this->save_all_table($table5,$columsinsert,$datacolumsinsert);
			}
	  		#############################		
			$data=['message' => "".lang('messages_lang.labelle_message_update_success').""];
			session()->setFlashdata('alert', $data);
			return redirect('double_commande_new/Liquidation_Double_Commande/get_liquid_Afaire');
		}
		else
		{
			$dist=3;
			return $this->getOne_corriger(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),$dist);
		}  	
	}

	//récupération du sous tutelle par rapport à l'institution
	public function getSousTutel()
	{
		$session  = \Config\Services::session();
		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
		if($gdc!=1 AND $ced!=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

		$sql_institution='SELECT inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL,inst_institutions_sous_tutel.SOUS_TUTEL_ID FROM inst_institutions_sous_tutel WHERE 1 AND inst_institutions_sous_tutel.INSTITUTION_ID ='.$INSTITUTION_ID.' ';
		$sous_tutel = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");
		

		$tutel="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($sous_tutel as $key)
		{
			$tutel.= "<option value ='".$key->SOUS_TUTEL_ID."'>".$key->CODE_SOUS_TUTEL."-".$key->DESCRIPTION_SOUS_TUTEL."</option>";
		}
		$output = array("tutel"=>$tutel);
		return $this->response->setJSON($output);
	}

	//Interface de la liste des liquidation à rejeter
	function get_liquidation_rejeter($id=0)
	{
		$data = $this->urichk();
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$session  = \Config\Services::session();
		$user_id ='';
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return  redirect('Login_Ptba/do_logout');
		}

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
		if($gdc!=1 AND $ced!=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];

		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
		$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];
		$data['nbr_from_ord']=$data_menu['nbr_from_ord'];

		return view('App\Modules\double_commande_new\Views\Liste_Liquidation_Rejeter_View',$data);
	}

	//fonction pour affichage d'une liste de la liquidation à rejeter
	public function listing_liquidation_rejeter()
	{
		$session  = \Config\Services::session();
		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');

		if($gdc!=1 AND $ced!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

    	//Filtres de la liste
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
		$ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
		$getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

		$ID_INST='';
		foreach ($getaffect as $value)
		{
			$ID_INST.=$value->INSTITUTION_ID.' ,';           
		}

		$ID_INST = substr($ID_INST,0,-1);

		$critere1="";
		$critere2=" ";
		$critere3="";
		if(!empty($INSTITUTION_ID))
		{
			$critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$critere3=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$group = "";
		$requetedebase="SELECT DISTINCT(titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),exec.EXECUTION_BUDGETAIRE_ID,
		dev.DEVISE_TYPE_ID,
		dev.DESC_DEVISE_TYPE,
		exec.ENG_BUDGETAIRE,
		exec.ENG_BUDGETAIRE_DEVISE,
		exec.ENG_BUDGETAIRE_DEVISE,
		exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,
		det.MONTANT_LIQUIDATION LIQUIDATION,
        det.MONTANT_LIQUIDATION_DEVISE LIQUIDATION_DEVISE,
		ligne.CODE_NOMENCLATURE_BUDGETAIRE,
		ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
		titre.ETAPE_DOUBLE_COMMANDE_ID,
		exec.NUMERO_BON_ENGAGEMENT 
		FROM execution_budgetaire exec 
		JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
		JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID 
		JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID 
		JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID  
		WHERE titre.ETAPE_DOUBLE_COMMANDE_ID = 13 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN(".$ID_INST.")";

		$order_by = '';
		$order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

		$search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE  LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE  LIKE '%$var_search%')"):'';

		$critaire = $critere1." ".$critere3." ".$critere2;
    	//condition pour le query principale
		$conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    	// condition pour le query filter
		$conditionsfilter = $critaire . " ". $search ." " . $group;

		$requetedebases=$requetedebase." ".$conditions;

		$requetedebasefilter=$requetedebase." ".$conditionsfilter;

		$query_secondaire = 'CALL getTable("'.$requetedebases.'");';

		$fetch_actions = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u=1;
		foreach ($fetch_actions as $row)
		{
			$et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
			$getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;

			$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
			$callpsreq = "CALL getRequete(?,?,?,?);";
			$user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm,'PROFIL_ID DESC');
			$getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

			$number=$row->NUMERO_BON_ENGAGEMENT;
			if(!empty($getProfil))
			{
				foreach ($getProfil as $value)
				{
					if($prof_id == $value->PROFIL_ID || $prof_id==1)
					{
						$number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

						$bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-arrow-up'></span></a>";
					}
				}
			}

      		//Nombre des tâches
			$count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
			$count_task = 'CALL `getTable`("'.$count_task.'");';
			$nbre_task = $this->ModelPs->getRequeteOne($count_task);

			$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
			$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

			$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
			$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

			$LIQUIDATION=floatval($row->LIQUIDATION);
			$LIQUIDATION=number_format($LIQUIDATION,0,","," ");

			if($row->DEVISE_TYPE_ID!=1)
			{
				$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
				$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

				$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
				$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

				$LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
				$LIQUIDATION=number_format($LIQUIDATION,4,","," ");
			}

			$action='';
			$sub_array = array();
			$sub_array[] = $number;
			$sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
			$point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
			$sub_array[] = $point;               
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $ENG_BUDGETAIRE;
			$sub_array[] = $ENG_JURIDIQUE;
			$sub_array[] = $LIQUIDATION;
			$action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
			$action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
			$sub_array[] = $action;
			$data[] = $sub_array;
		}

		$recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
		$recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data,
		);

		return $this->response->setJSON($output);
	}

  	// insert data partiel
  	public function add_partiel($value='')
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$rules = [
			'DATE_RECEPTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'TITRE_CREANCE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_CREANCE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'MOTIF_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'TAUX_TVA_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'EXONERATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'DATE_TRANSMISSION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'TYPE_ANALYSE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			]      
		];

		$TYPE_MONTANT_ID = $this->request->getPost('TYPE_MONTANT_ID');
		$MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
		$BUDGETAIRE_TYPE_DOCUMENT_ID = $this->request->getPost('BUDGETAIRE_TYPE_DOCUMENT_ID');

		if ($TYPE_MONTANT_ID != 1)
		{
			$rules['MONTANT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['LIQUIDATION'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['COUT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['DATE_COUT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];      

			$infoTaches=$this->request->getPost("infoTaches");
			$infoTaches = json_decode($infoTaches, true);

			foreach($infoTaches as $key)
			{
				$liquidTache=$this->request->getPost("MONTANT_DEVISE_".$key['EBET_ID']);
				if($liquidTache > ($key['TACHE_JURIDIQUE_DEVISE'] - $key['TACHE_LIQUIDATION_DEVISE']))
				{
					$rules['MONTANT_DEVISE_'.$key["EBET_ID"]] = [
						'label' => '',
						'rules' => 'required',
						'errors' => [
							'required' => '<font style="color:red;size:2px;">Montant invalide</font>'
						]
					];
				}
			} 
		}
		else
		{
			$rules['MONTANT_CREANCE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];

			$infoTaches=$this->request->getPost("infoTaches");
			$infoTaches = json_decode($infoTaches, true);

			foreach($infoTaches as $key)
			{
				$liquidTache=$this->request->getPost("MONTANT_".$key['EBET_ID']);
				if($liquidTache > ($key['TACHE_JURIDIQUE'] - $key['TACHE_LIQUIDATION']))
				{
					$rules['MONTANT_'.$key["EBET_ID"]] = [
						'label' => '',
						'rules' => 'required',
						'errors' => [
							'required' => '<font style="color:red;size:2px;">Montant invalide</font>'
						]
					];
				}
			}
		}

		if ($MARCHE_PUBLIQUE == 1)
		{
			$rules['DATE_LIVRAISON_CONTRAT'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
		}

		if ($BUDGETAIRE_TYPE_DOCUMENT_ID == 2)
		{
			$rules['intro_note'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
		}

		$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
		$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
		{
			$TITRE_CREANCE = $this->request->getPost('TITRE_CREANCE');
			$DATE_CREANCE = $this->request->getPost('DATE_CREANCE');
			$MONTANT_CREANCE = $this->request->getPost('MONTANT_CREANCE');
			$DATE_LIQUIDATION = $this->request->getPost('DATE_LIQUIDATION');
			$MOTIF_LIQUIDATION = str_replace("'", "\'", $this->request->getPost('MOTIF_LIQUIDATION'));
			$MONTANT_DEVISE = $this->request->getPost('MONTANT_DEVISE');
			$TAUX_TVA_ID = $this->request->getPost('TAUX_TVA_ID');
			$EXONERATION = $this->request->getPost('EXONERATION');
			$ID_TYPE_LIQUIDATION = $this->request->getPost('ID_TYPE_LIQUIDATION');
			$MONTANT_RACCROCHE_JURIDIQUE_VALUE = $this->request->getPost('MONTANT_RACCROCHE_JURIDIQUE_VALUE');
			$MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE = $this->request->getPost('MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE');
			$DATE_LIVRAISON_CONTRAT = $this->request->getPost('DATE_LIVRAISON_CONTRAT');
			$LIQUIDATION = preg_replace('/\s/', '', $this->request->getPost('LIQUIDATION'));
			$LIQUIDATION = str_replace(',', '', $LIQUIDATION);
			$COUT_DEVISE=$this->request->getPost('COUT_DEVISE');
			$COUT_DEVISE=str_replace(' ','',$COUT_DEVISE);
			$DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');

			$PATH_FACTURE='';
			$PATH_PV_RECEPTION='';
			$INTRODUCTION_NOTE='';
			
			if($BUDGETAIRE_TYPE_DOCUMENT_ID==2)
			{
				$PATH_FACTURE_LIQUIDATION = $this->request->getPost('PATH_FACTURE_LIQUIDATION');
				$PATH_FACTURE=$this->uploadFile('PATH_FACTURE_LIQUIDATION','double_commande_new',$PATH_FACTURE_LIQUIDATION);
				$INTRODUCTION_NOTE = $this->request->getPost('intro_note');

			}

		  //on fait l'update dans infos sup si marche c'est marché publique
			if ($MARCHE_PUBLIQUE==1)
			{
		  	######################## upload file ##############################
				$PATH_PV_RECEPTION_LIQUIDATION=$this->request->getPost('PATH_PV_RECEPTION_LIQUIDATION');
				$PATH_PV_RECEPTION=$this->uploadFile('PATH_PV_RECEPTION_LIQUIDATION','double_commande_new',$PATH_PV_RECEPTION_LIQUIDATION);
			  ######################## upload file ##############################
			}

		  ##################################################################################
			$MONTANT_DEVISE=!empty($MONTANT_DEVISE)?$MONTANT_DEVISE:0;
			$EXONERATION=!empty($EXONERATION)?$EXONERATION:0;

			$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
			$ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

		  //récuperer les etapes et mouvements
			$psgetrequete = "CALL `getRequete`(?,?,?,?);";
			$step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
			$get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
		  $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];// MOUVEMENT_DEPENSE_ID quité

		  $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.'','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
		  $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
		  $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant

		  $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
		  $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);
		  $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
		  // mouve qui va suivre
		  ##########################################################################

			//mise a jour dans la table execution_budgetaire
		  $tableLiquidTermin = 'execution_budgetaire';
		  $whereLiquidTermin='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
		  $dataLiquidTermin='IS_FINISHED=1';
			####################################################################

			//recuperation des donnees pour la partielle precedente
		  $det='SELECT exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,exec.TRIMESTRE_ID,exec.DATE_DEMANDE AS DATE_ENGAGEMENT_BUDGETAIRE,exec.DATE_ENG_JURIDIQUE AS DATE_ENGAGEMENT_JURIDIQUE,det.COUR_DEVISE,det.DATE_COUR_DEVISE,ebet.QTE,exec.ANNEE_BUDGETAIRE_ID,ebet.UNITE,exec.COMMENTAIRE FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE exec.EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' ORDER BY det.EXECUTION_BUDGETAIRE_DETAIL_ID DESC LIMIT 1';
		  $getdet = "CALL `getTable`('" . $det . "');";
		  $get_donDet= $this->ModelPs->getRequeteOne($getdet);

		  $UNITE = str_replace("'","\'",$get_donDet['UNITE']);
		  $COMMENTAIRE = str_replace("'","\'",$get_donDet['COMMENTAIRE']);

			//cas ou type montant est dollar, ero....
		  if ($TYPE_MONTANT_ID!=1)
		  {
		  	$MONTANT_DEVISE_LIQUIDATION = $this->request->getPost('MONTANT_DEVISE_LIQUIDATION');
		  	$MONTANT_RACCROCHE_LIQUIDATION=$this->request->getPost('MONTANT_RACCROCHE_LIQUIDATION');
		  	$SOMME_MONTANT_DEVISE = $MONTANT_DEVISE+$MONTANT_DEVISE_LIQUIDATION;
		  	$SOMME_LIQUIDATION=$LIQUIDATION+$MONTANT_RACCROCHE_LIQUIDATION;
		  	$SOMME_LIQUIDATION=number_format(floatval($SOMME_LIQUIDATION),0, ',', '');

				//cas partiel //paiement terminé
		  	if ($MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE==$SOMME_MONTANT_DEVISE)
		  	{
		  		$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
		  	}

		  	$table = 'execution_budgetaire';
		  	$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
		  	$data='LIQUIDATION_DEVISE='.$SOMME_MONTANT_DEVISE.',LIQUIDATION="'.$SOMME_LIQUIDATION.'"';
		  	$this->update_all_table($table,$data,$where);

				//recuperation de l'engagement juridik et budgetaire
		  	$DEVISE_TYPE_HISTO_LIQUI_ID	="";		  
		  	$taux_exist = "SELECT DEVISE_TYPE_HISTO_ID,DEVISE_TYPE_ID,TAUX,DATE_INSERTION FROM devise_type_hist WHERE DEVISE_TYPE_ID=".$TYPE_MONTANT_ID." AND TAUX=".$COUT_DEVISE." AND DATE_INSERTION LIKE '$DATE_COUT_DEVISE%'";
		  	$taux_exist='CALL `getTable`("'.$taux_exist.'")';
		  	$taux_exist= $this->ModelPs->getRequeteOne($taux_exist);
		  	if(!empty($taux_exist))
		  	{
		  		$DEVISE_TYPE_HISTO_LIQUI_ID=$taux_exist["DEVISE_TYPE_HISTO_ID"];
		  	}
		  	else
		  	{
		  		$columns="DEVISE_TYPE_ID,TAUX,IS_ACTIVE,DATE_INSERTION";
		  		$data_col=$TYPE_MONTANT_ID.",".$COUT_DEVISE.",0,'".$DATE_COUT_DEVISE."'";
		  		$table_dev="devise_type_hist";
		  		$DEVISE_TYPE_HISTO_LIQUI_ID =$this->save_all_table($table_dev,$columns,$data_col);
		  	}

		  	$table1 = 'execution_budgetaire_tache_detail';
		  	$columsinsert1="EXECUTION_BUDGETAIRE_ID, MONTANT_LIQUIDATION_DEVISE, MONTANT_LIQUIDATION,DEVISE_TYPE_HISTO_LIQUI_ID, TITRE_CREANCE, DATE_CREANCE, DATE_LIQUIDATION, MOTIF_LIQUIDATION,PATH_PV_RECEPTION_LIQUIDATION,PATH_FACTURE_LIQUIDATION,COUR_DEVISE,DATE_COUR_DEVISE,COMMENTAIRE,INTRODUCTION_NOTE";
		  	$datacolumsinsert1=$EXECUTION_BUDGETAIRE_ID.", ".$MONTANT_DEVISE.", '".$LIQUIDATION."',".$DEVISE_TYPE_HISTO_LIQUI_ID.",'".$TITRE_CREANCE."', '".$DATE_CREANCE."', '".$DATE_LIQUIDATION."', '".$MOTIF_LIQUIDATION."','".$PATH_PV_RECEPTION."','".$PATH_FACTURE."','".$COUT_DEVISE."','".$DATE_COUT_DEVISE."','".$COMMENTAIRE."','".$INTRODUCTION_NOTE."'";
		  	$id_det=$this->save_all_table($table1,$columsinsert1,$datacolumsinsert1);

		  	//inserer le nouveau TD ID
		  	$tableEBTD = 'execution_budgetaire_titre_decaissement';
		  	$columsinsertEBTD="EXECUTION_BUDGETAIRE_ID, EXECUTION_BUDGETAIRE_DETAIL_ID, ETAPE_DOUBLE_COMMANDE_ID";
		  	$datacolumsinsertEBTD=$EXECUTION_BUDGETAIRE_ID.", ".$id_det.", ".$NEXT_ETAPE_ID;
		  	$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->save_all_table($tableEBTD,$columsinsertEBTD,$datacolumsinsertEBTD);

		  	//update execution_bugetaire_execution_tache
		  	foreach ($infoTaches as $infoTache) {
		  		$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
		  		$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
		  		$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;

		  		$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
		  		$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
		  		$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

		  		//save les montants de chaque tache
		  		$columnsEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE";
		  		$dataEBETD=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$infoTache['EBET_ID'].",".$MONTANT_EBET.",".$MONTANT_DEVISE_EBET."";
		  		$tableEBETD="execution_budgetaire_execution_tache_detail";
		  		$resEBETD =$this->save_all_table($tableEBETD,$columnsEBETD,$dataEBETD);

		  		//additionner les montants des taches avant l'insertion
		  		$MONTANT_DEVISE_EBET = $MONTANT_DEVISE_EBET + $infoTache['TACHE_LIQUIDATION_DEVISE'];
		  		$MONTANT_EBET = $MONTANT_DEVISE_EBET * $COUT_DEVISE;
		  		$MONTANT_EBET = $MONTANT_EBET + $infoTache['TACHE_LIQUIDATION'];

		  		$tableEBET='execution_budgetaire_execution_tache';
		  		$conditionsEBET='EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ='.$infoTache['EBET_ID'];
		  		$datatomodifieEBET= 'MONTANT_LIQUIDATION='.$MONTANT_EBET.', MONTANT_LIQUIDATION_DEVISE='.$MONTANT_DEVISE_EBET;
		  		$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);
		  	}

		  }
		  else
		  {
		  	$MONTANT_RACCROCHE_LIQUIDATION = $this->request->getPost('MONTANT_RACCROCHE_LIQUIDATION');
		  	$SOMME_MONTANT_CREANCE = floatval($MONTANT_CREANCE)+floatval($MONTANT_RACCROCHE_LIQUIDATION);
		  	$SOMME_LIQUIDATION = $SOMME_MONTANT_CREANCE;
		  	// $LIQUIDATION=$SOMME_LIQUIDATION;

				//cas partiel //paiement terminé
		  	if ($MONTANT_RACCROCHE_JURIDIQUE_VALUE==$SOMME_MONTANT_CREANCE)
		  	{
		  		$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
		  	}

		  	$table = 'execution_budgetaire';
		  	$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
		  	$data='LIQUIDATION='.$SOMME_MONTANT_CREANCE;
		  	$this->update_all_table($table,$data,$where);
				#################################

				//recuperation de l'engagement juridik et budgetaire
		  	$table1 = 'execution_budgetaire_tache_detail';
		  	$columsinsert1="EXECUTION_BUDGETAIRE_ID,MONTANT_CREANCE, MONTANT_LIQUIDATION, TITRE_CREANCE, DATE_CREANCE, DATE_LIQUIDATION, MOTIF_LIQUIDATION,PATH_PV_RECEPTION_LIQUIDATION,PATH_FACTURE_LIQUIDATION,COUR_DEVISE,DATE_COUR_DEVISE,COMMENTAIRE,INTRODUCTION_NOTE";
		  	$datacolumsinsert1=$EXECUTION_BUDGETAIRE_ID.", ".$MONTANT_CREANCE.", ".$MONTANT_CREANCE.", '".$TITRE_CREANCE."', '".$DATE_CREANCE."', '".$DATE_LIQUIDATION."', '".$MOTIF_LIQUIDATION."','".$PATH_PV_RECEPTION."','".$PATH_FACTURE."',1,'null','".$COMMENTAIRE."','".$INTRODUCTION_NOTE."'";
		  	$id_det=$this->save_all_table($table1,$columsinsert1,$datacolumsinsert1);

		  	//Enregistrement dans execution_budgetaire_titre_decaissement
		  	//inserer le nouveau TD ID
		  	$tableEBTD = 'execution_budgetaire_titre_decaissement';
		  	$columsinsertEBTD="EXECUTION_BUDGETAIRE_ID, EXECUTION_BUDGETAIRE_DETAIL_ID, ETAPE_DOUBLE_COMMANDE_ID";
		  	$datacolumsinsertEBTD=$EXECUTION_BUDGETAIRE_ID.", ".$id_det.", ".$NEXT_ETAPE_ID;
		  	$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->save_all_table($tableEBTD,$columsinsertEBTD,$datacolumsinsertEBTD);

		  	//update execution_bugetaire_execution_tache
		  	foreach ($infoTaches as $infoTache) 
		  	{
		  		$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
		  		$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
		  		$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;

		  		$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
		  		$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
		  		$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

		  		//save les montants de chaque tache
		  		$columnsEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,MONTANT_LIQUIDATION,MONTANT_LIQUIDATION_DEVISE";
		  		$dataEBETD=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$infoTache['EBET_ID'].",".$MONTANT_EBET.",".$MONTANT_DEVISE_EBET."";
		  		$tableEBETD="execution_budgetaire_execution_tache_detail";
		  		$resEBETD =$this->save_all_table($tableEBETD,$columnsEBETD,$dataEBETD);

		  		//additionner les montants des taches avant l'insertion
		  		$MONTANT_EBET = $MONTANT_EBET + $infoTache['TACHE_LIQUIDATION'];
		  		$MONTANT_DEVISE_EBET = $MONTANT_DEVISE_EBET + $infoTache['TACHE_LIQUIDATION_DEVISE'];

		  		$tableEBET='execution_budgetaire_execution_tache';
		  		$conditionsEBET='EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ='.$infoTache['EBET_ID'];
		  		$datatomodifieEBET= 'MONTANT_LIQUIDATION='.$MONTANT_EBET.', MONTANT_LIQUIDATION_DEVISE='.$MONTANT_DEVISE_EBET;
		  		$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);
		  	}

		  }
			//insert dans la table historique_raccrochage
		  $OBSERVATION = str_replace("'", "\'", $this->request->getPost('OBSERVATION'));
		  $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
		  $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
		  $TYPE_RACCROCHAGE_ID = 2;

		  $table2 = 'execution_budgetaire_tache_detail_histo';
		  $columsinsert2="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, USER_ID,ETAPE_DOUBLE_COMMANDE_ID,OBSERVATION,DATE_TRANSMISSION, DATE_RECEPTION";
		  $datacolumsinsert2=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.", ".$USER_ID.", ".$ETAPE_ID.", '".$OBSERVATION."', '".$DATE_TRANSMISSION."', '".$DATE_RECEPTION."'";
		  $this->save_all_table($table2,$columsinsert2,$datacolumsinsert2);
	        ##################################################

		  $table = 'execution_budgetaire_tache_detail';
		  $where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
		  $data='TAUX_TVA_ID='.$TAUX_TVA_ID.', EXONERATION='.$EXONERATION.'';
		  $this->update_all_table($table,$data,$where);
		    #############################################################

		    // insert dans la table historique_raccrochage_operation_verification
		  $table5 = 'execution_budgetaire_histo_operation_verification';
		  $TYPE_ANALYSE_ID = $this->request->getPost('TYPE_ANALYSE_ID[]');

		  foreach ($TYPE_ANALYSE_ID as $key)
		  {
		  	$columsinsert="TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
		  	$datacolumsinsert=$key.", ".$ETAPE_ID.", ".$id_det." ";
		  	$this->save_all_table($table5,$columsinsert,$datacolumsinsert);
		  }
		    #################################################
		  
		  $data=['message' => "".lang('messages_lang.message_success').""];
		  session()->setFlashdata('alert', $data);
		  return redirect('double_commande_new/Liquidation_Double_Commande/get_liquid_Afaire');
		}
		else
		{
			$dist=1;
			return $this->getOne_partiel(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),$dist);
		}	  
	}

	// update data liquidation
	public function update_partielle($value='')
	{
		$db = db_connect();

		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		//$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$rules = [
			'DATE_RECEPTION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'TITRE_CREANCE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_CREANCE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire</font>'
				]
			],
			'DATE_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'MOTIF_LIQUIDATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'TAUX_TVA_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'EXONERATION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'DATE_TRANSMISSION' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			],
			'TYPE_ANALYSE_ID' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			]      
		];

		$TYPE_MONTANT_ID = $this->request->getPost('TYPE_MONTANT_ID');
		$MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
		$BUDGETAIRE_TYPE_DOCUMENT_ID = $this->request->getPost('BUDGETAIRE_TYPE_DOCUMENT_ID');

		if ($TYPE_MONTANT_ID != 1)
		{
			$rules['MONTANT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['LIQUIDATION'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['COUT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
			$rules['DATE_COUT_DEVISE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];  

			$infoTaches=$this->request->getPost("infoTaches");
			$infoTaches = json_decode($infoTaches, true);

			foreach($infoTaches as $key)
			{
				$liquidTache=$this->request->getPost("MONTANT_DEVISE_".$key['EBET_ID']);
				if($liquidTache > $key['SINGLE_TACHE_LIQUIDATION_DEVISE'])
				{
					$rules['MONTANT_DEVISE_'.$key["EBET_ID"]] = [
						'label' => '',
						'rules' => 'required',
						'errors' => [
							'required' => '<font style="color:red;size:2px;">Montant invalide</font>'
						]
					];
				}
			} 
		}
		else
		{
			$rules['MONTANT_CREANCE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];

			$rules['MONTANT_CREANCE'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];

			$infoTaches=$this->request->getPost("infoTaches");
			$infoTaches = json_decode($infoTaches, true);

			foreach($infoTaches as $key)
			{
				$liquidTache=$this->request->getPost("MONTANT_".$key['EBET_ID']);
				if($liquidTache > $key['SINGLE_TACHE_LIQUIDATION'])
				{
					$rules['MONTANT_'.$key["EBET_ID"]] = [
						'label' => '',
						'rules' => 'required',
						'errors' => [
							'required' => '<font style="color:red;size:2px;">Montant invalide</font>'
						]
					];
				}
			}
		}

		if ($MARCHE_PUBLIQUE == 1)
		{
			$rules['DATE_LIVRAISON_CONTRAT'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => 'Le champ est obligatoire'
				]
			];
		}

		if ($BUDGETAIRE_TYPE_DOCUMENT_ID == 2)
		{
			$rules['intro_note'] = [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire'
				]
			];
		}

		$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
		$EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
		{
			$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
			$TITRE_CREANCE = $this->request->getPost('TITRE_CREANCE');
			$DATE_CREANCE = $this->request->getPost('DATE_CREANCE');
			$MONTANT_CREANCE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_CREANCE'));
			$DATE_LIQUIDATION = $this->request->getPost('DATE_LIQUIDATION');
			$MOTIF_LIQUIDATION = str_replace("'", "\'", $this->request->getPost('MOTIF_LIQUIDATION'));
			$MONTANT_DEVISE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_DEVISE'));
			$TAUX_TVA_ID = $this->request->getPost('TAUX_TVA_ID');
			$EXONERATION = $this->request->getPost('EXONERATION');

			$ID_TYPE_LIQUIDATION = $this->request->getPost('ID_TYPE_LIQUIDATION');
			$MONTANT_RACCROCHE_JURIDIQUE_VALUE = $this->request->getPost('MONTANT_RACCROCHE_JURIDIQUE_VALUE');
			$MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE = $this->request->getPost('MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE');

			$DATE_LIVRAISON_CONTRAT = $this->request->getPost('DATE_LIVRAISON_CONTRAT');
			$LIQUIDATION = preg_replace('/\s/', '', $this->request->getPost('LIQUIDATION'));
			$COUT_DEVISE=$this->request->getPost('COUT_DEVISE');
			$COUT_DEVISE=str_replace(' ','',$COUT_DEVISE);
			$DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');

		    //on fait l'update dans infos sup si marche c'est marché publique
			if ($MARCHE_PUBLIQUE==1)
			{
		    	######################## upload file ##############################
				if (!empty($_FILES['PATH_PV_RECEPTION_LIQUIDATION']['tmp_name']))
				{
					$PATH_PV_RECEPTION_LIQUIDATION = $this->request->getPost('PATH_PV_RECEPTION_LIQUIDATION');
					$PATH_PV_RECEPTION=$this->uploadFile('PATH_PV_RECEPTION_LIQUIDATION','double_commande_new',$PATH_PV_RECEPTION_LIQUIDATION);
				}
				else
				{
					$PATH_PV_RECEPTION = $this->request->getPost('PATH_PV_RECEPTION_LIQUIDATION_OUP');
				}
			    ######################## upload file ##############################

		    	//mise a jour dans la table execution_budgetaire_tache_detail
				$table = 'execution_budgetaire_tache_detail';
				$where='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data='DATE_LIVRAISON_CONTRAT="'.$DATE_LIVRAISON_CONTRAT.'"';
				$this->update_all_table($table,$data,$where);
		        ##################################################################################

				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1='PATH_PV_RECEPTION_LIQUIDATION="'.$PATH_PV_RECEPTION.'"';
				$this->update_all_table($table1,$data1,$where1);
			}
           $INTRODUCTION_NOTE='';
			if($BUDGETAIRE_TYPE_DOCUMENT_ID==2)
			{
				if (!empty($_FILES['PATH_FACTURE_LIQUIDATION']['tmp_name']))
				{
					$PATH_FACTURE_LIQUIDATION = $this->request->getPost('PATH_FACTURE_LIQUIDATION');
					$PATH_FACTURE_LIQUIDATION=$this->uploadFile('PATH_FACTURE_LIQUIDATION','double_commande_new',$PATH_FACTURE_LIQUIDATION);
				}
				else
				{
					$PATH_FACTURE_LIQUIDATION = $this->request->getPost('PATH_FACTURE_LIQUIDATION_OUP');
				}
				$INTRODUCTION_NOTE = addslashes($this->request->getPost('intro_note'));

	      //mise a jour dans la table execution_budgetaire_raccrochage_activite_detail
				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1='PATH_FACTURE_LIQUIDATION="'.$PATH_FACTURE_LIQUIDATION.'",INTRODUCTION_NOTE="'.$INTRODUCTION_NOTE.'"';
				$this->update_all_table($table1,$data1,$where1);
			}

			$MONTANT_DEVISE=!empty($MONTANT_DEVISE)?$MONTANT_DEVISE:0;
			$EXONERATION=!empty($EXONERATION)?$EXONERATION:0;

			$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

			//mise a jour dans la table execution_budgetaire
			$EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
			$ETAPE_ID = $this->request->getPost('ETAPE_ID');
			//récuperer les etapes et mouvements
			$psgetrequete = "CALL `getRequete`(?,?,?,?);";

			$next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.'','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
			$get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
			$NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
	        //etape suivant

			$tableLiquidTermin = 'execution_budgetaire';
			$whereLiquidTermin='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
			$dataLiquidTermin='IS_FINISHED=1 ';

			$monnaie=$this->request->getPost('monnaie');

	       //cas ou type montant est dollar, ero....
			if ($TYPE_MONTANT_ID!=1)
			{
				$MONTANT_DEVISE_LIQUIDATION = $this->request->getPost('MONTANT_DEVISE_LIQUIDATION');

				$MONTANT_DEVISE_LIQUIDATION = floatval($MONTANT_DEVISE_LIQUIDATION) - floatval($monnaie);

				$SOMME_MONTANT_DEVISE = $MONTANT_DEVISE+$this->request->getPost('LIQUIDATION_TOTALE_DEVISE');

				$SOMME_LIQUIDATION = $LIQUIDATION+$this->request->getPost('LIQUIDATION_TOTALE');

				$LIQUIDATION = $LIQUIDATION;
				
				//cas partiel //paiement terminé
				if ($MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE==$SOMME_MONTANT_DEVISE)
				{
					$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
				}

				$DEVISE_TYPE_HISTO_LIQUI_ID	="";

				$taux_exist = "SELECT DEVISE_TYPE_HISTO_ID,DEVISE_TYPE_ID,TAUX,DATE_INSERTION FROM devise_type_hist WHERE DEVISE_TYPE_ID=".$TYPE_MONTANT_ID." AND TAUX=".$COUT_DEVISE." AND DATE_INSERTION LIKE '$DATE_COUT_DEVISE%'";
				$taux_exist='CALL `getTable`("'.$taux_exist.'")';
				$taux_exist= $this->ModelPs->getRequeteOne($taux_exist);
				if(!empty($taux_exist))
				{
					$DEVISE_TYPE_HISTO_LIQUI_ID=$taux_exist["DEVISE_TYPE_HISTO_ID"];
				}
				else
				{
					$columns="DEVISE_TYPE_ID,TAUX,IS_ACTIVE,DATE_INSERTION";
					$data_col=$TYPE_MONTANT_ID.",".$COUT_DEVISE.",0,'".$DATE_COUT_DEVISE."'";
					$table_dev="devise_type_hist";
					$DEVISE_TYPE_HISTO_LIQUI_ID =$this->save_all_table($table_dev,$columns,$data_col);
				}

				$table = 'execution_budgetaire';
				$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$data='LIQUIDATION_DEVISE='.$SOMME_MONTANT_DEVISE.', LIQUIDATION='.$SOMME_LIQUIDATION.'';
				$this->update_all_table($table,$data,$where);
				###############################################
								//update execution_bugetaire_execution_tache
				//cas total
				if($ID_TYPE_LIQUIDATION==2)
				{
					//update execution_bugetaire_execution_tache
					$tableEBET='execution_budgetaire_execution_tache';
					$conditionsEBET='EXECUTION_BUDGETAIRE_ID ='.$EXECUTION_BUDGETAIRE_ID;
					$datatomodifieEBET= 'MONTANT_LIQUIDATION=MONTANT_ENG_BUDGETAIRE, MONTANT_LIQUIDATION_DEVISE=MONTANT_ENG_BUDGETAIRE_DEVISE';
					$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);

					foreach ($infoTaches as $infoTache) {
						
						$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
						$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
						$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;

						$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
						$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
						$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

						//UPDATE les montants de chaque tache
						$datatomodifieEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID=".$infoTache['EBET_ID'].",MONTANT_LIQUIDATION=".$MONTANT_EBET.",MONTANT_LIQUIDATION_DEVISE=".$MONTANT_DEVISE_EBET.",INTRODUCTION_NOTE='".$INTRODUCTION_NOTE."'";

						$conditionsEBETD = "EXECUTION_BUDGETAIRE_EXECUTION_TACHE_DETAIL_ID=".$infoTache['EBETD_ID'];
						$tableEBETD="execution_budgetaire_execution_tache_detail";
						$resEBETD =$this->update_all_table($tableEBETD,$datatomodifieEBETD,$conditionsEBETD);
					}
				}
				//cas partiel
				else{
					foreach ($infoTaches as $infoTache) {
						$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
						$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
						$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;


						$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
						$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
						$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

						$MONTANT_EBET = $MONTANT_DEVISE_EBET * $COUT_DEVISE;

						$tableEBET='execution_budgetaire_execution_tache';
						$conditionsEBET='EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ='.$infoTache['EBET_ID'];
						$datatomodifieEBET= 'MONTANT_LIQUIDATION='.$MONTANT_EBET.', MONTANT_LIQUIDATION_DEVISE='.$MONTANT_DEVISE_EBET;
						$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);

						//UPDATE les montants de chaque tache
						$datatomodifieEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID=".$infoTache['EBET_ID'].",MONTANT_LIQUIDATION=".$MONTANT_EBET.",MONTANT_LIQUIDATION_DEVISE=".$MONTANT_DEVISE_EBET.",INTRODUCTION_NOTE='".$INTRODUCTION_NOTE."'";

						$conditionsEBETD = "EXECUTION_BUDGETAIRE_EXECUTION_TACHE_DETAIL_ID=".$infoTache['EBETD_ID'];
						$tableEBETD="execution_budgetaire_execution_tache_detail";
						$resEBETD =$this->update_all_table($tableEBETD,$datatomodifieEBETD,$conditionsEBETD);
					}

				}

				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1="MONTANT_LIQUIDATION_DEVISE=".$MONTANT_DEVISE.", MONTANT_LIQUIDATION='".$LIQUIDATION."', TITRE_CREANCE='".$TITRE_CREANCE."', DATE_CREANCE='".$DATE_CREANCE."', DATE_LIQUIDATION='".$DATE_LIQUIDATION."', MOTIF_LIQUIDATION='".$MOTIF_LIQUIDATION."', COUR_DEVISE='".$COUT_DEVISE."',DATE_COUR_DEVISE='".$DATE_COUT_DEVISE."',DEVISE_TYPE_HISTO_LIQUI_ID=".$DEVISE_TYPE_HISTO_LIQUI_ID.",INTRODUCTION_NOTE='".$INTRODUCTION_NOTE."'";
				$this->update_all_table($table1,$data1,$where1);

				//Enregistrement dans execution_budgetaire_titre_decaissement
	            $tableEBTD='execution_budgetaire_titre_decaissement';
	            $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
	            $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
	            $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);
			}
			else
			{
				$MONTANT_RACCROCHE_LIQUIDATION = $this->request->getPost('MONTANT_RACCROCHE_LIQUIDATION');

				$MONTANT_RACCROCHE_LIQUIDATION = floatval($MONTANT_RACCROCHE_LIQUIDATION) - floatval($monnaie);
				
				$SOMME_MONTANT_CREANCE = $MONTANT_CREANCE+$MONTANT_RACCROCHE_LIQUIDATION;

				$SOMME_LIQUIDATION = $MONTANT_CREANCE+$this->request->getPost('LIQUIDATION_TOTALE');

				//cas partiel //paiement terminé
				if ($MONTANT_RACCROCHE_JURIDIQUE_VALUE==$SOMME_LIQUIDATION)
				{
					$this->update_all_table($tableLiquidTermin,$dataLiquidTermin,$whereLiquidTermin);
				}

				$table = 'execution_budgetaire';
				$where='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
				$data='LIQUIDATION='.$SOMME_LIQUIDATION;
				$this->update_all_table($table,$data,$where);
				###############################################   ###########################

				//update execution_bugetaire_execution_tache
				if($ID_TYPE_LIQUIDATION==2)
				{
					//update execution_bugetaire_execution_tache
					$tableEBET='execution_budgetaire_execution_tache';
					$conditionsEBET='EXECUTION_BUDGETAIRE_ID ='.$EXECUTION_BUDGETAIRE_ID;
					$datatomodifieEBET= 'MONTANT_LIQUIDATION=MONTANT_ENG_BUDGETAIRE, MONTANT_LIQUIDATION_DEVISE=MONTANT_ENG_BUDGETAIRE_DEVISE';
					$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);

					foreach ($infoTaches as $infoTache) {

						$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
						$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
						$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;

						$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
						$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
						$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

						//UPDATE les montants de chaque tache
						$datatomodifieEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID=".$infoTache['EBET_ID'].",MONTANT_LIQUIDATION=".$MONTANT_EBET.",MONTANT_LIQUIDATION_DEVISE=".$MONTANT_DEVISE_EBET;

						$conditionsEBETD = "EXECUTION_BUDGETAIRE_EXECUTION_TACHE_DETAIL_ID=".$infoTache['EBETD_ID'];
						$tableEBETD="execution_budgetaire_execution_tache_detail";
						$resEBETD =$this->update_all_table($tableEBETD,$datatomodifieEBETD,$conditionsEBETD);
					}
				}
				//cas partiel
				else{
					foreach ($infoTaches as $infoTache) {
						$MONTANT_EBET=$this->request->getPost("MONTANT_".$infoTache['EBET_ID']);
						$MONTANT_EBET=str_replace(' ','',$MONTANT_EBET);
						$MONTANT_EBET = !empty($MONTANT_EBET) ? $MONTANT_EBET : 0;

						$MONTANT_DEVISE_EBET=$this->request->getPost("MONTANT_DEVISE_".$infoTache['EBET_ID']);
						$MONTANT_DEVISE_EBET=str_replace(' ','',$MONTANT_DEVISE_EBET);
						$MONTANT_DEVISE_EBET = !empty($MONTANT_DEVISE_EBET) ? $MONTANT_DEVISE_EBET : 0;

						//query les montant des taches des autre liquidations
						$req = "SELECT MONTANT_LIQUIDATION, MONTANT_LIQUIDATION_DEVISE FROM execution_budgetaire_execution_tache_detail WHERE EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID <> ".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID." AND EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID = ".$infoTache['EBET_ID'];
						$reqExec = "CALL `getTable`('" . $req . "');";
						$taches_dets = $this->ModelPs->getRequete($reqExec);

						//montant total pour la tache venant de toutes les autre liquidations partielles
						$MONTANT_TOTAL_TACHE = 0;
						$MONTANT_TOTAL_TACHE_DEVISE = 0;
						foreach ($taches_dets as $taches_det) {
							$MONTANT_TOTAL_TACHE += $taches_det->MONTANT_LIQUIDATION;
							$MONTANT_TOTAL_TACHE_DEVISE += $taches_det->MONTANT_LIQUIDATION_DEVISE;
						}

						//ajouter (a la somme des montants des taches des autre liquidation) le nouveau montant de la liquidation actuelle
						$MONTANT_TOTAL_TACHE +=  $MONTANT_EBET;
						$MONTANT_TOTAL_TACHE_DEVISE += $MONTANT_DEVISE_EBET;

						//update le montant total de la tache deja liquide
						$tableEBET='execution_budgetaire_execution_tache';
						$conditionsEBET='EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ='.$infoTache['EBET_ID'];
						$datatomodifieEBET= 'MONTANT_LIQUIDATION='.$MONTANT_TOTAL_TACHE.', MONTANT_LIQUIDATION_DEVISE='.$MONTANT_TOTAL_TACHE_DEVISE;
						$this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);

						//UPDATE les montants de chaque tache
						$datatomodifieEBETD="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID=".$infoTache['EBET_ID'].",MONTANT_LIQUIDATION=".$MONTANT_EBET.",MONTANT_LIQUIDATION_DEVISE=".$MONTANT_DEVISE_EBET.",INTRODUCTION_NOTE='".$INTRODUCTION_NOTE."'";

						$conditionsEBETD = "EXECUTION_BUDGETAIRE_EXECUTION_TACHE_DETAIL_ID=".$infoTache['EBETD_ID'];
						$tableEBETD="execution_budgetaire_execution_tache_detail";
						$resEBETD =$this->update_all_table($tableEBETD,$datatomodifieEBETD,$conditionsEBETD);

					}
				}


				$table1 = 'execution_budgetaire_tache_detail';
				$where1='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
				$data1="MONTANT_CREANCE=".$MONTANT_CREANCE.", MONTANT_LIQUIDATION=".$MONTANT_CREANCE.", TITRE_CREANCE='".$TITRE_CREANCE."', DATE_CREANCE='".$DATE_CREANCE."', DATE_LIQUIDATION='".$DATE_LIQUIDATION."', MOTIF_LIQUIDATION='".$MOTIF_LIQUIDATION."'";
				$this->update_all_table($table1,$data1,$where1);
				##########################################################################
				//Enregistrement dans execution_budgetaire_titre_decaissement
	            $tableEBTD='execution_budgetaire_titre_decaissement';
	            $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
	            $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
	            $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);
			}

			//Mise à jours dans la table historique_raccrochage 
			$OBSERVATION = str_replace("'", "\'", $this->request->getPost('OBSERVATION'));
			$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
			$DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');

			$table2 = 'execution_budgetaire_tache_detail_histo';
			$columsinsert2="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, USER_ID,ETAPE_DOUBLE_COMMANDE_ID,OBSERVATION, DATE_TRANSMISSION, DATE_RECEPTION";
			$datacolumsinsert2=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.", ".$USER_ID.", ".$ETAPE_ID.", '".$OBSERVATION."','".$DATE_TRANSMISSION."', '".$DATE_RECEPTION."'";
			$this->save_all_table($table2,$columsinsert2,$datacolumsinsert2);
	        #######################################################################

			$table = 'execution_budgetaire_tache_detail';
			$where='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
			$data='TAUX_TVA_ID='.$TAUX_TVA_ID.', EXONERATION='.$EXONERATION.'';
			$this->update_all_table($table,$data,$where);

	        // mise à jour dans la table historique_raccrochage_operation_verification
	        ##################### delete ###########################################
			$table5 = 'execution_budgetaire_histo_operation_verification';
			$deleteRequete3='DELETE FROM execution_budgetaire_histo_operation_verification WHERE execution_budgetaire_histo_operation_verification.EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' AND TYPE_ANALYSE_ID IN(SELECT budgetaire_type_analyse.BUDGETAIRE_TYPE_ANALYSE_ID FROM budgetaire_type_analyse WHERE budgetaire_type_analyse.MOUVEMENT_DEPENSE_ID=3)';
			$this->ModelPs->getRequete("CALL `getTable`('" . $deleteRequete3 . "')");
	      	########################   insert  #####################################
			$TYPE_ANALYSE_ID = $this->request->getPost('TYPE_ANALYSE_ID[]');

			foreach ($TYPE_ANALYSE_ID as $key)
			{
				$columsinsert="TYPE_ANALYSE_ID, ETAPE_DOUBLE_COMMANDE_ID, EXECUTION_BUDGETAIRE_ID";
				$datacolumsinsert=$key.", ".$ETAPE_ID.", ".$EXECUTION_BUDGETAIRE_ID." ";
				$this->save_all_table($table5,$columsinsert,$datacolumsinsert);
			}
	      	###############################################################################		
			$data=['message' => "".lang('messages_lang.labelle_message_update_success').""];
			session()->setFlashdata('alert', $data);
			return redirect('double_commande_new/Liquidation_Double_Commande/get_liquid_Afaire');  
		}
		else
		{
			$dist=3;
			return $this->getOne_corriger(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),$dist);
		}		  		
	}

	//vieux liquidation a faire
	public function get_liquid_partiel($value='')
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}
		else
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		}

		if($session->get('SESSION_SUIVIE_PTBA_LIQUIDATION') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

		$data_menu=$this->getDataMenuLiquidation_new();

		$data['institutions_user']=$data_menu['institutions_user'];

		$data['get_liquid_Afaire']=$data_menu['get_liquid_Afaire'];
		$data['get_liquid_deja_fait']=$data_menu['get_liquid_deja_fait'];
		$data['get_liquid_Avalider'] = $data_menu['get_liquid_Avalider'];
		$data['get_liquid_Acorriger'] = $data_menu['get_liquid_Acorriger'];
		$data['get_liquid_valider'] = $data_menu['get_liquid_valider'];
		$data['get_liquid_rejeter'] = $data_menu['get_liquid_rejeter'];
		$data['get_liquid_partielle'] = $data_menu['get_liquid_partielle'];
		$data['nbr_from_ord']=$data_menu['nbr_from_ord'];

		return view('App\Modules\double_commande_new\Views\Liquidation_Partiel_List_View',$data);
	}

	function listing_liquid_partiel()
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
		if($gdc !=1 AND $ced != 1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE',1,'exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec.LIQUIDATION',1);

		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%'.$var_search.'%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR dev.DESC_DEVISE_TYPE LIKE "%'.$var_search.'%" OR exec.ENG_BUDGETAIRE LIKE "%'.$var_search. '%" OR exec.ENG_BUDGETAIRE_DEVISE LIKE "%' .$var_search.'%" OR exec.ENG_JURIDIQUE LIKE "%'.$var_search.'%" OR exec.ENG_JURIDIQUE_DEVISE LIKE "%'.$var_search. '%" OR exec.LIQUIDATION LIKE "%' .$var_search.'%" OR exec.LIQUIDATION_DEVISE LIKE "%'.$var_search.'%")') : '';

		// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
		// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT exec.EXECUTION_BUDGETAIRE_ID,dev.DESC_DEVISE_TYPE,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.NUMERO_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec.LIQUIDATION,exec.LIQUIDATION_DEVISE,exec.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID =exec.EXECUTION_BUDGETAIRE_ID WHERE exec.LIQUIDATION_TYPE_ID=1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND IS_FINISHED !=1 ".$institution." ";

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase .' '.$conditions;

		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		foreach ($fetch_data as $row)
		{
			$sub_array = array();
			$et_db_comm=10;
			$getEtape='SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? 'Liquidation/getOne_partiel':0;
			$dist="/1";

			// $getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil prof WHERE ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm;

			// $getProf = "CALL `getTable`('".$getProf."');";
			// $Profil_connect= $this->ModelPs->getRequete($getProf);

			$callpsreq = "CALL getRequete(?,?,?,?);";
			$getProf = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm,'PROFIL_ID DESC');
			$Profil_connect= $this->ModelPs->getRequete($callpsreq, $getProf);

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
			foreach ($Profil_connect as $key)
			{
				$prof = (!empty($key->PROFIL_ID)) ? $key->PROFIL_ID : 0 ;

				if($prof_id== $prof || $prof_id==1)
				{
					$number="<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

					$bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
				}
			}

			//Nombre des tâches
			$count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
			$count_task = 'CALL `getTable`("'.$count_task.'");';
			$nbre_task = $this->ModelPs->getRequeteOne($count_task);

			$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
			$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

			$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
			$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

			$LIQUIDATION=floatval($row->LIQUIDATION);
			$LIQUIDATION=number_format($LIQUIDATION,0,","," ");

			if($row->DEVISE_TYPE_ID!=1)
			{
				$ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
				$ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

				$ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
				$ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");

				$LIQUIDATION=floatval($row->LIQUIDATION_DEVISE);
				$LIQUIDATION=number_format($LIQUIDATION,4,","," ");
			}

			$action='';
			$sub_array = array();
			$sub_array[] = $number;
			$sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
			$point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")'>".$nbre_task['nbre']."</a></center>";
			$sub_array[] = $point;               
			$sub_array[] = $row->DESC_DEVISE_TYPE;
			$sub_array[] = $ENG_BUDGETAIRE;
			$sub_array[] = $ENG_JURIDIQUE;
			$sub_array[] = $LIQUIDATION;
			$action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
			$action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
			$sub_array[] = $action;
			$data[] = $sub_array;
		}

		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $requetedebase . "')");
		$recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data,
		);
		return $this->response->setJSON($output);
		//echo json_encode($output);		
	}

	function listing_liquid_partiel_old()
	{
		$db = db_connect();
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$gdc = $session->get('SESSION_SUIVIE_PTBA_LIQUIDATION');
		$ced = $session->get('SESSION_SUIVIE_PTBA_CONFIRM_LIQUIDATION');
		if($gdc !=1 AND $ced != 1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

		$institution=' AND ptba_tache.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');

		if(!empty($INSTITUTION_ID))
		{
			$institution.=' AND ptba_tache.INSTITUTION_ID='.$INSTITUTION_ID;
		}

		if(!empty($SOUS_TUTEL_ID))
		{
			$institution.=' AND ptba_tache.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critere = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column = array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1, 'act.DESC_PAP_ACTIVITE',1, 'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (exec.NUMERO_BON_ENGAGEMENT LIKE "%' . $var_search . '%" OR exec.COMMENTAIRE LIKE "%' . $var_search . '%" OR exec.ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR act.DESC_PAP_ACTIVITE LIKE "%' . $var_search . '%")') : '';

		// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
		// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;

		$requetedebase= "SELECT exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec.LIQUIDATION,exec.LIQUIDATION_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,act.DESC_PAP_ACTIVITE,ptba_tache.DESC_TACHE,exec.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE FROM execution_budgetaire_execution_tache exec_tache JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID =exec.EXECUTION_BUDGETAIRE_ID WHERE 1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=1 ".$institution." ";

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase .' '.$conditions;

		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		foreach ($fetch_data as $row)
		{
			$sub_array = array();
			$et_db_comm=10;
			$getEtape='SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
			$getEtape = "CALL getTable('" . $getEtape . "');";
			$EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
			$step=($EtapeActuel) ? 'Liquidation/getOne_partiel':0;
			$dist="/1";

			$getProf = 'SELECT ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil prof WHERE ETAPE_DOUBLE_COMMANDE_ID=10';

			$getProf = "CALL `getTable`('".$getProf."');";
			$Profil_connect= $this->ModelPs->getRequete($getProf);

			$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
			$number=$row->NUMERO_BON_ENGAGEMENT;
			foreach ($Profil_connect as $key)
			{
				$prof = (!empty($key->PROFIL_ID)) ? $key->PROFIL_ID : 0 ;
				$bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
				if($prof_id== $prof || $prof_id==1)
				{
					$number= "<a  style='color:#fbbf25;' title='' href='".base_url('double_commande_new/'.$step."/".md5($row->EXECUTION_BUDGETAIRE_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
				}
			}

			$DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

			$DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

			$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

			$COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

			$money_liq  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE DEVISE_TYPE_ID='.$row->DEVISE_TYPE_ID;
			$money_liq = "CALL `getTable`('" . $money_liq . "');";
			$histo_money = $this->ModelPs->getRequeteOne($money_liq);
			$type_mont = (!empty($histo_money['DESC_DEVISE_TYPE'])) ? $histo_money['DESC_DEVISE_TYPE'] : 'N/A' ;

			$ENG_BUDGETAIRE=0;
			$ENG_JURIDIQUE=0;
			$MONTANT_LIQUIDATION=0;

			if ($row->DEVISE_TYPE_ID==1)
			{
				$ENG_BUDGETAIRE=number_format($row->ENG_BUDGETAIRE,'0',',',' ');
				$ENG_JURIDIQUE=number_format($row->ENG_JURIDIQUE,'0',',',' ');
				$MONTANT_LIQUIDATION=number_format($row->LIQUIDATION,'0',',',' ');	
			}
			else
			{
				$ENG_BUDGETAIRE=number_format($row->ENG_BUDGETAIRE_DEVISE,'4',',',' ');
				$ENG_JURIDIQUE=number_format($row->ENG_JURIDIQUE_DEVISE,'4',',',' ');
				$MONTANT_LIQUIDATION=number_format($row->LIQUIDATION_DEVISE,'4',',',' ');
			}

			$sub_array[]=$number;
			$sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
			$sub_array[]=$LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
			$sub_array[]=$DESC_PAP_ACTIVITE;       
			$sub_array[]=$DESC_TACHE;
			$sub_array[]=$COMMENTAIRE;
			$sub_array[]=$type_mont;
			$sub_array[]=$ENG_BUDGETAIRE;
			$sub_array[]=$ENG_JURIDIQUE;
			$sub_array[]=$MONTANT_LIQUIDATION;
			$data[] = $sub_array;
		}

		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $requetedebase . "')");
		$recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data,
		);
		return $this->response->setJSON($output);
		//echo json_encode($output);		
	}
}
?>
