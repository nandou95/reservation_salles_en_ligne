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

  class Phase_Administrative extends BaseController
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
      $MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
      $MOUVEMENT_DEPENSE_ID=2;

      $table="budgetaire_type_analyse_motif";
      $columsinsert = "MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE";
      $datacolumsinsert = "{$MOUVEMENT_DEPENSE_ID},'{$DESCRIPTION_MOTIF}',{$MARCHE_PUBLIQUE}";    
      $this->save_all_table($table,$columsinsert,$datacolumsinsert);

      $callpsreq = "CALL getRequete(?,?,?,?);";

          //récuperer les motifs
      $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','MOUVEMENT_DEPENSE_ID=2 AND IS_MARCHE='.$MARCHE_PUBLIQUE,'DESC_TYPE_ANALYSE_MOTIF ASC');
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


    //Interface de l'étape 4 Engagement juridique
    function eng_juridique($EXEC_BUDGET_RAC_DET_ID=0)
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

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT exec.EXECUTION_BUDGETAIRE_ID, exec.SOUS_TUTEL_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.DEVISE_TYPE_HISTO_ENG_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION,actions.ACTION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE, det.ETAPE_DOUBLE_COMMANDE_ID, exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,exec.INSTITUTION_ID,exec.SOUS_TUTEL_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'";

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
            $profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
            $callpsreq = "CALL `getRequete`(?,?,?,?);";

            $bind_profdescr = $this->getBindParms('PROFIL_ID,PROFIL_DESCR', 'user_profil', 'PROFIL_ID='.$profil, 'PROFIL_ID ASC');
            $data['prof'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_profdescr);

            $psgetrequete = "CALL `getRequete`(?,?,?,?);";


            //récuperer les devises
            $monnaie = $this->getBindParms('DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,DESC_DEVISE_TYPE AS DEVISE','devise_type','1','DEVISE ASC');
            $data['type_montant'] = $this->ModelPs->getRequete($psgetrequete, $monnaie);

            //récuperer les modèles
            $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
            $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

            //Récuperation de l'ACTION_ID
            $actions = $this->getBindParms('ACTION_ID','inst_institutions_actions','ACTION_ID="'.$data['details']['ACTION_ID'].'"','ACTION_ID ASC');
            $actions = str_replace('\\','',$actions);
            $data['ACTION_ID'] = $this->ModelPs->getRequeteOne($psgetrequete, $actions);

            if($data['details']['MARCHE_PUBLIQUE'] == 1)
            {
              //récuperer les types bénéficiaires
              $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','TYPE_BENEFICIAIRE_ID=1','DESC_TYPE_BENEFICIAIRE ASC');
              $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);

            }else{

              //récuperer les types bénéficiaires
              $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','1','DESC_TYPE_BENEFICIAIRE ASC');
              $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);
            }

            //Récupération du sous titre
            $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='. $data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');

            $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$EXEC_BUDGET_RAC_DET_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ############## FIN D'INFOS #########################################

            $data['fourn_acq'] = array();
            $data['PRESTATAIRE_ID'] = null;
            $data['file_error'] = '';

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new($EXEC_BUDGET_RAC_DET_ID);

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['creditVote']=$detail['creditVote'];
            $data['montant_reserve']=$detail['montant_reserve'];

            //Sélectionner le trimestre
            $dataa=$this->converdate();
            $data['debut'] = $dataa['debut'];

            return view('App\Modules\double_commande_new\Views\Eng_Juridique_View',$data);
          }  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }
    }

    //Interface de correction de l'étape 4 Engagement juridique
    function corriger_juridique($EXEC_BUDGET_RAC_DET_ID=0)
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

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $data = $this->urichk(); 

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT exec.DEVISE_TYPE_HISTO_ENG_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_JURIDIQUE AS MONTANT_RACCROCHE_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE AS MONTANT_RACCROCHE_JURIDIQUE_DEVISE,exec.SOUS_TUTEL_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION,actions.ACTION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE, det.ETAPE_DOUBLE_COMMANDE_ID, exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,exec.DEVISE_TYPE_ID AS TYPE_MONTANT_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,exec.INSTITUTION_ID,exec.SOUS_TUTEL_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'";

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

            ################### INFOS A CORRIGER ############################
            $get_juridiq = "SELECT exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_JURIDIQUE AS MONTANT_RACCROCHE_JURIDIQUE,exec.DATE_ENG_JURIDIQUE AS DATE_ENGAGEMENT_JURIDIQUE, info.MODELE_ID,exec.ENG_JURIDIQUE_DEVISE AS MONTANT_RACCROCHE_JURIDIQUE_DEVISE, det.COUR_DEVISE, info.REFERENCE, info.TYPE_BENEFICIAIRE_ID, info.PRESTATAIRE_ID, exec.MARCHE_PUBLIQUE, info.PATH_CONTRAT,det.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE_DEVISE,info.DATE_DEBUT_CONTRAT,info.DATE_FIN_CONTRAT FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'";

            $juridiq = 'CALL `getTable`("'.$get_juridiq.'");';

            $data['juridique'] = $this->ModelPs->getRequeteOne($juridiq);

            //Récuperation de l'étape précedent
            $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
            $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
            $etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
            $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."' AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
            $motif_rejetRqt = 'CALL `getTable`("' . $motif_rejet . '");';
            $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);

            //récuperer les types de monnaie
            $monnaie = $this->getBindParms('DEVISE_TYPE_ID AS TYPE_MONTANT_ID,DESC_DEVISE_TYPE AS DESC_MONTANT','devise_type','1','DEVISE_TYPE_ID ASC');
            $data['type_montant'] = $this->ModelPs->getRequete($psgetrequete, $monnaie);

            //récuperer les modèles
            $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
            $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

            if($data['details']['MARCHE_PUBLIQUE'] == 1)
            {
              //récuperer les types bénéficiaires
              $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','TYPE_BENEFICIAIRE_ID=1','DESC_TYPE_BENEFICIAIRE ASC');
              $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);

            }else{

              //récuperer les types bénéficiaires
              $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','1','DESC_TYPE_BENEFICIAIRE ASC');
              $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);
            }

            //récuperer les fournisseurs/acquéreurs
            $prestataire = $this->getBindParms('PRESTATAIRE_ID,NOM_PRESTATAIRE,PRENOM_PRESTATAIRE, TYPE_BENEFICIAIRE_ID, NIF_PRESTATAIRE','prestataire','TYPE_BENEFICIAIRE_ID='. $data['juridique']['TYPE_BENEFICIAIRE_ID'],'NOM_PRESTATAIRE ASC');
            $data['prest'] = $this->ModelPs->getRequete($psgetrequete, $prestataire);

            if($data['juridique']['TYPE_BENEFICIAIRE_ID'] == 1)
            {
              $data['prest_label'] = 'Fournisseur<font color="red">*</font>';

            }else{
              $data['prest_label'] = 'Acquéreur<font color="red">*</font>';
            }


            //Récupération du sous titre
            $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='. $data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');
            $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,OBSERVATION,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$EXEC_BUDGET_RAC_DET_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ####################### FIN D'INFOS #########################################

            $data['file_error'] = '';

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new($EXEC_BUDGET_RAC_DET_ID);

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['creditVote']=$detail['creditVote'];
            $data['montant_reserve']=$detail['montant_reserve'];

            //Sélectionner le trimestre
            $dataa=$this->converdate();
            $data['debut'] = $dataa['debut'];

            return view('App\Modules\double_commande_new\Views\Eng_Juridique_Corriger_View',$data);
          }  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }
    }

    //Interface de confirmation de l'engagement juridique, etape 5
    function confirmer_juridique($EXEC_BUDGET_RAC_DET_ID=0)
    {
      $session  = \Config\Services::session();
      $user_id ='';
      $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED');
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($ced!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT exec.EXECUTION_BUDGETAIRE_ID, exec.SOUS_TUTEL_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION,actions.ACTION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE, det.ETAPE_DOUBLE_COMMANDE_ID, exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,exec.INSTITUTION_ID,exec.SOUS_TUTEL_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'";
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
            $benef = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','1','DESCRIPTION ASC');
            $data['operation'] = $this->ModelPs->getRequete($psgetrequete, $benef);

            //récuperer les modeles
            $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
            $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

            //récuperer l'étape à corriger
            $step_correct = $this->getBindParms('ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR', 'budgetaire_etape_retour_correction','ETAPE_RETOUR_CORRECTION_ID <3','ETAPE_RETOUR_CORRECTION_ID ASC');
            $data['get_correct'] = $this->ModelPs->getRequete($psgetrequete, $step_correct);

            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$EXEC_BUDGET_RAC_DET_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            //Récuperer les motifs

            if ($data['details']['MARCHE_PUBLIQUE']==1)
            {

              $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','IS_MARCHE=1 AND MOUVEMENT_DEPENSE_ID=2','TYPE_ANALYSE_MOTIF_ID ASC');
              $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);
            } else {

              $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','IS_MARCHE=0 AND MOUVEMENT_DEPENSE_ID=2','TYPE_ANALYSE_MOTIF_ID ASC');
              $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);
            }


            //Récupération du sous titre
            $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='. $data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');

            $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ########################## FIN D'INFOS #####################

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new($EXEC_BUDGET_RAC_DET_ID);

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['creditVote']=$detail['creditVote'];
            $data['montant_reserve']=$detail['montant_reserve'];

            return view('App\Modules\double_commande_new\Views\Eng_Juridique_Confirm_View',$data);
          }  
        }
        return redirect('Login_Ptba/homepage'); 
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }   
    }

    //Enregistrement de l'étape 4 Engagement budgétaire
    function update_etape4()
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

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $db = db_connect();

      //Récupération des inputs
      $MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
      $EXEC_BUDGET_RAC_DET_ID = $this->request->getPost('EXEC_BUDGET_RAC_DET_ID');
      $EXEC_BUDGET_RAC_ID = $this->request->getPost('EXEC_BUDGET_RAC_ID');

      $MONTANT_EN_BIF = preg_replace('/\s/', '', $this->request->getPost('MONTANT_EN_BIF'));
      $MONTANT_EN_DEVISE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_EN_DEVISE'));
      $DATE_HEURE_JURIDIQUE = $this->request->getPost('DATE_HEURE_JURIDIQUE');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
      $DATE_FIN = $this->request->getPost('DATE_FIN');
      $TYPE_BENEFICIARE_ID = $this->request->getPost('TYPE_BENEFICIARE');
      $PRESTATAIRE_ID = $this->request->getPost('FOURNISSEUR_ACQUEREUR');
      $MODEL_ID = $this->request->getPost('MODEL');
      $REFERENCE = $this->request->getPost('REFERENCE');
      $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
      $ID_JUR_DEVISE = $this->request->getPost('ID_JUR_DEVISE');

      //Form validation
      $rules = [
        'MONTANT_EN_BIF' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
            
          ]
        ],

        'DATE_HEURE_JURIDIQUE' => [
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

        'MODEL' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],

        'REFERENCE' => [
          'rules' => 'required|max_length[25]',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
            'max_length' => '<font style="color:red;size:2px;">Vous ne devez pas dépasser 20 caractères</font>'
          ]
        ],

        'TYPE_BENEFICIARE' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          ]
        ],

        'FOURNISSEUR_ACQUEREUR' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          ]
        ]

      ];

      
      $TYPE_MONNAIE = $this->request->getPost('MONNAIE');
      if($TYPE_MONNAIE != 1)
      {
        $rules['MONTANT_EN_DEVISE'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];

      }

      $file_error = '';
      if($MARCHE_PUBLIQUE == 1){

        $rules['DATE_DEBUT'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];


        $rules['DATE_FIN'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];

        $file = $this->request->getFile('PATH_CONTRAT');

        if (!$file || !$file->isValid())
        {
          $file_error = '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>';
        }else{

          $maxFileSize = 25*(1024*1024);
          if ($file->getSize() > $maxFileSize)
          {
            $file_error = '<font style="color:red;size:2px;">'.lang('messages_lang.taille_maximale').' (25 MB)</font>';

          }else{

            $file_error = '';
          }
        }
      }


      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {
        if($MARCHE_PUBLIQUE == 0)
        {
          //Enregistrement du montant juridique 
          $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

          //récuperer les etapes et mouvements
          $psgetrequete = "CALL `getRequete`(?,?,?,?);";
          $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
          $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
          $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
          $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);
          $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          if($TYPE_MONNAIE != 1)
          {
            //Enregistrement dans activite_new
            $table3='execution_budgetaire';
            $conditions3='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie3= 'ENG_JURIDIQUE='.$MONTANT_EN_BIF.', ENG_JURIDIQUE_DEVISE='.$MONTANT_EN_DEVISE.',DATE_ENG_JURIDIQUE="'.$DATE_HEURE_JURIDIQUE.'",DEVISE_TYPE_HISTO_JURD_ID='.$ID_JUR_DEVISE;
            $this->update_all_table($table3,$datatomodifie3,$conditions3);

            //Enregistrement dans activite_detail
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement des infos supplémentaires
            $table_info='execution_budgetaire_tache_info_suppl';
            $conditions_info='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie_info= 'MODELE_ID='.$MODEL_ID.', REFERENCE="'.$REFERENCE.'", TYPE_BENEFICIAIRE_ID="'.$TYPE_BENEFICIARE_ID.'", PRESTATAIRE_ID='.$PRESTATAIRE_ID.'';
            $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);
          }
          else{

            //Enregistrement dans activite_new
            $table3='execution_budgetaire';
            $conditions3='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie3= 'ENG_JURIDIQUE='.$MONTANT_EN_BIF.',DATE_ENG_JURIDIQUE="'.$DATE_HEURE_JURIDIQUE.'",DEVISE_TYPE_HISTO_JURD_ID='.$ID_JUR_DEVISE;
            $this->update_all_table($table3,$datatomodifie3,$conditions3);

            //Enregistrement dans activite_detail
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement des infos supplémentaires
            $table_info='execution_budgetaire_tache_info_suppl';
            $conditions_info='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie_info= 'MODELE_ID='.$MODEL_ID.', REFERENCE="'.$REFERENCE.'", TYPE_BENEFICIAIRE_ID="'.$TYPE_BENEFICIARE_ID.'", PRESTATAIRE_ID='.$PRESTATAIRE_ID.'';
            $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);
          }          
        }
        elseif($MARCHE_PUBLIQUE == 1)
        {
          //Enregistrement du montant juridique dans raccrochage activité
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');
          $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
          $DATE_FIN = $this->request->getPost('DATE_FIN');

          $date_start = date_create($DATE_DEBUT);
          $date_end = date_create($DATE_FIN);
          $diff = date_diff($date_start,$date_end);
          $jours = $diff->format("%a")+1;


          //récuperer les etapes et mouvements
          $psgetrequete = "CALL `getRequete`(?,?,?,?);";
          $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
          $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
          $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
          $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

          $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];


          if($TYPE_MONNAIE !=1)
          { 
            //Enregistrement dans activite_new
            $table3='execution_budgetaire';
            $conditions3='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie3= 'ENG_JURIDIQUE='.$MONTANT_EN_BIF.', ENG_JURIDIQUE_DEVISE='.$MONTANT_EN_DEVISE.',DATE_ENG_JURIDIQUE="'.$DATE_HEURE_JURIDIQUE.'",DEVISE_TYPE_HISTO_JURD_ID='.$ID_JUR_DEVISE;
            $this->update_all_table($table3,$datatomodifie3,$conditions3);

            //Enregistrement dans activite_detail
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement des infos supplémentaires
            $PATH_CONTRAT = $this->request->getPost('PATH_CONTRAT');
            $CONTRAT=$this->uploadFile('PATH_CONTRAT','double_commande_new','CONTRAT');

            $table_info='execution_budgetaire_tache_info_suppl';
            $conditions_info='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie_info= 'MODELE_ID='.$MODEL_ID.', REFERENCE="'.$REFERENCE.'", PATH_CONTRAT="'.$CONTRAT.'", DATE_DEBUT_CONTRAT="'.$DATE_DEBUT.'", DATE_FIN_CONTRAT="'.$DATE_FIN.'", NBRE_JR_CONTRAT='.$jours.', TYPE_BENEFICIAIRE_ID='.$TYPE_BENEFICIARE_ID.', PRESTATAIRE_ID='.$PRESTATAIRE_ID;
            $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);
          }else{

            //Enregistrement dans activite_new
            $table3='execution_budgetaire';
            $conditions3='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie3= 'ENG_JURIDIQUE='.$MONTANT_EN_BIF.',DATE_ENG_JURIDIQUE="'.$DATE_HEURE_JURIDIQUE.'",DEVISE_TYPE_HISTO_JURD_ID='.$ID_JUR_DEVISE;
            $this->update_all_table($table3,$datatomodifie3,$conditions3);

            //Enregistrement dans activite_detail
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement des infos supplémentaires
            $PATH_CONTRAT = $this->request->getPost('PATH_CONTRAT');
            $CONTRAT=$this->uploadFile('PATH_CONTRAT','double_commande_new','CONTRAT');

            $table_info='execution_budgetaire_tache_info_suppl';
            $conditions_info='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie_info= 'MODELE_ID='.$MODEL_ID.', REFERENCE="'.$REFERENCE.'", PATH_CONTRAT="'.$CONTRAT.'",DATE_DEBUT_CONTRAT="'.$DATE_DEBUT.'", DATE_FIN_CONTRAT="'.$DATE_FIN.'", NBRE_JR_CONTRAT='.$jours.', TYPE_BENEFICIAIRE_ID='.$TYPE_BENEFICIARE_ID.', PRESTATAIRE_ID='.$PRESTATAIRE_ID;
            $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);
          }   
        }
        //Enregistrement dans historique raccrochage

        $columsinsert_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,OBSERVATION,DATE_TRANSMISSION,DATE_RECEPTION";

        $datatoinsert_histo="".$EXEC_BUDGET_RAC_DET_ID.",".$USER_ID_ENG.",".$ETAPE_ID.",'".str_replace("'", "\'", $COMMENTAIRE)."','".$DATE_TRANSMISSION."','".$DATE_RECEPTION."'";

        $table_histo='execution_budgetaire_tache_detail_histo';
        $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
        
        $data = [
          'message' => ''.lang('messages_lang.Enregistrer_succes_msg').''
        ];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Menu_Engagement_Juridique');


      }else{


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

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT exec.EXECUTION_BUDGETAIRE_ID, exec.SOUS_TUTEL_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION,actions.ACTION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE, det.ETAPE_DOUBLE_COMMANDE_ID, exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,exec.INSTITUTION_ID,exec.SOUS_TUTEL_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'";

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
            $profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
            $callpsreq = "CALL `getRequete`(?,?,?,?);";

            $bind_profdescr = $this->getBindParms('PROFIL_ID,PROFIL_DESCR', 'user_profil', 'PROFIL_ID='.$profil, 'PROFIL_ID ASC');
            $data['prof'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_profdescr);

            $psgetrequete = "CALL `getRequete`(?,?,?,?);";


            //récuperer les devises
            $monnaie = $this->getBindParms('DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,DESC_DEVISE_TYPE AS DEVISE','devise_type','1','DEVISE ASC');
            $data['type_montant'] = $this->ModelPs->getRequete($psgetrequete, $monnaie);

            //récuperer les modèles
            $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
            $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

            //Récuperation de l'ACTION_ID
            $actions = $this->getBindParms('ACTION_ID','inst_institutions_actions','ACTION_ID="'.$data['details']['ACTION_ID'].'"','ACTION_ID ASC');
            $actions = str_replace('\\','',$actions);
            $data['ACTION_ID'] = $this->ModelPs->getRequeteOne($psgetrequete, $actions);

            if($data['details']['MARCHE_PUBLIQUE'] == 1)
            {
              //récuperer les types bénéficiaires
              $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','TYPE_BENEFICIAIRE_ID=1','DESC_TYPE_BENEFICIAIRE ASC');
              $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);

            }else{

              //récuperer les types bénéficiaires
              $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','1','DESC_TYPE_BENEFICIAIRE ASC');
              $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);
            }

            //Récupération du sous titre
            $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='. $data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');

            $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$EXEC_BUDGET_RAC_DET_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ############## FIN D'INFOS #########################################

            $data['fourn_acq'] = array();
            $data['PRESTATAIRE_ID'] = null;
            $data['file_error'] = '';

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new($EXEC_BUDGET_RAC_DET_ID);

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['creditVote']=$detail['creditVote'];
            $data['montant_reserve']=$detail['montant_reserve'];

            //Sélectionner le trimestre
            $dataa=$this->converdate();
            $data['debut'] = $dataa['debut'];

            return view('App\Modules\double_commande_new\Views\Eng_Juridique_View',$data);
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


    //Correction de l'étape 4 Engagement budgétaire
    function update_corriger_etape4()
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

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $db = db_connect();

      //Récuperer les valeurs des inputs
      $MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
      $EXEC_BUDGET_RAC_ID = $this->request->getPost('EXEC_BUDGET_RAC_ID');
      $EXEC_BUDGET_RAC_DET_ID = $this->request->getPost('EXEC_BUDGET_RAC_DET_ID');
      
      $MONTANT_EN_BIF = preg_replace('/\s/', '', $this->request->getPost('MONTANT_EN_BIF'));
      $MONTANT_EN_DEVISE = preg_replace('/\s/', '', $this->request->getPost('MONTANT_EN_DEVISE'));

      $DATE_HEURE_JURIDIQUE = $this->request->getPost('DATE_HEURE_JURIDIQUE');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
      $DATE_FIN = $this->request->getPost('DATE_FIN');
      
      $TYPE_BENEFICIARE_ID = $this->request->getPost('TYPE_BENEFICIARE');
      $PRESTATAIRE_ID = $this->request->getPost('FOURNISSEUR_ACQUEREUR');
      $MODEL_ID = $this->request->getPost('MODEL');
      $REFERENCE = $this->request->getPost('REFERENCE');
      $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
      $ID_JUR_DEVISE = $this->request->getPost('ID_JUR_DEVISE');


      //Form validation
      $rules = [
        'MONTANT_EN_BIF' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          ]
        ],

        'DATE_HEURE_JURIDIQUE' => [
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

        'MODEL' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],

        'REFERENCE' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ],

        'TYPE_BENEFICIARE' => [
          'rules' => 'required|max_length[25]',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
            'max_length' => '<font style="color:red;size:2px;">Vous ne devez pas dépasser 20 caractères</font>'
          ]
        ],

        'FOURNISSEUR_ACQUEREUR' => [
          'rules' => 'required|max_length[25]',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
            'max_length' => '<font style="color:red;size:2px;">Vous ne devez pas dépasser 20 caractères</font>'
          ]
        ]

      ];

      $TYPE_MONNAIE = $this->request->getPost('MONNAIE');
      if($TYPE_MONNAIE != 1)
      {
        $rules['MONTANT_EN_DEVISE'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];

      }

      if($MARCHE_PUBLIQUE == 1)
      {
        $rules['DATE_DEBUT'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];


        $rules['DATE_FIN'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];
      }


      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {
        
        if($MARCHE_PUBLIQUE == 0)
        {
          //Enregistrement du montant juridique dans raccrochage activité

          $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

          //récuperer les etapes et mouvements
          $psgetrequete = "CALL `getRequete`(?,?,?,?);";
          $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
          $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
          $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
          $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

          $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
          


          if($TYPE_MONNAIE != 1)
          {

            //Enregistrement dans activite_new
            $table3='execution_budgetaire';
            $conditions3='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie3= 'ENG_JURIDIQUE='.$MONTANT_EN_BIF.', ENG_JURIDIQUE_DEVISE='.$MONTANT_EN_DEVISE.',DATE_ENG_JURIDIQUE="'.$DATE_HEURE_JURIDIQUE.'",DEVISE_TYPE_HISTO_JURD_ID='.$ID_JUR_DEVISE;
            $this->update_all_table($table3,$datatomodifie3,$conditions3);

            //Enregistrement dans activite_detail
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement des infos supplémentaires
            $table_info='execution_budgetaire_tache_info_suppl';
            $conditions_info='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie_info= 'MODELE_ID='.$MODEL_ID.', REFERENCE="'.$REFERENCE.'", TYPE_BENEFICIAIRE_ID="'.$TYPE_BENEFICIARE_ID.'", PRESTATAIRE_ID='.$PRESTATAIRE_ID.'';
            $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);
          }
          else{

            //Enregistrement dans activite_new
            $table3='execution_budgetaire';
            $conditions3='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie3= 'ENG_JURIDIQUE='.$MONTANT_EN_BIF.',DATE_ENG_JURIDIQUE="'.$DATE_HEURE_JURIDIQUE.'",DEVISE_TYPE_HISTO_JURD_ID='.$ID_JUR_DEVISE;
            $this->update_all_table($table3,$datatomodifie3,$conditions3);

            //Enregistrement dans activite_detail
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement des infos supplémentaires
            $table_info='execution_budgetaire_tache_info_suppl';
            $conditions_info='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie_info= 'MODELE_ID='.$MODEL_ID.', REFERENCE="'.$REFERENCE.'", TYPE_BENEFICIAIRE_ID="'.$TYPE_BENEFICIARE_ID.'", PRESTATAIRE_ID='.$PRESTATAIRE_ID.'';
            $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);

          }
          
        }
        elseif($MARCHE_PUBLIQUE == 1)
        {

          //Enregistrement du montant juridique dans raccrochage activité

          $ETAPE_ID = $this->request->getPost('ETAPE_ID');
          $DATE_DEBUT = $this->request->getPost('DATE_DEBUT');
          $DATE_FIN = $this->request->getPost('DATE_FIN');

          $date_start = date_create($DATE_DEBUT);
          $date_end = date_create($DATE_FIN);
          $diff = date_diff($date_start,$date_end);
          $jours = $diff->format("%a")+1;


          //récuperer les etapes et mouvements
          $psgetrequete = "CALL `getRequete`(?,?,?,?);";
          $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
          $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
          $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
          $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

          $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];


          $file = $this->request->getFile('PATH_CONTRAT');

          if (!$file || !$file->isValid())
          {
            $CONTRAT=$this->request->getPost('PATH_CONTRAT_OLD');

          }else{

            $PATH_CONTRAT = $this->request->getPost('PATH_CONTRAT');
            $CONTRAT=$this->uploadFile('PATH_CONTRAT','double_commande','CONTRAT');
            
          }


          if($TYPE_MONNAIE !=1)
          {

            //Enregistrement dans activite_new
            $table3='execution_budgetaire';
            $conditions3='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie3= 'ENG_JURIDIQUE='.$MONTANT_EN_BIF.', ENG_JURIDIQUE_DEVISE='.$MONTANT_EN_DEVISE.',DATE_ENG_JURIDIQUE="'.$DATE_HEURE_JURIDIQUE.'",DEVISE_TYPE_HISTO_JURD_ID='.$ID_JUR_DEVISE;
            $this->update_all_table($table3,$datatomodifie3,$conditions3);

           //Enregistrement dans activite_detail
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

             $table_info='execution_budgetaire_tache_info_suppl';
            $conditions_info='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie_info= 'MODELE_ID='.$MODEL_ID.', REFERENCE="'.$REFERENCE.'", PATH_CONTRAT="'.$CONTRAT.'", DATE_DEBUT_CONTRAT="'.$DATE_DEBUT.'", DATE_FIN_CONTRAT="'.$DATE_FIN.'", NBRE_JR_CONTRAT='.$jours.', TYPE_BENEFICIAIRE_ID='.$TYPE_BENEFICIARE_ID.', PRESTATAIRE_ID='.$PRESTATAIRE_ID;
            $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);
          }else{

            //Enregistrement dans activite_new
            $table3='execution_budgetaire';
            $conditions3='EXECUTION_BUDGETAIRE_ID ='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie3= 'ENG_JURIDIQUE='.$MONTANT_EN_BIF.',DATE_ENG_JURIDIQUE="'.$DATE_HEURE_JURIDIQUE.'",DEVISE_TYPE_HISTO_JURD_ID='.$ID_JUR_DEVISE;
            $this->update_all_table($table3,$datatomodifie3,$conditions3);

            //Enregistrement dans activite_detail
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            $table_info='execution_budgetaire_tache_info_suppl';
            $conditions_info='EXECUTION_BUDGETAIRE_ID='.$EXEC_BUDGET_RAC_ID;
            $datatomodifie_info= 'MODELE_ID='.$MODEL_ID.', REFERENCE="'.$REFERENCE.'", PATH_CONTRAT="'.$CONTRAT.'", DATE_DEBUT_CONTRAT="'.$DATE_DEBUT.'", DATE_FIN_CONTRAT="'.$DATE_FIN.'", NBRE_JR_CONTRAT='.$jours.', TYPE_BENEFICIAIRE_ID='.$TYPE_BENEFICIARE_ID.', PRESTATAIRE_ID='.$PRESTATAIRE_ID;
            $this->update_all_table($table_info,$datatomodifie_info,$conditions_info);
          }

          
        }
         $columsinsert_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,OBSERVATION,DATE_TRANSMISSION,DATE_RECEPTION";

        $datatoinsert_histo="".$EXEC_BUDGET_RAC_DET_ID.",".$USER_ID_ENG.",".$ETAPE_ID.",'".str_replace("'", "\'", $COMMENTAIRE)."','".$DATE_TRANSMISSION."','".$DATE_RECEPTION."'";

        $table_histo='execution_budgetaire_tache_detail_histo';
        $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);

        $data = [
          'message' => ''.lang('messages_lang.Enregistrer_succes_msg').''
        ];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Menu_Engagement_Juridique');
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

      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $data = $this->urichk(); 

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT exec.DEVISE_TYPE_HISTO_ENG_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.ENG_JURIDIQUE AS MONTANT_RACCROCHE_JURIDIQUE,exec.ENG_JURIDIQUE_DEVISE AS MONTANT_RACCROCHE_JURIDIQUE_DEVISE,exec.SOUS_TUTEL_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION,actions.ACTION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE, det.ETAPE_DOUBLE_COMMANDE_ID, exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,exec.DEVISE_TYPE_ID AS TYPE_MONTANT_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,exec.INSTITUTION_ID,exec.SOUS_TUTEL_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'";

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

            ################### INFOS A CORRIGER ############################
            $get_juridiq = "SELECT exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_JURIDIQUE AS MONTANT_RACCROCHE_JURIDIQUE,exec.DATE_ENG_JURIDIQUE AS DATE_ENGAGEMENT_JURIDIQUE, info.MODELE_ID,exec.ENG_JURIDIQUE_DEVISE AS MONTANT_RACCROCHE_JURIDIQUE_DEVISE, det.COUR_DEVISE, info.REFERENCE, info.TYPE_BENEFICIAIRE_ID, info.PRESTATAIRE_ID, exec.MARCHE_PUBLIQUE, info.PATH_CONTRAT,det.ETAPE_DOUBLE_COMMANDE_ID,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE_DEVISE,info.DATE_DEBUT_CONTRAT,info.DATE_FIN_CONTRAT FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'";

            $juridiq = 'CALL `getTable`("'.$get_juridiq.'");';

            $data['juridique'] = $this->ModelPs->getRequeteOne($juridiq);

            //Récuperation de l'étape précedent
            $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
            $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
            $etap_prev = $this->ModelPs->getRequeteOne($psgetrequete, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
            $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."' AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
            $motif_rejetRqt = 'CALL `getTable`("' . $motif_rejet . '");';
            $data['motif_rejet']= $this->ModelPs->getRequete($motif_rejetRqt);

            //récuperer les types de monnaie
            $monnaie = $this->getBindParms('DEVISE_TYPE_ID AS TYPE_MONTANT_ID,DESC_DEVISE_TYPE AS DESC_MONTANT','devise_type','1','DEVISE_TYPE_ID ASC');
            $data['type_montant'] = $this->ModelPs->getRequete($psgetrequete, $monnaie);

            //récuperer les modèles
            $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
            $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

            if($data['details']['MARCHE_PUBLIQUE'] == 1)
            {
              //récuperer les types bénéficiaires
              $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','TYPE_BENEFICIAIRE_ID=1','DESC_TYPE_BENEFICIAIRE ASC');
              $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);

            }else{

              //récuperer les types bénéficiaires
              $benef = $this->getBindParms('TYPE_BENEFICIAIRE_ID,DESC_TYPE_BENEFICIAIRE','type_beneficiaire','1','DESC_TYPE_BENEFICIAIRE ASC');
              $data['type_benef'] = $this->ModelPs->getRequete($psgetrequete, $benef);
            }

            //récuperer les fournisseurs/acquéreurs
            $prestataire = $this->getBindParms('PRESTATAIRE_ID,NOM_PRESTATAIRE,PRENOM_PRESTATAIRE, TYPE_BENEFICIAIRE_ID, NIF_PRESTATAIRE','prestataire','TYPE_BENEFICIAIRE_ID='. $data['juridique']['TYPE_BENEFICIAIRE_ID'],'NOM_PRESTATAIRE ASC');
            $data['prest'] = $this->ModelPs->getRequete($psgetrequete, $prestataire);

            if($data['juridique']['TYPE_BENEFICIAIRE_ID'] == 1)
            {
              $data['prest_label'] = 'Fournisseur<font color="red">*</font>';

            }else{
              $data['prest_label'] = 'Acquéreur<font color="red">*</font>';
            }


            //Récupération du sous titre
            $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='. $data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');
            $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,OBSERVATION,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$EXEC_BUDGET_RAC_DET_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ####################### FIN D'INFOS #########################################

            $data['file_error'] = '';

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new($EXEC_BUDGET_RAC_DET_ID);

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['creditVote']=$detail['creditVote'];
            $data['montant_reserve']=$detail['montant_reserve'];

            //Sélectionner le trimestre
            $dataa=$this->converdate();
            $data['debut'] = $dataa['debut'];

            return view('App\Modules\double_commande_new\Views\Eng_Juridique_Corriger_View',$data);
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

    //Enregistrement de l'étape 5 Confirmation d'engagement budgétaire
    function update_etape5()
    {
      $session  = \Config\Services::session();
      $USER_ID_ENG='';
      $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED');
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $USER_ID_ENG = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($ced!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $db = db_connect();

      //Récuperation des inputs
      $MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
      $EXEC_BUDGET_RAC_DET_ID = $this->request->getPost('EXEC_BUDGET_RAC_DET_ID');
      $EXEC_BUDGET_RAC_ID = $this->request->getPost('EXEC_BUDGET_RAC_ID');
      
      $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
      $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost('ETAPE_RETOUR_CORRECTION_ID');
      $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
      $OPERATION_ID = $this->request->getPost('OPERATION');

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

        'OPERATION' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]

      ];

      if($OPERATION_ID == 1)
      {
        $rules['ETAPE_RETOUR_CORRECTION_ID'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];

      }

      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {
        if($MARCHE_PUBLIQUE == 1)
        {
          if($OPERATION_ID == 1)
          {
            //Enregistrement du montant juridique dans raccrochage activité new
            $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

            //récuperer les etapes et mouvements
            $psgetrequete = "CALL `getRequete`(?,?,?,?);";
            $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
            $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=1 AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

            if($ETAPE_RETOUR_CORRECTION_ID == 1)
            {
              $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
              $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
            }
            else
            {
              //récuperer les etapes et mouvements
              $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=1','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
              $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
              $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant

              $MOUVEMENT_NEXT_ID =  $get_next_step['MOUVEMENT_DEPENSE_ID'];// mouve qui va suivre
              #######################################
              // c'est pour retour les montant utilise
              $this->gestion_rejet_ptba($EXEC_BUDGET_RAC_ID);
            }

            
            //Enregistrement dans historique vérification des motifs
            foreach($TYPE_ANALYSE_MOTIF_ID as $value)
            {
              $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
              $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
              $datatoinsert_histo_motif = "".$value.",".$ETAPE_ID.",".$EXEC_BUDGET_RAC_DET_ID."";
              $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
            }

            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement dans historique raccrochage detail

            $columsinsert_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";

            $datatoinsert_histo="".$EXEC_BUDGET_RAC_DET_ID.",".$USER_ID_ENG.",'".str_replace("'", "\'", $COMMENTAIRE)."',".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";

            $table_histo='execution_budgetaire_tache_detail_histo';
            $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
          }
          elseif($OPERATION_ID == 2)
          {
            //Enregistrement du montant juridique dans raccrochage activité
            $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

            //récuperer les etapes et mouvements
            $psgetrequete = "CALL `getRequete`(?,?,?,?);";
            $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
            $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=0 AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

            $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
            
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement dans historique raccrochage
            $columsinsert_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";

            $datatoinsert_histo="".$EXEC_BUDGET_RAC_DET_ID.",".$USER_ID_ENG.",'".str_replace("'", "\'", $COMMENTAIRE)."',".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";

            $table_histo='execution_budgetaire_tache_detail_histo';
            $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
          }
          elseif($OPERATION_ID == 3)
          {

            //Enregistrement du montant juridique dans raccrochage activité
            $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

            //récuperer les etapes et mouvements
            $psgetrequete = "CALL `getRequete`(?,?,?,?);";
            $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
            $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=2 AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

            $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
            
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID ='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement dans historique raccrochage
            $columsinsert_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";

            $datatoinsert_histo="".$EXEC_BUDGET_RAC_DET_ID.",".$USER_ID_ENG.",'".str_replace("'", "\'", $COMMENTAIRE)."',".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";

            $table_histo='execution_budgetaire_tache_detail_histo';
            $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
            $this->gestion_rejet_ptba($EXEC_BUDGET_RAC_ID);
          }
        }
        elseif($MARCHE_PUBLIQUE == 0)
        {
          if($OPERATION_ID == 1)
          {
            //Enregistrement du montant juridique dans raccrochage activité
            $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

            //récuperer les etapes et mouvements
            $psgetrequete = "CALL `getRequete`(?,?,?,?);";
            $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
            $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

            
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=1 AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

            if($ETAPE_RETOUR_CORRECTION_ID == 1)
            {
              $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
              $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
            }
            else
            {

              //récuperer les etapes et mouvements
              $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID','IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=1','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
              $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
              $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];//etape suivant

              $MOUVEMENT_NEXT_ID =  $get_next_step['MOUVEMENT_DEPENSE_ID'];// mouve qui va suivre
              #######################################
              // c'est pour retourner les montant utilise 
              $this->gestion_rejet_ptba($EXEC_BUDGET_RAC_ID);

            }

            //Enregistrement dans historique vérification des motifs
            foreach($TYPE_ANALYSE_MOTIF_ID as $value)
            {
              $insertToTable_motif='execution_budgetaire_histo_operation_verification_motif';
              $columninserthist_motif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
              $datatoinsert_histo_motif = "".$value.",".$ETAPE_ID.",".$EXEC_BUDGET_RAC_DET_ID."";
              $this->save_all_table($insertToTable_motif,$columninserthist_motif,$datatoinsert_histo_motif);
            }
            
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);
  
            //Enregistrement dans historique raccrochage
            $columsinsert_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";

            $datatoinsert_histo="".$EXEC_BUDGET_RAC_DET_ID.",".$USER_ID_ENG.",'".str_replace("'", "\'", $COMMENTAIRE)."',".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";
            $table_histo='execution_budgetaire_tache_detail_histo';
            $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);

          }elseif($OPERATION_ID == 2){

            //Enregistrement du montant juridique dans raccrochage activité

            $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

            //récuperer les etapes et mouvements
            $psgetrequete = "CALL `getRequete`(?,?,?,?);";
            $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
            $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=0 AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

            $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
            
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement dans historique raccrochage
            $columsinsert_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";

            $datatoinsert_histo="".$EXEC_BUDGET_RAC_DET_ID.",".$USER_ID_ENG.",'".str_replace("'", "\'", $COMMENTAIRE)."',".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";

            $table_histo='execution_budgetaire_tache_detail_histo';
            $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);
          }elseif($OPERATION_ID == 3){

            //Enregistrement du montant juridique dans raccrochage activité

            $ETAPE_ID = $this->request->getPost('ETAPE_ID'); 

            //récuperer les etapes et mouvements
            $psgetrequete = "CALL `getRequete`(?,?,?,?);";
            $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_ID,'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
            $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=2 AND IS_MARCHE=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID DESC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'],'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

            $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
            
            $table4='execution_budgetaire_tache_detail';
            $conditions4='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXEC_BUDGET_RAC_DET_ID;
            $datatomodifie4= 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID;
            $this->update_all_table($table4,$datatomodifie4,$conditions4);

            //Enregistrement dans historique raccrochage
            $columsinsert_histo="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID,DATE_RECEPTION,DATE_TRANSMISSION";

            $datatoinsert_histo="".$EXEC_BUDGET_RAC_DET_ID.",".$USER_ID_ENG.",'".str_replace("'", "\'", $COMMENTAIRE)."',".$ETAPE_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";

            $table_histo='execution_budgetaire_tache_detail_histo';
            $this->save_all_table($table_histo,$columsinsert_histo,$datatoinsert_histo);

            $this->gestion_rejet_ptba($EXEC_BUDGET_RAC_ID);
          }
        }



        $data = [
          'message' => ''.lang('messages_lang.confirmer_message').''
        ];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Menu_Engagement_Juridique/eng_jur_valider');

      }
      else
      {

      $session  = \Config\Services::session();
      $user_id ='';
      $ced = $session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE_CONFIRM_CED');
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($ced!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $data = $this->urichk();

      ################################ DETAILS D'INFOS ############################
      $getdemanande = "SELECT exec.DEVISE_TYPE_HISTO_ENG_ID,exec.EXECUTION_BUDGETAIRE_ID, exec.SOUS_TUTEL_ID, det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.NUMERO_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT,exec.ENG_BUDGETAIRE AS MONTANT_RACCROCHE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,progr.CODE_PROGRAMME,actions.LIBELLE_ACTION,actions.ACTION_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,exec.DATE_DEMANDE, det.ETAPE_DOUBLE_COMMANDE_ID, exec.MARCHE_PUBLIQUE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,det.COUR_DEVISE,exec.ENG_BUDGETAIRE_DEVISE AS MONTANT_RACCROCHE_DEVISE,exec.INSTITUTION_ID,exec.SOUS_TUTEL_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN  inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$EXEC_BUDGET_RAC_DET_ID."'";
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
            $benef = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','1','DESCRIPTION ASC');
            $data['operation'] = $this->ModelPs->getRequete($psgetrequete, $benef);

            //récuperer les modeles
            $modele = $this->getBindParms('MODELE_ID,DESC_MODELE', 'modele','1','DESC_MODELE ASC');
            $data['get_modele'] = $this->ModelPs->getRequete($psgetrequete, $modele);

            //récuperer l'étape à corriger
            $step_correct = $this->getBindParms('ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR', 'budgetaire_etape_retour_correction','ETAPE_RETOUR_CORRECTION_ID <3','ETAPE_RETOUR_CORRECTION_ID ASC');
            $data['get_correct'] = $this->ModelPs->getRequete($psgetrequete, $step_correct);

            //Le titre de l'étape
            $bind_step_title = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','ETAPE_DOUBLE_COMMANDE_ID='.$data['details']['ETAPE_DOUBLE_COMMANDE_ID'],'ETAPE_DOUBLE_COMMANDE_ID DESC');
            $bind_step_title = str_replace('\\','',$bind_step_title);
            $data['get_step_title'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_step_title);

            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,DATE_TRANSMISSION,DATE_INSERTION','execution_budgetaire_tache_detail_histo','MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$EXEC_BUDGET_RAC_DET_ID.'"','DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo);

            //Récuperer les motifs

            if ($data['details']['MARCHE_PUBLIQUE']==1)
            {

              $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','IS_MARCHE=1 AND MOUVEMENT_DEPENSE_ID=2','TYPE_ANALYSE_MOTIF_ID ASC');
              $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);
            } else {

              $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','IS_MARCHE=0 AND MOUVEMENT_DEPENSE_ID=2','TYPE_ANALYSE_MOTIF_ID ASC');
              $data['motif'] = $this->ModelPs->getRequete($psgetrequete, $bind_motif);
            }


            //Récupération du sous titre
            $bind_sous_tut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL, DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='. $data['details']['SOUS_TUTEL_ID'], 'DESCRIPTION_SOUS_TUTEL ASC');

            $data['sous_tut'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_sous_tut);

            $data['EXEC_BUDGET_RAC_DET_ID'] = $data['details']['EXECUTION_BUDGETAIRE_DETAIL_ID'];
            $data['EXEC_BUDGET_RAC_ID'] = $data['details']['EXECUTION_BUDGETAIRE_ID'];
            $data['ETAPE_ID']=$data['details']['ETAPE_DOUBLE_COMMANDE_ID'];

            ########################## FIN D'INFOS #####################

            $EXEC_BUDG_RAC_ID = md5($data['EXEC_BUDGET_RAC_ID']);
            $detail=$this->detail_new($EXEC_BUDGET_RAC_DET_ID);

            $data['get_info']=$detail['get_info'];
            $data['montantvote']=$detail['montantvote'];
            $data['creditVote']=$detail['creditVote'];
            $data['montant_reserve']=$detail['montant_reserve'];

            return view('App\Modules\double_commande_new\Views\Eng_Juridique_Confirm_View',$data);
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



    //Les fournisseurs / acquéreur
    function get_benef()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }


      $TYPE_BENEFICIARE =$this->request->getPost('TYPE_BENEFICIARE');
      $callpsreq = "CALL `getRequete`(?,?,?,?);";

      //récuperer les fournisseurs/acquéreurs
      $prest = $this->getBindParms('PRESTATAIRE_ID,NOM_PRESTATAIRE,PRENOM_PRESTATAIRE, TYPE_BENEFICIAIRE_ID, NIF_PRESTATAIRE','prestataire','TYPE_BENEFICIAIRE_ID='.$TYPE_BENEFICIARE,'NOM_PRESTATAIRE ASC');
      $fourn_acq = $this->ModelPs->getRequete($callpsreq, $prest);

      $html='<option value="">'.lang('messages_lang.selection_message').'</option>';


      if(!empty($fourn_acq))
      {
       foreach($fourn_acq as $key)
       { 
          if ($key->TYPE_BENEFICIAIRE_ID==1) 
          {
            $html.= "<option value='".$key->PRESTATAIRE_ID."'>".$key->NOM_PRESTATAIRE."   ".$key->PRENOM_PRESTATAIRE."&nbsp;&nbsp;&nbsp;NIF : ".$key->NIF_PRESTATAIRE."</strong></option>";
          }else{
            $html.= "<option value='".$key->PRESTATAIRE_ID."'>".$key->NOM_PRESTATAIRE."   ".$key->PRENOM_PRESTATAIRE."</strong></option>";
          }

        }
      }
      $output = array('status' => TRUE ,'benef' => $html);
          return $this->response->setJSON($output);//echo json_encode($output);
    }


    //Séléctionner les sous titres
        function get_soutut()
        {
          $session  = \Config\Services::session();  
          $INSTITUTION_ID =$this->request->getPost('INSTITUTION_ID');

          $callpsreq = "CALL `getRequete`(?,?,?,?);";

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

          if($session->get('SESSION_SUIVIE_PTBA_ENGAG_JURIDIQUE')!=1)
          {
            return redirect('Login_Ptba/homepage'); 
          }

          $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
          $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);


          $soutut = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel','INSTITUTION_ID='.$INSTITUTION_ID.'','DESCRIPTION_SOUS_TUTEL ASC','inst_institutions_sous_tutel','INSTITUTION_ID='.$INSTITUTION_ID.'','DESCRIPTION_SOUS_TUTEL ASC');
          $get_soutut = $this->ModelPs->getRequete($callpsreq, $soutut);
          //print_r($get_soutut);exit();

          $html='<option value="">'.lang('messages_lang.selection_message').'</option>';

          if(!empty($get_soutut))
          {
           foreach($get_soutut as $key)
           { 

            $html.= "<option value='".$key->SOUS_TUTEL_ID."'>".$key->CODE_SOUS_TUTEL." - ".$key->DESCRIPTION_SOUS_TUTEL."</option>";
            
          }
        }
        $output = array('status' => TRUE ,'html' => $html);
          return $this->response->setJSON($output);//echo json_encode($output);


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