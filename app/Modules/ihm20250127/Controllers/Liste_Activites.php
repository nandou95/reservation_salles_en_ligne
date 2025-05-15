<?php
/**RUGAMBA Jean Vainqueur
*Titre: LISTE ET AJOUT DES ACTIVITES DANS LE MODULE DEMANDE
*Numero de telephone: (+257) 66 33 43 25
*WhatsApp: (+257) 62 47 19 15
*Email: jean.vainqueur@mediabox.bi
*Date: 17 octobre,2023
**/
namespace  App\Modules\ihm\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;

class Liste_Activites extends BaseController
{
	function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs($db);
		$this->my_Model = new ModelPs($db);
    $this->validation = \Config\Services::validation();
    $this->session 	= \Config\Services::session();
    $table = new \CodeIgniter\View\Table();
  }

  //Liste view
  public function index($value='')
  {
    $data=$this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    
    $session  = \Config\Services::session();
    $profil ='';
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    
    if(!empty($user_id))
    {
      if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if($profil == 1)
      {
        //Sélectionner les institutions
        $bindparams = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','1','CODE_INSTITUTION ASC');
        $data['instit'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
        $data['profil'] = $profil;
        return view('App\Modules\ihm\Views\Dem_Liste_Activites_View',$data);
      }
      else
      {
        //Sélectionner les institutions
        $bindparams = $this->getBindParms('inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION,inst_institutions.CODE_INSTITUTION', '`user_affectaion` JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=user_affectaion.INSTITUTION_ID', 'USER_ID='.$user_id.'', '`DESCRIPTION_INSTITUTION` ASC');
        $data['instit'] = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
        $data['profil'] = $profil; 
        return view('App\Modules\ihm\Views\Dem_Liste_Activites_View',$data);
      }
    }
    else
    {
      return redirect('Login_Ptba');
    }
  }

  //fonction pour affichage d'une liste
  public function listing()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    //Filtres de la liste
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $CODE_PROGRAMME = $this->request->getPost('CODE_PROGRAMME');
    $CODE_ACTION = $this->request->getPost('CODE_ACTION');

    $critere1="";
    $critere2="";
    $critere3="";

    //Filtre par institution
    if(!empty($INSTITUTION_ID))
    {   
      $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`','inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, '`DESCRIPTION_INSTITUTION` ASC');
      $inst = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
      $critere1 = " AND `CODE_MINISTERE` LIKE '".$inst['CODE_INSTITUTION']."'";
      
      //Filtre par programme
      if(!empty($CODE_PROGRAMME))
      {
        $critere2=" AND CODE_PROGRAMME LIKE '%".$CODE_PROGRAMME."%' ";

        //Filtre par action
        if(!empty($CODE_ACTION))
        {
          //print_r($CODE_ACTION);exit();
          $critere3=" AND CODE_ACTION LIKE '%".$CODE_ACTION."%' ";
        }
      }
      
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if ($_POST['length'] != -1) {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }
    $order_by = '';

    $order_column=array(1,'ligne.CODE_NOMENCLATURE_BUDGETAIRE','CODES_PROGRAMMATIQUE','ACTIVITES','RESULTATS_ATTENDUS','T1','T2','T3','T4','IS_NOUVEAU',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'] : ' ORDER BY PTBA_ID DESC';

     $search = !empty($_POST['search']['value']) ?  (" AND (ligne.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%')"):'';

    $critaire= $critere1." ".$critere2." ".$critere3;

    //condition pour le query principale
    $conditions = $critaire." ".$search." ".$group." ".$order_by." ".$limit;
    
    // condition pour le query filter
    $conditionsfilter = $critaire." ".$search." ".$group;


    $requetedebase="SELECT PTBA_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,prog.CODE_PROGRAMME,act.CODE_ACTION,ligne.CODE_NOMENCLATURE_BUDGETAIRE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,IS_NOUVEAU,T1,T2,T3,T4 FROM ptba JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1";

    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    
    $data = array();
    $u=1;
    foreach ($fetch_actions as $row)
    {
      $ACTIVITES = (mb_strlen($row->ACTIVITES) > 9) ? (mb_substr($row->ACTIVITES, 0, 9) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->ACTIVITES."'><i class='fa fa-eye'></i></a>") : $row->ACTIVITES;

      $RESULTATS_ATTENDUS = (mb_strlen($row->RESULTATS_ATTENDUS) > 9) ? (mb_substr($row->RESULTATS_ATTENDUS, 0, 9) . "...<a class='btn-sm' data-toggle='tooltip' title='".$row->RESULTATS_ATTENDUS."'><i class='fa fa-eye'></i></a>") : $row->RESULTATS_ATTENDUS;

      $montant1=floatval($row->T1);
      $montant2=floatval($row->T2);
      $montant3=floatval($row->T3);
      $montant4=floatval($row->T4);

      $sub_array = array();
      $sub_array[] = $u++;
      $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
      $sub_array[] = !empty($row->CODES_PROGRAMMATIQUE) ? $row->CODES_PROGRAMMATIQUE : 'N/A';
      $sub_array[] = $ACTIVITES;
      $sub_array[] = $RESULTATS_ATTENDUS;
      $sub_array[]=number_format($montant1,2,","," ");
      $sub_array[]=number_format($montant2,2,","," ");
      $sub_array[]=number_format($montant3,2,","," ");
      $sub_array[]=number_format($montant4,2,","," ");

      $stat='';
      if( $row->IS_NOUVEAU == 1)
      {
        $stat='<font color="green" style="font-weight: 650;">'.lang('messages_lang.is_nouveau').'</font>';
      }else{

        $stat='<font color="red" style="font-weight: 650;">'.lang('messages_lang.is_ancien').'</font>';
      }
      $sub_array[] = $stat."

      <div class='modal fade' id='activite".$row->PTBA_ID."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <b style='font-size:13px;'> ".$row->ACTIVITES." </b>
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

      <div class='modal fade' id='result".$row->PTBA_ID."'>
      <div class='modal-dialog'>
      <div class='modal-content'>
      <div class='modal-body'>
      <center>
      <b style='font-size:13px;'> ".$row->RESULTATS_ATTENDUS." </b>
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
      ";

      $action = '<div class="dropdown" style="color:#fff;">
      <a class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-left">';

      $action .="<li>
        <a href='".base_url('ihm/Dem_Detail_Activite/'.$row->PTBA_ID)."'>
          <label>&nbsp;&nbsp;Détails</label>
        </a>
      </li>
      </ul>
      </div>";     
      $sub_array[]=$action;
      $data[] = $sub_array;
    }
    $recordsTotal = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebase. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL `getTable`("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);
  }  

  //fonction pour ajouter
  public function create()
  {
    $data=$this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id ='';
    
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }else{

      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }    

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user = $this->getBindParms('user_users.USER_ID , INSTITUTION_ID , IS_SOUS_TUTEL , SOUS_TUTEL_ID', 'user_users JOIN user_affectaion ON user_affectaion.USER_ID=user_users.USER_ID', 'user_users.USER_ID ='.$user_id.'' , 'user_users.USER_ID DESC');
    $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);

    $bind_instution_sous_tutel = $this->getBindParms('SOUS_TUTEL_ID ,DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'INSTITUTION_ID = '.$getuser['INSTITUTION_ID'].'', 'SOUS_TUTEL_ID DESC');
    $data['inst_sous_tutel']= $this->ModelPs->getRequete($callpsreq, $bind_instution_sous_tutel);

    $bind_programme = $this->getBindParms('PROGRAMME_ID ,INTITULE_PROGRAMME', 'inst_institutions_programmes', 'INSTITUTION_ID='.$getuser['INSTITUTION_ID'].'', 'PROGRAMME_ID DESC');
    $data['inst_program']= $this->ModelPs->getRequete($callpsreq, $bind_programme);
    $bind_action = $this->getBindParms('ACTION_ID  ,PROGRAMME_ID,OBJECTIF_ACTION', 'inst_institutions_actions', '1', 'ACTION_ID DESC');
    $data['inst_action']= $this->ModelPs->getRequete($callpsreq, $bind_action);

    //récuperer les codes et intitulés des institutions

    $bind_instit = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID='.$getuser['INSTITUTION_ID'].'', 'INSTITUTION_ID DESC');
    $instit= $this->ModelPs->getRequeteOne($callpsreq, $bind_instit);
    
    $data['INSTITUTION_ID'] = $instit['INSTITUTION_ID'];
    $data['code_instit'] = $instit['CODE_INSTITUTION'];
    $data['descr_instit'] = $instit['DESCRIPTION_INSTITUTION'];

    //Récuperer les divisions fonctionnelles
    $division = $this->getBindParms('DIVISION_ID,CODE_DIVISION,LIBELLE_DIVISION', 'class_fonctionnelle_division', '1', 'CODE_DIVISION ASC');
    $data['get_division'] = $this->ModelPs->getRequete($callpsreq, $division);

    //Sélectionner les motifs de création 
    $bindparams = $this->getBindParms('`MOTIF_ACTIVITE_ID`,`DESCR_MOTIF_ACTIVITE`', 'motif_creation_activite', '1', '`DESCR_MOTIF_ACTIVITE` ASC');
    $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bindparams);

    $data['set_action_id']= null;
    $data['set_code_budg']= null;
    $data['set_group_id']= null;
    $data['set_class_id']= null;

    $data['inst_action'] = array();
    $data['code_Buget'] = array();
    $data['get_class'] = array();
    $data['get_group'] = array();
    return view('App\Modules\ihm\Views\Dem_Activites_Add_View',$data);
  }

  public function insert()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $USER_ID = '';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $CODE_INSTITUTION = $this->request->getPost('CODE_INSTITUTION');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
    $ACTION_ID = $this->request->getPost('ACTION_ID');
    $CODE_NOMENCLATURE_BUDGETAIRE = $this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE');
    $DIVISION_ID = $this->request->getPost('DIVISION_ID');
    $GROUPE_ID = $this->request->getPost('GROUPE_ID');
    $CLASSE_ID = $this->request->getPost('CLASSE_ID');

    $rules = [
      'SOUS_TUTEL_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'PROGRAMME_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'ACTION_ID' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'CODE_NOMENCLATURE_BUDGETAIRE' => [
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ],
      'ACTIVITES' => [
        'rules' => 'required|max_length[2000]|min_length[3]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 2000 '.lang('messages_lang.msg_caracteres').'</font>',
          'min_length' =>'<font style="color:red;size:2px;">'.lang('messages_lang.msg_minim_trois').'</font>'
        ]
      ],
      'RESULTATS_ATTENDUS' => [
        'rules' => 'required|max_length[3000]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 3000 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'UNITE' => [
        'rules' => 'required|max_length[100]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 100 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'COUT_UNITAIRE_BIF' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'QT1' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'QT2' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'QT3' => [
        'rules' => 'required',
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'QT4' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'T1' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'T2' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'T3' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'T4' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'INTITULE_DES_GRANDES_MASSES' => [
        'rules' => 'required|max_length[500]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 500 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'GRANDE_MASSE_BP' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'GRANDE_MASSE_BM1' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'GRANDE_MASSE_BM' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'RESPONSABLE' => [
        'rules' => 'required|max_length[500]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 500 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'ARTICLE_ECONOMIQUE' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'NATURE_ECONOMIQUE' => [
        'rules' => 'required|max_length[50]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'INTITULE_ARTICLE_ECONOMIQUE' => [
        'rules' => 'required|max_length[500]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 500 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'INTITULE_NATURE_ECONOMIQUE' => [
        'rules' => 'required|max_length[500]',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 500 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ],
      'CODES_PROGRAMMATIQUE' => [
        'rules' => 'max_length[27]',
        'errors' => [
          'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 500 '.lang('messages_lang.msg_caracteres').'</font>'
        ]
      ]

    ];

    $FRAIS= $this->request->getPost('FRAIS');

    if ($FRAIS == 2)
    {
      $rules['MOTIF_ACTIVITE_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
        ]
      ];

      $MOTIF_ACTIVITE_ID= $this->request->getPost('MOTIF_ACTIVITE_ID');

      if ($MOTIF_ACTIVITE_ID == 2 || $MOTIF_ACTIVITE_ID == 3)
      {
        $rules['NOM'] = [
          'label' => '',
          'rules' => 'required|max_length[50]',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
            'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
          ]
        ];

        $rules['PRENOM'] = [
          'label' => '',
          'rules' => 'required|max_length[50]',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
            'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
          ]
        ];

        $rules['POSTE'] = [
          'label' => '',
          'rules' => 'required|max_length[50]',
          'errors' => [
            'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
            'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 50 '.lang('messages_lang.msg_caracteres').'</font>'
          ]
        ];
      }
    }
    $this->validation->setRules($rules);
    if($this->validation->withRequest($this->request)->run())
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      
      $MOTIF_ACTIVITE_ID= $this->request->getPost('MOTIF_ACTIVITE_ID');
      $CODE_INSTITUTION = $this->request->getPost('CODE_INSTITUTION');
      $DESCR_INSTITUTION = $this->request->getPost('DESCR_INSTITUTION');
      
      $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
      $ACTION_ID = $this->request->getPost('ACTION_ID');
      
      //récuperer l'id de l'institution

      $bind_instit = $this->getBindParms('INSTITUTION_ID','inst_institutions','CODE_INSTITUTION="'.$CODE_INSTITUTION.'"','CODE_INSTITUTION DESC');
      $bind_instit = str_replace('\\','',$bind_instit);
      $instit= $this->ModelPs->getRequeteOne($callpsreq, $bind_instit);

      $INSTITUTION_ID=$instit['INSTITUTION_ID'];
      /*$INTITULE_PROGRAMME=$prog['INTITULE_PROGRAMME'];
      $OBJECTIF_DU_PROGRAMME=$prog['OBJECTIF_DU_PROGRAMME'];

      //récuperer les codes et intitulés des actions

      $bind_act = $this->getBindParms('ACTION_ID,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION','inst_institutions_actions','ACTION_ID='.$ACTION_ID.'','ACTION_ID DESC');
      $act= $this->ModelPs->getRequeteOne($callpsreq, $bind_act);

      $CODE_ACTION=$act['CODE_ACTION'];
      $LIBELLE_ACTION= str_replace("\\", "", $act['LIBELLE_ACTION']);
      $OBJECTIF_ACTION= str_replace("\\", "", $act['OBJECTIF_ACTION']);*/

      $CODE_NOMENCLATURE_BUDGETAIRE = $this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE');

      $CODES_PROGRAMMATIQUE = $this->request->getPost('CODES_PROGRAMMATIQUE');
      
      $ACTIVITES = $this->request->getPost('ACTIVITES');
      $RESULTATS_ATTENDUS = $this->request->getPost('RESULTATS_ATTENDUS');
      $UNITE = $this->request->getPost('UNITE');
      $COUT_UNITAIRE_BIF = floatval($this->request->getPost('COUT_UNITAIRE_BIF'));
      $QT1 = $this->request->getPost('QT1');
      $QT2 = $this->request->getPost('QT2');
      $QT3 = $this->request->getPost('QT3');
      $QT4 = $this->request->getPost('QT4');
      
      $QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE = floatval($QT1)+floatval($QT2)+floatval($QT3)+floatval($QT4);
      $INTITULE_DES_GRANDES_MASSES = $this->request->getPost('INTITULE_DES_GRANDES_MASSES');
      $GRANDE_MASSE_BP = $this->request->getPost('GRANDE_MASSE_BP');
      $GRANDE_MASSE_BM1 = $this->request->getPost('GRANDE_MASSE_BM1');
      $GRANDE_MASSE_BM = $this->request->getPost('GRANDE_MASSE_BM');
      $RESPONSABLE = $this->request->getPost('RESPONSABLE');  
      
      $T1 = $this->request->getPost('T1');
      $T2 = $this->request->getPost('T2');
      $T3 = $this->request->getPost('T3');
      $T4 = $this->request->getPost('T4');
      $PROGRAMMATION_FINANCIERE_BIF=floatval($T1) + floatval($T2) + floatval($T3) + floatval($T4);

      $MONTANT_RESTANT_T1 = $this->request->getPost('T1');
      $MONTANT_RESTANT_T2 = $this->request->getPost('T2');
      $MONTANT_RESTANT_T3 = $this->request->getPost('T3');
      $MONTANT_RESTANT_T4 = $this->request->getPost('T4');

      //ARTICLE ECONOMIQUE
      $ARTICLE_ECONOMIQUE = $this->request->getPost('ARTICLE_ECONOMIQUE');
      $INTITULE_ARTICLE_ECONOMIQUE = $this->request->getPost('INTITULE_ARTICLE_ECONOMIQUE');

      //NATURE ECONOMIQUE
      $NATURE_ECONOMIQUE = $this->request->getPost('NATURE_ECONOMIQUE');
      $INTITULE_NATURE_ECONOMIQUE = $this->request->getPost('INTITULE_NATURE_ECONOMIQUE');
      
      //DIVISION FONCTIONNELLE
      $DIVISION_ID = $this->request->getPost('DIVISION_ID');
      
      $bind_div_fonc = $this->getBindParms('DIVISION_ID,CODE_DIVISION,LIBELLE_DIVISION','class_fonctionnelle_division','DIVISION_ID='.$DIVISION_ID.'','DIVISION_ID DESC');
      $div_fonc= $this->ModelPs->getRequeteOne($callpsreq, $bind_div_fonc);

      $DIVISION_FONCTIONNELLE=$div_fonc['CODE_DIVISION'];
      $INTITULE_DIVISION_FONCTIONNELLE=$div_fonc['LIBELLE_DIVISION'];
      
      //GROUPE FONCTIONNEL
      $GROUPE_ID = $this->request->getPost('GROUPE_ID');

      $bind_group = $this->getBindParms('GROUPE_ID,CODE_GROUPE,LIBELLE_GROUPE','class_fonctionnelle_groupe','GROUPE_ID='.$GROUPE_ID.'','GROUPE_ID DESC');
      $group= $this->ModelPs->getRequeteOne($callpsreq, $bind_group);

      $GROUPE_FONCTIONNELLE=$group['CODE_GROUPE'];
      $INTITULE_GROUPE_FONCTIONNELLE=$group['LIBELLE_GROUPE'];
      
      //CLASSE FONCTIONNELLE
      $CLASSE_ID = $this->request->getPost('CLASSE_ID');

      $bind_class = $this->getBindParms('CLASSE_ID,CODE_CLASSE,LIBELLE_CLASSE','class_fonctionnelle_classe','CLASSE_ID='.$CLASSE_ID.'','CLASSE_ID DESC');
      $class= $this->ModelPs->getRequeteOne($callpsreq, $bind_class);

      $CLASSE_FONCTIONNELLE=$class['CODE_CLASSE'];
      $INTITULE_CLASSE_FONCTIONNELLE=$class['LIBELLE_CLASSE'];

      $TRAITE=0;
      $IS_NOUVEAU=1;

      $columsinsert="INSTITUTION_ID,PROGRAMME_ID,ACTION_ID,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,CODE_NOMENCLATURE_BUDGETAIRE_ID,ACTIVITES,RESULTATS_ATTENDUS,UNITE,COUT_UNITAIRE_BIF,QT1,QT2,QT3,QT4,T1,T2,T3,T4,INTITULE_DES_GRANDES_MASSES,GRANDE_MASSE_BP,GRANDE_MASSE_BM1,GRANDE_MASSE_BM,RESPONSABLE,CODES_PROGRAMMATIQUE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE, INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE, CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,TRAITE,IS_NOUVEAU,PROGRAMMATION_FINANCIERE_BIF";

      $datatoinsert="".$INSTITUTION_ID.",".$PROGRAMME_ID.",".$ACTION_ID.",'".$QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE."',".$CODE_NOMENCLATURE_BUDGETAIRE.",'".str_replace("'", "\'", $ACTIVITES)."','".str_replace("'", "\'", $RESULTATS_ATTENDUS)."','".$UNITE."','".$COUT_UNITAIRE_BIF."','".$QT1."','".$QT2."','".$QT3."','" .$QT4."','".$T1."','".$T2."','".$T3."','".$T4."','".str_replace("'", "\'", $INTITULE_DES_GRANDES_MASSES)."','".$GRANDE_MASSE_BP."','".$GRANDE_MASSE_BM1."','" .$GRANDE_MASSE_BM."','".$RESPONSABLE."','".$CODES_PROGRAMMATIQUE."','".$ARTICLE_ECONOMIQUE."','".str_replace("'", "\'", $INTITULE_ARTICLE_ECONOMIQUE)."','".$NATURE_ECONOMIQUE."','".str_replace("'", "\'", $INTITULE_NATURE_ECONOMIQUE)."','".$DIVISION_FONCTIONNELLE."','".str_replace("'", "\'", $INTITULE_DIVISION_FONCTIONNELLE)."','".$GROUPE_FONCTIONNELLE."','".str_replace("'", "\'", $INTITULE_GROUPE_FONCTIONNELLE)."','".$CLASSE_FONCTIONNELLE."','".str_replace("'", "\'", $INTITULE_CLASSE_FONCTIONNELLE)."','".$MONTANT_RESTANT_T1."','".$MONTANT_RESTANT_T2."','".$MONTANT_RESTANT_T3."','".$MONTANT_RESTANT_T4."',".$TRAITE.",".$IS_NOUVEAU.",'".$PROGRAMMATION_FINANCIERE_BIF."'";

      $table='ptba';
      $PTBA_ID= $this->save_all_table($table,$columsinsert,$datatoinsert);
      $data=$this->urichk();

      //Table associative
      if(!empty($MOTIF_ACTIVITE_ID))
      {
        if($MOTIF_ACTIVITE_ID == 2 || $MOTIF_ACTIVITE_ID == 3)
        {
          $NOM = $this->request->getPost('NOM');
          $PRENOM = $this->request->getPost('PRENOM');
          $POSTE = $this->request->getPost('POSTE');

          $table2='ptba_motif_activite';
          $columsinsert1="PTBA_ID,MOTIF_ACTIVITE_ID,USER_ID,NOM,PRENOM,POSTE";
          $datatoinsert1="".$PTBA_ID.",".$MOTIF_ACTIVITE_ID.",".$USER_ID.",'".$NOM."','".$PRENOM."','".$POSTE."'";
          $this->save_all_table($table2,$columsinsert1,$datatoinsert1);
        }else{

          $table3='ptba_motif_activite';
          $columsinsert2="PTBA_ID,MOTIF_ACTIVITE_ID,USER_ID";
          $datatoinsert2="".$PTBA_ID.",".$MOTIF_ACTIVITE_ID.",".$USER_ID."";
          $this->save_all_table($table3,$columsinsert2,$datatoinsert2);
        }        
      }     

      $data = [
        'message' => ''.lang('messages_lang.Enregistrer_succes_msg').''
      ];
      session()->setFlashdata('alert', $data);

      return redirect('ihm/Liste_Activites');
    }
    else
    {
      $data=$this->urichk();
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $session  = \Config\Services::session();
      $user_id ='';
      
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
      }else{

        return redirect('Login_Ptba');
      }
      

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      $user = $this->getBindParms('USER_ID , INSTITUTION_ID , IS_SOUS_TUTEL , SOUS_TUTEL_ID', 'user_users', 'USER_ID ='.$user_id.'' , 'USER_ID DESC');
      $getuser = $this->ModelPs->getRequeteOne($callpsreq, $user);

      //récuperer les sous titres à partir des institutiions 
      $bind_instution_sous_tutel = $this->getBindParms('SOUS_TUTEL_ID ,DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'INSTITUTION_ID='.$getuser['INSTITUTION_ID'].'', 'SOUS_TUTEL_ID DESC');
      $data['inst_sous_tutel']= $this->ModelPs->getRequete($callpsreq, $bind_instution_sous_tutel);

      $bind_programme = $this->getBindParms('PROGRAMME_ID ,INTITULE_PROGRAMME', 'inst_institutions_programmes', 'INSTITUTION_ID='.$getuser['INSTITUTION_ID'].'', 'PROGRAMME_ID DESC');
      $data['inst_program']= $this->ModelPs->getRequete($callpsreq, $bind_programme);

      //récuperer les actions à partir des programmes
      if(!empty($PROGRAMME_ID))
      {
        $bind_action = $this->getBindParms('ACTION_ID  , LIBELLE_ACTION', 'inst_institutions_actions', 'PROGRAMME_ID ='.$PROGRAMME_ID , 'ACTION_ID ASC');
        $data['inst_action']= $this->ModelPs->getRequete($callpsreq, $bind_action);

      }else{

         $data['inst_action'] = array();
      }
      
      //récuperer les codes budgétaires
      if(!empty($SOUS_TUTEL_ID))
      {
        $getcodeSousTutel = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL', 'inst_institutions_sous_tutel', ' INSTITUTION_ID ='.$getuser['INSTITUTION_ID'].' AND SOUS_TUTEL_ID = '.$SOUS_TUTEL_ID.'', 'CODE_SOUS_TUTEL  ASC');
        $code_SouTutel= $this->ModelPs->getRequeteOne($callpsreq, $getcodeSousTutel);
        $CODEBUDGET = $CODE_INSTITUTION.'00'.$code_SouTutel['CODE_SOUS_TUTEL'];
        //print_r($CODEBUDGET);exit();

        //Le code budgetaire
        $getcodeBudget = "SELECT DISTINCT CODE_NOMENCLATURE_BUDGETAIRE FROM ptba WHERE CODE_NOMENCLATURE_BUDGETAIRE LIKE '".$CODEBUDGET."%'";
        
        $getcodeBudget = 'CALL `getTable`("'.$getcodeBudget.'");';

        $data['code_Buget']= $this->ModelPs->getRequete($getcodeBudget);

      }else{

        $data['code_Buget'] = array();
      }

      //récuperer les codes et intitulés des institutions
      $bind_instit = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID='.$getuser['INSTITUTION_ID'].'', 'INSTITUTION_ID DESC');
      $instit= $this->ModelPs->getRequeteOne($callpsreq, $bind_instit);
      
      $data['INSTITUTION_ID'] = $instit['INSTITUTION_ID'];
      $data['code_instit'] = $instit['CODE_INSTITUTION'];
      $data['descr_instit'] = $instit['DESCRIPTION_INSTITUTION'];

      //Récuperer les divisions fonctionnelles
      $division = $this->getBindParms('DIVISION_ID,CODE_DIVISION,LIBELLE_DIVISION', 'class_fonctionnelle_division', '1', 'CODE_DIVISION ASC');
      $data['get_division'] = $this->ModelPs->getRequete($callpsreq, $division);


      //Récuperer les groupes dépendants de la division
      if(!empty($DIVISION_ID))
      {
        $group = $this->getBindParms('GROUPE_ID,DIVISION_ID,CODE_GROUPE,LIBELLE_GROUPE', 'class_fonctionnelle_groupe','DIVISION_ID='.$DIVISION_ID, 'CODE_GROUPE ASC');
        $data['get_group'] = $this->ModelPs->getRequete($callpsreq, $group);

      }else{

        $data['get_group'] = array();
      }
      

      //Récuperer les classes dépendants d'un groupe
      if(!empty($GROUPE_ID))
      {
        $classe = $this->getBindParms('CLASSE_ID,GROUPE_ID,CODE_CLASSE,LIBELLE_CLASSE', 'class_fonctionnelle_classe', 'GROUPE_ID='.$GROUPE_ID,'CODE_CLASSE ASC');

        $data['get_class'] = $this->ModelPs->getRequete($callpsreq, $classe);
      }else{

        $data['get_class'] = array();
      }
      

      //Sélectionner les motifs de création 
      $bindparams = $this->getBindParms('`MOTIF_ACTIVITE_ID`,`DESCR_MOTIF_ACTIVITE`', 'motif_creation_activite', '1', '`DESCR_MOTIF_ACTIVITE` ASC');
      $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bindparams);

      $data['set_action_id']= $ACTION_ID;
      $data['set_code_budg']= $CODE_NOMENCLATURE_BUDGETAIRE;
      $data['set_group_id']= $GROUPE_ID;
      $data['set_class_id']= $CLASSE_ID;
      return view('App\Modules\ihm\Views\Dem_Activites_Add_View',$data);
    }
  } 

  public function save($columsinsert,$datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $table='ptba';
    $bindparms=[$table,$columsinsert,$datacolumsinsert];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $this->ModelPs->createUpdateDelete($insertReqAgence,$bindparms);
  }

  /* Debut Gestion insertion */
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
  /* Fin Gestion insertion */

  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    $db = db_connect();
    $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    return $bindparams;
  }

  //Sélectionner les programmes à partir des sous tutelles
  function get_prog()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $INSTITUTION_ID =$this->request->getPost('INSTITUTION_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    
    //Sélectionner les programmes
    $bindprog = $this->getBindParms('PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME,INSTITUTION_ID','inst_institutions_programmes', 'INSTITUTION_ID='.$INSTITUTION_ID,'`INTITULE_PROGRAMME` ASC');
    $prog = $this->ModelPs->getRequete($callpsreq, $bindprog);
    
    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';

    if(!empty($prog) )
    {
      foreach($prog as $key)
      {   
        if($key->CODE_PROGRAMME==set_value('CODE_PROGRAMME'))
        {
          $html.= "<option value='".$key->CODE_PROGRAMME."' selected>".$key->CODE_PROGRAMME."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->INTITULE_PROGRAMME."</option>";
        }
        else
        {
          $html.= "<option value='".$key->CODE_PROGRAMME."'>".$key->CODE_PROGRAMME."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->INTITULE_PROGRAMME."</option>";
        }

      }
    }
    else
    {
      $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
    }
    $output = array('status' => TRUE , 'html' => $html);
    return $this->response->setJSON($output);
  }

  //Sélectionner les actions à partir des programmes
  function get_action()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $CODE_PROGRAMME =$this->request->getPost('CODE_PROGRAMME');

    $callpsreq = "CALL `getRequete`(?,?,?,?);";


    //récuperer les programmes 
    $get_prog = "SELECT DISTINCT PROGRAMME_ID FROM inst_institutions_programmes WHERE CODE_PROGRAMME LIKE '%".$CODE_PROGRAMME."%' ORDER BY PROGRAMME_ID ";
    $program = 'CALL `getTable`("'.$get_prog.'")';

    $prog = $this->ModelPs->getRequeteOne( $program);

    $get_action = "SELECT LIBELLE_ACTION, CODE_ACTION FROM inst_institutions_actions WHERE PROGRAMME_ID=".$prog['PROGRAMME_ID']." ORDER BY LIBELLE_ACTION ";

    $details='CALL `getTable`("'.$get_action.'")';
    $action = $this->ModelPs->getRequete( $details);

    $html='<option value="">-'.lang('messages_lang.selection_message').'-</option>';

    if(!empty($action) )
    {
      foreach($action as $key)
      {   
        if($key->CODE_ACTION==set_value('CODE_ACTION'))
        {
          $html.= "<option value='".$key->CODE_ACTION."' selected>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
        }
        else
        {
          $html.= "<option value='".$key->CODE_ACTION."'>".$key->CODE_ACTION."&nbsp;&nbsp-&nbsp;&nbsp;".$key->LIBELLE_ACTION."</option>";
        }

      }
    }else{

      $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
    }

    $output = array('status' => TRUE , 'html' => $html);
    return $this->response->setJSON($output);
  }

  //LES SELECTS POUR LA PAGE DE CREATION D UNE NOUVELLE ACTIVITE
  public function create_get_code()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $CODE_INSTITUTION = $this->request->getPost('CODE_INSTITUTION');
    $set_code_budg = $this->request->getPost('set_code_budg');
   
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $getcodeSousTutel = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL', 'inst_institutions_sous_tutel', ' INSTITUTION_ID ='.$INSTITUTION_ID.' AND SOUS_TUTEL_ID = '.$SOUS_TUTEL_ID.'', 'CODE_SOUS_TUTEL  ASC');
    $code_SouTutel= $this->ModelPs->getRequeteOne($callpsreq, $getcodeSousTutel);
    $CODEBUDGET = $CODE_INSTITUTION.'00'.$code_SouTutel['CODE_SOUS_TUTEL'];
     //print_r($CODEBUDGET);exit();

    //Le code budgetaire
    $getcodeBudget = "SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID, CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE LIKE '".$CODEBUDGET."%'";   

    $getcodeBudget = 'CALL `getTable`("'.$getcodeBudget.'");';
    $code_Buget= $this->ModelPs->getRequete($getcodeBudget);
    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($code_Buget as $key)
    {
      $html.='<option value="'.$key->CODE_NOMENCLATURE_BUDGETAIRE_ID.'">'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'</option>';   
    }

    $output = array("codeBudgetaire" => $html);
    return $this->response->setJSON($output);
  }

  public function create_get_action()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $session  = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $PROGRAMME_ID =$this->request->getPost('PROGRAMME_ID');
    $set_ACTION_ID =$this->request->getPost('set_ACTION_ID');

    if(!empty($PROGRAMME_ID))
    {
      $bind_action = $this->getBindParms('ACTION_ID  , LIBELLE_ACTION', 'inst_institutions_actions', 'PROGRAMME_ID ='.$PROGRAMME_ID , 'ACTION_ID ASC');
      $actions= $this->ModelPs->getRequete($callpsreq, $bind_action);
      $tutel="<option value=''>".lang('messages_lang.selection_message')."</option>";
      foreach ($actions as $tut)
      {
        $tutel.= "<option value ='".$tut->ACTION_ID."' >".str_replace("\\", "", $tut->LIBELLE_ACTION)."</option>";
      }

    }else{
      $tutel="";
    }
    $output = array("tutel"=>$tutel);
    return $this->response->setJSON($output);
  }

  //Sélectionner les groupes à partir des divisions
  function get_groupes()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $DIVISION_ID =$this->request->getPost('DIVISION_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $division = $this->getBindParms('GROUPE_ID,DIVISION_ID,CODE_GROUPE,LIBELLE_GROUPE', 'class_fonctionnelle_groupe', 'DIVISION_ID='.$DIVISION_ID, 'CODE_GROUPE ASC');
    $get_division = $this->ModelPs->getRequete($callpsreq, $division);
    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';

    if(!empty($get_division) )
    {
      foreach($get_division as $key)
      {
        $html.= "<option value='".$key->GROUPE_ID."'>".$key->CODE_GROUPE." - ".$key->LIBELLE_GROUPE."</option>";  
      }
    }
    $output = array('status' => TRUE , 'div' => $html);
    return $this->response->setJSON($output);
  }

  //Sélectionner les classes à partir des groupes
  function get_classes()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $GROUPE_ID =$this->request->getPost('GROUPE_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $classe = $this->getBindParms('CLASSE_ID,GROUPE_ID,CODE_CLASSE,LIBELLE_CLASSE', 'class_fonctionnelle_classe', 'GROUPE_ID='.$GROUPE_ID,'CODE_CLASSE ASC');

    $get_class = $this->ModelPs->getRequete($callpsreq, $classe);
    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';

    if(!empty($get_class))
    {
      foreach($get_class as $key)
      {
        $html.= "<option value='".$key->CLASSE_ID."'>".$key->CODE_CLASSE." - ".$key->LIBELLE_CLASSE."</option>";
      }
    }
    $output = array('status' => TRUE , 'classes' => $html,'GROUPE_ID'=>$GROUPE_ID);
        return $this->response->setJSON($output);
  }
}	
?>