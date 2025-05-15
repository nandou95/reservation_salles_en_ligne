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
      $user_id = '';
      if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
          $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      } else {
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
            $execution_id = $this->getBindParms('EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID', 'execution_budgetaire_tache_detail', 'ETAPE_DOUBLE_COMMANDE_ID="'.$etape_actuel.'"', 'EXECUTION_BUDGETAIRE_ID DESC');
            $execution_id = str_replace('\\', '', $execution_id);
            $id_execution= $this->ModelPs->getRequeteOne($callpsreq, $execution_id);
            $EXECUTION_BUDGETAIRE_ID=$id_execution['EXECUTION_BUDGETAIRE_ID'];
            $id_crypt=$id_execution['EXECUTION_BUDGETAIRE_ID'];

            // titre etape
            $callpsreq = "CALL `getRequete`(?,?,?,?);";
            $titre = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DESC_ETAPE_DOUBLE_COMMANDE,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID ='.$etape_actuel,' DESC_ETAPE_DOUBLE_COMMANDE DESC');
            $data['titre_etape'] = $this->ModelPs->getRequeteOne($callpsreq, $titre);

            //statut_document_bordereau_transmission--
            $callpsreq = "CALL `getRequete`(?,?,?,?);";
            $type_operation = $this->getBindParms('STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID ,DESC_STATUT_OPERATION_BORDEREAU_TRANSMISSION', 'statut_operation_bordereau_transmission', 'STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=1',' STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID DESC');
            $data['type_document_transmissions'] = $this->ModelPs->getRequeteOne($callpsreq, $type_operation);

            //type document---
            $callpsreq = "CALL `getRequete`(?,?,?,?);";
            $type_document = $this->getBindParms('TYPE_DOCUMENT_ID', 'type_document', 'TYPE_DOCUMENT_ID ='. 2,' TYPE_DOCUMENT_ID  DESC');
            $data['type_documents'] = $this->ModelPs->getRequeteOne($callpsreq, $type_document);

            //--chargement du 'multiselect titre decaissement'-- 
            $titre_decaissement  ='SELECT execution.NUMERO_TITRE_DECAISSEMNT,execution.EXECUTION_BUDGETAIRE_DETAIL_ID  FROM execution_budgetaire_tache_detail execution WHERE execution.ETAPE_DOUBLE_COMMANDE_ID ='.$etape_actuel; 
            $titre_decaissements = str_replace("\'", "'", $titre_decaissement);
            $data['get_titre_decaissement']= $this->ModelPs->getRequete($titre_decaissements);
            // $execution_budgetaire_id = $data['get_titre_decaissement'];

            //--chargement de l'input origine destination'--
            $origine_destination ='SELECT origine.ID_ORIGINE_DESTINATION ,origine.ORIGINE, origine.DESTINATION FROM origine_destination  origine WHERE origine.IS_ACTIVE =1 AND origine.ID_ORIGINE_DESTINATION =2';   
            $origine_destinations = "CALL `getTable`('" . $origine_destination . "');";
            $data['get_origine_destination']= $this->ModelPs->getRequeteOne($origine_destinations);
            
            //Le min de la date de réception
            $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_DETAIL_ID,DATE_TRANSMISSION','  execution_budgetaire_tache_detail_histo','EXECUTION_BUDGETAIRE_DETAIL_ID='.$id_execution['EXECUTION_BUDGETAIRE_DETAIL_ID'],'DATE_INSERTION DESC');
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
        $user_id = '';
        if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
            $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
        } else {
            return redirect('Login_Ptba/do_logout');
        }

        if($this->session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_DIR_COMPTABLE') !=1)
        {
          return redirect('Login_Ptba/homepage'); 
        }
        
        //---insertion dans 'execution_bord_transmission new'-
        $db = db_connect();
        $selected_value = $this->request->getPost('EXECUTION_BUDGETAIRE_DETAIL_ID[]');
        $NUM_BORDEREAU_TRANSMISSION = $this->request->getPost('NUM_BORDEREAU_TRANSMISSION');
        $PATH_BORDEREAU_TRANSMISSION = $this->uploadFile('PATH_BORDEREAU_TRANSMISSION','double_commande_new','PATH_BORDEREAU_TRANSMISSION');
        $ID_ORIGINE_DESTINATION = $this->request->getPost('ORIGINE_DESTINATION');
        $ETAPE_DOUBLE_COMMANDE_ID = $this->request->getPost('ETAPE_DOUBLE_COMMANDE_ID');
        $DATE_RECEPTION = $this->request->getPost('DATE_RECEPTION');
        $DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');
        $STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID = $this->request->getPost('STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID');
        $TYPE_DOCUMENT_ID = $this->request->getPost('TYPE_DOCUMENT_ID');        
        $USER_ID = $user_id;
        // $type_document=2;
        /* *isertion dans execution budgetaire bordereau transmission */
        // $insertInto='execution_budgetaire_bordereau_transmission_new';
        $colum="NUMERO_BORDEREAU_TRANSMISSION,PATH_BORDEREAU_TRANSMISSION,ID_ORIGINE_DESTINATION,USER_ID,DATE_TRANSMISSION_BD, STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID ";
        $datacolums = "'{$NUM_BORDEREAU_TRANSMISSION}', '{$PATH_BORDEREAU_TRANSMISSION}', '{$ID_ORIGINE_DESTINATION}', '{$USER_ID}','{$DATE_TRANSMISSION}','{$STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID}'";
        $insertData = $this->insert_in_execution_budgetaire_bordereau_transmission($colum,$datacolums);        

        //--execution budgetaire bordereau transmission bon -
        foreach ($selected_value as $EXECUTION_BUDGETAIRE_DETAIL_ID) {
            //numero document--
            $callpsreq = "CALL `getRequete`(?,?,?,?);";
            $num = $this->getBindParms('NUMERO_TITRE_DECAISSEMNT', 'execution_budgetaire_tache_detail', 'EXECUTION_BUDGETAIRE_DETAIL_ID='. $EXECUTION_BUDGETAIRE_DETAIL_ID,'EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
            $numero_titre= $this->ModelPs->getRequeteOne($callpsreq, $num);
            $numero_titre_decaissement=$numero_titre['NUMERO_TITRE_DECAISSEMNT'];

            // $insertInto='execution_budgetaire_bordereau_transmission_bon_titre';
            $colum="BORDEREAU_TRANSMISSION_ID,TYPE_DOCUMENT_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,NUMERO_DOCUMENT,USER_ID, STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID ";
            $datacolums = "'{$insertData}', '{$TYPE_DOCUMENT_ID}', '{$EXECUTION_BUDGETAIRE_DETAIL_ID}', '{$numero_titre_decaissement}', '{$USER_ID}','{$STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID}'";
            $this->insert_in_execution_budgetaire_bordereau_transmission_bon_titre($colum,$datacolums);
        }

        //--insertion dans 'historique_raccrochage'
        foreach ($selected_value as $value) {

            //insertion dans historique_raccrochage_detail-
            $historique_table_detail = "execution_budgetaire_tache_detail_histo";
            $columninserthistdet = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_TRANSMISSION";
            $datatoinsert_histo_detail = "'" . $value . "','" . $USER_ID . "','" . $ETAPE_DOUBLE_COMMANDE_ID . "','" . $DATE_TRANSMISSION . "'";
            $this->save_all_table($historique_table_detail, $columninserthistdet, $datatoinsert_histo_detail);
           
            //id_raccrochage_activite
            $callpsreq = "CALL `getRequete`(?,?,?,?);";
            $id_exec_budg = $this->getBindParms('EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_tache_detail', 'EXECUTION_BUDGETAIRE_DETAIL_ID="' . $value . '"', ' EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
            $id_exec_budg = str_replace('\\', '', $id_exec_budg);
            $data['id_exec_budgetaire'] = $this->ModelPs->getRequeteOne($callpsreq, $id_exec_budg);
            $id_exec_budgetaire=$data['id_exec_budgetaire']['EXECUTION_BUDGETAIRE_ID'];

            //-etape suivante---

            $etape_suivant ='SELECT etapeConfig.ETAPE_DOUBLE_COMMANDE_SUIVANT_ID FROM execution_budgetaire_etape_double_commande mouv JOIN execution_budgetaire_etape_double_commande_config etapeConfig ON etapeConfig.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = mouv.ETAPE_DOUBLE_COMMANDE_ID WHERE etapeConfig.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ='.$ETAPE_DOUBLE_COMMANDE_ID;   
            $etape_suivants = "CALL `getTable`('" . $etape_suivant . "');";
            $id_etape_suivant=$data['get_titre_decaissement']= $this->ModelPs->getRequeteOne($etape_suivants);
            $id_etape_suivante=$id_etape_suivant['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            //--update dans la table 'execution_budgetaire' 
        
            $updateTable='execution_budgetaire_tache_detail';
		    $critere = "EXECUTION_BUDGETAIRE_DETAIL_ID =".$value;
            $datatoupdate ='ETAPE_DOUBLE_COMMANDE_ID='.$id_etape_suivante;
		    $bindparams =[$updateTable,$datatoupdate,$critere];
		    $insertRequete = 'CALL `updateData`(?,?,?);';
		    $this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
        }
        $data=['message' => lang('messages_lang.message_success')];
		  session()->setFlashdata('alert', $data);
		  return redirect('double_commande_new/List_Bordereau_Deja_Transmsis');
    }

    public function checkbord()
    {
      $NUM_BORDEREAU_TRANSMISSION = $this->request->getPost('NUM_BORDEREAU_TRANSMISSION');
      $ID_ORIGINE_DESTINATION = $this->request->getPost('ID_ORIGINE_DESTINATION');
      $get_bord = "SELECT NUMERO_BORDEREAU_TRANSMISSION,ID_ORIGINE_DESTINATION FROM execution_budgetaire_bordereau_transmission WHERE NUMERO_BORDEREAU_TRANSMISSION='".$NUM_BORDEREAU_TRANSMISSION."' AND ID_ORIGINE_DESTINATION=".$ID_ORIGINE_DESTINATION;
      $get_bord='CALL `getTable`("'.$get_bord.'")';
      $get_bord= $this->ModelPs->getRequeteOne($get_bord);
      $status=0;
      if(!empty($get_bord))
      {
        $status=0;
      }
      else
      {
        $status=1;
      }
      return json_encode(array('status'=>$status));
    }
}
