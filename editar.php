<!DOCTYPE html>
<html>
<head>
    <title>Editar Pacote</title>
</head>
<body>
    <h1>Editar Pacote de Viagem</h1>
    
    <?php
    require_once 'conexao.php';
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        header('Location: index.php');
        exit;
    }
    
    // Buscar o pacote
    try {
        $stmt = $pdo->prepare("SELECT * FROM pacotes WHERE id = ?");
        $stmt->execute([$id]);
        $pacote = $stmt->fetch();
        
        if (!$pacote) {
            header('Location: index.php');
            exit;
        }
    } catch(PDOException $e) {
        echo '<p>Erro: ' . $e->getMessage() . '</p>';
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $valor = $_POST['valor'];
        $origem = $_POST['origem'];
        $destino = $_POST['destino'];
        $duracao_dias = $_POST['duracao_dias'];
        
        try {
            $stmt = $pdo->prepare("UPDATE pacotes SET valor = ?, origem = ?, destino = ?, duracao_dias = ? WHERE id = ?");
            $stmt->execute([$valor, $origem, $destino, $duracao_dias, $id]);
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
            <input type="number" name="valor" step="0.01" value="<?php echo $pacote['valor']; ?>" required>
        </p>
        <p>
            <label>Origem:</label><br>
            <input type="text" name="origem" value="<?php echo $pacote['origem']; ?>" required>
        </p>
        <p>
            <label>Destino:</label><br>
            <input type="text" name="destino" value="<?php echo $pacote['destino']; ?>" required>
        </p>
        <p>
            <label>Duração (dias):</label><br>
            <input type="number" name="duracao_dias" value="<?php echo $pacote['duracao_dias']; ?>" required>
        </p>
        <p>
            <button type="submit">Atualizar</button>
            <a href="index.php">Cancelar</a>
        </p>
    </form>
</body>
</html>