<!DOCTYPE html>
<html>
  <head>
    <title>DM Page</title>
    <link href="css/styles.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredericka+the+Great&family=Cinzel:wght@400;500;600;700&family=Eczar&family=Vollkorn:wght@400;500;600&display=swap" rel="stylesheet">
  </head>
  <body>
    <?php
      include "schema.php";
      // Login data- can change these to match your own account
      $cwl_id = "maxgmr";
      $student_number = "53905238";

      $db_connection = NULL;

      $dm_id = NULL;
      $dm_name = NULL;
      $game_table = array();
      $effect_table = array();
      // $itemName_table = array();

      $player_attrs_list = ["dexterity", "constitution", "intelligence", "strength", "charisma", "wisdom", "totalXP", "weightLimit"];
      $status_effect_list = ["Blinded", "Charmed", "Deafened", "Frightened", "Invisible"];

      $attr_table = array();
      $comp_table = array();
      $effect_table = array();

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

      // Sets the global $game_table variable as a 2d array of several different useful values associated with games in database
      function loadGameTable() {
        global $dm_id, $game_table;

        $game_table = getResult(execSQL(
          "SELECT G.gameID, G.gameName, G.since
          FROM GameDMdBy G
          WHERE G.dmID = $dm_id"
        ));
      }

      // // Sets the global $item_table variable as a 2d array of the names of the items that exist in the database
      // function loadItemNameTable() {
      //   global $itemName_table;

      //   $itemName_table = getResult(execSQL(
      //     "SELECT DISTINCT I.itemName
      //     FROM Item I"
      //   ));
      // }

      // If the given DM ID exists in the database, load that DM's data and characters. Otherwise, redirect back to the login page.
      function handleLogin() {
        global $dm_id, $dm_name;
        
        $user_input = $_POST["dmID"];

        //if login info is suspicious and not within parameters, alert and redirect to login page
        if(!is_numeric($user_input)|| strlen($user_input)>10){
          echo "<script>alert('Invalid ID. Try Again');</script>";
          echo "<script>window.location.href='index.php'; </script>";
          //header("Location: index.php");
          exit;
        }

        $query = oci_fetch_array(execSQL( // Check if any DMs with the given DM ID exist in the database
          "SELECT dmID, dmName
          FROM DM
          WHERE dmID ='$user_input'"
        ), OCI_NUM);

        if ($query) { // If DM exists, set the global variables for the logged-in DM ID, DM name, game list, and item list to their proper values
          $dm_id = $query[0];
          $dm_name = $query[1];
          $game_table = loadGameTable();
        } else {
          header("Location: index.php");
          exit; // Redirect to login page
        }
      }

      function handleAttr() {
        global $dm_id, $dm_name, $attr_table;
        $dm_id = $_GET["dmID"];
        $dm_name = $_GET["dmName"];
        $game_table = loadGameTable();
        // $itemName_table = loadItemNameTable();
        $aggr = $_GET["aggr"];
        $attr = $_GET["selectedAttr"];

        $attr_table = getResult(execSQL(
          "SELECT G.gameName, $aggr(C.$attr)
          FROM GameDMdBy G, PlayedBy P, Chr C
          WHERE G.dmID = $dm_id AND
                G.gameID = P.gameID AND
                P.playerID = C.playerID
          GROUP BY G.gameName
          HAVING COUNT (*) > 1
          ORDER BY G.gameName"
        ));
      }

      function handleComp() {
        global $dm_id, $dm_name, $comp_table;
        $dm_id = $_GET["dmID"];
        $dm_name = $_GET["dmName"];
        $game_table = loadGameTable();
        // $itemName_table = loadItemNameTable();
        $aggr = $_GET["aggr"];
        $attr = $_GET["attr"];
        $comp = $_GET["comp"];
        $thresh = $_GET["thresh"];

        $comp_table = getResult(execSQL(
          "SELECT DISTINCT G.gameName, $aggr(C.$attr)
          FROM GameDMdBy G, Player P, PlayedBy PB, Chr C
          WHERE G.dmID = $dm_id AND
                G.gameID = PB.gameID AND
                P.playerID = C.playerID AND
                PB.playerID = P.playerID AND
                C.$attr $comp $thresh
          GROUP BY G.gameName
          ORDER BY G.gameName"
        ));

        foreach ($comp_table as $row) {
          consoleLog($row);
        }
      }

      function handleEffect() {
        global $dm_id, $dm_name, $effect_table;
        $dm_id = $_GET["dmID"];
        $dm_name = $_GET["dmName"];
        $game_table = loadGameTable();
        // $itemName_table = loadItemNameTable();
        $effect = $_GET["effect"];

        $effect_table = getResult(execSQL(
          "SELECT G.gameName, COUNT(DISTINCT C.charID)
          FROM GameDMdBy G, PlayedBy P, Chr C
          WHERE 
          G.dmID = $dm_id AND
          G.gameID = P.gameID AND
          P.playerID = C.playerID AND EXISTS (
              SELECT *
              FROM AfflictedBy A
              WHERE A.effectName LIKE '{$effect}%' AND
              C.charID = A.charID
          )
          GROUP BY gameName
          ORDER BY gameName"
        ));

        foreach ($effect_table as $row) {
          consoleLog($row);
          // testing stuff
          // echo $row[0];
          // echo var_dump($row[0]);
          // echo var_dump($effect);
          // if($row[0] == $effect){echo "true";};
        }
      }

      // Routes different requests to their proper functions
      function handleRequest() {
        if (connectDB()) {
            if (array_key_exists("dmlogin", $_POST)) {
              handleLogin();
            } else if (array_key_exists("attrBtn", $_GET)) {
              handleAttr();
            } else if (array_key_exists("compBtn", $_GET)) {
              handleComp();
            } else if (array_key_exists("effectBtn", $_GET)) {
              handleEffect();
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
    <div class="page">
      <div class = "sidebar">
        <h2>DM</h2>
        <h4><?= $dm_name; ?></h4>
        <h4><?= $dm_id; ?></h4>
        <p></p><p></p>
        <div class="form">
          <form method="GET" action="dm.php" id="attrForm">
            <input type="hidden" id="dmName" name="dmName" value="<?= $dm_name; ?>">
            <input type="hidden" id="dmID" name="dmID" value=<?= $dm_id; ?>>
            <h4>Find the</h4>
            <select id="selectedAggr" name="aggr">
              <option value="MAX">Maximum</option>
              <option value="AVG">Average</option>
              <option value="MIN">Minimum</option>
            </select>
            <select id="selectedAttr" name="selectedAttr">
                <?php foreach($player_attrs_list as $current_attr): ?>
                    <option value=<?= $current_attr; ?>><?= $current_attr; ?></option>
                <?php endforeach; ?>
            </select>
            <h4>in each game with at least 2 characters</h4>
          </form>
          <button type="submit" form="attrForm" name="attrBtn">See Statistics</button>
        </div>
        
        <p></p><p></p>
        <div class="form">
          <form method="GET" action="dm.php" id="compForm">
            <input type="hidden" id="dmName" name="dmName" value="<?= $dm_name; ?>">
            <input type="hidden" id="dmID" name="dmID" value=<?= $dm_id; ?>>
            <h4>Find the </h4>
            <select id="selectedAggr" name="aggr">
              <option value="MAX">Maximum</option>
              <option value="AVG">Average</option>
              <option value="MIN">Minimum</option>
            </select>
            <h4>across all characters with </h4>
            <select id="selectedAttr" name="attr">
                <?php foreach($player_attrs_list as $current_attr): ?>
                    <option value=<?= $current_attr; ?>><?= $current_attr; ?></option>
                <?php endforeach; ?>
            </select>
            <select id="selectedComp" name="comp">
              <option value=">">></option>
              <option value=">=">>=</option>
              <option value="=">=</option>
              <option value="<="><=</option>
              <option value="<"><</option>
            </select>
            <!-- <label for="thresh">(A number eg. '10'):</label>
            <input id="thresh" name="thresh" type="text"/> -->
            <select id="selectedThresh" name="thresh">
              <?php for($i = 0; $i < 31; $i++): ?>
                    <option value=<?= $i; ?>><?= $i; ?></option>
              <?php endfor; ?>
            </select>
            <h4>in each game</h4>
          </form>
          <button type="submit" form="compForm" name="compBtn">See Statistics</button>
        </div>

        <p></p><p></p>
        <div class="form">
          <form method="GET" action="dm.php" id="effectForm">
            <input type="hidden" id="dmName" name="dmName" value="<?= $dm_name; ?>">
            <input type="hidden" id="dmID" name="dmID" value=<?= $dm_id; ?>>
            <h4>Find the number of characters in each game who are afflicted with </h4>
            <select id="selectedEffect" name="effect">
                <?php foreach($status_effect_list as $effect): ?>
                    <option value=<?= $effect; ?>><?= $effect; ?></option>
                <?php endforeach; ?>
            </select>
          </form>
          <button type="submit" form="effectForm" name="effectBtn">See Statistics</button>
        </div>
        <button onclick="location.href='index.php'">LOGOUT</button>        
      </div>

      <div class="container">
        <h2>Games</h2>
        <table>
            <?php foreach($game_table as $row): ?>
                  <tr>
                      <?php foreach($row as $value): ?>
                          <td><?= $value; ?></td>
                      <?php endforeach; ?>
                  </tr>
            <?php endforeach; ?>
        </table>
        <?php if(array_key_exists("attrBtn", $_GET)): ?>
          <h2><?= $_GET["aggr"]; ?> <?= $_GET["selectedAttr"] ?> of Each Game (with At Least 2 Characters)</h2>
          <table>
            <?php foreach($attr_table as $row): ?>
              <tr>
                <?php foreach($row as $value): ?>
                <td><?= $value; ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
        <?php if(array_key_exists("compBtn", $_GET)): ?>
          <h2><?= $_GET["aggr"]; ?> of All Characters With <?= $_GET["attr"]; ?> <?= $_GET["comp"] ?> <?=$_GET["thresh"] ?> in Each Game</h2>
          <table>
            <?php foreach($comp_table as $row): ?>
              <tr>
                <?php foreach($row as $value): ?>
                <td><?= $value; ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
            <tr><?php if(empty($comp_table)): ?>
              Not found.
            <?php endif; ?></tr>
          </table>
        <?php endif; ?>
        <?php if(array_key_exists("effectBtn", $_GET)): ?>
          <h2>Number of characters in each game who have the effect <?= $_GET["effect"]; ?></h2>
          <table>
            <?php foreach($effect_table as $row): ?>
              <tr>
                <?php foreach($row as $value): ?>
                <td><?= $value; ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
            <tr><?php if(empty($effect_table)): ?>
            None found.
            <?php endif; ?></tr>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </body>
</html>