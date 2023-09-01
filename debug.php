<html>
    <head>
        <title>Debug</title>
        <link href="css/styles.css" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fredericka+the+Great&family=Cinzel:wght@400;500;600;700&family=Eczar&family=Vollkorn:wght@400;500;600&display=swap" rel="stylesheet">
    </head>
    <body>
    <?php
        // Show errors on webpage
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        include "schema.php";
        // Login data- can change these to match your own account
        $cwl_id = "maxgmr";
        $student_number = "53905238";

        $displayed_table;

        $db_connection = NULL;
        $players_list = array();
        $div_player_id = NULL;
        $div_player_name = NULL;
        $div_result = array();

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

        // Gets list of all players from the database
        function getPlayersList() {
            global $db_connection, $players_list;
            $players_list = getResult(execSQL(
                "SELECT playerID, playerName 
                FROM Player"
            ));
        }

        // Clears all the tables in the database
        function handleReset() {
            global $db_connection, $create_tables, $table_names;
            $table_names_reversed = array_reverse($table_names);

            consoleLog("Deleting database...");
            foreach ($table_names_reversed as $table_name) { // Delete every table
                execSQL("DROP TABLE $table_name");
            }

            consoleLog("Creating tables...");
            foreach ($create_tables as $cmd) { // Recreate every table
                execSQL($cmd);
            }

            consoleLog("Tables reset.");
        }

        // Adds default values to the database (as detailed in "schema.php")
        function handleDefault() {
            global $insert_default, $db_connection;

            consoleLog("Adding default values to database...");
            foreach ($insert_default as $cmd) {
                execSQL($cmd);
            }

            oci_commit($db_connection);
            consoleLog("Default values added.");
        }

        // Displays the raw data of the chosen table
        function handleView() {
            global $db_connection, $displayed_table;
            $table_name = $_GET["tables"];

            consoleLog("Requesting $table_name table...");
            // TODO: Make it so the sorting isnt dumb
            $displayed_table = getResult(execSQL("SELECT * FROM $table_name"));
        }

        // Displays a projection of a chosen table
        function handleProj() {
            global $db_connection, $displayed_table, $table_attrs;
            $table_name = $_GET["tables"];

            $proj_attrs = [];

            foreach ($table_attrs[$_GET["tables"]] as $attr) {
                if (array_key_exists($attr, $_GET)) {
                    $proj_attrs[] = $attr;
                }
            }

            $displayed_table = getResult(execSQL("SELECT " . implode(", ", $proj_attrs) . " FROM $table_name"));
        }

        // Gets a list of all players in all games the given player is in
        function handleDiv() {
            global $db_connection, $div_player_id, $div_player_name, $div_result;
            $pid = $_GET["divPlayer"];

            $query = oci_fetch_array(execSQL( // Check if any players with the given player ID exist in the database
                "SELECT playerID, playerName
                FROM Player
                WHERE playerID =$pid"
              ), OCI_NUM);
      
            if ($query) { // If player exists, set the global variables for the logged-in player ID, player name, and character list to their proper values
                $div_player_id = $query[0];
                $div_player_name = $query[1];
            } else {
                header("Location: debug.php");
                exit; // Reset
            }

            $div_result = getResult(execSQL(
                "SELECT P.playerID, P.playerName
                FROM Player P
                WHERE NOT EXISTS (
                    (SELECT PB1.gameID
                    FROM PlayedBy PB1
                    WHERE PB1.playerID = $pid)
                    MINUS
                    (SELECT PB2.gameID
                    FROM PlayedBy PB2
                    WHERE PB2.playerID = P.playerID)
                ) AND P.playerID <> $pid"
            ));

            // foreach($div_result as $row) {
            //     consoleLog($row);
            // }
        }

        // Routes different requests to their proper functions
        function handleRequest() {
            if (connectDB()) {
                if (array_key_exists("resetBtn", $_POST)) {
                    handleReset();
                } else if (array_key_exists("defaultBtn", $_POST)) {
                    handleDefault();
                } else if (array_key_exists("viewBtn", $_GET)) {
                    handleView();
                } else if (array_key_exists("projBtn", $_GET)) {
                    handleProj();
                } else if (array_key_exists("divBtn", $_GET)) {
                    handleDiv();
                }

                disconnectDB();
            }
        }

        // END OF FUNCTIONS
        if (connectDB()) {
            getPlayersList();
            disconnectDB();
        }
        
        if ((count($_POST) > 0) || (count($_GET) > 0)) {
            handleRequest();
            if (connectDB()) {
                getPlayersList();
                disconnectDB();
            }
        }
    ?>
        <div class="container">
            <h1>Debug Page</h1>
            <button onclick="location.href='index.php'">BACK</button>
            <h3>Reset the entire database</h3>
            <div class="form">
                <form method="POST" action="debug.php" id="resetForm">
                    <input type="hidden" id="resetReq" name="resetReq">
                </form>
                <button type="submit" form="resetForm" name="resetBtn">Reset and Create Database</button>
            </div>

            <h3>Populate the database with default values</h3>
            <div class="form">
                <form method="POST" action="debug.php" id="defaultForm">
                    <input type="hidden" id="defaultReq" name="defaultReq">
                </form>
                <button type="submit" form="defaultForm" name="defaultBtn">Add Default Values</button>
            </div>

            <h3>View Table</h3>
            <div class="form">
                <form method="GET" action="debug.php" id="viewForm">
                    <label for="tables">Select a table to view:</label>
                    <select id="tables" name="tables">
                        <?php foreach($table_names as $table_name): ?>
                            <option value=<?= $table_name; ?>><?= $table_name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <button type="submit" form="viewForm" name="viewBtn">View Table</button>
            </div>
            <?php if (array_key_exists("viewBtn", $_GET) || array_key_exists("projBtn", $_GET)): ?>
                <h3><?= $_GET["tables"]; ?></h3>
                <h4>Projection Attributes</h4>
                <form method="GET" action="debug.php" id="projForm">
                    <input type="hidden" id="tables" name="tables" value=<?= $_GET["tables"]; ?>>
                    <table>
                        <tr>
                            <?php foreach($table_attrs[$_GET["tables"]] as $attr): ?>
                                <td><input type="checkbox" id=<?= $attr; ?> name=<?= $attr; ?> value=<?= $attr; ?>><label for=<?= $attr; ?>><?= $attr; ?></label></td>
                            <?php endforeach; ?>
                        </tr>
                    </table>
                </form>
                <button type="submit" form="projForm" name="projBtn">Filter Attributes</button>
                <table>
                    <?php foreach($displayed_table as $row): ?>
                        <tr>
                            <?php foreach($row as $value): ?>
                                <td><?= $value ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            <h3>Get Players in All Games Chosen Player is in</h3>
            <div class="form">
                <form method="GET" action="debug.php" id="divForm">
                    <label for="divPlayer">Select a player to view:</label>
                    <select id="divPlayer" name="divPlayer">
                        <?php foreach($players_list as $current_player): ?>
                            <option value=<?= $current_player[0]; ?>><?= $current_player[1] . " (" . $current_player[0] . ")"; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <button type="submit" form="divForm" name="divBtn">See Players</button>
            </div>
            <?php if (array_key_exists("divBtn", $_GET)): ?>
                <h3>Players in All Games <?= $div_player_name; ?> &#40;ID: <?= $div_player_id; ?>&#41; is in</h3>
                <table>
                    <?php foreach($div_result as $row): ?>
                        <tr>
                            <?php foreach($row as $value): ?>
                                <td><?= $value ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </body>
</html>
