<?php 

/**
 * Auteur: Christa
 * Tache: Raccrochage des activités du deuxième trimestre
 * email: christa@mediabox.bi
 * date: le 11/01/2023
 */
namespace  App\Modules\demande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Raccrochage_Deuxieme_Trim extends BaseController
{
	
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	function index()
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}
		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }
		$data=$this->urichk();
		$ind=$this->indicateur_deuxieme_trimestre_new();
		$data['get_qte_phys']=$ind['get_qte_phys'];
		$data['get_pas_qte_phys']=$ind['get_pas_qte_phys'];
		$data['get_racrochet'] = $ind['get_racrochet'];
		$data['get_deja_racrochet'] = $ind['get_deja_racrochet'];
		$data['institutions_user']=$ind['getuser'];
		return view('App\Modules\demande_new\Views\Raccrochage_Deuxieme_Trim_List_View',$data);   
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	function listing($value = 0)
	{
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$userfiancier = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/login');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$institution=' AND execution_budgetaire_new.INSTITUTION_ID IN(SELECT `INSTITUTION_ID` FROM `user_affectaion` WHERE `USER_ID`='.$user_id.')';
		$CODE_INSTITUTION=$this->request->getPost('CODE_INSTITUTION');
		$CODE_SOUS_TUTEL=$this->request->getPost('CODE_SOUS_TUTEL');

		if (!empty($CODE_INSTITUTION))
		{
			$institution.=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "'.$CODE_INSTITUTION.'%"';
		}
		if (!empty($CODE_SOUS_TUTEL))
		{
			$institution.=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "'.$CODE_INSTITUTION.'00'.$CODE_SOUS_TUTEL.'%"';
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
		$order_column = array('ligne.CODE_NOMENCLATURE_BUDGETAIRE','LIBELLE','CREDIT_VOTE','TRANSFERTS_CREDITS','CREDIT_APRES_TRANSFERT' ,'ENG_BUDGETAIRE','ENG_JURIDIQUE', 'LIQUIDATION', 'ORDONNANCEMENT', 'PAIEMENT','DECAISSEMENT', 1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR ORDONNANCEMENT LIKE "%' . $var_search . '%" OR PAIEMENT LIKE "%' . $var_search . '%" OR DECAISSEMENT LIKE "%' . $var_search . '%" OR ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR  ENG_JURIDIQUE LIKE "%' . $var_search . '%" OR  LIQUIDATION LIKE "%' . $var_search . '%" OR  LIBELLE LIKE "%' . $var_search . '%" OR PAIEMENT LIKE "%' . $var_search . '%")') : '';

    	// Condition pour la requête principale
		$conditions = $critere.' '.$search.' '.$group.' '.$order_by.' '.$limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere.' '.$search.' '.$group;
		$requetedebase= 'SELECT CREDIT_VOTE,LIBELLE,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,DATE_DEMANDE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,EXECUTION_BUDGETAIRE_ID,IS_RACCROCHE,TRANSFERTS_CREDITS,CREDIT_APRES_TRANSFERT FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE IS_RACCROCHE = 0 AND (IS_TRANSFERTS=0 OR IS_TRANSFERTS=2) AND TRIMESTRE_ID=2 '.$institution;

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
			
			$imputation_row="";
			if ($userfiancier==1) 
			{
				$imputation_row= "<a  title='".lang('messages_lang.titr_bout_racc')."' href='".base_url("demande_new/Raccrochage_Deuxieme_Trim/getOne/".md5($row->EXECUTION_BUDGETAIRE_ID))."' >".$row->IMPUTATION."</a>";
			}
			else
			{
				$imputation_row=$row->IMPUTATION;
			}
			$LIBELLE = $LIBELLE." 
			<div class='modal fade' id='institution".$row->EXECUTION_BUDGETAIRE_ID."'>
			<div class='modal-dialog'>
			<div class='modal-content'>
			<div class='modal-body'>
			<center>
			<h5><b> ".$row->LIBELLE." </b></h5>
			</center>
			</div>
			<div class='modal-footer'>
			Quitter
			<button class='btn btn-primary btn-md' data-dismiss='modal'>
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

			$sub_array[]=$imputation_row;
			$sub_array[]=$LIBELLE;
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
				$BTN_TRAITE = "<a class='btn btn-primary btn-sm' title='".lang('messages_lang.titr_bout_racc')."' href='".base_url("demande_new/Raccrochage_Deuxieme_Trim/getOne/".md5($row->EXECUTION_BUDGETAIRE_ID))."' ><i class='fa fa-link text-light'></a>";
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

	//appel le view de raccrochage
	function getOne($EXECUTION_BUDGETAIRE_ID)
	{
		$db = db_connect();
		$data=$this->urichk();
		$session  = \Config\Services::session();
		$trimestre='T2';
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}
		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }
		
		if(empty($EXECUTION_BUDGETAIRE_ID))
		{
			return redirect('demande_new/Raccrochage_Deuxieme_Trim');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparams = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.CODE_INSTITUTION', '`user_affectaion` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID', 'USER_ID='.$user_id.'', '`DESCRIPTION_INSTITUTION` ASC');
        $data['institutions'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

		$somme_t_ligne=$this->getBindParms('(CASE WHEN `MOUVEMENT_DEPENSE_ID`=1 THEN `MONTANT_REALISE` WHEN `MOUVEMENT_DEPENSE_ID`=2 THEN `MONTANT_REALISE_JURIDIQUE` WHEN `MOUVEMENT_DEPENSE_ID`=3 THEN `MONTANT_REALISE_LIQUIDATION` WHEN `MOUVEMENT_DEPENSE_ID`=4 THEN `MONTANT_REALISE_ORDONNANCEMENT` WHEN `MOUVEMENT_DEPENSE_ID`=5 THEN `MONTANT_REALISE_DECAISSEMENT` WHEN `MOUVEMENT_DEPENSE_ID`=7 THEN `MONTANT_REALISE_PAIEMENT` END) as montant_realise_ligne','execution_budgetaire_tempo','md5(EXECUTION_BUDGETAIRE_ID)="'.$EXECUTION_BUDGETAIRE_ID.'"','1');
		$somme_t_ligne=str_replace('\"', '"', $somme_t_ligne);
		$montant_total_ligne = $this->ModelPs->getRequete($callpsreq, $somme_t_ligne);

		$total_ligne=0;
		foreach ($montant_total_ligne as $key_value)
		{
			$total_ligne=$total_ligne+$key_value->montant_realise_ligne;
		}

		$total_ligne_explode=explode(',', $total_ligne);
		$virgule=count($total_ligne_explode);
		if ($virgule==2)
		{
			$total_ligne1=$total_ligne_explode[0];
			$total_ligne2=$total_ligne_explode[1];

			$first_number=substr($total_ligne2,0,1);
			if ($first_number>=5)
			{
				$total_ligne1=$total_ligne1+1;
			}else
			{
				$total_ligne1=$total_ligne1;
			}
		}elseif ($virgule==1)
		{
			$total_ligne1=$total_ligne_explode[0];
		}

		$data['total_ligne']=$total_ligne1;
		$table="execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID AS IMPUTATION_ID,ORDONNANCEMENT,ENG_BUDGETAIRE,ENG_JURIDIQUE,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,LIQUIDATION,LIBELLE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,EXECUTION_BUDGETAIRE_ID';
		$where="md5(execution_budgetaire_new.EXECUTION_BUDGETAIRE_ID)='".$EXECUTION_BUDGETAIRE_ID."'";
		$orderby=' EXECUTION_BUDGETAIRE_ID DESC';
		$where=str_replace("\'", "'", $where);

		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$data['info']= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
		$data['id']=$data['info']['EXECUTION_BUDGETAIRE_ID'];

		$CODE_INSTITUTION=substr($data['info']['IMPUTATION'],0,2);
		$CODE_SOUS_TUTEL=substr($data['info']['IMPUTATION'],4,3);
		$bind_parmsinst=$this->getBindParms("INSTITUTION_ID,DESCRIPTION_INSTITUTION,CODE_INSTITUTION,TYPE_INSTITUTION_ID","inst_institutions","CODE_INSTITUTION='".$CODE_INSTITUTION."'",'INSTITUTION_ID ASC');
		$bind_parmsinst=str_replace("\'","'",$bind_parmsinst);
		$data['resultatinst']=$this->ModelPs->getRequeteOne($callpsreq, $bind_parmsinst);

		if (empty($data['resultatinst']))
		{
			$message_error = '<div style="width:100%;margin-top:0px;margin-bottom:0px" class="alert alert-danger">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.lang('messages_lang.aucune_info_trouve').' &nbsp;<b>'.$data['info']['IMPUTATION'].'</b>.
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" style="float: right;" class="close" data-dismiss="alert">&times;</a>
			</div>';
			$data=['message' => "".$message_error.""];
		    session()->setFlashdata('alert', $data);
		    return redirect('demande_new/Raccrochage_Deuxieme_Trim');
		}
		$INSTITUTION_ID=$data['resultatinst']['INSTITUTION_ID'];
		$bind_parmsinsttut=$this->getBindParms("SOUS_TUTEL_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL","inst_institutions_sous_tutel","INSTITUTION_ID=".$INSTITUTION_ID." AND CODE_SOUS_TUTEL='".$CODE_SOUS_TUTEL."'",'SOUS_TUTEL_ID ASC');
		$bind_parmsinsttut=str_replace("\'","'",$bind_parmsinsttut);
		$data['resultatinsttut']=$this->ModelPs->getRequeteOne($callpsreq, $bind_parmsinsttut);
		$SOUS_TUTEL_ID=!empty($data['resultatinsttut']) ? $data['resultatinsttut']['SOUS_TUTEL_ID']:0;
		$getmouvement = $this->getBindParms('MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_tempo','1',' MOUVEMENT_DEPENSE_ID ASC');
		$mouvenent = $this->ModelPs->getRequeteOne($callpsreq, $getmouvement);
		if(!empty($mouvenent))
		{
			if(($mouvenent['MOUVEMENT_DEPENSE_ID']==1) || ($mouvenent['MOUVEMENT_DEPENSE_ID']==2)||($mouvenent['MOUVEMENT_DEPENSE_ID']==3)|| ($mouvenent['MOUVEMENT_DEPENSE_ID']==4) || ($mouvenent['MOUVEMENT_DEPENSE_ID']==5) || ($mouvenent['MOUVEMENT_DEPENSE_ID']==6) || ($mouvenent['MOUVEMENT_DEPENSE_ID']==7) ){

				$getmouvement  = $this->getBindParms('SUM(MONTANT_REALISE) mont_realise,SUM(`MONTANT_REALISE_JURIDIQUE`) as jurd,SUM(`MONTANT_REALISE_LIQUIDATION`) as liq,SUM(`MONTANT_REALISE_ORDONNANCEMENT`) as ord,SUM(`MONTANT_REALISE_PAIEMENT`) as paie,SUM(`MONTANT_REALISE_DECAISSEMENT`) as decais,DESC_MOUVEMENT_DEPENSE,execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_tempo join proc_mouvement_depense on execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID=proc_mouvement_depense.MOUVEMENT_DEPENSE_ID ',' execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID='.$mouvenent['MOUVEMENT_DEPENSE_ID'].' AND md5(EXECUTION_BUDGETAIRE_ID)="'.$EXECUTION_BUDGETAIRE_ID.'"','execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID ASC');

				$getmouvement=str_replace('\"', '"', $getmouvement);
				$data['mouvement_montant']= $this->ModelPs->getRequeteOne($callpsreq, $getmouvement);
			}
		}
		$imputation=$data['info']['IMPUTATION'];
		$imputation_id=$data['info']['IMPUTATION_ID'];
		$get_montant_t = $this->getBindParms('SUM(MONTANT_RESTANT_T2) total,SUM(T2) T2','ptba','CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$imputation_id.'', '1');
		$get_montant_t=str_replace('\"','"',$get_montant_t);
		$data['montant_total'] = $this->ModelPs->getRequeteOne($callpsreq, $get_montant_t);

		$table="ptba JOIN execution_budgetaire_new ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID= ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='ACTIVITES,PTBA_ID,EXECUTION_BUDGETAIRE_ID,GRANDE_MASSE_BM,CODES_PROGRAMMATIQUE';
		$where="ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID =".$imputation_id."";
		$orderby=' CODE_NOMENCLATURE_BUDGETAIRE_ID DESC';
		$where=str_replace("\'", "'", $where);

		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$data['demande_exec']= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

		if(empty($data['demande_exec']))
		{
			return redirect('demande_new/Raccrochage_Deuxieme_Trim');
		}

		$bindparam = $this->getBindParms('EXECUTION_ID_TEMPO,execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID,EXECUTION_ID_TEMPO,MONTANT_REALISE,ptba.ACTIVITES,DOC_RACCROCHE,COMMENTAIRE,prog.INTITULE_PROGRAMME,act.LIBELLE_ACTION,PREUVE,MARCHE_PUBLIQUE,inst_institutions.DESCRIPTION_INSTITUTION,proc_mouvement_depense.DESC_MOUVEMENT_DEPENSE,MONTANT_REALISE_JURIDIQUE,MONTANT_REALISE_LIQUIDATION,MONTANT_REALISE_ORDONNANCEMENT,MONTANT_REALISE_PAIEMENT,MONTANT_REALISE_DECAISSEMENT','execution_budgetaire_tempo  join ptba on execution_budgetaire_tempo.ID_PTBA=ptba.PTBA_ID join inst_institutions on inst_institutions.INSTITUTION_ID=execution_budgetaire_tempo.INSTITUTION_ID join proc_mouvement_depense on proc_mouvement_depense.MOUVEMENT_DEPENSE_ID=execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID','md5(EXECUTION_BUDGETAIRE_ID)="'.$EXECUTION_BUDGETAIRE_ID.'"','EXECUTION_ID_TEMPO DESC');

		$bindparam=str_replace('\"','"',$bindparam);
		$data['info_tableau']= $this->ModelPs->getRequete($callpsreq, $bindparam);

		$table_p="ptba";
		$columnselect='ACTIVITES,PTBA_ID';
		$where="ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$imputation_id." AND ptba.T1 > 0 AND ptba.PTBA_ID NOT IN(SELECT `ID_PTBA` FROM execution_budgetaire_tempo)";
		$orderby=' ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID DESC';
		$where=str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table_p),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$data['activite']= $this->ModelPs->getRequete($callpsreq, $bindparams);

		$bindparam = $this->getBindParms('EXECUTION_ID_TEMPO,ptba.ACTIVITES','execution_budgetaire_tempo  join ptba on execution_budgetaire_tempo.ID_PTBA=ptba.PTBA_ID', '1','EXECUTION_ID_TEMPO  asc');
		$data['infoactivit']= $this->ModelPs->getRequeteOne($callpsreq, $bindparam);

		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$data['profil']=$profil;

		$mvt_depense_params=$this->getBindParms('MOUVEMENT_DEPENSE_ID,DESC_MOUVEMENT_DEPENSE','proc_mouvement_depense', 'MOUVEMENT_DEPENSE_ID !=6','MOUVEMENT_DEPENSE_ID asc');
		$data['mvt_depense']= $this->ModelPs->getRequete($callpsreq, $mvt_depense_params);
		return view('App\Modules\demande_new\Views\Raccrochage_Deuxieme_Trim_View',$data);
	}

	//récupère les infos par activité
	function get_montant($PTBA_ID)
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

		$dataa=$this->converdate();
		$debut=$dataa['debut'];
		$fin=$dataa['fin'];
		$tranche=$dataa['CODE_TRANCHE'];
		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');	
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$table="ptba JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		
		$columnselect='MONTANT_RESTANT_T2 as montant_restant,inst.CODE_INSTITUTION AS CODE_MINISTERE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,PTBA_ID,T2 as tranche,QT1 as qte,UNITE,act.CODE_ACTION,act.LIBELLE_ACTION,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME';
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
		$montant_explode=explode(',', $activiteinfo['montant_restant']);
		$virgule=count($montant_explode);
		if ($virgule==2)
		{
			$montant1=$montant_explode[0];
			$montant2=$montant_explode[1];

			$first_number=substr($montant2,0,1);
			if ($first_number>=5)
			{
				$montant1=$montant1+1;
			}else
			{
				$montant1=$montant1;
			}
		}elseif ($virgule==1)
		{
			$montant1=$montant_explode[0];
		}
		$montant_format=number_format($montant1,'0',',',' ');
		$output = array(
			"mont" => $montant_format,//$activiteinfo['tranche'],
			"QUANTITE" => $activiteinfo['qte'],
			"UNITE" => $activiteinfo['UNITE'],
			"MONTANT" => $montant1,
			"CODE_ACTION" => $activiteinfo['CODE_ACTION'],
			"CODE_PROGRAMME" => $activiteinfo['CODE_PROGRAMME'],
			"ACTION" => $activiteinfo['LIBELLE_ACTION'],
			"PROGRAMME" => $activiteinfo['INTITULE_PROGRAMME'],
			"INSTITUTION_ID" => $ministere['INSTITUTION_ID'],
			"DESCRIPTION_INSTITUTION" => $ministere['DESCRIPTION_INSTITUTION'],
			"MONTANT_RESTANT" => $montant1
		);
		return $this->response->setJSON($output);
	}

	/**
	 * fonction pour enregistrer des informations temporaiment
	 */
	function saveinfo_activite()
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

		$demande = $this->request->getPost('demande');
		$Mouvement_id = $this->request->getPost('Mouvement_code');

		$rules = [
			'PTBA_ID' => [
	        'label' => '',
	        'rules' => 'required',
	        'errors' => [
	        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
	    	]
        ],
        'Mouvement_code' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'MARCHE_PUBLIC' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'montant_realise' => [
	        'label' => '',
	        'rules' => 'required',
	        'errors' => [
	        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
	    	]
    	]
		];
		

		if ($Mouvement_id==2)
		{
			$rules = [
				'montant_realise_jurid' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'numero_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}elseif ($Mouvement_id==3)
		{
			$rules = [
				'montant_realise_jurid' => [
		        'label' => '',
		        'rules' => 'required',
		        'errors' => [
		        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
		    	]
	        	],
	        	'montant_realise_liq' => [
		        'label' => '',
		        'rules' => 'required',
		        'errors' => [
		        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
		    	]
	        	],
	        	'numero_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}elseif ($Mouvement_id==4)
		{
			$rules = [
				'montant_realise_jurid' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_liq' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_ord' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'numero_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}elseif ($Mouvement_id==5)
		{
			$rules = [
				'montant_realise_jurid' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_liq' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_ord' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_paie' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_decais' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'numero_decaiss' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_decais' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}elseif ($Mouvement_id==7)
		{
			$rules = [
				'montant_realise_jurid' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_liq' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_ord' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_paie' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'numero_decaiss' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_decais' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}

		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
    {
      $PTBA_ID = $this->request->getPost('PTBA_ID');
			$montant_vote = $this->request->getPost('montant_vote');
			$montant_realise = $this->request->getPost('montant_realise');
			$COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
			$MARCHE_PUBLIC = $this->request->getPost('MARCHE_PUBLIC');
			$programes_code = $this->request->getPost('programes_code');
			$actions = $this->request->getPost('actions');
			$institutions = $this->request->getPost('Institutions');
			$numero_bon = $this->request->getPost('numero_bon');
			$date_bon = $this->request->getPost('date_bon');
			$numero_decaiss = $this->request->getPost('numero_decaiss');
			$date_decais = $this->request->getPost('date_decais');

			$numero_bon=trim($numero_bon);
			$numero_decaiss=trim($numero_decaiss);
			$COMMENTAIRE=trim($COMMENTAIRE);

			$COMMENTAIRE = str_replace("\n"," ",$COMMENTAIRE);
			$COMMENTAIRE = str_replace("\r"," ",$COMMENTAIRE);
			$COMMENTAIRE = str_replace("\t"," ",$COMMENTAIRE);
			$COMMENTAIRE = str_replace('"',' ',$COMMENTAIRE);
			$COMMENTAIRE = str_replace("'",' ',$COMMENTAIRE);

			$numero_bon = str_replace("\n"," ",$numero_bon);
			$numero_bon = str_replace("\r"," ",$numero_bon);
			$numero_bon = str_replace("\t"," ",$numero_bon);
			$numero_bon = str_replace('"',' ',$numero_bon);
			$numero_bon = str_replace("'",' ',$numero_bon);

			$numero_decaiss = str_replace("\n"," ",$numero_decaiss);
			$numero_decaiss = str_replace("\r"," ",$numero_decaiss);
			$numero_decaiss = str_replace("\t"," ",$numero_decaiss);
			$numero_decaiss = str_replace('"',' ',$numero_decaiss);
			$numero_decaiss = str_replace("'",' ',$numero_decaiss);

			$nbre_bon=strlen($numero_bon);
			$nbre_td=strlen($numero_decaiss);
			
			
			$montant_realise_jurid = $this->request->getPost('montant_realise_jurid');
			$montant_realise_liq = $this->request->getPost('montant_realise_liq');
			$montant_realise_ord = $this->request->getPost('montant_realise_ord');
			$montant_realise_paie = $this->request->getPost('montant_realise_paie');
			$montant_realise_decais = $this->request->getPost('montant_realise_decais');		
			
			$doc_raccroche=$_FILES["doc_raccroche"]["name"];
			$PREUVE=$_FILES["PREUVE"]["name"];

			if ($doc_raccroche!='')
			{
				$DOCUMENT=$this->uploadFile('doc_raccroche','doc_raccroches',$doc_raccroche);
			}else
			{
				$DOCUMENT='';
			}
			if ($PREUVE!='')
			{
				$DOCUMENT_PREUVE=$this->uploadFile('PREUVE','doc_preuves',$PREUVE);
			}else
			{
				$DOCUMENT_PREUVE='';
			}

			if(empty($montant_vote))
			{
				$montant_vote = 0;
			}
			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$table="op_tranches";
			$columnselect='TRANCHE_ID';
			$where="CODE_TRANCHE='T2'";
			$orderby='TRANCHE_ID ASC';
			$where=str_replace("\'", "'", $where);
			$db = db_connect();
			$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
			$bindparams=str_replace("\'", "'", $bindparams);
			$tranche_id= 2;//$this->ModelPs->getRequeteOne($callpsreq, $bindparams);

			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$table_tempo="execution_budgetaire_tempo";
			$column='ID_PTBA';
			$cdtion="ID_PTBA=".$PTBA_ID;
			$orderby='ID_PTBA DESC';
			$cdtion=str_replace("\'", "'", $cdtion);
			$db = db_connect();
			$ptba_params =[$db->escapeString($column),$db->escapeString($table_tempo),$db->escapeString($cdtion),$db->escapeString($orderby)];
			$ptba_params=str_replace("\'", "'", $ptba_params);
			$ptbainfo= $this->ModelPs->getRequeteOne($callpsreq, $ptba_params);

			$column_select='sum(MONTANT_REALISE)';
			$crit="1";
			$orderby='ID_PTBA DESC';
			$crit=str_replace("\'", "'", $crit);
			$db = db_connect();
			$params_somme =[$db->escapeString($column_select),$db->escapeString($table_tempo),$db->escapeString($crit),$db->escapeString($orderby)];
			$params_somme=str_replace("\'", "'", $params_somme);
			$somme= $this->ModelPs->getRequeteOne($callpsreq, $params_somme);

			$ptba=$this->getBindParms('ID_PTBA','execution_budgetaire_tempo','ID_PTBA='.$PTBA_ID,'ID_PTBA');
			$ptba_id=$this->ModelPs->getRequeteOne($callpsreq, $ptba);

			if (empty($ptba_id['ID_PTBA']))
			{
				if (!empty($institutions) && !empty($programes_code) && !empty($actions) && !empty($montant_vote))
				{
					if(($Mouvement_id==1) || ($Mouvement_id==2) || ($Mouvement_id==4) || ($Mouvement_id==3))
					{
						$columsinsert="EXECUTION_BUDGETAIRE_ID,ID_PTBA,MONTANT_REALISE,TRANCHE_ID,USER_ID,DOC_RACCROCHE,COMMENTAIRE,INSTITUTION_ID,CODE_PROGRAMME,CODE_ACTION,MOUVEMENT_DEPENSE_ID,PREUVE,MARCHE_PUBLIQUE,NUMERO_BON_ENGAGEMENT,DATE_BON_ENGAGEMENT,MONTANT_REALISE_JURIDIQUE,MONTANT_REALISE_LIQUIDATION,MONTANT_REALISE_ORDONNANCEMENT";
						$MONTANT_TRANSFERT=!empty($MONTANT_TRANSFERT)?($MONTANT_TRANSFERT):0;

						$datacolumsinsert=$demande.",".$PTBA_ID.",".$montant_realise.",".$tranche_id.",".$user_id.",'".$DOCUMENT."','".$COMMENTAIRE."',".$institutions.",'".$programes_code."','".$actions."',".$Mouvement_id.",'".$DOCUMENT_PREUVE."',".$MARCHE_PUBLIC.",'".$numero_bon."','".$date_bon."','".$montant_realise_jurid."','".$montant_realise_liq."','".$montant_realise_ord."' ";
						$this->save_info($columsinsert,$datacolumsinsert);

						return $this->getOne(md5($demande));
					}
					else
					{
						// print_r($montant_realise_decais);die();
						$columsinsert="EXECUTION_BUDGETAIRE_ID,ID_PTBA,MONTANT_REALISE,TRANCHE_ID,USER_ID,DOC_RACCROCHE,COMMENTAIRE,INSTITUTION_ID,CODE_PROGRAMME,CODE_ACTION,MOUVEMENT_DEPENSE_ID,PREUVE,MARCHE_PUBLIQUE,NUMERO_TITRE_DECAISSEMNT,DATE_TITRE_DECAISSEMENT,MONTANT_REALISE_JURIDIQUE,MONTANT_REALISE_LIQUIDATION,MONTANT_REALISE_ORDONNANCEMENT,MONTANT_REALISE_PAIEMENT,MONTANT_REALISE_DECAISSEMENT";
						$datacolumsinsert=$demande.",".$PTBA_ID.",".$montant_realise.",".$tranche_id.",".$user_id.",'".$DOCUMENT."','".$COMMENTAIRE."',".$institutions.",'".$programes_code."','".$actions."',".$Mouvement_id.",'".$DOCUMENT_PREUVE."',".$MARCHE_PUBLIC.",'".$numero_decaiss."','".$date_decais."',".$montant_realise_jurid.",".$montant_realise_liq.",".$montant_realise_ord.",".$montant_realise_paie.",".$montant_realise_decais." ";
						$this->save_info($columsinsert,$datacolumsinsert);

						return $this->getOne(md5($demande));
					}
				}	
			}else
			{
				return $this->getOne(md5($demande));
			}
  	}
  	else
  	{
			return $this->getOne(md5($demande));
  	}
	}

	function save_info($columsinsert,$datacolumsinsert)
	{
	  // $columsinsert: Nom des colonnes separe par,
	  // $datacolumsinsert : les donnees a inserer dans les colonnes
		$table='execution_budgetaire_tempo';
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$this->ModelPs->createUpdateDelete($insertReqAgence,$bindparms);
	}
	///enregistrement d'une activité dans tempo
	public function save_infoactivite($columsinsert,$datacolumsinsert)
	{
	  // $columsinsert: Nom des colonnes separe par,
	  // $datacolumsinsert : les donnees a inserer dans les colonnes
		$table='execution_budgetaire_raccrochage_activite_new';
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$id_raccrochage=$this->ModelPs->getRequeteOne($insertReqAgence, $bindparms);
		$racc=$id_raccrochage['id'];
	}

	/**
	 * fonction pour enregistrer dans la base les information et les montant ,quantite correspondant
	 */
	function enregister()
	{
		$demande = $this->request->getPost('demande_id');
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
		$table="execution_budgetaire_tempo";
		$columnselect='*';
		$where="EXECUTION_BUDGETAIRE_ID=".$demande;
		$orderby='EXECUTION_ID_TEMPO DESC';
		$where=str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$infos_tempo= $this->ModelPs->getRequete($callpsreq, $bindparams);
				
		$montant_exec=$this->getBindParms('ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT','execution_budgetaire_new','EXECUTION_BUDGETAIRE_ID='.$demande,' EXECUTION_BUDGETAIRE_ID');
		$info= $this->ModelPs->getRequeteOne($callpsreq, $montant_exec);

		$mouvement=$this->getBindParms('SUM(`MONTANT_REALISE`) as mont_realise,SUM(`MONTANT_REALISE_JURIDIQUE`) as jurd,SUM(`MONTANT_REALISE_LIQUIDATION`) as liq,SUM(`MONTANT_REALISE_ORDONNANCEMENT`) as ord,SUM(`MONTANT_REALISE_PAIEMENT`) as paie,SUM(`MONTANT_REALISE_DECAISSEMENT`) as decais','execution_budgetaire_tempo',' EXECUTION_BUDGETAIRE_ID='.$demande,'EXECUTION_BUDGETAIRE_ID');
		$mvt_depense=$this->ModelPs->getRequeteOne($callpsreq, $mouvement);


		if ($mvt_depense['mont_realise']!=$info['ENG_BUDGETAIRE'])
		{
			$data=['message' => ''.lang('messages_lang.racc_egal_budget').''];
			session()->setFlashdata('alert', $data);
			return $this->getOne(md5($demande));
		}
		elseif ($mvt_depense['jurd']!=$info['ENG_JURIDIQUE'])
		{
			$data=['message' => ''.lang('messages_lang.racc_egal_jur').''];
			session()->setFlashdata('alert', $data);
			return $this->getOne(md5($demande));
		}
		elseif ($mvt_depense['liq']!=$info['LIQUIDATION'])
		{
			$data=['message' => ''.lang('messages_lang.racc_egal_liquid').''];
			session()->setFlashdata('alert', $data);
			return $this->getOne(md5($demande));
		}
		elseif ($mvt_depense['ord']!=$info['ORDONNANCEMENT'])
		{
			$data=['message' => ''.lang('messages_lang.racc_egal_ordo').''];
			session()->setFlashdata('alert', $data);
			return $this->getOne(md5($demande));
		}
		elseif ($mvt_depense['paie']!=$info['PAIEMENT'])
		{
			$data=['message' => ''.lang('messages_lang.racc_egal_pay').''];
			session()->setFlashdata('alert', $data);
			return $this->getOne(md5($demande));
		}
		elseif ($mvt_depense['decais']!=$info['DECAISSEMENT'])
		{
			$data=['message' => ''.lang('messages_lang.racc_egal_decais').''];
			session()->setFlashdata('alert', $data);
			return $this->getOne(md5($demande));
		}

		$MONTANT_RACCROCHE_JURIDIQUE=0;
		$MONTANT_RACCROCHE_LIQUIDATION=0;
		$MONTANT_RACCROCHE_ORDONNANCEMENT=0;
		$MONTANT_RACCROCHE_PAIEMENT=0;
		$MONTANT_RACCROCHE_DECAISSEMENT=0;
		
		foreach($infos_tempo as $infoact)
		{
			$EXECUTION_BUDGETAIRE_ID=$infoact->EXECUTION_BUDGETAIRE_ID;
			$ID_PTBA=$infoact->ID_PTBA;
			$TRANCHE_ID=$infoact->TRANCHE_ID;
			$USER_ID=$infoact->USER_ID;
			$DOC_RACCROCHE=$infoact->DOC_RACCROCHE;
			$COMMENTAIRE=$infoact->COMMENTAIRE;
			$INSTITUTION_ID=$infoact->INSTITUTION_ID;
			$MOUVEMENT_DEPENSE_ID=$infoact->MOUVEMENT_DEPENSE_ID;
			$PREUVE=$infoact->PREUVE;
			$MARCHE_PUBLIQUE=$infoact->MARCHE_PUBLIQUE;
			$NUMERO_BON_ENGAGEMENT=$infoact->NUMERO_BON_ENGAGEMENT;
			$DATE_BON_ENGAGEMENT=$infoact->DATE_BON_ENGAGEMENT;
			$NUMERO_TITRE_DECAISSEMNT=$infoact->NUMERO_TITRE_DECAISSEMNT;
			$DATE_TITRE_DECAISSEMENT=$infoact->DATE_TITRE_DECAISSEMENT;

			$MONTANT_REALISE=$infoact->MONTANT_REALISE;
			$MONTANT_RACCROCHE_JURIDIQUE=$infoact->MONTANT_REALISE_JURIDIQUE;
			$MONTANT_RACCROCHE_LIQUIDATION=$infoact->MONTANT_REALISE_LIQUIDATION;
			$MONTANT_RACCROCHE_ORDONNANCEMENT=$infoact->MONTANT_REALISE_ORDONNANCEMENT;
			$MONTANT_RACCROCHE_PAIEMENT=$infoact->MONTANT_REALISE_PAIEMENT;
			$MONTANT_RACCROCHE_DECAISSEMENT=$infoact->MONTANT_REALISE_DECAISSEMENT;
			$MONTANT_TRANSFERT=$infoact->MONTANT_TRANSFERT;

			$MONTANT_REALISE=!empty($MONTANT_REALISE)?$MONTANT_REALISE:0;
			$MONTANT_RACCROCHE_JURIDIQUE=!empty($MONTANT_RACCROCHE_JURIDIQUE)?$MONTANT_RACCROCHE_JURIDIQUE:0;
			$MONTANT_RACCROCHE_LIQUIDATION=!empty($MONTANT_RACCROCHE_LIQUIDATION)?$MONTANT_RACCROCHE_LIQUIDATION:0;
			$MONTANT_RACCROCHE_ORDONNANCEMENT=!empty($MONTANT_RACCROCHE_ORDONNANCEMENT)?$MONTANT_RACCROCHE_ORDONNANCEMENT:0;
			$MONTANT_RACCROCHE_PAIEMENT=!empty($MONTANT_RACCROCHE_PAIEMENT)?$MONTANT_RACCROCHE_PAIEMENT:0;
			$MONTANT_RACCROCHE_DECAISSEMENT=!empty($MONTANT_RACCROCHE_DECAISSEMENT)?$MONTANT_RACCROCHE_DECAISSEMENT:0;

			$TYPE_RACCROCHAGE_ID=1;
			$TYPE_DOCUMENT_ID=1;
			$PATH_BON_ENGAGEMENT='';
			$PATH_TITRE_DECAISSEMENT='';

			if($MOUVEMENT_DEPENSE_ID==1 || $MOUVEMENT_DEPENSE_ID==2 || $MOUVEMENT_DEPENSE_ID==3 || $MOUVEMENT_DEPENSE_ID==4)
			{
				$PATH_BON_ENGAGEMENT=$DOC_RACCROCHE;

				$columsinsert="EXECUTION_BUDGETAIRE_ID,PTBA_ID,MONTANT_RACCROCHE,TRIMESTRE_ID,INSTITUTION_ID,MARCHE_PUBLIQUE,MONTANT_RACCROCHE_JURIDIQUE,MONTANT_RACCROCHE_LIQUIDATION,MONTANT_RACCROCHE_ORDONNANCEMENT,MONTANT_RACCROCHE_PAIEMENT,MONTANT_RACCROCHE_DECAISSEMENT,PREUVE,NUMERO_BON_ENGAGEMENT,DATE_BON_ENGAGEMENT";

				$datacolumsinsert=$EXECUTION_BUDGETAIRE_ID.",".$ID_PTBA.",".$MONTANT_REALISE.",".$TRANCHE_ID.",".$INSTITUTION_ID.",".$MARCHE_PUBLIQUE.",".$MONTANT_RACCROCHE_JURIDIQUE.",".$MONTANT_RACCROCHE_LIQUIDATION.",".$MONTANT_RACCROCHE_ORDONNANCEMENT.",".$MONTANT_RACCROCHE_PAIEMENT.",".$MONTANT_RACCROCHE_DECAISSEMENT.",'".$PREUVE."','".$NUMERO_BON_ENGAGEMENT."','".$DATE_BON_ENGAGEMENT."'";
			}
			else 
			if($MOUVEMENT_DEPENSE_ID==5 || $MOUVEMENT_DEPENSE_ID==7)
			{
				$TYPE_DOCUMENT_ID=2;
				$PATH_TITRE_DECAISSEMENT=$DOC_RACCROCHE;

				$columsinsert="EXECUTION_BUDGETAIRE_ID,PTBA_ID,MONTANT_RACCROCHE,TRIMESTRE_ID,INSTITUTION_ID,MARCHE_PUBLIQUE,MONTANT_RACCROCHE_JURIDIQUE,MONTANT_RACCROCHE_LIQUIDATION,MONTANT_RACCROCHE_ORDONNANCEMENT,MONTANT_RACCROCHE_PAIEMENT,MONTANT_RACCROCHE_DECAISSEMENT,PREUVE";

				$datacolumsinsert=$EXECUTION_BUDGETAIRE_ID.",".$ID_PTBA.",".$MONTANT_REALISE.",".$TRANCHE_ID.",".$INSTITUTION_ID.",".$MARCHE_PUBLIQUE.",".$MONTANT_RACCROCHE_JURIDIQUE.",".$MONTANT_RACCROCHE_LIQUIDATION.",".$MONTANT_RACCROCHE_ORDONNANCEMENT.",".$MONTANT_RACCROCHE_PAIEMENT.",".$MONTANT_RACCROCHE_DECAISSEMENT.",'".$PREUVE."'";
			}

			
			$table='execution_budgetaire_raccrochage_activite_new';
			$bindparms=[$table,$columsinsert,$datacolumsinsert];
			$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
			$id_raccrochage=$this->ModelPs->getRequeteOne($insertReqAgence, $bindparms);
			$racc=$id_raccrochage['id'];

			$table_det="execution_budgetaire_raccrochage_activite_detail";

			$columDetail="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,MOUVEMENT_DEPENSE_ID,TRIMESTRE_ID,MONTANT_RACCROCHE,MONTANT_RACCROCHE_JURIDIQUE,MONTANT_RACCROCHE_LIQUIDATION,MONTANT_RACCROCHE_ORDONNANCEMENT,MONTANT_RACCROCHE_PAIEMENT,MONTANT_RACCROCHE_DECAISSEMENT,PATH_TITRE_DECAISSEMENT, DATE_TITRE_DECAISSEMENT";

			$datainsertDetail=$racc.",".$MOUVEMENT_DEPENSE_ID.",".$TRANCHE_ID.",".$MONTANT_REALISE.",".$MONTANT_RACCROCHE_JURIDIQUE.",".$MONTANT_RACCROCHE_LIQUIDATION.",".$MONTANT_RACCROCHE_ORDONNANCEMENT.",".$MONTANT_RACCROCHE_PAIEMENT.",".$MONTANT_RACCROCHE_DECAISSEMENT.",'".$PATH_TITRE_DECAISSEMENT."','".$DATE_TITRE_DECAISSEMENT."' ";

			$id_detail=$this->save_all_table($table_det,$columDetail,$datainsertDetail);
		
			//verification pour ne pas enregistrer 2 fois
			$exist_histo=$this->getBindParms('EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID ','historique_raccrochage_activite_detail','EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID='.$id_detail.' AND TYPE_RACCROCHAGE_ID='.$TYPE_RACCROCHAGE_ID,'EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID');
			$exist=$this->ModelPs->getRequeteOne($callpsreq,$exist_histo);

			if (empty($exist))
			{
				$this->historique_infos_sup($racc,$PATH_BON_ENGAGEMENT);
				$this->historique_raccrochage($id_detail,$USER_ID,$MOUVEMENT_DEPENSE_ID,$TYPE_RACCROCHAGE_ID,$COMMENTAIRE);
			}
			
			$table_ptba='ptba';
			$conditions='PTBA_ID='.$ID_PTBA;
			if($MOUVEMENT_DEPENSE_ID==5)
			{
				$getmontantvote  = $this->getBindParms('MONTANT_RESTANT_T2', 'ptba','PTBA_ID='.$ID_PTBA,' PTBA_ID ASC');
				$montantv= $this->ModelPs->getRequeteOne($callpsreq, $getmontantvote);
				$str_montant_restant=!empty(trim($montantv['MONTANT_RESTANT_T2']))?trim($montantv['MONTANT_RESTANT_T2']):0;
				$str_montant_decaissement=!empty(trim($MONTANT_RACCROCHE_DECAISSEMENT))?trim($MONTANT_RACCROCHE_DECAISSEMENT):0;
				$montant_restant=floatval($str_montant_restant);
				$montant_decaissement=floatval($str_montant_decaissement);
				$montant_restant=$montant_restant-$montant_decaissement;

				if ($montant_restant<0)
				{
					$data=['message' => ''.lang('messages_lang.rest_inf_zero').''];
					session()->setFlashdata('alert', $data);
					return $this->getOne(md5($EXECUTION_BUDGETAIRE_ID));
				}

				$donnees_modif='MONTANT_RESTANT_T2='.$montant_restant;
				$ps = "CALL `updateData`(?,?,?);";
				$this->update_all_table($table_ptba,$donnees_modif,$conditions);
			}

			$TYPE_OPERATION_ID=4;
			
		}
		
		$table='execution_budgetaire_new';
		$conditions='EXECUTION_BUDGETAIRE_ID='.$demande ;
		$datatomodifie= 'IS_RACCROCHE=1';
		$this->update_all_table($table,$datatomodifie,$conditions);
		################################################
		$critere =" EXECUTION_BUDGETAIRE_ID=".$demande;
		$table =$db->escapeString("execution_budgetaire_tempo");
		$bindparams =[$db->escapeString($table),$db->escapeString($critere)] ;
		$deleteRequete = "CALL `deleteData`(?,?);";
		$info=$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
		########################################################
		$data=['message' => ''.lang('messages_lang.racc_save_success').''];
		session()->setFlashdata('alert', $data);
		return redirect('demande_new/Raccrochage_Deuxieme_Trim');
	}

		//enregistrement dans la table historique
	function historique_raccrochage($DETAIL_ID,$USER_ID,$MOUVEMENT_DEPENSE_ID,$TYPE_RACCROCHAGE_ID,$OBSERVATION)
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

		$OBSERVATION=str_replace("'"," ",$OBSERVATION);
		$OBSERVATION=str_replace('"','',$OBSERVATION);
		$table="historique_raccrochage_activite_detail";
		$columnselect="EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID,USER_ID,TYPE_RACCROCHAGE_ID,OBSERVATION";
		$datacolumsinsert=$DETAIL_ID.",".$USER_ID.",".$TYPE_RACCROCHAGE_ID.",'".$OBSERVATION."'";
		$bindparms=[$table,$columnselect,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$this->ModelPs->createUpdateDelete($insertReq,$bindparms);
	}

	//enregistrement dans la table des infos supplementaires
	function historique_infos_sup($EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,$PATH_BON_ENGAGEMENT)
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

		$table="execution_budgetaire_raccrochage_activite_info_suppl_new";
		$columnselect="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,PATH_BON_ENGAGEMENT";

		$datacolumsinsert=$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.',"'.$PATH_BON_ENGAGEMENT.'"';

		$this->save_all_table($table,$columnselect,$datacolumsinsert);

	}
	/* Debut Gestion update table de la demande detail*/
	function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	/* Fin Gestion update table de la demande detail*/

	//fonction pour faire la suppresion dans la table tempo
	public function deleteData()
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

		$id= $this->request->uri->getSegment(4); 
		$demande= $this->request->uri->getSegment(5);
		$db = db_connect();     
		$statut = 0;
		
		$deleteRequete = "CALL `deleteData`(?,?);";
		$critere =$db->escapeString("EXECUTION_ID_TEMPO =".$id);
		$table =$db->escapeString("execution_budgetaire_tempo");
		$bindparams =[$db->escapeString($table),$db->escapeString($critere)] ;
		if ($this->ModelPs->createUpdateDelete($deleteRequete, $bindparams)) {
       
			$data=['message' => ''.lang('messages_lang.message_success_suppr').''];
			session()->setFlashdata('alert', $data);
			return $this->getOne(md5($demande));
		}else{

			return  false;
		}
	}

	/*
	* modification d'une activité dans la table tempo
	*/
	function modifier($id,$id_demande)
	{
		$db = db_connect();
		$data=$this->urichk(); 

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

		$callpsreq = "CALL getRequete(?,?,?,?);";

		$donnees = 'SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID AS IMPUTATION_ID, EXECUTION_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION, LIBELLE, CREDIT_VOTE, ENG_BUDGETAIRE, ENG_JURIDIQUE, LIQUIDATION, ORDONNANCEMENT, PAIEMENT, DECAISSEMENT, ANNEE_BUDGETAIRE_ID, DATE_DEMANDE, IS_RACCROCHE, MOUVEMENT_DEPENSE_ID, IS_TRANSFERTS, INSTITUTION_ID, SOUS_TUTEL_ID FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID = '.$id_demande;

		$donnees="CALL getList('".$donnees."')";

		$data['info']= $this->ModelPs->getRequeteOne($donnees);
		$CODE_INSTITUTION=substr($data['info']['IMPUTATION'],0,2);
		$CODE_SOUS_TUTEL=substr($data['info']['IMPUTATION'],4,3);

		$inst = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION,tipe.DESC_TYPE_INSTITUTION,inst.TYPE_INSTITUTION_ID FROM inst_institutions inst JOIN inst_types_institution tipe ON tipe.TYPE_INSTITUTION_ID=inst.TYPE_INSTITUTION_ID WHERE CODE_INSTITUTION="'.$CODE_INSTITUTION.'"';
		$inst="CALL getList('".$inst."')";
		$data['resultatinst']=$this->ModelPs->getRequeteOne($inst);

		$INSTITUTION_ID=$data['resultatinst']['INSTITUTION_ID'];
		$getSousTutel=$this->getBindParms("SOUS_TUTEL_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL","inst_institutions_sous_tutel","INSTITUTION_ID=".$INSTITUTION_ID." AND CODE_SOUS_TUTEL='".$CODE_SOUS_TUTEL."'",'SOUS_TUTEL_ID ASC');
		$getSousTutel=str_replace("\'","'",$getSousTutel);
		$resultatinsttut=$this->ModelPs->getRequeteOne($callpsreq, $getSousTutel);
		$data['sous_tutel']= $resultatinsttut;
		
		$imputation=$data['info']['IMPUTATION'];
		$imputation_id=$data['info']['IMPUTATION_ID'];

		$get_montant_t = $this->getBindParms('SUM(MONTANT_RESTANT_T2) total,SUM(T2) T2','ptba','CODE_NOMENCLATURE_BUDGETAIRE_ID='.$imputation_id.'','1');
		$get_montant_t=str_replace('\"','"',$get_montant_t);
		$data['montant_total'] = $this->ModelPs->getRequeteOne($callpsreq, $get_montant_t);

		$getmouvement = $this->getBindParms('MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_tempo','1',' MOUVEMENT_DEPENSE_ID ASC');
		$mouvenent = $this->ModelPs->getRequeteOne($callpsreq, $getmouvement);

		if(!empty($mouvenent))
		{
			if(($mouvenent['MOUVEMENT_DEPENSE_ID']==1) || ($mouvenent['MOUVEMENT_DEPENSE_ID']==2)||($mouvenent['MOUVEMENT_DEPENSE_ID']==3)|| ($mouvenent['MOUVEMENT_DEPENSE_ID']==4) || ($mouvenent['MOUVEMENT_DEPENSE_ID']==5) || ($mouvenent['MOUVEMENT_DEPENSE_ID']==6) || ($mouvenent['MOUVEMENT_DEPENSE_ID']==7) ){

				$getmouvement  = 'SELECT SUM(MONTANT_REALISE) mont_realise,SUM(MONTANT_REALISE_JURIDIQUE) as jurd,SUM(MONTANT_REALISE_LIQUIDATION) as liq,SUM(MONTANT_REALISE_ORDONNANCEMENT) as ord,SUM(MONTANT_REALISE_PAIEMENT) as paie,SUM(MONTANT_REALISE_DECAISSEMENT) as decais,DESC_MOUVEMENT_DEPENSE,execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID FROM execution_budgetaire_tempo join proc_mouvement_depense on execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID=proc_mouvement_depense.MOUVEMENT_DEPENSE_ID WHERE execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID='.$mouvenent['MOUVEMENT_DEPENSE_ID'].' AND EXECUTION_ID_TEMPO!='.$id.' AND EXECUTION_BUDGETAIRE_ID='.$id_demande.' ORDER BY execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID ASC';
				$getmouvement = "CALL getTable('" . $getmouvement . "');";
				$data['mouvement_montant']= $this->ModelPs->getRequeteOne($getmouvement);
			}
		}
		$bindparams = 'SELECT ACTIVITES,PTBA_ID,EXECUTION_BUDGETAIRE_ID,GRANDE_MASSE_BM,CODES_PROGRAMMATIQUE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,IMPUTATION FROM ptba JOIN execution_budgetaire_new ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$imputation_id.' ORDER BY ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID DESC';

		$bindparams="CALL getList('".$bindparams."')";
		$data['demande']= $this->ModelPs->getRequeteOne($bindparams);


		if(empty($data['demande']))
		{
			return redirect('demande_new/Raccrochage_Deuxieme_Trim');
		}

		$bindparams ='SELECT ACTIVITES,PTBA_ID,EXECUTION_BUDGETAIRE_ID FROM ptba JOIN execution_budgetaire_new ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID = ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$imputation_id.' ORDER BY ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID DESC';
		$bindparams="CALL getList('".$bindparams."')";
		$data['activite']= $this->ModelPs->getRequete($bindparams);
		$bindparam = 'SELECT EXECUTION_ID_TEMPO,ptba.ACTIVITES FROM execution_budgetaire_tempo  join ptba on execution_budgetaire_tempo.ID_PTBA=ptba.PTBA_ID WHERE 1 AND EXECUTION_ID_TEMPO='.$id.' ORDER BY EXECUTION_ID_TEMPO  asc';

		$bindparam="CALL getList('".$bindparam."')";
		$data['infoactivit']= $this->ModelPs->getRequeteOne($bindparam);

		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$data['profil']=$profil;

		$mvt_depense_params=$this->getBindParms('MOUVEMENT_DEPENSE_ID,DESC_MOUVEMENT_DEPENSE','proc_mouvement_depense', 'MOUVEMENT_DEPENSE_ID !=6','MOUVEMENT_DEPENSE_ID asc');
		$data['mvt_depense']= $this->ModelPs->getRequete($callpsreq, $mvt_depense_params);

		$somme_t_ligne='SELECT CASE WHEN MOUVEMENT_DEPENSE_ID=1 THEN MONTANT_REALISE WHEN MOUVEMENT_DEPENSE_ID=2 THEN MONTANT_REALISE_JURIDIQUE WHEN MOUVEMENT_DEPENSE_ID=3 THEN MONTANT_REALISE_LIQUIDATION WHEN MOUVEMENT_DEPENSE_ID=4 THEN MONTANT_REALISE_ORDONNANCEMENT WHEN MOUVEMENT_DEPENSE_ID=5 THEN MONTANT_REALISE_DECAISSEMENT WHEN MOUVEMENT_DEPENSE_ID=7 THEN MONTANT_REALISE_PAIEMENT END as montant_realise_ligne FROM execution_budgetaire_tempo where 1  AND EXECUTION_ID_TEMPO!='.$id.' AND EXECUTION_BUDGETAIRE_ID='.$id_demande;

		$somme_t_ligne = "CALL getTable('" . $somme_t_ligne . "');";
		
		$montant_total_ligne = $this->ModelPs->getRequete($somme_t_ligne);
		$total_ligne=0;
		foreach ($montant_total_ligne as $key_value)
		{
			$total_ligne=$total_ligne+$key_value->montant_realise_ligne;
		}
		
		$total_ligne_explode=explode(',', $total_ligne);
		$virgule=count($total_ligne_explode);
		if ($virgule==2)
		{
			$total_ligne1=$total_ligne_explode[0];
			$total_ligne2=$total_ligne_explode[1];

			$first_number=substr($total_ligne2,0,1);
			if ($first_number>=5)
			{
				$total_ligne1=$total_ligne1+1;
			}else
			{
				$total_ligne1=$total_ligne1;
			}
		}elseif ($virgule==1)
		{
			$total_ligne1=$total_ligne_explode[0];
		}
		$data['total_ligne']=$total_ligne1;

		$data['ligne_reste'] = $data['montant_total']['total']-$data['total_ligne'];

		$data_modif='SELECT EXECUTION_ID_TEMPO, EXECUTION_BUDGETAIRE_ID, ID_PTBA, MONTANT_REALISE, TRANCHE_ID, USER_ID, DOC_RACCROCHE, COMMENTAIRE, INSTITUTION_ID, CODE_PROGRAMME, CODE_ACTION, MOUVEMENT_DEPENSE_ID, PREUVE, MARCHE_PUBLIQUE, NUMERO_BON_ENGAGEMENT, DATE_BON_ENGAGEMENT, NUMERO_TITRE_DECAISSEMNT, DATE_TITRE_DECAISSEMENT, MONTANT_REALISE_LIQUIDATION, MONTANT_REALISE_ORDONNANCEMENT, MONTANT_REALISE_PAIEMENT, MONTANT_REALISE_DECAISSEMENT, MONTANT_REALISE_JURIDIQUE, IS_TRANSFERT_ACTIVITE, MONTANT_TRANSFERT, TRIMESTRE_ID FROM execution_budgetaire_tempo WHERE 1 AND EXECUTION_ID_TEMPO='.$id;

		$donnees_modif="CALL getList('".$data_modif."')";
		$data['info_modif']= $this->ModelPs->getRequeteOne($donnees_modif);

		$program = 'SELECT  CODE_PROGRAMME, INTITULE_PROGRAMME FROM inst_institutions_programmes WHERE CODE_PROGRAMME="'.$data['info_modif']['CODE_PROGRAMME'].'"';
		$program="CALL getList('".$program."')";
		$data['get_prog']= $this->ModelPs->getRequeteOne($program);

		$action = 'SELECT  CODE_ACTION, LIBELLE_ACTION FROM inst_institutions_actions WHERE CODE_ACTION="'.$data['info_modif']['CODE_ACTION'].'"';
		$action="CALL getList('".$action."')";
		$data['get_action']= $this->ModelPs->getRequeteOne($action);

		$mont_vote='SELECT  PTBA_ID, T2,MONTANT_RESTANT_T2 FROM ptba WHERE PTBA_ID='.$data['info_modif']['ID_PTBA'];
		$mont_vote="CALL getList('".$mont_vote."')";
		$data['mont_vote']= $this->ModelPs->getRequeteOne($mont_vote);

		$data['format_vote']=number_format($data['mont_vote']['MONTANT_RESTANT_T2'],'0',',',' ');

		$data['format_reste']=$data['mont_vote']['MONTANT_RESTANT_T2'];
		$data['tempo_id']=$id;
		$table="ptba JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='MONTANT_RESTANT_T2 as montant_restant,inst.CODE_INSTITUTION AS CODE_MINISTERE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,PTBA_ID,T2 as tranche,QT1 as qte,UNITE,act.CODE_ACTION,act.LIBELLE_ACTION,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME';
		$where="ptba.PTBA_ID ='".$data['info_modif']['ID_PTBA']."'";
		$orderby=' PTBA_ID DESC';
		$where=str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$activiteinfo= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

		if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 1)
		{
			$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T2']-$data['info_modif']['MONTANT_REALISE'];
		} 
		else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 2)
		{
			$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T2']-$data['info_modif']['MONTANT_REALISE_JURIDIQUE'];
		}
		else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 3)
		{
			$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T2']-$data['info_modif']['MONTANT_REALISE_LIQUIDATION'];
		}
		else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 4)
		{
			$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T2']-$data['info_modif']['MONTANT_REALISE_ORDONNANCEMENT'];

		}
		else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 5)
		{
			$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T2']-$data['info_modif']['MONTANT_REALISE_PAIEMENT'];

		}
		else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 7)
		{
			$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T2']-$data['info_modif']['MONTANT_REALISE_DECAISSEMENT'];

		}

		$data['id_demande']=$id_demande;
		return view('App\Modules\demande_new\Views\Raccrochage_Deuxieme_Trim_Modif_View',$data);
	}
	//modification d'une activité dans la table tempo
	function modifier_activite()
	{
		// return $this->getOne($demande);
		$db = db_connect();
		$data=$this->urichk();  
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$Mouvement_id = $this->request->getPost('Mouvement_code');
		$demande = $this->request->getPost('demande');
      	$EXECUTION_ID_TEMPO=$this->request->getPost('EXECUTION_ID_TEMPO');

		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }

		$rules = [
			'PTBA_ID' => [
	        'label' => '',
	        'rules' => 'required',
	        'errors' => [
	        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
	    	]
        ],
        
        'Mouvement_code' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'MARCHE_PUBLIC' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'montant_realise' => [
	        'label' => '',
	        'rules' => 'required',
	        'errors' => [
	        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
	    	]
    	]
		];
		
		if ($Mouvement_id==2)
		{
			$rules = [
				'montant_realise_jurid' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'numero_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}elseif ($Mouvement_id==3)
		{
			$rules = [
				'montant_realise_jurid' => [
		        'label' => '',
		        'rules' => 'required',
		        'errors' => [
		        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
		    	]
	        	],
	        	'montant_realise_liq' => [
		        'label' => '',
		        'rules' => 'required',
		        'errors' => [
		        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
		    	]
	        	],
	        	'numero_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}elseif ($Mouvement_id==4)
		{
			$rules = [
				'montant_realise_jurid' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_liq' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_ord' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'numero_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_bon' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}elseif ($Mouvement_id==5)
		{
			$rules = [
				'montant_realise_jurid' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_liq' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_ord' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_paie' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_decais' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'numero_decaiss' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_decais' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}elseif ($Mouvement_id==7)
		{
			$rules = [
				'montant_realise_jurid' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_liq' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_ord' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'montant_realise_paie' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'numero_decaiss' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	],
	        	'date_decais' => [
			        'label' => '',
			        'rules' => 'required',
			        'errors' => [
			        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
			    	]
	        	]
	        ];
		}
		$this->validation->setRules($rules);

		if($this->validation->withRequest($this->request)->run())
		{
			$PTBA_ID = $this->request->getPost('PTBA_ID');
			$montant_vote = $this->request->getPost('montant_vote');
			$COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
			$MARCHE_PUBLIC = $this->request->getPost('MARCHE_PUBLIC');
			$MARCHE_PUBLIC = (!empty($MARCHE_PUBLIC)) ? $MARCHE_PUBLIC:0;
			$programes_code = $this->request->getPost('programes_code');
			$actions = $this->request->getPost('actions');
			$institutions = $this->request->getPost('Institutions');	
			$doc_raccroche=$_FILES["doc_raccroche"]["name"];
			$PREUVE=$_FILES["PREUVE"]["name"];

			if ($doc_raccroche!="")
			{
				$DOCUMENT=$this->uploadFile('doc_raccroche','doc_raccroches',$doc_raccroche);
			}else
			{
				$DOCUMENT=$this->request->getPost('doc_raccroche23');
			}
			if ($PREUVE!="")
			{
				$DOCUMENT_PREUVE=$this->uploadFile('PREUVE','doc_preuves',$PREUVE);
			}else
			{
				$DOCUMENT_PREUVE=$this->request->getPost('PREUVE123');
			}
			
			if(empty($montant_vote))
			{
				$montant_vote = 0;
			}

			if(empty($quantite_vote))
			{
				$quantite_vote = 0;
			}	

			$numero_decaiss = $this->request->getPost('numero_decaiss');
			$date_decais = $this->request->getPost('date_decais');
			$numero_bon = $this->request->getPost('numero_bon');
			$date_bon = $this->request->getPost('date_bon');

			$montant_realise = $this->request->getPost('montant_realise');
			$montant_realise_jurid = $this->request->getPost('montant_realise_jurid');
			$montant_realise_liq = $this->request->getPost('montant_realise_liq');
			$montant_realise_ord = $this->request->getPost('montant_realise_ord');
			$montant_realise_decais = $this->request->getPost('montant_realise_decais');
			$montant_realise_paie = $this->request->getPost('montant_realise_paie');
			$numero_decaiss = str_replace("\n"," ",$numero_decaiss);
			$numero_decaiss = str_replace("\r"," ",$numero_decaiss);
			$numero_decaiss = str_replace("\t"," ",$numero_decaiss);
			$numero_decaiss = str_replace('"',' ',$numero_decaiss);
			$numero_decaiss = str_replace("'",' ',$numero_decaiss);

			$numero_bon = str_replace("\n"," ",$numero_bon);
			$numero_bon = str_replace("\r"," ",$numero_bon);
			$numero_bon = str_replace("\t"," ",$numero_bon);
			$numero_bon = str_replace('"',' ',$numero_bon);
			$numero_bon = str_replace("'",' ',$numero_bon);	

			if ($Mouvement_id==1)
			{
				$numero_decaiss = '';
				$date_decais = date('0000-00-00');
				$montant_realise_jurid = 0;
				$montant_realise_liq =0;
				$montant_realise_ord = 0;
				$montant_realise_paie = 0;
				$montant_realise_decais = 0;
			}
			else if ($Mouvement_id==2)
			{
				$numero_decaiss = '';
				$date_decais = date('0000-00-00');
				$montant_realise_liq = 0;
				$montant_realise_ord = 0;
				$montant_realise_paie = 0;
				$montant_realise_decais = 0;
			}
			else if ($Mouvement_id==3)
			{
				$numero_decaiss = '';
				$date_decais = date('0000-00-00');
				$montant_realise_ord = 0;
				$montant_realise_paie = 0;
				$montant_realise_decais = 0;
			}
			else if ($Mouvement_id==4)
			{
				$numero_decaiss = '';
				$date_decais = date('0000-00-00');
				$montant_realise_paie = 0;
				$montant_realise_decais = 0;		
			}
			else if ($Mouvement_id==5)
			{
				$numero_bon='';
				$date_bon=date('0000-00-00');
			}
			else if ($Mouvement_id==7)
			{
				$numero_bon='';
				$date_bon=date('0000-00-00');
				$montant_realise_decais = 0;
			}

			$COMMENTAIRE = str_replace("\n"," ",$COMMENTAIRE);
			$COMMENTAIRE = str_replace("\r"," ",$COMMENTAIRE);
			$COMMENTAIRE = str_replace("\t"," ",$COMMENTAIRE);
			$COMMENTAIRE = str_replace('"',' ',$COMMENTAIRE);
			$COMMENTAIRE = str_replace("'",' ',$COMMENTAIRE);
			$tranche_id=1;

			$numero_bon=trim($numero_bon);
			$numero_decaiss=trim($numero_decaiss);
			$COMMENTAIRE=trim($COMMENTAIRE);

			$where ="EXECUTION_ID_TEMPO=".$EXECUTION_ID_TEMPO;
			$tempo_table = "execution_budgetaire_tempo";

			$data_modify="EXECUTION_BUDGETAIRE_ID=".$demande." ,ID_PTBA=".$PTBA_ID." ,MONTANT_REALISE=".$montant_realise.",TRANCHE_ID=".$tranche_id.",USER_ID=".$user_id.",DOC_RACCROCHE='".$DOCUMENT."',COMMENTAIRE='".$COMMENTAIRE."',INSTITUTION_ID=".$institutions.",CODE_PROGRAMME='".$programes_code."',CODE_ACTION='".$actions."',MOUVEMENT_DEPENSE_ID=".$Mouvement_id.",PREUVE='".$DOCUMENT_PREUVE."',MARCHE_PUBLIQUE=".$MARCHE_PUBLIC.",NUMERO_BON_ENGAGEMENT='".$numero_bon."',DATE_BON_ENGAGEMENT='".$date_bon."',MONTANT_REALISE_JURIDIQUE=".$montant_realise_jurid.",MONTANT_REALISE_LIQUIDATION=".$montant_realise_liq.",MONTANT_REALISE_ORDONNANCEMENT=".$montant_realise_ord.",MONTANT_REALISE_PAIEMENT=".$montant_realise_paie.",DATE_TITRE_DECAISSEMENT='".$date_decais."',NUMERO_TITRE_DECAISSEMNT='".$numero_decaiss."',MONTANT_REALISE_DECAISSEMENT=".$montant_realise_decais;
			$this->update_all_table($tempo_table,$data_modify,$where);		
			return $this->getOne(md5($demande));
		}
    else
    {
      return $this->modifier($EXECUTION_ID_TEMPO,$demande);
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
	// pour uploader les documents
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
		return $newName;
	}

	//récuperation du path de bon d'engagement à afficher 
	function get_path_bon($id)
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

		$bind_params = $this->getBindParms('tempo.MOUVEMENT_DEPENSE_ID,PREUVE,DOC_RACCROCHE,proc_mouvement_depense.DESC_MOUVEMENT_DEPENSE', 'execution_budgetaire_tempo tempo JOIN proc_mouvement_depense ON proc_mouvement_depense.MOUVEMENT_DEPENSE_ID=tempo.MOUVEMENT_DEPENSE_ID', 'EXECUTION_ID_TEMPO='.$id,' EXECUTION_ID_TEMPO DESC');
		$docs= $this->ModelPs->getRequeteOne($callpsreq, $bind_params);

		$output = array(
			"MOUVEMENT_DEPENSE_ID" => $docs['MOUVEMENT_DEPENSE_ID'],
			"DESC_MOUVEMENT_DEPENSE" => $docs['DESC_MOUVEMENT_DEPENSE'],
			"DOC_RACCROCHE" => $docs['DOC_RACCROCHE']
		);
		return $this->response->setJSON($output);
	}

	//récuperation du path du preuve à afficher 
	function get_path_preuve($id)
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
		$bind_params = $this->getBindParms('tempo.MOUVEMENT_DEPENSE_ID,PREUVE,DOC_RACCROCHE,proc_mouvement_depense.DESC_MOUVEMENT_DEPENSE', 'execution_budgetaire_tempo tempo JOIN proc_mouvement_depense ON proc_mouvement_depense.MOUVEMENT_DEPENSE_ID=tempo.MOUVEMENT_DEPENSE_ID', 'EXECUTION_ID_TEMPO='.$id,' EXECUTION_ID_TEMPO DESC');
		$docs= $this->ModelPs->getRequeteOne($callpsreq, $bind_params);

		$output = array(
			"MOUVEMENT_DEPENSE_ID" => $docs['MOUVEMENT_DEPENSE_ID'],
			"DESC_MOUVEMENT_DEPENSE" => $docs['DESC_MOUVEMENT_DEPENSE'],
			"PREUVE" => $docs['PREUVE']
		);
		return $this->response->setJSON($output);
	}
}
?>