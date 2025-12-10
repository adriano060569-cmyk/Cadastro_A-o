<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit(); }

include 'conexao.php';

// 1. Buscar Objetivos Estratégicos para preencher o SELECT
$sql_objetivos = "SELECT id, titulo FROM objetivos_estrategicos ORDER BY titulo ASC";
$result_objetivos = $conn->query($sql_objetivos);
// 2. Buscar Metas para preencher o SELECT
$sql_metas = "SELECT id, titulo AS descricao FROM metas ORDER BY titulo ASC";

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Ação - Plano de Ação</title>
    <style>
        /* ... Seu CSS aqui ... */
    </style>
</head>
<body>
    <h2>Cadastrar Nova Ação para <?= htmlspecialchars($_SESSION['setor']); ?></h2>
    
    <form action="processar_acao.php" method="POST" onchange="handleStatusChange()">
        
        <div class="form-group">
            <label for="objetivo_id">Objetivo Estratégico:</label>
            <select name="objetivo_id" id="objetivo_id" required>
                <?php while($row = $result_objetivos->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['titulo']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="meta_id">Meta Associada:</label>
            <select name="meta_id" id="meta_id" required>
                 <?php while($row_meta = $result_metas->fetch_assoc()): ?>
                    <option value="<?= $row_meta['id'] ?>"><?= htmlspecialchars($row_meta['descricao']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="acao_descricao">Descrição da Ação:</label>
            <textarea name="acao_descricao" id="acao_descricao" rows="3" required placeholder="Descreva a ação a ser executada..."></textarea>
        </div>
        
        <div class="form-group">
            <label for="status">Status da Ação:</label>
            <select name="status" id="status">
                <option value="em_andamento">Em Andamento</option>
                <option value="concluida">Concluída</option>
                <option value="reprogramada">Reprogramada</option>
                <option value="planejada">Planejada</option>
            </select>
        </div>

        <div id="div_percentual" class="conditional-field">
            <label for="percentual">Percentual Executado (%):</label>
            <input type="number" name="percentual" id="percentual" min="0" max="100" value="0">
        </div>

        <div id="div_reprogramacao" class="conditional-field" style="display: none;">
            <label for="justificativa">Justificativa da Reprogramação:</label>
            <textarea name="justificativa" id="justificativa" rows="2"></textarea>
            <label for="data_repro">Nova Data:</label>
            <input type="date" name="data_repro" id="data_repro">
        </div>

        <div class="form-group">
            <button type="submit" name="cadastrar_acao">Cadastrar Ação</button>
        </div>
    </form>
 <a href="dashboard.php">Voltar para a Página Inicial</a>

<script>
    function handleStatusChange() {
        const status = document.getElementById('status').value;
        document.getElementById('div_percentual').style.display = 'none';
        document.getElementById('div_reprogramacao').style.display = 'none';

        if (status === 'em_andamento') {
            document.getElementById('div_percentual').style.display = 'block';
        } else if (status === 'reprogramada') {
            document.getElementById('div_reprogramacao').style.display = 'block';
        }
    }
    // Chamar na carga inicial para garantir o estado correto
    handleStatusChange(); 
</script>
</body>
</html>
