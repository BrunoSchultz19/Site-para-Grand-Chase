<?php
class Banco {
    private $servidor = "DESKTOP-547SG9D\\SQLEXPRESS";
    private $usuario = "sa";
    private $senha = "976090310";
    private $banco = "gc";
    private $conexao;

    public function __construct() {
        try {
            $this->conexao = new PDO(
                "sqlsrv:Server={$this->servidor};Database={$this->banco};TrustServerCertificate=1", 
                $this->usuario, 
                $this->senha
            );
            $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }

    public function getConexao() {
        return $this->conexao;
    }
}

$banco = new Banco();
$pdo = $banco->getConexao();
?>