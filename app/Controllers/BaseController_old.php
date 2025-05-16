<?php

namespace App\Controllers;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
abstract class BaseController extends Controller
{
  protected $request;
  protected $helpers = [];

  /** Constructor.*/
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
  {
    parent::initController($request, $response, $logger);
    $session = \Config\Services::session();
    $language = \Config\Services::language();
    $language->setLocale($session->lang);        
  }

  //recuperation des segments
  public function urichk()
  {
    $data['menu']= $this->request->uri->getSegment(1); 
    $data['sousmenu']= $this->request->uri->getSegment(2);
    $data['sousmenu2']= $this->request->uri->getSegment(3);  
    return $data;
  }

  public function count_croisement()
  {
    $nbre_taches="SELECT COUNT(`PTBA_TACHE_ID`) AS NBRE FROM ptba_tache WHERE 1";
    $nbre_taches = 'CALL getTable("'.$nbre_taches.'");';
    $nbre_taches = $this->ModelPs->getRequeteOne($nbre_taches);
    $data['nbre_tache'] = number_format($nbre_taches['NBRE'],'0',',',' ');
    $nbre_taches_revise="SELECT COUNT(`PTBA_TACHE_REVISE_ID`) AS NBRE FROM ptba_tache_revise WHERE 1";
    $nbre_taches_revise = 'CALL getTable("'.$nbre_taches_revise.'");';
    $nbre_taches_revise = $this->ModelPs->getRequeteOne($nbre_taches_revise);
    $data['nbre_tache_revise'] = number_format($nbre_taches_revise['NBRE'],'0',',',' ');
    //NBRE de taches trouvé dans le fichier ptba revisé
    $nbre_taches_trouves="SELECT COUNT(`PTBA_TACHE_REVISE_ID`) AS NBRE FROM ptba_tache_revise WHERE 1 AND ptba_tache_revise.PTBA_TACHE_ID >0 ";
    $nbre_taches_trouves = 'CALL getTable("'.$nbre_taches_trouves.'");';
    $nbre_taches_trouves = $this->ModelPs->getRequeteOne($nbre_taches_trouves);
    $data['nbre_tache_trouves'] = number_format($nbre_taches_trouves['NBRE'],'0',',',' ');
    //NBRE de taches non trouvées dans le fichier ptba revisé
    $nbre_taches_non_trouve="SELECT COUNT(`PTBA_TACHE_REVISE_ID`) AS NBRE FROM ptba_tache_revise WHERE 1 AND ptba_tache_revise.PTBA_TACHE_ID = 0 ";
    $nbre_taches_non_trouve = 'CALL getTable("'.$nbre_taches_non_trouve.'");';
    $nbre_taches_non_trouve = $this->ModelPs->getRequeteOne($nbre_taches_non_trouve);
    $data['nbre_tache_non_trouves'] = number_format($nbre_taches_non_trouve['NBRE'],'0',',',' ');
    return $data;
  }

