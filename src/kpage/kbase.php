<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kbase.php,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.1 $
!   Mailto     : cage@free.fr
!   Revised by : $Author: cage $
******************************************************************************/


// {{{
/**
 * Tous les objets pr�sents dans une kPage et la kPage elle-m�me
 * h�ritent de cette classe.
 *
 * Elle permet de factoriser les attributs et les m�thodes communes.
 * Un programmeur utilisant la kPage ne doit jamais acc�der directment
 * � des objets ou des m�thode de cette classe.
 *
 * @see kPage, kCpt, kElt
 *
 * @author Gerard CANTEGRIL <cage@free.fr>
 *
 */
class kBase
{

  // {{{ properties
  
  /**
   * Name of the element
   *
   * @private
   */
  var $_name='';

  /**
   * Class of the element. 
   *
   * @private
   */
  var $_class = '';
  var $_title = '';

  /**
   * Type of the element. Can be 'Menu', 'Form',
   *  or 'Std'
   *
   * @private
   */
  var $_type = '';
  
  /**
   * Language file which contain strings of objects
   *
   * @private
   */
  var $_stringFile='';

  /**
   * Path of the image
   *
   * @private
   */
  var $_imgPath='';
  
  /**
   * Actions associate with the element
   *
   * @private
   */
  var $_actions = array();
  
  /**
   * Attribs of the tag
   *
   * @private
   */
  var $_attribs;

  /**
   * Urls  associate with the element
   *
   * @private
   */
  var $_urls='';

  /**
   * Errors
   *
   * @private
   */
  var $_errs = array();
  
  /**
   * Error message number
   *
   * @private
   */
  var $_errNum = 0;

  // }}}

  // {{{ constructor
  /**
   * Constructor. 
   * @brief 
   * @~english Constructor
   * @~french Constructeur
   *
   * @par Description:
   * @~french Le constructeur permet de cr�er une instance de la classe kBase.
   * On ne doit jamais cr�er directement une instance de cette classe.
   *
   * @~english
   * See french doc.
   *
   * @return @~french Aucun
   *         @~english None
   * @private
   */  
  function kBase()
    {
      $this->_type = 'none';
      $this->_name = 'unname';
    }
  // }}}

  // {{{ display($file)
  /**
   * Constructor. 
   * @brief 
   * @~english Virtual fonction to display element
   * @~french Foniton virtuelle d'affichage d'un �l�ment
   *
   * @par Description:
   * @~french Cette m�thode est virtuelle et doit etre surcharg�e par
   * les objet heritant.
   *
   * @~english
   * See french doc.
   *
   * @param  $file (string) @~french Nom du fichier � utiliser pour la
   * recherche des chaines de caract�res
   *         @~english Default File name
   * @return @~french Aucun
   *         @~english None
   * @private
   */
  function display($file)
    {  
      $this->_stringFile = $file;
      //echo "You have to implement the display fonction ";
      //echo "for this type of component : $this->_type";
    }
  // }}}
  
  // {{{ getLabel()
  /**
   * @~english Return the string of the label
   * @~french Renvoi la chaine de caractere du label
   *
   * @par Description:
   * @~french La fonction recherche une chaine de caractere du nom
   * du label. Si elle existe, son contenu est renvoy� sinon le c'est
   * le nom du label qui est renvoye.
   *
   * @~english
   * See french doc.
   *
   * @param  $labelId (string) @~french Nom du label � rechercher
   *         @~english Name of the label
   * @return (string) @~french Label � afficher
   *         @~english Label to display
   *
   * @see addStringFile
   * @private
   */
  function getLabel($labelId)
    {
      // Inclure le fichier contenant les chaines de caracteres 
      // en fonction de la langue
      if ($this->_stringFile != '')
	{
	  if (is_file($this->_stringFile))
	    require ($this->_stringFile);
	  else
	    $this->addErr("getLabel : fichier introuvable :$this->_stringFile");
	}	  

      if (isset(${$labelId}))
	return "${$labelId}";
      else
	{
	  return $labelId;
	}
    }
  // }}}

  // {{{ getControl()
  /**
   * @~english Return the string of the control label
   * @~french Renvoi la chaine de caractere du message d'erreur
   *
   * @par Description:
   * @~french Lors de la saisie d'informations dans un formulaire,
   * certains champ sont obligatoire; Cette fonction renvoie le message
   * d'erreur a afficher quand un �l�ment est obligatoire.
   * Cette fonction est virtuelle et renvoi null. Elle doit etre
   * implement�e au niveau des quelques elets concernes.
   *
   * @~english
   * See french doc.
   *
   * @param  $file (string) @~french Fichier langue a utiliser
   *         @~english Name of the string file
   * @return (string) @~french Message � afficher
   *         @~english Message to display
   *
   * @see addStringFile
   * @private
   */
  function getControl($file)
    {
      return '';
    }
  // }}}



  // {{{ addErr()
  /**
   * @~english Add an error message to the list of message
   * @~french Ajoute un message a la liste des messages d'erreurs
   *
   * @par Description:
   * @~french Pour debug uniquement. Les message d'erreurs sont
   * affichees en debut de page
   *
   * @~english
   * See french doc.
   *
   * @param  $error (string) @~french Message d'erreur
   *         @~english Error message
   * @return @~french	Aucn
   *         @~english  None
   *
   * @private
   */
  function addErr($error)
    {
      $this->_errs[$this->_errNum++] = $error;
      echo "$error <br />";
    }
  // }}}  

  // {{{ isExist()
  /**
   * @~english Check if the objet has the name
   * @~french Verifie si le nom de l'objet est celui propos�
   * pas d�j�.
   *
   * @par Description:
   * @~french Dans une page, le nom de chaque objet de la page doit �tre
   * unique quel que soit sa nature (composant ou �l�ment). Pour un �l�ment,
   * cette m�thode v�rifie que l'�l�ment n'a pas le nom propos�. Pour un 
   * composant cette m�thode est surcharg�e et v�rifie le nom du composant 
   * et les noms de tous ses �l�ments. 
   * Cette fonction ne doit �tre utilis�e que par les developpeurs de 
   * la KPage; pas par les utilisateurs.
   *
   * @~english
   * See french doc.
   *
   * @param  $name (string) @~french Nom � v�rifier
   *         @~english Name to check
   * @return (boolean) @~french Pointeur surl'objet de ce nom ou false.
   *         @~english False or pointer on existing object
   *
   * @private
   */
  function isExist($name)
    {
      return ($name == $this->_name &&
	      substr($name, -2) != '[]')? $this : false;
    }
  // }}}

  // {{{ getName()
  /**
   * @brief 
   * @~english Return the name of the object
   * @~french Renvoie le nom de l'objet.
   *
   * @par Description:
   * @~french Aucune id�e de l'utilisation de cette fonction. Sert-elle
   * a quelque chose ?
   *
   * @~english
   * See french doc.
   *
   * @return @~french (string) Nom de l'objet
   *         @~english Name of the object
   *
   * @~
   * @see kPage
   * @par Exemple:
   * @code
   *    $kpage = new kPage('maPage');
   *    $name = kPage->getName();
   * @endcode
   * @public
   */
  function getName()
    { 
      return $this->_name;
    }
  // }}}

  function getType()
    { 
      return $this->_type;
    }
  // }}}

  // {{{ setImgPath()
  /**
   * @~english Set the default path for image file
   * @~french Fixe le chemin default pour les fichiers images
   *
   * @par Description:
   * @~french Le chemin par d�faut des fihiers images est utilis�
   * pour chercher les images des �l�ments de type image.
   *
   * @~english
   * See french doc.
   *
   * @param  $path (string) @~french Chemin des fichiers images
   *         @~english Default path for image file
   * @return @~french Aucun
   *         @~english None
   *
   * @~
   * @see addImg
   * @par Exemple:
   * @code
   *    $kPage = new kPage('Nom');
   *    $kPage->setImgPath('../img');
   * @endcode
   * @private
   */
  function setImgPath($path)
    {
      if (is_dir($path))
	$this->_imgPath = $path;
      else
	if ($path != '') $this->addErr("setImgPath : chemin inconnu :$path");
    }
  // }}}

  /**
   */
  function setClass($class)
    {
      $this->_class = $class;
    }

  /**
   * Enter description here...
   *
   * @param unknown_type $class
   */
  function setTitle($title)
    {
      $this->_title = $title;
    }
  // }}}
    
}

?>