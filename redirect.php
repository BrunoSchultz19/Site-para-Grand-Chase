<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
} else {
    header('Location: entrar.php');
}
exit;
?>