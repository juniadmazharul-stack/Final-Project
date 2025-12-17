<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paycheck Calculator - My First Web App</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>

    <header>
        <h1>Paycheck Calculator</h1>
    </header>

    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="calculator.php">Calculator</a></li>
            <li><a href="directory.php">Student Directory</a></li>
            <li><a href="guestbook.php">Guestbook</a></li>
            <li><a href="books.php">Book Finder</a></li>
        </ul>
    </nav>

    <main>
        <h2>Calculate Your Gross Pay</h2>

        <form method="post" action="calculator.php">
            <label for="hours">Hours Worked:</label>
            <input type="number" step="0.01" name="hours" id="hours" required>
            <br><br>

            <label for="rate">Hourly Pay Rate ($):</label>
            <input type="number" step="0.01" name="rate" id="rate" required>
            <br><br>

            <button type="submit" name="calculate">Calculate Paycheck</button>
        </form>

        <hr>

        <?php
        if (isset($_POST['calculate'])) {
            $hours = $_POST['hours'];
            $rate = $_POST['rate'];

            if (is_numeric($hours) && is_numeric($rate) && $hours >= 0 && $rate >= 0) {
                $salary = $hours * $rate;

                echo "<h3>Paycheck Calculation Result</h3>";
                echo "<p>Hours Worked: <strong>" . number_format($hours, 2) . "</strong></p>";
                echo "<p>Rate of Pay: <strong>$" . number_format($rate, 2) . "</strong> per hour</p>";
                echo "<p><strong>Total Pay: $" . number_format($salary, 2) . "</strong></p>";
            } else {
                echo "<p style='color:red;'>Please enter valid, non-negative numeric values for both fields.</p>";
            }
        }
        ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?>  Final Project Application | Designed by Mazharul Juniad</p>
    </footer>

</body>
</html>

