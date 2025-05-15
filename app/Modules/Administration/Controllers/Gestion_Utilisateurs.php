<?php
/**Nderagakura Alain Charbel
*Titre:CRUD DE GESTION DES utilisateurs
*Numero de telephone: (+257) 62 00 35 22
*WhatsApp: (+257) 62 00 35 22
*Email: charbel@mediabox.bi
*Date: 29 Août,2023
**/

//Modifié par
/**RUGAMBA Jean Vainqueur
*Numero de telephone: (+257) 62 00 35 22
*WhatsApp: (+257) 62 00 35 22
*Email: jean.vainqueur@mediabox.bi
*Date: 24 Janvier,2024
**/


namespace  App\Modules\Administration\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

class Gestion_Utilisateurs extends BaseController
{
	protected $session;

	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->library = new CodePlayHelper();
		$this->validation = \Config\Services::validation();
		$this->session = \Config\Services::session();
	}

	public function index($value='')
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		$data=$this->urichk();
		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		return view('App\Modules\Administration\Views\Utilisateurs_List_View',$data);   
	}

	//fonction pour affichage d'une liste
	public function listing($value = 0)
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column=array(1,'NOM','PRENOM','EMAIL','TELEPHONE1',1,'user_profil.PROFIL_DESCR','IS_ACTIVE',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].'  '. $_POST['order']['0']['dir'] : ' ORDER BY NOM ASC';
		$search = !empty($_POST['search']['value']) ? (' AND (NOM LIKE "%'.$var_search.'%" OR PRENOM LIKE "%'.$var_search.'%" OR EMAIL LIKE "%'.$var_search.'%" OR TELEPHONE1 LIKE "%'.$var_search.'%" OR user_profil.PROFIL_DESCR LIKE "%'.$var_search.'%")') : '';

		$conditions=$critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
		$conditionsfilter = $critaire . ' ' . $search . ' ' . $group;
		$requetedebase='SELECT USER_ID,NOM,PRENOM,EMAIL,TELEPHONE1,TELEPHONE2,DATE_FORMAT(DATE_ACTIVATION,"%d-%m-%Y %H:%i:%s") AS DATE_ACTIVATION_FR,DATE_FORMAT(DATE_INSERTION,"%d-%m-%Y %H:%i:%s") AS DATE_INSERTION_FR,user_profil.PROFIL_DESCR,user_users.IS_ACTIVE FROM user_users join user_profil on user_users.PROFIL_ID=user_profil.PROFIL_ID WHERE 1';
		$requetedebases=$requetedebase .' '. $conditions;
		$requetedebasefilter=$requetedebase.' '.$conditionsfilter;
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$u=1;
		foreach($fetch_actions as $row)
		{
			$count_instit = "SELECT COUNT(USER_ID) AS nbre FROM user_affectaion WHERE USER_ID=".$row->USER_ID;
			$count_instit = 'CALL `getTable`("'.$count_instit.'");';
      $nbre_instit = $this->ModelPs->getRequeteOne($count_instit);

			$sub_array=array();
			$sub_array[]= $u++;
			$sub_array[]=$row->NOM;
			$sub_array[]=$row->PRENOM;
			$sub_array[]=$row->EMAIL;
			$sub_array[]=$row->TELEPHONE1.'<br>'.$row->TELEPHONE2;
			$point="<a href='javascript:void(0)'  class='btn btn-dark btn-md' onclick='get_instit(".$row->USER_ID.")'>".$nbre_instit['nbre']."</a>";

			$sub_array[]=$point;
			$sub_array[]=$row->PROFIL_DESCR;
			$stat ='';
			if($row->IS_ACTIVE==0)
			{
				$stat = '<center><span class=" fa fa-close badge badge-pill badge-danger" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title="'.lang('messages_lang.title_desactiver').'">&nbsp;</span></center>';
			}
			else
			{
				$stat = '<center><span class=" fa fa-check badge badge-pill badge-success" style="font-size:20px;font-weight: bold;color: white;" data-toggle="tooltip" title="'.lang('messages_lang.title_activer').'">&nbsp;</span></center>';
			}
			$sub_array[]=$stat;

			if($row->IS_ACTIVE==1)
			{
				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

				$action .="<li>
				<a href='".base_url("Administration/Gestion_Utilisateurs/getOne/".md5($row->USER_ID))."'><label>&nbsp;&nbsp;".lang('messages_lang.modifier')."</label></a>
				</li>
				<li>
				<a href='javascript:void(0)' onclick='show_modal(".$row->USER_ID.")' title='désactiver' ><label>&nbsp;&nbsp;<font color='red'>".lang('messages_lang.desactive_action')."</font></label></a>

				</li>
				<div style='display:none;' id='message".$row->USER_ID."'>
				<center>
          <h5><strong>".lang('messages_lang.confimatation_desactive_action')."<br><center><font color='green'>".$row->NOM."&nbsp;&nbsp;".$row->PRENOM."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
          </h5>
        </center>
        </div>
        <div style='display:none;' id='footer".$row->USER_ID."'>
          <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
                            ".lang('messages_lang.quiter_action')."
                          </button>
          <a href='".base_url("Administration/Gestion_Utilisateurs/is_active/".$row->USER_ID)."' class='btn btn-danger btn-md'>".lang('messages_lang.desactive_action')."</a>
        </div>";
			}
			else
			{
				$action = '<div class="dropdown" style="color:#fff;">
				<a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Options  <span class="caret"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-left">';

				$action .="<li>
				<a href='".base_url("Administration/Gestion_Utilisateurs/getOne/".md5($row->USER_ID))."'><label>&nbsp;&nbsp;Modifier</label></a>
				</li>
				<li>
				<a href='javascript:void(0)' onclick='show_modal(".$row->USER_ID.")' title='désactiver' ><label>&nbsp;&nbsp;<font color='green'>".lang('messages_lang.active_action')."</font></label></a>

				</li>
				<div style='display:none;' id='message".$row->USER_ID."'>
				<center>
          <h5><strong>".lang('messages_lang.confimatation_active_action')."<br><center><font color='green'>".$row->NOM."&nbsp;&nbsp;".$row->PRENOM."</font> ? </center></strong><br><b style='background-color:prink;color:green;'></b>
          </h5>
        </center></div>
        <div style='display:none;' id='footer".$row->USER_ID."'>
          <button class='btn btn-primary btn-md' data-dismiss='modal' style='background-color: #a80;'>
                            ".lang('messages_lang.quiter_action')."
                          </button>
          <a href='".base_url("Administration/Gestion_Utilisateurs/is_active/".$row->USER_ID)."' class='btn btn-success btn-md'>".lang('messages_lang.active_action')."</a>
        </div>";
			}

			$sub_array[]=$action;
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

	//fonction pour affichage d'une liste
	public function detail_instit()
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$affect_user_id = $this->request->getPost('affect_user_id');

		$var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
		$var_search = str_replace("'", "\'", $var_search);
		$group = "";
		$critaire = "";
		$limit = 'LIMIT 0,1000';
		if($_POST['length'] != -1)
		{
			$limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
		}

		$order_by = '';
		$order_column=array(1,'inst.CODE_INSTITUTION','inst.DESCRIPTION_INSTITUTION',1);
		$order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY inst.DESCRIPTION_INSTITUTION  ASC';

		$search = !empty($_POST['search']['value']) ? (' AND (inst.CODE_INSTITUTION LIKE "%'.$var_search.'%" OR inst.DESCRIPTION_INSTITUTION LIKE "%'.$var_search.'%")') : '';

		$conditions=$critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
		$conditionsfilter = $critaire . ' ' . $search . ' ' . $group;
		$requetedebase='SELECT USER_AFFECTAION,USER_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION FROM user_affectaion aff JOIN inst_institutions inst ON aff.INSTITUTION_ID=inst.INSTITUTION_ID WHERE USER_ID='.$affect_user_id;
		$requetedebases=$requetedebase .' '. $conditions;
		$requetedebasefilter=$requetedebase.' '.$conditionsfilter;
		$query_secondaire = "CALL `getTable`('".$requetedebases."');";
		$fetch_actions = $this->ModelPs->datatable($query_secondaire);
		$data = array();
		$u=1;
		foreach($fetch_actions as $row)
		{
			$sub_array=array();
			$sub_array[]= $u++;
			$sub_array[]=$row->CODE_INSTITUTION;
			$sub_array[]=$row->DESCRIPTION_INSTITUTION;
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
	
	//fonction pour l'activation/désactivation
	function is_active($USER_ID)
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_user = $this->getBindParms('USER_ID ,	NOM , IS_ACTIVE', 'user_users', 'USER_ID='.$USER_ID,'USER_ID ASC');
		$users= $this->ModelPs->getRequeteOne($callpsreq, $bind_user);

		if($users['IS_ACTIVE']==0)
		{
			$updateTable='user_users';
			$critere = "USER_ID=".$USER_ID;
			$datatoupdate= 'IS_ACTIVE=1';
			$bindparams =[$updateTable,$datatoupdate,$critere];
			$insertRequete = 'CALL `updateData`(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
			$data = ['message' => ''.lang('messages_lang.labelle_et_mod_question_succes').''];
			session()->setFlashdata('alert', $data);
			return redirect('Administration/Gestion_Utilisateurs');
		}
		elseif($users['IS_ACTIVE']==1)
		{
			$updateTable='user_users';
			$critere = "USER_ID=".$USER_ID;
			$datatoupdate= 'IS_ACTIVE=0';
			$bindparams =[$updateTable,$datatoupdate,$critere];
			$insertRequete = 'CALL `updateData`(?,?,?);';
			$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
			$data=['message' => ''.lang('messages_lang.labelle_et_mod_question_succes_d').''];
			session()->setFlashdata('alert', $data);
			return redirect('Administration/Gestion_Utilisateurs');
		}
	}

 	//Fonction pour afficher le formulaire d'insertion
	public function ajout($value='')
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data=$this->urichk();
		$callpsreq="CALL `getRequete`(?,?,?,?);";
		$bind_profil=$this->getBindParms('PROFIL_ID,PROFIL_DESCR','user_profil','IS_ACTIVE=1','PROFIL_DESCR ASC');
		$data['profil']= $this->ModelPs->getRequete($callpsreq, $bind_profil);
		$bind_institution = $this->getBindParms('INSTITUTION_ID , DESCRIPTION_INSTITUTION', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
		$data['institution']= $this->ModelPs->getRequete($callpsreq, $bind_institution);
		$bind_tutel = $this->getBindParms('SOUS_TUTEL_ID , DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', '1', 'DESCRIPTION_SOUS_TUTEL ASC');
		$data['tutel']= $this->ModelPs->getRequete($callpsreq, $bind_tutel);

		//recuperer les données a mettre dans cart
		$USER_ID =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		$bind_chart = $this->getBindParms('USER_ID_TEMPO,inst.DESCRIPTION_INSTITUTION,user_profil.PROFIL_DESCR','user_users_tempo join user_profil on user_profil.PROFIL_ID=user_users_tempo.PROFIL_ID_TEMPO join inst_institutions inst on inst.INSTITUTION_ID=user_users_tempo.INSTITUTION_ID_TEMPO','USER_ID='.$USER_ID,'1');
		$data['chart']= $this->ModelPs->getRequete($callpsreq, $bind_chart);

		// selectionner les identifiant se trouvant dans cart
		$bind_chart_name = $this->getBindParms('USER_ID_TEMPO,TEMPO_NOM,TEMPO_PRENOM,TEMPO_EMAIL,TEMPO_TELEPHONE1,TEMPO_TELEPHONE2,PROFIL_ID_TEMPO,PROFIL_DESCR','user_users_tempo JOIN user_profil ON user_profil.PROFIL_ID=user_users_tempo.PROFIL_ID_TEMPO','USER_ID='.$USER_ID,'USER_ID_TEMPO DESC');
		$tempo= $this->ModelPs->getRequeteOne($callpsreq, $bind_chart_name);
		$data['tempo']=$tempo;

		return view('App\Modules\Administration\Views\Utilisateurs_Add_View',$data);   
	}

	//Fonction pour afficher le formulaire d'insertion des infos perso
	public function insert_tab1()
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$db = db_connect();

		$NOM = $this->request->getPost('NOM');
		$PRENOM = $this->request->getPost('PRENOM');
		$EMAIL = $this->request->getPost('EMAIL');
		$PASSWORD = $this->request->getPost('PASSWORD');
		$TELEPHONE1 = $this->request->getPost('TELEPHONE1');
		$TELEPHONE2 = $this->request->getPost('TELEPHONE2');
		$PROFIL_ID = $this->request->getPost('PROFIL_ID');
		$NOM=str_replace("'","\'",$NOM);
		$PRENOM=str_replace("'","\'",$PRENOM);

		//Vérification de l'addresse mail
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind_email = $this->getBindParms('EMAIL','user_users','EMAIL="'.$EMAIL.'"','EMAIL ASC');
    $bind_email = str_replace('\\','',$bind_email);
		$verify_mail = $this->ModelPs->getRequeteOne($callpsreq, $bind_email);

		if(empty($verify_mail))
		{
			$columsinsert="TEMPO_NOM,TEMPO_PRENOM,TEMPO_EMAIL,TEMPO_PASSWORD,TEMPO_TELEPHONE1,TEMPO_TELEPHONE2,PROFIL_ID_TEMPO,USER_ID";
			$datatoinsert="'".$NOM."','".$PRENOM."','".$EMAIL."','".$PASSWORD."','".$TELEPHONE1."','".$TELEPHONE2."',".$PROFIL_ID.",".$user_id;

	    $table='user_users_tempo';
	    $this->save_all_table($table,$columsinsert,$datatoinsert);

	    $callpsreq = "CALL `getRequete`(?,?,?,?);";
	    $bind_prof = $this->getBindParms('PROFIL_ID,PROFIL_NIVEAU_ID','user_profil','PROFIL_ID='.$PROFIL_ID,'PROFIL_ID ASC');
			$prof = $this->ModelPs->getRequeteOne($callpsreq, $bind_prof);

			if($prof['PROFIL_NIVEAU_ID'] == 1)
			{
				$bind_instit = $this->getBindParms('INSTITUTION_ID , DESCRIPTION_INSTITUTION', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
				$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
			}
			elseif($prof['PROFIL_NIVEAU_ID'] == 2)
			{
				$bind_instit = $this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID NOT IN(1,2,3,5,6,12)', 'DESCRIPTION_INSTITUTION ASC');
				$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
			}
			elseif($prof['PROFIL_NIVEAU_ID'] == 3)
			{
				$bind_instit = $this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID IN(12)', 'DESCRIPTION_INSTITUTION ASC');
				$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
				if($prof['PROFIL_ID']==9 || $prof['PROFIL_ID']==10)
				{
					$bind_instit = $this->getBindParms('INSTITUTION_ID , DESCRIPTION_INSTITUTION', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
					$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
				}
			}
			elseif($prof['PROFIL_NIVEAU_ID'] == 4)
			{
				$bind_instit = $this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID IN(1,2,3,5,6)', 'DESCRIPTION_INSTITUTION ASC');
				$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
			}

			$select_inst="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
			foreach ($instit as $key)
			{
				$select_inst.= "<option value ='".$key->INSTITUTION_ID."'>".$key->DESCRIPTION_INSTITUTION."</option>";
			}

			$tutel="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";

    	$output = array('status' =>true,'select_inst'=>$select_inst,'tutel'=>$tutel,'niv'=>$prof['PROFIL_NIVEAU_ID'],'PROFIL_ID'=>$prof['PROFIL_ID'],'NOM'=>$NOM,'PRENOM'=>$PRENOM,'EMAIL'=>$EMAIL,'TELEPHONE1'=>$TELEPHONE1,'TELEPHONE2'=>$TELEPHONE2,'PROFIL_ID1'=>$PROFIL_ID);
			return $this->response->setJSON($output);
		}
		else
		{
			$output = array('status' =>false);
			return $this->response->setJSON($output);
		}	
	}
	
	//fonction pour filtrer les niveaux d'intervention selon les profils
	public function get_prof_niv()
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$PROFIL_ID = $this->request->getPost('PROFIL_ID');

		$bind_prof = $this->getBindParms('PROFIL_ID,PROFIL_NIVEAU_ID','user_profil','PROFIL_ID='.$PROFIL_ID,'PROFIL_ID ASC');
		$prof = $this->ModelPs->getRequeteOne($callpsreq, $bind_prof);

		if($prof['PROFIL_NIVEAU_ID'] == 1)
		{
			$bind_instit = $this->getBindParms('INSTITUTION_ID , DESCRIPTION_INSTITUTION', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
			$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
		}
		elseif($prof['PROFIL_NIVEAU_ID'] == 2)
		{
			$bind_instit = $this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID NOT IN(1,2,3,5,6,12)', 'DESCRIPTION_INSTITUTION ASC');
			$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
		}
		elseif($prof['PROFIL_NIVEAU_ID'] == 3)
		{
			$bind_instit = $this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID IN(12)', 'DESCRIPTION_INSTITUTION ASC');
			$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
			if($prof['PROFIL_ID']==9 || $prof['PROFIL_ID']==10)
			{
				$bind_instit = $this->getBindParms('INSTITUTION_ID , DESCRIPTION_INSTITUTION', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
				$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
			}
		}
		elseif($prof['PROFIL_NIVEAU_ID'] == 4)
		{
			$bind_instit = $this->getBindParms('INSTITUTION_ID,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID IN(1,2,3,5,6)', 'DESCRIPTION_INSTITUTION ASC');
			$instit = $this->ModelPs->getRequete($callpsreq, $bind_instit);
		}

		$select_inst="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($instit as $key)
		{
			$select_inst.= "<option value ='".$key->INSTITUTION_ID."'>".$key->DESCRIPTION_INSTITUTION."</option>";
		}

		$tutel = "<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		$output = array("select_inst"=>$select_inst,"select_tut"=>$tutel,"niv"=>$prof['PROFIL_NIVEAU_ID'],'PROFIL_ID'=>$prof['PROFIL_ID']);
		return $this->response->setJSON($output);
	}

	//fuction pour trouver les sous tutelle
	public function get_tutel()
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$IS_SOUS_TUTEL=$this->request->getPost('IS_SOUS_TUTEL');
		if($IS_SOUS_TUTEL==1)
		{
			$bind_tutel=$this->getBindParms('SOUS_TUTEL_ID , DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'INSTITUTION_ID='.$INSTITUTION_ID, 'SOUS_TUTEL_ID ASC');
			$sous_tutel= $this->ModelPs->getRequete($callpsreq, $bind_tutel);
			$tutel="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
			foreach ($sous_tutel as $tut)
			{
				$tutel.= "<option value ='".$tut->SOUS_TUTEL_ID."'>".$tut->DESCRIPTION_SOUS_TUTEL."</option>";
			}
		}
		else
		{
			$tutel="";
		}

		$output = array("tutel"=>$tutel);
		return $this->response->setJSON($output);
	}

	//fonction pour trouver le niveau de visualisation
	public function get_visualisation()
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$PROFIL_ID=$this->request->getPost('PROFIL_ID');
		if(!empty($PROFIL_ID))
		{
			$bind_visualisation=$this->getBindParms('NIVEAU_VISUALISATION_ID', 'user_profil', 'PROFIL_ID='.$PROFIL_ID, '1');
			$visualisation_id= $this->ModelPs->getRequeteOne($callpsreq, $bind_visualisation);
			$visual=$visualisation_id['NIVEAU_VISUALISATION_ID'];
			$output = array("visualisation_id"=>$visual);
			return $this->response->setJSON($output);
		}
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$columnselect=str_replace("\'", "'", $columnselect);
		$table=str_replace("\'", "'", $table);
		$where=str_replace("\'", "'", $where);
		$orderby=str_replace("\'", "'", $orderby);
		$bindparams=[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		$bindparams=str_replace('\"', '"', $bindparams);
		return $bindparams;
	}

	//fonction pour l'insertion dans la table des users et des affectation
	public function insert()
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$db = db_connect();
		$rules = [
			'NOM' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'PRENOM' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'EMAIL' => [
				'rules' => 'required|is_unique[user_users.EMAIL]',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>',
					'is_unique' => '<font style="color:red;size:2px;">L\'email éxiste déjà dans la base</font>'
				]
			],
			'TELEPHONE1' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'PASSWORD' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
		];
		$this->validation->setRules($rules);

		if($this->validation->withRequest($this->request)->run())
		{
			$callpsreq = "CALL `getRequete`(?,?,?,?);";

			//inserer directement si l'utilisateur est dans tous institution
			$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

			//selectionner le niveau de visualisation dans profil pour voir les institutions d'affection
			$PROFIL = $this->request->getPost('PROFIL_ID');
			$get_visualisation=$this->getBindParms('NIVEAU_VISUALISATION_ID','user_profil','PROFIL_ID='.$PROFIL,'1');
			$visualisation=$this->ModelPs->getRequeteOne($callpsreq,$get_visualisation);
			$visualisation_id=$visualisation['NIVEAU_VISUALISATION_ID'];
			if($INSTITUTION_ID==12 && $PROFIL!=9 && $PROFIL!=10)
			{
				$ID_USER=session()->get("SESSION_SUIVIE_PTBA_USER_ID");
				$NOM = $this->request->getPost('NOM');
				$PRENOM = $this->request->getPost('PRENOM');
				$EMAIL = $this->request->getPost('EMAIL');
				$PASSWORD=$this->request->getPost('PASSWORD');
				//print_r($PASSWORD);exit();
				$USER_NAME = $this->request->getPost('EMAIL');
				$TELEPHONE1 = $this->request->getPost('TELEPHONE1');
				$TELEPHONE2 = $this->request->getPost('TELEPHONE2');
				$PROFIL_ID = $this->request->getPost('PROFIL_ID');
				//$PASSWORD=$this->library->generate_password(5);
				$IS_CONNECTED=0;
				$IS_ACTIVE=1;
				$DATE_ACTIVATION=date('Y-m-d h:i');
				$DATE_INSERTION=date('Y-m-d h:i');
				$NOM=str_replace("'","\'",$NOM);
				$PRENOM=str_replace("'","\'",$PRENOM);
				
				$insertIntoTable='user_users';
				$columsinsert='NOM,PRENOM,EMAIL,USER_NAME,TELEPHONE1,TELEPHONE2,PASSWORD,PROFIL_ID,IS_CONNECTED,IS_ACTIVE,DATE_ACTIVATION,DATE_INSERTION,REGISTER_USER_ID';
				$datatoinsert_user = "'" . $NOM . "','" . $PRENOM . "','" . $EMAIL . "','" . $USER_NAME . "','" . $TELEPHONE1 . "','" . $TELEPHONE2 . "','" . md5($PASSWORD) . "'," . $PROFIL_ID . "," . $IS_CONNECTED . "," . $IS_ACTIVE . ",'" . $DATE_ACTIVATION . "','" . $DATE_INSERTION."',".$ID_USER;
				$this->save_all_table($insertIntoTable,$columsinsert,$datatoinsert_user);
				/*Recuperer le dernier USER_ID*/
				$dernier_user = $this->getBindParms('USER_ID', 'user_users', '1', 'USER_ID DESC LIMIT 1');
				$dernier= $this->ModelPs->getRequeteOne($callpsreq, $dernier_user);
				$USER_ID_AFF=$dernier['USER_ID'];
				if ($visualisation_id==1)
				{
					// recuperer toutes les institutions
					$inst = $this->getBindParms('INSTITUTION_ID', 'inst_institutions', '1', 'INSTITUTION_ID DESC');
					$get_inst= $this->ModelPs->getRequete($callpsreq, $inst);
					foreach($get_inst as $value)
					{
						$insertIntoUserAffectation='user_affectaion';
						$columsinsertaffect='USER_ID,INSTITUTION_ID';
						$datatoinsert_affect = "" . $USER_ID_AFF . "," . $value->INSTITUTION_ID;
						$this->save_all_table($insertIntoUserAffectation,$columsinsertaffect,$datatoinsert_affect);
					}
				}
				else
				{
					$insertIntoUserAffectation='user_affectaion';
					$columsinsertaffect='USER_ID,INSTITUTION_ID';
					$datatoinsert_affect = "" . $USER_ID_AFF . "," . $INSTITUTION_ID;
					$this->save_all_table($insertIntoUserAffectation,$columsinsertaffect,$datatoinsert_affect);
				}
				
				//supprimer dans la table tempo apres l'enregistrement
				$ID_USER=session()->get("SESSION_SUIVIE_PTBA_USER_ID");
				$table="user_users_tempo";
				$critere="USER_ID=".$ID_USER;
				$bindparams_delete =[$db->escapeString($table),$db->escapeString($critere)];
				$deleteRequete = "CALL `deleteData`(?,?);";
				$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams_delete);
				$notification = new Notification();
				$subject = utf8_decode(''.lang('messages_lang.titre_notification').'');
				$msg= "<b>".$NOM.'</b>  <b>'.$PRENOM." </b>
				,".lang('messages_lang.user_notification').":<br><br>	
				- ".lang('messages_lang.user_notification')." : <b>".$EMAIL."</b><br>
				- ".lang('messages_lang.password_notification')." : <b>".$PASSWORD."</b><br><br>
				".lang('messages_lang.middle_notification')."<a href=".base_url().">".lang('messages_lang.link_notification')."</a><br>
				".lang('messages_lang.end_notification')."";
				$notification->sendEmail($EMAIL,$subject, $msg, array(), array());

				$data=['message' => "Enregistrement effectué avec succès"];
				session()->setFlashdata('alert', $data);
				return redirect('Administration/Gestion_Utilisateurs');
			}
			else
			{
				$ID_USER=session()->get("SESSION_SUIVIE_PTBA_USER_ID");
				$insertIntoTable='user_users_tempo';
				$insert = $this->getBindParms('INSTITUTION_ID_TEMPO,TEMPO_NOM,TEMPO_PRENOM,TEMPO_EMAIL,TEMPO_PASSWORD,TEMPO_TELEPHONE1,TEMPO_TELEPHONE2,PROFIL_ID_TEMPO,IS_SOUS_TUTEL_TEMPO,SOUS_TUTEL_ID_TEMPO', 'user_users_tempo', 'USER_ID='.$ID_USER, '1');
				$users= $this->ModelPs->getRequeteOne($callpsreq, $insert);
				$NOM = $users['TEMPO_NOM'];
				$PRENOM = $users['TEMPO_PRENOM'];
				$EMAIL = $this->request->getPost("EMAIL");
				$USER_NAME = $users['TEMPO_EMAIL'];
				$TELEPHONE1 = $users['TEMPO_TELEPHONE1'];
				$TELEPHONE2 = $users['TEMPO_TELEPHONE2'];
				$PASSWORD = $users['TEMPO_PASSWORD'];
				//$PASSWORD=$this->library->generate_password(5);				
				$PROFIL_ID=$users['PROFIL_ID_TEMPO'];						
				$IS_CONNECTED=0;
				$IS_ACTIVE=1;
				$DATE_ACTIVATION=date('Y-m-d h:i');
				$DATE_INSERTION=date('Y-m-d h:i');
				$CODE_USER='administration';				

				//Enregistrer dans user_users
				$columsinsert="NOM,PRENOM,EMAIL,USER_NAME,TELEPHONE1,TELEPHONE2,PASSWORD,PROFIL_ID,IS_CONNECTED,IS_ACTIVE,DATE_ACTIVATION,DATE_INSERTION,REGISTER_USER_ID";
				$NOM=str_replace("'","\'",$NOM);
				$PRENOM=str_replace("'","\'",$PRENOM);

				$datatoinsert= "'" . $NOM . "','" . $PRENOM . "','" . $EMAIL . "','" . $USER_NAME . "','" . $TELEPHONE1 . "','" . $TELEPHONE2 . "','" . md5($PASSWORD) . "'," . $PROFIL_ID . "," . $IS_CONNECTED . "," . $IS_ACTIVE . ",'" . $DATE_ACTIVATION . "','" . $DATE_INSERTION . "',".$ID_USER;
    		$table='user_users';
    		$last_id = $this->save_all_table($table,$columsinsert,$datatoinsert);

				//inserer dans affectation
				//recuperer les donnees dans la table tempo 
				$insert_affect = $this->getBindParms('INSTITUTION_ID_TEMPO,PROFIL_ID_TEMPO,IS_SOUS_TUTEL_TEMPO,SOUS_TUTEL_ID_TEMPO', 'user_users_tempo', 'USER_ID='.$ID_USER.' AND IS_INSERTION=1', '1');
				$users_affect= $this->ModelPs->getRequete($callpsreq, $insert_affect);
				$USER_ID_AFFECT=$last_id;
				foreach($users_affect as $value)
				{
					$tabletoinsert='user_affectaion';
					$INSTITUTION_ID_AFFECT=$value->INSTITUTION_ID_TEMPO;
					if($INSTITUTION_ID_AFFECT !=0)
					{
						$IS_SOUS_TUTEL_AFFECT=!empty($value->IS_SOUS_TUTEL_TEMPO) ? $value->IS_SOUS_TUTEL_TEMPO:0;
						$SOUS_TUTEL_ID_AFFECT=!empty($value->SOUS_TUTEL_ID_TEMPO) ? ($value->SOUS_TUTEL_ID_TEMPO):'NULL';

						//Enregistrer dans users affectation
						$columsinsert2="USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID";
						$datatoinsert2= "".$USER_ID_AFFECT."," . $INSTITUTION_ID_AFFECT . "," . $IS_SOUS_TUTEL_AFFECT . "," . $SOUS_TUTEL_ID_AFFECT;
		    		$table='user_users';
		    		$this->save_all_table($tabletoinsert,$columsinsert2,$datatoinsert2);
					}
				}

				//supprimer dans la table tempo apres l'enregistrement
				$table="user_users_tempo";
				$critere="USER_ID=".$ID_USER;
				$bindparams_delete =[$db->escapeString($table),$db->escapeString($critere)];
				$deleteRequete = "CALL `deleteData`(?,?);";

				$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams_delete);
				$notification = new Notification();
				$subject = utf8_decode(''.lang('messages_lang.titre_notification').'');
				$msg= "<b>".$NOM.'</b>  <b>'.$PRENOM." </b>
				,".lang('messages_lang.user_notification').":<br><br>	
				- ".lang('messages_lang.user_notification')." : <b>".$EMAIL."</b><br>
				- ".lang('messages_lang.password_notification')." : <b>".$PASSWORD."</b><br><br>
				".lang('messages_lang.middle_notification')."<a href=".base_url().">".lang('messages_lang.link_notification')."</a><br>
				".lang('messages_lang.end_notification')."";
				$notification->sendEmail($EMAIL,$subject, $msg, array(), array());
				$data=['message' => ''.lang('messages_lang.Enregistrer_succes_msg').''];
				session()->setFlashdata('alert', $data);
				return redirect('Administration/Gestion_Utilisateurs');
			}
		}
		else
		{
			return $this->ajout();
		}
	}

	// fonction pour inserer dans la table tempo
	public function insert_tab_tempo()
	{
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}
		
		// selectionner les identifiant se trouvant dans cart
		$bind_chart_name = $this->getBindParms('TEMPO_NOM,TEMPO_PRENOM,TEMPO_EMAIL,TEMPO_PASSWORD,TEMPO_TELEPHONE1,TEMPO_TELEPHONE2,PROFIL_ID_TEMPO','user_users_tempo','USER_ID='.$user_id.' AND IS_INSERTION=1','USER_ID_TEMPO DESC');
		$tempo= $this->ModelPs->getRequeteOne($callpsreq, $bind_chart_name);
		
		
		$NOM = $this->request->getPost('NOM_1');
		$PRENOM = $this->request->getPost('PRENOM_1');
		$EMAIL = $this->request->getPost('EMAIL_1');
		$PASSWORD = $this->request->getPost('PASSWORD_1');

		$TELEPHONE1 = $this->request->getPost('TELEPHONE1_1');
		$TELEPHONE2 = $this->request->getPost('TELEPHONE2_1');
		$PROFIL_ID = $this->request->getPost('PROFIL_ID_1');
		$NOM=str_replace("'","\'",$NOM);
		$PRENOM=str_replace("'","\'",$PRENOM);
		$IS_INSERTION=1;

		if(!empty($tempo))
		{
			$NOM = $tempo['TEMPO_NOM'];
			$PRENOM = $tempo['TEMPO_PRENOM'];
			$EMAIL = $tempo['TEMPO_EMAIL'];
			$PASSWORD = $tempo['TEMPO_PASSWORD'];
			$TELEPHONE1 = $tempo['TEMPO_TELEPHONE1'];
			$TELEPHONE2 = $tempo['TEMPO_TELEPHONE2'];
			$PROFIL_ID = $tempo['PROFIL_ID_TEMPO'];
		}

		
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$IS_SOUS_TUTEL =$this->request->getPost('IS_SOUS_TUTEL');
		$SOUS_TUTEL_ID =!empty($this->request->getPost('SOUS_TUTEL_ID')) ? $this->request->getPost('SOUS_TUTEL_ID'): 0;
		$USER_ID =session()->get("SESSION_SUIVIE_PTBA_USER_ID");		
		
		//Enregistrer dans tempo
		$NOM=str_replace("'","\'",$NOM);
		$PRENOM=str_replace("'","\'",$PRENOM);
		
		$columsinsert="TEMPO_NOM,TEMPO_PRENOM,TEMPO_EMAIL,TEMPO_PASSWORD,TEMPO_TELEPHONE1,TEMPO_TELEPHONE2,INSTITUTION_ID_TEMPO,PROFIL_ID_TEMPO,IS_SOUS_TUTEL_TEMPO,SOUS_TUTEL_ID_TEMPO,USER_ID,IS_INSERTION";
		$elements1 = explode(',', $columsinsert);
		$count1 = count($elements1);

		$datatoinsert= "'" . $NOM . "','" . $PRENOM . "','" . $EMAIL . "','".$PASSWORD."','" . $TELEPHONE1 . "','" . $TELEPHONE2 . "'," . $INSTITUTION_ID . "," . $PROFIL_ID . ",'" . $IS_SOUS_TUTEL ."'," . $SOUS_TUTEL_ID .",".$USER_ID.",".$IS_INSERTION;
		$elements2 = explode(',', $datatoinsert);
		$count2 = count($elements2);

		// print_r($count2);exit();
		// echo $count2;
    $table='user_users_tempo';
    $this->save_all_table($table,$columsinsert,$datatoinsert);
    
    // selectionner les institutions
    $cart = \Config\Services::cart();
    $cart->destroy();
		$bind_temp_inst = $this->getBindParms('USER_ID_TEMPO,INSTITUTION_ID_TEMPO, DESCRIPTION_INSTITUTION','user_users_tempo temp JOIN inst_institutions inst ON temp.INSTITUTION_ID_TEMPO=inst.INSTITUTION_ID','USER_ID='.$user_id.' AND IS_INSERTION=1','USER_ID_TEMPO ASC');
		$tempo_inst= $this->ModelPs->getRequete($callpsreq, $bind_temp_inst);

		foreach($tempo_inst as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESCRIPTION_INSTITUTION'=>$value->DESCRIPTION_INSTITUTION,
        'USER_ID_TEMPO'=>$value->USER_ID_TEMPO,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }
    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>INSTITUTION&emsp;&emsp;&emsp;&emsp;</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());

    foreach ($cart->contents() as $items):
        
        if (preg_match('/FILECI/',$items['typecartitem']))
        {
        	$i++;
          $html.='<tr>
          <td>'.$j.'</td>
         
          <td>'.$items['DESCRIPTION_INSTITUTION'].'</td>
          <td>
          <a href="javascript:void(0)" onclick="show_modal('.$items['USER_ID_TEMPO'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
          </td>
          <td>
          <textarea id="DEL_CIBLE'.$items['USER_ID_TEMPO'].'" style="display:none">'.$items['DESCRIPTION_INSTITUTION'].'</textarea>
          <input type="hidden" id="rowid'.$items['USER_ID_TEMPO'].'" value='.$items['USER_ID_TEMPO'].'>
          </td>
          </tr>';        
        }

        $j++;
        $i++;
    endforeach;
    $html.=' </tbody>
    </table>';

    if ($i>0) 
    {
      $output = array('status' => TRUE, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
    else
    {
    	$html= '';
      $output = array('status' => TRUE, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
	}
	
	// fonction pour supprimer dans cart/table tempo
	public function delete()
	{
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$id = $this->request->getPost('id');
    $rowid=$this->request->getPost('rowid');
    $user_id_to_delete=$this->request->getPost('USER_ID');

		$db = db_connect();     
		$critere ="USER_ID_TEMPO =" .$id;
		$table="user_users_tempo";
		$bindparams =[$db->escapeString($table),$db->escapeString($critere)];
		$deleteRequete = "CALL `deleteData`(?,?);";
		$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);

		$critere ="USER_ID=" .$user_id. " AND INSTITUTION_ID_TEMPO=0";
		$table="user_users_tempo";
		$bindparams =[$db->escapeString($table),$db->escapeString($critere)];
		$deleteRequete = "CALL `deleteData`(?,?);";
		$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);

		// selectionner les institutions
    $cart = \Config\Services::cart();
    $cart->destroy();
    if (!empty($user_id_to_delete)) 
    {
    	$bind_temp_inst = $this->getBindParms('USER_ID_TEMPO,INSTITUTION_ID_TEMPO, DESCRIPTION_INSTITUTION','user_users_tempo temp JOIN inst_institutions inst ON temp.INSTITUTION_ID_TEMPO=inst.INSTITUTION_ID','USER_ID='.$user_id.' AND USER_ID_MODIFIER='.$user_id_to_delete,'USER_ID_TEMPO ASC');
    }else{
    	$bind_temp_inst = $this->getBindParms('USER_ID_TEMPO,INSTITUTION_ID_TEMPO, DESCRIPTION_INSTITUTION','user_users_tempo temp JOIN inst_institutions inst ON temp.INSTITUTION_ID_TEMPO=inst.INSTITUTION_ID','USER_ID='.$user_id,'USER_ID_TEMPO ASC');
    }
		
		$tempo_inst= $this->ModelPs->getRequete($callpsreq, $bind_temp_inst);

		foreach($tempo_inst as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESCRIPTION_INSTITUTION'=>$value->DESCRIPTION_INSTITUTION,
        'USER_ID_TEMPO'=>$value->USER_ID_TEMPO,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }

    $html="";
      $j=1;
      $i=0;
    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>INSTITUTION&emsp;&emsp;&emsp;&emsp;</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());

    foreach ($cart->contents() as $items):

    	if (preg_match('/FILECI/',$items['typecartitem']))
    	{
    		$i++;

    		$html.='<tr>
    		<td>'.$j.'</td>

    		<td>'.$items['DESCRIPTION_INSTITUTION'].'</td>
    		<td>
    		<a href="javascript:void(0)" onclick="show_modal('.$items['USER_ID_TEMPO'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
    		</td>
    		<td>
    		<textarea id="DEL_CIBLE'.$items['USER_ID_TEMPO'].'" style="display:none">'.$items['DESCRIPTION_INSTITUTION'].'</textarea>
    		<input type="hidden" id="rowid'.$items['USER_ID_TEMPO'].'" value='.$items['USER_ID_TEMPO'].'>
    		</td>
    		</tr>';        
    	}

    	$j++;
    	$i++;
    endforeach;
    $html.=' </tbody>
    </table>';

    if ($i>0)
    {
      # code...
      $output = array('status' => TRUE, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
    else
    {

      $html= '';
      $output = array('status' => TRUE, 'cart'=>$html);
      return $this->response->setJSON($output);
    }

		return redirect('Administration/Gestion_Utilisateurs/ajout');
	}

	// fonction get pour recuperer les données a modifier
	public function getOne($id)
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_users= $this->getBindParms('USER_ID ,	NOM ,	PRENOM, EMAIL ,TELEPHONE1 , TELEPHONE2 , PROFIL_ID ' , 'user_users', 'MD5(USER_ID)="'.$id.'"','USER_ID ASC');
		$bind_users=str_replace('\\', '', $bind_users);
		$data_user=$this->ModelPs->getRequeteOne($callpsreq, $bind_users);
		$data['users']= $data_user;
		$bind_profil=$this->getBindParms('PROFIL_ID,PROFIL_DESCR','user_profil','IS_ACTIVE=1','PROFIL_DESCR ASC');
		$data['profil']= $this->ModelPs->getRequete($callpsreq, $bind_profil);
		$bind_institution = $this->getBindParms('INSTITUTION_ID , DESCRIPTION_INSTITUTION', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
		$data['institution']= $this->ModelPs->getRequete($callpsreq, $bind_institution);
		$USER_ID=$data_user["USER_ID"];

		// selectionner les institutions
    $cart = \Config\Services::cart();
    $cart->destroy();

    $bind_chart = $this->getBindParms('USER_ID,USER_ID_TEMPO,inst.DESCRIPTION_INSTITUTION','user_users_tempo join inst_institutions inst on inst.INSTITUTION_ID=user_users_tempo.INSTITUTION_ID_TEMPO','USER_ID='.$user_id.' AND IS_INSERTION=2 AND MD5(USER_ID_MODIFIER)="'.$id.'"','1');
    $bind_chart=str_replace('\\', '', $bind_chart);
		$chart = $this->ModelPs->getRequete($callpsreq, $bind_chart);
		$data['table_to_delete']='';//table qui m'amene le cart pour savoir la ou je vais supprimer
		if (empty($chart)) 
		{
			$bind_chart = $this->getBindParms('USER_AFFECTAION,USER_ID,inst.DESCRIPTION_INSTITUTION','user_affectaion join inst_institutions inst on inst.INSTITUTION_ID=user_affectaion.INSTITUTION_ID','MD5(USER_ID)="'.$id.'"','1');
			$bind_chart=str_replace('\\', '', $bind_chart);
			$chart = $this->ModelPs->getRequete($callpsreq, $bind_chart);		
			foreach($chart as $value)
	    {
	      $file_data=array(
	        'id'=>uniqid(),
	        'qty'=>1,
	        'price'=>1,
	        'name'=>'CI',
	        'DESCRIPTION_INSTITUTION'=>$value->DESCRIPTION_INSTITUTION,
	        'USER_AFFECTAION'=>$value->USER_AFFECTAION,
	        'typecartitem'=>'FILECI'
	      );
	      $cart->insert($file_data);	      
	    }	
	    $data['table_to_delete']='user_affectaion';
		}else{
			foreach($chart as $value)
	    {
	      $file_data=array(
	        'id'=>uniqid(),
	        'qty'=>1,
	        'price'=>1,
	        'name'=>'CI',
	        'DESCRIPTION_INSTITUTION'=>$value->DESCRIPTION_INSTITUTION,
	        'USER_AFFECTAION'=>$value->USER_ID_TEMPO,
	        'typecartitem'=>'FILECI'
	      );
	      $cart->insert($file_data);
	    }
	    $data['table_to_delete']='user_users_tempo';
		}		

    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>INSTITUTION&emsp;&emsp;&emsp;&emsp;</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());

    foreach ($cart->contents() as $items):
    	if (preg_match('/FILECI/',$items['typecartitem']))
    	{

    		$i++;

    		$html.='<tr>
    		<td>'.$j.'</td>

    		<td>'.$items['DESCRIPTION_INSTITUTION'].'</td>
    		<td>
    		<a href="javascript:void(0)" onclick="show_modal_update('.$items['USER_AFFECTAION'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
    		</td>
    		<td>
    		<textarea id="DEL_CIBLE'.$items['USER_AFFECTAION'].'" style="display:none">'.$items['DESCRIPTION_INSTITUTION'].'</textarea>
    		<input type="hidden" id="rowid'.$items['USER_AFFECTAION'].'" value='.$items['USER_AFFECTAION'].'>
    		</td>
    		</tr>';
    	}
    	$j++;
    	$i++;
    endforeach;
    $html.=' </tbody>
    </table>';

    if ($i>0)
    {
    	$valeur = 1;
      $html;
    }
    else
    {
    	$valeur = 1;
      $html= '';
    }
    $data['valeur']=$valeur;
    $data['myCart']=$html;
		return view('App\Modules\Administration\Views\Utilisateurs_Update_View',$data);
	}

	//Mise a jour ou ajouter dans affectation etant sur la page de modification
	public function update_affectation()
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$USER_ID_MODIFIER = $this->request->getPost('USER_ID');
		$NOM = $this->request->getPost('NOM');
		$PRENOM = $this->request->getPost('PRENOM');
		$EMAIL = $this->request->getPost('EMAIL');
		$TELEPHONE1 = $this->request->getPost('TELEPHONE1');
		$TELEPHONE2 = $this->request->getPost('TELEPHONE2');
		$PROFIL_ID = $this->request->getPost('PROFIL_ID');
		$NOM=str_replace("'","\'",$NOM);
		$PRENOM=str_replace("'","\'",$PRENOM);
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$IS_SOUS_TUTEL =$this->request->getPost('IS_SOUS_TUTEL');
		$SOUS_TUTEL_ID=!empty($this->request->getPost('SOUS_TUTEL_ID')) ? $this->request->getPost('SOUS_TUTEL_ID'):0;
		$IS_INSERTION=2;
		
		$insertIntoUserTempo='user_users_tempo';
		$columsinserttempo="TEMPO_NOM,TEMPO_PRENOM,TEMPO_EMAIL,TEMPO_TELEPHONE1,TEMPO_TELEPHONE2,INSTITUTION_ID_TEMPO,PROFIL_ID_TEMPO,IS_SOUS_TUTEL_TEMPO,SOUS_TUTEL_ID_TEMPO,USER_ID,USER_ID_MODIFIER,IS_INSERTION";
		$datatoinsert_tempo= "'" . $NOM . "','" . $PRENOM . "','" . $EMAIL . "','" . $TELEPHONE1 . "','" . $TELEPHONE2 . "'," . $INSTITUTION_ID . "," . $PROFIL_ID . ",'" . $IS_SOUS_TUTEL ."'," . $SOUS_TUTEL_ID .",".$user_id.",".$USER_ID_MODIFIER.",".$IS_INSERTION;
		$this->save_all_table($insertIntoUserTempo,$columsinserttempo,$datatoinsert_tempo);

		// selectionner les institutions
    $cart = \Config\Services::cart();
    $cart->destroy();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_chart = $this->getBindParms('USER_ID,USER_ID_TEMPO,inst.DESCRIPTION_INSTITUTION','user_users_tempo join inst_institutions inst on inst.INSTITUTION_ID=user_users_tempo.INSTITUTION_ID_TEMPO','USER_ID='.$user_id.' AND IS_INSERTION=2 AND USER_ID_MODIFIER='.$USER_ID_MODIFIER,'1');
		$chart = $this->ModelPs->getRequete($callpsreq, $bind_chart);

		foreach($chart as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESCRIPTION_INSTITUTION'=>$value->DESCRIPTION_INSTITUTION,
        'USER_ID_TEMPO'=>$value->USER_ID_TEMPO,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }

    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>INSTITUTION&emsp;&emsp;&emsp;&emsp;</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());

    foreach ($cart->contents() as $items):
    	if (preg_match('/FILECI/',$items['typecartitem']))
    	{
    		$i++;

    		$html.='<tr>
    		<td>'.$j.'</td>

    		<td>'.$items['DESCRIPTION_INSTITUTION'].'</td>
    		<td>
    		<a href="javascript:void(0)" onclick="show_modal_update('.$items['USER_ID_TEMPO'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
    		</td>
    		<td>
    		<textarea id="DEL_CIBLE'.$items['USER_ID_TEMPO'].'" style="display:none">'.$items['DESCRIPTION_INSTITUTION'].'</textarea>
    		<input type="hidden" id="rowid'.$items['USER_ID_TEMPO'].'" value='.$items['USER_ID_TEMPO'].'>
    		</td>
    		</tr>';

    	}

    	$j++;
    	$i++;
    endforeach;
    $html.=' </tbody>
    </table>';

    if($i>0)
    {
      # code...
      $output = array('status' => TRUE, 'cart'=>$html,'TABLE_CART'=>'user_users_tempo');
      return $this->response->setJSON($output);//echo json_encode($output);      
    }
    else
    {

      $html= '';
      $output = array('status' => TRUE, 'cart'=>$html,'TABLE_CART'=>'user_users_tempo');
      return $this->response->setJSON($output);//echo json_encode($output);
    }				
	}

	//supprimer dans affectation etant sur modification
	public function delete_affectation()
	{
		$session  = \Config\Services::session();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$USER_ID = $this->request->getPost('USER_ID');
		$USER_AFFECTAION = $this->request->getPost('id');
    $rowid=$this->request->getPost('rowid');

		$db = db_connect();     
		$critere ="USER_AFFECTAION=" .$USER_AFFECTAION;
		$table="user_affectaion";
		$bindparams =[$db->escapeString($table),$db->escapeString($critere)];
		$deleteRequete = "CALL `deleteData`(?,?);";
		$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
		
		// selectionner les institutions
    $cart = \Config\Services::cart();
    $cart->destroy();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bind_chart = $this->getBindParms('USER_AFFECTAION,USER_ID,inst.DESCRIPTION_INSTITUTION','user_affectaion join inst_institutions inst on inst.INSTITUTION_ID=user_affectaion.INSTITUTION_ID','USER_ID='.$USER_ID,'USER_AFFECTAION');
		$chart = $this->ModelPs->getRequete($callpsreq, $bind_chart);

		foreach($chart as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESCRIPTION_INSTITUTION'=>$value->DESCRIPTION_INSTITUTION,
        'USER_AFFECTAION'=>$value->USER_AFFECTAION,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }
    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>INSTITUTION&emsp;&emsp;&emsp;&emsp;</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());

    foreach ($cart->contents() as $items):
    	if (preg_match('/FILECI/',$items['typecartitem']))
    	{
    		$i++;

    		$html.='<tr>
    		<td>'.$j.'</td>

    		<td>'.$items['DESCRIPTION_INSTITUTION'].'</td>
    		<td>
    		<a href="javascript:void(0)" onclick="show_modal_update('.$items['USER_AFFECTAION'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
    		</td>
    		<td>
    		<textarea id="DEL_CIBLE'.$items['USER_AFFECTAION'].'" style="display:none">'.$items['DESCRIPTION_INSTITUTION'].'</textarea>
    		<input type="hidden" id="rowid'.$items['USER_AFFECTAION'].'" value='.$items['USER_AFFECTAION'].'>
    		</td>
    		</tr>';
    	}

    	$j++;
    	$i++;
    endforeach;
    $html.=' </tbody>
    </table>';
    if($i>0)
    {
    	$output = array('status' => TRUE, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
    else
    {
    	$html= '';
      $output = array('status' => TRUE, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
	}

  //Mise à jour des utilisateurs
	public function update()
	{
		$session  = \Config\Services::session();
		if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
		}
		else
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_UTILISATEURS')!=1)
		{
			return redirect('Login_Ptba/homepage');
		}

		$db = db_connect();
		$rules = [
			'NOM' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],
			'PRENOM' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			],

			'TELEPHONE1' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
				]
			]
			
		];
		$this->validation->setRules($rules);

		if($this->validation->withRequest($this->request)->run())
		{
			$USER_ID_MODIFIER = $this->request->getPost('USER_ID');
			$NOM = $this->request->getPost('NOM');
			$PRENOM = $this->request->getPost('PRENOM');
			$TELEPHONE1 = $this->request->getPost('TELEPHONE1');
			$TELEPHONE2 = $this->request->getPost('TELEPHONE2');
			$PROFIL_ID = $this->request->getPost('PROFIL_ID');
			$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
			$DATE_ACTIVATION=date('Y-m-d h:i');
			$DATE_INSERTION=date('Y-m-d h:i');
			$callpsreq = "CALL `getRequete`(?,?,?,?);";
			//selectionner le niveau de visualisation dans profil pour voir les institutions d'affection
			$PROFIL = $this->request->getPost('PROFIL_ID');
			$get_visualisation=$this->getBindParms('NIVEAU_VISUALISATION_ID','user_profil','PROFIL_ID='.$PROFIL,'1');
			$visualisation=$this->ModelPs->getRequeteOne($callpsreq,$get_visualisation);
			$visualisation_id=$visualisation['NIVEAU_VISUALISATION_ID'];

			if($INSTITUTION_ID==12 && $PROFIL_ID!=9 && $PROFIL_ID!=10) 
			{
				//supprimer ce qui etait dans la table affection et inserer a nouveau
				$critere ="USER_ID=" .$USER_ID_MODIFIER;
				$table="user_affectaion";
				$bindparams =[$db->escapeString($table),$db->escapeString($critere)];
				$deleteRequete = "CALL `deleteData`(?,?);";
				$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);

				//inserer a nouveau
				$updateIntoTable='user_users';
				$columsupdate='NOM="'.$NOM.'",PRENOM="'.$PRENOM.'",TELEPHONE1="'.$TELEPHONE2.'",TELEPHONE2="'.$TELEPHONE2.'",PROFIL_ID='.$PROFIL_ID;
				$conditions='USER_ID='.$USER_ID_MODIFIER ;
				$this->update_all_table($updateIntoTable,$columsupdate,$conditions);
	
				if($visualisation_id==1)
				{
					// recuperer toutes les institutions
					$inst = $this->getBindParms('INSTITUTION_ID', 'inst_institutions', '1', 'INSTITUTION_ID DESC');
					$get_inst= $this->ModelPs->getRequete($callpsreq, $inst);
					foreach($get_inst as $value)
					{
						$insertIntoUserAffectation='user_affectaion';
						$columsinsertaffect='USER_ID,INSTITUTION_ID';
						$datatoinsert_affect = "".$USER_ID_MODIFIER."," . $value->INSTITUTION_ID;
						$this->save_all_table($insertIntoUserAffectation,$columsinsertaffect,$datatoinsert_affect);
					}
				}
				else
				{
					$insertIntoUserAffectation='user_affectaion';
					$columsinsertaffect='USER_ID,INSTITUTION_ID';
					$datatoinsert_affect = "" . $USER_ID_MODIFIER . "," . $INSTITUTION_ID;
					$this->save_all_table($insertIntoUserAffectation,$columsinsertaffect,$datatoinsert_affect);
				}
				
				$data=['message' => ''.lang('messages_lang.msg_modif').''];
				session()->setFlashdata('alert', $data);
				return redirect('Administration/Gestion_Utilisateurs');
			}
			else
			{
				$updateTable='user_users';
				$critere = "USER_ID=".$USER_ID_MODIFIER;
				$datatoupdate= 'NOM="'.$NOM.'",PRENOM="'.$PRENOM.'",TELEPHONE1="'.$TELEPHONE1.'",TELEPHONE2="'.$TELEPHONE2.'",PROFIL_ID='.$PROFIL_ID;
				$bindparams =[$updateTable,$datatoupdate,$critere];
				$insertRequete = 'CALL `updateData`(?,?,?);';
				$this->ModelPs->createUpdateDelete($insertRequete, $bindparams);				

				//recuperer les donnees dans la table tempo 
				$insert_affect = $this->getBindParms('INSTITUTION_ID_TEMPO,PROFIL_ID_TEMPO,IS_SOUS_TUTEL_TEMPO,SOUS_TUTEL_ID_TEMPO', 'user_users_tempo', 'USER_ID='.$user_id.' AND IS_INSERTION=2 AND USER_ID_MODIFIER='.$USER_ID_MODIFIER, '1');
				$users_affect= $this->ModelPs->getRequete($callpsreq, $insert_affect);
				$USER_ID_AFFECT=$USER_ID_MODIFIER;
				if(!empty($users_affect))
				{
					//supprimer ce qui etait dans la table affection et inserer a nouveau
					$critere ="USER_ID=".$USER_ID_MODIFIER;
					$table="user_affectaion";
					$bindparams =[$db->escapeString($table),$db->escapeString($critere)];
					$deleteRequete = "CALL `deleteData`(?,?);";
					$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
					foreach($users_affect as $value)
					{
						$tabletoinsert='user_affectaion';
						$INSTITUTION_ID_AFFECT=$value->INSTITUTION_ID_TEMPO;
						if($INSTITUTION_ID_AFFECT !=0)
						{
							$IS_SOUS_TUTEL_AFFECT=!empty($value->IS_SOUS_TUTEL_TEMPO) ? $value->IS_SOUS_TUTEL_TEMPO:0;
							$SOUS_TUTEL_ID_AFFECT=!empty($value->SOUS_TUTEL_ID_TEMPO) ? ($value->SOUS_TUTEL_ID_TEMPO):'NULL';

							//Enregistrer dans users affectation
							$columsinsert2="USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID";
							$datatoinsert2= "".$USER_ID_AFFECT."," . $INSTITUTION_ID_AFFECT . "," . $IS_SOUS_TUTEL_AFFECT . "," . $SOUS_TUTEL_ID_AFFECT;
			    		$table='user_users';
			    		$this->save_all_table($tabletoinsert,$columsinsert2,$datatoinsert2);
						}
					}
					//supprimer dans tempo apres l'enregistrement
					$table="user_users_tempo";
					$critere="USER_ID=".$user_id." AND IS_INSERTION=2 AND USER_ID_MODIFIER=".$USER_ID_MODIFIER;
					$bindparams_delete =[$db->escapeString($table),$db->escapeString($critere)];
					$deleteRequete = "CALL `deleteData`(?,?);";
					$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams_delete);
				}				
				
				$data=['message' => ''.lang('messages_lang.msg_modif').''];
				session()->setFlashdata('alert', $data);
				return redirect('Administration/Gestion_Utilisateurs');
			}
		}
	}

	//fonction pour inserer dans les colonnes souhaites
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

	/* update table */
	function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
	}
}
?>