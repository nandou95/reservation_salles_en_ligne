<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Models\ModelS;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Test_Navigateur extends BaseController
{
  protected $session;
  protected $ModelPs;
  public function __construct()
  {
    $this->library = new CodePlayHelper();
    $this->ModelPs = new ModelPs();
    $this->ModelS = new ModelS();
    $this->session = \Config\Services::session();
  }

  public function index()
  {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $users_browser = get_browser(null, true);
    // print_r($user_agent);
    // die();

    // Vérifier si le navigateur est obsolète
    if (preg_match('/MSIE|Internet Explorer|Trident/i', $user_agent))
    {
      echo '<p>Votre navigateur est obsolète. Veuillez le mettre à jour pour une meilleure expérience de navigation.</p>';
    }
    elseif (preg_match('/Firefox/i', $user_agent))
    {
      echo '<p>Votre navigateur Firefox est à jour.</p>';
    }
    elseif(preg_match('/Chrome/i', $user_agent))
    {
      echo '<p>Votre navigateur Chrome est à jour.</p>';
    }
    elseif (preg_match('/Safari/i', $user_agent))
    {
      echo '<p>Votre navigateur Safari est à jour.</p>';
    }
    elseif (preg_match('/Opera/i', $user_agent))
    {
      echo '<p>Votre navigateur Opera est à jour.</p>';
    }
    else
    {
      echo '<p>Navigateur inconnu détecté. Veuillez vérifier si votre navigateur est à jour.</p>';
    }
  }

  function browser($value='')
  {
    $browser = array(
      'version'   => '0.0.0',
      'majorver'  => 0,
      'minorver'  => 0,
      'build'     => 0,
      'name'      => 'unknown',
      'useragent' => ''
    );

    $browsers = array(
      'firefox', 'msie', 'opera', 'chrome', 'safari', 'mozilla', 'seamonkey', 'konqueror', 'netscape',
      'gecko', 'navigator', 'mosaic', 'lynx', 'amaya', 'omniweb', 'avant', 'camino', 'flock', 'aol'
    );

    if(isset($_SERVER['HTTP_USER_AGENT']))
    {
      $browser['useragent'] = $_SERVER['HTTP_USER_AGENT'];
      $user_agent = strtolower($browser['useragent']);
      foreach($browsers as $_browser)
      {
        if (preg_match("/($_browser)[\/ ]?([0-9.]*)/", $user_agent, $match))
        {
          $browser['name'] = $match[1];
          $browser['version'] = $match[2];
          @list($browser['majorver'], $browser['minorver'], $browser['build']) = explode('.', $browser['version']);
          break;
        }
      }
    }
  }

  function test_result_requete()
  {
    $query = "SELECT info.ID_DEMANDE_INFO_SUPP,info.NOM_PROJET,info.NUMERO_PROJET,info.DATE_DEBUT_PROJET,info.DATE_FIN_PROJET,info.DUREE_PROJET,info.EST_REALISE_NATIONAL,info.PATH_CONTEXTE_JUSTIFICATION,info.OBJECTIF_GENERAL,info.BENEFICIAIRE_PROJET,info.IMPACT_ATTENDU_ENVIRONNEMENT,info.IMPACT_ATTENDU_GENRE,info.OBSERVATION_COMPLEMENTAIRE,info.DATE_PREPARATION_FICHE_PROJET,info.A_UNE_ETUDE,info.EST_CO_FINANCE,info.A_UNE_IMPACT_ENV,info.A_UNE_IMPACT_GENRE,info.RISQUE_PROJET,info.IS_FINISHED,
      demande.CODE_DEMANDE,demande.ID_DEMANDE,
      stat.ID_STATUT_PROJET,stat.DESCR_STATUT_PROJET,
      inst.INSTITUTION_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,
      pilier.ID_PILIER,pilier.DESCR_PILIER,
      obj.ID_OBJECT_STRATEGIQUE,obj.DESCR_OBJECTIF_STRATEGIC,
      pnd.ID_OBJECT_STRATEGIC_PND,pnd.DESCR_OBJECTIF_STRATEGIC_PND,
      axe.ID_AXE_INTERVENTION_PND,axe.DESCR_AXE_INTERVATION_PND,
      prog.PROGRAMME_ID,prog.CODE_PROGRAMME,prog.INTITULE_PROGRAMME,
      act.ACTION_ID,act.LIBELLE_ACTION,act.CODE_ACTION,
      info.ID_PROGRAMME_PND,programme.DESCR_PROGRAMME
      FROM pip_demande_infos_supp info JOIN proc_demandes demande ON info.ID_DEMANDE = demande.ID_DEMANDE
      JOIN pip_statut_projet stat ON info.ID_STATUT_PROJET = stat.ID_STATUT_PROJET
      JOIN inst_institutions inst ON info.INSTITUTION_ID = inst.INSTITUTION_ID
      JOIN pilier ON info.ID_PILIER = pilier.ID_PILIER
      JOIN objectif_strategique obj ON info.ID_OBJECT_STRATEGIQUE = obj.ID_OBJECT_STRATEGIQUE
      JOIN objectif_strategique_pnd pnd ON info.ID_OBJECT_STRATEGIC_PND = pnd.ID_OBJECT_STRATEGIC_PND
      JOIN axe_intervention_pnd axe ON info.ID_AXE_INTERVENTION_PND = axe.ID_AXE_INTERVENTION_PND
      JOIN inst_institutions_programmes prog ON info.ID_PROGRAMME = prog.PROGRAMME_ID
      JOIN inst_institutions_actions act ON info.ID_ACTION = act.ACTION_ID
      JOIN programme_pnd programme ON info.ID_PROGRAMME_PND = programme.ID_PROGRAMME_PND
      WHERE info.ID_DEMANDE = 445";
    $oldValues = $this->ModelPs->getRequete('CALL `getTable`("' . $query . '")');

    echo "<pre>";
    print_r($oldValues);
    echo "</pre><br><br><br>";


    $provinces = $this->ModelPs->getRequete('CALL getTable("SELECT DISTINCT lieu.ID_PROVINCE,provinces.PROVINCE_NAME,COUNT(lieu.ID_COMMUNE) nbr_communes FROM pip_lieu_intervention_projet lieu JOIN provinces ON provinces.PROVINCE_ID=lieu.ID_PROVINCE WHERE lieu.ID_DEMANDE_INFO_SUPP=258 GROUP BY lieu.ID_PROVINCE")');
    // $provinces = $this->ModelPs->getRequete('CALL getTable("SELECT * FROM `pip_lieu_intervention_projet` WHERE 1")');
    $lieux = [];
    echo "<pre>";
    print_r($provinces);
    echo "</pre><br><br>";

    // die();
    foreach ($provinces as $province) 
    {
      $lieux[$province->PROVINCE_NAME] = $province;
      echo "<pre>";
      print_r($lieux[$province->PROVINCE_NAME]);
      echo "</pre><br><br>";
    }

    print_r($lieux);
    die();
  }
}
?>