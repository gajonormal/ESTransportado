<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

$userId = $_SESSION['user_id'];

// Buscar contagem de notificações não lidas
$sql = "SELECT COUNT(*) as count FROM Notificacoes WHERE id_utilizador = ? AND lida = FALSE";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => $row['count']]);

$stmt->close();
$conn->close();
?>