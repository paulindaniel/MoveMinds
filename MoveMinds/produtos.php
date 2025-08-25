<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// SIMULAÇÃO DE PRODUTOS - No futuro, estes dados podem vir da sua base de dados
$products = [
    ['id' => 1, 'name' => 'Garrafa MoveMinds', 'price' => 27.90, 'image' => 'https://placehold.co/200x200/f8f9fa/333?text=Garrafa'],
    ['id' => 2, 'name' => 'Kit Whey/Shake', 'price' => 120.99, 'image' => 'https://placehold.co/200x200/f8f9fa/333?text=Kit+Whey'],
    ['id' => 3, 'name' => 'Halter Russo 25KG', 'price' => 75.00, 'image' => 'https://placehold.co/200x200/f8f9fa/333?text=Halter'],
    ['id' => 4, 'name' => 'Fones Bluetooth Fit', 'price' => 89.90, 'image' => 'https://placehold.co/200x200/f8f9fa/333?text=Fones'],
    ['id' => 5, 'name' => 'Tênis Performance', 'price' => 249.90, 'image' => 'https://placehold.co/200x200/f8f9fa/333?text=Tênis'],
    ['id' => 6, 'name' => 'Short DryFit Max', 'price' => 59.90, 'image' => 'https://placehold.co/200x200/f8f9fa/333?text=Short']
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move Minds - Loja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Cole aqui o mesmo CSS das outras páginas para manter a consistência */
        :root {
            --body-bg: #f0f2f5; --app-bg: #ffffff; --text-primary: #1c1e21; --text-secondary: #65676b;
            --border-color: #e0e0e0; --header-icon-color: #606770; --search-bg: #f0f2f5; --card-bg: #f7f7f7;
            --btn-dark-bg: #343a40; --btn-primary-bg: #007bff; --footer-bg: #ffffff; --footer-icon-inactive: #65676b;
            --footer-icon-active: #007bff; --special-icon-bg: #343a40; --special-icon-color: #ffffff;
            --special-icon-active-bg: #007bff; --menu-bg: #ffffff; --overlay-bg: rgba(0, 0, 0, 0.5);
            --notification-bg: #28a745; --notification-color: white;
        }
        .dark-theme {
            --body-bg: #1e1e1e; --app-bg: #2c2c2c; --text-primary: #f0f0f0; --text-secondary: #b0b0b0;
            --border-color: #3f3f3f; --header-icon-color: #f0f0f0; --search-bg: #3f3f3f; --card-bg: #3a3a3a;
            --btn-primary-bg: #f0f0f0; --footer-bg: #1e1e1e; --footer-icon-active: #ffffff; --special-icon-bg: #f0f0f0;
            --special-icon-color: #1e1e1e; --special-icon-active-bg: #ffffff; --menu-bg: #2c2c2c;
        }
        body { font-family: 'Inter', Arial, sans-serif; margin: 0; background-color: var(--body-bg); display: flex; justify-content: center; min-height: 100vh; transition: background-color 0.3s ease; }
        .main-container { width: 100%; max-width: 1200px; background-color: var(--app-bg); color: var(--text-primary); box-shadow: 0 0 20px rgba(0,0,0,0.1); display: flex; flex-direction: column; position: relative; padding-bottom: 75px; transition: background-color 0.3s ease, color 0.3s ease; overflow-x: hidden; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; background-color: var(--app-bg); border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 100; }
        .header .menu-toggle { font-size: 22px; color: var(--header-icon-color); cursor: pointer; }
        .header .logo { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .header .search-bar { flex-grow: 1; max-width: 450px; display: flex; align-items: center; background-color: var(--search-bg); border-radius: 20px; padding: 8px 12px; margin: 0 15px; }
        .search-bar i { color: var(--text-secondary); margin-right: 8px; font-size: 0.9em; }
        .search-bar input { flex-grow: 1; background: none; border: none; color: var(--text-primary); font-size: 14px; outline: none; }
        .side-menu { position: fixed; top: 0; left: 0; width: 280px; height: 100%; background-color: var(--menu-bg); z-index: 1002; transform: translateX(-100%); transition: transform 0.3s ease-in-out; display: flex; flex-direction: column; padding: 20px; box-shadow: 4px 0px 15px rgba(0,0,0,0.1); }
        .side-menu.open { transform: translateX(0); }
        .menu-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .menu-header .logo-menu { font-weight: bold; font-size: 20px; color: var(--text-primary); }
        .close-menu-btn { font-size: 28px; cursor: pointer; color: var(--text-secondary); }
        .side-menu nav ul { list-style: none; padding: 0; margin: 0; }
        .side-menu nav a { display: block; padding: 15px 10px; color: var(--text-primary); text-decoration: none; font-size: 18px; border-radius: 8px; transition: background-color 0.2s ease; position: relative;}
        .side-menu nav a:hover { background-color: var(--search-bg); }
        .side-menu nav a i { margin-right: 15px; width: 24px; text-align: center; color: var(--text-secondary); }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: var(--overlay-bg); z-index: 1001; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease; }
        .overlay.visible { opacity: 1; visibility: visible; }
        .theme-toggle-fab { position: fixed; bottom: 95px; right: 25px; width: 50px; height: 50px; background-color: var(--special-icon-bg); color: var(--special-icon-color); border-radius: 50%; display: grid; place-items: center; font-size: 22px; cursor: pointer; z-index: 999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .page-content { padding: 20px; }
        .section-title { text-align: center; font-size: 18px; color: var(--text-primary); margin: 0 0 25px 0; font-weight: 600; }
        .products-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; }
        .product-card { background-color: var(--card-bg); border-radius: 12px; text-align: center; padding: 15px; border: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between; transition: transform .2s ease; }
        .product-card:hover { transform: translateY(-5px); }
        .product-card img { width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
        .product-card .product-name { font-size: 14px; font-weight: 600; margin-bottom: 5px; }
        .product-card .product-price { font-size: 16px; font-weight: bold; color: var(--btn-primary-bg); margin-bottom: 15px; }
        .dark-theme .product-card .product-price { color: var(--text-primary); }
        .add-to-cart-btn { background-color: var(--btn-dark-bg); color: white; border: none; padding: 10px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .footer-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 1200px; display: flex; justify-content: space-around; align-items: flex-end; background-color: var(--footer-bg); padding: 8px 0; border-top: 1px solid var(--border-color); z-index: 998; }
        .nav-item { display: flex; flex-direction: column; align-items: center; text-decoration: none; color: var(--footer-icon-inactive); font-size: 10px; padding: 5px; flex: 1; text-align: center; }
        .nav-item i { font-size: 20px; margin-bottom: 4px; }
        .nav-item.active { color: var(--footer-icon-active); }
        .nav-item.special-item { position: relative; }
        .nav-item.special-item .icon-wrapper { background-color: var(--special-icon-bg); border-radius: 50%; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; margin-bottom: 2px; margin-top: -25px; border: 4px solid var(--footer-bg); box-shadow: 0 -3px 6px rgba(0,0,0,0.1); }
        .nav-item.special-item .icon-wrapper i { font-size: 24px; color: var(--special-icon-color); margin-bottom: 0; }
        .nav-item.special-item.active .icon-wrapper { background-color: var(--special-icon-active-bg); }
        .nav-item.special-item.active span { color: var(--footer-icon-active); }
        .notification { position: fixed; bottom: 20px; left: 50%; transform: translate(-50%, 150px); background-color: var(--notification-bg); color: var(--notification-color); padding: 12px 25px; border-radius: 8px; z-index: 2000; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, transform 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .notification.show { opacity: 1; visibility: visible; transform: translate(-50%, 0); }
    </style>
</head>
<body>
    <div id="side-menu" class="side-menu">
        <!-- Menu Lateral (igual às outras páginas) -->
    </div>
    <div id="overlay" class="overlay"></div>

    <div class="main-container" id="main-container">
        <header class="header">
            <i class="fas fa-bars menu-toggle" id="menu-toggle"></i>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar">
            </div>
            <img src="https://placehold.co/60x60/EEE/111?text=MN" alt="Logo MN" class="logo">
        </header>
        
        <div class="theme-toggle-fab" id="theme-toggle">
             <i class="fas fa-moon"></i>
        </div>
        
        <div class="page-content">
            <h2 class="section-title">Nossos Produtos</h2>
            <div class="products-container">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <p class="product-name"><?php echo htmlspecialchars($product['name']); ?></p>
                        <p class="product-price">R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                        <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">Adicionar ao Carrinho</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <footer class="footer-nav"> 
            <a href="inicio.php" class="nav-item"> <i class="fas fa-home"></i> <span>Início</span> </a> 
            <a href="progresso.php" class="nav-item"> <i class="fas fa-chart-line"></i> <span>Progresso</span> </a> 
            <a href="loja.php" class="nav-item special-item active"> <div class="icon-wrapper"><i class="fas fa-shopping-bag"></i></div> <span>Loja</span> </a> 
            <a href="config.php" class="nav-item"> <i class="fas fa-cog"></i> <span>Configurações</span> </a> 
            <a href="perfil.php" class="nav-item"> <i class="fas fa-user-circle"></i> <span>Perfil</span> </a> 
        </footer>
    </div>
    
    <div id="notification" class="notification"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Cole aqui o JavaScript do menu lateral e do tema, igual às outras páginas
        });

        function showNotification(message) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
            }, 2500);
        }

        function addToCart(productId) {
            fetch('api/carrinho_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Produto adicionado ao carrinho!');
                } else {
                    showNotification('Erro ao adicionar produto.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('Erro de comunicação.');
            });
        }
    </script>
</body>
</html>
