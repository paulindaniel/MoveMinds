<?php
// Inicia a sessão no topo do ficheiro
session_start();

// --- LIGAÇÃO À BASE DE DADOS ---
$servidor = "localhost";
$utilizador_bd = "root";
$password_bd = "";
$basedados = "moveminds";

$ligacao = new mysqli($servidor, $utilizador_bd, $password_bd, $basedados);

// Variável para guardar mensagens de erro
$error_message = '';

// --- VERIFICA SE O FORMULÁRIO FOI SUBMETIDO ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if ($ligacao->connect_error) {
        $error_message = 'Erro de ligação à base de dados.';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error_message = 'Por favor, preencha todos os campos.';
        } else {
            // Procura o utilizador na tabela 'users'
            $stmt = $ligacao->prepare("SELECT id, nickname, password_hash FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 1) {
                $utilizador = $resultado->fetch_assoc();
                
                // Verifica a palavra-passe encriptada
                if (password_verify($password, $utilizador['password_hash'])) {
                    // Palavra-passe correta, guardar dados na sessão
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $utilizador['id'];
                    $_SESSION['nickname'] = $utilizador['nickname'];
                    
                    // Redireciona para a página inicial
                    header("Location: home.html");
                    exit();
                } else {
                    $error_message = 'E-mail ou palavra-passe incorretos.';
                }
            } else {
                $error_message = 'E-mail ou palavra-passe incorretos.';
            }
            $stmt->close();
        }
    }
    $ligacao->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move Minds - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --body-bg: #f0f2f5;
            --app-bg: #ffffff;
            --text-primary: #1c1e21;
            --text-secondary: #65676b;
            --border-color: #e0e0e0;
            --btn-primary-bg: #007bff;
            --error-bg: #f8d7da;
            --error-color: #721c24;
            --error-border: #f5c6cb;
            --special-icon-bg: #343a40;
            --special-icon-color: #ffffff;
        }
        .dark-theme {
            --body-bg: #1e1e1e;
            --app-bg: #2c2c2c;
            --text-primary: #f0f0f0;
            --text-secondary: #b0b0b0;
            --border-color: #3f3f3f;
            --special-icon-bg: #f0f0f0;
            --special-icon-color: #1e1e1e;
            --error-bg: #492226;
            --error-color: #f8d7da;
            --error-border: #721c24;
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            background-color: var(--body-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            transition: background-color 0.3s ease;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-sizing: border-box;
        }
        .login-card {
            background-color: var(--app-bg);
            color: var(--text-primary);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        .card-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .card-header .logo {
            width: 60px;
            height: 60px;
            margin-bottom: 10px;
        }
        .card-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--body-bg);
            color: var(--text-primary);
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color .2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--btn-primary-bg);
        }
        .login-btn {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            background-color: var(--btn-primary-bg);
            color: white;
            transition: opacity .2s;
        }
        .login-btn:hover { opacity: 0.9; }
        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .footer-link a {
            color: var(--btn-primary-bg);
            text-decoration: none;
            font-weight: 600;
        }
        .error-message {
            background-color: var(--error-bg);
            color: var(--error-color);
            border: 1px solid var(--error-border);
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .theme-toggle-fab { 
            position: fixed; 
            bottom: 25px; 
            right: 25px; 
            width: 50px; 
            height: 50px; 
            background-color: var(--special-icon-bg); 
            color: var(--special-icon-color); 
            border-radius: 50%; 
            display: grid; 
            place-items: center; 
            font-size: 22px; 
            cursor: pointer; 
            z-index: 999; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s ease; 
        }
        .theme-toggle-fab:hover { 
            transform: scale(1.1); 
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <img src="logo.png" alt="Logo Move Minds" class="logo">
                <h2>Bem-vindo de volta!</h2>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form id="login-form" method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Palavra-passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="login-btn">Entrar</button>
            </form>
            <div class="footer-link">
                <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
            </div>
        </div>
    </div>
    
    <div class="theme-toggle-fab" id="theme-toggle">
        <i class="fas fa-moon"></i>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            const themeIcon = themeToggle.querySelector('i');
            
            // --- Lógica do Tema ---
            const applyTheme = (theme) => {
                if (theme === 'dark') {
                    body.classList.add('dark-theme');
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                } else {
                    body.classList.remove('dark-theme');
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                }
            };
            
            const savedTheme = localStorage.getItem('theme') || 'light';
            applyTheme(savedTheme);

            themeToggle.addEventListener('click', () => {
                const isDark = body.classList.contains('dark-theme');
                if (isDark) {
                    applyTheme('light');
                    localStorage.setItem('theme', 'light');
                } else {
                    applyTheme('dark');
                    localStorage.setItem('theme', 'dark');
                }
            });
        });
    </script>
</body>
</html>
