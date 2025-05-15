<?php
session_start();

// Verificar se o usuário está logado e é aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'aluno') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber e limpar os dados do formulário
    $tipo_viagem = isset($_POST['tipo_viagem']) ? htmlspecialchars($_POST['tipo_viagem']) : 'ida';
    $origem = isset($_POST['origem']) ? htmlspecialchars($_POST['origem']) : '';
    $destino = isset($_POST['destino']) ? htmlspecialchars($_POST['destino']) : '';
    $tipo_transporte = isset($_POST['tipo_transporte']) ? htmlspecialchars($_POST['tipo_transporte']) : 'publica';
    $data_viagem = isset($_POST['data_viagem']) ? htmlspecialchars($_POST['data_viagem']) : '';

    // Formatar a data para exibição
    $data_formatada = date('d/m/Y', strtotime($data_viagem));
    
    // Verificar se todos os campos necessários foram preenchidos
    if (empty($origem) || empty($destino) || empty($data_viagem)) {
        $_SESSION['erro'] = "Todos os campos são obrigatórios";
        header("Location: index.php");
        exit();
    }

    // Consulta SQL base dependendo do tipo de transporte
    if ($tipo_transporte == 'publica') {
        $sql = "SELECT v.id, r.nome_rota, t.empresa, v.hora_partida, v.hora_chegada, v.preco, v.vagas_disponiveis 
                FROM viagens v 
                JOIN rotas r ON v.rota_id = r.id 
                JOIN transportadoras t ON v.transportadora_id = t.id 
                WHERE r.origem = ? AND r.destino = ? AND DATE(v.data_partida) = ? AND v.tipo = 'publica'
                ORDER BY v.hora_partida ASC";
    } else {
        $sql = "SELECT v.id, r.nome_rota, t.empresa, v.hora_partida, v.hora_chegada, v.preco, v.vagas_disponiveis 
                FROM viagens v 
                JOIN rotas r ON v.rota_id = r.id 
                JOIN transportadoras t ON v.transportadora_id = t.id 
                WHERE r.origem = ? AND r.destino = ? AND DATE(v.data_partida) = ? AND v.tipo = 'privada'
                ORDER BY v.hora_partida ASC";
    }

    // Preparar e executar a consulta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $origem, $destino, $data_viagem);
    $stmt->execute();
    $resultado = $stmt->get_result();
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
        .resultados-container {
            padding: 30px 0;
            max-width: 1200px;
            margin: 0 auto;
        }
        .criterios-pesquisa {
            background-color: #111111;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            color: #fff;
        }
        .viagem-card {
            background-color: #222221;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: #fff;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .viagem-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
        }
        .viagem-info {
            flex: 1;
        }
        .viagem-horario {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .viagem-horario h3 {
            margin: 0 20px;
            font-size: 20px;
            font-weight: bold;
        }
        .viagem-detalhes {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .viagem-detalhes p {
            margin-right: 20px;
            margin-bottom: 5px;
        }
        .viagem-preco {
            text-align: right;
            padding: 0 20px;
            border-left: 1px solid #444;
        }
        .preco-valor {
            font-size: 24px;
            font-weight: bold;
            color: #c2ff22;
        }
        .btn-reservar {
            background-color: #c2ff22;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin-top: 10px;
        }
        .btn-reservar:hover {
            background-color: #a8e600;
        }
        .sem-resultados {
            background-color: #222221;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            color: #fff;
        }
        .viagem-duracao {
            font-size: 14px;
            color: #888;
        }
        .badge-vagas {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
        }
        .baixa-disponibilidade {
            background-color: #ffc107;
        }
        .muito-baixa-disponibilidade {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">
            <img src="imagens/logo.png" alt="ESTransportado">
        </a>

        <ul class="navbar">
            <li><a href="as-minhas-reservas.html">As minhas reservas</a></li>
            <li><a href="consultar-horarios.html">Consultar horários</a></li>
            <li><a href="ajuda.html">Ajuda</a></li>
        </ul>

        <a href="perfil.php" class="btn btn-primary" id="btn-entrar">Perfil</a>
    </header>

    <main class="resultados-container">
        <div class="criterios-pesquisa">
            <h2>Resultados da pesquisa</h2>
            <div class="row">
                <div class="col-md-3">
                    <p><strong>De:</strong> <?php echo $origem; ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>Para:</strong> <?php echo $destino; ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>Data:</strong> <?php echo $data_formatada; ?></p>
                </div>
                <div class="col-md-3">
                    <p><strong>Tipo:</strong> <?php echo ucfirst($tipo_transporte); ?></p>
                </div>
            </div>
        </div>

        <?php if (isset($resultado) && $resultado->num_rows > 0): ?>
            <div class="resultados-lista">
                <?php while ($viagem = $resultado->fetch_assoc()): ?>
                    <?php
                    // Calcular a duração da viagem
                    $hora_partida = strtotime($viagem['hora_partida']);
                    $hora_chegada = strtotime($viagem['hora_chegada']);
                    $duracao = $hora_chegada - $hora_partida;
                    $horas = floor($duracao / 3600);
                    $minutos = floor(($duracao % 3600) / 60);
                    
                    // Determinar a classe de disponibilidade
                    $vagas_class = '';
                    if ($viagem['vagas_disponiveis'] <= 5) {
                        $vagas_class = 'muito-baixa-disponibilidade';
                    } elseif ($viagem['vagas_disponiveis'] <= 10) {
                        $vagas_class = 'baixa-disponibilidade';
                    }
                    ?>
                    <div class="viagem-card">
                        <div class="viagem-info">
                            <div class="viagem-horario">
                                <h3><?php echo substr($viagem['hora_partida'], 0, 5); ?></h3>
                                <div>
                                    <div class="viagem-duracao">
                                        <?php echo $horas > 0 ? $horas . 'h ' : ''; ?><?php echo $minutos; ?>min
                                    </div>
                                    <div>⟶</div>
                                </div>
                                <h3><?php echo substr($viagem['hora_chegada'], 0, 5); ?></h3>
                            </div>
                            <div class="viagem-detalhes">
                                <p><strong>Rota:</strong> <?php echo $viagem['nome_rota']; ?></p>
                                <p><strong>Transportadora:</strong> <?php echo $viagem['empresa']; ?></p>
                                <p>
                                    <span class="badge-vagas <?php echo $vagas_class; ?>">
                                        <?php echo $viagem['vagas_disponiveis']; ?> vagas disponíveis
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="viagem-preco">
                            <div class="preco-valor"><?php echo number_format($viagem['preco'], 2); ?>€</div>
                            <a href="reservar-viagem.php?id=<?php echo $viagem['id']; ?>" class="btn-reservar">Reservar</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="sem-resultados">
                <h3>Não foram encontrados resultados para a sua pesquisa</h3>
                <p>Tente alterar os critérios de pesquisa ou selecionar outra data.</p>
                <a href="index.php" class="btn btn-primary mt-3">Voltar</a>
            </div>
        <?php endif; ?>
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
                        <h3>Links <span>Rápidos</span></h3>
                        <ul>
                            <li><a href="as-minhas-reservas.html">As minhas reservas</a></li>
                            <li><a href="ajuda.html">Ajuda</a></li>
                            <li><a href="perfil.php">Perfil</a></li>
                        </ul>
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

    <!-- JavaScript do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>