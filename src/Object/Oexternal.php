<?php
/*****************************************************************************
 !   $Id$
 ******************************************************************************/

class Oexternal
{

	public static function factory()
	{
		$className = 'O' . Bn::getConfigValue('external', 'params');
		$externIo  = new $className();
        return $externIo;
	}
}

abstract class OexternalDriver extends Object
{
	abstract public function getInstancesInstance($aInstanceId, $aType = null);
	abstract public function getMember($aLicense=null, $aPoonaId=-1);
	abstract public function getPlayer($aLicense, $aSeason=null);
	
}
?>