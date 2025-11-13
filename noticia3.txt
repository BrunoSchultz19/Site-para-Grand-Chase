<?php
// noticia.php - Página individual de notícia
session_start();
require_once 'banco.php';

// Verifica se usuário está logado para mostrar informações no cabeçalho
$usuarioLogado = isset($_SESSION['usuario_login']);
$nomeUsuario = $_SESSION['usuario_nome'] ?? $_SESSION['usuario_login'] ?? '';

// Busca os dados do usuário logado
if ($usuarioLogado) {
    try {
        $stmt = $pdo->prepare("SELECT Login, nome, email, firstLogin, sex, gamePoint FROM users WHERE Login = ?");
        $stmt->execute([$_SESSION['usuario_login']]);
        $usuario = $stmt->fetch();

        // Carrega VCPoint do usuário
        $stmtVC = $pdo->prepare("SELECT vc.VCPoint 
                                FROM VCGAVirtualCash vc 
                                INNER JOIN users u ON vc.LoginUID = u.LoginUID 
                                WHERE u.Login = ?");
        $stmtVC->execute([$_SESSION['usuario_login']]);
        $vcPoint = $stmtVC->fetch();

    } catch (PDOException $e) {
        $usuario = [];
        $vcPoint = ['VCPoint' => 0];
    }
}

// Simulação de notícias do banco de dados (substitua por consulta real)
$noticias = [
    1 => [
        'id' => 1,
        'titulo' => '🎉 Grand Chase Skarlat 6.0 Lançado!',
        'conteudo' => '
            <p>Estamos extremamente felizes em anunciar o lançamento oficial da versão 6.0 do Grand Chase Skarlat! Esta atualização traz uma série de novidades que vão revolucionar sua experiência de jogo.</p>
            
            <h3>🎯 Novidades da Versão 6.0:</h3>
            <ul>
                <li><strong>Novos Personagens:</strong> Veigas e Uno agora estão disponíveis!</li>
                <li><strong>Dungeons Inéditas:</strong> 5 novas dungeons com mecânicas exclusivas</li>
                <li><strong>Sistema de Awakening:</strong> Evolua seus personagens além do limite</li>
                <li><strong>Balanceamento PvP:</strong> Melhorias significativas no sistema de PvP</li>
                <li><strong>Gráficos Otimizados:</strong> Texturas em alta resolução e efeitos visuais melhorados</li>
            </ul>
            
            <h3>⚙️ Melhorias Técnicas:</h3>
            <p>O servidor foi completamente otimizado para oferecer uma experiência mais estável e com menos lag. Implementamos:</p>
            <ul>
                <li>Servidores dedicados com maior capacidade</li>
                <li>Sistema anti-cheat aprimorado</li>
                <li>Backup automático de dados</li>
                <li>Suporte a conexões de até 100Mbps</li>
            </ul>
            
            <h3>🎁 Recompensas de Lançamento:</h3>
            <p>Para celebrar o lançamento, todos os jogadores que logarem durante esta semana receberão:</p>
            <ul>
                <li>10.000 CASH</li>
                <li>Pacote de Awakening</li>
                <li>Traje Exclusivo da Versão 6.0</li>
                <li+7 dias de VIP</li>
            </ul>
            
            <p>Não perca essa oportunidade! Junte-se a nós nessa nova jornada no Grand Chase Skarlat.</p>
            
            <p><strong>Atenciosamente,<br>Equipe de Desenvolvimento Skarlat</strong></p>
        ',
        'resumo' => 'Estamos felizes em anunciar o lançamento da versão 6.0 do Grand Chase Skarlat! Novos personagens, dungeons e muito mais!',
        'data' => '15/12/2024',
        'autor' => 'Administração',
        'categoria' => 'Atualização',
        'imagem' => 'noticias/versao-60.jpg',
        'visualizacoes' => 1247,
        'destaque' => true
    ],
    2 => [
        'id' => 2,
        'titulo' => '⚔️ Torneio Mensal Iniciado',
        'conteudo' => '
            <p>O tão aguardado Torneio Mensal de PvP está oficialmente iniciado! Prepare-se para batalhas épicas e conquiste prêmios incríveis.</p>
            
            <h3>🏆 Modalidades do Torneio:</h3>
            <ul>
                <li><strong>1v1 Solo:</strong> Disputas individuais</li>
                <li><strong>3v3 Team:</strong> Batalhas em equipe</li>
                <li><strong>Guild Wars:</strong> Conflitos entre guildas</li>
            </ul>
            
            <h3>📅 Cronograma:</h3>
            <ul>
                <li><strong>Inscrições:</strong> 01/12 a 10/12</li>
                <li><strong>Fase de Grupos:</strong> 11/12 a 20/12</li>
                <li><strong>Playoffs:</strong> 21/12 a 25/12</li>
                <li><strong>Final:</strong> 26/12 às 20:00</li>
            </ul>
            
            <h3>🎯 Premiação:</h3>
            <p><strong>1º Lugar:</strong><br>
            - 50.000 CASH<br>
            - Armadura Lendária<br>
            - Título "Campeão do Torneio"<br>
            - 30 dias de VIP</p>
            
            <p><strong>2º Lugar:</strong><br>
            - 25.000 CASH<br>
            - Armadura Épica<br>
            - 15 dias de VIP</p>
            
            <p><strong>3º Lugar:</strong><br>
            - 10.000 CASH<br>
            - Armadura Rara<br>
            - 7 dias de VIP</p>
            
            <p><strong>Inscreva-se já através do NPC Torneio em qualquer vila!</strong></p>
        ',
        'resumo' => 'Participe do nosso torneio mensal de PvP! Premiação em CASH e itens exclusivos para os melhores colocados.',
        'data' => '10/12/2024',
        'autor' => 'Staff',
        'categoria' => 'Evento',
        'imagem' => 'noticias/torneio-pvp.jpg',
        'visualizacoes' => 892,
        'destaque' => true
    ],
    3 => [
        'id' => 3,
        'titulo' => '🛠️ Manutenção Programada',
        'conteudo' => '
            <p>Informamos a todos os jogadores que haverá uma manutenção programada no servidor para implementação de melhorias e correções.</p>
            
            <h3>📅 Data e Horário:</h3>
            <p><strong>Sábado, 07/12/2024</strong><br>
            Das <strong>08:00</strong> às <strong>12:00</strong> (Horário de Brasília)</p>
            
            <h3>🔧 O que será feito:</h3>
            <ul>
                <li>Correção de bugs reportados</li>
                <li>Otimização do servidor de batalha</li>
                <li>Atualização do sistema de guildas</li>
                <li>Implementação de medidas anti-cheat</li>
                <li>Backup geral dos dados</li>
            </ul>
            
            <h3>⚠️ Importante:</h3>
            <ul>
                <li>O servidor ficará indisponível durante todo o período</li>
                <li>Recomendamos que saia de dungeons antes do horário marcado</li>
                <li>Todas as transações em andamento serão canceladas</li>
                <li>Eventos agendados serão reagendados automaticamente</li>
            </ul>
            
            <p>Agradecemos pela compreensão e pedimos desculpas pelo inconveniente.</p>
        ',
        'resumo' => 'Haverá uma manutenção no servidor no próximo sábado das 08:00 às 12:00 para implementação de melhorias.',
        'data' => '05/12/2024',
        'autor' => 'Administração',
        'categoria' => 'Aviso',
        'imagem' => 'noticias/manutencao.jpg',
        'visualizacoes' => 567,
        'destaque' => false
    ],
    4 => [
        'id' => 4,
        'titulo' => '🎁 Evento de Natal Começa!',
        'conteudo' => '
            <p>O espírito natalino chegou ao Grand Chase Skarlat! Participe do nosso evento especial de Natal e garanta recompensas exclusivas.</p>
            
            <h3>🎄 Dungeon Natalina:</h3>
            <p>Nova dungeon temática disponível até 25/12! Derrote o Grinch e salve o Natal para receber:</p>
            <ul>
                <li>Moedas de Natal</li>
                <li>Itens de coleção</li>
                <li>Trajes festivos</li>
                <li>Experiência extra</li>
            </ul>
            
            <h3>🎅 NPCs Especiais:</h3>
            <ul>
                <li><strong>Papai Noel:</strong> Troque moedas por itens exclusivos</li>
                <li><strong>Rena Rudolph:</strong> Missões diárias especiais</li>
                <li><strong>Anjinho:</strong> Bênçãos temporárias</li>
            </ul>
            
            <h3>📦 Presentes Diários:</h3>
            <p>Todo dia ao logar você recebe um presente especial contendo:</p>
            <ul>
                <li>CASH</li>
                <li>Poções</li>
                <li>Itens de evolução</li>
                <li>Chance de itens lendários</li>
            </ul>
            
            <h3>🏆 Ranking de Natal:</h3>
            <p>Os jogadores que mais ajudarem o Papai Noel aparecerão no ranking especial com prêmios exclusivos!</p>
            
            <p><strong>Que a magia do Natal esteja com você!</strong></p>
        ',
        'resumo' => 'Participe do nosso evento especial de Natal! Dungeons temáticas, NPCs especiais e recompensas exclusivas.',
        'data' => '01/12/2024',
        'autor' => 'Staff',
        'categoria' => 'Evento',
        'imagem' => 'noticias/natal.jpg',
        'visualizacoes' => 1103,
        'destaque' => true
    ]
];

// Obtém o ID da notícia da URL
$noticiaId = $_GET['id'] ?? 1;
$noticia = $noticias[$noticiaId] ?? $noticias[1];

// Notícias relacionadas (excluindo a atual)
$noticiasRelacionadas = array_filter($noticias, function($id) use ($noticiaId) {
    return $id != $noticiaId;
}, ARRAY_FILTER_USE_KEY);

// Limita a 3 notícias relacionadas
$noticiasRelacionadas = array_slice($noticiasRelacionadas, 0, 3);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($noticia['titulo']); ?> - Grand Chase Skarlat</title>
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
        }
        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
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
        .btn-voltar {
            background: #95a5a6;
            color: white;
        }
        .btn-voltar:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }

        /* Estilos para a página de notícia */
        .news-article {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        .news-header {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .news-category {
            background: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .news-date {
            color: #bdc3c7;
            font-size: 1rem;
        }
        .news-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            line-height: 1.3;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .news-author {
            color: #3498db;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .news-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
            color: #bdc3c7;
            font-size: 0.9rem;
        }
        .news-content {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            line-height: 1.8;
        }
        .news-content h2, .news-content h3 {
            margin: 2rem 0 1rem 0;
            color: #f39c12;
        }
        .news-content h2 {
            font-size: 1.8rem;
            border-bottom: 2px solid #f39c12;
            padding-bottom: 0.5rem;
        }
        .news-content h3 {
            font-size: 1.4rem;
        }
        .news-content p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }
        .news-content ul, .news-content ol {
            margin: 1rem 0 1.5rem 2rem;
        }
        .news-content li {
            margin-bottom: 0.5rem;
        }
        .news-content strong {
            color: #f39c12;
        }

        /* Seção de notícias relacionadas */
        .related-news {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 0 20px;
        }
        .related-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .related-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        .related-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }
        .related-category {
            background: #3498db;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .related-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        .related-date {
            color: #bdc3c7;
            font-size: 0.9rem;
        }

        /* Breadcrumb */
        .breadcrumb {
            max-width: 1000px;
            margin: 1rem auto 0 auto;
            padding: 0 20px;
            color: #bdc3c7;
        }
        .breadcrumb a {
            color: #3498db;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .news-title {
                font-size: 2rem;
            }
            .news-content {
                padding: 2rem;
            }
            .news-meta {
                flex-direction: column;
                align-items: flex-start;
            }
            .header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* Modal de informações do usuário (mantido do código anterior) */
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
            padding: 2rem;
            border-radius: 15px;
            max-width: 500px;
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
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 1rem;
        }
        .user-modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .close-modal {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .user-details {
            display: grid;
            gap: 0.8rem;
        }
        .user-detail {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
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
        }
        .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }
        .user-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db, #9b59b6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 1rem;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .cash-icon {
            width: 16px;
            height: 16px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho dinâmico -->
    <header class="header">
        <a href="index.php" class="logo">🏰 Grand Chase Skarlat</a>
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
            <a href="index.php" class="btn btn-voltar">📰 Todas as Notícias</a>
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
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Início</a> > 
            <a href="index.php#noticias">Notícias</a> > 
            <?php echo htmlspecialchars($noticia['titulo']); ?>
        </div>

        <!-- Artigo da Notícia -->
        <article class="news-article">
            <div class="news-header">
                <div class="news-meta">
                    <span class="news-category"><?php echo htmlspecialchars($noticia['categoria']); ?></span>
                    <span class="news-date">📅 <?php echo htmlspecialchars($noticia['data']); ?></span>
                </div>
                <h1 class="news-title"><?php echo htmlspecialchars($noticia['titulo']); ?></h1>
                <div class="news-author">✍️ Por: <?php echo htmlspecialchars($noticia['autor']); ?></div>
                <div class="news-stats">
                    <span>👁️ <?php echo number_format($noticia['visualizacoes']); ?> visualizações</span>
                    <?php if ($noticia['destaque']): ?>
                        <span>⭐ Em Destaque</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="news-content">
                <?php echo $noticia['conteudo']; ?>
            </div>
        </article>

        <!-- Notícias Relacionadas -->
        <?php if (!empty($noticiasRelacionadas)): ?>
        <section class="related-news">
            <h2 class="related-title">📖 Notícias Relacionadas</h2>
            <div class="related-grid">
                <?php foreach ($noticiasRelacionadas as $relacionada): ?>
                <a href="noticia.php?id=<?php echo $relacionada['id']; ?>" class="related-card">
                    <span class="related-category"><?php echo htmlspecialchars($relacionada['categoria']); ?></span>
                    <h3><?php echo htmlspecialchars($relacionada['titulo']); ?></h3>
                    <p class="related-date"><?php echo htmlspecialchars($relacionada['data']); ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Rodapé -->
    <footer style="text-align: center; padding: 2rem; background: rgba(0,0,0,0.8); margin-top: 50px;">
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

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideUserInfo();
            }
        });

        // Simular aumento de visualizações
        setTimeout(() => {
            // Em um sistema real, isso seria uma atualização no banco de dados
            console.log('Visualização registrada para a notícia ID: <?php echo $noticiaId; ?>');
        }, 2000);
    </script>
</body>
</html>