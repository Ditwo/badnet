    // {{{ _displayFormSample()
    /**
     * Print the menu with html tag
     *
     * @access public
     * @return void
     */
    function _displayFormSample()
    {
  
        $form = new KForm("users");

	$form->addEdit("obligatoire :",$GLOBALS['HTTP_GET_VARS']['action'], 10 );
	$form->setMaxLength("obligatoire :", 5);
	$form->addEdit("Facultatif :",$GLOBALS['HTTP_GET_VARS']['action'],5 );
	$form->noMandatory("Facultatif :");

	$form->addInfo("Une information affichée :","Date de création");

        $values = array( '1' => 'un', '2' => 'deux', '3' => 'trois');
	$form->addCombo("choix obligatoire:", $values);

        $values = array( '1' => 'douze', '2' => 'mille', '3' => 'et puis quoi');
	$form->addCombo("choix facultatif:", $values);
	$form->noMandatory("choix facultatif:");
	$form->setLength("choix facultatif:", 5);

	$form->addBtn("Go");

	$form->addRadio("radio1", true, 'toto');
	$form->addRadio("radio1", true, 'titi');
	$form->addRadio("radio3",false);

	$form->addCheck("check1", true, 'toto');
	$form->addCheck("check2", false, 'titi');
	$form->addCheck("check3", true);

	$form->addTitle("Example des possibilitésde Kform");

	$form->addMsg("Un message de test");

	$form->addWng("Attention: il manque un blanc dans laforme");

        $form->display();

       exit; 
    }
    // }}}
