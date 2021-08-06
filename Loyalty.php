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
use Thelia\Core\Translation\Translator;
use Thelia\Install\Database;
use Thelia\Model\ModuleQuery;
use Thelia\Module\BaseModule;

class Loyalty extends BaseModule
{
    const MODULE_DOMAIN = 'loyalty';

    const MODE_MULTIPLE_SLICES = 'multiple';
    const MODE_SINGLE_SLICE = 'unique';

    /**
     *
     * return false if CreditAccount module is not present
     *
     * @param ConnectionInterface $con
     * @return bool|void
     */
    public function preActivation(ConnectionInterface $con = null)
    {

        $module = ModuleQuery::create()
            ->filterByCode('CreditAccount')
            ->filterByActivate(self::IS_ACTIVATED)
            ->findOne();

        if (null === $module) {
            throw new \RuntimeException(Translator::getInstance()->trans('CreditAccount must be installed and activated', [], 'loyalty'));
        }
        
        return true;
    }


    public function postActivation(ConnectionInterface $con = null): void
    {
        $database = new Database($con->getWrappedConnection());
        $database->insertSql(null, [__DIR__ . "/Config/thelia.sql"]);

        self::setConfigValue('mode', self::MODE_MULTIPLE_SLICES);

        self::setConfigValue('unique_slice_amount', 0);
        self::setConfigValue('unique_slice_credit', 0);
    }

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
