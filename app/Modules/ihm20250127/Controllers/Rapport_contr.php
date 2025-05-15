<?php
/**Hakizumukama Egide
*Titre: rapport fonctionnel
*Numero de telephone: (+257) 62 129 877
*WhatsApp: (+257) 71 422 939
*Email: egideh@mediabox.bi
*Date: 14 septembre,2023
**/
namespace  App\Modules\ihm\Controllers;
use App\Models\ModelPs;
use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Rapport_contr extends BaseController
{
  function __construct()
  {
    $db = db_connect();
    $this->ModelPs = new ModelPs($db);
    $this->my_Model = new ModelPs($db);
    $this->validation = \Config\Services::validation();
    $this->session  = \Config\Services::session();
    $table = new \CodeIgniter\View\Table();
  }
    //Liste view
  public function index()
  {
   $db =db_connect();
   $data=$this->urichk();
   $callpsreq = "CALL `getRequete`(?,?,?,?);";
   $bind_institution = $this->getBindParms('INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION','inst_institutions', '1', 'INSTITUTION_ID ASC');
   $data['institution']= $this->ModelPs->getRequete($callpsreq, $bind_institution);
   return view('App\Modules\ihm\Views\rapport_view',$data);
 }
   //fonction pour affichage d'une liste
 public function listing()
   {
   $data=$this->urichk();
   $db= db_connect();
   $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
   $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
   $ACTION_ID=$this->request->getPost('ACTION_ID');
   $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
   $callpsreq = "CALL `getRequete`(?,?,?,?);";
   $criteres="";
   $prog="";
   $var_search= !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
   $limit='LIMIT 0,10';
   if($_POST['length'] != -1)
   {
    $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
  }
  if(!empty($INSTITUTION_ID))
  {
    $criteres.=" AND CODE_MINISTERE=".$INSTITUTION_ID;

  }
  if(!empty($SOUS_TUTEL_ID))
  {
    $criteres.=" AND SOUS_TUTEL_ID='".$SOUS_TUTEL_ID."'";
  }
  if(!empty($PROGRAMME_ID))
  {
    $criteres.=" AND ptba.CODE_PROGRAMME=".$PROGRAMME_ID;
  }
  if(!empty($ACTION_ID))
  {
    $criteres.=" AND CODE_ACTION='".$ACTION_ID."'";
  }


  $ptba="SELECT PTBA_ID,INTITULE_MINISTERE,inst_institutions_sous_tutel.SOUS_TUTEL_ID, inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,ptba.INTITULE_PROGRAMME,LIBELLE_ACTION,ACTIVITES,T1,T2,T3,T4,PROGRAMMATION_FINANCIERE_BIF FROM ptba JOIN inst_institutions_sous_tutel ON inst_institutions_sous_tutel.INSTITUTION_ID=ptba.CODE_MINISTERE JOIN inst_institutions_programmes ON inst_institutions_programmes.PROGRAMME_ID=ptba.CODE_PROGRAMME WHERE 1 ".$criteres;
  $order_by='';
  $search = !empty($_POST['search']['value']) ?  (" AND (CODE_PROGRAMME LIKE '%$var_search%' OR INTITULE_PROGRAMME LIKE '%$var_search%' OR LIBELLE_ACTION LIKE '%$var_search%' OR ACTIVITES LIKE '%$var_search%' OR T1 LIKE '%$var_search%' OR T2 LIKE '%$var_search%' OR T3 LIKE '%$var_search%' OR T4 LIKE '%$var_search%')"):'';

  $query_secondaire=$ptba.' '.$search.' '.$order_by.'   '.$limit;
  $query_filter = $ptba.' '.$search;
  $requete='CALL `getList`("'.$query_secondaire.'")';
  $fetch_cov_frais = $this->ModelPs->datatable($requete);
  $data = array();
  $u=1; 
  foreach($fetch_cov_frais as $info)
  {
    $montant="SELECT MONTANT_RACCROCHE FROM execution_budgetaire_raccrochage_activite WHERE PTBA_ID=$info->PTBA_ID";
    $requete='CALL `getTable`("'.$montant.'")';
    $montant_raccr = $this->ModelPs->getRequete($requete);


    $post=array();
    $post[]=$u++;
    if (strlen($info->INTITULE_MINISTERE)>8){ 
      $INTITULE_MINISTERE = substr($info->INTITULE_MINISTERE, 0, 8).'...<a class="btn-sm" data-toggle="modal" data-target="#institution'.$info->PTBA_ID.'" data-toggle="tooltip" title="Afficher"><i class="fa fa-eye"></i></a>';
    }else{
      $INTITULE_MINISTERE= $info->INTITULE_MINISTERE;
    }
    $post[]=$INTITULE_MINISTERE;

    if (strlen($info->DESCRIPTION_SOUS_TUTEL)>8){ 
      $DESCRIPTION_SOUS_TUTEL = substr($info->DESCRIPTION_SOUS_TUTEL, 0, 8).'...<a class="btn-sm" data-toggle="modal" data-target="#sous_tutelle'.$info->SOUS_TUTEL_ID.'" data-toggle="tooltip" title="Afficher"><i class="fa fa-eye"></i></a>';
    }else{
      $DESCRIPTION_SOUS_TUTEL= $info->DESCRIPTION_SOUS_TUTEL;
    }
    $post[]=$DESCRIPTION_SOUS_TUTEL;
    if (strlen($info->INTITULE_PROGRAMME)>8){ 
      $INTITULE_PROGRAMME = substr($info->INTITULE_PROGRAMME, 0, 8).'...<a class="btn-sm" data-toggle="modal" data-target="#pgm'.$info->PTBA_ID.'" data-toggle="tooltip" title="Afficher"><i class="fa fa-eye"></i></a>';
    }else{
      $INTITULE_PROGRAMME= $info->INTITULE_PROGRAMME;
    }
    $post[]=$INTITULE_PROGRAMME;
    $post[] = !empty($info->LIBELLE_ACTION) ? $info->LIBELLE_ACTION : 'N/A';
    if (strlen($info->ACTIVITES)>8){ 
      $ACTIVITES = substr($info->ACTIVITES, 0, 8).'...<a class="btn-sm" data-toggle="modal" data-target="#activite'.$info->PTBA_ID.'" data-toggle="tooltip" title="Afficher"><i class="fa fa-eye"></i></a>';
    }else{
      $ACTIVITES= $info->ACTIVITES;
    }


    $post[]=$ACTIVITES;
    $post[]=$info->T1;
    $post[]=$info->T2;
    $post[]=$info->T3;
    $post[]=$info->T4;
    $post[]=$info->PROGRAMMATION_FINANCIERE_BIF."
    <div class='modal fade' id='institution".$info->PTBA_ID."'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-body'>
            <center>
              <h5><b> ".$info->INTITULE_MINISTERE." </b></h5>
            </center>
          </div>
          <div class='modal-footer'>
            <button class='btn btn-primary btn-md' data-dismiss='modal'>
              Quitter
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class='modal fade' id='sous_tutelle".$info->SOUS_TUTEL_ID."'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-body'>
            <center>
              <h5><b> ".$info->DESCRIPTION_SOUS_TUTEL." </b></h5>
            </center>
          </div>
          <div class='modal-footer'>
            <button class='btn btn-primary btn-md' data-dismiss='modal'>
              Quitter
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class='modal fade' id='pgm".$info->PTBA_ID."'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-body'>
            <center>
              <h5><b> ".$info->INTITULE_PROGRAMME." </b></h5>
            </center>
          </div>
          <div class='modal-footer'>
            <button class='btn btn-primary btn-md' data-dismiss='modal'>
              Quitter
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class='modal fade' id='activite".$info->PTBA_ID."'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <div class='modal-body'>
            <center>
              <h5><b> ".$info->ACTIVITES." </b></h5>
            </center>
          </div>
          <div class='modal-footer'>
            <button class='btn btn-primary btn-md' data-dismiss='modal'>
              Quitter
            </button>
          </div>
        </div>
      </div>
    </div>";
    $psgetrequete = "CALL `getRequete`(?,?,?,?);";
    $T1='';
    $T2='';
    $T3='';
    $T4='';
    $MONT=!empty($montant_raccr['MONTANT_RACCROCHE']) ? $montant_raccr['MONTANT_RACCROCHE'] : 0;
    $TRANCHE=!empty($montant_raccr['TRIMESTRE_ID']) ? $montant_raccr['TRIMESTRE_ID'] : 0;
      //print_r($TRANCHE);exit();
    if (!empty($montant_raccr)) {

      foreach($montant_raccr as $value){
        $bind_tranch = $this->getBindParms('TRIMESTRE_ID', 'execution_budgetaire_raccrochage_activite', 'PTBA_ID="'.$info->PTBA_ID.'" ', 'TRIMESTRE_ID');
        $bind_tranch = str_replace('\"', '"', $bind_tranch);
        $tranc = $this->ModelPs->getRequeteOne($psgetrequete, $bind_tranch);
        if($tranc['TRIMESTRE_ID']==1){
          $T1=!empty($value->MONTANT_RACCROCHE) ? $value->MONTANT_RACCROCHE : 0;
        }else{
          $T1=0;
        }
        if($tranc['TRIMESTRE_ID']==2){
          $T2=!empty($value->MONTANT_RACCROCHE) ? $value->MONTANT_RACCROCHE : 0;
        }else{
          $T2=0;
        }
        if($tranc['TRIMESTRE_ID']==3){
          $T3=!empty($value->MONTANT_RACCROCHE) ? $value->MONTANT_RACCROCHE : 0;
        }else{
          $T3=0;
        }
        if($tranc['TRIMESTRE_ID']==4){
          $T4=!empty($value->MONTANT_RACCROCHE) ? $value->MONTANT_RACCROCHE : 0;
        }else{
          $T4=0;
        }
      }
    }
    $post[]=!empty($T1) ? $T1:0;
    $post[]=!empty($T2) ? $T2:0;
    $post[]=!empty($T3) ? $T3:0;
    $post[]=!empty($T4) ? $T4:0;
    $data[]=$post;
  }

  $requeteqp='CALL `getList`("'.$ptba.'")';
  $recordsTotal = $this->ModelPs->datatable( $requeteqp);
  $requeteqf='CALL `getList`("'.$query_filter.'")';
  $recordsFiltered = $this->ModelPs->datatable( $requeteqf);
  $output = array(
    "draw" => intval($_POST['draw']),
    "recordsTotal" =>count($recordsTotal),
    "recordsFiltered" => count($recordsFiltered),
    "data" => $data

  );
  echo json_encode($output);
}

//Fonction pour exporter des donnees
public function export($INSTITUTION_ID='',$SOUS_TUTEL_ID='',$PROGRAMME_ID='',$ACTION_ID='')
{

  $db = db_connect();
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $criteres='';
  $criteresou='';

  if($INSTITUTION_ID!=0)
  {

    $criteres.=" AND CODE_MINISTERE=".$INSTITUTION_ID;
  }

  if($SOUS_TUTEL_ID!=0)
  {
    $criteresou.=" AND SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
  }

  if($PROGRAMME_ID!=0)
  {
    $criteres.=" AND CODE_PROGRAMME=".$PROGRAMME_ID;
  }

  if($ACTION_ID!=0)
  {
    $criteres.=" AND CODE_ACTION=".$ACTION_ID;
  }


  $get_institutions = $this->getBindParms('`CODE_MINISTERE`,inst_institutions.DESCRIPTION_INSTITUTION,SUM(T1)T1,SUM(T2)T2,SUM(T3)T3,SUM(T4)T4,SUM(PROGRAMMATION_FINANCIERE_BIF)PROGRAMMATIONFBIF','`ptba` JOIN inst_institutions ON inst_institutions.CODE_INSTITUTION=ptba.CODE_MINISTERE', '1 '.$criteres.' GROUP BY `CODE_MINISTERE`,inst_institutions.DESCRIPTION_INSTITUTION', 'DESCRIPTION_INSTITUTION asc');

  $institutions = $this->ModelPs->getRequete($callpsreq, $get_institutions);

  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setCellValue('A1', 'IMPUTATION');
  $sheet->setCellValue('B1', 'BUDGET T1');
  $sheet->setCellValue('C1', 'BUDGET T2');
  $sheet->setCellValue('D1', 'BUDGET T3');
  $sheet->setCellValue('E1', 'BUDGET T4');       
  $sheet->setCellValue('F1', 'PROGRAMMATION FINANCIERE BIF');               
  $rows = 3;

  foreach ($institutions as $key)
  {
    $sheet->setCellValue('A' . $rows, $key->CODE_MINISTERE.'  '.$key->DESCRIPTION_INSTITUTION);
    $sheet->setCellValue('B' . $rows, $key->T1);
    $sheet->setCellValue('C' . $rows, $key->T2);
    $sheet->setCellValue('D' . $rows, $key->T3);
    $sheet->setCellValue('E' . $rows, $key->T4);
    $sheet->setCellValue('F' . $rows, $key->PROGRAMMATIONFBIF);
    $rows++;
    $get_sous_tutelle = $this->getBindParms('s_t.SOUS_TUTEL_ID,s_t.CODE_SOUS_TUTEL,s_t.DESCRIPTION_SOUS_TUTEL,SUM(T1) as T1,SUM(T2) as T2,SUM(T3) as T3,SUM(T4) as T4,SUM(PROGRAMMATION_FINANCIERE_BIF) as PROGRAMMATION_FINANCIERE_BIF','ptba JOIN inst_institutions ON inst_institutions.CODE_INSTITUTION=ptba.CODE_MINISTERE JOIN inst_institutions_sous_tutel s_t ON s_t.INSTITUTION_ID=inst_institutions.INSTITUTION_ID', 'inst_institutions.CODE_INSTITUTION='.$key->CODE_MINISTERE.$criteresou.' GROUP BY s_t.DESCRIPTION_SOUS_TUTEL', 's_t.CODE_SOUS_TUTEL ASC');

    $sous_tutelle = $this->ModelPs->getRequete($callpsreq, $get_sous_tutelle);
    if(!empty($sous_tutelle))
    {
      $rows1=$rows+2;
      foreach($sous_tutelle as $key_s_tutel)
      {
        $sheet->setCellValue('A' . $rows1, $key_s_tutel->CODE_SOUS_TUTEL.'  '.$key_s_tutel->DESCRIPTION_SOUS_TUTEL);
        $sheet->setCellValue('B' . $rows1, $key_s_tutel->T1);
        $sheet->setCellValue('C' . $rows1, $key_s_tutel->T2);
        $sheet->setCellValue('D' . $rows1, $key_s_tutel->T3);
        $sheet->setCellValue('E' . $rows1, $key_s_tutel->T4);
        $sheet->setCellValue('F' . $rows1, $key_s_tutel->PROGRAMMATION_FINANCIERE_BIF);
        $rows1++; 

        $get_progra=$this->getBindParms('CODE_PROGRAMME,INTITULE_PROGRAMME,SUM(T1) AS T1,SUM(T2) AS T2,SUM(T3) AS T3,SUM(T4) AS T4,SUM(PROGRAMMATION_FINANCIERE_BIF) AS PROGRAMMATION_FINANCIERE_BIF', 'ptba JOIN inst_institutions inst ON inst.CODE_INSTITUTION=ptba.CODE_MINISTERE JOIN inst_institutions_sous_tutel s_t ON s_t.INSTITUTION_ID=inst.INSTITUTION_ID', 's_t.SOUS_TUTEL_ID='.$key_s_tutel->SOUS_TUTEL_ID.$criteres.' GROUP BY CODE_PROGRAMME,INTITULE_PROGRAMME', 'INTITULE_PROGRAMME');
        $program = $this->ModelPs->getRequete($callpsreq, $get_progra);

        if (!empty($program)) 
        {
          $rows2=$rows1+2;
          foreach ($program as $key_prog) 
          {

            $sheet->setCellValue('A' . $rows2, $key_prog->CODE_PROGRAMME.' '.$key_prog->INTITULE_PROGRAMME);
            $sheet->setCellValue('B' . $rows2, $key_prog->T1);
            $sheet->setCellValue('C' . $rows2, $key_prog->T2);
            $sheet->setCellValue('D' . $rows2, $key_prog->T3);
            $sheet->setCellValue('E' . $rows2, $key_prog->T4);
            $sheet->setCellValue('F' . $rows2, $key_prog->PROGRAMMATION_FINANCIERE_BIF);
            $rows2++;

            $get_action=$this->getBindParms('ptba.CODE_ACTION,ptba.LIBELLE_ACTION,SUM(T1) AS T1,SUM(T2) AS T2,SUM(T3) AS T3,SUM(T4) AS T4,SUM(PROGRAMMATION_FINANCIERE_BIF) AS PROGRAMMATION_FINANCIERE_BIF', 'ptba JOIN inst_institutions inst ON inst.CODE_INSTITUTION=ptba.CODE_MINISTERE JOIN inst_institutions_actions act ON act.PROGRAMME_ID=ptba.CODE_ACTION JOIN inst_institutions_sous_tutel s_t ON s_t.INSTITUTION_ID=inst.INSTITUTION_ID', 'CODE_PROGRAMME="'.$key_prog->CODE_PROGRAMME.'" '.$criteres.' GROUP BY CODE_ACTION,LIBELLE_ACTION', 'CODE_ACTION ASC');

            $get_action=str_replace('\"', '"', $get_action);
            $actio = $this->ModelPs->getRequete($callpsreq, $get_action);

            if (!empty($actio)) 
            {
              $rows3=$rows2+2;
              foreach ($actio as $key_action) 
              {
                $sheet->setCellValue('A' . $rows3, $key_action->CODE_ACTION.' '.$key_action->LIBELLE_ACTION);
                $sheet->setCellValue('B' . $rows3, $key_action->T1);
                $sheet->setCellValue('C' . $rows3, $key_action->T2);
                $sheet->setCellValue('D' . $rows3, $key_action->T3);
                $sheet->setCellValue('E' . $rows3, $key_action->T4);
                $sheet->setCellValue('F' . $rows3, $key_action->PROGRAMMATION_FINANCIERE_BIF);
                $rows3++;
                
              }
              $rows2=$rows3+2;
            }

          }
          $rows1=$rows2+2;
        }
      }
      $rows=$rows1+2;
    }
  }

  $writer = new Xlsx($spreadsheet);
  $writer->save('world.xlsx');
  return $this->response->download('world.xlsx', null)->setFileName('rapport classification administrative.xlsx');
  return redirect('ihm/Rapport_contr/index');

}


public function get_dep()
{
  $callpsreq = "CALL `getRequete`(?,?,?,?);";

  $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
  $ACTION_ID=$this->request->getPost('ACTION_ID');
  $PROGRAMME_ID=$this->request->getPost('PROGRAMME_ID');
  $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');  
  $prog='<option value="">séléctionner</option>';
  $act='<option value="">séléctionner</option>';
  $sous_t='<option value="">séléctionner</option>';
  $TYPE_INSTITUTION_ID = '';

  if (!empty($INSTITUTION_ID))
  {
   $bind_instution_sous_tutel = $this->getBindParms('SOUS_TUTEL_ID,DESCRIPTION_SOUS_TUTEL','inst_institutions_sous_tutel st JOIN inst_institutions inst ON inst.INSTITUTION_ID=st.INSTITUTION_ID','inst.INSTITUTION_ID='.$INSTITUTION_ID,'DESCRIPTION_SOUS_TUTEL ASC');
   $inst_sous_tutel= $this->ModelPs->getRequete($callpsreq, $bind_instution_sous_tutel);

   $get_type=$this->getBindParms('`TYPE_INSTITUTION_ID`','inst_institutions','`CODE_INSTITUTION`='.$INSTITUTION_ID,'TYPE_INSTITUTION_ID');
   $type=$this->ModelPs->getRequeteOne($callpsreq,$get_type);
   $TYPE_INSTITUTION_ID = $type['TYPE_INSTITUTION_ID'];

   foreach ($inst_sous_tutel as $sous)
   {
     if (!empty($SOUS_TUTEL_ID))
     {
       if ($SOUS_TUTEL_ID==$sous->SOUS_TUTEL_ID) {
         $sous_t.= "<option value ='".$sous->SOUS_TUTEL_ID."' selected>".$sous->DESCRIPTION_SOUS_TUTEL."</option>";
       }
       else{
         $sous_t.= "<option value ='".$sous->SOUS_TUTEL_ID."'>".$sous->DESCRIPTION_SOUS_TUTEL."</option>";
       }
     }
     else
     {
       $sous_t.= "<option value ='".$sous->SOUS_TUTEL_ID."'>".$sous->DESCRIPTION_SOUS_TUTEL."</option>";
     }
   }
 }

 if (!empty($SOUS_TUTEL_ID))
 {
  $bind_programme = $this->getBindParms('DISTINCT `CODE_PROGRAMME`,`INTITULE_PROGRAMME`', 'inst_institutions_programmes`', 's_t.SOUS_TUTEL_ID='.$SOUS_TUTEL_ID.' ', 'INTITULE_PROGRAMME ASC');
  $programme= $this->ModelPs->getRequete($callpsreq, $bind_programme);

  foreach ($programme as $progra)
  {
   if (!empty($PROGRAMME_ID))
   {
     if ($PROGRAMME_ID==$progra->CODE_PROGRAMME) {
       $prog.= "<option value ='".$progra->CODE_PROGRAMME."' selected>".$progra->INTITULE_PROGRAMME."</option>";
     }
     else{
       $prog.= "<option value ='".$progra->CODE_PROGRAMME."'>".$progra->INTITULE_PROGRAMME."</option>";
     }
   }
   else
   {
     $prog.= "<option value ='".$progra->CODE_PROGRAMME."'>".$progra->INTITULE_PROGRAMME."</option>";
   }
 }

}

if (!empty($PROGRAMME_ID))
{
 $bind_action = $this->getBindParms('ACTION_ID,LIBELLE_ACTION,CODE_ACTION','inst_institutions_actions act JOIN inst_institutions_programmes prog ON prog.PROGRAMME_ID=act.PROGRAMME_ID', 'prog.CODE_PROGRAMME='.$PROGRAMME_ID, 'ACTION_ID ASC');
 $action = $this->ModelPs->getRequete($callpsreq, $bind_action); 
 foreach ($action as $value)
 {
   if (!empty($ACTION_ID))
   {
     if ($ACTION_ID==$value->ACTION_ID)
     {
       $act.= "<option value ='".$value->CODE_ACTION."' selected>".$value->LIBELLE_ACTION."</option>";
     }
     else
     {
       $act.= "<option value ='".$value->CODE_ACTION."'>".$value->LIBELLE_ACTION."</option>";
     }

   }
   else
   {
     $act.= "<option value ='".$value->CODE_ACTION."'>".$value->LIBELLE_ACTION."</option>";
   } 
 }

}
$output = array("prog"=>$prog,"act"=>$act,"sous_t"=>$sous_t,"TYPE_INSTITUTION_ID" => $TYPE_INSTITUTION_ID);

return $this->response->setJSON($output);

}

public function getBindParms($columnselect, $table, $where, $orderby)
{
  $db = db_connect();
  $bindparams =[$db->escapeString($columnselect),$db->escapeString($table),$db->escapeString($where),$db->escapeString($orderby)];
  return $bindparams;
}
}
?>
