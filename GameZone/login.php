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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $name, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    // Set session
                    $_SESSION['user_id']   = $id;
                    $_SESSION['user_name'] = $name;

                    header('Location: index.php');
                    exit();
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
            $stmt->close();
        } else {
            $error = 'Database error: unable to prepare statement.';
        }
    }
}

include 'includes/header.php';
?>
<div class="flex items-center justify-center min-h-screen">
    <div class="bg-slate-800 p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-3xl font-['Orbitron'] font-bold text-center mb-6 text-transparent bg-clip-text bg-gradient-to-r from-[#9333ea] to-[#22d3ee]">
            Login
        </h1>

        <?php if ($error): ?>
            <div class="bg-red-500/20 text-red-300 p-3 rounded-md mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
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
            <button type="submit" class="w-full neon-button">Login</button>
        </form>

        <p class="mt-6 text-center text-gray-400">
            Don’t have an account? <a href="register.php" class="text-[#22d3ee] hover:underline">Register here</a>
        </p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
