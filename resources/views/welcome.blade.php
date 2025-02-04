<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SNC PHARMA - Portal Administrativo</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-image: url('/images/background/login-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1000px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .logo {
            max-width: 200px;
            margin-bottom: 2rem;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 600;
        }

        .description {
            color: #34495e;
            margin-bottom: 2.5rem;
            line-height: 1.6;
            font-size: 1.1rem;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-primary {
            background: #3498db;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 2px solid #3498db;
            color: #3498db;
            background: transparent;
        }

        .btn-outline:hover {
            background: #3498db;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .auth-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="/images/logo/logo.png" alt="SNC PHARMA Logo" class="logo">
        
        <h1>Únete a Nuestra Misión de Mejorar la Salud Global</h1>
        
        <p class="description">
            En SNC PHARMA, trabajamos cada día para ofrecer soluciones farmacéuticas innovadoras. 
            Descubre cómo podemos ayudarte a mejorar la calidad de vida.
        </p>

        <div class="auth-buttons">
            <a href="{{ route('login') }}" class="btn btn-primary">Iniciar Sesión</a>
            <a href="{{ route('register') }}" class="btn btn-outline">Registrarse</a>
        </div>
    </div>
</body>
</html>
