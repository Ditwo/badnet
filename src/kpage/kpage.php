<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kpage.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.18 $
******************************************************************************/
require_once "kpage.inc";
require_once "kcpt.php";
require_once "kmenu.php";
require_once "kdiv.php";
require_once "kform.php";

// {{{
/**
 * Une kPage contient des objets. Il ya deux type d'objet : les composants
 * qui peuvent ou non contenir d'autres objets et des �l�ments qui eux ne peuvent
 * pas contenir d'autre objets.
 *
 * Les composants disponibles sont :
 *  @li les divisions
 *  @li les formulaires
 *  @li les menus
 *
 * Les principaux �l�ments disponibles sont :
 *  @li les messages
 *  @li les zones �ditables
 *  @li les listes
 *  @li les boutons
 *  @li les cases � choix unique ou multiple
 *  @li les tableaux
 *
 * Un �l�ment est toujours plac� dans un composant. 
 * Certaines m�thodes de la kPage permettent de rajouter un �l�ment 
 * directement dans la page; mais ces m�thodes cr�ent un composant division
 * et place l'�l�ment dans le composant cr��. Pour connaitre la liste des 
 * �l�ments suceptible d'appartenir � un composant, consulter la 
 * documentation du composant.
 * Pour ajouter un objet � une kPage, il faut utiliser les m�thodes de 
 * la famille addXxx. Ces m�thodes renvoient un pointeur sur l'objet cr��.
 * On peut alors compl�ter ou modifier le comportement par d�faut de l'objet
 * ajout�.
 *
 * Les propri�t�s d'une kPage ne sont pas directement accessibles. Il faut 
 * obligatoirement utiliser les m�thodes de la famille setXxx pour les 
 * renseigner et les m�thodes getXxx pour r�cup�rer leur valeurs.
 * Certaines propri�t�s sont fix�es � la cr�ation de la page et ne peuvent 
 * �tre modifi�es.
 *
 *
 * @see kDiv, kForm
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 *
 */
class kPage extends kCpt
{
  // {{{ properties
  
  /**
   * Width of the page
   *
   * @private
   */
  var $_pageWidth = '100%';
  
  /**
   * Name of the page
   *
   * @private
   */
  var $_name = '';
  
  /**
   * Image path of the page
   *
   * @private
   */
  var $_imgPath = '';

  /**
   * Style file of the page
   *
   * @private
   */
  var $_styleFile;

  /**
   * Jave script files
   *
   * @private
   */
  var $_javaFile;
  
  /**
   * Flags for javascript fonctions
   *
   * @private
   */
  var $_msg = array();

  /**
   * Flags for javascript fonctions
   *
   * @private
   */
  var $_defForm;

  /**
   * Flags for javascript fonctions
   *
   * @private
   */
  var $_defPage;

  /**
   * Flags for reload page
   *
   * @private
   */
  var $_reload=0;
  
  // }}}


  // {{{ addForm()
  /**
   * @brief 
   * @~english Add an form
   * @~french Ajout d'un composant de type Formulaire.
   *
   * @par Description:
   * @~french Un composant de type Formulaire possede plusieur items. 
   * Il est utilis� lorsque des informations doivent �tre saisies par
   * l'utilisateur.
   *
   * @~english
   * See french doc.
   *
   * @param  $formId  (string)    @~french nom du composant.
   *         @~english Component name.
   * @return @~french Pointeur sur le nouveau composant. 
   *         @~english Pointer on new component.
   *
   * @~
   * @see kForm, addDiv, addMenu, addWng
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->addForm('form');
   * @endcode
   */
  function &addForm($formId, $submitPage='', $action=KAF_NONE)
    {
      $elt = $this->isExist($formId);
      if ($this->isExist($formId))
      {
	$this->addErr("kPage->addForm: objet already exists $formId");
	return $elt;
      }
      $this->_objs[$formId] = new kForm($this, $formId, $submitPage, $action);
      return $this->_objs[$formId];
    }

