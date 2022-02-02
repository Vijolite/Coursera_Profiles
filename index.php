
<?php
require_once "pdo.php";
session_start();
?>
<html>
<head><title>Ija Saporenkova</title></head>
<body>
<?php
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) {
    echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
    unset($_SESSION['success']);
}

echo('<h1>Welcome to Resume Registry</h1>'."\n");


if (isset($_SESSION['name'])) {
    echo('<p><a href="logout.php">Logout</a> </p>');
} else {
    echo('<p><a href="login.php">Please log in</a> </p>');
    }




?>


<?php
$stmt = $pdo->query("SELECT concat(first_name,' ',last_name) as name, headline, profile_id FROM profile");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {echo ("<p>No rows found</p>"."\n");}
else {

    echo('<table border="1">'."\n");
    echo '<tr><td><b>Name</b></td>';
    echo '<td><b>Headline</b></td>';
    if (isset($_SESSION['name'])) {
            echo '<td><b>Action</b></td>';
        }

    $stmt = $pdo->query("SELECT concat(first_name,' ',last_name) as name, headline, profile_id FROM profile");
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
        echo "<tr><td>";

        echo('<a href="view.php?profile_id='.$row['profile_id'].'">');
        echo(htmlentities($row['name']));
        echo('</a>');
        echo("</td><td>");
        echo(htmlentities($row['headline']));
        if (isset($_SESSION['name'])) {
            echo("</td><td>");
            echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
            echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
        }
        echo("</td></tr>\n");
    }
    echo ('</table>'."\n");
}

if (isset($_SESSION['name'])) {
    echo ('<p><a href="add.php">Add New Entry</a></p>');
}
?>


<!--
<p><a href="add.php">Add New Entry</a></p>

<p><a href="logout.php">Logout</a> </p>
-->
