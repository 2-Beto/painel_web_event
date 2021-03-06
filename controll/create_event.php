<?php
session_start();

require 'functions.php';
require '../connect/connect.php';

foreach ($_POST as $key => $value){
    echo "{$key} = {$value}\r\n";
}

$etapa = $_SESSION['etapa'];
$retornar = $_SESSION['retorna'];	

// Adiciona cliente e dados básicos do evento
if($etapa == 1){
    
    // verifica se já tem cliente
    if(!isset($_POST['cliente_id'])){
        $cliente_id = "";
    } else{
        $cliente_id = mysqli_real_escape_string($link, $_POST['cliente_id']);
    }
    
    // Pega dados gerais
    $evento_nome = mysqli_real_escape_string($link, $_POST['evento_nome']);
    $evento_data = mysqli_real_escape_string($link, $_POST['evento_data']);
    $evento_hora = mysqli_real_escape_string($link, $_POST['evento_hora']);
    
    // se o cliente não tem cadastro, pega os dados e sobe
    if($cliente_id == ""){
        $cliente_nome = mysqli_real_escape_string($link, $_POST['cliente_nome']);
        $cliente_site = mysqli_real_escape_string($link, $_POST['cliente_site']);
        $cliente_responsavel = mysqli_real_escape_string($link, $_POST['cliente_responsavel']);
        $cliente_logo = $_FILES['cliente_logo'];
        
        $cliente_id = add_cliente($cliente_nome, $cliente_site, $cliente_responsavel, $cliente_logo);
        $_SESSION['cliente_id'] = $cliente_id;
    } 
    
    // insere o novo evento e retorna id
    $evento_id = add_evento($cliente_id, $evento_nome, $evento_data, $evento_hora);
    $_SESSION['evento_id'] = $evento_id;
    
    // muda etapa e redireciona
    $_SESSION['etapa'] = 2;
}

// Adiciona convidados e personalização
if($etapa == 2){
    $evento_id = $_SESSION['evento_id'];
    
    if(!isset($_GET['f'])){
        $f = "";
    } else{
        $f = mysqli_real_escape_string($link, $_GET['f']);
    }
    if($f == 'add'){
        $convidados_nome = mysqli_real_escape_string($link, $_POST['convidados_nome']);
        $convidados_curriculo = mysqli_real_escape_string($link, $_POST['convidados_curriculo']);
        $convidados_bio = mysqli_real_escape_string($link, $_POST['convidados_bio']);
        $convidados_foto = $_FILES['convidados_foto'];
        $add_convidado = add_convidado($evento_id, $convidados_nome, $convidados_curriculo, $convidados_bio, $convidados_foto);
        if($add_convidado == 1){
            $get_convidados = get_convidados($evento_id);
            $_SESSION['list_convidados'] = array();
            
            $i = 0;
            if ($get_convidados->num_rows > 0) {
                while($row = $get_convidados->fetch_assoc()) { 
                    $_SESSION['list_convidados'][$i] = $row;
                    $i++;
                }
            }
        }
        
    } else if($f == 'edit'){
        $edit_convidados_nome = mysqli_real_escape_string($link, $_POST['edit_convidados_nome']);
        $edit_convidados_curriculo = mysqli_real_escape_string($link, $_POST['edit_convidados_curriculo']);
        $edit_convidados_bio = mysqli_real_escape_string($link, $_POST['edit_convidados_bio']);
        $edit_convidados_id = mysqli_real_escape_string($link, $_POST['edit_convidados_id']);
        if($_FILES['edit_convidados_foto']['size'] == 0){
            $edit_convidados_foto = 0;
        } else {
            $edit_convidados_foto = $_FILES['edit_convidados_foto'];
        }
        $edit_convidado = edit_convidado($edit_convidados_id, $edit_convidados_nome, $edit_convidados_curriculo, $edit_convidados_foto, $edit_convidados_bio);
        if($edit_convidado == 1){
            $get_convidados = get_convidados($evento_id);
            $_SESSION['list_convidados'] = array();
            
            $i = 0;
            if ($get_convidados->num_rows > 0) {
                while($row = $get_convidados->fetch_assoc()) { 
                    $_SESSION['list_convidados'][$i] = $row;
                    $i++;
                }
            }
        }
        
    } else if($f == 'del'){
        $del_convidados_id = mysqli_real_escape_string($link, $_POST['del_convidados_id']);
        $del_convidado = del_convidado($del_convidados_id);
        if($del_convidado == 1){
            $get_convidados = get_convidados($evento_id);
            $_SESSION['list_convidados'] = array();
            
            $i = 0;
            if ($get_convidados->num_rows > 0) {
                while($row = $get_convidados->fetch_assoc()) { 
                    $_SESSION['list_convidados'][$i] = $row;
                    $i++;
                }
            }
        }
    } else {
        $personalizacao_bg = $_FILES['personalizacao_bg'];
        $personalizacao_logo = $_FILES['personalizacao_logo'];
        $personalizacao_cor1 = mysqli_real_escape_string($link, $_POST['personalizacao_cor1']);
        $personalizacao_cor2 = mysqli_real_escape_string($link, $_POST['personalizacao_cor2']);
        if(isset($_POST['tipo_de_convidados'])){
            $tipo_de_convidados = mysqli_real_escape_string($link, $_POST['tipo_de_convidados']);
        } else {
            $tipo_de_convidados = 0;
        }
        $add_personalizacao = add_personalizacao($evento_id, $personalizacao_bg, $personalizacao_logo, $personalizacao_cor1, $personalizacao_cor2, $tipo_de_convidados);
        if($add_personalizacao == 1){
            $_SESSION['etapa'] = 3;
        }
    }
}

