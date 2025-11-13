<?php
session_start();
require_once 'banco.php';

if (!isset($_SESSION['usuario_login'])) {
    header('Location: entrar.php');
    exit;
}

try {
    // Carrega dados do usuário
    $stmt = $pdo->prepare("SELECT Login, nome, email, firstLogin, sex, gamePoint, LoginUID FROM users WHERE Login = ?");
    $stmt->execute([$_SESSION['usuario_login']]);
    $usuario = $stmt->fetch();

    // Carrega personagens do usuário
    $stmtChars = $pdo->prepare("SELECT * FROM Characters WHERE Login = ?");
    $stmtChars->execute([$_SESSION['usuario_login']]);
    $personagens = $stmtChars->fetchAll(PDO::FETCH_ASSOC);

    // Carrega VCPoint do usuário
    $stmtVC = $pdo->prepare("SELECT vc.VCPoint 
                            FROM VCGAVirtualCash vc 
                            INNER JOIN users u ON vc.LoginUID = u.LoginUID 
                            WHERE u.Login = ?");
    $stmtVC->execute([$_SESSION['usuario_login']]);
    $vcPoint = $stmtVC->fetch();

    // Carrega itens do inventário do usuário
    $stmtItens = $pdo->prepare("SELECT ItemUID, LoginUID, ItemID FROM UIGAUserItem WHERE LoginUID = ?");
    $stmtItens->execute([$usuario['LoginUID']]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    // Carrega itens EQUIPADOS nos personagens (versão corrigida)
    $stmtEquip = $pdo->prepare("SELECT ue.* 
                               FROM UEGAUserEquipItem ue 
                               WHERE ue.LoginUID = ? AND ue.Deleted = 0");
    $stmtEquip->execute([$usuario['LoginUID']]);
    $itensEquipados = $stmtEquip->fetchAll(PDO::FETCH_ASSOC);

    // Carrega itens da loja (CashItemDisplayList)
    $stmtLoja = $pdo->prepare("SELECT * FROM itemmall.dbo.CashItemDisplayList");
    $stmtLoja->execute();
    $itensLoja = $stmtLoja->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// Função para traduzir nomes dos itens
function traduzirItem($itemName, $itemID) {
    $traducoes = [
        '解除魔珠封印的魔法書包[10個]' => 'Pacote de Livros Mágicos [10]',
        '解除魔珠封印的魔法書包[100個]' => 'Pacote de Livros Mágicos [100]',
        '解除魔珠封印的魔法書包[30個]' => 'Pacote de Livros Mágicos [30]',
        '解除魔珠封印的魔法書' => 'Livro Mágico',
        '楓葉妖精之戒指' => 'Anel da Fada de Bordo',
        '開啟技能樹' => 'Desbloquear Árvore de Habilidades',
        '卡巴羅斯套裝' => 'Conjunto Kabaros',
        '無辜的卡巴羅斯套裝' => 'Conjunto Kabaros Inocente',
        '無辜的卡巴羅斯手套' => 'Luvas Kabaros Inocentes',
        '無辜的卡巴羅斯披風' => 'Capa Kabaros Inocente',
        '小愛死神項鍊' => 'Colar do Deus da Morte',
        '假面天使面罩' => 'Máscara do Anjo Mascarado',
        '假面天使翅膀' => 'Asas do Anjo Mascarado',
        '假面天使護盾' => 'Escudo do Anjo Mascarado',
        'GC CLUB' => 'Clube GC',
        '小型背包(+30格)' => 'Mochila Pequena (+30)',
        '鑲嵌珠寶' => 'Jóias de Incrustação',
        '傑洛獲得任務【金】' => 'Missão do Zero [Ouro]',
        '傑恩獲得任務【金】' => 'Missão do Jin [Ouro]',
        '狄奧獲得任務【金】' => 'Missão do Dio [Ouro]',
        '屬性魔法書組合包[10個]' => 'Pacote de Magia [10]',
        '運動風海灘造型寶箱' => 'Baú Visual Praia Esportiva',
        '私立貴族學院造型寶箱' => 'Baú Visual Academia Nobre',
        'Jr.杜爾基本技能(500回)' => 'Habilidade Jr. Dull (500x)'
    ];
    
    return $traducoes[$itemName] ?? $itemName;
}

// Função para obter ícone baseado no tipo de item
function getIconeItem($itemID) {
    $icones = [
        // Livros e magias
        '102030' => '📖', '102050' => '📚', '103160' => '📚', '413320' => '📚',
        '508630' => '📚', '102030' => '📖',
        // Missões de personagens
        '508990' => '🎯', '508740' => '🎯', '508940' => '🎯',
        // Conjuntos e roupas
        '412550' => '👕', '526060' => '👕', '526100' => '🧤',
        // Acessórios
        '506030' => '💍', '526120' => '🧣', '645690' => '📿',
        '504550' => '🎭', '504560' => '🪽', '504580' => '🛡️',
        // Serviços
        '99600' => '🌳', '42340' => '🎫', '43500' => '🎒',
        // Baús e pacotes
        '640200' => '🎁', '684040' => '🎁',
        // Habilidades
        '663050' => '⚡',
        // Outros
        '287060' => '💎'
    ];
    
    return $icones[$itemID] ?? '📦';
}

include 'topo.php';
?>

<!-- Botão Voltar para o Index -->
<div style="text-align: center; margin: 20px 0;">
    <a href="index.php" class="btn" style="background: #9b59b6; display: inline-block; padding: 10px 20px; text-decoration: none; color: white; border-radius: 5px; font-weight: bold;">
        🏠 Voltar para o Início
    </a>
</div>

<!-- Conteúdo principal da tela (área central/esquerda) -->
<div style="min-height: 80vh; padding: 20px;">
    <h1 style="text-align: center; color: #2c3e50; margin-bottom: 30px;">🏰 Grand Chase Skarlat</h1>
    
    <div style="text-align: center; margin: 40px 0;">
        <p style="font-size: 18px; color: #7f8c8d;">Bem-vindo ao servidor Grand Chase Skarlat!</p>
        <p style="font-size: 16px; color: #7f8c8d;">Aqui você pode gerenciar sua conta e visualizar suas estatísticas.</p>
    </div>

    <!-- Botões -->
    <div style="text-align: center; margin: 30px 0; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
        <button id="btnInventario" class="btn" style="background: #e67e22; display: inline-block; padding: 12px 25px; text-decoration: none; color: white; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; font-size: 16px;">
            🎒 Inventário (<?php echo count($itens); ?> itens)
        </button>
        
        <button id="btnEquipamentos" class="btn" style="background: #3498db; display: inline-block; padding: 12px 25px; text-decoration: none; color: white; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; font-size: 16px;">
            ⚔️ Equipados (<?php echo count($itensEquipados); ?> itens)
        </button>

        <button id="btnLoja" class="btn" style="background: #e74c3c; display: inline-block; padding: 12px 25px; text-decoration: none; color: white; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; font-size: 16px;">
            🛍️ Loja (<?php echo count($itensLoja); ?> itens)
        </button>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <p>🎉 Login realizado com sucesso!</p>
        <p>✅ Email com capacidade ampliada</p>
        <p>🗃️ Campo email: <strong>NVARCHAR(150)</strong></p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="https://www.mediafire.com/file/zfb3vawbpm2szh2/Grand+Chase+Skarlat+6.0.rar/file" 
           target="_blank" 
           class="btn" 
           style="background: #27ae60; display: inline-block; width: auto; padding: 10px 20px; margin: 0 10px; text-decoration: none; color: white; border-radius: 5px;">
            📥 Download Grand Chase Skarlat 6.0
        </a>
    </div>
</div>

<!-- Modal do Inventário -->
<div id="modalInventario" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center;">
    <div id="conteudoInventario" style="background: white; width: 90%; max-width: 800px; max-height: 80vh; border-radius: 10px; padding: 20px; position: relative; transform: scale(0.8); opacity: 0; transition: all 0.3s ease;">
        <!-- Botão Fechar -->
        <button id="fecharInventario" style="position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #7f8c8d;">&times;</button>
        
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">🎒 Meu Inventário</h2>
        
        <!-- Lista de Itens -->
        <div style="max-height: 60vh; overflow-y: auto;">
            <?php if (!empty($itens)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                    <?php foreach ($itens as $item): ?>
                        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; background: #f8f9fa; text-align: center;">
                            <div style="font-size: 24px; margin-bottom: 10px;">
                                <?php 
                                // Ícone baseado no tipo de item (simulação)
                                $itemIcons = [
                                    '1' => '⚔️', '2' => '🛡️', '3' => '🏹', '4' => '📿', 
                                    '5' => '👕', '6' => '👖', '7' => '👟', '8' => '💍',
                                    '9' => '📦', '10' => '🧪', '11' => '📜', '12' => '💎'
                                ];
                                $icon = $itemIcons[$item['ItemID'] % 12] ?? '📦';
                                echo $icon;
                                ?>
                            </div>
                            <div style="font-weight: bold; color: #2c3e50; margin-bottom: 5px;">
                                Item #<?php echo htmlspecialchars($item['ItemID']); ?>
                            </div>
                            <div style="font-size: 12px; color: #7f8c8d;">
                                <div>ItemUID: <?php echo htmlspecialchars($item['ItemUID']); ?></div>
                                <div>LoginUID: <?php echo htmlspecialchars($item['LoginUID']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">📭</div>
                    <h3 style="color: #7f8c8d; margin-bottom: 10px;">Inventário Vazio</h3>
                    <p style="color: #95a5a6;">Você não possui itens no inventário.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Estatísticas do Inventário -->
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-around; text-align: center;">
                <div>
                    <div style="font-size: 24px; color: #3498db;"><?php echo count($itens); ?></div>
                    <div style="font-size: 12px; color: #7f8c8d;">Total de Itens</div>
                </div>
                <div>
                    <div style="font-size: 24px; color: #27ae60;"><?php 
                        $tiposItens = array_unique(array_column($itens, 'ItemID'));
                        echo count($tiposItens);
                    ?></div>
                    <div style="font-size: 12px; color: #7f8c8d;">Tipos Diferentes</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal dos Equipamentos -->
<div id="modalEquipamentos" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center;">
    <div id="conteudoEquipamentos" style="background: white; width: 90%; max-width: 900px; max-height: 80vh; border-radius: 10px; padding: 20px; position: relative; transform: scale(0.8); opacity: 0; transition: all 0.3s ease;">
        <!-- Botão Fechar -->
        <button id="fecharEquipamentos" style="position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #7f8c8d;">&times;</button>
        
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">⚔️ Itens Equipados</h2>
        
        <!-- Lista de Itens Equipados -->
        <div style="max-height: 60vh; overflow-y: auto;">
            <?php if (!empty($itensEquipados)): ?>
                <div style="display: grid; gap: 15px;">
                    <?php foreach ($itensEquipados as $equip): ?>
                        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="font-size: 32px;">
                                        <?php 
                                        // Ícone baseado no ItemOrderNo (posição do equipamento)
                                        $slotIcons = [
                                            '0' => '⚔️', '1' => '🛡️', '2' => '👕', '3' => '👖',
                                            '4' => '👟', '5' => '💍', '6' => '📿', '7' => '👑'
                                        ];
                                        $slotIcon = $slotIcons[$equip['ItemOrderNo']] ?? '📦';
                                        echo $slotIcon;
                                        ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: bold; color: #2c3e50; font-size: 16px;">
                                            Item #<?php echo htmlspecialchars($equip['ItemID']); ?>
                                        </div>
                                        <div style="font-size: 14px; color: #7f8c8d;">
                                            <strong>Slot:</strong> <?php echo htmlspecialchars($equip['ItemOrderNo']); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #95a5a6;">
                                            ItemUID: <?php echo htmlspecialchars($equip['ItemUID']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 12px; color: #27ae60; font-weight: bold;">
                                        ✅ Equipado
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">🛡️</div>
                    <h3 style="color: #7f8c8d; margin-bottom: 10px;">Nenhum Item Equipado</h3>
                    <p style="color: #95a5a6;">Seus personagens não possuem itens equipados.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Estatísticas dos Equipamentos -->
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-around; text-align: center;">
                <div>
                    <div style="font-size: 24px; color: #3498db;"><?php echo count($itensEquipados); ?></div>
                    <div style="font-size: 12px; color: #7f8c8d;">Itens Equipados</div>
                </div>
                <div>
                    <div style="font-size: 24px; color: #27ae60;"><?php 
                        $slotsOcupados = array_unique(array_column($itensEquipados, 'ItemOrderNo'));
                        echo count($slotsOcupados);
                    ?></div>
                    <div style="font-size: 12px; color: #7f8c8d;">Slots Ocupados</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal da Loja -->
<div id="modalLoja" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 2000; justify-content: center; align-items: center;">
    <div id="conteudoLoja" style="background: white; width: 95%; max-width: 1200px; max-height: 90vh; border-radius: 15px; padding: 25px; position: relative; transform: scale(0.8); opacity: 0; transition: all 0.3s ease;">
        <!-- Botão Fechar -->
        <button id="fecharLoja" style="position: absolute; top: 15px; right: 20px; background: none; border: none; font-size: 28px; cursor: pointer; color: #7f8c8d; z-index: 2001;">&times;</button>
        
        <!-- Cabeçalho da Loja -->
        <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="color: #2c3e50; margin-bottom: 10px;">🛍️ Loja Grand Chase</h2>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; display: inline-block;">
                <strong>Seu Saldo:</strong> 
                <span style="color: #27ae60; font-weight: bold; font-size: 18px;">
                    <?php echo number_format($vcPoint['VCPoint'] ?? 0); ?> CASH
                </span>
            </div>
        </div>
        
        <!-- Filtros -->
        <div style="margin-bottom: 20px; text-align: center;">
            <button class="filtro-btn active" data-categoria="todos">Todos</button>
            <button class="filtro-btn" data-categoria="livros">📚 Livros</button>
            <button class="filtro-btn" data-categoria="conjuntos">👕 Conjuntos</button>
            <button class="filtro-btn" data-categoria="acessorios">💎 Acessórios</button>
            <button class="filtro-btn" data-categoria="missoes">🎯 Missões</button>
            <button class="filtro-btn" data-categoria="servicos">🛠️ Serviços</button>
        </div>
        
        <!-- Lista de Itens da Loja -->
        <div style="max-height: 65vh; overflow-y: auto;">
            <?php if (!empty($itensLoja)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                    <?php foreach ($itensLoja as $item): 
                        $nomeTraduzido = traduzirItem($item['ItemName'], $item['ItemID']);
                        $icone = getIconeItem($item['ItemID']);
                        $preco = $item['Price'];
                        $quantidade = $item['Factor'] > 0 ? $item['Factor'] : 1;
                    ?>
                        <div class="item-loja" 
                             style="border: 2px solid #e0e0e0; border-radius: 12px; padding: 20px; background: white; transition: all 0.3s ease; cursor: pointer;"
                             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.1)';"
                             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';"
                             data-categoria="<?php 
                                 if (in_array($item['ItemID'], ['102030','102050','103160','413320','508630'])) echo 'livros';
                                 elseif (in_array($item['ItemID'], ['412550','526060','526100','526120'])) echo 'conjuntos';
                                 elseif (in_array($item['ItemID'], ['506030','645690','504550','504560','504580'])) echo 'acessorios';
                                 elseif (in_array($item['ItemID'], ['508990','508740','508940'])) echo 'missoes';
                                 elseif (in_array($item['ItemID'], ['99600','42340','43500','287060'])) echo 'servicos';
                                 else echo 'outros';
                             ?>">
                            <div style="text-align: center;">
                                <div style="font-size: 40px; margin-bottom: 15px;">
                                    <?php echo $icone; ?>
                                </div>
                                <div style="font-weight: bold; color: #2c3e50; margin-bottom: 10px; font-size: 16px; min-height: 40px;">
                                    <?php echo htmlspecialchars($nomeTraduzido); ?>
                                </div>
                                
                                <?php if ($quantidade > 1): ?>
                                    <div style="background: #3498db; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; display: inline-block; margin-bottom: 8px;">
                                        x<?php echo $quantidade; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="font-size: 20px; color: #e74c3c; font-weight: bold; margin: 10px 0;">
                                    💰 <?php echo number_format($preco); ?>
                                </div>
                                
                                <div style="font-size: 12px; color: #7f8c8d;">
                                    <div>ID: <?php echo htmlspecialchars($item['ItemID']); ?></div>
                                    <div>UID: <?php echo htmlspecialchars($item['ItemUID']); ?></div>
                                </div>
                                
                                <button style="background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; margin-top: 10px; font-weight: bold; width: 100%;">
                                    Comprar Agora
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 60px; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 64px; margin-bottom: 20px;">😔</div>
                    <h3 style="color: #7f8c8d; margin-bottom: 10px;">Loja Vazia</h3>
                    <p style="color: #95a5a6;">Nenhum item disponível no momento.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Estatísticas da Loja -->
        <div style="margin-top: 25px; padding-top: 20px; border-top: 2px solid #ecf0f1;">
            <div style="display: flex; justify-content: space-around; text-align: center;">
                <div>
                    <div style="font-size: 24px; color: #3498db;"><?php echo count($itensLoja); ?></div>
                    <div style="font-size: 14px; color: #7f8c8d;">Total de Itens</div>
                </div>
                <div>
                    <div style="font-size: 24px; color: #27ae60;"><?php 
                        $itensUnicos = array_unique(array_column($itensLoja, 'ItemID'));
                        echo count($itensUnicos);
                    ?></div>
                    <div style="font-size: 14px; color: #7f8c8d;">Tipos Diferentes</div>
                </div>
                <div>
                    <div style="font-size: 24px; color: #e74c3c;"><?php 
                        $precoTotal = array_sum(array_column($itensLoja, 'Price'));
                        echo number_format($precoTotal);
                    ?></div>
                    <div style="font-size: 14px; color: #7f8c8d;">CASH Total</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Painel do usuário FIXO no canto direito -->
<div style="position: fixed; top: 0; right: 0; width: 350px; height: 100vh; background: white; padding: 20px; border-left: 2px solid #3498db; box-shadow: -4px 0 15px rgba(0,0,0,0.1); z-index: 1000; overflow-y: auto;">
    <div class="user-info">
        <h3 style="margin-top: 0;">👋 Olá, <?php echo htmlspecialchars($usuario['nome']); ?>!</h3>
        <p><strong>Login:</strong> <?php echo htmlspecialchars($usuario['Login']); ?></p>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
        <p><strong>Sexo:</strong> 
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
        </p>
        <p><strong>Game Points:</strong> <?php echo number_format($usuario['gamePoint'] ?? 0); ?></p>
        <p><strong>CASH:</strong> 
            <?php if (file_exists('icon/cash.png')): ?>
                <img src="icon/cash.png" alt="CASH" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;">
            <?php else: ?>
                🪙
            <?php endif; ?>
            <?php echo number_format($vcPoint['VCPoint'] ?? 0); ?>
        </p>
        <p><strong>Data de Cadastro:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['firstLogin'])); ?></p>
    </div>

    <h4 style="text-align: center; margin: 20px 0; color: #2c3e50;">🎮 Meus Personagens</h4>

    <!-- Seção de Personagens -->
    <?php if (!empty($personagens)): ?>
    <div style="max-height: 50vh; overflow-y: auto;">
        <div style="display: grid; gap: 10px;">
            <?php foreach ($personagens as $personagem): ?>
                <?php 
                // Tenta encontrar as colunas automaticamente
                $charType = null;
                $exp = null;
                $level = null;
                $win = null;
                $lose = null;
                
                // Procura pelas colunas baseado nos nomes comuns
                foreach ($personagem as $key => $value) {
                    if (strpos(strtolower($key), 'chartype') !== false) $charType = $value;
                    if (strpos(strtolower($key), 'exp') !== false) $exp = $value;
                    if (strpos(strtolower($key), 'level') !== false) $level = $value;
                    if (strpos(strtolower($key), 'win') !== false) $win = $value;
                    if (strpos(strtolower($key), 'lose') !== false) $lose = $value;
                }
                
                // Se não encontrou pelos nomes, tenta pelas posições numéricas
                if ($charType === null) $charType = $personagem[1] ?? $personagem['Column1'] ?? 0;
                if ($exp === null) $exp = $personagem[3] ?? $personagem['Column3'] ?? 0;
                if ($level === null) $level = $personagem[4] ?? $personagem['Column4'] ?? 1;
                if ($win === null) $win = $personagem[5] ?? $personagem['Column5'] ?? 0;
                if ($lose === null) $lose = $personagem[6] ?? $personagem['Column6'] ?? 0;
                
                $totalBatalhas = $win + $lose;
                $winRate = $totalBatalhas > 0 ? ($win / $totalBatalhas) * 100 : 0;
                
                // Mapeia CharType para nomes dos personagens e ícones
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
                    12 => ['nome' => 'Ley', 'icone' => 'icon/ley.png'],
                    13 => ['nome' => 'Rufus', 'icone' => 'icon/rufus.png'],
                    14 => ['nome' => 'Rin', 'icone' => 'icon/rin.png'],
                    15 => ['nome' => 'Asin', 'icone' => 'icon/asin.png'],
                    16 => ['nome' => 'Lime', 'icone' => 'icon/lime.png'],
                    17 => ['nome' => 'Edel', 'icone' => 'icon/edel.png'],
                    18 => ['nome' => 'Veigas', 'icone' => 'icon/veigas.png'],
                    19 => ['nome' => 'Uno', 'icone' => 'icon/uno.png']
                ];
                
                $dadosPersonagem = $charTypes[$charType] ?? ['nome' => 'Personagem ' . $charType, 'icone' => 'icon/default.png'];
                $nomePersonagem = $dadosPersonagem['nome'];
                $iconePersonagem = $dadosPersonagem['icone'];
                ?>
                <div style="border: 1px solid #ddd; border-radius: 6px; padding: 10px; background: #f9f9f9;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <?php if (file_exists($iconePersonagem)): ?>
                            <img src="<?php echo $iconePersonagem; ?>" alt="<?php echo $nomePersonagem; ?>" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 2px solid #3498db;">
                        <?php else: ?>
                            <div style="width: 30px; height: 30px; background: #bdc3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #7f8c8d; border: 2px solid #95a5a6;">
                                🎮
                            </div>
                        <?php endif; ?>
                        <div>
                            <strong style="color: #2c3e50;"><?php echo htmlspecialchars($nomePersonagem); ?></strong>
                            <div style="font-size: 12px; color: #7f8c8d;">
                                Nível: <strong style="color: #e74c3c;"><?php echo $level; ?></strong> | 
                                Win Rate: <strong style="color: #3498db;"><?php echo number_format($winRate, 1); ?>%</strong>
                            </div>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; font-size: 11px;">
                        <div>Exp: <?php echo number_format($exp); ?></div>
                        <div>Vitórias: <?php echo number_format($win); ?></div>
                        <div>Derrotas: <?php echo number_format($lose); ?></div>
                        <div>Total: <?php echo number_format($totalBatalhas); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
        <p style="color: #856404; margin: 0; font-size: 12px;">❌ Nenhum personagem encontrado</p>
    </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ecf0f1;">
        <a href="sair.php" class="btn" style="background: #e74c3c; display: block; padding: 10px; text-decoration: none; color: white; border-radius: 5px; font-size: 14px;">
            🚪 Sair do Sistema
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Função genérica para abrir/fechar modais
    function setupModal(btnId, modalId, conteudoId, fecharId) {
        const btn = document.getElementById(btnId);
        const modal = document.getElementById(modalId);
        const conteudo = document.getElementById(conteudoId);
        const fechar = document.getElementById(fecharId);

        // Abrir modal
        btn.addEventListener('click', function() {
            modal.style.display = 'flex';
            setTimeout(() => {
                conteudo.style.transform = 'scale(1)';
                conteudo.style.opacity = '1';
            }, 10);
        });

        // Fechar modal
        fechar.addEventListener('click', function() {
            conteudo.style.transform = 'scale(0.8)';
            conteudo.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        });

        // Fechar clicando fora
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                conteudo.style.transform = 'scale(0.8)';
                conteudo.style.opacity = '0';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        });
    }

    // Configurar os modais
    setupModal('btnInventario', 'modalInventario', 'conteudoInventario', 'fecharInventario');
    setupModal('btnEquipamentos', 'modalEquipamentos', 'conteudoEquipamentos', 'fecharEquipamentos');
    setupModal('btnLoja', 'modalLoja', 'conteudoLoja', 'fecharLoja');

    // Filtros da loja
    const filtros = document.querySelectorAll('.filtro-btn');
    const itens = document.querySelectorAll('.item-loja');

    filtros.forEach(filtro => {
        filtro.addEventListener('click', function() {
            // Remove active de todos
            filtros.forEach(f => f.classList.remove('active'));
            // Adiciona active no clicado
            this.classList.add('active');
            
            const categoria = this.getAttribute('data-categoria');
            
            itens.forEach(item => {
                if (categoria === 'todos' || item.getAttribute('data-categoria') === categoria) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Fechar com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = ['modalInventario', 'modalEquipamentos', 'modalLoja'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                const conteudo = document.getElementById('conteudo' + modalId.replace('modal', ''));
                if (modal.style.display === 'flex') {
                    conteudo.style.transform = 'scale(0.8)';
                    conteudo.style.opacity = '0';
                    setTimeout(() => {
                        modal.style.display = 'none';
                    }, 300);
                }
            });
        }
    });
});

// Estilo para botões de filtro ativos
const style = document.createElement('style');
style.textContent = `
    .filtro-btn {
        background: #ecf0f1;
        border: none;
        padding: 8px 16px;
        margin: 0 5px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .filtro-btn.active {
        background: #3498db;
        color: white;
    }
    .filtro-btn:hover {
        background: #bdc3c7;
    }
`;
document.head.appendChild(style);
</script>

<?php include 'rodape.php'; ?>