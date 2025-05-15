<?php

namespace App\Controllers;

use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Recover_pwd extends BaseController
{
  protected $session;
  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
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

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $columnselect=str_replace("\'", "'", $columnselect);
    $table=str_replace("\'", "'", $table);
    $where=str_replace("\'", "'", $where);
    $orderby=str_replace("\'", "'", $orderby);
    $bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    $bindparams=str_replace('\"', '"', $bindparams);
    return $bindparams;
  }

  //fonction pour inserer dans les colonnes souhaites
  public function save_all_table($table,$columsinsert,$datacolumsinsert)
  {
    $ModelPs = new ModelPs();
    // $columsinsert: Nom des colonnes separe par,
      // $datacolumsinsert : les donnees a inserer dans les colonnes
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams =[$table,$columsinsert,$datacolumsinsert];
    $result=$ModelPs->getRequeteOne($insertReq,$tableparams);
    return $id=$result['id'];
  }

  /* update table */
  function update_all_table($table,$datatomodifie,$conditions)
  {
    $ModelPs = new ModelPs();
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  public function index()
  {
    // $annees_pip=$this->get_annee_pip();
    // print_r($annees_pip);die();
    return view('Recover_pwd_View');
  }

  public function set_pwd()
  {
    $ModelPs = new ModelPs();
    $USERNAME = $this->request->getPost('inputUsername');
    $bindparams = $this->getBindParms('*', 'user_users', ' USER_NAME like "'.$USERNAME.'"', ' USER_NAME DESC');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";  
    $connexion = $ModelPs->getRequeteOne($callpsreq, $bindparams);
    $statutconnexion='';
    if(!empty($connexion['USER_NAME']))
    {
      if($connexion['IS_ACTIVE']==1)
      {
        $PASSWORD=$this->library->generate_password(5);
        $updateIntoTable='user_users';
        $columsupdate='PASSWORD="' . md5($PASSWORD) . '"';
        $conditions='USER_ID='.$connexion['USER_ID'];
        $this->update_all_table($updateIntoTable,$columsupdate,$conditions);
        $notification = new Notification();
        $subject = utf8_decode('Récupération du mot de passe oublie');
        $msg= "<b>".$connexion['NOM'].'</b>  <b>'.$connexion['PRENOM']." </b>
        ,Vos identifiants de connexion sur la plateforme sont:<br><br>
        - Nom d'utilisateur : <b>$USERNAME</b><br>
        - Mot de passe : <b>$PASSWORD</b><br><br>
        Vous pouvez vous connecter en <a href=".base_url().">cliquant ici</a><br>
        A bientôt sur la plateforme!";
        $notification->sendEmail($USERNAME,$subject, $msg, array(), array());
        $data=['message' => "Le changement du mot de passe oublie est faite avec succès"];
        session()->setFlashdata('alert', $data);
        $usersExiste=1;
      }
      else
      {
        $usersExiste=2;
      }
    }
    else
    {
      $usersExiste=0;
    }
    echo json_encode(array('usersExiste'=>$usersExiste,'statutconnexion'=>$statutconnexion));  
  }
}
?>