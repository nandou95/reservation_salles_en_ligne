<?php
  /** Alain Charbel Nderagakura
    *Titre: Les liste de paiement cas salaire
    *Numero de telephone: (+257) 62003522
    *Email: charbel@mediabox.bi
    *Date: 10 septembre,2024
    * 
    **/
  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;
  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

  class Paiement_Salaire_Liste extends BaseController
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


    function index()
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if (empty($user_id)) {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_PRISE_CHARGE_SALAIRE')==1)
      {
        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_prise_charge');
      }
      else if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_NET')==1)
      {
        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_td_Salaire_Ne');
      }
      else if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_RETENUS')==1)
      {
        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_td_Autres_Retenus');
      }
      else if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DIR_COMPT_SALAIRE')==1)
      {
        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_dir_compt');
      }
      else if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DGFP_SALAIRE')==1)
      {
        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_dgfp');
      }
      else if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_MIN_SALAIRE')==1)
      {
        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_ministre');
      }
      else if($session->get('SESSION_SUIVIE_PTBA_VALIDATION_SALAIRE_NET')==1)
      {
        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_valide_td_net');
      }
      else if($session->get('SESSION_SUIVIE_PTBA_VALIDATION_RETENUS_SALAIRE')==1)
      {
        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_valide_td_autre_retenu');
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }
    }

    //vue liste prise en charge
    function vue_prise_charge()
    {
      $session  = \Config\Services::session();
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }
      if($session->get('SESSION_SUIVIE_PTBA_PRISE_CHARGE_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $data['element']='';
      $callpsreq = "CALL `getRequete`(?,?,?,?);";

      $gettype = 'SELECT TYPE_SALAIRE_ID,DESC_TYPE_SALAIRE FROM type_salairie ORDER BY TYPE_SALAIRE_ID ASC';
      $gettype = "CALL `getTable`('" . $gettype . "');";
      $data['type'] = $this->ModelPs->getRequete($gettype);

      $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE 1 ORDER BY MOIS_ID ASC';
      $get_mois = "CALL `getTable`('" . $get_mois . "');";
      $data['get_mois']= $this->ModelPs->getRequete($get_mois);

      $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire WHERE 1 ORDER BY CATEGORIE_SALAIRE_ID ASC';
      $getCateg = "CALL `getTable`('" . $getCateg . "');";
      $data['getCateg'] = $this->ModelPs->getRequete($getCateg);
      
      $nbr_paiement=$this->count_paiement_salaire();
      $data['nbr_prise_charge_salaire']=$nbr_paiement['nbr_prise_charge_salaire'];
      $data['sign_dir_compt']=$nbr_paiement['sign_dir_compt'];
      $data['sign_min']=$nbr_paiement['sign_min'];
      $data['sign_dgfp']=$nbr_paiement['sign_dgfp'];
      $data['valid_td_net']=$nbr_paiement['valid_td_net'];
      $data['valid_td_autr_ret']=$nbr_paiement['valid_td_autr_ret'];
      $data['nbre_td_net']=$nbr_paiement['nbre_td_net'];
      $data['nbre_td_autr_ret']=$nbr_paiement['nbre_td_autr_ret'];

      return view('App\Modules\double_commande_new\Views\Paiement_Salaire_Prise_Charge_View', $data);
    }

    //fonction pour affichage d'une liste de prise en charge
    public function listing_prise_charge()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_PRISE_CHARGE_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 19;

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $TYPE_SALAIRE_ID=$this->request->getPost('TYPE_SALAIRE_ID');
      $CATEGORIE_SALAIRE_ID=$this->request->getPost('CATEGORIE_SALAIRE_ID');
      $MOIS_ID=$this->request->getPost('MOIS_ID');
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $critere5 = "";

      if(!empty($TYPE_SALAIRE_ID))
      {
        $critere1 .=" AND exec.TYPE_SALAIRE_ID=".$TYPE_SALAIRE_ID;
      }

      if(!empty($CATEGORIE_SALAIRE_ID))
      {
        $critere2 .=" AND exec.CATEGORIE_SALAIRE_ID=".$CATEGORIE_SALAIRE_ID;
      }

      if(!empty($MOIS_ID))
      {
        $critere3 .=" AND exec.MOIS_ID=".$MOIS_ID;
      }

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND exec_detail.DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
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

      $order_column = array('ANNEE_DESCRIPTION','DESC_MOIS','DESC_TYPE_SALAIRE','DESC_CATEGORIE_SALAIRE','exec.LIQUIDATION','exec.ORDONNANCEMENT','');

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESC_TYPE_SALAIRE ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (ANNEE_DESCRIPTION LIKE '%$var_search%'  OR ANNEE_DESCRIPTION LIKE '%$var_search%' OR DESC_TYPE_SALAIRE LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%' )") : '';

       $requetedebase = "SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,ANNEE_DESCRIPTION,td.COMPTE_CREDIT,td.MONTANT_DECAISSEMENT,exec.ORDONNANCEMENT,exec.LIQUIDATION,DESC_MOIS,DESC_TYPE_SALAIRE,DESC_CATEGORIE_SALAIRE FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID LEFT JOIN annee_budgetaire on annee_budgetaire.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID LEFT JOIN mois ON mois.MOIS_ID=exec.MOIS_ID LEFT JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID = exec.TYPE_SALAIRE_ID  LEFT JOIN categorie_salaire ON categorie_salaire.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 ";

      $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

      $critaire = $critere1 . " " . $critere2. " " . $critere3. " " . $critere4. " " . $critere5;
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
        $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm.' AND IS_SALAIRE=1';
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
        $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
        $dist = "";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        $number = $row->TITRE_DECAISSEMENT;
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";

              $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
            }
          }
        }
        // Montant de decaissement
        $MONTANT = $row->MONTANT_DECAISSEMENT;
        $LIQUIDATION = $row->LIQUIDATION;
        $ORDONNANCEMENT = $row->ORDONNANCEMENT;

        $sub_array = array();
        $sub_array[] = $row->ANNEE_DESCRIPTION;
        $sub_array[] = $row->DESC_MOIS;
        $sub_array[] = $row->DESC_TYPE_SALAIRE;
        $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
        $sub_array[] = number_format($LIQUIDATION,$this->get_precision($LIQUIDATION),",", " ");
        
        $sub_array[] = number_format($ORDONNANCEMENT,$this->get_precision($ORDONNANCEMENT),",", " ");


        $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
        '" . $bouton . "' ";

        $sub_array[] = $action1;
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

    //fonction pour l'export excel de prise en charge
    public function exporter_Excel_prise_charge($TYPE_SALAIRE_ID=0,$CATEGORIE_SALAIRE_ID=0,$MOIS_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_PRISE_CHARGE_SALAIRE');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/homepage');
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 19;
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1=" ";
      $critere2=" ";
      $critere3=" ";
      $critere4=" ";
      $critere5=" ";
      if($TYPE_SALAIRE_ID>0)
      {
        $critere1 .=" AND exec.TYPE_SALAIRE_ID=".$TYPE_SALAIRE_ID;
      }

      if($CATEGORIE_SALAIRE_ID>0)
      {
        $critere2 .=" AND exec.CATEGORIE_SALAIRE_ID=".$CATEGORIE_SALAIRE_ID;
      }

      if($MOIS_ID>0)
      {
        $critere3 .=" AND exec.MOIS_ID=".$MOIS_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND exec_detail.DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
      }

      $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";
      $requetedebase="SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,ANNEE_DESCRIPTION,td.COMPTE_CREDIT,td.MONTANT_DECAISSEMENT,exec.ORDONNANCEMENT,exec.LIQUIDATION,DESC_MOIS,DESC_TYPE_SALAIRE,DESC_CATEGORIE_SALAIRE FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID LEFT JOIN annee_budgetaire on annee_budgetaire.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID LEFT JOIN mois ON mois.MOIS_ID=exec.MOIS_ID LEFT JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID = exec.TYPE_SALAIRE_ID  LEFT JOIN categorie_salaire ON categorie_salaire.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 ".$critere1.$critere2.$critere3.$critere4.$critere5.$group;

      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('C1', 'PRISE EN CHARGE DES SALAIRES');
      $sheet->setCellValue('A3', 'ANNEE BUDGETAIRE');
      $sheet->setCellValue('B3', 'MOIS');
      $sheet->setCellValue('C3', 'TYPE SALARIE');
      $sheet->setCellValue('D3', 'CATEGORIE SALARIE');
      $sheet->setCellValue('E3', 'LIQUIDATION');
      $sheet->setCellValue('F3', 'ORDONNANCEMENT');

      $rows = 4;
      $i=1;
      foreach ($getData as $key)
      {
        $LIQUIDATION = $key->LIQUIDATION;
        $ORDONNANCEMENT = $key->ORDONNANCEMENT;

        $sheet->setCellValue('A' . $rows, $key->ANNEE_DESCRIPTION);
        $sheet->setCellValue('B' . $rows, $key->DESC_MOIS);
        $sheet->setCellValue('C' . $rows, $key->DESC_TYPE_SALAIRE);
        $sheet->setCellValue('D' . $rows, $key->DESC_CATEGORIE_SALAIRE);
        $sheet->setCellValue('E' . $rows, number_format($LIQUIDATION,$this->get_precision($LIQUIDATION),",", " "));
        $sheet->setCellValue('F' . $rows, number_format($ORDONNANCEMENT,$this->get_precision($ORDONNANCEMENT),",", " "));
       
        $rows++;
        $i++;
      }

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('prise_en_charge_des_salaires'.$code.'.xlsx');

      return redirect('double_commande_new/Paiement_Salaire_Liste/vue_prise_charge');
    }

    //vue liste signature par le directeur de la comptabilité
    function vue_sign_dir_compt()
    {
      $session  = \Config\Services::session();
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if (empty($user_id)) {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DIR_COMPT_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $nbr_paiement=$this->count_paiement_salaire();
      $data['nbr_prise_charge_salaire']=$nbr_paiement['nbr_prise_charge_salaire'];
      $data['sign_dir_compt']=$nbr_paiement['sign_dir_compt'];
      $data['sign_min']=$nbr_paiement['sign_min'];
      $data['sign_dgfp']=$nbr_paiement['sign_dgfp'];
      $data['valid_td_net']=$nbr_paiement['valid_td_net'];
      $data['valid_td_autr_ret']=$nbr_paiement['valid_td_autr_ret'];

      $data['nbre_td_net']=$nbr_paiement['nbre_td_net'];
      $data['nbre_td_autr_ret']=$nbr_paiement['nbre_td_autr_ret'];

      return view('App\Modules\double_commande_new\Views\Liste_Sign_Dir_Compt_View_Salaire', $data);
    }

    //fonction pour le listing signature directeur de la comptabilité
    public function listing_sign_dir_compt()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DIR_COMPT_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $ETAPE_DOUBLE_COMMANDE_ID = 23;

      // Filtres de la liste
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $critere5 = "";

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
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

      $order_column = array('td.TITRE_DECAISSEMENT','benef.DESC_BENEFICIAIRE','td.COMPTE_CREDIT','td.MONTANT_DECAISSEMENT');

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.TITRE_DECAISSEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (td.TITRE_DECAISSEMENT LIKE '%$var_search%'  OR benef.DESC_BENEFICIAIRE LIKE '%$var_search%' OR td.COMPTE_CREDIT LIKE '%$var_search%' OR td.MONTANT_PAIEMENT LIKE '%$var_search%' )") : '';

      $requetedebase = "SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT,td.MONTANT_PAIEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3";

      $group = "";

      $critaire = $critere1 . " " . $critere2 . " " . $critere3 . " " . $critere4 . " " . $critere5;
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
        $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm.' AND IS_SALAIRE=1';
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
        $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
        $dist = "";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        $number = $row->TITRE_DECAISSEMENT;
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";

              $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
            }
          }
        }
        // Montant de decaissement
        $MONTANT = $row->MONTANT_PAIEMENT;
      
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->DESC_BENEFICIAIRE;
        $sub_array[] = $row->COMPTE_CREDIT;

        $sub_array[] = number_format($MONTANT, $this->get_precision($MONTANT), ",", " ");
       

        $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
          '" . $bouton . "' ";

        $sub_array[] = $action1;
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

    //fonction pour l'export excel de Signature td par le Directeur de la comptabilité
    public function exporter_Excel_sign_dir_compt($DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DIR_COMPT_SALAIRE');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/homepage');
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 23;
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1=" ";
      $critere2=" ";
      $critere3=" ";
      $critere4=" ";
      $critere5=" ";
      
      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND exec_detail.DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
      }

      $group = "";
      $requetedebase="SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT,td.MONTANT_PAIEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3".$critere4.$critere5.$group;

      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('C1', 'SIGNATURE SUR LE TD PAR LE DIRECTEUR DE LA COMPTABILITE');
      $sheet->setCellValue('A3', 'TITRE DECAISSEMENT');
      $sheet->setCellValue('B3', 'BENEFICIAIRE');
      $sheet->setCellValue('C3', 'COMPTE BANCAIRE');
      $sheet->setCellValue('D3', 'MONTANT');

      $rows = 4;
      $i=1;
      foreach ($getData as $key)
      {
        $MONTANT = $key->MONTANT_PAIEMENT;

        $sheet->setCellValue('A' . $rows, $key->TITRE_DECAISSEMENT);
        $sheet->setCellValue('B' . $rows, $key->DESC_BENEFICIAIRE);
        $sheet->setCellValue('C' . $rows, $key->COMPTE_CREDIT);
        $sheet->setCellValue('D' . $rows, number_format($MONTANT,$this->get_precision($MONTANT),",", " "));
       
        $rows++;
        $i++;
      }

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('signature_td_directeur_comptabilite'.$code.'.xlsx');

      return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_dir_compt');
    }

    //vue liste signature par DGFP
    function vue_sign_dgfp()
    {
      $session  = \Config\Services::session();
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if (empty($user_id)) {
        return redirect('Login_Ptba/do_logout');
      }

      if($this->session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DGFP_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $nbr_paiement=$this->count_paiement_salaire();
      $data['nbr_prise_charge_salaire']=$nbr_paiement['nbr_prise_charge_salaire'];
      $data['sign_dir_compt']=$nbr_paiement['sign_dir_compt'];
      $data['sign_min']=$nbr_paiement['sign_min'];
      $data['sign_dgfp']=$nbr_paiement['sign_dgfp'];
      $data['valid_td_net']=$nbr_paiement['valid_td_net'];
      $data['valid_td_autr_ret']=$nbr_paiement['valid_td_autr_ret'];
       $data['nbre_td_net']=$nbr_paiement['nbre_td_net'];
      $data['nbre_td_autr_ret']=$nbr_paiement['nbre_td_autr_ret'];

      return view('App\Modules\double_commande_new\Views\Liste_Sign_DGFP_View_Salaire', $data);
    }

    //fonction pour le listing signature dgfp
    public function listing_sign_dgfp()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DGFP_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $ETAPE_DOUBLE_COMMANDE_ID = 24;

      // Filtres de la liste
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $critere5 = "";

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
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

      $order_column = array('TITRE_DECAISSEMENT','benef.DESC_BENEFICIAIRE','td.COMPTE_CREDIT','td.MONTANT_DECAISSEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY TITRE_DECAISSEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.TITRE_DECAISSEMENT LIKE '%$var_search%' OR DESC_BENEFICIAIRE LIKE '%$var_search%' OR td.COMPTE_CREDIT LIKE '%$var_search%' )") : '';

      $requetedebase = "SELECT DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_PAIEMENT,td.MONTANT_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3";

      $group = "";

      $critaire = $critere4 . " " . $critere5;
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
        $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm.' AND IS_SALAIRE=1';
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
        $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
        $dist = "";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        $number = $row->TITRE_DECAISSEMENT;
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";

              $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
            }
          }
        }

        // Montant de decaissement
        $MONTANT = $row->MONTANT_PAIEMENT;
       
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->DESC_BENEFICIAIRE;
        $sub_array[] = $row->COMPTE_CREDIT;
        $sub_array[] = number_format($MONTANT, $this->get_precision($MONTANT), ",", " ");
      
        $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
          '" . $bouton . "' ";
        $sub_array[] = $action1;
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

    //fonction pour l'export excel de Signature td par le DGFP
    public function exporter_Excel_sign_DGFP($DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DGFP_SALAIRE');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/homepage');
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 24;
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1=" ";
      $critere2=" ";
      $critere3=" ";
      $critere4=" ";
      $critere5=" ";
      
      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

      $group = "";
      $requetedebase="SELECT DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_PAIEMENT,td.MONTANT_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3".$critere4.$critere5.$group;

      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('C1', 'SIGNATURE SUR LE TD PAR LE DGFP');
      $sheet->setCellValue('A3', 'TITRE DECAISSEMENT');
      $sheet->setCellValue('B3', 'BENEFICIAIRE');
      $sheet->setCellValue('C3', 'COMPTE BANCAIRE');
      $sheet->setCellValue('D3', 'MONTANT');

      $rows = 4;
      $i=1;
      foreach ($getData as $key)
      {
        $MONTANT = $key->MONTANT_PAIEMENT;

        $sheet->setCellValue('A' . $rows, $key->TITRE_DECAISSEMENT);
        $sheet->setCellValue('B' . $rows, $key->DESC_BENEFICIAIRE);
        $sheet->setCellValue('C' . $rows, $key->COMPTE_CREDIT);
        $sheet->setCellValue('D' . $rows, number_format($MONTANT,$this->get_precision($MONTANT),",", " "));
       
        $rows++;
        $i++;
      }

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('signature_td_DGFP'.$code.'.xlsx');

      return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_dgfp');
    }

    //vue liste signature par le ministre
    function vue_sign_ministre()
    {
      $session  = \Config\Services::session();
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if (empty($user_id)) {
        return redirect('Login_Ptba/do_logout');
      }

      if($this->session->get('SESSION_SUIVIE_PTBA_SIGNATURE_MIN_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $nbr_paiement=$this->count_paiement_salaire();
      $data['nbr_prise_charge_salaire']=$nbr_paiement['nbr_prise_charge_salaire'];
      $data['sign_dir_compt']=$nbr_paiement['sign_dir_compt'];
      $data['sign_min']=$nbr_paiement['sign_min'];
      $data['sign_dgfp']=$nbr_paiement['sign_dgfp'];
      $data['valid_td_net']=$nbr_paiement['valid_td_net'];
      $data['valid_td_autr_ret']=$nbr_paiement['valid_td_autr_ret'];

      $data['nbre_td_net']=$nbr_paiement['nbre_td_net'];
      $data['nbre_td_autr_ret']=$nbr_paiement['nbre_td_autr_ret'];

      return view('App\Modules\double_commande_new\Views\Liste_Sign_Ministre_View_Salaire', $data);
    }

    //fonction pour le listing signature ministre
    public function listing_sign_ministre()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_MIN_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $ETAPE_DOUBLE_COMMANDE_ID = 25;

      //Filtres de la liste
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $critere5 = "";

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
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

      $order_column = array('TITRE_DECAISSEMENT','benef.DESC_BENEFICIAIRE','COMPTE_CREDIT','MONTANT_DECAISSEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY TITRE_DECAISSEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (TITRE_DECAISSEMENT LIKE '%$var_search%' OR benef.DESC_BENEFICIAIRE LIKE '%$var_search%' OR COMPTE_CREDIT LIKE '%$var_search%' OR MONTANT_DECAISSEMENT LIKE '%$var_search%')") : '';

      $requetedebase = "SELECT DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_PAIEMENT,td.MONTANT_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 ";

      $group = "";

      $critaire = $critere4 . " " . $critere5;
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
        $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm.' AND IS_SALAIRE=1';
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
        $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
        $dist = "";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        $number = $row->TITRE_DECAISSEMENT;
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";

              $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
            }
          }
        }

        $MONTANT = $row->MONTANT_PAIEMENT;

        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->DESC_BENEFICIAIRE;
        $sub_array[] = $row->COMPTE_CREDIT;
        $sub_array[] = number_format($MONTANT, $this->get_precision($MONTANT), ",", " ");

        $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
          '" . $bouton . "'";
        $sub_array[] = $action1;
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

    //fonction pour l'export excel de Signature td par le Ministre
    public function exporter_Excel_sign_Ministre($DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_SIGNATURE_MIN_SALAIRE');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/homepage');
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 25;
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1=" ";
      $critere2=" ";
      $critere3=" ";
      $critere4=" ";
      $critere5=" ";
      
      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

      $group = "";
      $requetedebase="SELECT DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_PAIEMENT,td.MONTANT_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3".$critere4.$critere5.$group;

      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('C1', 'SIGNATURE SUR LE TD PAR LE MINISTRE');
      $sheet->setCellValue('A3', 'TITRE DECAISSEMENT');
      $sheet->setCellValue('B3', 'BENEFICIAIRE');
      $sheet->setCellValue('C3', 'COMPTE BANCAIRE');
      $sheet->setCellValue('D3', 'MONTANT');

      $rows = 4;
      $i=1;
      foreach ($getData as $key)
      {
        $MONTANT = $key->MONTANT_PAIEMENT;

        $sheet->setCellValue('A' . $rows, $key->TITRE_DECAISSEMENT);
        $sheet->setCellValue('B' . $rows, $key->DESC_BENEFICIAIRE);
        $sheet->setCellValue('C' . $rows, $key->COMPTE_CREDIT);
        $sheet->setCellValue('D' . $rows, number_format($MONTANT,$this->get_precision($MONTANT),",", " "));
       
        $rows++;
        $i++;
      }

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('signature_td_ministre'.$code.'.xlsx');

      return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_ministre');
    }

    //vue liste validation TD Net
    function vue_valide_td_net()
    {
      $session  = \Config\Services::session();
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('VALIDATION_SALAIRE_NET');
      if (empty($user_id)) {
        return redirect('Login_Ptba/do_logout');
      }

      if($this->session->get('SESSION_SUIVIE_PTBA_VALIDATION_SALAIRE_NET') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $nbr_paiement=$this->count_paiement_salaire();
      $data['nbr_prise_charge_salaire']=$nbr_paiement['nbr_prise_charge_salaire'];
      $data['sign_dir_compt']=$nbr_paiement['sign_dir_compt'];
      $data['sign_min']=$nbr_paiement['sign_min'];
      $data['sign_dgfp']=$nbr_paiement['sign_dgfp'];
      $data['valid_td_net']=$nbr_paiement['valid_td_net'];
      $data['valid_td_autr_ret']=$nbr_paiement['valid_td_autr_ret'];
      $data['nbre_td_net']=$nbr_paiement['nbre_td_net'];
      $data['nbre_td_autr_ret']=$nbr_paiement['nbre_td_autr_ret'];

      return view('App\Modules\double_commande_new\Views\Validation_TD_Salaire_Liste_View_Net', $data);
    }

    //fonction pour le listing liste validation TD Net
    public function listing_valide_td_net()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_VALIDATION_SALAIRE_NET') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $ETAPE_DOUBLE_COMMANDE_ID = 26;

      //Filtres de la liste
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $critere5 = "";

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
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

      $order_column = array('TITRE_DECAISSEMENT','DESC_BENEFICIAIRE','COMPTE_CREDIT','MONTANT_PAIEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY TITRE_DECAISSEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (TITRE_DECAISSEMENT LIKE '%$var_search%' OR DESC_BENEFICIAIRE LIKE '%$var_search%' OR COMPTE_CREDIT LIKE '%$var_search%' OR MONTANT_PAIEMENT LIKE '%$var_search%')") : '';

      $requetedebase = "SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_PAIEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=0";

      $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

      $critaire = $critere4 . " " . $critere5;
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
        $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm.' AND IS_SALAIRE=1';
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
        $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
        $dist = "";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        $number = $row->TITRE_DECAISSEMENT;
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";

              $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
            }
          }
        }

        $MONTANT = $row->MONTANT_PAIEMENT;
  
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->DESC_BENEFICIAIRE;
        $sub_array[] = $row->COMPTE_CREDIT;
        $sub_array[] = number_format($MONTANT, $this->get_precision($MONTANT), ",", " ");
        $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
          '" . $bouton . "'";
        $sub_array[] = $action1;
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

    //fonction pour l'export excel de validation du td salaire net
    public function exporter_Excel_validate_td_salaire_net($DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_VALIDATION_SALAIRE_NET');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/homepage');
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 26;
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1=" ";
      $critere2=" ";
      $critere3=" ";
      $critere4=" ";
      $critere5=" ";
      
      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

      $group = "";
      $requetedebase="SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_PAIEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=0".$critere4.$critere5.$group;

      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('C1', 'VALIDATION TD SALAIRE NET');
      $sheet->setCellValue('A3', 'TITRE DECAISSEMENT');
      $sheet->setCellValue('B3', 'BENEFICIAIRE');
      $sheet->setCellValue('C3', 'COMPTE BANCAIRE');
      $sheet->setCellValue('D3', 'MONTANT');

      $rows = 4;
      $i=1;
      foreach ($getData as $key)
      {
        $MONTANT = $key->MONTANT_PAIEMENT;

        $sheet->setCellValue('A' . $rows, $key->TITRE_DECAISSEMENT);
        $sheet->setCellValue('B' . $rows, $key->DESC_BENEFICIAIRE);
        $sheet->setCellValue('C' . $rows, $key->COMPTE_CREDIT);
        $sheet->setCellValue('D' . $rows, number_format($MONTANT,$this->get_precision($MONTANT),",", " "));
       
        $rows++;
        $i++;
      }

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('validation_td_salaire_net'.$code.'.xlsx');

      return redirect('double_commande_new/Paiement_Salaire_Liste/vue_valide_td_net');
    }

    //vue liste validation TD autre retenu
    function vue_valide_td_autre_retenu()
    {
      $session  = \Config\Services::session();
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if (empty($user_id)) {
        return redirect('Login_Ptba/do_logout');
      }

      if($this->session->get('SESSION_SUIVIE_PTBA_VALIDATION_RETENUS_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $nbr_paiement=$this->count_paiement_salaire();
      $data['nbr_prise_charge_salaire']=$nbr_paiement['nbr_prise_charge_salaire'];
      $data['sign_dir_compt']=$nbr_paiement['sign_dir_compt'];
      $data['sign_min']=$nbr_paiement['sign_min'];
      $data['sign_dgfp']=$nbr_paiement['sign_dgfp'];
      $data['valid_td_net']=$nbr_paiement['valid_td_net'];
      $data['valid_td_autr_ret']=$nbr_paiement['valid_td_autr_ret'];
      $data['nbre_td_net']=$nbr_paiement['nbre_td_net'];
      $data['nbre_td_autr_ret']=$nbr_paiement['nbre_td_autr_ret'];

      return view('App\Modules\double_commande_new\Views\Validation_TD_Salaire_Liste_View_Autre_Retenu', $data);
    }

    //fonction pour le listing liste validation TD autre retenu
    public function listing_valide_td_autre_retenu()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_VALIDATION_RETENUS_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $ETAPE_DOUBLE_COMMANDE_ID = 26;

      //Filtres de la liste
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $critere5 = "";

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
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

      $order_column = array('TITRE_DECAISSEMENT','DESC_BENEFICIAIRE','COMPTE_CREDIT','MONTANT_PAIEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY TITRE_DECAISSEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (TITRE_DECAISSEMENT LIKE '%$var_search%' OR DESC_BENEFICIAIRE LIKE '%$var_search%' OR MONTANT_PAIEMENT LIKE '%$var_search%' OR COMPTE_CREDIT LIKE '%$var_search%' )") : '';

      $requetedebase = "SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_PAIEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=1";

      $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

      $critaire = $critere4 . " " . $critere5;
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
        $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm.' AND IS_SALAIRE=1 ORDER BY ETAPE_DOUBLE_COMMANDE_CONFIG_ID DESC';
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
        $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
        $dist = "";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        $number = $row->TITRE_DECAISSEMENT;
        $bouton = '';
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/Validation_TD_Salaire/vue_valid_titre_autre'. "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";

              $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/Validation_TD_Salaire/vue_valid_titre_autre'. "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
            }
          }
        }

        $MONTANT = $row->MONTANT_PAIEMENT;
    
        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = $row->DESC_BENEFICIAIRE;
        $sub_array[] = $row->COMPTE_CREDIT;
        $sub_array[] = number_format($MONTANT, $this->get_precision($MONTANT), ",", " ");

        $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
          '" . $bouton . "'";
        $sub_array[] = $action1;
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

    //fonction pour l'export excel de validation du td salaire autre retenu
    public function exporter_Excel_validate_td_salaire_autre_retenu($DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_VALIDATION_RETENUS_SALAIRE');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/homepage');
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 26;
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1=" ";
      $critere2=" ";
      $critere3=" ";
      $critere4=" ";
      $critere5=" ";
      
      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_ELABORATION_TD >= '".$DATE_DEBUT."' AND td.DATE_ELABORATION_TD <= '".$DATE_FIN."'";
      }

      $group = "";
      $requetedebase="SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_PAIEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=1".$critere4.$critere5.$group;

      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('C1', 'VALIDATION TD SALAIRE AUTRES RETENUS');
      $sheet->setCellValue('A3', 'TITRE DECAISSEMENT');
      $sheet->setCellValue('B3', 'BENEFICIAIRE');
      $sheet->setCellValue('C3', 'COMPTE BANCAIRE');
      $sheet->setCellValue('D3', 'MONTANT');

      $rows = 4;
      $i=1;
      foreach ($getData as $key)
      {
        $MONTANT = $key->MONTANT_PAIEMENT;

        $sheet->setCellValue('A' . $rows, $key->TITRE_DECAISSEMENT);
        $sheet->setCellValue('B' . $rows, $key->DESC_BENEFICIAIRE);
        $sheet->setCellValue('C' . $rows, $key->COMPTE_CREDIT);
        $sheet->setCellValue('D' . $rows, number_format($MONTANT,$this->get_precision($MONTANT),",", " "));
       
        $rows++;
        $i++;
      }

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('validation_td_salaire_autre_retenus'.$code.'.xlsx');

      return redirect('double_commande_new/Paiement_Salaire_Liste/vue_valide_td_autre_retenu');
    }

    //vue de la liste Etablissement  de decaissement pour le cas des salaires nets
    function vue_td_Salaire_Net()
    {
      $session  = \Config\Services::session();
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_NET')!=1){
        return redirect('Login_Ptba/homepage');
      }
      $data['element']='';
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      
      $nbr_paiement=$this->count_paiement_salaire();
      $data['nbr_prise_charge_salaire']=$nbr_paiement['nbr_prise_charge_salaire'];
      $data['sign_dir_compt']=$nbr_paiement['sign_dir_compt'];
      $data['sign_min']=$nbr_paiement['sign_min'];
      $data['sign_dgfp']=$nbr_paiement['sign_dgfp'];
      $data['valid_td_net']=$nbr_paiement['valid_td_net'];
      $data['valid_td_autr_ret']=$nbr_paiement['valid_td_autr_ret'];

      $data['nbre_td_net']=$nbr_paiement['nbre_td_net'];
      $data['nbre_td_autr_ret']=$nbr_paiement['nbre_td_autr_ret'];

      $gettype = 'SELECT TYPE_SALAIRE_ID,DESC_TYPE_SALAIRE FROM type_salairie ORDER BY TYPE_SALAIRE_ID ASC';
      $gettype = "CALL `getTable`('" . $gettype . "');";
      $data['type'] = $this->ModelPs->getRequete($gettype);

      $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE 1 ORDER BY MOIS_ID ASC';
      $get_mois = "CALL `getTable`('" . $get_mois . "');";
      $data['get_mois']= $this->ModelPs->getRequete($get_mois);

      $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire WHERE 1 ORDER BY CATEGORIE_SALAIRE_ID ASC';
      $getCateg = "CALL `getTable`('" . $getCateg . "');";
      $data['getCateg'] = $this->ModelPs->getRequete($getCateg);

      return view('App\Modules\double_commande_new\Views\Paiement_Salaire_TD_Salaire_Net_View',$data);
    }

    function listing_td_Salaire_Net()
    {
      $session  = \Config\Services::session();

      if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_NET') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 20;

      // Filtres de la liste
      $TYPE_SALAIRE_ID=$this->request->getPost('TYPE_SALAIRE_ID');
      $CATEGORIE_SALAIRE_ID=$this->request->getPost('CATEGORIE_SALAIRE_ID');
      $MOIS_ID=$this->request->getPost('MOIS_ID');
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $critere5 = "";

      if(!empty($TYPE_SALAIRE_ID))
      {
        $critere1 .=" AND exec.TYPE_SALAIRE_ID=".$TYPE_SALAIRE_ID;
      }

      if(!empty($CATEGORIE_SALAIRE_ID))
      {
        $critere2 .=" AND exec.CATEGORIE_SALAIRE_ID=".$CATEGORIE_SALAIRE_ID;
      }

      if(!empty($MOIS_ID))
      {
        $critere3 .=" AND exec.MOIS_ID=".$MOIS_ID;
      }

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND exec_detail.DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
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

      $order_column = array('ANNEE_DESCRIPTION','DESC_MOIS','DESC_TYPE_SALAIRE','DESC_CATEGORIE_SALAIRE','exec.LIQUIDATION','exec.ORDONNANCEMENT','');

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ANNEE_DESCRIPTION ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (ANNEE_DESCRIPTION LIKE '%$var_search%'  OR DESC_MOIS LIKE '%$var_search%' OR DESC_TYPE_SALAIRE LIKE '%$var_search%' OR exec.ORDONNANCEMENT LIKE '%$var_search%' OR exec.LIQUIDATION LIKE '%$var_search%' )") : '';

      $requetedebase = "SELECT DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.MONTANT_DECAISSEMENT,exec.ORDONNANCEMENT,exec.LIQUIDATION, exec.EXECUTION_BUDGETAIRE_ID, ANNEE_DESCRIPTION, DESC_MOIS ,DESC_TYPE_SALAIRE,DESC_CATEGORIE_SALAIRE  FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID LEFT JOIN annee_budgetaire on annee_budgetaire.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID LEFT JOIN mois ON mois.MOIS_ID=exec.MOIS_ID LEFT JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID = exec.TYPE_SALAIRE_ID  LEFT JOIN categorie_salaire ON categorie_salaire.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=0";

      $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";

      $critaire = $critere1 . " " . $critere2. " " . $critere3. " " . $critere4. " " . $critere5;
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
        $et_db_comm = ($row->ETAPE_DOUBLE_COMMANDE_ID) ? ($row->ETAPE_DOUBLE_COMMANDE_ID) : 0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm.' AND IS_SALAIRE=1';
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
        $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
        $dist = "";

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        $number = $row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . "</a>";

              $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
            }
          }
        }
        // Montant de decaissement
        $MONTANT = $row->MONTANT_DECAISSEMENT;
        $LIQUIDATION = $row->LIQUIDATION;
        $ORDONNANCEMENT = $row->ORDONNANCEMENT;

        $sub_array = array();
        $sub_array[] = $row->ANNEE_DESCRIPTION;
         $sub_array[] = $row->DESC_MOIS;
         $sub_array[] = $row->DESC_TYPE_SALAIRE;
         $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
        $sub_array[] = number_format($LIQUIDATION,$this->get_precision($LIQUIDATION),",", " ");
        $sub_array[] = number_format($ORDONNANCEMENT,$this->get_precision($ORDONNANCEMENT),",", " ");

        $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
        '" . $bouton . "' ";

        $sub_array[] = $action1;
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

    //fonction pour l'export excel td salaire net etablissement
    public function exporter_Excel_td_salaire_net($TYPE_SALAIRE_ID=0,$CATEGORIE_SALAIRE_ID=0,$MOIS_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_NET');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/homepage');
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 20;
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1=" ";
      $critere2=" ";
      $critere3=" ";
      $critere4=" ";
      $critere5=" ";
      if($TYPE_SALAIRE_ID>0)
      {
        $critere1 .=" AND exec.TYPE_SALAIRE_ID=".$TYPE_SALAIRE_ID;
      }

      if($CATEGORIE_SALAIRE_ID>0)
      {
        $critere2 .=" AND exec.CATEGORIE_SALAIRE_ID=".$CATEGORIE_SALAIRE_ID;
      }

      if($MOIS_ID>0)
      {
        $critere3 .=" AND exec.MOIS_ID=".$MOIS_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND exec_detail.DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
      }

      $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";
      $requetedebase="SELECT DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.MONTANT_DECAISSEMENT,exec.ORDONNANCEMENT,exec.LIQUIDATION, exec.EXECUTION_BUDGETAIRE_ID, ANNEE_DESCRIPTION, DESC_MOIS ,DESC_TYPE_SALAIRE,DESC_CATEGORIE_SALAIRE  FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID LEFT JOIN annee_budgetaire on annee_budgetaire.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID LEFT JOIN mois ON mois.MOIS_ID=exec.MOIS_ID LEFT JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID = exec.TYPE_SALAIRE_ID  LEFT JOIN categorie_salaire ON categorie_salaire.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=0".$critere1.$critere2.$critere3.$critere4.$critere5.$group;

      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('C1', 'ETABLISSEMENT DU TITRE DE DECAISSEMENT');
      $sheet->setCellValue('A3', 'ANNEE BUDGETAIRE');
      $sheet->setCellValue('B3', 'MOIS');
      $sheet->setCellValue('C3', 'TYPE SALARIE');
      $sheet->setCellValue('D3', 'CATEGORIE SALARIE');
      $sheet->setCellValue('E3', 'LIQUIDATION');
      $sheet->setCellValue('F3', 'ORDONNANCEMENT');

      $rows = 4;
      $i=1;
      foreach ($getData as $key)
      {
        $LIQUIDATION = $key->LIQUIDATION;
        $ORDONNANCEMENT = $key->ORDONNANCEMENT;

        $sheet->setCellValue('A' . $rows, $key->ANNEE_DESCRIPTION);
        $sheet->setCellValue('B' . $rows, $key->DESC_MOIS);
        $sheet->setCellValue('C' . $rows, $key->DESC_TYPE_SALAIRE);
        $sheet->setCellValue('D' . $rows, $key->DESC_CATEGORIE_SALAIRE);
        $sheet->setCellValue('E' . $rows, number_format($LIQUIDATION,$this->get_precision($LIQUIDATION),",", " "));
        $sheet->setCellValue('F' . $rows, number_format($ORDONNANCEMENT,$this->get_precision($ORDONNANCEMENT),",", " "));
       
        $rows++;
        $i++;
      }

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('etablissement_du_titre_de_décaissement_net_salaire'.$code.'.xlsx');

      return redirect('double_commande_new/Paiement_Salaire_Liste/vue_td_Salaire_Net');
    }

    //vue de la liste Etablissement  de decaissement pour le cas des salaires nets
    function vue_td_Autres_Retenus()
    {
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }
        if($this->session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_RETENUS') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $data['element']='';
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      
      $nbr_paiement=$this->count_paiement_salaire();
      $data['nbr_prise_charge_salaire']=$nbr_paiement['nbr_prise_charge_salaire'];
      $data['sign_dir_compt']=$nbr_paiement['sign_dir_compt'];
      $data['sign_min']=$nbr_paiement['sign_min'];
      $data['sign_dgfp']=$nbr_paiement['sign_dgfp'];
      $data['valid_td_net']=$nbr_paiement['valid_td_net'];
      $data['valid_td_autr_ret']=$nbr_paiement['valid_td_autr_ret'];
      $data['nbre_td_net']=$nbr_paiement['nbre_td_net'];
      $data['nbre_td_autr_ret']=$nbr_paiement['nbre_td_autr_ret'];

      $gettype = 'SELECT TYPE_SALAIRE_ID,DESC_TYPE_SALAIRE FROM type_salairie ORDER BY TYPE_SALAIRE_ID ASC';
      $gettype = "CALL `getTable`('" . $gettype . "');";
      $data['type'] = $this->ModelPs->getRequete($gettype);

      $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE 1 ORDER BY MOIS_ID ASC';
      $get_mois = "CALL `getTable`('" . $get_mois . "');";
      $data['get_mois']= $this->ModelPs->getRequete($get_mois);

      $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire WHERE 1 ORDER BY CATEGORIE_SALAIRE_ID ASC';
      $getCateg = "CALL `getTable`('" . $getCateg . "');";
      $data['getCateg'] = $this->ModelPs->getRequete($getCateg);

      return view('App\Modules\double_commande_new\Views\Paiement_Salaire_TD_Autres_Retenus_View',$data);
    }

    function listing_td_Autre_Retenu()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_RETENUS') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 20;
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      // Filtres de la liste
      $TYPE_SALAIRE_ID=$this->request->getPost('TYPE_SALAIRE_ID');
      $CATEGORIE_SALAIRE_ID=$this->request->getPost('CATEGORIE_SALAIRE_ID');
      $MOIS_ID=$this->request->getPost('MOIS_ID');
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID IN (SELECT PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil WHERE ETAPE_DOUBLE_COMMANDE_ID =20)";
      }
      $critere1 = "";
      $critere2 = ""; 
      $critere3 = "";
      $critere4 = "";
      $critere5 = "";

      if(!empty($TYPE_SALAIRE_ID))
      {
        $critere1 .=" AND exec.TYPE_SALAIRE_ID=".$TYPE_SALAIRE_ID;
      }

      if(!empty($CATEGORIE_SALAIRE_ID))
      {
        $critere2 .=" AND exec.CATEGORIE_SALAIRE_ID=".$CATEGORIE_SALAIRE_ID;
      }

      if(!empty($MOIS_ID))
      {
        $critere3 .=" AND exec.MOIS_ID=".$MOIS_ID;
      }

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND exec_detail.DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
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
      $order_column = array('ANNEE_DESCRIPTION','DESC_MOIS','DESC_TYPE_SALAIRE','DESC_CATEGORIE_SALAIRE','LIQUIDATION','ORDONNANCEMENT',1);
      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESC_MOIS ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (td.ANNEE_DESCRIPTION LIKE '%$var_search%' OR DESC_TYPE_SALAIRE LIKE '%$var_search%' OR DESC_CATEGORIE_SALAIRE LIKE '%$var_search%' OR DESC_MOIS LIKE '%$var_search%' )") : '';

       $requetedebase = "SELECT DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.MONTANT_DECAISSEMENT,exec.ORDONNANCEMENT,exec.LIQUIDATION, exec.EXECUTION_BUDGETAIRE_ID, ANNEE_DESCRIPTION, DESC_MOIS ,DESC_TYPE_SALAIRE,DESC_CATEGORIE_SALAIRE  FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID LEFT JOIN annee_budgetaire on annee_budgetaire.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID LEFT JOIN mois ON mois.MOIS_ID=exec.MOIS_ID LEFT JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID = exec.TYPE_SALAIRE_ID  LEFT JOIN categorie_salaire ON categorie_salaire.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 AND td.ETAPE_DOUBLE_COMMANDE_ID=20 ".$cond_prof." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=1";

      $group = " group by td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

      $critaire = $critere1 . " " . $critere2 . " " . $critere3 . " " . $critere4 . " " . $critere5;
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
        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=20','PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        
        $bouton ='';
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/Phase_Comptable_Salaire/etablir_titre_retenu/'. md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
            }
          }
        }

        // Montant de decaissement
        $MONTANT = $row->MONTANT_DECAISSEMENT;
        $LIQUIDATION = $row->LIQUIDATION;
        $ORDONNANCEMENT = $row->ORDONNANCEMENT;

        $sub_array = array();
        $sub_array[] = $row->ANNEE_DESCRIPTION;
        $sub_array[] = $row->DESC_MOIS;
        $sub_array[] = $row->DESC_TYPE_SALAIRE;
        $sub_array[] = $row->DESC_CATEGORIE_SALAIRE;
        $sub_array[] = number_format($LIQUIDATION,$this->get_precision($LIQUIDATION),",", " ");
        
        $sub_array[] = number_format($ORDONNANCEMENT,$this->get_precision($ORDONNANCEMENT),",", " ");


        $action1 = "<div class='dropdown' data-toggle='tooltip' title='Traiter' style='color:#fff;'>
        '" . $bouton . "' ";

        $sub_array[] = $action1;
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

    //fonction pour l'export excel td salaire autre retenue etablissement
    public function exporter_Excel_td_autre_retenu($TYPE_SALAIRE_ID=0,$CATEGORIE_SALAIRE_ID=0,$MOIS_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_RETENUS');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/homepage');
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 20;
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID IN (SELECT PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil WHERE ETAPE_DOUBLE_COMMANDE_ID =20)";
      }

      $critere1=" ";
      $critere2=" ";
      $critere3=" ";
      $critere4=" ";
      $critere5=" ";

      if($TYPE_SALAIRE_ID>0)
      {
        $critere1 .=" AND exec.TYPE_SALAIRE_ID=".$TYPE_SALAIRE_ID;
      }

      if($CATEGORIE_SALAIRE_ID>0)
      {
        $critere2 .=" AND exec.CATEGORIE_SALAIRE_ID=".$CATEGORIE_SALAIRE_ID;
      }

      if($MOIS_ID>0)
      {
        $critere3 .=" AND exec.MOIS_ID=".$MOIS_ID;
      }

      if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
      {
        $critere4.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
      }

      if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
      {
        $critere5.=" AND exec_detail.DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND exec_detail.DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
      }

      $group = " GROUP BY exec.EXECUTION_BUDGETAIRE_ID";
      $requetedebase="SELECT DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.MONTANT_DECAISSEMENT,exec.ORDONNANCEMENT,exec.LIQUIDATION, exec.EXECUTION_BUDGETAIRE_ID, ANNEE_DESCRIPTION, DESC_MOIS ,DESC_TYPE_SALAIRE,DESC_CATEGORIE_SALAIRE  FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID LEFT JOIN annee_budgetaire on annee_budgetaire.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID LEFT JOIN mois ON mois.MOIS_ID=exec.MOIS_ID LEFT JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID = exec.TYPE_SALAIRE_ID  LEFT JOIN categorie_salaire ON categorie_salaire.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 AND td.ETAPE_DOUBLE_COMMANDE_ID=20 ".$cond_prof." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=1".$critere1.$critere2.$critere3.$critere4.$critere5.$group;

      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('C1', 'ETABLISSEMENT DU TITRE DE DECAISSEMENT');
      $sheet->setCellValue('A3', 'ANNEE BUDGETAIRE');
      $sheet->setCellValue('B3', 'MOIS');
      $sheet->setCellValue('C3', 'TYPE SALARIE');
      $sheet->setCellValue('D3', 'CATEGORIE SALARIE');
      $sheet->setCellValue('E3', 'LIQUIDATION');
      $sheet->setCellValue('F3', 'ORDONNANCEMENT');

      $rows = 4;
      $i=1;
      foreach ($getData as $key)
      {
        $LIQUIDATION = $key->LIQUIDATION;
        $ORDONNANCEMENT = $key->ORDONNANCEMENT;

        $sheet->setCellValue('A' . $rows, $key->ANNEE_DESCRIPTION);
        $sheet->setCellValue('B' . $rows, $key->DESC_MOIS);
        $sheet->setCellValue('C' . $rows, $key->DESC_TYPE_SALAIRE);
        $sheet->setCellValue('D' . $rows, $key->DESC_CATEGORIE_SALAIRE);
        $sheet->setCellValue('E' . $rows, number_format($LIQUIDATION,$this->get_precision($LIQUIDATION),",", " "));
        $sheet->setCellValue('F' . $rows, number_format($ORDONNANCEMENT,$this->get_precision($ORDONNANCEMENT),",", " "));
       
        $rows++;
        $i++;
      }

      $code=date('YmdHis');
      $writer = new Xlsx($spreadsheet);
      $writer->save('world.xlsx');
      return $this->response->download('world.xlsx', null)->setFileName('etablissement_du_titre_de_décaissement_autre_retenus'.$code.'.xlsx');

      return redirect('double_commande_new/Paiement_Salaire_Liste/vue_td_Autres_Retenus');
    }

   //Cette fonction retourne le nombre des chiffres d un nombre ($value) passé en paramètre
    function get_precision($value=0)
    {
      $parts = explode('.', strval($value));
      return isset($parts[1]) ? strlen($parts[1]) : 0; 
    }

    //récupération du sous titre par rapport à l'institution
    function get_sous_titre($INSTITUTION_ID = 0)
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

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

    public function getBindParms($columnselect, $table, $where, $orderby)
    {
      $db = db_connect();
      $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
      return $bindparams;
    }
  }