<?php
/**HABIMANA NANDOU
*Titre : Génération du code programmatique des activités
*Téléphone : +257 71483905
*Email : nandou@mediabox.bi
*Date: 28 sept 2023
**/

namespace  App\Modules\demande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;
ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Gener_code_programatique extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParms($columnselect,$table,$where,$orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	public function index($value='')
	{
		$session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
    if(empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_DEMANDE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
    $bind_parms=$this->getBindParms("DISTINCT CODE_PROGRAMME","ptba","1","CODE_PROGRAMME ASC");
    $resultatprogramme=$this->ModelPs->getRequete($callpsreq,$bind_parms);
    if(!empty($resultatprogramme))
    {
    	foreach($resultatprogramme as $keyprogramme)
    	{
    		$bind_parmaction=$this->getBindParms("DISTINCT CODE_ACTION","ptba","CODE_PROGRAMME='".$keyprogramme->CODE_PROGRAMME."'",'CODE_ACTION ASC');
    		$bind_parmaction=str_replace("\'","'",$bind_parmaction);
    		$resultataction=$this->ModelPs->getRequete($callpsreq,$bind_parmaction);
    		if(!empty($resultataction))
    		{
    			foreach ($resultataction as $keyaction)
    			{
    				$bind_parmsactivite=$this->getBindParms("PTBA_ID,CODE_NOMENCLATURE_BUDGETAIRE,ACTIVITES","ptba","CODE_ACTION='".$keyaction->CODE_ACTION."' AND TRAITE=0","PTBA_ID ASC");
    				$bind_parmsactivite=str_replace("\'","'",$bind_parmsactivite);
    				$resultatactivite=$this->ModelPs->getRequete($callpsreq,$bind_parmsactivite);
    				if(!empty($resultatactivite))
    				{
    					$activite_value=1;
    					foreach($resultatactivite as $keyactivite)
    					{
    						$value_code="".$activite_value;
    						if($activite_value<100)
    						{
    							$value_code="0".$activite_value;
    						}
    						if($activite_value<10)
    						{
    							$value_code="00".$activite_value;
    						}
    						$CODES_PROGRAMMATIQUE_NEW="".$keyaction->CODE_ACTION."".$value_code;
    						$CODE_NOMENCLATURE_BUDGETAIRE_NEW="".$keyactivite->CODE_NOMENCLATURE_BUDGETAIRE."".$CODES_PROGRAMMATIQUE_NEW;
    						$set_colonnes='CODE_NOMENCLATURE_BUDGETAIRE_NEW="'.$CODE_NOMENCLATURE_BUDGETAIRE_NEW.'",CODES_PROGRAMMATIQUE_NEW="'.$CODES_PROGRAMMATIQUE_NEW.'",TRAITE=1';
    						$condiction='PTBA_ID="'.$keyactivite->PTBA_ID.'"';
    						$table="ptba";
    						$bindparams =[$table,$set_colonnes,$condiction];
								$updateRequete = "CALL `updateData`(?,?,?);";
								$resultat=$this->ModelPs->createUpdateDelete($updateRequete,$bindparams);
								$activite_value=$activite_value+1;
    					}
    				}
    			}
    		}
    	}
    }
	}
}
?>