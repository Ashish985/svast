<?php

class Admin_model extends CI_Model{


  public function insertFileData($table='',$data='')
   {
		if(!empty($data) && !empty($table)){
          $this->db->insert($table,$data);
          return $this->db->insert_id();
		}else{
			return false;
		}
   }

   public function getCount($table){
	return $this->db->count_all_results('tbl_output');
   }

   public function getOutputData($skip, $limit) 
   {
      $this->db->order_by("inserted_date", "desc");
      $query = $this->db->get('tbl_output', $limit, $skip);
	// print_r($query->result_array());
	return $query->result_array();
   }


}

 ?>