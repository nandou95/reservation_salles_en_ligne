<?php
/*
*Jean Vainqueur RUGAMBA
*Titre: Liste  deréception par la BRB
*Numero de telephone: (+257) 66 33 43 25
*WhatsApp: (+257) 62 47 19 15
*Email: jean.vainqueur@mediabox.bi
*Date: 15 fév,2023
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Liste_Reception_Prise_Charge extends BaseController
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


  //Interface de la liste
  function index($id=0)
  {
    $data = $this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    // if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE') ==1)
    // {
    //   return redirect('double_commande_new/Receptio_Border_Dir_compt'); 
    // }

    // if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB') ==1)
    // {
    //   return redirect('double_commande_new/Reception_BRB/liste_vue'); 
    // }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $data_menu=$this->getDataMenuReception();

    $data['recep_prise_charge']=$data_menu['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$data_menu['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$data_menu['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$data_menu['deja_recep_dir_comptable'];
    $data['recep_brb']=$data_menu['recep_brb'];
    $data['déjà_recep_brb']=$data_menu['déjà_recep_brb'];

    $paiement = $this->count_paiement();
    $data['paie_a_faire'] = $paiement['get_paie_afaire'];
    $data['paie_deja_fait'] = $paiement['get_paie_deja_faire'];

    $data_titre=$this->nbre_titre_decaisse();
    $data['get_bord_brb']=$data_titre['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$data_titre['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$data_titre['get_bord_dc'];
    $data['get_bord_deja_dc']=$data_titre['get_bord_deja_dc'];

    $validee = $this->count_validation_titre();
    $data['get_titre_valide'] = $validee['get_titre_valide'];
    $data['get_titre_termine'] = $validee['get_titre_termine'];

    $data['profil_id'] = $profil_id;
    return view('App\Modules\double_commande_new\Views\Liste_Reception_Prise_Charge_View',$data);
  }

  //fonction pour affichage d'une liste
  public function listing()
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';         
    }
    $ID_INST = substr($ID_INST,0,-1);

    //Filtres de la liste

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1="";
    $critere2="";
    $critere3="";

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column=array('bord_trans.NUMERO_BORDEREAU_TRANSMISSION',1,1,1,1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY bord_trans.BORDEREAU_TRANSMISSION_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (bord_trans.NUMERO_BORDEREAU_TRANSMISSION LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere2." ".$critere3;
      //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebase="SELECT DISTINCT(bord_trans.BORDEREAU_TRANSMISSION_ID),bord_trans.NUMERO_BORDEREAU_TRANSMISSION,bord_trans.PATH_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission bord_trans JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_tache_detail det ON bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=17 AND bon_titre.TYPE_DOCUMENT_ID=1 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1";

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';


    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=17';
    $getEtape = "CALL getTable('" . $getEtape . "');";
    $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
    $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;

      //print_r($fetch_actions);exit();
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=17','PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      $bouton="";
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $bouton= "<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Traiter' href='".base_url($step."/".md5($row->BORDEREAU_TRANSMISSION_ID))."' ><span class='fa fa-arrow-up'></span></a>";
          }  
        }
      }
      
      //Nombre des bon d'engagement
      $count_bon = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_DETAIL_ID) AS nbre FROM execution_budgetaire_bordereau_transmission_bon_titre WHERE BORDEREAU_TRANSMISSION_ID=".$row->BORDEREAU_TRANSMISSION_ID;
      $count_bon = 'CALL `getTable`("'.$count_bon.'");';
      $nbre_bon = $this->ModelPs->getRequeteOne($count_bon);

        //Sommation des montants ordonnancement
      $sum_bon = "SELECT SUM(det.MONTANT_ORDONNANCEMENT) AS somme FROM execution_budgetaire_bordereau_transmission_bon_titre bon_tit JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=bon_tit.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE BORDEREAU_TRANSMISSION_ID=".$row->BORDEREAU_TRANSMISSION_ID;
      $sum_bon = 'CALL `getTable`("'.$sum_bon.'");';
      $total_ordo = $this->ModelPs->getRequeteOne($sum_bon);
        // $total_ordo = floatval($total_ordo);
      $total_ordo_value=!empty($total_ordo)?$total_ordo['somme']:0;

      $number = "<a  title='' style='color:#ffb944;' href='".base_url($step."/".md5($row->BORDEREAU_TRANSMISSION_ID))."' >" .  $row->NUMERO_BORDEREAU_TRANSMISSION . "</a>";

      $action='';
      $sub_array = array();
      $sub_array[] = $number;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_bon(".$row->BORDEREAU_TRANSMISSION_ID.")'>".$nbre_bon['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = number_format($total_ordo_value,2,","," ");   
      $sub_array[] ="<center><a href='".base_url('uploads/double_commande_new/'.$row->PATH_BORDEREAU_TRANSMISSION)."' target='_blank'><span class='fa fa-file-pdf' style='color:red;font-size: 30px;'></a></center>";        
      $action ="<a class='btn btn-primary btn-sm'  title='Traiter' href='".base_url($step."/".md5($row->BORDEREAU_TRANSMISSION_ID))."' ><span class='fa fa-arrow-up'></span></a>";
      $sub_array[] = $action;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //Deétail Bon
  public function detail_bons()
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $bord_id = $this->request->getPost('bord_id');

      //Filtres de la liste

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1="";
    $critere2="";
    $critere3="";

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column=array('exec.NUMERO_BON_ENGAGEMENT',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere2." ".$critere3;
      //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebase="SELECT DISTINCT exec.NUMERO_BON_ENGAGEMENT, det.MONTANT_ORDONNANCEMENT, suppl.PATH_BORDEREAU_ENGAGEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_info_suppl suppl ON exec.EXECUTION_BUDGETAIRE_ID=suppl.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_bordereau_transmission bord_trans ON bon_titre.BORDEREAU_TRANSMISSION_ID=bord_trans.BORDEREAU_TRANSMISSION_ID WHERE bord_trans.BORDEREAU_TRANSMISSION_ID=".$bord_id;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';


    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      //print_r($fetch_actions);exit();
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $mont_ordonnance = floatval($row->MONTANT_ORDONNANCEMENT);

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;       
      $sub_array[] ="<a href='".base_url('uploads/double_commande_new/'.$row->PATH_BORDEREAU_ENGAGEMENT)."' target='_blank'><span class='fa fa-file-pdf' style='color:red;font-size: 30px;'></a>";        
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //Interface de la liste des déjà réceptionnées
  function deja_recep($id=0)
  {
    $data = $this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $data_menu=$this->getDataMenuReception();
    $data['recep_prise_charge']=$data_menu['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$data_menu['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$data_menu['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$data_menu['deja_recep_dir_comptable'];
    $data['recep_brb']=$data_menu['recep_brb'];
    $data['déjà_recep_brb']=$data_menu['déjà_recep_brb'];

    $paiement = $this->count_paiement();
    $data['paie_a_faire'] = $paiement['get_paie_afaire'];
    $data['paie_deja_fait'] = $paiement['get_paie_deja_faire'];

    $data_titre=$this->nbre_titre_decaisse();
    $data['get_bord_brb']=$data_titre['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$data_titre['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$data_titre['get_bord_dc'];
    $data['get_bord_deja_dc']=$data_titre['get_bord_deja_dc'];

    $validee = $this->count_validation_titre();
    $data['get_titre_valide'] = $validee['get_titre_valide'];
    $data['get_titre_termine'] = $validee['get_titre_termine'];

    $data['profil_id'] = $profil_id;
    return view('App\Modules\double_commande_new\Views\Liste_Deja_Recep_Prise_Charge_View',$data);
  }

  //fonction pour affichage d'une liste déjà
  public function listing_deja()
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_affectation = $this->getBindParms('INSTITUTION_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';         
    }
    $ID_INST = substr($ID_INST,0,-1);

      //Filtres de la liste

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1="";
    $critere2="";
    $critere3="";


    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');


    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column=array('bord_trans.NUMERO_BORDEREAU_TRANSMISSION',1,1,1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY bord_trans.BORDEREAU_TRANSMISSION_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (bord_trans.NUMERO_BORDEREAU_TRANSMISSION LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere2." ".$critere3;
      //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebase="SELECT DISTINCT(bord_trans.BORDEREAU_TRANSMISSION_ID),bord_trans.NUMERO_BORDEREAU_TRANSMISSION,bord_trans.PATH_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission bord_trans JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_tache_detail det ON bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID>17 AND bon_titre.TYPE_DOCUMENT_ID=1 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2";

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';


    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      //print_r($fetch_actions);exit();
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      //Nombre des bon d'engagement
      $count_bon = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_DETAIL_ID) AS nbre FROM execution_budgetaire_bordereau_transmission_bon_titre WHERE BORDEREAU_TRANSMISSION_ID=".$row->BORDEREAU_TRANSMISSION_ID;
      $count_bon = 'CALL `getTable`("'.$count_bon.'");';
      $nbre_bon = $this->ModelPs->getRequeteOne($count_bon);

      //Sommation des montants ordonnancement
      $sum_bon = "SELECT SUM(MONTANT_ORDONNANCEMENT) AS somme FROM execution_budgetaire_bordereau_transmission_bon_titre bon_tit JOIN execution_budgetaire_tache_detail det ON bon_tit.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE bon_tit.TYPE_DOCUMENT_ID=1 AND bon_tit.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID>1 AND BORDEREAU_TRANSMISSION_ID=".$row->BORDEREAU_TRANSMISSION_ID;
      $sum_bon = 'CALL `getTable`("'.$sum_bon.'");';
      $total_ordo = $this->ModelPs->getRequeteOne($sum_bon);
        // $mont_total = floatval($total_ordo);
      $mont_total_value=!empty($total_ordo)?$total_ordo['somme']:0;


      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BORDEREAU_TRANSMISSION;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_bon(".$row->BORDEREAU_TRANSMISSION_ID.")'>".$nbre_bon['nbre']."</a></center>";
      $sub_array[] = $point;
      $sub_array[] = number_format($mont_total_value,2,","," ");        
      $sub_array[] ="<center><a href='".base_url('uploads/double_commande_new/'.$row->PATH_BORDEREAU_TRANSMISSION)."' target='_blank'><span class='fa fa-file-pdf' style='color:red;font-size: 30px;'></a></center>";        

      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //Deétail Bon déjà
  public function deja_detail_bons()
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    
    $bord_id = $this->request->getPost('bord_id');

      //Filtres de la liste

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $critere1="";
    $critere2="";
    $critere3="";

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column=array('exec.NUMERO_BON_ENGAGEMENT',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere2." ".$critere3;
      //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

      // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebase="SELECT DISTINCT exec.NUMERO_BON_ENGAGEMENT, det.MONTANT_ORDONNANCEMENT, suppl.PATH_BORDEREAU_ENGAGEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_info_suppl suppl ON exec.EXECUTION_BUDGETAIRE_ID=suppl.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=bon_titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_bordereau_transmission bord_trans ON bon_titre.BORDEREAU_TRANSMISSION_ID=bord_trans.BORDEREAU_TRANSMISSION_ID WHERE bord_trans.BORDEREAU_TRANSMISSION_ID=".$bord_id;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';


    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      //print_r($fetch_actions);exit();
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $mont_ordonnance = floatval($row->MONTANT_ORDONNANCEMENT);
      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;       
      $sub_array[] ="<a href='".base_url('uploads/double_commande_new/'.$row->PATH_BORDEREAU_ENGAGEMENT)."' target='_blank'><span class='fa fa-file-pdf' style='color:red;font-size: 30px;'></a>";        
      $data[] = $sub_array;
    }
    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebasefilter . '")');
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
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  // trouver le sous titre a partir de institution choisit
  function get_sousTutel($INSTITUTION_ID=0)
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

    $getSousTutel  = 'SELECT SOUS_TUTEL_ID,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE INSTITUTION_ID = '.$INSTITUTION_ID.' ORDER BY DESCRIPTION_SOUS_TUTEL  ASC';
    $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
    $sousTutel = $this->ModelPs->getRequete($getSousTutel);

    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($sousTutel as $key)
    {
      $html.='<option value="'.$key->SOUS_TUTEL_ID.'">'.$key->CODE_SOUS_TUTEL.'-'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
    }

    $output = array(
      "SousTutel" => $html,
    );

    return $this->response->setJSON($output);
  }
}
?>