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
Meta data check
```
$pic = Picture::check("CONTENTS");
print_r($pic->getMeta());
```

Demo
----

Included with this repo are 3 demo files: encrypt.php, decrypt.php, and check.php.

Usage examples:

Make an image that is encrypted using text file "hello.txt" using password "avocado" and save it to hello.png
```
php encrypt.php hello.txt avocado > hello.png
```
To decode the message from the image we just made
```
php decrypt.php hello.png avocado
```
If you want to view the meta data of the image we just made
```
php check.php hello.png
```

Extra encryption options
------------------------
There are 4 (or 5 if you count password) options that you can include with encrypting a file into an image
All of the options are enabled by default.

1. Version
    - Include the version information of the picturecipher
2. Quickcheck
    - Include a hash of the phrase "Hello World" encrypted using the same password that the image was encrypted with.
        - The picturecipher will attempt to encrypt the text "Hello World" using the password you supply and see if it matches the encrypted image's hash.
    - This can be a security hazard since it will be much faster to bruteforce an encrypted image vs having to wait for the image to decrypt and seeing if the output is garbled
3. Checksum
    - Include a checksum of the data to verify if the file has been tampered with.
4. Date
    - Include the date that the file was encrypted

By default, all of the options are enabled.
In order to disable the options, you must include the following lines with the options you want to disable before calling outputImage().
```
// Init. picture
$pic = Picture::encrypt("TEXT", "PASSWORD");

// Set options
$pic->setOption("useVersion",    false);
$pic->setOption("useQuickcheck", false);
$pic->setOption("useChecksum",   false);
$pic->setOption("useDate",       false);

// Output image with selected options
$pic->outputImage();
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
    - These values that are mapped from base64 to ints are 6 bit numbers.
5. 8 bit chunks are taken from the binary string and are converted into integers for pixels.
    - 3 8-bit chunks per pixel.
    - The reason why alpha channel is not used is because PHP GD library only allows a 7-bit alpha channel for some stupid reason. :(
6. The rest of the pixels that are unused are filled with (0, 0, 0) pixels representing null data.
7. The picture meta data is added at the end of the PNG file's iEND chunk