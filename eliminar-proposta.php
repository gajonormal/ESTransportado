<?php
session_start();
require_once 'basedados/basedados.h';

if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

$id_proposta = $_POST['id_proposta'] ?? null;

if (!$id_proposta || !is_numeric($id_proposta)) {
    $_SESSION['error_message'] = "ID de proposta inválido.";
    header("Location: listar-oferta.php");
    exit();
}

// Verificar se a proposta pertence ao utilizador
$query = "SELECT id_proposta FROM PropostasTransporte WHERE id_proposta = ? AND id_aluno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_proposta, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Proposta não encontrada ou não pertence ao utilizador.";
    header("Location: listar-oferta.php");
    exit();
}

// Atualizar o estado para 'inativo'
$update = "UPDATE PropostasTransporte SET estado = 'inativo' WHERE id_proposta = ?";
$update_stmt = $conn->prepare($update);
$update_stmt->bind_param("i", $id_proposta);

if ($update_stmt->execute()) {
    $_SESSION['success_message'] = "Proposta eliminada com sucesso!";
} else {
    $_SESSION['error_message'] = "Erro ao eliminar a proposta.";
}

header("Location: listar-oferta.php");
exit();
?>
