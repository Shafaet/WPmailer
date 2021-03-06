<?php
	/*
	 * This file should be used to generate any buttons.
	 * All database interaction should be done using dbconfig.php
	 */
	require_once(dirname(__FILE__) . '/mail_dbconfig.php');
	class WPmailerButtons
	{
		function getHomeButton()
		{
			echo "
			<form>
			<input class=btn type=submit value='WPmailer Home' />
			<input type=hidden name='page' value='WPmailer' />
			</form>
			";
		}
		function replyButton($sender_id, $sent_mail_flag)
		{
			if($sent_mail_flag!=NULL)return;
			echo "<a href=?page=WPmailer&send_new=true&reply_to_user_id=$sender_id> <button class=btn-small >Reply</button></a>";
		}
		function markAsReadButton($id, $sent_mail_flag)
		{
			if($sent_mail_flag!=NULL)return;
			echo "<a href='?page=WPmailer&show_message=true&message_id=$id&mark_read=true'><button class=btn-small >Mark as old</button></a>";
		}
		function markAsUnreadButton($id, $sent_mail_flag)
		{
			if($sent_mail_flag!=NULL)return;
			echo "<a href='?page=WPmailer&show_message=true&message_id=$id&mark_unread=true'><button class=btn-small >Mark as unread</button></a>";
		}
		function sendNewMessageButton()
		{
			echo "
			<form>
			<input class=btn type=submit value='New message' />
			<input type=hidden name='send_new' value='true' />
			<input type=hidden name='page' value='WPmailer' />
			</form>
			";
		
		}
		function inboxButton($echo=true)
		{
			$dbHandler=new WPmailerDBhandler();		
			$unread=$dbHandler->getNumberOfUnreadMessage();
			
			$button="
			<form>
			<input class=btn type=submit value='Inbox($unread)' />
			<input type=hidden name='inbox' value='true' />
			<input type=hidden name='page' value='WPmailer' />
			</form>
			";
			if($echo==true) echo $button;
			return $button;
		}

		function sentMailButton()
		{
			echo "
			<form>
			<input class=btn type=submit value='Sent mails' />
			<input type=hidden name='sent_mails' value='true' />
			<input type=hidden name='page' value='WPmailer' />
			</form>
			";
		}		
		function inboxRefreshButton()
		{
			echo "
			<form>
			<input class=btn type=submit value='Refresh Inbox' />
			<input type=hidden name='inbox' value='true' />
			<input type=hidden name='page' value='WPmailer' />
			</form>
			";
	
		}
		function deleteButtonInShowMessage($message_id, $sent_mail_flag)
		{
		        	
			$concat_del_outbox="";
			if($sent_mail_flag!=NULL)
				$concat_del_outbox='&del_outbox=true';
			echo "<a href='?page=WPmailer&delete_message=true&message_id=$message_id$concat_del_outbox'><button class=btn-small >Delete</button></a>";
		
		}
	}


?>
