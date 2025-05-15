<?php

/**
* 
develope par SONIA MUNEZERO
sonia@mediabox.bi
WhatsApp +989397728740
Téléphone 65165772
liste ligne budgetaire qui ont des quantites physique 
Le 19/09/2023
* 
**/

namespace App\Modules\demande_new\Controllers;   

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Ligne_Budget_Qte_phys extends BaseController
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
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout'); 
    }
    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data=$this->urichk();
    $data['titre'] = "Liste des activités raccrochées";
    $ind=$this->indicateur_new();
    $data['get_qte_phys']=$ind['get_qte_phys'];
    $data['get_pas_qte_phys']=$ind['get_pas_qte_phys'];
    $data['get_racrochet'] = $ind['get_racrochet'];
    $data['get_deja_racrochet'] = $ind['get_deja_racrochet'];
    $data['institutions_user']=$ind['getuser'];
    return view('App\Modules\demande_new\Views\Ligne_Budget_Qte_phys_View',$data);
  }
  //récupération du sous tutelle par rapport à l'institution
  function get_sous_tutelle($CODE_INSTITUTION=0)
  {   
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }
    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
      return redirect('Login_Ptba/do_logout');
    }

    $db = db_connect();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $get_sous_tutelle = $this->getBindParms('SOUS_TUTEL_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.CODE_INSTITUTION='.$CODE_INSTITUTION.' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
    $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);
    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($sous_tutelle as $key)
    {
      $html.='<option value="'.$key->CODE_SOUS_TUTEL.'">'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
    }
    $output = array(
        "sous_tutel" => $html
    );
    return $this->response->setJSON($output);
  }

  public function liste_qte_phys()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }
    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
      return redirect('Login_Ptba/do_logout');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $institution=' AND exe.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';
    $CODE_INSTITUTION=$this->request->getPost('CODE_INSTITUTION');
    $CODE_SOUS_TUTEL=$this->request->getPost('CODE_SOUS_TUTEL');

    if (!empty($CODE_INSTITUTION))
    {
      $institution.=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "'.$CODE_INSTITUTION.'%"';
    }
    if (!empty($CODE_SOUS_TUTEL))
    {
      $institution.=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "'.$CODE_INSTITUTION.'00'.$CODE_SOUS_TUTEL.'%"';
    }

    $query_principal='SELECT racc.QTE_RACCROCHE,racc.UNITE,racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,ptba.CODES_PROGRAMMATIQUE,mvt.DESC_MOUVEMENT_DEPENSE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,ptba.ACTIVITES,exe.SOUS_TUTEL_ID,racc.MONTANT_RACCROCHE,racc.MONTANT_RACCROCHE_JURIDIQUE,racc.MONTANT_RACCROCHE_LIQUIDATION,racc.MONTANT_RACCROCHE_ORDONNANCEMENT,racc.MONTANT_RACCROCHE_PAIEMENT,racc.MONTANT_RACCROCHE_DECAISSEMENT FROM execution_budgetaire_raccrochage_activite_new racc JOIN execution_budgetaire_raccrochage_activite_detail det ON det.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID=racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID  JOIN execution_budgetaire_new exe ON exe.EXECUTION_BUDGETAIRE_ID=racc.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN proc_mouvement_depense mvt ON mvt.MOUVEMENT_DEPENSE_ID=det.MOUVEMENT_DEPENSE_ID JOIN inst_institutions_ligne_budgetaire ligne ON exe.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 AND exe.TRIMESTRE_ID=1 AND exe.IS_RACCROCHE=1 AND det.EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID IN(SELECT EXECUTION_BUDGETAIRE_RACCROCHAGE_DETAIL_ID FROM historique_raccrochage_activite_detail WHERE TYPE_RACCROCHAGE_ID=2) '.$institution;

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit="LIMIT 0,10";
    if($_POST['length'] != -1)
    {
      $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
    }

    $order_by="";
    $order_column="";
    $order_column= array('ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba.CODES_PROGRAMMATIQUE','ptba.ACTIVITES',1,1,'ptba.UNITE','mvt.DESC_MOUVEMENT_DEPENSE',1,1,1,1,1,1);

    $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID DESC";

    $search = !empty($_POST['search']['value']) ?  (' AND ( ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR  ptba.CODES_PROGRAMMATIQUE LIKE "%'.$var_search.'%" OR ptba.ACTIVITES LIKE "%'.$var_search.'%" OR  racc.MONTANT_RACCROCHE LIKE "%'.$var_search.'%" OR  racc.QTE_RACCROCHE LIKE "%'.$var_search.'%" OR mvt.DESC_MOUVEMENT_DEPENSE LIKE "%'.$var_search.'%" OR ptba.UNITE LIKE "%'.$var_search.'%")'):"";
    $search = str_replace("'","\'",$search);
    $critaire = " ";

    $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;
    $query_filter = $query_principal." ".$search." ".$critaire;
    $requete="CALL `getList`('".$query_secondaire."')";
    $fetch_cov_frais = $this->ModelPs->datatable( $requete);
    $data = array();
    $u=0;
    foreach($fetch_cov_frais as $info)
    {
      if (mb_strlen($info->ACTIVITES) > 8){ 
        $ACTIVITES =  mb_substr($info->ACTIVITES, 0, 8) .'...<a class="btn-sm" title="Afficher"  onclick="show_modal('.$info->EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.')"><i class="fa fa-eye"></i></a>';
      }else
      {
        $ACTIVITES = !empty($info->ACTIVITES) ?  : 'N/A';
      }

      $montant_racc=$info->MONTANT_RACCROCHE;
      $qte_racc=$info->QTE_RACCROCHE;
      $post=array();
      $u=$u+1;
      $post[]=!empty($info->CODES_PROGRAMMATIQUE) ? $info->CODES_PROGRAMMATIQUE : 'N/A';
      $post[]=$ACTIVITES;
      $post[]=!empty($info->IMPUTATION) ? $info->IMPUTATION : 'N/A';     
      $post[]=$qte_racc;
      $post[]=!empty($info->UNITE) ? $info->UNITE : 'N/A';
      $post[]=$info->DESC_MOUVEMENT_DEPENSE;
      $post[]=number_format($montant_racc,2,","," ");
      $post[] = (!empty($info->MONTANT_RACCROCHE_JURIDIQUE)) ? number_format($info->MONTANT_RACCROCHE_JURIDIQUE,'2',',',' ') : 0 ;
      $post[] = (!empty($info->MONTANT_RACCROCHE_LIQUIDATION)) ? number_format($info->MONTANT_RACCROCHE_LIQUIDATION,'2',',',' ') : 0 ;
      $post[] = (!empty($info->MONTANT_RACCROCHE_ORDONNANCEMENT)) ? number_format($info->MONTANT_RACCROCHE_ORDONNANCEMENT,'2',',',' ') : 0 ;
      $post[] = (!empty($info->MONTANT_RACCROCHE_PAIEMENT)) ? number_format($info->MONTANT_RACCROCHE_PAIEMENT,'2',',',' ') : 0 ;
      $post[] = (!empty($info->MONTANT_RACCROCHE_DECAISSEMENT)) ? number_format($info->MONTANT_RACCROCHE_DECAISSEMENT,'2',',',' ') : 0 ;
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
  
  function activiteGet($id=0)
  {  
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
      return redirect('Login_Ptba/do_logout');
    }

    $data=$this->urichk();
    $acti="SELECT ACTIVITES FROM execution_budgetaire_raccrochage_activite_new JOIN ptba ON ptba.PTBA_ID=execution_budgetaire_raccrochage_activite_new.PTBA_ID WHERE 1 AND EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = ".$id;
    $acti='CALL `getList`("'.$acti.'")';
    $get_act = $this->ModelPs->getRequeteOne( $acti);
    $output = array(
      "activity" => $get_act['ACTIVITES'],   
    );
    return $this->response->setJSON($output);
  }
}
?>