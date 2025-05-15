<?php
/*
    jemapess 
*/

namespace  App\Modules\demande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Exec_Budget_Raccrocher_Trim2 extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}
	/**
 * Encode array from latin1 to utf8 recursively
 * @param $dat
 * @return array|string
 */
	public static function convert_from_latin1_to_utf8_recursively($dat)
	{
		if (is_string($dat)) {
			return utf8_encode($dat);
		} elseif (is_array($dat)) {
			$ret = [];
			foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);
			return $ret;
		} elseif (is_object($dat)) {
			foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);
			return $dat;
		} else {
			return $dat;
		}
	}
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
	/* Fin Gestion insertion */

	/* Debut Gestion update table de la demande detail*/
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	/* Fin Gestion update table de la demande detail*/


	public function index($value='')
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		$user_id ='';
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}
		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }

    $ind=$this->indicateur_deuxieme_trimestre_new();
    $data['get_qte_phys']=$ind['get_qte_phys'];
    $data['get_pas_qte_phys']=$ind['get_pas_qte_phys'];
    $data['get_racrochet'] = $ind['get_racrochet'];
    $data['get_deja_racrochet'] = $ind['get_deja_racrochet'];
		$data['institutions_user']=$ind['getuser'];
		return view('App\Modules\demande_new\Views\Exec_Budget_Raccrocher_View_Trim2',$data);   
	}

	//récupération du sous tutelle par rapport à l'institution
	function get_sous_tutelle($CODE_INSTITUTION=0)
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

    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_sous_tutelle = $this->getBindParms('SOUS_TUTEL_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.CODE_INSTITUTION='.$CODE_INSTITUTION.' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
    $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);
    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($sous_tutelle as $key)
    {
      $html.='<option value="'.$key->CODE_SOUS_TUTEL.'">'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
    }
    $output = array(
        "sous_tutel" => $html
    );
    return $this->response->setJSON($output);
	}

	public function getBindParms($columnselect,$table,$where,$orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	//listing debut
	function listing($value = 0)
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$quantite = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/login');
		}
		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$institution=' AND exe.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';
		$CODE_INSTITUTION=$this->request->getPost('CODE_INSTITUTION');
		$CODE_SOUS_TUTEL=$this->request->getPost('CODE_SOUS_TUTEL');

		if (!empty($CODE_INSTITUTION))
		{
			$institution.=' AND IMPUTATION LIKE "'.$CODE_INSTITUTION.'%"';
		}
		if (!empty($CODE_SOUS_TUTEL))
		{
			$institution.=' AND IMPUTATION LIKE "'.$CODE_INSTITUTION.'00'.$CODE_SOUS_TUTEL.'%"';
		}

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
		$order_column = array('ptba.CODES_PROGRAMMATIQUE','ptba.ACTIVITES','ligne.CODE_NOMENCLATURE_BUDGETAIRE','mvt.DESC_MOUVEMENT_DEPENSE','racc.MONTANT_RACCROCHE','racc.MONTANT_RACCROCHE_JURIDIQUE','racc.MONTANT_RACCROCHE_LIQUIDATION','racc.MONTANT_RACCROCHE_ORDONNANCEMENT','racc.MONTANT_RACCROCHE_PAIEMENT','racc.MONTANT_RACCROCHE_DECAISSEMENT',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID   DESC';

		$search = !empty($_POST['search']['value']) ? (' AND (ptba.ACTIVITES LIKE "%'.$var_search.'%" OR exe.IMPUTATION LIKE "%'.$var_search.'%" OR racc.UNITE LIKE "%'.$var_search.'%" OR ptba.CODES_PROGRAMMATIQUE LIKE "%'.$var_search.'%" OR racc.MONTANT_RACCROCHE LIKE "%'.$var_search.'%")') : '';

    // Condition pour la requête principale
		$conditions = $critere.' '.$search.' '.$group.' '.$order_by.' '.$limit;
    // Condition pour la requête de filtre
		$conditionsfilter = $critere.' '.$search.' '.$group;
		$requetedebase= 'SELECT racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,ptba.CODES_PROGRAMMATIQUE,mvt.DESC_MOUVEMENT_DEPENSE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,ptba.ACTIVITES,exe.SOUS_TUTEL_ID,racc.MONTANT_RACCROCHE,racc.MONTANT_RACCROCHE_JURIDIQUE,racc.MONTANT_RACCROCHE_LIQUIDATION,racc.MONTANT_RACCROCHE_ORDONNANCEMENT,racc.MONTANT_RACCROCHE_PAIEMENT,racc.MONTANT_RACCROCHE_DECAISSEMENT FROM execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_raccrochage_activite_detail det ON det.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID=racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID JOIN execution_budgetaire_new exe ON exe.EXECUTION_BUDGETAIRE_ID=racc.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN proc_mouvement_depense mvt ON mvt.MOUVEMENT_DEPENSE_ID=det.MOUVEMENT_DEPENSE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND exe.TRIMESTRE_ID=2  AND exe.IS_RACCROCHE=1 AND det.EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID NOT IN(SELECT EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID FROM historique_raccrochage_activite_detail WHERE TYPE_RACCROCHAGE_ID=2) '.$institution;

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase.' '.$conditions;
		$requetedebasefilter = $requetedebase.' '.$conditionsfilter;
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		foreach ($fetch_data as $row)
		{
			$sub_array = array();	
			$progr_row="";
			if ($quantite==1) 
			{
				$progr_row= "<a  title='Quantité' href='".base_url("demande_new/Exec_Budget_Raccrocher_Trim2/editQuantite/".$row->EXECUTION_BUDGETAIRE_RACCROCHAGE_ID)."' >".$row->CODES_PROGRAMMATIQUE."</a>";
			}
			else
			{
				$progr_row=$row->CODES_PROGRAMMATIQUE;
			}

			$sub_array[]=$progr_row;
			$sub_array[]= $row->ACTIVITES;
			$sub_array[] =$row->IMPUTATION;
			$sub_array[] =$row->DESC_MOUVEMENT_DEPENSE;
			$sub_array[] =number_format($row->MONTANT_RACCROCHE,'2',',',' ');
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_JURIDIQUE)) ? number_format($row->MONTANT_RACCROCHE_JURIDIQUE,'2',',',' ') : 0;
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_LIQUIDATION)) ? number_format($row->MONTANT_RACCROCHE_LIQUIDATION,'2',',',' ') : 0 ;
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_ORDONNANCEMENT)) ? number_format($row->MONTANT_RACCROCHE_ORDONNANCEMENT,'2',',',' ') : 0 ;
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_PAIEMENT)) ? number_format($row->MONTANT_RACCROCHE_PAIEMENT,'2',',',' ') : 0 ;
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_DECAISSEMENT)) ? number_format($row->MONTANT_RACCROCHE_DECAISSEMENT,'2',',',' ') : 0 ;
			$BTN_TRAITE = "<a class='btn btn-primary btn-sm' title='Quantité' href='".base_url("demande_new/Exec_Budget_Raccrocher_Trim2/editQuantite/".$row->EXECUTION_BUDGETAIRE_RACCROCHAGE_ID)."' > <i class='fa fa-link text-light'></i></a>";
			if($quantite==1) 
			{
				$action="".$BTN_TRAITE."";
				$sub_array[]=$action;
			}
			$data[] = $sub_array;
		}
		$recordsTotal = $this->ModelPs->datatable("CALL `getTable`('".$requetedebase."')");
		$recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('".$requetedebasefilter."')");
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" => count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data,
		);
		return $this->response->setJSON($output);
	}
	// fin

	function editQuantite($id=0)
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}
		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$donnees_racc = 'SELECT racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,racc.INSTITUTION_ID,exe.EXECUTION_BUDGETAIRE_ID,mvt.DESC_MOUVEMENT_DEPENSE,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,ptba.ACTIVITES,ptba.PTBA_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,ptba.CODES_PROGRAMMATIQUE,racc.MONTANT_RACCROCHE,ptba.QT1,ptba.UNITE,exe.TRIMESTRE_ID,det.MOUVEMENT_DEPENSE_ID,racc.MONTANT_RACCROCHE_JURIDIQUE,racc.MONTANT_RACCROCHE_LIQUIDATION,racc.MONTANT_RACCROCHE_ORDONNANCEMENT,racc.MONTANT_RACCROCHE_PAIEMENT,racc.MONTANT_RACCROCHE_DECAISSEMENT FROM execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_raccrochage_activite_detail det ON det.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID=racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN execution_budgetaire exe ON exe.EXECUTION_BUDGETAIRE_ID=racc.EXECUTION_BUDGETAIRE_ID JOIN proc_mouvement_depense mvt ON mvt.MOUVEMENT_DEPENSE_ID=det.MOUVEMENT_DEPENSE_ID JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = '.$id;
		$donnees_racc="CALL `getList`('".$donnees_racc."')";
		$data['info']= $this->ModelPs->getRequeteOne($donnees_racc);

		$CODE_INSTITUTION=substr($data['info']['IMPUTATION'],0,2);
		$CODE_SOUS_TUTEL=substr($data['info']['IMPUTATION'],4,3);
		$inst=$this->getBindParms("INSTITUTION_ID,DESCRIPTION_INSTITUTION,CODE_INSTITUTION,TYPE_INSTITUTION_ID","inst_institutions","CODE_INSTITUTION='".$CODE_INSTITUTION."'",'INSTITUTION_ID ASC');
		$inst=str_replace("\'","'",$inst);
		$data['resultatinst']=$this->ModelPs->getRequeteOne($callpsreq, $inst);

		$INSTITUTION_ID=$data['resultatinst']['INSTITUTION_ID'];
		$getSousTutel=$this->getBindParms("SOUS_TUTEL_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL","inst_institutions_sous_tutel","INSTITUTION_ID=".$INSTITUTION_ID." AND CODE_SOUS_TUTEL='".$CODE_SOUS_TUTEL."'",'SOUS_TUTEL_ID ASC');
		$getSousTutel=str_replace("\'","'",$getSousTutel);
		$resultatinsttut=$this->ModelPs->getRequeteOne($callpsreq, $getSousTutel);
		$data['sous_tutel']= $resultatinsttut;
		$data['qte_vote'] = $data['info']['QT1'];
		return view('App\Modules\demande_new\Views\Exec_Budget_Raccrocher_Add_Qte_View',$data);
	}

	public function save($value='')
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}
		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$user = $this->getBindParms('USER_ID', 'user_users', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
		$getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);
		$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_RACCROCHAGE_ID');

		$rules = [
			'QTE_RACCROCHE' => [
				'label' => '',
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			]
		];
		$this->validation->setRules($rules);

		if($this->validation->withRequest($this->request)->run())
		{
			$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID;
			$MOUVEMENT_DEPENSE_ID=$this->request->getPost('MOUVEMENT_DEPENSE_ID');
			$QTE_RACCROCHE  =  $this->request->getPost('QTE_RACCROCHE');
			$UNITE = $this->request->getPost('UNITE');
			$COMMENTAIRE=$this->request->getPost('COMMENTAIRE');
			$USER_ID_QUANTITE = $getuser['USER_ID'];
			$TYPE_RACCROCHAGE_ID = 2;

			$COMMENTAIRE = str_replace("\n"," ",$COMMENTAIRE);
			$COMMENTAIRE = str_replace("\r"," ",$COMMENTAIRE);
			$COMMENTAIRE = str_replace("\t"," ",$COMMENTAIRE);
			$COMMENTAIRE = str_replace('"',' ',$COMMENTAIRE);
			$COMMENTAIRE = str_replace("'","\'",$COMMENTAIRE);
			$QTE_RACCROCHE = str_replace(".",",",$QTE_RACCROCHE);
			$COMMENTAIRE = (!empty($COMMENTAIRE)) ? $COMMENTAIRE : 'Aucune' ;

			$detail='SELECT EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID FROM execution_budgetaire_raccrochage_activite_detail det WHERE EXECUTION_BUDGETAIRE_RACCROCHAGE_ID='.$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID;
			$get_detail="CALL `getList`('".$detail."')";
			$id_detail= $this->ModelPs->getRequeteOne($get_detail);

			$existance = 'SELECT EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID FROM historique_raccrochage_activite_detail WHERE EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID='.$id_detail['EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID'].' AND TYPE_RACCROCHAGE_ID ='.$TYPE_RACCROCHAGE_ID;
			$existance="CALL `getList`('".$existance."')";
			$id_exist= $this->ModelPs->getRequeteOne($existance);

			if (empty($id_exist))
			{
		  	// INSERER HISTOIRIQUE
				$insertIntoDetail='historique_raccrochage_activite_detail';
				$columsinsert="EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID,USER_ID,TYPE_RACCROCHAGE_ID,OBSERVATION";
				$datacolumsinsert=$id_detail['EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID'].",".$USER_ID_QUANTITE.",".$TYPE_RACCROCHAGE_ID.",'".$COMMENTAIRE."'";
				$HISTO_RACCROCHAGE_ID =$this->save_all_table($insertIntoDetail,$columsinsert,$datacolumsinsert);
			}
			else
			{
		  	//mise a jour dans la table histo
				$table = 'historique_raccrochage_activite_detail';
				$where='EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID="'.$id_detail['EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID'].'"';
				$data='OBSERVATION = "'.$COMMENTAIRE.'",USER_ID='.$USER_ID_QUANTITE.',TYPE_RACCROCHAGE_ID ='.$TYPE_RACCROCHAGE_ID;
				$this->update_all_table($table,$data,$where);
			}
			//mise a jour dans la table execution_budgetaire_raccrochage_activite
			$table = 'execution_budgetaire_raccrochage_activite_new';
			$where='EXECUTION_BUDGETAIRE_RACCROCHAGE_ID="'.$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.'"';
			$data='QTE_RACCROCHE = "'.$QTE_RACCROCHE.'",UNITE="'.$UNITE.'"';
			$this->update_all_table($table,$data,$where);

			$data=['message' => ''.lang('messages_lang.racc_msg_succ').''];
			session()->setFlashdata('alert', $data);
			return redirect('demande_new/Exec_Budget_Raccrocher_Trim2');
		}
		else
		{
			return $this->editQuantite($EXECUTION_BUDGETAIRE_RACCROCHAGE_ID);
		}	
	}
}
?>