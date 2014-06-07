<?php
class Picture
{
	// Base 64 characters
	const ORIGINAL = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

	private $input;
	private $base64;
	private $password;
	private $cipher;
	private $input_len;
	private $result;

	public static function encrypt($input, $password = null) {
		$instance = new self();

		$instance->initInput($input);
		$instance->setPassword($password);
		$instance->scramble();
		$instance->setBase64($instance->encode());
		$instance->setResult($instance->swap($instance->getBase64(), $instance->getOriginal(), $instance->getCipher()));

		return $instance;
	}

	public static function decrypt($input, $password = null) {
		$instance = new self();

		$instance->initInput($input);
		$instance->setPassword($password);
		$instance->scramble();
		$instance->setBase64($instance->getInput());
		$instance->setBase64($instance->swap($instance->getBase64(), $instance->getCipher(), $instance->getOriginal()));
		$instance->setResult($instance->decode());

		return $instance;
	}

	private function initInput($input) {
		$this->setInput($input);
	}

	private function scramble() {
		// Seed random number generator
		srand(hexdec(substr(md5($this->password), 0, 10)));

		// Shuffle ORIGINAL string
    	$this->setCipher(str_shuffle(Picture::ORIGINAL));
	}

	private function encode() {
		return base64_encode($this->getInput());
	}

	private function decode() {
		return base64_decode($this->getBase64());
	}

	private function swap($text, $original, $new) {
		// Replace base64 string with chars from scrambled original
		$len = strlen($text);

	    for ($a = 0; $a < $len; $a++) {
	        for ($b = 0; $b < 65; $b++) {
	            if ($original[$b] == $text[$a]) {
	                $result .= $new[$b];
	            }
	        }
	    }

	    // Return result
	    return $result;
	}

	// Getters
	public function getOriginal() { return Picture::ORIGINAL; }

	public function getInput() { return $this->input; }
	public function getBase64() { return $this->base64; }
	public function getPassword() { return $this->password; }
	public function getCipher() { return $this->cipher; }
	public function getResult() { return $this->result; }

	// Setters
	private function setInput($val) { $this->input = $val; }
	private function setBase64($val) { $this->base64 = $val; }
	private function setPassword($val) { $this->password = $val; }
	private function setCipher($val) { $this->cipher = $val; }
	private function setResult($val) { $this->result = $val; }
}
?>
