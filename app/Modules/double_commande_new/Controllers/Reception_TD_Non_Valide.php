<?php
/**
 * auteur: joa-kevin iradukunda
 * tache: liste des controles de decaissement par brb et besd
 * date: 05/11/2024
 * email: joa-kevin.iradukunda@mediabox.bi
 * phone: +257 62 63 65 35
 */

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;

class Reception_TD_Non_Valide extends BaseController
{
    protected $session;
    protected $ModelPs;
    protected $library;
    protected $validation;

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

    //Interface de la liste des TD a receptionner pour correction
    function index()
    {
      $data = $this->urichk();
      $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if (empty($user_id)) {
        return redirect('Login_Ptba/do_logout');
      }

      if($this->session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
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
      $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];
      $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
      $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
      $data['get_etape_corr'] = $paiement['get_etape_corr'];
      $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
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

      $inst = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION', 'inst_institutions JOIN user_affectaion ON user_affectaion.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'user_affectaion.USER_ID=' . $user_id . '', 'DESCRIPTION_INSTITUTION ASC');
      $data['institutions'] = $this->ModelPs->getRequete($callpsreq, $inst);

      return view('App\Modules\double_commande_new\Views\Liste_Reception_TD_Non_Valide_View', $data);
    }

    //fonction de la liste des TD a receptionner pour correction
    public function listing()
    {
        $session  = \Config\Services::session();

        if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
        {
            return redirect('Login_Ptba/homepage'); 
        }

        $ETAPE_DOUBLE_COMMANDE_ID = 47;

        //Filtres de la liste
        $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
        $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');

        $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

        $cond_prof = ' ';
        if($profil_id != 1)
        {
            $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
        }

        $critere1 = "";
        $critere2 = "";

        if (!empty($INSTITUTION_ID)) 
        {
            $critere1 = " AND ptba.INSTITUTION_ID=" . $INSTITUTION_ID;
            if (!empty($SOUS_TUTEL_ID)) 
            {
                $critere2 = " AND ptba.SOUS_TUTEL_ID=" . $SOUS_TUTEL_ID;
            }
        }

        $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
        $var_search = str_replace("'", "\'", $var_search);
        $group = "";
        $critaire = "";
        $limit = 'LIMIT 0,1000';
        if ($_POST['length'] != -1) 
        {
            $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
        }
        $order_by = '';

        $order_column = array('NUMERO_BON_ENGAGEMENT','td.TITRE_DECAISSEMENT','ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE','ptba.DESC_TACHE','dev.DESC_DEVISE_TYPE','exec.ENG_BUDGETAIRE','exec.ENG_JURIDIQUE','exec_detail.MONTANT_LIQUIDATION','exec_detail.MONTANT_ORDONNANCEMENT','td.MONTANT_PAIEMENT', 1);

        $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC';

        $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%$var_search%' OR ptba.DESC_TACHE LIKE '%$var_search%' OR exec.ENG_BUDGETAIRE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR td.TITRE_DECAISSEMENT LIKE '%$var_search%')") : '';

        $requetedebase = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.ENG_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE,exec_detail.MONTANT_LIQUIDATION,exec_detail.MONTANT_LIQUIDATION_DEVISE,exec_detail.MONTANT_ORDONNANCEMENT,exec_detail.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE,ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,td.TITRE_DECAISSEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,dev.DESC_DEVISE_TYPE,dev.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID = 5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=".$ETAPE_DOUBLE_COMMANDE_ID." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

        $group = " ";

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
            $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
            $getEtape = "CALL getTable('" . $getEtape . "');";
            $EtapeActuel = $this->ModelPs->getRequeteOne($getEtape);
            $step = ($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE'] : 0;
            $dist = "";

            $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
            $callpsreq = "CALL getRequete(?,?,?,?);";
            $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
            $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
            $number = $row->NUMERO_BON_ENGAGEMENT;
            if (!empty($getProfil))
            {
                foreach ($getProfil as $value)
                {
                    if ($prof_id == $value->PROFIL_ID || $prof_id==1)
                    {
                        $number = "<a  title='' style='color:#ffb944;' href='" . base_url('double_commande_new/'.$step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' >" . $row->NUMERO_BON_ENGAGEMENT . "</a>";

                        $bouton = "<a class='btn btn-primary btn-sm'  title='' href='" . base_url('double_commande_new/'. $step . "/" . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "' ><span class='fa fa-edit'></span></a>";
                    }
                }
            }

            //Nombre des tâches
            $count_task = "SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) AS nbre FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID=".$row->EXECUTION_BUDGETAIRE_ID;
            $count_task = 'CALL `getTable`("'.$count_task.'");';
            $nbre_task = $this->ModelPs->getRequeteOne($count_task);

            $MONTANT_BUDG = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_BUDGETAIRE) : floatval($row->ENG_BUDGETAIRE_DEVISE);
            $MONTANT_JURIDIQUE = $row->DEVISE_TYPE_ID == 1 ? floatval($row->ENG_JURIDIQUE) : floatval($row->ENG_JURIDIQUE_DEVISE);
            $MONTANT_LIQUIDATION = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_LIQUIDATION) : floatval($row->MONTANT_LIQUIDATION_DEVISE);
            $MONTANT_ORDONNANCEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_ORDONNANCEMENT) : floatval($row->MONTANT_ORDONNANCEMENT_DEVISE);
            $MONTANT_PAIEMENT = $row->DEVISE_TYPE_ID == 1 ? floatval($row->MONTANT_PAIEMENT) : floatval($row->MONTANT_PAIEMENT_DEVISE);

            $sub_array = array();
            $sub_array[] = $row->NUMERO_BON_ENGAGEMENT;
            $sub_array[] = $row->TITRE_DECAISSEMENT;
            $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
            $point="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='get_task(".$row->EXECUTION_BUDGETAIRE_ID.")'>".$nbre_task['nbre']."</a></center>";
            $sub_array[] = $point;
            $sub_array[] = $row->DESC_DEVISE_TYPE;
            $sub_array[] = number_format($MONTANT_BUDG, 4, ",", " ");
            $sub_array[] = number_format($MONTANT_JURIDIQUE, 4, ",", " ");
            $sub_array[] = number_format($MONTANT_LIQUIDATION, 4, ",", " ");
            $sub_array[] = number_format($MONTANT_ORDONNANCEMENT, 4, ",", " ");
            $sub_array[] = number_format($MONTANT_PAIEMENT, 4, ",", " ");

            $action2 = "<a class='btn btn-primary btn-sm' data-toggle='tooltip' title='Détail' href='" . base_url('double_commande_new/detail/' . md5($row->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)) . "'><span class='fa fa-plus'></span></a>";

            $sub_array[] = "<div style='display: flex; flex-wrap: nowrap;'>".$action2."</div>";
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
        return $this->response->setJSON($output);
    }

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