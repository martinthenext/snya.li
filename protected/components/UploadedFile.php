<?php

class UploadedFile extends CUploadedFile
{

    static private $_files;
    private $_name;
    private $_tempName;
    private $_type;
    private $_size;
    private $_error;
    private $_md5Name;

    public function getFilename()
    {
        return $this->_md5Name . '.' . $this->getExtensionName();
    }

    public function getPath()
    {
        return mb_substr($this->_md5Name, 0, 2, 'UTF-8') . DIRECTORY_SEPARATOR
                . mb_substr($this->_md5Name, 2, 2) . DIRECTORY_SEPARATOR;
    }

    /**
     * Constructor.
     * Use {@link getInstance} to get an instance of an uploaded file.
     * @param string $name the original name of the file being uploaded
     * @param string $tempName the path of the uploaded file on the server.
     * @param string $type the MIME-type of the uploaded file (such as "image/gif").
     * @param integer $size the actual size of the uploaded file in bytes
     * @param integer $error the error code
     */
    public function __construct($name, $tempName, $type, $size, $error)
    {
        $this->_name = $name;
        if (file_exists($tempName) && is_file($tempName)) {
            $this->_md5Name = md5_file($tempName);
        }
        $this->_tempName = $tempName;
        $this->_type = $type;
        $this->_size = $size;
        $this->_error = $error;
        return parent::__construct($name, $tempName, $type, $size, $error);
    }

    /**
     * Processes incoming files for {@link getInstanceByName}.
     * @param string $key key for identifiing uploaded file: class name and subarray indexes
     * @param mixed $names file names provided by PHP
     * @param mixed $tmp_names temporary file names provided by PHP
     * @param mixed $types filetypes provided by PHP
     * @param mixed $sizes file sizes provided by PHP
     * @param mixed $errors uploading issues provided by PHP
     */
    protected static function collectFilesRecursive($key, $names, $tmp_names, $types, $sizes, $errors)
    {
        if (is_array($names)) {
            foreach ($names as $item => $name)
                self::collectFilesRecursive($key . '[' . $item . ']', $names[$item], $tmp_names[$item], $types[$item], $sizes[$item], $errors[$item]);
        } else
            self::$_files[$key] = new UploadedFile($names, $tmp_names, $types, $sizes, $errors);
    }

    /**
     * Initially processes $_FILES superglobal for easier use.
     * Only for internal usage.
     */
    protected static function prefetchFiles()
    {
        self::$_files = array();
        if (!isset($_FILES) || !is_array($_FILES))
            return;

        foreach ($_FILES as $class => $info)
            self::collectFilesRecursive($class, $info['name'], $info['tmp_name'], $info['type'], $info['size'], $info['error']);
    }

    /**
     * Returns an instance of the specified uploaded file.
     * The name can be a plain string or a string like an array element (e.g. 'Post[imageFile]', or 'Post[0][imageFile]').
     * @param string $name the name of the file input field.
     * @return CUploadedFile the instance of the uploaded file.
     * Null is returned if no file is uploaded for the specified name.
     */
    public static function getInstanceByName($name)
    {

        if (null === self::$_files)
            self::prefetchFiles();

        return isset(self::$_files[$name]) && self::$_files[$name]->getError() != UPLOAD_ERR_NO_FILE ? self::$_files[$name] : null;
    }

    /**
     * Returns an array of instances starting with specified array name.
     *
     * If multiple files were uploaded and saved as 'Files[0]', 'Files[1]',
     * 'Files[n]'..., you can have them all by passing 'Files' as array name.
     * @param string $name the name of the array of files
     * @return CUploadedFile[] the array of CUploadedFile objects. Empty array is returned
     * if no adequate upload was found. Please note that this array will contain
     * all files from all subarrays regardless how deeply nested they are.
     */
    public static function getInstancesByName($name)
    {
        if (null === self::$_files)
            self::prefetchFiles();

        $len = strlen($name);
        $results = array();
        foreach (array_keys(self::$_files) as $key)
            if (0 === strncmp($key, $name . '[', $len + 1) && self::$_files[$key]->getError() != UPLOAD_ERR_NO_FILE)
                $results[] = self::$_files[$key];
        return $results;
    }

    /**
     * Returns all uploaded files for the given model attribute.
     * @param CModel $model the model instance
     * @param string $attribute the attribute name. For tabular file uploading, this can be in the format of "[$i]attributeName", where $i stands for an integer index.
     * @return CUploadedFile[] array of CUploadedFile objects.
     * Empty array is returned if no available file was found for the given attribute.
     */
    public static function getInstances($model, $attribute)
    {
        return self::getInstancesByName(CHtml::resolveName($model, $attribute));
    }

}
