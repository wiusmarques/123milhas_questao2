<?php
use Slim\Http\Request;
use Slim\Http\Response;


include_once("classes/Manager.php");

$app->post('/add/parking/space/', function () {
    return Manager::addParkingSpace($_POST['name']);

});


$app->post('/add/car/', function () {
    return Manager::addCar($_POST['board']);
});

$app->post('/paymant/', function () {
    return Manager::paymant($_POST['board'], $_POST['value']);
});

$app->get('/parking/space/available', function () {

    return Manager::getSpaceAvailable();;

});

$app->get('/parking/ammount/{board}', function ($request, $response, $args) {
    return Manager::getAmmount($args['board']);

});

$app->delete('/remove/parking/space/{name}', function ($request, $response, $args) {
    return Manager::removeParkingSpace($args['name']);
});


?>