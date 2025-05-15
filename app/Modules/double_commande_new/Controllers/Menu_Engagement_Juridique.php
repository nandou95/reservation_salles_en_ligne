<?php
/*Jean Vainqueur RUGAMBA
*Titre: Liste Engagement juridique à faire
*Numero de telephone: (+257) 66 33 43 25
*WhatsApp: (+257) 62 47 19 15
*Email: jean.vainqueur@mediabox.bi
*Date: 8 novembre,2023
*/
namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M');

class Menu_Engagement_Juridique extends BaseController
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
    $db = db_connect();
    $bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }

  //Debut liste des engagements juridiques à faire
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data_menu=$this->getDataMenuJuridique_new();
    $data['institutions']=$data_menu['institutions'];
    $data['get_jurid_Afaire']=$data_menu['jur_a_faire'];
    $data['get_jurid_deja_fait']=$data_menu['jur_deja_fait'];
    $data['get_jurid_Avalider'] = $data_menu['jur_a_valider'];
    $data['get_jurid_valider'] = $data_menu['jur_deja_valide'];
    $data['get_jurid_Acorriger'] = $data_menu['jur_a_corriger'];
    $data['get_jurid_Arejeter'] = $data_menu['jur_rejeter'];
    return view('App\Modules\double_commande_new\Views\Eng_Jur_Faire_View',$data);
  }

  public function listing()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;


    $var_search = str_replace("'", "", $var_search);

    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID = 6 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN(".$ID_INST.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE  LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE  LIKE '%$var_search%')"):'';

   
    $critaire = $critere1." ".$critere3." ".$critere2;
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $et_db_comm=6;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
      $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;

      $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      if(!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number="<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

            $bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-arrow-up'></span></a>";
          }
        }
      }
      
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");
      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
      $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
      $sub_array[] = $action;
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

  //Fin liste des engagements juridiques à faire

  //Debut liste des engagements juridiques déjà faits
  function eng_jur_deja_fait($id=0)
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data_menu=$this->getDataMenuJuridique_new();
    $data['institutions']=$data_menu['institutions'];
    $data['get_jurid_Afaire']=$data_menu['jur_a_faire'];
    $data['get_jurid_deja_fait']=$data_menu['jur_deja_fait'];
    $data['get_jurid_Avalider'] = $data_menu['jur_a_valider'];
    $data['get_jurid_valider'] = $data_menu['jur_deja_valide'];
    $data['get_jurid_Acorriger'] = $data_menu['jur_a_corriger'];
    $data['get_jurid_Arejeter'] = $data_menu['jur_rejeter'];

    return view('App\Modules\double_commande_new\Views\Eng_Jur_Deja_Fait_View',$data);
  }

  public function listing_deja_fait()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    $critere4="";
    $critere5="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    $str_condiction_user=" AND histo.USER_ID=".$user_id;
    if($profil_id==1)
    {
      $str_condiction_user="";
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID>6 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.  EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE histo.ETAPE_DOUBLE_COMMANDE_ID=6 ".$str_condiction_user.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2." ".$critere4." ".$critere5;
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
        $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $ENG_JURIDIQUE;
      $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

      $action .="<li>
                  <a href='".base_url("double_commande_new/detail/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."'><label>&nbsp;&nbsp;Détails</label></a>
                 </li>";

      $action .= ($row->ETAPE_DOUBLE_COMMANDE_ID == 7) ? "
        <li>
          <a href='javascript:void(0)' onclick='show_modal(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")' title='corriger' ><label>&nbsp;&nbsp;<font color='green'>Correction</font></label></a>
        </li>" : "";

      $action .="
        <div style='display:none;' id='message".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
          <center>
            <h5><strong>Voulez_vous corriger <br><center><font color='green'>".$row->NUMERO_BON_ENGAGEMENT."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
            </h5>
          </center>
        </div>
        <div style='display:none;' id='footer".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
          <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
                              ".lang('messages_lang.quiter_action')."
          </button>
          <a href='".base_url("double_commande_new/Menu_Engagement_Juridique/is_correction/".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.action_corriger_pip')."
          </a>
        </div>";

      $sub_array[] = $action;
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

  //Fin liste des engagements juridiques déjà faits

  //Debut liste des engagements juridiques à corriger
  function eng_jur_corriger($id=0)
  {
    $data = $this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id ='';
    $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE');

    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($gdc!=1)
    {
      return  redirect('Login_Ptba/homepage');
    }

    $data_menu=$this->getDataMenuJuridique_new();

    $data['institutions']=$data_menu['institutions'];

    $data['get_jurid_Afaire']=$data_menu['jur_a_faire'];
    $data['get_jurid_deja_fait']=$data_menu['jur_deja_fait'];
    $data['get_jurid_Avalider'] = $data_menu['jur_a_valider'];
    $data['get_jurid_valider'] = $data_menu['jur_deja_valide'];
    $data['get_jurid_Acorriger'] = $data_menu['jur_a_corriger'];
    $data['get_jurid_Arejeter'] = $data_menu['jur_rejeter'];
    return view('App\Modules\double_commande_new\Views\Eng_Jur_Corr_View',$data);
  }

  public function listing_jur_corriger()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    $cond_user_id='';
    if($profil_id!=1)
    {
      $cond_user_id=' AND exec.USER_ID='.$user_id;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID = 8 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN(".$ID_INST.") AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$cond_user_id.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE  LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2;
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
      $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;

      $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      if(!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number="<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

            $bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-arrow-up'></span></a>";
          }
        }
      }
      
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
        $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $ENG_JURIDIQUE;
      $action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
      $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
      $sub_array[] = $action;
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

  //Fin liste des engagements juridiques à corriger

  //Debut liste des engagements juridiques à valider
  function eng_jur_valider($id=0)
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data_menu=$this->getDataMenuJuridique_new();

    $data['institutions']=$data_menu['institutions'];
    $data['get_jurid_Afaire']=$data_menu['jur_a_faire'];
    $data['get_jurid_deja_fait']=$data_menu['jur_deja_fait'];
    $data['get_jurid_Avalider'] = $data_menu['jur_a_valider'];
    $data['get_jurid_valider'] = $data_menu['jur_deja_valide'];
    $data['get_jurid_Acorriger'] = $data_menu['jur_a_corriger'];
    $data['get_jurid_Arejeter'] = $data_menu['jur_rejeter'];
    return view('App\Modules\double_commande_new\Views\Eng_Jur_Valider_View',$data);
  }

  public function listing_jur_valider()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID = 7 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN(".$ID_INST.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2;
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
      $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;

      $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      if(!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number="<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

            $bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-arrow-up'></span></a>";
          }
        }
      }
      
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
        $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $ENG_JURIDIQUE;
      $action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
      $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
      $sub_array[] = $action;
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

  //Debut liste des engagements juridiques à valider

  //Debut liste des engagements juridiques déjà validés
  function eng_jur_deja_valide($id=0)
  {
    $data = $this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id ='';
    $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE');
    $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED');

    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($gdc!=1 AND $ced!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data_menu=$this->getDataMenuJuridique_new();

    $data['institutions']=$data_menu['institutions'];

    $data['get_jurid_Afaire']=$data_menu['jur_a_faire'];
    $data['get_jurid_deja_fait']=$data_menu['jur_deja_fait'];
    $data['get_jurid_Avalider'] = $data_menu['jur_a_valider'];
    $data['get_jurid_valider'] = $data_menu['jur_deja_valide'];
    $data['get_jurid_Acorriger'] = $data_menu['jur_a_corriger'];
    $data['get_jurid_Arejeter'] = $data_menu['jur_rejeter'];
    return view('App\Modules\double_commande_new\Views\Eng_Jur_Deja_Valide_View',$data);
  }

  public function listing_deja_valide()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1 AND $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    $critere4="";
    $critere5="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
    }

    $str_condiction_user=" AND USER_ID=".$user_id;
    if($profil_id==1)
    {
      $str_condiction_user="";
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID > 9 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN (".$ID_INST.") AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.  EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1".$str_condiction_user.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE  LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE  LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2." ".$critere4." ".$critere5;
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
        $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $ENG_JURIDIQUE;
      $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

      $action .="<li>
                  <a href='".base_url("double_commande_new/detail/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."'><label>&nbsp;&nbsp;Détails</label></a>
                 </li>";

      $action .= ($row->ETAPE_DOUBLE_COMMANDE_ID == 7) ? "
        <li>
          <a href='javascript:void(0)' onclick='show_modal(".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.")' title='corriger' ><label>&nbsp;&nbsp;<font color='green'>Correction</font></label></a>
        </li>" : "";

      $action .="
        <div style='display:none;' id='message".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
          <center>
            <h5><strong>Voulez_vous corriger <br><center><font color='green'>".$row->NUMERO_BON_ENGAGEMENT."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
            </h5>
          </center>
        </div>
        <div style='display:none;' id='footer".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'>
          <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
                              ".lang('messages_lang.quiter_action')."
          </button>
          <a href='".base_url("double_commande_new/Menu_Engagement_Juridique/is_correction/".$row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.action_corriger_pip')."
          </a>
        </div>";

      $sub_array[] = $action;
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

  //Fin liste des engagements juridiques déjà validés 

  //Debut liste des engagements juridiques rejeter
  function eng_jur_rejeter($id=0)
  {
    $data = $this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id ='';

    $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE');
    $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED');
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($gdc!=1 AND $ced!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data_menu=$this->getDataMenuJuridique_new();
    $data['institutions']=$data_menu['institutions'];
    $data['get_jurid_Afaire']=$data_menu['jur_a_faire'];
    $data['get_jurid_deja_fait']=$data_menu['jur_deja_fait'];
    $data['get_jurid_Avalider'] = $data_menu['jur_a_valider'];
    $data['get_jurid_valider'] = $data_menu['jur_deja_valide'];
    $data['get_jurid_Acorriger'] = $data_menu['jur_a_corriger'];
    $data['get_jurid_Arejeter'] = $data_menu['jur_rejeter'];

    return view('App\Modules\double_commande_new\Views\Eng_Jur_Rejet_View',$data);
  }

  public function listing_jur_rejeter()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1 AND $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }

    $ID_INST = substr($ID_INST,0,-1);

    $critere1="";
    $critere2=" ";
    $critere3="";
    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $group = "";
    $requetedebase="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE_DEVISE,exec.ENG_JURIDIQUE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID = 9 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN(".$ID_INST.")";

    $order_by = '';
    $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','ENG_JURIDIQUE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2;
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter = $critaire . " ". $search ." " . $group;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
      $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
      $getEtape = "CALL getTable('" . $getEtape . "');";
      $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
      $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;

      $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$et_db_comm,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      $number =$row->NUMERO_BON_ENGAGEMENT;
      if(!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";
            $bouton="<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-arrow-up'></span></a>";
          }
        }
      }
      
      //Nombre des tâches
      $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
      $count_task = 'CALL `getTable`("'.$count_task.'");';
      $nbre_task = $this->ModelPs->getRequeteOne($count_task);

      $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0,","," ");

      $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
      $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,0,","," ");

      if($row->DEVISE_TYPE_ID!=1)
      {
        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE_DEVISE);
        $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,4,","," ");

        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE_DEVISE);
        $ENG_JURIDIQUE=number_format($ENG_JURIDIQUE,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
      $sub_array[] = $point;               
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $ENG_BUDGETAIRE;
      $sub_array[] = $ENG_JURIDIQUE;
      $action1 ='<div class="row dropdown" style="color:#fff;">"'.$bouton.'"';
      $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
      $sub_array[] = $action;
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

  //Cette fonction retourne le nombre des chiffres d un nombre ($value) passé en paramètre
  function get_precision($value=0)
  {
    $parts = explode('.', strval($value));
    return isset($parts[1]) ? strlen($parts[1]) : 0; 
  }

  //fonction pour exporter les eng juridique deja validees en PDF
  function exporter_pdf_deja_valide($INSTITUTION_ID='',$SOUS_TUTEL_ID='',$DATE_DEBUT=0,$DATE_FIN=0)
  {

    $session  = \Config\Services::session();
    $user_id ='';
    $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE');
    $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED');
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($gdc!=1 AND $ced!=1)
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
    $critere1="";
    $critere2="";
    $critere4="";
    $critere5="";

    if($INSTITUTION_ID != 0)
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
    }
    else{
      $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
    }

    if($SOUS_TUTEL_ID != 0)
    {
      $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
    }

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $cond_user_id='';
    if($profil_id!=1)
    {
      $cond_user_id=' AND histo.USER_ID='.$user_id;
    }

    $var_search = '';
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $group = "";
    $critaire = "";
    $limit = '';
    $order_by = '';
    $order_column='';
    $search ='';

    $critaire = $critere1." ".$critere2." ".$critere4." ".$critere5;
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    $requetedebase="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID),
                           det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           dev.DEVISE_TYPE_ID,
                           dev.DESC_DEVISE_TYPE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_BUDGETAIRE_DEVISE,
                           exec.ENG_JURIDIQUE,
                           exec.ENG_JURIDIQUE_DEVISE,
                           DESC_TACHE,
                           act.DESC_PAP_ACTIVITE,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           exec.COMMENTAIRE,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ptba_tache.SOUS_TUTEL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.DATE_ENG_JURIDIQUE,
                           titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID 
                    FROM  execution_budgetaire_execution_tache exec_tache 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID 
                    JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID 
                    JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID 
                    LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID 
                    JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
                    JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID =exec.EXECUTION_BUDGETAIRE_ID 
                    WHERE titre.ETAPE_DOUBLE_COMMANDE_ID >9 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND ptba_tache.INSTITUTION_ID IN(".$ID_INST.") AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$cond_user_id.")";

    $requetedebases=$requetedebase." ".$conditions;

    $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';

    $getData = $this->ModelPs->datatable($query_secondaire);

    //GET CURRENT INSTITUTIONS
    $requeteInst = "SELECT DESCRIPTION_INSTITUTION 
    FROM inst_institutions
    WHERE INSTITUTION_ID = $INSTITUTION_ID";
    $requeteInst = 'CALL `getTable`("'.$requeteInst.'");';
    $getInst = $this->ModelPs->datatable($requeteInst);

    //GET CURRENT SOUS TUTEL
    $requeteSousTut = "SELECT DESCRIPTION_SOUS_TUTEL 
    FROM inst_institutions_sous_tutel
    WHERE SOUS_TUTEL_ID = $SOUS_TUTEL_ID";
    $requeteSousTut = 'CALL `getTable`("'.$requeteSousTut.'");';
    $getSousTut = $this->ModelPs->datatable($requeteSousTut);

    $DESCRIPTION_INSTITUTION = $getInst ? $getInst[0]->DESCRIPTION_INSTITUTION : '';
    $DESCRIPTION_INSTITUTION = !empty($DESCRIPTION_INSTITUTION) ? $DESCRIPTION_INSTITUTION : '-';

    $DESCRIPTION_SOUS_TUTEL = $getSousTut ? $getSousTut[0]->DESCRIPTION_SOUS_TUTEL : '';
    $DESCRIPTION_SOUS_TUTEL = !empty($DESCRIPTION_SOUS_TUTEL) ? $DESCRIPTION_SOUS_TUTEL : '-';

    $html="<html> <body>";
    $html.="<center><b><h3><u>".lang('messages_lang.titre_jur_dej_valide')."</u></h3></b></center>";
    $html.="<h5>
    ".lang('messages_lang.table_institution').": 
    ".$DESCRIPTION_INSTITUTION."
    </h5>";
    $html.="<h5>
    ".lang('messages_lang.table_st').": 
    ".$DESCRIPTION_SOUS_TUTEL."
    </h5>";
    
    $html.='<table cellspacing="0">';
    $html.='<tr>
    <th style="border: 1px solid #000; font-size: 10px;">#</th>
    <th style="border: 1px solid #000; font-size: 10px;">BON D\'ENGAGEMENT</th>
    <th style="border: 1px solid #000; font-size: 10px;">IMPUTATION</th>
    <th style="border: 1px solid #000; font-size: 10px;">TACHE</th>
    <th style="border: 1px solid #000; font-size: 10px;">DEVISE</th>
    <th style="border: 1px solid #000; font-size: 10px;">ENGAGEMENT BUDGETAIRE</th>
    <th style="border: 1px solid #000; font-size: 10px;">ENGAGEMENT JURIDIQUE</th>
    </tr>';
    $i=0;
    foreach($getData as $key)
    {
      $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $get_task = 'CALL `getTable`("'.$get_task.'");';
      $tasks = $this->ModelPs->getRequete($get_task);
      $task_items = '';

      foreach ($tasks as $task) {
        $task_items .= '<li style="margin-left:+10px">'.$task->DESC_TACHE.'</li>';
      }

      $TACHE=empty($task_items) ? '<center>-</center>' : $task_items;

      $i++;
      $html .= '<tr>';
      $html .= '<td style="border: 1px solid #000; font-size: 8px; ">' . $i. '</td>';
      $html .= '<td style="border: 1px solid #000; font-size: 8px; ">' . $key->NUMERO_BON_ENGAGEMENT . '</td>';
      $html .= '<td style="border: 1px solid #000; font-size: 8px; ">' . $key->CODE_NOMENCLATURE_BUDGETAIRE . '</td>';

      // $DESC_PAP_ACTIVITE = !empty($key->DESC_PAP_ACTIVITE) ? $key->DESC_PAP_ACTIVITE : '<center>-</center>';
      // $html .= '<td style="border: 1px solid #000; font-size: 8px; ">' . $DESC_PAP_ACTIVITE . '</td>';
      $html .= '<td style="border: 1px solid #000; font-size: 8px; ">' . $TACHE . '</td>';
      // $html .= '<td style="border: 1px solid #000; font-size: 8px; ">' . $key->COMMENTAIRE . '</td>';
      $html .= '<td style="border: 1px solid #000; font-size: 8px; ">' . $key->DESC_DEVISE_TYPE . '</td>';
      $html .= '<td style="border: 1px solid #000; font-size: 8px; ">' . number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " ") . '</td>';
      $html .= '<td style="border: 1px solid #000; font-size: 8px; ">' .number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "). '</td>';
      $html .= '</tr>';
    }

    $html.='</table>';
    $html.="</body></html>";

    $dompdf = new Dompdf();
      // Charger le contenu HTML
    $dompdf->loadHtml($html);
        // Définir la taille et l'orientation du papier
    $dompdf->setPaper('A4', 'landscape');

      // Rendre le HTML en PDF
    $dompdf->render();
      // Envoyer le fichier PDF en tant que téléchargement
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="engagements_juridiques_deja_valide'.uniqid().'.pdf"');
    echo $dompdf->output();
  }

  //fonction pour exporter les eng juridique deja validees en EXCEL
  function exporter_excel_deja_valide($INSTITUTION_ID='',$SOUS_TUTEL_ID='',$DATE_DEBUT=0,$DATE_FIN=0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE');
    $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED');
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($gdc!=1 AND $ced!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    $critere1='';
    $critere2='';
    $critere4="";
    $critere5="";

    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $cond_user_id='';
    if($profil_id!=1)
    {
      $cond_user_id=' AND histo.USER_ID='.$user_id;
    }


    if($INSTITUTION_ID != 0)
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
    }
    else{
      $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
    }

    if($SOUS_TUTEL_ID != 0)
    {
      $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
    }

    $group = "";
    $requetedebase="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID),
                           det.EXECUTION_BUDGETAIRE_DETAIL_ID,
                           dev.DEVISE_TYPE_ID,
                           dev.DESC_DEVISE_TYPE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_BUDGETAIRE_DEVISE,
                           exec.ENG_JURIDIQUE,
                           exec.ENG_JURIDIQUE_DEVISE,
                           ptba_tache.DESC_TACHE,
                           act.DESC_PAP_ACTIVITE,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           exec.COMMENTAIRE,
                           exec.NUMERO_BON_ENGAGEMENT,
                           ptba_tache.SOUS_TUTEL_ID,
                           exec.EXECUTION_BUDGETAIRE_ID,
                           exec.DATE_ENG_JURIDIQUE,
                           titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID
                    FROM  execution_budgetaire_execution_tache exec_tache 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID 
                    JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID 
                    JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID 
                    LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID 
                    JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
                    JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID =exec.EXECUTION_BUDGETAIRE_ID 
                    WHERE titre.ETAPE_DOUBLE_COMMANDE_ID >9 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND ptba_tache.INSTITUTION_ID IN(".$ID_INST.") AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$cond_user_id.") ".$critere2.$critere1.$critere4.$critere5.$group;

    $query_secondaire = 'CALL `getTable`("'.$requetedebase.'");';

    $getData = $this->ModelPs->datatable($query_secondaire);

      //GET CURRENT INSTITUTIONS
    $requeteInst = "SELECT DESCRIPTION_INSTITUTION 
    FROM inst_institutions
    WHERE INSTITUTION_ID = $INSTITUTION_ID";
    $requeteInst = 'CALL `getTable`("'.$requeteInst.'");';
    $getInst = $this->ModelPs->datatable($requeteInst);

      //GET CURRENT SOUS TUTEL
    $requeteSousTut = "SELECT DESCRIPTION_SOUS_TUTEL 
    FROM inst_institutions_sous_tutel
    WHERE SOUS_TUTEL_ID = $SOUS_TUTEL_ID";
    $requeteSousTut = 'CALL `getTable`("'.$requeteSousTut.'");';
    $getSousTut = $this->ModelPs->datatable($requeteSousTut);

    $DESCRIPTION_INSTITUTION = $getInst ? $getInst[0]->DESCRIPTION_INSTITUTION : '';
    $DESCRIPTION_INSTITUTION = !empty($DESCRIPTION_INSTITUTION) ? $DESCRIPTION_INSTITUTION : '-';

    $DESCRIPTION_SOUS_TUTEL = $getSousTut ? $getSousTut[0]->DESCRIPTION_SOUS_TUTEL : '';
    $DESCRIPTION_SOUS_TUTEL = !empty($DESCRIPTION_SOUS_TUTEL) ? $DESCRIPTION_SOUS_TUTEL : '-';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A2', 'LISTE DES ENGAGEMENTS JURIDIQUES DEJA VALIDES');

    $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
    $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
    $sheet->setCellValue('A7', '#');
    $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
    $sheet->setCellValue('C7', 'IMPUTATION');
    // $sheet->setCellValue('D7', 'ACTIVITE');
    $sheet->setCellValue('D7', 'TACHE');
    // $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
    $sheet->setCellValue('E7', 'DEVISE');
    $sheet->setCellValue('F7', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('G7', 'ENGAGEMENT JURIDIQUE');
    $rows = 9;
    $i=1;
    foreach ($getData as $key)
    {
      //Nombre des tâches
      $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $get_task = 'CALL `getTable`("'.$get_task.'");';
      $tasks = $this->ModelPs->getRequete($get_task);
      $task_items = '';

      foreach ($tasks as $task) {
        $task_items .= "- ".$task->DESC_TACHE . "\n";
      }

      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
      $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      // $DESC_PAP_ACTIVITE = !empty($key->DESC_PAP_ACTIVITE) ? $key->DESC_PAP_ACTIVITE : '-';
      // $sheet->setCellValue('D' . $rows, $DESC_PAP_ACTIVITE);
      $sheet->setCellValue('D' . $rows, trim($task_items));
      $sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
      // $sheet->setCellValue('E' . $rows, $key->COMMENTAIRE);
      $sheet->setCellValue('E' . $rows, $key->DESC_DEVISE_TYPE);
      $sheet->setCellValue('F' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
      $sheet->setCellValue('G' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
      $rows++;
      $i++;
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save('engagements_juridiques_deja_valides.xlsx');
    return $this->response->download('engagements_juridiques_deja_valides.xlsx', null)->setFileName('Engagements juridiques valides'.uniqid().'.xlsx');
    return redirect('double_commande_new/Menu_Engagement_Juridique/eng_jur_deja_valides');
  }

  //fonction pour exporter les eng juridique deja validees en EXCEL
  function exporter_Excel_deja_fait($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE');
    $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED');
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($gdc!=1 AND $ced!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    $critere1='';
    $critere2='';
    $critere4="";
    $critere5="";

    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $cond_user_id='';
    if($profil_id!=1)
    {
      $cond_user_id=' AND histo.USER_ID='.$user_id;
    }


    if($INSTITUTION_ID != 0)
    {
      $critere1=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
    }
    else{
      $critere1=" AND ptba_tache.INSTITUTION_ID IN (".$ID_INST.")";
    }

    if($SOUS_TUTEL_ID != 0)
    {
      $critere2=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $critere4.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $critere5.=" AND exec.DATE_ENG_JURIDIQUE >= '".$DATE_DEBUT."' AND exec.DATE_ENG_JURIDIQUE <= '".$DATE_FIN."'";
    }

    $group = "";
    $requetedebase="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID),
                           titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,
                           dev.DEVISE_TYPE_ID,
                           dev.DESC_DEVISE_TYPE,
                           exec.ENG_BUDGETAIRE,
                           exec.ENG_BUDGETAIRE_DEVISE,
                           exec.ENG_JURIDIQUE,
                           exec.ENG_JURIDIQUE_DEVISE,
                           ligne.CODE_NOMENCLATURE_BUDGETAIRE,
                           ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,
                           titre.ETAPE_DOUBLE_COMMANDE_ID,
                           exec.NUMERO_BON_ENGAGEMENT 
                    FROM execution_budgetaire exec 
                    JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
                    JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID 
                    JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID
                    JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID 
                    JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID 
                    WHERE titre.ETAPE_DOUBLE_COMMANDE_ID >6 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND ptba_tache.INSTITUTION_ID IN(".$ID_INST.") AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE histo.ETAPE_DOUBLE_COMMANDE_ID=6 ".$cond_user_id.") ".$critere2.$critere1.$critere4.$critere5.$group;

    $query_secondaire = 'CALL `getTable`("'.$requetedebase.'");';

    $getData = $this->ModelPs->datatable($query_secondaire);

      //GET CURRENT INSTITUTIONS
    $requeteInst = "SELECT DESCRIPTION_INSTITUTION 
    FROM inst_institutions
    WHERE INSTITUTION_ID = $INSTITUTION_ID";
    $requeteInst = 'CALL `getTable`("'.$requeteInst.'");';
    $getInst = $this->ModelPs->datatable($requeteInst);

      //GET CURRENT SOUS TUTEL
    $requeteSousTut = "SELECT DESCRIPTION_SOUS_TUTEL 
    FROM inst_institutions_sous_tutel
    WHERE SOUS_TUTEL_ID = $SOUS_TUTEL_ID";
    $requeteSousTut = 'CALL `getTable`("'.$requeteSousTut.'");';
    $getSousTut = $this->ModelPs->datatable($requeteSousTut);

    $DESCRIPTION_INSTITUTION = $getInst ? $getInst[0]->DESCRIPTION_INSTITUTION : '';
    $DESCRIPTION_INSTITUTION = !empty($DESCRIPTION_INSTITUTION) ? $DESCRIPTION_INSTITUTION : '-';

    $DESCRIPTION_SOUS_TUTEL = $getSousTut ? $getSousTut[0]->DESCRIPTION_SOUS_TUTEL : '';
    $DESCRIPTION_SOUS_TUTEL = !empty($DESCRIPTION_SOUS_TUTEL) ? $DESCRIPTION_SOUS_TUTEL : '-';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A2', 'LISTE DES ENGAGEMENTS JURIDIQUES DEJA FAITS');

    $sheet->setCellValue('A3', lang('messages_lang.table_institution').': '.$DESCRIPTION_INSTITUTION);
    $sheet->setCellValue('A4', lang('messages_lang.table_st').': '.$DESCRIPTION_SOUS_TUTEL);
    $sheet->setCellValue('A7', '#');
    $sheet->setCellValue('B7', 'BON D\'ENGAGEMENT');
    $sheet->setCellValue('C7', 'IMPUTATION');
    // $sheet->setCellValue('D7', 'ACTIVITE');
    $sheet->setCellValue('D7', 'TACHE');
    // $sheet->setCellValue('F7', 'OBJECT D\'ENGAGEMENT');
    $sheet->setCellValue('E7', 'DEVISE');
    $sheet->setCellValue('F7', 'ENGAGEMENT BUDGETAIRE');
    $sheet->setCellValue('G7', 'ENGAGEMENT JURIDIQUE');
    $rows = 9;
    $i=1;
    foreach ($getData as $key)
    {
      //Nombre des tâches
      $get_task = "SELECT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID, DESC_TACHE FROM execution_budgetaire_execution_tache ebet LEFT JOIN execution_budgetaire_titre_decaissement ebtd  ON ebtd.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID WHERE ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$key->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $get_task = 'CALL `getTable`("'.$get_task.'");';
      $tasks = $this->ModelPs->getRequete($get_task);
      $task_items = '';

      foreach ($tasks as $task) {
        $task_items .= "- ".$task->DESC_TACHE . "\n";
      }

      $sheet->setCellValue('A' . $rows, $i);
      $sheet->setCellValue('B' . $rows, $key->NUMERO_BON_ENGAGEMENT);
      $sheet->setCellValue('C' . $rows, $key->CODE_NOMENCLATURE_BUDGETAIRE);
      // $DESC_PAP_ACTIVITE = !empty($key->DESC_PAP_ACTIVITE) ? $key->DESC_PAP_ACTIVITE : '-';
      // $sheet->setCellValue('D' . $rows, $DESC_PAP_ACTIVITE);
      $sheet->setCellValue('D' . $rows, trim($task_items));
      $sheet->getStyle('D' . $rows)->getAlignment()->setWrapText(true);
      // $sheet->setCellValue('E' . $rows, $key->COMMENTAIRE);
      $sheet->setCellValue('E' . $rows, $key->DESC_DEVISE_TYPE);
      $sheet->setCellValue('F' . $rows, number_format($key->ENG_BUDGETAIRE, $this->get_precision($key->ENG_BUDGETAIRE), ",", " "));
      $sheet->setCellValue('G' . $rows, number_format($key->ENG_JURIDIQUE, $this->get_precision($key->ENG_JURIDIQUE), ",", " "));
      $rows++;
      $i++;
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save('engagements_juridiques_deja_faits.xlsx');
    return $this->response->download('engagements_juridiques_deja_faits.xlsx', null)->setFileName('Engagements juridiques faits'.uniqid().'.xlsx');
    return redirect('double_commande_new/Menu_Engagement_Juridique/eng_jur_deja_fait');
  }

  //Fin liste des engagements juridiques rejeter
  // trouver le sous titre a partir de institution choisit
  function get_sousTutel($INSTITUTION_ID=0)
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

    $getSousTutel='SELECT SOUS_TUTEL_ID,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE INSTITUTION_ID='.$INSTITUTION_ID.' ORDER BY DESCRIPTION_SOUS_TUTEL ASC';
    $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
    $sousTutel = $this->ModelPs->getRequete($getSousTutel);

    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($sousTutel as $key)
    {
      $html.='<option value="'.$key->SOUS_TUTEL_ID.'">'.$key->CODE_SOUS_TUTEL.'-'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
    }

    $output=array(
      "SousTutel" => $html
    );
    return $this->response->setJSON($output);
  }

  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  //fonction pour la correction
  function is_correction($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)
  {
    $session  = \Config\Services::session();
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind_detail_etape = $this->getBindParms('EXECUTION_BUDGETAIRE_ID,ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_titre_decaissement', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,'EXECUTION_BUDGETAIRE_ID ASC');
    $etape= $this->ModelPs->getRequeteOne($callpsreq, $bind_detail_etape);

    $datatoupdate='';
    if($etape['ETAPE_DOUBLE_COMMANDE_ID']== 7)
    {
      $datatoupdate= 'ETAPE_DOUBLE_COMMANDE_ID=8';
    }
    else{
      return redirect('Login_Ptba/homepage');
    }

    $updateTable='execution_budgetaire_titre_decaissement';
    $critere = " EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
    $this->update_all_table($updateTable,$datatoupdate,$critere);
    // $this->gestion_rejet_ptba($etape['EXECUTION_BUDGETAIRE_ID']);
    $url = 'double_commande_new/Menu_Engagement_Juridique/eng_jur_corriger';
    return redirect($url);
  }

  //get les detail des taches
  function detail_task()
  {
    $session  = \Config\Services::session();

    $task_id = $this->request->getPost('task_id');

      //Filtres de la liste
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $critere1="";

    $critere3="";

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';

    $requetedebase="SELECT DISTINCT(task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID),
                           task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,
                           task.EXECUTION_BUDGETAIRE_ID,
                           task.PTBA_TACHE_ID,
                           task.MONTANT_ENG_BUDGETAIRE,
                           task.MONTANT_ENG_BUDGETAIRE_DEVISE,
                           task.MONTANT_ENG_JURIDIQUE,
                           task.MONTANT_ENG_JURIDIQUE_DEVISE,
                           ptba.DESC_TACHE,
                           task.QTE,
                           dev.DEVISE_TYPE_ID,
                           dev.DESC_DEVISE_TYPE,
                           ebtd.ETAPE_DOUBLE_COMMANDE_ID 
                    FROM execution_budgetaire_execution_tache task 
                    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID 
                    JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID 
                    JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID 
                    JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID
                    WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND task.EXECUTION_BUDGETAIRE_ID=".$task_id."";

    $order_column=array('ptba.DESC_TACHE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE');

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ptba.DESC_TACHE LIKE '%$var_search%' OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3;

    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $order_by . " " . $limit;

    // condition pour le query filter
    $conditionsfilter=$critaire." ".$search;

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    { 

      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 9) ? (mb_substr($row->DESC_TACHE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE);
      $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,0,","," ");

      $MONTANT_ENG_JURIDIQUE=floatval($row->MONTANT_ENG_JURIDIQUE);
      $MONTANT_ENG_JURIDIQUE=number_format($MONTANT_ENG_JURIDIQUE,0,","," ");
      if($row->DEVISE_TYPE_ID!=1)
      {
        $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE_DEVISE);
        $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,4,","," ");

        $MONTANT_ENG_JURIDIQUE=floatval($row->MONTANT_ENG_JURIDIQUE_DEVISE);
        $MONTANT_ENG_JURIDIQUE=number_format($MONTANT_ENG_JURIDIQUE,4,","," ");
      }

      $action='';
      $sub_array = array();
      $sub_array[] = $DESC_TACHE;
      $sub_array[] = $row->QTE;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $MONTANT_ENG_BUDGETAIRE;
      if($row->ETAPE_DOUBLE_COMMANDE_ID > 6){$sub_array[] = $MONTANT_ENG_JURIDIQUE;}
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebases. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);//echo json_encode($output);
  }
}
?>
