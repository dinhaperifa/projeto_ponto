<?php
//Isso instrui o navegador a não armazenar em cache a página
header("Cache-Control: no-cache, must-revalidate");


session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) {
    // Se não estiver autenticado, redireciona para a página de login
    header('Location: logout.php');
    exit;
}

// Verifica se a sessão de redirecionamento está configurada
if(isset($_SESSION['redirect_to_login']) && $_SESSION['redirect_to_login'] === true) {
    // Limpa a sessão de redirecionamento
    unset($_SESSION['redirect_to_login']);
    // Redireciona para a página de login
    header('Location: index.php');
    exit;
}

// Configurações do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'projeto_devops';

// Conexão com o MySQL
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o formulário foi submetido
if(isset($_POST['tipo_pausa'])) {
    // Obtém o tipo de pausa do formulário
    $tipo_pausa = $_POST['tipo_pausa'];
    $id_usuario = $_SESSION['id_usuario'];
    
    // Verifica se o usuário já registrou uma pausa do tipo no dia atual
    $sql = "SELECT COUNT(*) AS total FROM pausas WHERE tipo = ? AND id_usuario = ? AND DATE(data_hora) = CURDATE()";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $tipo_pausa, $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row['total'] > 0) {
            echo "Você já registrou essa pausa hoje.";
        } else {
            // Insere a pausa na tabela
            $sql_insert = "INSERT INTO pausas (tipo, id_usuario) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            if ($stmt_insert) {
                $stmt_insert->bind_param("si", $tipo_pausa, $id_usuario);
                if ($stmt_insert->execute()) {
                    // Pausa registrada com sucesso
                    //echo "Pausa registrada com sucesso!";
                } else {
                    echo "Erro ao registrar a pausa: " . $stmt_insert->error;
                }
            } else {
                echo "Erro ao preparar a consulta: " . $conn->error;
            }
        }
    } else {
        echo "Erro ao preparar a consulta: " . $conn->error;
    }
}



// Função para fechar a conexão e redirecionar para a página de login
function logout() {
    // Fecha a conexão
    global $conn;
    $conn->close();

    // Redireciona para a página de login
    header("Location: index.php");
    exit; // Garante que o script não continue executando após o redirecionamento
}

// Verifica se o botão "Sair" foi clicado
if(isset($_POST['sair'])) {
    // Exibe um prompt de confirmação
    echo '<script>
            var confirmar = confirm("Deseja realmente sair?");
            if (confirmar) {
                location.href = "index.php";
            }
          </script>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pausas</title>
</head>
<body>
   

    <h2>Registrar Pausas</h2>
    <form method="post">
        <button type="submit" name="tipo_pausa" value="Entrada">Entrada</button>
        <button type="submit" name="tipo_pausa" value="Pausa Café">Pausa Café</button>
        <button type="submit" name="tipo_pausa" value="Retorno Café">Retorno Café</button>
        <button type="submit" name="tipo_pausa" value="Pausa Almoço">Pausa Almoço</button>
        <button type="submit" name="tipo_pausa" value="Retorno Almoço">Retorno Almoço</button>
        <button type="submit" name="tipo_pausa" value="Pausa Reunião">Pausa Reunião</button>
        <button type="submit" name="tipo_pausa" value="Retorno Reunião">Retorno Reunião</button>
        <button type="submit" name="tipo_pausa" value="Pausa Banheiro">Pausa Banheiro</button>
        <button type="submit" name="tipo_pausa" value="Retorno Banheiro">Retorno Banheiro</button>
        <button type="submit" name="tipo_pausa" value="Saída">Saída</button>  <br><br>

   <!-- Adiciona um link para o histórico -->
   <a href="historico.php">Ver Histórico</a> <br><br>

        <!-- Adiciona um botão "Sair" -->
        <button type="submit" name="sair">Sair</button>
    </form>

    <!-- Div para exibir a pausa registrada -->
    <div id="mensagemPausa">
        <?php
        // Exibe a mensagem de pausa registrada, se houver
        if(isset($_POST['tipo_pausa'])) {
            echo "Pausa registrada: " . $_POST['tipo_pausa'];
        }
        ?>
    </div>

    <script>
    // Detecta quando o usuário tenta navegar para trás
    window.addEventListener('popstate', function (event) {
        // Redireciona para a página de login
        window.location.href = 'index.php';
    });
</script>

</body>
</html>
