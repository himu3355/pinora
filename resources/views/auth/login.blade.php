<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to Your Account — Pinora</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #c9a96e;
            --primary-hover: #bda060;
            --dark: #121212;
            --dark-surface: #1e1e1e;
            --light: #f9f9f9;
            --text-muted: #888888;
            --border: #333333;
            --error: #ff4d4d;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--dark);
            color: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
            background-color: var(--dark-surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .auth-header p {
            font-size: 14px;
            color: var(--text-muted);
        }

        .alert-checkout {
            background-color: rgba(201, 169, 110, 0.15);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--light);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background-color: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--light);
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.25);
        }

        .remember-forgot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--text-muted);
        }

        .remember-me input {
            accent-color: var(--primary);
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .error-message {
            color: var(--error);
            font-size: 12px;
            margin-top: 6px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: var(--dark);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .social-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: var(--text-muted);
            font-size: 13px;
        }

        .social-divider::before, .social-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border);
        }

        .social-divider:not(:empty)::before {
            margin-right: .5em;
        }

        .social-divider:not(:empty)::after {
            margin-left: .5em;
        }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            background-color: transparent;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--light);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-google:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .btn-google svg {
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-header">
        <h1>PINORA</h1>
        <p>Login to your account to continue</p>
    </div>

    @if($redirect === 'checkout')
        <div class="alert-checkout">
            Please sign in or register to complete your purchase.
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="hidden" name="redirect" value="{{ $redirect }}">

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="john@example.com">
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="remember-forgot">
            <label class="remember-me">
                <input type="checkbox" name="remember">
                Remember Me
            </label>
            <a href="#" class="forgot-password">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-submit">Sign In</button>
    </form>

    <div class="social-divider">Or continue with</div>

    <a href="{{ route('auth.google') }}" style="text-decoration: none;">
        <button class="btn-google">
            <svg viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Google
        </button>
    </a>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('register', ['redirect' => $redirect]) }}">Create one</a>
    </div>
</div>

</body>
</html>
