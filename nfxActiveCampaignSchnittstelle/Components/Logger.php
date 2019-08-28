<?php

namespace nfxActiveCampaignSchnittstelle\Components;

use Shopware\Components\Plugin\ConfigReader;
use Monolog\Logger as BaseLogger;
use Monolog\Handler\RotatingFileHandler;


class Logger extends BaseLogger
{
    /**
     * @var ContainerInterface
     */    
    private $pluginDirectory;
    private $config;
    private $pluginName;
    

    /**
     * 
     * @param type $pluginName
     * @param type $pluginDirectory
     * @param \NfxAutomaticDocumentsGenerator\Components\ConfigReader $configReader
     */
    public function __construct($pluginName, $pluginDirectory, ConfigReader $configReader)
    {
       $this->pluginName = $pluginName;
       $this->pluginDirectory = $pluginDirectory;
       $this->config = $configReader->getByPluginName($pluginName);
       
       $name = $this->getFileName();
       parent::__construct($name);
    }
    
    

    /**
     * Adds a log record.
     *
     * @param int $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     *
     * @return bool Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = array())
    {
        if (!$this->handlers) {
            $this->pushHandler(new RotatingFileHandler($this->getStreamName(), 30, static::DEBUG));
        }        
        /*
        if ($level == self::DEBUG) {
            if (!$this->config->ENABLE_DEBUG)
                return false;
        }
         */
        return parent::addRecord($level, $message, $context);
    }
    

    /**
     * Log error messages into a file
     *
     * @param <type> $ex
     * @param <type> $fct
     * @param type $sendEmail
     */
    public function logError($ex, $fct, $sendEmail = false) {
        $context = array();
        $context["Error Source"] = $fct . " (" . $ex->getFile() . " - " . $ex->getLine() . ")";
        $context["Trace"] =  $ex->getTraceAsString();
        
        $this->addError($ex->getMessage(), $context);
    }
    
    /**
     * the file where the info will be logged
     * @return type
     */
    private function getStreamName(){
        return Shopware()->Container()->getParameter('kernel.logs_dir') . "/" . $this->getFileName() . "_". Shopware()->Container()->getParameter('kernel.environment') . ".log";
    }

    /**
     * get the log file name
     * @return type
     */
    private function getFileName(){
        $name = $this->pluginName;
        $text = "";
        $lastCapital = true;
        for($i = 0; $i < strlen($name); $i++){
            if($name[$i] !== strtolower($name[$i])){
                if(!$lastCapital){
                    $text .= "_";
                }
                $lastCapital = true;
            } else {
                $lastCapital = false;
            }
            $text .= strtolower($name[$i]);
        }
        return str_replace("nfx", "nfx_", $text);
    }
    
}
?>
