<?php
/*****************************************************************************
!   Module     : kPage
!   File       : $Source: /cvsroot/aotb/badnet/src/kpage/kpage.inc,v $
!   Version    : $Name: HEAD $
!   Revision   : $Revision: 1.4 $
!   Revised by : $Author: cage $
!   Date       : $Date: 2007/01/18 07:51:18 $
******************************************************************************/

define("KID_NONE",            0);
define("KID_DEFAULT",         99);
define("KID_RELOAD",          1);
define("KID_NEW",             2);
define("KID_EDIT",            3);
define("KID_DELETE",          4);
define("KID_SEARCH",          6);
define("KID_UPDATE",          7);
define("KID_PRINT",           8);
define("KID_LOGOUT",          9);
define("KID_LOGIN",           10);
define("KID_NEXTPAGE",        11);
define("KID_PREVPAGE",        12);
define("KID_FIRSTPAGE",       13);
define("KID_LASTPAGE",        14);
define("KID_ADD",             15);
define("KID_CONFIRM",         16);
define("KID_ERASE",           17);

define("KID_HOME",            20);
define("KID_CLOSE",           21);
define("KID_SELECT",          22);
define("KID_EXPORT",          23);
define("KID_UNDELETE",        24);
define("KID_CONFIDENT",       25);
define("KID_PRIVATE",         26);
define("KID_PUBLISHED",       27);

define("KID_MSG",             50);

define("KID_USER",           100);

define("KAF_SUBMIT",          'valid');
define("KAF_AJAJ",            'callAjaj');
define("KAF_UPLOAD",          'upload');
define("KAF_VALID",           'upload');
define("KAF_NEWWIN",          'newWin');
define("KAF_NEWWINURL",       'newWinUrl');
define("KAF_CANCEL",          'cancelClose');
define("KAF_NONE",            'Nolink');


define("KAF_UPDATE",          'Update');

define("KOD_NONE",           1);
define("KOD_SELECT",         2);
define("KOD_READ",           4);
define("KOD_WRITE",          8);
define("KOD_DELETE",         KOD_SELECT | KOD_WRITE);

define("KOD_BREAK",         -1);  

?>