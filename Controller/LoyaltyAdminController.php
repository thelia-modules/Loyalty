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
use Loyalty\Loyalty as LoyaltyModule;
use Loyalty\Model\Loyalty;
use Loyalty\Model\LoyaltyQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;

/**
 * Class LoyaltyAdminController
 * @package Loyalty\Controller
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */
#[Route('/admin/loyalty', name: 'loyalty')]
class LoyaltyAdminController extends BaseAdminController
{
    #[Route('/update', name: '_update', methods: ['POST'])]
    public function updateAction(Request $request): Response
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::UPDATE)) {
            return $response;
        }

        $this->getTokenProvider()->checkToken((string) $request->request->get('_token', ''));

        $loyaltiesMin = $request->request->all('loyalty_min');
        $loyaltiesMax = $request->request->all('loyalty_max');
        $loyaltiesAmount = $request->request->all('loyalty_amount');

        foreach ($loyaltiesMin as $id => $value) {
            if (null === $loyalty = LoyaltyQuery::create()->findPk((int) $id)) {
                continue;
            }

            $loyalty
                ->setMin($this->toFloat($value))
                ->setMax($this->toFloat($loyaltiesMax[$id] ?? null))
                ->setAmount($this->toFloat($loyaltiesAmount[$id] ?? null))
                ->save();
        }

        $mode = $request->request->get('loyalty_mode', LoyaltyModule::MODE_MULTIPLE_SLICES);

        LoyaltyModule::setConfigValue('mode', LoyaltyModule::MODE_SINGLE_SLICE === $mode ? LoyaltyModule::MODE_SINGLE_SLICE : LoyaltyModule::MODE_MULTIPLE_SLICES);
        LoyaltyModule::setConfigValue('unique_slice_amount', $this->toFloat($request->request->get('unique_slice_amount')) ?? 0);
        LoyaltyModule::setConfigValue('unique_slice_credit', $this->toFloat($request->request->get('unique_slice_credit')) ?? 0);

        return $this->redirectToConfiguration($request, 'success', 'Loyalty configuration saved.');
    }

    #[Route('/create', name: '_create', methods: ['POST'])]
    public function createAction(Request $request): Response
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm(LoyaltyCreateForm::getName());

        try {
            $loyaltyForm = $this->validateForm($form, 'POST');

            (new Loyalty())
                ->setMin($this->toFloat($loyaltyForm->get('min')->getData()))
                ->setMax($this->toFloat($loyaltyForm->get('max')->getData()))
                ->setAmount($this->toFloat($loyaltyForm->get('amount')->getData()))
                ->save();
        } catch (FormValidationException $e) {
            return $this->redirectToConfiguration($request, 'danger', $this->createStandardFormValidationErrorMessage($e), false);
        } catch (\Exception $e) {
            return $this->redirectToConfiguration($request, 'danger', $e->getMessage(), false);
        }

        return $this->redirectToConfiguration($request, 'success', 'Loyalty slice created.');
    }

    #[Route('/delete', name: '_delete', methods: ['POST'])]
    public function deleteAction(Request $request): Response
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['Loyalty'], AccessManager::DELETE)) {
            return $response;
        }

        $this->getTokenProvider()->checkToken((string) $request->request->get('_token', ''));

        LoyaltyQuery::create()->findPk((int) $request->request->get('slice_id', 0))?->delete();

        return $this->redirectToConfiguration($request, 'success', 'Loyalty slice deleted.');
    }

    private function redirectToConfiguration(Request $request, string $type, string $message, bool $translate = true): Response
    {
        $request->getSession()->getFlashBag()->add(
            $type,
            $translate ? Translator::getInstance()->trans($message, [], LoyaltyModule::MODULE_DOMAIN) : $message
        );

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/Loyalty'));
    }

    /**
     * Accepts "12,5" as well as "12.5"; an empty value becomes null.
     */
    private function toFloat(mixed $value): ?float
    {
        if (null === $value || '' === trim((string) $value)) {
            return null;
        }

        return (float) str_replace(',', '.', (string) $value);
    }
}
