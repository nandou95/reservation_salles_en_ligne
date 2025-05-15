<?php
  /**Alain Charbel Nderagakura
    *Titre: Liste Engagement budgetaire deja fait
    *Numero de telephone: (+257) 62003522
    *WhatsApp: (+257) 76887837
    *Email: charbel@mediabox.bi
    *Date: 7 novembre,2023
    * 
    * Ameliorer par SONIA Munezero
    * +25765165772
    * sonia@mediabox.bi
    * le 22/01/2024
    **/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;

  class Menu_Engagement_Budgetaire extends BaseController
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

    //Interface de la liste des activites sans numero du bon d'engagement
    function get_sans_bon_engagement()
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];

      //selectionner les valeurs a mettre dans le menu en haut
        $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);

      return view('App\Modules\double_commande_new\Views\Eng_Budg_Sans_BE_View',$data);
    }

    function listing_sans_be()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = 2;
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $critere1="";
      $critere2=' AND det.ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_DOUBLE_COMMANDE_ID.' AND exec.USER_ID='.$user_id;
      $critere3="";
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      if(!empty($INSTITUTION_ID))
      { 
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else
      {
        $critere1="  AND exec.INSTITUTION_ID IN(".$ID_INST.")";
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere3="AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,act.DESC_PAP_ACTIVITE,ptba_tache.DESC_TACHE,exec.QTE_RACCROCHE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,det.ETAPE_DOUBLE_COMMANDE_ID  FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1";

      $order_column=array('ligne.CODE_NOMENCLATURE_BUDGETAIRE','act.DESC_PAP_ACTIVITE','exec.QTE_RACCROCHE','exec.COMMENTAIRE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ligne.CODE_NOMENCLATURE_BUDGETAIRE ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.QTE_RACCROCHE LIKE '%$var_search%')"):'';

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
        $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        $bouton="<a class='btn btn-primary btn-sm' title=''><span class='fa fa-arrow-up'></span></a>";

       if (!empty($getProfil))
       {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $bouton= "<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Traiter' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-arrow-up'></span></a>";
            }  
          }
       }

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'"><i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 6) ? (mb_substr($row->COMMENTAIRE, 0, 10) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire'. $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></center></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

        $action='';
        $sub_array = array();
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $DESC_PAP_ACTIVITE; 
        $sub_array[] = $DESC_TACHE; 
        $sub_array[] = $row->QTE_RACCROCHE;
        $sub_array[] = $COMMENTAIRE;
        $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
        $sub_array[] = $bouton;
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

      return $this->response->setJSON($output);//echo json_encode($output);
    }
    
    //Interface de la liste des activites
    function index_deja_fait($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];


      //selectionner les valeurs a mettre dans le menu en haut
        $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);


      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);

      return view('App\Modules\double_commande_new\Views\Eng_Budg_Deja_Fait_View',$data);
    }

    //fonction pour affichage d'une liste des activites
    public function listing_Deja_Fait()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $critere1="";
      $critere2=' AND det.ETAPE_DOUBLE_COMMANDE_ID>1 AND exec.USER_ID='.$user_id;
      $critere3="";
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      if(!empty($INSTITUTION_ID))
      {  
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
      }
      else
      {
        $critere1="  AND exec.INSTITUTION_ID IN(".$ID_INST.")";
      }

      if(!empty($SOUS_TUTEL_ID))
      {
        $critere3="AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,ptba_tache.DESC_TACHE,act.DESC_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, exec.COMMENTAIRE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT  FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1";

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%')"):'';

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
        $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
        $dist="";
        if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
        {
          if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
        }

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        $number=$row->NUMERO_BON_ENGAGEMENT;

        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
            }  
          }
        }

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 6) ? (mb_substr($row->COMMENTAIRE, 0, 6) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

        $action='';
        $sub_array = array();
        $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;//$number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $DESC_PAP_ACTIVITE; 
        $sub_array[] = $DESC_TACHE;               
        $sub_array[] = $COMMENTAIRE;
        $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
        $action2 ="<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action2;
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

      return $this->response->setJSON($output);//echo json_encode($output);
    }

    //Interface de la liste des engagements a valider
    function index_A_Valide($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];

      //selectionner les valeurs a mettre dans le menu en haut
        $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);

      return view('App\Modules\double_commande_new\Views\Eng_Budg_A_Valide_View',$data);
    }

    //fonction pour affichage d'une liste des engagements a valider
    public function listing_a_valide()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      
      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);
      $critere1="";
      $critere2='AND det.ETAPE_DOUBLE_COMMANDE_ID=3 AND exec.INSTITUTION_ID IN('.$ID_INST.')';
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
        $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.ETAPE_DOUBLE_COMMANDE_ID,ptba_tache.DESC_TACHE,exec.EXECUTION_BUDGETAIRE_ID, act.DESC_PAP_ACTIVITE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,exec.NUMERO_BON_ENGAGEMENT  FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1";
      $order_by = '';

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.EXECUTION_BUDGETAIRE_ID ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

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
        $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
        $dist="";
        if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
        {
          if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
        }

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton="<a class='btn btn-primary btn-sm' title=''><span class='fa fa-arrow-up'></span></a>";

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
               $number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

               $bouton= "<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Traiter' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
            }  
          }
        }

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 4) ? (mb_substr($row->COMMENTAIRE, 0, 4). '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

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
        $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
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

      return $this->response->setJSON($output);//echo json_encode($output);
    }
    
    //Interface de la liste des engagements a corriger
    function index_A_Corr()
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      //selectionner les valeurs a mettre dans le menu en haut
        $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);
      
      return view('App\Modules\double_commande_new\Views\Eng_Budg_Corr_View',$data);
    }

    //fonction pour affichage d'une liste des engagements a corriger
    public function listing_A_Corrige()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      $critere1="";
      $critere3="";
      $callpsreq = "CALL getRequete(?,?,?,?);";
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.',';           
      }
      $ID_INST = substr($ID_INST,0,-1);

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
        $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere3;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,det.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,act.DESC_PAP_ACTIVITE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=4";    
      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

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
        $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
        $dist="";
        if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
        {
          if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
        }

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton="<a class='btn btn-info btn-sm' title='Traiter'><span class='fa fa-arrow-up'></span></a>";

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
              $number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

              $bouton= "<a style='color:#fbbf25;' style='background:#061e69;' class='btn btn-info btn-sm' title='Traiter' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
            }  
          }
        }

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 6) ? (mb_substr($row->COMMENTAIRE, 0, 6) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

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
        $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action;
        //$sub_array[] = $action2;
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

      return $this->response->setJSON($output);//echo json_encode($output);
    }

    //Interface de la liste des engagements REJETER
    function rejete_interface()
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE');
      $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED');

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($gdc!=1 AND $ced!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      //selectionner les valeurs a mettre dans le menu en haut
        $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);
      
      return view('App\Modules\double_commande_new\Views\Eng_Budg_Rejet_List_View',$data);
    }

    //fonction pour affichage d'une liste des engagements rejetter
    public function listing_eng_rejette()
    {
      $session  = \Config\Services::session();
      $gdc = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE');
      $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED');

      if($gdc!=1 AND $ced!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      
      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      $critere1="";
      $critere3="";
      $callpsreq = "CALL getRequete(?,?,?,?);";
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.',';           
      }
      $ID_INST = substr($ID_INST,0,-1);

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
        $critere3=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
      }

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%')"):'';

      $critaire = $critere1." ".$critere3;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.NUMERO_BON_ENGAGEMENT,ligne.CODE_NOMENCLATURE_BUDGETAIRE,det.ETAPE_DOUBLE_COMMANDE_ID,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,act.DESC_PAP_ACTIVITE,ptba_tache.DESC_TACHE,exec.COMMENTAIRE,exec.ENG_BUDGETAIRE FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=5";    
      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

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
        $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
        $dist="";
        if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
        {
          if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
        }

        $number=$row->NUMERO_BON_ENGAGEMENT;
        $bouton="<a class='btn btn-info btn-sm' title='Traiter'><span class='fa fa-arrow-up'></span></a>";

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
              $number= "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

              $bouton= "<a style='color:#fbbf25;' style='background:#061e69;' class='btn btn-info btn-sm' title='Traiter' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
            }  
          }
        }
        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 6) ? (mb_substr($row->COMMENTAIRE, 0, 6) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

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
        $action ="<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
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

      return $this->response->setJSON($output);//echo json_encode($output);
    }

    //Interface de la liste des engagements deja valide
    function index_Deja_Valide($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      $callpsreq = "CALL getRequete(?,?,?,?);";
      //selectionner les valeurs a mettre dans le menu en haut
        $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $data['profil_id']=$profil_id;
      //selection les institution de la personne connectee
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $eng_budg=$this->count_engag_budg_new();
      $data['SBE']=$eng_budg['SBE'];
      $data['EBF']=$eng_budg['EBF'];
      $data['EBAV']=$eng_budg['EBAV'];
      $data['EBDV']=$eng_budg['EBDV'];
      $data['EBCorr']=$eng_budg['EBCorr'];
      $data['nbr_eng_rej']=$eng_budg['nbr_eng_rej'];

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);
      
      return view('App\Modules\double_commande_new\Views\Eng_Budg_Deja_Valide_View',$data);
    }

    //fonction pour affichage d'une liste des engagements deja valider
    public function listing_Deja_Valide()
    {
      $session  = \Config\Services::session();
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
      $var_search = str_replace("'", "\'", $var_search);
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.ETAPE_DOUBLE_COMMANDE_ID,exec.EXECUTION_BUDGETAIRE_ID,act.DESC_PAP_ACTIVITE,ptba_tache.DESC_TACHE,exec.ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.COMMENTAIRE,exec.NUMERO_BON_ENGAGEMENT FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID > 5 AND exec.INSTITUTION_ID IN(".$ID_INST.")";

      $order_by = '';
      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,'act.DESC_PAP_ACTIVITE',1,'exec.COMMENTAIRE','exec.ENG_BUDGETAIRE',1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY exec.NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR act.DESC_PAP_ACTIVITE LIKE '%$var_search%' OR exec.COMMENTAIRE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE  LIKE '%$var_search%')"):'';

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
        $step=($EtapeActuel) ? 'double_commande_new/'.$EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
        $dist="";
        if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
        {
          if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
        }

        $number=$row->NUMERO_BON_ENGAGEMENT;

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
              $number= "<a style='color:#fbbf25;' title='Traiter' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";
            }  
          }
        }

        $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

        $DESC_PAP_ACTIVITE = (mb_strlen($row->DESC_PAP_ACTIVITE) > 4) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

        $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE = (mb_strlen($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE) > 4) ? (mb_substr($row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'">&nbsp;<i class="fa fa-eye"></i></a>') : $row->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 6) ? (mb_substr($row->COMMENTAIRE, 0, 6) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire' . $row->EXECUTION_BUDGETAIRE_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>') : $row->COMMENTAIRE;

        $ENG_BUDGETAIRE=floatval($row->ENG_BUDGETAIRE);

        $action='';
        $sub_array = array();
        $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;//$number;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $DESC_PAP_ACTIVITE; 
        $sub_array[] = $DESC_TACHE;               
        $sub_array[] = $COMMENTAIRE;
        $sub_array[] = number_format($ENG_BUDGETAIRE,2,","," ");
        $action2 ="<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action2;
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

      return $this->response->setJSON($output);//echo json_encode($output);
    }

    public function get_soutut()
    {
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $session  = \Config\Services::session();
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $html='<option value="">Sélectionner</option>';
      $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
      if(!empty($INSTITUTION_ID))
      {
        $st = $this->getBindParms('SOUS_TUTEL_ID ,DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','INSTITUTION_ID='.$INSTITUTION_ID,'DESCRIPTION_SOUS_TUTEL ASC');
        $get_st = $this->ModelPs->getRequete($callpsreq, $st);
      }
      
      foreach($get_st as $key)
      {
        $html.= "<option value='".$key->SOUS_TUTEL_ID ."'>".$key->DESCRIPTION_SOUS_TUTEL."</option>";
      }
      $output = array('status' => TRUE ,'html' => $html);
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
  }
?>