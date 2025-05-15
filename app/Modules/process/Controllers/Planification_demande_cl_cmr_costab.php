<?php

//AMELIRATION ET CORRECTION
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: Planification demande CL, CMR , COSTAB
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 02 11 2023
**/

namespace App\Modules\process\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;

class Planification_demande_cl_cmr_costab extends BaseController
{
	protected $library;
	protected $ModelPs;
	protected $session;
	protected $validation;

	function __construct()
	{
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}
	
  //appel du view de la liste des actions
	function index($ACTION_ID='',$ID_DEMANDE='', $getForm='')
	{
		$data = $this->urichk();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$getAction = 'SELECT IS_INITIAL FROM proc_actions WHERE proc_actions.ACTION_ID='.$ACTION_ID.'';
    $getAction = "CALL `getTable`('" . $getAction . "');";
    $getAction = $this->ModelPs->getRequeteOne($getAction);

    if ($getAction['IS_INITIAL']==1)
    {
    	if($ACTION_ID=='')
			{
				return redirect('Login_Ptba/do_logout');
			}
        $ID_DEMANDE = 0;
    }
    else
    {
      $infosDemande = 'SELECT ID_DEMANDE FROM `proc_demandes` WHERE md5(ID_DEMANDE)="'.$ID_DEMANDE.'"';
			$infosDemande = "CALL `getTable`('" . $infosDemande . "');";
			$resultatDemande= $this->ModelPs->getRequeteOne($infosDemande);
			if(empty($resultatDemande))
			{
				return redirect('Login_Ptba/do_logout');
			}

			$ID_DEMANDE = $resultatDemande['ID_DEMANDE'];
			if($ACTION_ID=='' || $ID_DEMANDE=='')
			{
				return redirect('Login_Ptba/do_logout');
			}
    }

		################################################################################
    $bindAction = $this->getBindParms('ID_CL_CMR_COSTAB_CATEGORIE,ACTION_ID','proc_actions','proc_actions.ACTION_ID='.$ACTION_ID.'','1');
		$data['getAction'] = $this->ModelPs->getRequeteOne($callpsreq,$bindAction);
		$data['ID_DEMANDE'] = $ID_DEMANDE;

		$data['categories'] = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT ID_CL_CMR_COSTAB_CATEGORIE,CL_CMR_COSTAB_CATEGORY FROM cl_cmr_costab_categorie WHERE 1 AND ID_CL_CMR_COSTAB_CATEGORIE=".$data['getAction']['ID_CL_CMR_COSTAB_CATEGORIE']."')");
		########################################################################################

    // get piliers
    $data['piliers'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PILIER,DESCR_PILIER FROM pilier WHERE 1 ORDER BY ID_PILIER ASC')");

    //get enjeux
    $bindenjeux = $this->getBindParms('ID_ENJEUX, DESCR_ENJEUX', 'enjeux', '1', 'ID_ENJEUX ASC');
    $data['enjeux'] = $this->ModelPs->getRequete($callpsreq, $bindenjeux);

    //axe_intervention_pnd
    $data['axe_intervation'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_AXE_INTERVENTION_PND, DESCR_AXE_INTERVATION_PND FROM axe_intervention_pnd 
        WHERE 1 ORDER BY DESCR_AXE_INTERVATION_PND ASC')");

    // get programme
    $bindProgramme = $this->getBindParms('ID_PROGRAMME_PND, DESCR_PROGRAMME', 'programme_pnd', '1', 'ID_PROGRAMME_PND ASC');
    $data['programme'] = $this->ModelPs->getRequete($callpsreq, $bindProgramme);
    ################################################################################

	  //get instition d'affectation de personne connecté
	  $user = $this->getBindParms('aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION','user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID','USER_ID='.$user_id.'','INSTITUTION_ID');
	  $data['institution'] = $this->ModelPs->getRequete($callpsreq,$user);

		$data['getForm'] = $getForm;

    $get_institution ="SELECT INSTITUTION_ID FROM planification_demande_cl_cmr_tempo WHERE 1 AND ID_USER = {$user_id}";
    $my_instit = "CALL `getTable`('".$get_institution."');";
    $rqt = $this->ModelPs->getRequeteOne($my_instit);

    if(!empty($rqt))
    {
      $data['instit_id_select'] = $rqt['INSTITUTION_ID'];
    }
    else{
      $data['instit_id_select'] = '';
    }
		return view('App\Modules\process\Views\Planification_demande_ci_cmr_add_views', $data);
	}

	public function getObjectif(int $id_pilier)
  {
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }  

    $objectif_strategique = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_OBJECT_STRATEGIQUE, DESCR_OBJECTIF_STRATEGIC, ID_PILIER FROM objectif_strategique 
      WHERE 1 AND ID_PILIER = {$id_pilier} ORDER BY DESCR_OBJECTIF_STRATEGIC ASC')");

    $html_objectif = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($objectif_strategique as $key)
    {
      $html_objectif .= '<option value="'.$key->ID_OBJECT_STRATEGIQUE.'">' . $key->DESCR_OBJECTIF_STRATEGIC . '</option>';
    }

    $output = array(
      "objectif" => $html_objectif
    );
    return $this->response->setJSON($output);
  }

  public function getIndicateur(int $id_objectif)
  {
    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $objectif_strategique_indicateur = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_INDICACTEUR_OBJECT_STRATEGIQUE , DESC_INDICACTEUR_OBJECT_STRATEGIQUE, ID_OBJECT_STRATEGIQUE FROM objectif_strategique_indicateur 
      WHERE 1 AND ID_OBJECT_STRATEGIQUE = {$id_objectif} ORDER BY DESC_INDICACTEUR_OBJECT_STRATEGIQUE ASC')");

    $html_indicateur = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($objectif_strategique_indicateur as $key)
    {
      $html_indicateur .= '<option value="'.$key->ID_INDICACTEUR_OBJECT_STRATEGIQUE .'">' . $key->DESC_INDICACTEUR_OBJECT_STRATEGIQUE . '</option>';
    }

    $output = array(
      "indicateur" => $html_indicateur
    );
    return $this->response->setJSON($output);
  }

  function liste_cl_cmr()
  {
    $session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }
        
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_CL_CMR_COSTAB_CATEGORIE = $this->request->getPost("ID_CL_CMR_COSTAB_CATEGORIE");

    $data ="SELECT planification_demande_cl_cmr_tempo.ID_PLANS_DEMANDE_CL_CMR,planification_demande_cl_cmr_tempo.ID_CL_CMR_CATEGORIE,planification_demande_cl_cmr_tempo.ID_PILIER,planification_demande_cl_cmr_tempo.ID_OBJECT_STRATEGIQUE,planification_demande_cl_cmr_tempo.ID_PLANS_INDICATEUR,planification_demande_cl_cmr_tempo.ID_DEMANDE,planification_demande_cl_cmr_tempo.PRECISIONS,planification_demande_cl_cmr_tempo.REFERENCE,planification_demande_cl_cmr_tempo.CIBLE,planification_demande_cl_cmr_tempo.ID_USER,
			inst_institutions.DESCRIPTION_INSTITUTION,pilier.DESCR_PILIER,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,objectif_strategique_indicateur.DESC_INDICACTEUR_OBJECT_STRATEGIQUE FROM planification_demande_cl_cmr_tempo JOIN inst_institutions ON planification_demande_cl_cmr_tempo.INSTITUTION_ID = inst_institutions.INSTITUTION_ID JOIN pilier ON planification_demande_cl_cmr_tempo.ID_PILIER = pilier.ID_PILIER JOIN objectif_strategique ON planification_demande_cl_cmr_tempo.ID_OBJECT_STRATEGIQUE = objectif_strategique.ID_OBJECT_STRATEGIQUE JOIN objectif_strategique_indicateur ON planification_demande_cl_cmr_tempo.ID_PLANS_INDICATEUR = objectif_strategique_indicateur.ID_INDICACTEUR_OBJECT_STRATEGIQUE WHERE 1 AND planification_demande_cl_cmr_tempo.ID_USER = {$user_id} AND planification_demande_cl_cmr_tempo.ID_CL_CMR_CATEGORIE = {$ID_CL_CMR_COSTAB_CATEGORIE} ORDER BY planification_demande_cl_cmr_tempo.ID_PLANS_DEMANDE_CL_CMR ASC";
    $data = "CALL `getTable`('".$data."');";
    $rqt = $this->ModelPs->getRequete($data);
    $count_data = count($rqt);

    $table = '';
    $table = '<div class="table-responsive">
               <table  id="tables_cl_cmr" class="table table-bordered table-hover table-striped table-condesed">
                  <thead>
                  <tr>
                      <th>INSTITUTION</th>
                      <th>PILIER</th>
                      <th>OBJECTIF</th>
                      <th>INDICATEUR</th>
                      <th>PRECISION</th>
                      <th>REFERENCE</th>
                      <th>CIBLE</th>
                      <th>ACTION</th>
                  </tr>
                  <thead><tbody>';
        
    foreach ($rqt as $row)
    {
      $table.="<tr>
                <td>".$row->DESCRIPTION_INSTITUTION."</td>
                <td>".$row->DESCR_PILIER."</td>
                <td>".$row->DESCR_OBJECTIF_STRATEGIC."</td>
                <td>".$row->DESC_INDICACTEUR_OBJECT_STRATEGIQUE."</td> 
                <td>".$row->PRECISIONS."</td> 
                <td>".$row->REFERENCE."</td>
                <td>".$row->CIBLE."</td>
                <td>
                  <a onclick='editercl_cmr(".$row->ID_PLANS_DEMANDE_CL_CMR.")' href='javascript:;' style='color: green'><i class='fa fa-pencil'></i> </a>&nbsp;&nbsp;
                  <a onclick='supprimer_cl_cmr(".$row->ID_PLANS_DEMANDE_CL_CMR.")' href='javascript:;' style='color: red'><i class='fa fa-trash'></i> </a>
                </td>
              </tr>";
    }
    $table.='</tbody><table/></div>';
    $table.='<script>
                   $(document).ready(function(){
             
                   $("#tables_cl_cmr").DataTable({
                        lengthMenu: [[2,10, 20,-1], [2,10, 20, "All"]],
                    pageLength: 2,
                      "columnDefs":[{
                          "targets":[],
                          "orderable":false
                      }],
  
                   language: {
                            "sProcessing":     "Traitement en cours...",
                            "sSearch":         "Rechercher&nbsp;:",
                            "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
                            "sInfo":           "Affichage de l\'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                            "sInfoEmpty":      "Affichage de l\'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
                            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                            "sInfoPostFix":    "",
                            "sLoadingRecords": "Chargement en cours...",
                            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
                            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
                            "oPaginate": {
                              "sFirst":      "Premier",
                              "sPrevious":   "Pr&eacute;c&eacute;dent",
                              "sNext":       "Suivant",
                              "sLast":       "Dernier"
                            },
                            "oAria": {
                              "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                              "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
                            }
                        }
                          
                });
            }); 
        </script>';

      $output = array('tabledata'=>$table, 'count_data'=>$count_data);
      echo json_encode($output);
  }

