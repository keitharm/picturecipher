<?php
require_once("Text.class.php");

class Picture
{
	private $input;
	private $password;
	private $medium;
	private $image;
	private $output;

	public static function encrypt($input, $password = null) {
		$instance = new self();

		$instance->input     = $input;
		$instance->password  = $password;
		$instance->medium    = Text::encrypt($instance->getInput(), $instance->getPassword())->getResult();
		$instance->output    = $instance->toBin($instance->getMedium());

		return $instance;
	}

	public static function decrypt($image, $password = null) {
		$instance = new self();
		$instance->input    = $image;
		$instance->password = $password;
		$instance->medium   = $instance->fromBin($instance->getInput());
		$instance->output   = Text::decrypt($instance->getMedium(), $instance->getPassword())->getResult();

		return $instance;
	}

	private function charToInt($char) {
		return array_search($char, $this->chars());
	}

	private function intToChar($int) {
		return $this->chars()[$int];
	}

	private function toBin($content) {
		// Split encrypted text into array;
		$split = str_split($content);

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

		// Return binary string
		return $str;
	}

	private function fromBin($content) {
		$offset = substr($content, 0, 3);
		$remove = bindec($offset);
		// Remove the zero padding (if it exists)
		#echo $content . "\n";
		if ($remove == 0) {
			$new = substr($content, 3);
		} else {
			$new = substr(substr($content, 0, -$remove), 3);
		}

		// Remove 1st 3 characters for padding calculation
		$chars = explode(" ", chunk_split($new, 6, " "));

		// Unset last empty char chunk
		unset($chars[count($chars)-1]);

		foreach ($chars as $char) {
			$str .= $this->intToChar(bindec($char));
		}

		// Return string
		return $str;
	}

	private function chars() {
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
		return $chars;
	}

	// Getters
	public function getInput() { return $this->input; }
	public function getPassword() { return $this->password; }
	public function getMedium() { return $this->medium; }
	public function getImage() { return $this->image; }
	public function getOutput() { return $this->output; }

	// Setters
	private function setInput($val) { $this->input = $val; }
	private function setPassword($val) { $this->password = $val; }
	private function setMedium($val) { $this->medium = $val; }
	private function setImage($val) { $this->image = $val; }
	private function setOutput($val) { $this->output = $val; }
}
?>
