<?php
function is_set($key)
{
    return isset($_GET[$key]) || isset($_POST[$key]);
}

function get($key)
{
    return isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : null);
}

function get_safe($key)
{
    global $conn;
    $value = get($key);

    if (is_null($value)) {
        return null;
    }

    if (is_array($value)) {
        // Iterate through the array and escape each value
        return array_map(function($item) use ($conn) {
            return mysqli_real_escape_string($conn, $item);
        }, $value);
    } else {
        return mysqli_real_escape_string($conn, $value);
    }
}
?>
