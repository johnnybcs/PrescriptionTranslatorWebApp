<?php
/*
ClearDB is the Heroku database.

CLEARDB_DATABASE_URL => mysql://[username]:[password]@[host]/[database name]?reconnect=true

CLEARDB_DATABASE_URL: mysql://b9b5eb281a6874:c6f7e695@us-cdbr-east-06.cleardb.net/heroku_993c503940d0c6c?    reconnect=true

Use $connection = mysqli_connect(...) for local development
*/
// $connection = mysqli_connect("localhost", "root", "", "prescriptionDictionary");
    $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
    $server = $url["host"];
    $username = $url["user"];
    $password = $url["pass"];
    $db = substr($url["path"], 1);
    $connection = new mysqli($server, $username, $password, $db);

    $input = "&nbsp";
    $output = "&nbsp";
    $randomPrescription = "";

    class Prescription {
        public $hourlyFrequency;
        public $dailyFrequency;
        public $timing;
        public $amount;
        public $methodOfDelivery;

        function __construct() {
            $this->hourlyFrequency = "";
            $this->dailyFrequency = "";
            $this->timing = "";
            $this->amount = "";
            $this->methodOfDelivery = "";
        }
    }

    $prescription = new Prescription();

    if (isset($_POST["submit"]) && isset($_POST["input"]) && $_POST["input"] != "") {
        global $input;
        $input = $_POST["input"];
        $input = strtolower($input);
        $input = str_replace(".", "", $input);
        $tokens = explode(" ", $input);
        for ($i = 0; $i < count($tokens); $i++) {
            $tokens[$i] = trim($tokens[$i]);
            getHourlyFrequency($tokens[$i]);
            getDailyFrequency($tokens[$i]);
            getTiming($tokens[$i]);
            getAmount($tokens[$i], $tokens, $i);
            getMethodOfDelivery($tokens[$i]);
        }
        $output = "Take " . $prescription->amount . " " . $prescription->methodOfDelivery
            . " " . $prescription->hourlyFrequency . " " . $prescription->timing
            . " " . $prescription->dailyFrequency;
    }

    function getAmount($token, $tokens, $i) {
        global $connection;
        global $prescription;
        $query = "SELECT * FROM amount WHERE abbreviation = '{$token}'";
        $result = mysqli_query($connection, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row && isset($tokens[$i - 1])) {
            $prescription->amount = $tokens[$i - 1] . " " . $row["translation"];
        } else {
            $tokenWithNumRemoved = preg_replace('/[0-9]+/', '', $token);
            $query = "SELECT * FROM amount WHERE abbreviation = '{$tokenWithNumRemoved}'";
            $result = mysqli_query($connection, $query);
            $row = mysqli_fetch_assoc($result);
            if ($row) {
                $prescription->amount = $token;
            }
        }
    }

    function getMethodOfDelivery($token) {
        global $connection;
        global $prescription;
        $query = "SELECT * FROM methodOfDelivery WHERE abbreviation = '{$token}'";
        $result = mysqli_query($connection, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row) { 
            $prescription->methodOfDelivery = $row["translation"];
        }
    }

    function getHourlyFrequency($token) {
        global $connection;
        global $prescription;
        $query = "SELECT * FROM hourlyFrequency WHERE abbreviation = '{$token}'";
        $result = mysqli_query($connection, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row) { 
            $prescription->hourlyFrequency = $row["translation"];
        }
    }

    function getTiming($token) {
        global $connection;
        global $prescription;
        $query = "SELECT * FROM timing WHERE abbreviation = '{$token}'";
        $result = mysqli_query($connection, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row) { 
            $prescription->timing = $row["translation"];
        }
    }

    function getDailyFrequency($token) {
        global $connection;
        global $prescription;
        $query = "SELECT * FROM dailyFrequency WHERE abbreviation = '{$token}'";
        $result = mysqli_query($connection, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            $prescription->dailyFrequency = $row["translation"];
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription Translator</title>
    <link rel="icon" href="favicon.png">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</head>
<body>
   <div class="container">
     <div class="row justify-content-center">
         <h1 class="display-3 text-white">Prescription Translator</h1>
     </div>
     <br>
     <br>
      <div class="row">
        <div class="col-sm">
          <br>
           <form action="" method="post">
                <div class="form-group">
                    <label class="text-white" style="font-size:30px; " for="input">Enter your prescription</label>
                    <input class="form-control form-control-lg" id="input" type="text" name="input">
                </div>
                <input class="btn btn-primary" type="submit" name="submit" value="SUBMIT">
                <input class="btn btn-success" type="submit" name="generate" value="GENERATE RANDOM PRESCRIPTION">
            </form>
        </div>
        <div class="col-sm">
           <br>
            <div class="jumbotron" style="padding: 1em 1em;">
               <h4 style="display:inline; text-decoration-skip-ink: none;"><?php echo "<i>$input</i>" ?></h4>
               <hr class="my-4">
               <h1 style="display:inline; color:#217fe5; ">translates into</h1>
               <hr class="my-4">
               <h4 class="lead"><?php echo $output; ?></h4>
            </div>
        </div>
        </div>
    </div>
<?php
    if (isset($_POST["generate"])) {
        generateRandomPrescription();
    }

    function generateRandomPrescription() {
        global $connection;
        $id = rand(1,10);
        $query = "SELECT * FROM drugPrescription WHERE id = {$id}";
        $result = mysqli_query($connection, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            $randomPrescription = $row["name"] . " " . 
                                  $row["amount"] . " " .  
                                  $row["methodOfDelivery"] . " " .
                                  $row["hourlyFrequency"] . " " .
                                  $row["timing"] . " " .
                                  $row["dailyFrequency"];
?>
<script>
            var input = document.getElementById("input");
            input.value = "<?php echo $randomPrescription ?>";
</script>
<?php
        }
    }
?>
</body>
</html>