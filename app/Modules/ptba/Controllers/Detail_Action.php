<?php
/**RUGAMBA Jean Vainqueur
  *Titre: Détail des actions
  *Numero de telephone: (+257) 66 33 43 25
  *WhatsApp: (+257) 62 47 19 15
  *Email: jean.vainqueur@mediabox.bi
  *Date: 20 Septembre,2023
**/
namespace App\Modules\ptba\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Detail_Action extends BaseController
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
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
    
    $data = $this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $bind_id_exist = $this->getBindParms('ACTION_ID','inst_institutions_actions','MD5(ACTION_ID)="'.$id.'"','1');
    $bind_id_exist=str_replace('\\','',$bind_id_exist);
    $id_exist = $this->ModelPs->getRequeteOne($psgetrequete, $bind_id_exist);
     //print_r($id_exist);exit();
    if(empty($id_exist)){


      return redirect('Login_Ptba/do_logout');
    }
    else
    {
      $get_act = 'SELECT ACTION_ID,CODE_ACTION,LIBELLE_ACTION,PROGRAMME_ID FROM inst_institutions_actions WHERE 1 AND MD5(ACTION_ID)= "'.$id.'"';
      $get_act=str_replace('\\','',$get_act);
      $get_act = "CALL `getTable`('".$get_act."');";
      $data['action'] = $this->ModelPs->getRequeteOne($get_act);

      //Programme
      $bindprog = $this->getBindParms('`PROGRAMME_ID`,`INSTITUTION_ID`,`CODE_PROGRAMME`,`INTITULE_PROGRAMME`','inst_institutions_programmes', 'PROGRAMME_ID='.$data['action']['PROGRAMME_ID'],'`PROGRAMME_ID`');
      $data['prog'] = $this->ModelPs->getRequeteOne($psgetrequete,$bindprog);

      //Institution
      $bindinst = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`','inst_institutions', 'INSTITUTION_ID='.$data['prog']['INSTITUTION_ID'],'`INSTITUTION_ID`');
      $data['instit'] = $this->ModelPs->getRequeteOne($psgetrequete,$bindinst);


      $bind_tranche = $this->getBindParms('`TRANCHE_ID`,`CODE_TRANCHE`,CONCAT(`DATE_DEBUT`,"-",date_format(now(),"%Y")) as debut,CONCAT(`DATE_FIN`,"-",date_format(now(),"%Y")) as fin','op_tranches','1','`TRANCHE_ID`');
      $bind_tranche = str_replace('\"','"',$bind_tranche);
      $tranches = $this->ModelPs->getRequete($psgetrequete,$bind_tranche);

      // montant voté
      $bind_montant_vote = $this->getBindParms('SUM(`T1`) as T1,SUM(`T2`) as T2,SUM(`T3`) as T3,SUM(`T4`) as T4','ptba','`ACTION_ID`="'.$data['action']['ACTION_ID'].'"','SUM(`T1`)');

      $bind_montant_vote = str_replace('\"','"',$bind_montant_vote);

      $data['montant_vote'] = $this->ModelPs->getRequeteOne($psgetrequete,$bind_montant_vote);

      $data['montant_total'] = $data['montant_vote']['T1']+$data['montant_vote']['T2']+$data['montant_vote']['T3']+$data['montant_vote']['T4'];


      // montant execute par tranche
      $mont_exe = "SELECT COUNT(DISTINCT racc.PTBA_ID) as activites,SUM(racc.MONTANT_RACCROCHE) as EXECUTEE,tranche.CODE_TRANCHE FROM execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID LEFT JOIN op_tranches tranche ON tranche.TRANCHE_ID=racc.TRIMESTRE_ID WHERE 1 AND PTBA.ACTION_ID ='".$data['action']['ACTION_ID']."' GROUP BY tranche.CODE_TRANCHE";
      $mont_exe = 'CALL `getTable`("'.$mont_exe.'");';
      $execute = $this->ModelPs->getRequete($mont_exe);

      $executeMoney1 = 0;$executeMoney2 = 0;$executeMoney3 = 0;$executeMoney4 = 0;
      $activites1 = 0;$activites2 = 0;$activites3 = 0;$activites4 = 0;

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

          if($tranc['CODE_TRANCHE'] == 'T1')
          {
            $executeMoney1 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste1 = $data['montant_vote']['T1'] - floatval($executeMoney1);
            $activites1 = $value->activites;
          }
          else if ($tranc['CODE_TRANCHE'] == 'T2')
          {
            $executeMoney2 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste2 = $data['montant_vote']['T2'] - floatval($executeMoney2);
            $activites2 = $value->activites; 
          }
          else if ($tranc['CODE_TRANCHE'] == 'T3')
          {
            $executeMoney3 =(!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste3=($data['montant_vote']['T3'] -  floatval($executeMoney3));
            $activites3 = $value->activites;
          }
          else
          {
            $executeMoney4 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
            $reste4 = $data['montant_vote']['T4'] -  floatval($executeMoney4);
            $activites4 = $value->activites;
          }
        } 
      }

      $data['tot_exe'] = $executeMoney1+$executeMoney2+$executeMoney3+$executeMoney4;
      $data['restant'] = $reste1+$reste2+$reste3+$reste4;
      $data['totAct'] = $activites1+$activites2+$activites3+$activites4;

      $data['reste1']=$reste1;
      $data['reste2']=$reste2;
      $data['reste3']=$reste3;
      $data['reste4']=$reste4;
      $data['executeMoney1']=$executeMoney1;
      $data['executeMoney2']=$executeMoney2;
      $data['executeMoney3']=$executeMoney3;
      $data['executeMoney4']=$executeMoney4;
      $data['activites1']=$activites1;
      $data['activites2']=$activites2;
      $data['activites3']=$activites3;
      $data['activites4']=$activites4;

      // Total des activités
      $query_principal = 'SELECT act.`ACTION_ID`, ptba.PTBA_ID FROM `inst_institutions_actions` act JOIN ptba ON act.ACTION_ID=ptba.ACTION_ID JOIN execution_budgetaire_raccrochage_activite_new exe ON ptba.PTBA_ID=exe.PTBA_ID WHERE 1 AND MD5(act.ACTION_ID) ="'.$id.'"';
      $query_principal=str_replace('\\','',$query_principal);
      $requete="CALL `getList`('".$query_principal."')";
      $recordsTotal = $this->ModelPs->datatable( $requete);
      $data['total_activ']=count($recordsTotal);
      return view('App\Modules\ptba\Views\Detail_Action_View',$data);
    }
  }

  //liste des activités
  function liste_activite($id)
  {
    $session  = \Config\Services::session();
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }
    if($session->get('SESSION_SUIVIE_PTBA_PTBA_ACTIONS')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    

    $query_principal = "SELECT act.`ACTION_ID`, ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE, ptba.CODES_PROGRAMMATIQUE, ptba.ACTIVITES,ptba.T1,ptba.T2,ptba.T3,ptba.T4,exe.MONTANT_RACCROCHE, ptba.PTBA_ID FROM `inst_institutions_actions` act JOIN ptba ON act.ACTION_ID=ptba.ACTION_ID JOIN execution_budgetaire_raccrochage_activite_new exe ON ptba.PTBA_ID=exe.PTBA_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID =ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND act.`ACTION_ID` =".$id;

    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE','ptba.CODES_PROGRAMMATIQUE','ptba.ACTIVITES',1,1,1);

    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ptba.CODES_PROGRAMMATIQUE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ptba.CODES_PROGRAMMATIQUE LIKE '%$var_search%' OR ptba.ACTIVITES LIKE '%$var_search%')"):'';

    $critaire = '';
    $query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.'   '.$limit;

    $query_filter = $query_principal.' '.$search.' '.$critaire;
    $requete='CALL `getList`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable($requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      $montant_vote=floatval($info->T1)+floatval($info->T2)+floatval($info->T3)+floatval($info->T4);
      $post=array();
      $post[]=!empty($info->CODE_NOMENCLATURE_BUDGETAIRE) ? $info->CODE_NOMENCLATURE_BUDGETAIRE : 'N/A';
      $post[]=!empty($info->CODE_NOMENCLATURE_BUDGETAIRE) ? $info->CODE_NOMENCLATURE_BUDGETAIRE : 'N/A';
      $post[]=!empty($info->CODES_PROGRAMMATIQUE) ? $info->CODES_PROGRAMMATIQUE : 'N/A';
      $post[]=$info->ACTIVITES;
      $post[]=number_format($montant_vote,0,","," ");
      $post[]=number_format($info->MONTANT_RACCROCHE,0,","," ");

      $action = '<div class="dropdown" style="color:#fff;">
      <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i>'.lang('messages_lang.dropdown_link_options').'<span class="caret"></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-left">';

      $action .="<li>
      <a href='".base_url("ptba/Detail_Activite/".$info->PTBA_ID)."'><label>&nbsp;&nbsp;".lang('messages_lang.details_prog_budg')."</label></a>
      </li></ul>";
      $post[]= $action;
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

  /**
    * fonction pour retourner le tableau des parametre pour le PS pour les selection
    * @param string  $columnselect //colone A selectionner
    * @param string  $table        //table utilisE
    * @param string  $where        //condition dans la clause where
    * @param string  $orderby      //order by
    * @return  mixed
  */
  public function getBindParms($columnselect,$table,$where,$orderby)
  {
    // code...
    $db = db_connect();
      // print_r($db->lastQuery);die();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }
}
?>