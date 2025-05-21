<!DOCTYPE html>
<html>
<head>
    <title>Gerenciamento de Clientes</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 5px; }
    </style>
</head>
<body>
    <h1>Gerenciamento de Clientes</h1>
    
    <?php
    require_once 'conexao.php';

    // Função para formatar CPF no padrão 000.000.000-00
    function formatarCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '\1.\2.\3-\4', $cpf);
    }

    // Cadastrar novo cliente
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');

        if ($nome === '' || $cpf === '') {
            echo '<p>Todos os campos são obrigatórios.</p>';
        } else {
            try {
                // Verificar se CPF já existe
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE cpf = ?");
                $stmt->execute([$cpf]);

                if ($stmt->fetchColumn() > 0) {
                    echo '<p>Este CPF já está cadastrado.</p>';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO clientes (nome, cpf) VALUES (?, ?)");
                    $stmt->execute([$nome, $cpf]);
                    echo '<p>Cliente cadastrado com sucesso!</p>';
                }
            } catch (PDOException $e) {
                echo '<p>Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        }
    }

    // Excluir cliente
    if (isset($_GET['excluir'])) {
        $id = (int) $_GET['excluir'];

        try {
            // Verificar se cliente tem compras
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendas WHERE cliente_id = ?");
            $stmt->execute([$id]);

            if ($stmt->fetchColumn() > 0) {
                echo '<p>Este cliente já realizou compras e não pode ser excluído.</p>';
            } else {
                $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
                $stmt->execute([$id]);
                echo '<p>Cliente excluído com sucesso!</p>';
            }
        } catch (PDOException $e) {
            echo '<p>Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }

    // Listar clientes
    try {
        $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nome");
        $clientes = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo '<p>Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
        $clientes = [];
    }
    ?>

    <div>
        <a href="index.php">Pacotes</a> | 
        <a href="vendas.php">Vendas</a>
    </div>
    
    <h2>Novo Cliente</h2>
    <form method="POST">
        <p>
            <label>Nome:</label><br>
            <input type="text" name="nome" required>
        </p>
        <p>
            <label>CPF:</label><br>
            <input type="text" name="cpf" required pattern="\d{11}" title="Digite um CPF válido com 11 dígitos">
        </p>
        <p>
            <button type="submit">Cadastrar</button>
        </p>
    </form>
    
    <h2>Clientes Cadastrados</h2>
    <?php if (!empty($clientes)): ?>
    <table>
        <tr>
            <th>Nome</th>
            <th>CPF</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($clientes as $cliente): ?>
        <tr>
            <td><?= htmlspecialchars($cliente['nome']) ?></td>
            <td><?= htmlspecialchars(formatarCPF($cliente['cpf'])) ?></td>
            <td>
                <a href="clientes.php?excluir=<?= htmlspecialchars($cliente['id']) ?>" 
                   onclick="return confirm('Confirmar exclusão?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <p>Nenhum cliente cadastrado.</p>
    <?php endif; ?>
</body>
</html>