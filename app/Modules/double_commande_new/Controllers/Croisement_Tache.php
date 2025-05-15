<?php
/**
 * Auteur: NDERAGAKURA Alain Charbel
 * email: charbel@mediabox.bi
 * téléphone: +257 62 00 35 22
 * tache:interface de croisement de tache
 */

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;

class Croisement_Tache extends BaseController
{
  protected $session;
  protected $ModelPs;
  
  public function __construct()
  {
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  public function index($CODE_NOMENCLATURE_BUDGETAIRE_ID)
  {
    $data=$this->urichk();
    // $CODE_NOMENCLATURE_BUDGETAIRE_ID=md5($CODE_NOMENCLATURE_BUDGETAIRE_ID);
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }

    $get_tache = "SELECT PTBA_TACHE_ID,DESC_TACHE,CODE_NOMENCLATURE_BUDGETAIRE,RESULTAT_ATTENDUS_TACHE, UNITE, Q_TOTAL, QT1, QT2, QT3, QT4, COUT_UNITAIRE,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache WHERE MD5(CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND PTBA_TACHE_ID NOT IN (SELECT PTBA_TACHE_ID FROM ptba_tache_revise WHERE MD5(CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND PTBA_TACHE_ID>0)";
    $data['get_tache'] = $this->ModelPs->getRequete('CALL `getTable`("' . $get_tache . '")');

    $get_tache_revise = "SELECT PTBA_TACHE_REVISE_ID,CODE_NOMENCLATURE_BUDGETAIRE,DESC_TACHE,RESULTAT_ATTENDUS_TACHE, UNITE, Q_TOTAL, QT1, QT2, QT3, QT4, COUT_UNITAIRE,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache_revise WHERE MD5(CODE_NOMENCLATURE_BUDGETAIRE_ID)='".$CODE_NOMENCLATURE_BUDGETAIRE_ID."' AND PTBA_TACHE_ID=0";
    $data['get_tache_revise'] = $this->ModelPs->getRequete('CALL `getTable`("' . $get_tache_revise . '")');

    return view('App\Modules\double_commande_new\Views\Croisement_Tache_View',$data);
  }

  public function save()
  {
    $CODE_NOMENCLATURE_BUDGETAIRE=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE');
    $tache1=$this->request->getPost('tache1');
    $tache2=$this->request->getPost('tache2');
    $PTBA_TACHE_REVISE_ID=$this->request->getPost('PTBA_TACHE_REVISE_ID');
    if($tache1>0 and $tache2>0 and $tache1==$tache2)
    {
      $data='PTBA_TACHE_ID='.$tache1;
      $where='PTBA_TACHE_REVISE_ID='.$PTBA_TACHE_REVISE_ID;
      $this->update_all_table('ptba_tache_revise',$data,$where);
      $output = array('status' => TRUE);
      return $this->response->setJSON($output);
    }
    else
    {
      $output = array('status' => FALSE);
      return $this->response->setJSON($output);
    }
  }

  public function get_info()
  {
    $data=$this->urichk();
    $session  = \Config\Services::session();

    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }

    $id_tache=$this->request->getPost('id_tache');
    $get_tache = "SELECT RESULTAT_ATTENDUS_TACHE, UNITE, Q_TOTAL, QT1, QT2, QT3, QT4, COUT_UNITAIRE,BUDGET_T1,BUDGET_T2,BUDGET_T3,BUDGET_T4 FROM ptba_tache WHERE PTBA_TACHE_ID=".$id_tache;
    $get_tache = $this->ModelPs->getRequete('CALL `getTable`("' . $get_tache . '")');
    $html='';
    foreach ($get_tache as $key)
    {
      $html= '<tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Résultat attendu</font></td>
      <td><strong><font style="float:left;">'.$key->RESULTAT_ATTENDUS_TACHE.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Unité</font></td>
      <td><strong><font style="float:left;">'.$key->UNITE.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Quantité Total</font></td>
      <td><strong><font style="float:left;">'.$key->Q_TOTAL.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Quantité T1</font></td>
      <td><strong><font style="float:left;">'.$key->QT1.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Quantité T2</font></td>
      <td><strong><font style="float:left;">'.$key->QT2.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Quantité T3</font></td>
      <td><strong><font style="float:left;">'.$key->QT3.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Quantité T4</font></td>
      <td><strong><font style="float:left;">'.$key->QT4.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Coût unitaire</font></td>
      <td><strong><font style="float:left;">'.$key->COUT_UNITAIRE.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Budget T1</font></td>
      <td><strong><font style="float:left;">'.$key->BUDGET_T1.'</font></strong></td>
      </tr>
      <tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Budget T2</font></td>
      <td><strong><font style="float:left;">'.$key->BUDGET_T2.'</font></strong></td>
      </tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Budget T3</font></td>
      <td><strong><font style="float:left;">'.$key->BUDGET_T3.'</font></strong></td>
      </tr>
      <td style="width:250px ;"><font style="float:left;"><i class="fa fa-credit-card"> </i>&nbsp;Budget T4</font></td>
      <td><strong><font style="float:left;">'.$key->BUDGET_T4.'</font></strong></td>
      </tr>';
    }
    $output = array('html' => $html);
    return $this->response->setJSON($output);
  }

  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
}