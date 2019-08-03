<?php

/*****
 * =========================================================
 * ============        FILE UPLOAD  CLASS     ==============
 * =========================================================
 * 
 * php script manage uploaded file
 * 
 *****/

class FileUpload
{

   // Properties

   // Uploaded file data
   private $name;
   private $type;
   private $tmp_name;
   private $error;
   private $size;

   private $fileExtension;
   private $allowedFileExtensions = array('png', 'jpg', 'jpeg', 'gif', 'doc', 'docs', 'xls', 'txt');
   private $uploadMaxFileSize;

   // Constructor
   public function __construct(array $file)
   {
      // set uploaded file details
      $this->name              = $this->getName($file['name']);
      $this->type              = $file['type'];
      $this->tmp_name          = $file['tmp_name'];
      $this->error             = $file['error'];
      $this->size              = $file['size'];
      $this->uploadMaxFileSize = ini_get('upload_max_filesize');
   }

   // Methods
   /**
    *  Make encryption to uploaded file
    *
    * @param String $fileName
    * @return String $encryptionName
    */
   private function getName($fileName)
   {
      $nameAndExtension = explode('.', $fileName);
      // file extension is the last index in array "end()"
      $this->fileExtension = strtolower(end($nameAndExtension));

      $cryptName = base64_encode(time() . $fileName);
      $name = strtolower($cryptName);
      $name = substr($name, 0, 30);

      return $name;
   }

   /**
    * Get the uploaded ile name that saved in destination
    *
    * @param null
    * @return String $uploadedFileName
    */
   public function getFileName()
   {
      return $this->name . '.' . $this->fileExtension;
   }

   /**
    * Check file is an allowed extension
    * 
    * @param null
    * @return Boolean
    */
   private function isAllowedFileExtension()
   {
      return \in_array($this->fileExtension, $this->allowedFileExtensions);
   }

   /**
    * Check file is an allowed size
    * 
    * @param null
    * @return Boolean
    */
   public function isNotAllowedFileSize()
   {
      \preg_match_all('/(\d+)([MG])/i', $this->uploadMaxFileSize, $matches);

      $maxFileSizeToUpload = $matches[1][0];
      $sizeUnit = $matches[2][0];
      // Convert file size from bytes to megabytes | gigabytes
      $fileSize = ($sizeUnit == 'M') ? ($this->size / 1024 / 1024) : ($this->size / 1024 / 1024 / 1024);
      $fileSize = ceil($fileSize);
      $this->size = $fileSize;
      return (int) $fileSize > (int) $maxFileSizeToUpload;
   }

   private function CheckUploadError()
   {
      switch ($this->error) {
         case 1:
            throw new Exception("Sorray, file size " . $this->size . " is larger than the max size file upload " . $this->uploadMaxFileSize);
            break;
         case 2:
            throw new Exception("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.");
            break;
         case 3:
            throw new Exception("The uploaded file was only partially uploaded.");
            break;
         case 4:
            throw new Exception("No file was uploaded.");
            break;
         case 6:
            throw new Exception("Missing a temporary folder.");
            break;
         case 7:
            throw new Exception("Failed to write file to disk.");
            break;
         case 8:
            throw new Exception("A PHP extension stopped the file upload.");
            break;
         default:
            throw new Exception("There\'s an error in uploading file.");
            break;
      }
   }

   public function upload($distention)
   {
      if ($this->error != 0) {
         $this->CheckUploadError();
      } elseif (!$this->isAllowedFileExtension()) {
         throw new Exception("Sorray, not support this file extension");
      } elseif ($this->isNotAllowedFileSize()) {
         throw new Exception("Sorray, file size is larger than the max size file upload " . $this->uploadMaxFileSize);
      } else {
         if (!is_writable($distention)) {
            throw new Exception("The folder directory" . $distention . " not writable");
         } else {
            move_uploaded_file($this->tmp_name, $distention . DIRECTORY_SEPARATOR . $this->getFileName());
         }
      }
   }

   public function remove($file)
   {
      unlink($file);
   }
}
