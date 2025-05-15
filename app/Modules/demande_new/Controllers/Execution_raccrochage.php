<?php

/**
 * auteur:    Baleke Kahamire Bonheur
 * Titre:     Execution racrochage
 * telephone: (+257) 67 86 62 83
 * WhatsApp:  (+257) 67 86 62 83
 * Email:     bonheur.baleke@mediabox.bi
 * Date:      11 jan 2024
 **/

namespace  App\Modules\demande_new\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;

class Execution_raccrochage extends BaseController
{
  protected $session;
  protected $ModelPs;
  protected $library;
  protected $validation;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  public function budget_deja_raccroche() 
  {
    $data=$this->urichk();
		if(empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID'))){
			return redirect('Login_Ptba/do_logout');
		}

		$session  = \Config\Services::session();
		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }

    $ind=$this->indicateur_deuxieme_trimestre_new();
    $data['get_qte_phys']=$ind['get_qte_phys'];
    $data['get_pas_qte_phys']=$ind['get_pas_qte_phys'];
    $data['get_racrochet'] = $ind['get_racrochet'];
    $data['get_deja_racrochet'] = $ind['get_deja_racrochet'];

    $get_sous_tutelle = $this->getBindParms('CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','DESCRIPTION_INSTITUTION ASC');
	  $sous_tutelle = $this->ModelPs->getRequete("CALL `getRequete`(?,?,?,?)", $get_sous_tutelle);
    $data['institutions_user'] = $sous_tutelle;
		return view('App\Modules\demande_new\Views\Execution_raccrochage_List_view',$data);   
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

