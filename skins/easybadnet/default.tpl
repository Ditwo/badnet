<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>EasyBadNet</title>

<!--badnetDeclare Inline:{Events:{host:'http://www.badnet.org/badnet',order:'evnt_lastupdate DESC'}}-->
<!--badnetDeclare Local:{Events:{user:'user',pwd:'user'}}-->
<!--badnetDeclare Login:{Login:{}}-->

<!--badnetDeclare LastNews:{News:{max:5,delay:30,unique:'yes',user:'user',pwd:'user'}}-->

<!--badnetDeclare Version:{Version:'yes'}-->

<link rel="stylesheet" type="text/css" href="../skins/easybadnet/default.css" />

</head>
<body>

<div id="container">

<div id="badnethead">
   <div id="logoBadNet">
     <a href="http://www.badnet.org" >
     <img alt="BadNet" src="../skins/easybadnet/badnet.jpg" />
     </a>
   </div>
   <div id="logoEasyPhp">
     <a href="http://www.easyphp.org" >
     <img alt="BadNet" src="../skins/easybadnet/easyphp.jpg" />
     </a>
   </div>
   <div id="slogan">
      <p>La promotion du Badminton par le net</p>
   </div>


   <p style="padding:5px; font-size:10px;">
EasyBadNet est un package basé sur EasyPhp1.8 et BadNet. Il est conçu pour faciliter l'installation et l'utilisation de BadNet sur une machine locale.<span style="font-weight:bold;color:orange;">N'utilisez pas EasyBadNet pour installer BadNet sur votre site</span>. Pour ce faire, reportez-vous au manuel d'installation de BadNet disponible sur le site <a style="font-weight:bold;font-size:11px;" href="http://www.badnet.org">http://www.badnet.org</a>
   </p>
</div><!-- fin badnetHead-->


<div id="news">
  <h1> Vos dernières notes</h1>
  <p class="rappel"><span style="text-decoration:underline;">Pour ajouter une note :</span> dans la page d'accueil du tournoi, utilisez l'onglet 'Brèves', puis 'Nouveau'.</p>
  <!--badnetLastNews-->
</div><!-- fin news-->

<div id="Enligne" >
  <h1> Tournois en ligne</h1>
  <p class="rappel">Ces tournois sont hébérgés chez Badminton Netware</p>
</div><!-- fin Enligne-->



<div id="last" class="rubrique">
  <h2>Accés direct à la gestion de vos tournoi</h2>
Cliquer sur le tournoi de votre choix.
  <!--badnetLocal-->
<p>
<span style="color:red;">Attention : </span> L'accés direct s'effectue par l'intermédiaire de l'utilisateur pré-déclaré. </p>
<p><span>login :</span>user</p>
<p><span>mot de passe:</span>user</p>
<p>Si vous changez le mot de passe, vous ne pourrez plus accéder directement à vos tournois. Vous devrez alors passer par l'Espace d'administration accessible ci-dessous.
</p>

</div>

<div id="admin" class="rubrique">
  <h2>Accés à l'espace d'administration</h2>
<p>
L'Espace d'administration vous permet d'accéder aux fonctions avancées de BadNet et de créer de nouveaux tournois. Pour vous connecter, utiliser les informations de connexion suivantes:
</p>
<p><span>login :</span> admin</p>
<p><span>mot de passe:</span> admin</p>
<p><span style="color:red;">Attention : </span> si vous changer le mot de passe et vous l'oubliez, vous n'aurez aucun moyen simple de le retrouver. Consulter alors la <a href="http://www.badnet.org/">FAQ</a> de EasyBadNet pour trouver une solution. 
</p>
<!--badnetLogin-->
</div>

<div id="badnetfoot">
<p>Page proposée par <!--badnetVersion--> </p>
</div><!-- fin badnetfoot-->

</div><!-- fin container-->


</body>
</html>
