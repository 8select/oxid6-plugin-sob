<?php

namespace ASign\EightSelect\Model;

use OxidEsales\Eshop\Application\Controller\ArticleDetailsController;
use OxidEsales\Eshop\Application\Model\SelectList;
use OxidEsales\EshopCommunity\Application\Model\Selection;

/**
 * Class Article
 * @package ASign\EightSelect\Model
 */
class Article extends Article_parent
{
    /** @var array */
    protected $_colorLabels = null;

    /** @var string */
    protected $_virtualMasterSku = null;

    /**
     * Get EightSelect virtual sku
     *
     * @return string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function getEightSelectVirtualSku()
    {
        if ($this->_virtualMasterSku !== null) {
            return $this->_virtualMasterSku;
        }

        $this->_virtualMasterSku = $this->getFieldData('oxartnum');

        $view = $this->getConfig()->getTopActiveView();
        if ($view instanceof ArticleDetailsController) {
            $varSelections = $view->getVariantSelections();

            if ($varSelections && $varSelections['blPerfectFit'] && $varSelections['oActiveVariant']) {
                $variant = $varSelections['oActiveVariant'];
                $this->_virtualMasterSku = $variant->oxarticles__oxartnum->value;
            } elseif (isset($varSelections['selections']) && count($varSelections['selections'])) {
                $colorLabels = $this->getEightSelectColorLabels();

                /** @var SelectList $varSelectList */
                foreach ($varSelections['selections'] as $varSelectList) {
                    if (in_array($varSelectList->getLabel(), $colorLabels) && $varSelectList->getActiveSelection()) {
                        /** @var Selection $selection */
                        $selection = $varSelectList->getActiveSelection();
                        $fieldValue = strtolower($selection->getName());
                        $this->_virtualMasterSku .= '-' . str_replace(' ', '', $fieldValue);
                        break;
                    }
                }
            }
        }

        return $this->_virtualMasterSku;
    }

    /**
     * Get EightSelect color labels
     *
     * @return array|null
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    protected function getEightSelectColorLabels()
    {
        if ($this->_colorLabels === null) {
            $query = "SELECT OXOBJECT FROM eightselect_attribute2oxid WHERE ESATTRIBUTE = 'farbe'";
            $this->_colorLabels = (array) \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getCol($query);
        }

        return $this->_colorLabels;
    }
}
