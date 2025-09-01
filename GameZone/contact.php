<?php
include 'includes/header.php';

// Simple form submission simulation
$message_sent = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // In a real application, you would process and send an email here.
    // For this local XAMPP setup, we'll just simulate a success message.
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);
    
    // Simulate success
    $message_sent = true;
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto bg-slate-800 p-8 rounded-lg shadow-lg">
        <h1 class="text-4xl font-['Orbitron'] font-bold text-center mb-2 text-transparent bg-clip-text bg-gradient-to-r from-[#9333ea] to-[#22d3ee]">
            Contact Us
        </h1>
        <p class="text-center text-gray-400 mb-8">Have a question or need support? Drop us a line!</p>

        <?php if ($message_sent): ?>
            <div class="bg-green-500/20 text-green-300 p-4 rounded-md text-center">
                <h3 class="font-bold text-lg">Thank You!</h3>
                <p>Your message has been received. We'll get back to you shortly.</p>
            </div>
        <?php else: ?>
            <form action="contact.php" method="POST" class="space-y-6">
                <div>
                    <label for="name" class="block mb-2 font-semibold">Your Name</label>
                    <input type="text" name="name" id="name" required class="w-full bg-slate-700 p-3 rounded-md focus:ring-2 focus:ring-[#9333ea] outline-none">
                </div>
                <div>
                    <label for="email" class="block mb-2 font-semibold">Your Email</label>
                    <input type="email" name="email" id="email" required class="w-full bg-slate-700 p-3 rounded-md focus:ring-2 focus:ring-[#9333ea] outline-none">
                </div>
                <div>
                    <label for="subject" class="block mb-2 font-semibold">Subject</label>
                    <input type="text" name="subject" id="subject" required class="w-full bg-slate-700 p-3 rounded-md focus:ring-2 focus:ring-[#9333ea] outline-none">
                </div>
                <div>
                    <label for="message" class="block mb-2 font-semibold">Message</label>
                    <textarea name="message" id="message" rows="5" required class="w-full bg-slate-700 p-3 rounded-md focus:ring-2 focus:ring-[#9333ea] outline-none"></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="neon-button">Send Message</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>