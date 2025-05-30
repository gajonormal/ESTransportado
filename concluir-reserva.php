<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Obter dados da viagem
$id_viagem = isset($_GET['id_viagem']) ? $_GET['id_viagem'] : null;

if (!$id_viagem) {
    header("Location: pagina_inicial.php");
    exit();
}

// Buscar dados da viagem
$stmt = $conn->prepare("SELECT * FROM Viagens WHERE id_viagem = ?");
$stmt->bind_param("i", $id_viagem);
$stmt->execute();
$result = $stmt->get_result();
$viagem = $result->fetch_assoc();

// Buscar um condutor específico
$stmt = $conn->prepare("SELECT * FROM Condutores WHERE id_condutor = 1");
$stmt->execute();
$result = $stmt->get_result();
$condutor = $result->fetch_assoc();

// Se a viagem não existir, redirecionar
if (!$viagem) {
    header("Location: pagina_inicial.php");
    exit();
}

// Verificar se a viagem está ativa
if ($viagem['estado'] !== 'ativo') {
    header("Location: pagina_inicial.php");
    exit();
}

// Verificar se ainda há lugares disponíveis
if ($viagem['lotacao_atual'] >= $viagem['lotacao_maxima']) {
    header("Location: pagina_inicial.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ESTransportado</title>

  <!-- BOOTSTRAP 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="style.css">

  <!-- BOXICONS -->
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
        <li><a href="listar-oferta.php">Minhas Ofertas</a></li>
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

  <style>
   
    .container {
        max-width: 700px;
        padding: 20px;
        border-radius: 20px;
        
    }
    .box {
        background: #111111;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 15px;
    }
    
    .box h5 {
        background: #bdf13b;
        font-weight: bold;
        color:#111111;
        padding: 10px;
        border-radius: 20px;
        display: inline-block;
    }
    

    .btn-add {
        display: block;
        width: fit-content; /* Ajusta a largura ao conteúdo */
        padding: 10px 20px;
        background: #bdf13b;
        color: #2c2c2c;
        text-align: center;
        border-radius: 15px;
        text-decoration: none;
        font-weight: bold;
        border: none;
        margin: 0 auto; /* Centraliza horizontalmente */
    }

    .btn-add:hover {
        background: var(--second-color);
        transition: 0.2s all;
    
    }

    .btn-lugar {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 10px;
        background: #2c2c2c;
        color: #ffffff;
        border-radius: 15px;
        border: none;
        text-decoration: none;
        font-weight: bold;
        gap: 10px; 
    }

    .btn-lugar:hover {
        background: #333333;
        transition: 0.5s all;
    }
    
    .btn-pagar {
        display: block;
        width: 100%; /* Ajusta a largura ao conteúdo */
        padding: 10px 20px;
        background: #bdf13b;
        color: #2c2c2c;
        text-align: center;
        border-radius: 15px;
        text-decoration: none;
        font-weight: bold;
        border: none;
        margin: 0 auto; /* Centraliza horizontalmente */
    }

    .btn-pagar:hover{
        background: var(--second-color);
        transition: 0.2s all;
    }

    .svg-seta {
        margin-left: auto;
    }

    .metodo-pagamento {
        display: flex;
        align-items: center;
        background: #2c2c2c;
        padding: 10px;
        margin: 5px 0;
        border-radius: 8px;
        border: 3px solid #3c3c3c;
    }

    .metodo-pagamento input {
        margin-right: 10px;
    }

    .metodo-pagamento svg {
        height: 20px;
        margin-right: 10px; 
    }

    .wrapper {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        width: 100%;
        max-width: 1200px;
        margin: auto;
    }

    .btn-avaliacoes {
        display: block;
        margin-top: 10px;
        padding: 10px 20px;
        background: #bdf13b;
        color: #2c2c2c;
        text-align: center;
        border-radius: 15px;
        text-decoration: none;
        font-weight: bold;
        border: none;
        
    }

    .btn-avaliacoes:hover{
        background: var(--second-color);
        transition: 0.2s all;
    }

</style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="box">
                <h5>Passageiros</h5>
                <form action="processar-reserva.php" method="POST">
                    <input type="hidden" name="id_viagem" value="<?php echo htmlspecialchars($id_viagem); ?>">
                    <label>Primeiro nome</label>
                    <div class="box-inserir">
                        <input type="text" name="primeiro_nome" placeholder="Insira o seu primeiro nome" required>
                    </div>
                    <label>Sobrenome</label>
                    <div class="box-inserir">
                        <input type="text" name="sobrenome" placeholder="Insira o seu sobrenome" required>
                    </div>
                    <button type="submit" class="btn-add">Adicionar passageiro</button>
                </form>
            </div>
            <div class="box">
                <h5>Reservar Lugar</h5>  
                <button type="button" class="btn-lugar" onclick="escolherLugar()">
                    <svg class="svg-bancos" fill="#ffffff" height="50px" width="50px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" stroke="#ffffff" stroke-width="0.00512"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M490.667,192h-21.333C457.551,192,448,201.551,448,213.333c0,11.782,9.551,21.333,21.333,21.333v42.667h-23.361 l-14.974-149.665C427.393,91.53,396.968,64,360.64,64h-38.613c-29.9,0-55.786,18.657-66.026,45.409 C245.761,82.658,219.874,64,189.973,64H151.36c-36.328,0-66.753,27.53-70.359,63.675L66.028,277.333H42.667v-42.667 c11.782,0,21.333-9.551,21.333-21.333C64,201.551,54.449,192,42.667,192H21.333C9.551,192,0,201.551,0,213.333v85.333 C0,310.449,9.551,320,21.333,320H64v42.667C64,374.449,73.551,384,85.333,384v42.667c0,11.782,9.551,21.333,21.333,21.333 c11.782,0,21.333-9.551,21.333-21.333V384h64v42.667c0,11.782,9.551,21.333,21.333,21.333c11.782,0,21.333-9.551,21.333-21.333 V384H256h21.333v42.667c0,11.782,9.551,21.333,21.333,21.333c11.782,0,21.333-9.551,21.333-21.333V384h64v42.667 c0,11.782,9.551,21.333,21.333,21.333c11.782,0,21.333-9.551,21.333-21.333V384c11.782,0,21.333-9.551,21.333-21.333V320h42.667 c11.782,0,21.333-9.551,21.333-21.333v-85.333C512,201.551,502.449,192,490.667,192z M405.333,341.333h-128V320h128V341.333z M234.667,341.333h-128V320h128V341.333z M294.123,131.916c1.43-14.331,13.496-25.249,27.903-25.249h38.613 c14.408,0,26.474,10.918,27.903,25.243l14.55,145.424H279.574L294.123,131.916z M123.457,131.916 c1.43-14.331,13.496-25.249,27.903-25.249h38.613c14.408,0,26.474,10.918,27.903,25.243l14.55,145.424H108.908L123.457,131.916z"></path> </g> </g> </g></svg>
                    Escolhe o teu lugar
                    <svg class="svg-seta" fill="#ffffff" height="20px" width="20px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 330 330" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path id="XMLID_222_" d="M250.606,154.389l-150-149.996c-5.857-5.858-15.355-5.858-21.213,0.001 c-5.857,5.858-5.857,15.355,0.001,21.213l139.393,139.39L79.393,304.394c-5.857,5.858-5.857,15.355,0.001,21.213 C82.322,328.536,86.161,330,90,330s7.678-1.464,10.607-4.394l149.999-150.004c2.814-2.813,4.394-6.628,4.394-10.606 C255,161.018,253.42,157.202,250.606,154.389z"></path> </g></svg>
                </button>
            </div>
            <div class="box">
                <h5>Contacto</h5>
                <form action="processar-reserva.php" method="POST">
                    <input type="hidden" name="id_viagem" value="<?php echo htmlspecialchars($id_viagem); ?>">
                    <label>E-mail</label>
                    <div class="box-inserir">
                        <input type="email" name="email" placeholder="Insira o seu e-mail" required>
                    </div>
                    <label>Telémovel</label>
                    <div class="box-inserir">
                        <input type="tel" name="telefone" placeholder="Insira o seu numero de telémovel" required>
                    </div>
                </form>
            </div>
            <div class="box">
                <h5>Pagamento</h5>
                <form action="processar-pagamento.php" method="POST">
                    <input type="hidden" name="id_viagem" value="<?php echo htmlspecialchars($id_viagem); ?>">
                    <input type="hidden" name="lugar" id="lugarSelecionado" value="">
                    <div class="metodo-pagamento">
                        <input type="radio" id="mbway" name="pagamento" value="MB Way" required onchange="verificarPagamento()">
                        <label for="mbway">
                            <svg id="Camada_1" data-name="Camada 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 143.2 69.57"><defs><style>.cls-1{fill:red;}.cls-2{fill:#ffffff;}</style></defs><title>Logo_MBWay</title><path class="cls-1" d="M7.07,61.84l-.24,1.88a1.54,1.54,0,0,0,1.35,1.72H69.29a1.56,1.56,0,0,0,1.58-1.54,1.15,1.15,0,0,0,0-.19l-.25-1.88A2.68,2.68,0,0,1,73,58.9a2.64,2.64,0,0,1,2.91,2.34v0l.24,1.83c.47,4.07-1.84,7.65-6,7.65H7.51c-4.12,0-6.42-3.58-5.95-7.65l.24-1.83A2.62,2.62,0,0,1,4.68,58.9h0a2.69,2.69,0,0,1,2.38,2.94" transform="translate(-1.5 -1.16)"/><path class="cls-2" d="M63.37,47.71A5,5,0,0,0,68.63,43a2.35,2.35,0,0,0,0-.26c-.06-2.91-2.71-4.79-5.66-4.8H57a2.48,2.48,0,0,1,0-5h4c2.69-.11,4.76-1.74,4.89-4.27.13-2.73-2.21-4.77-5.06-4.77H51.15l0,23.77H63.37m7.33-19a7.84,7.84,0,0,1-2.33,5.61l-.15.17.2.12a9.74,9.74,0,0,1,5,8.14,10,10,0,0,1-9.8,10.13h-15a2.63,2.63,0,0,1-2.59-2.65h0V21.66A2.62,2.62,0,0,1,48.68,19h0l12.15,0a9.61,9.61,0,0,1,9.87,9.33v.33" transform="translate(-1.5 -1.16)"/><path class="cls-2" d="M23.26,43.08l.07.2.07-.2c.68-1.88,1.51-4,2.38-6.23s1.8-4.67,2.69-6.85,1.76-4.18,2.58-5.9a19.91,19.91,0,0,1,2-3.61A4,4,0,0,1,36.26,19h.61a2.91,2.91,0,0,1,1.92.62A2.15,2.15,0,0,1,39.55,21l3.81,29.5a2.47,2.47,0,0,1-.65,1.79,2.6,2.6,0,0,1-1.85.6,3,3,0,0,1-1.92-.56,2.07,2.07,0,0,1-.89-1.48c-.13-1-.24-2.07-.36-3.27s-.76-6.33-.93-7.64-1.22-9.66-1.59-12.69l0-.26-1.22,2.56c-.41.88-.86,1.93-1.35,3.16s-1,2.53-1.47,3.91-2.89,8.06-2.89,8.06c-.22.61-.64,1.84-1,3s-.73,2.15-.82,2.34a3.42,3.42,0,0,1-4.6,1.49A3.46,3.46,0,0,1,20.29,50c-.1-.19-.44-1.21-.83-2.34s-.77-2.35-1-3c0,0-2.35-6.74-2.88-8.06s-1-2.67-1.47-3.91-.95-2.28-1.35-3.16L11.53,27l0,.26c-.37,3-1.43,11.36-1.6,12.69S9.14,46.36,9,47.55s-.25,2.29-.37,3.27a2.07,2.07,0,0,1-.89,1.48,3,3,0,0,1-1.91.56A2.57,2.57,0,0,1,4,52.26a2.47,2.47,0,0,1-.65-1.79L7.11,21a2.16,2.16,0,0,1,.77-1.32A2.88,2.88,0,0,1,9.8,19h.61a4,4,0,0,1,3.19,1.46,19.33,19.33,0,0,1,2,3.61q1.23,2.58,2.58,5.9t2.7,6.85c.87,2.26,1.69,4.35,2.37,6.23" transform="translate(-1.5 -1.16)"/><path class="cls-1" d="M15.8,1.16H62.06c4.36,0,6.53,3.27,7,7.59l.2,1.38a2.72,2.72,0,0,1-2.39,3A2.67,2.67,0,0,1,64,10.71v0L63.8,9.38c-.19-1.64-.88-2.91-2.55-2.91H16.62c-1.67,0-2.36,1.27-2.56,2.91l-.18,1.31A2.66,2.66,0,0,1,11,13.1h0a2.71,2.71,0,0,1-2.39-3l.19-1.38c.52-4.31,2.68-7.59,7-7.59" transform="translate(-1.5 -1.16)"/><path class="cls-2" d="M99,32.26c-.32,1.23-.65,2.55-1,4s-.7,2.75-1,4-.65,2.39-1,3.36a10.89,10.89,0,0,1-.76,2,2,2,0,0,1-1.89.94,4.09,4.09,0,0,1-1-.15,1.63,1.63,0,0,1-1-.86,12.06,12.06,0,0,1-.76-2.08c-.3-1-.62-2.22-1-3.57s-.67-2.77-1-4.28-.65-2.91-.91-4.2-.5-2.4-.68-3.3-.28-1.45-.31-1.64a1.6,1.6,0,0,1,0-.23v-.13a1.13,1.13,0,0,1,.44-.93,1.63,1.63,0,0,1,1.08-.35,1.76,1.76,0,0,1,1,.26,1.39,1.39,0,0,1,.54.89s.06.37.18,1,.29,1.38.48,2.31.41,2,.64,3.17.48,2.36.75,3.56.52,2.35.78,3.48.49,2.09.72,2.9c.22-.76.47-1.63.74-2.61s.55-2,.82-3,.52-2.09.77-3.13.48-2,.7-2.92.39-1.69.55-2.39.28-1.21.37-1.55a1.9,1.9,0,0,1,.64-1A1.78,1.78,0,0,1,99,25.35a1.84,1.84,0,0,1,1.22.39,1.71,1.71,0,0,1,.6,1c.27,1.09.53,2.33.82,3.69s.6,2.73.91,4.12.65,2.76,1,4.1.67,2.52,1,3.55c.22-.81.47-1.77.73-2.89s.51-2.28.78-3.48.54-2.36.78-3.53.48-2.22.68-3.15.37-1.69.48-2.27.19-.9.19-.92a1.49,1.49,0,0,1,.54-.88,1.72,1.72,0,0,1,1-.26,1.69,1.69,0,0,1,1.09.35,1.16,1.16,0,0,1,.44.93v.13a2,2,0,0,1,0,.24c0,.18-.13.72-.32,1.64s-.42,2-.69,3.29-.58,2.69-.91,4.18-.68,2.91-1,4.26-.64,2.54-1,3.56a11.57,11.57,0,0,1-.76,2.06,1.77,1.77,0,0,1-1,.9,3.45,3.45,0,0,1-1,.18,2.83,2.83,0,0,1-.41,0,3.75,3.75,0,0,1-.58-.13,2.31,2.31,0,0,1-.6-.32,1.49,1.49,0,0,1-.48-.6,15.11,15.11,0,0,1-.72-2.12c-.29-1-.59-2.1-.92-3.34s-.64-2.56-1-3.92-.61-2.63-.88-3.81" transform="translate(-1.5 -1.16)"/><path class="cls-2" d="M116.69,40.3c-.34,1.08-.64,2.08-.89,3s-.51,1.67-.73,2.26a1.51,1.51,0,0,1-3-.4,1.31,1.31,0,0,1,.07-.44l.42-1.39c.24-.78.55-1.75.93-2.93s.81-2.44,1.27-3.83.94-2.77,1.43-4.13,1-2.63,1.46-3.8A23.07,23.07,0,0,1,119,25.78a1.56,1.56,0,0,1,.73-.77,3.11,3.11,0,0,1,1.24-.2,3.25,3.25,0,0,1,1.27.23,1.4,1.4,0,0,1,.72.81c.32.67.7,1.58,1.13,2.71s.91,2.36,1.39,3.68,1,2.66,1.44,4,.91,2.64,1.3,3.82.73,2.19,1,3,.46,1.37.52,1.62a1.31,1.31,0,0,1,.07.44,1.26,1.26,0,0,1-.41,1,1.56,1.56,0,0,1-1.17.39,1.24,1.24,0,0,1-.87-.25,1.66,1.66,0,0,1-.45-.72c-.23-.59-.49-1.34-.8-2.26s-.63-1.92-1-3h-8.45m7.5-2.93c-.48-1.46-.92-2.8-1.35-4S122,31,121.52,29.86c-.11-.25-.23-.53-.35-.87s-.2-.51-.22-.57a2.55,2.55,0,0,0-.22.54c-.13.36-.24.65-.36.9-.45,1.1-.88,2.26-1.3,3.49s-.86,2.56-1.33,4Z" transform="translate(-1.5 -1.16)"/><path class="cls-2" d="M135.65,38.05a2.92,2.92,0,0,1-.32-.38l-.33-.46c-.32-.45-.65-1-1-1.64s-.75-1.32-1.12-2-.73-1.45-1.07-2.18-.68-1.41-.95-2-.53-1.18-.73-1.64a6.56,6.56,0,0,1-.37-1,1.34,1.34,0,0,1-.09-.26s0-.13,0-.25a1.38,1.38,0,0,1,.42-1,1.58,1.58,0,0,1,1.17-.41,1.24,1.24,0,0,1,1,.34,2.2,2.2,0,0,1,.41.67l.33.74c.17.38.38.85.62,1.41s.53,1.18.85,1.86.63,1.33,1,2l.95,1.87a14.31,14.31,0,0,0,.86,1.46,24.85,24.85,0,0,0,1.39-2.47c.49-1,1-1.95,1.41-2.92s.84-1.82,1.18-2.55l.59-1.39a2.23,2.23,0,0,1,.42-.67,1.16,1.16,0,0,1,1-.34,1.56,1.56,0,0,1,1.17.41,1.31,1.31,0,0,1,.42,1,1,1,0,0,1,0,.25l-.08.26-.39,1c-.19.47-.43,1-.72,1.64s-.59,1.31-.93,2-.72,1.45-1.09,2.18-.74,1.4-1.11,2-.72,1.21-1,1.65a5.38,5.38,0,0,1-.65.78v7a1.49,1.49,0,0,1-.42,1.11,1.53,1.53,0,0,1-2.15,0,1.55,1.55,0,0,1-.47-1.15v-7" transform="translate(-1.5 -1.16)"/></svg>
                            MB way
                        </label>
                    </div>
                    
                    <div class="metodo-pagamento">
                        <input type="radio" id="cartao" name="pagamento" value="Cartão Bancário" onchange="verificarPagamento()">
                        <label for="cartao">
                            <svg width="30px" height="30px" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M14 13V9.00001C14 7.89544 14.8954 7.00001 16 7.00001H42C43.1046 7.00001 44 7.89544 44 9.00001V27C44 28.1046 43.1046 29 42 29H40" stroke="#ffffff" stroke-width="2.112" stroke-linecap="round" stroke-linejoin="round"></path> <rect x="4" y="19" width="30" height="22" rx="2" fill="#2F88FF" stroke="#ffffff" stroke-width="2.112" stroke-linecap="round" stroke-linejoin="round"></rect> <path d="M4 28L34 28" stroke="white" stroke-width="2.112" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M34 23L34 35" stroke="#ffffff" stroke-width="2.112" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M4 23L4 35" stroke="#ffffff" stroke-width="2.112" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M11 34L19 34" stroke="white" stroke-width="2.112" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M25 34L27 34" stroke="white" stroke-width="2.112" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                            Cartão bancário
                        </label>
                    </div>
            
                    <div class="metodo-pagamento">
                        <input type="radio" id="paypal" name="pagamento" value="PayPal" onchange="verificarPagamento()">
                        <label for="paypal">
                            <i class='bx bxl-paypal'></i>
                             PayPal
                        </label>
                    </div>
                    <button type="submit" class="btn-pagar" id="btnPagar" disabled>Pagar Agora</button>
                </form>
            </div>
        </div>
        <div class="container">
            <div class="box">
                <h5>A tua reserva</h5>
                <p>Destino: <?php echo htmlspecialchars($viagem['origem']); ?> - <?php echo htmlspecialchars($viagem['destino']); ?></p>
                <p>Horário: <?php echo date('H:i', strtotime($viagem['data_partida'])); ?> - <?php echo date('H:i', strtotime($viagem['data_chegada'])); ?></p>
                <p>Lugares disponíveis: <?php echo $viagem['lotacao_maxima'] - $viagem['lotacao_atual']; ?></p>
            </div>
            <div class="box">
                <h5>Condutor</h5>
                <div class="condutor-info">
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($condutor['nome_condutor']); ?></p>
                    <p><strong>Classificação:</strong> ⭐⭐⭐⭐⭐ (<?php echo number_format($condutor['media_avaliacoes'], 1); ?>)</p>
                    <p><strong>Total de Avaliações:</strong> <?php echo $condutor['total_avaliacoes']; ?></p>
                </div>
            </div>
            <div class="box">
                <h5>Preço</h5>
                <p>Total: €<?php echo number_format($viagem['preco'], 2); ?></p>
            </div>
        </div>
    </div>

    <script>
    // Função para verificar se um lugar foi selecionado e método de pagamento escolhido
    function verificarLugarSelecionado() {
        const lugar = document.getElementById('lugarSelecionado').value;
        const metodoPagamento = document.querySelector('input[name="pagamento"]:checked');
        const btnPagar = document.getElementById('btnPagar');
        btnPagar.disabled = !lugar || !metodoPagamento;
    }

    // Função para verificar método de pagamento
    function verificarPagamento() {
        verificarLugarSelecionado();
    }

    // Verificar lugar selecionado quando a página carregar
    window.onload = function() {
        verificarLugarSelecionado();
    }

    // Função para escolher lugar
    function escolherLugar() {
        window.location.href = 'escolher-lugar.php?id_viagem=<?php echo htmlspecialchars($id_viagem); ?>';
    }

    // Verificar se há um lugar selecionado na URL
    const urlParams = new URLSearchParams(window.location.search);
    const lugarSelecionado = urlParams.get('lugar');
    if (lugarSelecionado) {
        document.getElementById('lugarSelecionado').value = lugarSelecionado;
        verificarLugarSelecionado();
    }
    </script>

    <!-- Rodapé -->
    <footer class="rodape">
        <div class="container">
            <div class="row">
                <!-- Sobre -->
                <div class="col-md-4">
                    <div class="rodape-sobre">
                        <h3>Sobre a <span>EST</span>ransportado</h3>
                        <p>A ESTransportado oferece soluções de transporte eficientes e acessíveis para estudantes, ligando-os com as suas instituições de ensino.</p>
                    </div>
                </div>
                <!-- Links Rápidos -->
                <div class="col-md-4">
                    <div class="rodape-links">
                    </div>
                </div>
                <!-- Contacto -->
                <div class="col-md-4">
                    <div class="rodape-contactos">
                        <h3>Contacte-nos</h3>
                        <ul>
                            <li><strong>Email:</strong> info@estransportado.pt</li>
                            <li><strong>Telefone:</strong> +351 123 456 789</li>
                            <li><strong>Endereço:</strong> Rua da Universidade, 1000 - Castelo Branco, Portugal</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="rodape-direitos">
                <p>&copy; 2025 ESTransportado. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>