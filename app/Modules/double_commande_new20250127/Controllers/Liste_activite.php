<?php
  /**Alain Charbel Nderagakura
    *Titre: Liste Double commande
    *Numero de telephone: (+257) 62003522
    *WhatsApp: (+257) 76887837
    *Email: charbel@mediabox.bi
    *Date: 27 Octobre,2023
    **/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;

  class Liste_activite extends BaseController
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

    //Interface du detail en passant par les listes
    
    public function detail_view($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        return redirect('Login_Ptba');
      }
      $detail=$this->detail_new($EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID);
      $data['get_info']=$detail['get_info'];
      $data['montantvote']=$detail['montantvote'];
      $data['creditVote']=$detail['creditVote'];
      $data['montant_reserve']=$detail['montant_reserve'];
      return view('App\Modules\double_commande_new\Views\Liste_Activite_Detail_View',$data);
    }
    
    /**
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
      // print_r($db->lastQuery);die();
      $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
      return $bindparams;
    }
    
  }

?>