<?php
session_start();

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se o botão "Sair" foi clicado
    if(isset($_POST['sair'])) {
        // Configura a sessão de redirecionamento
        $_SESSION['redirect_to_login'] = true;
        // Redireciona para a página de login
        header('Location: logout.php');
        exit;
    }
}

// Conexão com o MySQL
$usuario = 'root';
$senha = '';
$database= 'projeto_devops';
$host='localhost';

// Cria a conexão
$conn = new mysqli($host, $usuario, $senha, $database);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta SQL para verificar as credenciais
    $sql = "SELECT id FROM usuarios WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Credenciais corretas, obtém o ID do usuário e armazena na sessão
        $row = $result->fetch_assoc();
        session_start();
        $_SESSION['id_usuario'] = $row['id'];


        
        // Redireciona para pausas.php
        header('Location: pausas.php');
        exit;
    } else {
        // Credenciais incorretas, exibe uma mensagem de erro
        echo "Usuário ou senha incorretos.";
    }
}

// Fecha a conexão
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="" method="post">
        <label for="username">Usuário:</label><br>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Senha:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>

    <script>
    // Função para desabilitar o botão de voltar do navegador
    function desabilitarBotaoVoltar() {
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function () {
            window.history.pushState(null, "", window.location.href);
        };
    }

    // Chama a função ao carregar a página
    desabilitarBotaoVoltar();
</script>

</body>
</html>
