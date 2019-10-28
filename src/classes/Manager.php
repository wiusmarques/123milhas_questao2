<?php

include_once("ParkingSpace.php");

class Manager{


    static function getSpaceAvailable(){
        $parkingSpace = new ParkingSpace;
        return $parkingSpace->getSpaceAvailable();
    }
     
    static function addParkingSpace($name){
        
        $parkingSpace = new ParkingSpace;
        return $parkingSpace->createSpace($name);

    }

    static function removeParkingSpace($name){
        $parkingSpace = new ParkingSpace;
        return $parkingSpace->removeSpace($name);
    }

    static function addCar($board){
        $parkingSpace = new ParkingSpace;
        return $parkingSpace->addCar($board);
    }
    
    static function getAmmount($board){
        $parkingSpace = new ParkingSpace;
        return $parkingSpace->getAmmount($board);
    }

    static function paymant($board, $value){
        $parkingSpace = new ParkingSpace;
        return $parkingSpace->payment($board, $value);
    }
}

?>