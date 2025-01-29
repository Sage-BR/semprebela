<?php 
session_start(); // Importante adicionar se não estiver em outro arquivo

require_once '../functions/functions.php';
require_once '../functions/config.php';
require_once '../functions/conexao.php';
require_once '../functions/database.php';

// Verificação de login
if(!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])){
    flash("mensagem", "É necessário estar logado para acessar essa área!", "danger");
    header("Location: ../index.php");
    exit;
}

// Verificação de nível de acesso - área exclusiva para usuários (nível 2)
if($_SESSION['nivel'] != 2){
    flash("mensagem", "Você não tem permissão para acessar essa área!", "danger");
    header("Location: ../admin/");  // Redireciona admin para sua área correta
    exit;
}

// Verificação de status
if($_SESSION['status'] != 'S'){
    header("Location: cadastro/");
    exit;
}

// Incluindo os arquivos necessários
require_once '../includes/cabecalho.php';
require_once './includes/menu.php';
?>

<div class="container-fluid page-body-wrapper">
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <?= getFlash('mensagem') ?>
                        <div class="d-flex flex-column mt-5">                                    
                            <h3 class="text-primary col-sm-1 align-self-center">Olá! Seja Bem Vindo(a)!</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>