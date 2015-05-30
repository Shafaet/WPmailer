<?php
	require_once(dirname(__FILE__) . '/mail_dbconfig.php');
	/*
		Printing all the notifications and headers should be done through this file.
		Any interaction with database must be done using dbconfig.php
	*/
	function getUnreadMessageNotification()
	{
		
		$dbHandler=new WPmailerDBhandler();
		$numUnreadMessage=$dbHandler->getNumberOfUnreadMessage();
		
		if($numUnreadMessage)
		echo "<div class=notification_unread ><p> You have ".$numUnreadMessage." unread message(s)!</p></div>";

	}

	function messageSentNotification()
	{
		echo "<div class=notification_message_sent><p>Message Sent!</div>";
	}
	function markedOldNotification()
	{
		echo "<div class=notification_message_sent ><p>Message marked as <em>old!</em></div>";
	}
	function markedUnreadNotification()
	{
		echo "<div class=notification_unread ><p>Message marked as <em>unread!</em></div>";
	}
	function deletedNotification()
	{
                echo "<div class=notification_msg_delete><p>Deleted one message!</p></div>";
        }
        function notDeletedNotification()
	{
                echo "<div class=notification_msg_delete><p>Can't delete, wrong message id!</p></div>";
        }
        function showMessageHeader($isoutbox, $receiver_id, $time)
        {
                if($isoutbox)
                	$sent_direction_text = 'Sent to';
                else
                	$sent_direction_text = 'Sent by';
        	echo "<p><div class='msg_header'>$sent_direction_text: </div>".getDisplayName($receiver_id)."<div class='msg_header'><br>Time: </div>". $time."</p>";
        }
?>

