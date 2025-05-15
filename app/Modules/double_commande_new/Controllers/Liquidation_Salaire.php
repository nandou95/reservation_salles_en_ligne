<?php
/*
*NDERAGAKURA ALAIN CHARBEL
*Titre: Engagement cas salaire
*Numero de telephone: (+257) 62003522
*Email: charbel@mediabox.bi
*Date: 12 aout,2024
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Liquidation_Salaire extends BaseController
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
    $MOUVEMENT_DEPENSE_ID=3;

    $table="budgetaire_type_analyse_motif";
    $columsinsert = "MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF,IS_SALAIRE";
    $datacolumsinsert = "{$MOUVEMENT_DEPENSE_ID},'{$DESCRIPTION_MOTIF}',1";    
    $this->save_all_table($table,$columsinsert,$datacolumsinsert);

    $callpsreq = "CALL getRequete(?,?,?,?);";

      //récuperer les motifs
    $bind_motif = $this->getBindParms('TYPE_ANALYSE_MOTIF_ID,MOUVEMENT_DEPENSE_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','MOUVEMENT_DEPENSE_ID=3 AND IS_SALAIRE=1','DESC_TYPE_ANALYSE_MOTIF ASC');
    $motif = $this->ModelPs->getRequete($callpsreq, $bind_motif);

    $html='';
    if(!empty($motif))
    {
      foreach($motif as $key)
      { 
        $html.= "<option value='".$key->TYPE_ANALYSE_MOTIF_ID."'>".$key->DESC_TYPE_ANALYSE_MOTIF."</option>";
      }
    }
    $html.='<option value="-1">'.lang('messages_lang.selection_autre').'</option>';
    $output = array('status' => TRUE ,'motifs' => $html);
    return $this->response->setJSON($output);
  }

  // affiche le view pour la 1er etape d'engagement salaire
  function add()
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

    // if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    // {
    //   return redirect('Login_Ptba/homepage');
    // }

    $ETAPE_DOUBLE_COMMANDE_ID=10;
    
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

    // $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire ORDER BY CATEGORIE_SALAIRE_ID ASC';
    // $getCateg = "CALL `getTable`('" . $getCateg . "');";
    // $data['categorie'] = $this->ModelPs->getRequete($getCateg);

    $gettype = 'SELECT TYPE_SALAIRE_ID,DESC_TYPE_SALAIRE FROM type_salairie ORDER BY TYPE_SALAIRE_ID ASC';
    $gettype = "CALL `getTable`('" . $gettype . "');";
    $data['type'] = $this->ModelPs->getRequete($gettype);

    $dataa=$this->converdate();
    $TRIMESTRE_ID = $dataa['TRIMESTRE_ID'];

    $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE TRIMESTRE_ID<='.$TRIMESTRE_ID.' ORDER BY MOIS_ID ASC';
    $get_mois = "CALL `getTable`('" . $get_mois . "');";
    $data['get_mois']= $this->ModelPs->getRequete($get_mois);

    return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Add_View',$data);
  }

  //get categorie salarie
  function get_salarie()
  {
    $session  = \Config\Services::session();
    $USER_ID='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    

    if(!empty($INSTITUTION_ID)) 
    {
      if ($INSTITUTION_ID==17)
      {
        $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire WHERE CATEGORIE_SALAIRE_ID=3 ORDER BY CATEGORIE_SALAIRE_ID ASC';
      }
      else
      {
        $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire WHERE CATEGORIE_SALAIRE_ID!=3 ORDER BY CATEGORIE_SALAIRE_ID ASC';   
      }
      $getCateg = "CALL `getTable`('" . $getCateg . "');";
      $getCateg = $this->ModelPs->getRequete($getCateg);

      $html ='<option>'.lang('messages_lang.label_select').'</option>';
      foreach ($getCateg as $key)
      {
        $html.='<option value="'.$key->CATEGORIE_SALAIRE_ID.'">'.$key->DESC_CATEGORIE_SALAIRE.'</option>';
      }
      return json_encode(array("html"=>$html));
    }
  }

  //save engagement des salaires
  function savesalaire()
  {
    $session  = \Config\Services::session();
    $USER_ID='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    $rules = [
      'INSTITUTION_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],      
      'SOUS_TITRE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'CATEGORIE_SALAIRE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'MOIS_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],      
      'NET' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'QTE_FONCTION_PUBLIQUE' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],  
      'QTE_RESSOURCES_HUMAINES' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    $getdata=$this->request->getPost("getdata");
    $getdata = json_decode($getdata, true);

    foreach($getdata as $key)
    {
      $MONT=$this->request->getPost("MONTANT_LIQUIDE".$key['PTBA_TACHE_ID']);
      if (empty($MONT))
      {
        $rules['MONTANT_LIQUIDE'.$key["PTBA_TACHE_ID"]] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
          ]
        ];
      }            
    }      

    $this->validation->setRules($rules);

    if($this->validation->withRequest($this->request)->run())
    {
      $INSTITUTION_ID=$this->request->getPost("INSTITUTION_ID");
      $SOUS_TITRE_ID=$this->request->getPost("SOUS_TITRE_ID");
      $CATEGORIE_SALAIRE_ID=$this->request->getPost("CATEGORIE_SALAIRE_ID");
      $MOIS_ID=$this->request->getPost("MOIS_ID");
      $TYPE_SALAIRE_ID=$this->request->getPost('TYPE_SALAIRE_ID');
      $INSS_P=str_replace(' ','',$this->request->getPost("INSS_P"));
      $INSS_RP=str_replace(' ','',$this->request->getPost("INSS_RP"));
      $ONPR=str_replace(' ','',$this->request->getPost("ONPR"));
      $MFP=str_replace(' ','',$this->request->getPost("MFP"));
      $IMPOT=str_replace(' ','',$this->request->getPost("IMPOT"));
      $AUTRES_RETENUS=str_replace(' ','',$this->request->getPost("AUTRES_RETENUS"));
      $NET=str_replace(' ','',$this->request->getPost("NET"));
      $QTE_FONCTION_PUBLIQUE=$this->request->getPost("QTE_FONCTION_PUBLIQUE");
      $QTE_RESSOURCES_HUMAINES=$this->request->getPost("QTE_RESSOURCES_HUMAINES");
      $MONTANT_LIQUIDE=0;
      $ETAPE_ID=10;
      $ANNEE_BUDGETAIRE_ID=$this->get_annee_budgetaire();

      $TOTAL_RUBRIQUE=floatval($INSS_P)+floatval($INSS_RP)+floatval($ONPR)+floatval($MFP)+floatval($IMPOT)+floatval($AUTRES_RETENUS)+floatval($NET);

      $psgetrequete = "CALL `getRequete`(?,?,?,?)";
      $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID.' AND IS_CORRECTION=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
      $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
      $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

      foreach($getdata as $key)
      {
        $LIQUIDE=$this->request->getPost("MONTANT_LIQUIDE".$key['PTBA_TACHE_ID']);
        $LIQUIDE=str_replace(' ','',$LIQUIDE);

        $MONTANT_RESTANT=$this->request->getPost("MONTANT_RESTANT".$key['PTBA_TACHE_ID']);
        $MONTANT_RESTANT=str_replace(' ','',$LIQUIDE);        

        if($LIQUIDE>$MONTANT_RESTANT)
        {
          $data=['message' => "".lang('messages_lang.mount_sup').""];
          session()->setFlashdata('alert', $data);
          return $this->add();
        }
        $MONTANT_LIQUIDE +=intval($LIQUIDE);
      }

      if($TOTAL_RUBRIQUE!=$MONTANT_LIQUIDE)
      {
        $data=['message' => "".lang('messages_lang.mont_rubr_differ').""];
        session()->setFlashdata('alert', $data);
        return $this->add();
      }

      $DEVISE_TYPE_HISTO_ENG_ID=1;
      $LIQUIDATION_TYPE_ID=2;
      $EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3;      
      //$TRIMESTRE_ID=$this->converdate()['TRIMESTRE_ID'];
      $getmois ="SELECT TRIMESTRE_ID FROM mois WHERE MOIS_ID=".$MOIS_ID;
      $getmois = 'CALL `getTable`("'.$getmois.'");';
      $getmois= $this->ModelPs->getRequeteOne($getmois);
      $TRIMESTRE_ID=$getmois['TRIMESTRE_ID'];
      $DATE_BON_ENGAGEMENT=date('Y-m-d');
      $DATE_ENG_JURIDIQUE=date('Y-m-d');
      $DATE_LIQUIDATION=date('Y-m-d');

      $get_exec="SELECT exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ENG_BUDGETAIRE,ENG_JURIDIQUE,LIQUIDATION,INSS_P,INSS_RP,ONPR,MFP,IMPOT,AUTRES_RETENUS,NET FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID." AND MOIS_ID=".$MOIS_ID." AND CATEGORIE_SALAIRE_ID=".$CATEGORIE_SALAIRE_ID;
      $get_exec = 'CALL `getTable`("'.$get_exec.'");';
      $get_exec= $this->ModelPs->getRequeteOne($get_exec);
      $EXECUTION_BUDGETAIRE_ID="";
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='';
      if(empty($get_exec))
      {
        $insertIntoexec='execution_budgetaire';
        $columns="ANNEE_BUDGETAIRE_ID,TRIMESTRE_ID,ENG_BUDGETAIRE,DEVISE_TYPE_HISTO_ENG_ID,ENG_JURIDIQUE,DEVISE_TYPE_HISTO_JURD_ID,LIQUIDATION_TYPE_ID,LIQUIDATION,USER_ID,TYPE_ENGAGEMENT_ID,EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID,MOIS_ID,CATEGORIE_SALAIRE_ID,TYPE_SALAIRE_ID,INSS_P,INSS_RP,ONPR,MFP,IMPOT,AUTRES_RETENUS,NET";
        $datacolums=$ANNEE_BUDGETAIRE_ID.",".$TRIMESTRE_ID.",".$MONTANT_LIQUIDE.",".$DEVISE_TYPE_HISTO_ENG_ID.",".$MONTANT_LIQUIDE.",".$DEVISE_TYPE_HISTO_ENG_ID.",".$LIQUIDATION_TYPE_ID.",".$MONTANT_LIQUIDE.",".$USER_ID.",1,".$EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID.",".$MOIS_ID.",".$CATEGORIE_SALAIRE_ID.",".$TYPE_SALAIRE_ID.",".$INSS_P.",".$INSS_RP.",".$ONPR.",".$MFP.",".$IMPOT.",".$AUTRES_RETENUS.",".$NET;
        $EXECUTION_BUDGETAIRE_ID=$this->save_all_table($insertIntoexec,$columns,$datacolums);

        $insertIntodet='execution_budgetaire_tache_detail';
        $columnsdet="EXECUTION_BUDGETAIRE_ID,MONTANT_LIQUIDATION,DATE_LIQUIDATION";
        $datacolumsdet=$EXECUTION_BUDGETAIRE_ID.",".$MONTANT_LIQUIDE.",'".$DATE_LIQUIDATION."'";
        $EXECUTION_BUDGETAIRE_DETAIL_ID=$this->save_all_table($insertIntodet,$columnsdet,$datacolumsdet);

        $insertIntoSt='execution_budgetaire_salaire_sous_titre';
        $columSt="EXECUTION_BUDGETAIRE_ID,INSTITUTION_ID,SOUS_TUTEL_ID,TOTAL_SALAIRE,INSS_P,INSS_RP,ONPR,MFP,IMPOT,AUTRES_RETENUS,NET,QTE_FONCTION_PUBLIQUE,QTE_RESSOURCES_HUMAINES";
        $datacolumsSt=$EXECUTION_BUDGETAIRE_ID.",".$INSTITUTION_ID.",".$SOUS_TITRE_ID.",".$MONTANT_LIQUIDE.",".$INSS_P.",".$INSS_RP.",".$ONPR.",".$MFP.",".$IMPOT.",".$AUTRES_RETENUS.",".$NET.",".$QTE_FONCTION_PUBLIQUE.",".$QTE_RESSOURCES_HUMAINES;
        $this->save_all_table($insertIntoSt,$columSt,$datacolumsSt);

        //insertion dans execution_budgetaire_titre_decaissement
        $insertIntoTD='execution_budgetaire_titre_decaissement';
        $columTD="EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID";
        $datacolumsTD=$EXECUTION_BUDGETAIRE_ID.",".$EXECUTION_BUDGETAIRE_DETAIL_ID.",".$NEXT_ETAPE_ID;
        $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->save_all_table($insertIntoTD,$columTD,$datacolumsTD);

        //insertion dans info suppl
        $insertIntoInfoSup='execution_budgetaire_tache_info_suppl';
        $columInfoSup="EXECUTION_BUDGETAIRE_ID";
        $datacolumsInfoSup=$EXECUTION_BUDGETAIRE_ID;
        $this->save_all_table($insertIntoInfoSup,$columInfoSup,$datacolumsInfoSup);
      }
      else
      {
        $LIQUIDATION_TOTAL=$get_exec['LIQUIDATION']+$MONTANT_LIQUIDE;
        $INSS_P_TOTAL=$get_exec['INSS_P']+$INSS_P;
        $INSS_RP_TOTAL=$get_exec['INSS_RP']+$INSS_RP;
        $ONPR_TOTAL=$get_exec['ONPR']+$ONPR;
        $MFP_TOTAL=$get_exec['MFP']+$MFP;
        $IMPOT_TOTAL=$get_exec['IMPOT']+$IMPOT;
        $AUTRES_RETENUS_TOTAL=$get_exec['AUTRES_RETENUS']+$AUTRES_RETENUS;
        $NET_TOTAL=$get_exec['NET']+$NET;

        $updateIntoexec='execution_budgetaire';
        $conditions='EXECUTION_BUDGETAIRE_ID='.$get_exec['EXECUTION_BUDGETAIRE_ID'];
        $datacolumsUpdate="ENG_BUDGETAIRE=".$LIQUIDATION_TOTAL.",ENG_JURIDIQUE=".$LIQUIDATION_TOTAL.",LIQUIDATION=".$LIQUIDATION_TOTAL.",INSS_P=".$INSS_P_TOTAL.",INSS_RP=".$INSS_RP_TOTAL.",ONPR=".$ONPR_TOTAL.",MFP=".$MFP_TOTAL.",IMPOT=".$IMPOT_TOTAL.",AUTRES_RETENUS=".$AUTRES_RETENUS_TOTAL.",NET=".$NET_TOTAL;
        $this->update_all_table($updateIntoexec,$datacolumsUpdate,$conditions);
        $EXECUTION_BUDGETAIRE_ID=$get_exec['EXECUTION_BUDGETAIRE_ID'];

        $get_st="SELECT TOTAL_SALAIRE,INSS_P,INSS_RP,ONPR,MFP,IMPOT,AUTRES_RETENUS,NET,QTE_FONCTION_PUBLIQUE,QTE_RESSOURCES_HUMAINES FROM execution_budgetaire_salaire_sous_titre st WHERE EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID." AND SOUS_TUTEL_ID=".$SOUS_TITRE_ID;
        $get_st = 'CALL `getTable`("'.$get_st.'");';
        $get_st= $this->ModelPs->getRequeteOne($get_st);

        //insertion dans execution_budgetaire_salaire_sous_titre
        $insertIntoSt='execution_budgetaire_salaire_sous_titre';
        $columSt="EXECUTION_BUDGETAIRE_ID,INSTITUTION_ID,SOUS_TUTEL_ID,TOTAL_SALAIRE,INSS_P,INSS_RP,ONPR,MFP,IMPOT,AUTRES_RETENUS,NET,QTE_FONCTION_PUBLIQUE,QTE_RESSOURCES_HUMAINES";
        $datacolumsSt=$EXECUTION_BUDGETAIRE_ID.",".$INSTITUTION_ID.",".$SOUS_TITRE_ID.",".$MONTANT_LIQUIDE.",".$INSS_P.",".$INSS_RP.",".$ONPR.",".$MFP.",".$IMPOT.",".$AUTRES_RETENUS.",".$NET.",".$QTE_FONCTION_PUBLIQUE.",".$QTE_RESSOURCES_HUMAINES;
        $this->save_all_table($insertIntoSt,$columSt,$datacolumsSt);

        $updateIntodet='execution_budgetaire_tache_detail';
        $columnsdetUpdate="MONTANT_LIQUIDATION=".$LIQUIDATION_TOTAL.",DATE_LIQUIDATION='".$DATE_LIQUIDATION."'";
        $this->update_all_table($updateIntodet,$columnsdetUpdate,$conditions);
        $EXECUTION_BUDGETAIRE_DETAIL_ID=$get_exec['EXECUTION_BUDGETAIRE_DETAIL_ID'];

        //modification dans execution_budgetaire_titre_decaissement
        $updateIntoTD='execution_budgetaire_titre_decaissement';
        $datacolumsTDupdate="EXECUTION_BUDGETAIRE_DETAIL_ID=".$EXECUTION_BUDGETAIRE_DETAIL_ID;
        $this->update_all_table($updateIntoTD,$datacolumsTDupdate,$conditions); 
        $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$get_exec['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'];
      }

      foreach($getdata as $key)
      {
        $LIQUIDE=$this->request->getPost("MONTANT_LIQUIDE".$key['PTBA_TACHE_ID']);
        $LIQUIDE=str_replace(' ','',$LIQUIDE);
        $QTE_FONCTION_PUBLIQUE=$this->request->getPost("QTE_FONCTION_PUBLIQUE".$key['PTBA_TACHE_ID']);
        $QTE_RESSOURCES_HUMAINES=$this->request->getPost("QTE_RESSOURCES_HUMAINES".$key['PTBA_TACHE_ID']);
        //RESULTAT_ATTENDUS = QTE_RESSOURCES_HUMAINES && QTE =QTE_FONCTION_PUBLIQUE 
        $columns="EXECUTION_BUDGETAIRE_ID,PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_JURIDIQUE,MONTANT_LIQUIDATION,USER_ID";
        $datacolums=$EXECUTION_BUDGETAIRE_ID.",".$key['PTBA_TACHE_ID'].",".$LIQUIDE.",".$LIQUIDE.",".$LIQUIDE.",".$USER_ID."";
        $insertIntotache="execution_budgetaire_execution_tache";
        $this->save_all_table($insertIntotache,$columns,$datacolums);

        // retrancher l'argent dans ptba
        $MoneyRest  = 'SELECT ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T1,BUDGET_UTILISE_T2,BUDGET_UTILISE_T3,BUDGET_UTILISE_T4 FROM ptba_tache WHERE PTBA_TACHE_ID='.$key['PTBA_TACHE_ID'].' AND ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
        $MoneyRest = "CALL `getTable`('" . $MoneyRest . "');";
        $RestPTBA = $this->ModelPs->getRequeteOne($MoneyRest);

        $whereptba ="PTBA_TACHE_ID = ".$key['PTBA_TACHE_ID'];        
        $insertIntoptba='ptba_tache';
        if ($TRIMESTRE_ID==1)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T1']) - floatval($LIQUIDE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T1']) + floatval($LIQUIDE);

          $columptba="BUDGET_RESTANT_T1=".$apresEng.",BUDGET_UTILISE_T1=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==2)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T2']) - floatval($LIQUIDE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T2']) + floatval($LIQUIDE);

          $columptba="BUDGET_RESTANT_T2=".$apresEng.",BUDGET_UTILISE_T2=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==3)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T3']) - floatval($LIQUIDE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T3']) + floatval($LIQUIDE);

          $columptba="BUDGET_RESTANT_T3=".$apresEng.",BUDGET_UTILISE_T3=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($TRIMESTRE_ID==4)
        {
          $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T4']) - floatval($LIQUIDE);
          $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T4']) + floatval($LIQUIDE);
          
          $columptba="BUDGET_RESTANT_T4=".$apresEng.",BUDGET_UTILISE_T4=".$total_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }  
      }

      //insertion dans historique
      $insertIntoOp='execution_budgetaire_tache_detail_histo';
      $columOp="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID";
      $datacolumsOp=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_ID.",".$USER_ID."";
      $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

      $data=['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);
      return redirect('double_commande_new/Liquidation_Salaire_Liste/index_Deja_Fait');
    }
    else
    {
      return $this->add();
    }
  }

  //add beneficiaire de la depense
  function add_benef()
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

    // if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    // {
    //   return redirect('Login_Ptba/homepage');
    // }

    $ETAPE_DOUBLE_COMMANDE_ID=10;
    
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

    $data['ANNEE_BUDGETAIRE_ID']=$this->get_annee_budgetaire();
    $getAnnee  = 'SELECT ANNEE_DESCRIPTION FROM annee_budgetaire WHERE ANNEE_BUDGETAIRE_ID ='.$data['ANNEE_BUDGETAIRE_ID'].'  ORDER BY ANNEE_BUDGETAIRE_ID ASC';
    $getAnnee = "CALL `getTable`('" . $getAnnee . "');";
    $data['ANNEE_DESCRIPTION'] = $this->ModelPs->getRequeteOne($getAnnee)['ANNEE_DESCRIPTION'];

    $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire ORDER BY CATEGORIE_SALAIRE_ID ASC';
    $getCateg = "CALL `getTable`('" . $getCateg . "');";
    $data['categorie'] = $this->ModelPs->getRequete($getCateg);

    $gettype = 'SELECT TYPE_SALAIRE_ID,DESC_TYPE_SALAIRE FROM type_salairie ORDER BY TYPE_SALAIRE_ID ASC';
    $gettype = "CALL `getTable`('" . $gettype . "');";
    $data['type'] = $this->ModelPs->getRequete($gettype);

    $getBenef = 'SELECT BENEFICIAIRE_TITRE_ID,DESC_BENEFICIAIRE FROM  beneficiaire_des_titres ORDER BY BENEFICIAIRE_TITRE_ID ASC';
    $getBenef = "CALL `getTable`('" . $getBenef . "');";
    $data['beneficiaire'] = $this->ModelPs->getRequete($getBenef);

    $dataa=$this->converdate();
    $TRIMESTRE_ID = $dataa['TRIMESTRE_ID'];
    $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE TRIMESTRE_ID='.$TRIMESTRE_ID.' ORDER BY MOIS_ID ASC';
    $get_mois = "CALL `getTable`('" . $get_mois . "');";
    $data['get_mois']= $this->ModelPs->getRequete($get_mois);

    return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Add_Benef_View',$data);
  }

  //Les nouveaux benef
  function save_newBenef()
  {
    $session  = \Config\Services::session();

    $DESCRIPTION_BENEF = $this->request->getPost('DESCRIPTION_BENEF');
    $MOUVEMENT_DEPENSE_ID=3;

    $table="beneficiaire_des_titres";
    $columsinsert = "DESC_BENEFICIAIRE";
    $datacolumsinsert = "'{$DESCRIPTION_BENEF}'";    
    $this->save_all_table($table,$columsinsert,$datacolumsinsert);

    $callpsreq = "CALL getRequete(?,?,?,?);";

      //récuperer les benefs
    $bind_benef = $this->getBindParms('BENEFICIAIRE_TITRE_ID,DESC_BENEFICIAIRE','beneficiaire_des_titres','1','DESC_BENEFICIAIRE ASC');
    $benef = $this->ModelPs->getRequete($callpsreq, $bind_benef);

    $html='';

    if(!empty($benef))
    {
      foreach($benef as $key)
      { 
        $html.= "<option value='".$key->BENEFICIAIRE_TITRE_ID."'>".$key->DESC_BENEFICIAIRE."</option>";
      }
    }
    $html.='<option value="-1">'.lang('messages_lang.selection_autre').'</option>';
    $output = array('status' => TRUE ,'benef' => $html);
    return $this->response->setJSON($output);
  }

  //view liquidation salaire autre retenu
  function add_autre_retenu()
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

    // if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    // {
    //   return redirect('Login_Ptba/homepage');
    // }

    $ETAPE_DOUBLE_COMMANDE_ID=10;
    
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

    $data['ANNEE_BUDGETAIRE_ID']=$this->get_annee_budgetaire();
    $getAnnee  = 'SELECT ANNEE_DESCRIPTION FROM annee_budgetaire WHERE ANNEE_BUDGETAIRE_ID ='.$data['ANNEE_BUDGETAIRE_ID'].'  ORDER BY ANNEE_BUDGETAIRE_ID ASC';
    $getAnnee = "CALL `getTable`('" . $getAnnee . "');";
    $data['ANNEE_DESCRIPTION'] = $this->ModelPs->getRequeteOne($getAnnee)['ANNEE_DESCRIPTION'];

    $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire ORDER BY CATEGORIE_SALAIRE_ID ASC';
    $getCateg = "CALL `getTable`('" . $getCateg . "');";
    $data['categorie'] = $this->ModelPs->getRequete($getCateg);

    $gettype = 'SELECT TYPE_SALAIRE_ID,DESC_TYPE_SALAIRE FROM type_salairie ORDER BY TYPE_SALAIRE_ID ASC';
    $gettype = "CALL `getTable`('" . $gettype . "');";
    $data['type'] = $this->ModelPs->getRequete($gettype);

    $getBenef = 'SELECT BENEFICIAIRE_TITRE_ID,DESC_BENEFICIAIRE FROM  beneficiaire_des_titres ORDER BY BENEFICIAIRE_TITRE_ID ASC';
    $getBenef = "CALL `getTable`('" . $getBenef . "');";
    $data['beneficiaire'] = $this->ModelPs->getRequete($getBenef);

    $dataa=$this->converdate();
    $TRIMESTRE_ID = $dataa['TRIMESTRE_ID'];
    // $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE TRIMESTRE_ID='.$TRIMESTRE_ID.' ORDER BY MOIS_ID ASC';
    $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE 1 ORDER BY MOIS_ID ASC';
    $get_mois = "CALL `getTable`('" . $get_mois . "');";
    $data['get_mois']= $this->ModelPs->getRequete($get_mois);

    $cart = \Config\Services::cart();
    $cart->destroy();
    $bind_data = $this->getBindParms('EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID,catego.DESC_CATEGORIE_SALAIRE,type_salairie.DESC_TYPE_SALAIRE,mois.DESC_MOIS,benef.DESC_BENEFICIAIRE,MOTIF_PAIEMENT,MONTANT_PAIEMENT','execution_budgetaire_autre_retenue_tempo tempo JOIN categorie_salaire catego ON catego.CATEGORIE_SALAIRE_ID=tempo.CATEGORIE_SALAIRE_ID JOIN mois ON mois.MOIS_ID=tempo.MOIS_ID JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID=tempo.TYPE_SALAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=tempo.BENEFICIAIRE_TITRE_ID','USER_ID='.$user_id,'1');
    $bind_data= $this->ModelPs->getRequete($callpsreq, $bind_data);

    foreach($bind_data as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESC_CATEGORIE_SALAIRE'=>$value->DESC_CATEGORIE_SALAIRE,
        'DESC_TYPE_SALAIRE'=>$value->DESC_TYPE_SALAIRE,
        'DESC_MOIS'=>$value->DESC_MOIS,
        'DESC_BENEFICIAIRE'=>$value->DESC_BENEFICIAIRE,
        'MOTIF_PAIEMENT'=>$value->MOTIF_PAIEMENT,
        'MONTANT_PAIEMENT'=>number_format($value->MONTANT_PAIEMENT,$this->get_precision($value->MONTANT_PAIEMENT),'',' '),
        'EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'=>$value->EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }
    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>'.lang('messages_lang.categorie_salarie').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.type_salarie').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.label_mois').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.labelle_mot').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.labelle_montant').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.labelle_beneficiaire_salary').'&emsp;&emsp;&emsp;&emsp;</th>
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
          $html.='<tr>
          <td>'.$j.'</td>
         
          <td>'.$items['DESC_CATEGORIE_SALAIRE'].'</td>
          <td>'.$items['DESC_TYPE_SALAIRE'].'</td>
          <td>'.$items['DESC_MOIS'].'</td>
          <td>'.$items['MOTIF_PAIEMENT'].'</td>
          <td>'.$items['DESC_BENEFICIAIRE'].'</td>
          <td>'.$items['MONTANT_PAIEMENT'].'</td>
          <td>
          <a href="javascript:void(0)" onclick="show_modal('.$items['EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
          </td>
          <td>
          <input type="hidden" id="rowid'.$items['EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'].'" value='.$items['EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'].'>
          </td>
          </tr>';        
        }

        $j++;
        $i++;
    endforeach;
    $html.=' </tbody>
    </table>';
    $data['nbr_cart']=$i;
    if ($i>0) {
      $data['html']=$html;
    }
    else
    {
      $data['html']='';
    }

    return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Autre_Retenu_Add_View',$data);
  }

  public function save_tempo()
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
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $CATEGORIE_SALAIRE_ID=$this->request->getPost('CATEGORIE_SALAIRE_ID');
    $TYPE_SALAIRE_ID=$this->request->getPost('TYPE_SALAIRE_ID');
    $MOIS_ID=$this->request->getPost('MOIS_ID');
    $MOTIF_PAIEMENT=$this->request->getPost('MOTIF_PAIEMENT');
    $MOTIF_PAIEMENT=addslashes($MOTIF_PAIEMENT);
    $MONTANT_PAIEMENT=$this->request->getPost('MONTANT_PAIEMENT');
    $MONTANT_PAIEMENT=str_replace(' ','',$MONTANT_PAIEMENT);
    $BENEFICIAIRE_TITRE_ID=$this->request->getPost('BENEFICIAIRE_TITRE_ID');

    $columsinsert="CATEGORIE_SALAIRE_ID,TYPE_SALAIRE_ID,MOIS_ID,MOTIF_PAIEMENT,MONTANT_PAIEMENT,BENEFICIAIRE_TITRE_ID,USER_ID";
    $datatoinsert= $CATEGORIE_SALAIRE_ID.','.$TYPE_SALAIRE_ID.',"'.$MOIS_ID.'","'.$MOTIF_PAIEMENT.'",'.$MONTANT_PAIEMENT.','.$BENEFICIAIRE_TITRE_ID.','.$user_id;
    $table='execution_budgetaire_autre_retenue_tempo';
    $this->save_all_table($table,$columsinsert,$datatoinsert);

    $cart = \Config\Services::cart();
    $cart->destroy();
    $bind_data = $this->getBindParms('EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID,catego.DESC_CATEGORIE_SALAIRE,type_salairie.DESC_TYPE_SALAIRE,mois.DESC_MOIS,benef.DESC_BENEFICIAIRE,MOTIF_PAIEMENT,MONTANT_PAIEMENT','execution_budgetaire_autre_retenue_tempo tempo JOIN categorie_salaire catego ON catego.CATEGORIE_SALAIRE_ID=tempo.CATEGORIE_SALAIRE_ID JOIN mois ON mois.MOIS_ID=tempo.MOIS_ID JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID=tempo.TYPE_SALAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=tempo.BENEFICIAIRE_TITRE_ID','USER_ID='.$user_id,'1');
    $bind_data= $this->ModelPs->getRequete($callpsreq, $bind_data);

    foreach($bind_data as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESC_CATEGORIE_SALAIRE'=>$value->DESC_CATEGORIE_SALAIRE,
        'DESC_TYPE_SALAIRE'=>$value->DESC_TYPE_SALAIRE,
        'DESC_MOIS'=>$value->DESC_MOIS,
        'DESC_BENEFICIAIRE'=>$value->DESC_BENEFICIAIRE,
        'MOTIF_PAIEMENT'=>$value->MOTIF_PAIEMENT,
        'MONTANT_PAIEMENT'=>number_format($value->MONTANT_PAIEMENT,$this->get_precision($value->MONTANT_PAIEMENT),'',' '),
        'EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'=>$value->EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }
    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>'.lang('messages_lang.categorie_salarie').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.type_salarie').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.label_mois').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.labelle_mot').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.labelle_montant').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.labelle_beneficiaire_salary').'&emsp;&emsp;&emsp;&emsp;</th>
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
          $html.='<tr>
          <td>'.$j.'</td>
         
          <td>'.$items['DESC_CATEGORIE_SALAIRE'].'</td>
          <td>'.$items['DESC_TYPE_SALAIRE'].'</td>
          <td>'.$items['DESC_MOIS'].'</td>
          <td>'.$items['MOTIF_PAIEMENT'].'</td>
          <td>'.$items['DESC_BENEFICIAIRE'].'</td>
          <td>'.$items['MONTANT_PAIEMENT'].'</td>
          <td>
          <a href="javascript:void(0)" onclick="show_modal('.$items['EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
          </td>
          <td>
          <input type="hidden" id="rowid'.$items['EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'].'" value='.$items['EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'].'>
          </td>
          </tr>';        
        }

        $j++;
        $i++;
    endforeach;
    $html.=' </tbody>
    </table>';
    if ($i>0) 
    {
      $output = array('nbr' => $i, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
    else
    {
      $html= '';
      $output = array('nbr' => $i, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
  }

  public function delete()
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

    $id=$this->request->getPost('id');

    $db = db_connect();     
    $critere ="EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID =" .$id;
    $table="execution_budgetaire_autre_retenue_tempo";
    $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";
    $this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);

    $cart = \Config\Services::cart();
    $cart->destroy();
    $bind_data = $this->getBindParms('EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID,catego.DESC_CATEGORIE_SALAIRE,type_salairie.DESC_TYPE_SALAIRE,mois.DESC_MOIS,benef.DESC_BENEFICIAIRE,MOTIF_PAIEMENT,MONTANT_PAIEMENT','execution_budgetaire_autre_retenue_tempo tempo JOIN categorie_salaire catego ON catego.CATEGORIE_SALAIRE_ID=tempo.CATEGORIE_SALAIRE_ID JOIN mois ON mois.MOIS_ID=tempo.MOIS_ID JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID=tempo.TYPE_SALAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=tempo.BENEFICIAIRE_TITRE_ID','USER_ID='.$user_id,'1');
    $bind_data= $this->ModelPs->getRequete($callpsreq, $bind_data);

    foreach($bind_data as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESC_CATEGORIE_SALAIRE'=>$value->DESC_CATEGORIE_SALAIRE,
        'DESC_TYPE_SALAIRE'=>$value->DESC_TYPE_SALAIRE,
        'DESC_MOIS'=>$value->DESC_MOIS,
        'DESC_BENEFICIAIRE'=>$value->DESC_BENEFICIAIRE,
        'MOTIF_PAIEMENT'=>$value->MOTIF_PAIEMENT,
        'MONTANT_PAIEMENT'=>number_format($value->MONTANT_PAIEMENT,$this->get_precision($value->MONTANT_PAIEMENT),'',' '),
        'EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'=>$value->EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }
    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>'.lang('messages_lang.categorie_salarie').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.type_salarie').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.label_mois').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.labelle_mot').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.labelle_montant').'&emsp;&emsp;&emsp;&emsp;</th>
    <th>'.lang('messages_lang.labelle_beneficiaire_salary').'&emsp;&emsp;&emsp;&emsp;</th>
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
          $html.='<tr>
          <td>'.$j.'</td>
         
          <td>'.$items['DESC_CATEGORIE_SALAIRE'].'</td>
          <td>'.$items['DESC_TYPE_SALAIRE'].'</td>
          <td>'.$items['DESC_MOIS'].'</td>
          <td>'.$items['MOTIF_PAIEMENT'].'</td>
          <td>'.$items['DESC_BENEFICIAIRE'].'</td>
          <td>'.$items['MONTANT_PAIEMENT'].'</td>
          <td>
          <a href="javascript:void(0)" onclick="show_modal('.$items['EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
          </td>
          <td>
          <input type="hidden" id="rowid'.$items['EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'].'" value='.$items['EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID'].'>
          </td>
          </tr>';        
        }

        $j++;
        $i++;
    endforeach;
    $html.=' </tbody>
    </table>';
    if ($i>0) 
    {
      $output = array('nbr' => $i, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
    else
    {
      $html= '';
      $output = array('nbr' => $i, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
  }

  public function save_autre_retenu()
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
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $bind_data = $this->getBindParms('EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID,CATEGORIE_SALAIRE_ID,TYPE_SALAIRE_ID,MOIS_ID,MOTIF_PAIEMENT,MONTANT_PAIEMENT,BENEFICIAIRE_TITRE_ID','execution_budgetaire_autre_retenue_tempo','USER_ID='.$user_id,'1');
    $bind_data= $this->ModelPs->getRequete($callpsreq, $bind_data);

    $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
    foreach($bind_data as $val)
    {
      $get_exec="SELECT exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID." AND MOIS_ID=".$val->MOIS_ID." AND CATEGORIE_SALAIRE_ID=".$val->CATEGORIE_SALAIRE_ID;
      $get_exec = 'CALL `getTable`("'.$get_exec.'");';
      $get_exec= $this->ModelPs->getRequeteOne($get_exec);

      $columsinsert="EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,MOTIF_PAIEMENT,MONTANT_PAIEMENT,BENEFICIAIRE_TITRE_ID,IS_TD_NET";
      $datatoinsert= $get_exec['EXECUTION_BUDGETAIRE_ID'].','.$get_exec['EXECUTION_BUDGETAIRE_DETAIL_ID'].',20,"'.$val->MOTIF_PAIEMENT.'","'.$val->MONTANT_PAIEMENT.'",'.$val->BENEFICIAIRE_TITRE_ID.',1';
      $table='execution_budgetaire_titre_decaissement';
      $this->save_all_table($table,$columsinsert,$datatoinsert);

      $db = db_connect();
      $critere ="EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID =" .$val->EXECUTION_BUDGETAIRE_AUTRE_RETENU_ID;
      $table="execution_budgetaire_autre_retenue_tempo";
      $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
      $deleteRequete = "CALL `deleteData`(?,?);";
      $this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
    }    

    return redirect('double_commande_new/Liquidation_Salaire_Liste/liste_autre_retenu');
  }

  public function save_benef()
  {
    $session  = \Config\Services::session();
    $USER_ID='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    $rules = [
      'CATEGORIE_SALAIRE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],      
      'TYPE_SALAIRE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'MOIS_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'BENEFICIAIRE_TITRE_ID' => [
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
      $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');
      $MOIS_ID=$this->request->getPost('MOIS_ID');
      $CATEGORIE_SALAIRE_ID=$this->request->getPost('CATEGORIE_SALAIRE_ID');
      $BENEFICIAIRE_TITRE_ID=$this->request->getPost('BENEFICIAIRE_TITRE_ID');
      $get_exec="SELECT EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire WHERE ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID." AND MOIS_ID=".$MOIS_ID." AND CATEGORIE_SALAIRE_ID=".$CATEGORIE_SALAIRE_ID;
      $get_exec = 'CALL `getTable`("'.$get_exec.'");';
      $get_exec= $this->ModelPs->getRequeteOne($get_exec);

      if(!empty($get_exec['EXECUTION_BUDGETAIRE_ID']))
      {
        $table="execution_budgetaire_salaire_beneficiaire";
        $columns="EXECUTION_BUDGETAIRE_ID,BENEFICIAIRE_TITRE_ID";
        $data=$get_exec['EXECUTION_BUDGETAIRE_ID'].",".$BENEFICIAIRE_TITRE_ID;
        $this->save_all_table($table,$columns,$data);

        $data=['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liquidation_Salaire_Liste/index_Deja_valider');
      }
      else
      {
        $data=['message' => "Pas d'execution correspondant à votre recherche"];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liquidation_Salaire/add_benef');
      }    
    }
    else
    {
      return $this->add_benef();
    }
  }
  // affiche le view pour la correction de l'etape d'engagement salaire
  function add_correction_view($id=0)
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

    // if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE')!=1)
    // {
    //   return redirect('Login_Ptba/homepage');
    // }
    
    $callpsreq = "CALL `getRequete`(?,?,?,?);";    
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID', 'user_affectaion', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $dataa=$this->converdate();
    $TRIMESTRE_ID = $dataa['TRIMESTRE_ID'];
    $trim=$this->converdate();
    $data['TRIMESTRE_ID']=$trim['TRIMESTRE_ID'];

    $getdonnees = "SELECT EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID,exec.ANNEE_BUDGETAIRE_ID,exec.EXECUTION_BUDGETAIRE_ID,sous_titre.INSTITUTION_ID,st.SOUS_TUTEL_ID,exec.MOIS_ID,exec.CATEGORIE_SALAIRE_ID,exec.TYPE_SALAIRE_ID,td.ETAPE_DOUBLE_COMMANDE_ID,etape.DESC_ETAPE_DOUBLE_COMMANDE,sous_titre.INSS_P,sous_titre.INSS_RP,sous_titre.ONPR,sous_titre.MFP,sous_titre.IMPOT,sous_titre.AUTRES_RETENUS,sous_titre.NET,td.EXECUTION_BUDGETAIRE_DETAIL_ID,td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,QTE_FONCTION_PUBLIQUE,QTE_RESSOURCES_HUMAINES FROM execution_budgetaire exec JOIN execution_budgetaire_salaire_sous_titre sous_titre ON exec.EXECUTION_BUDGETAIRE_ID=sous_titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=sous_titre.INSTITUTION_ID JOIN inst_institutions_sous_tutel st ON st.SOUS_TUTEL_ID=sous_titre.SOUS_TUTEL_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=sous_titre.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande etape ON etape.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 AND md5(EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID) = '".$id."'";
    $getdonnees = 'CALL `getTable`("'.$getdonnees.'");';
    $data['getdonnees']= $this->ModelPs->getRequeteOne($getdonnees);

    $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$data['getdonnees']['ETAPE_DOUBLE_COMMANDE_ID'],' ETAPE_DOUBLE_COMMANDE_ID DESC');
    $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);
    $data['etape1'] = $data['getdonnees']['DESC_ETAPE_DOUBLE_COMMANDE'];

    $get_data = "SELECT exec.PTBA_TACHE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,DESC_TACHE,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4,MONTANT_LIQUIDATION,QTE,RESULTAT_ATTENDUS FROM execution_budgetaire_execution_tache exec JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exec.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire exec_budg ON exec_budg.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID WHERE tache.SOUS_TUTEL_ID=".$data['getdonnees']['SOUS_TUTEL_ID']." AND MOIS_ID=".$data['getdonnees']['MOIS_ID']." AND CATEGORIE_SALAIRE_ID=".$data['getdonnees']['CATEGORIE_SALAIRE_ID']." AND exec_budg.ANNEE_BUDGETAIRE_ID=".$data['getdonnees']['ANNEE_BUDGETAIRE_ID'];
    $get_data = 'CALL `getTable`("'.$get_data.'");';
    $data['get_data']= $this->ModelPs->getRequete($get_data);
    $INSTITUTION_ID=$data['getdonnees']['INSTITUTION_ID'];

    $getInst  = 'SELECT TYPE_INSTITUTION_ID,INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID = '.$INSTITUTION_ID.' ORDER BY CODE_INSTITUTION ASC';
    $getInst = "CALL `getTable`('" . $getInst . "');";
    $data['institutions'] = $this->ModelPs->getRequete($getInst);

    $getSousTutel  = 'SELECT SOUS_TUTEL_ID,INSTITUTION_ID,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE SOUS_TUTEL_ID = '.$data['getdonnees']['SOUS_TUTEL_ID'].' ORDER BY DESCRIPTION_SOUS_TUTEL  ASC';
    $getSousTutel = "CALL `getTable`('" . $getSousTutel . "');";
    $data['sousTutel'] = $this->ModelPs->getRequete($getSousTutel);

    $getCateg = 'SELECT CATEGORIE_SALAIRE_ID,DESC_CATEGORIE_SALAIRE FROM categorie_salaire WHERE CATEGORIE_SALAIRE_ID='.$data['getdonnees']['CATEGORIE_SALAIRE_ID'].' ORDER BY CATEGORIE_SALAIRE_ID ASC';
    $getCateg = "CALL `getTable`('" . $getCateg . "');";
    $data['categorie'] = $this->ModelPs->getRequete($getCateg);

    $gettype = 'SELECT TYPE_SALAIRE_ID,DESC_TYPE_SALAIRE FROM type_salairie WHERE TYPE_SALAIRE_ID='.$data['getdonnees']['TYPE_SALAIRE_ID'].' ORDER BY TYPE_SALAIRE_ID ASC';
    $gettype = "CALL `getTable`('" . $gettype . "');";
    $data['type'] = $this->ModelPs->getRequete($gettype);

    $get_mois  = 'SELECT MOIS_ID,DESC_MOIS FROM mois WHERE TRIMESTRE_ID='.$TRIMESTRE_ID.' AND MOIS_ID='.$data['getdonnees']['MOIS_ID'].' ORDER BY MOIS_ID ASC';
    $get_mois = "CALL `getTable`('" . $get_mois . "');";
    $data['get_mois']= $this->ModelPs->getRequete($get_mois);

    return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Correction_View',$data);
  }

  //save engagement des salaires
  function save_correction_salaire()
  {
    $session  = \Config\Services::session();
    $USER_ID='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }
    
    $rules = [
      'INSTITUTION_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'SOUS_TITRE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'CATEGORIE_SALAIRE_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'MOIS_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'NET' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'QTE_FONCTION_PUBLIQUE' => [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
          ]
        ],     

      'QTE_RESSOURCES_HUMAINES'=> [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    $getdata=$this->request->getPost("getdata");
    $getdata = json_decode($getdata, true);

    foreach($getdata as $key)
    {
      $MONT=$this->request->getPost("MONTANT_LIQUIDE".$key['PTBA_TACHE_ID']);
      if (empty($MONT))
      {
        $rules['MONTANT_LIQUIDE'.$key["PTBA_TACHE_ID"]] = [
          'label' => '',
          'rules' => 'required',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
          ]
        ];
      }      
    }      

    $this->validation->setRules($rules);    
    $EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID =$this->request->getPost('EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID');

    if($this->validation->withRequest($this->request)->run())
    {
      $EXECUTION_BUDGETAIRE_ID=$this->request->getPost("EXECUTION_BUDGETAIRE_ID");
      $EXECUTION_BUDGETAIRE_DETAIL_ID=$this->request->getPost("EXECUTION_BUDGETAIRE_DETAIL_ID");
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost("EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID");
      $INSTITUTION_ID=$this->request->getPost("INSTITUTION_ID");
      $SOUS_TITRE_ID=$this->request->getPost("SOUS_TITRE_ID");
      $MOIS_ID=$this->request->getPost("MOIS_ID");
      $ETAPE_DOUBLE_COMMANDE=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $TYPE_SALAIRE_ID=$this->request->getPost('TYPE_SALAIRE_ID');
      $CATEGORIE_SALAIRE_ID=$this->request->getPost('CATEGORIE_SALAIRE_ID');
      $EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID =$this->request->getPost('EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID');
      $INSS_P=str_replace(' ','',$this->request->getPost("INSS_P"));
      $INSS_RP=str_replace(' ','',$this->request->getPost("INSS_RP"));
      $ONPR=str_replace(' ','',$this->request->getPost("ONPR"));
      $MFP=str_replace(' ','',$this->request->getPost("MFP"));
      $IMPOT=str_replace(' ','',$this->request->getPost("IMPOT"));
      $AUTRES_RETENUS=str_replace(' ','',$this->request->getPost("AUTRES_RETENUS"));
      $NET=str_replace(' ','',$this->request->getPost("NET"));
      $QTE_FONCTION_PUBLIQUE=$this->request->getPost("QTE_FONCTION_PUBLIQUE");
      $QTE_RESSOURCES_HUMAINES=$this->request->getPost("QTE_RESSOURCES_HUMAINES");

      $TOTAL_RUBRIQUE=floatval($INSS_P)+floatval($INSS_RP)+floatval($ONPR)+floatval($MFP)+floatval($IMPOT)+floatval($AUTRES_RETENUS)+floatval($NET);      

      $psgetrequete = "CALL `getRequete`(?,?,?,?)";
      $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID','execution_budgetaire_etape_double_commande_config','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_DOUBLE_COMMANDE.' AND IS_CORRECTION=0','ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
      $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
      $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

      $QTE_FONC_PUB=0;
      $QTE_RESS_HUM=0;
      $MONTANT_LIQUIDE=0;
      
      foreach($getdata as $key)
      {
        $LIQUIDE=$this->request->getPost("MONTANT_LIQUIDE".$key['PTBA_TACHE_ID']);
        $LIQUIDE=str_replace(' ','',$LIQUIDE);

        $MONTANT_RESTANT=$this->request->getPost("MONTANT_RESTANT".$key['PTBA_TACHE_ID']);
        $MONTANT_RESTANT=str_replace(' ','',$LIQUIDE);

        if($LIQUIDE>$MONTANT_RESTANT)
        {
          $data=['message' => "".lang('messages_lang.mount_sup').""];
          session()->setFlashdata('alert', $data);
          return $this->add_correction_view(md5($EXECUTION_BUDGETAIRE_ID));
        }
        $MONTANT_LIQUIDE +=intval($LIQUIDE);
      }

      if($TOTAL_RUBRIQUE!=$MONTANT_LIQUIDE)
      {
        $data=['message' => "".lang('messages_lang.mont_rubr_differ').""];
        session()->setFlashdata('alert', $data);
        return $this->add();
      }

      $DEVISE_TYPE_HISTO_ENG_ID=1;
      $LIQUIDATION_TYPE_ID=2;
      $EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3;
      $ANNEE_BUDGETAIRE_ID=$this->get_annee_budgetaire();
      //$TRIMESTRE_ID=$this->converdate()['TRIMESTRE_ID'];
      $getmois ="SELECT TRIMESTRE_ID FROM mois WHERE MOIS_ID=".$MOIS_ID;
      $getmois = 'CALL `getTable`("'.$getmois.'");';
      $getmois= $this->ModelPs->getRequeteOne($getmois);
      $TRIMESTRE_ID=$getmois['TRIMESTRE_ID'];
      $DATE_BON_ENGAGEMENT=date('Y-m-d');
      $DATE_ENG_JURIDIQUE=date('Y-m-d');    

      foreach($getdata as $key)
      {
        $LIQUIDE=$this->request->getPost("MONTANT_LIQUIDE".$key['PTBA_TACHE_ID']);
        $LIQUIDE=str_replace(' ','',$LIQUIDE);
        $LIQUIDE=intval($LIQUIDE);

        //budget engage avant la correction
        $MoneyEngag  = 'SELECT MONTANT_LIQUIDATION,EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' AND PTBA_TACHE_ID='.$key['PTBA_TACHE_ID'].'';
        $MoneyEngag = "CALL `getTable`('".$MoneyEngag."');";
        $MoneyEngag = $this->ModelPs->getRequeteOne($MoneyEngag);

        // retrancher l'argent dans ptba
        $MoneyRest  = 'SELECT ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T1,BUDGET_UTILISE_T2,BUDGET_UTILISE_T3,BUDGET_UTILISE_T4 FROM ptba_tache WHERE PTBA_TACHE_ID='.$key['PTBA_TACHE_ID'].' AND ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
        $MoneyRest = "CALL `getTable`('" . $MoneyRest . "');";
        $RestPTBA = $this->ModelPs->getRequeteOne($MoneyRest);

        $whereptba ="PTBA_TACHE_ID = ".$key['PTBA_TACHE_ID'];        
        $insertIntoptba='ptba_tache';
        if(!empty($MoneyEngag))
        {
          if ($TRIMESTRE_ID==1)
          {
            $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T1']) + $MoneyEngag['MONTANT_LIQUIDATION'] - floatval($LIQUIDE);
            $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T1']) -$MoneyEngag['MONTANT_LIQUIDATION'] + floatval($LIQUIDE);

            $columptba="BUDGET_RESTANT_T1=".$apresEng.",BUDGET_UTILISE_T1=".$total_utilise;
            $this->update_all_table($insertIntoptba,$columptba,$whereptba);
          }
          else if ($TRIMESTRE_ID==2)
          {
            $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T2'])+$MoneyEngag['MONTANT_LIQUIDATION'] - floatval($LIQUIDE);
            $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T2']) + $MoneyEngag['MONTANT_LIQUIDATION']- floatval($LIQUIDE);

            $columptba="BUDGET_RESTANT_T2=".$apresEng.",BUDGET_UTILISE_T2=".$total_utilise;
            $this->update_all_table($insertIntoptba,$columptba,$whereptba);
          }
          else if ($TRIMESTRE_ID==3)
          {
            $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T3']) + $MoneyEngag['MONTANT_LIQUIDATION']- floatval($LIQUIDE);
            $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T3']) +$MoneyEngag['MONTANT_LIQUIDATION']- floatval($LIQUIDE);

            $columptba="BUDGET_RESTANT_T3=".$apresEng.",BUDGET_UTILISE_T3=".$total_utilise;
            $this->update_all_table($insertIntoptba,$columptba,$whereptba);
          }
          else if ($TRIMESTRE_ID==4)
          {
            $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T4']) +$MoneyEngag['MONTANT_LIQUIDATION']- floatval($LIQUIDE);
            $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T4']) +$MoneyEngag['MONTANT_LIQUIDATION']- floatval($LIQUIDE);
            
            $columptba="BUDGET_RESTANT_T4=".$apresEng.",BUDGET_UTILISE_T4=".$total_utilise;
            $this->update_all_table($insertIntoptba,$columptba,$whereptba);
          }

          //RESULTAT_ATTENDUS = QTE_RESSOURCES_HUMAINES && QTE =QTE_FONCTION_PUBLIQUE
          $datacolums="EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID.",PTBA_TACHE_ID=".$key['PTBA_TACHE_ID'].",MONTANT_ENG_BUDGETAIRE=".$LIQUIDE.",MONTANT_ENG_JURIDIQUE=".$LIQUIDE.",MONTANT_LIQUIDATION=".$LIQUIDE.",USER_ID=".$USER_ID;
          $insertIntotache="execution_budgetaire_execution_tache";
          $where="EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID=".$MoneyEngag['EXECUTION_BUDGETAIRE_EXECUTION_TACHE_ID'];  
          $this->update_all_table($insertIntotache,$datacolums,$where);   
        }
        else
        {
          $QTE_FONCTION_PUBLIQUE=$this->request->getPost("QTE_FONCTION_PUBLIQUE".$key['PTBA_TACHE_ID']);
          $QTE_RESSOURCES_HUMAINES=$this->request->getPost("QTE_RESSOURCES_HUMAINES".$key['PTBA_TACHE_ID']);
          //RESULTAT_ATTENDUS = QTE_RESSOURCES_HUMAINES && QTE =QTE_FONCTION_PUBLIQUE
          $datacolums=$EXECUTION_BUDGETAIRE_ID.",".$key['PTBA_TACHE_ID'].",".$LIQUIDE.",".$LIQUIDE.",".$LIQUIDE.",".$USER_ID;
          $insertIntotache="execution_budgetaire_execution_tache";
          $columns="EXECUTION_BUDGETAIRE_ID,PTBA_TACHE_ID,MONTANT_ENG_BUDGETAIRE,MONTANT_ENG_JURIDIQUE,MONTANT_LIQUIDATION,USER_ID";
          $this->save_all_table($insertIntotache,$columns,$datacolums);

          // retrancher l'argent dans ptba
          $MoneyRest  = 'SELECT ANNEE_BUDGETAIRE_ID,PTBA_TACHE_ID,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_UTILISE_T1,BUDGET_UTILISE_T2,BUDGET_UTILISE_T3,BUDGET_UTILISE_T4 FROM ptba_tache WHERE PTBA_TACHE_ID='.$key['PTBA_TACHE_ID'].' AND ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
          $MoneyRest = "CALL `getTable`('" . $MoneyRest . "');";
          $RestPTBA = $this->ModelPs->getRequeteOne($MoneyRest);

          $whereptba ="PTBA_TACHE_ID = ".$key['PTBA_TACHE_ID'];        
          $insertIntoptba='ptba_tache';
          if ($TRIMESTRE_ID==1)
          {
            $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T1']) - floatval($LIQUIDE);
            $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T1']) + floatval($LIQUIDE);

            $columptba="BUDGET_RESTANT_T1=".$apresEng.",BUDGET_UTILISE_T1=".$total_utilise;
            $this->update_all_table($insertIntoptba,$columptba,$whereptba);
          }
          else if ($TRIMESTRE_ID==2)
          {
            $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T2']) - floatval($LIQUIDE);
            $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T2']) + floatval($LIQUIDE);

            $columptba="BUDGET_RESTANT_T2=".$apresEng.",BUDGET_UTILISE_T2=".$total_utilise;
            $this->update_all_table($insertIntoptba,$columptba,$whereptba);
          }
          else if ($TRIMESTRE_ID==3)
          {
            $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T3']) - floatval($LIQUIDE);
            $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T3']) + floatval($LIQUIDE);

            $columptba="BUDGET_RESTANT_T3=".$apresEng.",BUDGET_UTILISE_T3=".$total_utilise;
            $this->update_all_table($insertIntoptba,$columptba,$whereptba);
          }
          else if ($TRIMESTRE_ID==4)
          {
            $apresEng = floatval($RestPTBA['BUDGET_RESTANT_T4']) - floatval($LIQUIDE);
            $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T4']) + floatval($LIQUIDE);
            
            $columptba="BUDGET_RESTANT_T4=".$apresEng.",BUDGET_UTILISE_T4=".$total_utilise;
            $this->update_all_table($insertIntoptba,$columptba,$whereptba);
          } 
        }
      }

      //somme engage apres la modification
      $MoneyEngagExec = 'SELECT SUM(MONTANT_LIQUIDATION) AS MONTANT_LIQUIDATION FROM execution_budgetaire_execution_tache WHERE EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.'';
      $MoneyEngagExec = "CALL `getTable`('".$MoneyEngagExec."');";
      $MoneyEngagExec = $this->ModelPs->getRequeteOne($MoneyEngagExec);
      $LIQUIDATION=$MoneyEngagExec['MONTANT_LIQUIDATION'];

      //somme retenu apres la modification
      $MoneyRetenu = 'SELECT SUM(INSS_P) AS INSS_P,SUM(INSS_RP) AS INSS_RP,SUM(ONPR) AS ONPR,SUM(MFP) AS MFP,SUM(IMPOT) AS IMPOT,SUM(AUTRES_RETENUS) AS AUTRES_RETENUS,SUM(NET) AS NET,SUM(QTE_FONCTION_PUBLIQUE) AS QTE_FONCTION_PUBLIQUE,SUM(QTE_RESSOURCES_HUMAINES) AS QTE_RESSOURCES_HUMAINES FROM execution_budgetaire_salaire_sous_titre WHERE EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.'';
      $MoneyRetenu = "CALL `getTable`('".$MoneyRetenu."');";
      $MoneyRetenu = $this->ModelPs->getRequeteOne($MoneyRetenu);

      //insertion dans execution_budgetaire_salaire_sous_titre
      $cond="EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID=".$EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID;
      $UpdateIntoSt='execution_budgetaire_salaire_sous_titre';
      $datacolumsSt="TOTAL_SALAIRE=".$MONTANT_LIQUIDE.",INSS_P=".$INSS_P.",INSS_RP=".$INSS_RP.",ONPR=".$ONPR.",MFP=".$MFP.",IMPOT=".$IMPOT.",AUTRES_RETENUS=".$AUTRES_RETENUS.",NET=".$NET.",QTE_FONCTION_PUBLIQUE=".$QTE_FONCTION_PUBLIQUE.",QTE_RESSOURCES_HUMAINES=".$QTE_RESSOURCES_HUMAINES.",A_CORRIGER=0";
      $this->update_all_table($UpdateIntoSt,$datacolumsSt,$cond);

      $where="EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;
      $insertIntoexec='execution_budgetaire';    
      $datacolums="ENG_BUDGETAIRE=".$LIQUIDATION.",DEVISE_TYPE_HISTO_ENG_ID=".$DEVISE_TYPE_HISTO_ENG_ID.",DATE_BON_ENGAGEMENT='".$DATE_BON_ENGAGEMENT."',ENG_JURIDIQUE=".$LIQUIDATION.",DATE_ENG_JURIDIQUE='".$DATE_ENG_JURIDIQUE."',DEVISE_TYPE_HISTO_JURD_ID=".$DEVISE_TYPE_HISTO_ENG_ID.",LIQUIDATION_TYPE_ID=".$LIQUIDATION_TYPE_ID.",LIQUIDATION=".$LIQUIDATION.",USER_ID=".$USER_ID.",EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=".$EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID.",MOIS_ID=".$MOIS_ID.",CATEGORIE_SALAIRE_ID=".$CATEGORIE_SALAIRE_ID.",TYPE_SALAIRE_ID=".$TYPE_SALAIRE_ID.",INSS_P=".$MoneyRetenu['INSS_P'].",INSS_RP=".$MoneyRetenu['INSS_RP'].",ONPR=".$MoneyRetenu['ONPR'].",MFP=".$MoneyRetenu['MFP'].",IMPOT=".$MoneyRetenu['IMPOT'].",AUTRES_RETENUS=".$MoneyRetenu['AUTRES_RETENUS'].",NET=".$MoneyRetenu['NET'];
      $this->update_all_table($insertIntoexec,$datacolums,$where);

      $insertIntodet='execution_budgetaire_tache_detail';
      $datacolumsdet="MONTANT_LIQUIDATION=".$MONTANT_LIQUIDE;
      $this->update_all_table($insertIntodet,$datacolumsdet,$where);

      //modification dans execution_budgetaire_titre_decaissement
      $get_non_corriger = 'SELECT EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID FROM execution_budgetaire_salaire_sous_titre WHERE EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID.' AND A_CORRIGER=1';
      $get_non_corriger = "CALL `getTable`('".$get_non_corriger."');";
      $get_non_corriger = $this->ModelPs->getRequete($get_non_corriger);
      if(empty($get_non_corriger['EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID']))
      {
        $conditions="EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID;
        $updateIntoTD='execution_budgetaire_titre_decaissement';
        $datacolumsTDupdate="ETAPE_DOUBLE_COMMANDE_ID=".$NEXT_ETAPE_ID;
        $this->update_all_table($updateIntoTD,$datacolumsTDupdate,$conditions);
      }

      $insertIntoOp='execution_budgetaire_tache_detail_histo';
      $columOp="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID";
      $datacolumsOp=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_DOUBLE_COMMANDE.",".$USER_ID."";
      $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

      $data=['message' => "".lang('messages_lang.labelle_message_update_success').""];
        session()->setFlashdata('alert', $data);
      return redirect('double_commande_new/Liquidation_Salaire_Liste/index_A_Corr');
    }
    else
    {
      return $this->add_correction_view(md5($EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID));
    }
  }

  //affiche l'interface de comfirmation de l'engagement salaire
  function add_confirm($id=0)
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

    // if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
    // {
    //   return redirect('Login_Ptba/homepage'); 
    // }

    $infoAffiche  = 'SELECT exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire exec JOIN execution_budgetaire_titre_decaissement det ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE md5(exec.EXECUTION_BUDGETAIRE_ID) = "'.$id.'"';
    $infoAffiche = "CALL `getTable`('" . $infoAffiche . "');";
    $data['info']= $this->ModelPs->getRequeteOne($infoAffiche);
    
    $callpsreq = "CALL `getRequete`(?,?,?,?);";     
    $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID = '.$data['info']['ETAPE_DOUBLE_COMMANDE_ID'],' DESC_ETAPE_DOUBLE_COMMANDE DESC');
    $titre= $this->ModelPs->getRequeteOne($callpsreq, $titre);
    $data['etape2'] =$titre['DESC_ETAPE_DOUBLE_COMMANDE'];

    $data['EXECUTION_BUDGETAIRE_ID'] = $data['info']['EXECUTION_BUDGETAIRE_ID'];

    $operation  = 'SELECT ID_OPERATION,DESCRIPTION FROM budgetaire_type_operation_validation WHERE ID_OPERATION<>3 ORDER BY DESCRIPTION ASC';
    $operation = "CALL `getTable`('" . $operation . "');";
    $data['get_operation'] = $this->ModelPs->getRequete($operation);

    $rejet_motif='SELECT TYPE_ANALYSE_MOTIF_ID, DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif WHERE MOUVEMENT_DEPENSE_ID=3 AND IS_SALAIRE = 1';
    $rejet_motif = "CALL `getTable`('" .$rejet_motif. "');";
    $data['get_motif']= $this->ModelPs->getRequete($rejet_motif);

    $get_st_a_corriger='SELECT st.SOUS_TUTEL_ID, DESCRIPTION_SOUS_TUTEL,CODE_INSTITUTION_SOUS_TUTEL FROM inst_institutions_sous_tutel sous_tutel JOIN execution_budgetaire_salaire_sous_titre st ON st.SOUS_TUTEL_ID=sous_tutel.SOUS_TUTEL_ID WHERE EXECUTION_BUDGETAIRE_ID='.$data['EXECUTION_BUDGETAIRE_ID'] ;
    $get_st_a_corriger = "CALL `getTable`('" .$get_st_a_corriger. "');";
    $data['get_st_a_corriger']= $this->ModelPs->getRequete($get_st_a_corriger);

    $trim=$this->converdate();
    $data['TRIMESTRE_ID']=$trim['TRIMESTRE_ID'];

    return view('App\Modules\double_commande_new\Views\Liquidation_Salaire_Confirmation_View',$data);
  }

  // insertion et update pour la 2em etape
  function save_confirm()
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

    // if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1)
    // {
    //   return redirect('Login_Ptba/homepage'); 
    // }

    $EXECUTION_BUDGETAIRE_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_ID');

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
      $rules['SOUS_TUTEL_ID'] = [
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
      $USER_ID=$user_id;

      $EXECUTION_BUDGETAIRE_DETAIL_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID');
      $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID');
      $DATE_RECEPTION=$this->request->getPost('DATE_RECEPTION');
      $DATE_TRANSMISSION=$this->request->getPost('DATE_TRANSMISSION');
      $ETAPE_DOUBLE_COMMANDE=$this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
      $ID_OPERATION = $this->request->getPost('ID_OPERATION');
      $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
      $COMMENTAIRE = addslashes($COMMENTAIRE);
      $motif='';

      $callpsreq = "CALL `getRequete`(?,?,?,?);";     
      $etape_suivante = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,IS_CORRECTION,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', ' ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_DOUBLE_COMMANDE,' ETAPE_DOUBLE_COMMANDE_SUIVANT_ID DESC');
      $etape_suivante22= $this->ModelPs->getRequete($callpsreq, $etape_suivante);

      foreach ($etape_suivante22 as $key)
      {
        if ($key->IS_CORRECTION == 0)
        {
          $ETAPE_DOUBLE_COMMANDE_ID=$key->ETAPE_DOUBLE_COMMANDE_SUIVANT_ID;
        } 
      }
      if ($ID_OPERATION==1)
      {
        $motif = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
        $st=$this->request->getPost('SOUS_TUTEL_ID[]');

        foreach ($etape_suivante22 as $key)
        {
          if ($key->IS_CORRECTION == 1)
          {
            $ETAPE_DOUBLE_COMMANDE_ID=$key->ETAPE_DOUBLE_COMMANDE_SUIVANT_ID;
          }
        }

        foreach($motif as $mot)
        {
          $insertIntomotif='execution_budgetaire_histo_operation_verification_motif';
          $colummotif="TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";

          $datacolumsmotif=$mot.",".$ETAPE_DOUBLE_COMMANDE.",".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."";
          $this->save_all_table($insertIntomotif,$colummotif,$datacolumsmotif);
        }

        foreach($st as $key)
        {
          $updateIntoStitre='execution_budgetaire_salaire_sous_titre';
          $datacolumsStitre="A_CORRIGER=1";
          $where="EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID." AND SOUS_TUTEL_ID=".$key;
          $this->update_all_table($updateIntoStitre,$datacolumsStitre,$where);
        }
      }

      $whereDet ="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = ".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;      
      $insertIntoDet='execution_budgetaire_titre_decaissement';
      $columDet="ETAPE_DOUBLE_COMMANDE_ID = ".$ETAPE_DOUBLE_COMMANDE_ID;
      $this->update_all_table($insertIntoDet,$columDet,$whereDet);

      $insertIntoOp='execution_budgetaire_tache_detail_histo';
      $columOp="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,OBSERVATION,DATE_RECEPTION,DATE_TRANSMISSION,MOTIF_REJET";
      $datacolumsOp=$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID.",".$ETAPE_DOUBLE_COMMANDE.",".$USER_ID.",'".$COMMENTAIRE."','".$DATE_RECEPTION."','".$DATE_TRANSMISSION."','".$COMMENTAIRE."'";
      $this->save_all_table($insertIntoOp,$columOp,$datacolumsOp);

      $data=['message' => "".lang('messages_lang.conf_succ').""];
      session()->setFlashdata('alert', $data);

      return redirect('double_commande_new/Liquidation_Salaire_Liste/index_Deja_valider');
    }
    else
    {
      return $this->add_confirm(md5($EXECUTION_BUDGETAIRE_ID));
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

  //listing tache par sous titre
  public function listing_st()
  {
    // if($this->session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    // {
    //   return redirect('Login_Ptba/homepage'); 
    // }

    $bouton = '';
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('DESCRIPTION_INSTITUTION', 'DESC_CATEGORIE_SALAIRE',1,'TOTAL_SALAIRE');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_INSTITUTION ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR DESC_CATEGORIE_SALAIRE LIKE '%$var_search%' OR TOTAL_SALAIRE LIKE '%$var_search%')") : '';

    //condition pour le query principale
    $conditions = $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $search . " " . $group;

    $requetedebase = "SELECT EXECUTION_BUDGETAIRE_SALAIRE_SOUS_TITRE_ID,exec.EXECUTION_BUDGETAIRE_ID,st.SOUS_TUTEL_ID,DESCRIPTION_INSTITUTION,CODE_INSTITUTION,DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL,categ.DESC_CATEGORIE_SALAIRE,SUM(TOTAL_SALAIRE) AS TOTAL_SALAIRE FROM execution_budgetaire exec JOIN execution_budgetaire_salaire_sous_titre sous_titre ON exec.EXECUTION_BUDGETAIRE_ID=sous_titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=sous_titre.INSTITUTION_ID JOIN inst_institutions_sous_tutel st ON st.SOUS_TUTEL_ID=sous_titre.SOUS_TUTEL_ID JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID WHERE 1 AND exec.EXECUTION_BUDGETAIRE_ID = ".$EXECUTION_BUDGETAIRE_ID." GROUP BY DESCRIPTION_INSTITUTION";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach($fetch_actions as $key)
    {
      $count_st = "SELECT exec.SOUS_TUTEL_ID AS nbr FROM execution_budgetaire_salaire_sous_titre exec  WHERE EXECUTION_BUDGETAIRE_ID=".$key->EXECUTION_BUDGETAIRE_ID." AND exec.SOUS_TUTEL_ID=".$key->SOUS_TUTEL_ID." GROUP BY SOUS_TUTEL_ID";
      $count_st = 'CALL `getTable`("'.$count_st.'");';
      $nbre_st = $this->ModelPs->getRequete($count_st);
      $nbre_st = (!empty($nbre_st))?count($nbre_st):0;

      $sub_array = array();
      $sub_array[]=$u++;
      $sub_array[]=$key->DESCRIPTION_INSTITUTION;
      $sub_array[]=$key->DESC_CATEGORIE_SALAIRE;
      $nbrs="<center><a href='javascript:void(0)'  class='btn btn-primary btn-sm' onclick='listing_par_sous_titre(".$key->EXECUTION_BUDGETAIRE_ID.",".$key->SOUS_TUTEL_ID.")'>".$nbre_st."</a></center>";
      $sub_array[] = $nbrs;
      $sub_array[]=number_format($key->TOTAL_SALAIRE,$this->get_precision($key->TOTAL_SALAIRE),',',' ');
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);
  }

  //listing sous titre par execution
  public function listing_par_sous_titre()
  {
    // if($this->session->get('SESSION_SUIVIE_PTBA_DECAISSEMENT') !=1)
    // {
    //   return redirect('Login_Ptba/homepage'); 
    // }

    $bouton = '';
    $profil_id = $this->session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $EXECUTION_BUDGETAIRE_ID=$this->request->getPost('EXECUTION_BUDGETAIRE_ID');
    $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column = array('DESCRIPTION_SOUS_TUTEL', 'TOTAL_SALAIRE');
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_SOUS_TUTEL ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESCRIPTION_SOUS_TUTEL LIKE '%$var_search%' OR TOTAL_SALAIRE LIKE '%$var_search%')") : '';

    //condition pour le query principale
    $conditions = $search . " " . $group . " " . $order_by . " " . $limit;
    // condition pour le query filter
    $conditionsfilter = $search . " " . $group;

    $requetedebase = "SELECT DESCRIPTION_SOUS_TUTEL,TOTAL_SALAIRE FROM execution_budgetaire_salaire_sous_titre st JOIN inst_institutions_sous_tutel titel ON titel.SOUS_TUTEL_ID=st.SOUS_TUTEL_ID WHERE EXECUTION_BUDGETAIRE_ID=".$EXECUTION_BUDGETAIRE_ID." AND st.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID." GROUP BY titel.SOUS_TUTEL_ID";

    $requetedebases = $requetedebase . " " . $conditions;
    $requetedebasefilter = $requetedebase . " " . $conditionsfilter;
    $query_secondaire = 'CALL getTable("' . $requetedebases . '");';
    $fetch_actions = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    foreach($fetch_actions as $row)
    {
      $sub_array = array();
      $sub_array[] = $row->DESCRIPTION_SOUS_TUTEL;
      $sub_array[] = number_format($row->TOTAL_SALAIRE,$this->get_precision($row->TOTAL_SALAIRE),',',' ');
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' . $requetedebase . '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' . $requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);
  }

  // trouver le code  a partir de sous titre choisit
  function get_data()
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
    //$trim=$this->converdate();
    //$TRIMESTRE_ID=$trim['TRIMESTRE_ID'];

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TITRE_ID=$this->request->getPost('SOUS_TITRE_ID');
    $CATEGORIE_SALAIRE_ID=$this->request->getPost('CATEGORIE_SALAIRE_ID');
    $MOIS_ID=$this->request->getPost('MOIS_ID');

    $getmois ="SELECT TRIMESTRE_ID FROM mois WHERE MOIS_ID=".$MOIS_ID;
    $getmois = 'CALL `getTable`("'.$getmois.'");';
    $getmois= $this->ModelPs->getRequeteOne($getmois);
    $TRIMESTRE_ID=$getmois['TRIMESTRE_ID'];

    $critere="AND tache.INSTITUTION_ID=".$INSTITUTION_ID;
    if(!empty($SOUS_TITRE_ID))
    {
      $critere.=" AND tache.SOUS_TUTEL_ID=".$SOUS_TITRE_ID;
    }

    $ANNEE_BUDGETAIRE_ID=$this->get_annee_budgetaire();

    //1:sous status 2:sous contrat 3:enseignant
    $getdonnees='';
    if(!empty($CATEGORIE_SALAIRE_ID))
    {
      if($CATEGORIE_SALAIRE_ID==1)
      {
        $getdonnees = "SELECT PTBA_TACHE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,activ.DESC_PAP_ACTIVITE,DESC_TACHE,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4,soulit.SOUS_LITTERA_ID FROM ptba_tache tache JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID LEFT JOIN pap_activites activ ON activ.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 ".$critere." AND CODE_SOUS_LITTERA IN (61110,61160,61140,61610) AND ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
        $getqte="SELECT `QT1`,`QT2`,`QT3`,`QT4` FROM ptba_tache tache JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 ".$critere." AND soulit.CODE_SOUS_LITTERA=61110 AND ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
      }
      elseif($CATEGORIE_SALAIRE_ID==2)
      {
        $getdonnees = "SELECT PTBA_TACHE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,activ.DESC_PAP_ACTIVITE,DESC_TACHE,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4,soulit.SOUS_LITTERA_ID FROM ptba_tache tache JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID LEFT JOIN pap_activites activ ON activ.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 ".$critere." AND CODE_SOUS_LITTERA IN (61210,61240,61260,61620) AND ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
        $getqte="SELECT `QT1`,`QT2`,`QT3`,`QT4` FROM ptba_tache tache JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 ".$critere." AND soulit.CODE_SOUS_LITTERA=61210 AND ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
      }
      else
      {
        $getdonnees = "SELECT PTBA_TACHE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,activ.DESC_PAP_ACTIVITE,DESC_TACHE,BUDGET_RESTANT_T1,BUDGET_RESTANT_T2,BUDGET_RESTANT_T3,BUDGET_RESTANT_T4,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4,soulit.SOUS_LITTERA_ID FROM ptba_tache tache JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID LEFT JOIN pap_activites activ ON activ.PAP_ACTIVITE_ID=tache.PAP_ACTIVITE_ID JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 ".$critere." AND CODE_SOUS_LITTERA IN (61110,61160,61140,61610,61210,61240,61260,61620) AND ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
        $getqte="SELECT `QT1`,`QT2`,`QT3`,`QT4` FROM ptba_tache tache JOIN class_economique_sous_littera soulit ON soulit.SOUS_LITTERA_ID=tache.SOUS_LITTERA_ID WHERE 1 ".$critere." AND soulit.CODE_SOUS_LITTERA IN (61110,61210) AND ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
      }
    }   
    
    $getdata = 'CALL `getTable`("'.$getdonnees.'");';
    $get_data= $this->ModelPs->getRequete($getdata);

    $getqt = 'CALL `getTable`("'.$getqte.'");';
    $getqt= $this->ModelPs->getRequeteOne($getqt);

    $html='';
    $QTE="";
    foreach($get_data as $key)
    {
      $MONTANT_RESTANT='';
      $MONTANT_VOTE='';
      
      if($TRIMESTRE_ID==1)
      {
        $MONTANT_RESTANT=$key->BUDGET_RESTANT_T1;
        $MONTANT_VOTE=$key->BUDGET_T1;
        $QTE=$getqt['QT1'];
      }
      elseif($TRIMESTRE_ID==2)
      {
        $MONTANT_RESTANT=$key->BUDGET_RESTANT_T2;
        $MONTANT_VOTE=$key->BUDGET_T2;
        $QTE=$getqt['QT2'];
      }
      elseif($TRIMESTRE_ID==3)
      {
        $MONTANT_RESTANT=$key->BUDGET_RESTANT_T3;
        $MONTANT_VOTE=$key->BUDGET_T3;
        $QTE=$getqt['QT3'];
      }
      elseif($TRIMESTRE_ID==4)
      {
        $MONTANT_RESTANT=$key->BUDGET_RESTANT_T4;
        $MONTANT_VOTE=$key->BUDGET_T4;
        $QTE=$getqt['QT4'];
      }

      $DESC_PAP_ACTIVITE='';
      if(!empty($key->DESC_PAP_ACTIVITE))
      {
        $DESC_PAP_ACTIVITE=$key->DESC_PAP_ACTIVITE;
      }
      else
      {
        $DESC_PAP_ACTIVITE="-";
      }

      $html.='<tr>
        <td>'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'</td>
        <td>'.$key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'</td>
        <td>'.$key->DESC_TACHE.'</td>
        <td>'.$MONTANT_VOTE.'</td>
        <td>'.number_format($MONTANT_RESTANT,$this->get_precision($MONTANT_RESTANT),'.',' ').'
          <input type="hidden" name="MONTANT_RESTANT'.$key->PTBA_TACHE_ID.'" id="MONTANT_RESTANT'.$key->PTBA_TACHE_ID.'" value='.$MONTANT_RESTANT.'>
          </td>
        <td><input type="text" oninput="calculer('.$key->PTBA_TACHE_ID.');formatInputValue(this)" name="MONTANT_LIQUIDE'.$key->PTBA_TACHE_ID.'" id="MONTANT_LIQUIDE'.$key->PTBA_TACHE_ID.'" class="form-control">
        <font color="red" id="error_MONTANT_LIQUIDE'.$key->PTBA_TACHE_ID.'"></font></td>
      <tr>';
    }    
    $output = array("html" => $html,"getdata"=>$get_data,"qteRHR"=>$QTE);
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
}
?>