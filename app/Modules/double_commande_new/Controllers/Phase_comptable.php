<?php

/** 
 * controller pour la phase comptable du processus d execution budgetaire
 * @author: derick
 * email: derick@mediabox.bi
 * tel:77432485
 */
namespace App\Modules\double_commande_new\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
class Phase_comptable extends BaseController
{
  protected $session;
  protected $ModelPs;
  protected $library;
  protected $ModelS;
  protected $validation;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  //Les nouveaux motifs
  function save_newMotif()
  {
    $session  = \Config\Services::session();

    $DESCRIPTION_MOTIF = $this->request->getPost('DESCRIPTION_MOTIF');
    $MARCHE_PUBLIQUE = 0;
    $MOUVEMENT_DEPENSE_ID=1;

    $table="budgetaire_type_analyse_motif";
    $columsinsert = "MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE";
    $datacolumsinsert = "{$MOUVEMENT_DEPENSE_ID},'{$DESCRIPTION_MOTIF}',{$MARCHE_PUBLIQUE}";    
    $this->save_all_table($table,$columsinsert,$datacolumsinsert);

    $callpsreq = "CALL getRequete(?,?,?,?);";

    //récuperer les motifs
    $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','IS_MARCHE=0','TYPE_ANALYSE_MOTIF_ID ASC');
    $motif = $this->ModelPs->getRequete($callpsreq, $bind_motif);

    $html='<option value="-1">'.lang('messages_lang.selection_autre').'</option>';

    if(!empty($motif))
    {
      foreach($motif as $key)
      { 
        $html.= "<option value='".$key->TYPE_ANALYSE_MOTIF_ID."'>".$key->DESC_TYPE_ANALYSE_MOTIF."</option>";
      }
    }
    $output = array('status' => TRUE ,'motifs' => $html);
    return $this->response->setJSON($output);
  }

