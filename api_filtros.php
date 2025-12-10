<?php
include 'conexao.php';
header('Content-Type: application/json');

$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$id_input = filter_input(INPUT_GET, 'id', FILTER_DEFAULT) ?? 0; 

$data = [];

if ($type === 'metas' && $id_input) {
    // AQUI VOCÊ ESTÁ FILTRANDO A PARTIR DA TABELA PLANOS_ACAO (o que é uma estrutura estranha, mas mantida para compatibilidade com seus códigos anteriores)
    $stmt = $conn->prepare("SELECT DISTINCT meta_id AS id, meta_descricao AS descricao FROM planos_acao WHERE objetivo_id = ?");
    $stmt->bind_param("i", $id_input);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $data[] = ['id' => $row['id'], 'descricao' => htmlspecialchars($row['descricao'])]; 
    }
    $stmt->close();
} elseif ($type === 'acoes' && $id_input) {
    // Buscar ações relacionadas a uma meta (usando meta_id NUMERICO agora)
    $stmt = $conn->prepare("SELECT id, acao_descricao AS descricao FROM planos_acao WHERE meta_id = ?");
    $stmt->bind_param("i", $id_input); // Bind como inteiro
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $data[] = ['id' => $row['id'], 'descricao' => htmlspecialchars($row['descricao'])];
    }
    $stmt->close();
} elseif ($type === 'detalhes' && $id_input) {
    // Buscar detalhes de uma ação específica
    $stmt = $conn->prepare("SELECT * FROM planos_acao WHERE id = ?");
    $stmt->bind_param("i", $id_input);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    if ($data) {
        $data = array_map('htmlspecialchars', $data);
    }
}

$conn->close();
echo json_encode($data);
?>
