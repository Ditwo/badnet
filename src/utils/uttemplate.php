<?php
/*****************************************************************************
 !   Module     : utils
 !   File       : $Source: /cvsroot/aotb/badnet/src/utils/uttemplate.php,v $
 !   Version    : $Name:  $
 !   Revision   : $Revision: 1.10 $
 !   Revised by : $Author: cage $
 !   Date       : $Date: 2007/05/19 09:56:26 $
 ******************************************************************************/

require_once "utservices.php";
require_once "PEAR.php";

/**
 * Cette classe permet d'utiliser des modeles de page
 * pour la generation de la page d'acceuil ou des page visiteurs
 *
 * @author Gerard CANTEGRIL
 *
 */

class utTemplate
{
	/**
	 * filename
	 * @var array
	 */
	var $file  = array();

	/*
	 * $_keys[key] = "key"
	 * @var array
	 */
	var $_keys = array();

	/**
	 * $_vals[key] = "value";
	 * @var array
	 */
	var $_vals = array();

	// {{{ setFile()
	/**
	* Set appropriate template files
	*
	* With this method you set the template files you want to use.
	* Either you supply an associative array with key/value pairs
	* where the key is the handle for the filname and the value
	* is the filename itself, or you define $handle as the file name
	* handle and $filename as the filename if you want to define only
	* one template.
	*
	* @access public
	* @param  mixed handle for a filename or array with handle/name value pairs
	* @param  string name of template file
	* @return bool
	*/
	function setFile($fileName = "")
	{
		if (!file_exists($fileName))
		$this->halt(sprintf("setFile: file %s does not exist.",$fileName));
		$this->file = $fileName;
	}
	// }}}

	// {{{ parse()
	/**
	* Parse handle into target
	*
	* Parses the template file
	*
	* @access public
	* @return string parsed handle
	*/
	function parse()
	{
		// Load the template file
		$template = $this->_loadFile();

		// Find vars declared in template
		$this->_getKeys($template);

		// Construct content of each vars
		$this->_expandVars();

		// Replace declaration and vars in template
		return @str_replace($this->_keys, $this->_vals, $template);
	}
	// }}}

	// {{{ _getOpenads()
	/**
	* Renvoi la campagne de pub
	*/
	function _getOpenads($id, $obj)
	{
		$args = get_object_vars($obj);
		if ( !empty($args['campagneId']))
		$campagneId = $args['campagneId'];
		else
		$campagneId = 2;
		if (@include(getenv('DOCUMENT_ROOT').'/Openads/phpadsnew.inc.php')) {
			if (!isset($phpAds_context)) $phpAds_context = array();
			$phpAds_raw = view_raw ('', $campagneId, '', '', '0', $phpAds_context);
			return $phpAds_raw['html'];
		}
		else
		return '';

	}
	//}}}

