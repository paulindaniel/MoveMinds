<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// --- LÓGICA DE BACKEND ---

// 1. VERIFICAR SE O UTILIZADOR ESTÁ LOGADO
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Utilizador não autenticado']);
    } else {
        header("Location: login.php");
    }
    exit;
}

// --- LIGAÇÃO À BASE DE DADOS ---
$servidor = "localhost";
$utilizador_bd = "root";
$password_bd = "";
$basedados = "moveminds";
$ligacao = new mysqli($servidor, $utilizador_bd, $password_bd, $basedados);

if ($ligacao->connect_error) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erro de ligação']);
    } else {
        die("Ligação falhou: " . $ligacao->connect_error);
    }
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. PROCESSAR PEDIDOS DE ATUALIZAÇÃO (VIA JAVASCRIPT/FETCH)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $setting = $data['setting'] ?? null;
    $value = isset($data['value']) ? ($data['value'] ? 1 : 0) : null;

    $allowed_columns = ['google_fit_connected', 'apple_health_connected', 'push_notifications_enabled', 'email_promotions_enabled'];

    if ($setting && in_array($setting, $allowed_columns) && !is_null($value)) {
        $stmt = $ligacao->prepare("UPDATE users SET `$setting` = ? WHERE id = ?");
        $stmt->bind_param("ii", $value, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Falha ao salvar a configuração.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Configuração inválida.']);
    }
    $ligacao->close();
    exit; // Termina o script aqui para pedidos POST
}

