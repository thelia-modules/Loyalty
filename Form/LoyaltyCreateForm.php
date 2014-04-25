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

    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * $this->formBuilder->add("name", "text")
     *   ->add("email", "email", array(
     *           "attr" => array(
     *               "class" => "field"
     *           ),
     *           "label" => "email",
     *           "constraints" => array(
     *               new \Symfony\Component\Validator\Constraints\NotBlank()
     *           )
     *       )
     *   )
     *   ->add('age', 'integer');
     *
     * @return null
     */
    protected function buildForm()
    {
        $this->formBuilder
            ->add('min', 'number', [
                'constraints' => [
                    new NotBlank()
                ],
                'label' => Translator::getInstance()->trans("minimum"),
                'label_attr' => [
                    'for' => 'loyalty_min'
                ]
            ])
            ->add('max', 'number', [
                'constraints' => [
                    new NotBlank()
                ],
                'label' => Translator::getInstance()->trans("maximum"),
                'label_attr' => [
                    'for' => 'loyalty_max'
                ]
            ])
            ->add('amount', 'number', [
                'constraints' => [
                    new NotBlank()
                ],
                'label' => Translator::getInstance()->trans("amount"),
                'label_attr' => [
                    'for' => 'loyalty_amount'
                ]
            ])
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return 'loyalty_create';
    }
}