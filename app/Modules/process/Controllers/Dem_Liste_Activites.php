<?php
/*** 
    * HAVYARIMANA Jean Thierry
    *Titre: LISTE ET AJOUT DES ACTIVITES DANS LE MODULE PROCESS
    *Email: thierry.havyarimana@mediabox.bi
    *Date: 07 Decembre,2023

    Modifier par 
    NIYONGERE James
     *Titre: LISTE ET AJOUT DES ACTIVITES DANS LE MODULE PROCESS
    *Email: james.niyongere@mediabox.bi
*Date: 08-02,2024___
**/
namespace  App\Modules\process\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;

class Dem_Liste_Activites extends BaseController
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
    $user_id='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
      {
        return redirect('Login_Ptba/homepage');
      }

      $profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      if($profil == 1)
      {
        //Sélectionner les institutions       
        $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', '1', '`DESCRIPTION_INSTITUTION` ASC');
        $data['instit'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);
        $data['profil'] = $profil;
        return view('App\Modules\process\Views\Dem_Liste_Activites_View',$data);
      }else{
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

        //Sélectionner l'utilisateur connecté
        $user = $this->getBindParms('USER_ID , INSTITUTION_ID', 'user_users', 'USER_ID ='.$user_id.'' , 'USER_ID DESC');
        $getuser = $this->ModelPs->getRequeteOne($psgetrequete, $user);

        //Sélectionner les institutions
        $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', 'INSTITUTION_ID='.$getuser['INSTITUTION_ID'].'', '`DESCRIPTION_INSTITUTION` ASC');
        $data['instit'] = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);
        $data['profil'] = $profil; 
        return view('App\Modules\process\Views\Dem_Liste_Activites_View',$data);
      }
    }else{
      return redirect('Login_Ptba');
    }
  }


  //Liste View Activites
  public function activite_demande_index($value='')
  {

    // print_r('expression');die();

    $session  = \Config\Services::session();
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }
    else
    {
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data=$this->urichk();

    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_PROGRAMME`,`INTITULE_PROGRAMME`', 'inst_institutions_programmes', '1', '`INTITULE_PROGRAMME` ASC');
    $data['instit'] = $this->ModelPs->getRequete($psgetrequete, $bindparams);

    $action_inst = $this->getBindParms('`SOUS_TUTEL_ID`,`CODE_SOUS_TUTEL`,`DESCRIPTION_SOUS_TUTEL`', 'inst_institutions_sous_tutel', '1', '`DESCRIPTION_SOUS_TUTEL` ASC');
    $data['inst_tutel'] =$this->ModelPs->getRequete($psgetrequete, $action_inst);

    return view('App\Modules\process\Views\Activites_Demandes_List_View',$data);   
  }



  // recuperation des donnees pour la modification
  public function Get_one_data($id)
  {
    $data=$this->urichk();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $session  = \Config\Services::session();
    $USER_ID ='';
    
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }else{

      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    //récupérer INSTITUTION_ID de la table user_users à partir de $demande
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    //
    $getId_programme = $this->getBindParms('CODE_MINISTERE,DEMANDE_ID', 'ptba_programmation_budgetaire_tempo', 'PTBA_PROGR_BUDG_ID_Tempo='.$id , 'CODE_MINISTERE DESC');
    $Code_institution = $this->ModelPs->getRequeteOne($callpsreq, $getId_programme);
    // print_r($Code_institution);die();
    //GER INSTITUTION

    //
    $getuser = $this->getBindParms('INSTITUTION_ID', 'user_affectaion', 'USER_ID='.$USER_ID , 'INSTITUTION_ID DESC');
    $user = $this->ModelPs->getRequeteOne($callpsreq, $getuser);

    $bind_instution_sous_tutel = $this->getBindParms('SOUS_TUTEL_ID ,DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'INSTITUTION_ID='.$user['INSTITUTION_ID'].'', 'SOUS_TUTEL_ID DESC');
    $data['inst_sous_tutel']= $this->ModelPs->getRequete($callpsreq, $bind_instution_sous_tutel);

    $bind_programme = $this->getBindParms('PROGRAMME_ID ,INTITULE_PROGRAMME', 'inst_institutions_programmes', 'INSTITUTION_ID='.$user['INSTITUTION_ID'].'', 'PROGRAMME_ID DESC');
    $data['inst_program']= $this->ModelPs->getRequete($callpsreq, $bind_programme);

    $action_inst = $this->getBindParms('ACTION_ID,OBJECTIF_ACTION,LIBELLE_ACTION', 'inst_institutions_actions', '1', 'LIBELLE_ACTION ASC');
    $data['inst_action'] =$this->ModelPs->getRequete($callpsreq, $action_inst);
    //récuperer les codes et intitulés des institutions

    $bind_instit = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID='.$Code_institution['CODE_MINISTERE'].'', 'INSTITUTION_ID DESC');
    $instit= $this->ModelPs->getRequeteOne($callpsreq, $bind_instit);

    $data['INSTITUTION_ID'] = $instit['INSTITUTION_ID'];
    $data['code_instit'] = $instit['CODE_INSTITUTION'];
    $data['descr_instit'] = $instit['DESCRIPTION_INSTITUTION'];
    $data['Id_demande_up'] = $Code_institution['DEMANDE_ID'];

    //Récuperer les divisions fonctionnelles
    $division = $this->getBindParms('DIVISION_ID,CODE_DIVISION,LIBELLE_DIVISION', 'class_fonctionnelle_division', '1', 'CODE_DIVISION ASC');
    $data['get_division'] = $this->ModelPs->getRequete($callpsreq, $division);

    //Sélectionner les motifs de création 
    $bindparams = $this->getBindParms('`MOTIF_ACTIVITE_ID`,`DESCR_MOTIF_ACTIVITE`', 'motif_creation_activite', '1', '`DESCR_MOTIF_ACTIVITE` ASC');
    $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bindparams);

    //Sélectionner code article
    $economique_article = $this->getBindParms('`ARTICLE_ID`,`LIBELLE_ARTICLE`', 'class_economique_article', '1', '`LIBELLE_ARTICLE` ASC');
    $data['article_economique'] =$this->ModelPs->getRequete($callpsreq, $economique_article);

    //Sélectionner division
    $division_fonctionnelle = $this->getBindParms('`DIVISION_ID`,CODE_DIVISION,`LIBELLE_DIVISION`', 'class_fonctionnelle_division', '1', '`LIBELLE_DIVISION` ASC');
    $data['fonctionnelle_division'] =$this->ModelPs->getRequete($callpsreq, $division_fonctionnelle);

    //Sélectionner groupe fonctionnelle
    $groupe_fonctionnelle = $this->getBindParms('`GROUPE_ID`,CODE_GROUPE,`LIBELLE_GROUPE`', 'class_fonctionnelle_groupe', '1', '`LIBELLE_GROUPE` ASC');
    $data['fonctionnelle_groupe'] =$this->ModelPs->getRequete($callpsreq, $groupe_fonctionnelle);

    //Sélectionner classe fonctionnelle
    $classe_fonctionnelle = $this->getBindParms('`CLASSE_ID`,CODE_CLASSE,`LIBELLE_CLASSE`', 'class_fonctionnelle_classe', '1', '`LIBELLE_CLASSE` ASC');
    $data['fonctionnelle_classe'] =$this->ModelPs->getRequete($callpsreq,  $classe_fonctionnelle);

    //Sélectionner classe fonctionnelle
    $classemasse = $this->getBindParms('`GRANDE_MASSE_ID`,`DESCRIPTION_GRANDE_MASSE`', 'inst_grande_masse', '1', '`DESCRIPTION_GRANDE_MASSE` ASC');
    $data['masse_classe'] =$this->ModelPs->getRequete($callpsreq,  $classemasse);

    // Get Chapitre
    $chapitre = $this->getBindParms('`CHAPITRE_ID`,`CODE_CHAPITRE`,`LIBELLE_CHAPITRE`', 'class_economique_chapitre', '1', '`LIBELLE_CHAPITRE` ASC');
    $data['chapitres'] =$this->ModelPs->getRequete($callpsreq, $chapitre);

    //Sélectionner grandes masses
    $classgrandes_masse = $this->getBindParms('`GRANDE_MASSE_ID`,`DESCRIPTION_GRANDE_MASSE`', 'inst_grande_masse', '1','`DESCRIPTION_GRANDE_MASSE` ASC');
    $data['masse'] =$this->ModelPs->getRequete($callpsreq,$classgrandes_masse);

    $Activite= $this->getBindParms('PARAGRAPHE,LITTERA,CHAPITRES,INTITULE_LIGNE,SOUS_TUTEL_ID,INTITULE_NATURE_ECONOMIQUE,CODE_ACTION,INTITULE_ARTICLE_ECONOMIQUE,CODE_NOMENCLATURE_BUDGETAIRE,CODE_PROGRAMME,PTBA_PROGR_BUDG_ID_Tempo,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,COUT_UNITAIRE_BIF,UNITE,QT1,QT2,QT3,QT4,T1,T2,T3,T4,GRANDE_MASSE_BP,RESPONSABLE' , 'ptba_programmation_budgetaire_tempo', 'PTBA_PROGR_BUDG_ID_Tempo='.$id,'PTBA_PROGR_BUDG_ID_Tempo ASC');
    $data['activites']=$this->ModelPs->getRequeteOne($callpsreq, $Activite);
    $data['set_action_id']= null;
    $data['set_code_budg']= null;
    $data['set_group_id']= null;
    $data['set_class_id']= null;
    $data['code_Buget'] = array();

    $data['get_class'] = array();
    $data['get_group'] = array();

    $get_progr = $this->getBindParms('CODE_PROGRAMME,PROGRAMME_ID,INTITULE_PROGRAMME','inst_institutions_programmes','CODE_PROGRAMME = '.$data['activites']['CODE_PROGRAMME'],' CODE_PROGRAMME ASC');
    $data['get_prgr']= $this->ModelPs->getRequeteOne($callpsreq, $get_progr);

    $get_action = $this->getBindParms('ACTION_ID,OBJECTIF_ACTION,LIBELLE_ACTION', 'inst_institutions_actions', 'CODE_ACTION = '.$data['activites']['CODE_ACTION'], 'LIBELLE_ACTION ASC');
    $data['get_actio'] =$this->ModelPs->getRequeteOne($callpsreq, $get_action);
   // print_r($data['get_actio']);die();

    $get_chap = $this->getBindParms('ACTION_ID,OBJECTIF_ACTION,LIBELLE_ACTION', 'inst_institutions_actions', 'CODE_ACTION = '.$data['activites']['CODE_ACTION'], 'LIBELLE_ACTION ASC');
    $data['get_chapitre'] =$this->ModelPs->getRequeteOne($callpsreq, $get_chap);

    $get_eco = $this->getBindParms('SOUS_LITTERA_ID,LITTERA_ID,CODE_SOUS_LITTERA,LIBELLE_SOUS_LITTERA', 'class_economique_sous_littera', '1' ,'CODE_SOUS_LITTERA ASC');
    $data['economie'] =$this->ModelPs->getRequete($callpsreq, $get_eco);

    $ge_art = $this->getBindParms('ARTICLE_ID,CHAPITRE_ID,CODE_ARTICLE,LIBELLE_ARTICLE', 'class_economique_article', '1', 'ARTICLE_ID ASC');
    $data['article'] =$this->ModelPs->getRequete($callpsreq, $ge_art);

    $get_paragr = $this->getBindParms('PARAGRAPHE_ID,ARTICLE_ID,CODE_PARAGRAPHE,LIBELLE_PARAGRAPHE', 'class_economique_paragraphe', '1', 'LIBELLE_PARAGRAPHE ASC');
    $data['get_paragraphe'] =$this->ModelPs->getRequete($callpsreq, $get_paragr);

    $get_litera = $this->getBindParms('LITTERA_ID,PARAGRAPHE_ID,CODE_LITTERA,LIBELLE_LITTERA', 'class_economique_littera', '1', 'LIBELLE_LITTERA ASC');
    $data['littera_get'] =$this->ModelPs->getRequete($callpsreq, $get_litera);


    return view('App\Modules\process\Views\Dem_Activites_Update_View',$data);

  }
  public function Confirmation($value='')
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

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data=$this->urichk();
    return view('App\Modules\process\Views\Dem_Activites_add_Confirmation_View',$data); 
  }

  // listes des Activites demandes ju
  public function Liste_activite_demandes($value=''){
    $session  = \Config\Services::session();
    $user_id ='';
    $criteres="";
    $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    }else{
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    
    // $callpsreq = "CALL `getRequete`(?,?,?,?);";

    //$callpsreq = "CALL `getRequete`(?,?,?,?);";
    //AND INSTITUTION_ID IN
    $ID_SOUS_TUTEL=$this->request->getpost('PROGRAMME_ID');
    $institution='(SELECT `INSTITUTION_ID` FROM `user_affectaion` WHERE `USER_ID`='.$user_id.')';
    $ID_INSTITUTION= $this->ModelPs->getRequeteOne("CALL `getTable`('".$institution."')");


    $Institutions='SELECT CODE_INSTITUTION FROM  inst_institutions WHERE INSTITUTION_ID="'.$ID_INSTITUTION['INSTITUTION_ID'].'"';
    // $callpsreq = "CALL `getTable`($bind_act1);";
    $CODE_INSTITUTION= $this->ModelPs->getRequeteOne("CALL `getTable`('".$Institutions."')");

    $profil_niveau_ID=$session->get('SESSION_SUIVIE_PTBA_PROFIL_NIVEAU_ID');
    $niveau_visualition_ID=$session->get('SESSION_SUIVIE_PTBA_NIVEAU_VISUALISATION_ID');

    if($niveau_visualition_ID==2){
      $profil = $session->get('SESSION_SUIVIE_PTBA_PROFIL_NIVEAU_ID');
      $institution.=' AND CODE_INSTITUTION LIKE "'.$CODE_INSTITUTION['CODE_INSTITUTION'].'%"'; 

      $db = db_connect();
      $requetedebase='SELECT b.NATURE_ECONOMIQUE,b.INTITULE_NATURE_ECONOMIQUE,sous.LIBELLE_SOUS_LITTERA,b.CODE_MINISTERE,b.INTITULE_MINISTERE,b.ARTICLE_ECONOMIQUE,b.LIBELLE_ACTION,b.CODE_PROGRAMME,b.CODES_PROGRAMMATIQUE,e.LIBELLE_ARTICLE,b.UNITE,b.ACTIVITES,b.RESULTATS_ATTENDUS,
      b.QT1,b.QT2,QT3,b.QT4,b.T1,b.T2,b.T3,b.T4,b.COUT_UNITAIRE_BIF,
      b.GRANDE_MASSE_BP,b.RESPONSABLE ,
      g.DESCRIPTION_GRANDE_MASSE,b.CODE_NOMENCLATURE_BUDGETAIRE,b.DIVISION_FONCTIONNELLE,
      d.LIBELLE_DIVISION,e.CODE_ARTICLE,eco.CODE_LITTERA,
      groupe.LIBELLE_GROUPE,b.GROUPE_FONCTIONNELLE,i.DESCRIPTION_INSTITUTION,ins.INTITULE_PROGRAMME,
      act.LIBELLE_ACTION,b.CLASSE_FONCTIONNELLE,cl.LIBELLE_CLASSE,eco.LIBELLE_LITTERA
      FROM ptba_programmation_budgetaire b LEFT JOIN class_economique_article e ON e.ARTICLE_ID=b.INTITULE_ARTICLE_ECONOMIQUE 
      LEFT JOIN inst_grande_masse g ON g.GRANDE_MASSE_ID=b.INTITULE_DES_GRANDES_MASSES 
      LEFT JOIN class_fonctionnelle_division d ON d.DIVISION_ID=b.INTITULE_DIVISION_FONCTIONNELLE
      LEFT JOIN class_fonctionnelle_groupe groupe ON groupe.GROUPE_ID=b.INTITULE_GROUPE_FONCTIONNELLE
      LEFT JOIN inst_institutions i ON i.CODE_INSTITUTION=b.CODE_MINISTERE 
      LEFT JOIN inst_institutions_programmes ins ON ins.CODE_PROGRAMME=b.CODE_PROGRAMME 
      LEFT JOIN inst_institutions_actions act ON act.CODE_ACTION=b.CODE_ACTION
      LEFT JOIN class_fonctionnelle_classe cl ON cl.CLASSE_ID=b.INTITULE_CLASSE_FONCTIONNELLE
      LEFT JOIN  class_economique_littera eco ON eco.LITTERA_ID=b.LITTERA LEFT JOIN class_economique_sous_littera sous ON sous.SOUS_LITTERA_ID=b.INTITULE_NATURE_ECONOMIQUE
      WHERE  b.CODE_MINISTERE="'.$ID_SOUS_TUTEL.'" AND b.CODE_MINISTERE='.$institution;
    }

    $requetedebase='SELECT b.NATURE_ECONOMIQUE,b.INTITULE_NATURE_ECONOMIQUE,sous.LIBELLE_SOUS_LITTERA,b.CODE_MINISTERE,b.INTITULE_MINISTERE,b.ARTICLE_ECONOMIQUE,b.CODES_PROGRAMMATIQUE,b.LIBELLE_ACTION,b.CODE_PROGRAMME,e.LIBELLE_ARTICLE,b.UNITE,b.ACTIVITES,b.RESULTATS_ATTENDUS,
    b.QT1,b.QT2,QT3,b.QT4,b.T1,b.T2,b.T3,b.T4,b.COUT_UNITAIRE_BIF,
    b.GRANDE_MASSE_BP,b.RESPONSABLE ,
    g.DESCRIPTION_GRANDE_MASSE,b.CODE_NOMENCLATURE_BUDGETAIRE,b.DIVISION_FONCTIONNELLE,
    d.LIBELLE_DIVISION,e.CODE_ARTICLE,eco.CODE_LITTERA,
    groupe.LIBELLE_GROUPE,b.GROUPE_FONCTIONNELLE,i.DESCRIPTION_INSTITUTION,ins.INTITULE_PROGRAMME,
    act.LIBELLE_ACTION,b.CLASSE_FONCTIONNELLE,cl.LIBELLE_CLASSE,eco.LIBELLE_LITTERA
    FROM ptba_programmation_budgetaire b LEFT JOIN class_economique_article e ON e.ARTICLE_ID=b.INTITULE_ARTICLE_ECONOMIQUE 
    LEFT JOIN inst_grande_masse g ON g.GRANDE_MASSE_ID=b.INTITULE_DES_GRANDES_MASSES 
    LEFT JOIN class_fonctionnelle_division d ON d.DIVISION_ID=b.INTITULE_DIVISION_FONCTIONNELLE
    LEFT JOIN class_fonctionnelle_groupe groupe ON groupe.GROUPE_ID=b.INTITULE_GROUPE_FONCTIONNELLE
    LEFT JOIN inst_institutions i ON i.CODE_INSTITUTION=b.CODE_MINISTERE 
    LEFT JOIN inst_institutions_programmes ins ON ins.CODE_PROGRAMME=b.CODE_PROGRAMME 
    LEFT JOIN inst_institutions_actions act ON act.CODE_ACTION=b.CODE_ACTION
    LEFT JOIN class_fonctionnelle_classe cl ON cl.CLASSE_ID=b.INTITULE_CLASSE_FONCTIONNELLE
    LEFT JOIN  class_economique_littera eco ON eco.LITTERA_ID=b.LITTERA LEFT JOIN class_economique_sous_littera sous ON sous.SOUS_LITTERA_ID=b.INTITULE_NATURE_ECONOMIQUE';
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
   $order_column=array('b.LIBELLE_ACTION','b.ARTICLE_ECONOMIQUE','b.CODE_MINISTERE','e.LIBELLE_ARTICLE');
   $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].'  '. $_POST['order']['0']['dir'] : ' ORDER BY b.PTBA_PROGR_BUDG_ID  DESC';
   $search = !empty($_POST['search']['value']) ? (' AND (b.LIBELLE_ACTION LIKE "%'.$var_search.'%" OR b.ARTICLE_ECONOMIQUE LIKE "%'.$var_search.'%" OR b.CODE_MINISTERE LIKE "%'.$var_search.'%" OR b.CODE_MINISTERE LIKE "%'.$var_search.'%" OR e.LIBELLE_ARTICLE LIKE "%'.$var_search.'%")') : '';

   $conditions=$critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
   $conditionsfilter = $critaire . ' ' . $search . ' ' . $group; 

   $requetedebases=$requetedebase .' '. $conditions;
   $requetedebasefilter=$requetedebase.' '.$conditionsfilter;
   $query_secondaire = "CALL `getTable`('".$requetedebases."');";
   $fetch_datas = $this->ModelPs->datatable($query_secondaire);
   //echo json_encode($fetch_datas);
   $data = array();
   $i=1;

   foreach($fetch_datas as $row)
   {
     $sub_array=array();
     $sub_array[]= $i++;
     //$sub_array[]=$row->DESCR_PILIER;
     $sub_array[]=$row->DESCRIPTION_INSTITUTION;
     $sub_array[]=$row->INTITULE_PROGRAMME;
     $sub_array[]=$row->LIBELLE_ACTION;
     $sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
     $sub_array[]=$row->CODES_PROGRAMMATIQUE;
     $sub_array[]=$row->ACTIVITES;
     $sub_array[]=$row->RESULTATS_ATTENDUS;
     $sub_array[]=$row->CODE_ARTICLE ;
     $sub_array[]=$row->NATURE_ECONOMIQUE;
     $sub_array[]=$row->LIBELLE_ARTICLE;
     $sub_array[]=$row->LIBELLE_SOUS_LITTERA;
     $sub_array[]=$row->DIVISION_FONCTIONNELLE;
     $sub_array[]=$row->LIBELLE_DIVISION;
     $sub_array[]=$row->GROUPE_FONCTIONNELLE;
     $sub_array[]=$row->LIBELLE_GROUPE;
     $sub_array[]=$row->CLASSE_FONCTIONNELLE;
     $sub_array[]=$row->LIBELLE_CLASSE;
     $sub_array[]=$row->COUT_UNITAIRE_BIF;
     $sub_array[]=$row->UNITE;
     $sub_array[]=$row->QT1  ;
     $sub_array[]=$row->QT2;
     $sub_array[]=$row->QT3;
     $sub_array[]=$row->QT4;
     $sub_array[]=$row->T1 ;
     $sub_array[]=$row->T2 ;
     $sub_array[]=$row->T3 ;
     $sub_array[]=$row->T4 ;
     $sub_array[]=$row->DESCRIPTION_GRANDE_MASSE;
     $sub_array[]=$row->GRANDE_MASSE_BP;
     $sub_array[]=$row->RESPONSABLE ;
     $data[] = $sub_array;
   }
   // fin du boucle
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
public function listing_data($value = 0)
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

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }

  $requetedebase='SELECT b.NATURE_ECONOMIQUE,b.INTITULE_NATURE_ECONOMIQUE,sous.LIBELLE_SOUS_LITTERA,b.CODE_MINISTERE,b.INTITULE_MINISTERE,b.ARTICLE_ECONOMIQUE,b.LIBELLE_ACTION,b.PTBA_PROGR_BUDG_ID_Tempo,b.CODE_PROGRAMME,b.CODES_PROGRAMMATIQUE,e.LIBELLE_ARTICLE,b.UNITE,b.ACTIVITES,b.RESULTATS_ATTENDUS,
  b.QT1,b.QT2,QT3,b.QT4,b.T1,b.T2,b.T3,b.T4,b.COUT_UNITAIRE_BIF,
  b.GRANDE_MASSE_BP,b.RESPONSABLE ,
  g.DESCRIPTION_GRANDE_MASSE,b.CODE_NOMENCLATURE_BUDGETAIRE,b.DIVISION_FONCTIONNELLE,
  d.LIBELLE_DIVISION,e.CODE_ARTICLE,eco.CODE_LITTERA,
  groupe.LIBELLE_GROUPE,b.GROUPE_FONCTIONNELLE,b.USER_ID,i.DESCRIPTION_INSTITUTION,ins.INTITULE_PROGRAMME,
  act.LIBELLE_ACTION,b.CLASSE_FONCTIONNELLE,cl.LIBELLE_CLASSE,eco.LIBELLE_LITTERA
  FROM ptba_programmation_budgetaire_tempo b LEFT JOIN class_economique_article e ON e.ARTICLE_ID=b.INTITULE_ARTICLE_ECONOMIQUE 
  LEFT JOIN inst_grande_masse g ON g.GRANDE_MASSE_ID=b.INTITULE_DES_GRANDES_MASSES 
  LEFT JOIN class_fonctionnelle_division d ON d.DIVISION_ID=b.INTITULE_DIVISION_FONCTIONNELLE
  LEFT JOIN class_fonctionnelle_groupe groupe ON groupe.GROUPE_ID=b.INTITULE_GROUPE_FONCTIONNELLE
  LEFT JOIN inst_institutions i ON i.CODE_INSTITUTION=b.CODE_MINISTERE 
  LEFT JOIN inst_institutions_programmes ins ON ins.CODE_PROGRAMME=b.CODE_PROGRAMME 
  LEFT JOIN inst_institutions_actions act ON act.CODE_ACTION=b.CODE_ACTION
  LEFT JOIN class_fonctionnelle_classe cl ON cl.CLASSE_ID=b.INTITULE_CLASSE_FONCTIONNELLE
  LEFT JOIN  class_economique_littera eco ON eco.LITTERA_ID=b.LITTERA LEFT JOIN class_economique_sous_littera sous ON sous.SOUS_LITTERA_ID=b.INTITULE_NATURE_ECONOMIQUE
  WHERE b.USER_ID="'.$user_id.'" ';

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
  $order_column=array('b.LIBELLE_ACTION','b.ARTICLE_ECONOMIQUE','b.CODE_MINISTERE','e.LIBELLE_ARTICLE');
  $order_by = isset($_POST['order']) ? ' ORDER BY '.$order_column[$_POST['order']['0']['column']].'  '. $_POST['order']['0']['dir'] : ' ORDER BY b.PTBA_PROGR_BUDG_ID_Tempo ASC';
  $search = !empty($_POST['search']['value']) ? (' AND (b.LIBELLE_ACTION LIKE "%'.$var_search.'%" OR b.ARTICLE_ECONOMIQUE LIKE "%'.$var_search.'%" OR b.CODE_MINISTERE LIKE "%'.$var_search.'%" OR b.CODE_MINISTERE LIKE "%'.$var_search.'%" OR e.LIBELLE_ARTICLE LIKE "%'.$var_search.'%")') : '';

  $conditions=$critaire.' '.$search.' '.$group.' '.$order_by.' '.$limit;
  $conditionsfilter = $critaire . ' ' . $search . ' ' . $group;

  $requetedebases=$requetedebase .' '. $conditions;
  $requetedebasefilter=$requetedebase.' '.$conditionsfilter;
  $query_secondaire = "CALL `getTable`('".$requetedebases."');";
  $fetch_datas = $this->ModelPs->datatable($query_secondaire);
  //echo json_encode($fetch_datas);
  $data = array();
  $i=1;
  foreach($fetch_datas as $row)
  {
    $sub_array=array();
    $sub_array[]= $i++;
    $sub_array[]=$row->DESCRIPTION_INSTITUTION;
    $sub_array[]=$row->INTITULE_PROGRAMME;
    $sub_array[]=$row->LIBELLE_ACTION;
    $sub_array[]=$row->CODE_NOMENCLATURE_BUDGETAIRE;
    $sub_array[]=$row->CODES_PROGRAMMATIQUE;
    $sub_array[]=$row->ACTIVITES;
    $sub_array[]=$row->RESULTATS_ATTENDUS;
    $sub_array[]=$row->CODE_ARTICLE ;
    $sub_array[]=$row->NATURE_ECONOMIQUE;
    $sub_array[]=$row->LIBELLE_ARTICLE;
    $sub_array[]=$row->LIBELLE_SOUS_LITTERA;
    $sub_array[]=$row->DIVISION_FONCTIONNELLE;
    $sub_array[]=$row->LIBELLE_DIVISION;
    $sub_array[]=$row->GROUPE_FONCTIONNELLE;
    $sub_array[]=$row->LIBELLE_GROUPE;
    $sub_array[]=$row->CLASSE_FONCTIONNELLE;
    $sub_array[]=$row->LIBELLE_CLASSE;
    $sub_array[]=$row->COUT_UNITAIRE_BIF;
    $sub_array[]=$row->UNITE;
    $sub_array[]=$row->QT1  ;
    $sub_array[]=$row->QT2;
    $sub_array[]=$row->QT3;
    $sub_array[]=$row->QT4;
    $sub_array[]=$row->T1 ;
    $sub_array[]=$row->T2 ;
    $sub_array[]=$row->T3 ;
    $sub_array[]=$row->T4 ;
    $sub_array[]=$row->DESCRIPTION_GRANDE_MASSE;
    $sub_array[]=$row->GRANDE_MASSE_BP;
    $sub_array[]=$row->RESPONSABLE ;

    $action='<div class="dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
    <i class="fa fa-cog"></i>   Action
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">

    <li><a class="dropdown-item" data-toggle="modal" onclick="delete_row('.$row->PTBA_PROGR_BUDG_ID_Tempo.')"> <label class="text-danger"> Supprimer </label></a></li>

    </ul>
    </div>';

    $action='<div class="dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
    <i class="fa fa-cog"></i>   Action
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">

    <li><a class="dropdown-item" data-toggle="modal" onclick="delete_row('.$row->PTBA_PROGR_BUDG_ID_Tempo.')"> <label class="text-danger"> Supprimer </label></a></li>

    </ul>
    </div>';

    $sub_array[]=$action;
    $data[] = $sub_array;
  }
  // fin du boucle
  $recordsTotal = $this->ModelPs->datatable("CALL `getTable`('" . $requetedebase . "')");
  $recordsFiltered = $this->ModelPs->datatable(" CALL `getTable`('" . $requetedebasefilter . "')");
  $output = array(
    "draw" => intval($_POST['draw']),
    "recordsTotal" => count($recordsTotal),
    "recordsFiltered" => count($recordsFiltered),
    "data" => $data,
  );
  return $this->response->setJSON($output);//
  //echo json_encode($output);
}



// $Prog_ID
public function save_confirm(){

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


  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
          
  //récuperer les donnees dans la table temporaire


  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $bind_prog = $this->getBindParms('PTBA_PROGR_BUDG_ID_Tempo,DEMANDE_ID,CODE_MINISTERE,INTITULE_MINISTERE,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_PROGRAMME,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE,GRANDE_MASSE_BP,INTITULE_DES_GRANDES_MASSES,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,USER_ID', 'ptba_programmation_budgetaire_tempo',1,'PTBA_PROGR_BUDG_ID_Tempo ASC');
  $bind_prog=str_replace("\'","'",$bind_prog);
  $act= $this->ModelPs->getRequete($callpsreq, $bind_prog);


  foreach($act as $rowActivite){

    $ID_PROGR=str_replace("\\", "", $rowActivite->PTBA_PROGR_BUDG_ID_Tempo);
    $ID_DEMANDES=str_replace("\\", "", $rowActivite->DEMANDE_ID);
    $CODE_MINISTERE=str_replace("\\", "", $rowActivite->CODE_MINISTERE);
    $INTITULE_MINISTERE= str_replace("\\", "", $rowActivite->INTITULE_MINISTERE);
    $CODE_PROGRAMME= str_replace("\\", "", $rowActivite->CODE_PROGRAMME);

    $INTITULE_PROGRAMME=str_replace("\\", "", $rowActivite->INTITULE_PROGRAMME);
    $OBJECTIF_PROGRAMME= str_replace("\\", "", $rowActivite->OBJECTIF_PROGRAMME);
    $CODE_ACTION= str_replace("\\", "", $rowActivite->CODE_ACTION);
    $LIBELLE_ACTION=str_replace("\\", "", $rowActivite->LIBELLE_ACTION);
    $OBJECTIF_ACTION= str_replace("\\", "", $rowActivite->OBJECTIF_ACTION);
    $CODE_NOMENCLATURE_BUDGETAIRE= str_replace("\\", "", $rowActivite->CODE_NOMENCLATURE_BUDGETAIRE);
    $ARTICLE_ECONOMIQUE=str_replace("\\", "", $rowActivite->ARTICLE_ECONOMIQUE);
    $INTITULE_ARTICLE_ECONOMIQUE= str_replace("\\", "", $rowActivite->INTITULE_ARTICLE_ECONOMIQUE);
    $NATURE_ECONOMIQUE= $rowActivite->NATURE_ECONOMIQUE;
    $INTITULE_NATURE_ECONOMIQUE= str_replace("\\", "", $rowActivite->INTITULE_NATURE_ECONOMIQUE);
    $DIVISION_FONCTIONNELLE= str_replace("\\", "", $rowActivite->DIVISION_FONCTIONNELLE);
    $INTITULE_DIVISION_FONCTIONNELLE= str_replace("\\", "", $rowActivite->INTITULE_DIVISION_FONCTIONNELLE);
    $GROUPE_FONCTIONNELLE= str_replace("\\", "", $rowActivite->GROUPE_FONCTIONNELLE);
    $INTITULE_GROUPE_FONCTIONNELLE= str_replace("\\", "", $rowActivite->INTITULE_GROUPE_FONCTIONNELLE);
    $CLASSE_FONCTIONNELLE= str_replace("\\", "", $rowActivite->CLASSE_FONCTIONNELLE);
    $INTITULE_CLASSE_FONCTIONNELLE= str_replace("\\", "", $rowActivite->INTITULE_CLASSE_FONCTIONNELLE);
    $CODES_PROGRAMMATIQUE= str_replace("\\", "", $rowActivite->CODES_PROGRAMMATIQUE);
    $ACTIVITES= str_replace("\\", "", $rowActivite->ACTIVITES);
    $RESULTATS_ATTENDUS= str_replace("\\", "", $rowActivite->RESULTATS_ATTENDUS);
    $UNITE= str_replace("\\", "", $rowActivite->UNITE);
    $QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE= str_replace("\\", "", $rowActivite->QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE);
    $QT1= str_replace("\\", "", $rowActivite->QT1);
    $QT2= str_replace("\\", "", $rowActivite->QT2);
    $QT3= str_replace("\\", "", $rowActivite->QT3);
    $QT4= str_replace("\\", "", $rowActivite->QT4);
    $COUT_UNITAIRE_BIF= str_replace("\\", "", $rowActivite->COUT_UNITAIRE_BIF);
    $T1= str_replace("\\", "", $rowActivite->T1);
    $T2= str_replace("\\", "", $rowActivite->T2);
    $T3= str_replace("\\", "", $rowActivite->T3);
    $T4= str_replace("\\", "", $rowActivite->T4);
    $PROGRAMMATION_FINANCIERE_BIF= str_replace("\\", "", $rowActivite->PROGRAMMATION_FINANCIERE_BIF);
    $RESPONSABLE= str_replace("\\", "", $rowActivite->RESPONSABLE);
    $GRANDE_MASSE_BP= str_replace("\\", "", $rowActivite->GRANDE_MASSE_BP);
    $INTITULE_DES_GRANDES_MASSES= str_replace("\\", "", $rowActivite->INTITULE_DES_GRANDES_MASSES);
    $MONTANT_RESTANT_T1= str_replace("\\", "", $rowActivite->MONTANT_RESTANT_T1);
    $MONTANT_RESTANT_T2= str_replace("\\", "", $rowActivite->MONTANT_RESTANT_T2);
    $MONTANT_RESTANT_T3= str_replace("\\", "", $rowActivite->MONTANT_RESTANT_T3);
    $MONTANT_RESTANT_T4= str_replace("\\", "", $rowActivite->MONTANT_RESTANT_T4);


    $insertIntoTable='ptba_programmation_budgetaire';
    $columsinsert='DEMANDE_ID,
    CODE_MINISTERE,
    INTITULE_MINISTERE,
    CODE_PROGRAMME,
    INTITULE_PROGRAMME,
    OBJECTIF_PROGRAMME,
    CODE_ACTION,
    LIBELLE_ACTION,
    OBJECTIF_ACTION,
    CODE_NOMENCLATURE_BUDGETAIRE,
    ARTICLE_ECONOMIQUE,
    INTITULE_ARTICLE_ECONOMIQUE,
    NATURE_ECONOMIQUE,
    INTITULE_NATURE_ECONOMIQUE,
    DIVISION_FONCTIONNELLE,
    INTITULE_DIVISION_FONCTIONNELLE,
    GROUPE_FONCTIONNELLE,
    INTITULE_GROUPE_FONCTIONNELLE,
    CLASSE_FONCTIONNELLE,
    INTITULE_CLASSE_FONCTIONNELLE,
    CODES_PROGRAMMATIQUE,
    ACTIVITES,
    RESULTATS_ATTENDUS,
    UNITE,
    QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,
    QT1,
    QT2,
    QT3,
    QT4,
    COUT_UNITAIRE_BIF,
    T1,
    T2,
    T3,
    T4,
    PROGRAMMATION_FINANCIERE_BIF,
    RESPONSABLE,
    GRANDE_MASSE_BP,
    INTITULE_DES_GRANDES_MASSES,
    MONTANT_RESTANT_T1,
    MONTANT_RESTANT_T2,
    MONTANT_RESTANT_T3,
    MONTANT_RESTANT_T4
    ';
    $datatoinsert_Activite= "'".$ID_DEMANDES."',
    '" .str_replace("'", "\'",$CODE_MINISTERE) . "',
    '" .str_replace("'", "\'", $INTITULE_MINISTERE)."',
    '" .$CODE_PROGRAMME."',
    '" .str_replace("'", "\'",$INTITULE_PROGRAMME)."',
    '" .str_replace("'", "\'",$OBJECTIF_PROGRAMME)."',
    '" .$CODE_ACTION."',
    '" .$LIBELLE_ACTION."',
    '" .$OBJECTIF_ACTION."',
    '" .$CODE_NOMENCLATURE_BUDGETAIRE."',
    '" .$ARTICLE_ECONOMIQUE."',
    '" .str_replace("'", "\'",$INTITULE_ARTICLE_ECONOMIQUE)."',
    '" .$NATURE_ECONOMIQUE."',
    '" .str_replace("'", "\'",$INTITULE_NATURE_ECONOMIQUE)."',
    '" .$DIVISION_FONCTIONNELLE."',
    '" .str_replace("'", "\'",$INTITULE_DIVISION_FONCTIONNELLE)."',
    '" .$GROUPE_FONCTIONNELLE."',
    '" .$INTITULE_GROUPE_FONCTIONNELLE."',
    '" .$CLASSE_FONCTIONNELLE."',
    '" .str_replace("'", "\'",$INTITULE_CLASSE_FONCTIONNELLE)."',
    '" .$CODES_PROGRAMMATIQUE."',
    '" .$ACTIVITES."',
    '" .$RESULTATS_ATTENDUS."',
    '" .$UNITE."',
    '" .$QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE."',
    '" .$QT1."',
    '" .$QT2."',
    '" .$QT3."',
    '" .$QT4."',
    '" .$COUT_UNITAIRE_BIF."',
    '" .$T1."',
    '" .$T2."',
    '" .$T3."',
    '" .$T4."',
    '" .$PROGRAMMATION_FINANCIERE_BIF."',
    '" .$RESPONSABLE."',
    '" .$GRANDE_MASSE_BP."',
    '".str_replace("'", "\'", $INTITULE_DES_GRANDES_MASSES)."',
    '" . $MONTANT_RESTANT_T1 ."',
    '" . $MONTANT_RESTANT_T2 ."',
    '" . $MONTANT_RESTANT_T3."',
    '" . $MONTANT_RESTANT_T4 ."'";
    $datamessage=$this->save_all_table($insertIntoTable,$columsinsert,$datatoinsert_Activite);
    //récupérer l'ETAPE_ID à partir de la demande
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $getDemandes = $this->getBindParms('ETAPE_ID, CODE_DEMANDE', 'proc_demandes', 'ID_DEMANDE ="'.$ID_DEMANDES.'"', 'CODE_DEMANDE ASC');

    $getDemandes =\str_replace('\"','"',$getDemandes);
    $Demande = $this->ModelPs->getRequeteOne($callpsreq, $getDemandes);
    //récupérer l'action à partir de l'ETAPE_ID de la demande
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $getaction = $this->getBindParms('ACTION_ID, ETAPE_ID, MOVETO', 'proc_actions', 'ETAPE_ID ='.$Demande['ETAPE_ID'].'', 1);
    $action = $this->ModelPs->getRequeteOne($callpsreq, $getaction);
    //mettre à jour la table progr_budg_infos_supp
    $where ="ID_DEMANDE= '".$ID_DEMANDES."'";
    $insertInto='progr_budg_infos_supp';
    $colum="PTBA_PROGR_BUDG_ID =".$datamessage;
    $this->update_all_table($insertInto,$colum,$where);

    //mettre à jour la table proc_demandes(ETAPE_ID)
    $where ="ID_DEMANDE = '".$ID_DEMANDES."'";
    $insertInto='proc_demandes';
    $colum="ETAPE_ID = ".$action['MOVETO'];
    $this->update_all_table($insertInto,$colum,$where);

    //suppression dans la table temporaire
    $condition ="PTBA_PROGR_BUDG_ID_Tempo=".$rowActivite->PTBA_PROGR_BUDG_ID_Tempo;
    $table='ptba_programmation_budgetaire_tempo';
    $deleteparams =[$db->escapeString($table),$db->escapeString($condition)];
    $deleteRequete = "CALL `deleteData`(?,?);";
    $delete=$this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);

  }

  $data=$this->urichk();
  $data = [
    'message' => ''.lang('messages_lang.msg_confirm_activ').''
  ];
  session()->setFlashdata('alert', $data);
  return redirect('process/Demandes_Program_Budget');
}
	//fonction pour la suppression
public function delete()
{
  $session  = \Config\Services::session();
  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }

  $db = db_connect();  
  $RowId=$this->request->getPost("id");   
  $condition ="PTBA_PROGR_BUDG_ID_Tempo=".$RowId  ;
  $table='ptba_programmation_budgetaire_tempo';
  $deleteparams =[$db->escapeString($table),$db->escapeString($condition)];
  $deleteRequete = "CALL `deleteData`(?,?);";
  $delete=$this->ModelPs->createUpdateDelete($deleteRequete, $deleteparams);
  $data = [
    'message' => ''.lang('messages_lang.message_success_suppr').''
  ];
  session()->setFlashdata('alert', $data);
  return json_encode($data);
}


//fonction pour affichage d'une liste
public function listing()
{
  $session  = \Config\Services::session();
  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
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
    $bindparams = $this->getBindParms('`INSTITUTION_ID`,`CODE_INSTITUTION`,`DESCRIPTION_INSTITUTION`', 'inst_institutions', 'INSTITUTION_ID='.$INSTITUTION_ID, '`DESCRIPTION_INSTITUTION` ASC');
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
  $order_column=array(1,'CODE_NOMENCLATURE_BUDGETAIRE','CODES_PROGRAMMATIQUE','CODES_PROGRAMMATIQUE','ACTIVITES','RESULTATS_ATTENDUS',1,1);
  $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY CODE_NOMENCLATURE_BUDGETAIRE DESC';
  $search = !empty($_POST['search']['value']) ?  (" AND (CODE_NOMENCLATURE_BUDGETAIRE LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR RESULTATS_ATTENDUS LIKE '%$var_search%')"):'';

  /*$search = !empty($_POST['search']['value']) ? (' AND (CODE_NOMENCLATURE_BUDGETAIRE LIKE "%'.$var_search.'%" OR ACTIVITES LIKE "%' . $var_search . '%" OR RESULTATS_ATTENDUS LIKE "%' . $var_search . '%")') : '';*/
  $critaire= $critere1 ." ". $critere2 ." ". $critere3;

    //condition pour le query principale
  $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;

    // condition pour le query filter
  $conditionsfilter = $critaire . " ". $search ." " . $group;
  $requetedebase="SELECT PTBA_PROGR_BUDG_ID,CODE_MINISTERE,CODE_PROGRAMME,CODE_ACTION, CODE_NOMENCLATURE_BUDGETAIRE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,T1,T2,T3,T4 FROM ptba_programmation_budgetaire WHERE 1";
  $requetedebases=$requetedebase." ".$conditions;
  $requetedebasefilter=$requetedebase." ".$conditionsfilter;
  $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';
  //print_r($query_secondaire);exit();
  $fetch_actions = $this->ModelPs->datatable($query_secondaire);
  $data = array();
  $u=1;
  foreach ($fetch_actions as $row)
  {
    $ACTIVITES = (mb_strlen($row->ACTIVITES) > 9) ? (mb_substr($row->ACTIVITES, 0, 9) . "...<a class='btn-sm' data-toggle='modal' data-target='#activite" . $row->PTBA_PROGR_BUDG_ID . "' data-toggle='tooltip' title='Afficher'><i class='fa fa-eye'></i></a>") : $row->ACTIVITES;
    $RESULTATS_ATTENDUS = (mb_strlen($row->RESULTATS_ATTENDUS) > 9) ? (mb_substr($row->RESULTATS_ATTENDUS, 0, 9) . "...<a class='btn-sm' data-toggle='modal' data-target='#result" . $row->PTBA_PROGR_BUDG_ID . "' data-toggle='tooltip' title='Afficher'><i class='fa fa-eye'></i></a>") : $row->RESULTATS_ATTENDUS;
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

    $sub_array[] = "

    <div class='modal fade' id='activite".$row->PTBA_PROGR_BUDG_ID."'>
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
    <div class='modal fade' id='result".$row->PTBA_PROGR_BUDG_ID."'>
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
    <a href='".base_url('process/Dem_Detail_Activite/'.$row->PTBA_PROGR_BUDG_ID)."'>
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
  return $this->response->setJSON($output);//echo json_encode($output);
}

  //fonction pour ajouter
public function create($ID_DEMANDE)
{

  $data=$this->urichk();
  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $session  = \Config\Services::session();
  $USER_ID ='';

  if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
  {
    $USER_ID = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
  }else{
    return redirect('Login_Ptba');
  }

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
  
  //récupérer user_id à partir de demande qui a comme champ "ID_DEMANDE" = $ID_DEMANDE
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $getDemande  = 'SELECT ID_DEMANDE FROM proc_demandes WHERE md5(ID_DEMANDE) ="'.$ID_DEMANDE.'" ORDER BY ID_DEMANDE ASC';
  $getDemande = "CALL `getTable`('" . $getDemande . "');";
  $data['Id_demande_Row'] = $this->ModelPs->getRequeteOne($getDemande);
  // print_r($data['Id_demande_Row']);die();


    //récupérer INSTITUTION_ID de la table user_users à partir de $demande
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $getuser='SELECT INSTITUTION_ID FROM planification_demande_costab WHERE md5(ID_DEMANDE)="'.$ID_DEMANDE.'"';
  $user= $this->ModelPs->getRequeteOne("CALL `getTable`('".$getuser."')");

    // $getuser='SELECT aff.INSTITUTION_ID FROM user_users user LEFT JOIN user_affectaion aff ON aff.USER_ID=user.USER_ID WHERE user.USER_ID="'.$USER_ID.'"';
    // $user= $this->ModelPs->getRequeteOne("CALL `getTable`('".$getuser."')");
    // print_r($user);die();

  $data['ID_DEMANDE'] = $ID_DEMANDE;

  $bind_instution_sous_tutel = $this->getBindParms('SOUS_TUTEL_ID,CODE_SOUS_TUTEL,DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'INSTITUTION_ID='.$user['INSTITUTION_ID'].'', 'SOUS_TUTEL_ID DESC');
  $data['inst_sous_tutel']= $this->ModelPs->getRequete($callpsreq, $bind_instution_sous_tutel);

  $bind_programme = $this->getBindParms('PROGRAMME_ID ,INTITULE_PROGRAMME', 'inst_institutions_programmes', 'INSTITUTION_ID='.$user['INSTITUTION_ID'].'', 'PROGRAMME_ID DESC');
  $data['inst_program']= $this->ModelPs->getRequete($callpsreq, $bind_programme);
  // actions
    // $action_inst = $this->getBindParms('ACTION_ID,OBJECTIF_ACTION', 'inst_institutions_actions', '1', 'ACTION_ID DESC');
    // $data['inst_action']= $this->ModelPs->getRequete($callpsreq,$action_inst);

  $action_inst = $this->getBindParms('`ACTION_ID`,`OBJECTIF_ACTION`,`LIBELLE_ACTION`', 'inst_institutions_actions', '1', '`LIBELLE_ACTION` ASC');
  $data['inst_action'] =$this->ModelPs->getRequete($callpsreq, $action_inst);

    // Get Chapitre
  $chapitre = $this->getBindParms('`CHAPITRE_ID`,`CODE_CHAPITRE`,`LIBELLE_CHAPITRE`', 'class_economique_chapitre', '1', '`LIBELLE_CHAPITRE` ASC');
  $data['chapitres'] =$this->ModelPs->getRequete($callpsreq, $chapitre);


    // Get Chapitre
  $Paragraphe = $this->getBindParms('`PARAGRAPHE_ID`,`CODE_PARAGRAPHE`,`LIBELLE_PARAGRAPHE`', 'class_economique_paragraphe', '1', '`LIBELLE_PARAGRAPHE` ASC');
  $data['paragraphes'] =$this->ModelPs->getRequete($callpsreq, $Paragraphe);

    //

  $littera_econo = $this->getBindParms('`LITTERA_ID`,`CODE_LITTERA`,`LIBELLE_LITTERA`', 'class_economique_littera', '1', '`LIBELLE_LITTERA` ASC');
  $data['economique_littera'] =$this->ModelPs->getRequete($callpsreq, $littera_econo);
    //inst_action
    //récuperer les codes et intitulés des institutions

  $bind_instit = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION', 'inst_institutions', 'INSTITUTION_ID='.$user['INSTITUTION_ID'], 'INSTITUTION_ID DESC');
  $instit= $this->ModelPs->getRequeteOne($callpsreq, $bind_instit);
   // dd($instit);
  $data['INSTITUTION_ID'] = $instit['INSTITUTION_ID'];
  $data['code_instit'] = $instit['CODE_INSTITUTION'];
  $data['descr_instit'] = $instit['DESCRIPTION_INSTITUTION'];

    //Récuperer les divisions fonctionnelles
  $division = $this->getBindParms('DIVISION_ID,CODE_DIVISION,LIBELLE_DIVISION', 'class_fonctionnelle_division', '1', 'CODE_DIVISION ASC');
  $data['get_division'] = $this->ModelPs->getRequete($callpsreq, $division);

    //Sélectionner les motifs de création 
  $bindparams = $this->getBindParms('`MOTIF_ACTIVITE_ID`,`DESCR_MOTIF_ACTIVITE`', 'motif_creation_activite', '1', '`DESCR_MOTIF_ACTIVITE` ASC');
     //Sélectionner code article
  $economique_article = $this->getBindParms('`ARTICLE_ID`,`LIBELLE_ARTICLE`', 'class_economique_article', '1', '`LIBELLE_ARTICLE` ASC');
  $data['article_economique'] =$this->ModelPs->getRequete($callpsreq, $economique_article);

        //Sélectionner classe economique littera
  $economique_article = $this->getBindParms('`LITTERA_ID`,`LIBELLE_LITTERA`', 'class_economique_littera', '1', '`LIBELLE_LITTERA` ASC');
  $data['classe_economique_littera'] =$this->ModelPs->getRequete($callpsreq, $economique_article);

         //Sélectionner division
  $division_fonctionnelle = $this->getBindParms('`DIVISION_ID`,`LIBELLE_DIVISION`', 'class_fonctionnelle_division', '1', '`LIBELLE_DIVISION` ASC');
  $data['fonctionnelle_division'] =$this->ModelPs->getRequete($callpsreq, $division_fonctionnelle);


            //Sélectionner groupe fonctionnelle
  $groupe_fonctionnelle = $this->getBindParms('`GROUPE_ID`,`LIBELLE_GROUPE`', 'class_fonctionnelle_groupe', '1', '`LIBELLE_GROUPE` ASC');
  $data['fonctionnelle_groupe'] =$this->ModelPs->getRequete($callpsreq, $groupe_fonctionnelle);


  //Sélectionner classe fonctionnelle
  $classe_fonctionnelle = $this->getBindParms('`CLASSE_ID`,`LIBELLE_CLASSE`', 'class_fonctionnelle_classe', '1', '`LIBELLE_CLASSE` ASC');
  $data['fonctionnelle_classe'] =$this->ModelPs->getRequete($callpsreq,  $classe_fonctionnelle);

               //Sélectionner classe fonctionnelle
  $classemasse = $this->getBindParms('`GRANDE_MASSE_ID`,`DESCRIPTION_GRANDE_MASSE`', 'inst_grande_masse', '1', '`DESCRIPTION_GRANDE_MASSE` ASC');
  $data['masse_classe'] =$this->ModelPs->getRequete($callpsreq,  $classemasse);

            //Sélectionner grandes masses
  $classgrandes_masse = $this->getBindParms('`GRANDE_MASSE_ID`,`DESCRIPTION_GRANDE_MASSE`', 'inst_grande_masse', '1','`DESCRIPTION_GRANDE_MASSE` ASC');
  $data['masse'] =$this->ModelPs->getRequete($callpsreq,$classgrandes_masse);
       // recuperation donnees tempo
      //  $classgrandes_masse = $this->getBindParms('`GRANDE_MASSE_ID`,`DESCRIPTION_GRANDE_MASSE`', 'ptba_programmation_budgetaire_tempo', '1','`DESCRIPTION_GRANDE_MASSE` ASC');
      //  $data['masse'] =$this->ModelPs->getRequete($callpsreq,$classgrandes_masse);

  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $bind_prog = $this->getBindParms('PTBA_PROGR_BUDG_ID_Tempo,CODE_MINISTERE,INTITULE_MINISTERE,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_PROGRAMME,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,CODE_NOMENCLATURE_BUDGETAIRE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE,GRANDE_MASSE_BP,INTITULE_DES_GRANDES_MASSES,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,USER_ID', 'ptba_programmation_budgetaire_tempo',1,'PTBA_PROGR_BUDG_ID_Tempo ASC');
  $bind_prog=str_replace("\'","'",$bind_prog);
  $data['data_tempo']= $this->ModelPs->getRequete($callpsreq, $bind_prog);

  $data['set_action_id']= null;
  $data['set_code_budg']= null;
  $data['set_group_id']= null;
  $data['set_class_id']= null;

  $data['inst_action'] = array();
  $data['code_Buget'] = array();

  $data['get_class'] = array();
  $data['get_group'] = array();
  //founction pour recuperation des donnees dans la table tempo
  return view('App\Modules\process\Views\Dem_Activites_Add_View',$data);
}

  //fonction pour récupérer les programmes en fonction de institution_id
public function get_programs(){

  $session  = \Config\Services::session();
  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }

  $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');

  $callpsreq = "CALL `getRequete`(?,?,?,?);";

    //Sélectionner les programmes
  $bindprog = $this->getBindParms('PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME,INSTITUTION_ID','inst_institutions_programmes', 'INSTITUTION_ID='.$INSTITUTION_ID, '`INTITULE_PROGRAMME` ASC');
  $prog = $this->ModelPs->getRequete($callpsreq, $bindprog);

  $html='<option value="">-Sélectionner-</option>';

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
  }else{

    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';
  }

  $output = array('status' => TRUE , 'html' => $html);

  echo json_encode($output);

}

  // rSave my datas
public function insert_data(){

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

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
    //
    //$callp="CALL getRequete(?,?,?,?);";
   // $callp = "CALL `getRequete`(?,?,?,?);";
  $INSTITUTION_ID=$this->request->getpost("INSTITUTION_ID");
  $ID_DEMANDE=$this->request->getpost("ID_DEMANDE");
    //$

  $id_demande_new=$this->request->getpost("ID_DEMANDE_NEW");
 //dd($id_demande_new);
  $CODE_INSTITUTION=$this->request->getpost("CODE_INSTITUTION");
  $DESCR_INSTITUTION=$this->request->getpost("DESCR_INSTITUTION");
  $SOUS_TUTEL_ID=$this->request->getpost("SOUS_TUTEL_ID");
  $PROGRAMME_ID=$this->request->getpost("PROGRAMME_ID");
  $ACTION_ID=$this->request->getpost("ACTION_ID");
    // print_r($PROGRAMME_ID);die();
  $CODE_NOMENCLATURE_BUDGETAIRE=$this->request->getpost("CODE_NOMENCLATURE_BUDGETAIRE");
  $CODES_PROGRAMMATIQUE=$this->request->getpost("CODES_PROGRAMMATIQUE");
  $ACTIVITES=$this->request->getpost("ACTIVITES");
  $RESULTATS_ATTENDUS=$this->request->getpost("RESULTATS_ATTENDUS");
  $CODE_GROUPE=$this->request->getpost('CODE_GROUPE');
  
  $ARTICLE_ECONOMIQUE=$this->request->getpost("ARTICLE_ECONOMIQUE");
  $NATURE_ECONOMIQUE=$this->request->getpost("NATURE_ECONOMIQUE");
  $INTITULE_ARTICLE_ECONOMIQUE=$this->request->getpost("INTITULE_ARTICLE_ECONOMIQUE");
  $INTITULE_NATURE_ECONOMIQUE=$this->request->getpost("INTITULE_NATURE_ECONOMIQUE");
  $CODE_DIVISION=$this->request->getpost("CODE_DIVISION");
  $CODE_CLASSE=$this->request->getpost("CODE_CLASSE");
  $INTITULE_DIVISION=$this->request->getpost("INTITULE_DIVISION");

  $INTITULE_GROUPE=$this->request->getpost("INTITULE_GROUPE");
  $INTITULE_CLASSE=$this->request->getpost("INTITULE_CLASSE");
  $COUT_UNITAIRE_BIF=$this->request->getpost("COUT_UNITAIRE_BIF");
  $UNITE=$this->request->getpost("UNITE");
  $QT1 = $this->request->getPost('QT1');
  $QT2 = $this->request->getPost('QT2');
  $QT3 = $this->request->getPost('QT3');
  $QT4 = $this->request->getPost('QT4');
  $QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE = floatval($QT1)+floatval($QT2)+floatval($QT3)+floatval($QT4);
  //dd( $QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE);
  $T1 = $this->request->getPost('T1');
  $T2 = $this->request->getPost('T2');
  $T3 = $this->request->getPost('T3');
  $T4 = $this->request->getPost('T4');
  $PROGRAMMATION_FINANCIERE_BIF=floatval($T1) + floatval($T2) + floatval($T3) + floatval($T4);

  $MONTANT_RESTANT_T1 = $this->request->getPost('T1');
  $MONTANT_RESTANT_T2 = $this->request->getPost('T2');
  $MONTANT_RESTANT_T3 = $this->request->getPost('T3');
  $MONTANT_RESTANT_T4 = $this->request->getPost('T4');
  $INTITULE_DES_GRANDES_MASSES = $this->request->getPost('INTITULE_DES_GRANDES_MASSES');

  //print_r($INTITULE_DES_GRANDES_MASSES);die();
  $GRANDE_MASSE_BP = $this->request->getPost('GRANDE_MASSE_BP');
  $GRANDE_MASSE_BM1 = $this->request->getPost('GRANDE_MASSE_BM1');
  $GRANDE_MASSE_BM = $this->request->getPost('GRANDE_MASSE_BM');
  $RESPONSABLE = $this->request->getPost('RESPONSABLE'); 
  $CHAPITRES=$this->request->getpost("CHAPITRES");
  $PARAGRAPHE=$this->request->getpost("PARAGRAPHE");
  $LITTERA=$this->request->getpost("LITTERA");
  $INTITULE_LIGNE=$this->request->getpost("INTITULE_LIGNE");

    //dd($_POST);
          //récuperer les codes et intitulés des actions
  $bind_act1='SELECT ACTION_ID,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION FROM inst_institutions_actions where ACTION_ID="'.$ACTION_ID.'"';
  $act= $this->ModelPs->getRequeteOne("CALL `getTable`('".$bind_act1."')");
  //dd($act);

  $CODE_ACTION=$act['CODE_ACTION'];
  $LIBELLE_ACTION= str_replace("\\", "", $act['LIBELLE_ACTION']);
  $OBJECTIF_ACTION= str_replace("\\", "", $act['OBJECTIF_ACTION']);
  //rec
  //récuperer les codes et intitulés des programmes

  $callprocedure = "CALL `getRequete`(?,?,?,?);";
  $table="inst_institutions_programmes";
  $column="PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_DU_PROGRAMME";
  $where="PROGRAMME_ID=".$PROGRAMME_ID."";
  $orderby='1 DESC';
  $where=str_replace("\'","'",$where);
  //$bind_parmsinst=str_replace("\'","'",$bind_parmsinst);
  $db=db_connect();
  $bindparmss=[$db->escapeString($column),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
  $bindparamska=str_replace("\'","'",$bindparmss);

  $prog= $this->ModelPs->getRequeteOne($callprocedure,$bindparamska);

  $session  = \Config\Services::session();
  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/do_logout');
  }

  $CODE_PROGRAMME=$prog['CODE_PROGRAMME'];
  $INTITULE_PROGRAMME=$prog['INTITULE_PROGRAMME'];
  $OBJECTIF_DU_PROGRAMME=$prog['OBJECTIF_DU_PROGRAMME'];
  
  $insertIntoTable='ptba_programmation_budgetaire_tempo';
  $columsinsert='
  SOUS_TUTEL_ID,
  DEMANDE_ID,
  CODE_MINISTERE,
  INTITULE_MINISTERE,
  CODE_PROGRAMME,
  INTITULE_PROGRAMME,
  OBJECTIF_PROGRAMME,
  CODE_ACTION,
  LIBELLE_ACTION,
  OBJECTIF_ACTION,
  CODE_NOMENCLATURE_BUDGETAIRE,
  ARTICLE_ECONOMIQUE,
  INTITULE_ARTICLE_ECONOMIQUE,
  NATURE_ECONOMIQUE,
  INTITULE_NATURE_ECONOMIQUE,
  DIVISION_FONCTIONNELLE,
  INTITULE_DIVISION_FONCTIONNELLE,
  GROUPE_FONCTIONNELLE,
  INTITULE_GROUPE_FONCTIONNELLE,
  CLASSE_FONCTIONNELLE,
  INTITULE_CLASSE_FONCTIONNELLE,
  CODES_PROGRAMMATIQUE,
  ACTIVITES,
  RESULTATS_ATTENDUS,
  UNITE,
  QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,
  QT1,
  QT2,
  QT3,
  QT4,
  COUT_UNITAIRE_BIF,
  T1,
  T2,
  T3,
  T4,
  PROGRAMMATION_FINANCIERE_BIF,
  RESPONSABLE,
  GRANDE_MASSE_BP,
  INTITULE_DES_GRANDES_MASSES,
  MONTANT_RESTANT_T1,
  MONTANT_RESTANT_T2,
  MONTANT_RESTANT_T3,
  MONTANT_RESTANT_T4,
  USER_ID,CHAPITRES,PARAGRAPHE,LITTERA,INTITULE_LIGNE';
  $datatoinsert_Activite= "'".$SOUS_TUTEL_ID."','".$this->request->getpost("ID_DEMANDE_NEW")."',
  '" .$CODE_INSTITUTION . "',
  '" .str_replace("'", "\'", $DESCR_INSTITUTION)."',
  '" .$CODE_PROGRAMME."',
  '" .str_replace("'", "\'",$INTITULE_PROGRAMME)."',
  '" .str_replace("'", "\'",$OBJECTIF_DU_PROGRAMME)."',
  '" .$CODE_ACTION."',
  '" .$LIBELLE_ACTION."',
  '" .$OBJECTIF_ACTION."',
  '" .$CODE_NOMENCLATURE_BUDGETAIRE."',
  '" .$ARTICLE_ECONOMIQUE."',
  '" .str_replace("'", "\'",$INTITULE_ARTICLE_ECONOMIQUE)."',
  '" .$NATURE_ECONOMIQUE."',
  '" .str_replace("'", "\'",$INTITULE_NATURE_ECONOMIQUE)."',
  '" .$CODE_DIVISION."',
  '" .str_replace("'", "\'",$INTITULE_DIVISION)."',
  '" .$CODE_GROUPE."',
  '" .$INTITULE_GROUPE."',
  '" .$CODE_CLASSE."',
  '" .str_replace("'", "\'",$INTITULE_CLASSE)."',
  '" .$CODES_PROGRAMMATIQUE."',
  '" .$ACTIVITES."',
  '" .$RESULTATS_ATTENDUS."',
  '" .$UNITE."',
  '" .$QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE."',
  '" .$QT1."',
  '" .$QT2."',
  '" .$QT3."',
  '" .$QT4."',
  '" .$COUT_UNITAIRE_BIF."',
  '" .$T1."',
  '" .$T2."',
  '" .$T3."',
  '" .$T4."',
  '" .$PROGRAMMATION_FINANCIERE_BIF."',
  '" .$RESPONSABLE."',
  '" .$GRANDE_MASSE_BP."',
  '".str_replace("'", "\'", $INTITULE_DES_GRANDES_MASSES)."',
  '" . $MONTANT_RESTANT_T1 ."',
  '" . $MONTANT_RESTANT_T2 ."',
  '" . $MONTANT_RESTANT_T3."',
  '" . $MONTANT_RESTANT_T4 ."',
  '" . $USER_ID ."','".$CHAPITRES."','".$PARAGRAPHE."','".$LITTERA."','".$INTITULE_LIGNE."'";
  $datamessage=$this->save_all_table($insertIntoTable,$columsinsert,$datatoinsert_Activite);

  $data = [
    'message' => ''.lang('messages_lang.Enregistrer_succes_msg').''
  ];
  session()->setFlashdata('alert', $data);

  return $this->create($ID_DEMANDE);
}

// update my datas
public function update_data(){

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

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
  
  $RowId=$this->request->getpost('RowiD');
  
  $INSTITUTION_ID=$this->request->getpost("INSTITUTION_ID");
  $ID_DEMANDE=$this->request->getpost("ID_DEMANDE");

  $CODE_INSTITUTION=$this->request->getpost("CODE_INSTITUTION");
  $DESCR_INSTITUTION=$this->request->getpost("DESCR_INSTITUTION");
  $SOUS_TUTEL_ID=$this->request->getpost("SOUS_TUTEL_ID");
  $PROGRAMME_ID=$this->request->getpost("PROGRAMME_ID");
  $ACTION_ID=$this->request->getpost("ACTION_ID");
  // print_r($ACTION_ID);die();
  $CODE_NOMENCLATURE_BUDGETAIRE=$this->request->getpost("CODE_NOMENCLATURE_BUDGETAIRE");
  $CODES_PROGRAMMATIQUE=$this->request->getpost("CODES_PROGRAMMATIQUE");
  $ACTIVITES=$this->request->getpost("ACTIVITES");
  $RESULTATS_ATTENDUS=$this->request->getpost("RESULTATS_ATTENDUS");
  $CODE_GROUPE=$this->request->getpost('CODE_GROUPE');

  $ARTICLE_ECONOMIQUE=$this->request->getpost("ARTICLE_ECONOMIQUE");
  $NATURE_ECONOMIQUE=$this->request->getpost("NATURE_ECONOMIQUE");
  $INTITULE_ARTICLE_ECONOMIQUE=$this->request->getpost("INTITULE_ARTICLE_ECONOMIQUE");
  $INTITULE_NATURE_ECONOMIQUE=$this->request->getpost("INTITULE_NATURE_ECONOMIQUE");
  $CODE_DIVISION=$this->request->getpost("CODE_DIVISION");
  $CODE_CLASSE=$this->request->getpost("CODE_CLASSE");
  $INTITULE_DIVISION=$this->request->getpost("INTITULE_DIVISION");

  $CHAPITRES=$this->request->getpost("CHAPITRES");
  $PARAGRAPHE=$this->request->getpost("PARAGRAPHE");
  $LITTERA=$this->request->getpost("LITTERA");
  $INTITULE_LIGNE=$this->request->getpost("INTITULE_LIGNE");
  $SOUS_TUTEL_ID=$this->request->getpost("SOUS_TUTEL_ID");

  $INTITULE_GROUPE=$this->request->getpost("INTITULE_GROUPE");
  $INTITULE_CLASSE=$this->request->getpost("INTITULE_CLASSE");
  $COUT_UNITAIRE_BIF=$this->request->getpost("COUT_UNITAIRE_BIF");
  $UNITE=$this->request->getpost("UNITE");
  $QT1 = $this->request->getPost('QT1');
  $QT2 = $this->request->getPost('QT2');
  $QT3 = $this->request->getPost('QT3');
  $QT4 = $this->request->getPost('QT4');
  $QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE = floatval($QT1)+floatval($QT2)+floatval($QT3)+floatval($QT4);
  //dd( $QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE);
  $T1 = $this->request->getPost('T1');
  $T2 = $this->request->getPost('T2');
  $T3 = $this->request->getPost('T3');
  $T4 = $this->request->getPost('T4');
  $PROGRAMMATION_FINANCIERE_BIF=floatval($T1) + floatval($T2) + floatval($T3) + floatval($T4);
  
  $MONTANT_RESTANT_T1 = $this->request->getPost('T1');
  $MONTANT_RESTANT_T2 = $this->request->getPost('T2');
  $MONTANT_RESTANT_T3 = $this->request->getPost('T3');
  $MONTANT_RESTANT_T4 = $this->request->getPost('T4');
  $INTITULE_DES_GRANDES_MASSES = $this->request->getPost('INTITULE_DES_GRANDES_MASSE');
  
  //dd($INTITULE_DES_GRANDES_MASSES);die();
  $GRANDE_MASSE_BP = $this->request->getPost('GRANDE_MASSE_BP');
  $GRANDE_MASSE_BM1 = $this->request->getPost('GRANDE_MASSE_BM1');
  $GRANDE_MASSE_BM = $this->request->getPost('GRANDE_MASSE_BM');
  $RESPONSABLE = $this->request->getPost('RESPONSABLE'); 
  
  $bind_act1='SELECT ACTION_ID,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION FROM inst_institutions_actions where ACTION_ID="'.$ACTION_ID.'"';
  $act= $this->ModelPs->getRequeteOne("CALL `getTable`('".$bind_act1."')");
  
  $CODE_ACTION=$act['CODE_ACTION'];
  $LIBELLE_ACTION= str_replace("\\", "", $act['LIBELLE_ACTION']);
  $OBJECTIF_ACTION= str_replace("\\", "", $act['OBJECTIF_ACTION']);
  
  $callprocedure = "CALL `getRequete`(?,?,?,?);";
  $table="inst_institutions_programmes";
  $column="PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_DU_PROGRAMME";
  $where="PROGRAMME_ID=".$PROGRAMME_ID."";
  $orderby='1 DESC';
  $where=str_replace("\'","'",$where);

  $db=db_connect();
  $bindparmss=[$db->escapeString($column),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
  $bindparamska=str_replace("\'","'",$bindparmss);
  
  $prog= $this->ModelPs->getRequeteOne($callprocedure,$bindparamska);
  
  $CODE_PROGRAMME=$prog['CODE_PROGRAMME'];
  $INTITULE_PROGRAMME=$prog['INTITULE_PROGRAMME'];
  $OBJECTIF_DU_PROGRAMME=$prog['OBJECTIF_DU_PROGRAMME'];

  $UpdateTable='ptba_programmation_budgetaire_tempo';
  $columsupdate='CODE_MINISTERE="'.$CODE_INSTITUTION .'",
  INTITULE_MINISTERE="'.$DESCR_INSTITUTION.'",
  CODE_PROGRAMME="'. $CODE_PROGRAMME.'",
  INTITULE_PROGRAMME="'.$INTITULE_PROGRAMME.'",
  OBJECTIF_PROGRAMME="'.$OBJECTIF_DU_PROGRAMME.'",
  CODE_ACTION="'.$CODE_ACTION.'",
  LIBELLE_ACTION="'.$LIBELLE_ACTION.'",
  OBJECTIF_ACTION="'.$OBJECTIF_ACTION.'",
  CODE_NOMENCLATURE_BUDGETAIRE="'.$CODE_NOMENCLATURE_BUDGETAIRE.'",
  ARTICLE_ECONOMIQUE="'.$ARTICLE_ECONOMIQUE.'",
  INTITULE_ARTICLE_ECONOMIQUE="'.$INTITULE_ARTICLE_ECONOMIQUE.'",
  NATURE_ECONOMIQUE="'.$NATURE_ECONOMIQUE.'",
  INTITULE_NATURE_ECONOMIQUE="'.$INTITULE_NATURE_ECONOMIQUE.'",
  DIVISION_FONCTIONNELLE="'.$CODE_DIVISION.'",
  INTITULE_DIVISION_FONCTIONNELLE="'.$INTITULE_DIVISION.'",
  GROUPE_FONCTIONNELLE="'.$CODE_GROUPE.'",
  INTITULE_GROUPE_FONCTIONNELLE="'.$INTITULE_GROUPE.'",
  CLASSE_FONCTIONNELLE="'.$CODE_CLASSE.'",
  CODES_PROGRAMMATIQUE="'.$CODES_PROGRAMMATIQUE.'",
  ACTIVITES="'.$ACTIVITES.'",
  RESULTATS_ATTENDUS="'.$RESULTATS_ATTENDUS.'",
  UNITE="'.$UNITE.'",
  QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE="'.$QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE.'",
  QT1="'.$QT1.'",
  QT2="'.$QT2.'",
  QT3="'.$QT3.'",
  QT4="'.$QT4.'",
  COUT_UNITAIRE_BIF="'.$COUT_UNITAIRE_BIF.'",
  T1="'.$T1.'",
  T2="'.$T2.'",
  T3="'.$T3.'",
  T4="'.$T4.'",
  PROGRAMMATION_FINANCIERE_BIF="'.$PROGRAMMATION_FINANCIERE_BIF.'",
  RESPONSABLE="'.$RESPONSABLE.'",
  GRANDE_MASSE_BP="'.$GRANDE_MASSE_BP.'",
  INTITULE_DES_GRANDES_MASSES="'.$INTITULE_DES_GRANDES_MASSES.'",
  MONTANT_RESTANT_T1="'.$MONTANT_RESTANT_T1.'",
  MONTANT_RESTANT_T2="'.$MONTANT_RESTANT_T2.'",
  MONTANT_RESTANT_T3="'.$MONTANT_RESTANT_T3.'",
  MONTANT_RESTANT_T4="'.$MONTANT_RESTANT_T4.'",
  CHAPITRES="'.$CHAPITRES.'",PARAGRAPHE="'.$PARAGRAPHE.'",LITTERA="'.$LITTERA.'",INTITULE_LIGNE="'.$INTITULE_LIGNE.'",SOUS_TUTEL_ID="'.$SOUS_TUTEL_ID.'"';

  $conditions='PTBA_PROGR_BUDG_ID_Tempo='.$RowId;
  $datamessage=$this->update_all_table($UpdateTable,$columsupdate,$conditions);

  $data = [
    'message' => ''.lang('messages_lang.modification_reussi').''
  ];
  session()->setFlashdata('alert', $data);

  return redirect('process/Demandes_Program_Budget');

}

  //fonction pour insérer le formulaire
public function insert()
{
  // dd($this->request->getPost());
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

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }

  $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
    // dd($ID_DEMANDE);
  $CODE_INSTITUTION = $this->request->getPost('CODE_INSTITUTION');
  $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
  $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
  $ACTION_ID = $this->request->getPost('set_ACTION_ID');
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
      'rules' => 'required|max_length[20]',
      'errors' => [
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
        'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 20 '.lang('messages_lang.msg_caracteres').'</font>',
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
    ]
    ,
    'CODES_PROGRAMMATIQUE' => [
      'rules' => 'max_length[27]',
      'errors' => [
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
        'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 500 '.lang('messages_lang.msg_caracteres').'</font>'
      ]
    ]
    ,
    'CODE_DIVISION' => [
      'rules' => 'max_length[20]',
      'errors' => [
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
        'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 20 '.lang('messages_lang.msg_caracteres').'</font>'
      ]
    ]
    ,
    'CODE_GROUPE' => [
      'rules' => 'max_length[20]',
      'errors' => [
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
        'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 20 '.lang('messages_lang.msg_caracteres').'</font>'
      ]
    ]
    ,
    'CODE_CLASSE' => [
      'rules' => 'max_length[20]',
      'errors' => [
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>',
        'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 20 '.lang('messages_lang.msg_caracteres').'</font>'
      ]
    ]
    ,
    'INTITULE_DIVISION' => [
      'rules' => 'required|max_length[500]',
      'errors' => [
        'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 500 '.lang('messages_lang.msg_caracteres').'</font>',
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
      ]
    ]
    ,
    'INTITULE_GROUPE' => [
      'rules' => 'max_length[20]',
      'errors' => [
        'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 500 '.lang('messages_lang.msg_caracteres').'</font>',
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
      ]
    ]
    ,
    'INTITULE_CLASSE' => [
      'rules' => 'required|max_length[500]',
      'errors' => [
        'max_length' => '<font style="color:red;size:2px;">'.lang('messages_lang.msg_depas').' 500 '.lang('messages_lang.msg_caracteres').'</font>',
        'required' => '<font style="color:red;size:2px;">'.lang('messages_lang.champ_obligatoire').'</font>'
      ]
    ]

  ];



  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $MOTIF_ACTIVITE_ID= $this->request->getPost('MOTIF_ACTIVITE_ID');
  $CODE_INSTITUTION = $this->request->getPost('CODE_INSTITUTION');
  $DESCR_INSTITUTION = $this->request->getPost('DESCR_INSTITUTION');

  $PROGRAMME_ID = $this->request->getPost('PROGRAMME_ID');
  $ACTION_ID = $this->request->getPost('ACTION_ID');

      //récuperer les codes et intitulés des programmes

  $bind_prog = $this->getBindParms('PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME, OBJECTIF_DU_PROGRAMME','inst_institutions_programmes','PROGRAMME_ID='.$PROGRAMME_ID.'','PROGRAMME_ID DESC');
  $prog= $this->ModelPs->getRequeteOne($callpsreq, $bind_prog);

  $CODE_PROGRAMME=$prog['CODE_PROGRAMME'];
  $INTITULE_PROGRAMME=$prog['INTITULE_PROGRAMME'];
  $OBJECTIF_DU_PROGRAMME=$prog['OBJECTIF_DU_PROGRAMME'];

      //récuperer les codes et intitulés des actions

  $bind_act = $this->getBindParms('ACTION_ID,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION','inst_institutions_actions','ACTION_ID='.$ACTION_ID.'','ACTION_ID DESC');
  $act= $this->ModelPs->getRequeteOne($callpsreq, $bind_act);

  $CODE_ACTION=$act['CODE_ACTION'];
  $LIBELLE_ACTION= str_replace("\\", "", $act['LIBELLE_ACTION']);
  $OBJECTIF_ACTION= str_replace("\\", "", $act['OBJECTIF_ACTION']);

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
  $DIVISION_FONCTIONNELLE = $this->request->getPost('CODE_DIVISION');
  $INTITULE_DIVISION_FONCTIONNELLE = $this->request->getPost('INTITULE_DIVISION');


      //GROUPE FONCTIONNEL
  $GROUPE_FONCTIONNELLE = $this->request->getPost('CODE_GROUPE');
  $INTITULE_GROUPE_FONCTIONNELLE = $this->request->getPost('INTITULE_GROUPE');

      //CLASSE FONCTIONNELLE
  $CLASSE_FONCTIONNELLE = $this->request->getPost('CODE_CLASSE');
  $INTITULE_CLASSE_FONCTIONNELLE = $this->request->getPost('INTITULE_CLASSE');


  $columsinsert="QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,COUT_UNITAIRE_BIF,QT1,QT2,QT3,QT4,T1,T2,T3,T4,INTITULE_DES_GRANDES_MASSES,GRANDE_MASSE_BP,RESPONSABLE,CODE_MINISTERE,INTITULE_MINISTERE,CODE_PROGRAMME,INTITULE_PROGRAMME,OBJECTIF_PROGRAMME,CODE_ACTION,LIBELLE_ACTION,OBJECTIF_ACTION,CODES_PROGRAMMATIQUE,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE, INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE, CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4,PROGRAMMATION_FINANCIERE_BIF";

  $datatoinsert="'".$QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE."','".$CODE_NOMENCLATURE_BUDGETAIRE."','".str_replace("'", "\'", $ACTIVITES)."','".str_replace("'", "\'", $RESULTATS_ATTENDUS)."','".$UNITE."','".$COUT_UNITAIRE_BIF."','".$QT1."','".$QT2."','".$QT3."','" .$QT4."','".$T1."','".$T2."','".$T3."','".$T4."','".str_replace("'", "\'", $INTITULE_DES_GRANDES_MASSES)."','".$GRANDE_MASSE_BP."','".$RESPONSABLE."','".$CODE_INSTITUTION."','".str_replace("'", "\'", $DESCR_INSTITUTION)."','".$CODE_PROGRAMME."','".str_replace("'", "\'", $INTITULE_PROGRAMME)."','".str_replace("'", "\'", $OBJECTIF_DU_PROGRAMME)."','".$CODE_ACTION."','".str_replace("'", "\'", $LIBELLE_ACTION)."','".str_replace("'", "\'", $OBJECTIF_ACTION)."','".$CODES_PROGRAMMATIQUE."','".$ARTICLE_ECONOMIQUE."','".str_replace("'", "\'", $INTITULE_ARTICLE_ECONOMIQUE)."','".$NATURE_ECONOMIQUE."','".str_replace("'", "\'", $INTITULE_NATURE_ECONOMIQUE)."','".$DIVISION_FONCTIONNELLE."','".str_replace("'", "\'", $INTITULE_DIVISION_FONCTIONNELLE)."','".$GROUPE_FONCTIONNELLE."','".str_replace("'", "\'", $INTITULE_GROUPE_FONCTIONNELLE)."','".$CLASSE_FONCTIONNELLE."','".str_replace("'", "\'", $INTITULE_CLASSE_FONCTIONNELLE)."','".$MONTANT_RESTANT_T1."','".$MONTANT_RESTANT_T2."','".$MONTANT_RESTANT_T3."','".$MONTANT_RESTANT_T4."','".$PROGRAMMATION_FINANCIERE_BIF."'";

  $table='ptba_programmation_budgetaire_tempo';
  $PTBA_PROGR_BUDG_ID= $this->save_all_table($table,$columsinsert,$datatoinsert);
      // dd('ptba_progr_budg_id : '.$PTBA_PROGR_BUDG_ID);

      //récupérer l'ETAPE_ID à partir de la demande
  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $getDemande = $this->getBindParms('ETAPE_ID, CODE_DEMANDE', 'proc_demandes', 'md5(ID_DEMANDE) ="'.$ID_DEMANDE.'"', 'CODE_DEMANDE ASC');
      // dd($getDemande);
  $getDemande =\str_replace('\"','"',$getDemande);
  $Demande = $this->ModelPs->getRequeteOne($callpsreq, $getDemande);
      //récupérer l'action à partir de l'ETAPE_ID de la demande
  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $getaction = $this->getBindParms('ACTION_ID, ETAPE_ID, MOVETO', 'proc_actions', 'ETAPE_ID ='.$Demande['ETAPE_ID'].'', 1);
  $action = $this->ModelPs->getRequeteOne($callpsreq, $getaction);

      //mettre à jour la table progr_budg_infos_supp
  $where ="md5(ID_DEMANDE)= '".$ID_DEMANDE."'";
  $insertInto='progr_budg_infos_supp';
  $colum="PTBA_PROGR_BUDG_ID =".$PTBA_PROGR_BUDG_ID;
  $this->update_all_table($insertInto,$colum,$where);

  //print_r($action['MOVETO']);die();
      //mettre à jour la table proc_demandes(ETAPE_ID)
  $where ="md5(ID_DEMANDE) = '".$ID_DEMANDE."'";
  $insertInto='proc_demandes';
  $colum="ETAPE_ID = ".$action['MOVETO'];
  $this->update_all_table($insertInto,$colum,$where);
  $data=$this->urichk();

  $data = [
    'message' => ''.lang('messages_lang.Enregistrer_succes_msg').''
  ];
  session()->setFlashdata('alert', $data);

  return redirect('process/Demandes_Program_Budget');
}

public function save($columsinsert,$datacolumsinsert)
{
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
  $table='ptba_programmation_budgetaire_tempo';
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
  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }

  $INSTITUTION_ID =$this->request->getPost('INSTITUTION_ID');

  $callpsreq = "CALL `getRequete`(?,?,?,?);";

    //Sélectionner les programmes
  $bindprog = $this->getBindParms('PROGRAMME_ID,CODE_PROGRAMME,INTITULE_PROGRAMME,INSTITUTION_ID','inst_institutions_programmes', 'INSTITUTION_ID='.$INSTITUTION_ID, '`INTITULE_PROGRAMME` ASC');
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
  }else{

    $html='<option value="">-Sélectionner-</option>';
  }

  $output = array('status' => TRUE , 'html' => $html);
    return $this->response->setJSON($output);//echo json_encode($output);

  }


  //Sélectionner les actions à partir des programmes
  function get_action()
  {
    $CODE_PROGRAMME =$this->request->getPost('CODE_PROGRAMME');

    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $get_action = "SELECT DISTINCT LIBELLE_ACTION, CODE_ACTION FROM ptba_programmation_budgetaire WHERE CODE_PROGRAMME LIKE '%".$CODE_PROGRAMME."%' ORDER BY LIBELLE_ACTION ";

    $details='CALL `getTable`("'.$get_action.'")';
    $action = $this->ModelPs->getRequete( $details);

    $html='<option value="">'.lang('messages_lang.selection_message').'</option>';

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
  public function Get_code_classes(){
    //$boolean=$_GET['iduser'];
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $classe_ID =$this->request->getPost('classe_ID');
    $bind_classe = $this->getBindParms('CLASSE_ID,CODE_CLASSE', 'class_fonctionnelle_classe', 'CLASSE_ID='.$classe_ID , 'CLASSE_ID ASC');
    $pragraph= $this->ModelPs->getRequete($callpsreq, $bind_classe);
    echo json_encode($pragraph);

  }

  public function Get_code_sous_tutels(){
          //$boolean=$_GET['iduser'];
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $SOUS_TUTEL_ID =$this->request->getPost('SOUS_TUTEL_ID');
    $bind_tutel = $this->getBindParms('SOUS_TUTEL_ID,CODE_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'SOUS_TUTEL_ID='.$SOUS_TUTEL_ID , 'SOUS_TUTEL_ID ASC');
    $pragraph= $this->ModelPs->getRequete($callpsreq, $bind_tutel);

    echo json_encode($pragraph);

  }

  public function Get_code_classe(){

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $Classe_ID =$this->request->getPost('CLASSE_ID');
    $bind_classe = $this->getBindParms('CLASSE_ID,CODE_CLASSE', ' class_fonctionnelle_classe', 'CLASSE_ID='.$Classe_ID , 'CLASSE_ID ASC');
    $pragraph= $this->ModelPs->getRequete($callpsreq,$bind_classe);
    echo json_encode($pragraph);

  }
  public function Get_code_groupe(){

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $Groupe_ID =$this->request->getPost('Groupe_ID');
    $bind_groupe = $this->getBindParms('GROUPE_ID,CODE_GROUPE', ' class_fonctionnelle_groupe', 'GROUPE_ID='.$Groupe_ID , 'GROUPE_ID ASC');
    $pragraph= $this->ModelPs->getRequete($callpsreq,$bind_groupe);
    echo json_encode($pragraph);

  }
  public function Get_code_divisions(){

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $Division_ID =$this->request->getPost('Division_ID');
    $bind_division = $this->getBindParms('DIVISION_ID,CODE_DIVISION', ' class_fonctionnelle_division', 'DIVISION_ID='.$Division_ID , 'DIVISION_ID ASC');
    $pragraph= $this->ModelPs->getRequete($callpsreq, $bind_division);
    echo json_encode($pragraph);

  }

  public function Get_code_code_programmatique(){

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $Action_ID =$this->request->getPost('Action_ID');
    $bind_classe = $this->getBindParms('ACTION_ID,CODE_ACTION', ' inst_institutions_actions', 'ACTION_ID='.$Action_ID , 'ACTION_ID ASC');
    $pragraph= $this->ModelPs->getRequete($callpsreq, $bind_classe);
    echo json_encode($pragraph);

  }

  public function Get_code_sous_litteras(){
          //$boolean=$_GET['iduser'];
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $SOUS_LITTERA_ID =$this->request->getPost('SOUS_LITTERA_ID');
    $bind_classe = $this->getBindParms('SOUS_LITTERA_ID,CODE_SOUS_LITTERA,LITTERA_ID', ' class_economique_sous_littera', 'SOUS_LITTERA_ID ='.$SOUS_LITTERA_ID , 'SOUS_LITTERA_ID ASC');
    $pragraph= $this->ModelPs->getRequeteOne($callpsreq, $bind_classe);

    $output = array(

      "pragraph" => $pragraph['CODE_SOUS_LITTERA'],
    );

    return $this->response->setJSON($output);

  }

  public function Get_code_Paragraphe(){
    //$boolean=$_GET['iduser'];
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $PARAGRAPHE_ID =$this->request->getPost('PARAGRAPHE_ID');
    $bind_paragraphe = $this->getBindParms('PARAGRAPHE_ID,CODE_PARAGRAPHE', 'class_economique_paragraphe', 'CODE_PARAGRAPHE='.$PARAGRAPHE_ID , 'PARAGRAPHE_ID ASC');
    $pragraph= $this->ModelPs->getRequete($callpsreq, $bind_paragraphe);
    echo json_encode($pragraph);

  }

  public function Get_code_division(){
    //$boolean=$_GET['iduser'];
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $DIVISION_ID =$this->request->getPost('DIVISION_ID');
    $bind_division = $this->getBindParms('DIVISION_ID,CODE_DIVISION', 'class_fonctionnelle_division', 'DIVISION_ID='.$DIVISION_ID , 'DIVISION_ID ASC');
    $tutel_sous= $this->ModelPs->getRequete($callpsreq, $bind_division);
    echo json_encode($tutel_sous);

  }
  // get codes

  public function Get_code_articles(){
    //$boolean=$_GET['iduser'];
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $ARTICLE_ID =$this->request->getPost('ID_ARTicle');
    $bind_article = $this->getBindParms('ARTICLE_ID ,CODE_ARTICLE', 'class_economique_article', 'ARTICLE_ID='.$ARTICLE_ID , 'ARTICLE_ID ASC');
    $tutel_sous= $this->ModelPs->getRequete($callpsreq, $bind_article);
    echo json_encode($tutel_sous);

  }
         //LES SELECTS POUR LA PAGE DE CREATION D UNE NOUVELLE ACTIVITE
  public function create_get_code()
  {
    $db = db_connect();
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $CODE_INSTITUTION = $this->request->getPost('CODE_INSTITUTION');
    $set_code_budg = $this->request->getPost('set_code_budg');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $getcodeSousTutel = $this->getBindParms('SOUS_TUTEL_ID,INSTITUTION_ID,CODE_SOUS_TUTEL', 'inst_institutions_sous_tutel', ' INSTITUTION_ID ='.$INSTITUTION_ID.' AND SOUS_TUTEL_ID = '.$SOUS_TUTEL_ID.'', 'CODE_SOUS_TUTEL  ASC');
    $code_SouTutel= $this->ModelPs->getRequeteOne($callpsreq, $getcodeSousTutel);
    $CODEBUDGET = $CODE_INSTITUTION.'00'.$code_SouTutel['CODE_SOUS_TUTEL'];
    //Le code budgetaire
    $getcodeBudget = "SELECT DISTINCT CODE_NOMENCLATURE_BUDGETAIRE FROM ptba_programmation_budgetaire WHERE CODE_NOMENCLATURE_BUDGETAIRE LIKE '".$CODEBUDGET."%'";
    $getcodeBudget = 'CALL `getTable`("'.$getcodeBudget.'");';
    $code_Buget= $this->ModelPs->getRequete($getcodeBudget);
    $html="<option value=''>".lang('messages_lang.selection_message')."</option>";
    foreach ($code_Buget as $key)
    {

      $html.='<option value="'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'">'.$key->CODE_NOMENCLATURE_BUDGETAIRE.'</option>';   
    }
    $output = array(
      "codeBudgetaire" => $html
    );
    return $this->response->setJSON($output);
  }

  // get sous tutel
  public function create_get_Sous_tutel()
  {

   $session  = \Config\Services::session();
   $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
   $callpsreq = "CALL `getRequete`(?,?,?,?);";

   $callpsreq = "CALL `getRequete`(?,?,?,?);";
   $PROGRAMME_ID =$this->request->getPost('PROGRAMME_ID');
     //$set_ACTION_ID =$this->request->getPost('set_ACTION_ID');

   if(!empty($PROGRAMME_ID))
   {
    $bind_action = $this->getBindParms('SOUS_TUTEL_ID,DESCRIPTION_SOUS_TUTEL', 'inst_institutions_sous_tutel', 'INSTITUTION_ID='.$PROGRAMME_ID , 'SOUS_TUTEL_ID ASC');
    $tutel_sous= $this->ModelPs->getRequete($callpsreq, $bind_action);
    $tutel="<option value=''>".lang('messages_lang.selection_message')."</option>";
    foreach ($tutel_sous as $tut)
    {

      $tutel.= "<option value ='".$tut->SOUS_TUTEL_ID."' >".str_replace("\\", "", $tut->DESCRIPTION_SOUS_TUTEL)."</option>";

    }

  }else{
    $tutel="";
  }
  $output = array("tutel"=>$tutel);
  return $this->response->setJSON($output);
}
    // get code groupe
public function create_get_code_classe()
{

  $session  = \Config\Services::session();
  $session  = \Config\Services::session();
  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $GROUPE_ID =$this->request->getPost('GROUPE_ID');
       //$set_ACTION_ID =$this->request->getPost('set_ACTION_ID');

  if(!empty($GROUPE_ID))
  {
    $bind_groupe = $this->getBindParms('CLASSE_ID,GROUPE_ID,CODE_CLASSE,LIBELLE_CLASSE', 'class_fonctionnelle_classe', 'GROUPE_ID='.$GROUPE_ID , 'GROUPE_ID ASC');
    $tutel_sous= $this->ModelPs->getRequete($callpsreq, $bind_groupe);
    $tutel="<option value=''>".lang('messages_lang.selection_message')."</option>";
    foreach ($tutel_sous as $tut)
    {

      $tutel.= "<option value ='".$tut->CLASSE_ID."' >".str_replace("\\", "", $tut->LIBELLE_CLASSE)."</option>";
           
    }
  
  }else{
    $tutel="";
  }
  $output = array("tutel"=>$tutel);
  return $this->response->setJSON($output);
}

// get code groupe
public function create_get_code_groupe()
{

  $session  = \Config\Services::session();
  $session  = \Config\Services::session();
  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $CODE_DIVISION =$this->request->getPost('CODE_DIVISION');

  if(!empty($CODE_DIVISION))
  {
    $bind_groupe = $this->getBindParms('GROUPE_ID,DIVISION_ID,CODE_GROUPE,LIBELLE_GROUPE', 'class_fonctionnelle_groupe', 'DIVISION_ID='.$CODE_DIVISION , 'GROUPE_ID ASC');
    $tutel_sous= $this->ModelPs->getRequete($callpsreq, $bind_groupe);
    $tutel="<option value=''>".lang('messages_lang.selection_message')."</option>";
    foreach ($tutel_sous as $tut)
    {
      $tutel.= "<option value ='".$tut->GROUPE_ID."' >".str_replace("\\", "", $tut->LIBELLE_GROUPE)."</option>"; 
    }
  
  }else{
    $tutel="";
  }
  $output = array("tutel"=>$tutel);
  return $this->response->setJSON($output);
}

   // get code article
public function create_get_code_article()
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
  $CHAPITRE_ID =$this->request->getPost('CHAPITRE_ID');

  if(!empty($CHAPITRE_ID))
  {
   $bind_chapitre = $this->getBindParms('CHAPITRE_ID,ARTICLE_ID,CODE_ARTICLE,LIBELLE_ARTICLE', 'class_economique_article', 'CHAPITRE_ID='.$CHAPITRE_ID , 'CHAPITRE_ID ASC');
   $tutel_sous= $this->ModelPs->getRequete($callpsreq, $bind_chapitre);
   $tutel="<option value=''>".lang('messages_lang.selection_message')."</option>";
   foreach ($tutel_sous as $tut)
   {
     $tutel.= "<option value ='".$tut->ARTICLE_ID."' >".str_replace("\\", "", $tut->LIBELLE_ARTICLE)."</option>";
   }

 }else{
   $tutel="";
 }
 $output = array("tutel"=>$tutel);
 return $this->response->setJSON($output);
}

// get code article
public function create_get_code_paragraphe()
{

  $session  = \Config\Services::session();
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $ARTICLE_ID =$this->request->getPost('ARTICLE_ID');

  if(!empty($ARTICLE_ID))
  {
    $bind_chapitre = $this->getBindParms('PARAGRAPHE_ID,ARTICLE_ID,CODE_PARAGRAPHE,LIBELLE_PARAGRAPHE', 'class_economique_paragraphe', 'ARTICLE_ID='.$ARTICLE_ID , 'LIBELLE_PARAGRAPHE ASC');
    $tutel_sous= $this->ModelPs->getRequete($callpsreq, $bind_chapitre);
    $tutel="<option value=''>".lang('messages_lang.selection_message')."</option>";
    foreach ($tutel_sous as $tut)
    {
      $tutel.= "<option value ='".$tut->PARAGRAPHE_ID."' >".str_replace("\\", "", $tut->LIBELLE_PARAGRAPHE)."</option>";
    }
     
  }else{
     $tutel="";
  }
  $output = array("tutel"=>$tutel);
  return $this->response->setJSON($output);
}

// get code article
public function create_get_code_littera()
{
  $session  = \Config\Services::session();
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $PARAGRAPHE_ID =$this->request->getPost('PARAGRAPHE_ID');

  if(!empty($PARAGRAPHE_ID))
  {
    $bind_chapitre = $this->getBindParms('LITTERA_ID,PARAGRAPHE_ID,CODE_LITTERA,LIBELLE_LITTERA', 'class_economique_littera', 'PARAGRAPHE_ID='.$PARAGRAPHE_ID , 'LIBELLE_LITTERA ASC');
    $tutel_sous= $this->ModelPs->getRequete($callpsreq, $bind_chapitre);
    $tutel="<option value=''>".lang('messages_lang.selection_message')."</option>";
    foreach ($tutel_sous as $tut)
    {
      $tutel.= "<option value ='".$tut->LITTERA_ID."' >".str_replace("\\", "", $tut->LIBELLE_LITTERA)."</option>";
    }

  }else{
    $tutel="";
  }
  $output = array("tutel"=>$tutel);
  return $this->response->setJSON($output);
}
// get code sous littera
public function create_get_code_sous_littera()
{
  $session  = \Config\Services::session();
  $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $callpsreq = "CALL `getRequete`(?,?,?,?);";
  $LITTERA_ID=$this->request->getPost('LITTERA_ID');

  if(!empty($LITTERA_ID))
  {
   $bind_chapitre = $this->getBindParms('SOUS_LITTERA_ID,LITTERA_ID,CODE_SOUS_LITTERA,LIBELLE_SOUS_LITTERA', 'class_economique_sous_littera', 'LITTERA_ID='.$LITTERA_ID , 'LIBELLE_SOUS_LITTERA ASC');
   $tutel_sous= $this->ModelPs->getRequete($callpsreq, $bind_chapitre);
   $tutel="<option value=''>".lang('messages_lang.selection_message')."</option>";
   foreach ($tutel_sous as $tut)
   {
     $tutel.= "<option value ='".$tut->SOUS_LITTERA_ID."' >".str_replace("\\", "", $tut->LIBELLE_SOUS_LITTERA)."</option>";
   }

 }else{
   $tutel="";
 }
 $output = array("tutel"=>$tutel);
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

public function cl_cmr()
{
  $session  = \Config\Services::session();
  if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
  {
    return redirect('Login_Ptba/do_logout');
  }

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }
  $ID_DEMANDE = $this->request->getPost('ID_DEMANDE');
  $id='';
  if(!empty($ID_DEMANDE))
  {
    $id=' AND ID_DEMANDE = '.$ID_DEMANDE;
  }

  $query_principal='SELECT ID_PLANS_DEMANDE_CL_CMR,pilier.DESCR_PILIER, stra.DESCR_OBJECTIF_STRATEGIC,ind.DESCR_INDICATEUR, PRECISIONS, REFERENCE, CIBLE, ID_DEMANDE FROM planification_demande_cl_cmr cl JOIN pilier ON pilier.ID_PILIER=cl.ID_PILIER JOIN objectif_strategique stra ON stra.ID_OBJECT_STRATEGIQUE=cl.ID_OBJECT_STRATEGIQUE JOIN planification_indicateur ind ON ind.ID_PLANS_INDICATEUR=cl.ID_PLANS_INDICATEUR WHERE ID_CL_CMR_CATEGORIE=1 '.$id;

  $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    //$var_search = str_replace("'", "''", $var_search);
  $limit="LIMIT 0,10";
  if($_POST['length'] != -1)
  {
    $limit="LIMIT ".$_POST['start'].",".$_POST['length'];
  }

  $order_by="";
  $order_column="";
  $order_column= array(1,'pilier.DESCR_PILIER','stra.DESCR_OBJECTIF_STRATEGIC', 'ind.DESCR_INDICATEUR','PRECISIONS','REFERENCE','CIBLE');

  $order_by = isset($_POST['order']) ? " ORDER BY ". $order_column[$_POST['order']['0']['column']] ."  ".$_POST['order']['0']['dir'] : " ORDER BY ID_PLANS_DEMANDE_CL_CMR ASC";

  $search = !empty($_POST['search']['value']) ?  (' AND (pilier.DESCR_PILIER LIKE "%'.$var_search.'%" OR stra.DESCR_OBJECTIF_STRATEGIC LIKE "%'.$var_search.'%" OR ind.DESCR_INDICATEUR LIKE "%'.$var_search.'%" OR PRECISIONS LIKE "%'.$var_search.'%" OR REFERENCE LIKE "%'.$var_search.'%" OR CIBLE LIKE "%'.$var_search.'%")'):"";
  $search = str_replace("'","\'",$search);
  $critaire = " ";

  $query_secondaire=$query_principal." ".$search." ".$critaire." ".$order_by." ".$limit;

  $query_filter = $query_principal." ".$search." ".$critaire;

  $requete="CALL `getList`('".$query_secondaire."')";
  $fetch_cov_frais = $this->ModelPs->datatable( $requete);
  $data = array();
  $u=1;
  foreach($fetch_cov_frais as $row)
  {
    $sub_array = array();

    if(strlen($row->DESCR_PILIER) > 3)
    {
      $DESCR_PILIER =  substr($row->DESCR_PILIER, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_PILIER.'"><i class="fa fa-eye"></i></a>';
    }
    else
    {
      $DESCR_PILIER =  $row->DESCR_PILIER;
    }

    if(strlen($row->DESCR_OBJECTIF_STRATEGIC) > 3)
    {
      $DESCR_OBJECTIF_STRATEGIC =  substr($row->DESCR_OBJECTIF_STRATEGIC, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_OBJECTIF_STRATEGIC.'"><i class="fa fa-eye"></i></a>';
    }
    else
    {
      $DESCR_OBJECTIF_STRATEGIC =  $row->DESCR_OBJECTIF_STRATEGIC;
    }

    if(strlen($row->DESCR_INDICATEUR) > 3)
    {
      $DESCR_INDICATEUR =  substr($row->DESCR_INDICATEUR, 0, 3) .'...<a class="btn-sm" title="'.$row->DESCR_INDICATEUR.'"><i class="fa fa-eye"></i></a>';
    }
    else
    {
      $DESCR_INDICATEUR =  $row->DESCR_INDICATEUR;
    }

    $sub_array[]=$u++;
    $sub_array[]=$DESCR_PILIER;
    $sub_array[]=$DESCR_OBJECTIF_STRATEGIC;
    $sub_array[]=$DESCR_INDICATEUR;
    $sub_array[]=$row->REFERENCE;
    $sub_array[]=$row->CIBLE;
    $sub_array[]=$row->PRECISIONS;
    $sub_array[]='edit';
    $data[] = $sub_array;
  }

  $requeteqp="CALL `getList`('".$query_principal."')";
  $recordsTotal = $this->ModelPs->datatable( $requeteqp);
  $requeteqf="CALL `getList`('".$query_filter."')";
  $recordsFiltered = $this->ModelPs->datatable($requeteqf);
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
  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
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
  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
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
  return $this->response->setJSON($output);//echo json_encode($output);
}

function get_code_int($INTITULE_DES_GRANDES_MASSES=0)
{
  $db = db_connect();
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

  if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PROGRAMMATION_BUDGETAIRE') !=1)
  {
    return redirect('Login_Ptba/homepage');
  }

  $get_code_gde  = 'SELECT GRANDE_MASSE_ID, DESCRIPTION_GRANDE_MASSE FROM inst_grande_masse WHERE GRANDE_MASSE_ID = '.$INTITULE_DES_GRANDES_MASSES.' ORDER BY GRANDE_MASSE_ID  ASC';
  $get_code_gde = "CALL `getTable`('" . $get_code_gde . "');";
  $code_gde = $this->ModelPs->getRequeteOne($get_code_gde);

  $gde_code = $code_gde['GRANDE_MASSE_ID'];

  $output = array(
    "code_gde"=>$gde_code,
  );

  return $this->response->setJSON($output);
}

}	
?>