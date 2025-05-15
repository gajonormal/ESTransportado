<?php
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Processar filtro
$tipo_filtro = isset($_GET['tipo']) ? $_GET['tipo'] : 'aluno';

// Processar remoção
if (isset($_GET['remover'])) {
    $id_remover = $_GET['remover'];
    $stmt = $conn->prepare("DELETE FROM Utilizadores WHERE id_utilizador = ?");
    $stmt->bind_param("i", $id_remover);
    $stmt->execute();
    
    // Recarregar a página para atualizar a lista
    header("Location: gerir-utilizadores.php?tipo=$tipo_filtro");
    exit();
}

// Obter utilizadores conforme filtro
$utilizadores = [];
$stmt = $conn->prepare("SELECT * FROM Utilizadores WHERE tipo = ? ORDER BY nome_completo");
$stmt->bind_param("s", $tipo_filtro);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $utilizadores[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESTransportado - Gerir Utilizadores</title>
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
        
        /* Filtro */
        .filtro-container {
            background: #222;
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filtro-container label {
            color: #c2ff22;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        .filtro-container select {
            background: #333;
            color: white;
            border: 1px solid #444;
            padding: 8px 15px;
            border-radius: 10px;
            width: 200px;
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
        
        .btn-edit {
            color: #c2ff22;
        }
        
        .btn-remove {
            color: #ff6b6b;
        }
        
        .btn-ban {
            color: #ff9e3b;
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
        <h1>Gerir Utilizadores</h1>
        
        <div class="tab-container">
            <!-- Filtro por tipo de utilizador -->
            <div class="filtro-container">
                <label for="tipo-utilizador">Filtrar por tipo:</label>
                <select id="tipo-utilizador" onchange="location = this.value;">
                    <option value="gerir-utilizadores.php?tipo=aluno" <?= $tipo_filtro == 'aluno' ? 'selected' : '' ?>>Alunos</option>
                    <option value="gerir-utilizadores.php?tipo=gestor" <?= $tipo_filtro == 'gestor' ? 'selected' : '' ?>>Gestores</option>
                </select>
            </div>

            <!-- Lista de utilizadores -->
            <?php if (empty($utilizadores)): ?>
                <div class="no-registros">Não há <?= $tipo_filtro == 'aluno' ? 'alunos' : 'gestores' ?> registados</div>
            <?php else: ?>
                <?php foreach ($utilizadores as $utilizador): ?>
                    <div class="utilizador-item">
                        <div class="utilizador-info">
                            <h4>
                                <?= htmlspecialchars($utilizador['nome_completo']) ?>
                                <span class="tipo-badge"><?= $utilizador['tipo'] == 'aluno' ? 'Aluno' : 'Gestor' ?></span>
                            </h4>
                            
                            <div class="utilizador-detalhes">
                                <span><i class='bx bx-envelope'></i> <?= htmlspecialchars($utilizador['email_institucional']) ?></span>
                                <span><i class='bx bx-id-card'></i> <?= htmlspecialchars($utilizador['numero_matricula']) ?></span>
                                <span><i class='bx bx-calendar'></i> <?= date('d/m/Y', strtotime($utilizador['data_registo'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <!-- Botão Editar -->
                            <a href="editar-utilizador.php?id=<?= $utilizador['id_utilizador'] ?>" class="btn-action btn-edit">
                                <i class='bx bx-edit'></i>
                            </a>
                            
                            <!-- Botão Remover -->
                            <a href="gerir-utilizadores.php?tipo=<?= $tipo_filtro ?>&remover=<?= $utilizador['id_utilizador'] ?>" 
                               class="btn-action btn-remove" 
                               onclick="return confirm('Tem certeza que deseja remover este utilizador?')">
                                <i class='bx bx-trash'></i>
                            </a>
                            
                            <!-- Botão Banir (opcional) -->
                            <a href="banir-utilizador.php?id=<?= $utilizador['id_utilizador'] ?>" 
                               class="btn-action btn-ban" 
                               onclick="return confirm('Tem certeza que deseja banir este utilizador?')">
                                <i class='bx bx-block'></i>
                            </a>
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
        // Confirmação antes de remover
        document.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja remover este utilizador?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Confirmação antes de banir
        document.querySelectorAll('.btn-ban').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja banir este utilizador?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>