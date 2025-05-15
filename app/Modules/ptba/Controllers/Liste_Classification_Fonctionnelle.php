<?php 
/*
* develope par SONIA MUNEZERO
* sonia@mediabox.bi
* WhatsApp +989397728740
* Téléphone 65165772
* Liste ptba classification fonctionnelle
* Le 26/09/2023
*/
namespace App\Modules\ptba\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
class Liste_Classification_Fonctionnelle extends BaseController
{
  public function __construct()
  { 
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  //function qui appelle le view de la liste 
  function index()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_FONCTIONNELLE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data = $this->urichk();

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    $division = $this->getBindParms('DIVISION_ID,CODE_DIVISION,LIBELLE_DIVISION', 'class_fonctionnelle_division', '1', 'CODE_DIVISION ASC');
    $data['get_division'] = $this->ModelPs->getRequete($psgetrequete, $division);

    $tranche = $this->getBindParms('TRANCHE_ID,CODE_TRANCHE,DESCRIPTION_TRANCHE','op_tranches','1','TRANCHE_ID ASC');
    $data['get_tranche'] = $this->ModelPs->getRequete($psgetrequete, $tranche);

    return view('App\Modules\ptba\Views\Liste_Classification_Fonctionnelle_View',$data);   
  }

	//liste des ptba classification fonctionnelle
  function classification_liste()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_FONCTIONNELLE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    $DIVISION_ID = $this->request->getPost('DIVISION_ID');
    $GROUPE_ID = $this->request->getPost('GROUPE_ID');
    $CLASSE_ID = $this->request->getPost('CLASSE_ID');
    $CODE_TRANCHE = $this->request->getPost('CODE_TRANCHE');
    $CODE_DIVISION=0;
    $critere_div='';

		//Filtre par division
    if(!empty($DIVISION_ID))
    {
      $division = $this->getBindParms('DIVISION_ID,CODE_DIVISION,LIBELLE_DIVISION', 'class_fonctionnelle_division', 'DIVISION_ID='.$DIVISION_ID, 'LIBELLE_DIVISION ASC');
      $get_division = $this->ModelPs->getRequeteOne($psgetrequete, $division);
      $CODE_DIVISION = $get_division['CODE_DIVISION'];
      $critere_div .= " AND ptba.DIVISION_FONCTIONNELLE='".$CODE_DIVISION."'";
    }

		//Filtre par groupe
    if(!empty($GROUPE_ID))
    {
      $gpe = $this->getBindParms('GROUPE_ID,DIVISION_ID,CODE_GROUPE,LIBELLE_GROUPE', 'class_fonctionnelle_groupe', 'GROUPE_ID='.$GROUPE_ID, 'CODE_GROUPE ASC');
      $get_gpe= $this->ModelPs->getRequeteOne($psgetrequete, $gpe);
      $critere_div .=" AND ptba.GROUPE_FONCTIONNELLE ='".$get_gpe['CODE_GROUPE']."'";
    }

    //Filtre par classe
    if(!empty($CLASSE_ID))
    {
      $class = $this->getBindParms('CLASSE_ID,GROUPE_ID,CODE_CLASSE,LIBELLE_CLASSE', 'class_fonctionnelle_classe', 'CLASSE_ID='.$CLASSE_ID, 'CODE_CLASSE ASC');
      $get_class= $this->ModelPs->getRequeteOne($psgetrequete, $class);
      $critere_div .=" AND ptba.CLASSE_FONCTIONNELLE ='".$get_class['CODE_CLASSE']."'";
    }

    $query_principal="SELECT ptba.PTBA_ID,ptba.UNITE,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME AS CODE_PROGRAMME,prog.INTITULE_PROGRAMME AS INTITULE_PROGRAMME,act.CODE_ACTION AS CODE_ACTION,act.LIBELLE_ACTION AS LIBELLE_ACTION,ptba.ACTIVITES,ptba.CODES_PROGRAMMATIQUE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ptba.RESULTATS_ATTENDUS,ptba.T1,ptba.T2,ptba.T3,ptba.T4,ptba.QT1,ptba.QT2,ptba.QT3,ptba.QT4,ptba.QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,ptba.PROGRAMMATION_FINANCIERE_BIF FROM ptba JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions_actions act ON act.ACTION_ID=ptba.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID = ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 ".$critere_div."";

