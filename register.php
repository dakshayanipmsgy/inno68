<?php
session_start();
require_once __DIR__ . '/functions.php';

$roles = ['Vendor', 'Consumer', 'Financier', 'DISCOM'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? '';

    if ($name === '') {
        $errors[] = 'Name is required.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }

    if ($password === '' || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }

    if (!in_array($role, $roles, true)) {
        $errors[] = 'Please select a valid role.';
    }

    if (empty($errors)) {
        $users = readJSON('users.json');
        $emailExists = false;

        foreach ($users as $user) {
            if (isset($user['email']) && strcasecmp($user['email'], $email) === 0) {
                $emailExists = true;
                break;
            }
        }

        if ($emailExists) {
            $errors[] = 'An account with that email already exists.';
        } else {
            $users[] = [
                'id' => uniqid('user_', true),
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => $phone,
                'role' => $role,
                'created_at' => date('c'),
            ];

            if (writeJSON('users.json', $users)) {
                $success = 'Registration successful. You can now log in.';
            } else {
                $errors[] = 'Unable to save your registration. Please try again.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Digital RESCO Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f766e, #1e3a8a);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .form-control, .form-select {
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="fw-bold mb-0 text-primary">Create Account</h2>
                    <a href="index.php" class="text-decoration-none">Home</a>
                </div>
                <p class="text-muted">Join the Digital RESCO Solar Platform to unlock financing and clean energy opportunities.</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="role" class="form-label">Select Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="" disabled <?= empty($role) ? 'selected' : '' ?>>Choose a role</option>
                            <?php foreach ($roles as $option): ?>
                                <option value="<?= $option ?>" <?= (isset($role) && $role === $option) ? 'selected' : '' ?>><?= $option ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Register</button>
                        <a href="login.php" class="btn btn-outline-secondary">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
