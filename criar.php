<!DOCTYPE html>
<html>
<head>
    <title>Criar Pacote</title>
</head>
<body>
    <h1>Criar Pacote de Viagem</h1>
    
    <?php
    require_once 'conexao.php';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $valor = $_POST['valor'];
        $origem = $_POST['origem'];
        $destino = $_POST['destino'];
        $duracao_dias = $_POST['duracao_dias'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO pacotes (valor, origem, destino, duracao_dias) VALUES (?, ?, ?, ?)");
            $stmt->execute([$valor, $origem, $destino, $duracao_dias]);
            header('Location: index.php?status=success');
            exit;
        } catch(PDOException $e) {
            echo '<p>Erro: ' . $e->getMessage() . '</p>';
        }
    }
    ?>
    
    <form method="POST">
        <p>
            <label>Valor:</label><br>
            <input type="number" name="valor" step="0.01" required>
        </p>
        <p>
            <label>Origem:</label><br>
            <input type="text" name="origem" required>
        </p>
        <p>
            <label>Destino:</label><br>
            <input type="text" name="destino" required>
        </p>
        <p>
            <label>Duração (dias):</label><br>
            <input type="number" name="duracao_dias" required>
        </p>
        <p>
            <button type="submit">Salvar</button>
            <a href="index.php">Cancelar</a>
        </p>
    </form>
</body>
</html>