    $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit='LIMIT 0,10';
    if($_POST['length'] != -1)
    {
      $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
    }
    $order_by='';
    $order_column='';
    $order_column= array('inst.DESCRIPTION_INSTITUTION',1,1,1,'ligne.CODE_NOMENCLATURE_BUDGETAIRE','ptba.RESULTATS_ATTENDUS',1,1);
    $order_by = isset($_POST['order']) ? ' ORDER BY '. $order_column[$_POST['order']['0']['column']] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY ptba.PTBA_ID ASC';
    $search = !empty($_POST['search']['value']) ?  (" AND (inst.CODE_INSTITUTION LIKE '%$var_search%' OR inst.DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR prog.CODE_PROGRAMME LIKE '%$var_search%' OR prog.INTITULE_PROGRAMME LIKE '%$var_search%' OR act.CODE_ACTION LIKE '%$var_search%' OR act.LIBELLE_ACTION LIKE '%$var_search%' OR ptba.ACTIVITES LIKE '%$var_search%' OR CODES_PROGRAMMATIQUE LIKE '%$var_search%' OR ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%')"):'';
    $query_secondaire=$query_principal.' '.$search.' '.$order_by.'   '.$limit;
    $query_filter = $query_principal.' '.$search;
    $requete='CALL `getList`("'.$query_secondaire.'")';
    $fetch_cov_frais = $this->ModelPs->datatable($requete);
    $data = array();
    $u=1;

