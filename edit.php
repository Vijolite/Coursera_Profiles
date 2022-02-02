<?php

session_start();

if ( ! isset($_SESSION['name']) ) {
    die('Not logged in');
}

if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

function validatePos() {
  for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['year'.$i]) ) continue;
    if ( ! isset($_POST['desc'.$i]) ) continue;

    $year = $_POST['year'.$i];
    $desc = $_POST['desc'.$i];
    if ( strlen($year) == 0 || strlen($desc) == 0 ) {
      return "All fields are required";
    }

    if ( ! is_numeric($year) ) {
      return "Position year must be numeric";
    }
  }
  return true;
}

require_once "pdo.php"; //require_once "pdo.php";

// Validation
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
    if ((strlen($_POST['first_name']) < 1 ) || (strlen($_POST['last_name']) < 1 ) || (strlen($_POST['email']) < 1 )|| (strlen($_POST['headline']) < 1 )) {
            $_SESSION['error'] = "All fields are required";
            header("Location: edit.php?profile_id=".$_POST['profile_id']);
            return;
        } else {
            if (strpos($_POST['email'],'@')==FALSE) {
            $_SESSION['error'] = "Email address must contain @";
            header("Location: edit.php?profile_id=".$_POST['profile_id']);
            return;
            } else {
              $v=validatePos();
              if ($v !== true ) {
                $_SESSION['error'] = validatePos();
                header("Location: edit.php?profile_id=".$_POST['profile_id']);
                return;
              }
            }
          }  

    $sql = "UPDATE profile SET first_name = :first_name, last_name = :last_name, email = :email, headline = :headline, summary = :summary
            WHERE profile_id = :profile_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':first_name' => $_POST['first_name'],
        ':last_name' => $_POST['last_name'],
        ':email' => $_POST['email'],
        ':headline' => $_POST['headline'],
        ':summary' => $_POST['summary'],
        ':profile_id' => $_POST['profile_id']));

    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    // Insert the position entries
    $rank = 1;
    for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['year'.$i]) ) continue;
      if ( ! isset($_POST['desc'.$i]) ) continue;

      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];
      $stmt = $pdo->prepare('INSERT INTO Position
        (profile_id, rank, year, description)
        VALUES ( :pid, :rank, :year, :desc)');

      $stmt->execute(array(
      ':pid' => $_REQUEST['profile_id'],
      ':rank' => $rank,
      ':year' => $year,
      ':desc' => $desc)
      );
      $rank++;
    }


    $_SESSION['success'] = 'Record edited';
    header( 'Location: index.php' ) ;
    return;

}

// Guardian: Make sure that profile_id is present
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


// Flash pattern
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
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


<?php
echo ('<h1> Editing Profile for '.htmlentities($_SESSION['user_name']).'</h1>');
?>

<form method="post">

<p>First Name:
<input type="text" name="first_name" size="60" value="<?= $fn ?>"></p>
<p>Last Name:
<input type="text" name="last_name" size="60" value="<?= $ln ?>"></p>
<p>Email:
<input type="text" name="email" size="30" value="<?= $em ?>"></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80" value="<?= $he ?>"></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80" > <?= $su ?> </textarea>
<input type="hidden" name="profile_id" value="<?= $profile_id ?>">
</p>

<p>
  Position: 
  <input type="submit" id="addPos" value="+">
  <div id="position_fields">


    <?php
    $stmt = $pdo->prepare("SELECT year,description FROM position WHERE profile_id = :xyz");
    $stmt->execute(array(":xyz" => $profile_id));
        
    $c=0;
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {

        echo ("<div id=position".($c+1).">");
        echo '<p>Year: <input type="text" name=year'.($c+1)." value=".htmlentities($row['year'])." />";
        

        echo '<input type="button" value="-" onclick="';
        echo "$('#position".($c+1)."')".".remove();return false;";
        echo '">';
        

        echo '</p>';
        echo '<textarea name=desc'.($c+1).' rows="8" cols="80">';
        echo (htmlentities($row['description']));
        echo '</textarea>';
        echo '</div>';

        $c++;
    }
    ?>


  </div>
</p>


<p>
<input type="submit" value="Save"/>
<input type="submit" name="cancel" value="Cancel">
</p>

</form>

<script>
//countPos = 0;
countPos=<?php echo $c ; ?>;
console.log(countPos);

// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });
});
</script>

</div>
</body>
</html>
