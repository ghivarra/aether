<?php

$filePath = ROOTPATH . '.env';

// Check if the file exists and is readable
if (!file_exists($filePath) || !is_readable($filePath))
{
    // You might want to log an error here instead of just returning false
    error_log("Error: .env file not found or not readable at '{$filePath}'");
    return false;
}

// Open the .env file for reading
$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Check if file() successfully read the lines
if ($lines === false)
{
    error_log("Error: Could not read lines from '{$filePath}'");
    return false;
}

foreach ($lines as $line)
{
    // Trim whitespace from the beginning and end of the line
    $line = trim($line);

    // Ignore comments (lines starting with #) and empty lines (already handled by file() flags, but good for robustness)
    if (empty($line) || str_starts_with($line, '#')) {
        continue;
    }

    // Find the position of the first equals sign
    $equalsPos = strpos($line, '=');

    // If no equals sign or it's at the very beginning (e.g., "=VALUE"), skip
    if ($equalsPos === false || $equalsPos === 0) {
        continue;
    }

    // Extract the key and value
    $key = substr($line, 0, $equalsPos);
    $value = substr($line, $equalsPos + 1);

    // Trim whitespace from the key and the value AFTER splitting
    $key = trim($key);
    $value = trim($value); // <-- FIX: Trim whitespace from the value here

    // Remove quotes from value if present
    // Handles 'value', "value", and 'value with spaces' or "value with spaces"
    if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
        $value = substr($value, 1, -1);
        // Handle escaped double quotes inside double-quoted strings
        $value = str_replace('\"', '"', $value);
    } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
        $value = substr($value, 1, -1);
        // Handle escaped single quotes inside single-quoted strings
        $value = str_replace("\'", "'", $value);
    }

    // Set the environment variable using putenv() for server-level access
    // and $_ENV for script-level access
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    // You might also want to set $_SERVER[$key] = $value; if needed for compatibility
    // $_SERVER[$key] = $value;
}