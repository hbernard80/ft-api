# ft-api

📆 13/05/2026

## Le projet

Projet Symfony de connexion à une API externe.

Extraction d'informations de l'API France Travail [https://francetravail.io](https://francetravail.io) (fréquence quotidienne) :

Pour la ville d'Amiens avec un rayon de 10 kilomètres :

| Critère publication                    | Source des offres                                                 | Type de contrat |
| -------------------------------------- | ----------------------------------------------------------------- | --------------- |
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

## Configuration France Travail

L'API France Travail utilise OAuth2 avec le grant `client_credentials`. Le point d'accès token attend les identifiants applicatifs et le scope de l'API ciblée dans un corps `application/x-www-form-urlencoded`. Pour l'API Offres d'emploi v2, renseigner ces variables dans _.env.local_ :

```dotenv
FRANCE_TRAVAIL_CLIENT_ID="votre_client_id"
FRANCE_TRAVAIL_CLIENT_SECRET="votre_client_secret"
FRANCE_TRAVAIL_TOKEN_URL="https://entreprise.francetravail.fr/connexion/oauth2/access_token?realm=/partenaire"
FRANCE_TRAVAIL_API_BASE_URL="https://api.francetravail.io"
FRANCE_TRAVAIL_SCOPE="api_offresdemploiv2 o2dsoffre"
```

Une erreur `HTTP/1.1 400 Bad Request` sur `/connexion/oauth2/access_token` indique généralement un scope absent ou différent de celui associé à l'application sur francetravail.io, ou des identifiants invalides. Vérifier la valeur exacte du scope dans l'espace France Travail IO si l'erreur persiste.

## Import quotidien des statistiques France Travail

La commande Symfony suivante interroge l'API Offres d'emploi de France Travail et enregistre ou met à jour les totaux du jour pour Amiens (`commune=80021`) dans un rayon de 10 km :

```bash
php bin/console app:ft-stats:import
```

Les six indicateurs importés sont :

* toutes les offres ;
* toutes les offres publiées depuis moins d'un jour (`publieeDepuis=1`) ;
* toutes les offres en CDI uniquement ;
* les offres publiées par France Travail uniquement ;
* les offres publiées par France Travail uniquement en CDI ;
* les offres publiées par France Travail uniquement depuis moins d'un jour (`publieeDepuis=1`).

> [!NOTE]
> Le total `Toutes sources` de l'API peut être inférieur au total affiché sur francetravail.fr : certaines offres partenaires visibles sur le site ne sont pas forcément diffusables via l'API, selon l'accord de diffusion donné par le partenaire.

Pour une fréquence quotidienne, planifier cette commande via cron ou le planificateur de l'hébergement applicatif, par exemple :

```cron
0 6 * * * cd /chemin/vers/ft-api && php bin/console app:ft-stats:import
```
