#Test visuels réalisés :

## Création de messages :
- [X] Lorsque deux utilisateurs ouvrent une nouvelle même conversation (entre deux utilisateurs uniquement), le UUID est mis en place en javascript permettant d'afficher le nouveau message simultanément chez les deux utilisateurs.
- [X] Lors de la première conversation, l'`alert` disparaît dans l'INDEX
- [X] Lors de la première conversation, le `no-message` disparaît dans le NAVBAR et le `go-message` apparaît
- [X] Lors d'une nouvelle conversation, celle-ci est ajoutée à l'INDEX et dans le NAVBAR

## Suppression d'une conversation :
- [X] Si après la suppression d'une conversation il n'y a plus de message, l'`alert` revient dans l'INDEX et le `no-message` revient dans le NAVBAR
- [X] La suppression d'une conversation ne supprime que la conversation de l'utilisateur, et non des autres
- [X] La suppression d'une conversation supprime les messages dans : INDEX, NAVBAR, CONVERSATION
- [X] On ne peut pas supprimer une converation à laquelle on a pas participé
- [X] La suppression d'une conversation raffraîchie la liste des dernières conversation dans le NAVBAR (attention, il y a un `limit`)

## Ajout de médias :
- [X] La suppression d'un média par l'utilisateur ne provoque pas d'erreur dans la conversation
- [X] L'ajout des médias les sélectionnes pour la prochaine soumission de formulaire

## Expédition du formulaire et erreur :
- [X] Une erreur est renvoyée lors de la soumission du formulaire vide
- [X] La soumission d'un formulaire sans message mais avec média sélectionne les médias après l'erreur pour message non complété
- [X] La soumission d'un formulaire valide RESET le formulaire et retire la sélection les médias

## Lecture de conversation :

## SQL :
- [ ] Vérifier les requêtes MySQL

// voir pour la date
// couleur/lien de la conversation