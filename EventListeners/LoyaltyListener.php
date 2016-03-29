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
    protected function computeAmount($cartTotal)
    {
        $amount = false;

        $mode = Loyalty::getConfigValue('mode', Loyalty::MODE_MULTIPLE_SLICES);
echo "m=$mode";
        if ($mode == Loyalty::MODE_MULTIPLE_SLICES) {
            $loyaltySlice = LoyaltyQuery::create()
                ->filterByMin($cartTotal, Criteria::LESS_EQUAL)
                ->filterByMax($cartTotal, Criteria::GREATER_EQUAL)
                ->findOne()
            ;

            if ($loyaltySlice) {
                $amount = $loyaltySlice->getAmount();
            }
        } elseif ($mode == Loyalty::MODE_SINGLE_SLICE) {
            if (0 < $sliceValue = Loyalty::getConfigValue('unique_slice_amount', 0)) {
                $sliceCount = floor($cartTotal / $sliceValue);
                
                $amount = $sliceCount * Loyalty::getConfigValue('unique_slice_credit', 0);
            }
        }

        return $amount;
    }
    
    public function updateCreditAccount(OrderEvent $event)
    {
        $order = $event->getOrder();

        if ($order->isPaid(true)) {
            $amount = $this->computeAmount($order->getTotalAmount($tax, false));

            if ($amount > 0) {
                $creditEvent = new CreditAccountEvent($order->getCustomer(), $amount, $order->getId());

                $event->getDispatcher()->dispatch(CreditAccount::CREDIT_ACCOUNT_ADD_AMOUNT, $creditEvent);
            }
        } else {
            // Remove any credited amount
            $entryList = CreditAmountHistoryQuery::create()->findByOrderId($order->getId());

            /** @var CreditAmountHistory $entry */
            foreach ($entryList as $entry) {
                $creditEvent = new CreditAccountEvent($order->getCustomer(), - $entry->getAmount(), $order->getId());

                $event->getDispatcher()->dispatch(CreditAccount::CREDIT_ACCOUNT_ADD_AMOUNT, $creditEvent);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::ORDER_UPDATE_STATUS => ['updateCreditAccount', 120]
        ];
    }
}
