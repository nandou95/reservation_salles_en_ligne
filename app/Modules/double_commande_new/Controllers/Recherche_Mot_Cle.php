<?php

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

ini_set('max_execution_time', 4000);
ini_set('memory_limit','2048M'); 
class Recherche_Mot_Cle extends BaseController
{
  protected $session;
  protected $ModelPs;
  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
  }

  public function index()
  {
    $session=\Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }
    $data=$this->urichk();
    return view('App\Modules\double_commande_new\Views\Recherche_Mot_Cle_View',$data);
  }

  public function getInfo()
  {
    $callpsreq = "CALL getRequete(?,?,?,?);";
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

    $value=$this->request->getPost('value');
    // $value=str_replace("'","\'",$value);
    $value=addslashes($value);
    $html='';
    if(!empty($value))
    {
      //get sous titre
      $get_sous_t="SELECT SOUS_TUTEL_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL FROM inst_institutions_sous_tutel WHERE DESCRIPTION_SOUS_TUTEL LIKE '%{$value}%'";
      $get_sous_t='CALL `getTable`("'.$get_sous_t.'")';
      $get_sous_t= $this->ModelPs->getRequete($get_sous_t);

      if(!empty($get_sous_t))
      {
        $u=1;
        $html .='<div class="table-responsive">
                <table class="table table-striped">
                <thead>
                  <tr>
                    <th>'.lang('messages_lang.table_st').' ('.count($get_sous_t).')</th>
                  </tr>
                </thead>
                <tbody>';
        foreach ($get_sous_t as $val)
        {
          $html .='<tr>
              <td><a href="'.base_url("double_commande_new/Recherche_Mot_Cle/getInfo_sousTitre/")."/".md5($val->SOUS_TUTEL_ID).'">'.$val->CODE_SOUS_TUTEL.' - '.$val->DESCRIPTION_SOUS_TUTEL.'</a></td>
          </tr>';
        }

        $html .='</tbody></table></div>';
      }

      //get ligne budgetaire
      $get_ligne = "SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE LIKE '%{$value}%' OR LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE LIKE '%{$value}%'";
      $get_ligne='CALL `getTable`("'.$get_ligne.'")';
      $get_ligne= $this->ModelPs->getRequete($get_ligne);

      if(!empty($get_ligne))
      {
        $u=1;
        $html .='<div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                    <tr>
                      <th>'.lang('messages_lang.label_ligne').' ('.count($get_ligne).')</th>
                    </tr>
                    </thead>
                  <tbody>';
        foreach ($get_ligne as $key)
        {
          $html .='<tr>
              <td><a href="'.base_url("double_commande_new/Recherche_Mot_Cle/getInfo_ligne/")."/".md5($key->CODE_NOMENCLATURE_BUDGETAIRE_ID).'">'.$key->CODE_NOMENCLATURE_BUDGETAIRE.' - '.$key->LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE.'</a></td>
          </tr>';
        }  
        $html .='</tbody></table></div>';  
      }

      //get activite
      $get_activite = "SELECT PAP_ACTIVITE_ID,DESC_PAP_ACTIVITE FROM pap_activites WHERE DESC_PAP_ACTIVITE LIKE '%{$value}%'";
      $get_activite='CALL `getTable`("'.$get_activite.'")';
      $get_activite= $this->ModelPs->getRequete($get_activite);

      if(!empty($get_activite))
      {
        $u=1;
        $html .='<div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                    <tr>
                      <th>'.lang('messages_lang.table_activite').' ('.count($get_activite).')</th>
                    </tr>
                    </thead>
                  <tbody>';
        foreach ($get_activite as $key)
        {
          $html .='<tr>
              <td><a href="'.base_url("double_commande_new/Recherche_Mot_Cle/getInfo_activite/")."/".md5($key->PAP_ACTIVITE_ID).'">'.$key->DESC_PAP_ACTIVITE.'</a></td>
          </tr>';
        }  
        $html .='</tbody></table></div>';  
      }

      //get tache
      $get_tache = "SELECT PTBA_TACHE_ID,DESC_TACHE FROM ptba_tache WHERE DESC_TACHE LIKE '%{$value}%'";
      $get_tache='CALL `getTable`("'.$get_tache.'")';
      $get_tache= $this->ModelPs->getRequete($get_tache);

      if(!empty($get_tache))
      {
        $u=1;
        $html .='<div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                    <tr>
                      <th>'.lang('messages_lang.label_taches').' ('.count($get_tache).')</th>
                    </tr>
                    </thead>
                  <tbody>';
        foreach ($get_tache as $key)
        {
          $html .='<tr>
              <td><a href="'.base_url("double_commande_new/Recherche_Mot_Cle/getInfo_tache/")."/".md5($key->PTBA_TACHE_ID).'">'.$key->DESC_TACHE.'</a></td>
          </tr>';
        }  
        $html .='</tbody></table></div>';  
      }
    }

    $output = array('html' => $html);
      return $this->response->setJSON($output);
  }

  //get info sous titre
  public function getInfo_sousTitre($SOUS_TUTEL_ID)
  {
    $session=\Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }
    $data=$this->urichk();

    //get vote
    $get_det_vote="SELECT SUM(BUDGET_T1) AS BUDGET_T1,SUM(BUDGET_T2) AS BUDGET_T2,SUM(BUDGET_T3) AS BUDGET_T3,SUM(BUDGET_T4) AS BUDGET_T4,COUNT(PAP_ACTIVITE_ID) AS NBR_ACTIVITE,DESCRIPTION_SOUS_TUTEL FROM ptba_tache JOIN inst_institutions_sous_tutel st ON st.SOUS_TUTEL_ID=ptba_tache.SOUS_TUTEL_ID WHERE md5(ptba_tache.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."'";
    $get_det_vote='CALL `getTable`("'.$get_det_vote.'")';
    $data['get_det_vote']= $this->ModelPs->getRequeteOne($get_det_vote);

    //get exec T1
    $get_det_exec1="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."' AND exec.TRIMESTRE_ID=1";
    $get_det_exec1='CALL `getTable`("'.$get_det_exec1.'")';
    $data['get_det_exec1']= $this->ModelPs->getRequeteOne($get_det_exec1);

    //get exec T2
    $get_det_exec2="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."' AND exec.TRIMESTRE_ID=2";
    $get_det_exec2='CALL `getTable`("'.$get_det_exec2.'")';
    $data['get_det_exec2']= $this->ModelPs->getRequeteOne($get_det_exec2);

    //get exec T3
    $get_det_exec3="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."' AND exec.TRIMESTRE_ID=3";
    $get_det_exec3='CALL `getTable`("'.$get_det_exec3.'")';
    $data['get_det_exec3']= $this->ModelPs->getRequeteOne($get_det_exec3);

    //get exec T4
    $get_det_exec4="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."' AND exec.TRIMESTRE_ID=4";
    $get_det_exec4='CALL `getTable`("'.$get_det_exec4.'")';
    $data['get_det_exec4']= $this->ModelPs->getRequeteOne($get_det_exec4);

    //montant transferes
    $trans="SELECT SUM(MONTANT_TRANSFERT) AS total FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_TRANSFERT WHERE md5(ptba.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."'";
    $trans='CALL getTable("'.$trans.'");';
    $data['trans']=$this->ModelPs->getRequeteOne($trans);

    //montant récu
    $recu="SELECT SUM(MONTANT_RECEPTION) AS total FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_RECEPTION WHERE md5(ptba.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."'";
    $recu='CALL getTable("'.$recu.'");';
    $data['recu']=$this->ModelPs->getRequeteOne($recu);

    //tache vote/nouveau
    $activ_vote="SELECT COUNT(PTBA_TACHE_ID) AS NBT_VOTE FROM ptba_tache ptba WHERE md5(ptba.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."' AND IS_NOUVEAU=0";
    $activ_vote='CALL getTable("'.$activ_vote.'");';
    $data['activ_vote']=$this->ModelPs->getRequeteOne($activ_vote);

    $activ_nouveau="SELECT COUNT(PTBA_TACHE_ID) AS NBT_NOUVEAU FROM ptba_tache ptba WHERE md5(ptba.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."' AND IS_NOUVEAU=1";
    $activ_nouveau='CALL getTable("'.$activ_nouveau.'");';
    $data['activ_nouveau']=$this->ModelPs->getRequeteOne($activ_nouveau);

    //get programme
    $programme="SELECT COUNT(DISTINCT PROGRAMME_ID) AS NBR_PROG FROM ptba_tache ptba WHERE md5(ptba.SOUS_TUTEL_ID)='".$SOUS_TUTEL_ID."'";
    $programme='CALL getTable("'.$programme.'");';
    $data['programme']=$this->ModelPs->getRequeteOne($programme);

    return view('App\Modules\double_commande_new\Views\Recherche_Mot_Cle_Sous_Titre_View',$data);
  }

  //get info ligne budg
  public function getInfo_ligne($CODE_NOMENCLATURE_BUDGETAIRE_ID)
  {
    $session=\Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }
    $data=$this->urichk();
    //get vote
    $get_det_vote="SELECT SUM(BUDGET_T1) AS BUDGET_T1,SUM(BUDGET_T2) AS BUDGET_T2,SUM(BUDGET_T3) AS BUDGET_T3,SUM(BUDGET_T4) AS BUDGET_T4,COUNT(PAP_ACTIVITE_ID) AS NBR_ACTIVITE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE FROM ptba_tache JOIN inst_institutions_ligne_budgetaire lign ON lign.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE md5(ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."'";
    $get_det_vote='CALL `getTable`("'.$get_det_vote.'")';
    $data['get_det_vote']= $this->ModelPs->getRequeteOne($get_det_vote);

    $get_det_exec1="SELECT  sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND exec.TRIMESTRE_ID=1";
    $get_det_exec1='CALL `getTable`("'.$get_det_exec1.'")';
    $data['get_det_exec1']= $this->ModelPs->getRequeteOne($get_det_exec1);

    //get exec T2
    $get_det_exec2="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND exec.TRIMESTRE_ID=2";
    $get_det_exec2='CALL `getTable`("'.$get_det_exec2.'")';
    $data['get_det_exec2']= $this->ModelPs->getRequeteOne($get_det_exec2);

    //get exec T3
    $get_det_exec3="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND exec.TRIMESTRE_ID=3";
    $get_det_exec3='CALL `getTable`("'.$get_det_exec3.'")';
    $data['get_det_exec3']= $this->ModelPs->getRequeteOne($get_det_exec3);

    //get exec T4
    $get_det_exec4="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND exec.TRIMESTRE_ID=4";
    $get_det_exec4='CALL `getTable`("'.$get_det_exec4.'")';
    $data['get_det_exec4']= $this->ModelPs->getRequeteOne($get_det_exec4);

    //montant transferes
    $trans="SELECT SUM(MONTANT_TRANSFERT) AS total FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_TRANSFERT WHERE md5(ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."'";
    $trans='CALL getTable("'.$trans.'");';
    $data['trans']=$this->ModelPs->getRequeteOne($trans);

    //montant récu
    $recu="SELECT SUM(MONTANT_RECEPTION) AS total FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_RECEPTION WHERE md5(ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."'";
    $recu='CALL getTable("'.$recu.'");';
    $data['recu']=$this->ModelPs->getRequeteOne($recu);

    //tache vote/nouveau
    $activ_vote="SELECT COUNT(PTBA_TACHE_ID) AS NBT_VOTE FROM ptba_tache ptba WHERE md5(ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND IS_NOUVEAU=0";
    $activ_vote='CALL getTable("'.$activ_vote.'");';
    $data['activ_vote']=$this->ModelPs->getRequeteOne($activ_vote);

    $activ_nouveau="SELECT COUNT(PTBA_TACHE_ID) AS NBT_NOUVEAU FROM ptba_tache ptba WHERE md5(ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND IS_NOUVEAU=1";
    $activ_nouveau='CALL getTable("'.$activ_nouveau.'");';
    $data['activ_nouveau']=$this->ModelPs->getRequeteOne($activ_nouveau);
    return view('App\Modules\double_commande_new\Views\Recherche_Mot_Cle_Ligne_Budg_View',$data);
  }

  //get info activite
  public function getInfo_activite($PAP_ACTIVITE_ID)
  {
    $session=\Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }
    $data=$this->urichk();
    //get vote
    $get_det_vote="SELECT SUM(BUDGET_T1) AS BUDGET_T1,SUM(BUDGET_T2) AS BUDGET_T2,SUM(BUDGET_T3) AS BUDGET_T3,SUM(BUDGET_T4) AS BUDGET_T4,DESC_PAP_ACTIVITE FROM ptba_tache JOIN pap_activites act ON act.PAP_ACTIVITE_ID=ptba_tache.PAP_ACTIVITE_ID WHERE md5(ptba_tache.PAP_ACTIVITE_ID)='".$PAP_ACTIVITE_ID."'";
    $get_det_vote='CALL `getTable`("'.$get_det_vote.'")';
    $data['get_det_vote']= $this->ModelPs->getRequeteOne($get_det_vote);

    $get_det_exec="SELECT  sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.PAP_ACTIVITE_ID)='".$PAP_ACTIVITE_ID."'";
    $get_det_exec='CALL `getTable`("'.$get_det_exec.'")';
    $data['get_det_exec1']= $this->ModelPs->getRequeteOne($get_det_exec);

    //get exec T2
    $get_det_exec2="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.PAP_ACTIVITE_ID)='".$PAP_ACTIVITE_ID."' AND exec.TRIMESTRE_ID=2";
    $get_det_exec2='CALL `getTable`("'.$get_det_exec2.'")';
    $data['get_det_exec2']= $this->ModelPs->getRequeteOne($get_det_exec2);

    //get exec T3
    $get_det_exec3="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.PAP_ACTIVITE_ID)='".$PAP_ACTIVITE_ID."' AND exec.TRIMESTRE_ID=3";
    $get_det_exec3='CALL `getTable`("'.$get_det_exec3.'")';
    $data['get_det_exec3']= $this->ModelPs->getRequeteOne($get_det_exec3);

    //get exec T4
    $get_det_exec4="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.PAP_ACTIVITE_ID)='".$PAP_ACTIVITE_ID."' AND exec.TRIMESTRE_ID=4";
    $get_det_exec4='CALL `getTable`("'.$get_det_exec4.'")';
    $data['get_det_exec4']= $this->ModelPs->getRequeteOne($get_det_exec4);

    //montant transferes
    $trans="SELECT SUM(MONTANT_TRANSFERT) AS total FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_TRANSFERT WHERE md5(ptba.PAP_ACTIVITE_ID)='".$PAP_ACTIVITE_ID."'";
    $trans='CALL getTable("'.$trans.'");';
    $data['trans']=$this->ModelPs->getRequeteOne($trans);

    //montant récu
    $recu="SELECT SUM(MONTANT_RECEPTION) AS total FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_RECEPTION WHERE md5(ptba.PAP_ACTIVITE_ID)='".$PAP_ACTIVITE_ID."'";
    $recu='CALL getTable("'.$recu.'");';
    $data['recu']=$this->ModelPs->getRequeteOne($recu);

    //tache vote/nouveau
    $activ_vote="SELECT COUNT(PTBA_TACHE_ID) AS NBT_VOTE FROM ptba_tache ptba WHERE md5(ptba.PAP_ACTIVITE_ID)='".$PAP_ACTIVITE_ID."' AND IS_NOUVEAU=0";
    $activ_vote='CALL getTable("'.$activ_vote.'");';
    $data['activ_vote']=$this->ModelPs->getRequeteOne($activ_vote);

    $activ_nouveau="SELECT COUNT(PTBA_TACHE_ID) AS NBT_NOUVEAU FROM ptba_tache ptba WHERE md5(ptba.PAP_ACTIVITE_ID)='".$PAP_ACTIVITE_ID."' AND IS_NOUVEAU=1";
    $activ_nouveau='CALL getTable("'.$activ_nouveau.'");';
    $data['activ_nouveau']=$this->ModelPs->getRequeteOne($activ_nouveau);
    return view('App\Modules\double_commande_new\Views\Recherche_Mot_Cle_Activite_View',$data);
  }

  //get info tache
  public function getInfo_tache($PTBA_TACHE_ID)
  {
    $session=\Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }
    $data=$this->urichk();
    //get vote
    $get_det_vote="SELECT SUM(BUDGET_T1) AS BUDGET_T1,SUM(BUDGET_T2) AS BUDGET_T2,SUM(BUDGET_T3) AS BUDGET_T3,SUM(BUDGET_T4) AS BUDGET_T4,DESC_TACHE FROM ptba_tache WHERE md5(ptba_tache.PTBA_TACHE_ID)='".$PTBA_TACHE_ID."'";
    $get_det_vote='CALL `getTable`("'.$get_det_vote.'")';
    $data['get_det_vote']= $this->ModelPs->getRequeteOne($get_det_vote);

    $get_det_exec="SELECT  sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.PTBA_TACHE_ID)='".$PTBA_TACHE_ID."'";
    $get_det_exec='CALL `getTable`("'.$get_det_exec.'")';
    $data['get_det_exec1']= $this->ModelPs->getRequeteOne($get_det_exec);

    //get exec T2
    $get_det_exec2="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.PTBA_TACHE_ID)='".$PTBA_TACHE_ID."' AND exec.TRIMESTRE_ID=2";
    $get_det_exec2='CALL `getTable`("'.$get_det_exec2.'")';
    $data['get_det_exec2']= $this->ModelPs->getRequeteOne($get_det_exec2);

    //get exec T3
    $get_det_exec3="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.PTBA_TACHE_ID)='".$PTBA_TACHE_ID."' AND exec.TRIMESTRE_ID=3";
    $get_det_exec3='CALL `getTable`("'.$get_det_exec3.'")';
    $data['get_det_exec3']= $this->ModelPs->getRequeteOne($get_det_exec3);

    //get exec T4
    $get_det_exec4="SELECT sum(`ENG_BUDGETAIRE`) as ENG_BUDGETAIRE,sum(`ENG_JURIDIQUE`) as ENG_JURIDIQUE, sum(LIQUIDATION) as LIQUIDATION,sum(`ORDONNANCEMENT`) as ORDONNANCEMENT,sum(PAIEMENT) as PAIEMENT,sum(DECAISSEMENT) as DECAISSEMENT FROM execution_budgetaire exec JOIN execution_budgetaire_execution_tache exectache ON exec.EXECUTION_BUDGETAIRE_ID=exectache.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache tache ON tache.PTBA_TACHE_ID=exectache.PTBA_TACHE_ID WHERE md5(tache.PTBA_TACHE_ID)='".$PTBA_TACHE_ID."' AND exec.TRIMESTRE_ID=4";
    $get_det_exec4='CALL `getTable`("'.$get_det_exec4.'")';
    $data['get_det_exec4']= $this->ModelPs->getRequeteOne($get_det_exec4);

    //montant transferes
    $trans="SELECT SUM(MONTANT_TRANSFERT) AS total FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_TRANSFERT WHERE md5(ptba.PTBA_TACHE_ID)='".$PTBA_TACHE_ID."'";
    $trans='CALL getTable("'.$trans.'");';
    $data['trans']=$this->ModelPs->getRequeteOne($trans);

    //montant récu
    $recu="SELECT SUM(MONTANT_RECEPTION) AS total FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_RECEPTION WHERE md5(ptba.PTBA_TACHE_ID)='".$PTBA_TACHE_ID."'";
    $recu='CALL getTable("'.$recu.'");';
    $data['recu']=$this->ModelPs->getRequeteOne($recu);

    return view('App\Modules\double_commande_new\Views\Recherche_Mot_Cle_Tache_View',$data);
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$where,$db->escapeString($orderby)];
    return $bindparams;
  }
}
?>