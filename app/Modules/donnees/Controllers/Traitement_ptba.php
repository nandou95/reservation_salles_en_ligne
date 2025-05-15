<?php
namespace App\Modules\donnees\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Traitement_ptba extends BaseController
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

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }

  public function getBindParmsLimit($columnselect, $table, $where, $orderby,$Limit)
  {
    $db = db_connect();
    $columnselect=str_replace("\'", "'", $columnselect);
    $table=str_replace("\'", "'", $table);
    $where=str_replace("\'", "'", $where);
    $orderby=str_replace("\'", "'", $orderby);
    $Limit=str_replace("\'", "'", $Limit);
    $bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby),$db->escapeString($Limit)];
    $bindparams=str_replace('\"', '"', $bindparams);
    return $bindparams;
  }

  public function save_all_table($table,$columsinsert,$datacolumsinsert)
  {
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams =[$table,$columsinsert,$datacolumsinsert];
    $result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
    return $id=$result['id'];
  }

  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  public function traite_inst_pro_action()
  {
    // data code institution
    $table="ptba";
    $callpsreq="CALL `getRequete`(?,?,?,?);";
    // Debut institutions
    $bind_ptbainst=$this->getBindParms('DISTINCT CODE_MINISTERE',$table,'1','CODE_MINISTERE ASC');
    $institutions= $this->ModelPs->getRequete($callpsreq,$bind_ptbainst);
    if(!empty($institutions))
    {
      foreach ($institutions as $institution)
      {
        $condiction='CODE_INSTITUTION="'.$institution->CODE_MINISTERE.'"';
        $bind_inst=$this->getBindParms('INSTITUTION_ID','inst_institutions',$condiction, 'INSTITUTION_ID ASC');
        $bind_inst=str_replace('\"','"',$bind_inst);
        $ptba_inst=$this->ModelPs->getRequeteOne($callpsreq,$bind_inst);
        if(!empty($ptba_inst))
        {
          $where='CODE_MINISTERE="'.$institution->CODE_MINISTERE.'"';
          $data='INSTITUTION_ID='.$ptba_inst['INSTITUTION_ID'];
          $this->update_all_table($table,$data,$where);
        }
      }
    }
    print_r('ok institutions');
    // Fin institutions

    // Debut programmes
    $bind_ptbapro=$this->getBindParms('DISTINCT CODE_PROGRAMME',$table,'1','CODE_PROGRAMME ASC');
    $programmes= $this->ModelPs->getRequete($callpsreq,$bind_ptbapro);
    if(!empty($programmes))
    {
      foreach ($programmes as $programme)
      {
        $condictionpro='CODE_PROGRAMME="'.$programme->CODE_PROGRAMME.'"';
        $bind_pro=$this->getBindParms('PROGRAMME_ID','inst_institutions_programmes',$condictionpro,'PROGRAMME_ID ASC');
        $bind_pro=str_replace('\"','"',$bind_pro);
        $ptba_pro=$this->ModelPs->getRequeteOne($callpsreq,$bind_pro);
        if(!empty($ptba_pro))
        {
          $wherepro='CODE_PROGRAMME="'.$programme->CODE_PROGRAMME.'"';
          $datapro='PROGRAMME_ID='.$ptba_pro['PROGRAMME_ID'];
          $this->update_all_table($table,$datapro,$wherepro);
        }
      }
    }
    print_r('ok programmes');
    // Fin programmes

    // Debut actions
    $bind_ptbaaction=$this->getBindParms('DISTINCT CODE_ACTION',$table,'1','CODE_ACTION ASC');
    $actions= $this->ModelPs->getRequete($callpsreq,$bind_ptbaaction);
    if(!empty($actions))
    {
      foreach ($actions as $action)
      {
        $condiction_action='CODE_ACTION="'.$action->CODE_ACTION.'"';
        $bind_action=$this->getBindParms('ACTION_ID','inst_institutions_actions',$condiction_action, 'ACTION_ID ASC');
        $bind_action=str_replace('\"','"',$bind_action);
        $ptba_action=$this->ModelPs->getRequeteOne($callpsreq,$bind_action);
        if(!empty($ptba_action))
        {
          $where_action='CODE_ACTION="'.$action->CODE_ACTION.'"';
          $data_action='ACTION_ID='.$ptba_action['ACTION_ID'];
          $this->update_all_table($table,$data_action,$where_action);
        }
      }
    }
    print_r('ok actions');
    die();
    // Fin actions
  }

  public function traite_code_nomeclature()
  {
    $table="ptba";
    $tableinsert="inst_institutions_ligne_budgetaire";
    $columsinserte="INSTITUTION_ID,PROGRAMME_ID,ACTION_ID,CODE_NOMENCLATURE_BUDGETAIRE";
    $callpsreq="CALL `getRequete`(?,?,?,?);";
    // Debut code nomenclature
    $bind_ptbacode=$this->getBindParms('DISTINCT CODE_NOMENCLATURE_BUDGETAIRE',$table,'1','CODE_NOMENCLATURE_BUDGETAIRE ASC');
    $code_nomenclatures= $this->ModelPs->getRequete($callpsreq,$bind_ptbacode);
    if(!empty($code_nomenclatures))
    {
      foreach ($code_nomenclatures as $code_nomenclature)
      {
        // get 
        $condiction='CODE_NOMENCLATURE_BUDGETAIRE="'.$code_nomenclature->CODE_NOMENCLATURE_BUDGETAIRE.'"';
        $bind_data=$this->getBindParms('INSTITUTION_ID,PROGRAMME_ID,ACTION_ID',$table,$condiction, 'PTBA_ID ASC');
        $bind_data=str_replace('\"','"',$bind_data);
        $ptba_data=$this->ModelPs->getRequeteOne($callpsreq,$bind_data);
        if(!empty($ptba_data))
        {
          $valuecolumsinserte=$ptba_data['INSTITUTION_ID'].",".$ptba_data['PROGRAMME_ID'].",".$ptba_data['ACTION_ID'].",'".$code_nomenclature->CODE_NOMENCLATURE_BUDGETAIRE."'";
          $CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->save_all_table($tableinsert,$columsinserte,$valuecolumsinserte);
          if (!empty($CODE_NOMENCLATURE_BUDGETAIRE_ID))
          {
            $where='CODE_NOMENCLATURE_BUDGETAIRE="'.$code_nomenclature->CODE_NOMENCLATURE_BUDGETAIRE.'"';
            $data='CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID;
            $this->update_all_table($table,$data,$where);
          }
        }
      }
    }
    // Fin code nomenclature
    print_r('ok code nomenclature budget');
    die();
  }

  public function traite_code_nomeclature_execution()
  {
    $table="execution_budgetaire_new";
    $tabletwo="inst_institutions_ligne_budgetaire";
    $callpsreq="CALL `getRequete`(?,?,?,?);";
    $bind_exec=$this->getBindParms('DISTINCT IMPUTATION',$table,'1','IMPUTATION ASC');
    $imputations= $this->ModelPs->getRequete($callpsreq,$bind_exec);
    if(!empty($imputations))
    {
      foreach($imputations as $imputation)
      {
        // get info code bugdetaire
        $condiction='CODE_NOMENCLATURE_BUDGETAIRE="'.$imputation->IMPUTATION.'"';
        $bind_data=$this->getBindParms('CODE_NOMENCLATURE_BUDGETAIRE_ID,INSTITUTION_ID,PROGRAMME_ID,ACTION_ID',$tabletwo,$condiction, 'CODE_NOMENCLATURE_BUDGETAIRE_ID ASC');
        $bind_data=str_replace('\"','"',$bind_data);
        $ptba_data=$this->ModelPs->getRequeteOne($callpsreq,$bind_data);
        if(!empty($ptba_data))
        {
          $where='IMPUTATION="'.$imputation->IMPUTATION.'"';
          $data='INSTITUTION_ID='.$ptba_data['INSTITUTION_ID'].',PROGRAMME_ID='.$ptba_data['PROGRAMME_ID'].',ACTION_ID='.$ptba_data['ACTION_ID'].',CODE_NOMENCLATURE_BUDGETAIRE_ID='.$ptba_data['CODE_NOMENCLATURE_BUDGETAIRE_ID'];
          $this->update_all_table($table,$data,$where);
        }
      }
    }
    print_r('OK imputation execution');
  }
}
?>