// 3. IR BUSCAR AS CONFIGURAÇÕES ATUAIS PARA MOSTRAR NA PÁGINA
$stmt = $ligacao->prepare("SELECT google_fit_connected, apple_health_connected, push_notifications_enabled, email_promotions_enabled FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();
$settings = $resultado->fetch_assoc();
$stmt->close();
$ligacao->close();

// Se o utilizador não for encontrado na base de dados (por exemplo, foi apagado), termina a sessão
if (!$settings) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Converte os valores para booleanos para usar no HTML
$settings['google_fit_connected'] = !empty($settings['google_fit_connected']);
$settings['apple_health_connected'] = !empty($settings['apple_health_connected']);
$settings['push_notifications_enabled'] = !empty($settings['push_notifications_enabled']);
$settings['email_promotions_enabled'] = !empty($settings['email_promotions_enabled']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move Minds - Configurações</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Estilos Globais e Variáveis de Tema */
        :root {
            /* Tema Claro (Padrão) */
            --body-bg: #f0f2f5;
            --app-bg: #ffffff;
            --text-primary: #1c1e21;
            --text-secondary: #65676b;
            --border-color: #e0e0e0;
            --header-icon-color: #606770;
            --search-bg: #f0f2f5;
            --card-bg: #f7f7f7;
            --card-border: #ddd;
            --btn-dark-bg: #343a40;
            --btn-primary-bg: #007bff;
            --footer-bg: #ffffff;
            --footer-icon-inactive: #65676b;
            --footer-icon-active: #007bff;
            --special-icon-bg: #343a40;
            --special-icon-color: #ffffff;
            --special-icon-active-bg: #007bff;
            --menu-bg: #ffffff;
            --overlay-bg: rgba(0, 0, 0, 0.5);
            --notification-bg: #28a745;
            --notification-color: white;
            --check-icon-color: #28a745;
        }

        .dark-theme {
            /* Tema Escuro */
            --body-bg: #1e1e1e;
            --app-bg: #2c2c2c;
            --text-primary: #f0f0f0;
            --text-secondary: #b0b0b0;
            --border-color: #3f3f3f;
            --header-icon-color: #f0f0f0;
            --search-bg: #3f3f3f;
            --card-bg: #3a3a3a;
            --card-border: #4a4a4a;
            --btn-primary-bg: #f0f0f0;
            --footer-bg: #1e1e1e;
            --footer-icon-active: #ffffff; 
            --special-icon-bg: #f0f0f0;
            --special-icon-color: #1e1e1e;
            --special-icon-active-bg: #ffffff; 
            --menu-bg: #2c2c2c;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            background-color: var(--body-bg);
            display: flex;
            justify-content: center;
            min-height: 100vh;
            transition: background-color 0.3s ease;
        }

        .main-container {
            width: 100%;
            max-width: 1200px;
            background-color: var(--app-bg);
            color: var(--text-primary);
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            position: relative;
            padding-bottom: 75px;
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow-x: hidden;
        }

        /* Header */
        .header { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; background-color: var(--app-bg); border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 100; transition: background-color 0.3s ease, border-color 0.3s ease; }
        .header .menu-toggle { font-size: 22px; color: var(--header-icon-color); cursor: pointer; }
        .header .logo { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .header .search-bar { flex-grow: 1; max-width: 450px; display: flex; align-items: center; background-color: var(--search-bg); border-radius: 20px; padding: 8px 12px; margin: 0 15px; transition: background-color 0.3s ease; }
        .search-bar i { color: var(--text-secondary); margin-right: 8px; font-size: 0.9em; }
        .search-bar input { flex-grow: 1; background: none; border: none; color: var(--text-primary); font-size: 14px; outline: none; }
        .search-bar input::placeholder { color: var(--text-secondary); }

        /* Menu Lateral */
        .side-menu { position: fixed; top: 0; left: 0; width: 280px; height: 100%; background-color: var(--menu-bg); z-index: 1002; transform: translateX(-100%); transition: transform 0.3s ease-in-out; display: flex; flex-direction: column; padding: 20px; box-shadow: 4px 0px 15px rgba(0,0,0,0.1); }
        .side-menu.open { transform: translateX(0); }
        .menu-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .menu-header .logo-menu { font-weight: bold; font-size: 20px; color: var(--text-primary); }
        .close-menu-btn { font-size: 28px; cursor: pointer; color: var(--text-secondary); }
        .side-menu nav ul { list-style: none; padding: 0; margin: 0; }
        .side-menu nav a { display: block; padding: 15px 10px; color: var(--text-primary); text-decoration: none; font-size: 18px; border-radius: 8px; transition: background-color 0.2s ease; position: relative;}
        .side-menu nav a:hover { background-color: var(--search-bg); }
        .side-menu nav a i { margin-right: 15px; width: 24px; text-align: center; color: var(--text-secondary); }
        
        /* Overlay */
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: var(--overlay-bg); z-index: 1001; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease; }
        .overlay.visible { opacity: 1; visibility: visible; }
        
        /* Botão de Tema Flutuante */
        .theme-toggle-fab { position: fixed; bottom: 95px; right: 25px; width: 50px; height: 50px; background-color: var(--special-icon-bg); color: var(--special-icon-color); border-radius: 50%; display: grid; place-items: center; font-size: 22px; cursor: pointer; z-index: 999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s ease; }
        .theme-toggle-fab:hover { transform: scale(1.1); }

        /* Estilos da Página de Configurações */
        .page-content { padding: 20px; }
        .section-title { text-align: left; font-size: 18px; color: var(--text-primary); margin: 0 0 15px 0; font-weight: 600; }
        
        .settings-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 10px 25px;
            max-width: 800px;
            margin: 0 auto 25px auto;
            border: 1px solid var(--border-color);
        }
        
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            color: var(--text-primary);
        }
        .setting-item:last-child {
            border-bottom: none;
        }
        .setting-item-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .setting-item-content i {
            font-size: 20px;
            color: var(--text-secondary);
            width: 25px;
            text-align: center;
        }
        .setting-item-text .label {
            font-size: 16px;
            font-weight: 500;
        }
        .setting-item-text .description {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        /* Toggle Switch */
        .toggle-switch { position: relative; display: inline-block; width: 50px; height: 28px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .4s; border-radius: 28px; }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--btn-primary-bg); }
        .dark-theme input:checked + .slider { background-color: var(--check-icon-color); }
        input:checked + .slider:before { transform: translateX(22px); }

        /* Footer */
        .footer-nav { 
            position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 1200px; 
            display: flex; justify-content: space-around; align-items: flex-end; background-color: var(--footer-bg); 
            padding: 8px 0; border-top: 1px solid var(--border-color); z-index: 998; transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .nav-item { display: flex; flex-direction: column; align-items: center; text-decoration: none; color: var(--footer-icon-inactive); font-size: 10px; padding: 5px; flex: 1; text-align: center; transition: color 0.3s ease; }
        .nav-item i { font-size: 20px; margin-bottom: 4px; }
        .nav-item.active { color: var(--footer-icon-active); }
        .nav-item:hover:not(.active) { color: var(--text-primary); }
        .nav-item.special-item { position: relative; }
        .nav-item.special-item .icon-wrapper { background-color: var(--special-icon-bg); border-radius: 50%; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; margin-bottom: 2px; margin-top: -25px; border: 4px solid var(--footer-bg); box-shadow: 0 -3px 6px rgba(0,0,0,0.1); transition: background-color 0.3s ease, border-color 0.3s ease; position: relative; }
        .nav-item.special-item .icon-wrapper i { font-size: 24px; color: var(--special-icon-color); margin-bottom: 0; }
        .nav-item.special-item.active .icon-wrapper { background-color: var(--special-icon-active-bg); }
        .nav-item.special-item.active span { color: var(--footer-icon-active); }
        
        /* Notificação */
        .notification {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translate(-50%, 150px);
            background-color: var(--notification-bg);
            color: var(--notification-color);
            padding: 12px 25px;
            border-radius: 8px;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .notification.show {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, 0);
        }

    </style>
</head>
<body>
    <div id="side-menu" class="side-menu">
        <div class="menu-header"> <span class="logo-menu">Move Minds</span> <span id="close-menu-btn" class="close-menu-btn">&times;</span> </div>
        <nav>
            <ul>
                <li><a href="home.html"><i class="fas fa-home"></i>Início</a></li>
                <li><a href="shop.html"><i class="fas fa-rocket"></i>Planos</a></li>
                <li><a href="shop.html"><i class="fas fa-shopping-bag"></i>Produtos</a></li>
                <li><a href="progresso.html"><i class="fas fa-chart-line"></i>Progresso</a></li>
                <li><a href="carrinho.php"><i class="fas fa-shopping-cart"></i>Carrinho</a></li>
                <li><a href="perfil.php"><i class="fas fa-user-circle"></i>Perfil</a></li>
                <li><a href="config.php"><i class="fas fa-cog"></i>Configurações</a></li>
            </ul>
        </nav>
    </div>
    <div id="overlay" class="overlay"></div>

    <div class="main-container" id="main-container">
        <header class="header">
            <i class="fas fa-bars menu-toggle" id="menu-toggle"></i>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar">
            </div>
            <img src="assets/logo.png" alt="Logo MN" class="logo">
        </header>
        
        <div class="theme-toggle-fab" id="theme-toggle">
             <i class="fas fa-moon"></i>
        </div>
        
        <div class="page-content">
            <h2 class="section-title" style="text-align:center;">Configurações</h2>
            
            <h3 class="section-title">Contas Conectadas</h3>
            <div class="settings-card">
                <div class="setting-item">
                    <div class="setting-item-content">
                        <i class="fab fa-google" style="color: #DB4437;"></i>
                        <div class="setting-item-text">
                            <span class="label">Google Fit / Health Connect</span>
                            <span class="description">Sincronize seus treinos e dados de saúde.</span>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="google_fit_connected" name="google_fit_connected" <?php echo $settings['google_fit_connected'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="setting-item">
                    <div class="setting-item-content">
                        <i class="fab fa-apple" style="color: var(--text-primary);"></i>
                        <div class="setting-item-text">
                            <span class="label">Apple Health</span>
                            <span class="description">Importe seus dados do Apple Health.</span>
                        </div>
                    </div>
                     <label class="toggle-switch">
                        <input type="checkbox" id="apple_health_connected" name="apple_health_connected" <?php echo $settings['apple_health_connected'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <h3 class="section-title">Notificações</h3>
            <div class="settings-card">
                <div class="setting-item">
                    <div class="setting-item-content">
                        <i class="fas fa-bell"></i>
                        <div class="setting-item-text">
                            <span class="label">Notificações Push</span>
                            <span class="description">Receba lembretes e atualizações.</span>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="push_notifications_enabled" name="push_notifications_enabled" <?php echo $settings['push_notifications_enabled'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                 <div class="setting-item">
                    <div class="setting-item-content">
                        <i class="fas fa-envelope"></i>
                        <div class="setting-item-text">
                            <span class="label">Promoções por E-mail</span>
                            <span class="description">Novidades e ofertas exclusivas.</span>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="email_promotions_enabled" name="email_promotions_enabled" <?php echo $settings['email_promotions_enabled'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <h3 class="section-title">Conta</h3>
            <div class="settings-card">
                 <a href="perfil.php" class="setting-item">
                    <div class="setting-item-content">
                        <i class="fas fa-key"></i>
                        <div class="setting-item-text">
                            <span class="label">Alterar Palavra-passe</span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                 <a href="#" class="setting-item">
                    <div class="setting-item-content">
                        <i class="fas fa-file-contract"></i>
                        <div class="setting-item-text">
                            <span class="label">Termos de Serviço</span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                 <a href="#" class="setting-item">
                    <div class="setting-item-content">
                        <i class="fas fa-question-circle"></i>
                        <div class="setting-item-text">
                            <span class="label">Ajuda & Suporte</span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        
        <footer class="footer-nav"> 
            <a href="home.html" class="nav-item" data-target="inicio"> <i class="fas fa-home"></i> <span>Início</span> </a> 
            <a href="progresso.html" class="nav-item" data-target="progresso"> <i class="fas fa-chart-line"></i> <span>Progresso</span> </a> 
            <a href="shop.html" class="nav-item" data-target="loja"> <i class="fas fa-shopping-bag"></i> <span>Loja</span> </a> 
            <a href="config.php" class="nav-item special-item active" data-target="configuracoes">
                <div class="icon-wrapper"><i class="fas fa-cog"></i></div>
                <span>Configurações</span> 
            </a> 
            <a href="perfil.php" class="nav-item" data-target="perfil"> <i class="fas fa-user-circle"></i> <span>Perfil</span> </a> 
        </footer>
    </div>
    
    <div id="notification" class="notification"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            const themeIcon = themeToggle.querySelector('i');
            
            const menuToggle = document.getElementById('menu-toggle');
            const sideMenu = document.getElementById('side-menu');
            const closeMenuBtn = document.getElementById('close-menu-btn');
            const overlay = document.getElementById('overlay');
            const notification = document.getElementById('notification');

            // --- Lógica do Menu Lateral ---
            function openMenu() { sideMenu.classList.add('open'); overlay.classList.add('visible'); }
            function closeMenu() { sideMenu.classList.remove('open'); overlay.classList.remove('visible'); }
            menuToggle.addEventListener('click', openMenu);
            closeMenuBtn.addEventListener('click', closeMenu);
            overlay.addEventListener('click', closeMenu);

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
            
            // --- Lógica de Notificação ---
            function showNotification(message, isSuccess = true) {
                notification.textContent = message;
                notification.style.backgroundColor = isSuccess ? 'var(--notification-bg)' : '#dc3545';
                notification.classList.add('show');
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            }

            // --- Lógica da Página de Configurações ---
            const toggles = document.querySelectorAll('.toggle-switch input');

            toggles.forEach(toggle => {
                toggle.addEventListener('change', (e) => {
                    const settingName = e.target.name;
                    const settingValue = e.target.checked;
                    
                    saveSetting(settingName, settingValue);
                });
            });

            function saveSetting(setting, value) {
                // Usamos o fetch para enviar a alteração para o próprio ficheiro PHP
                fetch('config.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest' // Identifica como um pedido AJAX
                    },
                    body: JSON.stringify({ setting: setting, value: value })
                })
                .then(response => {
                    if (!response.ok) {
                        // Se a resposta não for OK, tenta ler como texto para ver o erro PHP
                        return response.text().then(text => { throw new Error(text || 'Erro no servidor') });
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        showNotification('Configuração salva!', true);
                    } else {
                        showNotification(result.error || 'Erro ao salvar configuração.', false);
                    }
                })
                .catch(err => {
                    console.error("Erro ao salvar:", err);
                    showNotification('Erro de comunicação.', false);
                });
            }
        });
    </script>
</body>
</html>