  public function supprimer_cl_cmr()
  {
    $db = db_connect();
    $id = $this->request->getPost('id');
    $critere = "ID_PLANS_DEMANDE_CL_CMR= {$id}";
    $table = "planification_demande_cl_cmr_tempo";
    $bindparams = [$db->escapeString($table), $db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";
    $this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
    echo json_encode(array('status'=>true));
  }

  public function editercl_cmr()
  {
    $id = $this->request->getPost("id");

    $cl_cmr_data = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT ID_PILIER,ID_OBJECT_STRATEGIQUE,ID_PLANS_INDICATEUR,PRECISIONS,REFERENCE,CIBLE,ID_PLANS_DEMANDE_CL_CMR,INSTITUTION_ID FROM planification_demande_cl_cmr_tempo WHERE 1 AND ID_PLANS_DEMANDE_CL_CMR = {$id} ORDER BY ID_PLANS_DEMANDE_CL_CMR ASC')");
    ###########################################################################################
    $institution_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT INSTITUTION_ID ,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1 ORDER BY INSTITUTION_ID ASC')");

    $html_institution='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($institution_data as $key)
    {
      $selected='';
      if($key->INSTITUTION_ID==$cl_cmr_data['INSTITUTION_ID'])
      {
        $selected=' selected';
      }
      $html_institution.='<option value="'.$key->INSTITUTION_ID.'"'.$selected.'>'.$key->DESCRIPTION_INSTITUTION.'</option>';
    }
    ##################################################################################################
    $indicateur_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_INDICACTEUR_OBJECT_STRATEGIQUE ,DESC_INDICACTEUR_OBJECT_STRATEGIQUE FROM objectif_strategique_indicateur WHERE 1 ORDER BY ID_INDICACTEUR_OBJECT_STRATEGIQUE ASC')");

    $html_indicateur='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($indicateur_data as $key)
    {
        $selected='';
        if($key->ID_INDICACTEUR_OBJECT_STRATEGIQUE==$cl_cmr_data['ID_PLANS_INDICATEUR'])
        {
            $selected=' selected';
        }
        $html_indicateur.='<option value="'.$key->ID_INDICACTEUR_OBJECT_STRATEGIQUE.'"'.$selected.'>'.$key->DESC_INDICACTEUR_OBJECT_STRATEGIQUE.'</option>';
    }
    #############################################################################################
    $piliers_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PILIER,DESCR_PILIER FROM pilier WHERE 1 ORDER BY ID_PILIER ASC')");

    $html_pilier='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($piliers_data as $key)
    {
        $selected='';
        if($key->ID_PILIER==$cl_cmr_data['ID_PILIER'])
        {
            $selected=' selected';
        }
        $html_pilier.='<option value="'.$key->ID_PILIER.'"'.$selected.'>'.$key->DESCR_PILIER.'</option>';
    }
    ##############################################################################################

    $objectif_strategique = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_OBJECT_STRATEGIQUE,DESCR_OBJECTIF_STRATEGIC FROM objectif_strategique 
        WHERE 1')");

    $html_objectif = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($objectif_strategique as $key)
    {
      $selected='';
      if($key->ID_OBJECT_STRATEGIQUE==$cl_cmr_data['ID_OBJECT_STRATEGIQUE'])
      {
          $selected=' selected';
      }
      $html_objectif.='<option value="'.$key->ID_OBJECT_STRATEGIQUE.'"'.$selected.'>'.$key->DESCR_OBJECTIF_STRATEGIC.'</option>';
    }

    $output = array(
        'status'=>true,
        "cl_cmr_data" => $cl_cmr_data,
        "html_institution" => $html_institution,
        "html_indicateur" => $html_indicateur,
        "html_pilier" => $html_pilier,
        "html_objectif" => $html_objectif,
    );
    return $this->response->setJSON($output);
  }

  public function save_cl_cmr()
  {
    $session  = \Config\Services::session();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if($user_id=='')
    {
        return redirect('Login_Ptba/do_logout');
    }

    $SOURCE = $this->request->getPost("SOURCE");
    $ID_PLANS_DEMANDE_CL_CMR = $this->request->getPost("ID_PLANS_DEMANDE_CL_CMR");
    $PRECISIONS = $this->request->getPost("PRECISIONS");
    $REFERENCE = $this->request->getPost("REFERENCE");
    $CIBLE = $this->request->getPost("CIBLE");
    $ID_PILIER = $this->request->getPost("ID_PILIER");
    $ID_OBJECT_STRATEGIQUE = $this->request->getPost("ID_OBJECT_STRATEGIQUE");
    $ID_PLANS_INDICATEUR = $this->request->getPost("ID_PLANS_INDICATEUR");
    $ID_CL_CMR_COSTAB_CATEGORIE = $this->request->getPost("ID_CL_CMR_COSTAB_CATEGORIE");
    $INSTITUTION_ID = $this->request->getPost("INSTITUTION_ID");
    $PRECISIONS = str_replace("'", "\'", $PRECISIONS);
    $REFERENCE = str_replace("'", "\'", $REFERENCE);
    $CIBLE = str_replace("'", "\'", $CIBLE);
      
    //add
    if($SOURCE==1)
    {
      $this->save_all_table("planification_demande_cl_cmr_tempo",
        "ID_CL_CMR_CATEGORIE, ID_PILIER, ID_OBJECT_STRATEGIQUE, ID_PLANS_INDICATEUR, PRECISIONS, REFERENCE, CIBLE, INSTITUTION_ID, ID_USER", "'{$ID_CL_CMR_COSTAB_CATEGORIE}','{$ID_PILIER}','{$ID_OBJECT_STRATEGIQUE}','{$ID_PLANS_INDICATEUR}' ,'{$PRECISIONS}','{$REFERENCE}','{$CIBLE}','{$INSTITUTION_ID}','{$user_id}'" );
    }
    else
    {
      $table = 'planification_demande_cl_cmr_tempo';
      $where='ID_PLANS_DEMANDE_CL_CMR='.$ID_PLANS_DEMANDE_CL_CMR.'';
      $data='ID_PILIER='.$ID_PILIER.', ID_OBJECT_STRATEGIQUE='.$ID_OBJECT_STRATEGIQUE.', ID_PLANS_INDICATEUR='.$ID_PLANS_INDICATEUR.', PRECISIONS="'.$PRECISIONS.'", REFERENCE='.$REFERENCE.', CIBLE='.$CIBLE.', INSTITUTION_ID='.$INSTITUTION_ID.' ';
      $this->update_all_table($table,$data,$where);
    }
    echo json_encode(array('status'=>true));
  }

  function liste_costab($ID_CL_CMR_COSTAB_CATEGORIE)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }
        
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data ="SELECT planification_demande_costab_tempo.*,inst_institutions.DESCRIPTION_INSTITUTION,enjeux.DESCR_ENJEUX,pilier.DESCR_PILIER,axe_intervention_pnd.DESCR_AXE_INTERVATION_PND,objectif_strategique.ID_OBJECT_STRATEGIQUE,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,inst_institutions_programmes.CODE_PROGRAMME,inst_institutions_programmes.INTITULE_PROGRAMME,ID_PLANS_PROJET FROM planification_demande_costab_tempo JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=planification_demande_costab_tempo.INSTITUTION_ID JOIN enjeux ON planification_demande_costab_tempo.ID_ENJEUX = enjeux.ID_ENJEUX JOIN pilier ON planification_demande_costab_tempo.ID_PILIER = pilier.ID_PILIER JOIN axe_intervention_pnd ON planification_demande_costab_tempo.ID_AXE_INTERVENTION_PND = axe_intervention_pnd.ID_AXE_INTERVENTION_PND JOIN objectif_strategique ON planification_demande_costab_tempo.ID_OBJECT_STRATEGIQUE = objectif_strategique.ID_OBJECT_STRATEGIQUE JOIN inst_institutions_programmes ON planification_demande_costab_tempo.PROGRAMME_ID = inst_institutions_programmes.PROGRAMME_ID WHERE 1 AND planification_demande_costab_tempo.ID_COSTAB_CATEGORIE=".$ID_CL_CMR_COSTAB_CATEGORIE." AND planification_demande_costab_tempo.ID_USER=".$user_id."";
    $data = "CALL `getTable`('".$data."');";
    $rqt = $this->ModelPs->getRequete($data);
    $count_data = count($rqt);

