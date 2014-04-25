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

        $this->redirectToRoute("admin.module.configure", [], ['module_code' => 'Loyalty']);
    }

    public function createAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::CREATE)) {
            return $response;
        }

        $form = new LoyaltyCreateForm($this->getRequest());

        try {

            $loyaltyForm = $this->validateForm($form);

            $loyalty = (new Loyalty())
                ->setMin($loyaltyForm->get('min')->getData())
                ->setMax($loyaltyForm->get('max')->getData())
                ->setAmount($loyaltyForm->get('amount')->getData())
                ->save();

            $this->redirectToRoute("admin.module.configure", [], ['module_code' => 'Loyalty']);

        } catch(\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("loyalty slice creation"),
                $e->getMessage(),
                $form,
                $e
            );

            return $this->render(
                "module-configure",
                array(
                    "module_code" => "Loyalty",
                )
            );
        }
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

        $this->redirectToRoute("admin.module.configure", [], ['module_code' => 'Loyalty']);
    }
} 