<?php
session_start();

// 1. VERIFICAR SE O UTILIZADOR ESTÁ LOGADO
// Se não existir uma sessão 'loggedin', redireciona para a página de login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// --- LIGAÇÃO À BASE DE DADOS ---
$servidor = "localhost";
$utilizador_bd = "root";
$password_bd = "";
$basedados = "moveminds";
$ligacao = new mysqli($servidor, $utilizador_bd, $password_bd, $basedados);

if ($ligacao->connect_error) {
    die("Ligação falhou: " . $ligacao->connect_error);
}

// Obter o ID do utilizador da sessão
$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// 2. PROCESSAR O FORMULÁRIO QUANDO FOR SUBMETIDO
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nickname = $_POST['nickname'];
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    
    // Processar upload da foto
    $photo_path = $_POST['current_photo']; // Mantém a foto atual por defeito
    if (isset($_FILES['photo-upload']) && $_FILES['photo-upload']['error'] == 0) {
        $upload_dir = 'uploads/'; // Crie uma pasta chamada 'uploads' no seu projeto
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $photo_name = basename($_FILES["photo-upload"]["name"]);
        $target_file = $upload_dir . $user_id . '_' . $photo_name;
        
        // Move o ficheiro carregado para a pasta de uploads
        if (move_uploaded_file($_FILES["photo-upload"]["tmp_name"], $target_file)) {
            $photo_path = $target_file;
        } else {
             $message = "Erro ao carregar a foto.";
             $message_type = 'error';
        }
    }

    // Atualizar dados na base de dados usando a tabela 'users'
    $stmt = $ligacao->prepare("UPDATE users SET nickname = ?, fullName = ?, email = ?, dob = ?, photo_path = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nickname, $fullName, $email, $dob, $photo_path, $user_id);

    if ($stmt->execute()) {
        $message = "Perfil atualizado com sucesso!";
        $message_type = 'success';
    } else {
        $message = "Erro ao atualizar o perfil.";
        $message_type = 'error';
    }
    $stmt->close();
}


