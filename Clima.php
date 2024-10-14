<?php

require_once './db/Conection.php';
class Clima {
    private $conexion;
    private $apiKey = '6383b5127e7f55a0e7402f2db91783d0'; // Inserta tu API key de OpenWeather aquí

    public function __construct() {
        $this->conexion = (new Conection())->getPDO();
    }

    // Obtener el clima desde la API de OpenWeather
    public function obtenerClima($ciudad) {
        try {
            $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($ciudad) . "&appid=" . $this->apiKey . "&units=metric&lang=es";
            $response = file_get_contents($url);

            if ($response === FALSE) {
                throw new Exception("Error al obtener datos de la API.");
            }

            $data = json_decode($response, true);
            if (isset($data['cod']) && $data['cod'] == 200) {
                // Procesar los datos de la API
                $temperatura = $data['main']['temp'];
                $descripcion = $data['weather'][0]['description'];

                // Guardar los datos en la base de datos
                $this->guardarDatos($ciudad, $temperatura, $descripcion);
                return [
                    'ciudad' => $ciudad,
                    'temperatura' => $temperatura,
                    'descripcion' => $descripcion
                ];
            } else {
                throw new Exception("Error al obtener el clima para la ciudad: $ciudad");
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            return null;
        }
    }

    // Guardar datos en la base de datos
    private function guardarDatos($ciudad, $temperatura, $descripcion) {
        try {
            $query = "INSERT INTO datos (ciudad, temperatura, descripcion_clima) VALUES (:ciudad, :temperatura, :descripcion)";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':ciudad', $ciudad);
            $stmt->bindParam(':temperatura', $temperatura);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al guardar los datos en la base de datos: " . $e->getMessage());
        }
    }
}

// Procesar la solicitud POST con datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['ciudad'])) {
    $ciudad = $data['ciudad'];
    $clima = new Clima();
    $resultado = $clima->obtenerClima($ciudad);

    if ($resultado) {
        echo json_encode([
            'mensaje' => 'Datos guardados exitosamente',
            'data' => $resultado
        ]);
    }
} else {
    echo json_encode(['error' => 'No se proporcionó el nombre de la ciudad']);
}