<?php
session_start();
include 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Verifica se o formulário correto foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar_tudo'])) {
    
    // Captura e Saneamento dos dados
    $objetivo_id = filter_input(INPUT_POST, 'objetivo_id', FILTER_SANITIZE_NUMBER_INT);
    $titulo_meta = filter_input(INPUT_POST, 'titulo_meta', FILTER_SANITIZE_SPECIAL_CHARS);
    $acoes_descricao = $_POST['acoes_descricao']; // Array de descrições
    $responsavel_id = $_SESSION['usuario_id'];

    // Validação básica
    if (empty($objetivo_id) || empty($titulo_meta) || empty($acoes_descricao)) {
        $_SESSION['mensagem_erro'] = "Erro: Preencha todos os campos obrigatórios, incluindo pelo menos uma ação.";
        header("Location: cadastrar_meta.php");
        exit();
    }

    // --- INICIAR TRANSAÇÃO ---
    $conn->begin_transaction();
    $sucesso_transacao = true;

    try {
        // 1. INSERIR NA TABELA 'meta'
        // Use 'titulo' e 'objetivo_id' conforme sua estrutura de DB
        $sql_meta = "INSERT INTO meta (titulo, objetivo_id) VALUES (?, ?)";
        $stmt_meta = $conn->prepare($sql_meta);
        $stmt_meta->bind_param("si", $titulo_meta, $objetivo_id);
        
        if (!$stmt_meta->execute()) {
            throw new Exception("Erro ao inserir a meta: " . $stmt_meta->error);
        }
        
        // Capturar o ID da meta que acabou de ser inserida
        $meta_id = $conn->insert_id;
        $stmt_meta->close();

        // 2. INSERIR NA TABELA 'planos_acao' PARA CADA AÇÃO
        $sql_acao = "INSERT INTO planos_acao (meta_id, objetivo_id, acao_descricao, responsavel_id, status) VALUES (?, ?, ?, ?, 'planejada')";
        $stmt_acao = $conn->prepare($sql_acao);
        
        foreach ($acoes_descricao as $descricao) {
            $descricao_saneada = filter_var($descricao, FILTER_SANITIZE_SPECIAL_CHARS);
            
            if (!empty($descricao_saneada)) {
                // Vincular e executar para cada ação
                $stmt_acao->bind_param("iisi", $meta_id, $objetivo_id, $descricao_saneada, $responsavel_id);
                if (!$stmt_acao->execute()) {
                    throw new Exception("Erro ao inserir uma ação: " . $stmt_acao->error);
                }
            }
        }
        
        $stmt_acao->close();

        // Se tudo ocorreu bem, comita (salva) a transação
        $conn->commit();
        $_SESSION['mensagem_sucesso'] = "Meta e todas as ações cadastradas com sucesso!";

    } catch (Exception $e) {
        // Se houver qualquer erro, reverte todas as operações
        $conn->rollback();
        $_SESSION['mensagem_erro'] = "Falha na operação: " . $e->getMessage();
    }
    
    $conn->close();

    header("Location: dashboard.php");
    exit();

} else {
    // Redireciona se o acesso não foi via submissão de formulário POST
    header("Location: dashboard.php");
    exit();
}
