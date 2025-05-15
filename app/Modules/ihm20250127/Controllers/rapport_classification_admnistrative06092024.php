<?php 
/**
* HAKIZUMUKAMA Egide
* Rapport de classification admnistrative
* Email: egideh@mediabox.bi
* Telephone: 62 129 8777
* le 28/09/2023
*/
/*
*NDERAGAKURA Alain Charbel
*Titre: Ajout des export word et PDF
*WhatsApp: +25776887837
*Email pro: charbel@mediabox.bi
*Date: 04 mars 2024
*/

namespace App\Modules\ihm\Controllers;   
use App\Controllers\BaseController;
use Config\Database;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Dompdf\Dompdf;
ini_set('max_execution_time', 20000);
ini_set('memory_limit','4048M');
ob_end_clean();

class rapport_classification_admnistrative extends BaseController
{
  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  //function qui appelle le view de la liste 
  function index()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
    $data = $this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
		//Sélectionner les institutions
    $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`','inst_institutions','1','`CODE_INSTITUTION` ASC');
    $data['instit'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
    //get annee budgetaire en cours
    $data['annee_budgetaire_en_cours']=$this->get_annee_budgetaire();
    //Sélectionner les annees budgetaires
    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID<='.$data['annee_budgetaire_en_cours'],'ANNEE_BUDGETAIRE_ID ASC');
    $data['annee_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $get_ann_budg);
    
    return view('App\Modules\ihm\Views\rapport_classification_administrative_view',$data);   
  }

	//liste des ptba
  function listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    // Get the database connection instance
    $db = Database::connect();
    // Store the original sql_mode value
    $result = $db->query("SELECT @@SESSION.sql_mode AS sql_mode");
    $rowx = $result->getRow();
    $originalSqlMode = $rowx->sql_mode;
    // Modify the sql_mode option to disable only_full_group_by
    $db->query("SET sql_mode=''");

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
    $ACTION_ID = $this->request->getPost('ACTION_ID');
    $ANNEE_BUDGETAIRE_ID = $this->request->getPost('ANNEE_BUDGETAIRE_ID');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');

    //$ann=$this->get_annee_budgetaire();
    
    $critere1="";
    $critere2="";
    $critere3="";
    $critere_join="";
    $critere4="AND ptba.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    $critere_date="";

		//Filtre par institution
    if(!empty($INSTITUTION_ID))
    {
      // $critere_join=" JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID";
      $critere1 = " AND inst.INSTITUTION_ID = ".$INSTITUTION_ID;
        //Filtre par programme
      if(!empty($PROGRAMME_ID))
      {
        // $critere_join.=" JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=ptba.PROGRAMME_ID";
        $critere2=" AND prog.PROGRAMME_ID=".$PROGRAMME_ID;
      		//Filtre par action
        if(!empty($ACTION_ID))
        {
          // $critere_join.=" JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba.ACTION_ID";
          $critere3=" AND act.ACTION_ID=".$ACTION_ID;
        }
      } 
    }
    //filtre pour date debut et date fin
    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date=" AND DATE_BON_ENGAGEMENT >='".$DATE_DEBUT."'";
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date=" AND DATE_BON_ENGAGEMENT <= '".$DATE_FIN."'";
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date=" AND DATE_BON_ENGAGEMENT BETWEEN '".$DATE_DEBUT."' AND '".$DATE_FIN."'";
    }

    $criteres = $critere1." ".$critere2." ".$critere3." ".$critere4;

    $query_principal="SELECT ptba.PTBA_TACHE_ID,ptba.BUDGET_ANNUEL,pap.DESC_PAP_ACTIVITE,ptba.DESC_TACHE,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,prog.INTITULE_PROGRAMME,act.LIBELLE_ACTION,ptba.CODE_NOMENCLATURE_BUDGETAIRE FROM ptba_tache ptba JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_actions act ON act.ACTION_ID=ptba.ACTION_ID LEFT JOIN pap_activites pap ON pap.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID WHERE 1 ".$criteres;
    $limit="LIMIT 2";
    if($_POST['length'] != -1)
    {
      $limit="LIMIT ".$_POST["start"].",".$_POST["length"];
    }

    $order_by="";
    $order_column="";
    $order_column= array('inst.DESCRIPTION_INSTITUTION','prog.INTITULE_PROGRAMME',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);

    $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ." ".$_POST['order']['0']['dir'] : " ORDER BY inst.CODE_INSTITUTION ASC";

    $search = !empty($_POST['search']['value']) ? (" AND (CODE_NOMENCLATURE_BUDGETAIRE LIKE '%". $var_search."%' OR ptba.DESC_TACHE LIKE '%".$var_search."%' OR ptba.RESULTAT_ATTENDUS_TACHE LIKE '%".$var_search."%' OR inst.CODE_INSTITUTION LIKE '%".$var_search."%' OR inst.DESCRIPTION_INSTITUTION LIKE '%".$var_search."%' OR prog.CODE_PROGRAMME LIKE '%".$var_search."%' OR prog.INTITULE_PROGRAMME LIKE '%".$var_search."%' OR act.CODE_ACTION LIKE '%".$var_search."%' OR act.LIBELLE_ACTION LIKE '%".$var_search."%')") : "";

    
    $query_secondaire = $query_principal." ".$search." ".$order_by." ".$limit;
    $query_secondaire = str_replace("\\", "", $query_secondaire);	

    $query_filter = $query_principal." ".$search;
    $query_filter=str_replace('"','\\"',$query_filter);
    $requete='CALL `getTable`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      $get_vote = "SELECT exec.PTBA_TACHE_ID,SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.PAIEMENT) AS PAIEMENT,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec WHERE PTBA_TACHE_ID=".$info->PTBA_TACHE_ID."".$critere_date;
      $get_vote='CALL `getTable`("'.$get_vote.'")';
      $executes = $this->ModelPs->getRequeteOne($get_vote);      
      $CREDIT_VOTE=!empty($info->BUDGET_ANNUEL) ?$info->BUDGET_ANNUEL : '0';

      //Montant transferé
      $param_mont_trans = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$info->PTBA_TACHE_ID,'1');
      $param_mont_trans=str_replace('\"','"',$param_mont_trans);
      $mont_transf=$this->ModelPs->getRequeteOne($psgetrequete,$param_mont_trans);
      $MONTANT_TRANSFERT=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$info->PTBA_TACHE_ID,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($psgetrequete,$param_mont_recep);
      $MONTANT_RECEPTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $TRANSFERTS_CREDITS_RESTE=(floatval($MONTANT_TRANSFERT) - floatval($MONTANT_RECEPTION));

      if($TRANSFERTS_CREDITS_RESTE >= 0)
      {
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE;
      }
      else{
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($CREDIT_VOTE) - floatval($MONTANT_TRANSFERT)) + floatval($MONTANT_RECEPTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['PTBA_TACHE_ID']==$mont_recep['PTBA_TACHE_ID'])
      {
        $TRANSFERTS_CREDITS = $MONTANT_TRANSFERT;
        $CREDIT_APRES_TRANSFERT = floatval($CREDIT_VOTE);
      }

      $MONTANT_ENGAGE=!empty($executes['MONTANT_ENGAGE']) ? $executes['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($executes['MONTANT_JURIDIQUE']) ? $executes['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($executes['MONTANT_LIQUIDATION']) ? $executes['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($executes['MONTANT_ORDONNANCEMENT']) ? $executes['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT=!empty($executes['PAIEMENT']) ? $executes['PAIEMENT'] : '0';
      $MONTANT_DECAISSEMENT=!empty($executes['MONTANT_DECAISSEMENT'])?$executes['MONTANT_DECAISSEMENT']:'0';

      $post=array();
      $DESCRIPTION_INSTITUTION = (mb_strlen($info->DESCRIPTION_INSTITUTION) > 9) ? (mb_substr($info->DESCRIPTION_INSTITUTION, 0, 9) . '...<a class="btn-sm" title="'.$info->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>') : $info->DESCRIPTION_INSTITUTION;

      $INTITULE_PROGRAMME = (mb_strlen($info->INTITULE_PROGRAMME) > 9) ? (mb_substr($info->INTITULE_PROGRAMME, 0, 9) . '...<a class="btn-sm" title="'.$info->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>') : $info->INTITULE_PROGRAMME;

      $LIBELLE_ACTION = (mb_strlen($info->LIBELLE_ACTION) > 7) ? (mb_substr($info->LIBELLE_ACTION, 0, 7) . '...<a class="btn-sm" title="'.$info->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>') : $info->LIBELLE_ACTION;

      $DESC_TACHE = (mb_strlen($info->DESC_TACHE) > 9) ? (mb_substr($info->DESC_TACHE, 0, 9) . '...<a class="btn-sm" title="'.$info->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $info->DESC_TACHE;

      $DESC_TACHE = mb_convert_encoding($DESC_TACHE, 'UTF-8', 'UTF-8');

      $post[]=$DESCRIPTION_INSTITUTION;
      $post[]=$INTITULE_PROGRAMME;
      $post[]=$LIBELLE_ACTION = (!empty($LIBELLE_ACTION)) ? $LIBELLE_ACTION : 'N/A';
      $post[]=$info->CODE_NOMENCLATURE_BUDGETAIRE;
      $post[]=$DESC_TACHE;
      $post[]=number_format($CREDIT_VOTE,0,","," ");
      $post[] = number_format($TRANSFERTS_CREDITS,0,","," ");//trans cerdit
      $post[] = number_format($CREDIT_APRES_TRANSFERT,0,","," ");//credit apres transfert
      $post[] = number_format($MONTANT_ENGAGE,0,","," ");//engag budgetaie
      $post[] = number_format($MONTANT_JURIDIQUE,0,","," ");//engag juridik
      $post[] = number_format($MONTANT_LIQUIDATION,0,","," ");//liquidat
      $post[] = number_format($MONTANT_ORDONNANCEMENT,0,","," ");//ordonancem
      $post[] = number_format($PAIEMENT,0,","," ");//paiement
      $post[] = number_format($MONTANT_DECAISSEMENT,0,","," ");
      $data[]=$post;  
    }

    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' . $query_principal . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' . $query_filter . '")');

    /*$requeteqp='CALL `getList`("'.$query_principal.'")';
    $recordsTotal = $this->ModelPs->datatable( $requeteqp);
    $requeteqf='CALL `getList`("'.$query_filter.'")';
    $recordsFiltered = $this->ModelPs->datatable( $requeteqf);*/
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    return $this->response->setJSON($output);
  }

  // LES SELECTS DES ACTIVITES
  //Sélectionner les sous tutelles à partir des institutions
  function get_soutut()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $INSTITUTION_ID =$this->request->getPost('INSTITUTION_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'INSTITUTION_ID='.$INSTITUTION_ID, 'CODE_SOUS_TUTEL ASC');
    $sous_tut = $this->ModelPs->getRequete($callpsreq, $bind_sous_tut);
    $get_type=$this->getBindParms('`TYPE_INSTITUTION_ID`','inst_institutions','`INSTITUTION_ID`='.$INSTITUTION_ID,'TYPE_INSTITUTION_ID');

    $type=$this->ModelPs->getRequeteOne($callpsreq,$get_type);
    //Declaration des labels pour l'internalisation
    $input_select = lang("messages_lang.labelle_selecte");
    $html='<option value="">'.$input_select.'</option>';

    if(!empty($sous_tut) )
    {
      foreach($sous_tut as $key)
      { 
        if($key->SOUS_TUTEL_ID==set_value('SOUS_TUTEL_ID'))
        {
          $html.= "<option value='".$key->SOUS_TUTEL_ID."' selected>".$key->CODE_SOUS_TUTEL."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_SOUS_TUTEL."</option>";
        }
        else
        {
          $html.= "<option value='".$key->SOUS_TUTEL_ID."'>".$key->CODE_SOUS_TUTEL."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_SOUS_TUTEL."</option>";
        }
      }
    }
    else
    {
      //Declaration des labels pour l'internalisation
      $input_select = lang("messages_lang.labelle_selecte");
      $html='<option value="">'.$input_select.'</option>';
    }

    $output = array(
      'status' => TRUE , 'html' => $html, 'TYPE_INSTITUTION_ID' => $type['TYPE_INSTITUTION_ID'],
    );
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //Sélectionner les programmes à partir des sous tutelles
  function get_prog()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $INSTITUTION_ID =$this->request->getPost('INSTITUTION_ID');
    // $SOUS_TUTEL_ID =$this->request->getPost('SOUS_TUTEL_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $get_prog = "SELECT INSTITUTION_ID,PROGRAMME_ID,INTITULE_PROGRAMME, CODE_PROGRAMME FROM inst_institutions_programmes WHERE INSTITUTION_ID =".$INSTITUTION_ID." ORDER BY CODE_PROGRAMME";

    $details='CALL `getTable`("'.$get_prog.'")';
    $prog = $this->ModelPs->getRequete($details);
    //Declaration des labels pour l'internalisation
    $input_select = lang("messages_lang.labelle_selecte");
    $html='<option value="">'.$input_select.'</option>';

    if(!empty($prog))
    {
      foreach($prog as $key)
      {
        if($key->PROGRAMME_ID==set_value('PROGRAMME_ID'))
        {
          $html.= "<option value='".$key->PROGRAMME_ID."' selected>".$key->CODE_PROGRAMME."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->INTITULE_PROGRAMME."</option>";
        }
        else
        {
          $html.= "<option value='".$key->PROGRAMME_ID."'>".$key->CODE_PROGRAMME."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->INTITULE_PROGRAMME."</option>";
        }
      }
    }
    else
    {
      //Declaration des labels pour l'internalisation
      $input_select = lang("messages_lang.labelle_selecte");
      $html='<option value="">'.$input_select.'</option>';
    }

    $output = array('status' => TRUE , 'html' => $html);
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //Sélectionner les actions à partir des programmes
  function get_action()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $PROGRAMME_ID =$this->request->getPost('PROGRAMME_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_action = "SELECT PROGRAMME_ID,ACTION_ID, CODE_ACTION,LIBELLE_ACTION FROM inst_institutions_actions WHERE PROGRAMME_ID=".$PROGRAMME_ID." ORDER BY CODE_ACTION";

    $details='CALL `getTable`("'.$get_action.'")';
    $action = $this->ModelPs->getRequete($details);
    //Declaration des labels pour l'internalisation
    $input_select = lang("messages_lang.labelle_selecte");
    $html='<option value="">'.$input_select.'</option>';
    if(!empty($action) )
    {
      foreach($action as $key)
      {
        if($key->ACTION_ID==set_value('ACTION_ID'))
        {
          $html.= "<option value='".$key->ACTION_ID."'>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
        }
        else
        {
          $html.= "<option value='".$key->ACTION_ID."'>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
        }
      }
    }
    else
    {
      //Declaration des labels pour l'internalisation
      $input_select = lang("messages_lang.labelle_selecte");
      $html='<option value="">'.$input_select.'</option>';
    }

    $output = array('status' => TRUE , 'html' => $html);
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    // print_r($db->lastQuery);die();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  //Fonction pour exporter des donnees
  public function export($INSTITUTION_ID='',$SOUS_TUTEL_ID='',$PROGRAMME_ID='',$ACTION_ID='',$ANNEE_BUDGETAIRE_ID='',$DATE_DEBUT='',$DATE_FIN='',$activ='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $titre_document="Titres 01 au 76";
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $criteres='';
    $critere_progr='';
    $critere_action='';
    $critere_date="";
    $critere_date_act='';

    $ann_eco=$this->get_annee_budgetaire();

    //filtre pour anne budg
    $critere_annee=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    $critere_anne_ptba=" AND ptba.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    //filtre pour date debut et date fin

    //filtre pour date debut et date fin
    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date=' AND DATE_DEMANDE >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date=' AND DATE_DEMANDE <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date=' AND DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date_act=' AND DATE_BON_ENGAGEMENT >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date_act=' AND DATE_BON_ENGAGEMENT <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date_act=' AND DATE_BON_ENGAGEMENT BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
    $annee_budgetaire = $this->ModelPs->getRequeteOne($callpsreq, $get_ann_budg);
    $annee_dexcr=str_replace('-','/',$annee_budgetaire['ANNEE_DESCRIPTION']);

    $p_deb = '01/07/'.substr($annee_dexcr, 0, 4);

    $periode_debut = (!empty($DATE_DEBUT)) ? date('d/m/Y',strtotime($DATE_DEBUT)) : $p_deb ;
    $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : date("d/m/Y");

    if($ann_eco != $ANNEE_BUDGETAIRE_ID)
    {
      $p_fin = '30/06/'.substr($annee_dexcr,5);
      $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;
    }

    if($INSTITUTION_ID!=0)
    {
      $criteres.=" AND ptba.INSTITUTION_ID=".$INSTITUTION_ID."";
      $get_code = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, 'CODE_INSTITUTION ASC');
      $institutionscode = $this->ModelPs->getRequeteOne($callpsreq, $get_code);
      $titre_document="Titre ".$institutionscode['CODE_INSTITUTION'];
    }

    if($PROGRAMME_ID!=0)
    {
      $critere_progr.=" AND ptba.PROGRAMME_ID=".$PROGRAMME_ID;
    }

    if($ACTION_ID!=0)
    {
      $critere_action.=" AND ptba.ACTION_ID=".$ACTION_ID;
    }

    $get_institutions = $this->getBindParms('DISTINCT ptba.INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions inst JOIN ptba_tache ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID', '1'.$criteres.''.$critere_anne_ptba.'', 'CODE_INSTITUTION ASC');

    $institutions = $this->ModelPs->getRequete($callpsreq, $get_institutions);
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A3', 'CIRCUIT DES DEPENSES');
    $sheet->setCellValue('A4', 'Classification Administrative');
    $sheet->setCellValue('A5', 'EXERCICE '.$annee_dexcr.', N° BUDGET 0          Période du '.$periode_debut.' au '.$periode_fin.'');
    $sheet->setCellValue('A7', $titre_document);
    $sheet->setCellValue('A8', 'Source Financement: 11');
    $sheet->setCellValue('A11', 'IMPUTATION');
    $sheet->setCellValue('B11', 'LIBELLE DE LA TÂCHE');
    $sheet->setCellValue('C11', 'CREDIT VOTE');
    $sheet->setCellValue('D11', 'TRANSFERTS CREDITS');
    $sheet->setCellValue('E11', 'CREDIT APRES TRANSFERT');       
    $sheet->setCellValue('F11', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('G11', 'ENGAGEMENT JURIDIQUE');
    $sheet->setCellValue('H11', 'LIQUIDATION');
    $sheet->setCellValue('I11', 'ORDONNANCEMENT');
    $sheet->setCellValue('J11', 'PAIEMENT');
    $sheet->setCellValue('K11', 'DECAISSEMENT');

    $rows = 13;
    foreach ($institutions as $key)
    {
      $params_infos='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' '.$critere_annee.''.$critere_date.'';
      $params_infos = "CALL `getTable`('" .$params_infos."');";
      $infos_sup= $this->ModelPs->getRequeteOne($params_infos);

      $get_vote_inst = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID."";
      $get_vote_inst='CALL `getTable`("'.$get_vote_inst.'")';
      $votes_inst = $this->ModelPs->getRequeteOne($get_vote_inst);
      $MONTANT_CREDIT_VOTE_1=(!empty($votes_inst['CREDIT_VOTE'])) ? $votes_inst['CREDIT_VOTE'] : 0;

      //Montant transferé
      $param_mont_trans_inst = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_trans_inst=str_replace('\"','"',$param_mont_trans_inst);

      $mont_transf_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_inst);
      $MONTANT_TRANSFERT_INST=floatval($mont_transf_inst['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep_inst = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_recep_inst=str_replace('\"','"',$param_mont_recep_inst);
      $mont_recep_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_inst);
      $MONTANT_RECEPTION_INST=floatval($mont_recep_inst['MONTANT_RECEPTION']);
 
      $MONTANT_TRANSFERT_RESTE_INST = (floatval($MONTANT_TRANSFERT_INST) - floatval($MONTANT_RECEPTION_INST));

      if($MONTANT_TRANSFERT_RESTE_INST >= 0)
      {
        $MONTANT_TRANSFERT_1=$MONTANT_TRANSFERT_RESTE_INST;
      }else{
        $MONTANT_TRANSFERT_1=$MONTANT_TRANSFERT_RESTE_INST*(-1);
      }

      $CREDIT_APRES_TRANSFERT_1=(floatval($MONTANT_CREDIT_VOTE_1) - floatval($MONTANT_TRANSFERT_INST)) + floatval($MONTANT_RECEPTION_INST);

      if($CREDIT_APRES_TRANSFERT_1 < 0){
        $CREDIT_APRES_TRANSFERT_1 = $CREDIT_APRES_TRANSFERT_1*(-1);
      }

      if($mont_transf_inst['INSTITUTION_ID']==$mont_recep_inst['INSTITUTION_ID'])
      {
        $MONTANT_TRANSFERT_1 = $MONTANT_TRANSFERT_INST;
        $CREDIT_APRES_TRANSFERT_1 = floatval($MONTANT_CREDIT_VOTE_1);
      }

      $MONTANT_ENGAGE_1=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE_1=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION_1=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT_1=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT_1=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $MONTANT_DECAISSEMENT_1=!empty($infos_sup['MONTANT_DECAISSEMENT'])?$infos_sup['MONTANT_DECAISSEMENT']:'0';
      $sheet->setCellValue('A' . $rows, $key->CODE_INSTITUTION.'  '.$key->DESCRIPTION_INSTITUTION);
      $sheet->setCellValue('B' . $rows, ' ');
      $sheet->setCellValue('C' . $rows, $MONTANT_CREDIT_VOTE_1);
      $sheet->setCellValue('D' . $rows, $MONTANT_TRANSFERT_1);
      $sheet->setCellValue('E' . $rows, $CREDIT_APRES_TRANSFERT_1);
      $sheet->setCellValue('F' . $rows, $MONTANT_ENGAGE_1); 
      $sheet->setCellValue('G' . $rows, $MONTANT_JURIDIQUE_1);
      $sheet->setCellValue('H' . $rows, $MONTANT_LIQUIDATION_1);
      $sheet->setCellValue('I' . $rows, $MONTANT_ORDONNANCEMENT_1);
      $sheet->setCellValue('J' . $rows, $PAIEMENT_1);
      $sheet->setCellValue('K' . $rows, $MONTANT_DECAISSEMENT_1);
      $rows++;

      $get_program = ' SELECT DISTINCT ptba.PROGRAMME_ID,prog.INSTITUTION_ID,CODE_PROGRAMME,INTITULE_PROGRAMME FROM inst_institutions_programmes prog JOIN ptba_tache ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' '.$critere_progr.' ORDER BY CODE_PROGRAMME ASC';
      $get_program = "CALL `getTable`('" .$get_program."');";
      $programs= $this->ModelPs->getRequete($get_program);

      if(!empty($programs))
      {
        $rows=$rows+1;
        foreach($programs as $key_progr)
        {
          $params_infos_2='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' '.$critere_annee.''.$critere_date;

          $params_infos_2 = "CALL `getTable`('" .$params_infos_2."');";
          $infos_sup_2= $this->ModelPs->getRequeteOne($params_infos_2);

          $get_vote_prog = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID."";
          $get_vote_prog='CALL `getTable`("'.$get_vote_prog.'")';
          $votes_prog = $this->ModelPs->getRequeteOne($get_vote_prog);
          $MONTANT_CREDIT_VOTE_2=!empty($votes_prog['CREDIT_VOTE']) ? $votes_prog['CREDIT_VOTE'] : '0';

          //Montant transferé
          $param_mont_trans_prog = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID,'1');
          $param_mont_trans_prog=str_replace('\"','"',$param_mont_trans_prog);

          $mont_transf_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_prog);
          $MONTANT_TRANSFERT_PROG=floatval($mont_transf_prog['MONTANT_TRANSFERT']);

          //Montant receptionné
          $param_mont_recep_prog = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID,'1');
          $param_mont_recep_prog=str_replace('\"','"',$param_mont_recep_prog);
          $mont_recep_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_prog);
          $MONTANT_RECEPTION_PROG=floatval($mont_recep_prog['MONTANT_RECEPTION']);
     
          $MONTANT_TRANSFERT_RESTE_PROG = (floatval($MONTANT_TRANSFERT_PROG) - floatval($MONTANT_RECEPTION_PROG));

          if($MONTANT_TRANSFERT_RESTE_PROG >= 0)
          {
            $MONTANT_TRANSFERT_2=$MONTANT_TRANSFERT_RESTE_PROG;
          }else{
            $MONTANT_TRANSFERT_2=$MONTANT_TRANSFERT_RESTE_PROG*(-1);
          }

          $CREDIT_APRES_TRANSFERT_2=(floatval($MONTANT_CREDIT_VOTE_2) - floatval($MONTANT_TRANSFERT_PROG)) + floatval($MONTANT_RECEPTION_PROG);

          if($CREDIT_APRES_TRANSFERT_2 < 0){
            $CREDIT_APRES_TRANSFERT_2 = $CREDIT_APRES_TRANSFERT_2*(-1);
          }

          if($mont_transf_prog['PROGRAMME_ID']==$mont_recep_prog['PROGRAMME_ID'])
          {
            $MONTANT_TRANSFERT_2 = $MONTANT_TRANSFERT_PROG;
            $CREDIT_APRES_TRANSFERT_2 = floatval($MONTANT_CREDIT_VOTE_2);
          }

          $MONTANT_ENGAGE_2=!empty($infos_sup_2['MONTANT_ENGAGE']) ? $infos_sup_2['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_2=!empty($infos_sup_2['MONTANT_JURIDIQUE']) ? $infos_sup_2['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_2=!empty($infos_sup_2['MONTANT_LIQUIDATION']) ? $infos_sup_2['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_2=!empty($infos_sup_2['MONTANT_ORDONNANCEMENT']) ? $infos_sup_2['MONTANT_ORDONNANCEMENT'] : '0';
          $PAIEMENT_2=!empty($infos_sup_2['PAIEMENT']) ? $infos_sup_2['PAIEMENT'] : '0';
          $MONTANT_DECAISSEMENT_2=!empty($infos_sup_2['MONTANT_DECAISSEMENT'])?$infos_sup_2['MONTANT_DECAISSEMENT']:'0';
          $sheet->setCellValue('A' . $rows, '    '.$key_progr->CODE_PROGRAMME.'  '.$key_progr->INTITULE_PROGRAMME);
          $sheet->setCellValue('B' . $rows, ' ');
          $sheet->setCellValue('C' . $rows, $MONTANT_CREDIT_VOTE_2);
          $sheet->setCellValue('D' . $rows, $MONTANT_TRANSFERT_2);
          $sheet->setCellValue('E' . $rows, $CREDIT_APRES_TRANSFERT_2);
          $sheet->setCellValue('F' . $rows, $MONTANT_ENGAGE_2); 
          $sheet->setCellValue('G' . $rows, $MONTANT_JURIDIQUE_2);
          $sheet->setCellValue('H' . $rows, $MONTANT_LIQUIDATION_2);
          $sheet->setCellValue('I' . $rows, $MONTANT_ORDONNANCEMENT_2);
          $sheet->setCellValue('J' . $rows, $PAIEMENT_2);
          $sheet->setCellValue('K' . $rows, $MONTANT_DECAISSEMENT_2);
          $rows++; 

          $get_action='SELECT DISTINCT ptba.ACTION_ID, ptba.PROGRAMME_ID,CODE_ACTION,LIBELLE_ACTION FROM inst_institutions_actions act JOIN ptba_tache ptba ON ptba.ACTION_ID=act.ACTION_ID  WHERE  ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND  ptba.PROGRAMME_ID= '.$key_progr->PROGRAMME_ID.' '.$critere_action.' ORDER BY CODE_ACTION ASC';
          $get_action = "CALL `getTable`('" .$get_action."');";
          $action= $this->ModelPs->getRequete($get_action);

          if(!empty($action))
          {
            $rows=$rows+1;

            foreach($action as $key_action)
            {
              $params_infos_3='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' '.$critere_annee.''.$critere_date;
              $params_infos_3 = "CALL `getTable`('" .$params_infos_3."');";
              $infos_sup_3= $this->ModelPs->getRequeteOne($params_infos_3);

              $get_vote_act = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID." AND ptba.ACTION_ID=".$key_action->ACTION_ID."";
              $get_vote_act='CALL `getTable`("'.$get_vote_act.'")';
              $votes_act = $this->ModelPs->getRequeteOne($get_vote_act);
              $MONTANT_CREDIT_VOTE_3=!empty($votes_act['CREDIT_VOTE']) ? $votes_act['CREDIT_VOTE'] : '0';

              //Montant transferé
              $param_mont_trans_act = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID,'1');
              $param_mont_trans_act=str_replace('\"','"',$param_mont_trans_act);
              $mont_transf_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_act);
              $MONTANT_TRANSFERT_ACT=floatval($mont_transf_act['MONTANT_TRANSFERT']);

              //Montant receptionné
              $param_mont_recep_act = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID,'1');
              $param_mont_recep_act=str_replace('\"','"',$param_mont_recep_act);
              $mont_recep_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_act);
              $MONTANT_RECEPTION_ACT=floatval($mont_recep_act['MONTANT_RECEPTION']);
         
              $MONTANT_TRANSFERT_RESTE_ACT = (floatval($MONTANT_TRANSFERT_ACT) - floatval($MONTANT_RECEPTION_ACT));

              if($MONTANT_TRANSFERT_RESTE_ACT >= 0)
              {
                $MONTANT_TRANSFERT_3=$MONTANT_TRANSFERT_RESTE_ACT;
              }else{
                $MONTANT_TRANSFERT_3=$MONTANT_TRANSFERT_RESTE_ACT*(-1);
              }

              $CREDIT_APRES_TRANSFERT_3=(floatval($MONTANT_CREDIT_VOTE_3) - floatval($MONTANT_TRANSFERT_ACT)) + floatval($MONTANT_RECEPTION_ACT);

              if($CREDIT_APRES_TRANSFERT_3 < 0){
                $CREDIT_APRES_TRANSFERT_3 = $CREDIT_APRES_TRANSFERT_3*(-1);
              }

              if($mont_transf_act['ACTION_ID']==$mont_recep_act['ACTION_ID'])
              {
                $MONTANT_TRANSFERT_3 = $MONTANT_TRANSFERT_ACT;
                $CREDIT_APRES_TRANSFERT_3 = floatval($MONTANT_CREDIT_VOTE_3);
              }

              $MONTANT_ENGAGE_3=!empty($infos_sup_3['MONTANT_ENGAGE']) ? $infos_sup_3['MONTANT_ENGAGE'] : '0';
              $MONTANT_JURIDIQUE_3=!empty($infos_sup_3['MONTANT_JURIDIQUE']) ? $infos_sup_3['MONTANT_JURIDIQUE'] : '0';
              $MONTANT_LIQUIDATION_3=!empty($infos_sup_3['MONTANT_LIQUIDATION']) ? $infos_sup_3['MONTANT_LIQUIDATION'] : '0';
              $MONTANT_ORDONNANCEMENT_3=!empty($infos_sup_3['MONTANT_ORDONNANCEMENT']) ? $infos_sup_3['MONTANT_ORDONNANCEMENT'] : '0';
              $PAIEMENT_3=!empty($infos_sup_3['PAIEMENT']) ? $infos_sup_3['PAIEMENT'] : '0';
              $MONTANT_DECAISSEMENT_3=!empty($infos_sup_3['MONTANT_DECAISSEMENT'])?$infos_sup_3['MONTANT_DECAISSEMENT']:'0';

              $sheet->setCellValue('A' . $rows, '      '.$key_action->CODE_ACTION.'  '.$key_action->LIBELLE_ACTION);
              $sheet->setCellValue('B' . $rows, ' ');
              $sheet->setCellValue('C' . $rows, $MONTANT_CREDIT_VOTE_3);
              $sheet->setCellValue('D' . $rows, $MONTANT_TRANSFERT_3);
              $sheet->setCellValue('E' . $rows, $CREDIT_APRES_TRANSFERT_3);
              $sheet->setCellValue('F' . $rows, $MONTANT_ENGAGE_3); 
              $sheet->setCellValue('G' . $rows, $MONTANT_JURIDIQUE_3);
              $sheet->setCellValue('H' . $rows, $MONTANT_LIQUIDATION_3);
              $sheet->setCellValue('I' . $rows, $MONTANT_ORDONNANCEMENT_3);
              $sheet->setCellValue('J' . $rows, $PAIEMENT_3);
              $sheet->setCellValue('K' . $rows, $MONTANT_DECAISSEMENT_3);
              $rows++;

              $get_imputation='SELECT DISTINCT ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE FROM   inst_institutions_ligne_budgetaire ligne JOIN ptba_tache ptba ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE ASC';

              $get_imputation = "CALL `getTable`('" .$get_imputation."');";
              $imputations= $this->ModelPs->getRequete($get_imputation);

              if(!empty($imputations))
              {
                $rows=$rows+1;

                foreach($imputations as $key_imput)
                {
                  $params_infos_4='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID.' '.$critere_annee.''.$critere_date;

                  $params_infos_4 = "CALL `getTable`('" .$params_infos_4."');";
                  $infos_sup_4= $this->ModelPs->getRequeteOne($params_infos_4);

                  $get_vote_imp = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID." AND ptba.ACTION_ID =".$key_action->ACTION_ID." AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID."";
                  $get_vote_imp='CALL `getTable`("'.$get_vote_imp.'")';
                  $votes_imp = $this->ModelPs->getRequeteOne($get_vote_imp);
                  $MONTANT_CREDIT_VOTE_4=!empty($votes_imp['CREDIT_VOTE']) ? $votes_imp['CREDIT_VOTE'] : '0';
                  
                  //Montant transferé
                  $param_mont_trans_imp = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID,'1');
                  $param_mont_trans_imp=str_replace('\"','"',$param_mont_trans_imp);

                  $mont_transf_imp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_imp);
                  $MONTANT_TRANSFERT_IMP=floatval($mont_transf_imp['MONTANT_TRANSFERT']);

                  //Montant receptionné
                  $param_mont_recep_imp = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID,'1');
                  $param_mont_recep_imp=str_replace('\"','"',$param_mont_recep_imp);
                  $mont_recep_imp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_imp);
                  $MONTANT_RECEPTION_IMP=floatval($mont_recep_imp['MONTANT_RECEPTION']);
             
                  $MONTANT_TRANSFERT_RESTE_IMP = (floatval($MONTANT_TRANSFERT_IMP) - floatval($MONTANT_RECEPTION_IMP));

                  if($MONTANT_TRANSFERT_RESTE_IMP >= 0)
                  {
                    $MONTANT_TRANSFERT_4=$MONTANT_TRANSFERT_RESTE_IMP;
                  }else{
                    $MONTANT_TRANSFERT_4=$MONTANT_TRANSFERT_RESTE_IMP*(-1);
                  }

                  $CREDIT_APRES_TRANSFERT_4=(floatval($MONTANT_CREDIT_VOTE_4) - floatval($MONTANT_TRANSFERT_IMP)) + floatval($MONTANT_RECEPTION_IMP);

                  if($CREDIT_APRES_TRANSFERT_4 < 0){
                    $CREDIT_APRES_TRANSFERT_4 = $CREDIT_APRES_TRANSFERT_4*(-1);
                  }

                  if($mont_transf_imp['CODE_NOMENCLATURE_BUDGETAIRE_ID']==$mont_recep_imp['CODE_NOMENCLATURE_BUDGETAIRE_ID'])
                  {
                    $MONTANT_TRANSFERT_4 = $MONTANT_TRANSFERT_IMP;
                    $CREDIT_APRES_TRANSFERT_4 = floatval($MONTANT_CREDIT_VOTE_4);
                  }

                  $MONTANT_ENGAGE_4=!empty($infos_sup_4['MONTANT_ENGAGE']) ? $infos_sup_4['MONTANT_ENGAGE'] : '0';
                  $MONTANT_JURIDIQUE_4=!empty($infos_sup_4['MONTANT_JURIDIQUE']) ? $infos_sup_4['MONTANT_JURIDIQUE'] : '0';
                  $MONTANT_LIQUIDATION_4=!empty($infos_sup_4['MONTANT_LIQUIDATION']) ? $infos_sup_4['MONTANT_LIQUIDATION'] : '0';
                  $MONTANT_ORDONNANCEMENT_4=!empty($infos_sup_4['MONTANT_ORDONNANCEMENT']) ? $infos_sup_4['MONTANT_ORDONNANCEMENT'] : '0';
                  $PAIEMENT_4=!empty($infos_sup_4['PAIEMENT']) ? $infos_sup_4['PAIEMENT'] : '0';
                  $MONTANT_DECAISSEMENT_4=!empty($infos_sup_4['MONTANT_DECAISSEMENT'])?$infos_sup_4['MONTANT_DECAISSEMENT']:'0';

                  $sheet->setCellValue('A' . $rows, '        '.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE);
                  $sheet->setCellValue('B' . $rows, ' ');
                  $sheet->setCellValue('C' . $rows, $MONTANT_CREDIT_VOTE_4);
                  $sheet->setCellValue('D' . $rows, $MONTANT_TRANSFERT_4);
                  $sheet->setCellValue('E' . $rows, $CREDIT_APRES_TRANSFERT_4);
                  $sheet->setCellValue('F' . $rows, $MONTANT_ENGAGE_4); 
                  $sheet->setCellValue('G' . $rows, $MONTANT_JURIDIQUE_4);
                  $sheet->setCellValue('H' . $rows, $MONTANT_LIQUIDATION_4);
                  $sheet->setCellValue('I' . $rows, $MONTANT_ORDONNANCEMENT_4);
                  $sheet->setCellValue('J' . $rows, $PAIEMENT_4);
                  $sheet->setCellValue('K' . $rows, $MONTANT_DECAISSEMENT_4);
                  $rows++;

                  if ($activ==1)
                  {
                    $get_tache='SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE INSTITUTION_ID='.$key->INSTITUTION_ID.' AND PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ACTION_ID ='.$key_action->ACTION_ID.' AND CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ';

                    $get_tache = "CALL `getTable`('" .$get_tache."');";
                    $taches= $this->ModelPs->getRequete($get_tache);

                    if(!empty($taches))
                    {
                      $rows=$rows+1;

                      foreach($taches as $key_tache)
                      {
                        $params_infos_5='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.PAIEMENT) AS PAIEMENT,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba.PTBA_TACHE_ID='.$key_tache->PTBA_TACHE_ID.''.$critere_annee.' '.$critere_date_act.'';

                        $params_infos_5= "CALL `getTable`('" .$params_infos_5."');";
                        $infos_sup_5= $this->ModelPs->getRequeteOne($params_infos_5);

                        $get_vote_task = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID." AND ptba.ACTION_ID =".$key_action->ACTION_ID." AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID =".$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID." AND ptba.PTBA_TACHE_ID=".$key_tache->PTBA_TACHE_ID;
                        $get_vote_task ='CALL `getTable`("'.$get_vote_task.'")';
                        $votes_task = $this->ModelPs->getRequeteOne($get_vote_task);
                        $MONTANT_CREDIT_VOTE_5=!empty($votes_task['CREDIT_VOTE']) ? $votes_task['CREDIT_VOTE'] : '0';

                        //Montant transferé
                        $param_mont_trans_task = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$key_tache->PTBA_TACHE_ID,'1');
                        $param_mont_trans_task=str_replace('\"','"',$param_mont_trans_task);

                        $mont_transf_task=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_task);
                        $MONTANT_TRANSFERT_TASK=floatval($mont_transf_task['MONTANT_TRANSFERT']);

                        //Montant receptionné
                        $param_mont_recep_task = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$key_tache->PTBA_TACHE_ID,'1');
                        $param_mont_recep_task=str_replace('\"','"',$param_mont_recep_task);
                        $mont_recep_task=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_task);
                        $MONTANT_RECEPTION_TASK=floatval($mont_recep_task['MONTANT_RECEPTION']);
                   
                        $MONTANT_TRANSFERT_RESTE_TASK = (floatval($MONTANT_TRANSFERT_TASK) - floatval($MONTANT_RECEPTION_TASK));

                        if($MONTANT_TRANSFERT_RESTE_TASK >= 0)
                        {
                          $MONTANT_TRANSFERT_5=$MONTANT_TRANSFERT_RESTE_TASK;
                        }else{
                          $MONTANT_TRANSFERT_5=$MONTANT_TRANSFERT_RESTE_TASK*(-1);
                        }

                        $CREDIT_APRES_TRANSFERT_5=(floatval($MONTANT_CREDIT_VOTE_5) - floatval($MONTANT_TRANSFERT_TASK)) + floatval($MONTANT_RECEPTION_TASK);

                        if($CREDIT_APRES_TRANSFERT_5 < 0){
                          $CREDIT_APRES_TRANSFERT_5 = $CREDIT_APRES_TRANSFERT_5*(-1);
                        }

                        if($mont_transf_task['PTBA_TACHE_ID']==$mont_recep_task['PTBA_TACHE_ID'])
                        {
                          $MONTANT_TRANSFERT_5 = $MONTANT_TRANSFERT_TASK;
                          $CREDIT_APRES_TRANSFERT_5 = floatval($MONTANT_CREDIT_VOTE_5);
                        }

                        $MONTANT_ENGAGE_5=!empty($infos_sup_5['MONTANT_ENGAGE']) ? $infos_sup_5['MONTANT_ENGAGE'] : '0';
                        $MONTANT_JURIDIQUE_5=!empty($infos_sup_5['MONTANT_JURIDIQUE']) ? $infos_sup_5['MONTANT_JURIDIQUE'] : '0';
                        $MONTANT_LIQUIDATION_5=!empty($infos_sup_5['MONTANT_LIQUIDATION']) ? $infos_sup_5['MONTANT_LIQUIDATION'] : '0';
                        $MONTANT_ORDONNANCEMENT_5=!empty($infos_sup_5['MONTANT_ORDONNANCEMENT']) ? $infos_sup_5['MONTANT_ORDONNANCEMENT'] : '0';
                        $PAIEMENT_5=!empty($infos_sup_5['PAIEMENT']) ? $infos_sup_5['PAIEMENT'] : '0';
                        $MONTANT_DECAISSEMENT_5=!empty($infos_sup_5['MONTANT_DECAISSEMENT'])?$infos_sup_5['MONTANT_DECAISSEMENT']:'0';

                        $desc_tache = mb_convert_encoding($key_tache->DESC_TACHE, 'UTF-8', 'UTF-8');

                        $sheet->setCellValue('A' . $rows,' ');
                        $sheet->setCellValue('B' . $rows, $desc_tache);
                        $sheet->setCellValue('C' . $rows, $MONTANT_CREDIT_VOTE_5);
                        $sheet->setCellValue('D' . $rows, $MONTANT_TRANSFERT_5);
                        $sheet->setCellValue('E' . $rows, $CREDIT_APRES_TRANSFERT_5);
                        $sheet->setCellValue('F' . $rows, $MONTANT_ENGAGE_5); 
                        $sheet->setCellValue('G' . $rows, $MONTANT_JURIDIQUE_5);
                        $sheet->setCellValue('H' . $rows, $MONTANT_LIQUIDATION_5);
                        $sheet->setCellValue('I' . $rows, $MONTANT_ORDONNANCEMENT_5);
                        $sheet->setCellValue('J' . $rows, $PAIEMENT_5);
                        $sheet->setCellValue('K' . $rows, $MONTANT_DECAISSEMENT_5);
                        $rows++;
                      }
                    } 
                  } 
                }
                $rows=$rows+1;
              } 
            }
          } 
          $rows=$rows+1; 
        } 
      }
      $rows=$rows+1;
    }

    // Set the width of column A to 30
    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(30);
    $sheet->getColumnDimension('E')->setWidth(30);
    $sheet->getColumnDimension('F')->setWidth(30);
    $sheet->getColumnDimension('G')->setWidth(30);
    $sheet->getColumnDimension('H')->setWidth(30);
    $sheet->getColumnDimension('I')->setWidth(30);
    $sheet->getColumnDimension('J')->setWidth(30);
    $sheet->getColumnDimension('K')->setWidth(30);
    $writer = new Xlsx($spreadsheet);

    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('rapport classification administrative.xlsx');
    return redirect('ihm/rapport_classification_administrative');
  }

  //Fonction pour exporter des donnees en pdf
  public function export_pdf($INSTITUTION_ID='',$SOUS_TUTEL_ID='',$PROGRAMME_ID='',$ACTION_ID='',$ANNEE_BUDGETAIRE_ID='',$DATE_DEBUT='',$DATE_FIN='',$activ='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $criteres='';
    // $criteresou='';
    $critere_progr='';
    $critere_action='';
    $critere_date='';
    $critere_date_act='';
    $ann_eco=$this->get_annee_budgetaire();

    //filtre pour anne budg
    $critere_annee=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    $critere_anne_ptba=" AND ptba.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    $titre_document="Titres 01 au 76";
    //filtre pour date debut et date fin
    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date=' AND DATE_DEMANDE >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date=' AND DATE_DEMANDE <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date=' AND DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date_act=' AND DATE_BON_ENGAGEMENT >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date_act=' AND DATE_BON_ENGAGEMENT <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date_act=' AND DATE_BON_ENGAGEMENT BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    if($INSTITUTION_ID!=0)
    {
      $criteres.=" AND ptba.INSTITUTION_ID=".$INSTITUTION_ID."";
      $get_code = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, 'CODE_INSTITUTION ASC');
      $institutionscode = $this->ModelPs->getRequeteOne($callpsreq, $get_code);
      $titre_document="Titre ".$institutionscode['CODE_INSTITUTION'];
    }

    if($PROGRAMME_ID!=0)
    {
      $critere_progr.=" AND ptba.PROGRAMME_ID=".$PROGRAMME_ID;
    }

    if($ACTION_ID!=0)
    {
      $critere_action.=" AND ptba.ACTION_ID=".$ACTION_ID;
    }
    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
    $annee_budgetaire = $this->ModelPs->getRequeteOne($callpsreq, $get_ann_budg);
    $annee_dexcr=str_replace('-','/',$annee_budgetaire['ANNEE_DESCRIPTION']);

    $p_deb = '01/07/'.substr($annee_dexcr, 0, 4);

    $periode_debut = (!empty($DATE_DEBUT)) ? date('d/m/Y',strtotime($DATE_DEBUT)) : $p_deb ;
    $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : date("d/m/Y");

    if($ann_eco != $ANNEE_BUDGETAIRE_ID)
    {
      $p_fin = '30/06/'.substr($annee_dexcr,5);
      $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;
    }

    $get_institutions = $this->getBindParms('DISTINCT ptba.INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions inst JOIN ptba_tache ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID', '1'.$criteres.''.$critere_anne_ptba.'', 'CODE_INSTITUTION ASC');

    $institutions = $this->ModelPs->getRequete($callpsreq, $get_institutions);

    //titre du document word
    $dompdf = new Dompdf();
    // Définir la largeur du tableau
    $tableWidth = '750';
    $html = "<html>";
    $html.= "<body>";
    //titre du document pdf
    $html.='<h3><center>CIRCUIT DES DEPENSES</center></h3>';
    $html.='<h4><center>classification Administrative</center></h4>';
    $html.='<h5><center>EXERCICE: '.$annee_dexcr.', N° Budget  0 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Période du '.$periode_debut.' au '.$periode_fin.'</center></h5>';
    $html.='<h6>'.$titre_document.' <br> Source de Financement 11</h6>';
    $html.= '<table style="border-collapse: collapse; width: ' . $tableWidth . '";font-size: 11.3px; border="1">';
    $html.='<tr>
    <th style="border: 1px solid #000;width:5%">IMPUTATION</th>
    <th style="border: 1px solid #000;">LIBELLE DE L\'ACTIVITE</th>
    <th style="border: 1px solid #000;">CREDIT VOTE</th>
    <th style="border: 1px solid #000;">TRANSFERTS CREDITS</th>
    <th style="border: 1px solid #000;">CREDIT APRES TRANSFERT</th>
    <th style="border: 1px solid #000;">ENG BUDG.</th>
    <th style="border: 1px solid #000;">ENG JURID.</th>
    <th style="border: 1px solid #000;">LIQU.</th>
    <th style="border: 1px solid #000;">ORDON.</th>
    <th style="border: 1px solid #000;">PAIEMENT</th>
    <th style="border: 1px solid #000;">DEC.</th>
    </tr>';

    foreach ($institutions as $key)
    {
      $params_infos='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' '.$critere_annee.''.$critere_date.'';
      $params_infos = "CALL `getTable`('" .$params_infos."');";
      $infos_sup= $this->ModelPs->getRequeteOne($params_infos);

      $get_vote_inst = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID."";
      $get_vote_inst='CALL `getTable`("'.$get_vote_inst.'")';
      $votes_inst = $this->ModelPs->getRequeteOne($get_vote_inst);
      $MONTANT_CREDIT_VOTE_1=(!empty($votes_inst['CREDIT_VOTE'])) ? $votes_task['CREDIT_VOTE'] : 0;

      //Montant transferé
      $param_mont_trans_inst = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_trans_inst=str_replace('\"','"',$param_mont_trans_inst);

      $mont_transf_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_inst);
      $MONTANT_TRANSFERT_INST=floatval($mont_transf_inst['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep_inst = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_recep_inst=str_replace('\"','"',$param_mont_recep_inst);
      $mont_recep_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_inst);
      $MONTANT_RECEPTION_INST=floatval($mont_recep_inst['MONTANT_RECEPTION']);
 
      $MONTANT_TRANSFERT_RESTE_INST = (floatval($MONTANT_TRANSFERT_INST) - floatval($MONTANT_RECEPTION_INST));

      if($MONTANT_TRANSFERT_RESTE_INST >= 0)
      {
        $MONTANT_TRANSFERT_1=$MONTANT_TRANSFERT_RESTE_INST;
      }else{
        $MONTANT_TRANSFERT_1=$MONTANT_TRANSFERT_RESTE_INST*(-1);
      }

      $CREDIT_APRES_TRANSFERT_1=(floatval($MONTANT_CREDIT_VOTE_1) - floatval($MONTANT_TRANSFERT_INST)) + floatval($MONTANT_RECEPTION_INST);

      if($CREDIT_APRES_TRANSFERT_1 < 0){
        $CREDIT_APRES_TRANSFERT_1 = $CREDIT_APRES_TRANSFERT_1*(-1);
      }

      if($mont_transf_inst['INSTITUTION_ID']==$mont_recep_inst['INSTITUTION_ID'])
      {
        $MONTANT_TRANSFERT_1 = $MONTANT_TRANSFERT_INST;
        $CREDIT_APRES_TRANSFERT_1 = floatval($MONTANT_CREDIT_VOTE_1);
      }      

      $MONTANT_ENGAGE_1=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE_1=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION_1=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT_1=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT_1=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $MONTANT_DECAISSEMENT_1=!empty($infos_sup['MONTANT_DECAISSEMENT'])?$infos_sup['MONTANT_DECAISSEMENT']:'0';
      $html.='<tr>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.$key->CODE_INSTITUTION.'  '.$key->DESCRIPTION_INSTITUTION.'</td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">   </td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_CREDIT_VOTE_1,'0',',',' ').'</td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT_1,'0',',',' ').'</td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_1,'0',',',' ').'</td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_1,0,","," ").'</td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_1,0,","," ").'</td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_1,0,","," ").'</td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_1,0,","," ").'</td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($PAIEMENT_1,0,","," ").'</td>';
      $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_1,0,","," ").'</td>';
      $html.='</tr>';

      $get_program = ' SELECT DISTINCT ptba.PROGRAMME_ID,prog.INSTITUTION_ID,CODE_PROGRAMME,INTITULE_PROGRAMME FROM inst_institutions_programmes prog JOIN ptba_tache ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' '.$critere_progr.' ORDER BY CODE_PROGRAMME ASC';
      $get_program = "CALL `getTable`('" .$get_program."');";
      $programs= $this->ModelPs->getRequete($get_program);

      if(!empty($programs))
      {
        foreach($programs as $key_progr)
        {
          $params_infos_2='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' '.$critere_annee.''.$critere_date;

          $params_infos_2 = "CALL `getTable`('" .$params_infos_2."');";
          $infos_sup_2= $this->ModelPs->getRequeteOne($params_infos_2);

          $get_vote_prog = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID."";
          $get_vote_prog='CALL `getTable`("'.$get_vote_prog.'")';
          $votes_prog = $this->ModelPs->getRequeteOne($get_vote_prog);
          $MONTANT_CREDIT_VOTE_2=!empty($votes_prog['CREDIT_VOTE']) ? $votes_prog['CREDIT_VOTE'] : '0';

          //Montant transferé
          $param_mont_trans_prog = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID,'1');
          $param_mont_trans_prog=str_replace('\"','"',$param_mont_trans_prog);

          $mont_transf_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_prog);
          $MONTANT_TRANSFERT_PROG=floatval($mont_transf_prog['MONTANT_TRANSFERT']);

          //Montant receptionné
          $param_mont_recep_prog = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID,'1');
          $param_mont_recep_prog=str_replace('\"','"',$param_mont_recep_prog);
          $mont_recep_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_prog);
          $MONTANT_RECEPTION_PROG=floatval($mont_recep_prog['MONTANT_RECEPTION']);
     
          $MONTANT_TRANSFERT_RESTE_PROG = (floatval($MONTANT_TRANSFERT_PROG) - floatval($MONTANT_RECEPTION_PROG));

          if($MONTANT_TRANSFERT_RESTE_PROG >= 0)
          {
            $MONTANT_TRANSFERT_2=$MONTANT_TRANSFERT_RESTE_PROG;
          }else{
            $MONTANT_TRANSFERT_2=$MONTANT_TRANSFERT_RESTE_PROG*(-1);
          }

          $CREDIT_APRES_TRANSFERT_4=(floatval($MONTANT_CREDIT_VOTE_2) - floatval($MONTANT_TRANSFERT_PROG)) + floatval($MONTANT_RECEPTION_PROG);

          if($CREDIT_APRES_TRANSFERT_2 < 0){
            $CREDIT_APRES_TRANSFERT_2 = $CREDIT_APRES_TRANSFERT_2*(-1);
          }

          if($mont_transf_prog['PROGRAMME_ID']==$mont_recep_prog['PROGRAMME_ID'])
          {
            $MONTANT_TRANSFERT_2 = $MONTANT_TRANSFERT_PROG;
            $CREDIT_APRES_TRANSFERT_2 = floatval($MONTANT_CREDIT_VOTE_2);
          }

          $MONTANT_ENGAGE_2=!empty($infos_sup_2['MONTANT_ENGAGE']) ? $infos_sup_2['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_2=!empty($infos_sup_2['MONTANT_JURIDIQUE']) ? $infos_sup_2['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_2=!empty($infos_sup_2['MONTANT_LIQUIDATION']) ? $infos_sup_2['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_2=!empty($infos_sup_2['MONTANT_ORDONNANCEMENT']) ? $infos_sup_2['MONTANT_ORDONNANCEMENT'] : '0';
          $PAIEMENT_2=!empty($infos_sup_2['PAIEMENT']) ? $infos_sup_2['PAIEMENT'] : '0';
          $MONTANT_DECAISSEMENT_2=!empty($infos_sup_2['MONTANT_DECAISSEMENT'])?$infos_sup_2['MONTANT_DECAISSEMENT']:'0';
            $html.='<tr>';
            $html.='<td style="border: 1px solid #000; ">&nbsp;'.$key_progr->CODE_PROGRAMME.'  '.$key_progr->INTITULE_PROGRAMME.'</td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">   </td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_CREDIT_VOTE_2,'0',',',' ').'</td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT_2,'0',',',' ').'</td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_2,'0',',',' ').'</td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_2,0,","," ").'</td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_2,0,","," ").'</td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_2,0,","," ").'</td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_2,0,","," ").'</td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($PAIEMENT_2,0,","," ").'</td>';
            $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_2,0,","," ").'</td>';
            $html.='</tr>';

            $get_action='SELECT DISTINCT ptba.ACTION_ID, ptba.PROGRAMME_ID,CODE_ACTION,LIBELLE_ACTION FROM inst_institutions_actions act JOIN ptba_tache ptba ON ptba.ACTION_ID=act.ACTION_ID  WHERE  ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND  ptba.PROGRAMME_ID= '.$key_progr->PROGRAMME_ID.' '.$critere_action.' ORDER BY CODE_ACTION ASC';
            $get_action = "CALL `getTable`('" .$get_action."');";
            $action= $this->ModelPs->getRequete($get_action);

            if(!empty($action))
            {
              foreach ($action as $key_action) 
              {
                $params_infos_3='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' '.$critere_annee.''.$critere_date;
                $params_infos_3 = "CALL `getTable`('" .$params_infos_3."');";
                $infos_sup_3= $this->ModelPs->getRequeteOne($params_infos_3);

                $get_vote_act = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID." AND ptba.ACTION_ID=".$key_action->ACTION_ID."";
                $get_vote_act='CALL `getTable`("'.$get_vote_act.'")';
                $votes_act = $this->ModelPs->getRequeteOne($get_vote_act);
                $MONTANT_CREDIT_VOTE_3=!empty($votes_act['CREDIT_VOTE']) ? $votes_act['CREDIT_VOTE'] : '0';

                //Montant transferé
                $param_mont_trans_act = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID,'1');
                $param_mont_trans_act=str_replace('\"','"',$param_mont_trans_act);
                $mont_transf_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_act);
                $MONTANT_TRANSFERT_ACT=floatval($mont_transf_act['MONTANT_TRANSFERT']);

                //Montant receptionné
                $param_mont_recep_act = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID,'1');
                $param_mont_recep_act=str_replace('\"','"',$param_mont_recep_act);
                $mont_recep_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_act);
                $MONTANT_RECEPTION_ACT=floatval($mont_recep_act['MONTANT_RECEPTION']);
           
                $MONTANT_TRANSFERT_RESTE_ACT = (floatval($MONTANT_TRANSFERT_ACT) - floatval($MONTANT_RECEPTION_ACT));

                if($MONTANT_TRANSFERT_RESTE_ACT >= 0)
                {
                  $MONTANT_TRANSFERT_3=$MONTANT_TRANSFERT_RESTE_ACT;
                }else{
                  $MONTANT_TRANSFERT_3=$MONTANT_TRANSFERT_RESTE_ACT*(-1);
                }

                $CREDIT_APRES_TRANSFERT_3=(floatval($MONTANT_CREDIT_VOTE_3) - floatval($MONTANT_TRANSFERT_ACT)) + floatval($MONTANT_RECEPTION_ACT);

                if($CREDIT_APRES_TRANSFERT_3 < 0){
                  $CREDIT_APRES_TRANSFERT_3 = $CREDIT_APRES_TRANSFERT_3*(-1);
                }

                if($mont_transf_act['ACTION_ID']==$mont_recep_act['ACTION_ID'])
                {
                  $MONTANT_TRANSFERT_3 = $MONTANT_TRANSFERT_ACT;
                  $CREDIT_APRES_TRANSFERT_3 = floatval($MONTANT_CREDIT_VOTE_3);
                }

                $MONTANT_ENGAGE_3=!empty($infos_sup_3['MONTANT_ENGAGE']) ? $infos_sup_3['MONTANT_ENGAGE'] : '0';
                $MONTANT_JURIDIQUE_3=!empty($infos_sup_3['MONTANT_JURIDIQUE']) ? $infos_sup_3['MONTANT_JURIDIQUE'] : '0';
                $MONTANT_LIQUIDATION_3=!empty($infos_sup_3['MONTANT_LIQUIDATION']) ? $infos_sup_3['MONTANT_LIQUIDATION'] : '0';
                $MONTANT_ORDONNANCEMENT_3=!empty($infos_sup_3['MONTANT_ORDONNANCEMENT']) ? $infos_sup_3['MONTANT_ORDONNANCEMENT'] : '0';
                $PAIEMENT_3=!empty($infos_sup_3['PAIEMENT']) ? $infos_sup_3['PAIEMENT'] : '0';
                $MONTANT_DECAISSEMENT_3=!empty($infos_sup_3['MONTANT_DECAISSEMENT'])?$infos_sup_3['MONTANT_DECAISSEMENT']:'0';

                $html.='<tr>';
                $html.='<td style="border: 1px solid #000; ">&nbsp;&nbsp;&nbsp;'.$key_action->CODE_ACTION.'  '.$key_action->LIBELLE_ACTION.'</td>';
                $html .= '<td style="border: 1px solid #000;font-size:10px"></td>';
                $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_CREDIT_VOTE_3,'0',',',' ').'</td>';
                $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT_3,'0',',',' ').'</td>';
                $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_3,'0',',',' ').'</td>';
                $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_3,0,","," ").'</td>';
                $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_3,0,","," ").'</td>';
                $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_3,0,","," ").'</td>';
                $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_3,0,","," ").'</td>';
                $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($PAIEMENT_3,0,","," ").'</td>';
                $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_3,0,","," ").'</td>';
                $html.='</tr>';

                $get_imputation='SELECT DISTINCT ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE FROM   inst_institutions_ligne_budgetaire ligne JOIN ptba_tache ptba ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE ASC';

                $get_imputation = "CALL `getTable`('" .$get_imputation."');";
                $imputations= $this->ModelPs->getRequete($get_imputation);

                if(!empty($imputations))
                {
                  foreach ($imputations as $key_imput) 
                  {
                    $params_infos_4='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID.' '.$critere_annee.''.$critere_date;

                    $params_infos_4 = "CALL `getTable`('" .$params_infos_4."');";
                    $infos_sup_4= $this->ModelPs->getRequeteOne($params_infos_4);

                    $get_vote = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID." AND ptba.ACTION_ID =".$key_action->ACTION_ID." AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID."";
                    $get_vote='CALL `getTable`("'.$get_vote.'")';
                    $votes = $this->ModelPs->getRequeteOne($get_vote);
                    $MONTANT_CREDIT_VOTE_4=!empty($votes['CREDIT_VOTE']) ? $votes['CREDIT_VOTE'] : '0';
                    
                    //Montant transferé
                    $param_mont_trans_imp = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID,'1');
                    $param_mont_trans_imp=str_replace('\"','"',$param_mont_trans_imp);

                    $mont_transf_imp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_imp);
                    $MONTANT_TRANSFERT_IMP=floatval($mont_transf_imp['MONTANT_TRANSFERT']);

                    //Montant receptionné
                    $param_mont_recep_imp = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID,'1');
                    $param_mont_recep_imp=str_replace('\"','"',$param_mont_recep_imp);
                    $mont_recep_imp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_imp);
                    $MONTANT_RECEPTION_IMP=floatval($mont_recep_imp['MONTANT_RECEPTION']);
               
                    $MONTANT_TRANSFERT_RESTE_IMP = (floatval($MONTANT_TRANSFERT_IMP) - floatval($MONTANT_RECEPTION_IMP));

                    if($MONTANT_TRANSFERT_RESTE_IMP >= 0)
                    {
                      $MONTANT_TRANSFERT_4=$MONTANT_TRANSFERT_RESTE_IMP;
                    }else{
                      $MONTANT_TRANSFERT_4=$MONTANT_TRANSFERT_RESTE_IMP*(-1);
                    }

                    $CREDIT_APRES_TRANSFERT_4=(floatval($MONTANT_CREDIT_VOTE_4) - floatval($MONTANT_TRANSFERT_IMP)) + floatval($MONTANT_RECEPTION_IMP);

                    if($CREDIT_APRES_TRANSFERT_4 < 0){
                      $CREDIT_APRES_TRANSFERT_4 = $CREDIT_APRES_TRANSFERT_4*(-1);
                    }

                    if($mont_transf_imp['CODE_NOMENCLATURE_BUDGETAIRE_ID']==$mont_recep_imp['CODE_NOMENCLATURE_BUDGETAIRE_ID'])
                    {
                      $MONTANT_TRANSFERT_4 = $MONTANT_TRANSFERT_IMP;
                      $CREDIT_APRES_TRANSFERT_4 = floatval($MONTANT_CREDIT_VOTE_4);
                    }

                    $MONTANT_ENGAGE_4=!empty($infos_sup_4['MONTANT_ENGAGE']) ? $infos_sup_4['MONTANT_ENGAGE'] : '0';
                    $MONTANT_JURIDIQUE_4=!empty($infos_sup_4['MONTANT_JURIDIQUE']) ? $infos_sup_4['MONTANT_JURIDIQUE'] : '0';
                    $MONTANT_LIQUIDATION_4=!empty($infos_sup_4['MONTANT_LIQUIDATION']) ? $infos_sup_4['MONTANT_LIQUIDATION'] : '0';
                    $MONTANT_ORDONNANCEMENT_4=!empty($infos_sup_4['MONTANT_ORDONNANCEMENT']) ? $infos_sup_4['MONTANT_ORDONNANCEMENT'] : '0';
                    $PAIEMENT_4=!empty($infos_sup_4['PAIEMENT']) ? $infos_sup_4['PAIEMENT'] : '0';
                    $MONTANT_DECAISSEMENT_4=!empty($infos_sup_4['MONTANT_DECAISSEMENT'])?$infos_sup_4['MONTANT_DECAISSEMENT']:'0';

                   $html.='<tr>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">&nbsp;&nbsp;&nbsp;'.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE.'</td>';
                   $html .= '<td style="border: 1px solid #000;font-size:10px"></td>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_CREDIT_VOTE_4,'0',',',' ').'</td>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT_4,'0',',',' ').'</td>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_4,'0',',',' ').'</td>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_4,0,","," ").'</td>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_4,0,","," ").'</td>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_4,0,","," ").'</td>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_4,0,","," ").'</td>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($PAIEMENT_4,0,","," ").'</td>';
                   $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_4,0,","," ").'</td>';
                   $html.='</tr>';

                   if ($activ==1)
                   {
                     $get_tache='SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE INSTITUTION_ID='.$key->INSTITUTION_ID.' AND PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ACTION_ID ='.$key_action->ACTION_ID.' AND CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ';

                    $get_tache = "CALL `getTable`('" .$get_tache."');";
                    $taches= $this->ModelPs->getRequete($get_tache);

                    if(!empty($taches))
                    {
                    
                      foreach($taches as $key_tache)
                      {
                        $params_infos_5='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.PAIEMENT) AS PAIEMENT,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba.PTBA_TACHE_ID='.$key_tache->PTBA_TACHE_ID.''.$critere_annee.' '.$critere_date_act.'';

                        $params_infos_5= "CALL `getTable`('" .$params_infos_5."');";
                        $infos_sup_5= $this->ModelPs->getRequeteOne($params_infos_5);

                        $get_vote_task = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID." AND ptba.ACTION_ID =".$key_action->ACTION_ID." AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID =".$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID." AND ptba.PTBA_TACHE_ID=".$key_tache->PTBA_TACHE_ID;
                        $get_vote_task='CALL `getTable`("'.$get_vote_task.'")';
                        $votes_task = $this->ModelPs->getRequeteOne($get_vote_task);
                        $MONTANT_CREDIT_VOTE_5=!empty($votes_task['CREDIT_VOTE']) ? $votes_task['CREDIT_VOTE'] : '0';

                        //Montant transferé
                        $param_mont_trans_task = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$key_tache->PTBA_TACHE_ID,'1');
                        $param_mont_trans_task=str_replace('\"','"',$param_mont_trans_task);

                        $mont_transf_task=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_task);
                        $MONTANT_TRANSFERT_TASK=floatval($mont_transf_task['MONTANT_TRANSFERT']);

                        //Montant receptionné
                        $param_mont_recep_task = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$key_tache->PTBA_TACHE_ID,'1');
                        $param_mont_recep_task=str_replace('\"','"',$param_mont_recep_task);
                        $mont_recep_task=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_task);
                        $MONTANT_RECEPTION_TASK=floatval($mont_recep_task['MONTANT_RECEPTION']);
                   
                        $MONTANT_TRANSFERT_RESTE_TASK = (floatval($MONTANT_TRANSFERT_TASK) - floatval($MONTANT_RECEPTION_TASK));

                        if($MONTANT_TRANSFERT_RESTE_TASK >= 0)
                        {
                          $MONTANT_TRANSFERT_5=$MONTANT_TRANSFERT_RESTE_TASK;
                        }else{
                          $MONTANT_TRANSFERT_5=$MONTANT_TRANSFERT_RESTE_TASK*(-1);
                        }

                        $CREDIT_APRES_TRANSFERT_5=(floatval($MONTANT_CREDIT_VOTE_5) - floatval($MONTANT_TRANSFERT_TASK)) + floatval($MONTANT_RECEPTION_TASK);

                        if($CREDIT_APRES_TRANSFERT_5 < 0){
                          $CREDIT_APRES_TRANSFERT_5 = $CREDIT_APRES_TRANSFERT_5*(-1);
                        }

                        if($mont_transf_task['PTBA_TACHE_ID']==$mont_recep_task['PTBA_TACHE_ID'])
                        {
                          $MONTANT_TRANSFERT_5 = $MONTANT_TRANSFERT_TASK;
                          $CREDIT_APRES_TRANSFERT_5 = floatval($MONTANT_CREDIT_VOTE_5);
                        }

                        $MONTANT_ENGAGE_5=!empty($infos_sup_5['MONTANT_ENGAGE']) ? $infos_sup_5['MONTANT_ENGAGE'] : '0';
                        $MONTANT_JURIDIQUE_5=!empty($infos_sup_5['MONTANT_JURIDIQUE']) ? $infos_sup_5['MONTANT_JURIDIQUE'] : '0';
                        $MONTANT_LIQUIDATION_5=!empty($infos_sup_5['MONTANT_LIQUIDATION']) ? $infos_sup_5['MONTANT_LIQUIDATION'] : '0';
                        $MONTANT_ORDONNANCEMENT_5=!empty($infos_sup_5['MONTANT_ORDONNANCEMENT']) ? $infos_sup_5['MONTANT_ORDONNANCEMENT'] : '0';
                        $PAIEMENT_5=!empty($infos_sup_5['PAIEMENT']) ? $infos_sup_5['PAIEMENT'] : '0';
                        $MONTANT_DECAISSEMENT_5=!empty($infos_sup_5['MONTANT_DECAISSEMENT'])?$infos_sup_5['MONTANT_DECAISSEMENT']:'0';
                        $DESC_TACHE = mb_convert_encoding($key_tache->DESC_TACHE, 'UTF-8', 'UTF-8');

                        $html.='<tr>';
                        $html .= '<td style="border: 1px solid #000;font-size:10px"></td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.utf8_encode($DESC_TACHE).'</td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_CREDIT_VOTE_5,'0',',',' ').'</td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_TRANSFERT_5,'0',',',' ').'</td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($CREDIT_APRES_TRANSFERT_5,'0',',',' ').'</td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ENGAGE_5,0,","," ").'</td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_JURIDIQUE_5,0,","," ").'</td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_LIQUIDATION_5,0,","," ").'</td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_ORDONNANCEMENT_5,0,","," ").'</td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($PAIEMENT_5,0,","," ").'</td>';
                        $html.='<td style="border: 1px solid #000;font-size:10px ">'.number_format($MONTANT_DECAISSEMENT_5,0,","," ").'</td>';
                        $html.='</tr>';
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    $html.='</table>';
    $html.= "</body>";    
    $html.='</html>';

    // print($html);
    // die();
    
    // Charger le contenu HTML
    $dompdf->loadHtml($html);
    // Définir la taille et l'orientation du papier
    $dompdf->setPaper('A4', 'landscape');

    // Rendre le HTML en PDF
    $dompdf->render();
    $name_file = 'Classification_administrative'.uniqid().'.pdf';
    // Envoyer le fichier PDF en tant que téléchargement
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Classification_administrative.pdf"');
    echo $dompdf->output();
  }  

  //Fonction pour exporter des donnees en word
  public function export_word($INSTITUTION_ID='',$SOUS_TUTEL_ID='',$PROGRAMME_ID='',$ACTION_ID='',$ANNEE_BUDGETAIRE_ID='',$DATE_DEBUT='',$DATE_FIN='',$activ='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_CLASSIFICATION_ADMINISTRATIVE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $titre_document="Titres 01 au 76";
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $criteres='';
    $critere_progr='';
    $critere_action='';
        // $criteresou='';
    $critere_date="";
    $critere_date_act='';
    $ann_eco=$this->get_annee_budgetaire();
        //filtre pour anne budg
    $critere_annee=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    $critere_anne_ptba=" AND ptba.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
  
    //filtre pour date debut et date fin
         
    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date=' AND DATE_DEMANDE >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date=' AND DATE_DEMANDE <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date=' AND DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date_act=' AND DATE_BON_ENGAGEMENT >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date_act=' AND DATE_BON_ENGAGEMENT <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date_act=' AND DATE_BON_ENGAGEMENT BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }


    if($INSTITUTION_ID!=0)
    {
      $criteres.=" AND ptba.INSTITUTION_ID=".$INSTITUTION_ID."";
      $get_code = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, 'CODE_INSTITUTION ASC');
      $institutionscode = $this->ModelPs->getRequeteOne($callpsreq, $get_code);
      $titre_document="Titre ".$institutionscode['CODE_INSTITUTION'];
    }

    if($PROGRAMME_ID!=0)
    {
      $critere_progr.=" AND ptba.PROGRAMME_ID=".$PROGRAMME_ID;
    }

    if($ACTION_ID!=0)
    {
      $critere_action.=" AND ptba.ACTION_ID=".$ACTION_ID;
    }

    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
    $annee_budgetaire = $this->ModelPs->getRequeteOne($callpsreq, $get_ann_budg);
    $annee_dexcr=str_replace('-','/',$annee_budgetaire['ANNEE_DESCRIPTION']);

    $p_deb = '01/07/'.substr($annee_dexcr, 0, 4);

    $periode_debut = (!empty($DATE_DEBUT)) ? date('d/m/Y',strtotime($DATE_DEBUT)) : $p_deb ;
    $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : date("d/m/Y");

    if($ann_eco != $ANNEE_BUDGETAIRE_ID)
    {
      $p_fin = '30/06/'.substr($annee_dexcr,5);
      $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;
    }

    $get_institutions = $this->getBindParms('DISTINCT ptba.INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions inst JOIN ptba_tache ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID', '1'.$criteres.''.$critere_anne_ptba.'', 'CODE_INSTITUTION ASC');
    $institutions = $this->ModelPs->getRequete($callpsreq, $get_institutions);

    $phpWord = new PhpWord();

    // Définir la section en mode paysage
    $sectionStyle = array(
      'orientation' => 'landscape',
      'marginTop' => 300,   
          'marginRight' => 350, // Marge droite en twips
          'marginLeft' => 350, // Marge gauche en twips
          'colsNum' => 1,
        );
    $section = $phpWord->addSection($sectionStyle);

    // creation du tableau avec bordure
    $tableStyle = [
      'borderSize' => 6,
    ];    
    $phpWord->addTableStyle('myTable', $tableStyle);
    //titre du document word
    $section->addText('CIRCUIT DES DEPENSES', ['bold' => true, 'size'=> 16], ['align' => 'center']);
    $section->addText('Classification Administrative', ['bold' => false, 'size'=> 14], ['align' => 'center']);
    $section->addText('EXERCICE '.$annee_dexcr.', N° BUDGET 0                     Période du '.$periode_debut.' au '.$periode_fin.'', ['bold' => false, 'size'=> 12], ['align' => 'center']);
    $section->addText($titre_document, ['bold' => false, 'size'=> 11], ['align' => 'left']);
    $section->addText('Source de Financement 11', ['bold' => false, 'size'=> 11], ['align' => 'left']);
    $table = $section->addTable('myTable');
    $table->addRow();

    $table->addCell(2500)->addText('IMPUTATION', ['bold' => true],  ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(1800)->addText('LIBELLE DE L\'ACTIVITE', ['bold' => true],  ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(1800)->addText('CREDIT VOTE', ['bold' => true],  ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(1800)->addText('TRANSFERTS CREDITS', ['bold' => true],  ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(1800)->addText('CREDIT APRES TRANSFERT', ['bold' => true],  ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(2500)->addText('ENG BUDGETAIRE', ['bold' => true], ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(2500)->addText('ENG JURIDIQUE', ['bold' => true], ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(2500)->addText('LIQUIDATION', ['bold' => true], ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(2500)->addText('ORDONNANCEMENT', ['bold' => true], ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(2500)->addText('PAIEMENT', ['bold' => true], ['border'=>2], ['bordersize'=>6],['size'=> 11]);
    $table->addCell(2500)->addText('DECAISSEMENT', ['bold' => true], ['border'=>2], ['bordersize'=>6],['size'=> 11]);

    foreach ($institutions as $key)
    {
      $params_infos='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' '.$critere_annee.''.$critere_date.'';
      $params_infos = "CALL `getTable`('" .$params_infos."');";
      $infos_sup= $this->ModelPs->getRequeteOne($params_infos);

      $get_vote_inst = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID."";
      $get_vote_inst='CALL `getTable`("'.$get_vote_inst.'")';
      $votes_inst = $this->ModelPs->getRequeteOne($get_vote_inst);
      $MONTANT_CREDIT_VOTE_1=(!empty($votes_inst['CREDIT_VOTE'])) ? $votes_task['CREDIT_VOTE'] : 0;

      //Montant transferé
      $param_mont_trans_inst = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_trans_inst=str_replace('\"','"',$param_mont_trans_inst);

      $mont_transf_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_inst);
      $MONTANT_TRANSFERT_INST=floatval($mont_transf_inst['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep_inst = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.INSTITUTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID,'1');
      $param_mont_recep_inst=str_replace('\"','"',$param_mont_recep_inst);
      $mont_recep_inst=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_inst);
      $MONTANT_RECEPTION_INST=floatval($mont_recep_inst['MONTANT_RECEPTION']);
 
      $MONTANT_TRANSFERT_RESTE_INST = (floatval($MONTANT_TRANSFERT_INST) - floatval($MONTANT_RECEPTION_INST));

      if($MONTANT_TRANSFERT_RESTE_INST >= 0)
      {
        $MONTANT_TRANSFERT_1=$MONTANT_TRANSFERT_RESTE_INST;
      }else{
        $MONTANT_TRANSFERT_1=$MONTANT_TRANSFERT_RESTE_INST*(-1);
      }

      $CREDIT_APRES_TRANSFERT_1=(floatval($MONTANT_CREDIT_VOTE_1) - floatval($MONTANT_TRANSFERT_INST)) + floatval($MONTANT_RECEPTION_INST);

      if($CREDIT_APRES_TRANSFERT_1 < 0){
        $CREDIT_APRES_TRANSFERT_1 = $CREDIT_APRES_TRANSFERT_1*(-1);
      }

      if($mont_transf_inst['INSTITUTION_ID']==$mont_recep_inst['INSTITUTION_ID'])
      {
        $MONTANT_TRANSFERT_1 = $MONTANT_TRANSFERT_INST;
        $CREDIT_APRES_TRANSFERT_1 = floatval($MONTANT_CREDIT_VOTE_1);
      }

      $MONTANT_ENGAGE_1=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE_1=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION_1=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT_1=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT_1=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $MONTANT_DECAISSEMENT_1=!empty($infos_sup['MONTANT_DECAISSEMENT'])?$infos_sup['MONTANT_DECAISSEMENT']:'0';

      $DESCRIPTION_INSTITUTION = str_replace("\n","",$key->DESCRIPTION_INSTITUTION);
      $DESCRIPTION_INSTITUTION = str_replace("\r","",$DESCRIPTION_INSTITUTION);
      $DESCRIPTION_INSTITUTION = str_replace("\t","",$DESCRIPTION_INSTITUTION);
      $DESCRIPTION_INSTITUTION = str_replace('"','',$DESCRIPTION_INSTITUTION);
      $DESCRIPTION_INSTITUTION = str_replace("'","\'",$DESCRIPTION_INSTITUTION);

      $table->addRow();
      $table->addCell(3000,['cellMargin' => 20])->addText(' '.$key->CODE_INSTITUTION.'  '.$DESCRIPTION_INSTITUTION, null, ['spaceBefore' => 10],['size'=> 11]);
      $table->addCell(3000)->addText(' ',['size'=> 11]);
      $table->addCell(3000)->addText(number_format($MONTANT_CREDIT_VOTE_1,'0',',',' '),['size'=> 11]);
      $table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT_1,'0',',',' '),['size'=> 11]);
      $table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_1,'0',',',' '),['size'=> 11]);
      $table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_1,0,","," "),['size'=> 11]);
      $table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_1,0,","," "),['size'=> 11]);
      $table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_1,0,","," "),['size'=> 11]);
      $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_1,0,","," "),['size'=> 11]);
      $table->addCell(3000)->addText(number_format($PAIEMENT_1,0,","," "),['size'=> 11]);
      $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_1,0,","," "),['size'=> 11]);

      $get_program = ' SELECT DISTINCT ptba.PROGRAMME_ID,prog.INSTITUTION_ID,CODE_PROGRAMME,INTITULE_PROGRAMME FROM inst_institutions_programmes prog JOIN ptba_tache ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' '.$critere_progr.' ORDER BY CODE_PROGRAMME ASC';
      $get_program = "CALL `getTable`('" .$get_program."');";
      $programs= $this->ModelPs->getRequete($get_program);
      if(!empty($programs))
      {
        foreach($programs as $key_progr)
        {
          $params_infos_2='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' '.$critere_annee.''.$critere_date;

          $params_infos_2 = "CALL `getTable`('" .$params_infos_2."');";
          $infos_sup_2= $this->ModelPs->getRequeteOne($params_infos_2);

          $get_vote_prog = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID."";
          $get_vote_prog='CALL `getTable`("'.$get_vote_prog.'")';
          $votes_prog = $this->ModelPs->getRequeteOne($get_vote_prog);
          $MONTANT_CREDIT_VOTE_2=!empty($votes_prog['CREDIT_VOTE']) ? $votes_prog['CREDIT_VOTE'] : '0';

          //Montant transferé
          $param_mont_trans_prog = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID,'1');
          $param_mont_trans_prog=str_replace('\"','"',$param_mont_trans_prog);

          $mont_transf_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_prog);
          $MONTANT_TRANSFERT_PROG=floatval($mont_transf_prog['MONTANT_TRANSFERT']);

          //Montant receptionné
          $param_mont_recep_prog = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID,'1');
          $param_mont_recep_prog=str_replace('\"','"',$param_mont_recep_prog);
          $mont_recep_prog=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_prog);
          $MONTANT_RECEPTION_PROG=floatval($mont_recep_prog['MONTANT_RECEPTION']);
     
          $MONTANT_TRANSFERT_RESTE_PROG = (floatval($MONTANT_TRANSFERT_PROG) - floatval($MONTANT_RECEPTION_PROG));

          if($MONTANT_TRANSFERT_RESTE_PROG >= 0)
          {
            $MONTANT_TRANSFERT_2=$MONTANT_TRANSFERT_RESTE_PROG;
          }else{
            $MONTANT_TRANSFERT_2=$MONTANT_TRANSFERT_RESTE_PROG*(-1);
          }

          $CREDIT_APRES_TRANSFERT_4=(floatval($MONTANT_CREDIT_VOTE_2) - floatval($MONTANT_TRANSFERT_PROG)) + floatval($MONTANT_RECEPTION_PROG);

          if($CREDIT_APRES_TRANSFERT_2 < 0){
            $CREDIT_APRES_TRANSFERT_2 = $CREDIT_APRES_TRANSFERT_2*(-1);
          }

          if($mont_transf_prog['PROGRAMME_ID']==$mont_recep_prog['PROGRAMME_ID'])
          {
            $MONTANT_TRANSFERT_2 = $MONTANT_TRANSFERT_PROG;
            $CREDIT_APRES_TRANSFERT_2 = floatval($MONTANT_CREDIT_VOTE_2);
          }

          $MONTANT_ENGAGE_2=!empty($infos_sup_2['MONTANT_ENGAGE']) ? $infos_sup_2['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_2=!empty($infos_sup_2['MONTANT_JURIDIQUE']) ? $infos_sup_2['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_2=!empty($infos_sup_2['MONTANT_LIQUIDATION']) ? $infos_sup_2['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_2=!empty($infos_sup_2['MONTANT_ORDONNANCEMENT']) ? $infos_sup_2['MONTANT_ORDONNANCEMENT'] : '0';
          $PAIEMENT_2=!empty($infos_sup_2['PAIEMENT']) ? $infos_sup_2['PAIEMENT'] : '0';
          $MONTANT_DECAISSEMENT_2=!empty($infos_sup_2['MONTANT_DECAISSEMENT'])?$infos_sup_2['MONTANT_DECAISSEMENT']:'0';

            $INTITULE_PROGRAMME = str_replace("\n","",$key_progr->INTITULE_PROGRAMME);
            $INTITULE_PROGRAMME = str_replace("\r","",$INTITULE_PROGRAMME);
            $INTITULE_PROGRAMME = str_replace("\t","",$INTITULE_PROGRAMME);
            $INTITULE_PROGRAMME = str_replace('"','',$INTITULE_PROGRAMME);
            $INTITULE_PROGRAMME = str_replace("'","\'",$INTITULE_PROGRAMME);

            $table->addRow();
            $table->addCell(3000,['cellMargin' => 40])->addText('  '.$key_progr->CODE_PROGRAMME.'  '.$INTITULE_PROGRAMME, null, ['spaceBefore' => 10],['size'=> 11]); 
            $table->addCell(3000)->addText(' ',['size'=> 11]);
            $table->addCell(3000)->addText(number_format($MONTANT_CREDIT_VOTE_2,'0',',',' '),['size'=> 11]);
            $table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT_2,'0',',',' '),['size'=> 11]);
            $table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_2,'0',',',' '),['size'=> 11]);
            $table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_2,0,","," "),['size'=> 11]);
            $table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_2,0,","," "),['size'=> 11]);
            $table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_2,0,","," "),['size'=> 11]);
            $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_2,0,","," "),['size'=> 11]);
            $table->addCell(3000)->addText(number_format($PAIEMENT_2,0,","," "),['size'=> 11]);
            $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_2,0,","," "),['size'=> 11]);

            $get_action='SELECT DISTINCT ptba.ACTION_ID, ptba.PROGRAMME_ID,CODE_ACTION,LIBELLE_ACTION FROM inst_institutions_actions act JOIN ptba_tache ptba ON ptba.ACTION_ID=act.ACTION_ID  WHERE  ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND  ptba.PROGRAMME_ID= '.$key_progr->PROGRAMME_ID.' '.$critere_action.' ORDER BY CODE_ACTION ASC';
            $get_action = "CALL `getTable`('" .$get_action."');";
            $action= $this->ModelPs->getRequete($get_action);

            if(!empty($action)) 
            {
              foreach($action as $key_action) 
              {
                $params_infos_3='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' '.$critere_annee.''.$critere_date;
                $params_infos_3 = "CALL `getTable`('" .$params_infos_3."');";
                $infos_sup_3= $this->ModelPs->getRequeteOne($params_infos_3);

                $get_vote_act = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID." AND ptba.ACTION_ID=".$key_action->ACTION_ID."";
                $get_vote_act='CALL `getTable`("'.$get_vote_act.'")';
                $votes_act = $this->ModelPs->getRequeteOne($get_vote_act);
                $MONTANT_CREDIT_VOTE_3=!empty($votes_act['CREDIT_VOTE']) ? $votes_act['CREDIT_VOTE'] : '0';

                //Montant transferé
                $param_mont_trans_act = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID,'1');
                $param_mont_trans_act=str_replace('\"','"',$param_mont_trans_act);
                $mont_transf_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_act);
                $MONTANT_TRANSFERT_ACT=floatval($mont_transf_act['MONTANT_TRANSFERT']);

                //Montant receptionné
                $param_mont_recep_act = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID,'1');
                $param_mont_recep_act=str_replace('\"','"',$param_mont_recep_act);
                $mont_recep_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_act);
                $MONTANT_RECEPTION_ACT=floatval($mont_recep_act['MONTANT_RECEPTION']);
           
                $MONTANT_TRANSFERT_RESTE_ACT = (floatval($MONTANT_TRANSFERT_ACT) - floatval($MONTANT_RECEPTION_ACT));

                if($MONTANT_TRANSFERT_RESTE_ACT >= 0)
                {
                  $MONTANT_TRANSFERT_3=$MONTANT_TRANSFERT_RESTE_ACT;
                }else{
                  $MONTANT_TRANSFERT_3=$MONTANT_TRANSFERT_RESTE_ACT*(-1);
                }

                $CREDIT_APRES_TRANSFERT_3=(floatval($MONTANT_CREDIT_VOTE_3) - floatval($MONTANT_TRANSFERT_ACT)) + floatval($MONTANT_RECEPTION_ACT);

                if($CREDIT_APRES_TRANSFERT_3 < 0){
                  $CREDIT_APRES_TRANSFERT_3 = $CREDIT_APRES_TRANSFERT_3*(-1);
                }

                if($mont_transf_act['ACTION_ID']==$mont_recep_act['ACTION_ID'])
                {
                  $MONTANT_TRANSFERT_3 = $MONTANT_TRANSFERT_ACT;
                  $CREDIT_APRES_TRANSFERT_3 = floatval($MONTANT_CREDIT_VOTE_3);
                }

                $MONTANT_ENGAGE_3=!empty($infos_sup_3['MONTANT_ENGAGE']) ? $infos_sup_3['MONTANT_ENGAGE'] : '0';
                $MONTANT_JURIDIQUE_3=!empty($infos_sup_3['MONTANT_JURIDIQUE']) ? $infos_sup_3['MONTANT_JURIDIQUE'] : '0';
                $MONTANT_LIQUIDATION_3=!empty($infos_sup_3['MONTANT_LIQUIDATION']) ? $infos_sup_3['MONTANT_LIQUIDATION'] : '0';
                $MONTANT_ORDONNANCEMENT_3=!empty($infos_sup_3['MONTANT_ORDONNANCEMENT']) ? $infos_sup_3['MONTANT_ORDONNANCEMENT'] : '0';
                $PAIEMENT_3=!empty($infos_sup_3['PAIEMENT']) ? $infos_sup_3['PAIEMENT'] : '0';
                $MONTANT_DECAISSEMENT_3=!empty($infos_sup_3['MONTANT_DECAISSEMENT'])?$infos_sup_3['MONTANT_DECAISSEMENT']:'0';

                $LIBELLE_ACTION = str_replace("\n","",$key_action->LIBELLE_ACTION);
                $LIBELLE_ACTION = str_replace("\r","",$LIBELLE_ACTION);
                $LIBELLE_ACTION = str_replace("\t","",$LIBELLE_ACTION);
                $LIBELLE_ACTION = str_replace('"','',$LIBELLE_ACTION);
                $LIBELLE_ACTION = str_replace("'","\'",$LIBELLE_ACTION);

                $table->addRow();
                $table->addCell(1500,['cellMargin' => 60])->addText('   '.$key_action->CODE_ACTION.'  '.$LIBELLE_ACTION, null, ['spaceBefore' => 10],['size'=> 11]); 
                $table->addCell(3000)->addText(' ',['size'=> 11]);
                $table->addCell(3000)->addText(number_format($MONTANT_CREDIT_VOTE_3,'0',',',' '),['size'=> 11]);
                $table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT_3,'0',',',' '),['size'=> 11]);
                $table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_3,'0',',',' '),['size'=> 11]);
                $table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_3,0,","," "),['size'=> 11]);
                $table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_3,0,","," "),['size'=> 11]);
                $table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_3,0,","," "),['size'=> 11]);
                $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_3,0,","," "),['size'=> 11]);
                $table->addCell(3000)->addText(number_format($PAIEMENT_3,0,","," "),['size'=> 11]);
                $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_3,0,","," "),['size'=> 11]);

                $get_imputation='SELECT DISTINCT ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE FROM   inst_institutions_ligne_budgetaire ligne JOIN ptba_tache ptba ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE ASC';

                $get_imputation = "CALL `getTable`('" .$get_imputation."');";
                $imputations= $this->ModelPs->getRequete($get_imputation);

                if(!empty($imputations)) 
                {
                  foreach ($imputations as $key_imput) 
                  {
                    $params_infos_4='SELECT SUM(ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT ,SUM(PAIEMENT) AS PAIEMENT ,SUM(DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID.' '.$critere_annee.''.$critere_date;

                    $params_infos_4 = "CALL `getTable`('" .$params_infos_4."');";
                    $infos_sup_4= $this->ModelPs->getRequeteOne($params_infos_4);

                    $get_vote = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID." AND ptba.ACTION_ID =".$key_action->ACTION_ID." AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID."";
                    $get_vote='CALL `getTable`("'.$get_vote.'")';
                    $votes = $this->ModelPs->getRequeteOne($get_vote);
                    $MONTANT_CREDIT_VOTE_4=!empty($votes['CREDIT_VOTE']) ? $votes['CREDIT_VOTE'] : '0';
                    
                    //Montant transferé
                    $param_mont_trans_imp = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID,'1');
                    $param_mont_trans_imp=str_replace('\"','"',$param_mont_trans_imp);

                    $mont_transf_imp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_imp);
                    $MONTANT_TRANSFERT_IMP=floatval($mont_transf_imp['MONTANT_TRANSFERT']);

                    //Montant receptionné
                    $param_mont_recep_imp = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID,'1');
                    $param_mont_recep_imp=str_replace('\"','"',$param_mont_recep_imp);
                    $mont_recep_imp=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_imp);
                    $MONTANT_RECEPTION_IMP=floatval($mont_recep_imp['MONTANT_RECEPTION']);
               
                    $MONTANT_TRANSFERT_RESTE_IMP = (floatval($MONTANT_TRANSFERT_IMP) - floatval($MONTANT_RECEPTION_IMP));

                    if($MONTANT_TRANSFERT_RESTE_IMP >= 0)
                    {
                      $MONTANT_TRANSFERT_4=$MONTANT_TRANSFERT_RESTE_IMP;
                    }else{
                      $MONTANT_TRANSFERT_4=$MONTANT_TRANSFERT_RESTE_IMP*(-1);
                    }

                    $CREDIT_APRES_TRANSFERT_4=(floatval($MONTANT_CREDIT_VOTE_4) - floatval($MONTANT_TRANSFERT_IMP)) + floatval($MONTANT_RECEPTION_IMP);

                    if($CREDIT_APRES_TRANSFERT_4 < 0){
                      $CREDIT_APRES_TRANSFERT_4 = $CREDIT_APRES_TRANSFERT_4*(-1);
                    }

                    if($mont_transf_imp['CODE_NOMENCLATURE_BUDGETAIRE_ID']==$mont_recep_imp['CODE_NOMENCLATURE_BUDGETAIRE_ID'])
                    {
                      $MONTANT_TRANSFERT_4 = $MONTANT_TRANSFERT_IMP;
                      $CREDIT_APRES_TRANSFERT_4 = floatval($MONTANT_CREDIT_VOTE_4);
                    }

                    $MONTANT_ENGAGE_4=!empty($infos_sup_4['MONTANT_ENGAGE']) ? $infos_sup_4['MONTANT_ENGAGE'] : '0';
                    $MONTANT_JURIDIQUE_4=!empty($infos_sup_4['MONTANT_JURIDIQUE']) ? $infos_sup_4['MONTANT_JURIDIQUE'] : '0';
                    $MONTANT_LIQUIDATION_4=!empty($infos_sup_4['MONTANT_LIQUIDATION']) ? $infos_sup_4['MONTANT_LIQUIDATION'] : '0';
                    $MONTANT_ORDONNANCEMENT_4=!empty($infos_sup_4['MONTANT_ORDONNANCEMENT']) ? $infos_sup_4['MONTANT_ORDONNANCEMENT'] : '0';
                    $PAIEMENT_4=!empty($infos_sup_4['PAIEMENT']) ? $infos_sup_4['PAIEMENT'] : '0';
                    $MONTANT_DECAISSEMENT_4=!empty($infos_sup_4['MONTANT_DECAISSEMENT'])?$infos_sup_4['MONTANT_DECAISSEMENT']:'0';

                   $table->addRow();
                   $table->addCell(1500,['cellMargin' => 60])->addText('     '.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE, null, ['spaceBefore' => 10],['size'=> 11]);
                   $table->addCell(3000)->addText(' ',['size'=> 11]);
                   $table->addCell(3000)->addText(number_format($MONTANT_CREDIT_VOTE_4,'0',',',' '),['size'=> 11]);
                   $table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT_4,'0',',',' '),['size'=> 11]);
                   $table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_4,'0',',',' '),['size'=> 11]);
                   $table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_4,0,","," "),['size'=> 11]);
                   $table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_4,0,","," "),['size'=> 11]);
                   $table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_4,0,","," "),['size'=> 11]);
                   $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_4,0,","," "),['size'=> 11]);
                   $table->addCell(3000)->addText(number_format($PAIEMENT_4,0,","," "),['size'=> 11]);
                   $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_4,0,","," "),['size'=> 11]);

                   if ($activ==1) 
                   {
                     $get_tache='SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE INSTITUTION_ID='.$key->INSTITUTION_ID.' AND PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ACTION_ID ='.$key_action->ACTION_ID.' AND CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ';

                    $get_tache = "CALL `getTable`('" .$get_tache."');";
                    $taches= $this->ModelPs->getRequete($get_tache);

                    if(!empty($taches))
                    {
                      foreach($taches as $key_tache)
                      {
                        $params_infos_5='SELECT SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.PAIEMENT) AS PAIEMENT,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT FROM execution_budgetaire exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID WHERE ptba.INSTITUTION_ID='.$key->INSTITUTION_ID.' AND ptba.PROGRAMME_ID='.$key_progr->PROGRAMME_ID.' AND ptba.ACTION_ID ='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba.PTBA_TACHE_ID='.$key_tache->PTBA_TACHE_ID.''.$critere_annee.' '.$critere_date_act.'';

                        $params_infos_5= "CALL `getTable`('" .$params_infos_5."');";
                        $infos_sup_5= $this->ModelPs->getRequeteOne($params_infos_5);


                        $get_vote_task = "SELECT SUM(ptba.BUDGET_ANNUEL) AS CREDIT_VOTE FROM ptba_tache ptba WHERE ptba.INSTITUTION_ID=".$key->INSTITUTION_ID." AND ptba.PROGRAMME_ID=".$key_progr->PROGRAMME_ID." AND ptba.ACTION_ID =".$key_action->ACTION_ID." AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID =".$key_imput->CODE_NOMENCLATURE_BUDGETAIRE_ID." AND ptba.PTBA_TACHE_ID=".$key_tache->PTBA_TACHE_ID;
                        $get_vote_task='CALL `getTable`("'.$get_vote_task.'")';
                        $votes_task = $this->ModelPs->getRequeteOne($get_vote_task);
                        $MONTANT_CREDIT_VOTE_5=!empty($votes_task['CREDIT_VOTE']) ? $votes_task['CREDIT_VOTE'] : '0';

                        //Montant transferé
                        $param_mont_trans_task = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$key_tache->PTBA_TACHE_ID,'1');
                        $param_mont_trans_task=str_replace('\"','"',$param_mont_trans_task);

                        $mont_transf_task=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_task);
                        $MONTANT_TRANSFERT_TASK=floatval($mont_transf_task['MONTANT_TRANSFERT']);

                        //Montant receptionné
                        $param_mont_recep_task = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$key_tache->PTBA_TACHE_ID,'1');
                        $param_mont_recep_task=str_replace('\"','"',$param_mont_recep_task);
                        $mont_recep_task=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_task);
                        $MONTANT_RECEPTION_TASK=floatval($mont_recep_task['MONTANT_RECEPTION']);
                   
                        $MONTANT_TRANSFERT_RESTE_TASK = (floatval($MONTANT_TRANSFERT_TASK) - floatval($MONTANT_RECEPTION_TASK));

                        if($MONTANT_TRANSFERT_RESTE_TASK >= 0)
                        {
                          $MONTANT_TRANSFERT_5=$MONTANT_TRANSFERT_RESTE_TASK;
                        }else{
                          $MONTANT_TRANSFERT_5=$MONTANT_TRANSFERT_RESTE_TASK*(-1);
                        }

                        $CREDIT_APRES_TRANSFERT_5=(floatval($MONTANT_CREDIT_VOTE_5) - floatval($MONTANT_TRANSFERT_TASK)) + floatval($MONTANT_RECEPTION_TASK);

                        if($CREDIT_APRES_TRANSFERT_5 < 0){
                          $CREDIT_APRES_TRANSFERT_5 = $CREDIT_APRES_TRANSFERT_5*(-1);
                        }

                        if($mont_transf_task['PTBA_TACHE_ID']==$mont_recep_task['PTBA_TACHE_ID'])
                        {
                          $MONTANT_TRANSFERT_5 = $MONTANT_TRANSFERT_TASK;
                          $CREDIT_APRES_TRANSFERT_5 = floatval($MONTANT_CREDIT_VOTE_5);
                        }
                        

                        $MONTANT_ENGAGE_5=!empty($infos_sup_5['MONTANT_ENGAGE']) ? $infos_sup_5['MONTANT_ENGAGE'] : '0';
                        $MONTANT_JURIDIQUE_5=!empty($infos_sup_5['MONTANT_JURIDIQUE']) ? $infos_sup_5['MONTANT_JURIDIQUE'] : '0';
                        $MONTANT_LIQUIDATION_5=!empty($infos_sup_5['MONTANT_LIQUIDATION']) ? $infos_sup_5['MONTANT_LIQUIDATION'] : '0';
                        $MONTANT_ORDONNANCEMENT_5=!empty($infos_sup_5['MONTANT_ORDONNANCEMENT']) ? $infos_sup_5['MONTANT_ORDONNANCEMENT'] : '0';
                        $PAIEMENT_5=!empty($infos_sup_5['PAIEMENT']) ? $infos_sup_5['PAIEMENT'] : '0';
                        $MONTANT_DECAISSEMENT_5=!empty($infos_sup_5['MONTANT_DECAISSEMENT'])?$infos_sup_5['MONTANT_DECAISSEMENT']:'0';

                        $DESC_TACHE = str_replace("\n", "", $key_tache->DESC_TACHE);
                        $DESC_TACHE = str_replace("\r", "", $DESC_TACHE);
                        $DESC_TACHE = str_replace("\t", "", $DESC_TACHE);
                        $DESC_TACHE = str_replace('"', '', $DESC_TACHE);
                        $DESC_TACHE = str_replace("'", "\'", $DESC_TACHE);

                        $DESC_TACHE = mb_convert_encoding($DESC_TACHE, 'UTF-8', 'UTF-8');

                          // Add row and cells to the table
                        $table->addRow();
                        $table->addCell(1500, ['cellMargin' => 60])->addText('   ', null, ['spaceBefore' => 10], ['size' => 11]);
                        $table->addCell(3000)->addText(utf8_encode($DESC_TACHE), ['size' => 11]);
                        $table->addCell(3000)->addText(number_format($MONTANT_CREDIT_VOTE_5,'0',',',' '),['size'=> 11]);
                        $table->addCell(3000)->addText(number_format($MONTANT_TRANSFERT_5,'0',',',' '),['size'=> 11]);
                        $table->addCell(3000)->addText(number_format($CREDIT_APRES_TRANSFERT_5,'0',',',' '),['size'=> 11]);
                        $table->addCell(3000)->addText(number_format($MONTANT_ENGAGE_5,0,","," "),['size'=> 11]);
                        $table->addCell(3000)->addText(number_format($MONTANT_JURIDIQUE_5,0,","," "),['size'=> 11]);
                        $table->addCell(3000)->addText(number_format($MONTANT_LIQUIDATION_5,0,","," "),['size'=> 11]);
                        $table->addCell(3000)->addText(number_format($MONTANT_ORDONNANCEMENT_5,0,","," "),['size'=> 11]);
                        $table->addCell(3000)->addText(number_format($PAIEMENT_5,0,","," "),['size'=> 11]);
                        $table->addCell(3000)->addText(number_format($MONTANT_DECAISSEMENT_5,0,","," "),['size'=> 11]);
                      }
                    }

                  }

                }
              }

            }
          }
        }

      }
    }

    $filename = 'Rapport_classification_administrative.docx';
    $phpWord->save($filename);

    return $this->response->download($filename,null)->setFileName($filename);
  }

  function get_date_limit()
  {
    $ANNEE_BUDGETAIRE_ID = $this->request->getPost('ANNEE_BUDGETAIRE_ID');

    $ann=$this->get_annee_budgetaire();

    $psgetrequete = "CALL getRequete(?,?,?,?);";
    //annee budgetaire: mettre par défaut année en cours
    $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
    $anne_budgetaire = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams_anne);
    $exercice = $anne_budgetaire['ANNEE_DESCRIPTION'];

    if($ann==$ANNEE_BUDGETAIRE_ID)
    {
      $date_start= substr($exercice,0,4).'-07-01';
      $date_fin= date('Y-m-d');
    }
    else
    {
      $date_start= substr($exercice,0,4).'-07-01';
      $date_fin= substr($exercice,5).'-06-30';
    }

    $output = array('status' => TRUE ,'datedebut' => $date_start , 'datefin' => $date_fin);
    return $this->response->setJSON($output);
  }
}
?>