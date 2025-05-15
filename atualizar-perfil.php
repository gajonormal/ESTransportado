<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

require_once 'basedados/basedados.h';

$id = $_SESSION['user_id'];
$nome = trim($_POST['nome_completo'] ?? '');
$email = trim($_POST['email_institucional'] ?? '');
$data_nascimento = $_POST['data_nascimento'] ?? '';
$sexo = $_POST['sexo'] ?? '';

// Validações
$erros = [];

if (empty($nome)) {
    $erros[] = "O nome é obrigatório.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || 
    (!str_ends_with($email, '@ipcb.pt') && !str_ends_with($email, '@ipcbcampus.pt'))) {
    $erros[] = "O email deve ser válido e terminar em @ipcb.pt ou @ipcbcampus.pt.";
}

if (!in_array($sexo, ['masculino', 'feminino'])) {
    $erros[] = "Sexo inválido.";
}

if (!empty($data_nascimento) && !DateTime::createFromFormat('Y-m-d', $data_nascimento)) {
    $erros[] = "Data de nascimento inválida.";
}

if (!empty($erros)) {
    $mensagem = urlencode(implode(' ', $erros));
    header("Location: perfil.php?status=erro&mensagem=$mensagem");
    exit();
}

// Atualização
$stmt = $conn->prepare("UPDATE Utilizadores SET nome_completo = ?, email_institucional = ?, data_nascimento = ?, sexo = ? WHERE id_utilizador = ?");
$stmt->bind_param("ssssi", $nome, $email, $data_nascimento, $sexo, $id);

if ($stmt->execute()) {
    header("Location: perfil.php?status=sucesso&mensagem=" . urlencode("Perfil atualizado com sucesso."));
} else {
    header("Location: perfil.php?status=erro&mensagem=" . urlencode("Erro ao atualizar o perfil."));
}
exit();
