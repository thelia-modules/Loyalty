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

namespace Loyalty\EventListeners;

use CreditAccount\CreditAccount;
use CreditAccount\Event\CreditAccountEvent;
use CreditAccount\Model\CreditAmountHistory;
use CreditAccount\Model\CreditAmountHistoryQuery;
use Loyalty\Loyalty;
use Loyalty\Model\LoyaltyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;

/**
 * Class LoyaltyListener
 * @package Loyalty\EventListeners
 * @author Manuel Raynaud <mraynaud@openstudio.fr>, Franck Allimant <thelia@cqfdev.fr>
 */
class LoyaltyListener implements EventSubscriberInterface
{
    protected EventDispatcherInterface $dispatcher;

    /**
     * LoyaltyListener constructor.
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }


    protected function computeAmount(float $cartTotal): float
    {
        $amount = 0.0;

        $mode = Loyalty::getConfigValue('mode', Loyalty::MODE_MULTIPLE_SLICES);

        if ($mode == Loyalty::MODE_MULTIPLE_SLICES) {
            $loyaltySlice = LoyaltyQuery::create()
                ->filterByMin($cartTotal, Criteria::LESS_EQUAL)
                ->filterByMax($cartTotal, Criteria::GREATER_EQUAL)
                ->findOne()
            ;

            if ($loyaltySlice) {
                $amount = (float) $loyaltySlice->getAmount();
            }
        } elseif ($mode == Loyalty::MODE_SINGLE_SLICE) {
            if (0 < $sliceValue = (float) Loyalty::getConfigValue('unique_slice_amount', 0)) {
                $sliceCount = floor($cartTotal / $sliceValue);
                
                $amount = $sliceCount * (float) Loyalty::getConfigValue('unique_slice_credit', 0);
            }
        }

        return $amount;
    }
    
    public function updateCreditAccount(OrderEvent $event): void
    {
        $order = $event->getOrder();

        if ($order->isPaid(true)) {
            // Cart total without shipping costs (the tax amount is returned by reference).
            $tax = 0;
            $amount = $this->computeAmount($order->getTotalAmount($tax, false));

            if ($amount > 0) {
                $creditEvent = new CreditAccountEvent($order->getCustomer(), $amount, $order->getId());

                $this->dispatcher->dispatch($creditEvent, CreditAccount::CREDIT_ACCOUNT_ADD_AMOUNT);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::ORDER_UPDATE_STATUS => ['updateCreditAccount', 120]
        ];
    }
}
