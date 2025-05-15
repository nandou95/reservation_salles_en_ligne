<?php
  /**
    * *MUNEZERO SONIA
    *Titre: Validation des Titre de decaisemment (interface de validation et la liste)
    *Numero de telephone: (+257) 65165772
    *Email: sonia@mediabox.bi
    *Date: 29 fevrier,2024
    **/

  /**Modifié par Jean-Vainqueur RUGAMBA
   *Numero de telephone: +257 66 33 43 25
   *WhatsApp: +257 62 47 19 15
   *Email pro: jean.vainqueur@mediabox.bi
   *Date: 11 Sept 2024
   **/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;
  use Dompdf\Dompdf;
  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


  class Validation_Titre extends BaseController
  {
    protected $session;
    protected $ModelPs;
    
    public function __construct()
    { 
      $this->library = new CodePlayHelper();
      $this->ModelPs = new ModelPs();
      $this->session = \Config\Services::session();
      $this->validation = \Config\Services::validation();

      $db = \Config\Database::connect();
      $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
      $db->query($sql);
    }

    public function getBindParms($columnselect, $table, $where, $orderby)
    {
      // code...
      $db = db_connect();
      $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
      return $bindparams;
    }

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
    /* Fin Gestion insertion */

    //Update
    public function update_all_table($table,$datatomodifie,$conditions)
    {
      $bindparams =[$table,$datatomodifie,$conditions];
      $updateRequete = "CALL `updateData`(?,?,?);";
      $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
    }

    public function uploadFile($fieldName, $folder, $prefix = ''): string
    {
      $prefix = ($prefix === '') ? uniqid() : $prefix;
      $path = '';
      $file = $this->request->getFile($fieldName);

      if ($file->isValid() && !$file->hasMoved()) {
        $newName = $prefix.'_'.uniqid(). '' . date('ymdhis') . '.' . $file->getExtension();
        $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
        $path = 'uploads/' . $folder . '/' . $newName;
      }
      return $newName;
    }

    // visualiser l'interface des validation a faire
    function liste_valide_faire()
    {
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if (empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($this->session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $paiement = $this->count_paiement();
      $data['get_recep_obr'] = $paiement['get_recep_obr'];
      $data['get_prise_charge'] = $paiement['get_prise_charge'];
      $data['get_etab_titre'] = $paiement['get_etab_titre'];
      $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
      $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
      $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
      $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc']; 
      $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
      $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
      $data['get_etape_corr'] = $paiement['get_etape_corr'];
      $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
      $data['recep_prise_charge']=$paiement['recep_prise_charge'];
      $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
      $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
      $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];
      $data['get_bord_brb']=$paiement['get_bord_brb'];
      $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
      $data['get_bord_dc']=$paiement['get_bord_dc'];
      $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];
      $data['get_titre_valide'] = $paiement['get_titre_valide'];
      $data['get_titre_termine'] = $paiement['get_titre_termine'];
      $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
      $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID='.$user_id.'', 'DESCRIPTION_INSTITUTION ASC');
      $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

      return view('App\Modules\double_commande_new\Views\Liste_Valide_Titre_Faire_View', $data);
    }

    //fonction pour affichage d'une liste de titre des decaisement a valide
    public function liste_validation()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $ETAPE_ID = 26;
      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

      $critere1 = "";
      $critere2 = "";

      if (!empty($INSTITUTION_ID)) {
        $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
        if (!empty($SOUS_TUTEL_ID)) {
          $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
        }
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

      $group = " ";

      $requetedebase = "SELECT DISTINCT(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,det.MONTANT_LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID =".$ETAPE_ID;

      $order_column = array('td.TITRE_DECAISSEMENT','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE', 'exec.ENG_JURIDIQUE', 'det.MONTANT_LIQUIDATION','det.MONTANT_ORDONNANCEMENT', 'td.MONTANT_PAIEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (td.TITRE_DECAISSEMENT LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR det.MONTANT_LIQUIDATION LIKE '%$var_search%' OR det.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR td.MONTANT_PAIEMENT LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';

      $critaire = $critere1 . " " . $critere2;
      //condition pour le query principale
      $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

      // condition pour le query filter
      $conditionsfilter = $critaire . " " . $search . " " . $group;

      $requetedebases = $requetedebase . " " . $conditions;
      $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
      $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
      $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      $data = array();
      $u = 1;
      foreach ($fetch_actions as $row)
      {
        $get_lien='SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID;
        $get_lien = "CALL getTable('" . $get_lien . "');";
        $link= $this->ModelPs->getRequeteOne($get_lien);
        $links=($link) ? 'double_commande_new/'.$link['LINK_ETAPE_DOUBLE_COMMANDE']:0;

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        $bouton="<a class='btn btn-primary btn-sm' title=''><span class='fa fa-arrow-up'></span></a>";

        $number=$row->TITRE_DECAISSEMENT;

        if(!empty($getProfil))
        {
          foreach($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $bouton= "<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Traiter' href='".base_url($links."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-arrow-up'></span></a>";
              $number = "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($links."/".md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)."")."' >".$row->TITRE_DECAISSEMENT."</a>";
            }
          }
        }

        $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
        $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
        $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
        $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);
        $MONTANT_PAIEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_PAIEMENT) : floatval($row->MONTANT_PAIEMENT_DEVISE);

        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->DESC_DEVISE_TYPE;
        $sub_array[] = number_format($MONTANT_BUDG, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_JURIDIQUE, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_LIQUIDATION, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_PAIEMENT, 2, ",", " ");

        $action1 ='<div class="row dropdown" style="color:#fff;">
        "'.$bouton.'"';
        $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action;
        $data[] = $sub_array;
      }

      $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebase . '")');
      $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebasefilter . '")');
      $output = array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" => count($recordsTotal),
        "recordsFiltered" => count($recordsFiltered),
        "data" => $data,
      );

      return $this->response->setJSON($output); //echo json_encode($output);
    }

    // visualiser l'interface des validation DEJA faire
    function liste_valide_termine()
    {
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if (empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }
      if($this->session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $annee_budgetaire_en_cours=$this->get_annee_budgetaire();

      $ANNEE_BUDGETAIRE_ID=$annee_budgetaire_en_cours;
      $INSTITUTION_ID=0;
      $SOUS_TUTEL_ID=0;
      $DATE_DEBUT=0;
      $DATE_FIN=0;

      $paiement = $this->count_paiement($ANNEE_BUDGETAIRE_ID,$INSTITUTION_ID,$SOUS_TUTEL_ID,$DATE_DEBUT,$DATE_FIN);
      $data['recep_prise_charge']=$paiement['recep_prise_charge'];
      $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
      $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
      $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];    
      $data['get_recep_obr'] = $paiement['get_recep_obr'];
      $data['get_prise_charge'] = $paiement['get_prise_charge'];
      $data['get_etab_titre'] = $paiement['get_etab_titre'];
      $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
      $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
      $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
      $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
      $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
      $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
      $data['get_etape_corr'] = $paiement['get_etape_corr'];
      $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
      $data['get_bord_brb']=$paiement['get_bord_brb'];
      $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
      $data['get_bord_dc']=$paiement['get_bord_dc'];
      $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];
      $data['get_titre_valide'] = $paiement['get_titre_valide'];
      $data['get_titre_termine'] = $paiement['get_titre_termine'];
      $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
      $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];

      $psgetrequete = "CALL `getRequete`(?,?,?,?)";
      $get_ann_budg = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$annee_budgetaire_en_cours,'ANNEE_BUDGETAIRE_ID ASC');
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $data['annee_budgetaire'] = $this->ModelPs->getRequete($psgetrequete, $get_ann_budg);
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'CODE_INSTITUTION ASC');
      $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

      $user_inst = "SELECT aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID WHERE USER_ID=".$user_id." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
      $user_inst_res = 'CALL getTable("'.$user_inst.'");';
      $institutions_user = $this->ModelPs->getRequete($user_inst_res);
      $INSTITUTION_ID = $institutions_user[0]->INSTITUTION_ID;
      $data['first_element_id'] = $INSTITUTION_ID;
      
      return view('App\Modules\double_commande_new\Views\Liste_Valide_Titre_Termine_View', $data);
    }

    //fonction pour affichage d'une liste de titre des decaisement deja faire 
    public function liste_validation_termine()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $ETAPE_ID = 27;
      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
      $DATE_FIN = $this->request->getPost('DATE_FIN');
      $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
      $critere_annee="";
      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
       if(!empty($ANNEE_BUDGETAIRE_ID))
      {
        $critere_annee.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
      }
      if (!empty($INSTITUTION_ID)) {
        $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
        if (!empty($SOUS_TUTEL_ID)) {
          $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
        }
      }

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere3.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
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

      $group = " ";

      $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec_detail.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE 1 AND td.ETAPE_DOUBLE_COMMANDE_ID >=".$ETAPE_ID. " AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND td.ETAPE_DOUBLE_COMMANDE_ID NOT IN(37,38,39,40,41,42) ".$critere1." ".$critere2." ".$critere3." ".$critere4." ".$critere_annee."";

      $order_column = array('td.TITRE_DECAISSEMENT','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE', 'exec.ENG_JURIDIQUE', 'exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (td.TITRE_DECAISSEMENT LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec_detail.MONTANT_LIQUIDATION LIKE '%$var_search%' OR exec_detail.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR td.MONTANT_PAIEMENT LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%')") : '';

      $critaire = $critere1 . " " . $critere2 . " " . $critere3 . " " . $critere4;
      //condition pour le query principale
      $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

      // condition pour le query filter
      $conditionsfilter = $critaire . " " . $search . " " . $group;

      $requetedebases = $requetedebase . " " . $conditions;
      $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
      $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
      $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      $data = array();
      $u = 1;
      foreach ($fetch_actions as $row)
           {
        $number=$row->TITRE_DECAISSEMENT;
        $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
        $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
        $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
        $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);
        $MONTANT_PAIEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_PAIEMENT) : floatval($row->MONTANT_PAIEMENT_DEVISE);
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->DESC_DEVISE_TYPE;
        $sub_array[] = number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " ");
        $sub_array[] = number_format($MONTANT_JURIDIQUE, $this->get_precision($MONTANT_JURIDIQUE), ",", " ");
        $sub_array[] = number_format($MONTANT_LIQUIDATION, $this->get_precision($MONTANT_LIQUIDATION), ",", " ");
        $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " ");
        $sub_array[] = number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_PAIEMENT), ",", " ");

        $action = "<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))."' ><span class='fa fa-plus'></span></a>";
        $sub_array[] = $action;
        $data[] = $sub_array;
      }

      $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebase . '")');
      $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' . $requetedebasefilter . '")');
      $output = array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" => count($recordsTotal),
        "recordsFiltered" => count($recordsFiltered),
        "data" => $data,
      );

      return $this->response->setJSON($output); //echo json_encode($output);
    }

    // EXporter un pdf TD valides
    function generatePdf($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $dompdf = new Dompdf();
      $html="<html><body>";
      $html.="<center><b>".lang('messages_lang.titre_termine')."</b></center><br><br>";


      $callpsreq = "CALL `getRequete`(?,?,?,?);";

      $ETAPE_ID = 27;
      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $nom_institution='';
      $nom_sous_titre="";
      $callpsreq = "CALL getRequete(?,?,?,?);";

      if($INSTITUTION_ID>0)
      {
        $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
        $inst = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','INSTITUTION_ID='.$INSTITUTION_ID.'','INSTITUTION_ID DESC');
        $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

        $nom_institution="Institution : ".$instt['DESCRIPTION_INSTITUTION'];
        if($SOUS_TUTEL_ID>0)
        {

          $critere2 = " AND exec.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
          $inst = $this->getBindParms('SOUS_TUTEL_ID, DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.'','SOUS_TUTEL_ID DESC');
          $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);
          $nom_sous_titre=" Sous titre: ".$instt['DESCRIPTION_SOUS_TUTEL'];
        }
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere3.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

      $requetedebase = "SELECT exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec_detail.EXECUTION_BUDGETAIRE_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_tache_detail exec_detail JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID LEFT JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE 1 AND td.ETAPE_DOUBLE_COMMANDE_ID >=".$ETAPE_ID. " AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND td.ETAPE_DOUBLE_COMMANDE_ID NOT IN(37,38,39,40,41,42) ".$critere1." ".$critere2." ".$critere3." ".$critere4." GROUP BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC";
      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');

       $html.="
       <p style='font-size:9px;'>".$nom_institution."</p>
       <p style='font-size:9px;'>".$nom_sous_titre."</p>

      <table cellspacing='0'>
      <tr>
      <th style='font-size:8px; border: 1px solid black'>#</th>
      <th style='font-size:8px; border: 1px solid black'>TITRE DECAISSEMENT</th>
      <th style='font-size:8px; border: 1px solid black'>DEVISE</th>
      <th style='font-size:8px; border: 1px solid black'>ENGAGEMENT BUDGETAIRE</th>
      <th style='font-size:8px; border: 1px solid black'>ENGAGEMENT JURIDIQUE</th>
      <th style='font-size:8px; border: 1px solid black'>LIQUIDATION</th>
      <th style='font-size:8px; border: 1px solid black'>ORDONNANCEMENT</th>
      <th style='font-size:8px; border: 1px solid black'>PAIEMENT</th>

      </tr>";


      $i=1;
      foreach ($getData as $key) {

        $ENG_BUDGETAIRE=number_format($key->ENG_BUDGETAIRE,'4',',',' ');

        $ACTIVITE=empty($key->DESC_PAP_ACTIVITE) ? '-' : $key->DESC_PAP_ACTIVITE;
        $TACHE=empty($key->DESC_TACHE) ? '-' : $key->DESC_TACHE;
        $MONTANT_BUDG = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_BUDGETAIRE) : floatval($key->ENG_BUDGETAIRE_DEVISE);
        $MONTANT_JURIDIQUE = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_JURIDIQUE) : floatval($key->ENG_JURIDIQUE_DEVISE);
        $MONTANT_LIQUIDATION = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_LIQUIDATION) : floatval($key->MONTANT_LIQUIDATION_DEVISE);
        $MONTANT_ORDONNANCEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_ORDONNANCEMENT) : floatval($key->MONTANT_ORDONNANCEMENT_DEVISE);
        $MONTANT_PAIEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_PAIEMENT) : floatval($key->MONTANT_PAIEMENT_DEVISE);


        $html.="
        <tr>
        <td style='font-size:8px; border: 1px solid black'>".$i."</td>
        <td style='font-size:8px; border: 1px solid black'>".$key->TITRE_DECAISSEMENT."</td>
        <td style='font-size:8px; border: 1px solid black'>".$key->DESC_DEVISE_TYPE."</td>

        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " ")."</td>
        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_JURIDIQUE, $this->get_precision($MONTANT_JURIDIQUE), ",", " ")."</td>

        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_LIQUIDATION, $this->get_precision($MONTANT_LIQUIDATION), ",", " ")."</td>
        <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_ORDONNANCEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " ")."</td>

         <td style='font-size:8px; border: 1px solid black'>".number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " ")."</td>

        </tr>

        ";
        $i++;
      }


      $html.="</table></body></html>";

          // Charger le contenu HTML
      $dompdf->loadHtml($html);
        // Définir la taille et l'orientation du papier
      $dompdf->setPaper('A4', 'portrait');

        // Rendre le HTML en PDF
      $dompdf->render();
      $name_file = 'Eng_bugdetaire'.uniqid().'.pdf';
        // $fichier='uploads/double_commande/PIECEJUSTIFICATIVE'.uniqid();
      $PATH_PIECE_JUSTIFICATIVE = 'uploads/double_commande/'.$name_file;

      header('Content-Type: application/pdf');
      header('Content-Disposition: attachment; filename="TD_valides'.uniqid().'.pdf"');

      echo $dompdf->output();

    }
      
    // Exporter l aliste excel TD valides
    function exporter_Excel($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {

      //$profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $ETAPE_ID = 27;
      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $nom_institution='';
      $nom_sous_titre="";
      $callpsreq = "CALL getRequete(?,?,?,?);";

      if ($INSTITUTION_ID>0)
      {
        $critere1 = " AND ptba.INSTITUTION_ID=" . $INSTITUTION_ID;
        $inst = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','INSTITUTION_ID='.$INSTITUTION_ID.'','INSTITUTION_ID DESC');
        $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

        $nom_institution="Institution : ".$instt['DESCRIPTION_INSTITUTION'];
        if ($SOUS_TUTEL_ID>0)
        {

          $critere2 = " AND ptba.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
          $inst = $this->getBindParms('SOUS_TUTEL_ID, DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.'','SOUS_TUTEL_ID DESC');
          $instt= $this->ModelPs->getRequeteOne($callpsreq, $inst);

          $nom_sous_titre="Sous titre : ".$instt['DESCRIPTION_SOUS_TUTEL'];
        }
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere3.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

      $requetedebase = "SELECT exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec_detail.EXECUTION_BUDGETAIRE_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_tache_detail exec_detail JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID LEFT JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE 1 AND td.ETAPE_DOUBLE_COMMANDE_ID >=".$ETAPE_ID. " AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND td.ETAPE_DOUBLE_COMMANDE_ID NOT IN(37,38,39,40,41,42) ".$critere1." ".$critere2." ".$critere3." ".$critere4." GROUP BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC";
     
      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();

      $sheet->setCellValue('C1', ''.$nom_institution.$nom_sous_titre.'');
      $sheet->setCellValue('C2', ''.$nom_sous_titre.'');
      $sheet->setCellValue('C3', ''.str_replace('&nbsp;', ' ', lang('messages_lang.titre_termine')).'');

      $sheet->setCellValue('A4', '#');
      $sheet->setCellValue('B4', 'TITRE DECAISSEMENT');
      $sheet->setCellValue('C4', 'DEVISE');
      $sheet->setCellValue('D4', 'ENGAGEMENT BUDGETAIRE');
      $sheet->setCellValue('E4', 'ENGAGEMENT JURIDIQUE');
      $sheet->setCellValue('F4', 'LIQUIDATION');
      $sheet->setCellValue('G4', 'ORDONNANCEMENT');
      $sheet->setCellValue('H4', 'PAIEMENT');
         
 
      $rows = 5;
      $i=1;
      foreach ($getData as $key)
      {

        $MONTANT_BUDG = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_BUDGETAIRE) : floatval($key->ENG_BUDGETAIRE_DEVISE);
        $MONTANT_JURIDIQUE = $key->DEVISE_TYPE_ID == 1 ? floatval($key->ENG_JURIDIQUE) : floatval($key->ENG_JURIDIQUE_DEVISE);
        $MONTANT_LIQUIDATION = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_LIQUIDATION) : floatval($key->MONTANT_LIQUIDATION_DEVISE);
        $MONTANT_ORDONNANCEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_ORDONNANCEMENT) : floatval($key->MONTANT_ORDONNANCEMENT_DEVISE);
        $MONTANT_PAIEMENT = $key->DEVISE_TYPE_ID == 1 ? floatval($key->MONTANT_PAIEMENT) : floatval($key->MONTANT_PAIEMENT_DEVISE);

        $sheet->setCellValue('A' . $rows, $i);
        $sheet->setCellValue('B' . $rows, $key->TITRE_DECAISSEMENT);
        $sheet->setCellValue('C' . $rows, $key->DESC_DEVISE_TYPE);

        $sheet->setCellValue('D' . $rows, number_format($MONTANT_BUDG, $this->get_precision($MONTANT_BUDG), ",", " "));
        $sheet->setCellValue('E' . $rows, number_format($MONTANT_JURIDIQUE, $this->get_precision($MONTANT_JURIDIQUE), ",", " "));
        $sheet->setCellValue('F' . $rows, number_format($MONTANT_LIQUIDATION, $this->get_precision($MONTANT_LIQUIDATION), ",", " "));
        $sheet->setCellValue('G' . $rows, number_format($MONTANT_ORDONNANCEMENT, $this->get_precision($MONTANT_ORDONNANCEMENT), ",", " "));
        $sheet->setCellValue('H' . $rows, number_format($MONTANT_PAIEMENT, $this->get_precision($MONTANT_PAIEMENT), ",", " "));
         
        $rows++;
        $i++;
      }

      $sheet->getColumnDimension('A')->setWidth(30);
      $sheet->getColumnDimension('B')->setWidth(30);
      $sheet->getColumnDimension('C')->setWidth(30);
      $sheet->getColumnDimension('D')->setWidth(50);
      $sheet->getColumnDimension('E')->setWidth(30);
      $sheet->getColumnDimension('F')->setWidth(30);
      $sheet->getColumnDimension('G')->setWidth(30);
      $sheet->getColumnDimension('H')->setWidth(30);
      $sheet->getColumnDimension('I')->setWidth(30);
      $sheet->getColumnDimension('J')->setWidth(30);
      $sheet->getColumnDimension('K')->setWidth(30);

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('TD_valides'.$code.'.xlsx');

      return redirect('double_commande_new/Validation_Titre/liste_valide_termine');
    }


    // visualiser l'interface de validation d'un titre de decaissement
    function confirmer($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if(empty($user_id))
      {
         return redirect('Login_Ptba/do_logout');
      }
      
      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $infoAffiche  = 'SELECT det.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,det.MONTANT_LIQUIDATION,det.MONTANT_ORDONNANCEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,td.MONTANT_PAIEMENT,td.TITRE_DECAISSEMENT FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) = "'.$id.'"';

      $infoAffiche = "CALL `getTable`('" .$infoAffiche."');";
      $data['info']= $this->ModelPs->getRequeteOne($infoAffiche);

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $callpsreq = "CALL `getRequete`(?,?,?,?);";
            $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'],' DESC_ETAPE_DOUBLE_COMMANDE DESC');
            $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);
            $data['etape_titre'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];

            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'],'DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

            $detail=$this->detail_new($id);
            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['get_infoEBET']=$detail['get_infoEBET'];  
            return view('App\Modules\double_commande_new\Views\Titre_A_Valide_view',$data);
          }
        }
        return redirect('Login_Ptba/homepage');
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }
    }

    // fonction pour avancer l'etape et pour faire la modification des donnees
    function save_titre_valider()
    {
      $db = db_connect();
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout'); 
      }

      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID'); 
      $rules = [
         'DATE_RECEPTION' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
          ]
        ],
        'DATE_VALIDE_TITRE' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
          ]
        ],
        'DATE_TRANSMISSION' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
          ]
        ]
      ];

      $this->validation->setRules($rules);

      if($this->validation->withRequest($this->request)->run())
      {
        $session  = \Config\Services::session();
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
        $callpsreq = "CALL `getRequete`(?,?,?,?);";
        $user = $this->getBindParms('USER_ID', 'user_users', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
        $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);
        $USER_ID=$getuser['USER_ID'];

        $id_exec_titr_dec = $this->request->getPost('id_exec_titr_dec');
        $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID'); 
        $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID'); 

        $DATE_VALIDE_TITRE=$this->request->getPost('DATE_VALIDE_TITRE');
        $ETAPE_DOUBLE_COMMANDE_ID=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
        $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
        $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');

        $callpsreq = "CALL `getRequete`(?,?,?,?);";     
        $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,IS_CORRECTION,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE_COMMANDE_ID.' AND IS_SALAIRE=0',' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
        $etape_suivante22= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);

        $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
        $ETAPE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];

        $insertIntoOp='execution_budgetaire_tache_detail_histo';
        $columOp="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsOp=$id_exec_titr_dec.",".$ETAPE_ID.",".$USER_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";
        $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

        $whereracc ="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_exec_titr_dec;
        $insertIntoracc='execution_budgetaire_titre_decaissement';
        $columracc="ETAPE_DOUBLE_COMMANDE_ID = ".$ETAPE_DOUBLE_COMMANDE_ID.",DATE_VALIDE_TITRE = '".$DATE_VALIDE_TITRE."'";
        $this->update_all_table($insertIntoracc,$columracc,$whereracc);

        $data=['message' => "".lang('messages_lang.valid_titre_dec').""];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Validation_Titre/liste_valide_termine');
      }
      else
      {
        return $this->confirmer(md5($id_exec_titr_dec));
      }
    }

    //récupération du sous titre par rapport à l'institution
    function get_sous_titre($INSTITUTION_ID = 0)
    {
      $db = db_connect();
      $callpsreq = "CALL `getRequete`(?,?,?,?);";

      $get_sous_tutelle = $this->getBindParms('`SOUS_TUTEL_ID`,`DESCRIPTION_SOUS_TUTEL`', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.INSTITUTION_ID=' . $INSTITUTION_ID . ' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
      $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);

      $html = '<option value="">' . lang('messages_lang.labelle_selecte') . '</option>';
      foreach ($sous_tutelle as $key)
      {
        $html .= '<option value="' . $key->SOUS_TUTEL_ID . '">' . $key->DESCRIPTION_SOUS_TUTEL . '</option>';
      }

      $output = array(
        "sous_tutel" => $html,
      );

      return $this->response->setJSON($output);
    }

      // cette fonction permet de recuperer le nombre de chiffres apres la virgule d un  nomb re passé en paramettre
        function get_precision($value=0){
      
      $parts = explode('.', strval($value));
      return isset($parts[1]) ? strlen($parts[1]) : 0;
     
    }






    public function get_ordon_Afaire_sup($value='')
  {
    $data=$this->urichk();
    $callpsreq = "CALL getRequete(?,?,?,?);";

    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
    else
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $user_inst = "SELECT aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID WHERE USER_ID=".$user_id." ORDER BY inst_institutions.CODE_INSTITUTION ASC";
      $user_inst_res = 'CALL getTable("'.$user_inst.'");';
      $institutions_user = $this->ModelPs->getRequete($user_inst_res);

      $INSTITUTION_ID = $institutions_user[0]->INSTITUTION_ID;
      $SOUS_TUTEL_ID = 0;
      $DU = 0;
      $AU = 0;

    $data_menu=$this->getDataMenuOrdonnancement($INSTITUTION_ID,$SOUS_TUTEL_ID,$DU,$AU);  
    $data['get_etape_reject_ordo']=$data_menu['get_etape_reject_ordo'];
    $data['institutions_user']=$data_menu['institutions_user'];
    $data['get_ordon_Afaire']=$data_menu['get_ordon_Afaire'];
    $data['get_ordon_Afaire_sup']=$data_menu['get_ordon_Afaire_sup'];
    $data['get_ordon_deja_fait']=$data_menu['get_ordon_deja_fait'];
    $data['get_bord_spe']=$data_menu['get_bord_spe'];
    $data['get_bord_deja_spe']=$data_menu['get_bord_deja_spe'];
    $data['get_ordon_AuCabinet']=$data_menu['get_ordon_AuCabinet'];
    $data['get_ordon_BorderCabinet']=$data_menu['get_ordon_BorderCabinet'];
    $data['get_ordon_BonCED']=$data_menu['get_ordon_BonCED'];

    $data['first_element_id'] = $INSTITUTION_ID;
    return view('App\Modules\double_commande_new\Views\Ordonnancement_Double_Commande_Afaire_sup_List',$data);   
  }
  }
?>