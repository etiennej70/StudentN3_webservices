# StudentN3_webservices

Webservices tournant sur serveur permettant de stocker les infos et de gérer l'authentification des utilisateurs sur l'application StudentN3.

### AddBonPlan

Ce webservice permet d'ajouter un bon plan. Il prend en entrée les paramètres POST suivant :
- token : le token d'authentification de l'utilisateur
- nom : le nom du bon plan
- adresse : l'adresse du bon plan, au format "N rue exemple, Ville"
- description : description du bon plan
- type : type du bon plan
- longitude : longitude de l'adresse
- latitude : latitude du bon plan
- date de début : date de début du bon plan, au format dd-mm-yyyy
- date de fin (optionnelle) : même format

Retour : JSON avec le message d'erreur ou "Success" si tout s'est bien passé.

### checkToken

Ce webservice permet de vérifier l'utilisateur pour le connecter au service. Au travers de ce service, on va vérifier que le token passé en POST existe, et que les adresses IP du requérant et celle associée au token sont cohérentes.
Le retour sera un JSON avec le message d'erreur, ou "Success" si tout s'est bien passé.

### getBonsPlans

Ce webservice renvoit tous les bons plans d'actualité, c'est à dire ceux sans date de fin ou dont a date de fin est ultérieure à la date du jour. Aucun paramètre n'est requis en entrée. Ce webservice est totalement ouvert.

### getOneBonPlan

Ce webservice permet d'obtenir les détails concernant un bon plan, à partir de son titre. Il prend en entrée le nom du bon plan, et retourne le détail du bo pla sous la forme d'un JSON. Ce webservice ne requière pas d'authentification.

### login

Ce webservice permet de gérer dl'authentification des utilisateurs sur la plateforme. Il prend en entrée en POST :
- email : l'adresse email de l'utilisateur
- passwd : le mot de passe de l'utilisateur
Il sera alors effectué ue vérification de ses informations en base de données. Si le couple correspond à un utilisateur existant, un token d'authentification lui sera attribué. En retour, le webservice retour un JSON contenant :
- le token attribué
- le prénom de l'utilisateur
- le nom de l'utilisateur
- la date de naissance de l'utilisateur
- le téléphone de l'utilisateur
- l'école de l'utilisateur

### logout

Ce webservice permet de gérer la déconnexion de l'utilisateur quand il le souhaite. Le webservice prend en entrée, en POST, le token d'authentification de l'utilisateur. Il va alors le supprimer de la mémoire du serveur, et renvoyer le message d'erreur ou de succès en JSON.

### register

Ce webservice permet d'enregistrer un nouvel utilisateur sur le système. Il prend en entrée, en POST :
- nom : le nom de l'utilisateur
- prenom : le prénom de l'utilisateur
- dateNaissance : la date de naissance de l'utilisateur, au format dd-mm-yyyy
- email : l'adresse email de l'utilisateur
- ecole : l'école de l'utilisateur
- motdepasse : le mot de passe de l'utilisateur

En retour, on obtient, au format JSON, le message d'erreur ou "Success" si tout s'est bien passé.

