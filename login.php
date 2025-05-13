<?php
session_start();
require_once 'basedados/basedados.h'; // Arquivo com a conexão ao banco de dados

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validar email institucional
    if (!preg_match('/@(ipcb\.pt|ipcbcampus\.pt)$/i', $email)) {
        $_SESSION['error'] = "Por favor, utilize um email institucional (@ipcb.pt ou @ipcbcampus.pt).";
        header("Location: pagina-login.php");
        exit();
    }
    
    // Buscar usuário no banco de dados
    $stmt = $conn->prepare("SELECT id_utilizador, password_hash, tipo, conta_ativa, nome_completo FROM Utilizadores WHERE email_institucional = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificar se a conta está ativa
        if (!$user['conta_ativa']) {
            $_SESSION['error'] = "Sua conta está desativada. Por favor, contacte o administrador.";
            header("Location: pagina-login.php");
            exit();
        }
        
        // Verificar a senha (comparação direta pois não está hashada)
        if ($password === $user['password_hash']) {
            // Login bem-sucedido - criar sessão
            $_SESSION['user_id'] = $user['id_utilizador'];
            $_SESSION['user_type'] = $user['tipo'];
            $_SESSION['user_name'] = $user['nome_completo'];
            
            // Lembrar usuário se selecionado (modificado para não usar password_hash)
            if (isset($_POST['remember'])) {
                $cookie_value = base64_encode($email . ':' . $password);
                setcookie('remember_token', $cookie_value, time() + (86400 * 30), "/"); // 30 dias
            }
            
            // Redirecionar conforme o tipo de usuário
            switch ($user['tipo']) {
                case 'admin':
                    header("Location: pagina-admin.php");
                    break;
                case 'gestor':
                    header("Location: pagina-gestor.php");
                    break;
                case 'aluno':
                    header("Location: pagina-aluno.php");
                    break;
                default:
                    header("Location: pagina_inicial.html");
            }
            exit();
        } else {
            $_SESSION['error'] = "Credenciais inválidas. Por favor, tente novamente.";
            header("Location: pagina-login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Nenhuma conta encontrada com este email. Por favor, registe-se.";
        header("Location: pagina-login.php");
        exit();
    }
} else {
    header("Location: pagina-login.php");
    exit();
}
?>