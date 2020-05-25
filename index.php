<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription Translator</title>
</head>
<body>
    <form action="" method="post">
        <label>Enter your prescription</label>
        <input type="text" name="input">
        <input type="submit" name="submit" value="SUBMIT">
        <input type="button" onclick="" name="generate" value="GENERATE RANDOM PRESCRIPTION">
   </form>
   <?php
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
        $connection = mysqli_connect("localhost", "root", "", "prescriptionDictionary");
        $output = "";
        if (isset($_POST["submit"]) && isset($_POST["input"])) {
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
        function generateRandomPrescription() {
            echo "generate random prescription";
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
   <p>
       <?php
            echo $output;
       ?>
    </p>
</body>
</html>