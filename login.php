<?php
session_start();
require_once 'basedados/basedados.h';

// Configurações de segurança
define('MAX_TENTATIVAS', 5);
define('JANELA_TEMPO', 15 * 60);
define('TEMPO_BLOQUEIO', 30 * 60);
define('PASSWORD_HASH_COST', 12);

// Função para registrar tentativas de login
function registrarTentativa($conn, $email, $sucesso) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
    
    $stmt = $conn->prepare("INSERT INTO tentativas_login 
                          (email, ip_address, user_agent, sucesso) 
                          VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $email, $ip, $user_agent, $sucesso);
    return $stmt->execute();
}

// Função para verificar bloqueio por brute force
function verificarBloqueio($conn, $email) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $janela_tempo = JANELA_TEMPO;
    $max_tentativas = MAX_TENTATIVAS;
    
    // Verificar por IP
    $stmt = $conn->prepare("SELECT COUNT(*) as tentativas 
                          FROM tentativas_login 
                          WHERE ip_address = ? 
                          AND sucesso = 0 
                          AND data_tentativa > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->bind_param("si", $ip, $janela_tempo);
    $stmt->execute();
    $result = $stmt->get_result();
    $tentativas_ip = $result->fetch_assoc()['tentativas'];
    
    // Verificar por Email
    $stmt = $conn->prepare("SELECT COUNT(*) as tentativas 
                          FROM tentativas_login 
                          WHERE email = ? 
                          AND sucesso = 0 
                          AND data_tentativa > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->bind_param("si", $email, $janela_tempo);
    $stmt->execute();
    $result = $stmt->get_result();
    $tentativas_email = $result->fetch_assoc()['tentativas'];
    
    if ($tentativas_ip >= $max_tentativas || $tentativas_email >= $max_tentativas) {
        $stmt = $conn->prepare("SELECT MAX(data_tentativa) as ultima_tentativa 
                              FROM tentativas_login 
                              WHERE (ip_address = ? OR email = ?) 
                              AND sucesso = 0");
        $stmt->bind_param("ss", $ip, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $ultima_tentativa = strtotime($result->fetch_assoc()['ultima_tentativa']);
        
        $tempo_bloqueio = TEMPO_BLOQUEIO;
        return max(0, ($ultima_tentativa + $tempo_bloqueio) - time());
    }
    
    return 0;
}

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Verificar bloqueio por brute force
    $tempo_bloqueio = verificarBloqueio($conn, $email);
    if ($tempo_bloqueio > 0) {
        $minutos = ceil($tempo_bloqueio / 60);
        $_SESSION['error'] = "Muitas tentativas falhadas. Tente novamente em $minutos minutos.";
        header("Location: pagina-login.php");
        exit();
    }
    
    // Validar email institucional
    if (!preg_match('/@(ipcb\.pt|ipcbcampus\.pt)$/i', $email)) {
        registrarTentativa($conn, $email, 0);
        $_SESSION['error'] = "Por favor, utilize um email institucional (@ipcb.pt ou @ipcbcampus.pt).";
        header("Location: pagina-login.php");
        exit();
    }
    
    // Buscar usuário com verificação de banimento
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
        
        // Verificar banimento
        if ($user['banido']) {
            $stmt_ban = $conn->prepare("SELECT motivo, data_fim FROM Banimentos 
                                       WHERE id_utilizador = ? AND ativo = TRUE 
                                       AND NOW() BETWEEN data_inicio AND IFNULL(data_fim, NOW() + INTERVAL 1 DAY)
                                       ORDER BY data_fim DESC LIMIT 1");
            $stmt_ban->bind_param("i", $user['id_utilizador']);
            $stmt_ban->execute();
            $ban_result = $stmt_ban->get_result();
            $ban_info = $ban_result->fetch_assoc();
            
            $data_fim = date('d/m/Y H:i', strtotime($ban_info['data_fim']));
            $_SESSION['error'] = "Conta suspensa até $data_fim. Motivo: " . $ban_info['motivo'];
            registrarTentativa($conn, $email, 0);
            header("Location: pagina-login.php");
            exit();
        }
        
        // Verificar conta ativa
        if (!$user['conta_ativa']) {
            $_SESSION['error'] = "Conta desativada. Contacte o administrador.";
            registrarTentativa($conn, $email, 0);
            header("Location: pagina-login.php");
            exit();
        }
        
        // Verificar senha
        if (password_verify($password, $user['password_hash'])) {
            // Login bem-sucedido
            registrarTentativa($conn, $email, 1);
            
            // Atualizar hash se necessário
            if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST])) {
                $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
                $stmt = $conn->prepare("UPDATE Utilizadores SET password_hash = ? WHERE id_utilizador = ?");
                $stmt->bind_param("si", $newHash, $user['id_utilizador']);
                $stmt->execute();
            }
            
            // Criar sessão
            $_SESSION['user_id'] = $user['id_utilizador'];
            $_SESSION['user_type'] = $user['tipo'];
            $_SESSION['user_name'] = $user['nome_completo'];
            
            // Redirecionamento
            switch ($user['tipo']) {
                case 'admin': header("Location: pagina-admin.php"); break;
                case 'gestor': header("Location: pagina-gestor.php"); break;
                case 'aluno': header("Location: pagina-aluno.php"); break;
                default: header("Location: pagina_inicial.html");
            }
            exit();
        } else {
            // Senha incorreta
            registrarTentativa($conn, $email, 0);
            
            // Obter o número atual de tentativas falhadas
            $janela_tempo = JANELA_TEMPO;
            $stmt = $conn->prepare("SELECT COUNT(*) as tentativas 
                                  FROM tentativas_login 
                                  WHERE email = ? 
                                  AND sucesso = 0 
                                  AND data_tentativa > DATE_SUB(NOW(), INTERVAL ? SECOND)");
            $stmt->bind_param("si", $email, $janela_tempo);
            $stmt->execute();
            $result = $stmt->get_result();
            $tentativas_atual = $result->fetch_assoc()['tentativas'];
            
            // Calcular tentativas restantes
            $tentativas_restantes = MAX_TENTATIVAS - $tentativas_atual;
            
            // Verificar se atingiu o limite
            if ($tentativas_atual >= MAX_TENTATIVAS) {
                $tempo_bloqueio = TEMPO_BLOQUEIO;
                $minutos = ceil($tempo_bloqueio / 60);
                $_SESSION['error'] = "Muitas tentativas falhadas. Tente novamente em $minutos minutos.";
            } else {
                $_SESSION['error'] = "Credenciais inválidas. Tentativas restantes: $tentativas_restantes";
            }
            
            header("Location: pagina-login.php");
            exit();
        }
    } else {
        // Email não encontrado
        registrarTentativa($conn, $email, 0);
        $_SESSION['error'] = "Nenhuma conta encontrada com este email. Por favor, registe-se.";
        header("Location: pagina-login.php");
        exit();
    }
} else {
    header("Location: pagina-login.php");
    exit();
}
?>