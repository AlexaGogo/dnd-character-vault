<!DOCTYPE html>
<html>
  <head>
    <title>Login Page</title>
    <link href="css/styles.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredericka+the+Great&family=Cinzel:wght@400;500;600;700&family=Eczar&family=Vollkorn:wght@400;500;600&display=swap" rel="stylesheet">
  </head>
  <body>
    <div class="container">

      <div class = "header">
        <h1>D
          <img src="images/dnd_logo.png" alt="dnd_logo" id="logo">
          D Database
        </h1>
        <a href="add-player.php">
          <button>ADD PLAYER</button>
        </a>
      </div>
    
      <p>
        Welcome, adventurers. First time?
      </p>

      <div class="input-form">
        <form method="POST" action="main.php" id="playLoginForm">
          <h4>Player Login</h4>
          Player ID: <input id="playerID" name="playerID" />
        </form>
        <button type="submit" form="playLoginForm" name="plogin" value="login">Login</button>
      </div>
      
      <div class="input-form">
        <form method="POST" action="dm.php" id="dmLoginForm">
          <h4>Dungeon Master Login</h4>
          Dungeon Master ID: <input id="dmID" name="dmID" />
        </form>
        <button type="submit" form="dmLoginForm" name="dmlogin" value="login">Login</button>
      </div>

      <div class="footer">
        <div id="debugger">
          <!--
          <a href="debug.php" class="link-button">Debug</a>
          <br><br>
          -->
          <a href="debug.php"><img src="images/d20.png" id="d20" alt="debug"></a>
        </div>
        <p>Adventure hinges on more than just a throw of the dice...</p>
      </div>
    </div>
  </body>
</html>

