<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $acao_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $responsavel_id = $_SESSION['usuario_id'];

    $sql = "DELETE FROM planos_acao WHERE id = ? AND responsavel_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $acao_id, $responsavel_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['mensagem_sucesso'] = "Ação deletada com sucesso!";
        } else {
            $_SESSION['mensagem_erro'] = "Ação não encontrada ou você não tem permissão para deletá-la.";
        }
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao deletar ação: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

header("Location: editar_plano.php");
exit();
?>
