<?php

/**
 * @author Bonfils de Jésus
 * PNUD page d'accueil grand publique
 */
namespace App\Controllers;
use App\Models\ModelPs;
use App\Models\ModelS;
//use App\Libraries\Utils;  //usage ya librairie utils
//use App\Libraries\CodePlayHelper;  //usage ya librairie CodePlayHelper



class Home extends BaseController
{
	protected $session;
    protected $ModelPs;
    
    public function __construct()
    {	
      $this->session = \Config\Services::session();

      $this->ModelPs = new ModelPs();
      $this->ModelS = new ModelS();
    }

    public function index()
    {
    	$sql='SELECT * FROM frontend_decret ORDER BY ID_DECRET DESC  ';
      	$data['frontend_decret'] = $this->ModelPs->getRequete("CALL `getTable`('" . $sql . "')");
        return view('Home_View',$data);
    }

    public function besd_info($value='')
    {
    	return view('besd_infoView');
    }

    public function newsletter()
    {
      $EMAIL = $this->request->getPost('EMAIL');

      //Verifier Email dans la table pricipales "'.$EMAIL.'"
       $columnselect='EMAIL';
       $table='frontend_newsletter';
       $where="EMAIL='".$EMAIL."'";
       $orderby='ID_NEWSLETTER  DESC';
       $where=str_replace("\'", "'", $where);
       $db = db_connect();

       $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
       $bindparams=str_replace("\'", "'", $bindparams);
       $callpsreq = "CALL `getRequete`(?,?,?,?);";
       $frontend_newsletter = $this->ModelPs->getRequeteOne($callpsreq, $bindparams);

       if (!empty($frontend_newsletter)) {
        //cas d'un Email existant
           $statut = 1;
       }else{
           $statut=2;

           $insertIntoTable = "frontend_newsletter";

           $datatoinsert = ' 
           "'.$EMAIL.'" ';

           $bindparams =[$insertIntoTable,$datatoinsert];
           $insertRequete = "CALL `insertIntoTable`(?,?);";
           $this->ModelPs->createUpdateDelete($insertRequete, $bindparams);
       }

       echo json_encode(array('statut'=>$statut));
    }
}
