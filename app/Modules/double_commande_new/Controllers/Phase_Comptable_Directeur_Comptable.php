<?php

/**
 * 
 * *MUGISHA Jemapess
 *Numero de telephone: (+257) 68001621
 *Email: jemapess.mugisha@mediabox.bi
 *Date: 19 Decembre,2023
 **/

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Phase_Comptable_Directeur_Comptable extends BaseController
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

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    // code...
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  /* Debut Gestion insertion */
  public function save_all_table($table, $columsinsert, $datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams = [$table, $columsinsert, $datacolumsinsert];
    $result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
    return $id = $result['id'];
  }
  /* Fin Gestion insertion */

  //Update
  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  public function uploadFile($fieldName, $folder, $prefix = ''): string
  {
    $prefix = ($prefix === '') ? uniqid() : $prefix;
    $path = '';

    $file = $this->request->getFile($fieldName);

    $folderPath = ROOTPATH . 'public/uploads/' . $folder;
    if (!is_dir($folderPath)) {
      mkdir($folderPath, 0777, true);
    }

    if ($file->isValid() && !$file->hasMoved()) {
      $newName = $prefix . '_' . uniqid() . '' . date('ymdhis') . '.' . $file->getExtension();
      $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
      $path = 'uploads/' . $folder . '/' . $newName;
    }
    return $path;
  }

  //afficher le view du phase comptable
  function formulaire()
  {
    $etape_actuel=21;
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($this->session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_DIR_COMPTABLE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $execution_id = $this->getBindParms('EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dc.ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE,MOUVEMENT_DEPENSE_ID,td.TITRE_DECAISSEMENT','execution_budgetaire_titre_decaissement td JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID','td.ETAPE_DOUBLE_COMMANDE_ID='.$etape_actuel.'', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
          $execution_id = str_replace('\\', '', $execution_id);
          $id_execution= $this->ModelPs->getRequeteOne($callpsreq, $execution_id);

          //titre etape
          $data['titre_etape'] = $this->ModelPs->getRequeteOne($callpsreq, $execution_id);

          //--chargement du 'multiselect titre decaissement'-- 
          $data['get_titre_decaissement']= $this->ModelPs->getRequete($callpsreq, $execution_id);

          //statut_document_bordereau_transmission--
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $type_operation = $this->getBindParms('STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID ,DESC_STATUT_OPERATION_BORDEREAU_TRANSMISSION','statut_operation_bordereau_transmission','STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=1','STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID DESC');
          $data['type_document_transmissions'] = $this->ModelPs->getRequeteOne($callpsreq, $type_operation);

          //type document---
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $type_document = $this->getBindParms('TYPE_DOCUMENT_ID', 'type_document', 'TYPE_DOCUMENT_ID ='. 2,' TYPE_DOCUMENT_ID  DESC');
          $data['type_documents'] = $this->ModelPs->getRequeteOne($callpsreq, $type_document);

            
          //--chargement de l'input origine destination'--
          $origine_destination ='SELECT origine.ID_ORIGINE_DESTINATION ,origine.ORIGINE, origine.DESTINATION FROM origine_destination  origine WHERE origine.IS_ACTIVE =1 AND origine.ID_ORIGINE_DESTINATION =2';   
          $origine_destinations = "CALL `getTable`('" . $origine_destination . "');";
          $data['get_origine_destination']= $this->ModelPs->getRequeteOne($origine_destinations);

            //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='.$id_execution['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID'],'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\','',$bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          return view('App\Modules\double_commande_new\Views\Phase_Comptable_Directeur_Comptable_View.php',$data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }    
  }
  function insert_in_execution_budgetaire_bordereau_transmission ($column, $data) 
  {
   $insertReq="CALL insertLastIdIntoTableColonnes(?,?,?);";
   return $this->save_all_table('execution_budgetaire_bordereau_transmission', $column, $data);
 }

 function insert_in_execution_budgetaire_bordereau_transmission_bon_titre ($column, $data) 
 {
  $insertReq="CALL insertLastIdIntoTableColonnes(?,?,?);";
  return $this->save_all_table('execution_budgetaire_bordereau_transmission_bon_titre', $column, $data);
}

function save()
{
  $data = $this->urichk();
  $session  = \Config\Services::session();
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
  if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
  {
    return redirect('Login_Ptba/do_logout');
  }

  if($this->session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_DIR_COMPTABLE') !=1)
  {
    return redirect('Login_Ptba/homepage'); 
  }

  //Form validation
  $rules = [
    'NUM_BORDEREAU_TRANSMISSION' => [
      'rules' => 'required',
      'errors' => [
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
      ]
    ],

    'DATE_TRANSMISSION' => [
      'rules' => 'required',
      'errors' => [
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
      ]
    ],
  ];

  $this->validation->setRules($rules);
  if($this->validation->withRequest($this->request)->run())
  {
    //---insertion dans 'execution_bord_transmission new'-
    $db = db_connect();
    $selected_value = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID[]');
    $NUM_BORDEREAU_TRANSMISSION = $this->request->getPost('NUM_BORDEREAU_TRANSMISSION');
    $PATH_BORDEREAU_TRANSMISSION = $this->uploadFile('PATH_BORDEREAU_TRANSMISSION','double_commande_new','PATH_BORDEREAU_TRANSMISSION');
    $ID_ORIGINE_DESTINATION = $this->request->getPost('ID_ORIGINE_DESTINATION');
    $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
    $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
    $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
    $STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID = $this->request->getPost('STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID');
    $TYPE_DOCUMENT_ID = $this->request->getPost('TYPE_DOCUMENT_ID');        
    $USER_ID = $user_id;

    $colum="NUMERO_BORDEREAU_TRANSMISSION,PATH_BORDEREAU_TRANSMISSION,ID_ORIGINE_DESTINATION,USER_ID,DATE_TRANSMISSION_BD, STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID ";
    $datacolums = "'{$NUM_BORDEREAU_TRANSMISSION}', '{$PATH_BORDEREAU_TRANSMISSION}', '{$ID_ORIGINE_DESTINATION}', '{$USER_ID}','{$DATE_TRANSMISSION}','{$STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID}'";
    $insertData = $this->insert_in_execution_budgetaire_bordereau_transmission($colum,$datacolums);        

    //--execution budgetaire bordereau transmission bon -
    foreach ($selected_value as $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)
    {
      //numero document--et--execution_budgetaire_id
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $num = $this->getBindParms('TITRE_DECAISSEMENT,EXECUTION_BUDGETAIRE_ID','execution_budgetaire_titre_decaissement','EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID='. $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
      $numero_titre= $this->ModelPs->getRequeteOne($callpsreq, $num);
      $numero_titre_decaissement=$numero_titre['TITRE_DECAISSEMENT'];
      $id_exec_budgetaire=$numero_titre['EXECUTION_BUDGETAIRE_ID'];

      // $insertInto='execution_budgetaire_bordereau_transmission_bon_titre';
      $colum="BORDEREAU_TRANSMISSION_ID,TYPE_DOCUMENT_ID,EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,NUMERO_DOCUMENT,USER_ID, STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID ";
      $datacolums = "'{$insertData}', '{$TYPE_DOCUMENT_ID}', '{$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID}', '{$numero_titre_decaissement}', '{$USER_ID}','{$STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID}'";
      $this->insert_in_execution_budgetaire_bordereau_transmission_bon_titre($colum,$datacolums);

      //--insertion dans 'historique_raccrochage'
      $historique_table_detail = "execution_budgetaire_tache_detail_histo";
      $columninserthistdet = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,USER_ID,ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION";
      $datatoinsert_histo_detail = "'" . $EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID . "','" . $USER_ID . "','" . $ETAPE_DOUBLE_COMMANDE_ID . "','" . $DATE_TRANSMISSION . "'";
      $this->save_all_table($historique_table_detail, $columninserthistdet, $datatoinsert_histo_detail);

      //-etape suivante---
      $etape_suivant ='SELECT ETAPE_DOUBLE_COMMANDE_SUIVANT_ID FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ='.$ETAPE_DOUBLE_COMMANDE_ID.' AND IS_SALAIRE=0';   
      $etape_suivants = "CALL `getTable`('" . $etape_suivant . "');";
      $id_etape_suivant=$data['get_titre_decaissement']= $this->ModelPs->getRequeteOne($etape_suivants);
      $id_etape_suivante=$id_etape_suivant['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

      //--update dans la table 'execution_budgetaire_titre_decaissement' 
      $updateTable='execution_budgetaire_titre_decaissement';
      $critere = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID =".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID;
      $datatoupdate ='ETAPE_DOUBLE_COMMANDE_ID='.$id_etape_suivante;
      $bindparams =[$updateTable,$datatoupdate,$critere];
      $insertRequete = 'CALL `updateData`(?,?,?);';
      $this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
      
    }
    $data=['message' => lang('messages_lang.message_success')];
    session()->setFlashdata('alert', $data);
    return redirect('double_commande_new/List_Bordereau_Deja_Transmsis');

  }else{

      return $this->formulaire();
  }

}
}
