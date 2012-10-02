/**********************************************
 !   $Id$
 *********************************************/
function pageAccueil()
{

  $("#calendar").datepicker({ 
    numberOfMonths: 1
    ,minDate: new Date(2005, 1 - 1, 1)
    ,maxDate: '+1y'
    ,changeMonth: false
    ,changeYear: false
    ,changeFirstDay: false
  }, $.datepicker.regional['fr']);

}

