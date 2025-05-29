<?php
session_start();

// Determinar a página inicial com base no tipo de usuário (se estiver logado)
$pagina_inicial = 'pagina-inicial.php'; // padrão para não logados

if (isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'admin':
            $pagina_inicial = 'pagina-admin.php';
            break;
        case 'gestor':
            $pagina_inicial = 'pagina-gestor.php';
            break;
        case 'aluno':
            $pagina_inicial = 'pagina-aluno.php';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESTransportado - Ajuda e Suporte</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>

<body>
    <header>
    <a href="pagina-aluno.php" class="logo">
        <img src="imagens/logo.png" alt="ESTransportado">
    </a>

    <ul class="navbar">
        <li><a href="as-minhas-reservas.php">As minhas reservas</a></li>
        <li><a href="consultar-horarios.php">Consultar horarios</a></li>
        <li><a href="ajuda.php">Ajuda</a></li>
    </ul>

    <!-- Botão de Notificações com contador -->
    <a href="notificacoes.php" class="notification-button">
        <i class="bell-icon">🔔</i>
        <span id="notification-count" class="notification-count">0</span>
    </a>

    <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>

    <style>
    /* Estilo para o botão de notificações */
    .notification-button {
        position: relative;
        display: inline-block;
        padding: 8px 12px;
        margin-right: 15px;
        color: #333;
        text-decoration: none;
        font-size: 1.2em;
    }

    .notification-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: red;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        display: none;
    }
    </style>

    <script>
    // Função para carregar o contador de notificações não lidas
    function loadNotificationCount() {
        fetch('get_notification_count.php')
            .then(response => response.json())
            .then(data => {
                const notificationCount = document.getElementById('notification-count');
                if (data.count > 0) {
                    notificationCount.textContent = data.count;
                    notificationCount.style.display = 'block';
                } else {
                    notificationCount.style.display = 'none';
                }
            });
    }

    // Carregar contador quando a página é carregada
    document.addEventListener('DOMContentLoaded', function() {
        loadNotificationCount();
        
        // Atualizar contador periodicamente (a cada 30 segundos)
        setInterval(loadNotificationCount, 30000);
    });
    </script>
    </header>

    <div class="container">
        <h1>Ajuda e Suporte</h1>

        <div class="lista-ajuda">
            <div class="ajuda-item">
                <h4>Informações de Contacto</h4>
                <p>Precisa de assistência? A nossa equipa de suporte está pronta para ajudar. Utilize as seguintes informações para entrar em contacto connosco:</p>
                <p><strong>Email:</strong> <a href="mailto:info@estransportado.pt">info@estransportado.pt</a></p>
                <p><strong>Telefone:</strong> <a href="tel:+351123456789">+351 123 456 789</a></p>
                <p><strong>Endereço:</strong> Rua da Universidade, 1000 - Castelo Branco, Portugal</p>
            </div>

            <div class="ajuda-item">
                <h4>Perguntas Frequentes (FAQ)</h4>
                <p>Consulte as nossas perguntas frequentes para encontrar respostas rápidas para as suas dúvidas mais comuns:</p>
                <ul>
                    <li><strong>Como posso criar uma reserva?</strong> <br> Para criar uma reserva, navegue até à página de reservas, selecione o seu destino e datas, e siga os passos indicados.</li>
                    <li><strong>Como posso cancelar a minha reserva?</strong> <br> Pode cancelar a sua reserva através da secção "As minhas reservas" na sua conta. Por favor, verifique a nossa política de cancelamento.</li>
                    <li><strong>Quais são os métodos de pagamento aceites?</strong> <br> Aceitamos cartões de crédito (Visa, Mastercard) e transferência bancária.</li>
                    <li><strong>O que devo fazer se o autocarro estiver atrasado?</strong> <br> Lamentamos qualquer inconveniente causado por atrasos. Por favor, contacte o nosso suporte para obter informações atualizadas.</li>
                </ul>
            </div>

            <div class="ajuda-item">
                <h4>Suporte Adicional</h4>
                <p>Se a sua questão não foi respondida nas Perguntas Frequentes, por favor, não hesite em contactar-nos diretamente por email ou telefone. A nossa equipa fará o possível para o ajudar o mais rapidamente possível.</p>
            </div>
        </div>
    </div>

    <style>
        body {
            font-size: 16px;
        }
        .container {
            max-width: 960px;
            margin: 30px auto;
            padding: 20px;
        }
        h1 {
            color: #c2ff22;
            text-align: center;
            margin-bottom: 30px;
        }
        .ajuda-item {
            background: #333;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            color: #eee;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .ajuda-item h4 {
            color: #c2ff22;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.5em;
            border-bottom: 1px solid #555;
            padding-bottom: 10px;
        }
        .ajuda-item p {
            margin-bottom: 15px;
            font-size: 1.1em;
            line-height: 1.6;
            color: #ddd;
        }
        .ajuda-item strong {
            color: #c2ff22;
            font-weight: bold;
        }
        .rodape {
            background-color: #222;
            color: white;
            padding: 30px 0;
            text-align: center;
            width: 100%;
            margin-top: 40px;
        }
        .rodape a {
            color: #c2ff22;
            text-decoration: none;
        }
        .login-link {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .login-link a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background-color: #c2ff22;
            border-radius: 5px;
            color: #333;
            font-weight: bold;
        }
        .login-link a:hover {
            background-color: #a8e01e;
        }
    </style>
</body>
</html>