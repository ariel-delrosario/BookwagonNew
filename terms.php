<?php
// No need for database connection in this file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - BookWagon</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #f8b079;
            --primary-light: #ffd0b1;
            --primary-dark: #e69c68;
            --secondary-color: #f8fafc;
            --accent-color: #6366f1; /* Indigo */
            --text-dark: #1e293b;
            --text-light: #64748b;
            --text-muted: #94a3b8;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            background-color: var(--secondary-color);
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            line-height: 1.7;
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        
        .navbar-brand img {
            height: 45px;
            margin-right: 10px;
        }
        
        .navbar-brand span {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .terms-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            padding: 40px;
            margin: 40px auto;
        }
        
        h1 {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }
        
        h2 {
            color: var(--text-dark);
            font-weight: 600;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        p {
            color: var(--text-light);
            margin-bottom: 20px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .last-updated {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-style: italic;
            margin-top: -20px;
            margin-bottom: 30px;
        }
        
        .btn-back {
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        footer {
            background-color: white;
            padding: 30px 0;
            margin-top: 50px;
            text-align: center;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        ul {
            padding-left: 20px;
        }
        
        ul li {
            margin-bottom: 10px;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="images/logo.png" alt="BookWagon Logo">
                <span>BookWagon</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="signup.php">Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Terms of Service Content -->
    <div class="container">
        <div class="terms-container">
            <h1>Terms of Service</h1>
            <p class="last-updated">Last Updated: <?php echo date("F j, Y"); ?></p>
            
            <div class="section">
                <p>Welcome to BookWagon! These Terms of Service ("Terms") govern your use of the BookWagon website, services, and applications (collectively, the "Service"). By accessing or using our Service, you agree to be bound by these Terms. If you disagree with any part of the Terms, you may not access the Service.</p>
            </div>
            
            <div class="section">
                <h2>1. Accounts</h2>
                <p>When you create an account with us, you must provide accurate, complete, and up-to-date information. You are responsible for safeguarding the password you use to access the Service and for any activities or actions under your password.</p>
                <p>You agree not to disclose your password to any third party. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.</p>
            </div>
            
            <div class="section">
                <h2>2. Book Listings and Transactions</h2>
                <p>BookWagon facilitates the buying, selling, and renting of books between users. When listing a book for sale or rent, you agree to:</p>
                <ul>
                    <li>Provide accurate descriptions of the book's condition</li>
                    <li>Set fair prices that reflect the book's condition and market value</li>
                    <li>Honor your commitments to sell or rent when a transaction is agreed upon</li>
                    <li>Respond promptly to inquiries about your listings</li>
                </ul>
                <p>As a buyer or renter, you agree to:</p>
                <ul>
                    <li>Make payments promptly and using the designated payment methods</li>
                    <li>Treat borrowed books with care and return them in the same condition</li>
                    <li>Complete transactions as agreed upon with sellers</li>
                </ul>
            </div>
            
            <div class="section">
                <h2>3. Service Fees</h2>
                <p>BookWagon may charge fees for certain aspects of the Service. You agree to pay all fees and charges associated with your account on a timely basis and with a valid payment method. All fees are non-refundable unless otherwise stated.</p>
            </div>
            
            <div class="section">
                <h2>4. Intellectual Property</h2>
                <p>The Service and its original content (excluding content provided by users), features, and functionality are and will remain the exclusive property of BookWagon and its licensors. The Service is protected by copyright, trademark, and other laws of the Philippines and foreign countries.</p>
                <p>Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of BookWagon.</p>
            </div>
            
            <div class="section">
                <h2>5. User-Generated Content</h2>
                <p>By posting content on BookWagon, you grant us a non-exclusive, transferable, sub-licensable, royalty-free, worldwide license to use, copy, modify, create derivative works based on, distribute, publicly display, publicly perform, and otherwise use that content in connection with the Service.</p>
                <p>You agree not to post content that:</p>
                <ul>
                    <li>Is illegal, harmful, threatening, abusive, harassing, defamatory, or invasive of another's privacy</li>
                    <li>Infringes on intellectual property rights of others</li>
                    <li>Contains malware, viruses, or other malicious code</li>
                    <li>Impersonates any person or entity</li>
                    <li>Constitutes unauthorized advertising or spam</li>
                </ul>
            </div>
            
            <div class="section">
                <h2>6. Limitation of Liability</h2>
                <p>In no event shall BookWagon, its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including loss of profits, data, use, goodwill, or other intangible losses, resulting from:</p>
                <ul>
                    <li>Your access to or use of or inability to access or use the Service</li>
                    <li>Any conduct or content of any third party on the Service</li>
                    <li>Any content obtained from the Service</li>
                    <li>Unauthorized access, use or alteration of your transmissions or content</li>
                </ul>
            </div>
            
            <div class="section">
                <h2>7. Termination</h2>
                <p>We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
                <p>Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you may simply discontinue using the Service or contact us to request account deletion.</p>
            </div>
            
            <div class="section">
                <h2>8. Changes to Terms</h2>
                <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will try to provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>
                <p>By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms. If you do not agree to the new terms, please stop using the Service.</p>
            </div>
            
            <div class="section">
                <h2>9. Governing Law</h2>
                <p>These Terms shall be governed and construed in accordance with the laws of the Philippines, without regard to its conflict of law provisions.</p>
                <p>Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights. If any provision of these Terms is held to be invalid or unenforceable by a court, the remaining provisions of these Terms will remain in effect.</p>
            </div>
            
            <div class="section">
                <h2>10. Contact Us</h2>
                <p>If you have any questions about these Terms, please contact us at support@bookwagon.ph.</p>
            </div>
            
            <div class="mt-4">
                <a href="signup.php" class="btn btn-back"><i class="fas fa-arrow-left me-2"></i>Back to Sign Up</a>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> BookWagon. All rights reserved.</p>
            <p>
                <a href="terms.php" class="text-decoration-none text-primary">Terms of Service</a> | 
                <a href="privacy.php" class="text-decoration-none text-primary">Privacy Policy</a>
            </p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 