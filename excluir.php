<?php
require_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: index.php?status=error');
    exit;
}

try {
    // Verificar se o pacote está disponível (não foi vendido)
    $stmt = $pdo->prepare("SELECT disponivel FROM pacotes WHERE id = ?");
    $stmt->execute([$id]);
    $pacote = $stmt->fetch();
    
    if (!$pacote) {
        header('Location: index.php?status=error');
        exit;
    }
    
    // Se o pacote já foi vendido, não permitir exclusão
    if (isset($pacote['disponivel']) && !$pacote['disponivel']) {
        header('Location: index.php?status=error');
        exit;
    }
    
    // Excluir o pacote
    $stmt = $pdo->prepare("DELETE FROM pacotes WHERE id = ?");
    $stmt->execute([$id]);
    
    // Redirecionar com sucesso
    header('Location: index.php?status=success');
    exit;
    
} catch(PDOException $e) {
    // Redirecionar com erro
    header('Location: index.php?status=error');
    exit;
}
?>