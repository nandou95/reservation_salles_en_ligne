<?php
  /** Claude Niyongabo
    *Titre: Les liste de de caissement cas salaire
    *Numero de telephone: (+257) 69641375
    *Email: claude@mediabox.bi
    *Date: 17 septembre,2024
    * 
    **/
  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;
  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
  
  class Decaissement_Salaire_Liste extends BaseController
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

    function vue_decaiss_faire()
    {
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }
       if($this->session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      

      $nbr_decaissement=$this->count_decaissement_salaire();
      $data['nbre_decaiss_faire']=$nbr_decaissement['nbre_decaiss_faire'];
      $data['nbre_decaiss_Fait']=$nbr_decaissement['nbre_decaiss_Fait'];

      return view('App\Modules\double_commande_new\Views\Decaissement_Salaire_Liste_A_Faire_View', $data);
    }

    public function listing_decaissement_faire()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $ETAPE_DOUBLE_COMMANDE_ID = 29;

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $cond_prof = ' ';
      if($profil_id != 1)
      {
        $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
      }

      $critere1 = "";
      $critere2 = "";


      $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
      $var_search = str_replace("'", "\'", $var_search);
      $group = "";
      $critaire = "";
      $limit = 'LIMIT 0,1000';
      if ($_POST['length'] != -1) {
        $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
      }
      $order_by = '';

      $order_column = array('TITRE_DECAISSEMENT','DESC_BENEFICIAIRE','COMPTE_CREDIT','MONTANT_DECAISSEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY TITRE_DECAISSEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (TITRE_DECAISSEMENT LIKE '%$var_search%' OR DESC_BENEFICIAIRE LIKE '%$var_search%' OR COMPTE_CREDIT LIKE '%$var_search%' OR MONTANT_DECAISSEMENT LIKE '%$var_search%')") : '';

      $requetedebase = "SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_PAIEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID LEFT JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3";

      $group = " GROUP BY EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

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
              $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/Decaissement_Salaire/index_dec_salaire/'. md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->TITRE_DECAISSEMENT . "</a>";

              $bouton = "<a class='btn btn-primary btn-sm'  title='Traiter' href='" . base_url('double_commande_new/Decaissement_Salaire/index_dec_salaire/'.md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
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

    //Décaissements déjà  faits
    function vue_decaiss_faits()
    {
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }
      if($this->session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $nbr_decaissement=$this->count_decaissement_salaire();
      $data['nbre_decaiss_faire']=$nbr_decaissement['nbre_decaiss_faire'];
      $data['nbre_decaiss_Fait']=$nbr_decaissement['nbre_decaiss_Fait'];

      return view('App\Modules\double_commande_new\Views\Decaissement_Salaire_Liste_Faits_View', $data);
    }

    function listing_decaissement_deja_fait()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      
      //Filtres de la liste
      $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
      $DATE_FIN=$this->request->getPost('DATE_FIN');

      $critere1 = "";
      $critere2 = "";
      $critere3 = "";
      $critere4 = "";
      $critere5 = "";

      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_DECAISSEMENT >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_DECAISSEMENT >= '".$DATE_DEBUT."' AND td.DATE_DECAISSEMENT <= '".$DATE_FIN."'";
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

      $order_column = array('TITRE_DECAISSEMENT','DESC_BENEFICIAIRE','COMPTE_CREDIT','MONTANT_DECAISSEMENT', 1);

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY TITRE_DECAISSEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (TITRE_DECAISSEMENT LIKE '%$var_search%' OR DESC_BENEFICIAIRE LIKE '%$var_search%' OR COMPTE_CREDIT LIKE '%$var_search%' OR MONTANT_DECAISSEMENT LIKE '%$var_search%')") : '';

      $requetedebase = "SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID WHERE 1 AND td.ETAPE_DOUBLE_COMMANDE_ID=30 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3";

      $group = " GROUP BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
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
        $MONTANT = $row->MONTANT_DECAISSEMENT;
        $sub_array = array();
        $sub_array[] = $row->TITRE_DECAISSEMENT;
        $sub_array[] = $row->DESC_BENEFICIAIRE;
        $sub_array[] = $row->COMPTE_CREDIT;
        $sub_array[] = number_format($MONTANT, $this->get_precision($MONTANT), ",", " ");
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

    //fonction pour l'export excel de Décaissements déjà faits
    public function exporter_Excel_decaissement_fait($DATE_DEBUT=0,$DATE_FIN=0)
    {
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT_SALAIRE');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/homepage');
      }

      $critere1=" ";
      $critere2=" ";
      $critere3=" ";
      $critere4=" ";
      $critere5=" ";
      
      if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
      {
        $critere4.=" AND td.DATE_DECAISSEMENT >= '".$DATE_DEBUT."'";
      }

      if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
      {
        $critere5.=" AND td.DATE_DECAISSEMENT >= '".$DATE_DEBUT."' AND td.DATE_DECAISSEMENT <= '".$DATE_FIN."'";
      }

      $group = " GROUP BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
      $requetedebase="SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT,td.MONTANT_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID WHERE 1 AND td.ETAPE_DOUBLE_COMMANDE_ID=30 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3".$critere4.$critere5.$group;

      $getData = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")'); 

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('C1', 'DECAISSEMENT SALAIRE DEJA FAITS');
      $sheet->setCellValue('A3', 'TITRE DECAISSEMENT');
      $sheet->setCellValue('B3', 'BENEFICIAIRE');
      $sheet->setCellValue('C3', 'COMPTE BANCAIRE');
      $sheet->setCellValue('D3', 'MONTANT');

      $rows = 4;
      $i=1;
      foreach ($getData as $key)
      {
        $MONTANT = $key->MONTANT_DECAISSEMENT;

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
      return $this->response->download('world.xlsx', null)->setFileName('decaissements_salaire_deja_faits'.$code.'.xlsx');

      return redirect('double_commande_new/Decaissement_Salaire_Liste/vue_decaiss_faits');
    }

    public function getBindParms($columnselect, $table, $where, $orderby)
    {
      $db = db_connect();
      $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
      return $bindparams;
    }
       //Cette fonction retourne le nombre des chiffres d un nombre ($value) passé en paramètre
    function get_precision($value=0)
    {
      $parts = explode('.', strval($value));
      return isset($parts[1]) ? strlen($parts[1]) : 0; 
    }

  }


?>