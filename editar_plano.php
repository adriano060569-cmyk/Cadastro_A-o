<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$setor_id = $_SESSION['usuario_id'];
$acao_para_editar = null;
$mensagem_sucesso = '';
$mensagem_erro = '';

if (isset($_SESSION['mensagem_sucesso'])) { $mensagem_sucesso = $_SESSION['mensagem_sucesso']; unset($_SESSION['mensagem_sucesso']); }
if (isset($_SESSION['mensagem_erro'])) { $mensagem_erro = $_SESSION['mensagem_erro']; unset($_SESSION['mensagem_erro']); }

if (isset($_GET['id_acao_editar'])) {
    $id_acao = filter_input(INPUT_GET, 'id_acao_editar', FILTER_SANITIZE_NUMBER_INT);
    $sql_edit = "SELECT * FROM planos_acao WHERE id = ? AND responsavel_id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("ii", $id_acao, $setor_id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();

    if ($result_edit->num_rows == 1) {
        $acao_para_editar = $result_edit->fetch_assoc();
    } else {
        $mensagem_erro = "Ação não encontrada ou você não tem permissão para editá-la.";
    }
    $stmt_edit->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar_acao'])) {
    $id_acao_update = filter_input(INPUT_POST, 'acao_id', FILTER_SANITIZE_NUMBER_INT);
    // Nota: meta_descricao/objetivo_id não são editáveis nesta view, apenas status/progresso
    $acao_descricao = filter_input(INPUT_POST, 'acao_descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
    
    $percentual = ($status == 'em_andamento' || $status == 'concluida') ? filter_input(INPUT_POST, 'percentual', FILTER_SANITIZE_NUMBER_INT) : 0;
    $justificativa = ($status == 'reprogramada' || $status == 'cancelada') ? filter_input(INPUT_POST, 'justificativa', FILTER_SANITIZE_SPECIAL_CHARS) : NULL;
    $data_repro = ($status == 'reprogramada') ? filter_input(INPUT_POST, 'data_repro', FILTER_SANITIZE_SPECIAL_CHARS) : NULL;

    $sql_update = "UPDATE planos_acao SET acao_descricao=?, status=?, percentual_execucao=?, justificativa_reprogramacao=?, data_reprogramacao=? WHERE id=? AND responsavel_id=?";
    $stmt_update = $conn->prepare($sql_update);
    // Tipos: s, s, i, s, s, i, i
    $stmt_update->bind_param("ssissii", 
        $acao_descricao, 
        $status, 
        $percentual, 
        $justificativa, 
        $data_repro,
        $id_acao_update,
        $setor_id
    );

    if ($stmt_update->execute()) {
        $_SESSION['mensagem_sucesso'] = "Ação ID $id_acao_update atualizada com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao atualizar ação: " . $stmt_update->error;
    }
    $stmt_update->close();
    header("Location: editar_plano.php");
    exit();
}

// Listar ações do setor
$sql_acoes = "SELECT * FROM planos_acao WHERE responsavel_id = ? ORDER BY id DESC";
$stmt_acoes = $conn->prepare($sql_acoes);
$stmt_acoes->bind_param("i", $setor_id);
$stmt_acoes->execute();
$result_acoes = $stmt_acoes->get_result();
$stmt_acoes->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Plano de Ação</title>
    <!-- Inclua seus estilos CSS aqui -->
</head>
<body>
    <h2>Gerenciar Ações do Setor de <?= htmlspecialchars($_SESSION['setor']); ?></h2>
    <!-- Exibe mensagens de sucesso/erro -->
    <?php if ($mensagem_sucesso): ?><div class="success"><?= htmlspecialchars($mensagem_sucesso); ?></div><?php endif; ?>
    <?php if ($mensagem_erro): ?><div class="error"><?= htmlspecialchars($mensagem_erro); ?></div><?php endif; ?>

    <?php if ($acao_para_editar): ?>
    <div class="edit-form">
        <h3>Editando Ação ID <?= $acao_para_editar['id']; ?></h3>
        <form action="editar_plano.php" method="POST">
            <input type="hidden" name="acao_id" value="<?= $acao_para_editar['id']; ?>">
            
            <label for="acao_descricao">Ação:</label>
            <textarea name="acao_descricao" id="acao_descricao" rows="3" required><?= htmlspecialchars($acao_para_editar['acao_descricao']); ?></textarea><br>
            
            <label for="status">Status:</label>
            <select name="status" id="status" onchange="toggleCamposOpcionais()">
                <option value="planejada" <?= ($acao_para_editar['status'] == 'planejada') ? 'selected' : ''; ?>>Planejada</option>
                <option value="em_andamento" <?= ($acao_para_editar['status'] == 'em_andamento') ? 'selected' : ''; ?>>Em Andamento</option>
                <option value="concluida" <?= ($acao_para_editar['status'] == 'concluida') ? 'selected' : ''; ?>>Concluída</option>
                <option value="reprogramada" <?= ($acao_para_editar['status'] == 'reprogramada') ? 'selected' : ''; ?>>Reprogramada</option>
                <option value="cancelada" <?= ($acao_para_editar['status'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
            </select><br>

            <div id="div_percentual">
                <label for="percentual">Percentual de Execução (%):</label>
                <input type="number" name="percentual" id="percentual" min="0" max="100" value="<?= $acao_para_editar['percentual_execucao']; ?>">
            </div>

            <div id="div_reprogramacao">
                <label for="justificativa">Justificativa:</label>
                <textarea name="justificativa" id="justificativa" rows="2"><?= htmlspecialchars($acao_para_editar['justificativa_reprogramacao']); ?></textarea>
                <label for="data_repro">Nova Data de Conclusão:</label>
                <input type="date" name="data_repro" id="data_repro" value="<?= $acao_para_editar['data_reprogramacao']; ?>">
            </div>
            
            <button type="submit" name="atualizar_acao">Salvar Alterações</button>
        </form>
    </div>
    <?php endif; ?>

    <h3>Minhas Ações Cadastradas</h3>
    <table>
        <!-- Tabela de ações e JS toggleCamposOpcionais() aqui -->
    </table>
    
    <!-- Script JS para toggle de campos -->
    <script>
        function toggleCamposOpcionais() { /* ... implementação JS do toggle ... */ }
        document.addEventListener('DOMContentLoaded', (event) => { if (document.getElementById('status')) { toggleCamposOpcionais(); } });
    </script>
</body>
</html>
