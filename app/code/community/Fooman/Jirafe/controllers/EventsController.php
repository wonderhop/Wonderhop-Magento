<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Fooman_Jirafe
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_EventsController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $token = $this->getRequest()->getParam('confirmToken');
        $hash = $this->getRequest()->getParam('hash');
        $version = $this->getRequest()->getParam('version');
        $siteId = $this->getRequest()->getParam('siteId');
        Mage::helper('foomanjirafe')->debug('token '.$token.' hash '.$hash.' version '.$version. ' site id ' .$siteId);
        $response = $this->getResponse();

        if($token && $hash && $siteId) {
            $jirafe = Mage::getModel('foomanjirafe/jirafe');
            $event = Mage::getResourceModel('foomanjirafe/event');
            if($jirafe->checkEventsToken($token, $hash)) {
                if ($event->acquireAdvisoryLock($siteId)) {
                    try{
                        if (!$jirafe->postEvents($token, $siteId, $version+1)) {
                            $responseCode = 500;
                        }
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $reponseCode = 501;
                    }
                    $event->releaseAdvisoryLock($siteId);
                } else {
                    $responseCode = 503;
                }
            } else {
                $responseCode = 401;
            }
        } else {
            $responseCode = 400;
        }

        if (empty($responseCode)) {
            $response->setBody("OK\n");
        } else {
            $response->setHttpResponseCode($responseCode);
            $response->setBody("KO\n");
        }
    }

}
