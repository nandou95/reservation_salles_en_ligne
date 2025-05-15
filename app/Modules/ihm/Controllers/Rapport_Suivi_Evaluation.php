<?php 
/**
 * christa
 * le 11/09/2023
 * rapport de suivi evaluation
 * christa@mediabox.bi
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
use Dompdf\Options;
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M');
class Rapport_Suivi_Evaluation extends BaseController
{
  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }
  function index()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_SUIVI_EVALUATION')!=1)
    {
     return redirect('Login_Ptba/homepage');
    }
    $data = $this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $anne_id=$this->get_annee_budgetaire();
    //$anne_id=2;
    //structure responsable
    $bindparams = $this->getBindParms('STRUTURE_RESPONSABLE_TACHE_ID,DESC_STRUTURE_RESPONSABLE_TACHE','struture_responsable_tache','1','DESC_STRUTURE_RESPONSABLE_TACHE ASC');
    $bindparams=str_replace('\"','"',$bindparams);
    $data['structure_responsable'] = $this->ModelPs->getRequete($psgetrequete,$bindparams);
    $data['RESPO']='';
    //tranche
    $bindparams_tr=$this->getBindParms('TRIMESTRE_ID,DESC_TRIMESTRE','trimestre','1','TRIMESTRE_ID');
    $data['tranches'] = $this->ModelPs->getRequete($psgetrequete, $bindparams_tr);
    //annee budgetaire: mettre par défaut année en cours
    $bindparams_anne=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$anne_id,'ANNEE_BUDGETAIRE_ID');
    $data['ANNEE_BUDGETAIRE_ID']=$anne_id;
    $data['anne_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $bindparams_anne);
    return view('App\Modules\ihm\Views\Rapport_Suivi_Evaluation_View',$data); 
  }

  //récupération des codes budgetaires par rapport à l action selectionné
  function get_imputation($RESPONSABLE=0,$PROGRAMME_ID=0,$ACTION_ID=0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_SUIVI_EVALUATION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_imputation = $this->getBindParms('DISTINCT pt.CODE_NOMENCLATURE_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache pt JOIN inst_institutions_ligne_budgetaire ligne ON pt.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','pt.STRUTURE_RESPONSABLE_TACHE_ID='.$RESPONSABLE.' AND pt.PROGRAMME_ID='.$PROGRAMME_ID.' AND pt.ACTION_ID='.$ACTION_ID.'','ligne.CODE_NOMENCLATURE_BUDGETAIRE ASC');
    $imputation = $this->ModelPs->getRequete($callpsreq,$get_imputation);
    //print_r($imputation);exit(); 
   
    $html='<option value="">'.lang('messages_lang.labelle_selecte').'</option>';
    foreach ($imputation as $key)
    {
      $html.='<option value="'.$key->CODE_NOMENCLATURE_BUDGETAIRE_ID.'">'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'  '.$key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'</option>';
    }
    $output = array(
      "imputation" => $html
    );
    return $this->response->setJSON($output);
  }

  //récuperation du programme par rapport au sous tutelle
  function get_programme($RESPONSABLE_ID=0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_SUIVI_EVALUATION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_program=$this->getBindParms('DISTINCT prog.PROGRAMME_ID,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$RESPONSABLE_ID.'','prog.INTITULE_PROGRAMME ASC');
    $get_program=str_replace('\"','"',$get_program);
    $programmes = $this->ModelPs->getRequete($callpsreq,$get_program);
    $html='<option value="">Sélectionner</option>';
    foreach ($programmes as $key)
    {
      $html.='<option value="'.$key->PROGRAMME_ID.'">'.$key->INTITULE_PROGRAMME.'</option>';
    }
    $output = array(
      "programs" => $html,
    );
    return $this->response->setJSON($output);
  }

  //récuperation des actions par rapport au programme
  function get_action($PROGRAMME_ID=0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_SUIVI_EVALUATION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_action = $this->getBindParms('act.ACTION_ID,act.CODE_ACTION,act.LIBELLE_ACTION','inst_institutions_actions act ','PROGRAMME_ID='.$PROGRAMME_ID.'','act.LIBELLE_ACTION  ASC');
    $get_action=str_replace('\"', "", $get_action);
    $actions = $this->ModelPs->getRequete($callpsreq, $get_action);
    $html='<option value="">Sélectionner</option>';
    foreach ($actions as $key)
    {
      $html.='<option value="'.$key->ACTION_ID.'">'.trim($key->LIBELLE_ACTION).'</option>';
    }
    $output = array(
      "actions" => $html,
    );
    return $this->response->setJSON($output);
  }

  //rapport de suivi evaluation sous format de liste
  function liste()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_SUIVI_EVALUATION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $RESPONSABLE=$this->request->getPost('RESPONSABLE');
    $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
    $ACTION_ID=$this->request->getPost('ACTION_ID');
    $CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
    $TRIMESTRE_ID = $this->request->getPost('TRIMESTRE_ID');
    $ANNEE_BUDGETAIRE_ID = $this->request->getPost('ANNEE_BUDGETAIRE_ID');
    $DATE_FIN = $this->request->getPost('DATE_FIN');
    $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';$group='';
    $order_column = array('resp.DESC_STRUTURE_RESPONSABLE_TACHE','prog.INTITULE_PROGRAMME','act.LIBELLE_ACTION','ligne.CODE_NOMENCLATURE_BUDGETAIRE','DESC_TACHE','RESULTAT_ATTENDUS_TACHE',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);
    $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY RESPONSABLE  ASC';
    $search = !empty($_POST['search']['value']) ? (' AND (resp.DESC_STRUTURE_RESPONSABLE_TACHE LIKE "%' . $var_search . '%" OR prog.INTITULE_PROGRAMME LIKE "%' . $var_search . '%" OR act.LIBELLE_ACTION LIKE "%'.$var_search.'%" OR DESC_TACHE LIKE "%'.$var_search.'%")') : '';
    $group=' ';

    $critere_resp="";
    $critere_tranche="";
    $critere_anne="";
    $critere_date="";

    $ann=$this->get_annee_budgetaire();
    
    if (!empty($RESPONSABLE))
    {
      $critere_resp.=' AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$RESPONSABLE.' ';

      if (!empty($PROGRAMME_ID))
      {
        $critere_resp.=' AND prog.PROGRAMME_ID='.$PROGRAMME_ID.' ';

        if (!empty($ACTION_ID))
        {
          $critere_resp.=' AND act.ACTION_ID='.$ACTION_ID.' ';

          if (!empty($CODE_NOMENCLATURE_BUDGETAIRE_ID))
          {
            $critere_resp.=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID.' ';
          }
        }
      }
    }

    //Tranche_id pour les transferts
    $tranch_transf =" ";
    if(!empty($TRIMESTRE_ID))
    {
      $tranch_transf=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    if(!empty($TRIMESTRE_ID))
    {
      $critere_tranche.=" AND exec.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }else{
      $critere_tranche.=" AND exec.TRIMESTRE_ID=5";
    }
    if(!empty($ANNEE_BUDGETAIRE_ID))
    {
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    }else{
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ann;
    }

    //filtre pour date debut et date fin
    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date=" AND DATE_BON_ENGAGEMENT >=".$DATE_DEBUT;
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date=" AND DATE_BON_ENGAGEMENT <= ".$DATE_FIN;
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date=" AND DATE_BON_ENGAGEMENT BETWEEN ".$DATE_DEBUT." AND ".$DATE_FIN;
    }

      // Condition pour la requête principale
    $conditions=$critere_resp.' '.$search.' '.$group.' '.$order_by.' '.$limit;
      // Condition pour la requête de filtre
    $conditionsfilter=$critere_resp.' '.$search.' '.$group;

    $requetedebase="SELECT PTBA_TACHE_ID,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE,DESC_TACHE,RESULTAT_ATTENDUS_TACHE,resp.DESC_STRUTURE_RESPONSABLE_TACHE FROM ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN struture_responsable_tache resp ON resp.STRUTURE_RESPONSABLE_TACHE_ID =ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID WHERE 1";

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';

    $requetedebases = $requetedebase.' '.$conditions;
    // print_r($requetedebases);die();
    $requetedebasefilter = $requetedebase.' '.$conditionsfilter;
    $query_secondaire = "CALL `getTable`('".$requetedebases."');";
    $fetch_infos = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach ($fetch_infos as $row)
    {
      
      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(BUDGET_T1) AS total,SUM(QT1) as qte_total";  
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(BUDGET_T2) AS total,SUM(QT2) as qte_total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(BUDGET_T3) AS total,SUM(QT3) as qte_total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(BUDGET_T4) AS total,SUM(QT4) as qte_total";
      }else{
        $montant_total="SUM(BUDGET_ANNUEL) AS total,SUM(Q_TOTAL) as qte_total";
      }

      $params_activ=$this->getBindParms($montant_total,'ptba_tache','PTBA_TACHE_ID="'.$row->PTBA_TACHE_ID.'"','PTBA_TACHE_ID ASC');
      $params_activ=str_replace('\"','"',$params_activ);
      $total_vote=$this->ModelPs->getRequeteOne($callpsreq,$params_activ);
      $BUDGET_VOTE=intval($total_vote['total']);
      $BUDGET_VOTE=!empty($BUDGET_VOTE) ? $BUDGET_VOTE : '1';
      $QUANTITE_VOTE=intval($total_vote['qte_total']);

      //récupération des montants à  afficher
      $params_infos=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ebet.PTBA_TACHE_ID='.$row->PTBA_TACHE_ID.$critere_tranche.$critere_anne.$critere_date,'1');

      $params_infos=str_replace('\"','"',$params_infos);
      $infos_sup=$this->ModelPs->getRequeteOne($callpsreq,$params_infos);

      //Montant transferé
      $param_mont_trans = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$row->PTBA_TACHE_ID.' '.$tranch_transf,'1');
      $param_mont_trans=str_replace('\"','"',$param_mont_trans);
      $mont_transf=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans);
      $MONTANT_TRANSFERT=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.PTBA_TACHE_ID='.$row->PTBA_TACHE_ID.' '.$tranch_transf,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep);
      $MONTANT_RECEPTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $TRANSFERTS_CREDITS_RESTE=(floatval($MONTANT_TRANSFERT) - floatval($MONTANT_RECEPTION));

      if($TRANSFERTS_CREDITS_RESTE >= 0)
      {
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE;
      }
      else{
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($BUDGET_VOTE) - floatval($MONTANT_TRANSFERT)) + floatval($MONTANT_RECEPTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['PTBA_TACHE_ID']==$mont_recep['PTBA_TACHE_ID'])
      {
        $TRANSFERTS_CREDITS = $MONTANT_TRANSFERT;
        $CREDIT_APRES_TRANSFERT = floatval($BUDGET_VOTE);
      }

      ///recuperer le montant,qte realise par trimestre
      $params_qte_realise=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.PTBA_TACHE_ID='.$row->PTBA_TACHE_ID.$critere_tranche,'1');
      $params_qte_realise=str_replace('\"','"',$params_qte_realise);
      $qte_realise=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise);

      $RESULTAT_REALISE=!empty($qte_realise['resultat_realise']) ? $qte_realise['resultat_realise'] : '0';
      $MONTANT_TRANSFERT=$TRANSFERTS_CREDITS;
      $CREDIT_APRES_TRANSFERT=$CREDIT_APRES_TRANSFERT;
      $MONTANT_ENGAGE=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $MONTANT_PAIEMENT=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $MONTANT_TITRE_DECAISSEMENT=!empty($infos_sup['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup['MONTANT_TITRE_DECAISSEMENT'] : '0';
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
      $sub_array = array();
      
      if(strlen($row->DESC_STRUTURE_RESPONSABLE_TACHE) > 8)
      {
        $RESPONSABLE =  mb_substr($row->DESC_STRUTURE_RESPONSABLE_TACHE, 0, 7).'...<a class="btn-sm" title="'.$row->DESC_STRUTURE_RESPONSABLE_TACHE.'"><i class="fa fa-eye"></i></a>';
      }
      else{
        $RESPONSABLE =  !empty($row->DESC_STRUTURE_RESPONSABLE_TACHE) ? $row->DESC_STRUTURE_RESPONSABLE_TACHE : 'N/A';
      }
      if(strlen($row->INTITULE_PROGRAMME) > 8)
      {
        $INTITULE_PROGRAMME =  mb_substr($row->INTITULE_PROGRAMME, 0, 7).'...<a class="btn-sm" title="'.$row->INTITULE_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
      }else
      {
        $INTITULE_PROGRAMME =  !empty($row->INTITULE_PROGRAMME) ? $row->INTITULE_PROGRAMME : 'N/A';
      }

      if(strlen($row->LIBELLE_ACTION) > 8)
      {
        $LIBELLE_ACTION =  mb_substr($row->LIBELLE_ACTION, 0, 7).'...<a class="btn-sm" title="'.$row->LIBELLE_ACTION.'"><i class="fa fa-eye"></i></a>';
      }
      else{
        $LIBELLE_ACTION =  !empty($row->LIBELLE_ACTION) ? $row->LIBELLE_ACTION : 'N/A';
      }

      if(strlen($row->DESC_TACHE) > 8)
      {
        $DESC_TACHE =  mb_substr($row->DESC_TACHE, 0, 7).'...<a class="btn-sm" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>';
      }
      else{
        $DESC_TACHE =  !empty($row->DESC_TACHE) ? $row->DESC_TACHE : 'N/A';
      }
      if(strlen($row->RESULTAT_ATTENDUS_TACHE) > 8)
      {
        $RESULTAT_ATTENDUS_TACHE =  mb_substr($row->RESULTAT_ATTENDUS_TACHE, 0, 7).'...<a class="btn-sm" title="'.$row->RESULTAT_ATTENDUS_TACHE.'"><i class="fa fa-eye"></i></a>';
      }
      else{
        $RESULTAT_ATTENDUS_TACHE =  !empty($row->RESULTAT_ATTENDUS_TACHE) ? $row->RESULTAT_ATTENDUS_TACHE : 'N/A';
      }
      
      $TRANSFERTS_CREDITS=$TRANSFERTS_CREDITS;
      $CREDIT_APRES_TRANSFERT=$CREDIT_APRES_TRANSFERT;
      $ENG_BUDGETAIRE=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $JURIDIQUE=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $LIQUIDATION=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $ORDONNANCEMENT=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $PAIEMENT=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $DECAISSEMENT=!empty($infos_sup['MONTANT_DECAISSEMENT']) ? $infos_sup['MONTANT_DECAISSEMENT'] :'0';

      $sub_array[] = $RESPONSABLE;
      $sub_array[] = $INTITULE_PROGRAMME;
      $sub_array[] = $LIBELLE_ACTION;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $DESC_TACHE;
      $sub_array[] = $RESULTAT_ATTENDUS_TACHE;
      $sub_array[] = number_format($BUDGET_VOTE,0,","," ");
      $sub_array[] = number_format($TRANSFERTS_CREDITS,0,","," ");
      $sub_array[] = number_format($CREDIT_APRES_TRANSFERT,0,","," ");
      $sub_array[] = number_format($ENG_BUDGETAIRE,0,","," ");
      $sub_array[] = number_format($JURIDIQUE,0,","," ");
      $sub_array[] = number_format($LIQUIDATION,0,","," ");
      $sub_array[] = number_format($ORDONNANCEMENT,0,","," ");
      $sub_array[] = number_format($PAIEMENT,0,","," ");
      $sub_array[] = number_format($DECAISSEMENT,0,","," ");
      $sub_array[] = number_format($ecart_engage,0,","," ");
      $sub_array[] = number_format($ecart_juridique,0,","," ");
      $sub_array[] = number_format($ecart_ordonnancement,0,","," ");
      $sub_array[] = number_format($ecart_paiement,0,","," ");
      $sub_array[] = number_format($ecart_decaissement,0,","," ");
      $sub_array[] = number_format($ecart_physique,0,","," ");
      $sub_array[] = number_format($taux_engage,0,","," ");
      $sub_array[] = number_format($taux_juridique,0,","," ");
      $sub_array[] = number_format($taux_liquidation,0,","," ");
      $sub_array[] = number_format($taux_ordonnancement,0,","," ");
      $sub_array[] = number_format($taux_paiement,0,","," ");
      $sub_array[] = number_format($taux_decaissement,0,","," ");
      $sub_array[] = $QUANTITE_REALISE;
      $sub_array[] = $ecart_physique;
      $data[] = $sub_array;
    }

    $recordsTotal=$this->ModelPs->datatable("CALL `getTable`('".$requetedebase."')");
    $recordsFiltered=$this->ModelPs->datatable(" CALL `getTable`('".$requetedebasefilter."')");
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
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
    // code...
    $db = db_connect();
    // print_r($db->lastQuery);die();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  //function pour exporter le rapport de suivie evaluation dans excel
  function exporter_filtre($RESPONSABLE='',$PROGRAMME_ID='',$ACTION_ID='',$CODE_NOMENCLATURE_BUDGETAIRE_ID='',$TRIMESTRE_ID='',$ANNEE_BUDGETAIRE_ID='',$DATE_DEBUT='',$DATE_FIN='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_SUIVI_EVALUATION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $db = db_connect();
    $critere_resp='';
    $critere_tranche='';
    $critere_imput='';
    $critere_pr='';
    $critere_act='';
    $critere_anne='';
    $critere_date="";
    $critere_date_act='';

    $ann=$this->get_annee_budgetaire();
    //$ann=2;
    
    if ($RESPONSABLE!=0)
    {
      $critere_resp=' AND pt.STRUTURE_RESPONSABLE_TACHE_ID='.$RESPONSABLE.' ';

      if ($PROGRAMME_ID>0)
      {
        $critere_pr=' AND prog.PROGRAMME_ID='.$PROGRAMME_ID.' ';

        if ($ACTION_ID>0)
        {
          $critere_act=' AND act.ACTION_ID='.$ACTION_ID.' ';

          if ($CODE_NOMENCLATURE_BUDGETAIRE_ID>0)
          {
            $critere_imput=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID.' ';
          }
        }
      }
    }

    //Tranche_id pour les transferts
    $tranch_transf =" ";
    if($TRIMESTRE_ID!=5)
    {
      $tranch_transf=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    if($TRIMESTRE_ID!=5)
    {
      $critere_tranche.=" AND exec.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    if($ANNEE_BUDGETAIRE_ID>0)
    {
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    }else{
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ann;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date=' AND DATE_DEMANDE >="'.$DATE_DEBUT.'"';
      $critere_date_act=' AND DATE_BON_ENGAGEMENT >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date=' AND DATE_DEMANDE <= "'.$DATE_FIN.'"';
      $critere_date_act=' AND DATE_BON_ENGAGEMENT <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date=' AND DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
      $critere_date_act=' AND DATE_BON_ENGAGEMENT BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_responsable = $this->getBindParms('DISTINCT pt.STRUTURE_RESPONSABLE_TACHE_ID, resp.DESC_STRUTURE_RESPONSABLE_TACHE AS RESPONSABLE','ptba_tache pt JOIN inst_institutions inst ON pt.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON pt.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON pt.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON pt.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN struture_responsable_tache resp ON resp.STRUTURE_RESPONSABLE_TACHE_ID=pt.STRUTURE_RESPONSABLE_TACHE_ID','1 ' . $critere_resp, 'RESPONSABLE ASC ');
    $get_responsable=str_replace('\"', '"', $get_responsable);
    $responsable = $this->ModelPs->getRequete($callpsreq, $get_responsable);

    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
    $annee_budgetaire = $this->ModelPs->getRequeteOne($callpsreq, $get_ann_budg);
    $annee_dexcr=str_replace('-','/',$annee_budgetaire['ANNEE_DESCRIPTION']);

    $p_deb = '01/07/'.substr($annee_dexcr, 0, 4);
    $p_fin = date("d/m/Y");
    if($TRIMESTRE_ID==1){
      $p_deb = '01/07/'.substr($annee_dexcr, 0, 4);
      $p_fin = '30/09/'.substr($annee_dexcr, 0, 4);
    }
    if($TRIMESTRE_ID==2){
      $p_deb = '01/10/'.substr($annee_dexcr, 0, 4);
      $p_fin = '31/12/'.substr($annee_dexcr, 0, 4);
    }
    if($TRIMESTRE_ID==3){
      $p_deb = '01/01/'.substr($annee_dexcr, 0, 4);
      $p_fin = '31/03/'.substr($annee_dexcr, 0, 4);
    }
    if($TRIMESTRE_ID==4){
      $p_deb = '01/04/'.substr($annee_dexcr, 0, 4);
      $p_fin = '30/06/'.substr($annee_dexcr, 0, 4);
    }

    $periode_debut = (!empty($DATE_DEBUT)) ? date('d/m/Y',strtotime($DATE_DEBUT)) : $p_deb ;
    $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;

    if($ann != $ANNEE_BUDGETAIRE_ID)
    {
      $p_fin = '30/06/'.substr($annee_dexcr,5);
      $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A3', 'CIRCUIT DES DEPENSES');
    $sheet->setCellValue('A4', 'SUIVI EVALUATION');
    $sheet->setCellValue('A5', 'EXERCICE '.$annee_dexcr.', N° BUDGET 0          Période du '.$periode_debut.' au '.$periode_fin.'');
    $sheet->setCellValue('A10', 'IMPUTATION');
    $sheet->setCellValue('B10', 'TÂCHES PREVUES');
    $sheet->setCellValue('C10', 'RESULTATS ATTENDUS');
    $sheet->setCellValue('D10', 'BUDGET VOTE');       
    $sheet->setCellValue('E10', 'TRANSFERTS CREDITS');       
    $sheet->setCellValue('F10', 'CREDIT APRES TRANSFERT');       
    $sheet->setCellValue('G10', 'ENGAGEMENT BUDGETAIRE');       
    $sheet->setCellValue('H10', 'ENGAGEMENT JURIDIQUE');       
    $sheet->setCellValue('I10', 'LIQUIDATION');       
    $sheet->setCellValue('J10', 'ORDONNANCEMENT');       
    $sheet->setCellValue('K10', 'PAIEMENT');       
    $sheet->setCellValue('L10', 'DECAISSEMENT');         
    $sheet->setCellValue('M10', 'ECART BUDGETAIRE');       
    $sheet->setCellValue('N10', 'ECART JURIDIQUE');       
    $sheet->setCellValue('O10', 'ECART LIQUIDATION');       
    $sheet->setCellValue('P10', 'ECART PAIEMENT');       
    $sheet->setCellValue('Q10', 'ECART ORDONNANCEMENT');       
    $sheet->setCellValue('R10', 'ECART DECAISSEMENT');       
    $sheet->setCellValue('S10', 'TAUX BUDGETAIRE');       
    $sheet->setCellValue('T10', 'TAUX JURIDIQUE');       
    $sheet->setCellValue('U10', 'TAUX LIQUIDATION');       
    $sheet->setCellValue('V10', 'TAUX ORDONNANCEMENT');       
    $sheet->setCellValue('W10', 'TAUX PAIEMENT');       
    $sheet->setCellValue('X10', 'TAUX DECAISSEMENT');       
    $sheet->setCellValue('Y10', 'RESULTATS REALISES');       
    $sheet->setCellValue('Z10', 'ECART PHYSIQUE');     
    $rows = 11;

    //boucle pour les institutions
    foreach ($responsable as $key)
    {
      $params_infos=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache pt ON pt.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND pt.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' ','1');
      $params_infos=str_replace('\"','"',$params_infos);
      $infos_sup=$this->ModelPs->getRequeteOne($callpsreq,$params_infos);

      ///recuperer le montant,qte voté par trimestre
      $montant_total="SUM(BUDGET_ANNUEL) AS total,SUM(Q_TOTAL) as qte_total";
      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(BUDGET_T1) AS total,SUM(QT1) as qte_total";  
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(BUDGET_T2) AS total,SUM(QT2) as qte_total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(BUDGET_T3) AS total,SUM(QT3) as qte_total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(BUDGET_T4) AS total,SUM(QT4) as qte_total";
      }

      $params_div=$this->getBindParms($montant_total,'ptba_tache','STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' ','PTBA_TACHE_ID ASC');
      $params_div=str_replace('\"','"',$params_div);
      $total_vote=$this->ModelPs->getRequeteOne($callpsreq,$params_div);
      $BUDGET_VOTE=intval($total_vote['total']);
      $BUDGET_VOTE=!empty($BUDGET_VOTE) ? $BUDGET_VOTE : '1';
      $QUANTITE_VOTE=intval($total_vote['qte_total']);

      //Montant transferé
      $param_mont_trans = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.STRUTURE_RESPONSABLE_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' '.$tranch_transf,'1');
      $param_mont_trans=str_replace('\"','"',$param_mont_trans);
      $mont_transf=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans);
      $MONTANT_TRANSFERT=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.STRUTURE_RESPONSABLE_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' '.$tranch_transf,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep);
      $MONTANT_RECEPTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $TRANSFERTS_CREDITS_RESTE=(floatval($MONTANT_TRANSFERT) - floatval($MONTANT_RECEPTION));

      if($TRANSFERTS_CREDITS_RESTE >= 0)
      {
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE;
      }
      else{
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($BUDGET_VOTE) - floatval($MONTANT_TRANSFERT)) + floatval($MONTANT_RECEPTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['STRUTURE_RESPONSABLE_TACHE_ID']==$mont_recep['STRUTURE_RESPONSABLE_TACHE_ID'])
      {
        $TRANSFERTS_CREDITS = $MONTANT_TRANSFERT;
        $CREDIT_APRES_TRANSFERT = floatval($BUDGET_VOTE);
      }

      ///recuperer le montant,qte realise par trimestre
      $params_qte_realise=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' '.$critere_tranche,'1');
      $params_qte_realise=str_replace('\"','"',$params_qte_realise);
      $qte_realise=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise);
      $RESULTAT_REALISE=!empty($qte_realise['resultat_realise']) ? $qte_realise['resultat_realise'] : '0';
      $MONTANT_ENGAGE=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $MONTANT_PAIEMENT=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $MONTANT_TITRE_DECAISSEMENT=!empty($infos_sup['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup['MONTANT_TITRE_DECAISSEMENT'] : '0';
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
      $sheet->setCellValue('A' . $rows, $key->RESPONSABLE);
      $sheet->setCellValue('B' . $rows, '');
      $sheet->setCellValue('C' . $rows, '');
      $sheet->setCellValue('D' . $rows, $BUDGET_VOTE);
      $sheet->setCellValue('E' . $rows, $TRANSFERTS_CREDITS);
      $sheet->setCellValue('F' . $rows, $CREDIT_APRES_TRANSFERT);
      $sheet->setCellValue('G' . $rows, $MONTANT_ENGAGE);
      $sheet->setCellValue('H' . $rows, $MONTANT_JURIDIQUE);
      $sheet->setCellValue('I' . $rows, $MONTANT_LIQUIDATION);
      $sheet->setCellValue('J' . $rows, $MONTANT_ORDONNANCEMENT);
      $sheet->setCellValue('K' . $rows, $MONTANT_PAIEMENT);
      $sheet->setCellValue('L' . $rows, $MONTANT_TITRE_DECAISSEMENT);
      $sheet->setCellValue('M' . $rows, $ecart_engage);
      $sheet->setCellValue('N' . $rows, $ecart_juridique);
      $sheet->setCellValue('O' . $rows, $ecart_liquidation);
      $sheet->setCellValue('P' . $rows, $ecart_ordonnancement);
      $sheet->setCellValue('Q' . $rows, $ecart_paiement);
      $sheet->setCellValue('R' . $rows, $ecart_decaissement);

      $sheet->setCellValue('S' . $rows, $taux_engage);
      $sheet->setCellValue('T' . $rows, $taux_juridique);
      $sheet->setCellValue('U' . $rows, $taux_liquidation);
      $sheet->setCellValue('V' . $rows, $taux_ordonnancement);
      $sheet->setCellValue('W' . $rows, $taux_paiement);
      $sheet->setCellValue('X' . $rows, $taux_decaissement);
      $sheet->setCellValue('Y' . $rows, $RESULTAT_REALISE);
      $sheet->setCellValue('Z' . $rows, $ecart_physique);
      $sheet->setCellValue('AA' .$rows, '');

      //export par rapport au programme

      $get_program=$this->getBindParms('DISTINCT prog.PROGRAMME_ID,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.''.$critere_pr,'prog.CODE_PROGRAMME ASC');
      $get_program=str_replace('\"','"',$get_program);
      $programmes= $this->ModelPs->getRequete($callpsreq,$get_program);

      foreach ($programmes as $key_program)
      {
        $rows++;
        $params_infos_pgm=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache pt ON pt.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND pt.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND pt.PROGRAMME_ID='.$key_program->PROGRAMME_ID.'','1');
        $params_infos_pgm=str_replace('\"','"',$params_infos_pgm);
        $infos_sup_pgm=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_pgm);
        $params_total_pgm=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.'','1');
        $params_total_pgm=str_replace('\"','"',$params_total_pgm);
        $total_pgm=$this->ModelPs->getRequeteOne($callpsreq,$params_total_pgm);
        $BUDGET_VOTE_PGM=intval($total_pgm['total']);
        $BUDGET_VOTE_PGM=!empty($BUDGET_VOTE_PGM) ? $BUDGET_VOTE_PGM : '1';
        $QUANTITE_VOTE_PGM=intval($total_pgm['qte_total']);


        //Montant transferé
        $param_mont_trans_prg = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$tranch_transf,'1');
        $param_mont_trans_prg=str_replace('\"','"',$param_mont_trans_prg);
        $mont_transf_prg=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_prg);
        $MONTANT_TRANSFERT_PGM=floatval($mont_transf_prg['MONTANT_TRANSFERT']);

        //Montant receptionné
        $param_mont_recep_prg = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$tranch_transf,'1');
        $param_mont_recep_prg=str_replace('\"','"',$param_mont_recep_prg);
        $mont_recep_prg=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_prg);
        $MONTANT_RECEPTION_PGM=floatval($mont_recep_prg['MONTANT_RECEPTION']);

        $TRANSFERTS_CREDITS_RESTE_PGM=(floatval($MONTANT_TRANSFERT_PGM) - floatval($MONTANT_RECEPTION_PGM));

        if($TRANSFERTS_CREDITS_RESTE_PGM >= 0)
        {
          $TRANSFERTS_CREDITS_PGM = $TRANSFERTS_CREDITS_RESTE_PGM;
        }
        else{
          $TRANSFERTS_CREDITS_PGM = $TRANSFERTS_CREDITS_RESTE_PGM*(-1);
        }

        $CREDIT_APRES_TRANSFERT_PGM=(floatval($BUDGET_VOTE_PGM) - floatval($MONTANT_TRANSFERT_PGM)) + floatval($MONTANT_RECEPTION_PGM);

        if($CREDIT_APRES_TRANSFERT_PGM < 0){
          $CREDIT_APRES_TRANSFERT_PGM = $CREDIT_APRES_TRANSFERT_PGM*(-1);
        }

        if($mont_transf_prg['PROGRAMME_ID']==$mont_recep_prg['PROGRAMME_ID'])
        {
          $TRANSFERTS_CREDITS_PGM = $MONTANT_TRANSFERT_PGM;
          $CREDIT_APRES_TRANSFERT_PGM = floatval($BUDGET_VOTE_PGM);
        }


        ///recuperer le montant,qte realise par trimestre
        $params_qte_realise_pgm=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$critere_tranche,'1');
        $params_qte_realise_pgm=str_replace('\"','"',$params_qte_realise_pgm);
        $qte_realise_pgm=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_pgm);
        $RESULTAT_REALISE_PGM=!empty($qte_realise_pgm['resultat_realise']) ? $qte_realise_pgm['resultat_realise'] : '0';
        $MONTANT_ENGAGE_PGM=!empty($infos_sup_pgm['MONTANT_ENGAGE']) ? $infos_sup_pgm['MONTANT_ENGAGE'] : '0';
        $MONTANT_JURIDIQUE_PGM=!empty($infos_sup_pgm['MONTANT_JURIDIQUE']) ? $infos_sup_pgm['MONTANT_JURIDIQUE'] : '0';
        $MONTANT_LIQUIDATION_PGM=!empty($infos_sup_pgm['MONTANT_LIQUIDATION']) ? $infos_sup_pgm['MONTANT_LIQUIDATION'] : '0';
        $MONTANT_ORDONNANCEMENT_PGM=!empty($infos_sup_pgm['MONTANT_ORDONNANCEMENT']) ? $infos_sup_pgm['MONTANT_ORDONNANCEMENT'] : '0';
        $MONTANT_PAIEMENT_PGM=!empty($infos_sup_pgm['PAIEMENT']) ? $infos_sup_pgm['PAIEMENT'] : '0';
        $MONTANT_TITRE_DECAISSEMENT_PGM=!empty($infos_sup_pgm['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_pgm['MONTANT_TITRE_DECAISSEMENT'] : '0';
        $QUANTITE_REALISE_PGM=!empty($qte_realise_pgm['QTE_REALISE']) ? $qte_realise_pgm['QTE_REALISE'] : '0';
        $ecart_engage_pgm=$BUDGET_VOTE_PGM-$MONTANT_ENGAGE_PGM;
        $ecart_juridique_pgm=$BUDGET_VOTE_PGM-$MONTANT_JURIDIQUE_PGM;
        $ecart_liquidation_pgm=$BUDGET_VOTE_PGM-$MONTANT_LIQUIDATION_PGM;
        $ecart_ordonnancement_pgm=$BUDGET_VOTE_PGM-$MONTANT_ORDONNANCEMENT_PGM;
        $ecart_paiement_pgm=$BUDGET_VOTE_PGM-$MONTANT_PAIEMENT_PGM;
        $ecart_decaissement_pgm=$BUDGET_VOTE_PGM-$MONTANT_TITRE_DECAISSEMENT_PGM;
        $ecart_physique_pgm=$QUANTITE_VOTE_PGM-$QUANTITE_REALISE_PGM;
        $taux_engage_pgm=$MONTANT_ENGAGE_PGM*100/$BUDGET_VOTE_PGM;
        $taux_juridique_pgm=$MONTANT_JURIDIQUE_PGM*100/$BUDGET_VOTE_PGM;
        $taux_liquidation_pgm=$MONTANT_LIQUIDATION_PGM*100/$BUDGET_VOTE_PGM;
        $taux_ordonnancement_pgm=$MONTANT_ORDONNANCEMENT_PGM*100/$BUDGET_VOTE_PGM;
        $taux_paiement_pgm=$MONTANT_PAIEMENT_PGM*100/$BUDGET_VOTE_PGM;
        $taux_decaissement_pgm=$MONTANT_TITRE_DECAISSEMENT_PGM*100/$BUDGET_VOTE_PGM;
        $sheet->setCellValue('A' . $rows, '   '.$key_program->INTITULE_PROGRAMME);
        $sheet->setCellValue('B' . $rows, '');
        $sheet->setCellValue('C' . $rows, '');
        $sheet->setCellValue('D' . $rows, $BUDGET_VOTE_PGM);
        $sheet->setCellValue('E' . $rows, $TRANSFERTS_CREDITS_PGM);
        $sheet->setCellValue('F' . $rows, $CREDIT_APRES_TRANSFERT_PGM);
        $sheet->setCellValue('G' . $rows, $MONTANT_ENGAGE_PGM);
        $sheet->setCellValue('H' . $rows, $MONTANT_JURIDIQUE_PGM);
        $sheet->setCellValue('I' . $rows, $MONTANT_LIQUIDATION_PGM);
        $sheet->setCellValue('J' . $rows, $MONTANT_ORDONNANCEMENT_PGM);
        $sheet->setCellValue('K' . $rows, $MONTANT_PAIEMENT_PGM);
        $sheet->setCellValue('L' . $rows, $MONTANT_TITRE_DECAISSEMENT_PGM);
        $sheet->setCellValue('M' . $rows, $ecart_engage_pgm);
        $sheet->setCellValue('N' . $rows, $ecart_juridique_pgm);
        $sheet->setCellValue('O' . $rows, $ecart_liquidation_pgm);
        $sheet->setCellValue('P' . $rows, $ecart_ordonnancement_pgm);
        $sheet->setCellValue('Q' . $rows, $ecart_paiement_pgm);
        $sheet->setCellValue('R' . $rows, $ecart_decaissement_pgm);
        $sheet->setCellValue('S' . $rows, $taux_engage_pgm);
        $sheet->setCellValue('T' . $rows, $taux_juridique_pgm);
        $sheet->setCellValue('U' . $rows, $taux_liquidation_pgm);
        $sheet->setCellValue('V' . $rows, $taux_ordonnancement_pgm);
        $sheet->setCellValue('W' . $rows, $taux_paiement_pgm);
        $sheet->setCellValue('X' . $rows, $taux_decaissement_pgm);
        $sheet->setCellValue('Y' . $rows, $RESULTAT_REALISE_PGM);
        $sheet->setCellValue('Z' . $rows, $ecart_physique_pgm);
        $sheet->setCellValue('AA' .$rows, '');
        ///EXPORT PAR RAPPORT A L ACTION
        $params_actions=$this->getBindParms('DISTINCT act.ACTION_ID,act.CODE_ACTION,act.LIBELLE_ACTION','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND prog.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$critere_act,'act.CODE_ACTION ASC');
        $params_actions=str_replace('\"', '"', $params_actions);
        $actions=$this->ModelPs->getRequete($callpsreq, $params_actions);

        foreach ($actions as $key_action)
        {
          $rows++;
          $params_infos_sup_act=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' ','1');
          $params_infos_sup_act=str_replace('\"', '"', $params_infos_sup_act);
          $infos_sup_action=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_sup_act);
          
          $params_total_action=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' ','PTBA_TACHE_ID ASC');
          $params_total_action=str_replace('\"','"',$params_total_action);
          $total_action=$this->ModelPs->getRequeteOne($callpsreq,$params_total_action);
          $BUDGET_VOTE_ACT=intval($total_action['total']);
          $BUDGET_VOTE_ACT=!empty($BUDGET_VOTE_ACT) ? $BUDGET_VOTE_ACT : '1';
          $QUANTITE_VOTE_ACT=intval($total_action['qte_total']);

          //Montant transferé
          $param_mont_trans_act = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' '.$tranch_transf,'1');
          $param_mont_trans_act=str_replace('\"','"',$param_mont_trans_act);
          $mont_transf_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_act);
          $MONTANT_TRANSFERT_ACT=floatval($mont_transf_act['MONTANT_TRANSFERT']);

          //Montant receptionné
          $param_mont_recep_act = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' '.$tranch_transf,'1');
          $param_mont_recep_act=str_replace('\"','"',$param_mont_recep_act);
          $mont_recep_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_act);
          $MONTANT_RECEPTION_ACT=floatval($mont_recep_act['MONTANT_RECEPTION']);

          $TRANSFERTS_CREDITS_RESTE_ACT=(floatval($MONTANT_TRANSFERT_ACT) - floatval($MONTANT_RECEPTION_ACT));

          if($TRANSFERTS_CREDITS_RESTE_ACT >= 0)
          {
            $TRANSFERTS_CREDITS_ACT = $TRANSFERTS_CREDITS_RESTE_ACT;
          }
          else{
            $TRANSFERTS_CREDITS_ACT = $TRANSFERTS_CREDITS_RESTE_ACT*(-1);
          }

          $CREDIT_APRES_TRANSFERT_ACT=(floatval($BUDGET_VOTE_ACT) - floatval($MONTANT_TRANSFERT_ACT)) + floatval($MONTANT_RECEPTION_ACT);

          if($CREDIT_APRES_TRANSFERT_ACT < 0){
            $CREDIT_APRES_TRANSFERT_ACT = $CREDIT_APRES_TRANSFERT_ACT*(-1);
          }

          if($mont_transf_act['ACTION_ID']==$mont_recep_act['ACTION_ID'])
          {
            $TRANSFERTS_CREDITS_ACT = $MONTANT_TRANSFERT_ACT;
            $CREDIT_APRES_TRANSFERT_ACT = floatval($BUDGET_VOTE_ACT);
          }


          ///recuperer le montant,qte realise par trimestre
          $params_qte_realise_action=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' '.$critere_tranche,'1');
          $params_qte_realise_action=str_replace('\"','"',$params_qte_realise_action);
          $qte_realise_action=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_action);
          $RESULTAT_REALISE_ACT=!empty($qte_realise_action['resultat_realise']) ? $qte_realise_action['resultat_realise'] : '0';
          $MONTANT_ENGAGE_ACT=!empty($infos_sup_action['MONTANT_ENGAGE']) ? $infos_sup_action['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_ACT=!empty($infos_sup_action['MONTANT_JURIDIQUE']) ? $infos_sup_action['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_ACT=!empty($infos_sup_action['MONTANT_LIQUIDATION']) ? $infos_sup_action['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_ACT=!empty($infos_sup_action['MONTANT_ORDONNANCEMENT']) ? $infos_sup_action['MONTANT_ORDONNANCEMENT'] : '0';
          $MONTANT_PAIEMENT_ACT=!empty($infos_sup_action['PAIEMENT']) ? $infos_sup_action['PAIEMENT'] : '0';
          $MONTANT_TITRE_DECAISSEMENT_ACT=!empty($infos_sup_action['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_action['MONTANT_TITRE_DECAISSEMENT'] : '0';
          $QUANTITE_REALISE_ACT=!empty($qte_realise_action['QTE_REALISE']) ? $qte_realise_action['QTE_REALISE'] : '0';

          $ecart_engage_act=$BUDGET_VOTE_ACT-$MONTANT_ENGAGE_ACT;
          $ecart_juridique_act=$BUDGET_VOTE_ACT-$MONTANT_JURIDIQUE_ACT;
          $ecart_liquidation_act=$BUDGET_VOTE_ACT-$MONTANT_LIQUIDATION_ACT;
          $ecart_ordonnancement_act=$BUDGET_VOTE_ACT-$MONTANT_ORDONNANCEMENT_ACT;
          $ecart_paiement_act=$BUDGET_VOTE_ACT-$MONTANT_PAIEMENT_ACT;
          $ecart_decaissement_act=$BUDGET_VOTE_ACT-$MONTANT_TITRE_DECAISSEMENT_ACT;
          $ecart_physique_act=$QUANTITE_VOTE_ACT-$QUANTITE_REALISE_ACT;
          $taux_engage_act=$MONTANT_ENGAGE_ACT*100/$BUDGET_VOTE_ACT;
          $taux_juridique_act=$MONTANT_JURIDIQUE_ACT*100/$BUDGET_VOTE_ACT;
          $taux_liquidation_act=$MONTANT_LIQUIDATION_ACT*100/$BUDGET_VOTE_ACT;
          $taux_ordonnancement_act=$MONTANT_ORDONNANCEMENT_ACT*100/$BUDGET_VOTE_ACT;
          $taux_paiement_act=$MONTANT_PAIEMENT_ACT*100/$BUDGET_VOTE_ACT;
          $taux_decaissement_act=$MONTANT_TITRE_DECAISSEMENT_ACT*100/$BUDGET_VOTE_ACT;

          $sheet->setCellValue('A' . $rows, '       '.$key_action->LIBELLE_ACTION);
          $sheet->setCellValue('B' . $rows, '');
          $sheet->setCellValue('C' . $rows, '');
          $sheet->setCellValue('D' . $rows, $BUDGET_VOTE_ACT);
          $sheet->setCellValue('E' . $rows, $TRANSFERTS_CREDITS_ACT);
          $sheet->setCellValue('F' . $rows, $CREDIT_APRES_TRANSFERT_ACT);
          $sheet->setCellValue('G' . $rows, $MONTANT_ENGAGE_ACT);
          $sheet->setCellValue('H' . $rows, $MONTANT_JURIDIQUE_ACT);
          $sheet->setCellValue('I' . $rows, $MONTANT_LIQUIDATION_ACT);
          $sheet->setCellValue('J' . $rows, $MONTANT_ORDONNANCEMENT_ACT);
          $sheet->setCellValue('K' . $rows, $MONTANT_PAIEMENT_ACT);
          $sheet->setCellValue('L' . $rows, $MONTANT_TITRE_DECAISSEMENT_ACT);

          $sheet->setCellValue('M' . $rows, $ecart_engage_act);
          $sheet->setCellValue('N' . $rows, $ecart_juridique_act);
          $sheet->setCellValue('O' . $rows, $ecart_liquidation_act);
          $sheet->setCellValue('P' . $rows, $ecart_ordonnancement_act);
          $sheet->setCellValue('Q' . $rows, $ecart_paiement_act);
          $sheet->setCellValue('R' . $rows, $ecart_decaissement_act);

          $sheet->setCellValue('S' . $rows, $taux_engage_act);
          $sheet->setCellValue('T' . $rows, $taux_juridique_act);
          $sheet->setCellValue('U' . $rows, $taux_liquidation_act);
          $sheet->setCellValue('V' . $rows, $taux_ordonnancement_act);
          $sheet->setCellValue('W' . $rows, $taux_paiement_act);
          $sheet->setCellValue('X' . $rows, $taux_decaissement_act);
          $sheet->setCellValue('Y' . $rows, $RESULTAT_REALISE_ACT);
          $sheet->setCellValue('Z' . $rows, $ecart_physique_act);
          $sheet->setCellValue('AA' .$rows, '');

          ///export par rapport au code budgetaire

          $params_imputation=$this->getBindParms('DISTINCT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' '.$critere_imput,'ligne.CODE_NOMENCLATURE_BUDGETAIRE ASC');
          $params_imputation=str_replace('\"', '"', $params_imputation);
          $imputation=$this->ModelPs->getRequete($callpsreq, $params_imputation);

          foreach ($imputation as $key_code)
          { 
            $rows++;
            $params_infos_imput=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ','1');
            $params_infos_imput=str_replace('\"', '"', $params_infos_imput);
            $infos_sup_imputation=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_imput);
            $params_total_imput=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ','PTBA_TACHE_ID ASC');
            $params_total_imput=str_replace('\"','"',$params_total_imput);
            $total_imput=$this->ModelPs->getRequeteOne($callpsreq,$params_total_imput);
            $BUDGET_VOTE_IMPUT=intval($total_imput['total']);
            $BUDGET_VOTE_IMPUT=!empty($BUDGET_VOTE_IMPUT) ? $BUDGET_VOTE_IMPUT : '1';
            $QUANTITE_VOTE_IMPUT=intval($total_imput['qte_total']);


            //Montant transferé
            $param_mont_trans_imput = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID',' ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.'  '.$tranch_transf,'1');
            $param_mont_trans_imput=str_replace('\"','"',$param_mont_trans_imput);
            $mont_transf_imput=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_imput);
            $MONTANT_TRANSFERT_IMPUT=floatval($mont_transf_imput['MONTANT_TRANSFERT']);

            //Montant receptionné
            $param_mont_recep_imput = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID',' ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.'  '.$tranch_transf,'1');
            $param_mont_recep_imput=str_replace('\"','"',$param_mont_recep_imput);
            $mont_recep_imput=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_imput);
            $MONTANT_RECEPTION_IMPUT=floatval($mont_recep_imput['MONTANT_RECEPTION']);
            $TRANSFERTS_CREDITS_RESTE_IMPUT=(floatval($MONTANT_TRANSFERT_IMPUT) - floatval($MONTANT_RECEPTION_IMPUT));

            if($TRANSFERTS_CREDITS_RESTE_IMPUT >= 0)
            {
              $TRANSFERTS_CREDITS_IMPUT = $TRANSFERTS_CREDITS_RESTE_IMPUT;
            }
            else{
              $TRANSFERTS_CREDITS_IMPUT = $TRANSFERTS_CREDITS_RESTE_IMPUT*(-1);
            }

            $CREDIT_APRES_TRANSFERT_IMPUT=(floatval($BUDGET_VOTE_IMPUT) - floatval($MONTANT_TRANSFERT_IMPUT)) + floatval($MONTANT_RECEPTION_IMPUT);

            if($CREDIT_APRES_TRANSFERT_IMPUT < 0){
              $CREDIT_APRES_TRANSFERT_IMPUT = $CREDIT_APRES_TRANSFERT_IMPUT*(-1);
            }

            if($mont_transf_imput['CODE_NOMENCLATURE_BUDGETAIRE_ID']==$mont_recep_imput['CODE_NOMENCLATURE_BUDGETAIRE_ID'])
            {
              $TRANSFERTS_CREDITS_IMPUT = $MONTANT_TRANSFERT_IMPUT;
              $CREDIT_APRES_TRANSFERT_IMPUT = floatval($BUDGET_VOTE_IMPUT);
            }


            ///recuperer le montant,qte realise par trimestre
            $params_qte_realise_imput=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' '.$critere_tranche,'1');
            $params_qte_realise_imput=str_replace('\"','"',$params_qte_realise_imput);
            $qte_realise_imput=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_imput);
            $RESULTAT_REALISE_IMPUT=!empty($qte_realise_imput['resultat_realise']) ? $qte_realise_imput['resultat_realise'] : '0';
            $MONTANT_ENGAGE_IMPUT=!empty($infos_sup_imputation['MONTANT_ENGAGE']) ? $infos_sup_imputation['MONTANT_ENGAGE'] : '0';
            $MONTANT_JURIDIQUE_IMPUT=!empty($infos_sup_imputation['MONTANT_JURIDIQUE']) ? $infos_sup_imputation['MONTANT_JURIDIQUE'] : '0';
            $MONTANT_LIQUIDATION_IMPUT=!empty($infos_sup_imputation['MONTANT_LIQUIDATION']) ? $infos_sup_imputation['MONTANT_LIQUIDATION'] : '0';
            $MONTANT_ORDONNANCEMENT_IMPUT=!empty($infos_sup_imputation['MONTANT_ORDONNANCEMENT']) ? $infos_sup_imputation['MONTANT_ORDONNANCEMENT'] : '0';
            $MONTANT_PAIEMENT_IMPUT=!empty($infos_sup_imputation['PAIEMENT']) ? $infos_sup_imputation['PAIEMENT'] : '0';
            $MONTANT_TITRE_DECAISSEMENT_IMPUT=!empty($infos_sup_imputation['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_imputation['MONTANT_TITRE_DECAISSEMENT'] : '0';
            $QUANTITE_REALISE_IMPUT=!empty($qte_realise_imput['QTE_REALISE']) ? $qte_realise_imput['QTE_REALISE'] : '0';

            $ecart_engage_imput=$BUDGET_VOTE_IMPUT-$MONTANT_ENGAGE_IMPUT;
            $ecart_juridique_imput=$BUDGET_VOTE_IMPUT-$MONTANT_JURIDIQUE_IMPUT;
            $ecart_liquidation_imput=$BUDGET_VOTE_IMPUT-$MONTANT_LIQUIDATION_IMPUT;
            $ecart_ordonnancement_imput=$BUDGET_VOTE_IMPUT-$MONTANT_ORDONNANCEMENT_IMPUT;
            $ecart_paiement_imput=$BUDGET_VOTE_IMPUT-$MONTANT_PAIEMENT_IMPUT;
            $ecart_decaissement_imput=$BUDGET_VOTE_IMPUT-$MONTANT_TITRE_DECAISSEMENT_IMPUT;
            $ecart_physique_imput=$QUANTITE_VOTE_IMPUT-$QUANTITE_REALISE_IMPUT;

            $taux_engage_imput=$MONTANT_ENGAGE_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_juridique_imput=$MONTANT_JURIDIQUE_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_liquidation_imput=$MONTANT_LIQUIDATION_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_ordonnancement_imput=$MONTANT_ORDONNANCEMENT_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_paiement_imput=$MONTANT_PAIEMENT_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_decaissement_imput=$MONTANT_TITRE_DECAISSEMENT_IMPUT*100/$BUDGET_VOTE_IMPUT;

            $sheet->setCellValue('A' . $rows, '           '.$key_code->CODE_NOMENCLATURE_BUDGETAIRE);
            $sheet->setCellValue('B' . $rows, '');
            $sheet->setCellValue('C' . $rows, '');
            $sheet->setCellValue('D' . $rows, $BUDGET_VOTE_IMPUT);
            $sheet->setCellValue('E' . $rows, $TRANSFERTS_CREDITS_IMPUT);
            $sheet->setCellValue('F' . $rows, $CREDIT_APRES_TRANSFERT_IMPUT);
            $sheet->setCellValue('G' . $rows, $MONTANT_ENGAGE_IMPUT);
            $sheet->setCellValue('H' . $rows, $MONTANT_JURIDIQUE_IMPUT);
            $sheet->setCellValue('I' . $rows, $MONTANT_LIQUIDATION_IMPUT);
            $sheet->setCellValue('J' . $rows, $MONTANT_ORDONNANCEMENT_IMPUT);
            $sheet->setCellValue('K' . $rows, $MONTANT_PAIEMENT_IMPUT);
            $sheet->setCellValue('L' . $rows, $MONTANT_TITRE_DECAISSEMENT_IMPUT);

            $sheet->setCellValue('M' . $rows, $ecart_engage_imput);
            $sheet->setCellValue('N' . $rows, $ecart_juridique_imput);
            $sheet->setCellValue('O' . $rows, $ecart_liquidation_imput);
            $sheet->setCellValue('P' . $rows, $ecart_ordonnancement_imput);
            $sheet->setCellValue('Q' . $rows, $ecart_paiement_imput);
            $sheet->setCellValue('R' . $rows, $ecart_decaissement_imput);

            $sheet->setCellValue('S' . $rows, $taux_engage_imput);
            $sheet->setCellValue('T' . $rows, $taux_juridique_imput);
            $sheet->setCellValue('U' . $rows, $taux_liquidation_imput);
            $sheet->setCellValue('V' . $rows, $taux_ordonnancement_imput);
            $sheet->setCellValue('W' . $rows, $taux_paiement_imput);
            $sheet->setCellValue('X' . $rows, $taux_decaissement_imput);
            $sheet->setCellValue('Y' . $rows, $RESULTAT_REALISE_IMPUT);
            $sheet->setCellValue('Z' . $rows, $ecart_physique_imput);
            $sheet->setCellValue('AA' .$rows, '');

            ///export des activites
            $params_activite=$this->getBindParms('DISTINCT PTBA_TACHE_ID,DESC_TACHE,RESULTAT_ATTENDUS_TACHE','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ','DESC_TACHE ASC');
            $params_activite=str_replace('\"', '"', $params_activite);
            $activites=$this->ModelPs->getRequete($callpsreq, $params_activite);

            foreach ($activites as $key_activ)
            {
              $rows++;
              $params_infos_activ=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba_tache.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID,'1');
              $params_infos_activ=str_replace('\"', '"', $params_infos_activ);
              $infos_sup_activite=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_activ);
              $params_total_activ=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID,'PTBA_TACHE_ID ASC');
              $params_total_activ=str_replace('\"','"',$params_total_activ);
              $total_activite=$this->ModelPs->getRequeteOne($callpsreq,$params_total_activ);
              $BUDGET_VOTE_TACHE=intval($total_activite['total']);
              $BUDGET_VOTE_TACHE=!empty($BUDGET_VOTE_TACHE) ? $BUDGET_VOTE_TACHE : '1';
              $QUANTITE_VOTE_ACTIV=intval($total_activite['qte_total']);


              //Montant transferé
              $param_mont_trans_tache = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID.' '.$tranch_transf,'1');
              $param_mont_trans_tache=str_replace('\"','"',$param_mont_trans_tache);
              $mont_transf_tache=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_tache);
              $MONTANT_TRANSFERT_TACHE=floatval($mont_transf_tache['MONTANT_TRANSFERT']);

              //Montant receptionné
              $param_mont_recep_tache = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID.' '.$tranch_transf,'1');
              $param_mont_recep_tache=str_replace('\"','"',$param_mont_recep_tache);
              $mont_recep_tache=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_tache);
              $MONTANT_RECEPTION_TACHE=floatval($mont_recep_tache['MONTANT_RECEPTION']);
              $TRANSFERTS_CREDITS_RESTE_TACHE=(floatval($MONTANT_TRANSFERT_TACHE) - floatval($MONTANT_RECEPTION_TACHE));

              if($TRANSFERTS_CREDITS_RESTE_TACHE >= 0)
              {
                $TRANSFERTS_CREDITS_TACHE = $TRANSFERTS_CREDITS_RESTE_TACHE;
              }
              else{
                $TRANSFERTS_CREDITS_TACHE = $TRANSFERTS_CREDITS_RESTE_TACHE*(-1);
              }

              $CREDIT_APRES_TRANSFERT_TACHE=(floatval($BUDGET_VOTE_TACHE) - floatval($MONTANT_TRANSFERT_TACHE)) + floatval($MONTANT_RECEPTION_TACHE);

              if($CREDIT_APRES_TRANSFERT_TACHE < 0){
                $CREDIT_APRES_TRANSFERT_TACHE = $CREDIT_APRES_TRANSFERT_TACHE*(-1);
              }

              if($mont_transf_tache['PTBA_TACHE_ID']==$mont_recep_tache['PTBA_TACHE_ID'])
              {
                $TRANSFERTS_CREDITS_TACHE = $MONTANT_TRANSFERT_TACHE;
                $CREDIT_APRES_TRANSFERT_TACHE = floatval($BUDGET_VOTE_TACHE);
              }

              ///recuperer le montant,qte realise par trimestre
              $params_qte_realise_activ=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID NOT IN(42) AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.'  AND ptba_tache.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID.' '.$critere_tranche,'1');
              $params_qte_realise_activ=str_replace('\"','"',$params_qte_realise_activ);
              $qte_realise_activ=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_activ);
              $RESULTAT_REALISE_ACTIV=!empty($qte_realise_activ['resultat_realise']) ? $qte_realise_activ['resultat_realise'] : '0';
              $MONTANT_ENGAGE_ACTIV=!empty($infos_sup_activite['MONTANT_ENGAGE']) ? $infos_sup_activite['MONTANT_ENGAGE'] : '0';
              $MONTANT_JURIDIQUE_ACTIV=!empty($infos_sup_activite['MONTANT_JURIDIQUE']) ? $infos_sup_activite['MONTANT_JURIDIQUE'] : '0';
              $MONTANT_LIQUIDATION_ACTIV=!empty($infos_sup_activite['MONTANT_LIQUIDATION']) ? $infos_sup_activite['MONTANT_LIQUIDATION'] : '0';
              $MONTANT_ORDONNANCEMENT_ACTIV=!empty($infos_sup_activite['MONTANT_ORDONNANCEMENT']) ? $infos_sup_activite['MONTANT_ORDONNANCEMENT'] : '0';
              $MONTANT_PAIEMENT_ACTIV=!empty($infos_sup_activite['PAIEMENT']) ? $infos_sup_activite['PAIEMENT'] : '0';
              $MONTANT_TITRE_DECAISSEMENT_ACTIV=!empty($infos_sup_activite['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_activite['MONTANT_TITRE_DECAISSEMENT'] : '0';
              $QUANTITE_REALISE_ACTIV=!empty($qte_realise_activ['QTE_REALISE']) ? $qte_realise_activ['QTE_REALISE'] : '0';

              $ecart_engage_activ=$BUDGET_VOTE_TACHE-$MONTANT_ENGAGE_ACTIV;
              $ecart_juridique_activ=$BUDGET_VOTE_TACHE-$MONTANT_JURIDIQUE_ACTIV;
              $ecart_liquidation_activ=$BUDGET_VOTE_TACHE-$MONTANT_LIQUIDATION_ACTIV;
              $ecart_ordonnancement_activ=$BUDGET_VOTE_TACHE-$MONTANT_ORDONNANCEMENT_ACTIV;
              $ecart_paiement_activ=$BUDGET_VOTE_TACHE-$MONTANT_PAIEMENT_ACTIV;
              $ecart_decaissement_activ=$BUDGET_VOTE_TACHE-$MONTANT_TITRE_DECAISSEMENT_ACTIV;
              $ecart_physique_activ=$QUANTITE_VOTE_ACTIV-$QUANTITE_REALISE_ACTIV;

              $taux_engage_activ=$MONTANT_ENGAGE_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_juridique_activ=$MONTANT_JURIDIQUE_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_liquidation_activ=$MONTANT_LIQUIDATION_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_ordonnancement_activ=$MONTANT_ORDONNANCEMENT_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_paiement_activ=$MONTANT_PAIEMENT_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_decaissement_activ=$MONTANT_TITRE_DECAISSEMENT_ACTIV*100/$BUDGET_VOTE_TACHE;

              $sheet->setCellValue('A' . $rows, '');
              $sheet->setCellValue('B' . $rows, $key_activ->DESC_TACHE);
              $sheet->setCellValue('C' . $rows, $key_activ->RESULTAT_ATTENDUS_TACHE);
              $sheet->setCellValue('D' . $rows, $BUDGET_VOTE_TACHE);
              $sheet->setCellValue('E' . $rows, $TRANSFERTS_CREDITS_TACHE);
              $sheet->setCellValue('F' . $rows, $CREDIT_APRES_TRANSFERT_TACHE);
              $sheet->setCellValue('G' . $rows, $MONTANT_ENGAGE_ACTIV);
              $sheet->setCellValue('H' . $rows, $MONTANT_JURIDIQUE_ACTIV);
              $sheet->setCellValue('I' . $rows, $MONTANT_LIQUIDATION_ACTIV);
              $sheet->setCellValue('J' . $rows, $MONTANT_ORDONNANCEMENT_ACTIV);
              $sheet->setCellValue('K' . $rows, $MONTANT_PAIEMENT_ACTIV);
              $sheet->setCellValue('L' . $rows, $MONTANT_TITRE_DECAISSEMENT_ACTIV);

              $sheet->setCellValue('M' . $rows, $ecart_engage_activ);
              $sheet->setCellValue('N' . $rows, $ecart_juridique_activ);
              $sheet->setCellValue('O' . $rows, $ecart_liquidation_activ);
              $sheet->setCellValue('P' . $rows, $ecart_ordonnancement_activ);
              $sheet->setCellValue('Q' . $rows, $ecart_paiement_activ);
              $sheet->setCellValue('R' . $rows, $ecart_decaissement_activ);

              $sheet->setCellValue('S' . $rows, $taux_engage_activ);
              $sheet->setCellValue('T' . $rows, $taux_juridique_activ);
              $sheet->setCellValue('U' . $rows, $taux_liquidation_activ);
              $sheet->setCellValue('V' . $rows, $taux_ordonnancement_activ);
              $sheet->setCellValue('W' . $rows, $taux_paiement_activ);
              $sheet->setCellValue('X' . $rows, $taux_decaissement_activ);
              $sheet->setCellValue('Y' . $rows, $RESULTAT_REALISE_ACTIV);
              $sheet->setCellValue('Z' . $rows, $ecart_physique_activ);
              $sheet->setCellValue('AA' .$rows, '');

            }
          }
        }
      }
      $rows++;
    }

    $sheet->getColumnDimension('A')->setWidth(35);
    $sheet->getColumnDimension('B')->setWidth(35);
    $sheet->getColumnDimension('C')->setWidth(35);
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('suivi.xlsx');
    return $this->response->download('suivi.xlsx', null)->setFileName('suivi évaluation.xlsx');
    return redirect('ihm/Rapport_Suivi_Evaluation');   
  }

  //function pour exporter dans word
  function exporter_word($RESPONSABLE='',$PROGRAMME_ID='',$ACTION_ID='',$CODE_NOMENCLATURE_BUDGETAIRE_ID='',$TRIMESTRE_ID='',$ANNEE_BUDGETAIRE_ID='',$DATE_DEBUT='',$DATE_FIN='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_SUIVI_EVALUATION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $db = db_connect();
    $critere_resp='';
    $critere_tranche='';
    $critere_imput='';
    $critere_pr='';
    $critere_act='';
    $critere_anne='';
    $critere_date="";
    $critere_date_act='';
    $ann=$this->get_annee_budgetaire();
    //$ann=2;
    
    if ($RESPONSABLE!=0)
    {
      $critere_resp=' AND pt.STRUTURE_RESPONSABLE_TACHE_ID='.$RESPONSABLE.' ';

      if ($PROGRAMME_ID>0)
      {
        $critere_pr=' AND prog.PROGRAMME_ID='.$PROGRAMME_ID.' ';

        if ($ACTION_ID>0)
        {
          $critere_act=' AND act.ACTION_ID='.$ACTION_ID.' ';

          if ($CODE_NOMENCLATURE_BUDGETAIRE_ID>0)
          {
            $critere_imput=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID.' ';
          }
        }
      }
    }

    //Tranche_id pour les transferts
    $tranch_transf =" ";
    if($TRIMESTRE_ID!=5)
    {
      $tranch_transf=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    if($TRIMESTRE_ID!=5)
    {
      $critere_tranche.=" AND exec.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }
    if($ANNEE_BUDGETAIRE_ID>0)
    {
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    }else{
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ann;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date=' AND DATE_DEMANDE >="'.$DATE_DEBUT.'"';
      $critere_date_act=' AND DATE_BON_ENGAGEMENT >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date=' AND DATE_DEMANDE <= "'.$DATE_FIN.'"';
      $critere_date_act=' AND DATE_BON_ENGAGEMENT <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date=' AND DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
      $critere_date_act=' AND DATE_BON_ENGAGEMENT BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
    $annee_budgetaire = $this->ModelPs->getRequeteOne($callpsreq, $get_ann_budg);
    $annee_dexcr=str_replace('-','/',$annee_budgetaire['ANNEE_DESCRIPTION']);

    $p_deb = '01/07/'.substr($annee_dexcr, 0, 4);
    $p_fin = date("d/m/Y");
    if($TRIMESTRE_ID==1){
      $p_deb = '01/07/'.substr($annee_dexcr, 0, 4);
      $p_fin = '30/09/'.substr($annee_dexcr, 0, 4);
    }
    if($TRIMESTRE_ID==2){
      $p_deb = '01/10/'.substr($annee_dexcr, 0, 4);
      $p_fin = '31/12/'.substr($annee_dexcr, 0, 4);
    }
    if($TRIMESTRE_ID==3){
      $p_deb = '01/01/'.substr($annee_dexcr, 0, 4);
      $p_fin = '31/03/'.substr($annee_dexcr, 0, 4);
    }
    if($TRIMESTRE_ID==4){
      $p_deb = '01/04/'.substr($annee_dexcr, 0, 4);
      $p_fin = '30/06/'.substr($annee_dexcr, 0, 4);
    }

    $periode_debut = (!empty($DATE_DEBUT)) ? date('d/m/Y',strtotime($DATE_DEBUT)) : $p_deb ;
    $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;

    if($ann != $ANNEE_BUDGETAIRE_ID)
    {
      $p_fin = '30/06/'.substr($annee_dexcr,5);
      $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;
    }

    // Assuming $data contains the data you want to export
    $get_responsable = $this->getBindParms('DISTINCT pt.STRUTURE_RESPONSABLE_TACHE_ID, resp.DESC_STRUTURE_RESPONSABLE_TACHE AS RESPONSABLE','ptba_tache pt JOIN inst_institutions inst ON pt.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON pt.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON pt.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON pt.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN struture_responsable_tache resp ON resp.STRUTURE_RESPONSABLE_TACHE_ID=pt.STRUTURE_RESPONSABLE_TACHE_ID','1 ' . $critere_resp, 'RESPONSABLE ASC ');
    $get_responsable=str_replace('\"', '"', $get_responsable);
    $responsable = $this->ModelPs->getRequete($callpsreq, $get_responsable);
      // Create a new PhpWord object
    $phpWord = new PhpWord();

    // Définir la section en mode paysage
    $sectionStyle = array(
    'orientation' => 'landscape',
    'marginTop' => 600,
    'colsNum' => 1,
    );
    $tableStyle = [
        'borderSize' => 6,
    ];

    $phpWord->addTableStyle('myTable', $tableStyle);

    // Add a section
    $section = $phpWord->addSection($sectionStyle);

    $section->addText('CIRCUIT DES DEPENSES', ['bold' => true, 'size'=> 16], ['align' => 'center']);
    $section->addText('SUIVI EVALUATION', ['bold' => true, 'underline' => 'single', 'size'=> 14], ['align' => 'center']);
    $section->addText('EXERCICE '.$annee_dexcr.', N° BUDGET 0                     Période du '.$periode_debut.' au '.$periode_fin.'', ['bold' => false, 'size'=> 12], ['align' => 'center']);

    // Add a table
    $table = $section->addTable('myTable');

    // Add header row with bold text
    $table->addRow();
    $table->addCell(1500)->addText('RESPONSABLE', ['bold' => true,  'size' => 6], ['align' => 'center'], ['border'=>2], ['bordersize'=>3]);
    $table->addCell(1500)->addText('TÂCHE', ['bold' => true,  'size' => 6], ['align' => 'center'], ['border'=>2], ['bordersize'=>3]);
    $table->addCell(1500)->addText('RESULTATS ATTENDUS', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('BUDGET VOTE', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('TRANSFERTS', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('APRES TRANSFERT', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('ENG.BUDGETAIRE', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('ENG.JURIDIQUE', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('LIQUIDATION', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('ORDONNANCEMENT', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('PAIEMENT', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('DECAISSEMENT', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('ECART BUDGETAIRE', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('ECART JURIDIQUE', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('ECART LIQUIDATION', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('ECART PAIEMENT', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('ECART ORDONNANCEMENT', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    $table->addCell(1500)->addText('ECART DECAISSEMENT', ['bold' => true,  'size' => 6], ['align' => 'center'], ['bordersize'=>3]);
    

    ///boucle pour les responsables
    foreach ($responsable as $key)
    {
      $params_infos=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache pt ON pt.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID','pt.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' ','1');
      $params_infos=str_replace('\"', '"', $params_infos);
      $infos_sup=$this->ModelPs->getRequeteOne($callpsreq, $params_infos);

      ///recuperer le montant,qte voté par trimestre
      $montant_total="SUM(BUDGET_ANNUEL) AS total,SUM(Q_TOTAL) as qte_total";
      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(BUDGET_T1) AS total,SUM(QT1) as qte_total";  
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(BUDGET_T2) AS total,SUM(QT2) as qte_total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(BUDGET_T3) AS total,SUM(QT3) as qte_total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(BUDGET_T4) AS total,SUM(QT4) as qte_total";
      }

      $params_div=$this->getBindParms($montant_total,'ptba_tache','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' ','PTBA_TACHE_ID ASC');
      $params_div=str_replace('\"','"',$params_div);
      $total_vote=$this->ModelPs->getRequeteOne($callpsreq,$params_div);
      $BUDGET_VOTE=intval($total_vote['total']);

      $BUDGET_VOTE=!empty($BUDGET_VOTE) ? $BUDGET_VOTE : '1';

      $QUANTITE_VOTE=intval($total_vote['qte_total']);

      //Montant transferé
      $param_mont_trans = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.STRUTURE_RESPONSABLE_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' '.$tranch_transf,'1');
      $param_mont_trans=str_replace('\"','"',$param_mont_trans);
      $mont_transf=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans);
      $MONTANT_TRANSFERT=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.STRUTURE_RESPONSABLE_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' '.$tranch_transf,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep);
      $MONTANT_RECEPTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $TRANSFERTS_CREDITS_RESTE=(floatval($MONTANT_TRANSFERT) - floatval($MONTANT_RECEPTION));

      if($TRANSFERTS_CREDITS_RESTE >= 0)
      {
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE;
      }
      else{
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($BUDGET_VOTE) - floatval($MONTANT_TRANSFERT)) + floatval($MONTANT_RECEPTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['STRUTURE_RESPONSABLE_TACHE_ID']==$mont_recep['STRUTURE_RESPONSABLE_TACHE_ID'])
      {
        $TRANSFERTS_CREDITS = $MONTANT_TRANSFERT;
        $CREDIT_APRES_TRANSFERT = floatval($BUDGET_VOTE);
      }


      ///recuperer le montant,qte realise par trimestre
      $params_qte_realise=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.'  '.$critere_tranche,'1');
      $params_qte_realise=str_replace('\"','"',$params_qte_realise);
      $qte_realise=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise);

      $RESULTAT_REALISE=!empty($qte_realise['resultat_realise']) ? $qte_realise['resultat_realise'] : '0';
      $MONTANT_ENGAGE=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $MONTANT_PAIEMENT=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $MONTANT_TITRE_DECAISSEMENT=!empty($infos_sup['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup['MONTANT_TITRE_DECAISSEMENT'] : '0';
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

      $RESPONSABLE = preg_replace('/[^a-zA-Z0-9_\p{L}\s]/u', '',$key->RESPONSABLE);

      $table->addRow();
      $table->addCell(1500, ['cellMargin' => 20])->addText($db->escapeString($RESPONSABLE), ['size' => 6]);
      $table->addCell(1500)->addText('');
      $table->addCell(1500)->addText('');
      $table->addCell(1500)->addText(number_format($BUDGET_VOTE,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($TRANSFERTS_CREDITS,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($CREDIT_APRES_TRANSFERT,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($MONTANT_ENGAGE,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($MONTANT_JURIDIQUE,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($MONTANT_LIQUIDATION,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($MONTANT_ORDONNANCEMENT,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($MONTANT_PAIEMENT,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($MONTANT_TITRE_DECAISSEMENT,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($ecart_engage,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($ecart_juridique,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($ecart_liquidation,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($ecart_ordonnancement,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($ecart_paiement,0,","," "),['size' => 6]);
      $table->addCell(1500)->addText(number_format($ecart_decaissement,0,","," "),['size' => 6]);

      $get_program=$this->getBindParms('DISTINCT prog.PROGRAMME_ID,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.''.$critere_pr,'prog.CODE_PROGRAMME ASC');
      $get_program=str_replace('\"', '"',$get_program);
      $programmes= $this->ModelPs->getRequete($callpsreq,$get_program);

      foreach ($programmes as $key_program)
      {
        $params_infos_pgm=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache pt ON pt.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID','pt.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND pt.PROGRAMME_ID='.$key_program->PROGRAMME_ID.'','1');

        $params_infos_pgm=str_replace('\"', '"', $params_infos_pgm);

        $infos_sup_pgm=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_pgm);

        $params_total_pgm=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.'','1');
        $params_total_pgm=str_replace('\"','"',$params_total_pgm);

        $total_pgm=$this->ModelPs->getRequeteOne($callpsreq,$params_total_pgm);

        $BUDGET_VOTE_PGM=intval($total_pgm['total']);
        $BUDGET_VOTE_PGM=!empty($BUDGET_VOTE_PGM) ? $BUDGET_VOTE_PGM : '1';
        $QUANTITE_VOTE_PGM=intval($total_pgm['qte_total']);


        //Montant transferé
        $param_mont_trans_prg = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$tranch_transf,'1');
        $param_mont_trans_prg=str_replace('\"','"',$param_mont_trans_prg);
        $mont_transf_prg=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_prg);
        $MONTANT_TRANSFERT_PGM=floatval($mont_transf_prg['MONTANT_TRANSFERT']);

        //Montant receptionné
        $param_mont_recep_prg = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$tranch_transf,'1');
        $param_mont_recep_prg=str_replace('\"','"',$param_mont_recep_prg);
        $mont_recep_prg=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_prg);
        $MONTANT_RECEPTION_PGM=floatval($mont_recep_prg['MONTANT_RECEPTION']);

        $TRANSFERTS_CREDITS_RESTE_PGM=(floatval($MONTANT_TRANSFERT_PGM) - floatval($MONTANT_RECEPTION_PGM));

        if($TRANSFERTS_CREDITS_RESTE_PGM >= 0)
        {
          $TRANSFERTS_CREDITS_PGM = $TRANSFERTS_CREDITS_RESTE_PGM;
        }
        else{
          $TRANSFERTS_CREDITS_PGM = $TRANSFERTS_CREDITS_RESTE_PGM*(-1);
        }

        $CREDIT_APRES_TRANSFERT_PGM=(floatval($BUDGET_VOTE_PGM) - floatval($MONTANT_TRANSFERT_PGM)) + floatval($MONTANT_RECEPTION_PGM);

        if($CREDIT_APRES_TRANSFERT_PGM < 0){
          $CREDIT_APRES_TRANSFERT_PGM = $CREDIT_APRES_TRANSFERT_PGM*(-1);
        }

        if($mont_transf_prg['PROGRAMME_ID']==$mont_recep_prg['PROGRAMME_ID'])
        {
          $TRANSFERTS_CREDITS_PGM = $MONTANT_TRANSFERT_PGM;
          $CREDIT_APRES_TRANSFERT_PGM = floatval($BUDGET_VOTE_PGM);
        }

        ///recuperer le montant,qte realise par trimestre
        $params_qte_realise_pgm=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$critere_tranche,'1');
        $params_qte_realise_pgm=str_replace('\"','"',$params_qte_realise_pgm);
        $qte_realise_pgm=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_pgm);

        $RESULTAT_REALISE_PGM=!empty($qte_realise_pgm['resultat_realise']) ? $qte_realise_pgm['resultat_realise'] : '0';

        $MONTANT_ENGAGE_PGM=!empty($infos_sup_pgm['MONTANT_ENGAGE']) ? $infos_sup_pgm['MONTANT_ENGAGE'] : '0';
        $MONTANT_JURIDIQUE_PGM=!empty($infos_sup_pgm['MONTANT_JURIDIQUE']) ? $infos_sup_pgm['MONTANT_JURIDIQUE'] : '0';
        $MONTANT_LIQUIDATION_PGM=!empty($infos_sup_pgm['MONTANT_LIQUIDATION']) ? $infos_sup_pgm['MONTANT_LIQUIDATION'] : '0';
        $MONTANT_ORDONNANCEMENT_PGM=!empty($infos_sup_pgm['MONTANT_ORDONNANCEMENT']) ? $infos_sup_pgm['MONTANT_ORDONNANCEMENT'] : '0';
        $MONTANT_PAIEMENT_PGM=!empty($infos_sup_pgm['PAIEMENT']) ? $infos_sup_pgm['PAIEMENT'] : '0';
        $MONTANT_TITRE_DECAISSEMENT_PGM=!empty($infos_sup_pgm['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_pgm['MONTANT_TITRE_DECAISSEMENT'] : '0';
        $QUANTITE_REALISE_PGM=!empty($qte_realise_pgm['QTE_REALISE']) ? $qte_realise_pgm['QTE_REALISE'] : '0';
        $ecart_engage_pgm=$BUDGET_VOTE_PGM-$MONTANT_ENGAGE_PGM;
        $ecart_juridique_pgm=$BUDGET_VOTE_PGM-$MONTANT_JURIDIQUE_PGM;
        $ecart_liquidation_pgm=$BUDGET_VOTE_PGM-$MONTANT_LIQUIDATION_PGM;
        $ecart_ordonnancement_pgm=$BUDGET_VOTE_PGM-$MONTANT_ORDONNANCEMENT_PGM;
        $ecart_paiement_pgm=$BUDGET_VOTE_PGM-$MONTANT_PAIEMENT_PGM;
        $ecart_decaissement_pgm=$BUDGET_VOTE_PGM-$MONTANT_TITRE_DECAISSEMENT_PGM;
        $ecart_physique_pgm=$QUANTITE_VOTE_PGM-$QUANTITE_REALISE_PGM;
        $taux_engage_pgm=$MONTANT_ENGAGE_PGM*100/$BUDGET_VOTE_PGM;
        $taux_juridique_pgm=$MONTANT_JURIDIQUE_PGM*100/$BUDGET_VOTE_PGM;
        $taux_liquidation_pgm=$MONTANT_LIQUIDATION_PGM*100/$BUDGET_VOTE_PGM;
        $taux_ordonnancement_pgm=$MONTANT_ORDONNANCEMENT_PGM*100/$BUDGET_VOTE_PGM;
        $taux_paiement_pgm=$MONTANT_PAIEMENT_PGM*100/$BUDGET_VOTE_PGM;
        $taux_decaissement_pgm=$MONTANT_TITRE_DECAISSEMENT_PGM*100/$BUDGET_VOTE_PGM;

        $INTITULE_PROGRAMME = preg_replace('/[^a-zA-Z0-9_\p{L}\s]/u', '',$key_program->INTITULE_PROGRAMME);

        $table->addRow();
        $table->addCell(1500, ['cellMargin' => 30])->addText($db->escapeString($INTITULE_PROGRAMME), ['size' => 6]);
        $table->addCell(1500)->addText('');
        $table->addCell(1500)->addText('');
        $table->addCell(1500)->addText(number_format($BUDGET_VOTE_PGM,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($TRANSFERTS_CREDITS_PGM,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($CREDIT_APRES_TRANSFERT_PGM,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($MONTANT_ENGAGE_PGM,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($MONTANT_JURIDIQUE_PGM,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($MONTANT_LIQUIDATION_PGM,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($MONTANT_ORDONNANCEMENT_PGM,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($MONTANT_PAIEMENT_PGM,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($MONTANT_TITRE_DECAISSEMENT_PGM,0,","," "), ['size' => 6]);

        $table->addCell(1500)->addText(number_format($ecart_engage_pgm,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($ecart_juridique_pgm,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($ecart_liquidation_pgm,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($ecart_ordonnancement_pgm,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($ecart_paiement_pgm,0,","," "), ['size' => 6]);
        $table->addCell(1500)->addText(number_format($ecart_decaissement_pgm,0,","," "), ['size' => 6]);

        ///EXPORT PAR RAPPORT A L ACTION
        $params_actions=$this->getBindParms('DISTINCT act.ACTION_ID,act.CODE_ACTION,act.LIBELLE_ACTION','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND prog.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$critere_act,'act.CODE_ACTION ASC');
        $params_actions=str_replace('\"', '"', $params_actions);
        $actions=$this->ModelPs->getRequete($callpsreq, $params_actions);

        foreach ($actions as $key_action)
        {
          $params_infos_sup_act=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' ','1');
          $params_infos_sup_act=str_replace('\"', '"', $params_infos_sup_act);
          $infos_sup_action=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_sup_act);

          $params_total_action=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' ','PTBA_TACHE_ID ASC');
          $params_total_action=str_replace('\"','"',$params_total_action);

          $total_action=$this->ModelPs->getRequeteOne($callpsreq,$params_total_action);

          $BUDGET_VOTE_ACT=intval($total_action['total']);
          $BUDGET_VOTE_ACT=!empty($BUDGET_VOTE_ACT) ? $BUDGET_VOTE_ACT : '1';
          $QUANTITE_VOTE_ACT=intval($total_action['qte_total']);

          //Montant transferé
          $param_mont_trans_act = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' '.$tranch_transf,'1');
          $param_mont_trans_act=str_replace('\"','"',$param_mont_trans_act);
          $mont_transf_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_act);
          $MONTANT_TRANSFERT_ACT=floatval($mont_transf_act['MONTANT_TRANSFERT']);

          //Montant receptionné
          $param_mont_recep_act = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' '.$tranch_transf,'1');
          $param_mont_recep_act=str_replace('\"','"',$param_mont_recep_act);
          $mont_recep_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_act);
          $MONTANT_RECEPTION_ACT=floatval($mont_recep_act['MONTANT_RECEPTION']);

          $TRANSFERTS_CREDITS_RESTE_ACT=(floatval($MONTANT_TRANSFERT_ACT) - floatval($MONTANT_RECEPTION_ACT));

          if($TRANSFERTS_CREDITS_RESTE_ACT >= 0)
          {
            $TRANSFERTS_CREDITS_ACT = $TRANSFERTS_CREDITS_RESTE_ACT;
          }
          else{
            $TRANSFERTS_CREDITS_ACT = $TRANSFERTS_CREDITS_RESTE_ACT*(-1);
          }

          $CREDIT_APRES_TRANSFERT_ACT=(floatval($BUDGET_VOTE_ACT) - floatval($MONTANT_TRANSFERT_ACT)) + floatval($MONTANT_RECEPTION_ACT);

          if($CREDIT_APRES_TRANSFERT_ACT < 0){
            $CREDIT_APRES_TRANSFERT_ACT = $CREDIT_APRES_TRANSFERT_ACT*(-1);
          }

          if($mont_transf_act['ACTION_ID']==$mont_recep_act['ACTION_ID'])
          {
            $TRANSFERTS_CREDITS_ACT = $MONTANT_TRANSFERT_ACT;
            $CREDIT_APRES_TRANSFERT_ACT = floatval($BUDGET_VOTE_ACT);
          }


          ///recuperer le montant,qte realise par trimestre
          $params_qte_realise_action=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' '.$critere_tranche,'1');
          $params_qte_realise_action=str_replace('\"','"',$params_qte_realise_action);
          $qte_realise_action=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_action);

          $RESULTAT_REALISE_ACT=!empty($qte_realise_action['resultat_realise']) ? $qte_realise_action['resultat_realise'] : '0';
          $MONTANT_ENGAGE_ACT=!empty($infos_sup_action['MONTANT_ENGAGE']) ? $infos_sup_action['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_ACT=!empty($infos_sup_action['MONTANT_JURIDIQUE']) ? $infos_sup_action['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_ACT=!empty($infos_sup_action['MONTANT_LIQUIDATION']) ? $infos_sup_action['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_ACT=!empty($infos_sup_action['MONTANT_ORDONNANCEMENT']) ? $infos_sup_action['MONTANT_ORDONNANCEMENT'] : '0';
          $MONTANT_PAIEMENT_ACT=!empty($infos_sup_action['PAIEMENT']) ? $infos_sup_action['PAIEMENT'] : '0';
          $MONTANT_TITRE_DECAISSEMENT_ACT=!empty($infos_sup_action['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_action['MONTANT_TITRE_DECAISSEMENT'] : '0';
          $QUANTITE_REALISE_ACT=!empty($qte_realise_action['QTE_REALISE']) ? $qte_realise_action['QTE_REALISE'] : '0';
          $ecart_engage_act=$BUDGET_VOTE_ACT-$MONTANT_ENGAGE_ACT;
          $ecart_juridique_act=$BUDGET_VOTE_ACT-$MONTANT_JURIDIQUE_ACT;
          $ecart_liquidation_act=$BUDGET_VOTE_ACT-$MONTANT_LIQUIDATION_ACT;
          $ecart_ordonnancement_act=$BUDGET_VOTE_ACT-$MONTANT_ORDONNANCEMENT_ACT;
          $ecart_paiement_act=$BUDGET_VOTE_ACT-$MONTANT_PAIEMENT_ACT;
          $ecart_decaissement_act=$BUDGET_VOTE_ACT-$MONTANT_TITRE_DECAISSEMENT_ACT;
          $ecart_physique_act=$QUANTITE_VOTE_ACT-$QUANTITE_REALISE_ACT;

          $taux_engage_act=$MONTANT_ENGAGE_ACT*100/$BUDGET_VOTE_ACT;
          $taux_juridique_act=$MONTANT_JURIDIQUE_ACT*100/$BUDGET_VOTE_ACT;
          $taux_liquidation_act=$MONTANT_LIQUIDATION_ACT*100/$BUDGET_VOTE_ACT;
          $taux_ordonnancement_act=$MONTANT_ORDONNANCEMENT_ACT*100/$BUDGET_VOTE_ACT;
          $taux_paiement_act=$MONTANT_PAIEMENT_ACT*100/$BUDGET_VOTE_ACT;
          $taux_decaissement_act=$MONTANT_TITRE_DECAISSEMENT_ACT*100/$BUDGET_VOTE_ACT;

          $LIBELLE_ACTION = preg_replace('/[^a-zA-Z0-9_\p{L}\s]/u', '',$key_action->LIBELLE_ACTION);

          $table->addRow();
          $table->addCell(1500, ['cellMargin' => 40])->addText(addslashes($LIBELLE_ACTION), ['size' => 6]);
          $table->addCell(1500)->addText('');
          $table->addCell(1500)->addText('');
          $table->addCell(1500)->addText(number_format($BUDGET_VOTE_ACT,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($TRANSFERTS_CREDITS_ACT,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($CREDIT_APRES_TRANSFERT_ACT,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($MONTANT_ENGAGE_ACT,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($MONTANT_JURIDIQUE_ACT,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($MONTANT_LIQUIDATION_ACT,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($MONTANT_ORDONNANCEMENT_ACT,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($MONTANT_PAIEMENT_ACT,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($MONTANT_TITRE_DECAISSEMENT_ACT,0,","," "), ['size' => 6]);

          $table->addCell(1500)->addText(number_format($ecart_engage_act,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($ecart_juridique_act,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($ecart_liquidation_act,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($ecart_ordonnancement_act,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($ecart_paiement_act,0,","," "), ['size' => 6]);
          $table->addCell(1500)->addText(number_format($ecart_decaissement_act,0,","," "), ['size' => 6]);

          $params_imputation=$this->getBindParms('DISTINCT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' '.$critere_imput,'ligne.CODE_NOMENCLATURE_BUDGETAIRE ASC');
          $params_imputation=str_replace('\"', '"', $params_imputation);
          $imputation=$this->ModelPs->getRequete($callpsreq, $params_imputation);

          foreach ($imputation as $key_code)
          {
            $params_infos_imput=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ','1');
            $params_infos_imput=str_replace('\"', '"', $params_infos_imput);
            $infos_sup_imputation=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_imput);
            $params_total_imput=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ','PTBA_TACHE_ID ASC');
            $params_total_imput=str_replace('\"','"',$params_total_imput);
            $total_imput=$this->ModelPs->getRequeteOne($callpsreq,$params_total_imput);

            $BUDGET_VOTE_IMPUT=intval($total_imput['total']);
            $BUDGET_VOTE_IMPUT=!empty($BUDGET_VOTE_IMPUT) ? $BUDGET_VOTE_IMPUT : '1';
            $QUANTITE_VOTE_IMPUT=intval($total_imput['qte_total']);

            //Montant transferé
            $param_mont_trans_imput = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID',' ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.'  '.$tranch_transf,'1');
            $param_mont_trans_imput=str_replace('\"','"',$param_mont_trans_imput);
            $mont_transf_imput=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_imput);
            $MONTANT_TRANSFERT_IMPUT=floatval($mont_transf_imput['MONTANT_TRANSFERT']);

            //Montant receptionné
            $param_mont_recep_imput = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID',' ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.'  '.$tranch_transf,'1');
            $param_mont_recep_imput=str_replace('\"','"',$param_mont_recep_imput);
            $mont_recep_imput=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_imput);
            $MONTANT_RECEPTION_IMPUT=floatval($mont_recep_imput['MONTANT_RECEPTION']);
            $TRANSFERTS_CREDITS_RESTE_IMPUT=(floatval($MONTANT_TRANSFERT_IMPUT) - floatval($MONTANT_RECEPTION_IMPUT));

            if($TRANSFERTS_CREDITS_RESTE_IMPUT >= 0)
            {
              $TRANSFERTS_CREDITS_IMPUT = $TRANSFERTS_CREDITS_RESTE_IMPUT;
            }
            else{
              $TRANSFERTS_CREDITS_IMPUT = $TRANSFERTS_CREDITS_RESTE_IMPUT*(-1);
            }

            $CREDIT_APRES_TRANSFERT_IMPUT=(floatval($BUDGET_VOTE_IMPUT) - floatval($MONTANT_TRANSFERT_IMPUT)) + floatval($MONTANT_RECEPTION_IMPUT);

            if($CREDIT_APRES_TRANSFERT_IMPUT < 0){
              $CREDIT_APRES_TRANSFERT_IMPUT = $CREDIT_APRES_TRANSFERT_IMPUT*(-1);
            }

            if($mont_transf_imput['CODE_NOMENCLATURE_BUDGETAIRE_ID']==$mont_recep_imput['CODE_NOMENCLATURE_BUDGETAIRE_ID'])
            {
              $TRANSFERTS_CREDITS_IMPUT = $MONTANT_TRANSFERT_IMPUT;
              $CREDIT_APRES_TRANSFERT_IMPUT = floatval($BUDGET_VOTE_IMPUT);
            }


            ///recuperer le montant,qte realise par trimestre
            $params_qte_realise_imput=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' '.$critere_tranche,'1');
            $params_qte_realise_imput=str_replace('\"','"',$params_qte_realise_imput);
            $qte_realise_imput=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_imput);

            $RESULTAT_REALISE_IMPUT=!empty($qte_realise_imput['resultat_realise']) ? $qte_realise_imput['resultat_realise'] : '0';
            $MONTANT_ENGAGE_IMPUT=!empty($infos_sup_imputation['MONTANT_ENGAGE']) ? $infos_sup_imputation['MONTANT_ENGAGE'] : '0';
            $MONTANT_JURIDIQUE_IMPUT=!empty($infos_sup_imputation['MONTANT_JURIDIQUE']) ? $infos_sup_imputation['MONTANT_JURIDIQUE'] : '0';
            $MONTANT_LIQUIDATION_IMPUT=!empty($infos_sup_imputation['MONTANT_LIQUIDATION']) ? $infos_sup_imputation['MONTANT_LIQUIDATION'] : '0';
            $MONTANT_ORDONNANCEMENT_IMPUT=!empty($infos_sup_imputation['MONTANT_ORDONNANCEMENT']) ? $infos_sup_imputation['MONTANT_ORDONNANCEMENT'] : '0';
            $MONTANT_PAIEMENT_IMPUT=!empty($infos_sup_imputation['PAIEMENT']) ? $infos_sup_imputation['PAIEMENT'] : '0';
            $MONTANT_TITRE_DECAISSEMENT_IMPUT=!empty($infos_sup_imputation['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_imputation['MONTANT_TITRE_DECAISSEMENT'] : '0';
            $QUANTITE_REALISE_IMPUT=!empty($qte_realise_imput['QTE_REALISE']) ? $qte_realise_imput['QTE_REALISE'] : '0';

            $ecart_engage_imput=$BUDGET_VOTE_IMPUT-$MONTANT_ENGAGE_IMPUT;
            $ecart_juridique_imput=$BUDGET_VOTE_IMPUT-$MONTANT_JURIDIQUE_IMPUT;
            $ecart_liquidation_imput=$BUDGET_VOTE_IMPUT-$MONTANT_LIQUIDATION_IMPUT;
            $ecart_ordonnancement_imput=$BUDGET_VOTE_IMPUT-$MONTANT_ORDONNANCEMENT_IMPUT;
            $ecart_paiement_imput=$BUDGET_VOTE_IMPUT-$MONTANT_PAIEMENT_IMPUT;
            $ecart_decaissement_imput=$BUDGET_VOTE_IMPUT-$MONTANT_TITRE_DECAISSEMENT_IMPUT;
            $ecart_physique_imput=$QUANTITE_VOTE_IMPUT-$QUANTITE_REALISE_IMPUT;

            $taux_engage_imput=$MONTANT_ENGAGE_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_juridique_imput=$MONTANT_JURIDIQUE_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_liquidation_imput=$MONTANT_LIQUIDATION_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_ordonnancement_imput=$MONTANT_ORDONNANCEMENT_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_paiement_imput=$MONTANT_PAIEMENT_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_decaissement_imput=$MONTANT_TITRE_DECAISSEMENT_IMPUT*100/$BUDGET_VOTE_IMPUT;

            $table->addRow();
            $table->addCell(1500, ['cellMargin' => 50])->addText($key_code->CODE_NOMENCLATURE_BUDGETAIRE, ['size' => 6]);
            $table->addCell(1500)->addText('');
            $table->addCell(1500)->addText('');
            $table->addCell(1500)->addText(number_format($BUDGET_VOTE_IMPUT,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($TRANSFERTS_CREDITS_IMPUT,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($CREDIT_APRES_TRANSFERT_IMPUT,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($MONTANT_ENGAGE_IMPUT,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($MONTANT_JURIDIQUE_IMPUT,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($MONTANT_LIQUIDATION_IMPUT,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($MONTANT_ORDONNANCEMENT_IMPUT,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($MONTANT_PAIEMENT_IMPUT,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($MONTANT_TITRE_DECAISSEMENT_IMPUT,0,","," "), ['size' => 6]);

            $table->addCell(1500)->addText(number_format($ecart_engage_imput,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($ecart_juridique_imput,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($ecart_liquidation_imput,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($ecart_ordonnancement_imput,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($ecart_paiement_imput,0,","," "), ['size' => 6]);
            $table->addCell(1500)->addText(number_format($ecart_decaissement_imput,0,","," "), ['size' => 6]);

            ///export des activites
            $params_activite=$this->getBindParms('DISTINCT PTBA_TACHE_ID,DESC_TACHE,RESULTAT_ATTENDUS_TACHE','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ','DESC_TACHE ASC');
            $params_activite=str_replace('\"', '"', $params_activite);
            $activites=$this->ModelPs->getRequete($callpsreq, $params_activite);

            foreach ($activites as $key_activ)
            {
              $params_infos_activ=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba_tache.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID,'1');
              $params_infos_activ=str_replace('\"', '"', $params_infos_activ);
              $infos_sup_activite=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_activ);
              $params_total_activ=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID,'PTBA_TACHE_ID ASC');
              $params_total_activ=str_replace('\"','"',$params_total_activ);
              $total_activite=$this->ModelPs->getRequeteOne($callpsreq,$params_total_activ);
              $BUDGET_VOTE_TACHE=intval($total_activite['total']);
              $BUDGET_VOTE_TACHE=!empty($BUDGET_VOTE_TACHE) ? $BUDGET_VOTE_TACHE : '1';
              $QUANTITE_VOTE_ACTIV=intval($total_activite['qte_total']);

              //Montant transferé
              $param_mont_trans_tache = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID.' '.$tranch_transf,'1');
              $param_mont_trans_tache=str_replace('\"','"',$param_mont_trans_tache);
              $mont_transf_tache=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_tache);
              $MONTANT_TRANSFERT_TACHE=floatval($mont_transf_tache['MONTANT_TRANSFERT']);

              //Montant receptionné
              $param_mont_recep_tache = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID.' '.$tranch_transf,'1');
              $param_mont_recep_tache=str_replace('\"','"',$param_mont_recep_tache);
              $mont_recep_tache=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_tache);
              $MONTANT_RECEPTION_TACHE=floatval($mont_recep_tache['MONTANT_RECEPTION']);
              $TRANSFERTS_CREDITS_RESTE_TACHE=(floatval($MONTANT_TRANSFERT_TACHE) - floatval($MONTANT_RECEPTION_TACHE));

              if($TRANSFERTS_CREDITS_RESTE_TACHE >= 0)
              {
                $TRANSFERTS_CREDITS_TACHE = $TRANSFERTS_CREDITS_RESTE_TACHE;
              }
              else{
                $TRANSFERTS_CREDITS_TACHE = $TRANSFERTS_CREDITS_RESTE_TACHE*(-1);
              }

              $CREDIT_APRES_TRANSFERT_TACHE=(floatval($BUDGET_VOTE_TACHE) - floatval($MONTANT_TRANSFERT_TACHE)) + floatval($MONTANT_RECEPTION_TACHE);

              if($CREDIT_APRES_TRANSFERT_TACHE < 0){
                $CREDIT_APRES_TRANSFERT_TACHE = $CREDIT_APRES_TRANSFERT_TACHE*(-1);
              }

              if($mont_transf_tache['PTBA_TACHE_ID']==$mont_recep_tache['PTBA_TACHE_ID'])
              {
                $TRANSFERTS_CREDITS_TACHE = $MONTANT_TRANSFERT_TACHE;
                $CREDIT_APRES_TRANSFERT_TACHE = floatval($BUDGET_VOTE_TACHE);
              }

              ///recuperer le montant,qte realise par trimestre
              $params_qte_realise_activ=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.'  AND ptba_tache.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID.' '.$critere_tranche,'1');
              $params_qte_realise_activ=str_replace('\"','"',$params_qte_realise_activ);
              $params_qte_realise_activ=str_replace('\"','"',$params_qte_realise_activ);
              $qte_realise_activ=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_activ);

              $RESULTAT_REALISE_ACTIV=!empty($qte_realise_activ['resultat_realise']) ? $qte_realise_activ['resultat_realise'] : '0';
              $MONTANT_ENGAGE_ACTIV=!empty($infos_sup_activite['MONTANT_ENGAGE']) ? $infos_sup_activite['MONTANT_ENGAGE'] : '0';
              $MONTANT_JURIDIQUE_ACTIV=!empty($infos_sup_activite['MONTANT_JURIDIQUE']) ? $infos_sup_activite['MONTANT_JURIDIQUE'] : '0';
              $MONTANT_LIQUIDATION_ACTIV=!empty($infos_sup_activite['MONTANT_LIQUIDATION']) ? $infos_sup_activite['MONTANT_LIQUIDATION'] : '0';
              $MONTANT_ORDONNANCEMENT_ACTIV=!empty($infos_sup_activite['MONTANT_ORDONNANCEMENT']) ? $infos_sup_activite['MONTANT_ORDONNANCEMENT'] : '0';
              $MONTANT_PAIEMENT_ACTIV=!empty($infos_sup_activite['PAIEMENT']) ? $infos_sup_activite['PAIEMENT'] : '0';
              $MONTANT_TITRE_DECAISSEMENT_ACTIV=!empty($infos_sup_activite['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_activite['MONTANT_TITRE_DECAISSEMENT'] : '0';

              $QUANTITE_REALISE_ACTIV=!empty($qte_realise_activ['QTE_REALISE']) ? $qte_realise_activ['QTE_REALISE'] : '0';

              $ecart_engage_activ=$BUDGET_VOTE_TACHE-$MONTANT_ENGAGE_ACTIV;
              $ecart_juridique_activ=$BUDGET_VOTE_TACHE-$MONTANT_JURIDIQUE_ACTIV;
              $ecart_liquidation_activ=$BUDGET_VOTE_TACHE-$MONTANT_LIQUIDATION_ACTIV;
              $ecart_ordonnancement_activ=$BUDGET_VOTE_TACHE-$MONTANT_ORDONNANCEMENT_ACTIV;
              $ecart_paiement_activ=$BUDGET_VOTE_TACHE-$MONTANT_PAIEMENT_ACTIV;
              $ecart_decaissement_activ=$BUDGET_VOTE_TACHE-$MONTANT_TITRE_DECAISSEMENT_ACTIV;
              $ecart_physique_activ=$QUANTITE_VOTE_ACTIV-$QUANTITE_REALISE_ACTIV;

              $taux_engage_activ=$MONTANT_ENGAGE_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_juridique_activ=$MONTANT_JURIDIQUE_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_liquidation_activ=$MONTANT_LIQUIDATION_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_ordonnancement_activ=$MONTANT_ORDONNANCEMENT_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_paiement_activ=$MONTANT_PAIEMENT_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_decaissement_activ=$MONTANT_TITRE_DECAISSEMENT_ACTIV*100/$BUDGET_VOTE_TACHE;

              $DESC_TACHE = preg_replace('/[^a-zA-Z0-9_\p{L}\s]/u', '',$key_activ->DESC_TACHE);
              $RESULTAT_ATTENDUS_TACHE = preg_replace('/[^a-zA-Z0-9_\p{L}\s]/u', '',$key_activ->RESULTAT_ATTENDUS_TACHE);

              $table->addRow();
              $table->addCell(1500, ['cellMargin' => 0])->addText('');
              $table->addCell(1500)->addText($db->escapeString($DESC_TACHE), ['size' => 6]);
              $table->addCell(1500)->addText($db->escapeString($RESULTAT_ATTENDUS_TACHE), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($BUDGET_VOTE_TACHE,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($TRANSFERTS_CREDITS_TACHE,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($CREDIT_APRES_TRANSFERT_TACHE,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($MONTANT_ENGAGE_ACTIV,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($MONTANT_JURIDIQUE_ACTIV,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($MONTANT_LIQUIDATION_ACTIV,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($MONTANT_ORDONNANCEMENT_ACTIV,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($MONTANT_PAIEMENT_ACTIV,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($MONTANT_TITRE_DECAISSEMENT_ACTIV,0,","," "), ['size' => 6]);

              $table->addCell(1500)->addText(number_format($ecart_engage_activ,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($ecart_juridique_activ,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($ecart_liquidation_activ,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($ecart_ordonnancement_activ,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($ecart_paiement_activ,0,","," "), ['size' => 6]);
              $table->addCell(1500)->addText(number_format($ecart_decaissement_activ,0,","," "), ['size' => 6]);

            }
          }
        }
      }
    }

    // Save the document
    $filename = 'suivi_evaluation.docx';
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($filename);

    $contenu = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');

    // Force download the Word file
    return $this->response->download($contenu, null)->setFileName($filename);

    return redirect('ihm/Rapport_Suivi_Evaluation');
  }

  //function pour exporter dans pdf
  function exporter_pdf($RESPONSABLE,$PROGRAMME_ID,$ACTION_ID,$CODE_NOMENCLATURE_BUDGETAIRE_ID,$TRIMESTRE_ID,$ANNEE_BUDGETAIRE_ID,$DATE_DEBUT='',$DATE_FIN='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RAPPORTS_SUIVI_EVALUATION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    // Chargez les options de Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);

    // Définir l'orientation paysage (landscape)
    $options->set('defaultPaperOrientation', 'landscape');

    // Instanciez Dompdf avec les options
    $dompdf = new Dompdf($options);

    // $dompdf->loadHtml('<h3><center>SUIVI EVALUATION</center></h3>');
    // Définir la largeur du tableau
    $tableWidth = '100%';

    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $critere_resp='';
    $critere_tranche='';
    $critere_imput='';
    $critere_pr='';
    $critere_act='';
    $critere_anne='';
    $critere_date="";
    $critere_date_act='';
    $ann=$this->get_annee_budgetaire();
    //$ann=2;

    if ($RESPONSABLE!=0)
    {
      $critere_resp=' AND pt.STRUTURE_RESPONSABLE_TACHE_ID='.$RESPONSABLE.' ';

      if ($PROGRAMME_ID>0)
      {
        $critere_pr=' AND prog.PROGRAMME_ID='.$PROGRAMME_ID.' ';

        if ($ACTION_ID>0)
        {
          $critere_act=' AND act.ACTION_ID='.$ACTION_ID.' ';

          if ($CODE_NOMENCLATURE_BUDGETAIRE_ID>0)
          {
            $critere_imput=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID.' ';
          }
        }
      }
    }

    //Tranche_id pour les transferts
    $tranch_transf =" ";
    if($TRIMESTRE_ID!=5)
    {
      $tranch_transf=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
    }

    if($TRIMESTRE_ID!=5)
    {
      $critere_tranche.=" AND exec.TRIMESTRE_ID=".$TRIMESTRE_ID;
    }
    if($ANNEE_BUDGETAIRE_ID>0)
    {
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    }else{
      $critere_anne.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ann;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere_date=' AND DATE_DEMANDE >="'.$DATE_DEBUT.'"';
      $critere_date_act=' AND DATE_BON_ENGAGEMENT >="'.$DATE_DEBUT.'"';
    }
    if(!empty($DATE_FIN) AND empty($DATE_DEBUT))
    {
      $critere_date=' AND DATE_DEMANDE <= "'.$DATE_FIN.'"';
      $critere_date_act=' AND DATE_BON_ENGAGEMENT <= "'.$DATE_FIN.'"';
    }
    if (!empty($DATE_FIN) AND !empty($DATE_FIN)) 
    {
      $critere_date=' AND DATE_DEMANDE BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
      $critere_date_act=' AND DATE_BON_ENGAGEMENT BETWEEN "'.$DATE_DEBUT.'" AND "'.$DATE_FIN.'"';
    }

    $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID,'ANNEE_BUDGETAIRE_ID ASC');
    $annee_budgetaire = $this->ModelPs->getRequeteOne($callpsreq, $get_ann_budg);
    $annee_dexcr=str_replace('-','/',$annee_budgetaire['ANNEE_DESCRIPTION']);

    $p_deb = '01/07/'.substr($annee_dexcr, 0, 4);
    $p_fin = date("d/m/Y");
    if($TRIMESTRE_ID==1){
      $p_deb = '01/07/'.substr($annee_dexcr, 0, 4);
      $p_fin = '30/09/'.substr($annee_dexcr, 0, 4);
    }
    if($TRIMESTRE_ID==2){
      $p_deb = '01/10/'.substr($annee_dexcr, 0, 4);
      $p_fin = '31/12/'.substr($annee_dexcr, 0, 4);
    }
    if($TRIMESTRE_ID==3){
      $p_deb = '01/01/'.substr($annee_dexcr, 0, 4);
      $p_fin = '31/03/'.substr($annee_dexcr, 0, 4);
    }
    if($TRIMESTRE_ID==4){
      $p_deb = '01/04/'.substr($annee_dexcr, 0, 4);
      $p_fin = '30/06/'.substr($annee_dexcr, 0, 4);
    }

    $periode_debut = (!empty($DATE_DEBUT)) ? date('d/m/Y',strtotime($DATE_DEBUT)) : $p_deb ;
    $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;

    if($ann != $ANNEE_BUDGETAIRE_ID)
    {
      $p_fin = '30/06/'.substr($annee_dexcr,5);
      $periode_fin = (!empty($DATE_FIN)) ? date('d/m/Y',strtotime($DATE_FIN)) : $p_fin;
    }

    // Assuming $data contains the data you want to export
    $get_responsable = $this->getBindParms('DISTINCT pt.STRUTURE_RESPONSABLE_TACHE_ID, resp.DESC_STRUTURE_RESPONSABLE_TACHE AS RESPONSABLE','ptba_tache pt JOIN inst_institutions inst ON pt.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON pt.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON pt.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON pt.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN struture_responsable_tache resp ON resp.STRUTURE_RESPONSABLE_TACHE_ID=pt.STRUTURE_RESPONSABLE_TACHE_ID','1 ' . $critere_resp, 'RESPONSABLE ASC ');
    $get_responsable=str_replace('\"', '"', $get_responsable);
    $responsable = $this->ModelPs->getRequete($callpsreq, $get_responsable);

    $html = "<html>";
    $html.= "<body>";
    //titre du document pdf
    $html.='<h3><center>CIRCUIT DES DEPENSES</center></h3>';
    $html.='<h4><center>SUIVI EVALUATION</center></h4>';
    $html.='<h5><center>EXERCICE: '.$annee_dexcr.', N° Budget  0 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Période du '.$periode_debut.' au '.$periode_fin.'</center></h5>';
    // $html.='<h6>'.$titre_document.' <br> Source de Financement 11</h6>';

    // Ajouter le tableau au HTML
    $table = '<table style="border-collapse: collapse; width: ' . $tableWidth . '" border="1">';
    $table.='<tr>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">IMPUTATION</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">TÂCHES PREVUES</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">RESULTATS ATTENDUS</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">CREDIT VOTE</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;"> TRANSFERTS </th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">APRES TRANSFERT</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">BUDGETAIRE</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">JURIDIQUE</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">LIQUIDA TION</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">ORDONNANCE MENT</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">PAIEMENT</th>
          <th style="border: 1px solid #000; white-space: nowrap; font-size: 8px;">DECAISSEMENT</th>
          </tr>';

    ///boucle pour les responsables
    foreach ($responsable as $key)
    {
      $params_infos=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache pt ON pt.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID','pt.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' ','1');
      $params_infos=str_replace('\"','"',$params_infos);
      $infos_sup=$this->ModelPs->getRequeteOne($callpsreq,$params_infos);

      ///recuperer le montant,qte voté par trimestre
      $montant_total="SUM(BUDGET_ANNUEL) AS total,SUM(Q_TOTAL) as qte_total";
      if($TRIMESTRE_ID==1)
      {
        $montant_total="SUM(BUDGET_T1) AS total,SUM(QT1) as qte_total";  
      }
      else if ($TRIMESTRE_ID==2)
      {
        $montant_total="SUM(BUDGET_T2) AS total,SUM(QT2) as qte_total";
      }
      else if ($TRIMESTRE_ID==3)
      {
        $montant_total="SUM(BUDGET_T3) AS total,SUM(QT3) as qte_total";
      }
      else if ($TRIMESTRE_ID==4)
      {
        $montant_total="SUM(BUDGET_T4) AS total,SUM(QT4) as qte_total";
      }

      $params_div=$this->getBindParms($montant_total,'ptba_tache','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' ','PTBA_TACHE_ID ASC');
      $params_div=str_replace('\"','"',$params_div);
      $total_vote=$this->ModelPs->getRequeteOne($callpsreq,$params_div);
      $BUDGET_VOTE=intval($total_vote['total']);
      $BUDGET_VOTE=!empty($BUDGET_VOTE) ? $BUDGET_VOTE : '1';
      $QUANTITE_VOTE=intval($total_vote['qte_total']);

      //Montant transferé
      $param_mont_trans = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.STRUTURE_RESPONSABLE_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' '.$tranch_transf,'1');
      $param_mont_trans=str_replace('\"','"',$param_mont_trans);
      $mont_transf=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans);
      $MONTANT_TRANSFERT=floatval($mont_transf['MONTANT_TRANSFERT']);

      //Montant receptionné
      $param_mont_recep = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.STRUTURE_RESPONSABLE_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' '.$tranch_transf,'1');
      $param_mont_recep=str_replace('\"','"',$param_mont_recep);
      $mont_recep=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep);
      $MONTANT_RECEPTION=floatval($mont_recep['MONTANT_RECEPTION']);

      $TRANSFERTS_CREDITS_RESTE=(floatval($MONTANT_TRANSFERT) - floatval($MONTANT_RECEPTION));

      if($TRANSFERTS_CREDITS_RESTE >= 0)
      {
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE;
      }
      else{
        $TRANSFERTS_CREDITS = $TRANSFERTS_CREDITS_RESTE*(-1);
      }

      $CREDIT_APRES_TRANSFERT=(floatval($BUDGET_VOTE) - floatval($MONTANT_TRANSFERT)) + floatval($MONTANT_RECEPTION);

      if($CREDIT_APRES_TRANSFERT < 0){
        $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
      }

      if($mont_transf['STRUTURE_RESPONSABLE_TACHE_ID']==$mont_recep['STRUTURE_RESPONSABLE_TACHE_ID'])
      {
        $TRANSFERTS_CREDITS = $MONTANT_TRANSFERT;
        $CREDIT_APRES_TRANSFERT = floatval($BUDGET_VOTE);
      }


      ///recuperer le montant,qte realise par trimestre
      $params_qte_realise=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.'  '.$critere_tranche,'1');
      $params_qte_realise=str_replace('\"','"',$params_qte_realise);
      $qte_realise=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise);

      $RESULTAT_REALISE=!empty($qte_realise['resultat_realise']) ? $qte_realise['resultat_realise'] : '0';
      $MONTANT_ENGAGE=!empty($infos_sup['MONTANT_ENGAGE']) ? $infos_sup['MONTANT_ENGAGE'] : '0';
      $MONTANT_JURIDIQUE=!empty($infos_sup['MONTANT_JURIDIQUE']) ? $infos_sup['MONTANT_JURIDIQUE'] : '0';
      $MONTANT_LIQUIDATION=!empty($infos_sup['MONTANT_LIQUIDATION']) ? $infos_sup['MONTANT_LIQUIDATION'] : '0';
      $MONTANT_ORDONNANCEMENT=!empty($infos_sup['MONTANT_ORDONNANCEMENT']) ? $infos_sup['MONTANT_ORDONNANCEMENT'] : '0';
      $MONTANT_PAIEMENT=!empty($infos_sup['PAIEMENT']) ? $infos_sup['PAIEMENT'] : '0';
      $MONTANT_TITRE_DECAISSEMENT=!empty($infos_sup['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup['MONTANT_TITRE_DECAISSEMENT'] : '0';
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

      $table .= '<tr>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . $key->RESPONSABLE . '</td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; "> </td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; "> </td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($BUDGET_VOTE,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($TRANSFERTS_CREDITS,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($CREDIT_APRES_TRANSFERT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ENGAGE,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_JURIDIQUE,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_LIQUIDATION,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ORDONNANCEMENT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_PAIEMENT,0,","," ") . '</td>';
      $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_TITRE_DECAISSEMENT,0,","," ") . '</td>';
      $table .= '</tr>';

      //export par rapport au programme

      $get_program=$this->getBindParms('DISTINCT prog.PROGRAMME_ID,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.''.$critere_pr,'prog.CODE_PROGRAMME ASC');
      $get_program=str_replace('\"', '"',$get_program);
      $programmes= $this->ModelPs->getRequete($callpsreq,$get_program);

      foreach ($programmes as $key_program)
      {
        $params_infos_pgm=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache pt ON pt.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID','pt.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND pt.PROGRAMME_ID='.$key_program->PROGRAMME_ID.'','1');

        $params_infos_pgm=str_replace('\"', '"', $params_infos_pgm);
        $infos_sup_pgm=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_pgm);
        $params_total_pgm=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.'','1');
        $params_total_pgm=str_replace('\"','"',$params_total_pgm);
        $total_pgm=$this->ModelPs->getRequeteOne($callpsreq,$params_total_pgm);
        $BUDGET_VOTE_PGM=intval($total_pgm['total']);
        $BUDGET_VOTE_PGM=!empty($BUDGET_VOTE_PGM) ? $BUDGET_VOTE_PGM : '1';
        $QUANTITE_VOTE_PGM=intval($total_pgm['qte_total']);

        //Montant transferé
        $param_mont_trans_prg = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$tranch_transf,'1');
        $param_mont_trans_prg=str_replace('\"','"',$param_mont_trans_prg);
        $mont_transf_prg=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_prg);
        $MONTANT_TRANSFERT_PGM=floatval($mont_transf_prg['MONTANT_TRANSFERT']);

        //Montant receptionné
        $param_mont_recep_prg = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PROGRAMME_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$tranch_transf,'1');
        $param_mont_recep_prg=str_replace('\"','"',$param_mont_recep_prg);
        $mont_recep_prg=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_prg);
        $MONTANT_RECEPTION_PGM=floatval($mont_recep_prg['MONTANT_RECEPTION']);

        $TRANSFERTS_CREDITS_RESTE_PGM=(floatval($MONTANT_TRANSFERT_PGM) - floatval($MONTANT_RECEPTION_PGM));

        if($TRANSFERTS_CREDITS_RESTE_PGM >= 0)
        {
          $TRANSFERTS_CREDITS_PGM = $TRANSFERTS_CREDITS_RESTE_PGM;
        }
        else{
          $TRANSFERTS_CREDITS_PGM = $TRANSFERTS_CREDITS_RESTE_PGM*(-1);
        }

        $CREDIT_APRES_TRANSFERT_PGM=(floatval($BUDGET_VOTE_PGM) - floatval($MONTANT_TRANSFERT_PGM)) + floatval($MONTANT_RECEPTION_PGM);

        if($CREDIT_APRES_TRANSFERT_PGM < 0){
          $CREDIT_APRES_TRANSFERT_PGM = $CREDIT_APRES_TRANSFERT_PGM*(-1);
        }

        if($mont_transf_prg['PROGRAMME_ID']==$mont_recep_prg['PROGRAMME_ID'])
        {
          $TRANSFERTS_CREDITS_PGM = $MONTANT_TRANSFERT_PGM;
          $CREDIT_APRES_TRANSFERT_PGM = floatval($BUDGET_VOTE_PGM);
        }

        ///recuperer le montant,qte realise par trimestre
        $params_qte_realise_pgm=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$critere_tranche,'1');
        $params_qte_realise_pgm=str_replace('\"','"',$params_qte_realise_pgm);
        $qte_realise_pgm=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_pgm);

        $RESULTAT_REALISE_PGM=!empty($qte_realise_pgm['resultat_realise']) ? $qte_realise_pgm['resultat_realise'] : '0';
        $MONTANT_TRANSFERT_PGM=!empty($infos_sup_pgm['MONTANT_TRANSFERT']) ? $infos_sup_pgm['MONTANT_TRANSFERT'] : '0';
        $CREDIT_APRES_TRANSFERT_PGM=!empty($infos_sup_pgm['CREDIT_APRES_TRANSFERT']) ? $infos_sup_pgm['CREDIT_APRES_TRANSFERT'] :'0';
        $MONTANT_ENGAGE_PGM=!empty($infos_sup_pgm['MONTANT_ENGAGE']) ? $infos_sup_pgm['MONTANT_ENGAGE'] : '0';
        $MONTANT_JURIDIQUE_PGM=!empty($infos_sup_pgm['MONTANT_JURIDIQUE']) ? $infos_sup_pgm['MONTANT_JURIDIQUE'] : '0';
        $MONTANT_LIQUIDATION_PGM=!empty($infos_sup_pgm['MONTANT_LIQUIDATION']) ? $infos_sup_pgm['MONTANT_LIQUIDATION'] : '0';
        $MONTANT_ORDONNANCEMENT_PGM=!empty($infos_sup_pgm['MONTANT_ORDONNANCEMENT']) ? $infos_sup_pgm['MONTANT_ORDONNANCEMENT'] : '0';
        $MONTANT_PAIEMENT_PGM=!empty($infos_sup_pgm['PAIEMENT']) ? $infos_sup_pgm['PAIEMENT'] : '0';
        $MONTANT_TITRE_DECAISSEMENT_PGM=!empty($infos_sup_pgm['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_pgm['MONTANT_TITRE_DECAISSEMENT'] : '0';
        $QUANTITE_REALISE_PGM=!empty($qte_realise_pgm['QTE_REALISE']) ? $qte_realise_pgm['QTE_REALISE'] : '0';

        $ecart_engage_pgm=$BUDGET_VOTE_PGM-$MONTANT_ENGAGE_PGM;
        $ecart_juridique_pgm=$BUDGET_VOTE_PGM-$MONTANT_JURIDIQUE_PGM;
        $ecart_liquidation_pgm=$BUDGET_VOTE_PGM-$MONTANT_LIQUIDATION_PGM;
        $ecart_ordonnancement_pgm=$BUDGET_VOTE_PGM-$MONTANT_ORDONNANCEMENT_PGM;
        $ecart_paiement_pgm=$BUDGET_VOTE_PGM-$MONTANT_PAIEMENT_PGM;
        $ecart_decaissement_pgm=$BUDGET_VOTE_PGM-$MONTANT_TITRE_DECAISSEMENT_PGM;
        $ecart_physique_pgm=$QUANTITE_VOTE_PGM-$QUANTITE_REALISE_PGM;

        $taux_engage_pgm=$MONTANT_ENGAGE_PGM*100/$BUDGET_VOTE_PGM;
        $taux_juridique_pgm=$MONTANT_JURIDIQUE_PGM*100/$BUDGET_VOTE_PGM;
        $taux_liquidation_pgm=$MONTANT_LIQUIDATION_PGM*100/$BUDGET_VOTE_PGM;
        $taux_ordonnancement_pgm=$MONTANT_ORDONNANCEMENT_PGM*100/$BUDGET_VOTE_PGM;
        $taux_paiement_pgm=$MONTANT_PAIEMENT_PGM*100/$BUDGET_VOTE_PGM;
        $taux_decaissement_pgm=$MONTANT_TITRE_DECAISSEMENT_PGM*100/$BUDGET_VOTE_PGM;

        $table .= '<tr>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; padding-left:2px; ">' . $key_program->INTITULE_PROGRAMME . '</td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; "> </td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; "> </td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($BUDGET_VOTE_PGM,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($TRANSFERTS_CREDITS_PGM,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($CREDIT_APRES_TRANSFERT_PGM,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ENGAGE_PGM,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_JURIDIQUE_PGM,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_LIQUIDATION_PGM,0,","," ") . '</td>'; 
        $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ORDONNANCEMENT_PGM,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_PAIEMENT_PGM,0,","," ") . '</td>';
        $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_TITRE_DECAISSEMENT_PGM,0,","," ") . '</td>';
        $table .= '</tr>';

        ///EXPORT PAR RAPPORT A L ACTION
        $params_actions=$this->getBindParms('DISTINCT act.ACTION_ID,act.CODE_ACTION,act.LIBELLE_ACTION','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND prog.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' '.$critere_act,'act.CODE_ACTION ASC');
        $params_actions=str_replace('\"', '"', $params_actions);
        $actions=$this->ModelPs->getRequete($callpsreq, $params_actions);

        foreach ($actions as $key_action)
        {
          $params_infos_sup_act=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' ','1');
          $params_infos_sup_act=str_replace('\"', '"', $params_infos_sup_act);
          $infos_sup_action=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_sup_act);

          $params_total_action=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' ','PTBA_TACHE_ID ASC');
          $params_total_action=str_replace('\"','"',$params_total_action);

          $total_action=$this->ModelPs->getRequeteOne($callpsreq,$params_total_action);

          $BUDGET_VOTE_ACT=intval($total_action['total']);
          $BUDGET_VOTE_ACT=!empty($BUDGET_VOTE_ACT) ? $BUDGET_VOTE_ACT : '1';
          $QUANTITE_VOTE_ACT=intval($total_action['qte_total']);


          //Montant transferé
          $param_mont_trans_act = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' '.$tranch_transf,'1');
          $param_mont_trans_act=str_replace('\"','"',$param_mont_trans_act);
          $mont_transf_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_act);
          $MONTANT_TRANSFERT_ACT=floatval($mont_transf_act['MONTANT_TRANSFERT']);

          //Montant receptionné
          $param_mont_recep_act = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.ACTION_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' '.$tranch_transf,'1');
          $param_mont_recep_act=str_replace('\"','"',$param_mont_recep_act);
          $mont_recep_act=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_act);
          $MONTANT_RECEPTION_ACT=floatval($mont_recep_act['MONTANT_RECEPTION']);

          $TRANSFERTS_CREDITS_RESTE_ACT=(floatval($MONTANT_TRANSFERT_ACT) - floatval($MONTANT_RECEPTION_ACT));

          if($TRANSFERTS_CREDITS_RESTE_ACT >= 0)
          {
            $TRANSFERTS_CREDITS_ACT = $TRANSFERTS_CREDITS_RESTE_ACT;
          }
          else{
            $TRANSFERTS_CREDITS_ACT = $TRANSFERTS_CREDITS_RESTE_ACT*(-1);
          }

          $CREDIT_APRES_TRANSFERT_ACT=(floatval($BUDGET_VOTE_ACT) - floatval($MONTANT_TRANSFERT_ACT)) + floatval($MONTANT_RECEPTION_ACT);

          if($CREDIT_APRES_TRANSFERT_ACT < 0){
            $CREDIT_APRES_TRANSFERT_ACT = $CREDIT_APRES_TRANSFERT_ACT*(-1);
          }

          if($mont_transf_act['ACTION_ID']==$mont_recep_act['ACTION_ID'])
          {
            $TRANSFERTS_CREDITS_ACT = $MONTANT_TRANSFERT_ACT;
            $CREDIT_APRES_TRANSFERT_ACT = floatval($BUDGET_VOTE_ACT);
          }


          ///recuperer le montant,qte realise par trimestre
          $params_qte_realise_action=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' '.$critere_tranche,'1');
          $params_qte_realise_action=str_replace('\"','"',$params_qte_realise_action);
          $qte_realise_action=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_action);

          $RESULTAT_REALISE_ACT=!empty($qte_realise_action['resultat_realise']) ? $qte_realise_action['resultat_realise'] : '0';
          $MONTANT_ENGAGE_ACT=!empty($infos_sup_action['MONTANT_ENGAGE']) ? $infos_sup_action['MONTANT_ENGAGE'] : '0';
          $MONTANT_JURIDIQUE_ACT=!empty($infos_sup_action['MONTANT_JURIDIQUE']) ? $infos_sup_action['MONTANT_JURIDIQUE'] : '0';
          $MONTANT_LIQUIDATION_ACT=!empty($infos_sup_action['MONTANT_LIQUIDATION']) ? $infos_sup_action['MONTANT_LIQUIDATION'] : '0';
          $MONTANT_ORDONNANCEMENT_ACT=!empty($infos_sup_action['MONTANT_ORDONNANCEMENT']) ? $infos_sup_action['MONTANT_ORDONNANCEMENT'] : '0';
          $MONTANT_PAIEMENT_ACT=!empty($infos_sup_action['PAIEMENT']) ? $infos_sup_action['PAIEMENT'] : '0';
          $MONTANT_TITRE_DECAISSEMENT_ACT=!empty($infos_sup_action['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_action['MONTANT_TITRE_DECAISSEMENT'] : '0';
          $QUANTITE_REALISE_ACT=!empty($qte_realise_action['QTE_REALISE']) ? $qte_realise_action['QTE_REALISE'] : '0';

          $ecart_engage_act=$BUDGET_VOTE_ACT-$MONTANT_ENGAGE_ACT;
          $ecart_juridique_act=$BUDGET_VOTE_ACT-$MONTANT_JURIDIQUE_ACT;
          $ecart_liquidation_act=$BUDGET_VOTE_ACT-$MONTANT_LIQUIDATION_ACT;
          $ecart_ordonnancement_act=$BUDGET_VOTE_ACT-$MONTANT_ORDONNANCEMENT_ACT;
          $ecart_paiement_act=$BUDGET_VOTE_ACT-$MONTANT_PAIEMENT_ACT;
          $ecart_decaissement_act=$BUDGET_VOTE_ACT-$MONTANT_TITRE_DECAISSEMENT_ACT;
          $ecart_physique_act=$QUANTITE_VOTE_ACT-$QUANTITE_REALISE_ACT;

          $taux_engage_act=$MONTANT_ENGAGE_ACT*100/$BUDGET_VOTE_ACT;
          $taux_juridique_act=$MONTANT_JURIDIQUE_ACT*100/$BUDGET_VOTE_ACT;
          $taux_liquidation_act=$MONTANT_LIQUIDATION_ACT*100/$BUDGET_VOTE_ACT;
          $taux_ordonnancement_act=$MONTANT_ORDONNANCEMENT_ACT*100/$BUDGET_VOTE_ACT;
          $taux_paiement_act=$MONTANT_PAIEMENT_ACT*100/$BUDGET_VOTE_ACT;
          $taux_decaissement_act=$MONTANT_TITRE_DECAISSEMENT_ACT*100/$BUDGET_VOTE_ACT;

          $table .= '<tr>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; padding-left:5px; ">' . $key_action->LIBELLE_ACTION . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; "> </td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; "> </td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($BUDGET_VOTE_ACT,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($TRANSFERTS_CREDITS_ACT,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($CREDIT_APRES_TRANSFERT_ACT,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ENGAGE_ACT,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_JURIDIQUE_ACT,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_LIQUIDATION_ACT,0,","," ") . '</td>';
          
          $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ORDONNANCEMENT_ACT,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_PAIEMENT_ACT,0,","," ") . '</td>';
          $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_TITRE_DECAISSEMENT_ACT,0,","," ") . '</td>';
          $table .= '</tr>';

          $params_imputation=$this->getBindParms('DISTINCT ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' '.$critere_imput,'ligne.CODE_NOMENCLATURE_BUDGETAIRE ASC');
          $params_imputation=str_replace('\"', '"', $params_imputation);
          $imputation=$this->ModelPs->getRequete($callpsreq, $params_imputation);

          foreach ($imputation as $key_code)
          {
            $params_infos_imput=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ','1');
            $params_infos_imput=str_replace('\"', '"', $params_infos_imput);
            $infos_sup_imputation=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_imput);
            $params_total_imput=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ','PTBA_TACHE_ID ASC');
            $params_total_imput=str_replace('\"','"',$params_total_imput);
            $total_imput=$this->ModelPs->getRequeteOne($callpsreq,$params_total_imput);

            $BUDGET_VOTE_IMPUT=intval($total_imput['total']);
            $BUDGET_VOTE_IMPUT=!empty($BUDGET_VOTE_IMPUT) ? $BUDGET_VOTE_IMPUT : '1';
            $QUANTITE_VOTE_IMPUT=intval($total_imput['qte_total']);

            //Montant transferé
            $param_mont_trans_imput = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID',' ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' '.$tranch_transf,'1');
            $param_mont_trans_imput=str_replace('\"','"',$param_mont_trans_imput);
            $mont_transf_imput=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_imput);
            $MONTANT_TRANSFERT_IMPUT=floatval($mont_transf_imput['MONTANT_TRANSFERT']);

            //Montant receptionné
            $param_mont_recep_imput = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID',' ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.'  '.$tranch_transf,'1');
            $param_mont_recep_imput=str_replace('\"','"',$param_mont_recep_imput);
            $mont_recep_imput=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_imput);
            $MONTANT_RECEPTION_IMPUT=floatval($mont_recep_imput['MONTANT_RECEPTION']);
            $TRANSFERTS_CREDITS_RESTE_IMPUT=(floatval($MONTANT_TRANSFERT_IMPUT) - floatval($MONTANT_RECEPTION_IMPUT));

            if($TRANSFERTS_CREDITS_RESTE_IMPUT >= 0)
            {
              $TRANSFERTS_CREDITS_IMPUT = $TRANSFERTS_CREDITS_RESTE_IMPUT;
            }
            else{
              $TRANSFERTS_CREDITS_IMPUT = $TRANSFERTS_CREDITS_RESTE_IMPUT*(-1);
            }

            $CREDIT_APRES_TRANSFERT_IMPUT=(floatval($BUDGET_VOTE_IMPUT) - floatval($MONTANT_TRANSFERT_IMPUT)) + floatval($MONTANT_RECEPTION_IMPUT);

            if($CREDIT_APRES_TRANSFERT_IMPUT < 0){
              $CREDIT_APRES_TRANSFERT_IMPUT = $CREDIT_APRES_TRANSFERT_IMPUT*(-1);
            }

            if($mont_transf_imput['CODE_NOMENCLATURE_BUDGETAIRE_ID']==$mont_recep_imput['CODE_NOMENCLATURE_BUDGETAIRE_ID'])
            {
              $TRANSFERTS_CREDITS_IMPUT = $MONTANT_TRANSFERT_IMPUT;
              $CREDIT_APRES_TRANSFERT_IMPUT = floatval($BUDGET_VOTE_IMPUT);
            }


            ///recuperer le montant,qte realise par trimestre
            $params_qte_realise_imput=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' '.$critere_tranche,'1');
            $params_qte_realise_imput=str_replace('\"','"',$params_qte_realise_imput);
            $qte_realise_imput=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_imput);

            $RESULTAT_REALISE_IMPUT=!empty($qte_realise_imput['resultat_realise']) ? $qte_realise_imput['resultat_realise'] : '0';
            $MONTANT_ENGAGE_IMPUT=!empty($infos_sup_imputation['MONTANT_ENGAGE']) ? $infos_sup_imputation['MONTANT_ENGAGE'] : '0';
            $MONTANT_JURIDIQUE_IMPUT=!empty($infos_sup_imputation['MONTANT_JURIDIQUE']) ? $infos_sup_imputation['MONTANT_JURIDIQUE'] : '0';
            $MONTANT_LIQUIDATION_IMPUT=!empty($infos_sup_imputation['MONTANT_LIQUIDATION']) ? $infos_sup_imputation['MONTANT_LIQUIDATION'] : '0';
            $MONTANT_ORDONNANCEMENT_IMPUT=!empty($infos_sup_imputation['MONTANT_ORDONNANCEMENT']) ? $infos_sup_imputation['MONTANT_ORDONNANCEMENT'] : '0';
            $MONTANT_PAIEMENT_IMPUT=!empty($infos_sup_imputation['PAIEMENT']) ? $infos_sup_imputation['PAIEMENT'] : '0';
            $MONTANT_TITRE_DECAISSEMENT_IMPUT=!empty($infos_sup_imputation['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_imputation['MONTANT_TITRE_DECAISSEMENT'] : '0';
            $QUANTITE_REALISE_IMPUT=!empty($qte_realise_imput['QTE_REALISE']) ? $qte_realise_imput['QTE_REALISE'] : '0';

            $ecart_engage_imput=$BUDGET_VOTE_IMPUT-$MONTANT_ENGAGE_IMPUT;
            $ecart_juridique_imput=$BUDGET_VOTE_IMPUT-$MONTANT_JURIDIQUE_IMPUT;
            $ecart_liquidation_imput=$BUDGET_VOTE_IMPUT-$MONTANT_LIQUIDATION_IMPUT;
            $ecart_ordonnancement_imput=$BUDGET_VOTE_IMPUT-$MONTANT_ORDONNANCEMENT_IMPUT;
            $ecart_paiement_imput=$BUDGET_VOTE_IMPUT-$MONTANT_PAIEMENT_IMPUT;
            $ecart_decaissement_imput=$BUDGET_VOTE_IMPUT-$MONTANT_TITRE_DECAISSEMENT_IMPUT;
            $ecart_physique_imput=$QUANTITE_VOTE_IMPUT-$QUANTITE_REALISE_IMPUT;

            $taux_engage_imput=$MONTANT_ENGAGE_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_juridique_imput=$MONTANT_JURIDIQUE_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_liquidation_imput=$MONTANT_LIQUIDATION_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_ordonnancement_imput=$MONTANT_ORDONNANCEMENT_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_paiement_imput=$MONTANT_PAIEMENT_IMPUT*100/$BUDGET_VOTE_IMPUT;
            $taux_decaissement_imput=$MONTANT_TITRE_DECAISSEMENT_IMPUT*100/$BUDGET_VOTE_IMPUT;

            $table .= '<tr>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; padding-left:7px;">' . $key_code->CODE_NOMENCLATURE_BUDGETAIRE . '</td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; "> </td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; "> </td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($BUDGET_VOTE_IMPUT,0,","," ") . '</td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($TRANSFERTS_CREDITS_IMPUT,0,","," ") . '</td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($CREDIT_APRES_TRANSFERT_IMPUT,0,","," ") . '</td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ENGAGE_IMPUT,0,","," ") . '</td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_JURIDIQUE_IMPUT,0,","," ") . '</td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_LIQUIDATION_IMPUT,0,","," ") . '</td>';
            
            $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ORDONNANCEMENT_IMPUT,0,","," ") . '</td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_PAIEMENT_IMPUT,0,","," ") . '</td>';
            $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_TITRE_DECAISSEMENT_IMPUT,0,","," ") . '</td>';
            $table .= '</tr>';

            ///export des activites
            $params_activite=$this->getBindParms('DISTINCT PTBA_TACHE_ID,DESC_TACHE,RESULTAT_ATTENDUS_TACHE','ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' ','DESC_TACHE ASC');
            $params_activite=str_replace('\"', '"', $params_activite);
            $activites=$this->ModelPs->getRequete($callpsreq, $params_activite);

            foreach ($activites as $key_activ)
            {
              $params_infos_activ=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) AS MONTANT_ENGAGE,SUM(ebet.MONTANT_ENG_JURIDIQUE) AS MONTANT_JURIDIQUE,SUM(ebet.MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION,SUM(ebet.MONTANT_DECAISSEMENT) AS MONTANT_DECAISSEMENT,SUM(ebet.MONTANT_ORDONNANCEMENT) AS MONTANT_ORDONNANCEMENT,SUM(ebet.MONTANT_PAIEMENT) AS PAIEMENT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba_tache.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID,'1');
              $params_infos_activ=str_replace('\"', '"', $params_infos_activ);
              $infos_sup_activite=$this->ModelPs->getRequeteOne($callpsreq, $params_infos_activ);
              $params_total_activ=$this->getBindParms($montant_total,'ptba_tache JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID',' ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID,'PTBA_TACHE_ID ASC');
              $params_total_activ=str_replace('\"','"',$params_total_activ);
              $total_activite=$this->ModelPs->getRequeteOne($callpsreq,$params_total_activ);
              $BUDGET_VOTE_TACHE=intval($total_activite['total']);
              $BUDGET_VOTE_TACHE=!empty($BUDGET_VOTE_TACHE) ? $BUDGET_VOTE_TACHE : '1';
              $QUANTITE_VOTE_ACTIV=intval($total_activite['qte_total']);

              //Montant transferé
              $param_mont_trans_tache = $this->getBindParms('SUM(MONTANT_TRANSFERT) AS MONTANT_TRANSFERT,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_TRANSFERT = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID.' '.$tranch_transf,'1');
              $param_mont_trans_tache=str_replace('\"','"',$param_mont_trans_tache);
              $mont_transf_tache=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_trans_tache);
              $MONTANT_TRANSFERT_TACHE=floatval($mont_transf_tache['MONTANT_TRANSFERT']);

              //Montant receptionné
              $param_mont_recep_tache = $this->getBindParms('SUM(MONTANT_RECEPTION) AS MONTANT_RECEPTION,ptba.PTBA_TACHE_ID','transfert_historique_transfert JOIN ptba_tache ptba ON transfert_historique_transfert.PTBA_TACHE_ID_RECEPTION = ptba.PTBA_TACHE_ID','ptba.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba.ACTION_ID='.$key_action->ACTION_ID.' AND ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.' AND ptba.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID.' '.$tranch_transf,'1');
              $param_mont_recep_tache=str_replace('\"','"',$param_mont_recep_tache);
              $mont_recep_tache=$this->ModelPs->getRequeteOne($callpsreq,$param_mont_recep_tache);
              $MONTANT_RECEPTION_TACHE=floatval($mont_recep_tache['MONTANT_RECEPTION']);
              $TRANSFERTS_CREDITS_RESTE_TACHE=(floatval($MONTANT_TRANSFERT_TACHE) - floatval($MONTANT_RECEPTION_TACHE));

              if($TRANSFERTS_CREDITS_RESTE_TACHE >= 0)
              {
                $TRANSFERTS_CREDITS_TACHE = $TRANSFERTS_CREDITS_RESTE_TACHE;
              }
              else{
                $TRANSFERTS_CREDITS_TACHE = $TRANSFERTS_CREDITS_RESTE_TACHE*(-1);
              }

              $CREDIT_APRES_TRANSFERT_TACHE=(floatval($BUDGET_VOTE_TACHE) - floatval($MONTANT_TRANSFERT_TACHE)) + floatval($MONTANT_RECEPTION_TACHE);

              if($CREDIT_APRES_TRANSFERT_TACHE < 0){
                $CREDIT_APRES_TRANSFERT_TACHE = $CREDIT_APRES_TRANSFERT_TACHE*(-1);
              }

              if($mont_transf_tache['PTBA_TACHE_ID']==$mont_recep_tache['PTBA_TACHE_ID'])
              {
                $TRANSFERTS_CREDITS_TACHE = $MONTANT_TRANSFERT_TACHE;
                $CREDIT_APRES_TRANSFERT_TACHE = floatval($BUDGET_VOTE_TACHE);
              }

              ///recuperer le montant,qte realise par trimestre
              $params_qte_realise_activ=$this->getBindParms('SUM(ebet.MONTANT_ENG_BUDGETAIRE) resultat_realise,SUM(ebet.QTE) QTE_REALISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON ebtd.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_programmes prog ON ptba_tache.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba_tache.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID','1 AND ptba_tache.STRUTURE_RESPONSABLE_TACHE_ID='.$key->STRUTURE_RESPONSABLE_TACHE_ID.' AND ptba_tache.PROGRAMME_ID='.$key_program->PROGRAMME_ID.' AND ptba_tache.ACTION_ID='.$key_action->ACTION_ID.' AND ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID='.$key_code->CODE_NOMENCLATURE_BUDGETAIRE_ID.'  AND ptba_tache.PTBA_TACHE_ID='.$key_activ->PTBA_TACHE_ID.' '.$critere_tranche,'1');
              $params_qte_realise_activ=str_replace('\"','"',$params_qte_realise_activ);
              $params_qte_realise_activ=str_replace('\"','"',$params_qte_realise_activ);
              $qte_realise_activ=$this->ModelPs->getRequeteOne($callpsreq,$params_qte_realise_activ);

              $RESULTAT_REALISE_ACTIV=!empty($qte_realise_activ['resultat_realise']) ? $qte_realise_activ['resultat_realise'] : '0';
              $MONTANT_ENGAGE_ACTIV=!empty($infos_sup_activite['MONTANT_ENGAGE']) ? $infos_sup_activite['MONTANT_ENGAGE'] : '0';
              $MONTANT_JURIDIQUE_ACTIV=!empty($infos_sup_activite['MONTANT_JURIDIQUE']) ? $infos_sup_activite['MONTANT_JURIDIQUE'] : '0';
              $MONTANT_LIQUIDATION_ACTIV=!empty($infos_sup_activite['MONTANT_LIQUIDATION']) ? $infos_sup_activite['MONTANT_LIQUIDATION'] : '0';
              $MONTANT_ORDONNANCEMENT_ACTIV=!empty($infos_sup_activite['MONTANT_ORDONNANCEMENT']) ? $infos_sup_activite['MONTANT_ORDONNANCEMENT'] : '0';
              $MONTANT_PAIEMENT_ACTIV=!empty($infos_sup_activite['PAIEMENT']) ? $infos_sup_activite['PAIEMENT'] : '0';
              $MONTANT_TITRE_DECAISSEMENT_ACTIV=!empty($infos_sup_activite['MONTANT_TITRE_DECAISSEMENT']) ? $infos_sup_activite['MONTANT_TITRE_DECAISSEMENT'] : '0';

              $QUANTITE_REALISE_ACTIV=!empty($qte_realise_activ['QTE_REALISE']) ? $qte_realise_activ['QTE_REALISE'] : '0';

              $ecart_engage_activ=$BUDGET_VOTE_TACHE-$MONTANT_ENGAGE_ACTIV;
              $ecart_juridique_activ=$BUDGET_VOTE_TACHE-$MONTANT_JURIDIQUE_ACTIV;
              $ecart_liquidation_activ=$BUDGET_VOTE_TACHE-$MONTANT_LIQUIDATION_ACTIV;
              $ecart_ordonnancement_activ=$BUDGET_VOTE_TACHE-$MONTANT_ORDONNANCEMENT_ACTIV;
              $ecart_paiement_activ=$BUDGET_VOTE_TACHE-$MONTANT_PAIEMENT_ACTIV;
              $ecart_decaissement_activ=$BUDGET_VOTE_TACHE-$MONTANT_TITRE_DECAISSEMENT_ACTIV;
              $ecart_physique_activ=$QUANTITE_VOTE_ACTIV-$QUANTITE_REALISE_ACTIV;

              $taux_engage_activ=$MONTANT_ENGAGE_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_juridique_activ=$MONTANT_JURIDIQUE_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_liquidation_activ=$MONTANT_LIQUIDATION_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_ordonnancement_activ=$MONTANT_ORDONNANCEMENT_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_paiement_activ=$MONTANT_PAIEMENT_ACTIV*100/$BUDGET_VOTE_TACHE;
              $taux_decaissement_activ=$MONTANT_TITRE_DECAISSEMENT_ACTIV*100/$BUDGET_VOTE_TACHE;

              $DESC_TACHE = preg_replace('/[^a-zA-Z0-9_\p{L}\s]/u', '',$key_activ->DESC_TACHE);

              $table .= '<tr>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; "> </td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">'.$DESC_TACHE.'</td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">'.$key_activ->RESULTAT_ATTENDUS_TACHE.'</td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($BUDGET_VOTE_TACHE,0,","," ") . '</td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($TRANSFERTS_CREDITS_TACHE,0,","," ") . '</td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($CREDIT_APRES_TRANSFERT_TACHE,0,","," ") . '</td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ENGAGE_ACTIV,0,","," ") . '</td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_JURIDIQUE_ACTIV,0,","," ") . '</td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_LIQUIDATION_ACTIV,0,","," ") . '</td>'; 
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_ORDONNANCEMENT_ACTIV,0,","," ") . '</td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_PAIEMENT_ACTIV,0,","," ") . '</td>';
              $table .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($MONTANT_TITRE_DECAISSEMENT_ACTIV,0,","," ") . '</td>';
              $table .= '</tr>';
            }
          }
        }

      }
    }
    $table .= '</table';
    $html.=$table;
    $html.= "</body>";
    $html.='</html>';

    // Charger le contenu HTML
    $dompdf->loadHtml($html);

    // Définir la taille et l'orientation du papier
    $dompdf->setPaper('A4', 'landscape');

    // Rendre le PDF (par défaut, il sera généré dans le répertoire système temporaire)
    $dompdf->render();
    // Télécharger le PDF

    // Envoyer le fichier PDF en tant que téléchargement
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="suivi_evaluation.pdf"');
    echo $dompdf->output();

    // $dompdf->stream('suivi evaluation.pdf', ['Attachment' => 'Suivi Evaluation']);
    // return redirect('ihm/Rapport_Suivi_Evaluation');
  }    
}
?>