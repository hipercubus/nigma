<?php
/**
 * Copyright (c) 2016 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @category   WURFL
 * @package	WURFL_Request_UserAgentNormalizer_Generic
 * @copyright  ScientiaMobile, Inc.
 * @license	GNU Affero General Public License
 * @version	$id$
 */
/**
 * User Agent Normalizer - clean IIS Logging from user agent
 * @package	WURFL_Request_UserAgentNormalizer_Generic
 * @deprecated
 */
class WURFL_Request_UserAgentNormalizer_Generic_IISLogging implements WURFL_Request_UserAgentNormalizer_Interface
{
    /**
     * This method clean the IIS logging from user agent string.
     *
     * @param string $userAgent
     * @return string Normalized user agent
     */
    public function normalize($userAgent)
    {
        //If there are no spaces in a UA and more than 2 plus symbols, the UA is likely affected by IIS style logging issues 
        if (substr_count($userAgent, ' ') === 0 and substr_count($userAgent, '+') > 2) {
            $userAgent = str_replace('+', ' ', $userAgent);
        }

        return $userAgent;
    }
}
