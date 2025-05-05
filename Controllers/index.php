<?php
require_once("./../Models/enseignant.php");

// Configuration des en-têtes HTTP
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Récupération de la méthode HTTP et du chemin
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/vue_back/Controllers/index.php'; // Chemin de base de l'application
$path = str_replace($basePath, '', $path);
$path = trim($path, '/'); // Supprime les barres obliques au début et à la fin

// Fonction utilitaire pour envoyer une réponse JSON
function json_response($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Fonction utilitaire pour valider les données d'entrée
function validate_input($input, $requiredFields)
{
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Le champ '$field' est requis.");
        }
    }
}

// Routage
try {
    switch (true) {
        case $method === 'GET' && $path === 'enseignants':
            // Récupérer tous les enseignants
            $enseignants = Enseignant::list_all();
            // Convertir les objets Enseignant en tableaux associatifs
            $enseignantsArray = array_map(function ($enseignant) {
                return [
                    'num_ens' => $enseignant->get_num(),
                    'nom' => $enseignant->get_nom(),
                    'nbheures' => $enseignant->get_nbheures(),
                    'tauxhoraire' => $enseignant->get_taux_horaires(),
                    'salaire' => $enseignant->get_salaire(),
                ];
            }, $enseignants);

            json_response($enseignantsArray);
            break;

        case $method === 'GET' && preg_match('/^enseignants(\/(\d+))?$/', $path, $matches):
            if (isset($matches[2])) {
            // Récupérer un enseignant par ID
            $num_ens = intval($matches[2]);
            $enseignants = Enseignant::list_all();
            $enseignants = array_filter($enseignants, fn($e) => $e->get_num() === $num_ens);
            if (empty($enseignants)) {
                throw new Exception("Enseignant non trouvé");

                    json_response(array_values($enseignant)[0]);
            }
            else {
            $enseignantsArray = array_map(function ($enseignant) {
                return [
                'num_ens' => $enseignant->get_num(),
                'nom' => $enseignant->get_nom(),
                'nbheures' => $enseignant->get_nbheures(),
                'tauxhoraire' => $enseignant->get_taux_horaires(),
                'salaire' => $enseignant->get_salaire(),
                ];
            }, $enseignants);
            json_response($enseignantsArray);
            }
        }
            break;
        case $method === 'POST' && $path === 'create':
            // Créer un nouvel enseignant
            $input = json_decode(file_get_contents('php://input'), true);
            validate_input($input, ['nom', 'nbheures', 'tauxhoraire']);

            $enseignant = new Enseignant(
                $input['nom'],
                $input['nbheures'],
                $input['tauxhoraire'],
                null
            );

            try{
                $enseignant->insert_into_database();
            }catch (Exception $e){
                throw new Exception("Échec de la création de l'enseignant");
                json_response(['error' => 'Échec de la création de l\'enseignant'], 500);
            }

            json_response(['message' => 'Enseignant créé avec succès', 'num_ens' => $enseignant->get_num()]);
            break;
        case $method === 'PUT' && $path === 'update':
            // Mettre à jour un enseignant
            $input = json_decode(file_get_contents('php://input'), true);
            validate_input($input, ['num_ens', 'nom', 'nbheures', 'tauxhoraire']);

            $enseignant = new Enseignant(
            $input['num_ens'],
            $input['nom'],
            $input['nbheures'],
            $input['tauxhoraire']
            );

            $result = $enseignant->update_in_database();
            if (!$result) {
            throw new Exception("Échec de la mise à jour de l'enseignant");
            }

            json_response(['message' => 'Enseignant mis à jour avec succès']);
            break;

        case $method === 'DELETE' && preg_match('/^delete\/(\d+)$/', $path, $matches):
            // Supprimer un enseignant
            $num_ens = intval($matches[1]);

            $enseignant = new Enseignant(null, null, null, $num_ens);
            $result = $enseignant->delete_from_database();
            if (!$result) {
            throw new Exception("Échec de la suppression de l'enseignant");
            }

            json_response(['message' => 'Enseignant supprimé avec succès']);
            break;
        default:
            http_response_code(404);
            json_response(['error' => 'Route non trouvée']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    json_response(['error' => $e->getMessage()]);
}
