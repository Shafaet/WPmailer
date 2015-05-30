<?php
	require_once(dirname(__FILE__) . '/mail_dbconfig.php');
	function getUnreadMessageNotification()
	{
		
		$dbHandler=new WPmailerDBhandler();
		$numUnreadMessage=$dbHandler->getNumberOfUnreadMessage();
		
		if($numUnreadMessage)
		echo "<div class=notification_unread ><p> You have ".$numUnreadMessage." unread message(s)!</p></div>";

	}

	function messageSentNotification()
	{
		echo "<div class=notification_message_sent ><p>Message Sent!</div>";
	}
	function markedOldNotification()
	{
		echo "<div class=notification_message_sent ><p>Message marked as <em>old!</em></div>";
	}
	function markedUnreadNotification()
	{
		echo "<div class=notification_unread ><p>Message marked as <em>unread!</em></div>";
	}
?>

