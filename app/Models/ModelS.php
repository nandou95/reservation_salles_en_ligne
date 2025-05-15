<?php
/**
 * @author julesyuliyusi@gmail.com
 * Model sans procedure stockee
 * Refere to this https://codeigniter4.github.io/userguide/database/query_builder.html?highlight=query%20builder#insertbatch
 */
namespace App\Models;

use CodeIgniter\Model;

class ModelS extends Model
{   
	//make query
    public function maker($requete)
    {
        return $this->db->query($requete);
    }
    //make_datatables : requete avec Condition,LIMIT start,length
    public function datatable($requete)
    {   
        $query =$this->maker($requete);//call function make query
        return $query->getResult();
    }
    //count_all_data : requete sans Condition sans LIMIT start,length
    public function all_data($requete)
    {
       $query =$this->maker($requete); //call function make query
       return $query->getNumRows();
    }
     //get_filtered_data : requete avec Condition sans LIMIT start,length
    public function filtrer($requete)
    {
         $query =$this->maker($requete);//call function make query
         return $query->getNumRows();

    }	
    public function create($table,$data){
        $sql=$this->db->table($table)->insert($data);
        return $sql ;
    }

}
?>