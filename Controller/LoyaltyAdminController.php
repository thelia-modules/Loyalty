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
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Loyalty\Loyalty as LoyaltyModule;
use Thelia\Core\Translation\Translator;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/loyalty", name="loyalty")
 * Class LoyaltyAdminController
 * @package Loyalty\Controller
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */
class LoyaltyAdminController extends BaseAdminController
{
    /**
     * @Route("/update", name="_update", methods="POST")
     */
    public function updateAction(RequestStack $requestStack)
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::UPDATE)) {
            return $response;
        }
        $request = $requestStack->getCurrentRequest()->request;
        
        $loyaltiesMin = $request->all('loyalty_min');
        $loyaltiesMax = $request->all('loyalty_max');
        $loyaltiesAmount = $request->all('loyalty_amount');

        foreach ($loyaltiesMin as $id => $value) {
            $loyalty = LoyaltyQuery::create()->findPk($id);

            $loyalty
                ->setMin($value)
                ->setMax($loyaltiesMax[$id])
                ->setAmount($loyaltiesAmount[$id])
                ->save();
        }
    
        $mode = $request->get('loyalty_mode', 'multiple');
        $unique_slice_amount = $request->get('unique_slice_amount', '');
        $unique_slice_credit = $request->get('unique_slice_credit', '');

        LoyaltyModule::setConfigValue('unique_slice_amount', $unique_slice_amount);
        LoyaltyModule::setConfigValue('unique_slice_credit', $unique_slice_credit);
        LoyaltyModule::setConfigValue('mode', $mode);

        return $this->generateRedirectFromRoute("admin.module.configure", [], ['module_code' => 'Loyalty']);
    }

    /**
     * @Route("/create", name="_create", methods="POST")
     */
    public function createAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm(LoyaltyCreateForm::getName());

        try {

            $loyaltyForm = $this->validateForm($form);

            (new Loyalty())
                ->setMin($loyaltyForm->get('min')->getData())
                ->setMax($loyaltyForm->get('max')->getData())
                ->setAmount($loyaltyForm->get('amount')->getData())
                ->save();


        } catch(\Exception $e) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans("loyalty slice creation"),
                $e->getMessage(),
                $form,
                $e
            );
        }

        return $this->generateRedirectFromRoute("admin.module.configure", [], ['module_code' => 'Loyalty']);
    }

    /**
     * @Route("/delete", name="_delete", methods="POST")
     */
    public function deleteAction(RequestStack $requestStack)
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::DELETE)) {
            return $response;
        }

        $loyaltyId = $requestStack->getCurrentRequest()->request->get('slice_id');

        if ($loyaltyId) {
            $loyalty = LoyaltyQuery::create()->findPk($loyaltyId);

            if($loyalty) {
                $loyalty->delete();
            }
        }

        return $this->generateRedirectFromRoute("admin.module.configure", [], ['module_code' => 'Loyalty']);
    }
} 