<?php
/**
 * Auteur: Nderagakura Alain Charbel
 * email: @mediabox.bi
 * tel: +257 62 00 35 22
 * date 15.02.2024 15:12
 */

namespace App\Modules\double_commande_new\Controllers;   
use App\Controllers\BaseController;
use App\Models\ModelPs;
use App\Libraries\CodePlayHelper;
use App\Libraries\Notification;

class Signataire_Note extends BaseController
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

  //fonction pour inserer dans les colonnes souhaites
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

  /* update table */
  function update_all_table($table,$datatomodifie,$conditions)
  {
    $bindparams =[$table,$datatomodifie,$conditions];
    $updateRequete = "CALL `updateData`(?,?,?);";
    $resultat=$this->ModelPs->createUpdateDelete($updateRequete, $bindparams);
  }

  //récupération du sous tutelle par rapport à l'institution
  public function getSousTutel()
  {
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');

    $sql_institution='SELECT CODE_SOUS_TUTEL,inst_institutions_sous_tutel.DESCRIPTION_SOUS_TUTEL,inst_institutions_sous_tutel.SOUS_TUTEL_ID FROM inst_institutions_sous_tutel WHERE 1 AND inst_institutions_sous_tutel.INSTITUTION_ID ='.$INSTITUTION_ID.' ';
    $sous_tutel = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");


    $tutel="<option value=''>--Sous tutel--</option>";
    foreach ($sous_tutel as $key)
    {
      $tutel.= "<option value ='".$key->SOUS_TUTEL_ID."'>".$key->CODE_SOUS_TUTEL."-".$key->DESCRIPTION_SOUS_TUTEL."</option>";
    }
    $output = array("tutel"=>$tutel);
    return $this->response->setJSON($output);
  }

  public function get_view()
  {
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $data=$this->urichk();
    
    $cart = \Config\Services::cart();
    $cart->destroy();
    $bind_data = $this->getBindParms('INSTITUTION_SIGNATAIRE_ID_TEMPO,INSTITUTION_ID_TEMPO,DESCRIPTION_INSTITUTION,DESCRIPTION_SOUS_TUTEL,SOUS_TUTEL_ID_TEMPO,DESC_POSTE_TEMPO,NOM_PRENOM_TEMPO','inst_institutions_signataires_notes_tempo tempo JOIN inst_institutions inst ON tempo.INSTITUTION_ID_TEMPO=inst.INSTITUTION_ID LEFT JOIN inst_institutions_sous_tutel st ON tempo.SOUS_TUTEL_ID_TEMPO=st.SOUS_TUTEL_ID','USER_ID='.$user_id,'1');
    $bind_data= $this->ModelPs->getRequete($callpsreq, $bind_data);

    foreach($bind_data as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESCRIPTION_INSTITUTION'=>$value->DESCRIPTION_INSTITUTION,
        'DESCRIPTION_SOUS_TUTEL'=>$value->DESCRIPTION_SOUS_TUTEL,
        'DESC_POSTE_TEMPO'=>$value->DESC_POSTE_TEMPO,
        'NOM_PRENOM_TEMPO'=>$value->NOM_PRENOM_TEMPO,
        'INSTITUTION_SIGNATAIRE_ID_TEMPO'=>$value->INSTITUTION_SIGNATAIRE_ID_TEMPO,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }
    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>INSTITUTION&emsp;&emsp;&emsp;&emsp;</th>
    <th>SOUS TUTEL&emsp;&emsp;&emsp;&emsp;</th>
    <th>POSTE&emsp;&emsp;&emsp;&emsp;</th>
    <th>NOM ET PRENOM&emsp;&emsp;&emsp;&emsp;</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());

    foreach ($cart->contents() as $items):
        
      if (preg_match('/FILECI/',$items['typecartitem']))
      {
        $i++;
        $html.='<tr>
        <td>'.$j.'</td>
       
        <td>'.$items['DESCRIPTION_INSTITUTION'].'</td>
        <td>'.$items['DESCRIPTION_SOUS_TUTEL'].'</td>
        <td>'.$items['DESC_POSTE_TEMPO'].'</td>
        <td>'.$items['NOM_PRENOM_TEMPO'].'</td>
        <td>
        <a href="javascript:void(0)" onclick="show_modal('.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
        </td>
        <td>
        <textarea id="DEL_CIBLE'.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].'" style="display:none">'.$items['DESCRIPTION_INSTITUTION'].'</textarea>
        <input type="hidden" id="rowid'.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].'" value='.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].'>
        </td>
        </tr>';        
      }

      $j++;
      $i++;
    endforeach;
    $html.=' </tbody>
    </table>';
    
    $data['nbr_cart']=$i;
    if ($i>0) {
      $data['html']=$html;
      $data['bind_data']=$bind_data;
    }
    else
    {
      $data['html']='';
      $data['bind_data']='';
    }
    $sql_institution='SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1';
    // $sql_institution='SELECT INSTITUTION_ID,CODE_INSTITUTION,DESCRIPTION_INSTITUTION FROM inst_institutions WHERE 1 AND INSTITUTION_ID='.$bind_data[0]->INSTITUTION_ID_TEMPO;
    $data['institution'] = $this->ModelPs->getRequete("CALL `getTable`('" . $sql_institution . "')");
    return view('App\Modules\double_commande_new\Views\Signataire_Note_Add_View',$data);
  }

  public function save_tempo()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $INSTITUTION_ID=$this->request->getPost('INSTITUTION_ID');
    $DESC_POSTE=$this->request->getPost('DESC_POSTE');
    $NOM_PRENOM=$this->request->getPost('NOM_PRENOM');
    $SOUS_TUTEL_ID=$this->request->getPost('SOUS_TUTEL_ID');
    $SOUS_TUTEL_ID=!empty($SOUS_TUTEL_ID)?$SOUS_TUTEL_ID:'NULL';

    $columsinsert="INSTITUTION_ID_TEMPO,SOUS_TUTEL_ID_TEMPO,DESC_POSTE_TEMPO,NOM_PRENOM_TEMPO,USER_ID";
    $datatoinsert= $INSTITUTION_ID.','.$SOUS_TUTEL_ID.',"'.$DESC_POSTE.'","'.$NOM_PRENOM.'",'.$user_id;
    $table='inst_institutions_signataires_notes_tempo';
    $this->save_all_table($table,$columsinsert,$datatoinsert);

    $cart = \Config\Services::cart();
    $cart->destroy();
    $bind_data = $this->getBindParms('INSTITUTION_SIGNATAIRE_ID_TEMPO,INSTITUTION_ID_TEMPO,DESCRIPTION_INSTITUTION,DESCRIPTION_SOUS_TUTEL,SOUS_TUTEL_ID_TEMPO,DESC_POSTE_TEMPO,NOM_PRENOM_TEMPO','inst_institutions_signataires_notes_tempo tempo JOIN inst_institutions inst ON tempo.INSTITUTION_ID_TEMPO=inst.INSTITUTION_ID LEFT JOIN inst_institutions_sous_tutel st ON tempo.SOUS_TUTEL_ID_TEMPO=st.SOUS_TUTEL_ID','USER_ID='.$user_id,'1');
    $bind_data= $this->ModelPs->getRequete($callpsreq, $bind_data);

    foreach($bind_data as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESCRIPTION_INSTITUTION'=>$value->DESCRIPTION_INSTITUTION,
        'DESCRIPTION_SOUS_TUTEL'=>$value->DESCRIPTION_SOUS_TUTEL,
        'DESC_POSTE_TEMPO'=>$value->DESC_POSTE_TEMPO,
        'NOM_PRENOM_TEMPO'=>$value->NOM_PRENOM_TEMPO,
        'INSTITUTION_SIGNATAIRE_ID_TEMPO'=>$value->INSTITUTION_SIGNATAIRE_ID_TEMPO,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }
    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>INSTITUTION&emsp;&emsp;&emsp;&emsp;</th>
    <th>SOUS TUTEL&emsp;&emsp;&emsp;&emsp;</th>
    <th>POSTE&emsp;&emsp;&emsp;&emsp;</th>
    <th>NOM ET PRENOM&emsp;&emsp;&emsp;&emsp;</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());

    foreach ($cart->contents() as $items):
        
        if (preg_match('/FILECI/',$items['typecartitem']))
        {
          $i++;
          $html.='<tr>
          <td>'.$j.'</td>
         
          <td>'.$items['DESCRIPTION_INSTITUTION'].'</td>
          <td>'.$items['DESCRIPTION_SOUS_TUTEL'].'</td>
          <td>'.$items['DESC_POSTE_TEMPO'].'</td>
          <td>'.$items['NOM_PRENOM_TEMPO'].'</td>
          <td>
          <a href="javascript:void(0)" onclick="show_modal('.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
          </td>
          <td>
          <textarea id="DEL_CIBLE'.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].'" style="display:none">'.$items['DESCRIPTION_INSTITUTION'].'</textarea>
          <input type="hidden" id="rowid'.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].'" value='.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].'>
          </td>
          </tr>';        
        }

        $j++;
        $i++;
    endforeach;
    $html.=' </tbody>
    </table>';
    if ($i>0) 
    {
      $output = array('nbr' => $i, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
    else
    {
      $html= '';
      $output = array('nbr' => $i, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
  }

  public function delete()
  {
    $session  = \Config\Services::session();
    $callpsreq = "CALL `getRequete`(?,?,?,?);";
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

    $id=$this->request->getPost('id');

    $db = db_connect();     
    $critere ="INSTITUTION_SIGNATAIRE_ID_TEMPO =" .$id;
    $table="inst_institutions_signataires_notes_tempo";
    $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
    $deleteRequete = "CALL `deleteData`(?,?);";
    $this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);

    $cart = \Config\Services::cart();
    $cart->destroy();
    $bind_data = $this->getBindParms('INSTITUTION_SIGNATAIRE_ID_TEMPO,INSTITUTION_ID_TEMPO,DESCRIPTION_INSTITUTION,DESCRIPTION_SOUS_TUTEL,SOUS_TUTEL_ID_TEMPO,DESC_POSTE_TEMPO,NOM_PRENOM_TEMPO','inst_institutions_signataires_notes_tempo tempo JOIN inst_institutions inst ON tempo.INSTITUTION_ID_TEMPO=inst.INSTITUTION_ID LEFT JOIN inst_institutions_sous_tutel st ON tempo.SOUS_TUTEL_ID_TEMPO=st.SOUS_TUTEL_ID','USER_ID='.$user_id,'1');
    $bind_data= $this->ModelPs->getRequete($callpsreq, $bind_data);

    foreach($bind_data as $value)
    {
      $file_data=array(
        'id'=>uniqid(),
        'qty'=>1,
        'price'=>1,
        'name'=>'CI',
        'DESCRIPTION_INSTITUTION'=>$value->DESCRIPTION_INSTITUTION,
        'DESCRIPTION_SOUS_TUTEL'=>$value->DESCRIPTION_SOUS_TUTEL,
        'DESC_POSTE_TEMPO'=>$value->DESC_POSTE_TEMPO,
        'NOM_PRENOM_TEMPO'=>$value->NOM_PRENOM_TEMPO,
        'INSTITUTION_SIGNATAIRE_ID_TEMPO'=>$value->INSTITUTION_SIGNATAIRE_ID_TEMPO,
        'typecartitem'=>'FILECI'
      );
      $cart->insert($file_data);
    }
    $html="";
      $j=1;
      $i=0;

    $html.='
    <table class="table table-striped">
    <thead class="table-dark">
    <tr>
    <th>#</th>
    <th>INSTITUTION&emsp;&emsp;&emsp;&emsp;</th>
    <th>SOUS TUTEL&emsp;&emsp;&emsp;&emsp;</th>
    <th>POSTE&emsp;&emsp;&emsp;&emsp;</th>
    <th>NOM ET PRENOM&emsp;&emsp;&emsp;&emsp;</th>
    <th>OPTION</th>
    </tr>
    </thead>
    <tbody>';
    $i=0;
    $val=count($cart->contents());

    foreach ($cart->contents() as $items):
        
        if (preg_match('/FILECI/',$items['typecartitem']))
        {
          $i++;
          $html.='<tr>
          <td>'.$j.'</td>
         
          <td>'.$items['DESCRIPTION_INSTITUTION'].'</td>
          <td>'.$items['DESCRIPTION_SOUS_TUTEL'].'</td>
          <td>'.$items['DESC_POSTE_TEMPO'].'</td>
          <td>'.$items['NOM_PRENOM_TEMPO'].'</td>
          <td>
          <a href="javascript:void(0)" onclick="show_modal('.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].')" class="btn btn-danger btn-sm" title="'.lang('messages_lang.supprimer_action').'" ><span class="fa fa-trash"></span></a>
          </td>
          <td>
          <textarea id="DEL_CIBLE'.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].'" style="display:none">'.$items['DESCRIPTION_INSTITUTION'].'</textarea>
          <input type="hidden" id="rowid'.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].'" value='.$items['INSTITUTION_SIGNATAIRE_ID_TEMPO'].'>
          </td>
          </tr>';        
        }

        $j++;
        $i++;
    endforeach;
    $html.=' </tbody>
    </table>';
    if ($i>0) 
    {
      $output = array('nbr' => $i, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
    else
    {
      $html= '';
      $output = array('nbr' => $i, 'cart'=>$html);
      return $this->response->setJSON($output);
    }
  }

  public function save()
  {
    $session  = \Config\Services::session();
    $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $callpsreq = "CALL `getRequete`(?,?,?,?);";

    $bind_data = $this->getBindParms('INSTITUTION_SIGNATAIRE_ID_TEMPO,INSTITUTION_ID_TEMPO,DESCRIPTION_INSTITUTION,DESCRIPTION_SOUS_TUTEL,SOUS_TUTEL_ID_TEMPO,DESC_POSTE_TEMPO,NOM_PRENOM_TEMPO','inst_institutions_signataires_notes_tempo tempo JOIN inst_institutions inst ON tempo.INSTITUTION_ID_TEMPO=inst.INSTITUTION_ID LEFT JOIN inst_institutions_sous_tutel st ON tempo.SOUS_TUTEL_ID_TEMPO=st.SOUS_TUTEL_ID','USER_ID='.$user_id,'1');
    $bind_data= $this->ModelPs->getRequete($callpsreq, $bind_data);

    foreach($bind_data as $val)
    {
      $SOUS_TUTEL_ID_TEMPO=!empty($val->SOUS_TUTEL_ID_TEMPO)?$val->SOUS_TUTEL_ID_TEMPO:'NULL';
      $columsinsert="INSTITUTION_ID,SOUS_TUTEL_ID,DESC_POSTE,NOM_PRENOM";
      $datatoinsert= $val->INSTITUTION_ID_TEMPO.','.$SOUS_TUTEL_ID_TEMPO.',"'.$val->DESC_POSTE_TEMPO.'","'.$val->NOM_PRENOM_TEMPO.'"';
      $table='inst_institutions_signataires_notes';
      $this->save_all_table($table,$columsinsert,$datatoinsert);

      $db = db_connect();
      $critere ="INSTITUTION_SIGNATAIRE_ID_TEMPO =" .$val->INSTITUTION_SIGNATAIRE_ID_TEMPO;
      $table="inst_institutions_signataires_notes_tempo";
      $bindparams =[$db->escapeString($table),$db->escapeString($critere)];
      $deleteRequete = "CALL `deleteData`(?,?);";
      $this->ModelPs->createUpdateDelete($deleteRequete, $bindparams);
    }    

    return redirect('double_commande_new/Signataire_Note/liste');
  }

  function liste()
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

    $callpsreq = "CALL getRequete(?,?,?,?);";

    //selection les institution de la personne connectee
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
    return view('App\Modules\double_commande_new\Views\Signataire_Note_Liste_View',$data);
  }

  function listing()
  {
    $session  = \Config\Services::session();

    //Filtres de la liste
    $INSTITUTION_ID = $this->request->getPost('INSTITUTION_ID');
    $SOUS_TUTEL_ID = $this->request->getPost('SOUS_TUTEL_ID');
    $callpsreq = "CALL getRequete(?,?,?,?);";
    $user_id=$session->get('SESSION_SUIVIE_PTBA_USER_ID');
    $critere1="";
    $critere2='';
    $critere3="";
    //selection les institution de la personne connectee
    $user_affectation = $this->getBindParms('USER_AFFECTAION,USER_ID,INSTITUTION_ID,IS_SOUS_TUTEL,SOUS_TUTEL_ID','user_affectaion','USER_ID='.$user_id.'','USER_ID DESC');
    $getaffect= $this->ModelPs->getRequete($callpsreq, $user_affectation);

    $ID_INST='';
    foreach ($getaffect as $value)
    {
      $ID_INST.=$value->INSTITUTION_ID.' ,';           
    }
    $ID_INST = substr($ID_INST,0,-1);

    if(!empty($INSTITUTION_ID))
    {
      $critere1=" AND inst.INSTITUTION_ID=".$INSTITUTION_ID;
    }
    else
    {
      $critere1=" AND inst.INSTITUTION_ID IN(".$ID_INST.")";
    }

    if(!empty($SOUS_TUTEL_ID))
    {
      $critere3="AND st.SOUS_TUTEL_ID=".$SOUS_TUTEL_ID;
    }

    $var_search = !empty($_POST['search']['value']) ? $_POST['search']['value'] : null;
    $var_search = str_replace("'", "\'", $var_search);
    $group = "";
    $critaire = "";
    $limit = 'LIMIT 0,1000';
    if($_POST['length'] != -1)
    {
      $limit = 'LIMIT ' . $_POST["start"] . ',' . $_POST["length"];
    }

    $order_by = '';
    $group = "";

    $requetedebase="SELECT INSTITUTION_SIGNATAIRE_ID,inst.CODE_INSTITUTION,inst.DESCRIPTION_INSTITUTION,st.CODE_SOUS_TUTEL,st.DESCRIPTION_SOUS_TUTEL,DESC_POSTE,NOM_PRENOM FROM inst_institutions_signataires_notes sign JOIN inst_institutions inst ON inst.INSTITUTION_ID=sign.INSTITUTION_ID LEFT JOIN inst_institutions_sous_tutel st ON st.SOUS_TUTEL_ID=sign.SOUS_TUTEL_ID WHERE 1";

    $order_column=array('DESCRIPTION_INSTITUTION','DESCRIPTION_SOUS_TUTEL','DESC_POSTE','NOM_PRENOM',1);

    $order_by = isset($_POST['order']) ? ' ORDER BY ' . $order_column[$_POST['order']['0']['column']] . '  ' . $_POST['order']['0']['dir'] : ' ORDER BY DESCRIPTION_INSTITUTION ASC';

    $search = !empty($_POST['search']['value']) ?  (" AND (DESCRIPTION_INSTITUTION LIKE '%$var_search%' OR DESCRIPTION_SOUS_TUTEL LIKE '%$var_search%' OR DESC_POSTE LIKE '%$var_search%' OR DESC_POSTE LIKE '%$var_search%')"):'';

    $critaire = $critere1." ".$critere3." ".$critere2;

    // print_r($critaire);die();
    //condition pour le query principale
    $conditions = $critaire ." ". $search ." ". $group ." ". $order_by . " " . $limit;
    
    // condition pour le query filter
    $conditionsfilter=$critaire." ".$search." ". $group ." ". $order_by . " " . $limit;
    
    $requetedebases=$requetedebase." ".$conditions;

    $requetedebasefilter=$requetedebase." ".$conditionsfilter;

    $query_secondaire = 'CALL getTable("'.$requetedebases.'");';

    // print_r($query_secondaire);exit();

    $fetch_actions = $this->ModelPs->datatable($query_secondaire);
    
    $u=1;
    $data = array();
    foreach ($fetch_actions as $row)
    {
      $sub_array=array();
      $sub_array[] = $u++;
      $sub_array[] = $row->DESCRIPTION_INSTITUTION;
      $sub_array[] = $row->DESCRIPTION_SOUS_TUTEL?$row->DESCRIPTION_SOUS_TUTEL:'-';
      $sub_array[] = $row->DESC_POSTE;
      $sub_array[] = $row->NOM_PRENOM;
      $sub_array[] = "<a href='".base_url('double_commande_new/Signataire_Note/get_update_view/'.$row->INSTITUTION_SIGNATAIRE_ID)."' class='btn btn-primary btn-sm' style='background: #556B2F;' title='Modifier'><span class='fa fa-edit'></span></a>";
      $data[] = $sub_array;
    }
    
    $recordsTotal = $this->ModelPs->datatable('CALL getTable("' .$requetedebases. '")');
    $recordsFiltered = $this->ModelPs->datatable('CALL getTable("' .$requetedebasefilter . '")');
    $output = array(
      "draw" => intval($_POST['draw']),
      "recordsTotal" => count($recordsTotal),
      "recordsFiltered" => count($recordsFiltered),
      "data" => $data,
    );

    return $this->response->setJSON($output);//echo json_encode($output);
  }
}
?>