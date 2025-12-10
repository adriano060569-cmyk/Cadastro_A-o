<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar_objetivo'])) {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($titulo)) {
        $_SESSION['mensagem_erro'] = "O título do objetivo é obrigatório.";
    } else {
        $sql = "INSERT INTO objetivos_estrategicos (titulo, descricao) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            $_SESSION['mensagem_erro'] = "Erro na preparação da consulta: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $titulo, $descricao);

            if ($stmt->execute()) {
                $_SESSION['mensagem_sucesso'] = "Objetivo estratégico cadastrado com sucesso!";
            } else {
                $_SESSION['mensagem_erro'] = "Erro ao cadastrar objetivo: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    $conn->close();
}

header("Location: dashboard.php"); 
exit();
?>
