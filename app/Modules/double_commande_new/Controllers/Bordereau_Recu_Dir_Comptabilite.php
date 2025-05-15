<?php
/*
*Alain Charbel Nderagakura
*Titre: Liste des bordereau recu par le directeur comptable
*Numero de telephone: (+257) 62003522
*WhatsApp: (+257) 76887837
*Email: charbel@mediabox.bi
*Date: 15 fevrier,2024
*/
namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Bordereau_Recu_Dir_Comptabilite extends BaseController
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

  //Interface de la liste des activites
  function index($id=0)
  {
    $data = $this->urichk();
    $session  = \Config\Services::session(); 
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    //selectionner les valeurs a mettre dans le menu en haut
    $annee = $this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','1','ANNEE_BUDGETAIRE_ID ASC');
    $data['annee'] = $this->ModelPs->getRequete($callpsreq, $annee);
    $data['annee_encours']=$this->get_annee_budgetaire();

    $ANNEE_BUDGETAIRE_ID=$data['annee_encours'];
    $INSTITUTION_ID=0;
    $SOUS_TUTEL_ID=0;
    $DATE_DEBUT=0;
    $DATE_FIN=0;

    $paiement = $this->count_paiement($ANNEE_BUDGETAIRE_ID,$INSTITUTION_ID,$SOUS_TUTEL_ID,$DATE_DEBUT,$DATE_FIN);
    $data['recep_prise_charge']=$paiement['recep_prise_charge'];
    $data['deja_recep_prise_charge']=$paiement['deja_recep_prise_charge'];
    $data['recep_dir_comptable']=$paiement['recep_dir_comptable'];
    $data['deja_recep_dir_comptable']=$paiement['deja_recep_dir_comptable'];
    
    $data['get_recep_obr'] = $paiement['get_recep_obr'];
    $data['get_prise_charge'] = $paiement['get_prise_charge'];
    $data['get_etab_titre'] = $paiement['get_etab_titre'];
    $data['get_sign_dir_compt'] = $paiement['get_sign_dir_compt'];
    $data['get_sign_dir_dgfp'] = $paiement['get_sign_dir_dgfp'];
    $data['get_sign_ministre'] = $paiement['get_sign_ministre'];
    $data['get_recep_td_corriger'] = $paiement['get_recep_td_corriger'];

    $data['get_prise_charge_corr'] = $paiement['get_prise_charge_corr'];
    $data['get_etab_titre_corr'] = $paiement['get_etab_titre_corr'];
    $data['get_etape_corr'] = $paiement['get_etape_corr'];
    $data['get_etape_reject_pc'] = $paiement['get_etape_reject_pc'];
    $data['get_bord_brb']=$paiement['get_bord_brb'];
    $data['get_bord_deja_trans_brb']=$paiement['get_bord_deja_trans_brb'];
    $data['get_bord_dc']=$paiement['get_bord_dc'];
    $data['get_bord_deja_dc']=$paiement['get_bord_deja_dc'];
    $data['get_titre_valide'] = $paiement['get_titre_valide'];
    $data['get_titre_termine'] = $paiement['get_titre_termine'];
    $data['get_nbr_av_obr']=$paiement['get_nbr_av_obr'];
    $data['get_nbr_av_pc']=$paiement['get_nbr_av_pc'];
  
    return view('App\Modules\double_commande_new\Views\Bordereau_Recu_Dir_Comptabilite_View',$data);
  }

  //fonction pour affichage d'une liste des bord. déjà réceptionnés
  public function listing()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $db = db_connect();
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critere = "";
    $DATE_DEBUT=$this->request->getPost('DATE_DEBUT');
    $DATE_FIN=$this->request->getPost('DATE_FIN');
    $ANNEE_BUDGETAIRE_ID=$this->request->getPost('ANNEE_BUDGETAIRE_ID');

    if(!empty($ANNEE_BUDGETAIRE_ID))
    {
      $critere.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
    }

    if(!empty($DATE_DEBUT) AND empty($DATE_FIN))
    {
      $critere.=' AND exec.DATE_DEMANDE >= "'.$DATE_DEBUT.'"';
    }

    if (!empty($DATE_FIN) && !empty($DATE_DEBUT))
    {
      $critere.=' AND exec.DATE_DEMANDE >= "'.$DATE_DEBUT.'" AND exec.DATE_DEMANDE <= "'.$DATE_FIN.'"';
    }
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $order_column = array('bord_trans.NUMERO_BORDEREAU_TRANSMISSION', 1, 1,1);
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY bord_trans.BORDEREAU_TRANSMISSION_ID   ASC';

    $search = !empty($_POST['search']['value']) ? (' AND (bord_trans.NUMERO_BORDEREAU_TRANSMISSION LIKE "%' . $var_search . '%")') : '';

    $conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    $conditionsfilter = $critere . ' ' . $search . ' ' . $group;

    $requetedebase= 'SELECT DISTINCT bord_trans.BORDEREAU_TRANSMISSION_ID,bord_trans.NUMERO_BORDEREAU_TRANSMISSION,bord_trans.PATH_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission bord_trans JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID WHERE ID_ORIGINE_DESTINATION=2 AND STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=2 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)';

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';

    $requetedebases = $requetedebase . ' ' . $conditions;
    $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
    $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
    //print_r($query_secondaire);die();§*

    $fetch_data = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u=1;

    foreach ($fetch_data as $row)
    {
      $titre = 'SELECT COUNT(BORDEREAU_TRANSMISSION_BON_TITRE_ID) as nbre FROM execution_budgetaire_bordereau_transmission_bon_titre WHERE 1 AND BORDEREAU_TRANSMISSION_ID = '.$row->BORDEREAU_TRANSMISSION_ID.' AND TYPE_DOCUMENT_ID = 2 AND STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2';
      $titre = "CALL `getTable`('" . $titre . "');";
      $titre_decaisser = $this->ModelPs->getRequeteOne($titre);

      $Nbre_titre='<center><a onclick="get_detail_titre('.$row->BORDEREAU_TRANSMISSION_ID.')" href="javascript:;" ><button class="btn btn-primary"><b style="color:white;">'.$titre_decaisser['nbre'].'</b></button></a></center>';

      $money_dec='SELECT SUM(det.MONTANT_ORDONNANCEMENT) as decaisse FROM execution_budgetaire_bordereau_transmission_bon_titre titre JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE BORDEREAU_TRANSMISSION_ID = '.$row->BORDEREAU_TRANSMISSION_ID.' AND TYPE_DOCUMENT_ID=2 AND STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2';
      $money_dec = "CALL `getTable`('" . $money_dec . "');";
      $money_decaisser = $this->ModelPs->getRequeteOne($money_dec);
      $money_decaisser_value=!empty($money_decaisser)?$money_decaisser['decaisse']:0;

      $documa = (!empty($row->PATH_BORDEREAU_TRANSMISSION)) ? '<a onclick="get_doc('.$row->BORDEREAU_TRANSMISSION_ID.')" href="javascript:;"><span style="font-size: 30px;color:red;" class="fa fa-file-pdf"></span></a>' : 'N/A';

      $bouton= "<a class='btn btn-primary btn-sm' style='color:#fbbf25;' title='Traiter' href='".base_url("double_commande_new/detail"."/".md5($row->BORDEREAU_TRANSMISSION_ID))."' ><span class='fa fa-arrow-up'></span></a>";

      $sub_array = array();
      $sub_array[] = $row->NUMERO_BORDEREAU_TRANSMISSION;
      $sub_array[] = $Nbre_titre;
      $sub_array[] = $money_decaisser_value;
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

  function liste_titre_decaissement($id=0)
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_DIR_COMPTABLE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    
    $id=$this->request->getPost('id');
    $query_principal="SELECT titre.BORDEREAU_TRANSMISSION_BON_TITRE_ID, titre.NUMERO_DOCUMENT, dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,stat.DESC_STATUT_DOCUMENT_BORDEREAU_TRANSMISSION FROM execution_budgetaire_bordereau_transmission_bon_titre titre JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = det.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN statut_document_bordereau_transmission stat ON stat.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID WHERE titre.BORDEREAU_TRANSMISSION_ID = ".$id." AND TYPE_DOCUMENT_ID=2 AND stat.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2";

    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }

    $order_by='';
    $order_column='';
    $order_column= array('titre.NUMERO_DOCUMENT','dev.DESC_DEVISE_TYPE','det.MONTANT_ORDONNANCEMENT','stat.DESC_STATUT_DOCUMENT_BORDEREAU_TRANSMISSION');

    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY titre.BORDEREAU_TRANSMISSION_BON_TITRE_ID   DESC';

    $search = !empty($_POST['search']['value']) ? (" AND (stat.DESC_STATUT_DOCUMENT_BORDEREAU_TRANSMISSION LIKE '%". $var_search."%' OR det.MONTANT_ORDONNANCEMENT LIKE '%" . $var_search . "%' OR titre.NUMERO_DOCUMENT LIKE '%" . $var_search . "%' OR dev.DESC_DEVISE_TYPE LIKE '%" . $var_search . "%')") : '';

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
      $sub_array[] = $row->DESC_DEVISE_TYPE;
      if ($row->DEVISE_TYPE_ID==1) {
        $sub_array[] = $row->MONTANT_ORDONNANCEMENT;
      }else{
        $sub_array[] = $row->MONTANT_ORDONNANCEMENT_DEVISE;
      }
      $sub_array[] = $row->DESC_STATUT_DOCUMENT_BORDEREAU_TRANSMISSION;

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
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }
}
?>