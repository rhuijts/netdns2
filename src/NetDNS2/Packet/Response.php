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
 * @since     File available since Release 0.6.0
 *
 */

namespace NetDNS2\Packet;

/**
 * This class handles building new DNS response packets; it parses binary packed
 * packets that come off the wire
 * 
 */
class Response extends \NetDNS2\Packet
{
    /*
     * The name servers that this response came from
     */
    public $answer_from;

    /*
     * The socket type the answer came from (TCP/UDP)
     */
    public $answer_socket_type;

    /*
     * The query response time in microseconds
     */
    public $response_time = 0;

    /**
     * Constructor - builds a new \NetDNS2\Packet\Response object
     *
     * @param string  $data binary DNS packet
     * @param integer $size the length of the DNS packet
     *
     * @throws \NetDNS2\Exception
     * @access public
     *
     */
    public function __construct($data, $size)
    {
        $this->set($data, $size);
    }

    /**
     * builds a new \NetDNS2\Packet\Response object
     *
     * @param string  $data binary DNS packet
     * @param integer $size the length of the DNS packet
     *
     * @return boolean
     * @throws \NetDNS2\Exception
     * @access public
     *
     */
    public function set($data, $size)
    {
        //
        // store the full packet
        //
        $this->rdata    = $data;
        $this->rdlength = $size;

        //
        // parse the header
        // 
        // we don't bother checking the size earlier, because the first thing the
        // header class does, is check the size and throw and exception if it's
        // invalid.
        //
        $this->header = new \NetDNS2\Header($this);

        //
        // if the truncation bit is set, then just return right here, because the
        // rest of the packet is probably empty; and there's no point in processing
        // anything else.
        //
        // we also don't need to worry about checking to see if the the header is 
        // null or not, since the \NetDNS2\Header() constructor will throw an 
        // exception if the packet is invalid.
        //
        if ($this->header->tc == 1)
        {
            return false;
        }

        //
        // parse the questions
        //
        for($x = 0; $x < $this->header->qdcount; ++$x)
        {
            $this->question[$x] = new \NetDNS2\Question($this);
        }

        //
        // parse the answers
        //
        for($x = 0; $x < $this->header->ancount; ++$x)
        {
            $o = \NetDNS2\RR::parse($this);

            if (is_null($o) == false)
            {
                $this->answer[] = $o;
            }
        } 

        //
        // parse the authority section
        //
        for($x = 0; $x < $this->header->nscount; ++$x)
        {
            $o = \NetDNS2\RR::parse($this);

            if (is_null($o) == false)
            {
                $this->authority[] = $o;  
            }
        }

        //
        // parse the additional section
        //
        for($x = 0; $x < $this->header->arcount; ++$x)
        {
            $o = \NetDNS2\RR::parse($this);

            if (is_null($o) == false)
            {
                $this->additional[] = $o; 
            }
        }

        return true;
    }
}
