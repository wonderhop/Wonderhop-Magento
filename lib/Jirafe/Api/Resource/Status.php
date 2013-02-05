<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API status object.
 *
 * @author Fooman Ltd
 */
class Jirafe_Api_Resource_Status extends Jirafe_Api_Object
{
    /**
     * Initializes status object.
     *
     * @param   integer                      $id         site ID
     * @param   Jirafe_Api_Collection_Orders $collection orders collection
     * @param   Jirafe_Client                $client     API client
     */
    public function __construct(Jirafe_Api_Collection_Orders $collection, Jirafe_Client $client)
    {
        parent::__construct(null, $collection, $client);
    }

    /**
     * Returns status path name.
     *
     * @return  string
     */
    public function getPath()
    {
        return sprintf('%s/%s', $this->getCollection()->getPath(), 'status');
    }

}
