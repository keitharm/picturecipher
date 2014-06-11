<?php
require_once("Text.class.php");

class Picture
{
    private $input;
    private $password;
    private $text;
    private $binary;
    private $output;

    public static function encrypt($input, $password = null) {
        $instance = new self();

        // Set input and password
        $instance->input     = $input;
        $instance->password  = $password;

        // Create Text object using input and password
        $instance->text      = Text::encrypt($instance->getInput(), $instance->getPassword());

        // Convert Text object output into binary
        $instance->binary    = $instance->toBin($instance->getText()->getOutput());

        return $instance;
    }

    public static function decrypt($input, $password = null) {
        $instance = new self();

        // Set input and password
        $instance->input    = $input;
        $instance->password = $password;

        // Convert image into binary
        $instance->binary   = $instance->decodeImage();

        // Convert binary to Text object
        $instance->text     = Text::decrypt($instance->fromBin($instance->getBinary()), $instance->getPassword());

        // Decrypt text object to original input
        $instance->output   = $instance->getText()->getOutput();

        return $instance;
    }

    public function outputImage() {
        $chunks = $this->getChunks();

        // Add extra chunks to make total chuncks divisible by 3 for complete RGB pixels
        while (count($chunks) % 3 != 0) {
            $chunks[] = 0xFF;
        }

        // Calculate dimensions of image
        $dim = ceil(sqrt(count($chunks) / 3));
        $img = imagecreatetruecolor($dim, ceil(count($chunks) / $dim / 3));
        $colors = array();

        // Convert groups of 3 chunks into pixels
        for ($a = 0; $a < count($chunks); $a += 3) {
            array_push($colors, imagecolorallocate($img, bindec($chunks[$a]), bindec($chunks[$a+1]), bindec($chunks[$a+2])));
        }

        // Set color for each pixel
        for ($j = 0; $j < $dim; $j++) {
            for ($i = 0; $i < $dim; $i++) {
                @imagesetpixel($img, $i, $j, $colors[($j*$dim)+$i]);
            }
        }

        // Output image
        imagepng($img);
        imagedestroy($img);
    }

    private function decodeImage() {
        // Array filled with information regarding the image
        $imageinfo = getimagesize($this->getInput());

        // Height in pixels
        $height = $imageinfo[1];

        // Width in pixels
        $width = $imageinfo[0];

        // initialize result and prev
        $result = null;
        $prev = null;

        // Create image
        $im = @imagecreatefrompng($this->getInput());

        // Finds the index of the color of each pixel in the image
        for ($j = 0; $j < $height; $j++) {
            for ($i = 0; $i < $width; $i++) {
                // Adds the index of the colors into the $chars array
                $chars[] = imagecolorsforindex($im, imagecolorat($im, $i, $j));
            }
        }

        // Push RGB pixel colors into values array
        for ($a = 0; $a < count($chars); $a++) {
            $values[] = $chars[$a]["red"];
            $values[] = $chars[$a]["green"];
            $values[] = $chars[$a]["blue"];
        }

        // Hack that currently works. Needs to be replaced with something more reliable for detecting where the data ends.
        // Possibly add position data ends at at the EOF.
        while ($values[count($values)-1] == 0xFF || $values[count($values)-1] == 0x00) {
            if ($prev == 0xFF && $values[count($values)-1] == 0x00) {
                break;
            }
            $prev = $values[count($values)-1];
            unset($values[count($values)-1]);
        }

        // Convert each pixel color from decimal to it's binary value
        for ($a = 0; $a < count($values); $a++) {
            $result .= sprintf("%08d", decbin($values[$a]));
        }
        return $result;
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
        // Initialize str
        $str = null;

        $offset = substr($content, 0, 3);
        $remove = bindec($offset);
        // Remove the zero padding (if it exists)
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

    private function getChunks() {
        // Split binary string into byte chunks
        $chunks = explode(" ", chunk_split($this->getBinary(), 8, " "));

        // Unset null byte
        unset($chunks[count($chunks)-1]);

        // Reindex array
        $chunks = array_values($chunks);

        return $chunks;
    }

    // Getters
    public function getInput() { return $this->input; }
    public function getPassword() { return $this->password; }
    public function getText() { return $this->text; }
    public function getBinary() { return $this->binary; }
    public function getMedium() { return $this->medium; }
    public function getOutput() { return $this->output; }

    // Setters
    private function setInput($val) { $this->input = $val; }
    private function setPassword($val) { $this->password = $val; }
    private function setText($val) { $this->text = $val; }
    private function setBinary($val) { $this->binary = $val; }
    private function setMedium($val) { $this->medium = $val; }
    private function setOutput($val) { $this->output = $val; }
}
?>
