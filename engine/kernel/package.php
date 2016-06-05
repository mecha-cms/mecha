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

class Package extends __ {

    protected $open = null;
    protected $opens = null;
    protected $zip = null;

    public function __construct($files, $fallback = false) {
        if( ! extension_loaded('zip')) {
            if(is_null($fallback)) {
                Guardian::abort('<a href="http://www.php.net/manual/en/book.zip.php" title="PHP &ndash; Zip" rel="nofollow" target="_blank">PHP Zip</a> extension is not installed on your web server.');
            }
            return $fallback;
        }
        $this->open = $this->opens = null;
        $this->zip = new ZipArchive();
        if(is_array($files)) {
            $this->opens = array();
            $taken = false;
            foreach($files as $key => $value) {
                $key = File::path($key);
                $value = File::path($value);
                $this->opens[$key] = $value;
                if( ! $taken) {
                    $this->open = $key;
                    $taken = true;
                }
            }
        } else {
            $this->open = File::path($files);
        }
        return $this;
    }

    public static function take($files) {
        return new Package($files);
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

    public function pack($destination = null, $bucket = false) {
        if(is_dir($this->open)) {
            $root = rtrim($this->open, DS);
            $package = File::B($this->open);
        } else {
            $root = File::D($this->open);
            $package = File::N($this->open);
        }
        // Handling for `Package::take('foo/bar')->pack()`
        if(is_null($destination)) {
            $destination = File::D($root) . DS . $package . '.zip';
        } else {
            $destination = File::path($destination);
            // Handling for `Package::take('foo/bar')->pack('package.zip')`
            if(strpos($destination, DS) === false) {
                $root = ! is_array($this->opens) ? File::D($root) : $root;
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
        if( ! $this->zip->open($destination, ZipArchive::CREATE)) {
            return false;
        }
        if($bucket !== false) {
            if($bucket !== true) {
                $package = $bucket;
            }
            $dir = $package . DS;
            $this->zip->addEmptyDir($package);
        } else {
            $dir = "";
        }
        if(is_array($this->opens)) {
            foreach($this->opens as $key => $value) {
                if(File::exist($key)) {
                    $this->zip->addFile($key, $dir . $value);
                }
            }
            $this->zip->close();
        } else {
            if(is_dir($this->open)) {
                foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->open, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                    if(is_dir($file)) {
                        $this->zip->addEmptyDir(str_replace($this->open . DS, $dir, $file . DS));
                    } else if(is_file($file)) {
                        $this->zip->addFromString(str_replace($this->open . DS, $dir, $file), file_get_contents($file));
                    }
                }
            } else if(is_file($this->open)) {
                $this->zip->addFromString($dir . File::B($this->open), file_get_contents($this->open));
            }
            $this->zip->close();
        }
        $this->open = $destination;
        return $this;
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

    public function extractTo($destination, $bucket = false) {
        if(is_null($destination)) {
            $destination = File::D($this->open);
        } else {
            $destination = rtrim(File::path($destination), DS);
        }
        // Handling for `Package::take('file.zip')->extractTo('foo/bar', true)`
        if($bucket === true) {
            $bucket = File::N($this->open);
        }
        if($bucket !== false && ! File::exist($destination . DS . $bucket)) {
            $bucket = File::path($bucket);
            mkdir($destination . DS . $bucket, 0777, true);
        }
        if($this->zip->open($this->open) === true) {
            if($bucket !== false) {
                $this->zip->extractTo($destination . DS . $bucket);
            } else {
                $this->zip->extractTo($destination);
            }
            $this->zip->close();
        }
        return $this;
    }

    // --ibid
    public function extract($bucket = false) {
        return $this->extractTo(null, $bucket);
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

    public function addFile($file, $destination = null) {
        if($this->zip->open($this->open) === true) {
            // Handling for `Package::take('file.zip')->addFile('test.txt')`
            if(strpos($file, DS) === false) {
                $file = File::D($this->open) . DS . $file;
            }
            if(File::exist($file)) {
                if(is_null($destination)) {
                    $destination = File::B($file);
                }
                $this->zip->addFile($file, $destination);
            }
            $this->zip->close();
        }
        return $this;
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

    public function addFiles($files) {
        foreach($files as $key => $value) {
            $this->addFile($key, $value);
        }
        return $this;
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

    public function deleteFile($file) {
        if($this->zip->open($this->open) === true) {
            if($this->zip->locateName($file) !== false) {
                $this->zip->deleteName($file);
            }
            $this->zip->close();
        }
        return $this;
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

    public function deleteFiles($files) {
        foreach($files as $file) {
            $this->deleteFile($file);
        }
        return $this;
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

    public function deleteFolder($folder) {
        $folder = rtrim(File::path($folder), DS);
        if($this->zip->open($this->open) === true) {
            for($i = 0; $i < $this->zip->numFiles; ++$i) {
                $info = $this->zip->statIndex($i);
                $b = rtrim(substr(File::path($info['name']), 0, strlen($folder)), DS);
                if($b === $folder) {
                    $this->zip->deleteIndex($i);
                }
            }
            $this->zip->close();
        }
        return $this;
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

    public function deleteFolders($folders) {
        foreach($folders as $folder) {
            $this->deleteFolder($folder);
        }
        return $this;
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

    public function renameFile($old, $new = "") {
        if($this->zip->open($this->open) === true) {
            $old = File::path($old);
            $root = File::D($old) !== "" ? File::D($old) . DS : "";
            $this->zip->renameName($old, $root . File::B($new));
            $this->zip->close();
        }
        return $this;
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

    public function renameFiles($names) {
        foreach($names as $old => $new) {
            $this->renameFile($old, $new);
        }
        return $this;
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

    public function read($file, $results = false) {
        if($this->zip->open($this->open) === true) {
            if($this->zip->locateName($file) !== false) {
                $results = $this->zip->getFromName($file);
            }
            $this->zip->close();
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

    public function inspect($key = null, $fallback = false) {
        $results = array();
        if($this->zip->open($this->open) === true) {
            $results = array_merge(File::inspect($this->open), array(
                'status' => $this->zip->status,
                'total' => $this->zip->numFiles
            ));
            for($i = 0; $i < $results['total']; ++$i) {
                $data = $this->zip->statIndex($i);
                $data['name'] = str_replace(DS . DS, DS, File::path($data['name']));
                $results['files'][$i] = $data;
            }
            $this->zip->close();
        }
        if( ! is_null($key)) {
            return Mecha::GVR($results, $key, $fallback);
        }
        return ! empty($results) ? $results : $fallback;
    }

}