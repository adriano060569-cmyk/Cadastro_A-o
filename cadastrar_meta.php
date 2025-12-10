<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

include 'conexao.php';

// 1. Buscar Objetivos Estratégicos para preencher o SELECT
$sql_objetivos = "SELECT id, titulo FROM objetivos_estrategicos ORDER BY titulo ASC";
$result_objetivos = $conn->query($sql_objetivos);

$conn->close();

// Captura mensagens de feedback da sessão (se houver)
$mensagem_sucesso = isset($_SESSION['mensagem_sucesso']) ? $_SESSION['mensagem_sucesso'] : '';
unset($_SESSION['mensagem_sucesso']);
$mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : '';
unset($_SESSION['mensagem_erro']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Metas e Ações</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .success { color: green; }
        .error { color: red; }
        .action-group { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; }
        .add-action-btn { background-color: #28a745; color: white; border: none; padding: 10px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Cadastrar Nova Meta e Ações Associadas</h2>

    <?php if ($mensagem_sucesso): ?><p class="success"><?= htmlspecialchars($mensagem_sucesso) ?></p><?php endif; ?>
    <?php if ($mensagem_erro): ?><p class="error"><?= htmlspecialchars($mensagem_erro) ?></p><?php endif; ?>
    
    <!-- O FORMULÁRIO APONTA PARA O NOVO ARQUIVO DE PROCESSAMENTO -->
    <form action="processar_meta_e_acoes.php" method="POST">
        
        <div class="form-group">
            <label for="objetivo_id">Objetivo Estratégico:</label>
            <select name="objetivo_id" id="objetivo_id" required>
                <option value="">Selecione um Objetivo</option>
                <?php while($row = $result_objetivos->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['titulo']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="titulo_meta">Título da Meta:</label>
            <input type="text" name="titulo_meta" id="titulo_meta" required placeholder="Descreva a meta a ser atingida...">
        </div>
        
        <!-- Área para Múltiplas Ações -->
        <h3>Ações</h3>
        <div id="acoes_container">
            <!-- Ações serão adicionadas aqui via JavaScript -->
            <div class="action-group" id="action_group_0">
                <label for="acao_descricao_0">Descrição da Ação 1:</label>
                <textarea name="acoes_descricao[]" id="acao_descricao_0" rows="3" required placeholder="Descreva a ação a ser executada..."></textarea>
            </div>
        </div>
        
        <button type="button" class="add-action-btn" onclick="addAcaoField()">+ Adicionar outra ação</button>

        <div class="form-group" style="margin-top: 20px;">
            <button type="submit" name="cadastrar_tudo">Cadastrar Meta e Ações</button>
        </div>
    </form>
    <a href="dashboard.php">Voltar para a Página Inicial</a>

    <script>
        let actionCount = 1;

        function addAcaoField() {
            const container = document.getElementById('acoes_container');
            const newActionGroup = document.createElement('div');
            newActionGroup.classList.add('action-group');
            newActionGroup.id = 'action_group_' + actionCount;
            
            newActionGroup.innerHTML = `
                <label for="acao_descricao_${actionCount}">Descrição da Ação ${actionCount + 1}:</label>
                <textarea name="acoes_descricao[]" id="acao_descricao_${actionCount}" rows="3" required placeholder="Descreva a ação a ser executada..."></textarea>
                <button type="button" onclick="removeAcaoField('${newActionGroup.id}')" style="background-color: #dc3545; color: white; border: none; padding: 5px; margin-top: 5px;">Remover</button>
            `;
            
            container.appendChild(newActionGroup);
            actionCount++;
        }

        function removeAcaoField(groupId) {
            const group = document.getElementById(groupId);
            if (group) {
                group.remove();
            }
        }
    </script>
</body>
</html>
