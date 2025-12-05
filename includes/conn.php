<?php
	$conn = new mysqli('localhost', 'root', '@dmin', 'core_online_voting_system');

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
?>