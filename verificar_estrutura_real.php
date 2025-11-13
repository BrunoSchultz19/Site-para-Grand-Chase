<?php
$servidor = "DESKTOP-547SG9D\\SQLEXPRESS";
$usuario = "sa";
$senha = "976090310";
$banco = "gc";

try {
    $pdo = new PDO("sqlsrv:Server=$servidor;Database=$banco;TrustServerCertificate=1", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Estrutura REAL da Tabela users</h2>";
    
    // Descobrir todas as colunas
    $sql = "
        SELECT 
            COLUMN_NAME,
            DATA_TYPE,
            IS_NULLABLE,
            CHARACTER_MAXIMUM_LENGTH
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'users'
        ORDER BY ORDINAL_POSITION
    ";
    
    $stmt = $pdo->query($sql);
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr style='background: #2c3e50; color: white;'>";
    echo "<th>Coluna</th><th>Tipo</th><th>Pode ser Nulo?</th><th>Tamanho Máx</th>";
    echo "</tr>";
    
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td><strong>{$coluna['COLUMN_NAME']}</strong></td>";
        echo "<td>{$coluna['DATA_TYPE']}</td>";
        echo "<td>{$coluna['IS_NULLABLE']}</td>";
        echo "<td>" . ($coluna['CHARACTER_MAXIMUM_LENGTH'] ? $coluna['CHARACTER_MAXIMUM_LENGTH'] : '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar alguns registros de exemplo
    echo "<h3>📋 Primeiros 3 registros (exemplo):</h3>";
    $sqlDados = "SELECT TOP 3 * FROM users";
    $stmtDados = $pdo->query($sqlDados);
    $registros = $stmtDados->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($registros) > 0) {
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr style='background: #34495e; color: white;'>";
        foreach (array_keys($registros[0]) as $coluna) {
            echo "<th>{$coluna}</th>";
        }
        echo "</tr>";
        
        foreach ($registros as $registro) {
            echo "<tr>";
            foreach ($registro as $valor) {
                echo "<td>" . ($valor !== null ? htmlspecialchars(substr($valor, 0, 30)) : 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>