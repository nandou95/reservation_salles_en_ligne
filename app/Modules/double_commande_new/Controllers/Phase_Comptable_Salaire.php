<?php
  /**Claude Niyongabo
    *Titre: Gestion du paiement des salaires par categories (Prise en charge, Etablissement des titres, Validation des titres,Signature des titres)
    *Numero de telephone: (+257) 69641375
    *Email: claude@mediabox.bi
    *Date: 5 septembre,2023
    * 
    **/
  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;
  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
  use Dompdf\Dompdf;
  // Phase_Comptable_Salaire
  class Phase_Comptable_Salaire extends BaseController
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

    //interface de prise en charge des salaires
  	public function prise_Charge($id='')
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
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

      if($this->session->get('SESSION_SUIVIE_PTBA_PRISE_CHARGE_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $dataa=$this->converdate();
      $TRIMESTRE_ID = $dataa['TRIMESTRE_ID'];
      //execution_budgetaire
      $bind_exec = $this->getBindParms("exec.EXECUTION_BUDGETAIRE_ID, `ANNEE_BUDGETAIRE_ID`, `TRIMESTRE_ID`, `NUMERO_BON_ENGAGEMENT`,  exec.LIQUIDATION, exec.LIQUIDATION_DEVISE, exec.ORDONNANCEMENT, exec.ORDONNANCEMENT_DEVISE, exec.PAIEMENT, exec.PAIEMENT_DEVISE,MOIS_ID,CATEGORIE_SALAIRE_ID,TYPE_SALAIRE_ID,det.DATE_ORDONNANCEMENT", "execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID", "md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'", "EXECUTION_BUDGETAIRE_ID ASC");
      $bind_exec =str_replace('\\', '', $bind_exec) ;
      $data['execution'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_exec);
      $histo = $this->getBindParms("DATE_INSERTION","execution_budgetaire_tache_detail_histo", "ETAPE_DOUBLE_COMMANDE_ID = 14 AND md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'","EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC");
      $histo =str_replace('\\', '', $histo) ;
      $histo= $this->ModelPs->getRequeteOne($callpsreq, $histo);
      $data['DATE_ORDONNANCEMENT']=$histo['DATE_INSERTION'];
      $data['execution']['CATEGORIE_SALAIRE_ID']=($data['execution']['CATEGORIE_SALAIRE_ID'])>0 ? $data['execution']['CATEGORIE_SALAIRE_ID'] : 0;
      $data['execution']['TYPE_SALAIRE_ID']=($data['execution']['TYPE_SALAIRE_ID'])>0 ? $data['execution']['TYPE_SALAIRE_ID'] : 0;
      $data['execution']['MOIS_ID']=($data['execution']['MOIS_ID'])>0 ? $data['execution']['MOIS_ID'] : 0;

     //etape actuelle
      $ETAPE_DOUBLE_COMMANDE_ID=19;

      $data['etape_actuel']=$ETAPE_DOUBLE_COMMANDE_ID;
      
      $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_ID DESC');
      $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

      $data['etape1'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];
      $getCat  = 'SELECT `CATEGORIE_SALAIRE_ID`, `DESC_CATEGORIE_SALAIRE` FROM `categorie_salaire` WHERE 1 AND CATEGORIE_SALAIRE_ID='.$data['execution']['CATEGORIE_SALAIRE_ID'];
      $getCat = "CALL getTable('" .$getCat. "');";
      $data['categ_salaire'] = $this->ModelPs->getRequeteOne($getCat);


      $sqlmois="SELECT `MOIS_ID`, `DESC_MOIS`, `CODE_MOIS`, `TRIMESTRE_ID`, `DEBUT_MOIS`, `FIN_MOIS` FROM mois WHERE MOIS_ID=".$data['execution']['MOIS_ID'];
      $sqlmois = "CALL getTable('".$sqlmois. "');";
      $data['mois'] = $this->ModelPs->getRequeteOne($sqlmois);

      $sqltypes_salaire="SELECT `TYPE_SALAIRE_ID`, `DESC_TYPE_SALAIRE` FROM `type_salairie` WHERE TYPE_SALAIRE_ID=".$data['execution']['TYPE_SALAIRE_ID'];
      $sqltypes_salaire = "CALL getTable('".$sqltypes_salaire. "');";
      $data['types_salaire'] = $this->ModelPs->getRequeteOne($sqltypes_salaire);

      $sql_controles="SELECT `ID_CONTROLE_COMPTABLE`, `DESC_CONTROLE_COMPTABLE` FROM `controles_comptable` WHERE 1 ORDER BY DESC_CONTROLE_COMPTABLE ASC";
      $sql_controles = "CALL getTable('".$sql_controles. "');";
      $data['controles_comptables'] = $this->ModelPs->getRequete($sql_controles);

      return view('App\Modules\double_commande_new\Views\Phase_Comptable_Salaire_PC_View',$data);
    }

    // Enregistrer 
    public function save_prise_Charge()
    {
      if($this->session->get('SESSION_SUIVIE_PTBA_PRISE_CHARGE_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $ID_CONTROLE_COMPTABLE = $this->request->getPost('ID_CONTROLE_COMPTABLE');
      $MOTIF_REFUS = $this->request->getPost('MOTIF_REFUS');
      $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
      $ETAPE_ACTUELLE_ID=$this->request->getPost('ETAPE_ACTUELLE_ID');

      $rules=[
        'ID_CONTROLE_COMPTABLE' => [
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
          ]
        ]
      ];

      $this->validation->setRules($rules);
      if($this->validation->withRequest($this->request)->run())
      {
        $callpsreq = "CALL `getRequete`(?,?,?,?);";
        $ID_CONTROLE_COMPTABLE =explode(',', $ID_CONTROLE_COMPTABLE);

        foreach ($ID_CONTROLE_COMPTABLE as $key ) 
        {
          $table='execution_budgetaire_controles_comptable';
          $columsinsert="EXECUTION_BUDGETAIRE_ID,ID_CONTROLE_COMPTABLE";
          $datatoinsert=$EXECUTION_BUDGETAIRE_ID.",".$key;
          $this->save_all_table($table,$columsinsert,$datatoinsert);
        }

        //execution_budgetaire
        $bind_exec = $this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID, `NUMERO_BON_ENGAGEMENT`,  exec.LIQUIDATION, exec.ORDONNANCEMENT, exec.PAIEMENT,det.DATE_ORDONNANCEMENT', 'execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID', 'exec.EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID, 'EXECUTION_BUDGETAIRE_ID ASC');
        $execution= $this->ModelPs->getRequeteOne($callpsreq, $bind_exec);

        $ORDONNANCEMENT=($execution['ORDONNANCEMENT'])>0 ? $execution['ORDONNANCEMENT'] : 0;
       
        $PAIEMENT=$ORDONNANCEMENT;
        $sql_etap= "SELECT `ETAPE_DOUBLE_COMMANDE_CONFIG_ID`, `ETAPE_DOUBLE_COMMANDE_ACTUEL_ID`, `ETAPE_DOUBLE_COMMANDE_SUIVANT_ID` FROM `execution_budgetaire_etape_double_commande_config` WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=".$ETAPE_ACTUELLE_ID." AND IS_SALAIRE=1";
        $etap_suivante = "CALL getTable('" .$sql_etap. "');";
        $etap_suivante= $this->ModelPs->getRequeteOne($etap_suivante);

        $whereDet ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID;      
        $table_toUpdate='execution_budgetaire_titre_decaissement';
        // $table_toUpdate2='execution_budgetaire';
        // $table_toUpdate3='execution_budgetaire_execution_tache';

        $columDet="ETAPE_DOUBLE_COMMANDE_ID=".$etap_suivante['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'].",MOTIF_REFUS='".$MOTIF_REFUS."'";

        $columDet2="PAIEMENT=".$PAIEMENT."";
        $columDet3="MONTANT_PAIEMENT=".$PAIEMENT."";

        $this->update_all_table($table_toUpdate,$columDet,$whereDet);
        // $this->update_all_table($table_toUpdate2,$columDet2,$whereDet);
        // $this->update_all_table($table_toUpdate3,$columDet3,$whereDet);

        $response = [
          'message' => '<font style="color:green;size:2px;">'.lang('messages_lang.Enregistrer_succes_msg').'</font>'
        ];
        return $this->response->setJSON($response);

      }
      else
      {
        $errors = []; 
        foreach ($rules as $field => $rule)
        {
          $error = $this->validation->getError($field);
          if ($error !== null)
          {
            $errors[$field] = $error;
          }
        }
        $response = [
          'errors' => $errors
        ];
        return $this->response->setJSON($response);
      }
    }

    // Etablissement TD
    function etablir_titre($id='')
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }
       if($this->session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_NET') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $dataa=$this->converdate();
      
      $TRIMESTRE_ID = $dataa['TRIMESTRE_ID'];

      //execution_budgetaire
      $bind_exec = $this->getBindParms("td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, exec.EXECUTION_BUDGETAIRE_ID, `ANNEE_BUDGETAIRE_ID`, `TRIMESTRE_ID`, exec.LIQUIDATION, exec.LIQUIDATION_DEVISE, exec.ORDONNANCEMENT, exec.ORDONNANCEMENT_DEVISE, exec.PAIEMENT, exec.PAIEMENT_DEVISE,MOIS_ID,CATEGORIE_SALAIRE_ID,TYPE_SALAIRE_ID,det.DATE_ORDONNANCEMENT,NET", "execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID", "md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'", "1");
        $bind_exec =str_replace('\\', '', $bind_exec) ;
      $data['execution'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_exec);
      $data['execution']['CATEGORIE_SALAIRE_ID']=($data['execution']['CATEGORIE_SALAIRE_ID'])>0 ? $data['execution']['CATEGORIE_SALAIRE_ID'] : 0;
      $data['execution']['TYPE_SALAIRE_ID']=($data['execution']['TYPE_SALAIRE_ID'])>0 ? $data['execution']['TYPE_SALAIRE_ID'] : 0;
      $data['execution']['MOIS_ID']=($data['execution']['MOIS_ID'])>0 ? $data['execution']['MOIS_ID'] : 0;

      //etape actuelle
      $ETAPE_DOUBLE_COMMANDE_ID=20;

      $data['etape_actuel']=$ETAPE_DOUBLE_COMMANDE_ID;
      
      $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_ID DESC');
      $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

      $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_ID DESC');
      $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

      $data['etape1'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];
      $getCat  = 'SELECT `CATEGORIE_SALAIRE_ID`, `DESC_CATEGORIE_SALAIRE` FROM `categorie_salaire` WHERE 1 AND CATEGORIE_SALAIRE_ID='.$data['execution']['CATEGORIE_SALAIRE_ID'];
      $getCat = "CALL getTable('" .$getCat. "');";
      $data['categ_salaire'] = $this->ModelPs->getRequeteOne($getCat);

      $sqlmois="SELECT `MOIS_ID`, `DESC_MOIS`, `CODE_MOIS`, `TRIMESTRE_ID`, `DEBUT_MOIS`, `FIN_MOIS` FROM mois WHERE MOIS_ID=".$data['execution']['MOIS_ID'];
      $sqlmois = "CALL getTable('".$sqlmois. "');";
      $data['mois'] = $this->ModelPs->getRequeteOne($sqlmois);

      $sqltypes_salaire="SELECT `TYPE_SALAIRE_ID`, `DESC_TYPE_SALAIRE` FROM `type_salairie` WHERE TYPE_SALAIRE_ID=".$data['execution']['TYPE_SALAIRE_ID'];
      $sqltypes_salaire = "CALL getTable('".$sqltypes_salaire. "');";
      $data['types_salaire'] = $this->ModelPs->getRequeteOne($sqltypes_salaire);

      $sql_controles="SELECT `ID_CONTROLE_COMPTABLE`, `DESC_CONTROLE_COMPTABLE` FROM `controles_comptable` WHERE 1 ORDER BY DESC_CONTROLE_COMPTABLE ASC";
      $sql_controles = "CALL getTable('".$sql_controles. "');";
      $data['controles_comptables'] = $this->ModelPs->getRequete($sql_controles);

      $sqlben="SELECT ben.BENEFICIAIRE_TITRE_ID, DESC_BENEFICIAIRE FROM `beneficiaire_des_titres` ben JOIN execution_budgetaire_salaire_beneficiaire exec_ben ON ben.BENEFICIAIRE_TITRE_ID= exec_ben.BENEFICIAIRE_TITRE_ID WHERE exec_ben.EXECUTION_BUDGETAIRE_ID=".$data['execution']['EXECUTION_BUDGETAIRE_ID'];
      $sqlben = "CALL getTable('".$sqlben. "');";
      $data['beneficiaires'] = $this->ModelPs->getRequete($sqlben);

      return view('App\Modules\double_commande_new\Views\Phase_Comptable_Salaire_Etablir_TD_View',$data);
    }

    // Enregistrer Etablissement TD, $type définit le type de titre de decaissement  $type=1: salaire net ; $type=2: Autre retenus
    public function save_edition_TD($type=0)
    {
      $NUMERO_TD = $this->request->getPost('NUMERO_TD');
      $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
      $ETAPE_ACTUELLE_ID=$this->request->getPost('ETAPE_ACTUELLE_ID');
      $MOTIF_DECAISS = $this->request->getPost('MOTIF_DECAISS');
      $BENEFICIAIRE_TITRE_ID=$this->request->getPost('BENEFICIAIRE_TITRE_ID');
      $NET = $this->request->getPost('NET');
      $MONTANT_RESTANT=$this->request->getPost('MONTANT_RESTANT');

      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');

      
      if ($type==1)
      {
        //ETABLISSEMENT DES TITRES CAS DES SALAIRES NET
        $session  = \Config\Services::session();
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
        $rules=[
          'NUMERO_TD' => [
            'rules' => 'required',
            'errors' => [
              'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
            ]
          ]
        ];

        $this->validation->setRules($rules);
        if($this->validation->withRequest($this->request)->run())
        {
          $PATH_NUMERO_TD = $this->request->getPost('PATH_NUMERO_TD');
          $PATH_NUMERO_TD=$this->uploadFile('PATH_NUMERO_TD','double_commande_new',$PATH_NUMERO_TD);
          $montant_paiement="SELECT PAIEMENT FROM `execution_budgetaire` WHERE EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;
          $montant_paiement = "CALL getTable('" .$montant_paiement. "');";
          $montant_paiement= $this->ModelPs->getRequeteOne($montant_paiement);

          $sql_etap= "SELECT `ETAPE_DOUBLE_COMMANDE_CONFIG_ID`, `ETAPE_DOUBLE_COMMANDE_ACTUEL_ID`, `ETAPE_DOUBLE_COMMANDE_SUIVANT_ID` FROM `execution_budgetaire_etape_double_commande_config` WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=".$ETAPE_ACTUELLE_ID." AND IS_SALAIRE=1";
          $etap_suivante = "CALL getTable('" .$sql_etap. "');";
          $etap_suivante= $this->ModelPs->getRequeteOne($etap_suivante);
         
          // $table_toUpdate3='execution_budgetaire_execution_tache';
          $whereDet ="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;

          $table_toUpdate='execution_budgetaire_titre_decaissement';
          $columDet="ETAPE_DOUBLE_COMMANDE_ID=".$etap_suivante['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'].",MOTIF_REFUS='".$MOTIF_DECAISS."',MONTANT_PAIEMENT=".$NET.", BENEFICIAIRE_TITRE_ID=".$BENEFICIAIRE_TITRE_ID." ,TITRE_DECAISSEMENT='".$NUMERO_TD."',PATH_TITRE_DECAISSEMENT='".$PATH_NUMERO_TD."'";
          $this->update_all_table($table_toUpdate,$columDet,$whereDet);

          $whereDet ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID; 
          $TOTAL_PAIEMENT=intval($NET)+intval($montant_paiement['PAIEMENT']);
          $table_toUpdate2='execution_budgetaire';
          $columDet2="PAIEMENT=".$TOTAL_PAIEMENT."";
          $this->update_all_table($table_toUpdate2,$columDet2,$whereDet);

          //Historique
          $tableinsert='execution_budgetaire_tache_detail_histo';
          $columsinsert="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,OBSERVATION";

          $datatoinsert=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$user_id.",".$etap_suivante['ETAPE_DOUBLE_COMMANDE_ACTUEL_ID'].",'".$MOTIF_DECAISS."'";
          $this->save_all_table($tableinsert,$columsinsert,$datatoinsert);

          $response = [
            'message' => '<font style="color:green;size:2px;">'.lang('messages_lang.Enregistrer_succes_msg').'</font>'
          ];
          return $this->response->setJSON($response);
        }
        else
        {
          $errors = []; 
          foreach ($rules as $field => $rule) 
          {
            $error = $this->validation->getError($field);
            if ($error !== null) 
            {
              $errors[$field] = $error;
            }
          }

          $response = [
            'errors' => $errors
          ];
          return $this->response->setJSON($response);
        }
      }
      else if($type==2)
      {        
        //ETABLISSEMENT DES TITRES CAS AUTRES RETENUS

        // $req="SELECT `EXECUTION_BUDGETAIRE_DETAIL_ID`, `EXECUTION_BUDGETAIRE_ID` FROM `execution_budgetaire_tache_detail` WHERE EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;
        // $det = "CALL getTable('" .$req. "');";
        // $det= $this->ModelPs->getRequeteOne($det);
        $PATH_NUMERO_TD=$this->request->getPost('PATH_NUMERO_TD');
        $NUMERO_TD=$this->request->getPost('NUMERO_TD');
        $PATH_NUMERO_TD=$this->uploadFile('PATH_NUMERO_TD','double_commande_new',$PATH_NUMERO_TD);
        $BANQUE_ID=$this->request->getPost('BANQUE_ID');
        $COMPTE_CREDIT=$this->request->getPost('COMPTE_CREDIT');
        $COMPTE_CREDIT=!empty($COMPTE_CREDIT)?$COMPTE_CREDIT:'NULL';

        $sql_etap= "SELECT `ETAPE_DOUBLE_COMMANDE_CONFIG_ID`, `ETAPE_DOUBLE_COMMANDE_ACTUEL_ID`, `ETAPE_DOUBLE_COMMANDE_SUIVANT_ID` FROM `execution_budgetaire_etape_double_commande_config` WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=".$ETAPE_ACTUELLE_ID." AND IS_SALAIRE=1";
        $etap_suivante = "CALL getTable('" .$sql_etap. "');";
        $etap_suivante= $this->ModelPs->getRequeteOne($etap_suivante);

        if(empty($etap_suivante))
        {
          return redirect('Login_Ptba/do_logout');
        }

        $callpsreq = "CALL `getRequete`(?,?,?,?);";
     
        //elements stockés dans la table tempo
        // $bind_chart_name = $this->getBindParms('EXECUTION_BUDGETAIRE_TEMPO_DECAISSEMENT_ID,MONTANT_DECAISS, BENEFICIAIRE_TITRE_ID, NUMERO_TD,PATH_NUMERO_TD, MOTIF_DECAISS','execution_budgetaire_tempo_titre_decaissement','EXECUTION_BUDGETAIRE_ID="'.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'"','EXECUTION_BUDGETAIRE_TEMPO_DECAISSEMENT_ID ASC');
        //  $bind_chart_name =str_replace('\\', '', $bind_chart_name) ;
        // $tempo_list= $this->ModelPs->getRequete($callpsreq, $bind_chart_name);
        $date_actuel=date('Y-m-d H:i:s');
        // $TOTAL_DEC=0;
        // foreach ($tempo_list as $key) 
        // { 
          $tableinsert='execution_budgetaire_titre_decaissement';
          $columsinsert="TITRE_DECAISSEMENT='".$NUMERO_TD."',ETAPE_DOUBLE_COMMANDE_ID=".$etap_suivante['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'].",PATH_TITRE_DECAISSEMENT='".$PATH_NUMERO_TD."',MOTIF_DECAISSEMENT='".$MOTIF_DECAISS."',BANQUE_ID=".$BANQUE_ID.",COMPTE_CREDIT='".$COMPTE_CREDIT."',DATE_ELABORATION_TD='".$date_actuel."'";

          $where1="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
          $this->update_all_table($tableinsert,$columsinsert,$where1);
          // $TOTAL_DEC=$TOTAL_DEC+$key->MONTANT_DECAISS;
        // }

        // $whereDet ="EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID; 
        // $TOTAL_DEC=intval($TOTAL_DEC);
        // $MONTANT_RESTANT=intval($MONTANT_RESTANT)-$TOTAL_DEC;
        // $table_toUpdate2='execution_budgetaire';
        // $columDet2="AUTRES_RETENUS=".$MONTANT_RESTANT."";
        // $this->update_all_table($table_toUpdate2,$columDet2,$whereDet);

        // supprimer ls elements de la table tmpo 
        // $sqldelete="DELETE FROM execution_budgetaire_tempo_titre_decaissement WHERE EXECUTION_BUDGETAIRE_ID =".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID." ";
        // $sqldelete = "CALL getTable('" .$sqldelete. "');";
        // $sqldelete= $this->ModelPs->getRequeteOne($sqldelete);

      
        $response = [
          'message' => 1
        ];
        return $this->response->setJSON($response);
      }

      // redirect('double_commande_new/Paiement_Salaire_Liste/vue_prise_charge');
    }

    function etablir_titre_retenu($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }
      if($this->session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_RETENUS') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $dataa=$this->converdate();
      
      $TRIMESTRE_ID = $dataa['TRIMESTRE_ID'];

        //execution_budgetaire
      $bind_exec = $this->getBindParms("td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID, exec.EXECUTION_BUDGETAIRE_ID, `ANNEE_BUDGETAIRE_ID`, `TRIMESTRE_ID`, exec.LIQUIDATION, exec.ORDONNANCEMENT, exec.AUTRES_RETENUS,MOIS_ID,CATEGORIE_SALAIRE_ID,TYPE_SALAIRE_ID,det.DATE_ORDONNANCEMENT,NET,DESC_BENEFICIAIRE,MOTIF_PAIEMENT,MONTANT_PAIEMENT", "execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN beneficiaire_des_titres titr ON titr.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID", "md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'", "1");
      $bind_exec =str_replace('\\', '', $bind_exec) ;
      $data['execution'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_exec);

      //deja fais autres retenus
      $autres_ret="SELECT SUM(MONTANT_PAIEMENT) as MONTANT_PAIEMENT FROM execution_budgetaire_titre_decaissement WHERE EXECUTION_BUDGETAIRE_ID=".$data['execution']['EXECUTION_BUDGETAIRE_ID']." AND IS_TD_NET=1";
      $autres_ret = "CALL getTable('".$autres_ret. "');";
      $data['autres_ret'] = $this->ModelPs->getRequeteOne($autres_ret);

      $data['execution']['CATEGORIE_SALAIRE_ID']=($data['execution']['CATEGORIE_SALAIRE_ID'])>0 ? $data['execution']['CATEGORIE_SALAIRE_ID'] : 0;
      $data['execution']['TYPE_SALAIRE_ID']=($data['execution']['TYPE_SALAIRE_ID'])>0 ? $data['execution']['TYPE_SALAIRE_ID'] : 0;
      $data['execution']['MOIS_ID']=($data['execution']['MOIS_ID'])>0 ? $data['execution']['MOIS_ID'] : 0;

     //etape actuelle
      $ETAPE_DOUBLE_COMMANDE_ID=20;

      $data['etape_actuel']=$ETAPE_DOUBLE_COMMANDE_ID;
      
      $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_ID DESC');
      $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

      $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$ETAPE_DOUBLE_COMMANDE_ID,' ETAPE_DOUBLE_COMMANDE_ID DESC');
      $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);

      $data['etape1'] = $titre['DESC_ETAPE_DOUBLE_COMMANDE'];
      $getCat  = 'SELECT `CATEGORIE_SALAIRE_ID`, `DESC_CATEGORIE_SALAIRE` FROM `categorie_salaire` WHERE 1 AND CATEGORIE_SALAIRE_ID='.$data['execution']['CATEGORIE_SALAIRE_ID'];
      $getCat = "CALL getTable('" .$getCat. "');";
      $data['categ_salaire'] = $this->ModelPs->getRequeteOne($getCat);

      $sqlmois="SELECT `MOIS_ID`, `DESC_MOIS`, `CODE_MOIS`, `TRIMESTRE_ID`, `DEBUT_MOIS`, `FIN_MOIS` FROM mois WHERE MOIS_ID=".$data['execution']['MOIS_ID'];
      $sqlmois = "CALL getTable('".$sqlmois. "');";
      $data['mois'] = $this->ModelPs->getRequeteOne($sqlmois);

      $sqltypes_salaire="SELECT `TYPE_SALAIRE_ID`, `DESC_TYPE_SALAIRE` FROM `type_salairie` WHERE TYPE_SALAIRE_ID=".$data['execution']['TYPE_SALAIRE_ID'];
      $sqltypes_salaire = "CALL getTable('".$sqltypes_salaire. "');";
      $data['types_salaire'] = $this->ModelPs->getRequeteOne($sqltypes_salaire);

      $sql_controles="SELECT `ID_CONTROLE_COMPTABLE`, `DESC_CONTROLE_COMPTABLE` FROM `controles_comptable` WHERE 1 ORDER BY DESC_CONTROLE_COMPTABLE ASC";
      $sql_controles = "CALL getTable('".$sql_controles. "');";
      $data['controles_comptables'] = $this->ModelPs->getRequete($sql_controles);

      $sqlben="SELECT ben.BENEFICIAIRE_TITRE_ID, DESC_BENEFICIAIRE FROM `beneficiaire_des_titres` ben JOIN execution_budgetaire_salaire_beneficiaire exec_ben ON ben.BENEFICIAIRE_TITRE_ID= exec_ben.BENEFICIAIRE_TITRE_ID WHERE exec_ben.EXECUTION_BUDGETAIRE_ID=".$data['execution']['EXECUTION_BUDGETAIRE_ID'];
      $sqlben = "CALL getTable('".$sqlben. "');";
      $data['beneficiaires'] = $this->ModelPs->getRequete($sqlben);

      $histo = $this->getBindParms("DATE_INSERTION","execution_budgetaire_tache_detail_histo", "ETAPE_DOUBLE_COMMANDE_ID = 14 AND md5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'","EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC");
      $histo =str_replace('\\', '', $histo) ;
      $histo= $this->ModelPs->getRequeteOne($callpsreq, $histo);
      $data['DATE_ORDONNANCEMENT']=$data['execution']['DATE_ORDONNANCEMENT'];

      $banque = $this->getBindParms("BANQUE_ID,NOM_BANQUE","banque", "1","NOM_BANQUE DESC");
      $banque =str_replace('\\', '', $banque) ;
      $data['banque']= $this->ModelPs->getRequete($callpsreq, $banque);

      return view('App\Modules\double_commande_new\Views\Phase_Comptable_Salaire_Etablir_TD2_View',$data);
    }

    // insert into cart
    public function insert_tab_tempo()
    {
      $session  = \Config\Services::session();
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba');
      }
       if($this->session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_RETENUS') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $BENEFICIAIRE_TITRE_ID = $this->request->getPost('BENEFICIAIRE_TITRE_ID');
      $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');
       $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
      $NUMERO_TD= $this->request->getPost('NUMERO_TD');
      $PATH_NUMERO_TD = $this->request->getFile('PATH_NUMERO_TD');

      $PATH_NUMERO_TD=$this->uploadFile('PATH_NUMERO_TD','double_commande_new',$PATH_NUMERO_TD);

      $MOTIF_DECAISS = $this->request->getPost('MOTIF_DECAISS');
     
       $MONTANT_DECAISS=str_replace(' ','',$this->request->getPost("MONTANT_DECAISS"));

      $NUMERO_TD=str_replace("'","\'",$NUMERO_TD);
      $MOTIF_DECAISS=str_replace("'","\'",$MOTIF_DECAISS);
      // Ici la colonne EXECUTION_BUDGETAIRE_ID représente EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID 
      $columsinsert="EXECUTION_BUDGETAIRE_ID,MONTANT_DECAISS,BENEFICIAIRE_TITRE_ID,NUMERO_TD,PATH_NUMERO_TD,MOTIF_DECAISS";
      $elements1 = explode(',', $columsinsert);
      $count1 = count($elements1);

      $datatoinsert= "".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",'".$MONTANT_DECAISS."','" . $BENEFICIAIRE_TITRE_ID . "','" . $NUMERO_TD . "','" . $PATH_NUMERO_TD . "','".$MOTIF_DECAISS."'";
     
       // Enregistrer dans la table tempo
      $table='execution_budgetaire_tempo_titre_decaissement';
      $this->save_all_table($table,$columsinsert,$datatoinsert);

      $output = array('status' => 1);
      return $this->response->setJSON($output);
    }

    //afficher ds elements dispo dans cart pour un $EXECUTION_BUDGETAIRE_ID donné
    public function afficher_cart($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      // Metttre dans l cart 
      $cart = \Config\Services::cart();
      $cart->destroy();
       if($this->session->get('SESSION_SUIVIE_PTBA_PRISE_CHARGE_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $bind_chart_name = $this->getBindParms('EXECUTION_BUDGETAIRE_TEMPO_DECAISSEMENT_ID,MONTANT_DECAISS, BENEFICIAIRE_TITRE_ID, NUMERO_TD,PATH_NUMERO_TD, MOTIF_DECAISS','execution_budgetaire_tempo_titre_decaissement','EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.'','EXECUTION_BUDGETAIRE_TEMPO_DECAISSEMENT_ID ASC');
      $tempo_list= $this->ModelPs->getRequete($callpsreq, $bind_chart_name);

       $MONTANT_DECAISSE=0;
      foreach($tempo_list as $value)
      {
        $file_data=array(
          'id'=>uniqid(),
          'qty'=>1,
          'price'=>1,
          'name'=>'CI',
          'MONTANT_DECAISS'=>number_format($value->MONTANT_DECAISS,0,' ',' '),
          'BENEFICIAIRE_TITRE_ID'=>$value->BENEFICIAIRE_TITRE_ID,
          'NUMERO_TD'=>$value->NUMERO_TD,
          'PATH_NUMERO_TD'=>$value->PATH_NUMERO_TD,
          'MOTIF_DECAISS'=>$value->MOTIF_DECAISS,
          'EXECUTION_BUDGETAIRE_TEMPO_DECAISSEMENT_ID'=>$value->EXECUTION_BUDGETAIRE_TEMPO_DECAISSEMENT_ID,
          'typecartitem'=>'FILECI'
        );

        $cart->insert($file_data);
        $MONTANT_DECAISSE=$MONTANT_DECAISSE+$value->MONTANT_DECAISS;
      }
      $val=count($cart->contents());

      $html="";
      $j=1;
      $i=0;

      $html.='
      <table class="table table-striped">
      <thead class="table-dark">
      <tr>
      <th>#</th>
      <th>MONTANT</th>
      <th>BENEFICIAIRE</th>
      <th>NUMERO&nbsp;TD</th>
      <th>TITRE&nbsp;DECAISSEMENT</th>
      <th>MOTIF&nbsp;DECAISSEMENT</th>
      <th>OPTIONS</th>
      </tr>
      </thead>
      <tbody>';
      $i=0;         

      foreach ($cart->contents() as $items)
      {
        if (preg_match('/FILECI/',$items['typecartitem']))
        {
          $bind_chart_name = $this->getBindParms('`BENEFICIAIRE_TITRE_ID`, `DESC_BENEFICIAIRE`','beneficiaire_des_titres','BENEFICIAIRE_TITRE_ID='.$items['BENEFICIAIRE_TITRE_ID'].'','BENEFICIAIRE_TITRE_ID ASC');
          $ben= $this->ModelPs->getRequeteOne($callpsreq, $bind_chart_name);

          $i++;
          $html.='<tr>
          <td>'.$j.'</td>

          <td>'.$items['MONTANT_DECAISS'].'</td>
          <td>'.$ben['DESC_BENEFICIAIRE'].'</td>
          <td>'.$items['NUMERO_TD'].'</td>
          <td><a title="Afficher" onclick=><i class="fa fa-file-pdf" style="font-size:14px"></i></a></td>
          <td>'.$items['MOTIF_DECAISS'].'</td>
          <td>
          <a href="javascript:void(0)" onclick="delete_Cart('.$items['EXECUTION_BUDGETAIRE_TEMPO_DECAISSEMENT_ID'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
          </td>

          </tr>';  
        }
        $j++;
        $i++;
      }

      $html.=' </tbody>
      </table>';
      if ($i>0) 
      {
        $output = array('status' => TRUE, 'cart'=>$html,'MONTANT_DECAISSE'=>$MONTANT_DECAISSE);
        return $this->response->setJSON($output);
      }
      else
      {
        $html= '';
        $output = array('status' => TRUE, 'cart'=>$html);
        return $this->response->setJSON($output);
      }
    }

    // Supprimer dans un element d'un panier
    public function delete_InCart($ID_DELETE)
    {
      $session  = \Config\Services::session();
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba');
      }
       if($this->session->get('SESSION_SUIVIE_PTBA_ETABLISSEMENT_TD_RETENUS') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $db = db_connect(); 
      $data=$this->urichk();
      $callpsreq = "CALL `getRequete`(?,?,?,?);";


      $critere ="EXECUTION_BUDGETAIRE_TEMPO_DECAISSEMENT_ID=" .$ID_DELETE;
      $table="execution_budgetaire_tempo_titre_decaissement";
      $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
      $deleteRequete = "CALL `deleteData`(?,?);";
      $this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);

      $output = array('status' => 1);
      return $this->response->setJSON($output);
    }

    /* Debut signature du titre par dir comptable*/
    public function signature_titre_dir_compt($id)
    {
      // $id=md5($id);
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if (empty($user_id)) {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DIR_COMPT_SALAIRE') !=1)
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
            $bindparamss =$this->getBindParms('det.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,MONTANT_DECAISSEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_ORDONNANCEMENT,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_ELABORATION_TD','execution_budgetaire_tache_detail det JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID  JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID','md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
            $bindparams34 = str_replace("\\", "", $bindparamss);
            $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

            //récuperer l'étape à corriger
            $step_correct = $this->getBindParms('ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR', 'budgetaire_etape_retour_correction','ETAPE_RETOUR_CORRECTION_ID !=1','ETAPE_RETOUR_CORRECTION_ID ASC');
            $data['get_correct'] = $this->ModelPs->getRequete($callpsreq, $step_correct);

            $gettypevalidation = $this->getBindParms('ID_OPERATION,DESCRIPTION','budgetaire_type_operation_validation','ID_OPERATION IN (2)','ID_OPERATION ASC');
            $data['type'] = $this->ModelPs->getRequete($callpsreq, $gettypevalidation);

            //Récuperer les motifs
            $bind_motif = $this->getBindParms('BUDGETAIRE_TYPE_ANALYSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE', 'budgetaire_type_analyse','1', 'BUDGETAIRE_TYPE_ANALYSE_ID ASC');
            $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);
            return view('App\Modules\double_commande_new\Views\Phase_Comptable_Salaire_Signature_Dir_Compt_View', $data);
          }
        }
        return redirect('Login_Ptba/homepage');
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }    
    }

    public function save_signature_titre_dir_compt()
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if (empty($user_id)) {
        return  redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DIR_COMPT_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
     

      $id = $this->request->getPost('DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR');
      $date_signature_titre = $this->request->getPost('date_signature_titre');
      $date_reception = $this->request->getPost('date_reception');
      $date_transmission = $this->request->getPost('date_transmission');
      $ETAPE_ID = $this->request->getPost('ETAPE_ID');
      $ID_OPERATION = $this->request->getPost('ID_OPERATION');
      $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost('id_exec_titr_dec');

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
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_SALAIRE=1', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          $table = 'execution_budgetaire_titre_decaissement';

          $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;

          $datatomodifie = 'DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR="'.$date_signature_titre . '",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'"';
          $this->update_all_table($table, $datatomodifie, $conditions);

          $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $datacolumsinsert = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
          $this->save_histo($columsinsert, $datacolumsinsert);

          $data = ['message' => "".lang('messages_lang.message_success').""];
          session()->setFlashdata('alert', $data);

          return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_dir_compt');
        } 
        //si c'est retour a la correction
        elseif ($ID_OPERATION == 1) 
        {
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND AND IS_SALAIRE=1', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          // print_r('expression');die();

          $table = 'execution_budgetaire_titre_decaissement';
          $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
          $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.'';
          $this->update_all_table($table, $datatomodifie, $conditions);

          $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $datacolumsinsert = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
          $this->save_histo($columsinsert, $datacolumsinsert);

          //Enregistrement dans historique vérification des motifs
          foreach ($TYPE_ANALYSE_MOTIF_ID as $value) {
            $insertToTable_motif = '  execution_budgetaire_histo_operation_verification_motif';
            $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
            $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . "";
            $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
          }
          $data = ['message' => "".lang('messages_lang.message_success').""];
          session()->setFlashdata('alert', $data);
          return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_dir_compt');
        }
      } 
      else 
      {
        return $this->signature_titre_dir_compt(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
      }
    }


    /* debut signature par dg finance*/
    public function signature_titre_dgfp($id)
    {   
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if (empty($user_id)) {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DGFP_SALAIRE') !=1)
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
            $callpsreq = "CALL `getRequete`(?,?,?,?);";
            
            $bindparamss=$this->getBindParms('det.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,td.MONTANT_PAIEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_ORDONNANCEMENT,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID','execution_budgetaire_tache_detail det JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID','md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
            $bindparams34 = str_replace("\\", "", $bindparamss);
            $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

            //Requete pour les operation
            $get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION', ' budgetaire_type_operation_validation', 'ID_OPERATION IN(2)', 'ID_OPERATION ASC');
            $get_oper = str_replace('\\', '', $get_oper);
            $data['operation'] = $this->ModelPs->getRequete($callpsreq, $get_oper);

            //Récuperation de l'étape précedent
            $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
            $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
            $etap_prev = $this->ModelPs->getRequeteOne($callpsreq, $bind_etap_prev);

            //Récuperer les motifs
            $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
            $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

            //Récuperation de l'étape précedent
            $date_trans = $this->getBindParms('DATE_TRANSMISSION,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID','  execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$id."'",'DATE_INSERTION DESC');
            $date_trans = str_replace("\'", "'", $date_trans);
            $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $date_trans);

            return view('App\Modules\double_commande_new\Views\Phase_Comptable_Salaire_Signature_DGFP_View', $data);
          }
        }
        return redirect('Login_Ptba/homepage');
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }
    }

    //--------------fx save_signature_titre_dgfp
    public function save_signature_titre_dgfp()
    {
      $session  = \Config\Services::session();
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if(empty($user_id))
      {
        return  redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_DGFP_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('id_exec_titr_dec');
   
      $date_signature_titre = $this->request->getPost('date_signature_titre');
      $date_reception = $this->request->getPost('date_reception');
      $date_transmission = $this->request->getPost('date_transmission');
      $paiement = $this->request->getPost('paiement');
      $ID_OPERATION = $this->request->getPost('ID_OPERATION');
      $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
      $ETAPE_ID = $this->request->getPost('ETAPE_ID');

      //vérifier si le montant paiement est supérieur ou egal à 100 millions
      $is_superieur_cent_million='';
      if ($paiement >= 100000000) {
        $is_superieur_cent_million=' AND EST_SUPERIEUR_CENT_MILLION=1';
      }
  
      $psgetrequete = "CALL getRequete(?,?,?,?);";

      // requette pour recuperer l'etape suivante et mouvement_id  WHERE VISA DONC IS_CORRECTION =0;
      $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_SALAIRE=1 '.$is_superieur_cent_million.'', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
      $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

    
    
      // $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
      $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
   

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

      if($ID_OPERATION == 1)
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
      if ($this->validation->withRequest($this->request)->run()) 
      {
        if ($ID_OPERATION == 2) 
        {
          if ($paiement > 100000000)
          {
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config', 'EST_SUPERIEUR_CENT_MILLION=1 AND IS_SALAIRE=1 and ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID, '1 ASC');
            $get_next_stepsup = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $sup_million = $get_next_stepsup['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $table = 'execution_budgetaire_titre_decaissement';
            $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
            $datatomodifie = 'DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE="'.$date_signature_titre.'",ETAPE_DOUBLE_COMMANDE_ID="'.$sup_million.'"';
            $this->update_all_table($table, $datatomodifie, $conditions);

            $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
            $datacolumsinsert = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
            $this->save_histo($columsinsert, $datacolumsinsert);
          }
          else
          {
            $next_step_inf = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config', 'EST_SUPERIEUR_CENT_MILLION=0 AND IS_SALAIRE=1 and ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID, '1 ASC');
            $get_next_stepinf = $this->ModelPs->getRequeteOne($psgetrequete, $next_step_inf);

            $inf_million = $get_next_stepinf['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $table = 'execution_budgetaire_titre_decaissement';
            $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
            $datatomodifie = ' DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE="'.$date_signature_titre.'",ETAPE_DOUBLE_COMMANDE_ID="'.$inf_million.'"';
            $this->update_all_table($table, $datatomodifie, $conditions);

            $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
            $datacolumsinsert = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
            $this->save_histo($columsinsert, $datacolumsinsert);
          }
        }
        elseif ($ID_OPERATION == 1) 
        {
          //---------------RETOUR A LA CORRECTION--------------------
          // requette pour recuperer l'etape suivante WHERE RETOUR A LA CORRECTION DONC IS_CORRECTION =1;
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_SALAIRE=1', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          foreach ($TYPE_ANALYSE_MOTIF_ID as $value) 
          {
            $insertToTable_motif = 'execution_budgetaire_histo_operation_verification_motif';
            $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
            $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . "";
            $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
          }
          $table = 'execution_budgetaire_titre_decaissement';
          $conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
          $datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.'';
          $this->update_all_table($table, $datatomodifie, $conditions);

          $columsinsert = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $datacolumsinsert = $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
          $this->save_histo($columsinsert, $datacolumsinsert);
        }

        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_dgfp');
      } 
      else 
      {
        return $this->signature_titre_dgfp(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
      }
    }

    //view signature du titre par ministre
    function signature_titre_min($id = 0)
    {
     
      $data=$this->urichk();
      $session  = \Config\Services::session();
      if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_MIN_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $psgetrequete = "CALL `getRequete`(?,?,?,?)";

      $get_hist=$this->getBindParms('det.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,MONTANT_DECAISSEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION,DESC_ETAPE_DOUBLE_COMMANDE,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID','execution_budgetaire_tache_detail det JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_DETAIL_ID=det.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_tache_detail_histo histo ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID','md5(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)="'.$id.'"','td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
      $get_hist=str_replace('\\','',$get_hist);
      $data['id']=$this->ModelPs->getRequeteOne($psgetrequete,$get_hist);

      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$data['id']['ETAPE_DOUBLE_COMMANDE_ID'],'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            //Requete pour les operation
            $get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION', ' budgetaire_type_operation_validation', 'ID_OPERATION IN (1,2)', 'ID_OPERATION ASC');
            $get_oper = str_replace('\\', '', $get_oper);
            $data['operation'] = $this->ModelPs->getRequete($callpsreq, $get_oper);
            //Récuperer les motifs
            $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_MARCHE','budgetaire_type_analyse_motif','1','TYPE_ANALYSE_MOTIF_ID ASC');
            
            $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

            return view('App\Modules\double_commande_new\Views\Phase_Comptable_Salaire_Signature_Min',$data);
          }
        }
        return redirect('Login_Ptba/homepage');
      }
      else
      {
        return redirect('Login_Ptba/homepage');
      }
    }

    //save signature du titre par ministre
    function save_signature_titre_min()
    {
      $data=$this->urichk();
      $session  = \Config\Services::session();
      $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
      if(empty($user_id))
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_SIGNATURE_MIN_SALAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost('id_exec_titr_dec');
      $id_racc=$this->request->getPost('id_raccrochage');
      $id_etape=$this->request->getPost('etape');
      $DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');
      $DATE_SIGNATURE=$this->request->getPost('DATE_SIGNATURE');
      $DATE_INSERTION=date('Y-m-d h:i:s');
      $ID_OPERATION = $this->request->getPost('ID_OPERATION');
      $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');

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
        'ID_OPERATION' => [
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
      else if ($ID_OPERATION == 2)
      {
        $rules['DATE_SIGNATURE'] = [
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
        if($ID_OPERATION == 2)
        {
          //récuperer les etapes et mouvements
          $psgetrequete = "CALL `getRequete`(?,?,?,?);";
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$id_etape.' AND IS_SALAIRE=1','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          //modification dans la table titre_decaissement
          $table='execution_budgetaire_titre_decaissement';
          $data_racc='DATE_SIGNATURE_TD_MINISTRE="'.$DATE_SIGNATURE.'",ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.' ';
          $conditions='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ;
          $this->update_all_table($table,$data_racc,$conditions);

          //insertion dans l'historique_detail
          $table_histo='execution_budgetaire_tache_detail_histo';
          $columsinsert="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_RECEPTION,DATE_TRANSMISSION";
          $data_histo=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.','.$user_id.','.$id_etape.',"'.$DATE_INSERTION.'","'.$DATE_RECEPTION.'","'.$DATE_TRANSMISSION.'"';
          $this->save_all_table($table_histo,$columsinsert,$data_histo);
        }
        elseif ($ID_OPERATION == 1)
        {
          //récuperer les etapes et mouvements
          $psgetrequete = "CALL `getRequete`(?,?,?,?);";
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$id_etape.' AND IS_SALAIRE=1','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          //modification dans la table titre_decaissement
          $table='execution_budgetaire_titre_decaissement';
          $data_racc='ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.' ';
          $conditions='EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ;
          $this->update_all_table($table,$data_racc,$conditions);

          //insertion dans l'historique_detail
          $table_histo='execution_budgetaire_tache_detail_histo';
          $columsinsert="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_INSERTION,DATE_RECEPTION,DATE_TRANSMISSION";
          $data_histo=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.','.$user_id.','.$id_etape.',"'.$DATE_INSERTION.'","'.$DATE_RECEPTION.'","'.$DATE_TRANSMISSION.'"';
          $this->save_all_table($table_histo,$columsinsert,$data_histo);

          //Enregistrement dans historique vérification des motifs
          foreach ($TYPE_ANALYSE_MOTIF_ID as $value)
          {
            $insertToTable_motif = 'execution_budgetaire_histo_operation_verification_motif';
            $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
            $datatoinsert_histo_motif = "" . $value . "," . $id_etape . "," . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . "";
            $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
          }
        }
        
        $data=['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Paiement_Salaire_Liste/vue_sign_ministre');
      }
      else
      {
        return $this->signature_titre_min(md5($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID));
      }
    }

    //Cette fonction retourne le nombre des chiffres d un nombre ($value) passé en paramètre
    function get_precision($value=0)
    {
      $parts = explode('.', strval($value));
      return isset($parts[1]) ? strlen($parts[1]) : 0; 
    }

    //récupération du sous titre par rapport à l'institution
    function get_sous_titre($INSTITUTION_ID = 0)
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $db = db_connect();
      $callpsreq = "CALL `getRequete`(?,?,?,?);";

      $get_sous_tutelle = $this->getBindParms('`SOUS_TUTEL_ID`,`DESCRIPTION_SOUS_TUTEL`', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.INSTITUTION_ID=' . $INSTITUTION_ID . ' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
      $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);

      $html = '<option value="">' . lang('messages_lang.labelle_selecte') . '</option>';
      foreach ($sous_tutelle as $key)
      {
        $html .= '<option value="' . $key->SOUS_TUTEL_ID . '">' . $key->DESCRIPTION_SOUS_TUTEL . '</option>';
      }

      $output = array(
        "sous_tutel" => $html,
      );

      return $this->response->setJSON($output);
    }

    public function uploadFile($fieldName=NULL, $folder=NULL, $prefix = NULL): string
    {
      $prefix = ($prefix === '') ? uniqid() : $prefix;
      $path = '';

      $file = $this->request->getFile($fieldName);

      if ($file->isValid() && !$file->hasMoved()) {
        $newName = uniqid(). '' . date('ymdhis') . '.' . $file->getExtension();
        $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
        $path = 'uploads/' . $folder . '/' . $newName;
      }
      return $path;
    }

    public function save_all_table($table,$columsinsert,$datacolumsinsert)
    {
      $bindparms=[$table,$columsinsert,$datacolumsinsert];
      $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
      $tableparams =[$table,$columsinsert,$datacolumsinsert];
      $result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
      return $id=$result['id'];
    }

    //Update
    public function update_all_table($table,$datatomodifie,$conditions)
    {
      $bindparams =[$table,$datatomodifie,$conditions];
      $updateRequete = "CALL `updateData`(?,?,?);";
      $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
    }

    public function getBindParms($columnselect, $table, $where, $orderby)
    {
      $db = db_connect();
      $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
      return $bindparams;
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

  }
?>