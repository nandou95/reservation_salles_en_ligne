<?php
/*
*Joa-Kevin IRADUKUNDA
*Titre: Controller pour identifier les doublants dans les tables
*Numero de telephone: (+257) 62 636 535
*WhatsApp: (+27) 61 436 6546
*Email: joa-kevin.iradukunda@mediabox.bi
*Date: 25 novembre,2024
*/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;

class Doublants extends BaseController
{
	protected $session;
  	protected $ModelPs;

  	public function __construct()
	{
	    $this->ModelPs = new ModelPs();
	    $this->session = \Config\Services::session();
	}

	function doublant_pap_activite ()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$doublantsReq = "SELECT PAP_ACTIVITE_ID, DESC_PAP_ACTIVITE, COUNT(*) NOMBRE_APPARISSION FROM pap_activites GROUP BY DESC_PAP_ACTIVITE HAVING COUNT(*) > 1 ORDER BY NOMBRE_APPARISSION DESC";
		$doublants = $this->ModelPs->getRequete("CALL `getTable`('" . $doublantsReq . "')");

		print_r(count($doublants));
		print_r(" données répétées dans pap_activites");

		$u = 1;
		foreach ($doublants as $doublant)
		{	
			echo("<br>");
			print_r("#".$u. ": ");
			
			$DESC_PAP_ACTIVITE = str_replace("'","\'", $doublant->DESC_PAP_ACTIVITE);
			$doublantReq = "SELECT PAP_ACTIVITE_ID FROM pap_activites WHERE DESC_PAP_ACTIVITE = '".addslashes($DESC_PAP_ACTIVITE)."'";
			$doublantResults = $this->ModelPs->getRequete('CALL `getTable`("' . $doublantReq . '")');
			print_r(count($doublantResults). " apparitions =>\n");
			print('[');
			foreach($doublantResults as $doublantResult)
			{
				print($doublantResult->PAP_ACTIVITE_ID);
				print(',');
			}
			print(']');
			$u++;
		}
	}

	function doublant_costab_activite ()
	{
		$callpsreq = "CALL `getRequete`(?,?,?,?);";
		$doublantsReq = "SELECT COSTAB_ACTIVITE_ID , DESC_COSTAB_ACTIVITE, COUNT(*) NOMBRE_APPARISSION FROM costab_activites GROUP BY DESC_COSTAB_ACTIVITE HAVING COUNT(*) > 1 ORDER BY NOMBRE_APPARISSION DESC";
		$doublants = $this->ModelPs->getRequete("CALL `getTable`('" . $doublantsReq . "')");

		print_r(count($doublants));
		print_r(" données répétées dans costab_activites");

		$u = 1;
		foreach ($doublants as $doublant)
		{	
			echo("<br>");
			print_r("#".$u. ": ");

			$DESC_COSTAB_ACTIVITE = str_replace("'","\'", $doublant->DESC_COSTAB_ACTIVITE);
			$doublantReq = "SELECT COSTAB_ACTIVITE_ID FROM costab_activites WHERE DESC_COSTAB_ACTIVITE = '".addslashes($DESC_COSTAB_ACTIVITE)."'";
			$doublantResults = $this->ModelPs->getRequete('CALL `getTable`("' . $doublantReq . '")');
			print_r(count($doublantResults). " apparitions =>\n");
			print('[');
			foreach($doublantResults as $doublantResult)
			{
				print($doublantResult->COSTAB_ACTIVITE_ID);
				print(',');
			}
			print(']');
			$u++;
		}
	}
}

?>