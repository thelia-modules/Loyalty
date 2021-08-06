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

namespace Loyalty\Form;

use Loyalty\Loyalty;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

/**
 * Class LoyaltyCreateForm
 * @package Loyalty\Form
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */
class LoyaltyCreateForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add('min', NumberType::class, [
                'constraints' => [
                    new NotBlank()
                ],
                'label' => Translator::getInstance()->trans("From cart amount", [], Loyalty::MODULE_DOMAIN)
            ])
            ->add('max', NumberType::class, [
                'constraints' => [
                    new NotBlank()
                ],
                'label' => Translator::getInstance()->trans("To cart amount", [], Loyalty::MODULE_DOMAIN)
            ])
            ->add('amount', NumberType::class, [
                'constraints' => [
                    new NotBlank()
                ],
                'label' => Translator::getInstance()->trans("Amount added to loyalty account", [], Loyalty::MODULE_DOMAIN)
            ])
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public static function getName()
    {
        return 'loyalty_create';
    }
}