    foreach($fetch_cov_frais as $info)
    {
      $quantite=0;
      $montant=0;

      if(!empty($CODE_TRANCHE))
      {
        if($CODE_TRANCHE == 'T1')
        {
          $quantite=floatval($info->QT1);
          $montant=floatval($info->T1);
        }
        elseif($CODE_TRANCHE == 'T2')
        {
          $quantite=floatval($info->QT2);
          $montant=floatval($info->T2);
        }
        elseif($CODE_TRANCHE == 'T3')
        {
          $quantite=floatval($info->QT3);
          $montant=floatval($info->T3);
        }
        elseif($CODE_TRANCHE == 'T4')
        {
          $quantite=floatval($info->QT4);
          $montant=floatval($info->T4);
        }
      }
      else
      {
        $quantite = floatval($info->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE);
        $montant = floatval($info->PROGRAMMATION_FINANCIERE_BIF);
      }

      $post=array();
      $INTITULE_MINISTERE = addslashes($info->INTITULE_MINISTERE);

      if(mb_strlen($INTITULE_MINISTERE) > 9)
      {
        //Declaration des labels pour l'internalisation
        $icone_afficher = lang("messages_lang.icone_afficher");
        $INTITULE_MINISTERE =  mb_substr($INTITULE_MINISTERE, 0, 8) .'...<a class="btn-sm" data-toggle="modal" data-target="#inst'.$info->PTBA_ID.'" data-toggle="tooltip" title='.$icone_afficher.'><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $INTITULE_MINISTERE = $INTITULE_MINISTERE ;
      }

      $INTITULE_PROGRAMME = addslashes($info->INTITULE_PROGRAMME);

      if(mb_strlen($INTITULE_PROGRAMME) > 9)
      {
        //Declaration des labels pour l'internalisation
        $icone_afficher = lang("messages_lang.icone_afficher");
        $INTITULE_PROGRAMME =  mb_substr($INTITULE_PROGRAMME, 0, 8) .'...<a class="btn-sm" data-toggle="modal" data-target="#prog'.$info->PTBA_ID.'" data-toggle="tooltip" title='.$icone_afficher.'><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $INTITULE_PROGRAMME = $INTITULE_PROGRAMME   ;
      }

      $LIBELLE_ACTION = addslashes($info->LIBELLE_ACTION);
      if(mb_strlen($LIBELLE_ACTION) > 9)
      {
        //Declaration des labels pour l'internalisation
        $icone_afficher = lang("messages_lang.icone_afficher");
        $LIBELLE_ACTION =  mb_substr($LIBELLE_ACTION, 0, 8) .'...<a class="btn-sm" data-toggle="modal" data-target="#action'.$info->PTBA_ID.'" data-toggle="tooltip" title='.$icone_afficher.'><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $LIBELLE_ACTION =  $LIBELLE_ACTION;
      }

      $ACTIVITES = addslashes($info->ACTIVITES);
      if (mb_strlen($ACTIVITES) > 9)
      {
        //Declaration des labels pour l'internalisation
        $icone_afficher = lang("messages_lang.icone_afficher");
        $ACTIVITES =  mb_substr($ACTIVITES, 0, 8) .'...<a class="btn-sm" data-toggle="modal" data-target="#activites'.$info->PTBA_ID.'" data-toggle="tooltip" title='.$icone_afficher.'><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $ACTIVITES =  $ACTIVITES;
      }

      $RESULTATS_ATTENDUS = addslashes($info->RESULTATS_ATTENDUS);
      if(mb_strlen($RESULTATS_ATTENDUS) > 9)
      {
        //Declaration des labels pour l'internalisation
        $icone_afficher = lang("messages_lang.icone_afficher");
        $RESULTATS_ATTENDUS =  mb_substr($RESULTATS_ATTENDUS, 0, 9) .'...<a class="btn-sm" data-toggle="modal" data-target="#resultat'.$info->PTBA_ID.'" data-toggle="tooltip" title='.$icone_afficher.'><i class="fa fa-eye"></i></a>';
      }
      else
      {
        $RESULTATS_ATTENDUS =  $RESULTATS_ATTENDUS;
      }

      $unite = addslashes($info->UNITE);
      $action = !empty($LIBELLE_ACTION) ? $LIBELLE_ACTION : 'N/A';
      $code_action = !empty($info->CODE_ACTION) ? $info->CODE_ACTION : 'N/A';
      $instit = !empty($INTITULE_MINISTERE) ? $INTITULE_MINISTERE : 'N/A';
      $code_instit = !empty($info->CODE_MINISTERE) ? $info->CODE_MINISTERE : 'N/A';
      $program = !empty($INTITULE_PROGRAMME) ? $INTITULE_PROGRAMME : 'N/A';
      $code_program = !empty($info->CODE_PROGRAMME) ? $info->CODE_PROGRAMME : 'N/A';
      $activites = !empty($ACTIVITES) ? $ACTIVITES : 'N/A';
      $code_activites = !empty($info->CODES_PROGRAMMATIQUE) ? $info->CODES_PROGRAMMATIQUE : 'N/A';
      $code_budget = !empty($info->CODE_NOMENCLATURE_BUDGETAIRE) ? $info->CODE_NOMENCLATURE_BUDGETAIRE : 'N/A';
      $result = !empty($RESULTATS_ATTENDUS) ? $RESULTATS_ATTENDUS : 'N/A';
      $unite = !empty($unite) ? $unite : 'N/A';

      $post[]=$instit.' ('.$code_instit.')';
      $post[]=$program.' ('.$code_program.')';
      $post[]=$action.' ('.$code_action.')';
      $post[]=$activites.' ('.$code_activites.')';
      $post[]=$code_budget;			
      $post[]=$result;
      $post[]=number_format($quantite,2,","," ").' '.$unite;
      $post[]=number_format($montant,2,","," ")." BIF

      <div class='modal fade' id='inst".$info->PTBA_ID."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <b style='font-size:13px;'> ".trim($info->INTITULE_MINISTERE)." </b>
      </center>
      </div>
      <div class='modal-footer'>
      <button class='btn btn-primary btn-md' data-dismiss='modal'>
      Quitter
      </button>
      </div>
      </div>
      </div>
      </div>

      <div class='modal fade' id='prog".$info->PTBA_ID."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <b style='font-size:13px;'> ".trim($info->INTITULE_PROGRAMME)." </b>
      </center>
      </div>
      <div class='modal-footer'>
      <button class='btn btn-primary btn-md' data-dismiss='modal'>
      Quitter
      </button>
      </div>
      </div>
      </div>
      </div>

      <div class='modal fade' id='action".$info->PTBA_ID."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <b style='font-size:13px;'> ".trim($info->LIBELLE_ACTION)." </b>
      </center>
      </div>
      <div class='modal-footer'>
      <button class='btn btn-primary btn-md' data-dismiss='modal'>
      Quitter
      </button>
      </div>
      </div>
      </div>
      </div>

      <div class='modal fade' id='activites".$info->PTBA_ID."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <b style='font-size:13px;'> ".trim($info->ACTIVITES)." </b>
      </center>
      </div>
      <div class='modal-footer'>
      <button class='btn btn-primary btn-md' data-dismiss='modal'>
      Quitter
      </button>
      </div>
      </div>
      </div>
      </div>

      <div class='modal fade' id='resultat".$info->PTBA_ID."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <b style='font-size:13px;'> ".trim($info->RESULTATS_ATTENDUS)." </b>
      </center>
      </div>
      <div class='modal-footer'>
      <button class='btn btn-primary btn-md' data-dismiss='modal'>
      Quitter
      </button>
      </div>
      </div>
      </div>
      </div>";
      $data[]=$post;  
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

  //Sélectionner les groupes à partir des divisions
  function get_groupes()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_FONCTIONNELLE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $DIVISION_ID =$this->request->getPost('DIVISION_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $division = $this->getBindParms('GROUPE_ID,DIVISION_ID,CODE_GROUPE,LIBELLE_GROUPE', 'class_fonctionnelle_groupe', 'DIVISION_ID='.$DIVISION_ID, 'CODE_GROUPE ASC');
    $get_division = $this->ModelPs->getRequete($callpsreq, $division);
      //Declaration des labels pour l'internalisation
    $input_select = lang("messages_lang.labelle_selecte");
    $html='<option value="">'.$input_select.'</option>';

    if(!empty($get_division) )
    {
      foreach($get_division as $key)
      {
        if($key->GROUPE_ID==set_value('GROUPE_ID'))
        {
          $html.= "<option value='".$key->GROUPE_ID."' selected>".$key->CODE_GROUPE." - ".$key->LIBELLE_GROUPE."</option>";
        }
        else
        {
          $html.= "<option value='".$key->GROUPE_ID."'>".$key->CODE_GROUPE." - ".$key->LIBELLE_GROUPE."</option>";
        }
      }
    }
    $output = array('status' => TRUE , 'div' => $html);
    return $this->response->setJSON($output);
  }

  //Sélectionner les classes à partir des groupes
  function get_classes()
  {
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_PTBA_CLASSIFICATION_FONCTIONNELLE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $GROUPE_ID =$this->request->getPost('GROUPE_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $classe = $this->getBindParms('CLASSE_ID,GROUPE_ID,CODE_CLASSE,LIBELLE_CLASSE', 'class_fonctionnelle_classe', 'GROUPE_ID='.$GROUPE_ID, 'CODE_CLASSE ASC');
    $get_class = $this->ModelPs->getRequete($callpsreq, $classe);
    //Declaration des labels pour l'internalisation
    $input_select = lang("messages_lang.labelle_selecte");
    $html='<option value="">'.$input_select.'</option>';

    if(!empty($get_class) )
    {
      foreach($get_class as $key)
      {
        if($key->CLASSE_ID==set_value('CLASSE_ID'))
        {
          $html.= "<option value='".$key->CLASSE_ID."' selected>".$key->CODE_CLASSE." - ".$key->LIBELLE_CLASSE."</option>";
        }
        else
        {
          $html.= "<option value='".$key->CLASSE_ID."'>".$key->CODE_CLASSE." - ".$key->LIBELLE_CLASSE."</option>";
        }
      }
    }
    $output = array('status' => TRUE , 'classes' => $html);
    return $this->response->setJSON($output);
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }
}
?>