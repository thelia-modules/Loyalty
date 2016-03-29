<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Loyalty\Controller;

use Loyalty\Form\LoyaltyCreateForm;
use Loyalty\Model\Loyalty;
use Loyalty\Model\LoyaltyQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Loyalty\Loyalty as LoyaltyModule;


/**
 * Class LoyaltyAdminController
 * @package Loyalty\Controller
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */
class LoyaltyAdminController extends BaseAdminController
{
    public function updateAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::UPDATE)) {
            return $response;
        }
        $request = $this->getRequest();
        
        $loyaltiesMin = $request->request->get('loyalty_min', []);
        $loyaltiesMax = $request->request->get('loyalty_max', []);
        $loyaltiesAmount = $request->request->get('loyalty_amount', []);

        foreach ($loyaltiesMin as $id => $value) {
            $loyalty = LoyaltyQuery::create()->findPk($id);

            $loyalty
                ->setMin($value)
                ->setMax($loyaltiesMax[$id])
                ->setAmount($loyaltiesAmount[$id])
                ->save();
        }
    
        $mode = $request->request->get('loyalty_mode', 'multiple');
        $unique_slice_amount = $request->request->get('unique_slice_amount', '');
        $unique_slice_credit = $request->request->get('unique_slice_credit', '');

        LoyaltyModule::setConfigValue('unique_slice_amount', $unique_slice_amount);
        LoyaltyModule::setConfigValue('unique_slice_credit', $unique_slice_credit);
        LoyaltyModule::setConfigValue('mode', $mode);

        return $this->generateRedirectFromRoute("admin.module.configure", [], ['module_code' => 'Loyalty']);
    }

    public function createAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::CREATE)) {
            return $response;
        }

        $form = new LoyaltyCreateForm($this->getRequest());

        try {

            $loyaltyForm = $this->validateForm($form);

            (new Loyalty())
                ->setMin($loyaltyForm->get('min')->getData())
                ->setMax($loyaltyForm->get('max')->getData())
                ->setAmount($loyaltyForm->get('amount')->getData())
                ->save();


        } catch(\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("loyalty slice creation"),
                $e->getMessage(),
                $form,
                $e
            );
        }

        return $this->generateRedirectFromRoute("admin.module.configure", [], ['module_code' => 'Loyalty']);
    }

    public function deleteAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::DELETE)) {
            return $response;
        }

        $loyaltyId = $this->getRequest()->request->get('slice_id');

        if ($loyaltyId) {
            $loyalty = LoyaltyQuery::create()->findPk($loyaltyId);

            if($loyalty) {
                $loyalty->delete();
            }
        }

        return $this->generateRedirectFromRoute("admin.module.configure", [], ['module_code' => 'Loyalty']);
    }
} 