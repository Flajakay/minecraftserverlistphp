 <?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "script";

$conn = new mysqli($servername, $username, $password, $dbname);

$sql = "SELECT server_id, votes FROM servers ORDER BY votes DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		echo "id: " . $row["server_id"]. " Votes: " . $row["votes"] . "<br>";
	}
} else {
	echo "0 results";
}

$conn->close();

?> 