// 3. IR BUSCAR OS DADOS ATUAIS DO UTILIZADOR PARA MOSTRAR NA PÁGINA
$stmt = $ligacao->prepare("SELECT nickname, fullName, email, dob, photo_path FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();
$utilizador = $resultado->fetch_assoc();
$stmt->close();
$ligacao->close();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move Minds - Perfil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
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
            --featured-border: #007bff;
            --check-icon-color: #28a745;
            --special-icon-bg: #343a40;
            --special-icon-color: #ffffff;
            --special-icon-active-bg: #007bff;
            --menu-bg: #ffffff;
            --overlay-bg: rgba(0, 0, 0, 0.5);
            --notification-bg: #28a745;
            --notification-color: white;
            --error-bg: #f8d7da;
            --error-color: #721c24;
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
            --btn-dark-bg: #1e1e1e;
            --btn-primary-bg: #1e1e1e;
            --footer-bg: #1e1e1e;
            --footer-icon-inactive: #888;
            --footer-icon-active: #ffffff;
            --featured-border: #5a5a5a;
            --check-icon-color: #4CAF50;
            --special-icon-bg: #f0f0f0;
            --special-icon-color: #1e1e1e;
            --special-icon-active-bg: #ffffff;
            --menu-bg: #2c2c2c;
            --error-bg: #492226;
            --error-color: #f8d7da;
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
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            background-color: var(--app-bg);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

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

        /* Estilos da Página de Perfil */
        .page-content { padding: 20px; }
        .section-title { text-align: center; font-size: 18px; color: var(--text-primary); margin: 0 0 25px 0; font-weight: 600; }

        .profile-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            max-width: 700px;
            margin: 0 auto 25px auto;
            border: 1px solid var(--border-color);
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 25px;
        }
        .profile-picture-wrapper {
            position: relative;
            margin-bottom: 15px;
        }
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--app-bg);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .change-photo-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: var(--btn-primary-bg);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .profile-form .form-group { margin-bottom: 20px; }
        .profile-form label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: var(--text-secondary); }
        .profile-form input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--app-bg);
            color: var(--text-primary);
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color .2s, background-color .2s;
        }
        .profile-form input:focus {
            outline: none;
            border-color: var(--btn-primary-bg);
        }
        .profile-form input:disabled {
            background: transparent;
            border-color: transparent;
            padding-left: 0;
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .profile-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .profile-btn {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .2s;
        }
        .profile-btn:hover { opacity: 0.9; }
        .edit-btn { background-color: var(--btn-primary-bg); color: white; }
        .save-btn { background-color: var(--check-icon-color); color: white; display: none; }
        .logout-btn { background-color: var(--btn-dark-bg); color: white; text-decoration: none; text-align: center; }
        
        .message-box {
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            color: white;
        }
        .message-box.error { background-color: var(--error-bg); color: var(--error-color); border: 1px solid var(--error-border); }
        .message-box.success { background-color: var(--notification-bg); }

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
                <li><a href="configuracoes.html"><i class="fas fa-cog"></i>Configurações</a></li>
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
            <img src="<?php echo htmlspecialchars($utilizador['photo_path'] ?? 'https://placehold.co/60x60/EEE/111?text=MN'); ?>" alt="Logo MN" class="logo">
        </header>
        
        <div class="theme-toggle-fab" id="theme-toggle">
             <i class="fas fa-moon"></i>
        </div>
        
        <div class="page-content">
            <h2 class="section-title">Meu Perfil</h2>
            
             <?php if (!empty($message)): ?>
                <div class="message-box <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-picture-wrapper">
                        <img src="<?php echo htmlspecialchars($utilizador['photo_path'] ?? 'https://placehold.co/120x120/777/FFF?text=User'); ?>" alt="Foto de Perfil" class="profile-picture" id="profile-picture-preview">
                        <label for="photo-upload" class="change-photo-btn"><i class="fas fa-camera"></i></label>
                    </div>
                </div>

                <form class="profile-form" id="profile-form" method="POST" action="perfil.php" enctype="multipart/form-data">
                    <input type="file" id="photo-upload" name="photo-upload" accept="image/*" style="display: none;">
                    <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($utilizador['photo_path'] ?? ''); ?>">

                    <div class="form-group">
                        <label for="nickname">Nome de Utilizador</label>
                        <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($utilizador['nickname'] ?? ''); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="fullName">Nome Completo</label>
                        <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($utilizador['fullName'] ?? ''); ?>" disabled>
                    </div>
                     <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($utilizador['email'] ?? ''); ?>" disabled>
                    </div>
                     <div class="form-group">
                        <label for="dob">Data de Nascimento</label>
                        <input type="text" id="dob" name="dob" value="<?php echo htmlspecialchars($utilizador['dob'] ?? ''); ?>" placeholder="dd/mm/aaaa" disabled>
                    </div>

                    <div class="profile-actions">
                        <button type="button" class="profile-btn edit-btn" id="edit-btn">Editar Perfil</button>
                        <button type="submit" class="profile-btn save-btn" id="save-btn">Salvar Alterações</button>
                    </div>
                </form>
            </div>
            
            <div class="profile-card">
                 <a href="logout.php" class="profile-btn logout-btn">Terminar Sessão</a>
            </div>
        </div>
        
        <footer class="footer-nav"> 
            <a href="home.html" class="nav-item" data-target="inicio"> <i class="fas fa-home"></i> <span>Início</span> </a> 
            <a href="progresso.html" class="nav-item" data-target="progresso"> <i class="fas fa-chart-line"></i> <span>Progresso</span> </a> 
            <a href="shop.html" class="nav-item" data-target="loja"> <i class="fas fa-shopping-bag"></i> <span>Loja</span> </a> 
            <a href="configuracoes.html" class="nav-item" data-target="configuracoes"> <i class="fas fa-cog"></i> <span>Configurações</span> </a> 
            <a href="perfil.php" class="nav-item special-item active" data-target="perfil"> 
                <div class="icon-wrapper"><i class="fas fa-user-circle"></i></div>
                <span>Perfil</span> 
            </a> 
        </footer>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            const themeIcon = themeToggle.querySelector('i');
            
            const menuToggle = document.getElementById('menu-toggle');
            const sideMenu = document.getElementById('side-menu');
            const closeMenuBtn = document.getElementById('close-menu-btn');
            const overlay = document.getElementById('overlay');

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

            // --- Lógica da Página de Perfil ---
            const editBtn = document.getElementById('edit-btn');
            const saveBtn = document.getElementById('save-btn');
            const profileForm = document.getElementById('profile-form');
            const formInputs = Array.from(profileForm.querySelectorAll('input')).filter(input => input.type !== 'hidden');
            const photoUpload = document.getElementById('photo-upload');
            const photoPreview = document.getElementById('profile-picture-preview');

            function toggleFormEdit(enable) {
                formInputs.forEach(input => {
                    // Não ativa o campo de email para edição
                    if (input.type !== 'file' && input.id !== 'email') {
                       input.disabled = !enable;
                    }
                });
                saveBtn.style.display = enable ? 'flex' : 'none';
                editBtn.style.display = enable ? 'none' : 'flex';
            }

            editBtn.addEventListener('click', () => {
                toggleFormEdit(true);
            });

            // Mostra a pré-visualização da imagem ao escolher um ficheiro
            photoUpload.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        photoPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>
