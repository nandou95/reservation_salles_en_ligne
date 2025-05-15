<?php 
/*
  @author HABIMANA Nandou
 * 71483905
 * nandou@mediabox.bi
 * 01/07/2023
 * Liste des ptba des institutions
*/
/*
*Modifié par Christa
*le 05/09/2023
* christa@mediabox.bi
*/
  namespace App\Modules\ihm\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;
  class Institution extends BaseController
  {
  	protected $session;
    protected $ModelPs;
    public function __construct()
    {
      $this->library = new CodePlayHelper();
      $this->ModelPs = new ModelPs();
      $this->session = \Config\Services::session();
    }

  	function index()
  	{
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_INSTITUTION')!=1)
      {
       return redirect('Login_Ptba/homepage');
      }
      $data = $this->urichk();
      $data['titre']= lang('messages_lang.liste_des_institutions');
      return view('App\Modules\ihm\Views\Institution_List_View',$data);
  	}

    //liste des institutions
  	function get_info()
  	{
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_MASQUE_SAISI_INSTITUTION')!=1)
      {
       return redirect('Login_Ptba/homepage');
      }
      $query_principal="SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION,TYPE_INSTITUTION_ID FROM inst_institutions WHERE 1";

  		$var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
  		$limit='LIMIT 0,10';
  		if($_POST['length'] != -1)
  		{
  			$limit='LIMIT '.$_POST["start"].','.$_POST["length"];
  		}
  		$order_by='';
  		$order_column='';
  		$order_column= array('','CODE_INSTITUTION','DESCRIPTION_INSTITUTION','');
  		$order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION ASC';
  		$search = !empty($_POST['search']['value']) ?  (" AND (CODE_INSTITUTION LIKE '%$var_search%' OR DESCRIPTION_INSTITUTION LIKE '%$var_search%')"):'';
  		$query_secondaire=$query_principal.' '.$search.' '.$order_by.'   '.$limit;
  		$query_filter = $query_principal.' '.$search;
  		$requete='CALL `getList`("'.$query_secondaire.'")';
  		$fetch_cov_frais = $this->ModelPs->datatable($requete);
  		$data = array();
  		$u=1;
  		foreach($fetch_cov_frais as $info)
  		{
  			$post=array();
        $post[]=$u;
  			$post[]=$info->CODE_INSTITUTION;
  			$post[]=$info->DESCRIPTION_INSTITUTION;
  			$post[]='<a href="'.base_url().'/ihm/Detail_Institution/'.MD5($info->INSTITUTION_ID).'" class="btn btn-primary">'.lang('messages_lang.detail').'</a>';
  			$data[]=$post;
        $u=$u+1;
  		}
  		$requeteqp='CALL `getList`("'.$query_principal.'")';
  		$recordsTotal = $this->ModelPs->datatable( $requeteqp);
  		$requeteqf='CALL `getList`("'.$query_filter.'")';
  		$recordsFiltered = $this->ModelPs->datatable( $requeteqf);
  		$output = array(
  			"draw" => intval($_POST['draw']),
  			"recordsTotal" =>count($recordsTotal),
  			"recordsFiltered" => count($recordsFiltered),
  			"data" => $data
  		);
  		echo json_encode($output);
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