  // {{{ addImg()
  /**
   * @brief 
   * @~english Add an image
   * @~french Ajoute une image dans la page
   *
   * @par Description:
   * @~french Une image est un composant Division contenant un �l�ment 
   * de type Image. Le nom du composant est le nom pass� en parametre 
   * suffix� par Div. Le nom de l'�l�ment Image est compos� du nom pass�
   * en parametre. Si le nom du fichier image n'est pas fourni, il 
   * est construit avec le nom de l'�l�ment et l'extension '.png' et
   * le fichier est suppose se trouver dans le dossier d�fini par setImgPath.
   * Si le fichier n'existe pas, l'image n'est pas affich�e. Aucun message 
   * d'erreur n'est affich�.
   *
   * @~english
   * See french doc.
   *
   * @param  $imgId  (string)    @~french nom de l'image
   *         @~english name of image
   * @param  $file   (string)  @~french nom du fichier contenant l'image
   *         @~english Filename of the picture
   * @return @~french Pointeur sur l'�l�ment Img
   *         @~english Pointer on new element Img.
   *
   * @~
   * @see addDiv, kDiv, kImg, setImgPath
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->addImg('Message');
   * @endcode
   */
  function &addImg($imgId, $file='')
    {
      $name = $imgId.'Div';
      $kDiv =& $this->addDiv($name);
      return $kDiv->addImg($imgId, $file);
    }
  // }}}

  // {{{ addJavaFile()
  /**
   * @brief 
   * @~english Add an file for javascript
   * @~french Ajout d'un fichier pour le Javascript
   *
   * @par Description:
   * @~french Des fichiers java script permettent d'ajouter 
   * des procedure java script pour des actions �clanch�es 
   * par les elements actifs. 
   *
   * @~english
   * See french doc.
   *
   * @param  $file  (string)    @~french nom du fichier java script
   *         @~english filename of the java script
   * @return @~french Aucun.
   *         @~english None.
   *
   * @~
   * @see 
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $file = "prog.js";
   *    $kpage->addJavaFile($file);
   * @endcode
   */
  function addJavaFile($file)
    {
      if (is_file($file))
	$this->_javaFile[] = $file;
      else
	$this->addErr("kPage->addJavaFile : file not found :$file");
    }
  // }}}

  // {{{ addMenu()
  /**
   * @brief 
   * @~english Add an Menu
   * @~french Ajout d'un composant de type Menu.
   *
   * @par Description:
   * @~french Un composant de type Menu possede plusieur items. A chacun
   * est associ� une action. Un item est represent� par un label ou une
   * icone ou les deux. Ils peuvent �tre dispos�s horizontalement ou 
   * verticalement.
   *
   * @~english
   * See french doc.
   *
   * @param  $labelId  (string)    @~french nom de l'�l�ment. 
   *         @~english element name.
   * @param  $items    (array)    @~french liste des items du menu
   *         @~english Items of the menu
   * @return @~french Pointeur sur le nouvel �l�ment. 
   *         @~english Pointer on new element.
   *
   * @~
   * @see kMenu
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $items = array('ForDivs'  => array(KAF_NONE),
   *     	     'NewDiv'   => array(KAF_NEWWIN,    'divs', 
   *   					 KID_NEW,  0, 450, 180),
   *     	     'UpdtDiv'  => array(KAF_NEWWIN,    'divs', 
   *					 KID_EDIT,  0, 450, 180),
   *           	     'DelDiv'   => array(KAF_UPLOAD, 'divs', 
   *				 KID_DELETE));
   *   $kpage->addMenu("menuDiv", $items);
   * @endcode
   */
  function &addMenu($labelId, $items='')
    {
      echo "kpage->addMenu !!!!<br>";
      if ($this->isExist($labelId))
      {
	$this->addErr("kPage->addMenu: object already exists $labelId");
	return null;
      }

      $title = '';
      if (isset($items['Title']))
	{
	  $title = $items['Title'];
	  unset($items['Title']);
	}
      foreach( $items as $name => $action)
	{
	  $fct = @array_shift($action);
	  if ($fct == KAF_CHECKROWS)
	    {
	      if (!isset($action[2]))
		{
		  echo "form=".$this->_name."; element:$name<br>";
		  echo "  action CHECKROWS: quoi? voir gege!!!;<br>";
		}
	      $msgId = "need$action[2]";
	      $msg = $this->getLabel($msgId);
	      $this->_msg[] = "msg['".$msgId."']='".$msg."';\n";
	    }
	  else
	    if (!isset($action[2])) $action[2]=$labelId;
	  
	  if ($fct != KAF_NONE)
	    {
	      $arg = @implode("','", $action);
	      $fct .= "('$this->_name','".$arg."')"; 	
	    }
	  $items[$name] = $fct;
	}
      $this->_objs[$labelId] = new kMenu($labelId, $items, $title);
      $this->_objs[$labelId]->setImgPath($this->_imgPath);
      return $this->_objs[$labelId];
    }
  // }}}

