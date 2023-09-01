<html>
    <head>
        <title>Character Details</title>
        <link href="css/styles.css" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fredericka+the+Great&family=Cinzel:wght@400;500;600;700&family=Eczar&family=Vollkorn:wght@400;500;600&display=swap" rel="stylesheet">
    </head>
    <body>
    <?php
        // PUT ALL YOUR PHP STUFF HERE!
        include "schema.php";
        $cwl_id = "maxgmr";
        $student_number = "53905238";
        $db_connection = NULL;

        $player_table = array();
        $chr_table = array();
        $edit_table = array();
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
        $edit_table_attrs = [
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
            "className",
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

        function handleDetails() {
            global $player_table, $chr_table, $edit_table;

            $pID = $_POST["playerID"];
            $cID = $_POST["characterID"];

            $player_table = getResult(execSQL(
                "SELECT DISTINCT playerID, playerName FROM Player WHERE '$pID' = playerID"
            ))[0];

            $chr_table = getResult(execSQL(
                "SELECT DISTINCT C.charID, C.charName, C.dexterity, C.constitution, C.intelligence, C.strength, C.charisma, C.wisdom, C.weightLimit, 
                C.currentWeight, C.totalXP, C.raceName, R.raceDesc, CL.className, CL.classDesc, XTL.lv, IE.overencumber
                FROM Chr C, Race R, Class CL, XPToLevel XTL, IsEncumbered IE, PlayedBy PB, AssignedTo A
                WHERE
                C.playerID = '$pID' AND
                C.raceName = R.raceName AND
                XTL.totalXP = C.totalXP AND
                IE.weightLimit = C.weightLimit AND
                IE.currentWeight = C.currentWeight AND
                C.charID = A.charID AND
                C.playerID = A.playerID AND
                A.className = CL.className AND
                C.charID = '$cID'
                ORDER BY C.charName"
            ))[0];

            $edit_table = getResult(execSQL(
                "SELECT DISTINCT TRIM(C.charName), C.dexterity, C.constitution, C.intelligence, C.strength, C.charisma, C.wisdom, C.weightLimit, 
                C.currentWeight, C.totalXP, TRIM(C.raceName), TRIM(CL.className)
                FROM Chr C, Race R, Class CL, XPToLevel XTL, IsEncumbered IE, PlayedBy PB, AssignedTo A
                WHERE
                C.playerID = '$pID' AND
                C.raceName = R.raceName AND
                XTL.totalXP = C.totalXP AND
                IE.weightLimit = C.weightLimit AND
                IE.currentWeight = C.currentWeight AND
                C.charID = A.charID AND
                C.playerID = A.playerID AND
                A.className = CL.className AND
                C.charID = '$cID'
                ORDER BY C.charName"
            ))[0];
        }

        function getLevelFromXP($xp) {
            if ($xp >= 355000) {
                return 20;
            } else if ($xp >= 305000) {
                return 19;
            } else if ($xp >= 265000) {
                return 18;
            } else if ($xp >= 225000) {
                return 17;
            } else if ($xp >= 195000) {
                return 16;
            } else if ($xp >= 165000) {
                return 15;
            } else if ($xp >= 140000) {
                return 14;
            } else if ($xp >= 120000) {
                return 13;
            } else if ($xp >= 100000) {
                return 12;
            } else if ($xp >= 85000) {
                return 11;
            } else if ($xp >= 64000) {
                return 10;
            } else if ($xp >= 48000) {
                return 9;
            } else if ($xp >= 34000) {
                return 8;
            } else if ($xp >= 23000) {
                return 7;
            } else if ($xp >= 14000) {
                return 6;
            } else if ($xp >= 6500) {
                return 5;
            } else if ($xp >= 2700) {
                return 4;
            } else if ($xp >= 900) {
                return 3;
            } else if ($xp >= 300) {
                return 2;
            }
            return 1;
        }

        function handleEdit() {
            global $db_connection;
            $fmt_char_name = preg_replace("/[^A-Za-z0-9 \-]/", '', $_POST["charName"]);
            $char_id_test = $_POST["characterID"];
            $player_id_test = $_POST["playerID"];
            $class_test = $_POST["className"];
            $dexterity_test = $_POST["dexterity"];
            $constitution_test = $_POST["constitution"];
            $intelligence_test = $_POST["intelligence"];
            $strength_test = $_POST["strength"];
            $charisma_test = $_POST["charisma"];
            $wisdom_test = $_POST["wisdom"];
            $class_exists = oci_fetch_array(execSQL(
                "SELECT className
                FROM Class
                WHERE className ='$class_test'"
              ), OCI_NUM);
            $race_test = $_POST["raceName"];
            $race_exists = oci_fetch_array(execSQL(
                "SELECT raceName
                FROM Race
                WHERE raceName ='$race_test'"
              ), OCI_NUM);
            if (
                (strlen($fmt_char_name)<100) &&
                (strlen($fmt_char_name)>0) &&
                (is_numeric($dexterity_test)) &&
                (is_numeric($constitution_test)) &&
                (is_numeric($intelligence_test)) &&
                (is_numeric($strength_test)) &&
                (is_numeric($charisma_test)) &&
                (is_numeric($wisdom_test)) &&
                (is_numeric($_POST["weightLimit"])) &&
                (is_numeric($_POST["currentWeight"])) &&
                (is_numeric($_POST["totalXP"])) &&
                ($class_exists) &&
                ($race_exists)
            ) {
                $total_xp_test = $_POST["totalXP"];
                $lv_test = getLevelFromXP($total_xp_test);
                $xtl_exists = oci_fetch_array(execSQL(
                    "SELECT totalXP
                    FROM XPToLevel
                    WHERE totalXP = $total_xp_test"
                    ), OCI_NUM);
                if (!$xtl_exists) {
                    execSQL("INSERT INTO XPToLevel VALUES ($total_xp_test, $lv_test)");
                    oci_commit($db_connection);
                }

                $wl_test = $_POST["weightLimit"];
                $cw_test = $_POST["currentWeight"];
                if ($cw_test > $wl_test) {
                    $oe_test = 1;
                } else {
                    $oe_test = 0;
                }
                $wlcw_exists = oci_fetch_array(execSQL(
                    "SELECT weightLimit, currentWeight
                    FROM IsEncumbered
                    WHERE weightLimit = $wl_test AND currentWeight = $cw_test"
                    ), OCI_NUM);
                if (!$wlcw_exists) {
                    execSQL("INSERT INTO IsEncumbered VALUES ($wl_test, $cw_test, $oe_test)");
                    oci_commit($db_connection);
                }

                execSQL(
                    "UPDATE AssignedTo
                    SET className = '$class_test'
                    WHERE charID = $char_id_test AND playerID = $player_id_test"
                );
                execSQL(
                    "UPDATE Chr
                    SET 
                        charName = '$fmt_char_name', 
                        dexterity = $dexterity_test, 
                        constitution = $constitution_test, 
                        intelligence = $intelligence_test, 
                        strength = $strength_test, 
                        charisma = $charisma_test,
                        wisdom = $wisdom_test,
                        weightLimit = $wl_test,
                        currentWeight = $cw_test,
                        totalXP = $total_xp_test,
                        raceName = '$race_test'
                    WHERE charID = $char_id_test AND playerID = $player_id_test"
                );
                oci_commit($db_connection);
            } else {
                echo "<script>alert('Problem with edited fields. Ensure the race and class exist and all fields are their proper data type.');</script>";
                echo "<script>window.location.href='index.php'; </script>";
                exit;
            }
        }

        // Routes different requests to their proper functions
        function handleRequest() {
            if (connectDB()) {
                if (array_key_exists("detailsBtn", $_POST)) {
                    handleDetails();
                } else if (array_key_exists("editBtn", $_POST)) {
                    handleEdit();
                    handleDetails();
                }
                disconnectDB();
            }
        }

        // END OF FUNCTIONS
        if ((count($_POST) > 0) || (count($_GET) > 0)) {
            handleRequest();
        } else {
            header("Location: index.php"); // If no character info given, redirect to the login page
            exit;
        }
        // END OF PHP
    ?>

    <div class="page">
      <div class = "sidebar">
       <h2>Character Details</h2>
       <h3>Player ID: <?=$_POST["playerID"]?></h3>
       <h3>Character ID: <?=$_POST["characterID"]?></h3>
       <button onclick="location.href='index.php'">LOGOUT</button>
      </div>

      <div class="container">
        <h2><?= $chr_table[1]; ?></h2>
        <table>
            <tr>
            <?php foreach($chr_table_attrs as $attr): ?>
                <td><?= $attr; ?></td>
            <?php endforeach; ?>
            <tr>
            <?php foreach($chr_table as $val): ?>
                <td><?= $val; ?></td>
            <?php endforeach; ?>
            </tr>
        </table>
        <h2>Edit Character</h2>
        <form method="POST" action="chardetails.php" id="editForm">
            <input type="hidden" id="playerID" name="playerID" value=<?=$_POST["playerID"]?>>
            <input type="hidden" id="characterID" name="characterID" value=<?=$_POST["characterID"]?>>
            <table>
                <?php $i = 0; ?>
                <?php foreach($edit_table_attrs as $attr_name): ?>
                    <tr>
                        <td>
                            <?= $attr_name; ?>: <input id=<?= $attr_name; ?> name=<?= $attr_name; ?> value="<?= $edit_table[$i]; ?>" />
                            <?php $i = ++$i; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </form>
        <button type="submit" form="editForm" formaction="chardetails.php" name="editBtn">Submit Edits</button>
      </div>
    </div>
    </body>
</html>