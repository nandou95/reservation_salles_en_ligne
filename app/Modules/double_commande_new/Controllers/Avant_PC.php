<?php
/**
 * Auteur: RUGAMBA Jean Vainqueur
 * email: jean.vainqueur@mediabox.bi
 * whatsapp: +257 62 47 19 15
 * téléphone: +257 66 33 43 25
 * date 15.02.2024 15:12
 */

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Avant_PC extends BaseController
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
    $db = db_connect();
    $IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $IMPORTndparams;
  }
  
  /**
   * renvoie la vue qui va afficher l'interface d'affectation
   */
  public function index ()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_AVANT_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $etape_actuel_id = 33;
    $data['id_etape'] = $etape_actuel_id;

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel_id,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {

        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $psgetrequete = "CALL `getRequete`(?,?,?,?)";

          $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID ='.$etape_actuel_id,' ETAPE_DOUBLE_COMMANDE_ID DESC');
          $titre= $this->ModelPs->getRequeteOne($psgetrequete, $titre);

          $data['etapes'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];

          ///recuperation bn d'engagement
          $bind_bon_engagement="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,devise.DEVISE_TYPE_ID, devise.DESC_DEVISE_TYPE,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_execution_tache exec_tache ON exec.EXECUTION_BUDGETAIRE_ID=exec_tache.EXECUTION_BUDGETAIRE_ID LEFT JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec_tache.PTBA_TACHE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN devise_type devise ON exec.DEVISE_TYPE_ID = devise.DEVISE_TYPE_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=33 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) GROUP BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $bind_bon_engagement = 'CALL getTable("'.$bind_bon_engagement.'");';
          $data['bon_engagement'] = $this->ModelPs->getRequete($bind_bon_engagement);
          $bind_pers_affect= $this->getBindParms('USER_ID,NOM,PRENOM','user_users','PROFIL_ID=6 OR PROFIL_ID=82',' NOM ASC');
          $data['pers_affect'] = $this->ModelPs->getRequete($psgetrequete, $bind_pers_affect);
          return view('App\Modules\double_commande_new\Views\Avant_PC_Add_View', $data);
        }
      }

      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    } 
  }

  /**
   * Transmission vers l'OBR
   */

  //traitement et enregistrement dans la BD
  function save()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_AVANT_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    
    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    $id_etape = $this->request->getPost('id_etape');
    $etape_request = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID="' . $id_etape . '"', 'ETAPE_DOUBLE_COMMANDE_SUIVANT_ID ASC');
    $etape_request = str_replace('\"', '"', $etape_request);
    $next_etape_data = $this->ModelPs->getRequeteOne($psgetrequete, $etape_request)['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];


    $rules = [
      'DATE_RECEPTION' => [
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
      ],
      
      'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'uploaded' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'AFFECT_USER_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'uploaded' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]

    ];

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run())
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID[]');
      $AFFECT_USER_ID = $this->request->getPost('AFFECT_USER_ID[]');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      
      if (!empty($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID))
      {
        foreach ($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID as $value)
        {
          $det_request = $this->getBindParms('td.EXECUTION_BUDGETAIRE_DETAIL_ID','execution_budgetaire_tache_detail det JOIN  execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID="' . $value . '"', '1');
          $det_request = str_replace('\"', '"', $det_request);
          $detail_id = $this->ModelPs->getRequeteOne($psgetrequete, $det_request)['EXECUTION_BUDGETAIRE_DETAIL_ID'];

          $tabledet = 'execution_budgetaire_tache_detail';
          $conditionsdet = 'EXECUTION_BUDGETAIRE_DETAIL_ID =' . $detail_id;
          $datatomodifiedet = 'USER_AFFECTE_ID='.$AFFECT_USER_ID.'';
          $this->update_all_table($tabledet, $datatomodifiedet, $conditionsdet);

          $table = 'execution_budgetaire_titre_decaissement';
          $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID =' . $value;
          $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$next_etape_data.'';
          $this->update_all_table($table, $datatomodifie, $conditions);

          //insertion dans l'historique
          $column_histo = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $data_histo = $value . ',' . $id_etape . ',' . $user_id . ',"' . $DATE_RECEPTION . '","' . $DATE_TRANSMISSION . '"';
          $this->save_histo_racrochage($column_histo, $data_histo);
        }
      }
      return redirect('double_commande_new/Liste_Avant_PC');
    }
    else
    {
      return $this->index();
    }
  }

  public function save_histo_racrochage($columsinsert, $datacolumsinsert)
  {
    $table = 'execution_budgetaire_tache_detail_histo';
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $this->ModelPs->createUpdateDelete($insertReqAgence, $bindparms);
  }

  /* Debut Gestion insertion */
  public function save_all_table($table, $columsinsert, $datacolumsinsert)
  {
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams = [$table, $columsinsert, $datacolumsinsert];
    $result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
    return $id = $result['id'];
  }

  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }


 
}
?>