  // {{{ addStyleFile()
  /**
   * @brief 
   * @~english Add an file form CSS
   * @~french Ajout d'un fichier de style � la page
   *
   * @par Description:
   * @~french Des fichiers de style par d�faut sont utilis�s dans les 
   * pages g�n�r�s par kPage. Pour personnaliser l'apparence et la mise 
   * en page des composants et des �l�ments de la page, des fichiers de 
   * style supplementaire peuvent �tre ajout�s. Ils seront prioritaires 
   * sur les fichiers de style par defaut
   *
   * @~english
   * See french doc.
   *
   * @param  $file  (string)    @~french nom du fichier css
   *         @~english filename of the Css-style.
   * @param  $media (string)    @~french type du media support� (screen, 
   * print, audio)
   *         @~english Media type (screen, print, audio)
   * @return @~french Aucun.
   *         @~english None.
   *
   * @~
   * @see 
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $file = "utils/badnet.css";
   *    $kpage->addStyleFile($file);
   * @endcode
   */
  function addStyleFile($file, $media='screen')
    {
      if (is_file($file))
	$this->_styleFile[$file] = $media;
      else
	$this->addErr("kPage->addStyleFile : file not found :$file");
    }
  // }}}

  // {{{ addWng()
  /**
   * @brief 
   * @~english Add an warning message
   * @~french Ajoute un message d'avertissement dans la page
   *
   * @par Description:
   * @~french Un message d'avertissement est un composant Division
   * contenant un �l�ment de type Warning. Le nom du composant est le
   * le nom pass� en parametre suffix� par Div. Le nom de l'�l�ment 
   * Warning est compos� du nom pass� en parametre.
   *
   * @~english
   * See french doc.
   *
   * @param  $wngId  (string)    @~french nom du message d'avertissement
   *         @~english name of warning
   * @return @~french Pointeur sur l'�l�ment Wng. 
   *         @~english Pointer on new element Wng.
   *
   * @~
   * @see kDiv, kWng
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->addWng('Message');
   * @endcode
   */
  function &addWng($wngId)
    {
      $name = $wngId.'Div';
      $kDiv =& $this->addDiv($name);
      return $kDiv->addWng($wngId);
    }
  // }}}
  
  // {{{ beginDisplay()
  /**
   * @brief 
   * @~english Start displaying the page
   * @~french Commence l'affichage de la page
   *
   * @par Description:
   * @~french L'en tete de la page HTML est affich� avec les titres ainsi
   * que tous les composants d�j� ajout�s � la page. La fin de page n'est 
   * pas affich�e. Pour afficher la fin de page, utiliser la m�thode 
   * EndDisplay. Cette m�thode est utilis�e lorsque que l'on souhaite 
   * ajouter et afficher des composants au cours d'un traitement (par 
   * exemple afficher l'�tat d'avancement d'un travail). Pour un cas 
   * d'affichage classique (toute la page en une seule fois) pr�f�rer la 
   * m�thode Display().
   *
   * @~english
   * See french doc.
   *
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see display, endDisplay
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->addWng('Message');
   *    $kpage->beginDisplay();
   *    ....
   *    ....
   *    $kpage->endDisplay();
   * @endcode
   */
  function beginDisplay()
    {
      // Print the head of the page
      $this->_head();
      
      // Display the error messages
      for ($i=0; $i < $this->_errNum; $i++)
	echo "&nbsp;&nbsp;".$this->_errs[$i]."<br />";
      
      // Display the objects of the page
      if (count($this->_objs))
	{
	  foreach( $this->_objs as $name => $cpt)
	    {
	      $cpt->display($this->_stringFile);
	      unset($this->_objs[$name]);
	    }
	}
    }
  // }}}

