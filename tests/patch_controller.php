<?php
$file = __DIR__ . '/../app/Controllers/CustomerController.php';
$content = file_get_contents($file);

$target = '            if ($newAvatar) {
                $updateData[\'profile_photo\'] = $newAvatar;
            }

            $newDoc = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                \'id_document_file\',
                \'id_document_file_base64\',
                \'customers/documents\',
                [
                    \'application/pdf\'   => \'pdf\',
                    \'application/x-pdf\' => \'pdf\',
                    \'image/jpeg\'        => \'jpg\',
                    \'image/pjpeg\'       => \'jpg\',
                    \'image/png\'         => \'png\',
                    \'image/x-png\'       => \'png\',
                    \'image/webp\'        => \'webp\',
                ]
            );
            if ($newDoc) {
                $updateData[\'id_document_file\'] = $newDoc;
            }';

$replacement = '            if ($newAvatar) {
                if (!empty($customer[\'profile_photo\'])) {
                    $this->deleteCustomerAssociatedFiles([\'profile_photo\' => $customer[\'profile_photo\']]);
                }
                $updateData[\'profile_photo\'] = $newAvatar;
            } elseif (!empty($input[\'remove_profile_photo\']) && $input[\'remove_profile_photo\'] === \'1\') {
                if (!empty($customer[\'profile_photo\'])) {
                    $this->deleteCustomerAssociatedFiles([\'profile_photo\' => $customer[\'profile_photo\']]);
                }
                $updateData[\'profile_photo\'] = null;
            }

            $newDoc = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                \'id_document_file\',
                \'id_document_file_base64\',
                \'customers/documents\',
                [
                    \'application/pdf\'   => \'pdf\',
                    \'application/x-pdf\' => \'pdf\',
                    \'image/jpeg\'        => \'jpg\',
                    \'image/pjpeg\'       => \'jpg\',
                    \'image/png\'         => \'png\',
                    \'image/x-png\'       => \'png\',
                    \'image/webp\'        => \'webp\',
                ]
            );
            if ($newDoc) {
                if (!empty($customer[\'id_document_file\'])) {
                    $this->deleteCustomerAssociatedFiles([\'id_document_file\' => $customer[\'id_document_file\']]);
                }
                $updateData[\'id_document_file\'] = $newDoc;
            } elseif (!empty($input[\'remove_id_document\']) && $input[\'remove_id_document\'] === \'1\') {
                if (!empty($customer[\'id_document_file\'])) {
                    $this->deleteCustomerAssociatedFiles([\'id_document_file\' => $customer[\'id_document_file\']]);
                }
                $updateData[\'id_document_file\'] = null;
            }';

// Normalize CRLF to LF for matching
$cNorm = str_replace("\r\n", "\n", $content);
$tNorm = str_replace("\r\n", "\n", $target);
$rNorm = str_replace("\r\n", "\n", $replacement);

if (strpos($cNorm, $tNorm) !== false) {
    $cNorm = str_replace($tNorm, $rNorm, $cNorm);
    // restore CRLF if original had CRLF
    if (strpos($content, "\r\n") !== false) {
        $cNorm = str_replace("\n", "\r\n", $cNorm);
    }
    file_put_contents($file, $cNorm);
    echo "SUCCESS: CustomerController.php updated successfully!\n";
} else {
    echo "ERROR: Target not found in CustomerController.php\n";
}
