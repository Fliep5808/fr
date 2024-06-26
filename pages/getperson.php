<?php
require 'db.php';

if (isset($_GET['id'])) {
    $person = getPersonById($_GET['id']);
    if ($person) {
        echo '<label for="mobile">Mobile:</label>';
        echo '<input type="text" name="mobile" value="' . $person['mobile'] . '"><br>';
        echo '<label for="status">Status:</label>';
        echo '<input type="text" name="status" value="' . $person['status'] . '"><br>';
        echo '<label for="notes">Notes:</label>';
        echo '<textarea name="notes">' . $person['notes'] . '</textarea><br>';
    }
}
?>