<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Transfert 
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 25 10 2023
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

class Transfert_Meme_Activite extends BaseController
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

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $IMPORTndparams;
  }

  public function uploadFile($fieldName=NULL, $folder=NULL, $prefix = NULL): string
  {
    $prefix = ($prefix === '') ? uniqid() : $prefix;
    $path = '';

    $file = $this->request->getFile($fieldName);

    if ($file->isValid() && !$file->hasMoved()) {
      $newName = uniqid(). '' . date('ymdhis') . '.' . $file->getExtension();
      $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
      $path = 'uploads/' . $folder . '/' . $newName;
    }
    return $path;
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

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";

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

    //tranches
    $tranche= $tranche="SELECT `TRIMESTRE_ID`, `CODE_TRIMESTRE`,CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) as debut,CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y')) as fin FROM `trimestre` WHERE 1 AND date_format(now(),'%m-%d-%Y') BETWEEN CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) AND CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y'))";
    $getTranchee = 'CALL `getTable`("'.$tranche.'");';
    $getTranche = $this->ModelPs->getRequeteOne($getTranchee);

    $gettranches='SELECT DESC_TRIMESTRE,TRIMESTRE_ID  FROM trimestre WHERE 1 AND TRIMESTRE_ID>'.$getTranche['TRIMESTRE_ID'].' AND TRIMESTRE_ID!=5';
    $data['tranches'] = $this->ModelPs->getRequete("CALL `getTable`('" . $gettranches . "')");

    //Sélectionner les motifs de création 
    $getmotif='SELECT MOTIF_TACHE_ID,DESCR_MOTIF_TACHE FROM motif_creation_tache WHERE 1 ORDER BY DESCR_MOTIF_TACHE ASC ';
    $data['motif'] = $this->ModelPs->getRequete("CALL `getTable`('" . $getmotif . "')");

    return view('App\Modules\double_commande_new\Views\Transfert_Meme_Activite_Add_View',$data);
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

    if ($TRIMESTRE_ID==1)
    {
      $MONTANT_TRIMESTRE_SELECTION = $montant_info['BUDGET_RESTANT_T1'];
    }
    else if ($TRIMESTRE_ID==2)
    {
      $MONTANT_TRIMESTRE_SELECTION = $montant_info['BUDGET_RESTANT_T2'];
    }
    else if ($TRIMESTRE_ID==3)
    {
      $MONTANT_TRIMESTRE_SELECTION = $montant_info['BUDGET_RESTANT_T3'];
    }
    else if ($TRIMESTRE_ID==4)
    {
      $MONTANT_TRIMESTRE_SELECTION = $montant_info['BUDGET_RESTANT_T4'];
    }

    $output = array(
      "MONTANT_TRIMESTRE_SELECTION" => $MONTANT_TRIMESTRE_SELECTION
    );
    return $this->response->setJSON($output);
  }

  public function get_MontantVoteByActivite($value='')
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
    $bind_proc = $this->getBindParms('BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4,BUDGET_ANNUEL', 'ptba_tache', 'PTBA_TACHE_ID ='.$PTBA_TACHE_ID,'PTBA_TACHE_ID  ASC');
    $montant_info= $this->ModelPs->getRequeteOne($callpsreq, $bind_proc);
    #####################################      ##################################

   //tranches
    $tranche="SELECT `TRIMESTRE_ID`, `CODE_TRIMESTRE`,CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) as debut,CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y')) as fin FROM `trimestre` WHERE 1 AND date_format(now(),'%m-%d-%Y') BETWEEN CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) AND CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y'))";
    $getTranchee = 'CALL `getTable`("'.$tranche.'");';
    $getTranche = $this->ModelPs->getRequeteOne($getTranchee);


    if ($getTranche['TRIMESTRE_ID']==1)
    {
      $MONTANT_VOTE = $montant_info['BUDGET_T1'];
      $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T1'];
    }
    else if ($getTranche['TRIMESTRE_ID']==2)
    {
      $MONTANT_VOTE = $montant_info['BUDGET_T2'];
      $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T2'];
    }
    else if ($getTranche['TRIMESTRE_ID']==3)
    {
      $MONTANT_VOTE = $montant_info['BUDGET_T3'];
      $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T3'];
    }
    else if ($getTranche['TRIMESTRE_ID']==4)
    {
      $MONTANT_VOTE = $montant_info['BUDGET_T4'];
      $MONTANT_RESTANT = $montant_info['BUDGET_RESTANT_T4'];
    }

    $output = array(
      "MONTANT_VOTE" => $MONTANT_VOTE,
      "MONTANT_RESTANT" => $MONTANT_RESTANT
    );
    return $this->response->setJSON($output);
  }

  public function getInfoDetail($value='')
  {
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if ($USER_ID=='') {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $MOTIF_TACHE_ID = $this->request->getPost('MOTIF_TACHE_ID');
    $NOM = $this->request->getPost('NOM');
    $PRENOM = $this->request->getPost('PRENOM');
    $POSTE = $this->request->getPost('POSTE');
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $CODE_NOMENCLATURE_BUDGETAIRE_ID = $this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
    $PTBA_ID = $this->request->getPost('PTBA_ID');
    $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
    $TRIMESTRE_ID = $this->request->getPost('TRIMESTRE_ID');
    $MONTANT_TRIMESTRE_SELECTION = $this->request->getPost('MONTANT_TRIMESTRE_SELECTION');
    $MONTANT_VOTE = $this->request->getPost('MONTANT_VOTE');
    $MONTANT_RESTANT = $this->request->getPost('MONTANT_RESTANT');
    $MONTANT_TRANSFERT = $this->request->getPost('MONTANT_TRANSFERT');
    $MONTANT_APRES_TRANSFERT = $this->request->getPost('MONTANT_APRES_TRANSFERT');


    ######################## upload file autorisation de transfert ##############################
    $AUTORISATION = $this->request->getPost('AUTORISATION_TRANSFERT');
    $AUTORISATION_TRANSFERT=$this->uploadFile('AUTORISATION_TRANSFERT','file_autorisation_tempo',$AUTORISATION);

    $insertIntoTable='transfert_tempo_autorisation';
    $columsinsert="USER_ID,AUTORISATION_TRANSFERT";
    $datacolumsinsert=$USER_ID.",'".$AUTORISATION_TRANSFERT."'";
    $TEMPO_AUTORISATION_TRANSFERT_ID   = $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

    $sql_file='SELECT AUTORISATION_TRANSFERT FROM transfert_tempo_autorisation WHERE TEMPO_AUTORISATION_TRANSFERT_ID='.$TEMPO_AUTORISATION_TRANSFERT_ID.' AND USER_ID='.$USER_ID.'';
    $file = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_file . "')");
    $show_file = '<a href="'.base_url($file['AUTORISATION_TRANSFERT']).'" target="_blank"><span class="fa fa-file-pdf" style="color:green;font-size: 200%;"></span></a>';
    #####################################      ################################################
    

    //Sélectionner les motifs de création 
    if (!empty($MOTIF_TACHE_ID)) {
      $sql_motif='SELECT DESCR_MOTIF_TACHE FROM motif_creation_tache WHERE 1 AND MOTIF_TACHE_ID='.$MOTIF_TACHE_ID.' ORDER BY DESCR_MOTIF_TACHE ASC ';
      $motif = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_motif . "')");
    }
    

    //get data institution
    $sql_institution='SELECT DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1 AND INSTITUTION_ID ='.$INSTITUTION_ID.' ';
    $institution = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_institution . "')");

    //get sous tutel
    $sql_tutel='SELECT DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE 1 AND SOUS_TUTEL_ID ='.$SOUS_TUTEL_ID.' ';
    $sous_tutel = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_tutel . "')");

    //get activité
    if(!empty($PTBA_ID))
    {
      
      $sql_activite = $this->getBindParms('DESC_PAP_ACTIVITE,PAP_ACTIVITE_ID', 'pap_activites', ' PAP_ACTIVITE_ID='.$PTBA_ID.' ', 'DESC_PAP_ACTIVITE  ASC');
      $activites = $this->ModelPs->getRequeteOne($callpsreq, $sql_activite);
    }
    

    //get tâche
    $sql_tache = $this->getBindParms('PTBA_TACHE_ID, DESC_TACHE','ptba_tache','PTBA_TACHE_ID='.$PTBA_TACHE_ID,'DESC_TACHE ASC ');
    $taches = $this->ModelPs->getRequeteOne($callpsreq, $sql_tache);

    // ligne budgetaire
    $imputa = 'SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID;
    $imputa = "CALL `getTable`('" . $imputa . "');";
    $IMPUTATI= $this->ModelPs->getRequeteOne($imputa);
    $CODE_NOMENCLATURE_BUDGETAIRE = $IMPUTATI['CODE_NOMENCLATURE_BUDGETAIRE'];

    //get tranche
    $sql_tranche='SELECT DESC_TRIMESTRE FROM trimestre WHERE 1 AND TRIMESTRE_ID='.$TRIMESTRE_ID.'';
    $tranches = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_tranche . "')");

    $infoMotif = '';
    if ($MOTIF_TACHE_ID==2 || $MOTIF_TACHE_ID==3) {
      $infoMotif = '<tr>
      <td style="width:350px ;"><font style="float:left;"><i class="fa fa-comment"> </i>&nbsp;'.lang('messages_lang.label_motif').' </font></td>
      <td><strong><font style="float:left;">'.$motif['DESCR_MOTIF_TACHE'].'</font></strong></td>
      </tr>
      <tr>
      <td style="width:350px ;"><font style="float:left;"><i class="fa fa-user"> </i>&nbsp;'.lang('messages_lang.labelle_nom').'</font></td>
      <td><strong><font style="float:left;">'.$NOM.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:350px ;"><font style="float:left;"><i class="fa fa-user"> </i>&nbsp;'.lang('messages_lang.labelle_prenom').'</font></td>
      <td><strong><font style="float:left;">'.$PRENOM.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:350px ;"><font style="float:left;"><i class="fa fa-home"> </i>&nbsp;'.lang('messages_lang.poste').' </font></td>
      <td><strong><font style="float:left;">'.$POSTE.'</font></strong></td>
      </tr>';
    }else if ($MOTIF_TACHE_ID==1 || $MOTIF_TACHE_ID==4){
      $infoMotif = '<tr>
      <td style="width:350px ;"><font style="float:left;"><i class="fa fa-comment"> </i>&nbsp;'.lang('messages_lang.label_motif').'</font></td>
      <td><strong><font style="float:left;">'.$motif['DESCR_MOTIF_TACHE'].'</font></strong></td>
      </tr>';
    }

    $html_activite = ' ';
    if(!empty($PTBA_ID))
    {
      $html_activite ='<tr>
      <td style="width:350px ;"><font style="float:left;"><i class="fa fa-cogs"> </i>&nbsp;'.lang('messages_lang.labelle_activite').'</font></td>
      <td><strong><font style="float:left;">'.$activites['DESC_PAP_ACTIVITE'].'</font></strong></td>
      </tr>';
    }

    $html = '<div style="border:1px solid #ddd;border-radius:5px;margin: 5px">
    <div class="row" style="margin :  5px">
    <div class="col-12">
    <div class=" table-responsive ">
    <table class="table m-b-0 m-t-20">
    <tbody>
    '.$infoMotif.'
    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-home"> </i>&nbsp;'.lang('messages_lang.labelle_inst_min').'</font></td>
    <td><strong><font style="float:left;">'.$institution['DESCRIPTION_INSTITUTION'].'</font></strong></td>
    </tr>
    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-certificate"> </i>&nbsp;'.lang('messages_lang.label_sousTitre').'</font></td>
    <td><strong><font style="float:left;">'. $sous_tutel['DESCRIPTION_SOUS_TUTEL'].'</font></strong></td>
    </tr>
    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-circle"> </i>&nbsp;'.lang('messages_lang.labelle_code_budgetaire').' </font></td>
    <td><strong><font style="float:left;">'.$CODE_NOMENCLATURE_BUDGETAIRE.'</font></strong></td>
    </tr>
    '.$html_activite.'
    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-tasks"> </i>&nbsp;'.lang('messages_lang.label_taches').'</font></td>
    <td><strong><font style="float:left;">'.$taches['DESC_TACHE'].'</font></strong></td>
    </tr>
    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-list"> </i>&nbsp;'.lang('messages_lang.labelle_tranche').'</font></td>
    <td><strong><font style="float:left;">'.$tranches['DESC_TRIMESTRE'].'</font></strong></td>
    </tr>

    <tr>
    <td style="width:350px;">
      <font style="float:left;"><i class="fa fa-credit-card"></i>&nbsp;'.lang('messages_lang.label_mont_select').'</font>
    </td>
    <td><strong><font style="float:left;">'.$MONTANT_TRIMESTRE_SELECTION.'</font></strong></td>
    </tr>

    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-credit-card"></i>&nbsp;'.lang('messages_lang.mont_a_transf').'</font></td>
    <td><strong><font style="float:left;">'.$MONTANT_TRANSFERT.'</font></strong></td>
    </tr>

    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-credit-card"></i>&nbsp;'.lang('messages_lang.labelle_montant_vote').'</font></td>
    <td><strong><font style="float:left;">'.$MONTANT_VOTE.'</font></strong></td>
    </tr>

    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-credit-card"></i>&nbsp;'.lang('messages_lang.label_Money_res').'</font></td>
    <td><strong><font style="float:left;">'.$MONTANT_RESTANT.'</font></strong></td>
    </tr>

    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-credit-card"></i>&nbsp;'.lang('messages_lang.labelle_montant_apres_transfert').'</font></td>
    <td><strong><font style="float:left;">'.$MONTANT_APRES_TRANSFERT.'</font></strong></td>
    </tr>

    <tr>
    <td style="width:350px ;"><font style="float:left;"><i class="fa fa-file"></i>&nbsp;'.lang('messages_lang.label_auto_trans').'</font></td>
    <td><strong><font style="float:left;">'.$show_file.'</font></strong></td>
    </tr>

    </tbody>
    </table>        
    </div>
    </div>
    </div>
    </div>';

    $output = array(
      "html" => $html
    );
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

  // Enregistrement des données dans la table historique_transfert
  public function send_data($value='')
  {
    $data=$this->urichk();
    $session  = \Config\Services::session();

    $db = db_connect(); 
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    
    if ($USER_ID=='')
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');

    $TRIMESTRE_ID = $this->request->getPost('TRIMESTRE_ID');
    $MONTANT_TRANSFERT = preg_replace('/\s/', '', $this->request->getPost('MONTANT_TRANSFERT'));
    $TYPE_OPERATION_ID = 4;

    ####################### upload file autorisation de transfert ##############################
    $AUTORISATION_TRANSFERT = $this->request->getPost('AUTORISATION_TRANSFERT');
    $AUTORISATION_TRANSFERT=$this->uploadFile('AUTORISATION_TRANSFERT','double_commande',$AUTORISATION_TRANSFERT);


    $insertIntoTable='transfert_historique_transfert';

    $columsinsert="TYPE_OPERATION_ID,USER_ID,MONTANT_TRANSFERT,PTBA_TACHE_ID_TRANSFERT,MONTANT_RECEPTION,PTBA_TACHE_ID_RECEPTION,TRIMESTRE_ID,AUTORISATION_TRANSFERT";
    $datacolumsinsert=$TYPE_OPERATION_ID.",".$USER_ID.",".$MONTANT_TRANSFERT.",".$PTBA_TACHE_ID.",".$MONTANT_TRANSFERT.",".$PTBA_TACHE_ID.",".$TRIMESTRE_ID.",'".$AUTORISATION_TRANSFERT."'";
    $HISTORIQUE_TRANSFERT_ID  = $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);
    ##################################"              ######################

    // get transfert d'origine
    $tache = $this->getBindParms('BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4','ptba_tache','PTBA_TACHE_ID ='.$PTBA_TACHE_ID,'PTBA_TACHE_ID  ASC');
    $data_tache = $this->ModelPs->getRequeteOne($psgetrequete, $tache);

    //mise a jour dans la table ptba
    $table = 'ptba_tache';
    $where='PTBA_TACHE_ID ='.$PTBA_TACHE_ID.'';

    if($TRIMESTRE_ID==1)
    {
      //cas du transfert
      $MONTANT_RESTANT_T1 = $data_tache['BUDGET_RESTANT_T1']-$MONTANT_TRANSFERT;
      $data='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_T1.'';
      $this->update_all_table($table,$data,$where);
    }
    else if ($TRIMESTRE_ID==2)
    {
      //cas du transfert
      $MONTANT_RESTANT_T2 = $data_tache['BUDGET_RESTANT_T2']-$MONTANT_TRANSFERT;
      $data='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_T2.'';
      $this->update_all_table($table,$data,$where);
    }
    else if ($TRIMESTRE_ID==3)
    {
      //cas du transfert
      $MONTANT_RESTANT_T3 = $data_tache['BUDGET_RESTANT_T3']-$MONTANT_TRANSFERT;
      $data='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_T3.'';
      $this->update_all_table($table,$data,$where);
    }
    else if ($TRIMESTRE_ID==4)
    {
      //cas du transfert
      $MONTANT_RESTANT_T4 = $data_tache['BUDGET_RESTANT_T4']-$MONTANT_TRANSFERT;
      $data='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_T4.'';
      $this->update_all_table($table,$data,$where);
    }

    //get transfert de destinataire
   //tranches
    $tranche="SELECT `TRIMESTRE_ID`, `CODE_TRIMESTRE`,CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) as debut,CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y')) as fin FROM `trimestre` WHERE 1 AND date_format(now(),'%m-%d-%Y') BETWEEN CONCAT(`DATE_DEBUT`,'-',date_format(now(),'%Y')) AND CONCAT(`DATE_FIN`,'-',date_format(now(),'%Y'))";
    $getTranchee = 'CALL `getTable`("'.$tranche.'");';
    $getTranche = $this->ModelPs->getRequeteOne($getTranchee);

    if ($getTranche['TRIMESTRE_ID']==1)
    {
      //cas receptrice
      $MONTANT_RESTANT_T1_RECEVOIR = $data_tache['BUDGET_RESTANT_T1']+$MONTANT_TRANSFERT;
      $data='BUDGET_RESTANT_T1 = '.$MONTANT_RESTANT_T1_RECEVOIR.'';
      $this->update_all_table($table,$data,$where);
    }
    else if ($getTranche['TRIMESTRE_ID']==2)
    {
      //cas receptrice
      $MONTANT_RESTANT_T2_RECEVOIR = $data_tache['BUDGET_RESTANT_T2']+$MONTANT_TRANSFERT;
      $data='BUDGET_RESTANT_T2 = '.$MONTANT_RESTANT_T2_RECEVOIR.'';
      $this->update_all_table($table,$data,$where);
    }
    else if ($getTranche['TRIMESTRE_ID']==3)
    {
      //cas receptrice
      $MONTANT_RESTANT_T3_RECEVOIR = $data_tache['BUDGET_RESTANT_T3']+$MONTANT_TRANSFERT;
      $data='BUDGET_RESTANT_T3 = '.$MONTANT_RESTANT_T3_RECEVOIR.'';
      $this->update_all_table($table,$data,$where);
    }
    else if ($getTranche['TRIMESTRE_ID']==4)
    {
      //cas receptrice
      $MONTANT_RESTANT_T4_RECEVOIR = $data_tache['BUDGET_RESTANT_T4']+$MONTANT_TRANSFERT;
      $data='BUDGET_RESTANT_T4 = '.$MONTANT_RESTANT_T4_RECEVOIR.'';
      $this->update_all_table($table,$data,$where);
    }

    ######### Avez-vous l'autorisation du Ministre des finances ? insetion motif  ##################
    //Table associative
    $MOTIF_TACHE_ID = $this->request->getPost('MOTIF_TACHE_ID');

    if(!empty($MOTIF_TACHE_ID))
    {
      $table='transfert_meme_tache_motif';

      if($MOTIF_TACHE_ID == 2 || $MOTIF_TACHE_ID == 3)
      {
        $NOM = $this->request->getPost('NOM');
        $PRENOM = $this->request->getPost('PRENOM');
        $POSTE = $this->request->getPost('POSTE');

        $columsinsert1="HISTORIQUE_TRANSFERT_ID,MOTIF_TACHE_ID,USER_ID,NOM,PRENOM,POSTE";
        $datatoinsert1="".$HISTORIQUE_TRANSFERT_ID.",".$MOTIF_TACHE_ID.",".$USER_ID.",'".$NOM."','".$PRENOM."','".$POSTE."'";
        $this->save_all_table($table,$columsinsert1,$datatoinsert1);
      }
      else
      {
        $columsinsert2="HISTORIQUE_TRANSFERT_ID,MOTIF_TACHE_ID,USER_ID";
        $datatoinsert2="".$HISTORIQUE_TRANSFERT_ID.",".$MOTIF_TACHE_ID.",".$USER_ID."";
        $this->save_all_table($table,$columsinsert2,$datatoinsert2);
      }
    }
    ######################################      ##################################    ################

    //Delete file dans la table tempo autorisation transfert
    $delete_file_folder = 'SELECT AUTORISATION_TRANSFERT FROM transfert_tempo_autorisation WHERE USER_ID='.$USER_ID.' ORDER BY TEMPO_AUTORISATION_TRANSFERT_ID DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $delete_file_folder . "')");

    foreach ($tempo as $item)
    { 
      $AUTORISATION_TRANSFERT = $item->AUTORISATION_TRANSFERT;
    }
    // unlink($AUTORISATION_TRANSFERT);

    $sql_file='DELETE FROM transfert_tempo_autorisation WHERE USER_ID='.$USER_ID.'';
    $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_file . "')");
    #####################################      ################################################

    $data=['message' => "".lang('messages_lang.message_success').""];
    session()->setFlashdata('alert', $data);
    return redirect('double_commande_new/Transfert_Double_Commande/liste_transfert');
  }

  public function deleteFile($value='')
  {
    $session  = \Config\Services::session();
    $USER_ID = session()->get("SESSION_SUIVIE_PTBA_USER_ID");

    if ($USER_ID=='') {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_TRANSFERT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    //Delete file dans la table tempo autorisation transfert
    $delete_file_folder = 'SELECT AUTORISATION_TRANSFERT FROM transfert_tempo_autorisation WHERE USER_ID='.$USER_ID.' ORDER BY TEMPO_AUTORISATION_TRANSFERT_ID DESC  ';
    $tempo = $this->ModelPs->getRequete("CALL `getTable`('" . $delete_file_folder . "')");

    foreach ($tempo as $item)
    { 
      $AUTORISATION_TRANSFERT = $item->AUTORISATION_TRANSFERT;
    }
    unlink($AUTORISATION_TRANSFERT);

    $sql_file='DELETE FROM transfert_tempo_autorisation WHERE USER_ID='.$USER_ID.'';
    $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_file . "')");
    #####################################      ################################################
  }
}
?>
