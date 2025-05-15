<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: DEMANDE BUDGETAIRE CORRIGER
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 14 sept 2023
**/

/**
 * Modifié par christa
 * date: le 30 sept 2023
 * christa@mediabox.bi
 **/
namespace  App\Modules\demande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Proc_Demande_Budget_Corriger extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
		// $this->load->library('Excel');
	}

	public function index($value='')
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
		$ind=$this->indicateur_new();
		$data['get_qte_phys']=$ind['get_qte_phys'];
		$data['get_pas_qte_phys']=$ind['get_pas_qte_phys'];
		$data['get_racrochet'] = $ind['get_racrochet'];
		$data['get_deja_racrochet'] = $ind['get_deja_racrochet'];
		$data['institutions_user']=$ind['getuser'];
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		return view('App\Modules\demande_new\Views\Proc_Demande_Budget_Corriger_List',$data);   
	}
	//récupération du sous tutelle par rapport à l'institution
	function get_sous_tutelle($CODE_INSTITUTION=0)
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
		if(!empty($CODE_INSTITUTION))
		{
			$institution.=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "'.$CODE_INSTITUTION.'%"';
		}
		if(!empty($CODE_SOUS_TUTEL))
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
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    	// Condition pour la requête de filtre
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;
		$requetedebase= 'SELECT CREDIT_VOTE,LIBELLE,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,DATE_DEMANDE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,EXECUTION_BUDGETAIRE_ID,IS_RACCROCHE,TRANSFERTS_CREDITS,CREDIT_APRES_TRANSFERT FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE IS_RACCROCHE = 0 AND TRIMESTRE_ID=1 AND (IS_TRANSFERTS=0 OR IS_TRANSFERTS=2)'.$institution;

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);

		$data = array();
		$u = 1;
		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		foreach($fetch_data as $row)
		{
			$sub_array = array();
			if(mb_strlen($row->LIBELLE) > 8)
			{ 
				$LIBELLE =  mb_substr($row->LIBELLE, 0, 8) .'...<a class="btn-sm" title="Afficher" data-toggle="modal" data-target="#institution'.$row->EXECUTION_BUDGETAIRE_ID.'" data-toggle="tooltip" ><i class="fa fa-eye"></i></a>';
			}else
			{
				$LIBELLE =  $row->LIBELLE;
			}
			
			$imputation_row="";
			if($userfiancier==1) 
			{
				$imputation_row= "<a  style='color:#fbbf25;' title='Raccrocher' href='".base_url("demande_new/Proc_Demande_Budget_Corriger/getOne/".$row->EXECUTION_BUDGETAIRE_ID)."' >".$row->IMPUTATION."</a>";
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
				$BTN_TRAITE = "<a class='btn btn-primary btn-sm' title='Raccrocher' href='".base_url("demande_new/Proc_Demande_Budget_Corriger/getOne/".$row->EXECUTION_BUDGETAIRE_ID)."' ><i class='fa fa-link text-light'></a>";
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

	//fonction get pour recuperer les données
	function getOne($id)
	{
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
		//recuperation du trimestre en cours
		$dataa=$this->converdate();
		$tranche=$dataa['CODE_TRANCHE'];
		//fin recuperation du trimestre en cours
		if(empty($id))
		{
			return redirect('demande_new/Proc_Demande_Budget_Corriger');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparams = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.CODE_INSTITUTION', '`user_affectaion` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID', 'USER_ID='.$user_id.'', '`DESCRIPTION_INSTITUTION` ASC');
        $data['institutions'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$somme_t_ligne=$this->getBindParms('(CASE WHEN `MOUVEMENT_DEPENSE_ID`=1 THEN `MONTANT_REALISE` WHEN `MOUVEMENT_DEPENSE_ID`=2 THEN `MONTANT_REALISE_JURIDIQUE` WHEN `MOUVEMENT_DEPENSE_ID`=3 THEN `MONTANT_REALISE_LIQUIDATION` WHEN `MOUVEMENT_DEPENSE_ID`=4 THEN `MONTANT_REALISE_ORDONNANCEMENT` WHEN `MOUVEMENT_DEPENSE_ID`=5 THEN `MONTANT_REALISE_DECAISSEMENT` WHEN `MOUVEMENT_DEPENSE_ID`=7 THEN `MONTANT_REALISE_PAIEMENT` END) as montant_realise_ligne','execution_budgetaire_tempo','EXECUTION_BUDGETAIRE_ID='.$id,'1');

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
		$columnselect='ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID AS IMPUTATION_ID ,ORDONNANCEMENT,ENG_BUDGETAIRE,ENG_JURIDIQUE,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,LIQUIDATION,LIBELLE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,EXECUTION_BUDGETAIRE_ID';
		$where="execution_budgetaire_new.EXECUTION_BUDGETAIRE_ID=".$id;
		$orderby=' EXECUTION_BUDGETAIRE_ID DESC';
		$where=str_replace("\'","'", $where);
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$data['info']= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

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
		    return redirect('demande_new/Proc_Demande_Budget_Corriger');
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

				$getmouvement  = $this->getBindParms('SUM(MONTANT_REALISE) mont_realise,SUM(`MONTANT_REALISE_JURIDIQUE`) as jurd,SUM(`MONTANT_REALISE_LIQUIDATION`) as liq,SUM(`MONTANT_REALISE_ORDONNANCEMENT`) as ord,SUM(`MONTANT_REALISE_PAIEMENT`) as paie,SUM(`MONTANT_REALISE_DECAISSEMENT`) as decais,DESC_MOUVEMENT_DEPENSE,execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_tempo join proc_mouvement_depense on execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID=proc_mouvement_depense.MOUVEMENT_DEPENSE_ID ',' execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID='.$mouvenent['MOUVEMENT_DEPENSE_ID'].' AND EXECUTION_BUDGETAIRE_ID='.$id,'execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID ASC');

				$data['mouvement_montant']= $this->ModelPs->getRequeteOne($callpsreq, $getmouvement);
			}
		}
		
		$imputation=$data['info']['IMPUTATION'];
		$imputation_id=$data['info']['IMPUTATION_ID'];
		$get_montant_t = $this->getBindParms('SUM(MONTANT_RESTANT_T1) total,SUM(T1) T1', 'ptba', 'CODE_NOMENCLATURE_BUDGETAIRE ="'.$imputation.'"', '1');
		$get_montant_t=str_replace('\"','"',$get_montant_t);
		$data['montant_total'] = $this->ModelPs->getRequeteOne($callpsreq, $get_montant_t);
				
		$montant_transfert=$this->getBindParms('SUM(`MONTANT_TRANSFERT`) as tr','execution_budgetaire_tempo','EXECUTION_BUDGETAIRE_ID='.$id,'1');
		$data['transfert']=$this->ModelPs->getRequeteOne($callpsreq, $montant_transfert);

		$ligne_budg=$data['montant_total']['total']+$data['transfert']['tr'];
		$data['restant_par_ligne']=$ligne_budg-$data['total_ligne'];
		$table="ptba JOIN execution_budgetaire_new ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='ACTIVITES,PTBA_ID,EXECUTION_BUDGETAIRE_ID,GRANDE_MASSE_BM,CODES_PROGRAMMATIQUE';
		$where="ptba.CODE_NOMENCLATURE_BUDGETAIRE ='".$imputation."'";
		$orderby=' CODE_NOMENCLATURE_BUDGETAIRE DESC';
		$where=str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$data['demande_exec']= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

		if(empty($data['demande_exec']))
		{
			return redirect('demande_new/Proc_Demande_Budget_Corriger');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparam = $this->getBindParms('EXECUTION_ID_TEMPO,execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID,EXECUTION_ID_TEMPO,MONTANT_REALISE,ptba.ACTIVITES,DOC_RACCROCHE,COMMENTAIRE,ptba.INTITULE_PROGRAMME,ptba.LIBELLE_ACTION,PREUVE,MARCHE_PUBLIQUE,inst_institutions.DESCRIPTION_INSTITUTION,proc_mouvement_depense.DESC_MOUVEMENT_DEPENSE,MONTANT_REALISE_JURIDIQUE,MONTANT_REALISE_LIQUIDATION,MONTANT_REALISE_ORDONNANCEMENT,MONTANT_REALISE_PAIEMENT,MONTANT_REALISE_DECAISSEMENT','execution_budgetaire_tempo  join ptba on execution_budgetaire_tempo.ID_PTBA=ptba.PTBA_ID join inst_institutions on inst_institutions.INSTITUTION_ID=execution_budgetaire_tempo.INSTITUTION_ID join proc_mouvement_depense on proc_mouvement_depense.MOUVEMENT_DEPENSE_ID=execution_budgetaire_tempo.MOUVEMENT_DEPENSE_ID', ' EXECUTION_BUDGETAIRE_ID='.$id,'EXECUTION_ID_TEMPO DESC');
		$data['info_tableau']= $this->ModelPs->getRequete($callpsreq, $bindparam);

		$table_p="ptba";
		$columnselect='ACTIVITES,PTBA_ID';
		$where="ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$imputation_id." AND ptba.T1 > 0 AND ptba.PTBA_ID NOT IN(SELECT `ID_PTBA` FROM execution_budgetaire_tempo)";
		$orderby=' CODE_NOMENCLATURE_BUDGETAIRE DESC';
		$where=str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table_p),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$data['activite']= $this->ModelPs->getRequete($callpsreq, $bindparams);
		
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparam = $this->getBindParms('EXECUTION_ID_TEMPO,ptba.ACTIVITES','execution_budgetaire_tempo  join ptba on execution_budgetaire_tempo.ID_PTBA=ptba.PTBA_ID', '1','EXECUTION_ID_TEMPO  asc');
		$data['infoactivit']= $this->ModelPs->getRequeteOne($callpsreq, $bindparam);
		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$data['profil']=$profil;
		$mvt_depense_params=$this->getBindParms('MOUVEMENT_DEPENSE_ID,DESC_MOUVEMENT_DEPENSE','proc_mouvement_depense', 'MOUVEMENT_DEPENSE_ID !=6','MOUVEMENT_DEPENSE_ID asc');
		$data['mvt_depense']= $this->ModelPs->getRequete($callpsreq, $mvt_depense_params);
		$op_tranches=$this->getBindParms('`TRANCHE_ID`,`DESCRIPTION_TRANCHE`','op_tranches','TRANCHE_ID!=1','TRANCHE_ID');
		$data['tranches']=$this->ModelPs->getRequete($callpsreq, $op_tranches);
		$data['id'] = $id;		
		return view('App\Modules\demande_new\Views\Proc_Demande_Budget_Corriger_View',$data);		
	}

	function get_montant($PTBA_ID)
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

		$dataa=$this->converdate();
		$debut=$dataa['debut'];
		$fin=$dataa['fin'];
		$tranche=$dataa['CODE_TRANCHE'];
		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$table="ptba JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='MONTANT_RESTANT_T1 as montant_restant,inst.CODE_INSTITUTION AS CODE_MINISTERE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,PTBA_ID,T1 as tranche,QT1 as qte,UNITE,act.CODE_ACTION,act.LIBELLE_ACTION,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME';
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
		if($virgule==2)
		{
			$montant1=$montant_explode[0];
			$montant2=$montant_explode[1];

			$first_number=substr($montant2,0,1);
			if($first_number>=5)
			{
				$montant1=$montant1+1;
			}else
			{
				$montant1=$montant1;
			}
		}elseif($virgule==1)
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

	// gestion de la dependance sous tutel et code  budgetaire
	function get_code()
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

		$SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_proc = $this->getBindParms('ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION', 'execution_budgetaire_new JOIN ptba ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN inst_institutions ON ptba.CODE_MINISTERE = inst_institutions.CODE_INSTITUTION JOIN inst_institutions_sous_tutel ON inst_institutions.INSTITUTION_ID = inst_institutions_sous_tutel.INSTITUTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID', 'SOUS_TUTEL_ID ='.$SOUS_TUTEL_ID,'SOUS_TUTEL_ID  ASC');
		$code_Buget= $this->ModelPs->getRequete($callpsreq, $bind_proc);

		$html='<option value="">'.lang('messages_lang.selection_message').'</option>';
		foreach ($code_Buget as $key)
		{
			$html.='<option value="'.$key->IMPUTATION.'">'.$key->IMPUTATION.'</option>';
		}
		$output = array(
			"codeBudgetaire" => $html,
		);
		return $this->response->setJSON($output);
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
	 * fonction pour faire la suppresion dans la table 
	 */
	public function deleteData()
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

		$id= $this->request->uri->getSegment(4); 
		$demande= $this->request->uri->getSegment(5);

		$db = db_connect();     
		$statut = 0;	
		$deleteRequete = "CALL `deleteData`(?,?);";
		$critere =$db->escapeString("EXECUTION_ID_TEMPO =".$id);
		$table =$db->escapeString("execution_budgetaire_tempo");
		$bindparams =[$db->escapeString($table),$db->escapeString($critere)] ;
		if ($this->ModelPs->createUpdateDelete($deleteRequete, $bindparams)) {
            // $statut = 1;
			$data=['message' => ''.lang('messages_lang.message_success_suppr').''];
			session()->setFlashdata('alert', $data);
			return $this->getOne($demande);
		}else{
			return  false;
		}
	}

	/**
	 * fonction pour enregistrer des informations temporairement
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
		$IS_TRANSFERT_ACTIVITE=$this->request->getPost('IS_TRANSFERT_ACTIVITE');
		$Mouvement_id = $this->request->getPost('Mouvement_code');

		$rules = [
			'PTBA_ID' => [
	        'label' => '',
	        'rules' => 'required',
	        'errors' => [
	        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
	    	]
        ],
        'IS_TRANSFERT_ACTIVITE' => [
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
		if ($IS_TRANSFERT_ACTIVITE==1)
		{
			$rules = [
				'TRANCHE_ID' => [
		        'label' => '',
		        'rules' => 'required',
		        'errors' => [
		        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
		    	]
				],
				'MONTANT_TRANSFERT' => [
		        'label' => '',
		        'rules' => 'required',
		        'errors' => [
		        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
		          ]
		        ]
		    ];
	        
		}

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
			$MONTANT_TRANSFERT=$this->request->getPost('MONTANT_TRANSFERT');
			$TRIMESTRE_ID=$this->request->getPost('TRANCHE_ID');
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
			$where="CODE_TRANCHE='T1'";
			$orderby='TRANCHE_ID ASC';
			$where=str_replace("\'", "'", $where);
			$db = db_connect();
			$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
			$bindparams=str_replace("\'","'", $bindparams);
			$tranche_id= 1;

			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			$table_tempo="execution_budgetaire_tempo";
			$column='ID_PTBA';
			$cdtion="ID_PTBA=".$PTBA_ID;
			$orderby='ID_PTBA DESC';
			$cdtion=str_replace("\'","'",$cdtion);
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

			$TRIMESTRE_ID=(!empty($TRIMESTRE_ID)) ? $TRIMESTRE_ID : 0 ;
			$ptba=$this->getBindParms('ID_PTBA','execution_budgetaire_tempo','ID_PTBA='.$PTBA_ID,'ID_PTBA');
			$ptba_id=$this->ModelPs->getRequeteOne($callpsreq, $ptba);

			if (empty($ptba_id['ID_PTBA']))
			{
				if (!empty($institutions) && !empty($programes_code) && !empty($actions) && !empty($montant_vote))
				{
					if(($Mouvement_id==1) || ($Mouvement_id==2) || ($Mouvement_id==4) || ($Mouvement_id==3))
					{
						$columsinsert="EXECUTION_BUDGETAIRE_ID,ID_PTBA,MONTANT_REALISE,TRANCHE_ID,USER_ID,DOC_RACCROCHE,COMMENTAIRE,INSTITUTION_ID,CODE_PROGRAMME,CODE_ACTION,MOUVEMENT_DEPENSE_ID,PREUVE,MARCHE_PUBLIQUE,NUMERO_BON_ENGAGEMENT,DATE_BON_ENGAGEMENT,MONTANT_REALISE_JURIDIQUE,MONTANT_REALISE_LIQUIDATION,MONTANT_REALISE_ORDONNANCEMENT,IS_TRANSFERT_ACTIVITE,MONTANT_TRANSFERT,TRIMESTRE_ID";
						$MONTANT_TRANSFERT=!empty($MONTANT_TRANSFERT)?($MONTANT_TRANSFERT):0;
						$datacolumsinsert=$demande.",".$PTBA_ID.",".$montant_realise.",".$tranche_id.",".$user_id.",'".$DOCUMENT."','".$COMMENTAIRE."',".$institutions.",'".$programes_code."','".$actions."',".$Mouvement_id.",'".$DOCUMENT_PREUVE."',".$MARCHE_PUBLIC.",'".$numero_bon."','".$date_bon."','".$montant_realise_jurid."','".$montant_realise_liq."','".$montant_realise_ord."',".$IS_TRANSFERT_ACTIVITE.",".$MONTANT_TRANSFERT.",".$TRIMESTRE_ID." ";
						$this->save_info($columsinsert,$datacolumsinsert);
						return $this->getOne($demande);
					}
					else
					{
						$columsinsert="EXECUTION_BUDGETAIRE_ID,ID_PTBA,MONTANT_REALISE,TRANCHE_ID,USER_ID,DOC_RACCROCHE,COMMENTAIRE,INSTITUTION_ID,CODE_PROGRAMME,CODE_ACTION,MOUVEMENT_DEPENSE_ID,PREUVE,MARCHE_PUBLIQUE,NUMERO_TITRE_DECAISSEMNT,DATE_TITRE_DECAISSEMENT,MONTANT_REALISE_JURIDIQUE,MONTANT_REALISE_LIQUIDATION,MONTANT_REALISE_ORDONNANCEMENT,MONTANT_REALISE_PAIEMENT,MONTANT_REALISE_DECAISSEMENT,IS_TRANSFERT_ACTIVITE,MONTANT_TRANSFERT,TRIMESTRE_ID";
						$datacolumsinsert=$demande.",".$PTBA_ID.",".$montant_realise.",".$tranche_id.",".$user_id.",'".$DOCUMENT."','".$COMMENTAIRE."',".$institutions.",'".$programes_code."','".$actions."',".$Mouvement_id.",'".$DOCUMENT_PREUVE."',".$MARCHE_PUBLIC.",'".$numero_decaiss."','".$date_decais."',".$montant_realise_jurid.",".$montant_realise_liq.",".$montant_realise_ord.",".$montant_realise_paie.",".$montant_realise_decais.",".$IS_TRANSFERT_ACTIVITE.",".$MONTANT_TRANSFERT.",".$TRIMESTRE_ID." ";
						$this->save_info($columsinsert,$datacolumsinsert);

						return $this->getOne($demande);
					}
				}	
			}else
			{
				return $this->getOne($demande);
			}
    }
    else
    {
			return $this->getOne($demande);
    }
	}
	

	// gestion de la dependance code  budgetaire et activites
	function get_activitesByCode()
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

		$CODE_NOMENCLATURE_BUDGETAIRE = $this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$getcodeactivite = $this->getBindParms('ligne.CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,PTBA_ID,CODES_PROGRAMMATIQUE','ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ligne.CODE_NOMENCLATURE_BUDGETAIRE= '.$CODE_NOMENCLATURE_BUDGETAIRE.' ','ACTIVITES  ASC');
		$code_activites = $this->ModelPs->getRequete($callpsreq, $getcodeactivite);

		$html='<option value="">'.lang('messages_lang.selection_message').'</option>';
		foreach ($code_activites as $key)
		{
			$html.='<option value="'.$key->PTBA_ID.'">'.$key->ACTIVITES.' ('.$key->CODES_PROGRAMMATIQUE.')</option>';
		}
		##################################################
		$bind_proc = $this->getBindParms('LIBELLE', 'proc_demande_exec_budgetaire_a_corriger', 'CODE_NOMENCLATURE_BUDGETAIRE ='.$CODE_NOMENCLATURE_BUDGETAIRE,'CODE_NOMENCLATURE_BUDGETAIRE  ASC');
		$libelle= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);

		$output = array(
			"LIBELLE" => $libelle['LIBELLE'],
			"activite_by_code" => $html
		);
		return $this->response->setJSON($output);
	}

	/* Debut Gestion update table de la demande detail*/
	function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
	/* Fin Gestion update table de la demande detail*/

	/* Debut Gestion insertion */
	function save_all_table($table,$columsinsert,$datacolumsinsert)
	{
	  //$columsinsert: Nom des colonnes separe par,
	  //$datacolumsinsert : les donnees a inserer dans les colonnes
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams =[$table,$columsinsert,$datacolumsinsert];
		$result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
		return $id=$result['id'];
	}

	function save($value='')
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

		$DEM_EXEC_BUDGETAIRE_ADMINISTRATION_CORRIGER_ID = $this->request->getPost('DEM_EXEC_BUDGETAIRE_ADMINISTRATION_CORRIGER_ID');

		//mise a jour dans la table corriger
		$table = 'proc_demande_exec_budgetaire_a_corriger';
		$where='DEM_EXEC_BUDGETAIRE_ADMINISTRATION_CORRIGER_ID="'.$DEM_EXEC_BUDGETAIRE_ADMINISTRATION_CORRIGER_ID.'"';
		$data='TRAITE = 1';
		$this->update_all_table($table,$data,$where);

			//insert dans la table execution
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_proc = $this->getBindParms('*', 'proc_demande_exec_budgetaire_a_corriger', 'DEM_EXEC_BUDGETAIRE_ADMINISTRATION_CORRIGER_ID ='.$DEM_EXEC_BUDGETAIRE_ADMINISTRATION_CORRIGER_ID,'DEM_EXEC_BUDGETAIRE_ADMINISTRATION_CORRIGER_ID  ASC');
		$proc_demande= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);

		$EXEC_BUDG_PHASE_ADMIN_ID = $proc_demande['EXEC_BUDG_PHASE_ADMIN_ID']; 
		$PROCESS_ID = $proc_demande['PROCESS_ID']; 
		$MOUVEMENT_DEPENSE_ID = $proc_demande['MOUVEMENT_DEPENSE_ID']; 
		$INSTITUTION_ID = $proc_demande['INSTITUTION_ID']; 
		$SOUS_TUTEL_ID = $proc_demande['SOUS_TUTEL_ID']; 
		$ETAPE_ID = $proc_demande['ETAPE_ID']; 
		$CODE_NOMENCLATURE_BUDGETAIRE = $proc_demande['CODE_NOMENCLATURE_BUDGETAIRE']; 
		$MONTANT_ENGAGE = $proc_demande['MONTANT_ENGAGE']; 
		$MONTANT_TRANSFERT = $proc_demande['MONTANT_TRANSFERT']; 
		$DATE_INSERTION = $proc_demande['DATE_INSERTION']; 
		$PTBA_ID  =  $this->request->getPost('PTBA_ID');

		$insertIntoTable='proc_demande_exec_budgetaire';
		$columsinsert="ETAPE_ID,DATE_INSERTION,MONTANT_ENGAGE,EXEC_BUDG_PHASE_ADMIN_ID,PROCESS_ID,CODE_NOMENCLATURE_BUDGETAIRE,SOUS_TUTEL_ID,MOUVEMENT_DEPENSE_ID,PTBA_ID,INSTITUTION_ID ,MONTANT_TRANSFERT";
		$datacolumsinsert=$ETAPE_ID.",'".$DATE_INSERTION."',".$MONTANT_ENGAGE.",".$EXEC_BUDG_PHASE_ADMIN_ID.",".$PROCESS_ID.",".$CODE_NOMENCLATURE_BUDGETAIRE.",".$SOUS_TUTEL_ID.",".$MOUVEMENT_DEPENSE_ID.",".$PTBA_ID.",".$INSTITUTION_ID.",".$MONTANT_TRANSFERT;
		$this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);
		$data=['message' => lang('messages_lang.enregistrement_reussi')];
		session()->setFlashdata('alert', $data);
		return redirect('demande_new/Proc_Demande_Budget_Corriger');
	}

	//enregistrement dans la table historique
	function historique_raccrochage($DETAIL_ID,$USER_ID,$MOUVEMENT_DEPENSE_ID,$TYPE_RACCROCHAGE_ID,$OBSERVATION)
	{
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/homepage');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/do_logout');
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
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }

		$table="execution_budgetaire_raccrochage_activite_info_suppl_new";
		$columnselect="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,PATH_BON_ENGAGEMENT";
		$datacolumsinsert=$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.',"'.$PATH_BON_ENGAGEMENT.'"';
		$bindparms=[$table,$columnselect,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$this->ModelPs->createUpdateDelete($insertReq,$bindparms);
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
			$data=['message' => "Le montant raccroché doit être égal au montant de l'engagement budgétaire"];
			session()->setFlashdata('alert', $data);
			return $this->getOne($demande);
		}
		elseif ($mvt_depense['jurd']!=$info['ENG_JURIDIQUE'])
		{
			$data=['message' => lang('messages_lang.racc_egal_jur')];
			session()->setFlashdata('alert', $data);
			return $this->getOne($demande);
		}
		elseif ($mvt_depense['liq']!=$info['LIQUIDATION'])
		{
			$data=['message' => lang('messages_lang.racc_egal_liquid')];
			session()->setFlashdata('alert', $data);
			return $this->getOne($demande);
		}
		elseif ($mvt_depense['ord']!=$info['ORDONNANCEMENT'])
		{
			$data=['message' => lang('messages_lang.racc_egal_ordo')];
			session()->setFlashdata('alert', $data);
			return $this->getOne($demande);
		}
		elseif ($mvt_depense['paie']!=$info['PAIEMENT'])
		{
			$data=['message' => lang('messages_lang.racc_egal_pay')];
			session()->setFlashdata('alert', $data);
			return $this->getOne($demande);
		}
		elseif ($mvt_depense['decais']!=$info['DECAISSEMENT'])
		{
			$data=['message' => lang('messages_lang.racc_egal_decais')];
			session()->setFlashdata('alert', $data);
			return $this->getOne($demande);
		}

		$ligne_budg=$this->request->getPost('ligne_budg');

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

			$IS_TRANSFERT_ACTIVITE=$infoact->IS_TRANSFERT_ACTIVITE;
			$MONTANT_TRANSFERT=!empty($MONTANT_TRANSFERT)?$MONTANT_TRANSFERT:0;
			$TRIMESTRE_ID=$infoact->TRIMESTRE_ID;

			$TYPE_RACCROCHAGE_ID=1;
			$TYPE_DOCUMENT_ID=1;
			$PATH_BON_ENGAGEMENT='';
			$PATH_TITRE_DECAISSEMENT='';

			if($MOUVEMENT_DEPENSE_ID==1 || $MOUVEMENT_DEPENSE_ID==2 || $MOUVEMENT_DEPENSE_ID==3 || $MOUVEMENT_DEPENSE_ID==4)
			{
				$PATH_BON_ENGAGEMENT=$DOC_RACCROCHE;
			}
			else 
			if($MOUVEMENT_DEPENSE_ID==5 || $MOUVEMENT_DEPENSE_ID==7)
			{
				$TYPE_DOCUMENT_ID=2;
				$PATH_TITRE_DECAISSEMENT=$DOC_RACCROCHE;
			}

			$columsinsert="EXECUTION_BUDGETAIRE_ID,PTBA_ID,MONTANT_RACCROCHE,TRIMESTRE_ID,INSTITUTION_ID,MARCHE_PUBLIQUE,MONTANT_RACCROCHE_JURIDIQUE,MONTANT_RACCROCHE_LIQUIDATION,MONTANT_RACCROCHE_ORDONNANCEMENT,MONTANT_RACCROCHE_PAIEMENT,MONTANT_RACCROCHE_DECAISSEMENT,PREUVE,IS_TRANSFERT_ACTIVITE";

			$datacolumsinsert=$EXECUTION_BUDGETAIRE_ID.",".$ID_PTBA.",".$MONTANT_REALISE.",".$TRANCHE_ID.",".$INSTITUTION_ID.",".$MARCHE_PUBLIQUE.",".$MONTANT_RACCROCHE_JURIDIQUE.",".$MONTANT_RACCROCHE_LIQUIDATION.",".$MONTANT_RACCROCHE_ORDONNANCEMENT.",".$MONTANT_RACCROCHE_PAIEMENT.",".$MONTANT_RACCROCHE_DECAISSEMENT.",'".$PREUVE."',".$IS_TRANSFERT_ACTIVITE;
			$table='execution_budgetaire_raccrochage_activite_new';
			$bindparms=[$table,$columsinsert,$datacolumsinsert];
			$insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
			$id_raccrochage=$this->ModelPs->getRequeteOne($insertReqAgence, $bindparms);
			$racc=$id_raccrochage['id'];

			$get_detail=$this->getBindParms('EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID','execution_budgetaire_raccrochage_activite_detail','EXECUTION_BUDGETAIRE_RACCROCHAGE_ID='.$racc,'EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID');
			$details=$this->ModelPs->getRequeteOne($callpsreq,$get_detail);

			if (empty($details))
			{
				$table_det="execution_budgetaire_raccrochage_activite_detail";

				$columDetail="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,MOUVEMENT_DEPENSE_ID,TRIMESTRE_ID,MONTANT_RACCROCHE,MONTANT_RACCROCHE_JURIDIQUE,MONTANT_RACCROCHE_LIQUIDATION,MONTANT_RACCROCHE_ORDONNANCEMENT,MONTANT_RACCROCHE_PAIEMENT,MONTANT_RACCROCHE_DECAISSEMENT,PATH_TITRE_DECAISSEMENT, DATE_TITRE_DECAISSEMENT";

				$datainsertDetail=$racc.",".$MOUVEMENT_DEPENSE_ID.",".$TRANCHE_ID.",".$MONTANT_REALISE.",".$MONTANT_RACCROCHE_JURIDIQUE.",".$MONTANT_RACCROCHE_LIQUIDATION.",".$MONTANT_RACCROCHE_ORDONNANCEMENT.",".$MONTANT_RACCROCHE_PAIEMENT.",".$MONTANT_RACCROCHE_DECAISSEMENT.",'".$PATH_TITRE_DECAISSEMENT."','".$DATE_TITRE_DECAISSEMENT."' ";
				$bindparms_det=[$table_det,$columDetail,$datainsertDetail];

				$insertReqdet = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
				$id_det=$this->ModelPs->getRequeteOne($insertReqdet, $bindparms_det);
				$id_detail=$id_det['id'];

				//verification pour ne pas enregistrer 2 fois
				$exist_histo=$this->getBindParms('EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID ','historique_raccrochage_activite_detail','EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID='.$id_detail.' AND TYPE_RACCROCHAGE_ID='.$TYPE_RACCROCHAGE_ID,'EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID');
				$exist=$this->ModelPs->getRequeteOne($callpsreq,$exist_histo);
				
				if (empty($exist))
				{
					$this->historique_infos_sup($racc,$NUMERO_BON_ENGAGEMENT,$PATH_BON_ENGAGEMENT,$DATE_BON_ENGAGEMENT,$NUMERO_TITRE_DECAISSEMNT,$PATH_TITRE_DECAISSEMENT,$DATE_TITRE_DECAISSEMENT);
					$this->historique_raccrochage($id_detail,$USER_ID,$MOUVEMENT_DEPENSE_ID,$TYPE_RACCROCHAGE_ID,$COMMENTAIRE);
				}
			}

			
			$table_ptba='ptba';
			$conditions='PTBA_ID='.$ID_PTBA;
			if($MOUVEMENT_DEPENSE_ID==5)
			{
				$getmontantvote  = $this->getBindParms('MONTANT_RESTANT_T1', 'ptba','PTBA_ID='.$ID_PTBA,' PTBA_ID ASC');
				$montantv= $this->ModelPs->getRequeteOne($callpsreq, $getmontantvote);
				$str_montant_restant=!empty(trim($montantv['MONTANT_RESTANT_T1']))?trim($montantv['MONTANT_RESTANT_T1']):0;
				$str_montant_decaissement=!empty(trim($MONTANT_RACCROCHE_DECAISSEMENT))?trim($MONTANT_RACCROCHE_DECAISSEMENT):0;
				$montant_restant=floatval($str_montant_restant);
				$montant_decaissement=floatval($str_montant_decaissement);
				$montant_restant=$montant_restant-$montant_decaissement;
				$donnees_modif='MONTANT_RESTANT_T1='.$montant_restant;
				$ps = "CALL `updateData`(?,?,?);";
				$this->update_all_table($table_ptba,$donnees_modif,$conditions);
			}

			$TYPE_OPERATION_ID=4;
			if($IS_TRANSFERT_ACTIVITE==1)
			{
				$col_transf="TYPE_OPERATION_ID,USER_ID,MONTANT_TRANSFERT,PTBA_ID_TRANSFERT,MONTANT_RECEPTION,PTBA_ID_RECEPTION,EXECUTION_BUDGETAIRE_ID,TRIMESTRE_ID,INSTITUTION_ID_TRANSFERT,INSTITUTION_ID_RECEPTION";

				$data_transfert=$TYPE_OPERATION_ID.",".$USER_ID.",".$MONTANT_TRANSFERT.",".$ID_PTBA.",".$MONTANT_TRANSFERT.",".$ID_PTBA.",".$demande.",".$TRIMESTRE_ID.",".$INSTITUTION_ID.",".$INSTITUTION_ID;

				$table_transfert="historique_transfert";
				$bindparms=[$table_transfert,$col_transf,$data_transfert];
				$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";

				$check_hist=$this->getBindParms('HISTORIQUE_TRANSFERT_ID','historique_transfert','TYPE_OPERATION_ID=4 AND `MONTANT_TRANSFERT`='.$MONTANT_TRANSFERT.' AND `PTBA_ID_TRANSFERT`='.$ID_PTBA.' AND `MONTANT_RECEPTION`='.$MONTANT_TRANSFERT.' AND `PTBA_ID_RECEPTION`='.$ID_PTBA.' AND `TRIMESTRE_ID`=1','HISTORIQUE_TRANSFERT_ID');
				$historique=$this->ModelPs->getRequeteOne($callpsreq, $check_hist);

				if (empty($historique['HISTORIQUE_TRANSFERT_ID']))
				{
					$this->ModelPs->createUpdateDelete($insertReq,$bindparms);
				}

				$mont_tranche=$this->getBindParms('PTBA_ID,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,ACTIVITES','ptba','PTBA_ID='.$ID_PTBA,'PTBA_ID');
				$montant= $this->ModelPs->getRequeteOne($callpsreq, $mont_tranche);

				$MONTANT_RESTANT=0;
				$datatomodifie='';

				if ($TRIMESTRE_ID==2)
				{
					if ($MONTANT_TRANSFERT <= $montant['MONTANT_RESTANT_T2'])
					{
						$MONTANT_RESTANT=floatval($montant['MONTANT_RESTANT_T2'])-floatval($MONTANT_TRANSFERT);
						$datatomodifie= 'MONTANT_RESTANT_T2="'.$MONTANT_RESTANT.'"';
					}else
					{
						$data=['message' => "".lang('messages_lang.trans_trim')." <br> ".$montant['ACTIVITES']." "];
						session()->setFlashdata('alert', $data);
						return $this->getOne($demande);
					}
				}
				elseif ($TRIMESTRE_ID==3)
				{
					if ($MONTANT_TRANSFERT <= $montant['MONTANT_RESTANT_T3'])
					{
						
						$MONTANT_RESTANT=floatval($montant['MONTANT_RESTANT_T3'])-floatval($MONTANT_TRANSFERT);
						$datatomodifie= 'MONTANT_RESTANT_T3="'.$MONTANT_RESTANT.'"';
					}else
					{
						$data=['message' => "".lang('messages_lang.trans_trim')."<br> ".$montant['ACTIVITES']." "];
						session()->setFlashdata('alert', $data);
						return $this->getOne($demande);
					}
				}
				elseif ($TRIMESTRE_ID==4)
				{
					if ($MONTANT_TRANSFERT <= $montant['MONTANT_RESTANT_T4'])
					{
						
						$MONTANT_RESTANT=floatval($montant['MONTANT_RESTANT_T4'])-floatval($MONTANT_TRANSFERT);
						$datatomodifie= 'MONTANT_RESTANT_T4="'.$MONTANT_RESTANT.'"';
					}else
					{
						$data=['message' => "".lang('messages_lang.trans_trim')." <br> ".$montant['ACTIVITES']." "];
						session()->setFlashdata('alert', $data);
						return $this->getOne($demande);
					}
				}
				elseif ($TRIMESTRE_ID==5)
				{
					$MONTANT_RESTANT=floatval($montant['MONTANT_RESTANT_T2'])+floatval($montant['MONTANT_RESTANT_T3'])+floatval($montant['MONTANT_RESTANT_T4']);
					if ($MONTANT_TRANSFERT <= $MONTANT_RESTANT)
					{
						$datatomodifie='MONTANT_RESTANT_T2="0",MONTANT_RESTANT_T3="0",MONTANT_RESTANT_T4="0" ';
					}else
					{
						$data=['message' => "".lang('messages_lang.trans_trim')." <br> ".$montant['ACTIVITES']." "];
						session()->setFlashdata('alert', $data);
						return $this->getOne($demande);
					}
				}
				$NEW_MONTANT=$montant['MONTANT_RESTANT_T1']+$MONTANT_TRANSFERT;

				if($MONTANT_RESTANT<0)
				{
					$data=['message' => "".lang('messages_lang.save_infer_zero')." <br> ".$montant['ACTIVITES']." "];
					session()->setFlashdata('alert', $data);
					return $this->getOne($demande);
				}
				$datatomodifiep= 'MONTANT_RESTANT_T1="'.$NEW_MONTANT.'",'.$datatomodifie;
				$this->update_all_table($table_ptba,$datatomodifiep,$conditions);
				
			}
		}
		$MONTANT_DEPASSE=0;
		if($ligne_budg<0)
		{
			$MONTANT_DEPASSE=1;
		}
		$table='execution_budgetaire_new';
		$conditions='EXECUTION_BUDGETAIRE_ID='.$demande ;
		$datatomodifie= 'IS_RACCROCHE=1,MONTANT_DEPASSE='.$MONTANT_DEPASSE;
		$this->update_all_table($table,$datatomodifie,$conditions);
		################################################
		$critere =" EXECUTION_BUDGETAIRE_ID=".$demande;
		$table =$db->escapeString("execution_budgetaire_tempo");
		$bindparams =[$db->escapeString($table),$db->escapeString($critere)] ;
		$deleteRequete = "CALL `deleteData`(?,?);";
		$info=$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
		########################################################
		$data=['message' => lang('messages_lang.racc_save_success')];
		session()->setFlashdata('alert', $data);
		return redirect('demande_new/Proc_Demande_Budget_Corriger');
	}
	

	// pour uploader les documents
	public function uploadFile($fieldName=NULL, $folder=NULL, $prefix = NULL): string
	{
		$prefix = ($prefix === '') ? uniqid() : $prefix;
		$path = '';

		$file = $this->request->getFile($fieldName);
		if($file->isValid() && !$file->hasMoved()) {
			$newName = uniqid(). '' . date('ymdhis') . '.' . $file->getExtension();
			$file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
			$path = 'uploads/' . $folder . '/' . $newName;
		}
		return $newName;
	}

	
	//function pour récupérer le montant d'une tranche
	function get_mont_transfert($TRANCHE_ID,$PTBA_ID)
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

		$db = db_connect();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_params = $this->getBindParms('MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,`PROGRAMMATION_FINANCIERE_BIF`', 'ptba', '`PTBA_ID`='.$PTBA_ID,' PTBA_ID ASC');
		$montant= $this->ModelPs->getRequeteOne($callpsreq, $bind_params);

		$montant_trim=0;
		if ($TRANCHE_ID==1)
		{
			$montant_trim=$montant['MONTANT_RESTANT_T1'];
		}elseif ($TRANCHE_ID==2)
		{
			$montant_trim=$montant['MONTANT_RESTANT_T2'];
		}elseif ($TRANCHE_ID==3)
		{
			$montant_trim=$montant['MONTANT_RESTANT_T3'];
		}elseif ($TRANCHE_ID==4)
		{
			$montant_trim=$montant['MONTANT_RESTANT_T4'];
		}elseif ($TRANCHE_ID==5)
		{
			$montant_trim=$montant['MONTANT_RESTANT_T2']+$montant['MONTANT_RESTANT_T3']+$montant['MONTANT_RESTANT_T4'];
		}
		$output = array(
			"montant_trim" => $montant_trim,
		);
		return $this->response->setJSON($output);
	}


	//function pour recuperer les donnees 
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
		$dataa=$this->converdate();
		$tranche=$dataa['CODE_TRANCHE'];

		$callpsreq = "CALL getRequete(?,?,?,?);";
		$donnees = 'SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID AS IMPUTATION_ID, EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_BRUT_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION, LIBELLE, CREDIT_VOTE, TRANSFERTS_CREDITS, CREDIT_APRES_TRANSFERT, ENG_BUDGETAIRE, ENG_JURIDIQUE, LIQUIDATION, ORDONNANCEMENT, PAIEMENT, DECAISSEMENT, DATE_DEMANDE, IS_RACCROCHE, MOUVEMENT_DEPENSE_ID, IS_TRANSFERTS, INSTITUTION_ID, SOUS_TUTEL_ID FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND EXECUTION_BUDGETAIRE_ID = '.$id_demande;

		$donnees="CALL getList('".$donnees."')";
		$data['info']= $this->ModelPs->getRequeteOne($donnees);
		$CODE_INSTITUTION=substr($data['info']['IMPUTATION'],0,2);
		$CODE_SOUS_TUTEL=substr($data['info']['IMPUTATION'],4,3);

		$inst = 'SELECT INSTITUTION_ID, CODE_INSTITUTION, DESCRIPTION_INSTITUTION,tipe.DESC_TYPE_INSTITUTION,inst.TYPE_INSTITUTION_ID FROM inst_institutions inst JOIN inst_types_institution tipe ON tipe.TYPE_INSTITUTION_ID=inst.TYPE_INSTITUTION_ID WHERE CODE_INSTITUTION="'.$CODE_INSTITUTION.'"';
		$inst="CALL getList('".$inst."')";
		$data['resultatinst']=$this->ModelPs->getRequeteOne($inst);

		$INSTITUTION_ID=$data['resultatinst']['INSTITUTION_ID'];
		$getSousTutel=$this->getBindParms("SOUS_TUTEL_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL","inst_institutions_sous_tutel","INSTITUTION_ID=".$INSTITUTION_ID." AND CODE_SOUS_TUTEL='".$CODE_SOUS_TUTEL."'",'SOUS_TUTEL_ID ASC');
		$getSousTutel=str_replace("\'","'",$getSousTutel);
		$resultatinsttut=$this->ModelPs->getRequeteOne($callpsreq, $getSousTutel);
		$data['sous_tutel']= $resultatinsttut;
		
		$imputation=$data['info']['IMPUTATION'];
		$imputation_id=$data['info']['IMPUTATION_ID'];
		$get_montant_t = $this->getBindParms('SUM(MONTANT_RESTANT_T1) total,SUM(T1) T1', 'ptba', 'CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$imputation_id.' ', '1');
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

		$bindparams = 'SELECT ACTIVITES,PTBA_ID,EXECUTION_BUDGETAIRE_ID,GRANDE_MASSE_BM,CODES_PROGRAMMATIQUE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION FROM ptba  JOIN execution_budgetaire_new ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$imputation_id.' ORDER BY ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID DESC';

		$bindparams="CALL getList('".$bindparams."')";
		$data['demande']= $this->ModelPs->getRequeteOne($bindparams);


		if(empty($data['demande']))
		{
			return redirect('demande_new/Proc_Demande_Budget_Corriger');
		}

		$callpsreq = "CALL getRequete(?,?,?,?);";
		$bindparams ='SELECT ACTIVITES,PTBA_ID,EXECUTION_BUDGETAIRE_ID FROM ptba JOIN execution_budgetaire_new ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID = ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$imputation_id.' ORDER BY ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID DESC';
		$bindparams="CALL getList('".$bindparams."')";
		$data['activite']= $this->ModelPs->getRequete($bindparams);

		$callpsreq = "CALL getRequete(?,?,?,?);";
		$bindparam = 'SELECT EXECUTION_ID_TEMPO,ptba.ACTIVITES FROM execution_budgetaire_tempo  join ptba on execution_budgetaire_tempo.ID_PTBA=ptba.PTBA_ID WHERE 1 AND EXECUTION_ID_TEMPO='.$id.' ORDER BY EXECUTION_ID_TEMPO  asc';

		$bindparam="CALL getList('".$bindparam."')";
		$data['infoactivit']= $this->ModelPs->getRequeteOne($bindparam);
		$profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		$data['profil']=$profil;

		$mvt_depense_params=$this->getBindParms('MOUVEMENT_DEPENSE_ID,DESC_MOUVEMENT_DEPENSE','proc_mouvement_depense', 'MOUVEMENT_DEPENSE_ID !=6','MOUVEMENT_DEPENSE_ID asc');
		$data['mvt_depense']= $this->ModelPs->getRequete($callpsreq, $mvt_depense_params);

		$op_tranches=$this->getBindParms('TRANCHE_ID,DESCRIPTION_TRANCHE','op_tranches','TRANCHE_ID!=1','TRANCHE_ID');
		$data['tranches']=$this->ModelPs->getRequete($callpsreq, $op_tranches);
		
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
		$mont_vote='SELECT  PTBA_ID, T1,MONTANT_RESTANT_T1 FROM ptba WHERE PTBA_ID='.$data['info_modif']['ID_PTBA'];
		$mont_vote="CALL getList('".$mont_vote."')";
		$data['mont_vote']= $this->ModelPs->getRequeteOne($mont_vote);
		$data['format_vote']=number_format($data['mont_vote']['MONTANT_RESTANT_T1'],'0',',',' ');
		$data['format_reste']=$data['mont_vote']['MONTANT_RESTANT_T1'];
		$data['tempo_id']=$id;

		###################################### ######################
		$callpsreq = "CALL getRequete(?,?,?,?);";
		$table="ptba JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID";
		$columnselect='MONTANT_RESTANT_T1 as montant_restant,inst.CODE_INSTITUTION AS CODE_MINISTERE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,PTBA_ID,T1 as tranche,QT1 as qte,UNITE,act.CODE_ACTION,act.LIBELLE_ACTION,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME';
		$where="ptba.PTBA_ID ='".$data['info_modif']['ID_PTBA']."'";
		$orderby=' PTBA_ID DESC';
		$where=str_replace("\'", "'", $where);
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace("\'", "'", $bindparams);
		$activiteinfo= $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

		$data['MONTANT_APRES_TRANSFERT']=$data['info_modif']['MONTANT_TRANSFERT']+$data['mont_vote']['MONTANT_RESTANT_T1'];
		$data['tot_ligne_transfert']=0;
		if (!empty($data['info_modif']['MONTANT_TRANSFERT']))
		{
			if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 1)
			{
				$data['reste_act'] = $data['MONTANT_APRES_TRANSFERT']-$data['info_modif']['MONTANT_REALISE'];
			} 
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 2)
			{
				$data['reste_act'] = $data['MONTANT_APRES_TRANSFERT']-$data['info_modif']['MONTANT_REALISE_JURIDIQUE'];
			}
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 3)
			{
				$data['reste_act'] = $data['MONTANT_APRES_TRANSFERT']-$data['info_modif']['MONTANT_REALISE_LIQUIDATION'];
			}
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 4)
			{
				$data['reste_act'] = $data['MONTANT_APRES_TRANSFERT']-$data['info_modif']['MONTANT_REALISE_ORDONNANCEMENT'];

			}
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 5)
			{
				$data['reste_act'] = $data['MONTANT_APRES_TRANSFERT']-$data['info_modif']['MONTANT_REALISE_PAIEMENT'];

			}
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 7)
			{
				$data['reste_act'] = $data['MONTANT_APRES_TRANSFERT']-$data['info_modif']['MONTANT_REALISE_DECAISSEMENT'];

			}
			$tot_ligne_apres_transfert=$data['ligne_reste']+$data['info_modif']['MONTANT_TRANSFERT'];
			$data['tot_ligne_transfert'] = !empty($tot_ligne_apres_transfert) ? $tot_ligne_apres_transfert : 0;
		} else 
		{
			if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 1)
			{
				$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T1']-$data['info_modif']['MONTANT_REALISE'];
			} 
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 2)
			{
				$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T1']-$data['info_modif']['MONTANT_REALISE_JURIDIQUE'];
			}
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 3)
			{
				$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T1']-$data['info_modif']['MONTANT_REALISE_LIQUIDATION'];
			}
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 4)
			{
				$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T1']-$data['info_modif']['MONTANT_REALISE_ORDONNANCEMENT'];
			}
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 5)
			{
				$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T1']-$data['info_modif']['MONTANT_REALISE_PAIEMENT'];
			}
			else if ($data['info_modif']['MOUVEMENT_DEPENSE_ID'] == 7)
			{
				$data['reste_act'] = $data['mont_vote']['MONTANT_RESTANT_T1']-$data['info_modif']['MONTANT_REALISE_DECAISSEMENT'];
			}
		}
		$data['id_demande']=$id_demande;
		return view('App\Modules\demande_new\Views\Proc_Demande_Budget_Corriger_Modif_View',$data);   
	}

	function modifier_activite()
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

		// return $this->getOne($demande);
		$db = db_connect();
		$data=$this->urichk();  
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$IS_TRANSFERT_ACTIVITE=$this->request->getPost('IS_TRANSFERT_ACTIVITE');
		$Mouvement_id = $this->request->getPost('Mouvement_code');
		$demande = $this->request->getPost('demande');
      	$EXECUTION_ID_TEMPO=$this->request->getPost('EXECUTION_ID_TEMPO');

		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		$rules = [
			'PTBA_ID' => [
	        'label' => '',
	        'rules' => 'required',
	        'errors' => [
	        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
	    	]
        ],
        'IS_TRANSFERT_ACTIVITE' => [
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
		if ($IS_TRANSFERT_ACTIVITE==1)
		{
			$rules = [
				'TRANCHE_ID' => [
		        'label' => '',
		        'rules' => 'required',
		        'errors' => [
		        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
		    	]
				],
				'MONTANT_TRANSFERT' => [
		        'label' => '',
		        'rules' => 'required',
		        'errors' => [
		        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
		          ]
		        ]
		    ]; 
		}

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
			$IS_TRANSFERT_ACTIVITE = (!empty($IS_TRANSFERT_ACTIVITE)) ? $IS_TRANSFERT_ACTIVITE:0;
			$COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
			$MARCHE_PUBLIC = $this->request->getPost('MARCHE_PUBLIC');
			$MARCHE_PUBLIC = (!empty($MARCHE_PUBLIC)) ? $MARCHE_PUBLIC:0;
			$programes_code = $this->request->getPost('programes_code');
			$actions = $this->request->getPost('actions');
			$institutions = $this->request->getPost('Institutions');	
			$MONTANT_TRANSFERT=$this->request->getPost('MONTANT_TRANSFERT');
			$MONTANT_TRANSFERT = (!empty($MONTANT_TRANSFERT)) ? $MONTANT_TRANSFERT : 0 ;
			$TRIMESTRE_ID=$this->request->getPost('TRANCHE_ID');
			$TRIMESTRE_ID = (!empty($TRIMESTRE_ID)) ? $TRIMESTRE_ID : 0 ;
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

			$data_modify="EXECUTION_BUDGETAIRE_ID=".$demande." ,ID_PTBA=".$PTBA_ID." ,MONTANT_REALISE=".$montant_realise.",TRANCHE_ID=".$tranche_id.",USER_ID=".$user_id.",DOC_RACCROCHE='".$DOCUMENT."',COMMENTAIRE='".$COMMENTAIRE."',INSTITUTION_ID=".$institutions.",CODE_PROGRAMME='".$programes_code."',CODE_ACTION='".$actions."',MOUVEMENT_DEPENSE_ID=".$Mouvement_id.",PREUVE='".$DOCUMENT_PREUVE."',MARCHE_PUBLIQUE=".$MARCHE_PUBLIC.",NUMERO_BON_ENGAGEMENT='".$numero_bon."',DATE_BON_ENGAGEMENT='".$date_bon."',MONTANT_REALISE_JURIDIQUE=".$montant_realise_jurid.",MONTANT_REALISE_LIQUIDATION=".$montant_realise_liq.",MONTANT_REALISE_ORDONNANCEMENT=".$montant_realise_ord.",IS_TRANSFERT_ACTIVITE=".$IS_TRANSFERT_ACTIVITE.",MONTANT_TRANSFERT=".$MONTANT_TRANSFERT.",TRIMESTRE_ID=".$TRIMESTRE_ID.",MONTANT_REALISE_PAIEMENT=".$montant_realise_paie.",DATE_TITRE_DECAISSEMENT='".$date_decais."',NUMERO_TITRE_DECAISSEMNT='".$numero_decaiss."',MONTANT_REALISE_DECAISSEMENT=".$montant_realise_decais;
			$this->update_all_table($tempo_table,$data_modify,$where);		
			return $this->getOne($demande);
		}
    else
    {
      return $this->modifier($EXECUTION_ID_TEMPO,$demande);
    }
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