	// {{{ _getEventsInterclubs()
	/**
	* Renvoi la tables des tournois par equipes
	*
	* @access private
	* @param  string $id  nom du composant html
	* @param  obj    $obj criteres de selection des tournois
	* @return string  Chaine html avec les tournois
	*/
	function _getEventsInterclubs($id, $obj)
	{
		$args = get_object_vars($obj);

		$glue  = '';
		$where = '';
		$host  = '';
		$user  = '';
		$pwd   = '';
		$order = 1;
		foreach($args as $field=>$value)
		{
	  if ($field == 'host')
	  $host = $value;
	  else if ($field == 'user')
	  $user = $value;
	  else if ($field == 'pwd')
	  $pwd = $value;
	  else
	  {
	  	$where .= "{$glue} evnt_{$field}='{$value}'";
	  	$glue = " AND ";
	  }
		}

		$fields = array('evnt_id', 'evnt_level', 'evnt_name', 'evnt_nbvisited', 'evnt_zone');
		$order = 'evnt_level DESC, evnt_zone';
		$str = '';
		$serv = new utservices($host);
		$events = $serv->select('events', $fields, $where, $order);
		if(isset($events['errMsg']))
		{
	  $str .="<p style=\"color:blue;font-weight:bold;font-size:11px;\">{$events['errMsg']}</p>";
	  unset($events['errMsg']);
		}
		$label = array(79=>"International",
		78=>"National",
		77=>"R�gional",
		76=>"D�partemental"
		);

		if (count($events))
		{
	  $str .= "<table width=\"100%\">\n";
	  //$str .= "<table>\n";
	  $level = '';
	  foreach ($events as $event)
	  {
	  	if ($level != $event['evnt_level'])
	  	{
	  		$level = $event['evnt_level'];
	  		$str .= "<tr><td colspan=\"3\"  class=\"monthClass\">{$label[$level]}</td></tr>\n";
	  	}

	  	$str .= "<tr><td class=\"dayClass\">{$event['evnt_zone']}</td>\n";
	  	$str .= "<td class=\"eventClass\"><a href=\"{$_SERVER['PHP_SELF']}";
	  	$str .= "?kpid=events";
	  	$str .= "&kaid=98";
	  	$str .= "&eventId={$event['evnt_id']}";
	  	$str .= "&uid={$user}";
	  	$str .= "&puid={$pwd}";
	  	$str .= "\">{$event['evnt_name']}</a></td>\n";
	  	$str .= "<td class=\"eventClass\">{$event['evnt_nbvisited']}</td></tr>\n";
	  }
	  $str .= "</table>\n";
		}
		return $str;
	}
	// }}}

	// {{{ _getEventsCalendar()
	/**
	* Renvoi la liste des tournois dans une table claase par mois
	*
	* @access private
	* @param  string $id  nom du composant html
	* @param  obj    $obj criteres de selection des tournois
	* @return string  Chaine html avec les tournois
	*/
	function _getEventsCalendar($id, $obj)
	{
		$args = get_object_vars($obj);

		$glue  = '';
		$where = '';
		$host  = '';
		$user  = '';
		$pwd   = '';
		$order = 1;
		foreach($args as $field=>$value)
		{
	  if ($field == 'host')
	  $host = $value;
	  else if ($field == 'user')
	  $user = $value;
	  else if ($field == 'pwd')
	  $pwd = $value;
	  else
	  {
	  	$where .= "{$glue} evnt_{$field}='{$value}'";
	  	$glue = " AND ";
	  }
		}
		$label = array(79=>"International",
		78=>"National",
		77=>"Régional",
		76=>"Départemental"
		);


		$fields = array('evnt_id', 'evnt_firstday', 'evnt_name',
		      'evnt_nbvisited', 'evnt_zone');
		$order = 'evnt_firstday DESC';
		$str = '';
		$serv = new utservices($host);
		$events = $serv->select('events', $fields, $where, $order);
		if(isset($events['errMsg']))
		{
	  $str .="<p style=\"color:blue;font-weight:bold;font-size:11px;\">{$events['errMsg']}</p>";
	  unset($events['errMsg']);
		}
		if (count($events))
		{
	  $str .= "<table>\n";
	  $mois = '';
	  foreach ($events as $event)
	  {
	  	$month = substr($event['evnt_firstday'], 5, 2);
	  	if ($month != $mois)
	  	{
	  		$mois = $month;
	  		$month = strftime('%B %Y', mktime(substr($event['evnt_firstday'],11,2),
	  		substr($event['evnt_firstday'],14,2),
	  		substr($event['evnt_firstday'],17,4),
	  		substr($event['evnt_firstday'],5,2),
	  		substr($event['evnt_firstday'],8,2),
	  		substr($event['evnt_firstday'],0,4)));
	  		$str .= "<tr><td colspan=\"4\"  class=\"monthClass\"> $month</td></tr>\n";
	  	}
	  	$day = substr($event['evnt_firstday'],8,2).'-'.substr($event['evnt_firstday'],5,2);
	  	$str .= "<tr><td class=\"dayClass\">{$day}</td>\n";
	  	$str .= "<td class=\"eventClass\">{$event['evnt_zone']}</td>\n";
	  	$str .= "<td class=\"eventClass\"><a href=\"{$_SERVER['PHP_SELF']}";
	  	$str .= "?kpid=events";
	  	$str .= "&kaid=98";
	  	$str .= "&eventId={$event['evnt_id']}";
	  	$str .= "&uid={$user}";
	  	$str .= "&puid={$pwd}";
	  	$str .= "\">{$event['evnt_name']}</a></td>\n";
	  	$str .= "<td class=\"eventClass\">{$event['evnt_nbvisited']}</td></tr>\n";
	  }
	  $str .= "</table>\n";
		}
		return $str;
	}
	// }}}

