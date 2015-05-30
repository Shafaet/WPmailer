
<div class="wrap">
<p><font size=6><b>WPmailer</b></font></p>
<script src= "http://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
<?php
	$css_link=plugins_url('/mail_style.css',__FILE__) ;
	echo "<link rel='stylesheet' type='text/css' href=$css_link />";
	
	require_once(dirname(__FILE__) . '/mail_functions.php');
	require_once(dirname(__FILE__) . '/mail_buttons.php');
	require_once(dirname(__FILE__) . '/mail_notifications.php');
	require_once(dirname(__FILE__) . '/mail_dbconfig.php');
	
	class WPmailerAdmin
	{
		public function __construct()
		{
			$this->buttons=new WPmailerButtons();
			$this->dbHandler=new WPmailerDBhandler();
		}
		function homePage()
		{
			
			//getUnreadMessageNotification();
			$this->buttons->sendNewMessageButton();	
			$this->buttons->inboxButton();
			$this->buttons->sentMailButton();
		}
		function sendNewMessage()
		{
			//getUnreadMessageNotification();
			$this->buttons->getHomeButton();
			$this->buttons->inboxButton();
			$this->buttons->sentMailButton();
		
			
			echo "<div class=editor_area />";
			echo "<p><b>Send New Message</b></p>";
			$action_url=$_SERVER['PHP_SELF'];
			echo "<form ng-app='messageFormApp' ng-controller='validateCtrl' name='newMessageForm' novalidate action=$action_url >";
			
			
			echo "	<input type=hidden name='deliver_msg' value='true' />
				<input type=hidden name='page' value='WPmailer' />";
			
			if(isset($_REQUEST['reply_to_user_id']))
			{
				$reply_to_user_id=$_REQUEST['reply_to_user_id'];
				$reply_to_user_name=getDisplayName($reply_to_user_id);
				echo "<input type=hidden name='user' value=$reply_to_user_id />" ;
				echo "<p>Send message to: ".$reply_to_user_name."<br>";
			}
			else
			{
				wp_dropdown_users(array('show_option_none'=>'Select a recipient'));
				echo "<br>";
			}
			
			echo "<input type=text ng-change='printError()' name=subject size='80' ng-model='subject' required pattern='[A-Za-z0-9 ()?!*.@-]+' />";
			echo "<p class=form_error>{{subject_error_msg}}</p>";
			echo "<p class=form_good>{{subject_thanks_msg}}</p>";
			
			$editor_options=array('media_buttons'=>false,'quicktags'=>false,'textarea_name'=>'message_body','textarea_rows'=>15);
			wp_editor("Write your message here","message_body",$editor_options);
			echo "<br>";
			echo "<input class=btn type=submit value='Send' ng-disabled='newMessageForm.subject.\$dirty && newMessageForm.subject.\$invalid' />";
			
			echo "</form>";
			echo "</div>";

		}
		function deliverMessage()
		{
				
				$message_body=base64_url_encode($_REQUEST['message_body']);
				$receiver=$_REQUEST['user'];
				$subject=$_REQUEST['subject'];
				$sender=get_current_user_id();
				$data=array('body' => $message_body, 'sender'=>$sender,'receiver'=>$receiver,'subject'=>$subject);
				
				if($this->dbHandler->insertMessage($data))
				{
					messageSentNotification();
				}
				else
				{
					echo "<br><font color=red font size=5>Ohh no! Message couldn't be sent!</font><br>";
				}
				echo "<br>";
			//	getUnreadMessageNotification();
		
				$this->buttons->getHomeButton();
				$this->buttons->inboxButton();
				$this->buttons->sentMailButton();
				$this->buttons->sendNewMessageButton();
				
		
		}
		function inbox()
		{
			//getUnreadMessageNotification();
			$this->buttons->getHomeButton();
			$this->buttons->inboxRefreshButton();
			$this->buttons->sentMailButton();
			$this->buttons->sendNewMessageButton();
			
			$table_data = $this->dbHandler->getCurrentUsersInbox();
			echo "<div class=table_area>";
			if(count($table_data)==0)
			{
			    echo "<p>Seems like your inbox is empty!</p>";
			}
			else
			{
				
				echo "<table border=1>";
				echo "<tr class=first_row >";
				echo "<td width=150>Time</td>";
				echo "<td width=100>Sender</td>";
				echo "<td width=150>Subject</td>";
				echo "<td width=100>Status</td>";
				echo "<td width=100>Options</td>";
				echo "</tr>";
				 
				foreach($table_data as $row)
				{
					$time=$row->time;
					$subject=$row->subject;
					$status=$row->read_flag;
					$id=$row->id;
					$sender_id=$row->sender;
					echo "<tr>";
					echo "<td>$time</td>";
					echo "<td>".getDisplayName($sender_id)."</td>";
					echo "<td><a href='?page=WPmailer&show_message=true&message_id=$id'>$subject</a></td>";
					if($status==NULL) echo "<td><div class=form_error>Unread</div></td>";
					else              echo "<td><div class=form_good>Old</div></td>";
				
					$delete_icon_url=plugins_url('images/delete-icon.png', __FILE__ );
					echo "<td><a href='?page=WPmailer&delete_message=true&message_id=$id'><img src=$delete_icon_url /> </a></td>";
					echo "</tr>";
				}
				echo "</table>";
			echo "</div>";
			}
		

		}
		function sent_mails()
		{
			//getUnreadMessageNotification();
			$this->buttons->getHomeButton();
			$this->buttons->inboxButton();
			$this->buttons->sentMailButton();
			$this->buttons->sendNewMessageButton();
			
			$table_data = $this->dbHandler->getCurrentUsersOutbox();
			echo "<div class=table_area>";
			if(count($table_data)==0)
			{
			    echo "<p>Seems like your Outbox is empty!</p>";
			}
			else
			{
				
				echo "<table border=1>";
				echo "<tr class=first_row >";
				echo "<td width=150>Time</td>";
				echo "<td width=100>Sent to</td>";
				echo "<td width=150>Subject</td>";
				echo "<td width=100>Options</td>";
				echo "</tr>";
				 
				foreach($table_data as $row)
				{
					$time=$row->time;
					$subject=$row->subject;
					$id=$row->id;
					$receiver_id= $row->receiver;
					echo "<tr>";
					echo "<td>$time</td>";
					echo "<td>".getDisplayname($receiver_id)."</td>";
					echo "<td><a href='?page=WPmailer&show_message=true&message_id=$id'>$subject</a></td>";
				
					$delete_icon_url=plugins_url('images/delete-icon.png', __FILE__ );
					echo "<td><a href='?page=WPmailer&delete_message=true&message_id=$id&del_outbox=true'><img src=$delete_icon_url /> </a></td>";
					echo "</tr>";
				}
				echo "</table>";
			echo "</div>";
			}
		
			
		}
		function deleteMessage()
		{
			if(isset($_REQUEST['message_id']))
			{
				$message_id=$_REQUEST['message_id'];
				if($this->dbHandler->deleteMessage($message_id))
				{
					echo "<div class=notification_msg_delete><p>Deleted one message!</p></div><br>";
					
				}
			}
			if(isset($_REQUEST['del_outbox']))
				$this->sent_mails();
			else
				$this->inbox();

		}
		function showMessage()
		{
			//getUnreadMessageNotification();
			
			if(isset($_REQUEST['message_id']))
			{
				
				$message_id=$_REQUEST['message_id'];
				$row=$this->dbHandler->getMessage($message_id);
				if($row==NULL)
				{
			    		echo "<p>Seems like you are trying to do something wrong!</p>";
				}
				else
				{
					
					$id=$row->id;
					$time=$row->time;
					$sender_id=$row->sender;
					$receiver_id=$row->receiver;
					$subject=$row->subject;
					$read_flag=$row->read_flag;
					$body=urldecode(base64_url_decode($row->body));
					
					if($receiver_id!=get_current_user_id())
					{
						echo "<p>Nice try! But you don't have the permission to see view this page</p>";
						exit(0);
					}
					if(isset($_REQUEST['mark_read']))
					{
						if($this->dbHandler->markRead($id))
						{
							markedOldNotification();
							$read_flag=1;
						}
					}
					if(isset($_REQUEST['mark_unread']))
					{
						
						if($this->dbHandler->markUnread($id))
						{
		
							markedUnreadNotification();
							$read_flag=0;
						}
					}
					$this->buttons->getHomeButton();
					$this->buttons->sendNewMessageButton();	
					$this->buttons->inboxButton();
					$this->buttons->sentMailButton();
					echo "<div class=no_float></div>";
					echo "<div class=message_area>";
						echo "<p><div class='msg_header'>Sent by: </div>".getDisplayName($sender_id)."<div class='msg_header'>&nbsp&nbsp&nbsp&nbsp&nbspTime: </div>". $time."</p>";
						echo "<div class=message_body>$body</div><br>";
						$delete_icon_url=plugins_url('images/delete-icon.png', __FILE__ );
						$this->buttons->replyButton($sender_id);
						echo "<a href='?page=WPmailer&delete_message=true&message_id=$id'><button class=btn-small >Delete</button></a>";
						if($read_flag==NULL)
						$this->buttons->markAsReadButton($id);
						else
						$this->buttons->markAsUnreadButton($id);
						
					echo "</div>";
				}
				
			
		
			}
			else
			{
				$this->buttons->getHomeButton();
				echo "<br><p>Error! Message id missing</p>";
			}
			
		
		
		}
	
	
	}
	
	$mailerAdmin=new WPmailerAdmin();
	if(isset($_REQUEST['send_new']))
	{
		$mailerAdmin->sendNewMessage();
	}
	else if(isset($_REQUEST['deliver_msg']))
	{
		$mailerAdmin->deliverMessage();
	}
	else if(isset($_REQUEST['inbox']))
	{
		$mailerAdmin->inbox();
	}
	else if(isset($_REQUEST['show_message']))
	{
		$mailerAdmin->showMessage();
	}
	else if(isset($_REQUEST['delete_message']))
	{
		$mailerAdmin->deleteMessage();
	}
	else if(isset($_REQUEST['sent_mails']))
	{
		$mailerAdmin->sent_mails();
	}
	else
	{
		
		$mailerAdmin->homePage();
	}
		
		

?>.


<script>
var app = angular.module('messageFormApp', []);
app.controller('validateCtrl', function($scope) {
    $scope.subject = 'Message Subject';
    var subject_was_invalid=0;
    $scope.printError = function()
    {
    	   if($scope.newMessageForm.$error.required)
    	   {
               $scope.subject_error_msg="Please write a message subject.";
               $scope.subject_thanks_msg="";
               subject_was_invalid=1;
           }
           else if($scope.newMessageForm.subject.$invalid)
           {
               $scope.subject_error_msg="Only these characters are valid: [A-Za-z0-9 ()?!*.@-]";
               $scope.subject_thanks_msg="";
               subject_was_invalid=1;
           }
           else
           {
           	$scope.subject_error_msg="";
           	if(subject_was_invalid)
           	$scope.subject_thanks_msg="Thanks!";
           }
    } 

});
</script>

