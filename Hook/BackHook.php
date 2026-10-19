<?php

namespace Loyalty\Hook;

use CreditAccount\Model\CreditAccountQuery;
use Loyalty\Form\LoyaltyCreateForm;
use Loyalty\Loyalty;
use Loyalty\Model\LoyaltyQuery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\CurrencyQuery;

/**
 * Renders the module configuration page (slices, single slice mode and loyalty account balances).
 */
class BackHook extends BaseHook
{
    public function __construct(
        private readonly TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfiguration'],
            ],
        ];
    }

    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $accounts = [];
        foreach (CreditAccountQuery::create()->joinWithCustomer()->find() as $creditAccount) {
            $customer = $creditAccount->getCustomer();
            $accounts[] = [
                'customer_id' => $customer->getId(),
                'customer_name' => trim($customer->getLastname().' '.$customer->getFirstname()),
                'amount' => (float) $creditAccount->getAmount(),
            ];
        }

        $event->add($this->render('loyalty/module-configuration.html.twig', [
            'mode' => Loyalty::getConfigValue('mode', Loyalty::MODE_MULTIPLE_SLICES),
            'unique_slice_amount' => Loyalty::getConfigValue('unique_slice_amount', ''),
            'unique_slice_credit' => Loyalty::getConfigValue('unique_slice_credit', ''),
            'slices' => LoyaltyQuery::create()->orderByMin()->find(),
            'accounts' => $accounts,
            'currency_symbol' => CurrencyQuery::create()->findOneByByDefault(1)?->getSymbol() ?? '€',
            'create_form' => $this->formFactory->createForm(LoyaltyCreateForm::getName())->createView()->getView(),
        ]));
    }
}
