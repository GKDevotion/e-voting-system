<?php
	$conn = new mysqli('localhost', 'root', '@dmin', 'online_voting_system');

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
?>