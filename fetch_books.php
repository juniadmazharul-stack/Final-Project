<?php
// fetch_books.php - This file processes the XML and returns HTML for the AJAX call
header('Content-Type: text/html');

// 1. Get the filter parameter from the AJAX request
$filterGenre = isset($_GET['genre']) ? strtolower($_GET['genre']) : 'all';

// 2. Load the XML file using SimpleXML
if (!file_exists('books.xml')) {
    echo "Error: books.xml file not found.";
    exit;
}

$xml = simplexml_load_file('books.xml');

// Start the HTML output table structure
$output = '<table class="data-table">';
$output .= '<tr><th>Title</th><th>Author</th><th>Year</th><th>Genre</th></tr>';

$found = false;

// 3. Loop through each book element
foreach ($xml->book as $book) {
    // Convert current book genre to lowercase for case-insensitive comparison
    $bookGenre = strtolower((string)$book->genre);
    
    // 4. Implement the filtering logic
    if ($filterGenre == 'all' || $bookGenre == $filterGenre) {
        $found = true;
        // Output a table row for books that match the filter (or all)
        $output .= '<tr>';
        $output .= '<td>' . htmlspecialchars((string)$book->title) . '</td>';
        $output .= '<td>' . htmlspecialchars((string)$book->author) . '</td>';
        $output .= '<td>' . htmlspecialchars((string)$book->year) . '</td>';
        $output .= '<td>' . htmlspecialchars((string)$book->genre) . '</td>';
        $output .= '</tr>';
    }
}

if (!$found) {
    $output .= '<tr><td colspan="4">No books found for this genre.</td></tr>';
}

$output .= '</table>';

// 5. Echo the final HTML output back to the AJAX front-end
echo $output;
?>