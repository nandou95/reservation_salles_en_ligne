<?php
/**RUGAMBA Jean Vainqueur
*Titre:Changement de mot de passe
*Numero de telephone: (+257) 66 33 43 25
*WhatsApp: (+257) 62 47 19 15
*Email: jean.vainqueur@mediabox.bi
*Date: 5 Oct,2023
**/

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Change_Password extends BaseController
{
  protected $session;
  protected $ModelPs;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  //Interface de changement de mot de passe
  public function index($params=NULL)
  {
    $session  = \Config\Services::session();
    $data=$this->urichk();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    elseif(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    $get_user = "SELECT USER_ID,PASSWORD FROM user_users WHERE USER_ID= ".$user_id;

    $get_user = "CALL `getTable`('".$get_user."');";
    $user = $this->ModelPs->getRequeteOne($get_user);
    $data['old_pass'] = $user['PASSWORD'];
    $data['message'] = $params;
    return view('Change_Password_View',$data);
  }

  //Fonction pour modifier le mot de passe d'un utilisateur
  public function new_password($value='')
  {
    $session  = \Config\Services::session();
    $db = db_connect();
    $params=NULL;
    $rule =  [
      'OLD_PASSWORD' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'NEW_PASSWORD' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'NEW_PASSWORD_CONF' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    $this->validation->setRules($rule);
    
    if($this->validation->withRequest($this->request)->run())
    {
      $user_id ='';
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      }
      elseif(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        return redirect('Login_Ptba');
      }

      $psgetrequete = "CALL `getRequete`(?,?,?,?);";

      $get_user = "SELECT USER_ID,PASSWORD FROM user_users WHERE USER_ID= ".$user_id;

      $get_user = "CALL `getTable`('".$get_user."');";
      $user = $this->ModelPs->getRequeteOne($get_user);

      $OLD_PASSWORD = $this->request->getPost('OLD_PASSWORD');

      if($user['PASSWORD']==md5($OLD_PASSWORD))
      {
        $NEW_PASSWORD = md5($this->request->getPost('NEW_PASSWORD'));
        $NEW_PASSWORD_CONF = md5($this->request->getPost('NEW_PASSWORD_CONF'));

        if($NEW_PASSWORD==$NEW_PASSWORD_CONF)
        {
          $table = 'user_users';
          $conditions = 'USER_ID='.$user_id;
          $datatomodifie = 'PASSWORD="'.$NEW_PASSWORD.'"';
          $this->update_all_table($table, $datatomodifie, $conditions);
          
          $data = [
          'message' => "Changement de mot de passe effectué avec succès."
          ];
          session()->setFlashdata('alert', $data);
          return redirect('Login_Ptba');

        }else{

          return $this->index("Le nouveau et lemot de passe confirmé ne sont pas identiques!");
        }
      
      }else{

        return $this->index("Le nom d'utilisateur ou/et ancien mot de passe incorect(s)!");
      }


    }
    else
    {
       return $this->index($params);
    }
  } 

  /**
    * @author jules@mediabox.bi
    * fonction pour retourner le tableau des parametre pour le PS pour les selection
    * @param string  $columnselect //colone A selectionner
    * @param string  $table        //table utilisE
    * @param string  $where        //condition dans la clause where
    * @param string  $orderby      //order by
    * @return  mixed
  */
  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    // code...
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$where,$db->escapeString($orderby)];
    return $bindparams;
  }

  /* Debut Gestion update*/
  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
  /* Fin Gestion update*/
}
?>