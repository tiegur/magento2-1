<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Centinel
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Centinel\Helper\Data
 */
namespace Magento\Centinel\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInfoBlock()
    {
        /** @var $block \Magento\Payment\Helper\Data */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Payment\Helper\Data');
        /** @var $paymentInfo \Magento\Payment\Model\Info */
        $paymentInfo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Payment\Model\Info');
        $paymentInfo->setMethod('checkmo');
        $result = $block->getInfoBlock($paymentInfo);
        $this->assertInstanceOf('Magento\OfflinePayments\Block\Info\Checkmo', $result);
    }
}
