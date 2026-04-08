<?php
namespace App\Traits;

trait FileUploadTrait {
    protected function uploadFile(string $inputName, string $path): ?string {
        if (!isset($_FILES[$inputName])) {
            return null;
        }

        if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            error_log("Upload error ({$inputName}): " . $_FILES[$inputName]['error']);
            return null;
        }

        $fileName = time() . '_' . basename($_FILES[$inputName]['name']);
        $storageRoot = dirname(__DIR__, 2) . '/storage/uploads/';
        $fullPath = rtrim($storageRoot . trim($path, '/'), '/') . '/';

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $fullPath . $fileName)) {
            error_log("Failed to move uploaded file for {$inputName} to {$fullPath}{$fileName}");
            return null;
        }

        return $fileName;
    }
    protected function getUploadErrorMessage(int $errorCode): string {
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return 'Upload successful.';
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'ፋይሉ ከፍተኛ ነው። እባክዎ ከእጅጉ ዕድል ውስጥ ያስገቡ።';
            case UPLOAD_ERR_PARTIAL:
                return 'ፋይሉ ክፍት ነው። እንገና ይሞክሩ።';
            case UPLOAD_ERR_NO_FILE:
                return 'ፋይል አልተመረጠም። እባክዎ ፎቶንና የ201 ፋይልን ይምረጡ።';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'የጊጥ ግዴታ በሆነ አውታረ ማዕከል የተጎዳ።';
            case UPLOAD_ERR_CANT_WRITE:
                return 'ፋይሉን ወደ አውታረ ማከማቻ ማድረግ አልተቻለም።';
            case UPLOAD_ERR_EXTENSION:
                return 'የፋይል እቅድ በሚገድድ ስር ተዘግቷል።';
            default:
                return 'የፋይል ስህተት ተከስቷል።';
        }
    }
}   