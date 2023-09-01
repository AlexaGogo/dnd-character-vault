<html>
    <head>
        <title>Add Character</title>
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

        $new_cid;
        $player_id = NULL;
        $player_name = NULL;
        $player_table = array();
        $chr_table = array();
        $input_table = array();
        // $input_table = [
        //     "Candace Beezy",
        //     16, 11, 15, 13, 9, 12, 105, 44, 115067,
        //     "Elf",
        //     "Wizard",
        // ];
        $placeholder_table = [
            "Alphanumeric Name (eg.ImCool123)",
            "Number", 
            "Number", 
            "Number", 
            "Number", 
            "Number", 
            "Number", 
            "Number", 
            "Number", 
            "Number",
            "Races: eg. Dragonborn/Elf/Halfling/Human/Tiefling etc.",
            "Classes: eg. Bard/Cleric/Druid/Fighter/Rogue/Wizard etc.",
        ];
        $error_table = array();
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
        $input_table_attrs = [
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
        
        $req_attrs = [
            "charName", 
            "weightLimit", 
            "totalXP", 
            "raceName", 
            "className",
        ];

        $chr_attrs_read = array(); //mapTable function --->future implementation
        $req_attrs_read = array();

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

        // //map table to readable table
        // function mapTable($tab) {
        //     global $table_attrs, $table_attrs_readable;
        //     $t_read = "";
        //     $t_val = ""
        //     foreach($tab as $tab_attr):
        //         //create map in schema for attrname => readable name
        //     endforeach;
        // }

        // If the given player ID exists in the database, load that player's data and characters. Otherwise, redirect back to the login page.
        function handleStart() {
            global $player_id, $player_name, $input_table;
            
            $user_input = $_POST["playerID"];

            $query = oci_fetch_array(execSQL( // Check if any players with the given player ID exist in the database
            "SELECT playerID, playerName
            FROM Player
            WHERE playerID ='$user_input'"
            ), OCI_NUM);

            $player_id = $query[0];
            $player_name = $query[1];

            if(array_key_exists("inputs", $_POST)){
                //saving through input_table not currently working
                //$input_table = $_POST["inputs"];
            }
        }

        function handleFail() {
            global $player_table, $chr_table_attrs, $input_table;

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

            $input_table = getResult(execSQL(
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

        // Determines the new character ID
        // Sets the new ID to one higher than the highest existing ID
        function getNewID() {
            if (connectDB()) {
                $id = 99999; // If there are no players, set the player ID to 100000

                $result = execSQL(
                    "SELECT MAX(charID) 
                    FROM Chr"
                    );
                while ($row = oci_fetch_array($result, OCI_NUM)) { 
                    $id = $row[0]; 
                }

                disconnectDB();

                return ++$id;
            }
            return False;
        }
        
        // function checkValid($val, $isNum=TRUE, $canNull=FALSE, $hasDefault=FALSE){
        //     return true;
        // }

        function handleAdd() {
            global $db_connection, $input_table, $error_table,$player_id,$new_cid;
            $fmt_char_name = preg_replace("/[^A-Za-z0-9 \-]/", '', $_POST["charName"]);
            $class_test = $_POST["className"];
            $dexterity_test = $_POST["dexterity"];
            $constitution_test = $_POST["constitution"];
            $intelligence_test = $_POST["intelligence"];
            $strength_test = $_POST["strength"];
            $charisma_test = $_POST["charisma"];
            $wisdom_test = $_POST["wisdom"];

            if(empty($_POST["dexterity"])){
                $dexterity_test=0;
            }
            if(empty($_POST["constitution"])){
                $constitution_test=0;
            }
            if(empty($_POST["intelligence"])){
                $intelligence_test=0;
            }
            if(empty($_POST["strength"])){
                $strength_test=0;
            }
            if(empty($_POST["charisma"])){
                $charisma_test=0;
            }
            if(empty($_POST["wisdom"])){
                $wisdom_test=0;
            }
            if(empty($_POST["currentWeight"])){
                $cw_test=0;
            }

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
                (is_numeric($dexterity_test) || is_null($dexterity_test)) &&
                (is_numeric($constitution_test) || is_null($constitution_test)) &&
                (is_numeric($intelligence_test) || is_null($intelligence_test)) &&
                (is_numeric($strength_test) || is_null($strength_test)) &&
                (is_numeric($charisma_test) || is_null($charisma_test)) &&
                (is_numeric($wisdom_test) || is_null($wisdom_test)) &&
                (is_numeric($_POST["weightLimit"])) &&
                (is_numeric($cw_test) || is_null($cw_test)) &&
                (is_numeric($_POST["totalXP"])) &&
                ($class_exists) &&
                ($race_exists)
            ) {
                //set default values
                $cw_test = max(0, $_POST["currentWeight"]);
                $total_xp_test = max(0, $_POST["totalXP"]);
                //set other dependent values
                //$total_xp_test = $_POST["totalXP"];
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

                //$cw_test = $_POST["currentWeight"];
                $wl_test = $_POST["weightLimit"];
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
                    "INSERT INTO Chr
                    VALUES (
                        $new_cid,
                        $player_id,
                        '$fmt_char_name', 
                        $dexterity_test, 
                        $constitution_test, 
                        $intelligence_test, 
                        $strength_test, 
                        $charisma_test,
                        $wisdom_test,
                        $wl_test,
                        $cw_test,
                        $total_xp_test,
                        '$race_test'
                        )"
                );
                execSQL(
                    "INSERT INTO AssignedTo
                    VALUES (
                        $new_cid,
                        $player_id,
                        '$class_test'
                    )"
                );

                oci_commit($db_connection);
    
                echo"<form hidden='true' method='POST' action='main.php' id='reAdd'>
                <input type='hidden' id='playerID' name='playerID' value=$player_id>
                <input type='hidden' id='addedChr' name='addedChr'>
                </form>";
                echo "<script>document.getElementById('reAdd').submit(); </script>";
            } else {
                $input_table = [
                    $fmt_char_name,
                    $dexterity_test, 
                    $constitution_test, 
                    $intelligence_test, 
                    $strength_test, 
                    $charisma_test,
                    $wisdom_test,
                    $wl_test,
                    $cw_test,
                    $total_xp_test,
                    $race_test,
                    $class_test,
                    $new_cid,
                ];
            
                echo "<script>alert('Problem with edited fields. Ensure the race and class exist and all fields are their proper data type.');</script>";
                echo "
                <form hidden='true' method='POST' action='add-character.php' id='reTry'>
                <input type='hidden' id='playerID' name='playerID' value=$player_id>
                <input type='hidden' id='inputs' name='inputs' value=$input_table>
                </form>
                <script>document.getElementById('reTry').submit(); </script>";
                exit;
        
            }
        }

        // Routes different requests to their proper functions
        function handleRequest() {
            if (connectDB()) {
                // if(exists($_POST["playerID"])==FALSE){
                //     disconnectDB();
                //     echo "<script>alert('You are not logged in. Please Log in');</script>";
                //     echo "<script>window.location.href='index.php'; </script>";
                // exit;
                // }

                if (array_key_exists("playerID", $_POST)) {
                    handleStart();
                } else {
                    header("Location: index.php"); // If no player info given, redirect to the login page
                    exit;
                }

                if (array_key_exists("addBtn", $_POST)) {
                    handleStart();
                    handleAdd();
                } 
                // else if (array_key_exists("inputs", $_POST)){
                //     handleFail();
                // }

                disconnectDB();
            }
        }

        // END OF FUNCTIONS
        $new_cid = getNewID();
        if ((count($_POST) > 0) || (count($_GET) > 0)) {
            handleRequest();
            $new_cid = getNewID();
            
        } else {
            header("Location: index.php"); // If no character info given, redirect to the login page
            exit;
        }
        // END OF PHP
    ?>

    <div class="page">
    
    <form hidden='true' method='POST' action='main.php' id='reAdd'>
        <input type='hidden' id='playerID' name='playerID' value=<?=$player_id?>>
        <input type='hidden' id='addedChr' name='addedChr'>
    </form>

      <div class = "sidebar">
       <h2>Add Character</h2>
       <h4><?= $player_name; ?></h4>
       <h4><?= $player_id; ?></h4>
       <button onclick="location.href='index.php'">LOGOUT</button>
      </div>

      <div class="container">
        <h2>Character Creation Form</h2>
        <p ><span class="error">*</span> indicates required</p>
        <form method="POST" action="add-character.php" id="addForm">
            <table>
                <tr>Character ID: <?= $new_cid; ?></tr>
                <?php $i = 0; ?>
                <?php foreach($input_table_attrs as $attr_name): ?>
                    <tr>
                        <td>
                            <?= $attr_name; ?>:
                            <input id=<?= $attr_name; ?> 
                                name=<?= $attr_name; ?> 
                                placeholder=<?= $placeholder_table[$i]; ?>
                                value='<?= $input_table[$i]; ?>' />
                            <?php $i = ++$i; ?> 
                            <?php if(in_array($attr_name,$req_attrs)): ?>
                                <span class="error">*</span>
                            <?php endif; ?>
                            <span class="error">
                                <?php echo $error_table[$i]; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <input type="hidden" id="playerID" name="playerID" value=<?=$player_id?>>
        </form>
        <button type="submit" form="addForm" name="addBtn">Add Character</button>
      </div>
    </div>
    </body>
</html>