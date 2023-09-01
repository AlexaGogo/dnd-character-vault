<html>
    <head>
        <title>Add Player</title>
        <link href="css/styles.css" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fredericka+the+Great&family=Cinzel:wght@400;500;600;700&family=Eczar&family=Vollkorn:wght@400;500;600&display=swap" rel="stylesheet">
    </head>
    <body>
    <?php
        // TODO: Add back button
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);

        include "schema.php";
        // Login data- can change these to match your own account
        $cwl_id = "maxgmr";
        $student_number = "53905238";

        $new_id;

        $db_connection = NULL;

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

        // Adds a new player to the database
        function handleAdd() {
            global $db_connection, $new_id; // The new player's ID is automatically assigned

            $player_name = preg_replace("/[^A-Za-z0-9 \-]/", '', $_POST["playerName"]); // Remove all non-valid characters

            if (strlen($player_name) > 0) { // If the player name isn't all non-valid characters, add it to the database along with its automatically assigned ID
                execSQL("INSERT INTO Player VALUES ($new_id, '$player_name')");
                oci_commit($db_connection);
            }
        }

        // Routes different requests to their proper functions
        function handleRequest() {
            if (connectDB()) {
                if (array_key_exists("addBtn", $_POST)) {
                    handleAdd();
                }

                disconnectDB();
            }
        }

        // Determines the new player ID
        function getNewID() {
            if (connectDB()) {
                $id = 99999; // If there are no players, set the player ID to 100000

                $result = execSQL(
                    "SELECT MAX(playerID) 
                    FROM Player"
                    //GROUP BY playerID"
                    );
                while ($row = oci_fetch_array($result, OCI_NUM)) { // If there are players in the database, set the new player ID to one higher than the highest existing player ID
                    $id = $row[0]; 
                }

                disconnectDB();

                return ++$id;
            }
            return False;
        }

        // END OF FUNCTIONS
        $new_id = getNewID();
        if ((count($_POST) > 0)) {
            handleRequest();
            $new_id = getNewID();
        }
    ?>

        <div class="container">
            <h1>Add Player</h1>
            <?php if ($new_id): ?>
                <p>Your player ID will be: <?= $new_id; ?></p>
            <?php endif; ?>
            <div class="input-form">
                <form method="POST" action="add-player.php" id="addForm">
                    <h4>Player Name</h4>
                    <input name="playerName" />
                </form>
                <button type="submit" form="addForm" name="addBtn">Add Player</button>
            </div>
        </div>
    </body>
</html>