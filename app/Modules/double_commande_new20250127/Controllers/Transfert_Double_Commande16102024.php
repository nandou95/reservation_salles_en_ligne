<?php
/**RUGAMBA Jean Vainqueur
*Titre: Transfert 
*Numero de telephone: +257 66 33 43 25
*WhatsApp: +257 62 47 19 15
*Email pro: jean.vainqueur@mediabox.bi
*Date: 24 06 2024
**/

namespace  App\Modules\double_commande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M'); 

class Transfert_Double_Commande extends BaseController
{
  protected $session;

  public function __construct()
  {
    $db = db_connect();
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->validation = \Config\Services::validation();
    $this->session = \Config\Services::session();
  }

  // pour uploader les documents
  public function uploadFile($fieldName, $folder, $prefix = ''): string
  {
    $prefix = ($prefix === '') ? uniqid() : $prefix;
    $path = '';

    $file = $this->request->getFile($fieldName);

    $folderPath = ROOTPATH . 'public/uploads/' . $folder;
    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    if ($file->isValid() && !$file->hasMoved()) {
      $newName = $prefix.'_'.uniqid(). '' . date('ymdhis') . '.' . $file->getExtension();
      $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
      $path = 'uploads/' . $folder . '/' . $newName;
    }
    return $path;
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $IMPORTndparams;
  }

  public function index($value='')
  {
    print_r('ok');die();
  }

  // Recuperation et envoie des données dans le vieu via le id code exec de la table execution_budgetaire
  public function add()
  {
    $data=$this->urichk();
    $session  = \Config\Services::session();
    $psgetrequete = "CALL `getRequete`(?,?,??)";

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if ($USER_ID=='') {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    //get data institution
    $sql_institution='SELECT inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.INSTITUTION_ID FROM inst_institutions JOIN user_affectaion ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID WHERE 1 AND USER_ID ='.$USER_ID.' ';
    $data['institution'] = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");

    $sql_institution_rec='SELECT DESCRIPTION_INSTITUTION,INSTITUTION_ID FROM inst_institutions WHERE 1 ORDER BY DESCRIPTION_INSTITUTION ASC';
    $data['institution_rec'] = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution_rec . "')");
     
    //tranches
    $tranche="SELECT `TRIMESTRE_ID`, `CODE_TRIMESTRE`,CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) as debut,CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y')) as fin FROM `trimestre` WHERE 1 AND date_format(now(),'%m-%d-%Y') BETWEEN CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) AND CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y'))";
    $getTranchee = 'CALL `getTable`("'.$tranche.'");';
    $getTranche = $this->ModelPs->getRequeteOne($getTranchee);

    $gettranches='SELECT DESC_TRIMESTRE,TRIMESTRE_ID  FROM trimestre WHERE 1 AND TRIMESTRE_ID >='.$getTranche['TRIMESTRE_ID'].' ';
    $data['tranches'] = $this->ModelPs->getRequete("CALL `getTable`('" . $gettranches . "')");

    //trimestre de destination

    $gettrim_destination='SELECT DESC_TRIMESTRE,TRIMESTRE_ID  FROM trimestre WHERE 1 AND TRIMESTRE_ID >='.$getTranche['TRIMESTRE_ID'].' AND TRIMESTRE_ID<5';
    $data['trim_destination'] = $this->ModelPs->getRequete("CALL `getTable`('" . $gettrim_destination . "')");

    //Sélectionner les motifs de création 
    $getmotif='SELECT MOTIF_TACHE_ID,DESCR_MOTIF_TACHE FROM motif_creation_tache WHERE 1 ORDER BY DESCR_MOTIF_TACHE ASC ';
    $data['motif'] = $this->ModelPs->getRequete("CALL `getTable`('" . $getmotif . "')");
    return view('App\Modules\double_commande_new\Views\Transfert_Double_Commande_Add_View',$data);
  }

  
  // trouver le sous titre a partir de institution choisit
  function get_sousTutel($INSTITUTION_ID=0)
  {
    $db = db_connect();
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

    $getSousTutel  = 'SELECT SOUS_TUTEL_ID,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE INSTITUTION_ID = '.$INSTITUTION_ID.' ORDER BY DESCRIPTION_SOUS_TUTEL  ASC';
    $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
    $sousTutel = $this->ModelPs->getRequete($getSousTutel);

    $html='<option value="">Sélectionner</option>';
    foreach ($sousTutel as $key)
    {
      $html.='<option value="'.$key->SOUS_TUTEL_ID.'">'.$key->CODE_SOUS_TUTEL.'-'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
    }

    $output = array(

      "SousTutel" => $html,
    );

    return $this->response->setJSON($output);
  }

  // trouver le sous titre a partir de institution choisit
  function get_inst($INSTITUTION_ID=0)
  {
    $db = db_connect();
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

    $getInst  = 'SELECT INSTITUTION_ID,DESCRIPTION_INSTITUTION, TYPE_INSTITUTION_ID FROM inst_institutions WHERE INSTITUTION_ID = '.$INSTITUTION_ID.'';
    $getInst = "CALL `getTable`('" . $getInst . "');";
    $institution = $this->ModelPs->getRequeteOne($getInst);

    $output = array(

      "inst_activite" => $institution['TYPE_INSTITUTION_ID'],
    );

    return $this->response->setJSON($output);
  }

  // trouver le code  a partir de sous titre choisit
  function get_code($SOUS_TUTEL_ID=0)
  {
    $db = db_connect();
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

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $getcodeBudget = "SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE INSTITUTION_ID = ".$INSTITUTION_ID." AND SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    $getcodeBudget = 'CALL `getTable`("'.$getcodeBudget.'");';
    $code_Buget= $this->ModelPs->getRequete($getcodeBudget);

    $html='<option value="">Sélectionner</option>';
    foreach ($code_Buget as $key)
    {
      $html.='<option value="'.$key->CODE_NOMENCLATURE_BUDGETAIRE_ID.'">'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'-'.$key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'</option>';
    }

    $output = array(

      "codeBudgetaire" => $html,
    );

    return $this->response->setJSON($output);
  }

