<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hillcrest Suites API</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #008ea2;
            --primary-light: #00b4cc;
            --primary-dark: #006b7a;
            --success: #10b981;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-light: #9ca3af;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --border: #e5e7eb;
            --shadow: rgba(0, 0, 0, 0.1);
            --shadow-lg: rgba(0, 0, 0, 0.25);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 50%, #1e293b 100%);
            min-height: 100vh;
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(0, 180, 204, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 107, 122, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 20s infinite linear;
        }

        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            width: 60px;
            height: 60px;
            background: var(--primary-light);
            border-radius: 50%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 60%;
            right: 15%;
            width: 40px;
            height: 40px;
            background: var(--success);
            transform: rotate(45deg);
            animation-delay: -7s;
        }

        .shape:nth-child(3) {
            bottom: 30%;
            left: 20%;
            width: 80px;
            height: 80px;
            background: var(--primary);
            border-radius: 20px;
            animation-delay: -14s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(-60px) rotate(240deg); }
        }

        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .card {
            background: var(--bg-primary);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 
                0 20px 25px -5px var(--shadow-lg),
                0 10px 10px -5px var(--shadow);
            max-width: 700px;
            width: 100%;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light), var(--success));
            background-size: 300% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .card-header {
            text-align: center;
            padding: 3rem 2rem 2rem;
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }

        .logo {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 
                0 20px 40px rgba(0, 142, 162, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            position: relative;
            animation: logoFloat 6s ease-in-out infinite;
        }

        .logo::before {
            content: 'API';
            color: white;
            font-weight: 700;
            font-size: 1.75rem;
            letter-spacing: 3px;
        }

        .logo::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--primary), var(--primary-light), var(--success), var(--primary));
            background-size: 400% 400%;
            border-radius: 22px;
            z-index: -1;
            animation: gradientShift 4s ease infinite;
            opacity: 0.7;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: var(--success);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 500;
            border: 1px solid rgba(16, 185, 129, 0.2);
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }

        .title {
            color: var(--text-primary);
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            line-height: 1.1;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .card-content {
            padding: 0 2rem 3rem;
        }

        .description {
            color: var(--text-secondary);
            line-height: 1.7;
            font-size: 1.1rem;
            margin-bottom: 3rem;
            text-align: center;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .feature-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem 1.5rem;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 142, 162, 0.1), transparent);
            transition: left 0.5s;
        }

        .feature-card:hover::before {
            left: 100%;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 
                0 25px 50px -12px rgba(0, 142, 162, 0.25),
                0 0 0 1px rgba(0, 142, 162, 0.1);
            border-color: rgba(0, 142, 162, 0.3);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
            filter: grayscale(0.3);
            transition: filter 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            filter: grayscale(0);
        }

        .feature-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }

        .feature-description {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .cta-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 1.25rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 10px 30px rgba(0, 142, 162, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .cta-button:hover::before {
            left: 100%;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 20px 40px rgba(0, 142, 162, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.2);
        }

        .cta-icon {
            transition: transform 0.3s ease;
        }

        .cta-button:hover .cta-icon {
            transform: translate(2px, -2px);
        }

        .footer {
            margin-top: 2rem;
            padding: 2rem;
            background: var(--bg-secondary);
            border-top: 1px solid var(--border);
            text-align: center;
            border-radius: 0 0 24px 24px;
        }

        .footer-text {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .footer-subtext {
            color: var(--text-light);
            font-size: 0.8rem;
            opacity: 0.7;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .card-content {
                padding: 0 1.5rem 2rem;
            }

            .title {
                font-size: 2.25rem;
            }

            .subtitle {
                font-size: 1.1rem;
            }

            .description {
                font-size: 1rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .feature-card {
                padding: 1.5rem 1rem;
            }

            .cta-button {
                padding: 1rem 2rem;
                font-size: 1rem;
            }

            .logo {
                width: 80px;
                height: 80px;
            }

            .logo::before {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 1rem 0.5rem;
            }

            .title {
                font-size: 2rem;
            }

            .card {
                margin: 0 0.5rem;
            }
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus styles for better accessibility */
        .cta-button:focus {
            outline: 2px solid var(--primary-light);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="main-container">
        <div class="card">
            <div class="card-header">
                <div class="logo-container">
                    <div class="logo"></div>
                </div>
                
                <div class="status-badge">
                    <div class="status-dot"></div>
                    API Server Online
                </div>

                <h1 class="title">Hillcrest Suites API</h1>
                <p class="subtitle">Backend Server & API Gateway</p>
            </div>

            <div class="card-content">
                <p class="description">
                    Welcome to the Hillcrest Suites API server. This powerful backend provides secure endpoints for authentication, administrative functions, and guest services, handling all data operations and business logic for our premium booking platform.
                </p>

                <div class="features-grid">
                    <div class="feature-card">
                        <span class="feature-icon">🔐</span>
                        <h3 class="feature-title">Secure Authentication</h3>
                        <p class="feature-description">Enterprise-grade user login and registration endpoints with advanced security protocols</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">⚙️</span>
                        <h3 class="feature-title">Admin Management</h3>
                        <p class="feature-description">Comprehensive administrative API endpoints for system management and monitoring</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">👥</span>
                        <h3 class="feature-title">Guest Services</h3>
                        <p class="feature-description">Seamless public booking and inquiry endpoints for enhanced guest experience</p>
                    </div>
                </div>

                <div class="cta-container">
                    <a href="https://www.hillcrest-suites.pcds.edu.ph" class="cta-button" target="_blank" rel="noopener noreferrer">
                        <span>Explore Frontend Experience</span>
                        <svg class="cta-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="footer">
                <p class="footer-text">© 2025 Hillcrest Suites. All rights reserved.</p>
                <p class="footer-subtext">
                    This page is displayed when accessing the API server directly. 
                    For the complete booking experience, visit our frontend application.
                </p>
            </div>
        </div>
    </div>
</body>
</html>