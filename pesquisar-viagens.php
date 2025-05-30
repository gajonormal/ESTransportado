<?php
session_start();

// Verificar se o usuário está logado e é aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'aluno') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Coletar dados do formulário
$origem = isset($_POST['origem']) ? mysqli_real_escape_string($conn, $_POST['origem']) : '';
$destino = isset($_POST['destino']) ? mysqli_real_escape_string($conn, $_POST['destino']) : '';
$tipo_transporte = isset($_POST['tipo_transporte']) ? mysqli_real_escape_string($conn, $_POST['tipo_transporte']) : '';
$data_viagem = isset($_POST['data_viagem']) ? mysqli_real_escape_string($conn, $_POST['data_viagem']) : '';

// DEBUG: Mostrar valores recebidos
echo "<!-- DEBUG: origem=$origem, destino=$destino, tipo_transporte=$tipo_transporte, data_viagem=$data_viagem -->";

// Construir a query SQL para buscar viagens
$sql = "SELECT v.* FROM viagens v WHERE v.ativo = 1";
$conditions = array();

if (!empty($origem)) {
    $conditions[] = "v.origem = '$origem'";
}

if (!empty($destino)) {
    $conditions[] = "v.destino = '$destino'";
}

// Converter valor do formulário para valor do banco de dados
if (!empty($tipo_transporte)) {
    $tipo_banco = ($tipo_transporte == 'publica') ? 'publico' : 'privado';
    $conditions[] = "v.tipo = '$tipo_banco'";
}

if (!empty($data_viagem)) {
    $conditions[] = "DATE(v.data_partida) = '$data_viagem'";
}

if (count($conditions) > 0) {
    $sql .= " AND " . implode(' AND ', $conditions);
}

// DEBUG: Mostrar query SQL
echo "<!-- DEBUG: SQL=$sql -->";

$result = mysqli_query($conn, $sql);

// DEBUG: Verificar erros na query
if (!$result) {
    echo "<!-- DEBUG: Erro SQL: " . mysqli_error($conn) . " -->";
}

// Array para armazenar as viagens com lugares disponíveis
$viagens = array();