	// {{{ _getLogin()
	/**
	* Obtention du bloc de login
	*
	* @access private
	* @param  string $id  nom du composant html
	* @param  obj    $obj criteres de selection des news
	* @return string  Chaine html avec les tournois
	*/
	function _getLogin($id, $obj)
	{
		$args = get_object_vars($obj);
		$labelClass = isset($args['labelClass'])   ? $args['labelClass'] :'kLabel';
		$legendClass = isset($args['legendClass']) ? $args['legendClass']:'kLegend';
		$loginValue = isset($args['loginValue']) ? $args['loginValue']:'';
		$pwdValue   = isset($args['pwdValue']) ? $args['pwdValue']:'';
		$str = <<<EOD
	    <form id="formLogin" method="post" action="{$_SERVER['PHP_SELF']}?kpid=cnx&amp;kaid=103">
	    <fieldset id="blkLogin" >
	    <legend class='$legendClass'>Connexion</legend>
	    <div id="divLogin">
	    <label><span class='$labelClass'>Login :</span>
	    <input type="text" name="username" id="username" value="$loginValue" size="13" maxlength="20"  />
	    </label>
	    </div>
	    <div id="divPassword">
	    <label><span class='$labelClass'>Mot de passe :</span>
	    <input type="password" name="password" id="password" value="$pwdValue" size="13" maxlength="15"  />
	    </label>
	    </div>
	    <div id="divBtn">
	    <input name="btnConnect" value=" Connecter" id="btnConnect" type="submit" />
	    </div>
	    </fieldset>
	    
	    <p id='loosePwd'>
	    <a href="{$_SERVER['PHP_SELF']}?kpid=cnx&amp;kaid=106">Mot de passe ou login perdu ?</a>
	    </p>
	    </form><!-- end form formLogin -->
EOD;
		return $str;
	}
	// }}}

	// {{{ _getNext()
	/**
	* Obtention des prochains matchs
	*
	* @access private
	* @param  string $id  nom du composant html
	* @param  obj    $obj criteres de selection des news
	* @return string  Chaine html avec les tournois
	*/
	function _getNext($id, $obj)
	{
		$args = get_object_vars($obj);

		$fields = array('DISTINCT evnt_id', 'evnt_name',
		      'min(tie_schedule)',
		      "DATE(tie_schedule) as date ",
		      'tie_place');
		$tables = array('events', 'ties', 'rounds', 'draws');
		$where = "evnt_id = draw_eventId".
	" AND draw_id = rund_drawId".
	" AND rund_id = tie_roundId".
	" AND tie_pbl =".WBS_DATA_PUBLIC.
	" AND DATE(tie_schedule) > CURDATE()+ 0".
	" GROUP BY evnt_id";
		$order = 'tie_schedule';

		$db = new utbase();
		$res = $db->_select($tables, $fields, $where, $order);
		/*
		 $serv = new utservices();
		 $res = $serv->select($tables, $fields, $where, $order);
		 */
		$str = "";
		$eventIds = array();
		//foreach ($res as $event)
		while ($event = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (!in_array($event['evnt_id'], $eventIds))
			{
				$eventIds[] = $event['evnt_id'];
				$str .= "<p>{$event['date']}: <a href=\"{$_SERVER['PHP_SELF']}";
				$str .= "?kpid=cnx";
				$str .= "&kaid=100";
				$str .= "&eventId={$event['evnt_id']}";
				$str .= "\">{$event['evnt_name']}</a></p>\n";
			}
		}
		return $str;
	}
	// }}}

