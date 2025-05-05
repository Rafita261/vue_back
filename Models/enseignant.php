<?php
require("./../Models/db.php");

class Enseignant
{
    private $num_ens, $nom, $nbheures, $tauxhoraire;

    public function __construct($nom, $nbheures, $tauxhoraire, $num_ens = null)
    {
        $this->num_ens = $num_ens;
        $this->nom = $nom;
        $this->nbheures = $nbheures;
        $this->tauxhoraire = $tauxhoraire;
    }

    // Getters
    public function get_num()
    {
        return $this->num_ens;
    }

    public function get_nom()
    {
        return $this->nom;
    }

    public function get_nbheures()
    {
        return $this->nbheures;
    }

    public function get_taux_horaires()
    {
        return $this->tauxhoraire;
    }

    public function get_salaire()
    {
        return $this->nbheures * $this->tauxhoraire ;
    }

    // Insérer un enseignant dans la base de données
    public function insert_into_database()
    {
        global $pdo;

        try {
            $sql = "INSERT INTO ENSEIGNANT (nom, nbheures, tauxhoraire) VALUES (?, ?, ?)";
            $query = $pdo->prepare($sql);
            $query->execute([$this->nom, $this->nbheures, $this->tauxhoraire]);

            // Récupérer l'ID auto-incrémenté
            $this->num_ens = $pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'insertion : " . $e->getMessage());
        }
    }

    // Mettre à jour un enseignant dans la base de données
    public function update_in_database()
    {
        global $pdo;

        if ($this->num_ens === null) {
            throw new Exception("Impossible de mettre à jour : l'enseignant n'existe pas dans la base.");
        }

        try {
            $sql = "UPDATE ENSEIGNANT SET nom = ?, nbheures = ?, tauxhoraire = ? WHERE num_ens = ?";
            $query = $pdo->prepare($sql);
            $query->execute([$this->nom, $this->nbheures, $this->tauxhoraire, $this->num_ens]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour : " . $e->getMessage());
        }
    }

    // Supprimer un enseignant de la base de données
    public function delete_from_database()
    {
        global $pdo;

        if ($this->num_ens === null) {
            throw new Exception("Impossible de supprimer : l'enseignant n'existe pas dans la base.");
        }

        try {
            $sql = "DELETE FROM ENSEIGNANT WHERE num_ens = ?";
            $query = $pdo->prepare($sql);
            $query->execute([$this->num_ens]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression : " . $e->getMessage());
        }
    }

    // Méthode statique pour lister tous les enseignants
    public static function list_all()
    {
        global $pdo;

        try {
            $sql = "SELECT * FROM ENSEIGNANT";
            $query = $pdo->query($sql);
            $enseignants = [];

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $enseignants[] = new Enseignant(
                    $row['nom'],
                    $row['nbheures'],
                    $row['tauxhoraire'],
                    $row['num_ens']
                );
            }

            return $enseignants;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des enseignants : " . $e->getMessage());
        }
    }
}
