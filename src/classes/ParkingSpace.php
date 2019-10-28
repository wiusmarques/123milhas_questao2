<?php

include_once("Messages.php");

class ParkingSpace extends Messages
{

    private $park;
    private $prices;
    private $parkFileDirectory;
    private $priceFileDirectory;

    function __construct()
    {
        date_default_timezone_set('America/Sao_Paulo');
        $this->setParkfileDirectory();
        $this->setPriceFileDirectory();
        $this->getParkFile();
        $this->getParkPrices();
    }

    private function getParkFile(){
        $this->park = json_decode(file_get_contents($this->parkFileDirectory, "r"), true);
        
        if($this->park == null){
            
            file_put_contents($this->parkFileDirectory, json_encode([]));
            $this->park = json_decode(file_get_contents($this->parkFileDirectory, "r"), true);

        }
        
    }

    private function getParkPrices(){
        $this->prices = json_decode(file_get_contents($this->priceFileDirectory, "r"), false);
        
        if($this->prices == null){
            
            file_put_contents($this->priceFileDirectory, json_encode([]));
            $this->prices = json_decode(file_get_contents($this->priceFileDirectory, "r"), false);

        }
        
    }

    private function setParkfileDirectory(){
        $this->parkFileDirectory = dirname($_SERVER['DOCUMENT_ROOT']) . "\\src\\data\\vagas.json";
    }

    private function setPriceFileDirectory(){
        $this->priceFileDirectory = dirname($_SERVER['DOCUMENT_ROOT']) . "\\src\\data\\prices.json";
    }

    public function removeSpace($space_name){
        if($this->spaceExist($space_name)){

            if($this->spaceHasCar($space_name)){
                return parent::getMessageSpaceHasCar($this->park[$space_name]['board']);
            }

            if($this->removeSpage($space_name)){
                $responseMessae = parent::getMessageRemoveSpaceSuccess($space_name);
                $this->save();
                return $responseMessae;
            };



        }
        return parent::getMessageSpaceNotFound();
    }

    private function spaceHasCar($space_name){
        if(!empty($this->park[$space_name]['board'])){
            return true;
        }
        
        return false;
    }

    public function getSpaceAvailable(){
        $spaceAvailable = $this->park;
        foreach($spaceAvailable as $space){

            if(!empty($space['board'])){
                unset($spaceAvailable[$space['space_name']]);
            }
        }
        
        if(count($spaceAvailable) > 0){
            return json_encode($spaceAvailable, JSON_UNESCAPED_UNICODE);
        }

        return parent::getMessageSpaceUnavailable();
        
    }

    public function createSpace($name){
        
        if(empty($name) || strlen($name) < 2){
            return parent::getMessageStringSizeIcorrect();
        } 

        if(!$this->spaceExist($name)){
            if($this->addSpace($name)){
                return parent::getMessageAddSpaceSuccess($name);
            };
        }
        return parent::getMessageAddSpaceExist();
    }

    public function addCar($board){
        
        if(empty($board)){
            return parent::getMessageInvalidBoard();
        }

        $board = preg_replace('[-]', "", $board);

        if(preg_match('/^[A-Z]{3}[0-9]{4}$/',$board)){
            
            $spaceAvailable = json_decode($this->getSpaceAvailable(), true);
            
            if(isset($spaceAvailable['status']) && $spaceAvailable['status'] == 0){
                return parent::getMessageSpaceUnavailable();
            }


            if($this->carExist($board)){
                return parent::getMessageCarExist();
            }

            $space = array_pop($spaceAvailable);
            $space['board'] = $board;
            $space['start_reservation'] = date("Y-m-d H:i:s");

            $this->park[$space['space_name']] = $space;
            
            
            if($this->save()){
                return parent::getMessageCarAlocateOnSpace($space['space_name'], $board);
            }

            return parent::getMessageErroToAlocateCarOnSpace();
            
        }
        
        return parent::getMessageInvalidBoard();
    }

    public function payment($board, $value){
        
        if($this->carExist($board)){
            $debit = json_decode($this->getAmmount($board), false);
            $debit = floatval($debit->value);

            if($this->validPaymant($debit, $value)){
                return $this->paymentApply($board, $debit, $value);
            }

            return $this->validPaymant($debit, $value);
        }

        return parent::getMessageCarNotFound($board);
    }

    private function validPaymant($debit, $value){
        if($value >= floatval($debit)){
            return true;
        }

        return parent::getMessageRequireMoreManey($debit, $value);
    }

    private function paymentApply($board, $debit, $value){
        $space = $this->getSpacerByBoard($board);
        
        $valueToBack = floatval($value) - floatval($debit);
        
        $this->freeSpace($space);
        return $this->saveLogTransaction($space, $board, $debit, $value, $valueToBack);
        
    }

