<?php 
/**Joa-Kevin Iradukunda
*Titre: AJOUT - PTBA_TACHE
*Numero de telephone: (+257) 62 63 65 35
*WhatsApp: (+27) 61 436 6546
*Email: joa-kevin.iradukunda@mediabox.bi
*Date: 02 Septembre,2024
**/

namespace  App\Modules\ihm\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;

class Taches extends BaseController
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

	//fonction pour recuperer les sous titre
	public function get_sous_titre()
	{

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$sous_titres=array();
		if($INSTITUTION_ID != "")
		{
			$sql_sous_titre='SELECT DESCRIPTION_SOUS_TUTEL,CODE_SOUS_TUTEL,SOUS_TUTEL_ID FROM inst_institutions_sous_tutel WHERE 1 AND INSTITUTION_ID ='.$INSTITUTION_ID.' ORDER BY SOUS_TUTEL_ID';
			$sous_titres = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_sous_titre . "')");
		}
		
		$titre="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($sous_titres as $sous_titre)
		{
			$titre.= "<option value ='".$sous_titre->SOUS_TUTEL_ID."'>".$sous_titre->CODE_SOUS_TUTEL."-".$sous_titre->DESCRIPTION_SOUS_TUTEL."</option>";
		}
		$output = array("titre"=>$titre);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les articles economiques
	public function get_article()
	{
		$callpsreq="CALL `getRequete`(?,?,?,?);";
		$CHAPITRE_ID=$this->request->getPost('CHAPITRE_ID');
		$resArticles=array();
		if($CHAPITRE_ID!="")
		{
			$sql_article='SELECT ARTICLE_ID,CODE_ARTICLE,LIBELLE_ARTICLE FROM class_economique_article WHERE 1 AND CHAPITRE_ID ='.$CHAPITRE_ID.' ORDER BY ARTICLE_ID';
			$resArticles=$this->ModelPs->getRequete("CALL `getTable`('" . $sql_article . "')");
		}
		
		$article="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resArticles as $resArticle)
		{
			$article.="<option value ='".$resArticle->ARTICLE_ID."'>".$resArticle->CODE_ARTICLE."-".$resArticle->LIBELLE_ARTICLE."</option>";
		}
		$output = array("article"=>$article);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les paragraphes
	public function get_paragraphe()
	{
		$callpsreq="CALL `getRequete`(?,?,?,?);";
		$ARTICLE_ID=$this->request->getPost('ARTICLE_ID');
		$resParagraphes=array();
		if($ARTICLE_ID!="")
		{
			$sql_paragraphe='SELECT PARAGRAPHE_ID,CODE_PARAGRAPHE,LIBELLE_PARAGRAPHE FROM class_economique_paragraphe WHERE 1 AND ARTICLE_ID ='.$ARTICLE_ID.' ORDER BY PARAGRAPHE_ID';
			$resParagraphes=$this->ModelPs->getRequete("CALL `getTable`('" . $sql_paragraphe . "')");
		}
		
		$paragraphe="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resParagraphes as $resParagraphe)
		{
			$paragraphe.="<option value ='".$resParagraphe->PARAGRAPHE_ID."'>".$resParagraphe->CODE_PARAGRAPHE."-".$resParagraphe->LIBELLE_PARAGRAPHE."</option>";
		}
		$output = array("paragraphe"=>$paragraphe);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les littera
	public function get_littera()
	{
		$callpsreq="CALL `getRequete`(?,?,?,?);";
		$PARAGRAPHE_ID=$this->request->getPost('PARAGRAPHE_ID');
		$resLitteras=array();
		if($PARAGRAPHE_ID!="")
		{
			$sql_littera='SELECT LITTERA_ID,CODE_LITTERA,LIBELLE_LITTERA FROM class_economique_littera WHERE 1 AND PARAGRAPHE_ID ='.$PARAGRAPHE_ID.' ORDER BY LITTERA_ID';
			$resLitteras=$this->ModelPs->getRequete("CALL `getTable`('" . $sql_littera . "')");
		}
		
		$littera="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resLitteras as $resLittera)
		{
			$littera.="<option value ='".$resLittera->LITTERA_ID."'>".$resLittera->CODE_LITTERA."-".$resLittera->LIBELLE_LITTERA."</option>";
		}
		$output = array("littera"=>$littera);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les sous littera
	public function get_sous_littera()
	{
		$callpsreq="CALL `getRequete`(?,?,?,?);";
		$LITTERA_ID=$this->request->getPost('LITTERA_ID');
		$resSousLitteras=array();
		if($LITTERA_ID!="")
		{
			$sql_sous_littera='SELECT SOUS_LITTERA_ID,CODE_SOUS_LITTERA,LIBELLE_SOUS_LITTERA FROM class_economique_sous_littera WHERE 1 AND LITTERA_ID ='.$LITTERA_ID.' ORDER BY SOUS_LITTERA_ID';
			$resSousLitteras=$this->ModelPs->getRequete("CALL `getTable`('" . $sql_sous_littera . "')");
		}
		
		$sous_littera="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resSousLitteras as $resLittera)
		{
			$sous_littera.="<option value ='".$resLittera->SOUS_LITTERA_ID."'>".$resLittera->CODE_SOUS_LITTERA."-".$resLittera->LIBELLE_SOUS_LITTERA."</option>";
		}
		$output = array("sous_littera"=>$sous_littera);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les groupes
	public function get_groupe()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$DIVISION_ID=$this->request->getPost('DIVISION_ID');
		$resGroupes=array();
		if($DIVISION_ID != "")
		{
			$sql_groupes='SELECT GROUPE_ID,CODE_GROUPE,LIBELLE_GROUPE FROM class_fonctionnelle_groupe WHERE 1 AND DIVISION_ID ='.$DIVISION_ID.' ORDER BY GROUPE_ID';
			$resGroupes = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_groupes . "')");
		}
		
		$groupe="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resGroupes as $resGroupe)
		{
			$groupe.= "<option value ='".$resGroupe->GROUPE_ID."'>".$resGroupe->CODE_GROUPE."-".$resGroupe->LIBELLE_GROUPE."</option>";
		}
		$output = array("groupe"=>$groupe);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les classes
	public function get_classe()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$GROUPE_ID=$this->request->getPost('GROUPE_ID');
		$resClasses=array();
		if($GROUPE_ID != "")
		{
			$sql_classes='SELECT CLASSE_ID,CODE_CLASSE,LIBELLE_CLASSE FROM class_fonctionnelle_classe WHERE 1 AND GROUPE_ID ='.$GROUPE_ID.' ORDER BY CLASSE_ID';
			$resClasses = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_classes . "')");
		}
		
		$classe="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resClasses as $resClasse)
		{
			$classe.= "<option value ='".$resClasse->CLASSE_ID."'>".$resClasse->CODE_CLASSE."-".$resClasse->LIBELLE_CLASSE."</option>";
		}
		$output = array("classe"=>$classe);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les programmes
	public function get_programme()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		$resProgrammes=array();
		if($INSTITUTION_ID != "")
		{
			$sql_programme='SELECT PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME FROM inst_institutions_programmes WHERE 1 AND INSTITUTION_ID ='.$INSTITUTION_ID.' ORDER BY PROGRAMME_ID';
			$resProgrammes = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_programme . "')");
		}
		
		$programme="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resProgrammes as $resProgramme)
		{
			$programme.= "<option value ='".$resProgramme->PROGRAMME_ID."'>".$resProgramme->CODE_PROGRAMME."-".$resProgramme->INTITULE_PROGRAMME."</option>";
		}
		$output = array("programme"=>$programme);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les programmes
	public function get_action()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
		$resActions=array();
		if($PROGRAMME_ID != "")
		{
			$sql_action='SELECT ACTION_ID,CODE_ACTION,LIBELLE_ACTION FROM inst_institutions_actions WHERE 1 AND PROGRAMME_ID ='.$PROGRAMME_ID.' ORDER BY ACTION_ID';
			$resActions = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_action . "')");
		}
		
		$action="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resActions as $resAction)
		{
			$action.= "<option value ='".$resAction->ACTION_ID."'>".$resAction->CODE_ACTION."-".$resAction->LIBELLE_ACTION."</option>";
		}
		$output = array("action"=>$action);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les codes budgetaires
	public function get_code_budgetaire()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
		$resCodeBudgs=array();
		if($SOUS_TUTEL_ID != "")
		{
			$sql_code_budg='SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE 1 AND SOUS_TUTEL_ID ='.$SOUS_TUTEL_ID.' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE_ID';
			$resCodeBudgs = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_code_budg . "')");
		}

		$code_budg="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resCodeBudgs as $resCodeBudg)
		{
			$code_budg.= "<option value ='".$resCodeBudg->CODE_NOMENCLATURE_BUDGETAIRE_ID."'>".$resCodeBudg->CODE_NOMENCLATURE_BUDGETAIRE."</option>";
		}
		$output = array("code_budgetaire"=>$code_budg);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les pap activites
	public function get_pap_activite()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$CODE_NOMENCLATURE_BUDGETAIRE_ID=$this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
		$resPapActivites=array();
		if($CODE_NOMENCLATURE_BUDGETAIRE_ID != "")
		{
			$sql_code_budg='SELECT PAP_ACTIVITE_ID,DESC_PAP_ACTIVITE FROM pap_activites WHERE 1 AND CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$CODE_NOMENCLATURE_BUDGETAIRE_ID.' ORDER BY PAP_ACTIVITE_ID';
			$resPapActivites = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_code_budg . "')");
		}

		$pap_activite="<option value=''>--".lang('messages_lang.labelle_selecte')."--</option>";
		foreach ($resPapActivites as $resPapActivite)
		{
			$pap_activite.= "<option value ='".$resPapActivite->PAP_ACTIVITE_ID."'>".$resPapActivite->DESC_PAP_ACTIVITE."</option>";
		}
		$output = array("pap_activite"=>$pap_activite);
		return $this->response->setJSON($output);
	}

	//fonction pour recuperer les details d' une institution
	public function get_institution_info()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
		
		$getInst  = 'SELECT TYPE_INSTITUTION_ID FROM inst_institutions WHERE INSTITUTION_ID = '.$INSTITUTION_ID.'';
		$getInst = "CALL `getTable`('" . $getInst . "');";
		$institution = $this->ModelPs->getRequeteOne($getInst);

		$output = array(
			"institution_type" => $institution['TYPE_INSTITUTION_ID'],
		);

		return $this->response->setJSON($output);
	}

	//fonction pour ajouter
	public function create()
	{
		$data=$this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$session  = \Config\Services::session();

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1 && $session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT')!=1)
		{
		 return redirect('Login_Ptba/homepage');
		}

		$user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		// get institution
		$bind_institutions=$this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions','INSTITUTION_ID IN (SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')','CODE_INSTITUTION ASC');
		$data['institutions']= $this->ModelPs->getRequete($callpsreq,$bind_institutions);

		// get pilier
		$bind_piliers=$this->getBindParms('ID_PILIER,DESCR_PILIER','pnd_pilier','1','ID_PILIER ASC');
		$data['piliers']= $this->ModelPs->getRequete($callpsreq,$bind_piliers);

		// get objectif de la vision
		$bind_vision_objectifs=$this->getBindParms('OBJECTIF_VISION_ID,DESC_OBJECTIF_VISION','vision_objectif','1','DESC_OBJECTIF_VISION ASC');
		$data['vision_objectifs']= $this->ModelPs->getRequete($callpsreq,$bind_vision_objectifs);

		// get axe
		$bind_axes=$this->getBindParms('AXE_PND_ID,DESCR_AXE_PND','pnd_axe','1','AXE_PND_ID ASC');
		$data['axes']= $this->ModelPs->getRequete($callpsreq,$bind_axes);

		// get programme prioritaire
		$bind_programme_prioritaires=$this->getBindParms('PROGRAMME_PRIORITAIRE_ID,DESC_PROGRAMME_PRIORITAIRE','inst_institutions_programme_prioritaire','1','DESC_PROGRAMME_PRIORITAIRE ASC');
		$data['programme_prioritaires']= $this->ModelPs->getRequete($callpsreq,$bind_programme_prioritaires);

		// get chapitre economique
		$bind_chapitres=$this->getBindParms('CHAPITRE_ID,CODE_CHAPITRE,LIBELLE_CHAPITRE','class_economique_chapitre','1','CODE_CHAPITRE ASC');
		$data['chapitres']= $this->ModelPs->getRequete($callpsreq,$bind_chapitres);

		// get activite costab
		$bind_costabs=$this->getBindParms('COSTAB_ACTIVITE_ID,DESC_COSTAB_ACTIVITE','costab_activites','1','DESC_COSTAB_ACTIVITE ASC');
		$data['costabs']= $this->ModelPs->getRequete($callpsreq,$bind_costabs);

		// get activite pap
		$bind_paps=$this->getBindParms('PAP_ACTIVITE_ID,DESC_PAP_ACTIVITE','pap_activites','1','DESC_PAP_ACTIVITE ASC');
		$data['paps']= $this->ModelPs->getRequete($callpsreq,$bind_paps);

		// get pnd indicateurs
		$bind_pnds=$this->getBindParms('INDICATEUR_PND_ID,DESC_INDICATEUR_PND','pnd_indicateur','1','DESC_INDICATEUR_PND ASC');
		$data['pnds']= $this->ModelPs->getRequete($callpsreq,$bind_pnds);

		// get structure responsable tache
		$bind_struct_resp=$this->getBindParms('STRUTURE_RESPONSABLE_TACHE_ID,DESC_STRUTURE_RESPONSABLE_TACHE','struture_responsable_tache','1','DESC_STRUTURE_RESPONSABLE_TACHE ASC');
		$data['structs']= $this->ModelPs->getRequete($callpsreq,$bind_struct_resp);

		// get grande masse
		$bind_gd_masses=$this->getBindParms('GRANDE_MASSE_ID,DESCRIPTION_GRANDE_MASSE','inst_grande_masse','1','DESCRIPTION_GRANDE_MASSE ASC');
		$data['gd_masses']= $this->ModelPs->getRequete($callpsreq,$bind_gd_masses);

		// get annees budgetaires
		$bind_annees=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION','annee_budgetaire','1','ANNEE_DESCRIPTION ASC');
		$data['annees']= $this->ModelPs->getRequete($callpsreq,$bind_annees);

		//Récuperer les divisions fonctionnelles
		$division = $this->getBindParms('DIVISION_ID,CODE_DIVISION,LIBELLE_DIVISION', 'class_fonctionnelle_division', '1', 'CODE_DIVISION ASC');
		$data['get_division'] = $this->ModelPs->getRequete($callpsreq, $division);

		//Sélectionner les motifs de création 
		$bindparams = $this->getBindParms('MOTIF_TACHE_ID, DESCR_MOTIF_TACHE', 'motif_creation_tache', '1', 'DESCR_MOTIF_TACHE ASC');
		$data['motif'] = $this->ModelPs->getRequete($callpsreq, $bindparams);

		return view('App\Modules\ihm\Views\Dem_Taches_Add_View',$data);
	}

	//ajouter la nouvelle tache
	public function save_tache()
	{
		$db = db_connect();
		$session  = \Config\Services::session();

		if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
		{
			return redirect('Login_Ptba');
		}

		if($session->get('SESSION_SUIVIE_PTBA_ENGAG_BUDGETAIRE_CONFIRM_CED')!=1 && $session->get('SESSION_SUIVIE_PTBA_ORDONNANCEMENT')!=1)
		{
		 return redirect('Login_Ptba/homepage');
		}

		$USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

		$PATH_LETTRE_AUTORISATION = $this->request->getFile('PATH_LETTRE_AUTORISATION');
		$FRAIS = $this->request->getPost('FRAIS');
		$MOTIF_TACHE_ID = $this->request->getPost('MOTIF_TACHE_ID');
		$NOM = $this->request->getPost('NOM');
		$PRENOM = $this->request->getPost('PRENOM');
		$POSTE = $this->request->getPost('POSTE');
		$TYPE_INSTITUTION_ID = $this->request->getPost('TYPE_INSTITUTION_ID');
		$INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
		$SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
		$ID_PILIER = $this->request->getPost('ID_PILIER');
		$OBJECTIF_VISION_ID = $this->request->getPost('OBJECTIF_VISION_ID');
		$AXE_PND_ID = $this->request->getPost('AXE_PND_ID');
		$PROGRAMME_PRIORITAIRE_ID = $this->request->getPost('PROGRAMME_PRIORITAIRE_ID');
		$CHAPITRE_ID = $this->request->getPost('CHAPITRE_ID');
		$ARTICLE_ID = $this->request->getPost('ARTICLE_ID');
		$PARAGRAPHE_ID = $this->request->getPost('PARAGRAPHE_ID');
		$LITTERA_ID = $this->request->getPost('LITTERA_ID');
		$SOUS_LITTERA_ID = $this->request->getPost('SOUS_LITTERA_ID');
		$DIVISION_ID = $this->request->getPost('DIVISION_ID');
		$GROUPE_ID = $this->request->getPost('GROUPE_ID');
		$CLASSE_ID = $this->request->getPost('CLASSE_ID');
		$PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
		$ACTION_ID = $this->request->getPost('ACTION_ID');
		$COSTAB_ACTIVITE_ID = $this->request->getPost('COSTAB_ACTIVITE_ID');
		$CODE_NOMENCLATURE_BUDGETAIRE_ID = $this->request->getPost('CODE_NOMENCLATURE_BUDGETAIRE_ID');
		$PAP_ACTIVITE_ID = $this->request->getPost('PAP_ACTIVITE_ID');
		$INDICATEUR_PND_ID = $this->request->getPost('INDICATEUR_PND_ID');
		$TACHE = $this->request->getPost('TACHE');
		$RESULTATS_ATTENDUS = $this->request->getPost('RESULTATS_ATTENDUS');
		$STRUTURE_RESPONSABLE_TACHE_ID = $this->request->getPost('STRUTURE_RESPONSABLE_TACHE_ID');
		$GRANDE_MASSE_ID = $this->request->getPost('GRANDE_MASSE_ID');
		$ANNEE_BUDGETAIRE_ID = $this->request->getPost('ANNEE_BUDGETAIRE_ID');
		$COUT_UNITAIRE_BIF = $this->request->getPost('COUT_UNITAIRE_BIF');
		$UNITE = $this->request->getPost('UNITE');
		$QT1 = $this->request->getPost('QT1');
		$QT2 = $this->request->getPost('QT2');
		$QT3 = $this->request->getPost('QT3');
		$QT4 = $this->request->getPost('QT4');

		$rules=[
			'MOTIF_TACHE_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'INSTITUTION_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'SOUS_TUTEL_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'ID_PILIER' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'OBJECTIF_VISION_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'AXE_PND_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'CHAPITRE_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'ARTICLE_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'PARAGRAPHE_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'LITTERA_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'SOUS_LITTERA_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'DIVISION_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'GROUPE_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'CLASSE_ID' => [
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
			'CODE_NOMENCLATURE_BUDGETAIRE_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'TACHE' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'GRANDE_MASSE_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'ANNEE_BUDGETAIRE_ID' => [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			],
			'COUT_UNITAIRE_BIF' => [
				'rules' => 'required|numeric',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
					'numeric' => '<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>'
				]
			],
			'QT1' => [
				'rules' => 'required|numeric',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
					'numeric' => '<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>'
				]
			],
			'QT2' => [
				'rules' => 'required|numeric',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
					'numeric' => '<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>'
				]
			],
			'QT3' => [
				'rules' => 'required|numeric',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
					'numeric' => '<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>'
				]
			],
			'QT4' => [
				'rules' => 'required|numeric',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
					'numeric' => '<font style="color:red;size:2px;">Seules les valeurs numeriques sont permises</font>'
				]
			]
		];

		//validate pap activite
		if($TYPE_INSTITUTION_ID==2)
		{
			$rules['PAP_ACTIVITE_ID'] = [
				'rules' => 'required',
				'errors' => [
					'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
				]
			];
		}
		//validate nom, prenom and poste
		if(!empty($MOTIF_TACHE_ID))
		{
			if($MOTIF_TACHE_ID == 2 || $MOTIF_TACHE_ID == 3)
			{
				$NOM = $this->request->getPost('NOM');
				$PRENOM = $this->request->getPost('PRENOM');
				$POSTE = $this->request->getPost('POSTE');
				$rules['NOM'] = [
					'rules' => 'required|alpha',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
						'alpha' => '<font style="color:red;size:2px;">Seules les valeurs alphabetiques sont permises</font>'
					]
				];
				$rules['PRENOM'] = [
					'rules' => 'required|alpha',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
						'alpha' => '<font style="color:red;size:2px;">Seules les valeurs alphabetiques sont permises</font>'
					]
				];
				$rules['POSTE'] = [
					'rules' => 'required|alpha',
					'errors' => [
						'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
						'alpha' => '<font style="color:red;size:2px;">Seules les valeurs alphabetiques sont permises</font>'
					]
				];

			}
		}

		//validate lettre d'autorisation
		$error_PATH_LETTRE_AUTORISATION = "";
		$maxFileSize = 10 * 1024 * 1024; //10Mb
		if(!$PATH_LETTRE_AUTORISATION || !$PATH_LETTRE_AUTORISATION->isValid())
		{
			$error_PATH_LETTRE_AUTORISATION = '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>';
		}
		if($PATH_LETTRE_AUTORISATION->getSize() > $maxFileSize)
		{
			$error_PATH_LETTRE_AUTORISATION = '<font style="color:red;size:2px;">Fichier trop volumineux, veuillez sélectionner un fichier de moins de 10Mb</font>';
		}
		$this->validation->setRules($rules);
		if($this->validation->withRequest($this->request)->run())
		{
			//upload letter d'autorization sur server
			$PATH_LETTRE_AUTORISATION=$this->uploadFile('PATH_LETTRE_AUTORISATION','double_commande_new',$PATH_LETTRE_AUTORISATION);

    		//get le code budgetaire
			$sql_code_budg='SELECT CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE FROM inst_institutions_ligne_budgetaire WHERE 1 AND CODE_NOMENCLATURE_BUDGETAIRE_ID ='.$CODE_NOMENCLATURE_BUDGETAIRE_ID.' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE_ID';
			$resCodeBudgs = $this->ModelPs->getRequeteOne("CALL `getTable`('" . $sql_code_budg . "')");
			$CODE_NOMENCLATURE_BUDGETAIRE = $resCodeBudgs['CODE_NOMENCLATURE_BUDGETAIRE'];

			$Q_TOTAL = $QT1 + $QT2 + $QT3 + $QT4;

			$table_tache='ptba_tache';

			$columsinsert="INSTITUTION_ID,SOUS_TUTEL_ID,ID_PILIER,OBJECTIF_VISION_ID,AXE_PND_ID,PROGRAMME_ID,ACTION_ID,CODE_NOMENCLATURE_BUDGETAIRE_ID,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ID,SOUS_LITTERA_ID,DIVISION_ID,GROUPE_ID,CLASSE_ID,DESC_TACHE,Q_TOTAL,QT1,QT2,QT3,QT4,COUT_UNITAIRE,GRANDE_MASSE_ID,ANNEE_BUDGETAIRE_ID,IS_NOUVEAU";
			$IS_NOUVEAU = 1;
			$datatoinsert=$INSTITUTION_ID.",".$SOUS_TUTEL_ID.",".$ID_PILIER.",".$OBJECTIF_VISION_ID.",".$AXE_PND_ID.",".$PROGRAMME_ID.",".$ACTION_ID.",".$CODE_NOMENCLATURE_BUDGETAIRE_ID.",".$CODE_NOMENCLATURE_BUDGETAIRE.",".$ARTICLE_ID.",".$SOUS_LITTERA_ID.",".$DIVISION_ID.",".$GROUPE_ID.",".$CLASSE_ID.",'".str_replace("'", "\'", $TACHE)."',".$Q_TOTAL.",".$QT1.",".$QT2.",".$QT3.",".$QT4.",".$COUT_UNITAIRE_BIF.",".$GRANDE_MASSE_ID.",".$ANNEE_BUDGETAIRE_ID.",".$IS_NOUVEAU;

			if($PAP_ACTIVITE_ID){
				$columsinsert.=",PAP_ACTIVITE_ID";
				$datatoinsert.=",".$PAP_ACTIVITE_ID;
			}
			if($PROGRAMME_PRIORITAIRE_ID){
				$columsinsert.=",PROGRAMME_PRIORITAIRE_ID";
				$datatoinsert.=",".$PROGRAMME_PRIORITAIRE_ID;
			}
			if($COSTAB_ACTIVITE_ID){
				$columsinsert.=",COSTAB_ACTIVITE_ID";
				$datatoinsert.=",".$COSTAB_ACTIVITE_ID;
			}
			if($INDICATEUR_PND_ID){
				$columsinsert.=",PND_INDICATEUR_ID";
				$datatoinsert.=",".$INDICATEUR_PND_ID;
			}
			if($RESULTATS_ATTENDUS){
				$columsinsert.=",RESULTAT_ATTENDUS_TACHE";
				$datatoinsert.=",'".str_replace("'", "\'", $RESULTATS_ATTENDUS)."'";
			}
			if($UNITE){
				$columsinsert.=",UNITE";
				$datatoinsert.=",'".str_replace("'", "\'", $UNITE)."'";
			}
			if($STRUTURE_RESPONSABLE_TACHE_ID){
				$columsinsert.=",STRUTURE_RESPONSABLE_TACHE_ID";
				$datatoinsert.=",".$STRUTURE_RESPONSABLE_TACHE_ID;
			}

			$PTBA_TACHE_ID= $this->save_all_table($table_tache,$columsinsert,$datatoinsert);

			
			if($MOTIF_TACHE_ID == 2 || $MOTIF_TACHE_ID == 3)
			{
				$table_ptba_motif='ptba_motif_nouvelle_tache';
				$columsinsert1="PTBA_TACHE_ID,MOTIF_TACHE_ID,USER_ID,NOM,PRENOM,POSTE,PATH_DOC_AUTORISATION";
				$datatoinsert1="".$PTBA_TACHE_ID.",".$MOTIF_TACHE_ID.",".$USER_ID.",'".$NOM."','".$PRENOM."','".$POSTE."','".$PATH_LETTRE_AUTORISATION."'";
				$this->save_all_table($table_ptba_motif,$columsinsert1,$datatoinsert1);
			}else
			{
				$table_ptba_motif='ptba_motif_nouvelle_tache';
				$columsinsert2="PTBA_TACHE_ID,MOTIF_TACHE_ID,USER_ID,PATH_DOC_AUTORISATION";
				$datatoinsert2="".$PTBA_TACHE_ID.",".$MOTIF_TACHE_ID.",".$USER_ID.",'".$PATH_LETTRE_AUTORISATION."'";
				$this->save_all_table($table_ptba_motif,$columsinsert2,$datatoinsert2);
			}
			
			$response = [
				'message' => '<font style="color:green;size:2px;">'.lang('messages_lang.Enregistrer_succes_msg').'</font>'
			];
			return $this->response->setJSON($response);
			
		}
		else{
			$errors = []; 
			foreach ($rules as $field => $rule) {
				$error = $this->validation->getError($field);
				if ($error !== null) {
					$errors[$field] = $error;
				}
			}
			$errors['PATH_LETTRE_AUTORISATION'] = $error_PATH_LETTRE_AUTORISATION;

			$response = [
				'errors' => $errors
			];

			return $this->response->setJSON($response);
		}
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $bindparams;
	}

	public function save_all_table($table,$columsinsert,$datacolumsinsert)
	{
		$bindparms=[$table,$columsinsert,$datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams =[$table,$columsinsert,$datacolumsinsert];
		$result=$this->ModelPs->getRequeteOne($insertReq,$tableparams);
		return $id=$result['id'];
	}

	public function uploadFile($fieldName=NULL, $folder=NULL, $prefix = NULL): string
	{
	    $prefix = ($prefix === '') ? uniqid() : $prefix;
	    $path = '';

	    $file = $this->request->getFile($fieldName);

	    if ($file->isValid() && !$file->hasMoved()) {
	      $newName = uniqid(). '' . date('ymdhis') . '.' . $file->getExtension();
	      $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
	      $path = 'uploads/' . $folder . '/' . $newName;
	    }
	    return $path;
	}
}

?>