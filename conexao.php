<?php
$servername = "localhost";
$username = "root";       // Seu usuário do MySQL
$password = "";           // Sua senha do MySQL
$dbname = "plano_acao_db"; // O nome do banco de dados que você criou

// Tentativa de criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Define o charset para garantir acentuação correta
if (!$conn->set_charset("utf8mb4")) {
    error_log("Erro ao carregar o conjunto de caracteres utf8mb4: " . $conn->error);
}

// O script termina aqui e a variável $conn está pronta para ser usada nos outros arquivos.
?>
