<?php

/**
 * DNS Library for handling lookups and updates. 
 *
 * Copyright (c) 2020, Mike Pultz <mike@mikepultz.com>. All rights reserved.
 *
 * See LICENSE for more details.
 *
 * @category  Networking
 * @package   NetDNS2
 * @author    Mike Pultz <mike@mikepultz.com>
 * @copyright 2020 Mike Pultz <mike@mikepultz.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      https://netdns2.com/
 * @since     File available since Release 1.1.0
 *
 */

namespace NetDNS2;

/**
 * SSL Private Key container class
 * 
 */
class PrivateKey
{
    /*
     * the filename that was loaded; stored for reference
     */
    public $filename;

    /*
     * the keytag for the signature
     */
    public $keytag;

    /*
     * the sign name for the signature
     */
    public $signname;

    /*
     * the algorithm used for the signature
     */
    public $algorithm;

    /*
     * the key format of the signature
     */
    public $key_format;

    /*
     * the openssl private key id
     */
    public $instance;

    /*
     * RSA: modulus
     */
    private $_modulus;

    /*
     * RSA: public exponent
     */
    private $_public_exponent;

    /*
     * RSA: rivate exponent
     */
    private $_private_exponent;

    /*
     * RSA: prime1
     */
    private $_prime1;

    /*
     * RSA: prime2
     */
    private $_prime2;

    /*
     * RSA: exponent 1
     */
    private $_exponent1;

    /*
     * RSA: exponent 2
     */
    private $_exponent2;

    /*
     * RSA: coefficient
     */
    private $_coefficient;

    /*
     * DSA: prime
     */
    public $prime;
    
    /*
     * DSA: subprime
     */
    public $subprime;

    /*
     * DSA: base
     */
    public $base;

    /*
     * DSA: private value
     */
    public $private_value;

    /*
     * DSA: public value
     */
    public $public_value;

    /**
     * Constructor - base constructor the private key container class
     * 
     * @param string $file path to a private-key file to parse and load
     *
     * @throws \NetDNS2\Exception
     * @access public
     * 
     */
    public function __construct($file = null)
    {
        if (is_null($file) == false)
        {
            $this->parseFile($file);
        }
    }

