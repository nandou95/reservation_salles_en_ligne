<?php
  /**
    *MUNEZERO SONIA
    *Titre: phase administrative
    *Numero de telephone: (+257) 65165772
    *Email: sonia@mediabox.bi
    *Date: 30 novembre,2023
    **/
namespace App\Modules\process\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Process_planification_Cdmt_Cbmt extends BaseController
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

  public function uploadFile($fieldName,$folder,$prefix= ''): string
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

  public function getBindParms($columnselect,$table,$where,$orderby)
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

  // affiche le view pour la 1er etape d'engagement budgetaire (engage)
  function detail_cdmt($id=0)
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $infosData = 'SELECT ID_DEMANDE,ETAPE_ID,PROCESS_ID FROM `proc_demandes` WHERE md5(ID_DEMANDE)="'.$id.'"';
    $infosData = "CALL `getTable`('" . $infosData . "');";
    $resultat= $this->ModelPs->getRequeteOne($infosData);

    $note_cbmt = 'SELECT  ID_DEMANDE, PATH_NOTE_CADRAGE,etap.DESCR_ETAPE FROM planification_demande_cadrage_cbmt note JOIN proc_etape etap ON etap.ETAPE_ID=note.ETAPE_ID WHERE md5(ID_DEMANDE) = "'.$id.'"';
    $note_cbmt = "CALL `getTable`('" . $note_cbmt . "');";
    $data['get_note_cbmt']= $this->ModelPs->getRequete($note_cbmt);

    if ($resultat['PROCESS_ID'] != 10)
    {
      return redirect('Login_Ptba/do_logout');
    }
    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ID_PROFIL_ETAPE,ETAPE_ID,PROFIL_ID','proc_profil_etape','ETAPE_ID='.$resultat['ETAPE_ID'].'','ID_PROFIL_ETAPE DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if(!empty($getProfil))
    {
      foreach($getProfil as $value)
      {

        if($prof_id == $value->PROFIL_ID || $prof_id==1)
        {

          $getEtape  = 'SELECT proc_demandes.ETAPE_ID FROM proc_demandes WHERE proc_demandes.ID_DEMANDE='.$resultat['ID_DEMANDE'].' AND proc_demandes.PROCESS_ID= '.$resultat['PROCESS_ID'];
          $getEtape = "CALL `getTable`('" . $getEtape . "');";
          $getEtape = $this->ModelPs->getRequeteOne($getEtape);
          $ETAPE_ID = $getEtape['ETAPE_ID'];

          #############################################################################  #####################
          //Informations pour detailler les informations d'une demande
          $infoAffiche = 'SELECT proc_demandes.ID_DEMANDE,proc_demandes.CODE_DEMANDE,proc_demandes.DATE_INSERTION,proc_process.NOM_PROCESS,proc_etape.DESCR_ETAPE,user_users.NOM,user_users.PRENOM FROM proc_demandes JOIN proc_process ON proc_process.PROCESS_ID=proc_demandes.PROCESS_ID JOIN proc_etape ON proc_etape.ETAPE_ID=proc_demandes.ETAPE_ID JOIN user_users ON user_users.USER_ID=proc_demandes.USER_ID WHERE proc_demandes.ID_DEMANDE='.$resultat['ID_DEMANDE'].' AND proc_demandes.PROCESS_ID='.$resultat['PROCESS_ID'];
          $infoAffiche = "CALL `getTable`('" . $infoAffiche . "');";
          $data['infoAffiche'] = $this->ModelPs->getRequeteOne($infoAffiche);

          ###########################################################           ###############################

          $PROFIL_ID = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
          $getAction = 'SELECT proc_demandes.ID_DEMANDE,proc_actions.IS_INITIAL ,proc_actions.GET_FORM,proc_actions.LINK_FORM,proc_actions.ACTION_ID, proc_actions.ETAPE_ID, proc_actions.MOVETO,proc_actions.IS_REQUIRED, proc_actions.DESCR_ACTION, proc_etape.DESCR_ETAPE, proc_demandes.PROCESS_ID FROM proc_actions JOIN proc_etape ON proc_etape.ETAPE_ID=proc_actions.ETAPE_ID JOIN proc_demandes ON proc_actions.ETAPE_ID=proc_demandes.ETAPE_ID WHERE proc_actions.ETAPE_ID='.$ETAPE_ID.' AND proc_demandes.PROCESS_ID= '.$resultat['PROCESS_ID'].' AND proc_demandes.ID_DEMANDE = '.$resultat['ID_DEMANDE'].'';
          $getAction = "CALL `getTable`('".$getAction."');";
          $data['getAction'] = $this->ModelPs->getRequete($getAction);
          return view('App\Modules\process\Views\Process_Planification_Cdmt_Cbmt_View',$data);
        }  
      }
      return redirect('Login_Ptba/do_logout'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }
  }

  public function getAction($value='')
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ACTION_ID = $this->request->getPost('ACTION_ID');  
    //get etape suivant et les actions
    $getAction  = 'SELECT DESCR_ACTION FROM proc_actions WHERE ACTION_ID='.$ACTION_ID.'';
    $getAction = "CALL `getTable`('" . $getAction . "');";
    $getActionData = $this->ModelPs->getRequeteOne($getAction);
    $DESCR_ACTION = $getActionData['DESCR_ACTION'];
    $output = array('DESCR_ACTION'=>$DESCR_ACTION);
    return $this->response->setJSON($output);
  }

  public function envoyer($value='')
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $USER_ID  = session()->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    $COMMENTAIRE = $this->request->getPost('COMMENTAIRE');
    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    $ACTION_ID = $this->request->getPost('ACTION_ID');
    $MOVETO = $this->request->getPost('MOVETO');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $IS_REQUIRED = $this->request->getPost('IS_REQUIRED');

    $insertIntoTable='proc_demandes_historique';
    $columsinsert="ID_DEMANDE,ETAPE_ID,USER_ID,ACTION_ID,COMMENTAIRE";
    $datacolumsinsert=$ID_DEMANDE.','.$ETAPE_ID.','.$USER_ID.','.$ACTION_ID.',"'.$COMMENTAIRE.'"';
    $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

    //get process_id suivant 
    $getprocess  = 'SELECT PROCESS_ID FROM proc_etape WHERE ETAPE_ID='.$MOVETO.'';
    $getprocess = "CALL `getTable`('" . $getprocess . "');";
    $PROCESS_ID = $this->ModelPs->getRequeteOne($getprocess);

    //mise à jour dans la table proc_demandes / on recupere Next étape
    $table = 'proc_demandes';
    $where='ID_DEMANDE='.$ID_DEMANDE.'';
    $data='ETAPE_ID='.$MOVETO.', PROCESS_ID='.$PROCESS_ID['PROCESS_ID'];
    $this->update_all_table($table,$data,$where);
    $data=['message' => lang('messages_lang.message_success')];
    session()->setFlashdata('alert', $data);
    return redirect('process/Demandes');
  }

  public function envoyernote($value='')
  {  
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $USER_ID  = session()->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    $COMMENTAIRE = $this->request->getPost('COMMENTAIREnote');
    $ID_DEMANDE = $this->request->getPost('ID_DEMANDEnote');
    $ACTION_ID = $this->request->getPost('ACTION_IDnote');
    $MOVETO = $this->request->getPost('MOVETOnote');
    $ETAPE_ID = $this->request->getPost('ETAPE_IDnote');
    $GET_FORM = $this->request->getPost('GET_FORMnote');
    $IS_REQUIRED = $this->request->getPost('IS_REQUIREDnote');
    $PATH_NOTE_CADRAGE=$this->request->getFile('PATH_NOTE_CADRAGE');
    $maxFileSize = 200 * 1024;
    if($PATH_NOTE_CADRAGE->getSize() > $maxFileSize)
    {
      $data=[
        'message' => lang('messages_lang.pdf_max')
      ];
      session()->setFlashdata('alert', $data);
      return $this->detail_cdmt(md5($ID_DEMANDE));
    }

    if($IS_REQUIRED==1)
    {
      $PATH_NOTE_CADRAGE_DOC=$this->uploadFile('PATH_NOTE_CADRAGE','process','NOTE_CADRAGE');
      $insertIntoDoc='planification_demande_cadrage_cbmt';
      $columsinsertDoc="ID_DEMANDE,PATH_NOTE_CADRAGE,ETAPE_ID";
      $datacolumsinsertDoc=$ID_DEMANDE.',"'.$PATH_NOTE_CADRAGE_DOC.'",'.$ETAPE_ID;
      $this->save_all_table($insertIntoDoc,$columsinsertDoc,$datacolumsinsertDoc);
    }
    else
    {
      $PATH_NOTE_CADRAGE_up='';
      if (!$PATH_NOTE_CADRAGE || !$PATH_NOTE_CADRAGE->isValid())
      {
        $PATH_NOTE_CADRAGE_up=$this->request->getPost('PATH_NOTE_CADRAGE_old');
      }
      else
      {
        $PATH_NOTE_CADRAGE_up=$this->uploadFile('PATH_NOTE_CADRAGE','process','NOTE_CADRAGE');
      }

      $tableup = 'planification_demande_cadrage_cbmt';
      $whereup='ID_DEMANDE='.$ID_DEMANDE.'';
      $dataup='PATH_NOTE_CADRAGE = "'.$PATH_NOTE_CADRAGE_up.'", ETAPE_ID = '.$ETAPE_ID;
      $this->update_all_table($tableup,$dataup,$whereup);
    }
    
    $insertIntoTable='proc_demandes_historique';
    $columsinsert="ID_DEMANDE,ETAPE_ID,USER_ID,ACTION_ID,COMMENTAIRE";
    $datacolumsinsert=$ID_DEMANDE.','.$ETAPE_ID.','.$USER_ID.','.$ACTION_ID.',"'.$COMMENTAIRE.'"';
    $this->save_all_table($insertIntoTable,$columsinsert,$datacolumsinsert);

    //get process_id suivant 
    $getprocess  = 'SELECT PROCESS_ID FROM proc_etape WHERE ETAPE_ID='.$MOVETO.'';
    $getprocess = "CALL `getTable`('" . $getprocess . "');";
    $PROCESS_ID = $this->ModelPs->getRequeteOne($getprocess);

    //mise à jour dans la table proc_demandes / on recupere Next étape
    $table = 'proc_demandes';
    $where='ID_DEMANDE='.$ID_DEMANDE.'';
    $data='ETAPE_ID='.$MOVETO.', PROCESS_ID='.$PROCESS_ID['PROCESS_ID'];
    $this->update_all_table($table,$data,$where);
    $data=['message' => lang('messages_lang.message_success')];
    session()->setFlashdata('alert', $data);
    return redirect('process/Demandes');
  }

  public function historique()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    $id='';
    if(!empty($ID_DEMANDE))
    {
      $id=" AND proc_demandes.ID_DEMANDE = ".$ID_DEMANDE;
    }

    $query_principal="SELECT proc_demandes.CODE_DEMANDE, proc_etape.DESCR_ETAPE, proc_actions.DESCR_ACTION, proc_demandes_historique.COMMENTAIRE, user_users.NOM, user_users.PRENOM, proc_demandes_historique.DATE_INSERTION, proc_demandes_historique.ID_HISTORIQUE,user_profil.PROFIL_DESCR FROM proc_demandes_historique JOIN proc_demandes ON proc_demandes.ID_DEMANDE=proc_demandes_historique.ID_DEMANDE JOIN proc_actions ON proc_actions.ACTION_ID=proc_demandes_historique.ACTION_ID JOIN proc_etape ON proc_etape.ETAPE_ID=proc_demandes_historique.ETAPE_ID JOIN user_users ON user_users.USER_ID=proc_demandes_historique.USER_ID JOIN user_profil ON user_profil.PROFIL_ID=user_users.PROFIL_ID WHERE 1".$id;

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    //$var_search = str_replace("'", "''", $var_search);
    $limit="LIMIT 0,10";
    if($_POST['length'] != -1)
    {
      $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
    }

    $order_by="";
    $order_column="";
    $order_column= array(1,'proc_etape.DESCR_ETAPE','proc_actions.DESCR_ACTION', 'COMMENTAIRE','user_users.NOM','user_users.PRENOM','DATE_INSERTION');

    $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY ID_HISTORIQUE DESC";

    $search = !empty($_POST['search']['value']) ?  (" AND (COMMENTAIRE LIKE '%$var_search%' OR user_users.NOM LIKE '%$var_search%' OR proc_etape.DESCR_ETAPE LIKE '%$var_search%' OR proc_actions.DESCR_ACTION LIKE '%$var_search%' OR user_users.PRENOM LIKE '%$var_search%' OR date_format(proc_demandes.DATE_INSERTION,'d-m-Y') LIKE '%$var_search%' )"):'';
    $search = str_replace("'","\'",$search);
    $critaire = " ";

    $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;

    $query_filter = $query_principal." ".$search." ".$critaire;

    $requete='CALL `getTable`("'.$query_secondaire.'");';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $row)
    {
      $sub_array = array();

      if (strlen($row->DESCR_ETAPE) > 3)
      {
        $DESCR_ETAPE =  mb_substr($row->DESCR_ETAPE, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_ETAPE.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_ETAPE =  $row->DESCR_ETAPE;
      }

      if(strlen($row->COMMENTAIRE) > 12)
      {
        $COMMENTAIRE =  mb_substr($row->COMMENTAIRE, 0, 12) .'...<a class="btn-sm" title="'.$row->COMMENTAIRE.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $COMMENTAIRE =  $row->COMMENTAIRE;
      }

      if(strlen($row->DESCR_ACTION) > 3)
      {
        $DESCR_ACTION =  mb_substr($row->DESCR_ACTION, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_ACTION.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_ACTION =  $row->DESCR_ACTION;
      }

      if(strlen($row->PROFIL_DESCR) > 3)
      {
        $PROFIL_DESCR =  mb_substr($row->PROFIL_DESCR, 0, 3) .'...<a class="btn-sm" title="'.$row->PROFIL_DESCR.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $PROFIL_DESCR =  $row->PROFIL_DESCR;
      }

      if(strlen($row->NOM.' '.$row->PRENOM) > 3)
      {
        $nom =  mb_substr($row->NOM.' '.$row->PRENOM, 0, 3) .'...<a class="btn-sm" title="'.$row->NOM.' '.$row->PRENOM.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $nom =  $row->NOM.' '.$row->PRENOM;
      }

      $sub_array[]=$u++;
      $sub_array[]=$DESCR_ETAPE;
      $sub_array[]=$DESCR_ACTION;
      $sub_array[]=$COMMENTAIRE;
      $sub_array[]=$PROFIL_DESCR;
      $sub_array[]=$nom;
      $sub_array[]=date('d-m-Y',strtotime($row->DATE_INSERTION));
      $data[] = $sub_array;
    }

    $requeteqp='CALL `getTable`("'.$query_principal.'");';
    $recordsTotal = $this->ModelPs->datatable( $requeteqp);
    $requeteqf='CALL `getTable`("'.$query_filter.'");';
    $recordsFiltered = $this->ModelPs->datatable( $requeteqf);
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }

  public function cl_cmr()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    $id='';
    // if(!empty($ID_DEMANDE))
    // {
    //   $id=' AND cl.ID_DEMANDE = '.$ID_DEMANDE;
    // }

    $query_principal='SELECT cl_cmr.PRECISIONS, cl_cmr.REFERENCE, cl_cmr.CIBLE, pilier.DESCR_PILIER, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE FROM planification_demande_cl_cmr cl_cmr JOIN cl_cmr_costab_categorie ON cl_cmr_costab_categorie.ID_CL_CMR_COSTAB_CATEGORIE =cl_cmr.ID_CL_CMR_CATEGORIE JOIN pilier ON pilier.ID_PILIER=cl_cmr.ID_PILIER JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =cl_cmr.ID_OBJECT_STRATEGIQUE  JOIN objectif_strategique_indicateur ON objectif_strategique_indicateur.ID_INDICACTEUR_OBJECT_STRATEGIQUE =cl_cmr.ID_PLANS_INDICATEUR WHERE cl_cmr.ID_DEMANDE='.$ID_DEMANDE.' AND cl_cmr.ID_CL_CMR_CATEGORIE=1';

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    //$var_search = str_replace("'", "''", $var_search);
    $limit="LIMIT 0,10";
    if($_POST['length'] != -1)
    {
      $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
    }

    $order_by="";
    $order_column="";
    $order_column= array(1,'pilier.DESCR_PILIER','objectif_strategique.DESCR_OBJECTIF_STRATEGIC', 'objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE','cl_cmr.PRECISIONS','cl_cmr.REFERENCE','cl_cmr.CIBLE');

    $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY ID_PLANS_DEMANDE_CL_CMR ASC";

    $search = !empty($_POST['search']['value']) ?  (' AND (pilier.DESCR_PILIER LIKE "%'.$var_search.'%" OR objectif_strategique.DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE LIKE "%'.$var_search.'%" OR cl_cmr.PRECISIONS LIKE "%'.$var_search.'%" OR cl_cmr.REFERENCE LIKE "%'.$var_search.'%" OR cl_cmr.CIBLE LIKE "%'.$var_search.'%")'):"";
    $search = str_replace("'","\'",$search);
    $critaire = " ";

    $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;

    $query_filter = $query_principal." ".$search." ".$critaire;

    $requete="CALL `getList`('".$query_secondaire."')";
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $row)
    {
      $sub_array = array();

      if(strlen($row->DESCR_PILIER) > 6)
      {
        $DESCR_PILIER =  mb_substr($row->DESCR_PILIER, 0, 6) .'...<a class="btn-sm" title="'.$row->DESCR_PILIER.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_PILIER =  $row->DESCR_PILIER;
      }

      if(strlen($row->DESCR_OBJECTIF_STRATEGIC) > 6)
      {
        $DESCR_OBJECTIF_STRATEGIC =  mb_substr($row->DESCR_OBJECTIF_STRATEGIC, 0, 6) .'...<a class="btn-sm" title="'.$row->DESCR_OBJECTIF_STRATEGIC.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_OBJECTIF_STRATEGIC =  $row->DESCR_OBJECTIF_STRATEGIC;
      }

      if(strlen($row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE) > 12)
      {
        $DESC_INDICACTEUR_OBJECT_STRATEGIQUE =  mb_substr($row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE, 0, 12) .'...<a class="btn-sm" title="'.$row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESC_INDICACTEUR_OBJECT_STRATEGIQUE =  $row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE;
      }

      $sub_array[]= $u++;
      $sub_array[]= $DESCR_PILIER;
      $sub_array[]= $DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]= $DESC_INDICACTEUR_OBJECT_STRATEGIQUE;
      $sub_array[]= $row->REFERENCE;
      $sub_array[]= $row->CIBLE;
      $sub_array[]= $row->PRECISIONS;

      $sub_array[]='edit';
      $data[] = $sub_array;
    }

    $requeteqp="CALL `getList`('".$query_principal."')";
    $recordsTotal = $this->ModelPs->datatable( $requeteqp);
    $requeteqf="CALL `getList`('".$query_filter."')";
    $recordsFiltered = $this->ModelPs->datatable( $requeteqf);
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }

  public function costab()
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    $id='';
    $query_principal='SELECT costab.BUDGET_ANNE_1, costab.BUDGET_ANNE_2, costab.BUDGET_ANNE_3, costab.BUDGET_ANNE_4, costab.BUDGET_ANNE_5, costab.BUDGET_TOTAL, enjeux.DESCR_ENJEUX, pilier.DESCR_PILIER, axe_intervention_pnd.DESCR_AXE_INTERVATION_PND, objectif_strategique.DESCR_OBJECTIF_STRATEGIC, programme_pnd.DESCR_PROGRAMME, ID_PLANS_PROJET FROM planification_demande_costab costab JOIN cl_cmr_costab_categorie ON cl_cmr_costab_categorie.ID_CL_CMR_COSTAB_CATEGORIE=costab.ID_COSTAB_CATEGORIE JOIN enjeux ON enjeux.ID_ENJEUX =costab.ID_ENJEUX JOIN pilier ON pilier.ID_PILIER=costab.ID_PILIER JOIN axe_intervention_pnd ON axe_intervention_pnd.ID_AXE_INTERVENTION_PND =costab.ID_AXE_INTERVENTION_PND JOIN objectif_strategique ON objectif_strategique.ID_OBJECT_STRATEGIQUE =costab.ID_OBJECT_STRATEGIQUE JOIN programme_pnd ON programme_pnd.ID_PROGRAMME_PND=costab.PROGRAMME_ID WHERE costab.ID_DEMANDE='.$ID_DEMANDE.'  AND costab.ID_COSTAB_CATEGORIE=1';

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit="LIMIT 0,10";
    if($_POST['length'] != -1)
    {
      $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
    }

    $order_by="";
    $order_column="";
    $order_column= array(1,'enjeux.DESCR_ENJEUX','pilier.DESCR_PILIER', 'axe_intervention_pnd.DESCR_AXE_INTERVATION_PND','objectif_strategique.DESCR_OBJECTIF_STRATEGIC','programme_pnd.DESCR_PROGRAMME','ID_PLANS_PROJET',1,1,1,1,1,1);

    $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY ID_PLANS_DEMANDE_COSTAB ASC";

    $search = !empty($_POST['search']['value']) ?  (' AND (enjeux.DESCR_ENJEUX LIKE "%'.$var_search.'%" OR pilier.DESCR_PILIER LIKE "%'.$var_search.'%" OR axe_intervention_pnd.DESCR_AXE_INTERVATION_PND LIKE "%'.$var_search.'%" OR objectif_strategique.DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR programme_pnd.DESCR_PROGRAMME LIKE "%'.$var_search.'%" OR ID_PLANS_PROJET LIKE "%'.$var_search.'%")'):"";
    $search = str_replace("'","\'",$search);
    $critaire = " ";
    $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;

    $query_filter = $query_principal." ".$search." ".$critaire;

    $requete="CALL `getList`('".$query_secondaire."')";
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);

    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $row)
    {
      $sub_array = array();

      if(strlen($row->DESCR_ENJEUX) > 3)
      {
        $DESCR_ENJEUX =  mb_substr($row->DESCR_ENJEUX, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_ENJEUX.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_ENJEUX =  $row->DESCR_ENJEUX;
      }

      if(strlen($row->DESCR_PILIER) > 3)
      {
        $DESCR_PILIER =  mb_substr($row->DESCR_PILIER, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_PILIER.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_PILIER =  $row->DESCR_PILIER;
      }

      if(strlen($row->DESCR_AXE_INTERVATION_PND) > 3)
      {
        $DESCR_AXE_INTERVATION_PND =  mb_substr($row->DESCR_AXE_INTERVATION_PND, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_AXE_INTERVATION_PND.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_AXE_INTERVATION_PND =  $row->DESCR_AXE_INTERVATION_PND;
      }

      if(strlen($row->DESCR_OBJECTIF_STRATEGIC) > 3)
      {
        $DESCR_OBJECTIF_STRATEGIC =  mb_substr($row->DESCR_OBJECTIF_STRATEGIC, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_OBJECTIF_STRATEGIC.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_OBJECTIF_STRATEGIC =  $row->DESCR_OBJECTIF_STRATEGIC;
      }

      if(strlen($row->DESCR_PROGRAMME) > 3)
      {
        $DESCR_PROGRAMME =  mb_substr($row->DESCR_PROGRAMME, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_PROGRAMME.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $DESCR_PROGRAMME =  $row->DESCR_PROGRAMME;
      }

      if(strlen($row->ID_PLANS_PROJET) > 3)
      {
        $ID_PLANS_PROJET =  mb_substr($row->ID_PLANS_PROJET, 0, 3) .'...<a class="btn-sm" title="'.$row->ID_PLANS_PROJET.'"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $ID_PLANS_PROJET =  $row->ID_PLANS_PROJET;
      }

      $sub_array[]= $u++;
      $sub_array[]= $DESCR_ENJEUX;
      $sub_array[]= $DESCR_PILIER;
      $sub_array[]= $DESCR_AXE_INTERVATION_PND;
      $sub_array[]= $DESCR_OBJECTIF_STRATEGIC;
      $sub_array[]= $DESCR_PROGRAMME;
      $sub_array[]= $ID_PLANS_PROJET;
      $sub_array[]=number_format($row->BUDGET_ANNE_1,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_ANNE_2,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_ANNE_3,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_ANNE_4,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_ANNE_5,0,',',' ');
      $sub_array[]=number_format($row->BUDGET_TOTAL,0,',',' ');

      $data[] = $sub_array;
    }

    $requeteqp="CALL `getList`('".$query_principal."')";
    $recordsTotal = $this->ModelPs->datatable( $requeteqp);
    $requeteqf="CALL `getList`('".$query_filter."')";
    $recordsFiltered = $this->ModelPs->datatable( $requeteqf);
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" =>count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }
}
?>
