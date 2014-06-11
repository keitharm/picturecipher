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
----

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

How it works
------------
1. Using the password that is supplied, the random number generator is seeded and a string containing base64 characters is shuffled.
2. The input that is to be encrypted is converted into base64.
3. The base64 encoded input now has its characters swapped with the base64 string that was shuffled.

	```
	Input:                                  Hello World
	Base64 encoded input:                   SGVsbG8gV29ybGQ=
	Original String base64 string:          ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/
	Scrambled string using password potato: ogImJ6HhezlyiY91FWUA7DtvVwaSx3/+ETGObsRZc025qpBNMXQC8KfLnkj4Pdru
	New base64 string after character swap: UHDqSHPEDfdQSHF
	```
4. Base64 encoded and swapped string is converted to binary by mapping the base64 characters to their respective integer values.
    - First 3 characters is offset calculation for how many extra zeros are added as padding to make string divisible by 8.
    - These values that are mapped from base64 to ints are 6 bit numbers.
5. 8 bit chunks are taken from the binary string and are converted into integers for pixels.
    - 3 8-bit chunks per pixel.
    - The reason why alpha channel is not used is because PHP GD library only allows a 7-bit alpha channel for some stupid reason. :(
6. The rest of the pixels that are unused are filled with (0, 0, 0) pixels representing null data.
