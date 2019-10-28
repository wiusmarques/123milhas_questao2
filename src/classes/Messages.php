<?php

abstract class Messages{
    
    /* 
    * 
    * Manager Messages
    *
    */

    protected function getMessageAddSpaceSuccess(){
        return json_encode(array(
            "status" => 1,
            "message" => "Vaga Cadastrada com sucesso",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageAddSpaceExist(){
        return json_encode(array(
            "status" => 1,
            "message" => "Essa vaga já existe em nosso sistema.",
        ), JSON_UNESCAPED_UNICODE);
    }
    
    protected function getMessageRemoveSpaceSuccess($name){
        return json_encode(array(
            "status" => 1,
            "message" => "A vaga " . $name . " foi removida com sucesso.",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageSpaceNotFound(){
        return json_encode(array(
            "status" => 0,
            "message" => "Não foi encontrada nenhuma vaga com o nome informado",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageSpaceUnavailable(){
        return json_encode(array(
            "status" => 0,
            "message" => "Não há nenhuma vaga disponível no momento",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageSpaceHasCar($board){
        return json_encode(array(
            "status" => 0,
            "message" => "A vaga não pode ser removida, o veículo com a placa " . $board . " está utilizando a vaga no moneto, faça a baixa e tente novamente.",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageStringSizeIcorrect(){
        return json_encode(array(
            "status" => 0,
            "message" => "O nome da vaga não pode ter menos que dois caracters.",
        ), JSON_UNESCAPED_UNICODE);
    }
    protected function getMessageInvalidBoard(){
        return json_encode(array(
            "status" => 0,
            "message" => "A placa informada é inválida",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageCarAlocateOnSpace($space_name, $board){
        return json_encode(array(
            "status" => 1,
            "message" => "A vaga " . $space_name . " foi reservada para o veóculo " . $board . " e o tempo de uso já está contatndo.",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageErroToAlocateCarOnSpace(){
        return json_encode(array(
            "status" => 0,
            "message" => "Erro na liberação de vaga, tente novamente ou entre em contato com um administrador",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageCarExist(){
        return json_encode(array(
            "status" => 0,
            "message" => "Esse carro já está registrado em uma vaga",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageSpaceToCarNotFound(){
        return json_encode(array(
            "status" => 0,
            "message" => "O veículo solicitado não está estacionado em nossa garagem",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageWithFinalPrice($ammout, $start_reservation, $totalTime){
        return json_encode(array(
            "status" => 1,
            "price" => "R$" . $ammout,
            "value" => $ammout,
            "inicial_date" => $start_reservation,
            "total_time" => $totalTime,
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageCarNotFound($board){
        return json_encode(array(
            "status" => 0,
            "message" => "O pagamento não foi processado o veículo " . $board . " não foi localizado.",
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageRequireMoreManey($debit, $value){
        return json_encode(array(
            "status" => 0,
            "message" => "O valor pago é menor do que o débito da conta",
            "debito" => $debit,
            "value_payment" => $value,
        ), JSON_UNESCAPED_UNICODE);
    }

    protected function getMessageTransactionSucess($log){
        return json_encode(array(
            "status" => 1,
            "message" => "A transação foi registrada com sucesso, após a liberação do troco o carro poderá ser liberado",
            $log,
        ), JSON_UNESCAPED_UNICODE);
    }

}

?>