  // {{{ close()
  /**
   * @brief 
   * @~english Close a window.
   * @~french Ferme la fen�tre courante.
   *
   * @par Description:
   * @~french La fen�tre courante est ferm�e. Si demand�, la fen�tre 
   * parente est rafraichie.
   *
   * @~english
   * Print the page with nothing inside. Refresh the parent window if 
   * needed and close the current window
   *
   * @param  $refresh (boolean) @~french Rafraichissement de la fen�tre parente
   *         @~english Have we to refresh the parent window
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see beginDisplay, endDisplay, display
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->close(false);
   * @endcode
   */
  function close($refresh=true, $pageId='', $actId='', $data='')
    {
      echo "<html>\n<head>\n";
      echo "<script language=\"Javascript\" src=\"kpage/kpage.js\">";
      echo "</script>\n";
      echo "</head>\n\n";
      if ($refresh)
	echo "<body onload=\"return(uploadClose(this, '$pageId', '$actId', '$data'));\">\n";
      else
	echo "<body onload=\"return(cancelClose(this));\">\n";
      echo "</body>\n";
      echo "</html>\n";
    }
  // }}}

  // {{{ refreshParent()
  /**
   * @brief 
   * @~english Refresh parent
   * @~french Force le reaffichage de la page parente
   *
   * @par Description:
   * @~french La fen�tre courante est ferm�e. Si demand�, la fen�tre 
   * parente est rafraichie.
   *
   * @~english
   * Print the page with nothing inside. Refresh the parent window if 
   * needed and close the current window
   *
   * @param  $refresh (boolean) @~french Rafraichissement de la fen�tre parente
   *         @~english Have we to refresh the parent window
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see beginDisplay, endDisplay, display
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->close(false);
   * @endcode
   */
  function refreshParent($pageId='', $actId='', $data='')
    {
      echo "<html>\n<head>\n";
      echo "<script language=\"Javascript\" src=\"kpage/kpage.js\">";
      echo "</script>\n";
      echo "</head>\n\n";
      echo "<body onload=\"return(refreshParent('$pageId', '$actId', '$data'));\">\n";
      echo "</body>\n";
      echo "</html>\n";
    }
  // }}}


  // {{{ display()
  /**
   * @brief 
   * @~english Display the page
   * @~french Affichage de la page
   *
   * @par Description:
   * @~french La page et tous ses composants sont affich�s.
   *
   * @~english
   * See french doc.
   *
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see beginDisplay, endDisplay
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->addWng('Message');
   *    $kpage->Display();
   * @endcode
   */
  function display($file='')
    {
      ob_start();
      $this->beginDisplay();
      $this->endDisplay();
      $buf = ob_get_contents();
      ob_end_clean();
      $search = 'badnetCacheSince';

      // Write the content of the page in the cache
      if ($file != '')
	{
	  $date = date(DATE_DATETIME);
	  $buf = str_replace($search, "En cache depuis le $date", $buf);
	  $fd = @fopen($file, "w");
	  @fwrite($fd, $buf);
	  @fclose($fd);
	  @chmod($file, 0777);
	}
      else
	$buf = str_replace($search, "", $buf);

      ob_start();
      echo $buf;
      ob_end_flush();
    }
  // }}}
  
