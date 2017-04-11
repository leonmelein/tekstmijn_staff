<?php
class Element_YesNo extends Element_Radio {
	public function __construct($label, $name, array $properties = null) {
		$options = array(
			"1" => "Ja",
			"0" => "Nee"
		);

		if(!is_array($properties))
			$properties = array("inline" => 1);
		elseif(!array_key_exists("inline", $properties))
			$properties["inline"] = 1;
		
		parent::__construct($label, $name, $options, $properties);
    }
}
