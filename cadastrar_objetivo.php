<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Objetivo</title>
    <style>/* Seu CSS */</style>
</head>
<body>
    <h2>Cadastrar Novo Objetivo Estratégico</h2>
    <form action="processar_objetivo.php" method="POST">
        <div class="form-group">
            <label for="titulo">Título do Objetivo:</label>
            <input type="text" name="titulo" id="titulo" required>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição Detalhada:</label>
            <textarea name="descricao" id="descricao" rows="4"></textarea>
        </div>
        <button type="submit" name="cadastrar_objetivo">Cadastrar Objetivo</button>
    </form>
    <a href="dashboard.php">Voltar para o Dashboard</a>
</body>
</html>
