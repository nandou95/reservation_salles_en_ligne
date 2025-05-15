<?php
/**
* Rapport des pip par piliers
* Fait par ninette@mediabox.bi
* le 30/11/2023
*/
## Appel de l'espace de nom du Controllers
namespace App\Modules\dashboard\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
###declaration d'une classe controlleur
class Rapport_Pip_piliers extends BaseController
{
	protected $session;
	protected $ModelPs;
	###fonction constructeur
	function __construct()
	{
		# code...
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->ModelS = new ModelS();
		$this->session = \Config\Services::session();
	}

	###fonction qui retourne les couleurs
	public function getcolor() 
	{
		$chars = 'ABCDEF0123456789';
		$color = '#';
		for ( $i= 0; $i < 6; $i++ )
		{
			$color.= $chars[rand(0, strlen($chars) -1)];
		}
		return $color;
	}
	function index()
	{
		$data=$this->urichk();
		$session  = \Config\Services::session();
		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }
    if($session->get('SESSION_SUIVIE_PTBA_TABLEAU_BORD_PIP_PILIER')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
		$user_id ='';
		$inst_connect ='';
		$prof_connect ='';
	  $type_connect ='';
	 $requete_type="SELECT INSTITUTION_ID, DESCRIPTION_INSTITUTION as Name FROM inst_institutions WHERE 1   GROUP BY INSTITUTION_ID,DESCRIPTION_INSTITUTION"; 
		$data['type_ministre']=$this->ModelPs->getRequete('CALL getTable("'.$requete_type.'")');
	  $pilliers="SELECT pilier.ID_PILIER,pilier.DESCR_PILIER FROM `pilier` WHERE 1 GROUP BY ID_PILIER, DESCR_PILIER ";
		$data['pillierrrr']=$this->ModelPs->getRequete('CALL getTable("'.$pilliers.'")');
		$data['INSTITUTION_ID']=$this->request->getPost('');
		$data['ID_PILIER']=$this->request->getPost('');
		$data['prof_connect']=$prof_connect;
		$data['type_connect']=$type_connect;
		return view('App\Modules\dashboard\Views\Rapport_Pip_pilliers_View',$data);
	}
     ##fonction get_rapport qui permet d'afficher le rapport et appel des filtres
	public function get_rapport()
	{
		$data=$this->urichk();
		$db = db_connect(); 
		$session  = \Config\Services::session();
		$ID_PILIER=$this->request->getVar('ID_PILIER');
		$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
		$inst_conn=$this->request->getVar('inst_conn');
		$cond='';
		$titre="".lang("messages_lang.pip_rapport_pilier_annee")." ::";
		if(!empty($INSTITUTION_ID)){
			$cond.=" AND pip_demande_infos_supp.INSTITUTION_ID= ".$INSTITUTION_ID;
		}
		$condition='';
		if(!empty($ID_PILIER))
		{
			$condition.=" AND pip_demande_infos_supp.ID_PILIER= ".$ID_PILIER;
			$pilliers="SELECT DESCR_PILIER FROM `pilier` WHERE ID_PILIER=".$ID_PILIER;
			$pilier=$this->ModelPs->getRequeteOne('CALL getTable("'.$pilliers.'")');
			$titre="".lang("messages_lang.pip_rapport_pilier_annee")." ".$pilier['DESCR_PILIER']." ::";
		}
        // pilier.ID_PILIER
		$total="SELECT SUM(`TOTAl_BIF`) AS TOT  FROM `pip_demande_source_financement` JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_demande_source_financement.ID_DEMANDE_INFO_SUPP JOIN pip_demande_source_financement_valeur_cible ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."  ".$condition."";
      //total à utiliser lors du calcul du pourcentage
		$tot=$this->ModelPs->getRequeteOne('CALL getTable("'.$total.'")');
     //Anee 2 2024-2025
		$pillier2="SELECT pilier.ID_PILIER AS ID,pilier.DESCR_PILIER AS NAME,(SELECT SUM(`TOTAl_BIF`) AS NBRE  FROM `pip_demande_source_financement` JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_demande_source_financement.ID_DEMANDE_INFO_SUPP JOIN pip_demande_source_financement_valeur_cible ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT WHERE pip_demande_infos_supp.ID_PILIER=pilier.ID_PILIER AND pip_demande_source_financement_valeur_cible.ANNEE_BUDGETAIRE_ID=2  AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."  ".$condition.") AS NBRE FROM pilier WHERE 1   GROUP BY pilier.ID_PILIER,pilier.DESCR_PILIER"; 
		$nbr_pillier2=$this->ModelPs->getRequete('CALL getTable("'.$pillier2.'")'); 
		$data_project2="";
		$data_total2=0;
		foreach ($nbr_pillier2 as $key) {
			$get_exec=($key->NBRE)?$key->NBRE:'0';
      // calcul du pourcentage
			$px=0;
			if ($tot['TOT']>0)
			{
				$px=($get_exec/$tot['TOT'])*100;
			}
			$data_total2=$data_total2+$get_exec;
			$data_project2.="{name:'".$this->str_replacecatego(trim($key->NAME))."',y:".$px.",key:'".$key->ID."',key2:'2'},";
		   }
      //Anee 3 2025 - 2026
		$pillier3="SELECT pilier.ID_PILIER AS ID,pilier.DESCR_PILIER AS NAME,(SELECT SUM(`TOTAl_BIF`) AS NBRE  FROM `pip_demande_source_financement` JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_demande_source_financement.ID_DEMANDE_INFO_SUPP JOIN pip_demande_source_financement_valeur_cible ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT WHERE pip_demande_infos_supp.ID_PILIER=pilier.ID_PILIER AND pip_demande_source_financement_valeur_cible.ANNEE_BUDGETAIRE_ID=3 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0 ".$cond."  ".$condition.") AS NBRE FROM pilier WHERE 1   GROUP BY pilier.ID_PILIER,pilier.DESCR_PILIER"; 
		$nbr_pillier3=$this->ModelPs->getRequete('CALL getTable("'.$pillier3.'")'); 
		$data_project3="";
		$data_total3=0;
		foreach ($nbr_pillier3 as $key) {
			$get_exec=($key->NBRE)?$key->NBRE:'0';
      // calcul du pourcentage
			$px=0;
			if ($tot['TOT']>0)
			   {
				$px=($get_exec/$tot['TOT'])*100;
			   }
			$data_total3=$data_total3+$get_exec;
			$data_project3.="{name:'".$this->str_replacecatego(trim($key->NAME))."',y:".$px.",key:'".$key->ID."',key2:'3'},";
		   }
    //Anee 4 2026 - 2027
		$pillier4="SELECT pilier.ID_PILIER AS ID,pilier.DESCR_PILIER AS NAME,(SELECT SUM(`TOTAl_BIF`) AS NBRE  FROM `pip_demande_source_financement` JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_demande_source_financement.ID_DEMANDE_INFO_SUPP JOIN pip_demande_source_financement_valeur_cible ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT WHERE pip_demande_infos_supp.ID_PILIER=pilier.ID_PILIER AND pip_demande_source_financement_valeur_cible.ANNEE_BUDGETAIRE_ID=4 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0  ".$cond."  ".$condition.") AS NBRE FROM pilier WHERE 1  GROUP BY pilier.ID_PILIER,pilier.DESCR_PILIER"; 
		 $nbr_pillier4=$this->ModelPs->getRequete('CALL getTable("'.$pillier4.'")'); 
		 $data_project4="";
		 $data_total4=0;
		foreach ($nbr_pillier4 as $key) {
			$get_exec=($key->NBRE)?$key->NBRE:'0';
      // calcul du pourcentage
			$px=0;
			if ($tot['TOT']>0)
			{
				$px=($get_exec/$tot['TOT'])*100;
			}
			$data_total4=$data_total4+$get_exec;
			$data_project4.="{name:'".$this->str_replacecatego(trim($key->NAME))."',y:".$px.",key:'".$key->ID."',key2:'4'},";
		}
     // ".number_format($total,0,'',' ')."
		$total=0;
		$total=$data_total4+$data_total3+$data_total2;
		

