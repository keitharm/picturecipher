<?php
require_once("Text.class.php");

class Picture
{
    const VERSION = "1.0.0";
    const SEP = "^";
    const END = "#";

    private $input;
    private $password;
    private $text;
    private $binary;
    private $output;
    private $offset;
    private $meta;
    private $options = array(
        "useVersion" => 1,
        "useQuickcheck" => 1,
        "useChecksum" => 1,
        "useDate" => 1
    );

    public static function encrypt($input, $password = null) {
        $instance = new self();

        // Set input and password
        $instance->input     = $input;
        $instance->password  = $password;

        // Create Text object using input and password
        $instance->text      = Text::encrypt($instance->getInput(), $instance->getPassword());

        // Convert Text object output into binary
        $instance->binary    = $instance->textToBin($instance->getText()->getOutput());

        return $instance;
    }

    public static function decrypt($input, $password = null) {
        $instance = new self();

        // Set input and password
        $instance->input    = $input;
        $instance->password = $password;

        // Get meta info
        $instance->extractMetaInfo();

        // Perform quickcheck if available
        $instance->isValidPassword();

        // Convert image into binary
        $instance->binary   = $instance->decodeImage();

        // Perform checksum verification
        $instance->isValidChecksum();

        // Convert binary to Text object
        $instance->text     = Text::decrypt($instance->binToText($instance->getBinary()), $instance->getPassword());

        // Decrypt text object to original input
        $instance->output   = $instance->getText()->getOutput();

        return $instance;
    }

    public static function check($input) {
        $instance = new self();

        // Set input
        $instance->input    = $input;

        // Get meta info
        $instance->extractMetaInfo();

        return $instance;
    }

    public function outputImage() {
        // Add meta information to input
        $this->genMetaInfo();

        $chunks = $this->getChunks();

        // Add extra chunks to make total chuncks divisible by 3 for complete RGB pixels
        while (count($chunks) % 3 != 0) {
            // How many chunks extra in last pixel
            $last++;
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

        echo "\n" . Text::encrypt($this->getMeta(), "potato")->getOutput() . "|" . Text::encrypt((int)$last, "potato")->getOutput() . "|" . Text::encrypt(count($chunks), "potato")->getOutput();
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

        // Convert each pixel color from decimal to it's binary value
        for ($a = 0; $a < $this->meta["data_end"]-$this->meta["last"]; $a++) {
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

    private function textToBin($content) {
        $content_len = strlen($content);

        // Convert content into binary
        for ($a = 0; $a < $content_len; $a++) {
            $bin .= sprintf("%06d", decbin($this->charToInt($content[$a])));
        }

        // Fill rest of last chunk with zeros as padding and calculate offset
        $offset = substr(sprintf("%03d", decbin(8 - (strlen($bin) % 8))), -3);
        $this->setOffset($offset);
        $bin .= str_repeat("0", bindec($offset));

        // Return binary string
        return $bin;
    }

    private function genMetaInfo() {
        // Initialize meta
        $meta = null;

        if ($this->useVersion()) {
            $meta .= Picture::VERSION;
        }
        $meta .= Picture::SEP;

        if ($this->useChecksum()) {
            $meta .= md5($this->getBinary());
        }
        $meta .= Picture::SEP;

        if ($this->useQuickcheck()) {
            $meta .= Text::encrypt("Hello World", $this->getPassword())->getOutput();
        }
        $meta .= Picture::SEP;

        if ($this->useDate()) {
            $meta .= time();
        }
        $meta .= Picture::END;

        // Save meta
        $this->setMeta($meta);
    }

    private function extractMetaInfo() {
        $meta = $this->tailCustom($this->getInput(), 1);
        $meta_parts = explode("|", $meta);

        foreach ($meta_parts as &$part) {
            $part = Text::decrypt($part, "potato")->getOutput();
        }

        $info = explode("^", substr($meta_parts[0], 0, -1));
        $meta_parts[0] = $info;

        // Default options
        $this->setMeta(array(
            "version" => $meta_parts[0][0],
            "checksum" => $meta_parts[0][1],
            "quickcheck" => $meta_parts[0][2],
            "date" => array(
                "timestamp" => $meta_parts[0][3],
                "formatted" => @date("F j, Y, g:i:s a", $meta_parts[0][3])
            ),
            "last" => $meta_parts[1],
            "data_end" => $meta_parts[2]
        ));
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

    public function binToText($bin) {
        $chars = explode(" ", chunk_split($bin, 6, " "));

        // Unset last empty char chunk
        unset($chars[count($chars)-1]);

        foreach ($chars as $char) {
            $str .= $this->intToChar(bindec($char));
        }

        return $str;
    }

    private function isValidPassword() {
        if ($this->getMeta("quickcheck") != null) {
            if ($this->getMeta("quickcheck") != Text::encrypt("Hello World", $this->getPassword())->getOutput()) {
                die("Error: Invalid Password\n");
            }
        }
    }

    private function isValidChecksum() {
        if ($this->getMeta("checksum") != null) {
            if ($this->getMeta("checksum") != md5($this->getBinary())) {
                die("Error: Corrupt data\n");
            }
        }
    }

    private function tailCustom($filepath, $lines = 1, $adaptive = true) {
        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) return false;

        // Sets buffer size
        if (!$adaptive) $buffer = 4096;
        else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") $lines -= 1;

        // Start reading
        $output = '';
        $chunk = '';

        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);

            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);

            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;

            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");

        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }

        // Close file and return
        fclose($f);
        return trim($output);
    }

    private function useVersion() {
        return $this->getOption("useVersion");
    }
    private function useQuickcheck() {
        return $this->getOption("useQuickcheck");
    }
    private function useChecksum() {
        return $this->getOption("useChecksum");
    }
    private function useDate() {
        return $this->getOption("useDate");
    }

    // Getters
    public function getInput() { return $this->input; }
    public function getPassword() { return $this->password; }
    public function getText() { return $this->text; }
    public function getBinary() { return $this->binary; }
    public function getOutput() { return $this->output; }
    public function getOffset() { return $this->offset; }
    public function getMeta($field = null) { return (!$field) ? $this->meta : $this->meta[$field]; }
    public function getOption($field) { return $this->options[$field]; }

    // Setters
    public function setOption($field, $val) { $this->options[$field] = $val; }

    private function setInput($val) { $this->input = $val; }
    private function setPassword($val) { $this->password = $val; }
    private function setText($val) { $this->text = $val; }
    private function setBinary($val) { $this->binary = $val; }
    private function setOutput($val) { $this->output = $val; }
    private function setOffset($val) { $this->offset = $val; }
    private function setMeta($val) { $this->meta = $val; }
}
?>
