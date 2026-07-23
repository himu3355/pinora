<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Partner — Pinora Vendor Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f43f5e; /* Rose primary accent for vendors */
            --primary-hover: #e11d48;
            --dark: #0f172a;
            --dark-surface: #1e293b;
            --light: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
            --error: #ef4444;
            --success: #10b981;
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
            padding: 40px 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 680px; /* Wider to accommodate side-by-side or well-spaced fields */
            background-color: var(--dark-surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .auth-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .auth-header p {
            font-size: 15px;
            color: var(--text-muted);
        }

        .form-section-title {
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary);
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(244, 63, 94, 0.2);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
            color: var(--light);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background-color: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--light);
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.25);
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
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            margin-top: 20px;
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
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-header">
        <h1>PINORA PARTNERS</h1>
        <p>Start selling your fine jewelry and metals. Register your store today.</p>
    </div>

    @if (session('success'))
        <div style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: var(--success); padding: 16px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('vendor.apply.submit') }}">
        @csrf

        <!-- Section 1: User Account Details -->
        <div class="form-section-title">Owner Details</div>
        <div class="form-grid">
            <div class="form-group">
                <label for="owner_name">Owner's Full Name</label>
                <input type="text" id="owner_name" name="owner_name" class="form-control" value="{{ old('owner_name') }}" required autofocus placeholder="John Doe">
                @error('owner_name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Account Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="john@example.com">
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

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="••••••••">
            </div>
        </div>

        <!-- Section 2: Store Details -->
        <div class="form-section-title">Store Details</div>
        <div class="form-grid">
            <div class="form-group">
                <label for="store_name">Store / Brand Name</label>
                <input type="text" id="store_name" name="store_name" class="form-control" value="{{ old('store_name') }}" required placeholder="Golden Wonders">
                @error('store_name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Business Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" required placeholder="+91 98765 43210">
                @error('phone')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="gst_number">GSTIN (Optional)</label>
                <input type="text" id="gst_number" name="gst_number" class="form-control" value="{{ old('gst_number') }}" placeholder="22AAAAA0000A1Z5">
                @error('gst_number')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="pan_number">PAN Number (Optional)</label>
                <input type="text" id="pan_number" name="pan_number" class="form-control" value="{{ old('pan_number') }}" placeholder="ABCDE1234F">
                @error('pan_number')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="description">About Your Business</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Briefly describe the kinds of jewelry or metals you sell...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn-submit">Register as Partner</button>
    </form>

    <div class="auth-footer">
        Already a partner? <a href="/vendor/login">Login to Vendor Panel</a>
    </div>
</div>

</body>
</html>