  // {{{ endDisplay()
  /**
   * @brief 
   * @~english Print the end of the page
   * @~french Termine l'affichage de la page
   *
   * @par Description:
   * @~french Affiche tous les composants ajout�s � la page depuis le d�but
   * de l'affichage, puis termine la page.
   *
   * @~english
   * See french doc.
   *
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see beginDisplay, display
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $kpage->addWng('Traitement en cours');
   *    $kpage->beginDisplay();
   *    ....
   *    ....
   *    $kpage->endDisplay();
   * @endcode
   */
  function endDisplay()
    {
      // Display the components of the page
      if (count($this->_objs))
	{
	  foreach( $this->_objs as $name => $cpt)
	    {
	      $cpt->display($this->_stringFile);
	      unset($this->_objs[$name]);
	    }
	}
      
      $this->_printJava();
      // End of the page
      $pageId = kform::getPageId();
      $actId =  kform::getActId();
      if ($pageId == 'cnx' && $actId =='103')
	{
	  $pageId = 'events';
	  $actId =  '201';
	}
      echo "<form id=\"kHistory\" action=\"post\"><div>
<input type=\"hidden\" name=\"kpid\" id=\"kpid\" value=\"$pageId\"  />
<input type=\"hidden\" name=\"kaid\" id=\"kaid\" value=\"$actId\"  />\n";
      $data = kform::getData();
      if ($data == 'undefined') $data ='';
      $text = "<input type=\"hidden\" name=\"kdata\" id=\"kdata\" value=\"".$data."\"  />\n";
      if (defined("SID") && (SID != ''))
	{
	  $text .= "<input type=\"hidden\" name=\"PHPSESSID\" id=\"PHPSESSID\" ";
	  $text .= "value=\"".session_id()."\"  />\n";
	}
      $text .= "</div>\n</form><!-- end form fHistory -->\n</body>\n</html>";
      echo $text;
    }
  // }}}
  

  // {{{ getInput()
  /**
   * @brief 
   * @~english Return the value of the field.
   * @~french Renvoie la valeur d'un champ de la page.
   *
   * @par Description:
   * @~french Permet de r�cuperer les valeurs des champs d'un formulaire
   * ou d'un param�tre pass� � la page lorsqu'elle est envoy�e par 
   * l'utilisateur. Les codes Html sont inhib�s. Si le champ ou le
   * param�tre n'existe pas, la fonction renvoie une chaine vide ''. 
   *
   * @~english
   * See french doc.
   *
   * @param  $name (string) @~french Nom du champ
   *         @~english Field name
   * @return @~french (string) Valeur du champ
   *         @~english Field value
   *
   * @~
   * @see getInputHtml
   * @par Exemple:
   * @code
   *    $name = kPage::getInput('Nom');
   * @endcode
   */
  function getInput($name, $default='')
    {
      $res = kform::getInputHtml($name, $default);
      if (is_array($res))
	return $res;
      else
	return htmlspecialchars($res);
    }
  // }}}

  // {{{ getInputHtml()
  /**
   * @brief 
   * @~english Return the value of the field.
   * @~french Renvoie la valeur d'un champ de la page.
   *
   * @par Description:
   * @~french Permet de r�cuperer les valeurs des champs d'un formulaire
   * ou d'un param�tre pass� � la page lorsqu'elle est envoy�e par 
   * l'utilisateur. Les codes Html sont conserv�s. Si le champ ou le
   * param�tre n'existe pas, la fonction renvoie une chaine vide ''. 
   *
   * L'utilisation de cette fonction est d�conseill�e pour des raisons
   * de securit�. L'utilisateur peut saisir du code Javascript qui
   * sera ex�ut� sur une mahine cliente et ainsi recup�rer, par exemple,
   * des cookies pr�sents sur cette machine. Comme par exemple numero et 
   * code de carte banciare...
   *
   * @~english
   * See french doc.
   *
   * @param  $name (string) @~french Nom du champ
   *         @~english Field name
   * @return @~french (string) Valeur du champ
   *         @~english Field value
   *
   * @~
   * @see getInputHtml
   * @par Exemple:
   * @code
   *    $name = kPage::getInput('Nom');
   * @endcode
   */
  function getInputHtml($name)
    {
      if (isset($_GET[$name]))
	$value = $_GET[$name];
      
      else if (isset($GLOBALS['HTTP_GET_VARS'][$$name]))
	$value = $GLOBALS['HTTP_GET_VARS'][$name];
      
      else if (isset($_POST[$name]))
	$value = $_POST[$name];
      
      else if (isset($GLOBALS['HTTP_POST_VARS'][$name]))
	$value = $GLOBALS['HTTP_POST_VARS'][$name];
      else 
	$value = '';
      return $value;
    }
  // }}}