    $table = '';
    $table = '<div class="table-responsive">
                 <table  id="tables_costab" class="table table-bordered table-hover table-striped table-condesed">
                    <thead>
                    <tr>
                        <th>INSTITUTION</th>
                        <th>ENJEUX</th>
                        <th>PILIER</th>
                        <th>AXE&nbsp;INTERVENTION</th>
                        <th>OBJECTIF</th>
                        <th>PROGRAMME</th>
                        <th>PLANIFICATION</th>
                        <th>BUDGET&nbsp;ANNEE&nbsp;1</th>
                        <th>BUDGET&nbsp;ANNEE&nbsp;2</th>
                        <th>BUDGET&nbsp;ANNEE&nbsp;3</th>
                        <th>BUDGET&nbsp;ANNEE&nbsp;4</th>
                        <th>BUDGET&nbsp;ANNEE&nbsp;5</th>
                        <th>BUDGET&nbsp;TOTAL</th>
                        <th>ACTION</th>
                    </tr>
                    <thead><tbody>';

    foreach ($rqt as $row)
    {
      $table.="<tr>
                      <td>".$row->DESCRIPTION_INSTITUTION."</td>
                      <td>".$row->DESCR_ENJEUX."</td>
                      <td>".$row->DESCR_PILIER."</td>
                      <td>".$row->DESCR_AXE_INTERVATION_PND."</td> 
                      <td>".$row->DESCR_OBJECTIF_STRATEGIC."</td> 
                      <td>".$row->INTITULE_PROGRAMME."</td>
                      <td>".$row->ID_PLANS_PROJET."</td>
                      <td>".$row->BUDGET_ANNE_1."</td>
                      <td>".$row->BUDGET_ANNE_2."</td>
                      <td>".$row->BUDGET_ANNE_3."</td> 
                      <td>".$row->BUDGET_ANNE_4."</td> 
                      <td>".$row->BUDGET_ANNE_5."</td>
                      <td>".$row->BUDGET_TOTAL."</td>
                      <td>
                        <a onclick='editercostab(".$row->ID_PLANS_DEMANDE_COSTAB.")' href='javascript:;' style='color: green'><i class='fa fa-pencil'></i> </a>&nbsp;&nbsp;
                        <a onclick='supprimer_costab(".$row->ID_PLANS_DEMANDE_COSTAB.")' href='javascript:;' style='color: red'><i class='fa fa-trash'></i> </a>
                      </td>
                    </tr>";
    }
    $table.='</tbody><table/></div>';
    $table.='<script>
                   $(document).ready(function(){
             
                   $("#tables_costab").DataTable({
                        lengthMenu: [[2,10, 20,-1], [2,10, 20, "All"]],
                    pageLength: 2,
                      "columnDefs":[{
                          "targets":[],
                          "orderable":false
                      }],
  
                   language: {
                            "sProcessing":     "Traitement en cours...",
                            "sSearch":         "Rechercher&nbsp;:",
                            "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
                            "sInfo":           "Affichage de l\'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                            "sInfoEmpty":      "Affichage de l\'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
                            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                            "sInfoPostFix":    "",
                            "sLoadingRecords": "Chargement en cours...",
                            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
                            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
                            "oPaginate": {
                              "sFirst":      "Premier",
                              "sPrevious":   "Pr&eacute;c&eacute;dent",
                              "sNext":       "Suivant",
                              "sLast":       "Dernier"
                            },
                            "oAria": {
                              "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                              "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
                            }
                        }
                          
                });
            }); 
        </script>';