	// {{{ _getVersion()
	/**
	* Obtention de la version de badnet
	*
	* @access private
	* @param  string $id  nom du composant html
	* @param  obj    $obj criteres de selection des news
	* @return string  Chaine html avec les tournois
	*/
	function _getVersion($id, $obj)
	{
		$args = get_object_vars($obj);

		// Demande des donnees au serveur
		$serv = new utservices();
		$res = $serv->select('meta', 'meta_value', "meta_name='version'");
		$version = $res[0]['meta_value'];
		if(preg_match("/V[\d]+_[\d]+r[\d]+.*/", $version, $curVersion))
		$version = ereg_replace('_', '.', $curVersion[0]);
		$str = "<a href=\"http://www.badnet.org\" alt=\"Site BadNet\"> BadNet $version</a>";
		return $str;
	}
	// }}}

	// {{{ _getUpdated()
	/**
	* Obtention des derniers tournois mis a jour
	*
	* @access private
	* @param  string $id  nom du composant html
	* @param  obj    $obj criteres de selection des news
	* @return string  Chaine html avec les tournois
	*/
	function _getUpdated($id, $obj)
	{
		$args = get_object_vars($obj);

		$glue = '';
		$where = '';
		// Nombre maximum de tournois
		if (isset($args['max']))
		{
	  $limit = $args['max'];
	  unset($args['max']);
		}

		// Anciennete max de la mise a jour
		if (isset($args['delay']))
		{
	  $where = "DATE(evnt_lastupdate) > CURDATE() - INTERVAL {$args['delay']} DAY";
	  $glue = " AND ";
	  unset($args['delay']);
		}

		// Crietre de recherches suplementaires
		foreach($args as $field=>$value)
		{
	  $where .= "{$glue} evnt_{$field}='{$value}'";
	  $glue = " AND ";
		}

		// Demande des donnees au serveur
		$fields = array('evnt_id', 'evnt_name', 'evnt_lastupdate');
		$tables = array('events');
		$order = 'evnt_lastupdate DESC';
		$serv = new utservices();
		$res = $serv->select($tables, $fields, $where, $order);
		$str = '';
		$nb = 0;
		$str = "";
		$utd = new utdate();
		foreach ($res as $event)
		{
	  $utd->setIsoDateTime($event['evnt_lastupdate']);
	  $date = $utd->getDate();
	  $str .= "<p>{$date} : <a href=\"{$_SERVER['PHP_SELF']}";
	  $str .= "?kpid=cnx";
	  $str .= "&kaid=100";
	  $str .= "&eventId={$event['evnt_id']}";
	  $str .= "\">{$event['evnt_name']}</a></p>\n";
	  if ($limit && $nb>$limit)
	  break;
	  $nb++;
		}
		return $str;
	}
	// }}}

