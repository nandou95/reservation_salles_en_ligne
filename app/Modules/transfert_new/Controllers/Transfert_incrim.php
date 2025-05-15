<?php
/*
*kazimushahara derick
*Titre: fonction pour les transferts
*WhatsApp: +257 77 432485
*Email pro: derick@mediabox.bi
*Date:  Le 2/10/2023
**/
namespace  App\Modules\transfert_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Transfert_incrim extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}
	
	public function index()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		//nombre de transfert par type de transfert
		$transfert=$this->count_transfert_new();
		$data['nbre_tr_hist']=$transfert['nbre_tr_hist'];
		$data['nbre_incrim']=$transfert['nbre_incrim'];
		$data['nbre_imput']=$transfert['nbre_imput'];
		$data['nbre_activite']=$transfert['nbre_activite'];
		//fin nombre de transfert par type de transfert
		return view('App\Modules\transfert_new\Views\Transfert_incrim_list_view',$data);   
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	public function save_info($columsinsert,$datacolumsinsert)
	{
	  // $columsinsert: Nom des colonnes separe par,
	  // $datacolumsinsert : les donnees a inserer dans les colonnes
		$table='transfert_tempo';
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$this->ModelPs->createUpdateDelete($insertReqAgence,$bindparms);
	}

	/* Debut Gestion update table de la demande detail*/
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}

	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$userfiancier = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$institution=' AND execution_budgetaire_new.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';
		
		$db = db_connect();
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
		$order_column = array('ligne.CODE_NOMENCLATURE_BUDGETAIRE', 'LIBELLE','CREDIT_VOTE','TRANSFERTS_CREDITS','CREDIT_APRES_TRANSFERT', 'ENG_BUDGETAIRE','ENG_JURIDIQUE', 'LIQUIDATION', 'ORDONNANCEMENT', 'PAIEMENT','DECAISSEMENT', 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND ( ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR ORDONNANCEMENT LIKE "%' . $var_search . '%" OR PAIEMENT LIKE "%' . $var_search . '%" OR DECAISSEMENT LIKE "%' . $var_search . '%" OR ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR  ENG_JURIDIQUE LIKE "%' . $var_search . '%" OR  LIQUIDATION LIKE "%' . $var_search . '%" OR  LIBELLE LIKE "%' . $var_search . '%" OR PAIEMENT LIKE "%' . $var_search . '%")') : '';

		// Condition pour la requête principale
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
		// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;
		$requetedebase= 'SELECT EXECUTION_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,LIBELLE,CREDIT_VOTE,TRANSFERTS_CREDITS,CREDIT_APRES_TRANSFERT,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,IS_RACCROCHE,MOUVEMENT_DEPENSE_ID,IS_TRANSFERTS,INSTITUTION_ID FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE IS_RACCROCHE = 0 AND IS_TRANSFERTS=1 AND CREDIT_APRES_TRANSFERT>CREDIT_VOTE '.$institution;

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;

		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;

		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		foreach ($fetch_data as $row)
		{
			$sub_array = array();

			if (mb_strlen($row->LIBELLE) > 8){ 
				$LIBELLE =  mb_substr($row->LIBELLE, 0, 8) .'...<a class="btn-sm" title="'.lang('messages_lang.liste_Afficher').'"  onclick="show_modal('.$row->EXECUTION_BUDGETAIRE_ID.')"><i class="fa fa-eye"></i></a>';

			}else
			{
				$LIBELLE =  $row->LIBELLE;
			}
			

			if($userfiancier==1)
			{
				$sub_array[] = "<a  style='text-decoration:none'title='Transférer' href='".base_url("transfert_new/Transfert_Incrementation/getOne/".$row->EXECUTION_BUDGETAIRE_ID)."' >".$row->IMPUTATION."</a>";
			}
			else
			{
				$sub_array[] =$row->IMPUTATION;
			}

			$sub_array[] = $LIBELLE." 
			<div class='modal fade' id='institution".$row->EXECUTION_BUDGETAIRE_ID."'>
			<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-body'>
			<center>
			<h5><b> ".$row->LIBELLE." </b></h5>
			</center>
			</div>
			<div class='modal-footer'>
			<button class='btn btn-primary btn-md' data-dismiss='modal'>Quitter
			</button>
			</div>
			</div>
			</div>
			</div>";

			$CREDIT_VOTE=number_format($row->CREDIT_VOTE,'2',',',' ');
			$TRANSFERTS_CREDITS=number_format($row->TRANSFERTS_CREDITS,'2',',',' ');
			$CREDIT_APRES_TRANSFERT=number_format($row->CREDIT_APRES_TRANSFERT,'2',',',' ');
			$ENG_BUDGETAIRE=number_format($row->ENG_BUDGETAIRE,'2',',',' ');
			$ENG_JURIDIQUE=number_format($row->ENG_JURIDIQUE,'2',',',' ');
			$LIQUIDATION=number_format($row->LIQUIDATION,'2',',',' ');
			$ORDONNANCEMENT=number_format($row->ORDONNANCEMENT,'2',',',' ');
			$PAIEMENT=number_format($row->PAIEMENT,'2',',',' ');
			$DECAISSEMENT=number_format($row->DECAISSEMENT,'2',',',' ');
			
			$sub_array[] = !empty($CREDIT_VOTE) ? $CREDIT_VOTE : 0 ;
			$sub_array[] = !empty($TRANSFERTS_CREDITS) ? $TRANSFERTS_CREDITS : 0;
			$sub_array[] = !empty($CREDIT_APRES_TRANSFERT) ? $CREDIT_APRES_TRANSFERT : 0;
			$sub_array[] = !empty($ENG_BUDGETAIRE) ? $ENG_BUDGETAIRE : 0 ;
			$sub_array[] = !empty($ENG_JURIDIQUE) ? $ENG_JURIDIQUE : 0;
			$sub_array[] = !empty($LIQUIDATION) ? $LIQUIDATION : 0;
			$sub_array[] = !empty($ORDONNANCEMENT)  ?$ORDONNANCEMENT : 0;
			$sub_array[] = !empty($PAIEMENT) ? $PAIEMENT : 0;
			$sub_array[] = !empty($DECAISSEMENT) ? $DECAISSEMENT : 0;

			if ($row->IS_RACCROCHE==1)
			{
				$BTN_TRAITE = "<a href='#' class='btn btn-info btn-sm'><i class='fa fa-check text-light'></i></a>";
			}
			else
			{	
				$BTN_TRAITE = "<a class='btn btn-primary btn-sm' title='Transférer' href='".base_url("transfert_new/Transfert_Incrementation/getOne/".$row->EXECUTION_BUDGETAIRE_ID)."'><i class='fa fa-money-bill-transfer text-light' style='font-size:20px;'></i></a>";
			}

			if($userfiancier==1)
			{
				$action="".$BTN_TRAITE."";
				$sub_array[]=$action;
			}

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
		return $this->response->setJSON($output);//echo json_encode($output);
	}

	public function getOne($id)
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$userfiancier = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER');
		if (empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$dataa=$this->converdate();
		$tranche=$dataa['CODE_TRANCHE'];

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$inst=$this->getBindParms('INSTITUTION_ID','execution_budgetaire_new','EXECUTION_BUDGETAIRE_ID='.$id,' EXECUTION_BUDGETAIRE_ID DESC');
		$institution_id=$this->ModelPs->getRequeteOne($callpsreq, $inst);
		
		$getInst=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','INSTITUTION_ID='.$institution_id['INSTITUTION_ID'],'DESCRIPTION_INSTITUTION ASC');
		$institutions = $this->ModelPs->getRequeteOne($callpsreq, $getInst);
		$institution=$institutions['CODE_INSTITUTION'];

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$table='execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID';
		$columnselect='ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID AS IMPUTATION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,EXECUTION_BUDGETAIRE_ID,TRANSFERTS_CREDITS';
		$where="execution_budgetaire_new.EXECUTION_BUDGETAIRE_ID=".$id;
		$orderby='EXECUTION_BUDGETAIRE_ID DESC';
		$where=str_replace("\'", "'", $where);
		$db=db_connect();
		$bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$data['info']= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
		$imputation=$data['info']['IMPUTATION'];
		$imputation_id=$data['info']['IMPUTATION_ID'];

		$table="ptba  JOIN execution_budgetaire_new ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='ACTIVITES,PTBA_ID,EXECUTION_BUDGETAIRE_ID';
		$where="ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$imputation_id."";
		$orderby='ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID DESC';
		$where=str_replace("\'", "'", $where);
		$db=db_connect();
		$bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$data['activite']=$this->ModelPs->getRequete($callpsreq, $bindparams);
		$ligne=$this->getBindParms('ligne.CODE_NOMENCLATURE_BUDGETAIRE,PTBA_ID','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','1','PTBA_ID ASC');
		$data['lignebudgetaire']=$this->ModelPs->getRequete($callpsreq, $ligne);

		####################################################
		$table='execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID';
		$columnselect='ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID AS IMPUTATION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,EXECUTION_BUDGETAIRE_ID,TRANSFERTS_CREDITS';
		$where="execution_budgetaire_new.EXECUTION_BUDGETAIRE_ID=".$id;
		$orderby='EXECUTION_BUDGETAIRE_ID DESC';
		$where=str_replace("\'", "'", $where);
		$db=db_connect();
		$bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$data['info']=$this->ModelPs->getRequeteOne($callpsreq,$bindparams);
		$imputation=$data['info']['IMPUTATION'];
		$imputation_id=$data['info']['IMPUTATION_ID'];

		$table="ptba JOIN execution_budgetaire_new ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='ACTIVITES,PTBA_ID,EXECUTION_BUDGETAIRE_ID';
		$where="ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$imputation_id."";
		$orderby='ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID DESC';
		$where=str_replace("\'","'", $where);
		$db=db_connect();
		$bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'","'",$bindparams);
		$data['activite1']=$this->ModelPs->getRequeteOne($callpsreq, $bindparams); 
		###############################################################

		$ministere_params=$this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION','inst_institutions','1','INSTITUTION_ID asc');
		$data['ministere']= $this->ModelPs->getRequete($callpsreq,$ministere_params);

		$table=" transfert_tempo JOIN ptba ON transfert_tempo.PTBA_ID=ptba.PTBA_ID JOIN ptba AS ptbaa ON transfert_tempo.ACTIVE_A_TRANSFERT_ARGENT=ptbaa.PTBA_ID JOIN inst_institutions ON transfert_tempo.MINISTERE_ID=inst_institutions.INSTITUTION_ID";
		$columnselect='ID_TRANSFERT,LIGNE_BUDGETAIRE,ptbaa.CODE_NOMENCLATURE_BUDGETAIRE code_transfert,`MONTANT_CREDIT_TRANSFERT`,`MONTANT_VOTE`,`MONTANT_A_TRANSFERE`,`MONTANT_APRES_TRANSFERT`,`LIGNE_BUDGETAIRE_TRANSFERT`,`MONTANT_TOTAL_VOTE_PAR_LIGNE`,`MONTANT_TOTAL_APRES_TRANSFERT_PAR_LIGNE_BUDG`,inst_institutions.DESCRIPTION_INSTITUTION,`MONTANT_VOTE_ACTIVITE_TRANSFERT`,`MONTANT_PRECIS_ACTIVITE`,`MONTANT_PRECIS_APRES_TRANSFERT`,ptba.ACTIVITES AS activite_line,ptbaa.ACTIVITES AS activite_trnasfert';
		$where="STATUT=0";
		$orderby='1 DESC';
		$where=str_replace("\'", "'", $where);
		$db=db_connect();
		$bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'","'",$bindparams);
		$data['transfert']=$this->ModelPs->getRequete($callpsreq, $bindparams);
		$data['countDataTable'] = count($data['transfert']);

		$sommation_montant_transfert=$this->getBindParms('SUM(MONTANT_A_TRANSFERE) as montant_transfert','transfert_tempo','EXECUTION_BUDGETAIRE_ID='.$id,'ID_TRANSFERT ASC');
		$data['summ_transfert']=$this->ModelPs->getRequeteOne($callpsreq,$sommation_montant_transfert);

		$sommation_montant_transfert=$this->getBindParms('SUM(MONTANT_PRECIS_ACTIVITE) as montant_transfert_activte','transfert_tempo','EXECUTION_BUDGETAIRE_ID='.$id,'ID_TRANSFERT ASC');
		$data['summ_transfert_act']=$this->ModelPs->getRequeteOne($callpsreq, $sommation_montant_transfert);
		return view('App\Modules\transfert_new\Views\Transfert_add_view',$data);		
	}

	function get_montant($PTBA_ID)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$dataa=$this->converdate();
		$debut=$dataa['debut'];
		$fin=$dataa['fin'];
		$tranche=$dataa['CODE_TRANCHE'];
		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$table="ptba OIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='T1 as montant,inst.CODE_INSTITUTION AS CODE_MINISTERE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,PTBA_ID,T1 as tranche,QT1 as qte,UNITE,act.CODE_ACTION,act.LIBELLE_ACTION,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME';
		$where="ptba.PTBA_ID ='".$PTBA_ID."'";
		$orderby=' PTBA_ID DESC';
		$where=str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$activiteinfo= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);	

		$ptba_params=$this->getBindParms('PTBA_ID','execution_budgetaire_raccrochage_activite_new', 'execution_budgetaire_raccrochage_activite_new.PTBA_ID='.$PTBA_ID,'PTBA_ID asc');
		$ptba_id= $this->ModelPs->getRequeteOne($callpsreq, $ptba_params);

		$ministere_params=$this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION','inst_institutions', 'CODE_INSTITUTION='.$activiteinfo['CODE_MINISTERE'],'1');
		$ministere= $this->ModelPs->getRequeteOne($callpsreq, $ministere_params);

		$montant_format=$activiteinfo['tranche'];
		$output = array("mont" => $montant_format);
		return $this->response->setJSON($output);
	}

	/* fonction pour recuperer les codes budgetaires en fonction du ministere choisis */
	public function get_code($ministere_id)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$getmin = $this->getBindParms('CODE_INSTITUTION','inst_institutions','INSTITUTION_ID='.$ministere_id,'INSTITUTION_ID  ASC');
		$code_min = $this->ModelPs->getRequeteOne($callpsreq, $getmin);
		$code=$code_min['CODE_INSTITUTION'];

		$getcodeactivite = $this->getBindParms('CODE_NOMENCLATURE_BUDGETAIRE','inst_institutions_ligne_budgetaire','ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "'.$code.'%"','CODE_NOMENCLATURE_BUDGETAIRE ASC');
		$getcodeactivite=str_replace("\\'","",$getcodeactivite);
		$getcodeactivite=str_replace("\\","",$getcodeactivite);
		$code_activites = $this->ModelPs->getRequete($callpsreq, $getcodeactivite);

		$html='<option value="">Sélectionner</option>';
		foreach ($code_activites as $key)
		{
			$html.='<option value="'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'">'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'</option>';
		}

		$output=array("code" => $html);
		return $this->response->setJSON($output);
	}

	public function get_montant_act_transfert($PTBA_ID)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$dataa=$this->converdate();
		$debut=$dataa['debut'];
		$fin=$dataa['fin'];
		$tranche=$dataa['CODE_TRANCHE'];
		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$table="ptbaJOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='T1 as montant,inst.CODE_INSTITUTION AS CODE_MINISTERE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,PTBA_ID,T1 as tranche,QT1 as qte,UNITE,act.CODE_ACTION,act.LIBELLE_ACTION,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME';
		$where="ptba.PTBA_ID ='".$PTBA_ID."'";
		$orderby=' PTBA_ID DESC';
		$where=str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$activiteinfo= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);	

		$ptba_params=$this->getBindParms('PTBA_ID','execution_budgetaire_raccrochage_activite_new', 'execution_budgetaire_raccrochage_activite_new.PTBA_ID='.$PTBA_ID,'PTBA_ID asc');
		$ptba_id= $this->ModelPs->getRequeteOne($callpsreq, $ptba_params);

		$ministere_params=$this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION','inst_institutions', 'CODE_INSTITUTION='.$activiteinfo['CODE_MINISTERE'],'1');
		$ministere= $this->ModelPs->getRequeteOne($callpsreq, $ministere_params);

		$montant_format=$activiteinfo['tranche'];
		
		$output = array("montant" => $montant_format);
		return $this->response->setJSON($output);
	}

	/* fonction poure recuperer les activites selon la ligne budgetaire */
	public function get_activite($LIGNE_BUDG_ID)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$dataa=$this->converdate();
		$tranche=$dataa['CODE_TRANCHE'];
		$callpsreq="CALL `getRequete`(?,?,?,?);";
		
		$getactive=$this->getBindParms('ACTIVITES,PTBA_ID,T1 as tranche','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ligne.CODE_NOMENCLATURE_BUDGETAIRE='.$LIGNE_BUDG_ID,'PTBA_ID  ASC');
		$activite = $this->ModelPs->getRequete($callpsreq, $getactive);
		$html='<option value="">Sélectionner</option>';
		foreach ($activite as $key)
		{
			$html.='<option value="'.$key->PTBA_ID.'">'.$key->ACTIVITES.'</option>';
		}

		$output = array("activites" => $html);
		return $this->response->setJSON($output);
	}

	/* fonction recuperer la sommation des active */
	public function get_summ_activite($LIGNE_BUDG_ID)
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$dataa=$this->converdate();
		$tranche=$dataa['CODE_TRANCHE'];
		$callpsreq="CALL `getRequete`(?,?,?,?);";
		
		$get_summactivite=$this->getBindParms('sum(T1) as sommation','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ligne.CODE_NOMENCLATURE_BUDGETAIRE='.$LIGNE_BUDG_ID,'PTBA_ID  ASC');
		$activite_sum=$this->ModelPs->getRequeteOne($callpsreq, $get_summactivite);
		
		$output = array("activite_sum" => $activite_sum['sommation']);
		return $this->response->setJSON($output);
	}

	/* fonction pour enregistrer les informations temeporairement concerenant les transferts */
	public function enregistre_tempo()
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$STATUT=0;
		$ligne_budgetaire=$this->request->getPost('ligne');
		$id=$this->request->getPost('id');
		$transfert_apres_credit=$this->request->getPost('transfert_apres_credit');
		$ptba=$this->request->getPost('PTBA_ID');
		$montant_vote=$this->request->getPost('montant_vote');
		$montant_transfer=$this->request->getPost('transfert_transferer');
		$montant_apres_transfert=$this->request->getPost('apres_transfert');
		$ministere=$this->request->getPost('ministere');
		$Ligne_budgetaire_transfert=$this->request->getPost('LIGNE_BUDGETAIRE_TRANSFERT');
		$montantvote_ligne_transfert=$this->request->getPost('montant_vote_lbtransfert');
		$montant_apres_transfert=$this->request->getPost('montant_apres_transfert');
		$aactiv_transfert=$this->request->getPost('aactiv_transfert');
		$montant_vote_active_transfert=$this->request->getPost('montant_vote_active_transfert');
		$mont_precise_transfert=$this->request->getPost('mont_precise_transfert');
		$mont_precise_apres_transfert=$this->request->getPost('mont_precise_apres_transfert');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$get_infoactivite = $this->getBindParms('ACTIVE_A_TRANSFERT_ARGENT','transfert_tempo','1','ID_TRANSFERT  ASC');
		$activite_info = $this->ModelPs->getRequeteOne($callpsreq, $get_infoactivite);

		$get_infoactivite = $this->getBindParms('ACTIVE_A_TRANSFERT_ARGENT,PTBA_ID,MONTANT_PRECIS_ACTIVITE','transfert_tempo','1','ID_TRANSFERT  ASC');
		$activite_infos = $this->ModelPs->getRequeteOne($callpsreq, $get_infoactivite);

		if(!empty($activite_infos))
		{
			if($activite_infos['ACTIVE_A_TRANSFERT_ARGENT']==$aactiv_transfert)
			{
				$data=['message' => "".lang('messages_lang.mess_trans_meme_act').""];
				session()->setFlashdata('alert', $data);
				return $this->getOne($id);
			}
		}

		$STATUT=0;
		$columsinsert="LIGNE_BUDGETAIRE,MONTANT_CREDIT_TRANSFERT,PTBA_ID,MONTANT_VOTE,MONTANT_A_TRANSFERE,MONTANT_APRES_TRANSFERT,MINISTERE_ID,LIGNE_BUDGETAIRE_TRANSFERT,MONTANT_TOTAL_VOTE_PAR_LIGNE,MONTANT_TOTAL_APRES_TRANSFERT_PAR_LIGNE_BUDG,ACTIVE_A_TRANSFERT_ARGENT,MONTANT_VOTE_ACTIVITE_TRANSFERT,MONTANT_PRECIS_ACTIVITE,MONTANT_PRECIS_APRES_TRANSFERT,EXECUTION_BUDGETAIRE_ID,STATUT,USER_ID";
		$datacolumsinsert= $ligne_budgetaire.",".$transfert_apres_credit.",".$ptba.",".$montant_vote.",".$montant_transfer.",".$montant_apres_transfert.",".$ministere.",'".$Ligne_budgetaire_transfert."',".$montantvote_ligne_transfert.",".$montant_apres_transfert.",".$aactiv_transfert.",".$montant_vote_active_transfert.",".$mont_precise_transfert.",".$mont_precise_apres_transfert.",".$id.",".$STATUT.",".$user_id."";
		$this->save_info($columsinsert,$datacolumsinsert);
		$data=['message' => "".lang('messages_lang.mess_trans_succes').""];
		session()->setFlashdata('alert', $data);
		return $this->getOne($id);	
	}

	/* la fonction permettant d'enregistrer les les transferts */
	public function save_transfert()
	{
		$session  = \Config\Services::session();

		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
		$sum_montant_transfert=$this->request->getPost('sum_montant_transfert');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$gettransfert = $this->getBindParms('LIGNE_BUDGETAIRE,MONTANT_CREDIT_TRANSFERT,PTBA_ID,MONTANT_VOTE,MONTANT_A_TRANSFERE,MONTANT_APRES_TRANSFERT,MINISTERE_ID,LIGNE_BUDGETAIRE_TRANSFERT,MONTANT_TOTAL_VOTE_PAR_LIGNE,MONTANT_TOTAL_APRES_TRANSFERT_PAR_LIGNE_BUDG,ACTIVE_A_TRANSFERT_ARGENT,MONTANT_VOTE_ACTIVITE_TRANSFERT,MONTANT_PRECIS_ACTIVITE,MONTANT_PRECIS_APRES_TRANSFERT,EXECUTION_BUDGETAIRE_ID,STATUT,USER_ID','transfert_tempo','EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID,'ID_TRANSFERT   ASC');
		$transfert = $this->ModelPs->getRequete($callpsreq, $gettransfert);
		
		foreach($transfert as $trans)
		{
			$dataa=$this->converdate();
			$tranche=$dataa['CODE_TRANCHE'];
			$info= $trans->MONTANT_A_TRANSFERE;
			$MONTANT_PRECIS_ACTIVITE=$trans->MONTANT_PRECIS_ACTIVITE;
			$PTBA_ID=$trans->PTBA_ID;
			$ACTIVE_A_TRANSFERT_ARGENT= $trans->ACTIVE_A_TRANSFERT_ARGENT;
			$MONTANT_APRES_TRANSFERT= $trans->MONTANT_APRES_TRANSFERT;
			$MONTANT_PRECIS_APRES_TRANSFERT= $trans->MONTANT_PRECIS_APRES_TRANSFERT;
			$trans_id=$trans->LIGNE_BUDGETAIRE;

			$gettransfertss = $this->getBindParms('T1,MONTANT_RESTANT_T1','ptba','PTBA_ID='.$PTBA_ID,'PTBA_ID  ASC');
			$montant_restant= $this->ModelPs->getRequeteOne($callpsreq, $gettransfertss);
			$soustraction=$montant_restant['MONTANT_RESTANT_T1'] -$info;

			$gettransferts2 = $this->getBindParms('T1,MONTANT_RESTANT_T1','ptba','PTBA_ID='.$ACTIVE_A_TRANSFERT_ARGENT,'PTBA_ID  ASC');
			$montant_restants= $this->ModelPs->getRequeteOne($callpsreq, $gettransferts2);
			$soustraction2=$montant_restants['MONTANT_RESTANT_T1'] + $MONTANT_PRECIS_ACTIVITE;

			$table='ptba';
			$conditions='PTBA_ID='.$PTBA_ID ;
			$datatomodifie= 'MONTANT_RESTANT_T1="'.$soustraction.'"';
			$this->update_all_table($table,$datatomodifie,$conditions);

			$table='ptba';
			$conditions='PTBA_ID='.$PTBA_ID ;
			$datatomodifie='T1="'.$MONTANT_APRES_TRANSFERT.'"';
			$this->update_all_table($table,$datatomodifie,$conditions);

			$conditions='PTBA_ID='.$ACTIVE_A_TRANSFERT_ARGENT ;
			$datatomodifie='MONTANT_RESTANT_T1="'.$soustraction2.'"';
			$this->update_all_table($table,$datatomodifie,$conditions);

			$table='ptba';
			$conditions='PTBA_ID='.$ACTIVE_A_TRANSFERT_ARGENT ;
			$datatomodifie='T1="'.$MONTANT_PRECIS_APRES_TRANSFERT.'"';
			$this->update_all_table($table,$datatomodifie,$conditions);

			$IS_TRANSFERTS=2;
			$table='execution_budgetaire_new';
			$conditions='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID  ;
			$datatomodifie='IS_TRANSFERTS="'.$IS_TRANSFERTS.'"';
			$this->update_all_table($table,$datatomodifie,$conditions);

			$STATUT=1;
			$table='transfert_tempo';
			$conditions_tempo='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID  ;
			$datatomodifie_tempo='STATUT="'.$STATUT.'"';
			$this->update_all_table($table,$datatomodifie_tempo,$conditions_tempo);

			if($sum_montant_transfert== $info)
			{
				return redirect('transfert_new/Transfert');
			}
			else
			{
				return $this->getOne($EXECUTION_BUDGETAIRE_ID);
			}
		}
	}

	/* fonction pour faire la suppresion dans la table */
	public function deleteData()
	{
		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_TRANSFERT_RACCROCHAGE')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$id=$this->request->uri->getSegment(4); 
		$demande=$this->request->uri->getSegment(5);
		$db=db_connect();     
		$statut=0;
		$deleteRequete="CALL `deleteData`(?,?);";
		$critere=$db->escapeString("ID_TRANSFERT=".$id);
		$table=$db->escapeString("transfert_tempo");
		$bindparams =[$db->escapeString($table),$db->escapeString($critere)] ;
		if($this->ModelPs->createUpdateDelete($deleteRequete, $bindparams))
		{
			return $this->getOne($demande);
		}
		else
		{
			return  false;
		}
	}

	function libelleCall($id=0)
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/login');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$libel="SELECT LIBELLE FROM execution_budgetaire_new  WHERE 1 AND TRIMESTRE_ID = 2 AND EXECUTION_BUDGETAIRE_ID = ".$id;
		$libel='CALL `getList`("'.$libel.'")';
		$libelle_commentaire = $this->ModelPs->getRequeteOne( $libel);
		$output = ["data123" => $libelle_commentaire['LIBELLE']];
		return $this->response->setJSON($output);
	}
}
?>