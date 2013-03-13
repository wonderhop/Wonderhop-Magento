<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API orders report.
 *
 * @author Fooman Ltd
 */
class Jirafe_Api_Collection_Orders extends Jirafe_Api_Collection
{
    /**
     * Initializes orders collection.
     *
     * @param   Jirafe_Api_Resource_Site        $parent site resource
     * @param   Jirafe_Client                   $client API client
     */
    public function __construct(Jirafe_Api_Resource_Site $parent, Jirafe_Client $client)
    {
        parent::__construct($parent, $client);
    }

    public function status()
    {
        return new Jirafe_Api_Resource_Status($this, $this->getClient());
    }
}