  // trouver les activites a partir du line budgetaire
  function get_activite1($CODE_NOMENCLATURE_BUDGETAIRE_ID=0)
  {
    
    
    $db = db_connect();
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

    $getActivite = 'SELECT PAP_ACTIVITE_ID, DESC_PAP_ACTIVITE FROM pap_activites WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID.' ORDER BY PAP_ACTIVITE_ID  ASC';
    $getActivite = "CALL `getTable`('" . $getActivite . "');";
    $activites = $this->ModelPs->getRequete($getActivite);

    $html='<option value="">Sélectionner</option>';
    foreach ($activites as $key)
    {
      $html.='<option value="'.$key->PAP_ACTIVITE_ID.'">'.$key->DESC_PAP_ACTIVITE.'</option>';
    }

    $output = array(

      "activite" => $html,
    );

    return $this->response->setJSON($output);
  }

  // trouver le activite  a partir de code choisit
  function get_taches($id = 0,$TYPE_INSTITUTION_ID=0)
  {
    $db = db_connect();
    $session = \Config\Services::session();
    $user_id = '';

    if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    } else {
      return redirect('Login_Ptba/do_logout');
    }


    if ($TYPE_INSTITUTION_ID == 2)
    {
      $getTacheactivite = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PAP_ACTIVITE_ID = {$id} ORDER BY PTBA_TACHE_ID ASC";
    } 
    else if ($TYPE_INSTITUTION_ID == 1)
    {
      $getTacheactivite = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID = {$id} ORDER BY PTBA_TACHE_ID ASC";
    }

    $getTacheactivite = "CALL `getList`('{$getTacheactivite}')";
    $tache_activites = $this->ModelPs->getRequete($getTacheactivite);
    $html = '<option value="">Sélectionner</option>';
    foreach ($tache_activites as $key)
    {
      $html .= '<option value="' . $key->PTBA_TACHE_ID . '">' . $key->DESC_TACHE . '</option>';
    }

