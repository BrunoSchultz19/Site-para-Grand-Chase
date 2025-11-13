<?php
session_start();
require_once 'banco.php';

// Se usuário já está logado, redireciona para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

// Verifica se usuário está logado para mostrar informações no cabeçalho
$usuarioLogado = isset($_SESSION['usuario_login']);
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['usuario_login'] ?? '';

// Busca os dados do usuário logado (igual ao painel.php)
if ($usuarioLogado) {
    try {
        $stmt = $pdo->prepare("SELECT Login, nome, email, firstLogin, sex, gamePoint FROM users WHERE Login = ?");
        $stmt->execute([$_SESSION['usuario_login']]);
        $usuario = $stmt->fetch();

        // Carrega VCPoint do usuário (igual ao painel.php)
        $stmtVC = $pdo->prepare("SELECT vc.VCPoint 
                                FROM VCGAVirtualCash vc 
                                INNER JOIN users u ON vc.LoginUID = u.LoginUID 
                                WHERE u.Login = ?");
        $stmtVC->execute([$_SESSION['usuario_login']]);
        $vcPoint = $stmtVC->fetch();

    } catch (PDOException $e) {
        // Se der erro, define valores padrão
        $usuario = [];
        $vcPoint = ['VCPoint' => 0];
    }
}

// Busca os usuários online
try {
    $stmtOnline = $pdo->prepare("
        SELECT u.Login, u.nome, cs.UserNum 
        FROM ConnectStatusDB cs 
        INNER JOIN users u ON cs.UserNum = u.LoginUID 
        WHERE cs.ConnectStat = 1
        ORDER BY u.nome
    ");
    $stmtOnline->execute();
    $usuariosOnline = $stmtOnline->fetchAll(PDO::FETCH_ASSOC);
    $totalOnline = count($usuariosOnline);
} catch (PDOException $e) {
    $usuariosOnline = [];
    $totalOnline = 0;
}

// Busca os personagens com mais EXP
try {
    $stmtTopChars = $pdo->prepare("
        SELECT TOP 20 c.*, u.nome as usuario_nome 
        FROM Characters c 
        INNER JOIN users u ON c.Login = u.Login 
        ORDER BY c.Exp DESC
    ");
    $stmtTopChars->execute();
    $topPersonagens = $stmtTopChars->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $topPersonagens = [];
}

// Notícias do servidor (você pode substituir por dados do banco de dados)
$noticias = [
    [
        'titulo' => 'NOTICIA TESTE 1',
        'conteudo' => 'NOTICIA TESTE',
        'data' => '15/12/2024',
        'autor' => 'Administração',
        'categoria' => 'Atualização',
        'link' => 'noticia1.php'
    ],
    [
        'titulo' => 'NOTICIA TESTE 2',
        'conteudo' => 'NOTICIA TESTE',
        'data' => '10/12/2024',
        'autor' => 'Staff',
        'categoria' => 'Evento',
        'link' => 'noticia2.php'
    ],
    [
        'titulo' => 'NOTICIA TESTE 3',
        'conteudo' => 'NOTICIA TESTE',
        'data' => '05/12/2024',
        'autor' => 'Administração',
        'categoria' => 'Aviso',
        'link' => 'noticia3.php'
    ],
    [
        'titulo' => 'NOTICIA TESTE 4',
        'conteudo' => 'NOTICIA TESTE',
        'data' => '01/12/2024',
        'autor' => 'Staff',
        'categoria' => 'Evento',
        'link' => 'noticia4.php'
    ]
];

// Mapeia CharType para nomes dos personagens e ícones (mantendo apenas os selecionados)
$charTypes = [
    0 => ['nome' => 'Elesis', 'icone' => 'icon/elesis.png'],
    1 => ['nome' => 'Lire', 'icone' => 'icon/lire.jpg'],
    2 => ['nome' => 'Arme', 'icone' => 'icon/arme.png'],
    3 => ['nome' => 'Lass', 'icone' => 'icon/lass.png'],
    4 => ['nome' => 'Ryan', 'icone' => 'icon/ryan.png'],
    5 => ['nome' => 'Ronan', 'icone' => 'icon/ronan.png'],
    6 => ['nome' => 'Amy', 'icone' => 'icon/amy.jpg'],
    7 => ['nome' => 'Jin', 'icone' => 'icon/jin.jpg'],
    8 => ['nome' => 'Sieghart', 'icone' => 'icon/sieg.png'],
    9 => ['nome' => 'Mari', 'icone' => 'icon/mari.png'],
    10 => ['nome' => 'Dio', 'icone' => 'icon/dio.jpg'],
    11 => ['nome' => 'Zero', 'icone' => 'icon/zero.png'],
    12 => ['nome' => 'Ley', 'icone' => 'icon/ley.png']
];

// Dados do carrossel - APENAS IMAGENS
$carouselImages = [
    [
        'imagem' => 'icon/teste1.png',
        'titulo' => 'Nova Atualização Disponível!',
        'descricao' => 'Confira as novidades da última atualização do servidor'
    ],
    [
        'imagem' => 'icon/teste2.png',
        'titulo' => 'Evento Especial de Natal',
        'descricao' => 'Participe do nosso evento especial de fim de ano'
    ],
    [
        'imagem' => 'icon/teste3.png',
        'titulo' => 'Novos Personagens',
        'descricao' => 'Descubra os novos personagens disponíveis'
    ],
    [
        'imagem' => 'icon/teste4.png',
        'titulo' => 'Torneio Mensal',
        'descricao' => 'Inscrições abertas para o torneio deste mês'
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Chase Skarlat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .header {
            background: rgba(0, 0, 0, 0.8);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: relative;
        }
        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
        }
        .nav-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-right: 1rem;
        }
        .user-name {
            font-weight: bold;
            color: #3498db;
        }
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-align: center;
            display: inline-block;
            cursor: pointer;
        }
        .btn-info {
            background: #f39c12;
            color: white;
        }
        .btn-info:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }
        .btn-login {
            background: #3498db;
            color: white;
        }
        .btn-login:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        .btn-cadastro {
            background: #27ae60;
            color: white;
        }
        .btn-cadastro:hover {
            background: #219a52;
            transform: translateY(-2px);
        }
        .btn-painel {
            background: #9b59b6;
            color: white;
        }
        .btn-painel:hover {
            background: #8e44ad;
            transform: translateY(-2px);
        }
        .btn-sair {
            background: #e74c3c;
            color: white;
        }
        .btn-sair:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        /* USUÁRIOS ONLINE NO CABEÇALHO */
        .online-users-header {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(39, 174, 96, 0.3);
            border: 1px solid rgba(39, 174, 96, 0.5);
            transition: all 0.3s ease;
        }

        .online-users-header:hover {
            background: rgba(39, 174, 96, 0.5);
            transform: translateY(-2px);
        }

        .online-count-header {
            background: #27ae60;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: bold;
            min-width: 30px;
            text-align: center;
        }

        .online-users-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: rgba(0, 0, 0, 0.95);
            border-radius: 10px;
            padding: 1rem;
            min-width: 300px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .online-users-dropdown.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .online-dropdown-title {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #27ae60;
            border-bottom: 2px solid #27ae60;
            padding-bottom: 0.5rem;
        }

        .online-users-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .online-user-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .online-user-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .user-avatar-small {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db, #9b59b6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            border: 2px solid #27ae60;
            flex-shrink: 0;
        }

        .user-info-small {
            flex: 1;
        }

        .user-name-small {
            font-weight: bold;
            font-size: 0.9rem;
            color: #ecf0f1;
        }

        .user-login-small {
            color: #bdc3c7;
            font-size: 0.8rem;
        }

        .user-online-dot {
            width: 8px;
            height: 8px;
            background: #27ae60;
            border-radius: 50%;
            margin-left: auto;
            flex-shrink: 0;
        }

        .no-users-online-dropdown {
            text-align: center;
            padding: 1rem;
            color: rgba(255, 255, 255, 0.7);
            font-style: italic;
        }

        /* Carrossel de Notícias - SIMPLIFICADO */
        .news-carousel {
            position: relative;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            overflow: hidden;
        }

        .carousel-container {
            position: relative;
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .carousel-slide.active {
            opacity: 1;
        }

        .carousel-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 2rem;
            color: white;
        }

        .carousel-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .carousel-description {
            font-size: 1rem;
            opacity: 0.9;
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .carousel-nav:hover {
            background: rgba(0,0,0,0.8);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-prev {
            left: 20px;
        }

        .carousel-next {
            right: 20px;
        }

        .carousel-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .carousel-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .carousel-indicator.active {
            background: white;
            transform: scale(1.2);
        }

        /* NOVO LAYOUT - Ranking à esquerda e Notícias à direita */
        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 50px 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .ranking-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .news-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .ranking-title, .news-title {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        /* Estilos para o filtro de personagens - COMPACTO */
        .ranking-filter {
            margin-bottom: 1rem;
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-input {
            flex: 1;
            min-width: 180px;
            padding: 8px 12px;
            border-radius: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(0, 0, 0, 0.3);
            color: white;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .filter-input:focus {
            border-color: #3498db;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .filter-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .character-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
            gap: 8px;
            margin: 10px 0;
            padding: 12px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }
        
        .character-filter-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .character-filter-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .character-filter-item.active {
            background: rgba(52, 152, 219, 0.3);
            border-color: #3498db;
        }
        
        .character-filter-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3498db;
            margin-bottom: 3px;
        }
        
        .character-filter-name {
            font-size: 0.65rem;
            text-align: center;
            font-weight: bold;
            color: #ecf0f1;
            line-height: 1.1;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: none;
            background: #3498db;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .filter-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .filter-reset {
            background: #e74c3c;
        }
        
        .filter-reset:hover {
            background: #c0392b;
        }
        
        /* Botão Expandir Ranking */
        .expand-ranking-btn {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            background: rgba(52, 152, 219, 0.3);
            color: white;
            border: 2px solid #3498db;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .expand-ranking-btn:hover {
            background: rgba(52, 152, 219, 0.5);
            transform: translateY(-2px);
        }
        
        .no-results {
            text-align: center;
            padding: 2rem;
            color: rgba(255, 255, 255, 0.7);
            font-style: italic;
        }
        
        .ranking-table {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-height: 520px;
            transition: all 0.3s ease;
        }
        
        .ranking-table.expanded {
            max-height: 1000px;
        }
        
        .ranking-header {
            display: grid;
            grid-template-columns: 60px 1fr 1fr 1fr 1fr;
            background: rgba(52, 152, 219, 0.8);
            padding: 0.8rem;
            font-weight: bold;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .ranking-row {
            display: grid;
            grid-template-columns: 60px 1fr 1fr 1fr 1fr;
            padding: 0.8rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            align-items: center;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .ranking-row.hidden {
            display: none;
        }
        
        .ranking-row:last-child {
            border-bottom: none;
        }
        
        .ranking-row:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .rank-number {
            font-size: 1rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin: 0 auto;
        }
        
        .rank-1 { background: #f39c12; color: white; }
        .rank-2 { background: #bdc3c7; color: white; }
        .rank-3 { background: #cd7f32; color: white; }
        .rank-other { background: #34495e; color: white; }
        
        .character-info {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: flex-start;
        }
        
        .character-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3498db;
        }
        
        .character-name {
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .player-name {
            color: #ecf0f1;
            font-size: 0.9rem;
        }
        
        .exp-value {
            font-weight: bold;
            color: #f39c12;
            font-size: 0.9rem;
        }
        
        .level-value {
            font-weight: bold;
            color: #e74c3c;
            font-size: 0.9rem;
        }
        
        .news-grid {
            display: grid;
            gap: 1.2rem;
        }
        
        .news-card {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            padding: 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .news-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .news-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.8rem;
        }
        
        .news-category {
            background: #e74c3c;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .news-date {
            color: #bdc3c7;
            font-size: 0.8rem;
        }
        
        .news-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.8rem;
            color: #fff;
            line-height: 1.3;
        }
        
        .news-content {
            color: #ecf0f1;
            line-height: 1.5;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
        }
        
        .news-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 0.8rem;
        }
        
        .news-author {
            color: #3498db;
            font-weight: bold;
            font-size: 0.8rem;
        }
        
        .read-more {
            color: #f39c12;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.8rem;
            transition: color 0.3s ease;
        }
        
        .read-more:hover {
            color: #e67e22;
        }
        
        .download-section {
            text-align: center;
            padding: 40px 20px;
            background: rgba(0, 0, 0, 0.3);
            margin: 40px 0;
        }
        
        .btn-download {
            background: #e74c3c;
            color: white;
            font-size: 1.1rem;
            padding: 12px 25px;
        }
        
        .btn-download:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .welcome-message {
            text-align: center;
            background: rgba(52, 152, 219, 0.2);
            padding: 15px;
            margin: 15px auto;
            max-width: 700px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            font-size: 0.9rem;
        }
        
        .hero {
            text-align: center;
            padding: 100px 20px;
        }
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 50px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .default-icon {
            width: 35px;
            height: 35px;
            background: #bdc3c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #7f8c8d;
            border: 2px solid #95a5a6;
        }
        
        /* Estilos para o modal de informações */
        .user-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .user-modal-content {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            max-width: 450px;
            width: 90%;
            color: #2c3e50;
            position: relative;
            transform: scale(0.7);
            opacity: 0;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .user-modal.show .user-modal-content {
            transform: scale(1);
            opacity: 1;
        }
        
        .user-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.8rem;
        }
        
        .user-modal-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .close-modal {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-details {
            display: grid;
            gap: 0.6rem;
        }
        
        .user-detail {
            display: flex;
            justify-content: space-between;
            padding: 0.4rem 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .user-detail:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: bold;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }
        
        .detail-value {
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .user-avatar-large {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db, #9b59b6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin: 0 auto 0.8rem;
            border: 3px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .cash-icon {
            width: 14px;
            height: 14px;
            vertical-align: middle;
        }
        
        /* Responsividade */
        @media (max-width: 1024px) {
            .content-wrapper {
                grid-template-columns: 1fr;
                gap: 1.2rem;
            }
            
            .ranking-header,
            .ranking-row {
                grid-template-columns: 50px 1fr 1fr 1fr 1fr;
            }
            
            .carousel-container {
                height: 350px;
            }
            
            .carousel-title {
                font-size: 1.5rem;
            }
            
            .online-users-dropdown {
                min-width: 280px;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                padding: 1rem;
            }
            
            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .online-users-header {
                order: -1;
                width: 100%;
                justify-content: center;
            }
            
            .online-users-dropdown {
                left: 50%;
                transform: translateX(-50%);
                min-width: 90%;
            }
            
            .ranking-header,
            .ranking-row {
                grid-template-columns: 45px 1fr 1fr 1fr;
            }
            
            .ranking-header div:nth-child(5),
            .ranking-row div:nth-child(5) {
                display: none;
            }
            
            .ranking-filter {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-input, .filter-btn {
                width: 100%;
            }
            
            .character-filter-grid {
                grid-template-columns: repeat(auto-fill, minmax(55px, 1fr));
            }
            
            .character-filter-icon {
                width: 35px;
                height: 35px;
            }
            
            .character-filter-name {
                font-size: 0.6rem;
            }
            
            .carousel-container {
                height: 300px;
            }
            
            .carousel-title {
                font-size: 1.3rem;
            }
            
            .carousel-description {
                font-size: 0.9rem;
            }
            
            .carousel-nav {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 480px) {
            .ranking-header,
            .ranking-row {
                grid-template-columns: 35px 1fr 1fr;
            }
            
            .ranking-header div:nth-child(4),
            .ranking-row div:nth-child(4),
            .ranking-header div:nth-child(5),
            .ranking-row div:nth-child(5) {
                display: none;
            }
            
            .character-info {
                flex-direction: column;
                gap: 4px;
            }
            
            .character-filter-grid {
                grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
            }
            
            .character-filter-icon {
                width: 30px;
                height: 30px;
            }
            
            .character-filter-name {
                font-size: 0.55rem;
            }
            
            .carousel-container {
                height: 250px;
            }
            
            .carousel-content {
                padding: 1rem;
            }
            
            .carousel-title {
                font-size: 1.1rem;
            }
            
            .carousel-description {
                font-size: 0.8rem;
            }
            
            .carousel-nav {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Cabeçalho dinâmico -->
    <header class="header">
        <div class="logo">🏰 Grand Chase Skarlat</div>
        
        <!-- Usuários Online no Cabeçalho -->
        <div class="online-users-header" id="onlineUsersHeader">
            <span>👥 Online</span>
            <span class="online-count-header"><?php echo $totalOnline; ?></span>
            
            <!-- Dropdown de Usuários Online -->
            <div class="online-users-dropdown" id="onlineUsersDropdown">
                <div class="online-dropdown-title">Usuários Online (<?php echo $totalOnline; ?>)</div>
                
                <div class="online-users-list">
                    <?php if (!empty($usuariosOnline)): ?>
                        <?php foreach ($usuariosOnline as $usuarioOnline): ?>
                        <div class="online-user-item">
                            <div class="user-avatar-small">
                                <?php echo strtoupper(substr($usuarioOnline['nome'] ?? $usuarioOnline['Login'], 0, 1)); ?>
                            </div>
                            <div class="user-info-small">
                                <div class="user-name-small"><?php echo htmlspecialchars($usuarioOnline['nome']); ?></div>
                                <div class="user-login-small">@<?php echo htmlspecialchars($usuarioOnline['Login']); ?></div>
                            </div>
                            <div class="user-online-dot"></div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-users-online-dropdown">
                            <p>Nenhum usuário online no momento</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="nav-buttons">
            <?php if ($usuarioLogado): ?>
                <!-- Se usuário está logado -->
                <div class="user-info">
                    <span class="user-name">👋 Olá, <?php echo htmlspecialchars($nomeUsuario); ?>!</span>
                </div>
                <button class="btn btn-info" onclick="showUserInfo()">ℹ️ Minhas Informações</button>
                <a href="painel.php" class="btn btn-painel">📊 Painel</a>
                <a href="sair.php" class="btn btn-sair">🚪 Sair</a>
            <?php else: ?>
                <!-- Se usuário não está logado -->
                <a href="entrar.php" class="btn btn-login">🚪 Entrar</a>
                <a href="cadastro.php" class="btn btn-cadastro">📝 Cadastrar</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Modal de Informações do Usuário -->
    <div id="userModal" class="user-modal">
        <div class="user-modal-content">
            <div class="user-modal-header">
                <h2 class="user-modal-title">📋 Minhas Informações</h2>
                <button class="close-modal" onclick="hideUserInfo()">×</button>
            </div>
            
            <?php if ($usuarioLogado && isset($usuario)): ?>
            <div class="user-avatar-large">
                <?php echo strtoupper(substr($usuario['nome'] ?? $usuario['Login'], 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="user-detail">
                    <span class="detail-label">👤 Nome:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($usuario['nome']); ?></span>
                </div>
                <div class="user-detail">
                    <span class="detail-label">🔑 Login:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($usuario['Login']); ?></span>
                </div>
                <div class="user-detail">
                    <span class="detail-label">📧 Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($usuario['email']); ?></span>
                </div>
                <div class="user-detail">
                    <span class="detail-label">⚧ Sexo:</span>
                    <span class="detail-value">
                        <?php 
                        $sexo = $usuario['sex'] ?? '';
                        if ($sexo === '0' || $sexo === 0) {
                            echo 'Masculino';
                        } elseif ($sexo === '1' || $sexo === 1) {
                            echo 'Feminino';
                        } else {
                            echo 'Não informado';
                        }
                        ?>
                    </span>
                </div>
                <div class="user-detail">
                    <span class="detail-label">🎮 Game Points:</span>
                    <span class="detail-value"><?php echo number_format($usuario['gamePoint'] ?? 0); ?></span>
                </div>
                <div class="user-detail">
                    <span class="detail-label">
                        <?php if (file_exists('icon/cash.png')): ?>
                            <img src="icon/cash.png" alt="CASH" class="cash-icon">
                        <?php else: ?>
                            🪙
                        <?php endif; ?>
                        CASH:
                    </span>
                    <span class="detail-value"><?php echo number_format($vcPoint['VCPoint'] ?? 0); ?></span>
                </div>
                <div class="user-detail">
                    <span class="detail-label">📅 Data de Cadastro:</span>
                    <span class="detail-value">
                        <?php 
                        if (isset($usuario['firstLogin'])) {
                            echo date('d/m/Y H:i', strtotime($usuario['firstLogin']));
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </span>
                </div>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: #7f8c8d;">
                <p>❌ Erro ao carregar informações do usuário.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <main>
        <?php if ($usuarioLogado): ?>
        <div class="welcome-message">
            <h2>🎉 Bem-vindo de volta, <?php echo htmlspecialchars($nomeUsuario); ?>!</h2>
            <p>Você está logado. Clique no botão "Minhas Informações" para ver seus dados ou acesse o painel para ver suas estatísticas completas.</p>
        </div>

        <!-- Carrossel de Notícias - APENAS IMAGENS (AGORA ABAIXO DA MENSAGEM DE BEM-VINDO) -->
        <section class="news-carousel">
            <div class="carousel-container" id="carouselContainer">
                <?php foreach ($carouselImages as $index => $imagem): ?>
                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                    <?php if (file_exists($imagem['imagem'])): ?>
                        <img src="<?php echo $imagem['imagem']; ?>" alt="<?php echo $imagem['titulo']; ?>" class="carousel-image">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <?php echo $imagem['titulo']; ?>
                        </div>
                    <?php endif; ?>
                    <div class="carousel-content">
                        <h3 class="carousel-title"><?php echo $imagem['titulo']; ?></h3>
                        <p class="carousel-description"><?php echo $imagem['descricao']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Botões de Navegação -->
                <button class="carousel-nav carousel-prev" onclick="prevSlide()">❮</button>
                <button class="carousel-nav carousel-next" onclick="nextSlide()">❯</button>

                <!-- Indicadores -->
                <div class="carousel-indicators">
                    <?php foreach ($carouselImages as $index => $imagem): ?>
                    <button class="carousel-indicator <?php echo $index === 0 ? 'active' : ''; ?>" 
                            onclick="goToSlide(<?php echo $index; ?>)"></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php else: ?>
        <!-- Conteúdo apenas para usuários NÃO logados -->
        <section class="hero">
            <h1>Bem-vindo ao Grand Chase Skarlat</h1>
            <p>Reviva a emoção do Grand Chase em um servidor privado repleto de recursos exclusivos e uma comunidade ativa!</p>
            <a href="https://www.mediafire.com/file/zfb3vawbpm2szh2/Grand+Chase+Skarlat+6.0.rar/file" 
               target="_blank" 
               class="btn btn-download">
                📥 Download Grand Chase Skarlat 6.0
            </a>
        </section>

        <!-- Carrossel de Notícias - APENAS IMAGENS (AGORA ABAIXO DO HERO) -->
        <section class="news-carousel">
            <div class="carousel-container" id="carouselContainer">
                <?php foreach ($carouselImages as $index => $imagem): ?>
                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                    <?php if (file_exists($imagem['imagem'])): ?>
                        <img src="<?php echo $imagem['imagem']; ?>" alt="<?php echo $imagem['titulo']; ?>" class="carousel-image">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <?php echo $imagem['titulo']; ?>
                        </div>
                    <?php endif; ?>
                    <div class="carousel-content">
                        <h3 class="carousel-title"><?php echo $imagem['titulo']; ?></h3>
                        <p class="carousel-description"><?php echo $imagem['descricao']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Botões de Navegação -->
                <button class="carousel-nav carousel-prev" onclick="prevSlide()">❮</button>
                <button class="carousel-nav carousel-next" onclick="nextSlide()">❯</button>

                <!-- Indicadores -->
                <div class="carousel-indicators">
                    <?php foreach ($carouselImages as $index => $imagem): ?>
                    <button class="carousel-indicator <?php echo $index === 0 ? 'active' : ''; ?>" 
                            onclick="goToSlide(<?php echo $index; ?>)"></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="feature-card">
                <h3>🎮 Jogabilidade Clássica</h3>
                <p>Experimente o Grand Chase como você se lembra, com todas as mecânicas clássicas que tornaram o jogo lendário.</p>
            </div>
            <div class="feature-card">
                <h3>👥 Comunidade Ativa</h3>
                <p>Junte-se a uma comunidade vibrante de jogadores apaixonados pelo Grand Chase.</p>
            </div>
            <div class="feature-card">
                <h3>⚡ Servidor Estável</h3>
                <p>Desfrute de um servidor otimizado com alta estabilidade e performance.</p>
            </div>
        </section>
        <?php endif; ?>

        <!-- NOVO LAYOUT: Ranking à esquerda e Notícias à direita -->
        <div class="content-wrapper">
            <!-- Ranking de Personagens (Lado Esquerdo) -->
            <section class="ranking-section">
                <h2 class="ranking-title">🏆 Ranking de Personagens</h2>
                
                <!-- Filtro de Personagens com Ícones - COMPACTO -->
                <div class="ranking-filter">
                    <input type="text" id="filterInput" class="filter-input" placeholder="Buscar por personagem ou jogador...">
                    <button id="filterBtn" class="filter-btn filter-btn">🔍 Filtrar</button>
                    <button id="resetBtn" class="filter-btn filter-reset">🔄 Limpar</button>
                </div>
                
                <!-- Grid de personagens para filtro visual - COMPACTO -->
                <div class="character-filter-grid" id="characterFilterGrid">
                    <div class="character-filter-item active" data-character="all">
                        <div class="default-icon">🎮</div>
                        <span class="character-filter-name">Todos</span>
                    </div>
                    <?php foreach ($charTypes as $id => $char): ?>
                    <div class="character-filter-item" data-character="<?php echo $id; ?>">
                        <?php if (file_exists($char['icone'])): ?>
                            <img src="<?php echo $char['icone']; ?>" alt="<?php echo $char['nome']; ?>" class="character-filter-icon">
                        <?php else: ?>
                            <div class="default-icon"><?php echo substr($char['nome'], 0, 1); ?></div>
                        <?php endif; ?>
                        <span class="character-filter-name"><?php echo $char['nome']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="ranking-table" id="rankingTable">
                    <div class="ranking-header">
                        <div>Posição</div>
                        <div>Personagem</div>
                        <div>Jogador</div>
                        <div>Nível</div>
                        <div>Experiência</div>
                    </div>
                    <div id="rankingContainer">
                    <?php if (!empty($topPersonagens)): ?>
                        <?php 
                        $showCount = 0;
                        foreach ($topPersonagens as $index => $personagem): 
                            // Tenta encontrar as colunas automaticamente
                            $charType = null;
                            $exp = null;
                            $level = null;
                            
                            // Procura pelas colunas baseado nos nomes comuns
                            foreach ($personagem as $key => $value) {
                                if (strpos(strtolower($key), 'chartype') !== false) $charType = $value;
                                if (strpos(strtolower($key), 'exp') !== false) $exp = $value;
                                if (strpos(strtolower($key), 'level') !== false) $level = $value;
                            }
                            
                            // Se não encontrou pelos nomes, tenta pelas posições numéricas
                            if ($charType === null) $charType = $personagem[1] ?? $personagem['Column1'] ?? 0;
                            if ($exp === null) $exp = $personagem[3] ?? $personagem['Column3'] ?? 0;
                            if ($level === null) $level = $personagem[4] ?? $personagem['Column4'] ?? 1;
                            
                            $dadosPersonagem = $charTypes[$charType] ?? ['nome' => 'Personagem ' . $charType, 'icone' => 'icon/default.png'];
                            $nomePersonagem = $dadosPersonagem['nome'];
                            $iconePersonagem = $dadosPersonagem['icone'];
                            $usuarioNome = $personagem['usuario_nome'] ?? 'Desconhecido';
                            
                            $showCount++;
                            $isHidden = $showCount > 10 ? 'hidden' : '';
                        ?>
                            <div class="ranking-row <?php echo $isHidden; ?>" data-character="<?php echo $nomePersonagem; ?>" data-player="<?php echo $usuarioNome; ?>" data-chartype="<?php echo $charType; ?>">
                                <div>
                                    <div class="rank-number <?php echo 'rank-' . ($index + 1 <= 3 ? $index + 1 : 'other'); ?>">
                                        <?php echo $index + 1; ?>
                                    </div>
                                </div>
                                <div class="character-info">
                                    <?php if (file_exists($iconePersonagem)): ?>
                                        <img src="<?php echo $iconePersonagem; ?>" alt="<?php echo $nomePersonagem; ?>" class="character-icon">
                                    <?php else: ?>
                                        <div class="default-icon">🎮</div>
                                    <?php endif; ?>
                                    <span class="character-name"><?php echo htmlspecialchars($nomePersonagem); ?></span>
                                </div>
                                <div class="player-name"><?php echo htmlspecialchars($usuarioNome); ?></div>
                                <div class="level-value"><?php echo $level; ?></div>
                                <div class="exp-value"><?php echo number_format($exp); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="ranking-row">
                            <div colspan="5" style="text-align: center; padding: 2rem;">
                                Nenhum dado de ranking disponível no momento.
                            </div>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
                
                <!-- Botão Expandir -->
                <?php if (!empty($topPersonagens) && count($topPersonagens) > 10): ?>
                <button id="expandRankingBtn" class="expand-ranking-btn">
                    📊 Ver Ranking Completo (<?php echo count($topPersonagens); ?> jogadores)
                </button>
                <?php endif; ?>
            </section>

            <!-- Notícias do Servidor (Lado Direito) -->
            <section class="news-section">
                <h2 class="news-title">📰 Notícias do Servidor</h2>
                <div class="news-grid">
                    <?php foreach ($noticias as $noticia): ?>
                    <div class="news-card">
                        <div class="news-header">
                            <span class="news-category"><?php echo htmlspecialchars($noticia['categoria']); ?></span>
                            <span class="news-date"><?php echo htmlspecialchars($noticia['data']); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                        <p class="news-content"><?php echo htmlspecialchars($noticia['conteudo']); ?></p>
                        <div class="news-footer">
                            <span class="news-author">Por: <?php echo htmlspecialchars($noticia['autor']); ?></span>
                            <a href="<?php echo htmlspecialchars($noticia['link']); ?>" class="read-more">Ler mais →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <!-- Rodapé -->
    <footer style="text-align: center; padding: 1.5rem; background: rgba(0,0,0,0.8); margin-top: 40px;">
        <p>&copy; 2024 Grand Chase Skarlat. Todos os direitos reservados.</p>
    </footer>

    <script>
        function showUserInfo() {
            const modal = document.getElementById('userModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        function hideUserInfo() {
            const modal = document.getElementById('userModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 400);
        }

        // Fechar modal clicando fora
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideUserInfo();
            }
        });

        // USUÁRIOS ONLINE NO CABEÇALHO
        const onlineUsersHeader = document.getElementById('onlineUsersHeader');
        const onlineUsersDropdown = document.getElementById('onlineUsersDropdown');

        // Mostrar/ocultar dropdown de usuários online
        onlineUsersHeader.addEventListener('click', function(e) {
            e.stopPropagation();
            onlineUsersDropdown.classList.toggle('show');
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function(e) {
            if (!onlineUsersHeader.contains(e.target)) {
                onlineUsersDropdown.classList.remove('show');
            }
        });

        // CARROSSEL SIMPLIFICADO - APENAS IMAGENS
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const indicators = document.querySelectorAll('.carousel-indicator');
        const totalSlides = slides.length;
        let autoPlayInterval;

        // Função para mostrar slide específico
        function showSlide(index) {
            // Remove classe active de todos os slides e indicadores
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            // Adiciona classe active ao slide e indicador atual
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
            
            currentSlide = index;
        }

        // Função para próximo slide
        function nextSlide() {
            let nextIndex = currentSlide + 1;
            if (nextIndex >= totalSlides) {
                nextIndex = 0;
            }
            showSlide(nextIndex);
            resetAutoPlay();
        }

        // Função para slide anterior
        function prevSlide() {
            let prevIndex = currentSlide - 1;
            if (prevIndex < 0) {
                prevIndex = totalSlides - 1;
            }
            showSlide(prevIndex);
            resetAutoPlay();
        }

        // Função para ir para slide específico
        function goToSlide(index) {
            showSlide(index);
            resetAutoPlay();
        }

        // Iniciar autoplay
        function startAutoPlay() {
            autoPlayInterval = setInterval(nextSlide, 5000);
        }

        // Parar autoplay
        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }

        // Resetar autoplay
        function resetAutoPlay() {
            stopAutoPlay();
            startAutoPlay();
        }

        // Pausar autoplay quando o mouse estiver sobre o carrossel
        const carouselContainer = document.getElementById('carouselContainer');
        carouselContainer.addEventListener('mouseenter', stopAutoPlay);
        carouselContainer.addEventListener('mouseleave', startAutoPlay);

        // Iniciar autoplay quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            startAutoPlay();
            
            // Filtro de personagens (código anterior mantido)
            const filterInput = document.getElementById('filterInput');
            const filterBtn = document.getElementById('filterBtn');
            const resetBtn = document.getElementById('resetBtn');
            const expandBtn = document.getElementById('expandRankingBtn');
            const rankingTable = document.getElementById('rankingTable');
            const characterFilterItems = document.querySelectorAll('.character-filter-item');
            const rankingRows = document.querySelectorAll('.ranking-row');
            const rankingContainer = document.getElementById('rankingContainer');
            const originalRows = Array.from(rankingRows);
            
            let selectedCharacter = 'all';
            let isExpanded = false;
            
            // Função para expandir/recolher o ranking
            function toggleRanking() {
                isExpanded = !isExpanded;
                
                if (isExpanded) {
                    // Mostrar todas as linhas
                    rankingRows.forEach(row => {
                        row.classList.remove('hidden');
                    });
                    rankingTable.classList.add('expanded');
                    expandBtn.innerHTML = '📊 Recolher Ranking';
                } else {
                    // Mostrar apenas os 10 primeiros
                    rankingRows.forEach((row, index) => {
                        if (index >= 10) {
                            row.classList.add('hidden');
                        }
                    });
                    rankingTable.classList.remove('expanded');
                    expandBtn.innerHTML = '📊 Ver Ranking Completo (' + originalRows.length + ' jogadores)';
                }
            }
            
            // Função para filtrar os resultados
            function filterRankings() {
                const searchTerm = filterInput.value.toLowerCase();
                
                let filteredRows = originalRows;
                
                // Filtrar por texto (personagem ou jogador)
                if (searchTerm) {
                    filteredRows = filteredRows.filter(row => {
                        const character = row.getAttribute('data-character').toLowerCase();
                        const player = row.getAttribute('data-player').toLowerCase();
                        return character.includes(searchTerm) || player.includes(searchTerm);
                    });
                }
                
                // Filtrar por personagem específico
                if (selectedCharacter !== 'all') {
                    filteredRows = filteredRows.filter(row => {
                        const charType = row.getAttribute('data-chartype');
                        return charType === selectedCharacter;
                    });
                }
                
                // Limpar o container
                rankingContainer.innerHTML = '';
                
                // Adicionar as linhas filtradas
                if (filteredRows.length > 0) {
                    filteredRows.forEach((row, index) => {
                        // Atualizar a posição
                        const rankNumber = row.querySelector('.rank-number');
                        rankNumber.textContent = index + 1;
                        
                        // Atualizar a classe de rank
                        rankNumber.className = 'rank-number';
                        if (index + 1 <= 3) {
                            rankNumber.classList.add('rank-' + (index + 1));
                        } else {
                            rankNumber.classList.add('rank-other');
                        }
                        
                        // Mostrar/ocultar baseado na expansão
                        if (!isExpanded && index >= 10) {
                            row.classList.add('hidden');
                        } else {
                            row.classList.remove('hidden');
                        }
                        
                        rankingContainer.appendChild(row);
                    });
                    
                    // Mostrar/ocultar botão de expandir
                    if (expandBtn) {
                        if (filteredRows.length > 10) {
                            expandBtn.style.display = 'block';
                        } else {
                            expandBtn.style.display = 'none';
                        }
                    }
                } else {
                    // Mostrar mensagem de nenhum resultado
                    const noResults = document.createElement('div');
                    noResults.className = 'no-results';
                    noResults.textContent = 'Nenhum personagem encontrado com os critérios de busca.';
                    rankingContainer.appendChild(noResults);
                    
                    // Ocultar botão de expandir
                    if (expandBtn) {
                        expandBtn.style.display = 'none';
                    }
                }
            }
            
            // Event listeners para os ícones dos personagens
            characterFilterItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remover classe active de todos os itens
                    characterFilterItems.forEach(i => i.classList.remove('active'));
                    
                    // Adicionar classe active ao item clicado
                    this.classList.add('active');
                    
                    // Atualizar o personagem selecionado
                    selectedCharacter = this.getAttribute('data-character');
                    
                    // Aplicar o filtro
                    filterRankings();
                });
            });
            
            // Event listeners
            filterBtn.addEventListener('click', filterRankings);
            
            resetBtn.addEventListener('click', function() {
                filterInput.value = '';
                
                // Resetar para "Todos os personagens"
                characterFilterItems.forEach(i => i.classList.remove('active'));
                document.querySelector('.character-filter-item[data-character="all"]').classList.add('active');
                selectedCharacter = 'all';
                
                filterRankings();
            });
            
            // Botão expandir
            if (expandBtn) {
                expandBtn.addEventListener('click', toggleRanking);
            }
            
            // Filtrar ao pressionar Enter no campo de busca
            filterInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    filterRankings();
                }
            });
            
            // Aplicar filtro inicial
            filterRankings();
        });
    </script>
</body>
</html>