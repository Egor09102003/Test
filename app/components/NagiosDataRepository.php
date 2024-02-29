<?php

namespace app\components;

/***************************************************************
 *  Copyright notice
 *
 *  2016 Anton Danilov <anton@i-tribe.de>, interactive tribe GmbH
 *
 *  All rights reserved
 *
 ***************************************************************/

use yii\base\Component;
use yii\helpers\FileHelper;

/**
 * Class NagiosDataRepository
 * @package app\components
 */
class NagiosDataRepository extends Component
{

    /**
     * Sites log data directory path
     *
     * @var string
     */
    public $filesPath;

    /**
     * Signature file path
     *
     * @var string
     */
    public $signature;

    /**
     * @return array
     * TODO: Implement getData() method.
     */
    public function getData()
    {
        $filesPath = __DIR__ . '/../data/sites/';
        $signature = __DIR__ . '/../data/signature.txt';
        $configLines = file($signature, FILE_IGNORE_NEW_LINES);
        $config = $this->configParser($configLines);
        $dirs = scandir($filesPath);
        $result = [];
        foreach ($dirs as $dir) {
            $filesDir = $filesPath . $dir . '/';
            if ($dir != '.' && $dir != '..' && is_dir($filesDir)) {
                $files = scandir($filesDir);
                $fileDir = $filesDir . end($files);
                // проверка файла на пустоту
                while (!file_get_contents($fileDir) && count($files) != 0) {
                    array_pop($files);
                    $fileDir = $filesDir . end($files);
                }
                //сохранение строк файла
                $lines = file($fileDir, FILE_IGNORE_NEW_LINES);
                $siteInfo = $this->dataParser($lines);
                // проверка актуальности расширений
                $siteInfo['extension'] = true;
                foreach ($siteInfo['extensions'] as $ext => $version) {
                    if (array_key_exists($ext, $config) && $this->versionCheck($version, $config[$ext])) {
                        $siteInfo['extension'] = false;
                        break;
                    }
                }
                // проверка актуальности версии TYPO3
                $siteInfo['typo3_support'] = 3;
                if ($this->versionCheck($siteInfo['TYPO3'], $config['TYPO3']['critical'])) {
                    $siteInfo['typo3_support'] = 2;
                }
                if ($this->versionCheck($siteInfo['TYPO3'], $config['TYPO3']['warning'])) {
                    $siteInfo['typo3_support'] = 1;
                }
                $siteInfo['last_update'] = date("d.m.Y H:i", filemtime($fileDir));
                // добавление в результирующий массив информации о текущем сайте
                $result[] = $siteInfo;
            }
        }
        return $result;
    }

    // парсинг данных сайтов
    public function dataParser($lines): array
    {
        $result = [];
        $result['extensions'] = [];
        foreach ($lines as $line) {
            if (preg_match('/^(TYPO3|PHP):version-/', $line)) {
                $key = explode(':', $line)[0];
                $result[$key] = explode('-', $line)[1];
            } else if (str_starts_with($line, 'EXT:')) {
                $key = substr($line, 4, strpos($line, '-') - 4);
                $value = substr($line, strrpos($line, '-') + 1, strlen($line));
                $result['extensions'][$key] = $value;
            } else if (str_starts_with($line, 'EXTDEPTYPO3:')){
                $key = substr($line, 12, strpos($line, '-') - 12);
                $value = substr($line, strpos($line, '-') + 1,
                    strrpos($line, '-') - strpos($line, '-') - 1);
                $result['extensions'][$key] = $value;
            } else if (str_starts_with($line, 'SITENAME')) {
                $result['name'] = str_replace('SITENAME:', '', $line);
            }
        }
        return $result;
    }

    // парсинг файла с актуальными версиями
    public function configParser($lines): array
    {
        $result['TYPO3']['critical'] = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, 'typo3-version.warning')) {
                $result['TYPO3']['warning'][] = substr($line, 24, strlen($line) - 24);
            }else if (str_starts_with($line, 'typo3-version.critical')) {
                $result['TYPO3']['critical'] = array_merge($result['TYPO3']['critical'],
                    explode(',', trim(substr($line, 24, strlen($line) - 24))));
            } else if ((str_starts_with($line, 'extension.'))) {
                $line = str_replace('extension.', '', $line);
                $key = substr($line, 0, strpos($line, '.'));
                $result[$key] = [];
                $result[$key] = array_merge($result[$key],
                    explode(',',
                        substr($line, strlen($key) + 12, strlen($line) - strlen($key) - 12)));
            }

        }
        return $result;
    }

    // проверка актуальности текущей версии
    public function versionCheck($currentVersion, $versions): bool
    {
        foreach ($versions as $version) {
            if ($version == $currentVersion) {
                return true;
            } else {
                if (str_contains($version, 'x')) {
                    $flag = true;
                    for ($i = 0; $i < strpos($version, 'x'); $i++) {
                        if ($i < strlen($currentVersion) &&
                            $version[$i] != $currentVersion[$i]) {
                            $flag = false;
                            break;
                        }
                    }
                    if ($flag) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

}