    private function freeSpace($space){
        $space->board = "";
        $space->start_reservation = "";
        
        $this->park[$space->space_name] = $space;

        $this->save();
    }

    private function saveLogTransaction($space, $board, $debit, $value, $valueToBack){
        $dataTransaction = date("YmdHis");
        $logFileDirectory = dirname($_SERVER['DOCUMENT_ROOT']) . "\\src\\data\\transaction_" . $board .  "_" . $dataTransaction . ".json";

        $log = array(
            "space_information" => $space,
            "board" => $board,
            "receve_value" => $value,
            "debit_value" => $debit,
            "value_to_back" => $valueToBack,
            "transaction_date" => $dataTransaction
        );

        try{
            file_put_contents($logFileDirectory, json_encode($log));
            return parent::getMessageTransactionSucess($log);
        }catch(Exception $e){

        }
    }

    
    public function getAmmount($board){
        
        $board = preg_replace('[-]', "", $board);

        if(!$this->getSpacerByBoard($board)){
            return parent::getMessageSpaceToCarNotFound();
        }

        $space = $this->getSpacerByBoard($board);

        
        $ammoutInformations = $this->calcAmmount($space->start_reservation);
        return parent::getMessageWithFinalPrice($ammoutInformations['value'], $space->start_reservation, $ammoutInformations['total_time']);

    }

    private function calcAmmount($start_reservation){
        
        $date_time = new DateTime($start_reservation);
        $diff = $date_time->diff( new DateTime());

        $timeInformation = $this->getTimeInformations($diff);
        
        if($timeInformation['month'] > 0){
            $timeInformationUsed = array('month' => 1,'days' => 30, 'hours' => 24, 'minuts' => 60);
            return $this->calculate($timeInformation, $timeInformationUsed, $this->prices->month, $diff);

        }

        if($timeInformation['days'] > 0){

            $timeInformationUsed = array('days' => 1, 'hours' => 24, 'minuts' => 60);
            return $this->calculate($timeInformation, $timeInformationUsed, $this->prices->day, $diff);

        }

        if($timeInformation['hours'] > 0){

            $timeInformationUsed = array('hours' => 1, 'minuts' => 60);
            return $this->calculate($timeInformation, $timeInformationUsed, $this->prices->hour, $diff);

        }

        if($timeInformation['minuts'] > 0){
            
            $timeInformationUsed = array('minuts' => 60);
            return $this->calculate($timeInformation, $timeInformationUsed, $this->prices->hour, $diff);

        }

        return array(
            "value" => number_format(0, 2, '.', ','),
            "total_time" => ('0 mês(s), 0 dia(s), 0 hora(s), 0 minuto(s)'),
        );
    }

    private function getTimeInformations($diff){
        return array(
            "minuts" => $diff->format('%i'),
            "hours" => $diff->format('%h'),
            "days" => $diff->format('%d'),
            "month" => $diff->format('%m'),
        );
    }

    private function calculate($timeInformation, $timeInformationUsed, $valueRefecence, $diff){
        $ammout = 0;

        foreach($timeInformationUsed as $key => $value){

            $ammout += $timeInformation[$key] * $valueRefecence / $value;

        }
        $ammout = number_format($ammout, 2, '.', ',');
        //var_dump($ammout);
        return array(
            "value" => $ammout,
            "total_time" => $diff->format('%m mês(s), %d dia(s), %H hora(s), %i minuto(s)'),
        );
        
    }

    public function getSpacerByBoard($board){
        $board = preg_replace('[-]', "", $board);

        $park = json_decode(file_get_contents($this->parkFileDirectory, "r"), false);
        
        foreach($park as $space){
            if($space->board == $board){
                return $space;
            }
        }
        return false;
    }

    private function carExist($board){
        $park = json_decode(file_get_contents($this->parkFileDirectory, "r"), false);
        $board = preg_replace('[-]', "", $board);
        foreach($park as $space){
            if($space->board == $board){
                return true;
            }
        }
        return false;
    }

    private function spaceExist($space_name){
        $keyExist = array_key_exists($space_name, $this->park);
        
        if(!$keyExist){
            return false;
        }

        return true;
    }

    private function addSpace($space_name){
        $space_name = strtoupper($space_name);
        $newSpace = array(
            $space_name => array(
                "space_name" => $space_name,
                "board" => "",
                "start_reservation" => "",
            )
        );

        $this->park = array_merge($this->park, $newSpace);
        return $this->save();
    }

    private function removeSpage($name){
        unset($this->park[$name]);
        return true;
    }

    private function save(){
        try{
            file_put_contents($this->parkFileDirectory, json_encode($this->park));
            return true;
        }catch(Exception $e){
            return $e->getMessage();
            
        }
    }

    

}

?>
