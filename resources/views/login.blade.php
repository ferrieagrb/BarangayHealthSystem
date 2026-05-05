<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
                                <!--<div style="border: 3px solid black;">
                                    <h2>Register</h2>
                                    <form action="/register" method="POST">
                                        @csrf
                                        <input type="text" placeholder="name" name="name">
                                        <input type="text" placeholder="email" name="email">
                                        <input type="password" placeholder="password" name="password">
                                        <button> Register </button>
                                    </form>
                                </div>-->
    <div class="login-container">
        <div class="left-side">
            <div class="overlay"></div>
        </div>
        <div class="right-side">
            <a href="{{ route('landing') }}" class="admin-login">← Home Page</a>
            <h2>Welcome to <br><strong>Barangay Amuyong<br>Health Information System</strong></h2>
            <p>Enter your credentials to access your account</p>

            <form action="{{ route('login.submit') }}" method="POST"> 
                @csrf  
               @if (session('error'))
                    <div style="
                        background:#ffdddd;
                        color:#a10000;
                        padding:12px;
                        margin-bottom:15px;
                        border-radius:8px;
                        border:1px solid #ff5c5c;
                    ">
                        {{ session('error') }}
                    </div>
                @endif
                
                <label for="Username">Username</label>
                <input type="text" placeholder="Enter your user name" name="Username">
                <div class="password-label">
                    <label for="password">Password</label>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>
                
                <input type="password" placeholder="Enter your password" name="Password">
                <button> Login </button>
            </form>
            
                
                         
        </div>
        
    </div> 
     
</body>
</html>