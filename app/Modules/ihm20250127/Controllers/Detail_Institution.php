<?php

#modifié by claude@mediabox.bi on 23th september
#Ajout du rapport 

/*
*Modifié par Charbel
*le 16/10/2023
* charbel@mediabox.bi
*/
/*
*Modifié par Charbel
*le 17/11/2023
* charbel@mediabox.bi
*/
namespace App\Modules\ihm\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Detail_Institution extends BaseController
{
  protected $session;
  protected $ModelPs;
  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
  }

  function index($id=0)
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data = $this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $bind_id_exist = $this->getBindParms('PROGRAMME_ID','inst_institutions_programmes','MD5(PROGRAMME_ID)="'.$id.'"','1');
    $bind_id_exist=str_replace('\\','',$bind_id_exist);
    $id_exist = $this->ModelPs->getRequeteOne($psgetrequete,$bind_id_exist);
    if(empty($id_exist)){
      return redirect('Login_Ptba/do_logout');
    }
    else
    {
    $insti = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1 AND MD5(INSTITUTION_ID) ="'.$id.'"';
      $insti = str_replace('\\','',$insti);
      $insti = "CALL `getTable`('".$insti."');"; 
      $data['get_institution'] = $this->ModelPs->getRequeteOne($insti);
      // getBindParms liste_activite
      $bind_tranche = $this->getBindParms('`TRANCHE_ID`,`CODE_TRANCHE`,CONCAT(`DATE_DEBUT`,"-",date_format(now(),"%Y")) as debut,CONCAT(`DATE_FIN`,"-",date_format(now(),"%Y")) as fin','op_tranches','1','`TRANCHE_ID`');
      $bind_tranche = str_replace('\"','"',$bind_tranche);
      $tranches = $this->ModelPs->getRequete($psgetrequete,$bind_tranche);
      //montant voté
      $bind_montant_vote = $this->getBindParms('SUM(`T1`) as T1,SUM(`T2`) as T2,SUM(`T3`) as T3,SUM(`T4`) as T4','ptba JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID','ptba.INSTITUTION_ID='.$data['get_institution']['INSTITUTION_ID'].'','SUM(`T1`)');
      $bind_montant_vote = str_replace('\"','"',$bind_montant_vote);
      $data['montant_vote'] = $this->ModelPs->getRequeteOne($psgetrequete,$bind_montant_vote);
      $data['montant_total'] = $data['montant_vote']['T1']+$data['montant_vote']['T2']+$data['montant_vote']['T3']+$data['montant_vote']['T4'];
      //montant executer par tranche
      $mont_exe ="SELECT SUM(racc.MONTANT_RACCROCHE) as EXECUTEE,tranche.CODE_TRANCHE FROM execution_budgetaire_raccrochage_activite racc JOIN op_tranches tranche ON tranche.TRANCHE_ID=racc.TRIMESTRE_ID   WHERE 1 AND MD5(racc.INSTITUTION_ID)='".$id."' GROUP BY tranche.CODE_TRANCHE";
      $mont_exe = str_replace('\\','',$mont_exe);
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
        foreach ($execute as $value)
        {
          $bind_tranch = $this->getBindParms('CODE_TRANCHE','op_tranches','`CODE_TRANCHE`="'.$value->CODE_TRANCHE.'" ','CODE_TRANCHE');
          $bind_tranch = str_replace('\"','"',$bind_tranch);
          $tranc = $this->ModelPs->getRequeteOne($psgetrequete,$bind_tranch);

          if($tranc['CODE_TRANCHE']=='T1')
          {
            $executeMoney1 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste1 = $data['montant_vote']['T1'] - floatval($executeMoney1);
          }
          else if ($tranc['CODE_TRANCHE'] == 'T2')
          {
            $executeMoney2 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste2 = $data['montant_vote']['T2'] - floatval($executeMoney2); 
          }
          else if ($tranc['CODE_TRANCHE'] == 'T3')
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

      //  les chiffres sur les tabs
      $nbre_program ='SELECT progr.PROGRAMME_ID,inst.INSTITUTION_ID,inst.CODE_INSTITUTION,progr.CODE_PROGRAMME,progr.INTITULE_PROGRAMME,progr.OBJECTIF_DU_PROGRAMME,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4 FROM inst_institutions inst JOIN ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes progr ON progr.INSTITUTION_ID=inst.INSTITUTION_ID WHERE 1 AND MD5(inst.INSTITUTION_ID) = "'.$id.'" GROUP BY progr.PROGRAMME_ID,inst.INSTITUTION_ID,inst.CODE_INSTITUTION,progr.CODE_PROGRAMME,progr.INTITULE_PROGRAMME,progr.OBJECTIF_DU_PROGRAMME';
      $nbre_program = str_replace('\\','',$nbre_program);
      $nbre_program = "CALL `getTable`('".$nbre_program."');";
      $get_nbre_program = $this->ModelPs->getRequete($nbre_program);
      $data['nbre_program']=(!empty($get_nbre_program)) ? count($get_nbre_program):0;

      $nbre_action = 'SELECT inst.INSTITUTION_ID,inst.CODE_INSTITUTION,ptba.CODE_ACTION,ptba.LIBELLE_ACTION,ptba.OBJECTIF_ACTION,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4 FROM inst_institutions inst JOIN ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID WHERE 1 AND MD5(inst.INSTITUTION_ID) = "'.$id.'" GROUP BY inst.INSTITUTION_ID,inst.CODE_INSTITUTION,act.CODE_ACTION,act.LIBELLE_ACTION,act.OBJECTIF_ACTION';
      $nbre_action = str_replace('\\','',$nbre_action);
      $nbre_action = "CALL `getTable`('".$nbre_action."');";
      $get_nbre_action=$this->ModelPs->getRequete($nbre_action);
      $data['action']=(!empty($get_nbre_action)) ? count($get_nbre_action):0;

      $nbre_activite = 'SELECT ptba.PTBA_ID, inst.INSTITUTION_ID,inst.CODE_INSTITUTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ptba.CODES_PROGRAMMATIQUE,ptba.CODES_PROGRAMMATIQUE,ptba.ACTIVITES, T1,T2,T3, T4 FROM inst_institutions inst JOIN ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(inst.INSTITUTION_ID) ="'.$id.'"';
      $nbre_activite = str_replace('\\','',$nbre_activite);
      $nbre_activite = "CALL `getTable`('".$nbre_activite."');";
      $get_nbre_activite = $this->ModelPs->getRequete($nbre_activite);
      $data['activite']=(!empty($get_nbre_activite)) ? count($get_nbre_activite):0;

      $nbre_ligne= 'SELECT inst.INSTITUTION_ID,inst.CODE_INSTITUTION,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4,ligne.CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions inst JOIN ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(inst.INSTITUTION_ID) ="'.$id.'" GROUP BY inst.INSTITUTION_ID,inst.CODE_INSTITUTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE';
      $nbre_ligne = str_replace('\\','',$nbre_ligne);
      $nbre_ligne = "CALL `getTable`('".$nbre_ligne."');"; 
      $get_nbre_ligne= $this->ModelPs->getRequete($nbre_ligne);
      $data['ligne']=(!empty($get_nbre_ligne)) ? count($get_nbre_ligne):0;
      //fin

      //Rapport graphique (claude@mediabox.bi)
      $data_budget="{name:'Budget voté (<>)',data:[";
      $data_total=0;

      $sttl=("SELECT sous.`CODE_SOUS_TUTEL` AS CODE,sous.`DESCRIPTION_SOUS_TUTEL` AS NAME,SUM(T1+T2+T3+T4) AS TRANCHE FROM ptba  JOIN (SELECT inst_institutions_sous_tutel.CODE_SOUS_TUTEL,inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE MD5(inst_institutions_sous_tutel.INSTITUTION_ID)='".$id."') sous  ON SUBSTR(ptba.CODE_NOMENCLATURE_BUDGETAIRE,5,3)=sous.CODE_SOUS_TUTEL WHERE ptba.CODE_MINISTERE ='".$data['get_institution']['CODE_INSTITUTION']."' GROUP BY  sous.`CODE_SOUS_TUTEL`,sous.`DESCRIPTION_SOUS_TUTEL`");
        $sttl = str_replace('\\', '', $sttl);
      $budget_vot=$this->ModelPs->getRequete('CALL getTable("'.$sttl.'")');

      foreach($budget_vot as $key)
      {
        $get_vot=($key->TRANCHE)?$key->TRANCHE:'0';
        $data_total=$data_total+$get_vot;
        $data_budget.="{name:'".$this->str_replacecatego(trim($key->NAME))."',y:".$get_vot.",key:'".$key->CODE."',key2:1},";
      }

      //Budget exécuté
      $data_total1=0;
      $data_budget.="]},{name:'Budget exécuté (@)',data:[";
      $soustutel=("SELECT `CODE_INSTITUTION` as CODE, `DESCRIPTION_INSTITUTION` as NAME,SUM(racc.`MONTANT_RACCROCHE`) AS MONTANT FROM `inst_institutions` JOIN `execution_budgetaire_raccrochage_activite` racc ON racc.INSTITUTION_ID=inst_institutions.INSTITUTION_ID WHERE  MD5(inst_institutions.INSTITUTION_ID)='".$id."' GROUP BY `CODE_INSTITUTION`, `DESCRIPTION_INSTITUTION`");
      $soustutel = str_replace('\\','',$soustutel);
      $soustut=$this->ModelPs->getRequete('CALL getTable("'.$soustutel.'")');

      foreach($soustut as $value)
      {
        $get_exec=$value->MONTANT>0 ? $value->MONTANT : '0';
        $data_total1=$data_total1+$get_exec;
        $data_budget.="{name:'".$this->str_replacecatego(trim($value->NAME))."',y:".$get_exec.",key:'".$value->CODE."',key2:2},";
      }

      $data_budget.="]}";
      $data_budget=str_replace('<>',number_format($data_total,0,'.',' '),$data_budget);
      $data_budget=str_replace('@',number_format($data_total1,0,'.',' '),$data_budget);

      $tot_gen=$data_total+$data_total1;
      $data['data_budget']=$data_budget;
      $data['tot_gen']=$tot_gen;
      $data['data_total']=$data_total;
      $data['data_total1']=$data_total1;
      return view('App\Modules\ihm\Views\Detail_Institution_View',$data);
    }
  }

  // DETAILS DU RAPPORT GRAPHIQUE
  public function detail_rapport()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $KEY=$this->request->getPost('key');
    $KEY2=$this->request->getPost('key2');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null; 
    $query_principal="";
    $order_by='';
    $search="";

    $order_by="";
    if($KEY2==1)
    {
    // BUDGET VOTE
      $query_principal="SELECT ptba.`ACTIVITES` AS NAME1,`T1`,`T2`,`T3`,`T4`,inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL AS DESCRIPTION_INSTITUTION  FROM ptba JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN inst_institutions_sous_tutel ON  SUBSTR(ligne.CODE_NOMENCLATURE_BUDGETAIRE,5,3)=inst_institutions_sous_tutel.CODE_SOUS_TUTEL";

      if($_POST['order']['0']['column']!=0)
      {
        $order_by = isset($_POST['order']) ?' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_SOUS_TUTEL  ASC';
      }

      $search = !empty($_POST['search']['value']) ? ("AND (
       ACTIVITES LIKE '%$var_search%' OR DESCRIPTION_SOUS_TUTEL LIKE '%$var_search%')") : '';
    }
    else
    {
    // BUDGET EXECUTE
      $query_principal="SELECT `MONTANT_RACCROCHE` AS MONTANT,DESCRIPTION_INSTITUTION,ptba.ACTIVITES AS NAME1  FROM `execution_budgetaire_raccrochage_activite` racc JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=racc.INSTITUTION_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID WHERE 1 ";
      if($_POST['order']['0']['column']!=0)
      {
        $order_by = isset($_POST['order']) ?' ORDER BY '.$_POST['order']['0']['column'] .' '.$_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION  ASC';
      }
      $search = !empty($_POST['search']['value']) ? ("AND (ptba.ACTIVITES LIKE '%$var_search%' OR DESCRIPTION_INSTITUTION LIKE '%$var_search%')") : '';
    }

    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $critere="";
    $critere=($KEY2==1) ? " AND inst_institutions_sous_tutel.CODE_SOUS_TUTEL='".$KEY."'":" AND inst_institutions.CODE_INSTITUTION='".$KEY."'";

    $conditions=$query_principal.' '.$critere.' '.$search.' '.$order_by.' '.$limit;
    $query_filter=$query_principal.' '.$critere.' '.$search;
    $query_secondaire = 'CALL `getTable`("'.$conditions.'");';

    $fetch_data = $this->ModelPs->datatable($query_secondaire);
    $u=0;
    $data = array();
    foreach ($fetch_data as $row) 
    {
      $u++;
      $intrant=array();
      $intrant[] ='<center><font color="#000000" size=2><label>'.$u.'</label></font> </center>';

      if($KEY2==1)
      {
        $budget=$row->T1+$row->T2+$row->T3+$row->T4;
        $intrant[] ='<font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font>';
        $intrant[] ='<font color="#000000" size=2><label>'.$row->NAME1.'</label></font>';
        $intrant[] ='<center><font color="#000000" size=2><label>'.number_format($budget,0,'',' ').'</label></font> </center>';
      }
      else
      {
        $intrant[] ='<center><font color="#000000" size=2><label>'.$row->DESCRIPTION_INSTITUTION.'</label></font> </center>';
        $intrant[] ='<font color="#000000" size=2><label>'.$row->NAME1.'</label></font> ';
        $intrant[] ='<center><font color="#000000" size=2><label>'.number_format($row->MONTANT,0,'',' ').'</label></font> </center>';
      }
      $data[] = $intrant;        
    }
    $recordsTotal ="CALL `getTable`('".$query_principal."');";
    $recordsFiltered ="CALL `getTable`('".$query_filter."');";
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($this->ModelPs->datatable($query_principal)),
      "recordsFiltered" =>count($this->ModelPs->datatable($query_filter)),
      "data" => $data
    );
    echo json_encode($output);
  }

  public function  liste_programme($id)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $query_principal="SELECT progr.PROGRAMME_ID,inst.INSTITUTION_ID,inst.CODE_INSTITUTION,progr.CODE_PROGRAMME,progr.INTITULE_PROGRAMME,progr.OBJECTIF_DU_PROGRAMME,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4,exe.LIQUIDATION FROM inst_institutions inst JOIN ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN execution_budgetaire_new exe ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=exe.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN inst_institutions_programmes progr ON progr.INSTITUTION_ID=inst.INSTITUTION_ID WHERE 1 AND inst.INSTITUTION_ID = ".$id." GROUP BY progr.PROGRAMME_ID,inst.INSTITUTION_ID,inst.CODE_INSTITUTION,progr.CODE_PROGRAMME,progr.INTITULE_PROGRAMME,progr.OBJECTIF_DU_PROGRAMME";
    $query_principal = str_replace('\\','',$query_principal);
    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('progr.CODE_PROGRAMME','progr.INTITULE_PROGRAMME','progr.OBJECTIF_DU_PROGRAMME','ptba.T1','exe.LIQUIDATION');

    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY progr.CODE_PROGRAMME ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (progr.CODE_PROGRAMME LIKE '%$var_search%' OR progr.INTITULE_PROGRAMME LIKE '%$var_search%' OR progr.OBJECTIF_DU_PROGRAMME LIKE '%$var_search%' OR ptba.T1 LIKE '%$var_search%' OR exe.LIQUIDATION LIKE '%$var_search%')"):'';

    $critaire = '';
    $query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.' '.$limit;

    $query_filter = $query_principal.' '.$search.' '.$critaire;
    $requete='CALL `getList`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      $montant_exe ="SELECT prog.CODE_PROGRAMME,SUM(racc.MONTANT_RACCROCHE) AS MONTANT_RACCROCHE FROM execution_budgetaire_raccrochage_activite racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID WHERE 1 AND prog.CODE_PROGRAMME= '".$info->CODE_PROGRAMME."'  GROUP BY prog.CODE_PROGRAMME";

      $montant_exe='CALL `getList`("'.$montant_exe.'")';
      $get_money = $this->ModelPs->getRequeteOne($montant_exe);
      $montant_program = (!empty($get_money['MONTANT_RACCROCHE'])) ? $get_money['MONTANT_RACCROCHE'] :0;

      $montant_vote=$info->T1+$info->T2+$info->T3+$info->T4;
      $post=array();
      $post[]=!empty($info->CODE_PROGRAMME) ? $info->CODE_PROGRAMME : 'N/A';
      $post[]=!empty($info->INTITULE_PROGRAMME) ? $info->INTITULE_PROGRAMME : 'N/A';
      $post[]=!empty($info->OBJECTIF_PROGRAMME) ? $info->OBJECTIF_PROGRAMME : 'N/A';
      $post[]=number_format($montant_vote,0,","," ").' BIF';
      $post[]=number_format($montant_program,0,","," ").' BIF';
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

  public function  liste_action($id)
  { 
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $query_principal="SELECT inst.INSTITUTION_ID,inst.CODE_INSTITUTION,act.CODE_ACTION,act.LIBELLE_ACTION,act.OBJECTIF_ACTION,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4 FROM inst_institutions inst JOIN ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID WHERE 1 AND inst.INSTITUTION_ID = ".$id." GROUP BY inst.INSTITUTION_ID,inst.CODE_INSTITUTION,act.CODE_ACTION,act.LIBELLE_ACTION,act.OBJECTIF_ACTION";
    $query_principal = str_replace('\\', '', $query_principal);
    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('act.CODE_ACTION','act.LIBELLE_ACTION','act.OBJECTIF_ACTION','ptba.T1',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY act.CODE_ACTION ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (act.CODE_ACTION LIKE '%$var_search%' OR act.LIBELLE_ACTION LIKE '%$var_search%' OR act.OBJECTIF_ACTION LIKE '%$var_search%' OR ptba.T1 LIKE '%$var_search%')"):'';

    $critaire = '';
    $query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.' '.$limit;

    $query_filter = $query_principal.' '.$search.' '.$critaire;
    $requete='CALL `getList`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      $montant_exe ="SELECT act.CODE_ACTION,SUM(racc.MONTANT_RACCROCHE) AS MONTANT_RACCROCHE FROM execution_budgetaire_raccrochage_activite racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID WHERE 1 AND act.CODE_ACTION='".$info->CODE_ACTION."' GROUP BY act.CODE_ACTION";

      $montant_exe='CALL `getList`("'.$montant_exe.'")';
      $get_money = $this->ModelPs->getRequeteOne($montant_exe);
      $montant = (!empty($get_money['MONTANT_RACCROCHE'])) ? $get_money['MONTANT_RACCROCHE'] :0;
      $montant_vote=$info->T1+$info->T2+$info->T3+$info->T4;
      $post=array();
      $post[]=!empty($info->CODE_ACTION) ? $info->CODE_ACTION : 'N/A';
      $post[]=!empty($info->LIBELLE_ACTION) ? $info->LIBELLE_ACTION : 'N/A';
      $post[]=!empty($info->OBJECTIF_ACTION) ? $info->OBJECTIF_ACTION : 'N/A';
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

  public function  liste_activite($id)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $query_principal="SELECT ptba.PTBA_ID, inst.INSTITUTION_ID,inst.CODE_INSTITUTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ptba.CODES_PROGRAMMATIQUE,ptba.CODES_PROGRAMMATIQUE,ptba.ACTIVITES,T1, T2,T3, T4 FROM inst_institutions inst JOIN ptba ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND inst.INSTITUTION_ID =".$id."";
    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba.CODES_PROGRAMMATIQUE','ptba.ACTIVITES','ptba.T1');

    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .' '.$_POST['order']['0']['dir'] : ' ORDER BY ligne.CODE_NOMENCLATURE_BUDGETAIRE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba.CODES_PROGRAMMATIQUE LIKE '%$var_search%' OR ptba.ACTIVITES LIKE '%$var_search%' OR ptba.T1 LIKE '%$var_search%')"):'';

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
      $post[]=!empty($info->ACTIVITES) ? $info->ACTIVITES : 'N/A';
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
    if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_INSTITUTION')!=1)
      {
    return redirect('Login_Ptba/homepage');
      }
    $query_principal="SELECT inst.INSTITUTION_ID,inst.CODE_INSTITUTION,SUM(ptba.T1) AS T1,SUM(ptba.T2) AS T2,SUM(ptba.T3) AS T3,SUM(ptba.T4) AS T4, ligne.CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions inst JOIN ptba ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND inst.INSTITUTION_ID =".$id." GROUP BY inst.INSTITUTION_ID,inst.CODE_INSTITUTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE";
    $query_principal = str_replace('\\','',$query_principal);
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
    $montant ="SELECT ligne.CODE_NOMENCLATURE_BUDGETAIRE,SUM(racc.MONTANT_RACCROCHE) AS MONTANT_RACCROCHE FROM execution_budgetaire_raccrochage_activite racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID  WHERE 1 AND ligne.CODE_NOMENCLATURE_BUDGETAIRE= '".$info->CODE_NOMENCLATURE_BUDGETAIRE."' GROUP BY ptba.CODE_NOMENCLATURE_BUDGETAIRE";
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
    $recordsFiltered = $this->ModelPs->datatable( $requeteqf);

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
    $bindparams = [$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
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