if($etapa == 3){
    $evento_id = $_SESSION['evento_id'];
    $insert_linha_configuracao = insert_linha_configuracao($evento_id);
    
    if(!isset($_GET['f'])){
        $f = "";
    } else{
        $f = mysqli_real_escape_string($link, $_GET['f']);
}

    if(isset($_POST['interacao_perguntas'])){
        $interacao_perguntas = 1;
    }
    else{
        $interacao_perguntas = 0;
    }

    $interacao_codigo = mysqli_real_escape_string($link, $_POST['interacao_codigo']);
    $transmissao_player1 = mysqli_real_escape_string($link, $_POST['transmissao_player1']);
    $transmissao_player2 = mysqli_real_escape_string($link, $_POST['transmissao_player2']);
    $transmissao_traducao = mysqli_real_escape_string($link, $_POST['transmissao_traducao']);
    $add_interacao_tranmissao = add_interacao($evento_id, $interacao_perguntas, $interacao_codigo);
    $add_transmissao = add_transmissao($evento_id, $transmissao_player1, $transmissao_player2, $transmissao_traducao);
    $_SESSION['etapa'] = 4;
}
if($etapa == 4){
    
        $cadastro= new stdClass;    
        $cadastro->nome=isset($_POST['campo_nome'])?1:null;
        $cadastro->sobrenome= isset($_POST['campo_sobrenome'])?1:null;
        $cadastro->email= isset($_POST['campo_email'])?1:null;
        $cadastro->telefone= isset($_POST['campo_telefone'])?1:null;
        $cadastro->celular= isset($_POST['campo_celular'])?1:null;
        $cadastro->empresa=  isset($_POST['campo_empresa'])?1:null;
        $cadastro->cargo= isset($_POST['campo_cargo'])?1:null;
        $cadastro->especialidade= isset($_POST['campo_especialidade'])?1:null;
        $cadastro->ufcrm= isset($_POST['campo_ufcrm'])?1:null;
        $cadastro->senha= isset($_POST['campo_senha'])?1:null;
        
        if($cadastro->senha){
            $cadastro->senha_padrao= isset($_POST['senha_padrao'])?1:null;
            $cadastro->senha_aleatoria= isset($_POST['senha_aleatoria'])?1:null;
            $cadastro->senha_campo= isset($_POST['senha_campo'])?$_POST['senha_campo']:null;
        }

        if($cadastro->senha){
            $cadastro->valida_crm= isset($_POST['valida_crm'])?1:null;
            $cadastro->valida_email= isset($_POST['valida_email'])?1:null;    
        }
                      
        foreach($cadastro as $propName => $propValue ){    
            if (($propValue)){
                $cadastro->flag=1;
                $_SESSION['etapa'] =5;
                $cadastroJson=json_encode($cadastro);
                add_cadastro($cadastroJson); 
                #$sql="INSERT INTO configuracoes (lives_idlives,player1,campos_cadastro) VALUES (10,'MegaPlay','$cadastroJson')";
                #mysqli_query($link, $sql);
                break;   
            }else{
                $_SESSION['invalid']=1;
                $cadastro->flag=0;
                $_SESSION['etapa'] =4;
            }
        }
        
       
}
if($etapa == 5){
    $evento_id = $_SESSION['evento_id'];

    if(!isset($_GET['f'])){
        $f = "";
    } else{
        $f = mysqli_real_escape_string($link, $_GET['f']);
    }

    $login_campo_nome = isset($_POST['login_campo_nome']) ?'nome' : '';
    $login_campo_sobrenome =isset($_POST['login_campo_sobrenome']) ? 'sobrenome' : '';
    $login_campo_email = isset($_POST['login_campo_email']) ?  'email' : '';
    $login_campo_telefone = isset($_POST['login_campo_telefone']) ?  'telefone' : '';
    $login_campo_celular = isset($_POST['login_campo_celular']) ? 'celular' : '';
    $login_campo_empresa = isset($_POST['login_campo_empresa']) ?  'empresa' : '';
    $login_campo_cargo = isset($_POST['login_campo_cargo']) ?  'cargo' : '';
    $login_campo_especialidade =isset($_POST['login_campo_especialidade']) ?  'especialidade' : '';
    $login_campo_uf_crm = isset($_POST['login_campo_uf_crm']) ? 'UF_CRM' : '';
    $campo_senha = isset($_POST['campo_senha']) ?  'senha' : '';
    $campos_login = "$login_campo_nome, $login_campo_sobrenome, $login_campo_email, $login_campo_telefone, $login_campo_celular, $login_campo_empresa, $login_campo_cargo, $login_campo_especialidade, $login_campo_uf_crm, $campo_senha";
    $add_login = add_login($evento_id, $campos_login);
    if(isset($_POST['login_campo_nome']) || isset($_POST['login_campo_sobrenome']) || isset($_POST['login_campo_email']) || isset($_POST['login_campo_telefone']) || isset($_POST['login_campo_celular']) || isset($_POST['login_campo_empresa']) || isset($_POST['login_campo_cargo']) || isset($_POST['login_campo_especialidade']) || isset($_POST['login_campo_uf_crm']) || isset($_POST['campo_senha'])){
        $_SESSION['etapa'] = 6;
    }else{
        $_SESSION['invalid']= 1;
    }
    
}

if($etapa == 6){
  
    $texto_email_cadastro = mysqli_real_escape_string($link, $_POST['texto_email_cadastro']);
    $texto_email_nova_senha = mysqli_real_escape_string($link, $_POST['texto_email_nova_senha']);
    $sql=("UPDATE `configuracoes` SET `mensagem_cadastro`='$texto_email_cadastro', `mensagem_reset_mail`='$texto_email_nova_senha' WHERE `id` like 1 ");
    mysqli_query($link, $sql);
    mysqli_close($link);
    $_SESSION['etapa'] = 7;
};

header('Location: ../install/');

?>