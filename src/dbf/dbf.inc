<?php
/*****************************************************************************
!   Module     : Export Dbf
!   File       : $Source: /cvsroot/aotb/badnet/src/dbf/dbf.inc,v $
!   Version    : $Name:  $
!   Revision   : $Revision: 1.3 $
!   Author     : G.CANTEGRIL
!   Revised by : $Author: cage $
!   Date       : $Date: 2006/03/03 22:11:10 $
!   Mailto     : cage@free.fr
******************************************************************************
!   License   : Licensed under GPL [http://www.gnu.org/copyleft/gpl.html]
!      This program is free software; you can redistribute it and/or
!      modify it under the terms of the GNU General Public License
!      as published by the Free Software Foundation; either version 2
!      of the License, or (at your option) any later version.
!
!      This program is distributed in the hope that it will be useful,
!      but WITHOUT ANY WARRANTY; without even the implied warranty of
!      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
!      GNU General Public License for more details.
!
!      You should have received a copy of the GNU General Public License
!      along with this program; if not, write to the Free Software
!      Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, 
!      USA.
******************************************************************************/

define("DBF_WRITE",             WBS_ACT_DBF+1);
define("DBF_SEND",              WBS_ACT_DBF+2);
define("DBF_ARCHIVES",          WBS_ACT_DBF+3);
define("DBF_IMPORT",            WBS_ACT_DBF+4);
define("DBF_EXPORT",            WBS_ACT_DBF+5);
define("DBF_EXPORT_REGIS",      WBS_ACT_DBF+6);
define("DBF_EXPORT_DRAWS",      WBS_ACT_DBF+7);
define("DBF_EXPORT_RESULTS",    WBS_ACT_DBF+8);
define("DBF_ARCHIVE_FILE",      WBS_ACT_DBF+9);

define("DBF_IMPORT_SELECT",    WBS_ACT_DBF+20);
define("DBF_IMPORT_READ",      WBS_ACT_DBF+21)
?>