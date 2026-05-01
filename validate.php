<?php

// validate.php 
/**
 * Sanitize user input to prevent security issues
 */
function sanitize($value) {
    $value = trim($value);
    $value = stripslashes($value);
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    return $value;
}

/**
 * Validate a username required, letters/numbers/underscores, max 50 chars
 */
function is_valid_username($username) {
    $error = '';
    if (empty($username)) {
        $error = 'Username is required.';
    } elseif (preg_match('/^[a-zA-Z0-9_]+$/', $username) == 0) {
        $error = 'Username can only contain letters, numbers, and underscores.';
    } elseif (strlen($username) > 50) {
        $error = 'Username cannot exceed 50 characters.';
    }
    return $error;
}

/**
 * Validate an email address required, must be valid format
 */
function is_valid_email($email) {
    $error = '';
    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }
    return $error;
}

/**
 * Validate a name required, letters/spaces/hyphens only
 */
function is_valid_name($name, $field_name = 'Name') {
    $error = '';
    if (empty($name)) {
        $error = $field_name . ' is required.';
    } elseif (preg_match('/^[a-zA-Z\s\-]+$/', $name) == 0) {
        $error = $field_name . ' should only contain letters.';
    }
    return $error;
}

/**
 * Validate subscription type required, must be Free/Basic/Premium
 */
function is_valid_subscription($sub) {
    $error = '';
    $valid_subs = ['Free', 'Basic', 'Premium'];
    if (empty($sub)) {
        $error = 'Subscription type is required.';
    } elseif (!in_array($sub, $valid_subs)) {
        $error = 'Please select a valid subscription type.';
    }
    return $error;
}

/**
 * Validate a daterequired, must be a valid date
 */
function is_valid_date($value) {
    $error = '';
    if (empty($value)) {
        $error = 'Join date is required.';
    } else {
        $date = date_parse($value);
        if ($date['error_count'] > 0 || !checkdate($date['month'], $date['day'], $date['year'])) {
            $error = 'Please enter a valid date.';
        }
    }
    return $error;
}
?>