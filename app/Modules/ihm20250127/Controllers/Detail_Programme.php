<?php

/**NDERAGAKURA Alain Charbel
*Titre:Modification / crypter l'id
*Numero de telephone: (+257) 62003522
*Email: charbel@mediabox.bi
*Date: 17 Novembre,2023
**/
namespace App\Modules\ihm\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
class Detail_Programme extends BaseController
{
  protected $session;
  protected $ModelPs;

  public function __construct()
  { 
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
  }

  function index($id)
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    } 
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data = $this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $bind_id_exist = $this->getBindParms('PROGRAMME_ID','inst_institutions_programmes','MD5(PROGRAMME_ID)="'.$id.'"','1');
    $bind_id_exist=str_replace('\\','',$bind_id_exist);
    $id_exist = $this->ModelPs->getRequeteOne($psgetrequete, $bind_id_exist);
    if(empty($id_exist)){
      return redirect('Login_Ptba/do_logout');
    }
    else
    {
      $get_programme ='SELECT progr.PROGRAMME_ID,progr.CODE_PROGRAMME,progr.INTITULE_PROGRAMME,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION  FROM inst_institutions_programmes progr JOIN inst_institutions inst ON inst.INSTITUTION_ID=progr.INSTITUTION_ID WHERE 1 AND MD5(progr.PROGRAMME_ID) ="'.$id.'"';
      $get_programme=str_replace('\\','',$get_programme);
      $get_programme = "CALL `getTable`('".$get_programme."');";
      $data['programme'] = $this->ModelPs->getRequeteOne($get_programme);

      $bind_tranche = $this->getBindParms('`TRANCHE_ID`,`CODE_TRANCHE`,CONCAT(`DATE_DEBUT`,"-",date_format(now(),"%Y")) as debut,CONCAT(`DATE_FIN`,"-",date_format(now(),"%Y")) as fin','op_tranches','1','`TRANCHE_ID`');
      $bind_tranche = str_replace('\"','"',$bind_tranche);
      $tranches = $this->ModelPs->getRequete($psgetrequete,$bind_tranche);

      //vote
      $bind_montant_vote = $this->getBindParms('SUM(`T1`) as T1,SUM(`T2`) as T2,SUM(`T3`) as T3,SUM(`T4`) as T4','ptba JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID','prog.CODE_PROGRAMME="'.$data['programme']['CODE_PROGRAMME'].'" ','SUM(`T1`)');
      $bind_montant_vote = str_replace('\"','"',$bind_montant_vote);
      $data['montant_vote'] = $this->ModelPs->getRequeteOne($psgetrequete,$bind_montant_vote);
      $data['montant_total'] = $data['montant_vote']['T1']+$data['montant_vote']['T2']+$data['montant_vote']['T3']+$data['montant_vote']['T4'];

      //execute 
      $mont_exe = "SELECT SUM(racc.MONTANT_RACCROCHE) as EXECUTEE,tranche.CODE_TRANCHE FROM execution_budgetaire_raccrochage_activite racc JOIN op_tranches tranche ON tranche.TRANCHE_ID=racc.TRIMESTRE_ID JOIN ptba ON racc.PTBA_ID=ptba.PTBA_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID WHERE 1 AND prog.CODE_PROGRAMME='".$data['programme']['CODE_PROGRAMME']."' GROUP BY tranche.CODE_TRANCHE";
      $mont_exe = 'CALL `getTable`("'.$mont_exe.'");';
      $execute = $this->ModelPs->getRequete($mont_exe);

      $executeMoney1 = 0;
      $executeMoney2 = 0;
      $executeMoney3 = 0;
      $executeMoney4 = 0;
      $reste1 = $data['montant_vote']['T1'];
      $reste2 = $data['montant_vote']['T2'];
      $reste3 = $data['montant_vote']['T3'];
      $reste4 = $data['montant_vote']['T4'];
      if(!empty($execute))
      {
        foreach($execute as $value)
        {
          $bind_tranch = $this->getBindParms('CODE_TRANCHE','op_tranches','`CODE_TRANCHE`="'.$value->CODE_TRANCHE.'"','CODE_TRANCHE');
          $bind_tranch = str_replace('\"','"',$bind_tranch);
          $tranc = $this->ModelPs->getRequeteOne($psgetrequete,$bind_tranch);

          if($tranc['CODE_TRANCHE'] == 'T1')
          {
            $executeMoney1 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste1 = $data['montant_vote']['T1'] - floatval($executeMoney1);
          }
          else if($tranc['CODE_TRANCHE'] == 'T2')
          {
            $executeMoney2 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste2 = $data['montant_vote']['T2'] - floatval($executeMoney2); 
          }
          else if($tranc['CODE_TRANCHE'] == 'T3')
          {
            $executeMoney3 =(!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste3=($data['montant_vote']['T3'] -  floatval($executeMoney3));
          }
          else
          {
            $executeMoney4 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste4 = $data['montant_vote']['T4'] -  floatval($executeMoney4);
          }
        }
      }

      $data['tot_exe'] = $executeMoney1+$executeMoney2+$executeMoney3+$executeMoney4;
      $data['restant'] = $reste1+$reste2+$reste3+$reste4;
      $data['reste1']=$reste1;
      $data['reste2']=$reste2;
      $data['reste3']=$reste3;
      $data['reste4']=$reste4;
      $data['executeMoney1']=$executeMoney1;
      $data['executeMoney2']=$executeMoney2;
      $data['executeMoney3']=$executeMoney3;
      $data['executeMoney4']=$executeMoney4;
      // fin

      // chiffre dans les tabs
      $nbre_action = 'SELECT prog.PROGRAMME_ID,prog.CODE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,prog.OBJECTIF_DU_PROGRAMME,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4,exe.LIQUIDATION FROM `inst_institutions_programmes` prog JOIN ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN execution_budgetaire_new exe ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=exe.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID WHERE 1 AND MD5(prog.PROGRAMME_ID) = "'.$id.'" GROUP BY prog.PROGRAMME_ID,prog.CODE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,prog.OBJECTIF_DU_PROGRAMME';
      $nbre_action=str_replace('\\','',$nbre_action);
      $nbre_action = "CALL `getTable`('".$nbre_action."');";
      $get_nbre_action= $this->ModelPs->getRequete($nbre_action);
      $data['action'] = (!empty($get_nbre_action)) ? count($get_nbre_action):0;

      $nbre_activite = 'SELECT ptba.PTBA_ID, prog.PROGRAMME_ID,ptba.CODES_PROGRAMMATIQUE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ptba.ACTIVITES,ptba.T1 AS T1,ptba.T2 AS T2,ptba.T3 AS T3,ptba.T4 AS T4 FROM `inst_institutions_programmes` prog JOIN ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(prog.PROGRAMME_ID) = "'.$id.'"';
      $nbre_activite=str_replace('\\','',$nbre_activite);
      $nbre_activite = "CALL `getTable`('".$nbre_activite."');";
      $get_nbre_activite = $this->ModelPs->getRequete($nbre_activite);
      $data['nbre_activite'] = (!empty($get_nbre_activite)) ? count($get_nbre_activite):0;

      $nbre_ligne= 'SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE,progr.PROGRAMME_ID,progr.CODE_PROGRAMME,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4 FROM inst_institutions_programmes progr JOIN ptba ON ptba.PROGRAMME_ID=progr.PROGRAMME_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(progr.PROGRAMME_ID) ="'.$id.'" GROUP BY progr.PROGRAMME_ID,progr.CODE_PROGRAMME,ptba.CODE_NOMENCLATURE_BUDGETAIRE';
      $nbre_ligne = "CALL `getTable`('".$nbre_ligne."');";
      $nbre_ligne=str_replace('\\','',$nbre_ligne);
      $get_nbre_ligne = $this->ModelPs->getRequete($nbre_ligne);
      $data['nbre_ligne']=(!empty($get_nbre_ligne)) ? count($get_nbre_ligne):0;
      //fin
      return view('App\Modules\ihm\Views\Detail_Programme_View',$data);
    }
  }

  //liste des actions
  function liste_action($id)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $query_principal="SELECT prog.PROGRAMME_ID,prog.CODE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,prog.OBJECTIF_DU_PROGRAMME,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4 FROM `inst_institutions_programmes` prog JOIN ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID WHERE 1 AND prog.PROGRAMME_ID = ".$id." GROUP BY prog.PROGRAMME_ID,prog.CODE_PROGRAMME,act.CODE_ACTION,act.LIBELLE_ACTION,prog.OBJECTIF_DU_PROGRAMME";

    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('prog.PROGRAMME_ID','act.CODE_ACTION','act.LIBELLE_ACTION','prog.OBJECTIF_DU_PROGRAMME','ptba.T1',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY prog.CODE_PROGRAMME ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (prog.CODE_PROGRAMME LIKE '%$var_search%' OR act.CODE_ACTION LIKE '%$var_search%' OR ptba.OBJECTIF_DU_PROGRAMME LIKE '%$var_search%')"):'';

    $critaire = '';
    $query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.' '.$limit;

    $query_filter = $query_principal.' '.$search.' '.$critaire;
    $requete='CALL `getList`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      $montant_exe ="SELECT act.CODE_ACTION,SUM(racc.MONTANT_RACCROCHE) AS MONTANT_RACCROCHE FROM execution_budgetaire_raccrochage_activite racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID  WHERE 1 AND act.CODE_ACTION= '".$info->CODE_ACTION."' GROUP BY act.CODE_ACTION";
      $montant_exe='CALL `getList`("'.$montant_exe.'")';
      $get_money = $this->ModelPs->getRequeteOne($montant_exe);
      $montant = (!empty($get_money['MONTANT_RACCROCHE'])) ? $get_money['MONTANT_RACCROCHE'] :0;

      $montant_vote=$info->T1+$info->T2+$info->T3+$info->T4;
      $post=array();
      $post[]=!empty($info->CODE_ACTION) ? $info->CODE_ACTION : 'N/A';
      $post[]=!empty($info->LIBELLE_ACTION) ? $info->LIBELLE_ACTION : 'N/A';
      $post[]=!empty($info->OBJECTIF_DU_PROGRAMME) ? $info->OBJECTIF_DU_PROGRAMME : 'N/A';
      $post[]=number_format($montant_vote,0,","," ").' BIF';
      $post[]=number_format($montant,0,","," ").' BIF';
      $data[]=$post;  
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

  //liste des actions
  function liste_activite($id)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $query_principal="SELECT ptba.PTBA_ID, prog.PROGRAMME_ID,ptba.CODES_PROGRAMMATIQUE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ptba.CODE_NOMENCLATURE_BUDGETAIRE,ptba.ACTIVITES,ptba.T1 AS T1,ptba.T2 AS T2,ptba.T3 AS T3,ptba.T4 AS T4 FROM `inst_institutions_programmes` prog JOIN ptba ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND prog.PROGRAMME_ID =".$id."";

    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('prog.PROGRAMME_ID','ptba.CODES_PROGRAMMATIQUE','ptba.ACTIVITES','ptba.T1','1');

    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .' '.$_POST['order']['0']['dir'] : ' ORDER BY prog.PROGRAMME_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (prog.CODES_PROGRAMMATIQUE LIKE '%$var_search%' OR ptba.ACTIVITES LIKE '%$var_search%')"):'';

    $critaire = '';
    $query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.' '.$limit;

    $query_filter = $query_principal.' '.$search.' '.$critaire;
    $requete='CALL `getList`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      $montant_execute ="SELECT PTBA_ID,MONTANT_RACCROCHE FROM execution_budgetaire_raccrochage_activite WHERE 1 AND PTBA_ID =".$info->PTBA_ID;

      $montant_execute='CALL `getList`("'.$montant_execute.'")';
      $get_money = $this->ModelPs->getRequeteOne($montant_execute);
      $montant_execute = (!empty($get_money['MONTANT_RACCROCHE'])) ? $get_money['MONTANT_RACCROCHE'] :0;

      $montant_vote=$info->T1+$info->T2+$info->T3+$info->T4;
      $post=array();
      $post[]=!empty($info->CODE_NOMENCLATURE_BUDGETAIRE) ? $info->CODE_NOMENCLATURE_BUDGETAIRE : 'N/A';
      $post[]=!empty($info->CODE_NOMENCLATURE_BUDGETAIRE) ? $info->CODE_NOMENCLATURE_BUDGETAIRE : 'N/A';   
      $post[]=!empty($info->CODES_PROGRAMMATIQUE) ? $info->CODES_PROGRAMMATIQUE : 'N/A';
      $post[]=!empty($info->CODES_PROGRAMMATIQUE) ? $info->CODES_PROGRAMMATIQUE : 'N/A';
      $post[]=$info->ACTIVITES;
      $post[]=number_format($montant_vote,0,","," ").' BIF';
      $post[]= number_format($montant_execute,0,',',' ').' BIF';
      $data[]=$post;  
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

  public function  liste_ligne_budget($id)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_PTBA_PROGRAMMES')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $query_principal="SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE,progr.PROGRAMME_ID,progr.CODE_PROGRAMME,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4 FROM inst_institutions_programmes progr JOIN ptba ON ptba.PROGRAMME_ID=progr.PROGRAMME_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND progr.PROGRAMME_ID =".$id." GROUP BY progr.PROGRAMME_ID,progr.CODE_PROGRAMME,ligne.CODE_NOMENCLATURE_BUDGETAIRE";
    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }
    $order_by='';
    $order_column='';
    $order_column= array('ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba.T1','1');
    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ligne.CODE_NOMENCLATURE_BUDGETAIRE ASC';
    $search = !empty($_POST['search']['value']) ?  (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba.T1 LIKE '%$var_search%')"):'';
    $critaire = '';
    $query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.' '.$limit;
    $query_filter = $query_principal.' '.$search.' '.$critaire;
    $requete='CALL `getList`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      $montant ="SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE,SUM(racc.MONTANT_RACCROCHE) AS MONTANT_RACCROCHE FROM execution_budgetaire_raccrochage_activite racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND ligne.CODE_NOMENCLATURE_BUDGETAIRE= '".$info->CODE_NOMENCLATURE_BUDGETAIRE."' GROUP BY ligne.CODE_NOMENCLATURE_BUDGETAIRE";
      $montant='CALL `getList`("'.$montant.'")';
      $get_money = $this->ModelPs->getRequeteOne($montant);
      $montant = (!empty($get_money['MONTANT_RACCROCHE'])) ? $get_money['MONTANT_RACCROCHE'] :0;
      $montant_vote=$info->T1+$info->T2+$info->T3+$info->T4;
      $post=array();
      $post[]=!empty($info->CODE_NOMENCLATURE_BUDGETAIRE) ? $info->CODE_NOMENCLATURE_BUDGETAIRE : 'N/A';
      $post[]=number_format($montant_vote,0,","," ").' BIF';
      $post[]=number_format($montant,0,","," ").' BIF';
      $data[]=$post;  
    }

    $requeteqp='CALL `getList`("'.$query_principal.'")';
    $recordsTotal = $this->ModelPs->datatable( $requeteqp);
    $requeteqf='CALL `getList`("'.$query_filter.'")';
    $recordsFiltered = $this->ModelPs->datatable($requeteqf);
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }

  public function getBindParms($columnselect,$table,$where,$orderby)
  {
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }
}
?>