<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit();
}

require_once 'basedados/basedados.h';

$user_id = $_SESSION['user_id'];
$id_reserva = $_POST['id_reserva'] ?? null;

if (!$id_reserva || !is_numeric($id_reserva)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID de reserva inválido']);
    exit();
}

// Verificar se a reserva pertence ao usuário
$verifica_query = "SELECT r.id_reserva 
                   FROM Reservas r
                   JOIN Passageiros p ON r.id_passageiro = p.id_passageiro
                   WHERE r.id_reserva = ? AND p.id_utilizador = ?";
$verifica_stmt = $conn->prepare($verifica_query);
$verifica_stmt->bind_param("ii", $id_reserva, $user_id);
$verifica_stmt->execute();
$verifica_result = $verifica_stmt->get_result();

if ($verifica_result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Reserva não encontrada ou não pertence ao usuário']);
    exit();
}

// Atualizar o estado para 'cancelado' (em vez de deletar, que é mais seguro)
$update_query = "UPDATE Reservas SET estado = 'cancelado' WHERE id_reserva = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("i", $id_reserva);

if ($update_stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Erro ao cancelar reserva']);
}

$conn->close();
?>