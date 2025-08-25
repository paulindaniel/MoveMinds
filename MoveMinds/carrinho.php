<?php
session_start();

// 1. VERIFICAR SE O UTILIZADOR ESTÁ LOGADO
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// --- SIMULAÇÃO DE DADOS DO CARRINHO ---
// Num cenário real, estes dados viriam da base de dados associados ao $_SESSION['user_id']
$cart_items = [
    [
        'id' => 1,
        'name' => 'Garrafa MoveMinds',
        'price' => 27.90,
        'quantity' => 2,
        'image' => 'https://placehold.co/150x150/f8f9fa/333?text=Garrafa'
    ],
    [
        'id' => 2,
        'name' => 'Kit Whey/Shake',
        'price' => 120.99,
        'quantity' => 1,
        'image' => 'https://placehold.co/150x150/f8f9fa/333?text=Kit+Whey'
    ],
    [
        'id' => 3,
        'name' => 'Short DryFit Pro',
        'price' => 59.90,
        'quantity' => 1,
        'image' => 'https://placehold.co/150x150/f8f9fa/333?text=Short'
    ]
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Move Minds - Carrinho</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Estilos Globais e Variáveis de Tema */
        :root {
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
            --btn-success-bg: #28a745;
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
        }

        .dark-theme {
            --body-bg: #1e1e1e;
            --app-bg: #2c2c2c;
            --text-primary: #f0f0f0;
            --text-secondary: #b0b0b0;
            --border-color: #3f3f3f;
            --header-icon-color: #f0f0f0;
            --search-bg: #3f3f3f;
            --card-bg: #3a3a3a;
            --card-border: #4a4a4a;
            --btn-primary-bg: #f0f0f0; /* Botão primário claro no tema escuro */
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

        /* Estilos da Página de Carrinho */
        .page-content { padding: 20px; }
        .section-title { text-align: center; font-size: 18px; color: var(--text-primary); margin: 0 0 25px 0; font-weight: 600; }
        .cart-layout { display: grid; grid-template-columns: 1fr; gap: 25px; }
        @media (min-width: 992px) {
            .cart-layout { grid-template-columns: 2fr 1fr; }
        }

        .cart-items .cart-item { display: flex; gap: 15px; background-color: var(--card-bg); padding: 15px; border-radius: 12px; margin-bottom: 15px; }
        .cart-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .item-details { flex-grow: 1; }
        .item-details h3 { font-size: 16px; margin: 0 0 5px 0; }
        .item-details .price { font-size: 14px; color: var(--text-secondary); font-weight: 500; }
        .item-controls { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
        .quantity-selector { display: flex; align-items: center; gap: 10px; }
        .quantity-btn { width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--border-color); background-color: var(--app-bg); cursor: pointer; font-weight: bold; }
        .quantity { font-weight: 600; }
        .remove-item-btn { background: none; border: none; color: var(--danger-bg, #dc3545); cursor: pointer; font-size: 16px; }

        .order-summary { background-color: var(--card-bg); border-radius: 12px; padding: 25px; position: sticky; top: 90px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 15px; }
        .summary-row.total { font-weight: bold; font-size: 18px; border-top: 2px solid var(--border-color); padding-top: 15px; margin-top: 15px; }
        
        .payment-methods h3 { margin-top: 25px; margin-bottom: 15px; }
        .payment-option { display: flex; align-items: center; gap: 15px; padding: 15px; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 10px; cursor: pointer; }
        .payment-option input[type="radio"] { display: none; }
        .payment-option .icon { font-size: 24px; width: 30px; text-align: center; }
        .payment-option .label { font-weight: 500; }
        .payment-option.selected { border-color: var(--btn-primary-bg); background-color: var(--app-bg); }
        .payment-details { display: none; margin-top: 15px; padding: 15px; background-color: var(--app-bg); border-radius: 8px; }
        .payment-details.active { display: block; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 12px; color: var(--text-secondary); }
        .form-group input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-color); background-color: var(--search-bg); color: var(--text-primary); box-sizing: border-box; }
        .card-details { display: flex; gap: 10px; }
        
        .checkout-btn { width: 100%; padding: 15px; border-radius: 8px; border: none; font-size: 16px; font-weight: 600; cursor: pointer; background-color: var(--btn-success-bg); color: white; transition: opacity .2s; margin-top: 20px;}
        .checkout-btn:hover { opacity: 0.9; }

        /* Footer */
        .footer-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 1200px; display: flex; justify-content: space-around; align-items: flex-end; background-color: var(--footer-bg); padding: 8px 0; border-top: 1px solid var(--border-color); z-index: 998; transition: background-color 0.3s ease, border-color 0.3s ease; }
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
        .notification { position: fixed; bottom: 20px; left: 50%; transform: translate(-50%, 150px); background-color: var(--notification-bg); color: var(--notification-color); padding: 12px 25px; border-radius: 8px; z-index: 2000; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, transform 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .notification.show { opacity: 1; visibility: visible; transform: translate(-50%, 0); }
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
            <img src="logo.png" alt="Logo MN" class="logo">
        </header>
        
        <div class="theme-toggle-fab" id="theme-toggle">
             <i class="fas fa-moon"></i>
        </div>
        
        <div class="page-content">
            <h2 class="section-title">Meu Carrinho</h2>
            <div class="cart-layout">
                <div class="cart-items" id="cart-items-container">
                    <!-- Itens do carrinho serão inseridos aqui pelo PHP -->
                    <?php if (empty($cart_items)): ?>
                        <p style="text-align: center; color: var(--text-secondary);">O seu carrinho está vazio.</p>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-id="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="price">R$ <?php echo number_format($item['price'], 2, ',', '.'); ?></p>
                                    <div class="item-controls">
                                        <div class="quantity-selector">
                                            <button class="quantity-btn minus-btn">-</button>
                                            <span class="quantity"><?php echo $item['quantity']; ?></span>
                                            <button class="quantity-btn plus-btn">+</button>
                                        </div>
                                        <button class="remove-item-btn"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="order-summary">
                    <h3>Resumo do Pedido</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="subtotal">R$ 0,00</span>
                    </div>
                    <div class="summary-row">
                        <span>Frete</span>
                        <span id="shipping">R$ 15,00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="total">R$ 0,00</span>
                    </div>
                    
                    <div class="payment-methods">
                        <h3>Pagamento</h3>
                        <div class="payment-option selected" data-target="credit-card-details">
                            <input type="radio" name="payment" id="credit-card" checked>
                            <i class="fas fa-credit-card icon"></i>
                            <label for="credit-card" class="label">Cartão de Crédito</label>
                        </div>
                        <div class="payment-option" data-target="pix-details">
                            <input type="radio" name="payment" id="pix">
                            <i class="fab fa-pix icon"></i>
                            <label for="pix" class="label">PIX</label>
                        </div>
                        <div class="payment-details active" id="credit-card-details">
                             <div class="form-group">
                                <label for="card-number">Número do Cartão</label>
                                <input type="text" id="card-number" placeholder="0000 0000 0000 0000">
                             </div>
                             <div class="card-details">
                                 <div class="form-group">
                                    <label for="card-expiry">Validade</label>
                                    <input type="text" id="card-expiry" placeholder="MM/AA">
                                 </div>
                                  <div class="form-group">
                                    <label for="card-cvc">CVC</label>
                                    <input type="text" id="card-cvc" placeholder="123">
                                 </div>
                             </div>
                        </div>
                         <div class="payment-details" id="pix-details">
                            <p style="text-align: center;">Leia o código QR com o seu telemóvel para pagar com PIX.</p>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Rickrolling_QR_code.png" alt="Código QR PIX" style="display: block; margin: 10px auto; border-radius: 8px;">
                        </div>
                    </div>

                    <button class="checkout-btn">Finalizar Compra</button>
                </div>
            </div>
        </div>
        
        <footer class="footer-nav"> 
            <a href="home.html" class="nav-item" data-target="inicio"> <i class="fas fa-home"></i> <span>Início</span> </a> 
            <a href="shop.html" class="nav-item" data-target="progresso"> <i class="fas fa-chart-line"></i> <span>Progresso</span> </a> 
            <a href="shop.html" class="nav-item special-item active" data-target="loja"> 
                 <div class="icon-wrapper"><i class="fas fa-shopping-bag"></i></div>
                 <span>Loja</span> 
            </a> 
            <a href="config.php" class="nav-item" data-target="configuracoes"> <i class="fas fa-cog"></i> <span>Configurações</span> </a> 
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

            // --- Lógica da Página do Carrinho ---
            const cartItemsContainer = document.getElementById('cart-items-container');
            const subtotalEl = document.getElementById('subtotal');
            const shippingEl = document.getElementById('shipping');
            const totalEl = document.getElementById('total');
            const paymentOptions = document.querySelectorAll('.payment-option');

            function formatCurrency(value) {
                return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            }

            function updateSummary() {
                let subtotal = 0;
                document.querySelectorAll('.cart-item').forEach(item => {
                    const price = parseFloat(item.dataset.price);
                    const quantity = parseInt(item.querySelector('.quantity').textContent);
                    subtotal += price * quantity;
                });
                
                const shipping = subtotal > 0 ? 15.00 : 0; // Frete grátis se o carrinho estiver vazio
                const total = subtotal + shipping;

                subtotalEl.textContent = formatCurrency(subtotal);
                shippingEl.textContent = formatCurrency(shipping);
                totalEl.textContent = formatCurrency(total);
            }

            cartItemsContainer.addEventListener('click', (e) => {
                const itemEl = e.target.closest('.cart-item');
                if (!itemEl) return;

                const quantityEl = itemEl.querySelector('.quantity');
                let quantity = parseInt(quantityEl.textContent);

                if (e.target.closest('.plus-btn')) {
                    quantity++;
                } else if (e.target.closest('.minus-btn')) {
                    if (quantity > 1) {
                        quantity--;
                    }
                } else if (e.target.closest('.remove-item-btn')) {
                    itemEl.remove();
                }
                
                quantityEl.textContent = quantity;
                updateSummary();
            });

            paymentOptions.forEach(option => {
                option.addEventListener('click', () => {
                    paymentOptions.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                    option.querySelector('input[type="radio"]').checked = true;

                    document.querySelectorAll('.payment-details').forEach(detail => detail.classList.remove('active'));
                    const targetId = option.dataset.target;
                    document.getElementById(targetId).classList.add('active');
                });
            });
            
            // Inicializar resumo
            updateSummary();
        });
    </script>
</body>
</html>
