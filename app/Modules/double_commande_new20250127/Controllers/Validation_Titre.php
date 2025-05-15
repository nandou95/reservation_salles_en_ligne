<?php
  /**
    * 
    * *MUNEZERO SONIA
    *Titre: Validation des Titre de decaisemment (interface de validation et la liste)
    *Numero de telephone: (+257) 65165772
    *Email: sonia@mediabox.bi
    *Date: 29 fevrier,2024
    **/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;

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
      $data['paie_a_faire'] = $paiement['get_paie_afaire'];
      $data['paie_deja_fait'] = $paiement['get_paie_deja_faire'];

      $data_menu=$this->getDataMenuReception();
      $data['recep_prise_charge']=$data_menu['recep_prise_charge'];
      $data['deja_recep_prise_charge']=$data_menu['deja_recep_prise_charge'];
      $data['recep_dir_comptable']=$data_menu['recep_dir_comptable'];
      $data['deja_recep_dir_comptable']=$data_menu['deja_recep_dir_comptable'];
      $data['recep_brb']=$data_menu['recep_brb'];
      $data['déjà_recep_brb']=$data_menu['déjà_recep_brb'];

      $data_titre=$this->nbre_titre_decaisse();
      $data['get_bord_brb']=$data_titre['get_bord_brb'];
      $data['get_bord_deja_trans_brb']=$data_titre['get_bord_deja_trans_brb'];
      $data['get_bord_dc']=$data_titre['get_bord_dc'];
      $data['get_bord_deja_dc']=$data_titre['get_bord_deja_dc'];

      $validee = $this->count_validation_titre();
      $data['get_titre_valide'] = $validee['get_titre_valide'];
      $data['get_titre_termine'] = $validee['get_titre_termine'];

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
      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

      $critere1 = "";
      $critere2 = "";

      if (!empty($INSTITUTION_ID)) {
        $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
        if (!empty($SOUS_TUTEL_ID)) {
          $critere2 = " AND budg.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
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

      $order_column = array('exec_detail.NUMERO_TITRE_DECAISSEMNT','exec.ENG_BUDGETAIRE', 'exec.ENG_JURIDIQUE', 'exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT', 'exec_detail.MONTANT_PAIEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec_detail.NUMERO_TITRE_DECAISSEMNT LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec_detail.MONTANT_LIQUIDATION LIKE '%$var_search%' OR exec_detail.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR exec_detail.MONTANT_PAIEMENT LIKE '%$var_search%')") : '';

      $critaire = $critere1 . " " . $critere2;
      //condition pour le query principale
      $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

      // condition pour le query filter
      $conditionsfilter = $critaire . " " . $search . " " . $group;

      $requetedebase = "SELECT exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec_detail.EXECUTION_BUDGETAIRE_ID,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_PAIEMENT,exec_detail.ETAPE_DOUBLE_COMMANDE_ID,exec_detail.NUMERO_TITRE_DECAISSEMNT FROM execution_budgetaire_tache_detail exec_detail JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_detail.EXECUTION_BUDGETAIRE_ID WHERE exec_detail.ETAPE_DOUBLE_COMMANDE_ID = 26";

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

        $number=$row->NUMERO_TITRE_DECAISSEMNT;

        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $bouton= "<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Traiter' href='".base_url($links."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-arrow-up'></span></a>";
              $number = "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($links."/".md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID)."")."' >".$row->NUMERO_TITRE_DECAISSEMNT."</a>";
            }
          }
        }

        $MONTANT_BUDG = floatval($row->ENG_BUDGETAIRE);
        $MONTANT_JURIDIQUE = floatval($row->ENG_JURIDIQUE);
        $MONTANT_LIQUIDATION = floatval($row->MONTANT_LIQUIDATION);
        $MONTANT_ORDONNANCEMENT = floatval($row->MONTANT_ORDONNANCEMENT);
        $MONTANT_PAIEMENT = floatval($row->MONTANT_PAIEMENT);

        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = number_format($MONTANT_BUDG, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_JURIDIQUE, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_LIQUIDATION, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_PAIEMENT, 2, ",", " ");

        $action1 ='<div class="row dropdown" style="color:#fff;">
        "'.$bouton.'"';
        $action =$action1." "."<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
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

      $paiement = $this->count_paiement();
      $data['paie_a_faire'] = $paiement['get_paie_afaire'];
      $data['paie_deja_fait'] = $paiement['get_paie_deja_faire'];

      $data_menu=$this->getDataMenuReception();
      $data['recep_prise_charge']=$data_menu['recep_prise_charge'];
      $data['deja_recep_prise_charge']=$data_menu['deja_recep_prise_charge'];
      $data['recep_dir_comptable']=$data_menu['recep_dir_comptable'];
      $data['deja_recep_dir_comptable']=$data_menu['deja_recep_dir_comptable'];
      $data['recep_brb']=$data_menu['recep_brb'];
      $data['déjà_recep_brb']=$data_menu['déjà_recep_brb'];

      $data_titre=$this->nbre_titre_decaisse();
      $data['get_bord_brb']=$data_titre['get_bord_brb'];
      $data['get_bord_deja_trans_brb']=$data_titre['get_bord_deja_trans_brb'];
      $data['get_bord_dc']=$data_titre['get_bord_dc'];
      $data['get_bord_deja_dc']=$data_titre['get_bord_deja_dc'];

      $validee = $this->count_validation_titre();
      $data['get_titre_valide'] = $validee['get_titre_valide'];
      $data['get_titre_termine'] = $validee['get_titre_termine'];

      $callpsreq = "CALL `getRequete`(?,?,?,?);";

      $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
      $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

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
      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

      $critere1 = "";
      $critere2 = "";

      if (!empty($INSTITUTION_ID)) {
        $critere1 = " AND exec.INSTITUTION_ID=" . $INSTITUTION_ID;
        if (!empty($SOUS_TUTEL_ID)) {
          $critere2 = " AND budg.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
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

      $order_column = array('exec_detail.NUMERO_TITRE_DECAISSEMNT','exec.ENG_BUDGETAIRE', 'exec.ENG_JURIDIQUE', 'exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT', 'exec_detail.MONTANT_PAIEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec_detail.NUMERO_TITRE_DECAISSEMNT LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR exec.ENG_JURIDIQUE LIKE '%$var_search%' OR exec_detail.MONTANT_LIQUIDATION LIKE '%$var_search%' OR exec_detail.MONTANT_ORDONNANCEMENT LIKE '%$var_search%' OR exec_detail.MONTANT_PAIEMENT LIKE '%$var_search%')") : '';

      $critaire = $critere1 . " " . $critere2;
      //condition pour le query principale
      $conditions = $critaire . " " . $search . " " . $group . " " . $order_by . " " . $limit;

      // condition pour le query filter
      $conditionsfilter = $critaire . " " . $search . " " . $group;

      $requetedebase = "SELECT exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_PAIEMENT,exec_detail.ETAPE_DOUBLE_COMMANDE_ID,exec_detail.NUMERO_TITRE_DECAISSEMNT FROM execution_budgetaire_tache_detail exec_detail JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_detail.EXECUTION_BUDGETAIRE_ID WHERE 1 AND exec_detail.ETAPE_DOUBLE_COMMANDE_ID >=27";

      $requetedebases = $requetedebase . " " . $conditions;
      $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
      $query_secondaire = 'CALL `getTable`("' . $requetedebases . '");';
      $fetch_actions = $this->ModelPs->datatable($query_secondaire);

      $data = array();
      $u = 1;
      foreach ($fetch_actions as $row)
      {
       
        $number=$row->NUMERO_TITRE_DECAISSEMNT;

        $MONTANT_BUDG = floatval($row->ENG_BUDGETAIRE);
        $MONTANT_JURIDIQUE = floatval($row->ENG_JURIDIQUE);
        $MONTANT_LIQUIDATION = floatval($row->MONTANT_LIQUIDATION);
        $MONTANT_ORDONNANCEMENT = floatval($row->MONTANT_ORDONNANCEMENT);
        $MONTANT_PAIEMENT = floatval($row->MONTANT_PAIEMENT);

        $sub_array = array();
        $sub_array[] = $number;
        $sub_array[] = number_format($MONTANT_BUDG, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_JURIDIQUE, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_LIQUIDATION, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 2, ",", " ");
        $sub_array[] = number_format($MONTANT_PAIEMENT, 2, ",", " ");

        $action = "<a class='btn btn-primary btn-sm' title='Détail' href='".base_url('double_commande_new/detail/'.md5($row->EXECUTION_BUDGETAIRE_DETAIL_ID))."' ><span class='fa fa-plus'></span></a>";
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

    // visualiser l'interface de validation d'un titre de decaissement
    function confirmer($id=0)
    {
      $data = $this->urichk();
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

      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_VALIDE_TD') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $infoAffiche  = 'SELECT det.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,det.MONTANT_LIQUIDATION,det.MONTANT_ORDONNANCEMENT,det.ETAPE_DOUBLE_COMMANDE_ID,det.MONTANT_PAIEMENT,det.NUMERO_TITRE_DECAISSEMNT FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE md5(EXECUTION_BUDGETAIRE_DETAIL_ID) = "'.$id.'"';

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

            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_DETAIL_ID='.$data['info']['EXECUTION_BUDGETAIRE_DETAIL_ID'],'DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

            $detail=$this->detail_new($id);
            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['creditVote']=$detail['creditVote'];
            $data['montant_reserve']=$detail['montant_reserve'];
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
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
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

        $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID'); 
        $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID'); 

        $DATE_VALIDE_TITRE=$this->request->getPost('DATE_VALIDE_TITRE');
        $ETAPE_DOUBLE_COMMANDE_ID=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
        $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
        $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');

        $callpsreq = "CALL `getRequete`(?,?,?,?);";     
        $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,IS_CORRECTION,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
        $etape_suivante22= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);

        $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
        $ETAPE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];

        $insertIntoOp='execution_budgetaire_tache_detail_histo';
        $columOp="EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsOp=$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$ETAPE_ID.",".$USER_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";
        $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

        $whereracc ="EXECUTION_BUDGETAIRE_DETAIL_ID = ".$EXECUTION_BUDGETAIRE_DETAIL_ID;
        $insertIntoracc='execution_budgetaire_tache_detail';
        $columracc="ETAPE_DOUBLE_COMMANDE_ID = ".$ETAPE_DOUBLE_COMMANDE_ID.",DATE_VALIDE_TITRE = '".$DATE_VALIDE_TITRE."'";
        $this->update_all_table($insertIntoracc,$columracc,$whereracc);

        $data=['message' => "".lang('messages_lang.valid_titre_dec').""];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Validation_Titre/liste_valide_termine');
      }
      else
      {
        return $this->confirmer(md5($EXECUTION_BUDGETAIRE_DETAIL_ID));
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
  }
?>