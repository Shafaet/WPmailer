<?php
	class WPmailerDBhandler
	{
		function getDatabaseTableName()
		{
			global $wpdb;
			return $wpdb->prefix.'shaf_WPmailer';
		}
		function insertIntoTable($data)
		{
			global $wpdb;
			return $wpdb->insert($this->getDatabaseTableName(),$data);
		}
		function insertMessage($data)
		{
			$ret1 = $this->insertIntoTable($data);
			$data['sent_mail_flag']=1;
			$ret2 = $this->insertIntoTable($data);
			return $ret1 || $ret2;
		}
		function getNumberOfUnreadMessage()
		{
			global $wpdb;
			$dbtable=$this->getDatabaseTableName();
			$sql="SELECT * FROM $dbtable WHERE sent_mail_flag is NULL AND receiver=".get_current_user_id()." AND read_flag is NULL OR read_flag=0";
			$table_data = $wpdb->get_results($sql);
			return count($table_data);
		}
		function getCurrentUsersInbox()
		{
			
			global $wpdb;
			$dbtable=$this->getDatabaseTableName();
			$sql="SELECT * FROM $dbtable WHERE sent_mail_flag is NULL AND receiver=".get_current_user_id();
			$table_data = $wpdb->get_results($sql);
			return $table_data;

		}
		function getCurrentUsersOutbox()
		{
			
			global $wpdb;
			$dbtable=$this->getDatabaseTableName();
			$sql="SELECT * FROM $dbtable WHERE sent_mail_flag=1 AND sender=".get_current_user_id();
			$table_data = $wpdb->get_results($sql);
			return $table_data;

		}
		function getMessage($message_id)
		{
			global $wpdb;
			$table=$this->getDatabaseTableName();
			$sql="SELECT * FROM $table WHERE id=".$message_id;
			$table_data = $wpdb->get_results($sql);
			if(count($table_data)==0)return NULL;
			foreach($table_data as $row)return $row;
		}
		function deleteMessage($message_id)
		{
			global $wpdb;
			$table=$this->getDatabaseTableName();
			return $wpdb->delete($table, array('id' => $message_id)); 
		}
		function markRead($message_id)
		{
			global $wpdb;
			$table=$this->getDatabaseTableName();
			return $wpdb->update($table, array('read_flag' => 1),array('id' => $message_id)); 
		
		}
		function markUnread($message_id)
		{
			global $wpdb;
			$table=$this->getDatabaseTableName();
			return $wpdb->update($table, array('read_flag' => NULL),array('id' => $message_id)); 
		
		}
	}

?>

