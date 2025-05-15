<?php
/*
*MUNEZERO SONIA
*Titre: Reception vers BRB
*Numero de telephone: (+257) 65165772
*Email: sonia@mediabox.bi
*Date: 15 FRVRIER,2024
*/
namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Reception_BRB extends BaseController
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

  // affiche le view pour la 1er etape d'engagement budgetaire (engage)
  function liste_vue()
  {
    $data = $this->urichk();
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $data_menu=$this->getDataMenuReception();
    $data['recep_prise_charge']=$data_menu['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$data_menu['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$data_menu['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$data_menu['deja_recep_dir_comptable'];
    $data['recep_brb']=$data_menu['recep_brb'];
    $data['déjà_recep_brb']=$data_menu['déjà_recep_brb'];

    $decais = $this->count_decaissement();
    $data['decais_a_faire'] = $decais['get_decais_afaire'];
    $data['decais_deja_fait'] = $decais['get_decais_deja_fait'];

    $data['titre']=''.lang('messages_lang.titre_rece_brb').'';
    return view('App\Modules\double_commande_new\Views\Reception_BRB_List_View',$data);
  }

  public function liste_reception_brb($value = 0)
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
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
    $order_column = array('bord.NUMERO_BORDEREAU_TRANSMISSION', 1, 1,1,1);
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID   ASC';

    $search=!empty($_POST['search']['value']) ? (' AND (bord.NUMERO_BORDEREAU_TRANSMISSION LIKE "%'.$var_search.'%")') : '';

    $conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    $conditionsfilter = $critere . ' ' . $search . ' ' . $group;
    
    $requetedebase= 'SELECT  DISTINCT bord.BORDEREAU_TRANSMISSION_ID,bord.NUMERO_BORDEREAU_TRANSMISSION,bord.PATH_BORDEREAU_TRANSMISSION,det.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_bordereau_transmission bord JOIN execution_budgetaire_bordereau_transmission_bon_titre bon ON bon.BORDEREAU_TRANSMISSION_ID=bord.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=bon.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE det.ETAPE_DOUBLE_COMMANDE_ID=28 AND bon.TYPE_DOCUMENT_ID=2 AND bon.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1';

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
      $get_lien='SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID;
      $get_lien = "CALL getTable('" . $get_lien . "');";
      $link= $this->ModelPs->getRequeteOne($get_lien);
      $links=($link) ? 'double_commande_new/'.$link['LINK_ETAPE_DOUBLE_COMMANDE']:0;


      $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $bouton="<a class='btn btn-primary btn-sm' title=''><span class='fa fa-arrow-up'></span></a>";

      $number=$row->NUMERO_BORDEREAU_TRANSMISSION;

      $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
      $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);        
      if (!empty($getProfil))
      {
        foreach ($getProfil as $value)
        {
          if ($prof_id == $value->PROFIL_ID || $prof_id==1)
          {
            $bouton= "<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Traiter' href='".base_url($links."/".md5($row->BORDEREAU_TRANSMISSION_ID))."' ><span class='fa fa-arrow-up'></span></a>";
            $number = "<a  title='Traiter' style='color:#fbbf25;' href='".base_url($links."/".md5($row->BORDEREAU_TRANSMISSION_ID)."")."' >".$row->NUMERO_BORDEREAU_TRANSMISSION."</a>";
          }  
        }
        
      }

      $titre = 'SELECT COUNT(BORDEREAU_TRANSMISSION_BON_TITRE_ID) as nbre FROM execution_budgetaire_bordereau_transmission_bon_titre WHERE 1 AND BORDEREAU_TRANSMISSION_ID = '.$row->BORDEREAU_TRANSMISSION_ID.' AND TYPE_DOCUMENT_ID = 2 AND STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1';
      $titre = "CALL `getTable`('" . $titre . "');";
      $titre_decaisser = $this->ModelPs->getRequeteOne($titre);

      $Nbre_titre='<center><a onclick="get_detail_titre('.$row->BORDEREAU_TRANSMISSION_ID.')" href="javascript:;" ><button class="btn btn-primary"><b style="color:white;">'.$titre_decaisser['nbre'].'</b></button></a></center>';

      $money_dec='SELECT SUM(det.MONTANT_ORDONNANCEMENT) as decaisse FROM execution_budgetaire_bordereau_transmission_bon_titre titre JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE BORDEREAU_TRANSMISSION_ID = '.$row->BORDEREAU_TRANSMISSION_ID.' AND TYPE_DOCUMENT_ID=2 AND STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1';
      $money_dec = "CALL `getTable`('" . $money_dec . "');";
      $money_decaisser = $this->ModelPs->getRequeteOne($money_dec);
      $money_decaisser_value=!empty($money_decaisser)?$money_decaisser['decaisse']:0;

      $documa = (!empty($row->PATH_BORDEREAU_TRANSMISSION)) ? '<a onclick="get_doc('.$row->BORDEREAU_TRANSMISSION_ID.')" href="javascript:;"><span style="font-size: 30px;color:red;" class="fa fa-file-pdf"></span></a>' : 'N/A';


      $sub_array = array();
      $sub_array[] = $number;
      $sub_array[] = $Nbre_titre;
      $sub_array[] = number_format($money_decaisser_value,2,',',' ');
      $sub_array[] = $documa;
      $sub_array[] = $bouton;
      
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

  function liste_titre_decaissement($id=0)
  {    
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_BRB') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $id=$this->request->getPost('id');

    $query_principal="SELECT titre.BORDEREAU_TRANSMISSION_BON_TITRE_ID, titre.NUMERO_DOCUMENT, det.MONTANT_ORDONNANCEMENT,stat.DESC_STATUT_DOCUMENT_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission_bon_titre titre JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN statut_document_bordereau_transmission stat ON stat.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID WHERE titre.BORDEREAU_TRANSMISSION_ID = ".$id." AND TYPE_DOCUMENT_ID = 2 AND titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1";

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

  function get_path_bord($id = 0)
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