<?php
// Profile actions handler
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $currentUser = Auth::user();
    $userId = (int)($currentUser['userId'] ?? $currentUser['userid'] ?? 0);

    if ($action === 'updateProfile' && $userId > 0) {
        $ok = User::updateProfile($userId, $_POST);
        if (!empty($_FILES['licensePdf']['tmp_name'])) {
            User::uploadLicenseDocument($userId, $_FILES['licensePdf']);
        }
        if ($ok) {
            $_SESSION['flash_success'] = 'Your profile details have been updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update profile. Please verify required fields.';
        }
        $currentPage = basename($_SERVER['PHP_SELF']);
        header("Location: {$currentPage}?view=profileView");
        exit;
    }

    if ($action === 'updatePassword' && $userId > 0) {
        $currentPass = trim($_POST['currentPassword'] ?? '');
        $newPass = trim($_POST['newPassword'] ?? '');
        $confirmPass = trim($_POST['confirmPassword'] ?? '');

        if (empty($currentPass) || empty($newPass)) {
            $_SESSION['flash_error'] = 'Please enter both current and new passwords.';
        } elseif ($newPass !== $confirmPass) {
            $_SESSION['flash_error'] = 'New passwords do not match. Please verify.';
        } elseif (strlen($newPass) < 6) {
            $_SESSION['flash_error'] = 'New password must be at least 6 characters long.';
        } else {
            $ok = User::changePassword($userId, $currentPass, $newPass);
            if ($ok) {
                $_SESSION['flash_success'] = 'Password changed successfully.';
            } else {
                $_SESSION['flash_error'] = 'Current password is incorrect.';
            }
        }
        $currentPage = basename($_SERVER['PHP_SELF']);
        header("Location: {$currentPage}?view=profileView");
        exit;
    }
}
