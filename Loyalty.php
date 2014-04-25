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
use Thelia\Core\Translation\Translator;
use Thelia\Install\Database;
use Thelia\Model\ModuleQuery;
use Thelia\Module\BaseModule;

class Loyalty extends BaseModule
{
    /*
     * You may now override BaseModuleInterface methods, such as:
     * install, destroy, preActivation, postActivation, preDeactivation, postDeactivation
     *
     * Have fun !
     */

    /**
     *
     * return false if CreditAccount module is not present
     *
     * @param ConnectionInterface $con
     * @return bool|void
     */
    public function preActivation(ConnectionInterface $con = null)
    {
        $return = true;
        $module = ModuleQuery::create()
            ->filterByCode('CreditAccount')
            ->filterByActivate(self::IS_ACTIVATED)
            ->findOne();

        if (null === $module) {
            throw new \RuntimeException(Translator::getInstance()->trans('CreditAccount must be installed and activated', [], 'loyalty'));
        }
    }


    public function postActivation(ConnectionInterface $con = null)
    {
        $database = new Database($con->getWrappedConnection());
        $database->insertSql(null, [__DIR__ . "/Config/thelia.sql"]);
    }
}
