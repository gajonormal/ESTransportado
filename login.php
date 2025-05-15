<?php
session_start();
require_once 'basedados/basedados.h';

// Função para verificar token de lembrar
function verificarTokenLembrar($conn) {
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
        list($user_id, $token) = explode(':', $_COOKIE['remember_token'], 2);
        
        $stmt = $conn->prepare("SELECT id_utilizador, remember_token, tipo, nome_completo, conta_ativa 
                               FROM Utilizadores 
                               WHERE id_utilizador = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verificar se conta está ativa
            if (!$user['conta_ativa']) {
                setcookie('remember_token', '', time() - 3600, "/");
                return false;
            }
            
            // Verificar token
            if (password_verify($token, $user['remember_token'])) {
                $_SESSION['user_id'] = $user['id_utilizador'];
                $_SESSION['user_type'] = $user['tipo'];
                $_SESSION['user_name'] = $user['nome_completo'];
                
                return true;
            }
        }
        
        // Token inválido - apagar cookie
        setcookie('remember_token', '', time() - 3600, "/");
    }
    return false;
}

// Verificar token de lembrar antes de processar o login
if (verificarTokenLembrar($conn)) {
    // Redirecionar conforme o tipo de usuário
    switch ($_SESSION['user_type']) {
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
}

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validar email institucional
    if (!preg_match('/@(ipcb\.pt|ipcbcampus\.pt)$/i', $email)) {
        $_SESSION['error'] = "Por favor, utilize um email institucional (@ipcb.pt ou @ipcbcampus.pt).";
        header("Location: pagina-login.php");
        exit();
    }
    
    // Buscar usuário no banco de dados com verificação de banimento
    $stmt = $conn->prepare("SELECT 
                            u.id_utilizador, 
                            u.password_hash, 
                            u.tipo, 
                            u.conta_ativa, 
                            u.nome_completo,
                            (SELECT COUNT(*) FROM Banimentos b 
                             WHERE b.id_utilizador = u.id_utilizador 
                             AND b.ativo = TRUE 
                             AND NOW() BETWEEN b.data_inicio AND IFNULL(b.data_fim, NOW() + INTERVAL 1 DAY)) as banido
                           FROM Utilizadores u 
                           WHERE u.email_institucional = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificar se o usuário está banido
        if ($user['banido']) {
            // Obter detalhes do banimento para mostrar ao usuário
            $stmt_ban = $conn->prepare("SELECT motivo, data_fim FROM Banimentos 
                                       WHERE id_utilizador = ? AND ativo = TRUE 
                                       AND NOW() BETWEEN data_inicio AND IFNULL(data_fim, NOW() + INTERVAL 1 DAY)
                                       ORDER BY data_fim DESC LIMIT 1");
            $stmt_ban->bind_param("i", $user['id_utilizador']);
            $stmt_ban->execute();
            $ban_result = $stmt_ban->get_result();
            $ban_info = $ban_result->fetch_assoc();
            
            $data_fim = date('d/m/Y H:i', strtotime($ban_info['data_fim']));
            $_SESSION['error'] = "Sua conta está temporariamente suspensa até $data_fim. Motivo: " . $ban_info['motivo'];
            header("Location: pagina-login.php");
            exit();
        }
        
        // Verificar se a conta está ativa
        if (!$user['conta_ativa']) {
            $_SESSION['error'] = "Sua conta está desativada. Por favor, contacte o administrador.";
            header("Location: pagina-login.php");
            exit();
        }
        
        // Verificar a senha usando password_verify
        if (password_verify($password, $user['password_hash'])) {
            // Verificar se a password precisa de ser reencriptada
            if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
                $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $conn->prepare("UPDATE Utilizadores SET password_hash = ? WHERE id_utilizador = ?");
                $stmt->bind_param("si", $newHash, $user['id_utilizador']);
                $stmt->execute();
            }
            
            // Login bem-sucedido - criar sessão
            $_SESSION['user_id'] = $user['id_utilizador'];
            $_SESSION['user_type'] = $user['tipo'];
            $_SESSION['user_name'] = $user['nome_completo'];
            
            // Lembrar usuário se selecionado
            if (isset($_POST['remember'])) {
                $token = bin2hex(random_bytes(32));
                $hashed_token = password_hash($token, PASSWORD_BCRYPT);
                
                // Armazenar token na base de dados
                $stmt = $conn->prepare("UPDATE Utilizadores SET remember_token = ? WHERE id_utilizador = ?");
                $stmt->bind_param("si", $hashed_token, $user['id_utilizador']);
                $stmt->execute();
                
                // Definir cookie seguro (30 dias de validade)
                setcookie(
                    'remember_token', 
                    $user['id_utilizador'] . ':' . $token, 
                    time() + (86400 * 30), 
                    "/", 
                    "", 
                    true,  // Apenas HTTPS
                    true   // Acessível apenas por HTTP (não por JavaScript)
                );
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