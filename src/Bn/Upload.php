<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

/**
 * Gestion des pieces jointes
 */
class Bn_upload
{
	/**
	 * Creation d'un dossier :
	 * on suppose que le chemin du dossier part de la racine du site
	 */
	function _mkdir($directory)
	{
		// Verifier l'existence du dossier
		if ( is_dir('../' . $directory) )
		{
			return true;
		}


		// Liste des dossiers

		// Si le safemode est actif, utiliser la connexion ftp
		if ( ini_get('safe_mode') )
		{
			// Recuperer les infos de connexion
			$server = Bn::getConfigValue('ftp', 'ftp');
			$user   = Bn::getConfigValue('user', 'ftp');
			$pwd    = Bn::getConfigValue('pwd', 'ftp');
			$home   = Bn::getConfigValue('rep', 'ftp');
			$connection = ftp_connect($server);

			// Se connecter et se placer dans le bon dossier
			if ( $connection === false )
			{
				echo("Connexion impossible au serveur [$server]");
				return false;
			}
			$res = ftp_login($connection, $user, $pwd);
			if ( $res === false )
			{
				echo("Echec de l'identification [$user]");
				return false;
			}
			$res = ftp_chdir($connection, $home);
			if ( $res === false )
			{
				echo("Modification dossier courant impossible [$home]");
				return false;
			}

			// Traitement des dossiers
			$location = '..';
			$reps = explode('/', $directory);
			foreach($reps as $rep)
			{
				$location .= '/' . $rep;
				// Le dossier existe, passage au suivant
				if ( ! is_dir($location) )
				{
					$res = ftp_mkdir($connection, $rep);
					if ( $res === false )
					{
						echo("Creation du dossier impossible [$rep]");
						return false;
					}
					$res = ftp_site($connection, 'CHMOD 0777 ' .  $rep);
					if ( $res === false )
					{
						echo("Modification des autorisations impossible [$rep]");
						return false;
					}
				}
				$res = ftp_chdir($connection, $rep);
				if ( $res === false )
				{
					echo("Modification dossier courant impossible [$rep]");
					return false;
				}
			}
			ftp_close($connection);
		}
		// Le safe mode n'est pas actif, utiliser la fonction classique
		else
		{
			$location = '..';
			$reps = explode('/', $directory);
			foreach($reps as $rep)
			{
				$location .= '/'. $rep;
				// Le dossier existe, passage au suivant
				if ( is_dir($location) )
				{
					continue;
				}

				// Creation du dossier
				$res = mkdir($location);
				if ( $res === false )
				{
					echo("Creation du dossier impossible [$location]");
					return false;
				}
				$res = chmod($location, 0777);
				if ( $res === false )
				{
					echo("Modification des autorisations impossible [$location]");
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Renvoi les pieces jointes d'une FT pour affichage
	 */
	function upload($file, $dest)
	{
		$msg = '';
		// Verification et creation du dossier destination si besoin
		if ( !Bn_upload::_mkdir($dest) )
		{
			return false;
		}

		//Upload du fichier
		require_once "HTTP/Upload.php";
		$upload = new HTTP_Upload("fr");
		$file = $upload->getFiles($file);
		$msg = null;
		if ($file->isValid())
		{
			$moved = $file->moveTo('../' . $dest);
			if (PEAR::isError($moved))
			{
				$msg = $moved->getMessage();
			}
		}
		elseif ($file->isMissing())
		{
			$msg = 'Fichier manquant';
		}
		elseif ($file->isError())
		{
			$msg =  $file->errorMsg();
		}
		if ( !empty($msg) )
		{
			echo ($msg . ' ' . $dest);
			return false;
		}
		return $file;
	}
}
?>
