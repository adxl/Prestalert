# Prestalert

Prestashop top banner module

# Installation

- `docker network create prestalert-net`  
- `docker run -ti --name prestalert-mysql --network prestalert-net -e MYSQL_ROOT_PASSWORD=admin -p 3307:3306 -d mysql:5.7`

Dans le conteneur prestalert-mysql :  
- `mysql -u root -p` 
- (mdp : admin)
- `CREATE DATABASE prestashop;`
- (détacher le conteneur)

- `docker run -ti --name prestalert --network prestalert-net -e DB_SERVER=prestalert-mysql -v $PWD/modules/prestalert:/var/www/html/modules/prestalert -p 8080:80  prestashop/prestashop:1.6`  

- (http://localhost pour installer)

Dans le conteneur prestalert :  
- `rm -rf install` 
- `mv admin admin947jlubzjh`

- (http://localhost:8080 pour installer)
