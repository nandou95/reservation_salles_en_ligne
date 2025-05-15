<?php
  /**
    * NDERAGAKURA Alain Charbel
    *Titre: Validation des Titre de decaisement cas salaire net et autre retenu 
    *Numero de telephone: (+257)62003522
    *Email: charbel@mediabox.bi
    *Date: 16 septembre,2024
    **/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;

  class Validation_TD_Salaire extends BaseController
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

    // visualiser l'interface de validation d'un titre de decaissement
    function vue_valid_titre_net($id=0)
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

      $infoAffiche  = 'SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID AS TITRE_ID,exec.EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ORDONNANCEMENT,exec.PAIEMENT,MONTANT_PAIEMENT,ETAPE_DOUBLE_COMMANDE_ID,TITRE_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT ,MOTIF_REFUS FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID WHERE md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) = "'.$id.'"';

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
            $titre = $this->getBindParms('DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'],' DESC_ETAPE_DOUBLE_COMMANDE DESC');
            $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);
            $data['etape_titre'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];

            $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['TITRE_ID'],'DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

            return view('App\Modules\double_commande_new\Views\Validation_TD_Salaire_Net_Add',$data);
          }
        }
        return redirect('Login_Ptba/homepage');
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }
    }

    // fonction pour enregistrer la validation d'un titre de decaissement
    function save_valid_titre_net()
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
        'BORDEREAU_TRANSMISSION' => [
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
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');

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
        $BORDEREAU_TRANSMISSION=$this->request->getPost('BORDEREAU_TRANSMISSION');
        $DATE_VALIDE_TITRE=$this->request->getPost('DATE_VALIDE_TITRE');
        $ETAPE_DOUBLE_COMMANDE_ID=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
        $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
        $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');

        $callpsreq = "CALL `getRequete`(?,?,?,?);";     
        $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,IS_CORRECTION,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
        $etape_suivante22= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);
        // print_r($etape_suivante22);die();

        $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
        $ETAPE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];

        $bord_exist  = 'SELECT BORDEREAU_TRANSMISSION_ID FROM execution_budgetaire_bordereau_transmission WHERE NUMERO_BORDEREAU_TRANSMISSION = "'.$BORDEREAU_TRANSMISSION.'" AND ID_ORIGINE_DESTINATION=3';
        $bord_exist = "CALL `getTable`('" .$bord_exist."');";
        $bord_exist= $this->ModelPs->getRequeteOne($bord_exist);

        $BORDEREAU_TRANSMISSION_ID=0;
        if(!empty($bord_exist['BORDEREAU_TRANSMISSION_ID']))
        {
          $BORDEREAU_TRANSMISSION_ID=$bord_exist['BORDEREAU_TRANSMISSION_ID'];
        }
        else
        {
          $insertIntoBord='execution_budgetaire_bordereau_transmission';
          $columBord="NUMERO_BORDEREAU_TRANSMISSION,ID_ORIGINE_DESTINATION,USER_ID,DATE_RECEPTION_BD,DATE_TRANSMISSION_BD";
          $datacolumsBord=$BORDEREAU_TRANSMISSION.",".$ETAPE_ID.",".$USER_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";
          $BORDEREAU_TRANSMISSION_ID=$this->save_all_table($insertIntoBord,$columBord,$datacolumsBord);
        }

        $insertIntoBonTitre='execution_budgetaire_bordereau_transmission_bon_titre';
        $columBonTitre="BORDEREAU_TRANSMISSION_ID,TYPE_DOCUMENT_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,NUMERO_DOCUMENT,USER_ID";
        $datacolumsBonTitre=$BORDEREAU_TRANSMISSION_ID.",2,".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",'".$BORDEREAU_TRANSMISSION."','".$USER_ID."'";
        $this->save_all_table($insertIntoBonTitre,$columBonTitre,$datacolumsBonTitre);

        $insertIntoOp='execution_budgetaire_tache_detail_histo';
        $columOp="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsOp=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_ID.",".$USER_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";
        $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

        $whereracc ="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $insertIntoracc='execution_budgetaire_titre_decaissement';
        $columracc="ETAPE_DOUBLE_COMMANDE_ID = ".$ETAPE_DOUBLE_COMMANDE_ID.",DATE_VALIDE_TITRE = '".$DATE_VALIDE_TITRE."'";
        $this->update_all_table($insertIntoracc,$columracc,$whereracc);

        $data=['message' => "".lang('messages_lang.valid_titre_dec').""];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_valide_td_net');
      }
      else
      {
        return $this->vue_valid_titre_net(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
      }
    }

    // visualiser l'interface de validation d'un TD Autr Ret
    function vue_valid_titre_autre($id=0)
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

      $infoAffiche  = 'SELECT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,MONTANT_PAIEMENT,ETAPE_DOUBLE_COMMANDE_ID,TITRE_DECAISSEMENT,benef.DESC_BENEFICIAIRE,td.COMPTE_CREDIT,AUTRES_RETENUS,MOTIF_REFUS FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID WHERE md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) = "'.$id.'"';

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
            $titre = $this->getBindParms('DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'],' DESC_ETAPE_DOUBLE_COMMANDE DESC');
            $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);
            $data['etape_titre'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];

            $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$data['info']['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'],'DATE_INSERTION DESC');
            $bind_date_histo = str_replace('\\','',$bind_date_histo);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

            $deja_paye="SELECT SUM(MONTANT_PAIEMENT) AS deja_paye FROM execution_budgetaire_titre_decaissement WHERE EXECUTION_BUDGETAIRE_ID=".$data['info']['EXECUTION_BUDGETAIRE_ID']." AND IS_TD_NET=1 AND ETAPE_DOUBLE_COMMANDE_ID IN (29,30)";
            $deja_paye = "CALL `getTable`('" .$deja_paye."');";
            $data['deja_paye']= $this->ModelPs->getRequeteOne($deja_paye)['deja_paye'];

            return view('App\Modules\double_commande_new\Views\Validation_TD_Salaire_Autre_Retenu_Add',$data);
          }
        }
        return redirect('Login_Ptba/homepage');
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }
    }

    // fonction pour enregistrer la validation d'un TD Autr Ret
    function save_valid_titre_autre()
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
        'BORDEREAU_TRANSMISSION' => [
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
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');

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
        $BORDEREAU_TRANSMISSION=$this->request->getPost('BORDEREAU_TRANSMISSION');
        $DATE_VALIDE_TITRE=$this->request->getPost('DATE_VALIDE_TITRE');
        $ETAPE_DOUBLE_COMMANDE_ID=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
        $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
        $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');

        $callpsreq = "CALL `getRequete`(?,?,?,?);";     
        $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,IS_CORRECTION,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
        $etape_suivante22= $this->ModelPs->getRequeteOne($callpsreq, $etape_suivante);

        $ETAPE_DOUBLE_COMMANDE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
        $ETAPE_ID=$etape_suivante22['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'];

        $bord_exist  = 'SELECT BORDEREAU_TRANSMISSION_ID FROM execution_budgetaire_bordereau_transmission WHERE NUMERO_BORDEREAU_TRANSMISSION = "'.$BORDEREAU_TRANSMISSION.'" AND ID_ORIGINE_DESTINATION=3';
        $bord_exist = "CALL `getTable`('" .$bord_exist."');";
        $bord_exist= $this->ModelPs->getRequeteOne($bord_exist);

        $BORDEREAU_TRANSMISSION_ID=0;
        if(!empty($bord_exist['BORDEREAU_TRANSMISSION_ID']))
        {
          $BORDEREAU_TRANSMISSION_ID=$bord_exist['BORDEREAU_TRANSMISSION_ID'];
        }
        else
        {
          $insertIntoBord='execution_budgetaire_bordereau_transmission';
          $columBord="NUMERO_BORDEREAU_TRANSMISSION,ID_ORIGINE_DESTINATION,USER_ID,DATE_RECEPTION_BD,DATE_TRANSMISSION_BD";
          $datacolumsBord=$BORDEREAU_TRANSMISSION.",".$ETAPE_ID.",".$USER_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";
          $BORDEREAU_TRANSMISSION_ID=$this->save_all_table($insertIntoBord,$columBord,$datacolumsBord);
        }

        $insertIntoBonTitre='execution_budgetaire_bordereau_transmission_bon_titre';
        $columBonTitre="BORDEREAU_TRANSMISSION_ID,TYPE_DOCUMENT_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,NUMERO_DOCUMENT,USER_ID";
        $datacolumsBonTitre=$BORDEREAU_TRANSMISSION_ID.",2,".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",'".$BORDEREAU_TRANSMISSION."','".$USER_ID."'";
        $this->save_all_table($insertIntoBonTitre,$columBonTitre,$datacolumsBonTitre);

        $insertIntoOp='execution_budgetaire_tache_detail_histo';
        $columOp="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsOp=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_ID.",".$USER_ID.",'".$DATE_RECEPTION."','".$DATE_TRANSMISSION."'";
        $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

        $whereracc ="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
        $insertIntoracc='execution_budgetaire_titre_decaissement';
        $columracc="ETAPE_DOUBLE_COMMANDE_ID = ".$ETAPE_DOUBLE_COMMANDE_ID.",DATE_VALIDE_TITRE = '".$DATE_VALIDE_TITRE."'";
        $this->update_all_table($insertIntoracc,$columracc,$whereracc);

        $data=['message' => "".lang('messages_lang.valid_titre_dec').""];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_valide_td_autre_retenu');
      }
      else
      {
        return $this->vue_valid_titre_net(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
      }
    }

    public function getBindParms($columnselect, $table, $where, $orderby)
    {
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
  }