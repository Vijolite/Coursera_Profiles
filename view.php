<?php

session_start();


if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

require_once "pdo.php"; //require_once "pdo.php";

// Guardian: Make sure that user_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}


$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$em = htmlentities($row['email']);
$he = htmlentities($row['headline']);
$su = htmlentities($row['summary']);
$profile_id = $row['profile_id'];
?>


<!DOCTYPE html>
<html>
<head>
<title>Ija Saporenkova</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">


<h1>Profile information</h1>
<form method="post">
<p>First name: <?= $fn ?></p>
<p>Last name: <?= $ln ?></p>
<p>Email: <?= $em ?></p>
<p>Headline:</p>
<p> <?= $he ?></p>
<p>Summary:</p>
<p> <?= $su ?></p>


<?php


$stmt = $pdo->prepare("SELECT year,description FROM position WHERE profile_id = :xyz");
$stmt->execute(array(":xyz" => $profile_id));
    
$c=0;
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
	if ($c===0) {
		echo('<p>Position:</p>'."\n");
		echo('<ul>'."\n");
	}
    echo "<li>";
    echo(htmlentities($row['year'])." ");
    echo(htmlentities($row['description']));
    echo ("</li>"."\n");
    $c++;
}
if ($c>0) echo ('</ul>'."\n");

?>




<a href="index.php">Done</a></p>
</form>


</div>
</body>
</html>
