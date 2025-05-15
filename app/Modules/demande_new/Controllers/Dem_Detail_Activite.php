<?php
/**RUGAMBA Jean Vainqueur
  *Titre: Détail des activités
  *Numero de telephone: (+257) 66 33 43 25
  *WhatsApp: (+257) 62 47 19 15
  *Email: jean.vainqueur@mediabox.bi
  *Date: 17 Octobre,2023
  **/

namespace App\Modules\demande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Dem_Detail_Activite extends BaseController
{
  protected $session;
  protected $ModelPs;
    
  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
  }

  function index($id=0)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba/login');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_NOUVEAU_ACTIVITE')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $data = $this->urichk();
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";

    //Activite
    $bindparams = $this->getBindParms('PTBA_ID,inst.CODE_INSTITUTION AS CODE_MINISTERE,inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,prog.OBJECTIF_DU_PROGRAMME AS OBJECTIF_PROGRAMME,act.CODE_ACTION,CODES_PROGRAMMATIQUE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,act.LIBELLE_ACTION,act.OBJECTIF_ACTION,ARTICLE_ECONOMIQUE,INTITULE_ARTICLE_ECONOMIQUE,NATURE_ECONOMIQUE,INTITULE_NATURE_ECONOMIQUE,DIVISION_FONCTIONNELLE,INTITULE_DIVISION_FONCTIONNELLE,GROUPE_FONCTIONNELLE,INTITULE_GROUPE_FONCTIONNELLE,CLASSE_FONCTIONNELLE,INTITULE_CLASSE_FONCTIONNELLE,CODES_PROGRAMMATIQUE,ACTIVITES,RESULTATS_ATTENDUS,UNITE,QUANTITE_TOTALE_PROGRAMMATION_PHYSIQUE,QT1,QT2,QT3,QT4,COUT_UNITAIRE_BIF,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF,RESPONSABLE,GRANDE_MASSE_BP,GRANDE_MASSE_BM,INTITULE_DES_GRANDES_MASSES,GRANDE_MASSE_BM1,MONTANT_RESTANT_T1,MONTANT_RESTANT_T2,MONTANT_RESTANT_T3,MONTANT_RESTANT_T4','ptba JOIN inst_institutions inst ON ptba.INSTITUTION_ID=inst.INSTITUTION_ID JOIN inst_institutions_programmes prog ON ptba.PROGRAMME_ID=prog.PROGRAMME_ID JOIN inst_institutions_actions act ON ptba.ACTION_ID=act.ACTION_ID JOIN inst_institutions_ligne_budgetaire ligne ON ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID', 'PTBA_ID='.$id, '`PTBA_ID` ASC');
    $data['activite'] = $this->ModelPs->getRequeteOne($psgetrequete, $bindparams);

    $bind_tranche = $this->getBindParms('`TRANCHE_ID`,`CODE_TRANCHE`,CONCAT(`DATE_DEBUT`,"-",date_format(now(),"%Y")) as debut,CONCAT(`DATE_FIN`,"-",date_format(now(),"%Y")) as fin','op_tranches','1','`TRANCHE_ID`');
    $bind_tranche = str_replace('\"', '"', $bind_tranche);
    $tranches = $this->ModelPs->getRequete($psgetrequete, $bind_tranche);

    ///montant voté
    $bind_montant_vote = $this->getBindParms('SUM(`T1`) as T1, SUM(`T2`) as T2, SUM(`T3`) as T3, SUM(`T4`) as T4', 'ptba', '`PTBA_ID`='.$id, 'SUM(`T1`)');
    $bind_montant_vote = str_replace('\"', '"', $bind_montant_vote);

    $data['montant_vote'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_montant_vote);

    $data['montant_total'] = $data['montant_vote']['T1']+$data['montant_vote']['T2']+$data['montant_vote']['T3']+$data['montant_vote']['T4'];

    ///quantité voté
    $bind_quant_vote = $this->getBindParms('SUM(`QT1`) as QT1, SUM(`QT2`) as QT2, SUM(`QT3`) as QT3, SUM(`QT4`) as QT4', 'ptba', '`PTBA_ID`='.$id, 'SUM(`QT1`)');
    $bind_quant_vote = str_replace('\"', '"', $bind_quant_vote);

    $data['quant_vote'] = $this->ModelPs->getRequeteOne($psgetrequete, $bind_quant_vote);

    $data['quant_total'] = $data['quant_vote']['QT1']+$data['quant_vote']['QT2']+$data['quant_vote']['QT3']+$data['quant_vote']['QT4'];

      
    //montant execute par tranche
    $mont_exe = "SELECT SUM(racc.MONTANT_RACCROCHE) as EXECUTEE,SUM(racc.QTE_RACCROCHE) as quant_exec,tranche.CODE_TRANCHE FROM execution_budgetaire_raccrochage_activite_new racc JOIN ptba ON ptba.PTBA_ID=racc.PTBA_ID LEFT JOIN op_tranches tranche ON tranche.TRANCHE_ID=racc.TRIMESTRE_ID   WHERE 1 AND racc.PTBA_ID  = ".$id." GROUP BY tranche.CODE_TRANCHE";
    $mont_exe = 'CALL `getTable`("'.$mont_exe.'");';
    $execute = $this->ModelPs->getRequete($mont_exe);

    $executeMoney1 = 0;$executeMoney2 = 0;$executeMoney3 = 0;$executeMoney4 = 0;
    $executeQuant1 = 0;$executeQuant2 = 0;$executeQuant3 = 0;$executeQuant4 = 0;

    $reste1 = $data['montant_vote']['T1'];
    $reste2 = $data['montant_vote']['T2'];
    $reste3 = $data['montant_vote']['T3'];
    $reste4 = $data['montant_vote']['T4'];

    $quant_rest_t1 = $data['quant_vote']['QT1'];
    $quant_rest_t2 = $data['quant_vote']['QT2'];
    $quant_rest_t3 = $data['quant_vote']['QT3'];
    $quant_rest_t4 = $data['quant_vote']['QT4'];
    if(!empty($execute))
    {
      foreach ($execute as $value)
      {
        $bind_tranch = $this->getBindParms('CODE_TRANCHE','op_tranches','`CODE_TRANCHE`="'.$value->CODE_TRANCHE.'" ', 'CODE_TRANCHE');
        $bind_tranch = str_replace('\"','"', $bind_tranch);
        $tranc = $this->ModelPs->getRequeteOne($psgetrequete,$bind_tranch);

        if($tranc['CODE_TRANCHE'] == 'T1')
        {
          $executeMoney1 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
          $reste1 = $data['montant_vote']['T1'] - floatval($executeMoney1);
          $executeQuant1 = (!empty($value->quant_exec)) ? $value->quant_exec : 0 ;
          $quant_rest_t1 = $data['quant_vote']['QT1'] - floatval($executeQuant1);
        }
        else if ($tranc['CODE_TRANCHE'] == 'T2')
        {
          $executeMoney2 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
          $reste2 = $data['montant_vote']['T2'] - floatval($executeMoney2);
          $executeQuant2 = (!empty($value->quant_exec)) ? $value->quant_exec : 0 ;
          $quant_rest_t2 = $data['quant_vote']['QT2'] - floatval($executeQuant2);
        }
        else if ($tranc['CODE_TRANCHE'] == 'T3')
        {
          $executeMoney3 =(!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
          $reste3=($data['montant_vote']['T3'] -  floatval($executeMoney3));
          $executeQuant3 = (!empty($value->quant_exec)) ? $value->quant_exec : 0 ;
          $quant_rest_t3 = $data['quant_vote']['QT3'] - floatval($executeQuant3);
        }
        else
        {
          $executeMoney4 = (!empty($value->EXECUTEE)) ? $value->EXECUTEE:0;
          $reste4 = $data['montant_vote']['T4'] -  floatval($executeMoney4);
          $executeQuant4 = (!empty($value->quant_exec)) ? $value->quant_exec : 0 ;
          $quant_rest_t4 = $data['quant_vote']['QT4'] - floatval($executeQuant4);
        }
      } 
    }

    $data['tot_exe'] = $executeMoney1+$executeMoney2+$executeMoney3+$executeMoney4;
    $data['restant'] = $reste1+$reste2+$reste3+$reste4; 
    $data['tot_quant_exe'] = $executeQuant1+$executeQuant2+$executeQuant3+$executeQuant4;
    $data['quant_restant'] = $quant_rest_t1+$quant_rest_t2+$quant_rest_t3+$quant_rest_t4;

    $data['reste1']=$reste1;
    $data['reste2']=$reste2;
    $data['reste3']=$reste3;
    $data['reste4']=$reste4;
    $data['executeMoney1']=$executeMoney1;
    $data['executeMoney2']=$executeMoney2;
    $data['executeMoney3']=$executeMoney3;
    $data['executeMoney4']=$executeMoney4;

    $data['quant_rest_t1'] = $quant_rest_t1;
    $data['quant_rest_t2'] = $quant_rest_t2;
    $data['quant_rest_t3'] = $quant_rest_t3;
    $data['quant_rest_t4'] = $quant_rest_t4;
    $data['executeQuant1']=$executeQuant1;
    $data['executeQuant2']=$executeQuant2;
    $data['executeQuant3']=$executeQuant3;
    $data['executeQuant4']=$executeQuant4;
    return view('App\Modules\demande_new\Views\Dem_Detail_Activite_View',$data);
  }

         /**
   * fonction pour retourner le tableau des parametre pour le PS pour les selection
   * @param string  $columnselect //colone A selectionner
   * @param string  $table        //table utilisE
   * @param string  $where        //condition dans la clause where
   * @param string  $orderby      //order by
   * @return  mixed
   */
  public function getBindParms($columnselect, $table, $where, $orderby)
  {
    // code...
    $db = db_connect();
    // print_r($db->lastQuery);die();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

}
?>