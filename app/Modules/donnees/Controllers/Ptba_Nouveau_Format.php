<?php

namespace App\Modules\donnees\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Ptba_Nouveau_Format extends BaseController
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
    $USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
    if(empty($USER_IDD))
    {
      return redirect('Login_Ptba/do_logout');
    }
    
    $data=$this->urichk();
    return view('App\Modules\donnees\Views\Ptba_Nouveau_Format_view',$data);
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

  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  public function save_all_table($table,$columsinsert,$datacolumsinsert)
  {
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams =[$table,$columsinsert,$datacolumsinsert];
    $result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
    return $id=$result['id'];
  }

  public function importfile()
  {
    $callpsreq="CALL `getRequete`(?,?,?,?);";
    $colums_objet_v="CODE_OBJECTIF_VISION,DESC_OBJECTIF_VISION";
    $session = \Config\Services::session();
    $USER_ID=0;
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    $UPLOAD_DOCUMENT=$_FILES["UPLOAD_DOCUMENT"]["name"];
    $extension=pathinfo($UPLOAD_DOCUMENT,PATHINFO_EXTENSION);
    if($extension=='csv')
    {
      $reader=new \PhpOffice\PhpSpreadsheet\Reader\Csv();
    }
    else if($extension=='xls')
    {
      $reader=new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    }
    else
    {
      $reader=new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    }
    
    $spreadsheet=$reader->load($_FILES["UPLOAD_DOCUMENT"]["tmp_name"]);
    $sheetdata=$spreadsheet->getActiveSheet()->toArray();
    $sheetcount=count($sheetdata);
    if($sheetcount>1)
    {
      for($i=1; $i < $sheetcount; $i++)
      { 
        $CODE_MINISTERE=trim($sheetdata[$i][0]);
        if(!empty($CODE_MINISTERE))
        {
          // Debut Institution
          $CODE_MINISTERE=str_replace("'","",$CODE_MINISTERE);
          $CODE_MINISTERE=str_replace('"','',$CODE_MINISTERE);
          $cond_inst='CODE_INSTITUTION="'.$CODE_MINISTERE.'"';
          $bind_inst=$this->getBindParms('INSTITUTION_ID','inst_institutions',$cond_inst, 'INSTITUTION_ID ASC');
          $bind_inst=str_replace('\"','"',$bind_inst);
          $institution=$this->ModelPs->getRequeteOne($callpsreq,$bind_inst);
          // $INTITULE_MINISTERE=trim($sheetdata[$i][1]);

          if(!empty($institution))
          {
            $INSTITUTION_ID=$institution['INSTITUTION_ID'];
            // Debut Pilier
            $PILIER=trim($sheetdata[$i][2]);
            if(!empty($PILIER))
            {
              $ID_PILIER=substr($PILIER,7,1);
              // Debut objectif vision
              $OBJECTIF_VISION=trim($sheetdata[$i][3]);
              if(!empty($OBJECTIF_VISION))
              {
                $CODE_OBJECTIF_VISION=substr($OBJECTIF_VISION,0,2);
                $cond_obj_v='CODE_OBJECTIF_VISION="'.$CODE_OBJECTIF_VISION.'"';
                $bind_obj_v=$this->getBindParms('OBJECTIF_VISION_ID','vision_objectif',$cond_obj_v,'OBJECTIF_VISION_ID ASC');
                $bind_obj_v=str_replace('\"','"',$bind_obj_v);
                $objectif_vision=$this->ModelPs->getRequeteOne($callpsreq,$bind_obj_v);
                $OBJECTIF_VISION_ID=0;
                if(!empty($objectif_vision))
                {
                  $OBJECTIF_VISION_ID=$objectif_vision['OBJECTIF_VISION_ID'];
                }
                else
                {
                  $OBJECTIF_VISION=str_replace(["\n", "\r"],' ',$OBJECTIF_VISION);
                  $OBJECTIF_VISION=str_replace('\n',' ',$OBJECTIF_VISION);
                  $OBJECTIF_VISION=str_replace('\r',' ',$OBJECTIF_VISION);
                  $OBJECTIF_VISION=str_replace('"','',$OBJECTIF_VISION);
                  $OBJECTIF_VISION=str_replace('"','',$OBJECTIF_VISION);
                  $OBJECTIF_VISION=str_replace("'","\'",$OBJECTIF_VISION);
                  $OBJECTIF_VISION=str_replace("\\\'","\'",$OBJECTIF_VISION);
                  $OBJECTIF_VISION=str_replace("\\'","\'",$OBJECTIF_VISION);
                  $value_objet_v='"'.$CODE_OBJECTIF_VISION.'","'.$OBJECTIF_VISION.'"';
                  $OBJECTIF_VISION_ID=$this->save_all_table('vision_objectif',$colums_objet_v,$value_objet_v);
                }

                if($OBJECTIF_VISION_ID>0)
                {
                  // Debut axe du PND
                  $AXE_PND=trim($sheetdata[$i][4]);
                  $AXE_PND_ID=substr($AXE_PND,4,1);
                  
                  if($AXE_PND_ID>0)
                  {
                    // Debut programmation
                    $CODE_PROGRAMME=trim($sheetdata[$i][5]);
                    $CODE_PROGRAMME=str_replace("'","",$CODE_PROGRAMME);
                    $CODE_PROGRAMME=str_replace('"','',$CODE_PROGRAMME);
                    $INTITULE_PROGRAMME=trim($sheetdata[$i][6]);
                    $OBJECTIF_PROGRAMME=trim($sheetdata[$i][7]);
                    $cond_progr='CODE_PROGRAMME="'.$CODE_PROGRAMME.'"';
                    $bind_progr=$this->getBindParms('PROGRAMME_ID','inst_institutions_programmes',$cond_progr,'PROGRAMME_ID ASC');
                    $bind_progr=str_replace('\"','"',$bind_progr);
                    $programme=$this->ModelPs->getRequeteOne($callpsreq,$bind_progr);
                    if(!empty($programme))
                    {
                      $PROGRAMME_ID=$programme['PROGRAMME_ID'];
                      // Debut action
                      $ACTION_ID=0;
                      $CODE_ACTION=trim($sheetdata[$i][8]);
                      $LIBELLE_ACTION=trim($sheetdata[$i][9]);
                      $OBJECTIF_ACTION=trim($sheetdata[$i][10]);
                      
                      if(empty($CODE_ACTION))
                      {
                        $CODE_ACTION=$CODE_PROGRAMME."01";
                      }

                      $cond_action='CODE_ACTION="'.$CODE_ACTION.'"';
                      $bind_action=$this->getBindParms('ACTION_ID','inst_institutions_actions',$cond_action,'ACTION_ID ASC');
                      $bind_action=str_replace('\"','"',$bind_action);
                      $action=$this->ModelPs->getRequeteOne($callpsreq,$bind_action);

                      if(!empty($action))
                      {
                        $ACTION_ID=$action['ACTION_ID'];
                      }
                      else
                      {
                        $str_action=!empty($LIBELLE_ACTION)?$LIBELLE_ACTION:$INTITULE_PROGRAMME;
                        $str_action_obj=!empty($LIBELLE_ACTION)?$OBJECTIF_ACTION:$OBJECTIF_PROGRAMME;
                        $str_action=str_replace(["\n", "\r"],' ',$str_action);
                        $str_action=str_replace('\n',' ',$str_action);
                        $str_action=str_replace('\r',' ',$str_action);
                        $str_action=str_replace('"','',$str_action);
                        $str_action=str_replace('"','',$str_action);
                        $str_action=str_replace("'","\'",$str_action);
                        $str_action=str_replace("\\\'","\'",$str_action);
                        $str_action=str_replace("\\'","\'",$str_action);

                        $str_action_obj=str_replace(["\n", "\r"],' ',$str_action_obj);
                        $str_action_obj=str_replace('\n',' ',$str_action_obj);
                        $str_action_obj=str_replace('\r',' ',$str_action_obj);
                        $str_action_obj=str_replace('"','',$str_action_obj);
                        $str_action_obj=str_replace('"','',$str_action_obj);
                        $str_action_obj=str_replace("'","\'",$str_action_obj);
                        $str_action_obj=str_replace("\\\'","\'",$str_action_obj);
                        $str_action_obj=str_replace("\\'","\'",$str_action_obj);
                        $colums_action="PROGRAMME_ID,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION";
                        $value_action=$PROGRAMME_ID.',"'.$CODE_ACTION.'","'.$str_action.'","'.$str_action_obj.'"';
                        $ACTION_ID=$this->save_all_table('inst_institutions_actions',$colums_action,$value_action);
                      }

                      if($ACTION_ID>0)
                      {
                        // Debut programme prioritaire
                        $col_extra="";
                        $val_extra="";
                        $PROGRAMME_PRIORITAIRE_ID=NULL;
                        $PROGRAMME_PRIORITAIRE=trim($sheetdata[$i][11]);
                        
                        if(!empty($PROGRAMME_PRIORITAIRE))
                        {
                          $cond_pro_pr='DESC_PROGRAMME_PRIORITAIRE="'.$PROGRAMME_PRIORITAIRE.'"';
                          $bind_pro_pr=$this->getBindParms('PROGRAMME_PRIORITAIRE_ID','inst_institutions_programme_prioritaire',$cond_pro_pr,'PROGRAMME_PRIORITAIRE_ID ASC');
                          $bind_pro_pr=str_replace('\"','"',$bind_pro_pr);
                          $programme_prioritaire=$this->ModelPs->getRequeteOne($callpsreq,$bind_pro_pr);
                          if(!empty($programme_prioritaire))
                          {
                            $PROGRAMME_PRIORITAIRE_ID=$programme_prioritaire['PROGRAMME_PRIORITAIRE_ID'];
                          }
                          else
                          {
                            $PROGRAMME_PRIORITAIRE=str_replace(["\n", "\r"],' ',$PROGRAMME_PRIORITAIRE);
                            $PROGRAMME_PRIORITAIRE=str_replace('\n',' ',$PROGRAMME_PRIORITAIRE);
                            $PROGRAMME_PRIORITAIRE=str_replace('\r',' ',$PROGRAMME_PRIORITAIRE);
                            $PROGRAMME_PRIORITAIRE=str_replace('"','',$PROGRAMME_PRIORITAIRE);
                            $PROGRAMME_PRIORITAIRE=str_replace('"','',$PROGRAMME_PRIORITAIRE);
                            $PROGRAMME_PRIORITAIRE=str_replace("'","\'",$PROGRAMME_PRIORITAIRE);
                            $PROGRAMME_PRIORITAIRE=str_replace("\\\'","\'",$PROGRAMME_PRIORITAIRE);
                            $PROGRAMME_PRIORITAIRE=str_replace("\\'","\'",$PROGRAMME_PRIORITAIRE);
                            $colums_pro_pri="DESC_PROGRAMME_PRIORITAIRE";
                            $value_pro_pri='"'.$PROGRAMME_PRIORITAIRE.'"';
                            $PROGRAMME_PRIORITAIRE_ID=$this->save_all_table('inst_institutions_programme_prioritaire',$colums_pro_pri,$value_pro_pri);
                          }
                          $col_extra=",PROGRAMME_PRIORITAIRE_ID";
                          $val_extra=",".$PROGRAMME_PRIORITAIRE_ID;
                        }
                        // Fin programme prioritaire

                        // Debut Gestion du sous titre
                        $SOUS_TUTEL_ID=0;
                        $CODE_NOMENCLATURE_BUDGETAIRE=trim($sheetdata[$i][12]);
                        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace("'","",$CODE_NOMENCLATURE_BUDGETAIRE);
                        $CODE_NOMENCLATURE_BUDGETAIRE=str_replace('"','',$CODE_NOMENCLATURE_BUDGETAIRE);
                        $CODE_SOUS_TUTEL=substr($CODE_NOMENCLATURE_BUDGETAIRE,4,3);
                        $cond_sout_tutel='CODE_SOUS_TUTEL="'.$CODE_SOUS_TUTEL.'" AND INSTITUTION_ID='.$INSTITUTION_ID;
                        $bind_sout_tutel=$this->getBindParms('SOUS_TUTEL_ID','inst_institutions_sous_tutel',$cond_sout_tutel,'SOUS_TUTEL_ID ASC');
                        $bind_sout_tutel=str_replace('\"','"',$bind_sout_tutel);
                        $sous_tutel=$this->ModelPs->getRequeteOne($callpsreq,$bind_sout_tutel);
                        if(!empty($sous_tutel))
                        {
                          $SOUS_TUTEL_ID=$sous_tutel['SOUS_TUTEL_ID'];
                        }
                        else
                        {
                          $DESCRIPTION_SOUS_TUTEL='';
                          $colums_sous_tutel="INSTITUTION_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL";
                          $value_sous_tutel=$INSTITUTION_ID.',"'.$CODE_SOUS_TUTEL.'","'.$DESCRIPTION_SOUS_TUTEL.'"';
                          $SOUS_TUTEL_ID=$this->save_all_table('inst_institutions_sous_tutel',$colums_sous_tutel,$value_sous_tutel);
                        }

                        if($SOUS_TUTEL_ID>0)
                        {
                          //  Debut code nomenclature budgetaire
                          $CODE_NOMENCLATURE_BUDGETAIRE_ID=0;
                          $cond_code_nom='CODE_NOMENCLATURE_BUDGETAIRE="'.$CODE_NOMENCLATURE_BUDGETAIRE.'"';
                          $bind_code_nom=$this->getBindParms('CODE_NOMENCLATURE_BUDGETAIRE_ID','inst_institutions_ligne_budgetaire',$cond_code_nom,'CODE_NOMENCLATURE_BUDGETAIRE_ID ASC');
                          $bind_code_nom=str_replace('\"','"',$bind_code_nom);
                          $ligne_budgetaire=$this->ModelPs->getRequeteOne($callpsreq,$bind_code_nom);
                          if(!empty($ligne_budgetaire))
                          {
                            $CODE_NOMENCLATURE_BUDGETAIRE_ID=$ligne_budgetaire['CODE_NOMENCLATURE_BUDGETAIRE_ID'];
                          }
                          else
                          {
                            $LIBELLE_CODE_BUDGETAIRE='';
                            $colums_code_nom="INSTITUTION_ID,SOUS_TUTEL_ID,PROGRAMME_ID,ACTION_ID,CODE_NOMENCLATURE_BUDGETAIRE,LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE";
                            $value_code_nom=$INSTITUTION_ID.','.$SOUS_TUTEL_ID.','.$PROGRAMME_ID.','.$ACTION_ID.',"'.$CODE_NOMENCLATURE_BUDGETAIRE.'","'.$LIBELLE_CODE_BUDGETAIRE.'"';
                            $CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->save_all_table('inst_institutions_ligne_budgetaire',$colums_code_nom,$value_code_nom);
                          }

                          if($CODE_NOMENCLATURE_BUDGETAIRE_ID>0)
                          {
                            // Debut Article economique
                            $CODE_ARTICLE=trim($sheetdata[$i][13]);
                            // $LIBELLE_ARTICLE=trim($sheetdata[$i][14]);
                            $cond_code_art='CODE_ARTICLE="'.$CODE_ARTICLE.'"';
                            $bind_code_art=$this->getBindParms('ARTICLE_ID','class_economique_article',$cond_code_art,'ARTICLE_ID ASC');
                            $bind_code_art=str_replace('\"','"',$bind_code_art);
                            $article=$this->ModelPs->getRequeteOne($callpsreq,$bind_code_art);
                            if(!empty($article))
                            {
                              $ARTICLE_ID=$article['ARTICLE_ID'];
                              // Debut nature economique(sous littera)
                              $CODE_SOUS_LITTERA=trim($sheetdata[$i][15]);
                              // $LIBELLE_SOUS_LITTERA=trim($sheetdata[$i][16]);
                              $cond_code_sous_littera='CODE_SOUS_LITTERA="'.$CODE_SOUS_LITTERA.'"';
                              $bind_code_sous_littera=$this->getBindParms('SOUS_LITTERA_ID','class_economique_sous_littera',$cond_code_sous_littera,'SOUS_LITTERA_ID ASC');
                              $bind_code_sous_littera=str_replace('\"','"',$bind_code_sous_littera);
                              $sous_littera=$this->ModelPs->getRequeteOne($callpsreq,$bind_code_sous_littera);
                              if(!empty($sous_littera))
                              {
                                $SOUS_LITTERA_ID=$sous_littera['SOUS_LITTERA_ID'];
                                // Debut division fonctionnelle
                                $CODE_DIVISION=trim($sheetdata[$i][17]);
                                // $LIBELLE_DIVISION=trim($sheetdata[$i][18]);
                                $cond_code_division='CODE_DIVISION="'.$CODE_DIVISION.'"';
                                $bind_code_division=$this->getBindParms('DIVISION_ID','class_fonctionnelle_division',$cond_code_division,'DIVISION_ID ASC');
                                $bind_code_division=str_replace('\"','"',$bind_code_division);
                                $division=$this->ModelPs->getRequeteOne($callpsreq,$bind_code_division);
                                if(!empty($division))
                                {
                                  $DIVISION_ID=$division['DIVISION_ID'];
                                  // Debut groupe fonctionnelle
                                  $CODE_GROUPE=trim($sheetdata[$i][19]);
                                  // $LIBELLE_GROUPE=trim($sheetdata[$i][20]);
                                  $cond_code_groupe='CODE_GROUPE="'.$CODE_GROUPE.'"';
                                  $bind_code_groupe=$this->getBindParms('GROUPE_ID','class_fonctionnelle_groupe',$cond_code_groupe,'GROUPE_ID ASC');
                                  $bind_code_groupe=str_replace('\"','"',$bind_code_groupe);
                                  $groupe=$this->ModelPs->getRequeteOne($callpsreq,$bind_code_groupe);
                                  if(!empty($groupe))
                                  {
                                    $GROUPE_ID=$groupe['GROUPE_ID'];
                                    // Debut classe fonctionnelle
                                    $CODE_CLASSE=trim($sheetdata[$i][21]);
                                    // $LIBELLE_CLASSE=trim($sheetdata[$i][22]);
                                    $cond_code_classe='CODE_CLASSE="'.$CODE_CLASSE.'"';
                                    $bind_code_classe=$this->getBindParms('CLASSE_ID','class_fonctionnelle_classe',$cond_code_classe,'CLASSE_ID ASC');
                                    $bind_code_classe=str_replace('\"','"',$bind_code_classe);
                                    $classe=$this->ModelPs->getRequeteOne($callpsreq,$bind_code_classe);
                                    if(!empty($classe))
                                    {
                                      $CLASSE_ID=$classe['CLASSE_ID'];
                                      // Debut activite PAP
                                      $PAP_ACTIVITE_ID=NULL;
                                      $PAP_ACTIVITE=trim($sheetdata[$i][23]);
                                      $RESULTAT_PAP_ACTIVITE=trim($sheetdata[$i][24]);

                                      if(!empty($PAP_ACTIVITE))
                                      {
                                        $PAP_ACTIVITE=str_replace(["\n", "\r"],' ',$PAP_ACTIVITE);
                                        $PAP_ACTIVITE=str_replace('\n',' ',$PAP_ACTIVITE);
                                        $PAP_ACTIVITE=str_replace('\r',' ',$PAP_ACTIVITE);
                                        $PAP_ACTIVITE=str_replace('"','',$PAP_ACTIVITE);
                                        $PAP_ACTIVITE=str_replace('"','',$PAP_ACTIVITE);
                                        $PAP_ACTIVITE=str_replace("'","\'",$PAP_ACTIVITE);
                                        $PAP_ACTIVITE=str_replace("\\\'","\'",$PAP_ACTIVITE);
                                        $PAP_ACTIVITE=str_replace("\\'","\'",$PAP_ACTIVITE);

                                        if(!empty($RESULTAT_PAP_ACTIVITE))
                                        {
                                          $RESULTAT_PAP_ACTIVITE=str_replace(["\n", "\r"],' ',$RESULTAT_PAP_ACTIVITE);
                                          $RESULTAT_PAP_ACTIVITE=str_replace('\n',' ',$RESULTAT_PAP_ACTIVITE);
                                          $RESULTAT_PAP_ACTIVITE=str_replace('\r',' ',$RESULTAT_PAP_ACTIVITE);
                                          $RESULTAT_PAP_ACTIVITE=str_replace('"','',$RESULTAT_PAP_ACTIVITE);
                                          $RESULTAT_PAP_ACTIVITE=str_replace('"','',$RESULTAT_PAP_ACTIVITE);
                                          $RESULTAT_PAP_ACTIVITE=str_replace("'","\'",$RESULTAT_PAP_ACTIVITE);
                                          $RESULTAT_PAP_ACTIVITE=str_replace("\\\'","\'",$RESULTAT_PAP_ACTIVITE);
                                          $RESULTAT_PAP_ACTIVITE=str_replace("\\'","\'",$RESULTAT_PAP_ACTIVITE);
                                        }

                                        $cond_act_pap='DESC_PAP_ACTIVITE="'.$PAP_ACTIVITE.'" AND CODE_NOMENCLATURE_BUDGETAIRE_ID='.$CODE_NOMENCLATURE_BUDGETAIRE_ID;
                                        $bind_act_pap=$this->getBindParms('PAP_ACTIVITE_ID','pap_activites',$cond_act_pap,'PAP_ACTIVITE_ID ASC');
                                        $bind_act_pap=str_replace('\"','"',$bind_act_pap);
                                        $bind_act_pap=str_replace("\\\'","'",$bind_act_pap);
                                        $bind_act_pap=str_replace("\\'","'",$bind_act_pap);
                                        $activite_pap=$this->ModelPs->getRequeteOne($callpsreq,$bind_act_pap);
                                        if(!empty($activite_pap))
                                        {
                                          $PAP_ACTIVITE_ID=$activite_pap['PAP_ACTIVITE_ID'];
                                        }
                                        else
                                        {
                                          $colums_act_pap="INSTITUTION_ID,SOUS_TUTEL_ID,PROGRAMME_ID,ACTION_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,DESC_PAP_ACTIVITE,RESULTAT_PAP_ACTIVITE";
                                          $value_act_pap=$INSTITUTION_ID.','.$SOUS_TUTEL_ID.','.$PROGRAMME_ID.','.$ACTION_ID.','.$CODE_NOMENCLATURE_BUDGETAIRE_ID.',"'.$PAP_ACTIVITE.'","'.$RESULTAT_PAP_ACTIVITE.'"';
                                          $PAP_ACTIVITE_ID=$this->save_all_table('pap_activites',$colums_act_pap,$value_act_pap);
                                        }
                                        $col_extra.=",PAP_ACTIVITE_ID";
                                        $val_extra.=",".$PAP_ACTIVITE_ID;
                                      }
                                      // Fin activite PAP

                                      // Debut activite costab pss
                                      $COSTAB_ACTIVITE_ID=NULL;
                                      $COSTAB_ACTIVITE=trim($sheetdata[$i][25]);
                                      if(!empty($COSTAB_ACTIVITE))
                                      {
                                        $COSTAB_ACTIVITE=str_replace(["\n", "\r"],' ',$COSTAB_ACTIVITE);
                                        $COSTAB_ACTIVITE=str_replace('\n',' ',$COSTAB_ACTIVITE);
                                        $COSTAB_ACTIVITE=str_replace('\r',' ',$COSTAB_ACTIVITE);
                                        $COSTAB_ACTIVITE=str_replace('"','',$COSTAB_ACTIVITE);
                                        $COSTAB_ACTIVITE=str_replace('"','',$COSTAB_ACTIVITE);
                                        $COSTAB_ACTIVITE=str_replace("'","\'",$COSTAB_ACTIVITE);
                                        $COSTAB_ACTIVITE=str_replace("\\\'","\'",$COSTAB_ACTIVITE);
                                        $COSTAB_ACTIVITE=str_replace("\\'","\'",$COSTAB_ACTIVITE);

                                        $cond_act_pss='DESC_COSTAB_ACTIVITE="'.$COSTAB_ACTIVITE.'"';
                                        $bind_act_pss=$this->getBindParms('COSTAB_ACTIVITE_ID','costab_activites',$cond_act_pss,'COSTAB_ACTIVITE_ID ASC');
                                        $bind_act_pss=str_replace('\"','"',$bind_act_pss);
                                        $activite_pss=$this->ModelPs->getRequeteOne($callpsreq,$bind_act_pss);
                                        if(!empty($activite_pss))
                                        {
                                          $COSTAB_ACTIVITE_ID=$activite_pss['COSTAB_ACTIVITE_ID'];
                                        }
                                        else
                                        {
                                          $colums_act_pss="DESC_COSTAB_ACTIVITE";
                                          $value_act_pss='"'.$COSTAB_ACTIVITE.'"';
                                          $COSTAB_ACTIVITE_ID=$this->save_all_table('costab_activites',$colums_act_pss,$value_act_pss);
                                        }
                                        $col_extra.=",COSTAB_ACTIVITE_ID";
                                        $val_extra.=",".$COSTAB_ACTIVITE_ID;
                                      }
                                      // Fin activite costab pss

                                      // Debut indicateur PND
                                      $PND_INDICATEUR_ID=NULL;
                                      $INDICATEUR_PND=trim($sheetdata[$i][26]);
                                      if(!empty($INDICATEUR_PND))
                                      {
                                        $INDICATEUR_PND=str_replace(["\n", "\r"],' ',$INDICATEUR_PND);
                                        $INDICATEUR_PND=str_replace('\n',' ',$INDICATEUR_PND);
                                        $INDICATEUR_PND=str_replace('\r',' ',$INDICATEUR_PND);
                                        $INDICATEUR_PND=str_replace('"','',$INDICATEUR_PND);
                                        $INDICATEUR_PND=str_replace('"','',$INDICATEUR_PND);
                                        $INDICATEUR_PND=str_replace("'","\'",$INDICATEUR_PND);
                                        $INDICATEUR_PND=str_replace("\\\'","\'",$INDICATEUR_PND);
                                        $INDICATEUR_PND=str_replace("\\'","\'",$INDICATEUR_PND);

                                        $cond_indic_pnd='DESC_INDICATEUR_PND="'.$INDICATEUR_PND.'"';
                                        $bind_indic_pnd=$this->getBindParms('INDICATEUR_PND_ID','pnd_indicateur',$cond_indic_pnd,'INDICATEUR_PND_ID ASC');
                                        $bind_indic_pnd=str_replace('\"','"',$bind_indic_pnd);
                                        $indicateur_pnd=$this->ModelPs->getRequeteOne($callpsreq,$bind_indic_pnd);
                                        if(!empty($indicateur_pnd))
                                        {
                                          $PND_INDICATEUR_ID=$indicateur_pnd['INDICATEUR_PND_ID'];
                                        }
                                        else
                                        {
                                          $colums_indic_pnd="DESC_INDICATEUR_PND";
                                          $value_indic_pnd='"'.$INDICATEUR_PND.'"';
                                          $PND_INDICATEUR_ID=$this->save_all_table('pnd_indicateur',$colums_indic_pnd,$value_indic_pnd);
                                        }
                                        $col_extra.=",PND_INDICATEUR_ID";
                                        $val_extra.=",".$PND_INDICATEUR_ID;
                                      }
                                      // Fin indicateur PND

                                      // Debut code programmatique
                                      $CODES_PROGRAMMATIQUE=trim($sheetdata[$i][27]);
                                      $CODES_PROGRAMMATIQUE=str_replace("'","",$CODES_PROGRAMMATIQUE);
                                      $CODES_PROGRAMMATIQUE=str_replace('"','',$CODES_PROGRAMMATIQUE);
                                      // Fin code programmatique

                                      // Debut tache
                                      $DESC_TACHE=trim($sheetdata[$i][28]);
                                      $RESULTAT_ATTENDUS_TACHE=trim($sheetdata[$i][29]);
                                      $DESC_TACHE=str_replace(["\n", "\r"],' ',$DESC_TACHE);
                                      $DESC_TACHE=str_replace('\n',' ',$DESC_TACHE);
                                      $DESC_TACHE=str_replace('\r',' ',$DESC_TACHE);
                                      $DESC_TACHE=str_replace('"','',$DESC_TACHE);
                                      $DESC_TACHE=str_replace('"','',$DESC_TACHE);
                                      $DESC_TACHE=str_replace("'","\'",$DESC_TACHE);
                                      $DESC_TACHE=str_replace("\\\'","\'",$DESC_TACHE);
                                      $DESC_TACHE=str_replace("\\'","\'",$DESC_TACHE);

                                      $RESULTAT_ATTENDUS_TACHE=str_replace(["\n", "\r"],' ',$RESULTAT_ATTENDUS_TACHE);
                                      $RESULTAT_ATTENDUS_TACHE=str_replace('\n',' ',$RESULTAT_ATTENDUS_TACHE);
                                      $RESULTAT_ATTENDUS_TACHE=str_replace('\r',' ',$RESULTAT_ATTENDUS_TACHE);
                                      $RESULTAT_ATTENDUS_TACHE=str_replace('"','',$RESULTAT_ATTENDUS_TACHE);
                                      $RESULTAT_ATTENDUS_TACHE=str_replace('"','',$RESULTAT_ATTENDUS_TACHE);
                                      $RESULTAT_ATTENDUS_TACHE=str_replace("'","\'",$RESULTAT_ATTENDUS_TACHE);
                                      $RESULTAT_ATTENDUS_TACHE=str_replace("\\\'","\'",$RESULTAT_ATTENDUS_TACHE);
                                      $RESULTAT_ATTENDUS_TACHE=str_replace("\\'","\'",$RESULTAT_ATTENDUS_TACHE);
                                      // Fin tache

                                      // Debut unite, quantite et Budget
                                      $UNITE=trim($sheetdata[$i][30]);
                                      $UNITE=str_replace(["\n", "\r"],' ',$UNITE);
                                      $UNITE=str_replace('\n',' ',$UNITE);
                                      $UNITE=str_replace('\r',' ',$UNITE);
                                      $UNITE=str_replace('"','',$UNITE);
                                      $UNITE=str_replace('"','',$UNITE);
                                      $UNITE=str_replace("'","\'",$UNITE);
                                      $UNITE=str_replace("\\\'","\'",$UNITE);
                                      $UNITE=str_replace("\\'","\'",$UNITE);

                                      $Q_TOTAL=trim($sheetdata[$i][31]);
                                      $QT1=trim($sheetdata[$i][32]);
                                      $QT2=trim($sheetdata[$i][33]);
                                      $QT3=trim($sheetdata[$i][34]);
                                      $QT4=trim($sheetdata[$i][35]);
                                      $COUT_UNITAIRE=trim($sheetdata[$i][36]);
                                      $BUDGET_T1=trim($sheetdata[$i][37]);
                                      $BUDGET_RESTANT_T1=$BUDGET_T1;
                                      $BUDGET_T2=trim($sheetdata[$i][38]);
                                      $BUDGET_RESTANT_T2=$BUDGET_T2;
                                      $BUDGET_T3=trim($sheetdata[$i][39]);
                                      $BUDGET_RESTANT_T3=$BUDGET_T3;
                                      $BUDGET_T4=trim($sheetdata[$i][40]);
                                      $BUDGET_RESTANT_T4=$BUDGET_T4;
                                      $BUDGET_ANNUEL=trim($sheetdata[$i][41]);
                                      // Fin unite, quantite et Budget

                                      // Debut structure responsable
                                      $STRUTURE_RESPONSABLE_TACHE_ID=NULL;
                                      $STRUTURE_RESPONSABLE=trim($sheetdata[$i][42]);
                                      if(!empty($STRUTURE_RESPONSABLE))
                                      {
                                        $cond_struct_resp='DESC_STRUTURE_RESPONSABLE_TACHE="'.$STRUTURE_RESPONSABLE.'"';
                                        $bind_struct_resp=$this->getBindParms('STRUTURE_RESPONSABLE_TACHE_ID','struture_responsable_tache',$cond_struct_resp,'STRUTURE_RESPONSABLE_TACHE_ID ASC');
                                        $bind_struct_resp=str_replace('\"','"',$bind_struct_resp);
                                        $structure_responsable=$this->ModelPs->getRequeteOne($callpsreq,$bind_struct_resp);
                                        if(!empty($structure_responsable))
                                        {
                                          $STRUTURE_RESPONSABLE_TACHE_ID=$structure_responsable['STRUTURE_RESPONSABLE_TACHE_ID'];
                                        }
                                        else
                                        {
                                          $colums_struct_resp="DESC_STRUTURE_RESPONSABLE_TACHE";
                                          $value_struct_resp='"'.$STRUTURE_RESPONSABLE.'"';
                                          $STRUTURE_RESPONSABLE_TACHE_ID=$this->save_all_table('struture_responsable_tache',$colums_struct_resp,$value_struct_resp);
                                        }
                                        $col_extra.=",STRUTURE_RESPONSABLE_TACHE_ID";
                                        $val_extra.=",".$STRUTURE_RESPONSABLE_TACHE_ID;
                                      }
                                      // Fin structure responsable

                                      // Debut grande masse
                                      $GRANDE_MASSE_ID=trim($sheetdata[$i][43]);
                                      // Fin grande masse

                                      // Debut enregistrement d'une tache
                                      $insertIntoTable='ptba_tache';
                                      $columsinserte="INSTITUTION_ID,SOUS_TUTEL_ID,ID_PILIER,OBJECTIF_VISION_ID,AXE_PND_ID,PROGRAMME_ID,ACTION_ID".$col_extra.",CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,CODES_PROGRAMMATIQUE,DESC_TACHE,RESULTAT_ATTENDUS_TACHE,UNITE,Q_TOTAL,QT1,QT2,QT3,QT4,COUT_UNITAIRE,BUDGET_T1,BUDGET_RESTANT_T1,BUDGET_T2,BUDGET_RESTANT_T2,BUDGET_T3,BUDGET_RESTANT_T3,BUDGET_T4,BUDGET_RESTANT_T4,BUDGET_ANNUEL,GRANDE_MASSE_ID,ARTICLE_ID,SOUS_LITTERA_ID,DIVISION_ID,GROUPE_ID,CLASSE_ID";
                                      $valuecolumsinserte=$INSTITUTION_ID.','.$SOUS_TUTEL_ID.','.$ID_PILIER.','.$OBJECTIF_VISION_ID.','.$AXE_PND_ID.','.$PROGRAMME_ID.','.$ACTION_ID.''.$val_extra.','.$CODE_NOMENCLATURE_BUDGETAIRE_ID.',"'.$CODE_NOMENCLATURE_BUDGETAIRE.'","'.$CODES_PROGRAMMATIQUE.'","'.$DESC_TACHE.'","'.$RESULTAT_ATTENDUS_TACHE.'","'.$UNITE.'","'.$Q_TOTAL.'","'.$QT1.'","'.$QT2.'","'.$QT3.'","'.$QT4.'","'.$COUT_UNITAIRE.'","'.$BUDGET_T1.'","'.$BUDGET_RESTANT_T1.'","'.$BUDGET_T2.'","'.$BUDGET_RESTANT_T2.'","'.$BUDGET_T3.'","'.$BUDGET_RESTANT_T3.'","'.$BUDGET_T4.'","'.$BUDGET_RESTANT_T4.'","'.$BUDGET_ANNUEL.'",'.$GRANDE_MASSE_ID.','.$ARTICLE_ID.','.$SOUS_LITTERA_ID.','.$DIVISION_ID.','.$GROUPE_ID.','.$CLASSE_ID;
                                      $PTBA_TACHE_ID=$this->save_all_table($insertIntoTable,$columsinserte,$valuecolumsinserte);
                                      // Fin enregistrement d'une tache
                                    }
                                    // Fin classe fonctionnelle
                                  }
                                  // Fin groupe fonctionnelle
                                }
                                // Fin division fonctionnelle
                              }
                              // Fin nature economique(sous littera)
                            }
                            // Fin Article economique
                          }
                          //  Debut code nomenclature budgetaire
                        }
                        // Fin Gestion du sous titre
                      }
                      // Fin action
                    }
                    // Fin programmation
                  }
                  // Fin axe du PND
                }
              }
              // Fin objectif vision
            }
            // Fin Pilier
          }
          // Fin Institution
        }
      }
    }
    return redirect('donnees/Ptba_Nouveau_Format');
  }
}
?>