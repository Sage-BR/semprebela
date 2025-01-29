<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'functions/functions.php';
require_once 'functions/config.php';
require_once 'functions/conexao.php';
require_once 'functions/database.php';

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = dbConnect();

    // Cadastro
    if (isset($_POST['cadastrar'])) {
        $usuario = sanitize_input($_POST['usuario']);
        $senha = sanitize_input($_POST['senha']);
        
        if (!empty($usuario) && !empty($senha)) {
            try {
                // Verifica se o usuário já existe
                $query = "SELECT ID FROM LOGIN WHERE USUARIO = ?";
                $stmt = mysqli_prepare($conn, $query);
                
                if (!$stmt) {
                    throw new Exception("Erro na preparação da query: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt, 's', $usuario);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    dbClose($conn);
                    flash("mensagem", "O usuário já existe. Tente outro nome!", "danger");
                    header("Location: cadastrar.php");
                    exit;
                }
                
                mysqli_stmt_close($stmt);
                
                // Insere o novo usuário
                $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);
                $insertQuery = "INSERT INTO LOGIN (USUARIO, SENHA, STATUS, FK_NIVEL) VALUES (?, ?, 'N', 2)";
                $stmt = mysqli_prepare($conn, $insertQuery);
                
                if (!$stmt) {
                    throw new Exception("Erro na preparação da query de inserção: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt, 'ss', $usuario, $senhaCriptografada);
                
                if (mysqli_stmt_execute($stmt)) {
                    dbClose($conn);
                    flash("mensagem", "Usuário cadastrado com sucesso!", "success");
                    header("Location: index.php");
                    exit;
                } else {
                    throw new Exception("Erro ao executar a inserção: " . mysqli_stmt_error($stmt));
                }
            } catch (Exception $e) {
                error_log("Erro no cadastro: " . $e->getMessage());
                flash("mensagem", "Erro ao cadastrar usuário: " . $e->getMessage(), "danger");
                dbClose($conn);
                header("Location: cadastrar.php");
                exit;
            }
        } else {
            flash("mensagem", "Por favor, preencha todos os campos!", "danger");
            dbClose($conn);
            header("Location: cadastrar.php");
            exit;
        }
    }

    // Login
    if (isset($_POST['entrar'])) {
        $usuario = sanitize_input($_POST['usuario']);
        $senha = sanitize_input($_POST['senha']);
        
        if (!empty($usuario) && !empty($senha)) {
            try {
                $query = "SELECT ID, USUARIO, SENHA, STATUS, FK_NIVEL FROM LOGIN WHERE USUARIO = ?";
                $stmt = mysqli_prepare($conn, $query);
                
                if (!$stmt) {
                    throw new Exception("Erro na preparação da query: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt, 's', $usuario);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    mysqli_stmt_bind_result($stmt, $id, $db_usuario, $db_senha, $status, $nivel);
                    mysqli_stmt_fetch($stmt);
                    
                    if (password_verify($senha, $db_senha)) {
                        $_SESSION['id'] = $id;
                        $_SESSION['usuario'] = $db_usuario;
                        $_SESSION['status'] = $status;
                        $_SESSION['nivel'] = $nivel;
                        
                        dbClose($conn);
                        $location = ($nivel == 1) ? "admin/" : "home/";
                        header("Location: " . $location);
                        exit;
                    } else {
                        flash("mensagem", "Senha incorreta!", "danger");
                    }
                } else {
                    flash("mensagem", "Usuário não encontrado!", "danger");
                }
            } catch (Exception $e) {
                error_log("Erro no login: " . $e->getMessage());
                flash("mensagem", "Erro ao realizar login: " . $e->getMessage(), "danger");
            }
        } else {
            flash("mensagem", "Por favor, preencha todos os campos!", "danger");
        }
        dbClose($conn);
        header("Location: index.php");
        exit;
    }

    if (isset($conn)) {
        dbClose($conn);
    }
    header("Location: index.php");
    exit;
}
?>