<?php

/**
* 
develope par SONIA MUNEZERO
sonia@mediabox.bi
WhatsApp +989397728740
Téléphone 65165772
liste de montant Vote > Montant Raccroche
Le 04/10/2023
* 
**/

namespace App\Modules\demande_new\Controllers;   

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class MontantVote_Inf_MontantRaccroche extends BaseController
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

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }

  // gestion d'affichage pour la 1ere insertion 
  public function index()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data=$this->urichk();
    
    $data['titre'] = lang('messages_lang.titr_montvot_inf');
    $ind=$this->indicateur();
    $data['get_qte_phys']=$ind['get_qte_phys'];
    $data['get_pas_qte_phys']=$ind['get_pas_qte_phys'];
    $data['get_racrochet'] = $ind['get_racrochet'];
    $data['get_deja_racrochet'] = $ind['get_deja_racrochet']; 
    $data['nbre_infer'] = $ind['nbre_infer'];
    $data['nbre_super'] = $ind['nbre_super'];
    $data['id_inst'] = $ind['id_inst'];
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $getInst  = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION', 'inst_institutions','INSTITUTION_ID IN ('.$data['id_inst'].') ',' DESCRIPTION_INSTITUTION ASC');
    $data['get_institution'] = $this->ModelPs->getRequete($callpsreq, $getInst);
    return view('App\Modules\demande_new\Views\MontantVote_Inf_MontantRaccroche_List_View',$data);
  }

  public function liste_vote_inf_racc()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID', 'user_affectaion', 'USER_ID = '.$user_id.'' , ' USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);
    $ID_INST='';
    $sous_tutel = '';
    $aff='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';
      if (!empty($value->SOUS_TUTEL_ID))
      {
        $sous_tutel .=$value->SOUS_TUTEL_ID.' ,';
      }           
    }

    $ID_INST = substr($ID_INST,0,-1);
    $sous_tutel = substr($sous_tutel,0,-1);
    $aff = ' AND exe.INSTITUTION_ID IN ('.$ID_INST.') AND exe.SOUS_TUTEL_ID IN ('.$sous_tutel.')';

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $critere = '';
    if(!empty($INSTITUTION_ID))
    {
      $critere = ' AND exe.INSTITUTION_ID='.$INSTITUTION_ID.'';
    }
    $cond=$aff.''.$critere;

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
    $order_column = array(1, 'LIBELLE', 'ENG_BUDGETAIRE','ENG_JURIDIQUE', 'LIQUIDATION', 'ORDONNANCEMENT', 'PAIEMENT', 1);
    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY EXECUTION_BUDGETAIRE_ID   ASC';

    $search = !empty($_POST['search']['value']) ? (' AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%' . $var_search . '%" OR ORDONNANCEMENT LIKE "%' . $var_search . '%" OR PAIEMENT LIKE "%' . $var_search . '%" OR DECAISSEMENT LIKE "%' . $var_search . '%" OR ENG_BUDGETAIRE LIKE "%' . $var_search . '%" OR  ENG_JURIDIQUE LIKE "%' . $var_search . '%" OR  LIQUIDATION LIKE "%' . $var_search . '%" OR  LIBELLE LIKE "%' . $var_search . '%" OR PAIEMENT LIKE "%' . $var_search . '%")') : '';

    // Condition pour la requête principale
    $conditions = $critere . ' ' . $search . ' ' . $group . ' ' . $order_by . ' ' . $limit;
    // Condition pour la requête de filtre
    $conditionsfilter = $critere . ' ' . $search . ' ' . $group;

    $requetedebase= 'SELECT LIBELLE,ENG_BUDGETAIRE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,ENG_JURIDIQUE,LIQUIDATION,ORDONNANCEMENT,PAIEMENT,DECAISSEMENT,DATE_DEMANDE,EXECUTION_BUDGETAIRE_ID,IS_RACCROCHE,TRANSFERTS_CREDITS,CREDIT_APRES_TRANSFERT,IS_TRANSFERTS FROM execution_budgetaire exe JOIN inst_institutions_ligne_budgetaire ligne ON exe.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE IS_RACCROCHE = 0 AND IS_TRANSFERTS=1 AND CREDIT_VOTE < CREDIT_APRES_TRANSFERT '.$cond;

    $var_search = !empty($this->request->getPost('search')['value']) ? $this->request->getPost('search')['value'] : null;
    $limit = 'LIMIT 0,10';

    $requetedebases = $requetedebase . ' ' . $conditions;
    $requetedebasefilter = $requetedebase . ' ' . $conditionsfilter;
    $query_secondaire = "CALL `getTable`('" . $requetedebases . "');";
    $fetch_data = $this->ModelPs->datatable($query_secondaire);

    $data = array();
    $u = 1;
    $profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    foreach ($fetch_data as $row)
    {
      $sub_array = array();
      if (mb_strlen($row->LIBELLE) > 8)
      { 
        $LIBELLE =  mb_substr($row->LIBELLE, 0, 8) .'...<a class="btn-sm" title="Afficher" data-toggle="modal" data-target="#institution'.$row->EXECUTION_BUDGETAIRE_ID.'" data-toggle="tooltip" ><i class="fa fa-eye"></i></a>';
      }else
      {
        $LIBELLE =  $row->LIBELLE;
      }
      $sub_array[] ="<a class='' title='Donner la quantité' href='".base_url("transfert/Transfert_Incrementation/getOne/".$row->EXECUTION_BUDGETAIRE_RACCROCHAGE_ID)."' >".$row->IMPUTATION."</a>";
      $sub_array[] = $LIBELLE." 
      <div class='modal fade' id='institution".$row->EXECUTION_BUDGETAIRE_ID."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <h5><b> ".$row->LIBELLE." </b></h5>
      </center>
      </div>
      <div class='modal-footer'>
      Quitter
      <button class='btn btn-primary btn-md' data-dismiss='modal'>
      </button>
      </div>
      </div>
      </div>
      </div>";
      $engage=number_format($row->ENG_BUDGETAIRE,'2',',',' ');
      $jurdique=number_format($row->ENG_JURIDIQUE,'2',',',' ');
      $liquidation=number_format($row->LIQUIDATION,'2',',',' ');
      $ordanance=number_format($row->ORDONNANCEMENT,'2',',',' ');
      $transfert=number_format($row->ORDONNANCEMENT,'2',',',' ');
      $transfert_apres=number_format($row->CREDIT_APRES_TRANSFERT,'2',',',' ');
      $paiement=number_format($row->PAIEMENT,'2',',',' ');
      $decaisment=number_format($row->DECAISSEMENT,'2',',',' ');

      $sub_array[] = !empty($engage) ? ' '.$engage.'  BIF' :'0' ;
      $sub_array[] = !empty($transfert) ? ' '.$transfert.'  BIF' :'0';
      $sub_array[] = !empty($transfert_apres) ? ' '.$transfert_apres.' BIF' :'0';
      $sub_array[] = !empty($jurdique) ? ' '.$jurdique.' BIF' :'0';
      $sub_array[] = !empty($liquidation) ? ' '.$liquidation.' BIF' :'0';
      $sub_array[] = !empty($ordanance) ? ' '.$ordanance.' BIF' :'0';
      $sub_array[] = !empty($paiement) ? ' '.$paiement.' BIF' :'0';
      $sub_array[] = !empty($decaisment) ? ' '.$decaisment.' BIF' :'0';
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
}
?>