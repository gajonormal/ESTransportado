<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Incluir conexão com o banco de dados
require_once 'basedados/basedados.h';

// Verificar se os dados necessários foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_viagem = isset($_POST['id_viagem']) ? $_POST['id_viagem'] : null;
    $metodo_pagamento = isset($_POST['pagamento']) ? $_POST['pagamento'] : null;
    
    if (!$id_viagem || !$metodo_pagamento) {
        $_SESSION['erro'] = "Dados incompletos para processar o pagamento.";
        header("Location: concluir-reserva.php?id_viagem=" . $id_viagem);
        exit();
    }

    // Buscar dados da viagem
    $stmt = $conn->prepare("SELECT * FROM Viagens WHERE id_viagem = ?");
    $stmt->bind_param("i", $id_viagem);
    $stmt->execute();
    $result = $stmt->get_result();
    $viagem = $result->fetch_assoc();

    if (!$viagem) {
        $_SESSION['erro'] = "Viagem não encontrada.";
        header("Location: pagina_inicial.php");
        exit();
    }

    // Verificar se ainda há lugares disponíveis
    if ($viagem['lotacao_atual'] >= $viagem['lotacao_maxima']) {
        $_SESSION['erro'] = "Desculpe, não há mais lugares disponíveis nesta viagem.";
        header("Location: pagina_inicial.php");
        exit();
    }

    // Buscar dados do passageiro
    $stmt = $conn->prepare("SELECT * FROM Passageiros WHERE id_utilizador = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $passageiro = $result->fetch_assoc();

    if (!$passageiro) {
        $_SESSION['erro'] = "Dados do passageiro não encontrados.";
        header("Location: concluir-reserva.php?id_viagem=" . $id_viagem);
        exit();
    }

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // Atualizar lotação da viagem
        $stmt = $conn->prepare("UPDATE Viagens SET lotacao_atual = lotacao_atual + 1 WHERE id_viagem = ?");
        $stmt->bind_param("i", $id_viagem);
        $stmt->execute();

        // Inserir a reserva
        $stmt = $conn->prepare("INSERT INTO Reservas (id_viagem, id_passageiro, preco_total, estado) VALUES (?, ?, ?, 'confirmado')");
        $stmt->bind_param("iid", $id_viagem, $passageiro['id_passageiro'], $viagem['preco']);
        $stmt->execute();

        // Criar notificação para o usuário
        $titulo = "Reserva Confirmada";
        $mensagem = "Sua reserva para a viagem de " . $viagem['origem'] . " para " . $viagem['destino'] . " foi confirmada.";
        
        $stmt = $conn->prepare("INSERT INTO Notificacoes (id_utilizador, titulo, mensagem, tipo) VALUES (?, ?, ?, 'reserva')");
        $stmt->bind_param("iss", $_SESSION['user_id'], $titulo, $mensagem);
        $stmt->execute();

        // Confirmar transação
        $conn->commit();

        $_SESSION['sucesso'] = "Reserva realizada com sucesso!";
        header("Location: pagina-aluno.php");
        exit();

    } catch (Exception $e) {
        // Em caso de erro, desfazer todas as alterações
        $conn->rollback();
        $_SESSION['erro'] = "Erro ao processar a reserva. Por favor, tente novamente.";
        header("Location: concluir-reserva.php?id_viagem=" . $id_viagem);
        exit();
    }
} else {
    header("Location: pagina_inicial.php");
    exit();
}
?> 