  // {{{ constructor
  /**
   * Constructor. 
   * @brief 
   * @~english Constructor
   * @~french Constructeur
   *
   * @par Description:
   * @~french Le constructeur permet de cr�er une instance de la classe kPage.
   * Si une variable du nom de la page existe, son contenu est affich� comme 
   * titre du navigateur, sinon le nom de la page est affich�.
   *
   * @~english
   * See french doc.
   *
   * @param  $name (string) @~french Nom de la page
   *         @~english Page name
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see setStringFile
   * @par Exemple:
   * @code
   *    $kPage = new kPage('Nom');
   * @endcode
   */
  function kPage($name)
    {
      $this->_name = $name;
      $this->_type = 'page';
      $this->_page = $this;
    }
  // }}}
    
  // {{{ addAction()
  /**
   * Constructor. 
   * @brief 
   * @~english Set the load  action 
   * @~french Definie l'action execute au chargement de la page
   *
   * @par Description:
   * @~french Cette fonction permet de d�finir l'action qui est execut�e
   * au chargement de la page. L'action est une fonction Javascript.
   * Il est possible d'utiliser les fonctions pr�-definies de la kPage. 
   * Auxquel cas les arguments doivent respecter la d�finition de la 
   * fonction utilis�e. Pour utiliser une fonction personalis�e, il faut la
   * placer dans un fichier externe .js et indiquer le fichier � la kPage
   * avec la fonction setJavaFile.
   *
   * @~english
   * See french doc.
   *
   * @param  $action (arraystring) @~french Tableau d�finissant l'action
   * � ex�cuter au chargement de la page. Le premier �l�ment du nom est
   * le nom de la fonction; les �l�ments suivants sont ses arguments.
   * Les fonctions pr�d�finies et leurs arguments sont:
   *     KAF_SUBMIT
   *     KAF_CHECKROW
   *     KAF_CHECKFIELDS
   *         @~english Action and his arguments.
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see setJavaFile
   * @par Exemple:
   * @code
   *    $kPage = new kPage('Nom');
   * @endcode
   */
  function addAction($evnt, $action)
    { 
      // First element of the action array is the name
      // of the fonction
      $fct = @array_shift($action);
      $arg = @implode("','", $action);
      $fct .= "('noSid', this, '$arg')"; 	
      $this->_actions[$evnt] = $fct; 
    }
  // }}}
  
  // {{{ setStringFile()
  /**
   * @~english Set the file for strings
   * @~french Fixe le ficher utilis� pour la recherhce des chaines 
   * de carat�res
   *
   * @par Description:
   * @~french Chaque composant ou �l�ment pr�sent dans la page et la 
   * page elle-m�me ont un nom. Lors de l'affichage de la page, si des
   * variables du m�me nom existe, leur contenu est affich�. Ces variables
   * sont d�finies dans des fichiers s�par�s.
   * Ce m�canise permet de changer de langue sans toucher au soft. 
   *
   * @~english
   * See french doc.
   *
   * @param  $file (string) @~french Nom du fichier � utiliser pour la
   * recherche des chaines de caract�res
   *         @~english Default File name
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see addEdit, addHide, addMsg, addWng....
   * @par Exemple:
   * Contenu du fichier string.inc
   * @code
   * <?php
   *   $page= "Bienvenue chez vous";
   * ?>
   * @endcode
   * Utilisation:
   * @code
   *    $kPage = new kPage('page');
   *    $kPage->setStringFile('./string.inc');
   *    $kPage->display();
   * @endcode
   */
  function setStringFile($file)
    {
      if (is_file($file))
	$this->_stringFile = $file;
      else
	$this->addErr("kPage->setStringFile : fichier introuvable :$file");
    }
  // }}}

