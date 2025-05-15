<?php
  /**Jean Vainqueur RUGAMBA
    *Titre: Liste Engagement juridique à faire
    *Numero de telephone: (+257) 66 33 43 25
    *WhatsApp: (+257) 62 47 19 15
    *Email: jean.vainqueur@mediabox.bi
    *Date: 8 novembre,2023
    **/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;

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


    //Interface de la liste des engagements juridiques à faire
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

      //Récuperation des étapes
      $bind_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID IN(7)','ETAPE_DOUBLE_COMMANDE_ID ASC');
      $data['step'] = $this->ModelPs->getRequete($callpsreq, $bind_step);

      $data['institutions']=$data_menu['institutions'];
      $data['get_jurid_Afaire']=$data_menu['jur_a_faire'];
      $data['get_jurid_deja_fait']=$data_menu['jur_deja_fait'];
      $data['get_jurid_Avalider'] = $data_menu['jur_a_valider'];
      $data['get_jurid_valider'] = $data_menu['jur_deja_valide'];
      $data['get_jurid_Acorriger'] = $data_menu['jur_a_corriger'];
      $data['get_jurid_Arejeter'] = $data_menu['jur_rejeter'];
      return view('App\Modules\double_commande_new\Views\Eng_Jur_Faire_View',$data);
    }

    //fonction pour affichage d'une liste des engagemenets juridiques à faire
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

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
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
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $ETAPE_ID = $this->request->getPost('ETAPE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $critere1="";
      $critere2="";
      $critere3="";

      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else
      {
        $critere1=" AND exec.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      if(!empty($ETAPE_ID))
      {
        $critere3=" AND det.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_ID;

      }else{

        $critere3=" AND det.ETAPE_DOUBLE_COMMANDE_ID=6";
      }

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

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'etap.DESC_ETAPE_DOUBLE_COMMANDE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY det.EXECUTION_BUDGETAIRE_DETAIL_ID  ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR etap.DESC_ETAPE_DOUBLE_COMMANDE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere2." ".$critere3;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID),det.EXECUTION_BUDGETAIRE_DETAIL_ID,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.SOUS_TUTEL_ID,exec.ENG_JURIDIQUE,etap.DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN execution_budgetaire_etape_double_commande etap ON etap.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1";
      
      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';


      $fetch_actions = $this->ModelPs->datatable($query_secondaire);
      
      //print_r($fetch_actions);exit();
      $data = array();
      $u=1;
      foreach ($fetch_actions as $row)
      {
        $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
        $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {

            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number= "<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

              $bouton= "<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-arrow-up'></span></a>";
            }  
          }
        }

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);
        
        $action='';
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
        $sub_array[] = $DESC_PAP_ACTIVITE;       
        $sub_array[] = $DESC_TACHE;             
        $sub_array[] = $COMMENTAIRE;
        $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
        $action1 ='<div class="row dropdown" style="color:#fff;">
        "'.$bouton.'"';
        $action =$action1." "."<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a></div>";
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

    //Interface de la liste des engagements juridiques déjà faits
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

    //fonction pour affichage d'une liste des engagemenets juridiques déjà faits
    public function listing_deja_fait()
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

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $critere1="";
      $critere2="";
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $critere1=" AND exec.INSTITUTION_ID IN (".$ID_INST.")";

      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $var_search = addcslashes($var_search,"'");
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'etap.DESC_ETAPE_DOUBLE_COMMANDE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.DATE_ENG_JURIDIQUE DESC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR etap.DESC_ETAPE_DOUBLE_COMMANDE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere2;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.SOUS_TUTEL_ID,exec.ENG_JURIDIQUE,etap.DESC_ETAPE_DOUBLE_COMMANDE,exec.EXECUTION_BUDGETAIRE_ID,exec.DATE_ENG_JURIDIQUE FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID  JOIN execution_budgetaire_etape_double_commande etap ON etap.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID>6 AND det.EXECUTION_BUDGETAIRE_DETAIL_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail_histo histo WHERE histo.ETAPE_DOUBLE_COMMANDE_ID=6 AND histo.USER_ID=".$user_id.")";
      
      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';

      //print_r($query_secondaire);exit();

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

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton= "<a style='color:#fbbf25;' class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";


        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);

        $action='';
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
        $sub_array[] = $DESC_PAP_ACTIVITE;       
        $sub_array[] = $DESC_TACHE;             
        $sub_array[] = $COMMENTAIRE;
        $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
        $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
        $action2 ="<a style='color:#fbbf25;' class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action2;
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

    //Interface de la liste des engagements juridiques à valider
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

    //fonction pour affichage d'une liste des engagemenets juridiques à valider
    public function listing_jur_valider()
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

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED')!=1)
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
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $critere1="";
      $critere2="";
      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else
      {
        $critere1=" AND exec.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $var_search = addcslashes($var_search,"'");
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'etap.DESC_ETAPE_DOUBLE_COMMANDE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.DATE_ENG_JURIDIQUE ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR etap.DESC_ETAPE_DOUBLE_COMMANDE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere2;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT DISTINCT(exec.ENG_BUDGETAIRE),det.EXECUTION_BUDGETAIRE_DETAIL_ID,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.SOUS_TUTEL_ID,exec.ENG_JURIDIQUE,etap.DESC_ETAPE_DOUBLE_COMMANDE,exec.EXECUTION_BUDGETAIRE_ID,exec.DATE_ENG_JURIDIQUE FROM  execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN execution_budgetaire_etape_double_commande etap ON etap.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=7 AND exec.INSTITUTION_ID IN (".$ID_INST.")";

      $requetedebases=$requetedebase." ".$conditions;
      $requetedebasefilter=$requetedebase." ".$conditionsfilter;
      $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';
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

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {

            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number= "<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

              $bouton= "<a class='btn btn-primary btn-sm' title='Traiter' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-arrow-up'></span></a>";
            }  
          }
        }

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);

        $action='';
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
        $sub_array[] = $DESC_PAP_ACTIVITE;       
        $sub_array[] = $DESC_TACHE;             
        $sub_array[] = $COMMENTAIRE;
        $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
        $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
        $action1 ='<div class="row dropdown" style="color:#fff;">
        "'.$bouton.'"';
        $action =$action1." "."<a style='color:#fbbf25;' class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a></div>";
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

    //Interface de la liste des engagements juridiques déjà validés
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

    //fonction pour affichage d'une liste des engagemenets juridiques déjà validés
    public function listing_deja_valide()
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
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $critere1="";
      $critere2="";
      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else{

        $critere1=" AND exec.INSTITUTION_ID IN (".$ID_INST.")";
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $var_search = addcslashes($var_search,"'");
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'etap.DESC_ETAPE_DOUBLE_COMMANDE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.DATE_ENG_JURIDIQUE DESC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR exec.act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR ENG_JURIDIQUE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR etap.DESC_ETAPE_DOUBLE_COMMANDE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere2;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT DISTINCT(exec.ENG_BUDGETAIRE),det.EXECUTION_BUDGETAIRE_DETAIL_ID,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.SOUS_TUTEL_ID,exec.ENG_JURIDIQUE,etap.DESC_ETAPE_DOUBLE_COMMANDE,exec.EXECUTION_BUDGETAIRE_ID,exec.DATE_ENG_JURIDIQUE FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN execution_budgetaire_etape_double_commande etap ON etap.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID >9 AND det.EXECUTION_BUDGETAIRE_DETAIL_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail_histo histo WHERE USER_ID=".$user_id.") AND exec.INSTITUTION_ID IN(".$ID_INST.")";
      
      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';

      //print_r($query_secondaire);exit();

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

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {

            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number= "<a style='color:#fbbf25;' title='' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
              $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";
            }  
          }
        }

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);

        $action='';
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
        $sub_array[] = $DESC_PAP_ACTIVITE;       
        $sub_array[] = $DESC_TACHE;             
        $sub_array[] = $COMMENTAIRE;
        $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
        $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
        $action2 ="<a style='color:#fbbf25;' class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action2;
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

    //Interface de la liste des engagements juridiques à corriger
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

    //fonction pour affichage d'une liste des engagemenets juridiques à corriger
    public function listing_jur_corriger()
    {
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
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $critere1="";
      $critere2="";
     
      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $var_search = addcslashes($var_search,"'");
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'etap.DESC_ETAPE_DOUBLE_COMMANDE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.DATE_ENG_JURIDIQUE ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR ENG_BUDGETAIRE LIKE '%$var_search%' OR ENG_JURIDIQUE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere2;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID),det.EXECUTION_BUDGETAIRE_DETAIL_ID,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.SOUS_TUTEL_ID,exec.ENG_JURIDIQUE,etap.DESC_ETAPE_DOUBLE_COMMANDE,exec.DATE_ENG_JURIDIQUE FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN execution_budgetaire_etape_double_commande etap ON etap.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=8 AND det.EXECUTION_BUDGETAIRE_DETAIL_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail_histo histo WHERE USER_ID=".$user_id.") AND exec.INSTITUTION_ID IN (".$ID_INST.")";
      
      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';

      //print_r($query_secondaire);exit();

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

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton= "<a class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {

            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number= "<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

             $bouton= "<a class='btn btn-primary btn-sm' title='Corriger' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-arrow-up'></span></a>";
            }  
          }
        }

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);

        $action='';
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
        $sub_array[] = $DESC_PAP_ACTIVITE;       
        $sub_array[] = $DESC_TACHE;             
        $sub_array[] = $COMMENTAIRE;
        $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
        $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
        $action1 ='<div class="row dropdown" style="color:#fff;">
        "'.$bouton.'"';
        $action =$action1." "."<a style='color:#fbbf25;' class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a></div>";
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


    //Interface de la liste des engagements juridiques à corriger
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

    //fonction pour affichage d'une liste des engagemenets juridiques à corriger
    public function listing_jur_rejeter()
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
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $critere1="";
      $critere2="";
     
      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere2=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $var_search = addcslashes($var_search,"'");
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'etap.DESC_ETAPE_DOUBLE_COMMANDE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.DATE_ENG_JURIDIQUE ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE  LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE  LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE  LIKE '%$var_search%' OR ENG_JURIDIQUE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere2;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID),det.EXECUTION_BUDGETAIRE_DETAIL_ID,tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,exec.SOUS_TUTEL_ID,exec.ENG_JURIDIQUE,etap.DESC_ETAPE_DOUBLE_COMMANDE,exec.DATE_ENG_JURIDIQUE FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN execution_budgetaire_etape_double_commande etap ON etap.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=9 AND exec.INSTITUTION_ID IN (".$ID_INST.")";
      
      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';

      //print_r($query_secondaire);exit();

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
  
        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton= "<a style='color:#fbbf25;' class='btn btn-primary btn-sm' title='' ><span class='fa fa-arrow-up'></span></a>";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number= "<a style='color:#fbbf25;' title='' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

             $bouton= "<a style='background:#061e69; color:#fbbf25;' class='btn btn-sm' title='Corriger' href='".base_url("double_commande_new/".$step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-arrow-up'></span></a>";
            }  
          }
        }

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_DETAIL_ID . '" data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);
        $ENG_JURIDIQUE=floatval($row->ENG_JURIDIQUE);

        $action='';
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE; 
        $sub_array[] = $DESC_PAP_ACTIVITE;       
        $sub_array[] = $DESC_TACHE;             
        $sub_array[] = $COMMENTAIRE;
        $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
        $sub_array[] = number_format($ENG_JURIDIQUE,2,","," ");
        $action2 ="<a style='color:#fbbf25;' class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action2;
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
      // print_r($db->lastQuery);die();
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