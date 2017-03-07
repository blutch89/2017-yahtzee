# Description
Jeu Yahtzee multijoueur développé comme carte de visite.

# Technologies
## Backend
* PHP
* Symfony 2.8

## Frontend
* Bootstrap 3.3
* Javascript
* AngularJS 1.5
* jQuery 2.2

# Installation
1. Copier les fichiers sur un serveur PHP
2. Créer une base de données
3. Relier l'application à la base de données en modifiant le fichier "app/config/parameters.yml"
4. Créer les tables en exécutant cette commande : "php app/console doctrine:schema:update --force"
6. Tester l'application en se rendant sur "http://nom_serveur/web/"

# Démo
Une démo est disponible à cette adresse: "http://yahtzee.thomasgigon.ch".

Pour pouvoir jouer à ce jeu, il faut au moins être 2 joueurs. Vous pouvez simuler un 2ème joueur en utilisant le mode "navigation privée" de votre navigateur.
