        </main>
        <footer class="border-t border-white/10 mt-16">
            <div class="container mx-auto px-4 py-6 text-center text-gray-500">
                <p>&copy; <?= date('Y') ?> GameZone DZ. All Rights Reserved.</p>
            </div>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="js/cart.js"></script>
    <script src="js/animations.js"></script>
    <script>
    particlesJS("particles-js", {
        "particles": {
            "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
            "color": { "value": "#d900ff" },
            "shape": { "type": "circle" },
            "opacity": { "value": 0.5, "random": true, "anim": { "enable": true, "speed": 1, "opacity_min": 0, "sync": false } },
            "size": { "value": 3, "random": true },
            "line_linked": { "enable": false },
            "move": { "enable": true, "speed": 0.5, "direction": "none", "random": true, "straight": false, "out_mode": "out", "bounce": false }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": { "onhover": { "enable": true, "mode": "bubble" }, "onclick": { "enable": false }, "resize": true },
            "modes": { "bubble": { "distance": 150, "size": 6, "duration": 2, "opacity": 1 } }
        },
        "retina_detect": true
    });
    </script>
</body>
</html>