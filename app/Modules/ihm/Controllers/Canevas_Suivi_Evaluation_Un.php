<?php 
/**
* RUGAMBA Jean Vainqueur 
* Liste - CANEVAS - SUIVI EVALUATION
* Email: jean.vainqueur@mediabox.bi
* Whatsapp: (+257) 62 47 19 15
* Telephone: (+257) 66 33 43 25
* le 25/04/2024
*/

namespace App\Modules\ihm\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Dompdf\Dompdf;
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M');
class Canevas_Suivi_Evaluation_Un extends BaseController
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
    
    $data = $this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
		//Sélectionner les institutions
    $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`','inst_institutions','1','`CODE_INSTITUTION` ASC');
    $data['instit'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
    //Sélectionner les annees budgetaires
    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','1','ANNEE_BUDGETAIRE_ID ASC');
    $data['annee_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $get_ann_budg);
    //get annee budgetaire en cours
    $data['annee_budgetaire_en_cours']=$this->get_annee_budgetaire();

    //trimestre
    $bindparams_tr=$this->getBindParms('`TRANCHE_ID`,`DESCRIPTION_TRANCHE`','op_tranches','1','TRANCHE_ID');
    $data['tranches'] = $this->ModelPs->getRequete($psgetrequete, $bindparams_tr);

    return view('App\Modules\ihm\Views\Canevas_Suivi_Evaluation_Deux_View',$data);   
  }

	//liste des canevs - suivi evaluation
  function listing()
  {
    $session  = \Config\Services::session();
    
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $CODE_PROGRAMME = $this->request->getPost('CODE_PROGRAMME');
    $CODE_ACTION = $this->request->getPost('CODE_ACTION');
    $ANNEE_BUDGETAIRE_ID = $this->request->getPost('ANNEE_BUDGETAIRE_ID');
    $TRIMESTRE_ID = $this->request->getPost('TRIMESTRE_ID');
    
    $critere="";
    $critere_tranche="";
    $critere_anne="";

    $ann=$this->get_annee_budgetaire();

		//Filtre par institution
    if(!empty($INSTITUTION_ID))
    {
      $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`','inst_institutions','INSTITUTION_ID='.$INSTITUTION_ID,'`DESCRIPTION_INSTITUTION` ASC');
      $inst = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
      $critere= " AND ptba.INSTITUTION_ID ='".$inst['INSTITUTION_ID']."'";


    	if (!empty($CODE_PROGRAMME))
      {

        $critere=" AND prog.CODE_PROGRAMME='".$CODE_PROGRAMME."'";

        if (!empty($CODE_ACTION))
        {
          $critere=" AND act.CODE_ACTION='".$CODE_ACTION."'" ;

        }
      }
    }

    if(!empty($TRIMESTRE_ID))
    {
      $critere_tranche.=" AND racc.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }else{
      $critere_tranche.=" AND racc.TRIMESTRE_ID=5";
    }
    if(!empty($ANNEE_BUDGETAIRE_ID))
    {
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    }else{
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ann;
    }
    
   
    $requetedebase="SELECT ptba.PTBA_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ptba.ACTIVITES,ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,ptba.PROGRAMMATION_FINANCIERE_BIF,ptba.CODES_PROGRAMMATIQUE,ptba.RESPONSABLE,ptba.RESULTATS_ATTENDUS,ptba.UNITE FROM ptba JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1";
    $limit="LIMIT 2";
    if($_POST['length'] != -1)
    {
      $limit="LIMIT ".$_POST["start"].",".$_POST["length"];
    }

    $group="";
    $order_by="";
    $order_column="";
    $order_column= array('inst.DESCRIPTION_INSTITUTION','prog.INTITULE_PROGRAMME','act.LIBELLE_ACTION','ptba.RESPONSABLE','ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba.ACTIVITES','ptba.RESULTATS_ATTENDUS','ptba.UNITE',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);

    $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ." ".$_POST['order']['0']['dir'] : " ORDER BY inst.CODE_INSTITUTION ASC";

    $search = !empty($_POST['search']['value']) ? (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%". $var_search."%' OR ptba.ACTIVITES LIKE '%".$var_search."%' OR ptba.RESULTATS_ATTENDUS LIKE '%".$var_search."%' OR inst.DESCRIPTION_INSTITUTION LIKE '%".$var_search."%' OR inst.CODE_INSTITUTION LIKE '%".$var_search."%' OR prog.CODE_PROGRAMME LIKE '%".$var_search."%' OR prog.INTITULE_PROGRAMME LIKE '%".$var_search."%' OR act.CODE_ACTION LIKE '%".$var_search."%' OR act.LIBELLE_ACTION LIKE '%".$var_search."%' OR ptba.CODES_PROGRAMMATIQUE LIKE '%".$var_search."%' OR ptba.RESPONSABLE LIKE '%".$var_search."%')") : "";

    // Condition pour la requête principale
    $conditions=$critere." ".$search." ".$group." ".$order_by." ".$limit;
    $conditions = str_replace("\\", "", $conditions);  
      // Condition pour la requête de filtre
    $conditionsfilter=$critere." ".$search." ".$group;
    $conditionsfilter = str_replace('"','\\"', $conditionsfilter);

    $requetedebases = $requetedebase." ".$conditions;
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire =' CALL `getTable`("'.$requetedebases.'");';
    $fetch_infos = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach($fetch_infos as $info)
    {
      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(T1) AS total,SUM(QT1) as qte_total";  
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(T2) AS total,SUM(QT2) as qte_total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(T3) AS total,SUM(QT3) as qte_total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(T4) AS total,SUM(QT4) as qte_total";
      }else{
        $montant_total="SUM(PROGRAMMATION_FINANCIERE_BIF) AS total,SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) as qte_total";
      }

      $params_activ=$this->getBindParms($montant_total,'ptba','PTBA_ID="'.$info->PTBA_ID.'"','PTBA_ID ASC');
      $params_activ=str_replace('\"','"',$params_activ);
      $total_vote=$this->ModelPs->getRequeteOne($callpsreq,$params_activ);
      $BUDGET_VOTE=intval($total_vote['total']);
      $BUDGET_VOTE=!empty($BUDGET_VOTE) ? $BUDGET_VOTE : '1';
      $QUANTITE_VOTE=intval($total_vote['qte_total']);

      //récupération des montants à  afficher
      $params_infos=$this->getBindParms('SUM(exec.TRANSFERTS_CREDITS) AS MONTANT_TRANSFERT,SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.CREDIT_APRES_TRANSFERT) AS CREDIT_APRES_TRANSFERT,SUM(exec.PAIEMENT) AS PAIEMENT','execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_new exec ON racc.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID',' racc.PTBA_ID='.$info->PTBA_ID.$critere_tranche.$critere_anne,'1');

      $params_infos=str_replace('\"','"',$params_infos);
      $infos_sup=$this->ModelPs->getRequeteOne($callpsreq,$params_infos);

      ///recuperer le montant,qte realise par trimestre
      $params_qte_realise=$this->getBindParms('SUM(racc.MONTANT_RACCROCHE) resultat_realise,SUM(racc.QTE_RACCROCHE) QTE_REALISE','execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID','1 AND ptba.PTBA_ID='.$info->PTBA_ID.$critere_tranche,'1');
      $params_qte_realise=str_replace('\"','"',$params_qte_realise);
      $qte_realise=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise);

      $RESULTAT_REALISE=!empty($qte_realise['resultat_realise']) ? $qte_realise['resultat_realise'] : '0';
      $MONTANT_TRANSFERT=!empty($infos_sup['MONTANT_TRANSFERT']) ? $infos_sup['MONTANT_TRANSFERT'] : '0';
      $CREDIT_APRES_TRANSFERT=!empty($infos_sup['CREDIT_APRES_TRANSFERT']) ? $infos_sup['CREDIT_APRES_TRANSFERT'] :'0';
      $MONTANT_ENGAGE=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $MONTANT_PAIEMENT=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $MONTANT_TITRE_DECAISSEMENT=!empty($infos_sup['MONTANT_DECAISSEMENT']) ? $infos_sup['MONTANT_DECAISSEMENT'] : '0';
      $QUANTITE_REALISE=!empty($qte_realise['QTE_REALISE']) ? $qte_realise['QTE_REALISE'] : '0';
      $ecart_engage=$BUDGET_VOTE-$MONTANT_ENGAGE;
      $ecart_juridique=$BUDGET_VOTE-$MONTANT_JURIDIQUE;
      $ecart_liquidation=$BUDGET_VOTE-$MONTANT_LIQUIDATION;
      $ecart_ordonnancement=$BUDGET_VOTE-$MONTANT_ORDONNANCEMENT;
      $ecart_paiement=$BUDGET_VOTE-$MONTANT_PAIEMENT;
      $ecart_decaissement=$BUDGET_VOTE-$MONTANT_TITRE_DECAISSEMENT;
      $ecart_physique=$QUANTITE_VOTE-$QUANTITE_REALISE;
      $taux_engage=$MONTANT_ENGAGE*100/$BUDGET_VOTE;
      $taux_juridique=$MONTANT_JURIDIQUE*100/$BUDGET_VOTE;
      $taux_liquidation=$MONTANT_LIQUIDATION*100/$BUDGET_VOTE;
      $taux_ordonnancement=$MONTANT_ORDONNANCEMENT*100/$BUDGET_VOTE;
      $taux_paiement=$MONTANT_PAIEMENT*100/$BUDGET_VOTE;
      $taux_decaissement=$MONTANT_TITRE_DECAISSEMENT*100/$BUDGET_VOTE;

      $post=array();
      $INTITULE_MINISTERE = (mb_strlen($info->INTITULE_MINISTERE) > 9) ? (mb_substr($info->INTITULE_MINISTERE, 0, 9) . '...<a class="btn-sm" title="'.$info->INTITULE_MINISTERE.'"><i class="fa fa-eye"></i></a>') : $info->INTITULE_MINISTERE;

      $INTITULE_PROGRAMME = (mb_strlen($info->INTITULE_PROGRAMME) > 9) ? (mb_substr($info->INTITULE_PROGRAMME, 0, 9) . '...<a class="btn-sm" title="'.$info->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>') : $info->INTITULE_PROGRAMME;

      $LIBELLE_ACTION = (mb_strlen($info->LIBELLE_ACTION) > 9) ? (mb_substr($info->LIBELLE_ACTION, 0, 9) . '...<a class="btn-sm" title="'.$info->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>') : $info->LIBELLE_ACTION;

      $ACTIVITES = (mb_strlen($info->ACTIVITES) > 9) ? (mb_substr($info->ACTIVITES, 0, 9) . '...<a class="btn-sm" title="'.$info->ACTIVITES.'"><i class="fa fa-eye"></i></a>') : $info->ACTIVITES;

      $RESPONSABLE = (mb_strlen($info->RESPONSABLE) > 9) ? (mb_substr($info->RESPONSABLE, 0, 9) . '...<a class="btn-sm" title="'.$info->RESPONSABLE.'"><i class="fa fa-eye"></i></a>') : $info->RESPONSABLE;

      $RESULTATS_ATTENDUS = (mb_strlen($info->RESULTATS_ATTENDUS) > 9) ? (mb_substr($info->RESULTATS_ATTENDUS, 0, 9) . '...<a class="btn-sm" title="'.$info->RESULTATS_ATTENDUS.'"><i class="fa fa-eye"></i></a>') : $info->RESULTATS_ATTENDUS;

      $TRANSFERTS_CREDITS=!empty($infos_sup['MONTANT_TRANSFERT']) ? $infos_sup['MONTANT_TRANSFERT'] : '0';
      $CREDIT_APRES_TRANSFERT=!empty($infos_sup['CREDIT_APRES_TRANSFERT']) ? $infos_sup['CREDIT_APRES_TRANSFERT'] :'0';
      $ENG_BUDGETAIRE=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $JURIDIQUE=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $LIQUIDATION=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $ORDONNANCEMENT=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $DECAISSEMENT=!empty($infos_sup['MONTANT_DECAISSEMENT']) ? $infos_sup['MONTANT_DECAISSEMENT'] :'0';

      $post[]=$INTITULE_MINISTERE;
      $post[]=$INTITULE_PROGRAMME;

      if($info->CODE_ACTION !="")
      {
        $post[]=$LIBELLE_ACTION;
      }
      elseif($info->CODE_ACTION =="")
      {
        $post[]="N/A";
      }

      if($info->RESPONSABLE !="")
      {
        $post[]=$RESPONSABLE;
      }
      elseif($info->RESPONSABLE =="")
      {
        $post[]="N/A";
      }

      $post[]=$info->CODE_NOMENCLATURE_BUDGETAIRE;
      $post[]=$ACTIVITES;
      $post[]=$RESULTATS_ATTENDUS;
      $post[]=$info->UNITE; 
      $post[] = number_format($BUDGET_VOTE,0,","," ");
      $post[] = number_format($TRANSFERTS_CREDITS,0,","," ");
      $post[] = number_format($CREDIT_APRES_TRANSFERT,0,","," ");
      $post[] = number_format($ENG_BUDGETAIRE,0,","," ");
      $post[] = number_format($JURIDIQUE,0,","," ");
      $post[] = number_format($LIQUIDATION,0,","," ");
      $post[] = number_format($ORDONNANCEMENT,0,","," ");
      $post[] = number_format($PAIEMENT,0,","," ");
      $post[] = number_format($DECAISSEMENT,0,","," ");
      $post[] = number_format($ecart_engage,0,","," ");
      $post[] = number_format($ecart_juridique,0,","," ");
      $post[] = number_format($ecart_liquidation,0,","," ");
      $post[] = number_format($ecart_ordonnancement,0,","," ");
      $post[] = number_format($ecart_paiement,0,","," ");
      $post[] = number_format($ecart_decaissement,0,","," ");
      $post[] = number_format($taux_engage,0,","," ");
      $post[] = number_format($taux_juridique,0,","," ");
      $post[] = number_format($taux_liquidation,0,","," ");
      $post[] = number_format($taux_ordonnancement,0,","," ");
      $post[] = number_format($taux_paiement,0,","," ");
      $post[] = number_format($taux_decaissement,0,","," ");
      $post[] = $QUANTITE_REALISE;
      $post[] = $ecart_physique;
      $data[] = $post;
    }

    $recordsTotal=$this->ModelPs->datatable('CALL `getTable`("'.$requetedebase.'")');
    $recordsFiltered=$this->ModelPs->datatable(' CALL `getTable`("'.$requetedebasefilter.'")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  // LES SELECTS DES ACTIVITES
  //Sélectionner les programmes à partir des sous tutelles
  function get_prog()
  {
    $session  = \Config\Services::session();
    $INSTITUTION_ID =$this->request->getPost('INSTITUTION_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    //Sélectionner les intitutions
    $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, '`DESCRIPTION_INSTITUTION` ASC');
    $instit = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

    $get_prog = "SELECT DISTINCT prog.INTITULE_PROGRAMME, prog.CODE_PROGRAMME FROM ptba JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID WHERE ptba.INSTITUTION_ID =".$instit['INSTITUTION_ID']." ORDER BY prog.INTITULE_PROGRAMME ASC ";

    $details='CALL `getTable`("'.$get_prog.'")';
    $prog = $this->ModelPs->getRequete( $details);
    //Declaration des labels pour l'internalisation
    $input_select = lang("messages_lang.labelle_selecte");
    $html='<option value="">'.$input_select.'</option>';

    if(!empty($prog))
    {
      foreach($prog as $key)
      {
        if($key->CODE_PROGRAMME==set_value('CODE_PROGRAMME'))
        {
          $html.= "<option value='".$key->CODE_PROGRAMME."' selected>".$key->CODE_PROGRAMME."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->INTITULE_PROGRAMME."</option>";
        }
        else
        {
          $html.= "<option value='".$key->CODE_PROGRAMME."'>".$key->CODE_PROGRAMME."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->INTITULE_PROGRAMME."</option>";
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
    $CODE_PROGRAMME =$this->request->getPost('CODE_PROGRAMME');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_action = "SELECT DISTINCT act.LIBELLE_ACTION, act.CODE_ACTION FROM ptba JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID WHERE prog.CODE_PROGRAMME LIKE '%".$CODE_PROGRAMME."%' ORDER BY act.LIBELLE_ACTION ";

    $details='CALL `getTable`("'.$get_action.'")';
    $action = $this->ModelPs->getRequete( $details);
    //Declaration des labels pour l'internalisation
    $input_select = lang("messages_lang.labelle_selecte");
    $html='<option value="">'.$input_select.'</option>';
    if(!empty($action) )
    {
      foreach($action as $key)
      {
        if($key->CODE_ACTION==set_value('CODE_ACTION'))
        {
          $html.= "<option value='".$key->CODE_ACTION."'>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
        }
        else
        {
          $html.= "<option value='".$key->CODE_ACTION."'>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
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


  /**
   * fonction pour retourner le tableau des parametre pour le PS pour les selection
   * @param string  $columnselect //colone A selectionner
   * @param string  $table        //table utilisE
   * @param string  $where        //condition dans la clause where
   * @param string  $orderby      //order by
   * @return  mixed
  */
  
  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    // print_r($db->lastQuery);die();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }



  //Fonction pour exporter des donnees
  public function export($INSTITUTION_ID='',$PROGRAMME_ID='',$ACTION_ID='',$ANNEE_BUDGETAIRE_ID='',$TRIMESTRE_ID='')
  {
    $session  = \Config\Services::session();
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $criteres="";
    $crit_inst="";
    $crit_prog="";
    $crit_action="";
    $critere_tranche="";
    $critere_anne="";

    $ann=$this->get_annee_budgetaire();

    if($INSTITUTION_ID!=0)
    {
      $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, '`DESCRIPTION_INSTITUTION` ASC');
      $inst = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
      $crit_inst.=" AND ptba.INSTITUTION_ID=".$inst['INSTITUTION_ID']."";
    }


    if($PROGRAMME_ID!=0)
    {
      $crit_prog.=" AND prog.CODE_PROGRAMME=".$PROGRAMME_ID;
    }

    if($ACTION_ID!=0)
    {
      $crit_action.=" AND act.CODE_ACTION=".$ACTION_ID;
    }

    $criteres.=$crit_inst.$crit_prog.$crit_action;

    if($TRIMESTRE_ID!=0)
    {
      $critere_tranche.=" AND racc.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }else{
      $critere_tranche.=" AND racc.TRIMESTRE_ID=5";
    }
    if($ANNEE_BUDGETAIRE_ID!=0)
    {
      $critere_anne.=" AND racc.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    }else{
      $critere_anne.=" AND racc.ANNEE_BUDGETAIRE_ID=".$ann;
    }

    $get_institutions = $this->getBindParms('inst.INSTITUTION_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst_institutions.DESCRIPTION_INSTITUTION','ptba JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba.INSTITUTION_ID','1 '.$crit_inst.' GROUP BY ptba.INSTITUTION_ID', 'ptba.INSTITUTION_ID ASC');

    $institutions = $this->ModelPs->getRequete($callpsreq, $get_institutions);


    $spreadsheet = new Spreadsheet();

    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Code Budgétaire')->getStyle('A1')->getFont()->setBold(true);
    $cell = $sheet->getCell('B1');
    $text = "INTITULE MINISTERE/PROGRAMME/ACTION/\nSTRUCTURE RESPONSABLE/ACTIVITE";
    $cell->setValue($text);
    $cell->getStyle()->getAlignment()->setWrapText(true);
    $cell->getStyle()->getFont()->setBold(true);
    $sheet->setCellValue('C1', 'Intitulé des résultats attendus annuels')->getStyle('C1')->getFont()->setBold(true);
    $sheet->setCellValue('D1', 'Intitulé des résultats attendus au Trimestre T')->getStyle('D1')->getFont()->setBold(true);
    $sheet->setCellValue('E1', 'Budget voté')->getStyle('E1')->getFont()->setBold(true);       
    $sheet->setCellValue('F1', 'TRANSFERTS CREDITS')->getStyle('F1')->getFont()->setBold(true);
    $sheet->setCellValue('G1', 'CREDIT APRES TRANSFERT')->getStyle('G1')->getFont()->setBold(true);
    $sheet->setCellValue('H1', 'ENGAGEMENT BUDGETAIRE')->getStyle('H1')->getFont()->setBold(true);
    $sheet->setCellValue('I1', 'ENGAGEMENT JURIDIQUE')->getStyle('I1')->getFont()->setBold(true);
    $sheet->setCellValue('J1', 'LIQUIDATION')->getStyle('J1')->getFont()->setBold(true);
    $sheet->setCellValue('K1', 'ORDONNANCEMENT')->getStyle('K1')->getFont()->setBold(true);
    $sheet->setCellValue('L1', 'PAIEMENT')->getStyle('L1')->getFont()->setBold(true);
    $sheet->setCellValue('M1', 'DECAISSEMENT')->getStyle('M1')->getFont()->setBold(true);
    $sheet->setCellValue('N1', 'ECART BUDGETAIRE')->getStyle('N1')->getFont()->setBold(true);
    $sheet->setCellValue('O1', 'ECART JURIDIQUE')->getStyle('O1')->getFont()->setBold(true);
    $sheet->setCellValue('P1', 'ECART LIQUIDATION')->getStyle('P1')->getFont()->setBold(true);
    $sheet->setCellValue('Q1', 'ECART ORDONNANCEMENT')->getStyle('Q1')->getFont()->setBold(true);
    $sheet->setCellValue('R1', 'ECART PAIEMENT')->getStyle('R1')->getFont()->setBold(true);
    $sheet->setCellValue('S1', 'ECART DECAISSEMENT')->getStyle('S1')->getFont()->setBold(true);
    $sheet->setCellValue('T1', 'TAUX BUDGETAIRE')->getStyle('T1')->getFont()->setBold(true);
    $sheet->setCellValue('U1', 'TAUX JURIDIQUE')->getStyle('U1')->getFont()->setBold(true);
    $sheet->setCellValue('V1', 'TAUX LIQUIDATION')->getStyle('V1')->getFont()->setBold(true);
    $sheet->setCellValue('W1', 'TAUX ORDONNANCEMENT')->getStyle('W1')->getFont()->setBold(true);
    $sheet->setCellValue('X1', 'TAUX PAIEMENT')->getStyle('X1')->getFont()->setBold(true);
    $sheet->setCellValue('Y1', 'TAUX DECAISSEMENT')->getStyle('Y1')->getFont()->setBold(true);
    $sheet->setCellValue('Z1', 'Résultats attendus au Trimestre T')->getStyle('Z1')->getFont()->setBold(true);
    $sheet->setCellValue('AA1', 'Ecart physique')->getStyle('AA1')->getFont()->setBold(true);

    
    $rows = 3;
    foreach ($institutions as $key)
    {
      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(T1) AS total,SUM(QT1) as qte_total";  
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(T2) AS total,SUM(QT2) as qte_total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(T3) AS total,SUM(QT3) as qte_total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(T4) AS total,SUM(QT4) as qte_total";
      }else{
        $montant_total="SUM(PROGRAMMATION_FINANCIERE_BIF) AS total,SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) as qte_total";
      }

      $params_activ=$this->getBindParms($montant_total,'ptba','ptba.INSTITUTION_ID="'.$key->INSTITUTION_ID.'" '.$crit_inst.' GROUP BY CODE_MINISTERE','CODE_MINISTERE ASC');
      $params_activ=str_replace('\"','"',$params_activ);
      $total_vote=$this->ModelPs->getRequeteOne($callpsreq,$params_activ);
      $BUDGET_VOTE=intval($total_vote['total']);
      $BUDGET_VOTE=!empty($BUDGET_VOTE) ? $BUDGET_VOTE : '1';
      $QUANTITE_VOTE=intval($total_vote['qte_total']);

      //récupération des montants à  afficher
      $params_infos=$this->getBindParms('SUM(exec.TRANSFERTS_CREDITS) AS MONTANT_TRANSFERT,SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.CREDIT_APRES_TRANSFERT) AS CREDIT_APRES_TRANSFERT,SUM(exec.PAIEMENT) AS PAIEMENT','execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_new exec ON racc.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID','ptba.INSTITUTION_ID="'.$key->INSTITUTION_ID.'"'.$crit_inst.$critere_tranche.$critere_anne.' GROUP BY ptba.INSTITUTION_ID',' ptba.INSTITUTION_ID ASC');

      $params_infos=str_replace('\"','"',$params_infos);
      $infos_sup=$this->ModelPs->getRequeteOne($callpsreq,$params_infos);

      ///recuperer le montant,qte realise par trimestre
      $params_qte_realise=$this->getBindParms('SUM(racc.MONTANT_RACCROCHE) resultat_realise,SUM(racc.QTE_RACCROCHE) QTE_REALISE','execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID','ptba.INSTITUTION_ID="'.$key->INSTITUTION_ID.'" '.$crit_inst.$critere_tranche.$critere_anne.' GROUP BY ptba.INSTITUTION_ID',' ptba.INSTITUTION_ID ASC');
      $params_qte_realise=str_replace('\"','"',$params_qte_realise);
      $qte_realise=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise);

      $RESULTAT_REALISE=!empty($qte_realise['resultat_realise']) ? $qte_realise['resultat_realise'] : '0';
      $MONTANT_TRANSFERT=!empty($infos_sup['MONTANT_TRANSFERT']) ? $infos_sup['MONTANT_TRANSFERT'] : '0';
      $CREDIT_APRES_TRANSFERT=!empty($infos_sup['CREDIT_APRES_TRANSFERT']) ? $infos_sup['CREDIT_APRES_TRANSFERT'] :'0';
      $MONTANT_ENGAGE=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $MONTANT_PAIEMENT=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $MONTANT_DECAISSEMENT=!empty($infos_sup['MONTANT_DECAISSEMENT']) ? $infos_sup['MONTANT_DECAISSEMENT'] : '0';
      $QUANTITE_REALISE=!empty($qte_realise['QTE_REALISE']) ? $qte_realise['QTE_REALISE'] : '0';
      $ecart_engage=$BUDGET_VOTE-$MONTANT_ENGAGE;
      $ecart_juridique=$BUDGET_VOTE-$MONTANT_JURIDIQUE;
      $ecart_liquidation=$BUDGET_VOTE-$MONTANT_LIQUIDATION;
      $ecart_ordonnancement=$BUDGET_VOTE-$MONTANT_ORDONNANCEMENT;
      $ecart_paiement=$BUDGET_VOTE-$MONTANT_PAIEMENT;
      $ecart_decaissement=$BUDGET_VOTE-$MONTANT_DECAISSEMENT;
      $ecart_physique=$QUANTITE_VOTE-$QUANTITE_REALISE;
      $taux_engage=$MONTANT_ENGAGE*100/$BUDGET_VOTE;
      $taux_juridique=$MONTANT_JURIDIQUE*100/$BUDGET_VOTE;
      $taux_liquidation=$MONTANT_LIQUIDATION*100/$BUDGET_VOTE;
      $taux_ordonnancement=$MONTANT_ORDONNANCEMENT*100/$BUDGET_VOTE;
      $taux_paiement=$MONTANT_PAIEMENT*100/$BUDGET_VOTE;
      $taux_decaissement=$MONTANT_DECAISSEMENT*100/$BUDGET_VOTE;

      
      $sheet->setCellValue('A' . $rows, ' '.$key->CODE_MINISTERE.'')->getStyle('A'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('B' . $rows, $key->DESCRIPTION_INSTITUTION)->getStyle('B'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('C' . $rows, ' ');
      $sheet->setCellValue('D' . $rows, ' ');
      $sheet->setCellValue('E' . $rows, $BUDGET_VOTE)->getStyle('E'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('F' . $rows, $MONTANT_TRANSFERT)->getStyle('F'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('G' . $rows, $CREDIT_APRES_TRANSFERT)->getStyle('G'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('H' . $rows, $MONTANT_ENGAGE)->getStyle('H'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('I' . $rows, $MONTANT_JURIDIQUE)->getStyle('I'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('J' . $rows, $MONTANT_LIQUIDATION)->getStyle('J'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('K' . $rows, $MONTANT_ORDONNANCEMENT)->getStyle('K'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('L' . $rows, $MONTANT_PAIEMENT)->getStyle('L'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('M' . $rows, $MONTANT_DECAISSEMENT)->getStyle('M'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('N' . $rows, $ecart_engage)->getStyle('N'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('O' . $rows, $ecart_juridique)->getStyle('O'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('P' . $rows, $ecart_liquidation)->getStyle('P'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('Q' . $rows, $ecart_ordonnancement)->getStyle('Q'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('R' . $rows, $ecart_paiement)->getStyle('R'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('S' . $rows, $ecart_decaissement)->getStyle('S'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('T' . $rows, $taux_engage)->getStyle('T'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('U' . $rows, $taux_juridique)->getStyle('U'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('V' . $rows, $taux_liquidation)->getStyle('V'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('W' . $rows, $taux_ordonnancement)->getStyle('W'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('X' . $rows, $taux_paiement)->getStyle('X'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('Y' . $rows, $taux_decaissement)->getStyle('Y'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('Z' . $rows, $QUANTITE_REALISE)->getStyle('Z'.$rows)->getFont()->setBold(true);
      $sheet->setCellValue('AA' . $rows, $ecart_physique)->getStyle('AA'.$rows)->getFont()->setBold(true);
      $rows++;
      
      $get_program = $this->getBindParms('prog.PROGRAMME_ID,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME', 'ptba JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID', 'ptba.INSTITUTION_ID="'.$key->INSTITUTION_ID.'" '.$crit_inst.$crit_prog.' GROUP BY prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME','prog.CODE_PROGRAMME ASC');
      $get_program=str_replace('\"','"',$get_program);

      $program = $this->ModelPs->getRequete($callpsreq, $get_program);
      //print_r($program);exit();
      if(!empty($program))
      {
        $rows1=$rows+1;
        foreach($program as $key_prog)
        {
          if($TRIMESTRE_ID==1)
          {
            $montant_total_1="SUM(T1) AS total,SUM(QT1) as qte_total";  
          }
          else if ($TRIMESTRE_ID==2)
          {
            $montant_total_1="SUM(T2) AS total,SUM(QT2) as qte_total";
          }
          else if ($TRIMESTRE_ID==3)
          {
            $montant_total_1="SUM(T3) AS total,SUM(QT3) as qte_total";
          }
          else if ($TRIMESTRE_ID==4)
          {
            $montant_total_1="SUM(T4) AS total,SUM(QT4) as qte_total";
          }else{
            $montant_total_1="SUM(PROGRAMMATION_FINANCIERE_BIF) AS total,SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) as qte_total";
          }

          $params_activ_1=$this->getBindParms($montant_total_1,'ptba JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID','ptba.PROGRAMME_ID="'.$key_prog->PROGRAMME_ID.'" '.$crit_inst.$crit_prog.' GROUP BY prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME','prog.CODE_PROGRAMME ASC');
          $params_activ_1=str_replace('\"','"',$params_activ_1);
          $total_vote_1=$this->ModelPs->getRequeteOne($callpsreq,$params_activ_1);
          $BUDGET_VOTE_1=intval($total_vote_1['total']);
          $BUDGET_VOTE_1=!empty($BUDGET_VOTE_1) ? $BUDGET_VOTE_1 : '1';
          $QUANTITE_VOTE_1=intval($total_vote_1['qte_total']);

          //récupération des montants à  afficher
          $params_infos_1=$this->getBindParms('SUM(exec.TRANSFERTS_CREDITS) AS MONTANT_TRANSFERT,SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.CREDIT_APRES_TRANSFERT) AS CREDIT_APRES_TRANSFERT,SUM(exec.PAIEMENT) AS PAIEMENT','execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_new exec ON racc.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID','ptba.PROGRAMME_ID="'.$key_prog->PROGRAMME_ID.'" '.$crit_inst.$crit_prog.$critere_tranche.$critere_anne.' GROUP BY prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME',' prog.CODE_PROGRAMME ASC');

          $params_infos_1=str_replace('\"','"',$params_infos_1);
          $infos_sup_1=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_1);

          ///recuperer le montant,qte realise par trimestre
          $params_qte_realise_1=$this->getBindParms('SUM(racc.MONTANT_RACCROCHE) resultat_realise,SUM(racc.QTE_RACCROCHE) QTE_REALISE','execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID','ptba.PROGRAMME_ID="'.$key_prog->PROGRAMME_ID.'" '.$crit_inst.$crit_prog.$critere_tranche.$critere_anne.' GROUP BY prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME','prog.CODE_PROGRAMME ASC');
          $params_qte_realise_1=str_replace('\"','"',$params_qte_realise_1);
          $qte_realise_1=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_1);

          $RESULTAT_REALISE_1=!empty($qte_realise_1['resultat_realise']) ? $qte_realise_1['resultat_realise'] : '0';
          $MONTANT_TRANSFERT_1=!empty($infos_sup_1['MONTANT_TRANSFERT']) ? $infos_sup_1['MONTANT_TRANSFERT'] : '0';
          $CREDIT_APRES_TRANSFERT_1=!empty($infos_sup_1['CREDIT_APRES_TRANSFERT']) ? $infos_sup_1['CREDIT_APRES_TRANSFERT'] :'0';
          $MONTANT_ENGAGE_1=!empty($infos_sup_1['MONTANT_ENGAGE']) ? $infos_sup_1['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_1=!empty($infos_sup_1['MONTANT_JURIDIQUE']) ? $infos_sup_1['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_1=!empty($infos_sup_1['MONTANT_LIQUIDATION']) ? $infos_sup_1['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_1=!empty($infos_sup_1['MONTANT_ORDONNANCEMENT']) ? $infos_sup_1['MONTANT_ORDONNANCEMENT'] : '0';
          $MONTANT_PAIEMENT_1=!empty($infos_sup_1['PAIEMENT']) ? $infos_sup_1['PAIEMENT'] : '0';
          $MONTANT_DECAISSEMENT_1=!empty($infos_sup_1['MONTANT_DECAISSEMENT']) ? $infos_sup_1['MONTANT_DECAISSEMENT'] : '0';
          $QUANTITE_REALISE_1=!empty($qte_realise_1['QTE_REALISE']) ? $qte_realise_1['QTE_REALISE'] : '0';
          $ecart_engage_1=$BUDGET_VOTE_1-$MONTANT_ENGAGE_1;
          $ecart_juridique_1=$BUDGET_VOTE_1-$MONTANT_JURIDIQUE_1;
          $ecart_liquidation_1=$BUDGET_VOTE_1-$MONTANT_LIQUIDATION_1;
          $ecart_ordonnancement_1=$BUDGET_VOTE_1-$MONTANT_ORDONNANCEMENT_1;
          $ecart_paiement_1=$BUDGET_VOTE_1-$MONTANT_PAIEMENT_1;
          $ecart_decaissemen_1=$BUDGET_VOTE_1-$MONTANT_DECAISSEMENT_1;
          $ecart_physique_1=$QUANTITE_VOTE_1-$QUANTITE_REALISE_1;
          $taux_engage_1=$MONTANT_ENGAGE_1*100/$BUDGET_VOTE_1;
          $taux_juridique_1=$MONTANT_JURIDIQUE_1*100/$BUDGET_VOTE_1;
          $taux_liquidation_1=$MONTANT_LIQUIDATION_1*100/$BUDGET_VOTE_1;
          $taux_ordonnancement_1=$MONTANT_ORDONNANCEMENT_1*100/$BUDGET_VOTE_1;
          $taux_paiement_1=$MONTANT_PAIEMENT_1*100/$BUDGET_VOTE_1;
          $taux_decaissement_1=$MONTANT_DECAISSEMENT_1*100/$BUDGET_VOTE_1;

      
          $sheet->setCellValue('A' . $rows1, '  '.$key_prog->CODE_PROGRAMME)->getStyle('A'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('B' . $rows1, '  '.$key_prog->INTITULE_PROGRAMME)->getStyle('B'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('C' . $rows1, ' ');
          $sheet->setCellValue('D' . $rows1, ' ');
          $sheet->setCellValue('E' . $rows1, $BUDGET_VOTE_1)->getStyle('E'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('F' . $rows1, $MONTANT_TRANSFERT_1)->getStyle('F'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('G' . $rows1, $CREDIT_APRES_TRANSFERT_1)->getStyle('G'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('H' . $rows1, $MONTANT_ENGAGE_1)->getStyle('H'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('I' . $rows1, $MONTANT_JURIDIQUE_1)->getStyle('I'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('J' . $rows1, $MONTANT_LIQUIDATION_1)->getStyle('J'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('K' . $rows1, $MONTANT_ORDONNANCEMENT_1)->getStyle('K'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('L' . $rows1, $MONTANT_PAIEMENT_1)->getStyle('L'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('M' . $rows1, $MONTANT_DECAISSEMENT_1)->getStyle('M'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('N' . $rows1, $ecart_engage_1)->getStyle('N'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('O' . $rows1, $ecart_juridique_1)->getStyle('O'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('P' . $rows1, $ecart_liquidation_1)->getStyle('P'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('Q' . $rows1, $ecart_ordonnancement_1)->getStyle('Q'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('R' . $rows1, $ecart_paiement_1)->getStyle('R'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('S' . $rows1, $ecart_decaissemen_1)->getStyle('S'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('T' . $rows1, $taux_engage_1)->getStyle('T'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('U' . $rows1, $taux_juridique_1)->getStyle('U'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('V' . $rows1, $taux_liquidation_1)->getStyle('V'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('W' . $rows1, $taux_ordonnancement_1)->getStyle('W'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('X' . $rows1, $taux_paiement_1)->getStyle('X'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('Y' . $rows1, $taux_decaissement_1)->getStyle('Y'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('Z' . $rows1, $QUANTITE_REALISE_1)->getStyle('Z'.$rows1)->getFont()->setBold(true);
          $sheet->setCellValue('AA' . $rows1, $ecart_physique_1)->getStyle('AA'.$rows1)->getFont()->setBold(true);
          $rows1++;

          $get_action = $this->getBindParms('act.ACTION_ID,act.CODE_ACTION,act.LIBELLE_ACTION','ptba JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID','ptba.PROGRAMME_ID="'.$key_prog->PROGRAMME_ID.'" '.$criteres.' GROUP BY act.CODE_ACTION,act.LIBELLE_ACTION', 'act.CODE_ACTION ASC');
          $get_action=str_replace('\"','"',$get_action);

          $action = $this->ModelPs->getRequete($callpsreq, $get_action);

          if(!empty($action)) 
          {
            $rows2=$rows1+1;

            foreach ($action as $key_act) 
            {
              if($TRIMESTRE_ID==1)
              {
                $montant_total_2="SUM(T1) AS total,SUM(QT1) as qte_total";  
              }
              else if ($TRIMESTRE_ID==2)
              {
                $montant_total_2="SUM(T2) AS total,SUM(QT2) as qte_total";
              }
              else if ($TRIMESTRE_ID==3)
              {
                $montant_total_2="SUM(T3) AS total,SUM(QT3) as qte_total";
              }
              else if ($TRIMESTRE_ID==4)
              {
                $montant_total_2="SUM(T4) AS total,SUM(QT4) as qte_total";
              }else{
                $montant_total_2="SUM(PROGRAMMATION_FINANCIERE_BIF) AS total,SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) as qte_total";
              }

              $params_activ_2=$this->getBindParms($montant_total_2,'ptba JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID','ptba.ACTION_ID="'.$key_act->ACTION_ID.'" '.$criteres.' GROUP BY act.CODE_ACTION,act.LIBELLE_ACTION','act.CODE_ACTION ASC');
              $params_activ_2=str_replace('\"','"',$params_activ_2);
              $total_vote_2=$this->ModelPs->getRequeteOne($callpsreq,$params_activ_2);
              $BUDGET_VOTE_2=intval($total_vote_2['total']);
              $BUDGET_VOTE_2=!empty($BUDGET_VOTE_2) ? $BUDGET_VOTE_2 : '1';
              $QUANTITE_VOTE_2=intval($total_vote_2['qte_total']);

              //récupération des montants à  afficher
              $params_infos_2=$this->getBindParms('SUM(exec.TRANSFERTS_CREDITS) AS MONTANT_TRANSFERT,SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.CREDIT_APRES_TRANSFERT) AS CREDIT_APRES_TRANSFERT,SUM(exec.PAIEMENT) AS PAIEMENT','execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_new exec ON racc.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID','ptba.ACTION_ID="'.$key_act->ACTION_ID.'" '.$criteres.$critere_tranche.$critere_anne.' GROUP BY act.CODE_ACTION,act.LIBELLE_ACTION','act.CODE_ACTION ASC');

              $params_infos_2=str_replace('\"','"',$params_infos_2);
              $infos_sup_2=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_2);

              ///recuperer le montant,qte realise par trimestre
              $params_qte_realise_2=$this->getBindParms('SUM(racc.MONTANT_RACCROCHE) resultat_realise,SUM(racc.QTE_RACCROCHE) QTE_REALISE','execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID','ptba.ACTION_ID="'.$key_act->ACTION_ID.'" '.$criteres.$critere_tranche.$critere_anne.' GROUP BY act.CODE_ACTION,act.LIBELLE_ACTION','act.CODE_ACTION ASC');
              $params_qte_realise_2=str_replace('\"','"',$params_qte_realise_2);
              $qte_realise_2=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_2);

              $RESULTAT_REALISE_2=!empty($qte_realise_2['resultat_realise']) ? $qte_realise_2['resultat_realise'] : '0';
              $MONTANT_TRANSFERT_2=!empty($infos_sup_2['MONTANT_TRANSFERT']) ? $infos_sup_2['MONTANT_TRANSFERT'] : '0';
              $CREDIT_APRES_TRANSFERT_2=!empty($infos_sup_2['CREDIT_APRES_TRANSFERT']) ? $infos_sup_2['CREDIT_APRES_TRANSFERT'] :'0';
              $MONTANT_ENGAGE_2=!empty($infos_sup_2['MONTANT_ENGAGE']) ? $infos_sup_2['MONTANT_ENGAGE'] : '0';
              $MONTANT_JURIDIQUE_2=!empty($infos_sup_2['MONTANT_JURIDIQUE']) ? $infos_sup_2['MONTANT_JURIDIQUE'] : '0';
              $MONTANT_LIQUIDATION_2=!empty($infos_sup_2['MONTANT_LIQUIDATION']) ? $infos_sup_2['MONTANT_LIQUIDATION'] : '0';
              $MONTANT_ORDONNANCEMENT_2=!empty($infos_sup_2['MONTANT_ORDONNANCEMENT']) ? $infos_sup_2['MONTANT_ORDONNANCEMENT'] : '0';
              $MONTANT_PAIEMENT_2=!empty($infos_sup_2['PAIEMENT']) ? $infos_sup_2['PAIEMENT'] : '0';
              $MONTANT_DECAISSEMENT_2=!empty($infos_sup_2['MONTANT_DECAISSEMENT']) ? $infos_sup_2['MONTANT_DECAISSEMENT'] : '0';
              $QUANTITE_REALISE_2=!empty($qte_realise_2['QTE_REALISE']) ? $qte_realise_2['QTE_REALISE'] : '0';
              $ecart_engage_2=$BUDGET_VOTE_2-$MONTANT_ENGAGE_2;
              $ecart_juridique_2=$BUDGET_VOTE_2-$MONTANT_JURIDIQUE_2;
              $ecart_liquidation_2=$BUDGET_VOTE_2-$MONTANT_LIQUIDATION_2;
              $ecart_ordonnancement_2=$BUDGET_VOTE_2-$MONTANT_ORDONNANCEMENT_2;
              $ecart_paiement_2=$BUDGET_VOTE_2-$MONTANT_PAIEMENT_2;
              $ecart_decaissemen_2=$BUDGET_VOTE_2-$MONTANT_DECAISSEMENT_2;
              $ecart_physique_2=$QUANTITE_VOTE_2-$QUANTITE_REALISE_2;
              $taux_engage_2=$MONTANT_ENGAGE_2*100/$BUDGET_VOTE_2;
              $taux_juridique_2=$MONTANT_JURIDIQUE_2*100/$BUDGET_VOTE_2;
              $taux_liquidation_2=$MONTANT_LIQUIDATION_2*100/$BUDGET_VOTE_2;
              $taux_ordonnancement_2=$MONTANT_ORDONNANCEMENT_2*100/$BUDGET_VOTE_2;
              $taux_paiement_2=$MONTANT_PAIEMENT_2*100/$BUDGET_VOTE_2;
              $taux_decaissement_2=$MONTANT_DECAISSEMENT_2*100/$BUDGET_VOTE_2;

      
              $sheet->setCellValue('A' . $rows2, '    '.$key_act->CODE_ACTION)->getStyle('A'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('B' . $rows2, '    '.$key_act->LIBELLE_ACTION)->getStyle('B'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('C' . $rows2, ' ');
              $sheet->setCellValue('D' . $rows2, ' ');
              $sheet->setCellValue('E' . $rows2, $BUDGET_VOTE_2)->getStyle('E'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('F' . $rows2, $MONTANT_TRANSFERT_2)->getStyle('F'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('G' . $rows2, $CREDIT_APRES_TRANSFERT_2)->getStyle('G'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('H' . $rows2, $MONTANT_ENGAGE_2)->getStyle('H'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('I' . $rows2, $MONTANT_JURIDIQUE_2)->getStyle('I'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('J' . $rows2, $MONTANT_LIQUIDATION_2)->getStyle('J'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('K' . $rows2, $MONTANT_ORDONNANCEMENT_2)->getStyle('K'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('L' . $rows2, $MONTANT_PAIEMENT_2)->getStyle('L'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('M' . $rows2, $MONTANT_DECAISSEMENT_2)->getStyle('M'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('N' . $rows2, $ecart_engage_2)->getStyle('N'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('O' . $rows2, $ecart_juridique_2)->getStyle('O'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('P' . $rows2, $ecart_liquidation_2)->getStyle('P'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('Q' . $rows2, $ecart_ordonnancement_2)->getStyle('Q'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('R' . $rows2, $ecart_paiement_2)->getStyle('R'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('S' . $rows2, $ecart_decaissemen_2)->getStyle('S'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('T' . $rows2, $taux_engage_2)->getStyle('T'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('U' . $rows2, $taux_juridique_2)->getStyle('U'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('V' . $rows2, $taux_liquidation_2)->getStyle('V'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('W' . $rows2, $taux_ordonnancement_2)->getStyle('W'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('X' . $rows2, $taux_paiement_2)->getStyle('X'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('Y' . $rows2, $taux_decaissement_2)->getStyle('Y'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('Z' . $rows2, $QUANTITE_REALISE_2)->getStyle('Z'.$rows2)->getFont()->setBold(true);
              $sheet->setCellValue('AA' . $rows2, $ecart_physique_2)->getStyle('AA'.$rows2)->getFont()->setBold(true);
              $rows2++;

              
              //Boucle des responsables  
              $get_respo = $this->getBindParms('ptba.RESPONSABLE', 'ptba', 'ptba.ACTION_ID="'.$key_act->ACTION_ID.'" '.$criteres.' GROUP BY ptba.RESPONSABLE','ptba.RESPONSABLE ASC');

              $get_respo=str_replace('\"', '"', $get_respo);
              $responsable = $this->ModelPs->getRequete($callpsreq, $get_respo);

              if (!empty($responsable)) 
              {
                $rows3=$rows2+1;

                foreach ($responsable as $key_respo) 
                {
                  if($TRIMESTRE_ID==1)
                  {
                    $montant_total_3="SUM(T1) AS total,SUM(QT1) as qte_total";  
                  }
                  else if ($TRIMESTRE_ID==2)
                  {
                    $montant_total_3="SUM(T2) AS total,SUM(QT2) as qte_total";
                  }
                  else if ($TRIMESTRE_ID==3)
                  {
                    $montant_total_3="SUM(T3) AS total,SUM(QT3) as qte_total";
                  }
                  else if ($TRIMESTRE_ID==4)
                  {
                    $montant_total_3="SUM(T4) AS total,SUM(QT4) as qte_total";
                  }else{
                    $montant_total_3="SUM(PROGRAMMATION_FINANCIERE_BIF) AS total,SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) as qte_total";
                  }

                  $params_activ_3=$this->getBindParms($montant_total_3,'ptba','ptba.RESPONSABLE LIKE "%'.$key_respo->RESPONSABLE.'%" '.$criteres.' GROUP BY ptba.RESPONSABLE','ptba.RESPONSABLE ASC');
                  $params_activ_3=str_replace('\"','"',$params_activ_3);
                  $total_vote_3=$this->ModelPs->getRequeteOne($callpsreq,$params_activ_3);
                  $BUDGET_VOTE_3=intval($total_vote_3['total']);
                  $BUDGET_VOTE_3=!empty($BUDGET_VOTE_3) ? $BUDGET_VOTE_3 : '1';
                  $QUANTITE_VOTE_3=intval($total_vote_3['qte_total']);

                  //récupération des montants à  afficher
                  $params_infos_3=$this->getBindParms('SUM(exec.TRANSFERTS_CREDITS) AS MONTANT_TRANSFERT,SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.CREDIT_APRES_TRANSFERT) AS CREDIT_APRES_TRANSFERT,SUM(exec.PAIEMENT) AS PAIEMENT','execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_new exec ON racc.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID','ptba.RESPONSABLE LIKE "%'.$key_respo->RESPONSABLE.'%" '.$criteres.$critere_tranche.$critere_anne.' GROUP BY ptba.RESPONSABLE','ptba.RESPONSABLE ASC');

                  $params_infos_3=str_replace('\"','"',$params_infos_3);
                  $infos_sup_3=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_3);

                  ///recuperer le montant,qte realise par trimestre
                  $params_qte_realise_3=$this->getBindParms('SUM(racc.MONTANT_RACCROCHE) resultat_realise,SUM(racc.QTE_RACCROCHE) QTE_REALISE','execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID','ptba.RESPONSABLE LIKE "%'.$key_respo->RESPONSABLE.'%" '.$criteres.$critere_tranche.$critere_anne.' GROUP BY ptba.RESPONSABLE','ptba.RESPONSABLE ASC');
                  $params_qte_realise_3=str_replace('\"','"',$params_qte_realise_3);
                  $qte_realise_3=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_3);

                  $RESULTAT_REALISE_3=!empty($qte_realise_3['resultat_realise']) ? $qte_realise_3['resultat_realise'] : '0';
                  $MONTANT_TRANSFERT_3=!empty($infos_sup_3['MONTANT_TRANSFERT']) ? $infos_sup_3['MONTANT_TRANSFERT'] : '0';
                  $CREDIT_APRES_TRANSFERT_3=!empty($infos_sup_3['CREDIT_APRES_TRANSFERT']) ? $infos_sup_3['CREDIT_APRES_TRANSFERT'] :'0';
                  $MONTANT_ENGAGE_3=!empty($infos_sup_3['MONTANT_ENGAGE']) ? $infos_sup_3['MONTANT_ENGAGE'] : '0';
                  $MONTANT_JURIDIQUE_3=!empty($infos_sup_3['MONTANT_JURIDIQUE']) ? $infos_sup_3['MONTANT_JURIDIQUE'] : '0';
                  $MONTANT_LIQUIDATION_3=!empty($infos_sup_3['MONTANT_LIQUIDATION']) ? $infos_sup_3['MONTANT_LIQUIDATION'] : '0';
                  $MONTANT_ORDONNANCEMENT_3=!empty($infos_sup_3['MONTANT_ORDONNANCEMENT']) ? $infos_sup_3['MONTANT_ORDONNANCEMENT'] : '0';
                  $MONTANT_PAIEMENT_3=!empty($infos_sup_3['PAIEMENT']) ? $infos_sup_3['PAIEMENT'] : '0';
                  $MONTANT_DECAISSEMENT_3=!empty($infos_sup_3['MONTANT_DECAISSEMENT']) ? $infos_sup_3['MONTANT_DECAISSEMENT'] : '0';
                  $QUANTITE_REALISE_3=!empty($qte_realise_3['QTE_REALISE']) ? $qte_realise_3['QTE_REALISE'] : '0';
                  $ecart_engage_3=$BUDGET_VOTE_3-$MONTANT_ENGAGE_3;
                  $ecart_juridique_3=$BUDGET_VOTE_3-$MONTANT_JURIDIQUE_3;
                  $ecart_liquidation_3=$BUDGET_VOTE_3-$MONTANT_LIQUIDATION_3;
                  $ecart_ordonnancement_3=$BUDGET_VOTE_3-$MONTANT_ORDONNANCEMENT_3;
                  $ecart_paiement_3=$BUDGET_VOTE_3-$MONTANT_PAIEMENT_3;
                  $ecart_decaissemen_3=$BUDGET_VOTE_3-$MONTANT_DECAISSEMENT_3;
                  $ecart_physique_3=$QUANTITE_VOTE_3-$QUANTITE_REALISE_3;
                  $taux_engage_3=$MONTANT_ENGAGE_3*100/$BUDGET_VOTE_3;
                  $taux_juridique_3=$MONTANT_JURIDIQUE_3*100/$BUDGET_VOTE_3;
                  $taux_liquidation_3=$MONTANT_LIQUIDATION_3*100/$BUDGET_VOTE_3;
                  $taux_ordonnancement_3=$MONTANT_ORDONNANCEMENT_3*100/$BUDGET_VOTE_3;
                  $taux_paiement_3=$MONTANT_PAIEMENT_3*100/$BUDGET_VOTE_3;
                  $taux_decaissement_3=$MONTANT_DECAISSEMENT_3*100/$BUDGET_VOTE_3;

      
                  $sheet->setCellValue('A' . $rows3, '    ');
                  $sheet->setCellValue('B' . $rows3, '    '.$key_respo->RESPONSABLE)->getStyle('B'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('C' . $rows3, ' ');
                  $sheet->setCellValue('D' . $rows3, ' ');
                  $sheet->setCellValue('E' . $rows3, $BUDGET_VOTE_3)->getStyle('E'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('F' . $rows3, $MONTANT_TRANSFERT_3)->getStyle('F'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('G' . $rows3, $CREDIT_APRES_TRANSFERT_3)->getStyle('G'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('H' . $rows3, $MONTANT_ENGAGE_3)->getStyle('H'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('I' . $rows3, $MONTANT_JURIDIQUE_3)->getStyle('I'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('J' . $rows3, $MONTANT_LIQUIDATION_3)->getStyle('J'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('K' . $rows3, $MONTANT_ORDONNANCEMENT_3)->getStyle('K'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('L' . $rows3, $MONTANT_PAIEMENT_3)->getStyle('L'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('M' . $rows3, $MONTANT_DECAISSEMENT_3)->getStyle('M'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('N' . $rows3, $ecart_engage_3)->getStyle('N'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('O' . $rows3, $ecart_juridique_3)->getStyle('O'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('P' . $rows3, $ecart_liquidation_3)->getStyle('P'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('Q' . $rows3, $ecart_ordonnancement_3)->getStyle('Q'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('R' . $rows3, $ecart_paiement_3)->getStyle('R'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('S' . $rows3, $ecart_decaissemen_3)->getStyle('S'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('T' . $rows3, $taux_engage_3)->getStyle('T'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('U' . $rows3, $taux_juridique_3)->getStyle('U'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('V' . $rows3, $taux_liquidation_3)->getStyle('V'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('W' . $rows3, $taux_ordonnancement_3)->getStyle('W'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('X' . $rows3, $taux_paiement_3)->getStyle('X'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('Y' . $rows3, $taux_decaissement_3)->getStyle('Y'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('Z' . $rows3, $QUANTITE_REALISE_3)->getStyle('Z'.$rows3)->getFont()->setBold(true);
                  $sheet->setCellValue('AA' . $rows3, $ecart_physique_3)->getStyle('AA'.$rows3)->getFont()->setBold(true);
                  $rows3++;
                  

                  //Activités avec leurs lignes budgétaires
                  $get_activ = $this->getBindParms('PTBA_ID,ACTIVITES,RESULTATS_ATTENDUS,CODE_NOMENCLATURE_BUDGETAIRE,UNITE', 'ptba', 'ptba.RESPONSABLE LIKE "%'.$key_respo->RESPONSABLE.'%" '.$criteres.'','PTBA_ID ASC');

                  $get_activ=str_replace('\"', '"', $get_activ);
                  $activite = $this->ModelPs->getRequete($callpsreq, $get_activ);


                  if (!empty($activite)) 
                  {
                    $rows4=$rows3+1;

                    foreach ($activite as $key_activ) 
                    {
                      if($TRIMESTRE_ID==1)
                      {
                        $montant_total_4="SUM(T1) AS total,SUM(QT1) as qte_total";  
                      }
                      else if ($TRIMESTRE_ID==2)
                      {
                        $montant_total_4="SUM(T2) AS total,SUM(QT2) as qte_total";
                      }
                      else if ($TRIMESTRE_ID==3)
                      {
                        $montant_total_4="SUM(T3) AS total,SUM(QT3) as qte_total";
                      }
                      else if ($TRIMESTRE_ID==4)
                      {
                        $montant_total_4="SUM(T4) AS total,SUM(QT4) as qte_total";
                      }else{
                        $montant_total_4="SUM(PROGRAMMATION_FINANCIERE_BIF) AS total,SUM(QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE) as qte_total";
                      }

                      $params_activ_4=$this->getBindParms($montant_total_4,'ptba','ptba.PTBA_ID ='.$key_activ->PTBA_ID.' '.$criteres.'','PTBA_ID ASC');
                      $params_activ_4=str_replace('\"','"',$params_activ_4);
                      $total_vote_4=$this->ModelPs->getRequeteOne($callpsreq,$params_activ_4);
                      $BUDGET_VOTE_4=intval($total_vote_4['total']);
                      $BUDGET_VOTE_4=!empty($BUDGET_VOTE_4) ? $BUDGET_VOTE_4 : '1';
                      $QUANTITE_VOTE_4=intval($total_vote_4['qte_total']);

                      //récupération des montants à  afficher
                      $params_infos_4=$this->getBindParms('SUM(exec.TRANSFERTS_CREDITS) AS MONTANT_TRANSFERT,SUM(exec.ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(exec.ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(exec.LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(exec.DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(exec.ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(exec.CREDIT_APRES_TRANSFERT) AS CREDIT_APRES_TRANSFERT,SUM(exec.PAIEMENT) AS PAIEMENT','execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_new exec ON racc.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID','ptba.PTBA_ID ='.$key_activ->PTBA_ID.' '.$criteres.$critere_tranche.$critere_anne.'','ptba.PTBA_ID ASC');

                      $params_infos_4=str_replace('\"','"',$params_infos_4);
                      $infos_sup_4=$this->ModelPs->getRequeteOne($callpsreq,$params_infos_4);

                      ///recuperer le montant,qte realise par trimestre
                      $params_qte_realise_4=$this->getBindParms('SUM(racc.MONTANT_RACCROCHE) resultat_realise,SUM(racc.QTE_RACCROCHE) QTE_REALISE','execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID','ptba.PTBA_ID ='.$key_activ->PTBA_ID.' '.$criteres.$critere_tranche.$critere_anne.'','ptba.PTBA_ID ASC');
                      $params_qte_realise_4=str_replace('\"','"',$params_qte_realise_4);
                      $qte_realise_4=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_4);

                      $RESULTAT_REALISE_4=!empty($qte_realise_4['resultat_realise']) ? $qte_realise_4['resultat_realise'] : '0';
                      $MONTANT_TRANSFERT_4=!empty($infos_sup_4['MONTANT_TRANSFERT']) ? $infos_sup_4['MONTANT_TRANSFERT'] : '0';
                      $CREDIT_APRES_TRANSFERT_4=!empty($infos_sup_4['CREDIT_APRES_TRANSFERT']) ? $infos_sup_4['CREDIT_APRES_TRANSFERT'] :'0';
                      $MONTANT_ENGAGE_4=!empty($infos_sup_4['MONTANT_ENGAGE']) ? $infos_sup_4['MONTANT_ENGAGE'] : '0';
                      $MONTANT_JURIDIQUE_4=!empty($infos_sup_4['MONTANT_JURIDIQUE']) ? $infos_sup_4['MONTANT_JURIDIQUE'] : '0';
                      $MONTANT_LIQUIDATION_4=!empty($infos_sup_4['MONTANT_LIQUIDATION']) ? $infos_sup_4['MONTANT_LIQUIDATION'] : '0';
                      $MONTANT_ORDONNANCEMENT_4=!empty($infos_sup_4['MONTANT_ORDONNANCEMENT']) ? $infos_sup_4['MONTANT_ORDONNANCEMENT'] : '0';
                      $MONTANT_PAIEMENT_4=!empty($infos_sup_4['PAIEMENT']) ? $infos_sup_4['PAIEMENT'] : '0';
                      $MONTANT_DECAISSEMENT_4=!empty($infos_sup_4['MONTANT_DECAISSEMENT']) ? $infos_sup_4['MONTANT_DECAISSEMENT'] : '0';
                      $QUANTITE_REALISE_4=!empty($qte_realise_4['QTE_REALISE']) ? $qte_realise_4['QTE_REALISE'] : '0';
                      $ecart_engage_4=$BUDGET_VOTE_4-$MONTANT_ENGAGE_4;
                      $ecart_juridique_4=$BUDGET_VOTE_4-$MONTANT_JURIDIQUE_4;
                      $ecart_liquidation_4=$BUDGET_VOTE_4-$MONTANT_LIQUIDATION_4;
                      $ecart_ordonnancement_4=$BUDGET_VOTE_4-$MONTANT_ORDONNANCEMENT_4;
                      $ecart_paiement_4=$BUDGET_VOTE_4-$MONTANT_PAIEMENT_4;
                      $ecart_decaissemen_4=$BUDGET_VOTE_4-$MONTANT_DECAISSEMENT_4;
                      $ecart_physique_4=$QUANTITE_VOTE_4-$QUANTITE_REALISE_4;
                      $taux_engage_4=$MONTANT_ENGAGE_4*100/$BUDGET_VOTE_4;
                      $taux_juridique_4=$MONTANT_JURIDIQUE_4*100/$BUDGET_VOTE_4;
                      $taux_liquidation_4=$MONTANT_LIQUIDATION_4*100/$BUDGET_VOTE_4;
                      $taux_ordonnancement_4=$MONTANT_ORDONNANCEMENT_4*100/$BUDGET_VOTE_4;
                      $taux_paiement_4=$MONTANT_PAIEMENT_4*100/$BUDGET_VOTE_4;
                      $taux_decaissement_4=$MONTANT_DECAISSEMENT_4*100/$BUDGET_VOTE_4;

      
                      $sheet->setCellValue('A' . $rows4, $key_activ->CODE_NOMENCLATURE_BUDGETAIRE);
                      $sheet->setCellValue('B' . $rows4, $key_activ->ACTIVITES);
                      $sheet->setCellValue('C' . $rows4, $key_activ->RESULTATS_ATTENDUS);
                      $sheet->setCellValue('D' . $rows4, $key_activ->UNITE);
                      $sheet->setCellValue('E' . $rows4, $BUDGET_VOTE_4);
                      $sheet->setCellValue('F' . $rows4, $MONTANT_TRANSFERT_4);
                      $sheet->setCellValue('G' . $rows4, $CREDIT_APRES_TRANSFERT_4);
                      $sheet->setCellValue('H' . $rows4, $MONTANT_ENGAGE_4);
                      $sheet->setCellValue('I' . $rows4, $MONTANT_JURIDIQUE_4);
                      $sheet->setCellValue('J' . $rows4, $MONTANT_LIQUIDATION_4);
                      $sheet->setCellValue('K' . $rows4, $MONTANT_ORDONNANCEMENT_4);
                      $sheet->setCellValue('L' . $rows4, $MONTANT_PAIEMENT_4);
                      $sheet->setCellValue('M' . $rows4, $MONTANT_DECAISSEMENT_4);
                      $sheet->setCellValue('N' . $rows4, $ecart_engage_4);
                      $sheet->setCellValue('O' . $rows4, $ecart_juridique_4);
                      $sheet->setCellValue('P' . $rows4, $ecart_liquidation_4);
                      $sheet->setCellValue('Q' . $rows4, $ecart_ordonnancement_4);
                      $sheet->setCellValue('R' . $rows4, $ecart_paiement_4);
                      $sheet->setCellValue('S' . $rows4, $ecart_decaissemen_4);
                      $sheet->setCellValue('T' . $rows4, $taux_engage_4);
                      $sheet->setCellValue('U' . $rows4, $taux_juridique_4);
                      $sheet->setCellValue('V' . $rows4, $taux_liquidation_4);
                      $sheet->setCellValue('W' . $rows4, $taux_ordonnancement_4);
                      $sheet->setCellValue('X' . $rows4, $taux_paiement_4);
                      $sheet->setCellValue('Y' . $rows4, $taux_decaissement_4);
                      $sheet->setCellValue('Z' . $rows4, $QUANTITE_REALISE_4);
                      $sheet->setCellValue('AA' . $rows4, $ecart_physique_4);
                      $rows4++;
                      
                    }
                    $rows3=$rows4+1;
                  }
                }
                $rows2=$rows3+1;
              }
            }
            $rows1=$rows2+1;
          }
        }
        $rows=$rows1+1;
      }
    }
    
    
    // Set the width of column A to 30
    $sheet->getColumnDimension('A')->setWidth(30);
    $sheet->getColumnDimension('B')->setWidth(45);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(30);
    $sheet->getColumnDimension('E')->setWidth(30);
    $sheet->getColumnDimension('F')->setWidth(30);
    $sheet->getColumnDimension('G')->setWidth(30);
    $sheet->getColumnDimension('H')->setWidth(30);
    $sheet->getColumnDimension('I')->setWidth(30);
    $sheet->getColumnDimension('J')->setWidth(30);
    $sheet->getColumnDimension('K')->setWidth(30);
    $sheet->getColumnDimension('L')->setWidth(30);
    $sheet->getColumnDimension('M')->setWidth(30);
    $sheet->getColumnDimension('N')->setWidth(30);
    $sheet->getColumnDimension('O')->setWidth(30);
    $sheet->getColumnDimension('P')->setWidth(30);
    $sheet->getColumnDimension('Q')->setWidth(30);
    $sheet->getColumnDimension('R')->setWidth(30);
    $sheet->getColumnDimension('S')->setWidth(30);
    $sheet->getColumnDimension('T')->setWidth(30);
    $sheet->getColumnDimension('U')->setWidth(30);
    $sheet->getColumnDimension('V')->setWidth(30);
    $sheet->getColumnDimension('W')->setWidth(30);
    $sheet->getColumnDimension('X')->setWidth(30);
    $sheet->getColumnDimension('Y')->setWidth(30);
    $sheet->getColumnDimension('Z')->setWidth(30);
    $sheet->getColumnDimension('AA')->setWidth(30);
    $writer = new Xlsx($spreadsheet);

    $writer->save('world.xlsx');
    return $this->response->download('world.xlsx', null)->setFileName('rapport canevas un suivi evaluation.xlsx');
    return redirect('ihm/rapport_classification_administrative');
  }

}

?>