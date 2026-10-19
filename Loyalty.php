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

namespace Loyalty;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Thelia\Core\Install\Database;
use Thelia\Core\Translation\Translator;
use Thelia\Model\ModuleQuery;
use Thelia\Module\BaseModule;

class Loyalty extends BaseModule
{
    public const MODULE_DOMAIN = 'loyalty';

    public const MODE_MULTIPLE_SLICES = 'multiple';
    public const MODE_SINGLE_SLICE = 'unique';

    /**
     * The CreditAccount module must be activated: loyalty credits are stored in its accounts.
     */
    public function preActivation(?ConnectionInterface $con = null): bool
    {
        $module = ModuleQuery::create()
            ->filterByCode('CreditAccount')
            ->filterByActivate(self::IS_ACTIVATED)
            ->findOne();

        if (null === $module) {
            throw new \RuntimeException(Translator::getInstance()->trans('CreditAccount must be installed and activated', [], self::MODULE_DOMAIN));
        }

        return true;
    }

    /**
     * Creates the table only when it does not exist yet, and initializes the configuration only
     * once: (re)activating the module must never wipe the configured slices.
     */
    public function postActivation(?ConnectionInterface $con = null): void
    {
        $database = new Database($con);
        $database->insertSql(null, [__DIR__.'/Config/insert.sql']);

        if (null === self::getConfigValue('mode')) {
            self::setConfigValue('mode', self::MODE_MULTIPLE_SLICES);
            self::setConfigValue('unique_slice_amount', 0);
            self::setConfigValue('unique_slice_credit', 0);
        }
    }

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([__DIR__.'/I18n/*'])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
