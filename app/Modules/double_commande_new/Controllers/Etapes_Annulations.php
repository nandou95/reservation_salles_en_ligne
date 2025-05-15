<?php
  /**RUGAMBA Jean Vainqueur
    *Titre: Etapes d'annulation
    *Numero de telephone: (+257) 66 33 43 25
    *WhatsApp: (+257) 62 47 19 15
    *Email: jean.vainqueur@mediabox.bi
    *Date: 26 Août,2024
    **/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;

  class Etapes_Annulations extends BaseController
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

    //Interface d'annulation au niveau de l'engagement budgétaire
    function rejeter_budget($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=0)
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      
      if(empty($user_id))
      {
        return  redirect('Login_Ptba/do_logout');
      }

      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID, tache.SOUS_TUTEL_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION,actions.ACTION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE, ebtd.ETAPE_DOUBLE_COMMANDE_ID, exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,tache.INSTITUTION_ID,tache.SOUS_TUTEL_ID FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=tache.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=tache.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'";
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
            //Récuperation de l'étape précedent
            $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
            $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
            $etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
            $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."' AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
            $motif_rejetRqt = 'CALL `getTable`("' . $motif_rejet . '");';
            $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);


            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            //Récuperer les motifs
            $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
            $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ########################## FIN D'INFOS #####################

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new(md5($data['details']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['get_infoEBET']=$detail['get_infoEBET'];

            return view('App\Modules\double_commande_new\Views\Etapes_Annulations_Budget_View',$data);
          }  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }   
    }

    //Enregistrement de l'annulation au niveau de l'engagement budgétaire
    function save_rejeter_budget()
    {
      $session  = \Config\Services::session();
      $USER_ID= $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if(empty($USER_ID))
      {
        return  redirect('Login_Ptba/do_logout'); 
      }

      $db = db_connect();

      //Récuperation des inputs
      $EXEC_BUDGET_RAC_DET_ID = $this->request->getPost('EXEC_BUDGET_RAC_DET_ID');
      $EXEC_BUDGET_RAC_ID = $this->request->getPost('EXEC_BUDGET_RAC_ID');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
      
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
      $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');

      //Form validation
      $rules = [
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
        'TYPE_ANALYSE_MOTIF_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]

      ];

      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {  
        //Enregistrement du montant juridique dans raccrochage activité new
        $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

        //récuperer les etapes et mouvements
        $psgetrequete = "CALL `getRequete`(?,?,?,?);";
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=2','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
                
        $this->gestion_rejet_ptba($EXEC_BUDGET_RAC_ID);

        /*$table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
        $datatomodifie_exec = 'ENG_BUDGETAIRE=0, ENG_BUDGETAIRE_DEVISE=0';
        $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);*/

        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('PTBA_TACHE_ID,ANNEE_BUDGETAIRE_ID,TRIMESTRE_ID,ENG_BUDGETAIRE,ENG_BUDGETAIRE_DEVISE','execution_budgetaire exec JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','exec.EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID.'','exec.EXECUTION_BUDGETAIRE_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_ptba = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
        $ANNEE_BUDGETAIRE_ID = $get_ptba['ANNEE_BUDGETAIRE_ID'];
        $TRIMESTRE_ID = $get_ptba['TRIMESTRE_ID'];
        $PTBA_TACHE_ID = $get_ptba['PTBA_TACHE_ID'];
        $ENG_BUDGETAIRE = $get_ptba['ENG_BUDGETAIRE'];

        //Enregistrement dans historique vérification des motifs
        foreach($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "".$value.",".$ETAPE_ID.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
          $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
        }

        $table4='execution_budgetaire_titre_decaissement';
        $conditions4='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
        $this->update_all_table($table4,$datatomodifie4,$conditions4);

        //Enregistrement dans historique raccrochage detail
        $columsinsert_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datatoinsert_histo="".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$USER_ID.",".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";
        $table_histo='execution_budgetaire_tache_detail_histo';
        $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
       
        $data = [
          'message' => ''.lang('messages_lang.mess_rejet_effectue').''
        ];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Menu_Engagement_Budgetaire/rejete_interface');
      }
      else
      {
        return $this->rejeter_budget(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
         
      }
    }

    //Interface d'annulation au niveau de juridique
    function rejeter_jurid($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=0)
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      
      if(empty($user_id))
      {
        return  redirect('Login_Ptba/do_logout');
      }

      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID, tache.SOUS_TUTEL_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION,actions.ACTION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE, ebtd.ETAPE_DOUBLE_COMMANDE_ID, exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,tache.INSTITUTION_ID,tache.SOUS_TUTEL_ID FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=tache.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=tache.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=tache.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'";
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
            //Récuperation de l'étape précedent
            $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
            $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
            $etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
            $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."' AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
            $motif_rejetRqt = 'CALL `getTable`("' . $motif_rejet . '");';
            $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);


            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            //Récuperer les motifs
            $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
            $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ########################## FIN D'INFOS #####################

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new(md5($data['details']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['get_infoEBET']=$detail['get_infoEBET'];

            return view('App\Modules\double_commande_new\Views\Etapes_Annulations_Jurid_View',$data);
          }  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }   
    }

    //Enregistrement de l'annulation au niveau de juridique
    function save_rejeter_jurid()
    {
      $session  = \Config\Services::session();
      $USER_ID= $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      
      if(empty($USER_ID))
      {
        return  redirect('Login_Ptba/do_logout'); 
      }

      $db = db_connect();

      //Récuperation des inputs
      $EXEC_BUDGET_RAC_DET_ID = $this->request->getPost('EXEC_BUDGET_RAC_DET_ID');
      $EXEC_BUDGET_RAC_ID = $this->request->getPost('EXEC_BUDGET_RAC_ID');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
      
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
      $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');

      //Form validation
      $rules = [
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
        'TYPE_ANALYSE_MOTIF_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]

      ];

      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {  
        //Enregistrement du montant juridique dans raccrochage activité new
        $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

        //récuperer les etapes et mouvements
        $psgetrequete = "CALL `getRequete`(?,?,?,?);";
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_SALAIRE=0 AND IS_CORRECTION=2','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
        
        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
        $datatomodifie_exec = 'ENG_JURIDIQUE=0,ENG_JURIDIQUE_DEVISE=0';
        $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);

        //Enregistrement dans historique vérification des motifs
        foreach($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "".$value.",".$ETAPE_ID.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
          $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
        }

        //Enregistrement dans execution_budgetaire_titre_decaissement
        $tableEBTD='execution_budgetaire_titre_decaissement';
        $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
        $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);


        //Enregistrement dans historique raccrochage detail

        $columsinsert_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";

        $datatoinsert_histo="".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$USER_ID.",".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";

        $table_histo='execution_budgetaire_tache_detail_histo';
        $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
       
        $data = [
          'message' => ''.lang('messages_lang.mess_rejet_effectue').''
        ];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Menu_Engagement_Juridique/eng_jur_rejeter');
      }
      else
      {
        return $this->rejeter_jurid(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
         
      }
    }
    
    //Interface d'annulation au niveau de la liquidation 
    function rejeter_liquid($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=0)
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      
      if(empty($user_id))
      {
        return  redirect('Login_Ptba/do_logout');
      }

      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,tache.SOUS_TUTEL_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION,actions.ACTION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE,ebtd.ETAPE_DOUBLE_COMMANDE_ID,exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,tache.INSTITUTION_ID,tache.SOUS_TUTEL_ID FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=tache.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=tache.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=tache.ACTION_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'";
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
            //Récuperation de l'étape précedent
            $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
            $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
            $etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
            $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."' AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
            $motif_rejetRqt = 'CALL `getTable`("' . $motif_rejet . '");';
            $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);

            //print_r($data['motif_rejet']);exit();

            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            //Récuperer les motifs
            $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
            $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ########################## FIN D'INFOS #####################

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new(md5($data['details']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['get_infoEBET']=$detail['get_infoEBET'];

            return view('App\Modules\double_commande_new\Views\Etapes_Annulations_Liquid_View',$data);
          }  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }   
    }

    //Enregistrement de l'annulation au niveau de la liquidation
    function save_rejeter_liquid()
    {
      $session  = \Config\Services::session();
      $USER_ID= $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      
      if(empty($USER_ID))
      {
        return  redirect('Login_Ptba/do_logout'); 
      }

      $db = db_connect();

      //Récuperation des inputs
      $EXEC_BUDGET_RAC_DET_ID = $this->request->getPost('EXEC_BUDGET_RAC_DET_ID');
      $EXEC_BUDGET_RAC_ID = $this->request->getPost('EXEC_BUDGET_RAC_ID');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
      
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
      $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');

      //Form validation
      $rules = [
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
        'TYPE_ANALYSE_MOTIF_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]

      ];

      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {  
        //Enregistrement du montant juridique dans raccrochage activité new
        $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

        //récuperer les etapes et mouvements
        $psgetrequete = "CALL `getRequete`(?,?,?,?);";
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_SALAIRE=0 AND IS_CORRECTION=2','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
        
        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.LIQUIDATION AS EXEC_LIQUIDATION,exec.LIQUIDATION_DEVISE AS EXEC_LIQUIDATION_DEVISE,exec.DEVISE_TYPE_ID,exec.LIQUIDATION_TYPE_ID,det.MONTANT_LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID','det.EXECUTION_BUDGETAIRE_DETAIL_ID=' . $EXEC_BUDGET_RAC_DET_ID .'','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);
        $LIQUIDATION_TYPE_ID = $get_mont_pay['LIQUIDATION_TYPE_ID'];

        //get detail
        $get_det = $this->getBindParms('COUNT(det.EXECUTION_BUDGETAIRE_DETAIL_ID) AS nbr','execution_budgetaire_tache_detail det JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','det.EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_DET_ID.' AND ETAPE_DOUBLE_COMMANDE_ID NOT IN(5,9,13,40,41,42)','det.EXECUTION_BUDGETAIRE_ID ASC');
        $get_det = $this->ModelPs->getRequeteOne($psgetrequete, $get_det);
        $nbr_liqui_partiel=$get_det['nbr'];

        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
      
        if($get_mont_pay['DEVISE_TYPE_ID'] != 1)
        {
          //mont liquidation à soustraire
          $update_pay_mont = floatval($get_mont_pay['EXEC_LIQUIDATION']) - floatval($get_mont_pay['MONTANT_LIQUIDATION']);
          $update_pay_mont_devise = floatval($get_mont_pay['EXEC_LIQUIDATION_DEVISE']) - floatval($get_mont_pay['MONTANT_LIQUIDATION_DEVISE']);
          $datatomodifie_exec = 'LIQUIDATION='.$update_pay_mont.', LIQUIDATION_DEVISE='.$update_pay_mont_devise;
          $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
        }
        else
        {
          //mont paiement à soustraire
          $update_pay_mont = $get_mont_pay['EXEC_LIQUIDATION'] - $get_mont_pay['MONTANT_LIQUIDATION']; 
          $datatomodifie_exec = 'LIQUIDATION='.$update_pay_mont.'';
          $this->update_all_table($table_exec, $datatomodifie_exec, $cond_exec);
        }

        $NEXT_ETAPE_ID = 9;
        if($LIQUIDATION_TYPE_ID==1 )
        {
          if($nbr_liqui_partiel>1)
          {
            $NEXT_ETAPE_ID = 42;
          }
          else
          {
            $NEXT_ETAPE_ID = 9;
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

        $table4='execution_budgetaire_tache_detail';
        $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
        $datatomodifie4= 'MONTANT_LIQUIDATION=0, MONTANT_LIQUIDATION_DEVISE=0';
        $this->update_all_table($table4,$datatomodifie4,$conditions4);

        //Enregistrement dans execution_budgetaire_titre_decaissement
        $tableEBTD='execution_budgetaire_titre_decaissement';
        $conditionsEBTD='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $datatomodifieEBTD= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
        $this->update_all_table($tableEBTD,$datatomodifieEBTD,$conditionsEBTD);

        //Enregistrement dans historique raccrochage detail
        $columsinsert_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datatoinsert_histo="".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$USER_ID.",".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";
        $table_histo='execution_budgetaire_tache_detail_histo';
        $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
       
        $data = [
          'message' => ''.lang('messages_lang.mess_rejet_effectue').''
        ];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liquidation/get_liquidation_rejeter');
      }
      else
      {
        return $this->rejeter_liquid(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
         
      }
    }

    //Interface d'annulation au niveau d'ordonnancement
    function rejeter_ordo($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=0)
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      
      if(empty($user_id))
      {
        return  redirect('Login_Ptba/do_logout');
      }

      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT  ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,exec.DATE_DEMANDE, ebtd.ETAPE_DOUBLE_COMMANDE_ID, exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE 1 AND MD5(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'";
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
            //Récuperation de l'étape précedent
            $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
            $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
            $etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
            $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."' AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
            $motif_rejetRqt = 'CALL `getTable`("' . $motif_rejet . '");';
            $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);

            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            //Récuperer les motifs
            $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
            $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ########################## FIN D'INFOS #####################

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new(MD5($data['details']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']));

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['get_infoEBET']=$detail['get_infoEBET'];

            return view('App\Modules\double_commande_new\Views\Etapes_Annulations_Ordo_View',$data);
          }  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }   
    }

    //Enregistrement de l'annulation au niveau d'ordonnancement
    function save_rejeter_ordo()
    {
      $session  = \Config\Services::session();
      $USER_ID= $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      
      if(empty($USER_ID))
      {
        return  redirect('Login_Ptba/do_logout'); 
      }

      $db = db_connect();

      //Récuperation des inputs
      $EXEC_BUDGET_RAC_DET_ID = $this->request->getPost('EXEC_BUDGET_RAC_DET_ID');
      $EXEC_BUDGET_RAC_ID = $this->request->getPost('EXEC_BUDGET_RAC_ID');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
      
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
      $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');

      //Form validation
      $rules = [
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
        'TYPE_ANALYSE_MOTIF_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]

      ];

      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {  
        //Enregistrement du montant juridique dans raccrochage activité new
        $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

        //récuperer les etapes et mouvements
        $psgetrequete = "CALL `getRequete`(?,?,?,?);";
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_SALAIRE=0 AND IS_CORRECTION=2','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        
        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID','ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID .'','det.EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
        
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;

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

        $table4='execution_budgetaire_tache_detail';
        $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
        $datatomodifie4= 'MONTANT_ORDONNANCEMENT=0,MONTANT_ORDONNANCEMENT_DEVISE=0';
        $this->update_all_table($table4,$datatomodifie4,$conditions4);

        $table = 'execution_budgetaire_titre_decaissement';
        $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
        $this->update_all_table($table,$datatomodifie,$conditions);

        //Enregistrement dans historique raccrochage detail

        $columsinsert_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";

        $datatoinsert_histo="".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$USER_ID.",".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";

        $table_histo='execution_budgetaire_tache_detail_histo';
        $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
       
        $data = [
          'message' => ''.lang('messages_lang.mess_rejet_effectue').''
        ];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liste_Annulation/annulation_ordo');
      }
      else
      {
        return $this->rejeter_ordo(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
         
      }
    }


    //Interface d'annulation au niveau de la prise en charge
    function rejeter_prise_en_charge($id=0)
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      
      if(empty($user_id))
      {
        return  redirect('Login_Ptba/do_logout');
      }

      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.EXECUTION_BUDGETAIRE_ID,td.EXECUTION_BUDGETAIRE_DETAIL_ID,td.ETAPE_DOUBLE_COMMANDE_ID,dc.DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 AND MD5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'";
      $getdemanande = 'CALL `getTable`("'.$getdemanande.'");';
      $data['details'] = $this->ModelPs->getRequeteOne($getdemanande);

      $getdemanande = str_replace('\\','',$getdemanande);
      $data['get_step_title'] = $this->ModelPs->getRequeteOne($getdemanande);

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
            //Récuperation de l'étape précedent
            $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID )='".$id."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
            $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
            $etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);
            //Le min de la date de réception
            $bind_etap_prev = str_replace('\\','',$bind_etap_prev);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
            $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."' AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
            $motif_rejetRqt = 'CALL `getTable`("' . $motif_rejet . '");';
            $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);

            //Récuperer les motifs
            $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
            $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ########################## FIN D'INFOS #####################

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
           /* $detail=$this->detail_new($EXEC_BUDGET_RAC_DET_ID);

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['creditVote']=$detail['creditVote'];
            $data['montant_reserve']=$detail['montant_reserve'];*/

            return view('App\Modules\double_commande_new\Views\Etapes_Annulations_pc_View',$data);
          }  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }   
    }

    //Enregistrement de l'annulation au niveau de la prise en charge 
    function save_rejeter()
    {
      $session  = \Config\Services::session();
      $USER_ID= $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      
      if(empty($USER_ID))
      {
        return  redirect('Login_Ptba/do_logout'); 
      }

      $db = db_connect();

      //Récuperation des inputs
      $id_exec_titr_dec = $this->request->getPost('id_exec_titr_dec');
      $EXEC_BUDGET_RAC_DET_ID = $this->request->getPost('EXEC_BUDGET_RAC_DET_ID');
      $EXEC_BUDGET_RAC_ID = $this->request->getPost('EXEC_BUDGET_RAC_ID');
      
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
      $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');

      //Form validation
      $rules = [
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
        'TYPE_ANALYSE_MOTIF_ID' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]

      ];

      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {  
        //Enregistrement du montant juridique dans raccrochage activité new
        $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

        //récuperer les etapes et mouvements
        $psgetrequete = "CALL `getRequete`(?,?,?,?);";
        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=2 AND IS_SALAIRE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
        
        $callpsreq = "CALL getRequete(?,?,?,?);";          
        $bindparamss =$this->getBindParms('exec.ORDONNANCEMENT AS EXEC_ORDONNANCEMENT,exec.ORDONNANCEMENT_DEVISE AS EXEC_ORDONNANCEMENT_DEVISE,exec.PAIEMENT AS EXEC_PAIMENT,exec.PAIEMENT_DEVISE AS EXEC_PAIEMENT_DEVISE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,td.MONTANT_PAIEMENT,td.MONTANT_PAIEMENT_DEVISE','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=' . $id_exec_titr_dec .'','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
        $bindparams = str_replace("\\", "", $bindparamss);
        $get_mont_pay = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

        $table_exec = 'execution_budgetaire';
        $cond_exec = 'EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;

        if($get_mont_pay['EXEC_PAIMENT'] > 0)
        {
          //print_r($get_mont_pay);exit();
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
        
        //Enregistrement dans historique vérification des motifs
        foreach($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
          $datatoinsert_histo_motif = "".$value.",".$ETAPE_ID.",".$id_exec_titr_dec."";
          $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
        }

        //Update de l'étape et montant paiement
        $tabledec='execution_budgetaire_titre_decaissement';
        $conditionsdec='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_exec_titr_dec;
        $datatomodifiedec= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', MONTANT_PAIEMENT=0, MONTANT_PAIEMENT_DEVISE=0';
        $this->update_all_table($tabledec,$datatomodifiedec,$conditionsdec);

        //Enregistrement dans historique execution budgetaire detail
        $columsinsert_histo="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datatoinsert_histo="".$id_exec_titr_dec.",".$USER_ID.",".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";

        $table_histo='execution_budgetaire_tache_detail_histo';
        $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
       
        $data = [
          'message' => ''.lang('messages_lang.mess_rejet_effectue').''
        ];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liste_Annulation/annulation_pc');
      }
      else
      {
        return $this->rejeter_prise_en_charge(md5($id_exec_titr_dec));
         
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
  }
?>