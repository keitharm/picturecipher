<?php
class Text
{
    // Characters used in Base 64
    const ORIGINAL = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

    private $input;
    private $password;
    private $cipher;
    private $base64;
    private $output;

    public static function encrypt($input, $password = null) {
        $instance = new self();

        $instance->input    = $input;
        $instance->password = $password;
        $instance->scramble();
        $instance->base64   = $instance->encode($instance->getInput());
        $instance->output   = $instance->swap($instance->getBase64(), $instance->getOriginal(), $instance->getCipher());

        return $instance;
    }

    public static function decrypt($input, $password = null) {
        $instance = new self();

        $instance->input    = $input;
        $instance->password = $password;
        $instance->scramble();
        $instance->base64   = $instance->swap($instance->getInput(), $instance->getCipher(), $instance->getOriginal());
        $instance->output   = $instance->decode($instance->getBase64());

        return $instance;
    }

    private function scramble() {
        // Seed random number generator
        mt_srand(hexdec(substr(md5($this->password), 0, 7)));

        // Shuffle base 64 character string
        $this->setCipher($this->myShuffle($this->getOriginal()));
    }

    private function encode($content) {
        return base64_encode($content);
    }

    private function decode($content) {
        return base64_decode($content);
    }

    private function swap($text, $original, $new) {
        // Replace base64 string with chars from scrambled original
        $len = strlen($text);
        $result = null;

        for ($a = 0; $a < $len; $a++) {
            for ($b = 0; $b < 64; $b++) {
                if ($original[$b] == $text[$a]) {
                    $result .= $new[$b];
                }
            }
        }

        // Return result
        return $result;
    }

    private function myShuffle($str) {
        $split = str_split($str);
        $new = "";

        while (strlen($new) != strlen($str)) {
            $pos = mt_rand(0, count($split)-1);
            $new .= $split[$pos];
            unset($split[$pos]);
            $split = array_values($split);
        }
        return $new;
    }

    public function getOriginal() { return Text::ORIGINAL; }

    // Getters
    public function getInput() { return $this->input; }
    public function getPassword() { return $this->password; }
    public function getCipher() { return $this->cipher; }
    public function getBase64() { return $this->base64; }
    public function getOutput() { return $this->output; }

    // Setters
    private function setInput($val) { $this->input = $val; }
    private function setPassword($val) { $this->password = $val; }
    private function setCipher($val) { $this->cipher = $val; }
    private function setBase64($val) { $this->base64 = $val; }
    private function setOutput($val) { $this->output = $val; }
}
?>