    $output = array(
        "tache_activite" => $html,
      );
      return $this->response->setJSON($output);
  }

  

  //recuperation de montany by trimestre
  public function getMontantAnnuel($value='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
    $TRIMESTRE_ID = $this->request->getPost('TRIMESTRE_ID');

    $bind_proc = $this->getBindParms('BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4,BUDGET_ANNUEL', 'ptba_tache', 'PTBA_TACHE_ID ='.$PTBA_TACHE_ID,'PTBA_TACHE_ID  ASC');
    $montant_info= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);

    if ($TRIMESTRE_ID==5)
    {
      //tranches
      $tranche="SELECT `TRIMESTRE_ID`, `CODE_TRIMESTRE`,CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) as debut,CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y')) as fin FROM `trimestre` WHERE 1 AND date_format(now(),'%m-%d-%Y') BETWEEN CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) AND CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y'))";
      $getTranchee = 'CALL `getTable`("'.$tranche.'");';
      $getTranche = $this->ModelPs->getRequeteOne($getTranchee);

      $gettranches = $this->getBindParms('DESC_TRIMESTRE,TRIMESTRE_ID ', 'trimestre', 'TRIMESTRE_ID>='.$getTranche['TRIMESTRE_ID'].' AND `TRIMESTRE_ID`!=5 ', 'TRIMESTRE_ID ASC');
      $data['tranches'] = $this->ModelPs->getRequete($callpsreq, $gettranches);
      $dataCount = count($data['tranches']);

      if ($dataCount==1)
      {
        $MONTANT_VOTE = $montant_info['BUDGET_T4'];
        $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T4'];
      }
      else if ($dataCount==2)
      {
        $MONTANT_VOTE = $montant_info['BUDGET_T3']+$montant_info['BUDGET_T4'];
        $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T3']+$montant_info['BUDGET_RESTANT_T4'];      
      }
      else if ($dataCount==3)
      {
        $MONTANT_VOTE = $montant_info['BUDGET_T2']+$montant_info['BUDGET_T3']+$montant_info['BUDGET_T4'];
        $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T2']+$montant_info['BUDGET_RESTANT_T3']+$montant_info['BUDGET_RESTANT_T4'];
      }
      else if ($dataCount==4)
      {
        $MONTANT_VOTE = $montant_info['BUDGET_T1']+$montant_info['BUDGET_T2']+$montant_info['BUDGET_T3']+$montant_info['BUDGET_T3'];
        $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T1']+$montant_info['BUDGET_RESTANT_T2']+$montant_info['BUDGET_RESTANT_T3']+$montant_info['BUDGET_RESTANT_T4'];
      }
    }else{

      if ($TRIMESTRE_ID==1)
      {
        $MONTANT_VOTE = $montant_info['BUDGET_T1'];
        $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T1'];
      }
      else if ($TRIMESTRE_ID==2)
      {
        $MONTANT_VOTE = $montant_info['BUDGET_T2'];
        $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T2'];
      }
      else if ($TRIMESTRE_ID==3)
      {
        $MONTANT_VOTE = $montant_info['BUDGET_T3'];
        $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T3'];
      }
      else if ($TRIMESTRE_ID==4)
      {
        $MONTANT_VOTE = $montant_info['BUDGET_T4'];
        $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T4'];
      }
    }

    $output = array(
        "MONTANT_VOTE" => $MONTANT_VOTE,
        "MONTANT_RESTANT" => $MONTANT_RESTANT
      );
    return $this->response->setJSON($output);
  }

  // trouver le sous titre a partir de institution choisit 2
  function get_sousTutel2($INSTITUTION_ID2=0)
  {
    $db = db_connect();
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

    $getSousTutel  = 'SELECT SOUS_TUTEL_ID,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE INSTITUTION_ID = '.$INSTITUTION_ID2.' ORDER BY DESCRIPTION_SOUS_TUTEL  ASC';
    $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
    $sousTutel = $this->ModelPs->getRequete($getSousTutel);

    $html='<option value="">Sélectionner</option>';
    foreach ($sousTutel as $key)
    {
      $html.='<option value="'.$key->SOUS_TUTEL_ID.'">'.$key->CODE_SOUS_TUTEL.'-'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
    }

    $output = array(

      "SousTutel" => $html,
    );

    return $this->response->setJSON($output);
  }

  // trouver le sous titre a partir de institution choisit
  function get_inst2($INSTITUTION_ID2=0)
  {
    $db = db_connect();
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

    $getInst  = 'SELECT INSTITUTION_ID,DESCRIPTION_INSTITUTION, TYPE_INSTITUTION_ID FROM inst_institutions WHERE INSTITUTION_ID = '.$INSTITUTION_ID2.'';
    $getInst = "CALL `getTable`('" . $getInst . "');";
    $institution = $this->ModelPs->getRequeteOne($getInst);

    $output = array(

      "inst_activite" => $institution['TYPE_INSTITUTION_ID'],
    );

    return $this->response->setJSON($output);
  }

  // trouver le code  a partir de sous titre choisit
  function get_code2($SOUS_TUTEL_ID2=0)
  {
    $db = db_connect();
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

    $INSTITUTION_ID2 = $this->request->getPost('INSTITUTION_ID2');
    $getcodeBudget = "SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE INSTITUTION_ID = ".$INSTITUTION_ID2." AND SOUS_TUTEL_ID=".$SOUS_TUTEL_ID2;
    $getcodeBudget = 'CALL `getTable`("'.$getcodeBudget.'");';
    $code_Buget= $this->ModelPs->getRequete($getcodeBudget);

    $html='<option value="">Sélectionner</option>';
    foreach ($code_Buget as $key)
    {
      $html.='<option value="'.$key->CODE_NOMENCLATURE_BUDGETAIRE_ID.'">'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'-'.$key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'</option>';
    }

    $output = array(

      "codeBudgetaire" => $html,
    );

    return $this->response->setJSON($output);
  }

  // trouver les activites a partir du line budgetaire
  function get_activite2($CODE_NOMENCLATURE_BUDGETAIRE_ID2=0)
  {
    $db = db_connect();
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

    $getActivite = 'SELECT PAP_ACTIVITE_ID, DESC_PAP_ACTIVITE FROM pap_activites WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID2.' ORDER BY PAP_ACTIVITE_ID  ASC';
    $getActivite = "CALL `getTable`('" . $getActivite . "');";
    $activites = $this->ModelPs->getRequete($getActivite);

    $html='<option value="">Sélectionner</option>';
    foreach ($activites as $key)
    {
      $html.='<option value="'.$key->PAP_ACTIVITE_ID.'">'.$key->DESC_PAP_ACTIVITE.'</option>';
    }

    $output = array(

      "activite" => $html,
    );

    return $this->response->setJSON($output);
  }

  // trouver le activite  a partir de code choisit
  function get_taches2($id = 0,$TYPE_INSTITUTION_ID=0)
  {
    $db = db_connect();
    $session = \Config\Services::session();
    $user_id = '';

    if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    } else {
      return redirect('Login_Ptba/do_logout');
    }


    if ($TYPE_INSTITUTION_ID == 2)
    {
      $getTacheactivite = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PAP_ACTIVITE_ID = {$id} ORDER BY PTBA_TACHE_ID ASC";
    } 
    else if ($TYPE_INSTITUTION_ID == 1)
    {
      $getTacheactivite = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID = {$id} ORDER BY PTBA_TACHE_ID ASC";
    }

    $getTacheactivite = "CALL `getList`('{$getTacheactivite}')";
    $tache_activites = $this->ModelPs->getRequete($getTacheactivite);
    $html = '<option value="">Sélectionner</option>';
    foreach ($tache_activites as $key)
    {
      $html .= '<option value="' . $key->PTBA_TACHE_ID . '">' . $key->DESC_TACHE . '</option>';
    }

    $output = array(
        "tache_activite" => $html,
      );
      return $this->response->setJSON($output);
  }

  ///recuparation du montant voté a partir d'une activite de destination
  function get_MontantVoteByActivite()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $PTBA_TACHE_ID2 = $this->request->getPost('PTBA_TACHE_ID2');
    $TRIMESTRE_ID_DESTINATION = $this->request->getPost('TRIMESTRE_ID_DESTINATION');

    $bind_proc = $this->getBindParms('BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4,BUDGET_ANNUEL', 'ptba_tache', 'PTBA_TACHE_ID ='.$PTBA_TACHE_ID2,'PTBA_TACHE_ID  ASC');
    $montant_info= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);

    if($TRIMESTRE_ID_DESTINATION == 1)
    {
      $MONTANT_VOTE = $montant_info['BUDGET_T1'];
      $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T1'];
    }
    elseif($TRIMESTRE_ID_DESTINATION == 2)
    {
      $MONTANT_VOTE = $montant_info['BUDGET_T2'];
      $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T2'];
    }
    elseif($TRIMESTRE_ID_DESTINATION == 3)
    {
      $MONTANT_VOTE = $montant_info['BUDGET_T3'];
      $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T3'];
    }
    elseif($TRIMESTRE_ID_DESTINATION == 4)
    {
      $MONTANT_VOTE = $montant_info['BUDGET_T4'];
      $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T4'];
    }



    $output = array(
      'TACHE_ID' => $PTBA_TACHE_ID2,
      'TRIMESTRE_ID' => $TRIMESTRE_ID_DESTINATION,
      "MONTANT_VOTE" => $MONTANT_VOTE,
      "MONTANT_RESTANT" => $MONTANT_RESTANT,
    );
    return $this->response->setJSON($output);
  }

  public function liste_tempo($value='')
  {
    $data=$this->urichk();
    $session  = \Config\Services::session();

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if ($USER_ID=='') {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    ########### get data  transfert_tempo_transfert_double_commande #############
    $sql='SELECT PTBA_TACHE_ID_TRANSFERT,TRIMESTRE_ID,TRIMESTRE_ID_DESTINATION,MONTANT_RESTANT_TRANSFERT,MONTANT_TRANSFERT,PTBA_TACHE_ID_RECEVOIR,MONTANT_RESTANT_RECEVOIR,MONTANT_RECEVOIR,USER_ID,TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID,MOTIF_TACHE_ID,NOM,PRENOM,POSTE,AUTORISATION_TRANSFERT FROM  transfert_tempo_transfert_double_commande WHERE 1 AND USER_ID='.$USER_ID.' ORDER BY TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID  DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $sql . "')");
    $count_data = count($tempo);

    $html = '';
    $html.='<br>
      <table class="table table-bordered">
      <thead>
      <tr>
      <th class="text-uppercase" style="white-space: nowrap;" >Tache&nbsp;&nbsp;d\'origine</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Trimestre d\'origine</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Trimestre de déstination</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Montant&nbsp;&nbsp;restant&nbsp;&nbsp;d\'origine</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Montant&nbsp;&nbsp;Transferer</th>

      <th class="text-uppercase" style="white-space: nowrap;" >Tache&nbsp;&nbsp;destination</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Montant&nbsp;&nbsp;restant&nbsp;&nbsp;destination</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Montant&nbsp;&nbsp;reception</th>

      <th class="text-uppercase" style="white-space: nowrap;" >Motif</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Nom</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Prénom</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Poste</th>
      <th class="text-uppercase" style="white-space: nowrap;" >Autorisation&nbsp;&nbsp;de&nbsp;&nbsp;transfert</th>

      <th class="text-uppercase" style="white-space: nowrap;" >Option</th>
      </tr>
      </thead>
      <body>';

    if ($count_data==0) {
      $status = 0;
    }else{
      $status = 1;
      
      foreach ($tempo as $key)
      {
        //Tache
       

        //activite
        $tache = $this->getBindParms('DESC_TACHE,PTBA_TACHE_ID', 'ptba_tache', 'PTBA_TACHE_ID = '.$key->PTBA_TACHE_ID_TRANSFERT .' ', 'DESC_TACHE ASC');
        $data_tache = $this->ModelPs->getRequeteOne($psgetrequete, $tache);

        //tranches d'origine
        $gettranches = $this->getBindParms('DESC_TRIMESTRE,CODE_TRIMESTRE,TRIMESTRE_ID', 'trimestre', 'TRIMESTRE_ID = '.$key->TRIMESTRE_ID.'', 'TRIMESTRE_ID ASC');
        $tranches = $this->ModelPs->getRequeteOne($psgetrequete, $gettranches);

        //tranches de déstination
        $gettrim_destin = $this->getBindParms('DESC_TRIMESTRE,CODE_TRIMESTRE,TRIMESTRE_ID', 'trimestre', 'TRIMESTRE_ID = '.$key->TRIMESTRE_ID_DESTINATION.'', 'TRIMESTRE_ID ASC');
        $trim_destin = $this->ModelPs->getRequeteOne($psgetrequete, $gettrim_destin);
        

        //taches
        $tache2 = $this->getBindParms('DESC_TACHE,PTBA_TACHE_ID', 'ptba_tache', 'PTBA_TACHE_ID = '.$key->PTBA_TACHE_ID_RECEVOIR .' ', 'DESC_TACHE  ASC');
        $data_tache2 = $this->ModelPs->getRequeteOne($psgetrequete, $tache2);

        //motif
        $DESCR_MOTIF_TACHE  = '';
        if ($key->MOTIF_TACHE_ID>0) {
          $motif = $this->getBindParms('MOTIF_TACHE_ID,DESCR_MOTIF_TACHE', 'motif_creation_tache', 'MOTIF_TACHE_ID='.$key->MOTIF_TACHE_ID.' ', 'MOTIF_TACHE_ID  ASC');
          $data_motif = $this->ModelPs->getRequeteOne($psgetrequete, $motif);
          $DESCR_MOTIF_TACHE = $data_motif['DESCR_MOTIF_TACHE'];
        }

        $MONTANT_RESTANT_TRANSFERT = floatval($key->MONTANT_RESTANT_TRANSFERT);
        $MONTANT_TRANSFERT = floatval($key->MONTANT_TRANSFERT);
        $MONTANT_RESTANT_RECEVOIR = floatval($key->MONTANT_RESTANT_RECEVOIR);
        $MONTANT_RECEVOIR = floatval($key->MONTANT_RECEVOIR);

        $NOM = !empty($key->NOM) ? $key->NOM : "Pas disponible";
        $PRENOM = !empty($key->PRENOM) ? $key->PRENOM : "Pas disponible";
        $POSTE = !empty($key->POSTE) ? $key->POSTE : "Pas disponible";


        $html.='<tr>  
                <td>'.$data_tache['DESC_TACHE'].'</td>
                <td>'.$tranches['DESC_TRIMESTRE'].'</td>
                <td>'.$trim_destin['DESC_TRIMESTRE'].'</td>
                <td>'.number_format($MONTANT_RESTANT_TRANSFERT,2,","," ").'</td>
                <td>'.number_format($MONTANT_TRANSFERT,2,","," ").'</td>

                <td>'.$data_tache2['DESC_TACHE'].'</td>
                <td>'.number_format($MONTANT_RESTANT_RECEVOIR,2,","," ").'</td>
                <td>'.number_format($MONTANT_RECEVOIR,2,","," ").'</td>

                <td>'.$DESCR_MOTIF_TACHE.'</td>
                <td>'.$NOM.'</td>
                <td>'.$PRENOM.'</td>
                <td>'.$POSTE.'</td>
                <td><a href="'.base_url($key->AUTORISATION_TRANSFERT).'" target="_blank"><span class="fa fa-file-pdf" style="color:green;font-size: 200%;"></span></a></td>

                <td>
                <a onclick="removeToCart('.$key->TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID.')" href="javascript:;" style="color: red"><i class="fa fa-trash"></i> <span id="loading_delete"></span> <span id="message'.$key->TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID.'"></span></a> 
                </td>
                </tr>';
      }
    }
    $html.='</body>
    </table>';

    $output = array(
      'status'=>$status,
      'html'=>$html
    );
    return $this->response->setJSON($output);
  }

  public function addToCart()
  {
    $session  = \Config\Services::session();

    // $cart = \Config\Services::cart();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $db = db_connect();  

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if ($USER_ID=='') {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

      //Form validation
      $rules = [
        'INSTITUTION_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'INSTITUTION_ID2' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'SOUS_TUTEL_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'SOUS_TUTEL_ID2' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'CODE_NOMENCLATURE_BUDGETAIRE_ID2' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'PTBA_TACHE_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'PTBA_TACHE_ID2' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'TRIMESTRE_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],
        'TRIMESTRE_ID_DESTINATION' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]

      ];

      $this->validation->setRules($rules);
      if(!$this->validation->withRequest($this->request)->run())
      {
        $errors = []; 
        foreach ($rules as $field => $rule) {
          $error = $this->validation->getError($field);
          if ($error !== null) {
              $errors[$field] = $error;
          }
        }

        $valeur = 1;
        $response = [
            'status' => false,
            'msg' => $errors,
            'valeur' => $valeur
        ];

        return $this->response->setJSON($response);
      }

    //ligne qui envoie
    $PTBA_TACHE_ID_TRANSFERT=$this->request->getPost('PTBA_TACHE_ID');
    $TRIMESTRE_ID=$this->request->getPost('TRIMESTRE_ID');
    $MONTANT_VOTE_TRANSFERT=preg_replace('/\s/', '', $this->request->getPost('MONTANT_VOTE'));
    $MONTANT_RESTANT_TRANSFERT=preg_replace('/\s/', '', $this->request->getPost('MONTANT_RESTANT'));
    $MONTANT_TRANSFERT=preg_replace('/\s/', '', $this->request->getPost('MONTANT_TRANSFERT'));

    $PTBA_TACHE_ID_RECEVOIR=$this->request->getPost('PTBA_TACHE_ID2');
    $TRIMESTRE_ID_DESTINATION=$this->request->getPost('TRIMESTRE_ID_DESTINATION');
    $MONTANT_VOTE_RECEVOIR=preg_replace('/\s/', '', $this->request->getPost('MONTANT_VOTE2'));
    $MONTANT_RESTANT_RECEVOIR=preg_replace('/\s/', '', $this->request->getPost('MONTANT_RESTANT2'));
    $MONTANT_RECEVOIR=preg_replace('/\s/', '', $this->request->getPost('MONTANT_RECEVOIR'));

    $MOTIF_TACHE_ID=$this->request->getPost('MOTIF_TACHE_ID');
    $NOM=$this->request->getPost('NOM');
    $PRENOM=$this->request->getPost('PRENOM');
    $POSTE=$this->request->getPost('POSTE');

    if (!empty($MOTIF_TACHE_ID)) {
      $MOTIF_TACHE_ID = $MOTIF_TACHE_ID;
    }else{
      $MOTIF_TACHE_ID = 0;
    }

    ####################### upload file autorisation de transfert ##############################
    $AUTORISATION = $this->request->getPost('AUTORISATION_TRANSFERT');
    $AUTORISATION_TRANSFERT=$this->uploadFile('AUTORISATION_TRANSFERT','double_commande',$AUTORISATION);

    $insertIntoTable='transfert_tempo_transfert_double_commande';
    $columsinsert="PTBA_TACHE_ID_TRANSFERT,TRIMESTRE_ID,TRIMESTRE_ID_DESTINATION,MONTANT_RESTANT_TRANSFERT,MONTANT_TRANSFERT,PTBA_TACHE_ID_RECEVOIR,MONTANT_RESTANT_RECEVOIR,MONTANT_RECEVOIR,USER_ID,MOTIF_TACHE_ID,NOM,PRENOM,POSTE,AUTORISATION_TRANSFERT";
    $datacolumsinsert=$PTBA_TACHE_ID_TRANSFERT.",".$TRIMESTRE_ID.",".$TRIMESTRE_ID_DESTINATION.",".$MONTANT_RESTANT_TRANSFERT.",".$MONTANT_TRANSFERT.",".$PTBA_TACHE_ID_RECEVOIR.",".$MONTANT_RESTANT_RECEVOIR.",".$MONTANT_RECEVOIR.",".$USER_ID.",".$MOTIF_TACHE_ID.",'".$NOM."','".$PRENOM."','".$POSTE."','".$AUTORISATION_TRANSFERT."'";
    $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

    $output = array('status'=>true);
    return $this->response->setJSON($output);
  }

  function removeToCart()
  {
    $db = db_connect();  
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID=$this->request->getPost('TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID');

    $delete_file_folder = 'SELECT AUTORISATION_TRANSFERT FROM  transfert_tempo_transfert_double_commande WHERE 1 AND TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID='.$TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID.' ORDER BY TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID  DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $delete_file_folder . "')");

    foreach ($tempo as $item)
    { 
      $AUTORISATION_TRANSFERT = $item->AUTORISATION_TRANSFERT;
    }
    unlink($AUTORISATION_TRANSFERT);

    $insertIntoTable='transfert_tempo_transfert_double_commande';
    $critere ="TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID=".$TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID;
    $deleteparams =[$db->escapeString($insertIntoTable),$db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";
    $this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);

    $output = array('status'=>true);
    return $this->response->setJSON($output);
  }

  /* Debut Gestion update table de la demande detail*/
  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
  /* Fin Gestion update table de la demande detail*/

   /* Debut Gestion insertion */
  public function save_all_table($table,$columsinsert,$datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams =[$table,$columsinsert,$datacolumsinsert];
    $result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
    return $id=$result['id'];
  }

  // Enregistrement des données dans la table transfert_historique_transfert
  public function send_data($value='')
  {
    $session  = \Config\Services::session();
    $data=$this->urichk();
    $db = db_connect(); 
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    $TYPE_OPERATION_ID = 3;

    if ($USER_ID=='')
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $sql='SELECT PTBA_TACHE_ID_TRANSFERT,TRIMESTRE_ID,TRIMESTRE_ID_DESTINATION,MONTANT_RESTANT_TRANSFERT,MONTANT_TRANSFERT,PTBA_TACHE_ID_RECEVOIR,MONTANT_RESTANT_RECEVOIR,MONTANT_RECEVOIR,USER_ID,TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID,MOTIF_TACHE_ID,NOM,PRENOM,POSTE,AUTORISATION_TRANSFERT FROM  transfert_tempo_transfert_double_commande WHERE 1 AND USER_ID='.$USER_ID.' ORDER BY TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID  DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $sql . "')");

    $insertIntoTable='transfert_historique_transfert';

    foreach ($tempo as $key => $value)
    {
      $columsinsert="TYPE_OPERATION_ID,USER_ID,MONTANT_TRANSFERT,PTBA_TACHE_ID_TRANSFERT,MONTANT_RECEPTION,PTBA_TACHE_ID_RECEPTION,TRIMESTRE_ID,TRIMESTRE_ID_DESTINATION,AUTORISATION_TRANSFERT";
      $datacolumsinsert=$TYPE_OPERATION_ID.",".$USER_ID.",".$value->MONTANT_TRANSFERT.",".$value->PTBA_TACHE_ID_TRANSFERT.",".$value->MONTANT_RECEVOIR.",".$value->PTBA_TACHE_ID_RECEVOIR.",".$value->TRIMESTRE_ID.",".$value->TRIMESTRE_ID_DESTINATION.",'".$value->AUTORISATION_TRANSFERT."'";
      $HISTORIQUE_TRANSFERT_ID  = $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);
      #######################################################

      ######### Avez-vous l'autorisation du Ministre des finances ? insetion motif  ##################
      //Table associative

      if($value->MOTIF_TACHE_ID>0)
      {
        $table_trans='transfert_motif_activite';

        if($value->MOTIF_TACHE_ID == 2 || $value->MOTIF_TACHE_ID == 3)
        {
          $NOM = $this->request->getPost('NOM');
          $PRENOM = $this->request->getPost('PRENOM');
          $POSTE = $this->request->getPost('POSTE');

          $columsinsert1="HISTORIQUE_TRANSFERT_ID,MOTIF_TACHE_ID,USER_ID,NOM,PRENOM,POSTE";
          $datatoinsert1="".$HISTORIQUE_TRANSFERT_ID.",".$value->MOTIF_TACHE_ID.",".$USER_ID.",'".$value->NOM."','".$value->PRENOM."','".$value->POSTE."'";
          $this->save_all_table($table_trans,$columsinsert1,$datatoinsert1);
        }
        else
        {
          $columsinsert2="HISTORIQUE_TRANSFERT_ID,MOTIF_TACHE_ID,USER_ID";
          $datatoinsert2="".$HISTORIQUE_TRANSFERT_ID.",".$value->MOTIF_TACHE_ID.",".$USER_ID."";
          $this->save_all_table($table_trans,$columsinsert2,$datatoinsert2);
        }
      }

      // get transfert d'origine
      $tache = $this->getBindParms('BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4','ptba_tache','PTBA_TACHE_ID ='.$value->PTBA_TACHE_ID_TRANSFERT.'','PTBA_TACHE_ID ASC');
      $data_tache = $this->ModelPs->getRequeteOne($psgetrequete, $tache);

      //get transfert de destinataire
      $tache2 = $this->getBindParms('BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4','ptba_tache','PTBA_TACHE_ID ='.$value->PTBA_TACHE_ID_RECEVOIR.'','PTBA_TACHE_ID ASC');
      $data_tache2 = $this->ModelPs->getRequeteOne($psgetrequete, $tache2);

      //mise a jour dans la table ptba_tache
      $table = 'ptba_tache';
      $where='PTBA_TACHE_ID='.$value->PTBA_TACHE_ID_TRANSFERT.'';
      $where2='PTBA_TACHE_ID='.$value->PTBA_TACHE_ID_RECEVOIR.'';

      if($value->TRIMESTRE_ID==1)
      {
        //cas du transfert
        $MONTANT_RESTANT_T1 = $data_tache['BUDGET_RESTANT_T1']-$value->MONTANT_TRANSFERT;
        $data='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_T1.'';
        $this->update_all_table($table,$data,$where);

        //cas receptrice
        if($value->TRIMESTRE_ID_DESTINATION==1)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T1']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==2)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T2']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==3)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T3']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==4)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T4']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
  
        $this->update_all_table($table,$data2,$where2);
      }
      else if ($value->TRIMESTRE_ID==2)
      {
        //cas du transfert
        $MONTANT_RESTANT_T2 = $data_tache['BUDGET_RESTANT_T2']-$value->MONTANT_TRANSFERT;
        $data='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_T2.'';
        $this->update_all_table($table,$data,$where);

        //cas receptrice
        if($value->TRIMESTRE_ID_DESTINATION==1)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T1']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==2)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T2']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==3)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T3']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==4)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T4']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }

        $this->update_all_table($table,$data2,$where2);
      }
      else if ($value->TRIMESTRE_ID==3)
      {
        //cas du transfert
        $MONTANT_RESTANT_T3 = $data_tache['BUDGET_RESTANT_T3']-$value->MONTANT_TRANSFERT;
        $data='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_T3.'';
        $this->update_all_table($table,$data,$where);

        //cas receptrice
        if($value->TRIMESTRE_ID_DESTINATION==1)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T1']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==2)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T2']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==3)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T3']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==4)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T4']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        $this->update_all_table($table,$data2,$where2);
      }
      else if ($value->TRIMESTRE_ID==4)
      {
        //cas du transfert
        $MONTANT_RESTANT_T4 = $data_tache['BUDGET_RESTANT_T4']-$value->MONTANT_TRANSFERT;
        $data='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_T4.'';
        $this->update_all_table($table,$data,$where);

        //cas receptrice
        if($value->TRIMESTRE_ID_DESTINATION==1)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T1']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==2)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T2']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==3)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T3']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        elseif($value->TRIMESTRE_ID_DESTINATION==4)
        {
          $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T4']+$value->MONTANT_RECEVOIR;
          $data2='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_RECEVOIR.'';
        }
        $this->update_all_table($table,$data2,$where2);
      }
      else if ($value->TRIMESTRE_ID==5)
      {
        //tranches
        $tranche="SELECT `TRIMESTRE_ID`, `CODE_TRIMESTRE`,CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) as debut,CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y')) as fin FROM `trimestre` WHERE 1 AND date_format(now(),'%m-%d-%Y') BETWEEN CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) AND CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y'))";
        $getTranchee = 'CALL `getTable`("'.$tranche.'");';
        $getTranche = $this->ModelPs->getRequeteOne($getTranchee);

        $gettranches = $this->getBindParms('DESC_TRIMESTRE,TRIMESTRE_ID ', 'trimestre', 'TRIMESTRE_ID>='.$getTranche['TRIMESTRE_ID'].' AND `TRIMESTRE_ID`!=5 ', 'TRIMESTRE_ID ASC');
        $data['tranches'] = $this->ModelPs->getRequete($callpsreq, $gettranches);
        $dataCount = count($data['tranches']);

        if ($dataCount==1)
        {
          //cas du transfert
          $MONTANT_RESTANT_T4 = 0;
          $data='BUDGET_RESTANT_T4='.$MONTANT_RESTANT_T4.'';
          $this->update_all_table($table,$data,$where);

          //cas receptrice
          if($value->TRIMESTRE_ID_DESTINATION==1)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T1']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==2)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T2']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==3)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T3']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==4)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T4']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          $this->update_all_table($table,$data2,$where2);
        }
        else if ($dataCount==2)
        {
          //cas du transfert
          $MONTANT_RESTANT_T3 = 0; 
          $MONTANT_RESTANT_T4 = 0;
          $data='BUDGET_RESTANT_T3='.$MONTANT_RESTANT_T3.', BUDGET_RESTANT_T4='.$MONTANT_RESTANT_T4.'';
          $this->update_all_table($table,$data,$where);

          //cas receptrice
          if($value->TRIMESTRE_ID_DESTINATION==1)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T1']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==2)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T2']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==3)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T3']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==4)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T4']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          $this->update_all_table($table,$data2,$where2);
        }
        else if ($dataCount==3)
        {
          //cas du transfert
          $MONTANT_RESTANT_T2 = 0;
          $MONTANT_RESTANT_T3 = 0;
          $MONTANT_RESTANT_T4 = 0;

          $data='BUDGET_RESTANT_T2='.$MONTANT_RESTANT_T2.', BUDGET_RESTANT_T3='.$MONTANT_RESTANT_T3.', BUDGET_RESTANT_T4='.$MONTANT_RESTANT_T4.'';
          $this->update_all_table($table,$data,$where);

          //cas receptrice
          if($value->TRIMESTRE_ID_DESTINATION==1)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T1']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==2)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T2']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==3)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T3']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==4)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T4']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }

          $this->update_all_table($table,$data2,$where2);
        }
        else if ($dataCount==4)
        {
          //cas du transfert
          $MONTANT_RESTANT_T1 = 0;
          $MONTANT_RESTANT_T2 = 0;
          $MONTANT_RESTANT_T3 = 0;
          $MONTANT_RESTANT_T4 = 0;

          $data='BUDGET_RESTANT_T1='.$MONTANT_RESTANT_T1.', BUDGET_RESTANT_T2='.$MONTANT_RESTANT_T2.', BUDGET_RESTANT_T3='.$MONTANT_RESTANT_T3.', BUDGET_RESTANT_T4='.$MONTANT_RESTANT_T4.'';
          $this->update_all_table($table,$data,$where);

          //cas receptrice
          if($value->TRIMESTRE_ID_DESTINATION==1)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T1']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==2)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T2']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==3)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T3']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          elseif($value->TRIMESTRE_ID_DESTINATION==4)
          {
            $MONTANT_RESTANT_RECEVOIR = $data_tache2['BUDGET_RESTANT_T4']+$value->MONTANT_RECEVOIR;
            $data2='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_RECEVOIR.'';
          }
          $this->update_all_table($table,$data2,$where2);
        }
      }

      ############################# delete from table tempo after insert data in the table histo
      $insertIntoTable2 = 'transfert_tempo_transfert_double_commande';
      $critere2 ="TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID=".$value->TEMPO_TRANSFERT_DOUBLE_COMMANDE_ID;
      $deleteparams2 =[$db->escapeString($insertIntoTable2),$db->escapeString($critere2)];
      $deleteRequete2 = "CALL `deleteData`(?,?);";
      $this->ModelPs->createUpdateDelete($deleteRequete2, $deleteparams2);
      ######################################   #################################
    }

    $data=['message' => "".lang('messages_lang.message_success').""];
    session()->setFlashdata('alert', $data);
    return redirect('double_commande_new/Transfert_Double_Commande/liste_transfert');
  }


  public function liste_transfert()
  {
    // print_r('ok');die();
    $data=$this->urichk();
    $session  = \Config\Services::session();

    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $data['titre']='Liste des transferts';
    return view('App\Modules\double_commande_new\Views\Transfert_Liste_view',$data);
  }

  public function listing_Transfert()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

      $query_principal = 'SELECT h.HISTORIQUE_TRANSFERT_ID, h.PTBA_TACHE_ID_TRANSFERT, h.TRIMESTRE_ID, h.MONTANT_RECEPTION, h.MONTANT_TRANSFERT, h.PTBA_TACHE_ID_RECEPTION, h.AUTORISATION_TRANSFERT,
                    inst.DESCRIPTION_INSTITUTION AS INSTITUTION_TRANSFERT,
                    inst_deux.DESCRIPTION_INSTITUTION AS INSTITUTION_RECEPTION,
                    t1.DESC_TACHE AS DESC_TACHE_TRANSFERT, t1.CODE_NOMENCLATURE_BUDGETAIRE AS CODE_NB_TRANSFERT,
                    t2.DESC_TACHE AS DESC_TACHE_RECEPTION, t2.CODE_NOMENCLATURE_BUDGETAIRE AS CODE_NB_RECEPTION,
                    tr.DESC_TRIMESTRE
                FROM transfert_historique_transfert h
                LEFT JOIN ptba_tache t1 ON t1.PTBA_TACHE_ID = h.PTBA_TACHE_ID_TRANSFERT
                LEFT JOIN ptba_tache t2 ON t2.PTBA_TACHE_ID = h.PTBA_TACHE_ID_RECEPTION
                LEFT JOIN trimestre tr ON tr.TRIMESTRE_ID = h.TRIMESTRE_ID
                LEFT JOIN inst_institutions inst ON inst.INSTITUTION_ID = t1.INSTITUTION_ID
                LEFT JOIN inst_institutions inst_deux ON inst_deux.INSTITUTION_ID = t2.INSTITUTION_ID
                WHERE 1';

      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      //$var_search = str_replace("'", "''", $var_search);
      $limit="LIMIT 0,10";
      if($_POST['length'] != -1)
      {
        $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
      }

      $order_by="";
      $order_column="";
      $order_column= array(1,'inst.DESCRIPTION_INSTITUTION','t1.CODE_NOMENCLATURE_BUDGETAIRE','t1.DESC_TACHE','tr.DESC_TRIMESTRE','h.MONTANT_TRANSFERT','inst_deux.DESCRIPTION_INSTITUTION','t2.CODE_NOMENCLATURE_BUDGETAIRE','t2.DESC_TACHE','h.MONTANT_RECEPTION');

      $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY HISTORIQUE_TRANSFERT_ID DESC";

      $search = !empty($_POST['search']['value']) ?  (' AND (h.MONTANT_TRANSFERT LIKE "%'.$var_search.'%" OR h.MONTANT_RECEPTION LIKE "%'.$var_search.'%" OR inst.DESCRIPTION_INSTITUTION LIKE "%'.$var_search.'%" OR inst_deux.DESCRIPTION_INSTITUTION LIKE "%'.$var_search.'%" OR t1.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR t2.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR t1.DESC_TACHE LIKE "%'.$var_search.'%" OR t2.DESC_TACHE LIKE "%'.$var_search.'%" OR tr.DESC_TRIMESTRE LIKE "%'.$var_search.'%")'):"";
      $search = str_replace("'","\'",$search);
      $critaire = " ";

      $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;

      $query_filter = $query_principal." ".$search." ".$critaire;

      $requete="CALL `getList`('".$query_secondaire."')";
      $fetch_cov_frais = $this->ModelPs->datatable( $requete);
      $data = array();
      $u=1;
      foreach($fetch_cov_frais as $key)
      {

        $INSTITUTION_TRANSFERT = (mb_strlen($key->INSTITUTION_TRANSFERT) > 4) ? (mb_substr($key->INSTITUTION_TRANSFERT, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $key->HISTORIQUE_TRANSFERT_ID . '" data-toggle="tooltip" title="'.$key->INSTITUTION_TRANSFERT.'"><i class="fa fa-eye"></i></a>') : $key->INSTITUTION_TRANSFERT;

        $INSTITUTION_RECEPTION = (mb_strlen($key->INSTITUTION_RECEPTION) > 4) ? (mb_substr($key->INSTITUTION_RECEPTION, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $key->HISTORIQUE_TRANSFERT_ID . '" data-toggle="tooltip" title="'.$key->INSTITUTION_RECEPTION.'"><i class="fa fa-eye"></i></a>') : $key->INSTITUTION_RECEPTION;

        $DESC_TACHE_TRANSFERT = (mb_strlen($key->DESC_TACHE_TRANSFERT) > 4) ? (mb_substr($key->DESC_TACHE_TRANSFERT, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $key->HISTORIQUE_TRANSFERT_ID . '" data-toggle="tooltip" title="'.$key->DESC_TACHE_TRANSFERT.'"><i class="fa fa-eye"></i></a>') : $key->DESC_TACHE_TRANSFERT;

        $DESC_TACHE_RECEPTION = (mb_strlen($key->DESC_TACHE_RECEPTION) > 4) ? (mb_substr($key->DESC_TACHE_RECEPTION, 0, 4) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite' . $key->HISTORIQUE_TRANSFERT_ID . '" data-toggle="tooltip" title="'.$key->DESC_TACHE_RECEPTION.'"><i class="fa fa-eye"></i></a>') : $key->DESC_TACHE_RECEPTION;

        $sub_array = array();
        $sub_array[]=$u++;
        $sub_array[]=$INSTITUTION_TRANSFERT;
        $sub_array[]=$key->CODE_NB_TRANSFERT;
        $sub_array[]=$DESC_TACHE_TRANSFERT;
        $sub_array[]=$key->DESC_TRIMESTRE;
        $sub_array[]= number_format($key->MONTANT_TRANSFERT,2,","," ");
        $sub_array[]=$INSTITUTION_RECEPTION;
        $sub_array[]=$key->CODE_NB_RECEPTION;
        $sub_array[]=$DESC_TACHE_RECEPTION;
        $sub_array[]= number_format($key->MONTANT_RECEPTION,2,","," ");

        $data[] = $sub_array;

      }

      $requeteqp="CALL `getList`('".$query_principal."')";
      $recordsTotal = $this->ModelPs->datatable( $requeteqp);
      $requeteqf="CALL `getList`('".$query_filter."')";
      $recordsFiltered = $this->ModelPs->datatable( $requeteqf);
      $output = array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" =>count($recordsTotal),
        "recordsFiltered" => count($recordsFiltered),
        "data" => $data
      );
     return $this->response->setJSON($output);
  }
}
?>
