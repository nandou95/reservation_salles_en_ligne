<?php
/**NIYONGABO Claude
*Titre: Tansmission brb vers MinFin
*Numero de telephone: 69 641 375
* claude@medaibox.bi
**/

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Transmission_Brb_MinFin extends BaseController
{
	protected $session;
	protected $ModelPs;
	public function __construct()
	{ 
		$this->library = new CodePlayHelper();
		$this->ModelPs = new ModelPs();
		$this->session = \Config\Services::session();
		$this->validation = \Config\Services::validation();
	}
	public function getBindParms($columnselect, $table, $where, $orderby)
	{
		$db = db_connect();
		$IMPORTndparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
		return $IMPORTndparams;
	}

	function transmettre()
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
		if($this->session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB_MINFIN') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}
		$callpsreq = "CALL getRequete(?,?,?,?);";
	
		$psgetrequete = "CALL `getRequete`(?,?,?,?);";
		$info = $this->getBindParms('`EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID`, `TITRE_DECAISSEMENT`, `ETAPE_DOUBLE_COMMANDE_ID`', 'execution_budgetaire_titre_decaissement', 'ETAPE_DOUBLE_COMMANDE_ID IN (45,46) ', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID DESC');
		$data['get_info'] = $this->ModelPs->getRequete($psgetrequete, $info);
		$data['titre']="Transmission des titres de décaissement à corriger";
		$data['etape']=3;
		$data['date_trans']=date('d-m-Y');
		return view('App\Modules\double_commande_new\Views\Transmission_Brb_MinFin_View',$data);
	}

	function save_transmission()
	{
		$data = $this->urichk();
		$session  = \Config\Services::session();
		$user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
		if (empty($user_id)) 
		{
			return redirect('Login_Ptba/do_logout');
		}

		if($this->session->get('SESSION_SUIVIE_PTBA_TRANSMISSION_BRB_MINFIN') !=1)
		{
			return redirect('Login_Ptba/homepage'); 
		}

    $psgetrequete = "CALL `getRequete`(?,?,?,?)";
    
    $rules = [
      
      'DATE_TRANSMISSION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      
      'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'uploaded' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
    ];

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run())
    {


    	$id_etape = $this->request->getPost('id_etape');
    	$callpsreq = "CALL `getRequete`(?,?,?,?);";
    	$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID = $this->request->getPost('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID[]');
    	$DATE_TRANSMISSION = $this->request->getPost('DATE_TRANSMISSION');

    	if (!empty($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)){
    		foreach ($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID as $value)
    		{
	    			// Récupérer l etape actuel execution_budgetaire_titre_decaissement
    			$etape_request = $this->getBindParms('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID', 'execution_budgetaire_titre_decaissement', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID="'.$value.'"', 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ASC');
    			$etape_request = str_replace('\"', '"', $etape_request);

    			$etape_request= $this->ModelPs->getRequeteOne($callpsreq, $etape_request);

	    			########################################
    			$table = 'execution_budgetaire_titre_decaissement';
    			$conditions = 'EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID =' . $value;
    			$datatomodifie = 'ETAPE_DOUBLE_COMMANDE_ID="47"';
    			$this->update_all_table($table, $datatomodifie, $conditions);

                   //insertion dans l'historique
    			$column_histo = "EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_TRANSMISSION";
    			$data_histo = $value . ',' . $etape_request['ETAPE_DOUBLE_COMMANDE_ID'] . ',' . $user_id . ',"' . $DATE_TRANSMISSION . '"';
    			$tablehist="execution_budgetaire_tache_detail_histo";
    			$this->save_all_table($tablehist,$column_histo, $data_histo);
    		}
    	}
    	return redirect('double_commande_new/Controles_Decaissement/correction_a_transmettre');
    }
	}

	/* Debut Gestion update table de la demande detail*/
	public function update_all_table($table,$datatomodifie,$conditions)
	{
		$bindparams =[$table,$datatomodifie,$conditions];
		$updateRequete = "CALL `updateData`(?,?,?);";
		$resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
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

}