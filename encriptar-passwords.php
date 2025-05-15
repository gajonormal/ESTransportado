<?php
// encrypt_passwords.php
require_once 'basedados/basedados.h';

// Função para criar hash seguro da password
function createPasswordHash($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Obter todos os utilizadores
$query = "SELECT id_utilizador, password_hash FROM Utilizadores";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $current_password = $row['password_hash'];
        
        // Verificar se a password já está encriptada
        if (password_needs_rehash($current_password, PASSWORD_BCRYPT)) {
            // Encriptar a password
            $hashed_password = createPasswordHash($current_password);
            
            // Atualizar na base de dados
            $update_stmt = $conn->prepare("UPDATE Utilizadores SET password_hash = ? WHERE id_utilizador = ?");
            $update_stmt->bind_param("si", $hashed_password, $row['id_utilizador']);
            $update_stmt->execute();
            
            echo "Password atualizada para o utilizador ID: " . $row['id_utilizador'] . "<br>";
        }
    }
} else {
    echo "Nenhum utilizador encontrado na base de dados.";
}

echo "Processo de encriptação concluído.";
?>