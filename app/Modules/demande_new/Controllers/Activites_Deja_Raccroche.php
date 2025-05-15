<?php
/**
* 
develope par SONIA MUNEZERO
sonia@mediabox.bi
WhatsApp +989397728740
Téléphone 65165772
liste des activites deja raccroche avec la possibilite d'ajouter les 2 documments(bon d'engagement et titre de decaissement) 
Le 17/10/2023
* 
**/

namespace App\Modules\demande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Activites_Deja_Raccroche extends BaseController
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

  public function save_all_table($table,$columsinsert,$datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams =[$table,$columsinsert,$datacolumsinsert];
    $result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
    return $id=$result['id'];
  }

  public function uploadFile($fieldName, $folder, $prefix = ''): string
  {
    $prefix = ($prefix === '') ? uniqid() : $prefix;
    $path = '';
    $file = $this->request->getFile($fieldName);
    if ($file->isValid() && !$file->hasMoved()) {
      $newName = uniqid(). '' . date('ymdhis') . '.' . $file->getExtension();
      $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
      $path = 'uploads/' . $folder . '/' . $newName;
    }
    return $newName;
  }

  //gestion d'affichage pour la 1ere insertion 
  public function index()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data=$this->urichk();
    return view('App\Modules\demande_new\Views\Activites_Deja_Raccrocher_List_View',$data);
  }

  public function list_activities()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $getInst  = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION', 'inst_institutions','INSTITUTION_ID IN ('.$ID_INST.')',' DESCRIPTION_INSTITUTION ASC');
    $institutions= $this->ModelPs->getRequeteOne($callpsreq, $getInst);
    $institution=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "'.$institutions['CODE_INSTITUTION'].'%"';

    if(!empty($getuser['SOUS_TUTEL_ID']))
    {
      $getsoustitre=$this->getBindParms('SOUS_TUTEL_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel','SOUS_TUTEL_ID='.$getuser['SOUS_TUTEL_ID'],' DESCRIPTION_SOUS_TUTEL ASC');
      $soustitre = $this->ModelPs->getRequeteOne($callpsreq, $getsoustitre);
      $institution=' AND ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "'.$institutions['CODE_INSTITUTION'].'00'.$soustitre['CODE_SOUS_TUTEL'].'%"';
    }

    $query_principal='SELECT racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,ptba.CODES_PROGRAMMATIQUE,mvt.DESC_MOUVEMENT_DEPENSE,ligne.CODE_NOMENCLATURE_BUDGETAIRE AS IMPUTATION,ptba.ACTIVITES,racc.MONTANT_RACCROCHE,racc.MONTANT_RACCROCHE_JURIDIQUE,racc.MONTANT_RACCROCHE_LIQUIDATION,racc.MONTANT_RACCROCHE_ORDONNANCEMENT,racc.MONTANT_RACCROCHE_PAIEMENT,racc.MONTANT_RACCROCHE_DECAISSEMENT FROM execution_budgetaire_raccrochage_activite racc JOIN execution_budgetaire exe ON exe.EXECUTION_BUDGETAIRE_ID=racc.EXECUTION_BUDGETAIRE_ID JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID JOIN proc_mouvement_depense mvt ON mvt.MOUVEMENT_DEPENSE_ID=racc.MOUVEMENT_DEPENSE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE exe.IS_RACCROCHE=1 '.$institution;

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit="LIMIT 0,10";
    if($_POST['length'] != -1)
    {
      $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
    }

    $order_by="";
    $order_column="";
    $order_column= array('ptba.CODES_PROGRAMMATIQUE','ptba.ACTIVITES','ligne.CODE_NOMENCLATURE_BUDGETAIRE',1,1,'mvt.DESC_MOUVEMENT_DEPENSE',1,1,1,1,1,1);

    $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY racc.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID DESC";

    $search = !empty($_POST['search']['value']) ?  (' AND ( ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR  ptba.CODES_PROGRAMMATIQUE LIKE "%'.$var_search.'%" OR ptba.ACTIVITES LIKE "%'.$var_search.'%" OR  racc.MONTANT_RACCROCHE LIKE "%'.$var_search.'%" OR mvt.DESC_MOUVEMENT_DEPENSE LIKE "%'.$var_search.'%")'):"";
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
      $statut = 'SELECT EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,STATUT FROM raccrochage_detail WHERE EXECUTION_BUDGETAIRE_RACCROCHAGE_ID='.$info->EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.' order by RACCROCHAGE_DETAIL_ID DESC';
      $statut = "CALL `getTable`('" . $statut . "');";
      $get_statut = $this->ModelPs->getRequeteOne($statut);

      if (mb_strlen($info->ACTIVITES) > 8)
      { 
        $ACTIVITES =  mb_substr($info->ACTIVITES, 0, 8) .'...<a class="btn-sm" title="Afficher"  onclick="show_activities('.$info->EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.')"><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $ACTIVITES = !empty($info->ACTIVITES) ?  : 'N/A';
      }

      $montant_racc=$info->MONTANT_RACCROCHE;
      $post=array();
      $u=$u+1;

      $progr_row='';
      if ($get_statut['STATUT']==0)
      {
        $progr_row= "<a  title='Document' href='".base_url("demande_new/Activites_Deja_Raccroche/documents/".$info->EXECUTION_BUDGETAIRE_RACCROCHAGE_ID)."' >".$info->CODES_PROGRAMMATIQUE."</a>";
      }
      else
      {
        $progr_row=$info->CODES_PROGRAMMATIQUE;
      }

      $post[]=$progr_row;
      $post[]=$ACTIVITES;
      $post[]=!empty($info->IMPUTATION) ? $info->IMPUTATION : 'N/A';
      $post[]=$info->DESC_MOUVEMENT_DEPENSE;
      $post[]=number_format($montant_racc,2,","," ");
      $post[] = (!empty($info->MONTANT_RACCROCHE_JURIDIQUE)) ? number_format($info->MONTANT_RACCROCHE_JURIDIQUE,'2',',',' ') : 0 ;
      $post[] = (!empty($info->MONTANT_RACCROCHE_LIQUIDATION)) ? number_format($info->MONTANT_RACCROCHE_LIQUIDATION,'2',',',' ') : 0 ;
      $post[] = (!empty($info->MONTANT_RACCROCHE_ORDONNANCEMENT)) ? number_format($info->MONTANT_RACCROCHE_ORDONNANCEMENT,'2',',',' ') : 0 ;
      $post[] = (!empty($info->MONTANT_RACCROCHE_PAIEMENT)) ? number_format($info->MONTANT_RACCROCHE_PAIEMENT,'2',',',' ') : 0 ;
      $post[] = (!empty($info->MONTANT_RACCROCHE_DECAISSEMENT)) ? number_format($info->MONTANT_RACCROCHE_DECAISSEMENT,'2',',',' ') : 0 ;

      $action="<center><a class='btn btn-primary btn-sm' title='Document' href='".base_url("demande_new/Activites_Deja_Raccroche/documents/".$info->EXECUTION_BUDGETAIRE_RACCROCHAGE_ID)."' > <i class='fa fa-edit text-light'></i></a></center>";
      $fait="<center><span class='fa fa-check' style='font-size:20px;font-weight: bold;color: green;' data-toggle='tooltip' title='Fait'>&nbsp;</span></center>";

      if($get_statut['STATUT']==0)
      {
        $post[]=$action;
      }
      else
      {
        $post[]=$fait;
      }
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

  function activities($id=0)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data=$this->urichk();
    $acti="SELECT ACTIVITES FROM execution_budgetaire_raccrochage_activite JOIN ptba ON ptba.PTBA_ID=execution_budgetaire_raccrochage_activite.PTBA_ID WHERE 1 AND EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = ".$id;
    $acti='CALL `getList`("'.$acti.'")';
    $get_act = $this->ModelPs->getRequeteOne( $acti);
    $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID=$id;

    $output = array(
      "activity" => $get_act['ACTIVITES'], 
      "EXECUTION_BUDGETAIRE_RACCROCHAGE_ID"=>$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID, 
    );
    return $this->response->setJSON($output);
  }

  function documents($id=0)
  {
    $data=$this->urichk();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $doc="SELECT TYPE_DOCUMENT_ID, DESCR_DOCUMENT FROM type_document WHERE 1";
    $doc='CALL `getList`("'.$doc.'")';
    $data['get_doc'] = $this->ModelPs->getRequete($doc);
    $data['titre'] = ''.lang('messages_lang.titr_ajout_docum').'';
    $data['EXECUTION_BUDGETAIRE_RACCROCHAGE_ID'] = $id;
    return view('App\Modules\demande_new\Views\Activites_Deja_Raccrocher_Add_View',$data);
  }

  function enregistre_document()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID  =  $this->request->getPost('EXECUTION_BUDGETAIRE_RACCROCHAGE_ID');
    $NUMERO_DOCUMENT = $this->request->getPost('NUMERO_DOCUMENT');
    $PATH_DOCUMENT = $this->request->getPost('PATH_DOCUMENT');
    $PATH=$this->uploadFile('PATH_DOCUMENT','Documet_activite_raccroche',$PATH_DOCUMENT);
    $DATE_DOCUMENT = $this->request->getPost('DATE_DOCUMENT');
    $TYPE_DOCUMENT_ID = $this->request->getPost('TYPE_DOCUMENT_ID');
    $STATUT=1;

    $insertIntoDetail='raccrochage_detail';
    $columsinsert="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,NUMERO_DOCUMENT,PATH_DOCUMENT,DATE_DOCUMENT,TYPE_DOCUMENT_ID,STATUT";
    $datacolumsinsert=$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID.",'".$NUMERO_DOCUMENT."','".$PATH."','".$DATE_DOCUMENT."',".$TYPE_DOCUMENT_ID.",".$STATUT;
    $RACCROCHAGE_DETAIL_ID =$this->save_all_table($insertIntoDetail,$columsinsert,$datacolumsinsert);

    return redirect('demande_new/Activites_Deja_Raccroche');
  }
}
?>