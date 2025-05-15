<?php
/*
*MUNEZERO SONIA
*Titre: phase administrative
*Numero de telephone: (+257) 65165772
*Email: sonia@mediabox.bi
*Date: 23 octobre,2023
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Phase_Administrative_Budget extends BaseController
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
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $DESCRIPTION_MOTIF = $this->request->getPost('DESCRIPTION_MOTIF');
    $MARCHE_PUBLIQUE = $this->request->getPost('MARCHE_PUBLIQUE');
    $MOUVEMENT_DEPENSE_ID=1;

    $table="budgetaire_type_analyse_motif";
    $columsinsert = "MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE";
    $datacolumsinsert = "{$MOUVEMENT_DEPENSE_ID},'{$DESCRIPTION_MOTIF}',{$MARCHE_PUBLIQUE}";    
    $this->save_all_table($table,$columsinsert,$datacolumsinsert);

    $callpsreq = "CALL getRequete(?,?,?,?);";

      //récuperer les motifs
    $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','MOUVEMENT_DEPENSE_ID=1 AND IS_MARCHE='.$MARCHE_PUBLIQUE,'DESC_TYPE_ANALYSE_MOTIF ASC');
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

  // affiche le view pour la 1er etape d'engagement budgetaire (engage)
  function etape1()
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ETAPE_DOUBLE_COMMANDE_ID=1;

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_ID DESC');
          $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

          $data['etape1'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];
          $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID', 'user_affectaion', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
          $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

          $ID_INST='';

          foreach ($getaffect as $value)
          {
            $ID_INST.=$value->INSTITUTION_ID.' ,';           
          }
          $ID_INST = substr($ID_INST,0,-1);

          $getInst  = 'SELECT TYPE_INSTITUTION_ID,INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
          $getInst = "CALL `getTable`('" . $getInst . "');";
          $data['institutions'] = $this->ModelPs->getRequete($getInst);

          $getMasse  = 'SELECT TYPE_ENGAGEMENT_ID,DESC_TYPE_ENGAGEMENT FROM type_engagement ORDER BY DESC_TYPE_ENGAGEMENT ASC';
          $getMasse = "CALL `getTable`('" . $getMasse . "');";
          $data['grande']= $this->ModelPs->getRequete($getMasse);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);

          $getTypeMarche  = 'SELECT ID_TYPE_MARCHE,DESCR_MARCHE FROM type_marche ORDER BY DESCR_MARCHE ASC';
          $getTypeMarche = "CALL `getTable`('" . $getTypeMarche . "');";
          $data['type_marche']= $this->ModelPs->getRequete($getTypeMarche);

          $get_typ_docs  = 'SELECT BUDGETAIRE_TYPE_DOCUMENT_ID,DESC_BUDGETAIRE_TYPE_DOCUMENT FROM budgetaire_type_document ORDER BY DESC_BUDGETAIRE_TYPE_DOCUMENT ASC';
          $get_typ_docs = "CALL `getTable`('" . $get_typ_docs . "');";
          $data['get_typ_docs']= $this->ModelPs->getRequete($get_typ_docs);

          $dataa=$this->converdate();
          $data['debut'] = $dataa['debut'];

          return view('App\Modules\double_commande_new\Views\Eng_Budget_Etapes1_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  // insertion de l'engagement budgetaire
  function save_etape1()
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


    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $rules = [
      'INSTITUTION_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'SOUS_TUTEL_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'MONTANT_RACCROCHE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'TYPE_ENGAGEMENT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'MARCHE_PUBLIQUE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'QTE_RACCROCHE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'BUDGETAIRE_TYPE_DOCUMENT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'COMMENTAIRE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');

    $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
    if ($TYPE_MONTANT_ID != 1)
    {
      $rules['MONTANT_EN_DEVISE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
      $rules['engagement_cous'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    }

    $this->validation->setRules($rules);

    if($this->validation->withRequest($this->request)->run())
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user = $this->getBindParms('USER_ID', 'user_users', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
      $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);
      $USER_ID=$getuser['USER_ID'];

      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
      $PAP_ACTIVITE_ID = $this->request->getPost('PAP_ACTIVITE_ID');
      $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
      $MONTANT_RACCROCHE = $this->request->getPost('engagement_budget');
      $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');
      $MARCHE_PUBLIQUE=$this->request->getPost('MARCHE_PUBLIQUE');
      $COMMENTAIRE=$this->request->getPost('COMMENTAIRE');
      $CODE_ACTION=$this->request->getPost('action_code');
      $CODE_PROGRAMME=$this->request->getPost('program_code');
      $CREDIT_VOTE=$this->request->getPost('montant_vote');  
      $TRANSFERTS_CREDITS=$this->request->getPost('get_trans');
      $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
      $UNITE=$this->request->getPost('UNITE');
      $TAUX_ECHANGE_ID=$this->request->getPost('TAUX_ECHANGE_ID'); 
      $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
      $ACTION_ID = $this->request->getPost('ACTION_ID');
      //$DEVISE_TYPE_HISTO_ID = ($TYPE_MONTANT_ID==1) ? 1 : $this->request->getPost('DEVISE_TYPE_HISTO_ID');
      // $COUS_ECHANGE= ($TYPE_MONTANT_ID==1) ? 1 : $this->request->getPost('COUS_ECHANGE');
      $COUS_ECHANGE= ($TYPE_MONTANT_ID==1) ? 1 : $this->request->getPost('engagement_cous');
      $COUS_ECHANGE=str_replace(" ", "", $COUS_ECHANGE);
      $BUDGETAIRE_TYPE_DOCUMENT_ID=$this->request->getPost('BUDGETAIRE_TYPE_DOCUMENT_ID');

      $sous_act=$this->request->getPost('sous_act'); 
      $fini=0; 
      $resultat_attend=0;
      $observ_resultat='';
      $QTE_RACCROCHE=0;
      if ($sous_act==1)
      {
        $fini=$this->request->getPost('fini'); 
        if ($fini==1)
        {
          $observ_resultat=$this->request->getPost('observ');
          $resultat_attend=$this->request->getPost('resultat_attend'); 
          $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');
        }
        else
        {
          $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');
        }
      }
      else
      {
        $resultat_attend=$this->request->getPost('QTE_RACCROCHE'); 
        $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');     
      }

      $CREDIT_APRES_TRANSFERT= floatval($CREDIT_VOTE) + floatval($TRANSFERTS_CREDITS) ;
      $IS_TRANSFERT_ACTIVITE = ($TRANSFERTS_CREDITS == 0) ? 0 : 1 ;

      $TRIMESTRE_ID=$this->request->getPost('TRIMESTRE_ID'); 
      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = 1',' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
      $etape_suivante= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);
      $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
      $MONTANT_EN_DEVISE=0;        
      $DATE_COUT_DEVISE= null;

      if ($TYPE_MONTANT_ID!=1)
      {
        $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
        $MONTANT_EN_DEVISE=$this->request->getPost('engagement_devise');
        $DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');
        $MONTANT_RACCROCHE = floatval($COUS_ECHANGE) * floatval($MONTANT_EN_DEVISE);
        $MONTANT_RACCROCHE=number_format($MONTANT_RACCROCHE,0, ',', '');
      }        

      $PATH_PPM_DOC='';
      $PATH_PV_ATTRIBUTION='';
      $PATH_AVIS_DNCMP='';
      $ID_TYPE_MARCHE=0;
      if ($MARCHE_PUBLIQUE==1)
      {
        $maxFileSize = 20000 * 1024;
        $ID_TYPE_MARCHE=$this->request->getPost('ID_TYPE_MARCHE');
        $path_avis=$this->request->getFile('path_avis'); 
        if ($path_avis->getSize() > $maxFileSize)
        {
          $data=['message' => "".lang('messages_lang.pdf_max').""];
          session()->setFlashdata('alert', $data);
          return $this->etape1();
        }
        $PATH_AVIS_DNCMP=$this->uploadFile('path_avis','double_commande_new','AVIS_DNCMP');

        $path_pv=$this->request->getFile('path_pv'); 
        if ($path_pv->getSize() > $maxFileSize)
        {
          $data=['message' => "".lang('messages_lang.pdf_max').""];
          session()->setFlashdata('alert', $data);
          return $this->etape1();
        }
        $PATH_PV_ATTRIBUTION=$this->uploadFile('path_pv','double_commande_new','PV_ATTRIBUTION');

        $PATH_PPM=$this->request->getFile('PATH_PPM');
        if (!$PATH_PPM || !$PATH_PPM->isValid())
        {
          $PATH_PPM_DOC='';
        } 
        else
        {
          if ($PATH_PPM->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->etape1();
          }
          $PATH_PPM_DOC=$this->uploadFile('PATH_PPM','double_commande_new','PPM');
        }
      }
      
      $PATH_LETTRE_OTB='';
      $PATH_LETTRE_TRANSMISSION='';
      $PATH_LISTE_PAIE='';
      $PATH_LETTRE_OTB=$this->request->getFile('PATH_LETTRE_OTB');
      $maxFileSize = 20000 * 1024;
      if ($PATH_LETTRE_OTB->getSize() > $maxFileSize)
      {
        $data=['message' => "".lang('messages_lang.pdf_max').""];
        session()->setFlashdata('alert', $data);
        return $this->etape1();
      }
      $PATH_LETTRE_OTB=$this->uploadFile('PATH_LETTRE_OTB','double_commande_new','LETTRE_OTB');

      if ($TYPE_ENGAGEMENT_ID==1)
      {
          // $PATH_LETTRE_OTB=$this->request->getFile('PATH_LETTRE_OTB');
        $PATH_LETTRE_TRANSMISSION=$this->request->getFile('PATH_LETTRE_TRANSMISSION');
        $PATH_LISTE_PAIE=$this->request->getFile('PATH_LISTE_PAIE');  
        $maxFileSize = 20000 * 1024;

          // if ($PATH_LETTRE_OTB->getSize() > $maxFileSize)
          // {
          //   $data=['message' => "".lang('messages_lang.pdf_max').""];
          //   session()->setFlashdata('alert', $data);
          //   return $this->etape1();
          // }

        if (!$PATH_LETTRE_TRANSMISSION || !$PATH_LETTRE_TRANSMISSION->isValid())
        {
          $PATH_LETTRE_TRANSMISSION='';
        }
        else
        {
          if ($PATH_LETTRE_TRANSMISSION->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->etape1();
          }
          $PATH_LETTRE_TRANSMISSION=$this->uploadFile('PATH_LETTRE_TRANSMISSION','double_commande_new','LETTRE_TRANSMISSION');
        }

        if ($PATH_LISTE_PAIE->getSize() > $maxFileSize)
        {
          $data=['message' => "".lang('messages_lang.pdf_max').""];
          session()->setFlashdata('alert', $data);
          return $this->etape1();
        }
          // $PATH_LETTRE_OTB=$this->uploadFile('PATH_LETTRE_OTB','double_commande_new','LETTRE_OTB');

        $PATH_LISTE_PAIE=$this->uploadFile('PATH_LISTE_PAIE','double_commande_new','LISTE_PAIE');
      }

      $getTache = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PTBA_TACHE_ID='.$PTBA_TACHE_ID;
      $getTache = "CALL `getTable`('" . $getTache . "');";
      $taches= $this->ModelPs->getRequeteOne($getTache);

      $imputa = 'SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID;
      $imputa = "CALL `getTable`('" . $imputa . "');";
      $IMPUTATI= $this->ModelPs->getRequeteOne($imputa);
      $IMPUTATION = $IMPUTATI['CODE_NOMENCLATURE_BUDGETAIRE'];

      $MOUVEMENT_DEPENSE_ID=1;
      $TYPE_DOCUMENT_ID=1;
      $ETAPE_ID=$etape_suivante['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];

      $COMMENTAIRE = str_replace("\n","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace("\r","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace("\t","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace('"','',$COMMENTAIRE);
      $COMMENTAIRE = str_replace("'","\'",$COMMENTAIRE);

      $observ_resultat = str_replace("\n","",$observ_resultat);
      $observ_resultat = str_replace("\r","",$observ_resultat);
      $observ_resultat = str_replace("\t","",$observ_resultat);
      $observ_resultat = str_replace('"','',$observ_resultat);
      $observ_resultat = str_replace("'","\'",$observ_resultat);

      $QTE_RACCROCHE = str_replace(".",",",$QTE_RACCROCHE);
      $resultat_attend = str_replace(".",",",$resultat_attend);

      $LIBELLE = str_replace("\n","",$taches['DESC_TACHE']);
      $LIBELLE = str_replace("\r","",$LIBELLE);
      $LIBELLE = str_replace("\t","",$LIBELLE);
      $LIBELLE = str_replace('"','',$LIBELLE);
      $LIBELLE = str_replace("'","\'",$LIBELLE);

      $UNITE = str_replace("\n","",$UNITE);
      $UNITE = str_replace("\r","",$UNITE);
      $UNITE = str_replace("\t","",$UNITE);
      $UNITE = str_replace('"','',$UNITE);
      $UNITE = str_replace("'","\'",$UNITE);

      $ANNEE_BUDGETAIRE_ID = $this->get_annee_budgetaire();

      $MoneyRest  = 'SELECT ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T1,BUDGET_UTILISE_T2,BUDGET_UTILISE_T3,BUDGET_UTILISE_T4 FROM ptba_tache WHERE PTBA_TACHE_ID='.$PTBA_TACHE_ID;
      $MoneyRest = "CALL `getTable`('" . $MoneyRest . "');";
      $RestPTBA = $this->ModelPs->getRequeteOne($MoneyRest);

      $existance = 'SELECT EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire  WHERE PTBA_TACHE_ID='.$PTBA_TACHE_ID.' AND NUMERO_BON_ENGAGEMENT IS NULL';
      $existance="CALL `getList`('".$existance."')";
      $id_exist= $this->ModelPs->getRequeteOne($existance);

      if (empty($id_exist))
      {
        /* si le taux existe sur la meme date et meme devise*/
        $DEVISE_TYPE_HISTO_ID=1;
        if($TYPE_MONTANT_ID!=1)
        {
          $taux_exist = "SELECT DEVISE_TYPE_HISTO_ID,DEVISE_TYPE_ID,TAUX,DATE_INSERTION FROM devise_type_hist WHERE DEVISE_TYPE_ID=".$TYPE_MONTANT_ID." AND TAUX=".$COUS_ECHANGE." AND DATE_INSERTION LIKE '$DATE_COUT_DEVISE%'";
          $taux_exist='CALL `getTable`("'.$taux_exist.'")';
          $taux_exist= $this->ModelPs->getRequeteOne($taux_exist);
          if(!empty($taux_exist))
          {
            $DEVISE_TYPE_HISTO_ID=$taux_exist["DEVISE_TYPE_HISTO_ID"];
          }
          else
          {
            $columns="DEVISE_TYPE_ID,TAUX,IS_ACTIVE,DATE_INSERTION";
            $data_col=$TYPE_MONTANT_ID.",".$COUS_ECHANGE.",0,'".$DATE_COUT_DEVISE."'";
            $table_dev="devise_type_hist";
            $DEVISE_TYPE_HISTO_ID =$this->save_all_table($table_dev,$columns,$data_col);
          }
        }
        /* si le taux existe sur la meme date et meme devise*/

        $col_pap='';
        $val_pap='';
        if (!empty($PAP_ACTIVITE_ID))
        {
          $col_pap=',PAP_ACTIVITE_ID';
          $val_pap=','.$PAP_ACTIVITE_ID;
        }
        $insertIntoExec='execution_budgetaire';
        $columExec="ANNEE_BUDGETAIRE_ID,TRIMESTRE_ID,INSTITUTION_ID,ENG_BUDGETAIRE,ENG_BUDGETAIRE_DEVISE,DEVISE_TYPE_HISTO_ENG_ID,SOUS_TUTEL_ID,PROGRAMME_ID,ACTION_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID".$col_pap.",PTBA_TACHE_ID,QTE_RACCROCHE,USER_ID,UNITE,MARCHE_PUBLIQUE,COMMENTAIRE,DEVISE_TYPE_ID";

        $datacolumsExec=$ANNEE_BUDGETAIRE_ID.",".$TRIMESTRE_ID.",".$INSTITUTION_ID.",".$MONTANT_RACCROCHE.",".$MONTANT_EN_DEVISE.",".$DEVISE_TYPE_HISTO_ID.",".$SOUS_TUTEL_ID.",".$PROGRAMME_ID.",".$ACTION_ID.",'".$CODE_NOMENCLATURE_BUDGETAIRE_ID."'".$val_pap.",".$PTBA_TACHE_ID.",'".$QTE_RACCROCHE."',".$user_id.",'".$UNITE."',".$MARCHE_PUBLIQUE.",'".$COMMENTAIRE."',".$TYPE_MONTANT_ID;
        $EXECUTION_BUDGETAIRE_ID =$this->save_all_table($insertIntoExec,$columExec,$datacolumsExec);

        $insertDetail = 'execution_budgetaire_tache_detail';
        $columDetail = "EXECUTION_BUDGETAIRE_ID,ETAPE_DOUBLE_COMMANDE_ID,COMMENTAIRE,DATE_COUR_DEVISE,COUR_DEVISE";

        if ($DATE_COUT_DEVISE===null) {

          $datacolumsDetail = $EXECUTION_BUDGETAIRE_ID.",".$ETAPE_DOUBLE_COMMANDE_ID.",'".$COMMENTAIRE."',null,".$COUS_ECHANGE;
        } else {
          $datacolumsDetail = $EXECUTION_BUDGETAIRE_ID.",".$ETAPE_DOUBLE_COMMANDE_ID.",'".$COMMENTAIRE."','".$DATE_COUT_DEVISE."',".$COUS_ECHANGE;
        }

        $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->save_all_table($insertDetail, $columDetail, $datacolumsDetail);

        $insertIntoSuppl='execution_budgetaire_tache_info_suppl';

        $columSuppl="EXECUTION_BUDGETAIRE_ID,TYPE_ENGAGEMENT_ID,BUDGETAIRE_TYPE_DOCUMENT_ID,PATH_PPM,PATH_LETTRE_OTB,PATH_LETTRE_TRANSMISSION,PATH_LISTE_PAIE,EST_SOUS_TACHE,EST_FINI_TACHE,RESULTAT_ATTENDUS,PATH_PV_ATTRIBUTION,PATH_AVIS_DNCMP,ID_TYPE_MARCHE,OBSERVATION_RESULTAT";

        $datacolumsSuppl=$EXECUTION_BUDGETAIRE_ID.",".$TYPE_ENGAGEMENT_ID.",".$BUDGETAIRE_TYPE_DOCUMENT_ID.",'".$PATH_PPM_DOC."','".$PATH_LETTRE_OTB."','".$PATH_LETTRE_TRANSMISSION."','".$PATH_LISTE_PAIE."',".$sous_act.",".$fini.",'".$resultat_attend."','".$PATH_PV_ATTRIBUTION."','".$PATH_AVIS_DNCMP."',".$ID_TYPE_MARCHE.",'".$observ_resultat."'";
        $this->save_all_table($insertIntoSuppl,$columSuppl,$datacolumsSuppl);

        $insertIntohist='execution_budgetaire_tache_detail_histo';
        $columhist="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID";
        $datacolumshist=$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$USER_ID.",'".$COMMENTAIRE."',".$ETAPE_ID;
        $this->save_all_table($insertIntohist,$columhist,$datacolumshist);

        $apresEng = 0;
        $total_utilise = 0;
        if ($TRIMESTRE_ID==1 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_BUDGETAIRE_ID) 
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T1']) - floatval($MONTANT_RACCROCHE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T1']) + floatval($MONTANT_RACCROCHE);

          $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T1=".$apresEng.",BUDGET_UTILISE_T1=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==2 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_BUDGETAIRE_ID)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T2']) - floatval($MONTANT_RACCROCHE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T2']) + floatval($MONTANT_RACCROCHE);

          $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T2=".$apresEng.",BUDGET_UTILISE_T2=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==3 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_BUDGETAIRE_ID)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T3']) - floatval($MONTANT_RACCROCHE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T3']) + floatval($MONTANT_RACCROCHE);

          $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T3=".$apresEng.",BUDGET_UTILISE_T3=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==4 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_BUDGETAIRE_ID)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T4']) - floatval($MONTANT_RACCROCHE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T4']) + floatval($MONTANT_RACCROCHE);

          $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T4=".$apresEng.",BUDGET_UTILISE_T4=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
      }
      else
      {
        $data=['message' => "".lang('messages_lang.bon_null').""];
        session()->setFlashdata('alert', $data);
        return $this->etape1();
      }

      $data=['message' => "".lang('messages_lang.eng_succ').""];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Fait');
    }
    else
    {
      return $this->etape1();
    }
  }

  // affiche le view pour la 1er etape prime d'engagement budgetaire (engage)
  function etape1_prime($id=0)
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    // $infoAffiche  = 'SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.ETAPE_DOUBLE_COMMANDE_ID,exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID) = "'.$id.'"';
    $infoAffiche  = 'SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.ETAPE_DOUBLE_COMMANDE_ID,
                    exec.EXECUTION_BUDGETAIRE_ID,ab.ANNEE_DESCRIPTION,inst.CODE_INSTITUTION
                     FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID 
                     JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID 
                     JOIN annee_budgetaire ab ON exec.ANNEE_BUDGETAIRE_ID=ab.ANNEE_BUDGETAIRE_ID WHERE md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID) = "'.$id.'"';
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
          $data['etape1_prime'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];

          $detail=$this->detail_new(md5($data['info']['EXECUTION_BUDGETAIRE_DETAIL_ID']));
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['creditVote']=$detail['creditVote'];
          $data['montant_reserve']=$detail['montant_reserve'];

          return view('App\Modules\double_commande_new\Views\Eng_Budget_Etapes1_Prime_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  function save_etape1_prime20240712()
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID'); 

    $rules = [
      'NUMERO_BON_ENGAGEMENT' => [
        'label' => '',
        'rules' => 'required|is_unique[execution_budgetaire.NUMERO_BON_ENGAGEMENT]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>',
          'is_unique' => '<font style="color:red;size:2px;">Le numéro existe déjà</font>'
        ]
      ],
      'DATE_ENG_BUDGETAIRE' => [
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
      $NUMERO_BON_ENGAGEMENT=$this->request->getPost('NUMERO_BON_ENGAGEMENT');
      $DATE_ENG_BUDGETAIRE=$this->request->getPost('DATE_ENG_BUDGETAIRE');
      $ETAPE_DOUBLE_COMMANDE_ID=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');

      $PATH_BON=$this->request->getFile('PATH_BON_ENGAGEMENT');  
      $maxFileSize = 20000 * 1024;

      if ($PATH_BON->getSize() > $maxFileSize)
      {
        $data=['message' => "".lang('messages_lang.pdf_max').""];
        session()->setFlashdata('alert', $data);
        return $this->etape1_prime(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
      }

      $PATH_BON_ENGAGEMENT=$this->uploadFile('PATH_BON_ENGAGEMENT','double_commande_new','BON_ENGAGEMENT');

      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,IS_CORRECTION,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
      $etape_suivante22= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);

      $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
      $ETAPE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];

      $wheredet ="EXECUTION_BUDGETAIRE_DETAIL_ID=".$EXECUTION_BUDGETAIRE_DETAIL_ID;
      $insertIntodet='execution_budgetaire_tache_detail';
      $columdet="ETAPE_DOUBLE_COMMANDE_ID = ".$ETAPE_DOUBLE_COMMANDE_ID;
      $this->update_all_table($insertIntodet,$columdet,$wheredet);

      $whereexec ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;        
      $insertIntoexec='execution_budgetaire';
      $columexec="NUMERO_BON_ENGAGEMENT = '".$NUMERO_BON_ENGAGEMENT."',DATE_BON_ENGAGEMENT = '".$DATE_ENG_BUDGETAIRE."',PATH_BON_ENGAGEMENT='".$PATH_BON_ENGAGEMENT."'";
      $this->update_all_table($insertIntoexec,$columexec,$whereexec);

      $insertIntoOp='execution_budgetaire_tache_detail_histo';
      $columOp="EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_TRANSMISSION";
      $datacolumsOp=$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$ETAPE_ID.",".$USER_ID.",'".$DATE_TRANSMISSION."'";

      $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

      $data=['message' => "".lang('messages_lang.ajout_bon').""];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Menu_Engagement_Budgetaire/get_sans_bon_engagement');
    }
    else
    {
      return $this->etape1_prime(md5($EXECUTION_BUDGETAIRE_DETAIL_ID));
    }
  }

  function save_etape1_prime()
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID'); 

    $rules = [
      'NUMERO_BON_ENGAGEMENT' => [
        'label' => '',
        'rules' => 'required|is_unique[execution_budgetaire.NUMERO_BON_ENGAGEMENT]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>',
          'is_unique' => '<font style="color:red;size:2px;">Le numéro existe déjà</font>'
        ]
      ],
      'DATE_ENG_BUDGETAIRE' => [
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
      $NUMERO_BON_ENGAGEMENT=$this->request->getPost('NUMERO_BON_ENGAGEMENT');
      $DATE_ENG_BUDGETAIRE=$this->request->getPost('DATE_ENG_BUDGETAIRE');
      $ETAPE_DOUBLE_COMMANDE_ID=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
      $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');

        // $PATH_BON=$this->request->getFile('PATH_BON_ENGAGEMENT');  
        // $maxFileSize = 20000 * 1024;

        // if ($PATH_BON->getSize() > $maxFileSize)
        // {
        //   $data=['message' => "".lang('messages_lang.pdf_max').""];
        //   session()->setFlashdata('alert', $data);
        //   return $this->etape1_prime(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
        // }

        // $PATH_BON_ENGAGEMENT=$this->uploadFile('PATH_BON_ENGAGEMENT','double_commande_new','BON_ENGAGEMENT');

      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,IS_CORRECTION,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
      $etape_suivante22= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);

      $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
      $ETAPE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];

      $wheredet ="EXECUTION_BUDGETAIRE_DETAIL_ID=".$EXECUTION_BUDGETAIRE_DETAIL_ID;
      $insertIntodet='execution_budgetaire_tache_detail';
      $columdet="ETAPE_DOUBLE_COMMANDE_ID = ".$ETAPE_DOUBLE_COMMANDE_ID;
      $this->update_all_table($insertIntodet,$columdet,$wheredet);

      $whereexec ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;        
      $insertIntoexec='execution_budgetaire';
      $columexec="NUMERO_BON_ENGAGEMENT = '".$NUMERO_BON_ENGAGEMENT."',DATE_BON_ENGAGEMENT = '".$DATE_ENG_BUDGETAIRE."'";
      $this->update_all_table($insertIntoexec,$columexec,$whereexec);

      $insertIntoOp='execution_budgetaire_tache_detail_histo';
      $columOp="EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_TRANSMISSION";
      $datacolumsOp=$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$ETAPE_ID.",".$USER_ID.",'".$DATE_TRANSMISSION."'";

      $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

      $data=['message' => "".lang('messages_lang.ajout_bon').""];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Menu_Engagement_Budgetaire/get_sans_bon_engagement');
    }
    else
    {
      return $this->etape1_prime(md5($EXECUTION_BUDGETAIRE_DETAIL_ID));
    }
  }

  // affiche le view pour la correction de la 1er etape d'engagement budgetaire
  function corrige_etape1_20240712($id=0)
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }        

    $infoAffiche  = 'SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.COMMENTAIRE,exec.QTE_RACCROCHE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.CODE_NOMENCLATURE_BUDGETAIRE_ID,tache.PTBA_TACHE_ID,tache.DESC_TACHE,act.PAP_ACTIVITE_ID,act.DESC_PAP_ACTIVITE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,progr.INTITULE_PROGRAMME,actions.ACTION_ID,progr.CODE_PROGRAMME,exec.DEVISE_TYPE_ID,actions.LIBELLE_ACTION,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,exec.SOUS_TUTEL_ID,exec.INSTITUTION_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.NUMERO_BON_ENGAGEMENT,exec.MARCHE_PUBLIQUE,inst.TYPE_INSTITUTION_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID) = "'.$id.'"';
    $infoAffiche = "CALL `getTable`('" .$infoAffiche."');";
    $data['get_info']= $this->ModelPs->getRequeteOne($infoAffiche);

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['get_info']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {

        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$data['get_info']['ETAPE_DOUBLE_COMMANDE_ID'],' DESC_ETAPE_DOUBLE_COMMANDE DESC');
          $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

          $data['etape_correction'] =$titre['DESC_ETAPE_DOUBLE_COMMANDE'];

          $rej  = 'SELECT rej.DATE_TRANSMISSION,rej.MOTIF_REJET,rej.OBSERVATION FROM execution_budgetaire_tache_detail_histo rej WHERE rej.EXECUTION_BUDGETAIRE_DETAIL_ID = "'.$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID'].'" ORDER BY EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID  DESC';
          $rej = "CALL `getTable`('" .$rej. "');";
          $get_rej= $this->ModelPs->getRequeteOne($rej);

          $data['rejet'] = (!empty($get_rej)) ? $get_rej['MOTIF_REJET'] : '' ;

          $date_eng  = 'SELECT supp.PATH_LETTRE_OTB,supp.PATH_LETTRE_TRANSMISSION,supp.PATH_LISTE_PAIE,supp.PATH_PPM,exec.NUMERO_BON_ENGAGEMENT,exec.PATH_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT AS DATE_ENGAGEMENT_BUDGETAIRE,supp.TYPE_ENGAGEMENT_ID,exec.ENG_BUDGETAIRE_DEVISE,det.COUR_DEVISE,det.DATE_COUR_DEVISE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,supp.EST_SOUS_TACHE,supp.EST_FINI_TACHE,RESULTAT_ATTENDUS,PATH_PV_ATTRIBUTION,PATH_AVIS_DNCMP,ID_TYPE_MARCHE,supp.OBSERVATION_RESULTAT FROM execution_budgetaire_tache_info_suppl supp JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=supp.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE supp.EXECUTION_BUDGETAIRE_ID='.$data['get_info']['EXECUTION_BUDGETAIRE_ID'];   
          $date_eng = "CALL `getTable`('" . $date_eng . "');";
          $data['get_date_eng']= $this->ModelPs->getRequeteOne($date_eng);

          $callpsreq = "CALL `getRequete`(?,?,?,?);";     
          $bind_date_histo = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,OBSERVATION,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_DETAIL_ID='.$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID'],'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\','',$bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

            //Récuperation de l'étape précedent
          $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_DETAIL_ID='.$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID'],'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
          $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
          $etap_prev = $this->ModelPs->getRequeteOne($callpsreq, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
          $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND EXECUTION_BUDGETAIRE_DETAIL_ID=".$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID']." AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
          $motif_rejetRqt = 'CALL getTable("' . $motif_rejet . '");';
          $data['get_histmotif']= $this->ModelPs->getRequete($motif_rejetRqt);

          $trans = 'SELECT DATE_TRANSMISSION FROM execution_budgetaire_tache_detail_histo WHERE ETAPE_DOUBLE_COMMANDE_ID='.$data['date_trans']['ETAPE_ID'].' AND EXECUTION_BUDGETAIRE_DETAIL_ID ="'.$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID'].'"';
          $trans="CALL `getList`('".$trans."')";
          $data['da_trans']= $this->ModelPs->getRequeteOne($trans);

          $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
          $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

          $ID_INST='';
          foreach ($getaffect as $value)
          {
            $ID_INST.=$value->INSTITUTION_ID.' ,';           
          }
          $ID_INST = substr($ID_INST,0,-1);

          $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
          $getInst = "CALL `getTable`('" .$getInst. "');";
          $data['institutions'] = $this->ModelPs->getRequete($getInst);

          $getSousTutel  = 'SELECT SOUS_TUTEL_ID,CODE_SOUS_TUTEL,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE INSTITUTION_ID='.$data['get_info']['INSTITUTION_ID'].' ORDER BY CODE_SOUS_TUTEL ASC';
          $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
          $data['sous_titre'] = $this->ModelPs->getRequete($getSousTutel);

          $ligne  = 'SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE SOUS_TUTEL_ID='.$data['get_info']['SOUS_TUTEL_ID'].' AND INSTITUTION_ID='.$data['get_info']['INSTITUTION_ID'].' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE ASC';
          $ligne = "CALL `getTable`('" . $ligne. "');";
          $data['get_ligne'] = $this->ModelPs->getRequete($ligne);

          $activite  = 'SELECT PAP_ACTIVITE_ID,DESC_PAP_ACTIVITE FROM pap_activites WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID ="'.$data['get_info']['CODE_NOMENCLATURE_BUDGETAIRE_ID'].'" ORDER BY PAP_ACTIVITE_ID ASC';
          $activite = "CALL `getTable`('" . $activite . "');";
          $data['get_activite'] = $this->ModelPs->getRequete($activite);


          $tache  = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PAP_ACTIVITE_ID ="'.$data['get_info']['PAP_ACTIVITE_ID'].'" ORDER BY DESC_TACHE ASC';
          if($data['get_info']['TYPE_INSTITUTION_ID']==1)
          {
            $tache  = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID ="'.$data['get_info']['CODE_NOMENCLATURE_BUDGETAIRE_ID'].'" ORDER BY DESC_TACHE ASC';
          }
          $tache = "CALL `getTable`('" . $tache . "');";
          $data['get_taches'] = $this->ModelPs->getRequete($tache);

          $getMasse  = 'SELECT TYPE_ENGAGEMENT_ID,DESC_TYPE_ENGAGEMENT FROM type_engagement ORDER BY DESC_TYPE_ENGAGEMENT ASC';
          $getMasse = "CALL `getTable`('" . $getMasse . "');";
          $data['grande']= $this->ModelPs->getRequete($getMasse);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);

          $getTypeMarche  = 'SELECT ID_TYPE_MARCHE,DESCR_MARCHE FROM type_marche ORDER BY DESCR_MARCHE ASC';
          $getTypeMarche = "CALL `getTable`('" . $getTypeMarche . "');";
          $data['type_marche']= $this->ModelPs->getRequete($getTypeMarche);

          $dataa=$this->converdate();
          $data['debut'] = $dataa['debut'];

          return view('App\Modules\double_commande_new\Views\Corrige_Etape1_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }  
  }

  function corrige_etape1($id=0)
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }        

    $infoAffiche  = 'SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.COMMENTAIRE,exec.QTE_RACCROCHE,det.ETAPE_DOUBLE_COMMANDE_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.CODE_NOMENCLATURE_BUDGETAIRE_ID,tache.PTBA_TACHE_ID,tache.DESC_TACHE,act.PAP_ACTIVITE_ID,act.DESC_PAP_ACTIVITE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,progr.INTITULE_PROGRAMME,actions.ACTION_ID,progr.CODE_PROGRAMME,exec.DEVISE_TYPE_ID,exec.DEVISE_TYPE_HISTO_ENG_ID,actions.LIBELLE_ACTION,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,inst.CODE_INSTITUTION AS CODE_MINISTERE,exec.SOUS_TUTEL_ID,exec.INSTITUTION_ID,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,exec.NUMERO_BON_ENGAGEMENT,exec.MARCHE_PUBLIQUE,inst.TYPE_INSTITUTION_ID,exec.TRIMESTRE_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=exec.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=exec.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID) = "'.$id.'"';
    $infoAffiche = "CALL `getTable`('" .$infoAffiche."');";
    $data['get_info']= $this->ModelPs->getRequeteOne($infoAffiche);

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['get_info']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {

        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$data['get_info']['ETAPE_DOUBLE_COMMANDE_ID'],' DESC_ETAPE_DOUBLE_COMMANDE DESC');
          $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

          $data['etape_correction'] =$titre['DESC_ETAPE_DOUBLE_COMMANDE'];

          $rej  = 'SELECT rej.DATE_TRANSMISSION,rej.MOTIF_REJET,rej.OBSERVATION FROM execution_budgetaire_tache_detail_histo rej WHERE rej.EXECUTION_BUDGETAIRE_DETAIL_ID = "'.$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID'].'" ORDER BY EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID  DESC';
          $rej = "CALL `getTable`('" .$rej. "');";
          $get_rej= $this->ModelPs->getRequeteOne($rej);

          $data['rejet'] = (!empty($get_rej)) ? $get_rej['MOTIF_REJET'] : '' ;

          $date_eng  = 'SELECT supp.PATH_LETTRE_OTB,supp.PATH_LETTRE_TRANSMISSION,supp.PATH_LISTE_PAIE,supp.PATH_PPM,exec.NUMERO_BON_ENGAGEMENT,exec.PATH_BON_ENGAGEMENT,exec.DATE_BON_ENGAGEMENT AS DATE_ENGAGEMENT_BUDGETAIRE,supp.TYPE_ENGAGEMENT_ID,exec.ENG_BUDGETAIRE_DEVISE,det.COUR_DEVISE,det.DATE_COUR_DEVISE,exec.DEVISE_TYPE_ID AS TAUX_ECHANGE_ID,supp.EST_SOUS_TACHE,supp.EST_FINI_TACHE,RESULTAT_ATTENDUS,PATH_PV_ATTRIBUTION,PATH_AVIS_DNCMP,ID_TYPE_MARCHE,supp.OBSERVATION_RESULTAT,BUDGETAIRE_TYPE_DOCUMENT_ID FROM execution_budgetaire_tache_info_suppl supp JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=supp.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE supp.EXECUTION_BUDGETAIRE_ID='.$data['get_info']['EXECUTION_BUDGETAIRE_ID'];   
          $date_eng = "CALL `getTable`('" . $date_eng . "');";
          $data['get_date_eng']= $this->ModelPs->getRequeteOne($date_eng);

          $callpsreq = "CALL `getRequete`(?,?,?,?);";     
          $bind_date_histo = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,OBSERVATION,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_DETAIL_ID='.$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID'],'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\','',$bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

            //Récuperation de l'étape précedent
          $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_DETAIL_ID='.$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID'],'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
          $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
          $etap_prev = $this->ModelPs->getRequeteOne($callpsreq, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
          $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND EXECUTION_BUDGETAIRE_DETAIL_ID=".$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID']." AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
          $motif_rejetRqt = 'CALL getTable("' . $motif_rejet . '");';
          $data['get_histmotif']= $this->ModelPs->getRequete($motif_rejetRqt);

          $trans = 'SELECT DATE_TRANSMISSION FROM execution_budgetaire_tache_detail_histo WHERE ETAPE_DOUBLE_COMMANDE_ID='.$data['date_trans']['ETAPE_ID'].' AND EXECUTION_BUDGETAIRE_DETAIL_ID ="'.$data['get_info']['EXECUTION_BUDGETAIRE_DETAIL_ID'].'"';
          $trans="CALL `getList`('".$trans."')";
          $data['da_trans']= $this->ModelPs->getRequeteOne($trans);

          $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
          $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

          $ID_INST='';
          foreach ($getaffect as $value)
          {
            $ID_INST.=$value->INSTITUTION_ID.' ,';           
          }
          $ID_INST = substr($ID_INST,0,-1);

          $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
          $getInst = "CALL `getTable`('" .$getInst. "');";
          $data['institutions'] = $this->ModelPs->getRequete($getInst);

          $getSousTutel  = 'SELECT SOUS_TUTEL_ID,CODE_SOUS_TUTEL,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE INSTITUTION_ID='.$data['get_info']['INSTITUTION_ID'].' ORDER BY CODE_SOUS_TUTEL ASC';
          $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
          $data['sous_titre'] = $this->ModelPs->getRequete($getSousTutel);

          $ligne  = 'SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE SOUS_TUTEL_ID='.$data['get_info']['SOUS_TUTEL_ID'].' AND INSTITUTION_ID='.$data['get_info']['INSTITUTION_ID'].' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE ASC';
          $ligne = "CALL `getTable`('" . $ligne. "');";
          $data['get_ligne'] = $this->ModelPs->getRequete($ligne);

          $activite  = 'SELECT PAP_ACTIVITE_ID,DESC_PAP_ACTIVITE FROM pap_activites WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID ="'.$data['get_info']['CODE_NOMENCLATURE_BUDGETAIRE_ID'].'" ORDER BY PAP_ACTIVITE_ID ASC';
          $activite = "CALL `getTable`('" . $activite . "');";
          $data['get_activite'] = $this->ModelPs->getRequete($activite);


          $tache  = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PAP_ACTIVITE_ID ="'.$data['get_info']['PAP_ACTIVITE_ID'].'" ORDER BY DESC_TACHE ASC';
          if($data['get_info']['TYPE_INSTITUTION_ID']==1)
          {
            $tache  = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID ="'.$data['get_info']['CODE_NOMENCLATURE_BUDGETAIRE_ID'].'" ORDER BY DESC_TACHE ASC';
          }
          $tache = "CALL `getTable`('" . $tache . "');";
          $data['get_taches'] = $this->ModelPs->getRequete($tache);

          $getMasse  = 'SELECT TYPE_ENGAGEMENT_ID,DESC_TYPE_ENGAGEMENT FROM type_engagement ORDER BY DESC_TYPE_ENGAGEMENT ASC';
          $getMasse = "CALL `getTable`('" . $getMasse . "');";
          $data['grande']= $this->ModelPs->getRequete($getMasse);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);

          $getTypeMarche  = 'SELECT ID_TYPE_MARCHE,DESCR_MARCHE FROM type_marche ORDER BY DESCR_MARCHE ASC';
          $getTypeMarche = "CALL `getTable`('" . $getTypeMarche . "');";
          $data['type_marche']= $this->ModelPs->getRequete($getTypeMarche);

          $get_typ_docs  = 'SELECT BUDGETAIRE_TYPE_DOCUMENT_ID,DESC_BUDGETAIRE_TYPE_DOCUMENT FROM budgetaire_type_document ORDER BY DESC_BUDGETAIRE_TYPE_DOCUMENT ASC';
          $get_typ_docs = "CALL `getTable`('" . $get_typ_docs . "');";
          $data['get_typ_docs']= $this->ModelPs->getRequete($get_typ_docs);

          $dataa=$this->converdate();
          $data['debut'] = $dataa['debut'];

          return view('App\Modules\double_commande_new\Views\Corrige_Etape1_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }  
  }

  // insetion de l'engagement budgetaire
  function etape1_correction_20240712()
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
    $rules = [
      'INSTITUTION_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'SOUS_TUTEL_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'PTBA_TACHE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'MONTANT_RACCROCHE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'TYPE_ENGAGEMENT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'MARCHE_PUBLIQUE' => [
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
      'QTE_RACCROCHE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'COMMENTAIRE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');

    $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
    if ($TYPE_MONTANT_ID != 1)
    {
      $rules['MONTANT_EN_DEVISE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
      $rules['COUS_ECHANGE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    }

    $this->validation->setRules($rules);

    if($this->validation->withRequest($this->request)->run())
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user = $this->getBindParms('USER_ID', 'user_users', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
      $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);
      $USER_ID=$getuser['USER_ID'];

      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
      $ETAPE_DOUBLE=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
      $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
      $MONTANT_RACCROCHE = $this->request->getPost('engagement_budget');
      $NUMERO_BON_ENGAGEMENT=$this->request->getPost('NUMERO_BON_ENGAGEMENT');
      $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');
      $MARCHE_PUBLIQUE=$this->request->getPost('MARCHE_PUBLIQUE');
      $COMMENTAIRE=$this->request->getPost('COMMENTAIRE');
      $DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION'); 
      $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');       
      $CODE_PROGRAMME=$this->request->getPost('program_code');
      $DATE_ENG_BUDGETAIRE=$this->request->getPost('DATE_ENG_BUDGETAIRE');
      $CREDIT_VOTE=$this->request->getPost('montant_vote');
      $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
      $num_be=$this->request->getPost('num_be');
      $DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
      $UNITE=$this->request->getPost('UNITE');
      $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
      $mont_eng1=$this->request->getPost('mont_eng1');
      $EXECUTION_BUDGETAIRE_DETAIL_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
      $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
      $ACTION_ID=$this->request->getPost('ACTION_ID');
      $sous_act=$this->request->getPost('sous_act'); 
      $fini=$this->request->getPost('fini123');  
      $resultat_attend=$this->request->getPost('attend'); 
      $QTE_RACCROCHE=$this->request->getPost('qte123'); 
      $observ_resultat = $this->request->getPost('obser11');

      if ($sous_act==1)
      {
        $fini=$this->request->getPost('fini'); 
        if ($fini==1)
        {
          $observ_resultat=$this->request->getPost('observ'); 
          $resultat_attend=$this->request->getPost('resultat_attend'); 
          $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');
        }
        else
        {
          $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');
        }
      }
      else
      {
        $resultat_attend=$this->request->getPost('QTE_RACCROCHE'); 
        $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');     
      }

      $TRIMESTRE_ID=$this->request->getPost('TRIMESTRE_ID');
      $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE.'',' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
      $etape_suivante= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);
      $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

      $ETAPE_ID=$etape_suivante['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];;
      $TYPE_DOCUMENT_ID=1;

      $MONTANT_EN_DEVISE=0;
      $COUS_ECHANGE=1;
      $DATE_COUT_DEVISE=null;
      $DEVISE_TYPE_HISTO_ID = 1;

      if ($TYPE_MONTANT_ID!=1)
      {
        $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
        $MONTANT_EN_DEVISE=$this->request->getPost('engagement_devise');
        $COUS_ECHANGE=$this->request->getPost('engagement_cous');
        $DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');
        $MONTANT_RACCROCHE = floatval($COUS_ECHANGE) * floatval($MONTANT_EN_DEVISE);
        $DEVISE_TYPE_HISTO_ID = $this->request->getPost('DEVISE_TYPE_HISTO_ID');
        $MONTANT_RACCROCHE=number_format($MONTANT_RACCROCHE,0,',','');
      }

      $PATH_PPM_DOC='';
      $PATH_PV_ATTRIBUTION='';
      $PATH_AVIS_DNCMP='';
      $ID_TYPE_MARCHE=0;

      $PATH_PPM = $this->request->getFile('PATH_PPM_edit');
      $path_avis = $this->request->getFile('path_avis_edit');
      $path_pv = $this->request->getFile('path_pv_edit');

      if ($MARCHE_PUBLIQUE==1)
      {
        $ID_TYPE_MARCHE=$this->request->getPost('ID_TYPE_MARCHE');
        $maxFileSize = 20000 * 1024;

        if (!$path_avis || !$path_avis->isValid())
        {
          $PATH_AVIS_DNCMP=$this->request->getPost('path_avis_old');
        }
        else
        {
          if ($path_avis->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $PATH_AVIS_DNCMP=$this->uploadFile('path_avis_edit','double_commande_new','AVIS_DNCMP');
        }

        if (!$path_pv || !$path_pv->isValid())
        {
          $PATH_PV_ATTRIBUTION=$this->request->getPost('path_pv_old');
        }
        else
        {
          if ($path_pv->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $PATH_PV_ATTRIBUTION=$this->uploadFile('path_pv_edit','double_commande_new','PV_ATTRIBUTION');
        }

        if (!$PATH_PPM || !$PATH_PPM->isValid())
        {
          $PATH_PPM_DOC=$this->request->getPost('PATH_PPM_old');
        }
        else
        {
          if ($PATH_PPM->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $PATH_PPM_DOC=$this->uploadFile('PATH_PPM_edit','double_commande_new','PPM');
        }
      }

      $paie_doc='';
      $trans_doc='';
      $otb_doc='';
      $bon_eng='';
      $maxFileSize = 20000 * 1024;

      $PATH_LETTRE_OTB = $this->request->getFile('PATH_LETTRE_OTB_edit');
      $PATH_LETTRE_TRANSMISSION = $this->request->getFile('PATH_LETTRE_TRANSMISSION_edit');
      $PATH_LISTE_PAIE = $this->request->getFile('PATH_LISTE_PAIE_edit');
      $PATH_BON_ENGAGEMENT = $this->request->getFile('PATH_BON_ENGAGEMENT_edit');

      if (!$PATH_LETTRE_OTB || !$PATH_LETTRE_OTB->isValid())
      {
        $otb_doc=$this->request->getPost('PATH_LETTRE_OTB_old');
      }
      else
      {
        if ($PATH_LETTRE_OTB->getSize() > $maxFileSize)
        {
          $data=['message' => "".lang('messages_lang.pdf_max').""];
          session()->setFlashdata('alert', $data);
          return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
        }
        $otb_doc=$this->uploadFile('PATH_LETTRE_OTB_edit','double_commande_new','LETTRE_OTB');
      }

      if (!$PATH_BON_ENGAGEMENT || !$PATH_BON_ENGAGEMENT->isValid())
      {
        $bon_eng=$this->request->getPost('PATH_BON_ENGAGEMENT_old');
      }
      else
      {
        if ($PATH_BON_ENGAGEMENT->getSize() > $maxFileSize)
        {
          $data=['message' => "".lang('messages_lang.pdf_max').""];
          session()->setFlashdata('alert', $data);
          return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
        }
        $bon_eng=$this->uploadFile('PATH_BON_ENGAGEMENT_edit','double_commande_new','BON_ENGAGEMENT');
      }


      if ($TYPE_ENGAGEMENT_ID==1)
      {
          // if (!$PATH_LETTRE_OTB || !$PATH_LETTRE_OTB->isValid())
          // {
          //   $otb_doc=$this->request->getPost('PATH_LETTRE_OTB_old');
          // }
          // else
          // {
          //   if ($PATH_LETTRE_OTB->getSize() > $maxFileSize)
          //   {
          //     $data=['message' => "".lang('messages_lang.pdf_max').""];
          //     session()->setFlashdata('alert', $data);
          //     return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          //   }
          //   $otb_doc=$this->uploadFile('PATH_LETTRE_OTB_edit','double_commande_new','LETTRE_OTB');
          // }

        if (!$PATH_LETTRE_TRANSMISSION || !$PATH_LETTRE_TRANSMISSION->isValid())
        {
          $trans_doc=$this->request->getPost('PATH_LETTRE_TRANSMISSION_old');
        }
        else
        {
          if ($PATH_LETTRE_TRANSMISSION->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $trans_doc=$this->uploadFile('PATH_LETTRE_TRANSMISSION_edit','double_commande_new','LETTRE_TRANSMISSION');
        }

        if (!$PATH_LISTE_PAIE || !$PATH_LISTE_PAIE->isValid())
        {
          $paie_doc=$this->request->getPost('PATH_LISTE_PAIE_old');
        }
        else
        {
          if ($PATH_LISTE_PAIE->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $paie_doc=$this->uploadFile('PATH_LISTE_PAIE_edit','double_commande_new','LISTE_PAIE');
        }

      }

      $imputa = 'SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID;
      $imputa = "CALL `getTable`('" . $imputa . "');";
      $IMPUTATI= $this->ModelPs->getRequeteOne($imputa);
      $IMPUTATION = $IMPUTATI['CODE_NOMENCLATURE_BUDGETAIRE'];

      $ANNEE_BUDGETAIRE_ID = $this->get_annee_budgetaire();

      $COMMENTAIRE = str_replace("\n","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace("\r","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace("\t","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace('"','',$COMMENTAIRE);
      $COMMENTAIRE = str_replace("'","\'",$COMMENTAIRE);

      $observ_resultat = str_replace("\n","",$observ_resultat);
      $observ_resultat = str_replace("\r","",$observ_resultat);
      $observ_resultat = str_replace("\t","",$observ_resultat);
      $observ_resultat = str_replace('"','',$observ_resultat);
      $observ_resultat = str_replace("'","\'",$observ_resultat);

      $QTE_RACCROCHE = str_replace(".",",",$QTE_RACCROCHE);
      $resultat_attend = str_replace(".",",",$resultat_attend);

      $UNITE = str_replace("\n","",$UNITE);
      $UNITE = str_replace("\r","",$UNITE);
      $UNITE = str_replace("\t","",$UNITE);
      $UNITE = str_replace('"','',$UNITE);
      $UNITE = str_replace("'","\'",$UNITE);

        // $this->gestion_retour_ptba($EXECUTION_BUDGETAIRE_DETAIL_ID,$MONTANT_RACCROCHE);

      $insertIntohist='execution_budgetaire_tache_detail_histo';
      $columhist="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION,DATE_RECEPTION";
      $datacolumshist=$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$USER_ID.",'".$COMMENTAIRE."',".$ETAPE_ID.",'".$DATE_TRANSMISSION."','".$DATE_RECEPTION."'";

      $this->save_all_table($insertIntohist,$columhist,$datacolumshist);

      $whereSuppl ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;
      $insertIntoSuppl='execution_budgetaire_tache_info_suppl';
      $columSuppl="TYPE_ENGAGEMENT_ID=".$TYPE_ENGAGEMENT_ID.", PATH_PPM='".$PATH_PPM_DOC."', PATH_LETTRE_OTB='".$otb_doc."', PATH_LETTRE_TRANSMISSION='".$trans_doc."', PATH_LISTE_PAIE='".$paie_doc."',EST_SOUS_TACHE=".$sous_act.",EST_FINI_TACHE=".$fini.",RESULTAT_ATTENDUS='".$resultat_attend."',PATH_PV_ATTRIBUTION='".$PATH_PV_ATTRIBUTION."',PATH_AVIS_DNCMP='".$PATH_AVIS_DNCMP."',ID_TYPE_MARCHE  =".$ID_TYPE_MARCHE.",OBSERVATION_RESULTAT = '".$observ_resultat."'";  
      $this->update_all_table($insertIntoSuppl,$columSuppl,$whereSuppl);

      $info_activite="";
      if(!empty($PAP_ACTIVITE_ID))
      {
        $info_activite=",PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID;
      }

      $whereracc ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;
      $insertIntoracc='execution_budgetaire';
      $columracc="INSTITUTION_ID=".$INSTITUTION_ID.",ENG_BUDGETAIRE=".$MONTANT_RACCROCHE.",ENG_BUDGETAIRE_DEVISE=".$MONTANT_EN_DEVISE.",DEVISE_TYPE_HISTO_ENG_ID=".$DEVISE_TYPE_HISTO_ID.",SOUS_TUTEL_ID=".$SOUS_TUTEL_ID.",PROGRAMME_ID=".$PROGRAMME_ID.",ACTION_ID=".$ACTION_ID.",CODE_NOMENCLATURE_BUDGETAIRE_ID=".$CODE_NOMENCLATURE_BUDGETAIRE_ID."".$info_activite.",PTBA_TACHE_ID=".$PTBA_TACHE_ID.",QTE_RACCROCHE=".$QTE_RACCROCHE.",UNITE='".$UNITE."',MARCHE_PUBLIQUE=".$MARCHE_PUBLIQUE.",COMMENTAIRE='".$COMMENTAIRE."',DEVISE_TYPE_ID=".$TYPE_MONTANT_ID.",NUMERO_BON_ENGAGEMENT='".$NUMERO_BON_ENGAGEMENT."',PATH_BON_ENGAGEMENT='".$bon_eng."'";
      $this->update_all_table($insertIntoracc,$columracc,$whereracc);


      $whereDet='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
      $insertDetail='execution_budgetaire_tache_detail';
      $columDetail='';
      if ($DATE_COUT_DEVISE === null)
      {
        $columDetail='ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_DOUBLE_COMMANDE_ID.',COUR_DEVISE="'.$COUS_ECHANGE.'",COMMENTAIRE="'.$COMMENTAIRE.'",DATE_COUR_DEVISE=null';
      }
      else
      {
        $columDetail='ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_DOUBLE_COMMANDE_ID.',COUR_DEVISE="'.$COUS_ECHANGE.'",COMMENTAIRE="'.$COMMENTAIRE.'",DATE_COUR_DEVISE="'.$DATE_COUT_DEVISE.'"';
      }

      $this->update_all_table($insertDetail,$columDetail,$whereDet);

      $data=['message' => "".lang('messages_lang.eng_corrig').""];
      session()->setFlashdata('alert', $data); 

      return redirect('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Corr');
    }
    else
    {
      return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_DETAIL_ID));
    }
  }

  function etape1_correction()
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
    $rules = [
      'INSTITUTION_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'SOUS_TUTEL_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'PTBA_TACHE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'MONTANT_RACCROCHE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'TYPE_ENGAGEMENT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'MARCHE_PUBLIQUE' => [
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
      'QTE_RACCROCHE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'BUDGETAIRE_TYPE_DOCUMENT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'COMMENTAIRE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');

    $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
    if ($TYPE_MONTANT_ID != 1)
    {
      $rules['MONTANT_EN_DEVISE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
      $rules['COUS_ECHANGE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    }

    $this->validation->setRules($rules);

    if($this->validation->withRequest($this->request)->run())
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user = $this->getBindParms('USER_ID', 'user_users', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
      $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);
      $USER_ID=$getuser['USER_ID'];

      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
      $ETAPE_DOUBLE=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
      $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
      $MONTANT_RACCROCHE = $this->request->getPost('engagement_budget');
      $NUMERO_BON_ENGAGEMENT=$this->request->getPost('NUMERO_BON_ENGAGEMENT');
      $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');
      $MARCHE_PUBLIQUE=$this->request->getPost('MARCHE_PUBLIQUE');
      $COMMENTAIRE=$this->request->getPost('COMMENTAIRE');
      $DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION'); 
      $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');       
      $CODE_PROGRAMME=$this->request->getPost('program_code');
      $DATE_ENG_BUDGETAIRE=$this->request->getPost('DATE_ENG_BUDGETAIRE');
      $CREDIT_VOTE=$this->request->getPost('montant_vote');
      $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
      $num_be=$this->request->getPost('num_be');
      $DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
      $UNITE=$this->request->getPost('UNITE');
      $PAP_ACTIVITE_ID=$this->request->getPost('PAP_ACTIVITE_ID');
      $mont_eng1=$this->request->getPost('mont_eng1');
      $EXECUTION_BUDGETAIRE_DETAIL_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
      $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
      $ACTION_ID=$this->request->getPost('ACTION_ID');
      $sous_act=$this->request->getPost('sous_act'); 
      $fini=$this->request->getPost('fini123');  
      $resultat_attend=$this->request->getPost('attend'); 
      $QTE_RACCROCHE=$this->request->getPost('qte123'); 
      $observ_resultat = $this->request->getPost('obser11');
      $BUDGETAIRE_TYPE_DOCUMENT_ID=$this->request->getPost('BUDGETAIRE_TYPE_DOCUMENT_ID');

      if ($sous_act==1)
      {
        $fini=$this->request->getPost('fini'); 
        if ($fini==1)
        {
          $observ_resultat=$this->request->getPost('observ'); 
          $resultat_attend=$this->request->getPost('resultat_attend'); 
          $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');
        }
        else
        {
          $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');
        }
      }
      else
      {
        $resultat_attend=$this->request->getPost('QTE_RACCROCHE'); 
        $QTE_RACCROCHE=$this->request->getPost('QTE_RACCROCHE');     
      }

      $TRIMESTRE_ID=$this->request->getPost('TRIMESTRE_ID');
      $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE.'',' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
      $etape_suivante= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);
      $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

      $ETAPE_ID=$etape_suivante['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];;
      $TYPE_DOCUMENT_ID=1;

      $MONTANT_EN_DEVISE=0;
      $COUS_ECHANGE=1;
      $DATE_COUT_DEVISE=null;
      $DEVISE_TYPE_HISTO_ID = 1;

      if ($TYPE_MONTANT_ID!=1)
      {
        $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
        $MONTANT_EN_DEVISE=$this->request->getPost('engagement_devise');
        $COUS_ECHANGE=$this->request->getPost('engagement_cous');
        $DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');
        $MONTANT_RACCROCHE = floatval($COUS_ECHANGE) * floatval($MONTANT_EN_DEVISE);
        $MONTANT_RACCROCHE=number_format($MONTANT_RACCROCHE,0,',','');
        // $DEVISE_TYPE_HISTO_ID = $this->request->getPost('DEVISE_TYPE_HISTO_ID');

        /* si le taux existe sur la meme date et meme devise*/
        $taux_exist = "SELECT DEVISE_TYPE_HISTO_ID,DEVISE_TYPE_ID,TAUX,DATE_INSERTION FROM devise_type_hist WHERE DEVISE_TYPE_ID=".$TYPE_MONTANT_ID." AND TAUX=".$COUS_ECHANGE." AND DATE_INSERTION LIKE '$DATE_COUT_DEVISE%'";
        $taux_exist='CALL `getTable`("'.$taux_exist.'")';
        $taux_exist= $this->ModelPs->getRequeteOne($taux_exist);
        if(!empty($taux_exist))
        {
          $DEVISE_TYPE_HISTO_ID=$taux_exist["DEVISE_TYPE_HISTO_ID"];
        }
        else
        {
          $columns="DEVISE_TYPE_ID,TAUX,IS_ACTIVE,DATE_INSERTION";
          $data_col=$TYPE_MONTANT_ID.",".$COUS_ECHANGE.",0,'".$DATE_COUT_DEVISE."'";
          $table_dev="devise_type_hist";
          $DEVISE_TYPE_HISTO_ID =$this->save_all_table($table_dev,$columns,$data_col);
        }
        /* si le taux existe sur la meme date et meme devise*/
      }

      $PATH_PPM_DOC='';
      $PATH_PV_ATTRIBUTION='';
      $PATH_AVIS_DNCMP='';
      $ID_TYPE_MARCHE=0;

      $PATH_PPM = $this->request->getFile('PATH_PPM_edit');
      $path_avis = $this->request->getFile('path_avis_edit');
      $path_pv = $this->request->getFile('path_pv_edit');

      if ($MARCHE_PUBLIQUE==1)
      {
        $ID_TYPE_MARCHE=$this->request->getPost('ID_TYPE_MARCHE');
        $maxFileSize = 20000 * 1024;

        if (!$path_avis || !$path_avis->isValid())
        {
          $PATH_AVIS_DNCMP=$this->request->getPost('path_avis_old');
        }
        else
        {
          if ($path_avis->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $PATH_AVIS_DNCMP=$this->uploadFile('path_avis_edit','double_commande_new','AVIS_DNCMP');
        }

        if (!$path_pv || !$path_pv->isValid())
        {
          $PATH_PV_ATTRIBUTION=$this->request->getPost('path_pv_old');
        }
        else
        {
          if ($path_pv->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $PATH_PV_ATTRIBUTION=$this->uploadFile('path_pv_edit','double_commande_new','PV_ATTRIBUTION');
        }

        if (!$PATH_PPM || !$PATH_PPM->isValid())
        {
          $PATH_PPM_DOC=$this->request->getPost('PATH_PPM_old');
        }
        else
        {
          if ($PATH_PPM->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $PATH_PPM_DOC=$this->uploadFile('PATH_PPM_edit','double_commande_new','PPM');
        }
      }

      $paie_doc='';
      $trans_doc='';
      $otb_doc='';
      $bon_eng='';
      $maxFileSize = 20000 * 1024;

      $PATH_LETTRE_OTB = $this->request->getFile('PATH_LETTRE_OTB_edit');
      $PATH_LETTRE_TRANSMISSION = $this->request->getFile('PATH_LETTRE_TRANSMISSION_edit');
      $PATH_LISTE_PAIE = $this->request->getFile('PATH_LISTE_PAIE_edit');
      $PATH_BON_ENGAGEMENT = $this->request->getFile('PATH_BON_ENGAGEMENT_edit');

      if (!$PATH_LETTRE_OTB || !$PATH_LETTRE_OTB->isValid())
      {
        $otb_doc=$this->request->getPost('PATH_LETTRE_OTB_old');
      }
      else
      {
        if ($PATH_LETTRE_OTB->getSize() > $maxFileSize)
        {
          $data=['message' => "".lang('messages_lang.pdf_max').""];
          session()->setFlashdata('alert', $data);
          return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
        }
        $otb_doc=$this->uploadFile('PATH_LETTRE_OTB_edit','double_commande_new','LETTRE_OTB');
      }

        // if (!$PATH_BON_ENGAGEMENT || !$PATH_BON_ENGAGEMENT->isValid())
        // {
        //   $bon_eng=$this->request->getPost('PATH_BON_ENGAGEMENT_old');
        // }
        // else
        // {
        //   if ($PATH_BON_ENGAGEMENT->getSize() > $maxFileSize)
        //   {
        //     $data=['message' => "".lang('messages_lang.pdf_max').""];
        //     session()->setFlashdata('alert', $data);
        //     return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
        //   }
        //   $bon_eng=$this->uploadFile('PATH_BON_ENGAGEMENT_edit','double_commande_new','BON_ENGAGEMENT');
        // }


      if ($TYPE_ENGAGEMENT_ID==1)
      {
          // if (!$PATH_LETTRE_OTB || !$PATH_LETTRE_OTB->isValid())
          // {
          //   $otb_doc=$this->request->getPost('PATH_LETTRE_OTB_old');
          // }
          // else
          // {
          //   if ($PATH_LETTRE_OTB->getSize() > $maxFileSize)
          //   {
          //     $data=['message' => "".lang('messages_lang.pdf_max').""];
          //     session()->setFlashdata('alert', $data);
          //     return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          //   }
          //   $otb_doc=$this->uploadFile('PATH_LETTRE_OTB_edit','double_commande_new','LETTRE_OTB');
          // }

        if (!$PATH_LETTRE_TRANSMISSION || !$PATH_LETTRE_TRANSMISSION->isValid())
        {
          $trans_doc=$this->request->getPost('PATH_LETTRE_TRANSMISSION_old');
        }
        else
        {
          if ($PATH_LETTRE_TRANSMISSION->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $trans_doc=$this->uploadFile('PATH_LETTRE_TRANSMISSION_edit','double_commande_new','LETTRE_TRANSMISSION');
        }

        if (!$PATH_LISTE_PAIE || !$PATH_LISTE_PAIE->isValid())
        {
          $paie_doc=$this->request->getPost('PATH_LISTE_PAIE_old');
        }
        else
        {
          if ($PATH_LISTE_PAIE->getSize() > $maxFileSize)
          {
            $data=['message' => "".lang('messages_lang.pdf_max').""];
            session()->setFlashdata('alert', $data);
            return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID));
          }
          $paie_doc=$this->uploadFile('PATH_LISTE_PAIE_edit','double_commande_new','LISTE_PAIE');
        }

      }

      $imputa = 'SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID;
      $imputa = "CALL `getTable`('" . $imputa . "');";
      $IMPUTATI= $this->ModelPs->getRequeteOne($imputa);
      $IMPUTATION = $IMPUTATI['CODE_NOMENCLATURE_BUDGETAIRE'];

      $ANNEE_BUDGETAIRE_ID = $this->get_annee_budgetaire();

      $COMMENTAIRE = str_replace("\n","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace("\r","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace("\t","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace('"','',$COMMENTAIRE);
      $COMMENTAIRE = str_replace("'","\'",$COMMENTAIRE);

      $observ_resultat = str_replace("\n","",$observ_resultat);
      $observ_resultat = str_replace("\r","",$observ_resultat);
      $observ_resultat = str_replace("\t","",$observ_resultat);
      $observ_resultat = str_replace('"','',$observ_resultat);
      $observ_resultat = str_replace("'","\'",$observ_resultat);

      $QTE_RACCROCHE = str_replace(".",",",$QTE_RACCROCHE);
      $resultat_attend = str_replace(".",",",$resultat_attend);

      $UNITE = str_replace("\n","",$UNITE);
      $UNITE = str_replace("\r","",$UNITE);
      $UNITE = str_replace("\t","",$UNITE);
      $UNITE = str_replace('"','',$UNITE);
      $UNITE = str_replace("'","\'",$UNITE);

        // $this->gestion_retour_ptba($EXECUTION_BUDGETAIRE_DETAIL_ID,$MONTANT_RACCROCHE);

      $insertIntohist='execution_budgetaire_tache_detail_histo';
      $columhist="EXECUTION_BUDGETAIRE_DETAIL_ID,USER_ID,OBSERVATION,ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION,DATE_RECEPTION";
      $datacolumshist=$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$USER_ID.",'".$COMMENTAIRE."',".$ETAPE_ID.",'".$DATE_TRANSMISSION."','".$DATE_RECEPTION."'";

      $this->save_all_table($insertIntohist,$columhist,$datacolumshist);

      $whereSuppl ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;
      $insertIntoSuppl='execution_budgetaire_tache_info_suppl';
      $columSuppl="TYPE_ENGAGEMENT_ID=".$TYPE_ENGAGEMENT_ID.", PATH_PPM='".$PATH_PPM_DOC."',BUDGETAIRE_TYPE_DOCUMENT_ID=".$BUDGETAIRE_TYPE_DOCUMENT_ID.",PATH_LETTRE_OTB='".$otb_doc."', PATH_LETTRE_TRANSMISSION='".$trans_doc."', PATH_LISTE_PAIE='".$paie_doc."',EST_SOUS_TACHE=".$sous_act.",EST_FINI_TACHE=".$fini.",RESULTAT_ATTENDUS='".$resultat_attend."',PATH_PV_ATTRIBUTION='".$PATH_PV_ATTRIBUTION."',PATH_AVIS_DNCMP='".$PATH_AVIS_DNCMP."',ID_TYPE_MARCHE  =".$ID_TYPE_MARCHE.",OBSERVATION_RESULTAT = '".$observ_resultat."'";  
      $this->update_all_table($insertIntoSuppl,$columSuppl,$whereSuppl);

      $info_activite="";
      if(!empty($PAP_ACTIVITE_ID))
      {
        $info_activite=",PAP_ACTIVITE_ID=".$PAP_ACTIVITE_ID;
      }

      $whereracc ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;
      $insertIntoracc='execution_budgetaire';
      $columracc="INSTITUTION_ID=".$INSTITUTION_ID.",ENG_BUDGETAIRE=".$MONTANT_RACCROCHE.",ENG_BUDGETAIRE_DEVISE=".$MONTANT_EN_DEVISE.",DEVISE_TYPE_HISTO_ENG_ID=".$DEVISE_TYPE_HISTO_ID.",SOUS_TUTEL_ID=".$SOUS_TUTEL_ID.",PROGRAMME_ID=".$PROGRAMME_ID.",ACTION_ID=".$ACTION_ID.",CODE_NOMENCLATURE_BUDGETAIRE_ID=".$CODE_NOMENCLATURE_BUDGETAIRE_ID."".$info_activite.",PTBA_TACHE_ID=".$PTBA_TACHE_ID.",QTE_RACCROCHE='".$QTE_RACCROCHE."',UNITE='".$UNITE."',MARCHE_PUBLIQUE=".$MARCHE_PUBLIQUE.",COMMENTAIRE='".$COMMENTAIRE."',DEVISE_TYPE_ID=".$TYPE_MONTANT_ID.",NUMERO_BON_ENGAGEMENT='".$NUMERO_BON_ENGAGEMENT."'";
        // $columracc="INSTITUTION_ID=".$INSTITUTION_ID.",ENG_BUDGETAIRE=".$MONTANT_RACCROCHE.",ENG_BUDGETAIRE_DEVISE=".$MONTANT_EN_DEVISE.",DEVISE_TYPE_HISTO_ENG_ID=".$DEVISE_TYPE_HISTO_ID.",SOUS_TUTEL_ID=".$SOUS_TUTEL_ID.",PROGRAMME_ID=".$PROGRAMME_ID.",ACTION_ID=".$ACTION_ID.",CODE_NOMENCLATURE_BUDGETAIRE_ID=".$CODE_NOMENCLATURE_BUDGETAIRE_ID."".$info_activite.",PTBA_TACHE_ID=".$PTBA_TACHE_ID.",QTE_RACCROCHE=".$QTE_RACCROCHE.",UNITE='".$UNITE."',MARCHE_PUBLIQUE=".$MARCHE_PUBLIQUE.",COMMENTAIRE='".$COMMENTAIRE."',DEVISE_TYPE_ID=".$TYPE_MONTANT_ID.",NUMERO_BON_ENGAGEMENT='".$NUMERO_BON_ENGAGEMENT."',PATH_BON_ENGAGEMENT='".$bon_eng."'";
      $this->update_all_table($insertIntoracc,$columracc,$whereracc);


      $whereDet='EXECUTION_BUDGETAIRE_DETAIL_ID='.$EXECUTION_BUDGETAIRE_DETAIL_ID;
      $insertDetail='execution_budgetaire_tache_detail';
      $columDetail='';
      if ($DATE_COUT_DEVISE === null)
      {
        $columDetail='ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_DOUBLE_COMMANDE_ID.',COUR_DEVISE="'.$COUS_ECHANGE.'",COMMENTAIRE="'.$COMMENTAIRE.'",DATE_COUR_DEVISE=null';
      }
      else
      {
        $columDetail='ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_DOUBLE_COMMANDE_ID.',COUR_DEVISE="'.$COUS_ECHANGE.'",COMMENTAIRE="'.$COMMENTAIRE.'",DATE_COUR_DEVISE="'.$DATE_COUT_DEVISE.'"';
      }

      $this->update_all_table($insertDetail,$columDetail,$whereDet);
      $this->gestion_retour_ptba($EXECUTION_BUDGETAIRE_DETAIL_ID,$MONTANT_RACCROCHE);

      $data=['message' => "".lang('messages_lang.eng_corrig').""];
      session()->setFlashdata('alert', $data); 

      return redirect('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Corr');
    }
    else
    {
      return $this->corrige_etape1(md5($EXECUTION_BUDGETAIRE_DETAIL_ID));
    }
  }

  // affiche le view pour la 2em etape d'engagement budgetaire (engage)
  function etape2($id=0)
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $infoAffiche  = 'SELECT det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.ETAPE_DOUBLE_COMMANDE_ID,exec.EXECUTION_BUDGETAIRE_ID,exec.MARCHE_PUBLIQUE FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID) = "'.$id.'"';

    $infoAffiche = "CALL `getTable`('" . $infoAffiche . "');";
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
          $data['etape2'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];

          $date_eng  = 'SELECT supp.PATH_PPM,supp.PATH_LETTRE_OTB,supp.PATH_LETTRE_TRANSMISSION,supp.PATH_LISTE_PAIE,supp.TYPE_ENGAGEMENT_ID,eng.DESC_TYPE_ENGAGEMENT FROM execution_budgetaire_tache_info_suppl supp JOIN type_engagement eng ON eng.TYPE_ENGAGEMENT_ID=supp.TYPE_ENGAGEMENT_ID WHERE supp.EXECUTION_BUDGETAIRE_ID = "'.$data['info']['EXECUTION_BUDGETAIRE_ID'].'"';
          $date_eng = "CALL `getTable`('" . $date_eng . "');";
          $data['get_date_eng']= $this->ModelPs->getRequeteOne($date_eng);

          $callpsreq = "CALL `getRequete`(?,?,?,?);";     
          $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_DETAIL_ID='.$data['info']['EXECUTION_BUDGETAIRE_DETAIL_ID'],'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\','',$bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $data['EXECUTION_BUDGETAIRE_ID'] = $data['info']['EXECUTION_BUDGETAIRE_ID'];

          $operation  = 'SELECT ID_OPERATION,DESCRIPTION FROM budgetaire_type_operation_validation ORDER BY DESCRIPTION ASC';
          $operation = "CALL `getTable`('" . $operation . "');";
          $data['get_operation'] = $this->ModelPs->getRequete($operation);

          $Verifier='';

          if ($data['get_date_eng']['TYPE_ENGAGEMENT_ID'] == 1)
          {
            $Verifier = 'SELECT BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,MOUVEMENT_DEPENSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID=1 AND IS_MARCHE =0 ORDER BY DESC_BUDGETAIRE_TYPE_ANALYSE ASC';
          }
          else if ($data['info']['MARCHE_PUBLIQUE'] == 1)
          {    
            $Verifier = 'SELECT BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,MOUVEMENT_DEPENSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID=1 AND IS_MARCHE =1 OR BUDGETAIRE_TYPE_ANALYSE_ID IN (1,2)  ORDER BY DESC_BUDGETAIRE_TYPE_ANALYSE ASC';
          }
          else
          {
            $Verifier = 'SELECT BUDGETAIRE_TYPE_ANALYSE_ID AS TYPE_ANALYSE_ID,MOUVEMENT_DEPENSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE AS DESC_TYPE_ANALYSE FROM budgetaire_type_analyse WHERE MOUVEMENT_DEPENSE_ID=1 AND BUDGETAIRE_TYPE_ANALYSE_ID IN (1,2) ORDER BY DESC_BUDGETAIRE_TYPE_ANALYSE ASC';
          }

          $Verifier = "CALL `getTable`('" . $Verifier . "');";
          $data['get_verifie']= $this->ModelPs->getRequete($Verifier);
          $data['count_verifier']= count($data['get_verifie']);

          $rejet_motif='';
          if ($data['info']['MARCHE_PUBLIQUE'] == 1)
          {
            $rejet_motif='SELECT TYPE_ANALYSE_MOTIF_ID, DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif WHERE MOUVEMENT_DEPENSE_ID=1 AND IS_MARCHE = 1';
          }
          else
          {
            $rejet_motif='SELECT TYPE_ANALYSE_MOTIF_ID, DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif WHERE MOUVEMENT_DEPENSE_ID=1 AND IS_MARCHE = 0';
          }

          $rejet_motif = "CALL `getTable`('" .$rejet_motif. "');";
          $data['get_motif']= $this->ModelPs->getRequete($rejet_motif);

          $detail=$this->detail_new(md5($data['info']['EXECUTION_BUDGETAIRE_DETAIL_ID']));

          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['creditVote']=$detail['creditVote'];
          $data['montant_reserve']=$detail['montant_reserve'];
          return view('App\Modules\double_commande_new\Views\Eng_Budget_Etapes2_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    } 
  }

  // insertion et update pour la 2em etape
  function save_etape2()
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

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID'); 
    $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID'); 

    $rules = [
      'ID_OPERATION' => [
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
      'DATE_RECEPTION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];
    $ID_OPERATION = $this->request->getPost('ID_OPERATION');
    if ($ID_OPERATION == 1)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    }
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

      $DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');
      $ETAPE_DOUBLE_COMMANDE=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $ID_OPERATION = $this->request->getPost('ID_OPERATION');
      $TYPE_ANALYSE_ID = $this->request->getPost('grande[]');
      $verifier = $this->request->getPost('verifier');

      $nbre_analyse = (!empty($TYPE_ANALYSE_ID)) ? count($TYPE_ANALYSE_ID) : 0 ;

      $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
      $COMMENTAIRE = str_replace("\n","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace("\r","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace("\t","",$COMMENTAIRE);
      $COMMENTAIRE = str_replace('"','',$COMMENTAIRE);
      $COMMENTAIRE = str_replace("'","\'",$COMMENTAIRE);
      $motif='';

      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,IS_CORRECTION,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE_COMMANDE,' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
      $etape_suivante22= $this->ModelPs->getRequete($callpsreq, $etape_suivante);

      foreach ($etape_suivante22 as $key) {
        if ($key->IS_CORRECTION == 0) {
          $ETAPE_DOUBLE_COMMANDE_ID=$key->ETAPE_DOUBLE_COMMANDE_SUIVANT_ID;
        } 
      }
      if ($ID_OPERATION==1)
      {
        $motif = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');

        foreach ($etape_suivante22 as $key) {
          if ($key->IS_CORRECTION == 1) {
            $ETAPE_DOUBLE_COMMANDE_ID=$key->ETAPE_DOUBLE_COMMANDE_SUIVANT_ID;
          }
        }
        foreach($motif as $mot)
        {
          $insertIntomotif='execution_budgetaire_histo_operation_verification_motif';
          $colummotif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";

          $datacolumsmotif=$mot.",".$ETAPE_DOUBLE_COMMANDE.",".$EXECUTION_BUDGETAIRE_DETAIL_ID."";
          $this->save_all_table($insertIntomotif,$colummotif,$datacolumsmotif);
        }

        $this->gestion_rejet_ptba($EXECUTION_BUDGETAIRE_ID);
      }

      if ($ID_OPERATION==3)
      {
        foreach ($etape_suivante22 as $key)
        {
          if($key->IS_CORRECTION == 2)
          {
            $ETAPE_DOUBLE_COMMANDE_ID=$key->ETAPE_DOUBLE_COMMANDE_SUIVANT_ID;
          }
        }

        $this->gestion_rejet_ptba($EXECUTION_BUDGETAIRE_ID);
      }
      if ($nbre_analyse != $verifier )
      {
        $data=['message' => "".lang('messages_lang.check_ver').""];
        session()->setFlashdata('alert', $data);
        return $this->etape2(md5($EXECUTION_BUDGETAIRE_DETAIL_ID));
      }
      else
      {

        foreach($TYPE_ANALYSE_ID as $analyse)
        {
          $insertIntoAnalyse='execution_budgetaire_histo_operation_verification';
          $columAnalyse="TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";

          $datacolumsAnalyse=$analyse.",".$ETAPE_DOUBLE_COMMANDE.",".$EXECUTION_BUDGETAIRE_ID."";

          $this->save_all_table($insertIntoAnalyse,$columAnalyse,$datacolumsAnalyse);
        }

      }

      $whereDet ="EXECUTION_BUDGETAIRE_DETAIL_ID = ".$EXECUTION_BUDGETAIRE_DETAIL_ID;

      $insertIntoDet='execution_budgetaire_tache_detail';
      $columDet="ETAPE_DOUBLE_COMMANDE_ID = ".$ETAPE_DOUBLE_COMMANDE_ID;
      $this->update_all_table($insertIntoDet,$columDet,$whereDet);

      $insertIntoOp='execution_budgetaire_tache_detail_histo';
      $columOp="EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,OBSERVATION,DATE_RECEPTION,DATE_TRANSMISSION,MOTIF_REJET";
      $datacolumsOp=$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$ETAPE_DOUBLE_COMMANDE.",".$USER_ID.",'".$COMMENTAIRE."','".$DATE_RECEPTION."','".$DATE_TRANSMISSION."','".$COMMENTAIRE."'";
      $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

      $data=['message' => "".lang('messages_lang.conf_succ').""];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Valide');
    }
    else
    {
      return $this->etape2(md5($EXECUTION_BUDGETAIRE_DETAIL_ID));
    }
  }

  // trouver le sous titre a partir de institution choisit
  function get_sousTutel($INSTITUTION_ID=0)
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

    $getSousTutel  = 'SELECT SOUS_TUTEL_ID,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE INSTITUTION_ID = '.$INSTITUTION_ID.' ORDER BY DESCRIPTION_SOUS_TUTEL  ASC';
    $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
    $sousTutel = $this->ModelPs->getRequete($getSousTutel);

    $html='<option value="">Sélectionner</option>';
    foreach ($sousTutel as $key)
    {
      $html.='<option value="'.$key->SOUS_TUTEL_ID.'">'.$key->CODE_SOUS_TUTEL.'-'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
    }

    $output = array(

      "SousTutel" => $html,
    );

    return $this->response->setJSON($output);
  }

  // trouver le sous titre a partir de institution choisit
  function get_inst($INSTITUTION_ID=0)
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

    $getInst  = 'SELECT INSTITUTION_ID,DESCRIPTION_INSTITUTION, TYPE_INSTITUTION_ID FROM inst_institutions WHERE INSTITUTION_ID = '.$INSTITUTION_ID.'';
    $getInst = "CALL `getTable`('" . $getInst . "');";
    $institution = $this->ModelPs->getRequeteOne($getInst);

    $output = array(

      "inst_activite" => $institution['TYPE_INSTITUTION_ID'],
    );

    return $this->response->setJSON($output);
  }

  // trouver le code  a partir de sous titre choisit
  function get_code($SOUS_TUTEL_ID=0)
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

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $getcodeBudget = "SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE INSTITUTION_ID = ".$INSTITUTION_ID." AND SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    $getcodeBudget = 'CALL `getTable`("'.$getcodeBudget.'");';
    $code_Buget= $this->ModelPs->getRequete($getcodeBudget);

    $html='<option value="">Sélectionner</option>';
    foreach ($code_Buget as $key)
    {
      $html.='<option value="'.$key->CODE_NOMENCLATURE_BUDGETAIRE_ID.'">'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'-'.$key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'</option>';
    }

    $output = array(

      "codeBudgetaire" => $html,
    );

    return $this->response->setJSON($output);
  }

  // trouver les activites a partir du line budgetaire
  function get_activite($CODE_NOMENCLATURE_BUDGETAIRE_ID=0)
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

    $getActivite = 'SELECT PAP_ACTIVITE_ID, DESC_PAP_ACTIVITE FROM pap_activites WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID.' ORDER BY PAP_ACTIVITE_ID  ASC';
    $getActivite = "CALL `getTable`('" . $getActivite . "');";
    $activites = $this->ModelPs->getRequete($getActivite);

    $html='<option value="">Sélectionner</option>';
    foreach ($activites as $key)
    {
      $html.='<option value="'.$key->PAP_ACTIVITE_ID.'">'.$key->DESC_PAP_ACTIVITE.'</option>';
    }

    $output = array(

      "activite" => $html,
    );

    return $this->response->setJSON($output);
  }

  // trouver le activite  a partir de code choisit
  function get_taches($id = 0,$TYPE_INSTITUTION_ID=0)
  {
    $db = db_connect();
    $session = \Config\Services::session();
    $user_id = '';

    if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    } else {
      return redirect('Login_Ptba/do_logout');
    }


    if ($TYPE_INSTITUTION_ID == 2)
    {
      $getTacheactivite = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PAP_ACTIVITE_ID = {$id} ORDER BY PTBA_TACHE_ID ASC";
    } 
    else if ($TYPE_INSTITUTION_ID == 1)
    {
      $getTacheactivite = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID = {$id} ORDER BY PTBA_TACHE_ID ASC";
    }

    $getTacheactivite = "CALL `getList`('{$getTacheactivite}')";
    $tache_activites = $this->ModelPs->getRequete($getTacheactivite);
    $html = '<option value="">Sélectionner</option>';
    foreach ($tache_activites as $key)
    {
      $html .= '<option value="' . $key->PTBA_TACHE_ID . '">' . $key->DESC_TACHE . '</option>';
    }

    $output = array(
      "tache_activite" => $html,
    );
    return $this->response->setJSON($output);
  }

  // trouver  tous montants   a partir de la tache choisit 
  function get_TacheMoney($PTBA_TACHE_ID=0)
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

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $getMoneyActivite = 'SELECT BUDGET_ANNUEL,DESC_TACHE,PTBA_TACHE_ID,BUDGET_T1 AS T1,BUDGET_T2 AS T2,BUDGET_T3 AS T3,BUDGET_T4 AS T4,BUDGET_RESTANT_T1,BUDGET_UTILISE_T1,BUDGET_RESTANT_T2,BUDGET_UTILISE_T2,BUDGET_RESTANT_T3,BUDGET_UTILISE_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T4,UNITE,QT1,QT2,QT3,QT4,progr.CODE_PROGRAMME,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,actions.LIBELLE_ACTION,progr.PROGRAMME_ID,actions.ACTION_ID FROM ptba_tache JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba_tache.ACTION_ID WHERE PTBA_TACHE_ID='.$PTBA_TACHE_ID.' ORDER BY PTBA_TACHE_ID  ASC';
    $getMoneyActivite = "CALL `getTable`('" .$getMoneyActivite. "');";
    $MoneyActivite= $this->ModelPs->getRequeteOne($getMoneyActivite);

    $dataa=$this->converdate();
    $data['debut']=$dataa['debut'];
    $data['fin']=$dataa['fin'];
    $CODE_TRANCHE=$dataa['CODE_TRIMESTRE'];
    $TRANCHE_ID=$dataa['TRIMESTRE_ID'];
    $ANNEE_BUDGETAIRE_ID = $this->get_annee_budgetaire();

    $resteEng=0;
    $MontantRestant = 0;
    $MontantVote = 0;
    $TRIMESTRE_ID=0;
    $qte=0;

    if ($CODE_TRANCHE == 'T1') {
      $MontantVote = floatval($MoneyActivite['T1']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T1']);
      $TRIMESTRE_ID=1;
      $qte=$MoneyActivite['QT1'];
    } else if ($CODE_TRANCHE == 'T2') {
      $MontantVote = floatval($MoneyActivite['T2']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T2']);
      $TRIMESTRE_ID=2;
      $qte=$MoneyActivite['QT2'];
    }else if ($CODE_TRANCHE == 'T3') {
      $MontantVote = floatval($MoneyActivite['T3']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T3']);
      $TRIMESTRE_ID=3;
      $qte=$MoneyActivite['QT3'];
    }else if ($CODE_TRANCHE == 'T4') {
      $MontantVote = floatval($MoneyActivite['T4']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T4']);
      $TRIMESTRE_ID=4;
      $qte=$MoneyActivite['QT4'];    
    }

    $montantRec = $MontantVote;
    $montantRec_vote = number_format($montantRec,'4',',',' ');
    $montantReste=$MontantRestant;
    $montantReste_ptba = number_format($MontantRestant,'4',',',' ');
    $TRIMESTRE_ID=$TRIMESTRE_ID;

    $reste_Engage= number_format($resteEng,4,',',' ');
    $quantite = $qte;
    $unite=$MoneyActivite['UNITE'];

    $BUDGET_ANNUEL_FORMAT=number_format($MoneyActivite['BUDGET_ANNUEL'],'4',',',' ');

    $output = array(
      "montant_vote" => $MontantVote,
      "vote" =>$montantRec_vote,
      "montant_restant" =>$montantReste,
      "restant" =>$montantReste_ptba,
      "program_code" => $MoneyActivite['CODE_PROGRAMME'],
      "programs" => $MoneyActivite['INTITULE_PROGRAMME'],
      "action_code" => $MoneyActivite['CODE_ACTION'], 
      "PROGRAMME_ID"=>$MoneyActivite['PROGRAMME_ID'],
      "ACTION_ID"=>$MoneyActivite['ACTION_ID'],      
      "action" => $MoneyActivite['LIBELLE_ACTION'],
      "UNITE"=>$unite,
      "TRIMESTRE_ID"=>$TRIMESTRE_ID,
      "resteEng"=>$resteEng,
      "reste_Engage"=>$reste_Engage, 
      "qte_vote" =>$quantite,
      "BUDGET_ANNUEL"=>$MoneyActivite['BUDGET_ANNUEL'],
      "BUDGET_ANNUEL_FORMAT"=>$BUDGET_ANNUEL_FORMAT
    );

    return $this->response->setJSON($output);
  }

  // trouver  tous montants a partir de la tache choisit cas de modification
  function get_TacheMoneyCorrection($PTBA_TACHE_ID=0,$TRIMESTRE_ID=0)
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

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $getMoneyActivite = 'SELECT BUDGET_ANNUEL,DESC_TACHE,PTBA_TACHE_ID,BUDGET_T1 AS T1,BUDGET_T2 AS T2,BUDGET_T3 AS T3,BUDGET_T4 AS T4,BUDGET_RESTANT_T1,BUDGET_UTILISE_T1,BUDGET_RESTANT_T2,BUDGET_UTILISE_T2,BUDGET_RESTANT_T3,BUDGET_UTILISE_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T4,UNITE,QT1,QT2,QT3,QT4,progr.CODE_PROGRAMME,progr.INTITULE_PROGRAMME,actions.CODE_ACTION,actions.LIBELLE_ACTION,progr.PROGRAMME_ID,actions.ACTION_ID FROM ptba_tache JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=ptba_tache.PROGRAMME_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba_tache.ACTION_ID WHERE PTBA_TACHE_ID='.$PTBA_TACHE_ID.' ORDER BY PTBA_TACHE_ID  ASC';
    $getMoneyActivite = "CALL `getTable`('" .$getMoneyActivite. "');";
    $MoneyActivite= $this->ModelPs->getRequeteOne($getMoneyActivite);

    $TRANCHE_ID=$TRIMESTRE_ID;
    $ANNEE_BUDGETAIRE_ID = $this->get_annee_budgetaire();

    $resteEng=0;
    $MontantRestant = 0;
    $MontantVote = 0;
    $TRIMESTRE_ID=0;
    $qte=0;

    if ($TRANCHE_ID == 1) {
      $MontantVote = floatval($MoneyActivite['T1']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T1']);
      $TRIMESTRE_ID=1;
      $qte=$MoneyActivite['QT1'];
    } else if ($TRANCHE_ID == 2) {
      $MontantVote = floatval($MoneyActivite['T2']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T2']);
      $TRIMESTRE_ID=2;
      $qte=$MoneyActivite['QT2'];
    }else if ($TRANCHE_ID == 3) {
      $MontantVote = floatval($MoneyActivite['T3']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T3']);
      $TRIMESTRE_ID=3;
      $qte=$MoneyActivite['QT3'];
    }else if ($TRANCHE_ID == 4) {
      $MontantVote = floatval($MoneyActivite['T4']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T4']);
      $TRIMESTRE_ID=4;
      $qte=$MoneyActivite['QT4'];    
    }

    $montantRec = $MontantVote;
    $montantRec_vote = number_format($montantRec,'4',',',' ');
    $montantReste=$MontantRestant;
    $montantReste_ptba = number_format($MontantRestant,'4',',',' ');
    $TRIMESTRE_ID=$TRIMESTRE_ID;

    $reste_Engage= number_format($resteEng,4,',',' ');
    $quantite = $qte;
    $unite=$MoneyActivite['UNITE'];

    $BUDGET_ANNUEL_FORMAT=number_format($MoneyActivite['BUDGET_ANNUEL'],'4',',',' ');

    $output = array(
      "montant_vote" => $MontantVote,
      "vote" =>$montantRec_vote,
      "montant_restant" =>$montantReste,
      "restant" =>$montantReste_ptba,
      "program_code" => $MoneyActivite['CODE_PROGRAMME'],
      "programs" => $MoneyActivite['INTITULE_PROGRAMME'],
      "action_code" => $MoneyActivite['CODE_ACTION'], 
      "PROGRAMME_ID"=>$MoneyActivite['PROGRAMME_ID'],
      "ACTION_ID"=>$MoneyActivite['ACTION_ID'],      
      "action" => $MoneyActivite['LIBELLE_ACTION'],
      "UNITE"=>$unite,
      "resteEng"=>$resteEng,
      "reste_Engage"=>$reste_Engage, 
      "qte_vote" =>$quantite,
      "BUDGET_ANNUEL"=>$MoneyActivite['BUDGET_ANNUEL'],
      "BUDGET_ANNUEL_FORMAT"=>$BUDGET_ANNUEL_FORMAT
    );

    return $this->response->setJSON($output);
  }

  // trouver le taux
  function get_taux($DEVISE_TYPE_ID=0)
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

    $getTaux  = 'SELECT DEVISE_TYPE_HISTO_ID,TAUX FROM devise_type_hist WHERE DEVISE_TYPE_ID='.$DEVISE_TYPE_ID.' AND IS_ACTIVE=1';
    $getTaux = "CALL `getTable`('" . $getTaux . "');";
    $taux = $this->ModelPs->getRequeteOne($getTaux);

    $dev = number_format($taux['TAUX'],4,',',' ');

    $output = array(

      "devise" => $taux['TAUX'],
      "dev"=>$dev,
      "id_taux"=>$taux['DEVISE_TYPE_HISTO_ID']
    );

    return $this->response->setJSON($output);
  }

  function get_docs($TYPE_ENGAGEMENT_ID=0)
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
    if ($TYPE_ENGAGEMENT_ID==1) 
    {
      $getdoc  = 'SELECT BUDGETAIRE_TYPE_DOCUMENT_ID,DESC_BUDGETAIRE_TYPE_DOCUMENT FROM `budgetaire_type_document` WHERE BUDGETAIRE_TYPE_DOCUMENT_ID=1';
    }
    else
    {
      $getdoc  = 'SELECT BUDGETAIRE_TYPE_DOCUMENT_ID,DESC_BUDGETAIRE_TYPE_DOCUMENT FROM `budgetaire_type_document` WHERE 1';
    }                 

    $getdocument = "CALL `getTable`('".$getdoc."');";          
    $doc = $this->ModelPs->getRequete($getdocument);
    $docs='<option value="">-- '.lang('messages_lang.label_select').' --</option>';
    foreach($doc as $keys)
    {
      if ($TYPE_ENGAGEMENT_ID==1) 
      {
        $docs.='<option value='.$keys->BUDGETAIRE_TYPE_DOCUMENT_ID.' selected>'.$keys->DESC_BUDGETAIRE_TYPE_DOCUMENT.'</option>';
      }
      else
      {
        $docs.='<option value='.$keys->BUDGETAIRE_TYPE_DOCUMENT_ID.'>'.$keys->DESC_BUDGETAIRE_TYPE_DOCUMENT.'</option>';
      }

    }
    $output = array(
      "docs" => $docs
    );

    return $this->response->setJSON($output);
  }
}
?>