  // {{{ setRelaod()
  /**
   * @~english Set the delay for reload page
   * @~french Fixe le delai d'attente avant rechargement de la page. 
   *
   * @par Description:
   * @~french Pour que la page se recharge automatiquement, donner un delai superieur
   *   a 0
   *
   * @~english
   * See french doc.
   *
   * @param  $file (string) @~french Nom du fichier � utiliser pour la
   * recherche des chaines de caract�res
   *         @~english Default File name
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see addEdit, addHide, addMsg, addWng....
   * @par Exemple:
   * Contenu du fichier string.inc
   * @code
   * <?php
   *   $page= "Bienvenue chez vous";
   * ?>
   * @endcode
   * Utilisation:
   * @code
   *    $kPage = new kPage('page');
   *    $kPage->setStringFile('./string.inc');
   *    $kPage->display();
   * @endcode
   */
  function setReload($delay)
    {
	$this->_reload = $delay;
    }
  // }}}

  

  
  //-----------------------------------------
  //  Here are private methode
  //------------------------------------------


  // {{{ _printJava()
  /**
   * Print the javascript of the page
   *
   * @private
   */
  function _printJava()
    {
      $msgs = $this->getControl($this->_stringFile);
      if (is_array($msgs))
	{
	  $text = "<script type=\"text/javascript\">\n<!--\n\n";
	  //echo "document.oncontextmenu=new Function(\"return false;\")\n";
	  $text .=  "function getMsg(msgId)\n{\n";
	  $text .= "var msg = new Object();\n";
	  foreach ($msgs as $msg) $text .= $msg;
	  $text .= "return msg[msgId];\n";
	  $text .= "}\n\n";
	  $text .= "// -->\n";
	  $text .= "</script>\n\n";
	  echo $text;
	}
    }
  // }}}
  
  // {{{ _head()
  /**
   * Print the header of the page
   *
   * @private 
   * @param integer    Delai de rechargement de la page
   * @param mixed      Donnes optionnelles pour lecorp de la page
   *
   * @return void
   */
  function _head()
    {
      $title = $this->getLabel($this->_name);
      echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"fr\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />
<meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\" />
<meta http-equiv=\"Content-Style-Type\" content=\"text/css\" />
<meta http-equiv=\"Content-Language\" content=\"fr\" />

<title>$title</title>

<meta http-equiv=\"expires\" content=\"0\" />
<meta http-equiv=\"cache-control\" content=\"no-cache,no-store\" />
<meta http-equiv=\"pragma\" content=\"no-cache\" />
<meta name=\"revisit-after\" content=\"15 days\" />\n";
      $text=''; 
      if($this->_reload>0)
	$text .= "<meta http-equiv=\"refresh\" content=\"{$this->_reload};\" />\n";

      //$text .="<link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"/badnet.png\" />\n";
      $text .="<link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"badnet.ico\" />\n";
      $text .="<link rel=\"stylesheet\" type=\"text/css\" href=\"kpage/kpage.css\" media=\"screen\" />\n";
      $text .="<link rel=\"stylesheet\" type=\"text/css\" href=\"kpage/kpage_P.css\" media=\"print\" />\n";

      if (is_array($this->_styleFile))
	{
	  foreach ($this->_styleFile as $file => $media)
	    {
	      $text .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$file";
	      $text .= "\" media=\"$media\" />\n";
	    }
	}
      $text .= "<script type=\"text/javascript\" src=\"kpage/kpage.js\"></script>\n";
      $json = true;
      if (is_array($this->_javaFile))
	{
	  foreach ($this->_javaFile as $file)
	    {
	      if($file == 'Bn/Js/jquery-1.4.2.pack.js') $json=false;
	      $text .=  '<script type="text/javascript" ';
	      $text .=  "src=\"$file\"></script>\n";
	    }
	}
	  if ($json) $text .= "<script type=\"text/javascript\" src=\"kpage/json.js\"></script>\n";
      echo $text;
      $this->_printJava();
      

      $text = "</head>\n\n";      
      if (count($this->_actions))
	{
	  //echo "onclick=\"$action;return(false);\" />\n";
	  $text .= "<body ";
	  foreach ($this->_actions as $evt=>$fct)
	    $text .= "$evt=\"return($fct);\" ";
	  $text .= ">\n";
	}
      else
	//echo "<body onunload=\"onferme();return(false);\">\n";
	$text .= "<body>\n";
      echo $text;
    }
  // }}}
  

}
?>