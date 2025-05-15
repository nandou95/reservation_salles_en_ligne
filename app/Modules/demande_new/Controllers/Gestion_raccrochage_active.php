<?php
/**
* HABIMANA NANDOU
* Titre: Gestion de raccrochage deja fait
* WhatsApp: +257 71 48 39 05
* Email pro: nandou@mediabox.bi
* Date: 09 octobre 2023
**/

namespace  App\Modules\demande_new\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

ini_set('max_execution_time', 0);
ini_set('memory_limit','12048M');

class Gestion_raccrochage_active extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	function index()
	{
		$session=\Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if(empty($user_id))
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER')!=1 && $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE')!=1)
    {
    	return redirect('Login_Ptba/homepage');
    }

		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$bindparms=$this->getBindParms('DISTINCT EXECUTION_BUDGETAIRE_ID','execution_budgetaire_raccrochage_activite','1','EXECUTION_BUDGETAIRE_ID ASC');
		$resultatss= $this->ModelPs->getRequete($callpsreq,$bindparms);
		if(!empty($resultatss))
		{
			foreach ($resultatss as $resultats)
			{
				$table="execution_budgetaire";
				$datatomodifie="IS_RACCROCHE=1";
				$conditions='EXECUTION_BUDGETAIRE_ID="'.$resultats->EXECUTION_BUDGETAIRE_ID.'"';
				$bindparams =[$table,$datatomodifie,$conditions];
				$updateRequete = "CALL `updateData`(?,?,?);";
				$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
			}
		}
	}
	
}
?>