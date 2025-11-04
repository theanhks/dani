<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>

    <!-- CSS core từ Quixlab -->
    <link href="{{ asset('admin/css/style.css') }}" rel="stylesheet">

    <!-- Icons -->
    <link href="{{ asset('admin/icons/themify-icons/css/themify-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/icons/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-form {
            background: #fff;
            padding: 40px 30px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 380px;
        }

        .login-form .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-form .logo img {
            height: 50px;
        }

        .login-form h3 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .form-control {
            height: 45px;
            font-size: 14px;
        }

        .btn-login {
            height: 45px;
            font-size: 15px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="login-form">
        <div class="logo">
            <img src="{{ asset('admin/images/logo-text.png') }}" alt="logo">
        </div>
        <h3>Admin Login</h3>
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="form-group mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required autofocus>
            </div>
            <div class="form-group mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="#">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-login">Login</button>
        </form>
    </div>

    <!-- JS core -->
    <script src="{{ asset('admin/plugins/common/common.min.js') }}"></script>
    <script src="{{ asset('admin/js/custom.min.js') }}"></script>
</body>

</html>