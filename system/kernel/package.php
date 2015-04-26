<?php

/**
 * =========================================================================
 *  FILE PACKER AND PACKAGE EXTRACTOR
 * =========================================================================
 *
 * -- CODE: ----------------------------------------------------------------
 *
 *    Package::take(array(
 *        'file-1.txt' => 'file-1.txt',
 *        'file-2.txt' => 'file-2.txt'
 *    ))->pack('file.zip');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/directory')->pack();
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/file.txt')->pack();
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/directory')->pack('package.zip');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/file.txt')->pack('package.zip');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/directory')->pack('foo/bar/package.zip');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/file.txt')->pack('foo/bar/package.zip');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/directory')->pack('file.zip', 'folder');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/file.txt')->pack('file.zip', 'folder');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/directory')->pack('file.zip', 'folder/again');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/file.txt')->pack('file.zip', 'folder/again');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/directory')->pack(null, true);
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('path/to/file.txt')->pack(null, true);
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->extract();
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->extract('folder');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->extractTo('path/to/directory');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->extractTo('path/to/directory', 'folder');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->addFile('file-1.txt', 'file-1.txt');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->addFiles(array(
 *        'file-1.txt' => 'file-1.txt',
 *        'file-2.txt' => 'file-2.txt'
 *    ));
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->deleteFile('file-1.txt');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->deleteFiles(
 *        'file-1.txt',
 *        'file-2.txt'
 *    ));
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->renameFiles(array(
 *        'foo.txt' => 'bar.txt',
 *        'baz.txt' => 'foo.txt'
 *    ));
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->renameFile('foo.txt', 'bar.txt');
 *
 * -------------------------------------------------------------------------
 *
 *    echo Package::take('file.zip')->getContent('file-1.txt');
 *
 * -------------------------------------------------------------------------
 *
 *    var_dump(Package::take('file.zip')->getInfo());
 *
 * -------------------------------------------------------------------------
 *
 */

class Package extends Plugger {

    protected static $open = null;
    protected static $map = null;

    public static function take($files = null) {
        if( ! extension_loaded('zip')) {
            Guardian::abort('<a href="http://www.php.net/manual/en/book.zip.php" title="PHP &ndash; Zip" rel="nofollow" target="_blank">PHP Zip</a> extension is not installed on your web server.');
        }
        self::$open = null;
        self::$map = null;
        if(is_array($files)) {
            self::$map = array();
            $taken = false;
            foreach($files as $key => $value) {
                $key = File::path($key);
                $value = File::path($value);
                self::$map[$key] = $value;
                if( ! $taken) {
                    self::$open = $key;
                    $taken = true;
                }
            }
        } else {
            self::$open = File::path($files);
        }
        return new static;
    }

