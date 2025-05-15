<?php
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Verificar se o usuário é admin
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: login.php"); // Redireciona se não for admin
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Processar ações (aceitar/recusar registos)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        $id_utilizador = $_POST['id_utilizador'];
        $acao = $_POST['acao'];
        
        if ($acao === 'aceitar') {
            // Ativar conta do utilizador
            $stmt = $conn->prepare("UPDATE Utilizadores SET conta_ativa = TRUE WHERE id_utilizador = ?");
            $stmt->bind_param("i", $id_utilizador);
            $stmt->execute();
        } elseif ($acao === 'recusar') {
            // Desativar conta do utilizador
            $stmt = $conn->prepare("UPDATE Utilizadores SET conta_ativa = FALSE WHERE id_utilizador = ?");
            $stmt->bind_param("i", $id_utilizador);
            $stmt->execute();
        }
        
        // Recarregar a página para atualizar a lista
        header("Location: gerir-registos.php");
        exit();
    }
}

// Obter registos pendentes (conta_ativa = FALSE)
$registos_pendentes = [];
$stmt = $conn->prepare("SELECT * FROM Utilizadores WHERE conta_ativa = FALSE ORDER BY data_registo DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $registos_pendentes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESTransportado - Gerir Registos Pendentes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
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
        
        /* Utilizador Items */
        .utilizador-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #222;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
            color: white;
        }
        
        .utilizador-info {
            flex-grow: 1;
            margin-right: 40px;
        }
        
        .utilizador-info h4 {
            color: #c2ff22;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.3em;
            display: flex;
            align-items: center;
        }
        
        .utilizador-detalhes {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            font-size: 0.95em;
            color: #ccc;
        }
        
        .utilizador-detalhes span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tipo-badge {
            background: #555;
            color: #eee;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 15px;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn-action {
            background-color: #333;
            border: none;
            border-radius: 10px;
            padding: 12px;
            color: white;
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            min-width: 50px;
            transition: all 0.3s;
        }
        
        .btn-accept {
            color: #4CAF50;
        }
        
        .btn-reject {
            color: #ff6b6b;
        }
        
        .btn-action:hover {
            background-color: #444;
        }
        
        /* No registros message */
        .no-registros {
            background-color: #222;
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <a href="pagina-admin.php" class="logo">
            <img src="imagens/logo.png" alt="ESTransportado">
        </a>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <h1>Gerir Registos Pendentes</h1>
        
        <div class="tab-container">
            <!-- Lista de registos pendentes -->
            <?php if (empty($registos_pendentes)): ?>
                <div class="no-registros">Não há registros pendentes</div>
            <?php else: ?>
                <?php foreach ($registos_pendentes as $registo): ?>
                    <div class="utilizador-item">
                        <div class="utilizador-info">
                            <h4>
                                <?= htmlspecialchars($registo['nome_completo']) ?>
                                <span class="tipo-badge"><?= $registo['tipo'] == 'aluno' ? 'Aluno' : 'Gestor' ?></span>
                            </h4>
                            
                            <div class="utilizador-detalhes">
                                <span><i class='bx bx-envelope'></i> <?= htmlspecialchars($registo['email_institucional']) ?></span>
                                <span><i class='bx bx-id-card'></i> <?= htmlspecialchars($registo['numero_matricula']) ?></span>
                                <span><i class='bx bx-calendar'></i> <?= date('d/m/Y H:i', strtotime($registo['data_registo'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <!-- Botão Aceitar -->
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_utilizador" value="<?= $registo['id_utilizador'] ?>">
                                <input type="hidden" name="acao" value="aceitar">
                                <button type="submit" class="btn-action btn-accept" title="Aceitar registo">
                                    <i class='bx bx-check'></i>
                                </button>
                            </form>
                            
                            <!-- Botão Recusar -->
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_utilizador" value="<?= $registo['id_utilizador'] ?>">
                                <input type="hidden" name="acao" value="recusar">
                                <button type="submit" class="btn-action btn-reject" title="Recusar registo">
                                    <i class='bx bx-x'></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
                                <li><a href="gerir-registos.php">Gerir registos</a></li>
                                <li><a href="gerir-utilizadores.php">Gerir utilizadores</a></li>
                                <li><a href="gerir-avaliacoes.php">Gerir avaliações</a></li>
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
    
    <script>
        // Confirmação antes de aceitar/recusar
        document.querySelectorAll('.btn-accept, .btn-reject').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const action = this.closest('form').querySelector('[name="acao"]').value;
                const actionText = action === 'aceitar' ? 'aceitar' : 'recusar';
                
                if (!confirm(`Tem certeza que deseja ${actionText} este registo?`)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>