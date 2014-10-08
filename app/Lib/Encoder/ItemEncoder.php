<?php
class ItemEncoder {
	//Example of Encode:
	//LOLALERTER_Redback93

	static private $leading = 'LOLALERTER_';

	static public function EncodeItem($username) {
		return ItemEncoder::$leading.$username;
	}

	static public function GetUsername($item) {
		return str_replace(ItemEncoder::$leading,'',$item);
	}
}