<?php
/**MWENEMBUGA MUKUBWA Bonfils de Jésus
*Titre: geokgraphie des institution
*Numero de telephone: +257 62 48 32 80
*WhatsApp: +257 62 48 32 80
*Email pro: bonfils@mediabox.bi
*Email pers: mwenembugamukubwabonfils@gmail.com
*Date: 25 sept 2023
**/

namespace  App\Modules\geo\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;
use Config\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('max_execution_time', 2000);
ini_set('memory_limit','2048M'); 

class Carte_Institutions extends BaseController
{
	public function __construct()
	{
		$db = db_connect();
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->validation = \Config\Services::validation();
	}

	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	//get carte 
	public function index($value = 0)
	{
		$USER_IDD =session()->get("SESSION_SUIVIE_PTBA_USER_ID");
		if(empty($USER_IDD))
		{
			return redirect('Login_Ptba/do_logout');
		}
		
		$data=$this->urichk();
		$db = db_connect();
		$callpsreq = "CALL `getRequete`(?,?,?,?);";

		$coordinates = '-3.4279861,29.9247777';
		$zoom = 9;

		$get_data = '';
		$critere = '';

		$INSTITUTION = $this->request->getPost('INSTITUTION_ID');

		if(!empty($INSTITUTION))
		{
			if($INSTITUTION>0)
			{	
				$one_data=$this->getBindParms('LATITUDE,LONGITUDE','inst_institutions','INSTITUTION_ID ='.$INSTITUTION .' ','INSTITUTION_ID ASC');
				$infos_data=$this->ModelPs->getRequeteOne($callpsreq, $one_data);
				$coordinates=$infos_data['LATITUDE'].','.$infos_data['LONGITUDE'];
				$zoom=11; 
				$critere = ' AND inst_institutions.INSTITUTION_ID='.$INSTITUTION;
			}
		}

		$getRequete='SELECT inst_institutions.TYPE_INSTITUTION_ID,INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION,LATITUDE,LONGITUDE,DESC_TYPE_INSTITUTION FROM inst_institutions JOIN inst_types_institution ON inst_types_institution.TYPE_INSTITUTION_ID =inst_institutions.TYPE_INSTITUTION_ID  WHERE 1 '.$critere.' ';
		$get_data_table = $this->ModelPs->datatable("CALL `getTable`('" . $getRequete . "')");

		foreach ($get_data_table as $key) {

			$INSTITUTION_ID = $key->INSTITUTION_ID;
			$CODE_INSTITUTION = $key->CODE_INSTITUTION;
			$DESC_TYPE_INSTITUTION = $key->DESC_TYPE_INSTITUTION;

			$DESCRIPTION_INSTITUTION = trim($key->DESCRIPTION_INSTITUTION);
			$DESCRIPTION_INSTITUTION = str_replace("\n","",$DESCRIPTION_INSTITUTION);
			$DESCRIPTION_INSTITUTION = str_replace("\r","",$DESCRIPTION_INSTITUTION);
			$DESCRIPTION_INSTITUTION = str_replace("\t","",$DESCRIPTION_INSTITUTION);
			$DESCRIPTION_INSTITUTION = str_replace('"','',$DESCRIPTION_INSTITUTION);
			$DESCRIPTION_INSTITUTION = str_replace("'",'',$DESCRIPTION_INSTITUTION);

			$LATITUDE = $key->LATITUDE;
			$LONGITUDE = $key->LONGITUDE;

			if ($key->TYPE_INSTITUTION_ID==1) {
				$CODE_COULEUR = "#07784d";//Institution
			}else{
				$CODE_COULEUR = "#c021bb";//Ministère
			}

			$get_data = $get_data. $LATITUDE.'<>'.$LONGITUDE.'<>'.$CODE_COULEUR .'<>'.$DESC_TYPE_INSTITUTION.'<>'.$DESCRIPTION_INSTITUTION.'<>'.$CODE_INSTITUTION.'<>'.$INSTITUTION_ID.'@';
		}


        ######################### Legende ####################################
		$getLegende='SELECT INSTITUTION_ID,DESCRIPTION_INSTITUTION,CODE_INSTITUTION FROM inst_institutions WHERE 1 '.$critere.' ORDER BY DESCRIPTION_INSTITUTION ASC  ';
		$data_legende = $this->ModelPs->datatable("CALL `getTable`('" . $getLegende . "')");

		$legende = '<table class="table table-bordered table-condensed">
		<tr>
		<td><center><b>'.lang('messages_lang.labelle_nom_geo').'</b></center></td>
		<td><center><b>'.lang('messages_lang.labelle_budget_v').'</b></center></td>
		</tr>';

		foreach ($data_legende as $value) {

			$params_infos=$this->getBindParms('SUM(ptba.PROGRAMMATION_FINANCIERE_BIF) AS PROGRAMMATION_FINANCIERE_BIF','ptba','ptba.INSTITUTION_ID ='.$value->INSTITUTION_ID .' ','PTBA_ID ASC');
			$infos_sup=$this->ModelPs->getRequeteOne($callpsreq, $params_infos);

			$legende.= '<tr>
			<td><b style="font-size:11px">'.$value->DESCRIPTION_INSTITUTION.'</b></td>
			<td><b style="font-size:11px;color: #c5932c">'.number_format($infos_sup['PROGRAMMATION_FINANCIERE_BIF'],0,","," ").'</b></td>
			</tr>';
		}
		$legende.= '</table>';

		$data['coordinates'] = $coordinates;
		$data['zoom'] = $zoom;
		$data['get_data'] = $get_data;
		$data['legende'] = $legende;

		//get data institution
		$data['INSTITUTION_ID']=$INSTITUTION;
		$institution = $this->getBindParms('DESCRIPTION_INSTITUTION,INSTITUTION_ID', 'inst_institutions', '1', 'DESCRIPTION_INSTITUTION ASC');
		$data['institution'] = $this->ModelPs->getRequete($callpsreq, $institution);

		return view('App\Modules\geo\Views\Carte_Institutions_View',$data);
	}
}
?>