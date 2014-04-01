<?php

namespace Loyalty\Model\Base;

use \Exception;
use \PDO;
use Loyalty\Model\Loyalty as ChildLoyalty;
use Loyalty\Model\LoyaltyQuery as ChildLoyaltyQuery;
use Loyalty\Model\Map\LoyaltyTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'loyalty' table.
 *
 *
 *
 * @method     ChildLoyaltyQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildLoyaltyQuery orderByMin($order = Criteria::ASC) Order by the min column
 * @method     ChildLoyaltyQuery orderByMax($order = Criteria::ASC) Order by the max column
 * @method     ChildLoyaltyQuery orderByAmount($order = Criteria::ASC) Order by the amount column
 *
 * @method     ChildLoyaltyQuery groupById() Group by the id column
 * @method     ChildLoyaltyQuery groupByMin() Group by the min column
 * @method     ChildLoyaltyQuery groupByMax() Group by the max column
 * @method     ChildLoyaltyQuery groupByAmount() Group by the amount column
 *
 * @method     ChildLoyaltyQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildLoyaltyQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildLoyaltyQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildLoyalty findOne(ConnectionInterface $con = null) Return the first ChildLoyalty matching the query
 * @method     ChildLoyalty findOneOrCreate(ConnectionInterface $con = null) Return the first ChildLoyalty matching the query, or a new ChildLoyalty object populated from the query conditions when no match is found
 *
 * @method     ChildLoyalty findOneById(int $id) Return the first ChildLoyalty filtered by the id column
 * @method     ChildLoyalty findOneByMin(double $min) Return the first ChildLoyalty filtered by the min column
 * @method     ChildLoyalty findOneByMax(double $max) Return the first ChildLoyalty filtered by the max column
 * @method     ChildLoyalty findOneByAmount(double $amount) Return the first ChildLoyalty filtered by the amount column
 *
 * @method     array findById(int $id) Return ChildLoyalty objects filtered by the id column
 * @method     array findByMin(double $min) Return ChildLoyalty objects filtered by the min column
 * @method     array findByMax(double $max) Return ChildLoyalty objects filtered by the max column
 * @method     array findByAmount(double $amount) Return ChildLoyalty objects filtered by the amount column
 *
 */
abstract class LoyaltyQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \Loyalty\Model\Base\LoyaltyQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\Loyalty\\Model\\Loyalty', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildLoyaltyQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildLoyaltyQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \Loyalty\Model\LoyaltyQuery) {
            return $criteria;
        }
        $query = new \Loyalty\Model\LoyaltyQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildLoyalty|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = LoyaltyTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(LoyaltyTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return   ChildLoyalty A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, MIN, MAX, AMOUNT FROM loyalty WHERE ID = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildLoyalty();
            $obj->hydrate($row);
            LoyaltyTableMap::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildLoyalty|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return ChildLoyaltyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(LoyaltyTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildLoyaltyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(LoyaltyTableMap::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildLoyaltyQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(LoyaltyTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(LoyaltyTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LoyaltyTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the min column
     *
     * Example usage:
     * <code>
     * $query->filterByMin(1234); // WHERE min = 1234
     * $query->filterByMin(array(12, 34)); // WHERE min IN (12, 34)
     * $query->filterByMin(array('min' => 12)); // WHERE min > 12
     * </code>
     *
     * @param     mixed $min The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildLoyaltyQuery The current query, for fluid interface
     */
    public function filterByMin($min = null, $comparison = null)
    {
        if (is_array($min)) {
            $useMinMax = false;
            if (isset($min['min'])) {
                $this->addUsingAlias(LoyaltyTableMap::MIN, $min['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($min['max'])) {
                $this->addUsingAlias(LoyaltyTableMap::MIN, $min['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LoyaltyTableMap::MIN, $min, $comparison);
    }

    /**
     * Filter the query on the max column
     *
     * Example usage:
     * <code>
     * $query->filterByMax(1234); // WHERE max = 1234
     * $query->filterByMax(array(12, 34)); // WHERE max IN (12, 34)
     * $query->filterByMax(array('min' => 12)); // WHERE max > 12
     * </code>
     *
     * @param     mixed $max The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildLoyaltyQuery The current query, for fluid interface
     */
    public function filterByMax($max = null, $comparison = null)
    {
        if (is_array($max)) {
            $useMinMax = false;
            if (isset($max['min'])) {
                $this->addUsingAlias(LoyaltyTableMap::MAX, $max['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($max['max'])) {
                $this->addUsingAlias(LoyaltyTableMap::MAX, $max['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LoyaltyTableMap::MAX, $max, $comparison);
    }

    /**
     * Filter the query on the amount column
     *
     * Example usage:
     * <code>
     * $query->filterByAmount(1234); // WHERE amount = 1234
     * $query->filterByAmount(array(12, 34)); // WHERE amount IN (12, 34)
     * $query->filterByAmount(array('min' => 12)); // WHERE amount > 12
     * </code>
     *
     * @param     mixed $amount The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildLoyaltyQuery The current query, for fluid interface
     */
    public function filterByAmount($amount = null, $comparison = null)
    {
        if (is_array($amount)) {
            $useMinMax = false;
            if (isset($amount['min'])) {
                $this->addUsingAlias(LoyaltyTableMap::AMOUNT, $amount['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($amount['max'])) {
                $this->addUsingAlias(LoyaltyTableMap::AMOUNT, $amount['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(LoyaltyTableMap::AMOUNT, $amount, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildLoyalty $loyalty Object to remove from the list of results
     *
     * @return ChildLoyaltyQuery The current query, for fluid interface
     */
    public function prune($loyalty = null)
    {
        if ($loyalty) {
            $this->addUsingAlias(LoyaltyTableMap::ID, $loyalty->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the loyalty table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(LoyaltyTableMap::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            LoyaltyTableMap::clearInstancePool();
            LoyaltyTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildLoyalty or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildLoyalty object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public function delete(ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(LoyaltyTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(LoyaltyTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        LoyaltyTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            LoyaltyTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // LoyaltyQuery
