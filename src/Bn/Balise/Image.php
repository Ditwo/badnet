<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Image extends Bn_Balise
{
	/**
	* Constructeur
	*
	*/
	public function __construct($aName, $aFilename, $aAlternate, $aMaxize=null)
	{
		$filename = $aFilename;
		if (substr($aFilename, 0, 7) != 'http://')
		{
			if ( ! file_exists($filename) ) $filename = Bn::getImagePath() . $aFilename;
			if ( ! file_exists($filename) ) $filename = 'Bn/Img/' . $aFilename;
		}
		//if (file_exists($filename))
		{
			parent::__construct('img/', $aName);
			$size = @getImagesize($filename);
			if ($size)
			{
				$width  = $size[0];
				$height = $size[1];
				// Recalculer la taille de l'image
				if (is_array($aMaxize))
				{
					$few = 1;
					if (isset($aMaxize['width']))
					{
						$few = $aMaxize['width'] / $size[0];
					}
					$feh = 1;
					if (isset($aMaxize['height']))
					{
						$feh = $aMaxize['height'] / $size[1];
					}
					$fe = min(1, $feh, $few);
					$width  = intval($size[0] * $fe);
					$height = intval($size[1] * $fe);
				}
				$this->setAttribute('width',  $width);
				$this->setAttribute('height', $height);
			}
			$this->setAttribute('src',    $filename);
			$this->setAttribute('alt',    $aAlternate);
			$this->setAttribute('class',  'bn-img');
		}
	}

}

?>