    /**
     * ====================================================================
     *  PACKING
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter    | Type   | Description
     *  ------------ | ------ | -------------------------------------------
     *  $destination | string | A package name, or path to a package file
     *  $bucket      | string | Root directory to be added in the ZIP file
     *  ------------ | ------ | -------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function pack($destination = null, $bucket = false) {
        $zip = new ZipArchive();
        if(is_dir(self::$open)) {
            $root = rtrim(self::$open, DS);
            $package = basename(self::$open);
        } else {
            $root = dirname(self::$open);
            $package = basename(self::$open, '.' . pathinfo(self::$open, PATHINFO_EXTENSION));
        }
        // Handling for `Package::take('foo/bar')->pack()`
        if(is_null($destination)) {
            $destination = dirname($root) . DS . $package . '.zip';
        } else {
            $destination = File::path($destination);
            // Handling for `Package::take('foo/bar')->pack('package.zip')`
            if(strpos($destination, DS) === false) {
                $destination = dirname($root) . DS . $destination;
            }
            // Handling for `Package::take('foo/bar')->pack('bar/baz')`
            if(is_dir($destination)) {
                $destination .= DS . $package . '.zip';
            }
        }
        if($old = File::exist($destination)) {
            File::open($old)->delete();
        }
        if( ! $zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }
        if($bucket !== false) {
            if($bucket !== true) {
                $package = $bucket;
            }
            $dir = $package . DS;
            $zip->addEmptyDir($package);
        } else {
            $dir = "";
        }
        if(is_array(self::$map)) {
            foreach(self::$map as $key => $value) {
                if(File::exist($key)) {
                    $zip->addFile($key, $dir . $value);
                }
            }
            $zip->close();
        } else {
            if(is_dir(self::$open)) {
                foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::$open, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                    if(is_dir($file)) {
                        $zip->addEmptyDir(str_replace(self::$open . DS, $dir, $file . DS));
                    } else if(is_file($file)) {
                        $zip->addFromString(str_replace(self::$open . DS, $dir, $file), file_get_contents($file));
                    }
                }
            } else if(is_file(self::$open)) {
                $zip->addFromString($dir . basename(self::$open), file_get_contents(self::$open));
            }
            $zip->close();
        }
        self::$open = $destination;
        return new static;
    }

    /**
     * ====================================================================
     *  EXTRACTING
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter    | Type   | Description
     *  ------------ | ------ | -------------------------------------------
     *  $destination | string | Path to a package file
     *  $bucket      | string | Load the extracted files into this folder
     *  ------------ | ------ | -------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function extractTo($destination = null, $bucket = false) {
        $zip = new ZipArchive();
        if(is_null($destination)) {
            $destination = dirname(self::$open);
        } else {
            $destination = rtrim(File::path($destination), '\\/');
        }
        // Handling for `Package::take('file.zip')->extractTo('foo/bar', true)`
        if($bucket === true) {
            $bucket = basename(self::$open, '.' . pathinfo(self::$open, PATHINFO_EXTENSION));
        }
        if($bucket !== false && ! File::exist($destination . DS . $bucket)) {
            $bucket = File::path($bucket);
            mkdir($destination . DS . $bucket, 0777, true);
        }
        if(File::exist(self::$open) && $zip->open(self::$open)) {
            if($bucket !== false) {
                $zip->extractTo($destination . DS . $bucket);
            } else {
                $zip->extractTo($destination);
            }
            $zip->close();
        }
        return new static;
    }

    public static function extract($bucket = false) {
        return self::extractTo(null, $bucket);
    }

    /**
     * ====================================================================
     *  ADD FILES TO A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------
     *  $files    | array | Array of files and its destinations
     *  --------- | ----- | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function addFiles($files = array(), $destination = null) {
        $zip = new ZipArchive();
        if(File::exist(self::$open) && $zip->open(self::$open)) {
            if( ! is_array($files)) {
                // Handling for `Package::take('file.zip')->addFile('test.txt')`
                if(strpos($files, DS) === false) {
                    $files = dirname(self::$open) . DS . $files;
                }
                if(File::exist($files)) {
                    if(is_null($destination)) {
                        $destination = basename($files);
                    }
                    $zip->addFile($files, $destination);
                }
                $zip->close();
                return new static;
            }
            foreach($files as $key => $value) {
                // Handling for `Package::take('file.zip')->addFiles(array('test-1.txt' => 'test-1.txt'))`
                if(strpos($key, DS) === false) {
                    $key = dirname(self::$open) . DS . $key;
                }
                if(is_null($value)) {
                    $value = basename($key);
                }
                if(File::exist($key)) {
                    $zip->addFile($key, $value);
                }
            }
            $zip->close();
        }
        return new static;
    }

    /**
     * ====================================================================
     *  ADD A FILE TO A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter    | Type   | Description
     *  ------------ | ------ | -------------------------------------------
     *  $path        | string | File to be added into the ZIP file
     *  $destination | string | File location path in the ZIP file
     *  ------------ | ------ | -------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function addFile($path = "", $destination = null) {
        return self::addFiles($path, $destination);
    }

    /**
     * ====================================================================
     *  REMOVE FILES FROM A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------
     *  $files    | array | Array of files to be deleted from the ZIP
     *  --------- | ----- | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function deleteFiles($files = array()) {
        $zip = new ZipArchive();
        if( ! is_array($files)) {
            $files = array($files);
        }
        if(File::exist(self::$open) && $zip->open(self::$open)) {
            foreach($files as $file) {
                if($zip->locateName($file) !== false) {
                    $zip->deleteName($file);
                }
            }
            $zip->close();
        }
        return new static;
    }

    /**
     * ====================================================================
     *  REMOVE A FILE FROM A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $file     | string | File to be deleted from the ZIP
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function deleteFile($file) {
        return self::deleteFiles($file);
    }

    /**
     * ====================================================================
     *  RENAME FILES INSIDE A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------
     *  $old      | array | Array of old file name and new file name
     *  --------- | ----- | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function renameFiles($old, $new = "") {
        $zip = new ZipArchive();
        if(File::exist(self::$open) && $zip->open(self::$open)) {
            if(is_array($old)) {
                foreach($old as $k => $v) {
                    $k = File::path($k);
                    $v = File::path($v);
                    $root = trim(dirname($k), DS . '.') !== "" ? dirname($k) . DS : "";
                    $zip->renameName($k, $root . basename($v));
                }
            } else {
                $old = File::path($old);
                $root = trim(dirname($old), DS . '.') !== "" ? dirname($old) . DS : "";
                $zip->renameName($old, $root . basename($new));
            }
            $zip->close();
        }
        return new static;
    }

    /**
     * ====================================================================
     *  RENAME A FILE INSIDE A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $old      | string | The old name
     *  $new      | string | The new name
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function renameFile($old, $new) {
        return self::renameFiles($old, $new);
    }

    /**
     * ====================================================================
     *  GET CONTENTS OF A FILE IN A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $file     | string | File path relative to the ZIP file as root
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function getContent($file) {
        $zip = new ZipArchive();
        $results = false;
        if(File::exist(self::$open) && $zip->open(self::$open)) {
            if($zip->locateName($file) !== false) {
                $results = $zip->getFromName($file);
            }
            $zip->close();
        }
        return $results;
    }

    /**
     * ====================================================================
     *  INSPECT A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $key      | string | Key of the resulted array data
     *  $fallback | mixed  | Fallback value if array value is not available
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function getInfo($key = null, $fallback = false) {
        $results = array();
        $zip = new ZipArchive();
        if(File::exist(self::$open) && $zip->open(self::$open)) {
            $extension = pathinfo($zip->filename, PATHINFO_EXTENSION);
            $results = array(
                'path' => self::$open,
                'name' => basename($zip->filename, '.' . $extension),
                'url' => File::url(self::$open),
                'extension' => strtolower($extension),
                'last_update' => filemtime(self::$open),
                'update' => date('Y-m-d H:i:s', filemtime(self::$open)),
                'size_raw' => filesize(self::$open),
                'size' => File::size(self::$open),
                'status' => $zip->status,
                'total' => $zip->numFiles
            );
            for($i = 0; $i < $results['total']; ++$i) {
                $results['files'][$i] = $zip->statIndex($i);
            }
            $zip->close();
        }
        if( ! is_null($key)) {
            return Mecha::GVR($results, $key, $fallback);
        }
        return ! empty($results) ? $results : $fallback;
    }

}