    $output = array('tabledata'=>$table, 'count_data'=>$count_data);
    echo json_encode($output);
  }
 
  public function supprimer_costab()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }
        
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $db = db_connect();
    $id = $this->request->getPost('id');
    $critere = "ID_PLANS_DEMANDE_COSTAB= {$id}";
    $table = "planification_demande_costab_tempo";
    $bindparams = [$db->escapeString($table), $db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";
    $this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
    echo json_encode(array('status'=>true));
  }

  public function editercostab()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }
        
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $id = $this->request->getPost("id");
    $costab_data = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT ID_ENJEUX,ID_PILIER,ID_AXE_INTERVENTION_PND,ID_OBJECT_STRATEGIQUE,PROGRAMME_ID,ID_PLANS_PROJET,ID_PLANS_DEMANDE_COSTAB,BUDGET_ANNE_1,BUDGET_ANNE_2,BUDGET_ANNE_3,BUDGET_ANNE_4,BUDGET_ANNE_5,BUDGET_TOTAL,ID_COSTAB_CATEGORIE,INSTITUTION_ID FROM planification_demande_costab_tempo WHERE 1 AND ID_PLANS_DEMANDE_COSTAB = {$id} ORDER BY ID_PLANS_DEMANDE_COSTAB ASC')");
    ##########################################################################################


    $institution_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT INSTITUTION_ID ,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1 ORDER BY INSTITUTION_ID ASC')");

    $html_institution='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($institution_data as $key)
    {
      $selected='';
      if($key->INSTITUTION_ID==$costab_data['INSTITUTION_ID'])
      {
        $selected=' selected';
      }
      $html_institution.='<option value="'.$key->INSTITUTION_ID.'"'.$selected.'>'.$key->DESCRIPTION_INSTITUTION.'</option>';
    }
    ##################################################################################################

    $bindenjeux = $this->getBindParms('ID_ENJEUX, DESCR_ENJEUX', 'enjeux', '1', 'ID_ENJEUX ASC');
    $enjeux = $this->ModelPs->getRequete($callpsreq, $bindenjeux);

    $html_enjeux='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($enjeux as $key)
    {
      $selected='';
      if($key->ID_ENJEUX==$costab_data['ID_ENJEUX'])
      {
          $selected=' selected';
      }
      $html_enjeux.='<option value="'.$key->ID_ENJEUX.'"'.$selected.'>'.$key->DESCR_ENJEUX.'</option>';
    }
    #############################################################################################

    $piliers_data = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PILIER,DESCR_PILIER FROM pilier WHERE 1 ORDER BY DESCR_PILIER ASC')");
    $html_pilier='<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($piliers_data as $key)
    {
      $selected='';
      if($key->ID_PILIER==$costab_data['ID_PILIER'])
      {
          $selected=' selected';
      }
      $html_pilier.='<option value="'.$key->ID_PILIER.'"'.$selected.'>'.$key->DESCR_PILIER.'</option>';
    }
    ##############################################################################################

    $axe_intervation = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_AXE_INTERVENTION_PND, DESCR_AXE_INTERVATION_PND FROM axe_intervention_pnd 
          WHERE 1 ORDER BY DESCR_AXE_INTERVATION_PND ASC')");
    $html_axe = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($axe_intervation as $key)
    {
      $selected='';
      if($key->ID_AXE_INTERVENTION_PND==$costab_data['ID_AXE_INTERVENTION_PND'])
      {
        $selected=' selected';
      }
      $html_axe.='<option value="'.$key->ID_AXE_INTERVENTION_PND.'"'.$selected.'>'.$key->DESCR_AXE_INTERVATION_PND.'</option>';
    }
    ########################################################################################
    $objectif_strategique = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_OBJECT_STRATEGIQUE,DESCR_OBJECTIF_STRATEGIC FROM objectif_strategique 
        WHERE 1')");
    $html_objectif = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($objectif_strategique as $key)
    {
      $selected='';
      if($key->ID_OBJECT_STRATEGIQUE==$costab_data['ID_OBJECT_STRATEGIQUE'])
      {
          $selected=' selected';
      }
      $html_objectif.='<option value="'.$key->ID_OBJECT_STRATEGIQUE.'"'.$selected.'>'.$key->DESCR_OBJECTIF_STRATEGIC.'</option>';
    }
    #########################################################################################
    $bindProgramme = $this->getBindParms('PROGRAMME_ID,INTITULE_PROGRAMME','inst_institutions_programmes','1','PROGRAMME_ID ASC');
    $programme = $this->ModelPs->getRequete($callpsreq,$bindProgramme);
    $html_programme = '<option value="">'.lang('messages_lang.selection_message').'</option>';
    foreach ($programme as $key)
    {
      $selected='';
      if($key->PROGRAMME_ID==$costab_data['PROGRAMME_ID'])
      {
          $selected=' selected';
      }
      $html_programme.='<option value="'.$key->PROGRAMME_ID.'"'.$selected.'>'.$key->INTITULE_PROGRAMME.'</option>';
    }
    #############################################################################################

    $output = array(
      'status'=>true,
      "costab_data" => $costab_data,
      "html_institution" => $html_institution,
      "html_enjeux" => $html_enjeux,
      "html_pilier" => $html_pilier,
      'html_axe'=>$html_axe,
      "html_objectif" => $html_objectif,
      "html_programme" => $html_programme,
    );
    return $this->response->setJSON($output);
  }
 
  public function save_costab()
  {
    $session  = \Config\Services::session();
    $user_id = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if($user_id=='')
    {
        return redirect('Login_Ptba/do_logout');
    }

    $SOURCE = $this->request->getPost("SOURCE");
    $ID_PLANS_DEMANDE_COSTAB = $this->request->getPost("ID_PLANS_DEMANDE_COSTAB");
    $ID_CL_CMR_COSTAB_CATEGORIE = $this->request->getPost("ID_CL_CMR_COSTAB_CATEGORIE");
    $ID_ENJEUX = $this->request->getPost("ID_ENJEUX");
    $ID_PILIER = $this->request->getPost("ID_PILIER");
    $ID_AXE_INTERVENTION_PND = $this->request->getPost("ID_AXE_INTERVENTION_PND");
    $ID_OBJECTIF = $this->request->getPost("ID_OBJECTIF");
    $PROGRAMME_ID = $this->request->getPost("PROGRAMME_ID");
    $ID_PLANS_PROJET = $this->request->getPost("ID_PLANS_PROJET");
    $BUDGET_ANNE_1 = $this->request->getPost("BUDGET_ANNEE_1");
    $BUDGET_ANNE_2 = $this->request->getPost("BUDGET_ANNEE_2");
    $BUDGET_ANNE_3 = $this->request->getPost("BUDGET_ANNEE_3");
    $BUDGET_ANNE_4 = $this->request->getPost("BUDGET_ANNEE_4");
    $BUDGET_ANNE_5 = $this->request->getPost("BUDGET_ANNEE_5");
    $BUDGET_TOTAL = $this->request->getPost("BUDGET_TOTAL");
    $INSTITUTION_ID2 = $this->request->getPost("INSTITUTION_ID2");
    $ID_PLANS_PROJET = str_replace("'", "\'", $ID_PLANS_PROJET);

    //add
    if($SOURCE==1)
    {
      $this->save_all_table("planification_demande_costab_tempo","ID_COSTAB_CATEGORIE,ID_ENJEUX,ID_PILIER,ID_AXE_INTERVENTION_PND,ID_OBJECT_STRATEGIQUE,PROGRAMME_ID,ID_PLANS_PROJET,BUDGET_ANNE_1,BUDGET_ANNE_2,BUDGET_ANNE_3,BUDGET_ANNE_4,BUDGET_ANNE_5,BUDGET_TOTAL,INSTITUTION_ID,ID_USER","'{$ID_CL_CMR_COSTAB_CATEGORIE}','{$ID_ENJEUX}','{$ID_PILIER}','{$ID_AXE_INTERVENTION_PND}','{$ID_OBJECTIF}','{$PROGRAMME_ID}','{$ID_PLANS_PROJET}','{$BUDGET_ANNE_1}','{$BUDGET_ANNE_2}','{$BUDGET_ANNE_3}','{$BUDGET_ANNE_4}','{$BUDGET_ANNE_5}','{$BUDGET_TOTAL}','{$INSTITUTION_ID2}','{$user_id}'");
    }
    else
    {
      $table = 'planification_demande_costab_tempo';
      $where='ID_PLANS_DEMANDE_COSTAB='.$ID_PLANS_DEMANDE_COSTAB.'';
      $data='ID_ENJEUX='.$ID_ENJEUX.',ID_PILIER='.$ID_PILIER.',ID_AXE_INTERVENTION_PND='.$ID_AXE_INTERVENTION_PND.', ID_OBJECT_STRATEGIQUE='.$ID_OBJECTIF.', PROGRAMME_ID='.$PROGRAMME_ID.', ID_PLANS_PROJET='.$ID_PLANS_PROJET.', BUDGET_ANNE_1='.$BUDGET_ANNE_1.', BUDGET_ANNE_2='.$BUDGET_ANNE_2.',BUDGET_ANNE_3='.$BUDGET_ANNE_3.',BUDGET_ANNE_4='.$BUDGET_ANNE_4.',BUDGET_ANNE_5='.$BUDGET_ANNE_5.',BUDGET_TOTAL='.$BUDGET_TOTAL.',INSTITUTION_ID='.$INSTITUTION_ID2.'';
      $this->update_all_table($table,$data,$where);
    }
    echo json_encode(array('status'=>true));
  }

  public function save_form_cl_cmr_costab()
	{
		$USER_ID = $this->session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if($USER_ID=='')
    {
     	return redirect('Login_Ptba/do_logout');
    }

    $session = \Config\Services::session();
    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_STRATEGIQUE') !=1 AND $session->get('SESSION_SUIVIE_PTBA_DEMANDE_PLANIFICATION_CDMT_CBMT') !=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $ID_DEMANDE = $this->request->getPost("ID_DEMANDE");
    $ACTION_ID = $this->request->getPost("ACTION_ID");
    $INSTITUTION_ID = $this->request->getPost("instit_id");

		### recuperation des ACTION_ID, ETAPE_ID, MOVETO, IS_INITIAL a partir d'une action qui vient en parametre
		$etap_data="SELECT ACTION_ID, ETAPE_ID, MOVETO, IS_INITIAL FROM proc_actions WHERE proc_actions.ACTION_ID = ".$ACTION_ID."";
		$etap_data = "CALL `getTable`('" . $etap_data . "');";
		$etap = $this->ModelPs->getRequeteOne($etap_data);
		$MOVETO = $etap['MOVETO'];
		$ETAPE_ACTUEL = $etap['ETAPE_ID'];
		$ACTION_ID = $etap['ACTION_ID'];
		#################################################################################################

		### recuperation du process a partir d'une etape de l'action en parametre
		$process_data ="SELECT PROCESS_ID FROM proc_etape WHERE proc_etape.ETAPE_ID = ".$etap['ETAPE_ID']."";
		$process_data = "CALL `getTable`('" . $process_data . "');";
		$process = $this->ModelPs->getRequeteOne($process_data);
		$PROCESS_ID = $process['PROCESS_ID'];

    if($etap['IS_INITIAL']==1)
    {
			//generate code demande
      //Récuperation de l'année budgétaire
      $annee_actu_id = $this->get_annee_budgetaire();
      $callpsreq = "CALL getRequete(?,?,?,?);";
      $bind=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN','annee_budgetaire','ANNEE_BUDGETAIRE_ID='.$annee_actu_id,'ANNEE_DEBUT ASC');
      $annees=$this->ModelPs->getRequeteOne($callpsreq, $bind);
      $ann_budg = $annees['ANNEE_DESCRIPTION'];

      //Récuperation du code de l'institution
      $bind_instit=$this->getBindParms('CODE_INSTITUTION','inst_institutions','INSTITUTION_ID='.$INSTITUTION_ID,'1');
      $code_instit=$this->ModelPs->getRequeteOne($callpsreq, $bind_instit);
			$CODE_DEMANDE = $code_instit['CODE_INSTITUTION'].'/'.$ann_budg;
    			
    	// insertion dans la table de demande
    	$ID_DEMANDE = $this->save_all_table("proc_demandes","CODE_DEMANDE,PROCESS_ID,ETAPE_ID,USER_ID","'{$CODE_DEMANDE}', '{$PROCESS_ID}', '{$MOVETO}', '{$USER_ID}'");
    			############################################################################################
    }
    else
    {
      //mise à jour dans la table proc_demandes / on recupere Next étape
      $table = 'proc_demandes';
      $where='ID_DEMANDE='.$ID_DEMANDE.'';
      $data='ETAPE_ID='.$MOVETO.'';
      $this->update_all_table($table,$data,$where);
      ######################################
      $ID_DEMANDE = $ID_DEMANDE;
    }

		//insertion dans la table historique apres une demande ou une action quelquonque
		$this->save_all_table("proc_demandes_historique","ID_DEMANDE,ETAPE_ID,USER_ID,ACTION_ID","'{$ID_DEMANDE}', '{$ETAPE_ACTUEL}', '{$USER_ID}', '{$ACTION_ID}'");
		#######################################################################################

		$cl_cmr_tempos = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PLANS_DEMANDE_CL_CMR,ID_CL_CMR_CATEGORIE,ID_PILIER,ID_OBJECT_STRATEGIQUE,ID_PLANS_INDICATEUR,PRECISIONS,REFERENCE,CIBLE,INSTITUTION_ID,ID_USER 
        FROM planification_demande_cl_cmr_tempo WHERE 1 AND ID_USER = {$USER_ID}')");

		if(!empty($cl_cmr_tempos))
		{
			foreach($cl_cmr_tempos as $cl_cmr_tempo)
			{
				$ID_CL_CMR_CATEGORIE = $cl_cmr_tempo->ID_CL_CMR_CATEGORIE;
				$ID_PILIER = $cl_cmr_tempo->ID_PILIER; 
				$ID_OBJECT_STRATEGIQUE = $cl_cmr_tempo->ID_OBJECT_STRATEGIQUE;
				$ID_PLANS_INDICATEUR = $cl_cmr_tempo->ID_PLANS_INDICATEUR; 
				$ID_DEMANDE = $ID_DEMANDE;
				$PRECISIONS = $cl_cmr_tempo->PRECISIONS;
        $PRECISIONS = str_replace("'", "\'", $PRECISIONS);
				$REFERENCE = $cl_cmr_tempo->REFERENCE;
        $REFERENCE = str_replace("'", "\'", $REFERENCE);
        $CIBLE = $cl_cmr_tempo->CIBLE;
        $CIBLE = str_replace("'", "\'", $CIBLE); 
				$INSTITUTION_ID = $cl_cmr_tempo->INSTITUTION_ID; 

				$insert_into_cl_cmr = $this->save_all_table("planification_demande_cl_cmr","ID_CL_CMR_CATEGORIE, ID_PILIER, ID_OBJECT_STRATEGIQUE, ID_PLANS_INDICATEUR, ID_DEMANDE, PRECISIONS, REFERENCE, CIBLE, INSTITUTION_ID","'{$ID_CL_CMR_CATEGORIE}','{$ID_PILIER}','{$ID_OBJECT_STRATEGIQUE}','{$ID_PLANS_INDICATEUR}','{$ID_DEMANDE}','{$PRECISIONS}','{$REFERENCE}','{$CIBLE}','{$INSTITUTION_ID}'");
			}

			$db = db_connect();
			$critere = "ID_USER = {$USER_ID}";
			$table = "planification_demande_cl_cmr_tempo";
			$bindparams = [$db->escapeString($table), $db->escapeString($critere)];
			$deleteRequete = "CALL `deleteData`(?,?);";
			$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
		}

		################################ Insertion In table COSTAB #########################################
		$costab_tempos = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_PLANS_DEMANDE_COSTAB,ID_COSTAB_CATEGORIE,ID_ENJEUX,ID_PILIER,ID_AXE_INTERVENTION_PND,ID_OBJECT_STRATEGIQUE,PROGRAMME_ID,ID_PLANS_PROJET,ID_DEMANDE,BUDGET_ANNE_1,BUDGET_ANNE_2,BUDGET_ANNE_3,BUDGET_ANNE_4,BUDGET_ANNE_5,BUDGET_TOTAL,INSTITUTION_ID,ID_USER FROM planification_demande_costab_tempo WHERE 1 AND ID_USER = {$USER_ID}')");

		if (!empty($costab_tempos))
		{
			foreach ($costab_tempos as $costab_tempo)
			{
				$ID_COSTAB_CATEGORIE_COSTAB = $costab_tempo->ID_COSTAB_CATEGORIE;
				$ID_ENJEUX_COSTAB = $costab_tempo->ID_ENJEUX;
				$ID_PILIER_COSTAB = $costab_tempo->ID_PILIER;
				$ID_AXE_INTERVENTION_PND_COSTAB = $costab_tempo->ID_AXE_INTERVENTION_PND;
				$ID_OBJECT_STRATEGIQUE_COSTAB = $costab_tempo->ID_OBJECT_STRATEGIQUE;
				$PROGRAMME_ID_COSTAB = $costab_tempo->PROGRAMME_ID;
				$ID_PLANS_PROJET_COSTAB = $costab_tempo->ID_PLANS_PROJET;
        $ID_PLANS_PROJET_COSTAB = str_replace("'", "\'", $ID_PLANS_PROJET_COSTAB); 
				$ID_DEMANDE_COSTAB = $ID_DEMANDE;
				$BUDGET_ANNE_1_COSTAB = $costab_tempo->BUDGET_ANNE_1;
				$BUDGET_ANNE_2_COSTAB = $costab_tempo->BUDGET_ANNE_2;
				$BUDGET_ANNE_3_COSTAB = $costab_tempo->BUDGET_ANNE_3;
				$BUDGET_ANNE_4_COSTAB = $costab_tempo->BUDGET_ANNE_4;
				$BUDGET_ANNE_5_COSTAB = $costab_tempo->BUDGET_ANNE_5;
        $BUDGET_TOTAL_COSTAB = $costab_tempo->BUDGET_TOTAL;
				$INSTITUTION_ID = $costab_tempo->INSTITUTION_ID;

				$insert_into_costab = $this->save_all_table("planification_demande_costab","ID_COSTAB_CATEGORIE,ID_ENJEUX,ID_PILIER,ID_AXE_INTERVENTION_PND,ID_OBJECT_STRATEGIQUE,PROGRAMME_ID,ID_PLANS_PROJET,ID_DEMANDE,BUDGET_ANNE_1,BUDGET_ANNE_2,BUDGET_ANNE_3,BUDGET_ANNE_4,BUDGET_ANNE_5,BUDGET_TOTAL,INSTITUTION_ID","'{$ID_COSTAB_CATEGORIE_COSTAB}','{$ID_ENJEUX_COSTAB}','{$ID_PILIER_COSTAB}','{$ID_AXE_INTERVENTION_PND_COSTAB}','{$ID_OBJECT_STRATEGIQUE_COSTAB}','{$PROGRAMME_ID_COSTAB}','{$ID_PLANS_PROJET_COSTAB}','{$ID_DEMANDE_COSTAB}','{$BUDGET_ANNE_1_COSTAB}','{$BUDGET_ANNE_2_COSTAB}','{$BUDGET_ANNE_3_COSTAB}','{$BUDGET_ANNE_4_COSTAB}','{$BUDGET_ANNE_5_COSTAB}','{$BUDGET_TOTAL_COSTAB}','{$INSTITUTION_ID}'");
			}

			$db = db_connect();
			$critere = "ID_USER = {$USER_ID}";
			$table = "planification_demande_costab_tempo";
			$bindparams = [$db->escapeString($table), $db->escapeString($critere)];
			$deleteRequete = "CALL `deleteData`(?,?);";
			$this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
		}
		$data=['message' => 
        ''.lang('messages_lang.message_success').''
    ];
    session()->setFlashdata('alert', $data);
    return redirect('process/Demandes');
	}

	/* Debut Gestion update table de la demande detail*/
  public function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }
  /* Fin Gestion update table de la demande detail*/
  public function save_all_table($table, $columsinsert, $datacolumsinsert)
	{
		$bindparms = [$table, $columsinsert, $datacolumsinsert];
		$insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
		$tableparams = [$table, $columsinsert, $datacolumsinsert];
		$result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
		return $id = $result['id'];
	}

	function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
		return $bindparams;
	}
}

?>