	// {{{ _getNews()
	/**
	* Obtention des dernieres nouvelles
	*
	* @access private
	* @param  string $id  nom du composant html
	* @param  obj    $obj criteres de selection des news
	* @return string  Chaine html avec les news
	*/
	function _getNews($id, $obj)
	{
		$args = get_object_vars($obj);

		$glue = '';
		$where = '';
		// Nombre maximum de nouvelles
		if (isset($args['max']))
		{
	  $limit = $args['max'];
	  unset($args['max']);
		}

		// Une seule nouvelle par tournoi
		$unique = false;
		if (isset($args['unique']))
		{
	  $unique = true;
	  unset($args['unique']);
		}

		// Anciennete max des nouvelles
		if (isset($args['delay']))
		{
	  //	  $where = "DATE(news_cre) > CURDATE() - INTERVAL {$args['delay']} DAY";
	  $where .= "{$glue} news_cre > CURDATE() - INTERVAL {$args['delay']} DAY";
	  $glue = " AND ";
	  unset($args['delay']);
		}
		if (isset($args['season']))
		{
	  $where .= "{$glue} evnt_season={$args['season']}";
	  $glue = " AND ";
	  unset($args['season']);
		}
		$host  = '';
		$user  = '';
		$pwd   = '';
		if (isset($args['host']))
		{
	  $host = $args['host'];
	  unset($args['host']);
		}
		if (isset($args['user']))
		{
	  $user = $args['user'];
	  unset($args['host']);
		}
		if (isset($args['pwd']))
		{
	  $pwd = $args['pwd'];
	  unset($args['host']);
		}

		// Demande des donnees au serveur
		$fields = array('news_id', 'news_cre', 'news_text',
		      'evnt_id', 'evnt_name');
		$tables = array('news', 'events');
		$where .= "{$glue} news_eventId = evnt_id";
		$order = 'news_cre DESC';
		$serv = new utservices($host);
		$res = $serv->select($tables, $fields, $where, $order);
		// Formatage
		$str = '';
		$nb = 0;
		$utd = new utdate();
		foreach ($res as $new)
		{
	  if ($unique && isset($events[$new['evnt_id']]))
	  continue;
	  $utd->setIsoDateTime($new['news_cre']);
	  $date = $utd->getDateTime();
	  $events[$new['evnt_id']] =  true;
	  $str .= '<div class="new">';
	  $str .= "<div class=\"date\"><p>{$date}</p></div>";
	  $str .= "<div class=\"name\"><p>";
	  $str .= "<a href=\"{$_SERVER['PHP_SELF']}";
	  $str .= "?kpid=events";
	  $str .= "&kaid=98";
	  $str .= "&uid={$user}";
	  $str .= "&puid={$pwd}";
	  $str .= "&eventId={$new['evnt_id']}";
	  $str .= "\">{$new['evnt_name']}</a></p></div>\n";
	  $str .= "<div class=\"brief\"><p>{$new['news_text']}</p></div>\n";
	  $str .= "</div>";
	  $nb++;
	  if ($limit && $nb>=$limit)
	  break;
		}
		return $str;
	}
	// }}}

	// {{{ _getEvents()
	/**
	* Renvoi la liste des tournois
	*
	* @access private
	* @param  string $id  nom du composant html
	* @param  obj    $obj criteres de selection des tournois
	* @return string  Chaine html avec les tournois
	*/
	function _getEvents($id, $obj)
	{
		$args = get_object_vars($obj);

		$glue  = '';
		$where = '';
		$host  = '';
		$user  = '';
		$pwd   = '';
		$order = 1;
		foreach($args as $field=>$value)
		{
	  if ($field == 'order')
	  $order = $value;
	  else if ($field == 'host')
	  $host = $value;
	  else if ($field == 'user')
	  $user = $value;
	  else if ($field == 'pwd')
	  $pwd = $value;
	  else
	  {
	  	$where .= "{$glue} evnt_{$field}='{$value}'";
	  	$glue = " AND ";
	  }
		}

		$fields = array('evnt_id', 'evnt_name');
		$str = '';
		$serv = new utservices($host);
		$events = $serv->select('events', $fields, $where, $order);
		if(isset($events['errMsg']))
		{
	  $str .="<p style=\"color:blue;font-weight:bold;font-size:11px;\">{$events['errMsg']}</p>";
	  unset($events['errMsg']);
		}
		if (count($events))
		{
	  $str .= "<ul id=\"{$id}\" class=\"badnetEvents\">\n";
	  foreach ($events as $event)
	  {
	  	$str .= "<li><a href=\"{$_SERVER['PHP_SELF']}";
	  	$str .= "?kpid=events";
	  	$str .= "&kaid=98";
	  	$str .= "&eventId={$event['evnt_id']}";
	  	$str .= "&uid={$user}";
	  	$str .= "&puid={$pwd}";
	  	$str .= "\">{$event['evnt_name']}</a></li>\n";
	  }
	  $str .= "</ul>\n";
		}
		return $str;
	}
	// }}}

