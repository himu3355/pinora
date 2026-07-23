<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Application — Pinora</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #c9a96e;
            --primary-hover: #bda060;
            --dark: #121212;
            --dark-surface: #1e1e1e;
            --light: #f9f9f9;
            --text-muted: #999999;
            --border: #333333;
            --error: #ff4d4d;
            --success: #2ecc71;
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background-color: var(--dark-surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 45px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .header p {
            font-size: 15px;
            color: var(--text-muted);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 35px;
        }

        @media (max-width: 600px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .container {
                padding: 25px;
            }
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        @media (max-width: 600px) {
            .form-group.full-width {
                grid-column: span 1;
            }
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--light);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background-color: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--light);
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.2);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .file-upload-wrapper {
            position: relative;
            background-color: rgba(0, 0, 0, 0.15);
            border: 2px dashed var(--border);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .file-upload-wrapper:hover {
            border-color: var(--primary);
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-text {
            font-size: 14px;
            color: var(--text-muted);
        }

        .file-upload-text span {
            color: var(--primary);
            font-weight: 500;
        }

        .error-message {
            color: var(--error);
            font-size: 12px;
            margin-top: 6px;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background-color: var(--primary);
            color: var(--dark);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .success-box {
            text-align: center;
            padding: 20px;
        }

        .success-icon {
            font-size: 60px;
            color: var(--success);
            margin-bottom: 20px;
        }

        .success-box h2 {
            font-size: 26px;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .success-box p {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .btn-home {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--primary);
            color: var(--dark);
            text-decoration: none;
            font-weight: 600;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .btn-home:hover {
            background-color: var(--primary-hover);
        }
    </style>
</head>
<body>

<div class="container">
    @if(session('success'))
        <div class="success-box">
            <div class="success-icon">✓</div>
            <h2>Application Submitted!</h2>
            <p>
                Thank you for applying to sell on Pinora. Your vendor registration request is now under review. 
                Our compliance team will review your submitted GST details, PAN card, and bank configurations. 
                You will receive an email notification once your account has been approved and activated.
            </p>
            <a href="/" class="btn-home">Return to Homepage</a>
        </div>
    @else
        <div class="header">
            <h1>PINORA PARTNERS</h1>
            <p>Onboard your store and showcase your designs to premium buyers</p>
        </div>

        <form method="POST" action="{{ route('vendor.apply.submit') }}" enctype="multipart/form-data">
            @csrf

            <!-- Section 1: Owner Details -->
            <div class="section-title">Owner Details</div>
            <div class="grid">
                <div class="form-group">
                    <label for="owner_name">Owner Full Name</label>
                    <input type="text" id="owner_name" name="owner_name" class="form-control" value="{{ old('owner_name') }}" required placeholder="e.g. Rajesh Kumar">
                    @error('owner_name') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="owner_email">Login Email Address</label>
                    <input type="email" id="owner_email" name="owner_email" class="form-control" value="{{ old('owner_email') }}" required placeholder="rajesh@store.com">
                    @error('owner_email') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="owner_phone">Contact Mobile No.</label>
                    <input type="tel" id="owner_phone" name="owner_phone" class="form-control" value="{{ old('owner_phone') }}" required placeholder="e.g. +91 98765 43210">
                    @error('owner_phone') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="password">Create Password</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                    @error('password') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="••••••••">
                </div>
            </div>

            <!-- Section 2: Store Information -->
            <div class="section-title">Store Information</div>
            <div class="grid">
                <div class="form-group">
                    <label for="store_name">Store / Brand Name</label>
                    <input type="text" id="store_name" name="store_name" class="form-control" value="{{ old('store_name') }}" required placeholder="e.g. Raj Mahal Jewellers">
                    @error('store_name') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="store_email">Store Contact Email</label>
                    <input type="email" id="store_email" name="store_email" class="form-control" value="{{ old('store_email') }}" placeholder="info@rajmahal.com">
                    @error('store_email') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="store_phone">Store Contact Phone</label>
                    <input type="tel" id="store_phone" name="store_phone" class="form-control" value="{{ old('store_phone') }}" placeholder="022-2432890">
                    @error('store_phone') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group full-width">
                    <label for="store_description">Store Description</label>
                    <textarea id="store_description" name="store_description" class="form-control" placeholder="Describe your heritage, specialities, or craft details...">{{ old('store_description') }}</textarea>
                    @error('store_description') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group full-width">
                    <label for="address">Registered Business Address</label>
                    <textarea id="address" name="address" class="form-control" required placeholder="Full registered workshop or showroom address...">{{ old('address') }}</textarea>
                    @error('address') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" value="{{ old('city') }}" required placeholder="Mumbai">
                    @error('city') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" class="form-control" value="{{ old('state') }}" required placeholder="Maharashtra">
                    @error('state') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="pincode">Pincode</label>
                    <input type="text" id="pincode" name="pincode" class="form-control" value="{{ old('pincode') }}" required placeholder="400001">
                    @error('pincode') <div class="error-message">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Section 3: Tax & Business Verification -->
            <div class="section-title">Tax & Registration Details</div>
            <div class="grid">
                <div class="form-group">
                    <label for="gst_number">GSTIN Number</label>
                    <input type="text" id="gst_number" name="gst_number" class="form-control" value="{{ old('gst_number') }}" required placeholder="27AAAAA0000A1Z5">
                    @error('gst_number') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="pan_number">PAN Number</label>
                    <input type="text" id="pan_number" name="pan_number" class="form-control" value="{{ old('pan_number') }}" required placeholder="ABCDE1234F">
                    @error('pan_number') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>GST Certificate (PDF / Image)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="gst_certificate" required accept=".pdf,image/*" onchange="updateFileName(this)">
                        <div class="file-upload-text">Drag file here or <span>browse</span> to upload GSTIN PDF</div>
                    </div>
                    @error('gst_certificate') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>PAN Card (PDF / Image)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="pan_card" required accept=".pdf,image/*" onchange="updateFileName(this)">
                        <div class="file-upload-text">Drag file here or <span>browse</span> to upload PAN PDF</div>
                    </div>
                    @error('pan_card') <div class="error-message">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Section 4: Settlement Bank Info -->
            <div class="section-title">Settlement Bank Account</div>
            <div class="grid">
                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" class="form-control" value="{{ old('bank_name') }}" required placeholder="HDFC Bank">
                    @error('bank_name') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="bank_account_name">Account Holder Name</label>
                    <input type="text" id="bank_account_name" name="bank_account_name" class="form-control" value="{{ old('bank_account_name') }}" required placeholder="Raj Mahal Jewellers Ltd.">
                    @error('bank_account_name') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="bank_account_number">Bank Account Number</label>
                    <input type="password" id="bank_account_number" name="bank_account_number" class="form-control" required placeholder="Account Number">
                    @error('bank_account_number') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="bank_ifsc_code">Bank IFSC Code</label>
                    <input type="text" id="bank_ifsc_code" name="bank_ifsc_code" class="form-control" value="{{ old('bank_ifsc_code') }}" required placeholder="HDFC0001234">
                    @error('bank_ifsc_code') <div class="error-message">{{ $message }}</div> @enderror
                </div>
            </div>

            <button type="submit" class="btn-submit">Submit Application</button>
        </form>
    @endif
</div>

<script>
    function updateFileName(input) {
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            const textElement = input.nextElementSibling;
            textElement.innerHTML = `Selected File: <span style="color:var(--success); font-weight:600;">${fileName}</span>`;
        }
    }
</script>

</body>
</html>
