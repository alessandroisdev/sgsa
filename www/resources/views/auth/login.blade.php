<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - SGSA</title>
    <!-- Use Bootstrap from CDN just for the standalone login page to avoid Vite issues if not built -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        .btn-login {
            background-color: #1b1b18;
            color: white;
            font-weight: 600;
        }
        .btn-login:hover {
            background-color: #000;
            color: white;
        }
    </style>
</head>
<body>

    <div class="card login-card">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark">SGSA Admin</h2>
            <p class="text-muted">Acesse o painel de gerenciamento</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger p-2 mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li><small>{{ $error }}</small></li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-medium text-secondary">E-mail</label>
                <input type="email" name="email" class="form-control form-control-lg" value="{{ old('email') }}" required autofocus placeholder="admin@sgsa.com">
            </div>

            <div class="mb-4">
                <label class="form-label fw-medium text-secondary">Senha</label>
                <input type="password" name="password" class="form-control form-control-lg" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-login btn-lg w-100">Entrar</button>
        </form>
    </div>

</body>
</html>
