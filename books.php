<?php
$page_title = "Book Finder";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - My First Web App</title>
    <link rel="stylesheet" href="style1.css">
    <script>
        function filterBooks(genre) {
            const resultsDiv = document.getElementById('book-results');
            resultsDiv.innerHTML = "Loading...";

            // AJAX request to fetch_books.php
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_books.php?genre=' + genre, true);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    resultsDiv.innerHTML = xhr.responseText;
                } else {
                    resultsDiv.innerHTML = "Error loading books.";
                }
            };
            xhr.send();
        }

        // Load all books by default when page opens
        window.onload = function() {
            filterBooks('all');
        };
    </script>
</head>
<body>

    <header>
        <div class="container">
            <h1>Book Finder</h1>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="calculator.php">Calculator</a></li>
                <li><a href="directory.php">Student Directory</a></li>
                <li><a href="guestbook.php">Guestbook</a></li>
                <li><a href="books.php">Book Finder</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <section class="card">
            <h2>Search Catalog</h2>
            <p>Select a genre to filter the book list dynamically using AJAX:</p>
            
            <div class="form-group">
                <label for="genre-select">Filter by Genre:</label>
                <select id="genre-select" onchange="filterBooks(this.value)">
                    <option value="all">All Genres</option>
                    <option value="fantasy">Fantasy</option>
                    <option value="dystopian">Dystopian</option>
                    <option value="science fiction">Science Fiction</option>
                    <option value="romance">Romance</option>
                    <option value="literary fiction">Literary Fiction</option>
                </select>
            </div>

            <div id="book-results" class="table-container">
                <!-- AJAX content will load here -->
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?>  Final Project Application | Designed by Mazharul Juniad</p>
        </div>
    </footer>

</body>
</html>