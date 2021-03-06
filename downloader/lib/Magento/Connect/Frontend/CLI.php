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
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Connect\Frontend;

/**
 * CLI Frontend implementation
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class CLI extends \Magento\Connect\Frontend
{

    /**
     * Collected output
     *
     * @var array
     */
    protected $_output = array();

    /**
     * Output error
     *
     * @param string $command
     * @param string $message
     * @return void
     */
    public function doError($command, $message)
    {
        parent::doError($command, $message);
        $this->writeln("Error: ");
        $this->writeln("$command: $message");
    }

    /**
     * Output config help
     *
     * @param array $data
     * @return void
     */
    public function outputConfigHelp($data)
    {
        foreach($data['data'] as $k=>$v) {
            if(is_scalar($v)) {
                $this->writeln($v);
            } elseif(is_array($v)) {
                $this->writeln(implode(": ", $v));
            }
        }
    }

    /**
     * Output info
     * @param array $data
     * @return void
     */
    public function outputRemoteInfo($data)
    {
        if(!is_array($data['releases'])) {
            return;
        }
        foreach ($data['releases'] as $r) {
            $this->writeln(implode(" ", $r));
        }
    }

    /**
     * @param string $type
     * @return string
     */
    public function detectMethodByType($type)
    {
        $defaultMethod = "output";
        $methodMap = array(
            'list-upgrades'=> 'outputUpgrades',
            'list-available' => 'outputChannelsPackages',
            'list-installed' => 'writeInstalledList',
            'package-dependencies' => 'outputPackageDeps',
            'package-prepare' => 'outputPackagePrepare',
            'list-files' => 'outputPackageContents',
            'config-help' => 'outputConfigHelp',
            'info' => 'outputRemoteInfo',
            'config-show' => 'outputConfig',
            'install' => 'outputInstallResult',
            'install-file' => 'outputInstallResult',
            'upgrade' => 'outputInstallResult',
            'upgrade-all' => 'outputInstallResult',
            'uninstall' => 'outputDeleted',
            'list-channels' => 'outputListChannels',
        );
        if(isset($methodMap[$type])) {
            return $methodMap[$type];
        }
        return $defaultMethod;
    }

    /**
     * @param array $data
     * @return void
     */
    public function outputDeleted($data)
    {
        if(!count($data['data'])) {
            return;
        }
        $this->writeln($data['title']);
        foreach($data['data'] as $row) {
            $this->writeln("$row[0]/$row[1]");
        }
    }

    /**
     * @param array $data
     * @return void
     */
    public function outputListChannels($data)
    {
        $this->writeln($data['title']);

        $channels =& $data['data'][\Magento\Connect\Singleconfig::K_CHAN];
        foreach($channels as $name => $v) {
            $this->writeln("$name: {$v[\Magento\Connect\Singleconfig::K_URI]}");
        }
        $aliases =& $data['data'][\Magento\Connect\Singleconfig::K_CHAN_ALIAS];
        if(count($aliases)) {
            $this->writeln();
            $this->writeln($data['title_aliases']);
            foreach($aliases as $k=>$v) {
                $this->writeln("$k => $v");
            }
        }

    }

    /**
     * Output install result
     * @param array $data
     * @return void
     */
    public function outputInstallResult($data)
    {
        if(isset($data['title'])) {
            $title = trim($data['title'])." ";
        } else {
            $title = '';
        }
        foreach($data['assoc'] as $row) {
            $this->printf("%s%s/%s %s\n", $title, $row['channel'], $row['name'], $row['version']);
        }
    }

    /**
     * Ouptut package contents
     * @param array $data
     * @return void
     */
    public function outputPackageContents($data)
    {
        $this->writeln($data['title']);
        foreach($data['data'] as $file) {
            $this->writeln($file);
        }
    }

    /**
     * Output package dependencies
     *
     * @param array $data
     * @return void
     */
    public function outputPackageDeps($data)
    {
        $title = $data['title'];
        $this->writeln($title);
        foreach($data['data'] as $package) {
            $this->printf("%-20s %-20s %-20s %-20s\n", $package['channel'], $package['name'], $package['min'], $package['max']);
        }
    }

    /**
     * Output package prepare
     *
     * @param array $data
     * @return void
     */
    public function outputPackagePrepare($data)
    {
        $title = $data['title'];
        $this->writeln($title);
        foreach($data['data'] as $package) {
            $this->printf("%-20s %-20s %-20s %-20s\n", $package['channel'], $package['name'], $package['version'], $package['install_state']);
        }
    }

    /**
     * Ouptut channel packages
     *
     * @param array $data
     * @return void
     */
    public function outputChannelsPackages($data)
    {
        foreach($data['data'] as $channelInfo) {
            $title =& $channelInfo['title'];
            $packages =& $channelInfo['packages'];
            $this->writeln($title);
            foreach($packages as $name=>$package) {
                $releases =& $package['releases'];
                $tmp = array();
                foreach($releases as $ver=>$state) {
                    $tmp[] = "$ver $state";
                }
                $tmp = implode(',',$tmp);
                $this->writeln($name.": ".$tmp);
            }
        }
    }

    /**
     * Make output
     *
     * @param array $data
     * @return void
     */
    public function output($data)
    {
        $capture = $this->isCapture();
        if($capture) {
            $this->_output[] = $data;
            return;
        }

        if(is_array($data)) {
            foreach($data as $type=>$params) {
                $method = $this->detectMethodByType($type);
                if($method) {
                    $this->$method($params);
                } else {
                    $this->writeln(__METHOD__." handler not found for {$type}");
                }
            }
        } else {
            $this->writeln($data);
        }
    }

    /**
     * Detailed package info
     * @param \Magento\Connect\Package $package
     * @return void
     */
    public function outputPackage($package)
    {
        $fields = array(
            'Name'=>'name',
            'Version'=>'version',
            'Stability'=>'stability',
            'Description' => 'description',
            'Date' => 'date',
            'Authors' => 'authors',
        );

        foreach($fields as $title => $fld) {
            $method = "get".ucfirst($fld);
            $data =  $package->$method();
            if(empty($data)) {
                continue;
            }
            $this->write($title.": ");
            if(is_array($data)) {
                $this->write(print_r($data,true));
            } else {
                $this->write($data);
            }
            $this->writeln('');
        }
    }


    /**
     * Write channels list
     * @param array $data
     * @return void
     */
    public function writeChannelsList($data)
    {
        $this->writeln("Channels available: ");
        $this->writeln("===================");
        $out = $data['byName'];
        ksort($out);
        foreach($out as $k=>$v)   {
            $this->printf ("%-20s %-20s\n", $k, $v);
        }
    }

    /**
     * Write installed list
     * @param array $data
     * @return void
     */
    public function writeInstalledList($data)
    {
        $totalCount = 0;
        foreach($data['data'] as $channel=>$packages) {
            $title = sprintf($data['channel-title'], $channel);
            $c = count($packages);
            $totalCount += $c;
            if(!$c) {
                continue;
            }
            $this->writeln($title);
            foreach($packages as $name=>$row) {
                $this->printf("%-20s %-20s\n", $name, $row['version']." ".$row['stability']);
            }
        }
        if($totalCount === 0) {
            $this->writeln("No installed packages");
        }
    }

    /**
     * Output commands list
     * @param array $data
     * @return void
     */
    public function outputCommandList($data)
    {
        $this->writeln("Connect commands available:");
        $this->writeln("===========================");
        foreach ($data as $k=>$v) {
            $this->printf ("%-20s %-20s\n", $k, $v['summary']);
        }
    }

    /**
     * Output config
     * @param array $data
     * @return void
     */
    public function outputConfig($data)
    {
        foreach($data['data'] as $name=>$row) {
            $value = $row['value'] === '' ? "<not set>" : strval($row['value']);
            $this->printf("%-30s %-20s %-20s\n",  $row['prompt'], $name, $value);
        }
    }

    /**
     * Output config variable
     * @param string $key
     * @param string $value
     * @return void
     */
    public function outputConfigVariable($key, $value)
    {
        if($value === '') {
            $value = '<not set>';
        }
        $this->writeln("Config variable '{$key}': {$value}");
    }

    /**
     * Write data and "\n" afterwards
     * @param string $data
     * @return void
     */
    public function writeln($data = '')
    {
        $this->write($data."\n");
    }


    /**
     * Get output, clear if needed
     *
     * @param bool $clearPrevious optional, true by default
     * @return array
     */
    public function getOutput($clearPrevious = true)
    {
        $out = $this->_output;
        if($clearPrevious) {
            $this->_output = array();
        }
        return $out;
    }

    /**
     * Write data to console
     * @param string $data
     * @return void
     */
    public function write($data)
    {
        if($this->isSilent()) {
            return;
        }
        echo $data;
    }

    /**
     * Output printf-stlye formatted string and args
     * @return void
     */
    public function printf()
    {
        $args = func_get_args();
        $this->write(call_user_func_array('sprintf', $args));
    }

    /**
     * Readline from console
     * @return string
     */
    public function readln()
    {
        $out = "";
        $key = fgetc(STDIN);
        while ($key!="\n") {
            $out.= $key;
            $key = fread(STDIN, 1);
        }
        return $out;
    }

    /**
     * Output upgrades
     * @param array $data
     * @return void
     */
    public function outputUpgrades($data)
    {
        foreach($data['data'] as $chan => $packages) {
            $this->writeln("Updates for ".$chan.": ");
            foreach($packages as $name => $data) {
                $this->writeln("  $name: {$data['from']} => {$data['to']}");
            }
        }
    }

}

