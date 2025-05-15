<?php
/*
*RUGAMBA JEAN VAINQUEUR
*Titre: Introduction des engagements budgétaires avec plusieurs tâches
*Numero de telephone: (+257) 66 33 43 25
*Whatsapp: (+257) 62 47 19 15 
*Email: jean.vainqueur@mediabox.bi
*Date: 12 Août,2024
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Introduction_Budget_Multi_Taches extends BaseController
{
  protected $session;
  protected $ModelPs;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
    $this->generate_note= new \App\Modules\double_commande_new\Controllers\Generate_Note();
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

 
  // affiche le view pour la 1er etape d'engagement budgetaire (engage)
  function etape1()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
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

          $getMasse  = 'SELECT TYPE_ENGAGEMENT_ID,DESC_TYPE_ENGAGEMENT FROM type_engagement WHERE TYPE_ENGAGEMENT_ID NOT IN(1,4,5)  ORDER BY DESC_TYPE_ENGAGEMENT ASC';
          $getMasse = "CALL `getTable`('" . $getMasse . "');";
          $data['grande']= $this->ModelPs->getRequete($getMasse);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);

          $get_tempo  = 'SELECT EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID FROM execution_budgetaire_tache_tempo WHERE INSTITUTION_ID ORDER BY EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID ASC';
          $get_tempo = "CALL `getTable`('" . $get_tempo . "');";
          $data['get_tempo']= $this->ModelPs->getRequete($get_tempo);

          $dataa=$this->converdate();
          $data['debut'] = $dataa['debut'];

          return view('App\Modules\double_commande_new\Views\Introduction_Budget_Multi_Taches_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  function save_tempo()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');;
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout'); 
    }

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user = $this->getBindParms('USER_ID', 'user_users', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
    $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);
    $USER_ID=$getuser['USER_ID'];

    //Form validation
    $rules = [
      'INSTITUTION_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'NOTE_REFERENCE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'SOUS_TUTEL_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'PTBA_TACHE_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'TYPE_MONTANT_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'TYPE_ENGAGEMENT_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'QTE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'COMMENTAIRE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'sous_act' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
    ];

    $TYPE_INSTITUTION_ID = $this->request->getPost('TYPE_INSTITUTION_ID');

    if($TYPE_INSTITUTION_ID == 2)
    {
       $rules['PAP_ACTIVITE_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }

    $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');

    if($TYPE_MONTANT_ID != 1)
    {
      $rules['MONTANT_EN_DEVISE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $rules['COUS_ECHANGE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $rules['DATE_COUT_DEVISE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }
    else
    {
      $rules['ENG_BUDGETAIRE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }

    $sous_act=$this->request->getPost('sous_act');

    if($sous_act == 1)
    {
      $rules['fini'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $fini=$this->request->getPost('fini');
      if($fini==1)
      {
        $rules['observ'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];

        $rules['resultat_attend'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];
      }  
    }
    
    $this->validation->setRules($rules);
    if (!$this->validation->withRequest($this->request)->run())
    {
      $errors = []; 
      foreach ($rules as $field => $rule) {
        $error = $this->validation->getError($field);
        if ($error !== null) {
            $errors[$field] = $error;
        }
      }

      $valeur = 1;
      $response = [
          'status' => false,
          'msg' => $errors,
          'valeur' => $valeur
      ];

      return $this->response->setJSON($response);
    }

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $NOTE_REFERENCE = $this->request->getPost('NOTE_REFERENCE');
    $NOTE_REFERENCE=addslashes($NOTE_REFERENCE);
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
    $PAP_ACTIVITE_ID = $this->request->getPost('PAP_ACTIVITE_ID');
    $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
    $ENG_BUDGETAIRE = $this->request->getPost('ENG_BUDGETAIRE');
    $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');
    $COMMENTAIRE=$this->request->getPost('COMMENTAIRE');
    $CREDIT_VOTE=$this->request->getPost('montant_vote');  
    $TRANSFERTS_CREDITS=$this->request->getPost('get_trans');
    $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
    $TAUX_ECHANGE_ID=$this->request->getPost('TAUX_ECHANGE_ID'); 
    $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
    $ACTION_ID = $this->request->getPost('ACTION_ID');
    $DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');
    $COUS_ECHANGE= ($TYPE_MONTANT_ID==1) ? 1 : $this->request->getPost('COUS_ECHANGE');
    $COUS_ECHANGE=str_replace(" ", "", $COUS_ECHANGE);
    $UNITE=$this->request->getPost('UNITE');
    $UNITE=addslashes($UNITE);

     
    $fini=0; 
    $resultat_attend=0;
    $observ_resultat='';
    $QTE=0;
    if ($sous_act==1)
    {
      $fini=$this->request->getPost('fini'); 
      if ($fini==1)
      {
        $observ_resultat=$this->request->getPost('observ');
        $resultat_attend=$this->request->getPost('resultat_attend'); 
        $QTE=$this->request->getPost('QTE');
      }
      else
      {
        $QTE=$this->request->getPost('QTE');
      }
    }
    else
    {
      $resultat_attend=$this->request->getPost('QTE'); 
      $QTE=$this->request->getPost('QTE');     
    }

    $MONTANT_EN_DEVISE=0;        
    $DATE_COUT_DEVISE= null;

    if ($TYPE_MONTANT_ID!=1)
    {
      $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
      $MONTANT_EN_DEVISE=$this->request->getPost('MONTANT_EN_DEVISE');
      $DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');
      $ENG_BUDGETAIRE = floatval($COUS_ECHANGE) * floatval($MONTANT_EN_DEVISE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0, ',', '');
    }
    
    $COMMENTAIRE = addslashes($COMMENTAIRE);
    $observ_resultat = addslashes($observ_resultat);

    $QTE = str_replace(".",",",$QTE);
    $resultat_attend = str_replace(".",",",$resultat_attend);
  
    $existance = 'SELECT exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_execution_tache exec_task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_task.EXECUTION_BUDGETAIRE_ID WHERE exec_task.PTBA_TACHE_ID='.$PTBA_TACHE_ID.' AND exec.NUMERO_BON_ENGAGEMENT IS NULL';
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
        $val_pap= ",".$PAP_ACTIVITE_ID."";
      }
      
      if(empty($EXECUTION_BUDGETAIRE_ID))
      {
        $insertIntoExec='execution_budgetaire_tache_tempo';
        $columExec="INSTITUTION_ID,SOUS_TITRE_ID,PROGRAMME_ID,ACTION_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID".$col_pap.",PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_BUDGETAIRE_DEVISE,DEVISE_TYPE_ID,DEVISE_TYPE_HISTO_ENG_ID,DATE_COUR_DEVISE,TAUX,QTE,COMMENTAIRE,TYPE_ENGAGEMENT_ID,EST_SOUS_TACHE,EST_FINI_TACHE,RESULTAT_ATTENDUS,OBSERVATION_RESULTAT,NOTE_REFERENCE,USER_ID,UNITE";

        if($DATE_COUT_DEVISE===null)
        {
          $datacolumsExec="{$INSTITUTION_ID},{$SOUS_TUTEL_ID},{$PROGRAMME_ID},{$ACTION_ID},{$CODE_NOMENCLATURE_BUDGETAIRE_ID}{$val_pap},{$PTBA_TACHE_ID},{$ENG_BUDGETAIRE},{$MONTANT_EN_DEVISE},{$TYPE_MONTANT_ID},{$DEVISE_TYPE_HISTO_ID},null,{$COUS_ECHANGE},'{$QTE}','{$COMMENTAIRE}',{$TYPE_ENGAGEMENT_ID},{$sous_act},{$fini},'{$resultat_attend}','{$observ_resultat}','{$NOTE_REFERENCE}',{$USER_ID},'{$UNITE}'";

        } else {

          $datacolumsExec="{$INSTITUTION_ID},{$SOUS_TUTEL_ID},{$PROGRAMME_ID},{$ACTION_ID},{$CODE_NOMENCLATURE_BUDGETAIRE_ID}{$val_pap},{$PTBA_TACHE_ID},{$ENG_BUDGETAIRE},{$MONTANT_EN_DEVISE},{$TYPE_MONTANT_ID},{$DEVISE_TYPE_HISTO_ID},'{$DATE_COUT_DEVISE}',{$COUS_ECHANGE},'{$QTE}','{$COMMENTAIRE}',{$TYPE_ENGAGEMENT_ID},{$sous_act},{$fini},'{$resultat_attend}','{$observ_resultat}','{$NOTE_REFERENCE}',{$USER_ID},'{$UNITE}'";
        }

        $this->save_all_table($insertIntoExec, $columExec, $datacolumsExec);
      }else{

        $insertIntoExec='execution_budgetaire_execution_tache';
        $columExec="EXECUTION_BUDGETAIRE_ID,PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_BUDGETAIRE_DEVISE,UNITE,QTE,EST_SOUS_TACHE,EST_FINI_TACHE,RESULTAT_ATTENDUS,OBSERVATION_RESULTAT,USER_ID";

        $datacolumsExec="{$EXECUTION_BUDGETAIRE_ID},{$PTBA_TACHE_ID},{$ENG_BUDGETAIRE},{$MONTANT_EN_DEVISE},'{$UNITE}','{$QTE}',{$sous_act},{$fini},'{$resultat_attend}','{$observ_resultat}',{$USER_ID}";

        $this->save_all_table($insertIntoExec, $columExec, $datacolumsExec);

        //montants exécutions
        $get_exec = "SELECT ANNEE_BUDGETAIRE_ID,TRIMESTRE_ID,ENG_BUDGETAIRE,ENG_BUDGETAIRE_DEVISE FROM execution_budgetaire WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;
        $get_exec = 'CALL getTable("'.$get_exec.'");'; 
        $exec_one = $this->ModelPs->getRequeteOne($get_exec);
        $TRIMESTRE_ID = $exec_one['TRIMESTRE_ID'];

        $eng_budget_exec = floatval($exec_one['ENG_BUDGETAIRE']) + floatval($ENG_BUDGETAIRE);
        $eng_budget_dev_exec = floatval($exec_one['ENG_BUDGETAIRE_DEVISE']) + floatval($MONTANT_EN_DEVISE);

        //Update dans la table des exécutions budgétaires
        $whereEx ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;        
        $insertIntoEx='execution_budgetaire';
        $columEx="ENG_BUDGETAIRE=".$eng_budget_exec.",ENG_BUDGETAIRE_DEVISE=".$eng_budget_dev_exec;
        $this->update_all_table($insertIntoEx,$columEx,$whereEx);


        $MoneyRest  = 'SELECT ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T1,BUDGET_UTILISE_T2,BUDGET_UTILISE_T3,BUDGET_UTILISE_T4 FROM ptba_tache WHERE PTBA_TACHE_ID='.$PTBA_TACHE_ID.' AND ANNEE_BUDGETAIRE_ID='.$exec_one['ANNEE_BUDGETAIRE_ID'];
        $MoneyRest = "CALL `getTable`('" . $MoneyRest . "');";
        $RestPTBA = $this->ModelPs->getRequeteOne($MoneyRest);

        $apresEng = 0;
        $total_utilise = 0;

        if ($TRIMESTRE_ID==1 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$exec_one['ANNEE_BUDGETAIRE_ID']) 
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T1']) - floatval($ENG_BUDGETAIRE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T1']) + floatval($ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T1=".$apresEng.",BUDGET_UTILISE_T1=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==2 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$exec_one['ANNEE_BUDGETAIRE_ID'])
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T2']) - floatval($ENG_BUDGETAIRE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T2']) + floatval($ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T2=".$apresEng.",BUDGET_UTILISE_T2=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==3 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$exec_one['ANNEE_BUDGETAIRE_ID'])
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T3']) - floatval($ENG_BUDGETAIRE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T3']) + floatval($ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T3=".$apresEng.",BUDGET_UTILISE_T3=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==4 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$exec_one['ANNEE_BUDGETAIRE_ID'])
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T4']) - floatval($ENG_BUDGETAIRE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T4']) + floatval($ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T4=".$apresEng.",BUDGET_UTILISE_T4=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }

      }
              
    }
    else
    {
      $valeur = 2;
      $output = array('status' => FALSE, 'valeur' => $valeur, 'msg_error' => lang('messages_lang.task_exist_deja'));
      return $this->response->setJSON($output);
    }
    
    $output = array('status' => TRUE, 'TYPE_MONTANT_ID' => $TYPE_MONTANT_ID);
    return $this->response->setJSON($output);

  }

  //Liste des tâches se trouvant dans tempo
  function listing_tempo()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $critere1="";
    $cond_user_id='';
   
    $critere3="";
    //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $NOTE_REFERENCE = $this->request->getPost('NOTE_REFERENCE');
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $var_search = addcslashes($var_search,"'");
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';

    $requetedebase="SELECT temp.EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID,act.DESC_PAP_ACTIVITE,ptba_tache.DESC_TACHE,temp.QTE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,temp.MONTANT_ENG_BUDGETAIRE,temp.MONTANT_ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,temp.COMMENTAIRE FROM execution_budgetaire_tache_tempo temp JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=temp.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=temp.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=temp.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=temp.DEVISE_TYPE_ID WHERE 1 AND temp.INSTITUTION_ID=".$INSTITUTION_ID." AND NOTE_REFERENCE='".$NOTE_REFERENCE."'";

    $order_column=array('ligne.CODE_NOMENCLATURE_BUDGETAIRE','act.DESC_PAP_ACTIVITE','ptba_tache.DESC_TACHE','temp.COMMENTAIRE','temp.QTE','dev.DESC_DEVISE_TYPE','temp.MONTANT_ENG_BUDGETAIRE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba_tache.DESC_TACHE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba_tache.DESC_TACHE LIKE '%$var_search%' OR temp.COMMENTAIRE LIKE '%$var_search%' OR temp.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR temp.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR temp.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

    $critaire =" ";
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $order_by . " " . $limit;
    
    // condition pour le query filter
    $conditionsfilter=$critaire." ".$search;
    
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {

      $DESC_PAP_ACTIVITE=(mb_strlen($row->DESC_PAP_ACTIVITE) > 13) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

      $DESC_PAP_ACTIVITE=!empty($DESC_PAP_ACTIVITE)?$DESC_PAP_ACTIVITE:'-';

      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 9) ? (mb_substr($row->DESC_TACHE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;

      $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 20) ? (mb_substr($row->COMMENTAIRE, 0, 19) . '...<a class="btn-sm" data-toggle="modal" data-target="#commentaire'. $row->EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID . ' data-toggle="tooltip" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></center></a>') : $row->COMMENTAIRE;

      $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE);
      $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,0,","," ");
      if($row->DEVISE_TYPE_ID!=1)
      {
        $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE_DEVISE);
        $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,4,","," ");
      } 

      $action='';
      $sub_array = array();
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $DESC_TACHE;
      $sub_array[] = $COMMENTAIRE;
      $sub_array[] = $row->QTE;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $MONTANT_ENG_BUDGETAIRE;
      $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("double_commande_new/Introduction_Budget_Multi_Taches/is_correct_tempo/".md5($row->EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID))."'><label>&nbsp;&nbsp;".lang('messages_lang.bouton_modifier')."</label></a>
        </li>
        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$row->EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID.")' title='corriger' ><label>&nbsp;&nbsp;<font color='red'>".lang('messages_lang.supprimer_action')."</font></label></a>

        </li>
        <div style='display:none;' id='message".$row->EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID."'>
        <center>
          <h5><strong>".lang('messages_lang.ask_correct_task')."<br><center><font color='green'>".$row->DESC_TACHE."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
          </h5>
        </center>
        </div>
        <div style='display:none;' id='footer".$row->EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID."'>
          <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
                            ".lang('messages_lang.quiter_action')."
          </button>
          <a href='".base_url("double_commande_new/Introduction_Budget_Multi_Taches/is_delete/".$row->EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.supprimer_action')."</a>
        </div>";
      $sub_array[] = $action;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebases. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //Page de modification dans tempo
  function is_correct_tempo($EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID)
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id ='';
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
       return redirect('Login_Ptba/do_logout');
    }

    $infoAffiche  = 'SELECT EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID,PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_BUDGETAIRE_DEVISE,DEVISE_TYPE_ID,DEVISE_TYPE_HISTO_ENG_ID,DATE_COUR_DEVISE,TAUX,QTE,UNITE,COMMENTAIRE,TYPE_ENGAGEMENT_ID,EST_SOUS_TACHE,EST_FINI_TACHE,RESULTAT_ATTENDUS,OBSERVATION_RESULTAT,NOTE_REFERENCE,USER_ID,INSTITUTION_ID,SOUS_TITRE_ID,PAP_ACTIVITE_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID FROM execution_budgetaire_tache_tempo WHERE 1 AND md5(EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID) = "'.$EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID.'"';
    $infoAffiche = "CALL `getTable`('" .$infoAffiche."');";
    $data['tempo']= $this->ModelPs->getRequeteOne($infoAffiche);

    //print_r($data['tempo']);exit();

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=1','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {

        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID =1',' DESC_ETAPE_DOUBLE_COMMANDE DESC');
          $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

          $data['etape1'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];

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

          $getSousTutel  = 'SELECT SOUS_TUTEL_ID,CODE_SOUS_TUTEL,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE INSTITUTION_ID='.$data['tempo']['INSTITUTION_ID'].' ORDER BY CODE_SOUS_TUTEL ASC';
          $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
          $data['sous_titre'] = $this->ModelPs->getRequete($getSousTutel);

          $ligne  = 'SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE SOUS_TUTEL_ID='.$data['tempo']['SOUS_TITRE_ID'].' AND INSTITUTION_ID='.$data['tempo']['INSTITUTION_ID'].' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE ASC';
          $ligne = "CALL `getTable`('" . $ligne. "');";
          $data['get_ligne'] = $this->ModelPs->getRequete($ligne);

          $activite  = 'SELECT PAP_ACTIVITE_ID,DESC_PAP_ACTIVITE FROM pap_activites WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID ="'.$data['tempo']['CODE_NOMENCLATURE_BUDGETAIRE_ID'].'" ORDER BY PAP_ACTIVITE_ID ASC';
          $activite = "CALL `getTable`('" . $activite . "');";
          $data['get_activite'] = $this->ModelPs->getRequete($activite);


          $tache  = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PAP_ACTIVITE_ID ="'.$data['tempo']['PAP_ACTIVITE_ID'].'" ORDER BY DESC_TACHE ASC';
          if(empty($data['tempo']['PAP_ACTIVITE_ID']))
          {
            $tache  = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID ="'.$data['tempo']['CODE_NOMENCLATURE_BUDGETAIRE_ID'].'" ORDER BY DESC_TACHE ASC';
          }
          $tache = "CALL `getTable`('" . $tache . "');";
          $data['get_taches'] = $this->ModelPs->getRequete($tache);

          $getMasse  = 'SELECT TYPE_ENGAGEMENT_ID,DESC_TYPE_ENGAGEMENT FROM type_engagement WHERE TYPE_ENGAGEMENT_ID NOT IN(1,4,5)  ORDER BY DESC_TYPE_ENGAGEMENT ASC';
          $getMasse = "CALL `getTable`('" . $getMasse . "');";
          $data['grande']= $this->ModelPs->getRequete($getMasse);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);

          $dataa=$this->converdate();
          $data['debut'] = $dataa['debut'];

          return view('App\Modules\double_commande_new\Views\Intro_Multi_Taches_Corriger_Tempo_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  //Modification dans tempo
  function save_correct_tempo()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user = $this->getBindParms('USER_ID', 'user_users', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
    $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);
    $USER_ID=$getuser['USER_ID'];

    //Form validation
    $rules = [
      'INSTITUTION_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'NOTE_REFERENCE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'SOUS_TUTEL_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'PTBA_TACHE_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'TYPE_MONTANT_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'TYPE_ENGAGEMENT_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'QTE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'COMMENTAIRE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'sous_act' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
    ];

    $TYPE_INSTITUTION_ID = $this->request->getPost('TYPE_INSTITUTION_ID');

    if($TYPE_INSTITUTION_ID == 2)
    {
       $rules['PAP_ACTIVITE_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }

    $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');

    if($TYPE_MONTANT_ID != 1)
    {
      $rules['MONTANT_EN_DEVISE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $rules['COUS_ECHANGE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $rules['DATE_COUT_DEVISE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }
    else
    {
      $rules['ENG_BUDGETAIRE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }

    $sous_act=$this->request->getPost('sous_act');

    if($sous_act == 1)
    {
      $rules['fini'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $fini=$this->request->getPost('fini');
      if($fini==1)
      {
        $rules['observ'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];

        $rules['resultat_attend'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];
      }  
    }
    

    $this->validation->setRules($rules);
    if (!$this->validation->withRequest($this->request)->run())
    {
      $errors = []; 
      foreach ($rules as $field => $rule) {
        $error = $this->validation->getError($field);
        if ($error !== null) {
            $errors[$field] = $error;
        }
      }

      $valeur = 1;
      $response = [
          'status' => false,
          'msg' => $errors,
          'valeur' => $valeur
      ];

      return $this->response->setJSON($response);
    }

    $TEMPO_ID = $this->request->getPost('TEMPO_ID');
    $NOTE_REFERENCE = $this->request->getPost('NOTE_REFERENCE');
    $NOTE_REFERENCE=addslashes($NOTE_REFERENCE);
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
    $PAP_ACTIVITE_ID = $this->request->getPost('PAP_ACTIVITE_ID');
    $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
    $ENG_BUDGETAIRE = $this->request->getPost('ENG_BUDGETAIRE');
    $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');
    $COMMENTAIRE=$this->request->getPost('COMMENTAIRE');
    $CREDIT_VOTE=$this->request->getPost('montant_vote');  
    $TRANSFERTS_CREDITS=$this->request->getPost('get_trans');
    $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
    $TAUX_ECHANGE_ID=$this->request->getPost('TAUX_ECHANGE_ID'); 
    $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
    $ACTION_ID = $this->request->getPost('ACTION_ID');
    $COUS_ECHANGE= ($TYPE_MONTANT_ID==1) ? 1 : $this->request->getPost('COUS_ECHANGE');
    $COUS_ECHANGE=str_replace(" ", "", $COUS_ECHANGE);
    $UNITE=$this->request->getPost('UNITE');
    $UNITE=addslashes($UNITE);

    $sous_act=$this->request->getPost('sous_act'); 
    $fini=0; 
    $resultat_attend=0;
    $observ_resultat='';
    $QTE=0;
    if ($sous_act==1)
    {
      $fini=$this->request->getPost('fini'); 
      if ($fini==1)
      {
        $observ_resultat=$this->request->getPost('observ');
        $resultat_attend=$this->request->getPost('resultat_attend'); 
        $QTE=$this->request->getPost('QTE');
      }
      else
      {
        $QTE=$this->request->getPost('QTE');
      }
    }
    else
    {
      $resultat_attend=$this->request->getPost('QTE'); 
      $QTE=$this->request->getPost('QTE');     
    }

    $MONTANT_EN_DEVISE=0;        
    $DATE_COUT_DEVISE= null;

    if ($TYPE_MONTANT_ID!=1)
    {
      $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
      $MONTANT_EN_DEVISE=$this->request->getPost('MONTANT_EN_DEVISE');
      $DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');
      $ENG_BUDGETAIRE=floatval($COUS_ECHANGE) * floatval($MONTANT_EN_DEVISE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0, ',', '');
    }
    
    $COMMENTAIRE = addslashes($COMMENTAIRE);
    $observ_resultat = addslashes($observ_resultat);

    $QTE = str_replace(".",",",$QTE);
    $resultat_attend = str_replace(".",",",$resultat_attend);
    $resultat_attend = addslashes($resultat_attend);
  
    $existance = 'SELECT exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_execution_tache exec_task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_task.EXECUTION_BUDGETAIRE_ID WHERE exec_task.PTBA_TACHE_ID='.$PTBA_TACHE_ID.' AND exec.NUMERO_BON_ENGAGEMENT IS NULL';
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
      $val_pap= null;
      if (!empty($PAP_ACTIVITE_ID))
      {
        $col_pap=',PAP_ACTIVITE_ID';
        $val_pap= $PAP_ACTIVITE_ID;
      }

      if($DATE_COUT_DEVISE===null)
      {
        $columExec="INSTITUTION_ID={$INSTITUTION_ID},SOUS_TITRE_ID={$SOUS_TUTEL_ID},PROGRAMME_ID={$PROGRAMME_ID},ACTION_ID={$ACTION_ID},CODE_NOMENCLATURE_BUDGETAIRE_ID={$CODE_NOMENCLATURE_BUDGETAIRE_ID},PAP_ACTIVITE_ID={$val_pap},PTBA_TACHE_ID={$PTBA_TACHE_ID},MONTANT_ENG_BUDGETAIRE={$ENG_BUDGETAIRE},MONTANT_ENG_BUDGETAIRE_DEVISE={$MONTANT_EN_DEVISE},DEVISE_TYPE_ID={$TYPE_MONTANT_ID},DEVISE_TYPE_HISTO_ENG_ID={$DEVISE_TYPE_HISTO_ID},TAUX={$COUS_ECHANGE},QTE='{$QTE}',COMMENTAIRE='{$COMMENTAIRE}',TYPE_ENGAGEMENT_ID={$TYPE_ENGAGEMENT_ID},EST_SOUS_TACHE={$sous_act},EST_FINI_TACHE={$fini},RESULTAT_ATTENDUS='{$resultat_attend}',OBSERVATION_RESULTAT='{$observ_resultat}',NOTE_REFERENCE='{$NOTE_REFERENCE}',USER_ID={$USER_ID},UNITE='{$UNITE}'";

      } else {

        $columExec="INSTITUTION_ID={$INSTITUTION_ID},SOUS_TITRE_ID={$SOUS_TUTEL_ID},PROGRAMME_ID={$PROGRAMME_ID},ACTION_ID={$ACTION_ID},CODE_NOMENCLATURE_BUDGETAIRE_ID={$CODE_NOMENCLATURE_BUDGETAIRE_ID},PAP_ACTIVITE_ID={$val_pap},PTBA_TACHE_ID={$PTBA_TACHE_ID},MONTANT_ENG_BUDGETAIRE={$ENG_BUDGETAIRE},MONTANT_ENG_BUDGETAIRE_DEVISE={$MONTANT_EN_DEVISE},DEVISE_TYPE_ID={$TYPE_MONTANT_ID},DEVISE_TYPE_HISTO_ENG_ID={$DEVISE_TYPE_HISTO_ID},DATE_COUR_DEVISE='{$DATE_COUT_DEVISE}',TAUX={$COUS_ECHANGE},QTE='{$QTE}',COMMENTAIRE='{$COMMENTAIRE}',TYPE_ENGAGEMENT_ID={$TYPE_ENGAGEMENT_ID},EST_SOUS_TACHE={$sous_act},EST_FINI_TACHE={$fini},RESULTAT_ATTENDUS='{$resultat_attend}',OBSERVATION_RESULTAT='{$observ_resultat}',NOTE_REFERENCE='{$NOTE_REFERENCE}',USER_ID={$USER_ID},UNITE='{$UNITE}'";
        
      }

      $whereExec ="EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID = ".$TEMPO_ID;
      $insertIntoExec='execution_budgetaire_tache_tempo';  
      $this->update_all_table($insertIntoExec,$columExec,$whereExec);
           
    }
    else
    {
      $valeur = 2;
      $output = array('status' => FALSE, 'valeur' => $valeur, 'msg_error' => lang('messages_lang.task_exist_deja'));
      return $this->response->setJSON($output);
    }
    
    $output = array('status' => TRUE );
    return $this->response->setJSON($output);

  }

  //Suppression de l'engagement dans la table des tempos
  function is_delete($EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID)
  {
    $session  = \Config\Services::session();

    $db = db_connect();     
    $critere ="EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID =" .$EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID;
    $table="execution_budgetaire_tache_tempo";
    $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";

    $this->ModelPs->createUpdateDelete($deleteRequete,$bindparams);
    /* $data = ['message' => ''.lang('messages_lang.message_success_suppr').''];
    session()->setFlashdata('alert', $data);*/
    return redirect('double_commande_new/Introduction_Budget_Multi_Taches/etape1');
  }

  //Liste des tâches se trouvant dans exécution_tache
  function listing_task()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $critere1="";
    $cond_user_id='';
   
    $critere3="";
    //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $id = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $id_exec_titr_dec = $this->request->getPost('id_exec_titr_dec');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';

    $requetedebase="SELECT task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,task.EXECUTION_BUDGETAIRE_ID,act.DESC_PAP_ACTIVITE,task.PTBA_TACHE_ID,task.MONTANT_ENG_BUDGETAIRE,task.MONTANT_ENG_BUDGETAIRE_DEVISE,ptba.DESC_TACHE,task.QTE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=2 AND task.EXECUTION_BUDGETAIRE_ID=".$id."";

    $order_column=array('ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba.DESC_TACHE','task.QTE','dev.DESC_DEVISE_TYPE','task.MONTANT_ENG_BUDGETAIRE',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY ptba.DESC_TACHE ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ptba.DESC_TACHE LIKE '%$var_search%' OR task.QTE LIKE '%$var_search%' OR dev.DESC_DEVISE_TYPE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE LIKE '%$var_search%' OR task.MONTANT_ENG_BUDGETAIRE_DEVISE LIKE '%$var_search%')"):'';

    $critaire =" ";
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $order_by . " " . $limit;
    
    // condition pour le query filter
    $conditionsfilter=$critaire." ".$search;
    
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    //print_r($query_secondaire);exit();

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {

      $DESC_PAP_ACTIVITE=(mb_strlen($row->DESC_PAP_ACTIVITE) > 13) ? (mb_substr($row->DESC_PAP_ACTIVITE, 0, 12) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESC_PAP_ACTIVITE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_PAP_ACTIVITE;

      $DESC_PAP_ACTIVITE=!empty($DESC_PAP_ACTIVITE)?$DESC_PAP_ACTIVITE:'-';

      $DESC_TACHE = (mb_strlen($row->DESC_TACHE) > 9) ? (mb_substr($row->DESC_TACHE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" data-target="#activite '. $row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID . ' data-toggle="tooltip" title="'.$row->DESC_TACHE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_TACHE;


      $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE);
      $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,0,","," ");
      if($row->DEVISE_TYPE_ID!=1)
      {
        $MONTANT_ENG_BUDGETAIRE=floatval($row->MONTANT_ENG_BUDGETAIRE_DEVISE);
        $MONTANT_ENG_BUDGETAIRE=number_format($MONTANT_ENG_BUDGETAIRE,4,","," ");
      } 

      $action='';
      $sub_array = array();
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = $DESC_TACHE;
      $sub_array[] = $row->QTE;
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      $sub_array[] = $MONTANT_ENG_BUDGETAIRE;
      $action = '<div class="dropdown" style="color:#fff;">
        <a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-left">';

        $action .="<li>
        <a href='".base_url("double_commande_new/Introduction_Budget_Multi_Taches/is_correct_task/".md5($row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID).'/'.$id_exec_titr_dec)."'><label>&nbsp;&nbsp;".lang('messages_lang.bouton_modifier')."</label></a>
        </li>
        <li>
        <a href='javascript:void(0)' onclick='show_modal(".$row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID.")' title='".lang('messages_lang.supprimer_action')."' ><label>&nbsp;&nbsp;<font color='red'>".lang('messages_lang.supprimer_action')."</font></label></a>

        </li>
        <div style='display:none;' id='message".$row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID."'>
        <center>
          <h5><strong>".lang('messages_lang.ask_correct_task')."<br><center><font color='green'>".$row->DESC_TACHE."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
          </h5>
        </center>
        </div>
        <div style='display:none;' id='footer".$row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID."'>
          <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
                            ".lang('messages_lang.quiter_action')."
          </button>
          <a href='".base_url("double_commande_new/Introduction_Budget_Multi_Taches/is_delete_task/".$row->EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID.'/'.$id_exec_titr_dec)."' class='btn btn-danger btn-md'>".lang('messages_lang.supprimer_action')."</a>
        </div>";
      $sub_array[] = $action;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebases. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);//echo json_encode($output);
  }

  //Suppression de l'engagement dans la table d'execution_taches
  function is_delete_task($EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID = 0, $id_exec_titr_dec = 0)
  {
    $session  = \Config\Services::session();

    $db = db_connect();
    $get_task = "SELECT exec.EXECUTION_BUDGETAIRE_ID,ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_BUDGETAIRE_DEVISE,TRIMESTRE_ID,ENG_BUDGETAIRE,ENG_BUDGETAIRE_DEVISE FROM execution_budgetaire_execution_tache task JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID WHERE 1 AND task.EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID=".$EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID;
    $get_task = 'CALL getTable("'.$get_task.'");';
    $task_one = $this->ModelPs->getRequeteOne($get_task);
    $TRIMESTRE_ID = $task_one['TRIMESTRE_ID'];

    $eng_budget_exec = floatval($task_one['ENG_BUDGETAIRE']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE']);
    $eng_budget_dev_exec = floatval($task_one['ENG_BUDGETAIRE_DEVISE']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE_DEVISE']);

    $whereExec ="EXECUTION_BUDGETAIRE_ID = ".$task_one['EXECUTION_BUDGETAIRE_ID'];        
    $insertIntoExec='execution_budgetaire';
    $columExec="ENG_BUDGETAIRE=".$eng_budget_exec.",ENG_BUDGETAIRE_DEVISE=".$eng_budget_dev_exec;
    $this->update_all_table($insertIntoExec,$columExec,$whereExec);

    //print_r($task_one);exit();

    $MoneyRest  = 'SELECT ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T1,BUDGET_UTILISE_T2,BUDGET_UTILISE_T3,BUDGET_UTILISE_T4 FROM ptba_tache WHERE PTBA_TACHE_ID='.$task_one['PTBA_TACHE_ID'].' AND ANNEE_BUDGETAIRE_ID='.$task_one['ANNEE_BUDGETAIRE_ID'];
    $MoneyRest = "CALL `getTable`('" . $MoneyRest . "');";
    $RestPTBA = $this->ModelPs->getRequeteOne($MoneyRest);

    $apresEng = 0;
    $total_utilise = 0;

    if ($TRIMESTRE_ID==1 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$task_one['ANNEE_BUDGETAIRE_ID']) 
    {
      $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T1']) + floatval($task_one['MONTANT_ENG_BUDGETAIRE']);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T1']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE']);

      $whereptba ="PTBA_TACHE_ID = ".$task_one['PTBA_TACHE_ID'];        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T1=".$apresEng.",BUDGET_UTILISE_T1=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
    else if ($TRIMESTRE_ID==2 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$task_one['ANNEE_BUDGETAIRE_ID'])
    {
      $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T2']) + floatval($task_one['MONTANT_ENG_BUDGETAIRE']);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T2']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE']);

      $whereptba ="PTBA_TACHE_ID = ".$task_one['PTBA_TACHE_ID'];        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T2=".$apresEng.",BUDGET_UTILISE_T2=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
    else if ($TRIMESTRE_ID==3 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$task_one['ANNEE_BUDGETAIRE_ID'])
    {
      $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T3']) + floatval($task_one['MONTANT_ENG_BUDGETAIRE']);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T3']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE']);

      $whereptba ="PTBA_TACHE_ID = ".$task_one['PTBA_TACHE_ID'];        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T3=".$apresEng.",BUDGET_UTILISE_T3=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
    else if ($TRIMESTRE_ID==4 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$task_one['ANNEE_BUDGETAIRE_ID'])
    {
      $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T4']) + floatval($task_one['MONTANT_ENG_BUDGETAIRE']);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T4']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE']);

      $whereptba ="PTBA_TACHE_ID = ".$task_one['PTBA_TACHE_ID'];        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T4=".$apresEng.",BUDGET_UTILISE_T4=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }

    $critere ="EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID =" .$EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID;
    $table="execution_budgetaire_execution_tache";
    $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";

    $this->ModelPs->createUpdateDelete($deleteRequete,$bindparams);
    $url = base_url('double_commande_new/Introduction_Budget_Multi_Taches/corrige_etape1/'.$id_exec_titr_dec);
    return redirect()->to($url);
  }

  //insertion de l'engagement budgetaire
  function save_etape1()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if(empty($user_id))
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
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'NOTE_REFERENCE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
    ];

    $this->validation->setRules($rules);

    if($this->validation->withRequest($this->request)->run())
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user = $this->getBindParms('USER_ID', 'user_users', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
      $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);
      $USER_ID=$getuser['USER_ID'];

      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $NOTE_REFERENCE = $this->request->getPost('NOTE_REFERENCE');
      
      $get_tempo = "SELECT EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID,INSTITUTION_ID,SOUS_TITRE_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_BUDGETAIRE_DEVISE,DEVISE_TYPE_ID,DEVISE_TYPE_HISTO_ENG_ID,DATE_COUR_DEVISE,TAUX,QTE,UNITE,COMMENTAIRE,TYPE_ENGAGEMENT_ID,EST_SOUS_TACHE,EST_FINI_TACHE,RESULTAT_ATTENDUS,OBSERVATION_RESULTAT,NOTE_REFERENCE,USER_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID FROM execution_budgetaire_tache_tempo WHERE 1 AND INSTITUTION_ID=".$INSTITUTION_ID." AND NOTE_REFERENCE='".$NOTE_REFERENCE."'";
      $get_tempo = 'CALL getTable("'.$get_tempo.'");';
      $tempo = $this->ModelPs->getRequete($get_tempo);
      $temp_one = $this->ModelPs->getRequeteOne($get_tempo);

      $DEVISE_TYPE_HISTO_ENG_ID = $temp_one['DEVISE_TYPE_HISTO_ENG_ID'];
      $DEVISE_TYPE_ID = $temp_one['DEVISE_TYPE_ID'];
      $COUR_DEVISE = $temp_one['TAUX'];
      $DATE_COUR_DEVISE = $temp_one['DATE_COUR_DEVISE'];
      $TYPE_ENGAGEMENT_ID = $temp_one['TYPE_ENGAGEMENT_ID'];
      $COMMENTAIRE = $temp_one['COMMENTAIRE'];

      $TRIMESTRE_ID = $this->converdate()['TRIMESTRE_ID'];
      $ANNEE_BUDGETAIRE_ID = $this->get_annee_budgetaire();
      $EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID = 2;

      $montant_eng_budget = 0;
      $montant_eng_devise = 0;
      foreach($tempo as $key)
      {
        $eng_budget = $key->MONTANT_ENG_BUDGETAIRE;
        $montant_eng_budget += $eng_budget;
        $eng_devise = $key->MONTANT_ENG_BUDGETAIRE_DEVISE;
        $montant_eng_devise += $eng_devise;
      }

      $SOUS_TITRE_ID=$temp_one['SOUS_TITRE_ID'];
      $CODE_NOMENCLATURE_BUDGETAIRE_ID=$temp_one['CODE_NOMENCLATURE_BUDGETAIRE_ID'];
      
      $insertIntoExec='execution_budgetaire';
      $columExec="ANNEE_BUDGETAIRE_ID,INSTITUTION_ID,SOUS_TUTEL_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,TRIMESTRE_ID,ENG_BUDGETAIRE,ENG_BUDGETAIRE_DEVISE,DEVISE_TYPE_HISTO_ENG_ID,DEVISE_TYPE_ID,EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID,USER_ID,TYPE_ENGAGEMENT_ID,COMMENTAIRE";

      $datacolumsExec=$ANNEE_BUDGETAIRE_ID.",".$INSTITUTION_ID.",".$SOUS_TITRE_ID.",".$CODE_NOMENCLATURE_BUDGETAIRE_ID.",".$TRIMESTRE_ID.",".$montant_eng_budget.",".$montant_eng_devise.",".$DEVISE_TYPE_HISTO_ENG_ID.",".$DEVISE_TYPE_ID.",".$EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID.",".$user_id.",".$TYPE_ENGAGEMENT_ID.",'".addslashes($COMMENTAIRE)."'";
      $EXECUTION_BUDGETAIRE_ID =$this->save_all_table($insertIntoExec,$columExec,$datacolumsExec);

      foreach($tempo as $key)
      {
        $sous_task= $key->EST_SOUS_TACHE; 
        $fini=0; 
        $resultat_attend=0;
        $observ_resultat=null;
        $QTE=0;
        if ($sous_task==1)
        {
          $fini=$key->EST_FINI_TACHE; 
          if ($fini==1)
          {
            $observ_resultat=$key->OBSERVATION_RESULTAT;
            $resultat_attend=$key->RESULTAT_ATTENDUS; 
            $QTE=$key->QTE;
          }
          else
          {
            $QTE=$key->QTE;
          }
        }
        else
        {
          $resultat_attend=$key->RESULTAT_ATTENDUS; 
          $QTE=$key->QTE;     
        }

        $MONTANT_EN_DEVISE=0;        
        $DATE_COUT_DEVISE= null;
        
        if($key->DEVISE_TYPE_ID !=1)
        {
          $TYPE_MONTANT_ID=$key->DEVISE_TYPE_ID;
          $MONTANT_EN_DEVISE=$key->MONTANT_ENG_BUDGETAIRE_DEVISE;
          $DATE_COUT_DEVISE=$key->DATE_COUR_DEVISE;
          $MONTANT_ENG_BUDGETAIRE = $key->MONTANT_ENG_BUDGETAIRE;
        }

        $getTache = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PTBA_TACHE_ID='.$key->PTBA_TACHE_ID;
        $getTache = "CALL `getTable`('" . $getTache . "');";
        $taches= $this->ModelPs->getRequeteOne($getTache);
        
        $ETAPE_ID=1;

        $MoneyRest  = 'SELECT ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T1,BUDGET_UTILISE_T2,BUDGET_UTILISE_T3,BUDGET_UTILISE_T4 FROM ptba_tache WHERE PTBA_TACHE_ID='.$key->PTBA_TACHE_ID.' AND ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
        $MoneyRest = "CALL `getTable`('" . $MoneyRest . "');";
        $RestPTBA = $this->ModelPs->getRequeteOne($MoneyRest);

        //Enregistrement dans la table des tâches multiples 
        $insertExecTask = 'execution_budgetaire_execution_tache';
        $columExecTask = "EXECUTION_BUDGETAIRE_ID,PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_BUDGETAIRE_DEVISE,UNITE,QTE,EST_SOUS_TACHE,EST_FINI_TACHE,RESULTAT_ATTENDUS,OBSERVATION_RESULTAT,USER_ID";

        $datacolumsExecTask = $EXECUTION_BUDGETAIRE_ID.",".$key->PTBA_TACHE_ID.",".$key->MONTANT_ENG_BUDGETAIRE.",".$key->MONTANT_ENG_BUDGETAIRE_DEVISE.",'".addslashes($key->UNITE)."','".$QTE."',".$sous_task.",".$fini.",'".addslashes($resultat_attend)."','".addslashes($observ_resultat)."',".$key->USER_ID;
        $this->save_all_table($insertExecTask, $columExecTask, $datacolumsExecTask);

        $apresEng = 0;
        $total_utilise = 0;

        if ($TRIMESTRE_ID==1 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_BUDGETAIRE_ID) 
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T1']) - floatval($key->MONTANT_ENG_BUDGETAIRE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T1']) + floatval($key->MONTANT_ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$key->PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T1=".$apresEng.",BUDGET_UTILISE_T1=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==2 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_BUDGETAIRE_ID)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T2']) - floatval($key->MONTANT_ENG_BUDGETAIRE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T2']) + floatval($key->MONTANT_ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$key->PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T2=".$apresEng.",BUDGET_UTILISE_T2=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==3 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_BUDGETAIRE_ID)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T3']) - floatval($key->MONTANT_ENG_BUDGETAIRE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T3']) + floatval($key->MONTANT_ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$key->PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T3=".$apresEng.",BUDGET_UTILISE_T3=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==4 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_BUDGETAIRE_ID)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T4']) - floatval($key->MONTANT_ENG_BUDGETAIRE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T4']) + floatval($key->MONTANT_ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$key->PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T4=".$apresEng.",BUDGET_UTILISE_T4=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }

      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ='.$ETAPE_ID,' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
      $etape_suivante= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);
      $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

      //Insertion dans détail
      $insertDetail = 'execution_budgetaire_tache_detail'; 
      $columDetail = "EXECUTION_BUDGETAIRE_ID,COUR_DEVISE";
      $datacolumsDetail = $EXECUTION_BUDGETAIRE_ID.",".$COUR_DEVISE;
      if($DATE_COUR_DEVISE !== null)
      {
        $columDetail = "EXECUTION_BUDGETAIRE_ID,COUR_DEVISE,DATE_COUR_DEVISE";
        $datacolumsDetail = $EXECUTION_BUDGETAIRE_ID.",".$COUR_DEVISE.",'".$DATE_COUR_DEVISE."'";
      }
      $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->save_all_table($insertDetail, $columDetail, $datacolumsDetail);

      //Insertion dans titre décaissement
      $insertDec = 'execution_budgetaire_titre_decaissement';
      $columDec = "EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID";
      $datacolumsDec = $EXECUTION_BUDGETAIRE_ID.",".$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$ETAPE_DOUBLE_COMMANDE_ID;
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->save_all_table($insertDec, $columDec, $datacolumsDec);
  
      //Insertion dans infos suppl
      $insertIntoSuppl='execution_budgetaire_tache_info_suppl';
      $columSuppl="EXECUTION_BUDGETAIRE_ID,NOTE_REFERENCE";
      $datacolumsSuppl=$EXECUTION_BUDGETAIRE_ID.",'".$NOTE_REFERENCE."'";
      $this->save_all_table($insertIntoSuppl,$columSuppl,$datacolumsSuppl);

      //Insertion dans historique
      $insertIntohist='execution_budgetaire_tache_detail_histo';
      $columhist="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID";
      $datacolumshist= $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$USER_ID.",".$ETAPE_ID;
      $this->save_all_table($insertIntohist,$columhist,$datacolumshist);

      foreach($tempo as $key)
      {
        $critere ="EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID=" .$key->EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID;
        $table="execution_budgetaire_tache_tempo";
        $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
        $deleteRequete = "CALL `deleteData`(?,?);";
        $this->ModelPs->createUpdateDelete($deleteRequete,$bindparams);
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

  //Page de modification dans execution_tache
  function is_correct_task($EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID = 0, $id_exec_titr_dec = 0)
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
       return redirect('Login_Ptba/do_logout');
    }

    $infoAffiche  = 'SELECT exec.EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID,task.PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_BUDGETAIRE_DEVISE,exec.DEVISE_TYPE_ID,exec.DEVISE_TYPE_HISTO_ENG_ID,dev.DATE_INSERTION,dev.TAUX,task.QTE,task.UNITE,exec.COMMENTAIRE,exec.TYPE_ENGAGEMENT_ID,task.EST_SOUS_TACHE,task.EST_FINI_TACHE,task.RESULTAT_ATTENDUS,task.OBSERVATION_RESULTAT,suppl.NOTE_REFERENCE,task.USER_ID,p.INSTITUTION_ID,p.SOUS_TUTEL_ID,p.PAP_ACTIVITE_ID,p.CODE_NOMENCLATURE_BUDGETAIRE_ID FROM execution_budgetaire_execution_tache task JOIN ptba_tache p ON p.PTBA_TACHE_ID=task.PTBA_TACHE_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID JOIN devise_type_hist dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_HISTO_ENG_ID JOIN execution_budgetaire_tache_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_ID=task.EXECUTION_BUDGETAIRE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=2 AND md5(EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID) = "'.$EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID.'"';
    $infoAffiche = "CALL `getTable`('" .$infoAffiche."');";
    $data['task']= $this->ModelPs->getRequeteOne($infoAffiche);
    $data['id_exec_titr_dec'] = $id_exec_titr_dec;


    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=4','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {

        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID =4',' DESC_ETAPE_DOUBLE_COMMANDE DESC');
          $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

          $data['etape1'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];

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

          $getSousTutel  = 'SELECT SOUS_TUTEL_ID,CODE_SOUS_TUTEL,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE INSTITUTION_ID='.$data['task']['INSTITUTION_ID'].' ORDER BY CODE_SOUS_TUTEL ASC';
          $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
          $data['sous_titre'] = $this->ModelPs->getRequete($getSousTutel);

          $ligne  = 'SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE SOUS_TUTEL_ID='.$data['task']['SOUS_TUTEL_ID'].' AND INSTITUTION_ID='.$data['task']['INSTITUTION_ID'].' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE ASC';
          $ligne = "CALL `getTable`('" . $ligne. "');";
          $data['get_ligne'] = $this->ModelPs->getRequete($ligne);

          $activite  = 'SELECT PAP_ACTIVITE_ID,DESC_PAP_ACTIVITE FROM pap_activites WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID ="'.$data['task']['CODE_NOMENCLATURE_BUDGETAIRE_ID'].'" ORDER BY PAP_ACTIVITE_ID ASC';
          $activite = "CALL `getTable`('" . $activite . "');";
          $data['get_activite'] = $this->ModelPs->getRequete($activite);


          $tache  = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PAP_ACTIVITE_ID ="'.$data['task']['PAP_ACTIVITE_ID'].'" ORDER BY DESC_TACHE ASC';
          if(empty($data['task']['PAP_ACTIVITE_ID']))
          {
            $tache  = 'SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID ="'.$data['task']['CODE_NOMENCLATURE_BUDGETAIRE_ID'].'" ORDER BY DESC_TACHE ASC';
          }
          $tache = "CALL `getTable`('" . $tache . "');";
          $data['get_taches'] = $this->ModelPs->getRequete($tache);

          $getMasse  = 'SELECT TYPE_ENGAGEMENT_ID,DESC_TYPE_ENGAGEMENT FROM type_engagement WHERE TYPE_ENGAGEMENT_ID NOT IN(1,4,5)  ORDER BY DESC_TYPE_ENGAGEMENT ASC';
          $getMasse = "CALL `getTable`('" . $getMasse . "');";
          $data['grande']= $this->ModelPs->getRequete($getMasse);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);

          $dataa=$this->converdate();
          $data['debut'] = $dataa['debut'];

          return view('App\Modules\double_commande_new\Views\Intro_Multi_Taches_Corriger_Task_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  //Modification dans execution_tache
  function save_correct_task()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user = $this->getBindParms('USER_ID', 'user_users', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
    $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);
    $USER_ID=$getuser['USER_ID'];

    //Form validation
    $rules = [
      'INSTITUTION_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'NOTE_REFERENCE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'SOUS_TUTEL_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'PTBA_TACHE_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'TYPE_MONTANT_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'TYPE_ENGAGEMENT_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'QTE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'COMMENTAIRE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],

      'sous_act' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
    ];

    $TYPE_INSTITUTION_ID = $this->request->getPost('TYPE_INSTITUTION_ID');

    if($TYPE_INSTITUTION_ID == 2)
    {
       $rules['PAP_ACTIVITE_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }

    $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');

    if($TYPE_MONTANT_ID != 1)
    {
      $rules['MONTANT_EN_DEVISE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $rules['COUS_ECHANGE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $rules['DATE_COUT_DEVISE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }
    else
    {
      $rules['ENG_BUDGETAIRE'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];
    }

    $sous_act=$this->request->getPost('sous_act');

    if($sous_act == 1)
    {
      $rules['fini'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $fini=$this->request->getPost('fini');
      if($fini==1)
      {
        $rules['observ'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];

        $rules['resultat_attend'] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ];
      }  
    }
    
    $this->validation->setRules($rules);
    if (!$this->validation->withRequest($this->request)->run())
    {
      $errors = []; 
      foreach ($rules as $field => $rule) {
        $error = $this->validation->getError($field);
        if ($error !== null) {
            $errors[$field] = $error;
        }
      }

      $valeur = 1;
      $response = [
          'status' => false,
          'msg' => $errors,
          'valeur' => $valeur
      ];

      return $this->response->setJSON($response);
    }

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID');
    $NOTE_REFERENCE = $this->request->getPost('NOTE_REFERENCE');
    $NOTE_REFERENCE=addslashes($NOTE_REFERENCE);
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
    $PAP_ACTIVITE_ID = $this->request->getPost('PAP_ACTIVITE_ID');
    $PTBA_TACHE_ID = $this->request->getPost('PTBA_TACHE_ID');
    $ENG_BUDGETAIRE = $this->request->getPost('ENG_BUDGETAIRE');
    $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');
    $COMMENTAIRE=$this->request->getPost('COMMENTAIRE');
    $CREDIT_VOTE=$this->request->getPost('montant_vote');  
    $TRANSFERTS_CREDITS=$this->request->getPost('get_trans');
    $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
    $TAUX_ECHANGE_ID=$this->request->getPost('TAUX_ECHANGE_ID'); 
    $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
    $ACTION_ID = $this->request->getPost('ACTION_ID');
    $COUS_ECHANGE= ($TYPE_MONTANT_ID==1) ? 1 : $this->request->getPost('COUS_ECHANGE');
    $COUS_ECHANGE=str_replace(" ", "", $COUS_ECHANGE);
    $UNITE=$this->request->getPost('UNITE');
    $UNITE=addslashes($UNITE);

    //montants exécutions
    $get_exec = "SELECT ANNEE_BUDGETAIRE_ID,TRIMESTRE_ID,ENG_BUDGETAIRE,ENG_BUDGETAIRE_DEVISE FROM execution_budgetaire WHERE 1 AND EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;
    $get_exec = 'CALL getTable("'.$get_exec.'");'; 
    $exec_one = $this->ModelPs->getRequeteOne($get_exec);
    $TRIMESTRE_ID = $exec_one['TRIMESTRE_ID'];

    //montants executions_taches
    $get_task = "SELECT MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_BUDGETAIRE_DEVISE FROM execution_budgetaire_execution_tache WHERE 1 AND EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID =".$EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID;
    $get_task = 'CALL getTable("'.$get_task.'");'; 
    $task_one = $this->ModelPs->getRequeteOne($get_task);

    $sous_task=$this->request->getPost('sous_act'); 
    $fini=0;
    $resultat_attend=0;
    $observ_resultat='';
    $QTE=0;
    if ($sous_task==1)
    {
      $fini=$this->request->getPost('fini'); 
      if ($fini==1)
      {
        $observ_resultat=$this->request->getPost('observ');
        $resultat_attend=$this->request->getPost('resultat_attend'); 
        $QTE=$this->request->getPost('QTE');
      }
      else
      {
        $QTE=$this->request->getPost('QTE');
      }
    }
    else
    {
      $resultat_attend=$this->request->getPost('QTE'); 
      $QTE=$this->request->getPost('QTE');     
    }

    $MONTANT_EN_DEVISE=0;        
    $DATE_COUT_DEVISE= null;

    if ($TYPE_MONTANT_ID!=1)
    {
      $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
      $MONTANT_EN_DEVISE=$this->request->getPost('MONTANT_EN_DEVISE');
      $DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');
      $ENG_BUDGETAIRE = floatval($COUS_ECHANGE) * floatval($MONTANT_EN_DEVISE);
      $ENG_BUDGETAIRE=number_format($ENG_BUDGETAIRE,0, ',', '');
    }
    
    $COMMENTAIRE = addslashes($COMMENTAIRE);
    $observ_resultat = addslashes($observ_resultat);

    $QTE = str_replace(".",",",$QTE);
    $resultat_attend = str_replace(".",",",$resultat_attend);
    $resultat_attend = addslashes($resultat_attend);

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

    //Update des montants dans exécution
    $eng_budg = (floatval($exec_one['ENG_BUDGETAIRE']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE'])) + floatval($ENG_BUDGETAIRE);
    $eng_budg_dev = (floatval($exec_one['ENG_BUDGETAIRE_DEVISE']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE_DEVISE'])) + floatval($MONTANT_EN_DEVISE);

    $columExec="ENG_BUDGETAIRE={$eng_budg},ENG_BUDGETAIRE_DEVISE={$eng_budg_dev}";
    $whereExec ="EXECUTION_BUDGETAIRE_ID =".$EXECUTION_BUDGETAIRE_ID;
    $insertIntoExec='execution_budgetaire';  
    $this->update_all_table($insertIntoExec,$columExec,$whereExec);

    //Update dans la table des ptba
    $MoneyRest  = 'SELECT ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T1,BUDGET_UTILISE_T2,BUDGET_UTILISE_T3,BUDGET_UTILISE_T4 FROM ptba_tache WHERE PTBA_TACHE_ID='.$PTBA_TACHE_ID.' AND ANNEE_BUDGETAIRE_ID='.$exec_one['ANNEE_BUDGETAIRE_ID'];
    $MoneyRest = "CALL `getTable`('" . $MoneyRest . "');";
    $RestPTBA = $this->ModelPs->getRequeteOne($MoneyRest);

    $apresEng = 0;
    $total_utilise = 0;

    if ($TRIMESTRE_ID==1 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$exec_one['ANNEE_BUDGETAIRE_ID']) 
    {
      $apresEng = (floatval($RestPTBA['BUDGET_RESTANT_T1']) + floatval($task_one['MONTANT_ENG_BUDGETAIRE'])) - floatval($ENG_BUDGETAIRE);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T1']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE']) + floatval($ENG_BUDGETAIRE) ;

      $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T1=".$apresEng.",BUDGET_UTILISE_T1=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
    else if ($TRIMESTRE_ID==2 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$exec_one['ANNEE_BUDGETAIRE_ID'])
    {
      $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T2']) + floatval($task_one['MONTANT_ENG_BUDGETAIRE']) - floatval($ENG_BUDGETAIRE);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T2']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE']) + floatval($ENG_BUDGETAIRE);

      $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T2=".$apresEng.",BUDGET_UTILISE_T2=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
    else if ($TRIMESTRE_ID==3 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$exec_one['ANNEE_BUDGETAIRE_ID'])
    {
      $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T3']) + floatval($task_one['MONTANT_ENG_BUDGETAIRE']) - floatval($ENG_BUDGETAIRE);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T3']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE']) + floatval($ENG_BUDGETAIRE);

      $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T3=".$apresEng.",BUDGET_UTILISE_T3=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
    else if ($TRIMESTRE_ID==4 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$exec_one['ANNEE_BUDGETAIRE_ID'])
    {
      $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T4']) + floatval($task_one['MONTANT_ENG_BUDGETAIRE']) - floatval($ENG_BUDGETAIRE);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T4']) - floatval($task_one['MONTANT_ENG_BUDGETAIRE']) + floatval($ENG_BUDGETAIRE);

      $whereptba ="PTBA_TACHE_ID = ".$PTBA_TACHE_ID;        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T4=".$apresEng.",BUDGET_UTILISE_T4=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }

    $columTask="PTBA_TACHE_ID={$PTBA_TACHE_ID},MONTANT_ENG_BUDGETAIRE={$ENG_BUDGETAIRE},MONTANT_ENG_BUDGETAIRE_DEVISE={$MONTANT_EN_DEVISE},UNITE='{$UNITE}',QTE='{$QTE}',EST_SOUS_TACHE={$sous_task},EST_FINI_TACHE={$fini},RESULTAT_ATTENDUS='{$resultat_attend}',OBSERVATION_RESULTAT='{$observ_resultat}',USER_ID={$USER_ID}";
    $whereTask ="EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID =".$EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID;
    $insertIntoTask='execution_budgetaire_execution_tache';  
    $this->update_all_table($insertIntoTask,$columTask,$whereTask);
    
    $output = array('status' => TRUE );
    return $this->response->setJSON($output);
  }

  function corrige_etape1($id=0)
  {

    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout'); 
    }       

    $infoAffiche  = 'SELECT tit_dec.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,tit_dec.ETAPE_DOUBLE_COMMANDE_ID,exec.EXECUTION_BUDGETAIRE_ID,suppl.NOTE_REFERENCE,det.COUR_DEVISE,det.DATE_COUR_DEVISE,exec.DEVISE_TYPE_ID,exec.COMMENTAIRE,exec.TYPE_ENGAGEMENT_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement tit_dec ON tit_dec.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=2 AND MD5(tit_dec.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) ="'.$id.'"';
    $infoAffiche = "CALL `getTable`('" .$infoAffiche."');";
    $data['infos']= $this->ModelPs->getRequeteOne($infoAffiche);
    //print_r($data['infos']);exit();

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['infos']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {

        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$data['infos']['ETAPE_DOUBLE_COMMANDE_ID'],' DESC_ETAPE_DOUBLE_COMMANDE DESC');
          $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

          $data['etape_correction'] =$titre['DESC_ETAPE_DOUBLE_COMMANDE'];

          $rej  = 'SELECT rej.DATE_TRANSMISSION,rej.MOTIF_REJET,rej.OBSERVATION FROM execution_budgetaire_tache_detail_histo rej WHERE rej.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = "'.$data['infos']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].'" ORDER BY EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID  DESC';
          $rej = "CALL `getTable`('" .$rej. "');";
          $get_rej= $this->ModelPs->getRequeteOne($rej);

          $data['rejet'] = (!empty($get_rej)) ? $get_rej['MOTIF_REJET'] : '' ; 

          $callpsreq = "CALL `getRequete`(?,?,?,?);";     
          $bind_date_histo = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,OBSERVATION,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['infos']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'],'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\','',$bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          //Récuperation de l'étape précedent
          $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID AS ETAPE_ID','execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['infos']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'],'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
          $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
          $etap_prev = $this->ModelPs->getRequeteOne($callpsreq, $bind_etap_prev);

            //get motif rejet de la table historique_raccrochage_operation_verification_motif 
          $motif_rejet  = "SELECT DISTINCT tpes.TYPE_ANALYSE_MOTIF_ID,tpes.DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif tpes JOIN execution_budgetaire_histo_operation_verification_motif motif ON motif.TYPE_ANALYSE_MOTIF_ID=tpes.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$data['infos']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']." AND ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_ID']."";
          $motif_rejetRqt = 'CALL getTable("' . $motif_rejet . '");';
          $data['get_histmotif']= $this->ModelPs->getRequete($motif_rejetRqt);

          $trans = 'SELECT DATE_TRANSMISSION FROM execution_budgetaire_tache_detail_histo WHERE ETAPE_DOUBLE_COMMANDE_ID='.$data['date_trans']['ETAPE_ID'].' AND EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ="'.$data['infos']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'].'"';
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

          $get_inst_exec = 'SELECT ptba.INSTITUTION_ID FROM execution_budgetaire_execution_tache task JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=task.PTBA_TACHE_ID WHERE task.EXECUTION_BUDGETAIRE_ID='.$data['infos']['EXECUTION_BUDGETAIRE_ID'].'  ORDER BY task.PTBA_TACHE_ID ASC';
          $get_inst_exec = "CALL `getTable`('" .$get_inst_exec. "');";
          $data['inst_exec'] = $this->ModelPs->getRequeteOne($get_inst_exec);


          $getMasse  = 'SELECT TYPE_ENGAGEMENT_ID,DESC_TYPE_ENGAGEMENT FROM type_engagement WHERE TYPE_ENGAGEMENT_ID NOT IN(1,4,5)  ORDER BY DESC_TYPE_ENGAGEMENT ASC';
          $getMasse = "CALL `getTable`('" . $getMasse . "');";
          $data['grande']= $this->ModelPs->getRequete($getMasse);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);

          $dataa=$this->converdate();
          $data['debut'] = $dataa['debut'];

          return view('App\Modules\double_commande_new\Views\Intro_Multi_Correct_View',$data);
        }  
      }
      return redirect('Login_Ptba/homepage'); 
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }  
  }
  
  function etape1_correction()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CORRECTION')!=1)
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
      'NOTE_REFERENCE' => [
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

      $id_exec_titr_dec = $this->request->getPost('id_exec_titr_dec');
      $id_titr_dec = $this->request->getPost('id_titr_dec');
      $EXECUTION_BUDGETAIRE_DETAIL_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
      $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
      $ETAPE_DOUBLE=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $NOTE_REFERENCE=$this->request->getPost('NOTE_REFERENCE');
      $NOTE_REFERENCE=addslashes($NOTE_REFERENCE);
      $COMMENTAIRE=$this->request->getPost('COMMENTAIRE');
      $COMMENTAIRE=addslashes($COMMENTAIRE);
      $TYPE_ENGAGEMENT_ID=$this->request->getPost('TYPE_ENGAGEMENT_ID');

      $TYPE_MONTANT_ID=$this->request->getPost('TYPE_MONTANT_ID');
      $DATE_COUT_DEVISE=$this->request->getPost('DATE_COUT_DEVISE');
      $COUR_DEVISE=$this->request->getPost('engagement_cous');

      $DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');

      $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE.'',' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
      $etape_suivante= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);
      $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
      $ETAPE_ID=$etape_suivante['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];

      //print_r($COMMENTAIRE);exit();

      //Update dans execution_budgetaire
      $whereExec='EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
      $insertExecec='execution_budgetaire';
      $columExec="COMMENTAIRE='{$COMMENTAIRE}', TYPE_ENGAGEMENT_ID={$TYPE_ENGAGEMENT_ID}";
      $this->update_all_table($insertExecec,$columExec,$whereExec);

      if($TYPE_MONTANT_ID != 1 && $DATE_COUT_DEVISE !== '')
      {
        //Update de la cour devise et date de cour devise dans tache_detail
        $whereDet ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;
        $insertIntoDet='execution_budgetaire_tache_detail';
        $columDet="COUR_DEVISE=".$COUR_DEVISE.", DATE_COUR_DEVISE='".$DATE_COUT_DEVISE."'";
        $this->update_all_table($insertIntoDet,$columDet,$whereDet);
      }
      
      //Update de la Note de reference dans info suppl
      $whereSuppl ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;
      $insertIntoSuppl='execution_budgetaire_tache_info_suppl';
      $columSuppl="NOTE_REFERENCE='".$NOTE_REFERENCE."'";  
      $this->update_all_table($insertIntoSuppl,$columSuppl,$whereSuppl);

      //Update dans titre décaissement
      $whereDec='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_titr_dec;
      $insertDec='execution_budgetaire_titre_decaissement';
      $columDec='ETAPE_DOUBLE_COMMANDE_ID='.$ETAPE_DOUBLE_COMMANDE_ID;
      $this->update_all_table($insertDec,$columDec,$whereDec);

      //Insertion dans historique
      $insertIntohist='execution_budgetaire_tache_detail_histo';
      $columhist="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION,DATE_RECEPTION";
      $datacolumshist=$id_titr_dec.",".$USER_ID.",".$ETAPE_ID.",'".$DATE_TRANSMISSION."','".$DATE_RECEPTION."'";
      $this->save_all_table($insertIntohist,$columhist,$datacolumshist);

      $data=['message' => "".lang('messages_lang.eng_succ').""];
      session()->setFlashdata('alert', $data);
      return redirect('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Fait');
    }
    else
    {
      return $this->corrige_etape1($id_exec_titr_dec);
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
    $ANNEE_BUDGETAIRE_ID=$this->get_annee_budgetaire();

    if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else 
    {
      return redirect('Login_Ptba/do_logout');
    }

    if ($TYPE_INSTITUTION_ID == 2)
    {
      $getTacheactivite = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE PAP_ACTIVITE_ID = {$id} AND ANNEE_BUDGETAIRE_ID={$ANNEE_BUDGETAIRE_ID} ORDER BY PTBA_TACHE_ID ASC";
    } 
    else if ($TYPE_INSTITUTION_ID == 1)
    {
      $getTacheactivite = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID = {$id} and ANNEE_BUDGETAIRE_ID={$ANNEE_BUDGETAIRE_ID} ORDER BY PTBA_TACHE_ID ASC";
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

  // trouver  tous montants   a partir de activite choisit 
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

    $task_id = $this->request->getPost('task_id');
    $mont_budget = $this->request->getPost('mont_budget');

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
    $budg_restant_annuel=0;

    if ($CODE_TRANCHE == 'T1') 
    {
      $MontantVote = floatval($MoneyActivite['T1']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T1']);
      if(!empty($task_id) && $task_id == $PTBA_TACHE_ID)
      {
        $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T1']);
      }

      $TRIMESTRE_ID=1;
      $qte=$MoneyActivite['QT1'];
      $budg_restant_annuel=$MoneyActivite['BUDGET_RESTANT_T1']+$MoneyActivite['BUDGET_RESTANT_T2']+$MoneyActivite['BUDGET_RESTANT_T3']+$MoneyActivite['BUDGET_RESTANT_T4'];
    }
    else if ($CODE_TRANCHE == 'T2') 
    {
      $MontantVote = floatval($MoneyActivite['T2']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T2']);

      if(!empty($task_id) && $task_id == $PTBA_TACHE_ID)
      {
        $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T2']);
      }
      $TRIMESTRE_ID=2;
      $qte=$MoneyActivite['QT2'];
      $budg_restant_annuel=$MoneyActivite['BUDGET_RESTANT_T2']+$MoneyActivite['BUDGET_RESTANT_T3']+$MoneyActivite['BUDGET_RESTANT_T4'];
    }
    else if ($CODE_TRANCHE == 'T3') 
    {
      $MontantVote = floatval($MoneyActivite['T3']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T3']);
      if(!empty($task_id) && $task_id == $PTBA_TACHE_ID)
      {
        $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T3']);
      }
      $TRIMESTRE_ID=3;
      $qte=$MoneyActivite['QT3'];
      $budg_restant_annuel=$MoneyActivite['BUDGET_RESTANT_T3']+$MoneyActivite['BUDGET_RESTANT_T4'];
    }
    else if ($CODE_TRANCHE == 'T4')
    {
      $MontantVote = floatval($MoneyActivite['T4']);
      $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T4']);
      if(!empty($task_id) && $task_id == $PTBA_TACHE_ID)
      {
        $MontantRestant = floatval($MoneyActivite['BUDGET_RESTANT_T4']);
      }
      $TRIMESTRE_ID=4;
      $qte=$MoneyActivite['QT4'];
      $budg_restant_annuel=$MoneyActivite['BUDGET_RESTANT_T4'];
    }

    $montantRec = $MontantVote;

    $precision1=$this->get_precision($MontantVote);
    $montantRec_vote = number_format($montantRec,$precision1,'.',' ');
    
    $precision2=$this->get_precision($MontantRestant);
    $montantReste_ptba = number_format($MontantRestant,$precision2,'.',' ');
    $montantReste=number_format($MontantRestant,$precision2,'.','');

    $TRIMESTRE_ID=$TRIMESTRE_ID;

    $reste_Engage= number_format($resteEng,4,'.',' ');
    $quantite = $qte;
    $unite=$MoneyActivite['UNITE'];    

    $BUDGET_ANNUEL_FORMAT=number_format($MoneyActivite['BUDGET_ANNUEL'],$this->get_precision($MoneyActivite['BUDGET_ANNUEL']),'.',' ');

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
      "BUDGET_ANNUEL_FORMAT"=>$BUDGET_ANNUEL_FORMAT,
      "budg_restant_annuel_formate"=>number_format($budg_restant_annuel,$this->get_precision($budg_restant_annuel),'.',' '),
      "budg_restant_annuel"=>number_format($budg_restant_annuel,$this->get_precision($budg_restant_annuel),'.','')
    );

    return $this->response->setJSON($output);
  }

  private function get_precision($value=0)
  {
    $string = strval($value);
    $number=explode('.',$string)[1] ?? '';
    $precision='';
    for($i=1;$i<=strlen($number);$i++)
    {
      $precision=$i;
    }
    if(!empty($precision)) 
    {
      return $precision;
    }
    else
    {
      return 0;
    }    
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

  

  public function valider_liste()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    $NOTE_REFERENCE = $this->request->getPost('NOTE_REFERENCE');
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');

    $get_tempo = "SELECT temp.EXECUTION_BUDGETAIRE_TACHE_TEMPO_ID,act.DESC_PAP_ACTIVITE,ptba_tache.DESC_TACHE,temp.QTE,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,temp.MONTANT_ENG_BUDGETAIRE,temp.MONTANT_ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,temp.COMMENTAIRE,temp.TAUX,temp.DATE_COUR_DEVISE FROM execution_budgetaire_tache_tempo temp JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=temp.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID=temp.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=temp.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=temp.DEVISE_TYPE_ID WHERE 1 AND temp.INSTITUTION_ID=".$INSTITUTION_ID." AND NOTE_REFERENCE='".$NOTE_REFERENCE."'";
    $get_tempo = 'CALL getTable("'.$get_tempo.'");';
    $tempo = $this->ModelPs->getRequete($get_tempo);
    $tempo_one = $this->ModelPs->getRequeteOne($get_tempo);
    
    if(!empty($tempo))
    {
      
      $cours_devise = $tempo_one['TAUX'];
      $date_devise = $tempo_one['DATE_COUR_DEVISE'];
      $output = array(
        'status' => TRUE, 
        'type_devise' => $tempo_one['DEVISE_TYPE_ID'],
        'cours_devise' => $cours_devise,
        'date_devise' => $date_devise
      );
      return $this->response->setJSON($output);
    }
    else
    {
      $output = array('status' => FALSE, 'msg_aucun'=> lang('messages_lang.labelle_et_aucun_element'));
      return $this->response->setJSON($output);
    }
  } 
}
?>