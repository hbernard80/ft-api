# ft-api

📆 13/05/2026

## Le projet

Projet Symfony de connexion à une API externe.

Extraction d'informations de l'API France Travail [https://francetravail.io](https://francetravail.io) (fréquence quotidienne) :

Pour la ville d'Amiens avec un rayon de 10 kilomètres :

| Critère publication                    | Source des offres                                                 | Type de contrat |
| -------------------------------------- | ----------------------------------------------------------------- | --------------- | --------------------------------------- |
| Toutes les offres                      | Toutes sources (France Travail, jobboards, intérim, entreprises…) | Tous contrats   |
| Toutes les offres                      | France Travail uniquement                                         | Tous contrats   |
| Toutes les offres                      | Toutes sources                                                    | CDI uniquement  |
| Toutes les offres                      | France Travail uniquement                                         | CDI uniquement  |
| Offres publiées depuis moins d’un jour | Toutes sources                                                    | Tous contrats   |
| Offres publiées depuis moins d’un jour | France Travail uniquement                                         | Tous contrats   |

Affichage sur la page d'accueil et stockage de ces statistiques par date en base de données (seuls les totaux sont conservés, aucune information de contenu des offres d'emploi).

## Stack technique

* Symfony 7.4 <abbr title="Long-Term Support">LTS</abbr> (webapp)
* API Platform 4.3.3
* PHP 8.5.0
* MySQL 8.0.43
* Framework CSS Bootstrap

## Etat actuel (Done)

* Installation et configuration de Symfony
* Intégration de Bootstrap via AssetMapper.
* Création d'un compte sur l'API France Travail
* Copie des clés d'API dans le fichier _.env.local_ (non versionné car clé secrète)
* Configuration de variables d'environnement dans le fichier _config/services.yaml_
* Création d'un service _src/Service/FranceTravailClientService.php_
* Création d'une entité _src/Entity/FtStats.php_ pour le stockage des statistiques en base de données