<?php
/**NSABIMANA Vincent
  *Numero de telephone (WhatsApp): (+257) 61970146
  *Email: vincent@mediabox.bi
  *Date: 22 Novembre,2023
  *Titre: Listes des demandes
**/
namespace App\Modules\process\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Demandes extends BaseController
{
  protected $session;
  protected $ModelPs;

  public function __construct()
  { 
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
  }

  function index()
  {
    $session = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 && $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data = $this->urichk();
    //$data['titre']="Liste des demandes";
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
      //Sélectionner les processus
    $bindparams = $this->getBindParms('`PROCESS_ID`,`NOM_PROCESS`','proc_process','PROCESS_ID IN(9,10) AND STATUT=1', '`PROCESS_ID` ASC');
    $data['process'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
    //Sélectionner les institutions
    $bindparams = $this->getBindParms('`INSTITUTION_ID`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', '1', '`INSTITUTION_ID` ASC');
    $data['institution'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
    #############################################################################################
    $PROFIL_ID = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    //get etape suivant et les actions AND proc_etape.PROFIL_ID='.$PROFIL_ID.'
    $getAction = 'SELECT proc_actions.ACTION_ID, proc_actions.ETAPE_ID, proc_actions.MOVETO,proc_actions.IS_REQUIRED, proc_actions.DESCR_ACTION, proc_actions.GET_FORM, proc_actions.LINK_FORM, proc_actions.ID_CL_CMR_COSTAB_CATEGORIE, proc_actions.IS_INITIAL FROM proc_profil_etape JOIN proc_etape ON proc_etape.ETAPE_ID=proc_profil_etape.ETAPE_ID JOIN  proc_actions ON proc_etape.ETAPE_ID= proc_actions.ETAPE_ID WHERE proc_profil_etape.PROFIL_ID='.$PROFIL_ID.' AND proc_actions.IS_INITIAL=1';
    $getActionData = "CALL `getTable`('".$getAction."');";
    $data['getAction'] = $this->ModelPs->getRequeteOne($getActionData);
    return view('App\Modules\process\Views\Demande_List_View',$data);
  } 

  //liste des demandes
  function listing()
  {
    $session = \Config\Services::session();
    $USER_ID =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    $PROFIL_ID =session()->get("SESSION_SUIVIE_PTBA_PROFIL_ID");
    if(empty($USER_ID))//check connexion
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_PROCESSUS=$this->request->getPost('PROCESS_ID');
    $ID_ETAPE=$this->request->getPost('ID_ETAPE');
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

    $critere="";
    $critere2="";
    $critere3="";

    if(!empty($ID_PROCESSUS)) {//criteres pour les processus

       $critere=' AND proc_demandes.PROCESS_ID='.$ID_PROCESSUS;
    }else
    {
      $critere=' AND (proc_demandes.PROCESS_ID=9 OR proc_demandes.PROCESS_ID=10)';
    }
    if(!empty($ID_ETAPE)) {//criteres pour les etapes
       $critere2=' AND proc_demandes.ETAPE_ID='.$ID_ETAPE;
    }

    if(!empty($INSTITUTION_ID)) { //criteres pour les institutions
       $critere3=' AND inst_institutions.INSTITUTION_ID='.$INSTITUTION_ID;
    }

    //requete des demandes 
    $query_principal = "SELECT proc_demandes.CODE_DEMANDE,proc_process.NOM_PROCESS,proc_process.LINK,proc_etape.DESCR_ETAPE,proc_demandes.PROCESS_ID,proc_demandes.ETAPE_ID,proc_demandes.DATE_INSERTION,proc_demandes.USER_ID,proc_demandes.ID_DEMANDE,inst_institutions.DESCRIPTION_INSTITUTION  FROM proc_demandes JOIN proc_etape ON proc_etape.ETAPE_ID=proc_demandes.ETAPE_ID JOIN proc_process ON proc_process.PROCESS_ID=proc_demandes.PROCESS_ID JOIN user_affectaion ON user_affectaion.USER_ID=proc_demandes.USER_ID JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID JOIN proc_profil_etape ON proc_demandes.ETAPE_ID=proc_profil_etape.ETAPE_ID WHERE 1 ".$critere." ".$critere2." ".$critere3." AND proc_profil_etape.PROFIL_ID=".$PROFIL_ID." AND proc_demandes.IS_END IS NULL"; 

    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('proc_demandes.CODE_DEMANDE','proc_process.NOM_PROCESS','proc_etape.DESCR_ETAPE','inst_institutions.DESCRIPTION_INSTITUTION',1,1,1);//

    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY proc_demandes.ID_DEMANDE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND ( proc_demandes.CODE_DEMANDE LIKE '%$var_search%' OR proc_process.NOM_PROCESS LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR inst_institutions.DESCRIPTION_INSTITUTION LIKE '%$var_search%')"):'';

    $critaire = '';
    $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;

    $query_filter = $query_principal." ".$search." ".$critaire;
    $requete='CALL `getTable`("'.$query_secondaire.'");';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      //Nom du process
      if(!empty($info->NOM_PROCESS))
      {
        if(strlen($info->NOM_PROCESS) > 6)
        {
          $NOM_PROCESS =  mb_substr($info->NOM_PROCESS, 0, 6) .'...<a class="btn-sm" title="'.$info->NOM_PROCESS.'"><i class="fa fa-eye"></i></a>';
        }
        else
        {
          $NOM_PROCESS = $info->NOM_PROCESS;
        }
      }
      else
      {
         $NOM_PROCESS = 'N/A';
      }


      //Déscription de l'étape
      if(!empty($info->DESCR_ETAPE))
      {
        if(strlen($info->DESCR_ETAPE) > 4)
        {
          $DESCR_ETAPE =  mb_substr($info->DESCR_ETAPE, 0, 4) .'...<a class="btn-sm" title="'.$info->DESCR_ETAPE.'"><i class="fa fa-eye"></i></a>';
        }
        else
        {
          $DESCR_ETAPE = $info->DESCR_ETAPE;
        }
      }
      else
      {
         $DESCR_ETAPE = 'N/A';
      }

      
      //Déscription de l'institution
      if(!empty($info->DESCRIPTION_INSTITUTION))
      {
        if(strlen($info->DESCRIPTION_INSTITUTION) > 6)
        {
          $DESCRIPTION_INSTITUTION =  mb_substr($info->DESCRIPTION_INSTITUTION, 0, 6) .'...<a class="btn-sm" title="'.$info->DESCRIPTION_INSTITUTION.'"><i class="fa fa-eye"></i></a>';
        }
        else
        {
          $DESCRIPTION_INSTITUTION = $info->DESCRIPTION_INSTITUTION;
        }
      }
      else
      {
         $DESCRIPTION_INSTITUTION = 'N/A';
      }

      $post=array();
      $post[]=$u++;
      $post[]= !empty($info->CODE_DEMANDE) ? $info->CODE_DEMANDE : 'N/A';
      $post[]= $NOM_PROCESS;
      $post[]= $DESCR_ETAPE;
      $post[]= $DESCRIPTION_INSTITUTION;
      $post[]=date('d-m-Y h:m:s',strtotime($info->DATE_INSERTION));
      $post[]="<a title='Traiter' class='btn btn-primary btn-md' href='".base_url("".$info->LINK."".md5($info->ID_DEMANDE))."'><i class='fa fa-list'></i></a>";
      $data[]=$post;  
    }
    $requeteqp='CALL `getTable`("'.$query_principal.'");';
    $recordsTotal = $this->ModelPs->datatable( $requeteqp);
    $requeteqf='CALL `getTable`("'.$query_filter.'");';
    $recordsFiltered = $this->ModelPs->datatable( $requeteqf);

    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }

  public function get_etapes()
  { //recuperation des etapes
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_PROCESSUS=$this->request->getPost('PROCESS_ID');

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
      //Sélectionner les etapes
    $bindparams = $this->getBindParms('`ETAPE_ID`,`DESCR_ETAPE`,PROCESS_ID', 'proc_etape', '1 AND PROCESS_ID='.$ID_PROCESSUS, '`ETAPE_ID` ASC');
    $get_etapes = $this->ModelPs->getRequete($psgetrequete, $bindparams);

    $html = '<option value="0">'.lang('messages_lang.selection_message').'</option>';
    foreach($get_etapes as $key_etapes)
    {
      $html .= '<option value="'.$key_etapes->ETAPE_ID.'">'.$key_etapes->DESCR_ETAPE.'</option>';
    }

    $output = array(
     'DATA_ETAPE'=>$html
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