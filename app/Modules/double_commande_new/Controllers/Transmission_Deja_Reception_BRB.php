<?php
/*
*MUNEZERO SONIA
*Titre: Transmission deja receptionner par BRB
*Numero de telephone: (+257) 65165772
*Email: sonia@mediabox.bi
*Date: 15 FRVRIER,2024
*/
namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Transmission_Deja_Reception_BRB extends BaseController
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
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  function liste_trans_rec_vue()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $decais = $this->count_decaissement();
    $data['decais_a_faire'] = $decais['get_decais_afaire'];
    $data['decais_deja_fait'] = $decais['get_decais_deja_fait'];
    $data['recep_brb']=$decais['recep_brb'];
    $data['déjà_recep_brb']=$decais['déjà_recep_brb'];
    $data['controle_brb']=$decais['controle_brb'];
    $data['controle_besd']=$decais['controle_besd'];
    $data['controle_a_corriger']=$decais['controle_a_corriger']; 

    $data['titre']=''.lang('messages_lang.titre_trans_rec_brb').'';

    return view('App\Modules\double_commande_new\Views\Transmission_Deja_Reception_BRB_List_View',$data);
  }

  public function liste_transm_deja_recept_brb($value = 0)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    
    $db = db_connect();
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critere = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column = array('bord_trans.NUMERO_BORDEREAU_TRANSMISSION', 1, 1,1,1);
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC';

    $search = !empty($_POST['search']['value']) ? (' AND (bord_trans.NUMERO_BORDEREAU_TRANSMISSION LIKE "%' . $var_search . '%")') : '';

    $conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    $conditionsfilter = $critere . ' ' . $search . ' ' . $group;
    
    $requetedebase= 'SELECT bord_trans.BORDEREAU_TRANSMISSION_ID,bord_trans.NUMERO_BORDEREAU_TRANSMISSION,bord_trans.PATH_BORDEREAU_TRANSMISSION,td.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_bordereau_transmission bord_trans ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE ID_ORIGINE_DESTINATION=3 AND STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=2 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)';

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';

    $requetedebases = $requetedebase . ' ' . $conditions;
    $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
    $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";

    $fetch_data = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;

    foreach ($fetch_data as $row)
    {
      $number=$row->NUMERO_BORDEREAU_TRANSMISSION;
      $titre = 'SELECT COUNT(BORDEREAU_TRANSMISSION_BON_TITRE_ID) as nbre FROM execution_budgetaire_bordereau_transmission_bon_titre WHERE 1 AND BORDEREAU_TRANSMISSION_ID = '.$row->BORDEREAU_TRANSMISSION_ID.' AND TYPE_DOCUMENT_ID = 2 AND STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2';
      $titre = "CALL `getTable`('" . $titre . "');";
      $titre_decaisser = $this->ModelPs->getRequeteOne($titre);
      $Nbre_titre='<center><a onclick="get_trans_titre('.$row->BORDEREAU_TRANSMISSION_ID.')" href="javascript:;" ><button class="btn btn-primary"><b style="color:white;">'.$titre_decaisser['nbre'].'</b></button></a></center>';

      $money_dec='SELECT SUM(det.MONTANT_ORDONNANCEMENT) as decaisse FROM execution_budgetaire_bordereau_transmission_bon_titre titre JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE BORDEREAU_TRANSMISSION_ID = '.$row->BORDEREAU_TRANSMISSION_ID.' AND TYPE_DOCUMENT_ID=2 AND titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2';
      $money_dec = "CALL `getTable`('" . $money_dec . "');";
      $money_decaisser = $this->ModelPs->getRequeteOne($money_dec);
      $money_decaisser_value=!empty($money_decaisser)?$money_decaisser['decaisse']:0;

      $documa = (!empty($row->PATH_BORDEREAU_TRANSMISSION)) ? '<a onclick="get_docum('.$row->BORDEREAU_TRANSMISSION_ID.')" href="javascript:;"><span style="font-size: 30px;color:red;" class="fa fa-file-pdf"></span></a>' : 'N/A';

      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $Nbre_titre;
      $sub_array[] = number_format($money_decaisser_value,2,',',' ');
      $sub_array[] = $documa;
      $data[] = $sub_array;
    }

    $recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $requetedebase . "')");
    $recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );
    return $this->response->setJSON($output);
  }

  function liste_reception_decaissement($id=0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $id=$this->request->getPost('id');

    $query_principal="SELECT titre.BORDEREAU_TRANSMISSION_BON_TITRE_ID, titre.NUMERO_DOCUMENT, det.MONTANT_ORDONNANCEMENT,stat.DESC_STATUT_DOCUMENT_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission_bon_titre titre JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN statut_document_bordereau_transmission stat ON stat.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID WHERE titre.BORDEREAU_TRANSMISSION_ID = ".$id." AND TYPE_DOCUMENT_ID = 2 AND titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2";

    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('titre.NUMERO_DOCUMENT','det.MONTANT_ORDONNANCEMENT');

    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY titre.BORDEREAU_TRANSMISSION_BON_TITRE_ID   DESC';

    $search = !empty($_POST['search']['value']) ? (" AND (stat.DESC_STATUT_DOCUMENT_BORDEREAU_TRANSMISSION LIKE '%". $var_search."%' OR det.MONTANT_ORDONNANCEMENT LIKE '%" . $var_search . "%' OR titre.NUMERO_DOCUMENT LIKE '%" . $var_search . "%')") : '';
    
    $critaire = '';
    $query_secondaire=$query_principal.' '.$search.' '.$critaire.' '.$order_by.'   '.$limit;

    $query_filter = $query_principal.' '.$search.' '.$critaire;
    $requete='CALL `getList`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=0;
    foreach($fetch_cov_frais as $row)
    {
      $session  = \Config\Services::session();
      
      $sub_array = array();
      $sub_array[] = $row->NUMERO_DOCUMENT;
      $sub_array[] = number_format($row->MONTANT_ORDONNANCEMENT,2,',',' ');

      $data[] = $sub_array;
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
    return $this->response->setJSON($output);
  }

  function get_path_bordereau($id = 0)
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = '';
    if (!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    } else {
      return redirect('Login_Ptba/do_logout');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $getpath = $this->getBindParms('BORDEREAU_TRANSMISSION_ID,PATH_BORDEREAU_TRANSMISSION', 'execution_budgetaire_bordereau_transmission', ' BORDEREAU_TRANSMISSION_ID = ' . $id . ' ', 'BORDEREAU_TRANSMISSION_ID  ASC');
    $path = $this->ModelPs->getRequeteOne($callpsreq, $getpath);

    $html = "<embed  src='" . base_url("" . $path['PATH_BORDEREAU_TRANSMISSION']) . "' scrolling='auto' height='500px' width='100%' frameborder='0'>";

    $output = array(

      "documentBord" => $html,
    );

    return $this->response->setJSON($output);
  }
}
?>