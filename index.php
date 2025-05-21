<!DOCTYPE html>
<html>
<head>
    <title>Pacotes de Viagem</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 5px; }
        .indisponivel { background-color: #ffcccc; }
    </style>
</head>
<body>
    <h1>Pacotes de Viagem</h1>
    
    <?php
    require_once 'conexao.php';
    
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            echo '<p>Operação realizada com sucesso!</p>';
        } else {
            echo '<p>Erro na operação.</p>';
        }
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM pacotes");
        $pacotes = $stmt->fetchAll();
    } catch(PDOException $e) {
        echo '<p>Erro: ' . $e->getMessage() . '</p>';
        $pacotes = [];
    }
    ?>
    
    <div>
        <a href="criar.php">Novo Pacote</a> | 
        <a href="clientes.php">Clientes</a> | 
        <a href="vendas.php">Vendas</a>
    </div>
    
    <table>
        <tr>
            <th>Valor</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Duração</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($pacotes as $pacote): ?>
        <tr class="<?php echo isset($pacote['disponivel']) && !$pacote['disponivel'] ? 'indisponivel' : ''; ?>">
            <td>R$ <?php echo $pacote['valor']; ?></td>
            <td><?php echo $pacote['origem']; ?></td>
            <td><?php echo $pacote['destino']; ?></td>
            <td><?php echo $pacote['duracao_dias']; ?> dias</td>
            <td>
                <?php 
                if (!isset($pacote['disponivel']) || $pacote['disponivel']) {
                    echo 'Disponível';
                } else {
                    echo 'Vendido';
                } 
                ?>
            </td>
            <td>
                <?php if (!isset($pacote['disponivel']) || $pacote['disponivel']): ?>
                <a href="excluir.php?id=<?php echo $pacote['id']; ?>" 
                   onclick="return confirm('Confirmar exclusão?')">Excluir</a>
                <?php else: ?>
                <span title="Pacotes vendidos não podem ser excluídos">Excluir</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>