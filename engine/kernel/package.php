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
 *    Package::take('file.zip')->deleteFolder('folder-1');
 *
 * -------------------------------------------------------------------------
 *
 *    Package::take('file.zip')->deleteFolders(
 *        'folder-1',
 *        'folder-2'
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
 *    echo Package::take('file.zip')->read('file-1.txt');
 *
 * -------------------------------------------------------------------------
 *
 *    var_dump(Package::take('file.zip')->inspect());
 *
 * -------------------------------------------------------------------------
 *
 */

class Package extends Base {

    protected static $open = null;
    protected static $opens = null;
    protected static $zip = null;

    public static function take($files) {
        if( ! extension_loaded('zip')) {
            Guardian::abort('<a href="http://www.php.net/manual/en/book.zip.php" title="PHP &ndash; Zip" rel="nofollow" target="_blank">PHP Zip</a> extension is not installed on your web server.');
        }
        self::$open = self::$opens = null;
        self::$zip = new ZipArchive();
        if(is_array($files)) {
            self::$opens = array();
            $taken = false;
            foreach($files as $key => $value) {
                $key = File::path($key);
                $value = File::path($value);
                self::$opens[$key] = $value;
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
        if(is_dir(self::$open)) {
            $root = rtrim(self::$open, DS);
            $package = File::B(self::$open);
        } else {
            $root = File::D(self::$open);
            $package = File::N(self::$open);
        }
        // Handling for `Package::take('foo/bar')->pack()`
        if(is_null($destination)) {
            $destination = File::D($root) . DS . $package . '.zip';
        } else {
            $destination = File::path($destination);
            // Handling for `Package::take('foo/bar')->pack('package.zip')`
            if(strpos($destination, DS) === false) {
                $root = ! is_array(self::$opens) ? File::D($root) : $root;
                $destination = $root . DS . $destination;
            }
            // Handling for `Package::take('foo/bar')->pack('bar/baz')`
            if(is_dir($destination)) {
                $destination .= DS . $package . '.zip';
            }
        }
        if($old = File::exist($destination)) {
            File::open($old)->delete();
        }
        if( ! self::$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }
        if($bucket !== false) {
            if($bucket !== true) {
                $package = $bucket;
            }
            $dir = $package . DS;
            self::$zip->addEmptyDir($package);
        } else {
            $dir = "";
        }
        if(is_array(self::$opens)) {
            foreach(self::$opens as $key => $value) {
                if(File::exist($key)) {
                    self::$zip->addFile($key, $dir . $value);
                }
            }
            self::$zip->close();
        } else {
            if(is_dir(self::$open)) {
                foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::$open, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                    if(is_dir($file)) {
                        self::$zip->addEmptyDir(str_replace(self::$open . DS, $dir, $file . DS));
                    } else if(is_file($file)) {
                        self::$zip->addFromString(str_replace(self::$open . DS, $dir, $file), file_get_contents($file));
                    }
                }
            } else if(is_file(self::$open)) {
                self::$zip->addFromString($dir . File::B(self::$open), file_get_contents(self::$open));
            }
            self::$zip->close();
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
     *  $bucket      | string | Load the extracted file(s) into this folder
     *  ------------ | ------ | -------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function extractTo($destination, $bucket = false) {
        if(is_null($destination)) {
            $destination = File::D(self::$open);
        } else {
            $destination = rtrim(File::path($destination), DS);
        }
        // Handling for `Package::take('file.zip')->extractTo('foo/bar', true)`
        if($bucket === true) {
            $bucket = File::N(self::$open);
        }
        if($bucket !== false && ! File::exist($destination . DS . $bucket)) {
            $bucket = File::path($bucket);
            mkdir($destination . DS . $bucket, 0777, true);
        }
        if(self::$zip->open(self::$open) === true) {
            if($bucket !== false) {
                self::$zip->extractTo($destination . DS . $bucket);
            } else {
                self::$zip->extractTo($destination);
            }
            self::$zip->close();
        }
        return new static;
    }

    // --ibid
    public static function extract($bucket = false) {
        return self::extractTo(null, $bucket);
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

    public static function addFile($file, $destination = null) {
        if(self::$zip->open(self::$open) === true) {
            // Handling for `Package::take('file.zip')->addFile('test.txt')`
            if(strpos($file, DS) === false) {
                $file = File::D(self::$open) . DS . $file;
            }
            if(File::exist($file)) {
                if(is_null($destination)) {
                    $destination = File::B($file);
                }
                self::$zip->addFile($file, $destination);
            }
            self::$zip->close();
        }
        return new static;
    }

    /**
     * ====================================================================
     *  ADD FILE(S) TO A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------
     *  $files    | array | Array of file(s) and its destination(s)
     *  --------- | ----- | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function addFiles($files) {
        foreach($files as $key => $value) {
            self::addFile($key, $value);
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
        if(self::$zip->open(self::$open) === true) {
            if(self::$zip->locateName($file) !== false) {
                self::$zip->deleteName($file);
            }
            self::$zip->close();
        }
        return new static;
    }

    /**
     * ====================================================================
     *  REMOVE FILE(S) FROM A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------
     *  $files    | array | Array of file(s) to be deleted from the ZIP
     *  --------- | ----- | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function deleteFiles($files) {
        foreach($files as $file) {
            self::deleteFile($file);
        }
        return new static;
    }

    /**
     * ====================================================================
     *  REMOVE A FOLDER WITH ITS CONTENT(S) FROM A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type   | Description
     *  --------- | ------ | ----------------------------------------------
     *  $folder   | string | Folder to be deleted from the ZIP
     *  --------- | ------ | ----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function deleteFolder($folder) {
        $folder = rtrim(File::path($folder), DS);
        if(self::$zip->open(self::$open) === true) {
            for($i = 0; $i < self::$zip->numFiles; ++$i) {
                $info = self::$zip->statIndex($i);
                $b = rtrim(substr(File::path($info['name']), 0, strlen($folder)), DS);
                if($b === $folder) {
                    self::$zip->deleteIndex($i);
                }
            }
            self::$zip->close();
        }
    }

    /**
     * ====================================================================
     *  REMOVE FOLDER(S) WITH ITS CONTENT(S) FROM A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------
     *  $folders  | array | Array of folder(s) to be deleted from the ZIP
     *  --------- | ----- | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function deleteFolders($folders) {
        foreach($folders as $folder) {
            self::deleteFolder($folder);
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

    public static function renameFile($old, $new = "") {
        if(self::$zip->open(self::$open) === true) {
            $old = File::path($old);
            $root = File::D($old) !== "" ? File::D($old) . DS : "";
            self::$zip->renameName($old, $root . File::B($new));
            self::$zip->close();
        }
        return new static;
    }

    /**
     * ====================================================================
     *  RENAME FILE(S) INSIDE A ZIP FILE
     * ====================================================================
     *
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *  Parameter | Type  | Description
     *  --------- | ----- | -----------------------------------------------
     *  $names    | array | Array of old file name and new file name
     *  --------- | ----- | -----------------------------------------------
     * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
     *
     */

    public static function renameFiles($names) {
        foreach($names as $old => $new) {
            self::renameFile($old, $new);
        }
        return new static;
    }

    /**
     * ====================================================================
     *  GET CONTENT OF A FILE IN A ZIP FILE
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

    public static function read($file, $results = false) {
        if(self::$zip->open(self::$open) === true) {
            if(self::$zip->locateName($file) !== false) {
                $results = self::$zip->getFromName($file);
            }
            self::$zip->close();
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

    public static function inspect($key = null, $fallback = false) {
        $results = array();
        if(self::$zip->open(self::$open) === true) {
            $results = array_merge(File::inspect(self::$open), array(
                'status' => self::$zip->status,
                'total' => self::$zip->numFiles
            ));
            for($i = 0; $i < $results['total']; ++$i) {
                $results['files'][$i] = self::$zip->statIndex($i);
            }
            self::$zip->close();
        }
        if( ! is_null($key)) {
            return Mecha::GVR($results, $key, $fallback);
        }
        return ! empty($results) ? $results : $fallback;
    }

}