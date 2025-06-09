<?php
session_start();

$message = '';
$message_type = ''; // 'success' ou 'error'

// --- VERIFICA SE O FORMULÁRIO FOI SUBMETIDO ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- LIGAÇÃO À BASE DE DADOS ---
    $servidor = "localhost";
    $utilizador_bd = "root";
    $password_bd = "";
    $basedados = "moveminds";

    $ligacao = new mysqli($servidor, $utilizador_bd, $password_bd, $basedados);

    if ($ligacao->connect_error) {
        $message = 'Erro de ligação à base de dados.';
        $message_type = 'error';
    } else {
        $nickname = $_POST['nickname'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm-password'] ?? '';

        // --- VALIDAÇÃO ---
        if (empty($nickname) || empty($email) || empty($password)) {
            $message = 'Por favor, preencha todos os campos.';
            $message_type = 'error';
        } elseif ($password !== $confirm_password) {
            $message = 'As palavras-passe não coincidem.';
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Formato de e-mail inválido.';
            $message_type = 'error';
        } else {
            // VERIFICAR SE O E-MAIL JÁ EXISTE
            $stmt = $ligacao->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = 'Este e-mail já está em uso.';
                $message_type = 'error';
            } else {
                // TUDO CERTO, INSERIR NOVO UTILIZADOR
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt->close(); // Fechar a declaração anterior

                $stmt = $ligacao->prepare("INSERT INTO users (nickname, email, password_hash) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nickname, $email, $password_hash);

                if ($stmt->execute()) {
                    $message = 'Cadastro realizado com sucesso! Pode fazer login.';
                    $message_type = 'success';
                } else {
                    $message = 'Erro ao realizar o cadastro.';
                    $message_type = 'error';
                }
            }
            $stmt->close();
        }
        $ligacao->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move Minds - Cadastro</title>
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
            --success-bg: #d4edda;
            --success-color: #155724;
            --success-border: #c3e6cb;
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
            --success-bg: #1c3d23;
            --success-color: #d4edda;
            --success-border: #155724;
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
        .register-btn {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            background-color: #1c1e21;
            color: white;
            transition: opacity .2s;
        }
        .register-btn:hover { opacity: 0.9; }
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
        .message-box {
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .message-box.error {
            background-color: var(--error-bg);
            color: var(--error-color);
            border: 1px solid var(--error-border);
        }
        .message-box.success {
            background-color: var(--success-bg);
            color: var(--success-color);
            border: 1px solid var(--success-border);
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
                <h2>Crie a sua conta</h2>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message-box <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form id="register-form" method="POST" action="cadastro.php">
                <div class="form-group">
                    <label for="nickname">Nome de Utilizador</label>
                    <input type="text" id="nickname" name="nickname" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Palavra-passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirmar Palavra-passe</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                </div>
                <button type="submit" class="register-btn">Cadastrar</button>
            </form>
            <div class="footer-link">
                <p>Já tem uma conta? <a href="login.php">Entre</a></p>
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
