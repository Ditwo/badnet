<?php
/*****************************************************************************
 !   Module     : Object
 !   File       : $Source:$
 !   Version    : $Name:$
 !   Revision   : $Revision:$
 !   Revised by : $Author:$
 !   Date       : $Date:$
 ******************************************************************************/
require_once 'Object.php';

class Ouser extends Object
{
   private $_userId   = -1;      
   
   private function __construct($auserId)
   {
        $this->_userId = $aUserId;
   }

}
?>
