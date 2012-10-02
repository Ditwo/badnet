<?php
/*****************************************************************************
 ******************************************************************************/
class Bniterator implements iterator {
	private $output;
	private $resource;
	private $key;
	private $class_input;
	private $class_output;

	public function __construct($aResource, $aInput, $aOutput)
	{
		$this->resource = $aResource;
		$this->class_input = new $aInput;
		$this->class_output = $aOutput;
	}

	public function key()
	{
		return $this->key;
	}

	public function valid()
	{
		return isset($this->output);
	}

	public function next()
	{
		$this->output = new $this->class_output( $this->class_input('Next_Element', $resource) );
		// C'est mal écrit mais je ne peux pas faire autrement, disons que je dit
		// que je veux faire passer la méthode qui va me permettre de passer à l'élément suivant de ma ressource et renvoyer le résultat de cette méthode dans ma classe d'output.
		$this->key++;
	}

	public function rewind()
	{
		$this->class_input('Rewind_Elements');
		$this->key = 0;
	}

	public function current()
	{
		return $this->output;
	}
}
?>