	// {{{ _getEventsLine()
	/**
	* Renvoi la liste des tournois avec inscription en ligne active
	*
	* @access private
	* @param  string $id  nom du composant html
	* @param  obj    $obj criteres de selection des tournois
	* @return string  Chaine html avec les tournois
	*/
	function _getEventsLine($id, $obj)
	{
		$args = get_object_vars($obj);

		$glue  = '';
		$where = '';
		$host  = '';
		$user  = '';
		$pwd   = '';
		$order = 1;
		foreach($args as $field=>$value)
		{
	  if ($field == 'order')
	  $order = $value;
	  else if ($field == 'host')
	  $host = $value;
	  else if ($field == 'user')
	  $user = $value;
	  else if ($field == 'pwd')
	  $pwd = $value;
	  else
	  {
	  	$where .= "{$glue} evnt_{$field}='{$value}'";
	  	$glue = " AND ";
	  }
		}
		$where .= "{$glue} evnt_liveentries=1".
	" AND DATE(evnt_deadline) > CURDATE()+ 0";

		$fields = array('evnt_id', 'evnt_name');
		$str = '';
		$serv = new utservices($host);
		$events = $serv->select('events', $fields, $where, $order);
		if(isset($events['errMsg']))
		{
	  $str .="<p style='color:blue;font-weight:bold;font-size:11px;'>{$events['errMsg']}</p>";
	  unset($events['errMsg']);
		}
		if (count($events))
		{
	  $str .= "<ul id=\"{$id}\" class=\"badnetEvents\">\n";
	  foreach ($events as $event)
	  {
	  	$str .= "<li style='text-align:right;'><a href=\"http://www.badnet.org/badnet30/Src/index.php";
	  	//$str .= "<li style='text-align:right;'><a href=\"http://127.0.0.1/~badnet/badnet.org/badnet30/Src/index.php";


	  	$str .= "?event={$event['evnt_id']}";
	  	$str .= "&lang=fra";
	  	$str .= "\">{$event['evnt_name']}</a></li>\n";
	  }
	  $str .= "</ul>\n";
		}
		return $str;
	}
	// }}}

	// {{{ _expandVars()
	/**
	* replace the values of the vars with the database content
	*
	* @access private
	* @param  string handle
	* @return bool   FALSE if error, true if all is ok
	*/
	function _expandVars()
	{
		require_once "json.php";
		$json = new Services_JSON();

		$nb =  count($this->_vals);
		for($i=0; $i<$nb; $i++)
		{
	  $val = $this->_vals[$i];
	  $key = '<!--badnet'.$this->_keys[$i].'-->';
	  $obj = each(get_object_vars($json->decode($val)));
	  $funcName = "_get{$obj[0]}";
	  $funcArgs = $obj[1];
	  if (method_exists($this, $funcName))
	  $str = $this->{$funcName}($this->_keys[$i], $funcArgs);
	  else
	  $str = "motClef inconnu : {$obj[0]}";
	  $this->_vals[$i] = $str;
	  $this->_keys[$i] = $key;
		}
	}
	// }}}}

	// {{{ _getKeys()
	/**
	* construct the (key, value) couple
	*
	* @access private
	* @param  string handle
	* @return bool   FALSE if error, true if all is ok
	*/
	function _getKeys($buf)
	{
		$exp = "/\<\!--badnetDeclare[ ]*(.*?):(.*)--\>/";
		$nb = preg_match_all($exp, $buf, $lines);

		$this->_keys = array();
		$this->_vals = array();
		for($i=0; $i<$nb; $i++)
		{
			array_push($this->_keys, $lines[0][$i]);
			$key = $lines[1][$i];
			array_unshift($this->_keys, $key);
			array_unshift($this->_vals, $lines[2][$i]);
		}
		return;
	}
	// }}}

	// {{{ _loadFile()
	/**
	* load file defined by handle if it is not loaded yet
	*
	* @access private
	* @param  string handle
	* @return bool   FALSE if error, true if all is ok
	*/
	function _loadFile()
	{
		$fileName = $this->file;

		if (!$fp = @fopen($fileName,"r"))
		$this->halt("_loadFile: couldn't open $fileName");

		$str = fread($fp,filesize($fileName));
		fclose($fp);
		 
		if ($str=='')
		$this->halt("_loadFile: $fileName does not exist or is empty.");

		return $str;
	}
	// }}}

	/**
	 * Error function. Halt template system with message to show
	 *
	 * @access public
	 * @param  string message to show
	 * @return bool
	 */
	function halt($msg)
	{
		PEAR::raiseError(sprintf("<b>Template Error:</b> %s<br>\n", $msg));
	}
}
?>