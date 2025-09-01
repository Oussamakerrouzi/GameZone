<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'Email is already registered.';
            } else {
                // Insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                if ($stmt) {
                    $stmt->bind_param("sss", $name, $email, $hashed_password);
                    if ($stmt->execute()) {
                        $success = 'Registration successful! You can now <a href="login.php" class="text-[#22d3ee] underline">login</a>.';
                    } else {
                        $error = 'Error registering user. Please try again.';
                    }
                } else {
                    $error = 'Database error: could not prepare statement.';
                }
            }
            $stmt->close();
        } else {
            $error = 'Database error: could not prepare statement.';
        }
    }
}

include 'includes/header.php';
?>
<div class="flex items-center justify-center min-h-screen">
    <div class="bg-slate-800 p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-3xl font-['Orbitron'] font-bold text-center mb-6 text-transparent bg-clip-text bg-gradient-to-r from-[#9333ea] to-[#22d3ee]">
            Register
        </h1>

        <?php if ($error): ?>
            <div class="bg-red-500/20 text-red-300 p-3 rounded-md mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500/20 text-green-300 p-3 rounded-md mb-4"><?= $success ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-6">
            <div>
                <label for="name" class="block mb-2">Name</label>
                <input type="text" name="name" id="name" required
                    value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                    class="w-full bg-slate-700 p-3 rounded-md focus:ring-2 focus:ring-[#9333ea] outline-none">
            </div>
            <div>
                <label for="email" class="block mb-2">Email</label>
                <input type="email" name="email" id="email" required
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                    class="w-full bg-slate-700 p-3 rounded-md focus:ring-2 focus:ring-[#9333ea] outline-none">
            </div>
            <div>
                <label for="password" class="block mb-2">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full bg-slate-700 p-3 rounded-md focus:ring-2 focus:ring-[#9333ea] outline-none">
            </div>
            <div>
                <label for="confirm_password" class="block mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                    class="w-full bg-slate-700 p-3 rounded-md focus:ring-2 focus:ring-[#9333ea] outline-none">
            </div>
            <button type="submit" class="w-full neon-button">Register</button>
        </form>

        <p class="mt-6 text-center text-gray-400">
            Already have an account? <a href="login.php" class="text-[#22d3ee] hover:underline">Login here</a>
        </p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
