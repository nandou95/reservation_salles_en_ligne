<?php
  /**Alain Charbel Nderagakura
    *Titre: Etat d'avancement double commande
    *Numero de telephone: (+257) 62003522
    *WhatsApp: (+257) 76887837
    *Email: charbel@mediabox.bi
    *Date: 24 juin,2024
    **/

  namespace App\Modules\double_commande_new\Controllers;   
  use App\Controllers\BaseController;
  use App\Models\ModelPs;
  use App\Libraries\CodePlayHelper;
  use App\Libraries\Notification;

  class Etat_avancement extends BaseController
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

    //Interface de la liste des activites
    function index($id=0)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      $user_id ='';
      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID'); 
      if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID'); 
      }
      else
      {
        return redirect('Login_Ptba/do_logout');
      }

      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $callpsreq = "CALL `getRequete`(?,?,?,?);";
      //selectionner les valeurs a mettre dans le menu en haut
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
      $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

      $ID_INST='';
      foreach ($getaffect as $value)
      {
        $ID_INST.=$value->INSTITUTION_ID.' ,';           
      }
      $ID_INST = substr($ID_INST,0,-1);

      $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
      $getInst = "CALL getTable('" .$getInst. "');";
      $data['institutions'] = $this->ModelPs->getRequete($getInst);
      return view('App\Modules\double_commande_new\Views\Etat_avancement_View',$data);
    }

    //récupération du sous titre par rapport à l'institution
    function get_sous_titre($INSTITUTION_ID=0)
    {
      if($this->session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $db = db_connect();
      $callpsreq = "CALL `getRequete`(?,?,?,?);";

      $get_sous_tutelle = $this->getBindParms('`SOUS_TUTEL_ID`,`DESCRIPTION_SOUS_TUTEL`', '`inst_institutions_sous_tutel` s_t JOIN inst_institutions inst ON inst.INSTITUTION_ID=s_t.`INSTITUTION_ID`', 'inst.INSTITUTION_ID='.$INSTITUTION_ID.' ', 'DESCRIPTION_SOUS_TUTEL  ASC');
      $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);

      $html='<option value="">Sélectionner</option>';
      foreach ($sous_tutelle as $key)
      {
        $html.='<option value="'.$key->SOUS_TUTEL_ID.'">'.$key->DESCRIPTION_SOUS_TUTEL.'</option>';
      }

      $output = array(
        "sous_tutel" => $html,
      );

      return $this->response->setJSON($output);
    }

    //fonction pour affichage d'une liste des activites
    public function listing()
    {
      $session  = \Config\Services::session();
      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      //Filtres de la liste
      $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
      $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
      $NUMERO_BON_ENGAGEMENT = $this->request->getPost('NUMERO_BON_ENGAGEMENT');
      $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
      $critere1="";$critere2="";$crit_num_bon="";
      // $critere3="AND exec.ETAPE_DOUBLE_COMMANDE_ID IN (SELECT DISTINCT ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_etape_double_commande WHERE PROFIL_ID=".$profil_id.")";
      if(!empty($INSTITUTION_ID))
      {
        $critere1=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
        if (!empty($SOUS_TUTEL_ID))
        {
          $critere1.=" AND budg.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
        }
      }
      if (!empty($NUMERO_BON_ENGAGEMENT))
      {
        $crit_num_bon=" AND exec.NUMERO_BON_ENGAGEMENT LIKE '%".$NUMERO_BON_ENGAGEMENT."%' ";
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

      $order_column=array('exec.NUMERO_BON_ENGAGEMENT','det.NUMERO_TITRE_DECAISSEMNT','lign.CODE_NOMENCLATURE_BUDGETAIRE','ptba.DESC_TACHE','mvt.DESC_MOUVEMENT_DEPENSE','dc.DESC_ETAPE_DOUBLE_COMMANDE');

      $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY NUMERO_BON_ENGAGEMENT ASC';

      $search = !empty($_POST['search']['value']) ?  (" AND (exec.NUMERO_BON_ENGAGEMENT LIKE '%".$var_search."%' OR det.NUMERO_TITRE_DECAISSEMNT LIKE '%".$var_search."%' OR lign.CODE_NOMENCLATURE_BUDGETAIRE LIKE '%".$var_search."%' OR ptba.DESC_TACHE LIKE '%".$var_search."%' OR mvt.DESC_MOUVEMENT_DEPENSE LIKE '%".$var_search."%' OR dc.DESC_ETAPE_DOUBLE_COMMANDE LIKE '%".$var_search."%') "):'';

      $critaire = $critere1." ".$crit_num_bon;
      //condition pour le query principale
      $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
      
      // condition pour le query filter
      $conditionsfilter = $critaire . " ". $search ." " . $group;

      $requetedebase="SELECT det.EXECUTION_BUDGETAIRE_ID,pap.DESC_PAP_ACTIVITE,lign.CODE_NOMENCLATURE_BUDGETAIRE,det.COMMENTAIRE,dc.ETAPE_DOUBLE_COMMANDE_ID,exec.NUMERO_BON_ENGAGEMENT,det.NUMERO_TITRE_DECAISSEMNT,dc.DESC_ETAPE_DOUBLE_COMMANDE,mvt.DESC_MOUVEMENT_DEPENSE,ptba.DESC_TACHE FROM execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID = exec.PTBA_TACHE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = det.ETAPE_DOUBLE_COMMANDE_ID JOIN budgetaire_mouvement_depense mvt ON dc.MOUVEMENT_DEPENSE_ID=mvt.MOUVEMENT_DEPENSE_ID LEFT JOIN pap_activites pap ON pap.PAP_ACTIVITE_ID=exec.PAP_ACTIVITE_ID JOIN inst_institutions_ligne_budgetaire lign ON lign.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1";
      
      $requetedebases=$requetedebase." ".$conditions;

      $requetedebasefilter=$requetedebase." ".$conditionsfilter;

      $query_secondaire = 'CALL `getTable`("'.$requetedebases.'");';

      $fetch_actions = $this->ModelPs->datatable($query_secondaire);
      
      $data = array();
      $u=1;
      foreach ($fetch_actions as $row)
      {
        $et_db_comm=($row->ETAPE_DOUBLE_COMMANDE_ID)?($row->ETAPE_DOUBLE_COMMANDE_ID):0;
        $getEtape = 'SELECT ETAPE_DOUBLE_COMMANDE_ACTUEL_ID, LINK_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande_config WHERE ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$et_db_comm;
        $getEtape = "CALL getTable('" . $getEtape . "');";
        $EtapeActuel= $this->ModelPs->getRequeteOne($getEtape);
        $step=($EtapeActuel) ? $EtapeActuel['LINK_ETAPE_DOUBLE_COMMANDE']:0;
        $dist="";
        if(!empty($row->ETAPE_DOUBLE_COMMANDE_ID))
        {
          if($row->ETAPE_DOUBLE_COMMANDE_ID==12) $dist="/1";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==13) $dist="/2";
          if($row->ETAPE_DOUBLE_COMMANDE_ID==14) $dist="/3";
        }
        
        $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
        $callpsreq = "CALL getRequete(?,?,?,?);";
        $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$row->ETAPE_DOUBLE_COMMANDE_ID,'PROFIL_ID DESC');
        $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);
        $number=$row->NUMERO_BON_ENGAGEMENT;
        if (!empty($getProfil))
        {
          foreach ($getProfil as $value)
          {
            if ($prof_id == $value->PROFIL_ID || $prof_id==1)
            {
              $number= "<a  title='' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_ID)."".$dist)."' >".$row->NUMERO_BON_ENGAGEMENT."</a>";

              $bouton= "<a class='btn btn-primary btn-sm' title='' href='".base_url($step."/".md5($row->EXECUTION_BUDGETAIRE_ID)."".$dist)."' ><span class='fa fa-arrow-up'></span></a>";
            }
          }
        }

        $TACHE = (mb_strlen($row->DESC_TACHE) > 4) ? (mb_substr($row->DESC_TACHE, 0, 4) . "...<a class='btn-sm' title='".$row->DESC_TACHE."'><i class='fa fa-eye'></i></a>") : $row->DESC_TACHE;

        $COMMENTAIRE = (mb_strlen($row->COMMENTAIRE) > 8) ? (mb_substr($row->COMMENTAIRE, 0, 8) . "...<a class='btn-sm' data-toggle='modal' title='".$row->COMMENTAIRE."'><i class='fa fa-eye'></i></a>") : $row->COMMENTAIRE;
        
        $ETAPE = (mb_strlen($row->DESC_ETAPE_DOUBLE_COMMANDE) > 9) ? (mb_substr($row->DESC_ETAPE_DOUBLE_COMMANDE, 0, 8) . '...<a class="btn-sm" data-toggle="modal" title="'.$row->DESC_ETAPE_DOUBLE_COMMANDE.'"><i class="fa fa-eye"></i></a>') : $row->DESC_ETAPE_DOUBLE_COMMANDE;

        $action='';
        $sub_array = array();
        $sub_array[] = $row->NUMERO_BON_ENGAGEMENT; //$number;
        $sub_array[] = $row->NUMERO_TITRE_DECAISSEMNT;
        $sub_array[] = $row->CODE_NOMENCLATURE_BUDGETAIRE;
        $sub_array[] = $TACHE;        
        // $sub_array[] = $COMMENTAIRE;
        $sub_array[] = $row->DESC_MOUVEMENT_DEPENSE;
        $sub_array[] = $ETAPE;
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

    //selectionner les etapes
    public function get_etape()
    {
      $callpsreq = "CALL `getRequete`(?,?,?,?);";
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

      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }

      $profil_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');

      $html='<option value="">Sélectionner</option>';
      $MOUVEMENT_DEPENSE_ID=$this->request->getPost('MOUVEMENT_DEPENSE_ID');
      if(!empty($MOUVEMENT_DEPENSE_ID))
      {
        $etape = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID ,DESC_ETAPE_DOUBLE_COMMANDE','execution_budgetaire_etape_double_commande','PROFIL_ID='.$profil_id.' AND MOUVEMENT_DEPENSE_ID='.$MOUVEMENT_DEPENSE_ID,'DESC_ETAPE_DOUBLE_COMMANDE ASC');
        $get_etape = $this->ModelPs->getRequete($callpsreq, $etape);
      }
      
      foreach($get_etape as $key)
      {
        $html.= "<option value='".$key->ETAPE_DOUBLE_COMMANDE_ID ."'>".$key->DESC_ETAPE_DOUBLE_COMMANDE."</option>";
      }
      $output = array('status' => TRUE ,'html' => $html);
      return $this->response->setJSON($output);//echo json_encode($output);
    }

    public function detail($EXECUTION_BUDGETAIRE_RACCROCHAGE_ID)
    {
      $data = $this->urichk();
      $session  = \Config\Services::session();
      if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
      {
        return redirect('Login_Ptba/do_logout');
      }
      if($session->get('SESSION_SUIVIE_PTBA_DOUBLE_COMMANDE_ETAT_AVANCEMENT') !=1)
      {
        return redirect('Login_Ptba/homepage'); 
      }
      $requetedebase="SELECT suppl.EXECUTION_BUDGETAIRE_RACCROCHAGE_INFO_SUPPL_ID,exec.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID,ptba.ACTIVITES,inst.CODE_INSTITUTION AS CODE_MINISTERE,actions.CODE_ACTION,progr.CODE_PROGRAMME,ptba.T1,ptba.T2,ptba.T3,ptba.T4,ptba.CODES_PROGRAMMATIQUE,MONTANT_RACCROCHE,MONTANT_RACCROCHE_JURIDIQUE,MONTANT_RACCROCHE_LIQUIDATION,budg.IMPUTATION,COMMENTAIRE,MONTANT_RACCROCHE_ORDONNANCEMENT,MONTANT_RACCROCHE_PAIEMENT,MONTANT_RACCROCHE_DECAISSEMENT,exec.TRIMESTRE_ID,suppl.NUMERO_BON_ENGAGEMENT,suppl.NUMERO_TITRE_DECAISSEMENT, inst.DESCRIPTION_INSTITUTION AS INTITULE_MINISTERE, progr.INTITULE_PROGRAMME,DESCRIPTION_SOUS_TUTEL,actions.LIBELLE_ACTION,exec.PTBA_ID,suppl.DATE_ENG_BUDGETAIRE,suppl.DATE_ENG_JURIDIQUE,suppl.DATE_LIQUIDATION,suppl.DATE_ORDONNANCEMENT,suppl.DATE_PAIMENT,suppl.DATE_DECAISSEMENT,suppl.DATE_PRISE_CHARGE,suppl.DATE_SIGNATURE_TD_MINISTRE,suppl.MONTANT_DEVISE_ENGAGEMENT_BUDGETAIRE,suppl.MONTANT_DEVISE_ENGAGEMENT_JURIDIQUE,suppl.MONTANT_EN_DEVISE,NOM_BANQUE,suppl.MOTIF_LIQUIDATION,suppl.MOTIF_PAIEMENT,suppl.DATE_APPROBATION_CONTRAT,suppl.DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR,suppl.DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE,suppl.DATE_TENUE_ATELIER,suppl.DATE_PRODUCTION_PROJET_LOI,suppl.MOTANT_FACTURE,suppl.DATE_RAPPROCHEMENT,suppl.DATE_PRODUCTION_BALANCE,suppl.DATE_PRODUCTION_COMPTE_RESULTAT,suppl.DATE_INTEGRATION_OBSERVATION,suppl.DATE_ELABORATION_TD,suppl.MONTANT_PRELEVEMENT_FISCALES,suppl.EXONERATION,DESCRIPTION_TAUX_TVA ,TITRE_CREANCE,MONTANT_CREANCE,suppl.PATH_BON_ENGAGEMENT,suppl.PATH_TITRE_DECAISSEMENT,suppl.PATH_PV_ATTRIBUTION,suppl.PATH_PPM,suppl.PATH_DAO,suppl.PATH_CONTRAT,suppl.PATH_PV_ATELIER,suppl.PATH_PROJET_LOI,suppl.PATH_CLASSIFICATION_ECONOMIQUE,suppl.PATH_CLASSIFICATION_ADMINISTRATIVE,suppl.PATH_VENTILATION_RECETTE,suppl.PATH_PV_RECEPTION,suppl.PATH_BORDEREAU_PV_RECEPTION,suppl.PATH_FACTURE,suppl.PATH_RAPPORT_COMPTE_GENERAL_TRESOR,suppl.PATH_BALANCE_COMPTE,suppl.PATH_LETTRE_OTB,suppl.PATH_LETTRE_TRANSMISSION,suppl.PATH_LISTE_PAIE,PATH_AVIS_DNCMP,suppl.DATE_CREANCE,suppl.MONTANT_DEVISE_LIQUIDATION,suppl.COUR_DEVISE_LIQUIDATION,budg.LIQUIDATION,suppl.DATE_DEVISE_LIQUIDATION, suppl.DATE_DEBUT_CONTRAT, suppl.DATE_FIN_CONTRAT, suppl.DATE_LIVRAISON_CONTRAT,suppl.TYPE_MONTANT_ID,exec.ID_TYPE_LIQUIDATION, exec.MARCHE_PUBLIQUE,exec.USER_ID FROM execution_budgetaire_raccrochage_activite exec LEFT JOIN ptba ON ptba.PTBA_ID = exec.PTBA_ID JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=ptba.PROGRAMME_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=ptba.INSTITUTION_ID JOIN inst_institutions_actions actions ON actions.ACTION_ID=ptba.ACTION_ID LEFT JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = exec.ETAPE_DOUBLE_COMMANDE_ID  JOIN execution_budgetaire budg ON budg.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_raccrochage_activite_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID=exec.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID LEFT JOIN inst_institutions_sous_tutel st ON st.SOUS_TUTEL_ID=budg.SOUS_TUTEL_ID LEFT JOIN banque ON banque.BANQUE_ID=suppl.BANQUE_ID LEFT JOIN taux_tva ON taux_tva.TAUX_TVA_ID = suppl.TAUX_TVA_ID WHERE MD5(exec.EXECUTION_BUDGETAIRE_RACCROCHAGE_ID)='".$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID."'";
      $data['get_info'] = $this->ModelPs->getRequeteOne('CALL `getTable`("' .$requetedebase. '")');
      return view('App\Modules\double_commande\Views\Liste_Activite_Detail',$data);
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