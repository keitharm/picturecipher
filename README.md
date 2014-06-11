Picture Cipher
==============
Picture Cipher will encrypt a message (or even a binary) using a symmetric key and convert the output into a png mosaic of colorful pixels.

The picture can be decoded back to the original message if it is provided with the same key that was used to encrypt it.

Usage
-----
Encryption
```
$pic = Picture::encrypt("TEXT", "PASSWORD");
$pic->outputImage();
```
Decryption
```
$pic = Picture::decrypt("CONTENTS", "PASSWORD");
echo $pic->getOutput();
```

Demo
--------

Included with this repo are 2 demo files, encrypt.php and decrypt.php.

Usage examples:

Make an image that is encrypted with the text "Hello World" using password "avocado" and save it to hello.png
```
php encrypt.php "Hello World" avocado > hello.png
```
To decode the message from the image we just made
```
php decrypt.php hello.png avocado
```
