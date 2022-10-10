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
   
   public function get_mngrId($table) 
   { 
     $this->db->select("*");    
     $this->db->group_by('manager');
     $query = $this->db->get($table);
     return $query->result();
   }

   public function get_agentId($table) 
   { 
     $this->db->select("*");    
     $this->db->group_by('agent'); 
     $query = $this->db->get($table);
     return $query->result();
   }

   public function get_clients($table) 
    { 
      $this->db->select("id,image,name,created_date");    
      // $this->db->order_by("created_date", "desc");
      $query = $this->db->get($table);
      // print_r($query->result());
      // die();
      return $query->result();
    } 

    public function get_clients_temp($table) 
    { 
      $this->db->select("tbl_clients.id,tbl_clients.image,tbl_clients.name,tbl_clients.created_date, tbl_pms.name as pms_name");    
      // $this->db->order_by("created_date", "desc");
      $this->db->from('tbl_clients');
      $this->db->join('tbl_pms', 'tbl_pms.id = tbl_clients.pms_id');
      $query = $this->db->get();
      // print_r($query->result());
      // die();
      return $query->result();
    }

    public function get_clients_temp1($table, $skip, $limit) 
    { 
      $this->db->select("tbl_clients.id,tbl_clients.image,tbl_clients.name,tbl_clients.created_date, tbl_pms.name as pms_name");    
      // $this->db->order_by("created_date", "desc");
      // $this->db->from('tbl_clients');
      $this->db->join('tbl_pms', 'tbl_pms.id = tbl_clients.pms_id');
      $query = $this->db->get('tbl_clients', $limit, $skip);
      return $query->result();
    }

  //   public function getOutputDataWhere($skip, $limit, $field, $arr) 
  //  {
  //     $this->db->order_by("inserted_date", "desc");
  //     $this->db->where_in($field, $arr);
  //     $query = $this->db->get('tbl_output', $limit, $skip);
  //     return $query->result_array();
  //  }

    public function get_clients_pms_temp($table) 
    { 
      $this->db->select("*");    
      // $this->db->order_by("created_date", "desc");
      $query = $this->db->get($table);
      // print_r($query->result());
      // die();
      return $query->result();
    }

    public function get_clients_pms_temp1($table, $skip, $limit) 
    { 
      $this->db->select("*");
      $query = $this->db->get($table, $limit, $skip);
      return $query->result();
    }

    public function get_clients_ByID($table, $field, $data) 
    { 
      $this->db->select("*"); 
      $this->db->from($table);
      $this->db->where($field,$data);
      $query = $this->db->get();
      if(count($query->result_array())==1){
        return $query->result_array()[0];
      }
      else{
        return $query->result_array();
      }
    }

    

   public function getCount($table){
	return $this->db->count_all_results($table);
   }
   public function getCountWhereIn($table, $field, $arr){
    //  $this->db->count_all_results($table);
     $this->db->select("*"); 
     $this->db->where_in($field,$arr);
     $query = $this->db->get($table);
     return $query->result();
    }

   public function getCountPms($table,$id){
      $where = "pms_id='Joe' AND status='boss' OR status='active'";
      $this->db->where('pms_id',$id);
      $query = $this->db->get($table);
      return $query->num_rows();
   }

   public function check_client($table,$name){
     
     $this->db->select('*');
     $this->db->from($table);
     $this->db->where('name', $name);
     $query = $this->db->get();
     if ($query->num_rows() > 0) {
         return $query->result();
     } else {
         return false;
     }
      
   }

   public function create($table,$data){

      $this->db->insert($table, $data);
      return $this->db->insert_id();
   }

   public function getOutputData($skip, $limit) 
   {
      // $this->db->select('*');
      // $this->db->from('tbl_output');
      // $this->db->join('tbl_users', 'tbl_users.id = tbl_output.Name');
      $this->db->order_by("inserted_date", "desc");
      $query = $this->db->get('tbl_output', $limit, $skip);
      // print_r($query->result_array());
      return $query->result_array();
   }

   public function getOutputDataWhere($skip, $limit, $field, $arr) 
   {
      $this->db->order_by("inserted_date", "desc");
      $this->db->where_in($field, $arr);
      $query = $this->db->get('tbl_output', $limit, $skip);
      return $query->result_array();
   }

   public function getOutputDataWhereId($skip, $limit, $field, $id) 
   {
      $this->db->order_by("inserted_date", "desc");
      $this->db->where_in($field, $id);
      $query = $this->db->get('tbl_output', $limit, $skip);
      return $query->result_array();
   }

   public function mgetOutputData($arr) 
   {
     $new_arr = array();
     foreach ($arr as $row) {
      array_push($new_arr, array(
        "data"=> $row,
        "client" => $this->get_all_byID('tbl_clients', $row['Facility']),
        "user" => $this->get_all_byID('tbl_users', $row['Name']),
        "pms" => $this->get_all_byID('tbl_pms', $row['pms_id']),
        "assigned_to" => $row['assigned_to'] ? $this->get_all_byID('tbl_users', $row['assigned_to']) : NULL,

        "status_name" => $row['StatusName'] ? $this->get_all_byID('status', $row['StatusName']) : NULL,
        "sub_status" => $row['SubStatus'] ? $this->get_all_byID('sub_status', $row['SubStatus']) : NULL,
        "action_code" => $row['ActionCode'] ? $this->get_all_byID('action_code', $row['ActionCode']) : NULL,
        "account_type" => $row['AccountType'] ? $this->get_all_byID('account_type', $row['AccountType']) : NULL,
      ));
     }
     return $new_arr;
   }

   public function mgetOutputData2($arr) 
   {
     $new_arr = array();
     foreach ($arr as $row) {
      array_push($new_arr, array(
        "data"=> $row,
        "client" => $this->get_all_byID('tbl_clients', $row->Facility),
        "user" => $this->get_all_byID('tbl_users', $row->Name),
        "pms" => $this->get_all_byID('tbl_pms', $row->pms_id),
        "assigned_to" => $row->assigned_to ? $this->get_all_byID('tbl_users', $row->assigned_to) : NULL,
        "status_name" => $row->StatusName ? $this->get_all_byID('status', $row->StatusName) : NULL,
        "sub_status" => $row->SubStatus ? $this->get_all_byID('sub_status', $row->SubStatus) : NULL,
        "action_code" => $row->ActionCode ? $this->get_all_byID('action_code', $row->ActionCode) : NULL,
        "account_type" => $row->AccountType ? $this->get_all_byID('account_type', $row->AccountType) : NULL,

        "all_status_name" => $this->get_all_data('status'),
        "all_sub_status" => $this->get_all_data('sub_status'),
        "all_action_code" => $this->get_all_data('action_code'),
        "all_account_type" => $this->get_all_data('account_type')
      ));
     }
    //  return $new_arr[0];
     return $new_arr;
   }

   public function get_all_byID($table, $id) 
   { 
      // $where = "id='$id' AND map_col_id is NOT NULL";
     $this->db->select("*");
     $this->db->from($table);
     $this->db->where('id', $id);
     $query = $this->db->get();
     return $query->result()[0];
   }
  
   //exel outputfile data get by id 
   public function outputfileGet($table,$id) 
   {
      $this->db->select("*");
      $this->db->from($table);
      $this->db->where("id", $id); 
      $query = $this->db->get();
  
      return $query->result();
   } 

   function is_record_exists($table, $id)
   {
      $this->db->where('id',$id);
      $query = $this->db->get($table);
      if ($query->num_rows() > 0){
         return true;
      }
      else{
         return false;
      }
   }


   //delete exel outputfile data by id 
   public function outputfileDeletedById($table,$id) 
   {
      
      if($this->is_record_exists($table, $id) == true){
         $this->db->where('id', $id);
         $this->db->delete($table);
         return array(
            'status' => 'success',
            'message' => 'record has been deleted',
        );
      }
      else{
         return array(
            'status' => 'error',
            'message' => 'id not exist!',
        );
      }
   }


   public function deletedByIdMul($table,$ids) 
   {
      for($i=0; $i<count($ids); $i++){
         if($this->is_record_exists($table, $ids[$i]) == true){
            $this->db->where('id', $ids[$i]);
            $this->db->delete($table);
         }
      }
      return array(
         'status' => 'success',
         'message' => 'record has been deleted',
     );
   }

   public function updateByIdMul($is_data) 
   {
          // Check so incoming data is actually an array and not empty
    if (is_array($is_data) && ! empty($is_data))
    {
      $this->db->update_batch('tbl_output', $is_data, 'id');
      return array(
         'status' => 'success',
         'message' => 'record has been updated',
      );
    }
    return false;
   }


   //edit exel outputfile data by id 
   public function outputfileEdit($table,$id,$data) 
   {
   //   print_r($data);
     if($this->is_record_exists($table, $id,$data) == true){
      $this->db->where('id', $id);
      return $this->db->update($table, $data); 
     }
     else{
      return false;
     }
      
   }
 
   public function get_data($table, $role) 
   { 
     $this->db->select("id,name,email,mobile,username,role,created_at");
     $this->db->from($table);
     $this->db->where('role', $role);
     $query = $this->db->get();
     return $query->result();
   }

   public function get_data2($table, $field, $id) 
   { 
     $this->db->select("users.*");
     $this->db->from($table);
     $this->db->join('tbl_users users', 'users.id = manager_agent.agent');
     $this->db->where($field, $id);
     $query = $this->db->get();
     return $query->result();
   }

   public function insert_data($table, $data){
     // echo json_encode($data);
     for ($i=0; $i < count($data); $i++) { 
        $this->db->insert($table,$data[$i]);
     }
        // $this->db->insert_batch($table,$data);
         return true;
   }


   public function get_count($table){
      $this->db->select('COUNT(*) as count');
      $this->db->from($table);
      $query = $this->db->get();
      return $query->result();
   }

   public function get_where($table, $field, $data){
      $this->db->select('*');
      $this->db->from('agent_claim');
      // $this->db->from($table);
      $this->db->join('tbl_output', 'tbl_output.id = agent_claim.claim_id');
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result();
    }
    public function get_where_new($table, $field, $data){
      $this->db->select('*');
      $this->db->from($table);
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result_array();
    }

    public function get_where2($table, $field, $data){
      $this->db->select('*');
      $this->db->from('manager_agent');
      // $this->db->from($table);
      $this->db->join('tbl_users', 'tbl_users.id = manager_agent.agent');
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result_array();
    }

    public function get_where3($table, $field, $data){
      $this->db->select('*');
      $this->db->from('manager_agent');
      $this->db->join('tbl_clients', 'tbl_clients.id = manager_agent.client');
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result_array();
    }

    public function get_where_temp($table, $field, $data){
      $this->db->select('*');
      $this->db->from($table);
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result();
    }
    public function get_where_temp_agent($table, $field, $data){

      $this->db->select('*');
      $this->db->from($table);
      $this->db->join('tbl_users', 'tbl_users.id = manager_agent.agent');
      $this->db->where($field, $data->id);
      $query = $this->db->get();
      return $query->result();
    }
    public function get_where_temp_client($table, $field, $data){
      $this->db->select('*');
      $this->db->from($table);
      // if($data->client)
      $this->db->join('tbl_clients', 'tbl_clients.id = manager_agent.client');
      $this->db->where($field, $data->id);
      $query = $this->db->get();
      return $query->result();
    }

    public function get_where_temp_client2($table, $field, $data){
      $this->db->select('agent_client.id, agent_client.manager_id, agent_client.client_id, agent_client.agent_id, client.image, client.name, client.pms_id');
      $this->db->from($table);
      // if($data->client)
      $this->db->join('tbl_clients as client', 'client.id = agent_client.client_id');
      $this->db->where($field, $data->manager);
      $this->db->where('agent_id', $data->agent);
      $query = $this->db->get();
      return $query->result();
    }
 

   public function get_all_data($table) 
   { 
     $this->db->select("*");
     $this->db->from($table);
     $query = $this->db->get();
     return $query->result();
   }

   public function delete_where($table,$field, $data) 
   {
      
       
     $this->db->where('manager', $data['manager_id']);
     $this->db->where($field, $data[$field]);
     $this->db->delete($table);
     
     return array(
        'status' => 'success',
        'message' => 'record has been deleted',
     );
     
   }


   public function delete_where_id($table,$field, $id) 
   {
      
         $this->db->where($field, $id);
         $this->db->delete($table);
   }
   
   public function get_whereJ_SF($table, $field, $data, $fields){
      $where = "pms_id='$data' AND map_col_id is NOT NULL";
      $this->db->select($fields);
      $this->db->from($table);
      $this->db->where($where);
      $query = $this->db->get();
      return $query->result();
    }

    public function get_where_SF($table, $field, $data, $fields){
      $this->db->select($fields);
      $this->db->from($table);
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result();
    }

    public function update_table($table,$field,$data) 
    {
      // $where = "id='Joe' AND status='boss' ";
       $this->db->where('id',$data['id']);
       return $this->db->update($table, array('assigned_to' => NULL)); 
      
       
    }

    public function update_table2($table,$data) 
    {
      // $where = "id='Joe' AND status='boss' ";
       $this->db->where('id',$data['id']);
       return $this->db->update($table, array('assigned_to' => $data['assigned_to'])); 
      
       
    }

    public function delete_where2($table,$field, $data) 
   {
      
       
     $this->db->where('manager_id', $data['manager_id']);
     $this->db->where('client_id', $data['client_id']);
     $this->db->where($field, $data[$field]);
     $this->db->delete($table);
     
     return array(
        'status' => 'success',
        'message' => 'record has been deleted',
     );
     
   }

   public function get_where_not_null($table, $field, $data, $extra){
    
    if($extra == 'client'){
      $this->db->select('manager_agent.id, manager_agent.manager, manager_agent.client, client.name as client_name');
      $this->db->from($table);
      $this->db->join('tbl_clients as client', 'client.id = manager_agent.client');
      $where = "manager='$data' AND client is NOT NULL";
    }
    if($extra == 'agent')
    {
      $this->db->select('manager_agent.id, manager_agent.manager, manager_agent.agent, user.name as agent_name');
      $this->db->from($table);
      $this->db->join('tbl_users as user', 'user.id = manager_agent.agent');
      $where = "manager='$data' AND agent is NOT NULL";
    }
    $this->db->where($where);
    $query = $this->db->get();
    return $query->result();
  }

  public function update_tbl_output($table, $field, $clients_arr, $agent_id, $pms) 
   {
    // $where = "Facility in (".implode(', ', $clients_arr).") AND pms_id in (".implode(', ', $pms).")";
      $SQL = "UPDATE `tbl_output` SET `assigned_to` = ".$agent_id." WHERE `Facility` IN (".implode(', ', $clients_arr).") AND `pms_id` IN (".implode(', ', $pms).")";
      // $data = array(
      //     'assigned_to' =>  $agent_id
      // );
      // $this->db->where($where);
      //not for comment
      // $this->db->where_in($field, $clients_arr);
      // need to comment
      // $this->db->where_in('pms_id', $pms);
      // $this->db->update($table, $data); 
      $query = $this->db->query($SQL);
      $query->result_array();
      return $SQL;
      
   }

   public function del_tbl_output($table, $field, $clients_id, $agent_id, $pms_id) 
   {
      $data = array(
          'assigned_to' => NULL
      );
      $this->db->where($field, $clients_id);
      $this->db->where('assigned_to', $agent_id);
      $this->db->where('pms_id', $pms_id);
      return $this->db->update($table, $data); 
      
   }

   public function get_unique_clients(){
    $arr = [];
      $where = "client is NOT NULL";
      $this->db->select("client");
      $this->db->from('manager_agent');
      $this->db->where($where);
      $query = $this->db->get();
      foreach($query->result_array() as $c){
        array_push($arr, $c['client']);
      };
      return $arr;
   }
   public function get_unique_agents(){
    $arr = [];
      $where = "agent is NOT NULL";
      $this->db->select("agent");
      $this->db->from('manager_agent');
      $this->db->where($where);
      $query = $this->db->get();
      foreach($query->result_array() as $c){
        array_push($arr, $c['agent']);
      };
      return $arr;
   }

   public function get_all_unassign_data($table, $for) 
   { 
     $this->db->select("*");
     if($for == 'agent'){
      if(count($this->get_unique_agents()) > 0){
         $this->db->where_not_in('id', $this->get_unique_agents());
      }
       $this->db->where('role', 3);
     }
     else if($for == 'client'){
        if(count($this->get_unique_clients()) > 0){
          $this->db->where_not_in('id', $this->get_unique_clients());
        }
     }
     $query = $this->db->get($table);
     return $query->result();
   }
}

 ?>