  public function prise_en_charge($BORDEREAU_TRANSMISSION_ID)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $etape_en_cour_id = 17;

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_en_cour_id,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);


    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";

          $numero_bordereau_trans_req = $this->getBindParms('NUMERO_BORDEREAU_TRANSMISSION, BORDEREAU_TRANSMISSION_ID', ' execution_budgetaire_bordereau_transmission', 'MD5(BORDEREAU_TRANSMISSION_ID)="' . $BORDEREAU_TRANSMISSION_ID . '"', 'BORDEREAU_TRANSMISSION_ID DESC');
          $numero_bordereau_trans_req = str_replace('\\', '', $numero_bordereau_trans_req);
          $numero_bordereau_trans_data = $this->ModelPs->getRequeteOne($callpsreq, $numero_bordereau_trans_req);

          $get_detail_id = $this->getBindParms('det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.EXECUTION_BUDGETAIRE_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID','execution_budgetaire_bordereau_transmission_bon_titre bon JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN  execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID','MD5(BORDEREAU_TRANSMISSION_ID)="'.$BORDEREAU_TRANSMISSION_ID.'" AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)', 'BORDEREAU_TRANSMISSION_ID DESC');
          $get_detail_id = str_replace('\\', '', $get_detail_id);
          $detail_data = $this->ModelPs->getRequete($callpsreq, $get_detail_id);

          $id_detail = end($detail_data)->EXECUTION_BUDGETAIRE_DETAIL_ID;
          $new_id = end($detail_data)->EXECUTION_BUDGETAIRE_ID;
          $titr_dec_id = end($detail_data)->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
          $data['numero_bordereau_trans_data'] = $numero_bordereau_trans_data;
          $data['id_detail'] = end($detail_data)->ETAPE_DOUBLE_COMMANDE_ID;
          $data['detail_id'] = $id_detail;
          $data['titr_dec_id'] = $titr_dec_id;

          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$titr_dec_id.'', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $request = "SELECT DISTINCT NUMERO_DOCUMENT,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,dev.DEVISE_TYPE_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID, td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID  FROM execution_budgetaire_bordereau_transmission_bon_titre bon JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE BORDEREAU_TRANSMISSION_ID =".$numero_bordereau_trans_data['BORDEREAU_TRANSMISSION_ID']." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";
          $bon_engagement_data = $this->ModelPs->getRequete("CALL getTable ('" . $request . "')");
          $data["bon_engagements"] = $bon_engagement_data;
          $data['infosup'] = $this->infosup_data($id_detail);

          $request_etape = "SELECT DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande WHERE ETAPE_DOUBLE_COMMANDE_ID =".$etape_en_cour_id;
          $etape_data = $this->ModelPs->getRequeteOne("CALL getTable ('" . $request_etape . "')");
          $data["etapes"] = $etape_data;

          return view('App\Modules\double_commande_new\Views\Phase_comptable_prise_en_charge_view', $data);
        }
      }

      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  public function save_prise_en_charge () 
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $bon_engagements = $this->request->getPost("bon_engagement[]");
    $date_transmission = $this->request->getPost('date_transmission');
    $date_reception = $this->request->getPost('date_reception');
    $bene = $this->request->getPost('type_bene');
    $num_bordereau = $this->request->getPost('num_bordereau');
    $BORDEREAU_TRANSMISSION_ID = $this->request->getPost("BORDEREAU_TRANSMISSION_ID");
    $etape_en_cour_id = $this->request->getPost("etape_en_cour_id");
    $detail_id = $this->request->getPost('detail_id');
    $titr_dec_id = $this->request->getPost('titr_dec_id');
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    
    $rules = [
      "bon_engagement" => [
        "label" => "bon_engagement",
        "rules" => "required",
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      "date_reception" => [
        "label" => "date_reception",
        "rules" => "required",
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      "date_transmission" => [
        "label" => "date_transmission",
        "rules" => "required",
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
    ];

    $this->validation->setRules($rules);

    if($this->validation->withRequest($this->request)->run())
    {
      $conditions='BORDEREAU_TRANSMISSION_ID='.$BORDEREAU_TRANSMISSION_ID;
      $datatomodifie = 'STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=2';
      $this->update_all_table('execution_budgetaire_bordereau_transmission', $datatomodifie,$conditions);

      $conditions_2 = 'BORDEREAU_TRANSMISSION_ID=' . $BORDEREAU_TRANSMISSION_ID;
      $datatomodifie_2 = 'STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=3';
      $this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre',$datatomodifie_2, $conditions_2);

      /** la boucle pour enregistre le bon engagement */
      foreach ($bon_engagements as $value)
      {
        $conditions_3='BORDEREAU_TRANSMISSION_ID='.$BORDEREAU_TRANSMISSION_ID.' AND EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$value;
        $datatomodifie_3 = 'STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2';
        $this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre', $datatomodifie_3, $conditions_3);

        $beneficier_req = $this->getBindParms('suppl.TYPE_BENEFICIAIRE_ID', 'execution_budgetaire_titre_decaissement td JOIN execution_budgetaire_tache_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_ID = td.EXECUTION_BUDGETAIRE_ID', 'td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$value , 'TYPE_BENEFICIAIRE_ID ASC');
        $get_beneficir_datas = $this->ModelPs->getRequeteOne($psgetrequete, $beneficier_req);
        $bene = $get_beneficir_datas['TYPE_BENEFICIAIRE_ID'];

        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$etape_en_cour_id.' AND IS_FOURNISSEUR =0 AND IS_SALAIRE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//33
        $conditions_2 = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$value;
        $datatomodifie_2 = 'ETAPE_DOUBLE_COMMANDE_ID='. $NEXT_ETAPE_ID;

        /** pour le fournisseur*/
        if($bene == 1)
        {
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$etape_en_cour_id.' AND IS_FOURNISSEUR =1 AND IS_SALAIRE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
          $datatomodifie_2 = 'ETAPE_DOUBLE_COMMANDE_ID='. $NEXT_ETAPE_ID;
        }
        $this->update_all_table('execution_budgetaire_titre_decaissement', $datatomodifie_2,$conditions_2);
        //insertion dans l'historique
        $column_histo = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $data_histo = $value . ',' . $etape_en_cour_id . ',' . $user_id . ',"' . $date_reception . '","' . $date_transmission . '"';
        $this->save_histo($column_histo, $data_histo);
      }

      return redirect('double_commande_new/Liste_Reception_Prise_Charge/deja_recep');
    }
    else 
    {
      redirect("double_commande_new/Phase_comptable/prise_en_charge/" . md5($id));
    }
  }

  /* debut reception obr*/
  public function reception_obr($id)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_OBR') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=18','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();

          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $table = "execution_budgetaire_tache_detail det JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID";
          $columnselect = "det.EXECUTION_BUDGETAIRE_DETAIL_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, det.EXECUTION_BUDGETAIRE_ID,td.ETAPE_DOUBLE_COMMANDE_ID";
          $where = "md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'";
          $orderby = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC';
          $where = str_replace("\'", "'", $where);
          $db = db_connect();
          $bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
          $bindparams34 = str_replace("\'", "'", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

          $EXECUTION_BUDGETAIRE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];
          $id_detail = $data['id']['EXECUTION_BUDGETAIRE_ID']; 
          //$id = md5($data['id']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']);

          //Requete pour les operation
          $get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','ID_OPERATION!=3','ID_OPERATION ASC');
          $get_oper = str_replace('\\', '', $get_oper);
          $data['operation'] = $this->ModelPs->getRequete($callpsreq, $get_oper);

          //Récuperer les motifs
          $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

          $psgetrequete = "CALL getRequete(?,?,?,?);";
          $resultat = $this->getBindParms('ID_ANALYSE,DESCRIPTION','analyse_resultat','ID_ANALYSE in(1,2)','ID_ANALYSE ASC');
          $data['resultat_data'] = $this->ModelPs->getRequete($psgetrequete, $resultat);

          //type retenu
          $bind_retenu = $this->getBindParms('TYPE_RETENU_ID,DESC_TYPE_RETENU,TYPE_RETENU_POURCENTAGE','bugetaire_type_retenu','1','TYPE_RETENU_ID ASC');
          $data['retenu_data'] = $this->ModelPs->getRequete($psgetrequete, $bind_retenu);

          //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION','execution_budgetaire_tache_detail_histo hist JOIN  execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = hist.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID', 'MD5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', 'td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $bindparamsetapes = $this->getBindParms('DESC_ETAPE_DOUBLE_COMMANDE,det.MONTANT_ORDONNANCEMENT','execution_budgetaire_etape_double_commande dc JOIN execution_budgetaire_titre_decaissement td ON td.ETAPE_DOUBLE_COMMANDE_ID = dc.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID', 'MD5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', 'td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');

          $bindparamsetapes = str_replace('\\','', $bindparamsetapes);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparamsetapes);

          $detail = $this->detail_new($id);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_reception_obr_view', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }   
  }

   /* fonction pour enregistrer les info recu par */
  public function save_reception_obr()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_OBR') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $date_reception = $this->request->getPost('date_reception');
    $date_transmission = $this->request->getPost('date_transmission');
    $TYPE_RETENU_ID = $this->request->getPost('TYPE_RETENU_ID');
    $resultat = $this->request->getPost('resultat');
    $montant_fiscale = preg_replace('/\s/', '', $this->request->getPost('montant_fiscale'));
    $ID_OPERATION = $this->request->getPost('ID_OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $ETAPE_ID = 18;
    $id = (string)$this->request->getPost('id');
    $id_exec_titr_dec = (string)$this->request->getPost('id_exec_titr_dec');

    $psgetrequete = "CALL getRequete(?,?,?,?);";


    $rules = [
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'ID_OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]

    ];

    if($ID_OPERATION == 1)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

    }else{

      $rules['resultat'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      if($resultat == 1)
      {
        $rules['TYPE_RETENU_ID'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];
        
        $rules['montant_fiscale'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];
      }

      
    }

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run()) {

      if ($ID_OPERATION == 1) 
      {
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=1','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
        $datatomodifies = "ETAPE_DOUBLE_COMMANDE_ID=".$NEXT_ETAPE_ID;
        $this->update_all_table($table, $datatomodifies, $conditions);


        //Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value) {
          $insertToTable_motif = 'execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id_exec_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }

      }elseif($ID_OPERATION == 2){

        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=0', '1 ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID=$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];


        $detail_id_query = $this->getBindParms('det.EXECUTION_BUDGETAIRE_DETAIL_ID ,det.EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_tache_detail det', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID =' . $id, '1 ASC');
        $detail_get_one = $this->ModelPs->getRequeteOne($psgetrequete, $detail_id_query);
        $id_exec_budg=$detail_get_one['EXECUTION_BUDGETAIRE_ID'];

        if ($resultat == 1) {

          $table_tva = 'execution_budgetaire_tache_detail_retenu';
          $columsinsert_tva = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,TYPE_RETENU_ID,MONTANT_RETENU";
          $datacolumsinsert_tva ="{$id_exec_titr_dec},{$TYPE_RETENU_ID},{$montant_fiscale}";
          $this->save_all_table($table_tva,$columsinsert_tva, $datacolumsinsert_tva);

          $tabledet = 'execution_budgetaire_tache_detail';
          $conditionsdet = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id;
          $datatomodifiedet = 'MONTANT_PRELEVEMENT_FISCALES='.$montant_fiscale.',RESULTANT_TYPE_ID='.$resultat.'';
          $this->update_all_table($tabledet,$datatomodifiedet, $conditionsdet);

        } else {
          $tabledet = 'execution_budgetaire_tache_detail';
          $conditionsdet = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id;
          $datatomodifiedet = 'RESULTANT_TYPE_ID='.$resultat.'';
          $this->update_all_table($tabledet,$datatomodifiedet, $conditionsdet);
        }

        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
        $datatomodifies = "ETAPE_DOUBLE_COMMANDE_ID=".$NEXT_ETAPE_ID;
        $this->update_all_table($table, $datatomodifies, $conditions);
      }

      $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
      $datacolumsinsert = $id_exec_titr_dec . "," . $ETAPE_ID . "," . $user_id . ",'" . $date_reception . "','" . $date_transmission . "'";
      $this->save_histo($columsinsert, $datacolumsinsert);
      
      return redirect('double_commande_new/Liste_Paiement/vue_obr');
      
    } else {
      return $this->reception_obr(md5($id_exec_titr_dec));
    }
  }

  /* fin reception obr*/

  //Enregistrement d'une nouvelle banque
  function save_newBanque()
  {
    $session  = \Config\Services::session();
    $callpsreq = "CALL getRequete(?,?,?,?);";

    $DESCRIPTION_BANQUE = addslashes($this->request->getPost('DESCRIPTION_BANQUE'));
    $ADRESSE_BANQUE = addslashes($this->request->getPost('ADRESSE_BANQUE'));
    $TYPE_INSTITUTION_FIN_ID = $this->request->getPost('TYPE_INSTITUTION_FIN_ID');

    
    $columsinsert = "NOM_BANQUE,TYPE_INSTITUTION_FIN_ID";
    $datacolumsinsert = "'{$DESCRIPTION_BANQUE}',{$TYPE_INSTITUTION_FIN_ID}";

    if(!empty($ADRESSE_BANQUE))
    {
      $columsinsert = "NOM_BANQUE,ADRESSE,TYPE_INSTITUTION_FIN_ID";
      $datacolumsinsert = "'{$DESCRIPTION_BANQUE}','{$ADRESSE_BANQUE}',{$TYPE_INSTITUTION_FIN_ID}";
    }

    $table="banque";    
    $this->save_all_table($table,$columsinsert,$datacolumsinsert);


    //récuperer les banques
    $bind_bank = $this->getBindParms('BANQUE_ID,NOM_BANQUE,ADRESSE,TYPE_INSTITUTION_FIN_ID','banque','1','NOM_BANQUE ASC');
    $bank = $this->ModelPs->getRequete($callpsreq, $bind_bank);

    $html='<option value="-1">'.lang('messages_lang.selection_autre').'</option>';

    if(!empty($bank))
    {
      foreach($bank as $key)
      { 
        $html.= "<option value='".$key->BANQUE_ID."'>".$key->NOM_BANQUE."</option>";
      }
    }
    $output = array('status' => TRUE ,'banks' => $html);
    return $this->response->setJSON($output);
  }

  
  // prise_en_charge_comptable
  public function prise_en_charge_comptable($id_titr_dec)
  {
    $cart = \Config\Services::cart();
    $cart->destroy();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $etape_actuel = 19;
    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";
          $banque = $this->getBindParms('BANQUE_ID,NOM_BANQUE', 'banque', '1', 'NOM_BANQUE asc');
          $data['get_banque'] = $this->ModelPs->getRequete($callpsreq, $banque);

          //Institutions financières
          $bind_inst_fin = $this->getBindParms('TYPE_INSTITUTION_FIN_ID,DESC_TYPE','inst_institution_fin_type', '1', 'TYPE_INSTITUTION_FIN_ID asc');
          $data['get_inst_fin'] = $this->ModelPs->getRequete($callpsreq, $bind_inst_fin);

          ############# FOR info detail, titre_decaissement
          $query_id = $this->getBindParms('td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.COMMENTAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,det.MONTANT_LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE,DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_ORDONNANCEMENT,dc.ETAPE_DOUBLE_COMMANDE_ID,exec.DEVISE_TYPE_ID,exec.TYPE_ENGAGEMENT_ID,suppl.TYPE_BENEFICIAIRE_ID','execution_budgetaire_tache_detail det JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_tache_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','MD5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id_titr_dec.'"', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
          $query_id = str_replace('\\', '', $query_id);
          $query_id = $this->ModelPs->getRequeteOne($callpsreq, $query_id);
          $id_exec_detail = (int)$query_id['EXECUTION_BUDGETAIRE_DETAIL_ID'];
          $id_exec = $query_id['EXECUTION_BUDGETAIRE_ID'];

          $data['id'] = $query_id;
          $data['etapes'] = $query_id;
          $data['infosup'] = $this->infosup_data($id_exec_detail);
          $data['devise_type']=$query_id['DEVISE_TYPE_ID'];

          //Récuperer le type de retenu
          $bind_type_retenu = $this->getBindParms('TYPE_RETENU_PRISE_CHARGE_ID,CODE_RETENU,LIBELLE','type_retenu_prise_charge','1','CODE_RETENU ASC');
          $data['type_retenu'] = $this->ModelPs->getRequete($callpsreq, $bind_type_retenu);
          
          $historique_raccrochage=$this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION','execution_budgetaire_tache_detail_histo hist','md5(hist.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id_titr_dec.'"','DATE_INSERTION DESC');
          $historique_raccrochage = str_replace('\\', '', $historique_raccrochage);
          $data["historique_data_insertion"] =  $this->ModelPs->getRequeteOne("CALL getRequete(?,?,?,?);", $historique_raccrochage);

          //Récuperer les motifs
          $bind_motif=$this->getBindParms('BUDGETAIRE_TYPE_ANALYSE_ID ,DESC_BUDGETAIRE_TYPE_ANALYSE', 'budgetaire_type_analyse', 'MOUVEMENT_DEPENSE_ID=3', 'BUDGETAIRE_TYPE_ANALYSE_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

          $confirmation_formulaire = $this->getBindParms('ID_OPERATION, DESCRIPTION', 'budgetaire_type_operation_validation', '1', 'DESCRIPTION DESC');
          $confirmation_formulaire = str_replace('\\', '', $confirmation_formulaire);
          $data['confirmation_formulaire_data'] = $this->ModelPs->getRequete($callpsreq, $confirmation_formulaire);
          $data["id_crypt"] = $id_exec_detail;

          $bind_motif=$this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','IS_MARCHE=0','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif_2'] = $this->ModelPs->getRequete($callpsreq,$bind_motif);

          $detail = $this->detail_new($id_titr_dec);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];

          return view('App\Modules\double_commande_new\Views\Phase_comptable_prise_en_charge_comptable_view', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  public function save_prise_en_charge_comptable()
  {
    $session  = \Config\Services::session();
    $cart = \Config\Services::cart();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $type_montant = $this->request->getPost('type_montant');
    $id = $this->request->getPost('id');
    $execution_budgetaire_detail_id = $this->request->getPost('id_detail');
    $id_titr_dec = $this->request->getPost('id_titr_dec');
    $Banque = $this->request->getPost('Banquess');
    $num_titre = $this->request->getPost('num_titre');
    $num_compte = $this->request->getPost('num_compte');
    $motif_paie = $this->request->getPost('motif_paie');
    $date_transmission = $this->request->getPost('date_transmission');
    $date_reception = $this->request->getPost('date_reception');
    $analyse = $this->request->getPost('analyse[]');
    $bene = $this->request->getPost('type_bene');
    $num_bordereau = $this->request->getPost('num_bordereau');
    $paiement_montant = (float)str_replace(' ', '', $this->request->getPost('paiement_montant'));
    $paiement_montant_devise = (float)$this->request->getPost('paiement_montant_dev');
    $date_paiement_devise = $this->request->getPost('date_paiement_devise');
    // $cour_paiement_devise = $this->request->getPost('cour_paiement_devise');
    $ordonancement = (float)$this->request->getPost('ordonancement');
    $montant_devise_ordonancement = (float)$this->request->getPost('MONTANT_DEVISE_ORDONNANCEMENT');
    $OPERATION = (int)$this->request->getPost('OPERATION');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    // $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost("ETAP_PRISE_EN_CHARGE");
    $NEXT_ETAPE_ID = null;
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $ETAPE_SUIVANTE_ID = null;
    $date_prise_en_charge = $this->request->getPost('date_prise_en_charge');
    $TYPE_ENGAGEMENT_ID = $this->request->getPost('TYPE_ENGAGEMENT_ID');

    $donnees = "".$paiement_montant."/-/".$paiement_montant_devise."/-/".$date_paiement_devise."/-/".$date_prise_en_charge."";

    //get montant existant dans execution_budgetaire
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $mont_paie_exista = $this->getBindParms('PAIEMENT,PAIEMENT_DEVISE','execution_budgetaire','EXECUTION_BUDGETAIRE_ID='.$id,'1 DESC');
    $montant_paie = $this->ModelPs->getRequeteOne($psgetrequete, $mont_paie_exista);

    $rules = [
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_prise_en_charge' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    if ($OPERATION == 1 || $OPERATION == 3)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    } else {

      $rules['Banquess'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];

      $rules['motif_paie'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    }

    $this->validation->setRules($rules);

    if ($this->validation->withRequest($this->request)->run())
    {
      $success = false;
      $historique_table = "execution_budgetaire_tache_detail_histo";
      $update_table_details = "execution_budgetaire_tache_detail";
      $table_info_sup = "execution_budgetaire_tache_info_suppl";
      $table_exec_titr_dec = "execution_budgetaire_titre_decaissement";

      $insertToTable_motif_operation = 'execution_budgetaire_histo_operation_verification';
      $columninserthist_motif_operation = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
      $datatoinsert_histo_motif_operation = "" . $OPERATION . "," . $ETAPE_ID . "," . $id . "";
      $this->save_all_table($insertToTable_motif_operation, $columninserthist_motif_operation, $datatoinsert_histo_motif_operation);

      $success = true;

      if (($bene == 1 and $type_montant == 1) or ($bene == 2 and  $type_montant == 1))
      {
        if ($OPERATION === 1)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          $callpsreq = "CALL getRequete(?,?,?,?);";          
          $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $bindparams = str_replace("\\", "", $bindparamss);
          $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

          $table_exec = 'execution_budgetaire';
          $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

          if($get_mont_pay['EXEC_PAIMENT'] > 0)
          {
            if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
            {
              //mont paiement à soustraire
              $update_pay_mont = floatval($get_mont_pay['EXEC_PAIMENT']) - floatval($get_mont_pay['MONTANT_PAIEMENT']);
              $update_pay_mont_devise = floatval($get_mont_pay['EXEC_PAIEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_PAIEMENT_DEVISE']);
              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

            }
            else
            {
              //mont paiement à soustraire
              $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT']; 
              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
            }
          }

          $psgetrequete = "CALL getRequete(?,?,?,?);";
          
          $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
          $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
          $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

          $updateTitr = $table_exec_titr_dec;
          $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
          $datatoupdateTitr = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
          $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
          $RequetePS = 'CALL updateData(?,?,?);';
          $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);
          
          //insertion des motifs
          if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
            foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
              $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id_titr_dec . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
            }
          }
          $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "'" . $id_titr_dec . "','" . $user_id . "','" . $ETAPE_ID . "','" . $date_reception . "','" . $date_transmission . "'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          $success = true;

        }
        if ($OPERATION === 3)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 2 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID.' AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          $callpsreq = "CALL getRequete(?,?,?,?);";          
          $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $bindparams = str_replace("\\", "", $bindparamss);
          $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

          $table_exec = 'execution_budgetaire';
          $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

          if($get_mont_pay['EXEC_PAIMENT'] > 0)
          {
            if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
            {
              //mont paiement à soustraire
              $update_pay_mont = floatval($get_mont_pay['EXEC_PAIMENT']) - floatval($get_mont_pay['MONTANT_PAIEMENT']);
              $update_pay_mont_devise = floatval($get_mont_pay['EXEC_PAIEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_PAIEMENT_DEVISE']);
              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

            }
            else
            {
              //mont paiement à soustraire
              $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT']; 
              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
            }
          }

          $psgetrequete = "CALL getRequete(?,?,?,?);";
          $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
          $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
          $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

          //Update de l'étape
          $updateTitr = $table_exec_titr_dec;
          $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
          $datatoupdateTitr = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
          $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
          $RequetePS = 'CALL updateData(?,?,?);';
          $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);
          
          //insertion des motifs
          if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
            foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
              $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id_titr_dec . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
            }
          }
          $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "".$id_titr_dec.",".$user_id."," . $ETAPE_ID . ",'" . $date_reception . "','" . $date_transmission . "'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          $success = true;

        }
        // pour verifier si ce visa
        elseif ($OPERATION === 2)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');

          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', '1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0 AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          if ($type_montant != 1) {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$execution_budgetaire_detail_id;
            $datatomodifie5 = 'DATE_PRISE_CHARGE="' . $date_prise_en_charge . '"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            //Update de l'étape et montant
            $updateTitr = $table_exec_titr_dec;
            $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
            $datatoupdateTitr = 'MONTANT_PAIEMENT='.$paiement_montant.',MONTANT_PAIEMENT_DEVISE = '.$paiement_montant_devise.', DATE_PAIEMENT = "'.$date_paiement_devise.'", ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
            $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
            $RequetePS = 'CALL updateData(?,?,?);';
            $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);

            //update dans execution
            $nouveau_mont1=floatval($montant_paie['PAIEMENT_DEVISE'])+$paiement_montant_devise;
            $nouveau_bif=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
            $table_exec1='execution_budgetaire';
            $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifie_exec1= 'PAIEMENT_DEVISE="'.$nouveau_mont1.'",PAIEMENT="'.$nouveau_bif.'"';
            $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);

            //Update dans execution_tache
            $conditionsTask = 'EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifieTask = 'MONTANT_PAIEMENT=MONTANT_ORDONNANCEMENT, MONTANT_PAIEMENT_DEVISE=MONTANT_ORDONNANCEMENT_DEVISE';
            $this->update_all_table('execution_budgetaire_execution_tache',$datatomodifieTask,$conditionsTask);
          } 
          else 
          {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            //Update de l'étape et montant
            $updateTitr = $table_exec_titr_dec;
            $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
            $datatoupdateTitr = 'MONTANT_PAIEMENT='.$paiement_montant.',DATE_PAIEMENT = "'.$date_prise_en_charge.'",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
            $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
            $RequetePS = 'CALL updateData(?,?,?);';
            $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);

             //update dans execution
            $nouveau_mont1=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
            $table_exec1='execution_budgetaire';
            $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifie_exec1= 'PAIEMENT="'.$nouveau_mont1.'"';
            $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);

            //Update dans execution_tache
            $conditionsTask = 'EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifieTask = 'MONTANT_PAIEMENT=MONTANT_ORDONNANCEMENT';
            $this->update_all_table('execution_budgetaire_execution_tache',$datatomodifieTask,$conditionsTask);
          }

          
          if($TYPE_ENGAGEMENT_ID == 1)
          {
            foreach($cart->contents() as $value)
            {
              $table_retenu = 'exec_budget_tache_detail_retenu_prise_charge';
              $column_insert_retenu = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,TYPE_RETENU_PRISE_CHARGE_ID,MONTANT_RETENU";
              $datacolumsinsert_retenu ="{$id_titr_dec},{$value['TYPE_RETENU_PRISE_CHARGE_ID']},{$value['MONTANT_RETENU']}";
              $this->save_all_table($table_retenu,$column_insert_retenu, $datacolumsinsert_retenu);
            }
            $cart->destroy();
          }

          $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "".$id_titr_dec.",".$user_id.",".$ETAPE_ID.",'".$date_reception."','".$date_transmission."'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          if (!empty($analyse)) {
            foreach ($analyse as $an) {
              $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE__ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $execution_budgetaire_raccrochage_activite_detail_id . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
            }
          }
          $success = true;
        }
      }
      else
      {
        if ($paiement_montant_devise > $montant_devise_ordonancement)
        {
          $data = ['message' => "".lang('messages_lang.label_ordo_money')."" . " &nbsp;" . "(" . number_format($montant_devise_ordonancement, 0, ' ,', ' ') . ")"];
          session()->setFlashdata('alert', $data);
          return $this->prise_en_charge(md5($id));
        }
        else
        {
          //$produit = $paiement_montant_devise * $cour_paiement_devise;
          //  NON COMENTER 
          if ($OPERATION === 1)
          {
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $callpsreq = "CALL getRequete(?,?,?,?);";          
            $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
            $bindparams = str_replace("\\", "", $bindparamss);
            $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

            $table_exec = 'execution_budgetaire';
            $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

            if($get_mont_pay['EXEC_PAIMENT'] > 0)
            {
              if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
              {
                //mont paiement à soustraire
                $update_pay_mont = floatval($get_mont_pay['EXEC_PAIMENT']) - floatval($get_mont_pay['MONTANT_PAIEMENT']);
                $update_pay_mont_devise = floatval($get_mont_pay['EXEC_PAIEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_PAIEMENT_DEVISE']);
                $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
                $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

              }
              else
              {
                //mont paiement à soustraire
                $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT']; 
                $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
                $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
              }
            }

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            $updateTitr = $table_exec_titr_dec;
            $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
            $datatoupdateTitr = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
            $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
            $RequetePS = 'CALL updateData(?,?,?);';
            $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);
            
            //insertion des motifs
            if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
              foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
                $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id_titr_dec . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
              }
            }
            $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "'" . $id_titr_dec . "','" . $user_id . "','" . $ETAPE_ID . "','" . $date_reception . "','" . $date_transmission . "'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            $success = true;

          }
          if ($OPERATION === 3)
          {
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 2 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID.' AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $callpsreq = "CALL getRequete(?,?,?,?);";          
            $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
            $bindparams = str_replace("\\", "", $bindparamss);
            $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

            $table_exec = 'execution_budgetaire';
            $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

            if($get_mont_pay['EXEC_PAIMENT'] > 0)
            {
              if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
              {
                //mont paiement à soustraire
                $update_pay_mont = floatval($get_mont_pay['EXEC_PAIMENT']) - floatval($get_mont_pay['MONTANT_PAIEMENT']);
                $update_pay_mont_devise = floatval($get_mont_pay['EXEC_PAIEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_PAIEMENT_DEVISE']);
                $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
                $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

              }
              else
              {
                //mont paiement à soustraire
                $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT']; 
                $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
                $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
              }
            }

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            //Update de l'étape
            $updateTitr = $table_exec_titr_dec;
            $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
            $datatoupdateTitr = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
            $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
            $RequetePS = 'CALL updateData(?,?,?);';
            $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);
            
            //insertion des motifs
            if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
              foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
                $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id_titr_dec . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
              }
            }
            $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "".$id_titr_dec.",".$user_id."," . $ETAPE_ID . ",'" . $date_reception . "','" . $date_transmission . "'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            $success = true;

          }

          // pour verifier si ce visa
          elseif ($OPERATION === 2) 
          {            $
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', '1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0 AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            $devi_lik = $this->getBindParms('MONTANT_LIQUIDATION_DEVISE', 'execution_budgetaire_tache_detail', ' EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id, '1');
            $get_devi_lik = $this->ModelPs->getRequeteOne($psgetrequete, $devi_lik);

            if ($type_montant != 1) 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);


              //Update de l'étape
              $updateTitr = $table_exec_titr_dec;
              $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
              $datatoupdateTitr = 'MONTANT_PAIEMENT=' . $paiement_montant . ',MONTANT_PAIEMENT_DEVISE = ' . $get_devi_lik['MONTANT_LIQUIDATION_DEVISE'] . ', DATE_PAIEMENT = "' . $date_paiement_devise . '",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
              $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
              $RequetePS = 'CALL updateData(?,?,?);';
              $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);

              //update dans execution
              $nouveau_mont1=floatval($montant_paie['PAIEMENT_DEVISE'])+$get_devi_lik['MONTANT_LIQUIDATION_DEVISE'];
              $nouveau_bif=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
              $table_exec1='execution_budgetaire';
              $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifie_exec1= 'PAIEMENT_DEVISE="'.$nouveau_mont1.'",PAIEMENT="'.$nouveau_bif.'"';
              $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);

              //Update dans execution_tache
              $conditionsTask = 'EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifieTask = 'MONTANT_PAIEMENT=MONTANT_ORDONNANCEMENT, MONTANT_PAIEMENT_DEVISE=MONTANT_ORDONNANCEMENT_DEVISE';
              $this->update_all_table('execution_budgetaire_execution_tache',$datatomodifieTask,$conditionsTask);
            } 
            else 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

              //Update de l'étape
              $updateTitr = $table_exec_titr_dec;
              $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
              $datatoupdateTitr = 'MONTANT_PAIEMENT=' . $paiement_montant . ', DATE_PAIEMENT = "'.$date_paiement_devise.'",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
              $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
              $RequetePS = 'CALL updateData(?,?,?);';
              $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);

              //update dans execution
              $nouveau_mont1=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
              $table_exec1='execution_budgetaire';
              $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifie_exec1= 'PAIEMENT="'.$nouveau_mont1.'"';
              $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
            
              //Update dans execution_tache
              $conditionsTask = 'EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifieTask = 'MONTANT_PAIEMENT=MONTANT_ORDONNANCEMENT';
              $this->update_all_table('execution_budgetaire_execution_tache',$datatomodifieTask,$conditionsTask);

            }

            if($TYPE_ENGAGEMENT_ID == 1)
            {
              foreach($cart->contents() as $value)
              {
                $table_retenu = 'exec_budget_tache_detail_retenu_prise_charge';
                $column_insert_retenu = "EXECUTION_BUDGETAIRE_DETAIL_ID,TYPE_RETENU_PRISE_CHARGE_ID,MONTANT_RETENU";
                $datacolumsinsert_retenu ="{$execution_budgetaire_detail_id},{$value['TYPE_RETENU_PRISE_CHARGE_ID']},{$value['MONTANT_RETENU']}";
                $this->save_all_table($table_retenu,$column_insert_retenu, $datacolumsinsert_retenu);
              }
              $cart->destroy();
            }
            $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "".$id_titr_dec.",".$user_id.",".$ETAPE_ID.",'".$date_reception . "','".$date_transmission."'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            if (!empty($analyse)) {
              foreach ($analyse as $an) {
                $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
              }
            }
            $success = true;
          }
        }
      }
      return redirect('double_commande_new/Liste_Paiement');
    } 
    else 
    {
      redirect("double_commande_new/Phase_comptable/prise_en_charge_comptable/" . md5($id_titr_dec));
    }
  }

  function detruire_cart()
  {
    $cart = \Config\Services::cart();

    $cart->destroy();      

    $display_save=0;
    $html= '';
    $output = array('status' => TRUE, 'cart'=>$html, 'display_save'=>$display_save);
    return $this->response->setJSON($output);//echo json_encode($output);   
  }

  // ADD ET DELETE CART
  //Le cart des retenus pour la Prise en charge
  public function add_cart()
  {
    $cart = \Config\Services::cart();
    $TYPE_RETENU_PRISE_CHARGE_ID=$this->request->getPost('TYPE_RETENU_PRISE_CHARGE_ID'); 
    $MONTANT_RETENU=$this->request->getPost('MONTANT_RETENU');

    $file_data=array(
      'id'=>$TYPE_RETENU_PRISE_CHARGE_ID,
      'qty'=>1,
      'price'=>1,
      'name'=>'CI',
      'TYPE_RETENU_PRISE_CHARGE_ID'=>$TYPE_RETENU_PRISE_CHARGE_ID,
      'MONTANT_RETENU'=>$MONTANT_RETENU,
      'typecartitem'=>'FILECI'
    );

    $cart->insert($file_data);

    $html="";
    $j=1;
    $i=0;

    $html.='
    <table class="table">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_type_retenu').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_mont_retenu').'</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());


    foreach ($cart->contents() as $items):
      if (preg_match('/FILECI/',$items['typecartitem']))
      {
        $i++;

        $psgetrequete = "CALL `getRequete`(?,?,?,?)";
        //recuperation des types de retenu
        $bind_type_retenu = $this->getBindParms('`TYPE_RETENU_PRISE_CHARGE_ID`,`CODE_RETENU`,`LIBELLE`','type_retenu_prise_charge','TYPE_RETENU_PRISE_CHARGE_ID='.$items['TYPE_RETENU_PRISE_CHARGE_ID'],'TYPE_RETENU_PRISE_CHARGE_ID ASC');
        $type_retenu = $this->ModelPs->getRequeteONe($psgetrequete, $bind_type_retenu);

        $html.='<tr>
        <td>'.$j.'</td>
        <td><strong>'.$type_retenu['CODE_RETENU'].'</strong>&nbsp;&nbsp;'.$type_retenu['LIBELLE'].'</td>
        <td>'.number_format($items['MONTANT_RETENU'], 4, ',', ' ').'</td>
        <td style="width: 5px;">
        <input type="hidden" id="rowid'.$j.'" value='.$items['rowid'].'>
        <button  class="btn btn-danger btn-xs" type="button" onclick="remove_cart('.$j.')">
        x
        </button>
        </tr>';
      }

      $j++;
      $i++;
    endforeach;
    $html.=' </tbody>
    </table>';

    if($i>0)
    {
      $display_save=1;
      $output = array('status' => TRUE, 'cart'=>$html, 'display_save'=>$display_save);
      return $this->response->setJSON($output);//echo json_encode($output);
    }
    else
    {
      $display_save=0;
      $html= '';
      $output = array('status' => TRUE, 'cart'=>$html, 'display_save'=>$display_save);
      return $this->response->setJSON($output);//echo json_encode($output);
    }
  }

  function delete_cart()
  {
    $cart = \Config\Services::cart();
    $rowid=$this->request->getPost('rowid');

    $cart->remove($rowid);      

    $html="";
    $j=1;
    $i=0;
    $html.='
    <table class="table">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_type_retenu').'</th>
    <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_mont_retenu').'</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';

    foreach ($cart->contents() as $item):
      if (preg_match('/FILECI/',$item['typecartitem'])) {

        $i++;

        $psgetrequete = "CALL `getRequete`(?,?,?,?)";
        //recuperation des types de retenu
        $bind_type_retenu = $this->getBindParms('`TYPE_RETENU_PRISE_CHARGE_ID`,CODE_RETENU,`LIBELLE`','type_retenu_prise_charge','TYPE_RETENU_PRISE_CHARGE_ID='.$item['TYPE_RETENU_PRISE_CHARGE_ID'],'TYPE_RETENU_PRISE_CHARGE_ID ASC');
        $type_retenu = $this->ModelPs->getRequeteONe($psgetrequete, $bind_type_retenu);

        $html.='<tr>
        <td>'.$j.'</td>
        <td><strong>'.$type_retenu['CODE_RETENU'].'</strong>&nbsp;&nbsp;'.$type_retenu['LIBELLE'].'</td>
        <td>'.number_format($item['MONTANT_RETENU'], 2, ',', ' ').'</td>
        <td style="width: 5px;">
        <input type="hidden" id="rowid'.$j.'" value='.$item['rowid'].'>
        <button  class="btn btn-danger btn-xs" type="button" onclick="remove_cart('.$j.')">
        x
        </button>
        </tr>' ;
      }

      $j++;
      $i++;
    endforeach;

    $html.=' </tbody>
    </table>';

    if($i>0)
    {
      $display_save=1;
      $output = array('status' => TRUE, 'cart'=>$html, 'display_save'=>$display_save);
      return $this->response->setJSON($output);//echo json_encode($output);
    }
    else
    {
      $display_save=0;
      $html= '';
      $output = array('status' => TRUE, 'cart'=>$html, 'display_save'=>$display_save);
      return $this->response->setJSON($output);//echo json_encode($output);
    }
  }
  //Fin Cart des retenus pour la Prise en charge


  public function prise_en_charge_etablissement($id = '')
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=20','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if(!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";
          $bindparamss = $this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,td.MONTANT_DECAISSEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_PAIEMENT','execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID','md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"','exec.EXECUTION_BUDGETAIRE_ID DESC');
          $bindparams34 = str_replace("\\", "", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

          $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];

          // Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION','execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['id']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].'', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);

          //Requete pour les operation
          $get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION', ' budgetaire_type_operation_validation', '1', 'ID_OPERATION ASC');
          $get_oper = str_replace('\\', '', $get_oper);
          $data['operation'] = $this->ModelPs->getRequete($callpsreq, $get_oper);

          //Récuperer les motifs
          $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

          $detail = $this->detail_new($id);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_prise_en_chage_et_etablissment_view.php', $data);
        }
      }

      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }


  /** fonction pour enregistrer les info de l etablissements de titre */
  public function save_prise_en_charge_etablissement()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }
    if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $numero_decaissement = $this->request->getPost('numero_decaissement');
    $date_elaboration = $this->request->getPost('date_elaboration');
    $date_transmission = $this->request->getPost('date_transmission');
    $montant_decaissement = preg_replace('/\s/', '', $this->request->getPost('montant_decaissement'));
    $id = $this->request->getPost('id');
    $id_detail = $this->request->getPost('id_detail');
    $id_titr_dec = $this->request->getPost('id_titr_dec');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $DEVISE_TYPE_ID_RETRAIT=$this->request->getPost('DEVISE_TYPE_ID_RETRAIT');
    $NOM_PERSONNE_RETRAIT=$this->request->getPost('NOM_PERSONNE_RETRAIT');

    $ID_OPERATION = $this->request->getPost('ID_OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');

    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ID ASC');
    $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);

    $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID. ' AND IS_SALAIRE=0', '1 ASC');
    $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

    $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

    $rules = [
      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    if($ID_OPERATION == 1 || $ID_OPERATION == 3)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }
    else if ($ID_OPERATION == 2)
    {
      $rules['numero_decaissement'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $rules['date_elaboration'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $rules['DEVISE_TYPE_ID_RETRAIT'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

    }

    $this->validation->setRules($rules);

    if ($this->validation->withRequest($this->request)->run()) 
    {
      if($ID_OPERATION == 2)
      {
        //modifier dans la table execution_budgetaire_titre_decaiss
        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_titr_dec;
        $datatomodifie = 'TITRE_DECAISSEMENT="'.$numero_decaissement.'",DATE_ELABORATION_TD="'.$date_elaboration.'",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'",NOM_PERSONNE_RETRAIT="'.addslashes($NOM_PERSONNE_RETRAIT).'",DEVISE_TYPE_ID_RETRAIT="'.$DEVISE_TYPE_ID_RETRAIT.'"';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_TRANSMISSION";
        $datacolumsinsert = "".$id_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);


      }
      elseif ($ID_OPERATION == 1)
      {

        $NEXT_ETAPE_ID = 39;

        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_PAIEMENT,det.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID','det.EXECUTION_BUDGETAIRE_DETAIL_ID=' . $id_detail .'','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

        if($get_mont_pay['EXEC_PAIMENT'] > 0)
        {
          if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
          {
            $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT'];
            $update_pay_mont_devise = $get_mont_pay['EXEC_PAIEMENT_DEVISE'] - $get_mont_pay['MONTANT_PAIEMENT_DEVISE'];

            $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

          }
          else
          {
            $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT'];
            $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
            
          }
        } 

        //modification dans la table execution_budgetaire_titre_decaissement
        $table='execution_budgetaire_titre_decaissement';
        $data_racc='ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.' ';
        $conditions='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_titr_dec ;
        $this->update_all_table($table,$data_racc,$conditions);

        //insertion dans l'historique_detail
        $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_TRANSMISSION";
        $datacolumsinsert = "".$id_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        //Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif = 'execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
      }
      elseif ($ID_OPERATION == 3)
      {

        $NEXT_ETAPE_ID = 41;

        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_PAIEMENT,det.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID','det.EXECUTION_BUDGETAIRE_DETAIL_ID=' . $id_detail .'','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

        if($get_mont_pay['EXEC_PAIMENT'] > 0)
        {
          if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
          {
            $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT'];
            $update_pay_mont_devise = $get_mont_pay['EXEC_PAIEMENT_DEVISE'] - $get_mont_pay['MONTANT_PAIEMENT_DEVISE'];

            $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

          }
          else
          {
            $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT'];
            $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
            
          }
        } 

        //modification dans la table execution_budgetaire_tache_detail
        $table='execution_budgetaire_tache_detail';
        $data_racc='MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
        $conditions='EXECUTION_BUDGETAIRE_DETAIL_ID='.$id_detail ;
        $this->update_all_table($table,$data_racc,$conditions);

        //modification dans la table execution_budgetaire_titre_decaissement
        $table='execution_budgetaire_titre_decaissement';
        $data_racc='ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.' ';
        $conditions='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_titr_dec ;
        $this->update_all_table($table,$data_racc,$conditions);

        //insertion dans l'historique_detail
        $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_TRANSMISSION";
        $datacolumsinsert = "".$id_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        //Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif = 'execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
      }
      
      $data=['message' => "".lang('messages_lang.message_success').""];
      session()->setFlashdata('alert', $data);
      return redirect('double_commande_new/Liste_Paiement/vue_etab_titre');
    } 
    else 
    {
      return $this->prise_en_charge_etablissement(md5($id_titr_dec));
    }
  }
  /* fin etablissement prise en charge*/
  /* fin etablissement prise en charge*/
  
  /* debut Correction prise en charge etablissement du titre*/
  public function correction_pc_etablissement($id = '')
  {
    
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=37','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          ################################ DETAILS D'INFOS ############################
          $get_correct = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,td.MONTANT_DECAISSEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,TITRE_DECAISSEMENT, DATE_ELABORATION_TD, NOM_PERSONNE_RETRAIT, DEVISE_TYPE_ID_RETRAIT, DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_PAIEMENT FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 AND MD5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'";
          $get_correct = 'CALL `getTable`("'.$get_correct.'");';

          $data['correct'] = $this->ModelPs->getRequeteOne($get_correct);
          $data['id'] = $this->ModelPs->getRequeteOne($get_correct); 
          $data['etapes'] = $this->ModelPs->getRequeteOne($get_correct);

          $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];

          // Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID="' . $data['id']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'] . '"', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);


          //get motif rejet de la table historique_raccrochage_operation_verification_motif 
          $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."' AND ETAPE_DOUBLE_COMMANDE_ID IN (38,43,44)";
          $motif_rejetRqt = 'CALL `getTable`("'.$motif_rejet.'");';
          $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);

          $detail = $this->detail_new($id);

          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_correction_pc_etablissement_view.php', $data);
        }
      }

      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  /** fonction pour enregistrer les info de correction de l etablissements de titre */
  public function save_correction_pc_etablissement()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }
    if($session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TITRE_DECAISSEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $numero_decaissement = $this->request->getPost('numero_decaissement');
    $date_elaboration = $this->request->getPost('date_elaboration');
    $date_transmission = $this->request->getPost('date_transmission');
    $montant_decaissement = preg_replace('/\s/', '', $this->request->getPost('montant_decaissement'));
    $id_exec_titr_dec = $this->request->getPost('id_exec_titr_dec');
    $id = $this->request->getPost('id');
    $id_detail = $this->request->getPost('id_detail');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $DEVISE_TYPE_ID_RETRAIT=$this->request->getPost('DEVISE_TYPE_ID_RETRAIT');
    $NOM_PERSONNE_RETRAIT=$this->request->getPost('NOM_PERSONNE_RETRAIT');

    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID.' AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
    $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

    $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

    $rules = [
      'numero_decaissement' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_elaboration' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'DEVISE_TYPE_ID_RETRAIT' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      
    ];
    $this->validation->setRules($rules);

    if ($this->validation->withRequest($this->request)->run()) 
    {
      
      //modifier dans la table execution_budgetaire_titre_decaiss
      $table = 'execution_budgetaire_titre_decaissement';
      $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
      $datatomodifie = 'TITRE_DECAISSEMENT="'.$numero_decaissement.'",DATE_ELABORATION_TD="'.$date_elaboration.'",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'",NOM_PERSONNE_RETRAIT="'.addslashes($NOM_PERSONNE_RETRAIT).'",DEVISE_TYPE_ID_RETRAIT="'.$DEVISE_TYPE_ID_RETRAIT.'"';
      $this->update_all_table($table, $datatomodifie, $conditions);

      $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_TRANSMISSION";
      $datacolumsinsert = "".$id_exec_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_transmission."'";
      $this->save_histo($columsinsert, $datacolumsinsert);
      return redirect('double_commande_new/Liste_Paiement/vue_correct_etab_titre');
    } 
    else 
    {
      return $this->correction_pc_etablissement(md5($id_exec_titr_dec));
    }
  }
  /* fin correction etablissement prise en charge*/

  /* Debut Retour A La Correction Depuis Les signatures du titre*/
  public function sign_titre_retour_correction($id)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      //print_r('a problem');exit();
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=38','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);


    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";          
          $bindparamss =$this->getBindParms('td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.EXECUTION_BUDGETAIRE_ID,td.EXECUTION_BUDGETAIRE_DETAIL_ID,MONTANT_DECAISSEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_ORDONNANCEMENT','execution_budgetaire_titre_decaissement td JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID','md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $bindparams34 = str_replace("\\", "", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

          $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];
          
          //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION,ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          //Récuperation de l'étape précedent
          $etap_prev = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);
          $data['etap_prev'] = $etap_prev['ETAPE_ID'];

          //récuperer l'étape à corriger
          $step_correct = $this->getBindParms('ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR', 'budgetaire_etape_retour_correction','ETAPE_RETOUR_CORRECTION_ID !=1','ETAPE_RETOUR_CORRECTION_ID ASC');
          $data['get_correct'] = $this->ModelPs->getRequete($callpsreq, $step_correct);

          $gettypevalidation = $this->getBindParms('ID_OPERATION,DESCRIPTION', 'budgetaire_type_operation_validation', 'ID_OPERATION=1', 'ID_OPERATION ASC');
          $data['type'] = $this->ModelPs->getRequete($callpsreq, $gettypevalidation);

          //Récuperer les motifs
          $bind_motif = $this->getBindParms('BUDGETAIRE_TYPE_ANALYSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE', 'budgetaire_type_analyse','1', 'BUDGETAIRE_TYPE_ANALYSE_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);


          //get motif rejet de la table historique_raccrochage_operation_verification_motif 
          $motif_rejet  = 'SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'" AND ETAPE_DOUBLE_COMMANDE_ID='.$etap_prev['ETAPE_ID'].'';
          $motif_rejet = str_replace("\\", "", $motif_rejet);
          $motif_rejetRqt = "CALL getTable('" . $motif_rejet . "');";
          $motif_rejetRqt = str_replace("\\", "", $motif_rejet);
          $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);


          $detail = $this->detail_new($id);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];
          return view('App\Modules\double_commande_new\Views\Sign_Titre_Retour_Correction_View', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }    
  }

  public function save_sign_titre_retour_correction()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $id_exec_titr_dec = $this->request->getPost('id_exec_titr_dec');
    $id = $this->request->getPost('id');
    $id_exec = $this->request->getPost('id_exec');
    $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
    $date_reception = $this->request->getPost('date_reception');
    $date_transmission = $this->request->getPost('date_transmission');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $ID_OPERATION = $this->request->getPost('ID_OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID');

    $rules = [
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'ID_OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    if($ID_OPERATION == 1)
    {
      $rules['ETAPE_RETOUR_CORRECTION_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

    }

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run()) {
      $psgetrequete = "CALL getRequete(?,?,?,?);";
      //si c'est retour a la correction
      if ($ID_OPERATION == 1) 
      {
        if($ETAPE_RETOUR_CORRECTION_ID == 1)
        {
          $NEXT_ETAPE_ID = 36;

          $callpsreq = "CALL getRequete(?,?,?,?);";          
          $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec.'','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $bindparams = str_replace("\\", "", $bindparamss);
          $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

          $table_exec = 'execution_budgetaire';
          $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id_exec;

          if($get_mont_pay['EXEC_PAIMENT'] > 0)
          {
            if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
            {
              //mont ordonnancement à soustraire
              $update_ordo_mont = floatval($get_mont_pay['EXEC_ORDONNANCEMENT']) - floatval($get_mont_pay['MONTANT_ORDONNANCEMENT']);
              $update_ordo_mont_devise = floatval($get_mont_pay['EXEC_ORDONNANCEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_ORDONNANCEMENT_DEVISE']);

              //mont paiement à soustraire
              $update_pay_mont = floatval($get_mont_pay['EXEC_PAIMENT']) - floatval($get_mont_pay['MONTANT_PAIEMENT']);
              $update_pay_mont_devise = floatval($get_mont_pay['EXEC_PAIEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_PAIEMENT_DEVISE']);

              $datatomodifie_exec = 'ORDONNANCEMENT='.$update_ordo_mont.', ORDONNANCEMENT_DEVISE='.$update_ordo_mont_devise.', PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
              
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

            }
            else
            {
              //mont ordonnancement à soustraire
              $update_ordo_mont = $get_mont_pay['EXEC_ORDONNANCEMENT'] - $get_mont_pay['MONTANT_ORDONNANCEMENT'];

              //mont paiement à soustraire
              $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT'];
              
              $datatomodifie_exec = 'ORDONNANCEMENT='.$update_ordo_mont.', PAIEMENT='.$update_pay_mont.'';
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
              
            }
          }  

        }
        elseif($ETAPE_RETOUR_CORRECTION_ID == 2)
        {
          $NEXT_ETAPE_ID = 39;
          $callpsreq = "CALL getRequete(?,?,?,?);";          
          $bindparamss =$this->getBindParms('exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_exec_titr_dec .'','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $bindparams = str_replace("\\", "", $bindparamss);
          $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

          $table_exec = 'execution_budgetaire';
          $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id_exec;

          if($get_mont_pay['EXEC_PAIMENT'] > 0)
          {
            if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
            {
              $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT'];
              $update_pay_mont_devise = $get_mont_pay['EXEC_PAIEMENT_DEVISE'] - $get_mont_pay['MONTANT_PAIEMENT_DEVISE'];

              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

            }
            else
            {
              $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT'];
              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
              
            }
          } 

        }
        elseif($ETAPE_RETOUR_CORRECTION_ID == 3)
        {
          $NEXT_ETAPE_ID = 37;
        }

        //Update de l'étape
        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_exec_titr_dec;
        $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.'';
        $this->update_all_table($table, $datatomodifie, $conditions);
        
        $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id_exec_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        //Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value) {
          $insertToTable_motif = '  execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id_exec_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
        $data = ['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liste_Paiement/vue_correct_etape');
      }
    } 
    else 
    {
      return $this->sign_titre_retour_correction(md5($id_exec_titr_dec));
    }
  }

  /* Fin Retour A La Correction Depuis Les signatures du titre*/

  /* Debut correction prise en charge*/
  public function correction_prise_en_charge($id = 0)
  {
    $cart = \Config\Services::cart();
    $cart->destroy();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $etape_actuel = 39;
    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";
          $banque = $this->getBindParms('BANQUE_ID,NOM_BANQUE', 'banque', '1', 'NOM_BANQUE asc');
          $data['get_banque'] = $this->ModelPs->getRequete($callpsreq, $banque);

          //Institutions financières
          $bind_inst_fin = $this->getBindParms('TYPE_INSTITUTION_FIN_ID,DESC_TYPE','inst_institution_fin_type', '1', 'TYPE_INSTITUTION_FIN_ID asc');
          $data['get_inst_fin'] = $this->ModelPs->getRequete($callpsreq, $bind_inst_fin);

          ############# FOR ID TITRE_DEC
          $query_id = $this->getBindParms('td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.EXECUTION_BUDGETAIRE_DETAIL_ID,td.EXECUTION_BUDGETAIRE_ID,exec.COMMENTAIRE,td.ETAPE_DOUBLE_COMMANDE_ID,det.MONTANT_LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE,det.MONTANT_ORDONNANCEMENT,dc.DESC_ETAPE_DOUBLE_COMMANDE,exec.DEVISE_TYPE_ID,DATE_PRISE_CHARGE,td.BANQUE_ID,td.COMPTE_CREDIT,DATE_PAIEMENT,exec.TYPE_ENGAGEMENT_ID,exec.MARCHE_PUBLIQUE,suppl.TYPE_BENEFICIAIRE_ID','execution_budgetaire_titre_decaissement td JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_tache_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID','MD5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $query_id = str_replace('\\', '', $query_id);
          $query_id = $this->ModelPs->getRequeteOne($callpsreq, $query_id);
          $id_exec_detail = (int)$query_id['EXECUTION_BUDGETAIRE_DETAIL_ID'];
          $id_exec = $query_id['EXECUTION_BUDGETAIRE_ID'];
          $data['id'] = $query_id;
          $data['etapes'] =  $query_id;
          $data['devise_type']= $query_id['DEVISE_TYPE_ID'];
          $data['infoprise'] =  $query_id;
          $data['type_eng'] =  $query_id;

    
          $historique_raccrochage=$this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION','execution_budgetaire_tache_detail_histo hist','md5(hist.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', 'DATE_INSERTION DESC');
          $historique_raccrochage = str_replace('\\', '', $historique_raccrochage);
          $data["historique_data_insertion"] =  $this->ModelPs->getRequeteOne("CALL getRequete(?,?,?,?);", $historique_raccrochage);

          //Récuperer les motifs
          $bind_motif=$this->getBindParms('BUDGETAIRE_TYPE_ANALYSE_ID ,DESC_BUDGETAIRE_TYPE_ANALYSE', 'budgetaire_type_analyse', 'MOUVEMENT_DEPENSE_ID=3', 'BUDGETAIRE_TYPE_ANALYSE_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

          $confirmation_formulaire = $this->getBindParms('ID_OPERATION, DESCRIPTION', 'budgetaire_type_operation_validation', '1', 'DESCRIPTION DESC');
          $confirmation_formulaire = str_replace('\\', '', $confirmation_formulaire);
          $data['confirmation_formulaire_data'] = $this->ModelPs->getRequete($callpsreq, $confirmation_formulaire);
          $data["id_crypt"] = $id_exec_detail;

          $bind_motif=$this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','IS_MARCHE=0','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif_2'] = $this->ModelPs->getRequete($callpsreq,$bind_motif);

          
          //get motif rejet de la table historique_raccrochage_operation_verification_motif 
          $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."' AND ETAPE_DOUBLE_COMMANDE_ID=38";
          $motif_rejetRqt = 'CALL `getTable`("'.$motif_rejet.'");';
          $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);

          //multi select de verification dans le view
          $sqlVerification='SELECT DISTINCT analyse.DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE,analyse.MOUVEMENT_DEPENSE_ID, analyse.BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,veri.EXECUTION_BUDGETAIRE_ID FROM budgetaire_type_analyse analyse JOIN execution_budgetaire_histo_operation_verification veri ON veri.TYPE_ANALYSE_ID=analyse.BUDGETAIRE_TYPE_ANALYSE_ID WHERE veri.EXECUTION_BUDGETAIRE_ID='.$id_exec.' AND veri.TYPE_ANALYSE_ID IN(SELECT BUDGETAIRE_TYPE_ANALYSE_ID FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID=3)';
          $data['get_verification2'] = $this->ModelPs->getRequete("CALL `getTable`('" . $sqlVerification . "')");

          
          // correction 
          if (!empty($data['get_verification2']))
          {
            $TYPE_ANALYSE_ID  = '';
            foreach ($data['get_verification2'] as $key)
            {
              $TYPE_ANALYSE_ID .= $key->TYPE_ANALYSE_ID.',';
            }

            $TYPE_ANALYSE_ID.=',';
            $TYPE_ANALYSE_ID = str_replace(',,','',$TYPE_ANALYSE_ID);

            $bindparams=$this->getBindParms('BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID, DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE','budgetaire_type_analyse','1 AND BUDGETAIRE_TYPE_ANALYSE_ID NOT IN ('.$TYPE_ANALYSE_ID.') AND MOUVEMENT_DEPENSE_ID = 3','DESC_BUDGETAIRE_TYPE_ANALYSE ASC');
            $data['get_verification'] = $this->ModelPs->getRequete($callpsreq, $bindparams);
          }
          else
          {
            $verification  = 'SELECT BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,MOUVEMENT_DEPENSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID = 3 AND  IS_MARCHE='.$data['id']['MARCHE_PUBLIQUE'].' ORDER BY DESC_TYPE_ANALYSE ASC';
            $verification = "CALL `getTable`('" . $verification . "');";
            $data['get_verification']= $this->ModelPs->getRequete($verification);
          }
  
          //Type d'opération
          
          $data['operation_type'] = 2;

          //Récuperer le type de retenu
          $bind_type_retenu = $this->getBindParms('TYPE_RETENU_PRISE_CHARGE_ID,CODE_RETENU,LIBELLE','type_retenu_prise_charge','1','CODE_RETENU ASC');
          $data['type_retenu'] = $this->ModelPs->getRequete($callpsreq, $bind_type_retenu);

          $data['mycart'] = '';
          if($data['type_eng']['TYPE_ENGAGEMENT_ID'] == 1)
          {
            $bind_exec_retenu = $this->getBindParms('TACHE_DETAIL_RETENU_PRISE_CHARGE_ID,TYPE_RETENU_PRISE_CHARGE_ID,MONTANT_RETENU','exec_budget_tache_detail_retenu_prise_charge','md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"','TACHE_DETAIL_RETENU_PRISE_CHARGE_ID ASC');
            $exec_retenu = $this->ModelPs->getRequete($callpsreq, $bind_exec_retenu);

            $cart = \Config\Services::cart();

            foreach($exec_retenu as $key)
            {
              $file_data=array(
                'id'=>$key->TYPE_RETENU_PRISE_CHARGE_ID,
                'qty'=>1,
                'price'=>1,
                'name'=>'CI',
                'TYPE_RETENU_PRISE_CHARGE_ID'=>$key->TYPE_RETENU_PRISE_CHARGE_ID,
                'MONTANT_RETENU'=>$key->MONTANT_RETENU,
                'typecartitem'=>'FILECI'
              );
              
              $cart->insert($file_data);
            }

            $html="";
            $j=1;
            $i=0;

            $html.='
            <table class="table">
            <thead class="table-dark">
            <tr>
            <th>#</th>
            <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_type_retenu').'</th>
            <th class="text-uppercase" style="white-space: nowrap;">'.lang('messages_lang.label_mont_retenu').'</th>
            <th>OPTION</th>
            </tr>
            </thead>
            <tbody>';
            $i=0;
            $val=count($cart->contents());


            foreach ($cart->contents() as $items):
              if (preg_match('/FILECI/',$items['typecartitem']))
              {
                $i++;

                $psgetrequete = "CALL `getRequete`(?,?,?,?)";
                //recuperation des types de retenu
                $bind_type_retenu = $this->getBindParms('`TYPE_RETENU_PRISE_CHARGE_ID`,`CODE_RETENU`,`LIBELLE`','type_retenu_prise_charge','TYPE_RETENU_PRISE_CHARGE_ID='.$items['TYPE_RETENU_PRISE_CHARGE_ID'],'TYPE_RETENU_PRISE_CHARGE_ID ASC');
                $type_retenu = $this->ModelPs->getRequeteONe($psgetrequete, $bind_type_retenu);

                $html.='<tr>
                <td>'.$j.'</td>
                <td><strong>'.$type_retenu['CODE_RETENU'].'</strong>&nbsp;&nbsp;'.$type_retenu['LIBELLE'].'</td>
                <td>'.number_format($items['MONTANT_RETENU'], 4, ',', ' ').'</td>
                <td style="width: 5px;">
                <input type="hidden" id="rowid'.$j.'" value='.$items['rowid'].'>
                <button  class="btn btn-danger btn-xs" type="button" onclick="remove_cart('.$j.')">
                x
                </button>
                </tr>';
              }

              $j++;
              $i++;
            endforeach;
            $html.=' </tbody>
            </table>';

            if ($i>0) {
              $data['mycart'] = $html;
            }else{
              $display_save=0;
              $html= '';
              $data['mycart'] = $html;
            }

          }

          $detail = $this->detail_new($id);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];

          return view('App\Modules\double_commande_new\Views\Phase_comptable_prise_en_charge_correction_View', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  public function save_correction_prise_en_charge()
  {
    $session  = \Config\Services::session();
    $cart = \Config\Services::cart();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PRISE_EN_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $type_montant = $this->request->getPost('type_montant');
    $id_titr_dec = $this->request->getPost('id_titr_dec');
    $id = $this->request->getPost('id');
    $execution_budgetaire_detail_id = $this->request->getPost('id_detail');
    $Banque = $this->request->getPost('Banquess');
    $num_titre = $this->request->getPost('num_titre');
    $num_compte = $this->request->getPost('num_compte');
    $motif_paie = $this->request->getPost('motif_paie');
    $date_transmission = $this->request->getPost('date_transmission');
    $date_reception = $this->request->getPost('date_reception');
    $analyse = $this->request->getPost('analyse[]');
    $bene = $this->request->getPost('type_bene');
    $num_bordereau = $this->request->getPost('num_bordereau');
    $paiement_montant = (float)str_replace(' ', '', $this->request->getPost('paiement_montant'));
    $paiement_montant_devise = (float)$this->request->getPost('paiement_montant_dev');
    $date_paiement_devise = $this->request->getPost('date_paiement_devise');
    // $cour_paiement_devise = $this->request->getPost('cour_paiement_devise');
    $ordonancement = (float)$this->request->getPost('ordonancement');
    $montant_devise_ordonancement = (float)$this->request->getPost('MONTANT_DEVISE_ORDONNANCEMENT');
    $OPERATION = (int)$this->request->getPost('OPERATION');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $NEXT_ETAPE_ID = null;
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $ETAPE_SUIVANTE_ID = null;
    $date_prise_en_charge = $this->request->getPost('date_prise_en_charge');
    $TYPE_ENGAGEMENT_ID = $this->request->getPost('TYPE_ENGAGEMENT_ID');

    $donnees = "".$paiement_montant."/-/".$paiement_montant_devise."/-/".$date_paiement_devise."/-/".$date_prise_en_charge."";

    //get montant existant dans execution_budgetaire
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $mont_paie_exista = $this->getBindParms('PAIEMENT,PAIEMENT_DEVISE','execution_budgetaire','EXECUTION_BUDGETAIRE_ID='.$id,'1 DESC');
    $montant_paie = $this->ModelPs->getRequeteOne($psgetrequete, $mont_paie_exista);

    $rules = [
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_prise_en_charge' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    if ($OPERATION == 1 || $OPERATION == 3)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    } else {

      $rules['Banquess'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];

      $rules['motif_paie'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    }

    $this->validation->setRules($rules);

    if ($this->validation->withRequest($this->request)->run())
    {
      $success = false;
      $historique_table = "execution_budgetaire_tache_detail_histo";
      $update_table_details = "execution_budgetaire_tache_detail";
      $table_info_sup = "execution_budgetaire_tache_info_suppl";
      $table_exec_titr_dec = "execution_budgetaire_titre_decaissement";

      $insertToTable_motif_operation = 'execution_budgetaire_histo_operation_verification';
      $columninserthist_motif_operation = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
      $datatoinsert_histo_motif_operation = "" . $OPERATION . "," . $ETAPE_ID . "," . $id . "";
      $this->save_all_table($insertToTable_motif_operation, $columninserthist_motif_operation, $datatoinsert_histo_motif_operation);

      $success = true;

      if (($bene == 1 and $type_montant == 1) or ($bene == 2 and  $type_montant == 1))
      {
        if ($OPERATION === 1)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          $callpsreq = "CALL getRequete(?,?,?,?);";          
          $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $bindparams = str_replace("\\", "", $bindparamss);
          $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

          $table_exec = 'execution_budgetaire';
          $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

          if($get_mont_pay['EXEC_PAIMENT'] > 0)
          {
            if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
            {
              //mont paiement à soustraire
              $update_pay_mont = floatval($get_mont_pay['EXEC_PAIMENT']) - floatval($get_mont_pay['MONTANT_PAIEMENT']);
              $update_pay_mont_devise = floatval($get_mont_pay['EXEC_PAIEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_PAIEMENT_DEVISE']);
              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

            }
            else
            {
              //mont paiement à soustraire
              $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT']; 
              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
            }
          }

          $psgetrequete = "CALL getRequete(?,?,?,?);";
          
          $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
          $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
          $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

          $updateTitr = $table_exec_titr_dec;
          $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
          $datatoupdateTitr = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
          $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
          $RequetePS = 'CALL updateData(?,?,?);';
          $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);
          
          //insertion des motifs
          if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
            foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
              $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id_titr_dec . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
            }
          }
          $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "'" . $id_titr_dec . "','" . $user_id . "','" . $ETAPE_ID . "','" . $date_reception . "','" . $date_transmission . "'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          $success = true;

        }
        if ($OPERATION === 3)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 2 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID.' AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          $callpsreq = "CALL getRequete(?,?,?,?);";          
          $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $bindparams = str_replace("\\", "", $bindparamss);
          $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

          $table_exec = 'execution_budgetaire';
          $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

          if($get_mont_pay['EXEC_PAIMENT'] > 0)
          {
            if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
            {
              //mont paiement à soustraire
              $update_pay_mont = floatval($get_mont_pay['EXEC_PAIMENT']) - floatval($get_mont_pay['MONTANT_PAIEMENT']);
              $update_pay_mont_devise = floatval($get_mont_pay['EXEC_PAIEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_PAIEMENT_DEVISE']);
              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

            }
            else
            {
              //mont paiement à soustraire
              $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT']; 
              $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
              $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
            }
          }

          $psgetrequete = "CALL getRequete(?,?,?,?);";
          $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
          $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
          $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

          //Update de l'étape
          $updateTitr = $table_exec_titr_dec;
          $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
          $datatoupdateTitr = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
          $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
          $RequetePS = 'CALL updateData(?,?,?);';
          $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);
          
          //insertion des motifs
          if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
            foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
              $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id_titr_dec . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
            }
          }
          $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "".$id_titr_dec.",".$user_id."," . $ETAPE_ID . ",'" . $date_reception . "','" . $date_transmission . "'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          $success = true;

        }
        // pour verifier si ce visa
        elseif ($OPERATION === 2)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');

          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', '1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0 AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          if ($type_montant != 1) {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$execution_budgetaire_detail_id;
            $datatomodifie5 = 'DATE_PRISE_CHARGE="' . $date_prise_en_charge . '"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            //Update de l'étape et montant
            $updateTitr = $table_exec_titr_dec;
            $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
            $datatoupdateTitr = 'MONTANT_PAIEMENT='.$paiement_montant.',MONTANT_PAIEMENT_DEVISE = '.$paiement_montant_devise.', DATE_PAIEMENT = "'.$date_paiement_devise.'", ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
            $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
            $RequetePS = 'CALL updateData(?,?,?);';
            $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);

            //update dans execution
            $nouveau_mont1=floatval($montant_paie['PAIEMENT_DEVISE'])+$paiement_montant_devise;
            $nouveau_bif=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
            $table_exec1='execution_budgetaire';
            $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifie_exec1= 'PAIEMENT_DEVISE="'.$nouveau_mont1.'",PAIEMENT="'.$nouveau_bif.'"';
            $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);

            //Update dans execution_tache
            $conditionsTask = 'EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifieTask = 'MONTANT_PAIEMENT=MONTANT_ORDONNANCEMENT, MONTANT_PAIEMENT_DEVISE=MONTANT_ORDONNANCEMENT_DEVISE';
            $this->update_all_table('execution_budgetaire_execution_tache',$datatomodifieTask,$conditionsTask);
          } 
          else 
          {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            //Update de l'étape et montant
            $updateTitr = $table_exec_titr_dec;
            $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
            $datatoupdateTitr = 'MONTANT_PAIEMENT='.$paiement_montant.',DATE_PAIEMENT = "'.$date_prise_en_charge.'",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
            $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
            $RequetePS = 'CALL updateData(?,?,?);';
            $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);

             //update dans execution
            $nouveau_mont1=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
            $table_exec1='execution_budgetaire';
            $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifie_exec1= 'PAIEMENT="'.$nouveau_mont1.'"';
            $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);

            //Update dans execution_tache
            $conditionsTask = 'EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifieTask = 'MONTANT_PAIEMENT=MONTANT_ORDONNANCEMENT';
            $this->update_all_table('execution_budgetaire_execution_tache',$datatomodifieTask,$conditionsTask);
          }

          
          if($TYPE_ENGAGEMENT_ID == 1)
          {
            foreach($cart->contents() as $value)
            {
              $table_retenu = 'exec_budget_tache_detail_retenu_prise_charge';
              $column_insert_retenu = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,TYPE_RETENU_PRISE_CHARGE_ID,MONTANT_RETENU";
              $datacolumsinsert_retenu ="{$id_titr_dec},{$value['TYPE_RETENU_PRISE_CHARGE_ID']},{$value['MONTANT_RETENU']}";
              $this->save_all_table($table_retenu,$column_insert_retenu, $datacolumsinsert_retenu);
            }
            $cart->destroy();
          }

          $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "".$id_titr_dec.",".$user_id.",".$ETAPE_ID.",'".$date_reception."','".$date_transmission."'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          if (!empty($analyse)) {
            foreach ($analyse as $an) {
              $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE__ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $execution_budgetaire_raccrochage_activite_detail_id . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
            }
          }
          $success = true;
        }
      }
      else
      {
        if ($paiement_montant_devise > $montant_devise_ordonancement)
        {
          $data = ['message' => "".lang('messages_lang.label_ordo_money')."" . " &nbsp;" . "(" . number_format($montant_devise_ordonancement, 0, ' ,', ' ') . ")"];
          session()->setFlashdata('alert', $data);
          return $this->prise_en_charge(md5($id));
        }
        else
        {
          //$produit = $paiement_montant_devise * $cour_paiement_devise;
          //  NON COMENTER 
          if ($OPERATION === 1)
          {
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $callpsreq = "CALL getRequete(?,?,?,?);";          
            $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
            $bindparams = str_replace("\\", "", $bindparamss);
            $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

            $table_exec = 'execution_budgetaire';
            $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

            if($get_mont_pay['EXEC_PAIMENT'] > 0)
            {
              if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
              {
                //mont paiement à soustraire
                $update_pay_mont = floatval($get_mont_pay['EXEC_PAIMENT']) - floatval($get_mont_pay['MONTANT_PAIEMENT']);
                $update_pay_mont_devise = floatval($get_mont_pay['EXEC_PAIEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_PAIEMENT_DEVISE']);
                $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
                $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

              }
              else
              {
                //mont paiement à soustraire
                $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT']; 
                $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
                $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
              }
            }

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            $updateTitr = $table_exec_titr_dec;
            $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
            $datatoupdateTitr = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
            $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
            $RequetePS = 'CALL updateData(?,?,?);';
            $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);
            
            //insertion des motifs
            if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
              foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
                $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id_titr_dec . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
              }
            }
            $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "'" . $id_titr_dec . "','" . $user_id . "','" . $ETAPE_ID . "','" . $date_reception . "','" . $date_transmission . "'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            $success = true;

          }
          if ($OPERATION === 3)
          {
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 2 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID.' AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $callpsreq = "CALL getRequete(?,?,?,?);";          
            $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
            $bindparams = str_replace("\\", "", $bindparamss);
            $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

            $table_exec = 'execution_budgetaire';
            $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$id;

            if($get_mont_pay['EXEC_PAIMENT'] > 0)
            {
              if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
              {
                //mont paiement à soustraire
                $update_pay_mont = floatval($get_mont_pay['EXEC_PAIMENT']) - floatval($get_mont_pay['MONTANT_PAIEMENT']);
                $update_pay_mont_devise = floatval($get_mont_pay['EXEC_PAIEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_PAIEMENT_DEVISE']);
                $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.', PAIEMENT_DEVISE='.$update_pay_mont_devise;
                $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

              }
              else
              {
                //mont paiement à soustraire
                $update_pay_mont = $get_mont_pay['EXEC_PAIMENT'] - $get_mont_pay['MONTANT_PAIEMENT']; 
                $datatomodifie_exec = 'PAIEMENT='.$update_pay_mont.'';
                $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
              }
            }

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            //Update de l'étape
            $updateTitr = $table_exec_titr_dec;
            $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
            $datatoupdateTitr = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
            $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
            $RequetePS = 'CALL updateData(?,?,?);';
            $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);
            
            //insertion des motifs
            if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
              foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
                $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id_titr_dec . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
              }
            }
            $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "".$id_titr_dec.",".$user_id."," . $ETAPE_ID . ",'" . $date_reception . "','" . $date_transmission . "'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            $success = true;

          }

          // pour verifier si ce visa
          elseif ($OPERATION === 2) 
          {            $
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', '1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0 AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            $devi_lik = $this->getBindParms('MONTANT_LIQUIDATION_DEVISE', 'execution_budgetaire_tache_detail', ' EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id, '1');
            $get_devi_lik = $this->ModelPs->getRequeteOne($psgetrequete, $devi_lik);

            if ($type_montant != 1) 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);


              //Update de l'étape
              $updateTitr = $table_exec_titr_dec;
              $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
              $datatoupdateTitr = 'MONTANT_PAIEMENT=' . $paiement_montant . ',MONTANT_PAIEMENT_DEVISE = ' . $get_devi_lik['MONTANT_LIQUIDATION_DEVISE'] . ', DATE_PAIEMENT = "' . $date_paiement_devise . '",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
              $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
              $RequetePS = 'CALL updateData(?,?,?);';
              $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);

              //update dans execution
              $nouveau_mont1=floatval($montant_paie['PAIEMENT_DEVISE'])+$get_devi_lik['MONTANT_LIQUIDATION_DEVISE'];
              $nouveau_bif=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
              $table_exec1='execution_budgetaire';
              $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifie_exec1= 'PAIEMENT_DEVISE="'.$nouveau_mont1.'",PAIEMENT="'.$nouveau_bif.'"';
              $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);

              //Update dans execution_tache
              $conditionsTask = 'EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifieTask = 'MONTANT_PAIEMENT=MONTANT_ORDONNANCEMENT, MONTANT_PAIEMENT_DEVISE=MONTANT_ORDONNANCEMENT_DEVISE';
              $this->update_all_table('execution_budgetaire_execution_tache',$datatomodifieTask,$conditionsTask);
            } 
            else 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

              //Update de l'étape
              $updateTitr = $table_exec_titr_dec;
              $critereTitr = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$id_titr_dec;
              $datatoupdateTitr = 'MONTANT_PAIEMENT=' . $paiement_montant . ', DATE_PAIEMENT = "'.$date_paiement_devise.'",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
              $bindparams = [$updateTitr,$datatoupdateTitr,$critereTitr];
              $RequetePS = 'CALL updateData(?,?,?);';
              $this->ModelPs->createUpdateDelete($RequetePS,$bindparams);

              //update dans execution
              $nouveau_mont1=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
              $table_exec1='execution_budgetaire';
              $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifie_exec1= 'PAIEMENT="'.$nouveau_mont1.'"';
              $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
            
              //Update dans execution_tache
              $conditionsTask = 'EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifieTask = 'MONTANT_PAIEMENT=MONTANT_ORDONNANCEMENT';
              $this->update_all_table('execution_budgetaire_execution_tache',$datatomodifieTask,$conditionsTask);

            }

            if($TYPE_ENGAGEMENT_ID == 1)
            {
              foreach($cart->contents() as $value)
              {
                $table_retenu = 'exec_budget_tache_detail_retenu_prise_charge';
                $column_insert_retenu = "EXECUTION_BUDGETAIRE_DETAIL_ID,TYPE_RETENU_PRISE_CHARGE_ID,MONTANT_RETENU";
                $datacolumsinsert_retenu ="{$execution_budgetaire_detail_id},{$value['TYPE_RETENU_PRISE_CHARGE_ID']},{$value['MONTANT_RETENU']}";
                $this->save_all_table($table_retenu,$column_insert_retenu, $datacolumsinsert_retenu);
              }
              $cart->destroy();
            }
            $columninserthist = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "".$id_titr_dec.",".$user_id.",".$ETAPE_ID.",'".$date_reception . "','".$date_transmission."'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            if (!empty($analyse)) {
              foreach ($analyse as $an) {
                $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
              }
            }
            $success = true;
          }
        }
      }
      return redirect('double_commande_new/Liste_Paiement/vue_correct_pc');
    }  
    else 
    {
      redirect("double_commande_new/Phase_comptable/correction_prise_en_charge/".md5($id_titr_dec));
    }
  }
  /* fin prise en charge correction*/


  /* Debut signature du titre par dir comptable*/
  public function reception_et_signature_titre($id)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=23','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";
          $bindparamss = $this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,td.MONTANT_DECAISSEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_PAIEMENT','execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID','md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"','exec.EXECUTION_BUDGETAIRE_ID DESC');
          $bindparams34 = str_replace("\\", "", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

          $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];
          
          //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          //récuperer l'étape à corriger
          $step_correct = $this->getBindParms('ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR', 'budgetaire_etape_retour_correction','ETAPE_RETOUR_CORRECTION_ID !=1','ETAPE_RETOUR_CORRECTION_ID ASC');
          $data['get_correct'] = $this->ModelPs->getRequete($callpsreq, $step_correct);

          $gettypevalidation = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','1','ID_OPERATION ASC');
          $data['type'] = $this->ModelPs->getRequete($callpsreq, $gettypevalidation);

          //Récuperer les motifs
          $bind_motif = $this->getBindParms('BUDGETAIRE_TYPE_ANALYSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE', 'budgetaire_type_analyse','1', 'BUDGETAIRE_TYPE_ANALYSE_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

          $detail = $this->detail_new($id);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_reception_et_signature_titre_view', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }    
  }


  

  public function save_reception_et_signature_titre()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $id = $this->request->getPost('id');
    $id_exec_titr_dec = $this->request->getPost('id_exec_titr_dec');
    $date_signature_titre = $this->request->getPost('date_signature_titre');
    $date_reception = $this->request->getPost('date_reception');
    $date_transmission = $this->request->getPost('date_transmission');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $ID_OPERATION = $this->request->getPost('ID_OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID');

    $rules = [
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'ID_OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    if($ID_OPERATION == 1 || $ID_OPERATION == 3)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

    }else if ($ID_OPERATION == 2)
    {
      $rules['date_signature_titre'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run()) {
      $psgetrequete = "CALL getRequete(?,?,?,?);";
      //si c'est visa
      if ($ID_OPERATION == 2) 
      {
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        //Update dans la table d'exécution_décaissement
        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
        $datatomodifie = 'DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR="'.$date_signature_titre . '",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.'';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id_exec_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        $data = ['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Liste_Paiement/vue_sign_dir_compt');
      } 
      //si c'est retour a la correction
      elseif ($ID_OPERATION == 1) 
      {
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION=1', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_exec_titr_dec;
        $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.'';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id_exec_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        //Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value) {
          $insertToTable_motif = '  execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id_exec_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
        $data = ['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liste_Paiement/vue_sign_dir_compt');
      }
      //si c'est annulation
      elseif ($ID_OPERATION == 3) 
      {
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION=2', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,DECAISSEMENT,DECAISSEMENT_DEVISE,exec.DEVISE_TYPE_ID,MONTANT_DECAISSEMENT,MONTANT_DECAISSEMENT_DEVISE','execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec .'','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
        $EXEC_BUDGET_RAC_ID = $get_mont_pay['EXECUTION_BUDGETAIRE_ID'];

        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;

        if($get_mont_pay['DECAISSEMENT'] > 0)
        {
          if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
          {
            //mont décaissement à soustraire
            $update_dec_mont = floatval($get_mont_pay['DECAISSEMENT']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT']);
            $update_dec_mont_devise = floatval($get_mont_pay['DECAISSEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT_DEVISE']);
            $datatomodifie_exec = 'DECAISSEMENT='.$update_dec_mont.', DECAISSEMENT_DEVISE='.$update_dec_mont_devise;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

          }
          else
          {
            //mont décaissement à soustraire
            $update_dec_mont = floatval($get_mont_pay['DECAISSEMENT']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT']);
            $datatomodifie_exec = 'DECAISSEMENT='.$update_dec_mont;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
          }
        }

        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_exec_titr_dec;
        $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_DECAISSEMENT=0, MONTANT_DECAISSEMENT_DEVISE=0';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id_exec_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        //Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value) {
          $insertToTable_motif = '  execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id_exec_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
        $data = ['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liste_Paiement/vue_sign_dir_compt');
      }
    } 
    else 
    {
      return $this->reception_et_signature_titre(md5($id));
    }
  }


  /* Fin signature du titre par dir comptable*/

  public function save_reception_et_signature_titre24()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }

    /*if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DIR_COMPTABILITE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }*/

    $id = $this->request->getPost('id');
    $date_signature_titre = $this->request->getPost('date_signature_titre');
    $date_reception = $this->request->getPost('date_reception');
    $date_transmission = $this->request->getPost('date_transmission');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $ID_OPERATION = $this->request->getPost('ID_OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID');

    $rules = [
      'date_signature_titre' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'ID_OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    if($ID_OPERATION == 1)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

    }

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run()) {
      $psgetrequete = "CALL getRequete(?,?,?,?);";
      //si c'est visa
      if ($ID_OPERATION == 2) 
      {

        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $table = 'execution_budgetaire_tache_detail';
        $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id;
        $datatomodifie = 'DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR="'.$date_signature_titre . '",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'"';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        $data = ['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Liste_Paiement');
      } 
      //si c'est retour a la correction
      elseif ($ID_OPERATION == 1) 
      {

        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION=1', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $table = 'execution_budgetaire_tache_detail';
        $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $id;
        $datatomodifie = 'DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR="'.$date_signature_titre .'",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'"';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        //Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value) {
          $insertToTable_motif = '  execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
        $data = ['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liste_Paiement');
      }
    } 
    else 
    {
      return $this->reception_et_signature_titre(md5($id));
    }
  }

  /* debut signature par dg finance*/
  public function analyse_depense($id)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=24','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";
          $bindparamss = $this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,td.MONTANT_DECAISSEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_PAIEMENT','execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID','md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"','exec.EXECUTION_BUDGETAIRE_ID DESC');
          $bindparams34 = str_replace("\\", "", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);
          $EXECUTION_BUDGETAIRE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];

          //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION,ETAPE_DOUBLE_COMMANDE_ID', 'execution_budgetaire_tache_detail_histo', 'MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);
          $etap_prev = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          //Requete pour les operation
          $get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION', ' budgetaire_type_operation_validation', '1', 'ID_OPERATION ASC');
          $get_oper = str_replace('\\', '', $get_oper);
          $data['operation'] = $this->ModelPs->getRequete($callpsreq, $get_oper);

          //Récuperer les motifs
          $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

          $detail = $this->detail_new($id);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_analyse_depense_view', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  //--------------fx save--------------------------
  public function save_analyse_depense()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_TITRE_SIGNATURE_DGFP') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $id = $this->request->getPost('id');
    $id_exec_titr_dec = $this->request->getPost('id_exec_titr_dec');
    $date_signature_titre = $this->request->getPost('date_signature_titre');
    $date_reception = $this->request->getPost('date_reception');
    $date_transmission = $this->request->getPost('date_transmission');
    $paiement = $this->request->getPost('paiement');
    $ID_OPERATION = $this->request->getPost('ID_OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');

    $psgetrequete = "CALL getRequete(?,?,?,?);";

    // requette pour recuperer l'etape suivante et mouvement_id  WHERE VISA DONC IS_CORRECTION =0;
    $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0', '1 ASC');
    $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

    $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'], 'ETAPE_DOUBLE_COMMANDE_ID ASC');
    $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

    // $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
    $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
    $EST_SUPERIEUR_CENT_MILLION = $get_next_step['EST_SUPERIEUR_CENT_MILLION'];

    $rules = [
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'ID_OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]

    ];

    if($ID_OPERATION == 1 || $ID_OPERATION == 3)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

    } else if ($ID_OPERATION == 2)
    {
      $rules['date_signature_titre'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }



    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run()) {
      if ($ID_OPERATION == 2) {
        if ($paiement > 100000000)
        {
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config','EST_SUPERIEUR_CENT_MILLION=1 and ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID.' AND IS_SALAIRE=0', '1 ASC');
          $get_next_stepsup = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $sup_million = $get_next_stepsup['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          $table = 'execution_budgetaire_titre_decaissement';
          $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
          $datatomodifie = 'DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE="'.$date_signature_titre.'",ETAPE_DOUBLE_COMMANDE_ID="'.$sup_million.'"';
          $this->update_all_table($table, $datatomodifie, $conditions);

          $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $datacolumsinsert = $id.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
          $this->save_histo($columsinsert, $datacolumsinsert);
        }
        else
        {
        $next_step_inf = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config', 'EST_SUPERIEUR_CENT_MILLION=0 and ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID.' AND IS_SALAIRE=0', '1 ASC');
          $get_next_stepinf = $this->ModelPs->getRequeteOne($psgetrequete, $next_step_inf);
          $inf_million = $get_next_stepinf['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
          $table = 'execution_budgetaire_titre_decaissement';
          $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
          $datatomodifie = 'DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE="'.$date_signature_titre.'",ETAPE_DOUBLE_COMMANDE_ID="'.$inf_million.'"';
          $this->update_all_table($table, $datatomodifie, $conditions);
          $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $datacolumsinsert = $id_exec_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
          $this->save_histo($columsinsert, $datacolumsinsert);
        }
      } elseif ($ID_OPERATION == 1) 
      {
        //---------------RETOUR A LA CORRECTION--------------------
        // requette pour recuperer l'etape suivante et mouvement_id  WHERE RETOUR A LA CORRECTION DONC IS_CORRECTION =1;
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION=1 AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        foreach ($TYPE_ANALYSE_MOTIF_ID as $value) 
        {
          $insertToTable_motif = '  execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id_exec_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
        $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.'';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id_exec_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);
      } elseif ($ID_OPERATION == 3) 
      {
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION=2 AND IS_SALAIRE=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,DECAISSEMENT,DECAISSEMENT_DEVISE,exec.DEVISE_TYPE_ID,MONTANT_DECAISSEMENT,MONTANT_DECAISSEMENT_DEVISE','execution_budgetaire_titre_decaissement det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID','det.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID =' . $id_exec_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
        $EXEC_BUDGET_RAC_ID = $get_mont_pay['EXECUTION_BUDGETAIRE_ID'];
        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
        if($get_mont_pay['DECAISSEMENT'] > 0)
        {
          //print_r($get_mont_pay);exit();
          if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
          {
            //mont décaissement à soustraire
            $update_dec_mont = floatval($get_mont_pay['DECAISSEMENT']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT']);
            $update_dec_mont_devise = floatval($get_mont_pay['DECAISSEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT_DEVISE']);
            $datatomodifie_exec = 'DECAISSEMENT='.$update_dec_mont.', DECAISSEMENT_DEVISE='.$update_dec_mont_devise;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

          }
          else
          {
            //mont décaissement à soustraire
            $update_dec_mont = floatval($get_mont_pay['DECAISSEMENT']) - floatval($get_mont_pay['MONTANT_DECAISSEMENT']);
            $datatomodifie_exec = 'DECAISSEMENT='.$update_dec_mont;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec); 
          }
        }

        foreach ($TYPE_ANALYSE_MOTIF_ID as $value) 
        {
          $insertToTable_motif = '  execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id_exec_titr_dec . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ='.$id_exec_titr_dec;
        $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_DECAISSEMENT=0, MONTANT_DECAISSEMENT_DEVISE=0';
        $this->update_all_table($table, $datatomodifie, $conditions);
        $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id_exec_titr_dec.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);
      }
      return redirect('double_commande_new/Liste_Paiement/vue_sign_dgfp');
    } 
    else 
    {
      return $this->analyse_depense(md5($id_exec_titr_dec));
    }
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
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  public function save_info_sup($columsinsert, $datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $table = 'execution_budgetaire_raccrochage_activite_info_suppl';
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $this->ModelPs->createUpdateDelete($insertReqAgence, $bindparms);
  }

  public function save_all_table($table, $columsinsert, $datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams = [$table, $columsinsert, $datacolumsinsert];
    $result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
    return $id = $result['id'];
  }

  public function save_histo($columsinsert, $datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $table = ' execution_budgetaire_tache_detail_histo';
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $this->ModelPs->createUpdateDelete($insertReqAgence, $bindparms);
  }

  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  public function uploadFile($fieldName, $folder, $prefix = ''): string
  {
    $prefix = ($prefix === '') ? uniqid() : $prefix;
    $path = '';

    $file = $this->request->getFile($fieldName);

    if ($file->isValid() && !$file->hasMoved())
    {
      $newName = uniqid() . '' . date('ymdhis') . '.' . $file->getExtension();
      $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
      $path = 'double_commande/' . $folder . '/' . $newName;
    }
    return $newName;
  }

  private function infosup_data ($id_raccrochage_detail) 
  {
    $table="execution_budgetaire_tache_info_suppl suppl JOIN execution_budgetaire exec ON suppl.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN type_beneficiaire ON type_beneficiaire.TYPE_BENEFICIAIRE_ID=suppl.TYPE_BENEFICIAIRE_ID";
    $columnselect="suppl.TYPE_BENEFICIAIRE_ID,type_beneficiaire.DESC_TYPE_BENEFICIAIRE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT";
    $where="det.EXECUTION_BUDGETAIRE_DETAIL_ID='".$id_raccrochage_detail."'";
    $orderby = 'det.EXECUTION_BUDGETAIRE_ID DESC';
    $where = str_replace("\'", "'", $where);
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    $bindparams = str_replace("\'", "'", $bindparams);
    return $this->ModelPs->getRequeteOne("CALL `getRequete`(?,?,?,?);", $bindparams);
  }
}
?>