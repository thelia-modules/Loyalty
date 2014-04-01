<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
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