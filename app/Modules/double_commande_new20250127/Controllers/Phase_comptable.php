<?php

/** 
 * controller pour la phase comptable du processus d execution budgetaire
 * @author: derick
 * email: derick@mediabox.bi
 * tel:77432485
 */

namespace App\Modules\double_commande_new\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Phase_comptable extends BaseController
{
  protected $session;
  protected $ModelPs;
  protected $library;
  protected $ModelS;
  protected $validation;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  public function prise_en_charge($BORDEREAU_TRANSMISSION_ID)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id))
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $etape_en_cour_id = 17;

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID='.$etape_en_cour_id,'PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);


    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";

          $numero_bordereau_trans_req = $this->getBindParms('NUMERO_BORDEREAU_TRANSMISSION, BORDEREAU_TRANSMISSION_ID', ' execution_budgetaire_bordereau_transmission', 'MD5(BORDEREAU_TRANSMISSION_ID)="' . $BORDEREAU_TRANSMISSION_ID . '"', 'BORDEREAU_TRANSMISSION_ID DESC');
          $numero_bordereau_trans_req = str_replace('\\', '', $numero_bordereau_trans_req);
          $numero_bordereau_trans_data = $this->ModelPs->getRequeteOne($callpsreq, $numero_bordereau_trans_req);

          $get_detail_id = $this->getBindParms('bon.EXECUTION_BUDGETAIRE_DETAIL_ID, det.EXECUTION_BUDGETAIRE_ID, det.ETAPE_DOUBLE_COMMANDE_ID', 'execution_budgetaire_bordereau_transmission_bon_titre bon JOIN  execution_budgetaire_tache_detail det ON bon.EXECUTION_BUDGETAIRE_DETAIL_ID = det.EXECUTION_BUDGETAIRE_DETAIL_ID', 'MD5(BORDEREAU_TRANSMISSION_ID)="' . $BORDEREAU_TRANSMISSION_ID . '"', 'BORDEREAU_TRANSMISSION_ID DESC');
          $get_detail_id = str_replace('\\', '', $get_detail_id);
          $detail_data = $this->ModelPs->getRequete($callpsreq, $get_detail_id);

          $id_detail = end($detail_data)->EXECUTION_BUDGETAIRE_DETAIL_ID;
          $new_id = end($detail_data)->EXECUTION_BUDGETAIRE_ID;
          $data['numero_bordereau_trans_data'] = $numero_bordereau_trans_data;
          $data['id_detail'] = end($detail_data)->ETAPE_DOUBLE_COMMANDE_ID;
          $data['detail_id'] = $id_detail;

          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'EXECUTION_BUDGETAIRE_DETAIL_ID="'.$id_detail.'"', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $request = "SELECT DISTINCT NUMERO_DOCUMENT,det.MONTANT_ORDONNANCEMENT,det.MONTANT_ORDONNANCEMENT_DEVISE,dev.DEVISE_TYPE_ID, bon.EXECUTION_BUDGETAIRE_DETAIL_ID FROM execution_budgetaire_bordereau_transmission_bon_titre bon JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=bon.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE BORDEREAU_TRANSMISSION_ID =".$numero_bordereau_trans_data['BORDEREAU_TRANSMISSION_ID']."";
          $bon_engagement_data = $this->ModelPs->getRequete("CALL getTable ('" . $request . "')");
          $data["bon_engagements"] = $bon_engagement_data;
          $data['infosup'] = $this->infosup_data($id_detail);

          $request_etape = "SELECT DESC_ETAPE_DOUBLE_COMMANDE FROM execution_budgetaire_etape_double_commande WHERE ETAPE_DOUBLE_COMMANDE_ID =".$etape_en_cour_id;
          $etape_data = $this->ModelPs->getRequeteOne("CALL getTable ('" . $request_etape . "')");
          $data["etapes"] = $etape_data;

          return view('App\Modules\double_commande_new\Views\Phase_comptable_prise_en_charge_view', $data);
        }
      }

      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  public function save_prise_en_charge () 
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_RECEPTION_PRISE_CHARGE') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $bon_engagements = $this->request->getPost("bon_engagement[]");
    $date_transmission = $this->request->getPost('date_transmission');
    $date_reception = $this->request->getPost('date_reception');
    $bene = $this->request->getPost('type_bene');
    $num_bordereau = $this->request->getPost('num_bordereau');
    $BORDEREAU_TRANSMISSION_ID = $this->request->getPost("BORDEREAU_TRANSMISSION_ID");
    $etape_en_cour_id = $this->request->getPost("etape_en_cour_id");
    $detail_id = $this->request->getPost('detail_id');
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    // print_r('bene:'.$bene);
    // print_r('tra:'.$BORDEREAU_TRANSMISSION_ID);
    // print_r($bon_engagements);
    // die();

    $rules = [
      "bon_engagement" => [
        "label" => "bon_engagement",
        "rules" => "required",
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
    ];

    $this->validation->setRules($rules);

    if($this->validation->withRequest($this->request)->run())
    {
      $conditions='BORDEREAU_TRANSMISSION_ID='.$BORDEREAU_TRANSMISSION_ID;
      $datatomodifie = 'STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=2';
      $this->update_all_table('execution_budgetaire_bordereau_transmission', $datatomodifie,$conditions);

      $conditions_2 = 'BORDEREAU_TRANSMISSION_ID=' . $BORDEREAU_TRANSMISSION_ID;
      $datatomodifie_2 = 'STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=3';
      $this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre',$datatomodifie_2, $conditions_2);

      /** la boucle pour enregistre le bon engagement */
      foreach ($bon_engagements as $value)
      {
        $conditions_3='BORDEREAU_TRANSMISSION_ID='.$BORDEREAU_TRANSMISSION_ID.' AND EXECUTION_BUDGETAIRE_DETAIL_ID='.$value;
        $datatomodifie_3 = 'STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=2';
        $this->update_all_table('execution_budgetaire_bordereau_transmission_bon_titre', $datatomodifie_3, $conditions_3);

        $beneficier_req = $this->getBindParms('suppl.TYPE_BENEFICIAIRE_ID', 'execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID='.$value , 'TYPE_BENEFICIAIRE_ID ASC');
        $get_beneficir_datas = $this->ModelPs->getRequeteOne($psgetrequete, $beneficier_req);
        $bene = $get_beneficir_datas['TYPE_BENEFICIAIRE_ID'];
        $NEXT_ETAPE_ID = 19;
        $conditions_2 = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$value;
        $datatomodifie_2 = 'ETAPE_DOUBLE_COMMANDE_ID='. $NEXT_ETAPE_ID;

        /** pour le fournisseur*/
        if($bene == 1)
        {
          $NEXT_ETAPE_ID = 18;
          $datatomodifie_2 = 'ETAPE_DOUBLE_COMMANDE_ID='. $NEXT_ETAPE_ID;
        }
        $this->update_all_table('execution_budgetaire_tache_detail', $datatomodifie_2,$conditions_2);
        //insertion dans l'historique
        $column_histo = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $data_histo = $value . ',' . $etape_en_cour_id . ',' . $user_id . ',"' . $date_reception . '","' . $date_transmission . '"';
        $this->save_histo($column_histo, $data_histo);
      }

      return redirect('double_commande_new/Liste_Reception_Prise_Charge/deja_recep');
    } else {
      redirect("double_commande_new/Phase_comptable/prise_en_charge/" . md5($id));
    }
  }

  /* debut reception obr*/
  public function reception_obr($id)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=18','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);


    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();

          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          $table = "execution_budgetaire_tache_detail det";
          $columnselect = "det.EXECUTION_BUDGETAIRE_DETAIL_ID, det.EXECUTION_BUDGETAIRE_ID,MONTANT_DECAISSEMENT,ETAPE_DOUBLE_COMMANDE_ID";
          $where = "md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$id."'";
          $orderby = 'EXECUTION_BUDGETAIRE_DETAIL_ID DESC';
          $where = str_replace("\'", "'", $where);
          $db = db_connect();
          $bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
          $bindparams34 = str_replace("\'", "'", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

          $EXECUTION_BUDGETAIRE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];
          $id = md5($data['id']['EXECUTION_BUDGETAIRE_DETAIL_ID']);

          $psgetrequete = "CALL getRequete(?,?,?,?);";
          $resultat = $this->getBindParms('ID_ANALYSE,DESCRIPTION', 'analyse_resultat', 'ID_ANALYSE in(1,2)', 'ID_ANALYSE ASC');
          $data['resultat_data'] = $this->ModelPs->getRequete($psgetrequete, $resultat);

          //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo hist JOIN  execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = hist.EXECUTION_BUDGETAIRE_DETAIL_ID', 'MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $table = "execution_budgetaire_etape_double_commande dc JOIN execution_budgetaire_tache_detail det ON det.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID";
          $columnselect = "DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_ORDONNANCEMENT,MONTANT_DECAISSEMENT";
          $where = "md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)='".$id."'";
          $orderby = '1 DESC';
          $where = str_replace("\'", "'", $where);
          $db = db_connect();
          $bindparamsetap = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
          $bindparamsetapes = str_replace("\'", "'", $bindparamsetap);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparamsetapes);

          $detail = $this->detail_new($id);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['creditVote']=$detail['creditVote'];
          $data['montant_reserve']=$detail['montant_reserve'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_reception_obr_view', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }   
  }

  /**
   * fonction pour enregistrer les info recu par
   */
  public function save_reception_obr()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $date_reception = $this->request->getPost('date_reception');
    $date_transmission = $this->request->getPost('date_transmission');
    $resultat = $this->request->getPost('resultat');
    $montant_fiscale = preg_replace('/\s/', '', $this->request->getPost('montant_fiscale'));
    $ETAPE_ID = 18;
    $id = (string)$this->request->getPost('id');

    $psgetrequete = "CALL getRequete(?,?,?,?);";

    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID='.$ETAPE_ID, '1 ASC');
    $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
    // $NEXT_ETAPE_ID = 19;
    $NEXT_ETAPE_ID=$get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

    // $bind_date_histo = $this->getBindParms('EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_tache_detail det', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID="'.$id.'"', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
    // $bind_date_histo = str_replace('\\', '', $bind_date_histo);
    // $id_new = $this->ModelPs->getRequeteOne($psgetrequete, $bind_date_histo)['EXECUTION_BUDGETAIRE_ID'];

    $rules = [
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'resultat' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run()) {
      $detail_id_query = $this->getBindParms('det.EXECUTION_BUDGETAIRE_DETAIL_ID ,det.EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_tache_detail det', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID =' . $id, '1 ASC');
      $detail_get_one = $this->ModelPs->getRequeteOne($psgetrequete, $detail_id_query);
      // $detail_id = $detail_get_one['EXECUTION_BUDGETAIRE_DETAIL_ID'];
      $id_new=$detail_get_one['EXECUTION_BUDGETAIRE_ID'];

      if ($resultat == 1) {
        $table = 'execution_budgetaire_tache_info_suppl';
        $conditions = 'EXECUTION_BUDGETAIRE_ID='.$id_new;
        $datatomodifie = 'MONTANT_PRELEVEMENT_FISCALES='.$montant_fiscale.',RESULTANT_TYPE_ID='.$resultat.'';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $table = 'execution_budgetaire_tache_detail';
        $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id;
        $datatomodifies = "ETAPE_DOUBLE_COMMANDE_ID=".$NEXT_ETAPE_ID;
        $this->update_all_table($table, $datatomodifies, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id . "," . $ETAPE_ID . "," . $user_id . ",'" . $date_reception . "','" . $date_transmission . "'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        return redirect('double_commande_new/Liste_Paiement');
      } else {
        $table = 'execution_budgetaire_tache_info_suppl';
        $conditions = 'EXECUTION_BUDGETAIRE_ID='.$id_new;
        $datatomodifie = 'RESULTANT_TYPE_ID='.$resultat;
        $this->update_all_table($table, $datatomodifie, $conditions);

        $table = 'execution_budgetaire_tache_detail';
        $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id;
        $datatomodifies = "ETAPE_DOUBLE_COMMANDE_ID=".$NEXT_ETAPE_ID;
        $this->update_all_table($table, $datatomodifies, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id . "," . $ETAPE_ID . "," . $user_id . ",'" . $date_reception . "','" . $date_transmission . "' ";
        $this->save_histo($columsinsert, $datacolumsinsert);

        return redirect('double_commande_new/Liste_Paiement');
      }
    } else {
      return $this->reception_obr(md5($id));
    }
  }
  /* fin reception obr*/

  //Enregistrement d'une nouvelle banque
  function save_newBanque()
  {
    $session  = \Config\Services::session();
    $callpsreq = "CALL getRequete(?,?,?,?);";

    $DESCRIPTION_BANQUE = addslashes($this->request->getPost('DESCRIPTION_BANQUE'));
    $ADRESSE_BANQUE = addslashes($this->request->getPost('ADRESSE_BANQUE'));
    $TYPE_INSTITUTION_FIN_ID = $this->request->getPost('TYPE_INSTITUTION_FIN_ID');

    
    $columsinsert = "NOM_BANQUE,TYPE_INSTITUTION_FIN_ID";
    $datacolumsinsert = "'{$DESCRIPTION_BANQUE}',{$TYPE_INSTITUTION_FIN_ID}";

    if(!empty($ADRESSE_BANQUE))
    {
      $columsinsert = "NOM_BANQUE,ADRESSE,TYPE_INSTITUTION_FIN_ID";
      $datacolumsinsert = "'{$DESCRIPTION_BANQUE}','{$ADRESSE_BANQUE}',{$TYPE_INSTITUTION_FIN_ID}";
    }

    $table="banque";    
    $BANQUE_ID=$this->save_all_table($table,$columsinsert,$datacolumsinsert);


    //récuperer les banques
    $bind_bank = $this->getBindParms('BANQUE_ID,NOM_BANQUE,ADRESSE,TYPE_INSTITUTION_FIN_ID','banque','1','NOM_BANQUE ASC');
    $bank = $this->ModelPs->getRequete($callpsreq, $bind_bank);

    $html='<option value="-1">'.lang('messages_lang.selection_autre').'</option>';

    if(!empty($bank))
    {
      foreach($bank as $key)
      {
        $selected="";
        if ($BANQUE_ID==$key->BANQUE_ID) {
          $selected=" selected";
        }
        $html.= "<option value='".$key->BANQUE_ID."'".$selected.">".$key->NOM_BANQUE."</option>";
      }
    }
    $output = array('status' => TRUE ,'banks' => $html);
    return $this->response->setJSON($output);
  }

  /* debut prise en charge comptable*/
  public function prise_en_charge_comptable($id_detail)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=19','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";
          $banque = $this->getBindParms('BANQUE_ID,NOM_BANQUE', 'banque', '1', 'NOM_BANQUE asc');
          $data['get_banque'] = $this->ModelPs->getRequete($callpsreq, $banque);

          //Institutions financières
          $bind_inst_fin = $this->getBindParms('TYPE_INSTITUTION_FIN_ID,DESC_TYPE','inst_institution_fin_type', '1', 'TYPE_INSTITUTION_FIN_ID asc');
          $data['get_inst_fin'] = $this->ModelPs->getRequete($callpsreq, $bind_inst_fin);

          ############# FOR ID RACCROCHAGE
          $query_id = $this->getBindParms('det.EXECUTION_BUDGETAIRE_DETAIL_ID,EXECUTION_BUDGETAIRE_ID', 'execution_budgetaire_tache_detail det', 'MD5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id_detail.'"', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
          $query_id = str_replace('\\', '', $query_id);
          $query_id = $this->ModelPs->getRequeteOne($callpsreq, $query_id);
          $id_exec_detail = (int)$query_id['EXECUTION_BUDGETAIRE_DETAIL_ID'];
          $id_exec = $query_id['EXECUTION_BUDGETAIRE_ID'];

          $columnselect="exec.COMMENTAIRE,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.EXECUTION_BUDGETAIRE_ID,det.ETAPE_DOUBLE_COMMANDE_ID, det.MONTANT_LIQUIDATION,det.MONTANT_LIQUIDATION_DEVISE";

          $table="execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID";
          
          $where="det.EXECUTION_BUDGETAIRE_DETAIL_ID='".$id_exec_detail."'";
          $orderby='det.EXECUTION_BUDGETAIRE_DETAIL_ID DESC';
          $where=str_replace("\'", "'", $where);
          $db=db_connect();
          $bindparamss = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
          $bindparams34 = str_replace("\'", "'", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);
          $data['infosup'] = $this->infosup_data($id_exec_detail);

          $columnselect_3="DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_ORDONNANCEMENT,dc.ETAPE_DOUBLE_COMMANDE_ID";

          $table_3 = "execution_budgetaire_etape_double_commande dc join execution_budgetaire_tache_detail det on  dc.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID";
          
          $where_3="det.EXECUTION_BUDGETAIRE_DETAIL_ID='".$id_exec_detail."'";
          $orderby_3 = '1 DESC';
          $where_3 = str_replace("\'", "'", $where_3);
          // $db = db_connect();
          $bindparamsetap=[$db->escapeString($columnselect_3),$db->escapeString($table_3),$db->escapeString($where_3),$db->escapeString($orderby_3)];
          $bindparamsetapes = str_replace("\'", "'", $bindparamsetap);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparamsetapes);
          
          $historique_raccrochage=$this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,DATE_TRANSMISSION','execution_budgetaire_tache_detail_histo hist','hist.EXECUTION_BUDGETAIRE_DETAIL_ID='.$id_exec_detail, 'DATE_INSERTION DESC');
          $historique_raccrochage = str_replace('\\', '', $historique_raccrochage);
          $data["historique_data_insertion"] =  $this->ModelPs->getRequeteOne("CALL getRequete(?,?,?,?);", $historique_raccrochage);

          //Récuperer les motifs
          $bind_motif=$this->getBindParms('BUDGETAIRE_TYPE_ANALYSE_ID ,DESC_BUDGETAIRE_TYPE_ANALYSE', 'budgetaire_type_analyse', 'MOUVEMENT_DEPENSE_ID=3', 'BUDGETAIRE_TYPE_ANALYSE_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

          $bind_raccr = $this->getBindParms('det.EXECUTION_BUDGETAIRE_ID,exec.DEVISE_TYPE_ID,ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_tache_detail det JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID', 'det.EXECUTION_BUDGETAIRE_DETAIL_ID="'. $id_exec_detail.'"', '1');
          $bind_raccr = str_replace('\\', '', $bind_raccr);
          $borderaux_transmission = $this->ModelPs->getRequeteOne("CALL getRequete(?,?,?,?);", $bind_raccr);
          $data['devise_type']=$borderaux_transmission['DEVISE_TYPE_ID'];

          $confirmation_formulaire = $this->getBindParms('ID_OPERATION, DESCRIPTION', 'budgetaire_type_operation_validation', '1 AND ID_OPERATION NOT IN(3)', 'DESCRIPTION DESC');
          $confirmation_formulaire = str_replace('\\', '', $confirmation_formulaire);
          $data['confirmation_formulaire_data'] = $this->ModelPs->getRequete($callpsreq, $confirmation_formulaire);
          $data["id_crypt"] = $id_exec_detail;

          $bind_motif=$this->getBindParms('TYPE_ANALYSE_MOTIF_ID,DESC_TYPE_ANALYSE_MOTIF','budgetaire_type_analyse_motif','IS_MARCHE=0','TYPE_ANALYSE_MOTIF_ID ASC');
          $data['motif_2'] = $this->ModelPs->getRequete($callpsreq,$bind_motif);

          //récuperer l'étape à corriger
          $step_correct = $this->getBindParms('ETAPE_RETOUR_CORRECTION_ID,DESCRIPTION_ETAPE_RETOUR', 'budgetaire_etape_retour_correction', 'ETAPE_RETOUR_CORRECTION_ID <> 1', 'ETAPE_RETOUR_CORRECTION_ID ASC');
          $data['etap_prise_en_charge'] = $this->ModelPs->getRequete($callpsreq, $step_correct);

          $detail = $this->detail_new($id_detail);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['creditVote']=$detail['creditVote'];
          $data['montant_reserve']=$detail['montant_reserve'];

          return view('App\Modules\double_commande_new\Views\Phase_comptable_prise_en_charge_comptable_view', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  public function save_prise_en_charge_comptable18072024()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $type_montant = $this->request->getPost('type_montant');
    $id = $this->request->getPost('id');
    $execution_budgetaire_detail_id = $this->request->getPost('id_detail');
    $Banque = $this->request->getPost('Banquess');
    $num_titre = $this->request->getPost('num_titre');
    $num_compte = $this->request->getPost('num_compte');
    $motif_paie = $this->request->getPost('motif_paie');
    $date_transmission = $this->request->getPost('date_transmission');
    $date_reception = $this->request->getPost('date_reception');
    $analyse = $this->request->getPost('analyse[]');
    $bene = $this->request->getPost('type_bene');
    $num_bordereau = $this->request->getPost('num_bordereau');
    $paiement_montant = (float)str_replace(' ', '', $this->request->getPost('paiement_montant'));
    $paiement_montant_devise = (float)$this->request->getPost('paiement_montant_dev');
    $date_paiement_devise = $this->request->getPost('date_paiement_devise');
    // $cour_paiement_devise = $this->request->getPost('cour_paiement_devise');
    $ordonancement = (float)$this->request->getPost('ordonancement');
    $montant_devise_ordonancement = (float)$this->request->getPost('MONTANT_DEVISE_ORDONNANCEMENT');
    $OPERATION = (int)$this->request->getPost('OPERATION');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost("ETAP_PRISE_EN_CHARGE");
    $NEXT_ETAPE_ID = null;
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $ETAPE_SUIVANTE_ID = null;
    $date_prise_en_charge = $this->request->getPost('date_prise_en_charge');

    $donnees = "".$paiement_montant."/-/".$paiement_montant_devise."/-/".$date_paiement_devise."/-/".$date_prise_en_charge."";

    //get montant existant dans execution_budgetaire
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $mont_paie_exista = $this->getBindParms('PAIEMENT,PAIEMENT_DEVISE','execution_budgetaire','EXECUTION_BUDGETAIRE_ID='.$id,'1 DESC');
    $montant_paie = $this->ModelPs->getRequeteOne($psgetrequete, $mont_paie_exista);

    $rules = [

      'Banquess' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'num_compte' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'motif_paie' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    if ($OPERATION == 1)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];

      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    }
    $this->validation->setRules($rules);

    if ($this->validation->withRequest($this->request)->run())
    {
      $success = false;
      $historique_table = "execution_budgetaire_tache_detail_histo";
      $update_table_details = "execution_budgetaire_tache_detail";
      $table_info_sup = "execution_budgetaire_tache_info_suppl";
      //$EXEC_BUDGET_RAC_ID = $this->request->getPost("id_crypt");

      // recuper les etat suivant et mouvement
      $execution_budgetaire_etape_double_commande_config = $this->getBindParms('execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_SUIVANT_ID = execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = ' . $ETAPE_ID, '1');
      $execution_budgetaire_etape_double_commande_config = str_replace('\\', '', $execution_budgetaire_etape_double_commande_config);
      $execution_budgetaire_etape_double_commande_config_data = $this->ModelPs->getRequeteOne("CALL getRequete(?,?,?,?);", $execution_budgetaire_etape_double_commande_config);
      $etat_suivante_id = $execution_budgetaire_etape_double_commande_config_data["ETAPE_DOUBLE_COMMANDE_SUIVANT_ID"];

      if (isset($TYPE_ANALYSE_MOTIF_ID) && !empty($TYPE_ANALYSE_MOTIF_ID))
      {
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif = 'execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $execution_budgetaire_detail_id . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
      }

      $insertToTable_motif_operation = 'execution_budgetaire_histo_operation_verification';
      $columninserthist_motif_operation = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
      $datatoinsert_histo_motif_operation = "" . $OPERATION . "," . $ETAPE_ID . "," . $id . "";
      $this->save_all_table($insertToTable_motif_operation, $columninserthist_motif_operation, $datatoinsert_histo_motif_operation);

      $updateTableDetail_1 = $update_table_details;
      $critere = "EXECUTION_BUDGETAIRE_DETAIL_ID = " . $execution_budgetaire_detail_id;
      $datatoupdate = 'MONTANT_PAIEMENT=' . $paiement_montant . ',ETAPE_DOUBLE_COMMANDE_ID=' . $etat_suivante_id . ',DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
      $bindparams = [$updateTableDetail_1, $datatoupdate, $critere];
      $RequetePS = 'CALL updateData(?,?,?);';
      $this->ModelPs->createUpdateDelete($RequetePS, $bindparams);

      $success = true;

      if (($bene == 1 and $type_montant == 1) or ($bene == 2 and  $type_montant == 1))
      {
        if ($OPERATION === 1)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');

          if ($ETAPE_RETOUR_CORRECTION_ID == 2)
          {
            //récuperer les etapes et mouvements
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 1 AND MOUVEMENT_DEPENSE_ID = 1', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
            //etape suivant
            $this->gestion_rejet_ptba($id);
          }
          elseif($ETAPE_RETOUR_CORRECTION_ID == 3)
          {
              //récuperer les etapes et mouvements
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=2', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
              //etape suivant
          }
          elseif ($ETAPE_RETOUR_CORRECTION_ID == 4)
          {
              //récuperer les etapes et mouvements
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=3', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
              //etape suivant
          }

          if ($type_montant != 1)
          {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
          }
          else
          {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
          }

          $conditions_infosup_1 = 'EXECUTION_BUDGETAIRE_ID='.$id;
          $datatomodifie_infosup_1 = 'COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
          $this->update_all_table($table_info_sup, $datatomodifie_infosup_1, $conditions_infosup_1);

          $columninserthist = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "'".$execution_budgetaire_detail_id."','".$user_id."','".$ETAPE_ID."','".$date_reception."','".$date_transmission."'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          $success = true;
          if (!empty($analyse))
          {
            foreach ($analyse as $an)
            {
              $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
            }
          }
          //insertion des motifs
          if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
            foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
              $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
            }
          }
        }
        // pour verifier si ce visa
        elseif ($OPERATION === 2)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');

          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', '1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          if ($type_montant != 1) {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$execution_budgetaire_detail_id;
            $datatomodifie5 = 'MONTANT_PAIEMENT='.$paiement_montant.', ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',MONTANT_PAIEMENT_DEVISE = '.$paiement_montant_devise.', DATE_PAIEMENT = "'.$date_paiement_devise.'",DATE_PRISE_CHARGE="' . $date_prise_en_charge . '"';;
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            //update dans execution
            $nouveau_mont1=floatval($montant_paie['PAIEMENT_DEVISE'])+$paiement_montant_devise;
            $nouveau_bif=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
            $table_exec1='execution_budgetaire';
            $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifie_exec1= 'PAIEMENT_DEVISE="'.$nouveau_mont1.'",PAIEMENT="'.$nouveau_bif.'"';
            $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
          } 
          else 
          {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'MONTANT_PAIEMENT='.$paiement_montant.', ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', DATE_PAIEMENT = "'.$date_prise_en_charge.'",DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
             //update dans execution
            $nouveau_mont1=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
            $table_exec1='execution_budgetaire';
            $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifie_exec1= 'PAIEMENT="'.$nouveau_mont1.'"';
            $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
          }

          $conditions_infosup_2 = 'EXECUTION_BUDGETAIRE_ID='.$id;
          $datatomodifie_infosup_2='COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
          $this->update_all_table($table_info_sup, $datatomodifie_infosup_2, $conditions_infosup_2);

          $columninserthist = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "'".$id."','".$user_id."','".$ETAPE_ID."','".$date_reception."','".$date_transmission."'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          if (!empty($analyse)) {
            foreach ($analyse as $an) {
              $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE__ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $execution_budgetaire_raccrochage_activite_detail_id . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
            }
          }
          $success = true;
        }
      }
      else
      {
        if ($paiement_montant_devise > $montant_devise_ordonancement)
        {
          $data = ['message' => "".lang('messages_lang.label_ordo_money')."" . " &nbsp;" . "(" . number_format($montant_devise_ordonancement, 0, ' ,', ' ') . ")"];
          session()->setFlashdata('alert', $data);
          return $this->prise_en_charge(md5($id));
        }
        else
        {
          //$produit = $paiement_montant_devise * $cour_paiement_devise;
          //  NON COMENTER 
          if ($OPERATION === 1)
          {
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            if ($ETAPE_RETOUR_CORRECTION_ID == 2)
            {
              //récuperer les etapes et mouvements
              $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 1 AND MOUVEMENT_DEPENSE_ID = 1', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
              $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
              $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID']; //etape suivant
              $this->gestion_rejet_ptba($id);
            }
            elseif ($ETAPE_RETOUR_CORRECTION_ID == 3)
            {
              //récuperer les etapes et mouvements
              $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=2', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
              $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
              $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID']; //etape suivant

              // $MOUVEMENT_NEXT_ID =  $get_next_step['MOUVEMENT_DEPENSE_ID']; // mouve qui va suivre
              #######################################         ##########################################
            }
            elseif ($ETAPE_RETOUR_CORRECTION_ID == 4)
            {
              //récuperer les etapes et mouvements
              $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION=1 AND MOUVEMENT_DEPENSE_ID=3', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
              $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
              $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID']; //etape suivant

              // $MOUVEMENT_NEXT_ID =  $get_next_step['MOUVEMENT_DEPENSE_ID']; // mouve qui va suivre
              #######################################         ##########################################
            }

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            // $devi_lik = $this->getBindParms('MONTANT_LIQUIDATION_DEVISE', 'execution_budgetaire_detail', ' EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id, '1');
            // $get_devi_lik = $this->ModelPs->getRequeteOne($psgetrequete, $devi_lik);

            if ($type_montant != 1) 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='. $NEXT_ETAPE_ID.',DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
            } 
            else 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$execution_budgetaire_detail_id;
              $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
            }

            // $conditions3 = 'EXECUTION_BUDGETAIRE_RACCROCHAGE_ID="' . $id . '"';
            // $datatomodifie3 = 'MONTANT_RACCROCHE_PAIEMENT=' . $produit;
            // $this->update_all_table($update_table_new, $datatomodifie3, $conditions3);

            //$table = 'execution_budgetaire_raccrochage_activite_info_suppl';
            $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            //$datatomodifie = 'MOTIF_PAIEMENT="' . $motif_paie . '",DATE_ENVOIE_OBR="' . $date_envoie_obr . '"';
            $datatomodifie = 'MOTIF_PAIEMENT="' . $motif_paie . '",DATE_PRISE_CHARGE="' . $date_prise_en_charge . '"';
            $this->update_all_table($update_table_details, $datatomodifie, $conditions);


            $conditions_infosup_7 = 'EXECUTION_BUDGETAIRE_ID=' . $id;
            $data_to_modifier_infosup_7 = 'COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
            $this->update_all_table($table_info_sup, $data_to_modifier_infosup_7, $conditions_infosup_7);

            $columninserthist = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "'" . $execution_budgetaire_detail_id . "','" . $user_id . "','" . $ETAPE_ID . "','" . $date_reception . "','" . $date_transmission . "'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            $success = true;
            if (!empty($analyse)) {
              foreach ($analyse as $an) {
                $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
              }
            }
            //insertion des motifs
            if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
              foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
                $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
              }
            }
          }

          // pour verifier si ce visa
          elseif ($OPERATION === 2) 
          {
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            // Mouvement depence
            $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);

            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', '1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            $devi_lik = $this->getBindParms('MONTANT_LIQUIDATION_DEVISE', 'execution_budgetaire_tache_detail', ' EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id, '1');
            $get_devi_lik = $this->ModelPs->getRequeteOne($psgetrequete, $devi_lik);

            if ($type_montant != 1) 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'MONTANT_PAIEMENT=' . $paiement_montant . ', ETAPE_DOUBLE_COMMANDE_ID=' . $NEXT_ETAPE_ID . ',MONTANT_PAIEMENT_DEVISE = ' . $get_devi_lik['MONTANT_LIQUIDATION_DEVISE'] . ', DATE_PAIEMENT = "' . $date_paiement_devise . '",DATE_PRISE_CHARGE="' . $date_prise_en_charge . '"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

              //update dans execution
              $nouveau_mont1=floatval($montant_paie['PAIEMENT_DEVISE'])+$get_devi_lik['MONTANT_LIQUIDATION_DEVISE'];
              $nouveau_bif=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
              $table_exec1='execution_budgetaire';
              $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifie_exec1= 'PAIEMENT_DEVISE="'.$nouveau_mont1.'",PAIEMENT="'.$nouveau_bif.'"';
              $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
            } 
            else 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'MONTANT_PAIEMENT=' . $paiement_montant . ', ETAPE_DOUBLE_COMMANDE_ID=' . $NEXT_ETAPE_ID . ', DATE_PAIEMENT = "' . $date_paiement_devise . '",DATE_PRISE_CHARGE="' . $date_prise_en_charge . '"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

              //update dans execution
              $nouveau_mont1=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
              $table_exec1='execution_budgetaire';
              $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifie_exec1= 'PAIEMENT="'.$nouveau_mont1.'"';
              $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
            }

            
            // $this->update_montant_execution_budgetaire($execution_budgetaire_raccrochage_activite_detail_id,$id,'PAIEMENT','MONTANT_RACCROCHE_PAIEMENT',$paiement_montant,$paiement_montant_devise,$detai_taux_echange_id);

            //$table = 'execution_budgetaire_raccrochage_activite_info_suppl';
            $conditions = 'EXECUTION_BUDGETAIRE_ID=' . $id;
            $datatomodifie = "BANQUE_ID='" . $Banque . "'";
            $this->update_all_table($table_info_sup, $datatomodifie, $conditions);

            $columninserthist = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "'".$execution_budgetaire_detail_id."','".$user_id."','".$ETAPE_ID."','".$date_reception . "','".$date_transmission."'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            if (!empty($analyse)) {
              foreach ($analyse as $an) {
                $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $execution_budgetaire_detail_id . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
              }
            }
            $success = true;
          }
        }
      }
      return redirect('double_commande_new/Liste_Paiement');
    } 
    else 
    {
      redirect("double_commande_new/Phase_comptable/prise_en_charge/" . md5($id));
    }
  }

  public function save_prise_en_charge_comptable()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id))
    {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $type_montant = $this->request->getPost('type_montant');
    $id = $this->request->getPost('id');
    $execution_budgetaire_detail_id = $this->request->getPost('id_detail');
    $Banque = $this->request->getPost('Banquess');
    $num_titre = $this->request->getPost('num_titre');
    $num_compte = $this->request->getPost('num_compte');
    $motif_paie = $this->request->getPost('motif_paie');
    $date_transmission = $this->request->getPost('date_transmission');
    $date_reception = $this->request->getPost('date_reception');
    $analyse = $this->request->getPost('analyse[]');
    $bene = $this->request->getPost('type_bene');
    $num_bordereau = $this->request->getPost('num_bordereau');
    $paiement_montant = (float)str_replace(' ', '', $this->request->getPost('paiement_montant'));
    $paiement_montant_devise = (float)$this->request->getPost('paiement_montant_dev');
    $date_paiement_devise = $this->request->getPost('date_paiement_devise');
    // $cour_paiement_devise = $this->request->getPost('cour_paiement_devise');
    $ordonancement = (float)$this->request->getPost('ordonancement');
    $montant_devise_ordonancement = (float)$this->request->getPost('MONTANT_DEVISE_ORDONNANCEMENT');
    $OPERATION = (int)$this->request->getPost('OPERATION');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    // $ETAPE_RETOUR_CORRECTION_ID = $this->request->getPost("ETAP_PRISE_EN_CHARGE");
    $NEXT_ETAPE_ID = null;
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $ETAPE_SUIVANTE_ID = null;
    $date_prise_en_charge = $this->request->getPost('date_prise_en_charge');

    $donnees = "".$paiement_montant."/-/".$paiement_montant_devise."/-/".$date_paiement_devise."/-/".$date_prise_en_charge."";

    //get montant existant dans execution_budgetaire
    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $mont_paie_exista = $this->getBindParms('PAIEMENT,PAIEMENT_DEVISE','execution_budgetaire','EXECUTION_BUDGETAIRE_ID='.$id,'1 DESC');
    $montant_paie = $this->ModelPs->getRequeteOne($psgetrequete, $mont_paie_exista);

    $rules = [

      'Banquess' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'num_compte' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'motif_paie' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];

    if ($OPERATION == 1)
    {
      $rules['TYPE_ANALYSE_MOTIF_ID'] = [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ];
    }
    $this->validation->setRules($rules);

    if ($this->validation->withRequest($this->request)->run())
    {
      $success = false;
      $historique_table = "execution_budgetaire_tache_detail_histo";
      $update_table_details = "execution_budgetaire_tache_detail";
      $table_info_sup = "execution_budgetaire_tache_info_suppl";
      //$EXEC_BUDGET_RAC_ID = $this->request->getPost("id_crypt");

      // recuper les etat suivant et mouvement
      $execution_budgetaire_etape_double_commande_config = $this->getBindParms('execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_SUIVANT_ID = execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = ' . $ETAPE_ID, '1');
      $execution_budgetaire_etape_double_commande_config = str_replace('\\', '', $execution_budgetaire_etape_double_commande_config);
      $execution_budgetaire_etape_double_commande_config_data = $this->ModelPs->getRequeteOne("CALL getRequete(?,?,?,?);", $execution_budgetaire_etape_double_commande_config);
      $etat_suivante_id = $execution_budgetaire_etape_double_commande_config_data["ETAPE_DOUBLE_COMMANDE_SUIVANT_ID"];

      if (isset($TYPE_ANALYSE_MOTIF_ID) && !empty($TYPE_ANALYSE_MOTIF_ID))
      {
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value)
        {
          $insertToTable_motif = 'execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $execution_budgetaire_detail_id . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
      }

      $insertToTable_motif_operation = 'execution_budgetaire_histo_operation_verification';
      $columninserthist_motif_operation = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
      $datatoinsert_histo_motif_operation = "" . $OPERATION . "," . $ETAPE_ID . "," . $id . "";
      $this->save_all_table($insertToTable_motif_operation, $columninserthist_motif_operation, $datatoinsert_histo_motif_operation);

      $updateTableDetail_1 = $update_table_details;
      $critere = "EXECUTION_BUDGETAIRE_DETAIL_ID = " . $execution_budgetaire_detail_id;
      $datatoupdate = 'MONTANT_PAIEMENT=' . $paiement_montant . ',ETAPE_DOUBLE_COMMANDE_ID=' . $etat_suivante_id . ',DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
      $bindparams = [$updateTableDetail_1, $datatoupdate, $critere];
      $RequetePS = 'CALL updateData(?,?,?);';
      $this->ModelPs->createUpdateDelete($RequetePS, $bindparams);

      $success = true;

      if (($bene == 1 and $type_montant == 1) or ($bene == 2 and  $type_montant == 1))
      {
        if ($OPERATION === 1)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');

          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          if ($type_montant != 1)
          {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
          }
          else
          {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
          }

          $conditions_infosup_1 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
          $datatomodifie_infosup_1 = 'COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
          $this->update_all_table($update_table_details, $datatomodifie_infosup_1, $conditions_infosup_1);

          $columninserthist = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "'".$execution_budgetaire_detail_id."','".$user_id."','".$ETAPE_ID."','".$date_reception."','".$date_transmission."'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          $success = true;
          if (!empty($analyse))
          {
            foreach ($analyse as $an)
            {
              $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
            }
          }
          //insertion des motifs
          if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
            foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
              $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
            }
          }
        }
        // pour verifier si ce visa
        elseif ($OPERATION === 2)
        {
          $ETAPE_ID = $this->request->getPost('ETAPE_ID');

          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', '1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
          $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          if ($type_montant != 1) {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$execution_budgetaire_detail_id;
            $datatomodifie5 = 'MONTANT_PAIEMENT='.$paiement_montant.', ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',MONTANT_PAIEMENT_DEVISE = '.$paiement_montant_devise.', DATE_PAIEMENT = "'.$date_paiement_devise.'",DATE_PRISE_CHARGE="' . $date_prise_en_charge . '"';;
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

            //update dans execution
            $nouveau_mont1=floatval($montant_paie['PAIEMENT_DEVISE'])+$paiement_montant_devise;
            $nouveau_bif=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
            $table_exec1='execution_budgetaire';
            $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifie_exec1= 'PAIEMENT_DEVISE="'.$nouveau_mont1.'",PAIEMENT="'.$nouveau_bif.'"';
            $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
          } 
          else 
          {
            $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            $datatomodifie5 = 'MONTANT_PAIEMENT='.$paiement_montant.', ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.', DATE_PAIEMENT = "'.$date_prise_en_charge.'",DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
            $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
             //update dans execution
            $nouveau_mont1=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
            $table_exec1='execution_budgetaire';
            $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
            $datatomodifie_exec1= 'PAIEMENT="'.$nouveau_mont1.'"';
            $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
          }

          $conditions_infosup_2 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
          $datatomodifie_infosup_2='COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
          $this->update_all_table($update_table_details, $datatomodifie_infosup_2, $conditions_infosup_2);

          $columninserthist = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
          $datatoinsert_histo_op = "'".$id."','".$user_id."','".$ETAPE_ID."','".$date_reception."','".$date_transmission."'";
          $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
          if (!empty($analyse)) {
            foreach ($analyse as $an) {
              $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE__ID";
              $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $execution_budgetaire_raccrochage_activite_detail_id . "";
              $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
            }
          }
          $success = true;
        }
      }
      else
      {
        if ($paiement_montant_devise > $montant_devise_ordonancement)
        {
          $data = ['message' => "".lang('messages_lang.label_ordo_money')."" . " &nbsp;" . "(" . number_format($montant_devise_ordonancement, 0, ' ,', ' ') . ")"];
          session()->setFlashdata('alert', $data);
          return $this->prise_en_charge(md5($id));
        }
        else
        {
          //$produit = $paiement_montant_devise * $cour_paiement_devise;
          //  NON COMENTER 
          if ($OPERATION === 1)
          {
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config JOIN execution_budgetaire_etape_double_commande ON execution_budgetaire_etape_double_commande_config.ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=execution_budgetaire_etape_double_commande.ETAPE_DOUBLE_COMMANDE_ID', 'IS_CORRECTION = 1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID = '.$ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);
            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            // $devi_lik = $this->getBindParms('MONTANT_LIQUIDATION_DEVISE', 'execution_budgetaire_detail', ' EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id, '1');
            // $get_devi_lik = $this->ModelPs->getRequeteOne($psgetrequete, $devi_lik);

            if ($type_montant != 1) 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='. $NEXT_ETAPE_ID.',DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
            } 
            else 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$execution_budgetaire_detail_id;
              $datatomodifie5 = 'ETAPE_DOUBLE_COMMANDE_ID='.$NEXT_ETAPE_ID.',DATE_PRISE_CHARGE="'.$date_prise_en_charge.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);
            }

            // $conditions3 = 'EXECUTION_BUDGETAIRE_RACCROCHAGE_ID="' . $id . '"';
            // $datatomodifie3 = 'MONTANT_RACCROCHE_PAIEMENT=' . $produit;
            // $this->update_all_table($update_table_new, $datatomodifie3, $conditions3);

            //$table = 'execution_budgetaire_raccrochage_activite_info_suppl';
            $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
            //$datatomodifie = 'MOTIF_PAIEMENT="' . $motif_paie . '",DATE_ENVOIE_OBR="' . $date_envoie_obr . '"';
            $datatomodifie = 'MOTIF_PAIEMENT="'.$motif_paie.'",DATE_PRISE_CHARGE="'.$date_prise_en_charge.'",COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
            $this->update_all_table($update_table_details, $datatomodifie, $conditions);


            // $conditions_infosup_7 = 'EXECUTION_BUDGETAIRE_ID=' . $id;
            // $data_to_modifier_infosup_7 = 'COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
            // $this->update_all_table($table_info_sup, $data_to_modifier_infosup_7, $conditions_infosup_7);

            $columninserthist = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "'" . $execution_budgetaire_detail_id . "','" . $user_id . "','" . $ETAPE_ID . "','" . $date_reception . "','" . $date_transmission . "'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            $success = true;
            if (!empty($analyse)) {
              foreach ($analyse as $an) {
                $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
              }
            }
            //insertion des motifs
            if (!empty($TYPE_ANALYSE_MOTIF_ID)) {
              foreach ($TYPE_ANALYSE_MOTIF_ID as $an) {
                $columsinsert = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $id . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification_motif",$columsinsert, $datacolumsinsert);
              }
            }
          }

          // pour verifier si ce visa
          elseif ($OPERATION === 2) 
          {
            $ETAPE_ID = $this->request->getPost('ETAPE_ID');
            // Mouvement depence
            $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ID ASC');
            $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);

            $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', '1 AND ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
            $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

            $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

            $psgetrequete = "CALL getRequete(?,?,?,?);";
            $devi_lik = $this->getBindParms('MONTANT_LIQUIDATION_DEVISE', 'execution_budgetaire_tache_detail', ' EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id, '1');
            $get_devi_lik = $this->ModelPs->getRequeteOne($psgetrequete, $devi_lik);

            if ($type_montant != 1) 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'MONTANT_PAIEMENT=' . $paiement_montant . ', ETAPE_DOUBLE_COMMANDE_ID=' . $NEXT_ETAPE_ID . ',MONTANT_PAIEMENT_DEVISE = ' . $get_devi_lik['MONTANT_LIQUIDATION_DEVISE'] . ', DATE_PAIEMENT = "' . $date_paiement_devise . '",DATE_PRISE_CHARGE="'.$date_prise_en_charge.'",COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

              //update dans execution
              $nouveau_mont1=floatval($montant_paie['PAIEMENT_DEVISE'])+$get_devi_lik['MONTANT_LIQUIDATION_DEVISE'];
              $nouveau_bif=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
              $table_exec1='execution_budgetaire';
              $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifie_exec1= 'PAIEMENT_DEVISE="'.$nouveau_mont1.'",PAIEMENT="'.$nouveau_bif.'"';
              $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
            } 
            else 
            {
              $conditions5 = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $execution_budgetaire_detail_id;
              $datatomodifie5 = 'MONTANT_PAIEMENT=' . $paiement_montant . ', ETAPE_DOUBLE_COMMANDE_ID=' . $NEXT_ETAPE_ID . ', DATE_PAIEMENT = "'.$date_paiement_devise.'",DATE_PRISE_CHARGE="'.$date_prise_en_charge.'",COMPTE_CREDIT="'.$num_compte.'",BANQUE_ID="'.$Banque.'"';
              $this->update_all_table($update_table_details, $datatomodifie5, $conditions5);

              //update dans execution
              $nouveau_mont1=floatval($montant_paie['PAIEMENT'])+$paiement_montant;
              $table_exec1='execution_budgetaire';
              $conditions_exec1='EXECUTION_BUDGETAIRE_ID='.$id;
              $datatomodifie_exec1= 'PAIEMENT="'.$nouveau_mont1.'"';
              $this->update_all_table($table_exec1,$datatomodifie_exec1,$conditions_exec1);
            }
            
            // $this->update_montant_execution_budgetaire($execution_budgetaire_raccrochage_activite_detail_id,$id,'PAIEMENT','MONTANT_RACCROCHE_PAIEMENT',$paiement_montant,$paiement_montant_devise,$detai_taux_echange_id);

            //$table = 'execution_budgetaire_raccrochage_activite_info_suppl';
            // $conditions = 'EXECUTION_BUDGETAIRE_ID=' . $id;
            // $datatomodifie = "BANQUE_ID='" . $Banque . "'";
            // $this->update_all_table($table_info_sup, $datatomodifie, $conditions);

            $columninserthist = "EXECUTION_BUDGETAIRE_DETAIL_ID, USER_ID, ETAPE_DOUBLE_COMMANDE_ID, DATE_RECEPTION, DATE_TRANSMISSION";
            $datatoinsert_histo_op = "'".$execution_budgetaire_detail_id."','".$user_id."','".$ETAPE_ID."','".$date_reception . "','".$date_transmission."'";
            $this->save_all_table($historique_table, $columninserthist, $datatoinsert_histo_op);
            if (!empty($analyse)) {
              foreach ($analyse as $an) {
                $columsinsert = "TYPE_ANALYSE_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_ID";
                $datacolumsinsert = $an . "," . $ETAPE_ID . "," . $execution_budgetaire_detail_id . "";
                $this->save_all_table("execution_budgetaire_histo_operation_verification",$columsinsert, $datacolumsinsert);
              }
            }
            $success = true;
          }
        }
      }
      return redirect('double_commande_new/Liste_Paiement');
    } 
    else 
    {
      redirect("double_commande_new/Phase_comptable/prise_en_charge/" . md5($id));
    }
  }
  /* fin prise en charge comptable*/

  /* debut prise en charge etablissement du titre*/
  public function prise_en_charge_etablissement_20240712($id = '')
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=20','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";
          $bindparamss = $this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.MONTANT_DECAISSEMENT,ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"','exec.EXECUTION_BUDGETAIRE_ID DESC');
          $bindparams34 = str_replace("\\", "", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

          $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];

          // Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'EXECUTION_BUDGETAIRE_DETAIL_ID="' . $data['id']['EXECUTION_BUDGETAIRE_DETAIL_ID'] . '"', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $bindparamsetapes=$this->getBindParms('DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_PAIEMENT','execution_budgetaire_etape_double_commande dc join execution_budgetaire_tache_detail det on  dc.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID','md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"','1 DESC');
          $bindparamsetapes = str_replace('\\', '', $bindparamsetapes);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparamsetapes);

          $detail = $this->detail_new($id);

          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['creditVote']=$detail['creditVote'];
          $data['montant_reserve']=$detail['montant_reserve'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_prise_en_chage_et_etablissment_view.php', $data);
        }
      }

      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  /** fonction pour enregistrer les info de l etablissements de titre */
  public function save_prise_en_charge_etablissement20240712()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }
    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $numero_decaissement = $this->request->getPost('numero_decaissement');
    $date_elaboration = $this->request->getPost('date_elaboration');
    $date_transmission = $this->request->getPost('date_transmission');
    $montant_decaissement = preg_replace('/\s/', '', $this->request->getPost('montant_decaissement'));
    $id = $this->request->getPost('id');
    $id_detail = $this->request->getPost('id_detail');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');

    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ID ASC');
    $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);

    $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID, '1 ASC');
    $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

    $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

    $rules = [
      'numero_decaissement' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_elaboration' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
    ];
    $this->validation->setRules($rules);

    if ($this->validation->withRequest($this->request)->run()) 
    {
      //modifier dans la table execution_budgetaire_raccrochage_activite_detail
      $table = 'execution_budgetaire_tache_detail';
      $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id_detail;
      $datatomodifie = 'NUMERO_TITRE_DECAISSEMNT="'.$numero_decaissement.'",DATE_ELABORATION_TD="'.$date_elaboration.'",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'"';
      $this->update_all_table($table, $datatomodifie, $conditions);

      $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_TRANSMISSION";
      $datacolumsinsert = "".$id_detail.",".$ETAPE_ID.",".$user_id.",'".$date_transmission."'";
      $this->save_histo($columsinsert, $datacolumsinsert);
      return redirect('double_commande_new/Liste_Paiement');
    } 
    else 
    {
      return $this->prise_en_charge_etablissement(md5($id));
    }
  }
  /* fin etablissement prise en charge*/

  /* debut prise en charge etablissement du titre*/
  public function prise_en_charge_etablissement($id = '')
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=20','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";
          $bindparamss = $this->getBindParms('exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.MONTANT_DECAISSEMENT,ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire exec JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"','exec.EXECUTION_BUDGETAIRE_ID DESC');
          $bindparams34 = str_replace("\\", "", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

          $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];

          // Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'EXECUTION_BUDGETAIRE_DETAIL_ID="' . $data['id']['EXECUTION_BUDGETAIRE_DETAIL_ID'] . '"', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $bindparamsetapes=$this->getBindParms('DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_PAIEMENT','execution_budgetaire_etape_double_commande dc join execution_budgetaire_tache_detail det on  dc.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID','md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"','1 DESC');
          $bindparamsetapes = str_replace('\\', '', $bindparamsetapes);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparamsetapes);

          $device  = 'SELECT DEVISE_TYPE_ID,DESC_DEVISE_TYPE FROM devise_type WHERE 1 ORDER BY DESC_DEVISE_TYPE ASC';
          $device = "CALL `getTable`('" . $device . "');";
          $data['get_device']= $this->ModelPs->getRequete($device);

          $detail = $this->detail_new($id);

          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['creditVote']=$detail['creditVote'];
          $data['montant_reserve']=$detail['montant_reserve'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_prise_en_chage_et_etablissment_view.php', $data);
        }
      }

      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }

  /** fonction pour enregistrer les info de l etablissements de titre */
  public function save_prise_en_charge_etablissement()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }
    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $numero_decaissement = $this->request->getPost('numero_decaissement');
    $date_elaboration = $this->request->getPost('date_elaboration');
    $date_transmission = $this->request->getPost('date_transmission');
    $montant_decaissement = preg_replace('/\s/', '', $this->request->getPost('montant_decaissement'));
    $id = $this->request->getPost('id');
    $id_detail = $this->request->getPost('id_detail');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $DEVISE_TYPE_ID_RETRAIT=$this->request->getPost('DEVISE_TYPE_ID_RETRAIT');
    $NOM_PERSONNE_RETRAT=$this->request->getPost('NOM_PERSONNE_RETRAT');

    $psgetrequete = "CALL getRequete(?,?,?,?);";
    $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ID ASC');
    $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);

    $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID, '1 ASC');
    $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

    $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

    $rules = [
      'numero_decaissement' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_elaboration' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'DEVISE_TYPE_ID_RETRAIT' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      // 'NOM_PERSONNE_RETRAT' => [
      //   'label' => '',
      //   'rules' => 'required',
      //   'errors' => [
      //     'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
      //   ]
      // ],
    ];
    $this->validation->setRules($rules);

    if ($this->validation->withRequest($this->request)->run()) 
    {
      //modifier dans la table execution_budgetaire_raccrochage_activite_detail
      $table = 'execution_budgetaire_tache_detail';
      $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id_detail;
      $datatomodifie = 'NUMERO_TITRE_DECAISSEMNT="'.$numero_decaissement.'",DATE_ELABORATION_TD="'.$date_elaboration.'",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'",NOM_PERSONNE_RETRAT="'.addslashes($NOM_PERSONNE_RETRAT).'",DEVISE_TYPE_ID_RETRAIT="'.$DEVISE_TYPE_ID_RETRAIT.'"';
      $this->update_all_table($table, $datatomodifie, $conditions);

      $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_TRANSMISSION";
      $datacolumsinsert = "".$id_detail.",".$ETAPE_ID.",".$user_id.",'".$date_transmission."'";
      $this->save_histo($columsinsert, $datacolumsinsert);
      return redirect('double_commande_new/Liste_Paiement');
    } 
    else 
    {
      return $this->prise_en_charge_etablissement(md5($id));
    }
  }
  /* fin etablissement prise en charge*/

  /* Debut signature du titre par dir comptable*/
  public function reception_et_signature_titre($id)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=23','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL getRequete(?,?,?,?);";          
          $bindparamss =$this->getBindParms('det.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,MONTANT_DECAISSEMENT,ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_tache_detail det','md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="' . $id .'"','EXECUTION_BUDGETAIRE_DETAIL_ID DESC');
          $bindparams34 = str_replace("\\", "", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);

          $EXECUTION_BUDGETAIRE_RACCROCHAGE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];
          
          //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          
          $bindparamsetap =$this->getBindParms('DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_ORDONNANCEMENT','execution_budgetaire_etape_double_commande dc join execution_budgetaire_tache_detail dt on dc.ETAPE_DOUBLE_COMMANDE_ID=dt.ETAPE_DOUBLE_COMMANDE_ID','md5(dt.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"','1 DESC');
          $bindparamsetapes = str_replace("\\", "", $bindparamsetap);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparamsetapes);

          $gettypevalidation = $this->getBindParms('ID_OPERATION,DESCRIPTION', 'budgetaire_type_operation_validation', 'ID_OPERATION NOT IN(1,3)', 'ID_OPERATION ASC');
          $data['type'] = $this->ModelPs->getRequete($callpsreq, $gettypevalidation);

          //Récuperer les motifs
          $bind_motif = $this->getBindParms('BUDGETAIRE_TYPE_ANALYSE_ID,DESC_BUDGETAIRE_TYPE_ANALYSE', 'budgetaire_type_analyse', 'MOUVEMENT_DEPENSE_ID=3', 'BUDGETAIRE_TYPE_ANALYSE_ID ASC');
          $data['motif'] = $this->ModelPs->getRequete($callpsreq, $bind_motif);

          $detail = $this->detail_new($id);
          $data['get_info']=$detail['get_info'];
          $data['montantvote']=$detail['montantvote'];
          $data['creditVote']=$detail['creditVote'];
          $data['montant_reserve']=$detail['montant_reserve'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_reception_et_signature_titre_view', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }    
  }

  public function save_reception_et_signature_titre()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $id = $this->request->getPost('id');
    $date_signature_titre = $this->request->getPost('date_signature_titre');
    $date_reception = $this->request->getPost('date_reception');
    $date_transmission = $this->request->getPost('date_transmission');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');
    $ID_OPERATION = $this->request->getPost('ID_OPERATION');
    // $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID');

    $rules = [
      'date_signature_titre' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'ID_OPERATION' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]
    ];


    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run()) {
      $psgetrequete = "CALL getRequete(?,?,?,?);";
      //si c'est visa
      if ($ID_OPERATION == 2) 
      {
        // $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ID ASC');
        // $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
        // // $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION=0', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        // $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'], 'ETAPE_DOUBLE_COMMANDE_ID ASC');
        // $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

        // $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $table = 'execution_budgetaire_tache_detail';
        $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id;
        $datatomodifie = 'DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR="'.$date_signature_titre . '",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'"';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        $data = ['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);

        return redirect('double_commande_new/Liste_Paiement');
      } 
      //si c'est retour a la correction
      elseif ($ID_OPERATION == 1) 
      {
        //--------- si c'est retour a la correction----------
        // $step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $ETAPE_ID, 'ETAPE_DOUBLE_COMMANDE_ID ASC');

        // $get_step = $this->ModelPs->getRequeteOne($psgetrequete, $step);
        
        // $MOUVEMENT_ACTU_ID =  $get_step['MOUVEMENT_DEPENSE_ID'];

        $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION=1', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID ASC');
        $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

        // $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID,MOUVEMENT_DEPENSE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'], 'ETAPE_DOUBLE_COMMANDE_ID ASC');
        // $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

        // $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
        $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

        $table = 'execution_budgetaire_tache_detail';
        $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID=' . $id;
        $datatomodifie = 'DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR="'.$date_signature_titre .'",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'"';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);

        //Enregistrement dans historique vérification des motifs
        foreach ($TYPE_ANALYSE_MOTIF_ID as $value) {
          $insertToTable_motif = '  execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
        $data = ['message' => "".lang('messages_lang.message_success').""];
        session()->setFlashdata('alert', $data);
        return redirect('double_commande_new/Liste_Paiement');
      }
    } 
    else 
    {
      return $this->reception_et_signature_titre(md5($id));
    }
  }
  /* Fin signature du titre par dir comptable*/

  /* debut signature par dg finance*/
  public function analyse_depense($id)
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }

    $prof_id = $session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_profil = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_PROFIL_ID,ETAPE_DOUBLE_COMMANDE_ID,PROFIL_ID','execution_budgetaire_etape_double_commande_profil','ETAPE_DOUBLE_COMMANDE_ID=24','PROFIL_ID DESC');
    $getProfil= $this->ModelPs->getRequete($callpsreq, $user_profil);

    if (!empty($getProfil))
    {
      foreach ($getProfil as $value)
      {
        if ($prof_id == $value->PROFIL_ID || $prof_id==1)
        {
          $data = $this->urichk();
          $callpsreq = "CALL `getRequete`(?,?,?,?);";
          
          $bindparamss=$this->getBindParms('EXECUTION_BUDGETAIRE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID','execution_budgetaire_tache_detail','md5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"','1 DESC');
          $bindparams34 = str_replace("\\", "", $bindparamss);
          $data['id'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparams34);
          $EXECUTION_BUDGETAIRE_ID = $data['id']['EXECUTION_BUDGETAIRE_ID'];

          //Le min de la date de réception
          $bind_date_histo = $this->getBindParms('DATE_TRANSMISSION', 'execution_budgetaire_tache_detail_histo', 'MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"', 'DATE_INSERTION DESC');
          $bind_date_histo = str_replace('\\', '', $bind_date_histo);
          $data['date_trans'] = $this->ModelPs->getRequeteOne($callpsreq, $bind_date_histo);

          $bindparamsetap = $this->getBindParms('DESC_ETAPE_DOUBLE_COMMANDE,MONTANT_ORDONNANCEMENT,MONTANT_DECAISSEMENT,MONTANT_PAIEMENT','execution_budgetaire_etape_double_commande dc join execution_budgetaire_tache_detail det on  dc.ETAPE_DOUBLE_COMMANDE_ID=det.ETAPE_DOUBLE_COMMANDE_ID','md5(det.EXECUTION_BUDGETAIRE_DETAIL_ID)="'.$id.'"','1 DESC');
          $bindparamsetapes = str_replace("\\", "", $bindparamsetap);
          $data['etapes'] = $this->ModelPs->getRequeteOne($callpsreq, $bindparamsetapes);

          //Requete pour les operation
          $get_oper = $this->getBindParms('ID_OPERATION,DESCRIPTION', ' budgetaire_type_operation_validation', 'ID_OPERATION NOT IN(1,3)', 'ID_OPERATION ASC');
          $get_oper = str_replace('\\', '', $get_oper);
          $data['operation'] = $this->ModelPs->getRequete($callpsreq, $get_oper);
    
          //Récuperation de l'étape précedent
          $bind_etap_prev = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID','  execution_budgetaire_tache_detail_histo',"MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)='".$id."'",'EXECUTION_BUDGETAIRE_DETAIL_HISTO_ID DESC');
          $bind_etap_prev = str_replace("\'", "'", $bind_etap_prev);
          $etap_prev = $this->ModelPs->getRequeteOne($callpsreq, $bind_etap_prev);

          $motif_rejet  = "SELECT DISTINCT verif.TYPE_ANALYSE_MOTIF_ID, DESC_TYPE_ANALYSE_MOTIF FROM budgetaire_type_analyse_motif analyse JOIN execution_budgetaire_histo_operation_verification_motif verif ON analyse.TYPE_ANALYSE_MOTIF_ID=verif.TYPE_ANALYSE_MOTIF_ID WHERE 1 AND MD5(EXECUTION_BUDGETAIRE_DETAIL_ID)='".$id."' AND verif.ETAPE_DOUBLE_COMMANDE_ID=".$etap_prev['ETAPE_DOUBLE_COMMANDE_ID']."";

          $motif_rejetRqt = 'CALL getTable("' . $motif_rejet . '");';
          $data['motif']= $this->ModelPs->getRequete($motif_rejetRqt);

          $detail = $this->detail_new($id);
          $data['get_info'] = $detail['get_info'];
          $data['montantvote'] = $detail['montantvote'];
          $data['creditVote'] = $detail['creditVote'];
          $data['montant_reserve'] = $detail['montant_reserve'];
          return view('App\Modules\double_commande_new\Views\Phase_comptable_analyse_depense_view', $data);
        }
      }
      return redirect('Login_Ptba/homepage');
    }
    else
    {
      return redirect('Login_Ptba/homepage');
    }
  }
  //--------------fx save--------------------------
  public function save_analyse_depense()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if (empty($user_id)) {
      return  redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PAIEMENT') !=1)
    {
      return redirect('Login_Ptba/homepage'); 
    }
    $id = $this->request->getPost('id');
    $date_signature_titre = $this->request->getPost('date_signature_titre');
    $date_reception = $this->request->getPost('date_reception');
    $date_transmission = $this->request->getPost('date_transmission');
    $paiement = $this->request->getPost('paiement');
    $ID_OPERATION = $this->request->getPost('ID_OPERATION');
    $TYPE_ANALYSE_MOTIF_ID = $this->request->getPost('TYPE_ANALYSE_MOTIF_ID[]');
    $ETAPE_ID = $this->request->getPost('ETAPE_ID');

    $psgetrequete = "CALL getRequete(?,?,?,?);";

    // requette pour recuperer l'etape suivante et mouvement_id  WHERE VISA DONC IS_CORRECTION =0;
    $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 0', '1 ASC');
    $get_next_step = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

    $next_move = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ID', 'execution_budgetaire_etape_double_commande', 'ETAPE_DOUBLE_COMMANDE_ID=' . $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'], 'ETAPE_DOUBLE_COMMANDE_ID ASC');
    $get_next_move = $this->ModelPs->getRequeteOne($psgetrequete, $next_move);

    // $MOUVEMENT_NEXT_ID =  $get_next_move['MOUVEMENT_DEPENSE_ID'];
    $NEXT_ETAPE_ID = $get_next_step['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];
    $EST_SUPERIEUR_CENT_MILLION = $get_next_step['EST_SUPERIEUR_CENT_MILLION'];

    $rules = [
      'date_signature_titre' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],
      'date_reception' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ],

      'date_transmission' => [
        'label' => '',
        'rules' => 'required',
        'errors' => [
          'required' => '<font style="color:red;size:2px;">Le champ est obligatoire</font>'
        ]
      ]

    ];

    $this->validation->setRules($rules);
    if ($this->validation->withRequest($this->request)->run()) {
      if ($ID_OPERATION == 2) {
        if ($paiement > 100000000)
        {
          $next_step = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config', 'EST_SUPERIEUR_CENT_MILLION=1 and ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID, '1 ASC');
          $get_next_stepsup = $this->ModelPs->getRequeteOne($psgetrequete, $next_step);

          $sup_million = $get_next_stepsup['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          $table = 'execution_budgetaire_tache_detail';
          $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id;
          $datatomodifie = 'DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR="'.$date_signature_titre.'",ETAPE_DOUBLE_COMMANDE_ID="'.$sup_million.'"';
          $this->update_all_table($table, $datatomodifie, $conditions);

          $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $datacolumsinsert = $id.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
          $this->save_histo($columsinsert, $datacolumsinsert);
        }
        else
        {
          $next_step_inf = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config', 'EST_SUPERIEUR_CENT_MILLION=0 and ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID, '1 ASC');
          $get_next_stepinf = $this->ModelPs->getRequeteOne($psgetrequete, $next_step_inf);

          $inf_million = $get_next_stepinf['ETAPE_DOUBLE_COMMANDE_SUIVANT_ID'];

          $table = 'execution_budgetaire_tache_detail';
          $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id;
          $datatomodifie = 'DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR="'.$date_signature_titre.'",ETAPE_DOUBLE_COMMANDE_ID="'.$inf_million.'"';
          $this->update_all_table($table, $datatomodifie, $conditions);

          $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
          $datacolumsinsert = $id.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
          $this->save_histo($columsinsert, $datacolumsinsert);
        }
      } elseif ($ID_OPERATION == 1) 
      {
        //---------------RETOUR A LA CORRECTION--------------------
        // requette pour recuperer l'etape suivante et mouvement_id  WHERE RETOUR A LA CORRECTION DONC IS_CORRECTION =1;
        $next_step_retour = $this->getBindParms('ETAPE_DOUBLE_COMMANDE_ACTUEL_ID,ETAPE_DOUBLE_COMMANDE_SUIVANT_ID,EST_SUPERIEUR_CENT_MILLION', 'execution_budgetaire_etape_double_commande_config', 'ETAPE_DOUBLE_COMMANDE_ACTUEL_ID=' . $ETAPE_ID . ' AND IS_CORRECTION = 1', '1 ASC');
        $get_next_step_retour = $this->ModelPs->getRequeteOne($psgetrequete, $next_step_retour);

        $NEXT_ETAPE_ID_retour = $get_next_move_retour['ETAPE_DOUBLE_COMMANDE_ID'];

        foreach ($TYPE_ANALYSE_MOTIF_ID as $value) 
        {
          $insertToTable_motif = '  execution_budgetaire_histo_operation_verification_motif';
          $columninserthist_motif = "TYPE_ANALYSE_MOTIF_ID,ETAPE_DOUBLE_COMMANDE_ID,EXECUTION_BUDGETAIRE_DETAIL_ID";
          $datatoinsert_histo_motif = "" . $value . "," . $ETAPE_ID . "," . $id . "";
          $this->save_all_table($insertToTable_motif, $columninserthist_motif, $datatoinsert_histo_motif);
        }
        $table = 'execution_budgetaire_tache_detail';
        $conditions = 'EXECUTION_BUDGETAIRE_DETAIL_ID='.$id;
        $datatomodifie = 'DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR="'.$date_signature_titre.'",ETAPE_DOUBLE_COMMANDE_ID="'.$NEXT_ETAPE_ID.'"';
        $this->update_all_table($table, $datatomodifie, $conditions);

        $columsinsert = "EXECUTION_BUDGETAIRE_DETAIL_ID,ETAPE_DOUBLE_COMMANDE_ID,USER_ID,DATE_RECEPTION,DATE_TRANSMISSION";
        $datacolumsinsert = $id.",".$ETAPE_ID.",".$user_id.",'".$date_reception."','".$date_transmission."'";
        $this->save_histo($columsinsert, $datacolumsinsert);
      }
      return redirect('double_commande_new/Liste_Paiement');
    } 
    else 
    {
      return $this->analyse_depense(md5($id));
    }
  }
  /* fin signature par dg finance*/

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
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    return $bindparams;
  }

  public function save_info_sup($columsinsert, $datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $table = 'execution_budgetaire_raccrochage_activite_info_suppl';
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $this->ModelPs->createUpdateDelete($insertReqAgence, $bindparms);
  }

  public function save_all_table($table, $columsinsert, $datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReq = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $tableparams = [$table, $columsinsert, $datacolumsinsert];
    $result = $this->ModelPs->getRequeteOne($insertReq, $tableparams);
    return $id = $result['id'];
  }

  public function save_histo($columsinsert, $datacolumsinsert)
  {
    // $columsinsert: Nom des colonnes separe par,
    // $datacolumsinsert : les donnees a inserer dans les colonnes
    $table = ' execution_budgetaire_tache_detail_histo';
    $bindparms = [$table, $columsinsert, $datacolumsinsert];
    $insertReqAgence = "CALL `insertLastIdIntoTableColonnes`(?,?,?);";
    $this->ModelPs->createUpdateDelete($insertReqAgence, $bindparms);
  }

  public function update_all_table($table, $datatomodifie, $conditions)
  {
    $bindparams = [$table, $datatomodifie, $conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat = $this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  public function uploadFile($fieldName, $folder, $prefix = ''): string
  {
    $prefix = ($prefix === '') ? uniqid() : $prefix;
    $path = '';

    $file = $this->request->getFile($fieldName);

    if ($file->isValid() && !$file->hasMoved()) {
      $newName = uniqid() . '' . date('ymdhis') . '.' . $file->getExtension();
      $file->move(ROOTPATH . 'public/uploads/' . $folder, $newName);
      $path = 'double_commande/' . $folder . '/' . $newName;
    }
    return $newName;
  }  

  private function infosup_data ($id_raccrochage_detail) 
  {
    $table="execution_budgetaire_tache_info_suppl suppl JOIN execution_budgetaire exec ON suppl.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN type_beneficiaire ON type_beneficiaire.TYPE_BENEFICIAIRE_ID=suppl.TYPE_BENEFICIAIRE_ID";
    $columnselect="suppl.TYPE_BENEFICIAIRE_ID,type_beneficiaire.DESC_TYPE_BENEFICIAIRE,exec.DEVISE_TYPE_ID,det.MONTANT_ORDONNANCEMENT";
    $where="det.EXECUTION_BUDGETAIRE_DETAIL_ID='".$id_raccrochage_detail."'";
    $orderby = 'det.EXECUTION_BUDGETAIRE_ID DESC';
    $where = str_replace("\'", "'", $where);
    $db = db_connect();
    $bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby)];
    $bindparams = str_replace("\'", "'", $bindparams);
    return $this->ModelPs->getRequeteOne("CALL `getRequete`(?,?,?,?);", $bindparams);
  }
}