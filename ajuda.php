<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: pagina-login.php");
    exit();
}

// Determinar a página inicial com base no tipo de usuário
$pagina_inicial = 'pagina-login.php'; // padrão

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
        default:
            $pagina_inicial = 'pagina-login.php';
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

<header>
    <a href="<?php echo $pagina_inicial; ?>" class="logo">
        <img src="imagens/logo.png" alt="ESTransportado">
    </a>
</header>

<body>
    <style>
        body {
            font-size: 16px; /* Aumentei o tamanho da fonte base */
        }
        .container {
            max-width: 960px; /* Largura máxima para um aspeto mais profissional */
            margin: 30px auto; /* Adicionei margem superior e inferior */
            padding: 20px;
        }
        h1 {
            color: #c2ff22;
            text-align: center;
            margin-bottom: 30px;
        }
        .ajuda-item {
            background: #333;
            padding: 25px; /* Aumentei o preenchimento */
            border-radius: 12px; /* Bordas mais arredondadas */
            margin-bottom: 25px;
            color: #eee;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Adicionei uma sombra suave */
        }
        .ajuda-item h4 {
            color: #c2ff22;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.5em; /* Aumentei o tamanho da fonte do título */
            border-bottom: 1px solid #555; /* Adicionei uma linha abaixo do título */
            padding-bottom: 10px;
        }
        .ajuda-item p {
            margin-bottom: 15px;
            font-size: 1.1em; /* Aumentei o tamanho da fonte do parágrafo */
            line-height: 1.6; /* Melhorei a legibilidade */
            color: #ddd;
        }
        .ajuda-item strong {
            color: #c2ff22;
            font-weight: bold;
        }
        .rodape {
            background-color: #222;
            color: white;
            padding: 30px 0; /* Aumentei o preenchimento */
            text-align: center;
            width: 100%;
            margin-top: 40px; /* Adicionei margem superior */
        }
        .rodape a {
            color: #c2ff22;
            text-decoration: none;
        }
        .rodape .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .rodape-sobre h3,
        .rodape-links h3,
        .rodape-contactos h3 {
            color: #c2ff22;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .rodape-links ul,
        .rodape-contactos ul {
            list-style: none;
            padding: 0;
        }
        .rodape-links li a,
        .rodape-contactos li {
            color: #eee;
            text-decoration: none;
            margin-bottom: 10px;
            display: block;
            font-size: 1em;
        }
        .rodape-contactos li strong {
            color: #c2ff22;
            font-weight: bold;
        }
        .rodape-direitos {
            margin-top: 20px;
            font-size: 0.9em;
            color: #999;
        }
    </style>

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

   