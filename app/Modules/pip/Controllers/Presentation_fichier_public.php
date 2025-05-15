<?php

/**
 * Auteur : Baleke Kahamire Bonheur 
 * Titre  : Presentation du fichier investisement public
 * numero : (+257) 67 86 62 83
 * email  : bonheur.baleke@mediabox.bi 
 * date   : 10.12.2023
 */
namespace App\Modules\pip\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Presentation_fichier_public extends BaseController
{
  protected $session;
  protected $ModelPs;
  protected $validation;
  protected $library;

  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->session = \Config\Services::session();
    $this->validation = \Config\Services::validation();
  }

  /* afficher la page principale */
  public function liste_projet()
  {
    $session  = \Config\Services::session();
    
    $data = $this->urichk();
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data["titre"] = "Présentation du Fichier Investisement Public";
    return view('App\Modules\pip\Views\Presentation_fichier_public_view', $data);
  }

  /** 
   * affiche la vue pour la liste 
   */
  function liste_view()
  {
    $session  = \Config\Services::session();
    
    $data = $this->urichk();
    if (empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $data["titre"] = "Présentation du Fichier Investisement Public";
    return view('App\Modules\pip\Views\Presentation_fichier_public_view', $data);
  }

  public function get_annee_pip_en_cour( $annees )
  {
    $session  = \Config\Services::session();
    if (empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }
    $YEAR= $annees ? $annees : date('Y');
    $CONDICTION="ANNEE_DEBUT='".$YEAR."'";
    $callpsreq = "CALL getRequeteLimit(?,?,?,?,?);";
    $bind=$this->getBindParmsLimit('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN','annee_budgetaire','ANNEE_DEBUT>='.$YEAR,'ANNEE_DEBUT ASC','3');
    $annees=$this->ModelPs->getRequete($callpsreq, $bind);
    return $annees;
  }

  /* la vue pour afficher les detail des la liste selon le projet */
  public function projet_detail(string $id)
  {
    $session  = \Config\Services::session();
    
    $data = $this->urichk();
    if (empty($session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
    {
      return redirect('Login_Ptba/do_logout');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $id_projet_infos_sup = null;
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $table="pip_demande_infos_supp";
    $columnselect="pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP";
    $where="md5(pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP)='". $id ."'";
    $orderby='ID_DEMANDE_INFO_SUPP DESC';
    $where=str_replace("\'", "'", $where);
    $db = db_connect();
    $bindparamss =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
    $bindparams34=str_replace("\'", "'", $bindparamss);
    $data['id']= $this->ModelPs->getRequeteOne($callpsreq,$bindparams34);
    $id_projet_infos_sup = (int)$data['id']["ID_DEMANDE_INFO_SUPP"];

    $get_annee_en_cour = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT annee_budgetaire.ANNEE_DEBUT FROM pip_valeur_nomenclature_livrable JOIN pip_cadre_mesure_resultat_livrable ON pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE = pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_livrable ON pip_cadre_mesure_resultat_livrable.ID_LIVRABLE = pip_demande_livrable.ID_DEMANDE_LIVRABLE JOIN pip_nomenclature_budgetaire ON pip_valeur_nomenclature_livrable.ID_NOMENCLATURE = pip_nomenclature_budgetaire.ID_NOMENCLATURE JOIN annee_budgetaire ON pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID = annee_budgetaire.ANNEE_BUDGETAIRE_ID WHERE 1 AND pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP = ". $id_projet_infos_sup ." ORDER BY annee_budgetaire.ANNEE_DEBUT ASC')");
    $an = $get_annee_en_cour ? $get_annee_en_cour['ANNEE_DEBUT'] : '' ;
    $premie_annee_en_cour = $this->get_annee_pip_en_cour($an)[0]->ANNEE_DEBUT;
    
    $data['fichier'] = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP, pip_demande_infos_supp.DUREE_PROJET, pip_demande_infos_supp.NUMERO_PROJET, pip_demande_infos_supp.OBJECTIF_GENERAL,pip_demande_infos_supp.NOM_PROJET,pip_demande_infos_supp.DATE_DEBUT_PROJET,pip_demande_infos_supp.EST_REALISE_NATIONAL, pip_demande_infos_supp.DATE_FIN_PROJET, pip_demande_infos_supp.PATH_CONTEXTE_JUSTIFICATION,pip_demande_infos_supp.BENEFICIAIRE_PROJET, pip_demande_infos_supp.IMPACT_ATTENDU_ENVIRONNEMENT,pip_demande_infos_supp.IMPACT_ATTENDU_GENRE, pip_demande_infos_supp.TAUX_CHANGE_EURO, pip_demande_infos_supp.TAUX_CHANGE_USD, pip_demande_infos_supp.OBSERVATION_COMPLEMENTAIRE,pip_demande_infos_supp.DATE_PREPARATION_FICHE_PROJET,inst_institutions.DESCRIPTION_INSTITUTION,pilier.DESCR_PILIER,pilier.NUMERO_PILIER,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,objectif_strategique.NUMERO_OBJECT_STRATEGIC,objectif_strategique.DESCR_OBJECTIF_STRATEGIC,objectif_strategique.NUMERO_OBJECT_STRATEGIC,objectif_strategique_pnd.DESCR_OBJECTIF_STRATEGIC_PND,objectif_strategique_pnd.NUMERO_OBJCTIF_STRATEGIC_PND,axe_intervention_pnd.DESCR_AXE_INTERVATION_PND,axe_intervention_pnd.NUM_AXE_INTERVENTION_PND,inst_institutions_programmes.INTITULE_PROGRAMME, inst_institutions_programmes.CODE_PROGRAMME,inst_institutions_actions.LIBELLE_ACTION,inst_institutions_actions.CODE_ACTION,pip_statut_projet.DESCR_STATUT_PROJET,programme_pnd.DESCR_PROGRAMME FROM pip_demande_infos_supp JOIN programme_pnd ON pip_demande_infos_supp.ID_PROGRAMME_PND = programme_pnd.ID_PROGRAMME_PND JOIN inst_institutions ON pip_demande_infos_supp.INSTITUTION_ID = inst_institutions.INSTITUTION_ID JOIN pilier ON pip_demande_infos_supp.ID_PILIER = pilier.ID_PILIER JOIN objectif_strategique ON pip_demande_infos_supp.ID_OBJECT_STRATEGIQUE = objectif_strategique.ID_OBJECT_STRATEGIQUE JOIN objectif_strategique_pnd ON pip_demande_infos_supp.ID_OBJECT_STRATEGIC_PND = objectif_strategique_pnd.ID_OBJECT_STRATEGIC_PND JOIN axe_intervention_pnd ON pip_demande_infos_supp.ID_AXE_INTERVENTION_PND = axe_intervention_pnd.ID_AXE_INTERVENTION_PND JOIN inst_institutions_programmes ON pip_demande_infos_supp.ID_PROGRAMME = inst_institutions_programmes.PROGRAMME_ID JOIN inst_institutions_actions ON pip_demande_infos_supp.ID_ACTION = inst_institutions_actions.ACTION_ID JOIN pip_statut_projet ON pip_demande_infos_supp.ID_STATUT_PROJET = pip_statut_projet.ID_STATUT_PROJET WHERE 1 AND pip_demande_infos_supp.ID_DEMANDE_INFO_SUPP = {$id_projet_infos_sup}')");

    $data['province_commune_lie'] = [];
    $data['province_province_lie'] = [];

    /* verifie si le projet et au niveau national pour afficher les provinces ainsi que les commune lier au niveau de realisation */
      
      if (isset($data["fichier"])) 
      {
        if($data["fichier"]["EST_REALISE_NATIONAL"] == 0) 
        {
          $data['province_province_lie'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT DISTINCT ID_PROVINCE,ID_DEMANDE_INFO_SUPP, PROVINCE_NAME FROM `pip_lieu_intervention_projet` LEFT JOIN provinces ON pip_lieu_intervention_projet.ID_PROVINCE = provinces.PROVINCE_ID WHERE 1 AND `ID_DEMANDE_INFO_SUPP` = " .$id_projet_infos_sup." GROUP BY ID_PROVINCE')");
        }
      }
      
      foreach($data['province_province_lie'] as $province)
      {
        $data['province_commune_lie'][$province->ID_PROVINCE] = $this->ModelPs->getRequete("CALL `getTable`('SELECT DISTINCT COMMUNE_NAME, ID_DEMANDE_INFO_SUPP FROM `pip_lieu_intervention_projet` LEFT JOIN communes ON pip_lieu_intervention_projet.ID_COMMUNE = communes.COMMUNE_ID WHERE 1 AND `ID_DEMANDE_INFO_SUPP` = " .$id_projet_infos_sup." AND `ID_PROVINCE` = ".$province->ID_PROVINCE."')");
      }
      
    /* les donne du document de reference associer au projet */
    $data["document_references"] = $this->ModelPs->getRequete("CALL `getTable`('SELECT TITRE_ETUDE,DOC_REFERENCE,DATE_REFERENCE,AUTEUR_ORGANISME,OBSERVATION FROM `pip_etude_document_reference`  WHERE 1 AND `ID_DEMANDE_INFO_SUPP` = ". $id_projet_infos_sup. "')");

    /* recuper les information de pip demande livrable */
    $data["livrables"] = $this->ModelPs->getRequete("CALL `getTable`('SELECT ID_DEMANDE_LIVRABLE, DESCR_LIVRABLE, COUT_LIVRABLE, OBJECTIF_SPECIFIQUE FROM `pip_demande_livrable` WHERE 1 AND `ID_DEMANDE_INFO_SUPP` = {$id_projet_infos_sup}')");

    /* Recuperer les nomenclatures de pip nomenclature budgetaire */
    $data['nomenclatures'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT * FROM pip_nomenclature_budgetaire')");
    
    /* recuper les information de pip risque */
    $data["risques"] = $this->ModelPs->getRequete("CALL `getTable`('SELECT NOM_RISQUE, MESURE_RISQUE FROM `pip_risques_projet`  WHERE 1 AND `ID_DEMANDE_INFO_SUPP` = {$id_projet_infos_sup}')");

    $count_pip_livrable_table = 0;
    $data['all_annee_budgetaires'] = $this->get_annee_pip_en_cour($an);
    
    $pip_livrables_tables = [];
    foreach ($data["livrables"] as $pip_demande_livrable_projecte) 
    {
      $pip_livrables_tables[$pip_demande_livrable_projecte->DESCR_LIVRABLE] = $this->ModelPs->getRequete("CALL `getTable` ('SELECT pip_demande_livrable.DESCR_LIVRABLE,pip_indicateur_mesure.INDICATEUR_MESURE,unite_mesure.UNITE_MESURE,pip_cadre_mesure_resultat_livrable.TOTAL_TRIENNAL,pip_cadre_mesure_resultat_livrable.TOTAL_DURE_PROJET,cadre_mesure_resultat_valeur_cible.VALEUR_ANNEE_CIBLE,annee_budgetaire.ANNEE_DEBUT,annee_budgetaire.ANNEE_FIN FROM pip_cadre_mesure_resultat_livrable LEFT JOIN pip_demande_livrable ON pip_cadre_mesure_resultat_livrable.ID_LIVRABLE = pip_demande_livrable.ID_DEMANDE_LIVRABLE LEFT JOIN pip_indicateur_mesure ON pip_cadre_mesure_resultat_livrable.ID_INDICATEUR_MESURE = pip_indicateur_mesure.ID_INDICATEUR_MESURE LEFT JOIN unite_mesure ON pip_cadre_mesure_resultat_livrable.ID_UNITE_MESURE = unite_mesure.ID_UNITE_MESURE LEFT JOIN cadre_mesure_resultat_valeur_cible ON cadre_mesure_resultat_valeur_cible.ID_CADRE_MESURE_RESULTAT_LIVRABLE = pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE LEFT JOIN annee_budgetaire ON annee_budgetaire.ANNEE_BUDGETAIRE_ID = cadre_mesure_resultat_valeur_cible.ANNEE_BUDGETAIRE_ID WHERE pip_cadre_mesure_resultat_livrable.ID_LIVRABLE = " . $pip_demande_livrable_projecte->ID_DEMANDE_LIVRABLE . "')");

      $count_pip_livrable_table += count($pip_livrables_tables[$pip_demande_livrable_projecte->DESCR_LIVRABLE]);
    }

    $tab_projet_par_livrable = [];  
    
    foreach ($data["livrables"] as $pip_demande_livrable_projecte) 
    {
      $tab_projet_par_livrable[$pip_demande_livrable_projecte->ID_DEMANDE_LIVRABLE] = $this->ModelPs->getRequete("CALL `getTable`('SELECT annee_budgetaire.ANNEE_BUDGETAIRE_ID,pip_demande_livrable.ID_DEMANDE_LIVRABLE,pip_demande_livrable.DESCR_LIVRABLE,pip_nomenclature_budgetaire.DESCR_NOMENCLATURE,pip_nomenclature_budgetaire.CODE_NOMENCLATURE,annee_budgetaire.ANNEE_DESCRIPTION,pip_valeur_nomenclature_livrable.MONTANT_NOMENCALTURE FROM pip_valeur_nomenclature_livrable JOIN pip_cadre_mesure_resultat_livrable ON pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE = pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_livrable ON pip_cadre_mesure_resultat_livrable.ID_LIVRABLE = pip_demande_livrable.ID_DEMANDE_LIVRABLE JOIN pip_nomenclature_budgetaire ON pip_valeur_nomenclature_livrable.ID_NOMENCLATURE = pip_nomenclature_budgetaire.ID_NOMENCLATURE JOIN annee_budgetaire ON pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID = annee_budgetaire.ANNEE_BUDGETAIRE_ID WHERE 1 AND pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP=" . $id_projet_infos_sup . " AND pip_cadre_mesure_resultat_livrable.ID_LIVRABLE = ".$pip_demande_livrable_projecte->ID_DEMANDE_LIVRABLE."')");
    }
    
    /** Budjet de projet par livrable */
    $data["nom_du_livrables"] = $tab_projet_par_livrable;

    // Calculer le budget total par annee budgetaire
    foreach($data['livrables'] as $livrable)
    {
      $total_budget[$livrable->ID_DEMANDE_LIVRABLE][$data['all_annee_budgetaires'][0]->ANNEE_BUDGETAIRE_ID] = 0;
      $total_budget[$livrable->ID_DEMANDE_LIVRABLE][$data['all_annee_budgetaires'][1]->ANNEE_BUDGETAIRE_ID] = 0;
      $total_budget[$livrable->ID_DEMANDE_LIVRABLE][$data['all_annee_budgetaires'][2]->ANNEE_BUDGETAIRE_ID] = 0;
    }

    $total_projet[$data['all_annee_budgetaires'][0]->ANNEE_BUDGETAIRE_ID] = 0;
    $total_projet[$data['all_annee_budgetaires'][1]->ANNEE_BUDGETAIRE_ID] = 0;
    $total_projet[$data['all_annee_budgetaires'][2]->ANNEE_BUDGETAIRE_ID] = 0;
    
    foreach($tab_projet_par_livrable as $livrable)
    {
      foreach($livrable as $item)
      {
        if(isset($total_budget[$item->ID_DEMANDE_LIVRABLE][$item->ANNEE_BUDGETAIRE_ID],$total_projet[$item->ANNEE_BUDGETAIRE_ID]))
        {
          $total_budget[$item->ID_DEMANDE_LIVRABLE][$item->ANNEE_BUDGETAIRE_ID] += $item->MONTANT_NOMENCALTURE;
          $total_projet[$item->ANNEE_BUDGETAIRE_ID] += $item->MONTANT_NOMENCALTURE;
        }
      }
    }
    
    $data['total_projet'] = $total_projet;
    $data['total_budget'] = $total_budget;

    // Calculer total livrable 
    $data['total_livrable'] = 0;

    $montants_total = [];
    
    foreach($data['nomenclatures'] as $nomen)
    {
      $montants_total[$data['all_annee_budgetaires'][0]->ANNEE_BUDGETAIRE_ID][$nomen->DESCR_NOMENCLATURE] = 0;
      $montants_total[$data['all_annee_budgetaires'][1]->ANNEE_BUDGETAIRE_ID][$nomen->DESCR_NOMENCLATURE] = 0;
      $montants_total[$data['all_annee_budgetaires'][2]->ANNEE_BUDGETAIRE_ID][$nomen->DESCR_NOMENCLATURE] = 0;
    }

    foreach($data["livrables"] as $livrable)
    {
      foreach($tab_projet_par_livrable[$livrable->ID_DEMANDE_LIVRABLE] as $value)
      {
        if($value->ANNEE_BUDGETAIRE_ID == $data['all_annee_budgetaires'][0]->ANNEE_BUDGETAIRE_ID)
        {
          $montants_total[$data['all_annee_budgetaires'][0]->ANNEE_BUDGETAIRE_ID][$value->DESCR_NOMENCLATURE] += $value->MONTANT_NOMENCALTURE;
        }
        if($value->ANNEE_BUDGETAIRE_ID == $data['all_annee_budgetaires'][1]->ANNEE_BUDGETAIRE_ID)
        {
          $montants_total[$data['all_annee_budgetaires'][1]->ANNEE_BUDGETAIRE_ID][$value->DESCR_NOMENCLATURE] += $value->MONTANT_NOMENCALTURE;
        }
        if($value->ANNEE_BUDGETAIRE_ID == $data['all_annee_budgetaires'][2]->ANNEE_BUDGETAIRE_ID)
        {
          $montants_total[$data['all_annee_budgetaires'][2]->ANNEE_BUDGETAIRE_ID][$value->DESCR_NOMENCLATURE] += $value->MONTANT_NOMENCALTURE;
        }
      }
    }
    
    $data['montants_total'] = $montants_total;
    foreach($data['livrables'] as $livrable)
    {
      $data['total_livrable'] += $livrable->COUT_LIVRABLE;
    }
    
    // Recuperer le taux d'échange

    $data['taux'] = $this->ModelPs->getRequete("CALL `getTable`('SELECT pip_taux_echange.DEVISE,pip_taux_echange.TAUX FROM pip_taux_echange WHERE pip_taux_echange.DEVISE LIKE \'%Dollar USA%\' OR pip_taux_echange.DEVISE LIKE \'%Euro%\'')");

    /** les donnes de user user */
    $data["user_connecter"] = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT NOM,EMAIL,PRENOM, TELEPHONE1,TELEPHONE2 FROM `user_users`  WHERE 1 AND `USER_ID` = ". $this->session->get('SESSION_SUIVIE_PTBA_USER_ID') ."')");

    $data["total_pour_tous_nomenclature"] = [];
    foreach ($data['all_annee_budgetaires'] as $data_all_annee_budgetaire) 
    {
      $data["total_pour_tous_nomenclature"][] = $this->ModelPs->getRequeteOne("CALL `getTable`('SELECT SUM(pip_valeur_nomenclature_livrable.MONTANT_NOMENCALTURE) as some FROM pip_valeur_nomenclature_livrable JOIN pip_cadre_mesure_resultat_livrable ON pip_valeur_nomenclature_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE = pip_cadre_mesure_resultat_livrable.ID_CADRE_MESURE_RESULTAT_LIVRABLE JOIN pip_demande_livrable ON pip_cadre_mesure_resultat_livrable.ID_LIVRABLE = pip_demande_livrable.ID_DEMANDE_LIVRABLE JOIN pip_nomenclature_budgetaire ON pip_valeur_nomenclature_livrable.ID_NOMENCLATURE = pip_nomenclature_budgetaire.ID_NOMENCLATURE JOIN annee_budgetaire ON pip_valeur_nomenclature_livrable.ANNEE_BUDGETAIRE_ID = annee_budgetaire.ANNEE_BUDGETAIRE_ID WHERE annee_budgetaire.ANNEE_BUDGETAIRE_ID = ". $data_all_annee_budgetaire->ANNEE_BUDGETAIRE_ID ." AND pip_cadre_mesure_resultat_livrable.ID_DEMANDE_INFO_SUPP = ". $id_projet_infos_sup ."')");
    }

    $data["demande_source_financements"] = $this->ModelPs->getRequete("CALL `getTable`('SELECT pip_demande_source_financement.ID_DEMANDE_SOURCE_FINANCEMENT,pip_demande_source_financement.TOTAL_DUREE_PROJET,pip_demande_source_financement.TOTAL_TRIENNAL,pip_source_financement_bailleur.NOM_SOURCE_FINANCE,pip_source_financement_bailleur.CODE_BAILLEUR FROM pip_demande_source_financement JOIN pip_source_financement_bailleur ON pip_demande_source_financement.ID_SOURCE_FINANCE_BAILLEUR = pip_source_financement_bailleur.ID_SOURCE_FINANCE_BAILLEUR WHERE pip_demande_source_financement.ID_DEMANDE_INFO_SUPP = " . $id_projet_infos_sup . "')");

    $valeur = []; 
    $total_financement = [];
    foreach($data["demande_source_financements"] as $source)
    {
      $valeur[$source->ID_DEMANDE_SOURCE_FINANCEMENT] = $this->ModelPs->getRequete("CALL `getTable`('SELECT pip_demande_source_financement_valeur_cible.ANNEE_BUDGETAIRE_ID,pip_demande_source_financement_valeur_cible.SOURCE_FINANCEMENT_VALEUR_CIBLE FROM pip_demande_source_financement_valeur_cible WHERE pip_demande_source_financement_valeur_cible.ID_DEMANDE_SOURCE_FINANCEMENT=\'{$source->ID_DEMANDE_SOURCE_FINANCEMENT}\' ')");
      $total_financement[$source->ID_DEMANDE_SOURCE_FINANCEMENT] = 0; 
    }

    $data['source_financement_valeur'] = $valeur;

    $total_financement_projet = [];
    foreach($data['all_annee_budgetaires'] as $key => $value)
    {
      $total_financement_projet[$data['all_annee_budgetaires'][$key]->ANNEE_BUDGETAIRE_ID] = 0;
    }

    foreach($data['source_financement_valeur'] as $key => $value)
    {
      foreach($value as $item)
      {
        if(isset($total_financement[$key],$total_financement_projet[$item->ANNEE_BUDGETAIRE_ID]))
        {
          $total_financement[$key] += $item->SOURCE_FINANCEMENT_VALEUR_CIBLE; 
          $total_financement_projet[$item->ANNEE_BUDGETAIRE_ID] += $item->SOURCE_FINANCEMENT_VALEUR_CIBLE;
        }
      }
    }
    
    $data['total_financement'] = $total_financement;

    $data['total_financement_projet'] = $total_financement_projet;
    $data['total_total'] = 0;
    foreach($total_financement_projet as $value)
    {
      $data['total_total'] += $value;
    }

    $data["pip_nomenclature_budgetaire"] = $this->ModelPs->getRequete("CALL `getTable`('SELECT DESCR_NOMENCLATURE FROM `pip_nomenclature_budgetaire`  WHERE 1')");

    $data["pip_livrables_tables"] = $pip_livrables_tables;
    $data["count_pip_livrable_table"] = $count_pip_livrable_table;
    $data["id"] = $id_projet_infos_sup;
    $data["titre"] = "Presentation du Fichier Insetisemant Public";
    return view('App\Modules\pip\Views\Presentation_fichier_public_detail_view', $data);
  }

  /* nous donne la reponse en Json de tous le projet qu'on a */
  function liste_projet_detail()
  {
    $session  = \Config\Services::session();
    
    if (empty($this->session->get('SESSION_SUIVIE_PTBA_USER_ID'))) 
    {
      return redirect('Login_Ptba');
    }

    if($session->get('SESSION_SUIVIE_PTBA_PIP_EXECUTION')!=1)
    {
      return redirect('Login_Ptba/homepage');
    }

    $query_principal = 'SELECT ID_DEMANDE_INFO_SUPP, NOM_PROJET FROM pip_demande_infos_supp WHERE 1 AND IS_FINISHED=1';
    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $limit = "LIMIT 0,10";
    if ($_POST['length'] != -1) {
      $limit = "LIMIT " . $_POST['start'] . "," . $_POST['length'];
    }

    $order_by = "";
    $order_column = "";
    $order_column = array(1, 'NOM_PROJET', 1);

    $order_by = isset($_POST['order']) ? " ORDER BY " . $order_column[$_POST['order']['0']['column']] . "  " . $_POST['order']['0']['dir'] : " ORDER BY ID_DEMANDE_INFO_SUPP ASC";
    $search = !empty($_POST['search']['value']) ?  (' AND ( NOM_PROJET LIKE "%' . $var_search . '%")') : "";
    $search = str_replace("'", "\'", $search);
    $critaire = " ";
    $query_secondaire = $query_principal . " " . $search . " " . $critaire . " " . $order_by . " " . $limit;
    $query_filter = $query_principal . " " . $search . " " . $critaire;
    $requete = "CALL `getTable`('" . $query_secondaire . "')";
    $fetch_cov_frais = $this->ModelPs->datatable($requete);
    $data = array();
    $u = 1;
    foreach ($fetch_cov_frais as $info) 
    {
      $post = array();
      $post[] = $u++;
      $post[] = $info->NOM_PROJET;
      $action = '<div class="dropdown" style="color:#fff;"><a class="btn btn-primary btn-md dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> '.lang('messages_lang.dropdown_link_options').'  <span class="caret"></span></a><ul class="dropdown-menu dropdown-menu-left">';

      $action .= "<li><a href='" . base_url("pip/presentation_fichier_investisement_public/projet_detail/" . md5($info->ID_DEMANDE_INFO_SUPP)) . "'><label>&nbsp;&nbsp;".lang('messages_lang.detail_title')."</label></a></li></ul>";
      $post[] = $action;
      $data[] = $post;
    }

    $requeteqp = "CALL `getTable`('" . $query_principal . "')";
    $recordsTotal = $this->ModelPs->datatable($requeteqp);
    $requeteqf = "CALL `getTable`('" . $query_filter . "')";
    $recordsFiltered = $this->ModelPs->datatable($requeteqf);
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data
    );
    echo json_encode($output);
  }
  
	public function getBindParmsLimit($columnselect, $table, $where, $orderby, $Limit)
	{
		$db = db_connect();
		$columnselect = str_replace("\'", "'", $columnselect);
		$table = str_replace("\'", "'", $table);
		$where = str_replace("\'", "'", $where);
		$orderby = str_replace("\'", "'", $orderby);
		$Limit = str_replace("\'", "'", $Limit);
		$bindparams = [$db->escapeString($columnselect), $db->escapeString($table), $db->escapeString($where), $db->escapeString($orderby), $db->escapeString($Limit)];
		$bindparams = str_replace('\"', '"', $bindparams);
		return $bindparams;
	}
}
?>