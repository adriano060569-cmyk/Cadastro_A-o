<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar_acao'])) {
    // Sanitiza e valida inputs
    $objetivo_id = filter_input(INPUT_POST, 'objetivo_id', FILTER_SANITIZE_NUMBER_INT);
    $meta_id = filter_input(INPUT_POST, 'meta_id', FILTER_SANITIZE_NUMBER_INT);
    $acao_descricao = filter_input(INPUT_POST, 'acao_descricao', FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
    $responsavel_id = $_SESSION['usuario_id']; // ID do usuário logado

    // Lógica condicional para campos opcionais baseados no status
    $percentual = ($status == 'em_andamento' || $status == 'concluida') ? filter_input(INPUT_POST, 'percentual', FILTER_SANITIZE_NUMBER_INT) : 0;
    $justificativa = ($status == 'reprogramada' || $status == 'cancelada') ? filter_input(INPUT_POST, 'justificativa', FILTER_SANITIZE_SPECIAL_CHARS) : NULL;
    $data_repro = ($status == 'reprogramada') ? filter_input(INPUT_POST, 'data_repro', FILTER_SANITIZE_SPECIAL_CHARS) : NULL;

    // Inserir novo usuário
    $sql_insert = "INSERT INTO planos_acao (objetivo_id, meta_id, acao_descricao, status, percentual_execucao, justificativa_reprogramacao, data_reprogramacao, responsavel_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    
    // Tipos: i (int), i (int), s (string), s (string), i (int), s (string/NULL), s (string/NULL), i (int)
    $stmt_insert->bind_param("iississsi", 
        $objetivo_id, 
        $meta_id,
        $acao_descricao, 
        $status, 
        $percentual, 
        $justificativa, 
        $data_repro,
        $responsavel_id
    );

    if ($stmt_insert->execute()) {
        $_SESSION['mensagem_sucesso'] = "Ação cadastrada com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao cadastrar ação: " . $conn->error;
    }
    $stmt_insert->close();
    $conn->close();
}

header("Location: dashboard.php");
exit();
