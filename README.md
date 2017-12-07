# Kubithon.org

Dépôt contenant le site web de Kubithon.

## Installation ou mise à jour

Idéalement, cloner le dépôt avec l'option `--recursive`. En cas d'oubli, ou de récupération tardive, cloner les sous-modules avec les commande (exécutées à la racine) :

```bash
git submodule init
git submodule update
```

Ensuite :
```bash
composer install
php bin/console doctrine:database:create
php bin/console doctrine:schema:create  # ou :update --force si mise à jour

# Important en développement !
php bin/console assets:install --symlink

# Et pour avoir un serveur (pourquoi pas)
php bin/console server:run
```

## Styles

Pour compiler les fichiers SCSS (ayant `sass` d'installé sur la machine) : 
```bash
make styles
```
… ou pour compiler au moindre changement : 
```bash
make watch
```

Pour un développement ne concernant pas le _front-end_, ceci n'est pas nécessaire : les fichiers CSS compilés sont inclus dans le dépôt pour plus de simplicité de déploiement.
