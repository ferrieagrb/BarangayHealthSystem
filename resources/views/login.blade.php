
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Amuyong - Health Information System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for modern icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <div class="login-container">
        <!-- Left Banner Side -->
        <div class="left-side">
            <div class="overlay-content">
                <div class="badge">
                    <i class="fa-solid fa-shield-halved"></i> Official Portal
                </div>
                <h1>Barangay Amuyong<br><span>Health Information System</span></h1>
                <p>Streamlining community healthcare services, patient records, and medical inventories securely.</p>
            </div>
        </div>

        <!-- Right Form Side -->
        <div class="right-side">
            <div class="form-wrapper">
                <div class="top-nav">
                    <a href="{{ route('landing') }}" class="back-home">
                        <i class="fa-solid fa-arrow-left"></i> Home Page
                    </a>
                </div>

                <div class="welcome-header">
                    <h2>Welcome Back</h2>
                    <p>Please enter your credentials to access your dashboard.</p>
                </div>

                <form action="{{ route('login.submit') }}" method="POST"> 
                    @csrf  
                    
                    @if (session('error'))
                        <div class="alert-error">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif
                    
                    <div class="input-group">
                        <label for="Username">Username or Email</label>
                        <div class="input-field-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="Username" placeholder="Enter your username" name="Username" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="password-label">
                            <label for="Password">Password</label>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>
                        <div class="input-field-wrapper">
                            <i class="fa-lock fa-solid"></i>
                            <input type="password" id="Password" placeholder="Enter your password" name="Password" required>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Sign In</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="portal-footer">
                    <p>&copy; 2026 Barangay Amuyong. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div> 

</body>
</html>