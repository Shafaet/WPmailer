
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
			//getUnreadMessageNotification()
			$this->buttons->inboxButton();
			$this->buttons->sentMailButton();
			$this->buttons->sendNewMessageButton();	
			if(current_user_can('activate_plugins'))
			{
				echo "<div class=WPmailer_admin_area >";
				echo "<p class=WPmailer_bigbold>Admin options</p>";
				echo "<p class=WPmailer_boldinline>Activating WPmailer in widgets:</p>";
				echo "<p>Paste this shortcode in a text widget [WPmailer_menu]</p>";
				echo "</div>";
			}
		}
		/*
		   Call this function to send a new message using following parameters:
		   	* send_new = true
		   	* reply_to_user_id (Optional)
		*/
		function sendNewMessage()
		{
			//getUnreadMessageNotification();
			$this->buttons->getHomeButton();
			$this->buttons->inboxButton();
			$this->buttons->sentMailButton();
		
			
			echo "<div class=editor_area />";
			echo "<p><b>Send New Message</b></p>";
			$action_url=$_SERVER['PHP_SELF'];
			/*
			    The following 'Send message' form is an angularJS app.
			    On submit, deliverMessage() function will be called with following parameters:
			    	* deliver_msg = True
			    	* token
			    	* user (Recipient)
			    	* subject
			    	* message_body
			    
			    The token will be stored in a session. the deliverMessage function will match
			    the token in url with the token in session before delivering the message.
			*/
			echo "<form method=POST ng-app='messageFormApp' ng-controller='validateCtrl' name='newMessageForm' novalidate action=$action_url?page=WPmailer onsubmit='return validateMenuSelection();'>";
			
			echo "<input type=hidden name='deliver_msg' value='true' />";
			
			if(isset($_REQUEST['reply_to_user_id']))
			{
			 	/*
			 	  If this parameter is set, the message form will be used to reply to a user.
			 	*/
				$reply_to_user_id=$_REQUEST['reply_to_user_id'];
				$reply_to_user_name=getDisplayName($reply_to_user_id);
				echo "<input type=hidden name='user' value=$reply_to_user_id />" ;
				echo "<p>Send message to: ".$reply_to_user_name."<br>";
			}
			else
			{
				wp_dropdown_users(array('show_option_none'=>'Select a recipient','id'=>'WPmailer_user_list'));
				echo "<br>";
				if(current_user_can('activate_plugins'))
				{
					//echo "<input type=checkbox name=send_to_all value=True >Send to everyone (This option is visible to admins only)<br>";
				}
			}
			$token=uniqid(get_current_user_id()).bin2hex(openssl_random_pseudo_bytes(16));
			$_SESSION["WPmailer_send_mail_token"]=$token;
			echo "<input type=text ng-change='printError()' name=subject size='80' ng-model='subject' required pattern='[A-Za-z0-9 ()?!*.@-]+' />";
			echo "<p class=form_error>{{subject_error_msg}}</p>"; 
			echo "<p class=form_good>{{subject_thanks_msg}}</p>"; 
			echo "<input type=hidden name=token value=$token />";
			$editor_options=array('media_buttons'=>false,'quicktags'=>false,'textarea_name'=>'message_body','textarea_rows'=>15);
			wp_editor("Write your message here","message_body",$editor_options);
			echo "<br>";
			echo "<input class=btn type=submit value='Send' ng-disabled='newMessageForm.subject.\$dirty && newMessageForm.subject.\$invalid' />";
			
			echo "</form>";
			echo "</div>";

		}
		/*
			Use this function to deliver a message using following parameters:
				* message_body
				* user
				* subject
				* deliver_msg = True
		
		*/
		function deliverMessage()
		{
				
				if(!isset($_REQUEST['token']) or !isset($_SESSION['WPmailer_send_mail_token']))
				{
					unset($_SESSION['WPmailer_send_mail_token']);
					echo "<p>Session expired!</p>";
				}
				else if($_REQUEST['token']!=$_SESSION['WPmailer_send_mail_token'])
				{
					unset($_SESSION['WPmailer_send_mail_token']);
					echo "<p>Session expired!</p>";
				}
				else if($_REQUEST['user']==-1)
				{
					unset($_SESSION['WPmailer_send_mail_token']);
					echo "<p>Did you forget to choose a recipient?</p>";
				}
				else
				{
					unset($_SESSION['WPmailer_send_mail_token']);
					$message_body=base64_url_encode($_REQUEST['message_body']);
					$receiver=$_REQUEST['user'];
					$subject=htmlspecialchars($_REQUEST['subject']);
					$sender=get_current_user_id();
					$data=array('body' => $message_body, 'sender'=>$sender,'receiver'=>$receiver,'subject'=>$subject);
					
					
					if(validateSubject($subject)==False)
					{
						echo "<br><font color=red font size=5>Bad subject!</font><br>";
						echo "<br><font color=red font size=5>This can happen if subject contains invalid character or have zero length or its longer than 200 characters!</font><br>";
						exit(0);
					}
					else if($this->dbHandler->insertMessage($data))
					{
						messageSentNotification();
					}
					else
					{
						/*
							One or more parameter is wrong!
						*/
						echo "<br><font color=red font size=5>Ohh no! Message couldn't be sent!</font><br>";
					}
				}
		
				$this->buttons->getHomeButton();
				$this->buttons->inboxButton();
				$this->buttons->sentMailButton();
				$this->buttons->sendNewMessageButton();
				
		
		}
		/*
			Call this function to show inbox using the following parameters:
				* inbox = true
		*/
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
					echo "<td><a href='?page=WPmailer&show_message=true&message_id=$id&outbox=true'>$subject</a></td>";
				
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
					deletedNotification();	
				}
				else
				{
					notDeletedNotification();
				}
			}
			if(isset($_REQUEST['del_outbox']))
				$this->sent_mails();
			else
				$this->inbox();

		}
		/*
		   Call shoeMessage function to display a message with following parameters in the url:
		        * message_id
		        * show_message = true
		*/
		
		function showMessage()
		{
			
			if(isset($_REQUEST['message_id']))
			{
				
				$message_id=$_REQUEST['message_id'];
				$row=$this->dbHandler->getMessage($message_id);
				$owner=$this->dbHandler->getMessageOwner($message_id);
				if($row==NULL) 
				{
				        /*
				        	This message id don't exist.
				        */
			    		echo "<p>Seems like you are trying to do something wrong!</p>";
				}
				else if($owner!=get_current_user_id())
				{
					  /*
					                This message don't belong to current user.
					  */
					echo "<p>Nice try! But you don't have the permission to see view this page</p>";
					exit(0);
				}
				else
				{
					
					$id=$row->id;
					$time=$row->time;
					$sender_id=$row->sender;
					$receiver_id=$row->receiver;
					$subject=$row->subject;
					$read_flag=$row->read_flag;
					$sent_mail_flag=$row->sent_mail_flag;
					$body=urldecode(base64_url_decode($row->body));
	
					if(isset($_REQUEST['mark_read']))
					{
						if($this->dbHandler->markRead($id))
						{
							markedOldNotification();
							$read_flag=1; //This variable is needed to markAs button
						}
					}
					if(isset($_REQUEST['mark_unread']))
					{
						if($this->dbHandler->markUnread($id))
						{
							markedUnreadNotification();
							$read_flag=0; //This variable is needed to markAs button
						}
					}
					$this->buttons->getHomeButton();
					$this->buttons->sendNewMessageButton();	
					$this->buttons->inboxButton();
					$this->buttons->sentMailButton();
					echo "<div class=no_float></div>";
					
					/*
						This code is responsible for how the message will be shown.
					*/
					echo "<div class=message_area>";
					
						showMessageHeader(isset($_REQUEST['outbox']), $receiver_id, $time);
						
						echo "<div class=message_body>$body</div><br>";
						$delete_icon_url=plugins_url('images/delete-icon.png', __FILE__ );
						$this->buttons->replyButton($sender_id, $sent_mail_flag);
						$this->buttons->deleteButtonInShowMessage($id, $sent_mail_flag);
						if($read_flag==NULL)
							$this->buttons->markAsReadButton($id,$sent_mail_flag);
						else
							$this->buttons->markAsUnreadButton($id,$sent_mail_flag);
						
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

</div>
<script>
var app = angular.module('messageFormApp', []);
app.controller('validateCtrl', function($scope) {
    $scope.subject = 'Message Subject';
    var subject_was_invalid=0;
    /*
         Function to validate subject field of message.
    */
    $scope.printError = function()
    {
    	   if($scope.newMessageForm.$error.required)
    	   {
               $scope.subject_error_msg="Please write a message subject. Valid characters are [A-Za-z0-9 ()?!*.@-]";
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
function validateMenuSelection()
{
	if( document.getElementById("WPmailer_user_list").value == -1)
	{
		alert("Please select a recipient");
		return false;
	}
	return true;
}
</script>

