<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Crypt;

class EncryptionHelper
{

    public static function encryptText($text, $key) {
        $cipher = 'AES-256-CBC'; // Choose a secure cipher method
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher)); // Generate a random IV
        
        // Encrypt the text with the key and IV
        $encryptedContent = openssl_encrypt($text, $cipher, $key, 0, $iv);
        
        // Store the IV with the encrypted text (needed for decryption)
        $encryptedText = base64_encode($iv . $encryptedContent);
        
        return $encryptedText; // Return the encrypted text with the IV
    }

    public static function decryptText($encryptedText, $key) {
        $cipher = 'AES-256-CBC';
        $decodedText = base64_decode($encryptedText); // Decode the base64-encoded text
        
        // Get the IV length and extract it from the decoded text
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = substr($decodedText, 0, $ivLength); // Extract the IV
        $encryptedContent = substr($decodedText, $ivLength); // The actual encrypted content
        
        // Decrypt the content using the key and IV
        $decryptedText = openssl_decrypt($encryptedContent, $cipher, $key, 0, $iv);
        
        return $decryptedText; // Return the decrypted text
    }
}
