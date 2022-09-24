# liligiroud1980

- Pour cloner le projet: 
```console
git clone git@github.com:lamdakara/liligiroud1980.git
```



- Ensuite executer la commande suivante pour installer les dépendances du projet ainsi sur que la mise à jour de DB : 
```console
composer install
composer dump-env dev  # Ensuite executer la commande suivante pour générer votre propre `.env.local.php` et le remplir vos identifiants de connexion
                       # à la base de    donnée ainsi les identifiants de tocken pour Stripe
php bin/console d:d:c # pour créer la base de donnée
php bin/console do:mi:migrate # pour lancer les migrations 
symfony server:start -d # pour demarrer le server 
```

Et enfin rdv sur la page https://127.0.0.1:8000/


## Scénario de deploiment

### Pré-requis 
Avoir ces logiciels d'installé sur le server :
- PHP (version supérieur ou egale à 8.1), Apache2, MySQL, Git, composer

### Etape pour le deploiement 
Une fois c'est logiciel d'installer sur le serveur, on doit executer les commandes suivantes : 
```console
cd /var/www/html # aller sur le dossier ou il ya le serveur apache
git clone git@github.com:lamdakara/liligiroud1980.git liligiroud # on clone le projet
cd liligiroud # on rentre dans le projet
composer install # installe les dépendances
composer dump-env prod # On doit remplir ensuite les informations sur les token pour stripe ainsi que les informations de la connexion à la base de donnée
php bin/console do:mi:migrate # on lance les migrations
php bin/console c:c # on vide le cache
sudo chown -R $USER:www-data  var/ public/ # On donne le droit à l'utilisateur connecté sur le serveur ainsi que groupe www-data (navigateur) d'accès au dossier public
```

### Après il faut configurer le vituale host
```console
sudo nano /etc/apache2/sites-available/000-default.conf
```

il faut juste changer 
```php
 DocumentRoot /var/www/html/liligiroud/public/
```
après il faut redemarer Apache : 
```console
sudo service apache2 restart
```
ET normalement des qu'on se rend sur le host de notre serveur on devrait à voir la page d'accueil http://notre_serveur.com/
