<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\MerchantOmsDataImport\Business\Step;

use Orm\Zed\Merchant\Persistence\SpyMerchantQuery;
use Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException;
use Spryker\Zed\DataImport\Business\Exception\InvalidDataException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\PublishAwareStep;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use Spryker\Zed\MerchantOmsDataImport\Business\DataSet\MerchantOmsProcessDataSetInterface;

class MerchantWriterStep extends PublishAwareStep implements DataImportStepInterface
{
    /**
     * @var array<string>
     */
    protected const REQUIRED_DATA_SET_KEYS = [
        MerchantOmsProcessDataSetInterface::MERCHANT_REFERENCE,
        MerchantOmsProcessDataSetInterface::FK_STATE_MACHINE_PROCESS,
    ];

    /**
     * @param \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface<mixed> $dataSet
     *
     * @throws \Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException
     *
     * @return void
     */
    public function execute(DataSetInterface $dataSet): void
    {
        $this->validateDataSet($dataSet);

        $merchantReference = $dataSet[MerchantOmsProcessDataSetInterface::MERCHANT_REFERENCE];
        $merchantEntity = $this->createMerchantPropelQuery()
            ->filterByMerchantReference($merchantReference)
            ->findOne();

        if (!$merchantEntity) {
            throw new EntityNotFoundException(sprintf('Could not find merchant by reference "%s"', $merchantReference));
        }

        $merchantEntity->setFkStateMachineProcess($dataSet[MerchantOmsProcessDataSetInterface::FK_STATE_MACHINE_PROCESS]);
        $merchantEntity->save();
    }

    /**
     * @return \Orm\Zed\Merchant\Persistence\SpyMerchantQuery<\Orm\Zed\Merchant\Persistence\SpyMerchant>
     */
    protected function createMerchantPropelQuery(): SpyMerchantQuery
    {
        return SpyMerchantQuery::create();
    }

    /**
     * @param \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface<mixed> $dataSet
     *
     * @throws \Spryker\Zed\DataImport\Business\Exception\InvalidDataException
     *
     * @return void
     */
    protected function validateDataSet(DataSetInterface $dataSet): void
    {
        foreach (static::REQUIRED_DATA_SET_KEYS as $requiredDataSetKey) {
            if (!$dataSet[$requiredDataSetKey]) {
                throw new InvalidDataException(sprintf('"%s" is required.', $requiredDataSetKey));
            }
        }
    }
}
