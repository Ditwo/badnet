<?php 
define('LOC_P_ACCOUNT_INFOS','Saisissez votre mot de passe et le code de sécurité pour terminer la création de votre compte'); 
define('LOC_MSG_ACCOUNT_EXIST','Un compte existe déjà pour ce joueur. Si vous pensez être victime d\'une usurpation, contacter le modérateur à partir de la page d\'accueil.'); 
define('LOC_MSG_UNKNOW_PLAYER','Numéro de licence inconnu dans la base féférale.'); 
define('LOC_MSG_BAD_BORN','La date de naissance de naissance ne correspond pas à celle enregistrée dans la base de données fédérale.'); 
define('LOC_LABEL_BORN','Date de naissance'); 
define('LOC_LABEL_LICENSE','Numéro de licence'); 
define('LOC_TITLE_ACCOUNT_LICENSE','Numéro de licence'); 
define('LOC_P_ACCOUNT_CLUB','Vous avez choisi la création d\'un compte Association. Vous pourrez l\'utiliser pour créer et gérer les compétitions que vous organisez, gérer les inscriptions de vos joueurs a toutes les compétitions déclarées. 
      Il ne pourra pas être converti en compte simple ou Joueur.'); 
define('LOC_TITLE_ACCOUNT_CLUB','Création d\'un compte Association'); 
define('LOC_P_ACCOUNT_PLAYER','Vous avez choisi la création d\'un compte Joueur. Vous pourrez l\'utiliser pour consulter vos résultats et vous inscrire en ligne aux compétitions déclarées. Il ne pourra pas être converti en compte simple ou Association.
      Saisissez votre numéro de licence et votre date de naissance. Ces informations doivent être conformes à celles enregistrées dans la base de données fédérale.'); 
define('LOC_TITLE_ACCOUNT_PLAYER','Création d\'un compte joueur'); 
define('LOC_P_ACCOUNT_OTHER','Vous avez choisi la création d\'un compte simple. Vous pourrez l\'utiliser pour devenir capitaine d\une équipe interclubs, ou gestionnaire d\'une compétition. Il pourra être converti en compte Association en le reliant à l\'association de votre choix.
      Il ne pourra pas être converti en compte Joueur.'); 
define('LOC_TITLE_ACCOUNT_OTHER','Création d\'un compte simple'); 
define('LOC_LABEL_OTHER_DESC','Utiliser ce type de compte si vous devez être capitaine d\'équipe interclubs ou gestionnaire d\'une ou plusieurs compétitions'); 
define('LOC_LABEL_OTHER','Compte simple.'); 
define('LOC_LABEL_CLUB_DESC','Ce type de compte est relié à une association affiliée à la fédération. Il permet de créer des compétitions, inscrire des équipes aux interclubs, gérer les joueurs de l\'association...'); 
define('LOC_LABEL_CLUB','Compte association.'); 
define('LOC_LABEL_PLAYER','Compte joueur.'); 
define('LOC_LABEL_PLAYER_DESC','Le compte Joueur est un compte personnel. Seul les joueurs licenciés peuvent utiliser ce type de compte. L\'identifiant est le numéro de licence. Ce compte permet de consulter son classement et permettra d\'utiliser le module d\'inscription en ligne aux tournois.'); 
define('LOC_P_ACCOUNT','Choisissez le type de compte en fonction de vos besoins. Le création de compte est gratuite; N\'hésitez pas à créer plusieurs compte pour séparer les fonctions que vous pouvez cumuler. Si vous êtes à la fois Juge-arbitre, responsable de d\'association et capitaine d\'équipe interclubs, créez un compte pour chaque fonction.'); 
define('LOC_TITLE_ACCOUNT2','Choix du type de compte'); 
define('LOC_TITLE_ACCOUNT_TYPE','Type de compte'); 
define('LOC_MSG_ERR_EMAIL_ACCOUNT','Une erreur s\'est produite lors de l\'envoi du message.'); 
define('LOC_LNK_BACK_HOME','Retour à la page d\'accueil'); 
define('LOC_MSG_ERRORLOGIN','Identifiant ou mot de passe incorrect. Si vous avez oublié l\'un ou l\'autre, utilisez le lien "Mot passe oublié"'); 
define('LOC_TITLE_BADLOGIN','Echec identification'); 
define('LOC_TITLE_LOGIN','Connexion'); 
define('LOC_MSG_SEND_PWD','Un nouveau mot de passe vous a été envoyé.'); 
define('LOC_MSG_ERR_EMAIL','Votre nouveau mot de passe n\'a pas pu être envoyé. Essayer ultérieurement.'); 
define('LOC_MSG_BAD_EMAIL','Cet email est inconnu.'); 
define('LOC_MSG_ACCOUNT_VALID','Bonjour,
      
votre compte est maintenant opérationnel.

--------------------------------
Identifiant : %bnid%
Mot de passe : %bnpwd%
--------------------------------
'); 
define('LOC_MSG_ACCOUNT_CONFIRM','Confirmation de compte'); 
define('LOC_EMAIL_BODY','Bonjour,
      
une demande de nouveau mot de passe viens d\'être effectuée pour votre compte.
Voici vos nouvelles informations de connexion.

------------------------------
Identifiant : %bnid%
Mot de passe : %bnpwd%
------------------------------
'); 
define('LOC_EMAIL_OBJECT_PWD','Mot de passe'); 
define('LOC_P_ASK_PWD','Saisissez votre adresse email et un nouveau mot de passe vous sera envoyé.'); 
define('LOC_TITLE_NEW_PASSWORD','Demande de mot de passe'); 
define('LOC_MSG_LOGIN_NOT_BLANK','L\'identifiant ne doit pas comporter de caractère "espace"'); 
define('LOC_MSG_LOGIN_ALREADY_USED','Cet identifiant est déjà utilisé. Choisissez-en un autre.'); 
define('LOC_MSG_BAD_DIFFPWD','Les deux mots de passe doivent être identiques.'); 
define('LOC_MSG_LOGIN_NOT_PWD','Pour raison de sécurité, le mot de passe doit être différent de l\'identifiant.'); 
define('LOC_MSG_EMAIL_ALREADY_USED','Cette adresse email est déjà utilisée !'); 
define('LOC_MSG_BAD_CODE','Le code image est incorrect. Essayez encore.'); 
define('LOC_MSG_AQUIT_DEMANDE','Votre demande de création de compte a bien été enregistrée. Vous allez recevoir un courrier électronique vous précisant la marche à suivre pour terminer votre enregistrement.'); 
define('LOC_BODY_DEMANDE','Bonjour,
      
une demande de création de compte avec votre adresse email vient d\'être effectuée.
Si vous n\'êtes pas l\'auteur de cette demande, ignorer simplement ce message.
Pour confirmer votre demande de création de compte utiliser le lien ci-dessous.

========================================
Confirmer votre compte
ATTENTION: CE LIEN EST ACTIF 3 JOURS SEULEMENT

%bnconfirm%

========================================

Salutation sportives.
'); 
define('LOC_OBJECT_DEMANDE','Demande de création de compte'); 
define('LOC_P_EMAIL','Un courrier électronique avec des instructions vous parviendra.'); 
define('LOC_P_CAPTCHA','Afin de sécuriser le processus de demande de compte, veuillez reporter le nombre de l\'image dans le champ "Nombre image"'); 
define('LOC_P_PSEUDO','Le nom est l\'information qui sera utilisée dans les courriers électroniques, le pseudonyme est l\'information publique qui sera affichée sur le site.'); 
define('LOC_LABEL_CAPTCHA','Nombre image'); 
define('LOC_LABEL_CONFIRM','Confirmation'); 
define('LOC_TITLE_CAPTCHA','Sécurité'); 
define('LOC_TITLE_SAISIE','Saisie des informations'); 
define('LOC_TITLE_NEW_ACCOUNT','Nouveau compte'); 
?>