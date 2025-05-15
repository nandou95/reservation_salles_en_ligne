<?php
/**RUGAMBA Jean Vainqueur
*Titre: Phase administrative
*Numero de telephone: (+257) 66 33 43 25
*WhatsApp: (+257) 62 47 19 15
*Email: jean.vainqueur@mediabox.bi
*Date: 24 Octobre,2023
**/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Ordonnancement extends BaseController
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
    $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','IS_MARCHE=0','DESC_TYPE_ANALYSE_MOTIF ASC');
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

  //Interface d'ordonnancement, etape 9 par le ministre
  function vue_ordonnance_ministre($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data = $this->urichk();

    ################################ DETAILS D'INFOS ############################
    $getdemanande = "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,tache.SOUS_TUTEL_ID, exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,execdet.MONTANT_LIQUIDATION,inst.DESCRIPTION_INSTITUTION,inst.CODE_INSTITUTION,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION, act.DESC_PAP_ACTIVITE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE,exec.DATE_DEMANDE AS DATE_ENGAGEMENT_BUDGETAIRE,execdet.DATE_LIQUIDATION,ebtd.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_BUDGETAIRE_DEVISE,tache.INSTITUTION_ID,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION_DEVISE,exec.LIQUIDATION_TYPE_ID ,exec.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=tache.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=tache.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=tache.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl info ON exec.EXECUTION_BUDGETAIRE_ID=info.EXECUTION_BUDGETAIRE_ID WHERE 1 AND MD5(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'";

    $getdemanande = 'CALL `getTable`("'.$getdemanande.'");';

    $data['details'] = $this->ModelPs->getRequeteOne($getdemanande);

    $det=$data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {

        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $psgetrequete = "CALL `getRequete`(?,?,?,?);";

          //récuperer les operations de validation
          $oper = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','ID_OPERATION < 3','DESCRIPTION ASC');
          $data['operation'] = $this->ModelPs->getRequete($psgetrequete, $oper);

          //récuperer l'étape à corriger
          // $step_correct = $this->getBindParms('ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR', 'budgetaire_etape_retour_correction','ETAPE_RETOUR_CORRECTION_ID > 1','ETAPE_RETOUR_CORRECTION_ID ASC');
          // $data['get_correct'] = $this->ModelPs->getRequete($psgetrequete, $step_correct);

          //Récuperer les motifs
          $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','IS_MARCHE=0','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);

          //récuperer les types bénéficiaires
          $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','1','DESC_TYPE_BENEFICIAIRE ASC');
          $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);

          //récuperer les fournisseurs/acquéreurs
          $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
          $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

          //Le titre de l'étape
          $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
          $bind_step_title = str_replace('\\','',$bind_step_title);
          $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

          //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\','',$bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

          //Récupération du sous titre
          $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='. $data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');

          $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

          $data['EXEC_BUDGET_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
          $data['EXEC_BUDGET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
          $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

          #################### FIN D'INFOS #########################################

          $data['file_error'] = '';

          $detail=$this->detail_new(MD5($data['details']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];

          return view('App\Modules\double_commande_new\Views\Ordonnance_Min_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }  
  }

  //Interface d'ordonnancement, etape par le DG Budget
  function vue_ordonnance_dgbudget($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data = $this->urichk();

    ################################ DETAILS D'INFOS ############################
    $getdemanande = "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,tache.SOUS_TUTEL_ID, exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,execdet.MONTANT_LIQUIDATION,inst.DESCRIPTION_INSTITUTION,inst.CODE_INSTITUTION,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION, act.DESC_PAP_ACTIVITE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE,exec.DATE_DEMANDE AS DATE_ENGAGEMENT_BUDGETAIRE,execdet.DATE_LIQUIDATION,ebtd.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_BUDGETAIRE_DEVISE,tache.INSTITUTION_ID,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION_DEVISE,exec.LIQUIDATION_TYPE_ID ,exec.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=tache.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=tache.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=tache.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl info ON exec.EXECUTION_BUDGETAIRE_ID=info.EXECUTION_BUDGETAIRE_ID WHERE 1 AND MD5(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'";

    $getdemanande = 'CALL `getTable`("'.$getdemanande.'");';

    $data['details'] = $this->ModelPs->getRequeteOne($getdemanande);

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $psgetrequete = "CALL `getRequete`(?,?,?,?);";

          //récuperer les operations de validation
          $oper = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','1','DESCRIPTION ASC');
          $data['operation'] = $this->ModelPs->getRequete($psgetrequete, $oper);

          //Récuperer les motifs
          $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF',' budgetaire_type_analyse_motif','IS_MARCHE=0','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);

          //récuperer les types bénéficiaires
          $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','1','DESC_TYPE_BENEFICIAIRE ASC');
          $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);

          //récuperer les fournisseurs/acquéreurs
          $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
          $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

          //Le titre de l'étape
          $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
          $bind_step_title = str_replace('\\','',$bind_step_title);
          $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

          //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\','',$bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

          //Récupération du sous titre
          $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='.$data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');

          $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

          $data['EXEC_BUDGET_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
          $data['EXEC_BUDGET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
          $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

          ##################### FIN D'INFOS #########################################
          $data['file_error'] = '';

          $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_DET_ID']);
          $detail=$this->detail_new(MD5($data['details']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['get_infoEBET']=$detail['get_infoEBET'];
          return view('App\Modules\double_commande_new\Views\Ordonnance_DG_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }   
  }

  //Enregistrement de l'étape 9 Ordonnancement - Ministre
  function update_etape9()
  {
    $session  = \Config\Services::session();
    $USER_ID_ENG='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID_ENG = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $db = db_connect();

    $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
    $EXEC_BUDGET_DET_ID = $this->request->getPost('EXEC_BUDGET_DET_ID');
    $EXEC_BUDGET_ID = $this->request->getPost('EXEC_BUDGET_ID');

    $MONTANT_EN_BIF = preg_replace('/\s/', '', $this->request->getPost('MONTANT_EN_BIF'));
    $MONTANT_EN_DEVISE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_DEVISE_ORDONNANCEMENT'));

    $DATE_HEURE_ORDONNANCE = $this->request->getPost('DATE_HEURE_ORDONNANCE');
    $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
    $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
    $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
    // $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $OPERATION_ID = $this->request->getPost('OPERATION_ID');
    $ID_TYPE_LIQUIDATION = $this->request->getPost('ID_TYPE_LIQUIDATION');

    //get montant existant dans execution_budgetaire
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $mont_ordo_exista = $this->getBindParms('ORDONNANCEMENT,ORDONNANCEMENT_DEVISE','execution_budgetaire','EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_ID,'1 DESC');
    $montant_ordo = $this->ModelPs->getRequeteOne($psgetrequete, $mont_ordo_exista);

    //Form validation
    $rules = [
      'OPERATION_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'DATE_TRANSMISSION' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'DATE_RECEPTION' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

    ];

    if($OPERATION_ID == 1 || $OPERATION_ID == 3)
    {
      // $rules['ETAPE_RETOUR_CORRECTION_ID'] = [
      //   'label' => '',
      //   'rules' => 'required',
      //   'errors' => [
      //     'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
      //   ]
      // ];

    }else{
      $rules['DATE_HEURE_ORDONNANCE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $TYPE_MONNAIE = $this->request->getPost('MONNAIE');  

      $file = $this->request->getFile('PATH_DOCUMENT');

      if (!$file || !$file->isValid())
      {
        $file_error = '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>';
      }else{

        $maxFileSize = 200 * 1024;
        if ($file->getSize() > $maxFileSize)
        {
          $file_error = '<font style="color:red;size:2px;">'.lang('messages_lang.taille_maximale').' (200 KB)</font>';

        }else{

          $file_error = '';
        }
      }
    }

    $this->validation->setRules($rules);
    if($this->validation->withRequest($this->request)->run())
    {
      if($OPERATION_ID == 1)
      {
        //Enregistrement du montant juridique dans raccrochage activité
        $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

          //récuperer les etapes et mouvements
        $psgetrequete = "CALL `getRequete`(?,?,?,?);";
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant

          //Enregistrement dans historique vérification des motifs
          foreach($TYPE_ANALYSE_MOTIF_ID as $value)
          {
            $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
            $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
            $datatoinsert_histo_motif = "".$value.",".$ETAPE_ID.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
            $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
          }

          $table3='execution_budgetaire_titre_decaissement';
          $conditions3='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
          $datatomodifie3= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
          $this->update_all_table($table3,$datatomodifie3,$conditions3);

          //Enregistrement dans execution
          $table_new='execution_budgetaire';
          $conditions_new='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_ID;
          $datatomodifie_new='IS_FINISHED=0';
          $this->update_all_table($table_new,$datatomodifie_new,$conditions_new);
        }
        else if($OPERATION_ID == 3)
        {
        //Enregistrement du montant juridique dans raccrochage activité
          $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

        //récuperer les etapes et mouvements
          $psgetrequete = "CALL `getRequete`(?,?,?,?);";
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=2 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant

        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID','ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID .'','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_ID;

        if($get_mont_pay['EXEC_ORDONNANCEMENT'] > 0)
        {
          if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
          {
            //mont ordonnancement à soustraire
            $update_ordo_mont = floatval($get_mont_pay['EXEC_ORDONNANCEMENT']) - floatval($get_mont_pay['MONTANT_ORDONNANCEMENT']);
            $update_ordo_mont_devise = floatval($get_mont_pay['EXEC_ORDONNANCEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_ORDONNANCEMENT_DEVISE']);
            $datatomodifie_exec = 'ORDONNANCEMENT='.$update_ordo_mont.', ORDONNANCEMENT_DEVISE='.$update_ordo_mont_devise;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
          }
          else
          {
            //mont ordonnancement à soustraire
            $update_ordo_mont = $get_mont_pay['EXEC_ORDONNANCEMENT'] - $get_mont_pay['MONTANT_ORDONNANCEMENT'];
            $datatomodifie_exec = 'ORDONNANCEMENT='.$update_ordo_mont;
            $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
            
          }
        }


        //Enregistrement dans historique vérification des motifs
        foreach($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "".$value.",".$ETAPE_ID.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
          $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
        }

        $table3='execution_budgetaire_tache_detail';
        $conditions3='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_DET_ID;
        $datatomodifie3= 'MONTANT_ORDONNANCEMENT=0,MONTANT_ORDONNANCEMENT_DEVISE=0';
        $this->update_all_table($table3,$datatomodifie3,$conditions3);

        $tableEBTD='execution_budgetaire_titre_decaissement';
        $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
        $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

        //Enregistrement dans execution
        $table_new='execution_budgetaire';
        $conditions_new='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_ID;
        $datatomodifie_new='IS_FINISHED=0';
        $this->update_all_table($table_new,$datatomodifie_new,$conditions_new);
      }
      else{
        //Enregistrement du montant ordonnancement dans raccrochage activité
        $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

        //récuperer les etapes et mouvements
        $psgetrequete = "CALL getRequete(?,?,?,?);";

        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
        $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $MONTANT_EN_BIF = floatval( $MONTANT_EN_BIF);
        $MONTANT_EN_DEVISE = floatval( $MONTANT_EN_DEVISE);

        if($TYPE_MONNAIE != 1)
        {
          //Details
          $table4='execution_budgetaire_tache_detail';
          $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_DET_ID;
          $datatomodifie4= 'MONTANT_ORDONNANCEMENT="'.$MONTANT_EN_BIF.'",DATE_ORDONNANCEMENT="'.$DATE_HEURE_ORDONNANCE.'", MONTANT_ORDONNANCEMENT_DEVISE='.$MONTANT_EN_DEVISE;
          $this->update_all_table($table4,$datatomodifie4,$conditions4);

          $tableEBTD='execution_budgetaire_titre_decaissement';
          $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
          $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
          $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

          //Enregistrement des infos supplémentaires
          $PATH_DOCUMENT = $this->request->getPost('PATH_DOCUMENT');
          $BORDEREAU=$this->uploadFile('PATH_DOCUMENT','double_commande_new','BORDEREAU');

          $table_info='execution_budgetaire_tache_info_suppl';
          $conditions_info='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_ID;
          $datatomodifie_info= 'PATH_BORDEREAU_ENGAGEMENT="'.$BORDEREAU.'"';
          $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);

              //update dans execution
          $nouveau_mont1=floatval($montant_ordo['ORDONNANCEMENT_DEVISE'])+$MONTANT_EN_DEVISE;
          $nouveau_bif=floatval($montant_ordo['ORDONNANCEMENT'])+$MONTANT_EN_BIF;
          $table_exec1='execution_budgetaire';
          $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_ID;
          $datatomodifie_exec1= 'ORDONNANCEMENT_DEVISE="'.$nouveau_mont1.'",ORDONNANCEMENT="'.$nouveau_bif.'"';
          $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
        }
        else
        {
          //Details
          $table4='execution_budgetaire_tache_detail';
          $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_DET_ID;
          $datatomodifie4= 'MONTANT_ORDONNANCEMENT="'.$MONTANT_EN_BIF.'",DATE_ORDONNANCEMENT="'.$DATE_HEURE_ORDONNANCE.'"';
          $this->update_all_table($table4,$datatomodifie4,$conditions4);

          $tableEBTD='execution_budgetaire_titre_decaissement';
          $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
          $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
          $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

          //Enregistrement des infos supplémentaires (document de bordereau)
          $PATH_DOCUMENT = $this->request->getPost('PATH_DOCUMENT');
          $BORDEREAU=$this->uploadFile('PATH_DOCUMENT','double_commande_new','BORDEREAU');

          $table_info='execution_budgetaire_tache_detail';
          $conditions_info='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_DET_ID;
          $datatomodifie_info= 'PATH_BON_ENGAGEMENT="'.$BORDEREAU.'"';
          $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);

              //update dans execution
          $nouveau_mont1=floatval($montant_ordo['ORDONNANCEMENT'])+$MONTANT_EN_BIF;
          $table_exec1='execution_budgetaire';
          $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_ID;
          $datatomodifie_exec1= 'ORDONNANCEMENT="'.$nouveau_mont1.'"';
          $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
        }
      }

      // update activite execution et execution brut
      // $this->update_montant_execution_budgetaire($EXEC_BUDGET_DET_ID,$EXEC_BUDGET_RAC_ID,'ORDONNANCEMENT','MONTANT_RACCROCHE_ORDONNANCEMENT',$MONTANT_EN_BIF,$MONTANT_EN_DEVISE,$TYPE_MONNAIE);

      //Enregistrement dans historique raccrochage
      $TYPE_RACCROCHAGE_ID =2;
      $columsinsert_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID, ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION,OBSERVATION";

      $datatoinsert_histo="".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$USER_ID_ENG.",".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."','".str_replace("'", "\'", $COMMENTAIRE)."'";

      $table_histo='execution_budgetaire_tache_detail_histo';
      $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);

      $data = [
        'message' => ''.lang('messages_lang.Enregistrer_succes_msg').''
      ];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Ordonnancement_Double_Commande/get_ordon_Afaire');
    }
    else
    {
        $session  = \Config\Services::session();
        $user_id ='';
        if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
        {
          $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
        }
        else
        {
          return  redirect('Login_Ptba/do_logout');
        }

        if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
        {
          return redirect('Login_Ptba/homepage'); 
        }

        $data = $this->urichk();

        ################################ DETAILS D'INFOS ############################
        $getdemanande = "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,tache.SOUS_TUTEL_ID, exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,execdet.MONTANT_LIQUIDATION,inst.DESCRIPTION_INSTITUTION,inst.CODE_INSTITUTION,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION, act.DESC_PAP_ACTIVITE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE,exec.DATE_DEMANDE AS DATE_ENGAGEMENT_BUDGETAIRE,execdet.DATE_LIQUIDATION,ebtd.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_BUDGETAIRE_DEVISE,tache.INSTITUTION_ID,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION_DEVISE,exec.LIQUIDATION_TYPE_ID ,exec.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=tache.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=tache.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=tache.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl info ON exec.EXECUTION_BUDGETAIRE_ID=info.EXECUTION_BUDGETAIRE_ID WHERE 1 AND MD5(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'";

        $getdemanande = 'CALL `getTable`("'.$getdemanande.'");';

        $data['details'] = $this->ModelPs->getRequeteOne($getdemanande);

        $det=$data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];

        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {

            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $psgetrequete = "CALL `getRequete`(?,?,?,?);";

              //récuperer les operations de validation
              $oper = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','ID_OPERATION < 3','DESCRIPTION ASC');
              $data['operation'] = $this->ModelPs->getRequete($psgetrequete, $oper);

              //récuperer l'étape à corriger
              $step_correct = $this->getBindParms('ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR', 'budgetaire_etape_retour_correction','ETAPE_RETOUR_CORRECTION_ID > 1','ETAPE_RETOUR_CORRECTION_ID ASC');
              $data['get_correct'] = $this->ModelPs->getRequete($psgetrequete, $step_correct);

              //Récuperer les motifs
              $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','IS_MARCHE=0','TYPE_ANALYSE_MOTIF_ID ASC');
              $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);

              //récuperer les types bénéficiaires
              $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','1','DESC_TYPE_BENEFICIAIRE ASC');
              $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);

              //récuperer les fournisseurs/acquéreurs
              $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
              $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

              //Le titre de l'étape
              $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
              $bind_step_title = str_replace('\\','',$bind_step_title);
              $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

              //Le min de la date de réception
              $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','DATE_INSERTION DESC');
              $bind_date_histo = str_replace('\\','',$bind_date_histo);
              $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

              //Récupération du sous titre
              $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='. $data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');

              $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

              $data['EXEC_BUDGET_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
              $data['EXEC_BUDGET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
              $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

              #################### FIN D'INFOS #########################################

              $data['file_error'] = '';

              $detail=$this->detail_new(MD5($data['details']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
              $data['get_info']=$detail['get_info'];
              $data['montantvote']=$detail['montantvote'];
              $data['get_infoEBET']=$detail['get_infoEBET'];

              return view('App\Modules\double_commande_new\Views\Ordonnance_Min_View',$data);
            }  
          }
          return redirect('Login_Ptba/homepage'); 
        }
        else
        {
          return redirect('Login_Ptba/homepage');
        }  
    }
  }

  //Enregistrement de l'étape 9 Ordonnancement - DG Budget
  function update_etapeDG()
  {
    $session  = \Config\Services::session();
    $USER_ID_ENG='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID_ENG = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $db = db_connect();

    $EXEC_BUDGET_DET_ID = $this->request->getPost('EXEC_BUDGET_DET_ID');
    $EXEC_BUDGET_ID = $this->request->getPost('EXEC_BUDGET_ID');
    $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
    
    $MONTANT_EN_BIF = preg_replace('/\s/', '', $this->request->getPost('MONTANT_EN_BIF'));
    $MONTANT_EN_DEVISE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_DEVISE_ORDONNANCEMENT'));

    $DATE_HEURE_ORDONNANCE = $this->request->getPost('DATE_HEURE_ORDONNANCE');
    $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
    $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
    $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
    // $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $OPERATION_ID = $this->request->getPost('OPERATION_ID');
    $ID_TYPE_LIQUIDATION = $this->request->getPost('LIQUIDATION_TYPE_ID');

    //get montant existant dans execution_budgetaire
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $mont_ordo_exista = $this->getBindParms('ORDONNANCEMENT,ORDONNANCEMENT_DEVISE','execution_budgetaire','EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_ID,'1 DESC');
    $montant_ordo = $this->ModelPs->getRequeteOne($psgetrequete, $mont_ordo_exista);

    //Form validation
    $rules = [
      'OPERATION_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'DATE_TRANSMISSION' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'DATE_RECEPTION' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

    ];

    if($OPERATION_ID == 1 || $OPERATION_ID == 3)
    {
    }else{

     $rules['DATE_HEURE_ORDONNANCE'] = [
      'label' => '',
      'rules' => 'required',
      'errors' => [
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
      ]
    ];

    $TYPE_MONNAIE = $this->request->getPost('MONNAIE');  


    $file = $this->request->getFile('PATH_DOCUMENT');

    if (!$file || !$file->isValid())
    {
      $file_error = '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>';
    }else{

      $maxFileSize = 200 * 1024;
      if ($file->getSize() > $maxFileSize)
      {
        $file_error = '<font style="color:red;size:2px;">'.lang('messages_lang.taille_maximale').' (200 KB)</font>';
        
      }else{

        $file_error = '';
      }
    }
  }

  $this->validation->setRules($rules);
  if($this->validation->withRequest($this->request)->run())
  {
    if($OPERATION_ID == 1)
    {
      //Enregistrement du montant juridique dans raccrochage activité
      $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 
      $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
      $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant

        //Enregistrement dans historique vérification des motifs
        foreach($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "".$value.",".$ETAPE_ID.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
          $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
        }

        $table3='execution_budgetaire_titre_decaissement';
        $conditions3='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $datatomodifie3= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
        $this->update_all_table($table3,$datatomodifie3,$conditions3);

        //Enregistrement dans exec budg
        $table_new='execution_budgetaire';
        $conditions_new='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_ID;
        $datatomodifie_new='IS_FINISHED=0';
        $this->update_all_table($table_new,$datatomodifie_new,$conditions_new);
    }else if($OPERATION_ID == 3)
    {
      //Enregistrement du montant juridique dans raccrochage activité
      $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

      //récuperer les etapes et mouvements
      $psgetrequete = "CALL `getRequete`(?,?,?,?);";
      $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=2 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
      $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
      $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant

      $callpsreq = "CALL getRequete(?,?,?,?);";          
      $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID','det.EXECUTION_BUDGETAIRE_DETAIL_ID=' . $EXEC_BUDGET_DET_ID .'','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
      $bindparams = str_replace("\\", "", $bindparamss);
      $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

      $table_exec = 'execution_budgetaire';
      $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_ID;

      if($get_mont_pay['EXEC_ORDONNANCEMENT'] > 0)
      {
        if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
        {
          //mont ordonnancement à soustraire
          $update_ordo_mont = floatval($get_mont_pay['EXEC_ORDONNANCEMENT']) - floatval($get_mont_pay['MONTANT_ORDONNANCEMENT']);
          $update_ordo_mont_devise = floatval($get_mont_pay['EXEC_ORDONNANCEMENT_DEVISE']) - floatval($get_mont_pay['MONTANT_ORDONNANCEMENT_DEVISE']);
          $datatomodifie_exec = 'ORDONNANCEMENT='.$update_ordo_mont.', ORDONNANCEMENT_DEVISE='.$update_ordo_mont_devise;
          $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

        }
        else
        {
          //mont ordonnancement à soustraire
          $update_ordo_mont = $get_mont_pay['EXEC_ORDONNANCEMENT'] - $get_mont_pay['MONTANT_ORDONNANCEMENT'];
          $datatomodifie_exec = 'ORDONNANCEMENT='.$update_ordo_mont;
          $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
          
        }
      }


      //Enregistrement dans historique vérification des motifs
      foreach($TYPE_ANALYSE_MOTIF_ID as $value)
      {
        $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
        $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
        $datatoinsert_histo_motif = "".$value.",".$ETAPE_ID.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
        $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
      }

      $table3='execution_budgetaire_tache_detail';
      $conditions3='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_DET_ID;
      $datatomodifie3= 'MONTANT_ORDONNANCEMENT=0,MONTANT_ORDONNANCEMENT_DEVISE=0';
      $this->update_all_table($table3,$datatomodifie3,$conditions3);

      //Enregistrement dans execution_budgetaire_titre_decaissement
      $tableEBTD='execution_budgetaire_titre_decaissement';
      $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
      $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

      //Enregistrement dans execution
      $table_new='execution_budgetaire';
      $conditions_new='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_ID;
      $datatomodifie_new='IS_FINISHED=0';
      $this->update_all_table($table_new,$datatomodifie_new,$conditions_new);
    }
    else
    {
        //Enregistrement du montant ordonnancement dans raccrochage activité
        $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

        //récuperer les etapes et mouvements    

        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
        $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
        
        $MONTANT_EN_BIF = floatval( $MONTANT_EN_BIF);
        $MONTANT_EN_DEVISE = floatval( $MONTANT_EN_DEVISE);
        
        if($TYPE_MONNAIE != 1)
        {
          //Details
          $table4='execution_budgetaire_tache_detail';
          $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_DET_ID;
          $datatomodifie4= 'MONTANT_ORDONNANCEMENT="'.$MONTANT_EN_BIF.'",DATE_ORDONNANCEMENT="'.$DATE_HEURE_ORDONNANCE.'", MONTANT_ORDONNANCEMENT_DEVISE='.$MONTANT_EN_DEVISE;
          $this->update_all_table($table4,$datatomodifie4,$conditions4);

          //Enregistrement dans execution_budgetaire_titre_decaissement
          $tableEBTD='execution_budgetaire_titre_decaissement';
          $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
          $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
          $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

          //update execution_bugetaire_execution_tache
          $tableEBET='execution_budgetaire_execution_tache';
          $conditionsEBET='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_ID;
          $datatomodifieEBET= 'MONTANT_ORDONNANCEMENT=MONTANT_LIQUIDATION, MONTANT_ORDONNANCEMENT_DEVISE=MONTANT_LIQUIDATION_DEVISE';
          $this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);

          //Enregistrement des infos supplémentaires
          $PATH_DOCUMENT = $this->request->getPost('PATH_DOCUMENT');
          $BORDEREAU=$this->uploadFile('PATH_DOCUMENT','double_commande_new','BORDEREAU');
          
          $table_info='execution_budgetaire_tache_detail';
          $conditions_info='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_DET_ID;
          $datatomodifie_info= 'PATH_BON_ENGAGEMENT="'.$BORDEREAU.'"';
          $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);

          //update dans execution
          $nouveau_mont1=floatval($montant_ordo['ORDONNANCEMENT_DEVISE'])+$MONTANT_EN_DEVISE;
          $nouveau_bif=floatval($montant_ordo['ORDONNANCEMENT'])+$MONTANT_EN_BIF;
          $table_exec1='execution_budgetaire';
          $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_ID;
          $datatomodifie_exec1= 'ORDONNANCEMENT_DEVISE="'.$nouveau_mont1.'",ORDONNANCEMENT="'.$nouveau_bif.'"';
          $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
          // print($nouveau_mont1);die();
        }
        else{
          //Details
          $table4='execution_budgetaire_tache_detail';
          $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_DET_ID;
          $datatomodifie4= 'MONTANT_ORDONNANCEMENT="'.$MONTANT_EN_BIF.'",DATE_ORDONNANCEMENT="'.$DATE_HEURE_ORDONNANCE.'"';
          $this->update_all_table($table4,$datatomodifie4,$conditions4);

          //Enregistrement dans execution_budgetaire_titre_decaissement
          $tableEBTD='execution_budgetaire_titre_decaissement';
          $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
          $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
          $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

          //update execution_bugetaire_execution_tache
          $tableEBET='execution_budgetaire_execution_tache';
          $conditionsEBET='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_ID;
          $datatomodifieEBET= 'MONTANT_ORDONNANCEMENT=MONTANT_LIQUIDATION, MONTANT_ORDONNANCEMENT_DEVISE=MONTANT_LIQUIDATION_DEVISE';
          $this->update_all_table($tableEBET,$datatomodifieEBET,$conditionsEBET);


          //Enregistrement des infos supplémentaires (document de bordereau)
          $PATH_DOCUMENT = $this->request->getPost('PATH_DOCUMENT');
          $BORDEREAU=$this->uploadFile('PATH_DOCUMENT','double_commande_new','BORDEREAU');
          
          $table_info='execution_budgetaire_tache_detail';
          $conditions_info='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_DET_ID;
          $datatomodifie_info= 'PATH_BON_ENGAGEMENT="'.$BORDEREAU.'"';
          $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);

          //update dans execution
          $nouveau_mont=$montant_ordo['ORDONNANCEMENT']+$MONTANT_EN_BIF;
          $table_exec='execution_budgetaire';
          $conditions_exec='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_ID;
          $datatomodifie_exec= 'ORDONNANCEMENT="'.$nouveau_mont.'"';
          $this->update_all_table($table_exec,$datatomodifie_exec,$conditions_exec);          
        }
      }
      // update activite execution et execution brut
      $TYPE_MONNAIE = $this->request->getPost('MONNAIE');

      //Enregistrement dans historique raccrochage
      $columsinsert_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION,OBSERVATION";

      $datatoinsert_histo="".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$USER_ID_ENG.",".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."','".str_replace("'", "\'", $COMMENTAIRE)."'";

      $table_histo='execution_budgetaire_tache_detail_histo';
      $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);

      $data = [
        'message' => ''.lang('messages_lang.Enregistrer_succes_msg').''
      ];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Ordonnancement_Double_Commande/get_ordon_Afaire');

  }
  else{
      $session  = \Config\Services::session();
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      
      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, execdet.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,tache.SOUS_TUTEL_ID, exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,execdet.MONTANT_LIQUIDATION,inst.DESCRIPTION_INSTITUTION,inst.CODE_INSTITUTION,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION, act.DESC_PAP_ACTIVITE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE,exec.DATE_DEMANDE AS DATE_ENGAGEMENT_BUDGETAIRE,execdet.DATE_LIQUIDATION,ebtd.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_BUDGETAIRE_DEVISE,tache.INSTITUTION_ID,exec.ENG_JURIDIQUE_DEVISE,execdet.MONTANT_LIQUIDATION_DEVISE,exec.LIQUIDATION_TYPE_ID ,exec.DEVISE_TYPE_ID FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=tache.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=tache.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=tache.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl info ON exec.EXECUTION_BUDGETAIRE_ID=info.EXECUTION_BUDGETAIRE_ID WHERE 1 AND MD5(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'";

      $getdemanande = 'CALL `getTable`("'.$getdemanande.'");';

      $data['details'] = $this->ModelPs->getRequeteOne($getdemanande);

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $psgetrequete = "CALL `getRequete`(?,?,?,?);";

            //récuperer les operations de validation
            $oper = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','ID_OPERATION < 3','DESCRIPTION ASC');
            $data['operation'] = $this->ModelPs->getRequete($psgetrequete, $oper);

            //récuperer l'étape à corriger
            $step_correct = $this->getBindParms('ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR', 'budgetaire_etape_retour_correction','ETAPE_RETOUR_CORRECTION_ID > 1','ETAPE_RETOUR_CORRECTION_ID ASC');
            $data['get_correct'] = $this->ModelPs->getRequete($psgetrequete, $step_correct);

            //Récuperer les motifs
            $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF',' budgetaire_type_analyse_motif','IS_MARCHE=0','TYPE_ANALYSE_MOTIF_ID ASC');
            $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);

            //récuperer les types bénéficiaires
            $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','1','DESC_TYPE_BENEFICIAIRE ASC');
            $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);

            //récuperer les fournisseurs/acquéreurs
            $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
            $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            //Récupération du sous titre
            $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='.$data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');

            $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

            $data['EXEC_BUDGET_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ##################### FIN D'INFOS #########################################
            $data['file_error'] = '';

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_DET_ID']);
            $detail=$this->detail_new(MD5($data['details']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));
            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['get_infoEBET']=$detail['get_infoEBET'];

            return view('App\Modules\double_commande_new\Views\Ordonnance_DG_View',$data);
          }  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }
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
// code...
  $db = db_connect();
// print_r($db->lastQuery);die();
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
  return $newName;
}

}
?>