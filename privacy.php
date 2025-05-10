<?php
// No need for database connection in this file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - BookWagon</title>
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
        
        .privacy-container {
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
        
        .table {
            margin-bottom: 30px;
        }
        
        .table th {
            background-color: var(--secondary-color);
            color: var(--text-dark);
            font-weight: 600;
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
    
    <!-- Privacy Policy Content -->
    <div class="container">
        <div class="privacy-container">
            <h1>Privacy Policy</h1>
            <p class="last-updated">Last Updated: <?php echo date("F j, Y"); ?></p>
            
            <div class="section">
                <p>At BookWagon, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our service. Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access the site.</p>
            </div>
            
            <div class="section">
                <h2>1. Information We Collect</h2>
                <p>We collect information that you provide directly to us when you:</p>
                <ul>
                    <li>Register for an account</li>
                    <li>Profile information (name, email, profile picture)</li>
                    <li>List books for sale or rent</li>
                    <li>Make purchases or rental transactions</li>
                    <li>Participate in forums or discussions</li>
                    <li>Contact customer support</li>
                </ul>
                
                <p>We may also collect certain information automatically when you visit our website, including:</p>
                <ul>
                    <li>IP address and device information</li>
                    <li>Browser type and version</li>
                    <li>Pages you view and how you interact with our service</li>
                    <li>Referring and exit pages</li>
                    <li>Date and time stamps</li>
                    <li>Cookies and similar tracking technologies</li>
                </ul>
            </div>
            
            <div class="section">
                <h2>2. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul>
                    <li>Provide, maintain, and improve our services</li>
                    <li>Create and manage your account</li>
                    <li>Process transactions and send related information</li>
                    <li>Send you technical notices, updates, security alerts, and support messages</li>
                    <li>Respond to your comments, questions, and customer service requests</li>
                    <li>Communicate with you about products, services, offers, and events</li>
                    <li>Monitor and analyze trends, usage, and activities in connection with our service</li>
                    <li>Detect, investigate, and prevent fraudulent transactions and other illegal activities</li>
                    <li>Personalize your experience and deliver content relevant to your interests</li>
                </ul>
            </div>
            
            <div class="section">
                <h2>3. Sharing of Information</h2>
                <p>We may share your personal information with:</p>
                <ul>
                    <li><strong>Other Users:</strong> When you list books or engage in transactions, certain information may be visible to other users as necessary to facilitate the transaction.</li>
                    <li><strong>Service Providers:</strong> We may share information with third-party vendors, consultants, and other service providers who need access to such information to carry out work on our behalf.</li>
                    <li><strong>In Response to Legal Process:</strong> We may disclose information in response to a subpoena, court order, or other governmental request.</li>
                    <li><strong>To Protect Rights:</strong> We may disclose information when we believe it's necessary to investigate, prevent, or take action regarding possible illegal activities, suspected fraud, situations involving potential threats to the safety of any person, or violations of our Terms of Service.</li>
                    <li><strong>Business Transfers:</strong> We may share or transfer information in connection with a merger, acquisition, reorganization, sale of assets, or bankruptcy.</li>
                </ul>
                
                <p>We do not sell, rent, or otherwise disclose your personal information to third parties for their marketing purposes without your consent.</p>
            </div>
            
            <div class="section">
                <h2>4. Data Retention</h2>
                <p>We retain personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. We will also retain and use information as necessary to comply with legal obligations, resolve disputes, and enforce our agreements.</p>
            </div>
            
            <div class="section">
                <h2>5. Security</h2>
                <p>We implement reasonable precautions and follow industry best practices to protect your personal information and ensure it is not inappropriately lost, misused, accessed, disclosed, altered, or destroyed. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p>
            </div>
            
            <div class="section">
                <h2>6. Cookies and Tracking Technologies</h2>
                <p>We use cookies and similar tracking technologies to track activity on our Service and hold certain information. Cookies are files with a small amount of data that may include an anonymous unique identifier. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our Service.</p>
                
                <p>Types of cookies we use:</p>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Essential Cookies</td>
                            <td>Necessary for the website to function properly, such as authentication and security</td>
                        </tr>
                        <tr>
                            <td>Preferences Cookies</td>
                            <td>Remember your preferences and settings</td>
                        </tr>
                        <tr>
                            <td>Analytics Cookies</td>
                            <td>Help us understand how visitors interact with our website</td>
                        </tr>
                        <tr>
                            <td>Marketing Cookies</td>
                            <td>Track visitors across websites to display relevant advertisements</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="section">
                <h2>7. Your Data Protection Rights</h2>
                <p>Depending on your location, you may have the following rights regarding your personal information:</p>
                <ul>
                    <li><strong>Access:</strong> You have the right to request copies of your personal information.</li>
                    <li><strong>Rectification:</strong> You have the right to request that we correct any information you believe is inaccurate or complete information you believe is incomplete.</li>
                    <li><strong>Erasure:</strong> You have the right to request that we erase your personal information, under certain conditions.</li>
                    <li><strong>Restriction of processing:</strong> You have the right to request that we restrict the processing of your personal information, under certain conditions.</li>
                    <li><strong>Object to processing:</strong> You have the right to object to our processing of your personal information, under certain conditions.</li>
                    <li><strong>Data portability:</strong> You have the right to request that we transfer the data we have collected to another organization, or directly to you, under certain conditions.</li>
                </ul>
                
                <p>If you make a request, we have one month to respond to you. If you would like to exercise any of these rights, please contact us at privacy@bookwagon.ph.</p>
            </div>
            
            <div class="section">
                <h2>8. Children's Privacy</h2>
                <p>Our Service is not directed to individuals under the age of 13. We do not knowingly collect personal information from children under 13. If you are a parent or guardian and you are aware that your child has provided us with personal information, please contact us. If we discover that a child under 13 has provided us with personal information, we will take steps to remove such information and terminate the child's account.</p>
            </div>
            
            <div class="section">
                <h2>9. Changes to This Privacy Policy</h2>
                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date. You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>
            </div>
            
            <div class="section">
                <h2>10. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us at privacy@bookwagon.ph or by mail at:</p>
                <p>BookWagon Philippines<br>123 Reading Lane<br>Makati City, Metro Manila<br>Philippines</p>
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