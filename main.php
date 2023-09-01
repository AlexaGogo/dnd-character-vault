<!DOCTYPE html>
<html>
  <head>
    <title>Player Page</title>
    <link href="css/styles.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredericka+the+Great&family=Cinzel:wght@400;500;600;700&family=Eczar&family=Vollkorn:wght@400;500;600&display=swap" rel="stylesheet">
  </head>
  <body>
    <?php
      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);
      // TODO: Add "debug" button
      include "schema.php";
      // Login data- can change these to match your own account
      $cwl_id = "maxgmr";
      $student_number = "53905238";

      $db_connection = NULL;

      $player_id = NULL;
      $player_name = NULL;
      $char_table = array();
      $chr_table_attrs = [
        "charID", 
        "charName", 
        "dexterity", 
        "constitution", 
        "intelligence", 
        "strength", 
        "charisma", 
        "wisdom", 
        "weightLimit", 
        "currentWeight", 
        "totalXP", 
        "raceName", 
        "raceDesc", 
        "className",
        "classDesc",
        "lv",
        "overencumber",
      ];
      $chr_num_attrs = [
        "charID", 
        "dexterity", 
        "constitution", 
        "intelligence", 
        "strength", 
        "charisma", 
        "wisdom", 
        "weightLimit", 
        "currentWeight", 
        "totalXP", 
        "lv",
        "overencumber",
      ];

      $chr_str_attrs = [
        "charName", 
        "raceName", 
        "raceDesc", 
        "className",
        "classDesc",
      ];

      // Prints out strings to the browser console for debugging
      function consoleLog($msg, $isError=False) {
        $output = $msg;
        if (is_array($output)) {
            $output = implode(',', $output);
        }
        $output = json_encode($output);

        $msgType = "log";
        if ($isError) {
            $msgType = "error";
        }
        echo "<script>console.$msgType('$output');</script>";
      }

      // Connects to the database; returns True on successful connection, False otherwise
      function connectDB() {
        global $db_connection, $cwl_id, $student_number;

        $db_connection = oci_connect("ora_" . $cwl_id, "a" . $student_number, "dbhost.students.cs.ubc.ca:1522/stu");

        if ($db_connection) {
            // consoleLog("Connected to database successfully.");
            return True;
        } else {
            consoleLog(htmlentities(oci_error()['message']), True);
            return False;
        }
      }

      // Disconnects from the database
      function disconnectDB() {
          global $db_connection;
          oci_close($db_connection);
          // consoleLog("Disconnected from database.");
      }

      // Parses the given string as SQL and executes it
      function execSQL($cmd) {
        global $db_connection;

        $statement = oci_parse($db_connection, $cmd);

        if (!$statement) {
            consoleLog(htmlentities("Cannot parse command: \r\n$cmd\r\n\r\n" . oci_error($db_connection)['message']), True);
        }

        $success = oci_execute($statement, OCI_DEFAULT);

        if (!$success) {
            consoleLog(htmlentities("Cannot execute command: \r\n$cmd\r\n\r\n" . oci_error($statement)['message']), True);
        }

        return $statement;
      }

      // Takes the raw database response SQL query and formats it as a 2d array
      function getResult($result) {
        $output = array();

        while ($row = oci_fetch_array($result, OCI_NUM)) {
            $output[] = $row;
        }

        return $output;
      }

      // Sets the global $char_table variable as a 2d array of several different useful values associated with a player's characters
      function loadCharTable() {
        global $player_id, $char_table;

        $char_table = getResult(execSQL(
          "SELECT DISTINCT C.charID, C.charName, C.dexterity, C.constitution, C.intelligence, C.strength, C.charisma, C.wisdom, C.weightLimit, 
          C.currentWeight, C.totalXP, C.raceName, R.raceDesc, CL.className, CL.classDesc, XTL.lv, IE.overencumber
          FROM Chr C, Race R, Class CL, XPToLevel XTL, IsEncumbered IE, PlayedBy PB, AssignedTo A
          WHERE
          C.playerID = '$player_id' AND
          C.raceName = R.raceName AND
          XTL.totalXP = C.totalXP AND
          IE.weightLimit = C.weightLimit AND
          IE.currentWeight = C.currentWeight AND
          C.charID = A.charID AND
          C.playerID = A.playerID AND
          A.className = CL.className
          ORDER BY C.charName"
        ));
      }

      // If the given player ID exists in the database, load that player's data and characters. Otherwise, redirect back to the login page.
      function handleLogin() {
        global $player_id, $player_name;
        
        $user_input = $_POST["playerID"];

        //if login info is suspicious and not within parameters, alert and redirect to login page
        if(!is_numeric($user_input)|| strlen($user_input)>10){
          echo "<script>alert('Invalid ID. Try Again');</script>";
          echo "<script>window.location.href='index.php'; </script>";
          //header("Location: index.php");
          exit;
        }

        $query = oci_fetch_array(execSQL( // Check if any players with the given player ID exist in the database
          "SELECT playerID, playerName
          FROM Player
          WHERE playerID ='$user_input'"
        ), OCI_NUM);

        if ($query) { // If player exists, set the global variables for the logged-in player ID, player name, and character list to their proper values
          $player_id = $query[0];
          $player_name = $query[1];
          $char_list = loadCharTable();
        } else {
          header("Location: index.php");
          exit; // Redirect to login page
        }
      }

      //delete the player's selected character. Reload page.
      function handleDelete() {
        global $char,$player_id, $player_name, $db_connection,$char_table;
        
        $player_id = $_POST["playerID"];
        $char = $_POST["characterID"];
        $cid = $_POST["characterID"];

        //delete selected character and its dependencies in order
        $lol = oci_fetch_array(execSQL(
          "DELETE FROM AssignedTo
          WHERE charID = '$cid'"
        ), OCI_NUM);
        $lol = oci_fetch_array(execSQL(
          "DELETE FROM AfflictedBy
          WHERE charID = '$cid'"
        ), OCI_NUM);
        $lol = oci_fetch_array(execSQL(
          "DELETE FROM Owns
          WHERE charID = '$cid'"
        ), OCI_NUM);
        $lol = oci_fetch_array(execSQL(
          "DELETE FROM Has
          WHERE charID = '$cid'"
        ), OCI_NUM);
        $lol = oci_fetch_array(execSQL(
          "DELETE FROM Chr
          WHERE charID = '$cid'"
        ), OCI_NUM);
        oci_commit($db_connection);

        handleLogin();
      }

      function handleSearch() {
        global $player_id, $player_name, $char_table;

        $pid = $_POST["playerID"];

        //initial set up
        $query = oci_fetch_array(execSQL( // Check if any players with the given player ID exist in the database
          "SELECT playerID, playerName
          FROM Player
          WHERE playerID ='$pid'"
        ), OCI_NUM);

        if ($query) { // If player exists, set the global variables for the logged-in player ID, player name, and character list to their proper values
          $player_id = $query[0];
          $player_name = $query[1];
        } else {
          header("Location: index.php");
          exit; // Redirect to login page
        }

        //value initialization
        $nattr = preg_replace("/[^A-Za-z0-9 \-]/", '',$_POST["numAttr"]);
        $comp = $_POST["comp"];
        $thresh = $_POST["thresh"];
        $cond = $_POST["cond"];
        consoleLog($cond);

        $prefix = "";
        if($nattr=="lv"){
          $prefix = "XTL.";
        }
        elseif($nattr=="overencumber"){
          $prefix = "IE.";
        }else{
          $prefix = "C.";
        }
        $nattr = $prefix.$nattr;

        //check if thresh set values to TRUE==TRUE
        if(empty($thresh)){
          $comp = "=";
          $thresh = $nattr;
        }


        $strattr = $_POST["strAttr"];
        $search_input = $_POST["search"];
        //check if search empty, then disregard this search by forcing it to be false
        $empty = (empty($search_input)&&($cond="OR")) ? 0 : 1;

        $prefix = "";
        if($strattr=="raceDesc"){
          $prefix = "R.";
        }
        elseif($strattr=="className" || $strattr=="classDesc"){
          $prefix = "CL.";
        }else{
          $prefix = "C.";
        }
        $strattr = $prefix.$strattr;

        //input checking- TODO used regreplce
        if(strlen($strattr)>100 || strlen($nattr)>100){
          echo "<script>alert('Invalid Search Inputs.');</script>";
          echo"<form hidden='true' method='POST' action='main.php' id='refreshPage'>
                <input type='hidden' id='playerID' name='playerID' value=$player_id>
                <input type='hidden' id='refresh' name='refresh'>
                </form>";
          echo "<script>document.getElementById('refreshPage').submit(); </script>";
        }

        //working string inputs
        $char_table = getResult(execSQL(
          "SELECT DISTINCT C.charID, C.charName, C.dexterity, C.constitution, C.intelligence, C.strength, C.charisma, C.wisdom, C.weightLimit, 
          C.currentWeight, C.totalXP, C.raceName, R.raceDesc, CL.className, CL.classDesc, XTL.lv, IE.overencumber
          FROM Chr C, Race R, Class CL, XPToLevel XTL, IsEncumbered IE, PlayedBy PB, AssignedTo A
          WHERE C.playerID = $pid AND
                C.raceName = R.raceName AND
                XTL.totalXP = C.totalXP AND
                IE.weightLimit = C.weightLimit AND
                IE.currentWeight = C.currentWeight AND
                C.charID = A.charID AND
                C.playerID = A.playerID AND
                A.className = CL.className AND
                (($strattr LIKE '%{$search_input}%' AND $empty = 1) $cond
                $nattr $comp $thresh)
          ORDER BY C.charName"
        ));

        foreach ($char_table as $row) {
          consoleLog($row);
        }
      }

      // Routes different requests to their proper functions
      function handleRequest() {
        if (connectDB()) {
          if(isset($_POST['detailsBtn'])) {
            # View Details button was clicked
            consoleLog("View details should not be routing here!", TRUE);
          }
          elseif(isset($_POST['deleteBtn'])) {
            handleDelete();
          }
          elseif(isset($_POST['searchBtn'])) {
            handleSearch();
          }
          elseif(isset($_POST['clearBtn'])) {
            handleLogin();
          }
          elseif (array_key_exists("plogin", $_POST)) {
              handleLogin();
          }
          elseif (array_key_exists("addedChr", $_POST) && array_key_exists("playerID", $_POST)) {
            handleLogin();
          }
          elseif (array_key_exists("refresh", $_POST) && array_key_exists("playerID", $_POST)) {
            handleLogin();
          }
          
            disconnectDB();
        }
      }

      // END OF FUNCTIONS
      if ((count($_POST) > 0) || (count($_GET) > 0)) {
        handleRequest();
        
      } else {
        header("Location: index.php"); // If no login info given, redirect to the login page
        exit;
      }
    ?>

    <form hidden='true' method="POST" action="add-character.php" id="goAdd">
        <input type="hidden" id="playerID" name="playerID" value=<?= $player_id ?>>
    </form>

    <div class="page">
    <div class = "sidebar">
      <h2>Player</h2>
      <h4><?= $player_name; ?></h4>
      <h4><?= $player_id; ?></h4>
      <button onclick="document.getElementById('goAdd').submit()">ADD</button>
      <button onclick="location.href='index.php'">LOGOUT</button>
    </div>

    <div class="container">
      <h2>Character Search</h2>
      <form method="POST" action="main.php" id="searchForm">
        <input type="hidden" id="playerID" name="playerID" value=<?= $player_id ?>>
        <input type="hidden" id="playerName" name="playerName" value=<?= $player_name ?>>
        <label for="selectedNumAttr">Character whose</label>
        <select id="selectedNumAttr" name="numAttr">
          <?php foreach($chr_num_attrs as $current_attr): ?>
            <option value=<?= $current_attr; ?>><?= $current_attr; ?></option>
          <?php endforeach; ?>
        </select>
        <label for="selectedComp">is</label>
        <select id="selectedComp" name="comp">
          <option value=">">greater than</option>
          <option value=">=">greater than or equal to</option>
          <option value="=">equal to</option>
          <option value="!=">not</option>
          <option value="<=">less than or equal to</option>
          <option value="<">less than</option>
        </select>
        <label for="thresh"></label>
        <input type="number" id="thresh" name="thresh"/>
        <p></p>
          <select name="cond" id="cond">
          <option value="AND" >AND</option>
          <option value="OR">OR</option>
        </select>
        <label for="searchValue">contains</label>
        <input type="text" id="searchValue" name="search"/>
        <label for="selectedStrAttr">in their</label>
        <select id="selectedStrAttr" name="strAttr">
          <?php foreach($chr_str_attrs as $current_attr): ?>
              <option value=<?= $current_attr; ?>><?= $current_attr; ?></option>
          <?php endforeach; ?>
        </select>
        <br>      
        <button type="submit" form="searchForm" name="searchBtn">Search</button>
        <button type="submit" form="searchForm" name="clearBtn">Clear</button>
      </form>

      <h2>Characters</h2>
      <form method="POST" action="main.php" id="mainForm">
        <table>
          <tr>
            <td>Select</td>
            <?php foreach($chr_table_attrs as $attr): ?>
              <td>
                <?= $attr; ?>
              </td>
            <?php endforeach; ?>
          </tr>
            <?php foreach($char_table as $row): ?>
                  <tr>
                    <td>
                      <input type="radio" id=<?=$row[0];?> name="characterID"
                      checked="true"
                      value=<?=$row[0];?>>
                    </td>
                      <?php foreach($row as $value): ?>
                          <td><?= $value ?></td>
                      <?php endforeach; ?>
                  </tr>
            <?php endforeach; ?>
        </table>
        <tr><?php if(empty($char_table)): ?>
              None found.<br>
        <?php endif; ?></tr>
        <input type="hidden" id="playerID" name="playerID" value=<?=$player_id?>>
        <!-- perform different actions depending on which button is pressed -->
        <button type="submit" form="mainForm" formaction="chardetails.php" name="detailsBtn">View Details</button>
        <button type="submit" form="mainForm" formaction="main.php" name="deleteBtn">Delete</button>
      </form>
    </div>

    </div>
  </body>
</html>