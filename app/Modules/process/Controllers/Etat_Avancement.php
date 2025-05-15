<?php

/*
  *HAVYARIMANA Jean Thierry
  *Email: thierry.havyarimana@mediabox.bi
  *Date: 23 Novembre,2023
  *Titre: Gestion des demandes, details de demandes et actions
*/
namespace App\Modules\process\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Exception;

class Etat_Avancement extends BaseController
{
  protected $session;
  protected $ModelPs;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
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
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  //view pour état d'avancement
  public function etat_avancement()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = '';
    if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    } else {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_ETAT_AVANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
      //Sélectionner les processus
    $bindparams = $this->getBindParms('`PROCESS_ID`,`NOM_PROCESS`','proc_process','1 AND STATUT=1','`PROCESS_ID` ASC');
    $data['process'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
    return view('App\Modules\process\Views\Etat_Avancement_View',$data);
  }
    
  //liste pour état d'avancement
  public function etat_avancement_listing()
  {
    $USER_ID =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_ID))
    {
      return redirect('Login_Ptba/do_logout');
    }

    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_ETAT_AVANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_PROCESSUS=$this->request->getPost('PROCESS_ID');
    $ID_ETAPE=$this->request->getPost('ID_ETAPE');
    $critere="";
    $critere2="";

    if (!empty($ID_PROCESSUS)) {
      $critere=' AND proc_demandes.PROCESS_ID='.$ID_PROCESSUS;
    }
    if (!empty($ID_ETAPE)) {
      $critere2=' AND proc_demandes.ETAPE_ID='.$ID_ETAPE;
    }

    $profil="SELECT `PROFIL_ID` FROM `user_users` WHERE 1 AND USER_ID=".$USER_ID;
    $getProfil = 'CALL `getTable`("'.$profil.'");';
    $profil_id = $this->ModelPs->getRequeteOne($getProfil)['PROFIL_ID'];

    $query_principal = "SELECT proc_demandes.CODE_DEMANDE, proc_process.NOM_PROCESS, proc_etape.DESCR_ETAPE, user_users.NOM AS NOM, user_users.PRENOM AS PRENOM, proc_demandes.ETAPE_ID, DATE_FORMAT(proc_demandes.DATE_INSERTION, '%d-%m-%Y %H:%i:%s') AS DATE_DEMANDE FROM proc_demandes JOIN proc_etape ON proc_etape.ETAPE_ID = proc_demandes.ETAPE_ID JOIN proc_process ON proc_process.PROCESS_ID = proc_demandes.PROCESS_ID JOIN user_users ON proc_demandes.USER_ID = user_users.USER_ID WHERE 1 ".$critere." ".$critere2;

    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array(1,'proc_demandes.CODE_DEMANDE','proc_process.NOM_PROCESS','proc_etape.DESCR_ETAPE','user_users.NOM',1,'proc_demandes.DATE_INSERTION');

    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY proc_demandes.ID_DEMANDE DESC';

    $search = !empty($_POST['search']['value']) ?  (" AND (proc_demandes.CODE_DEMANDE LIKE '%$var_search%' OR proc_process.NOM_PROCESS LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR DATE_FORMAT(proc_demandes.DATE_INSERTION, '%d-%m-%Y %H:%i:%s') LIKE '%$var_search%')"):'';

    $critaire = '';
    $query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.' '.$limit;

    $query_filter = $query_principal.' '.$search.' '.$critaire;
    $requete='CALL `getList`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);

    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      $post=array();
      $profil="SELECT `PROFIL_ID` FROM `proc_profil_etape` WHERE 1 AND ETAPE_ID=".$info->ETAPE_ID;
      $getProfil = 'CALL `getTable`("'.$profil.'");';
      $dataProfil = $this->ModelPs->getRequete($getProfil);
      $NBRE_PROFIL = count($dataProfil);

      $post[]=$u++;
      $post[]=!empty($info->CODE_DEMANDE) ? $info->CODE_DEMANDE : 'N/A';
      $post[]=!empty($info->NOM_PROCESS) ? $info->NOM_PROCESS : 'N/A';
      $post[]=!empty($info->DESCR_ETAPE) ? $info->DESCR_ETAPE : 'N/A';
      $post[]=!empty($info->NOM) ? $info->NOM.' '.$info->PRENOM : 'N/A';
      
      //get nombre de profils
      $action =  '';
      $action .="
      <div style='cursor:pointer'>
      <button style='cursor:inherit' class='btn btn-primary profil_etape_button' data-toggle='modal' onclick='get_profils(".$info->ETAPE_ID.")'>
      <label class='text-white' style='cursor:inherit'>".$NBRE_PROFIL."</label></button>
      </div>";

      $post[]= $action;
      $post[]=$info->DATE_DEMANDE;
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
    

  //fonction pour retourner les profils d'une étape
  public function get_profil_etape()
  {
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba'); 
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_ETAT_AVANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $html = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    $PROCESS_ID = $this->request->getPost('PROCESS_ID');
    if(!empty($PROCESS_ID))
    {
      $etape = $this->getBindParms('ETAPE_ID,DESCR_ETAPE', 'proc_etape', 'PROCESS_ID=' . $PROCESS_ID, 'DESCR_ETAPE ASC');
      $get_etape = $this->ModelPs->getRequete($callpsreq, $etape);

      foreach($get_etape as $key)
      {
        $html .= "<option value='" . $key->ETAPE_ID . "'>" . $key->DESCR_ETAPE . "</option>";
      }

      $output = array('status' => TRUE, 'html' => $html);
          return $this->response->setJSON($output); //echo json_encode($output);
    }
  }

  //recuperation des profils
  function get_profils($id)
  {
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_ETAT_AVANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
    if(!empty($id))
    {
      $profils= $this->getBindParms('proc_profil_etape.PROFIL_ID,user_profil.PROFIL_DESCR', 'proc_profil_etape JOIN user_profil ON user_profil.PROFIL_ID=proc_profil_etape.PROFIL_ID', 'proc_profil_etape.ETAPE_ID=' . $id, 'PROFIL_DESCR ASC');
            $get_profils = $this->ModelPs->getRequete($callpsreq, $profils);
            $liste='';
      foreach ($get_profils as $value)
      {
        $liste='<li>'.$value->PROFIL_DESCR.'</li>';
      }

      $output = array('liste' => $liste);
      return $this->response->setJSON($output); 
    }
  }
}
?>

