<?php 
/*
* @author HABIMANA Nandou
* 71483905
* nandou@mediabox.bi
* 01/07/2023
* Liste des ptba des institutions
*/

/*
* Modifié par Christa
* le 05/09/2023
* christa@mediabox.bi
*/
namespace App\Modules\ptba\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Ptba_Institution extends BaseController
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
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PTBA_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data = $this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $bindparams = $this->getBindParms('EXERCICE_ID,concat(`ANNEE_DEBUT`," - ",`ANNEE_FIN`) ANNEE', 'op_exercice', '1', '`EXERCICE_ID`');
    $bindparams = str_replace('\"', '"', $bindparams);
    $data['annees'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);

    //Declaration des labels pour l'internalisation
    $titre_institution = lang("messages_lang.titre_institution");
    $data['titre']="$titre_institution";
    return view('App\Modules\ptba\Views\Ptba_Institution_List_View',$data);
  }

  //liste des institutions
  function get_info()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PTBA_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ANNEE_ID = $this->request->getPost('ANNEE_ID');
    $critere='';
    if(!empty($ANNEE_ID))
    {
      $critere=' AND ptba_institutions.EXERCICE_ID='.$ANNEE_ID;
    }

    $query_principal="SELECT inst_institutions.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION,ptba_institutions.TRANCHE_UN,ptba_institutions.TRANCHE_DEUX,ptba_institutions.TRANCHE_TROIX,ptba_institutions.TRANCHE_QUATRE,ptba_institutions.TOTAL_ANNUEL FROM `ptba_institutions` LEFT JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=ptba_institutions.INSTITUTION_ID WHERE 1 ".$critere;

    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('inst_institutions.CODE_INSTITUTION','inst_institutions.DESCRIPTION_INSTITUTION','ptba_institutions.TRANCHE_UN','ptba_institutions.TRANCHE_DEUX','ptba_institutions.TRANCHE_TROIX','ptba_institutions.TRANCHE_QUATRE','ptba_institutions.TOTAL_ANNUEL');
    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY inst_institutions.DESCRIPTION_INSTITUTION ASC';

    $search = !empty($_POST['search']['value']) ? (' AND (inst_institutions.CODE_INSTITUTION LIKE "%' . $var_search . '%" OR inst_institutions.DESCRIPTION_INSTITUTION LIKE "%' . $var_search . '%")') : '';

    $search = str_replace('\"','"',$search);
    $search = str_replace("'","\'",$search);
    $critaire = '';
    $query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.'   '.$limit;

    $query_filter = $query_principal.' '.$search.' '.$critaire;
    $requete="CALL `getList`('".$query_secondaire."')";
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=1;
    foreach($fetch_cov_frais as $info)
    {
      $post=array();
      $post[]=$info->CODE_INSTITUTION;
      $post[]=$info->DESCRIPTION_INSTITUTION;
      $post[]=number_format($info->TRANCHE_UN,2,","," ");
      $post[]=number_format($info->TRANCHE_DEUX,2,","," ");
      $post[]=number_format($info->TRANCHE_TROIX,2,","," ");
      $post[]=number_format($info->TRANCHE_QUATRE,2,","," ");
      $post[]=number_format($info->TOTAL_ANNUEL,2,","," ");
      $post[]='<a href="'.base_url().'/ptba/Detail_Institution/'.md5($info->INSTITUTION_ID).'" class="btn btn-primary">DETAIL</a>';
      $data[]=$post;  
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

  //appel view nouvelle institution
  function nouvelle()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PTBA_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data = $this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $bindparams = $this->getBindParms('`INSTITUTION_ID`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', '1', '`DESCRIPTION_INSTITUTION` ASC');
    $data['institutions'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
    $anne_params= $this->getBindParms('EXERCICE_ID,concat(`ANNEE_DEBUT`," - ",`ANNEE_FIN`) ANNEE', 'op_exercice', '1', '`EXERCICE_ID`');
    $anne_params = str_replace('\"', '"', $anne_params);
    $data['annees'] = $this->ModelPs->getRequete($psgetrequete, $anne_params);
    $data['titre']=  lang("messages_lang.titr_nouv_inst");
    return view('App\Modules\ptba\Views\Ptba_Institution_Add_View',$data);
  }

  //insertion d'une nouvelle institution dans la BD 
  function create()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PTBA_INSTITUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $EXERCICE_ID = $this->request->getPost('EXERCICE_ID');
    $TRANCHE_UN = $this->request->getPost('TRANCHE_UN');
    $TRANCHE_DEUX = $this->request->getPost('TRANCHE_DEUX');
    $TRANCHE_TROIX = $this->request->getPost('TRANCHE_TROIX');
    $TRANCHE_QUATRE = $this->request->getPost('TRANCHE_QUATRE');
    $TOTAL_ANNUEL=$TRANCHE_UN+$TRANCHE_DEUX+$TRANCHE_TROIX+$TRANCHE_QUATRE;
    $USER_ID=1;
    $columsinsert="INSTITUTION_ID,TRANCHE_UN,TRANCHE_DEUX,TRANCHE_TROIX,TRANCHE_QUATRE,EXERCICE_ID,TOTAL_ANNUEL,USER_ID";
    $datacolumsinsert="".$INSTITUTION_ID.",".$TRANCHE_UN.",".$TRANCHE_DEUX.",".$TRANCHE_TROIX.",'".$TRANCHE_QUATRE."','".$EXERCICE_ID."','".$TOTAL_ANNUEL."','".$USER_ID."' ";
    $table='ptba_institutions';
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $this->ModelPs->createUpdateDelete($insertReqAgence,$bindparms);
    $statut=true;
    echo json_encode(array('statut'=>$statut));
  }

  /*
  * fonction pour retourner le tableau des parametre pour le PS pour les selection
  * @param string  $columnselect //colone A selectionner
  * @param string  $table        //table utilisE
  * @param string  $where        //condition dans la clause where
  * @param string  $orderby      //order by
  * @return  mixed
  */
  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db=db_connect();
    $bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }
}
?>