	public function listing()
	{
		$user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba');
		}
    $session  = \Config\Services::session();
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
		$order_column = array('ligne.CODE_NOMENCLATURE_BUDGETAIRE','LIBELLE',1,'CREDIT_VOTE','TRANSFERTS_CREDITS','CREDIT_APRES_TRANSFERT','ENG_BUDGETAIRE','ENG_JURIDIQUE','LIQUIDATION','ORDONNANCEMENT','PAIEMENT','DECAISSEMENT');
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID   ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR ORDONNANCEMENT LIKE "%' . $var_search . '%" OR PAIEMENT LIKE "%' . $var_search . '%" OR DECAISSEMENT LIKE "%' . $var_search . '%" OR ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR  ENG_JURIDIQUE LIKE "%' . $var_search . '%" OR  LIQUIDATION LIKE "%' . $var_search . '%" OR  LIBELLE LIKE "%' . $var_search . '%" OR PAIEMENT LIKE "%' . $var_search . '%")') : '';
		$conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
		$conditionsfilter = $critere . ' ' . $search . ' ' . $group;
		
		$requetedebase= 'SELECT EXECUTION_BUDGETAIRE_ID,CREDIT_VOTE,LIBELLE,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,DATE_DEMANDE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,EXECUTION_BUDGETAIRE_ID,IS_RACCROCHE,TRANSFERTS_CREDITS,CREDIT_APRES_TRANSFERT FROM execution_budgetaire_new JOIN inst_institutions_ligne_budgetaire ligne ON execution_budgetaire_new.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE IS_RACCROCHE = 1 AND TRIMESTRE_ID = 2' . $institution;

		$var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
		$limit = 'LIMIT 0,10';

		$requetedebases = $requetedebase . ' ' . $conditions;
		$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
		$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
		$fetch_data = $this->ModelPs->datatable($query_secondaire);
		$data = [];
		$u=1;

		foreach ($fetch_data as $row)
		{
			$racc = 'SELECT COUNT(EXECUTION_BUDGETAIRE_RACCROCHAGE_ID) as nbre FROM execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_new exe ON exe.EXECUTION_BUDGETAIRE_ID=racc.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN proc_mouvement_depense mvt ON mvt.MOUVEMENT_DEPENSE_ID=exe.MOUVEMENT_DEPENSE_ID WHERE 1 AND exe.TRIMESTRE_ID = 2 AND exe.EXECUTION_BUDGETAIRE_ID = '.$row->EXECUTION_BUDGETAIRE_ID;
			$racc = "CALL `getTable`('" . $racc . "');";
			$raccrocher = $this->ModelPs->getRequeteOne($racc);

			$racrocha='<center><a onclick="get_detail_activite('.$row->EXECUTION_BUDGETAIRE_ID.')" href="javascript:;" ><button class="btn btn-primary"><b style="color:white;">'.$raccrocher['nbre'].'</b></button></a></center>';
			$sub_array = [];
			if (mb_strlen($row->LIBELLE) > 8){ 
				$LIBELLE =  mb_substr($row->LIBELLE, 0, 8) .'...<a class="btn-sm" title="'.lang('messages_lang.liste_Afficher').'"  onclick="show_modal('.$row->EXECUTION_BUDGETAIRE_ID.')"><i class="fa fa-eye"></i></a>';

			}else
			{
				$LIBELLE =  $row->LIBELLE;
			}
			$sub_array[] = $row->IMPUTATION;
			$sub_array[] = $LIBELLE;
			$sub_array[] = $racrocha;
			$TRANSFERTS_CREDITS = number_format($row->TRANSFERTS_CREDITS,2,',',' ');
			$CREDIT_VOTE = number_format($row->CREDIT_VOTE,2,',',' ');
			$engage=number_format($row->ENG_BUDGETAIRE,'2',',',' ');
			$jurdique=number_format($row->ENG_JURIDIQUE,'2',',',' ');
			$liquidation=number_format($row->LIQUIDATION,'2',',',' ');
			$ordanance=number_format($row->ORDONNANCEMENT,'2',',',' ');
			$transfert=number_format($row->ORDONNANCEMENT,'2',',',' ');
			$transfert_apres=number_format($row->CREDIT_APRES_TRANSFERT,'2',',',' ');
			$paiement=number_format($row->PAIEMENT,'2',',',' ');
			$decaisment=number_format($row->DECAISSEMENT,'2',',',' ');

			$sub_array[] = !empty($CREDIT_VOTE) ? ' '.$CREDIT_VOTE :'0' ;
			$sub_array[] = !empty($TRANSFERTS_CREDITS) ? ' '.$TRANSFERTS_CREDITS:'0';		
			$sub_array[] = !empty($transfert_apres) ? ' '.$transfert_apres :'0';		
			$sub_array[] = !empty($engage) ? ' '.$engage :'0' ;
			$sub_array[] = !empty($jurdique) ? ' '.$jurdique :'0';
			$sub_array[] = !empty($liquidation) ? ' '.$liquidation:'0';
			$sub_array[] = !empty($ordanance) ? ' '.$ordanance :'0';
			$sub_array[] = !empty($paiement) ? ' '.$paiement :'0';
			$sub_array[] = !empty($decaisment) ? ' '.$decaisment:'0';
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
		$libel="SELECT LIBELLE FROM execution_budgetaire  WHERE 1 AND TRIMESTRE_ID = 2 AND EXECUTION_BUDGETAIRE_ID = ".$id;
		$libel='CALL `getList`("'.$libel.'")';
		$libelle_commentaire = $this->ModelPs->getRequeteOne( $libel);
		$output = ["data123" => $libelle_commentaire['LIBELLE']];
		return $this->response->setJSON($output);
	}

	function liste_activites($id=0)
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

		$query_principal="SELECT racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,ptba.CODES_PROGRAMMATIQUE,mvt.DESC_MOUVEMENT_DEPENSE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,ptba.ACTIVITES,exe.SOUS_TUTEL_ID,racc.MONTANT_RACCROCHE,racc.MONTANT_RACCROCHE_JURIDIQUE,racc.MONTANT_RACCROCHE_LIQUIDATION,racc.MONTANT_RACCROCHE_ORDONNANCEMENT,racc.MONTANT_RACCROCHE_PAIEMENT,racc.MONTANT_RACCROCHE_DECAISSEMENT FROM execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_new exe ON exe.EXECUTION_BUDGETAIRE_ID=racc.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN proc_mouvement_depense mvt ON mvt.MOUVEMENT_DEPENSE_ID=exe.MOUVEMENT_DEPENSE_ID JOIN inst_institutions_ligne_budgetaire ligne ON exe.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND exe.TRIMESTRE_ID = 2 AND exe.EXECUTION_BUDGETAIRE_ID = ".$id;
		$var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$limit='LIMIT 0,10';
		if($_POST['length'] != -1)
		{
			$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
		}

		$order_by='';
		$order_column='';
		$order_column= array('ptba.CODES_PROGRAMMATIQUE','ptba.ACTIVITES','ligne.CODE_NOMENCLATURE_BUDGETAIRE','racc.MONTANT_RACCROCHE','mvt.DESC_MOUVEMENT_DEPENSE',1,1,1,1,1);

		$order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID   DESC';

		$search = !empty($_POST['search']['value']) ? (" AND (ptba.ACTIVITES LIKE '%". $var_search."%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%" . $var_search . "%' OR racc.UNITE LIKE '%" . $var_search . "%' OR ptba.CODES_PROGRAMMATIQUE LIKE '%" . $var_search . "%' OR racc.MONTANT_RACCROCHE LIKE '%" . $var_search . "%')") : '';
		
		$critaire = '';
		$query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.'   '.$limit;

		$query_filter = $query_principal.' '.$search.' '.$critaire;
		$requete='CALL `getList`("'.$query_secondaire.'")';
		$fetch_cov_frais = $this->ModelPs->datatable( $requete);
		$data = array();
		$u=0;
		foreach($fetch_cov_frais as $row)
		{	
			$sub_array = array();

			if (mb_strlen($row->ACTIVITES) > 8){ 
				$ACTIVITES =  mb_substr($row->ACTIVITES, 0, 8) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->ACTIVITES.'"><i class="fa fa-eye"></i></a>';
			}else{
				$ACTIVITES =  $row->ACTIVITES;
			}			
			$sub_array[] = $row->CODES_PROGRAMMATIQUE;
			$sub_array[] = $ACTIVITES;
			$sub_array[] = $row->IMPUTATION;
			$sub_array[]=$row->DESC_MOUVEMENT_DEPENSE;
			$sub_array[] = number_format($row->MONTANT_RACCROCHE,'2',',',' ');
			
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_JURIDIQUE)) ? number_format($row->MONTANT_RACCROCHE_JURIDIQUE,'2',',',' ') : 0 ;
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_LIQUIDATION)) ? number_format($row->MONTANT_RACCROCHE_LIQUIDATION,'2',',',' ') : 0 ;
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_ORDONNANCEMENT)) ? number_format($row->MONTANT_RACCROCHE_ORDONNANCEMENT,'2',',',' ') : 0 ;
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_PAIEMENT)) ? number_format($row->MONTANT_RACCROCHE_PAIEMENT,'2',',',' ') : 0 ;
			$sub_array[] = (!empty($row->MONTANT_RACCROCHE_DECAISSEMENT)) ? number_format($row->MONTANT_RACCROCHE_DECAISSEMENT,'2',',',' ') : 0 ;
			$data[] = $sub_array;
		}

		$requeteqp='CALL `getList`("'.$query_principal.'")';
		$recordsTotal = $this->ModelPs->datatable( $requeteqp);
		$requeteqf='CALL `getList`("'.$query_filter.'")';
		$recordsFiltered = $this->ModelPs->datatable( $requeteqf);
		$output = array(
			"draw" => intval($_POST['draw']),
			"recordsTotal" =>count($recordsTotal),
			"recordsFiltered" => count($recordsFiltered),
			"data" => $data
		);
		echo json_encode($output);
	}

  public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}
}
?>