  // gerer les montant pour retour a la correction d'engagement budgetaire et cas de decaiser le montant inferieur au montant de paiement
  public function gestion_retour_ptba($EXECUTION_BUDGETAIRE_ID,$montant)
  { 
    $getMontant  = 'SELECT tache.BUDGET_RESTANT_T1,
    tache.BUDGET_UTILISE_T1,
    tache.BUDGET_RESTANT_T2,
    tache.BUDGET_UTILISE_T2,
    tache.BUDGET_RESTANT_T3,
    tache.BUDGET_UTILISE_T3,
    tache.BUDGET_RESTANT_T4,
    tache.BUDGET_UTILISE_T4,
    tache.PTBA_TACHE_ID, 
    exec.TRIMESTRE_ID,
    exec.ANNEE_BUDGETAIRE_ID,
    exec.ENG_BUDGETAIRE,
    exec.ENG_BUDGETAIRE_DEVISE,
    exec.DEVISE_TYPE_ID,
    titre.MONTANT_PAIEMENT,
    titre.MONTANT_PAIEMENT_DEVISE 
    FROM ptba_tache tache 
    JOIN execution_budgetaire_execution_tache ebet ON ebet.PTBA_TACHE_ID=tache.PTBA_TACHE_ID 
    JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebet.EXECUTION_BUDGETAIRE_ID 
    JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID 
    WHERE titre.EXECUTION_BUDGETAIRE_ID='.$EXECUTION_BUDGETAIRE_ID;
    $getMontant = "CALL `getTable`('".$getMontant."');";
    $RestPTBA= $this->ModelPs->getRequeteOne($getMontant);
    $retourMontant = 0;
    $montantAnnuler=0;
    $total_utilise=0;
    $ANNEE_ACTUEL = $this->get_annee_budgetaire();

    if($RestPTBA['TRIMESTRE_ID']==1 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_ACTUEL) 
    {
      $retourMontant = floatval($RestPTBA['BUDGET_RESTANT_T1']) - floatval($montant);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T1']) + floatval($montant);

      $whereptba="PTBA_TACHE_ID=".$RestPTBA['PTBA_TACHE_ID'];        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T1=".$retourMontant.",BUDGET_UTILISE_T1=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
    else if ($RestPTBA['TRIMESTRE_ID']==2 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_ACTUEL)
    {
      $retourMontant = floatval($RestPTBA['BUDGET_RESTANT_T2']) - floatval($montant);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T2']) + floatval($montant);
      $whereptba ="PTBA_TACHE_ID = ".$RestPTBA['PTBA_TACHE_ID'];        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T2=".$retourMontant.",BUDGET_UTILISE_T2=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
    else if ($RestPTBA['TRIMESTRE_ID']==3 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_ACTUEL)
    {
      $retourMontant = floatval($RestPTBA['BUDGET_RESTANT_T3']) - floatval($montant);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T3']) + floatval($montant);
      $whereptba="PTBA_TACHE_ID=".$RestPTBA['PTBA_TACHE_ID'];        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T3=".$retourMontant.",BUDGET_UTILISE_T3=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
    else if ($RestPTBA['TRIMESTRE_ID']==4 && $RestPTBA['ANNEE_BUDGETAIRE_ID']==$ANNEE_ACTUEL)
    {
      $retourMontant = floatval($RestPTBA['BUDGET_RESTANT_T4']) - floatval($montant);
      $total_utilise = floatval($RestPTBA['BUDGET_UTILISE_T4']) + floatval($montant);
      $whereptba ="PTBA_TACHE_ID = ".$RestPTBA['PTBA_TACHE_ID'];        
      $insertIntoptba='ptba_tache';
      $columptba="BUDGET_RESTANT_T4=".$retourMontant.",BUDGET_UTILISE_T4=".$total_utilise;
      $this->update_all_table($insertIntoptba,$columptba,$whereptba);
    }
  }

  // compte par rapport au suivi execution
  public function count_suivi_execution($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0)
  {
    $condition="";
    if(!empty($INSTITUTION_ID))
    {
      if($INSTITUTION_ID>0)
      {
        $condition=" AND ptba_tache.INSTITUTION_ID=".$INSTITUTION_ID;
        if(!empty($SOUS_TUTEL_ID))
        {
          if($SOUS_TUTEL_ID>0)
          {
            $condition.=" AND ptba_tache.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
          }
        }
      }
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";

    /*Debut engagement budgétaire a corriger*/
    $EBCorr = $this->getBindParms('COUNT(DISTINCT det.EXECUTION_BUDGETAIRE_DETAIL_ID) AS EBCORRIGE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=4'.$condition,'1');
    $get_EBCorr= $this->ModelPs->getRequeteOne($callpsreq, $EBCorr);
    $EBCORRIGE=$get_EBCorr['EBCORRIGE'];
    $data['EBCORRIGE']=number_format($EBCORRIGE,'0',',',' ');
    /*Fin engagement budgétaire a corriger*/

    /*Debut engagement budgétaire a validé*/
    $EBAVALIDE = $this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS EBAVALIDE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=3'.$condition,'1');
    $get_EBAV= $this->ModelPs->getRequeteOne($callpsreq, $EBAVALIDE);
    $EBAVALIDE=$get_EBAV['EBAVALIDE'];
    $data['EBAVALIDE']=number_format($EBAVALIDE,'0',',',' ');
    /*Fin engagement budgétaire a validé*/

    /*Debut engagement juridique a faire*/
    $eng_jurid_faire=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS EJFAIRE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=6'.$condition,'1');
    $get_EJFAIRE=$this->ModelPs->getRequeteOne($callpsreq, $eng_jurid_faire);
    $EJFAIRE=$get_EJFAIRE['EJFAIRE'];
    $data['EJFAIRE']=number_format($EJFAIRE,'0',',',' ');
    /*Fin engagement juridique a faire*/

    /*Debut engagement juridique a corriger */
    $jur_a_corriger = $this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS EJCORRIGER','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=8'.$condition,'1');
    $get_EJCORRIGER= $this->ModelPs->getRequeteOne($callpsreq, $jur_a_corriger);
    $EJCORRIGER=$get_EJCORRIGER['EJCORRIGER'];
    $data['EJCORRIGER']=number_format($EJCORRIGER,'0',',',' ');
    /*Fin engagement juridique a corriger*/

    /*Debut engagement juridique a validé*/
    $jur_a_valider = $this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS EJVALIDER','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=7'.$condition,'1');
    $get_EJVALIDER= $this->ModelPs->getRequeteOne($callpsreq, $jur_a_valider);
    $EJVALIDER=$get_EJVALIDER['EJVALIDER'];
    $data['EJVALIDER']=number_format($EJVALIDER,'0',',',' ');
    /*Fin engagement juridique a validé*/

    /*Debut Liquidation a faire*/
    $liquid_Afaire=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS LIQFAIRE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=10'.$condition,'1');
    $get_LIQFAIRE= $this->ModelPs->getRequeteOne($callpsreq,$liquid_Afaire);
    $LIQFAIRE=$get_LIQFAIRE['LIQFAIRE'];
    $data['LIQFAIRE']=number_format($LIQFAIRE,'0',',',' ');
    /*Fin Liquidation a faire*/

    /*Debut Liquidation a corriger */
    $liquid_Acorriger=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS LIQCORRIGER','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=12'.$condition,'1');
    $get_LIQCORRIGER= $this->ModelPs->getRequeteOne($callpsreq,$liquid_Acorriger);
    $LIQCORRIGER=$get_LIQCORRIGER['LIQCORRIGER'];
    $data['LIQCORRIGER']=number_format($LIQCORRIGER,'0',',',' ');
    /*Fin Liquidation a corriger*/

    /*Debut Liquidation a validé*/
    $liquid_a_valide=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS LIQVALIDE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=11'.$condition,'1');
    $get_LIQVALIDE= $this->ModelPs->getRequeteOne($callpsreq,$liquid_a_valide);
    $LIQVALIDE=$get_LIQVALIDE['LIQVALIDE'];
    $data['LIQVALIDE']=number_format($LIQVALIDE,'0',',',' ');
    /*Fin Liquidation a validé*/

    /*Debut Ordonnancement a validé*/
    $ordonnancement_a_valide=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS ORDVALIDE','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID IN(14,15)'.$condition,'1');
    $get_ORDVALIDE= $this->ModelPs->getRequeteOne($callpsreq,$ordonnancement_a_valide);
    $ORDVALIDE=$get_ORDVALIDE['ORDVALIDE'];
    $data['ORDVALIDE']=number_format($ORDVALIDE,'0',',',' ');
    /*Fin Ordonnancement a validé*/

    /*Debut prise en charge a receptionner joa-kevin.iradukunda@mediabox.bi*/
    $queryPcRecep=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS NUMTOT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=17 AND bon_titre.TYPE_DOCUMENT_ID=1'.$condition,'1');
    $resultPcRecep= $this->ModelPs->getRequeteOne($callpsreq,$queryPcRecep);
    $NUMTOTPcRecep=$resultPcRecep['NUMTOT'];
    $data['prise_charge_a_recep']=number_format($NUMTOTPcRecep,'0',',',' ');
    /*Fin prise en charge a receptionner*/

    /*Debut etablissement du titre en attente d' etablissement joa-kevin.iradukunda@mediabox.bi*/
    $queryTitreAttEtab=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS NUMTOT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=20'.$condition,'1');
    $resultTitreAttEtab= $this->ModelPs->getRequeteOne($callpsreq,$queryTitreAttEtab);
    $NUMTOTTitreAttEtab=$resultTitreAttEtab['NUMTOT'];
    $data['titre_attente_etab']=number_format($NUMTOTTitreAttEtab,'0',',',' ');
    /* Fin etablissement du titre en attente d' etablissement joa-kevin.iradukunda@mediabox.bi*/

    /*Debut etablissement du titre en attente de correction joa-kevin.iradukunda@mediabox.bi*/
    $queryTitreAttCorr=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS NUMTOT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=37'.$condition,'1');
    $resultTitreAttCorr= $this->ModelPs->getRequeteOne($callpsreq,$queryTitreAttCorr);
    $NUMTOTTitreAttCorr=$resultTitreAttCorr['NUMTOT'];
    $data['titre_attente_corr']=number_format($NUMTOTTitreAttCorr,'0',',',' ');
    /* Fin etablissement du titre en attente de correction joa-kevin.iradukunda@mediabox.bi*/

    /*Debut dir compt a receptionner joa-kevin.iradukunda@mediabox.bi*/
    $queryDirComptRecep=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS NUMTOT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=22 AND bon_titre.TYPE_DOCUMENT_ID=2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1'.$condition,'1');
    $resultDirComptRecep= $this->ModelPs->getRequeteOne($callpsreq,$queryDirComptRecep);
    $NUMTOTDirComptRecep=$resultDirComptRecep['NUMTOT'];
    $data['dir_compt_recep']=number_format($NUMTOTDirComptRecep,'0',',',' ');
    /* Fin dir compt a receptionner joa-kevin.iradukunda@mediabox.bi*/

    /*Debut obr a receptionner joa-kevin.iradukunda@mediabox.bi*/
    $queryObrRecep=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS NUMTOT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=18 '.$condition,'1');
    $resultObrRecep= $this->ModelPs->getRequeteOne($callpsreq,$queryObrRecep);
    $NUMTOTObrRecep=$resultObrRecep['NUMTOT'];
    $data['obr_recep']=number_format($NUMTOTObrRecep,'0',',',' ');
    /* Fin obr a receptionner joa-kevin.iradukunda@mediabox.bi*/

    /*decaissement en attente de traitement joa-kevin.iradukunda@mediabox.bi*/
    $queryDecAttTrait=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS NUMTOT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID IN(29) '.$condition,'1');
    $resultDecAttTrait= $this->ModelPs->getRequeteOne($callpsreq,$queryDecAttTrait);
    $NUMTOTDecAttTrait=$resultDecAttTrait['NUMTOT'];
    $data['dec_att_trait']=number_format($NUMTOTDecAttTrait,'0',',',' ');
    /*decaissement en attente de traitement joa-kevin.iradukunda@mediabox.bi*/
    /*decaissement en attente de reception brb joa-kevin.iradukunda@mediabox.bi*/
    $queryDecAttRecepBrb=$this->getBindParms('COUNT(DISTINCT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) AS NUMTOT','execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_execution_tache ebet ON ebet.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN ptba_tache ON ptba_tache.PTBA_TACHE_ID=ebet.PTBA_TACHE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=ptba_tache.CODE_NOMENCLATURE_BUDGETAIRE_ID','ebtd.ETAPE_DOUBLE_COMMANDE_ID=28 AND bon_titre.TYPE_DOCUMENT_ID=2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1'.$condition,'1');
    $resultDecAttRecepBrb= $this->ModelPs->getRequeteOne($callpsreq,$queryDecAttRecepBrb);
    $NUMTOTDecAttRecepBrb=$resultDecAttRecepBrb['NUMTOT'];
    $data['dec_att_recep_brb']=number_format($NUMTOTDecAttRecepBrb,'0',',',' ');
    /*decaissement en attente de reception brb joa-kevin.iradukunda@mediabox.bi*/

    return $data;
  }

  // gerer les montant des engangement rejeter et retourner pour la correction sur l'engagement budgetaire
  public function gestion_rejet_ptba($exec_id)
  {
    $getMontant  = 'SELECT tache.BUDGET_RESTANT_T1,
    tache.BUDGET_UTILISE_T1,
    tache.BUDGET_RESTANT_T2,
    tache.BUDGET_UTILISE_T2,
    tache.BUDGET_RESTANT_T3,
    tache.BUDGET_UTILISE_T3,
    tache.BUDGET_RESTANT_T4,
    tache.BUDGET_UTILISE_T4,
    tache.PTBA_TACHE_ID,
    ebet.MONTANT_ENG_BUDGETAIRE AS ENG_BUDGETAIRE
    
    FROM ptba_tache tache 
    JOIN execution_budgetaire_execution_tache ebet ON ebet.PTBA_TACHE_ID=tache.PTBA_TACHE_ID
    WHERE ebet.EXECUTION_BUDGETAIRE_ID='.$exec_id;
    $getMontant = "CALL `getTable`('".$getMontant."');";
    $RestPTBA= $this->ModelPs->getRequete($getMontant);

    $get_exec ='SELECT exec.TRIMESTRE_ID,MARCHE_PUBLIQUE,exec.ANNEE_BUDGETAIRE_ID FROM  execution_budgetaire exec WHERE  exec.EXECUTION_BUDGETAIRE_ID='.$exec_id;
    $get_exec = "CALL `getTable`('".$get_exec."');";
    $get_exec= $this->ModelPs->getRequeteOne($get_exec);

    $ANNEE_ACTUEL = $this->get_annee_budgetaire();
    $apresEng = 0;
    $reste_utilise= 0;

    //Cas du marche public
    $getMontantenleve = 'SELECT BUDGET_ENLEVE_T1,BUDGET_ENLEVE_T2,BUDGET_ENLEVE_T3,BUDGET_ENLEVE_T4 FROM execution_budgetaire_histo_budget_marche_public marche WHERE marche.EXECUTION_BUDGETAIRE_ID='.$exec_id.' AND ANNEE_BUDGETAIRE_ID='.$ANNEE_ACTUEL.' ORDER BY EXECUTION_BUDGETAIRE_HISTO_BUDGET_MARCHE_PUBLIC_ID DESC';
    $getMontantenleve = "CALL `getTable`('".$getMontantenleve."');";
    $MontantEnleve= $this->ModelPs->getRequeteOne($getMontantenleve);
    
    foreach($RestPTBA as $key)
    {
      if($get_exec['MARCHE_PUBLIQUE']==0)
      {
        if($get_exec['TRIMESTRE_ID']==1 && $get_exec['ANNEE_BUDGETAIRE_ID']==$ANNEE_ACTUEL)
        {
          $apresEng = floatval($key->BUDGET_RESTANT_T1) + floatval($key->ENG_BUDGETAIRE);
          $reste_utilise = floatval($key->BUDGET_UTILISE_T1) - floatval($key->ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$key->PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T1 = ".$apresEng.", BUDGET_UTILISE_T1=".$reste_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($get_exec['TRIMESTRE_ID']==2 && $get_exec['ANNEE_BUDGETAIRE_ID']==$ANNEE_ACTUEL)
        {
          $apresEng = floatval($key->BUDGET_RESTANT_T2) + floatval($key->ENG_BUDGETAIRE);
          $reste_utilise = floatval($key->BUDGET_UTILISE_T2) - floatval($key->ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$key->PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T2 = ".$apresEng.", BUDGET_UTILISE_T2=".$reste_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($get_exec['TRIMESTRE_ID']==3 && $get_exec['ANNEE_BUDGETAIRE_ID']==$ANNEE_ACTUEL)
        {
          $apresEng = floatval($key->BUDGET_RESTANT_T3) + floatval($key->ENG_BUDGETAIRE);
          $reste_utilise = floatval($key->BUDGET_UTILISE_T3) - floatval($key->ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID =".$key->PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T3 = ".$apresEng.", BUDGET_UTILISE_T3=".$reste_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        else if ($get_exec['TRIMESTRE_ID']==4 && $get_exec['ANNEE_BUDGETAIRE_ID']==$ANNEE_ACTUEL)
        {
          $apresEng = floatval($key->BUDGET_RESTANT_T4) + floatval($key->ENG_BUDGETAIRE);
          $reste_utilise = floatval($key->BUDGET_UTILISE_T4) - floatval($key->ENG_BUDGETAIRE);

          $whereptba ="PTBA_TACHE_ID = ".$key->PTBA_TACHE_ID;        
          $insertIntoptba='ptba_tache';
          $columptba="BUDGET_RESTANT_T4 = ".$apresEng.", BUDGET_UTILISE_T4=".$reste_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
      }
      else
      {
        
        $whereptba ="PTBA_TACHE_ID = ".$key->PTBA_TACHE_ID;        
        $insertIntoptba='ptba_tache';        

        if($MontantEnleve['BUDGET_ENLEVE_T1']>0)
        {
          $apresEng = floatval($key->BUDGET_RESTANT_T1) + floatval($MontantEnleve['BUDGET_ENLEVE_T1']);
          $reste_utilise = floatval($key->BUDGET_UTILISE_T1) - floatval($MontantEnleve['BUDGET_ENLEVE_T1']);
          $columptba="BUDGET_RESTANT_T1 = ".$apresEng.", BUDGET_UTILISE_T1=".$reste_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        if($MontantEnleve['BUDGET_ENLEVE_T2']>0)
        {
          $apresEng = floatval($key->BUDGET_RESTANT_T2) + floatval($MontantEnleve['BUDGET_ENLEVE_T2']);
          $reste_utilise = floatval($key->BUDGET_UTILISE_T2) - floatval($MontantEnleve['BUDGET_ENLEVE_T2']);
          $columptba="BUDGET_RESTANT_T2 = ".$apresEng.", BUDGET_UTILISE_T2=".$reste_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        if($MontantEnleve['BUDGET_ENLEVE_T3']>0)
        {
          $apresEng = floatval($key->BUDGET_RESTANT_T3) + floatval($MontantEnleve['BUDGET_ENLEVE_T3']);
          $reste_utilise = floatval($key->BUDGET_UTILISE_T3) - floatval($MontantEnleve['BUDGET_ENLEVE_T3']);
          $columptba="BUDGET_RESTANT_T3 = ".$apresEng.", BUDGET_UTILISE_T3=".$reste_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }
        if($MontantEnleve['BUDGET_ENLEVE_T4']>0)
        {
          $apresEng = floatval($key->BUDGET_RESTANT_T4) + floatval($MontantEnleve['BUDGET_ENLEVE_T4']);
          $reste_utilise = floatval($key->BUDGET_UTILISE_T4) - floatval($MontantEnleve['BUDGET_ENLEVE_T4']);
          $columptba="BUDGET_RESTANT_T4 = ".$apresEng.", BUDGET_UTILISE_T4=".$reste_utilise;
          $this->update_all_table($insertIntoptba,$columptba,$whereptba);
        }      
      }
    }      
  }

  // recuperer les annees du PIP
  public function get_annee_pip()
  {
    $YEAR=date('Y');
    $MONTHS=date('m');
    $CONDICTION="ANNEE_DEBUT='".$YEAR."'";

    if($MONTHS>=7 && $MONTHS<=12)
    {
      $YEAR=$YEAR+1;
    }

    $callpsreq = "CALL getRequeteLimit(?,?,?,?,?);";
    $bind=$this->getBindParmsLimit('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN','annee_budgetaire','ANNEE_DEBUT>='.$YEAR,'ANNEE_DEBUT ASC','3');
    $annees=$this->ModelPs->getRequete($callpsreq, $bind);
    return $annees;
  }

  // function pour gerer des projects du PIP
  function menu_pip()
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return redirect('Login_Ptba');
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";

    $institution=' AND info_sup.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

    $NIVEAU_VISUALISATION_ID=session()->get("SESSION_SUIVIE_PTBA_NIVEAU_VISUALISATION_ID");
    if($NIVEAU_VISUALISATION_ID==1)
    {
      $institution='';
    }

    $ficheComplet = "SELECT info_sup.ID_DEMANDE_INFO_SUPP,statut.DESCR_STATUT_PROJET,info_sup.NOM_PROJET,info_sup.NUMERO_PROJET,demande.ID_DEMANDE,demande.DATE_INSERTION,inst_institutions.INSTITUTION_ID,proc_etape.DESCR_ETAPE FROM pip_demande_infos_supp info_sup left join inst_institutions on  info_sup.INSTITUTION_ID=inst_institutions.INSTITUTION_ID left join proc_demandes demande on demande.ID_DEMANDE=info_sup.ID_DEMANDE left join proc_etape on  demande.ETAPE_ID=proc_etape.ETAPE_ID left join proc_process on demande.PROCESS_ID=proc_process.PROCESS_ID LEFT JOIN pip_statut_projet statut ON info_sup.ID_STATUT_PROJET=statut.ID_STATUT_PROJET WHERE info_sup.IS_ANNULER=0 AND  proc_process.PROCESS_ID=1 and  IS_FINISHED = 1 AND IS_COMPILE=0 ".$institution;

    $ficheComplet = 'CALL getTable("'.$ficheComplet.'");';
    $ficheComplet = $this->ModelPs->getRequete($ficheComplet);
    $ficheComplet = (!empty($ficheComplet)) ? count($ficheComplet) : 0 ;
    $data['nbre_Complet'] = number_format($ficheComplet,'0',',',' ');

    $ficheIncomplet = "SELECT info_sup.ID_DEMANDE_INFO_SUPP,statut.DESCR_STATUT_PROJET,info_sup.NOM_PROJET,info_sup.NUMERO_PROJET,demande.ID_DEMANDE,demande.DATE_INSERTION,inst_institutions.INSTITUTION_ID,proc_etape.DESCR_ETAPE FROM pip_demande_infos_supp info_sup left join inst_institutions on  info_sup.INSTITUTION_ID=inst_institutions.INSTITUTION_ID left join proc_demandes demande on demande.ID_DEMANDE=info_sup.ID_DEMANDE left join proc_etape on  demande.ETAPE_ID=proc_etape.ETAPE_ID left join proc_process on demande.PROCESS_ID=proc_process.PROCESS_ID LEFT JOIN pip_statut_projet statut ON info_sup.ID_STATUT_PROJET=statut.ID_STATUT_PROJET WHERE info_sup.IS_ANNULER=0 and  proc_process.PROCESS_ID=1 and  IS_FINISHED = 0 AND IS_COMPILE=0 ".$institution;

    $ficheIncomplet = 'CALL getTable("'.$ficheIncomplet.'");';
    $incomplet = $this->ModelPs->getRequete($ficheIncomplet);
    $incomplet = (!empty($incomplet)) ? count($incomplet) : 0 ;
    $data['nbre_incomplet']= number_format($incomplet,'0',',',' ');

    $ficheValide = "SELECT statut.DESCR_STATUT_PROJET,info_sup.NOM_PROJET,info_sup.NUMERO_PROJET,demande.ID_DEMANDE,demande.DATE_INSERTION,inst_institutions.INSTITUTION_ID,proc_etape.DESCR_ETAPE,proc_etape.ETAPE_ID FROM pip_demande_infos_supp info_sup left join inst_institutions on  info_sup.INSTITUTION_ID=inst_institutions.INSTITUTION_ID left join proc_demandes demande on demande.ID_DEMANDE=info_sup.ID_DEMANDE left join proc_etape on  demande.ETAPE_ID=proc_etape.ETAPE_ID left join proc_process on demande.PROCESS_ID=proc_process.PROCESS_ID LEFT JOIN pip_statut_projet statut ON info_sup.ID_STATUT_PROJET=statut.ID_STATUT_PROJET WHERE  proc_process.PROCESS_ID=1 and  IS_FINISHED = 1 AND IS_COMPILE=0 AND proc_etape.ETAPE_ID = 90 ".$institution;

    $ficheValide = 'CALL getTable("'.$ficheValide.'");';
    $valide = $this->ModelPs->getRequete($ficheValide);
    $valide = (!empty($valide)) ? count($valide) : 0 ;
    $data['nbre_valide']= number_format($valide,'0',',',' ');

    $fichecorriger = "SELECT statut.DESCR_STATUT_PROJET,info_sup.NOM_PROJET,info_sup.NUMERO_PROJET,demande.ID_DEMANDE,demande.DATE_INSERTION,inst_institutions.INSTITUTION_ID,proc_etape.DESCR_ETAPE,info_sup.IS_CORRECTION FROM pip_demande_infos_supp info_sup LEFT JOIN inst_institutions ON info_sup.INSTITUTION_ID = inst_institutions.INSTITUTION_ID LEFT JOIN proc_demandes demande ON demande.ID_DEMANDE = info_sup.ID_DEMANDE LEFT JOIN proc_etape ON demande.ETAPE_ID = proc_etape.ETAPE_ID LEFT JOIN proc_process ON demande.PROCESS_ID = proc_process.PROCESS_ID LEFT JOIN pip_statut_projet statut ON info_sup.ID_STATUT_PROJET = statut.ID_STATUT_PROJET WHERE proc_process.PROCESS_ID = 1 AND IS_FINISHED = 1 AND IS_COMPILE = 0 AND (info_sup.IS_CORRECTION = 1 OR info_sup.IS_CORRECTION = 2)".$institution;

    $fichecorriger = 'CALL getTable("'.$fichecorriger.'");';
    $corriger = $this->ModelPs->getRequete($fichecorriger);
    $corriger = (!empty($corriger)) ? count($corriger) : 0 ;
    $data['nbre_corriger']= number_format($corriger,'0',',',' ');
    return $data;
  }

  // pour gerer le fichier du PIP des projects compile
  function pip_compile()
  {
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return redirect('Login_Ptba');
    }

    $projetCompiler = "SELECT statut.DESCR_STATUT_PROJET,info_sup.NOM_PROJET,info_sup.NUMERO_PROJET,demande.ID_DEMANDE,demande.DATE_INSERTION,inst_institutions.INSTITUTION_ID,inst_institutions.DESCRIPTION_INSTITUTION,etape.DESCR_ETAPE FROM pip_demande_infos_supp info_sup LEFT JOIN inst_institutions ON info_sup.INSTITUTION_ID = inst_institutions.INSTITUTION_ID LEFT JOIN proc_demandes demande ON demande.ID_DEMANDE = info_sup.ID_DEMANDE LEFT JOIN proc_etape etape ON demande.ETAPE_ID = etape.ETAPE_ID JOIN proc_actions ON proc_actions.ETAPE_ID = etape.ETAPE_ID LEFT JOIN proc_process ON demande.PROCESS_ID = proc_process.PROCESS_ID LEFT JOIN pip_statut_projet statut ON info_sup.ID_STATUT_PROJET = statut.ID_STATUT_PROJET WHERE demande.PROCESS_ID = 1  AND info_sup.IS_COMPILE = 0 AND info_sup.IS_FINISHED = 1 AND proc_actions.IS_COMPILE=1 AND GET_FORM=1 ";

    $projetCompiler = 'CALL getTable("'.$projetCompiler.'");';
    $projetCompiler = $this->ModelPs->getRequete($projetCompiler);
    $projetCompiler = (!empty($projetCompiler)) ? count($projetCompiler) : 0 ;
    $data['compilation'] = number_format($projetCompiler,'0',',',' ');

    $pipPropose="SELECT ID_DOC_COMPILATION, PATH_DOC_COMPILER,proc_etape.DESCR_ETAPE,CODE_PIP, DATE_COMPILATION,STATUT FROM pip_document_compilation fiche JOIN proc_etape ON proc_etape.ETAPE_ID=fiche.ETAPE_ID WHERE STATUT=0";

    $pipPropose = 'CALL getTable("'.$pipPropose.'");';
    $proposer = $this->ModelPs->getRequete($pipPropose);
    $proposer = (!empty($proposer)) ? count($proposer) : 0 ;
    $data['pip_proposer']= number_format($proposer,'0',',',' ');

    $pipCorrige = "SELECT ID_DOC_COMPILATION, PATH_DOC_COMPILER,proc_etape.DESCR_ETAPE ,CODE_PIP, DATE_COMPILATION,STATUT FROM pip_document_compilation fiche JOIN proc_etape ON proc_etape.ETAPE_ID=fiche.ETAPE_ID WHERE STATUT=1";

    $pipCorrige = 'CALL getTable("'.$pipCorrige.'");';
    $corriger = $this->ModelPs->getRequete($pipCorrige);
    $corriger = (!empty($corriger)) ? count($corriger) : 0 ;
    $data['pip_corriger']= number_format($corriger,'0',',',' ');

    $pipvalide =  "SELECT ID_DOC_COMPILATION, PATH_DOC_COMPILER,proc_etape.DESCR_ETAPE ,CODE_PIP, DATE_COMPILATION,STATUT FROM pip_document_compilation fiche JOIN proc_etape ON proc_etape.ETAPE_ID=fiche.ETAPE_ID WHERE STATUT=2";

    $pipvalide = 'CALL getTable("'.$pipvalide.'");';
    $valider = $this->ModelPs->getRequete($pipvalide);
    $valider = (!empty($valider)) ? count($valider) : 0 ;
    $data['pip_valider']= number_format($valider,'0',',',' ');
    return $data;
  }

  // recuperer ANNEE_BUDGETAIRE_ID en cours
  public function get_annee_budgetaire()
  {
    $YEAR=date('Y');
    $MONTHS=date('m');

    if($MONTHS>=1 && $MONTHS<=6)
    {
      $YEAR=$YEAR-1;
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";
    $bind=$this->getBindParms('ANNEE_BUDGETAIRE_ID,ANNEE_DESCRIPTION,ANNEE_DEBUT,ANNEE_FIN','annee_budgetaire','ANNEE_DEBUT>='.$YEAR,'ANNEE_DEBUT ASC');
    $annees=$this->ModelPs->getRequeteOne($callpsreq, $bind);
    return $annees['ANNEE_BUDGETAIRE_ID'];
  }

  // recuperer les informations l'intervale d'une tranche par rapport a la date qui est d'aujourd'hui
  function converdate()
  {
    $tranche="SELECT TRIMESTRE_ID,DESC_TRIMESTRE, CODE_TRIMESTRE,CONCAT(DATE_DEBUT,'-',date_format(now(),'%Y')) as debut,CONCAT(DATE_FIN,'-',date_format(now(),'%Y')) as fin FROM trimestre WHERE 1 AND date_format(now(),'%m-%d-%Y') BETWEEN CONCAT(DATE_DEBUT,'-',date_format(now(),'%Y')) AND CONCAT(DATE_FIN,'-',date_format(now(),'%Y'))";
    $getTranchee = 'CALL getTable("'.$tranche.'");';

    $getTranche = $this->ModelPs->getRequeteOne($getTranchee);

    $jours=substr($getTranche['debut'],3,2);
    $mois=substr($getTranche['debut'],0,2);
    $annes=substr($getTranche['debut'],6,4);
    $datenow= date('H:i');
    $datedebut= $annes.'-'.$mois.'-'.$jours.' '.$datenow;

    $joursFin=substr($getTranche['fin'],3,2);
    $moisFin=substr($getTranche['fin'],0,2);
    $annesFin=substr($getTranche['fin'],6,4);
    $dateFin= $annesFin.'-'.$moisFin.'-'.$joursFin.' 00:01';
    $data['debut']=$datedebut;
    $data['fin']=$dateFin;
    $data['CODE_TRIMESTRE']=$getTranche['CODE_TRIMESTRE'];
    $data['TRIMESTRE_ID']=$getTranche['TRIMESTRE_ID'];
    $data['DESC_TRIMESTRE']=$getTranche['DESC_TRIMESTRE'];
    return $data;
  }

  // compter les engagements budgetaires
  public function count_engag_budg_new()
  {
    $db = \Config\Database::connect();
    $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    $db->query($sql);

    $session=\Config\Services::session();
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return redirect('Login_Ptba');
    }
    $callpsreq = "CALL getRequete(?,?,?,?);";

    //selectionner les valeurs a mettre dans le menu en haut
    $data['profil_id']=$profil_id;
    //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $str_condiction_user=" AND exec.USER_ID=".$user_id;
    if($profil_id==1)
    {
      $str_condiction_user="";
    }

    // Debut engagements budgetaire rejeter
    $eng_rej="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=5 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN (".$ID_INST.")";

    $eng_rej = 'CALL getTable("'.$eng_rej.'");';
    $eng_rej = $this->ModelPs->getRequete($eng_rej);
    $eng_rej = (!empty($eng_rej)) ? count($eng_rej) : 0 ;
    $data['nbr_eng_rej'] = number_format($eng_rej,'0',',',' ');
    // Fin engagements budgetaire rejeter

    // Debut engagement budgetaire sans bon
    $SBE= "SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=2 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$str_condiction_user." AND exec.INSTITUTION_ID IN(".$ID_INST.")";

    $get_SBE = $this->ModelPs->getRequete($SBE);
    $get_SBE = (!empty($get_SBE)) ? count($get_SBE) : 0 ;
    $data['SBE'] = number_format($get_SBE,'0',',',' ');
    // Fin engagement budgetaire sans bon

    /* Debut engagement budgétaire déjà fait EBF*/
    $EBF="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN (1,2) AND titre.ETAPE_DOUBLE_COMMANDE_ID>2 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 ".$str_condiction_user." AND exec.INSTITUTION_ID IN(".$ID_INST.")";

    $get_EBF = $this->ModelPs->getRequete($EBF);
    $get_EBF = (!empty($get_EBF)) ? count($get_EBF) : 0 ;
    $data['EBF'] = number_format($get_EBF,'0',',',' ');
    /* Fin engagement budgétaire déjà fait EBF*/

    /* Debut engagement budgétaire a valider EBAV*/
    $EBAV="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=3 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN (1,2) AND exec.INSTITUTION_ID IN (".$ID_INST.")";

    $get_EBAV = $this->ModelPs->getRequete($EBAV);
    $get_EBAV = (!empty($get_EBAV)) ? count($get_EBAV) : 0 ;
    $data['EBAV'] = number_format($get_EBAV,'0',',',' ');
    /* Fin engagement budgétaire a valider EBAV*/
    
    /* Debut engagement budgétaire deja valide EBDV*/
    $EBDV="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID > 5 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN (".$ID_INST.")";

    $EBDV = 'CALL getTable("'.$EBDV.'");';
    $EBDV = $this->ModelPs->getRequete($EBDV);
    $EBDV = (!empty($EBDV)) ? count($EBDV) : 0 ;
    $data['EBDV'] = number_format($EBDV,'0',',',' ');
    /* Fin Debut engagement budgétaire deja valide EBDV*/
    
    // Debut engagements rejetés
    $fin_rej="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN (1,2) AND exec.INSTITUTION_ID IN(".$ID_INST.")";

    $fin_rej = 'CALL getTable("'.$fin_rej.'");';
    $fin_rej = $this->ModelPs->getRequete($fin_rej);
    $fin_rej = (!empty($fin_rej)) ? count($fin_rej) : 0 ;
    $data['nbr_fin_rej'] = number_format($fin_rej,'0',',',' ');
    // Fin engagements rejetés

    /* Debut engagement budgétaire a corriger EBCorr*/
    $EBCorr="SELECT exec.EXECUTION_BUDGETAIRE_ID,titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,dev.DEVISE_TYPE_ID,dev.DESC_DEVISE_TYPE,exec.ENG_BUDGETAIRE,exec.ENG_BUDGETAIRE_DEVISE,ligne.CODE_NOMENCLATURE_BUDGETAIRE,titre.ETAPE_DOUBLE_COMMANDE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN (1,2) AND titre.ETAPE_DOUBLE_COMMANDE_ID=4 ".$str_condiction_user." AND exec.INSTITUTION_ID IN(".$ID_INST.")";

    $get_EBCorr = $this->ModelPs->getRequete($EBCorr);
    $get_EBCorr = (!empty($get_EBCorr)) ? count($get_EBCorr) : 0 ;
    $data['EBCorr'] = number_format($get_EBCorr,'0',',',' ');
    /* Fin engagement budgétaire a corriger EBCorr*/
    return $data;
  }

  public function getDataMenuJuridique_new($value='')
  {
    $db = \Config\Database::connect();
    $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    $db->query($sql);

    $callpsreq = "CALL getRequete(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return  redirect('Login_Ptba/do_logout');
    }

    //selectionner les valeurs a mettre dans le menu en haut
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $data['profil_id'] = $profil_id;

    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    $str_condiction_user=" AND USER_ID=".$user_id;
    if($profil_id==1)
    {
      $str_condiction_user="";
    }

    $getInst  = 'SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE INSTITUTION_ID IN ('.$ID_INST.')  ORDER BY CODE_INSTITUTION ASC';
    $getInst = "CALL getTable('" .$getInst. "');";
    $data['institutions'] = $this->ModelPs->getRequete($getInst);
    
    /* Debut engagement juridique à faire */
    $eng_jurid_faire='SELECT exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=6 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN ('.$ID_INST.')';
    $eng_jurid_faire = 'CALL getTable("'.$eng_jurid_faire.'");';
    $eng_jurid_faire = $this->ModelPs->getRequete($eng_jurid_faire);
    $eng_jurid_faire = (!empty($eng_jurid_faire)) ? count($eng_jurid_faire) : 0 ;
    $data['jur_a_faire'] = number_format($eng_jurid_faire,'0',',',' ');
    /* Fin engagement juridique à faire */

    /* Debut engagement juridique deja fait */
    $jur_deja_fait='SELECT exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID>6 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.  EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE histo.ETAPE_DOUBLE_COMMANDE_ID=6'.$str_condiction_user.')';

    $jur_deja_fait = 'CALL getTable("'.$jur_deja_fait.'");';
    $jur_deja_fait = $this->ModelPs->getRequete($jur_deja_fait);
    $jur_deja_fait = (!empty($jur_deja_fait)) ? count($jur_deja_fait) : 0 ;
    $data['jur_deja_fait'] = number_format($jur_deja_fait,'0',',',' ');
    /* Fin engagement juridique deja fait */


    /* Debut engagement juridique à corriger*/
    $jur_a_corriger='SELECT exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=8 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN ('.$ID_INST.') AND titre. EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE histo.ETAPE_DOUBLE_COMMANDE_ID=6 '.$str_condiction_user.')';

    $jur_a_corriger = 'CALL getTable("'.$jur_a_corriger.'");';
    $jur_a_corriger = $this->ModelPs->getRequete($jur_a_corriger);
    $jur_a_corriger = (!empty($jur_a_corriger)) ? count($jur_a_corriger) : 0 ;
    $data['jur_a_corriger'] = number_format($jur_a_corriger,'0',',',' ');
    /* Fin engagement juridique à corriger*/

    /* Debut engagement juridique à valider*/
    $jur_a_valider='SELECT exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=7 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN ('.$ID_INST.')';

    $jur_a_valider = 'CALL getTable("'.$jur_a_valider.'");';
    $jur_a_valider = $this->ModelPs->getRequete($jur_a_valider);
    $jur_a_valider = (!empty($jur_a_valider)) ? count($jur_a_valider) : 0 ;
    $data['jur_a_valider'] = number_format($jur_a_valider,'0',',',' ');
    /* Fin engagement juridique à valider*/

    /* Debut engagement juridique déjà validé*/
    $jur_deja_valide='SELECT exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID>9 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN ('.$ID_INST.') AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN (SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 '.$str_condiction_user.')';

    $jur_deja_valide = 'CALL getTable("'.$jur_deja_valide.'");';
    $jur_deja_valide = $this->ModelPs->getRequete($jur_deja_valide);
    $jur_deja_valide = (!empty($jur_deja_valide)) ? count($jur_deja_valide) : 0 ;
    $data['jur_deja_valide'] = number_format($jur_deja_valide,'0',',',' ');
    /* Fin engagement juridique déjà validé*/


    /* Debut engagement juridique rejeté*/
    $jur_rejeter='SELECT exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=9 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND exec.INSTITUTION_ID IN ('.$ID_INST.')';

    $jur_rejeter = 'CALL getTable("'.$jur_rejeter.'");';
    $jur_rejeter = $this->ModelPs->getRequete($jur_rejeter);
    $jur_rejeter = (!empty($jur_rejeter)) ? count($jur_rejeter) : 0 ;
    $data['jur_rejeter'] = number_format($jur_rejeter,'0',',',' ');
    /* Fin engagement juridique rejeté*/
    return $data;
  }

  // compter les engagements liquidation
  public function getDataMenuLiquidation_new($value='')
  {
    $db = \Config\Database::connect();
    $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    $db->query($sql);

    $callpsreq = "CALL getRequete(?,?,?,?);";
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
    else
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $cond_user_id='';
    if($profil_id!=1)
    {
      $cond_user_id=' AND histo.USER_ID='.$user_id;
    }

    // get instition d'affectation de personne connecté
    $user = $this->getBindParms('aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION','user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID','USER_ID='.$user_id.'','INSTITUTION_ID');
    $data['institutions_user'] = $this->ModelPs->getRequete($callpsreq, $user);
    $institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

    // get nombre liquidation à faire
    $nbr_liquid_Afaire="SELECT DISTINCT(exec.EXECUTION_BUDGETAIRE_ID) FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=10 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND IS_FINISHED!=1 ".$institution."";
    $nbr_liquid_Afaire = 'CALL getTable("'.$nbr_liquid_Afaire.'");';
    $nbr_liquid_Afaire = $this->ModelPs->getRequete($nbr_liquid_Afaire);
    $nbr_liquid_Afaire = (!empty($nbr_liquid_Afaire)) ? count($nbr_liquid_Afaire) : 0 ;
    $data['get_liquid_Afaire'] = number_format($nbr_liquid_Afaire,'0',',',' ');

    // get nombre liquidation deja fait
    $nbr_liquid_deja_fait="SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID = titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID = exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID>10 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN(SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$cond_user_id.")";

    $nbr_liquid_deja_fait = 'CALL getTable("'.$nbr_liquid_deja_fait.'");';
    $nbr_liquid_deja_fait = $this->ModelPs->getRequete($nbr_liquid_deja_fait);
    $nbr_liquid_deja_fait = (!empty($nbr_liquid_deja_fait)) ? count($nbr_liquid_deja_fait) : 0 ;
    $data['get_liquid_deja_fait'] = number_format($nbr_liquid_deja_fait,'0',',',' ');

    // get nombre liquidation à corriger
    $nbr_liquid_Acorriger = "SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM  execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=12 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN(SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$cond_user_id.")";
    $nbr_liquid_Acorriger = 'CALL getTable("'.$nbr_liquid_Acorriger.'");';
    $nbr_liquid_Acorriger = $this->ModelPs->getRequete($nbr_liquid_Acorriger);
    $nbr_liquid_Acorriger = (!empty($nbr_liquid_Acorriger)) ? count($nbr_liquid_Acorriger) : 0 ;
    $data['get_liquid_Acorriger'] = number_format($nbr_liquid_Acorriger,'0',',',' ');

    // Liquidation partielle
    $nbr_liquid_partielle = "SELECT exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_ID =exec.EXECUTION_BUDGETAIRE_ID WHERE exec.LIQUIDATION_TYPE_ID=1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND IS_FINISHED !=1 ".$institution."";
    $nbr_liquid_partielle = 'CALL getTable("'.$nbr_liquid_partielle.'");';
    $nbr_liquid_partielle = $this->ModelPs->getRequete($nbr_liquid_partielle);
    $nbr_liquid_partielle = (!empty($nbr_liquid_partielle)) ? count($nbr_liquid_partielle) : 0 ;
    $data['get_liquid_partielle'] = number_format($nbr_liquid_partielle,'0',',',' ');    

    //from ordonnancement to ced
    $requete_ord= "SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=31 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$institution."";
    $nbr_from_ord = 'CALL getTable("'.$requete_ord.'");';
    $nbr_from_ord = $this->ModelPs->getRequete($nbr_from_ord);
    $nbr_from_ord = (!empty($nbr_from_ord)) ? count($nbr_from_ord) : 0 ;
    $data['nbr_from_ord'] = number_format($nbr_from_ord,'0',',',' ');

    // get nombre liquidation à valider
    $nbr_liquid_Avalider = "SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID=11 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$institution."";
    $nbr_liquid_Avalider = 'CALL getTable("'.$nbr_liquid_Avalider.'");';
    $nbr_liquid_Avalider = $this->ModelPs->getRequete($nbr_liquid_Avalider);
    $nbr_liquid_Avalider = (!empty($nbr_liquid_Avalider)) ? count($nbr_liquid_Avalider) : 0 ;
    $data['get_liquid_Avalider'] = number_format($nbr_liquid_Avalider,'0',',',' ');

    // get nombre liquidation valider
    $nbr_liquid_valider = "SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=titre.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE titre.ETAPE_DOUBLE_COMMANDE_ID>12 AND titre.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID IN(SELECT histo.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_tache_detail_histo histo WHERE 1 ".$cond_user_id.")";//".$institution."
    $nbr_liquid_valider = 'CALL getTable("'.$nbr_liquid_valider.'");';
    $nbr_liquid_valider = $this->ModelPs->getRequete($nbr_liquid_valider);
    $nbr_liquid_valider = (!empty($nbr_liquid_valider)) ? count($nbr_liquid_valider) : 0 ;
    $data['get_liquid_valider'] = number_format($nbr_liquid_valider,'0',',',' ');

    // Liquidation rejeté
    $nbr_liquid_rejeter = "SELECT titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement titre JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=titre.EXECUTION_BUDGETAIRE_ID WHERE 1 AND titre.ETAPE_DOUBLE_COMMANDE_ID=13 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)  ".$institution."";
    $nbr_liquid_rejeter = 'CALL getTable("'.$nbr_liquid_rejeter.'");';
    $nbr_liquid_rejeter = $this->ModelPs->getRequete($nbr_liquid_rejeter);
    $nbr_liquid_rejeter = (!empty($nbr_liquid_rejeter)) ? count($nbr_liquid_rejeter) : 0 ;
    $data['get_liquid_rejeter'] = number_format($nbr_liquid_rejeter,'0',',',' ');

    return $data;
  }

  public function getDataMenuOrdonnancement($INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0,$ANNEE_BUDGETAIRE_ID=0)
  {
    $db = \Config\Database::connect();
    $sql = "SET sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
    $db->query($sql);
    
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $session  = \Config\Services::session();
    $user_id='';
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba/do_logout');
    }
    else
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }

    $condition="";
    if(!empty($INSTITUTION_ID))
    {
      if($INSTITUTION_ID>0)
      {
        $condition=" AND exec.INSTITUTION_ID=".$INSTITUTION_ID;
        if(!empty($SOUS_TUTEL_ID))
        {
          if($SOUS_TUTEL_ID>0)
          {
            $condition.=" AND exec.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
          }
        }
      }
    }

    if($ANNEE_BUDGETAIRE_ID>0)
    {
      $condition.=" AND exec.ANNEE_BUDGETAIRE_ID=".$ANNEE_BUDGETAIRE_ID;
    }

    if($DATE_DEBUT > 0 AND $DATE_FIN == 0)
    {
      $condition.=" AND DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."'";
    }

    if ($DATE_FIN > 0 && $DATE_DEBUT > 0)
    {
      $condition.=" AND DATE_ORDONNANCEMENT >= '".$DATE_DEBUT."' AND DATE_ORDONNANCEMENT <= '".$DATE_FIN."'";
    }

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $cond_profil="";
    $cond_user="";
    if ($profil_id!=1) 
    {
      $cond_profil=" AND prof.PROFIL_ID=".$profil_id;
      $cond_user=" AND histo.USER_ID=".$user_id;
    }
    // get instition d'affectation de personne connecté
    $user=$this->getBindParms('aff.INSTITUTION_ID,inst_institutions.CODE_INSTITUTION,inst_institutions.DESCRIPTION_INSTITUTION','user_affectaion aff JOIN inst_institutions ON inst_institutions.INSTITUTION_ID=aff.INSTITUTION_ID','USER_ID='.$user_id,'INSTITUTION_ID');
    $data['institutions_user'] = $this->ModelPs->getRequete($callpsreq,$user);

    $institution=' AND exec.INSTITUTION_ID IN(SELECT INSTITUTION_ID FROM user_affectaion WHERE USER_ID='.$user_id.')';

    // get nombre ordonancement à faire inferieur a 500.000.000
    $ordon_Afaire = "SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 ".$cond_profil." AND dc.MOUVEMENT_DEPENSE_ID=4 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID=14 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$institution.$condition."";

    $ordon_Afaire = 'CALL getTable("'.$ordon_Afaire.'");';
    $ordon_Afaire = $this->ModelPs->getRequete($ordon_Afaire);
    $ordon_Afaire = (!empty($ordon_Afaire)) ? count($ordon_Afaire) : 0 ;
    $data['get_ordon_Afaire'] = number_format($ordon_Afaire,'0',',',' ');

    // get nombre ordonancement à faire superieur a 500.000.000    
    $nbr_ordon_Afaire_sup = "SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE 1 ".$cond_profil." AND dc.MOUVEMENT_DEPENSE_ID=4 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID =15 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$institution.$condition."";
    
    $nbr_ordon_Afaire_sup='CALL getTable("'.$nbr_ordon_Afaire_sup.'");';
    $nbr_ordon_Afaire_sup=$this->ModelPs->getRequete($nbr_ordon_Afaire_sup);
    $nbr_ordon_Afaire_sup=(!empty($nbr_ordon_Afaire_sup)) ? count($nbr_ordon_Afaire_sup) : 0 ;
    $data['get_ordon_Afaire_sup'] = number_format($nbr_ordon_Afaire_sup,'0',',',' ');

    // get nombre ordonancement deja fait
    $nbr_ordon_deja_fait="SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=execdet.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID>14 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID<>15 AND ebtd.ETAPE_DOUBLE_COMMANDE_ID<>42 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) ".$institution.$condition."";

    $nbr_ordon_deja_fait = 'CALL getTable("'.$nbr_ordon_deja_fait.'");';
    $nbr_ordon_deja_fait = $this->ModelPs->getRequete($nbr_ordon_deja_fait);
    $nbr_ordon_deja_fait = (!empty($nbr_ordon_deja_fait)) ? count($nbr_ordon_deja_fait) : 0 ;
    $data['get_ordon_deja_fait'] = number_format($nbr_ordon_deja_fait,'0',',',' ');
    
    // get nbr des bons a transmission au service prise en charge au niveau direction budget
    $bord_spe='SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID LEFT JOIN devise_type_hist dev_hist ON dev_hist.DEVISE_TYPE_HISTO_ID=det.DEVISE_TYPE_HISTO_LIQUI_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=16 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)'.$condition;
    
    $bord_spe = 'CALL getTable("'.$bord_spe.'");';
    $nbre_bord_spe = $this->ModelPs->getRequete($bord_spe);
    $nbre_bord_spe = (!empty($nbre_bord_spe)) ? count($nbre_bord_spe) : 0 ;
    $data['get_bord_spe']= number_format($nbre_bord_spe,'0',',',' ');

    // get nbr des bordereaux deja transmis de direction budget vers service prise en charge
    $bord_deja_spe= 'SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) FROM execution_budgetaire_bordereau_transmission bord_trans JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bon_titre.BORDEREAU_TRANSMISSION_ID = bord_trans.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_titre_decaissement ebtd ON ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID = ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = det.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID = exec.INSTITUTION_ID LEFT JOIN devise_type_hist dev_hist ON dev_hist.DEVISE_TYPE_HISTO_ID = det.DEVISE_TYPE_HISTO_LIQUI_ID JOIN devise_type dev ON exec.DEVISE_TYPE_ID=dev.DEVISE_TYPE_ID WHERE 1 AND bon_titre.TYPE_DOCUMENT_ID = 1 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)'.$condition;

    $bord_deja_spe = 'CALL getTable("'.$bord_deja_spe.'");';
    $nbre_bord_deja_spe = $this->ModelPs->getRequete($bord_deja_spe);
    $nbre_bord_deja_spe = (!empty($nbre_bord_deja_spe)) ? count($nbre_bord_deja_spe) : 0 ;
    $data['get_bord_deja_spe']= number_format($nbre_bord_deja_spe,'0',',',' ');

    // get nombre ordonancement à envoyer au cabinet du ministre
    $nbr_ordon_AuCabinet = "SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID LEFT JOIN devise_type_hist dev_hist ON dev_hist.DEVISE_TYPE_HISTO_ID = det.DEVISE_TYPE_HISTO_LIQUI_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=34 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$condition;
    
    $nbr_ordon_AuCabinet = 'CALL getTable("'.$nbr_ordon_AuCabinet.'");';
    $nbr_ordon_AuCabinet = $this->ModelPs->getRequete($nbr_ordon_AuCabinet);
    $nbr_ordon_AuCabinet = (!empty($nbr_ordon_AuCabinet)) ? count($nbr_ordon_AuCabinet) : 0 ;
    $data['get_ordon_AuCabinet'] = number_format($nbr_ordon_AuCabinet,'0',',',' ');

    $nbr_ordon_BorderCabinet = "SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID LEFT JOIN devise_type_hist dev_hist ON dev_hist.DEVISE_TYPE_HISTO_ID = det.DEVISE_TYPE_HISTO_LIQUI_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=35 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$condition;
    
    $nbr_ordon_BorderCabinet = 'CALL getTable("'.$nbr_ordon_BorderCabinet.'");';
    $nbr_ordon_BorderCabinet = $this->ModelPs->getRequete($nbr_ordon_BorderCabinet);
    $nbr_ordon_BorderCabinet = (!empty($nbr_ordon_BorderCabinet)) ? count($nbr_ordon_BorderCabinet) : 0 ;
    $data['get_ordon_BorderCabinet'] = number_format($nbr_ordon_BorderCabinet,'0',',',' ');

    $nbr_ordon_BOnCED ="SELECT DISTINCT(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=ebtd.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE ebtd.ETAPE_DOUBLE_COMMANDE_ID=36 ".$cond_profil." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$condition;
    
    $nbr_ordon_BOnCED = 'CALL getTable("'.$nbr_ordon_BOnCED.'");';
    $nbr_ordon_BOnCED = $this->ModelPs->getRequete($nbr_ordon_BOnCED);
    $nbr_ordon_BOnCED = (!empty($nbr_ordon_BOnCED)) ? count($nbr_ordon_BOnCED) : 0 ;
    $data['get_ordon_BonCED'] = number_format($nbr_ordon_BOnCED,'0',',',' ');
    //Annulation Ordonnancement
    $etape_reject_ordo="SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=ebtd.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = ebtd.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON dc.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=4 ".$cond_profil." AND ebtd.ETAPE_DOUBLE_COMMANDE_ID=40 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$condition;

    $nbre_etape_reject_ordo = 'CALL getTable("'.$etape_reject_ordo.'");';
    $nbre_etape_reject_ordo = $this->ModelPs->getRequete($nbre_etape_reject_ordo);
    $nbre_etape_reject_ordo = (!empty($nbre_etape_reject_ordo)) ? count($nbre_etape_reject_ordo) : 0 ;
    $data['get_etape_reject_ordo'] = number_format($nbre_etape_reject_ordo,'0',',',' ');
    return $data;
  }
  
  function detail_new($EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)
  {
    $session  = \Config\Services::session();
    if(empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      return redirect('Login_Ptba');
    }

    $requetedebase="
    SELECT ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.TYPE_ENGAGEMENT_ID TYPE_ENGAGEMENT_ID,exec.DEVISE_TYPE_ID,exec.LIQUIDATION_TYPE_ID,prestataire.NOM_PRESTATAIRE, prestataire.PRENOM_PRESTATAIRE, type_beneficiaire.DESC_TYPE_BENEFICIAIRE, det.EXECUTION_BUDGETAIRE_DETAIL_ID,det.EXECUTION_BUDGETAIRE_ID ,exec.ENG_BUDGETAIRE, exec.ENG_JURIDIQUE, det.MONTANT_LIQUIDATION,exec.COMMENTAIRE, det.MONTANT_ORDONNANCEMENT,ebtd.MONTANT_PAIEMENT,ebtd.MONTANT_DECAISSEMENT,exec.TRIMESTRE_ID, exec.NUMERO_BON_ENGAGEMENT, ebtd.TITRE_DECAISSEMENT NUMERO_TITRE_DECAISSEMNT, exec.DATE_DEMANDE, exec.DATE_ENG_JURIDIQUE,ebtd.DATE_PAIEMENT,ebtd.DATE_DECAISSEMENT DATE_DECAISSENMENT,det.DATE_LIQUIDATION, det.DATE_ORDONNANCEMENT, ebtd.DATE_PAIEMENT, ebtd.DATE_DECAISSEMENT DATE_DECAISSENMENT, det.DATE_PRISE_CHARGE, ebtd.DATE_SIGNATURE_TD_MINISTRE, NOM_BANQUE,COMPTE_BANCAIRE, det.MOTIF_LIQUIDATION, ebtd.MOTIF_PAIEMENT, ebtd.DATE_SIGNATURE_TD_DIR_COMPTABILITE_PUBLIQUE_TRESOR, ebtd.DATE_SIGNATURE_TD_DIR_GENERALE_FINANCE_PUBLIQUE, det.MOTANT_FACTURE, ebtd.DATE_ELABORATION_TD, det.MONTANT_PRELEVEMENT_FISCALES, det.EXONERATION, devise.DESC_DEVISE_TYPE, det.TITRE_CREANCE, det.MONTANT_CREANCE, exec.PATH_BON_ENGAGEMENT, ebtd.PATH_TITRE_DECAISSEMENT, suppl.PATH_PV_ATTRIBUTION,suppl.PATH_PPM, suppl.PATH_CONTRAT, det.PATH_PV_RECEPTION_LIQUIDATION PATH_PV_RECEPTION, det.PATH_FACTURE_LIQUIDATION PATH_FACTURE, det.PATH_NOTE_A_LA_DCP PATH_LETTRE_OTB, suppl.PATH_LETTRE_TRANSMISSION, suppl.PATH_LISTE_PAIE, suppl.PATH_AVIS_DNCMP, det.DATE_CREANCE,det.COUR_DEVISE, exec.ENG_BUDGETAIRE_DEVISE, exec.ENG_JURIDIQUE_DEVISE, det.MONTANT_LIQUIDATION_DEVISE, det.MONTANT_ORDONNANCEMENT_DEVISE,ebtd.MONTANT_PAIEMENT_DEVISE, ebtd.MONTANT_DECAISSEMENT_DEVISE, suppl.DATE_DEBUT_CONTRAT, suppl.DATE_FIN_CONTRAT, det.DATE_LIVRAISON_CONTRAT, exec.MARCHE_PUBLIQUE, exec.USER_ID, liq_type.DESCRIPTION_LIQUIDATION, type_engagement.DESC_TYPE_ENGAGEMENT, type_marche.DESCR_MARCHE,devise_decais.DESC_DEVISE_TYPE AS DESC_DEVISE_TYPE_DEC,DESC_BUDGETAIRE_TYPE_DOCUMENT,ANNEE_DESCRIPTION,INTRODUCTION_NOTE,DATE_LIVRAISON_CONTRAT,PATH_FACTURE_LIQUIDATION,PATH_PV_RECEPTION_LIQUIDATION,DESC_MODELE,PATH_MODELE_PAIEMENT
    FROM execution_budgetaire_titre_decaissement ebtd JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = ebtd.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl suppl ON suppl.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=ebtd.EXECUTION_BUDGETAIRE_DETAIL_ID LEFT JOIN banque ON banque.BANQUE_ID = ebtd.BANQUE_ID LEFT JOIN taux_tva ON taux_tva.TAUX_TVA_ID = det.TAUX_TVA_ID LEFT JOIN liquidation_type liq_type ON liq_type.LIQUIDATION_TYPE_ID = exec.LIQUIDATION_TYPE_ID LEFT JOIN type_engagement ON type_engagement.TYPE_ENGAGEMENT_ID = exec.TYPE_ENGAGEMENT_ID LEFT JOIN type_marche ON type_marche.ID_TYPE_MARCHE = suppl.ID_TYPE_MARCHE LEFT JOIN devise_type devise ON devise.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID LEFT JOIN devise_type devise_decais ON devise_decais.DEVISE_TYPE_ID=ebtd.DEVISE_TYPE_ID_RETRAIT LEFT JOIN type_beneficiaire ON type_beneficiaire.TYPE_BENEFICIAIRE_ID = suppl.TYPE_BENEFICIAIRE_ID LEFT JOIN prestataire ON prestataire.PRESTATAIRE_ID = suppl.PRESTATAIRE_ID JOIN budgetaire_type_document typ_doc ON typ_doc.BUDGETAIRE_TYPE_DOCUMENT_ID = suppl.BUDGETAIRE_TYPE_DOCUMENT_ID JOIN annee_budgetaire annee ON annee.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID LEFT JOIN modele ON modele.MODELE_ID=suppl.MODELE_ID WHERE MD5(ebtd.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID)='".$EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID."'";

    $get_info = $this->ModelPs->getRequeteOne('CALL getTable("' .$requetedebase. '")');

    $requetedebaseEBET = "SELECT act.DESC_PAP_ACTIVITE,act.PAP_ACTIVITE_ID,tache.PTBA_TACHE_ID,tache.DESC_TACHE,ebet.QTE QTE_RACCROCHE,ebet.EST_SOUS_TACHE,inst.INSTITUTION_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,inst.TYPE_INSTITUTION_ID, actions.CODE_ACTION,actions.LIBELLE_ACTION,st.SOUS_TUTEL_ID, st.CODE_SOUS_TUTEL,st.DESCRIPTION_SOUS_TUTEL, progr.CODE_PROGRAMME,progr.INTITULE_PROGRAMME,tache.CODES_PROGRAMMATIQUE,ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID,ligne.CODE_NOMENCLATURE_BUDGETAIRE,ligne.LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE,tache.BUDGET_UTILISE_T1,tache.BUDGET_UTILISE_T2,tache.BUDGET_UTILISE_T3,tache.BUDGET_T1 AS T1, tache.BUDGET_T2 AS T2, tache.BUDGET_T3 AS T3, tache.BUDGET_T4 AS T4,tache.BUDGET_ANNUEL,tache.BUDGET_RESTANT_T1,tache.BUDGET_RESTANT_T2,tache.BUDGET_RESTANT_T3,tache.BUDGET_RESTANT_T4, ebet.EST_SOUS_TACHE, ebet.EST_FINI_TACHE, ebet.RESULTAT_ATTENDUS FROM execution_budgetaire_execution_tache ebet JOIN ptba_tache tache ON tache.PTBA_TACHE_ID = ebet.PTBA_TACHE_ID LEFT JOIN pap_activites act ON act.PAP_ACTIVITE_ID = tache.PAP_ACTIVITE_ID LEFT JOIN inst_institutions_programmes progr ON progr.PROGRAMME_ID=tache.PROGRAMME_ID LEFT JOIN inst_institutions_actions actions ON actions.ACTION_ID=tache.ACTION_ID LEFT JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=tache.CODE_NOMENCLATURE_BUDGETAIRE_ID LEFT JOIN inst_institutions_sous_tutel st ON st.SOUS_TUTEL_ID = tache.SOUS_TUTEL_ID LEFT JOIN inst_institutions inst ON inst.INSTITUTION_ID=tache.INSTITUTION_ID WHERE EXECUTION_BUDGETAIRE_ID=".$get_info['EXECUTION_BUDGETAIRE_ID'];
    $data['get_infoEBET'] = $this->ModelPs->getRequete('CALL getTable("' .$requetedebaseEBET. '")');


    // credit  vote par ligne
    $getMoneyVote = 'SELECT SUM(BUDGET_T1) as t1,SUM(BUDGET_T2) as t2,SUM(BUDGET_T3) as t3,SUM(BUDGET_T4) as t4,SUM(BUDGET_ANNUEL) as votes FROM ptba_tache WHERE CODE_NOMENCLATURE_BUDGETAIRE_ID='.$data['get_infoEBET'][0]->CODE_NOMENCLATURE_BUDGETAIRE_ID;
    $MoneyVote = $this->ModelPs->getRequeteOne('CALL getTable("' .$getMoneyVote. '")');
    $montant=$MoneyVote['t1']+$MoneyVote['t2']+$MoneyVote['t3']+$MoneyVote['t4'];

    //get tous les retenues joa-kevin.iradukunda@mediabox.bi
    $get_retenues = "SELECT det_ret.MONTANT_RETENU,typ_ret.CODE_RETENU,typ_ret.LIBELLE FROM exec_budget_tache_detail_retenu_prise_charge det_ret JOIN type_retenu_prise_charge typ_ret ON det_ret.TYPE_RETENU_PRISE_CHARGE_ID = typ_ret.TYPE_RETENU_PRISE_CHARGE_ID WHERE MD5(det_ret.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) = '".$get_info['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']."'";
    $retenues = 'CALL getTable("'.$get_retenues.'");';
    $retenues = $this->ModelPs->getRequete($retenues);
    $get_info['retenues'] = $retenues;

    //montant transferes de la ligne
    $trans="SELECT SUM(MONTANT_TRANSFERT) AS total,CODE_NOMENCLATURE_BUDGETAIRE_ID FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_TRANSFERT WHERE ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$data['get_infoEBET'][0]->CODE_NOMENCLATURE_BUDGETAIRE_ID;
    $trans='CALL getTable("'.$trans.'");';
    $trans=$this->ModelPs->getRequeteOne($trans);

    //montant récu sur la ligne
    $recu="SELECT SUM(MONTANT_RECEPTION) AS total,CODE_NOMENCLATURE_BUDGETAIRE_ID FROM transfert_historique_transfert trans JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=trans.PTBA_TACHE_ID_RECEPTION WHERE ptba.CODE_NOMENCLATURE_BUDGETAIRE_ID=".$data['get_infoEBET'][0]->CODE_NOMENCLATURE_BUDGETAIRE_ID;
    $recu='CALL getTable("'.$recu.'");';
    $recu=$this->ModelPs->getRequeteOne($recu);

    $MONTANT_TRANSFERT =
    $CREDIT_APRES_TRANSFERT =
    $MONTANT_TRANSFERT_RESTE = (floatval($trans['total']) - floatval($recu['total']));

    if($MONTANT_TRANSFERT_RESTE >= 0)
    {
      $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE;
    }
    else
    {
      $MONTANT_TRANSFERT=$MONTANT_TRANSFERT_RESTE*(-1);
    }

    $CREDIT_APRES_TRANSFERT=(floatval($montant) - floatval($trans['total'])) + floatval($recu['total']);

    if($CREDIT_APRES_TRANSFERT < 0){
      $CREDIT_APRES_TRANSFERT = $CREDIT_APRES_TRANSFERT*(-1);
    }
    if($trans['CODE_NOMENCLATURE_BUDGETAIRE_ID']==$recu['CODE_NOMENCLATURE_BUDGETAIRE_ID'])
    {
      $MONTANT_TRANSFERT = $trans['total'];
      $CREDIT_APRES_TRANSFERT = floatval($montant);
    }
    $get_info['MONTANT_TRANSFERT']=$MONTANT_TRANSFERT;
    $get_info['CREDIT_APRES_TRANSFERT'] =$CREDIT_APRES_TRANSFERT;

    $data['get_info']=$get_info;
    $data['montantvote']=$montant;
    return $data;
  }
  
  function get_filtre($ANNEE_BUDGETAIRE_ID=0,$INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    $filtre='';
    if($ANNEE_BUDGETAIRE_ID!=0)
    {
      $filtre.=' AND exec.ANNEE_BUDGETAIRE_ID='.$ANNEE_BUDGETAIRE_ID;
    }

    if($INSTITUTION_ID!=0)
    {
      $filtre.=' AND exec.INSTITUTION_ID='.$INSTITUTION_ID;
      if($SOUS_TUTEL_ID!=0) 
      {
        $filtre.=' AND exec.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID;
      }
    }

    if($DATE_DEBUT!=0 AND $DATE_FIN==0)
    {
      $filtre.=' AND exec.DATE_DEMANDE >= "'.$DATE_DEBUT.'"';
    }

    if($DATE_DEBUT!=0 AND $DATE_FIN!=0)
    {
      $filtre.=' AND exec.DATE_DEMANDE >= "'.$DATE_DEBUT.'" AND exec.DATE_DEMANDE <= "'.$DATE_FIN.'"';
    }
    return $filtre;
  }
  
  //function pour compter les engagement sur la phase paiement    
  function count_paiement($ANNEE_BUDGETAIRE_ID=0,$INSTITUTION_ID=0,$SOUS_TUTEL_ID=0,$DATE_DEBUT=0,$DATE_FIN=0)
  {
    $session=\Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba');
    }

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";

    $filtre=$this->get_filtre($ANNEE_BUDGETAIRE_ID,$INSTITUTION_ID,$SOUS_TUTEL_ID,$DATE_DEBUT,$DATE_FIN);

    /* Debut Réception par Prise en charge */
    $recep_prise_charge="SELECT COUNT(DISTINCT(bord_trans.BORDEREAU_TRANSMISSION_ID)) AS RECEPCHARGE FROM execution_budgetaire_bordereau_transmission bord_trans JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID WHERE ID_ORIGINE_DESTINATION=1 AND STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1, 2)".$filtre;

    $get_RECEPCHARGE = 'CALL getTable("'.$recep_prise_charge.'");';
    $get_RECEPCHARGE = $this->ModelPs->getRequeteOne($get_RECEPCHARGE);
    $data['recep_prise_charge'] = number_format($get_RECEPCHARGE['RECEPCHARGE'],'0',',',' ');
    /* Fin Réception par Prise en charge */

    /* Debut Déjà réceptionnés par Prise en charge */
    $deja_recep_prise_charge="SELECT COUNT(DISTINCT(bord_trans.BORDEREAU_TRANSMISSION_ID)) AS DEJARECEPCHARGE FROM execution_budgetaire_bordereau_transmission bord_trans JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID WHERE ID_ORIGINE_DESTINATION=1 AND STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=2 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $get_deja_recep_prise_charge = 'CALL getTable("'.$deja_recep_prise_charge.'");';
    $get_deja_recep_prise_charge = $this->ModelPs->getRequeteOne($get_deja_recep_prise_charge);
    $data['deja_recep_prise_charge'] = number_format($get_deja_recep_prise_charge['DEJARECEPCHARGE'],'0',',',' ');
    /* Fin Déjà réceptionnés par Prise en charge */

    // Debut get nombre des bons de commande avant OBR
    $nbr_av_obr="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=32 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";
    $nbr_av_obr = 'CALL getTable("'.$nbr_av_obr.'");';
    $nbr_av_obr = $this->ModelPs->getRequete($nbr_av_obr);
    $nbr_av_obr = (!empty($nbr_av_obr)) ? count($nbr_av_obr) : 0 ;
    $data['get_nbr_av_obr'] = number_format($nbr_av_obr,'0',',',' ');
    // Fin get nombre des bons de commande avant OBR

    //Debut Réception OBR
    $requetedebase="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=18 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $nbre_recep_obr ='CALL getTable("'.$requetedebase.'");';
    $nbre_recep_obr = $this->ModelPs->getRequete($nbre_recep_obr);
    $nbre_recep_obr = (!empty($nbre_recep_obr)) ? count($nbre_recep_obr) : 0 ;
    $data['get_recep_obr'] = number_format($nbre_recep_obr,'0',',',' ');
    //Fin Réception OBR

    //Debut get nombre des bons de commande avant prise en charge
    $nbr_av_pc="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=33 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";
    $nbr_av_pc = 'CALL getTable("'.$nbr_av_pc.'");';
    $nbr_av_pc = $this->ModelPs->getRequete($nbr_av_pc);
    $nbr_av_pc = (!empty($nbr_av_pc)) ? count($nbr_av_pc) : 0 ;
    $data['get_nbr_av_pc'] = number_format($nbr_av_pc,'0',',',' ');
    //Fin get nombre des bons de commande avant prise en charge

    //Debut Prise en charge
    $prise_charge="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=19 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND (exec_detail.USER_AFFECTE_ID=".$user_id." OR exec_detail.USER_AFFECTE_ID='' OR exec_detail.USER_AFFECTE_ID IS NULL)";
    $nbre_prise_charge ='CALL getTable("'.$prise_charge.'");';
    $nbre_prise_charge = $this->ModelPs->getRequete($nbre_prise_charge);
    $nbre_prise_charge = (!empty($nbre_prise_charge)) ? count($nbre_prise_charge) : 0 ;
    $data['get_prise_charge'] = number_format($nbre_prise_charge,'0',',',' ');
    //Fin Prise en charge

    //Debut Prise en charge - Correction
    $prise_charge_corr="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail  ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID = exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=39 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";
    $nbre_prise_charge_corr ='CALL getTable("'.$prise_charge_corr.'");';
    $nbre_prise_charge_corr = $this->ModelPs->getRequete($nbre_prise_charge_corr);
    $nbre_prise_charge_corr = (!empty($nbre_prise_charge_corr)) ? count($nbre_prise_charge_corr) : 0 ;
    $data['get_prise_charge_corr'] = number_format($nbre_prise_charge_corr,'0',',',' ');
    //Fin Prise en charge - Correction

    //Debut Annulation Prise en charge
    $etape_reject_pc="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=41 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $nbre_etape_reject_pc ='CALL getTable("'.$etape_reject_pc.'");';
    $nbre_etape_reject_pc = $this->ModelPs->getRequete($nbre_etape_reject_pc);
    $nbre_etape_reject_pc = (!empty($nbre_etape_reject_pc)) ? count($nbre_etape_reject_pc) : 0;
    $data['get_etape_reject_pc'] = number_format($nbre_etape_reject_pc,'0',',',' ');
    //Fin Annulation Prise en charge

    //Debut Décision de l'étape de correction
    $etape_corr="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=38 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $nbre_etape_corr ='CALL getTable("'.$etape_corr.'");';
    $nbre_etape_corr = $this->ModelPs->getRequete($nbre_etape_corr);
    $nbre_etape_corr = (!empty($nbre_etape_corr)) ? count($nbre_etape_corr) : 0;
    $data['get_etape_corr'] = number_format($nbre_etape_corr,'0',',',' ');
    //Fin Décision de l'étape de correction

    // Debut Etablissement du titre
    $etab_titre="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID = td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=20 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";
    $nbre_etab_titre ='CALL getTable("'.$etab_titre.'");';
    $nbre_etab_titre = $this->ModelPs->getRequete($nbre_etab_titre);
    $nbre_etab_titre = (!empty($nbre_etab_titre)) ? count($nbre_etab_titre) : 0 ;
    $data['get_etab_titre'] = number_format($nbre_etab_titre,'0',',',' ');
    // Fin Etablissement du titre

    //Debut reception des td a corriger
    $recep_td_corriger="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=47 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $nbre_recep_td_corriger ='CALL getTable("'.$recep_td_corriger.'");';
    $nbre_recep_td_corriger = $this->ModelPs->getRequete($nbre_recep_td_corriger);
    $nbre_recep_td_corriger = (!empty($nbre_recep_td_corriger)) ? count($nbre_recep_td_corriger) : 0;
    $data['get_recep_td_corriger'] = number_format($nbre_recep_td_corriger,'0',',',' ');
    //Debut reception des td a corriger

    // Debut Correction des titres
    $etab_titre_corr="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=37 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $nbre_etab_titre_corr ='CALL getTable("'.$etab_titre_corr.'");';
    $nbre_etab_titre_corr = $this->ModelPs->getRequete($nbre_etab_titre_corr);
    $nbre_etab_titre_corr = (!empty($nbre_etab_titre_corr)) ? count($nbre_etab_titre_corr) : 0 ;
    $data['get_etab_titre_corr'] = number_format($nbre_etab_titre_corr,'0',',',' ');
    // Fin Correction des titres

    // Debut Transimission des titres a la direction de la comptabilite
    $bord_dc = 'SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,td.TITRE_DECAISSEMENT,dev.DESC_DEVISE_TYPE,det.MONTANT_ORDONNANCEMENT,inst.DESCRIPTION_INSTITUTION FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN devise_type dev ON exec.DEVISE_TYPE_ID=dev.DEVISE_TYPE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=21 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)';
    $bord_dc = 'CALL getTable("'.$bord_dc.'");';
    $nbre_bord_dc = $this->ModelPs->getRequete($bord_dc);
    $nbre_bord_dc = (!empty($nbre_bord_dc)) ? count($nbre_bord_dc) : 0 ;
    $data['get_bord_dc']= number_format($nbre_bord_dc,'0',',',' ');
    // Fin Transimission des titres a la direction de la comptabilite

    // Debut Titres deja trasmis a la direction de la comptabilite
    $bord_deja_dc='SELECT DISTINCT(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) FROM execution_budgetaire_bordereau_transmission_bon_titre bon JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_bordereau_transmission ebbtn ON ebbtn.BORDEREAU_TRANSMISSION_ID=bon.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE bon.TYPE_DOCUMENT_ID=2 AND bon.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID=1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)';
    $bord_deja_dc = 'CALL getTable("'.$bord_deja_dc.'");';
    $nbre_bord_deja_dc = $this->ModelPs->getRequete($bord_deja_dc);
    $nbre_bord_deja_dc = (!empty($nbre_bord_deja_dc)) ? count($nbre_bord_deja_dc) : 0 ;
    $data['get_bord_deja_dc']= number_format($nbre_bord_deja_dc,'0',',',' ');
    // Fin Titres deja trasmis a la direction de la comptabilite

    /*Debut A réceptionner- Directeur de la comptabilité */
    $recep_dir_comptable  ="SELECT COUNT(DISTINCT(bord_trans.BORDEREAU_TRANSMISSION_ID)) AS nbr FROM execution_budgetaire_bordereau_transmission bord_trans JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID WHERE ID_ORIGINE_DESTINATION=2 AND STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1, 2)".$filtre;

    $get_recep_dir_comptable = 'CALL getTable("'.$recep_dir_comptable.'");';
    $get_recep_dir_comptable = $this->ModelPs->getRequeteOne($get_recep_dir_comptable);
    $data['recep_dir_comptable'] = number_format($get_recep_dir_comptable['nbr'],'0',',',' ');
    /*Fin A réceptionner- Directeur de la comptabilité */

    /*Debut Déjà réceptionnés - Directeur de la comptabilité */
    $deja_recep_dir_comptable = "SELECT COUNT(DISTINCT(bord_trans.BORDEREAU_TRANSMISSION_ID)) AS nbr FROM execution_budgetaire_bordereau_transmission bord_trans JOIN execution_budgetaire_bordereau_transmission_bon_titre bon_titre ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID WHERE ID_ORIGINE_DESTINATION=2 AND STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=2 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$filtre;

    $get_deja_recep_dir_comptable = 'CALL getTable("'.$deja_recep_dir_comptable.'");';
    $get_deja_recep_dir_comptable = $this->ModelPs->getRequeteOne($get_deja_recep_dir_comptable);
    $data['deja_recep_dir_comptable'] = number_format($get_deja_recep_dir_comptable['nbr'],'0',',',' ');
    /*Fin Déjà réceptionnés - Directeur de la comptabilité */

    // Debut Signature du titre par le directieur comptable
    $sign_dir_compt="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=23 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)".$filtre;

    $nbre_sign_dir_compt ='CALL getTable("'.$sign_dir_compt.'");';
    $nbre_sign_dir_compt = $this->ModelPs->getRequete($nbre_sign_dir_compt);
    $nbre_sign_dir_compt = (!empty($nbre_sign_dir_compt)) ? count($nbre_sign_dir_compt) : 0 ;
    $data['get_sign_dir_compt'] = number_format($nbre_sign_dir_compt,'0',',',' ');
    // Fin Signature du titre par le directieur comptable

    //Debut Signature du titre par le dgfp
    $sign_dir_dgfp="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=24 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $nbre_sign_dir_dgfp ='CALL getTable("'.$sign_dir_dgfp.'");';
    $nbre_sign_dir_dgfp = $this->ModelPs->getRequete($nbre_sign_dir_dgfp);
    $nbre_sign_dir_dgfp = (!empty($nbre_sign_dir_dgfp)) ? count($nbre_sign_dir_dgfp) : 0 ;
    $data['get_sign_dir_dgfp'] = number_format($nbre_sign_dir_dgfp,'0',',',' ');
    //Fin Signature du titre par le dgfp

    //Debut Signature du titre par le ministre
    $sign_ministre="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=dc.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=5 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=25 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $nbre_sign_ministre ='CALL getTable("'.$sign_ministre.'");';
    $nbre_sign_ministre = $this->ModelPs->getRequete($nbre_sign_ministre);
    $nbre_sign_ministre = (!empty($nbre_sign_ministre)) ? count($nbre_sign_ministre) : 0 ;
    $data['get_sign_ministre'] = number_format($nbre_sign_ministre,'0',',',' ');
    //Fin Signature du titre par le ministre

    // Debut titres de decaissement a valide
    $titre_valide='SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=26 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)';
    $titre_valide = 'CALL getTable("'.$titre_valide.'");';
    $nbre_titre_valide = $this->ModelPs->getRequete($titre_valide);
    $nbre_titre_valide = (!empty($nbre_titre_valide)) ? count($nbre_titre_valide) : 0 ;
    $data['get_titre_valide']= number_format($nbre_titre_valide,'0',',',' ');
    // Fin titres de decaissement a valide

    // Debut titres de decaissement deja valide
    $titre_termine='SELECT DISTINCT(td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID),exec.EXECUTION_BUDGETAIRE_ID,det.EXECUTION_BUDGETAIRE_DETAIL_ID,exec.ENG_BUDGETAIRE,exec.ENG_JURIDIQUE,det.MONTANT_LIQUIDATION,det.MONTANT_ORDONNANCEMENT,td.MONTANT_PAIEMENT,td.ETAPE_DOUBLE_COMMANDE_ID,td.TITRE_DECAISSEMENT FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID>=27 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2) AND td.ETAPE_DOUBLE_COMMANDE_ID NOT IN(37,38,39,40,41,42)'.$filtre;
    $titre_termine = 'CALL getTable("'.$titre_termine.'");';
    $nbre_titre_termine = $this->ModelPs->getRequete($titre_termine);
    $nbre_titre_termine = (!empty($nbre_titre_termine)) ? count($nbre_titre_termine) : 0 ;
    $data['get_titre_termine']= number_format($nbre_titre_termine,'0',',',' ');
    // Fin titres de decaissement deja valide

    // Debut titres de decaissement a trasmetre a la BRB
    $bord_brb = 'SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,exec.EXECUTION_BUDGETAIRE_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=27 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)'.$filtre;
    $bord_brb = 'CALL getTable("'.$bord_brb.'");';
    $nbre_bord_brb = $this->ModelPs->getRequete($bord_brb);
    $nbre_bord_brb = (!empty($nbre_bord_brb)) ? count($nbre_bord_brb) : 0 ;
    $data['get_bord_brb']= number_format($nbre_bord_brb,'0',',',' ');
    // Fin titres de decaissement a trasmetre a la BRB

    // Debut titres de decaissement deja trasmetre a la BRB
    $bord_deja_trans_brb='SELECT DISTINCT bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_bordereau_transmission ebbtn ON ebbtn.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID = td.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID = exec.INSTITUTION_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE bon_titre.TYPE_DOCUMENT_ID = 2 AND bon_titre.STATUT_DOCUMENT_BORDEREAU_TRANSMISSION_ID IN (1,2) AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)'.$filtre;
    $bord_deja_trans_brb = 'CALL getTable("'.$bord_deja_trans_brb.'");';
    $nbre_bord_deja_trans_brb = $this->ModelPs->getRequete($bord_deja_trans_brb);
    $nbre_bord_deja_trans_brb = (!empty($nbre_bord_deja_trans_brb)) ? count($nbre_bord_deja_trans_brb) : 0 ;
    $data['get_bord_deja_trans_brb']= number_format($nbre_bord_deja_trans_brb,'0',',',' ');
    // Fin titres de decaissement deja trasmetre a la BRB
    return $data;
  }


  //function pour compter les engagement sur la partie de decaissement
  function count_decaissement()
  {
    $session  = \Config\Services::session();
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    if(empty($user_id))
    {
      return redirect('Login_Ptba');
    }

    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND etp_prof.PROFIL_ID=".$profil_id;
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";

    /*Debut bordereux A réceptionner a la BRB */
    $recep_brb = "SELECT COUNT(DISTINCT(bord_trans.BORDEREAU_TRANSMISSION_ID)) AS nbr FROM execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_bordereau_transmission bord_trans ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE ID_ORIGINE_DESTINATION=3 AND STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=1 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $get_srecep_brb = 'CALL getTable("'.$recep_brb.'");';
    $get_srecep_brb = $this->ModelPs->getRequeteOne($get_srecep_brb);
    $data['recep_brb'] = number_format($get_srecep_brb['nbr'],'0',',',' ');
    /*Fin bordereux A réceptionner a la BRB */

    /*Debut bordereaux Déjà réceptionné a la BRB */
    $déjà_recep_brb = "SELECT COUNT(DISTINCT(bord_trans.BORDEREAU_TRANSMISSION_ID)) AS nbr FROM execution_budgetaire_bordereau_transmission_bon_titre bon_titre JOIN execution_budgetaire_bordereau_transmission bord_trans ON bord_trans.BORDEREAU_TRANSMISSION_ID=bon_titre.BORDEREAU_TRANSMISSION_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID=bon_titre.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID WHERE ID_ORIGINE_DESTINATION=3 AND STATUT_OPERATION_BORDEREAU_TRANSMISSION_ID=2 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $get_déjà_recep_brb = 'CALL getTable("'.$déjà_recep_brb.'");';
    $get_déjà_recep_brb = $this->ModelPs->getRequeteOne($get_déjà_recep_brb);
    $data['déjà_recep_brb'] = number_format($get_déjà_recep_brb['nbr'],'0',',',' ');
    /*Fin bordereaux Déjà réceptionné a la BRB */

    /* Debut controle des TD par BRB */
    $controle_brb = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=43 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $get_controle_brb = 'CALL getTable("'.$controle_brb.'");';
    $get_controle_brb = $this->ModelPs->getRequete($get_controle_brb);
    $data['controle_brb'] = number_format(count($get_controle_brb),'0',',',' ');
    /* Fin controle des TD par BRB */

    /* Debut controle des TD par BESD */
    $controle_besd = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_info_suppl info ON info.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire ligne ON ligne.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=exec.INSTITUTION_ID JOIN prestataire prest ON prest.PRESTATAIRE_ID=info.PRESTATAIRE_ID JOIN banque ON banque.BANQUE_ID=td.BANQUE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=44 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $get_controle_besd = 'CALL getTable("'.$controle_besd.'");';
    $get_controle_besd = $this->ModelPs->getRequete($get_controle_besd);
    $data['controle_besd'] = number_format(count($get_controle_besd),'0',',',' ');
    /* Fin controle des TD par BESD */

    /* Debut BRB/BESD Trasmis des TD a corriger au service prise en charge */
    $controle_a_corriger = "SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID IN (45,46) AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $get_controle_a_corriger = 'CALL getTable("'.$controle_a_corriger.'");';
    $get_controle_a_corriger = $this->ModelPs->getRequete($get_controle_a_corriger);
    $data['controle_a_corriger'] = number_format(count($get_controle_a_corriger),'0',',',' ');
    /* Fin BRB/BESD Trasmis des TD a corriger au service prise en charge */

    // Debut decaissement des TD
    $requetedebase="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail exec_detail ON exec_detail.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_etape_double_commande_profil etp_prof ON etp_prof.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire ligne_budg ON ligne_budg.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=29 ".$cond_prof." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $nbre_decais_a_faire = 'CALL getTable("'.$requetedebase.'");';
    $nbre_decais_a_faire = $this->ModelPs->getRequete($nbre_decais_a_faire);
    $nbre_decais_a_faire = (!empty($nbre_decais_a_faire)) ? count($nbre_decais_a_faire) : 0 ;
    $data['get_decais_afaire'] = number_format($nbre_decais_a_faire,'0',',',' ');
    // Fin decaissement des TD

    // Debut TD deja decaisser
    $decais_deja_fait="SELECT td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID FROM execution_budgetaire_titre_decaissement td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail execdet ON execdet.EXECUTION_BUDGETAIRE_DETAIL_ID=td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN execution_budgetaire_etape_double_commande dc ON dc.ETAPE_DOUBLE_COMMANDE_ID=td.ETAPE_DOUBLE_COMMANDE_ID JOIN inst_institutions_ligne_budgetaire inst ON inst.CODE_NOMENCLATURE_BUDGETAIRE_ID=exec.CODE_NOMENCLATURE_BUDGETAIRE_ID JOIN devise_type dev ON dev.DEVISE_TYPE_ID=exec.DEVISE_TYPE_ID WHERE dc.MOUVEMENT_DEPENSE_ID=6 AND td.ETAPE_DOUBLE_COMMANDE_ID=30 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID IN(1,2)";

    $nbre_decais_deja_faire = 'CALL getTable("'.$decais_deja_fait.'");';
    $nbre_decais_deja_faire = $this->ModelPs->getRequete($nbre_decais_deja_faire);
    $nbr_dej_fait = (!empty($nbre_decais_deja_faire)) ? count($nbre_decais_deja_faire) : 0 ;
    $data['get_decais_deja_fait'] = number_format($nbr_dej_fait,'0',',',' ');
    // Fin TD deja decaisser
    return $data;
  }

  //count liquidation salaire
  public function count_liquidation_salaire($value='')
  {
    $session  = \Config\Services::session();
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return redirect('Login_Ptba');
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";

    $requetedebase="SELECT COUNT(sous_titre.SOUS_TUTEL_ID) as nbre FROM execution_budgetaire exec JOIN execution_budgetaire_salaire_sous_titre sous_titre ON exec.EXECUTION_BUDGETAIRE_ID=sous_titre.EXECUTION_BUDGETAIRE_ID JOIN inst_institutions inst ON inst.INSTITUTION_ID=sous_titre.INSTITUTION_ID JOIN inst_institutions_sous_tutel st ON st.SOUS_TUTEL_ID=sous_titre.SOUS_TUTEL_ID WHERE 1 AND A_CORRIGER=1";
    $nbr_liqu_salaire = 'CALL getTable("'.$requetedebase.'");';
    $nbr_liqu_salaire = $this->ModelPs->getRequeteOne($nbr_liqu_salaire);
    $data['nbr_liqu_salaire'] = number_format($nbr_liqu_salaire['nbre'],'0',',',' ');

    $nbr_liq_a_valide="SELECT COUNT('exec.EXECUTION_BUDGETAIRE_ID') AS nbre FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID=11 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";
    $nbr_liq_a_valide = 'CALL getTable("'.$nbr_liq_a_valide.'");';
    $nbr_liq_a_valide = $this->ModelPs->getRequeteOne($nbr_liq_a_valide);
    $data['nbr_liq_a_valide'] = number_format($nbr_liq_a_valide['nbre'],'0',',',' ');

    $nbr_liq_deja_valide="SELECT COUNT('exec.EXECUTION_BUDGETAIRE_ID') AS nbre FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID>11 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";
    $nbr_liq_deja_valide = 'CALL getTable("'.$nbr_liq_deja_valide.'");';
    $nbr_liq_deja_valide = $this->ModelPs->getRequeteOne($nbr_liq_deja_valide);
    $data['nbr_liq_deja_valide'] = number_format($nbr_liq_deja_valide['nbre'],'0',',',' ');

    $nbr_liq_deja_fait="SELECT COUNT('exec.EXECUTION_BUDGETAIRE_ID') AS nbre FROM execution_budgetaire exec JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID WHERE td.ETAPE_DOUBLE_COMMANDE_ID>10 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";
    $nbr_liq_deja_fait = 'CALL getTable("'.$nbr_liq_deja_fait.'");';
    $nbr_liq_deja_fait = $this->ModelPs->getRequeteOne($nbr_liq_deja_fait);
    $data['nbr_liq_deja_fait'] = number_format($nbr_liq_deja_fait['nbre'],'0',',',' ');
    return $data;
  }

  //count ordonnancement salaire
  public function count_ordonnancement_salaire($value='')
  {
    $session  = \Config\Services::session();
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return redirect('Login_Ptba');
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";

    $requetedebase="SELECT COUNT(exec.EXECUTION_BUDGETAIRE_ID) as nbre FROM execution_budgetaire_titre_decaissement exec_td JOIN execution_budgetaire exec ON exec.EXECUTION_BUDGETAIRE_ID=exec_td.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=exec_td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN annee_budgetaire annee ON annee.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID JOIN type_salairie salarie ON salarie.TYPE_SALAIRE_ID=exec.TYPE_SALAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID WHERE exec_td.ETAPE_DOUBLE_COMMANDE_ID=14 AND EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";
    $nbr_ordo_salaire = 'CALL getTable("'.$requetedebase.'");';
    $nbr_ordo_salaire = $this->ModelPs->getRequeteOne($nbr_ordo_salaire);
    $data['nbr_ordo_salaire'] = number_format($nbr_ordo_salaire['nbre'],'0',',',' ');

    $nbr_ordo_deja_fait="SELECT COUNT(exec.EXECUTION_BUDGETAIRE_ID) as nbre FROM execution_budgetaire_titre_decaissement exec_td JOIN execution_budgetaire exec ON exec_td.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_tache_detail det ON det.EXECUTION_BUDGETAIRE_DETAIL_ID=exec_td.EXECUTION_BUDGETAIRE_DETAIL_ID JOIN type_salairie salarie ON salarie.TYPE_SALAIRE_ID=exec.TYPE_SALAIRE_ID JOIN annee_budgetaire annee ON annee.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID JOIN categorie_salaire categ ON categ.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID WHERE exec_td.ETAPE_DOUBLE_COMMANDE_ID>14 AND exec_td.ETAPE_DOUBLE_COMMANDE_ID<>15 AND EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3  AND IS_TD_NET=0";
    $nbr_ordo_deja_fait = 'CALL getTable("'.$nbr_ordo_deja_fait.'");';
    $nbr_ordo_deja_fait = $this->ModelPs->getRequeteOne($nbr_ordo_deja_fait);
    $data['nbr_ordo_deja_fait'] = number_format($nbr_ordo_deja_fait['nbre'],'0',',',' ');
    return $data;
  }

  //competer paiement salaire
  public function count_paiement_salaire()
  {
    $session  = \Config\Services::session();
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return redirect('Login_Ptba');
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";
    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
    }

    $requetedebase="SELECT COUNT(DISTINCT(exec.EXECUTION_BUDGETAIRE_ID)) as nbr FROM execution_budgetaire exec JOIN  execution_budgetaire_tache_detail det ON exec.EXECUTION_BUDGETAIRE_ID=det.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement titre ON titre.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON titre.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID JOIN mois ON mois.MOIS_ID=exec.MOIS_ID JOIN categorie_salaire cat ON cat.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN annee_budgetaire ON annee_budgetaire.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID WHERE 1 ".$cond_prof." AND titre.ETAPE_DOUBLE_COMMANDE_ID=19 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID=3";
    $nbr_prise_charge_salaire = 'CALL getTable("'.$requetedebase.'");';
    $nbr_prise_charge_salaire = $this->ModelPs->getRequeteOne($nbr_prise_charge_salaire);
    $data['nbr_prise_charge_salaire'] = number_format($nbr_prise_charge_salaire['nbr'],'0',',',' ');

    $sign_dir_compt="SELECT COUNT('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID') as nbr FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=23 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3";
    $sign_dir_compt = 'CALL getTable("'.$sign_dir_compt.'");';
    $sign_dir_compt = $this->ModelPs->getRequeteOne($sign_dir_compt);
    $data['sign_dir_compt'] = number_format($sign_dir_compt['nbr'],'0',',',' ');

    $sign_min="SELECT COUNT('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID') as nbr FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=25 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3";

   
    $sign_min = 'CALL getTable("'.$sign_min.'");';
    $sign_min = $this->ModelPs->getRequeteOne($sign_min);
    $data['sign_min'] = number_format($sign_min['nbr'],'0',',',' ');

    $sign_dgfp="SELECT COUNT('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID') as nbr FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=24 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3";
    $sign_dgfp = 'CALL getTable("'.$sign_dgfp.'");';
    $sign_dgfp = $this->ModelPs->getRequeteOne($sign_dgfp);
    $data['sign_dgfp'] = number_format($sign_dgfp['nbr'],'0',',',' ');

    //count validation td salaire net
    $valid_td_net="SELECT COUNT('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID') as nbr FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=26 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=0";
    $valid_td_net = 'CALL getTable("'.$valid_td_net.'");';
    $valid_td_net = $this->ModelPs->getRequeteOne($valid_td_net);
    $data['valid_td_net'] = number_format($valid_td_net['nbr'],'0',',',' ');

    //count validation td salaire autre retenu
    $valid_td_autr_ret="SELECT COUNT('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID') as nbr FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN beneficiaire_des_titres benef ON benef.BENEFICIAIRE_TITRE_ID=td.BENEFICIAIRE_TITRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=26 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=1";
    $valid_td_autr_ret = 'CALL getTable("'.$valid_td_autr_ret.'");';
    $valid_td_autr_ret = $this->ModelPs->getRequeteOne($valid_td_autr_ret);
    $data['valid_td_autr_ret'] = number_format($valid_td_autr_ret['nbr'],'0',',',' ');
    //count  td salaire net 
    $TD_Salaire_Net="SELECT COUNT(DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID) as nbr FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID  WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=20 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=0";
    $TD_Salaire_Net = 'CALL getTable("'.$TD_Salaire_Net.'");';
    $TD_Salaire_Net = $this->ModelPs->getRequeteOne($TD_Salaire_Net);
    $data['nbre_td_net'] = number_format($TD_Salaire_Net['nbr'],'0',',',' ');
    //count  td salaire autres retenus 
    $cond_prof_autre_retenu = ' ';
    if($profil_id != 1)
    {
      $cond_prof_autre_retenu =" AND prof.PROFIL_ID IN (SELECT PROFIL_ID FROM execution_budgetaire_etape_double_commande_profil WHERE ETAPE_DOUBLE_COMMANDE_ID =20)";
    }
    $Nb_Autre_retenus="SELECT DISTINCT EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID,td.ETAPE_DOUBLE_COMMANDE_ID,td.MONTANT_DECAISSEMENT,exec.ORDONNANCEMENT,exec.LIQUIDATION, exec.EXECUTION_BUDGETAIRE_ID, ANNEE_DESCRIPTION, DESC_MOIS ,DESC_TYPE_SALAIRE,DESC_CATEGORIE_SALAIRE  FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID LEFT JOIN annee_budgetaire on annee_budgetaire.ANNEE_BUDGETAIRE_ID=exec.ANNEE_BUDGETAIRE_ID LEFT JOIN mois ON mois.MOIS_ID=exec.MOIS_ID LEFT JOIN type_salairie ON type_salairie.TYPE_SALAIRE_ID = exec.TYPE_SALAIRE_ID  LEFT JOIN categorie_salaire ON categorie_salaire.CATEGORIE_SALAIRE_ID=exec.CATEGORIE_SALAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 AND td.ETAPE_DOUBLE_COMMANDE_ID=20 ".$cond_prof_autre_retenu." AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 AND IS_TD_NET=1 group by td.EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID";
    $Nb_Autre_retenus = 'CALL getTable("'.$Nb_Autre_retenus.'");';
    $Nb_Autre_retenus = count($this->ModelPs->getRequete($Nb_Autre_retenus));
    $data['nbre_td_autr_ret'] = number_format($Nb_Autre_retenus,'0',',',' ');

    return $data;
  }

  //commpter Decaissements 
  public function count_decaissement_salaire($value='')
  {
    $session  = \Config\Services::session();
    $profil_id=$session->get('SESSION_SUIVIE_PTBA_PROFIL_ID');
    $user_id ='';
    if(!empty($session->get('SESSION_SUIVIE_PTBA_USER_ID')))
    {
      $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    }
    else
    {
      return redirect('Login_Ptba');
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";
    $cond_prof = ' ';
    if($profil_id != 1)
    {
      $cond_prof =" AND prof.PROFIL_ID=".$profil_id;
    }
    // Nbre de decaissement a  faire
    $Nb_Decaiss_Faire="SELECT COUNT('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID') as nbr FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_etape_double_commande_profil prof ON td.ETAPE_DOUBLE_COMMANDE_ID=prof.ETAPE_DOUBLE_COMMANDE_ID WHERE 1 ".$cond_prof." AND td.ETAPE_DOUBLE_COMMANDE_ID=29 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 ";
    $Nb_Decaiss_Faire = 'CALL getTable("'.$Nb_Decaiss_Faire.'");';
    $Nb_Decaiss_Faire = $this->ModelPs->getRequeteOne($Nb_Decaiss_Faire);
    $data['nbre_decaiss_faire'] = number_format($Nb_Decaiss_Faire['nbr'],'0',',',' ');

    // Nbre de decaissement deja fait

    $Nb_Decaiss_Fait="SELECT COUNT('EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID') as nbr FROM execution_budgetaire exec JOIN execution_budgetaire_tache_detail exec_detail ON exec.EXECUTION_BUDGETAIRE_ID = exec_detail.EXECUTION_BUDGETAIRE_ID JOIN execution_budgetaire_titre_decaissement td ON td.EXECUTION_BUDGETAIRE_ID = exec.EXECUTION_BUDGETAIRE_ID WHERE 1 AND td.ETAPE_DOUBLE_COMMANDE_ID=30 AND exec.EXECUTION_BUDGETAIRE_TYPE_EXECUTION_ID =3 ";


    $Nb_Decaiss_Fait = 'CALL getTable("'.$Nb_Decaiss_Fait.'");';
    $Nb_Decaiss_Fait = $this->ModelPs->getRequeteOne($Nb_Decaiss_Fait);
    $data['nbre_decaiss_Fait'] = number_format($Nb_Decaiss_Fait['nbr'],'0',',',' ');

    return $data;
  }

  public function count_montant_exec_phase($TRIMESTRE_ID=0,$INSTITUTION_ID=0,$PROGRAMME_ID)
  {
    $condition="";
    if(!empty($INSTITUTION_ID))
    {
      if($INSTITUTION_ID>0)
      {
        $condition=" AND ptba.INSTITUTION_ID=".$INSTITUTION_ID;
        if(!empty($PROGRAMME_ID))
        {
          if($PROGRAMME_ID>0)
          {
            $condition.=" AND ptba.PROGRAMME_ID=".$PROGRAMME_ID;
          }
        }
      }
    }

    if (!empty($TRIMESTRE_ID))
    {
      if ($TRIMESTRE_ID!=5)
      {
        $condition.=" AND TRIMESTRE_ID=".$TRIMESTRE_ID;
      }
    }

    $callpsreq = "CALL getRequete(?,?,?,?);";

    $info_menu = $this->getBindParms('SUM(exec.MONTANT_ENG_BUDGETAIRE) AS ENG_BUDG,SUM(exec.MONTANT_ENG_JURIDIQUE) AS ENG_JURD,SUM(exec.MONTANT_LIQUIDATION) AS LIQUIDATION,SUM(exec.MONTANT_ORDONNANCEMENT) AS ORDONNANCEMENT,SUM(exec.MONTANT_PAIEMENT) AS PAIEMENT,SUM(exec.MONTANT_DECAISSEMENT) AS DECAISSEMENT','execution_budgetaire_execution_tache exec JOIN ptba_tache ptba ON ptba.PTBA_TACHE_ID=exec.PTBA_TACHE_ID LEFT JOIN pap_activites pap ON pap.PAP_ACTIVITE_ID=ptba.PAP_ACTIVITE_ID JOIN execution_budgetaire ON execution_budgetaire.EXECUTION_BUDGETAIRE_ID=exec.EXECUTION_BUDGETAIRE_ID','1 '.$condition,'1');
    $info_menu= $this->ModelPs->getRequeteOne($callpsreq, $info_menu);
    // $EBCORRIGE=$get_EBCorr['EBCORRIGE'];
    $data['ENG_BUDG']=number_format($info_menu['ENG_BUDG'],0,',',' ');
    $data['ENG_JURD']=number_format($info_menu['ENG_JURD'],0,',',' ');
    $data['LIQUIDATION']=number_format($info_menu['LIQUIDATION'],0,',',' ');
    $data['ORDONNANCEMENT']=number_format($info_menu['ORDONNANCEMENT'],0,',',' ');
    $data['PAIEMENT']=number_format($info_menu['PAIEMENT'],0,',',' ');
    $data['DECAISSEMENT']=number_format($info_menu['DECAISSEMENT'],0,',',' ');

    return $data;
  }
}
?>
