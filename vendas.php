<!DOCTYPE html>
<html>
<head>
    <title>Gerenciamento de Vendas</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 5px; }
    </style>
</head>
<body>
    <h1>Gerenciamento de Vendas</h1>
    
    <?php
    require_once 'conexao.php';
    
    // Registrar nova venda
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $cliente_id = intval($_POST['cliente_id']);
        $pacote_id = intval($_POST['pacote_id']);
        
        if ($cliente_id <= 0 || $pacote_id <= 0) {
            echo '<p>Selecione cliente e pacote.</p>';
        } else {
            try {
                // Verificar se o pacote está disponível
                $stmt = $pdo->prepare("SELECT disponivel FROM pacotes WHERE id = ?");
                $stmt->execute([$pacote_id]);
                $pacote = $stmt->fetch();
                
                if (!$pacote || !$pacote['disponivel']) {
                    echo '<p>Este pacote não está disponível para compra.</p>';
                } else {
                    // Iniciar transação para garantir consistência
                    $pdo->beginTransaction();
                    
                    // Registrar a venda
                    $stmt = $pdo->prepare("INSERT INTO vendas (cliente_id, pacote_id) VALUES (?, ?)");
                    $stmt->execute([$cliente_id, $pacote_id]);
                    
                    // Marcar pacote como indisponível
                    $stmt = $pdo->prepare("UPDATE pacotes SET disponivel = FALSE WHERE id = ?");
                    $stmt->execute([$pacote_id]);
                    
                    $pdo->commit();
                    
                    echo '<p>Venda registrada com sucesso!</p>';
                }
            } catch(PDOException $e) {
                $pdo->rollBack();
                echo '<p>Erro: ' . $e->getMessage() . '</p>';
            }
        }
    }
    
    // Cancelar venda
    if (isset($_GET['cancelar'])) {
        $id = intval($_GET['cancelar']);
        try {
            // Iniciar transação
            $pdo->beginTransaction();
            
            // Obter o pacote
            $stmt = $pdo->prepare("SELECT pacote_id FROM vendas WHERE id = ?");
            $stmt->execute([$id]);
            $venda = $stmt->fetch();
            
            if ($venda) {
                // Marcar pacote como disponível novamente
                $stmt = $pdo->prepare("UPDATE pacotes SET disponivel = TRUE WHERE id = ?");
                $stmt->execute([$venda['pacote_id']]);
                
                // Remover a venda
                $stmt = $pdo->prepare("DELETE FROM vendas WHERE id = ?");
                $stmt->execute([$id]);
                
                $pdo->commit();
                echo '<p>Venda cancelada com sucesso!</p>';
            } else {
                $pdo->rollBack();
                echo '<p>Venda não encontrada.</p>';
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            echo '<p>Erro: ' . $e->getMessage() . '</p>';
        }
    }
    
    // Buscar clientes
    try {
        $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nome");
        $clientes = $stmt->fetchAll();
    } catch(PDOException $e) {
        echo '<p>Erro: ' . $e->getMessage() . '</p>';
        $clientes = [];
    }
    
    // Buscar pacotes disponíveis
    try {
        $stmt = $pdo->query("SELECT * FROM pacotes WHERE disponivel = TRUE");
        $pacotes = $stmt->fetchAll();
    } catch(PDOException $e) {
        echo '<p>Erro: ' . $e->getMessage() . '</p>';
        $pacotes = [];
    }
    
    // Listar vendas
    try {
        $stmt = $pdo->query("
            SELECT v.*, c.nome, c.cpf, p.origem, p.destino, p.valor
            FROM vendas v
            JOIN clientes c ON v.cliente_id = c.id
            JOIN pacotes p ON v.pacote_id = p.id
            ORDER BY v.data_compra DESC
        ");
        $vendas = $stmt->fetchAll();
    } catch(PDOException $e) {
        echo '<p>Erro: ' . $e->getMessage() . '</p>';
        $vendas = [];
    }
    ?>
    
    <div>
        <a href="index.php">Pacotes</a> | 
        <a href="clientes.php">Clientes</a>
    </div>
    
    <h2>Registrar Nova Venda</h2>
    <?php if (count($clientes) == 0): ?>
    <p>Cadastre clientes antes de registrar vendas. <a href="clientes.php">Cadastrar Clientes</a></p>
    <?php elseif (count($pacotes) == 0): ?>
    <p>Não há pacotes disponíveis para venda.</p>
    <?php else: ?>
    <form method="POST">
        <p>
            <label>Cliente:</label><br>
            <select name="cliente_id" required>
                <option value="">Selecione</option>
                <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo $cliente['id']; ?>">
                    <?php echo $cliente['nome']; ?> (CPF: <?php echo $cliente['cpf']; ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label>Pacote:</label><br>
            <select name="pacote_id" required>
                <option value="">Selecione</option>
                <?php foreach ($pacotes as $pacote): ?>
                <option value="<?php echo $pacote['id']; ?>">
                    <?php echo $pacote['origem']; ?> → <?php echo $pacote['destino']; ?> 
                    (R$ <?php echo $pacote['valor']; ?>, <?php echo $pacote['duracao_dias']; ?> dias)
                </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <button type="submit">Registrar Venda</button>
        </p>
    </form>
    <?php endif; ?>
    
    <h2>Vendas Registradas</h2>
    <?php if (count($vendas) > 0): ?>
    <table>
        <tr>
            <th>Cliente</th>
            <th>Pacote</th>
            <th>Valor</th>
            <th>Data da Compra</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($vendas as $venda): ?>
        <tr>
            <td><?php echo $venda['nome']; ?> (<?php echo $venda['cpf']; ?>)</td>
            <td><?php echo $venda['origem']; ?> → <?php echo $venda['destino']; ?></td>
            <td>R$ <?php echo $venda['valor']; ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($venda['data_compra'])); ?></td>
            <td>
                <a href="vendas.php?cancelar=<?php echo $venda['id']; ?>" 
                   onclick="return confirm('Cancelar esta venda? O pacote voltará a ficar disponível.')">
                    Cancelar
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <p>Nenhuma venda registrada.</p>
    <?php endif; ?>
</body>
</html>