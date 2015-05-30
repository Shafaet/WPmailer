<?php
	/*
		All database interaction should be made through this class.
		
	*/
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
			/*
				For each sent message two messages will be inserted.
				One in senders outbox and one is recievers outbox.
				send_mail_flag is 1 if the message is inserted in the outbox.
			*/
			$ret1 = $this->insertIntoTable($data);
			$data['sent_mail_flag']=1;
			$ret2 = $this->insertIntoTable($data);
			return $ret1 || $ret2;
		}
		function getNumberOfUnreadMessage()
		{
			/*
				A message is unread if read_flag in the database is either 0 or NULL.
			*/
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
			$sql="SELECT * FROM $dbtable WHERE sent_mail_flag is NULL AND receiver=".get_current_user_id()." ORDER BY time";
			$table_data = $wpdb->get_results($sql);
			return $table_data;

		}
		function getCurrentUsersOutbox()
		{
			/*
				sent_mail_flag=1 indicates that the message is in the outbox of current user,
			*/
			global $wpdb;
			$dbtable=$this->getDatabaseTableName();
			$sql="SELECT * FROM $dbtable WHERE sent_mail_flag=1 AND sender=".get_current_user_id()." ORDER BY time";
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
		function getMessageOwner($message_id)
		{
			/*
				If this is a 'sent mail', sender is the owner
				If this is a recieved mail, reciever is the owner
			*/
			$row=$this->getMessage($message_id);
			if($row==NULL) return -1;
			$sent_mail_flag=$row->sent_mail_flag;	
			if($sent_mail_flag==NULL) $owner=$row->receiver;
			else $owner=$row->sender;
			return $owner;
		}
		function verifyMessageOwner($message_id)
		{
			$owner=$this->getMessageOwner($message_id);
			if(get_current_user_id()!=$owner)return false;
			return true;
		}	
		function deleteMessage($message_id)
		{
			if($this->verifyMessageOwner($message_id)==false)return false;
			global $wpdb;
			$table=$this->getDatabaseTableName();
			return $wpdb->delete($table, array('id' => $message_id)); 
		}
		function markRead($message_id)
		{
			if($this->verifyMessageOwner($message_id)==false)return false;
			global $wpdb;
			$table=$this->getDatabaseTableName();
			return $wpdb->update($table, array('read_flag' => 1),array('id' => $message_id)); 
		
		}
		function markUnread($message_id)
		{
			if($this->verifyMessageOwner($message_id)==false)return false;
			global $wpdb;
			$table=$this->getDatabaseTableName();
			return $wpdb->update($table, array('read_flag' => NULL),array('id' => $message_id)); 
		
		}
	}

?>

