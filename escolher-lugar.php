<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Obter ID da viagem
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

if (!$viagem) {
    header("Location: pagina_inicial.php");
    exit();
}

// Buscar lugares já reservados
$stmt = $conn->prepare("SELECT lugar FROM Reservas WHERE id_viagem = ? AND estado = 'confirmado'");
$stmt->bind_param("i", $id_viagem);
$stmt->execute();
$result = $stmt->get_result();
$lugares_ocupados = [];
while ($row = $result->fetch_assoc()) {
    $lugares_ocupados[] = $row['lugar'];
}

// Gerar matriz de lugares (4x4 para exemplo)
$total_lugares = $viagem['lotacao_maxima'];
$lugares_por_linha = 4;
$total_linhas = ceil($total_lugares / $lugares_por_linha);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escolher Lugar - ESTransportado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
        }
        .lugares-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        .lugar {
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #2c2c2c;
            color: white;
        }
        .lugar.disponivel:hover {
            background: #bdf13b;
            color: #2c2c2c;
        }
        .lugar.ocupado {
            background: #ff4444;
            cursor: not-allowed;
        }
        .lugar.selecionado {
            background: #bdf13b;
            color: #2c2c2c;
        }
        .legenda {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        .legenda-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .legenda-cor {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        .btn-confirmar {
            background: #bdf13b;
            color: #2c2c2c;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-confirmar:hover {
            background: #a8d835;
        }
        .btn-confirmar:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Escolha seu lugar</h2>
        <p>Viagem: <?php echo htmlspecialchars($viagem['origem']); ?> - <?php echo htmlspecialchars($viagem['destino']); ?></p>
        
        <div class="legenda">
            <div class="legenda-item">
                <div class="legenda-cor" style="background: #2c2c2c;"></div>
                <span>Disponível</span>
            </div>
            <div class="legenda-item">
                <div class="legenda-cor" style="background: #ff4444;"></div>
                <span>Ocupado</span>
            </div>
            <div class="legenda-item">
                <div class="legenda-cor" style="background: #bdf13b;"></div>
                <span>Selecionado</span>
            </div>
        </div>

        <form id="formLugar" action="processar-pagamento.php" method="POST">
            <input type="hidden" name="id_viagem" value="<?php echo htmlspecialchars($id_viagem); ?>">
            <input type="hidden" name="lugar" id="lugarSelecionado">
            
            <div class="lugares-grid">
                <?php
                for ($i = 1; $i <= $total_lugares; $i++) {
                    $lugar = 'A' . $i;
                    $ocupado = in_array($lugar, $lugares_ocupados);
                    $classe = $ocupado ? 'ocupado' : 'disponivel';
                    ?>
                    <div class="lugar <?php echo $classe; ?>" 
                         data-lugar="<?php echo $lugar; ?>"
                         <?php echo $ocupado ? 'onclick="return false;"' : 'onclick="selecionarLugar(this)"'; ?>>
                        <?php echo $lugar; ?>
                    </div>
                <?php } ?>
            </div>

            <button type="button" class="btn-confirmar" id="btnConfirmar" disabled onclick="confirmarLugar()">Confirmar Lugar</button>
        </form>
    </div>

    <script>
        function selecionarLugar(elemento) {
            // Remover seleção anterior
            document.querySelectorAll('.lugar.selecionado').forEach(el => {
                el.classList.remove('selecionado');
            });
            
            // Adicionar nova seleção
            elemento.classList.add('selecionado');
            
            // Atualizar input hidden
            document.getElementById('lugarSelecionado').value = elemento.dataset.lugar;
            
            // Habilitar botão
            document.getElementById('btnConfirmar').disabled = false;
        }

        function confirmarLugar() {
            const lugar = document.getElementById('lugarSelecionado').value;
            if (lugar) {
                window.location.href = 'concluir-reserva.php?id_viagem=<?php echo htmlspecialchars($id_viagem); ?>&lugar=' + lugar;
            }
        }
    </script>
</body>
</html> 