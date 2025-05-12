document.addEventListener('DOMContentLoaded', function() {
    console.log("Book tour script loaded");
    
    // Check if it's a new user or if tour was explicitly requested
    const urlParams = new URLSearchParams(window.location.search);
    const isNewUser = urlParams.get('new_user') === '1';
    const explicitTourRequest = urlParams.get('start_tour') === 'true';
    
    // Check for first login via PHP session variable
    // We'll assume the server includes this information in a data attribute
    const loginCountEl = document.querySelector('meta[name="login-count"]');
    const loginCount = loginCountEl ? parseInt(loginCountEl.getAttribute('content')) : null;
    const isFirstLogin = loginCount === 1; // First login would be login_count = 1
    
    // Tour should start for new users, first logins, or when explicitly requested
    const shouldStartTour = isNewUser || explicitTourRequest || isFirstLogin;
    
    console.log("New user:", isNewUser);
    console.log("First login:", isFirstLogin);
    console.log("Login count:", loginCount);
    console.log("Explicit tour request:", explicitTourRequest);
    console.log("Should start tour:", shouldStartTour);
    
    // Check if introJs is available
    if (typeof introJs === 'undefined') {
        console.error("IntroJs is not loaded! Please check the script inclusion.");
        return;
    }
    
    // Expose tour start function to global scope for manual testing
    window.startBookTour = function() {
        initAndStartTour();
    };
    
    if (shouldStartTour) {
        console.log("Starting tour for new user or first login...");
        
        // Only for new users, remove the 'new_user' parameter after starting the tour
        // This prevents the tour from showing again on refresh
        if (isNewUser) {
            // Update URL without the new_user parameter
            let url = new URL(window.location.href);
            url.searchParams.delete('new_user');
            window.history.replaceState({}, document.title, url.toString());
        }
        
        // Start the tour
        initAndStartTour();
    }
    
    // Function to initialize and start the tour
    function initAndStartTour() {
        try {
            // Set flag that tour has been shown
            sessionStorage.setItem('bookwagon_tour_shown', 'true');
            
            // Initialize the tour
            const tour = introJs();
            
            // Configure tour options
            tour.setOptions({
                steps: [
                    {
                        title: '👋 Welcome to BookWagon!',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/home.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Welcome'" alt="Welcome">
                                <p>Let's take a quick tour to show you around our platform. Click "Next" to continue.</p>
                            </div>
                        `,
                        position: 'center'
                    },
                    {
                        element: '.tab-menu a[href="dashboard.php"]',
                        title: '🏠 Home',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/home.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Home'" alt="Home">
                                <p>This is your home page where you can discover popular books, new releases, and more.</p>
                            </div>
                        `,
                        position: 'bottom'
                    },
                    {
                        element: '.tab-menu a[href="rentbooks.php"]',
                        title: '📚 Rent Books',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/renting.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Rent+Books'" alt="Rent Books">
                                <p>Browse our collection and rent books for a fraction of their purchase price.</p>
                            </div>
                        `,
                        position: 'bottom'
                    },
                    {
                        element: '.tab-menu a[href="explore.php"]',
                        title: '🗣️ Forum',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/forum.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Forum'" alt="Forum">
                                <p>Join discussions with other book lovers and share your thoughts on your favorite reads.</p>
                            </div>
                        `,
                        position: 'bottom'
                    },
                    {
                        element: '.tab-menu a[href="libraries.php"]',
                        title: '🏛️ Libraries',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/library.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Libraries'" alt="Libraries">
                                <p>Find libraries near you to expand your reading options.</p>
                            </div>
                        `,
                        position: 'bottom'
                    },
                    {
                        element: '.tab-menu a[href="bookswap.php"]',
                        title: '🔄 Book Swap',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/bookswaps.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Book+Swap'" alt="Book Swap">
                                <p>Trade books with other users in your area.</p>
                            </div>
                        `,
                        position: 'bottom'
                    },
                    {
                        element: '#heroCarousel',
                        title: '📢 Featured Content',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/home.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Featured'" alt="Featured Content">
                                <p>Stay updated with the latest book events, promotions, and news.</p>
                            </div>
                        `,
                        position: 'bottom'
                    },
                    {
                        element: '.section-header:nth-of-type(1)',
                        title: '🔥 Most Popular',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/home.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Popular'" alt="Most Popular">
                                <p>Discover trending books that everyone is reading right now.</p>
                            </div>
                        `,
                        position: 'bottom'
                    },
                    {
                        element: '.section-header:nth-of-type(2)',
                        title: '🆕 New Releases',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/renting.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=New+Releases'" alt="New Releases">
                                <p>Check out the latest additions to our collection.</p>
                            </div>
                        `,
                        position: 'bottom'
                    },
                    {
                        element: '.theme-tabs',
                        title: '🔎 Book Themes',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/forum.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Themes'" alt="Book Themes">
                                <p>Explore books by theme to find your next favorite read.</p>
                            </div>
                        `,
                        position: 'top'
                    },
                    {
                        element: '.libraries-section',
                        title: '📍 Local Libraries',
                        intro: `
                            <div class="tour-step-content">
                                <img src="images/tour/library.png" class="tour-character-img" onerror="this.src='https://via.placeholder.com/100x100?text=Local+Libraries'" alt="Local Libraries">
                                <p>Find libraries nearby to visit in person.</p>
                            </div>
                        `,
                        position: 'top'
                    },
                    {
                        title: '🎉 You\'re all set!',
                        intro: `
                            <div class="tour-step-content">
                                <div class="character-group">
                                    <img src="images/tour/home.png" class="tour-character-img small" onerror="this.src='https://via.placeholder.com/70x70?text=1'" alt="Character 1">
                                    <img src="images/tour/forum.png" class="tour-character-img small" onerror="this.src='https://via.placeholder.com/70x70?text=2'" alt="Character 2">
                                    <img src="images/tour/bookswaps.png" class="tour-character-img small" onerror="this.src='https://via.placeholder.com/70x70?text=3'" alt="Character 3">
                                </div>
                                <p>Thanks for joining BookWagon! Start exploring and enjoy your reading journey.</p>
                                <p class="tour-tip">If you need this tour again, click your profile and select "Restart Tour".</p>
                            </div>
                        `,
                        position: 'center'
                    }
                ],
                showStepNumbers: false,
                showBullets: true,
                exitOnOverlayClick: false,
                exitOnEsc: true,
                nextLabel: 'Next →',
                prevLabel: '← Back',
                doneLabel: 'Start Exploring',
                tooltipClass: 'book-tour-tooltip',
                highlightClass: 'book-tour-highlight',
                buttonClass: 'book-tour-button'
            });
            
            // Add custom styles for the tour
            const style = document.createElement('style');
            style.textContent = `
                .book-tour-tooltip {
                    max-width: 350px;
                    background-color: white;
                    border: none;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                    border-radius: 12px;
                    padding: 20px;
                }
                
                .book-tour-highlight {
                    background-color: rgba(217, 185, 155, 0.2) !important;
                    border-radius: 4px;
                    box-shadow: 0 0 0 8px rgba(217, 185, 155, 0.2) !important;
                    transition: all 0.3s ease-out;
                }
                
                .introjs-tooltip-title {
                    font-size: 1.2rem;
                    font-weight: 600;
                    margin-bottom: 10px;
                    color: #1e293b;
                }
                
                .introjs-tooltiptext {
                    font-size: 0.95rem;
                    line-height: 1.5;
                    color: #64748b;
                }
                
                .tour-step-content {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                }
                
                .tour-character-img {
                    width: 100px;
                    height: 100px;
                    object-fit: contain;
                    margin-bottom: 15px;
                    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
                    animation: float 3s ease-in-out infinite;
                }
                
                .tour-character-img.small {
                    width: 70px;
                    height: 70px;
                    margin: 0 5px;
                }
                
                .character-group {
                    display: flex;
                    justify-content: center;
                    margin-bottom: 15px;
                }
                
                .tour-tip {
                    font-size: 0.85rem;
                    font-style: italic;
                    color: #94a3b8;
                    margin-top: 10px;
                }
                
                @keyframes float {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-8px); }
                }
                
                .book-tour-button {
                    border-radius: 20px;
                    padding: 6px 14px;
                    font-weight: 500;
                    font-size: 0.9rem;
                    background-color: #d9b99b;
                    border: none;
                    color: white;
                    text-shadow: none;
                    transition: all 0.2s ease;
                }
                
                .book-tour-button:hover {
                    background-color: #c4a689;
                    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
                }
                
                .introjs-prevbutton {
                    border: 1px solid #d9b99b;
                    color: #d9b99b;
                    background-color: transparent;
                }
                
                .introjs-prevbutton:hover {
                    color: #c4a689;
                    border-color: #c4a689;
                    background-color: rgba(217, 185, 155, 0.1);
                }
                
                .introjs-bullets ul li a {
                    width: 8px;
                    height: 8px;
                    background: #e2e8f0;
                }
                
                .introjs-bullets ul li a.active {
                    background: #d9b99b;
                }
            `;
            document.head.appendChild(style);
            
            // Start the tour
            tour.start();
            
            // After tour completion
            tour.oncomplete(function() {
                showConfetti();
            });
            
            // Show a welcome message with confetti after tour completes
            function showConfetti() {
                // Add confetti effect
                const confettiDuration = 3000;
                const confettiEnd = Date.now() + confettiDuration;
                
                const confettiCanvas = document.createElement('canvas');
                confettiCanvas.id = 'welcome-confetti';
                confettiCanvas.style.position = 'fixed';
                confettiCanvas.style.top = '0';
                confettiCanvas.style.left = '0';
                confettiCanvas.style.width = '100%';
                confettiCanvas.style.height = '100%';
                confettiCanvas.style.zIndex = '999999';
                confettiCanvas.style.pointerEvents = 'none';
                document.body.appendChild(confettiCanvas);
                
                // Simple confetti effect (you can replace with a more robust library if needed)
                const context = confettiCanvas.getContext('2d');
                confettiCanvas.width = window.innerWidth;
                confettiCanvas.height = window.innerHeight;
                
                const colors = ['#d9b99b', '#ffd0b1', '#f8b079', '#e69c68', '#6366f1'];
                const shapes = ['circle', 'square', 'triangle'];
                const particles = [];
                
                for (let i = 0; i < 200; i++) {
                    particles.push({
                        x: Math.random() * confettiCanvas.width,
                        y: Math.random() * confettiCanvas.height - confettiCanvas.height,
                        color: colors[Math.floor(Math.random() * colors.length)],
                        shape: shapes[Math.floor(Math.random() * shapes.length)],
                        size: 5 + Math.random() * 15,
                        velocity: {
                            x: -1 + Math.random() * 2,
                            y: 1 + Math.random() * 3
                        },
                        rotation: 0,
                        rotationSpeed: -0.2 + Math.random() * 0.4
                    });
                }
                
                function drawConfetti() {
                    context.clearRect(0, 0, confettiCanvas.width, confettiCanvas.height);
                    
                    particles.forEach(particle => {
                        context.save();
                        context.translate(particle.x, particle.y);
                        context.rotate(particle.rotation);
                        context.fillStyle = particle.color;
                        
                        if (particle.shape === 'circle') {
                            context.beginPath();
                            context.arc(0, 0, particle.size / 2, 0, Math.PI * 2);
                            context.fill();
                        } else if (particle.shape === 'square') {
                            context.fillRect(-particle.size / 2, -particle.size / 2, particle.size, particle.size);
                        } else if (particle.shape === 'triangle') {
                            context.beginPath();
                            context.moveTo(0, -particle.size / 2);
                            context.lineTo(particle.size / 2, particle.size / 2);
                            context.lineTo(-particle.size / 2, particle.size / 2);
                            context.closePath();
                            context.fill();
                        }
                        
                        context.restore();
                        
                        particle.x += particle.velocity.x;
                        particle.y += particle.velocity.y;
                        particle.rotation += particle.rotationSpeed;
                        
                        if (particle.y > confettiCanvas.height) {
                            particle.y = -particle.size;
                            particle.x = Math.random() * confettiCanvas.width;
                        }
                    });
                    
                    if (Date.now() < confettiEnd) {
                        requestAnimationFrame(drawConfetti);
                    } else {
                        document.body.removeChild(confettiCanvas);
                    }
                }
                
                requestAnimationFrame(drawConfetti);
            }
        } catch (error) {
            console.error("Error initializing tour:", error);
        }
    }
    
    // Add a "Restart Tour" option to the user menu
    function addRestartTourOption() {
        // Look for the user dropdown menu
        const userDropdownElements = document.querySelectorAll('.dropdown-menu');
        
        userDropdownElements.forEach(dropdown => {
            if (dropdown.textContent.includes('Profile') || dropdown.textContent.includes('Settings')) {
                // Create a new menu item
                const restartItem = document.createElement('a');
                restartItem.className = 'dropdown-item';
                restartItem.href = '?start_tour=true';
                restartItem.innerHTML = '<i class="fas fa-compass me-2"></i>Restart Tour';
                
                // Insert before the last item (usually logout)
                dropdown.insertBefore(restartItem, dropdown.lastElementChild);
            }
        });
    }
    
    addRestartTourOption();
}); 