		echo json_encode(array('rapp'=>$rapp));
										}


																			public function detail_pil()
																			     {
																				$data=$this->urichk();
																				$db=db_connect(); 
																				$session  = \Config\Services::session();
																				$KEY=$this->request->getPost('key');
																				$KEY2=$this->request->getPost('key2');
																				$INSTITUTION_ID=$this->request->getVar('INSTITUTION_ID');
																				$ID_PILIER=$this->request->getVar('ID_PILIER');
																				$USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
																				$inst_conn=$this->request->getVar('inst_conn');
																				$cond11='';
																				if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
																				{
																					$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
																					$user_connect=("SELECT user_affectaion.`INSTITUTION_ID`,inst_institutions.NIVEAU_VISION inst_institutions FROM user_affectaion JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE USER_ID=".$user_id." ");
																					$user_connect_req=$this->ModelPs->getRequete(' CALL getTable("'.$user_connect.'")');
																					$nombre=count($user_connect_req);
																				}
																				else{
																					return redirect('Login_Ptba');
																				}
																				$criteres=" AND pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID=".$KEY2;
																				if(!empty($INSTITUTION_ID)){
																					$criteres.=" AND inst_institutions.INSTITUTION_ID= ".$INSTITUTION_ID;
																				}
																				$criteres1="";
																				if(!empty($ID_PILIER)){
																					$criteres1.=" AND pilier.ID_PILIER= ".$ID_PILIER;
																				}
																				$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
																				$query_principal="SELECT pip_valeur_nomenclature_livrable.MONTANT_NOMENCALTURE, pip_cadre_mesure_resultat_livrable.TOTAL_DURE_PROJET, pip_cadre_mesure_resultat_livrable.TOTAL_TRIENNAL,annee_budgetaire.ANNEE_DESCRIPTION, pip_demande_infos_supp.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION FROM `pip_valeur_nomenclature_livrable` JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_infos_supp on pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP=pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID  JOIN annee_budgetaire ON annee_budgetaire.ANNEE_BUDGETAIRE_ID=pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID WHERE 1 ".$cond11." ".$criteres." ".$criteres1." ";
																				$limit='LIMIT 0,10';
																				if($_POST['length'] != -1)
																				{
																					$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
																				}
																				$order_by='';
																				if($_POST['order']['0']['column']!=0)
																				{
																					$order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY SOURCE_FINANCEMENT_VALEUR_CIBLE  ASC'; 
																				}
																				$search = !empty($_POST['search']['value']) ? ("AND ( DESCRIPTION_INSTITUTION LIKE '%$var_search%' 
																					OR NOM_PROJET LIKE '%$var_search%' OR ANNEE_DESCRIPTION LIKE '%$var_search%' )") : '';
																				$critere=" AND pip_demande_infos_supp.ID_PILIER =".$KEY;
																				$conditions=$query_principal.' '.$critere.'  '.$search.' '.$order_by.'   '.$limit;
																				$query_filter=$query_principal.' '.$critere.'  '.$search;
																				$query_secondaire = 'CALL `getTable`("' . $conditions . '");';
																				$fetch_data = $this->ModelPs->datatable($query_secondaire);
																				$u=0;
																				$data = array();
																				foreach ($fetch_data as $row) 
																				{
																					$u++;
																					$pilier=array();
																					$pilier[] ='<font color="#000000" size=2><label>'.$u.'</label></font> ';
																					if(strlen($row->DESCRIPTION_INSTITUTION) > 10){
																						$pilier[] =(strlen($row->DESCRIPTION_INSTITUTION) > 10) ? substr($row->DESCRIPTION_INSTITUTION, 0, 9) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>' : $row->DESCRIPTION_INSTITUTION;
																					}else{
																						$pilier[] ='<font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
																					}
																					$pilier[] ='<font color="#000000" size=2><label>'.$row->NOM_PROJET.'</label></font>';
																					$pilier[] ='<font color="#000000" size=2><label>'.$row->ANNEE_DESCRIPTION.'</label></font>'; 
																					$pilier[] ="".number_format($row->MONTANT_NOMENCALTURE,0,'.',' ')."";
																					$data[] = $pilier;        
																				}
																				$recordsTotal ="CALL `getTable`('" . $query_principal . "');";
																				$recordsFiltered ="CALL `getTable`('" . $query_filter . "');";
																				$output = array(
																					"draw" => intval($_POST['draw']),
																					"recordsTotal" =>count($this->ModelPs->datatable($query_principal)),
																					"recordsFiltered" =>count($this->ModelPs->datatable($query_filter)),
																					"data" => $data
																				);
																				echo json_encode($output);
																			}



																			public function liste()
																			{
																				$critere="";
																				$critere1="";
																				$ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
																				$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
																				if (!empty($INSTITUTION_ID))
																				{
																					$critere.=" AND pip_demande_infos_supp.INSTITUTION_ID=".$INSTITUTION_ID;
																				}  
																				$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
																				$var_search = str_replace("'", "\'", $var_search);
																				$var_search=$this->str_replacecatego($var_search);
																				$group = "";
																				$critaire = "";
																				$limit = 'LIMIT 0,1000';
																				if ($_POST['length'] != -1)
																				{
																					$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
																				}
																				$order_by = '';

															$order_column = array('inst_institutions.CODE_INSTITUTION','DESCR_AXE_INTERVATION_PND','NOM_PROJET','DESCRIPTION_INSTITUTION','DESCR_STATUT_PROJET','DESCR_PILIER','');

														$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY inst_institutions.CODE_INSTITUTION ASC';

														$search = !empty($_POST['search']['value']) ? (' AND (NOM_PROJET LIKE "%' . $var_search . '%" OR ID_DEMANDE_INFO_SUPP LIKE "%' . $var_search . '%" OR DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%" OR NOM_PROJET LIKE "%' . $var_search . '%" OR ID_DEMANDE_INFO_SUPP LIKE "%' . $var_search . '%" OR DESCR_AXE_INTERVATION_PND LIKE "%' . $var_search . '%")') : '';
                              
													$conditions = $critaire . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
                        
													$conditionsfilter = $critaire . ' ' . $search . ' ' . $group;
													$requetedebase = 'SELECT pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP,DESCR_STATUT_PROJET,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,DESCR_AXE_INTERVATION_PND,inst_institutions.CODE_INSTITUTION,pip_demande_infos_supp.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,pilier.DESCR_PILIER,inst_institutions_programmes.INTITULE_PROGRAMME FROM `pip_demande_infos_supp`  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID   JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE=pip_demande_infos_supp.ID_OBJECT_STRATEGIQUE  JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND  JOIN pip_statut_projet statut ON statut.ID_STATUT_PROJET=pip_demande_infos_supp.ID_STATUT_PROJET  JOIN pilier on pilier.ID_PILIER=pip_demande_infos_supp.ID_PILIER JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME  WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0  '.$critere.' '.$critere1.'';
															$requetedebase = str_replace("'", "\'", $requetedebase);
															$requetedebases = $requetedebase . ' ' . $conditions;
															$requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
															$query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
															$fetch_projets = $this->ModelPs->datatable($query_secondaire);
															$data = array();
															$u = 1;
															$stat ='';
															foreach ($fetch_projets as $row)
																		{
																	$sub_array = array();
																	$sub_array[] = $u++;
																	$callpsreq = "CALL `getRequete`(?,?,?,?);";
																	$table = "pip_valeur_nomenclature_livrable JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN annee_budgetaire ON annee_budgetaire.ANNEE_BUDGETAIRE_ID=pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID";
																	$columnselect = " pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID, annee_budgetaire.ANNEE_DESCRIPTION, pip_valeur_nomenclature_livrable.MONTANT_NOMENCALTURE";
																	$where = "ID_DEMANDE_INFO_SUPP=" . $row->ID_DEMANDE_INFO_SUPP;
																	$orderby = 'ANNEE_BUDGETAIRE_ID ASC';
																	$where = str_replace("\'", "'", $where);
																	$db = db_connect();
																	$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
																	$bindparams34 = str_replace("\'", "'", $bindparamss);
																	$livrable = $this->ModelPs->getRequete($callpsreq, $bindparams34);
																	$table_anne = " pip_demande_source_financement_valeur_cible JOIN pip_demande_source_financement ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT";
																	$columnselect_anne = "ANNEE_BUDGETAIRE_ID,SOURCE_FINANCEMENT_VALEUR_CIBLE,pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT";
																	$where_anne = "ID_DEMANDE_INFO_SUPP=" . $row->ID_DEMANDE_INFO_SUPP;
																	$orderby_anne = 'pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT ASC';
																	$where_anne = str_replace("\'", "'", $where_anne);
																	$db = db_connect();
																	$bindparams34_anne= [$db->escapeString($columnselect_anne), $db->escapeString($table_anne), $db->escapeString($where_anne), $db->escapeString($orderby_anne)];
																	$bindparams3411 = str_replace("\'", "'", $bindparams34_anne);
																	$valeur_cible_anne = $this->ModelPs->getRequete($callpsreq, $bindparams34_anne);
																		if (strlen($row->DESCRIPTION_INSTITUTION) > 16) {
																			$sub_array[] = mb_substr($row->DESCRIPTION_INSTITUTION, 0, 15) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>';
																					}else{
																		$sub_array[] ='<font color="#000000" ><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
																					}
																			if (strlen($row->NOM_PROJET) >15) {
																		$sub_array[] = mb_substr($row->NOM_PROJET, 0, 12) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->NOM_PROJET.'"><i class="fa fa-eye"></i></a>';
																					}else{
																		$sub_array[] ='<font color="#000000" ><label>'.$row->NOM_PROJET.'</label></font>';
																					 }
																					if (strlen($row->DESCR_PILIER) > 12) {
																	$sub_array[] = mb_substr($row->DESCR_PILIER, 0, 11) .'...<a class="btn-sm" data-toggle="tooltip" title="'.$row->DESCR_PILIER.'"><i class="fa fa-eye"></i></a>';
																					}else{
																	$sub_array[] ='<font color="#000000" ><label>'.$row->DESCR_PILIER.'</label></font>';
																					} 
																	$anne1 = 0;
																	$anne2 = 0;
																	$anne3 = 0;
																	$projet_one="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$row->ID_DEMANDE_INFO_SUPP." "; 
																	$projets=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_one.'")');
																	$get_projects = !empty($projets['TAUX']) ? $projets['TAUX'] : '0';
																	if (isset($valeur_cible_anne[0]))
																	{
																		$anne1 = $valeur_cible_anne[0]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
																	}

																	if (isset($valeur_cible_anne[1]))
																	{
																		$anne2 = $valeur_cible_anne[1]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
																	}

																	if (isset($valeur_cible_anne[2]))
																	{
																		$anne3 = $valeur_cible_anne[2]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
																	}
																	$sub_array[] = number_format($anne1, '0', ',', ' ');
																	$sub_array[] = number_format($anne2, '0', ',', ' ');
																	$sub_array[] = number_format($anne3, '0', ',', ' ');
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
																			}

   //function pour exporter le Rapport de suivie evaluation dans excel
																			function exporter($INSTITUTION_ID)
																			{
																				$USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
																				if(empty($USER_IDD))
																				{
																					return redirect('Login_Ptba/do_logout');
																				}
																				$db = db_connect();
																				$callpsreq = "CALL getRequete(?,?,?,?);";
																				$criteres="";
																				if(!empty($INSTITUTION_ID))
																				{
																					$criteres.=" AND  pip_demande_infos_supp.INSTITUTION_ID= ".$INSTITUTION_ID;
																				}
																				$criteres1="";
																				if (!empty($ID_AXE_INTERVENTION_PND)){
																					$criteres.=" AND axe_intervention_pnd.ID_AXE_INTERVENTION_PND=".$ID_AXE_INTERVENTION_PND;
																				}
																				$getRequete="SELECT pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP,DESCR_STATUT_PROJET,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,DESCR_AXE_INTERVATION_PND,pip_demande_infos_supp.NOM_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,pilier.DESCR_PILIER,inst_institutions_programmes.INTITULE_PROGRAMME FROM `pip_demande_infos_supp`  JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=pip_demande_infos_supp.INSTITUTION_ID   JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE=pip_demande_infos_supp.ID_OBJECT_STRATEGIQUE  JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND=pip_demande_infos_supp.ID_AXE_INTERVENTION_PND  JOIN pip_statut_projet statut ON statut.ID_STATUT_PROJET=pip_demande_infos_supp.ID_STATUT_PROJET  JOIN pilier on pilier.ID_PILIER=pip_demande_infos_supp.ID_PILIER JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=pip_demande_infos_supp.ID_PROGRAMME  WHERE 1 AND  pip_demande_infos_supp.IS_FINISHED=1 AND  pip_demande_infos_supp.IS_ANNULER=0  ".$criteres." ";
																				$getData = $this->ModelPs->datatable("CALL getTable('" . $getRequete . "')");
																				$spreadsheet = new Spreadsheet();
																				$sheet = $spreadsheet->getActiveSheet();
																				$sheet->setCellValue('A1', 'INSTITUTION');
																				$sheet->setCellValue('B1', 'PROJET');
																				$sheet->setCellValue('C1', 'PILIERS');
																				$sheet->setCellValue('D1', '2024-2025');
																				$sheet->setCellValue('E1', '2025-2026');
																				$sheet->setCellValue('F1', '2026-2027');           
																				$rows = 3;
      //boucle pour les institutions 
																				foreach ($getData as $key)
																				{
																					$callpsreq = "CALL `getRequete`(?,?,?,?);";
																					$table = "pip_valeur_nomenclature_livrable JOIN pip_cadre_mesure_resultat_livrable ON pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE=pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN annee_budgetaire ON annee_budgetaire.ANNEE_BUDGETAIRE_ID=pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID";
																					$columnselect = " pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID, annee_budgetaire.ANNEE_DESCRIPTION, pip_valeur_nomenclature_livrable.MONTANT_NOMENCALTURE";
																					$where = "ID_DEMANDE_INFO_SUPP=" . $key->ID_DEMANDE_INFO_SUPP;
																					$orderby = 'ANNEE_BUDGETAIRE_ID ASC';
																					$where = str_replace("\'", "'", $where);
																					$db = db_connect();
																					$bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
																					$bindparams34 = str_replace("\'", "'", $bindparamss);
																					$livrable = $this->ModelPs->getRequete($callpsreq, $bindparams34);
																					$table_anne = " pip_demande_source_financement_valeur_cible JOIN pip_demande_source_financement ON pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT";
																					$columnselect_anne = "ANNEE_BUDGETAIRE_ID,SOURCE_FINANCEMENT_VALEUR_CIBLE,pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT";
																					$where_anne = "ID_DEMANDE_INFO_SUPP=" . $key->ID_DEMANDE_INFO_SUPP;
																					$orderby_anne = 'pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT ASC';
																					$where_anne = str_replace("\'", "'", $where_anne);
																					$db = db_connect();
																					$bindparams34_anne= [$db->escapeString($columnselect_anne), $db->escapeString($table_anne), $db->escapeString($where_anne), $db->escapeString($orderby_anne)];
																					$bindparams3411 = str_replace("\'", "'", $bindparams34_anne);
																					$valeur_cible_anne = $this->ModelPs->getRequete($callpsreq, $bindparams34_anne);
																					$anne1 = 0;
																					$anne2 = 0;
																					$anne3 = 0;
																					$projet_one="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$key->ID_DEMANDE_INFO_SUPP." "; 
																					$projets=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_one.'")');
																					$get_projects = !empty($projets['TAUX']) ? $projets['TAUX'] : '0';
																					$anne1 = 0;
																					$anne2 = 0;
																					$anne3 = 0;
																					$projet_one="SELECT pip_taux_echange.TAUX FROM pip_demande_source_financement JOIN pip_taux_echange on pip_taux_echange.TAUX_ECHANGE_ID=pip_demande_source_financement.TAUX_ECHANGE_ID  WHERE  pip_demande_source_financement.ID_DEMANDE_INFO_SUPP=".$key->ID_DEMANDE_INFO_SUPP." "; 
																					$projets=$this->ModelPs->getRequeteOne('CALL getTable("'.$projet_one.'")');
																					$get_projects = !empty($projets['TAUX']) ? $projets['TAUX'] : '0';
																					if (isset($valeur_cible_anne[0]))
																					{
																						$anne1 = $valeur_cible_anne[0]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
																					}
																					if (isset($valeur_cible_anne[1]))
																					{
																						$anne2 = $valeur_cible_anne[1]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
																					}
																					if (isset($valeur_cible_anne[2]))
																					{
																						$anne3 = $valeur_cible_anne[2]->SOURCE_FINANCEMENT_VALEUR_CIBLE * $get_projects;
																					}
																					$sheet->setCellValue('A' . $rows, $key->DESCRIPTION_INSTITUTION);
																					$sheet->setCellValue('B' . $rows, $key->NOM_PROJET);
																					$sheet->setCellValue('C' . $rows, $key->DESCR_PILIER);
																					$sheet->setCellValue('E' . $rows, $anne1);
																					$sheet->setCellValue('E' . $rows, $anne2);
																					$sheet->setCellValue('F' . $rows, $anne3);
																					$rows++;
																				} 
																				$writer = new Xlsx($spreadsheet);
																				$writer->save('world.xlsx');
																				return $this->response->download('world.xlsx', null)->setFileName('PIP par piliers.xlsx');
																				return redirect('dashboard/Rapport_Pip_piliers');
																			}
																			public function getBindParmsLimit($columnselect, $table, $where, $orderby, $Limit)
																			{
																				$db = db_connect();
																				$columnselect = str_replace("\'", "'", $columnselect);
																				$table = str_replace("\'", "'", $table);
																				$where = str_replace("\'", "'", $where);
																				$orderby = str_replace("\'", "'", $orderby);
																				$Limit = str_replace("\'", "'", $Limit);
																				$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby), $db->escapeString($Limit)];
																				$bindparams = str_replace('\"', '"', $bindparams);
																				return $bindparams;
																			}
   //Fonction pour gérer les caractères speciaux
																			function str_replacecatego($name)
																			{
																				$catego=str_replace("'"," ",$name);
																				$catego=str_replace("  "," ",$catego);
																				$catego=str_replace("\n"," ",$catego);
																				$catego=str_replace("\t"," ",$catego);
																				$catego=str_replace("\r"," ",$catego);
																				$catego=str_replace("@"," ",$catego);
																				$catego=str_replace("&"," ",$catego);
																				$catego=str_replace(">"," ",$catego);
																				$catego=str_replace("   "," ",$catego);
																				$catego=str_replace("?"," ",$catego);
																				$catego=str_replace("#"," ",$catego);
																				$catego=str_replace("%"," ",$catego);
																				$catego=str_replace("%!"," ",$catego);
																				$catego=str_replace(""," ",$catego);
																				return $catego;
																			}
																		}
																	?>