<?php
/*
*NDERAGAKURA Alain Charbel
*Titre: Montant par chaque Phase
*Numero de telephone: (+257) 62003522
*Email: charbel@mediabox.bi
*Date: 19/11/2024

*/
namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Montant_Execution_Par_Tache extends BaseController
{
  protected $session;
  protected $ModelPs;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db=db_connect();
    $bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }
   
  public function get_prog()
  {
    $callpsreq = "CALL getRequete(?,?,?,?);";

    $html='<option value="">'.lang('messages_lang.labelle_selecte').'</option>';
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    if(!empty($INSTITUTION_ID))
    {
      $st = $this->getBindParms('PROGRAMME_ID,INTITULE_PROGRAMME,CODE_PROGRAMME','inst_institutions_programmes','INSTITUTION_ID='.$INSTITUTION_ID,'INTITULE_PROGRAMME ASC');
      $get_st = $this->ModelPs->getRequete($callpsreq, $st);

      foreach($get_st as $key)
      {
        $html.="<option value='".$key->PROGRAMME_ID."'>".$key->CODE_PROGRAMME." ".$key->INTITULE_PROGRAMME."</option>";
      }
    }


    $output = array('status' => TRUE ,'html' => $html);
    return $this->response->setJSON($output);
  }

  public function change_count()
  {
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
    $TRIMESTRE_ID=$this->request->getPost('TRIMESTRE_ID');

    $menu_suivi_exec=$this->count_montant_exec_phase($TRIMESTRE_ID,$INSTITUTION_ID,$PROGRAMME_ID);
    $output['ENG_BUDG']=$menu_suivi_exec['ENG_BUDG'];
    $output['ENG_JURD']=$menu_suivi_exec['ENG_JURD'];
    $output['LIQUIDATION']=$menu_suivi_exec['LIQUIDATION'];
    $output['ORDONNANCEMENT']=$menu_suivi_exec['ORDONNANCEMENT'];
    $output['PAIEMENT']=$menu_suivi_exec['PAIEMENT'];
    $output['DECAISSEMENT']=$menu_suivi_exec['DECAISSEMENT'];
    return $this->response->setJSON($output);
  }

  public function get_liste()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    // if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    // {
    //   return redirect('Login_Ptba/homepage'); 
    // }

    $data=$this->urichk();
    $callpsreq="CALL getRequete(?,?,?,?);";
    $bind_institution=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
    $data['institutions']=$this->ModelPs->getRequete($callpsreq,$bind_institution);

    $bind_trimestre=$this->getBindParms('trim.TRIMESTRE_ID,trim.DESC_TRIMESTRE','trimestre trim','1','TRIMESTRE_ID ASC');
    $data['trimestre']=$this->ModelPs->getRequete($callpsreq,$bind_trimestre);

    $INSTITUTION_ID=0;
    $PROGRAMME_ID=0;
    $TRIMESTRE_ID=0;
    $menu_suivi_exec=$this->count_montant_exec_phase($TRIMESTRE_ID,$INSTITUTION_ID,$PROGRAMME_ID);


    $data['ENG_BUDG']=$menu_suivi_exec['ENG_BUDG'];
    $data['ENG_JURD']=$menu_suivi_exec['ENG_JURD'];
    $data['LIQUIDATION']=$menu_suivi_exec['LIQUIDATION'];
    $data['ORDONNANCEMENT']=$menu_suivi_exec['ORDONNANCEMENT'];
    $data['PAIEMENT']=$menu_suivi_exec['PAIEMENT'];
    $data['DECAISSEMENT']=$menu_suivi_exec['DECAISSEMENT'];

    // $data['prise_charge_a_recep']=$menu_suivi_exec['prise_charge_a_recep'];
    // $data['titre_attente_etab']=$menu_suivi_exec['titre_attente_etab'];
    // $data['titre_attente_corr']=$menu_suivi_exec['titre_attente_corr'];
    // $data['dir_compt_recep']=$menu_suivi_exec['dir_compt_recep'];
    // $data['obr_recep']=$menu_suivi_exec['obr_recep'];
    // $data['dec_att_trait']=$menu_suivi_exec['dec_att_trait'];
    // $data['dec_att_recep_brb']=$menu_suivi_exec['dec_att_recep_brb'];
    return view('App\Modules\double_commande_new\Views\Montant_Execution_Par_Tache_View',$data);
  }

  public function listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_SUIVI_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
    $TRIMESTRE_ID=$this->request->getPost('TRIMESTRE_ID');
    $critere1="";
    $callpsreq = "CALL getRequete(?,?,?,?);";

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND ptba.INSTITUTION_ID=".$INSTITUTION_ID;
      if(!empty($PROGRAMME_ID))
      {
        $critere1.=" AND ptba.PROGRAMME_ID=".$PROGRAMME_ID;
      }
    }
    if (!empty($TRIMESTRE_ID))
    {
      if ($TRIMESTRE_ID!=5)
      {
        $critere1.=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
      }
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by='';
    $order_column=array('pap.DESC_PAP_ACTIVITE','ptba.DESC_TACHE','exec.MONTANT_ENG_BUDGETAIRE','exec.MONTANT_ENG_JURIDIQUE','exec.MONTANT_LIQUIDATION','exec.MONTANT_ORDONNANCEMENT','exec.MONTANT_PAIEMENT','exec.MONTANT_DECAISSEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY pap.DESC_PAP_ACTIVITE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESC_PAP_ACTIVITE LIKE '%$var_search%' OR DESC_TACHE LIKE '%$var_search%' OR exec.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.MONTANT_ENG_JURIDIQUE LIKE '%$var_search%' OR exec.MONTANT_LIQUIDATION LIKE '%$var_search%' OR exec.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR exec.MONTANT_PAIEMENT LIKE '%$var_search%' OR exec.MONTANT_DECAISSEMENT LIKE '%$var_search%')"):'';
    $group = " GROUP BY exec.PTBA_TACHE_ID";

    //condition pour le query principale
    $conditions=$critere1." ".$search." ".$group." ".$order_by." ".$limit;

    // condition pour le query filter
    $conditionsfilter=$critere1." ".$search." ".$group;

    $requetedebase="SELECT pap.DESC_PAP_ACTIVITE,ptba.DESC_TACHE,exec.MONTANT_ENG_BUDGETAIRE,exec.MONTANT_ENG_JURIDIQUE,exec.MONTANT_LIQUIDATION,exec.MONTANT_ORDONNANCEMENT,exec.MONTANT_PAIEMENT,exec.MONTANT_DECAISSEMENT FROM execution_budgetaire_execution_tache exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites pap ON pap.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID JOIN execution_budgetaire ON execution_budgetaire.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE 1";   
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;
    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 14) ? (mb_substr($row->DESC_TACHE, 0, 14) . '...<a class="btn-sm" data-toggle="modal" data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 14) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 14) . '...<a class="btn-sm" title="'.$row->DESC_PAP_ACTIVITE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

      // $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

      $action='';
      $sub_array = array();
      $sub_array[] = !empty($DESC_PAP_ACTIVITE)?$DESC_PAP_ACTIVITE:'-';
      $sub_array[] = $DESC_TACHE;
      $sub_array[] = number_format($row->MONTANT_ENG_BUDGETAIRE,$this->get_precision($row->MONTANT_ENG_BUDGETAIRE),","," ");
      $sub_array[] = number_format($row->MONTANT_ENG_JURIDIQUE,$this->get_precision($row->MONTANT_ENG_JURIDIQUE),","," ");               
      $sub_array[] = number_format($row->MONTANT_LIQUIDATION,$this->get_precision($row->MONTANT_LIQUIDATION),","," ");
      $sub_array[] = number_format($row->MONTANT_ORDONNANCEMENT,$this->get_precision($row->MONTANT_ORDONNANCEMENT),","," ");
      $sub_array[] = number_format($row->MONTANT_PAIEMENT,$this->get_precision($row->MONTANT_PAIEMENT),","," ");
      $sub_array[] = number_format($row->MONTANT_DECAISSEMENT,$this->get_precision($row->MONTANT_DECAISSEMENT),","," ");
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
    "draw" => intval($_POST['draw']),
    "recordsTotal" => count($recordsTotal),
    "recordsFiltered" => count($recordsFiltered),
    "data" => $data,
    );
    return $this->response->setJSON($output);
  }

  private function get_precision($value=0)
  {
    $string = strval($value);
    $number=explode('.',$string)[1] ?? '';
    $precision='';
    for($i=1;$i<=strlen($number);$i++)
    {
      $precision=$i;
    }
    if(!empty($precision)) 
    {
      return $precision;
    }
    else
    {
      return 0;
    }    
  }
}