    /**
     * parses a private key file generated by dnssec-keygen
     * 
     * @param string $file path to a private-key file to parse and load
     *
     * @return boolean
     * @throws \NetDNS2\Exception
     * @access public
     * 
     */
    public function parseFile($file)
    {
        //
        // check for OpenSSL
        //
        if (extension_loaded('openssl') === false)
        {
            throw new \NetDNS2\Exception('the OpenSSL extension is required to use parse private key.', \NetDNS2\Lookups::E_OPENSSL_UNAVAIL);
        }

        //
        // check to make sure the file exists
        //
        if (is_readable($file) == false)
        {
            throw new \NetDNS2\Exception('invalid private key file: ' . $file, \NetDNS2\Lookups::E_OPENSSL_INV_PKEY);
        }

        //
        // get the base filename, and parse it for the local value
        //
        $keyname = basename($file);

        if (strlen($keyname) == 0)
        {
            throw new \NetDNS2\Exception('failed to get basename() for: ' . $file, \NetDNS2\Lookups::E_OPENSSL_INV_PKEY);
        }

        //
        // parse the keyname
        //
        if (preg_match("/K(.*)\.\+(\d{3})\+(\d*)\.private/", $keyname, $matches) == 1)
        {
            $this->signname    = $matches[1];
            $this->algorithm   = intval($matches[2]);
            $this->keytag      = intval($matches[3]);

        } else
        {
            throw new \NetDNS2\Exception('file ' . $keyname . ' does not look like a private key file!', \NetDNS2\Lookups::E_OPENSSL_INV_PKEY);
        }

        //
        // read all the data from the
        //
        $data = file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        if (count($data) == 0)
        {
            throw new \NetDNS2\Exception('file ' . $keyname . ' is empty!', \NetDNS2\Lookups::E_OPENSSL_INV_PKEY);
        }

        foreach($data as $line)
        {
            list($key, $value) = explode(':', $line);

            $key    = trim($key);
            $value  = trim($value);

            switch(strtolower($key))
            {
                case 'private-key-format':
                {
                    $this->key_format = $value;
                }
                break;
                case 'algorithm':
                {
                    if ($this->algorithm != $value)
                    {
                        throw new \NetDNS2\Exception('Algorithm mis-match! filename is ' . $this->algorithm . ', contents say ' . $value,
                            \NetDNS2\Lookups::E_OPENSSL_INV_ALGO);
                    }
                }
                break;

                //
                // RSA
                //
                case 'modulus':
                {
                    $this->_modulus = $value;
                }
                break;
                case 'publicexponent':
                {
                    $this->_public_exponent = $value;
                }
                break;
                case 'privateexponent':
                {
                    $this->_private_exponent = $value;
                }
                break;
                case 'prime1':
                {
                    $this->_prime1 = $value;
                }
                break;
                case 'prime2':
                {
                    $this->_prime2 = $value;
                }
                break;
                case 'exponent1':
                {
                    $this->_exponent1 = $value;
                }
                break;
                case 'exponent2':
                {
                    $this->_exponent2 = $value;
                }
                break;
                case 'coefficient':
                {
                    $this->_coefficient = $value;
                }
                break;

                //
                // DSA - this won't work in PHP until the OpenSSL extension is better
                //
                case 'prime(p)':
                {
                    $this->prime = $value;
                }
                break;
                case 'subprime(q)':
                {
                    $this->subprime = $value;
                }
                break;
                case 'base(g)':
                {
                    $this->base = $value;
                }
                break;
                case 'private_value(x)':
                {
                    $this->private_value = $value;
                }
                break;
                case 'public_value(y)':
                {
                    $this->public_value = $value;
                }
                break;

                default:
                {
                    throw new \NetDNS2\Exception('unknown private key data: ' . $key . ': ' . $value, \NetDNS2\Lookups::E_OPENSSL_INV_PKEY);
                }
            }
        }

        //
        // generate the private key
        //
        $args = [];

        switch($this->algorithm)
        {
            //
            // RSA
            //
            case \NetDNS2\Lookups::DNSSEC_ALGORITHM_RSAMD5:
            case \NetDNS2\Lookups::DNSSEC_ALGORITHM_RSASHA1:
            case \NetDNS2\Lookups::DNSSEC_ALGORITHM_RSASHA256:
            case \NetDNS2\Lookups::DNSSEC_ALGORITHM_RSASHA512:
            {
                $args = [

                    'rsa' => [

                        'n'     => base64_decode($this->_modulus),
                        'e'     => base64_decode($this->_public_exponent),
                        'd'     => base64_decode($this->_private_exponent),
                        'p'     => base64_decode($this->_prime1),
                        'q'     => base64_decode($this->_prime2),
                        'dmp1'  => base64_decode($this->_exponent1),
                        'dmq1'  => base64_decode($this->_exponent2),
                        'iqmp'  => base64_decode($this->_coefficient)
                    ]
                ];
            }
            break;

            //
            // DSA - this won't work in PHP until the OpenSSL extension is better
            //
            case \NetDNS2\Lookups::DNSSEC_ALGORITHM_DSA:
            {
                $args = [

                    'dsa' => [

                        'p'         => base64_decode($this->prime),
                        'q'         => base64_decode($this->subprime),
                        'g'         => base64_decode($this->base),
                        'priv_key'  => base64_decode($this->private_value),
                        'pub_key'   => base64_decode($this->public_value)
                    ]
                ];
            }
            break;
            default:
            {
                throw new \NetDNS2\Exception('we only currently support RSAMD5 and RSASHA1 encryption.', \NetDNS2\Lookups::E_OPENSSL_INV_PKEY);
            }
        }

        //
        // generate and store the key
        //
        $this->instance = openssl_pkey_new($args);

        if ($this->instance === false)
        {
            throw new \NetDNS2\Exception(openssl_error_string(), \NetDNS2\Lookups::E_OPENSSL_ERROR);
        }

        //
        // store the filename incase we need it for something
        //
        $this->filename = $file;

        return true;
    }
}
