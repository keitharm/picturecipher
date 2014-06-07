<?php
require_once("Text.class.php");

class Picture
{
	private $input;
	private $password;
	private $text;
	private $image;
	private $bin;

	public static function encrypt($input, $password = null) {
		$instance = new self();

		$instance->input     = $input;
		$instance->password  = $password;
		$instance->text = Text::encrypt($input, $instance->getPassword())->getResult();
		$instance->toBin();

		return $instance;
	}

	public static function decrypt($image, $password = null) {
		$instance = new self();

		return $instance;
	}

	private function charToInt($char) {
		$chars = array(
			'A', 'B', 'C', 'D', 'E',
			'F', 'G', 'H', 'I', 'J',
			'K', 'L', 'M', 'N', 'O',
			'P', 'Q', 'R', 'S', 'T',
			'U', 'V', 'W', 'X', 'Y',
			'Z', 'a', 'b', 'c', 'd',
			'e', 'f', 'g', 'h', 'i',
			'j', 'k', 'l', 'm', 'n',
			'o', 'p', 'q', 'r', 's',
			't', 'u', 'v', 'w', 'x',
			'y', 'z', '0', '1', '2',
			'3', '4', '5', '6', '7',
			'8', '9', '+', '/'
		);
		return array_search($char, $chars);
	}

	private function toBin() {
		// Split encrypted text into array;
		$split = str_split($this->getText());

		// First 3 characters of string are offset.
		// 000 in beginning until we calculate the
		// offset. Then it is updated.
		$str = "000";

		// Convert ascii characters into binary using charToInt mapping
		foreach ($split as $val) {
			$str .= sprintf("%06d", decbin($this->charToInt($val)));
		}

		// Calculate offset for padding in order to make last byte an octet
		$offset = sprintf("%03d", decbin(8 - (strlen($str) % 8)));

		// Replace 1st 3 characters with offset
		$str = substr_replace($str, $offset, 0, 3);

		// Fill rest of last chunk with zeros as padding
		$str .= str_repeat("0", 8 - (strlen($str) % 8));

		// Save binary string
		$this->setBin($str);
	}

	// Getters
	public function getInput() { return $this->input; }
	public function getPassword() { return $this->password; }
	public function getText() { return $this->text; }
	public function getImage() { return $this->image; }
	public function getBin() { return $this->bin; }

	// Setters
	private function setInput($val) { $this->input = $val; }
	private function setPassword($val) { $this->password = $val; }
	private function setText($val) { $this->text = $val; }
	private function setImage($val) { $this->image = $val; }
	private function setBin($val) { $this->bin = $val; }
}
?>