if ($result && mysqli_num_rows($result) > 0) {
    while ($viagem = mysqli_fetch_assoc($result)) {
        // Buscar número de reservas para esta viagem
        $id_viagem = $viagem['id_viagem'];
        $sql_reservas = "SELECT COUNT(*) as total_reservas 
                         FROM reservas 
                         WHERE id_viagem = $id_viagem AND estado = 'confirmada'";
        $result_reservas = mysqli_query($conn, $sql_reservas);
        
        if ($result_reservas) {
            $reservas = mysqli_fetch_assoc($result_reservas);
            
            // Calcular lugares disponíveis
            $lugares_disponiveis = $viagem['lotacao_maxima'] - $reservas['total_reservas'];
            
            // Adicionar à array de viagens apenas se houver lugares disponíveis
            if ($lugares_disponiveis > 0) {
                $viagem['lugares_disponiveis'] = $lugares_disponiveis;
                $viagens[] = $viagem;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados da Pesquisa - ESTransportado</title>
    <!-- BOOTSTRAP 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="style.css">
    <!-- BOXICONS -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <style>
        /* Main Content */
        .main-container {
            max-width: 95%;
            width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .tab-container {
            background: rgb(27, 27, 27);
            border-radius: 20px;
            padding: 30px;
            width: 100%;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            color: white;
        }
        
        .viagem-detalhes {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            font-size: 0.95em;
            color: #ccc;
        }
        
        .viagem-detalhes span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Viagem Item */
        .viagem-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #222;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
            color: white;
        }
        
        .viagem-info {
            flex-grow: 1;
            margin-right: 40px;
        }
        
        .viagem-info h4 {
            color: #c2ff22;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.3em;
            display: flex;
            align-items: center;
        }
        
        .tipo-badge {
            background: #555;
            color: #eee;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 15px;
        }
        
        .preco {
            font-size: 1.5em;
            font-weight: bold;
            color: #c2ff22;
            margin: 15px 0;
        }
        
        .lugares {
            margin: 15px 0;
            font-size: 1.1em;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn-reservar {
            background-color: #c2ff22;
            color: #333;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: bold;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-reservar:hover {
            background-color: #a8e01e;
        }
        
        /* No resultados message */
        .no-resultados {
            background-color: #222;
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .no-resultados h3 {
            color: #c2ff22;
            margin-bottom: 20px;
        }
        
        .no-resultados .btn-primary {
            background-color: #c2ff22;
            color: #333;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: bold;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .no-resultados .btn-primary:hover {
            background-color: #a8e01e;
        }
        
        /* Proposta de Viagem */
        .proposta-viagem {
            background: linear-gradient(135deg, #333, #444);
            border: 2px solid #c2ff22;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            text-align: center;
            color: white;
        }
        
        .proposta-viagem h3 {
            color: #c2ff22;
            margin-bottom: 15px;
            font-size: 1.4em;
        }
        
        .proposta-viagem p {
            margin-bottom: 20px;
            color: #ccc;
            font-size: 1.1em;
        }
        
        .btn-proposta {
            background: linear-gradient(135deg, #c2ff22, #a8e01e);
            color: #333;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(194, 255, 34, 0.3);
        }
        
        .btn-proposta:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(194, 255, 34, 0.4);
            color: #333;
        }
        
        .pesquisa-info {
            background-color: #333;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            color: #eee;
        }
        
        .pesquisa-info strong {
            color: #c2ff22;
        }
        
        .divider {
            border-top: 2px solid #444;
            margin: 30px 0;
        }
        
        .icon-highlight {
            color: #c2ff22;
            font-size: 1.5em;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <header>
        <a href="pagina-aluno.php" class="logo">
            <img src="imagens/logo.png" alt="ESTransportado">
        </a>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <h1>Resultados da Pesquisa</h1>
        
        <div class="tab-container">
            <div class="pesquisa-info">
                <p>
                    <span><i class='bx bx-map-pin'></i> De <strong><?php echo htmlspecialchars($origem); ?></strong> para <strong><?php echo htmlspecialchars($destino); ?></strong></span>
                    <?php if (!empty($data_viagem)): ?>
                        <span class="ms-4"><i class='bx bx-calendar'></i> Data: <strong><?php echo date('d/m/Y', strtotime($data_viagem)); ?></strong></span>
                    <?php endif; ?>
                    <?php if (!empty($tipo_transporte)): ?>
                        <span class="ms-4"><i class='bx bx-bus'></i> Tipo: <strong><?php echo ($tipo_transporte == 'publica' ? 'Pública' : 'Privada'); ?></strong></span>
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if (!empty($viagens)): ?>
                <?php foreach ($viagens as $viagem): ?>
                    <div class="viagem-item">
                        <div class="viagem-info">
                            <h4>
                                <?= htmlspecialchars($viagem['origem']) ?> → <?= htmlspecialchars($viagem['destino']) ?>
                                <span class="tipo-badge"><?= ($viagem['tipo'] == 'publico' ? 'Pública' : 'Privada') ?></span>
                            </h4>
                            
                            <div class="viagem-detalhes">
                                <span><i class='bx bx-time'></i> Partida: <?= date('H:i', strtotime($viagem['data_partida'])) ?> - <?= date('d/m/Y', strtotime($viagem['data_partida'])) ?></span>
                                <span><i class='bx bx-time-five'></i> Chegada: <?= date('H:i', strtotime($viagem['data_chegada'])) ?> - <?= date('d/m/Y', strtotime($viagem['data_chegada'])) ?></span>
                            </div>
                            
                            <div class="preco">
                                <i class='bx bx-euro'></i> <?= number_format($viagem['preco'], 2) ?>
                            </div>
                            
                            <div class="lugares">
                                <i class='bx bx-user'></i> Lugares disponíveis: <strong><?= $viagem['lugares_disponiveis'] ?></strong> de <?= $viagem['lotacao_maxima'] ?>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="reservar-viagem.php?id=<?= $viagem['id_viagem'] ?>" class="btn-reservar">
                                <i class='bx bx-check-circle'></i> Reservar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Divider -->
                <div class="divider"></div>
                
            <?php else: ?>
                <div class="no-resultados">
                    <h3><i class='bx bx-search-alt-2'></i> Nenhuma viagem encontrada</h3>
                    <p>Não foram encontradas viagens disponíveis com os critérios selecionados.</p>
                    <p>Pode tentar alterar os filtros da sua pesquisa, escolher outra data ou criar uma proposta de viagem.</p>
                    <div>
                        <a href="pagina-aluno.php" class="btn btn-primary">
                            <i class='bx bx-arrow-back'></i> Voltar à pesquisa
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Proposta de Viagem - Sempre visível -->
            <div class="proposta-viagem">
                <h3><i class='bx bx-bulb icon-highlight'></i>Não encontraste o que procuravas?</h3>
                <p>Cria uma proposta de viagem personalizada e espera que esta seja aceite pelo gestor! 
                   Se houver interesse nesta, a viagem poderá ser organizada.</p>
                <a href="criar-proposta.php?origem=<?= urlencode($origem) ?>&destino=<?= urlencode($destino) ?>&data=<?= urlencode($data_viagem) ?>&tipo=<?= urlencode($tipo_transporte) ?>" 
                   class="btn-proposta">
                    <i class='bx bx-plus-circle'></i> Criar Proposta de Viagem
                </a>
                <div style="margin-top: 15px; font-size: 0.9em; color: #aaa;">
                    <i class='bx bx-info-circle'></i> A sua proposta será analisada e, se viável, poderá tornar-se numa viagem oficial
                </div>
            </div>
        </div>
    </div>

    <footer class="rodape">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="rodape-sobre">
                        <h3>Sobre a <span>EST</span>ransportado</h3>
                        <p>A ESTransportado oferece soluções de transporte eficientes e acessíveis para estudantes, ligando-os com as suas instituições de ensino.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="rodape-links">
                        <h3>Links <span>Rápidos</span></h3>
                            <ul>
                                <li><a href="minhas-reservas.php">As minhas reservas</a></li>
                                <li><a href="consultar-horarios.php">Consultar horários</a></li>
                                <li><a href="ajuda.php">Ajuda</a></li>
                            </ul>
                    </div>
                </div>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Boxicons JS -->
    <script src="https://unpkg.com/boxicons@latest/dist/boxicons.js"></script>
</body>
</html>